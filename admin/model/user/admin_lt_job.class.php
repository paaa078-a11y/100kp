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
class admin_lt_job_controller extends adminCommon{


    public function getExp_action(){
        $ltar			=	array();
        //获取相关参数
        $cacheArr			=	$this -> MODEL('cache') -> GetCache(array('lt', 'ltjob', 'city'));
        $ltdata = $cacheArr['ltdata'];
        $ltclass  =  $cacheArr['ltclass_name'];
        //获取搜索
        foreach($ltdata['lt_exp'] as $k => $v){
            $ltar[$v]	=	$ltclass[$v];
        }
        $this->render_json('0','',$ltar);
    }

	/**
	 * 会员-猎头-猎头职位管理
	 * 2019-05-31 hjy
	 */
	public function index_action(){
        
		//获取搜索
		$memberWhereData	= 	$ltJobWhereData	= array();

		$keywordStr								=	trim($_POST['keyword']);
		$ltnameStr 								=	intval($_POST['ltnames']);

		//如果有用户相关的搜索条件,则先获取用户的id
		if(!empty($keywordStr) && $ltnameStr == 1){
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
			$ltJobWhereData['uid']				=	array('in', pylode(',', $memberUid));
		}

		//职位名称条件
		if(!empty($keywordStr) && $ltnameStr == 2){
			$ltJobWhereData['job_name']			=	array('like', $keywordStr);
		}
		//公司名称条件
		if(!empty($keywordStr) && $ltnameStr == 3){
			$ltJobWhereData['com_name']			=	array('like', $keywordStr);
		}
		//工作经验条件
		if(!empty($_POST['ltexp'])){
			$ltJobWhereData['exp']				=	array('=', $_POST['ltexp']);
		}
		if(!empty($_POST['zpstatus'])){
			$zpstatus       =   intval($_POST['zpstatus']);
			$ltJobWhereData['zp_status']     =   $zpstatus == 2 ? 0 : $zpstatus;
		}
		//审核状态条件
		if(!empty($_POST['status'])){
			if($_POST['status']=="1"){
				$ltJobWhereData['status']		=	1;
        		$ltJobWhereData['r_status']		=	1;
			}elseif($_POST['status']=="2"){
				$ltJobWhereData['r_status']		=	2;
			}elseif($_POST['status']=="3"){
				$ltJobWhereData['status']		  =	3;
			}elseif($_POST['status']=="4"){
				$ltJobWhereData['status']		=	0;
			}
		}
		//推荐状态条件
		if(!empty($_POST['rec'])){
			if($_POST['rec']=='2'){
				$ltJobWhereData['rec']			=	array('=', 0);
			}else{
				$ltJobWhereData['rec']			=	array('=', $_POST['rec']);
			}
		}
        //分页数大于0的情况下 执行列表查询
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM	=	$this  -> MODEL('page');
        $pages	=	$pageM -> adminPageList('lt_job',$ltJobWhereData,$page,array('limit' => $pageSize));
        if(!$pages['total']){
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }

		$ltjobM									=	$this -> MODEL('lietoujob');
        //limit order 只有在列表查询时才需要
        if(!empty($_POST['order']) && !empty($_POST['t'])){
            $ltJobWhereData['orderby']		=	$_POST['t'].','.$_POST['order'];
        }else{
            $ltJobWhereData['orderby']		=	array('r_status,asc','status,asc', 'id,desc');
        }
        $ltJobWhereData['limit']			=	$pages['limit'];
        $List								=	$ltjobM -> getList($ltJobWhereData);
		if(empty($List)) {
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }

        $ltuid								=	array();
        foreach($List as $lv){
            $ltuid[] 						=	$lv['uid'];
        }
        $bcWhereData						=	array('uid' => array('in', pylode(',',$ltuid)));
        //补充用户相关的信息
        $memberField						=	'`uid`, `username`';
        $memberList							=	$memberM -> getList($bcWhereData, array('field' => $memberField));
        $memberListIndex					=	array();
        if(!empty($memberList)){
            foreach ($memberList as $memberV) {
                $memberListIndex[$memberV['uid']]	=	$memberV;
            }
        }
        //补充进相关信息
        $cacheArr			=	$this -> MODEL('cache') -> GetCache(array('lt', 'ltjob', 'city'));
        foreach($List as $Lk => $Lv){
            if(isset($memberListIndex[$Lv['uid']])){
                $List[$Lk]					=	array_merge($List[$Lk], $memberListIndex[$Lv['uid']]);
            }
            $List[$Lk]['jobone']			=	$cacheArr['ltjob_name'][$Lv['jobone']];
            $List[$Lk]['exp']				=	$cacheArr['ltclass_name'][$Lv['exp']];
            $List[$Lk]['cityid']			=	$cacheArr['city_name'][$Lv['cityid']];
            $joburl = '';
            if($Lv['usertype'] == 3){
                $joburl = Url('lietou', array('c' => 'jobshow', 'id' => $Lv['id']));
            } else {
                $joburl = Url('lietou', array('c' => 'jobcomshow', 'id' => $Lv['id']));
            }
            $List[$Lk]['joburl'] = $joburl;
        }

        $this->render_json(0,'暂无数据',['data'=>$List,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);
	}
    
	/**
	 * 会员-猎头-猎头职位管理
	 * 审核职位 -> 保存数据
	 */
	public function status_action()
	{
	    $ltjobM   =	  $this -> MODEL('lietoujob');
	    
	    $statusData = array(
	        
	        'status'       =>  intval($_POST['status']),
	        'statusbody'   =>  trim($_POST['statusbody'])
	    );
	    $return = $ltjobM -> statusLtjob($_POST['pid'], $statusData);
        if ($return['errcode']==9){
            $this->admin_json(0,$return['msg']);
        }else{
            $this->render_json(1,$return['msg']);
        }
	}

	/**
	 * 会员-猎头-猎头职位管理
	 * 设置推荐
	 * 2019-05-31 hjy
	 */
	public function recommend_action(){

		$ltjobId					=	intval($_POST['id']);
        if (!$ltjobId){
            $this->render_json(1,'参数错误');
        }
		$ltjobRec					=	intval($_POST['rec']);
		$ltjobM						=	$this -> MODEL('lietoujob');
		$ltjobInfo					=	$ltjobM -> getInfo(array('id' => array('=', $ltjobId)), array('field' => '`job_name`, `uid`'));
		if(empty($ltjobInfo)){
            $this->render_json(1,'操作失败');
        }
		$upData		=	array();
		$msgContent	=	'';
		if($ltjobRec == 0){
			$upData['rec']			=	0;
			$upData['rec_time']		=	'';
			$msgContent	=	'管理员设置：取消推荐职位《'.$ltjobInfo['job_name'].'》';
		}elseif($ltjobRec == 1){
			$upData['rec']			=	1;
			$upData['rec_time']		=	time();
			$msgContent	=	'管理员设置：推荐职位《'.$ltjobInfo['job_name'].'》';
		}
		//修改职位状态
		if(!empty($upData)){
			$ltjobM -> upInfo(array('id' => array('=', $ltjobId)), $upData);
		}
		//记录系统日志
		if(!empty($msgContent)){
			$sysmsgM				=	$this -> MODEL('sysmsg');
			$sysmsgM -> addInfo(array('content' => $msgContent,'usertype'=>3,  'uid' => $ltjobInfo['uid']));
		}
        $this->render_json(0,'操作成功');
	}

	/**
	 * 会员-猎头-猎头职位管理
	 * 删除职位
	 * 2019-05-31 hjy
	 */
	public function del_action(){

        if (!$_POST['del']){
            $this->render_json(1,'参数错误');
        }
		$ltjobM		=	$this -> MODEL('lietoujob');
        $result		=	$ltjobM -> delLietouJob($_POST['del'],array('utype'=>'admin'));

        if ($result['errcode']==9){
            $this->admin_json(0,$result['msg']);
        }else{
            $this->render_json(1,$result['msg']);
        }
	}

	/**
	 * 会员-猎头-猎头职位管理
	 * 数据统计
	 * 2019-05-31 hjy
	 */
	public function ltjobNum_action(){
		$MsgNum	=	$this -> MODEL('msgNum');
        $arr = $MsgNum -> ltjobNumV1();
        $this->render_json(0,'操作成功',$arr);

    }
}
?>