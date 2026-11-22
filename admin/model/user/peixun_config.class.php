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

class peixun_config_controller extends adminCommon
{
    /**
     * 会员 - 培训 - 培训设置
     * 培训设置
     * 2019-06-06 hjy
     */
    public function index_action()
    {
        $config = $this->config;
        $config = array(
            'px_status' => isset($config['px_status']) ? $config['px_status'] : 1,
            'px_cert_status' => isset($config['px_cert_status']) ? $config['px_cert_status'] : 0,
            'px_logo_status' => isset($config['px_logo_status']) ? $config['px_logo_status'] : 0,
            'px_show_status' => isset($config['px_show_status']) ? $config['px_show_status'] : 0,
            'px_banner_status' => isset($config['px_banner_status']) ? $config['px_banner_status'] : 0,
            'px_enforce_info' => isset($config['px_enforce_info']) ? $config['px_enforce_info'] : 0,
        );

        $this->render_json(0, 'ok', compact('config'));
    }

    /**
     * 会员 - 培训 - 培训设置
     * 保存培训设置
     * 2019-06-06 hjy
     */
    public function config_action()
    {
        unset($_POST["pytoken"]);

        $configM = $this->MODEL('config');
        $postData = $this->post_trim($_POST);
        $configM->setConfig($postData);

        $this->web_config();

        $this->admin_json(0, "培训设置成功");
    }

    /**
     * 会员 - 培训 - 培训设置
     * 头像设置
     * 2019-06-06 hjy
     */
    public function logo_action()
    {
        if (!empty($_POST['submit'])) {
            unset($_POST['submit']);

            $this->web_config();
            $this->ACT_layer_msg("会员头像配置设置成功！", 9, $_SERVER['HTTP_REFERER'], 2, 1);
        }
        $config = $this->config;
        $config = array(
            'sy_px_icon_n' => isset($config['sy_px_icon']) ? checkpic($config['sy_px_icon']) : '',
            'sy_pxsubject_icon_n' => isset($config['sy_pxsubject_icon']) ? checkpic($config['sy_pxsubject_icon']) : '',
            'sy_pxteacher_icon_n' => isset($config['sy_pxteacher_icon']) ? checkpic($config['sy_pxteacher_icon']) : '',
            'sy_px_banner_n' => isset($config['sy_px_banner']) ? checkpic($config['sy_px_banner']) : '',
        );

        $this->render_json(0, 'ok', compact('config'));
    }

    /**
     * 会员 - 培训 - 培训设置
     * 头像设置 保存头像设置
     * 2023年7月28日 zcq
     */
    public function logoSave_action()
    {
        $configM = $this->MODEL('config');
        $uploadM = $this->MODEL('upload');

        if (!empty($_FILES)) {
            foreach ($_FILES as $field => $file) {
                $upData['file'] = $file;
                $upData['dir'] = 'logo';

                $upRes = $uploadM->newUpload($upData);
                if ($upRes && !empty($upRes['msg'])) {
                    $this->render_json(1, $upRes['msg']);
                } else {
                    $configData[$field] = $upRes['picurl'];
                }
            }
        }

        !empty($configData) && $configM->setConfig($configData);

        $this->web_config();
        $this->admin_json(0, "培训头像设置成功");
    }

}

?>