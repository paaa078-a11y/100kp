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
class admin_lt_member_controller extends adminCommon{


    public function getCache_action(){
        $ratingM				=	$this -> MODEL('rating');
        $whereData['category']	=	array('=', 2);
        $whereData['display']	=	array('=', 1);
        $whereData['orderby']	=	'sort,asc';
        $cusData['field']		=	'`id`,`name`';
        $rating					=	$ratingM -> getList($whereData, $cusData);
        $ratingarr				=	array();
        foreach($rating as $v){
            $ratingarr[$v['id']]=$v['name'];
        }
        $data['ratingarr'] = $ratingarr;
        $cacheM = $this->MODEL('cache');
        $domain = $cacheM->GetCache('domain');
        $data['dname'] = (object)$domain['Dname'];
        $this->render_json(0,'',$data);
    }

	/**
	 * 设置高级搜索功能
	 * 高级搜索参数
	 */
	public function set_search(){

		$ratingM				=	$this -> MODEL('rating');
		$whereData				=	array();
		$whereData['category']	=	array('=', 2);
		$whereData['display']	=	array('=', 1);
		$whereData['orderby']	=	'sort,asc';
		$cusData['field']		=	'`id`,`name`';
		$rating					=	$ratingM -> getList($whereData, $cusData);
		$ratingarr				=	array();
		if(!empty($rating)){
            foreach($rating as $v){
                $ratingarr[$v['id']]=$v['name'];
            }
		}
		$search_list[]	=	array(
			'param'	=>	'rec',
			'name'	=>	'推荐状态',
			'value'	=>	array(
				1	=>	'已推荐',
				2	=>	'未推荐'
			)
		);
		$search_list[]	=	array(
			'param'	=>	'status',
			'name'	=>	'审核状态',
			'value'	=>	array(
				1	=>	'已审核',
				3	=>	'未通过',
				2	=>	'已锁定',
				4	=>	'未审核'
			)
		);
		$search_list[]	=	array(
			'param'	=>	'rating',
			'name'	=>	'会员等级',
			'value'	=>	$ratingarr
		);
		$search_list[]	=	array(
			'param'	=>	'register',
			'name'	=>	'注册时间',
			'value'	=>	$this -> timeSection
		);
		$search_list[]	=	array(
			'param'	=>	'login',
			'name'	=>	'登录时间',
			'value'	=>	$this -> timeSection
		);

		$this->yunset('ratingarr', $ratingarr);
		$this->yunset('search_list', $search_list);
	}

	/**
	 * 日志搜索功能
	 * 搜索参数
	 */
	public function log_search(){
	
		$opera = array('10' => '职位', '2' => '财务', '3' => '下载简历', '5' => '收藏关注', '6' => '应聘委托', '7' => '基本信息', '8' => '修改密码', '11' => '修改账号', '13' => '认证绑定', '12' => '账号解绑', '16' => '图片', '17' => '积分兑换', '18' => '消息', '19' => '问答', '24' => '优惠券', '25' => '悬赏推荐', '26' => '浏览');
		$search_list[] = array('param' => 'operas', 'name' => '操作类型', 'value' => $opera);
		
		$parr = array('1' => '增加', '2' => '修改', '3' => '删除', '4' => '刷新');
		$search_list[] = array('param' => 'parrs', 'name' => '操作内容', 'value' => $parr);

	    $search_list[] = array('param' => 'end', 'name' => '操作时间', 'value' => $this -> timeSection);
		$this -> yunset('search_list', $search_list);
	}

	/**
	 * 会员-猎头-用户管理:全部猎头
	 */
	public function index_action(){
        
		$keywordStr								=	trim($_POST['keyword']);
		$typeStr 								=	intval($_POST['type']);
		$loginStr 								=	intval($_POST['login']);
		$regStr 								=	intval($_POST['register']);
		//如果有用户相关的搜索条件,则先获取用户的id
		$memberWhereData = $ltWhereData = array();
		if(!empty($keywordStr) && $typeStr == 1){
			$memberWhereData['username']		=	array('like', $keywordStr);
		}
		if(!empty($loginStr)){
			if($loginStr == 1){
				$memberWhereData['login_date']	=	array('>=', strtotime(date('Y-m-d')));
			}else{
				$memberWhereData['login_date']	=	array('>=', strtotime('-'.$loginStr.'day'));
			}
		}
		if(!empty($regStr)){
			if($regStr == 1){
				$memberWhereData['reg_date']	=	array('>=', strtotime(date('Y-m-d')));
			}else{
				$memberWhereData['reg_date']	=	array('>=', strtotime('-'.$regStr.'day'));
			}
		}
		if(!empty($_POST['status'])){
			if ($_POST['status']=='4'){
			    $ltWhereData['r_status']		=	array('=', 0);
			}else{
			    $ltWhereData['r_status']		=	array('=', $_POST['status']);
			}
		}
		$memberUid								=	array();

		$memberM								=	$this -> MODEL('userinfo');

		$CompanyM								=	$this -> MODEL('company');
		
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
		//公司名称条件
		if(!empty($keywordStr) && $typeStr == 2){
			$ltWhereData['com_name']			=	array('like', $keywordStr);
		}
		//email条件
		if(!empty($keywordStr) && $typeStr == 3){
			$ltWhereData['email']				=	array('like', $keywordStr);
		}
		//moblie条件
		if(!empty($keywordStr) && $typeStr == 4){
			$ltWhereData['moblie']				=	array('like', $keywordStr);
		}
		$ltM									=	$this -> MODEL('lietou');
		//会员等级条件
		$expireduids 							=	array();
		if(!empty($_POST['rating'])){
			$ltStaWbereData						=	array('rating' => array('=', $_POST['rating']));
			$list								=	$ltM -> getLtStatisList($ltStaWbereData, array('field' => '`uid`'));
			if(!empty($list)){
				foreach($list as $val){
					$expireduids[] = $val['uid'];
				}
			}else{
				$expireduids					=	array(0);
			}
		}
		$tmpUidArr = array_unique(array_merge($memberUid, $expireduids));
		if(!empty($tmpUidArr)){
			$ltWhereData['uid']					=	array('in', pylode(',', $tmpUidArr));
		}
		//推荐状态
		if ($_POST['rec'] == 1){
			$ltWhereData['rec']					=	array('=', 1);
		}elseif ($_POST['rec']=='2'){
			$ltWhereData['rec']					=	array('=', 0);
		}
		//提取分页
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM	=	$this  -> MODEL('page');
        $pages	=	$pageM -> adminPageList('lt_info',$ltWhereData,$page,array('limit' => $pageSize));
        if(!$pages['total']){
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
        //limit order 只有在列表查询时才需要
        if(!empty($_POST['order']) && !empty($_POST['t'])){
            $ltWhereData['orderby']			=	$_POST['t'].','.$_POST['order'];
        }else{
            $ltWhereData['orderby']			=	'uid';
        }
        $ltWhereData['limit']				=	$pages['limit'];
        $List								=	$ltM -> getList($ltWhereData);
		
		if(!$List) {
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
        $ltuid								=	array();
        foreach($List as $lv){
            $ltuid[] 						=	$lv['uid'];
        }
        $bcWhereData						=	array('uid' => array('in', pylode(',',$ltuid)));
        //补充用户相关的信息
        $memberField						=	'`uid`, `login_date`, `reg_date`, `username`, `status`, `login_ip`, `usertype`,`wxid`,`unionid`';
        $memberList							=	$memberM -> getList($bcWhereData, array('field' => $memberField));
        $memberListIndex					=	array();
        if(!empty($memberList)){
            foreach ($memberList as $memberV) {
                $memberListIndex[$memberV['uid']]	=	$memberV;
            }
        }
        //补充会员等级
        $statisField						=	'`uid`, `rating_name`, `rating`, `vip_etime`';
        $statisData							=	array('field' => $statisField, 'index' => 'uid');
        $statisList							=	$ltM -> getLtStatisList($bcWhereData, $statisData);

        $certWhereData						=	array('type' => 4);

        $certField							=	'`uid`,`check`,`status`';

        $certdata							=	array('field'=>$certField);

        $certList							=	$CompanyM->getCertList($certWhereData,$certdata);

        //补充进相关信息
        foreach($List as $Lk => $Lv){

            if(isset($memberListIndex[$Lv['uid']])){
                $List[$Lk]					=	array_merge($List[$Lk], $memberListIndex[$Lv['uid']]);
            }
            if(isset($statisList[$Lv['uid']])){
                $List[$Lk]					=	array_merge($List[$Lk], $statisList[$Lv['uid']]);
            }
            foreach($certList  as $val){
                if($Lv['uid']	==	$val['uid']){
                    $List[$Lk]['check']		        =	$val['check'];
                    $List[$Lk]['status_n']		    =	$val['status'];
                }
            }
            $List[$Lk]['vip_etime_ymd']		=	$List[$Lk]['vip_etime']?date('Y-m-d H:i',$List[$Lk]['vip_etime']):'';
            $List[$Lk]['reg_date_ymd']		=	$List[$Lk]['reg_date']?date('Y-m-d H:i',$List[$Lk]['reg_date']):'';
            $List[$Lk]['login_date_ymd']	=	$List[$Lk]['login_date']?date('Y-m-d H:i',$List[$Lk]['login_date']):'';

        }
        $this->render_json(0,'',['data'=>$List,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);


	}

	/**
	 * 会员-猎头-用户管理:全部猎头->删除猎头
	 */
	public function del_action(){
	    

        if(!$_POST['del']){
            $this->render_json(1,'参数错误');
        }
        $userinfoM  =  $this -> MODEL('userinfo');

        $return     =  $userinfoM -> delInfo($_POST['del'], 3);

        if ($return['errcode']==9){
            $this->admin_json(0,$return['msg']);
        }else{
            $this->render_json(1,$return['msg']);
        }
	}

	/**
	 * 会员-猎头-用户管理:全部猎头->锁定猎头
	 * 锁定信息
	 */
	public function lockinfo_action(){
		$memberM	=	$this -> MODEL('userinfo');
		$userinfo	=	$memberM -> getInfo(array('uid'=> $_POST['uid']), array('field' => 'lock_info'));
		echo empty($userinfo['lock_info']) ? '' : $userinfo['lock_info'];
		die;
	}

	/**
	 * 会员-猎头-用户管理:全部猎头->审核猎头
	 * 审核猎头
	 */
	public function status_action(){
	    
	    $userinfoM  =  $this -> MODEL('userinfo');
	    
	    $post       =  array(
	        'status'     =>  intval($_POST['status']),
	        'lock_info'  =>  trim($_POST['statusbody'])
	    );
	    
	    $uids       =  @explode(',', $_POST['uid']);
	    
	    $return     =  $userinfoM -> status(array('uid' => array('in', pylode(',', $uids)),'usertype'=>3),array('post'=>$post));
        if ($return['errcode']==9){
            $this->admin_json(0,$return['msg']);
        }else{
            $this->render_json(1,$return['msg']);
        }
	}

    /**
     *  返回用户登录信息
     */
    public function getLoginInfo_action(){
        $ltId 						= 	intval($_POST['id']);
        if (!$ltId){
            $this->render_json('1','参数错误,请重试!');
        }
        $memberM				=	$this -> MODEL('userinfo');
        $com_info 				=	$memberM -> getInfo(array('uid'=> $ltId));
        if ($com_info){
            $com_info['login_date_ymd'] = $com_info['login_date']?date('Y-m-d',$com_info['login_date']):'';
            $com_info['reg_date_ymd'] = $com_info['reg_date']?date('Y-m-d',$com_info['reg_date']):'';
        }
        $this->render_json('0','',$com_info);
    }
    /**
     *  返回工作经验 和目前头衔
     */
    public function workAndtitle_action(){
        $CacheList					=	$this->MODEL('cache')->GetCache(array('lt', 'city', 'ltjob', 'lthy'));
        $this->render_json('0','',['ltdata'=>$CacheList['ltdata'],'ltclass_name'=>$CacheList['ltclass_name']]);
    }
    public function ratingList_action(){
        $ratingM				=	$this -> MODEL('rating');
        $whereData				=	array();
        $whereData['category']	=	array('=', 2);
        $whereData['orderby']	=	'sort,asc';
        $rating_list			=	$ratingM -> getList($whereData);

        $this->render_json('0','',$rating_list);
    }

    public  function  getComInfo_action(){
        $ltId 						= 	intval($_POST['id']);
        //编辑页面信息
        if(empty($ltId)){
            $this->render_json('1','参数错误,请重试!');
        }
        $memberM				=	$this -> MODEL('userinfo');
        $com_info 				=	$memberM -> getInfo(array('uid'=> $ltId));
        $this->render_json('0','',$com_info);

    }
    public function ltInfo_action(){

        $ltId 						= 	intval($_POST['id']);
        //编辑页面信息
        if(empty($ltId)){
            $this->render_json('1','参数错误,请重试!');
        }
        //猎头统计信息
        $ltM					=	$this -> MODEL('lietou');
        $statis					=	$ltM -> getLtStatisInfo(array('uid' => array('=', $ltId)));
        //模板赋值
        $this->render_json(0,'',$statis);

    }
	/**
	 * 会员-猎头-用户管理:全部猎头->修改猎头
	 * 修改猎头信息
	 */
	public function edit_action(){

		//读取相关配置缓存信息
	    $CacheList					=	$this->MODEL('cache')->GetCache(array('lt', 'city', 'ltjob', 'lthy'));
		$ltId 						= 	intval($_POST['id']);
		//编辑页面信息
		if(empty($ltId)){
            $this->render_json('1','参数错误,请重试!');
        }
        //获取猎头信息
        $ltM					=	$this -> MODEL('lietou');
        $row 					=	$ltM -> getInfo(array('uid' => array('=', $ltId)));
        if(!empty($row['job'])){
            $job				=	@explode(',',$row['job']);
            foreach ($job as $v){
                $jobname[$v] 		=	$CacheList['ltjob_name'][$v];
            }
        }
        $row['jobnameArr'] = $jobname;
        if(!empty($row['hy'])){
            $hy					=	@explode(',', $row['hy']);
            foreach ($hy as $v){
                $hyname[$v] 		=	$CacheList['lthy_name'][$v];
            }
        }
        $row['hynameArr'] = $hyname;
        $row['cityArrModel'] = array();
        if ($row['provinceid']){
            $row['cityArrModel'][] = $row['provinceid'].' ';
        }
        if ($row['cityid']){
            $row['cityArrModel'][] = $row['cityid'].' ';
        }
        if ($row['three_cityid']){
            $row['cityArrModel'][] = $row['three_cityid'].' ';
        }

        $this->render_json('0','',$row);


	}

    public function save_action(){

        //保存修改的猎头信息
        $_POST = $this->post_trim($_POST);
        $mData = array(
            'email' => $_POST['email'],
            'moblie' => $_POST['moblie'],
        );
        $ltData = array(
            'realname' => $_POST['realname'],
            'com_name' => $_POST['com_name'],
            'email' => $_POST['email'],
            'moblie' => $_POST['moblie'],
            'phone' => $_POST['phone'],
            'provinceid' => $_POST['provinceid'],
            'cityid' => $_POST['cityid'],
            'three_cityid' => $_POST['threecityid'],
            'exp' => $_POST['exp'],
            'title' => $_POST['title'],
            'hy' => $_POST['hy'],
            'job' => $_POST['job'],
            'content' => $_POST['content'],
            'client' => $_POST['client']
        );

        $ltM = $this->MODEL('lietou');

        $return = $ltM->upLtInfo(array('uid' => intval($_POST['uid'])), array('mData' => $mData, 'ltData' => $ltData, 'utype' => 'admin'));
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

	/**
	 * 会员-猎头-用户管理:全部猎头->修改猎头
	 * 修改猎头信息->修改会员级别
	 */
	public function rating_action(){
		$ratUid				=	intval($_POST['uid']);
		$rating_name		=	trim($_POST['rat']);
		$rat_arr			=	@explode(',',$rating_name);
		if(empty($ratUid) || count($rat_arr) != 2){
			echo 0;die;
		}
		$ratingM			=	$this -> MODEL('rating');
		$value				=	$ratingM -> ltratingInfo($rat_arr[0], $ratUid);
		if(empty($value)){
			echo 0;die;
		}
		$ltM				=	$this -> MODEL('lietou');
		$statis				=	$ltM -> getLtStatisInfo(array('uid' => array('=', $ratUid)));
		if(empty($statis)){
			$value['uid']	=	$ratUid;
			$ltM -> addStatis($value);
		}else{
			if($statis['rating'] != $rat_arr[0]){
				$ltM -> upStatis(array('uid' => array('=', $ratUid)), $value);
			}
		}
		echo 1;die;
	}
 
	/**
	 * 会员-猎头-用户管理:全部猎头
	 * 修改会员等级->获取统计等级信息
	 */
	public function getstatis_action(){
		$ratUid					=	intval($_POST['uid']);
		if(empty($ratUid)){
            $this->render_json(1,'参数错误,请重试');
		}
		$ltM					=	$this -> MODEL('lietou');
		$fieldData				=	array('field' => '`rating`, `rating_name`, `lt_job_num`, `lt_down_resume`, `lt_editjob_num`,`lt_breakjob_num`, `integral`, `vip_etime`,`chat_num`');
		$rating					=	$ltM -> getLtStatisInfo(array('uid' => array('=', $ratUid)), $fieldData);
		if($rating['vip_etime'] > 0){
			$rating['vipetime'] = date('Y-m-d',$rating['vip_etime']);
		}else{
			$rating['vipetime'] = '';
		}
        $this->render_json(0,'',$rating);
	}

	/**
	 * 会员-猎头-用户管理:全部猎头
	 * 修改会员等级->保存会员的等级信息
	 */
	public function uprating_action(){
		$ratingId					=	intval($_POST['rating']);
		$ratingUid					=	intval($_POST['ratuid']);
		if(empty($ratingId) || empty($ratingUid)){
            $this->render_json(1,'缺少参数！');
		}

		unset($_POST['ratuid']);unset($_POST['pytoken']);

		if($_POST['delaytime'] && $_POST['oldetime']){
			
			$_POST['vip_etime'] 	=	strtotime($_POST['delaytime']);
			
		}else{
			$_POST['vip_etime'] 	=	intval($_POST['oldetime']);
		}
		unset($_POST['delaytime']);
		unset($_POST['oldetime']);

		$ratingM					=	$this -> MODEL('rating');
		$ratinginfo					=	$ratingM -> getInfo(array('id' => array('=', $ratingId)), array('field' => '`type`, `name`'));
		$_POST['rating_type']		=	$ratinginfo['type'];
		$_POST['rating_name']		=	$ratinginfo['name'];
		$ltM						=	$this -> MODEL('lietou');
		$id 						=	$ltM -> upStatis(array('uid' => array('=', $ratingUid)), $_POST);
        if ($id){
            $this->render_json(0,'猎头会员等级(ID:'.$ratingUid.')修改成功！');
        }else{
            $this->render_json(1,'修改失败！');
        }
	}

	/**
	 * 会员-猎头-用户管理:全部猎头
	 * 修改会员等级->选择会员等级
	 */
	public function getrating_action(){
		$ratingId	=	intval($_POST['id']);
		$uid		=   intval($_POST['uid']);
		if(empty($ratingId)){
            $this->render_json(1,'参数错误,请重试');
        }
		$ratingM					=	$this -> MODEL('rating');
		$rating						=	$ratingM -> changeltratingInfo($ratingId,$uid);
		
		if($rating['vip_etime']>0){
			$rating['oldetime'] = $rating['vip_etime'];
			$rating['vipetime'] = date('Y-m-d',$rating['vip_etime']);
			
		}else{
			$rating['oldetime'] = 0;
			$rating['vipetime'] = '不限';
		}
        $rating['rating'] = $_POST['id'];
        $this->render_json(0,'',$rating);
	}

	/**
	 * @desc 后台猎头列表 --  修改 -- 会员套餐 -- 提交表单
	 */
	function saveRating_action(){
        $uid		=	intval($_POST['uid']);
        if (!$uid){
            $this->render_json('1','参数错误,请重试!');
        }

        $ltM		=	$this->MODEL('lietou');

        $sData		=	array(
            'rating'			=>  $_POST['rating'],
            'vip_etime'			=>  $_POST['vipetime']?strtotime($_POST['vipetime']):0,
            'lt_job_num'		=>  intval($_POST['lt_job_num']),
            'lt_breakjob_num'	=>  intval($_POST['lt_breakjob_num']),
            'lt_down_resume'	=>  intval($_POST['lt_down_resume']),
            'chat_num'			=>  intval($_POST['chat_num'])
        );
        $return		=	$ltM -> setStatisInfo($uid, array('sData' => $sData, 'utype' => 'admin'));
        if ($return['errcode'] == 8){
            $this->render_json('1',$return['msg']);
        }else{
            $this->render_json('0',$return['msg']);
        }
	}

	/**
	 * @desc 后台猎头列表 --  修改 -- 账户信息 -- 提交表单
	 */
	 function saveUser_action(){


			$uid		=	intval($_POST['uid']);
            if (!$uid) {
                $this->render_json('1','参数错误');
            }

			$userInfoM	=	$this->MODEL('userinfo');

			$data		=	array(
				'username'	=>	$_POST['username'],
				'password'	=>	$_POST['password'],
				'status'	=>	$_POST['status'],
				'lock_info'	=>	$_POST['lock_info']
			);

			$result		=	$userInfoM -> addMemberCheck($data, $uid, 'admin');

			if(!empty($result['msg'])){
                $this->render_json(1,$result['msg']);
			}else{
				$userInfoM -> upInfo(array('uid' => $uid), $data);
                $this->render_json(0,'更新成功！');
			}

		
	 }

	/**
	 * 会员-猎头-用户管理:全部猎头
	 * 设置推荐
	 */
	public function lt_rec_action(){

		$ltId						=	intval($_POST['id']);
		$ltRec						=	intval($_POST['rec']);
		$ltM						=	$this -> MODEL('lietou');
		$nid 						=	$ltM -> upInfo(array('uid' => array('=', $ltId)), array('rec' => $ltRec));
		$sysmsgM					=	$this -> MODEL('sysmsg');
		if($nid && $ltRec == 1){
			$sysmsgM -> addInfo(array('content' => '管理员设置猎头推荐','usertype'=>3,  'uid' => $ltId));
		}elseif($nid && $ltRec == 0){
			$sysmsgM -> addInfo(array('content' => '管理员操作：取消猎头推荐','usertype'=>3,  'uid' => $ltId));
		}
		$this -> MODEL('log') -> addAdminLog('猎头会员(ID:'.$ltId.')推荐设置成功！');
        if ($nid){
            $this->render_json('0','操作成功');
        }else{
            $this->render_json('0','操作失败');

        }
	}
	/**
	 * 会员-猎头-用户管理:全部猎头
	 * 数据统计
	 */
	public function ltNum_action(){
 		$MsgNum = $this -> MODEL('msgNum');
		$arr =  $MsgNum -> ltNumV1();
        $this->render_json('0','',$arr);
	}

    public function mobliestatus_action()
    {

        $_POST      =   $this->post_trim($_POST);

        $LietouM    =   $this->MODEL('lietou');
        $UserinfoM  =   $this->MODEL('userinfo');

        if ($_POST['ltphonemoblie'] == "") {
            $this->render_json('1',"请填写手机号码");
        } elseif (CheckMobile($_POST['ltphonemoblie']) == false) {
            $this->render_json('1',"手机号码格式错误");
        }

        $uid        =   $_POST['uid'];
        $status     =   $_POST['mstatus'];

        $moblie     =   $_POST['ltphonemoblie'];

        $ltInfo     =   $LietouM->getInfo(array('uid' => $uid), array('field' => '`moblie`, `moblie_status`'));

        if (empty($ltInfo)) {
            $this->render_json('1',"当前数据错误");

        }
        if ($ltInfo['moblie'] == $moblie && $ltInfo['moblie_status'] == 1){
            $this->render_json('1',"手机号未变更，无需调整！");
        }

        $data   =   array('moblie_status' => $status, 'moblie' => $moblie);
        $nid    =   $LietouM->upInfo(array('uid' => $uid), $data);

        if (!$nid) {
            $this->render_json('1','手机认证失败');
        }
        $memberdata = array('moblie' => $moblie, 'moblie_status' => $status);

        $msg        =   '新手机号：'.$moblie;
        if (!empty($ltInfo['moblie']) && $ltInfo['moblie'] != $moblie) {

            $msg   .=  '，原手机号：' . $ltInfo['moblie'];
        }
        // 获取用户信息，用来判断旧手机号和用户名是否一致
        $member =   $UserinfoM->getInfo(array('uid' => $uid), array('field'=>'username,moblie'));
        if ($member['username'] == $member['moblie']) {

            $memberdata['username'] = $moblie;
            $msg .= '，同步调整用户名为'.$moblie;
        }
        // 有账号的用户名与新手机号一致的，将用户名改成新手机号
        $omb  =  $UserinfoM->getInfo(array('username' => $moblie), array('field'=>'uid'));
        if (!empty($omb)) {

            // 如果现有数据中，存在用户名是这个手机号的，要修改
            $UserinfoM->upInfo(array('uid' => $omb['uid']), array('username' => $moblie . '_s'));

            $logDetail  =   '账号修改：账号（UID：'.$uid.'）认证手机号，因本账号用户名与该手机号相同，调整本账号（ID：'.$omb['uid'].'）用户名（'.$moblie.' → '.$moblie.'_s）';

            $logM = $this->MODEL('log');
            $logM -> addAdminLog($logDetail);
        }
        $UserinfoM->upInfo(array('uid' => $uid), $memberdata);

        $this->obj->update_once('member', array('moblie' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'moblie' => $moblie));
        $this->obj->update_once('resume', array('telphone' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'telphone' => $moblie));
        $this->obj->update_once('company', array('linktel' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'linktel' => $moblie));
        $this->obj->update_once('lt_info', array('moblie' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'moblie' => $moblie));
        $this->obj->update_once('pr_train', array('linktel' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'linktel' => $moblie));
        $this->admin_json('0',"手机认证成功(用户ID：" . $uid . "，" . $msg . ")");

    }

    public function emailstatus_action()
    {
        $LietouM    =   $this->MODEL('lietou');
        $UserinfoM  =   $this->MODEL('userinfo');

        if ($_POST['ltemailmail'] == "") {
            $this->render_json('1',"请填写邮箱");
        } elseif (CheckRegEmail($_POST['ltemailmail']) == false) {
            $this->render_json('1',"邮箱格式错误");
        }

        $uid    =   $_POST['uid'];
        $status =   intval($_POST['estatus']);
        $email  =   $_POST['ltemailmail'];

        $ltInfo =   $LietouM->getInfo(array('uid' => $uid), array('field' => '`email_status`'));

        if (empty($ltInfo)) {
            $this->render_json('1',"当前数据错误");
        }

        $data   =   array('email_status' => $status, 'email' => $email);
        $nid    =   $LietouM->upInfo(array('uid' => $uid), $data);

        if (!$nid) {
            $this->render_json('1',"邮箱认证失败");
        }
        $emaildata  =   array('email' => $email, 'email_status' => $status);
        $UserinfoM->upInfo(array('uid' => $uid), $emaildata);

        $this->obj->update_once('member', array('email' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'email' => $email));
        $this->obj->update_once('lt_info', array('email' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'email' => $email));
        $this->obj->update_once('resume', array('email' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'email' => $email));
        $this->obj->update_once('company', array('linkmail' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'linkemail' => $email));
        $this->obj->update_once('px_train', array('linkmail' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'linkmail' => $email));

        $msg        =   '新邮箱：' . $email;
        if (!empty($ltInfo['linkmail']) && $ltInfo['linkmail'] != $email) {
            $msg    .=  '，原邮箱：' . $ltInfo['linkmail'];
        }

        $this -> MODEL('log') -> addAdminLog("猎头会员(ID".$uid.")认证邮箱【".$email."】");

        $this->render_json('0',"邮箱认证成功(用户ID：" . $uid . "，" . $msg . ")");
    }

    //批量认证
    public function batchfirm_action()
    {

        $LietouM    =   $this->MODEL('lietou');
        $UserinfoM  =   $this->MODEL('userinfo');
        $CompanyM   =   $this->MODEL('company');

        $status =   $_POST['plstatus'];
        $msg    =   array();

        if ($_POST['ltname_email'] == "" && $_POST['ltname_moblie'] == "" && $_POST['ltname_yyzz'] == "") {
            $this->render_json('1',"请选择认证类型");
        }
        if ($_POST['uid'] == "") {
            $this->render_json('1',"非法操作");

        }

        if ($status == "") {
            $this->render_json('1',"请选择认证状态");
        }

        if ($_POST['ltname_email'] || $_POST['ltname_moblie']) {

            $where['uid']   =   array('in', pylode(',', $_POST['uid']));
            $rows           =   $LietouM->getList($where, array('field' => '`uid`,`email`,`moblie`,`email_status`,`moblie_status`'));

            if (is_array($rows) && $rows) {

                if ($_POST['ltname_email']) {

                    array_push($msg, '邮箱');

                    foreach ($rows as $val) {
                        if ($val['email'] || $val['email_status'] == 1) {

                            $emailuid[] =   $val['uid'];
                        }
                    }

                    $emaildata          =   array('email_status' => $status);
                    $emailwhere['uid']  =   array('in', pylode(',', $emailuid));

                    $UserinfoM->upInfo($emailwhere, $emaildata);
                    $LietouM->upInfo($emailwhere, $emaildata);
                }

                if ($_POST['ltname_moblie']) {

                    array_push($msg, '手机');

                    foreach ($rows as $val) {
                        if ($val['moblie'] || $val['moblie_status'] == 1) {

                            $moblieuid[]    =   $val['uid'];
                        }
                    }

                    $mobliewhere['uid'] =   array('in', pylode(',', $moblieuid));
                    $mobliedata         =   array('moblie_status' => $status);

                    $UserinfoM->upInfo($mobliewhere, $mobliedata);
                    $LietouM->upInfo($mobliewhere, $mobliedata);
                }
            }

        }

        if ($_POST['ltname_yyzz']) {

            array_push($msg, '营业执照');

            if ($status != 0) {

                $yyzzwhere['uid']   =   array('in', pylode(',', $_POST['uid']));
                $yyzzwhere['type']  =   4;

                $yyzz               =   $CompanyM->getCertList($yyzzwhere, array('field' => '`uid`,`check`'));

                if (is_array($yyzz) && $yyzz) {
                    foreach ($yyzz as $val) {
                        if ($val['check']) {

                            $checkuid[] =   $val['uid'];
                        }
                    }
                }
            } else {

                $checkuid[]     =   $_POST['uid'];
            }

            $yyzzkwhere['uid']  =   array('in', pylode(',', $checkuid));
            $yyzzkdata          =   array('yyzz_status' => $status);

            $LietouM->upInfo($yyzzkwhere, $yyzzkdata);

            $checkdata          =   array('status' => $status);

            $checwhere['uid']   =   array('in', pylode(',', $checkuid));
            $checwhere['type']  =   4;

            $CompanyM->upCertInfo($checwhere, $checkdata, array('utype' => 'admin'));
        }

        $ty = $status = 1 ? '已认证' : '待认证';
        $this->admin_json(0,'(猎头列表)' . implode(',', $msg) . '批量设置' . $ty . '成功(ID:' . pylode(',', $_POST['uid']) . ')');
    }

	/**
	 * 会员-猎头-猎头认证管理
	 * 审核认证 -> 保存数据
	 * 2019-05-31 hjy
	 */
	public function ltstatus_action(){
	    
	    $ltUid									=	trim($_POST['uid']);
	    $ltStatus								=	intval($_POST['r_status']);
	    if(empty($ltUid)){
            $this->render_json('0','非法操作！');
	    }
	    
	    //查询数据是否存在
	    $ltM									=	$this -> MODEL('lietou');
	    $ltlist									=	$ltM -> getList(array('uid' => array('in', $ltUid)), array('field' => '`email`, `uid`, `realname`, `rzid`'));
	    if(empty($ltlist)){
            $this->render_json('0','数据错误！');
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
	        if ($ltStatus == 2){
	            
	            $statusInfo  =  $tagName.':'.$v['name'].'审核未通过';
	            
	            if($_POST['statusbody']){
	                
	                $statusInfo  .=  ' , 原因：'.$_POST['statusbody'];
	            }
	            $msg[$v['uid']]  =  $statusInfo;
	            
	        }elseif($ltStatus == 1){
	            
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
            $this->admin_json(0,'猎头认证(UID:'.$ltUid.')设置成功！');
	    }else{
            $this->admin_json(1,'设置失败！');
        }
	}
	public function yyzzlockinfo_action(){
		$companyM								=	$this -> MODEL('company');
		$userinfo								=	$companyM -> getCertInfo(array('uid' => array('=', $_POST['uid']), 'type' => array('=', 4)), array('field' => '`statusbody`'));
		echo $userinfo['statusbody'];die;
	}
	public function Imitate_action(){
	    
	    $userinfoM  =  $this->MODEL('userinfo');
	    
	    $member     =  $userinfoM -> getInfo(array('uid'=> intval($_GET['uid'])),array('field'=>'`uid`,`username`,`salt`,`email`,`password`,`usertype`,`did`'));
	    
 
	    $this -> cookie->unset_cookie($_SESSION['auid']);
	    
	    $this -> cookie->add_cookie($member['uid'],$member['username'],$member['salt'],$member['email'],$member['password'],3,$this->config['sy_logintime'],$member['did'],'1');
		
		$logM  		=  $this->MODEL('log');
		
		$content	=	'管理员'.$_SESSION['ausername'].'登录猎头账户(ID:'.$member['uid'].')';
		
		$adminLo	=	$logM -> addAdminLog($content);
		
		header('Location: '.$this->config['sy_weburl'].'/member');
	}


    /**
     * 会员列表（分配分站）
     */
    function checksitedid_action(){

        if(!$_POST['uid']){
            $this->render_json(1,'参数错误');
        }
        $siteM	=	$this -> MODEL('site');

        $return	=	$siteM -> memberSiteDid($_POST);

        if ($return['errcode']==9){
            $this->admin_json(0,$return['msg']);
        }else{
            $this->render_json(1,$return['msg']);
        }
    }
    /**
     * 会员列表（重置密码）
     */
    function reset_pw_action(){

        $userinfoM  =  $this->MODEL('userinfo');
        if(!$_POST['uid']){
            $this->render_json(1,'参数错误');
        }

        $userinfoM -> upInfo(array('uid'=>intval($_POST['uid'])),array('password'=>'123456'));

        $this -> MODEL('log') -> addAdminLog('会员(ID:'.$_POST['uid'].')重置密码成功');
        $this->render_json('0','修改成功');

    }

}
?>