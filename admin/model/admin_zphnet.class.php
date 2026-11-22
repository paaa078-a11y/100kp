<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class admin_zphnet_controller extends adminCommon{
	//设置高级搜索功能
	function set_search(){

		$search_list[]	=	array('param' => 'status','name' => '状态','value' => array('3' => '未开始','1' => '已开始','2' => '已结束'));

		$this -> yunset('search_list',$search_list);

	}

	function index_action(){

		$this -> set_search();
        session_start();
        // 修改成功之后跳转到列表页使用session中记录的上次进入列表页的请求参数
        if (isset($_GET['remember_prev']) && $_GET['remember_prev']) {
            foreach ($_SESSION['prev_zphnetlist'] as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        // 本次请求的参数保存到session中，供下次修改页面返回列表页使用
        $_SESSION['prev_zphnetlist'] = $_GET;
		if($_GET['keyword']){

			$where['title']		    =  array('like',trim($_GET['keyword']));

			$urlarr['keyword']		=  $_GET['keyword'];
		}

		if($_GET['status'] == '3'){

			$where['starttime']		=	array('unixtime','>',time());

			$urlarr['status']		=	$_GET['status'];

		}elseif($_GET['status'] == '1'){

			$where['starttime']		=	array('unixtime','<',time());

			$where['endtime']		=	array('unixtime','>',time());

			$urlarr['status']=$_GET['status'];

		}elseif($_GET['status'] == '2'){

			$where['endtime']		=	array('unixtime','<',time());

			$urlarr['status']		=	$_GET['status'];

		}


		//排序
		if($_GET['order']){

			$where['orderby'] = $_GET['t'].','.$_GET['order'];
			$urlarr['order']  =	$_GET['order'];
			$urlarr['t']	  =	$_GET['t'];

		}else{
			$where['orderby']		=  'id';
		}
		$urlarr        	=   $_GET;
		$urlarr['page']	=	'{{page}}';
        // 删除记住上一次请求参数，防止生成的分页中含有该参数影响页面跳转
        if (isset($urlarr['remember_prev'])) {
            unset($urlarr['remember_prev']);
        }

		$pageurl		=	Url($_GET['m'],$urlarr,'admin');

		$pageM			=	$this -> MODEL('page');

		$pages			=	$pageM -> pageList('zphnet',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){

			$where['limit']	   =  $pages['limit'];
			$zphnetM	       =  $this->MODEL('zphnet');
			$rows  			   =  $zphnetM -> getList($where,array('utype'=>'admin'));
			$class  =  $zphnetM->getClassList(array('keyid'=>0));
			foreach ($class as $v){
			    $area[$v['id']]  =  $v['name'];
			}
			$this->yunset('area',$area);
		}
		//提取分站内容
		$cacheM  =	$this -> MODEL('cache');
		$domain  =	$cacheM	-> GetCache('domain');

		$this -> yunset('Dname', $domain['Dname']);

		$this -> yunset('rows',$rows);

		$this -> yuntpl(array('admin/admin_zphnet_list'));
	}

	function add_action(){

		//提取分站内容
		$cacheM  =	$this -> MODEL('cache');
		$domain  =	$cacheM	-> GetCache('domain');

		$this -> yunset('Dname', $domain['Dname']);

		$zphnetM	=	$this->MODEL('zphnet');

		if(intval($_GET['id'])){

		    $info	=	$zphnetM -> getInfo(array('id' => intval($_GET['id'])));
            
		    $this -> yunset('info',$info);
			$this -> yunset('lasturl',$_SERVER['HTTP_REFERER']);
		}
		$class  =  $zphnetM->getClassList(array('keyid'=>0));
		$this->yunset('class',$class);

		$this -> yuntpl(array('admin/admin_zphnet_add'));
	}
	//后台修改参会企业职位
    function upjob_action(){
        if ($_POST['zphjob']){
            $value = array(
                'jobid'=>pylode(',',$_POST['zphjob'])
            );
            $zphM = $this->MODEL('zphnet');
            $nid = $zphM->upZphnetComSort($_POST['zcomid'],$value);
            $nid?$this->ACT_layer_msg("招聘会企业职位修改成功！",9,1,2,1):$this->ACT_layer_msg("招聘会企业职位修改失败！",8,$_SERVER['HTTP_REFERER']);
        }else{
            $this -> ACT_layer_msg("请选择参会职位！",8);
        }
    }
    //根据选择的企业展示前台可以显示的职位列表
    function getjoblist_action(){
        $JobM	=	$this->MODEL('job');
        $comid 	= 	intval($_POST['comid']);
        $type = $_POST['type'];
        if($type=='admin'){
            $zphnetM = $this->MODEL('zphnet');
            $comzph = $zphnetM->getZphnetCom(array('zid'=>$_POST['zid'],'uid'=>$comid));
            $jobids = explode(',',$comzph['jobid']);
            $rows = $JobM->getList(array('uid' => $comid, 'state' => '1', 'r_status' => array('<>', '2'), 'status' => array('<>', '1')));
            $html = '';
            foreach ($rows['list'] as $v) {
                if (in_array($v['id'], $jobids)) {
                    $html .= '<input type="checkbox" lay-skin="primary" checked="checked" name="zphjob[]" value="' . $v['id'] . '" title="' . $v['name'] . '">';
                } else {
                    $html .= '<input type="checkbox" lay-skin="primary" name="zphjob[]" value="' . $v['id'] . '" title="' . $v['name'] . '">';
                }
            }
        }else {
            $rows = $JobM->getList(array('uid' => $comid, 'state' => '1', 'r_status' => array('<>', '2'), 'status' => array('<>', '1')));
            $html = '';
            foreach ($rows['list'] as $v) {
                $html .= '<input type="checkbox" name="zphjob[]" value="' . $v['id'] . '" title="' . $v['name'] . '">';
            }
        }
        echo $html;
    }
	function save_action(){

		$zphnetM	=	$this -> MODEL('zphnet');

		if($_FILES){
		    $upData = [];
			foreach ($_FILES as $key => $value) {
				if ($value['tmp_name']) {
					$upData[$key]['file'] = $_FILES[$key];
					$upData[$key]['dir'] = 'zhaopinhui';
				}
			}

		    $arr = [
		    	'sl' 	=> 'pic',
		    	'hf' 	=> 'banner',
		    	'wapsl' => 'pic_wap',
		    	'waphf' => 'banner_wap',
		    ];
	
		    if ($upData) {
		    	$uploadM  =  $this->MODEL('upload');
		    	foreach ($upData as $key => $value) {
			    	$upRes = $uploadM->newUpload($value);
			    	if ($upRes && !empty($upRes['msg'])) {
			    		$return['msg']	    =  $upRes['msg'] ? $upRes['msg'] : $upRes['msg'];
			        	$this -> ACT_layer_msg($return['msg'],8);
			    	} else {
			    		$_POST[$arr[$key]] = $upRes['picurl'];
			    	}
			    }
		    }
		}

		if($_POST['time']){

			$times	=	@explode('~',$_POST['time']);

			$_POST['starttime']	=	trim($times[0]);

			$_POST['endtime']	=	trim($times[1]);

			if(strtotime($_POST['starttime'])>strtotime($_POST['endtime'])){

			    $this -> ACT_layer_msg('开始时间不得大于结束时间',8);
			}

			unset($_POST['time']);
		}
		$_POST['body']			=	str_replace("&amp;","&",$_POST['body']);

		if($_POST['id']){

		    $nbid	=	$zphnetM -> upInfo(array('id'=>intval($_POST['id'])),$_POST);

			isset($nbid)?$this->ACT_layer_msg("网络招聘会(ID:".$_POST['id'].")修改成功！",9,"index.php?m=admin_zphnet&remember_prev=1",2,1):$this->ACT_layer_msg("网络招聘会(ID:".$_POST['id'].")修改失败！",8,"index.php?m=admin_zphnet&remember_prev=1");

		}else{

		    $nbid	=	$zphnetM -> addInfo($_POST);

			isset($nbid)?$this->ACT_layer_msg("网络招聘会(ID:$nbid)添加成功！",9,"index.php?m=admin_zphnet",2,1):$this->ACT_layer_msg("网络招聘会(ID:$nbid)添加失败！",8,"index.php?m=admin_zphnet");
		}

	}

	function del_action(){

		if($_GET['id']){

			$this -> check_token();

			$delID   =  intval($_GET['id']);

		}elseif($_POST['del']){

			$delID   =  $_POST['del'];
		}

		$zphnetM  =  $this -> MODEL('zphnet');

		$return	  =  $zphnetM -> delZph($delID);

		$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}

	/**
	 * @desc 报名企业，高级搜索功能
	 */
	function set_searchs(){

		$search_list[]	=	array("param"=>"status","name"=>'审核状态',"value"=>array("3"=>"未审核","1"=>"已通过","2"=>"未通过"));

		$this -> yunset("search_list",$search_list);

	}
    function user_action(){
        $zphnetM  =  $this->MODEL('zphnet');
        if($_GET['id']){

            $where['zid']	=	intval($_GET['id']);

            $urlarr['id']	=	$_GET['id'];

        }

        if (trim($_GET['keyword'])){

            $resumeM  =  $this->MODEL('resume');
            $resume   =  $resumeM->getResumeInfo(array('name'=>array('like',trim($_GET['keyword']))),array('field'=>'uid'));

            if($resume){

                foreach($resume as $v){

                    $uid[]	=	$v['uid'];
                }
                $where['uid']	=	array('in',pylode(',', $uid));
            }
            $urlarr["keyword"]	=	$_GET["keyword"];
        }

        $urlarr['c']	 =	$_GET['c'];
		$urlarr        	 =   $_GET;
        $urlarr['page']  =	"{{page}}";
        $where['usertype'] = 1;

        $pageurl  =  Url($_GET['m'],$urlarr,'admin');
        $pageM	  =	 $this  -> MODEL('page');

        $pages	  =	 $pageM -> pageList('zphnet_user',$where,$pageurl,$_GET['page']);
        if($pages['total'] > 0){
            $where['limit']	   =  $pages['limit'];

            $rows  =  $zphnetM -> getZphnetUser($where,array('resume'=>$where['limit'],'keyword'=>$urlarr['keyword'],'utype'=>'admin'));

            $this -> yunset("rows",$rows);
        }
        $this -> yuntpl(array('admin/admin_zphnet_user'));
    }
	function com_action(){

		$this->set_searchs();

		$zphnetM  =  $this->MODEL('zphnet');
		$type	  =	 array('1'=>'招聘会名称','2'=>'企业名称');

		$this -> yunset('type',$type);
		session_start();
        if (isset($_GET['re_zphnet_prev']) && $_GET['re_zphnet_prev']) {
            foreach ($_SESSION['prev_zphnetcomlist'] as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_SESSION['prev_zphnetcomlist'] = $_GET;
		if($_GET['id']){

			$where['zid']	=	intval($_GET['id']);

			$urlarr['id']	=	$_GET['id'];

		}

		if($_GET['status']){

			if($_GET['status']=="3"){

				$where['status']	=	0;

			}elseif($_GET['status']=="1"){

				$where['status']	=	1;

			}elseif($_GET['status']=="2"){

				$where['status']	=	2;

			}
			$urlarr['status']	=	$_GET['status'];
		}

		if (trim($_GET['keyword'])){

		    $companyM  =  $this->MODEL('company');
		    $company   =  $companyM->getChCompanyList(array('name'=>array('like',trim($_GET['keyword']))),array('field'=>'uid'));

		    if($company){

		        foreach($company as $v){

		            $uid[]	=	$v['uid'];
		        }
		        $where['uid']	=	array('in',pylode(',', $uid));
		    }
		    $urlarr["keyword"]	=	$_GET["keyword"];
		}

		$urlarr['c']	 =	$_GET['c'];
		$urlarr        	 =   $_GET;
		$urlarr['page']  =	"{{page}}";

		$pageurl  =  Url($_GET['m'],$urlarr,'admin');
		$pageM	  =	 $this  -> MODEL('page');
		$pages	  =	 $pageM -> pageList('zphnet_com',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){

		    if($_GET['order']){
		        
		        $where['orderby']		=	$_GET['t'].','.$_GET['order'];
		        $urlarr['order']		=	$_GET['order'];
		        $urlarr['t']			=	$_GET['t'];
		    }else{
		        $where['orderby']  =  array('status,asc','id,desc');
		    }

		    $where['limit']	   =  $pages['limit'];

		    $rows  =  $zphnetM -> getZphnetComList($where,array('utype'=>'admin'));

		    session_start();

			$_SESSION['zphXLs']	=	$where;
		}

		$this -> yunset("rows",$rows);
		
		$zphnet  =  $zphnetM->getInfo(array('id'=>intval($_GET['id'])),array('field'=>'`zw`,`title`'));
		
		$noarea  =  0;
		if (!empty($zphnet['zw'])){
		    $class  =  $zphnetM->getClassList(array('keyid'=>$zphnet['zw']));
		    $area   =  array();
		    foreach ($class as $v){
		        $area[$v['id']]  =  $v['name'];
		    }
		    if(!empty($area)){
		        $this->yunset('area',$area);
		    }else{
		        $noarea  =  2;
		    }
		}else{
		    $noarea  =  1;
		}
		$this->yunset('noarea',$noarea);
		$this->yunset('zphnet',$zphnet);
		
		$this -> yuntpl(array('admin/admin_zphnet_com'));

	}

	function sbody_action(){

		$zphnetM  =	 $this -> MODEL('zphnet');
		$com	  =  $zphnetM->getZphnetCom(array('id'=>intval($_POST['id'])),array('field'=>'statusbody'));

		echo $com['statusbody'];die;

	}

	function status_action(){

	    $zphnetM  =	 $this -> MODEL('zphnet');

		$data['status']		=	$_POST['status'];

		$data['statusbody']	=	trim($_POST['statusbody']);

		$id					=	@explode(",",$_POST['pid']);

		$nid				=	$zphnetM -> zphnetComStatus($id,$data, array('utype' => 'admin'));

        if (isset($_POST['single'])){
            if ($nid){
                $this->layer_msg("网络招聘会报名企业(ID:".$_POST['pid'].")审核设置成功！",9);
            }else{
                $this->layer_msg("设置失败！",8);
            }
        }else{
            $nid?$this->ACT_layer_msg("网络招聘会报名企业(ID:".$_POST['pid'].")审核设置成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg("设置失败！",8,$_SERVER['HTTP_REFERER']);
        }
	}

    function audit_action(){
        $id =  intval($_GET['id']);

        $zphnetM =	 $this -> MODEL('zphnet');
        $ComM    =   $this -> MODEL('company');

        $zphnetCom	  =  $zphnetM->getZphnetCom(array('id'=>$id),array('field'=>'id,uid,status,statusbody'));

        $Info  =  $ComM->getInfo($zphnetCom['uid']);

        $Info['zphnet'] = $zphnetCom;

        $this->yunset('Info', $Info);

        $this->yuntpl(array('admin/admin_zphnet_comaudit'));
    }

    function userxls_action(){
        if($_POST['zid']&&$_POST['cid']) {
            $resumeM = $this->MODEL('resume');
            $where['usertype'] = 1;

            $rwhere = array('uid' => array('in', $_POST['cid']),'defaults' => 1);
            $rows = $resumeM->getList($rwhere,array('utype'=>'zphnet'));
            $this->yunset('list', $rows['list']);

            $this->MODEL('log')->addAdminLog('导出参与网络招聘会个人信息');

            header('Content-Type: application/vnd.ms-excel');

            header('Content-Disposition: attachment; filename=zphnetuser.xls');

            $this->yuntpl(array('admin/admin_zphnet_userxls'));
        }else{
            $this->ACT_layer_msg('没有可以导出的参会个人信息！',8,$_SERVER['HTTP_REFERER']);
        }
    }

    function comxls_action()
    {

        $zphnetM    =   $this->MODEL('zphnet');
        $CompanyM   =   $this->MODEL('company');
        $JobM       =   $this->MODEL('job');
        if ($_POST['zid']) {
            if ($_POST['cid']) {

                $zcwhere    =   array('id' => array('in', $_POST['cid']));
            } else {

                $zcwhere    =   array('zid' => $_POST['zid']);
            }

            $rows   =   $zphnetM->getZphnetComList($zcwhere);

            if (!empty($rows)) {

                $cacheM         =   $this->MODEL('cache');
                $cache          =   $cacheM->getCache(array('com'));
                $comclass_name  =   $cache['comclass_name'];

                $jobids = $jobuids = $comids = array();

                $title  = '';
                foreach ($rows as $key => $val) {

                    $comids[]   =   $val['uid'];

                    if ($val['jobid']) {

                        $jobids[]   =   $val['jobid'];
                    } else {

                        $jobuids[]  =   $val['uid'];
                    }

                    $title  =   $val['title'];
                }

                $comField   =   '`uid`,`name`,`mun`,`content`,`address`,`linktel`,`linkman`,`linkphone`,`welfare`,`money`,`moneytype`';
                $companys   =   $CompanyM->getChCompanyList(array('uid' => array('in', @implode(',', $comids))), array('field' => $comField));

                $jobField   =   '`id`,`uid`,`name`,`zp_num`,`minsalary`,`maxsalary`,`exp`,`edu`,`provinceid`,`cityid`,`three_cityid`';

                $jobWhere['state']      =   1;
                $jobWhere['status']     =   0;
                $jobWhere['r_status']   =   1;

                $jobWhere['PHPYUNBTWSTART'] =   '';
                $jobWhere['uid']            =   array('in', pylode(',', $jobuids));
                $jobWhere['id']             =   array('in', pylode(',', $jobids), 'OR');
                $jobWhere['PHPYUNBTWEND']   =   '';

                $jobsA  =   $JobM->getList($jobWhere, array('field' => $jobField));
                $jobs   =   $jobsA['list'];
                foreach ($companys as $k => $va) {

                    $companys[$k]['content']=   trim(strip_tags($va['content']));
                    $companys[$k]['mun']    =    $comclass_name[$va['mun']];
                    foreach ($jobs as $val) {
                        if ($va['uid'] == $val['uid']) {

                            $companys[$k]['jobs'][] =   $val;
                        }
                    }
                }
                $maxJobNum  =   1;
                foreach ($companys as $v) {
                    $jobnum =   count($v['jobs']);
                    if ($jobnum > $maxJobNum) {
                        $maxJobNum  =   $jobnum;
                    }
                }
                $this->yunset(array('list' => $companys, 'title' => $title));

                $this->MODEL('log')->addAdminLog('导出报名网络招聘会信息');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename=zphnetcom.xls');
                $this->yuntpl(array('admin/admin_zphnet_comxls'));
            } else {
                $this->ACT_layer_msg('没有可以导出的参会企业信息！', 8, $_SERVER['HTTP_REFERER']);
            }
        }
    }

    function deluser_action(){

        $zphnetM	=	$this->MODEL('zphnet');

        if($_GET['delid']){

            $this -> check_token();

            $delID	=  intval($_GET['delid']);
            $zid    =  intval($_GET['zid']);

        }elseif($_POST['del']){

            $delID	=	$_POST['del'];
            $zid    =  intval($_POST['zid']);
        }

        $arr	=	$zphnetM -> delZphnetUser($delID);

        $this -> layer_msg($arr['msg'], $arr['errcode'], $arr['layertype'],$_SERVER['HTTP_REFERER']);

    }
	//删除链接
	function delcom_action(){

		$zphnetM	=	$this->MODEL('zphnet');

		if($_GET['delid']){

			$this -> check_token();

			$delID	=  intval($_GET['delid']);
			$zid    =  intval($_GET['zid']);

		}elseif($_POST['del']){

			$delID	=	$_POST['del'];
			$zid    =  intval($_POST['zid']);
		}

		$arr	=	$zphnetM -> delZphnetCom($delID);

		$this -> layer_msg($arr['msg'], $arr['errcode'], $arr['layertype'],$_SERVER['HTTP_REFERER']);

	}

	function checksitedid_action(){

		if($_POST['uid']){

			$uids	=	@explode(',', $_POST['uid']);

			$uid	= 	pylode(',', $uids);

			if($uid){

				$siteDomain		=	$this -> MODEL('site');

				$siteDomain -> updDid(array('zphnet'), array('id' => array('in', $uid)), array('did' => $_POST['did']));

				$siteDomain -> updDid(array('zphnet_com'),  array('zid' => array('in', $uid)),array('did' => $_POST['did']));

				$this -> ACT_layer_msg('网络招聘会(ID:'.$_POST['uid'].')分配站点成功！', 9, $_SERVER['HTTP_REFERER']);

			}else{

				$this -> ACT_layer_msg('请正确选择需分配数据！', 8, $_SERVER['HTTP_REFERER']);

			}

		}else{

			$this -> ACT_layer_msg('参数不全请重试！', 8, $_SERVER['HTTP_REFERER']);
		}
	}

	function set_comaddsearch(){

        $ratingM    =   $this -> MODEL('rating');

        $rating     =   $ratingM -> getList(array( 'category' => '1', 'orderby' => 'sort' ), array('field'=>'`id`,`name`'));

		if(!empty($rating)){

		    $ratingarr    =   array();

			foreach($rating  as  $k => $v){

                $ratingarr[$v['id']]   =   $v['name'];

			}
		}

        include(CONFIG_PATH.'db.data.php');

        $source             =   $arr_data['source'];

        $timeSection        =   array('1'=>'今天', '3'=>'3天内', '7'=>'7天内', '15'=>'半月内', '30'=>'1个月内', '31'=> '1-3个月', '32' => '3-6个月', '33' => '6个月-1年', '34' => '1年以上');

        $status             =   array('1'=>'已审核', '2'=>'已锁定', '3'=>'未通过', '4'=>'未审核','5'=>'已暂停');

        $edtime             =   array('1'=>'7天内', '2'=>'一个月内', '3'=>'半年内', '4'=>'一年内', '5'=>'已到期');

        $isrec              =   array('1'=>'是', '2'=>'否', '3'=>'已到期');

        $isgw               =   array('1'=>'已分配', '2'=>'未分配');

        $search_list        =   array();

        $search_list[]      =   array('param'=>'rating','name'=>'会员等级','value'=>$ratingarr);

        $search_list[]      =   array('param'=>'time','name'=>'到期时间','value'=>$edtime);

        $search_list[]      =   array('param'=>'status','name'=>'审核状态','value'=>$status);

        $search_list[]      =   array('param'=>'source','name'=>'数据来源','value'=>$source);

        $search_list[]      =   array('param'=>'rec','name'=>'知名企业','value'=>$isrec);

        $search_list[]      =   array('param'=>'gw','name'=>'企业顾问','value'=>$isgw);

        $search_list[]      =   array('param'=>'lotime','name'=>'最近登录','value'=>$timeSection);

        $search_list[]      =   array('param'=>'adtime','name'=>'最近注册','value'=>$timeSection);

        $this -> yunset(array('source' => $source, 'ratingarr' => $ratingarr, 'search_list' => $search_list));

	}

	
	function comadd_action(){

	    $ComM          =   $this -> MODEL('company');

	    $where         =   array('r_status'=>1);

	    $mwhere        =   array();

	    $urlarr        =   $_GET;

		if($_GET['keyword']){

			$keywordStr	   =   trim($_GET['keyword']);

			$typeStr       =   intval($_GET['type']);

			if (!empty($keywordStr) && $typeStr == 1) {

                $where['PHPYUNBTWSTART_C']   = '';
                $where['name']               = array('like',$keywordStr);
                $where['shortname']          = array('like',$keywordStr,'OR');
                $where['PHPYUNBTWEND_C']     = '';

			}elseif (!empty($keywordStr) && $typeStr == 2){

				$mwhere['username']     =   array('like', $keywordStr);

			}else if (!empty($keywordStr) && $typeStr == 3) {

				$where['linkman']       =   array('like', $keywordStr);

			}else if (!empty($keywordStr) && $typeStr == 4) {

				$where['linktel']       =   array('like', $keywordStr);

			}else if (!empty($keywordStr) && $typeStr == 5) {

				$where['linkmail']      =   array('like', $keywordStr);

			}else  if (!empty($keywordStr) && $typeStr == 6){

				$where['uid'][]         =   array('=', $keywordStr);

			}

			$urlarr['type']			=	$typeStr;

	    	$urlarr['keyword']		=	$keywordStr;

		}

	    if ($_GET['status']) {

            $status                 =   intval($_GET['status']);

	        if($status == 4){
	        	$where['r_status']      =   0;
	        }else if($status == 5){
	        	$where['r_status']      =   4;
	        }else{
	        	$where['r_status']      =   $status;
	        }

            $urlarr['r_status']     =   $status;

	    }

	    if ($_GET['adtime']) {

	        $adtime    =   intval($_GET['adtime']);

	        if($adtime == 1){

                $mwhere['reg_date']     =   array('>',  strtotime('today'));

	        }else if ($adtime < 31){

	            $mwhere['reg_date']  =   array('>',  strtotime('-'.$adtime.' day'));

	        }else if ($adtime == 31){// 1 - 3 个月
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['reg_date'][]  =   array('<',  strtotime('-1 month'));
	            $mwhere['reg_date'][]  =   array('>=',  strtotime('-3 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($adtime == 32){// 3 - 6个月
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['reg_date'][]  =   array('<',  strtotime('-3 month'));
	            $mwhere['reg_date'][]  =   array('>=',  strtotime('-6 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($adtime == 33){// 6个月 - 1年
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['reg_date'][]  =   array('<',  strtotime('-6 month'));
	            $mwhere['reg_date'][]  =   array('>=',  strtotime('-12 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($adtime == 34){// 1年以上
	            $mwhere['reg_date']  =   array('<',  strtotime('-1 year'));
	        }

	        $urlarr['adtime']       =   $adtime;

	    }

	    if($_GET['lotime']){

	        $lotime    =   intval($_GET['lotime']);

	        if($lotime ==  1){

	            $mwhere['login_date']  =   array('>',  strtotime('today'));

	        }else if ($lotime < 31){

	            $mwhere['login_date']  =   array('>',  strtotime('-'.$lotime.' day'));

	        }else if ($lotime == 31){
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['login_date'][]  =   array('<',  strtotime('-1 month'));
	            $mwhere['login_date'][]  =   array('>=',  strtotime('-3 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($lotime == 32){
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['login_date'][]  =   array('<',  strtotime('-3 month'));
	            $mwhere['login_date'][]  =   array('>=',  strtotime('-6 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($lotime == 33){
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['login_date'][]  =   array('<',  strtotime('-6 month'));
	            $mwhere['login_date'][]  =   array('>=',  strtotime('-12 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($lotime == 34){
	            $mwhere['login_date']  =   array('<',  strtotime('-1 year'));
	        }

	        $urlarr['lotime']          =   $lotime;

	    }

	    if($_GET['source']){

	        $mwhere['source']          =   $_GET['source'];

	        $urlarr['source']          =   $_GET['source'];

	    }

	    $mUids		=	array();

	    $UserinfoM	=	$this -> MODEL('userinfo');

	    if(!empty($mwhere)){

	        $uidList	=	$UserinfoM  ->  getList($mwhere, array('field' => '`uid`'));

	        if(!empty($uidList)){

	            foreach($uidList as $uv){

	                $mUids[]	=	$uv['uid'];

	            }

	        }else{

	            $mUids			=	array(0);

			}

			$where['uid'][] =	array('in', pylode(',',$mUids));

	    }

        if($_GET['rating']){

            $where['rating']   =   $_GET['rating'];

            $urlarr['rating']   =   $_GET['rating'];

	    }
	    $toDay	=	strtotime(date('Y-m-d'));
	    if($_GET['time']){

	        $time  =   intval($_GET['time']);

	        if($time == 5){

	            $where['PHPYUNBTWSTART_A']    =   '';

	            $where['vipetime'][]         =   array('>', '0','AND');
	            $where['vipetime'][]         =   array('<',  $toDay,'AND');

	            $where['PHPYUNBTWEND_A']      =   '';

	        }else{

	            if($time == 1){

	                $num   =   '+7 day';

	            }elseif($time == 2 ){

	                $num   =   '+1 month';

	            }elseif($time == 3){

	                $num   =   '+6 month';

	            }elseif($time == 4){

	                $num='+1 year';

	            }

	            $where['PHPYUNBTWSTART_B']    =   '';

	            $where['vipetime'][]         =   array('>', time(),'AND');
	            $where['vipetime'][]         =   array('<', strtotime($num),'AND');

	            $where['PHPYUNBTWEND_B']      =   '';

	        }

	        $urlarr['time']    =   $time;

	    }

        if($_GET['rec']){

            $rec    =   intval($_GET['rec']);

            if($rec == 1){

                $where['rec']       =   '1';

                $where['hottime']   =   array('>', time());

            }elseif ($rec == 2){

                $where['rec']       =   '0';

            }elseif ($rec == 3){

                $where['rec']       =   '1';

                $where['hottime']   =   array('<', time());

            }

            $urlarr['rec']          =   $rec;

	    }

	    if($_GET['gw']){

	        if(intval($_GET['gw']) == 1){

                $where['crm_uid']     =   array('<>', '0');

	        }else{

	            $where['crm_uid']     =   '0';
	        }

            $urlarr['gw']           =   $_GET['gw'];

	    }

	    $urlarr['page']	=	'{{page}}';

	    $pageurl		=	Url($_GET['m'], $urlarr, 'admin');

	    //提取分页
	    $pageM			=	$this  -> MODEL('page');


	    $pages			=	$pageM -> pageList('company', $where, $pageurl, $_GET['page']);

	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){

	        //limit order 只有在列表查询时才需要
	        if($_GET['order']){

	            $where['orderby']		=	$_GET['t'].','.$_GET['order'];
	            $urlarr['order']		=	$_GET['order'];
	            $urlarr['t']			=	$_GET['t'];
	        }else if($_GET['time'] == '5'){

	            $where['orderby']		=	array('vipetime,desc','uid,desc');

	        }else if($_GET['time']){

	            $where['orderby']		=	array('vipetime,asc');

	        }else if($_GET['lotime']){

	            $where['orderby']		=	array('login_date,desc');

	        }else{

	            $where['orderby']		=	'uid,desc';
	        }

	        $where['limit']				=	$pages['limit'];

	        $ListNew    =   $ComM -> getList($where,array('utype'=>'admin'));
            // 查询本场网招参会企业，在待添加企业列表中展示，企业是否已参加
	        $zphnetM = $this->MODEL('zphnet');
	        $netcom  = $zphnetM->getZphnetComList(array('zid'=>$_GET['id']),array('field'=>'uid'));
	        if (!empty($netcom)){
	            foreach ($netcom as $v){
	                $ncuid[] = $v['uid'];
	            }
	        }
	        
            foreach ($ListNew['list']  as $key => $val){
                $ListNew['list'][$key]['wxBindmsg'] = $this->wxBindState($val);
                $ListNew['list'][$key]['join'] = 0;
                if (!empty($ncuid)){
                    if (in_array($val['uid'], $ncuid)){
                        $ListNew['list'][$key]['join'] = 1;
                    }
                }
            }

	        unset($where['limit']);

	        $this -> yunset(array('rows'=>$ListNew['list']));

	    }
        
	    //提取分站内容
	    $cacheM					         =	$this -> MODEL('cache');
	    $domain					         =	$cacheM	-> GetCache('domain');

	    $this -> yunset('Dname', $domain['Dname']);

	    $this -> set_comaddsearch();

        $this -> yuntpl(array('admin/admin_zphnet_comadd'));
	}

	//保存单个参会企业
	function comaddsave_action(){

		$zphnetM  =  $this->MODEL('zphnet');

		$_POST['uid']	=	intval($_POST['comid']);

		$_POST['zid']	=	intval($_POST['zphid']);

		$return  =  $zphnetM -> addZphnetCom($_POST);

		echo json_encode($return);exit();
	}
	//保存多个参会企业
	function muticomAddSave_action(){

		$zphnetM  =  $this->MODEL('zphnet');


		$data['comid']	=	$_POST['comid'];

		$data['zid']	=	intval($_POST['zphid']);

		$return  =  $zphnetM -> addZphnetMutiCom($data);

		echo json_encode($return);exit();
	}

	// 添加推荐企业分类
	function getClass_action(){

	    $zphnetM   =  $this->MODEL('zphnet');
	    $where     =  array('keyid'=>0,'orderby'=>'sort,desc');
	    $position  =  $zphnetM->getClassList($where);
	    $this -> yunset('position',$position);
	    $this -> yuntpl(array('admin/admin_zphnet_class'));
	}

	//分类管理
	function classManage_action()
	{
	    $zphnetM   =  $this->MODEL('zphnet');
	    //查询子类别
	    if($_GET['id']){
	        $id					 =	$_GET['id'];
	        $whereOne['id']		 =	$id;
	        $whereTwo['keyid']	 =	$id;
	        $whereTwo['orderby'] =	'sort,desc';
	        $onejob				 = 	$zphnetM->getClass($whereOne);
	        $twojob				 =	$zphnetM->getClassList($whereTwo);
	        $this->yunset('id',$id);
	        $this->yunset('onejob',$onejob);
	        $this->yunset('twojob',$twojob);
	    }
	    $position				=	$zphnetM->getClassList(array('keyid'=>'0'));
	    $this->yunset('position',$position);
	    $this->yuntpl(array('admin/admin_zphnet_class'));

	}

	//添加推荐类别保存
	function saveClass_action(){

	    $arr  =  array(
	        'ctype'  =>  $_POST['stype'],
	        'name'   =>  explode('-',$_POST['name']),
	        'keyid'  =>  $_POST['nid']
	    );

	    $zphnetM  =  $this->MODEL('zphnet');
	    $return   =  $zphnetM->addClass($arr);

	    echo $return['msg'];die;
	}

	// 推荐类别名称修改
	function ajax_action()
	{
	    if($_POST['id']){
	        $zphnetM  =  $this->MODEL('zphnet');

	        if (!empty($_POST['name'])){
	            $uparr['name']  =  $_POST['name'];
	        }
	        if (!empty($_POST['sort'])){
	            $uparr['sort']  =  $_POST['sort'];
	        }

	        $zphnetM->upClass(array('id'=>$_POST['id']),$uparr);
	        echo '1';
	        die;
	    }
	}

	// 删除推荐类别
	function delZphnetClass_action()
	{
	    $whereData	=	array();
	    if(isset($_GET['delid'])){
	        $this->check_token();
	        $delid  =  intval($_GET['delid']);
	    }
	    if($_POST['del']){

	        $delid  =  $_POST['del'];
	    }
	    $zphnetM  =  $this->MODEL('zphnet');
	    $return	  =	 $zphnetM->delClass($delid);

	    $this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}

	// 参会企业分配展会区域
	function setComZw_action()
	{

	    $zphnetM  =  $this->MODEL('zphnet');
	    $return	  =	 $zphnetM->upZphnetCom(array('id'=>array('in',$_POST['zphcomid'])),array('zw'=>$_POST['zw']));
        $class  =  $zphnetM->getClass(array('id'=>$_POST['zw']));
        $return['zwname'] = $class['name']? $class['name'] : '暂无';
        echo json_encode($return);exit();
	}
    //招聘会前台显示情况修改
    function upisopen_action(){
        if($_POST['pid']) {
            $zphnetM = $this->MODEL('zphnet');
            $return = $zphnetM->upIsOpen($_POST['pid'], $_POST['is_open']);
            $this->ACT_layer_msg($return['msg'], $return['errcode'], "index.php?m=admin_zphnet", 2, 1);
        }
    }
	
	function ajaxsort_action(){
		if($_POST['id']){
			$zphnetM  =  $this->MODEL('zphnet');
			$uparr['sort']  =  intval($_POST['sort']);
			$zphnetM->upZphnetCom(array('id'=>$_POST['id']),$uparr);
			echo '1';die;
		}
	}
}

?>