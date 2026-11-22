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
class admin_height_user_controller extends adminCommon{


    public function ltar_action(){
        include PLUS_PATH."/user.cache.php";
        foreach($userdata['user_type'] as $v){
            $ltar[$v]=$userclass_name[$v];
        }
        $this->render_json(0,'',['data'=>$ltar]);

    }
    public function ltarry_action(){
        include PLUS_PATH."/user.cache.php";
        foreach($userdata['user_report'] as $v){
            $ltarry[$v]=$userclass_name[$v];

        }
        $this->render_json(0,'',['data'=>$ltarry]);
    }
	
	/**
	 * 会员-猎头-优质人才简历
	 * 2019-05-31 hjy
	 */
	public function index_action(){

		$memberWhereData	= 	$huWhereData	=	array();
		$huWhereData['height_status']		=	array('<>', 0);

		//工作性质条件
		if(!empty($_POST['searchtype'])){
			$huWhereData['type']			=	array('=', $_POST['searchtype']);
		}

		//到岗时间条件
		if(!empty($_POST['searchreport'])){
			$huWhereData['report']			=	array('=', $_POST['searchreport']);
		}

		$keywordStr							=	trim($_POST['keyword']);
		$snameStr							=	intval($_POST['searchrname']);

		//如果有用户相关的搜索条件,则先获取用户的id
		if(!empty($keywordStr) && $snameStr == 1){
			$memberWhereData['username']	=	array('like', $keywordStr);
		}
		$memberUid							=	array();
		$memberM							=	$this -> MODEL('userinfo');
		if(!empty($memberWhereData)){
			$resWhere						=	array_merge(array('usertype' => array('=', 1)), $memberWhereData);
			$uidList						=	$memberM -> getList($resWhere, array('field' => '`uid`'));			
			if(!empty($uidList)){
				foreach($uidList as $uv){
					$memberUid[]			=	$uv['uid'];
				}
			}else{
				$memberUid					=	array(0);
			}
		}
		if(!empty($memberUid)){
			$huWhereData['uid']				=	array('in', pylode(',', $memberUid));
		}

		//简历名称条件
		if(!empty($keywordStr) && $snameStr == 2){
			$huWhereData['name']			=	array('like', $keywordStr);
		}
		//审核状态
		if(!empty($_POST['status'])){
            if($_POST['status']==4){
                $huWhereData['r_status']	  =	  2;
            }else{
                $huWhereData['r_status']	  =	  1;
                $huWhereData['height_status'] =	$_POST['status'];
            }
		}
		//推荐状态条件
		if(!empty($_POST['rec'])){
			if($_POST['rec']=='2'){
				$huWhereData['rec']			=	array('=', 0);
			}else{
				$huWhereData['rec']			=	array('=', $_POST['rec']);
			}
		}
		//审核时间条件
		$verifyStr							=	intval($_POST['verify']);
		if(!empty($verifyStr)){
			if($verifyStr == 1){
				$huWhereData['status_time']	=	array('>=', strtotime(date("Y-m-d")));
			}else{
				$huWhereData['status_time']	=	array('>=', strtotime('-'.$verifyStr.'day'));
			}
		}
        //提取分页
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM									=	$this  -> MODEL('page');
		$pages									=	$pageM -> adminPageList('resume_expect', $huWhereData,$page,array('limit' => $pageSize));
		//分页数大于0的情况下 执行列表查询
        if(!$pages['total']){
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
		$resumeM								=	$this -> MODEL('resume');
        //limit order 只有在列表查询时才需要
        if(!empty($_POST['order']) && !empty($_POST['t'])){
            if($_POST['t'] == 'time'){
                $huWhereData['orderby']		=	'status_time,'.$_POST['order'];
            }else{
                $huWhereData['orderby']		=	$_POST['t'].','.$_POST['order'];
            }
        }else{
            $huWhereData['orderby']			=	array('height_status,asc', 'id,desc');
        }
        $huWhereData['limit']				=	$pages['limit'];
        $List								=	$resumeM -> getList($huWhereData, array('utype' => 'admin'));

        foreach ($List['list'] as &$value){
            $value['status_time'] = $value['status_time']?date('Y-m-d H:i:s',$value['status_time']):"";
        }
        $this->render_json(0,'',['data'=>$List['list'],'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);
	}

	/**
	 * 会员-猎头-优质人才简历
	 * 审核职位 -> 保存数据
	 * 2019-05-31 hjy
	 */
	public function status_action(){
		$reId									=	trim($_POST['pid']);
		if(empty($reId)){
            $this->render_json(1,'审核设置失败');
		}
		$resumeM								=	$this -> MODEL('resume');

		//查询数据是否存在
		$list	=	$resumeM -> getSimpleList(array('id' => array('in', $reId),'r_status'=>1), array('field' => '`id`,`uid`,`name`'));

		//修改简历参数
		$upData									=	array();
		$upData['height_status']				=	$_POST['status'];
		$upData['statusbody']					=	$_POST['statusbody'];
		$upData['status_time']					=	time();
		$id		=		$resumeM -> upInfo(array('id' => array('in', $reId),'r_status'=>1), array('eData'=>$upData));
		
		if(!$id){
            $this->render_json(1,'审核设置失败');
        }
        if($list){
                /* 消息前缀 */
            $tagName  				=	'优质简历';
            foreach($list as $v){

                $uids[]  =  $v['uid'];
                /* 处理审核信息 */
                if ($_POST['status'] == 3){

                    $statusInfo  =  $tagName.':<a href="resumetpl,'.$v['id'].'">'.$v['name'].'</a>审核未通过';

                    if($_POST['statusbody']){

                        $statusInfo  .=  ' , 原因：'.$_POST['statusbody'];

                    }
                    $msg[$v['uid']][]  =  $statusInfo;

                }elseif($_POST['status'] == 2){

                    $msg[$v['uid']][]  =  $tagName.':<a href="resumetpl,'.$v['id'].'">'.$v['name'].'</a>已审核通过';
                }
            }
            //发送系统通知
            $sysmsgM	=	$this->MODEL('sysmsg');

            $sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>1, 'content'=>$msg));
            //记录管理员日志
            $this -> MODEL('log') -> addAdminLog('优质人才(ID:'.$reId.')审核设置成功');
        }

        $resumewhere['id']      =     array('in',$reId);

        $resumenum              =     $resumeM->getExpectNum($resumewhere);
        if($resumenum>1){
            $resumetwhere['id']           =     array('in',$reId);
            $resumetwhere['r_status']     =     1;
            $resumetnum                   =     $resumeM->getExpectNum($resumetwhere);
            $resumewwhere['id']           =     array('in',$reId);
            $resumewwhere['r_status']     =     array('<>',1);
            $resumewnum             	 =     $resumeM->getExpectNum($resumewwhere);
            if($resumewnum>0){
                $msg='优质人才批量审核成功'.$resumetnum.'条，失败'.$resumewnum.'条原因:个人账户未审核！';
            }else{
                $msg='优质人才批量审核成功(ID:'.$reId.')';
            }
            $errorCode = 0;
        } else {
            $resumewwhere['id'] = array('in', $reId);
            $resumewwhere['r_status'] = array('<>', 1);
            $resumetnum = $resumeM->getExpectNum($resumewwhere);
            if ($resumetnum > 0) {
                $msg = '审核优质人才(ID:' . $reId . ')失败，原因:个人账户未审核！';
                $errorCode = 1;
            } else {
                $msg = '审核优质人才(ID:' . $reId . ')成功';
                $errorCode = 0;
            }
        }
        $this->render_json($errorCode,$msg);
	}
    function heightuserstatus_action()
    {
        $userinfoM  =  $this -> MODEL('userinfo');
        $post       =  array(
            'status'     =>  intval($_POST['r_status'])
        );
        $return        =   $userinfoM -> status(array('uid' =>$_POST['cuid'],'usertype'=>1),array('post'=>$post));
        
        if($_POST['r_status']==1){
            $resumeM  =  $this -> MODEL('resume');
            $postData = array(
                'height_status'  =>  2,
                'statusbody'     =>  $_POST['statusbody']
            );
            $resumeM -> statusResume($_POST['cid'],array('post'=>$postData));
        }
        if($return['errcode']==9){
            $this->render_json(0,'审核成功');
        }else{
            $this->render_json(1,'审核失败');
        }
    }

	/**
	 * 会员-猎头-优质人才简历
	 * 设置推荐
	 * 2019-06-01 hjy
	 */
	public function recommend_action(){

		$resumeId								=	intval($_POST['id']);
		$resumeRec								=	intval($_POST['rec']);
		$resumeM								=	$this -> MODEL('resume');
        if (!$resumeId){
            $this->render_json(1,'参数错误!');

        }
		//获取哦简历信息
		$resume	=	$resumeM -> getExpect(array('id' => $resumeId), array('field' => '`id`,`name`,`uid`'));
		if(empty($resume)){
            $this->render_json(1,'参数错误!');
        }

		//修改推荐状态
		$nid = $resumeM -> upInfo(array('id' => array('=', $resumeId)),array('eData'=>array(''.$_POST['type'].''=>$resumeRec)));
		//记录系统日志
		$sysmsgM								=	$this -> MODEL('sysmsg');
		if($nid && $resumeRec == 1){
			$sysmsgM -> addInfo(array('content' => '管理员设置：推荐优质简历<a href="resumetpl,'.$resume['id'].'">《'.$resume['name'].'》</a>','usertype'=>1,  'uid' => $resume['uid']));
		}elseif($nid && $resumeRec == 0){
			$sysmsgM -> addInfo(array('content' => '管理员设置：取消推荐优质简历<a href="resumetpl,'.$resume['id'].'">《'.$resume['name'].'》','usertype'=>1,  'uid' => $resume['uid']));
		}
		//记录管理员日志
		$this->MODEL('log')->addAdminLog('优质人才(ID:'.$_POST['id'].')推荐成功');
        if ($nid){
            $this->render_json(0,'操作成功');
        }else{
            $this->render_json(1,'操作失败!');
        }
	}

	/**
	 * 会员-猎头-优质人才简历
	 * 删除简历
	 * 2019-06-01 hjy
	 */
	public function del_action(){
		$del									=	$_POST['del'];
		if(empty($del)){
            $this->render_json(1,'请选择您要删除的优质人才！');
		}

		$resumeM								=	$this -> MODEL('resume');
		if(is_array($del)){
			$del								=	pylode(',', $del);
		}
		$list	=	$resumeM -> getSimpleList(array('id' => array('in', $del)), array('field' => '`id`,`uid`,`name`'));
		if(empty($list)){
            $this->render_json(1,'参数错误！');
        }
		//修改简历状态
		$resumeM -> upInfo(array('id' => array('in', $del)), array('eData'=>array('height_status' => 0)));
		//记录系统日志
		$sysmsgM								=	$this -> MODEL('sysmsg');
		foreach ($list as $v) {
			$sysmsgM -> addInfo(array('content' => '管理员设置：取消优质简历<a href="resumetpl,'.$v['id'].'">《'.$v['name'].'》</a>','usertype'=>1,  'uid' => $v['uid']));
		}

        $this->admin_json(0,'优质人才(ID:'.$del.')删除成功！');
    }

	/**
	 * 会员-猎头-优质人才简历
	 * 数据统计
	 * 2019-06-01 hjy
	 */
	function ltjobNum_action(){
		$MsgNum	=	$this -> MODEL('msgNum');
		$arr  =  $MsgNum -> gresumeNumV1();
        $this->render_json(0,'',$arr);
	}
}
?>