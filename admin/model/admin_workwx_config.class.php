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

class admin_workwx_config_controller extends adminCommon
{

    function index_action()
    {

        if ($_POST) {

            $_POST      =   $this->post_trim($_POST);

            $configM    =   $this->MODEL('config');

            $configM->setConfig($_POST);

            $this->web_config();

            $this->ACT_layer_msg('企业微信配置成功', 9, $_SERVER['HTTP_REFERER'], 2, 1);
        }

        $this->yuntpl(array('admin/work/config'));
    }

}

?>