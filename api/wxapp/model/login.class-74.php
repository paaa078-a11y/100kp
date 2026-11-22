<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class login_controller extends wxapp_controller{    
    function ulogin_action(){
        $username=$this->stringfilter($_POST['username']);
        $password=$this->stringfilter($_POST['password']);
        $Member=$this->MODEL("userinfo");
        $user = $Member->GetMemberOne(array("username"=>$username),array("field"=>"`uid`,`username`,`salt`,`password`,`usertype`,`status`,`login_date`,`wxopenid`,`unionid`"));
        if (!$user){
            $user = $Member->GetMemberOne(array("moblie"=>$username),array("field"=>"`uid`,`username`,`salt`,`password`,`usertype`,`status`,`login_date`,`wxopenid`,`unionid`"));
        }
        if($user && is_array($user)){
            $pass = md5(md5($_POST['password']).$user['salt']);
            if($user['password']==$pass){
                //微信绑定账号
                if($_POST['code']){
                    $getdata=$this->getOpenid($_POST['code']);
                    if ($getdata==1){
                        $data['errmsg']='参数错误';
                        echo json_encode($data);die;
                    }
                    $openid=$getdata['openid'];
                    $unionid=$getdata['unionid'];
                    $where = "`wxopenid`='".$getdata['openid']."'";
                    if ($getdata['unionid']){
                        $where .= " OR (`unionid`='".$getdata['unionid']."' AND `unionid`<>'')";
                    }
                    $userinfo = $this->obj->DB_select_once("member",$where,'`uid`');
                    if($userinfo && $userinfo['uid']!=$user['uid']){
                        $data['error']=6;
                        $data['wxuid']= $userinfo['uid'];
                    }else{
                        $data['error']=1;
                    }
                    $data['uid']=$user['uid'];
                    echo json_encode($data);die;
                }
                if($user['status']=="2"){
                    $data['error']=3; 
                    $data['errmsg']='您的账号已被锁定';
                    echo json_encode($data);die;
                }
                if($user['usertype']=='1'){
                    $Resume=$this->MODEL("resume");
                    $info=$Resume->SelectResume(array("uid"=>$user['uid']),"`email_status`");
                }
                if($user['usertype']=='2'){
                    $Company=$this->MODEL("company");
                    $info=$Company->GetCompanyInfo(array("uid"=>$user['uid']),array("field"=>'`email_status`'));
                    $this->autoupjob($user['uid'],$user['usertype']);
                }
                if($user['usertype']=="2" && $this->config['com_status']!="1" && $user['status']!="1"){
                    $data['error']=4;
                    $data['errmsg']='您还没有通过审核';
                    echo json_encode($data);die;
                }
                if($this->config['user_status']=="1" && $user['usertype']=="1"&&$info['email_status']!="1"){
                    $data['error']=5;
                    $data['errmsg']='您的账户还未激活，请先激活';
                    echo json_encode($data);die;
                }
                $ip = fun_ip_get();
                $time = time();
                $Member->UpdateMember(array("login_ip"=>$ip,"login_date"=>$time,"`login_hits`=`login_hits`+1"),array("uid"=>$user['uid']));
                $logtime=date("Ymd",$user['login_date']);
                $nowtime=date("Ymd",$time);
                if($logtime!=$nowtime){
                    $this->MODEL('integral')->get_integral_action($user['uid'],"integral_login","会员登录");
                }
                $data['uid']=$user['uid'];
                $data['usertype']=$user['usertype'];
                $data['username']=$user['username'];
                $data['error']=1;
            }else{
                $data['error']=2;
                $data['errmsg']='密码错误';
            }
        }else{
            $data['error']=2;
            $data['errmsg']='用户不存在';
        }
        echo json_encode($data);die;
    }
    function wxcheck_action(){
        $getdata=$this->getOpenid($_POST['code']);
        $data=array();
        //$data=$this->getSession3rd($_POST, $getdata['session_key']);
        if($getdata['openid']){
            $data['openid']=$getdata['openid'];
            if ($getdata['unionid']){
                $userinfo = $this->obj->DB_select_once("member","`unionid`='".$getdata['unionid']."' and `usertype`>0","uid,username,salt,login_date,usertype,status,email_status");
            }else{
                $userinfo = $this->obj->DB_select_once("member","`wxopenid`='".$getdata['openid']."' and `usertype`>0","uid,username,salt,login_date,usertype,status,email_status");
            }
            if($userinfo['uid']){
                if($userinfo['status']=="2"){
                    $data['error']=3;
                    $data['errmsg']='您的账号已被锁定';
                    echo json_encode($data);die;
                }
                if($userinfo['usertype']=="2" && $this->config['com_status']!="1" && $userinfo['status']!="1"){
                    $data['error']=4;
                    $data['errmsg']='您还没有通过审核';
                    echo json_encode($data);die;
                }
                if($this->config['user_status']=="1" && $userinfo['usertype']=="1"&&$userinfo['email_status']!="1"){
                    $data['error']=5;
                    $data['errmsg']='您的账户还未激活，请先激活';
                    echo json_encode($data);die;
                }
                $time = time();
                $this->obj->update_once('member',array('login_date'=>$time),array('uid'=>$userinfo['uid']));
                $logtime=date("Ymd",$userinfo['login_date']);
                $nowtime=date("Ymd",$time);
                if($logtime!=$nowtime){
                    $this->MODEL('integral')->get_integral_action($userinfo['uid'],"integral_login","会员登录");
                }
                $data['uid']=$userinfo['uid'];
                $data['usertype']=$userinfo['usertype'];
            }else{
                $data['error']=1;
            }
        }else{
            if ($getdata['errcode']){
                $data['errcode'] = $getdata['errcode'];
                $data['errmsg']='出错了，请重试';
            }else{
                $data['errmsg']='参数错误';
            }
        }
        echo json_encode($data);die;
    }
    function wxbind_action(){
        $getdata=$this->getOpenid($_POST['code']);
        if ($getdata==1){
            $data['errmsg']='参数错误';
            echo json_encode($data);die;
        }
        //$data=$this->getSession3rd($_POST, $getdata['session_key']);
        $openid=$getdata['openid'];
        $unionid=$getdata['unionid'];
        $Member=$this->MODEL("userinfo");
        $user = $Member->GetMemberOne(array("uid"=>$_POST['uid']),array("field"=>"uid,usertype,wxopenid,unionid"));
        if ($unionid && $unionid!=$user['unionid']){
            $this->obj->DB_update_all("member","`wxopenid`='',`unionid`=''","`wxopenid`='".$openid."' OR (`unionid`='".$unionid."' AND `unionid`<>'')");
            $nid=$this->obj->DB_update_all("member","`wxopenid`='".$openid."',`unionid`='".$unionid."'","`uid`='".$user['uid']."'");
        }else {
            if ($openid!=$user['wxopenid']) {
                $this->obj->DB_update_all("member","`wxopenid`=''","`wxopenid`='".$openid."'");
                $nid=$this->obj->DB_update_all("member","`wxopenid`='".$openid."'","`uid`='".$user['uid']."'");
            }
        }
        if($nid){
            $time = time();
            $this->obj->update_once('member',array('login_date'=>$time),array('uid'=>$userinfo['uid']));
            $logtime=date("Ymd",$userinfo['login_date']);
            $nowtime=date("Ymd",$time);
            if($logtime!=$nowtime){
                $this->MODEL('integral')->get_integral_action($userinfo['uid'],"integral_login","会员登录");
            }
            $data['error']=1;
            $data['uid']=$user['uid'];
            $data['usertype']=$user['usertype'];
        }else{
            $data['error']=2;
        }
        echo json_encode($data);die;
    }
    function checkuser($username,$name){
        $user = $this->obj->DB_select_once("member","`username`='".$username."'","`uid`");
        if($user['uid']){
            $name.="_".rand(1000,9999);
            return $this->checkuser($name,$username);
        }else{
            return $username;
        }
    }
    /**
     * 登录验证。生成session3rd用来做用户登录状态验证
     */
    function getSession3rd($post,$sessionKey){
        if ($post['rawData'] && $post['signature'] && $post['encryptedData'] && $post['iv']){
            $rawData = $post['rawData'];
            $signature = $post['signature'];
            $signature2 = sha1(stripslashes($rawData).$sessionKey);
            if ($signature2 != $signature){
                $data['errmsg'] = '签名不匹配';
            }
            include APP_PATH."api/wxapp/PHP/wxBizDataCrypt.php";
            include DATA_PATH."api/wxpay/wxpay_data.php";
            $pc = new WXBizDataCrypt($wxpaydata['sy_xcxappid'], $sessionKey);
            $encryptedData = $post['encryptedData'];
            $iv = $post['iv'];
            $errCode = $pc->decryptData($encryptedData, $iv, $res );
            
            if ($errCode !== 0) {
                $data['errmsg'] = '解密数据失败';
            }
            $session3rd = null;
            $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
            $max = strlen($strPol)-1;
            for($i=0;$i<16;$i++){
                $session3rd .=$strPol[rand(0,$max)];
            }
            $data['session3rd']=$session3rd;
            return $data;
        }
    }
    /**
     * 企业登录刷新职位
     */
    function autoupjob($uid,$usertype){
        if($usertype=='2'){
            $JobM=$this->Model("job");
            $JobM->UpdateComjob(array("lastupdate"=>time()),array("`uid`='".$uid."' AND `autotype`='2' AND `autotime`>'".time()."'"));
        }
    }
}
?>