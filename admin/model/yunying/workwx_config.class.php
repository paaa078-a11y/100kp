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

class workwx_config_controller extends adminCommon
{
    /*
     * 获取企业微信配置信息
     */
    function getinfo_action()
    {
        $this->render_json(0, '', array(
            'wx_qy_corpid' => $this->config['wx_qy_corpid'],
            'sy_workwx_secret' => $this->config['sy_workwx_secret'],
            'sy_contact_secret' => $this->config['sy_contact_secret'],
            'wx_contact_url' => $this->config['wx_contact_url'],
            'wx_contact_token' => $this->config['wx_contact_token'],
            'wx_contact_EncodingAESKey' => $this->config['wx_contact_EncodingAESKey'],
            'wx_photo_agentId' => $this->config['wx_photo_agentId'],
            'wx_photo_secret' => $this->config['wx_photo_secret'],
            'wx_qy_agentid' => $this->config['wx_qy_agentid'],
            'wx_qy_secret' => $this->config['wx_qy_secret'],
        ));
    }
    /*
     * 企业微信配置保存
     */
    function save_action()
    {
        if ($_POST) {
            $_POST = $this->post_trim($_POST);
            $configM = $this->MODEL('config');
            $configM->setConfig($_POST);
            $this->web_config();
            $this->admin_json(0, '企业微信配置成功');
        }
    }
}
?>