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
class admin_lt_talent_controller extends adminCommon{

	/**
	 * 会员-猎头-简历库
	 * 简历列表
	 * 2019-06-04 hjy
	 */
	public function index_action(){

		$memberWhereData	= 	$ltTalWhereData	= array();
		$keywordStr								=	trim($_POST['keyword']);
		$nameStr 								=	intval($_POST['searchrname']);
		//如果有用户相关的搜索条件,则先获取用户的id
		if(!empty($keywordStr) && $nameStr == 1){
			$memberWhereData['username']		=	array('like', $keywordStr);
		}
		$memberUid								=	array();
		$memberM								=	$this -> MODEL('userinfo');
		if(!empty($memberWhereData)){
			$resWhere							=	$memberWhereData;
			$uidList							=	$memberM -> getList($resWhere, array('field' => '`uid`'));			
			if(!empty($uidList)){
				foreach($uidList as $uv){
					$memberUid[]				=	$uv['uid'];
				}
			}else{
				$memberUid						=	array(0);
			}
		}
		if(!empty($memberUid)){
			$ltTalWhereData['uid']				=	array('in', pylode(',', $memberUid));
		}
		//姓名条件
		if(!empty($keywordStr) && $nameStr == 2){
			$ltTalWhereData['name']				=	array('like', $keywordStr);
		}
		//意向职位条件
		if(!empty($keywordStr) && $nameStr == 3){
			$ltTalWhereData['jobname']			=	array('like', $keywordStr);
		}

        //分页数大于0的情况下 执行列表查询
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM	=	$this  -> MODEL('page');
        $pages	=	$pageM -> adminPageList('lt_talent',$ltTalWhereData,$page,array('limit' => $pageSize));
        if(!$pages['total']){
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }

		//分页数大于0的情况下 执行列表查询
		$talentM								=	$this -> MODEL('talent');
        //limit order 只有在列表查询时才需要
        if(!empty($_POST['order']) && !empty($_POST['t'])){
            if($_POST['t'] == 'time'){
                $ltTalWhereData['orderby']	=	'status_time,'.$_POST['order'];
            }else{
                $ltTalWhereData['orderby']	=	$_POST['t'].','.$_POST['order'];
            }
        }else{
            $ltTalWhereData['orderby']		=	'id,desc';
        }
        $ltTalWhereData['limit']			=	$pages['limit'];
        $List								=	$talentM -> getList($ltTalWhereData,array('field'=>'`id`,`uid`,`name`,`edu`,`exp`,`jobname`,`maxsalary`,`minsalary`,`provinceid`,`cityid`,`three_cityid`,`jobstatus`,`status`,`linktel`,`telstatus`'));
        $this->render_json(0,'',['data'=>$List,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);

	}
	/**
	 * 会员-猎头-简历库
	 * 简历预览
	 * 2019-06-04 hjy
	 */
	public function show_action(){
		$idStr									=	intval($_POST['id']);
		if(empty($idStr)){
            $this->render_json('1','参数错误,请重试');
        }
        $talentM							=	$this -> MODEL('talent');
        $info								=	$talentM -> getInfo(array('id' => $idStr));
        if (!$info){
            $this->render_json('1','不存在');
        }
        $this->render_json(0,'',['detail'=>$info]);
	}

	/**
	 * 会员-猎头-简历库
	 * 简历删除
	 * 2019-06-04 hjy
	 */
	public function del_action(){
		//批量删除
        if(empty($_POST['del'])){
            $this->render_json(1,'请选择您要删除的猎头简历库简历！');
        }
        $del = $_POST['del'];
		if(is_array($_POST['del'])){
			$del								=	pylode(',', $del);
		}
		$lttalM									=	$this -> MODEL('talent');
        $lttalM -> delTalent(array('id' => array('in', $del)));
        $this->admin_json('0','猎头简历库简历(ID:'.$del.')删除成功！');
	}
}
?>