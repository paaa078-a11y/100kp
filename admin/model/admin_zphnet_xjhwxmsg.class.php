<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class admin_zphnet_xjhwxmsg_controller extends adminCommon{
	
	function index_action(){
	    
	    $search_list[]	=	array('param' => 'status','name' => '状态','value' => array('1' => '成功','2' => '失败'));
	    
	    $this -> yunset('search_list',$search_list);
		
	    $where  =  array();
	    
		if (isset($_GET['status'])){
		    
		    if($_GET['status']==1){
		        
		        $where['status']  =  0;
		        
		    }else if ($_GET['status']==2){
		        
		        $where['status']  =  array('<>',0);
		    }
		    
		    $urlarr['status']	=	$_GET['status'];
		}
		
		if(isset($_GET['keyword'])){
			
		    $where['name']  =  array('like',trim($_GET['keyword']));
			
			$urlarr['keyword']	=	$_GET['keyword'];
		}
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c']; 
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('zphnet_xjhwxmsg',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
		    $where['orderby']		=	'id,desc';
			
			$where['limit']			=	$pages['limit'];
			
			$zphnetM  =  $this->MODEL('zphnet');
			$List	  =	 $zphnetM -> getXjhWxMsg($where);
			$this->yunset("rows",$List);
		}
		
		$this->yuntpl(array('admin/admin_zphnet_xjhwxmsg'));
		
	}
	function del_action(){

		if($_POST['del']){
			
			$delid  =  $_POST['del'];
			
		}else{
			
			$this->check_token();
			
			$delid  =  (int)$_GET['id'];
		}
		
		$zphnetM  =	 $this->MODEL('zphnet');
		
		$return	  =	 $zphnetM->delXjhWxMsg($delid);
		
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	/**
	 * 宣讲未开始前向通知发送失败用户重发
	 */
	function resend_action(){
	    
	    $zphnetM  =	 $this->MODEL('zphnet');
	    $return   =  $zphnetM->xjhWxmsgResend();
	    
	    echo json_encode($return);
	}
}

?>