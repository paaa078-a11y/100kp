<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class admin_event_log_controller extends adminCommon{
	function set_search(){
		$search_list[]=array("param"=>"usertype","name"=>'用户类型',"value"=>array("1"=>"个人用户","2"=>"企业用户"));
		$search_list[]=array("param"=>"times","name"=>'滞留时间',"value"=>array("1"=>"1分钟内","2"=>"1-2分钟内","3"=>"2-5分钟内","4"=>"5分钟以上"));
		$search_list[]=array("param"=>"opera","name"=>'操作行为',"value"=>array("1"=>"基本信息","2"=>"简历","3"=>"职位","4"=>"财务","5"=>"账户认证"));
		$search_list[]=array("param"=>"status","name"=>'行为结果',"value"=>array("3"=>"未操作","1"=>"操作完成","2"=>"操作失败"));
		$this->yunset("search_list",$search_list);
	}
	function index_action(){
		$LogM		=	$this -> MODEL('log');
		$UserinfoM	=	$this -> MODEL('userinfo');
		$this->set_search();	
		
		if(trim($_GET['keyword'])){
			if($_GET['type']==1){
				$username		=$UserinfoM -> getList(array('username'=>array('like',trim($_GET['keyword']))),array('field'=>'`uid`,`username`'));
				if($username && is_array($username)){
					foreach($username as $val){
						$muids[]	=	$val['uid'];
					}
					$where['uid']	=	array('in',pylode(',', $muids));
				}
			}
			$urlarr["keyword"]	=	$_GET["keyword"];
			$urlarr["type"]		=	$_GET["type"];
		}
		if($_GET['usertype']){
			$where['usertype']	=	$_GET['usertype'];
			$urlarr['usertype']	=	$_GET['usertype'];
		}

		if($_GET['times']){
			if($_GET['times']==1){
				$where['second']	=	array('<',60);
			}else if($_GET['times']==2){ 
				$where['PHPYUNBTWSTART_A']    =   '';
				$where['second'][]	=	array('>=',60,'AND');
				$where['second'][]	=	array('<',120,'AND');
				$where['PHPYUNBTWEND_A']    =   '';
			}else if($_GET['times']==3){
				$where['PHPYUNBTWSTART_A']    =   '';
				$where['second'][]	=	array('>=',120,'AND');
				$where['second'][]	=	array('<',300,'AND');
				$where['PHPYUNBTWEND_A']    =   '';
			}else if($_GET['times']==4){ 
				$where['second']	=	array('>=',300);
			}
			$urlarr['times']		=	$_GET['times'];
		}

		if($_GET['time']){
			$times=@explode('~',$_GET['time']);
			$where['timein'][]	=	array('>=',strtotime($times[0]."00:00:00"),'AND');
			$where['timein'][]	=	array('<=',strtotime($times[1]."23:59:59"),'AND');
	        $urlarr['time']		=	$_GET['time'];
	    }

		if($_GET['opera']){
			if($_GET['opera']==1){
			    
			    $where['PHPYUNBTWSTART_A']   =   '';
				$where['opera'][]	=	array('=', '10', 'OR');
				$where['opera'][]	=	array('=','11','OR');
				$where['PHPYUNBTWEND_A']     =   '';
			}else if($_GET['opera']==2){
			    
			    $where['PHPYUNBTWSTART_A']   =   '';
			    $where['opera'][]	=	array('=', '21', 'OR');
			    $where['opera'][]	=	array('=','22','OR');
			    $where['PHPYUNBTWEND_A']     =   '';
			}else if($_GET['opera']==3){
			    
			    $where['PHPYUNBTWSTART_A']   =   '';
			    $where['opera'][]	=	array('>', '30', 'AND');
			    $where['opera'][]	=	array('<','39','AND');
			    $where['PHPYUNBTWEND_A']     =   '';
			}else if($_GET['opera']==4){
			    
			    $where['PHPYUNBTWSTART_A']   =   '';
			    $where['opera'][]	=	array('>', '40', 'AND');
			    $where['opera'][]	=	array('<','49','AND');
			    $where['PHPYUNBTWEND_A']     =   '';
			}else if($_GET['opera']==5){
			    
			    $where['PHPYUNBTWSTART_A']   =   '';
			    $where['opera'][]	=	array('=', '12', 'OR');
			    $where['opera'][]	=	array('=','13','OR');
			    $where['PHPYUNBTWEND_A']     =   '';
			}
			$urlarr['opera']	=	$_GET['opera'];
		}

		if($_GET['status']){
			if($_GET['status']==1){
				$where['PHPYUNBTWSTART_A']       =    '';
				$where['status'][]	=	array('=','1','OR');
				$where['status'][]	=	array('=','10','OR');
				$where['status'][]	=	array('=','11','OR');
				$where['status'][]	=	array('=','12','OR');
				$where['PHPYUNBTWEND_A']    =   '';
			}else if($_GET['status']==2){
				$where['status']	=	2;
			}else{
				$where['status']	=	0;
			}
			$urlarr['status']		=	$_GET['status'];
		}
 		$urlarr['page']	=	"{{page}}";
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		$pageM			=	$this -> MODEL('page');
		$pages			=	$pageM -> pageList('user_log',$where,$pageurl,$_GET['page']);
		if($pages['total']>0){
			if($_GET['order']){
				$where['orderby']	=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']	=	$_GET['order'];
				$urlarr['t']		=	$_GET['t'];
           
		    }else{  
				$where['orderby']	=	'timeout'; 
			}
			$where['limit']		=	$pages['limit'];         
			$rows	=	$LogM -> getUserLogList($where,array('utype'=>'admin'));
		}
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_event_log'));
	}

	function del_action(){
		$this->check_token();
		$LogM	=	$this -> MODEL('log');
		if($_GET['del']){
			$return	=	$LogM->delEventlog($_GET['del']);
			$this->layer_msg( $return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
		}
	}
}

?>