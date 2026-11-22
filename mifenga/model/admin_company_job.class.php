<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class admin_company_job_controller extends adminCommon{

    function set_search($CacheList = array())
    {

        include(CONFIG_PATH.'db.data.php');
        $source =   $arr_data['source'];
        $this->yunset('source', $source);

        if (!$CacheList) {

            $cacheM     =   $this->MODEL('cache');
            $CacheList  =   $cacheM->GetCache(array('com', 'job', 'city'));
            $setArr     =   array('comdata' => $CacheList['comdata'], 'comclass_name' => $CacheList['comclass_name'], 'job_name' => $CacheList['job_name'], 'city_name' => $CacheList['city_name']);
            $this->yunset($setArr);
        }

        $comdata        =   $CacheList['comdata'];
        $comclass_name  =   $CacheList['comclass_name'];
        foreach ($comdata['job_edu'] as $k => $v) {
            $edu[$v]    =   $comclass_name[$v];
        }
        foreach ($comdata['job_exp'] as $k => $v) {
            $exp[$v]    =   $comclass_name[$v];
        }

        $ratingM        =   $this->MODEL('rating');
        $rating         =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
        if (!empty($rating)) {
            $ratingarr  =   array();
            foreach ($rating as $k => $v) {
                $ratingarr[$v['id']] = $v['name'];
            }
        }

        $search_list    =   array();
        $search_list[]  =   array('param' => 'state', 'name' => '审核状态', 'value' => array('1' => '已审核', '4' => '未审核', '3' => '未通过', '2' => '已锁定'));
        $search_list[]  =   array('param' => 'status', 'name' => '招聘状态', 'value' => array('1' => '已下架', '2' => '招聘中'));
        $search_list[]  =   array('param' => 'jtype', 'name' => '职位类型', 'value' => array('urgent' => '紧急职位', 'xuanshang' => '置顶职位', 'rec' => '推荐职位'));
        $search_list[]  =   array('param' => 'exp', 'name' => '工作经验', 'value' => $exp);
        $search_list[]  =   array('param' => 'edu', 'name' => '学历要求', 'value' => $edu);
        $search_list[]  =   array('param' => 'source', 'name' => '数据来源', 'value' => $source);
        $search_list[]  =   array('param' => 'adtime', 'name' => '发布时间', 'value' => array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月', '31' => '当月', '-1' => '昨天', '-7' => '本周'));
        $search_list[]  =   array('param' => 'rating', 'name' => '会员等级', 'value' => $ratingarr);
        $search_list[] = array('param' => 'openautho', 'name' => '开放权限', 'value' => array('1'=>'默认','2'=>'开放'));
        $search_list[] = array('param' => 'is_depower', 'name' => '是否降权', 'value' => array('1'=>'是','2'=>'否'));
        $this->yunset('search_list', $search_list);

    }

    function index_action()
    {

        session_start();
        if (isset($_GET['remember_prev']) && $_GET['remember_prev']) {
            foreach ($_SESSION['prev_comjoblist'] as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_SESSION['prev_comjoblist'] = $_GET;

        if (isset($_GET['fromCrmIndex']) && $_GET['fromCrmIndex'] == 1) {
            $comM   =   $this->MODEL('company');
            $coms   =   $comM->getChCompanyList(array('crm_uid' => array('=', intval($_SESSION['auid']))), array('field' => '`uid`'));
            $comuids=   array();
            foreach ($coms as $ck => $cv) {
                $comuids[]  =   $cv['uid'];
            }
            $where['uid']   =   array('in', pylode(',', $comuids));
        }
		if (isset($_GET['openautho'])){
            $where['linkopen'] = intval($_GET['openautho']);
            $urlarr['linkopen'] = intval($_GET['openautho']);
        }
        if (isset($_GET['state']) && !empty($_GET['state'])) {

            $state  =   intval($_GET['state']);
            if ($state == 2) {
                $where['r_status']  =   2;
            } else {
                $where['state']     =   $state == 4 ? 0 : $state;
            }
            $urlarr['state']        =   $state;
        }

        if (isset($_GET['status']) && !empty($_GET['status'])) {

            $status             =   intval($_GET['status']);
            $where['status']    =   $status == 2 ? 0 : $status;
            $urlarr['status']   =   $status;
        }

        if (isset($_GET['jtype']) && !empty($_GET['jtype'])) {

            $jtype  =   trim($_GET['jtype']);

            if ($jtype == 'rec') {

                $where['rec_time']  =   array('>', time());
            } else if ($jtype == 'urgent') {

                $where['urgent_time']   =   array('>', time());
            } else if ($jtype == 'xuanshang') {

                $where['xsdate']    =   array('>', time());
            }
            $urlarr['jtype'] = $jtype;
        }

        if (isset($_GET['edu']) && !empty($_GET['edu'])) {


            $where['edu']   =   $_GET['edu'];
            $urlarr['edu']  =   $_GET['edu'];
        }

        if (isset($_GET['exp']) && !empty($_GET['exp'])) {

            $where['exp'] = $_GET['exp'];
            $urlarr['exp'] = $_GET['exp'];
        }

        if (isset($_GET['source']) && !empty($_GET['source'])) {

            $where['source']    =   intval($_GET['source']);
            $urlarr['source']   =   intval($_GET['source']);
        }

        if (isset($_GET['adtime']) && !empty($_GET['adtime'])) {

            if ($_GET['adtime'] == '1') {

                $where['sdate'] =   array('>', strtotime('today'));
            } else if ($_GET['adtime'] == '31') {

                $month  =   get_this_month(time());
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['sdate'][]           =   array('>=', $month['start_month'], 'AND');
                $where['sdate'][]           =   array('<=', $month['end_month'], 'AND');
                $where['PHPYUNBTWEND_A']    =   '';
            } else if ($_GET['adtime'] == '-1') {

                $sTime  =   mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 86400;
                $eTime  =   mktime(23, 59, 59, date('m'), date('d'), date('Y')) - 86400;
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['sdate'][]           =   array('>=', $sTime, '');
                $where['sdate'][]           =   array('<=', $eTime, '');
                $where['PHPYUNBTWEND_A']    =   '';
            } else if ($_GET['adtime'] == '-7') {

                $sTime  =   strtotime(date('Y-m-d', strtotime("this week Monday", time())));
                $eTime  =   strtotime(date('Y-m-d', strtotime("this week Sunday", time()))) + 24 * 3600 - 1;
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['sdate'][]           =   array('>=', $sTime, '');
                $where['sdate'][]           =   array('<=', $eTime, '');
                $where['PHPYUNBTWEND_A']    =   '';
            } else {

                $where['sdate'] =   array('>', strtotime('-'.intval($_GET['adtime']).' day'));
            }

            $urlarr['adtime']   =   $_GET['adtime'];
        }

        if (isset($_GET['rating']) && !empty($_GET['rating'])){

            $where['rating']    =   $_GET['rating'];
            $urlarr['rating']   =   $_GET['rating'];
        }

        if (isset($_GET['job_class']) && !empty($_GET['job_class'])) {

            $where['PHPYUNBTWSTARTA']   =   '';
            $where['job1']              =   array('findin', $_GET['job_class']);
            $where['job1_son']          =   array('findin', $_GET['job_class'], 'OR');
            $where['job_post']          =   array('findin', $_GET['job_class'], 'OR');
            $where['PHPYUNBTWENDA']     =   '';

            $urlarr['job_class']        =   $_GET['job_class'];
        }

        if (isset($_GET['city_class']) && !empty($_GET['city_class'])) {

            $where['PHPYUNBTWSTARTB']   =   '';
            $where['provinceid']        =   array('findin', $_GET['city_class']);
            $where['cityid']            =   array('findin', $_GET['city_class'], 'OR');
            $where['three_cityid']      =   array('findin', $_GET['city_class'], 'OR');
            $where['PHPYUNBTWENDB']     =   '';

            $urlarr['city_class']       =   $_GET['city_class'];
        }

        if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
            $keywordStr =   trim($_GET['keyword']);
            $typeStr    =   intval($_GET['type']);

            if ($typeStr == 1) {

                $where['com_name']  =   array('like', $keywordStr);
            } elseif ($typeStr == 2) {

                $where['name']  =   array('like', $keywordStr);
            } elseif ($typeStr == 3) {

                $where['id']    =   $keywordStr;
            } elseif ($typeStr == 4) {

                $where['add_ip'] = array('like', $keywordStr);
            }

            $urlarr['type']     =   $typeStr;
            $urlarr['keyword']  =   $keywordStr;
        }

        if (isset($_GET['is_reserve']) && !empty($_GET['is_reserve'])) {

            $where['is_reserve']    =   intval($_GET['is_reserve']);
            $urlarr['is_reserve']   =   intval($_GET['is_reserve']);
        }

        if (isset($_GET['uid']) && !empty($_GET['uid'])) {
            $where['uid']   =   intval($_GET['uid']);
        }

        if (!empty($_GET['is_depower'])) {
            $where['is_depower'] = intval($_GET['is_depower']);
        }

        $urlarr         =   $_GET;
        $urlarr['page'] =   '{{page}}';

        if (isset($urlarr['remember_prev'])) {
            unset($urlarr['remember_prev']);
        }
        $pageurl=   Url($_GET['m'], $urlarr, 'admin');
        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('company_job', $where, $pageurl, $_GET['page']);
        if ($pages['total'] > 0) {
            if (isset($_GET['order']) && !empty($_GET['order'])) {

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   'lastupdate,desc';
            }

            $where['limit'] =   $pages['limit'];

            $JobM   =   $this->MODEL('job');
            $ListJob=   $JobM->getList($where, array('utype' => 'admin', 'cache' => '1', 'isurl' => 'yes', 'reserve' => 1, 'crm_user' => 1));

            unset($where['limit']);

            session_start();
            $_SESSION['jobXls'] =   $where;

            $CacheList  =   $ListJob['cache'];

            $setArr     =   array('rows' => $ListJob['list'], 'cache' => $CacheList, 'comdata' => $CacheList['comdata'], 'comclass_name' => $CacheList['comclass_name'], 'job_name' => $CacheList['job_name'], 'city_name' => $CacheList['city_name']);
            $this->yunset($setArr);
        }

        $this->set_search($CacheList);

        $WhbM   =   $this->MODEL('whb');
        $syJobHb=   $WhbM->getWhbList(array('type' => 1, 'isopen' => '1'));
        $this->yunset('hbNum', count($syJobHb));
        $this->yuntpl(array('admin/admin_company_job'));
    }

    /**
     * 招聘/下架操作
     */
    function checkstate_action()
    {
        if ($_POST['id'] && $_POST['state']) {

            $_POST['state'] =   $_POST['state'] == 2 ? 0 : $_POST['state'];

            $JobM   =   $this->MODEL('job');
            $id     =   intval($_POST['id']);
            $postData['status'] =   intval($_POST['state']);
            $JobM->upInfo($postData, array('id' => $id));

            if ($_POST['state'] == 0){

                $logMsg =   "职位(ID" . $_POST['id'] . ")上架成功";
            }else{

                $logMsg =   "职位(ID" . $_POST['id'] . ")下架成功";
            }

            $this->MODEL('log')->addAdminLog($logMsg);
        }
        echo 1;
    }
    // 	职位置顶
	function xuanshang_action(){

        $id     =   trim($_POST['pid']);

        $data   =   array(

            'top'   =>  intval($_POST['s']),
            'days'  =>  intval($_POST['days'])

        );

        $JobM   =   $this -> MODEL('job');

        $arr    =   $JobM -> addTopJob($id, $data);

        $this  ->  ACT_layer_msg( $arr['msg'],$arr['errcode'],$_SERVER['HTTP_REFERER'],2,1);

	}

	//  职位推荐
	function recommend_action(){

	    $id     =   trim($_POST['pid']);

	    $data   =   array(

	        'rec'   =>  intval($_POST['s']),
	        'days'  =>  intval($_POST['days'])

	    );

	    $JobM   =   $this -> MODEL('job');

	    $arr    =   $JobM -> addRecJob($id, $data);

	    $this  ->  ACT_layer_msg( $arr['msg'],$arr['errcode'],$_SERVER['HTTP_REFERER'],2,1);

	}

	//  职位紧急招聘
	function urgent_action(){

	    $id     =   trim($_POST['pid']);

	    $data   =   array(

	        'urgent'   =>  intval($_POST['s']),
	        'days'     =>  intval($_POST['days'])

	    );

	    $JobM   =   $this -> MODEL('job');

	    $arr    =   $JobM -> addUrgentJob($id, $data);

	    $this  ->  ACT_layer_msg( $arr['msg'],$arr['errcode'],$_SERVER['HTTP_REFERER'],2,1);

	}

	//  职位审核
    function status_action()
    {
        $jobM       =   $this->MODEL('job');

        $statusData = array(

            'state'         =>  intval($_POST['status']),
            'statusbody'    =>  trim($_POST['statusbody']),
            'single'    => isset($_POST['single']) ? $_POST['single'] : '',
            'lock_status'   =>  trim($_POST['lock_status']) // 新增需求 单个审核锁定企业也可以审核 新增lock_status 状态值，批量审核未传该值
        );
        
        $return = $jobM -> statusJob($_POST['pid'], $statusData);
        
        if (isset($_POST['single'])){
            if ($return['errcode'] == 9){
                if($_POST['atype'] == 1){
                    // 仅保存
                    $this->layer_msg($return['msg'],9);
                }else{
                    // 下一个待审核职位
                    $jobM  =  $this->MODEL('job');
                    $row   =  $jobM->getInfo(array('state'=>0,'orderby'=>array('lastupdate,DESC')), array('field'=>'id'));
                    if (!empty($row)){
                        $this->layer_msg($return['msg'],9,0,'index.php?m=admin_company_job&c=jobAudit&id='.$row['id']);
                    }else{
                        $this->layer_msg($return['msg'],9);
                    }
                }
            }else{
                $this->layer_msg($return['msg'],8);
            }
        }else{
            $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
        }
    }

    // 职位审核同步企业审核
    function cjobstatus_action()
    {
        if ($_POST) {
            
            $id         =   intval($_POST['pid']);
            $uid        =   intval($_POST['uid']);
            $status     =   intval($_POST['status']);
            $statusbody =   trim($_POST['statusbody']);
            
            $jobM   =   $this->MODEL('job');
            
            $post   =   array(
                
                'uid'           =>  $uid,
                'state'         =>  $status,
                'statusbody'    =>  $statusbody
            );
            
            $return     =   $jobM -> status($id, $post);
            
            if (isset($_POST['single'])){
                if ($return['errcode'] == 9){
                    if($_POST['atype'] == 1){
                        // 仅保存
                        $this->layer_msg($return['msg'],9);
                    }else{
                        // 下一个待审核职位
                        $jobM  =  $this->MODEL('job');
                        $row   =  $jobM->getInfo(array('state'=>0,'orderby'=>array('lastupdate,DESC')), array('field'=>'id'));
                        if (!empty($row)){
                            $this->layer_msg($return['msg'],9,0,'index.php?m=admin_company_job&c=jobAudit&id='.$row['id']);
                        }else{
                            $this->layer_msg($return['msg'],9);
                        }
                    }
                }else{
                    $this->layer_msg($return['msg'],8);
                }
            }else{
                $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
            }
        }
    }
    
    //  修改浏览量/曝光量
    function upjobhits_action()
    {
        $jobM   =   $this->MODEL('job');
        $return =   $jobM->upJobHits($_POST['pid'], $_POST['jobhits'], $_POST['jobexpoure']);
        $this->ACT_layer_msg($return['msg'], $return['errcode'], "index.php?m=admin_company_job", 2, 1);
    }

    /**
     * @desc 后台 -- 会员 -- 企业 -- 企业管理 / 职位管理 -- 新增  /  修改
     */
	function show_action()
    {

        $cacheM     =   $this->MODEL('cache');
        $options    =   array('job', 'com', 'city', 'hy', 'user');
        $cache      =   $cacheM->GetCache($options);

        foreach ($cache['com_sex'] as $k=> $v){
            if ($v != '男') {
                $cache['com_sexreq'][$k] = $v;
            }
        }
        $this->yunset('cache', $cache);

        $JobM       =   $this->MODEL('job');

        $companyM   =   $this->MODEL('company');
        $addressM   =   $this->MODEL('address');

        if (isset($_GET['id']) && intval($_GET['id']) > 0) {

            $id     =   intval($_GET['id']);
            $info   =   $JobM->getInfo(array('id' => $id), array('add'=>'yes'));

            $this->yunset('show', $info);
            $this->yunset('lasturl', $_SERVER['HTTP_REFERER']);
            $uid    =   $info['uid'];
        }
        if (intval($_GET['uid'])) {

            $uid    =   intval($_GET['uid']);
        }

        $company    =   $companyM->getInfo($uid, array('field' => '`uid`,`r_status`,`linkman`,`linktel`,`linkphone`,`address`,`provinceid`,`cityid`,`three_cityid`,`x`,`y`'));

        $cityStr    =   $company['job_city_one'].$company['job_city_two'].$company['job_city_three'];

        if ($company['linktel']){

            $company['linkmsg'] =   $company['linkman'].' - '.$company['linktel'].' - '.$cityStr.' - '.$company['address'];
        }else if ($company['linkphone']){

            $company['linkmsg'] =   $company['linkman'].' - '.$company['linkphone'].' - '.$cityStr.' - '.$company['address'];
        }else{

            $company['linkmsg'] =   $company['linkman'].' - '.$cityStr.' - '.$company['address'];
        }

        $addressList=   $addressM->getAddressList(array('uid' => $uid));

        $this->yunset(array('company' => $company, 'addressList' => $addressList, 'uid' => $uid));


        if($_POST){

		    $description	=	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'background-color:','background-color:','white-space:'),$_POST['content']);

		    if ($_POST['link_id'] == -1){

		        $provinceid     =   $company['provinceid'];
                $cityid         =   $company['cityid'];
                $three_cityid   =   $company['three_cityid'];

                $x              =   $company['x'];
                $y              =   $company['y'];

            }else if(intval($_POST['link_id']) > 0){

		        $addressInfo    =   $addressM->getAddressInfo(array('id' => $_POST['link_id'], 'uid' => $uid));

                $provinceid     =   $addressInfo['provinceid'];
                $cityid         =   $addressInfo['cityid'];
                $three_cityid   =   $addressInfo['three_cityid'];

                $x              =   $addressInfo['x'];
                $y              =   $addressInfo['y'];
            }

			$post	        =	array(
				'name'			=>	$_POST['name'],

				'job1'          =>  intval($_POST['job1']),
                'job1_son'      =>  intval($_POST['job1_son']),
                'job_post'      =>  intval($_POST['job_post']),

				'provinceid'    =>  intval($provinceid),
                'cityid'        =>  intval($cityid),
				'three_cityid'  =>  intval($three_cityid),

				'x'             =>  $x,
				'y'             =>  $y,

                'link_id'       =>  (int)$_POST['link_id'] ? $_POST['link_id'] : '',
                'is_link'       =>  $_POST['is_link'] ? $_POST['is_link'] : 1,

                'is_message'    =>  $_POST['is_message'] ?  $_POST['is_message'] : 1,
                'is_email'      =>  $_POST['is_email'] ? $_POST['is_email'] : 1,

                'minsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['minsalary']),
                'maxsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['maxsalary']),

                'description'	=>	$description,
                'r_status'      =>	$company['r_status'],
                'hy'            =>  intval($_POST['hy']),
                'number'        =>  intval($_POST['number']),
                'exp'           =>  intval($_POST['exp']),
                'report'        =>  intval($_POST['report']),
                'age'           =>  intval($_POST['age']),
                'sex'           =>  intval($_POST['sex']),
                'edu'           =>  intval($_POST['edu']),
                'is_graduate'   =>  intval($_POST['is_graduate']),
                'marriage'      =>  intval($_POST['marriage']),
                'lang'          =>  trim(pylode(',', $_POST['lang'])),
                'welfare' 		=> 	trim(implode(',', $_POST['welfare'])),

				'state'      	=> 	$company['r_status']==1 ? 1:0,
                'jobhits'       =>  intval($_POST['jobhits']),
                'jobexpoure'    =>  intval($_POST['jobexpoure']),

                'exp_req'       =>  trim($_POST['exp_req']),
                'edu_req'       =>  trim($_POST['edu_req']),

                'zp_num'        =>  intval($_POST['zp_num']),
                'zp_minage'     =>  intval($_POST['zp_minage']),
                'zp_maxage'     =>  intval($_POST['zp_maxage']),
                'minage_req'     =>  intval($_POST['minage_req']),
                'maxage_req'     =>  intval($_POST['maxage_req']),
                'sex_req'     =>  intval($_POST['sex_req']),
			);

			$data	        =	array(
				'post'			=>	$post,
				'id'			=>	intval($_POST['id']),
				'uid'			=>	$_POST['uid'],
				'utype'			=>	'admin'
			);

			$return	        =	$JobM->addJobInfo($data);

            if ($return['errcode'] == 9) {

                $this->ACT_layer_msg($return['msg'], $return['errcode'], 'index.php?m=admin_company_job&remember_prev=1');
            } else {

                $this->ACT_layer_msg($return['msg'], $return['errcode']);
            }
		}
		$this->yuntpl(array('admin/admin_company_job_show'));

	}

	// 转移类别
    function saveclass_action(){

        $JobM   =   $this -> MODEL('job');

		if($_POST['hy']   ==  ''){
			$this -> ACT_layer_msg('请选择行业类别！',8,$_SERVER['HTTP_REFERER']);
		}

		if($_POST['job1'] ==  ''){
			$this -> ACT_layer_msg('请选择职位类别！',8,$_SERVER['HTTP_REFERER']);
		}

		$data['hy']       =   $_POST['hy'];
		$data['job1']     =   $_POST['job1'];
		$data['job1_son'] =   $_POST['job1_son'];
		$data['job_post'] =   $_POST['job_post'];

		$id               =   @explode(',',$_POST['jobid']);

		$listA            =   $JobM -> getList(array('id' => array('in', pylode(',',$id))), array('cache'=>'1','field'=>'id,uid,name'));

		$nid              =   $JobM -> upInfo($data, array('id' => array('in', pylode(',',$id))));

		$job              =   $listA['list'];

		$cache            =   $listA['cache'];

		if($job){

		    $msg          =   array();
		    $uids         =   array();

		    //  提取职位uid 和职位名称
		    foreach ($job   as  $k => $v){

                $uids[] =  $v['uid'];

                $msg[$v['uid']][]  =  '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>管理员已修改，行业类别为：'.$cache[industry_name][$_POST['hy']].'，职位类别为：'.$cache[job_name][$_POST['job1']];

                if($_POST['job1_son']){
                    $msg[$v['uid']][]  .= ''.$cache[job_name][$_POST['job1_son']];
                }
                if($_POST['job_post']){
                    $msg[$v['uid']][]  .= ''.$cache[job_name][$_POST['job_post']];
                }
		    }

		    $sysmsgM    =   $this -> MODEL('sysmsg');

		    $sysmsgM    ->  addInfo(array('uid'=>$uids,'usertype'=>2, 'content'=>$msg));

		}


		$nid?$this->ACT_layer_msg('职位类别(ID:'.$_POST['jobid'].')修改成功！',9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg('修改失败！',8,$_SERVER['HTTP_REFERER']);
	}

	// 删除职位
	function del_action(){

		$this->check_token();

		$JobM	=	$this -> Model('job');
		$PackM	=	$this->Model('pack');

		$delID	=	is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];

		if (is_array($_GET['del'])) {

			$layer_type = 1; // 提示方式
		} else if ($_GET['del']) {

			$layer_type = 0; // 提示方式
		}

		$numwhere['jobid'] = array('in',pylode(',', $delID));

        //根据id查找
        $rewardList        =   $PackM->getRewardJobList($numwhere);
        $shareList         =   $PackM->getShareJobList($numwhere,array('utype'=>'admin'));

		if($rewardList ||  $shareList){
            if($rewardList){
                foreach($rewardList as $val){
                    $rjobids[]	=   $val['jobid'];
                }
            }
            if($shareList){
                foreach($shareList as $val){
                    $sjobids[]	=   $val['jobid'];
                }
            }
            if($rjobids && $sjobids){

                $input		=	array_merge($rjobids,$sjobids);//先组合一个$uid;
            }else{

                if($rjobids){
                    $input	=   $rjobids;
                }else{
                    $input	=   $sjobids;
                }
            }
            $jobid     =  array_merge(array_diff($input,$delID),array_diff($delID,$input));
            $numjobid  =  array_diff_assoc($input,$delID);//获取到重复的值
            $jobnum    =  count($jobid);//删除id
            $rewanum   =  count($numjobid);//无法删除id

            if($jobnum>0){

                $addArr	=	$JobM -> delJob($jobid, array('utype'=>'admin'));
                if($rewanum>0){

                    $msg  =  "删除成功职位".$jobnum.",删除失败".$rewanum.",原因：还有赏金未处理";
                    $this->layer_msg($msg,$addArr['errcode'],$addArr['layertype'],$_SERVER['HTTP_REFERER'],2,1);
                }else{

                    $this->layer_msg( $addArr['msg'],$addArr['errcode'],$addArr['layertype'],$_SERVER['HTTP_REFERER'],2,1);
                }
            }else{

                $this->layer_msg('该职位还有赏金未处理,无法删除！',8,$layer_type,$_SERVER['HTTP_REFERER'],2,1);
            }
        }else{

			$addArr  =  $JobM -> delJob($delID, array('utype'=>'admin'));
			$this->layer_msg( $addArr['msg'],$addArr['errcode'],$addArr['layertype'],$_SERVER['HTTP_REFERER'],2,1);
        }
 	}

    function refresh_action()
    {

        $JobM   =   $this->MODEL('job');

        $ids    =   @explode(',', $_POST['ids']);

        $result =   $JobM->upInfo(array('lastupdate' => time()), array('id' => array('in', pylode(',', $ids))));

        if ($result){

            $logM   =   $this->MODEL('log');
            $jobS   =   $logM->getJobBySxLog(array('id' => array('in', pylode(',', $ids))), array('type' => 1, 'field' => 'id,uid'));

            $vData  =   array();

            foreach ($jobS as $k => $v) {

                $vData[$k]['uid']       =   $v['uid'];
                $vData[$k]['usertype']  =   2;
                $vData[$k]['jobid']     =   $v['id'];
                $vData[$k]['type']      =   1;
                $vData[$k]['r_time']    =   time();
                $vData[$k]['port']      =   5;
                $vData[$k]['ip']        =   fun_ip_get();
                $vData[$k]['remark']    =   '管理员操作：后台职位列表刷新职位';
            }
            $logM->addJobSxLogS($vData);
        }

        $this->MODEL('log')->addAdminLog("职位(ID" . $_POST['ids'] . "刷新成功");
    }

	// 导出职位列表数据
	function xls_action(){

	    session_start();

	    $where = $_SESSION['jobXls'] ? $_SESSION['jobXls'] : array('orderby'=>'id');


	    if(!empty($_POST['type'])){

	        foreach($_POST['type'] as $v){

	            if($v == 'lastdate'){

	                $type[]  =  'lastupdate';

	            }else{

	                $type[]  =  $v;

	            }

	        }

	        $field  =  @implode(',', $type).',uid';

	    }else{

	        $field  =  'uid';

	    }

	    if($_POST['pid']){

	        $ids          =  @explode(',',$_POST['pid']);

	        $where['id']  =  array('in',pylode(',',$ids));

 	    }
	    if($_POST['limit']){

	        $where['limit']  =  intval($_POST['limit']);
	    }

	    $jobM      =   $this -> MODEL('job');

	    $listNew   =   $jobM -> getList($where,array('cache'=>1,'field'=>$field));

	    $jobs      =   $listNew['list'];
	    $cache     =   $listNew['cache'];


	    if (!empty($jobs)){

	        foreach($jobs as $k => $v){

	            $langs = array();

	            if($v['lang']!=""){

 	                $lang =   @explode(",",$v['lang']);

	                foreach($lang as $val){

	                    $langs[]   =   $cache[comclass_name][$val];

	                }

	                $jobs[$k]['lang_info'] = @implode(",",$langs);
	            }

	        }

 	        $this->yunset("cache",$cache);
	        $this->yunset("list",$jobs);
	        $this->yunset("type",$_POST['type']);

	        $this->MODEL('log')->addAdminLog("导出职位信息");
	        header("Content-Type: application/vnd.ms-excel");
	        header("Content-Disposition: attachment; filename=job.xls");
	        $this->yuntpl(array('admin/admin_job_xls'));
	    }

	}

	/* 职位匹配简历 */
	function matching_action(){

        $cacheM		=	$this -> MODEL('cache');
        $CacheList	=	$cacheM -> GetCache(array('user', 'job', 'city'));

        $setArr	    =	array(
            'userdata'		=>	$CacheList['userdata'],
            'userclass_name'=>	$CacheList['userclass_name'],
            'job_name'		=>	$CacheList['job_name'],
            'city_name'		=>	$CacheList['city_name']
        );
        $this -> yunset($setArr);

		if($_GET['id']){

            $id     =   intval($_GET['id']);
            $where = '1';
            $where .= ' and state=1';
            $where .= ' and status=1';
            $where .= ' and r_status=1';
            $where .= ' and defaults=1';
            $ResumeM   =   $this -> MODEL('resume');
            $JobM       =   $this->MODEL('job');
            $jobinfo    =   $JobM->getInfo(array('id' => $id), array('field'=>'id,uid,job1,job1_son,job_post,provinceid,cityid,three_cityid'));
			$this->yunset('comid', $jobinfo['uid']);

			if($jobinfo){
                if($_GET['keyword']){
                    $keyword = trim($_GET['keyword']);
                    $workWhere  =   array(
                        'name'      =>  array('like', $keyword),
                        'title'     =>  array('like', $keyword, 'OR'),
                    );
                    $work       =   $ResumeM->getResumeWorks($workWhere, 'eid');
                    if ($work) {
                        $eids = array();
                        foreach ($work as $v) {
                            $eids[] = $v['eid'];
                        }
                    }
                    $eduWhere   =   array(
                        'name'      =>  array('like', $keyword),
                        'specialty' =>  array('like', $keyword, 'OR')
                    );

                    $edu        =   $ResumeM->getResumeEdus($eduWhere, 'eid');

                    if ($edu) {

                        $eids = array();
                        foreach ($edu as $v) {

                            $eids[] = $v['eid'];
                        }
                    }
                    $UserinfoM	=	$this -> MODEL('userinfo');
                    $mwhere['description']     =   array('like', $keyword);
                    if(!empty($mwhere)){
                        $uidList	=	$UserinfoM  ->  getUserInfoList($mwhere, array('usertype'=>1,'field' => '`uid`'));
                        if(!empty($uidList)){
                            foreach($uidList as $uv){
                                $mUids[]	=	$uv['uid'];
                            }
                        }
                    }
                    $where .= " and (a.name like '%$keyword%'";
                    if (!empty($mUids)){
                        $where .= " or a.uid in (".pylode(',', $mUids).")";
                    }
                    if (!empty($eids)){
                        $where .= " or a.id in (".pylode(',', $eids).")";
                    }
                    $where .= ")";
                    $urlarr['keyword'] = $keyword;
                }
                //学历要求
                if ($_GET['edu']) {

                    $eduKey = $this->obj->select_once('userclass', array('variable' => 'user_edu'), "`id`");
                    $eduReq = $this->obj->select_once('userclass', array('id' => $_GET['edu']), "`sort`,`name`");
                    if ($eduReq['name'] != "不限") {

                        $eduArr = $this->obj->select_all('userclass', array('keyid' => $eduKey['id'], 'sort' => array('>=', $eduReq['sort'])), "`id`");
                        $eduIds = array();
                        foreach ($eduArr as $v) {
                            $eduIds[] = $v['id'];
                        }

                        $where .= " and a.edu in (".pylode(',', $eduIds).") ";
                    }

                    $urlarr['edu'] = intval($_GET['edu']);
                }
                //工作经验
                if ($_GET['exp']) {

                    $expKey = $this->obj->select_once('userclass', array('variable' => 'user_word'), "`id`");
                    $expReq = $this->obj->select_once('userclass', array('id' => $_GET['exp']), "`sort`,`name`");
                    if (isset($expReq) && $expReq['name'] != "不限") {

                        $expArr = $this->obj->select_all('userclass', array('keyid' => $expKey['id'], 'sort' => array('>=', $expReq['sort'])), "`id`");
                        $expIds = array();
                        foreach ($expArr as $v) {
                            $expIds[] = $v['id'];
                        }

                        $where .= " and a.exp in (".pylode(',', $expIds).") ";
                    }

                    $urlarr['exp'] = intval($_GET['exp']);
                }
                if($_GET['city_class']){
                    $cityclass = explode(',',$_GET['city_class']);
                    $this->yunset('cityArr',$cityclass);
                }
                if ($_GET['job_class']){
                    $jobclass = explode(',',$_GET['job_class']);
                    $this->yunset('jobArr',$jobclass);
                }


                if($_GET['label']){
                    $where .= ' and a.label='.intval($_GET['label']);
                    $urlarr['label'] = intval($_GET['label']);
                }
                if($_GET['content']){
                    $where .= " and a.content like '%".trim($_GET['content'])."%'";
                    $urlarr['content'] = $_GET['content'];
                }

                if($jobinfo['job_post']){
                    $jobstrid = $jobinfo['job_post'];
                }elseif($jobinfo['job1_son']){
                    $jobstrid = $jobinfo['job1_son'];
                }elseif ($jobinfo['job1']){
                    $jobstrid = $jobinfo['job1'];
                }

                if($jobinfo['three_cityid']){
                    $citystrid = $jobinfo['three_cityid'];
                }elseif($jobinfo['cityid']){
                    $citystrid = $jobinfo['cityid'];
                }elseif ($jobinfo['provinceid']){
                    $citystrid = $jobinfo['provinceid'];
                }

                $this->yunset('citystrid',$citystrid);
                $this->yunset('jobstrid',$jobstrid);

                if(isset($_GET['one'])){
                    header("location:index.php?m=admin_company_job&c=matching&id=".$jobinfo['id']."&city_class=".$citystrid."&job_class=".$jobstrid."&edu=".$jobinfo['edu']);
                }

	        }


	        $record    =   $ResumeM -> getResTsList(array('jobid'=>$id),array('field'=>'`eid`'));
	        if(!empty($record)){
    			foreach($record as $v){

    				$eids[]     =   $v['eid'];
    			}
                $where .= " and a.id not in (".pylode(',', $eids).") ";
	        }

	        $noUids     =   array();

	        $blackM		=	$this->MODEL('black');
            $black      =   $blackM->getBlackList(array('p_uid' => $jobinfo['uid']));
            if(!empty($black)){
    			foreach($black as $v){

    			    $buids[]    =   $v['c_uid'];
    			}
    			if(!empty($buids)){
    			    $noUids =   $buids;
                }
            }

            $applyList          =   $JobM->getSqJobList(array('job_id' => $jobinfo['id']),array('field' => '`uid`', 'utype' => 'simple'));
            if (!empty($applyList)){
                $sqUids         =   array();
                foreach ($applyList as $v) {
                    $sqUids[]   =   $v['uid'];
                }
                if(!empty($sqUids)){
                    $noUids =   !empty($noUids) ? array_merge($noUids, $sqUids) : $sqUids;
                }
            }
            $yqList             =   $JobM->getYqmsList(array('fid' => $jobinfo['uid']),array('field' => '`uid`', 'utype' => 'simple'));
            if (!empty($yqList)){
                $yqUids         =   array();
                foreach ($yqList as $v) {
                    $yqUids[]   =   $v['uid'];
                }
                if(!empty($yqUids)){
                    $noUids =   !empty($noUids) ? array_merge($noUids, $yqUids) : $yqUids;
                }
            }
            if (!empty($noUids)){
                $where .= " and a.uid not in (".pylode(',', $noUids).") ";
            }
            include(PLUS_PATH . 'city.cache.php');
            include(PLUS_PATH . 'cityparent.cache.php');
            include(PLUS_PATH . 'job.cache.php');
            include(PLUS_PATH . 'jobparent.cache.php');
            $city_job_class = '';
            if ($_GET['job_class'] || $_GET['city_class']) {
                $city_col = $job_col = '';
                $cjwhere = '';
                if ($_GET['job_class']) {
                    if ($job_parent[$_GET['job_class']] == '0') {
                        $job_col = "job1";
                        $cjwhere .= "$job_col = {$_GET['job_class']}";
                    } elseif (in_array($job_parent[$_GET['job_class']], $job_index)) {
                        $job_col = "job1_son";
                        $cjwhere .= "$job_col = {$_GET['job_class']}";
                    } elseif ($job_parent[$_GET['job_class']] > 0) {
                        $job_col = "job_post";
                        $cjwhere .= "$job_col = {$_GET['job_class']}";
                    }
                    $urlarr['job_class'] = $_GET['job_class'];
                }
                if ($_GET['city_class']) {
                    $cjand = $cjwhere ? ' AND ' : '';
                    if ($city_parent[$_GET['city_class']] == '0') {
                        $city_col = "provinceid";
                        $cjwhere .= "{$cjand}$city_col = {$_GET['city_class']}";
                    } elseif (in_array($city_parent[$_GET['city_class']], $city_index)) {
                        $city_col = "cityid";
                        $cjwhere .= "{$cjand}$city_col = {$_GET['city_class']}";
                    } elseif ($city_parent[$_GET['city_class']] > 0) {
                        $city_col = "three_cityid";
                        $cjwhere .= "{$cjand}$city_col = {$_GET['city_class']}";
                    }
                    $urlarr['city_class'] = $_GET['city_class'];
                }
                // 拼接唯一标识字段
                if ($city_col || $job_col) {
                    if ($city_col && $job_col) {
                        $cjwhere .= " AND {$city_col}_{$job_col}_num = 1";
                    } elseif ($city_col) {
                        $cjwhere .= " AND {$city_col}_num = 1";
                    } elseif ($job_col) {
                        $cjwhere .= " AND {$job_col}_num = 1";
                    }
                }
                $city_job_class = ",(select `eid` from `".$this->def."resume_city_job_class` where $cjwhere) cj";
                $where .= " and a.id = cj.eid";
            }
            $countSql = "select count(*) as num from `".$this->def."resume_expect` a{$city_job_class} where {$where}";

            //分页链接
            $urlarr        	=   $_GET;
            $urlarr['page'] = '{{page}}';
            $pageurl = Url('admin_company_job&c=matching&id='.$id.'', $urlarr, 'admin');
            //提取分页
            $pageM = $this->MODEL('page');
            $pages = $pageM->pageList('resume_expect', $where, $pageurl, $_GET['page'], '', $countSql);
            $order = '';
            //分页数大于0的情况下 执行列表查询
            if ($pages['total'] > 0) {
                //limit order 只有在列表查询时才需要
                if ($_GET['order']) {

                    if ($_GET['t'] == 'time') {

                        $order .= "order by a.lastupdate ". $_GET['order'];
                    } else {

                        $order .= 'order by a.' . $_GET['t'] . ' ' . $_GET['order'];
                    }

                    $urlarr['order'] = $_GET['order'];
                    $urlarr['t'] = $_GET['t'];
                } else {
                    $order .= 'order by a.lastupdate desc';
                }
                $sql        =   "select a.* from `".$this->def."resume_expect` a{$city_job_class} where {$where} {$order} limit {$pages['limit'][0]},{$pages['limit'][1]}";

                $List       =   $ResumeM->getList(array(), array('cache' => 1, 'utype' => 'admin', 'sql' => $sql));
                $this -> yunset(array('resumes'=>$List['list']));
            }
	        $this->yuntpl(array('admin/admin_matching'));
	    }
	}

	function jobNum_action(){
		$MsgNum=$this->MODEL('msgNum');
		echo $MsgNum->jobNum();
	}

    /**
     * @desc 企业微海报
     */
    function whb_action()
    {

        $id         =   intval($_GET['id']);
        $this->yunset('job_id', $id);

        $WhbM       =   $this->MODEL('whb');

        $imgList    =   $WhbM->getWhbList(array('type' => 1,'isopen' => '1', 'orderby' => 'sort,desc'));

        $this->yunset('imgList', $imgList);

        $this->yuntpl(array('wap/hb_job/admin_whb'));
    }

    function getJobHtml_action(){

        if ($_POST['id']){

            $wxpubtempM =   $this->MODEL('wxpubtemp');

            $html = $wxpubtempM->getOneJob($_POST['id'],'admin');

            echo $html;

        }
    }

    function addTuiWenTask_action()
    {

        if ($_POST) {

            $wxPubM =   $this->MODEL('wxpubtemp');

            $data   =   array(

                'jobid'     =>  $_POST['twtask_jobid'],
                'content'   =>  $_POST['twtask_content'],
                'urgent'    =>  $_POST['twtask_urgent'],
                'wcmoments' =>  $_POST['twtask_wcmoments'],
                'gzh'       =>  $_POST['twtask_gzh'],
                'type'      =>  1
            );
            $return =   $wxPubM->addTwTask($data, array('auid' => $_SESSION['auid']));

            echo json_encode($return);
            exit();
        }
    }

    // 职位单个审核，带职位详情
    function jobAudit_action(){
        
        $jobid =  intval($_GET['id']);
        $JobM  =  $this->MODEL('job');
        $Info  =  $JobM->getInfo(array('id' => $jobid));
        $memberInfo  =  $this->obj->select_once('member', array('uid' => $Info['uid']), '`status`,`lock_info`');

        if ($Info['is_link'] == 2){

            $link               =   $this->obj->select_once('company_job_link', array('id' => $Info['link_id']));
            $Info['tel']        =   $link['link_moblie'];
            $Info['phone']      =   $link['link_phone'];
            $Info['address']    =   $link['link_address'];
            $Info['linkman']    =   $link['link_man'];
        }else{

            $comInfo            =   $this->obj->select_once('company', array('uid' => $Info['uid']), '`linkman`, `linktel`,`linkphone`,`linkmail`, `address`');
            $Info['tel']        =   $comInfo['linktel'];
            $Info['phone']      =   $comInfo['linkphone'];
            $Info['address']    =   $comInfo['address'];
            $Info['linkman']    =   $comInfo['linkman'];
        }
        $Info['c_status'] = $memberInfo['status'];
        $Info['statusbody'] = trim($memberInfo['lock_info']);

        $this->yunset('Info', $Info);
        
        $cacheM    =  $this->MODEL('cache');
        $cacheList =  $cacheM->GetCache('com');
        $this->yunset($cacheList);
        
        // 待审核数量
        $snum = $JobM->getJobNum(array('state'=>0,'id'=>array('<>', $jobid)));
        $this->yunset('snum', $snum);
        
        $this->yuntpl(array('admin/admin_company_job_audit'));
    }

    function reserveJob_action()
    {

        session_start();
        if (isset($_GET['remember_prev']) && $_GET['remember_prev']==1) {
            foreach ($_SESSION['prev_reservelist'] as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_SESSION['prev_reservelist'] = $_GET;

        $JobM   =   $this->MODEL('job');

        $where  =   array(
            'is_reserve'    =>  1,
            'state'         =>  1,
            'status'        =>  0,
            'r_status'      =>  1
        );

        if (isset($_GET['keyword'])) {

            $keyStr     =   trim($_GET['keyword']);
            $typeStr    =   (int)$_GET['type'];

            if ($typeStr == 1) {

                $where['com_name']  =   array('like', $keyStr);
            } elseif ($typeStr == 2) {

                $where['name']      =   array('like', $keyStr);
            }

            $urlarr['type']     =   $_GET['type'];
            $urlarr['keyword']  =   $_GET['keyword'];
        }

        if (isset($_GET['uid']) && !empty($uid)) {

            $where['uid']       =   $_GET['uid'];
        }
        $urlarr        			=   $_GET;
        $urlarr['c']            =   'reserveJob';
        if (isset($urlarr['remember_prev'])) {
            unset($urlarr['remember_prev']);
        }
        $urlarr['page']         =   '{{page}}';
        $pageurl                =   Url($_GET['m'], $urlarr, 'admin');

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('company_job', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   'lastupdate,desc';
            }

            $where['limit'] =   $pages['limit'];

            $ListJob        =   $JobM->getList($where, array('utype' => 'admin', 'isurl' => 'yes', 'reserve' => 1));
            $setArr         =   array('rows' => $ListJob['list']);
            $this->yunset($setArr);
        }

        $this->yuntpl(array('admin/admin_company_reserve_job'));
    }

    function closeReserve_action(){

        $JobM	=	$this -> Model('job');
        if ($_GET['id']){

            $this->check_token();
            $ids    =   $_GET['id'];
            $layer_type = 0; // 提示方式
        }else if($_POST['ids']){

            $ids    =   $_POST['ids'];
            $layer_type = 1; // 提示方式
        }

        $return =   $JobM->closeReserve(array('jobids' => $ids, 'utype' => 'admin'));
        if ($return['errcode'] == 9){

            $this->MODEL('log')->addAdminLog("关闭职位（ID".$ids."）预约刷新");
        }

        $this->layer_msg( $return['msg'],$return['errcode'],$layer_type,$_SERVER['HTTP_REFERER'],2,1);
    }

    function ajaxCloseReserve_action()
    {

        $JobM   =   $this->Model('job');
        $return =   $JobM->closeReserve(array('auto' => 1));
        echo json_encode($return);die;
    }

    function upReserveJob_action()
    {

        $jobM   =   $this->MODEL('job');

        $data   =   array(

            'job_id'    =>  $_POST['job_id'],
            'end_time'  =>  strtotime($_POST['end_time']),
            'interval'  =>  $_POST['interval'],
            'status'    =>  $_POST['status'],
            's_time'    =>  $_POST['s_time'],
            'e_time'    =>  $_POST['e_time']
        );
        $return =   $jobM->reserveUpJob($data, array('utype' => 'admin', 'uid' => $_POST['uid']));

        $return['errcode']  =   $return['error'] == 1 ? 9 : 8;

        $this->ACT_layer_msg($return['msg'], $return['errcode'], "index.php?m=admin_company_job&c=reserveJob&remember_prev=1", 2, 1);
    }

    /**
     * @desc 职位匹配简历投递
     */
    function applyJob_action()
    {

        $where  =   array(

            'eid'       =>  intval($_GET['eid']),
            'uid'       =>  intval($_GET['uid']),
            'job_id'    =>  intval($_GET['id']),
            'com_id'    =>  intval($_GET['comid'])
        );

        $jobM   =   $this->MODEL('job');

        $return =   $jobM->applyJobByAdmin($where, array('auid' => $_SESSION['auid']));

        $err    =   array(
            'msg'   =>  $return['msg'],
            'type'  =>  $return['errcode']
        );

        echo json_encode($err);
        die;

    }

    function sxLog_action()
    {

        $logM   =   $this->MODEL('log');

        $where  =   array();

        $type   =   1;

        if ($_GET['type']){

            $type           =   (int)$_GET['type'];
            $where['type']  =   $_GET['type'];
            $urlarr['type'] =   $_GET['type'];
        }

        $keyStr =   trim($_GET['keyword']);
        $typeStr=   (int)$_GET['ktype'];

        if (!empty($keyStr)) {

            $jobWhere   =   array();

            if ($typeStr == 1) {

                $jobWhere['com_name']   =   array('like', $keyStr);
            } elseif ($typeStr == 2) {

               if($type=='3'){
                   $jobWhere['job_name']   =   array('like', $keyStr);
               }else{
                   $jobWhere['name']       =   array('like', $keyStr);
               }
            }

            $jobS   =   $logM->getJobBySxLog($jobWhere, array('field' => 'id', 'type' => $type));

            $jobIds =   array();

            foreach ($jobS as $jk => $jv) {
                if (!in_array($jv['id'], $jobIds)){
                    $jobIds[]   =   $jv['id'];
                }
            }

            $where['jobid']     =   array('in', pylode(',', $jobIds));

            $urlarr['type']     =   $_GET['type'];
            $urlarr['keyword']  =   $_GET['keyword'];
        }

        if (isset($_GET['uid']) && !empty($uid)) {

            $where['uid']       =   $_GET['uid'];
        }
        $urlarr        			=   $_GET;
        $urlarr['c']            =   'sxLog';
        $urlarr['page']         =   '{{page}}';

        $pageurl                =   Url($_GET['m'], $urlarr, 'admin');

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('job_refresh_log', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];

            } else {

                $where['orderby']   =   'id,desc';
            }

            $where['limit'] =   $pages['limit'];

            $List           =   $logM->getSxJobLogList($where, array('utype' => 'admin'));

            $this->yunset(array('rows' => $List));
        }
        $this->yuntpl(array('admin/admin_sx_job_log'));
    }

    function delSxLog_action()
    {

        $this->check_token();

        $LogM   =   $this -> MODEL('log');

        if($_GET['del']){

            $id =   $_GET['del'];
        }else{

            $id =   $_GET['id'];
        }

        $arr    =   $LogM -> delSxJobLog($id,array('utype'=>'admin'));

        $this->layer_msg($arr['msg'], $arr['errcode'], $arr['layertype'], $_SERVER['HTTP_REFERER']);
    }

    function saveAddress_action()
    {

        $addressM   =   $this->MODEL('address');

        $linkData   =   array(

            'uid'           =>  $_POST['uid'],
            'link_man'      =>  $_POST['link_man'],
            'link_moblie'   =>  $_POST['link_moblie'],
            'link_phone'    =>  $_POST['link_phone'],
            'email'         =>  $_POST['email'],
            'link_address'  =>  $_POST['link_address'],
            'provinceid'    =>  $_POST['provinceid'],
            'cityid'        =>  $_POST['cityid'],
            'three_cityid'  =>  $_POST['three_cityid'],
            'x'             =>  $_POST['x'],
            'y'             =>  $_POST['y']
        );

        $result         =   $addressM->saveAddress($linkData, array('utype' => 'admin'));
        $msg            =   $result ? '工作地址添加成功' : '工作地址添加失败';
        $link_id        =   $result;

        $errcode            =   $result ? 9 : 8;

        if ($result && (int)$_POST['is_link'] == 2){

            $addressList    =   $addressM->getAddressList(array('uid' => $_POST['uid']));
            $comM           =   $this->MODEL('company');
            $this->comInfo  =   $comM->getInfo($_POST['uid'], array('info' => '1', 'edit' => '1', 'logo' => '1', 'utype' => 'user'));
            $defLink        =   array(

                'link_man'      =>  $this->comInfo['linkman'],
                'link_moblie'   =>  $this->comInfo['linktel'],
                'link_phone'    =>  $this->comInfo['linkphone'],
                'email'         =>  $this->comInfo['linkmail'],
                'city'          =>  $this->comInfo['job_city_one'].$this->comInfo['job_city_two'].$this->comInfo['job_city_three'],

                'provinceid'    =>  $this->comInfo['provinceid'],
                'cityid'        =>  $this->comInfo['cityid'],
                'three_cityid'  =>  $this->comInfo['three_cityid'],
                'address'       =>  $this->comInfo['address'],
                'x'             =>  $this->comInfo['x'],
                'y'             =>  $this->comInfo['y']
            );


            echo json_encode(array('errcode' => $errcode, 'msg' => $msg, 'defLink' => $defLink, 'addressList' => $addressList, 'link_id' => $link_id));
            exit();
        }else{

            echo json_encode(array('errcode' => $errcode, 'msg' => $msg));
            exit();
        }
    }
	/**
     *users:王旭
     *Data:2023/2/4
     *Time:9:25
     *
     * 单职位联系方式选择性开放
     */
    function setlinkopen_action()
    {
        if (!empty($_POST['linkopen'])) {
//            if (intval($_POST['linkopen']) == 2 && ($this->config['sy_msg_isopen'] != 1 || $this->config['sy_msg_regcode'] != 1)) {
//                $this->ACT_layer_msg('请开启短信和注册验证码！', 8, $_SERVER['HTTP_REFERER'], 2, 1);
//            }
            $JobM = $this->Model('job');
            $return = $JobM->setlinkopen(array('linkopen' => intval($_POST['linkopen'])), array('id' => intval($_POST['linkjobid'])));
            $this->ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
        }
    }

    /**
     * 职位降权
     */
    function depower_action()
    {
        $jobM = $this->MODEL('job');

        if (empty($_POST['id']) || empty($_POST['is_depower'])) {
            $this->layer_msg('参数错误', 8);
        }

        $where['id'] = intval($_POST['id']);
        $data['is_depower'] = intval($_POST['is_depower']);

        $return = $jobM->upInfo($data, $where);

        $msg = $data['is_depower'] == 1 ? '降权' : '取消降权';

        if ($return) {
            $this->layer_msg("职位{$msg}（ID：{$where['id']}）操作成功");
        } else {
            $this->layer_msg("职位{$msg}操作失败", 8);
        }
    }
}
?>