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
class xjhlive_set_controller extends adminCommon
{
    function index_action(){
        $xjhM = $this->MODEL('xjhlive');
        $rt['sy_xjh_subtime'] = $this->config['sy_xjh_subtime'];
        $rt['sy_xjhlive_ext'] = $this->config['sy_xjhlive_ext'];
        $rt['sy_xjh_notice'] = $this->config['sy_xjh_notice'];
        $rt['sy_xjhlive_appkey'] = $this->config['sy_xjhlive_appkey'];
        $rt['sy_xjhlive_appsecret'] = $this->config['sy_xjhlive_appsecret'];
        $rt['num'] = $xjhM->xjhCanAdd();//宣讲会剩余数量
        $this->render_json(0, '', $rt);
    }
    function save_action(){
        if($_POST['config']){
            $post = array(
                'sy_xjh_subtime' => $_POST['sy_xjh_subtime'],
                'sy_xjh_notice' => $_POST['sy_xjh_notice'],
                'sy_xjhlive_ext' => $_POST['sy_xjhlive_ext'],
                'sy_xjhlive_appkey' => $_POST['sy_xjhlive_appkey'],
                'sy_xjhlive_appsecret' => $_POST['sy_xjhlive_appsecret']
            );
            $configM = $this->MODEL('config');
            $configM->setConfig($post);
            $this->web_config();
            $this->admin_json(0, '宣讲会设置配置修改成功！');
        }
    }
}