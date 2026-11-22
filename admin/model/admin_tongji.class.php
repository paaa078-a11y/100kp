<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class admin_tongji_controller extends adminCommon{	

	function index_action(){//会员注册统计
		
		$this->yuntpl(array('admin/admin_tongji'));
	}

	function chart_action() { // 统计页图表
        $TongJi = $this->MODEL('tongji');

        // 用户数据统计趋势图
        // 用户/简历趋势对比图
        // 新增用户
        $userStats = $TongJi->getTj('member', $_GET, 'reg_date', array('usertype' => '1'));
        $list['adduser']['name'] = '新增用户';
        $list['adduser']['list'] = $userStats['list'];
        $allNum['adduser'] = $userStats['allnum'];
        // 新增简历
        $expectStats = $TongJi->getTj('resume_expect', $_GET, 'ctime');
        $list['addexpect']['name'] = '新增简历';
        $list['addexpect']['list'] = $expectStats['list'];
        $allNum['addexpect'] = $expectStats['allnum'];

        // 简历投递/刷新趋势对比图
        // 简历投递数
        $resumeDeliveryStats = $TongJi->getTj('userid_job', $_GET, 'datetime');
        $list['resumeDelivery']['name'] = '简历投递';
        $list['resumeDelivery']['list'] = $resumeDeliveryStats['list'];
        $allNum['resumeDelivery'] = $resumeDeliveryStats['allnum'];
        // 简历刷新数
        $resumeRefreshStats = $TongJi->getTj('resume_refresh_log', $_GET, 'r_time');
        $list['resumeRefresh']['name'] = '简历刷新';
        $list['resumeRefresh']['list'] = $resumeRefreshStats['list'];
        $allNum['resumeRefresh'] = $resumeRefreshStats['allnum'];

        // 企业数据统计趋势图
        // 企业/职位趋势对比图
        // 新增企业
        $comStats = $TongJi->getTj('member', $_GET, 'reg_date', array('usertype' => '2'));
        $list['addcom']['name'] = '新增企业';
        $list['addcom']['list'] = $comStats['list'];
        $allNum['addcom'] = $comStats['allnum'];
        // 新增职位
        $jobStats = $TongJi->getTj('company_job', $_GET, 'sdate');
        $list['addjob']['name'] = '新增职位';
        $list['addjob']['list'] = $jobStats['list'];
        $allNum['addjob'] = $jobStats['allnum'];

        // 企业招聘趋势对比图
        // 简历下载数
        $downResumeStats = $TongJi->getTj('down_resume', $_GET, 'downtime');
        $list['downResume']['name'] = '简历下载';
        $list['downResume']['list'] = $downResumeStats['list'];
        $allNum['downResume'] = $downResumeStats['allnum'];
        // 职位刷新数
        $jobRefreshStats = $TongJi->getTj('job_refresh_log', $_GET, 'r_time');
        $list['jobRefresh']['name'] = '职位刷新';
        $list['jobRefresh']['list'] = $jobRefreshStats['list'];
        $allNum['jobRefresh'] = $jobRefreshStats['allnum'];
        // 邀请面试数
        $Stats = $TongJi->getTj('userid_msg', $_GET, 'datetime');
        $list['inviteInterview']['name'] = '邀请面试';
        $list['inviteInterview']['list'] = $Stats['list'];
        $allNum['inviteInterview'] = $Stats['allnum'];
        // 聊天统计
        $chatStats = $TongJi->getTj('chat_log', $_GET, 'sendTime');
        $list['chat']['name'] = '聊天';
        $list['chat']['list'] = $chatStats['list'];
        $allNum['chat'] = $chatStats['allnum'];

        $this->yunset('allNum', $allNum);
        $this->yunset('list', $list);

        $this->yuntpl(array('admin/admin_tongji_chart'));
    }
	
	function reg_action(){//会员注册统计
		
		$TongJi	=	$this -> MODEL('tongji');
	
		//总会员
		$Stats	=	$TongJi -> getTj('member',$_GET,'reg_date');
		$List['all']['name']	=	'所有会员';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];
		
		//企业
		$comStats	=	$TongJi -> getTj('member',$_GET,'reg_date',array('usertype'=>'2'));
		$List['com']['name']	=	'企业会员';
		$List['com']['list']	=	$comStats['list'];
		$AllNum['com']			=	$comStats['allnum'];
		
		//个人
		$userStats	=	$TongJi -> getTj('member',$_GET,'reg_date',array('usertype'=>'1'));
		$List['user']['name']	=	'个人会员';
		$List['user']['list']	=	$userStats['list'];
		$AllNum['user']			=	$userStats['allnum'];

		//注册来源统计

		$CountTj	=	$TongJi -> DataTj('reg',$Stats['timedate']['DateWhere'],'member','uid');
		
		$this->yunset('counttj',$CountTj);
		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);

		//echo json_encode($List);die;
		$this->yunset('name','会员注册统计');

		$this->yuntpl(array('admin/admin_tongji_reg'));
	}
    function login_action(){//会员登陆统计
        $TongJi	=	$this -> MODEL('tongji');
        //总会员
        $Stats	=	$TongJi -> getTj('login_log',$_GET,'ctime',array('usertype'=>array('in', '1,2')));
        $List['all']['name']	=	'所有会员';
        $List['all']['list']	=	$Stats['list'];
        $AllNum['all']			=	$Stats['allnum'];
        //企业
        $comStats	=	$TongJi -> getTj('login_log',$_GET,'ctime',array('usertype'=>'2'));
        $List['com']['name']	=	'企业会员';
        $List['com']['list']	=	$comStats['list'];
        $AllNum['com']			=	$comStats['allnum'];
        //个人
        $userStats	=	$TongJi -> getTj('login_log',$_GET,'ctime',array('usertype'=>'1'));
        $List['user']['name']	=	'个人会员';
        $List['user']['list']	=	$userStats['list'];
        $AllNum['user']			=	$userStats['allnum'];
        $this->yunset('AllNum',$AllNum);
        $this->yunset('list',$List);
        $this->yunset('name','会员登陆统计');
        $this->yuntpl(array('admin/admin_tongji_login'));
    }
	//职位浏览
	function lookjob_action(){

		$TongJi	=	$this -> MODEL('tongji');
	
		$Stats	=	$TongJi -> getTj('look_job',$_GET,'datetime');
		
		$List['all']['name']	=	'职位浏览';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];
		//统计浏览数最多的职位
		$TopList['job']			=	$TongJi -> TopTen("look_job",$Stats['timedate']['DateWhere'],"jobid","job");
		//统计浏览最多的企业
		$TopList['company'] 	=	$TongJi -> TopTen("look_job",$Stats['timedate']['DateWhere'],"com_id","company");
		
		
		$this->yunset('toplist',$TopList);
		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','职位浏览统计');

		$this->yuntpl(array('admin/admin_tongji_lookjob'));
	
	}

	//简历浏览
	function lookresume_action(){

		$TongJi	=	$this -> MODEL('tongji');
	
		$Stats	=	$TongJi -> getTj('look_resume',$_GET,'datetime');
		$List['all']['name']	=	'简历浏览';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];

		//统计浏览数最多的求职简历
		$TopList['expect']		=	$TongJi -> TopTen("look_resume",$Stats['timedate']['DateWhere'],"resume_id","expect");
		//统计浏览最多简历的企业
		$TopList['company']		=	$TongJi -> TopTen("look_resume",$Stats['timedate']['DateWhere'],"com_id","company");
		
		$this->yunset('toplist',$TopList);
		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','简历浏览统计');
		$this->yuntpl(array('admin/admin_tongji_lookresume'));
	
	}
	//邀请面试
	function useridmsg_action(){

		$TongJi	=	$this -> MODEL('tongji');
	
		$Stats	=	$TongJi -> getTj('userid_msg',$_GET,'datetime');
		$List['all']['name']	=	'邀请面试';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];
		//统计发出邀请面试最多的企业
		$TopList['company']		=	$TongJi -> TopTen("userid_msg",$Stats['timedate']['DateWhere'],"fid","company");

		//统计被邀请最多的求职者
		
		$TopList['resume']		=	$TongJi -> TopTen("userid_msg",$Stats['timedate']['DateWhere'],"uid","resume");
		
		//根据邀请面试数量统计紧缺人才

		$CountTj	=	$TongJi -> DataTj('job',$Stats['timedate']['DateWhere'],'userid_msg','jobid');
		
		$this->yunset('counttj',$CountTj);
		$this->yunset('toplist',$TopList);
		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','邀请面试统计');

		$this->yuntpl(array('admin/admin_tongji_useridmsg'));
	
	}

	//简历下载
	function downresume_action(){

		$TongJi	=	$this -> MODEL('tongji');
	
		$Stats	=	$TongJi -> getTj('down_resume',$_GET,'downtime');
		$List['all']['name']	=	'简历下载';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];
		
		//统计下载简历最多企业
		$TopList['company']		=	$TongJi -> TopTen("down_resume",$Stats['timedate']['DateWhere'],"comid","company");

		//统计被下载最多的求职者
		
		$TopList['resume']		= 	$TongJi -> TopTen("down_resume",$Stats['timedate']['DateWhere'],"uid","resume");
		
		//根据下载数据统计企业最感兴趣范围

		$CountTj	=	$TongJi -> DataTj('resume_expect',$Stats['timedate']['DateWhere'],'down_resume','eid');
		
		$this->yunset('counttj',$CountTj);
		$this->yunset('toplist',$TopList);

		
		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','简历下载统计');

		$this->yuntpl(array('admin/admin_tongji_downresume'));
	
	}
	//简历投递
	function useridjob_action(){

		$TongJi	=	$this -> MODEL('tongji');
	
		$Stats	=	$TongJi -> getTj('userid_job',$_GET,'datetime');
		$List['all']['name']	=	'简历投递';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];

		//统计发出邀请面试最多的企业
		$TopList['company']		=	$TongJi -> TopTen("userid_job",$Stats['timedate']['DateWhere'],"com_id","company");

		//统计发出最多申请的简历
		
		$TopList['resume']		=	$TongJi -> TopTen("userid_job",$Stats['timedate']['DateWhere'],"eid","expect");
		

		//根据邀请面试数量统计紧缺人才

		$CountTj	=	$TongJi -> DataTj('resume_expect',$Stats['timedate']['DateWhere'],'userid_job','eid');
		
		$this->yunset('toplist',$TopList);
		$this->yunset('counttj',$CountTj);
		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','简历投递统计');

		$this->yuntpl(array('admin/admin_tongji_useridjob'));
	
	}
	//订单统计
	function order_action(){

		$TongJi	=	$this -> MODEL('tongji');
		
		if($_GET['did']){
			$where	=	array('order_state' => '2', 'did' => $_GET['did']);
		}else{
			$where	=	array('order_state' => '2');
		}		

		$Stats	=	$TongJi -> getTj('company_order', $_GET, 'order_time', $where, "SUM(`order_price`) as count");

		$List['all']['name']	=	'充值金额';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];
		
		$TopList['company']		=	$TongJi -> TopTen('company_order', array_merge($Stats['timedate']['DateWhere'], $where),"uid","order",'10',"SUM(`order_price`) as count");
		
		//统计充值各类型
		$CountTj	=	$TongJi -> DataTj('order', array_merge($Stats['timedate']['DateWhere'], $where), 'company_order', 'id');

		$this->yunset('toplist',$TopList);
		$this->yunset('counttj',$CountTj);

		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','充值金额统计');
		
		//提取分站内容
	    $cacheM	=	$this -> MODEL('cache');
	    $domain	=	$cacheM	-> GetCache('domain');
	    
	    $this -> yunset('Dname', $domain['Dname']);

		$this->yuntpl(array('admin/admin_tongji_order'));
	
	}

	//消费统计
	function pay_action(){

		$TongJi	=	$this -> MODEL('tongji');
	
		$Stats	=	$TongJi -> getTj('company_pay',$_GET,'pay_time');
		$List['all']['name']	=	'消费记录';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];
		
		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','消费记录统计');

		$this->yuntpl(array('admin/admin_tongji_pay'));
	
	}

	//职位发布统计
	function job_action(){

		$TongJi	=	$this -> MODEL('tongji');
		// 发布职位统计信息
		$Stats	=	$TongJi -> getTj('company_job',$_GET,'sdate');
		$List['add']['list']	=	$Stats['list'];
		$List['add']['name']	=	'发布职位';
		$AllNum['add']			=	$Stats['allnum'];
		// 更新职位数据统计
        $updateStats = $TongJi -> getTj('job_refresh_log',$_GET,'r_time');
        $List['update']['list'] = $updateStats['list'];
        $List['update']['name']	=	'刷新职位';
        $AllNum['update'] = $updateStats['allnum'];

		//统计发布职位最多企业
		$TopList['company']		=	$TongJi -> TopTen("company_job",$Stats['timedate']['DateWhere'],"uid","company");

		//根据发布职位量统计企业需求

		$CountTj	=	$TongJi -> DataTj('job',$Stats['timedate']['DateWhere'],'company_job','id');

		$this->yunset('toplist',$TopList);
		$this->yunset('counttj',$CountTj);
        $this->yunset('AllNum',$AllNum);
        $this->yunset('list',$List);
		$this->yunset('name','发布职位统计');

		$this->yuntpl(array('admin/admin_tongji_job'));

	}
	//简历数据统计
	function resume_action(){

		$TongJi	=	$this -> MODEL('tongji');
		// 发布简历数据添加
		$Stats	=	$TongJi -> getTj('resume_expect',$_GET,'ctime');
		$List['add']['list']	=	$Stats['list'];
        $List['add']['name']	=	'简历新增';
		$AllNum['add']			=	$Stats['allnum'];
		// 更新简历数据统计
		$updateStats = $TongJi -> getTj('resume_refresh_log',$_GET,'r_time');
        $List['update']['list'] = $updateStats['list'];
        $List['update']['name'] = '简历刷新';
        $AllNum['update'] = $updateStats['allnum'];
		//根据简历数据统计求职需求

		$CountTj	=	$TongJi -> DataTj('resume_expect',$Stats['timedate']['DateWhere'],'resume_expect','id');

		$this->yunset('counttj',$CountTj);


		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','发布简历统计');

		$this->yuntpl(array('admin/admin_tongji_resume'));
	
	}

	//会员办理统计
	function rating_action(){

		$TongJi	=	$this -> MODEL('tongji');
	
		$Stats	=	$TongJi -> getTj('company_statis',$_GET,'sdate');
		$List['all']['name']	=	'会员办理';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];
		
		
		//统计VIP企业数量
		$TopList['company']		=	$TongJi -> TopTen("userid_job",$Stats['timedate']['DateWhere'],"com_id","company");

		//统计发出最多申请的简历
		
		$TopList['resume']		=	$TongJi -> TopTen("userid_job",$Stats['timedate']['DateWhere'],"eid","expect");
		

		//根据邀请面试数量统计紧缺人才

		$CountTj	=	$TongJi -> DataTj('resume_expect',$Stats['timedate']['DateWhere'],'userid_job','eid');
		
		$this->yunset('toplist',$TopList);
		$this->yunset('counttj',$CountTj);


		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','会员办理统计');

		$this->yuntpl(array('admin/admin_tongji'));
	
	}
	//企业统计
	function company_action(){

		$TongJi	=	$this -> MODEL('tongji');
	
		$Stats	=	$TongJi -> getTj('member',$_GET,'reg_date',array('usertype'=>'2'));
		$List['all']['name']	=	'企业统计';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];
		
		//待审核企业
		$comStats	=	$TongJi -> getTj('member',$_GET,'reg_date',array('usertype'=>'2','status'=>'0'));
		$List['com']['name']	=	'待审核企业';
		$List['com']['list']	=	$comStats['list'];
		$AllNum['com']			=	$comStats['allnum'];


		$CountTj	=	$TongJi -> DataTj('company',$Stats['timedate']['DateWhere'],'member','uid');
		
		$this->yunset('counttj',$CountTj);


		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','企业统计');

		$this->yuntpl(array('admin/admin_tongji_company'));
	
	}
	//广告点击统计
	function ad_action(){

		$TongJi	=	$this -> MODEL('tongji');
	
		$Stats 	=	$TongJi -> getTj('adclick',$_GET,'addtime');
		$List['all']['name']	=	'点击量';
		$List['all']['list']	=	$Stats['list'];
		$AllNum['all']			=	$Stats['allnum'];
		
		//统计点击量最多的职位
		$TopList['ad']			=	$TongJi -> TopTen("adclick",$Stats['timedate']['DateWhere'],"aid","ad");

		$this->yunset('toplist',$TopList);
		//$CountTj	=	$TongJi	-> DataTj('ad',$Stats['timedate']['DateWhere'],'adclick','id');
		//$this->yunset('counttj',$CountTj);


		$this->yunset('AllNum',$AllNum);
		$this->yunset('list',$List);
		$this->yunset('name','广告点击统计');

		$this->yuntpl(array('admin/admin_tongji_ad'));
	
	}

	
}

?>