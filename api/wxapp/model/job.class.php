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
class job_controller extends wxapp_controller{
	function list_action(){//职位列表
	    $jobM  =  $this->MODEL('job');
	    $cacheM = $this->MODEL('cache');
	    $search             =   false;
		$where['state']		=	1;
		$where['status']	=	0;
		$where['r_status']	=	1;
		$provinceid			=	(int)$_POST['provinceid'];
		$cityid				=	(int)$_POST['cityid'];
		$three_cityid		=	(int)$_POST['three_cityid'];
		$job1				=	(int)$_POST['job1'];
		$job1_son			=	(int)$_POST['job1_son'];
		$job_post			=	(int)$_POST['job_post'];
		$exp				=	(int)$_POST['exp'];
		$hy					=	(int)$_POST['hy'];
		$pr					=	(int)$_POST['pr'];
		$mun				=	(int)$_POST['mun'];
		$edu				=	$_POST['edu'];
		$notinids			=	$_POST['zdids'];
		
		$sdate				=	$_POST['sdate'];
		$edate				=	$_POST['edate'];
        $keyword            =   null;
		if ($_POST['keyword']!='undefined'){
		    $keyword		=	$this->stringfilter($_POST['keyword']);
		}
		$order				=	$_POST['order'];

		$nodata				=	$_POST['nodata'];
		$uid				=	(int)$_POST['uid'];
		$state				=	(int)$_POST['state'];

		$rec 				=	(int)$_POST['rec'];

        $where['is_depower'] = 2; // 默认查询未降权的职位

		if($_POST['state']){
			if($_POST['state']==2){
				$where['edate']	=	array('<',time());
			}elseif($_POST['state']==4){
				$where['state']	=	'0';
			}else{
				$where['state']	=	$_POST['state'];
			}
		}
		if($_POST['cuid']){
		    $where['uid']		=	$_POST['cuid'];
		    // 单查某企业下职位，排除分站参数
		    if (isset($_POST['did'])){
		        unset($_POST['did']);
		    }
		}
		if($_POST['urgent']){
            $where['urgent']     =  1;
			$where['urgent_time']=	array('>',time());
		}
		if($_POST['r_status']){
			$where['r_status']	=	(int)$_POST['r_status'];
		}
		if($_POST['status']){
			$where['status']	=	(int)$_POST['status'];
		}
		if($rec==1){
		    //老版的推荐排序为 优先排会员且按rec_time排序，此处暂时参照wap
            $where['rec']       =   1;
			$where['rec_time']	=	array('>=',time());
		}
		if($hy){//类别ID
			$where['hy']		=	$hy;
		}
		if($pr){//类别ID
			$where['pr']		=	$pr;
		}
		if($mun){//类别ID
			$where['mun']		=	$mun;
		}
		if($edu){
		    $CacheArr   =   $cacheM->GetCache(array('com'));
		    $eduArr  = $CacheArr['comdata']['job_edu'];
		    $eduSort = 0;
		    $eduIds  = array();
		    // 职位搜索，排序比搜索小的都符合条件。如搜“硕士”，类别排序小于等于“硕士”排序的（要排除不限）都符合
		    foreach ($eduArr as $k => $v) {
		        if ($v == $edu && $CacheArr['comclass_name'][$v] != '不限') {
		            $eduSort = $k;
		            break;
		        }
		    }
		    foreach ($eduArr as $k => $v) {
		        if ($k <= $eduSort && $CacheArr['comclass_name'][$v] != '不限') {
		            $eduIds[] = $v;
		        }
		    }
		    if (!empty($eduIds)) {
		        $where['edu'] = array('in', pylode(',', $eduIds));
		    }
		}
		if($exp){
		    if (!isset($CacheArr)){
		        $CacheArr   =   $cacheM->GetCache(array('com'));
		    }
			$expArr  = $CacheArr['comdata']['job_exp'];
			$expSort = 0;
			$expIds  = array();
			// 职位搜索，排序比搜索小的都符合条件。如搜“五年”，类别排序小于等于“五年”排序的（要排除不限）都符合
            foreach ($expArr as $k => $v) {
                if ($v == $exp && $CacheArr['comclass_name'][$v] != '不限') {
                    $expSort = $k;
                    break;
                }
            }
            foreach ($expArr as $k => $v) {
                if ($k <= $expSort && $CacheArr['comclass_name'][$v] != '不限') {
                    $expIds[] = $v;
                }
            }
            if (!empty($expIds)) {
            	$where['exp'] = array('in', pylode(',', $expIds));
            }
		}
		if($provinceid){//类别ID
			$where['provinceid']=	$provinceid;
		}
		if($cityid){//类别ID
			$where['cityid']	=	$cityid;
		}
		if($three_cityid){//类别ID
			$where['three_cityid']	=	$three_cityid;
		}
		
		if($job1){//类别ID
			$where['job1']		=	$job1;
		}
		if($job1_son){//类别ID
			$where['job1_son']	=	$job1_son;
		}
		if($job_post){//类别ID
			$where['job_post']	=	$job_post;
		}
		if($sdate){//开始时间
			$where['lastupdate']=	array('>',strtotime($sdate));
		}
		if($edate){//结束时间
		    $where['lastupdate']=	array('<',strtotime($edate));
		}
		if($_POST['sex']){
			$where['sex']		=	$_POST['sex'];
		}
		if($_POST['uptime']){//更新时间
			if($_POST['uptime']==1){
				$where['lastupdate']=	array('>',strtotime(date('Y-m-d 00:00:00')));
			}else{
				$where['lastupdate']=	array('>',strtotime('-'.$_POST['uptime'].' day'));
			}
		}else{
		    if($this->config['sy_datacycle_job']>0){
		        // 后台-页面设置-数据周期
                $where['lastupdate']=	array('>',strtotime('-'.$this->config['sy_datacycle_job'].' day'));
            }
        }
		
        if ($keyword) { //关键字

            $kCity  =   array();
            if (!isset($where['provinceid']) || !isset($where['cityid']) || !isset($where['three_cityid'])) {
                $cache  =   $cacheM->GetCache(array('city'));
                foreach ($cache['city_name'] as $k1 => $v1) {
                    if (strpos($v1, $keyword) !== false) {

                        $kCity[]    =   $k1;
                    }
                }
            }
            if ($this->config['job_full_text_search'] == 1){
                $kJob   =   array();
                if (!isset($where['job1']) || !isset($where['job1_son']) || !isset($where['job_post'])) {
                    $cache  =   $cacheM->GetCache(array('job'));
                    foreach ($cache['job_name'] as $k2 => $v2) {
                        if (strpos($v2, $keyword) !== false) {

                            $kJob[]     =   $k2;
                        }
                    }
                }
            }
            $where['PHPYUNBTWSTART_A']      =   '';
			if($_POST['provider']=='toutiao'){
            
                $where['name'][]            =  array('notlike', '带货');
                $where['name'][]            =  array('notlike', '播');
                $where['name'][]            =  array('notlike', '演员');
                $where['name'][]            =  array('like', $keyword);
                $where['com_name']          =  array('like', '有限公司');
                // 薪资不能是面议
                $where['minsalary']         =  array('>', 0);
            }else{
                
                $where['name']                  =   array('like', $keyword);
            }

           
            if ($this->config['job_full_text_search'] == 1) {
                $where['description']       =   array('like', $keyword, 'OR');
            }

            // 企业名称及简称搜索start
            $companyM       =   $this->MODEL('company');

            $comWhere['PHPYUNBTWSTART_B']   = '';
            $comWhere['name'][]             =   array('like', $keyword);
            $comWhere['shortname']          =   array('like', $keyword, 'OR');
            $comWhere['PHPYUNBTWEND_B']     =   '';
			if($_POST['provider']=='toutiao'){
                
                $comWhere['name'][]         =  array('like', '有限公司');
            }
            $cuids          =   array();
            $companys       =   $companyM->getChCompanyList($comWhere, array('field' => 'uid'));
            foreach ($companys as $v) {
                !in_array($v['uid'], $cuids) && array_push($cuids, $v['uid']);
            }
            if ($cuids) {
                $where['uid']               =   array('in', pylode(',', $cuids), 'OR');
            }
            // 企业名称及简称搜索end
            if (!empty($kCity)) {
                $where['provinceid']        =   array('in', pylode(',', $kCity), 'OR');
                $where['cityid']            =   array('in', pylode(',', $kCity), 'OR');
                $where['three_cityid']      =   array('in', pylode(',', $kCity), 'OR');
            }
            if ($this->config['job_full_text_search'] == 1){
                if (!empty($kJob)) {

                    $where['job1_son']      =   array('in', pylode(',', $kJob), 'OR');
                    $where['job_post']      =   array('in', pylode(',', $kJob), 'OR');
                }
            }
            $where['PHPYUNBTWEND_A']        =   '';
        }elseif($_POST['provider']=='toutiao'){
            
            $where['PHPYUNBTWSTART_C']  =  '';
            $where['name'][]            =  array('notlike', '带货');
            $where['name'][]            =  array('notlike', '播');
            $where['name'][]            =  array('notlike', '演员');
            $where['PHPYUNBTWEND_C']	=	'';
            $where['com_name']          =  array('like', '有限公司');
            $where['minsalary']         =  array('>', 0);
        }
        if ($_POST['salary']){
            $salaryArr  =  $this->salaryArr(true);
            $salary     =  $salaryArr[$_POST['salary']];
            
            if ($salary['minsalary'] > 0 && $salary['maxsalary'] > 0){
                
                $where['PHPYUNBTWSTART_A']  =  '';
                $where['minsalary'][]       =  array('>=', $salary['minsalary']);
                $where['minsalary'][]       =  array('<=', $salary['maxsalary']);
                $where['maxsalary']         =  array('<=', $salary['maxsalary']);
                $where['PHPYUNBTWEND_A']	=	'';
                
            } elseif ($salary['minsalary'] > 0 && $salary['maxsalary'] == 0){
                $where['PHPYUNBTWSTART_A']  =  '';
                $where['minsalary']         =  array('>=', $salary['minsalary']);
                $where['maxsalary']         =  array('>=', $salary['minsalary'], 'OR');
                $where['PHPYUNBTWEND_A']	=	'';
                
            } elseif ($salary['minsalary'] == 0 && $salary['maxsalary'] > 0){
                
                $where['minsalary']         =  array('<=', $salary['maxsalary']);
                $where['maxsalary']         =  array('<=', $salary['maxsalary']);
            }
        }
        if (!empty($_POST['welfare'])) {

            $CacheArr   =   $cacheM->GetCache('com');
            $welName    =   $CacheArr['comclass_name'][$_POST['welfare']];
            $where['welfare']   =   array('like', $welName);
        }
        // 处理分站查询条件
        if (!empty($_POST['did'])){
            
            $domain  =  $this->getDomain($_POST['did'], true);
            
            if (isset($domain['didcity'])){
                
                $data['didcity']    =  $domain['didcity'];
                
                if (!empty($_POST['provinceid'])){
                    // 分站下，再次选择城市，查询按选择的来
                    $where['provinceid']  =  $_POST['provinceid'];
                    $data['didcity']      =  $domain['city_name'][$_POST['provinceid']];
                }elseif (!empty($domain['provinceid'])){
                    $where['provinceid']  =  $domain['provinceid'];
                }
                if (!empty($_POST['cityid'])){
                    // 分站下，再次选择城市，查询按选择的来
                    $where['cityid']  =  $_POST['cityid'];
                    $data['didcity']  =  $domain['city_name'][$_POST['cityid']];
                }elseif (!empty($domain['cityid'])){
                    $where['cityid']  =  $domain['cityid'];
                }
                if (!empty($_POST['three_cityid'])){
                    // 分站下，再次选择城市，查询按选择的来
                    $where['three_cityid']  =  $_POST['three_cityid'];
                    $data['didcity']        =  $domain['city_name'][$_POST['three_cityid']];
                }elseif (!empty($domain['three_cityid'])){
                    $where['three_cityid']  =  $domain['three_cityid'];
                }
                
                $data['cityone']    =  $domain['cityone'];
                $data['citytwo']    =  $domain['citytwo'];
                $data['citythree']  =  $domain['citythree'];
                $data['provinceid']    =  !empty($where['provinceid']) ? intval($where['provinceid']) : 0;
                $data['cityid']        =  !empty($where['cityid']) ? intval($where['cityid']) : 0;
                $data['three_cityid']  =  !empty($where['three_cityid']) ? intval($where['three_cityid']) : 0;
            }
            if (isset($domain['didhy'])){
                
                if (!empty($domain['hyclass'])){
                    $where['hy']  =  $domain['hyclass'];
                }
            }
        }else {
            // 没有已选择的城市，按后台设置的列表页区域默认设置来（后台-页面设置-列表页区域默认设置）
            // 设置了一级城市，后面的搜索，不再展示其他一级城市
            if (empty($_POST['provinceid']) && empty($_POST['cityid']) && empty($_POST['three_cityid'])&&!$_POST['cuid'] || (!empty($_POST['provinceid']) && $_POST['provinceid'] == $this->config['sy_web_city_one'])){
                
                $list_cityid      = !empty($cityid) ? $cityid : 0;
                $list_threecityid = !empty($three_cityid) ? $three_cityid : 0;
                
                $listback = $this->listCity($list_cityid, $list_threecityid);
                if (!empty($listback)) {
                    if (isset($listback['provinceid'])){
                        $where['provinceid']  =  $listback['provinceid'];
                    }
                    if (isset($listback['cityid'])){
                        $where['cityid']  =  $listback['cityid'];
                    }
                    if (isset($listback['listcity'])){
                        $data['listcity']   =  $listback['listcity'];
                        $data['cityone']    =  $listback['cityone'];
                        $data['citytwo']    =  $listback['citytwo'];
                        $data['citythree']  =  $listback['citythree'];
                        
                        $data['provinceid']    =  !empty($where['provinceid']) ? intval($where['provinceid']) : 0;
                        $data['cityid']        =  $list_cityid;
                        $data['three_cityid']  =  $list_threecityid;
                    }
                }
            }
        }
        
		$page  =  $_POST['page'];

		$jfield   =  '`id`,`uid`,`name`,`provinceid`,`cityid`,`three_cityid`,`exp`,`edu`,`welfare`,`minsalary`,`maxsalary`,`lastupdate`,`com_name`,`sdate`,`rec`,`rec_time`,`urgent`,`urgent_time`,`xsdate`,`rating`,`zuid`,`com_logo`,`is_link`,`link_id`,`yyzz_status`';

		if(!$_POST['mapjob']){
			if ($page==1 && isset($_POST['joblist'])){
				$xswhere	=	$where;
			    if ($this->config['joblist_top']!=0){

			    	$xswhere['xsdate']	=	array('>',time());
			    	$xswhere['orderby']	=	'`lastupdate`,asc';

			    }else{
			    	$xswhere['xsdate']	=	array('>',time());
			    	$xswhere['orderby']	=	'rand(),desc';
			    	$xswhere['limit']	=	'20';

			    }
                //职位列表，置顶招聘(搜索置顶)，相关条件为空，则不显示置顶职位
                if ($this->config['joblist_top'] == 2 && !$job1 && !$job1_son && !$job_post && !$keyword) {
                    unset($xswhere);
                }
			}
		}
		if(isset($_POST['jobindex']) && $_POST['jobindex']){
            $xswhere	=	$where;
		    if($this->config['joblist_top_index']==1){
                $xswhere['xsdate']	=	array('>',time());
                $xswhere['orderby']	=	'`lastupdate`,asc';
            }elseif($this->config['joblist_top_index']==2){
                $xswhere['xsdate']	=	array('>',time());
                $xswhere['orderby']	=	'rand(),desc';
                $xswhere['limit']	=	'5';
            }
            if ($this->config['joblist_top_index'] == 0 || $_POST['page'] > 1) {
                unset($xswhere);
            }
        }
		if($_POST['mapjob'] && $_POST['x'] && $_POST['y']){
			
			$where['x'] = array('>', 0);
			$where['y'] = array('>', 0);
			$order = 'distance, asc';

			$x	=	$_POST['x'];
			$y	=	$_POST['y'];
		    // 根据sql距离计算公式计算距离并排序
	        $jfield .=   ",`x`,`y`, 6371 * acos(cos(radians(" . $y . ")) * cos(radians(`y`)) * cos(radians(`x`) - radians(" . $x . ")) + sin(radians(" . $y . ")) * sin(radians(`y`))) AS `distance`";
		}

		if($order){//排序
			$where['orderby']	=	$order;
		}else{
			$where['orderby']	=	'lastupdate,desc';
		}
		if ($_POST['limit']){
		    $limit				=	$_POST['limit'];
		    if($page){//分页
		        $pagenav		=	($page-1)*$limit;
		        $where['limit']	=	array($pagenav,$limit);
		    }else{
		    	$where['limit']	=	$limit;
		    }
		}
		
		
		
		if(!empty($xswhere)){
		    $xsjobrows 	=   $jobM->getList($xswhere,array('field'=>$jfield,'utype'=>'wxapp'));
			
			$xsrows		=	$xsjobrows['list'];
			if ($xsrows){
				$notinids 		= $zdids	=	array();
				foreach ($xsrows as $v){
					$notinids[] = $zdids[]	=	$v['id'];
		        }
	    	}
		}

		if(isset($notinids)){//其他职位
			$where['id']=	array('notin',pylode(',',$notinids));
		}
		$jobrows    	=   $jobM	->	getList($where,array('field'=>$jfield,'utype'=>'wxapp', 'video' => 1, 'link' => 'yes', 'member'=>'user', 'look_where' => array('uid' => $uid)));
		$rows			=	$jobrows['list'];

		if(is_array($rows)&&$rows){
		    // 清除置顶下面列表数据的置顶参数
		    foreach($rows as $k=>$v){
		        if (isset($v['xs']) && $v['xs'] == 1){
		            unset($rows[$k]['xs']);
		        }
		    }
		    if ($xsrows){
		    	
		        foreach($rows as $k=>$v){
		            foreach ($xsrows as $val){
		            	
		                if($v['id']==$val['id']){
		                    unset($rows[$k]);
		                }
		            }
		        }
		        foreach ($xsrows as $v){
		            // 置顶职位，清除职位更新时间
		            $v['lastupdate_date']  =  '';
		        	array_unshift($rows,$v);
		        }
		    }
		}elseif ($page==1 && isset($_POST['joblist']) && !$_POST['mapjob']){
			$rows  =  $xsrows;
		}
        foreach($rows as $k=>$v){

        	if($_POST['mapjob']){
        		if ($v['distance'] <= 1) {

	                $rows[$k]['dis']    =   ceil($v['distance'] * 1000) . 'm';
	            } else {

	                $rows[$k]['dis']    =   round($v['distance'], 2) . 'km';
	            }
        	}
        	
            if (!empty($v['lastupdate']) && !empty($v['lastupdate_n'])){
                $beginToday  =  strtotime('today');//今天开始时间戳
                if($v['lastupdate']>$beginToday){
                    $rows[$k]['lastupdate_n']  =  lastupdateStyle($v['lastupdate']);
                }
            }
        }

		$list	=	count($rows) > 0 ? $rows : array();

        $data['jnum']		=	$jobM->getJobNum(array('uid' => $_POST['cuid'], 'state' => 1, 'status' => 0, 'r_status' => 1,'is_depower'=>'2'));
        $data['list']		=	$list;
		$data['zdids']		=	$zdids;
		$data['com_job_sexswitch'] = $this->config['com_job_sexswitch'];
        $data['applocation']  =  $this->config['sy_ioslocation'];
		// 小程序用seo
        if ($this->plat == 'mini'){
            $seo            =  $this->seo('com_search','','','',false, true);
            $data['seo']    =  $seo;
        }
		$this->render_json(0,'',$data);
	}

    /**
     * 职位详情
     */
	function show_action()
	{
		$id		   =  (int)$_POST['id'];
		$JobM		=	$this -> MODEL('job');	
		$companyM  =	$this -> MODEL('company');	
		$job		=   $JobM -> getInfo(array('id' => $id ,'r_status' => array('<>', 2)), array('utype'=>'wxapp','com' => 'yes'));
        if($_POST['provider']=='toutiao'){
            if (stripos($job['jobname'], '演员') !== false
                || stripos($job['jobname'], '带货') !== false
                || stripos($job['jobname'], '播') !== false
                || stripos($job['description'], '演员') !== false
                || stripos($job['description'], '带货') !== false
                || stripos($job['description'], '播') !== false
                || stripos($job['com_name'], '有限公司') === false) {
                unset($job);
            }
        }
		if($job && is_array($job)){
			// 投递数量
	       $allnum     =   $JobM->getSqJobNum(array('job_id' => $id,'isdel'=>9,'is_browse'=>array('<>', 6)));
	        
	        $job['snum'] = $allnum;
        	
			if (!empty($job['description'])){
			    
			    $job['description'] = $this->preghtml($job['description']);
			}
			if($_POST['provider']=='toutiao'){
                $job['description'] =
                    "岗位名称：".$job['jobname']."</br>
                     岗位城市：".$job['citystr']."</br>
                     薪资范围：".$job['job_salary']."</br>
                     公司全称：".$job['com_name']."</br>
                     收费要求：本岗位不收取任何费用".$job['description'];
			}
			if (!empty($job['job_lang'])){
			    
			    $job['job_lang'] = implode(',', $job['job_lang']);
			}
			// 复制文本用职位简介
			$job['desc_n']  =   mb_substr(strip_tags($job['description']), 0, 200);
			$job['job_url'] =   Url('wap', array('c' => 'job', 'a' => 'comapply', 'id' => $id));
			$error			=	1;
			$msg			=	'';
			$data['list']	=	count($job)?$job:array();

            $uid  =  $usertype  =  '';

            if (!empty($_POST['uid']) && !empty($_POST['token'])){

                $member    =  $this->yzToken($_POST['uid'], $_POST['token']);
                $uid       =  $member['uid'];
                $usertype  =  $member['usertype'];
            }

            //联系方式
            $link		   =  $JobM->getCompanyJobTel(array('id'=>$job['id'],'uid'=>$uid,'usertype'=>$usertype,'utype'=>'com', 'provider' => $_POST['provider']));
            if (!empty($link['linkMsg'])){
                $link['msg']    =   $link['linkMsg'];
            }
            $link['data']       =   $link['linkData'];
            if ($link['linkCode'] == 1){

                $link['errorcode'] = 9;
            }else if ($link['linkCode'] > 1 && $link['linkCode'] < 6){

                $link['errorcode'] = 2;
            }else if ($link['linkCode'] == 6){

                $link['errorcode'] = 3;
            }else if ($link['linkCode'] == 7){
                if ($link['linkSub'] != 1){
                    $link['errorcode'] = 4;
                }
            }else if ($link['linkCode'] == 8){

                $link['errorcode'] = 2;
            }else if ($link['linkCode'] == 9){

                $link['errorcode'] = 9;
            }else{

                $link['errorcode'] = 9;
                $link['data']['privacy'] = 1;
                unset($link['data']['linktel']);
                unset($link['data']['linkphone']);
            }
            $data['link']   =   $link;
			// 小程序用seo
			if ($this->plat == 'mini'){
		        // 获取seo使用的数据
		        $seodata['job_name']       =   $job['jobname']; // 职位名称
		        $seodata['company_sname']   =  !empty($job['shortname'])?$job['shortname']:$job['com_name']; // 公司简称
		        $seodata['company_name']   =   $job['com_name']; // 公司名称
		        $seodata['industry_class'] =   $job['hy_n']; // 所属行业
		        $seodata['job_class']      =   $job['job_one'] . ',' . $job['job_two'] . ',' . $job['job_three']; // 职位名称
		        $seodata['job_salary']     =   $job['job_salary']; // 职位薪资
		        $seodata['job_desc']       =   $this->GET_content_desc($job['description']); // 描述
		        $this->data = $seodata;
		        $seo  =  $this->seo('comapply','','','',false, true);
		        if ($_POST['provider'] == 'toutiao'){
		            // 抖音的分享与其他的不同，图标很小，只能用logo
		            $seo['share_pic'] = $job['com_logo_n'];
		        }
		        $data['seo']  =  $seo;
		    }
            $data['chatOpen']	=  $this->config['sy_chat_open'];
            $data['chat_name']  =  $this->config['sy_chat_name'] ? $this->config['sy_chat_name'] : '';
            $data['com_job_sexswitch'] = $this->config['com_job_sexswitch'];
            $data['applocation']  =  $this->config['sy_ioslocation'];
            $data['iosfk']	=	$this->config['sy_iospay'] ;
            $data['resume_kstd']=$this->config['resume_kstd'];
            $data['sy_freewebtel'] =$this->config['sy_freewebtel'];
            
            @include(DATA_PATH."api/alipay/alipay_aop.php");
            $data['zhima'] = $_POST['provider'] == 'alipay' && !empty($alipaydata['zm_service_id']) ? 1: 0;

            $factlist = array();
	        if($job['fact_status']==1){
	            $factlist = $companyM->getFactPic(array('uid'=>$job['uid']));
	        }
        	$data['factlist']	=	$factlist;
		}else{
			$msg			=	'职位不存在!';
			$error			=	0;
			$data['list']	=	array();
		}
		
		$this->render_json($error,$msg,$data);
	}
	/**
	 * 职位详情页-推荐职位、简历列表等其他数据(通过分开查询，提高页面显示速度)
	 */
	function jobShowOther_action(){
	    
	    $info  =  array(); 
	    include(CONFIG_PATH.'db.data.php');
	    
	    $JobM  =  $this -> MODEL('job');
	    $job   =  $JobM -> getInfo(array('id' => $_POST['id'] ,'r_status' => array('<>', 2)), array('field'=>'`id`,`uid`,`name`,`description`,`job1`,`x`,`y`,`rewardpack`,`zuid`,`state`,`status`,`r_status`,`com_logo`'));
	    
	    if (!empty($job)){
	        // 静态图
	        $data['mapkey']      =  !empty($this->config['map_key']) ? 1 : 2;
	        if ($data['mapkey'] ==1) {
	            $job['staticimg'] = $JobM->getJobMappic(array('jobid' => $_POST['id']));
	        }else{
	            $job['staticimg'] = '';
	        }
	        
	        // 查询企业近期活跃情况
	        $logM		=	$this -> MODEL('log');
	        $wtime		=	time()-3600*24*7;
	        $loginlog	=	$logM->getLoginLog(array('uid'=>$job['uid'],'usertype'=>2,'ctime'=>array('<',$wtime)));
	        
	        if(!empty($loginlog)){
	            $info['isloginlog']	=	1;
	        }
	        
	        $uid  =  $usertype  =  '';
	        
	        if (!empty($_POST['uid']) && !empty($_POST['token'])){
	            
	            $member    =  $this->yzToken($_POST['uid'], $_POST['token']);
	            $uid       =  $member['uid'];
	            $usertype  =  $member['usertype'];
	        }
	        if(empty($uid) || (!empty($uid) && $uid != $job['uid'])){
	            if($job['r_status']==0||$job['r_status']==3){
	                $msg= '企业暂未通过审核！';
	            }elseif($job['r_status']==2||$job['r_status']==4){
	                $msg= '企业已被锁定！';
	            }elseif($job['state'] == 0){
	                $msg= '职位审核中！';
	            }elseif($job['state'] == 3){
	                $msg= '该职位未通过审核！';
	            }
	            if(isset($msg)){
	                $this->render_json(0, $msg);
	            }
	        }
	        // app用分享数据
	        if ($this->plat == 'app'){
	            if ($_POST['provider'] == 'app'){
	                $data['shareData']  =  array(
	                    'url'       =>  Url('wap',array('c'=>'job','a'=>'comapply','id'=>$job['id'])),
	                    'title'     =>  $job['name'],
	                    'summary'   =>  mb_substr(strip_tags($job['description']), 0,30,'UTF8'),
	                    'imageUrl'  =>  checkpic($job['com_logo'], $this->config['sy_unit_icon'])
	                );
	            }
	        }
	        $hbids = array();
	        if (isset($_POST['provider'])){
	            if (in_array($_POST['provider'], array('weixin','app')) && $this->config['sy_haibao_isopen'] == 1){
	                $WhbM       =   $this->MODEL('whb');
	                $syJobHb    =   $WhbM->getWhbList(array('type' => 1, 'isopen'=>'1'));
	                if(!empty($syJobHb)){
	                    $hbids = array();
	                    foreach ($syJobHb as $hk => $hv) {
	                        $hbids[] = $hv['id'];
	                    }
	                }
	            }
	        }
	        $data['hbids']  =  !empty($hbids) ? $hbids : array();
	        $data['hbNum']  =  count($data['hbids']);
	        $data['ishb']  	=  $this->config['sy_haibao_isopen'] == 1 ? true : false;

	        $JobM->addJobHits($job['id']);
	        
	        if(!empty($job['x']) && !empty($job['y'])){
	            $info['x']			=	$job['x'];
	            $info['y']			=	$job['y'];
	            $info['staticimg']	=	$job['staticimg'];
	        }
	        
	        //赏金职位
	        if($job['rewardpack']==1){
	            $packM				  =	 $this -> MODEL('pack');
	            $reward				  =	 $packM -> getRewardJobInfo($job['id']);
	            $info['money']		  =	 floatval($reward['money']);
	            $info['sqmoney']	  =	 floatval($reward['sqmoney']);
	            $info['invitemoney']  =	 floatval($reward['invitemoney']);
	            $info['offermoney']	  =	 floatval($reward['offermoney']);
	            
	        }
	        // 根据是否有猎头模块，来确定是否有子账号功能
	        $sy_son = isset($arr_data['modelconfig']['lietou']) ? 1 : 2;
	        // 子账号部门
	        if ($sy_son == 1){
	            $CompanyaccountM  	=   $this->MODEL('companyaccount');
	            $department			=	$CompanyaccountM ->getInfo(array('uid'=>$job['zuid']),array('field'=>'`uid`,`name`'));
	            $info['department']	=	$department['name'];
	        }
	        //查询推荐职位
	        $recwhere  =  array(
	            'job1'      =>  $job['job1'],
	            'id'        =>  array('<>',$job['id']),
	            'uid'       =>  array('<>',$job['uid']),
	            'r_status'  =>  1,
	            'state'     =>  1,
	            'status'    =>  0,
	            'limit'     =>  5,
	            'orderby'   =>  'lastupdate,desc'
	        );
            if ($_POST['provider'] == 'toutiao') {

                $recwhere['PHPYUNBTWSTART_C']   =   '';
                $recwhere['name'][]             =   array('notlike', '带货');
                $recwhere['name'][]             =   array('notlike', '播');
                $recwhere['name'][]             =   array('notlike', '演员');
                $recwhere['PHPYUNBTWEND_C']     =   '';
                $recwhere['com_name']           =   array('like', '有限公司');
                // 薪资不能是面议
                $recwhere['minsalary']          =   array('>', 0);
            }
	        // 处理分站查询条件
	        if (!empty($_POST['did'])){
	            
	            $domain  =  $this->getDomain($_POST['did']);
	            
	            if (!empty($domain['provinceid'])){
	                $recwhere['provinceid']  =  $domain['provinceid'];
	            }
	            if (!empty($domain['cityid'])){
	                $recwhere['cityid']  =  $domain['cityid'];
	            }
	            if (!empty($domain['three_cityid'])){
	                $recwhere['three_cityid']  =  $domain['three_cityid'];
	            }
	            if (!empty($domain['hyclass'])){
	                $recwhere['hy']  =  $domain['hyclass'];
	            }
	        }
            $recjob = $JobM->getList($recwhere);
            $data['recjob'] = count($recjob['list']) ? $recjob['list'] : array();
	        //面试评价
	        $pjlist = $avgArr = array();
	        if ($this->config['com_msg_status'] == 1){
	            $msgwhere['status']	=	1;
	            $msgwhere['jobid']	=	$_POST['id'];
	            $msgwhere['limit']	=   2;
	            $msgwhere['orderby']	=	'ctime,desc';
	            
	            $companyM=  $this->MODEL('company');
	            $pjlist  =	$companyM->getCompanyMsgList($msgwhere);
	            $avg	 =	$companyM->getCompanyMsgInfo(array('jobid'=>$_POST['id'],'status'=>1),array('field'=>'count(*) as num,AVG(score) as score,AVG(desscore) as desscore,AVG(comscore) as comscore,AVG(hrscore) as hrscore'));
	            $avgArr  = 	array('num'=>$avg['num'],'score'=>round($avg['score'],1),'desscore'=>round($avg['desscore'],1),'hrscore'=>round($avg['hrscore'],1),'comscore'=>round($avg['comscore'],1));
	        }
	        
	        // 查询咨询记录
	        $msgList = array();
	        if ($this->config['com_message'] == 1){
	            $msgM	  =	 $this -> MODEL('msg');
	            $msgList  =	 $msgM -> getList(array('jobid' => $job['id'],'status'=>1, 'del_status'=>0, 'job_uid' => $job['uid'],'reply' => array('<>',''), 'orderby' => 'datetime,desc', 'limit' => 5));
	        }
	    }
	    
	    if(!empty($uid)){
	        
	        if($usertype == 1){
	            // 个人改为申请前查询，下面是版本向下兼容代码20220323
	            if (!isset($_POST['not_rnum'])){
	                // 当前个人的简历
	                $resumeM	=	$this->MODEL('resume');
	                $resumelist	=	$resumeM->getSimpleList(array('uid'=>$uid,'r_status'=>1,'status' => array('<>',2), 'state' => 1, 'orderby' => 'defaults, desc'),array('field'=>'`id`,`defaults`'));
	                foreach($resumelist as $key=>$val){
	                    if($val['defaults']==1){
	                        $defaults	=	$val['id'];
	                    }
	                }
	                $data['rnum']	    =	count($resumelist);
	                $data['defaults']	=	$defaults;
	            }
	            
	            if (!empty($job)){
	                // 增加职位浏览记录
	                $JobM -> addLookJob(array('uid' => $uid, 'jobid' => $job['id'], 'com_id' => $job['uid'], 'datetime' => time(), 'jobname'=>$job['name']));
	                
	                //投递数量
	                $UJWhere['uid']   		=   $uid;
	                $UJWhere['job_id']		=   $job['id'];
	                $UJWhere['isdel']   	=   9;
	                $userid_job    			=   $JobM -> getSqJobNum($UJWhere);
	                // 邀请面试数量
	                $yqWhere['uid']   		=   $uid;
	                $yqWhere['jobid']		=   $job['id'];
	                $yqWhere['isdel']   	=   9;
	                $yqNum                  =   $JobM->getYqmsNum($yqWhere);
	                $data['useridjob']		=	$userid_job || $yqNum ? 1 : 0;
	                //收藏数量
	                $FJWhere['uid']   		=   $uid;
	                $FJWhere['job_id']		=   $job['id'];
	                $FJWhere['type']  		=   '1';
	                $fav_job				=	$JobM -> getFavJobNum($FJWhere);
	                $data['favjob']			=	$fav_job ? 1 : 0;
	            }
	        }
	    }
	    
	    if (isset($arr_data['modelconfig']['spview']) && $this->config['sy_spview_web'] == 1 && $_POST['provider'] == 'weixin'){
	        // IOS APP 不支持视频面试
	        //包含此职位且可预约的视频面试
	        $spviewM        =   $this->MODEL('spview');
	        
	        $yytime         =   time()+($this->config['sy_spview_yytime']*3600);
	        
	        $spviewWhere    =   array(
	            
	            'starttime' =>  array('>',$yytime),
	            'status'    =>  1,
	            'jobid'     =>  array('findin',$job['id']),
	            'roomstatus'=>  0
	        );
	        
	        $spview         =   $spviewM->getInfo($spviewWhere,array('field'=>'id'));
	        
	        $data['spviewid']	=	$spview['id'];
	    }
	    
	    $data['avgArr']		=	count($avgArr) ? $avgArr : array();
	    $data['pjlist']	    =	count($pjlist) ? $pjlist : array();
	    $data['msgList']	=   count($msgList['list'])?$msgList['list']:array();
	    
	    $data['sy_shenming']=  $this->config['sy_shenming'];
	    $data['iosfk']		=  $this->config['sy_iospay'];
	    $data['wqy']		=  $this->config['com_message'];
	    $data['info']       =  $info;
        $data['jzl']        =  isset($this->config['sy_job_lookfx']) ? $this->config['sy_job_lookfx'] : 0;
        $data['sqNumBack']  =  isset($this->config['sy_sq_job_num']) ? $this->config['sy_sq_job_num'] : 0;


	    //切换身份是否开启
        $data['sy_user_change'] =$this->config['sy_user_change'];
        // 复制文本单改为弹出前查询，下面是版本向下兼容代码20220323
        if (!isset($_POST['not_jobwb'])){
            $wxpubtempM     = $this->MODEL('wxpubtemp');
            $wxpubtemp_html = $wxpubtempM->getOneJob($job['id'],$_POST['provider']);
            
            $data['wxpubtemp_html'] = $wxpubtemp_html;
        }
        // 职位详情广告
        $ad =   $this->ad_wxapp(array('name'=>'job_show'), null, 1);
        if (!empty($ad)){
            $data['ad'] = $ad[0];
        }
        if ($_POST['provider'] == 'weixin') {

            $xcxGroupCode = $this->obj->select_once('work_qrcode', array('scene' => 1, 'xcx_use' => 1), '`qr_code`,`xcx_icon`,`xcx_title`');
            $xcxGroupCode['xcx_icon']   =   checkpic($xcxGroupCode['xcx_icon']);
            $data['xcxGroupCode'] =   $xcxGroupCode;
        }
        $data['sy_msg_isopen'] = $this->config['sy_msg_isopen'];
        $data['sy_msg_login'] = $this->config['sy_msg_login'];

	    $this->render_json(1, 'ok', $data);
	}

    /**
     *
     */
	function addJobTelLog_action(){

		if($_POST['jobid'] || $_POST['comid']){

			$jobM = $this->MODEL('job');

			$uid = 	$usertype = 0;

			if($_POST['uid'] && $_POST['token']){

				$member		=	$this->yzToken((int)$_POST['uid'],$_POST['token']);
				$uid        =  	$member['uid'];
				$usertype   =  	$member['usertype'];
			}
			
			$jobM->addTelLog(
				array(
					'uid'		=>	$uid,
					'usertype'	=>	$usertype,
					'source'	=>	$_POST['source'],
					'jobid'		=>	intval($_POST['jobid']),
					'comid'		=>	intval($_POST['comid'])
				)
			);
		}
		
	}
	/*
	 * 更新微信小程序面试通知订阅状态
	 */
	function interviewSub_action(){
        $this->yzToken(intval($_POST['uid']),$_POST['token']);
        if ($_POST['is_accept']) {
            $JobM =	$this->MODEL('job');
            // 投递记录订阅状态改为已订阅
            $JobM->updSqJob(array('id' => intval($_POST['id']), 'uid' => intval($_POST['uid'])), array('is_subscribe' => 1));
		}
	}
    /**
     * 申请职位
     */
	function apply_action()
	{
		$member		=	$this->yzToken((int)$_POST['uid'],$_POST['token']);
		$uid        =  $member['uid'];
		$usertype   =  $member['usertype'];
		$jobid 		=	(int)$_POST['jobid'];
		$eid 		=	(int)$_POST['eid'];
		$JobM		=	$this -> MODEL('job');
		$data		=	array(
			'uid'		=>	$uid,
			'usertype'	=>	$usertype,
			'eid'		=>	$eid,
			'job_id'	=>	$jobid,
		    'port'		=>	$this->port
		);

		$res	=	$JobM -> applyJob($data);
        $rtdata = array();
		if ($res['errorcode'] == 9){// 投递成功
		    $data['error']	=1;
		    if ($_POST['source'] == 13) {// 微信小程序
		        $userM = $this->MODEL('userinfo');
                $member = $userM->getInfo(array('uid' => $uid));
                if ($member['wxopenid']) {// 小程序openid不为空
                    $subtplM = $this->MODEL('subtpl');
                    $tpl = $subtplM->getTpl(array('name' => 'interview_tplid'));
                    if (isset($tpl['tpl_id']) && $tpl['tpl_id']) {
                        $rtdata['tpl_id'] = $tpl['tpl_id'];// 邀请面试订阅消息模板id
                        $rtdata['td_id'] = $res['nid'];// 投递记录id
                    }
                }
            }
            // 支付宝就业相关代码
            if (isset($_POST['provider']) && $_POST['provider']=='alipay' && isset($_POST['code'])){
                if (file_exists(APP_PATH.'app/model/alipayapi.model.php')){

                    $apidata = array(
                        'sqid'         => $res['nid'],
                        'squid'        => $uid,
                        'jobid'        => $jobid,
                        'code'         => $_POST['code']
                    );
                    $alipayapi = $this->MODEL('alipayapi');
                    $alipayapi->jobSqSync($apidata);
                }
            }
		}elseif ($res['errorcode'] == 7){
		    // 简历完整度不足
		    $data['error']	=3;
		}elseif($res['errorcode'] == 10){
		    // 简历未公开
		    $data['error']	=4;
		}elseif($res['errorcode'] == 12){
		    // 没有简历
		    $data['error']	=5;
		}elseif ($res['errorcode'] == 101 || $res['errorcode'] == 102){
		    if (isset($_POST['version'])){
		        if ($res['errorcode'] == 101){
		            // 简历基本信息未完善
		            $data['error'] = 6;
		        }elseif ($res['errorcode'] == 102){
		            // 简历求职意向未完善
		            $data['error'] = 7;
		        }
		        $rtdata['eid'] = $res['eid'];
		    }else{
		        // 简历完整度不足（向下兼容）
		        $data['error'] = 3;
		    }
		}else{
		    $data['error']	=2;
		}
		$data['msg']	=	$res['msg'];
		$this->render_json($data['error'],$data['msg'], $rtdata);
	}
	/**
	 * 收藏职位
	 */
	function fav_action()
	{
		$member		=	$this->yzToken((int)$_POST['uid'],$_POST['token']);
		$uid        =   $member['uid'];
		$usertype   =   $member['usertype'];
		$jobid 		=	(int)$_POST['jobid'];
		$JobM		=	$this -> MODEL('job');
		$data		=	array(
			'uid'		=>	$uid,
			'usertype'	=>	$usertype,
			'job_id'	=>	$jobid
		);
		$res		=	$JobM -> collectJob($data);
		$data['error']	=	$res['errorcode']==9 ? 1 : 2;
		$data['msg']	=	$res['msg'];
		$this->render_json($data['error'],$data['msg']);
	
	}

    /**
     * 取消收藏职位
     */
    function cancelFav_action()
    {
        $member		=	$this->yzToken((int)$_POST['uid'],$_POST['token']);

        $uid        =   $member['uid'];
        $usertype   =   $member['usertype'];
        $jobid 		=	(int)$_POST['jobid'];

        $JobM		=	$this -> MODEL('job');

        $data		=	array(
            'uid'		=>	$uid,
            'usertype'	=>	$usertype,
            'job_id'	=>	$jobid
        );
        $res		=	$JobM->cancelFavJob($data);
        $data['error']	=	$res['errcode']==9 ? 1 : 2;
        $data['msg']	=	$res['msg'];
        $this->render_json($data['error'],$data['msg']);

    }
 	 //举报职位
  	function repostlist_action(){
	
		$reportM	  			=	  	$this->MODEL('report');
	
		$jobM					=		$this->MODEL('job');
	
		$member		  			=	  	$this->yzToken((int)$_POST['uid'],$_POST['token']);
	
		$uid        			=   	$member['uid'];
	
		$usertype   			=  		$member['usertype'];
	
		$row					=		$reportM->getReportOne(array('p_uid'=>$uid,'eid'=>(int)$_POST['jobid'],'c_uid'=>(int)$_POST['jobuid'],'usertype'=>$usertype));	
		
		$data['error']			=		$row? 1		:	2;

		$this->render_json($data['error']);
	}
	//保存举报职位
	function savereport_action(){
		//先判断是否登录
		$reportM  =  $this->MODEL('report');
		$jobM     =  $this->MODEL('job');
		$member   =  $this->yzToken($_POST['uid'],$_POST['token']);
		if($member['usertype']==1){
			//同时查询
		    $row	=		$reportM->getReportOne(array('p_uid'=>$member['uid'],'eid'=>(int)$_POST['jobid'],'c_uid'=>(int)$_POST['jobuid'],'usertype'=>$member['usertype']));
			if($row && is_array($row)){
				$data['error']	='2';	
				$data['msg']	='已举报该职位';
			}else{
				//查询名称
				$job	=	$jobM->getInfo(array('id' => intval($_POST['jobid'])),array('field'=>'`uid`,`com_name`'));
				if($job['uid']==$member['uid']){
					$data['error']	='2';	
					$data['msg']	='自己不能举报自己发布职位';
				}else{		
					$data	=	array(
						'c_uid'		=>	$job['uid'],
						'inputtime'	=>	time(),
					    'p_uid'		=>	$member['uid'],
						'usertype'	=>	$member['usertype'],
						'eid'		=>	(int)$_POST['jobid'],
						'r_name'	=>	$job['com_name'],
						'username'	=>	$member['username'],
						'r_reason'	=>	$_POST['r_reason'],
						'did'		=>	$member['did']
					);
					
					$nid	=	$reportM->addJobReport($data);
					if($nid){
						$data['error']	='1';	
						$data['msg']	=	'举报成功！';
						
					}else{
						$data['error']	='2';	
						$data['msg']	=	'举报失败！';
					}
				}
			}
		}else{
			$data['error']	='2';	
			$data['msg']	='只有个人用户才能举报职位';
		}
		$this->render_json($data['error'],$data['msg']);
	}
	
	//申请赏金职位
	function rewardshare_action(){
		$packM 			= 		$this->MODEL('pack');
		$resumeM		=		$this->MODEL('resume');
		if(!$_POST['jobid']){
			$data['error']	=	2;
			$data['msg']	=	'参数不正确';
		}else{
            $member             =   $this->yzToken((int)$_POST['uid'], $_POST['token']);
            $where['uid']       =   $member['uid'];
            $where['defaults']  =   1;
            $rexpect            =   $resumeM->getExpect($where);
            if (!empty($rexpect)) {

                if ($rexpect['state'] == 1) {

                    //判断是否已申请职位
                    $expectwhere['uid']     =   $member['uid'];
                    $expectwhere['eid']     =   $rexpect['id'];
                    $expectwhere['comid']   =   $_POST['comid'];
                    $expectwhere['jobid']   =   $_POST['jobid'];
                    $packnum    =   $packM->getPackNum($expectwhere);
                    if ($packnum > 0) {

                        $data['error']  =   1;
                    } else {

                        $data['error']  =   3;
                    }
                } else {

                    $data['error']  =   4;
                    $data['msg']    =   '您的简历审核中，无法申请赏金职位';
                }
            } else {

                $data['error']  =   2;
                $data['msg']    =   '您暂未默认简历，请先添加';
            }
		}

		$this->render_json($data['error'],$data['msg']);
	}
	//保存赏金列表申请
	function saverewardshare_action(){
		$member			=	  	$this->yzToken((int)$_POST['uid'],$_POST['token']);
		$packM 			= 		$this->MODEL('pack');
		$uid			=		$member['uid'];
		$usertype		=		$member['usertype'];
		$jobid			=		(int)$_POST['jobid'];
		$comid			=		(int)$_POST['comid'];
		$return			=		$packM->sqRewardJob($jobid,$uid,1,'');
		
		$this->render_json($return['error'],$return['msg'],$return['tid']);
		
	}
	// 申请赏金职位，前置条件查询
	function rewardsharelist_action(){
		//查询职位列表
		$member			=	  	$this->yzToken((int)$_POST['uid'],$_POST['token']);
		$packM 			= 		$this->MODEL('pack');
		$resumeM		=		$this->MODEL('resume');
		if(!$_POST['jobid']){
			$data['error']	=	2;
			$data['msg']	=	'参数不正确';
		}else{
			//查找当前赏金职位是否存在
			$jobid						=		(int)$_POST['jobid'];
			$rewardjob					=		$packM->getRewardJobInfo($jobid);
			if($rewardjob){
				//判断简历是否匹配
				$where['uid']			=		$member['uid'];
				$where['defaults']		=		1;
				$where['state']			=		1;
				$rexpect				=		$resumeM->getExpect($where);
				
        //工作经历
				$checkwhere['uid']	=		$member['uid'];
				$checkwhere['eid']	=		$rexpect['id'];
				$work		=		$resumeM->getResumeWork($checkwhere);
				$rewardjob['eid']	=		$rexpect['id'];
				if($rewardjob['exp']==1 ){
					if($work){
						$rewardjob['worknum']		=		1;
						$rewardjob['workname']		=		'符合';
					}else{
						$rewardjob['worknum']		=		0;
						$rewardjob['workname']		=		'不符合';
					}
					
				}else{
					$rewardjob['worknum']		=		1;
					$rewardjob['workname']		=		'不限';
				}
				//教育经历
				$edu		=		$resumeM->getResumeEdu($checkwhere);
				if($rewardjob['edu']==1 ){
					if($edu){
						$rewardjob['edunum']		=		1;
						$rewardjob['eduname']		=		'符合';
					}else{
						$rewardjob['edunum']		=		0;
						$rewardjob['eduname']		=		'不符合';
					}
					
				}else{
					$rewardjob['edunum']		=		1;
					$rewardjob['eduname']		=		'不限';
				}
				//项目经历
				$project	=		$resumeM->getResumeProject($checkwhere);
				if($rewardjob['project']==1){
					if($project){
						$rewardjob['projectnum']		=		1;
						$rewardjob['projectname']		=		'符合';
					}else{
						$rewardjob['projectnum']		=		0;
						$rewardjob['projectname']		=		'不符合';
					}
					
				}else{
					$rewardjob['projectnum']		=		1;
					$rewardjob['projectname']		=		'不限';
				}
				//技能证书
     
				$skill		=	$resumeM->getResumeSkill($checkwhere);
			
				if($rewardjob['skill']==1){
					if($skill&&$skill['pic']){
						$rewardjob['skillnum']		=		1;
						$rewardjob['skillname']		=		'符合';
					}else{
						$rewardjob['skillnum']		=		0;
						$rewardjob['skillname']		=		'不符合';
					}
					
				}else{
					$rewardjob['skillnum']		=		1;
					$rewardjob['skillname']		=		'不限';
				}
			}else{
				$data['error']	=	2;
				$data['msg']	=	'相关职位不存在';
			}
		}
		$this->render_json($data['error'],$data['msg'],$rewardjob);
	}

    /**
     * 赏金职位列表
     */
	function rewardList_action(){
        
        $_POST['keyword']	=   $this->stringfilter($_POST['keyword']);
		
		$limit				=	$_POST['limit']?$_POST['limit']:20;
		
		$page  				=  	$_POST['page'];
		
		$where	=	array(
			'rewardpack'=>	'1',
			'r_status'	=>	'1',
			'state'		=>	'1',
			'status'	=>	'0',
			'name'		=>	array('like',$_POST['keyword']),
			'orderby' 	=> 	'lastupdate,desc'
		);
		// 处理分站查询条件
		if (!empty($_POST['did'])){
		    
		    $domain  =  $this->getDomain($_POST['did'], true);
		    
		    if (isset($domain['didcity'])){
		        
		        if (!empty($domain['provinceid'])){
		            $where['provinceid']  =  $domain['provinceid'];
		        }
		        if (!empty($domain['cityid'])){
		            $where['cityid']  =  $domain['cityid'];
		        }
		        if (!empty($domain['three_cityid'])){
		            $where['three_cityid']  =  $domain['three_cityid'];
		        }
		    }
		    if (isset($domain['didhy'])){
		        
		        if (!empty($domain['hyclass'])){
		            $where['hy']  =  $domain['hyclass'];
		        }
		    }
		}
		if($page){
			$pagenav		=	($page-1)*$limit;
			
			$where['limit']	=	array($pagenav,$limit);
		}else{
			$where['limit']	=	$limit;
		}
        
        $packM 		= 	$this->MODEL('pack');
		
        $rows 		= 	$packM->getRewardJobWxList($where);
		
        if($rows&&is_array($rows)){
            
            $data['error']  =   1;
			
            $data['list']   =   count($rows) ? $rows : array();
			
        }else{
            
            $data['error']  =   2;
        }
        $this->render_json($data['error'],'',$data);
        
    }

    /**
     * 分享赚赏金职位列表
     */
    function share_action(){

		$where['orderby']	=	'lastupdate,desc';
		
		if ($_POST['limit']){
		    $limit				=	$_POST['limit'];
		    if($_POST['page']){//分页
		        $pagenav		=	($_POST['page']-1)*$limit;
		        $where['limit']	=	array($pagenav,$limit);
		    }else{
		    	$where['limit']	=	$limit;
		    }
		}
		
		$packM 		= $this->MODEL('pack');
		$shareJob 	= $packM->getShareJob($where);
		
		$this->render_json(0,'ok',$shareJob);
	}


    /**
     * 分享赚赏金职位详情
     */
    function shareshow_action()
    {
		
		$packM  = $this->MODEL('pack');
		$shareJobOne = $packM->getShareJobOne($_POST['id']);
		
		if(empty($shareJobOne)){
			$this->render_json(1,'没有红包职位信息');
		}else{
			
		    if (!empty($_POST['uid']) && !empty($_POST['token'])){
		        
		        $member    =  $this->yzToken($_POST['uid'], $_POST['token']);
		        
		    }
		    //分享赏金发放
		    if (!empty($_POST['fx'])){
		        $fx   =  $_POST['fx'];
		        $userInfoM  =  $this->MODEL('userinfo');
		        $fxm     =  $userInfoM->getInfo(array('uid'=>$fx['uid']),array('field'=>'`uid`,`username`,`usertype`,`password`,`salt`,`wxopenid`'));
		        $mdtoken  =  md5($fxm['username'].$fxm['password'].$fxm['salt'].$fxm['usertype']);
		        
		        if($fx['token'] == $mdtoken && !empty($fxm['wxopenid'])){
		            
		            $packM->shareJobLook($shareJobOne,$fxm['uid'],$fxm['wxopenid']);
		        }
		    }
		    
			$data['list']           =  count($shareJobOne) ? $shareJobOne : array();
			$data['job_name']		=  $shareJobOne['name'];//职位名称
			$data['company_name']	=  $shareJobOne['com_name'];//公司名称
			$data['job_desc']		=  $this->GET_content_desc($shareJobOne['description']);//描述
			// app用分享数据
			if (!empty($_POST['lsb'])){
			    
			    $data['wapurl']         =  Url('wap',array('c'=>'reward','a'=>'shareshow','id'=>$shareJobOne['id']));
			    
			    $companyM  =  $this->MODEL('company');
			    $com       =  $companyM->getInfo($shareJobOne['uid'],array('field'=>'`logo`,`logo_status`','logo'=>1));
			    $data['imageUrl']  =  $com['logo'];
			}
			$data['cuswitch']   =  $this->config['sy_user_change'];
			$this->render_json(0,'ok',$data);
		}
	}

    /**
     * 快速申请职位页面数据获取
     */
	function jobapply_action()
    {

		$data['resume_kstd_req']		=	$this->config['resume_kstd_req'];
        $data['resume_create_exp']		=	$this->config['resume_create_exp'];
		$data['resume_create_edu']		=	$this->config['resume_create_edu'];
		$data['resume_create_project']	=	$this->config['resume_create_project'];
		$data['expcreate']				=	$this->config['expcreate'];
        $data['educreate']				=	$this->config['educreate'];

        $data['jobClassCreate']		    =	$this->config['sy_resume_job_classid'];

		$data['reg_pw_sp']		        =	$this->config['reg_pw_sp'];
		$data['reg_pw_zm']		        =	$this->config['reg_pw_zm'];
		$data['reg_pw_num']	            =	$this->config['reg_pw_num'];
		$data['code_web']               =   strpos($this->config['code_web'],'注册会员')!==false ? true : false;
		$data['code_kind']              =   $this->config['code_kind'];

		if ($this->config['sy_msg_isopen'] == 1 && $this->config['reg_real_name_check'] == 1 && $this->config['sy_msg_regcode'] == 1){
		    $data['isSMSVer']           =   1;
        }else{
            $data['isSMSVer']           =   0;
        }

        $data['resumename']             =   !empty($this->config['sy_resumename_num']) ? $this->config['sy_resumename_num'] : 0;

		$this->render_json(0,'',$data);
	}
	//快速申请职位入口
	function temporaryresume_action(){
		$userinfoM	=	$this->MODEL("userinfo");
        $_POST 		= 	$this->post_trim($_POST);
        $tdResArr = $userinfoM->fastToudi($_POST, 3);
        $data['msg'] = $tdResArr['msg'];
		$data['user'] = $tdResArr['user'];
        $data['error']	= $tdResArr['errcode'];
        if ($tdResArr['jobid']) {
            $data['jobid'] = intval($tdResArr['jobid']);
        }
        if ($tdResArr['eid']) {
            $data['eid'] = intval($tdResArr['eid']);
        }
        $this->render_json($tdResArr['errcode'],$tdResArr['msg'], $data);
	}

    /**
     * 职位详情-我要提问
     */
  	function savereply_action(){
		$msgM				=	$this -> MODEL('msg');
		  
		$member   			=  	$this -> yzToken($_POST['uid'],$_POST['token']);
		  
		$_POST				=	$this -> post_trim($_POST);
		  
		$_POST['uid']		=	$member['uid'];
		
		$_POST['username']	=	$member['username'];
		
		$_POST['usertype']	=	$member['usertype'];

		
		$res				=	$msgM -> addMsg($_POST, 'auto');
		
		$this->render_json($res['error'],$res['msg']);
  	}

    /**
     * 我要提问-热门问题
     */
	function getHotIssues_action(){
        $cacheM = $this->MODEL('cache');
        $com = $cacheM->GetCache('com');
        $data = [];
        if(!empty($com['comdata']['hot_issues'])){
            $hot_issues = $com['comdata']['hot_issues'];
            $comclass_name = $com['comclass_name'];
            foreach ($hot_issues as $val){
                $data['list'][] = $comclass_name[$val];
            }
        }
        $this->render_json(0, '', $data);
    }
    /**
     * 简历竞争力
     */
    function compete_action()
    {
        if (!empty($_POST['uid']) && !empty($_POST['token'])){
            
            $member    =  $this->yzToken($_POST['uid'], $_POST['token']);
        }
        if ($_POST['id']) {
            
            $competeM   =   $this->MODEL('compete');
            
            $List       =  $competeM->userJob($member['uid'], (int) $_POST['id'], $member['usertype']);
            
            $error		=	1;
            $msg		=	'';
            if($List['errcode'] == '2'){
                $error  =	0;
                $msg 	= 	'行业招聘较少，暂无足够样本用于数据分析！';
            }
            
            $this->render_json($error,$msg, $List);
        }
    }
    // 获取职位联系方式
    function getLink_action(){
        
        $member  =  $this->yzToken($_POST['uid'], $_POST['token']);
        
        $JobM    =  $this->MODEL('job');
        $link	 =  $JobM->getCompanyJobTel(array('id'=>$_POST['jobid'],'uid'=>$member['uid'],'usertype'=>$member['usertype'],'isgetprv'=>$this->config['sy_comprivacy_open']));

        $link['msg']        =   $link['linkMsg'];
        $link['errorcode']  =   $link['linkCode'];
        $link['data']       =   $link['linkData'];
        
        $this->render_json(0,'ok', $link);
    }
    // 查询职位复制文本内容
    function getJobWb_action(){
        
        $jobid = intval($_POST['jobid']);
        
        $wxpubtempM     = $this->MODEL('wxpubtemp');
        $wxpubtemp_html = $wxpubtempM->getOneJob($jobid,$_POST['provider']);
        $data['wxpubtemp_html'] = $wxpubtemp_html;
        
        $this->render_json(0,'ok', $data);
    }

    function getHbList_action()
    {

        if ($this->config['sy_haibao_isopen'] == 1)
        {

            $WhbM               =   $this->MODEL('whb');
            $hbList             =   $WhbM->getWhbList(array('type' => 1, 'isopen' => '1'));
            $return['hbList']   =   $hbList;
            $this->render_json(0, '', $return);
        } else {

            $this->render_json(-1, '暂未开启海报分享');
        }
    }
    // 职位详情-投递简历   6.2向下兼容，勿删
    function index_ajaxjob_action(){
        
        $member =  	$this -> yzToken($_POST['uid'],$_POST['token']);
        
        $resumeWhere = array(
            'uid'	 	=> $member['uid'],
            'defaults' 	=> 1
        );
        $ResumeM  =	 $this -> MODEL('resume');
        $list	  =	 $ResumeM -> getSimpleList($resumeWhere, array('field' => '`id`,`name`,`defaults`,`state`,`status`'));
        
        if(!empty($list)){
            
            foreach ($list as $k=>$v){
                if ($v['defaults'] == 1){
                    if ($v['state'] == 2){
                        $this->render_json(2, '您的简历被举报，请联系管理员了解详情，客服电话：'.$this->config['sy_freewebtel']);
                    }
                    if ($v['state'] == 3){
                        $this->render_json(3, '您的简历未通过审核，请按审核反馈调整简历');
                    }
                    if ($this->config['sy_shresume_applyjob'] != 1 && $v['state'] == 0){
                        // 简历必须审核通过才能投递，默认简历是未审核状态
                        $this->render_json(2, '您的简历尚未完成审核，请联系管理员加快审核进度，客服电话：'.$this->config['sy_freewebtel']);
                    }
                    if ($v['status'] == 2){
                        // 简历隐私状态是不公开
                        $this->render_json(3, '您的简历已隐藏，调整为公开后才能投递');
                    }
                }else{
                    // 非默认简历,排除不能投递的简历
                    if ($v['state'] > 1){
                        unset($list[$k]);
                    }
                    if ($this->config['sy_shresume_applyjob'] != 1 && $v['state'] == 0){
                        unset($list[$k]);
                    }
                    if ($v['status'] == 2){
                        // 简历隐私状态是不公开
                        unset($list[$k]);
                    }
                }
            }
            $this->render_json(1, 'ok', $list);
        }else{
            
            $this->render_json(4, '您还没有简历，请先创建简历');
        }
    }

    /**
     *users:王旭
     *Data:2023/2/13
     *Time:16:03
     */
    function getJobLink_action()
    {
        $id         =   intval($_POST['id']);
        $JobM       =   $this->MODEL('job');
        $dataArr    =   array('id' => $id, 'uid' => $this->uid, 'usertype' => $this->usertype, 'isgetprv' => $this->config['sy_comprivacy_open']);
        $telRes     =   $JobM->getJobLink($dataArr);
        $this->render_json(1, 'ok', $telRes);
    }
    
    function zhimaAgreement_action(){
        
        $arr = array(
            'jobid'            => $_POST['id'],
            'out_agreement_no' => time().mt_rand(111111,999999),
            'onceToken'        => time().mt_rand(111111,999999)
        );
        
        $aliM = $this->MODEL('alipayapi');
        $res = $aliM->zhimaAgreement($arr);
        
        if (isset($res['msg'])){
            
            $this->render_json(-1, $res['msg']);
        }else{
            $data['signStr'] = $res;
            $data['out_agreement_no'] = $arr['out_agreement_no'];
            $data['onceToken']        = $arr['onceToken'];
            $this->render_json(1, 'ok', $data);
        }
    }
    function zhimaJobworth_action(){
        
        $arr = array(
            'out_agreement_no' => $_POST['out_agreement_no'],
            'onceToken'        => $_POST['onceToken'],
            'source'           => $_POST['source'],
            'code'             => $_POST['code'],
            'jobid'            => $_POST['id']
        );
        
        $aliM = $this->MODEL('alipayapi');
        $res = $aliM->zhimaJobworth($arr);
        
        if (isset($res['msg'])){
            
            $this->render_json(-1, $res['msg']);
        }else{
            $this->render_json(0, 'ok', $res);
        }
    }
}
?>