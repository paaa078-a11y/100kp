<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2022 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class company_controller extends adminCommon
{

    function index_action()
    {

        if (!empty($_POST)) {

            $ComM   =   $this->MODEL('company');
            $where  =   $mwhere =   $mUids  =   array();
            $page   =   $_POST['page'];
            $limit  =    $_POST['limit'];
			$where['clue'] =1;
            if ($_POST['keyword'] != 'undefined') {

                $keyword            =   $this->stringfilter($_POST['keyword']);
                $data['keyword']    =   trim($_POST['keyword']);
            }
            if ($keyword) {
                $where['name']               = array('like',$keyword);
                $where['linktel']            = array('like',$keyword,'OR');
            }
            if (isset($_POST['r_status'])) {

                $status             =   intval($_POST['r_status']);
                if ($status == 2) {

                    $where['r_status']  =   2;
                } else {

                    $where['r_status']  =   $status == 4 ? 0 : $status;
                }
            }

            if ($_POST['rating']) {

                $where['rating']        =   $_POST['rating'];
            }
            if ($_POST['source']) {

                $mwhere['source']       =   $_POST['source'];
            }

            $toDay      =   strtotime(date('Y-m-d'));
            if ($_POST['time']) {

                $time   =   intval($_POST['time']);
                if ($time == 5) {

                    $where['PHPYUNBTWSTART_A']  =   '';
                    $where['vipetime'][]        =   array('>', '0', 'AND');
                    $where['vipetime'][]        =   array('<', $toDay, 'AND');
                    $where['PHPYUNBTWEND_A']    =   '';
                } else {

                    if ($time == 1) {
                        $num = '+7 day';
                    } elseif ($time == 2) {
                        $num = '+1 month';
                    } elseif ($time == 3) {
                        $num = '+6 month';
                    } elseif ($time == 4) {
                        $num = '+1 year';
                    }
                    $where['PHPYUNBTWSTART_B']  =   '';
                    $where['vipetime'][]        =   array('>', time(), 'AND');
                    $where['vipetime'][]        =   array('<', strtotime($num), 'AND');
                    $where['PHPYUNBTWEND_B']    =   '';
                }
            }
            if ($_POST['reg_time']) {
                $time   =   intval($_POST['reg_time']);

                if ($time == 1) {// 今日
                    $start = $toDay;

                } else if($time == 2) {// 本周
                    $start = strtotime(date("Y-m-d 00:00:00",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))));
                } else if($time == 3) {// 本月
                    $month = date("m");
                    // if ($month < 10) {
                    //     $month = '0' . $month;
                    // }
                    $start = strtotime(date("Y") . '-' . $month . '-01 00:00:00');

                }

                $mwhere['PHPYUNBTWSTART_A']  =   '';
                $mwhere['reg_date'][]        =   array('>=', $start, 'AND');
                $mwhere['reg_date'][]        =   array('<=', time(), 'AND');
                $mwhere['PHPYUNBTWEND_A']    =   '';
            }
            if (!empty($mwhere)) {

                $UserinfoM  =   $this->MODEL('userinfo');
                $uidList    =   $UserinfoM->getList($mwhere, array('field' => '`uid`'));
                if (!empty($uidList)) {
                    foreach ($uidList as $uv) {

                        $mUids[]    =   $uv['uid'];
                    }
                } else {

                    $mUids  =   array(0);
                }
                $where['uid'][] =   array('in', pylode(',', $mUids));
            }

            if ($_POST['rec']) {

                $rec = intval($_POST['rec']);

                if ($rec == 1) {

                    $where['rec']       =   '1';
                    $where['hottime']   =   array('>', time());
                } elseif ($rec == 2) {

                    $where['rec']       =   '0';
                } elseif ($rec == 3) {

                    $where['rec']       =   '1';
                    $where['hottime']   =   array('<', time());
                }
            }
            $where['orderby']   =   'uid,desc';

            if ($page) {

                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =   array($pagenav, $limit);
            } else {

                $where['limit'] =   $limit;
            }

            $rows               =   $ComM->getList($where, array('utype' => 'admin', 'logo' => 1, 'url' => 1));
            $data['list']       =    count($rows['list']) ? $rows['list'] : array();
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $this->yuntpl(array('wapadmin/user/company/company'));
    }

    function statis_action()
    {
        $regtime = array('1' => '今日', '2' => '本周', '3' => '本月');
        $edtime = array('1' => '7天内', '2' => '一个月内', '3' => '半年内', '4' => '一年内', '5' => '已到期');
        $statusarr = array(array('id' => 1, 'name' => '已审核'), array('id' => 2, 'name' => '已锁定'), array('id' => 3, 'name' => '未通过'), array('id' => 4, 'name' => '未审核'));
        $isrecarr = array(array('id' => 1, 'name' => '是'), array('id' => 2, 'name' => '否'), array('id' => 3, 'name' => '已到期'));
        foreach ($edtime as $k => $v) {
            $edtimearr[] = array('id' => $k, 'name' => $v);
        }
        foreach ($regtime as $k => $v) {
            $regtimearr[] = array('id' => $k, 'name' => $v);
        }

        include(CONFIG_PATH . 'db.data.php');
        $source = $arr_data['source'];
        foreach ($source as $k => $v) {
            $sourcearr[] = array('id' => $k, "name" => $v);
        }

        $ratingM = $this->MODEL('rating');

        $rating = $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));

        if (!empty($rating)) {

            $ratingarr = array();

            foreach ($rating as $k => $v) {

                $ratingarr[] = array("id" => $v['id'], "name" => $v['name']);

            }
        }

        //顾问
        $adminM = $this->MODEL('admin');
        if($this->config['did']>0){
            $gwhere['did'] = $this->config['did'];
        }
        $gwhere['is_crm'] =	'1';
        $gwInfo = $adminM->getList($gwhere);
        $gwArr = array();
        foreach ($gwInfo as $v) {
            $gwArr[] = array('id' => $v['uid'], 'name' => $v['name']);
        }
        unset($gwInfo);
        $data['gwinfo'] = $gwArr;
        $data['source'] = $source;
        $data['ratingarr'] = $ratingarr;
        $data['sourcearr'] = $sourcearr;
        $data['regtimearr'] = $regtimearr;
        $data['edtimearr'] = $edtimearr;
        $data['statusarr'] = $statusarr;
        $data['isrecarr'] = $isrecarr;
        header('content-type:application/json; charset=utf-8');
        echo json_encode($data);
        die;
    }
    function savefact_action(){
        
        $companyM =   $this->MODEL('company');
        
        $_POST = $this->post_trim($_POST);
        
        if (!$_POST['uid']){
            $this->render_json('1','参数错误，请重试！');
        }
        
        $newpic = array();

        if ($_FILES) {

            $fileArray = array();

            foreach ($_FILES['newpic'] as $nk => $nv) {
                foreach ($nv as $nkk => $nvv) {
                    $fileArray[$nkk][$nk] = $nvv;
                }
            }

            $UploadM = $this->MODEL('upload');
            
            foreach ($fileArray as $img) {
                
                $upArr = array(
                    'file' => $img,
                    'dir' => 'comfact',
                );
                $result = $UploadM->newUpload($upArr);
                if (!empty($result['msg'])) {
                    $this->render_json(8, $result['msg']);
                } else if (!empty($result['picurl'])) {
                    $newpic[] = $result['picurl'];
                }
                
            }
        }

        if(!empty($newpic)){
            foreach ($newpic as $v) {
                $factV = array(
                    'uid'=>$_POST['uid'],
                    'picurl'=>$v,
                    'ctime'=>time()
                );
                $companyM->addFactPic($factV);
            }
        }
        
        if(!empty($_POST['fact_delid'])){
            $companyM->delFactPic(array('id'=>array('in',pylode(',',$_POST['fact_delid']))));
        }

        $companyM->upInfo($_POST['uid'],array(),array('fact_status'=>$_POST['fact_status']));

        $this->render_json(9, '实地核验保存成功！');
        
    }
	function show_action() {
	    if($_POST){
            $com_id = $_POST['com_id'];

            if (!$com_id) {
                $this->render_json(1, '参数错误');
            }

            $jobM = $this->MODEL('job');
            $companyM = $this->MODEL('company');
            $downresumeM = $this->MODEL('downresume');
            $comaM = $this->MODEL('companyaccount');

            $returnData['job_num'] = $jobM->getJobNum(array('com_id' => $com_id)); // 职位数
            $returnData['invite_num'] = $jobM->getYqmsNum(array('fid' => $com_id)); // 邀请面试数
            $returnData['sqnum'] = $jobM->getSqJobNum(array('com_id' => $com_id)); // 收到简历
            $returnData['downresume_num'] = $downresumeM->getDownNum(array('comid' => $com_id, 'usertype' => 2)); // 下载简历数
            $returnData['child_account_num'] = $comaM->getNum(array('comid' => $com_id));

            $info = $companyM->getInfo($com_id, array('crm' => 1, 'auid' => $_SESSION['wuid'], 'logo' => 1, 'utype' => 'wapadmin'));
            $info['show'] = $companyM->getCompanyShowList(array('uid' => $com_id, 'orderby' => 'sort,desc'), array('field'=>'`title`,`id`,`picurl`'));

            $returnData['info'] = $info;

            include(CONFIG_PATH.'db.data.php');

            $source             =   $arr_data['source'];
            $returnData['source'] = $source;

            $ratingM        =   $this -> MODEL('rating');
            $returnData['rating_list']    =   $ratingM -> getList(array( 'category' => '1'), array('field'=>'`id`,`name`'));

            $returnData['auid']   =   $_SESSION['wuid'];

            $this->render_json(0, 'ok', $returnData);
        }
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $isOutcall = $appkey && $appsecret;
        $this->yunset('is_outcall', $isOutcall);// 是否配置外呼
        $this->yuntpl(array('wapadmin/user/company/company_show'));
    }

    /**
     * @desc 后台企业列表  --  修改会员等级  返回当前会员套餐数据
     */
    function getstatis_action(){

        $statisM    =   $this -> MODEL('statis');

        $ComM		=   $this -> MODEL('company');

        if($_POST['uid']){

            $uid	=	intval($_POST['uid']);

            $rating	=	$statisM -> getInfo($uid, array('usertype'=>'2'));

            $hotjob	=	$ComM -> getHotJob(array('uid'=>$uid));

            if($hotjob['time_start']<=time() && $hotjob['time_end']>=time()){
                $rating['hotjob']     =  1;
            }else{
                $rating['hotjob']     =  2;
            }

            if($rating['vip_etime']>0){

                $rating['vipetime']     =   date('Y-m-d',$rating['vip_etime']);

            }else{

                $rating['vipetime']     =   '不限';
            }
            $this->render_json(0, 'ok', $rating);
        }
    }

    /**
     * @desc 修改操作
     */
    function edit_action(){
        if ($_POST && $_POST['id']) {
            $uid            =   intval($_POST['id']);
            if (!$uid) {
                $this->render_json(1, '参数错误');
            }

            $ComM           =   $this -> MODEL('company');
            $row            =   $ComM ->getInfo($uid, array('edit'=>1));

            if(!empty($row['content'])){
                $row['content_t']  =  strip_tags($row['content']);
            }

            $cacheM    =  $this->MODEL('cache');
            $cache     =  $cacheM->GetCache('com');
            //福利缓存数据
            if(is_array($cache['comdata']['job_welfare'])){
                foreach($cache['comdata']['job_welfare'] as $k=>$v){
                    $welfareData[]	=	$cache['comclass_name'][$v];
                    $welfaresy[]	=	array('name'=>$cache['comclass_name'][$v],'ischecked'=>0);
                }
            }
            $welfareArr	 =	array_unique(array_merge($row['arraywelfare'],$welfareData));

            $welfareAll  =  $w  =  array();

            foreach($welfareArr as $k=>$v){
                $w['name']  =  $v;
                if(in_array($v,$row['arraywelfare'])){
                    $w['ischecked']	 =	1;
                }else{
                    $w['ischecked']	 =	0;
                }
                $welfareAll[]  =  $w;
            }
            if($welfareAll){
                $row['welfareAll']  =  $welfareAll;
            }else{
                $row['welfareAll']  =  $welfaresy;
            }

            $UserinfoM      =   $this -> MODEL('userinfo');
            $com_info       =   $UserinfoM -> getInfo(array('uid'=>$uid));

            $returnData = array('com_info' => $com_info, 'row' => $row, '');
            $this->render_json(0, 'ok', $returnData);
        }

        $this->yuntpl(array('wapadmin/user/company/company_edit'));
    }

    /**
     * @desc   后台企业列表 --  修改 -- 基本资料 -- 提交表单
     */
    function comeditsave_action(){
        $_POST = $this->post_trim($_POST);

        $companyM	=	$this->MODEL('company');

        if($_FILES['file']['tmp_name']){
            $upArr    =  array(
                'file'  =>  $_FILES['file'],
                'dir'   =>  'company'
            );
        }

        $uploadM	=	$this -> MODEL('upload');

        if(!empty($upArr)){
            $pic	=	$uploadM->newUpload($upArr);
        }

        if(!empty($pic['picurl'])){
            $picture	=	$pic['picurl'];
        }

        $comData   =  array(
            'name' 			=> 	$_POST['name'],
            'shortname' 	=> 	$_POST['shortname'],
            'hy' 			=> 	$_POST['hy'],
            'pr' 			=> 	$_POST['pr'],
            'mun' 			=> 	$_POST['mun'],
            'provinceid' 	=>	$_POST['provinceid'],
            'cityid' 		=> 	$_POST['cityid'],
            'three_cityid' 	=> 	$_POST['three_cityid'],
            'address' 		=>	$_POST['address'],
            'linkman'		=> 	$_POST['linkman'],
            'linktel'		=>	$_POST['moblie'],
            'linkphone'		=> 	$_POST['linkphone'],
            'linkmail' 		=> 	$_POST['email'],
            'sdate' 		=> 	$_POST['sdate'],
            'moneytype' 	=> 	$_POST['moneytype'],
            'money' 		=>	$_POST['money'],
            'linkjob' 		=> 	$_POST['linkjob'],
            'linkqq' 		=> 	$_POST['linkqq'],
            'website' 		=> 	$_POST['website'],
            'busstops' 		=> 	$_POST['busstops'],
            'infostatus' 	=> 	$_POST['infostatus'],
            'welfare' 		=> 	$_POST['welfare'],
            'r_status' 		=> 	$_POST['r_status'],
            'lastupdate'	=>  time(),
            'content'		=> 	str_replace(array('&amp;','background-color:#ffffff','background-color:#fff','white-space:nowrap;'),array('&','background-color:','background-color:','white-space:'),$_POST['content']),
            'admin_remark'  =>  $_POST['admin_remark'],
            'comqcode'		=>	$picture,
        );

        $row	=	$companyM -> getInfo($_POST['uid'], array('edit'=>1));

        if($row['comqcode'] && $picture==""){
            $comData['comqcode']	=	$row['comqcode'];
        }

        //	处理账号信息
        $mData      =   array(
            'email'         =>  $_POST['email'],
            'moblie'        =>  $_POST['moblie'],
            'address'       =>  trim($_POST['address']),
        );

        $return    =  $companyM->setCompany(array('uid'=>intval($_POST['uid'])),array('mData'=>$mData,'comData'=>$comData,/*'sData'=>$sData,*/'utype'=>'admin'));

        delfiledir('../data/upload/tel/'.$_POST['uid']);

        if ($return['errcode'] == 8){
            $this->render_json(1, $return['msg']);
        }else{
            $this->render_json(0, $return['msg']);
        }
    }

    function company_log_action()
    {
        if ($_POST) {

            $TongjiM    =   $this->MODEL('tongji');

            $where      =   array();
            $page       =   $_POST['page'];
            $limit      =   $_POST['limit'];

            $where['uid']       =   $_POST['uid'];
            $where['usertype']  =   2;

            if ($page) {

                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =   array($pagenav, $limit);
            } else {

                $where['limit'] =   $limit;
            }
            $where['orderby']   =   array('ctime,desc');

            $List   =   $TongjiM->tjtotal('member_log', $where);

            if (is_array($List)) {
                foreach ($List as $k => $v) {
                    if ($v['ctime']) {
                        $List[$k]['ctime_n'] = date('Y-m-d H:i:s', $v['ctime']);
                    }
                }
            }
            $data['list'] = $List;
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $this->yuntpl(array('wapadmin/user/company/company_log'));
    }

    function info_action(){
        if ($_POST){
            $ComM    =   $this -> MODEL('company');
            $where['uid'] = $_POST['uid'];
            $com = $ComM->getInfo($where['uid']);
            $data['com'] = $com;
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
    }

    /**
     * @desc  查询会员审核状态原因
     */
    function lockinfo_action(){
        $userinfoM  =  $this -> MODEL('userinfo');

        $member     =  $userinfoM -> getInfo(array('uid'=>$_POST['uid']),array('field'=>'lock_info'));

        echo trim($member['lock_info']);die;
    }

    /**
     * @desc   后台企业列表 -- 会员审核
     */
    function status_action(){

        $userinfoM  =  $this -> MODEL('userinfo');

        $post       =  array(
            'status'     =>  intval($_POST['status']),
            'lock_info'  =>  trim($_POST['statusbody'])
        );

        $uids       =   @explode(',', $_POST['uid']);

        $return     =   $userinfoM -> status(array('uid' => array('in', pylode(',', $uids)),'usertype'=>2), array('post' => $post));

        if($return['errcode']==9){
            $this->render_json(0, $return['msg']);
        }else{
            $this->render_json(1, $return['msg']);
        }
    }

    // 企业暂停
    function suspend_action()
    {

        $userinfoM  =   $this->MODEL('userinfo');

        $res = $this->MODEL('company')->jugdeSuspend(array('uid'=>$_POST['uid']));

        if ($res['errcode'] != 9) {
            $this->render_json(1, $res['msg']);
        }
        
        $post   =   array('status' => 4);
        $uid    =   intval($_POST['uid']);
        $return =   $userinfoM->status(array('uid' => $uid, 'usertype' => 2), array('post' => $post));

        $this->MODEL('log')->addAdminLog($return['msg']);

        if ($return['errcode'] == 9) {

            $this->MODEL('company')->setComStaticSub($uid);
            $this->render_json(0, $return['msg']);
        } else {

            $this->render_json(1, $return['msg']);
        }
    }

    // 解除暂停
    function setupcom_action(){

        $companyM  =  $this -> MODEL('company');

        $data = array(
            'uid'       =>  intval($_POST['uid']),
            'addzttime' =>  $_POST['addzttime'],
            'from'      =>  'wapadmin'
        );

        $res = $companyM->setupcom($data);

        $error = $res['errcode']==9?0:1;

        $this->render_json($error,$res['msg']);
    }

    /**
     * @desc 后台企业列表  --  修改会员等级  -- 弹出框  --选择会员等级返回套餐时间
     */
    function getrating_action(){

        $ratingM        =   $this -> MODEL('rating');

        if($_POST['id']){

            $id         =   intval($_POST['id']);

            $uid		=   intval($_POST['uid']);

            $rating	    =   $ratingM -> changeRatingInfo($id,$uid);

            if($rating['vip_etime']>0){

                $rating['oldetime'] =   $rating['vip_etime'];
                $rating['vip_etime_n'] =   date('Y-m-d',$rating['vip_etime']);

            }else{

                $rating['oldetime'] =   0;
                $rating['vip_etime_n'] =   '不限';
            }

            if($rating['max_time'] > 0){
                $rating['max_time'] = $rating['max_time'];
                $rating['max_time_n'] = date('Y-m-d',$rating['max_time']);
            }else{
                $rating['max_time'] = 0;
                $rating['max_time_n'] = '不限';
            }
            echo json_encode($rating);
        }
    }

    /**
     * @desc 后台企业列表  --  修改会员等级  -- 弹出框  --确认修改会员等级
     * 暂未用到
     */
    function uprating_action()
    {
        $rid = intval($_POST['rating']);
        $uid = intval($_POST['ratuid']);

        $ratingM        =   $this->MODEL('rating');
        $rating_info    =   $ratingM->getInfo(array('id' => $rid));

        if (empty($rid)) {
            $this->render_json(1, '请选择会员等级！');
        } else if ($uid) {

            $statisM    =   $this->MODEL('statis');
            $statis     =   $statisM->getInfo($uid, array('usertype' => 2));

            $sonM		=	$this->MODEL('companyaccount');
            $sonList    =   $sonM -> getList(array('comid' => $uid, 'status' => 1), array('field' => '`uid`'));

            $companyM   =   $this->MODEL('company');

            if($rating_info['service_time']==0){
                $_POST['vip_etime']=0;
            }else{
                $_POST['vip_etime']=strtotime($_POST['vipetime']);
            }

            $data		=   array();

            $sData		=	array('rating','rating_name','integral','vip_etime','job_num','breakjob_num','down_resume','invite_resume','zph_num','top_num','urgent_num','rec_num','sons_num','chat_num','spview_num');

            $msg		=	array();

            foreach ($_POST as $key => $value) {

                if(in_array($key, $sData)){

                    $value		=	$value ? $value : 0;

                    $data[$key] =   $value;

                    if($key == 'integral' && $value != $statis[$key]){

                        $msg[]	=	" 会员".$this->config['integral_pricename']."：".$statis[$key]." -> ".$value;
                    }else if($key == 'vip_etime' && $value != $statis[$key]){

                        $vEtime	=	$value ? date('Y-m-d', $value) : '不限';
                        $msg[]	=	" 会员到期时间：".$statis['vip_etime_n']." -> ".$vEtime;
                    }else if($key == 'job_num' && $value != $statis[$key]){

                        $msg[]	=	" 发布职位数：".$statis[$key]." -> ".$value;
                    }else if($key == 'breakjob_num' && $value != $statis[$key]){

                        $msg[]	=	" 刷新职位数：".$statis[$key]." -> ".$value;
                    }else if($key == 'down_resume' && $value != $statis[$key]){

                        $msg[]	=	" 下载简历数：".$statis[$key]." -> ".$value;
                    }else if($key == 'invite_resume' && $value != $statis[$key]){

                        $msg[]	=	" 邀请面试数：".$statis[$key]." -> ".$value;
                    }else if($key == 'zph_num' && $value != $statis[$key]){

                        $msg[]	=	" 招聘会报名：".$statis[$key]." -> ".$value;
                    }else if($key == 'top_num' && $value != $statis[$key]){

                        $msg[]	=	" 职位置顶数：".$statis[$key]." -> ".$value;
                    }else if($key == 'urgent_num' && $value != $statis[$key]){

                        $msg[]	=	" 紧急招聘数：".$statis[$key]." -> ".$value;
                    }else if($key == 'rec_num' && $value != $statis[$key]){

                        $msg[]	=	" 职位推荐数：".$statis[$key]." -> ".$value;
                    }else if($key == 'sons_num' && $value != $statis[$key]){

                        $msg[]	=	" 子账户数：".$statis[$key]." -> ".$value;
                    }else if($key == 'chat_num' && $value != $statis[$key]){

                        $msg[]	=	" 直聊对象数：".$statis[$key]." -> ".$value;
                    }else if($key == 'spview_num' && $value != $statis[$key]){

                        $msg[]	=	" 视频面试数：".$statis[$key]." -> ".$value;
                    }
                }
            }

            if(!empty($msg)){
                $msgContent	=	@implode('，', $msg);
            }

            $data['rating_type']	=	$rating_info['type'];

            $data['vip_stime']		=	$statis['vip_stime'] ? $statis['vip_stime'] : time();

            if (is_array($sonList) && ! empty($sonList)) {

                $spids  =   array();

                foreach ($sonList as $v) {
                    $spids[]    =   $v['uid'];
                }

                if ($data['rating_type'] == 2) { // 时间会员，变更套餐数据（每日上限）

                    $sonData    =   $data;
                } else { // 套餐会员

                    if ($statis['rating'] == $value['rating'] && isVip($statis['vip_etime'])) {

                        $sonData    =   array();
                    } else {

                        $sonData    =   array(

                            'job_num'       =>  0,
                            'breakjob_num'  =>  0,
                            'down_resume'   =>  0,
                            'invite_resume' =>  0,
                            'zph_num'       =>  0,
                            'top_num'       =>  0,
                            'rec_num'       =>  0,
                            'urgent_num'    =>  0,
                            'sons_num'      =>  0,
                            'chat_num'      =>  0,
                            'spview_num'    =>  0,
                            'rating_name'   =>  $data['rating_name'],
                            'rating_type'   =>  $data['rating_type'],
                            'vip_etime'     =>  $data['vip_etime'],
                            'vip_stime'     =>  $data['vip_stime'],
                            'rating'        =>  $data['rating']
                        );
                    }
                }
            }

            $company	=	$companyM->getInfo($uid);

            if ($_POST['hotjob']) {
                if (empty($company['name'])) {

                    $this->render_json(1, '请先完善企业资料！');
                }
            }

            if ($statis['rating'] != $_POST['rating'] || ($statis['rating'] == $_POST['rating'] && !isVip($statis['vip_etime']) && (int) $_POST['addday'] == 0)) {

                $data['vip_stime']  =   time();

                $statisM -> upInfo($data, array('uid' => $uid, 'usertype' => 2, 'adminedit' => '1', 'info' => $rating_info));

                $sName		=	!isVip($statis['vip_etime']) ? '过期会员' : $statis['rating_name'];

                $mContent	=	'会员等级：'.$sName.' -> '.$rating_info['name'];

                $msgContent	=	$msgContent ? $mContent.'，'.$msgContent : $mContent;

            } else {

                unset($data['rating']);

                $statisM -> upInfo($data, array('uid' => $uid, 'usertype' => 2));

            }

            if (!empty($spids) && !empty($sonData)) {

                $this->obj->update_once('company_statis', $sonData, array('uid' => array('in', pylode(',', $spids))));
            }

            if ($_POST['hotjob']) {

                $hotData    =   array(
                    'uid'           =>  $uid,
                    'username'      =>  $company['name'],
                    'rating'        =>  $data['rating_name'],
                    'time_start'    =>  $data['vip_stime'] ? $data['vip_stime'] : time(),
                    'time_end'      =>  $data['vip_etime'] == 0 ? strtotime("2029-12-30") : $data['vip_etime'],
                    'rating_id'     =>  $data['rating'],
                    'did'           =>  $company['did']
                );
                $hotjob     =   $companyM->getHotJob(array('uid' => $uid));
                if(!$hotjob['hot_pic']){

                    $hotData['hot_pic'] = $company['logo'];
                }
                if (!empty($hotjob)) {

                    $hotData['sort']        =   $hotjob['sort'];
                    $companyM -> upHotJob($uid, $hotData);
                } else {

                    $hotData['uid']         =   $uid;
                    $hotData['username']    =   $company['name'];
                    $hotData['did']         =   $company['did'];
                    $companyM->addHotJob($hotData);
                }
            } else {

                if ($company['rec'] == 1) {

                    $companyM->delHotJob($uid);
                }
            }

            $companyData = array(

                'rating'        =>  $_POST['rating'],
                'rating_name'   =>  $_POST['rating_name'],
                'vipetime'      =>  $_POST['vip_etime']
            );

            if ($data['vip_stime']) {
                $companyData['vipstime']    =   $data['vip_stime'];
            }

            if ($_POST['hotjob']) {

                $companyData['hotstart']    =   $data['vip_stime'];
                $companyData['hottime']     =   $data['vip_etime'] == 0 ? strtotime("2029-12-30") : $data['vip_etime'];
            } else {

                $companyData['hotstart']    =   0;
                $companyData['hottime']     =   0;
            }

            $companyM->upInfo($uid, '', $companyData);

            $logM	=	$this->MODEL('log');

            if($msgContent){

                $msgContent	=	'企业(ID:' . $uid . ')修改成功；'.$msgContent;
                $logM -> addAdminLog($msgContent);
            }

            $this->render_json(0, '企业(ID:' . $uid . ')修改成功！');
        } else {
            $this->render_json(1, '非法操作！');
        }
    }

    /**
     * @desc 后台企业列表 --  修改 -- 会员套餐 -- 提交表单
     */
    function saveRating_action(){

        if($_POST){

            $cuid		=	intval($_POST['uid']);

            $comM		=	$this->MODEL('company');

            $sData		=	array(

                'rating'            =>  $_POST['ratingid'],
                'vip_etime'         =>  $_POST['vip_etime'] ? strtotime($_POST['vip_etime']) : 0,
                'job_num'           =>  intval($_POST['job_num']),
                'breakjob_num'      =>  intval($_POST['breakjob_num']),
                'down_resume'       =>  intval($_POST['down_resume']),
                'invite_resume'     =>  intval($_POST['invite_resume']),

                'zph_num'           =>  intval($_POST['zph_num']),
                'top_num'			=>	intval($_POST['top_num']),
                'urgent_num'		=>	intval($_POST['urgent_num']),
                'rec_num'			=>	intval($_POST['rec_num']),

                'sons_num'			=>	intval($_POST['sons_num']),
                'chat_num'			=>	intval($_POST['chat_num']),
                'spview_num'		=>	intval($_POST['spview_num']),

                'suspend_num'       =>  intval($_POST['suspend_num']),
                'group_num'       =>  intval($_POST['group_num']),
                'max_time'          =>  $_POST['max_time'] ? strtotime($_POST['max_time']) : 0,
            );

            $return		=	$comM -> setStatisInfo($cuid, $sData);

            if ($return['errcode'] == 8){
                $this->render_json(8, $return['msg']);
            }else{
                $this->render_json($return['errcode'], $return['msg']);
            }
        }

    }

    /**
     * @desc 分配顾问
     */
    function checkguwen_action(){
        $ComM = $this->MODEL('company');
        $adminM = $this->MODEL('admin');
        $gid = intval($_POST['gid']);
        $comid = intval($_POST['comid']);
        $crmUser = $adminM->getAdminUser(array( 'uid' => $gid ));
        if (!is_array($crmUser)) {
            $this->render_json(1, '请选择分配顾问！');
        }
        $gData = array('crm_uid' => $gid, 'crm_time' => time(), 'crm_source' => 5);
        $whereData = array('crm_uid' => $gid, 'uid' => $comid);
        $arr = $ComM->setComGw($gData, $whereData);
        if ($arr['errcode'] == 9) {
            $this->render_json(0, $arr['msg']);
        } else {
            $this->render_json(1, $arr['msg']);
        }
    }
}
?>