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
class school_graduate_controller extends adminCommon{
	function index_action(){

		$jobM		=	$this->MODEL('job');
		$where['is_graduate']	=   1;
		if ($_POST['keyword']){
			$keytype	=	intval($_POST['type']);
			$keyword	=   trim($_POST['keyword']);
			if($keytype    ==    1){
				$where['com_name']	=	array('like',$keyword);
			}else{
				$where['name']		=	array('like',$keyword);
			}
		}
		if($_POST['source']){
			$source					=   intval($_POST['source']);
			$where['source']		=   $source;
		}

		if ($_POST['job_type']){
      
			$jobtype				=   intval($_POST['job_type']);
			$where['type']			=   $jobtype;
		}
		if($_POST['adtime']){
			if($_POST['adtime']=='1'){
				$where['sdate']		=	array('>',strtotime('today'));
			}else{
				$where['sdate']		=	array('>',strtotime('-'.intval($_POST['adtime']).' day'));
			}
		}
		if($_POST['state']){
			$state					=   intval($_POST['state']);
			if($state==2){
				$where['r_status']	=	array('>', 1);
			}else{
				$where['state']		=	$state == 4 ? 0 : $state;
			}
		}
		if($_POST['status']){
			$status					=   intval($_POST['status']);
			$where['status']		=   $status ==1? 1:0 ;
		}

		if($_POST['jtype']){
			if($_POST['jtype']=='rec'){
				$where['rec_time']		=	array('>',time());
			}else if($_POST['jtype']=='urgent'){
				$where['urgent_time']	=	array('>',time());
			}else if($_POST['jtype']=='xuanshang'){
				$where['xsdate']		=	array('>',time());
			}
		}
        if ($_POST['order']) {
            $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
        } else {
            $where['orderby'] = 'r_status,asc';
        }
        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('company_job', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        $list = [];
        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];
            $ListJob = $jobM->getList($where,array('utype'=>'admin','cache'=>'1','isurl'=>'yes'));
            $list = $ListJob['list'];
            $_SESSION['jobXls'] = $where;

        }

		$this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
	}
    public function  getCacheData_action(){
        $CacheList = $this->MODEL('cache')->GetCache(array('job','hy','city','com'));
        $cache = $CacheList;
        $comdata = $CacheList['comdata'];
        $comclass_name = $CacheList['comclass_name'];
        $job_name = $CacheList['job_name'];
        $city_name = $CacheList['city_name'];
        $jionly = empty($CacheList['job_type']) ? 1 : 0;
        $jobtypes = array();
        foreach ($CacheList['job_index'] as $fir_v) {
            $jobtypes[$fir_v] = array('value' => $fir_v, 'label' => $CacheList['job_name'][$fir_v]);
            if ($CacheList['job_type'][$fir_v]) {// 二级城市
                $jobtypes[$fir_v]['children'] = array();
                foreach ($CacheList['job_type'][$fir_v] as $sec_v) {
                    $jobtypes[$fir_v]['children'][$sec_v] = array('value' => $sec_v, 'label' => $CacheList['job_name'][$sec_v]);
                    if ($CacheList['city_type'][$sec_v]) {// 三级城市
                        $jobtypes[$fir_v]['children'][$sec_v]['children'] = array();
                        foreach ($CacheList['city_type'][$sec_v] as $thi_v) {
                            $jobtypes[$fir_v]['children'][$sec_v]['children'][$thi_v] = array('value' => $thi_v, 'label' => $CacheList['job_name'][$thi_v]);
                        }
                    }
                }
            }
        }
        $jobtypes = array_values($jobtypes);
        foreach ($jobtypes as $k => $v) {
            if ($v['children']) {
                $jobtypes[$k]['children'] = array_values($v['children']);
                foreach ($jobtypes[$k]['children'] as $kk => $vv) {
                    if ($vv['children']) {
                        $jobtypes[$k]['children'][$kk]['children'] = array_values($vv['children']);
                    }
                }
            }
        }
        $job_types = $jobtypes;
        $citys = array();
        foreach ($CacheList['city_index'] as $fir_v) {
            $citys[$fir_v] = array('value' => $fir_v, 'label' => $CacheList['city_name'][$fir_v]);
            if ($CacheList['city_type'][$fir_v]) {// 二级城市
                $citys[$fir_v]['children'] = array();
                foreach ($CacheList['city_type'][$fir_v] as $sec_v) {
                    $citys[$fir_v]['children'][$sec_v] = array('value' => $sec_v, 'label' => $CacheList['city_name'][$sec_v]);
                    if ($CacheList['city_type'][$sec_v]) {// 三级城市
                        $citys[$fir_v]['children'][$sec_v]['children'] = array();
                        foreach ($CacheList['city_type'][$sec_v] as $thi_v) {
                            $citys[$fir_v]['children'][$sec_v]['children'][$thi_v] = array('value' => $thi_v, 'label' => $CacheList['city_name'][$thi_v]);
                        }
                    }
                }
            }
        }
        $citys = array_values($citys);
        foreach ($citys as $k => $v) {
            if ($v['children']) {
                $citys[$k]['children'] = array_values($v['children']);
                foreach ($citys[$k]['children'] as $kk => $vv) {
                    if ($vv['children']) {
                        $citys[$k]['children'][$kk]['children'] = array_values($vv['children']);
                    }
                }
            }
        }
        $city_types = $citys;
        include(CONFIG_PATH."db.data.php");
        $source=$arr_data['source'];
        $search_list[]=array("param"=>"state","name"=>'审核状态',"value"=>array("1"=>"已审核","4"=>"未审核","3"=>"未通过","2"=>"已锁定"));
        $search_list[]=array("param"=>"status","name"=>'招聘状态',"value"=>array("1"=>"已下架","2"=>"发布中"));
        $search_list[]=array("param"=>"jtype","name"=>'职位类型',"value"=>array("urgent"=>"紧急职位","xuanshang"=>"置顶职位","rec"=>"推荐职位"));
        $search_list[]=array("param"=>"source","name"=>'数据来源',"value"=>$source);
        $search_list[]=array("param"=>"adtime","name"=>'发布时间',"value"=>array("1"=>"今天","3"=>"最近三天","7"=>"最近七天","15"=>"最近半月","30"=>"最近一个月"));
        $this->render_json(0,'', compact('cache','comdata','jionly','city_name','job_name','comclass_name','job_types','city_types','search_list','source'));
    }
	public function jobNum_action(){
        $MsgNum = $this->MODEL('msgNum');
        $statis = $MsgNum->sjobNum();
        $statis = json_decode($statis);
        $this->render_json(0, 'ok', compact('statis'));
    }
    function getinfo_action(){
        $JobM       =   $this->MODEL('job');
        $cacheM     =   $this->MODEL('cache');
        $options    =   array('job','com','city','hy');
        $cache      =   $cacheM -> GetCache($options);
        $id   =   intval($_POST['id']);
        $info =   $JobM   ->  getInfo(array('id' => $id));
        $info['lang']=@explode(',',$info['lang']);
        $com_job_myswitch = $this->config['com_job_myswitch'];
        $this->render_json(0, 'ok', compact('cache', 'info','com_job_myswitch'));
    }
    //职位详情及修改
	function show_action(){
        $cacheM = $this->MODEL('cache');
        $options = array('job', 'com', 'city', 'hy', 'user');
        $cache = $cacheM->GetCache($options);
        foreach ($cache['com_sex'] as $k => $v) {
            if ($v != '男') {
                $cache['com_sexreq'][$k] = $v;
            }
        }
        $rt['cache_com_sexreq'] = $cache['com_sexreq'];
        $rt['cache_userdata'] = $cache['userdata'];
        $rt['cache_userclassname'] = $cache['userclass_name'];
        $rt['cache'] = $cache;

        $JobM = $this->MODEL('job');
        $companyM = $this->MODEL('company');
        $addressM = $this->MODEL('address');
        if (isset($_POST['id']) && intval($_POST['id']) > 0) {
            $id = intval($_POST['id']);
            $info = $JobM->getInfo(array('id' => $id), array('add'=>'yes'));
            $all_welfare = $info['arraywelfare'] ? $info['arraywelfare'] : array();
            foreach ($cache['comdata']['job_welfare'] as $v) {
                !in_array($cache['comclass_name'][$v], $all_welfare) && array_push($all_welfare, $cache['comclass_name'][$v]);
            }
            $info['all_welfare'] = $all_welfare;
            $rt['show'] = $info;
            $uid = $info['uid'];
        }
        if (intval($_POST['uid'])) {
            $uid = intval($_POST['uid']);
            $all_welfare = array();
            foreach ($cache['comdata']['job_welfare'] as $v) {
                !in_array($cache['comclass_name'][$v], $all_welfare) && array_push($all_welfare, $cache['comclass_name'][$v]);
            }
            $rt['default_welfare'] = $all_welfare;
            $rt['job_types'] = $this->getJobTypeArr($cache);
            $rt['city_types'] = $this->getCityTypeArr($cache);
        }
        $company = $companyM->getInfo($uid, array('field' => '`uid`,`r_status`,`linkman`,`linktel`,`linkphone`,`address`,`provinceid`,`cityid`,`three_cityid`,`x`,`y`'));
        $cityStr = $company['job_city_one'] . $company['job_city_two'] . $company['job_city_three'];
        if ($company['linktel']) {
            $company['linkmsg'] = $company['linkman'] . ' - ' . $company['linktel'] . ' - ' . $cityStr . ' - ' . $company['address'];
        } else if ($company['linkphone']) {
            $company['linkmsg'] = $company['linkman'] . ' - ' . $company['linkphone'] . ' - ' . $cityStr . ' - ' . $company['address'];
        } else {
            $company['linkmsg'] = $company['linkman'] . ' - ' . $cityStr . ' - ' . $company['address'];
        }
        $addressList = $addressM->getAddressList(array('uid' => $uid));
        $rt['company'] = $company;
        $rt['addressList'] = $addressList;
        $rt['uid'] = $uid;
        if ($_POST['save']) {
            $description = str_replace(array("&amp;", "background-color:#ffffff", "background-color:#fff", "white-space:nowrap;"), array("&", 'background-color:', 'background-color:', 'white-space:'), $_POST['content']);
            if ($_POST['link_id'] == -1) {
                $provinceid = $company['provinceid'];
                $cityid = $company['cityid'];
                $three_cityid = $company['three_cityid'];
                $x = $company['x'];
                $y = $company['y'];
            } else if (intval($_POST['link_id']) > 0) {
                $addressInfo = $addressM->getAddressInfo(array('id' => $_POST['link_id'], 'uid' => $uid));
                $provinceid = $addressInfo['provinceid'];
                $cityid = $addressInfo['cityid'];
                $three_cityid = $addressInfo['three_cityid'];
                $x = $addressInfo['x'];
                $y = $addressInfo['y'];
            }
            $post = array(
                'name' => $_POST['name'],
                'job1' => intval($_POST['job1']),
                'job1_son' => intval($_POST['job1_son']),
                'job_post' => intval($_POST['job_post']),
                'provinceid' => intval($provinceid),
                'cityid' => intval($cityid),
                'three_cityid' => intval($three_cityid),
                'x' => $x,
                'y' => $y,
                'link_id' => (int)$_POST['link_id'] > 0 ? $_POST['link_id'] : '',
                'is_link' => $_POST['is_link'] ? $_POST['is_link'] : 1,
                'is_message' => $_POST['is_message'] ? $_POST['is_message'] : 1,
                'is_email' => $_POST['is_email'] ? $_POST['is_email'] : 1,
                'minsalary' => intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['minsalary']),
                'maxsalary' => intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['maxsalary']),
                'description' => $description,
                'r_status' => $company['r_status'],
                'hy' => intval($_POST['hy']),
                'number' => intval($_POST['number']),
                'exp' => intval($_POST['exp']),
                'report' => intval($_POST['report']),
                'age' => intval($_POST['age']),
                'sex' => intval($_POST['sex']),
                'edu' => intval($_POST['edu']),
                'is_graduate' => intval($_POST['is_graduate']),
                'marriage' => intval($_POST['marriage']),
                'lang' => trim(pylode(',', $_POST['checked_lang'])),
                'welfare' => trim(implode(',', $_POST['checked_welfare'])),
                'state' => $company['r_status'] == 1 ? 1 : 0,
                'jobhits' => intval($_POST['jobhits']),
                'jobexpoure' => intval($_POST['jobexpoure']),
                'exp_req' => trim($_POST['exp_req']),
                'edu_req' => trim($_POST['edu_req']),
                'zp_num' => intval($_POST['zp_num']),
                'zp_minage' => intval($_POST['zp_minage']),
                'zp_maxage' => intval($_POST['zp_maxage']),
                'minage_req' => intval($_POST['minage_req']),
                'maxage_req' => intval($_POST['maxage_req']),
                'sex_req' => intval($_POST['sex_req']),
                'status' => intval($_POST['status']),
            );
            $data = array(
                'post' => $post,
                'id' => intval($_POST['id']),
                'uid' => $_POST['uid'],
                'utype' => 'admin'
            );
            $return = $JobM->addJobInfo($data);
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        }
        $rt['mapurl'] = $this->config['mapurl'];
        $rt['mapsecret'] = $this->config['map_secret'];
        $this->render_json(0, '', $rt);
	}
    function saveAddress_action()
    {
        $addressM = $this->MODEL('address');
        $linkData = array(
            'uid' => $_POST['uid'],
            'link_man' => $_POST['link_man'],
            'link_moblie' => $_POST['link_moblie'],
            'link_phone' => $_POST['link_phone'],
            'email' => $_POST['email'],
            'link_address' => $_POST['link_address'],
            'provinceid' => $_POST['provinceid'],
            'cityid' => $_POST['cityid'],
            'three_cityid' => $_POST['three_cityid'],
            'x' => $_POST['x'],
            'y' => $_POST['y']
        );
        $result = $addressM->saveAddress($linkData, array('utype' => 'admin'));
        $msg = $result ? '工作地址添加成功' : '工作地址添加失败';
        $link_id = $result;
        $errcode = $result ? 9 : 8;
        if ($result && (int)$_POST['is_link'] == 2) {
            $addressList = $addressM->getAddressList(array('uid' => $_POST['uid']));
            $comM = $this->MODEL('company');
            $this->comInfo = $comM->getInfo($_POST['uid'], array('info' => '1', 'edit' => '1', 'logo' => '1', 'utype' => 'user'));
            $defLink = array(
                'link_man' => $this->comInfo['linkman'],
                'link_moblie' => $this->comInfo['linktel'],
                'link_phone' => $this->comInfo['linkphone'],
                'email' => $this->comInfo['linkmail'],
                'city' => $this->comInfo['job_city_one'] . $this->comInfo['job_city_two'] . $this->comInfo['job_city_three'],
                'provinceid' => $this->comInfo['provinceid'],
                'cityid' => $this->comInfo['cityid'],
                'three_cityid' => $this->comInfo['three_cityid'],
                'address' => $this->comInfo['address'],
                'x' => $this->comInfo['x'],
                'y' => $this->comInfo['y']
            );
            $this->render_json($errcode == 9 ? 0 : 1, $msg, array('addressList' => $addressList, 'link_id' => $link_id));
        } else {
            $this->render_json($errcode == 9 ? 0 : 1, $msg);
        }
    }
    private function getCityTypeArr($CacheList){
        $citys = array();
        foreach ($CacheList['city_index'] as $fir_v) {
            $citys[$fir_v] = array('value' => $fir_v, 'label' => $CacheList['city_name'][$fir_v]);
            if ($CacheList['city_type'][$fir_v]) {// 二级城市
                $citys[$fir_v]['children'] = array();
                foreach ($CacheList['city_type'][$fir_v] as $sec_v) {
                    $citys[$fir_v]['children'][$sec_v] = array('value' => $sec_v, 'label' => $CacheList['city_name'][$sec_v]);
                    if ($CacheList['city_type'][$sec_v]) {// 三级城市
                        $citys[$fir_v]['children'][$sec_v]['children'] = array();
                        foreach ($CacheList['city_type'][$sec_v] as $thi_v) {
                            $citys[$fir_v]['children'][$sec_v]['children'][$thi_v] = array('value' => $thi_v, 'label' => $CacheList['city_name'][$thi_v]);
                        }
                    }
                }
            }
        }
        $citys = array_values($citys);
        foreach ($citys as $k => $v) {
            if ($v['children']) {
                $citys[$k]['children'] = array_values($v['children']);
                foreach ($citys[$k]['children'] as $kk => $vv) {
                    if ($vv['children']) {
                        $citys[$k]['children'][$kk]['children'] = array_values($vv['children']);
                    }
                }
            }
        }
        return $citys;
    }

    private function getJobTypeArr($CacheList)
    {
        $jobtypes = array();
        foreach ($CacheList['job_index'] as $fir_v) {
            $jobtypes[$fir_v] = array('value' => $fir_v, 'label' => $CacheList['job_name'][$fir_v]);
            if ($CacheList['job_type'][$fir_v]) {// 二级
                $jobtypes[$fir_v]['children'] = array();
                foreach ($CacheList['job_type'][$fir_v] as $sec_v) {
                    $jobtypes[$fir_v]['children'][$sec_v] = array('value' => $sec_v, 'label' => $CacheList['job_name'][$sec_v]);
                    if ($CacheList['job_type'][$sec_v]) {// 三级
                        $jobtypes[$fir_v]['children'][$sec_v]['children'] = array();
                        foreach ($CacheList['job_type'][$sec_v] as $thi_v) {
                            $jobtypes[$fir_v]['children'][$sec_v]['children'][$thi_v] = array('value' => $thi_v, 'label' => $CacheList['job_name'][$thi_v]);
                        }
                    }
                }
            }
        }
        $jobtypes = array_values($jobtypes);
        foreach ($jobtypes as $k => $v) {
            if ($v['children']) {
                $jobtypes[$k]['children'] = array_values($v['children']);
                foreach ($jobtypes[$k]['children'] as $kk => $vv) {
                    if ($vv['children']) {
                        $jobtypes[$k]['children'][$kk]['children'] = array_values($vv['children']);
                    }
                }
            }
        }
        return $jobtypes;
    }
	function status_action(){
     
		$JobM  =   $this -> MODEL('job');
		if($_POST['pid']){
            $id                    =   @explode(',',$_POST['pid']);
            $data['state']         =   $_POST['status'];
            $data['statusbody']    =   $_POST['statusbody'];
            $data['lastupdate']    =   time();
            $nid                   =   $JobM -> upInfo($data, array('id' => array('in', pylode(',', $id))));
            $List					=	$JobM->getList(array('id'=>array('in',$_POST['pid'])) , array('field'=>'`id`,`uid`,`name`'));
            /* 消息前缀 */
            $tagName  				=	'职位';
            foreach($List as $v){
                 $uids[]  =  $v['uid'];
                /* 处理审核信息 */
                if ($_POST['status'] == 3){
                    $statusInfo  =  $tagName.':<a href="comjobtpl,'.$v['id'].'">'.$v['name'].'</a>审核未通过';
                    if($_POST['statusbody']){
                        $statusInfo  .=  ' , 原因：'.$_POST['statusbody'];
                    }
                    $msg[$v['uid']][]  =  $statusInfo;
                }elseif($_POST['status'] == 1){
                    $msg[$v['uid']][]  =  $tagName.':<a href="comjobtpl,'.$v['id'].'">'.$v['name'].'</a>已审核通过';
                }
            }
            //发送系统通知
            $sysmsgM	=	$this->MODEL('sysmsg');
            $sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>4, 'content'=>$msg));
            if (isset($_POST['single'])){
                if ($nid){
                    if($_POST['atype'] == 1){
                        // 仅保存
                        $this->render_json(0, '职位审核设置成功');
                    }else{
                        // 下一个待审核职位
                        $jobM  =  $this->MODEL('job');
                        $row   =  $jobM->getInfo(array('is_graduate'=>1,'state'=>0,'orderby'=>array('lastupdate,DESC')), array('field'=>'id'));
                        if (!empty($row)){
                            $this->render_json(0, '职位审核设置成功',array('id'=>$row['id']));
                        }else{
                            $this->render_json(0, '职位审核设置成功');
                        }
                    }
                }else{
                    $this->render_json(1, '职位审核设置失败');
                }
            }else{
                $this->render_json($nid? 0: 1, $nid?'职位审核设置成功':'设置失败');
            }
	    }else{
            $this->render_json(1, '非法操作');
		}
	}
    function cjobstatus_action(){
	    $userinfoM  =  $this -> MODEL('userinfo');
        $post       =  array(
            'status'     =>  intval($_POST['r_status']),
	        'lock_info'  =>  trim($_POST['statusbody'])
        );
        $return        =   $userinfoM -> status(array('uid' =>$_POST['cuid'],'usertype'=>2),array('post'=>$post));
        if($_POST['r_status']==1){
            $jobM       =   $this->MODEL('job');
            $statusData = array(
                'state'         =>  1,
                'statusbody'    =>  trim($_POST['statusbody'])
            );
            $jobM -> statusJob($_POST['cid'], $statusData);
        }

        if (isset($_POST['single'])){
            if ($return['errcode'] == 9){
                if($_POST['atype'] == 1){
                    // 仅保存
                    $this->render_json(0, $return['msg']);
                }else{
                    // 下一个待审核职位
                    $row   =  $jobM->getInfo(array('is_graduate'=>1,'state'=>0,'orderby'=>array('lastupdate,DESC')), array('field'=>'id'));
                    if (!empty($row)){
                        $this->render_json(0, $return['msg'],array('id'=>$row['id']));
                    }else{
                        $this->render_json(0, $return['msg']);
                    }
                }
            }else{
                $this->render_json(1, $return['msg']);
            }
        }else{
            if($return['errcode']==9){
                $this->render_json(0, '审核成功');
            }else{
                $this->render_json(1, '审核失败');
            }
        }
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

		$listA            =   $JobM -> getList(array('id' => array('in',pylode(',',$id))), array('cache'=>'1','field'=>'id,uid,name'));

		$nid              =   $JobM -> upInfo($data, array('id' => array('in',pylode(',',$id))));

		$job              =   $listA['list'];
		$cache            =   $listA['cache'];
      
		if($job){
			$msg          =   array();
			$uids         =   array();
          
			//  提取职位uid 和职位名称
			foreach ($job   as  $k => $v){
				  
				$uids[] =  $v['uid'];

				$msg[$v['uid']][]	=  	'您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>管理员已修改，行业类别为：'.$cache['industry_name'][$_POST['hy']].'，职位类别为：'.$cache['job_name'][$_POST['job1']];

				if($_POST['job1_son']){
				  $msg[$v['uid']][]	.= 	''.$cache[job_name][$_POST['job1_son']];
				}
				if($_POST['job_post']){
				  $msg[$v['uid']][]	.= 	''.$cache[job_name][$_POST['job_post']];
				}
			}
			   
			$sysmsgM    =   $this -> MODEL('sysmsg');
			$sysmsgM    ->  addInfo(array('uid'=>$uids,'usertype'=>4, 'content'=>$msg));
		}
		$nid?$this->ACT_layer_msg('职位类别(ID:'.$_POST['jobid'].')修改成功！',9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg('修改失败！',8,$_SERVER['HTTP_REFERER']);
	}
	// 	职位置顶
	function xuanshang_action(){
        $id     =   trim($_POST['pid']);
        $data   =   array(
            'top'   =>  intval($_POST['s']),
            'days'  =>  intval($_POST['addday'])
        );
        $JobM   =   $this -> MODEL('job');
        $arr    =   $JobM -> addTopJob($id, $data);
        $this->render_json($arr['errcode']==9?0:1, $arr['msg']);
	}
	//  职位推荐
	function recommend_action(){
	    $id     =   trim($_POST['pid']);
	    $data   =   array(
	        'rec'   =>  intval($_POST['s']),
	        'days'  =>  intval($_POST['addday'])
	    );
	    $JobM   =   $this -> MODEL('job');
	    $arr    =   $JobM -> addRecJob($id, $data);
        $this->render_json($arr['errcode']==9?0:1, $arr['msg']);
	}
	//  职位紧急招聘
	function urgent_action(){
	    $id     =   trim($_POST['pid']);
	    $data   =   array(
	        'urgent'   =>  intval($_POST['s']),
	        'days'     =>  intval($_POST['addday'])
	    );
	    
	    $JobM   =   $this -> MODEL('job');
	    $arr    =   $JobM -> addUrgentJob($id, $data);
        $this->render_json($arr['errcode']==9?0:1, $arr['msg']);
	}
	function del_action(){
		$JobM	=	$this -> Model('job');
		$delID	=	$_POST['del'];
		$addArr	=	$JobM -> delJob($delID,array('utype'=>'admin'));
        $this->render_json($addArr['errcode']==9?0:1, $addArr['msg']);
	}
	function refresh_action(){
    
		$JobM      =   $this -> MODEL('job');		
		$ids       =   @explode(',', $_POST['ids']);	
		$return    =   $JobM -> upInfo(array('lastupdate'=>time()), array('id' => array('in',pylode(',',$ids))));
		$this->MODEL('log')->addAdminLog("职位(ID".$_POST['ids']."刷新成功");
	}
	function xls_action(){
    
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
	                    $langs[]   =   $cache['comclass_name'][$val];
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
                $logMsg =   "校招职位(ID" . $_POST['id'] . ")下架成功";
            }else{
                $logMsg =   "校招职位(ID" . $_POST['id'] . ")上架成功";
            }
            $this->MODEL('log')->addAdminLog($logMsg);
        }
        $this->render_json(0, $logMsg);
    }
	

	function sjobNum_action(){
    
		$MsgNum = $this->MODEL('msgNum');
		echo $MsgNum->sjobNum();
	}

    // 职位单个审核，带职位详情
    function jobAudit_action(){
        $jobid =  intval($_POST['id']);
        $JobM  =  $this->MODEL('job');
        $info  =  $JobM->getInfo(array('id' => $jobid, 'is_graduate' => 1),array('com'=>'yes'));

        if ($info['is_link'] == 2){
            $link               =   $this->obj->select_once('company_job_link', array('id' => $info['link_id']));
            $info['tel']        =   $link['link_moblie'];
            $info['phone']      =   $link['link_phone'];
            $info['address']    =   $link['link_address'];
            $info['linkman']    =   $link['link_man'];
        }else{
            $comInfo            =   $this->obj->select_once('company', array('uid' => $info['uid']), '`linkman`, `linktel`,`linkphone`,`linkmail`, `address`');
            $info['tel']        =   $comInfo['linktel'];
            $info['phone']      =   $comInfo['linkphone'];
            $info['address']    =   $comInfo['address'];
            $info['linkman']    =   $comInfo['linkman'];
        }
        $info['crm_name']   =   '';
        if ($info['crm_uid']) {
            $crmuser = $this->obj->select_once('admin_user', array('uid' => $info['crm_uid']), '`uid`,`name`');
            $info['crm_name'] = $crmuser['name'] ? $crmuser['name'] : '';
        }
        $cacheM    =  $this->MODEL('cache');
        $cacheList =  $cacheM->GetCache('com');
        $comdata = $cacheList['comdata'];
        $comclass_name = $cacheList['comclass_name'];
        $com_sex = $cacheList['com_sex'];

        // 待审核数量
        $snum = $JobM->getJobNum(array('is_graduate' => 1,'state'=>0,'id'=>array('<>', $jobid)));
        $this->render_json(0, 'ok', compact('info','comdata','comclass_name','com_sex','snum'));
    }
}
?>