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
class specialjob_model extends model{
	/**
	 * 通过关联词搜索职位专题
	 */
    function getListByKeyword($keyword = ''){
        
        $time  = time();
        $where = array(
            'display' => 1,
            'keyword' => array('findin', $keyword)
        );
        
        $row = $this->getInfo($where);
        
        return $row;
    }

    /**
     * 自动搜索职位专题职位
     * @return string
     */
    function autoRefreshSpecialJobs($special){
        $time = time();
        $jobWhere = array();
        if($special['job_class']){
            $jobWhere['PHPYUNBTWSTART_A'] = '';
            $jobWhere['job1']	        =	array('findin', $special['job_class']);
            $jobWhere['job1_son']	    =	array('findin', $special['job_class'], 'OR');
            $jobWhere['job_post']	    =	array('findin', $special['job_class'], 'OR');
            $jobWhere['PHPYUNBTWEND_A']  = '';
        }
        if($special['city_class']){
            $jobWhere['PHPYUNBTWSTART_B'] = '';
            $jobWhere['provinceid']	=	array('findin', $special['city_class']);
            $jobWhere['cityid']	    =	array('findin', $special['city_class'], 'OR');
            $jobWhere['three_cityid']	=	array('findin', $special['city_class'], 'OR');
            $jobWhere['PHPYUNBTWEND_B']  = '';
        }
        if($special['edu']){
            include_once PLUS_PATH.'com.cache.php';
            $eduArr  = $comdata['job_edu'];
            $eduSort = 0;
            $eduIds  = array();
            // 职位搜索，排序比搜索小的都符合条件。如搜“硕士”，类别排序小于等于“硕士”排序的（要排除不限）都符合
            foreach ($eduArr as $k => $v) {
                if ($v == $special['edu'] && $comclass_name[$v] != '不限') {
                    $eduSort = $k;
                    break;
                }
            }
            foreach ($eduArr as $k => $v) {
                if ($k <= $eduSort && $comclass_name[$v] != '不限') {
                    $eduIds[] = $v;
                }
            }
            if (!empty($eduIds)) {
                $jobWhere['edu'] = array('in', pylode(',', $eduIds));
            }
        }
        if($special['exp']){
            include_once PLUS_PATH.'com.cache.php';
            $expArr  = $comdata['job_exp'];
            $expSort = 0;
            $expIds  = array();
            // 职位搜索，排序比搜索小的都符合条件。如搜“五年”，类别排序小于等于“五年”排序的（要排除不限）都符合
            foreach ($expArr as $k => $v) {
                if ($v == $special['exp'] && $comclass_name[$v] != '不限') {
                    $expSort = $k;
                    break;
                }
            }
            foreach ($expArr as $k => $v) {
                if ($k <= $expSort && $comclass_name[$v] != '不限') {
                    $expIds[] = $v;
                }
            }
            if (!empty($expIds)) {
                $jobWhere['exp'] = array('in', pylode(',', $expIds));
            }
        }
        if ($special['sex'] && $special['sex'] != 3) {// 设置了性别要求，且不为不限
            $jobWhere['sex'] = $special['sex'];
        }
        if($special['report']){
            include_once PLUS_PATH.'com.cache.php';
            $reportArr  = $comdata['job_report'];
            $reportSort = 0;
            $reportIds  = array();
            // 到岗时间搜索，排序比搜索小的都符合条件。如搜“1个月以内”，类别排序小于等于“1个月以内”排序的（要排除不限）都符合
            foreach ($reportArr as $k => $v) {
                if ($v == $special['report'] && $comclass_name[$v] != '不限') {
                    $reportSort = $k;
                    break;
                }
            }
            foreach ($reportArr as $k => $v) {
                if ($k <= $reportSort && $comclass_name[$v] != '不限') {
                    $reportIds[] = $v;
                }
            }
            if (!empty($reportIds)) {
                $jobWhere['report'] = array('in', pylode(',', $reportIds));
            }
        }
        if($special['welfare']){
            include_once PLUS_PATH.'com.cache.php';
            $welfares = $special['welfare'] ? explode(',', $special['welfare']) : array();
            $jobWhere['PHPYUNBTWSTART_C_DOUBLE']	=	'';
            foreach ($welfares as $k=>$v){
                $jobWhere['welfare'][]	=	array('like', $comclass_name[$v], $k==0?'AND': 'OR');
            }
            $jobWhere['PHPYUNBTWEND_C_DOUBLE']	=	'';
        }
        if ($special['hy']) {
            $jobWhere['hy'] = array('in', $special['hy']);
        }
        if ((int)$special['uptime'] > 0) {
            $upTime = intval($special['uptime']);
            if ($upTime == 1) {         //  今日更新
                $jobWhere['lastupdate'] = array('>=', strtotime('today'));
            } elseif ($upTime == 2) {   //  三日内
                $jobWhere['lastupdate'] = array('>=', strtotime('-3 days'));
            } elseif ($upTime == 3) {   //  七日内
                $jobWhere['lastupdate'] = array('>=', strtotime('-7 days'));
            } elseif ($upTime == 4) {   //  一月内
                $jobWhere['lastupdate'] = array('>=', strtotime('-30 days'));
            }
        }
        if ((int)$special['sdate'] > 0) {
            $sdate = intval($special['sdate']);
            if ($sdate == 1) {         //  今日更新
                $jobWhere['sdate'] = array('>=', strtotime('today'));
            } elseif ($sdate == 2) {   //  三日内
                $jobWhere['sdate'] = array('>=', strtotime('-3 days'));
            } elseif ($sdate == 3) {   //  七日内
                $jobWhere['sdate'] = array('>=', strtotime('-7 days'));
            } elseif ($sdate == 4) {   //  一月内
                $jobWhere['sdate'] = array('>=', strtotime('-30 days'));
            }
        }
        if ($special['rating']) {
            $jobWhere['rating'] = array('in', $special['rating']);
        }
        if($special['minsalary'] && $special['maxsalary']){
            $jobWhere['PHPYUNBTWSTART_D'] = '';
            $jobWhere['minsalary'] = array('>=', intval($special['minsalary']));
            $jobWhere['maxsalary'] = array('<=', intval($special['maxsalary']), 'AND');
            $jobWhere['PHPYUNBTWEND_D']  = '';
        } elseif ($special['minsalary'] && !$special['maxsalary']){
            $jobWhere['minsalary'] = array('>=', intval($special['minsalary']));
        } elseif (!$special['minsalary'] && $special['maxsalary']){
            $jobWhere['PHPYUNBTWSTART_E'] = '';
            $jobWhere['minsalary'] = array('<=', intval($special['maxsalary']));
            $jobWhere['maxsalary'] = array('<=', intval($special['maxsalary']), 'AND');
            $jobWhere['PHPYUNBTWEND_E']  = '';
        }
        //置顶 推荐 紧急 用OR连接
        $fieldArr = array();
        if ($special['is_zhiding']) {
            $fieldArr[] = 'xsdate';
        }
        if ($special['is_tuijian']) {
            $fieldArr[] = 'rec_time';
        }
        if ($special['is_jinji']) {
            $fieldArr[] = 'urgent_time';
        }
        $fieldArrCount = count($fieldArr);
        if ($fieldArrCount) {
            for ($i = 0; $i < $fieldArrCount; $i++) {
                $field = $fieldArr[$i];
                $fieldStr = str_replace('_', '', $field);
                $headDouble = ($i == 0) ? '_DOUBLE' : '';
                $footDouble = ($i == $fieldArrCount - 1) ? '_DOUBLE' : '';
                $jobWhere['PHPYUNBTWSTART_' . $fieldStr . $headDouble] = ($i == 0) ? '' : 'OR';
                $jobWhere[$field] = array('>', $time);
                $jobWhere['PHPYUNBTWEND_' . $fieldStr . $footDouble] = '';
            }
        }
        if($special['searchKeyword']){
            $jobWhere['PHPYUNBTWSTART_F'] = '';
            foreach ($special['searchKeyword'] as $k=>$v){
                $jobWhere['name'][]	=	array('like', $v, 'OR');
            }
            $jobWhere['PHPYUNBTWEND_F']  = '';
        }
        $jobWhere['r_status'] = 1;
        $jobWhere['status'] = 0;
        $jobWhere['state'] = 1;
        $jobWhere['limit'] = 100;
        $list = $this->select_all('company_job', $jobWhere, 'id');
        $jobids = array();
        if ($list) {
            foreach ($list as $v) {
                !in_array($v['id'], $jobids) && array_push($jobids, $v['id']);
            }
            $upSearch = array(
                'jobids' => pylode(',', $jobids)
            );
        } else {
            $upSearch = array(
                'jobids' => ''
            );
        }
        $upSearch['next_time'] = time() + $special['refresh_minutes'] * 60;
        $this->update_once('special_job_search',$upSearch,array('sid' => $special['id']));
        return pylode(',', $jobids);
    }

    function getInfo($whereData=array(),$data=array()){
        
        $field = isset($data['field']) ? $data['field'] : '*';
        $row   = $this->select_once('special_job', $whereData, $field);
        
        if (!empty($row)){
            if ($row['type'] == 2) {
                /* @var $job_name */
                include PLUS_PATH.'job.cache.php';
                /* @var $city_name */
                include PLUS_PATH.'city.cache.php';
                $jobsearch = $this->select_once('special_job_search', array('sid'=>$row['id']));
                if ($jobsearch) {
                    $row['next_time'] = $jobsearch['next_time'];
                    $row['refresh_minutes'] = $jobsearch['refresh_minutes'];
                    $row['job_class'] = $jobsearch['job_class'];
                    !empty($jobsearch['job_class']) && $row['job_class_n'] = $job_name[$jobsearch['job_class']];
                    $row['city_class'] = $jobsearch['city_class'];
                    !empty($jobsearch['city_class']) && $row['city_class_n'] = $city_name[$jobsearch['city_class']];
                    $row['welfare'] = $jobsearch['welfare'];
                    $row['welfare_arr'] = $jobsearch['welfare'] ? explode(',', $jobsearch['welfare']) : array();
                    $row['edu'] = $jobsearch['edu'];
                    $row['exp'] = $jobsearch['exp'];
                    $row['sex'] = $jobsearch['sex'];
                    $row['report'] = $jobsearch['report'];
                    $row['hy'] = $jobsearch['hy'];
                    $row['hy_arr'] = $jobsearch['hy'] ? explode(',', $jobsearch['hy']) : array();
                    $row['uptime'] = $jobsearch['uptime'] ? $jobsearch['uptime'] : 0;
                    $row['sdate'] = $jobsearch['sdate'] ? $jobsearch['sdate'] : 0;
                    $row['rating'] = $jobsearch['rating'];
                    $row['rating_arr'] = $jobsearch['rating'] ? explode(',', $jobsearch['rating']) : array();
                    $row['minsalary'] = $jobsearch['minsalary'];
                    $row['maxsalary'] = $jobsearch['maxsalary'];
                    $row['jobids'] = $jobsearch['jobids'];
                    $row['manual_jobids'] = $jobsearch['manual_jobids'];
                    $row['is_zhiding'] = $jobsearch['is_zhiding'];
                    $row['is_tuijian'] = $jobsearch['is_tuijian'];
                    $row['is_jinji'] = $jobsearch['is_jinji'];
                    $row['searchKeyword'] = !empty($jobsearch['keyword']) ? unserialize($jobsearch['keyword']) : array();
                }
            }
            $row['pic_n']          = checkpic($row['pic']);
            $row['list_pic_n']     = checkpic($row['list_pic']);
            // 6.3开始，将下面4张图合并成用同一张
            $row['wap_pic_n']      = checkpic($row['sjlist_pic']);
            $row['wap_list_pic_n'] = checkpic($row['sjlist_pic']);
            $row['sjlist_pic_n']     = checkpic($row['sjlist_pic']);
            $row['wap_sjlist_pic_n'] = checkpic($row['sjlist_pic']);
        }
        return $row;
    }

    function getNum($whereData=array()){
        $num    =   intval($this->select_num('special_job', $whereData));
        return $num;
    }
    
    function getList($whereData=array(),$data=array()){
        
        $field = isset($data['field']) ? $data['field'] : '*';
        $list  = $this->select_all('special_job', $whereData, $field);
        
        if(!empty($list)){
            if ($data['utype']=='admin' || $data['need_jobnum']) {
                $sjidTp1 = $sjidTp2 = array();
                foreach($list as $val){
                    if ($val['type'] == 2) {
                        $sjidTp2[] = $val['id'];
                    } else {
                        $sjidTp1[] = $val['id'];
                    }
                }
                // 自动搜索职位的职位专题
                $jobsearch=$this->select_all("special_job_search",array('sid'=>array('in', pylode(',', $sjidTp2))),"`sid`,`jobids`,`manual_jobids`");
                // 手动添加职位的职位专题
                $joblistWhere['sid'] =	array('in',pylode(',',$sjidTp1));
                $joblistWhere['groupby'] =	'sid';
                $joblist =	$this->select_all('special_job_list', $joblistWhere,'`sid`,count(id) as job_num');
                $spjobnums = array();
                if($jobsearch&&is_array($jobsearch)){
                    foreach($jobsearch as $k=>$v){
                        $jobidNum = $v['jobids'] ? count(explode(',', $v['jobids'])) : 0;
                        $manualNum = $v['manual_jobids'] ? count(explode(',', $v['manual_jobids'])) : 0;
                        $spjobnums[$v['sid']] = $jobidNum + $manualNum;
                    }
                }
                if($joblist&&is_array($joblist)){
                    foreach($joblist as $k=>$v){
                        $spjobnums[$v['sid']] = intval($v['job_num']);
                    }
                }
                foreach($list as $key=>$v){
                    $list[$key]['jobnum'] =	$spjobnums[$v['id']] ? $spjobnums[$v['id']] : 0;
                }
            }
            foreach ($list as $k => $v){
                $list[$k]['title_url'] = Url('special', array('c' => 'specialJob', 'id' => $v['id']));
                $list[$k]['sjlist_pic_n']   =   checkpic($v['sjlist_pic']);
                $list[$k]['display_switch'] = $v['display'] == 1 ? true : false;
                $list[$k]['tuiguang_switch'] = $v['tuiguang'] == 1 ? true : false;
            }
        }
        return $list;
    }
    function addInfo($data=array()){

        $data['ctime'] = time();
        $dataSearch = $data['data_search'];
        unset($data['data_search']);

        $nid = $this -> insert_into('special_job',$data);
        if ($data['type'] == 2 && $nid) {// 自动搜索职位的职位专题
            $dataSearch['sid'] = $nid;
            $dataSearch['ctime'] = time();
            $this -> insert_into('special_job_search',$dataSearch);
        }

        return $nid;
    }

    function upInfo($whereData=array(),$data=array()){
        if(!empty($whereData)){
            if (isset($data['data_search'])) {
                $dataSearch = $data['data_search'];
                unset($data['data_search']);
            }

            $nid	=	$this -> update_once('special_job',$data,$whereData);
            if ($data['type'] == 2) {// 自动搜索职位的职位专题
                $dataSearch['sid'] = $whereData['id'];
                $exist = $this->select_once('special_job_search', array('sid'=>$whereData['id']));
                if ($exist) {
                    $this -> update_once('special_job_search',$dataSearch,array('id'=>$exist['id']));
                } else {
                    $dataSearch['ctime'] = time();
                    $this -> insert_into('special_job_search',$dataSearch);
                }
            }
        }
        return $nid;
    }
    // 职位专题添加职位
    function addJob($data){
        
        $time     = time();
        $addjobid = $data['add'];
        $sj = $this->select_once('special_job', array('id'=>$data['sid']), 'type');
        if ($sj['type'] == 2) {
            $sjs = $this->select_once('special_job_search', array('sid'=>$data['sid']));
            $mamualJobids = array();
            if ($sjs['manual_jobids']) {
                $mamualJobids = explode(',', $sjs['manual_jobids']);
            }
            foreach ($addjobid as $v) {
                !in_array($v, $mamualJobids) && array_push($mamualJobids, $v);
            }
            $this->update_once('special_job_search',array('manual_jobids'=> pylode(',', $mamualJobids)),array('id'=>$sjs['id']));
        } else {
            $list = $this->select_all('special_job_list', array('sid'=>$data['sid']));
            if (!empty($list)){
                $jobids = array();
                foreach ($list as $v){
                    $jobids[] = $v['jobid'];
                }
                $addjobid = array_diff($addjobid, $jobids);
            }
            if (!empty($addjobid)){
                foreach ($addjobid as $v){
                    $add = array(
                        'sid'   => $data['sid'],
                        'jobid' => $v,
                        'ctime' => $time
                    );
                    $this->insert_into('special_job_list', $add);
                }
            }
        }
    }
    function getjobListShort($where = array(), $data = array()){
        $field = isset($data['field']) ? $data['field'] : '*';
        $list = $this->select_all('special_job_list', $where, $field);
        return $list;
    }
    // 查询职位专题参加的职位列表
    function getjobList($where = array(), $data = array()){
        
        $utype = isset($data['utype']) ? $data['utype'] : '';
        $autoAdd = isset($data['auto_add']) && $data['auto_add'] ? 1 : 0;
        $limit = $where['limit'];
        unset($where['limit']);
        if ($autoAdd == 0) {// 手动添加职位
            $list = $this->select_all('special_job_list', $where);
            if (!empty($list)){
                if ($utype != ''){
                    $time   = time();
                    $jobids = array();
                    foreach ($list as $v){
                        $jobids[] = $v['jobid'];
                    }
                }
            }
        } else {
            $jobids = explode(',', $where['id'][1]);
        }
        include PLUS_PATH.'com.cache.php';
        include PLUS_PATH.'comrating.cache.php';
        include PLUS_PATH.'city.cache.php';
        foreach ($comrat as $v){
            $rating[$v['id']] = $v['com_pic'];
            $ratingNameArr[$v['id']] = $v['name'];
        }
        $jwhere = array('id'=>array('in', pylode(',', $jobids)));
        if ($utype != 'admin') {// 前台职位专题详情仅展示招聘中的职位
            $jwhere['r_status'] = 1;
            $jwhere['status'] = 0;
            $jwhere['state'] = 1;
        }
		if($_POST['provider']=='toutiao'){
            
            $jwhere['PHPYUNBTWSTART_C']  =  '';
            $jwhere['name'][]            =  array('notlike', '主播');
            $jwhere['name'][]            =  array('notlike', '助播');
            $jwhere['PHPYUNBTWEND_C']	=	'';
            $jwhere['com_name']            =  array('like', '有限公司');
            
        }
        $jwhere['orderby']	=	array('lastupdate,desc');
        if ($limit) {
            $jwhere['limit'] = $limit;
        }
        $job = $this->select_all('company_job',$jwhere);
        $linkids = array();
        $comuids = array();
        foreach ($job as $v) {
            // 职位中link_id不为0的取job_link表中的link_address,否则去company表中address
            if ($v['link_id'] > 0) {
                !in_array($v['link_id'], $linkids) && array_push($linkids, $v['link_id']);
            } else {
                !in_array($v['uid'], $comuids) && array_push($comuids, $v['uid']);
            }
        }
        $linkAddress = $this->select_all('company_job_link', array('id'=>array('in', pylode(',',$linkids))), '`id`,`link_address`');
        $linkAddressArr = array();
        foreach ($linkAddress as $addr) {
            $linkAddressArr[$addr['id']] = $addr['link_address'];
        }
        $comAddress = $this->select_all('company', array('uid'=>array('in', pylode(',',$comuids))), '`uid`,`address`');
        $comAddressArr = array();
        foreach ($comAddress as $addr) {
            $comAddressArr[$addr['uid']] = $addr['address'];
        }
        $rt = array();
        foreach ($job as $k => $val){
            $rt[$k]['jobid']       = $val['id'];
            $rt[$k]['status']      = $val['status'];
            $rt[$k]['uid']         = $val['uid'];
            $rt[$k]['name']        = $val['name'];
            $rt[$k]['name_url']    = $this ->config['sy_weburl']."/job/index.php?c=comapply&id=".$val['id'];
            $rt[$k]['com_name']    = $val['com_name'];
            $rt[$k]['com_name_url']= Url('company', array('c' => 'show', 'id' => $val['uid']));
            $rt[$k]['rating_name'] = $ratingNameArr[$val['rating']];
            $rt[$k]['job_edu']     = $comclass_name[$val['edu']];
            $rt[$k]['job_exp']     = $comclass_name[$val['exp']];
            $rt[$k]['sdate']       = $val['sdate'];
            $rt[$k]['sdate_n']     = date('Y-m-d H:i', $val['sdate']);
            $rt[$k]['lastupdate']  = $val['lastupdate'];
            $rt[$k]['lastupdate_n']  = date('Y-m-d H:i', $val['lastupdate']);
            $rt[$k]['job_salary']  = salaryUnit($val['minsalary'], $val['maxsalary']);
            if(!empty($val['provinceid'])){
                $rt[$k]['job_city_one']    =	$city_name[$val['provinceid']];
                $rt[$k]['citystr']         =  $city_name[$val['provinceid']];
            }
            if(!empty($val['cityid'])){
                $rt[$k]['job_city_two']    =	$city_name[$val['cityid']];
                if (isset($rt[$k]['citystr'])){
                    $rt[$k]['citystr']    .=  '-'.$city_name[$val['cityid']];
                }else{
                    $rt[$k]['citystr']     = $city_name[$val['cityid']];
                }
            }
            if (!empty($val['three_cityid'])) {
                $rt[$k]['job_city_three']    =	$city_name[$val['three_cityid']];
                if (isset($rt[$k]['citystr'])){
                    $rt[$k]['citystr']    .=  '-'.$city_name[$val['three_cityid']];
                }else{
                    $rt[$k]['citystr']     = $city_name[$val['three_cityid']];
                }
            }
            $rt[$k]['address'] = $val['link_id'] ? $linkAddressArr[$val['link_id']] : $comAddressArr[$val['uid']];
            $rt[$k]['welfareArr']     =  array_filter(@explode(',', trim($val['welfare'])));
            if(isset($val['rec']) && isset($val['rec_time']) && $val['rec'] == 1 && $val['rec_time'] > $time){
                $rt[$k]['rec_n']	   =  1;
            }
            if(isset($val['urgent']) && isset($val['urgent_time']) && $val['urgent'] == 1 && $val['urgent_time'] > $time){
                $rt[$k]['urgent_n']  =  1;
            }
            $rt[$k]['rating_logo'] = isset($rating[$val['rating']]) ? checkpic($rating[$val['rating']]) : '';
            $rt[$k]['add_exp_edu'] = '';
            if ($rt[$k]['address']) {
                $rt[$k]['add_exp_edu'] .= $rt[$k]['address'];
            }
            if ($rt[$k]['job_exp']) {
                if ($rt[$k]['add_exp_edu']) {
                    $rt[$k]['add_exp_edu'] .= ' / ' . $rt[$k]['job_exp'];
                } else {
                    $rt[$k]['add_exp_edu'] .= $rt[$k]['job_exp'];
                }
            }
            if ($rt[$k]['job_edu']) {
                if ($rt[$k]['add_exp_edu']) {
                    $rt[$k]['add_exp_edu'] .= ' / ' . $rt[$k]['job_edu'];
                } else {
                    $rt[$k]['add_exp_edu'] .= $rt[$k]['job_edu'];
                }
            }
            if ($val['state'] == 0) {
                $status_n = '职位未审核';
            } else if ($val['state'] == 2) {
                $status_n = '职位已过期';
            } else if ($val['state'] == 3) {
                $status_n = '职位审核未通过';
            } else if ($val['r_status'] == 0) {
                $status_n = '企业未审核';
            } else if ($val['r_status'] == 2) {
                $status_n = '企业已锁定';
            } else if ($val['r_status'] == 3) {
                $status_n = '企业审核未通过';
            } else if ($val['r_status'] == 4) {
                $status_n = '企业已暂停';
            } else if ($val['status'] == 4) {
                $status_n = '已下架';
            } else {
                $status_n = '招聘中';
            }
            $rt[$k]['status_n'] = $status_n;
        }
        return $rt;
    }
    function getjoblistnum($where = array()){
        return $this->select_num('special_job_list',$where);
    }
    // 删除参加的职位
    function delJob($where = array(), $data){
        $sj = $this->select_once('special_job', array('id'=>$data['sid']), 'type');
        if ($sj['type'] == 2) {
            $sjs = $this->select_once('special_job_search', array('sid'=>$data['sid']));
            $mamualJobids = array();
            if ($sjs['manual_jobids']) {
                $mamualJobids = explode(',', $sjs['manual_jobids']);
            }
            $newManual = array_diff($mamualJobids, explode(',', $where['id'][1]));
            $result = $this->update_once('special_job_search',array('manual_jobids'=> pylode(',', $newManual)),array('id'=>$sjs['id']));
        } else {
            $result	= $this->delete_all('special_job_list',array('jobid'=>$where['id'],'sid'=>$data['sid']), '');
        }
        return	$result;
    }
    // 删除职位专题
    function del($where = array()){
        
        $res  =  $this->delete_all('special_job',$where,'');
        
        if($res){
            $jobnum = $this->select_num('special_job_list', array('sid'=>$where['id']));
            if ($jobnum > 0){
                $this->delete_all('special_job_list', array('sid'=>$where['id']),'');
            }
            $jobsearchNum = $this->select_num('special_job_search', array('sid'=>$where['id']));
            if ($jobsearchNum) {
                $this->delete_all('special_job_search', array('sid'=>$where['id']),'');
            }
        }
        return $res;
    }
}
?>