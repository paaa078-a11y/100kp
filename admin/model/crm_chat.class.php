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
class crm_chat_controller extends adminCommon{

    //设置高级搜索功能
    function set_search($CacheList = array())
    {

        if (!$CacheList){

            $cacheM		=	$this -> MODEL('cache');
            $CacheList	=	$cacheM -> GetCache(array('user', 'job', 'city'));

            $setArr	    =	array(
                'userdata'		=>	$CacheList['userdata'],
                'userclass_name'=>	$CacheList['userclass_name'],
                'job_name'		=>	$CacheList['job_name'],
                'city_name'		=>	$CacheList['city_name']
            );
            $this -> yunset($setArr);
        }

        $userdata       =   $CacheList['userdata'];
        $userclass_name =   $CacheList['userclass_name'];

        foreach($userdata['user_type'] as $k=>$v){

            $type[$v]   =   $userclass_name[$v];
        }

        foreach($userdata['user_edu'] as $k=>$v){

            $edu[$v]    =   $userclass_name[$v];
        }

        foreach($userdata['user_word'] as $k=>$v){

            $exp[$v]    =   $userclass_name[$v];
        }

        foreach($userdata['user_report'] as $k=>$v){

            $report[$v] =   $userclass_name[$v];
        }

        include(CONFIG_PATH.'db.data.php');

        $source		=    $arr_data['source'];
        $uptime		=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
        $adtime		=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
        $status     =   array('1'=>'已审核','2'=>'已锁定','3'=>'未通过','4'=>'未审核');
        $integrity  =	$arr_data['integrity_name'];
        $salary     =   array('2000_4000' => '2000-4000', '4000_6000' => '4000-6000', '6000_8000' => '6000-8000', '8000_10000' => '8000-10000', '10000' => '10000以上');
        $age        =   array('16_20' => '16-20岁', '21_30' => '21-30岁', '31_40' => '31-40岁', '41_50' => '41-50岁', '50' => '50岁以上');
        $isRemark	=	array('1'=>'是','2'=>'否');

        $search[]   =   array('param'=>'status','name'=>'简历审核','value'=>$status);
        $search[]	=	array('param'=>'crm_tel','name'=>'电话联系','value'=>array('1'=>'未联系','2'=>'已联系'));
        $search[]	=	array('param'=>'crm_wx','name'=>'微信联系','value'=>array('1'=>'未联系','2'=>'已联系'));
        $search[]	=	array('param'=>'crm_chat','name'=>'聊天联系','value'=>array('1'=>'未联系','2'=>'已联系'));
        $search[]	=	array('param'=>'uptime','name'=>'更新时间','value'=>$uptime);
        $search[]	=	array('param'=>'source','name'=>'数据来源','value'=>$source);
        $search[]	=	array('param'=>'adtime','name'=>'添加时间','value'=>$adtime);
        $search[]	=	array('param'=>'type','name'=>'工作性质','value'=>$type);
        $search[]	=	array('param'=>'edu','name'=>'最高学历','value'=>$edu);
        $search[]	=	array('param'=>'exp','name'=>'工作经验','value'=>$exp);
        $search[]	=	array('param'=>'report','name'=>'到岗时间','value'=>$report);
        $search[]   =   array('param'=>'salary','name'=>'薪资','value'=>$salary);
        $search[]   =   array('param'=>'age','name'=>'年龄','value'=>$age);
        $search[]	=	array('param'=>'remark','name'=>'是否备注','value'=>$isRemark);
        $this->yunset('source',$source);

        $this->yunset('search_list',$search);
        $this->yunset('integrity', $integrity);
    }
    /**
     * 会员-个人-简历管理
     */
    function index_action()
    {
        // 清理来源，方式沟通弹出框重复弹出
        if (count($_GET) > 2){
            unset($_GET['ly']);
        }
        $resumeM    =   $this->MODEL('resume');

        $urlarr     =   array();
        $where      =   'a.`defaults` = 1 ';

        include(CONFIG_PATH . 'db.data.php');

        //搜索类型和搜索关键字
        if ($_GET['keyword']) {

            $keytype = intval($_GET['keytype']);
            $keyword = trim($_GET['keyword']);

            if ($keytype == 1) {

                $where .= " and a.uname like '%$keyword%'";
            } elseif ($keytype == 2) {

                $where .= " and a.id = $keyword";
            } elseif ($keytype == 3) {

                $where .= " and a.name like '%$keyword%'";
            } elseif ($keytype == 4) {

                $mUids              =   array();
                $userInfoM          =   $this->MODEL('userinfo');
                $mWhere['username'] =   array('like', $keyword);
                if (!empty($mWhere)) {
                    $uidList        =   $userInfoM->getList($mWhere, array('field' => '`uid`'));
                    if (!empty($uidList)) {
                        foreach ($uidList as $uv) {
                            $mUids[]=   $uv['uid'];
                        }
                    }
                    $where  .= " and a.uid in (" . pylode(',', $mUids) . ")";
                }

            } elseif ($keytype == 5) {
                $mUids		        =	array();
                $userInfoM	        =	$this -> MODEL('userinfo');
                $mWhere['telphone'] =   array('like', $keyword);
                if(!empty($mWhere)){
                    $uidList	    =	$userInfoM  ->  getUserInfoList($mWhere, array('usertype'=>1,'field' => '`uid`'));
                    if(!empty($uidList)){
                        foreach($uidList as $uv){
                            $mUids[]=	$uv['uid'];
                        }
                    }
                    $where .= " and a.uid in (".pylode(',', $mUids).")";
                }
            } elseif ($keytype == 6) {                      //  教育经历

                $eduWhere   =   array(
                    'name'      =>  array('like', $keyword),
                    'title'     =>  array('like', $keyword, 'OR'),
                    'specialty' =>  array('like', $keyword, 'OR')
                );

                $edu        =   $resumeM->getResumeEdus($eduWhere, 'eid');

                if ($edu) {

                    $eids = array();
                    foreach ($edu as $v) {

                        $eids[] = $v['eid'];
                    }

                    $where .= " and a.id in (".pylode(',', $eids).")";
                }
            } elseif ($keytype == 7) {                      //  工作经历

                $workWhere  =   array(
                    'name'      =>  array('like', $keyword),
                    'title'     =>  array('like', $keyword, 'OR'),
                    'content'   =>  array('like', $keyword, 'OR')
                );
                $work       =   $resumeM->getResumeWorks($workWhere, 'eid');
                if ($work) {
                    $eids   =   array();
                    foreach ($work as $v) {
                        $eids[] =   $v['eid'];
                    }
                    $where .= " and a.id in (" . pylode(',', $eids) . ")";
                }
            } elseif ($keytype == 8) {                      //  项目经历

                $proWhere  =   array(
                    'name'      =>  array('like', $keyword),
                    'title'     =>  array('like', $keyword, 'OR'),
                    'content'   =>  array('like', $keyword, 'OR')
                );
                $work       =   $resumeM->getResumeProjects($proWhere, 'eid');
                if ($work) {
                    $eids   =   array();
                    foreach ($work as $v) {
                        $eids[] =   $v['eid'];
                    }
                    $where .= " and a.id in (".pylode(',', $eids).")";
                }
            } elseif ($keytype == 9) {                      //  培训经历

                $trainWhere  =   array(
                    'name'      =>  array('like', $keyword),
                    'title'     =>  array('like', $keyword, 'OR'),
                    'content'   =>  array('like', $keyword, 'OR')
                );
                $work       =   $resumeM->getResumeTrains($trainWhere, 'eid');
                if ($work) {
                    $eids   =   array();
                    foreach ($work as $v) {

                        $eids[] =   $v['eid'];
                    }
                    $where .= " and a.id in (".pylode(',', $eids).")";
                }
            } elseif ($keytype == 10) {                      //  职业技能

                $skillWhere  =   array(
                    'name'      =>  array('like', $keyword),
                    'title'     =>  array('like', $keyword, 'OR'),
                    'content'   =>  array('like', $keyword, 'OR')
                );
                $work       =   $resumeM->getResumeSkills($skillWhere, 'eid');
                if ($work) {
                    $eids   =   array();
                    foreach ($work as $v) {
                        $eids[] = $v['eid'];
                    }
                    $where .= " and a.id in (".pylode(',', $eids).")";
                }
            }
            $urlarr['keytype'] = $keytype;
            $urlarr['keyword'] = $keyword;
        }
        //来源
        if ($_GET['status']) {

            $status = intval($_GET['status']);

            if ($status == 2) {

                $where .= " and a.r_status = 2";
            } else {

                $where .= " and a.state = " . ($status == 4 ? 0 : $status);
            }

            $urlarr['status'] = $status;
        }
        //电话联系
        if ($_GET['crm_tel']) {
            
            $where .= " and a.crm_tel = ".intval($_GET['crm_tel']);
            
            $urlarr['crm_tel'] = intval($_GET['crm_tel']);
        }
        //微信联系
        if ($_GET['crm_wx']) {
            
            $where .= " and a.crm_wx = ".intval($_GET['crm_wx']);
            
            $urlarr['source'] = intval($_GET['crm_wx']);
        }
        //聊天联系
        if ($_GET['crm_chat']) {
            
            $where .= " and a.crm_chat = ".intval($_GET['crm_chat']);
            
            $urlarr['crm_chat'] = intval($_GET['crm_chat']);
        }
        //来源
        if ($_GET['source']) {

            $where .= " and a.source = ".intval($_GET['source']);

            $urlarr['source'] = intval($_GET['source']);
        }

        //发布时间
        if ($_GET['adtime']) {

            $adtime = intval($_GET['adtime']);

            if ($adtime == 1) {

                $where .= " and a.ctime > ".strtotime('today');

            } else {

                $where .= " and a.ctime > ".strtotime('-' . $adtime . ' day');
            }

            $urlarr['adtime'] = $adtime;
        }

        //更新时间
        if ($_GET['uptime']) {

            $uptime = intval($_GET['uptime']);

            if ($uptime == 1) {

                $where .= " and a.lastupdate > ".strtotime('today');

            } else {

                $where .= " and a.lastupdate > ".strtotime('-' . $uptime . ' day');
            }
            $urlarr['uptime'] = $uptime;
        }

        //工作性质
        if ($_GET['type']) {

            $where .= " and a.type = ".intval($_GET['type']);

            $urlarr['type'] = $_GET['type'];
        }

        //学历要求
        if ($_GET['edu']) {

            $where .= " AND a.`edu` =".$_GET['edu'];

            $urlarr['edu'] = intval($_GET['edu']);
        }

        //工作经验
        if ($_GET['exp']) {

            $where .= " AND a.`exp` =".$_GET['exp'];

            $urlarr['exp'] = intval($_GET['exp']);
        }

        //到岗时间
        if ($_GET['report']) {

            $where .= " and a.report = ".intval($_GET['report']);

            $urlarr['report'] = intval($_GET['report']);
        }

        //简历完整度
        if ($_GET['integrity']) {
			if ($_GET['integrity'] == 55) {
                $where .= " and a.integrity = 55";
            } else if ($_GET['integrity'] == 65) {
                $where .= " and a.integrity = 65";
            } else {
                $integrity_val = $arr_data['integrity_val'];
                $where .= " and a.integrity >= ".$integrity_val[$_GET['integrity']];
            }
        }
        
        //薪资待遇
        if (!empty($_GET['salary'])) {
            $salary = explode('_', $_GET['salary']);
            if ($salary[0] && $salary[1]) {
                $where.= " AND ((a.`minsalary`<=".intval($salary[0])." and a.`maxsalary`>=".intval($salary[0]).")
                 or (a.`minsalary`<=".intval($salary[1])." and a.`maxsalary`>=".intval($salary[1])."))";
            } elseif ($salary[0] && !$salary[1]) {
                $where.= " AND ((a.`minsalary`<=".intval($salary[0])." and a.`maxsalary`>=".intval($salary[0]).") 
                        or (a.`minsalary`>=".intval($salary[0])." and a.`maxsalary`>=".intval($salary[0]).")
                        or (a.`minsalary`!=0 and  a.`maxsalary`=0))";
            } elseif (!$salary[0] && $salary[1]) {
                $where.= " AND ((a.`minsalary`<=".intval($salary[1])." and a.`maxsalary`>=".intval($salary[1]).")
                        or (a.`minsalary`<=".intval($salary[1])." and a.`maxsalary`<=".intval($salary[1]).")  
                        or (a.`minsalary`<=".intval($salary[1])." and a.`maxsalary`=0) 
                        or (a.`minsalary`=0 and a.`maxsalary`!=0)
                        )";
            }
        }

        //年龄
        if (!empty($_GET['age'])) {
            $age = explode('_', $_GET['age']);
            if ($age[0] && $age[1]) {
                $mintime=date("Y-m-d",strtotime("-".$age[0]." year"));
                $maxtime=date("Y-m-d",strtotime("-".$age[1]." year"));
                $where .= " AND a.`birthday`>= '".$maxtime."' and a.`birthday`<='".$mintime."'";
            } elseif ($age[0] && !$age[1]) {
                $mintime=date("Y-m-d",strtotime("-".$age[0]." year"));
                $where.= " AND a.`birthday`<='".$mintime."'";
            } elseif (!$age[0] && $age[1]) {
                $maxtime=date("Y-m-d",strtotime("-".$age[1]." year"));
                $where.= " AND a.`birthday`>='".$maxtime."'";
            }
        }

        if ($_GET['remark'] == 1){
            
            $where .= " and ((a.content IS NOT NULL and a.content <> '') or a.label > 0)";
        }elseif ($_GET['remark'] == 2){
            
            $where .= " and (a.content IS NULL or a.content = '') and (a.label IS NULL or a.label = 0)";
        }

        include(PLUS_PATH . 'city.cache.php');
        include(PLUS_PATH . 'cityparent.cache.php');
        include(PLUS_PATH . 'job.cache.php');
        include(PLUS_PATH . 'jobparent.cache.php');
        $city_job_class = '';
        if ($_GET['job_class'] || $_GET['city_class']) {
            $city_col = $job_col = '';
            $cjwhere = '';
            if ($_GET['job_class']) {
                if ($job_parent[$_GET['job_class']] == '0') {
                    $job_col = "job1";
                    $cjwhere .= "$job_col = {$_GET['job_class']}";
                } elseif (in_array($job_parent[$_GET['job_class']], $job_index)) {
                    $job_col = "job1_son";
                    $cjwhere .= "$job_col = {$_GET['job_class']}";
                } elseif ($job_parent[$_GET['job_class']] > 0) {
                    $job_col = "job_post";
                    $cjwhere .= "$job_col = {$_GET['job_class']}";
                }
                $urlarr['job_class'] = $_GET['job_class'];
            }
            if ($_GET['city_class']) {
                $cjand = $cjwhere ? ' AND ' : '';
                if ($city_parent[$_GET['city_class']] == '0') {
                    $city_col = "provinceid";
                    $cjwhere .= "{$cjand}$city_col = {$_GET['city_class']}";
                } elseif (in_array($city_parent[$_GET['city_class']], $city_index)) {
                    $city_col = "cityid";
                    $cjwhere .= "{$cjand}$city_col = {$_GET['city_class']}";
                } elseif ($city_parent[$_GET['city_class']] > 0) {
                    $city_col = "three_cityid";
                    $cjwhere .= "{$cjand}$city_col = {$_GET['city_class']}";
                }
                $urlarr['city_class'] = $_GET['city_class'];
            }
            // 拼接唯一标识字段
            if ($city_col || $job_col) {
                if ($city_col && $job_col) {
                    $cjwhere .= " AND {$city_col}_{$job_col}_num = 1";
                } elseif ($city_col) {
                    $cjwhere .= " AND {$city_col}_num = 1";
                } elseif ($job_col) {
                    $cjwhere .= " AND {$job_col}_num = 1";
                }
            }
            $city_job_class = ",(select `eid` from `".$this->def."resume_city_job_class` where $cjwhere) cj";
            $where .= " and a.id = cj.eid";
        }

        $countSql = "select count(*) as num from `".$this->def."resume_expect` a{$city_job_class} where {$where}";

        //分页链接
		$urlarr        	=   $_GET;
        $urlarr['page'] = '{{page}}';
        $pageurl = Url($_GET['m'], $urlarr, 'admin');
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->pageList('resume_expect', $where, $pageurl, $_GET['page'], '', $countSql);

        $order = '';
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_GET['order']) {

                if ($_GET['t'] == 'time') {

                    $order .= "order by a.lastupdate ". $_GET['order'];
                } else {

                    $order .= 'order by a.' . $_GET['t'] . ' ' . $_GET['order'];
                }

                $urlarr['order'] = $_GET['order'];
                $urlarr['t'] = $_GET['t'];
            } else {
                $order .= 'order by a.lastupdate desc';
            }
            $sql        =   "select a.* from `".$this->def."resume_expect` a{$city_job_class} where {$where} {$order} limit {$pages['limit'][0]},{$pages['limit'][1]}";
            $List       =   $resumeM->getList(array(), array('cache' => 1, 'utype' => 'admin', 'sql' => $sql));
            $CacheList  =   $List['cache'];

            $setArr                 =   array(
                'rows'              =>  $List['list'],
                'userdata'          =>  $CacheList['userdata'],
                'userclass_name'    =>  $CacheList['userclass_name'],
                'job_name'          =>  $CacheList['job_name'],
                'city_name'         =>  $CacheList['city_name']
            );
            $this->yunset($setArr);
        }
        //高级搜索
        $this->set_search($CacheList);
		$appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $isOutcall = $appkey && $appsecret;
        $this->yunset('is_outcall', $isOutcall);// 是否配置外呼
        $this->yuntpl(array('admin/crm_chat'));
    }
    // 设置是否电话、微信联系
    function setState_action(){

        if ($_POST['type'] == 'crm_tel'){
            $eData['crm_tel']  =  $_POST['state'];
            $type = '电话';
        }elseif ($_POST['type'] == 'crm_wx'){
            $eData['crm_wx']  =  $_POST['state'];
            $type = '微信';
        }
        if (!empty($eData)){
            $resumeM  =  $this->MODEL('resume');
            $nid = $resumeM->upInfo(array('id'=>$_POST['id'],'uid'=>$_POST['uid']), array('eData'=>$eData));

            if ($nid){
                $res = array('errcode'=>9, 'msg'=>'客勤'.$type.'联系(ID: '.$_POST['uid'].')设置成功');
            }else{
                $res = array('errcode'=>8, 'msg'=>'设置失败');
            }
        }else{
            $res = array('errcode'=>8, 'msg'=>'请选择设置内容');
        }
        $this->layer_msg($res['msg'], $res['errcode']);
    }
    // 简历备注
    function label_action(){

        $id=(int)$_POST['id'];

        $post     =  array(
            'label'    => (int)$_POST['label'],
            'content'  => trim($_POST['content'])
        );

        $resumeM  =  $this -> MODEL('resume');

        $return   =  $resumeM -> label($id,$post);

        $this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
    }
    // 未登录用户获取聊天登录参数
    function getUnloginToken_action(){

        // 所有管理员只用一个账号
        $chatM   =  $this->MODEL('chat');
        $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));

        if (!empty($member)){
            $auid = $member['uid'];
        }else{
            $auid = $_SESSION['auid'];
        }

        $csM   =  $this->MODEL('chatcs');
        $data  =  $csM->getUnloginToken('admin', array('auid'=>$auid));

        echo json_encode($data);
    }
    // 聊天数据准备
    function enterRoom_action()
    {
        if ($_POST['uid']){
            // 所有管理员只用一个账号
            $chatM   =  $this->MODEL('chat');
            $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,DESC'));

            if (!empty($member)){
                $auid = $member['uid'];
            }else{
                $auid = $_SESSION['auid'];
            }
            $data   =  array(
                'toid'       =>  intval($_POST['uid']),
                'fromid'     =>  $auid,
                'tusertype'  =>  1,
                'fusertype'  =>  9
            );
            $chatM -> beginChat($data);
            // 增加对方记录
            $chatM -> userinfo(array('uid'=>$data['toid'],'usertype'=>$data['tusertype']));
			echo 1;die;
        }
    }
    // 聊天界面
    function room_action(){

        $toid = $_GET['id'];

        $resumeM    =   $this->MODEL('resume');
        $cacheM		=	$this -> MODEL('cache');
        $CacheList	=	$cacheM -> GetCache(array('com'));

        $setArr	=	array(
            'comdata'		=>	$CacheList['comdata'],
            'comclass_name' =>	$CacheList['comclass_name'],
            'adtime'        =>  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月')
        );
        $this -> yunset($setArr);

        // 所有管理员只用一个账号
        $chatM   =  $this->MODEL('chat');
        $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));

        if (!empty($member)){
            $auid = $member['uid'];
            // 清理错误客勤聊天记录
            //$this->clearChatErr($auid);

        }else{
            $auid = $_SESSION['auid'];
        }
        $auser = array(
            'uid' => $auid,
            'usertype' => 9,
            'nickname' => '求职助手',
            'linkman' => '官方客服'
        );
        // 处理聊天用户信息
        $chatM->checkMember($auser);

        //简历数据
        $expect = $resumeM->getExpect(array('uid'=>$toid,'defaults'=>1),array('field'=>'id,`uid`,`job_classid`,`city_classid`'));
        $return = $resumeM->getInfo(array('uid'=>$toid,'eid'=>$expect['id'],'tb'=>'all','needCache'=>1));
        $setarr = array(
            'toid'             =>  $toid,
            'expect'          =>   $return['expect'],
            'edu'             =>   $return['edu'],
            'other'           =>   $return['other'],
            'project'         =>   $return['project'],
            'skill'           =>   $return['skill'],
            'training'        =>   $return['training'],
            'work'            =>   $return['work'],
            'industry_index'  =>   $return['cache']['industry_index'],
            'industry_name'   =>   $return['cache']['industry_name'],
            'userdata'        =>   $return['cache']['userdata'],
            'userclass_name'  =>   $return['cache']['userclass_name'],
            'user_sex'        =>   $return['cache']['user_sex'],
        );
        if (!empty($expect['job_classid'])){
            $jobclassArr = explode(',', $expect['job_classid']);
            $job_classid = $jobclassArr[0];
        }else{
            $job_classid = '';
        }
        if (!empty($expect['city_classid'])){
            $cityclassArr = explode(',', $expect['city_classid']);
            $city_classid = $cityclassArr[0];
        }else{
            $city_classid = '';
        }
        $this->yunset('job_class',$job_classid);
        $this->yunset('city_class',$city_classid);
        $this->yunset($setarr);

        if(empty($return['cache']['city_type'])){
            $this   ->  yunset('cionly',1);
        }
        if(empty($return['cache']['job_type'])){
            $this   ->  yunset('jionly',1);
        }
        $where['uid']       =       $toid;

        $resumeinfo         =       $resumeM->getResumeInfo($where,array('logo'=>2));


        $this->yunset($setarr);
        $this->yunset('resumeinfo',$resumeinfo);
        //简历数据 end
        $this->yunset('auid', $auid);
        $this->yuntpl(array('admin/crm_chat_room'));
    }
    // 聊天界面初始数据
    function detail_action(){

        // 所有管理员只用一个账号
        $chatM   =  $this->MODEL('chat');
        $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));

        if (!empty($member)){
            $auid = $member['uid'];
        }else{
            $auid = $_SESSION['auid'];
        }

        // 左侧聊天对象记录
        $history  =  $chatM->getHistory($auid, 9, 0);

        if (!empty($_POST['toid'])){
            $toid  =  intval($_POST['toid']);
            // 收到的未读消息，设为已读
            $br  =  $chatM->getBeginid(array('fromid'=>$toid,'toid'=>$auid,'fusertype'=>1,'tusertype'=>9));
            if (!empty($br['beginid'])){
                $chatM->upChatLog(array('status'=>1),array('beginid'=>$br['beginid'],'to'=>$auid,'tusertype'=>9,'status'=>2));
            }
            $pArr  =  array(
                'uid'       =>  $auid,
                'usertype'  =>  9,
                'toid'      =>  $toid,
                'totype'    =>  1
            );
            // 简历数据
            $res['data']  =  $chatM->getPrepare($pArr);
            // 聊天内容
            $arr  =  array(
                'toid'       =>  $toid,
                'tusertype'  =>  1,
                'fromid'     =>  $auid,
                'fusertype'  =>  9,
                'page'       =>  1,
                'lastid'     =>  ''
            );
            $res['log']  =  $chatM -> getChatPage($arr);

            if(empty($res['log']['data'])){

                $resumeM   =  $this->MODEL('resume');
                
                $ureturn = $chatM -> userinfo(array('uid'=>$toid,'usertype'=>1));

                $expect = $resumeM->getExpect(array('uid'=>$toid,'defaults'=>1,'state'=>'1'),array('field'=>'`uid`,`name`'));

                $msg = array(
                    'id'        =>  $toid,
                    'mine'      =>  true,
                    'tusertype' =>  1,
                    'fusertype' =>  9,
                    'chating'   =>  3,
                    'username'  =>  $ureturn['mine']['username'],
                    'usertype'  =>  $ureturn['mine']['usertype'],
                    'linkman'   =>  $ureturn['mine']['linkman'],
                    'avatar'    =>  $ureturn['mine']['avatar'],
                    'expect'    =>  isset($expect['name']) ? $expect['name'] : ''
                );
                // 查询历史记录里有没有，防止增加重复数据
                if (!empty($history)){
                    $nodata = true;
                    foreach ($history as $v){
                        if ($v['id'] == $toid){
                            $nodata = false;
                            break;
                        }
                    }
                    if ($nodata){
                        array_unshift($history,$msg);
                    }
                }
            }
        }
        

        $res['list'] = $history;

        if(!empty($history)){
            $res['errcode'] = 0;
            echo json_encode($res);
        }else{
            echo json_encode(array('errcode'=>1));
        }
    }
    // 聊天对象列表
    function chatList_action(){

        // 所有管理员只用一个账号
        $chatM   =  $this->MODEL('chat');
        $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));

        if (!empty($member)){
            $auid = $member['uid'];
        }else{
            $auid = $_SESSION['auid'];
        }

        $page     =  intval($_POST['page']);

        $history  =  $chatM->getHistory($auid, 9, $page);

        if(!empty($history)){
            echo json_encode(array('errcode'=>0,'list'=>$history));
        }else{
            echo json_encode(array('errcode'=>1));
        }
    }
    // 聊天记录分页
    function getChatPage_action()
    {
        // 所有管理员只用一个账号
        $chatM   =  $this->MODEL('chat');
        $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));

        if (!empty($member)){
            $auid = $member['uid'];
        }else{
            $auid = $_SESSION['auid'];
        }

        $arr    =  array(
            'toid'       =>  $_POST['id'],
            'tusertype'  =>  1,
            'fromid'     =>  $auid,
            'fusertype'  =>  9,
            'page'       =>  $_POST['page'],
            'lastid'     =>  $_POST['lastid']
        );

        $return  =  $chatM -> getChatPage($arr);

        echo  json_encode($return);die;
    }
    //添加聊天发送记录
    function chatLog_action()
    {
        // 所有管理员只用一个账号
        $chatM   =  $this->MODEL('chat');
        $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));

        if (!empty($member)){
            $auid = $member['uid'];
        }else{
            $auid = $_SESSION['auid'];
        }

        $toid  =  intval($_POST['to']);
        $arr   =  array(
            'toid'       =>  $toid,
            'tusertype'  =>  $_POST['totype'],
            'fromid'     =>  $auid,
            'fusertype'  =>  9,
            'content'    =>  $_POST['content'],
            'timestamp'  =>  $_POST['timestamp'],
            'msgtype'    =>  $_POST['msgtype'],
            'nowid'      =>  $auid,
            'nowtype'    =>  9
        );

        $return  =  $chatM -> chatLog($arr);
        if ($return['error'] == 0 && !empty($_POST['eid'])){
            // 设置是否沟通状态
            if (empty($_POST['chated'])){
                $resumeM  =  $this->MODEL('resume');
                $expect   =  $resumeM->getExpect(array('id'=>$_POST['eid']), array('field'=>'crm_chat'));
                if (!empty($expect) && $expect['crm_chat'] == 1){
                    $resumeM->upInfo(array('id'=>$_POST['eid'],'uid'=>$toid), array('eData'=>array('crm_chat'=>2)));
                }
            }
        }
        echo json_encode($return);die;
    }
    // 发送图片信息-上传图片
    function uploadImage_action()
    {
        $UploadM  =  $this -> MODEL('upload');

        $upArr  =  array(
            'file' =>  $_FILES['file'],
            'dir'  =>  'chat'
        );
        $result  =  $UploadM->newUpload($upArr);

        if (!empty($result['msg'])){

            $return  =  array(
                'code'  =>  1,
                'msg'   =>  $result['msg'],
                'data'  =>  array()
            );
        }else{

            $pic  =  $result['picurl'];

            $result['picurl']  =  checkpic($pic);

            $return  =  array(
                'code'  =>  0,
                'msg'   =>  '',
                'data'  =>  array('url'=>$result['picurl'])
            );
        }
        echo json_encode($return);
    }
    // 职位列表
    function joblist_action(){
        $jobM  =  $this->MODEL('job');

        $search             =   false;
        $where['state']		=	1;
        $where['status']	=	0;
        $where['r_status']	=	1;

        if($_POST['keyword']){

            $keyword            =  trim($_POST['keyword']);
            $where['name']		=  array('like',$keyword);
            $where['com_name']  =  array('like',$keyword,'OR');

        }
        if($_POST['eid']){
            //查询简历信息
            $resumeM = $this->MODEL('resume');
            $expect = $resumeM->getExpect(array('id'=>$_POST['eid']),array('field'=>'`job_classid`,`city_classid`'));
            if ($expect){
                $where['PHPYUNBTWSTART_A'] = '';
                $where['provinceid']	  =	array('findin', $expect['city_classid']);
                $where['cityid']	      =	array('findin', $expect['city_classid'], 'OR');
                $where['three_cityid']	  =	array('findin', $expect['city_classid'], 'OR');
                $where['PHPYUNBTWEND_A']   = '';

                $where['PHPYUNBTWSTART_B'] = '';
                $where['job1']	  =	array('findin', $expect['job_classid']);
                $where['job1_son']	      =	array('findin', $expect['job_classid'], 'OR');
                $where['job_post']	  =	array('findin', $expect['job_classid'], 'OR');
                $where['PHPYUNBTWEND_B']   = '';
            }
        }
        if($_POST['city_class']){

            $where['PHPYUNBTWSTARTB'] = '';
            $where['provinceid']	  =	array('findin', $_POST['city_class']);
            $where['cityid']	      =	array('findin', $_POST['city_class'], 'OR');
            $where['three_cityid']	  =	array('findin', $_POST['city_class'], 'OR');
            $where['PHPYUNBTWENDB']   = '';
        }
        if($_POST['job_class']){

            $where['PHPYUNBTWSTARTA'] = '';
            $where['job1']	  =	array('findin', $_POST['job_class']);
            $where['job1_son']	      =	array('findin', $_POST['job_class'], 'OR');
            $where['job_post']	  =	array('findin', $_POST['job_class'], 'OR');
            $where['PHPYUNBTWENDA']   = '';
        }
        if($_POST['job_exp']){
            $where['exp'] = $_POST['job_exp'];
        }
        if($_POST['job_edu']){
            $where['edu'] = $_POST['job_exp'];
        }
        if($_POST['welfare']){
            $welfare = explode(',',$_POST['welfare']);
            if(!empty($welfare)){
                $where['PHPYUNBTWSTART']  =  '';
                foreach($welfare as $key=>$value){

                    $where['welfare'][] = array('like',trim($value),'AND');
                }
                $where['PHPYUNBTWEND']    =  '';
            }
        }
        if($_POST['adtime']){

            if($_POST['adtime']  ==  '1'){

                $where['sdate']  =  array('>',strtotime('today'));

            }else{

                $where['sdate']  =  array('>',strtotime('-'.intval($_POST['adtime']).' day'));
            }
        }
        $limit		    =  6;
        $jobnum         =  $jobM->getJobNum($where);
        $data['total']  =  ceil($jobnum/6);


        $page     =  $_POST['page'];
        $jfield   =  '`id`,`uid`,`name`,`provinceid`,`cityid`,`exp`,`edu`,`welfare`,`minsalary`,`maxsalary`,`lastupdate`,`com_name`,`sdate`,`rec`,`rec_time`,`urgent`,`urgent_time`,`xsdate`,`rating`,`zuid`';

        $where['orderby']	=	'lastupdate,desc';

        if($page){//分页
            $pagenav		=	($page-1)*$limit;
            $where['limit']	=	array($pagenav,$limit);
        }else{
            $where['limit']	=	$limit;
        }
        $jobrows    	=   $jobM->getList($where,array('field'=>$jfield,'utype'=>'wxapp'));
        $rows			=	$jobrows['list'];

        foreach($rows as $k=>$v){
            $rows[$k]['webjob_url'] = Url('job',array('c'=>'comapply','id'=>$v['id']));
            if (!empty($v['lastupdate']) && !empty($v['lastupdate_n'])){
                $beginToday  =  strtotime('today');//今天开始时间戳
                if($v['lastupdate']>$beginToday){
                    $rows[$k]['lastupdate_n']  =  lastupdateStyle($v['lastupdate']);
                }
            }
        }

        $list	=	count($rows) > 0 ? $rows : array();

        $data['list']		=	$list;

        echo json_encode($data);
    }

    // 客勤常用语
    function usefulData_action(){
        $chatM  =  $this->MODEL('chat');
        $where['type']		=	5;
        $where['orderby'] = 'sort,desc';
        $list = $chatM->getUsefulSetList($where, array('field' => 'content'));
        $data['list'] =	$list;
        echo json_encode($data);
    }

    /**
     * 清理错误客勤聊天记录
     */
    private function clearChatErr($auid){

        $chatM = $this->MODEL('chat');
        $ma    =  $chatM->getMemberList(array('usertype'=>9), array('field'=>'uid'));

        if (count($ma) > 1){
            // 所有管理员只用一个账号，清除其他账号
            $fq = array();
            foreach ($ma as $v){
                if ($v['uid'] != $auid && $v['uid'] > 0){
                    $fq[] = $v['uid'];
                }
            }
            if (!empty($fq)){
                // beiginid组合带字符串- ,修改需要action.class.php里checkWhere验证字段过滤增加- 来配合
                $fa  =  $chatM->getFriendList(array('uid'=>array('in', pylode(',', $fq)),'usertype'=>9));
                $ta  =  $chatM->getFriendList(array('fid'=>array('in', pylode(',', $fq)),'fusertype'=>9));

                $barr = array();
                foreach ($fa as $v){
                    $barr[] = $v['beginid'];

                    $beginid  =  $auid.'-'.$v['fid'].'-91';
                    $chatM->upFriend(array('beginid'=>$v['beginid'],'usertype'=>9), array('beginid'=>$beginid,'uid'=>$auid));
                }
                foreach ($ta as $v){
                    $barr[] = $v['beginid'];

                    $beginid  =  $v['fid'].'-'.$auid.'-91';
                    $chatM->upFriend(array('beginid'=>$v['beginid'],'fusertype'=>9), array('beginid'=>$beginid,'fid'=>$auid));
                }
                $barr = array_unique($barr);

                $fid = $tid = array();
                // 修改其他管理员聊天记录，到唯一管理员数据
                foreach ($barr as $v){

                    $logs = $chatM->getChatLogList(array('beginid'=>$v, 'fusertype'=>9),array('field'=>'`id`,`from`,`to`'));
                    if (!empty($logs)){
                        foreach ($logs as $val){
                            $beginid  =  $auid.'-'.$val['to'].'-91';
                            $chatM->upChatLog(array('from'=>$auid, 'beginid'=>$beginid),array('id'=>$val['id'],'fusertype'=>9));
                        }
                    }
                    $logs = $chatM->getChatLogList(array('beginid'=>$v, 'tusertype'=>9),array('field'=>'`id`,`from`,`to`'));
                    if (!empty($logs)){
                        foreach ($logs as $val){
                            $beginid  =  $val['to'].'-'.$auid.'-91';
                            $chatM->upChatLog(array('to'=>$auid, 'beginid'=>$beginid),array('id'=>$val['id'],'tusertype'=>9));
                        }
                    }
                }
                // 删除重复的聊天用户
                $chatM->delMember(array('uid'=>array('in',pylode(',', $fq)),'usertype'=>9));
            }
        }
    }
    // 没有简历用户列表
    function noresume_action(){

        include(CONFIG_PATH.'db.data.php');
        $source         =  $arr_data['source'];
        $search_list[]  =  array('param'=>'source','name'=>'数据来源','value'=>$source);
        $search_list[]  =  array('param'=>'lotime','name'=>'最近登录','value'=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
        $search_list[]  =  array('param'=>'adtime','name'=>'最近注册','value'=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
        $search_list[]  =  array('param'=>'status','name'=>'审核状态','value'=>array('1'=>'已审核','2'=>'已锁定','3'=>'未通过','4'=>'未审核'));
        $this->yunset('source',$source);
        $this->yunset('search_list',$search_list);

        $userInfoM  =   $this -> MODEL('userinfo');
        $resumeM    =   $this -> MODEL('resume');
        $where      =   array('def_job'=>0);
        $memberWhere=   array();
        //条件查询 ：搜索查询
        if($_GET['keyword']){

            $keytype  =   intval($_GET['keytype']);

            $keyword  =   trim($_GET['keyword']);

            if ($keytype == 1){

                $memberWhere['username']    =   array('like',$keyword);

            }elseif ($keytype == 2){

                $where['name']        =	 array('like',$keyword);

            }elseif ($keytype == 3){

                $where['telphone']    =  array('like',$keyword);

            }elseif ($keytype == 4){

                $where['email']       =  array('like',$keyword);

            }elseif ($keytype == 5){

                $where['uid'][]       =  array('=',$keyword);
            }

            $urlarr['keytype']	      =  $keytype;

            $urlarr['keyword']	      =  $keyword;
        }
        if($_GET['status']){

            $status                   =   intval($_GET['status']);
            $where['r_status']        =  $status == 4 ? 0 : $status;
            $urlarr['status']         =  $status;
        }
        if(isset($_GET['adtime'])){
            if($_GET['adtime']=='1'){

                $memberWhere['reg_date']    =   array('>',strtotime('today'));
            }else{

                $memberWhere['reg_date']    =   array('>',strtotime('-'.intval($_GET['adtime']).' day'));
            }

            $urlarr['adtime']               =   $_GET['adtime'];
        }
        if(isset($_GET['lotime'])){
            if($_GET['lotime']=='1'){

                $memberWhere['login_date']  =   array('>',strtotime('today'));
            }else{

                $memberWhere['login_date']  =   array('>',strtotime('-'.intval($_GET['lotime']).' day'));
            }
            $urlarr['lotime']               =   $_GET['lotime'];
        }
        if(isset($_GET['source'])){

            $source                         =   intval($_GET['source']);

            $memberWhere['source']          =   $source;

            $urlarr['source']               =   $source;
        }

        if(isset($memberWhere) && !empty($memberWhere)){

            $memberUids         =   array();

            $memberList		    =   $userInfoM->getList($memberWhere,array('field'=>'`uid`'));

            foreach($memberList  as $val){
                $memberUids[]   =   $val['uid'];
            }

            $where['uid']	    =   array('in',pylode(',',$memberUids));
        }
        $urlarr        	=   $_GET;
        $urlarr['page']	=	'{{page}}';

        $pageurl		=	Url($_GET['m'],$urlarr,'admin');
        $pageM			=	$this  -> MODEL('page');
        $pages			=   $pageM -> pageList('resume',$where,$pageurl,$_GET['page']);

        if($pages['total'] > 0){
            if(isset($_GET['order'])){
                if($_GET['t']=='time'){

                    $where['orderby']	=	'lastupdate,'.$_GET['order'];
                }else{

                    $where['orderby']	=	$_GET['t'].','.$_GET['order'];
                }
                $urlarr['order']		=	$_GET['order'];
                $urlarr['t']			=	$_GET['t'];
            }else{

                $where['orderby']		=	'uid';
            }
            $where['limit']				=	$pages['limit'];
            $List                       =   $resumeM -> getResumeList($where,array('utype'=>'admin'));

            $this -> yunset('userrows',$List);
        }

        $this->yuntpl(array('admin/crm_chat_noresume'));
    }
    // 没有简历用户备注
    function userlabel_action(){

        if (empty($_POST['content'])){

            $this->ACT_layer_msg('请填写备注内容',8);
        }

        $id = (int)$_POST['id'];

        $post     =  array(
            'label'  => trim($_POST['content'])
        );

        $resumeM  =  $this -> MODEL('resume');

        $return   =  $resumeM -> userlabel($id,$post);

        $this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
    }
    // 收到消息即设为已读
    function setMsgStatus_action()
    {
        $chatM   =  $this->MODEL('chat');
        $member  =  $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));

        if (!empty($member)){
            $auid = $member['uid'];
        }else{
            $auid = $_SESSION['auid'];
        }

        $arr     =  array(
            'toid'       =>  $auid,
            'fromid'     =>  $_POST['id'],
            'tusertype'  =>  9,
            'fusertype'  =>  $_POST['type'],
            'nowid'      =>  $auid,
            'nowtype'    =>  9
        );
        $return  =  $chatM->setMsg($arr);
        echo json_encode($return);die;
    }

    /**
     * @desc CRM-客勤列表:（虚拟号联系客户）
     */
    function contactCustomer_action()
    {
        $adminM = $this->MODEL('admin');
        $staffCallM = $this->MODEL('staffcalllog');
        $adminInfo = $adminM->getAdminUser(array('uid' => $_SESSION['auid']));
        if (!$adminInfo['moblie']) {
            echo json_encode(['code' => 400, 'msg' => '业务员未填写手机号']);exit;
        }
        if (!$_POST['uid']) {
            echo json_encode(['code' => 400, 'msg' => '用户uid不能为空']);exit;
        }
        $mobile = trim($_POST['mobile']);
        if (!$mobile || !CheckMobile($mobile)) {
            echo json_encode(['code' => 400, 'msg' => '手机号格式错误']);exit;
        }
//        $resumeM = $this->MODEL('resume');
//        $memberWhere['uid'] = $_POST['uid'];
//        $memberWhere['moblie'] = $mobile;
//        $memberInfo = $resumeM->getMemberInfo($memberWhere, array('field' => '`uid`,`moblie`'));
//        if (!$memberInfo) {
//            echo json_encode(['code' => 400, 'msg' => '获取个人信息失败']);exit;
//        }
        $apiurl = 'https://u.phpyun.com/call';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            echo json_encode(['code' => 400, 'msg' => '请先完成外呼配置！']);exit();
        }
        $params = array(
            'appKey' => $appkey,
            'appSecret' => $appsecret,
            'caller' => $adminInfo['moblie'],
            'callee' => $mobile
        );
        $res = CurlPost($apiurl, $params, 1);
//        file_put_contents(APP_PATH."new_call_log.txt",'客服外呼接口返回信息：('.date('Y-m-d H:i:s').')'.$res.";\n",FILE_APPEND);
        $result = json_decode($res, 1);
        $callLog = array(
            'uid' => $_SESSION['auid'],
            'username' => $adminInfo['name'],
            'session_id' => isset($result['data']['session_id']) ? $result['data']['session_id'] : '',
            'caller' => $params['caller'],
            'callee' => $params['callee'],
            'log_id' => isset($result['data']['log_id']) ? $result['data']['log_id'] : 0,// 综合平台外呼记录id
            'usertype' => 1,// 1:个人，2：企业，不传默认为企业
            'has_session' => isset($result['data']['has_session']) ? $result['data']['has_session'] : 0,// 是否有session_id
            'cuid' => $_POST['uid']
        );
        $callLog['api_res'] = json_encode(json_decode($res, 1), JSON_UNESCAPED_UNICODE);
        if ($result['code'] == 200) {// 呼叫成功
            $callLog['status'] = 1;
        } else {
            $callLog['status'] = 0;
        }
        $id = $staffCallM->addData($callLog);
        if ($result['code'] == 200) {
            echo json_encode(['code' => 200, 'msg' => '呼叫成功', 'data' => $id ? $id : 0]);exit();
        } else {
            echo json_encode(['code' => 400, 'msg' => $result['message'], 'data' => $id ? $id : 0]);exit();
        }
    }
    
    function phoneAuthNum_action(){
        $crmphoneauthM  =   $this -> MODEL('crmphoneauth');
        echo $crmphoneauthM->authNum();
    }
    function phoneAuthList_action(){
        include(CONFIG_PATH.'db.data.php');
        $source         =  $arr_data['source'];
        foreach ($source as $sk => $sv) {
            if(!in_array($sk,array(13,19,22))){
                unset($source[$sk]);
            }
        }
        $search_list[]  =  array('param'=>'source','name'=>'数据来源','value'=>$source);
        $search_list[]  =  array('param'=>'lotime','name'=>'最近授权','value'=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
        $search_list[]  =  array('param'=>'isuid','name'=>'是否注册','value'=>array('1'=>'已注册','2'=>'未注册'));
        $search_list[]  =  array('param'=>'isresume','name'=>'拥有简历','value'=>array('1'=>'已创建','2'=>'未创建'));
        $this->yunset('source',$source);
        $this->yunset('search_list',$search_list);

        $crmphoneauthM  =   $this -> MODEL('crmphoneauth');
        
        $userInfoM  =   $this -> MODEL('userinfo');

        $where      =   '1';
        $memberWhere=   array();
        //条件查询 ：搜索查询
        if($_GET['keyword']){

            $keytype  =   intval($_GET['keytype']);

            $keyword  =   trim($_GET['keyword']);

            if ($keytype == 1){

                $where .= " and a.phone like '%$keyword%'";

            }elseif ($keytype == 2){

                $memberUids         =   array();

                $memberList         =   $userInfoM->getUserInfoList(array('name'=>array('like',$keyword)),array('usertype'=>1));

                foreach($memberList  as $val){
                    $memberUids[]   =   $val['uid'];
                }

                $where .= " AND a.usertype=1 AND a.uid in (".pylode(',', $memberUids).")";

            }elseif ($keytype == 3){

                $memberUids         =   array();

                $memberList         =   $userInfoM->getUserInfoList(array('name'=>array('like',$keyword)),array('usertype'=>2));

                foreach($memberList  as $val){
                    $memberUids[]   =   $val['uid'];
                }

                $where .= " AND a.usertype=2 AND a.uid in (".pylode(',', $memberUids).")";

            }elseif ($keytype == 5){

                $where .= " and a.uid =".$keyword;
            }

            $urlarr['keytype']        =  $keytype;

            $urlarr['keyword']        =  $keyword;
        }
        if(($_GET['date']) && $_GET['time']<1){
            
            $times                  =   @explode('~',$_GET['date']);
            
            $where .= " and a.atime >= ".strtotime($times[0]." 00:00:00") ." and a.atime <= ".strtotime($times[1]." 23:59:59");

            $urlarr['date']         =   $_GET['date'];
        }
        if(isset($_GET['lotime'])){
            if($_GET['lotime']=='1'){

                $where .= " and a.atime >".strtotime('today');
            }else{

                $where .= " and a.atime >".strtotime('-'.intval($_GET['lotime']).' day');
            }
            $urlarr['lotime']               =   $_GET['lotime'];
        }
        if(isset($_GET['source'])){

            $source                         =   intval($_GET['source']);
            $where .= " and a.source =".$source;
            $urlarr['source']               =   $source;
            
        }

        if($_GET['isuid']){
            if($_GET['isuid']==1){
                $where .= " and a.uid > 0";
            }else if($_GET['isuid']==2){
                $where .= " and a.uid = 0";
            }
            
            $urlarr['isuid']                =   $_GET['isuid'];
        }
        if($_GET['isresume']){

            

            if($_GET['isresume']==1){

                $where .= " AND (EXISTS (SELECT `id`,`uid` FROM `".$this->def."resume_expect` b WHERE b.uid=a.uid))";

                

            }else if($_GET['isresume']==2){

                $where .= " AND (NOT EXISTS (SELECT `id`,`uid` FROM `".$this->def."resume_expect` b WHERE b.uid=a.uid))";

            }

            $where .= " AND a.uid>0 AND a.usertype=1";

        }
        
        $countSql = "select count(*) as num from `".$this->def."crm_phoneauth` a where {$where}";

        $sql = "select a.* from `".$this->def."crm_phoneauth` a where {$where}";
        
        $urlarr         =   $_GET;
        $urlarr['page'] =   '{{page}}';

        $pageurl        =   Url($_GET['m'],$urlarr,'admin');
        $pageM          =   $this  -> MODEL('page');
        $pages          =   $pageM -> pageList('crm_phoneauth',$where,$pageurl,$_GET['page'],'',$countSql);

        $order  =   '';
        if($pages['total'] > 0){
            if(isset($_GET['order'])){
                if($_GET['t']=='time'){
                    $order .= " order by a.atime ". $_GET['order'];
                }else{
                    $order .= ' order by a.' . $_GET['t'] . ' ' . $_GET['order'];
                }
                $urlarr['order']        =   $_GET['order'];
                $urlarr['t']            =   $_GET['t'];
            }else{
                $order .= " order by a.atime desc";
            }

            $limit      =    "limit {$pages['limit'][0]},{$pages['limit'][1]}";

            $sql        .=   " {$order} {$limit}";

            $List                       =   $crmphoneauthM -> getList(array(),array('utype'=>'admin','sql'=>$sql));

            $this -> yunset('userrows',$List);
        }

        $this->yuntpl(array('admin/crm_phoneauthlist'));
    }
    function phoneauthRemark_action(){

        if (empty($_POST['content'])){

            $this->ACT_layer_msg('请填写备注内容',8);
        }

        $id = (int)$_POST['id'];

        $post     =  array(
            'remark'  => trim($_POST['content'])
        );

        $crmphoneauthM  =  $this -> MODEL('crmphoneauth');

        $return   =  $crmphoneauthM -> upPhoneAuth(array('id'=>$id),$post);

        $this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
    }
    function delPhoneAuth_action(){

        $this->check_token();

        $crmphoneauthM  =   $this->MODEL('crmphoneauth');

        if (is_array($_GET['del'])) {

            $id = $_GET['del'];
        } else {

            $id = intval($_GET['del']);
        }
        $arr = $crmphoneauthM->delPhoneAuth($id, array('utype' => 'admin'));
        $this->layer_msg($arr['msg'], $arr['errcode'], $arr['layertype'], $_SERVER['HTTP_REFERER']);
    }
    function authContactCustomer_action()
    {
        $adminM = $this->MODEL('admin');
        $staffCallM = $this->MODEL('staffcalllog');
        $adminInfo = $adminM->getAdminUser(array('uid' => $_SESSION['auid']));
        if (!$adminInfo['moblie']) {
            echo json_encode(['code' => 400, 'msg' => '业务员未填写手机号']);exit;
        }
        
        $mobile = trim($_POST['mobile']);
        if (!$mobile || !CheckMobile($mobile)) {
            echo json_encode(['code' => 400, 'msg' => '手机号格式错误']);exit;
        }

        $apiurl = 'https://u.phpyun.com/call';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            echo json_encode(['code' => 400, 'msg' => '请先完成外呼配置！']);exit();
        }
        $params = array(
            'appKey' => $appkey,
            'appSecret' => $appsecret,
            'caller' => $adminInfo['moblie'],
            'callee' => $mobile
        );
        $res = CurlPost($apiurl, $params, 1);

        $result = json_decode($res, 1);
        $callLog = array(
            'uid' => $_SESSION['auid'],
            'username' => $adminInfo['name'],
            'session_id' => isset($result['data']['session_id']) ? $result['data']['session_id'] : '',
            'caller' => $params['caller'],
            'callee' => $params['callee'],
            'log_id' => isset($result['data']['log_id']) ? $result['data']['log_id'] : 0,// 综合平台外呼记录id
            'usertype' => $_POST['usertype'],
            'has_session' => isset($result['data']['has_session']) ? $result['data']['has_session'] : 0,// 是否有session_id
            'cuid' => $_POST['uid']
        );
        $callLog['api_res'] = json_encode(json_decode($res, 1), JSON_UNESCAPED_UNICODE);
        if ($result['code'] == 200) {// 呼叫成功
            $callLog['status'] = 1;
        } else {
            $callLog['status'] = 0;
        }
        $id = $staffCallM->addData($callLog);
        if ($result['code'] == 200) {
            echo json_encode(['code' => 200, 'msg' => '呼叫成功', 'data' => $id ? $id : 0]);exit();
        } else {
            echo json_encode(['code' => 400, 'msg' => $result['message'], 'data' => $id ? $id : 0]);exit();
        }
    }
}
?>