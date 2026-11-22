<?php
/*
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class spview_controller extends adminCommon
{
    function index_action(){
        if($_POST['keyword']){
            $keytype = intval($_POST['type']);
            $keyword = trim($_POST['keyword']);
            if ($keytype == 1){
                $where['uid'] = $keyword;
            }elseif ($keytype == 2){
                $cwhere['name'] = array('like',$keyword);
                $companyM = $this->MODEL('company');
                $com = $companyM->getList($cwhere,array('field'=>'`uid`'));
                if (!empty($com['list'])){
                    foreach ($com['list'] as $v){
                        $cuid[] = $v['uid'];
                    }
                    $where['uid'] = array('in',pylode(',', $cuid));
                }
            }
        }
        if($_POST['status']){
            $where['status'] = intval($_POST['status']) == 3 ? 0 : $_POST['status'];
        }
        //排序
        if($_POST['order']){
            $where['orderby'] = $_POST['t'].','.$_POST['order'];
        }else{
            $where['orderby'] = 'id';
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('spview', $where, $page, array('limit' => $pageSize));
        if($pages['total'] > 0){
            $where['limit'] = $pages['limit'];
            $spviewM = $this->MODEL('spview');
            $rows = $spviewM->getList($where,array('utype' => 'admin'));
        }
        $data['list'] = $rows ? $rows : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $data['preview_url'] = $this->config['sy_weburl'] . '/index.php?m=spview&c=show&look=admin&id=';
        $this->render_json(0,'ok', $data);
    }

    function add_action(){
        //提取分站内容
        $cacheM = $this->MODEL('cache');
        $user =	$cacheM->GetCache('user');
        $exparr = array();
        foreach ($user['userdata']['user_word'] as $v) {
            $exparr[] = array('value' => $v, 'label' => $user['userclass_name'][$v]);
        }
        $rt['exparr'] = $exparr;
        $eduarr = array();
        foreach ($user['userdata']['user_edu'] as $v) {
            $eduarr[] = array('value' => $v, 'label' => $user['userclass_name'][$v]);
        }
        $rt['eduarr'] = $eduarr;
        $sexarr = array();
        foreach ($user['user_sex'] as $k => $v) {
            $sexarr[] = array('value' => $k . '', 'label' => $v);
        }
        $rt['sexarr'] = $sexarr;
        $jobM = $this->MODEL('job');
        $jobwhere = array(
            'uid' => $_POST['uid'],
            'state' => 1,
            'status' => 0,
            'r_status' => 1
        );
        $List = $jobM->getList($jobwhere,array('field'=>'`name`,`id`'));
        $jobids = array();
        foreach ($List['list'] as $key => $value) {
            !in_array($value['id'], $jobids) && array_push($jobids, $value['id']);
            $jobArr[] = array('value' => $value['id'], 'label' => $value['name']);
        }
        // 去除当前非正常招聘中职位
        $jobid_arr = array();
        if (trim($_POST['jobid'])) {
            $jids = explode(',', trim($_POST['jobid']));
            foreach ($jids as $v) {
                in_array($v, $jobids) && array_push($jobid_arr, $v);
            }
        }
        $rt['jobid_arr'] = $jobid_arr;
        $rt['jobarr'] = $jobArr;
        $this->render_json(0, '', $rt);
    }

    function save_action(){
        $_POST = $this->post_trim($_POST);
        $post = array(
            'id' => $_POST['id'],
            'uid' => $_POST['uid'],
            'jobid' => trim(pylode(',', $_POST['jobid_arr'])),
            'starttime' => strtotime(str_replace('.', '-',$_POST['sdate_n'])),
            'exp' => trim($_POST['exp']),
            'edu' => trim($_POST['edu']),
            'sex' => trim(pylode(',', $_POST['sex_arr'])),
            'other' => trim(pylode(',', $_POST['other_arr'])),
            'remark' => trim($_POST['remark']),
            'status' => 1
        );
        $spviewM = $this->MODEL('spview');
        $return = $spviewM->addInfo($post, 'admin');
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    //修改审核状态
    function status_action(){
        $spviewM = $this->MODEL('spview');
        $id = $_POST['pid'];
        if (!$id) {
            $this->render_json(1, '非法操作！');
        }
        $data['status'] = $_POST['status'];
        $data['statusbody'] = trim($_POST['statusbody']);
        $nid = $spviewM->upStatusInfo($id, $where = array(), $data);
        if($nid){
            $msg = array();
            $uids = array();
            $spviews = $spviewM->getList(array('id' => array('in', pylode(',', $id))),array('field'=>'`id`,`uid`,`remark`'));
            foreach ($spviews as $v){
                $uids[] =$v['uid'];
            }
            $noticeM = $this->MODEL('notice');
            $wxM = $this->MODEL('weixin');
            $userinfoM = $this->MODEL('userinfo');
            $member = $userinfoM->getList(array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`email`,`moblie`'));
            foreach ($spviews as $k => $v){
                if ($_POST['status'] == 2) {
                    $statusInfo = '您的视频面试《' . $v['remark'] . '》审核未通过';
                    if ($data['statusbody']) {
                        $statusInfo .= '，原因：' . $data['statusbody'];
                    }
                    $msg[$v['uid']][] = $statusInfo;
                }elseif ($_POST['status'] == 1){
                    $msg[$v['uid']][] = '您的视频面试《' . $v['remark'] . '》审核通过';
                }
                foreach ($member as $mv){
                    $sendData = array();
                    if ($v['uid'] == $mv['uid']) {
                        $wxData['id'] = $v['id'];
                        $res = $wxM->sendWxSpviewStatus($wxData);
                        if (!$res || $this->config['wx_xxtz'] != '1' || !$this->config['wx_mbspmssh']  || $this->config['sy_msg_tz'] == 1){
                            $sendData['type'] = $_POST['status'] == 2 ? 'spmsshwtg' : 'spmsshtg';
                            $sendData['uid'] = $v['uid'];
                            $sendData['email'] = $mv['email'];
                            $sendData['moblie'] = $mv['moblie'];
                            $sendData['remark']	= $v['remark'];
                            $sendData['date'] = date('Y-m-d H:i:s');
                            $sendData['status_info'] = $data['statusbody'];
                            //邮箱短信通知
                            $noticeM->sendEmailType($sendData);
                            $sendData['port'] =	'5';
                            $noticeM->sendSMSType($sendData);
                        }
                    }
                }
            }
            //发送系统通知
            $sysmsgM = $this->MODEL('sysmsg');
            $sysmsgM->addInfo(array('uid' => $uids,'usertype'=>2,'content'=>$msg));
        }
        if ($nid) {
            $this->admin_json(0, "视频面试审核(ID:".$_POST['pid'].")设置成功！");
        } else {
            $this->render_json(1, "设置失败！");
        }
    }

    function del_action(){
        $delID = $_POST['del'];
        $spviewM = $this->MODEL('spview');
        $return	= $spviewM->delSpview($delID);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    function spresume_action(){
        $spviewM = $this -> MODEL('spview');
        $where['sid'] =	$_POST['sid'];
        if(!empty($_POST['keyword'])){
            $typeStr = $_POST['type'];
            $keyStr = $_POST['keyword'];
            if($typeStr == '2'){
                $jobM = $this->MODEL('job');
                $jWhere['name']	= array('like', $keyStr);
                $jobList = $jobM->getList($jWhere, array('field' => '`id`'));
                if (is_array($jobList)) {
                    $jobIds = array();
                    foreach ($jobList['list'] as $v) {
                        $jobIds[] =	$v['id'];
                    }
                }
                $where['jobid']	= array('in', pylode(',', $jobIds));
            }else{
                $resumeM = $this->MODEL('resume');
                $rWhere['name']	= array('like', $keyStr);
                $resuemList	= $resumeM->getResumeList($rWhere, array('field' => '`uid`,`name`'));
                if (is_array($resuemList)) {
                    $uids =	array();
                    foreach ($resuemList as $v) {
                        $uids[]	= $v['uid'];
                    }
                }
                $where['uid'] =	array('in', pylode(',', $uids));
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('spview_subscribe', $where, $page, array('limit' => $pageSize));
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            //limit order 只有在列表查询时才需要
            if($_POST['order']){
                $where['orderby'] = $_POST['t'].','.$_POST['order'];
            }else{
                $where['orderby'] = 'ctime';
            }
            $where['limit'] = $pages['limit'];
            $rows = $spviewM->getSublist($where, array('resume' => 1, 'job' => 1));
        }
        $data['list'] = $rows ? $rows : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }

    function delSub_action(){
        $delID = $_POST['del'];
        $spviewM = $this->MODEL('spview');
        $return = $spviewM->delSub($delID, array(), 'admin');
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
}