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
class admin_company_controller extends adminCommon{

	function set_search(){

        $ratingM    =   $this->MODEL('rating');
        $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));

		if(!empty($rating)){
		    $ratingarr    =   array();
			foreach($rating  as  $k => $v){

                $ratingarr[$v['id']]   =   $v['name'];
			}
		}

        include(CONFIG_PATH.'db.data.php');

        $source     =   $arr_data['source'];
        $cacheM		=	$this -> MODEL('cache');
        $CacheList	=	$cacheM -> GetCache(array('city'));
        $setArr 	=   array('city_name' => $CacheList['city_name']);
        $this->yunset($setArr);

        $loginTimeArr		=	array('1' => '今天', '2' => '本周', '3' => '本月', '4' => '1个月前', '5' => '3个月前', '6' => '半年前', '7' => '1年前');
        $registerTimeArr	=	array('1' => '今天', '2' => '本周', '3' => '本月', '4' => '1个月内', '5' => '3个月内', '6' => '半年内', '7' => '1年内');

        $status             =   array('1'=>'已审核', '2'=>'已锁定', '3'=>'未通过', '4'=>'未审核','5'=>'已暂停');
        $edtime             =   array('1'=>'7天内', '2'=>'一个月内', '3'=>'半年内', '4'=>'一年内', '5'=>'已到期','6'=>'当月');
        $isrec              =   array('1'=>'是', '2'=>'否', '3'=>'已到期');
        $isgw               =   array('1'=>'已分配', '2'=>'未分配');

        $search_list        =   array();
        $search_list[]      =   array('param'=>'rating','name'=>'会员等级','value'=>$ratingarr);
        $search_list[]      =   array('param'=>'time','name'=>'到期时间','value'=>$edtime);
        $search_list[]      =   array('param'=>'status','name'=>'审核状态','value'=>$status);
        $search_list[]      =   array('param'=>'source','name'=>'数据来源','value'=>$source);
        $search_list[]      =   array('param'=>'rec','name'=>'知名企业','value'=>$isrec);
        $search_list[]      =   array('param'=>'gw','name'=>'企业顾问','value'=>$isgw);
        $search_list[]      =   array('param'=>'lotime','name'=>'最近登录','value'=>$loginTimeArr);
        $search_list[]      =   array('param'=>'adtime','name'=>'最近注册','value'=>$registerTimeArr);
		$search_list[]      =   array('param'=>'has_job','name'=>'拥有职位','value'=>array('1'=>'是','2'=>'否'));
        $this->yunset(array('source' => $source, 'ratingarr' => $ratingarr, 'search_list' => $search_list));
	}

	/**
	 * @desc 后台 企业列表
	 */
	function index_action(){

	    $ComM          =   $this -> MODEL('company');

	    $where         =   array('clue' => 1);

	    $mwhere        =   array();
        $month = get_this_month(time());
	    $urlarr        =   $_GET;
		//判断是否有职位
        if(isset($_GET['has_job'])){
            if($_GET['has_job'] == '1'){
                $where['jobtime'] = array('>',0);// 最近一次发布/更新职位时间
            }elseif($_GET['has_job'] == '2'){
                $where['jobtime'] = 0;
            }
            $urlarr['has_job'] = $_GET['has_job'];
        }
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

			}elseif (!empty($keywordStr) && $typeStr == 7){

                $mwhere['login_ip']     =   array('like', $keywordStr);
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

        if (isset($_GET['lotime']) && !empty($_GET['lotime'])) {

            $lotime	=	intval($_GET['lotime']);

            if ($lotime == 1) {    //	当天

                $where['login_date'] = array('>', strtotime('today'));
            } else if ($lotime == 2) {    //	本周

                $where['PHPYUNBTWSTART_C'] = '';
                $where['login_date'][] = array('>', strtotime('this week 00:00:00'));
                $where['login_date'][] = array('<', time());
                $where['PHPYUNBTWEND_C'] = '';
            } else if ($lotime == 3) {    //	本月

                $where['PHPYUNBTWSTART_C'] = '';
                $where['login_date'][] = array('>=', $month['start_month']);
                $where['login_date'][] = array('<=', $month['end_month']);
                $where['PHPYUNBTWEND_C'] = '';
            } else if ($lotime == 4) {

                $where['login_date'] = array('<', strtotime('-1 month'));
            } else if ($lotime == 5) {

                $where['login_date'] = array('<', strtotime('-3 month'));
            } else if ($lotime == 6) {

                $where['login_date'] = array('<', strtotime('-6 month'));
            } else if ($lotime == 7) {

                $where['login_date'] = array('<', $month['-1 year']);
            }

            $urlarr['lotime'] = $lotime;
        }

        if (isset($_GET['adtime']) && !empty($_GET['adtime'])) {

            $adtime	=	intval($_GET['adtime']);

            if ($adtime == 1) {    // 今天

                $mwhere['reg_date']	=	array('>', strtotime('today'));
            } else if ($adtime == 2) {    //	本周

                $mwhere['reg_date']	= 	array('>', strtotime('this week 00:00:00'));
            } else if ($adtime == 3) {    // 	本月

                $mwhere['PHPYUNBTWSTART_C']	= 	'';
                $mwhere['reg_date'][] 		= 	array('>=', $month['start_month']);
                $mwhere['reg_date'][] 		= 	array('<=', $month['end_month']);
                $mwhere['PHPYUNBTWEND_C'] 	= 	'';
            } else if ($adtime == 4) {    //	1个月内

                $mwhere['reg_date'] 		= 	array('>=', strtotime('-1 month'));
            } else if ($adtime == 5) {    // 	3个月内

                $mwhere['reg_date'][] 		= 	array('>=', strtotime('-3 month'));
            } else if ($adtime == 6) {    // 	半年内

                $mwhere['reg_date'] 		= 	array('>=', strtotime('-6 month'));
            } else if ($adtime == 7) {    //	1年内

                $mwhere['reg_date'] 		= 	array('>=', strtotime('-1 year'));
            }

            $urlarr['adtime']	=	$adtime;
        }

        if($_GET['city_class']){

            $where['PHPYUNBTWSTARTB'] = '';
            $where['provinceid']	=	array('findin', $_GET['city_class']);
            $where['cityid']	    =	array('findin', $_GET['city_class'], 'OR');
            $where['three_cityid']	=	array('findin', $_GET['city_class'], 'OR');
            $where['PHPYUNBTWENDB']  = '';

            $urlarr['city_class']	=	$_GET['city_class'];
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

	        }else if ($time == 6){
                $where['PHPYUNBTWSTART_A']    =   '';

                $where['vipetime'][]         =   array('>=', $month['start_month'],'AND');
                $where['vipetime'][]         =   array('<=',  $month['end_month'],'AND');

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

            foreach ($ListNew['list']  as $key => $val){
                $ListNew['list'][$key]['wxBindmsg'] = $this->wxBindState($val);
            }

	        unset($where['limit']);

	        $this -> yunset(array('rows'=>$ListNew['list']));

	    }
        session_start();
        $_SESSION['comXls'] = $where;
	    //提取分站内容
	    $cacheM					         =	$this -> MODEL('cache');
	    $domain					         =	$cacheM	-> GetCache('domain');

	    $this -> yunset('Dname', $domain['Dname']);

	    //提取顾问信息
	    $adminM                        =   $this->MODEL('admin');

		if($this->config['did']>0){

			$gwhere['did']	=	$this->config['did'];
		}

		$gwhere['is_crm']	=	'1';
 	    $gwInfo             =   $adminM -> getList($gwhere);
	    $this -> yunset('gwInfo',$gwInfo);

	    $this -> yunset('today', $toDay);

	    $hbArr  =   [1,2,3,4,5,6,7,8,9,10];
	    $hbBgA  =   array();
        foreach ($hbArr as $v) {
            $hbBgA[]   =   $this->config['sy_weburl'].'/data/upload/whb/logo/'.$v.'.png';
	    }
        $this->yunset('hbBgA', $hbBgA);
	    $this -> set_search();

        $this -> yuntpl(array('admin/admin_company'));
	}

	/**
	 * @desc   后台企业列表 --  添加企业  -- 提交表单
	 */
	function add_action(){

	    $cacheM     =   $this->MODEL('cache');

	    $options    =   array('com','city','hy');

	    $cache      =   $cacheM -> GetCache($options);

	    $this       ->  yunset('cache',  $cache);

	    $ratingM    =   $this -> MODEL('rating');

	    $noticeM 	= 	$this->MODEL('notice');

	    $rating_list   =   $ratingM -> getList(array( 'category' => '1'), array('field'=>'`id`,`name`'));


	    if ($_POST['submit']) {

            if ($_POST['username'] == '' || mb_strlen($_POST['username']) < 2 || mb_strlen($_POST['username']) > 16) {

                $this->ACT_layer_msg('用户名格式错误', 8);
            } elseif ($_POST['password'] == '' || mb_strlen($_POST['password']) < 6 || mb_strlen($_POST['password']) > 20) {

                $this->ACT_layer_msg('密码格式错误', 8);
            }

            $mPost  =   array(
                'username'  =>  trim($_POST['username']),
                'companyName'  =>  trim($_POST['name']),
                'moblie'    =>  trim($_POST['moblie']),
                'email'     =>  trim($_POST['email'])
            );

	        $userinfoM  =  $this -> MODEL('userinfo');

	        $result     =  $userinfoM -> addMemberCheck($mPost);

	        if ($result['msg']){

	            $this -> ACT_layer_msg($result['msg'],8);
	        }

	        if($this->config['sy_uc_type']=='uc_center'){

	            $this -> obj-> uc_open();
	            $user  =  uc_get_user($_POST['username']);
	            if(is_array($user)){

	                $this -> ACT_layer_msg('该会员已经存在！',8);
	            }
	        }
	        $time  =  time();
	        $ip    =  fun_ip_get();
	        $pass  =  $_POST['password'];

	        if($this->config['sy_uc_type'] == 'uc_center'){

	            $uid  =  uc_user_register($_POST['username'],$pass,$_POST['email']);

	            if($uid < 0){

	                switch($uid){

	                    case '-1' : $data['msg']='用户名不合法!';
	                    break;
	                    case '-2' : $data['msg']='包含不允许注册的词语!';
	                    break;
	                    case '-3' : $data['msg']='用户名已经存在!';
	                    break;
	                    case '-4' : $data['msg']='Email 格式有误!';
	                    break;
	                    case '-5' : $data['msg']='Email 不允许注册!';
	                    break;
	                    case '-6' : $data['msg']='该 Email 已经被注册!';
	                    break;
	                }

	                $this -> ACT_layer_msg($data['msg'],8);

	            }else{

	                list($uid, $username, $email, $password, $salt)    =   uc_get_user($_POST['username'], $pass);

				}

	        }else{
	            $pwdRes    =   $userinfoM -> generatePwd(array('password' => $pass));

	            $salt      =  $pwdRes['salt'];
	            $password  =  $pwdRes['pwd'];

	        }

	        $mdata = array(

	            'username'  =>  trim($_POST['username']),
	            'password'  =>  $password,
	            'usertype'  =>  2,
	            'salt'      =>  $salt,
	            'address'   =>  $_POST['address'],
	            'moblie'    =>  $_POST['moblie'],
	            'email'     =>  $_POST['email'],
	            'reg_date'  =>  $time,
	            'reg_ip'    =>  $ip,
	            'status'    =>  1

	        );

            if($_POST['areacode'] && $_POST['telphone']){

                $_POST['phone'] =   $_POST['areacode'].'-'.$_POST['telphone'];

                if($_POST['exten']){

                    $_POST['phone'] .= '-'.$_POST['exten'];
                }
            }

 	        $udata = array(
	            'name'         =>  $_POST['name'],
	            'shortname'    =>  $_POST['shortname'],
	            'hy'           =>  $_POST['hy'],
	            'pr'           =>  $_POST['pr'],
	            'mun'          =>  $_POST['mun'],
	            'provinceid'   =>  $_POST['provinceid'],
	            'cityid'       =>  $_POST['cityid'],
	            'three_cityid' =>  $_POST['three_cityid'],
	            'address'      =>  $_POST['address'],
	            'linkman'      =>  $_POST['linkman'],
	            'linktel'      =>  $_POST['moblie'],
	            'linkphone'    =>  $_POST['phone'],
	            'linkmail'     =>  $_POST['email'],
	            'content'      =>  $_POST['content'],
				'lastupdate'   =>  time(),
				'r_status'     =>  1
	        );

	        $sdata 				= 	array(

	            'integral'     =>  intval($_POST['integral']),
	            'rating'       =>  intval($_POST['rating_name'])
	        );

	        $nid  =  $userinfoM -> addInfo(array('mdata' => $mdata,'udata' => $udata, 'sdata' => $sdata));

	        if($nid > 0){

	            if($_POST['sendemail'] && $_POST['email']){

					$noticeM->sendEmailType(array('name'=>$mdata['username'],'username'=>$mdata['username'],'password'=>$_POST['password'],'email'=>$_POST['email'],'type'=>'reg','uid'=>$nid));
				}

	            if($_POST['sendmsg'] && $_POST['moblie']){
	            	if(checkMsgOpen($this->config)){

						$noticeM->sendSMSType(array('name'=>$mdata['username'],'username'=>$mdata['username'],'password'=>$_POST['password'],'moblie'=>$_POST['moblie'],'type'=>'reg','uid'=>$nid,'port' => 1));
					}
	            }

	            $this->ACT_layer_msg('企业会员(ID:'.$nid.')添加成功！',9,'index.php?m=admin_company');

	        }else{

	            $this->ACT_layer_msg('企业会员添加失败，请重试！',8);
	        }
	    }

	    $this->yunset("rating_list",$rating_list);

	    $this->yuntpl(array('admin/admin_member_comadd'));

	}

	/**
	 * @desc 企业列表 修改操作
	 */
    function edit_action(){

        $adminM	= 	$this->MODEL('admin');
        $user 	= 	$adminM->getAdminUser(array('uid' => $_SESSION['auid']));
        $group 	= 	$adminM->getAdminGroup(array('id' => $user['m_id'], 'did' => $this->config['did']));
        $power 	=	unserialize($group['group_power']);
        $navigationM	= 	$this->MODEL('navigation');
        $navigation	 	= 	$navigationM->getAdminNav(array('name' => '修改套餐'));
        $navigationid 	=	$navigation['id'];
        if (in_array($navigationid, $power)) {
            $baseshow = 1;
        }

        if($_GET['id']){

            $uid 		=	intval($_GET['id']);

            $ComM 		= 	$this->MODEL('company');
            $row 		= 	$ComM->getInfo($uid, array('edit' => 1));
            $baseshow 	= 	$row['r_status'] == '4' ? 2 : $baseshow;

            $UserinfoM 	= 	$this->MODEL('userinfo');
            $com_info 	= 	$UserinfoM->getInfo(array('uid' => $uid));

            $StatisM 	= 	$this->MODEL('statis');
            $statis 	= 	$StatisM->getInfo($uid, array('usertype' => 2));

            $ratingM 	= 	$this->MODEL('rating');
            $rating_list= 	$ratingM->getList(array('category' => '1'), array('field' => '`id`,`name`'));

            $cacheM 	= 	$this->MODEL('cache');
            $options 	= 	array('com', 'city', 'hy');
            $cache 		= 	$cacheM->GetCache($options);

            $this->yunset(array('cache' => $cache, 'statis' => $statis, 'com_info' => $com_info, 'rating_list' => $rating_list, 'row' => $row));
            $this->yunset('city_name', $cache['city_name']);
            $this->yunset("rating", $_GET['rating']);
            $this->yunset("lasturl", $_SERVER['HTTP_REFERER']);
            $this->yunset("baseshow", $baseshow);
        }
        $this->yuntpl(array('admin/admin_member_comedit'));
    }


    /**
	 * @desc   后台企业列表 --  修改 --点击修改会员级别
	 */
	function rating_action(){

	    $ratingid       =   intval($_POST['ratingid']);
	    $uid            =   intval($_POST['uid']);

	    $statisM        =   $this -> MODEL('statis');

	    $statis         =   $statisM -> getInfo($uid, array('usertype'=>2));

	    $ratingM        =   $this -> MODEL('rating');

	    $rating_info    =   $ratingM -> getInfo(array( 'id' => $ratingid));

	    if($ratingid    !=  $statis['rating']){

	        if($statis && is_array($statis)){

	            $value  =   $ratingM -> ratingInfo($ratingid, $uid);

	            $statisM -> upInfo($value , array('uid' => $uid, 'usertype' => 2, 'adminedit' => '1', 'info' => $rating_info));

	            $this -> MODEL('log') -> addAdminLog("企业会员(ID".$_POST['uid'].")更新为【".$rating_info['name']."】");

	        }

	        echo 1;die;

	    }else{

	        echo 0;die;

		}

	}

	/**
	 * @desc 后台企业列表 -- 修改 -- 修改用户名
	 */
	function saveusername_action(){

	    $M	=	$this -> MODEL("userinfo");

	    $postData  =   $_POST;

	    $postData['admin']	=	'1';

	    $result    =   $M -> saveUserName($postData);

	    if($result['errcode'] != '1'){

	        echo $result['error']=='101' ? 2 : 1; die;

	    } else{
	        $sysmsgM    =   $this -> MODEL('sysmsg');

	        $sysmsgM    ->  addInfo(array('uid' => @explode(',',$_POST['uid']),'usertype'=>2, 'content' => '管理员为您修改用户名：<a href="comtpl,'.$_POST['uid'].'">'.trim($_POST['username'].'</a>')));

	        echo 0;die;
	    }

	}

	/**
	 * @desc   后台企业列表 --  修改 -- 基本资料 -- 提交表单
	 */
	function comeditsave_action(){

		$_POST = $this->post_trim($_POST);

		$isAjax  = isset($_POST['is_ajax']) ? true :  false;
        $picture = '';

		$companyM	=	$this->MODEL('company');

		if($_FILES['file']['tmp_name']){
			$upArr    =  array(
				'file'  =>  $_FILES['file'],
				'dir'   =>  'company'
			);

			$uploadM	=	$this -> MODEL('upload');

			if(!empty($upArr)){
				$pic	=	$uploadM->newUpload($upArr);
			}

			if(!empty($pic['picurl'])){
				$picture	=	$pic['picurl'];
			}
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
	        'moneytype' 	=> 	$_POST['moneytype'],
	        'money' 		=>	$_POST['money'],
	        'linkqq' 		=> 	$_POST['linkqq'],
	        'website' 		=> 	$_POST['website'],
	        'busstops' 		=> 	$_POST['busstops'],
	        'welfare' 		=> 	$_POST['welfare'],
	        'lastupdate'	=>  time(),
	        'content'		=> 	str_replace(array('&amp;','background-color:#ffffff','background-color:#fff','white-space:nowrap;'),array('&','background-color:','background-color:','white-space:'),$_POST['content']),
	        'admin_remark'  =>  $_POST['admin_remark'],
			'comqcode'		=>	$picture,
			'x' => $_POST['x'],
			'y' => $_POST['y']
	    );
		if (isset($_POST['r_status'])) {
		    $comData['r_status'] = $_POST['r_status'];
        }
        if (isset($_POST['infostatus'])){
            $comData['infostatus'] = $_POST['infostatus'];
        }
        if (isset($_POST['sdate'])){
            $comData['sdate'] = $_POST['sdate'];
        }
        if (isset($_POST['linkjob'])){
            $comData['linkjob'] = $_POST['linkjob'];
        }
	    $row	=	$companyM -> getInfo($_POST['uid'], array('edit'=>1));

		if($row['comqcode'] && $picture==""){
			$comData['comqcode']	=	$row['comqcode'];
		}
		//	处理账号信息
		$mData      =   array(

	        //'username'      =>  trim($_POST['username']),
	        //'password'      =>  $_POST['password'],
	        'email'         =>  $_POST['email'],
	        'moblie'        =>  $_POST['moblie'],
	        //'status'        =>  $_POST['status'],
	        'address'       =>  trim($_POST['address']),
	        //'lock_info'     =>  $_POST['lock_info']
	    );

		// 处理company_statis中的字段
	    /**$sData		=	array(
	        'rating'            =>  $_POST['ratingid'],
	        'vip_etime'         =>  $_POST['vip_etime']?strtotime($_POST['vip_etime']):0,
	        'job_num'           =>  intval($_POST['job_num']),
	        'down_resume'       =>  intval($_POST['down_resume']),
	        'invite_resume'     =>  intval($_POST['invite_resume']),
	        'breakjob_num'      =>  intval($_POST['breakjob_num']),
	        'part_num'          =>  intval($_POST['part_num']),
	        'breakpart_num'     =>  intval($_POST['breakpart_num']),
	        'zph_num'           =>  intval($_POST['zph_num']),
			'top_num'			=>	intval($_POST['top_num']),
			'urgent_num'		=>	intval($_POST['urgent_num']),
			'rec_num'			=>	intval($_POST['rec_num']),
	        'lt_job_num'        =>  intval($_POST['lt_job_num']),
	        'lt_down_resume'    =>  intval($_POST['lt_down_resume']),
	        'lt_breakjob_num'   =>  intval($_POST['zph_num'])
	    );*/


	    $return    =  $companyM->setCompany(array('uid'=>intval($_POST['uid'])),array('mData'=>$mData,'comData'=>$comData,/*'sData'=>$sData,*/'utype'=>'admin'));

        delfiledir('../data/upload/tel/'.$_POST['uid']);

        $lasturl  =  str_replace('&amp;','&',$_POST['lasturl']);

        if ($isAjax) {
            echo json_encode(array('error'=>$return['errcode'],'msg'=>$return['msg']));die;
        } else {
            if ($return['errcode'] == 8){
                $this->ACT_layer_msg($return['msg'], 8);
            }else{
                $this->ACT_layer_msg($return['msg'], $return['errcode'], $lasturl, 2, 1);
            }
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
				'spview_num'		=>	intval($_POST['spview_num'])
			);

			$return		=	$comM -> setStatisInfo($cuid, $sData);

			if ($return['errcode'] == 8){

				$this->ACT_layer_msg($return['msg'], 8);
			}else{

			    $this->ACT_layer_msg($return['msg'].'(企业列表-修改信息-修改会员信息，ID：'.$cuid.')', $return['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
			}
		}

	}

	/**
	 * @desc 后台企业列表 --  修改 -- 账户信息 -- 提交表单
	 */
	 function saveUser_action(){

	 	if($_POST){

			$cuid		=	intval($_POST['uid']);

			$userInfoM	=	$this->MODEL('userinfo');

			$data		=	array(

				'username'	=>	$_POST['username'],
				'password'	=>	$_POST['password'],
				'status'	=>	$_POST['status'],
				'lock_info'	=>	$_POST['lock_info']
			);

			$result		=	$userInfoM -> addMemberCheck($data, $cuid, 'admin');

			if(!empty($result['msg'])){

				$this->ACT_layer_msg($result['msg'], 8);
			}else{
				$this->ACT_layer_msg('更新成功(企业列表-修改信息-修改账户信息，ID：'.$cuid.')', 9, $_SERVER['HTTP_REFERER'], 2, 1);
			}

		}

	 }

	/**
	 * @desc   后台企业列表 -- 会员审核
	 */
	function status_action(){

	    $userinfoM  =  $this -> MODEL('userinfo');

	    $post       =  array(
			'status'     =>  intval($_POST['status']),
	        'lock_info'  =>  trim($_POST['statusbody']),
            'lock_status' => isset($_POST['lock_status']) ? intval($_POST['lock_status']) : '',
            'single'    => isset($_POST['single']) ? $_POST['single'] : '',
	    );

        $uids       =   @explode(',', $_POST['uid']);

        $return     =   $userinfoM -> status(array('uid' => array('in', pylode(',', $uids)),'usertype'=>2), array('post' => $post));

        if (isset($_POST['single'])){
            if ($return['errcode'] == 9){
                if($_POST['atype'] == 1){
                    // 仅保存
                    $this->layer_msg($return['msg'],9);
                }else{
                    // 下一个待审核企业
                    $ComM  =  $this -> MODEL('company');
                    $row   =  $ComM->getCompanyInfo(array('r_status'=>0,'orderby'=>array('uid,desc')), array('field'=>'uid'));
                    if (!empty($row)){
                        $this->layer_msg($return['msg'],9,0,'index.php?m=admin_company&c=companyAudit&uid='.$row['uid']);
                    }else{
                        $this->layer_msg($return['msg'],9);
                    }
                }
            }else{
                $this->layer_msg($return['msg'],8);
            }
        }else{
            if($return['errcode']==9){

                $this  ->  ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=admin_company",2,1);

            }else{

                $this  ->  ACT_layer_msg($return['msg'],$return['errcode']);

            }
        }

	}

    // 新审核，增加企业详情
    function companyAudit_action(){

        $uid =  intval($_GET['uid']);
        $ComM		=   $this -> MODEL('company');
        $userinfoM  =  $this -> MODEL('userinfo');

        $Info  =  $ComM->getInfo($uid, array('edit'=>1));

        $member     =  $userinfoM -> getInfo(array('uid'=>$uid),array('field'=>'lock_info,status,login_address,login_ip,moblie_address'));
        $Info['statusbody'] = trim($member['lock_info']);
		$Info['member_status'] = $member['status'];
		$Info['login_ip'] = $member['login_ip'];
        $Info['login_address'] = $member['login_address'];
        $Info['moblie_address'] = $member['moblie_address'];

        $this->yunset('Info', $Info);

        // 待审核数量
        $snum = $ComM->getCompanyNum(array('r_status'=>0, 'uid'=>array('<>', $uid)));
        $this->yunset('snum', $snum);

        $StatisM        =   $this -> MODEL('statis');
        $statis         =   $StatisM -> getInfo($uid, array('usertype'=>2));

        $ratingM        =   $this -> MODEL('rating');
        $rating_list    =   $ratingM -> getList(array( 'category' => '1'), array('field'=>'`id`,`name`'));

        $cacheM         =   $this->MODEL('cache');
        $options        =   array('com','city','hy');
        $cache          =   $cacheM -> GetCache($options);

        $this -> yunset(array('cache' => $cache, 'statis' => $statis, 'rating_list' => $rating_list));

        $this->yuntpl(array('admin/admin_company_audit'));
    }

	// 企业暂停
	function suspend_action(){

		$userinfoM  =  $this -> MODEL('userinfo');

        $post       =   array('status' => 4);
        $uid        =   intval($_POST['uid']);
        $return     =   $userinfoM->status(array('uid' => $uid,'usertype'=>2), array('post' => $post));

        if ($return['errcode'] == '9'){
            $this->MODEL('company')->setComStaticSub($uid);
        }
        $this->layer_msg($return['msg'], $return['errcode'],0, $_SERVER['HTTP_REFERER']);
	}
	/**
	 * @desc   企业列表 删除
	 */
	function del_action(){

		if($_GET['del']){

			$this -> check_token();

			if (is_array($_GET['del'])){

				$uid	=	$_GET['del'];
			}else{

				$uid	=	intval($_GET['del']);
			}

	    }else if($_POST['del']){

			$uid	=	$_POST['del'];
		}

        $userinfoM  =   $this -> MODEL('userinfo');

        $return     =   $userinfoM -> delInfo($uid, 2, $_POST['delAccount']);

	    $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
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
			echo json_encode($rating);
		}
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
				$rating['vipetime'] =   date('Y-m-d',$rating['vip_etime']);

			}else{

				$rating['oldetime'] =   0;
				$rating['vipetime'] =   '不限';
			}
			echo json_encode($rating);
		}
	}

	/**
	 * @desc 后台企业列表  --  修改会员等级  -- 弹出框  --确认修改会员等级
	 */

	function uprating_action()
    {


        $rid = intval($_POST['rating']);
        $uid = intval($_POST['ratuid']);

        $ratingM        =   $this->MODEL('rating');
        $rating_info    =   $ratingM->getInfo(array('id' => $rid));

        if (empty($rid)) {

            $this->ACT_layer_msg('请选择会员等级！', 8, $_SERVER['HTTP_REFERER']);
        } else if ($uid) {

            $statisM    =   $this->MODEL('statis');
			$statis     =   $statisM->getInfo($uid, array('usertype' => 2));

            $sonM		=	$this->MODEL('companyaccount');
            $sonList    =   $sonM -> getList(array('comid' => $uid, 'status' => 1), array('field' => '`uid`'));

			$companyM   =   $this->MODEL('company');
            $company	=	$companyM->getInfo($uid);

            if ($company['r_status'] == '4'){
                $this->ACT_layer_msg('请先解除暂停，再进行套餐调整！', 8, $_SERVER['HTTP_REFERER']);
            }

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

            if ($_POST['hotjob']) {
                if (empty($company['name'])) {

                    $this->ACT_layer_msg('请先完善企业资料！', 8, $_SERVER['HTTP_REFERER']);
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
            //后台修改套餐数量清空子账号套餐量调整,修改单个套餐不清空,只有修改套餐类型才会清空子账号套餐量
            if (!empty($spids) && !empty($sonData) && $statis['rating'] != $sonData['rating']) {

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

			$content = '企业列表修改会员等级(ID:' . $uid . ')；';
			$logContent =  isset($msgContent) ? $content.$msgContent : $content.'未修改会员等级、套餐';
			$logM	=	$this->MODEL('log');
			$logM -> addAdminLog($logContent);

            $this->ACT_layer_msg('企业(ID:' . $uid . ')修改成功！', 9, $_SERVER['HTTP_REFERER']);
        } else {

            $this->ACT_layer_msg('非法操作！', 8, $_SERVER['HTTP_REFERER']);
        }
    }
	function setupcom_action(){

		$companyM  =  $this -> MODEL('company');

		$data = array(
			'uid'		=>	intval($_POST['uid']),
			'addzttime'	=>	$_POST['addzttime'],
			'from'		=>	'admin'
		);

		$res = $companyM->setupcom($data);

		echo json_encode($res);die;
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
	 * @desc 分配顾问
	 */
	function checkguwen_action(){

        $ComM       =   $this->MODEL('company');

        $adminM     =   $this->MODEL('admin');

        $gid        =   intval($_POST['gid']);

        $comid      =   trim($_POST['comid']);

        $uid        =   @explode(',', $comid);

        $uids       =   pylode(',', $uid);

        $crmUser    =   $adminM->getAdminUser(array( 'uid' => $gid ));

        if (! is_array($crmUser)) {
            $this -> ACT_layer_msg('请选择分配顾问！', 8, $_SERVER['HTTP_REFERER']);
        }

        $gData = array('crm_uid' => $gid, 'crm_time' => time(), 'crm_source' => 5);
        $whereData = array('crm_uid' => $gid, 'uid' => $uids);
        $arr = $ComM->setComGw($gData, $whereData);

        $this->ACT_layer_msg($arr['msg'], $arr['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
    }

	/**
	 * @desc 会员企业列表，点击企业用户名成，跳转企业会员中心
	 *
	 * @param  $_GET['type']，跳转招聘统计分析页面
	 */
	function Imitate_action(){

	    $userinfoM  =  $this->MODEL('userinfo');

	    $member     =  $userinfoM -> getInfo(array('uid'=> intval($_GET['uid'])),array('field'=>'`uid`,`username`,`salt`,`email`,`password`,`usertype`,`did`'));

	    $this -> cookie->unset_cookie($_SESSION['auid']);

	    $this -> cookie->add_cookie($member['uid'],$member['username'],$member['salt'],$member['email'],$member['password'],2,$this->config['sy_logintime'],$member['did'],'1');

		$typeStr 	=	trim($_GET['type']);

		$url		=	'';

	    if(!empty($typeStr)){

	        if ($typeStr == 'job') {

	            $url = 'index.php?c='.$typeStr;
	        }else{

	            $url = 'index.php?c='.$typeStr;
	        }

		}
		$logM  		=  $this->MODEL('log');

		$content	=	'管理员'.$_SESSION['ausername'].'登录企业账户(ID:'.$member['uid'].')';

		$adminLo	=	$logM -> addAdminLog($content);

		header('Location: '.$this->config['sy_weburl'].'/member/'.$url);
	}

    /**
     * @desc  导出数据检测
     */
    function export_check_action(){
        $where = $_SESSION['comXls'] ? $_SESSION['comXls'] : array('orderby'=>'uid');
        if(!empty($_POST['type'])){
            foreach($_POST['type'] as $v){
                if($v == 'lastdate'){
                    $type[]  =  'lastupdate';
                }else if ($v == 'money'){
                    $type[]  = 'money';
                    $type[]  = 'moneytype';
                }else if ($v == 'crm_salesman'){
                    $type[]  = 'crm_uid';
                }else if ($v != 'rating' && $v != 'vip_stime' && $v != 'vip_etime'){
                    $type[]  =  $v;
                }
            }
            !in_array('uid', $type) && array_push($type, 'uid');
            $field  =  @implode(',', $type);
        }else{
            $field  =  'uid';
        }
        if($_POST['uid']){
            $uids          =  @explode(',',$_POST['uid']);
            $where['uid']  =  array('in',pylode(',',$uids));
            $_SESSION['comXlsIds'] = $where['uid'];// 记录勾选的id条件
        } else {
        	unset($_SESSION['comXlsIds']);
		}
        if($_POST['limit']){
            $where['limit']  =  intval($_POST['limit']);
            $_SESSION['comXlsLimit'] = intval($_POST['limit']);// 记录输入的条数限制
        } else {
        	unset($_SESSION['comXlsLimit']);
		}
        $_SESSION['xlsFields'] = $field;
        $_SESSION['postTypes'] = $_POST['type'];
        $ComM = $this->MODEL('company');
        $companyNum = $ComM->getCompanyNum($where);
        if (intval($companyNum) > 0){
            !is_null($_COOKIE['companyXls']) && $this->cookie->SetCookie("companyXls",true,time() - 86400);// 手动控制上一次下载成功的cookie过期
            echo json_encode(['code'=> 200,'field'=>$_SESSION['xlsFields']]);
        } else {
            echo json_encode(['code'=> 400, 'msg' => '暂无符合条件的企业数据！']);
        }
    }

    /**
     * @desc  导出企业数据
     */
    function xls_action(){
        $where = $_SESSION['comXls'] ? $_SESSION['comXls'] : array('orderby'=>'uid');
        if (isset($_SESSION['comXlsIds']) && $_SESSION['comXlsIds']) {
            $where['uid']  =  $_SESSION['comXlsIds'];// 勾选的id条件
        }
        if (isset($_SESSION['comXlsLimit']) && $_SESSION['comXlsLimit']) {
            $where['limit']  =  $_SESSION['comXlsLimit'];// 导出条数限制
        }
        $this->cookie->SetCookie("companyXls",true,time() + 60);// 前台获取到该cookie值后关闭页面加载动画
        $field = $_SESSION['xlsFields'] ? $_SESSION['xlsFields'] : 'uid';
        $ComM      =   $this -> MODEL('company');
        $listNew   =   $ComM -> getList($where,array('statis'=>1,'cache'=>1,'field'=>$field));
        $list      =   $listNew['list'];
        $cache     =   $listNew['cache'];
        if (!empty($list)){
            $this->yunset('list',$list);
            $this->yunset('cache',$cache);
            $this->yunset('type',$_SESSION['postTypes']);
            $this->MODEL('log')->addAdminLog('导出公司信息');
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=company.xls");
            $this->yuntpl(array('admin/admin_company_xls'));
        }
    }

	/**
	 * @desc  企业列表  分站设置
	 */
	function checksitedid_action(){

	    $uid		 =	trim($_POST['uid']);
	    $did		 =	intval($_POST['did']);

	    if(empty($uid)){

	        $this -> ACT_layer_msg('参数不全请重试！', 8);
	    }

	    $uids		 =	@explode(',',$_POST['uid']);
	    $uid 		 =	pylode(',',$uids);

	    if(empty($uid)){

	        $this -> ACT_layer_msg('请正确选择需分配用户！', 8);
	    }

	    $siteM       =  $this->MODEL('site');

	    $didData	 =    array('did' => $did);

	    $Table = array(
            'member',
            'company',
            'company_statis',
            'company_job',
            'company_cert',
            'company_news',
            'company_order',
            'company_product',
            'invoice_record',
            'partjob',
            'hotjob'
        );

	    $siteM -> updDid(array('report'), array('p_uid' => array('in', $uid),'usertype'=>2), $didData);

	    $siteM -> updDid(array('userid_msg'), array('fid' => array('in', $uid)),$didData);

	    $siteM -> updDid(array('company_pay','look_resume'),array('com_id'=>array('in', $uid),'usertype'=>2),$didData);

	    $siteM -> updDid(array('down_resume'),array('comid'=>array('in', $uid),'usertype'=>2),$didData);

	    $siteM -> updDid(array('ad_order'),array('comid'=>array('in', $uid)),$didData);

	    $siteM -> updDid($Table,array('uid'=>array('in', $uid)),$didData);

	    $this->ACT_layer_msg('会员(ID:'.$_POST['uid'].')分配站点成功！', 9, $_SERVER['HTTP_REFERER'],2,1);

	}

	/**
	 * @desc 后台    - 企业列表  - 更多操作
	 */
	function getinfo_action(){

	    if($_POST['comid']){

	        $comid =   intval($_POST['comid']);

	        $ComM  =   $this -> MODEL('company');

	        $info  =   $ComM -> getInfo($comid);

	        $uid   =   intval($info['uid']);

	        $UsernfoM  =   $this -> MODEL('userinfo');

	        $member    =   $UsernfoM -> getInfo(array('uid'=> $uid), array('field'=>'username, reg_ip, status'));

	        $statisM   =   $this -> MODEL('statis');

	        $statis    =   $statisM -> getInfo($uid, array('usertype'=>'2', 'field'=>'rating'));

	        $yyzz      =   $ComM -> getCertInfo(array('uid'=>$uid, 'type'=>'3'), array('field'=>'`check`'));

	        if ($info['crm_name']){

	            $info['adviser']    =   $info['crm_name'];
	        }else{
                $info['adviser']    =   null;
	        }
            $info['username']       =   $member['username'];
            $info['reg_ip']         =   $member['reg_ip'];
            $info['status']         =   $member['status'];
            $info['rating']         =   $statis['rating'];
            $info['yyzzurl']        =   $yyzz['old_check'];

            if ($info['linktel']){

                $info['phone']      =    $info['linktel'];

            }else{

                $info['phone']      =    $info['linkphone'];

            }
            if($info['zt_time']){
                $info['zt_days'] 	= 	intval((time()-$info['zt_time'])/86400);
            }
            $comOrderM              =   $this -> MODEL('companyorder');

            $integralNum            =   $comOrderM -> getCompanyPayNum(array('com_id'=>$uid, 'type'=>'1' ,'usertype'=>'2'));

            $info['integralNum']    =   $integralNum;

            $orderNum               =   $comOrderM -> getCompanyOrderNum(array('uid'=>$uid , 'usertype' => 2));

            $info['orderNum']       =   $orderNum;


            $downResumeM            =   $this -> MODEL('downresume');

            $downNum                =   $downResumeM -> getDownNum(array('comid'=>$uid,'usertype'=>2));

            $info['downNum']        =   $downNum;

            $jobM                   =   $this -> MODEL('job');

            $applyNum               =   $jobM -> getSqJobNum(array('com_id'=>$uid));

            $info['applyNum']       =   $applyNum;

            $inviteNum              =   $jobM -> getYqmsNum(array('fid'=>$uid));

            $info['inviteNum']      =   $inviteNum;

            $showNum                =   $ComM -> getComShowNum(array('uid'=>$uid));

            $info['showNum']        =   $showNum;

            $jobNum                 =   $jobM -> getJobNum(array('uid'=>$uid));

            $info['jobNum']         =   $jobNum;

			$comacM					=	$this -> MODEL('companyaccount');
			$info['sonsNum']        =   $comacM -> getNum(array('comid' => $uid));
			//提取分站内容
            $cacheM	= $this->MODEL('cache');
            $domain = $cacheM->GetCache('domain');
            if (!$info['did']) {
                $info['did'] = 0;
            }
            $info['did_name'] = $domain['Dname'][$info['did']];
            echo json_encode($info);

	    }
	}

	/**
	 * @desc 会员-企业-企业用户列表:（重置密码）
	 */
	function reset_companypassword_action(){

	    $this -> check_token();

	    $userinfoM  =  $this->MODEL('userinfo');

	    $userinfoM -> upInfo(array('uid'=>intval($_GET['uid'])),array('password'=>'123456'));

	    $this -> MODEL('log') -> addAdminLog('会员(ID:'.$_GET['uid'].')重置密码成功');

	    echo '1';

	}

	/**
	 * @desc 会员-企业-企业列表:（统计数量）
	 */
	function companyNum_action(){
		$MsgNum=$this->MODEL('msgNum');
		echo $MsgNum->companyNum(array('clue' => 1));
	}

	/**
	 * @desc 企业模板
	 */
	function mcomtpl_action(){

	    $tplM      =   $this -> MODEL('tpl');

	    $comid     =   intval($_GET['comid']);

	    $where     =   array();

	    $where['status']               =   '1';

	    $where['PHPYUNBTWSTART_A']     =    '';

	    $where[service_uid][]          =   array('=', '0','OR');

	    $where[service_uid][]          =   array('findin', $comid, 'OR');

	    $where['PHPYUNBTWEND_A']       =   '';

	    $where['orderby']              =   'id,desc';

	    $list                          =   $tplM -> getComtplList($where);

	    $this->yunset("list",$list);

	    $this->yunset("comid",$comid);

	    $statisM   =   $this -> MODEL('statis');

	    $statis    =   $statisM -> getInfo($comid, array('usertype'=>'2', 'field'=>'comtpl'));

	    $this->yunset('statis',$statis);

	    $this->yuntpl(array('admin/admin_company_mcomtpl'));
	}

	/**
	 * @desc 设置企业模板
	 */
	function msettpl_action(){

	    $this      ->  check_token();

	    $uid       =   intval($_GET['comid']);

	    $tplM      =   $this -> MODEL('tpl');

	    $id        =   intval($_GET['id']);

	    $tpl       =   $tplM -> getComtpl(array('id'=>$id), array('field'=>'url'));

	    $statisM   =   $this -> MODEL('statis');

	    $nid       =   $statisM -> upInfo(array('comtpl'=>$tpl['url']), array('uid'=>$uid, 'usertype'=>'2'));

	    if ($nid){


	        $sysmsgM    =   $this -> MODEL('sysmsg');

	        $sysmsgM    ->  addInfo(array('uid' => $uid, 'usertype'=>2, 'content' => '管理员为您设置企业模板：<a href="comtpl,'.$uid.'">'.trim($tpl['url'].'</a>')));

 	        $this->layer_msg('设置成功！',9);

	    }else{

	        $this->layer_msg('设置失败！',9);

	    }
	}

	/**
	 * @desc 职位匹配简历推送
	 */
	function directrecom_action(){

	    $where  =  array(
	        'eid'      =>  intval($_GET['eid']),
	        'jobid'    =>  intval($_GET['id']),
	        'comid'    =>  intval($_GET['comid'])
	    );

	    $userEntrustM  =  $this -> MODEL('userEntrust');

	    $return        =  $userEntrustM -> sendRecord($where);

	    $err           =   array(

	        'msg'      =>  $return['msg'],
	        'type'     =>  $return['errcode']

	    );

	    echo json_encode($err);die;

	 }

    /**
     * 企业认证总入口，入口整合，利于权限控制
     */
    function comcert_action()
    {

        if (isset($_GET['sbody'])) {

            $this->sbody();
        } elseif (isset($_POST['batchfirm'])) {

            $this->batchfirm();
        } elseif (isset($_POST['comemail'])) {

            $this->emaillock();
        } elseif (isset($_POST['comlinktel'])) {

            $this->phonelock();
        } elseif (isset($_GET['acwxbind'])) {

            $this->acwxbind();
        } else {

            $this->comStatus();
        }
    }

    /**
     * 列表-邮箱认证
     */
    function emaillock()
    {

        $CompanyM   =   $this->MODEL('company');
        $UserinfoM  =   $this->MODEL('userinfo');

        if ($_POST['comemail'] == "") {

            $this->ACT_layer_msg("请填写邮箱", 8);
        } elseif (CheckRegEmail($_POST['comemail']) == false) {

            $this->ACT_layer_msg("邮箱格式错误", 8);
        }

        $uid        =   $_POST['uid'];
        $status     =   $_POST['estatus'];
        $email      =   $_POST['comemail'];

        $comInfo    =   $CompanyM->getInfo($uid, array('field' => '`linkmail`, `email_status`'));

        if ($comInfo) {

            if ($comInfo['linkmail'] == $email && $comInfo['email_status'] == 1){

                $this->ACT_layer_msg("邮箱未变更，无需调整！", 9, $_SERVER['HTTP_REFERER'], 2, 1);
            }

            $data   =   array('email_status' => $status, 'linkmail' => $email);
            $nid    =   $CompanyM->upInfo($uid, '', $data);

            if ($nid) {

                $emaildata  =   array('email' => $email, 'email_status' => $status);
                $UserinfoM->upInfo(array('uid' => $uid), $emaildata);

                $this->obj->update_once('member', array('email' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'email' => $email));
                $this->obj->update_once('company', array('linkmail' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'linkemail' => $email));
                $this->obj->update_once('resume', array('email' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'email' => $email));
                $this->obj->update_once('lt_info', array('email' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'email' => $email));
                $this->obj->update_once('px_train', array('linkmail' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'linkmail' => $email));

                $msg        =   '新邮箱：' . $email;
                if (!empty($comInfo['linkmail']) && $comInfo['linkmail'] != $email) {

                    $msg    .=  '，原邮箱：' . $comInfo['linkmail'];
                }

                $this -> MODEL('log') -> addAdminLog("企业会员(ID".$_POST['uid'].")认证邮箱【".$email."】");

                $this->ACT_layer_msg("邮箱认证成功(用户ID：" . $uid . "，" . $msg . ")", 9, $_SERVER['HTTP_REFERER'], 2, 1);
            } else {

                $this->ACT_layer_msg("邮箱认证失败", 8, $_SERVER['HTTP_REFERER']);
            }
        } else {

            $this->ACT_layer_msg("当前数据错误", 8, $_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * 列表-手机认证
     */
    function phonelock()
    {

        $_POST      =   $this->post_trim($_POST);

        $CompanyM   =   $this->MODEL('company');
        $UserinfoM  =   $this->MODEL('userinfo');

        if ($_POST['comlinktel'] == "") {

            $this->ACT_layer_msg("请填写手机号码", 8);
        } elseif (CheckMobile($_POST['comlinktel']) == false) {

            $this->ACT_layer_msg("手机号码格式错误", 8);
        }

        $uid        =   $_POST['uid'];
        $status     =   $_POST['mstatus'];

        $moblie     =   $_POST['comlinktel'];

        $comInfo    =   $CompanyM->getInfo($uid, array('field' => '`linktel`,`moblie_status`'));

        if (!empty($comInfo)) {

            if ($comInfo['linktel'] == $moblie && $comInfo['moblie_status'] == 1){

                $this->ACT_layer_msg("手机号未变更，无需调整！", 9, $_SERVER['HTTP_REFERER'], 2, 1);
            }

            $data   =   array('moblie_status' => $status, 'linktel' => $moblie);
            $nid    =   $CompanyM->upInfo($uid, '', $data);

            if ($nid) {

                $mobliedata =   array('moblie' => $moblie, 'moblie_status' => $status);
                
                $msg  =  '新手机号：' . $moblie;
                if (!empty($comInfo['linktel']) && $comInfo['linktel'] != $moblie) {
                    
                    $msg    .=  '，原手机号：' . $comInfo['linktel'];
                }
                // 获取用户信息，用来判断旧手机号和用户名是否一致
                $member =   $UserinfoM->getInfo(array('uid' => $uid), array('field'=>'username,moblie'));
                if ($member['username'] == $member['moblie']) {
                    
                    // 有账号的用户名与新手机号一致的，将用户名改成新手机号
                    $omb  =  $UserinfoM->getInfo(array('username' => $moblie), array('field'=>'uid'));
                    if (!empty($omb)) {
                        
                        // 如果现有数据中，存在用户名是这个手机号的，要修改
                        $UserinfoM->upInfo(array('uid' => $omb['uid']), array('username' => $moblie . '_s'));
                        
                        $logDetail  =   '账号修改：账号（UID：'.$uid.'）认证手机号，因本账号用户名与该手机号相同，调整本账号（ID：'.$omb['uid'].'）用户名（'.$moblie.' → '.$moblie.'_s）';
                        
                        $logM = $this->MODEL('log');
                        $logM -> addAdminLog($logDetail);
                    }
                    $mobliedata['username'] = $moblie;
                    
                    $msg .= '，同步调整用户名为'.$moblie;
                }
                
                $UserinfoM->upInfo(array('uid' => $uid), $mobliedata);

                $this->obj->update_once('member', array('moblie' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'moblie' => $moblie));
                $this->obj->update_once('resume', array('telphone' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'telphone' => $moblie));
                $this->obj->update_once('company', array('linktel' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'linktel' => $moblie));
                $this->obj->update_once('lt_info', array('moblie' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'moblie' => $moblie));
                $this->obj->update_once('pr_train', array('linktel' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'linktel' => $moblie));

                $this->ACT_layer_msg("手机认证成功(用户ID：" . $uid . "，" . $msg . ")", 9, $_SERVER['HTTP_REFERER'], 2, 1);
            } else {

                $this->ACT_layer_msg("手机认证失败", 8, $_SERVER['HTTP_REFERER']);
            }
        } else {

            $this->ACT_layer_msg("当前数据错误", 8, $_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * 列表底部-批量认证
     */
    function batchfirm()
    {

        $CompanyM   =   $this->MODEL('company');
        $UserinfoM  =   $this->MODEL('userinfo');
        $status     =   $_POST['plstatus'];

        $msg        =   array();

        if ($_POST['comname_email'] == "" && $_POST['comname_moblie'] == "" && $_POST['comname_yyzz'] == "") {
            $this->ACT_layer_msg("请选择认证类型", 8);
        }
        if ($_POST['uid'] == "") {
            $this->ACT_layer_msg("非法操作", 8);
        }
        if ($status == "") {

            $this->ACT_layer_msg("请选择认证状态", 8);
        }

        if ($_POST['comname_email'] || $_POST['comname_moblie']) {

            $where['uid']   =   array('in', pylode(',', $_POST['uid']));

            $rows           =   $CompanyM->getChCompanyList($where, array('field' => '`uid`,`linktel`,`linkmail`,`moblie_status`,`email_status`'));

            if (is_array($rows) && $rows) {
                if ($_POST['comname_email']) {

                    array_push($msg, '邮箱');

                    foreach ($rows as $val) {

                        if ($val['linkmail'] || $val['email_status'] == 1) {

                            $emailuid[] =   $val['uid'];
                        }
                    }

                    $emaildata          =   array('email_status' => $status);
                    $emailwhere['uid']  =   array('in', pylode(',', $emailuid));
                    $UserinfoM->upInfo($emailwhere, $emaildata);
                    $CompanyM->upInfo($emailuid, '', $emaildata);
                }

                if ($_POST['comname_moblie']) {

                    array_push($msg, '手机');

                    foreach ($rows as $val) {
                        if ($val['linktel'] || $val['moblie_status'] == 1) {

                            $moblieuid[]    =   $val['uid'];
                        }
                    }

                    $mobliedata         =   array('moblie_status' => $status);
                    $mobliewhere['uid'] =   array('in', pylode(',', $moblieuid));
                    $UserinfoM->upInfo($mobliewhere, $mobliedata);
                    $CompanyM->upInfo($moblieuid, '', $mobliedata);
                }
            }
        }

        if ($_POST['comname_yyzz']) {
            //企业资质
            array_push($msg, '企业资质');

            if ($status != 0) {

                //已认证
                $yyzzwhere['uid']   =   array('in', pylode(',', $_POST['uid']));
                $yyzzwhere['type']  =   3;

                $yyzz   =   $CompanyM->getCertList($yyzzwhere, array('field' => '`uid`,`check`,`owner_cert`,`wt_cert`,`other_cert`'));

                if (is_array($yyzz) && $yyzz) {
                    foreach ($yyzz as $val) {

                        $pass   =   true;
                        if (!$val['check']) {
                            $pass = false;
                        }
                        if ($this->config['com_cert_owner'] == 1 && !$val['owner_cert']) {
                            $pass = false;
                        }
                        if ($this->config['com_cert_wt'] == 1 && !$val['wt_cert']) {
                            $pass = false;
                        }
                        if ($pass) {
                            $checkuid[] = $val['uid'];
                        }
                    }
                }
            } else {

                $checkuid[] = $_POST['uid'];
            }

            $yyzzkdata  =   array('yyzz_status' => $status);

            $CompanyM->upInfo($checkuid, '', $yyzzkdata);

            $checkdata  =   array('status' => $status);

            $checwhere['uid']   =   array('in', pylode(',', $checkuid));
            $checwhere['type']  =   3;

            $CompanyM->upCertInfo($checwhere, $checkdata, array('utype' => 'admin'));
        }

        $ty =   $status = 1 ? '已认证' : '待认证';

        $this->ACT_layer_msg('(企业列表)' . implode(',', $msg) . '批量设置' . $ty . '成功(ID:' . pylode(',', $_POST['uid']) . ')', 9, $_SERVER['HTTP_REFERER'], 2, 1);
    }

	/**
	 * 列表-认证审核
	 */
	function comStatus(){

	    $companyorder   =   $this->MODEL('companyorder');

	    $companyM       =   $this->MODEL('company');

	    $status         =   intval($_POST['r_status']);

	    if ($status == '') {

	        $this->ACT_layer_msg('请选审核状态！', 8, $_SERVER['HTTP_REFERER']);
	    }

	    if ($_POST['uid']) {

	        $uid  =  $_POST['uid'];

	        if ($status != 1) {

	            $yyzz_status = 2;

	        } else {

	            $yyzz_status = 1;

	            // 如果是“审核通过”，判断之前是否有过“审核通过的记录”，没有则增加企业资质审核通过的积分（只有第一次审核通过才加积分）

	            if (is_array($uid) && ! empty($uid)) {

	                $comids     =   @explode(',', $uid);

	                $paywhere['com_id'] = array('in', pylode(',', $comids));

	                $paywhere['pay_remark'] = '认证企业资质';

	                $companypay = $companyorder->getPayList($paywhere, array('field' => 'com_id'));

	                foreach ($companypay as $k => $v) {

	                    if (in_array($v, $uid)) {

	                        unset($uid[$k]);
	                    }
	                }
	                foreach ($uid as $v) {

	                    $this->MODEL('integral')->invtalCheck($v, 2,'integral_comcert', '认证企业资质',21);
	                }

	            } elseif ($uid != '') {

	                $paywhere['com_id'] = $uid;

	                $paywhere['pay_remark'] = '认证企业资质';

	                $num = $companyorder->getCompanyPayNum($paywhere);

	                if ($num < 1) {

	                    $this->MODEL('integral')->invtalCheck($uid, 2,'integral_comcert', '认证企业资质',21);
	                }
	            }
	        }

	        $companyData = array('yyzz_status' => $yyzz_status);

	        $companyM   ->  upInfo($uid, '', $companyData);

	        $companycertData = array(

	            'status'     => $status,

	            'statusbody' => $_POST['statusbody']
	        );

	        $id = $companyM -> upCertInfo(array('type'=>'3','uid' => array('in', pylode(',', $uid))), $companycertData,array('utype'=>'admin'));


	        // 职位免审核开启，管理勾选同步审核职位，未审核职位同步审核成功
	        $jobM   =   $this -> MODEL('job');
	        if ($this->config['com_free_status'] == '1' && $_POST['job_status']) {

	            $jobM   ->  upInfo(array('state'=>'1','r_status'=>1), array('state'=>'0', 'uid'=>array('in',pylode(',', $uid))));
	        }

	        $jobM   ->  upInfo(array('yyzz_status'=>$yyzz_status), array('uid'=>array('in',pylode(',', $uid))));

	        $ComA       =   $companyM->getList(array('uid'=>array('in',pylode(',', $uid))), array('field' => 'uid,name,linkmail'));

	        $company    =   $ComA['list'];

	        if ($this->config['sy_email_set'] == '1') {

	            if (is_array($company)) {

	                $notice = $this->MODEL('notice');

	                foreach ($company as $v) {
	                    if ($this->config['sy_email_comcert'] == '1' && $status > 0) {
	                        if ($status == '1') {
	                            $certinfo = '企业资质审核通过！';
	                        } else {
	                            $certinfo = '企业资质审核未通过！';
	                        }
	                        $notice->sendEmailType(array(
	                            'email' => $v['linkmail'],
	                            'certinfo' => $certinfo,
	                            'comname' => $v['name'],
	                            'uid' => $v['uid'],
	                            'name' => $v['name'],
	                            'type' => 'comcert'
	                        ));
	                    }
	                }
	            }
	        }

	        /* 消息前缀 */
	        foreach($company as $v){

	            $uids[]  =  $v['uid'];

	            /* 处理审核信息 */
	            if ($status == 2){

	                $statusInfo  =  '很遗憾 , 贵公司企业资质未能通过审核';

	                if($_POST['statusbody']){

	                    $statusInfo  .=  ' , 原因：'.$_POST['statusbody'];

	                }

	                $msg[$v['uid']]  =  $statusInfo;

	            }elseif($status == 1){

	                $msg[$v['uid']]  =  '贵公司企业资质审核通过，招聘人才更轻松！';

	            }
	        }
	        //发送系统通知
	        $sysmsgM	=	$this->MODEL('sysmsg');

	        $sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>2, 'content'=>$msg));

	        if ($id) {

	            $this->ACT_layer_msg('企业资质审核(UID:' . $uid . ')设置成功！', 9, $_SERVER['HTTP_REFERER'], 2, 1);
	        } else {

	            $this->ACT_layer_msg('设置失败！', 8, $_SERVER['HTTP_REFERER']);
	        }
	    } else {

	        $this->ACT_layer_msg('非法操作！', 8, $_SERVER['HTTP_REFERER']);
	    }
	}
	/**
	 * 列表-认证审核弹窗: 查询审核原因
	 */
	function sbody()
	{
	    $CompanyM = $this->MODEL('company');
	    
	    $where['type'] = 3;
	    
	    $where['uid'] = intval($_POST['uid']);
	    
	    $info = $CompanyM->getCertInfo($where, array('field' => 'statusbody'));
	    
	    echo $info['statusbody'];die;
	}
	//子账号列表
	function child_action(){

		$com_id		=	intval($_GET['com_id']);

		if(empty($com_id)){
			$this -> ACT_layer_msg('参数不全请重试！', 8);
		}

        $rows       =   array();
		$totalNum	=	0;
		if(!empty($com_id)){
		    $companyM       =   $this->MODEL('company');
		    $com            =   $companyM->getInfo($com_id, array('field'=>'name'));
		    $this->yunset('com', $com);
		    
			$comaM      	=   $this -> MODEL('companyaccount');
			$where['comid'] =   $com_id;

			$urlarr			=	$_GET;
			$urlarr['page']	=	'{{page}}';

			$pageurl		=	Url($_GET['m'], $urlarr, 'admin');

			$pageM		=	$this  -> MODEL('page');
			$pages		=	$pageM -> pageList('company_account', $where, $pageurl, $_GET['page']);
			//子账号列表
			if($pages['total'] > 0){
				$where['orderby']	=	'uid';
				$where['limit']		=	$pages['limit'];
				$rows	=	$comaM -> getWorkList($where);
			}
			$totalNum	=	$pages['total'];
		}

		$this -> yunset('totalNum', $totalNum);
		$this -> yunset('rows', $rows);
		$this -> yuntpl(array('admin/admin_company_child'));
	}

	/**
     * 添加 修改子账号
     * 2019-06-28
     */
    public function childeditsave_action(){

        $_POST      =   $this -> post_trim($_POST);
        $rurl       =   $_SERVER['HTTP_REFERER'];

		//参数判断
		$uid       	=   intval($_POST['uid']);
        if(empty($uid)){
            $this->ACT_layer_msg('非法操作！', 8, $rurl);
        }
        if(empty($_POST['submit'])){
            $this->ACT_layer_msg('参数错误！', 8, $rurl);
        }
        unset($_POST['submit']);
        $cuid       =   intval($_POST['cuid']);

        $memberM    =   $this -> MODEL('userinfo');
		unset($_POST['cuid']);
		unset($_POST['uid']);
        if(empty($cuid)){
            $res    =   $memberM -> addChildInfo(array(
                'uid'       =>  $uid,
                'cdata'     =>  $_POST
            ));
        }else{
            $res    =   $memberM -> updChildInfo(array('uid' => $cuid), $_POST);
        }

        //返回值
        if($res['ecode'] == 9){
            $this -> ACT_layer_msg($res['msg'], 9, $rurl);
        }else{
            $this -> ACT_layer_msg($res['msg'], 8);
        }

    }

	/**
     * 绑定 解绑子账号
     * 2019-06-28
     */
    public function childbind_action(){

        $_POST      =   $this -> post_trim($_POST);

        $comaM      =   $this -> MODEL('companyaccount');

        $res        =   $comaM -> bindChildInfo(array(
            'pid'   =>  $_POST['pid'],
            'type'  =>  $_POST['status'] == 1 ? 'unbind' : 'bind',
            'uid'   =>  $_POST['uid']
        ));
        $this->layer_msg($res['msg'], $res['ecode']);
	}

	/**
     * 分配套餐，获取目前的套餐
     */
    public function childgetstatis_action(){

		$cuid       =   intval($_POST['uid']);
		$pid		=	intval($_POST['pid']);
        if(empty($cuid)){
            echo json_encode(array('ecode' => 8, 'msg' => '参数错误'));
            die;
        }
        $inids      =   $pid.','.$cuid;

        $statisM    =   $this -> MODEL('statis');
        $statisList =   $statisM -> getList(array('uid' => array('in', $inids)));
        if(empty($statisList)){
            echo json_encode(array('ecode' => 8, 'msg' => '主账号套餐不存在'));
            die;
        }

        $fatherS    =   $sonS   =   array();
        foreach ($statisList as $sv) {
            if($sv['uid'] == $pid){
                $fatherS                =   $sv;
            }elseif ($sv['uid'] == $cuid) {
                $sonS                   =   $sv;
            }
        }
        $res        =   array(
            'fathers'   =>  $fatherS,
            'sons'      =>  $sonS
        );
        echo json_encode(array('ecode' => 9, 'msg' => 'ok', 'data' => $res));
        die;

	}

	/**
     * 保存分配的套餐
     * 2019-07-01
     */
    public function childassignsave_action(){

        $_POST      =   $this -> post_trim($_POST);
        $rurl       =   $_SERVER['HTTP_REFERER'];

        $pid        =   intval($_POST['pid']);
        if(empty($pid)){
            $this->ACT_layer_msg('请重新操作！', 8, $rurl);
        }

        //参数判断
        if(empty($_POST['submit'])){
            $this->ACT_layer_msg('参数错误！', 8, $rurl);
        }
        unset($_POST['submit']);
        $cuid       =   intval($_POST['cuid']);
        if(empty($cuid)){
            $this->ACT_layer_msg('非法操作！', 8, $rurl);
        }

        $statisM    =   $this -> MODEL('statis');
        unset($_POST['cuid']);
        unset($_POST['pid']);

        $_POST['uid']   =   $pid;
        $_POST['spid']  =   $cuid;
        $res            =   $statisM -> assignChildStatis($_POST);

        //返回值
        if($res['ecode'] == 9){
            $this -> ACT_layer_msg($res['msg'], 9, $rurl);
        }else{
            $this -> ACT_layer_msg($res['msg'], 8);
        }

    }

	/**
	 * @desc 企业微海报
	 */
    function mwhb_action()
    {
        $WhbM       =   $this->MODEL('whb');
        $comHb      =   $WhbM->getWhbList(array('type' => 2, 'isopen' => '1'), array('only' => 1));
        $this->yunset('comHb', $comHb);

        $comid  =   intval($_GET['comid']);
        $this->yunset('comid', $comid);
        $this->yuntpl(array('wap/hb/admin_whb'));
    }

	/**
	 * 列表-企业扫码绑定
	 */
	function acwxbind(){

	    $time = time();
	    $randStr = $time.rand(1000,9999);

	    $cookie = $this->MODEL('cookie');
	    $cookie -> setCookie('acwxbind' ,$randStr, $time+3600);

	    $WxM = $this->MODEL('weixin');
	    $qrcode = $WxM->applyWxQrcode($randStr, 'acwxbind', $_POST['comid']);
	    if(!$qrcode){
	        echo 0;
	    }else{
	        echo $qrcode;
	    }
	}
	// 检测二维码扫码绑定情况
	function getacbindstatus_action(){

	    if(isset($_COOKIE['acwxbind'])){
	        $WxM	 =  $this->MODEL('weixin');
	        $result  =  $WxM->getWxLoginStatus($_COOKIE['acwxbind'], $_POST['comid']);
	        if($result['status'] == 1){

	            if (!empty($result['member'])){

	                $this->layer_msg('扫码绑定成功',9,0);
	            }else{

	                $this->layer_msg('扫码绑定失败',8);
	            }
	        }else{
	            $this->layer_msg('');
	        }
	    }else{
	        $this->layer_msg('');
	    }
	}

    function setLogo_action(){

        if ($_POST){

            $comM   =   $this->MODEL('company');

            $result =   $comM->setLogoByAdmin(array('logo' => $_POST['logo'], 'logo_status' => 0), array('uid' => $_POST['uid']));

            echo json_encode(array('errcode' => $result ? 9 : 8, 'msg' => $result ? '设置成功' : '设置失败'));
            die;
        }
    }

    // 校验企业名称
    function checkName_action(){
        $companyName   =   trim($_POST['companyName']);
        $userInfoM  =   $this->MODEL('userinfo');
        $check      =   $userInfoM->addMemberCheck(array('companyName' => $companyName));

        echo $check['msg'];
        die;
    }

    public function checkComName_action()
    {

        $comM = $this->MODEL('company');

        $coms = array();
        if (!empty($_POST['companyName'])) {

            $coms = $comM->getKhList(array('name' => array('like', $_POST['companyName'])));
        }
        echo json_encode($coms);
        die;
    }

    function addTuiWenTask_action()
    {

        if ($_POST) {

            $wxPubM =   $this->MODEL('wxpubtemp');

            $data   =   array(

                'cuid'      =>  $_POST['twtask_uid'],
                'content'   =>  $_POST['twtask_content'],
                'urgent'    =>  $_POST['twtask_urgent'],
                'wcmoments' =>  $_POST['twtask_wcmoments'],
                'gzh'       =>  $_POST['twtask_gzh'],
                'type'      =>  2
            );
            $return =   $wxPubM->addTwTask($data, array('auid' => $_SESSION['auid']));

            echo json_encode($return);
            exit();
        }
    }

    function statisDetail_action()
    {

        $where['uid']	=	$_GET['uid'];

        if (isset($_GET['type']) && !empty($_GET['type'])){

            $where['type']	=	$_GET['type'];
            $urlarr['type']	=	$_GET['type'];
        }

        $urlarr 		= 	$_GET;
        $urlarr['page'] = 	'{{page}}';

        $pageurl	=	Url($_GET['m'], $urlarr, 'admin');

        $pageM	= 	$this->MODEL('page');
        $pages 	=	$pageM->pageList('company_statis_detail', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['orderby']	=	'id';
            $where['limit'] 	= $pages['limit'];

            $statisM	=	$this->MODEL('statis');
            $rows 		= 	$statisM->getStatisDetail($where);
            $this->yunset('rows', $rows);
        }

        $typeArr 		= 	array('1' => '上架|发布 职位', '2' => '刷新职位', '3' => '下载简历', '4' => '邀请面试', '5' => '职位推荐', '6' => '紧急招聘', '7' => '职位置顶', '8' => '招聘会报名', '9' => '子账号', '10' => '直聊', '11' => '视频面试');
        $search_list 	= 	array();
        $search_list[] 	= 	array('param' => 'type', 'name' => '套餐类目', 'value' => $typeArr);
        $this->yunset(array('search_list' => $search_list));

        $this->yuntpl(array('admin/admin_company_statis_detail'));
    }

    function delStatisDetail_action()
    {

        $this->check_token();

        $delID		=	isset($_GET['del']) ? $_GET['del'] : $_GET['id'];
        $statisM 	= 	$this->MODEL('statis');
        $return 	= 	$statisM->delStatisDetail($delID, array('utype' => 'admin'));
        $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER'], 2, 1);
    }

    function bindPackage_action()
    {

        if ($_POST) {

            $comM	=   $this->MODEL('company');

            $result	=   $comM->upInfo($_POST['uid'], array(), array('package' => $_POST['package']));

            if ($result){

                $this -> MODEL('log') -> addAdminLog('企业（UID：'.$_POST['uid'].'）绑定会员套餐（ID：'.$_POST['package'].'）成功');
                $return	=	array('code' => 9, 'msg' => '操作成功！');
            }else{

                $return	=	array('code' => 8, 'msg' => '操作失败！');
            }

            echo json_encode($return);
            exit();
        }
    }
}
?>