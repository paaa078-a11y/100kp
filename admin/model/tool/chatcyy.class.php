<?php

class chatcyy_controller extends adminCommon{
    
    function index_action(){
        
        $noUsername = true;
        $where = array();
        
        $chatM = $this->MODEL('chat');

        if(trim($_POST['keyword'])){
            
            $keyword    =   trim($_POST['keyword']);
            
            $type       =   intval($_POST['type']);

            if ($type==1){
                
                $userInfoM  =  $this->MODEL('userinfo');
                $member  =  $userInfoM->getList(array('username'=>array('like',$keyword)),array('field'=>'`uid`'));

                if (!empty($member)){
                    
                    $muids  =  array();
                    foreach ($member as $v){
                        
                        $muids[] = $v['uid'];
                    }
                    if (!empty($muids)){
                        
                        $where['uid']  =  array('in',pylode(',', $muids));
                        
                    }
                }else{
                    $noUsername = false;
                }
            }elseif($type==2){
                
                $where['content']   =   array('like',$keyword);
            }
        }

        if ($noUsername){
            $pageM          =   $this->MODEL('page');
            $pages          =   $pageM->adminPageList('chat_useful', $where,$_POST['page'],array('limit'=>$_POST['limit']));

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

                $where['limit'] =   $pages['limit'];
                $chatList       =   $chatM -> getChatUsefulList($where,array('admin'=>1));
            }
        }

        $data['list']           =   !empty($chatList)?$chatList:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }

    function del_action(){

        $chatM  =   $this -> Model('chat');
        
        if(is_array($_POST['del'])){
            
            $delid      =   @implode(',',$_POST['del']);
            
            $layer_type =   1;

            $where['id']    =   array('in',$delid);
        }else{
            
            $delid      =   (int)$_POST['del'];
            
            $layer_type =   0;

            $where['id']    =   $delid;
        }

        

        $data['utype'] = "admin";
        $del    =   $chatM -> delChatUseful($where,$data);

        if($del){
            $msg = '用户常用语(ID:'.$delid.')删除成功！';
            $error = 0;
        }else{
            $msg = '删除失败！';
            $error = 1;
        }

        $this->admin_json($error,$msg);

    }
    function add_action(){
        
        $data = array('content'=>trim($_POST['content']));
        
        $chatM  =   $this->Model('chat');
        $data['utype'] = "admin";

        $error = 1;

        if (isset($_POST['id'])){
            $res = $chatM->upChatUseful(array('id'=>intval($_POST['id'])), $data);
            $error = $res['errcode']==9?0:1;
            $msg = $res['msg'];
        }else{
            $msg = '数据异常请重试';
        }

        $this->render_json($error,$msg);
    }
    function usefulSet_action(){
        
        $chatM = $this->MODEL('chat');
        $chatList = $chatM->getUsefulSetList(array('orderby'=>'sort,desc'),array('admin'=>1));
        $list = array();
        foreach ($chatList as $k => $v) {
            $list[$v['type']][] = $v;
        }

        $data['list'] = $list;

        $data['type_arr'] = array(
            1=>'企业常用语',
            2=>'个人常用语',
            5=>'客勤常用语',
            3=>'企业打招呼语',
            4=>'个人打招呼语',
        );
        $this->render_json(0,'',$data);
    }
    function customdel_action(){

        $delid      =   (int)$_POST['del'];
        $chatM  =   $this -> Model('chat');

        $where['id']    =   $delid;
        $return =   $chatM -> delUsefulSet($where);

        $error = $return['errcode']==9?0:1;
        $msg = $return['msg'];
        
        $this->render_json($error,$msg);

    }
    function usefulSetAdd_action(){
        if($_POST['content']){
            
            $chatM = $this->MODEL('chat');
            $_POST['content'] = trim($_POST['content']);
            if(isset($_POST['id'])&&!empty($_POST['id'])){
                $return = $chatM->upChatUsefulSet(array('id'=>intval($_POST['id'])),$_POST);
            }else{

                $return = $chatM->addChatUsefulSet($_POST);
            }
            $error = $return['errcode']==9?0:1;
            $msg = $return['msg'];
        }else{
            $msg = '非法操作！';
            $error = 1;
        }

        $this->render_json($error,$msg);
    }
}
?>