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
	function index_action(){
	    
	    $tongjiM = $this->MODEL('tongji');
	    $row   = $tongjiM->getAbnormal(array('name'=>'tdnum'));
	    
	    $time = 3;
	    if (!empty($row) && ($row['ctime'] + 1800) > time()){
	        // 缓存没过期
	        $time = $row['time'];
	        $this->yunset('ctime', date('Y-m-d H:i:s', $row['ctime']));
	    }
	    $this->yunset('time', $time);
	    
		$this->yuntpl(array('admin/admin_abnormal'));
	}
	function listbox_action(){
	    
	    $this->yuntpl(array('admin/admin_abnormal_list'));
	}
	// 异常统计分析详情
	function detail_action(){
	    
	    if ($_GET['type'] == 'tdnum' || $_GET['type'] == 'tdclass' || $_GET['type'] == 'sqjob' || $_GET['type'] == 'jobsq'){
	        $rows = $this->userid_job();
	    }elseif ($_GET['type'] == 'usersqdel' || $_GET['type'] == 'sqdel'){
	        $rows = $this->sqdel();
	    }elseif ($_GET['type'] == 'lookjob'){
	        $rows = $this->lookjob();
	    }elseif ($_GET['type'] == 'tellog' || $_GET['type'] == 'jobtel' || $_GET['type'] == 'companytel'){
	        $rows = $this->jobtellog();
	    }elseif ($_GET['type'] == 'chatlog' || $_GET['type'] == 'comchatlog'){
	        $rows = $this->chatlog();
	    }elseif ($_GET['type'] == 'loginlog' || $_GET['type'] == 'comloginlog'){
	        $rows = $this->loginlog();
	    }elseif ($_GET['type'] == 'lookresume'){
            $rows = $this->lookresume();
        }elseif ($_GET['type'] == 'notviewresume'){
            $rows = $this->notviewresume();
        }elseif ($_GET['type'] == 'downresume'){
	        $rows = $this->downresume();
	    }elseif ($_GET['type'] == 'downdel'){
	        $rows = $this->downdel();
	    }elseif ($_GET['type'] == 'yqms'){
	        $rows = $this->yqms();
	    }elseif ($_GET['type'] == 'addjob'){
	        $rows = $this->addjob();
	    }elseif ($_GET['type'] == 'refreshjob'){
	        $rows = $this->refreshjob();
	    }elseif ($_GET['type'] == 'privacylog' || $_GET['type'] == 'jobprivacylog'){
	        $rows = $this->privacylog();
	    }elseif ($_GET['type'] == 'weblog' || $_GET['type'] == 'comweblog'){
	        $rows = $this->weblogtj();
	    }
	    $this->yunset(array('rows'=>$rows));
	    $this->yuntpl(array('admin/admin_abnormal_detail'));
	}
	
	// 简历投递数量
	function tdnum_action(){
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'tdnum');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('groupby'=>'uid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
	        
	        $rows = $tongjiM->tjtotal('userid_job',$where, array('field'=>'COUNT(*) AS num,uid'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'tdnum', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getuser($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 简历投递分类
	function tdclass_action(){
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'tdclass');
	    $rows    = $gad['list'];
	    if (empty($rows)|| isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array();
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
	        // 查询简历投递记录
	        $ujobs = $tongjiM->tjtotal('userid_job',$where, array('field'=>'uid,job_id'));
	        // 根据投递记录查询所有被投递的职位
	        $jobid = $sortlist = $classlist = array();
	        foreach ($ujobs as $v){
	            $jobid[] = $v['job_id'];
	        }
	        $jobid = array_unique($jobid);
	        $jobs = $tongjiM->tjtotal('company_job',array('id'=>array('in', pylode(',', $jobid))), array('field'=>'id,job1,job1_son,job_post'));
	        // 处理所有被投递的职位数据，获得职位类别数组。按一级类别处理
	        foreach ($ujobs as $k=>$v){
	            foreach ($jobs as $val){
	                if ($v['job_id'] == $val['id']){
	                    $classlist[$v['uid']][] = $val['job1'];
	                }
	            }
	        }
	        // 统计求职者投递分类数量
	        foreach ($classlist as $k=>$v){
	            $newclass = array_unique($v);
	            $sortlist[$k] = count($newclass);
	        }
	        // 调整排序，得到按投递职位类别总量排序的数组
	        arsort($sortlist);
	        // 按需展示条数获取统计数据
	        $rows = array();
	        $i = 0;
	        foreach ($sortlist as $k=>$v){
	            if ($i < $limit){
	                $rows[] = array('uid'=>$k, 'num'=>$v);
	                $i++;
	            }else{
	                break;
	            }
	        }
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'tdclass', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    $res['list']  = $this->getuser($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 职位浏览
	function lookjob_action(){
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'lookjob');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('groupby'=>'uid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('look_job',$where, array('field'=>'COUNT(*) AS num,uid'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'lookjob', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getuser($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 职位拨号
	function tellog_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'tellog');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('uid'=>array('>', 0),'groupby'=>'uid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'ctime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('job_tellog',$where, array('field'=>'COUNT(*) AS num,uid'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'tellog', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getuser($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 职位隐私号
	function privacylog_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'privacylog');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('jobid'=>array('>', 0),'groupby'=>'uid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'ctime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('privacy_log',$where, array('field'=>'COUNT(*) AS num,uid'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'privacylog', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getuser($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 直聊人数
	function chatlog_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'chatlog');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('usertype'=>$_POST['usertype'],'fusertype'=>2,'groupby'=>'uid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'ntime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('chat_friend',$where, array('field'=>'COUNT(*) AS `num`,`uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'chatlog', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getuser($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 登录次数
	function loginlog_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'loginlog');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array(
	            'opera'    => 32,
	            'type'     => 1,
	            'content'  => array('like', '登录'),
	            'usertype' => 1,
	            'groupby'  => '`uid`',
	            'orderby'  => array('num'),
	            'limit'    => $limit
	        );
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'ctime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('member_log',$where, array('field'=>'COUNT(*) AS `num`,`uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'loginlog', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getuser($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 行为轨迹
	function weblog_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'weblog');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array(
	            'usertype' => 1,
	            'groupby'  => '`ip`',
	            'orderby'  => array('num'),
	            'limit'    => $limit
	        );

	        if (trim($_POST['ip'])) {
                $where['ip'] = trim($_POST['ip']);
            }

	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'time');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('web_log',$where, array('field'=>'COUNT(*) AS `num`,`ip`'));
	        
	        foreach ($rows as $k=>$v){
	            $rows[$k]['city'] = $this->getCityByIp($v['ip']);
	            // 按IP查询用户量
	            $logwhere = array(
	                'ip'       => $v['ip'],
	                'usertype' => 1,
	                'groupby'  => 'uid'
	            );
	            $logwhere = $this->timeNumber($_POST['time'], $logwhere, 'time');
	            $logs = $tongjiM->tjtotal('web_log',$logwhere, array('field'=>'`uid`'));
	            $rows[$k]['usernum'] = count($logs);
	        }
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'weblog', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    $res['list']  = $rows;
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 个人删除投递简历记录
	function usersqdel_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'usersqdel');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('isdel'=>1,'groupby'=>'uid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
	        
	        $rows = $tongjiM->tjtotal('userid_job',$where, array('field'=>'COUNT(*) AS num,uid'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'usersqdel', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getuser($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
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
	function com_action(){
	    
	    $tongjiM = $this->MODEL('tongji');
	    $row   = $tongjiM->getAbnormal(array('name'=>'lookresume'));
	    
	    $time = 3;
	    if (!empty($row) && ($row['ctime'] + 1800) > time()){
	        // 缓存没过期
	        $time = $row['time'];
	    }
	    $this->yunset(array('time'=>$time,'ctime'=>date('Y-m-d H:i:s')));
	    
	    $this->yuntpl(array('admin/admin_abnormal_com'));
	}
	// 简历浏览
	function lookresume_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'lookresume');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('groupby'=>'com_id', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('look_resume',$where, array('field'=>'COUNT(*) AS `num`,`com_id` AS `uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'], 'lookresume', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
    // 简历未浏览
    function notviewresume_action(){

        // 先查缓存数据
        $tongjiM = $this->MODEL('tongji');
        $gad     = $tongjiM->getAbnormalData($_POST['time'],'notviewresume');
        $rows    = $gad['list'];
        if (empty($rows) || isset($_POST['list'])){
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('is_browse'=>1, 'groupby'=>'com_id', 'orderby'=>array('num'),'limit'=>$limit);
            // 默认查一周内数据
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');

            $tongjiM = $this->MODEL('tongji');
            $rows = $tongjiM->tjtotal('userid_job',$where, array('field'=>'COUNT(*) AS `num`,`com_id` AS `uid`'));
            if (!empty($rows) && !isset($_POST['list'])){
                $tongjiM->setabnormal($_POST['time'],'notviewresume', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list']  = $this->getcom($rows, $tongjiM);
        if (isset($gad['ctime'])){
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }


        echo json_encode($res);
    }
	// 简历下载
	function downresume_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'downresume');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('groupby'=>'comid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'downtime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('down_resume',$where, array('field'=>'COUNT(*) AS `num`,`comid` AS `uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'downresume', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 简历隐私号
	function jobprivacylog_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'jobprivacylog');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('eid'=>array('>', 0),'groupby'=>'comid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'ctime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('privacy_log',$where, array('field'=>'COUNT(*) AS `num`,`comid` AS `uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'jobprivacylog', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
    // 被投递岗位
    function jobsq_action(){

        // 先查缓存数据
        $tongjiM = $this->MODEL('tongji');
        $gad     = $tongjiM->getAbnormalData($_POST['time'],'jobsq');
        $rows    = $gad['list'];
        if (empty($rows) || isset($_POST['list'])){
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby'=>'job_id', 'orderby'=>array('num'),'limit'=>$limit);
            // 默认查一周内数据
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');

            $tongjiM = $this->MODEL('tongji');
            $rows = $tongjiM->tjtotal('userid_job',$where, array('field'=>'COUNT(*) AS `num`,`com_id` AS `uid`,`job_id`'));
            if (!empty($rows) && !isset($_POST['list'])){
                $tongjiM->setabnormal($_POST['time'],'jobsq', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list']  = $this->getcom($rows, $tongjiM);
        if (isset($gad['ctime'])){
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        echo json_encode($res);
    }
    // 被拨号岗位
    function jobtel_action(){

        // 先查缓存数据
        $tongjiM = $this->MODEL('tongji');
        $gad     = $tongjiM->getAbnormalData($_POST['time'],'jobtel');
        $rows    = $gad['list'];
        if (empty($rows) || isset($_POST['list'])){
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('jobid' => array('notnull'),'groupby'=>'jobid', 'orderby'=>array('num'),'limit'=>$limit);
            // 默认查一周内数据
            $where = $this->timeNumber($_POST['time'], $where, 'ctime');

            $tongjiM = $this->MODEL('tongji');
            $rows = $tongjiM->tjtotal('job_tellog',$where, array('field'=>'COUNT(*) AS `num`,`comid` AS `uid`,`jobid` AS `job_id`'));
            if (!empty($rows) && !isset($_POST['list'])){
                $tongjiM->setabnormal($_POST['time'],'jobtel', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list']  = $this->getcom($rows, $tongjiM);
        if (isset($gad['ctime'])){
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        echo json_encode($res);
    }
    // 被拨号企业
    function companytel_action(){

        // 先查缓存数据
        $tongjiM = $this->MODEL('tongji');
        $gad     = $tongjiM->getAbnormalData($_POST['time'],'companytel');
        $rows    = $gad['list'];
        if (empty($rows) || isset($_POST['list'])){
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby'=>'comid', 'orderby'=>array('num'),'limit'=>$limit);
            // 默认查一周内数据
            $where = $this->timeNumber($_POST['time'], $where, 'ctime');

            $tongjiM = $this->MODEL('tongji');
            $rows = $tongjiM->tjtotal('job_tellog',$where, array('field'=>'COUNT(*) AS `num`,`comid` AS `uid`,`jobid` AS `job_id`'));
            if (!empty($rows) && !isset($_POST['list'])){
                $tongjiM->setabnormal($_POST['time'],'companytel', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list']  = $this->getcom($rows, $tongjiM);
        if (isset($gad['ctime'])){
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        echo json_encode($res);
    }
	// 邀请面试
	function yqms_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'yqms');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('groupby'=>'fid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('userid_msg',$where, array('field'=>'COUNT(*) AS `num`,`fid` AS `uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'yqms', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 发布职位
	function addjob_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'addjob');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('groupby'=>'uid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'sdate');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('company_job',$where, array('field'=>'COUNT(*) AS num,uid'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'addjob', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 刷新职位
	function refreshjob_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'refreshjob');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('type'=>1,'groupby'=>'uid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'r_time');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('job_refresh_log',$where, array('field'=>'COUNT(*) AS num,uid'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'refreshjob', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 收到简历
	function sqjob_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'sqjob');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('groupby'=>'com_id', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('userid_job',$where, array('field'=>'COUNT(*) AS `num`,`com_id` AS `uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'sqjob', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 直聊人数
	function comchatlog_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'comchatlog');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('usertype'=>$_POST['usertype'],'fusertype'=>1,'groupby'=>'uid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'ntime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('chat_friend',$where, array('field'=>'COUNT(*) AS `num`,`uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'comchatlog', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 登录次数
	function comloginlog_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'comloginlog');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array(
	            'opera'    => 32,
	            'type'     => 1,
	            'content'  => array('like', '登录成功'),
	            'usertype' => 2,
	            'groupby'  => '`uid`',
	            'orderby'  => array('num'),
	            'limit'    => $limit
	        );
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'ctime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('member_log',$where, array('field'=>'COUNT(*) AS `num`,`uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'comloginlog', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 行为轨迹
	function comweblog_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'comweblog');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array(
	            'usertype' => 2,
	            'groupby'  => '`ip`',
	            'orderby'  => array('num'),
	            'limit'    => $limit
	        );

            if (trim($_POST['ip'])) {
                $where['ip'] = trim($_POST['ip']);
            }

	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'time');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('web_log',$where, array('field'=>'COUNT(*) AS `num`,`ip`'));
	        
	        foreach ($rows as $k=>$v){
	            $rows[$k]['city'] = $this->getCityByIp($v['ip']);
	            // 按IP查询用户量
	            $logwhere = array(
	                'ip'       => $v['ip'],
	                'usertype' => 2,
	                'groupby'  => 'uid'
	            );
	            $logwhere = $this->timeNumber($_POST['time'], $logwhere, 'time');
	            $logs = $tongjiM->tjtotal('web_log',$logwhere, array('field'=>'`uid`'));
	            $rows[$k]['usernum'] = count($logs);
	        }
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'comweblog', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    
	    $res['list']  = $rows;
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 企业删除投递简历记录
	function comsqdel_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'comsqdel');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('isdel'=>2,'groupby'=>'com_id', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
	        
	        $rows = $tongjiM->tjtotal('userid_job',$where, array('field'=>'COUNT(*) AS num,`com_id` AS `uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'comsqdel', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
	}
	// 删除简历下载记录
	function downdel_action(){
	    
	    // 先查缓存数据
	    $tongjiM = $this->MODEL('tongji');
	    $gad     = $tongjiM->getAbnormalData($_POST['time'],'downdel');
	    $rows    = $gad['list'];
	    if (empty($rows) || isset($_POST['list'])){
	        $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
	        $where = array('isdel'=>2,'groupby'=>'comid', 'orderby'=>array('num'),'limit'=>$limit);
	        // 默认查一周内数据
	        $where = $this->timeNumber($_POST['time'], $where, 'downtime');
	        
	        $tongjiM = $this->MODEL('tongji');
	        $rows = $tongjiM->tjtotal('down_resume',$where, array('field'=>'COUNT(*) AS `num`,`comid` AS `uid`'));
	        if (!empty($rows) && !isset($_POST['list'])){
	            $tongjiM->setabnormal($_POST['time'],'downdel', $rows);
	            $gad['ctime'] = time();
	        }
	    }
	    $res['list']  = $this->getcom($rows, $tongjiM);
	    if (isset($gad['ctime'])){
	        $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
	    }
	    
	    echo json_encode($res);
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
	// 详情页求职者数据统计弹窗
	function userstatis_action(){
	    
	    $tongjiM = $this->MODEL('tongji');
	    
	    $user = $tongjiM->tjtonce('resume',array('uid'=>$_POST['uid']), array('field'=>'`uid`,`name`,`telphone`,`def_job`'));
	    $member = $tongjiM->tjtonce('member',array('uid'=>$_POST['uid']), array('field'=>'`uid`,`username`,`status`'));
	    
	    $res = array(
	        'eid' => $user['def_job'],
	        'uid' => $user['uid'],
	        'name' => $user['name'],
	        'username' => $member['username']
	    );
	    if (!empty($user['telphone'])){
	        $res['link'] = $user['telphone'];
	    }
	    if ($member['status'] == 1){
	        $res['status'] = '已审核';
	    }elseif ($member['status'] == 2){
	        $res['status'] = '已锁定';
	    }
	    $res['time'] = $this->datatime($_POST['time']);
	    // 简历投递数量
	    $tdnumw = array('uid'=>$_POST['uid']);
	    $tdnumw = $this->timeNumber($_POST['time'], $tdnumw, 'datetime');
	    $res['tdnum_num'] = $tongjiM->tjtotalnum('userid_job',$tdnumw);
	    // 简历投递分类
	    $tdclassw = array('uid'=>$_POST['uid']);
	    $tdclassw = $this->timeNumber($_POST['time'], $tdclassw, 'datetime');
	    $ujobs = $tongjiM->tjtotal('userid_job',$tdclassw, array('field'=>'uid,job_id'));
	     // 根据投递记录查询所有被投递的职位
	    $jobid = $classlist = array();
	    foreach ($ujobs as $v){
	        $jobid[] = $v['job_id'];
	    }
	    $jobid = array_unique($jobid);
	    $jobs = $tongjiM->tjtotal('company_job',array('id'=>array('in', pylode(',', $jobid))), array('field'=>'id,job1,job1_son,job_post'));
	     // 处理所有被投递的职位数据，获得职位类别数组。按一级类别处理
	    foreach ($ujobs as $k=>$v){
	        foreach ($jobs as $val){
	            if ($v['job_id'] == $val['id']){
	                $classlist[] = $val['job1'];
	            }
	        }
	    }
	     // 统计求职者投递分类数量
	    $newclass = array_unique($classlist);
	    $res['tdclass_num'] = count($newclass);
	    // 删除简历投递记录
	    $usersqdelw = array('uid'=>$_POST['uid'],'isdel'=>1);
	    $usersqdelw = $this->timeNumber($_POST['time'], $usersqdelw, 'datetime');
	    $res['usersqdel_num'] = $tongjiM->tjtotalnum('userid_job',$usersqdelw);
	    // 职位浏览
	    $lookjobw = array('uid'=>$_POST['uid']);
	    $lookjobw = $this->timeNumber($_POST['time'], $lookjobw, 'datetime');
	    $res['lookjob_num'] = $tongjiM->tjtotalnum('look_job',$lookjobw);
	    // 职位拨号
	    $tellogw = array('uid'=>$_POST['uid']);
	    $tellogw = $this->timeNumber($_POST['time'], $tellogw, 'ctime');
	    $res['tellog_num'] = $tongjiM->tjtotalnum('job_tellog',$tellogw);
	    // 直聊人数
	    $chatlogw = array('uid'=>$_POST['uid'],'usertype'=>1,'fusertype'=>2);
	    $chatlogw = $this->timeNumber($_POST['time'], $chatlogw, 'ntime');
	    $res['chatlog_num'] = $tongjiM->tjtotalnum('chat_friend',$chatlogw);
	    // 登录次数
	    $loginlogw = array(
	        'uid'      => $_POST['uid'],
	        'opera'    => 0,
	        'type'     => 0,
	        'content'  => array('like', '登录成功'),
	        'usertype' => 1
	    );
	    $loginlogw = $this->timeNumber($_POST['time'], $loginlogw, 'ctime');
	    $res['loginlog_num'] = $tongjiM->tjtotalnum('member_log',$loginlogw);
	    // 职位隐私号
	    $privacylogw = array('uid'=>$_POST['uid'],'jobid'=>array('>', 0));
	    $privacylogw = $this->timeNumber($_POST['time'], $privacylogw, 'ctime');
	    $res['privacylog_num'] = $tongjiM->tjtotalnum('privacy_log',$privacylogw);
	    // IP活跃次数
	    $weblogw = array('uid'=>$_POST['uid'], 'usertype'=>1);
	    $weblogw = $this->timeNumber($_POST['time'], $weblogw, 'time');
	    $res['weblog_num'] = $tongjiM->tjtotalnum('web_log',$weblogw);
	    
	    echo json_encode($res);
	}
	// 详情页企业数据统计弹窗
	function comstatis_action(){
	    
	    $tongjiM = $this->MODEL('tongji');
	    
	    $com = $tongjiM->tjtonce('company',array('uid'=>$_POST['uid']), array('field'=>'`uid`,`name`,`linkphone`,`linktel`,`r_status`'));
	    $member = $tongjiM->tjtonce('member',array('uid'=>$_POST['uid']), array('field'=>'`uid`,`username`'));
	    
	    $res = array(
	        'uid' => $com['uid'],
	        'name' => $com['name'],
	        'username' => $member['username']
	    );
	    if (!empty($com['linktel'])){
	        $res['link'] = $com['linktel'];
	    }elseif (!empty($com['linkphone'])){
	        $res['link'] = $com['linkphone'];
	    }
	    if ($com['r_status'] == 0){
	        $res['status'] = '未审核';
	    }elseif ($com['r_status'] == 1){
	        $res['status'] = '已审核';
	    }elseif ($com['r_status'] == 2){
	        $res['status'] = '已锁定';
	    }elseif ($com['r_status'] == 3){
	        $res['status'] = '审核未通过';
	    }
	    $res['time'] = $this->datatime($_POST['time']);
	    $res['url'] = Url('company', array('c'=>'show','id'=>$com['uid']));
	    // 简历浏览
	    $lookresumew = array('com_id'=>$_POST['uid']);
	    $lookresumew = $this->timeNumber($_POST['time'], $lookresumew, 'datetime');
	    $res['lookresume_num'] = $tongjiM->tjtotalnum('look_resume',$lookresumew);
	    // 简历下载
	    $downresumew = array('comid'=>$_POST['uid']);
	    $downresumew = $this->timeNumber($_POST['time'], $downresumew, 'downtime');
	    $res['downresume_num'] = $tongjiM->tjtotalnum('down_resume',$downresumew);
	    // 删除简历下载记录
	    $downdelw = array('comid'=>$_POST['uid'],'isdel'=>2);
	    $downdelw = $this->timeNumber($_POST['time'], $downdelw, 'downtime');
	    $res['downdel_num'] = $tongjiM->tjtotalnum('down_resume',$downdelw);
	    // 邀请面试
	    $yqmsw = array('fid'=>$_POST['uid']);
	    $yqmsw = $this->timeNumber($_POST['time'], $yqmsw, 'datetime');
	    $res['yqms_num'] = $tongjiM->tjtotalnum('userid_msg',$yqmsw);
	    // 发布职位
	    $addjobw = array('uid'=>$_POST['uid']);
	    $addjobw = $this->timeNumber($_POST['time'], $addjobw, 'sdate');
	    $res['addjob_num'] = $tongjiM->tjtotalnum('company_job',$addjobw);
	    // 刷新职位
	    $refreshjobw = array('uid'=>$_POST['uid'],'usertype'=>2);
	    $refreshjobw = $this->timeNumber($_POST['time'], $refreshjobw, 'r_time');
	    $res['refreshjob_num'] = $tongjiM->tjtotalnum('job_refresh_log',$refreshjobw);
	    // 收到简历
	    $sqjobw = array('com_id'=>$_POST['uid']);
	    $sqjobw = $this->timeNumber($_POST['time'], $sqjobw, 'datetime');
	    $res['sqjob_num'] = $tongjiM->tjtotalnum('userid_job',$sqjobw);
	    // 删除收到简历记录
	    $sqdelw = array('com_id'=>$_POST['uid'],'isdel'=>2);
	    $sqdelw = $this->timeNumber($_POST['time'], $sqdelw, 'datetime');
	    $res['sqdel_num'] = $tongjiM->tjtotalnum('userid_job',$sqdelw);
	    // 直聊人数
	    $chatlogw = array('uid'=>$_POST['uid'],'usertype'=>2,'fusertype'=>1);
	    $chatlogw = $this->timeNumber($_POST['time'], $chatlogw, 'ntime');
	    $res['chatlog_num'] = $tongjiM->tjtotalnum('chat_friend',$chatlogw);
	    // 登录次数
	    $loginlogw = array(
	        'uid'      => $_POST['uid'],
	        'opera'    => 0,
	        'type'     => 0,
	        'content'  => array('like', '登录成功'),
	        'usertype' => 2
	    );
	    $loginlogw = $this->timeNumber($_POST['time'], $loginlogw, 'ctime');
	    $res['loginlog_num'] = $tongjiM->tjtotalnum('member_log',$loginlogw);
	    // 简历隐私号
	    $privacylogw = array('comid'=>$_POST['uid'],'eid'=>array('>', 0));
	    $privacylogw = $this->timeNumber($_POST['time'], $privacylogw, 'ctime');
	    $res['privacylog_num'] = $tongjiM->tjtotalnum('privacy_log',$privacylogw);
	    // IP活跃次数
	    $weblogw = array('uid'=>$_POST['uid'], 'usertype'=>2);
	    $weblogw = $this->timeNumber($_POST['time'], $weblogw, 'time');
	    $res['weblog_num'] = $tongjiM->tjtotalnum('web_log',$weblogw);
	    
	    echo json_encode($res);
	}
	// 锁定账号
	function lock_action(){
	    
	    $userinfoM  =  $this -> MODEL('userinfo');
	    
	    $post       =  array(
	        'status'     =>  2,
	        'lock_info'  =>  trim($_POST['lock_info'])
	    );
	    
	    $return = $userinfoM->lock(array('uid'=>intval($_POST['uid'])),array('post'=>$post));
	    
	    $this->layer_msg($return['msg'], $return['errcode']);
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