<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class supply_pic_controller extends adminCommon{

    /**
     * 会员-供求-图片管理：个人头像（列表）
     */
	function index_action(){

		$gqdemandM    			    	=  		$this -> MODEL('gqdemand');
	    $where['photo']  				=  		array('<>','');
	    if($_POST['keyword']){
	        $keytype  					=   	intval($_POST['type']);
	        $keyword  					=   	trim($_POST['keyword']);
	        if ($keytype == 1){
	            $where['name']      	=  		array('like',$keyword);
	        }elseif ($keytype == 2){
	            $where['uid']       	=  		array('=',$keyword);
			}
	    }
	    if(isset($_POST['status'])){
	        $status                 	=   	intval($_POST['status']);
	        $where['photo_status']  	=  		$status;
		}
        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('gq_info', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
	    //分页数大于0的情况下 执行列表查询
        $list= array();
	    if($pages['total'] > 0){
	        //limit order 只有在列表查询时才需要
			$where['orderby']			=		array('photo_status,desc','uid,desc');
	        $where['limit']				=		$pages['limit'];
	        $list						=		$gqdemandM->getGqinfoList($where,array('field'=>'`photo`,`uid`,`photo_status`,`name`'));
	    }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));

	}
    public function  getSearchData_action(){
        $search_list[]  = array("param"=> "status","name"=> '审核状态',"value" => array('1'=>'待审核','0' => '已审核','2' => '未通过'));
        $this->render_json(0, 'ok', compact('search_list'));
    }
	/**
	 * 会员-供求-图片管理：用户头像（获取审核说明）
	 */
	function getStatusBody_action(){
	    
		$gqdemandM     =  $this -> MODEL('gqdemand');

		$where['uid']  =  intval($_POST['uid']);
	    
		$return  	   =  $gqdemandM -> getGqInfo($where,array('field'=>'`photo_statusbody`','type'=>1));
	    $photo_statusbody = trim($return['photo_statusbody']);

        $this->render_json(0, 'ok', compact('photo_statusbody'));
	}
	/**
	 * 会员-个人-图片管理：个人头像（审核）
	 */
	function status_action(){
	    
		$gqdemandM    			    	=  		$this -> MODEL('gqdemand');
		$uid							=		@explode(',', $_POST['uid']);
		if($_POST['status']  ==""){
            $this->render_json(1, '请选择审核状态');
        }
		if($uid){
			if(is_array($uid)){
				$where['uid']                =              array('in', pylode(',', $uid));
			}else{
				$where['uid']                =              intval($uid);
			}
			if($_POST['status']  ==2){
				//头像不通过审核
				$data							 =				array(
					'photo'    			 		=>  			'',
					'photo_status'    			 =>  			intval($_POST['status']),
					'photo_statusbody' 		     =>  			$_POST['statusbody']
				);
			}else{
				//头像通过审核
				$data							 =				array(
					'photo_status'    			 =>  			intval($_POST['status']),
					'photo_statusbody' 		     =>  			$_POST['statusbody']
				);
			}
			$return  =  $gqdemandM -> upGqInfoStatus($where,$data);
            $this->render_json($return['errcode']==9?0:1, $return['msg']);
		}else{
            $this->render_json(1, '非法操作');
		}
	}

	
	/**
	 * 会员-个人-图片管理：个人头像（修改）
	 */
	function savePhoto_action(){

		$gqdemandM    			    	=  		$this -> MODEL('gqdemand');
	    $file     						=  		$_FILES['file'];
		$where['uid']					=		intval($_POST['id']);
		$data							=		array(
			'file'						=>		$file,
			'photo_status'				=>		0,
			'photo_statusbody'			=>		''
		);
	    $return   =  $gqdemandM -> upaddGqinfo($where,$data);
        $this->render_json($return['errcode']==9?0:1, $return['msg']);
	}

	
	/**
	 * 会员-个人-图片管理：个人头像（删除）
	 */
	function del_action(){

		$gqdemandM    			    	=  		$this -> MODEL('gqdemand');
	    if ($_POST['del']){
			$uid          				=  		intval($_POST['del']);
	    }elseif ($_POST['del']){
			$uid          				=  		$_POST['del'];
	    }
		$where['uid']                	=              array('in', pylode(',', $uid));
		$data							=				array(
			'photo'    			 		=>  			'',
			'photo_status'    			=>  			1,
			'photo_statusbody'    		=>  			''
		);
	    $return   =  $gqdemandM -> upaddGqinfo($where,$data,1);
        $this->render_json($return['errcode']==9?0:1, $return['msg']);
	}

}
?>