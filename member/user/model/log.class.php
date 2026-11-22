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
class log_controller extends user{
  
	function index_action(){
    $LogM 				= $this->MODEL('log');
		if($_POST){	
			
			$_POST['uid'] 		= $this->uid;
			$_POST['usertype'] 	= $this->usertype;
			
			 
			if($_COOKIE["delay"]==""){
				$nid = $LogM->addUserLog($_POST);
				$this->cookie->SetCookie("delay",$nid,time() + 60);
				echo $nid;die;
			}else{
				$ul = $LogM->getUserLog(array('id'=>$_COOKIE["delay"]),array('field'=>'`second`,`opera`'));
				
				if($ul['opera']==$_POST['opera']){
					
					$data['id'] 		= $_COOKIE["delay"];
					$data['orderid'] 	= $_POST["orderid"];
					$data['second'] 	= $_POST['second']+$ul['second'];
					$LogM->upUserLog($data);
					echo $_COOKIE["delay"];die;

				}else{

					$nid = $LogM->addUserLog($_POST);
					$this->cookie->SetCookie("delay",$nid,time() + 60);
					echo $nid;die;

				}
			}
 		}
	} 

	function gxLog_action(){
    $LogM	= $this->MODEL('log');
		if($_POST){
			
			if($_COOKIE["delay"]!=""){
				$ul = $LogM->getUserLog(array('id'=>$_COOKIE["delay"]),array('field'=>'`second`'));
				$_POST['second']	=	$_POST['second']+$ul['second'];
			}
			
			$_POST['uid']	 	= $this->uid;
			$_POST['usertype'] 	= $this->usertype;
			$LogM->upUserLog($_POST);
		}
	}
	 
}
?>