<?php

class privacylog_controller extends adminCommon{
    
    function index_action(){
        
        $mobliemsgM =   $this->MODEL('privacy');

        if(trim($_POST['keyword'])){
            
            $_POST['keyword']    =   trim($_POST['keyword']);

            if ($_POST['type']=='1'){
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['eid'][] =   array('=',  $_POST['keyword']);
                $where['eid'][] =   array('<>', '0');
                $where['PHPYUNBTWEND_A']    =   '';
            }else if($_POST['type']=='2'){
                
                $where['comid'] =   array('=',$_POST['keyword']);
                 
            }else if($_POST['type']=='3'){
                
                $where['bindnumber']    =   array('like',$_POST['keyword']);
                 
            }else if($_POST['type']=='4'){
                
                $where['middlenumber']  =   array('like',$_POST['keyword']);
                 
            }else if($_POST['type']=='5'){
                
                $where['result']    =   array('like',$_POST['keyword']);
                 
            }else if($_POST['type']=='6'){
                
                $where['message']   =   array('like',$_POST['keyword']);
                 
            }
        }


        if($_POST['state']){
            
            if($_POST['state'] == '1'){
                $where['result']    =   1;
            }else{
                $where['result']    =   array('>',1);
            }
        }
        
        if($_POST['time']){

            if($_POST['time']=='1'){
                
                $where['ctime'][]      =   array('>',strtotime(date("Y-m-d 00:00:00")));
            }else{
                
                $where['ctime'][]      =   array('>',strtotime('-'.$_POST['time'].'day'));
            }

        }else if ($_POST['date1'] && $_POST['date2']) {

            $where['ctime'][]   =   array('>=', $_POST['date1']);
            $where['ctime'][]   =   array('<=', $_POST['date2']+86399);
        }

        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->adminPageList('privacy_log', $where,$_POST['page'],array('limit'=>$_POST['limit']));

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
            $list               =   $mobliemsgM->getList($where);
        }

        $data['list']           =   !empty($list['list'])?$list['list']:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $pages['limit'][1];
        $this->render_json(0, '', $data);
    }

    function del_action(){

        $privacyM   =   $this->MODEL('privacy');
        
        if(is_array($_POST['del'])){
            
            $where['id']    =   array('in',pylode(',',$_POST['del']));
            
            $del            =   $privacyM->delMoblieMsg($where,array('type'=>'all'));
            
            $layer_type     =   1;
            
            $delid          =   pylode(',',$_POST['del']);
            
        }else{
            
            $where['id']    =   (int)$_POST['del'];
            
            $del            =   $privacyM->delMoblieMsg($where,array('type'=>'one'));
            
            $layer_type     =   0;
            
            $delid          =   (int)$_POST['del'];
        }
    
        if(!$delid){
            $this->render_json(1, '请选择要删除的内容！');
        }

        if($del){
            $msg = '隐私号日志记录(ID:'.$delid.')删除成功！';
            $error = 0;
        }else{
            $msg = '删除失败！';
            $error = 1;
        }

        $this->admin_json($error,$msg);

    }
}
?>