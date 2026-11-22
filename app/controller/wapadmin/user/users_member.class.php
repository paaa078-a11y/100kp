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

class users_member_controller extends adminCommon
{

    function index_action()
    {
        if (!empty($_POST)) {

            $resumeM    =   $this->MODEL('resume');
            $where  =   $mwhere =   $mUids  =   array();

            $page   =   $_POST['page'];
            $limit  =   $_POST['limit'];

            if (isset($_POST['def_job'])) {
                if ($_POST['def_job'] == '1') {

                    $where['def_job']   =   array('>', 0);
                } else {

                    $where['def_job']   =   0;
                }
            }

            if (trim($_POST['keyword'])) {

                $keywordStr         =   trim($_POST['keyword']);
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['name']      =   array('like', $keywordStr);
                $where['telphone']  =   array('like', $keywordStr, 'or');
                $where['PHPYUNBTWEND_B']    =   '';
                $data['keyword']    =   $keywordStr;
            }

            if ($_POST['status']) {
                $status             =   intval($_POST['status']);
                $where['r_status']  =   $status;
            }
            if (isset($_POST['adtime'])) {
                if ($_POST['adtime'] == '1') {

                    $mwhere['reg_date'] = array('>', strtotime('today'));
                } else {

                    $mwhere['reg_date'] = array('>', strtotime('-' . intval($_POST['adtime']) . ' day'));
                }
            }
            if (isset($_POST['lotime'])) {
                if ($_POST['lotime'] == '1') {

                    $mwhere['login_date'] = array('>', strtotime('today'));
                } else {

                    $mwhere['login_date'] = array('>', strtotime('-' . intval($_POST['lotime']) . ' day'));
                }
            }
            if ($_POST['source']) {
                $mwhere['source'] = $_POST['source'];
            }
            if (!empty($mwhere)) {

                $UserinfoM  =   $this->MODEL('userinfo');
                $uidList    =   $UserinfoM->getList($mwhere, array('field' => '`uid`'));
                if (!empty($uidList)) {
                    foreach ($uidList as $uv) {
                        $mUids[] = $uv['uid'];
                    }
                } else {
                    $mUids  =   array(0);
                }
                $where['uid'][] =   array('in', pylode(',', $mUids));
            }

            $where['orderby']   =   'uid,desc';
            if ($page) {
                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =   array($pagenav, $limit);
            } else {
                $where['limit'] =   $limit;
            }
            $List   =   $resumeM->getResumeList($where, array('utype' => 'admin'));

            $data['list'] = count($List) ? $List : [];

            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $isOutcall = $appkey && $appsecret;
        $this->yunset('is_outcall', $isOutcall);// 是否配置外呼
        $this->yuntpl(array('wapadmin/user_member'));
    }
	function statis_action(){
        include(CONFIG_PATH.'db.data.php');
        $source         =  $arr_data['source'];
        foreach ($source as $k=>$v){
            $sourcearr[] = array('id'=>$k,"name"=>$v);
        }
        $statusarr = array(array('id'=>1,'name'=>'正常'),array('id'=>2,'name'=>'锁定'));
        $rgtimearr = array(array('id'=>1,'name'=>'今天'),array('id'=>3,'name'=>'最近三天'),array('id'=>7,'name'=>'最近七天'),array('id'=>15,'name'=>'最近半月'),array('id'=>30,'name'=>'最近一个月'));
        $lotimearr = array(array('id'=>1,'name'=>'今天'),array('id'=>3,'name'=>'最近三天'),array('id'=>7,'name'=>'最近七天'),array('id'=>15,'name'=>'最近半月'),array('id'=>30,'name'=>'最近一个月'));
        $data['source'] = $source;
        $data['sourcearr'] = $sourcearr;
        $data['statusarr'] = $statusarr;
        $data['rgtimearr'] = $rgtimearr;
        $data['lotimearr'] = $lotimearr;
        header('content-type:application/json; charset=utf-8');
        echo json_encode($data);die;
    }
	function status_action(){
		$userinfoM	=	$this -> MODEL('userinfo');
		$post		=	array(
			'status'	=>	intval($_POST['status']),
			'lock_info'	=>	trim($_POST['statusbody'])
		);
		$return		=	$userinfoM -> status(array('uid'=> $_POST['id'],'usertype'=>1),array('post'=>$post));
		if($return['errcode']==9){
			$this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg($return['msg'],$return['errcode']);
		}
	}

    function member_log_action()
    {
        if ($_POST) {

            $TongjiM    =   $this->MODEL('tongji');

            $where      =   array();

            $page       =   $_POST['page'];
            $limit      =   $_POST['limit'];

            $where['uid']       =   $_POST['uid'];
            $where['usertype']  =   1;

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
        $this->yuntpl(array('wapadmin/user_member_log'));
    }

    function info_action(){
        if ($_POST) {
            $resumeM    =   $this -> MODEL('resume');
            $where['uid'] = $_POST['uid'];
            $resume = $resumeM->getResumeInfo($where);
            $data['userinfo'] = $resume;
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
    }

    function expectlist_action(){
        if ($_POST){
            $resumeM    =   $this -> MODEL('resume');
            $where['uid'] = $_POST['uid'];
            $resume = $resumeM->getList($where,array('utype'=>'admin'));
            $data['list'] = count($resume['list'])? $resume['list'] : [];
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
    }
	function edit_action(){
	    $_POST=$this->post_trim($_POST);
		if((int)$_GET['id']){
			$userinfoM	=	$this->MODEL('userinfo');
			$member		=	$userinfoM -> getInfo(array('uid'=>(int)$_GET['id']));
			$resumeM	=	$this -> MODEL('resume');
			$return		=	$resumeM -> getInfo(array('uid'=>(int)$_GET['id'], 'needCache'=>1));
			$setarr		=	array(
				'user_info'			=>	$member,
				'row'				=>	$return['resume'],
				'user_sex'			=>	$return['cache']['user_sex'],
				'userdata'			=>	$return['cache']['userdata'],
				'userclass_name'	=>	$return['cache']['userclass_name'],
				'lasturl'			=>	$_SERVER['HTTP_REFERER']
			);
			$this->yunset($setarr);
		}
		if($_POST['com_update']){
			$uid	=	intval($_POST['uid']);
			$mData	=	array(
				'username'	=>	$_POST['username'],
				'password'	=>	$_POST['password'],
				'moblie'	=>	$_POST['moblie'],
				'email'		=>	$_POST['email'],
				'status'	=>	$_POST['status']
			);
			$rData	=	array(
				'name'			=>	$_POST['name'],
				'sex'			=>	$_POST['sex'],
				'birthday'		=>	$_POST['birthday'],
				'exp'			=>	$_POST['exp'],
				'edu'			=>	$_POST['edu'],
				'telphone'		=>	$_POST['moblie'],
				'email'			=>	$_POST['email'],
				'domicile'		=>	$_POST['domicile'],
				'living'		=>	$_POST['living'],
				'marriage'		=>	$_POST['marriage'],
				'height'		=>	$_POST['height'],
				'nationality'	=>	$_POST['nationality'],
				'weight'		=>	$_POST['weight'],
				'idcard'		=>	$_POST['idcard'],
				'address'		=>	$_POST['address'],
				'homepage'		=>	$_POST['homepage'],
				'qq'			=>	$_POST['qq'],
				'description'	=>	$_POST['description'],
				'r_status'		=>	$_POST['status']
			);
			$resumeM	=	$this->MODEL('resume');
			$return		=	$resumeM -> upResumeInfo(array('uid'=>$uid),array('mData'=>$mData,'rData'=>$rData,'utype'=>'admin'));
			$lasturl	=	str_replace('&amp;','&',$_POST['lasturl']);
			$this->ACT_layer_msg($return['msg'], $return['errcode'], $lasturl, 2, 1);
		}
		
		$lasturl=$_SERVER['HTTP_REFERER'];
		if(strpos($lasturl, 'a=edit')===false){
		    if(strpos($lasturl, 'c=user_member')!==false){
		        $this->cookie->setcookie('lasturl',$lasturl,time()+300);
		        $_COOKIE['lasturl']=$lasturl;
		    }
		}
		$this->yunset('lasturl',$_COOKIE['lasturl']);
		
		$this->yunset("headertitle","个人用户");
		$this->yuntpl(array('wapadmin/admin_member_useredit'));
	}

	function add_action(){
		$this->yuntpl(array('wapadmin/admin_member_useradd'));
	}
	function save_action(){
		if($_POST['submit']){
			if($_POST['username']==''||mb_strlen($_POST['username'])<2||mb_strlen($_POST['username'])>16){
				$this -> ACT_layer_msg('用户名格式错误',8);
			}elseif($_POST['password']==''||mb_strlen($_POST['password'])<6||mb_strlen($_POST['password'])>20){
				$this -> ACT_layer_msg('密码格式错误',8);
			}elseif($_POST['moblie']==''){
				$this -> ACT_layer_msg('手机号不能为空',8);
			}
			$userinfoM  =  $this -> MODEL('userinfo');
			$result  	=  $userinfoM -> addMemberCheck($_POST);
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
			if($this->config['sy_uc_type']=='uc_center'){
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
					list($uid,$username,$email,$password,$salt)=uc_get_user($_POST['email'],$pass);
				}
			}else{
				$salt  =  substr(uniqid(rand()), -6);
				$password  =  passCheck($pass,$salt);
			}
			$mdata = array(
				'username'	=>	$_POST['username'],
				'password'	=>	$password,
				'usertype'	=>	1,
				'salt'		=>	$salt,
				'moblie'	=>	$_POST['moblie'],
				'email'		=>	$_POST['email'],
				'reg_date'	=>	$time,
				'reg_ip'	=>	$ip,
				'status'	=>	1
			);
			$udata = array(
				'email'		=>	$_POST['email'],
				'telphone'	=>	$_POST['moblie'],
				'r_status'	=>	1
			);
			$nid  =  $userinfoM -> addInfo(array('mdata'=>$mdata,'udata'=>$udata));
			if($nid > 0){
				$this->ACT_layer_msg('个人会员(ID:'.$nid.')添加成功！',9,'index.php?m=user_member');
			}else{
				$this->ACT_layer_msg('个人会员添加失败，请重试！',8);
			}
		}
	}
	function del_action(){
		if ($_GET['del']){
			$uid	=	intval($_GET['del']);
		}elseif ($_POST['del']){
			$uid	=	$_POST['del'];
		}
		$userinfoM	=	$this -> MODEL('userinfo');
		$return		=	$userinfoM -> delInfo($uid, 1);
		$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}

	function reset_pw_action(){
		$userinfoM	=	$this->MODEL('userinfo');
		$userinfoM -> upInfo(array('uid'=>intval($_GET['uid'])),array('password'=>'123456'));
		$this -> MODEL('log') -> addAdminLog('会员(ID:'.$_GET['uid'].')重置密码成功');
		echo "1";
	}

	function checksitedid_action(){
		$uid		=	trim($_POST['uid']);
		$did		=	intval($_POST['did']);
		if(empty($uid)){
			$this -> ACT_layer_msg('参数不全请重试！', 8);
		}
		$uids		=	@explode(',',$_POST['uid']);
		$uid 		=	pylode(',',$uids);
		if(empty($uid)){
			$this -> ACT_layer_msg('请正确选择需分配用户！', 8);
		}
		$siteM		=	$this->MODEL('site');
		$didData	=	array('did' => $did);
		$Table		=	array(
			'company_cert','company_msg','company_order','invoice_record','look_job','member',
			'member_statis','resume','resume_expect','user_entrust','userid_job'
		);
		$siteM -> updDid(array('report'), array('p_uid' => array('in', $uid)), $didData);
		$siteM -> updDid(array('company_pay'),array('com_id'=>array('in', $uid)),$didData);
		$siteM -> updDid($Table,array('uid'=>array('in', $uid)),$didData);
		$this->ACT_layer_msg('会员(ID:'.$_POST['uid'].')分配站点成功！',9,$_SERVER['HTTP_REFERER']);
	}
	function lock_action(){
		$userinfoM	=	$this -> MODEL('userinfo');
		$post		=	array(
			'status'     =>  intval($_POST['status']),
			'lock_info'  =>  trim($_POST['lockbody'])
		);
		$return		=	$userinfoM -> lock(array('uid'=>intval($_POST['id']),'usertype'=>1),array('post'=>$post));
		
		if($return['errcode']==9){
			$this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg($return['msg'],$return['errcode']);
		}
	}

    /**
     * @desc CRM-个人列表:（虚拟号联系客户）
     */
    function contactCustomer_action()
    {
        $adminM = $this->MODEL('admin');
        $staffCallM = $this->MODEL('staffcalllog');
        $adminInfo = $adminM->getAdminUser(array('uid' => $_SESSION['wuid']));
        if (!$adminInfo['moblie']) {
            echo json_encode(['code' => 400, 'msg' => '业务员未填写手机号']);exit;
        }
        if (!$_POST['uid']) {
            echo json_encode(['code' => 400, 'msg' => '用户uid不能为空']);exit;
        }
        $mobile = trim($_POST['mobile']);
        if (!$mobile || !CheckMobile($mobile)) {
            echo json_encode(['code' => 400, 'msg' => '手机号格式错误']);exit;
        }
        $apiurl = 'https://u.phpyun.com/call';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            echo json_encode(['code' => 400, 'msg' => '请先完成外呼配置！']);exit();
        }
        $params = array(
            'appKey' => $appkey,
            'appSecret' => $appsecret,
            'caller' => $adminInfo['moblie'],
            'callee' => $mobile
        );
        $res = CurlPost($apiurl, $params, 1);
//        file_put_contents(APP_PATH."new_call_log.txt",'客服外呼接口返回信息：('.date('Y-m-d H:i:s').')'.$res.";\n",FILE_APPEND);
        $result = json_decode($res, 1);
        $callLog = array(
            'uid' => $_SESSION['wuid'],
            'username' => $adminInfo['name'],
            'session_id' => isset($result['data']['session_id']) ? $result['data']['session_id'] : '',
            'caller' => $params['caller'],
            'callee' => $params['callee'],
            'log_id' => isset($result['data']['log_id']) ? $result['data']['log_id'] : 0,// 综合平台外呼记录id
            'usertype' => 1,// 1:个人，2：企业，不传默认为企业
            'has_session' => isset($result['data']['has_session']) ? $result['data']['has_session'] : 0,// 是否有session_id
            'cuid' => $_POST['uid']
        );
        $callLog['api_res'] = json_encode(json_decode($res, 1), JSON_UNESCAPED_UNICODE);
        if ($result['code'] == 200) {// 呼叫成功
            $callLog['status'] = 1;
        } else {
            $callLog['status'] = 0;
        }
        $id = $staffCallM->addData($callLog);
        if ($result['code'] == 200) {
            echo json_encode(['code' => 200, 'msg' => '呼叫成功', 'data' => $id ? $id : 0]);exit();
        } else {
            echo json_encode(['code' => 400, 'msg' => $result['message'], 'data' => $id ? $id : 0]);exit();
        }
    }
}

?>