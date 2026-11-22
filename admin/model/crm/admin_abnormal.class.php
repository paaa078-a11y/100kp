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
class admin_abnormal_controller extends adminCommon{  
	
	
	
	// 获取个人信息
	private function getuser($list, $tongjiM){
	    $uids = array();
	    foreach ($list as $v){
	        $uids[] = $v['uid'];
	    }
	    $resume = $tongjiM->tjtotal('resume',array('uid'=>array('in', pylode(',', $uids))), array('field'=>'`uid`,`name`'));
	    $member = $tongjiM->tjtotal('member',array('uid'=>array('in', pylode(',', $uids))), array('field'=>'`uid`,`username`'));
	    
	    foreach ($list as $k=>$v){
	        foreach ($resume as $val){
	            if ($v['uid'] == $val['uid']){
	                $list[$k]['name'] = $val['name'];
	            }
	        }
	        foreach ($member as $mval){
	            if ($v['uid'] == $mval['uid']){
	                $list[$k]['username'] = $mval['username'];
	            }
	        }
	    }
	    return $list;
	}
	
	// 获取企业信息
	private function getcom($list, $tongjiM){
	    $uids = array();
	    $jobIdArr = array();
	    foreach ($list as $v){
	        $uids[] = $v['uid'];
	        $jobIdArr[] = $v['job_id'];
	    }
	    $com = $tongjiM->tjtotal('company',array('uid'=>array('in', pylode(',', $uids))), array('field'=>'`uid`,`name`, `login_date`'));
	    $member = $tongjiM->tjtotal('member',array('uid'=>array('in', pylode(',', $uids))), array('field'=>'`uid`,`username`'));

	    if ($jobIdArr) {
            $job = $tongjiM->tjtotal('company_job',array('id'=>array('in', pylode(',', $jobIdArr))), array('field'=>'`id`,`name`'));
        }

	    foreach ($list as $k=>$v){
	        foreach ($com as $val){
	            if ($v['uid'] == $val['uid']){
	                $list[$k]['name'] = $val['name'];
                    $list[$k]['login_date'] = date('Y-m-d H:i:s', $val['login_date']);
	            }
	        }
	        foreach ($member as $mval){
	            if ($v['uid'] == $mval['uid']){
	                $list[$k]['username'] = $mval['username'];
	            }
	        }

	        if (!empty($job)) {
                foreach ($job as $jval){
                    if ($v['job_id'] == $jval['id']){
                        $list[$k]['job_name'] = $jval['name'];
                    }
                }
            }
	    }
	    return $list;
	}
	private function timeNumber($a, $where, $column){
	    if ($a == 1){
	        // 昨天
	        $stime = strtotime('yesterday');
	        $etime = strtotime('today');
	        $where['PHPYUNBTWSTART_A'] = '';
	        $where[$column][] = array('>', $stime);
	        $where[$column][] = array('<', $etime);
	        $where['PHPYUNBTWEND_A'] = '';
	    }elseif ($a == 2){
	        // 今天
	        $stime = strtotime('today');
	        $where[$column] = array('>', $stime);
	    }elseif ($a == 3){
	        // 一周内
	        $stime = strtotime('-7 day');
	        $where[$column] = array('>', $stime);
	    }elseif ($a == 4){
	        // 一个月内
	        $stime = strtotime('-30 day');
	        $where[$column] = array('>', $stime);
	    }elseif ($a == 5){
	        // 三个月内
	        $stime = strtotime('-90 day');
	        $where[$column] = array('>', $stime);
	    }else{
	        if (stripos($a, '|') != false){
	            // 因分页链接处理过的页码
	            $date = explode('|', $a);
	            $stime = trim($date[0]);
	            $etime = trim($date[1]);
	        }else{
	            // 原始参数
	            $date = explode('~', $a);
	            $stime = strtotime(trim($date[0]));
	            $etime = strtotime(trim($date[1]));
	        }
	        
	        $where['PHPYUNBTWSTART_A'] = '';
	        $where[$column][] = array('>', $stime);
	        $where[$column][] = array('<', $etime);
	        $where['PHPYUNBTWEND_A'] = '';
	    }
	    return $where;
	}
	// 自定义事件格式化
	private function diytime($a){
	    
	    if (stripos($a, '|') != false){
	        // 因分页链接处理过的页码
	        $date = explode('|', $a);
	        $stime = trim($date[0]);
	        $etime = trim($date[1]);
	    }else{
	        // 原始参数
	        $date = explode('~', $a);
	        $stime = strtotime(trim($date[0]));
	        $etime = strtotime(trim($date[1]));
	    }
	    
	    return $stime.'|'.$etime;
	}
	// 异常统计分析详情-简历投递
	private function userid_job(){
	    
	    if ($_GET['usertype'] == 2){
	        if ($_GET['job_id']) {
                $where      =   array('job_id'=>$_GET['job_id']);
            } else {
                $where      =   array('com_id'=>$_GET['uid']);
            }
	    }else{
	        $where      =   array('uid'=>$_GET['uid']);
	    }
	    // 默认查一周内数据
	    $where = $this->timeNumber($_GET['time'], $where, 'datetime');
	    
	    $rows           =   array();
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('userid_job',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $jobM = $this->MODEL('job');
	        $list = $jobM->getSqJobList($where, array('utype' => 'simple'));
	        
	        $jobid = $uids = array();
	        foreach ($list as $v){
	            $uids[] = $v['uid'];
	            $jobid[] = $v['job_id'];
	        }
	        $tongjiM = $this->MODEL('tongji');
	        $resume  = $tongjiM->tjtotal('resume',array('uid'=>array('in', pylode(',', $uids))), array('field'=>'`uid`,`name`'));
	        if ($_GET['usertype'] == 1){
	            // 个人会员，要查投递职位的职位类别
	            $jobs = $jobM->getList(array('id'=>array('in', pylode(',', $jobid))), array('field'=>'id,job1'));
	        }
	        foreach ($list as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['comid'] = $v['com_id'];
	            $rows[$k]['jobname'] = $v['job_name'];
	            $rows[$k]['comname'] = $v['com_name'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
	            foreach ($resume as $val){
	                if ($v['uid'] == $val['uid']){
	                    $rows[$k]['name'] = $val['name'];
	                }
	            }
	            if (isset($jobs)){
	                foreach ($jobs['list'] as $jv){
	                    if ($v['job_id'] == $jv['id']){
	                        $rows[$k]['jobclassname'] = $jv['job_one_n'];
	                    }
	                }
	            }
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-职位浏览
	private function lookjob(){
	    
	    $rows           =   array();
	    $where          =   array('uid'=>$_GET['uid']);
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'datetime');
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('look_job',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $jobM = $this->MODEL('job');
	        $list = $jobM->getLookJobList($where, array('utype' => 'admin'));
	        
	        $ip = array();
	        foreach ($list as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['comid'] = $v['com_id'];
	            $rows[$k]['name'] = $v['name'];
	            $rows[$k]['jobname'] = $v['job_name'];
	            $rows[$k]['comname'] = $v['com_name'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
	            $rows[$k]['ip'] = $v['ip'];
	            $ip[] = $v['ip'];
	        }
	        $city = array();
	        $ip = array_unique($ip);
	        foreach ($ip as $v){
	            $city[$v] = $this->getCityByIp($v);
	        }
	        foreach ($list as $k=>$v){
	            if ($city[$v['ip']]){
	                $rows[$k]['city'] = $city[$v['ip']];
	            }
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-职位拨号记录
	private function jobtellog(){
	    
	    $rows           =   array();
	    if ($_GET['job_id']) {
            $where          =   array('jobid'=>$_GET['job_id']);
        } elseif ($_GET['com_id']) {
            $where          =   array('comid'=>$_GET['com_id']);
        } else {
            $where          =   array('uid'=>$_GET['uid']);
        }

	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'ctime');
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('job_tellog',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $jobM = $this->MODEL('job');
	        $list = $jobM->getTelLogs($where, array('utype' => 'admin'));
	        
	        $ip = array();
	        foreach ($list as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = $v['username'];
	            $rows[$k]['comid'] = $v['comid'];
	            $rows[$k]['jobname'] = $v['job_name'];
	            $rows[$k]['comname'] = $v['com_name'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['ctime']);
	            $rows[$k]['ip'] = $v['ip'];
	            $ip[] = $v['ip'];
	        }
	        $city = array();
	        $ip = array_unique($ip);
	        foreach ($ip as $v){
	            $city[$v] = $this->getCityByIp($v);
	        }
	        foreach ($list as $k=>$v){
	            if ($city[$v['ip']]){
	                $rows[$k]['city'] = $city[$v['ip']];
	            }
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-聊天记录
	private function chatlog(){
	    
	    if ($_GET['usertype'] == 2){
	        $where      =   array('fid'=>$_GET['uid'],'fusertype'=>$_GET['usertype'],'usertype'=>1);
	    }else{
	        $where      =   array('uid'=>$_GET['uid'],'usertype'=>$_GET['usertype'],'fusertype'=>2);
	    }
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'ntime');
	    $rows           =   array();
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('chat_friend',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('ntime,desc');
	        $where['limit']	   =  $pages['limit'];
	        $chatM = $this->MODEL('chat');
	        $list  = $chatM->getFriendList($where, array('utype' => 'admin'));
	        
	        foreach ($list as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = $v['countname'];
	            $rows[$k]['comid'] = $v['fid'];
	            $rows[$k]['jobname'] = '';
	            $rows[$k]['comname'] = $v['rcountname'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['ntime']);
	            $rows[$k]['beginid'] = $v['beginid'];
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-登录次数
	private function loginlog(){
	    
	    $rows           =   array();
	    $where          =   array(
	        'opera'    => 32,
	        'type'     => 1,
	        'content'  => array('like', '登录成功'),
	        'uid'      => $_GET['uid'],
	        'usertype' => $_GET['usertype']
	    );
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'ctime');
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('member_log',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $logM = $this->MODEL('log');
	        $list = $logM->getMemlogList($where, array('utype' => 'admin'));
	        
	        $ip = array();
	        foreach ($list as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['jobname'] = '';
	            $rows[$k]['comid'] = $v['uid'];
	            $rows[$k]['content'] = str_replace(array('登录成功','账号'), '', $v['content']);
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['ctime']);
	            $rows[$k]['ip'] = $v['ip'];
	            if ($_GET['usertype'] == 2){
	                $rows[$k]['name'] = '';
	                $rows[$k]['comname'] = $v['comname'];
	            }else{
	                $rows[$k]['name'] = $v['rname'];
	                $rows[$k]['comname'] = '';
	            }
	            $ip[] = $v['ip'];
	        }
	        $city = array();
	        $ip = array_unique($ip);
	        foreach ($ip as $v){
	            $city[$v] = $this->getCityByIp($v);
	        }
	        foreach ($list as $k=>$v){
	            if ($city[$v['ip']]){
	                $rows[$k]['city'] = $city[$v['ip']];
	            }
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-简历浏览
	private function lookresume(){
	    
	    $rows           =   array();
	    $where          =   array('com_id'=>$_GET['uid']);
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'datetime');
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('look_resume',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $lokM = $this->MODEL('lookresume');
	        $list = $lokM->getList($where, array('utype' => 'admin'));
	        
	        $ip = array();
	        foreach ($list['list'] as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = $v['username'];
	            $rows[$k]['comid'] = $v['com_id'];
	            $rows[$k]['jobname'] = $v['job_name'];
	            $rows[$k]['comname'] = $v['com_name'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
	            $rows[$k]['ip'] = $v['ip'];
	            $ip[] = $v['ip'];
	        }
	        $city = array();
	        $ip = array_unique($ip);
	        foreach ($ip as $v){
	            $city[$v] = $this->getCityByIp($v);
	        }
	        foreach ($list['list'] as $k=>$v){
	            if ($city[$v['ip']]){
	                $rows[$k]['city'] = $city[$v['ip']];
	            }
	        }
	    }
	    return $rows;
	}
    // 异常统计分析详情-简历未浏览
    private function notviewresume(){
	    $rows           =   array();
        $where          =   array('com_id'=>$_GET['uid'], 'is_browse' => 1);
        // 默认查一周内数据
        $where          =   $this->timeNumber($_GET['time'], $where, 'datetime');
        $urlarr        	=   $_GET;
        $urlarr['page']	=	'{{page}}';
        if (strlen($urlarr['time']) > 1){
            // 处理时间戳，防止页码url错乱
            $urlarr['time'] = $this->diytime($urlarr['time']);
        }
        $pageurl		=	Url($_GET['m'],$urlarr,'admin');

        //提取分页
        $pageM			=	$this  -> MODEL('page');
        $pages			=	$pageM -> pageList('userid_job',$where,$pageurl,$_GET['page']);

        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){

            $where['orderby']  =  array('id,desc');
            $where['limit']	   =  $pages['limit'];
            $jobM = $this->MODEL('job');
            $list = $jobM->getSqJobList($where, array('utype' => 'admin'));

            foreach ($list as $k=>$v){
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['name'];
                $rows[$k]['comid'] = $v['com_id'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
                $rows[$k]['body'] = $v['body'];
            }
        }
        return $rows;
    }
	// 异常统计分析详情-简历下载
	private function downresume(){
	    
	    $rows           =   array();
	    $where          =   array('comid'=>$_GET['uid']);
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'downtime');
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('down_resume',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $dowM = $this->MODEL('downresume');
	        $list = $dowM->getList($where, array('utype' => 'admin'));
	        
	        foreach ($list['list'] as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = $v['username'];
	            $rows[$k]['comid'] = $v['comid'];
	            $rows[$k]['jobname'] = $v['job_name'];
	            $rows[$k]['comname'] = $v['com_name'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['downtime']);
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-邀请面试
	private function yqms(){
	    
	    $rows           =   array();
	    $where          =   array('fid'=>$_GET['uid']);
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'datetime');
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('userid_msg',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $jobM = $this->MODEL('job');
	        $list = $jobM->getYqmsList($where, array('utype' => 'admin'));
	        
	        foreach ($list as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = $v['name'];
	            $rows[$k]['comid'] = $v['fid'];
	            $rows[$k]['jobname'] = $v['jobname'];
	            $rows[$k]['comname'] = $v['fname'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-发布职位
	private function addjob(){
	    
	    $rows           =   array();
	    $where          =   array('uid'=>$_GET['uid']);
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'sdate');
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('company_job',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $jobM = $this->MODEL('job');
	        $list = $jobM->getList($where);
	        
	        foreach ($list['list'] as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = '';
	            $rows[$k]['comid'] = $v['uid'];
	            $rows[$k]['jobname'] = $v['name'];
	            $rows[$k]['comname'] = $v['com_name'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['sdate']);
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-刷新职位
	private function refreshjob(){
	    
	    $rows           =   array();
	    $where          =   array('uid'=>$_GET['uid'], 'type'=>1);
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'r_time');
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('job_refresh_log',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $logM = $this->MODEL('log');
	        $list = $logM->getSxJobLogList($where);
	        
	        foreach ($list as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = '';
	            $rows[$k]['comid'] = $v['uid'];
	            $rows[$k]['jobname'] = $v['job_name'];
	            $rows[$k]['comname'] = $v['com_name'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['r_time']);
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-隐私号
	private function privacylog(){
	    
	    if ($_GET['usertype'] == 2){
	        $where      =   array('comid'=>$_GET['uid'],'eid'=>array('>', 0));
	    }else{
	        $where      =   array('uid'=>$_GET['uid'],'jobid'=>array('>', 0));
	    }
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'ctime');
	    $rows           =   array();
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('privacy_log',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $priM = $this->MODEL('privacy');
	        $list = $priM->getList($where);
	        
	        foreach ($list['list'] as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = $v['sname'];
	            $rows[$k]['comid'] = $v['comid'];
	            $rows[$k]['comname'] = $v['fname'];
	            $rows[$k]['jobname'] = $v['jobname'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['ctime']);
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-IP活跃次数
	private function weblogtj(){
	    
	    $arr            =   explode('_', $_GET['ip']);
	    $rows           =   array();
	    $where          =   array(
	        'ip'       => implode('.', $arr),
	        'usertype' => $_GET['usertype'],
	        'groupby'  => 'uid',
	        'orderby'  => array('num')
	    );
	    // 默认查一周内数据
	    $where = $this->timeNumber($_GET['time'], $where, 'time');
	    
	    $wblM = $this->MODEL('weblog');
	    $list = $wblM->getWebLogList($where, array('field'=>'COUNT(*) AS `num`,`uid`,`usertype`,`ip`,`time`'));
	    
	    $ip = array();
	    foreach ($list as $k=>$v){
	        $rows[$k]['uid'] = $v['uid'];
	        $rows[$k]['jobname'] = '';
	        $rows[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
	        $rows[$k]['ip'] = $v['ip'];
	        $rows[$k]['num'] = $v['num'];
	        if ($_GET['usertype'] == 2){
	            $rows[$k]['name'] = '';
	            $rows[$k]['comname'] = $v['name_n'];
	            $rows[$k]['comid'] = $v['uid'];
	        }else{
	            $rows[$k]['name'] = $v['name_n'];
	            $rows[$k]['comname'] = '';
	        }
	        $ip[] = $v['ip'];
	    }
	    $city = array();
	    $ip = array_unique($ip);
	    foreach ($ip as $v){
	        $city[$v] = $this->getCityByIp($v);
	    }
	    foreach ($list as $k=>$v){
	        if ($city[$v['ip']]){
	            $rows[$k]['city'] = $city[$v['ip']];
	        }
	    }
	    return $rows;
	}
	// 个人、企业删除投递简历记录
	private function sqdel(){
	    
	    if ($_GET['usertype'] == 2){
	        $where      =   array('isdel'=>2);
	    }else{
	        $where      =   array('isdel'=>1);
	    }
	    $where['PHPYUNBTWSTART_A'] = '';
	    $where['com_id'] = array('=', $_GET['uid']);
	    $where['uid']    = array('=', $_GET['uid'], 'OR');
	    $where['PHPYUNBTWEND_A'] = '';
	    // 默认查一周内数据
	    $where = $this->timeNumber($_GET['time'], $where, 'datetime');
	    
	    $rows           =   array();
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('userid_job',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $jobM = $this->MODEL('job');
	        $list = $jobM->getSqJobList($where, array('utype' => 'admin'));
	        
	        foreach ($list as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = $v['name'];
	            $rows[$k]['comid'] = $v['com_id'];
	            $rows[$k]['jobname'] = $v['job_name'];
	            $rows[$k]['comname'] = $v['com_name'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
	        }
	    }
	    return $rows;
	}
	// 异常统计分析详情-删除简历下载记录
	private function downdel(){
	    
	    $rows           =   array();
	    $where          =   array('comid'=>$_GET['uid'],'isdel'=>2);
	    // 默认查一周内数据
	    $where          =   $this->timeNumber($_GET['time'], $where, 'downtime');
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    if (strlen($urlarr['time']) > 1){
	        // 处理时间戳，防止页码url错乱
	        $urlarr['time'] = $this->diytime($urlarr['time']);
	    }
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('down_resume',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        $where['orderby']  =  array('id,desc');
	        $where['limit']	   =  $pages['limit'];
	        $dowM = $this->MODEL('downresume');
	        $list = $dowM->getList($where, array('utype' => 'admin'));
	        
	        foreach ($list['list'] as $k=>$v){
	            $rows[$k]['uid'] = $v['uid'];
	            $rows[$k]['name'] = $v['username'];
	            $rows[$k]['comid'] = $v['comid'];
	            $rows[$k]['jobname'] = $v['job_name'];
	            $rows[$k]['comname'] = $v['com_name'];
	            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['downtime']);
	        }
	    }
	    return $rows;
	}
	private function datatime($a){
	    
	    if ($a == 1){
	        return '昨天';
	    }elseif ($a == 2){
	        return '今天';
	    }elseif ($a == 3){
	        return '一周内';
	    }elseif ($a == 4){
	        return '一个月内';
	    }elseif ($a == 5){
	        return '三个月内';
	    }else{
	        if (stripos($a, '|') != false){
	            // 因分页链接处理过的页码
	            $date = explode('|', $a);
	            $stime = date('Y-m-d H:i:s',trim($date[0]));
	            $etime = date('Y-m-d H:i:s',trim($date[1]));
	            return $stime .' ~ '.$etime;
	        }else{
	            // 原始参数
	            return $a;
	        }
	    }
	}
	// 通过IP获取地区
	private function getCityByIp($ip){
	    
	    $local = '';
	    $url = "https://whois.pconline.com.cn/ipJson.jsp?ip=" . $ip . "&json=true";
	    $ret = $this->transCodeToUTF8(curlGet($url));
	    
	    if (false !== $ret) {
	        
	        $output =   json_decode($ret, true);
	        if (isset($output['city']) && $output['city']) {
	            $local = str_replace(array('市', '县', '区'), '', $output['city']);
	        } else if (isset($output['pro']) && $output['pro']) {
	            $local = str_replace('省', '', $output['pro']);
	        }
	    }
	    return $local;
	}
	// 转utf8
	private function transCodeToUTF8($str)
	{
	    $encode = mb_detect_encoding($str, ['ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5', 'EUC-CN']);
	    return 'UTF-8' === $encode ? $str : mb_convert_encoding($str, 'UTF-8', $encode);
	}
}
?>