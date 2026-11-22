<?php

class chatset_controller extends adminCommon{
    
    function index_action(){
        
        $chatM   =  $this->MODEL('chat');
        $return  =  $chatM->chatRequest();

        $data['config'] = array(
            'sy_chat_open'      =>  $this->config['sy_chat_open'],
            'sy_chat_logo'      =>  checkpic($this->config['sy_chat_logo']),
            'sy_chat_limit'     =>  $this->config['sy_chat_limit'],
            'sy_chat_rates'     =>  $this->config['sy_chat_rates'],
            'sy_chat_exmerep'   =>  $this->config['sy_chat_exmerep'],
            'sy_chat_replyday'  =>  $this->config['sy_chat_replyday'],
            'sy_chat_name'      =>  $this->config['sy_chat_name'],
            'sy_chat_day'       =>  $this->config['sy_chat_day'],
            'sy_chat_recalltime'=>  $this->config['sy_chat_recalltime'],
            'sy_chat_appkey'    =>  $this->config['sy_chat_appkey'],
            'sy_chat_appsecret' =>  $this->config['sy_chat_appsecret'],
            'sy_chat_exphone'   =>  $this->config['sy_chat_exphone']==1?'1':'2',
            'sy_chat_warn'      =>  $this->config['sy_chat_warn'],
            'sy_chat_concheck'  =>  $this->config['sy_chat_concheck'],
            'servstatus'        =>  !empty($return['error'])?'不可用':'可用',
            'sy_chat_zdrat'     =>  !empty($this->config['sy_chat_zdrat'])?explode(',',$this->config['sy_chat_zdrat']):array(),
        );
        
        $rating = $this->MODEL('rating');
        $ratings = $rating->getList(array('category' => 1, 'orderby' => array('sort,desc')));
        $data['ratings'] = $ratings;
        $this->render_json(0,'ok', $data);
    }

    function save_action(){
        
        $_POST =  $this->post_trim($_POST);
        
        $configM  =  $this->MODEL('config');
        
        if ($_POST['sy_chat_open']=='1'){
            
            $data['sy_chat_open'] = 1;
        }else{
            
            $data['sy_chat_open'] = 2;
        }
        if ($_POST['sy_chat_exphone']=='2'){
            
            $data['sy_chat_exphone'] = 2;
        }else{
            
            $data['sy_chat_exphone'] = 1;
        }
        if ($_POST['sy_chat_concheck']=='2'){
            
            $data['sy_chat_concheck'] = 2;
        }else{
            
            $data['sy_chat_concheck'] = 1;
        }

        if ($_POST['sy_chat_exmerep']=='2'){
            $data['sy_chat_exmerep'] = 2;
        }else{
            $data['sy_chat_exmerep'] = 1;
        }
        if($_POST['sy_chat_zdrat']){
            $data['sy_chat_zdrat'] = implode(',',$_POST['sy_chat_zdrat']);
        }else{
            $data['sy_chat_zdrat'] = '';
        }

        $data['sy_chat_replyday']   =  $_POST['sy_chat_replyday'];
        $data['sy_chat_limit']      =  $_POST['sy_chat_limit'];
        $data['sy_chat_rates']      =  $_POST['sy_chat_rates'];
        $data['sy_chat_name']       =  $_POST['sy_chat_name'];
        $data['sy_chat_day']        =  $_POST['sy_chat_day'];
        $data['sy_chat_recalltime'] =  $_POST['sy_chat_recalltime'];
        $data['sy_chat_appkey']     =  $_POST['sy_chat_appkey'];
        $data['sy_chat_appsecret']  =  $_POST['sy_chat_appsecret'];
        $data['sy_chat_warn']       =  $_POST['sy_chat_warn'];
        $data['sy_chat_weburl']     =  'wss://yunliao.phpyun.com/wss';
        
        $configM -> setConfig($data);
        
        $this->web_config();
        
        $this->render_json(0,'聊天设置成功！');
    }

}
?>