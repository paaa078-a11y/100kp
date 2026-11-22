<?php

class wxtz_controller extends adminCommon{
    
    function index_action(){
        
        $data = array(
            'wx_xxtz'     => $this->config['wx_xxtz'],
            'wx_xxtz_tz'  => $this->config['wx_xxtz_tz'],
            'sy_msg_tz'   => $this->config['sy_msg_tz'],

            'wx_mbsqzw'   => $this->config['wx_mbsqzw'],
            'wx_mbzwsh'   => $this->config['wx_mbzwsh'],
            'wx_mbyqms'   => $this->config['wx_mbyqms'],
            'wx_mbjzbm'   => $this->config['wx_mbjzbm'],
            'wx_mbjzsh'   => $this->config['wx_mbjzsh'],
            'wx_mbjlsh'   => $this->config['wx_mbjlsh'],
            'wx_mbcztx'   => $this->config['wx_mbcztx'],
            'wx_mbreward'   => $this->config['wx_mbreward'],
            'wx_subscribetpl'   => $this->config['wx_subscribetpl'],
            'wx_mbchat'   => $this->config['wx_mbchat'],
            'wx_mbchat_ex'   => $this->config['wx_mbchat_ex'],
            'wx_userchangetpl'   => $this->config['wx_userchangetpl'],
            'wx_mbxjhlive'   => $this->config['wx_mbxjhlive'],
            'wx_mbspmssh'   => $this->config['wx_mbspmssh'],
            'wx_mbspview'   => $this->config['wx_mbspview'],

            'sy_chat_open'  =>$this->config['sy_chat_open'],
            'sy_chat_name'  =>$this->config['sy_chat_name'],

            'wx_mbsqzwlm'   => $this->config['wx_mbsqzwlm'],
            'wx_mbyqmslm'   => $this->config['wx_mbyqmslm'],
            'wx_mbcztxlm'   => $this->config['wx_mbcztxlm'],
            'wx_mbjzbmlm'   => $this->config['wx_mbjzbmlm'],
            'wx_mbzwshlm'   => $this->config['wx_mbzwshlm'],
            'wx_mbzwshsblm' => $this->config['wx_mbzwshsblm'],
            'wx_mbchatlm'   => $this->config['wx_mbchatlm'],
            'wx_mbsubscribelm'   => $this->config['wx_mbsubscribelm'],
        );
        
        $this->render_json(0,'ok', $data);
    }

    function save_action(){
        
        $_POST =  $this->post_trim($_POST);
        
        $configM  =  $this->MODEL('config');
        
        $configM -> setConfig($_POST);
        
        $this->web_config();
        
        $this->render_json(0,'微信配置更新成功！');
    }

    function wxmsglist_action(){
        
        $weixinM    =   $this->MODEL('weixin');

        if(trim($_POST['keyword'])){
            
            $keyword=   trim($_POST['keyword']);
            $type   =   intval($_POST['type']);
            $uids   =   array();

            if (in_array($type, array(1, 2, 3, 4))) {

                $UserinfoM  =   $this->MODEL('userinfo');

                $marr       =   array(
                    1   =>  array('name' => array('like', $keyword)),
                    2   =>  array('name' => array('like', $keyword)),
                    3   =>  array('realname' => array('like', $keyword)),
                    4   =>  array('name' => array('like', $keyword))
                );

                $ulist      =   $UserinfoM->getUserInfoList($marr[$type], array('usertype' => $type, 'field' => 'uid'));

                foreach ($ulist as $uk => $uv) {
                    if ($uv['uid']) {
                        $uids[] =   $uv['uid'];
                    }
                }
                $where['utype'] =   $type;
                $where['uid']   =   array('in', pylode(',', $uids));
            } elseif ($type == 90) {

                $where['uid']   =   $keyword;
            }
            
        }else{

            $where['utype']     =   array('<', 99);
        }

        if ($_POST['date1'] && $_POST['date2']) {

            $where['ctime'][]   =   array('>=', $_POST['date1']);
            $where['ctime'][]   =   array('<=', $_POST['date2']+86399);
        }

        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->adminPageList('wx_msg', $where,$_POST['page'],array('limit'=>$_POST['limit']));

        if ($pages['total'] > 0) {

            if($_POST['order']){
            
                if($_POST['order']=="desc"){
                    
                    $where['orderby']   =   $_POST['t'].',desc';
                    
                }else{
                    
                    $where['orderby']   =   $_POST['t'].',asc';
                    
                }
                
            }else{
                
                $where['orderby']       =   'id,desc';
        
            }

            $where['limit']     =   $pages['limit'];
            $wxMsgList  =   $weixinM->getWxmsgList($where);
        }

        $data['list']           =   !empty($wxMsgList)?$wxMsgList:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }
    function delmsg_action(){

        $weixinM        =   $this->Model('weixin');

        if (is_array($_POST['del'])) {

            $delid      =   @implode(',', $_POST['del']);
            $layer_type =   1;
        } else {

            $delid      =   (int)$_POST['del'];
            $layer_type =   0;
        }

        $where['id']    =   array('in', $delid);
        $del            =   $weixinM->delWxmsg(array('where' => $where));

        if($del){
            $msg = '微信通知消息(ID:' . $delid . ')删除成功！';
            $error = 0;
        }else{
            $msg = '删除失败！';
            $error = 1;
        }

        $this->admin_json($error,$msg);

    }

    function msgrepeat_action()
    {

        if ($_POST['id']) {

            $wxM    =   $this->MODEL('weixin');
            $msg    =   $wxM->msgrepeat($_POST['id']);
            $error = 0;
        } else {
            $error = 1;
            $msg =  "请选择需要重发的通知！";
        }

        $this->render_json($error,$msg);
    }
}
?>