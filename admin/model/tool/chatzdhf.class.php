<?php

class chatzdhf_controller extends adminCommon{
    
    function index_action(){
        
        $noUsername = true;
        $where = array();
        
        $chatM = $this->MODEL('chat');

        if(trim($_POST['keyword'])){
            
            $keyword    =   trim($_POST['keyword']);
            
            $type       =   intval($_POST['type']);

            if ($type==1){
                
                $companyM  =  $this->MODEL('company');
                $member  =  $companyM->getList(array('name'=>array('like',$keyword)),array('field'=>'`uid`'));
                
                if (!empty($member['list'])){
                    
                    $muids  =  array();
                    foreach ($member['list'] as $v){
                        
                        $muids[] = $v['uid'];
                    }
                    
                    $where['uid']  =  array('in',pylode(',', $muids));
                    
                }else{
                    $noUsername = false;
                }
            }elseif($type==2){
                
                $where['content']   =   array('like',$keyword);
            }elseif($type==3){
                
                $where['keyword']   =   array('like',$keyword);
            }
        }


        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->adminPageList('chat_zdcon', $where,$_POST['page'],array('limit'=>$_POST['limit']));

        if ($noUsername){
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
                $chatList       =   $chatM -> getChatZdConList($where,array('utype'=>'admin'));
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
        $del    =   $chatM -> delChatZdcon($where,$data);

        if($del){
            $msg = '用户自动回复(ID:'.$delid.')删除成功！';
            $error = 0;
        }else{
            $msg = '删除失败！';
            $error = 1;
        }

        $this->admin_json($error,$msg);

    }
    function save_action(){
        
        $data = array(
            'content'=>trim($_POST['content']),
            'keyword'=>trim($_POST['keyword']),
            'type'=>$_POST['type'],
            'sort'=>$_POST['sort'],
        );
        
        $chatM  =   $this->Model('chat');
        $data['utype'] = "admin";

        $error = 1;

        if (isset($_POST['id'])){
            $res = $chatM->upChatZdcon(array('id'=>intval($_POST['id'])), $data);
            $error = $res['errcode']==9?0:1;
            $msg = $res['msg'];
        }else{
            $msg = '数据异常请重试';
        }


        $this->render_json($error,$msg);
    }
}
?>