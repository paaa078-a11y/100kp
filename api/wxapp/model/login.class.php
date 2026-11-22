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
class login_controller extends wxapp_controller{
	
	function getCon_action(){
		
		$data	=	array(
			'sy_login_type'				=>	$this->config['sy_login_type']
		);
		if (isset($_POST['provider']) && ($_POST['provider'] == 'weixin' || $_POST['provider'] == 'baidu')) {
		    // 小程序关联登录入口判断
		    if ($_POST['provider'] == 'weixin'){
		        @include(DATA_PATH.'api/wxpay/wxpay_data.php');
		        if (isset($wxpaydata)){
		            if($wxpaydata['sy_xcxappid'] == '' || $wxpaydata['sy_xcxsecret'] ==''){
		                $data['isWxopen'] = false;
		            }else{
		                $data['isWxopen'] = true;
		            }
		        }
		    }
		    if ($_POST['provider'] == 'baidu'){
		        @include(DATA_PATH . 'api/baidu/baidu_data.php');
		        if (isset($baiduData)) {
		            if($baiduData['sy_bdlogin_appKey'] == '' || $baiduData['sy_bdlogin_appSecret'] == ''){
		                $data['isBdopen'] = false;
		            }else{
		                $data['isBdopen'] = true;
		            }
		        }
		    }
		}
		$data['sy_reg_type']    =   isset($this->config['sy_reg_type']) ? $this->config['sy_reg_type'] : 1;

        $data['sy_tt_phoneauth']=   isset($this->config['sy_tt_phoneauth']) ? $this->config['sy_tt_phoneauth'] : 0;

		$this -> render_json(1,'',$data);
	}
    /**
     * 账号登录
     */
    function ulogin_action()
    {
        if (isset($_POST['version']) && $_POST['version']=='6.1'){
            // 6.1版开始对注册、登录传的相关参数进行加密，这里需要解密
            $username = $this->decryptRequest($_POST['username']);
            $password = $this->decryptRequest($_POST['password']);
        }else{
            $username = $_POST['username'];
            $password = $_POST['password'];
        }
        $lData  =  array(
            'username'     =>  $this->stringfilter($username),
            'password'     =>  $this->stringfilter($password),
            'wxapp'        =>  1,
            'provider'     =>  $_POST['provider'],
            'clientid'     =>  $_POST['clientid'],
            'deviceToken'  =>  $_POST['deviceToken'],
            'source'       =>  $_POST['source'],
            'port'         =>  $this->port
        );
        
		if($_POST['act_login'] == 2){
			$lData['act_login'] = 1;
		}

		if ($_POST['is_yuliu']) {
            $lData['age'] = intval($_POST['age']);
            $lData['realname'] = trim($_POST['realname']);
            $lData['from'] = 3;
            $lData['source'] = 26;// 预留信息
            $lData['sex'] = intval($_POST['sex']);
            $lData['jobid'] = intval($_POST['jid']);
            $lData['is_yuliu'] = 1;
            $lData['act_login'] = 1;
            if ($this->plat == 'mini'){
                // 小程序不用验证短信验证码
                $lData['nocheckcode'] = 1;
            }else{
                $lData['nocheckcode'] = 0;
            }
        }

        $userinfoM  =  $this->MODEL("userinfo");

        $return     =  $userinfoM->userLogin($lData);

        if ($_POST['is_yuliu']) {
            $data['msg'] = $return['msg'];
            $data['error']	= $return['errcode'];
            if ($return['jobid']) {
                $data['jobid'] = intval($return['jobid']);
            }
            if ($return['eid']) {
                $data['eid'] = intval($return['eid']);
            }
            if ($return['user']) {
                $data['user'] = $return['user'];
            }
            $this->render_json($return['errcode'], $return['msg'], $data);
        } else {
            if($return['errcode']==2 || $return['user']['usertype'] > 2){
                $error = 1;
            }else if($return['errcode']==9){
                $error = 0;
            }else{
                $error = -1;
            }
            $msg = $return['msg'];
            $this->render_json($error, $msg, $return);
        }
    }
    // 第三方登录验证，目前只有APP和头条账号授权在用
    function loginCheck_action()
    {
        $userinfoM  =  $this->MODEL("userinfo");
        $ckData     =  array(
            'clientid'     =>  $_POST['clientid'],
            'deviceToken'  =>  $_POST['deviceToken'],
            'source'       =>  $_POST['source']
        );
        
        if ($_POST['type'] == 'weixin'){
            // app微信登录
            $getdata['openid']  =  $_POST['openid'];
            if (!empty($_POST['unionid']) && $_POST['unionid'] != 'undefinded'){
                $getdata['unionid']  =  $_POST['unionid'];
            }
            
            $data		=	array();
            
            if($getdata['openid']){
                
                $userinfoM  =  $this->MODEL("userinfo");
                
                $ckData['type']         =  $_POST['type'];
                $ckData['provider']     =  $_POST['provider'];
                $ckData['openid']       =  $getdata['openid'];
                $ckData['unionid']      =  !empty($getdata['unionid']) ? $getdata['unionid'] : '';
                
                $data  =  $userinfoM->loginCheck($ckData, false);
                
            }else{
                
                $data['error']  =  3;
                $data['user']   =  array();
                if (isset($getdata['errcode'])){
                    //$this->MODEL('errlog')->addErrorLog('', 10, 'wxapp微信登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg']);
                    if($getdata['errcode'] == 40125){
                        $data['msg']  =  '微信小程序配置错误，请联系网站管理员';
                    }else{
                        $data['msg']  =  $getdata['errcode'] ? '错误号:'.$getdata['errcode'] : '出错了，请重试';
                    }
                }else{
                    $data['msg']		=	'参数错误';
                }
            }
        }elseif ($_POST['type']=='toutiao'){
            // 小程序头条登录，第一次用code获取，后面用小程序里传过来的
            if (!empty($_POST['code'])){
                $getdata    =   $this->getTtOpenid($_POST['code'], $_POST['appid']);
            }else{
                $getdata['openid']  =  $_POST['openid'];
            }

            $data       =   array();

            if($getdata['openid']){

                $userinfoM  =  $this->MODEL("userinfo");

                $ckData['type']         =  $_POST['type'];
                $ckData['provider']     =  $_POST['provider'];
                $ckData['openid']       =  $getdata['openid'];
                $ckData['unionid']      =  !empty($getdata['unionid']) ? $getdata['unionid'] : '';
                
                $data  =  $userinfoM->loginCheck($ckData, false);

            }else{

                $data['error']  =  3;
                $data['user']   =  array();
                if (isset($getdata['errno'])){
                    //$this->MODEL('errlog')->addErrorLog('', 10, '抖音小程序登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['error'].'，'.$getdata['error_description']);
                    if($getdata['errno'] == 10010100){
                        $data['msg']  =  '抖音小程序配置错误，请联系网站管理员';
                    }else{
                        $data['msg']  =  $getdata['errno'] ? '错误号:'.$getdata['errno'] : '出错了，请重试';
                    }
                }else{
                    $data['msg']        =   '参数错误';
                }
            }
        }else{
            
            $ckData['type']     =  $_POST['type'];
            $ckData['openid']   =  $_POST['openid'];
            $ckData['unionid']  =  $_POST['unionid'];
            $ckData['provider'] =  $_POST['provider'];
            $data  =  $userinfoM->loginCheck($ckData);
        }
        if(!isset($data['user'])){
            $data['user'] = array();
        }
        // 小程序获取到openid的，回传留作后面使用
        if ($this->plat=='mini' && !empty($getdata['openid'])){
            $data['user']['openid'] = $getdata['openid'];
            $data['user']['unionid'] = !empty($getdata['unionid']) ? $getdata['unionid'] : '';
        }
		$this->render_json($data['error'],$data['msg'], $data['user']);
    }
    
	function sendmsg_action()
	{
	    $moblie			=		$_POST['moblie'];
	    
	    $this->checkMcsdk($moblie);
	    
		$noticeM 		= 		$this->MODEL('notice');
		
		$UserinfoM		=		$this->MODEL('userinfo');
		$userinfo 		= 		$UserinfoM->getInfo(array("moblie" => $moblie),array('field'=>"`usertype`,`uid`"));

		if ($this->config['sy_reg_type'] == 2 && empty($userinfo) && !isset($_POST['is_yuliu'])){

            $errcode    =   3;
            $msg        =   '请先注册账号';

        }else{
            $user = array(
                'uid' => $userinfo['uid'],
                'usertype' => $userinfo['usertype']
            );
            $result     =   $noticeM->sendCode($moblie, 'login', $this->port, $user, 6, 90, 'msg');
            if($result['error']==1){
                $errcode	=	1;
            }else{
                $errcode	=	2;
                $msg		=	$result['msg'];
            }
        }

		$this->render_json($errcode,$msg);
	}
	function getConfig_action()
    {
        $return  =  array(
            'reg_real_name_check'   =>  $this->config['reg_real_name_check'],
            'sy_reg_type'           =>  isset($this->config['sy_reg_type']) ? $this->config['sy_reg_type'] : 1
        );
        
		$this->render_json(1,'', $return);
    }
    
    // 创建新账号、手机号一键绑定（手机号已存在的，微信绑定已存在手机号）
    function balogin_action(){

        $ckData['type']       =  $_POST['type'];
        $ckData['source']     =  $_POST['source'];
        $ckData['openid']     =  $_POST['openid'];
        $ckData['unionid']    =  $_POST['unionid'];
        $ckData['provider']   =  $_POST['provider'];
        $ckData['cnewcount']  =  $_POST['cnewcount'];
        
        $userinfoM	=  $this->MODEL('userinfo');
        $data  =  $userinfoM->loginCheck($ckData);
        
		if(!isset($data['user'])){
		    $data['user'] = array();
		}
	    $this->render_json($data['error'],$data['msg'], $data['user']);
	}

    function jscode2session_action(){
        $ckData     =  array(
            'provider'     =>  $_POST['provider'],
            'source'       =>  $_POST['source'],
            'type'         =>  $_POST['provider'],
        );
        
        $data       =   array();
        $data['oauthData']  = array();

        if ($_POST['provider'] == 'weixin'){
           
            $getdata    =   $this->getOpenid($_POST['code'], $_POST['appid']);
            
        }else if ($_POST['provider'] == 'baidu'){
            
            $getdata    =   $this->getBdOpenid($_POST['code']);
        }else if ($_POST['provider']=='toutiao'){

            $getdata    =   $this->getTtOpenid($_POST['code'], $_POST['appid']);
            
        } 
        if($getdata['openid']){

            $data['error']  =  1;

            $oauthData['openid']       =  $getdata['openid'];
            $oauthData['unionid']      =  !empty($getdata['unionid']) ? $getdata['unionid'] : '';

            $oauthData['appid']        =  $getdata['appid'];

            $openssl  = new OpensslCrypt($this->xcxKey, $this->xcxPy);

            $oauthData['session_key']  = $openssl->miniEncrypt($getdata['session_key']);
            $oauthData['tologincheck'] = 0;
            if ($_POST['provider'] == 'weixin'){

                $userinfoM = $this->MODEL('userinfo');
                if (!empty($oauthData['unionid'])){
                
                    $uwhere  =  array('unionid'=>$oauthData['unionid'], 'wxopenid' => array('=', $oauthData['openid'], 'OR'));
                    
                }else{
                    $uwhere  =  array('wxopenid'=>$oauthData['openid']);
                }
                $userinfo  =  $userinfoM->getInfo($uwhere,array('field'=>'uid'));

                if(!empty($userinfo)){
                    $oauthData['tologincheck'] = 1;
                }
            }
            
            $data['oauthData']  = $oauthData;

        }else{

            $data['error']  =  -1;
            
            $errlogmsg = array(
                'weixin'    =>  'wxapp微信登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
                'baidu'     =>  '百度登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
                'toutiao'   =>  '抖音登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
            );
            $errmsg = array(
                'weixin'=>'微信小程序配置错误，请联系网站管理员',
                'baidu'=>'百度小程序配置错误，请联系网站管理员',
                'toutiao'=>'抖音小程序配置错误，请联系网站管理员',
            );
            if (isset($getdata['errcode'])){
                
                if($getdata['errcode'] == 40125){
                    $data['msg']  =  $errmsg[$_POST['provider']];
                }else{
                    $data['msg']  =  $getdata['errcode'] ? '错误号:'.$getdata['errcode'] : '出错了，请重试';
                }

            }else{
                $data['msg']        =   '参数错误';
            }
        }

        $this->render_json($data['error'],$data['msg'], $data);  
    }
    //小程序手机号授权登陆
    function phoneLoginCheck_action(){
        $userinfoM  =  $this->MODEL("userinfo");
        $ckData     =  array(
            'provider'     =>  $_POST['provider'],
            'source'       =>  $_POST['source'],
            'type'         =>  $_POST['provider'],
        );
        
        $data       =   array();


        if($_POST['notify_data'] && $_POST['openid']){

            $openssl  = new OpensslCrypt($this->xcxKey, $this->xcxPy);

            $getdata['session_key']  = $openssl->miniDecrypt($_POST['notify_data']);

            $getdata['openid'] = $_POST['openid'];

            $getdata['unionid'] = $_POST['unionid'];

            $getdata['appid'] = $_POST['preview'];

        }else{
            if ($_POST['provider'] == 'weixin'){
                // 微信小程序一键登录，需要session_key只能用code
                $getdata    =   $this->getOpenid($_POST['code'], $_POST['appid']);
                
            }else if ($_POST['provider'] == 'baidu'){
                // 百度小程序一键登录，需要session_key只能用code
                $getdata    =   $this->getBdOpenid($_POST['code']);
            }else if ($_POST['provider']=='toutiao'){

                $getdata    =   $this->getTtOpenid($_POST['code'], $_POST['appid']);
                
            }else if ($_POST['provider']=='alipay'){
                
                $getdata    =   $this->getAlipayuserid($_POST['code']);
                
            }  
        }
            

        if($getdata['openid']){

            $ckData['openid']       =  $getdata['openid'];
            $ckData['unionid']      =  !empty($getdata['unionid']) ? $getdata['unionid'] : '';

            $ckData['phoneEncrypt'] =  $_POST['rsaSign'];
            $ckData['phoneIv']      =  $_POST['sign'];
            $ckData['appid']        =  isset($getdata['appid']) ? $getdata['appid'] : '';
            $ckData['session_key']  =  isset($getdata['session_key']) ? $getdata['session_key'] : '';
            
            $ckData['aeskey']       =  isset($getdata['aeskey']) ? $getdata['aeskey'] : ''; // 支付宝小程序专用参数
            $ckData['backphone']    =  1;  // 小程序内手机号授权，未注册的只需拿到手机号，然后到信息填写页面提交注册，不用走快速注册

            $data       =  $userinfoM->decodePhoneNumber($ckData);
            
            
        }else{

            $data['error']  =  -1;
            $data['user']   =  array();

            $errlogmsg = array(
                'weixin'    =>  'wxapp微信登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
                'baidu'     =>  '百度登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
                'toutiao'   =>  '抖音登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
                'ailipay'   =>  '支付宝登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
            );
            $errmsg = array(
                'weixin'=>'微信小程序配置错误，请联系网站管理员',
                'baidu'=>'百度小程序配置错误，请联系网站管理员',
                'toutiao'=>'抖音小程序配置错误，请联系网站管理员',
                'alipay'=>'支付宝小程序配置错误，请联系网站管理员'
            );
            if (isset($getdata['errcode'])){
                if (isset($_POST['sign'])){
                    //$this->MODEL('errlog')->addErrorLog('', 10, $errlogmsg[$_POST['provider']]);
                }

                if($getdata['errcode'] == 40125){
                    $data['msg']  =  $errmsg[$_POST['provider']];
                }else{
                    $data['msg']  =  $getdata['errcode'] ? '错误号:'.$getdata['errcode'] : '出错了，请重试';
                }

            }else{
                $data['msg']        =   '参数错误';
            }
        }

        if(!isset($data['user'])){
            $data['user'] = array();
        }
        // 小程序获取到openid的，回传留作后面使用
        if ($this->plat == 'mini' && !empty($getdata['openid'])){
            $data['openid'] = $getdata['openid'];
            $data['unionid'] = !empty($getdata['unionid']) ? $getdata['unionid'] : '';
        }
        $this->render_json($data['error'],$data['msg'], $data);
    }
    //小程序手机号授权后，注册账号
    function saveLoginfo_action(){

        if (empty($_POST['usertype']) || empty($_POST['moblie'])){
            $this->render_json(-1, '请填写完成后再提交');
        }
        if($_POST['usertype']==1){
            if (empty($_POST['name']) || empty($_POST['sexid']) || empty($_POST['birthday'])){
                $this->render_json(-1, '请填写完成后再提交');
            }
        }else if($_POST['usertype']==2){
            if (empty($_POST['comname']) || empty($_POST['comlinkman'])){
                $this->render_json(-1, '请填写完成后再提交');
            }
        }
        $userinfoM  =  $this->MODEL('userinfo');
        $num = $userinfoM->getMemberNum(array('moblie'=>$_POST['moblie']));
        
        if ($num>0){
            $this->render_json(-1, '手机号已注册');
        }
        
        $getdata['openid']  =  $_POST['openid'];
        if (!empty($_POST['unionid']) && $_POST['unionid'] != 'undefinded'){
            $getdata['unionid']  =  $_POST['unionid'];
        }
        
        $ckData['from']         =  'loginfo';
        $ckData['moblie']       =  $_POST['moblie'];
        $ckData['provider']     =  $_POST['provider'];
        $ckData['source']       =  $_POST['source'];
        $ckData['openid']       =  $getdata['openid'];
        $ckData['unionid']      =  !empty($getdata['unionid']) ? $getdata['unionid'] : '';
        $ckData['usertype']     =  $_POST['usertype'];
        
        if($_POST['usertype']==1){
            $ckData['rdatav']['name']     =  $_POST['name'];
            $ckData['rdatav']['sex']      =  $_POST['sexid'];
            $ckData['rdatav']['birthday'] =  $_POST['birthday'];
            $ckData['rdatav']['telphone'] =  $_POST['moblie'];
        }else if($_POST['usertype']==2){
            $ckData['cdatav']['name']     =  $_POST['comname'];
            $ckData['cdatav']['linkman']  =  $_POST['comlinkman'];
            $ckData['cdatav']['linktel']  =  $_POST['moblie'];
        }
        
        $data  =  $userinfoM->fastReg($ckData,'wxxcx',$_POST['provider']);

        if(!isset($data['user'])){
            $data['user'] = array();
        }
        $this->render_json($data['error'],$data['msg'], $data['user']);
    }

    /**
     *users:王旭
     *Data:2023/2/13
     *Time:16:04
     */
    function jobsaveLoginfo_action(){

        if ($_POST['provider'] == 'weixin'){

            if (!empty($_POST['code'])){
                $getdata    =   $this->getOpenid($_POST['code'], $_POST['appid']);
            }else{
                $getdata['openid']  =  $_POST['openid'];
                if (!empty($_POST['unionid']) && $_POST['unionid'] != 'undefinded'){
                    $getdata['unionid']  =  $_POST['unionid'];
                }
            }
        }elseif ($_POST['provider']=='baidu'){

            if (!empty($_POST['code'])){
                $getdata    =   $this->getBdOpenid($_POST['code']);
            }else{
                $getdata['openid']  =  $_POST['openid'];
            }
        }else if ($_POST['provider']=='toutiao'){
            
            if (!empty($_POST['code'])){
                $getdata    =   $this->getBdOpenid($_POST['code']);
            }else{
                $getdata['openid']  =  $_POST['openid'];
            }
            
        }else if ($_POST['provider']=='alipay'){
            
            $getdata    =   $this->getAlipayuserid($_POST['code']);
            
        }  

        if (!empty($getdata['openid']) || $_POST['provider']=='app'){
            $ckData['from']         =  'loginfo';
            $ckData['moblie']       =  $_POST['moblie'];
            $ckData['provider']     =  $_POST['provider'];
            $ckData['source']       =  $_POST['source'];
            $ckData['openid']       =  $getdata['openid'];
            $ckData['unionid']      =  !empty($getdata['unionid']) ? $getdata['unionid'] : '';
            $ckData['usertype']     =  $_POST['usertype'];
            $ckData['clientid']       =   $_POST['clientid'];
            $ckData['deviceToken']    =   $_POST['deviceToken'];
            $ckData['moblie_code']    =   $_POST['mobliecode'];

            $ckData['rdatav']['name']     =  $_POST['name'];
            $ckData['rdatav']['birthday'] =  (intval(date('Y')) - intval($_POST['birthday'])).'-'.'01';;
            $ckData['rdatav']['telphone'] =  $_POST['moblie'];

            $userinfoM  =  $this->MODEL('userinfo');
            if ($_POST['provider']=='app'){
                $data = $userinfoM->fastReg($ckData, 'app', $_POST['provider']);
            }else {
                $data = $userinfoM->fastReg($ckData, 'wxxcx', $_POST['provider']);
            }

        }else{

            $data['error']  =  -1;
            $data['user']   =  array();

            $errlogmsg = array(
                'weixin'    =>  'wxapp微信登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
                'baidu'     =>  '百度登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
                'toutiao'   =>  '抖音登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
                'ailipay'   =>  '支付宝登录获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg'],
            );
            $errmsg = array(
                'weixin'=>'微信小程序配置错误，请联系网站管理员',
                'baidu'=>'百度小程序配置错误，请联系网站管理员',
                'toutiao'=>'抖音小程序配置错误，请联系网站管理员',
                'alipay'=>'支付宝小程序配置错误，请联系网站管理员'
            );

            if (isset($getdata['errcode'])){

                //$this->MODEL('errlog')->addErrorLog('', 10,$errlogmsg[$_POST['provider']]);

                if($getdata['errcode'] == 40125){

                    $data['msg']  =  $errmsg[$_POST['provider']];

                }else{
                    $data['msg']  =  $getdata['errcode'] ? '错误号:'.$getdata['errcode'] : '出错了，请重试';
                }
            }else{
                $data['msg']        =   '参数错误';
            }
        }

        if(!isset($data['user'])){
            $data['user'] = array();
        }
        $this->render_json($data['error'],$data['msg'], $data['user']);
    }
    /**
	 * 后台未开启实名认证，需绑定已有账号，登录验证并绑定
	 */
	function baloginsave_action(){

	    if (isset($_POST['version']) && $_POST['version']=='6.1'){
	        // 6.1版开始对注册、登录传的相关参数进行加密，这里需要解密
	        $username = $this->decryptRequest($_POST['username']);
	        $password = $this->decryptRequest($_POST['password']);
	    }else{
	        $username = $_POST['username'];
	        $password = $_POST['password'];
	    }
        $username = $this->stringfilter($username);
        $password = $this->stringfilter($password);
	        
		if ($_POST['type'] == 'weixin'){
	        // wxlogin页面，绑定账号来源是微信的，只有APP
		    $wdata  =  array(
		        'openid'       =>  $_POST['openid'],
		        'unionid'      =>  !empty($_POST['unionid']) && $_POST['unionid'] != 'undefinded' ? $_POST['unionid'] : '',
		        'source'       =>  	$_POST['source'],
		        'username'     =>  	$username,
		        'password'     =>  	$password,
		        'clientid'     =>  	$_POST['clientid'],
		        'deviceToken'  =>  	$_POST['deviceToken'],
		        'port'         =>  	4,	// 短信发送端口$port : 3-小程序  4-APP
		        'provider'	   =>	$_POST['provider']
		    );
		    $userinfoM	=  $this->MODEL('userinfo');
		    $result	    =  $userinfoM->bindacount($wdata, 'weixin');
		    
		    if ($result['errcode'] == 9){
		        
		        $data['user']     =  $result['user'];
		        $data['errcode']  =  1;
		        $data['msg']      =  '';
		        
		    }else if($result['errcode'] == 2){
		        $data['user']     =  $result['user'];
		        $data['errcode']  =  2;
		        $data['msg']      =  '';
		    }else{
		        
		        $data['errcode']   =  -1;
		        $data['msg']       =  $result['msg'];
		    }
	    }elseif ($_POST['type'] == 'toutiao'){
            
            
            // 小程序抖音登录，第一次用code获取，后面用小程序里传过来的
            if (!empty($_POST['code'])){
                $getdata    =   $this->getTtOpenid($_POST['code'], $_POST['appid']);
            }else{
                $getdata['openid']  =  $_POST['openid'];
                if (!empty($_POST['unionid']) && $_POST['unionid'] != 'undefinded'){
                    $getdata['unionid']  =  $_POST['unionid'];
                }
            }
            
            if (!empty($getdata['openid'])){
                
                $wdata  =  array(
                    'openid'       =>  $getdata['openid'],
                    'unionid'      =>  $getdata['unionid'],
                    'source'       =>   $_POST['source'],
                    'username'     =>   $username,
                    'password'     =>   $password,
                    'port'         =>   $this->port,
                    'provider'     =>   $_POST['provider']
                );
                $userinfoM  =  $this->MODEL('userinfo');
                $result     =  $userinfoM->bindacount($wdata, 'toutiao');
                
                if ($result['errcode'] == 9){
                    
                    $data['user']     =  $result['user'];
                    $data['errcode']  =  1;
                    $data['msg']      =  '';
                    
                }else if($result['errcode'] == 2){
                    $data['user']     =  $result['user'];
                    $data['errcode']  =  2;
                    $data['msg']      =  '';
                }else{
                    
                    $data['errcode']   =  -1;
                    $data['msg']       =  $result['msg'];
                }
                
            }else{
                
                $data['errcode']  =  -1;
                $data['user']   =  array();
                if (isset($getdata['errcode'])){
                    //$this->MODEL('errlog')->addErrorLog('', 10, '小程序抖音登录绑定账号获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg']);
                    if($getdata['errcode'] == 40125){
                        $data['msg']  =  '抖音小程序配置错误，请联系网站管理员';
                    }else{
                        $data['msg']  =  $getdata['errcode'] ? '错误号:'.$getdata['errcode'] : '出错了，请重试';
                    }
                }else{
                    $data['msg']      = '参数错误';
                }
            }
        }elseif ($_POST['type'] == 'qq'){
            
	        $wdata  =  array(
                'openid'       =>  	$_POST['openid'],
                'unionid'      =>  	$_POST['unionid'],
                'source'       =>  	$_POST['source'],
	            'username'     =>  	$username,
	            'password'     =>  	$password,
                'clientid'     =>  	$_POST['clientid'],
                'deviceToken'  =>  	$_POST['deviceToken'],
	            'port'         =>  	$this->port,
	            'provider'	   =>	$_POST['provider']
            );
            $userinfoM	=  $this->MODEL('userinfo');
            $result	    =  $userinfoM->bindacount($wdata, 'qq');
            
            if ($result['errcode'] == 9){
	                
                $data['user']     =  $result['user'];
                $data['errcode']  =  1;
                $data['msg']      =  '';
                
            }else if($result['errcode'] == 2){
            	$data['user']     =  $result['user'];
                $data['errcode']  =  2;
                $data['msg']      =  '';
            }else{
                
                $data['errcode']   =  -1;
                $data['msg']       =  $result['msg'];
            }
        }
        if(!isset($data['user'])){
            $data['user'] = array();
        }
	    $this->render_json($data['errcode'],$data['msg'], $data['user']);
	}
	/**
	 * 后台设置实名验证，微信登录等（非微信小程序获取手机号接口）需绑定手机号后再自动注册账号
	 */
	function fastregsave_action(){
	    
	    if (isset($_POST['version']) && $_POST['version']=='6.1'){
	        // 6.1版开始对注册、登录传的相关参数进行加密，这里需要解密
	        $username = $this->decryptRequest($_POST['username']);
	        $password = $this->decryptRequest($_POST['password']);
	    }else{
	        $username = $_POST['username'];
	        $password = $_POST['password'];
	    }
	    $username = $this->stringfilter($username);
	    $password = $this->stringfilter($password);
	    
	    if($_POST['provider'] == 'app'){
	        $source	=	3;
	    }elseif($_POST['provider'] == 'toutiao'){
            $source =   22;
        }
	    
	    if ($_POST['type'] == 'weixin'){
	        // wxlogin页面，绑定账号来源是微信的，只有APP
	        $wdata  =  array(
	            'openid'       =>  $_POST['openid'],
	            'unionid'      =>  !empty($_POST['unionid']) && $_POST['unionid'] != 'undefinded' ? $_POST['unionid'] : '',
	            'source'       =>  $source,
	            'moblie'       =>  $username,
	            'moblie_code'  =>  $password,
	            'clientid'     =>  $_POST['clientid'],
	            'deviceToken'  =>  $_POST['deviceToken'],
	            'port'         =>  4
	        );
	        $userinfoM	=  $this->MODEL('userinfo');
	        $result	    =  $userinfoM->fastReg($wdata, 'app', 'weixin');
	        
	        if ($result['errcode'] == 9){
	            
	            $data['user']     =  $result['user'];
	            $data['errcode']  =  1;
	            $data['msg']      =  '';
	            
	        }else{
	            
	            $data['errcode']   =  -1;
	            $data['msg']       =  $result['msg'];
	        }
	    }elseif($_POST['type'] == 'toutiao'){

            // 小程序抖音登录，第一次用code获取，后面用小程序里传过来的
            if (!empty($_POST['code'])){
                $getdata    =   $this->getTtOpenid($_POST['code'], $_POST['appid']);
            }else{
                $getdata['openid']  =  $_POST['openid'];
                if (!empty($_POST['unionid']) && $_POST['unionid'] != 'undefinded'){
                    $getdata['unionid']  =  $_POST['unionid'];
                }
            }
            $provider = $_POST['provider'];
            
            if (!empty($getdata['openid'])){
                
                $wdata  =  array(
                    'openid'       =>  $getdata['openid'],
                    'unionid'      =>  $getdata['unionid'],
                    'source'       =>  $source,
                    'moblie'       =>  $username,
                    'moblie_code'  =>  $password,
                    'port'         =>  $this->port
                );
                $userinfoM  =  $this->MODEL('userinfo');
                $result     =  $userinfoM->fastReg($wdata, $provider, 'toutiao');
                
                if ($result['errcode'] == 9){
                    
                    $data['user']     =  $result['user'];
                    $data['errcode']  =  1;
                    $data['msg']      =  '';
                    
                }else{
                    
                    $data['errcode']   =  -1;
                    $data['msg']       =  $result['msg'];
                }
                
            }else{
                
                $data['error']  =  3;
                $data['user']   =  array();
                if (isset($getdata['errcode'])){
                    //$this->MODEL('errlog')->addErrorLog('', 10, '抖音小程序登录绑定手机号获取openid错误。code:'.$_POST['code'].'。'.$getdata['errcode'].'，'.$getdata['errmsg']);
                    $data['errcode']    =   $getdata['errcode'];
                    if($getdata['errcode'] == 40125){
                        $data['msg']  =  '抖音小程序配置错误，请联系网站管理员';
                    }else{
                        $data['msg']  =  $getdata['errcode'] ? '错误号:'.$getdata['errcode'] : '出错了，请重试';
                    };
                }else{
                    $data['msg']        =   '参数错误';
                }
            }
        }elseif ($_POST['type'] == 'qq'){
            
	        $wdata  =  array(
	            'openid'       =>  $_POST['openid'],
	            'unionid'      =>  $_POST['unionid'],
	            'source'       =>  $source,
	            'moblie'       =>  $username,
	            'moblie_code'  =>  $password,
	            'clientid'     =>  $_POST['clientid'],
	            'deviceToken'  =>  $_POST['deviceToken'],
	            'port'         =>  $this->port
	        );
	        $userinfoM	=  $this->MODEL('userinfo');
	        $result	    =  $userinfoM->fastReg($wdata, 'qq', 'qq');
	        
	        if ($result['errcode'] == 9){
	            
	            $data['user']     =  $result['user'];
	            $data['errcode']  =  1;
	            $data['msg']      =  '';
	            
	        }else{
	            
	            $data['errcode']   =  -1;
	            $data['msg']       =  $result['msg'];
	        }
        }
        $this->render_json($data['errcode'],$data['msg'], $data['user']);
	}
	/**
	 * app端获取微信unionid。app首次微信登录无法获取unionid,需重新获取，防止异常。
	 */
	function getAppWxSns_action()
	{
	    $unionid = '';
	    if (!empty($_POST['code']) && !empty($_POST['openid'])){
	        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$_POST['code'].'&openid='.$_POST['openid'].'&lang=zh_CN';
	        
	        $rep = CurlGet($url);
	        $res = json_decode($rep,true);
	        if (isset($res['unionid'])){
	            $unionid = $res['unionid'];
	        }
	    }
	    $this->render_json(0,'ok', array('unionid'=>$unionid));
	}
    /**
     * app端 根据code获取微信openid unionid。
     */
    function getAppWxOpenid_action()
    {
        $data = array();
        if (!empty($_POST['code'])){
            
            $app_id = $this->config['sy_app_wxappid'];
            $app_secret = $this->config['sy_app_wxsecret'];
            $code = $_POST['code'];

            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$app_id."&secret=".$app_secret."&code=".$code."&grant_type=authorization_code";
            
            $rep = CurlGet($url);
            $res = json_decode($rep,true);

            if (!empty($res['openid'])){
                $data['errcode']   =  1;
                $data['unionid'] = !empty($res['unionid'])?$res['unionid']:'';
                $data['openid'] = $res['openid'];
            }else{
                $data['errcode']   =  -1;
                $data['msg']       =  $res['errcode'] ? '错误号:'.$res['errcode'] : '出错了，请重试';
            }
        }else{
            $data['errcode']   =  -1;
            $data['msg']       =  '出错了，请重试';
        }
        $this->render_json($data['errcode'],$data['msg'],$data);
    }
}
?>