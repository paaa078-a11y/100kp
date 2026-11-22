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
class supply_user_controller extends adminCommon{

	/**
	 * 会员
	 */
	function index_action(){
		$gqdemandM    			  	  			=  			$this -> MODEL('gqdemand');
		$where['state']							=			1;
		if($_POST['keyword']){
		    $keytype  							=   		intval($_POST['type']);
		    $keyword  							=   		trim($_POST['keyword']);
		    if ($keytype == 2){
				$where['moblie']      			=  			array('like',$keyword);
		    }elseif ($keytype == 3){
				$where['uid']         			=  			array('=',$keyword);
		    }else{
				$where['name']					=			array('like',$keyword);
			}
		}

		if($_POST['status']){
		    $status                   			=   		intval($_POST['status']);
		    $where['status']          			=  			$status == 4 ? 0 : $status;
		}
		if($_POST['adtime']){
		    if($_POST['adtime']=='1'){
		        $where['reg_date']	  			=  			array('>',strtotime('today'));
		    }else{
		        $where['reg_date']	  			=  			array('>',strtotime('-'.intval($_POST['adtime']).' day'));
		    }
		}
        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('gq_info', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list = array();
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
		    //limit order 只有在列表查询时才需要
		    if($_POST['order']){
		        if($_POST['t']=='time'){
		            $where['orderby']			=		 'lastupdate,'.$_POST['order'];
		        }else{
		            $where['orderby']			=		  $_POST['t'].','.$_POST['order'];
		        }
		    }else{
				$where['orderby']				=		  array('uid,desc');
			}
		    $where['limit']						=		 $pages['limit'];
			$list								=		 $gqdemandM->getGqinfoList($where);
		}
    

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page','search_list','domain'));
	}
    public function  getSearchData_action(){
        $cacheM					         =	$this -> MODEL('cache');
        $domain					         =	$cacheM	-> GetCache('domain');
        $domain['Dname'] = (object)$domain['Dname'];
        $search_list[]  		=  		array('param'=>'status','name'=>'审核状态','value'=>array('1'=>'已审核','2'=>'已锁定','3'=>'未通过','4'=>'未审核'));
        $this->render_json(0, 'ok', compact('search_list','domain'));
    }
	//浏览相关页面
	/***
	 * 添加和修浏览页面
	 * 
	 */
	public  function edit_action(){
		$gqdemandM        			=  			$this -> MODEL('gqdemand');
		$cacheM           			=     		$this->MODEL('cache');
		$options          			=     		array('user');
		$cache            			=    		$cacheM -> GetCache($options);
		$user_sex         			=     		$cache['user_sex'];
		if($_POST['uid']){
			$uid       				=			intval($_POST['uid']);
			$where['uid']			=			$uid;
			$info					=			$gqdemandM->getGqInfo($where);
		}
        $this->render_json(0, 'ok', compact('user_sex', 'info', 'cache'));
	}

	/**
	 * 保存信息除了功能 
	 * 
	*/
	public function savegqInfo_action(){
		$gqdemandM      			=  			$this -> MODEL('gqdemand');
		$_POST  			    	=  			$this->post_trim($_POST);

		if($_POST['submit']){
			$uid    				=  			intval($_POST['uid']);
			$gData  				=  			array(
				'name'         	  	=>  		$_POST['name'],
				'moblie'          	=>  		$_POST['moblie'],
				'sex'     		  	=>  		$_POST['sex'],
				'provinceid'      	=>  		$_POST['provinceid'],
				'cityid'          	=>  		$_POST['cityid'],
				'three_cityid'    	=>  		$_POST['three_cityid'],
				'services'        	=>  		str_replace(array('&amp;','background-color:#ffffff','background-color:#fff','white-space:nowrap;'),array('&','background-color:','background-color:','white-space:'),$_POST['services']),
				'salary'     	  	=>    		$_POST['salary'],
				'content'        	=>   		str_replace(array('&amp;','background-color:#ffffff','background-color:#fff','white-space:nowrap;'),array('&','background-color:','background-color:','white-space:'),$_POST['content']),
				'lastupdate'      	=>  		time(),
				'speciality'		=>			$_POST['speciality'],
				'file'				=>			$_FILES['file'],
			);
			$where['uid']			=			$uid;
			$return 				=			$gqdemandM->upaddGqinfo($where,$gData);
            $this->admin_json($return['errcode']==9?0:1, $return['msg']);
		}
	}
	/**
	 * @desc  删除供求会员信息
	 */
	public function del_action(){

		$gqdemandM	=	$this -> MODEL('gqdemand');

        $uid	=	$_POST['del'];
	    
	    $return		=	$gqdemandM -> delInfo($uid);
        $this->admin_json($return['errcode']==9?0:1, $return['msg']);
	}
	/**
	 * 审核功能
	 * 
	 */
	public function statis_action(){

		$gqdemandM  				=  			$this -> MODEL('gqdemand');
    	if($_POST['status']  ==""){
            $this->render_json(1, "请选择审核状态");
     	}
		$post						=			array(
			'status'     			=>  		intval($_POST['status']),
	     	'statusbody'  			=>  		trim($_POST['statusbody'])
		);
		$return     				=  			$gqdemandM -> upGqtatus(intval($_POST['uid']),$post);
        $this->render_json($return['errcode']==9?0:1, $return['msg']);
	}

    function log_action()
    {

        $logM       =   $this->MODEL('log');
        $memberM    =   $this->MODEL('userinfo');

        if ($_POST['utype']) {
            $where['usertype']  =   trim($_POST['utype']);
        } else {

            $where['usertype']  =   1;
        }
        if (isset($_POST['uid'])) {

            $where['uid']       =   intval($_POST['uid']);
        }

        $keywordStr =   trim($_POST['keyword']);

        if (!empty($keywordStr)) {
            if ($_POST['type'] == 1) {

                $member         =   $memberM->getList(array('username' => array('like', $keywordStr)), array('field' => '`uid`,`username`'));
                foreach ($member as $v) {
                    $uid[]      =   $v['uid'];
                }
                $where['uid']   =   array('in', pylode(",", $uid));

            } elseif ($_POST['type'] == 3) {

                $where['uid']   =   $keywordStr;
            }

        }

        $contentStr =   trim($_POST['content']);

        if (!empty($contentStr)) {

            $logDetailList      =   $this->obj->select_all('member_log_detail', array('detail' => array('like', $contentStr)), 'log_id');

            if (!empty($logDetailList)){

                $logIds         =   array();
                foreach ($logDetailList as $lk => $lv){

                    $logIds[]   =   $lv['log_id'];
                }

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['id']        =   array('in', pylode(',', $logIds), '');
                $where['content']   =   array('like', $contentStr, 'OR');
                $where['PHPYUNBTWEND_A']    =   '';
            }else{

                $where['content']   =   array('like', $contentStr);
            }

        }

        if (!empty($_POST['end'])) {

            if ($_POST['end'] == '1') {

                $where['ctime'] =   array('>=', strtotime(date("Y-m-d 00:00:00")));
            } else {

                $where['ctime'] =   array('>=', strtotime('-' . (int)$_POST['end'] . 'day'));
            }
        }

        if (!empty($_POST['operas'])){

            $operaStr   =   intval($_POST['operas']);
            $operaSql 	= 	array(
                '1'		=>	array('name' => array('职位')),
                '2'		=>	array('name' => array('创建','简历', '经历')),
                '3'		=>	array('name' => array('下载')),
                '4'	 	=>	array('name' => array('邀请')),
                '5'	 	=>	array('name' => array('收藏', '关注', '备注')),
                '6'		=>	array('name' => array('申请', '报名', '应聘', '委托')),
                '7'		=>	array('name' => array('基本信息')),
                '8'		=>	array('name' => array('修改密码')),
                '9'		=>	array('name' => array('兼职')),
                '10'    =>	array('name' => array('猎头职位')),
                '11'    =>	array('name' => array('用户名', '身份')),
                '12'    =>	array('name' => array('账号认证', '解除', '绑定', '验证', '资质', '执照', '认证')),
                '14'    =>	array('name' => array('招聘会', '专题')),
                '15'    =>	array('name' => array('地图', '助力')),
                '16'	=>	array('name' => array('图片', '头像', 'LOGO', '环境', '产品', '新闻', '二维码', '横幅')),
                '17' 	=>	array('name' => array('兑换', '积分'), 'realId' => 17),
                '18'	=>	array('name' => array('回复', '咨询', '留言', '系统消息')),
                '19' 	=>	array('name' => array('问答')),
                '20'	=>	array('name' => array('培训师', '讲师')),
                '21'	=>	array('name' => array('课程')),
                '22'	=>	array('name' => array('新闻')),
                '23'	=>	array('name' => array('举报')),
                '24' 	=>	array('name' => array('优惠券')),
                '25' 	=>	array('name' => array('悬赏', '推送')),
                '26' 	=>	array('name' => array('浏览', '黑名单')),
                '27' 	=>	array('name' => array('子账号')),
                '29' 	=>	array('name' => array('项目')),
                '30' 	=>	array('name' => array('聊天', '直聊')),
                '88' 	=>	array('name' => array('订单'))
            );
            if (array_key_exists($operaStr, $operaSql)) {

                if (count($operaSql[$operaStr]['name']) == 1){
                    $where['content']           =   array('like', $operaSql[$operaStr]['name'][0]);
                }else{

                    $where['PHPYUNBTWSTART']    =   '';
                    foreach ($operaSql[$operaStr]['name'] as $oV) {
                        $where['content'][]     =   array('like', $oV, 'OR');
                    }
                    $where['PHPYUNBTWEND']      =   '';
                }
            } elseif (!empty($operasStr)) {
                $where['opera']             =   $operaStr;
            }
        }
        if (isset($_POST['parrs']) && $_POST['parrs']) {
            $where['type']      =   intval($_POST['parrs']);
        }

        if (!empty($_POST['time'])) {
            $time = $_POST['time'];
            $time_begin = $time[0]? date('Y-m-d',strtotime(str_replace(array('T','Z'),' ',$time[0]))): date('Y-m-d', strtotime('-30 days'));
            $time_end  = $time[1] ? date('Y-m-d',strtotime(str_replace(array('T','Z'),' ',$time[1]))):date('Y-m-d');
            $where['ctime'][]   =   array('>=', strtotime($time_begin . "00:00:00"));
            $where['ctime'][]   =   array('<=', strtotime($time_end . "23:59:59"));
        }
        $pageM			=	$this  -> MODEL('page');
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pages	=	$pageM -> adminPageList('member_log',$where,$page,array('limit' => $pageSize));
        if(!$pages['total']){
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }

        if ($_POST['order']) {

            $where['orderby']   =   $_POST['t'].','.$_POST['order'];

        } else {

            $where['orderby']   =   array('id,desc');
        }
        $where['limit']         =   $pages['limit'];

        $List   =   $logM->getMemlogList($where, array('utype' => 'admin'));
        foreach ($List as &$value){
            $value['ctime_ymd'] = $value['ctime']?date('Y-m-d H:i:s',$value['ctime']):'';
        }
        $this->render_json(0,'',['data'=>$List,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);
    }


    function delLog_action()
    {

        $logM   =   $this->MODEL('log');

        if ($_POST['del'] == 'allgq') {

            $where['usertype']  =   5;
            $logM->delMemlog($where);
            $this->render_json(0,'已清空供求日志');
        } elseif ($_POST['del']) {

            $del    =   $_POST['del'];
            if (is_array($del)) {

                $where['id']    =   array('in', pylode(',', $del));
            } else {

                $where['id']    =   $del;
            }
            $return =   $logM->delMemlog($where);
            if ($return['errcode']==9){
                $this->render_json(0,$return['msg']);
            }else{
                $this->render_json(1,$return['msg']);
            }
        }
    }
	/**
	 * 会员-供求-供求用户列表: 全部供求（页面统计数量）
	 */
	function gqNum_action(){

		$MsgNum						=			$this->MODEL('msgNum');

		$data =  $MsgNum->gqNum();
        $this->render_json(0, 'ok', json_decode($data));
	}
}

?>