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
class admin_lt_pic_controller extends adminCommon{

	/**
	 * 会员-猎头-图片管理
	 * 2019-06-03 hjy
	 */
	public function index_action(){

		$ltWhereData['photo_big']				=	array('<>', '');
		$keywordStr							=	trim($_POST['keyword']);
		$typeStr							=	intval($_POST['type']);
		if(!empty($keywordStr)){
			if($typeStr == 2){
				$ltWhereData['uid']	=	array('=', $keywordStr);
			}elseif($typeStr == 3){
				$ltWhereData['realname']	=	array('like', $keywordStr);
			}else{
				$ltWhereData['com_name']	=	array('like', $keywordStr);
			}
		}

        if($_POST['status']|| $_POST['status'] == '0' ){
            $ltWhereData['photo_status']			=	array('=', intval($_POST['status']));
        }
		//提取分页
        //分页数大于0的情况下 执行列表查询
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM	=	$this  -> MODEL('page');
        $pages	=	$pageM -> adminPageList('lt_info',$ltWhereData,$page,array('limit' => $pageSize));

        if(!$pages['total']){
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
        //limit order 只有在列表查询时才需要
        $ltWhereData['orderby']				=	array('photo_status,desc','uid,desc');
        $ltWhereData['limit']				=	$pages['limit'];
        $ltM								=	$this -> MODEL('lietou');
        $List								=	$ltM -> getList($ltWhereData);

        $this->render_json(0,'',['data'=>$List,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);
	}
    /**
     * @desc 猎头LOGO审核
     */
    function status_action()
    {
        $lietouM   =   $this->MODEL('lietou');

        $post     				 =   	array(
            'photo_statusbody'   =>  	trim($_POST['statusbody']),
            'photo_status'       =>  	intval($_POST['status'])
		);
		$return   				=  		$lietouM -> statusLogo($_POST['sid'], array('post'=>$post));
        if ($return['errcode'] == 9){
            $this->admin_json(0,$return['msg']);
        }else{
            $this->admin_json(1,$return['msg']);
        }
    }
	/**
	 * 会员-猎头-图片管理
	 * 保存图片
	 * 2019-06-03 hjy
	 */
	public function uploadsave_action(){
		$_POST									=	$this -> post_trim($_POST);
		$id										=	intval($_POST['id']);

		if($_POST['type'] != 'photo'){
            $this->render_json(1,'参数错误！');
		}
		$ltM									=	$this -> MODEL('lietou');
		$whereData								=	array('uid' => array('=', $id));
		if(!$_FILES['file']['tmp_name']){
            $this->render_json('1','请选择图片！');
        }
        $upArr    =  array(
            'file'  =>  $_FILES['file'],
            'dir'   =>  'lietou',
            'watermark' => 0
        );
        $uploadM  =  $this->MODEL('upload');
        $pic      =  $uploadM->newUpload($upArr);

        if (!empty($pic['msg'])){
            $this->render_json('1',$pic['msg']);

        }elseif (!empty($pic['picurl'])){
            $pictures 	=  	$pic['picurl'];
        }

        $upData['photo_big']	=	$pictures;
		$nbid									=	$ltM -> upInfo($whereData, $upData);
		if(!empty($nbid)){
            $this->render_json('0','猎头头像(ID:'.$id.')修改成功！');
		}else{
            $this->render_json('1','修改失败！');
        }
	}
	/**
	 * 会员-猎头-图片管理
	 * 删除图片
	 * 2019-06-03 hjy
	 */
	public function del_action(){

		if(is_array($_POST['del'])){
			$id									=	pylode(',', $_POST['del']);
		}else{
			$id									=	$_POST['del'];
		}
		if(empty($id)){
            $this->render_json(1,'请选择您要删除的图片！');
		}
		$ltM									=	$this -> MODEL('lietou');
		//修改相应图片字段
		$delid									=	$ltM -> upInfo(array('uid' => array('in', $id)), array('photo_big' => ''));
		if(!empty($delid)){
            $this->render_json('0','猎头头像(ID:'.$id.')删除成功！');
		}else{
            $this->render_json('1','删除失败！');
        }

	}
}
?>