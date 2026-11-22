<?php

class chatlog_controller extends adminCommon{
    
    function index_action(){
        
        $noUsername = true;
        $where = array();
        
        $chatM = $this->MODEL('chat');

        if ($_POST['keyword']){
            
            $keyword  =  trim($_POST['keyword']);
            
            $type     =   trim($_POST['type']);

            if ($type==1 || $type==2){
                
                $userInfoM  =  $this->MODEL('userinfo');
                $member  =  $userInfoM->getList(array('username'=>array('like',$keyword)),array('field'=>'`uid`'));
                if (!empty($member)){
                    
                    $muids  =  array();
                    foreach ($member as $v){
                        
                        $muids[] = $v['uid'];
                    }
                    if ($type == 1){
                        
                        $where['from']  =  array('in',pylode(',', $muids));
                        
                    }elseif ($type == 2){
                        
                        $where['to']  =  array('in',pylode(',', $muids));
                    }
                }else{
                    $noUsername = false;
                }
            }elseif($type=='3'){
                
                $where['content']   =   array('like',$keyword);
            }else if($type=='4'){
                $comwhere['PHPYUNBTWSTART_C']   = '';
                $comwhere['name']               = array('like',$keyword);
                $comwhere['shortname']          = array('like',$keyword,'OR');
                $comwhere['PHPYUNBTWEND_C']     = '';

                $companyM  =  $this->MODEL('company');
                $cuid_arr  =  array();
                $coms = $companyM->getChCompanyList($comwhere,array('field'=>'uid'));
                foreach ($coms as $ck => $cv) {
                    $cuid_arr[] = $cv['uid'];
                }
                if(!empty($cuid_arr)){
                    $where['PHPYUNBTWSTART_S1_DOUBLE']=   '';
                
                    $where['fusertype']         =   2;
                    $where['from']              =   array('in',pylode(',',$cuid_arr));
                    $where['PHPYUNBTWEND_S1']  =   '';

                    $where['PHPYUNBTWSTART_S2']=   'OR';
                    $where['tusertype']         =   2;
                    $where['to']                =   array('in',pylode(',',$cuid_arr));
                    $where['PHPYUNBTWEND_S2_DOUBLE']  =   '';

                }else{
                    $noUsername = false;
                }
                

            }else if($type=='5'){
                
                $userwhere['name']               = array('like',$keyword);
                
                $userInfoM  =  $this->MODEL('userinfo');
                $uid_arr  =  array();
                $users = $userInfoM->getUserInfoList($userwhere,array('usertype'=>'1','field'=>'uid'));
                foreach ($users as $uk => $uv) {
                    $uid_arr[] = $uv['uid'];
                }
                if(!empty($uid_arr)){
                    $where['PHPYUNBTWSTART_S1_DOUBLE']=   '';
                    
                    $where['fusertype']         =   1;
                    $where['from']              =   array('in',pylode(',',$uid_arr));
                    $where['PHPYUNBTWEND_S1']  =   '';

                    $where['PHPYUNBTWSTART_S2']=   'OR';
                    $where['tusertype']         =   1;
                    $where['to']                =   array('in',pylode(',',$uid_arr));
                    $where['PHPYUNBTWEND_S2_DOUBLE']  =   '';

                }else{
                    $noUsername = false;
                }

            }else if($type=='6'){
                
                $userInfoM  =  $this->MODEL('userinfo');
                $member  =  $userInfoM->getList(array('moblie'=>array('like',$keyword)),array('field'=>'`uid`'));
                if (!empty($member)){
                    
                    $muids  =  array();
                    foreach ($member as $v){
                        
                        $muids[] = $v['uid'];
                    }
                    $where['PHPYUNBTWSTART_S1']=   '';
                    $where['from']  =  array('in',pylode(',', $muids));
                    $where['to']  =  array('in',pylode(',', $muids),'or');
                    $where['PHPYUNBTWEND_S1']  =   '';
                }else{
                    $noUsername = false;
                }

            }
        }
        if (isset($_POST['status']) && !empty($_POST['status'])){
            $where['status']    =   $_POST['status'];
        }
        if ($_POST['date1'] && $_POST['date2']) {

            $where['PHPYUNBTWSTART']=   '';
            $where['sendTime'][]    =   array('>=',strtotime($_POST['date1']." 00:00:00"));
            $where['sendTime'][]    =   array('<',strtotime($_POST['date2']." 23:59:59"));
            $where['PHPYUNBTWEND']  =   '';
        }
        if ($noUsername){
            $pageM          =   $this->MODEL('page');
            $pages          =   $pageM->adminPageList('chat_log', $where,$_POST['page'],array('limit'=>$_POST['limit']));

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
                $chatList       =   $chatM -> getChatLogList($where,array('admin'=>1));
                
                if ($chatList && is_array($chatList)){
                    
                    foreach ($chatList as $k=>$v){
                        
                        $chatList[$k]['sendTime'] = ceil($v['sendTime']/1000);
                        
                    }
                }
            }
        }

        $data['list']           =   !empty($chatList)?$chatList:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }
    function dellog_action(){
        $chatM  =   $this -> Model('chat');
        
        if(is_array($_POST['del'])){
            
            $delid      =   @implode(',',$_POST['del']);
            
            $layer_type =   1;
        }else{
            
            $delid      =   (int)$_POST['del'];
            
            $layer_type =   0;
        }
        
        $where['id']    =   array('in',$delid);
        
        $del    =   $chatM -> delChatLog(array('where'=>$where));

        if($del){
            $error = 0;
            $msg = '聊天记录(ID:'.$delid.')删除成功！';
        }else{
            $error = 1;
            $msg = '删除失败！';
        }

        $this->render_json($error,$msg);
    }
    function blacklist_action(){
        
        $noUsername = true;
        $where = array();
        $where['state']  =  '2';
        $chatM = $this->MODEL('chat');
        if ($_POST['keyword']){
            
            $keyword  =  trim($_POST['keyword']);
            
            if ($_POST['type'] == 1 || $_POST['type'] == 2){
                
                $userInfoM  =  $this->MODEL('userinfo');
                $member  =  $userInfoM->getList(array('username'=>array('like',$keyword)),array('field'=>'`uid`'));
                if (!empty($member)){
                    
                    $muids  =  array();
                    foreach ($member as $v){
                        
                        $muids[] = $v['uid'];
                    }
                    if ($_POST['type'] == 1){
                        
                        $where['uid']  =  array('in',pylode(',', $muids));
                        
                    }elseif ($_POST['type'] == 2){
                        
                        $where['fid']  =  array('in',pylode(',', $muids));
                    }
                }else{
                    $noUsername = false;
                }
                
            }elseif ($_POST['type'] == 3){
                
                $where['uid']  =  $keyword;
                
            }elseif ($_POST['type'] == 4){
                
                $where['fid']  =  $keyword;
            }
        }
        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->adminPageList('chat_friend', $where,$_POST['page'],array('limit'=>$_POST['limit']));

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

                $where['limit'] =   $pages['limit'];
                $chatList   =  $chatM->getFriendList($where,array('utype'=>'admin'));
            }
        }

        $data['list']          =   !empty($chatList)?$chatList:array();
        $data['total']         =   $pages['total'];
        $data['page_sizes']    =   $pages['page_sizes'];
        $data['page_size']     =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }

    function delblack_action(){

        $chatM  =   $this -> Model('chat');
        
        $del=$_POST['del'];

        if($del){
            if(is_array($del)){
                $layer_type  =  1;
                $del  =  pylode(',',$del);
            }else{
                $layer_type  =  0;
            }
            $chatM  =  $this->MODEL('chat');
            $chatM->upFriend(array('id'=>array('in',$del)),array('state'=>1));

            $msg = '屏蔽(ID:'.$del.')解除成功！';
            $error = 0;
        }else{
            $msg = '请选择您要解除的信息！';
            $error = 1;
        }

        $this->render_json($error,$msg);

    }

    function userchat_action(){
        
        $chatM  =   $this -> MODEL('chat');
        
        if (isset($_POST['id'])){
            $log = $chatM->getChatLog(array('id'=>(intval($_POST['id']))));
        }elseif (isset($_POST['beginid'])){
            $log = $chatM->getChatLog(array('beginid'=>$_POST['beginid']));
        }
        
        $arr    =  array(
            'toid'       =>  $log['to'],
            'tusertype'  =>  $log['tusertype'],
            'fromid'     =>  $log['from'],
            'fusertype'  =>  $log['fusertype'],
            'page'       =>  $_POST['page'],
            'lastid'     =>  ''
        );
        $return  =  $chatM -> getChatPage($arr);

        $pArr   =  array(
            'uid'       =>  intval($log['from']),
            'usertype'  =>  intval($log['fusertype']),
            'toid'      =>  intval($log['to']),
            'totype'    =>  intval($log['tusertype'])
        );

        $list   =  $chatM -> getPrepare($pArr);

        $return['joblist'] =  !empty($list['joblist'])?$list['joblist']:array(); 
        $return['expect'] =  !empty($list['expect'])?$list['expect']:array(); 


        $canwx  =   $chatM->getFriendCan(array('type'=>'wx','uid'=>$log['to'],'usertype'=>$log['tusertype'],'fid'=>$log['from'],'fusertype'=>$log['fusertype']));
        $cantel =   $chatM->getFriendCan(array('type'=>'tel','uid'=>$log['to'],'usertype'=>$log['tusertype'],'fid'=>$log['from'],'fusertype'=>$log['fusertype']));

        $return['cantel'] =  $cantel; 
        $return['canwx'] =  $canwx;   
        
        $this->render_json(0,'',$return);
    }
    
}
?>