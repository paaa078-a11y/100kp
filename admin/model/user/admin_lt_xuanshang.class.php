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
class admin_lt_xuanshang_controller extends adminCommon{


    /**
     * 返回配置数据
     */
    public function returnConfig_action(){
        $config = $this->config;
        $unit = '元';
        if ($config['lt_rebates_name']){
            $unit = $config['lt_rebates_name'];
        }
        $this->render_json(0,'',['unit'=>$unit]);
    }

    /**
	 * 会员-猎头-猎头悬赏
	 * 2019-06-03 hjy
	 */
	public function index_action(){
		$keywordStr									=	trim($_POST['keyword']);
		$typeStr 									=	intval($_POST['type']);

		$memberWhereData	= 	$rebatesWhereData	=	array();
		$memberUid									=	array();
		$memberM									=	$this -> MODEL('userinfo');
		if(!empty($keywordStr) && !empty($typeStr)){
			$memberWhereData['username']			=	array('like', $keywordStr);
			$uidList								=	$memberM -> getList($memberWhereData, array('field' => '`uid`'));			
			if(!empty($uidList)){
				foreach($uidList as $uv){
					$memberUid[]					=	$uv['uid'];
				}
			}else{
				$memberUid							=	array(0);
			}
			$uidStr									=	pylode(',', $memberUid);
			if($typeStr == 1){
				$rebatesWhereData['uid']			=	array('in', $uidStr);
			}
			if($typeStr == 4){
				$rebatesWhereData['job_uid']		=	array('in', $uidStr);
			}
		}
		$endStr										=	intval($_POST['end']);
		$replyStr									=	intval($_POST['reply']);
		$statusStr									=	intval($_POST['status']);
		//推荐时间条件
		if(!empty($endStr)){
			if($endStr == 1){
				$rebatesWhereData['datetime']		=	array('>=', strtotime(date("Y-m-d")));
			}else{
				$rebatesWhereData['datetime']		=	array('>=', strtotime('-'.$endStr.'day'));
			}
		}
		//回复时间条件
		if(!empty($replyStr)){
			if($replyStr == 1){
				$rebatesWhereData['reply_time']		=	array('>=', strtotime(date("Y-m-d")));
			}else{
				$rebatesWhereData['reply_time']		=	array('>=', strtotime('-'.$replyStr.'day'));
			}
		}
		//信息状态条件
        if($_POST['look']){
			$rebatesWhereData['status']				=	array('=', $_POST['look']);
        }
		//状态条件
		if($statusStr == 1){
			$rebatesWhereData['edate']				=	array('>', time());
		}elseif ($statusStr == 2) {
			$rebatesWhereData['edate']				=	array('<', time());
		}elseif ($statusStr == 3) {
			$rebatesWhereData['status']				=	array('=', 3);
		}elseif ($statusStr == 4) {
			$rebatesWhereData['status']				=	array('=', 0);
		}

        //分页数大于0的情况下 执行列表查询
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM	=	$this  -> MODEL('page');
        $pages	=	$pageM -> adminPageList('rebates',$rebatesWhereData,$page,array('limit' => $pageSize));
        if(!$pages['total']){
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
        //分页数大于0的情况下 执行列表查询
		$ltM=	$this -> MODEL('lietou');
        //limit order 只有在列表查询时才需要
        if(!empty($_POST['order']) && !empty($_POST['t'])){
            $rebatesWhereData['orderby']		=	$_POST['t'].','.$_POST['order'];
        }else{
            $rebatesWhereData['orderby']		=	array('status,asc', 'id,desc');
        }
        $rebatesWhereData['limit']				=	$pages['limit'];
        $List									=	$ltM -> getRebatesList($rebatesWhereData);

		if(empty($List)){
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
        $ltuid	=	$jobid						=	array();
        foreach($List as $lv){
            $ltuid[] 							=	$lv['uid'];
            $ltuid[] 							=	$lv['job_uid'];
            $jobid[]							=	$lv['job_id'];
        }
        $bcWhereData							=	array('uid' => array('in', pylode(',',$ltuid)));
        //补充用户相关的信息
        $memberField							=	'`uid`, `username`';
        $memberList								=	$memberM -> getList($bcWhereData, array('field' => $memberField));
        $memberListIndex						=	array();
        if(!empty($memberList)){
            foreach ($memberList as $memberV) {
                $memberListIndex[$memberV['uid']]	=	$memberV;
            }
        }
        //补充猎头职位相关的信息
        $ltjobField								=	'`id`, `com_name`, `job_name`, `rebates`';
        $ltjobM									=	$this -> MODEL('lietoujob');
        $ltjobList								=	$ltjobM -> getList(array('id' => array('in', pylode(',', $jobid))), array('field' => $ltjobField));
        $ltjobListIndex							=	array();
        if(!empty($ltjobList)){
            foreach ($ltjobList as $ltjobV) {
                $ltjobListIndex[$ltjobV['id']]	=	$ltjobV;
            }
        }
        //补充进相关信息
        foreach($List as $Lk => $Lv){
            if(isset($memberListIndex[$Lv['uid']])){
                $List[$Lk]['username']			=	$memberListIndex[$Lv['uid']]['username'];
            }
            if(isset($memberListIndex[$Lv['job_uid']])){
                $List[$Lk]['rname']				=	$memberListIndex[$Lv['uid']]['username'];
            }
            if(isset($ltjobListIndex[$Lv['job_id']])){
                $List[$Lk]['com_name'] 			=	$ltjobListIndex[$Lv['job_id']]['com_name'];
                $List[$Lk]['job_name'] 			=	$ltjobListIndex[$Lv['job_id']]['job_name'];
                $List[$Lk]['rebates']  			=	$ltjobListIndex[$Lv['job_id']]['rebates'];
            }
            $List[$Lk]['datetime'] = $Lv['datetime']?date('Y-m-d H:i:s',$Lv['datetime']):'';
            $List[$Lk]['reply_time'] = $Lv['reply_time']?date('Y-m-d H:i:s',$Lv['reply_time']):'';
        }
        $this->render_json(0,'',['data'=>$List,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);
    }

	/**
	 * 会员-猎头-查看悬赏
	 * 2019-06-03 hjy
	 */
	public function show_action(){
		$idStr	=	intval($_POST['id']);
		$resume	=	array();
		if(empty($idStr)) {
            $this->render_json('1','参数错误,请重试');
        }
        //获取悬赏信息
        $ltM	=	$this -> MODEL('lietou');
        $rebate	=	$ltM -> getRebatesInfo(array('id' => array('=', $idStr)));
        if(!empty($rebate)){
            //获取临时简历信息
            $resumeM	=	$this -> MODEL('resume');
            $resume		=	$resumeM -> getTempResumeInfo(array('rid' => array('=', $idStr)), array('scene' => 'detail'));
            if($resume['uname']	== $rebate['name']){
                $resume['telphone']	=	$rebate['phone'];
            }
        }
        $this->render_json('0','',['rebate'=>$rebate,'resume'=>$resume]);

	}

	/**
	 * 会员-猎头-猎头悬赏
	 * 删除悬赏
	 * 2019-06-03 hjy
	 */
	public function del_action(){

        if (!$_POST['del']){
            $this->render_json(1,'参数错误');
        }
        $id		=	$_POST['del'];

		$ltM		=	$this -> MODEL('lietou');
		$result		=	$ltM -> delRebates($id);

        if ($result['errcode']==9){
            $this->admin_json(0,$result['msg']);
        }else{
            $this->render_json(1,$result['msg']);
        }
	}

}
?>