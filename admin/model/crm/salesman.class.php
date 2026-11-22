<?php

/**
 * Class salesman_controller
 *
 * @author shiy
 * @version 7.0
 */

class salesman_controller extends adminCommon
{
    function getIndexStaticData_action(){
        $adminM         =   $this->MODEL('admin');
        $group  =   $adminM->getAdminGroupList(array('did' => $this->config['did'], 'orderby' => 'id'));
        $week   =   array('1' => '礼拜一', '2' => '礼拜二', '3' => '礼拜三', '4' => '礼拜四', '5' => '礼拜五', '6' => '礼拜六', '7' => '礼拜日');

        $data['group']          =  $group;
        $data['week']           =  $week;
        $data['picMaxSize']     =  $this->config['pic_maxsize'] ? $this->config['pic_maxsize'] : 5;
        $data['picType']        =  $this->config['pic_type'] ? $this->config['pic_type'] : 'jpg,png,jpeg,bmp,gif';
        $data['picPreUrl']      =  $this->config['sy_ossurl'];

        $this->render_json(0, 'ok', $data);
    }
    //  业务员列表
    function getList_action()
    {

        $adminM         =   $this->MODEL('admin');

        $where['did']   =   0;
        $where['is_crm']=   1;

        $keywordStr     =   trim($_POST['keyword']);
        $typeStr        =   intval($_POST['type']);
        if (isset($keywordStr) && !empty($keywordStr)) {
            if ($typeStr == 1){

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['name']              =   array('like', $keywordStr, '');
                $where['username']          =   array('like', $keywordStr, 'OR');
                $where['PHPYUNBTWEND_A']    =   '';
            }else if ($typeStr == 2){

                $where['uid']  =   array('like', $keywordStr);
            }
        }

        $page       =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('admin_user', $where, $page, array('limit' => $pageSize));

        $list   =   array();
        $total  =   intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {

                $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
            } else {

                $where['orderby']   =   'uid,desc';
            }

            $where['limit'] = $pages['limit'];
            $list   =   $adminM->getList($where, array('utype' => 'crm'));
            foreach ($list as $k => $v) {
                unset($list[$k]['password']);
                $list[$k]['num']        =   $v['num'] ? $v['num'] : '';
                $list[$k]['mobile']     =   $v['moblie'] ? $v['moblie'] : '';
                $list[$k]['crm_city']   =   $v['crm_city'] ? explode(',', $v['crm_city']) : array();
                $list[$k]['crm_duty']   =   $v['crm_duty'] ? explode(',', $v['crm_duty']) : array();
                $list[$k]['photo']    =   checkpic($v['photo']);
                $list[$k]['ewm']      =   checkpic($v['ewm']);
                $list[$k]['control_login']  =   $v['control_login'] ? explode(' - ', $v['control_login']) : '';
                $logintime = $v['control_login'] ? explode(' - ', $v['control_login']) : array();
                $list[$k]['login_start'] = $logintime[0] ? $logintime[0] : '';
                $list[$k]['login_end'] = $logintime[1] ? $logintime[1] : '';
            }
        }


        $result =   array(

            'list'          =>  $list,
            'total'         =>  intval($total),
            'pageSize'      =>  $pageSize,
            'pageSizes'     =>  $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }

    function getCache_action()
    {
        //海报信息
        $hbArr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $hbBgA = array();
        foreach ($hbArr as $v) {
            $hbBgA[] = $this->config['sy_weburl'] . '/data/upload/whb/logo/' . $v . '.png';
        }
        $data = array(
            'hbBgA' => $hbBgA,
        );
        $this->render_json(0, '', $data);
    }

    //  设置客户保护
    public function setCrmKh_action()
    {

        $adminM =   $this->MODEL('admin');
        $uid    =   intval($_POST['uid']);
        $nid    =   $adminM->upInfo(array('crm_kh' => (int)$_POST['crm_kh']), array('uid' => $uid));
        if ($nid) {
            $msg    =   $_POST['crm_kh'] == 1 ? '客户保护开启成功' : '客户保护权限关闭';

            $this->admin_json(0, $msg);
        } else {

            $this->render_json(1, '设置失败！');
        }
    }

    //  业务员离职
    public function setCrmStatus_action()
    {

        $adminM =   $this->MODEL('admin');

        $uid    =   intval($_POST['uid']);
        $status =   (int)$_POST['status'];
        $nid    =   $adminM->upInfo(array('status' => $status), array('uid' => $uid));
        if ($nid) {

            $msg=   $status == 1 ? '在职' : '离职';
            if ($status == 2) { //离职设置，客户重置未分配

                $release    =   isset($_POST['release']) && intval($_POST['release']) == 1 ? 1 : 0;// 是否释放客户
                if ($release) {

                    $companyM   =   $this->MODEL('company');
                    $comList    =   $this->obj->select_all('company', array('crm_uid' => $uid), 'uid');
                    if (!empty($comList)) {

                        $companyM->upInfo('', array('crm_uid' => $uid), array('crm_uid' => 0, 'crm_time' => 0, 'isfollow' => '0', 'f_time' => 0, 'pcrmuid' => $uid, 'release_time' => time()));

                        $releaseLogData =   array();
                        foreach ($comList as $k => $v) {

                            $releaseLogData[$k]['auid']     =   $uid;
                            $releaseLogData[$k]['uid']      =   $v['uid'];
                            $releaseLogData[$k]['content']  =   '业务员离职，释放客户';
                            $releaseLogData[$k]['ctime']    =   time();
                        }

                        $this->obj->DB_insert_multi('crm_release_log', $releaseLogData);
                    }

                    $msg    .=  '且释放客户';
                } else {

                    $msg    .=  '且保留客户';
                }
            }

            $this->admin_json(0, "业务员（ID:" . $uid . "）" . $msg . "设置成功！");
        } else {

            $this->render_json(1,'设置失败！');
        }
    }
    function getOptionStaticData_action(){

        $typeOptions=   array(
            array('id' =>1, 'name' => '录入客户'),
            array('id' =>2, 'name' => '客户资料'),
            array('id' =>3, 'name' => '客户跟进'),
            array('id' =>4, 'name' => '客户订单'),
            array('id' =>5, 'name' => '工作任务'),
            array('id' =>6, 'name' => '工作日志'),
            array('id' =>7, 'name' => '转交客户'),
            array('id' =>8, 'name' => '放弃客户'),
            array('id' =>9, 'name' => '等级标签'),
            array('id' =>10, 'name' => '外出申请'),
            array('id' =>11, 'name' => '联系方式'),
            array('id' =>12, 'name' => '领取客户'),
            array('id' =>13, 'name' => '推文任务'),
            array('id' =>14, 'name' => '发送短信'),
            array('id' =>15, 'name' => '简历推送'),
            array('id' =>16, 'name' => '岗位推广'),
            array('id' =>17, 'name' => '客户备注'),
            array('id' =>18, 'name' => '暂停招聘')
        );

        $adminM     =   $this->MODEL('admin');
        $crmUser    =   $adminM->getList(array('is_crm' => '1'));

        $data['typeOptions']  =  $typeOptions;
        $data['crmUser']      =  $crmUser;

        $this->render_json(0, 'ok', $data);
    }
    //  操作记录
    public function getOptionLog_action()
    {

        $crmM   =   $this->MODEL('crm');

        if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])){

            $where['auid']  =   $_POST['crm_uid'];
        }

        if (isset($_POST['logType']) && !empty($_POST['logType'])){

            $where['type']  =   $_POST['logType'];
        }

        $keywordStr         =   trim($_POST['keyword']);
        $where['content']   =   array('like', $keywordStr);

        if (isset($_POST['time']) && !empty($_POST['time'])) {

            $start  =   date('Y-m-d', $_POST['time'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['time'][1] / 1000);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['ctime'][]           =   array('>=', strtotime($start . " 00:00:00"));
            $where['ctime'][]           =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $page       =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('crm_log', $where, $page, array('limit' => $pageSize));

        $list   =   array();
        $total  =   intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {

                $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
            } else {

                $where['orderby']   =   'uid,desc';
            }

            $where['limit'] = $pages['limit'];
            $list   =   $crmM->getCrmLogList($where);
        }

        $result     =   array(

            'list'          =>  $list,
            'total'         =>  intval($total),
            'pageSize'      =>  $pageSize,
            'pageSizes'     =>  $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }

    //  删除操作记录
    function delOptLog_action()
    {

        $crmM   =   $this->MODEL('crm');
        $delId  =   $_POST['id'];
        if (!empty($delId)) {
            $return =   $crmM->delCrmLog($delId);
            if ($return['errcode'] == '9') {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        } else {
            $this->render_json(1, '参数错误，请重试！');
        }
    }

    //  指定业务员客户列表
    public function getCustomer_action()
    {

        $comM   =   $this->MODEL('company');

        $way    =   trim($_POST['way']);

        $where  =   array();

        if ($way == 'shift'){

            $where['crm_uid']   =   $_POST['crm_uid'];
        }else if ($way == 'assign'){

            $where['crm_uid']   =   0;
        }

        $keywordStr =   trim($_POST['keyword']);
        $typeStr    =   intval($_POST['type']);
        if (isset($keywordStr) && !empty($keywordStr)) {
            if ($typeStr == 1) {

                $where['name']  =   array('like', $keywordStr);
            } elseif ($typeStr == 2) {

                $where['uid']   =   $keywordStr;
            }
        }

        $page       =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('company', $where, $page, array('limit' => $pageSize));

        $list   =   array();
        $total  =   intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {

                $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
            } else {

                $where['orderby']   =   'uid,desc';
            }

            $where['limit'] = $pages['limit'];
            $rowArr =   $comM->getList($where, array('field' => '`uid`,`name`,`f_time`,`login_date`'));
            $list   =   $rowArr['list'];

            foreach ($list as $k => $v){

                $list[$k]['name']           =   !empty($v['name']) ? $v['name'] : '--';
                $list[$k]['login_date_n']   =   intval($v['login_date']) > 0 ? date('Y-m-d H:i', $v['login_date']) : '--';
                $list[$k]['time_n']         =   intval($v['f_time']) > 0 ? date('Y-m-d H:i', $v['f_time']) : '--';
            }
        }

        $result     =   array(

            'list'          =>  $list,
            'total'         =>  intval($total),
            'pageSize'      =>  $pageSize,
            'pageSizes'     =>  $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }
    function getCustomerStaticData_action(){

        $crmUser    =   array();

        $way    =   trim($_POST['way']);

        if ($way == 'shift') {

            $adminM =   $this->MODEL('admin');
            $crmUser=   $adminM->getList(array('is_crm' => '1', 'uid' => array('<>', $_POST['crm_uid'])));
        }

        $data['crmUser']      =  $crmUser;

        $this->render_json(0, 'ok', $data);
    }
    //  分配客户
    public function assignCustomer_action()
    {

        $salesmanId =   isset($_POST['uid']) ? $_POST['uid'] : 0;
        $companyId  =   isset($_POST['comid']) ? $_POST['comid'] : '';

        if ($salesmanId == 0 || $companyId == '') {

            $this->render_json(1, '参数错误，请稍候重试！');
        }

        if (!is_array($companyId)){
            $companyId  =   @explode(',', $companyId);
        }

        $comM       =   $this->MODEL('company');
        $result     =   $comM->upInfo($companyId, array(), array('crm_uid' => $salesmanId, 'crm_time' => time(), 'crm_source' => 5));

        if ($result){

            $this->admin_json(0, "分配客户（UID：".$_POST['comid']."）成功");
        }else{

            $this->render_json(1, '分配失败，请稍候重试！');
        }
    }

    //  转移客户
    public function shiftCustomer_action()
    {

        $salesmanId =   isset($_POST['uid']) ? $_POST['uid'] : 0;
        $companyId  =   isset($_POST['comid']) ? $_POST['comid'] : '';
        $new_uid    =   isset($_POST['new_uid']) ? $_POST['new_uid'] : 0;

        if (!is_array($companyId)){
            $companyId  =   @explode(',', $companyId);
        }

        if ($salesmanId == 0 || empty($companyId) || $new_uid == 0) {

            $this->render_json(1, '参数错误，请稍候重试！');
        }

        $companyId  =   @explode(',', $companyId);

        $comM       =   $this->MODEL('company');
        $result     =   $comM->upInfo($companyId, array('crm_uid' => $salesmanId), array('crm_uid' => $new_uid, 'crm_time' => time(), 'crm_source' => 4));

        if ($result){

            $this->admin_json(0, "转移客户（UID：".$_POST['comid']."）成功");
        }else{

            $this->render_json(1, '转移失败，请稍候重试！');
        }
    }

    //  保存业务员信息
    public function saveCrm_action()
    {

        $adminM =   $this->MODEL('admin');

        if(isset($_POST['crmAdd']) && intval($_POST['crmAdd']) == 1){

            $_POST  =   $this->post_trim($_POST);
            if ($_POST['login_start'] && $_POST['login_end']) {
                $logintime = array($_POST['login_start'], $_POST['login_end']);
            } else {
                $logintime = array();
            }
            $post   =   array(

                'username'          =>  $_POST['username'],
                'name'              =>  $_POST['name'],
                'm_id'              =>  $_POST['m_id'],
                'control_login'     =>  $logintime ? implode(' - ', $logintime) : '',
                'isdid'             =>  intval($_POST['isdid']),
                'index_lookstatistc'=>  $_POST['index_lookstatistc']
            );

            $post['is_crm'] =   $_POST['is_crm'];

            if (intval($_POST['is_crm']) == 1) {


                $post['weixin'] =   $_POST['weixin'];
                $post['moblie'] =   $_POST['mobile'];
                $post['qq']     =   $_POST['qq'];
                $post['num']    =   $_POST['num'];
                $post['call_num'] = $_POST['call_num'];
                $post['tuoxin_num'] = $_POST['tuoxin_num'];
                $post['follow_num'] = $_POST['follow_num'];
                $post['deal_num'] = $_POST['deal_num'];
                $post['month_deal_num'] = $_POST['month_deal_num'];
                $post['jobtai_ranking'] = intval($_POST['jobtai_ranking']);

                if (!empty($_POST['crm_duty'])) {
                    $post['crm_duty']   =   pylode(',', $_POST['crm_duty']);
                }
                if (!empty($_POST['crm_city'])) {
                    $post['crm_city']   =   $_POST['crm_city'];
                }
            }
            if(!empty($_FILES)){

                $uploadM        =   $this->MODEL('upload');

                if ($_FILES['photo_file']['tmp_name'] != '') {
                    $upArrPhoto =   array(

                        'file'  =>  $_FILES['photo_file'],
                        'dir'   =>  'adminuser'
                    );
                }
                if($_FILES['ewm_file']['tmp_name'] != ''){
                    $upArrEwm   =  array(

                        'file'  =>  $_FILES['ewm_file'],
                        'dir'   =>  'adminuser'
                    );
                }
                if (!empty($upArrPhoto)) {
                    $picPhoto   =   $uploadM->newUpload($upArrPhoto);
                }
                if (!empty($upArrEwm)) {
                    $picEwm     =   $uploadM->newUpload($upArrEwm);
                }

                if (!empty($picPhoto['picurl'])){
                    $post['photo']  =   $picPhoto['picurl'];
                }

                if (!empty($picEwm['picurl'])){
                    $post['ewm']    =   $picEwm['picurl'];
                }
            }

            if (empty($_POST['uid'])){

                $return =   $adminM->addAdminUser($post);
            }else{

                $return =   $adminM->upAdminUser($post, array('uid' => $_POST['uid']));

                if ($return['id'] && $_POST['uid'] == $_SESSION['auid']) {

                    if ($post['username'] != $adminuser['username'] || !empty($post['password'])
                        || $post['name'] != $adminuser['name'] || $post['m_id'] != $adminuser['m_id']
                        || $post['isdid'] != $adminuser['isdid'] || $post['is_crm'] != $adminuser['is_crm']
                        || $post['control_login'] != $adminuser['control_login']
                        || $post['index_lookstatistc'] != $adminuser['index_lookstatistc']) {

                        unset($_SESSION['authcode']);
                        unset($_SESSION['auid']);
                        unset($_SESSION['ausername']);
                        unset($_SESSION['ashell']);

                        $this->admin_json(0, '管理员(ID:' . $_POST['uid'] . ')修改成功,请重新登录！');
                    }
                }
            }
            if ($return['errcode'] == '9'){

                $this->admin_json(0, $return['msg']);
            }else{

                $this->render_json(1, $return['msg']);
            }
        }
    }

    //获取LOGO（后台生成LOGO或预览LOGO调用）
    function adminLogoHb_action()
    {
        $whbM = $this->MODEL('whb');
        if ($_POST['out']) {
            $data = array('text' => $_POST['name'], 'hb' => $_POST['hb'], 'dir' => 'adminuser');
            $data['out'] = 1;
            ob_start();
            $whbM->getLogoHb($data);
            $pic = ob_get_contents();
            ob_end_clean();
            if ($pic) {
                $this->render_json(0, '', $pic);
            } else {
                $this->render_json(1, '生成LOGO出错');
            }
        } else {
            $data = array('text' => $_GET['name'], 'hb' => $_GET['hb']);
            $whbM->getLogoHb($data);
        }
    }

    /**
     * 职业形象照设置
     */
    function setLogo_action()
    {
        if ($_POST) {
            $adminM = $this->MODEL('admin');
            $result = $adminM->upInfo(array('photo' => $_POST['logo']), array('uid' => $_POST['uid']));
            if ($result) {
                $this->admin_json(0, '职业形象照设置成功');
            } else {
                $this->render_json(1, '职业形象照设置失败');
            }
        }
    }

    function upPhoto_action()
    {
        $uid = intval($_POST['uid']);
        if (!empty($_FILES)) {
            if ($_FILES['file']['tmp_name']) {
                $upArrphoto = array(
                    'file' => $_FILES['file'],
                    'dir' => 'adminuser'
                );
            }
            $uploadM = $this->MODEL('upload');
            if (!empty($upArrphoto)) {
                $picphoto = $uploadM->newUpload($upArrphoto);
            }
            if (!empty($picphoto['msg'])) {
                $this->render_json(3, $picphoto['msg']);
            }
            if (!empty($picphoto['picurl'])) {
                $photo = $picphoto['picurl'];
                //更新
                $adminM = $this->MODEL('admin');
                $post = array('photo' => $photo);
                $where = array('uid' => $uid);
                $return = $adminM->upInfo($post, $where);
                if ($return) {
                    $this->admin_json(0, '职业形象照(CRM管理员UID: ' . $uid . ')更新成功', array('url' => checkpic($photo)));
                } else {
                    $this->render_json(4, '职业形象照更新失败');
                }
            }
            $this->render_json(2, '请上传文件');
        }
        $this->render_json(1, '请上传文件');
    }
}