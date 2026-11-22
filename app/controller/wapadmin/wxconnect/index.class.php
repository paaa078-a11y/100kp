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

class index_controller extends adminCommon
{

    function index_action()
    {
        if ($this->config['wx_rz'] != "1") {

            if ((int)$_GET['login'] == "1") {

                $this->ACT_msg_wap($this->config['sy_weburl'] . '/wapadmin/', $msg = "对不起，微信绑定已关闭！", 2, 5);
            } else {

                $this->ACT_msg_wap($this->config['sy_weburl'] . '/wapadmin/', $msg = "对不起，微信登录已关闭！", 2, 5);
            }
        }
        $this->seo('wxlogin');

        $app_id     =   $this->config['wx_appid'];
        $app_secret =   $this->config['wx_appsecret'];
        $my_url     =   $this->config['sy_weburl'] . "/wapadmin/index.php?m=wxconnect&c=index";

        if (isset($_GET['wxoauth'])) {
            $my_url .=  '&wxoauth=1';
        }
        $code       =   $_GET['code'];

        session_start();

        if (empty($code)) {

            $_SESSION['wx']['state']    =   md5(uniqid(rand(), TRUE));

            $dialog_url =   "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $app_id . "&redirect_uri=" . urlencode($my_url) . "&response_type=code&scope=snsapi_userinfo&state=" . $_SESSION['wx']['state'] . "#wechat_redirect";

            header("location:" . $dialog_url);
            die;
        }

        if ($_GET['state'] == $_SESSION['wx']['state']) {

            $token_url  =   "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $app_id . "&secret=" . $app_secret . "&code=" . $code . "&grant_type=authorization_code";

            if (function_exists('curl_init')) {

                $ch =   curl_init();
                curl_setopt($ch, CURLOPT_URL, $token_url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                $response   =   curl_exec($ch);
                curl_close($ch);
                $user   =   json_decode($response, true);
                if (!empty($user['openid'])) {
                    $userinfoM      =   $this->MODEL("admin");
                    $where['wxid']  =   $user['openid'];
                    $userinfo       =   $userinfoM->getAdminUser($where);
                    if (!$userinfo || $userinfo['status'] == '2') {
                        $msg = !$userinfo ? '请先绑定微信': '离职账户不能再登陆';
                        // 离职账户不能再登陆
                        $this->ACT_msg_wapadmin($this->config['sy_weburl'] . "/wapadmin/", $msg, 8);
//                        header("location:" . $this->config['sy_weburl'] . "/wapadmin/");
                        exit();
                    }
                    $_SESSION['wuid']       =  $userinfo['uid'];
                    $_SESSION['wusername']  =  $userinfo['username'];
                    $_SESSION['xsstooken']  =  sha1($this->config['sy_safekey']);
                    $_SESSION['wshell']     =  md5($userinfo['username'] . $userinfo['password'] . $this -> md);
                    $time  =  time();
                    $this -> cookie -> setCookie('wshell', md5($userinfo['username'] . $userinfo['password'] . $this -> md), $time + 80000);
                    $userinfoM->upAdminUser(array('lasttime' => $time), array('uid' => $userinfo['uid']));
                    // 会员日志，记录手动登录
                    $LogM   =   $this->MODEL('log');
                    $LogM->addAdminLog('WAP后台微信登录成功', '', '', '');
                    $this->ACT_msg($this->config['sy_weburl'] . "/wapadmin/index.php?c=index&a=index", "微信登陆成功！", 8);
                }
            }
        } else {
            header("location:" . $this->config['sy_weburl'] . "/wapadmin/");
        }
    }
}
?>