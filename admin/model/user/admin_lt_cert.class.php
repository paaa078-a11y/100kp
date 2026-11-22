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
class admin_lt_cert_controller extends adminCommon{


	/**
	 * 会员-猎头-猎头认证管理
	 * 2019-06-01 hjy
	 */
	public function index_action(){

		//获取搜索
		$certWhereData['type']					=	4;
		//审核状态条件
		if($_POST['status']|| $_POST['status'] == '0' ){
			$certWhereData['status']			=	array('=', $_POST['status']);
		}
		//关键词条件
		$keywordStr								=	trim($_POST['keyword']);
		$ltM									=	$this -> MODEL('lietou');
		$comids									=	array();
		if(!empty($keywordStr)){		
			$lt									=	$ltM -> getList(array('realname' => array('like', $keywordStr)),array('field' => '`uid`,`realname`'));
			if(!empty($lt)){
				foreach($lt as $val){
					$comids[] 					=	$val['uid'];
				}				
			}else{
				$comids							=	array(0);
			}
		}
		if(!empty($comids)){
			$certWhereData['uid']				=	array('in', pylode(',', $comids));
		}

		//申请时间条件
		$endStr									=	intval($_POST['end']);
		if(!empty($endStr)){
			if($endStr == 1){
				$certWhereData['ctime']			=	array('>=', strtotime(date("Y-m-d")));
			}else{
				$certWhereData['ctime']			=	array('>=', strtotime('-'.$endStr.'day'));
			}
		}
        //分页数大于0的情况下 执行列表查询
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM	=	$this  -> MODEL('page');
        $pages	=	$pageM -> adminPageList('company_cert',$certWhereData,$page,array('limit' => $pageSize));

        if(!$pages['total']){
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
        //limit order 只有在列表查询时才需要
        if(!empty($_POST['order']) && !empty($_POST['t'])){
            $certWhereData['orderby']		=	$_POST['t'].','.$_POST['order'];
        }else{
            $certWhereData['orderby']		=	array('status,asc', 'id,desc');
        }
        $certWhereData['limit']				=	$pages['limit'];
        $companyM								=	$this -> MODEL('company');
        $List								=	$companyM -> getCertList($certWhereData, array('utype'=>'ltcert'));
        foreach ($List as &$v){
            $v['ctime'] = $v['ctime'] ?date('Y-m-d H:i:s',$v['ctime']):'';
        }
        $this->render_json(0,'暂无数据',['data'=>$List,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);

	}

	/**
	 * 会员-猎头-猎头认证管理
	 * 审核认证 -> 保存数据
	 */
	public function status_action(){
	    
 		$ltUid									=	trim($_POST['uid']);
		$ltStatus								=	intval($_POST['status']);
		if(empty($ltUid)){
            $this->render_json(1,'非法操作！');
		}

		//查询数据是否存在
		$ltM									=	$this -> MODEL('lietou');
		$ltlist									=	$ltM -> getList(array('uid' => array('in', $ltUid)), array('field' => '`email`, `uid`, `realname`, `rzid`'));
		if(empty($ltlist)){
            $this->render_json(1,'数据错误');
        }

		if($ltStatus != 1){
			$cert_status						=	0;
		}else{
			$cert_status						=	1;
		}

		$notice 								=	$this -> MODEL('notice');
		$sysmsgM								=	$this -> MODEL('sysmsg');
		$inteM									=	$this -> MODEL('integral');
		$companyM								=	$this -> MODEL('company');
		
		/* 消息前缀 */		
		$tagName  								=	'职业资格';
		
		foreach($ltlist as $v){
			
			 $uids[]  =  $v['uid'];
						
			/* 处理审核信息 */
			if ($_POST['status'] == 2){
				
				$statusInfo  =  $tagName.':'.$v['name'].'审核未通过';
				
				if($_POST['statusbody']){
					
					$statusInfo  .=  ' , 原因：'.$_POST['statusbody'];
					
				}
				
				$msg[$v['uid']]  =  $statusInfo;
				
			}elseif($_POST['status'] == 1){
				
				$msg[$v['uid']]  =  $tagName.':'.$v['name'].'已审核通过';
				
			}
		}
		//发送系统通知
		$sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>3, 'content'=>$msg));
					
		foreach($ltlist as $v){
			$rzid								=	"YLT".sprintf("%08d", $v['uid']);
			//修改lietou信息
			$ltM -> upInfo(array('uid' => array('=', $v['uid'])),array('yyzz_status' => $cert_status, 'rzid' => $rzid));

			//记录系统日志
			if($ltStatus == 1){
				$certinfo						=	'职业资格审核通过！';
			}elseif($ltStatus == 2){
				$certinfo						=	'职业资格审核未通过！';
			}else{
				$certinfo						=	'职业资格待审核！';
			}	

			//发送email
			if(!empty($v['email'])){
				$notice -> sendEmailType(array(
						"uid"		=>	$v['uid'],
						"name"		=>	$v['realname'],
						"email"		=>	$v['email'],
						"certinfo"	=>	$certinfo,
						"comname"	=>	$v['realname'],
						"type"		=>	"comcert"
					)
				);
			}
			//审核通过 修改integral_ltcert
			if(empty($v['rzid']) && $ltStatus == 1){
				$inteM -> invtalCheck($v['uid'], 3,'integral_ltcert', '猎头执照认证',21);
			}
		}
		//修改认证表中数据
		$upData				=	array(
			'status'		=>	$ltStatus,
			'statusbody'	=>	trim($_POST['statusbody'])
		);
		$id										=	$companyM -> upCertInfo(array('uid' => array('in', $ltUid), 'type' => array('=', 4)), $upData,array('utype'=>'admin'));
		if($id){
            $this->admin_json(0,'猎头认证(UID:'.$_POST['status'].')设置成功！');
		}else{
            $this->admin_json(1,'设置失败！');
        }
	}

	/**
	 * 会员-猎头-猎头认证管理
	 * 删除认证
	 * 2019-06-01 hjy
	 */
	public function del_action(){
	    
	    $companyM      =   $this->MODEL('company');
	    
	    if (is_array($_POST['del'])) {
	        
	        $linkid    =   $_POST['del'];
	        
	    } else {
	        $linkid    =   $_POST['id'];
	    }
        if (!$linkid){
            $this->render_json(1,'参数错误');
        }
	    $ltM           =	$this -> MODEL('lietou');
	    
	    $ltM -> upInfo(array('uid' => array('in', pylode(',', $linkid))), array('yyzz_status' => 0));
	    
	    $err    =   $companyM -> delCert($linkid, array('type'=>'4'));
		if ($err['errcode'] == 9){
            $this->render_json(0,$err['msg']);
        }else{
            $this->render_json(1,$err['msg']);
        }

	}

	/**
	 * 会员-猎头-猎头认证管理
	 * 数据统计
	 * 2019-06-01 hjy
	 */
	public function ltcertNum_action(){
		$MsgNum 	=	$this -> MODEL('msgNum');
 		$arr =  $MsgNum -> ltcertNumV1();
        $this->render_json(0,'',$arr);
	}
}

?>