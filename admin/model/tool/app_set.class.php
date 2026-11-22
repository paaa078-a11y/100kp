<?php

class app_set_controller extends adminCommon{
    /**
     * 工具 移动端 app配置
     */
    function index_action(){
        include(DATA_PATH.'api/wxapp/app.config.php');
        if (!empty($appconfig['sy_push_appid']) || !empty($appconfig['sy_push_appsecret']) || !empty($appconfig['sy_push_appkey']) || !empty($appconfig['sy_push_masterSecret'])){
            $this->config['sy_push_appid']         =  $appconfig['sy_push_appid'];
            $this->config['sy_push_appsecret']     =  $appconfig['sy_push_appsecret'];
            $this->config['sy_push_appkey']        =  $appconfig['sy_push_appkey'];
            $this->config['sy_push_masterSecret']  =  $appconfig['sy_push_masterSecret'];
        }
        $data['config'] = array(
            'sy_app_open' => $this->config['sy_app_open'] == 1,
            'sy_push_open' => $this->config['sy_push_open'] == 1,
            'sy_push_appid' => $this->config['sy_push_appid'] ? $this->config['sy_push_appid'] : '',
            'sy_push_appkey' => $this->config['sy_push_appkey'] ? $this->config['sy_push_appkey'] : '',
            'sy_push_appsecret' => $this->config['sy_push_appsecret'] ? $this->config['sy_push_appsecret'] : '',
            'sy_push_masterSecret' => $this->config['sy_push_masterSecret'] ? $this->config['sy_push_masterSecret'] : '',
            'sy_iospay' => $this->config['sy_iospay'] == 1,
            'sy_ioslocation' => $this->config['sy_ioslocation'] == 1,
            'sy_app_wxkf' => $this->config['sy_app_wxkf'] == 1,
            'sy_app_qqlogin' => $this->config['sy_app_qqlogin'] == 1,
            'sy_app_wxlogin' => $this->config['sy_app_wxlogin'] == 1,
            'sy_app_wxsecret'=>$this->config['sy_app_wxsecret'],
            'sy_app_wxappid'=>$this->config['sy_app_wxappid'],
        );
        $this->render_json(0,'ok', $data);
    }

    /**
     * 工具 移动端 app配置 保存
     */
	function save_action()
    {
        $_POST = $this->post_trim($_POST);
		$data['sy_app_open'] 		=	$_POST['sy_app_open']== 'true'?1:2;
		$data['sy_push_open'] 		=	$_POST['sy_push_open']== 'true' && PHP_VERSION >= '5.5'?1:2;
		$data['sy_iospay'] 			=	$_POST['sy_iospay']== 'true'?1:2;
		$data['sy_ioslocation'] 	=	$_POST['sy_ioslocation']== 'true'?1:2;
		$data['sy_app_qqlogin'] 	=	$_POST['sy_app_qqlogin']== 'true'?1:2;
		$data['sy_app_wxlogin'] 	=	$_POST['sy_app_wxlogin']== 'true'?1:2;
		$data['sy_app_wxkf'] 		=	$_POST['sy_app_wxkf']== 'true'?1:2;
        $data['sy_app_wxappid'] 	= 	$_POST['sy_app_wxappid'];
        $data['sy_app_wxsecret']	= 	$_POST['sy_app_wxsecret'];
        $configM = $this->MODEL('config');
        $configM->setConfig($data);
        $this->web_config();
        $appData = array(
            'sy_push_appid' => $_POST['sy_push_appid'],
            'sy_push_appsecret' => $_POST['sy_push_appsecret'],
            'sy_push_appkey' => $_POST['sy_push_appkey'],
            'sy_push_masterSecret' => $_POST['sy_push_masterSecret'],

        );
        $wxappM = $this->MODEL('wxapp');
        $wxappM->setConfig($appData);
        if ($_POST['sy_push_open'] && PHP_VERSION < '5.5'){
            $this->render_json(1, "PHP版本低于5.5，无法使用推送！");
        }else{
            $this->admin_json(0, "App设置成功！");
        }
    }

    /**
     * 工具 移动端 app配置 版本更新
     */
    function version_action()
    {
        include(DATA_PATH.'api/wxapp/app.config.php');
        if (!empty($appconfig['apptitle']) || !empty($appconfig['androidurl']) || !empty($appconfig['iosurl'])){
            $this->config['iosversion'] = $appconfig['iosversion'];
            $this->config['iosurl'] = $appconfig['iosurl'];
            $this->config['androidversion'] = $appconfig['androidversion'];
            $this->config['androidurl'] = $appconfig['androidurl'];
            $this->config['apptitle'] = $appconfig['apptitle'];
            $this->config['appcontent'] = $appconfig['appcontent'];
            $this->config['sy_app_upforce'] = $appconfig['sy_app_upforce'];
        }
        $data['config'] = array(
            'iosversion' => $this->config['iosversion'] ? $this->config['iosversion'] : '',
            'iosurl' => $this->config['iosurl'] ? $this->config['iosurl'] : '',
            'androidversion' => $this->config['androidversion'] ? $this->config['androidversion'] : '',
            'androidurl' => $this->config['androidurl'] ? $this->config['androidurl'] : '',
            'apptitle' => $this->config['apptitle'] ? $this->config['apptitle'] : '',
            'appcontent' => $this->config['appcontent'] ? $this->config['appcontent'] : '',
            'sy_app_upforce' => $this->config['sy_app_upforce'] == 1,
        );
        $this->render_json(0,'ok', $data);
    }
    /**
     * 工具 移动端 app配置 版本更新 保存
     */
    function saveversion_action()
    {
        $_POST = $this->post_trim($_POST);
        $appData = array(
            'iosversion' => $_POST['iosversion'],
            'iosurl' => $_POST['iosurl'],
            'androidversion' => $_POST['androidversion'],
            'androidurl' => $_POST['androidurl'],
            'apptitle' => $_POST['apptitle'],
            'appcontent' => $_POST['appcontent'],
            'sy_app_upforce' => isset($_POST['sy_app_upforce']) && $_POST['sy_app_upforce']=='true' ? 1 : 0
        );
        $wxappM = $this->MODEL('wxapp');
        $wxappM->setConfig($appData);
        $this->admin_json(0, "App版本更新设置成功！");
    }
    /**
     * 工具 移动端 app配置 首页提示
     */
    function xybox_action()
    {
        include(DATA_PATH.'api/wxapp/app.config.php');
        $data['config'] = array(
            'xyboxtitle' => $appconfig['xyboxtitle'] ? $appconfig['xyboxtitle'] : '',
            'xyboxcontent' => $appconfig['xyboxcontent'] ? $appconfig['xyboxcontent'] : ''
        );
        $this->render_json(0,'ok', $data);
    }
    /**
     * 工具 移动端 app配置 首页提示 保存
     */
    function savexybox_action()
    {
        $_POST = $this->post_trim($_POST);
        $appData = array(
            'xyboxtitle' => $_POST['xyboxtitle'],
            'xyboxcontent' => $_POST['xyboxcontent']
        );
        $wxappM = $this->MODEL('wxapp');
        $wxappM->setConfig($appData);
        $this->admin_json(0, "App首页提示设置成功！");
    }

    /**
     * 求职助手
     */
    function career_counselor_action()
    {
        /* @var $tplapp */
        include(DATA_PATH . 'api/wxapp/tplapp.cache.php');
        if ($_POST && $_POST['submit']) {
            $uploadM = $this->MODEL('upload');
            $data['career_counselor_name'] = trim($_POST['name']);
            $data['career_counselor_greet'] = trim($_POST['greet']);
            $data['career_counselor_share_title'] = trim($_POST['share_title']);
            $data['career_counselor_job_classid'] = trim($_POST['job_class']);
            $data['career_counselor_avatar'] = $tplapp['career_counselor_avatar'];
            $data['career_counselor_share_img'] = $tplapp['career_counselor_share_img'];
            $data['career_counselor_user_avatar'] = $tplapp['career_counselor_user_avatar'];
            if(!empty($_FILES)){
                if($_FILES['avatar_file']['tmp_name']){
                    $upArrAvatar = array(
                        'file' => $_FILES['avatar_file'],
                        'dir' => 'appdiy'
                    );
                }
                if(!empty($upArrAvatar )){
                    $pic = $uploadM->newUpload($upArrAvatar );
                }
                if (!empty($pic['msg'])) {
                    $this->render_json(1, $pic['msg']);
                }
                if (!empty($pic['picurl'])){
                    $data['career_counselor_avatar'] = $pic['picurl'];
                }
                if($_FILES['user_avatar_file']['tmp_name']){
                    $upArrUserAvatar = array(
                        'file' => $_FILES['user_avatar_file'],
                        'dir' => 'appdiy'
                    );
                }
                if(!empty($upArrUserAvatar )){
                    $userpic = $uploadM->newUpload($upArrUserAvatar );
                }
                if (!empty($userpic['msg'])) {
                    $this->render_json(1, $userpic['msg']);
                }
                if (!empty($userpic['picurl'])){
                    $data['career_counselor_user_avatar'] = $userpic['picurl'];
                }
                if($_FILES['share_file']['tmp_name']){
                    $upArrShare = array(
                        'file' => $_FILES['share_file'],
                        'dir' => 'appdiy'
                    );
                }
                if(!empty($upArrShare )){
                    $sharepic = $uploadM->newUpload($upArrShare );
                }
                if (!empty($sharepic['msg'])) {
                    $this->render_json(1, $sharepic['msg']);
                }
                if (!empty($sharepic['picurl'])){
                    $data['career_counselor_share_img'] = $sharepic['picurl'];
                }
            }
            $this->savetplapp($data);
            $this->admin_json(0, '求职助手保存成功');
            exit();
        }
        $cacheM = $this->MODEL('cache');
        $cacheData = $cacheM->GetCache('job');
        $jonly = 0;
        if (empty($cacheData['job_type'])) {
            $jonly = 1;
        }
        $rt['jionly'] = $jonly;
        $rt['tplapp'] = $tplapp;
        $rt['career_counselor_avatar'] = !empty($tplapp['career_counselor_avatar']) ? checkpic($tplapp['career_counselor_avatar']) : getAdminPath('images/qzzs_avatar.png');
        $rt['career_counselor_share_img'] = checkpic($tplapp['career_counselor_share_img']);
        $rt['career_counselor_user_avatar'] = !empty($tplapp['career_counselor_user_avatar']) ? checkpic($tplapp['career_counselor_user_avatar']) : getAdminPath('images/qzzs_user_avatar.png');
        $selectedJobType = array();
        if (!empty($tplapp['career_counselor_job_classid'])) {
            $selectedjobclass = explode(',', $tplapp['career_counselor_job_classid']);
            foreach ($selectedjobclass as $v) {
                $selectedJobType[$v] = $cacheData['job_name'][$v];
            }
        }
        $rt['selectedJobType'] = $selectedJobType;
        $this->render_json(0, '', $rt);
    }

    /**
     * 保存到tplapp里
     * @param $data
     */
    function savetplapp($data)
    {
        $wxAppM = $this->MODEL('wxapp');
        $wxAppM->saveTplApp($data);
        $wxAppM->tplappCache();
    }
}
?>