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
class admin_lt_rating_controller extends adminCommon{

    public function returnConfig_action(){
        $config = $this->config;
        $this->render_json(0,'',['sy_chat_name'=>$config['sy_chat_name']]);
    }

    /**
	 * 会员-猎头-增值套餐服务
	 * 2019-06-03 hjy
	 */
	public function index_action(){
		//会员等级			
		$ratingM				=	$this -> MODEL('rating');
		$whereData				=	array();
		$whereData['category']	=	array('=', 2);
		//等级条件
		if(!empty($_POST['rating'])){
			$whereData['id']	=	array('id' => array('=', $_POST['rating']));
		}
		$whereData['orderby']	=	'sort,asc';
		$rating_list			=	$ratingM -> getList($whereData);
        foreach ($rating_list as &$value){
            $value['time_start'] = $value['time_start']?date('Y-m-d',$value['time_start']):'';
            $value['time_end'] =$value['time_end']?date('Y-m-d',$value['time_end']):'';
            if (trim($value['com_pic']) != '') {
                $value['com_pic']  =   checkpic($value['com_pic']);
            }
        }
        $this->render_json(0,'',['data'=>$rating_list]);
	}


    public function getCoupon_action()
    {
        $couponM				=	$this -> MODEL('coupon');
        $coupon					=	$couponM -> getList(array('id' => array('>', 0)));
        $this->render_json(0,'',$coupon);
    }

	/**
	 * 会员-猎头-增值套餐服务
	 * 编辑会员等级信息->保存数据
	 * 2019-06-03 hjy
	 */
	public function saveclass_action(){
	    
		$idStr					=	intval($_POST['id']);
		
		$ratingM				=	$this -> MODEL('rating');
		if($_FILES['file']['tmp_name']){
			$upArr    =  array(
				'file'  =>  $_FILES['file'],
				'dir'   =>  'compic'
			);

			$uploadM  =  $this->MODEL('upload');

			$pic      =  $uploadM->newUpload($upArr);
			if (!empty($pic['msg'])){
                $this->render_json(1,$pic['msg']);
			}elseif (!empty($pic['picurl'])){

				$pictures 	=  	$pic['picurl'];
			}

		}
		if(isset($pictures)){

			$_POST['com_pic']	=	$pictures;

		}else{

			unset($_POST['com_pic']);
		}

		$postData				=	$_POST;
		
	    if(empty($idStr)){
	        
	        $res				=	$ratingM -> addRating($postData);
	    }else{
	        $res				=	$ratingM -> upRating($idStr, $postData);
        }

        if ($res['errcode'] == 9 ){
            $this->admin_json(0,$res['msg']);
        }else{
            $this->render_json(1,$res['msg']);
        }

	}
	
	
	/**
	 * @desc   会员-猎头-增值套餐服务
	 * @desc   删除会员等级信息
	 * 2019-06-03 hjy
	 */
	public function delrating_action(){
        if(is_array($_POST['del'])){
            $id									=	pylode(',', $_POST['del']);
        }else{
            $id									=	$_POST['del'];
        }
        if(empty($id)){
            $this->render_json(1,'请选择您要删除的套餐！');
        }
		$ratingM				=	$this -> MODEL('rating');
		//删除等级数据
		$res					=	$ratingM -> delRating($id, array('category'=>'2'));
        if ($res['errcode'] ==9){
            $this->render_json(0,$res['msg']);
        }else{
            $this->render_json(1,$res['msg']);
        }
    }
	
	/**
	 * @desc 会员-猎头-增值套餐服务 - 增值服务
	 * 2019-06-03 hjy
	 */
	public function server_action(){
        $ltM					=	$this -> MODEL('lietou');
        $list					=	$ltM -> getLtserviceList(array('id' => array('>', 0)));
        $this->render_json(0,'',['data'=>$list]);
	}

	/**
	 * @desc 会员-猎头-增值套餐服务
	 * @desc 增值服务 -> 删除增值类型
	 * 2019-06-03 hjy
	 */
	function dels_action(){
	    
		if(is_array($_POST['id'])){
		    $id					=	pylode(',', $_POST['id']);
		}else{
		    $id					=	$_POST['id'];
		}
        if (!$id){
            $this->render_json('1','参数错误.');
        }
		$ltM					=	$this -> MODEL('lietou');
		$nid					=	$ltM -> delLtservice(array('id' => array('in', $id)));
		$ltM -> delLtservicedetail(array('type' => array('=', $id)));
		if(!empty($nid)){
            $this->render_json('0','增值服务包删除(ID:'.$id.')成功！');
		}else{
            $this->render_json('0','删除失败！');
        }
	}

	/**
	 * 会员-猎头-增值套餐服务
	 * 增值服务 -> 下架此服务
	 * 2019-06-03 hjy
	 */
	public function opera_action(){
		$dispStr				=	intval($_POST['display']);
		$idStr					=	intval($_POST['id']);
		if(empty($dispStr) || empty($idStr)){
            $this->render_json('1','参数错误,请重试');
		}
		$ltM					=	$this -> MODEL('lietou');
		$nid					=	$ltM -> upLtservice(array('id' => array('=', $idStr)), array('display' => $dispStr));
        if($nid){
			$this->render_json(0,'操作成功');
		}else{
            $this->render_json(1,'操作失败');

		}
	}

	/**
	 * 会员-猎头-增值套餐服务
	 * 增值服务 -> 服务列表详情
	 * 2019-06-03 hjy
	 */
	public function list_action(){
		$ltM					=	$this -> MODEL('lietou');
		$idStr					=	intval($_POST['id']);
        $list		=	array();
		if(!empty($idStr)){
			$list				=	$ltM -> getLtservicedetailList(array('type' => array('=', $idStr), 'orderby' => 'id,asc'));
		}
        $this->render_json('0','',$list);
	}

	/**
	 * 会员-猎头-增值套餐服务
	 * 增值服务 -> 删除增值服务套餐
	 * 2019-06-03 hjy
	 */
	public function delt_action(){

        if(is_array($_POST['del'])){
            $id									=	pylode(',', $_POST['del']);
        }else{
            $id									=	$_POST['del'];
        }
        if(empty($id)){
            $this->render_json(1,'请选择您要删除的套餐！');
        }
		$ltM					=	$this -> MODEL('lietou');
		$nid					=	$ltM -> delLtservicedetail(array('id' => array('in', $id)));
		if(!empty($nid)){
            $this->render_json(0,'套餐删除(ID:'.$id.')成功！');
        }else{
            $this->render_json(1,'删除失败！');
        }
	}

	/**
	 * 会员-猎头-增值套餐服务
	 * 增值服务 -> 保存设置的增值类型
	 * 2019-06-03 hjy
	 */
	public function save_action(){

		$name					=	trim($_POST['name']);
		$ltM					=	$this -> MODEL('lietou');
		$row					=	$ltM -> getLtserviceList(array('name' => array('=', $name)));
		$postData				=	$_POST;
		if(!empty($row)){
            $this->render_json(1,'增值包名称已存在！');
		}
        $nid 				=	$ltM -> addLtservice($postData);
        $name				=	'猎头增值包（ID：'.$nid.'）添加';
		if($nid){
            $this->render_json(0,$name.'成功！请在增值包中添加套餐！');
		}else{
            $this->render_json(0,$name.'失败！');
        }
	}

	/**
	 * 会员-猎头-增值套餐服务
	 * 增值服务 -> 保存增值包数据
	 * 2019-06-03 hjy
	 */
	public function saves_action(){
		$type						=	$_POST['type'];
        if (!$type){
            $this->render_json('1','请选择增值类型.');
        }
        $tid = $_POST['tid'];
        $ltM					=	$this -> MODEL('lietou');
        if(!$tid){
 			$nid				=	$ltM -> addLtservicedetail($_POST);
			$name				=	'套餐（ID：'.$nid.'）添加';
		}else{
            $nid				=	$ltM -> upLtservicedetail(array('id' => array('=', $tid)), $_POST);
			$name				=	'套餐（ID：'.$tid.'）更新';
		}
		if($nid){
            $this->render_json('0',$name.'成功！');
		}else{
            $this->render_json('0',$name.'失败！');
        }
	}

}

?>