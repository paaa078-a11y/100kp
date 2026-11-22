<?php

class privacyset_controller extends adminCommon{
    /**
     * 工具 移动端 app配置
     */
    function index_action(){
        
        $ratingM    =   $this->MODEL('rating');
        
        $qyRatings  =   $ratingM->getList(array('category' => 1, 'orderby' => array('sort,desc')));

        $data['qyRatings'] = $qyRatings;

        $data['config'] = array(
            'sy_privacy_appid' => $this->config['sy_privacy_appid'],
            'sy_privacy_token' => $this->config['sy_privacy_token'],
            'sy_privacy_time' => $this->config['sy_privacy_time'],
            'sy_privacy_callrec' => $this->config['sy_privacy_callrec'],
            'sy_privacy_open' => $this->config['sy_privacy_open'],
            'sy_privacy_type' => $this->config['sy_privacy_type'],
            'sy_comprivacy_open' => $this->config['sy_comprivacy_open'],
            'sy_privacy_rating' => !empty($this->config['sy_privacy_rating'])?explode(',', $this->config['sy_privacy_rating']):array(),
        );
        
        $this->render_json(0,'ok', $data);
    }

    function save_action(){
        
        $_POST =  $this->post_trim($_POST);
        
        $configM  =  $this->MODEL('config');
        
        $data['sy_privacy_open']        =   $_POST['sy_privacy_open']==1?1:2;
        $data['sy_comprivacy_open']     =   $_POST['sy_comprivacy_open']==1?1:2;
        $data['sy_privacy_appid']       =   $_POST['sy_privacy_appid'];
        $data['sy_privacy_token']       =   $_POST['sy_privacy_token'];
        $data['sy_privacy_time']        =   $_POST['sy_privacy_time'];
        $data['sy_privacy_callrec']     =   $_POST['sy_privacy_callrec'];
        $data['sy_privacy_type']        =   $_POST['sy_privacy_type'];
        $data['sy_privacy_rating']      =   @implode(',', $_POST['sy_privacy_rating']);
       
        $configM -> setConfig($data);
        
        $this->web_config();
        
        $this->render_json(0,'隐私号设置成功！');
    }

}
?>