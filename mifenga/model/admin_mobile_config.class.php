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
class admin_mobile_config_controller extends adminCommon{

    /**
     *
     */
    function index_action(){
        
        $this -> yuntpl(array('admin/admin_mobile_config'));
    }

    /**
     * 保存
     */
    function save_action(){
        
        if($_POST['mobile_config']){
            if ($_POST['sy_mobile']){
                $sy_mobile     =  1;
            }else{
                $sy_mobile     =  2;
            }
            $config  =  array(
                'sy_mobile'     =>  $sy_mobile,
                'sy_mobile_appkey'  =>  $_POST['sy_mobile_appkey'],
                'sy_mobile_appsecret'  =>  $_POST['sy_mobile_appsecret']
            );
            $configM  =  $this->MODEL('config');
            $configM -> setConfig($config);
            $this -> web_config();
            $this->ACT_layer_msg('手机号归属地配置设置成功',9,$_SERVER['HTTP_REFERER'],2,1);
        }
    }

    function get_restnum_action(){


        $returnArr['msgnum'] = $returnArr['businessnum'] = 0;

        if (!empty($this->config['sy_mobile_appkey']) && !empty($this->config['sy_mobile_appsecret'])) {

            $url = 'https://u.phpyun.com/feature';
            $url .= '?appKey=' . $this->config['sy_mobile_appkey'] . '&appSecret=' . $this->config['sy_mobile_appsecret'];

            if (extension_loaded('curl')) {

                $return = CurlGet($url);
            } else if (function_exists('file_get_contents')) {

                $return = file_get_contents($url);
            }

            if ($return) {
                $msgInfo = json_decode($return, true);
                if ($msgInfo['code'] == '200') {
                    $returnArr['msgnum'] = $msgInfo['num'];
                }
                unset($return);
            }
        }
        echo json_encode($returnArr);die;
    }
}
?>