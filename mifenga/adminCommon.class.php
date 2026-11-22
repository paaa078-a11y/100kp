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

/**
 * phpyun后台公共代码： admin（pc后台）/wapadmin（手机站后台）/siteadmin（分站后台）
 */
class adminCommon extends common{

    public $adminShell = null;
    // 除了m=index和m=admin_right，其他的控制器可以直接调用，不用重复查询
    public $adminPower = null;
    // admin/siteadmin后台公共部分
    function admin(){
        
        $this -> get_admin_user_shell(); // 权限
                                          
        // 凡是有POST参数过来的都验证token信息
        if ($this -> config['sy_iscsrf'] != '2') {
            
            if (! $_SESSION['pytoken']) {
                
                $_SESSION['pytoken']  =  substr(md5(uniqid() . $_SESSION['auid'] . $_SESSION['ausername'] . $_SESSION['ashell']), 8, 12);
            }
            if ($_POST) {
                if ($_POST['pytoken'] != $_SESSION['pytoken']) {
                    
                    $this -> ACT_layer_msg('来源地址非法！', 8, $this->config['sy_weburl']);
                }
                unset($_POST['pytoken']);
            }
            $this -> yunset('pytoken', $_SESSION['pytoken']);
        }
    }

    // wap站后台公共部分
    function wapadmin(){

        $UA  =  strtoupper($_SERVER['HTTP_USER_AGENT']);

        if (strpos($UA, 'WINDOWS NT') !== false) {
            // header('location:'.Url('index',array('c'=>'wap')));
        }
        if ($_GET['c'] != 'wxconnect') {// wap后台微信登陆，无需鉴权
            $this->get_wapadmin_user_shell();
        }
    }
    /*
     * 手机后台鉴权
     */
    function get_wapadmin_user_shell(){
//        $shellMsg = '暂无操作权限';
        // cache要判断C ajax不需要判断
        $ctl = $_GET['c'];
        $act = $_GET['a'];
        !$ctl && $ctl = 'index';
        !$act && $act = 'index';
        if ($_SESSION['wuid'] && $_SESSION['wshell']) {
            //验证登录信息
            $this->admin_get_user_shell($_SESSION['wuid'], $_SESSION['wshell']);
            if (!$this->adminShell) {
                $this->wapadminlogout();
                $this->WapAdminShellMsg($this->config['sy_weburl'] . '/wapadmin/', '登录超时，请刷新后重新登录！');
//                $this->ShellMsg('登录超时，请刷新后重新登录！');exit();
            }
            if ($ctl != 'admin_right' && $ctl != 'index' && $ctl != 'admin_webdt') {
                if ($act == 'contactCustomer') {
                    return true;
                }
                //优先验证4级权限池（各类子模块权限）
                $moduleNav = $this->get_shell_module($ctl,$act);
                if($moduleNav['error'] == '2'){//子权限验证未通过
                    $this->WapAdminShellMsg($this->config['sy_weburl'] . '/wapadmin/');
//                    $this->ACT_msg_wapadmin($this->config['sy_weburl'] . "/wapadmin/", $shellMsg, 8);
                    exit();
                }elseif($moduleNav['error'] == '1'){//子权限验证通过 直接放行 OR 继续父级权限的认证
                    //return true;
                }
                $url = 'index.php?m=' . $ctl;
                $navM = $this->MODEL('navigation');
                if ($ctl == 'admin_memberlog'){
                    $ctl = 'admin_member';
                    $url = 'index.php?m=' . $ctl;
                }
                // 我的客户 权限鉴定url与实际访问路由不一致，单独处理
                if ($ctl == 'crm_customer' && in_array($act, array('myCustomer', 'com', 'addFollow', 'addTask', 'upStatusType', 'remarkCom', 'contactCustomer'))) {
                    $url .= '&self=1';
                }
                $nav = $this->get_shell($url);
                if (!$nav) {
                    // $this->adminlogout();
                    $this->WapAdminShellMsg($this->config['sy_weburl'] . '/wapadmin/');
//                    $this->ACT_msg_wapadmin($this->config['sy_weburl'] . "/wapadmin/", $shellMsg, 8);
                    exit;
                }
                $info = $navM->getAdminNav(array('url'=>$url));
                if (!$info) {
                    $this->WapAdminShellMsg($this->config['sy_weburl'] . '/wapadmin/');
//                    $this->ACT_msg_wapadmin($this->config['sy_weburl'] . "/wapadmin/", $shellMsg, 8);
                }
            }
        } else if ($ctl != 'index' || $act != 'index') {
            $this->WapAdminShellMsg($this->config['sy_weburl'] . '/wapadmin/','请先登录', 8);
//            if (!empty($_POST)){
//                echo json_encode(array('error'=>8,'msg'=>'请先登录', 'url'=>$this->config['sy_weburl'] . '/wapadmin/'));
//            }else{
//                $this->ACT_msg_wapadmin($this->config['sy_weburl'] . "/wapadmin/", $shellMsg, 8);
//            }
        }
    }

    function WapAdminShellMsg($url = '', $msg = '您暂无操作权限！', $code = 8,  $tm = 5){
        if($_POST){
            echo json_encode(array('error'=>$code,'msg'=>$msg, 'url'=>$url));
        }else{
            $this->ACT_msg_wapadmin($url, $msg, $code, $tm);
        }
    }
    function get_admin_user_shell(){

		$shellMsg = '暂无操作权限';

        // cache要判断C ajax不需要判断
        if ($_SESSION['auid'] && $_SESSION['ashell']) {
            
			//验证登录信息
            $this -> admin_get_user_shell($_SESSION['auid'], $_SESSION['ashell']);
            
            if (!isset($this->adminShell)) {
                
                $this -> adminlogout();
                
                $this -> ShellMsg('登录超时，请刷新后重新登录！');exit();
            }
            
			

            if ($_GET['m'] == '' || $_GET['m'] == 'index' || $_GET['m'] == 'admin_nav') {
                // 首页按 admin_right 判断
                $_GET['m'] = 'admin_right';
            }
            if ($this->config['did'] && $_GET['a']) {
                
                $_GET['c'] = $_GET['a'];
            }
            $c   =  $_GET['c'];
            $m   =  $_GET['m'];
            
            if ($m != 'admin_right') {
				//优先验证4级权限池（各类子模块权限）
                $moduleNav = $this -> get_shell_module($m,$c);
			
				if($moduleNav['error'] == '2'){//子权限验证未通过
					
					$this -> ShellMsg($shellMsg);exit();

				}elseif($moduleNav['error'] == '1'){//子权限验证通过 直接放行 OR 继续父级权限的认证
					
					//return true;
					
				}
                $url      =  'index.php?m=' . $m;
				
                $c_array  =  array(
                    'cache',
                    'markcom',
                    'markuser',
                    'advertise',
                    'zhaopinhui',
                    'admin_user',
                    'wx',
                    'admin_siteadmin'
                );
                
                $wx_c_arr = array(
                    'Getpubtool',
                    'wxPubTempList',
                    'wxPubTemp',
                    'wxPubTempSave',
                    'wxPubTempDel',
                    'twTask',
                    'comtwTask',
                    'delTwTask',
                    'getTW',
                    'taskFinish',
					'getComTW'
                );
                
                $navM    =  $this -> MODEL('navigation');
                
                if ($c && $c != 'savagroup' && in_array($m, $c_array)) {
                    
                    if ($m == 'admin_user' && $c == 'pass') {
                        
                        $c  =  'myuser';
                    }elseif ($m == 'wx' && in_array($c,$wx_c_arr)) {
                        
                        $c  =  'pubtool';
                    }

                    $url  .=  '&c=' . $c;
                    
                    $info  =  $navM -> getAdminNav(array('url'=>$url));
                    
                    if (empty($info)) {
                        
                        $url  =  'index.php?m=' . $m;
                    }
                }

                if ($m == 'admin_memberlog'){
                    $m      =   'admin_member';
                    $url    =   'index.php?m=' . $m;
                }
                
                $nav  =  $this -> get_shell($url);
                if (! $nav) {
					
                    // $this->adminlogout();
                     $this -> ShellMsg($shellMsg);exit();
                }
                
                if (is_numeric($this->config['did'])) {
                    
                    if ($m == 'admin_user') {
                        
                        $where['url']   =  'index.php?m=admin_user&c=myuser';
                        $where['dids']  =  1;
                        
                    } else {
                        
                        $where['url']   =  $url;
                        $where['dids']  =  1;
                    }
                    $info  =  $navM -> getAdminNav($where);
                } else {
                    $info  =  $navM -> getAdminNav(array('url'=>$url));
                }
                if (! $info) {
                    $this -> ShellMsg($shellMsg);exit();
                }
            }
        } else {
            if ($_GET['m'] != '') {
                $this -> adminlogout();
                $this -> ShellMsg('登录超时，请刷新后重新登录！');exit();
            }
        }
    }

    // 用户退出
    function adminlogout(){
        unset($_SESSION['authcode']);
        unset($_SESSION['auid']);
        unset($_SESSION['ausername']);
        unset($_SESSION['ashell']);
        unset($_SESSION['md']);
        unset($_SESSION['tooken']);
        unset($_SESSION['xsstooken']);
       
    }
    // wapadmin用户退出
    function wapadminlogout(){
        unset($_SESSION['authcode']);
        unset($_SESSION['wuid']);
        unset($_SESSION['wusername']);
        unset($_SESSION['wshell']);
        unset($_SESSION['md']);
        unset($_SESSION['tooken']);
        unset($_SESSION['xsstooken']);
        
    }
	function ShellMsg($msg = '您暂无操作权限！'){
		
		//三种提醒方式 JS/AJAX：自定义错误状态 表单POST：ACT_MSG GET：普通提示
		header('HTTP/1.1 777 Not Authorization'); 

		if($_POST){
			$this->ACT_layer_msg( $msg ,8);
		}else{
			echo $msg;
		}
		
		exit();
	}
    /**
     *@desc  后台用户登录
     */
    function admin_get_user_login($username, $password, $url = 'index.php'){
        
        global $config;
        
        $username  =  str_replace(' ', '', $username);
        
        $adminM    =  $this -> MODEL('admin');
        
        if ($config['sy_web_site'] == '1') {
            
            $where['username']        =  $username;
            $where['PHPYUNBTWSTART']  =  '';
            $where['did']             =  $config['did'];
            $where['isdid']           =  array('=',1,'OR');
            $where['PHPYUNBTWEND']    =  '';
            $where['status']          =  1;
            
            $user  =  $adminM -> getAdminUser($where, array());
        } else {
            
            $user  =  $adminM -> getAdminUser(array('username' => $username,'status' => 1), array());
        }
        //判断是否有登录时间控制
        if($user['control_login']){
            $timeArr = explode(" - ",$user['control_login']);
            $currtime = time();
            $stime = strtotime(date('Y-m-d ',time()).$timeArr[0]) ;
            $etime = strtotime(date('Y-m-d ',time()).$timeArr[1]);
            if($stime>$currtime  || $etime<$currtime){
                $this -> ACT_layer_msg('不在规定登录时间内', 8, $url);
            }
        }
        $verify  =  $adminM -> verifyPass($password, $user['password']);
        
		$logM      =  $this -> MODEL('log');

        if ($verify) {
            
            $_SESSION['auid']       =  $user['uid'];
            $_SESSION['ausername']  =  $user['username'];
            $_SESSION['xsstooken']  =  sha1($config['sy_safekey']);
            $_SESSION['ashell']     =  md5($user['username'] . $user['password'] . $this -> md);
            
            
            
            $adminLog  =  $logM -> getAdminLog(array('uid' => $_SESSION['auid'],'content' => '登录成功','orderby' => 'ctime'));
            
            $time      =  time();
            
            if ($adminLog) {
                
                $this -> cookie -> setCookie('lasttime', $adminLog['ctime'], $time + 80000);
            } else {
                
                $this -> cookie -> setCookie('lasttime', $time, $time + 80000);
            }
            
            $this -> cookie -> setCookie('ashell', md5($user['username'] . $user['password'] . $this -> md), $time + 80000);
            
            $adminM -> upAdminUser(array('lasttime' => $time), array('uid' => $user['uid']));
           
            $logM -> addAdminLog('登录成功', '', '', '');
            
            $this -> ACT_layer_msg('登录成功！', 9, $url);
        } else {

			$logM -> addAdminLog("管理员：".$username.' 登录失败，登录密码：'.$password, '', '', '',array('auid'=>$user['uid'],'ausername'=>$username));
            
            $this -> ACT_layer_msg('用户名或密码不正确！', 8, $url);
        }
    }

    /**
     * 手机WAP后台用户登录
     */
    function wapadmin_get_user_login($username, $password, $url = 'index.php'){
        global $config;
        $username  =  str_replace(' ', '', $username);
        
        $adminM    =  $this -> MODEL('admin');
        
        if ($config['sy_web_site'] == '1') {
            
            $where['username']        =  $username;
            $where['PHPYUNBTWSTART']  =  '';
            $where['did']             =  $config['did'];
            $where['isdid']           =  array('=',1,'OR');
            $where['PHPYUNBTWEND']    =  '';
            $where['status']          =  1;
            
            $user  =  $adminM -> getAdminUser($where);
        } else {
            
            $user  =  $adminM -> getAdminUser(array('username' => $username,'status' => 1));
        }
        //判断是否有登录时间控制
        if($user['control_login']){
            $timeArr = explode(" - ",$user['control_login']);
            $currtime = time();
            $stime = strtotime(date('Y-m-d ',time()).$timeArr[0]) ;
            $etime = strtotime(date('Y-m-d ',time()).$timeArr[1]);
            if($stime>$currtime  || $etime<$currtime){
                $this -> layer_msg('不在规定登录时间内', 8, $url);
            }
        }
        $verify  =  $adminM -> verifyPass($password, $user['password']);
        
        if ($verify) {
            
            $_SESSION['wuid']       =  $user['uid'];
            $_SESSION['wusername']  =  $user['username'];
            $_SESSION['xsstooken']  =  sha1($config['sy_safekey']);
            $_SESSION['wshell']     =  md5($user['username'] . $user['password'] . $this -> md);
            
            $time  =  time();
            
            $this -> cookie -> setCookie('wshell', md5($user['username'] . $user['password'] . $this -> md), $time + 80000);
            
            $adminM -> upAdminUser(array('lasttime' => $time), array('uid' => $user['uid']));
            
            $this -> layer_msg('WAP后台登录成功', 9,0,'','',9);
        } else {
            $logM      =  $this -> MODEL('log');
            $logM -> addAdminLog("管理员：".$username.' 登录失败，登录密码：'.$password, '', '', '',array('auid'=>$user['uid'],'ausername'=>$username));
            
            $this -> layer_msg('用户名或密码不正确！', 8, $url);
        }
    }

    /**
     * 管理员用户登录权限验证
     */
    function admin_get_user_shell($uid, $shell){
        
        global $config;
        
        if (! preg_match("/^\d*$/", $uid)) {
            return false;
        }
        
        $adminM    =  $this -> MODEL('admin');
        
        if ($config['sy_web_site'] == '1') {
            
            $where['uid']             =  $uid;
            $where['PHPYUNBTWSTART']  =  '';
            $where['did']             =  $config['did'];
            $where['isdid']           =  array('=',1,'OR');
            $where['PHPYUNBTWEND']    =  '';
            
            $user  =  $adminM -> getAdminUser($where);
        } else {
            
            $user  =  $adminM -> getAdminUser(array('uid' => $uid));
        }
        
        $ps = !empty($user) ? $shell == md5($user['username'] . $user['password'] . $this->md) : FALSE;
        
        if ($ps){
            $this->adminShell = $user;
        }
    }

    /**
     * 检查访问地址
     */
    function check_token(){
        if ($this->config['sy_iscsrf'] != '2') {
            
            if ($_SESSION['pytoken'] != $_GET['pytoken'] || ! $_SESSION['pytoken']) {
                
                unset($_SESSION['pytoken']);
                
                $this -> ACT_layer_msg('来源地址非法！', 8, 'index.php');
                
                exit();
            }
        }
    }

    /**
     * 后台顶部导航条默认页面
     * @param $id     
     * @param $power
     */
    function GET_web_default($id, $power){
        
        if ($this->config['sy_web_site'] == '1' && $this->config['did']) {
            
            $where['dids'] = 1;
        }
        
        $navM  =  $this -> MODEL('navigation');
        
        $web   =  $navM -> getAdminNavList(array('keyid'=>array('in',pylode(',', $id)),'orderby'=>'sort,asc'));
        
        if (is_array($web['list'])) {
            
            foreach ($web['list'] as $v) {
                
                if (@in_array($v['id'], $power)) {
                    
                    $arr[]           =  $v['id'];
                    
                    $arr2[$v['id']]  =  $v['keyid'];
                }
            }
            
            $where['keyid']    =  array('in',pylode(',', $arr));
            $where['orderby']  =  'sort,asc';
            
            $webaa  =  $navM -> getAdminNavList($where);
            
            if (is_array($webaa)) {
                
                foreach ($webaa['list'] as $va) {
                     
                    if (@in_array($va['id'], $power)) {
                        
                        $value[$arr2[$va['keyid']]]  =  $va['url'];
                    }
                }
            }
        }
        
        return $value;
    }
	/**
     * 验证当前登录管理员，权限组操作模块权限认证
     * @param string $web  要检测的链接url
     * @param string $type
     * @return boolean
     */
    function get_shell_module($m,$c){
        
		$navM    =  $this -> MODEL('navigation');
        //首先检测当前链接是否在操作模块权限池中
		$nav     =  $navM -> getAdminNav(array('url'=>'index.php?m='.$m.'&c='.$c,'level'=>4));
			
		//不在权限池就无需验证
		if(!empty($nav)){
			// index.php里已经查了一遍权限，为了防止直接请求具体方法的，保留做容错处理
			if (empty($this->adminPower['power'])){
			    $adminM  =  $this->MODEL('admin');
			    $this->adminPower  =  $adminM -> getPower(array('id'=>$this->adminShell['m_id']));
			}
			
			if ($this->adminPower['power'] && in_array($nav['id'], $this->adminPower['power'])){

				$return['error'] = 1;
				$return['power'] = $this->adminPower;
			} else {
				$return['error'] = 2;
			}
		}else{

			$return['error'] = 3;
			
		}
        
        return $return;
    }
    /**
     * 验证当前登录管理员，管理员组导航菜单权限检测
     * @param string $web  要检测的链接url
     * @param string $type
     * @return boolean
     */
    function get_shell($web, $type = ''){
        // $web=str_replace('&','&amp;',$web);
        
        // get_shell_module里面，4级权限没查到的，不会查总权限，这里需要在查一遍
        if (empty($this->adminPower['power'])){
            $adminM  =  $this->MODEL('admin');
            $this->adminPower  =  $adminM -> getPower(array('id'=>$this->adminShell['m_id']));
        }
		
        //判断当前管理员类型有权限
        if ($this->adminPower['power'] && $this->adminPower['group_name']){
            
            $navM    =  $this -> MODEL('navigation');
            
            $nav     =  $navM -> getAdminNav(array('url'=>$web));
           
            $return  =  @in_array($nav['id'], $this->adminPower['power']) ? true : false;
        } else {
            $return  =  false;
        }
        return $return;
    }
    /**
     * 获取页面名称
     * @param string $id
     * @return string
     */
    function GET_web_check($id){
        
        $navM  =  $this -> MODEL('navigation');
        
        $nav   =  $navM -> getAdminNav(array('id'=>$id));
        
        if (is_array($nav)) {
            
            $value  =  $this -> GET_web_check($nav['keyid']);
            
            $value  .=  $nav['name'] . ' - ';
        }
        return $value;
    }
    /**
     * 获取微信/公众号/小程序绑定状态
     * @param string $id
     * @return string
     */
    function wxBindState($wxBind){
        
        if(!empty($wxBind['wxid'])){
            if(!empty($wxBind['unionid'])){
                $wxGzhmsg = '公众号已绑定，微信开放平台已绑定';
            }else{
                $wxGzhmsg = '公众号已绑定';
            }
        }else{
            $wxGzhmsg= '公众号未绑定';
        }
        $wxBindmsg = $wxGzhmsg;
		if(!empty($this->config['sy_xcxappid'])){
			if(!empty($wxBind['wxopenid'])){
				if($this->config['mini_wxopen']==1){
					$wxXcxmsg = '微信小程序已绑定，微信开放平台已绑定';
				}else{
					$wxXcxmsg = '微信小程序已绑定';
				}
			}else{
				$wxXcxmsg= '微信小程序未绑定';
			}
			$wxBindmsg .=  '</br>'.$wxXcxmsg;
		}
		// app是必定绑定微信开放平台的，不需要重复说明
		if($this->config['sy_app_open'] ==1){
			if(!empty($wxBind['app_wxid'])){
				$wxAppmsg = 'APP微信已绑定';
			}else{
				$wxAppmsg = 'APP微信未绑定';
			}
			$wxBindmsg .=  '</br>'.$wxAppmsg;
		}
        return $wxBindmsg;
    }
    /**
     * @desc    将接口返回数据以统一格式的JSON输出
     *
     * @param string $error 执行结果标识码
     * @param string $msg   执行结果描述信息
     * @param array $data   执行结果输出的数据
     * @param int $total
     */
    public function render_json($error = '', $msg = '', $data = array(), $total = 0)
    {
        $msg =  isset($msg) ? preg_replace('/\([^\)]+?\)/x', "", str_replace(array("（", "）"), array("(", ")"), $msg)) : '';
        $result =   array(
            'error' =>  $error,
            'msg'   =>  $msg,
            'data'  =>  isset($data) ? $data : array(),
            'total' =>  $total
        );
        header('content-type:application/json; charset=utf-8');
        echo json_encode($result);
        exit;
    }
}
?>