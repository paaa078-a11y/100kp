<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class admin_chat_zdcon_controller extends adminCommon{  
	function index_action(){
		$chatM = $this->MODEL('chat');
		$where = array();
		$noUsername = true;
		if($_GET['keyword']){
			
			$keyword	=	trim($_GET['keyword']);
			
			$type		=	intval($_GET['type']);
			
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
				
				$where['content']	=	array('like',$keyword);
			}elseif($type==3){
				
				$where['keyword']	=	array('like',$keyword);
			}
			$urlarr['type']			=	"".$type."";
			
			$urlarr['keyword']		=	"".$keyword."";
		}
		// 用户名没搜到的，不查询
		if ($noUsername){
			$urlarr        	=   $_GET;
		    $urlarr['page']	=	"{{page}}";
		    
		    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
		    
		    $pageM			=	$this  -> MODEL('page');
		    
		    $pages			=	$pageM -> pageList('chat_zdcon',$where,$pageurl,$_GET['page']);
		    
		    //分页数大于0的情况下 执行列表查询
		    if($pages['total'] > 0){
		        
		        if($_GET['order'])
		        {
		            $where['orderby']	=	$_GET['t'].','.$_GET['order'];
		            
		            $urlarr['order']	=	$_GET['order'];
		            
		            $urlarr['t']		=	$_GET['t'];
		        }else{
		            
		            $where['orderby']	=	'id';
		        }
		        
		        $where['limit']	=	$pages['limit'];
		        
		        $chatList		=	$chatM -> getChatZdConList($where,array('utype'=>'admin'));
		    }
		    
		    $this->yunset('rows',$chatList);
		}
		
		$this->yuntpl(array('admin/admin_chat_zdcon'));
	}
	
	function save_action(){
	    
	    $data = array(
	    	'content'=>trim($_POST['content']),
	    	'keyword'=>trim($_POST['keyword']),
	    	'type'=>$_POST['type'],
	    	'sort'=>$_POST['sort'],
	    );
	    
	    $chatM	=	$this->Model('chat');
	    $data['utype'] = "admin";
	    if (isset($_POST['id'])){
	        $res = $chatM->upChatZdcon(array('id'=>intval($_POST['id'])), $data);
	        $this->ACT_layer_msg($res['msg'],$res['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	    }
	}
	
	function del_action(){

		$chatM	=	$this -> Model('chat');
		
		if(is_array($_POST['del'])){
			
			$delid		=	@implode(',',$_POST['del']);
			
			$layer_type	=	1;
		}else{
			$this -> check_token();
			
			$delid		=	(int)$_GET['id'];
			
			$layer_type	=	0;
		}

		$where['id']	=	array('in',$delid);

        $data['utype'] = "admin";
		$del	=	$chatM -> delChatZdcon($where,$data);

		$del?$this -> layer_msg('用户自动回复(ID:'.$delid.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']):$this -> layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);

	}
}

?>