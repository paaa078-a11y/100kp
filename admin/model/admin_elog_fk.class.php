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
class admin_elog_fk_controller extends adminCommon{	

	function index_action(){
		
		$sdate = date('Y-m-d', strtotime('-1 day'));
		$edate = date('Y-m-d', time());

		$this->yunset('sdate', $sdate);
		$this->yunset('edate', $edate);

		$this->yuntpl(array('admin/admin_elog_fk'));
	}	
 

	function get_result($where){
		$logM					=		$this->MODEL('log');

		if($_GET['time']){
		
			$times				=		@explode('~',$_GET['time']);
			$where['timein'][]	=		array('>=',strtotime($times[0]."00:00:00"));
			$where['timein'][]	=		array('<=',strtotime($times[1]."23:59:59"));
	        $urlarr['time']		=		$_GET['time'];
			$time_begin 		= 		$times[0];
			$time_end 			= 		$times[1];
	    }else{
			$sdate 				= 		strtotime('-1 days');
			$edate 				= 		time();
			$where['timein'][]	=		array('>',$sdate);
			$where['timein'][]	=		array('<',$edate);
			$time_begin 		= 		date('Y-m-d', strtotime('-1 day'));
			$time_end 			= 		date('Y-m-d', time());
		}

		$this->yunset('time_begin', $time_begin);
		$this->yunset('time_end', $time_end);
		
		/**
		 *	1:默认超过10秒，并且在5分钟以内未操作用户行为;
		 *	2:财务行为操作，已经下单尚未付款行为;
		 *	3:其他用户操作，行为操作失败;
		*/

		if($_GET['seconds']){
			$seconds 			= 		(int)$_GET['seconds'];
			$urlarr['seconds']	=		$_GET['seconds'];
		}else{
			$seconds 			= 		10;
		}
		$this->yunset('seconds', $seconds);
		
		$where['second'][]		=		array('>',$seconds);
		$where['second'][]		=		array('<',300);

		$urlarr["c"]			=		$_GET['c'];
		$urlarr['page']			=		"{{page}}";
		$pageurl				=		Url($_GET['m'],$urlarr,'admin');
		$pageM					=		$this->MODEL('page');
		
		$pages					=		$pageM->pageList("user_log",$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
	            
			$where['orderby']	=		array('uid,desc','timein,desc');
	        
	        $where['limit']		=		$pages['limit'];
 	        
	        $List   			=   	$logM -> getUserLogList($where,array('utype'=>'admin'));
	        
	        $this -> yunset(array('rows'=>$List));
	        
	    }
	}
	 
	//完善信息统计
	function basic_action(){
			
		$where['opera'] = array('<=',11);

		$this->get_result($where);
		
		$this->yuntpl(array('admin/admin_elog_notice'));
	
	}
		
	//账号充值统计
	function integral_action(){
		
		$where['PHPYUNBTWSTART']	=	'';
		
		$where['opera'][] 			= 	array('=',41,'OR');
						
		$where['opera'][] 			= 	array('=',45,'OR');
		
		$where['PHPYUNBTWEND']		=	'';
		
		$this->get_result($where);
		
		$this->yuntpl(array('admin/admin_elog_notice'));
	
	}

	//会员套餐服务
	function vip_action(){
		
		$where['PHPYUNBTWSTART']	=	'';
		
		$where['opera'][] 			= 	array('>',41);
						
		$where['opera'][] 			= 	array('<',49);
		
		$where['opera'][] 			= 	array('<>',45);
		
		$where['PHPYUNBTWEND']		=	'';
		
		$this->get_result($where);
		
		$this->yuntpl(array('admin/admin_elog_notice'));
	
	}

	//新建简历
	function resume_action(){
		
		$where['PHPYUNBTWSTART']	=	'';
		
		$where['opera'][] 			= 	array('=',21,'OR');
						
		$where['opera'][] 			= 	array('=',22,'OR');
		
		$where['PHPYUNBTWEND']		=	'';

		$this->get_result($where);
		
		$this->yuntpl(array('admin/admin_elog_notice'));
	
	}

	//职位发布
	function job_action(){
		
		$where['PHPYUNBTWSTART']	=	'';
		
		$where['opera'][] 			= 	array('>=',31);
						
		$where['opera'][] 			= 	array('<=',38);
		
		$where['PHPYUNBTWEND']		=	'';
		
		$this->get_result($where);
		
		$this->yuntpl(array('admin/admin_elog_notice'));
	
	}

	//账户认证
	function binding_action(){
		
		$where['PHPYUNBTWSTART']	=	'';
		
		$where['opera'][] 			= 	array('=',12,'OR');
						
		$where['opera'][] 			= 	array('=',13,'OR');
		
		$where['PHPYUNBTWEND']		=	'';
		
		$this->get_result($where);
		
		$this->yuntpl(array('admin/admin_elog_notice'));
	
	}

	function note_action(){//备注反馈
		$logM						=	$this->MODEL('log');
		$note						=	trim($_POST['note']);
		$id							=	(int)$_POST['id'];
		if($id && $note){
			
			$nid					=	$logM->upUserLog(array('note'=>$note),array('id'=>$id));
			
		}
 		$nid?$this->ACT_layer_msg("跟进(ID:".$id.")设置成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg( "设置失败！",8,$_SERVER['HTTP_REFERER']);
	}

	function getFkNum_action(){
		
		$MsgNum		=		$this->MODEL('msgNum');
		
		$arr		=		$MsgNum->getFkNum();
		
		echo json_encode($arr);die;

	}

}

?>