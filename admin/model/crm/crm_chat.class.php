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


    /**
     * 业务员 客勤 拥有简历
     */
    function index_action()
    {

        $resumeM    =   $this->MODEL('resume');

        $where      =   'a.`defaults` = 1 ';

        include(CONFIG_PATH . 'db.data.php');

        //搜索类型和搜索关键字
        if ($_POST['keyword']) {

            $keytype = intval($_POST['keytype']);
            $keyword = trim($_POST['keyword']);

            if ($keytype == 1) {

                $where .= " and a.uname like '%$keyword%'";
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
            } elseif ($keytype == 11) {
                $where .= " and a.add_ip like '%$keyword%'";
            }
        }
        //来源
        if ($_POST['status']) {

            $status = intval($_POST['status']);

            if ($status == 2) {

                $where .= " and a.r_status = 2";
            } else {

                $where .= " and a.state = " . ($status == 4 ? 0 : $status);
            }
        }
        //来源
        if ($_POST['source']) {

            $where .= " and a.source = ".intval($_POST['source']);
        }
        //发布时间
        if ($_POST['adtime']) {

            $adtime = intval($_POST['adtime']);

            if ($adtime == 1) {

                $where .= " and a.ctime > ".strtotime('today');
            } else {

                $where .= " and a.ctime > ".strtotime('-' . $adtime . ' day');
            }
        }
        //更新时间
        if ($_POST['uptime']) {

            $uptime = intval($_POST['uptime']);

            if ($uptime == 1) {

                $where .= " and a.lastupdate > ".strtotime('today');
            } else {

                $where .= " and a.lastupdate > ".strtotime('-' . $uptime . ' day');
            }
        }
        //工作性质
        if ($_POST['type']) {

            $where .= " and a.type = ".intval($_POST['type']);
        }
        //学历要求
        if ($_POST['edu']) {

            $where .= " AND a.`edu` =".intval($_POST['edu']);
        }
        //工作经验
        if ($_POST['exp']) {

            $where .= " AND a.`exp` =".intval($_POST['exp']);
        }
        //到岗时间
        if ($_POST['report']) {

            $where .= " and a.report = ".intval($_POST['report']);
        }
        //简历完整度
        if ($_POST['integrity']) {
            if ($_POST['integrity'] == 55) {

                $where .= " and a.integrity = 55";
            } else if ($_POST['integrity'] == 65) {

                $where .= " and a.integrity = 65";
            } else {

                $integrity_val = $arr_data['integrity_val'];
                $where .= " and a.integrity >= ".$integrity_val[$_POST['integrity']];
            }
        }

        //薪资待遇
        if (!empty($_POST['salary'])) {
            $salary = explode('_', $_POST['salary']);
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
        if (!empty($_POST['age'])) {
            $age = explode('_', $_POST['age']);
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

        if ($_POST['remark'] == 1){

            $where .= " and ((a.content IS NOT NULL and a.content <> '') or a.label > 0)";
        }elseif ($_POST['remark'] == 2){

            $where .= " and (a.content IS NULL or a.content = '') and (a.label IS NULL or a.label = 0)";
        }

        include(PLUS_PATH . 'city.cache.php');
        include(PLUS_PATH . 'cityparent.cache.php');
        include(PLUS_PATH . 'job.cache.php');
        include(PLUS_PATH . 'jobparent.cache.php');
        $city_job_class = '';
        if ($_POST['job_class'] || $_POST['city_class']) {
            $city_col = $job_col = '';
            $cjwhere = '';
            if ($_POST['job_class']) {
                if ($job_parent[$_POST['job_class']] == '0') {
                    $job_col = "job1";
                    $cjwhere .= "$job_col = {$_POST['job_class']}";
                } elseif (in_array($job_parent[$_POST['job_class']], $job_index)) {
                    $job_col = "job1_son";
                    $cjwhere .= "$job_col = {$_POST['job_class']}";
                } elseif ($job_parent[$_POST['job_class']] > 0) {
                    $job_col = "job_post";
                    $cjwhere .= "$job_col = {$_POST['job_class']}";
                }
            }
            if ($_POST['city_class']) {
                $cjand = $cjwhere ? ' AND ' : '';
                if ($city_parent[$_POST['city_class']] == '0') {
                    $city_col = "provinceid";
                    $cjwhere .= "{$cjand}$city_col = {$_POST['city_class']}";
                } elseif (in_array($city_parent[$_POST['city_class']], $city_index)) {
                    $city_col = "cityid";
                    $cjwhere .= "{$cjand}$city_col = {$_POST['city_class']}";
                } elseif ($city_parent[$_POST['city_class']] > 0) {
                    $city_col = "three_cityid";
                    $cjwhere .= "{$cjand}$city_col = {$_POST['city_class']}";
                }
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

        //提取分页
        $pageM = $this->MODEL('page');
        $perPage = !empty($_POST['limit']) ? intval($_POST['limit']) : intval($this->config['sy_listnum']);
        $pages          =   $pageM->adminPageList('resume_expect', $where,$_POST['page'],array('limit'=>$perPage,'sql'=>$countSql));
        $order = '';
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {

                if ($_POST['t'] == 'time') {

                    $order .= "order by a.lastupdate ". $_POST['order'];
                } else {

                    $order .= 'order by a.' . $_POST['t'] . ' ' . $_POST['order'];
                }

            } else {
                $order .= 'order by a.id desc';
            }
            $sql        =   "select a.* from `".$this->def."resume_expect` a{$city_job_class} where {$where} {$order} limit {$pages['limit'][0]},{$pages['limit'][1]}";
            $List       =   $resumeM->getList(array(), array('cache' => 1, 'utype' => 'admin', 'sql' => $sql, 'work' => 1, 'chat_num' => 1));

            $row = $List['list'];

            foreach ($row as $k => $v) {
                $row[$k]['city_n']  = !empty($v['city_n'])?$v['city_n']:'';
                $row[$k]['citynum'] = $v['citynum']>0?$v['citynum']:0;
                $row[$k]['cityall'] = !empty($v['cityall'])?$v['cityall']:'';
                $row[$k]['lastupdate_t'] = date('Y-m-d H:i',$v['lastupdate']);
                $row[$k]['source_n'] = $arr_data['source'][$v['source']];

                $row[$k]['integrity'] = intval($v['integrity']);

                $row[$k]['sq_num'] = $v['sq_num']>0?$v['sq_num']:0;
                $row[$k]['chat_num'] = $v['chat_num']>0?$v['chat_num']:0;

                if(mb_strlen($v['content'])>12){
                    $row[$k]['content']  = mb_substr($v['content'],0,12).'...';
                }
                if(!empty($v['wxid'])){
//                    if(!empty($v['unionid'])){
//                        $wxGzhmsg = '公众号已绑定，微信开放平台已绑定';
//                    }else{
                        $wxGzhmsg = '公众号已绑定';
//                    }
                }else{
                    $wxGzhmsg= '公众号未绑定';
                }
                $row[$k]['wxBindmsg']       =             $wxGzhmsg;
            }
        }



        $data['list']           =   !empty($row)?$row:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $perPage;

        $this->render_json(0,'',$data);
    }
    /*
     * 业务员 客勤 拥有简历 顶部搜索条件
     */
    function getIndexStaticData_action(){

        $cacheM     =   $this->MODEL('cache');
        $CacheList  =   $cacheM -> GetCache(array('user'));
        $userdata       =   $CacheList['userdata'];
        $userclass_name =   $CacheList['userclass_name'];
        include(CONFIG_PATH . 'db.data.php');

        //高级搜索
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $isOutcall = $appkey && $appsecret;

        $type_arr = $edu_arr = $exp_arr = $report_arr = array();

        foreach($userdata['user_type'] as $tk=>$tv){

            $type_arr[]   =   array('val'=>$tv,'name'=>$userclass_name[$tv]);
        }
        foreach($userdata['user_edu'] as $ek=>$ev){

            $edu_arr[]   =   array('val'=>$ev,'name'=>$userclass_name[$ev]);
        }

        foreach($userdata['user_word'] as $wk=>$wv){

            $exp_arr[]   =   array('val'=>$wv,'name'=>$userclass_name[$wv]);
        }

        foreach($userdata['user_report'] as $rk=>$rv){

            $report_arr[]   =   array('val'=>$rv,'name'=>$userclass_name[$rv]);
        }
        foreach($userdata['user_label'] as $lk=>$lv){

            $label_arr[]   =   array('val'=>$lv,'name'=>$userclass_name[$lv]);
        }


        $data['source']         =   $arr_data['source'];
        $data['integrity_arr']  =   $arr_data['integrity_name'];
        $data['sy_chat_open']   =   $this->config['sy_chat_open'];
        $data['isOutcall']      =   $isOutcall;
        $data['type_arr']       =   $type_arr;
        $data['edu_arr']        =   $edu_arr;
        $data['exp_arr']        =   $exp_arr;
        $data['report_arr']     =   $report_arr;
        $data['label_arr']      =   $label_arr;

        $this->render_json(0, 'ok', $data);
    }
    /**
     * 跟进/备注 简历
     */
    function label_action()
    {
        $resumeM    =   $this->MODEL('resume');
        $data       =   array(

            'id'        =>  $_POST['id'],
            'uid'       =>  $_POST['uid'],
            'label'     =>  $_POST['label'],
            'content'   =>  $_POST['content'],
            'auid'      =>  $_SESSION['auid'],
            'f_time'    =>  $_POST['f_time']
        );

        $return     =   $resumeM->label($data);

        $error = $return['errcode']==9?0:1;

        $this->render_json($error,$return['msg']);
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
			$error = 0;
        }else{
            $error = 1;
        }
        $this->render_json($error);

    }
    // 聊天界面
    function room_action(){

        // 所有管理员只用一个账号
        $chatM   =  $this->MODEL('chat');
        $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));

        if (!empty($member)){
            $auid = $member['uid'];
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

        $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));

        if (!empty($member)){
            $auid = $member['uid'];
        }else{
            $auid = $_SESSION['auid'];
        }

        $csM    =  $this->MODEL('chatcs');
        $data   =  $csM->getUnloginToken('admin', array('auid'=>$auid));

        //常用语
        $usefulwhere['type']      =   5;
        $usefulwhere['orderby'] = 'sort,desc';
        $list = $chatM->getUsefulSetList($usefulwhere, array('field' => 'content'));
        $data['usefullist'] = $list;

        $this->render_json(0,'',$data);
    }
    /*
     * 聊天简历数据
     */
    function resumedata_action(){

        $toid = $_POST['toid'];

        $resumeM = $this->MODEL('resume');

        //简历数据
        $expect = $resumeM->getExpect(array('uid'=>$toid,'defaults'=>1),array('field'=>'id,`uid`,`job_classid`,`city_classid`'));

        $info = $resumeM->getInfoByEid(array('eid' => $expect['id']));

        $return = $resumeM->getInfo(array('uid' => $toid, 'eid' => $expect['id'], 'tb' => 'all', 'needCache' => 1));
        $expectData = array(
            'expect' => $return['expect'],
            'edu' => $return['edu'],
            'other' => $return['other'],
            'project' => $return['project'],
            'skill' => $return['skill'],
            'training' => $return['training'],
            'work' => $return['work'],
            'salary' => $resumeM->salary()
        );

        $where['uid'] = $info['uid'];

        $resume = $resumeM->getResumeInfo($where, array('logo' => 2));
        //简历数据end

        $user_sex = $return['cache']['user_sex'];
        $userdata = $return['cache']['userdata'];
        $userclass_name = $return['cache']['userclass_name'];
        $industry_index = $return['cache']['industry_index'];
        $industry_name = $return['cache']['industry_name'];

        $this->render_json(0, 'ok', compact('info', 'resume', 'expectData', 'user_sex', 'userdata', 'userclass_name', 'industry_index', 'industry_name'));
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
            $error = 0;

        }else{
            $error = 1;
        }

        $this->render_json($error,'',$res);
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

        $data = array();
        if(!empty($history)){
            $error = 0;
            $data['list'] = $history;
        }else{
            $error = 1;
        }
        $this->render_json($error,'',$data);
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

        $this->render_json(0,'',$return);
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
        $this->render_json(0,'',$return);
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
        $this->render_json($return['code'],$return['msg'],$return['data']);
    }
    function getJoblistStaticData_action(){

        $cacheM     =  $this->MODEL('cache');
        $cache      =  $cacheM->GetCache(array('com','job','city'));

        $comclass_name  =   $cache['comclass_name'];

        foreach ($cache['comdata']['job_exp'] as $v) {

            $job_exp_arr[]  =   array('val' => $v, 'name' => $comclass_name[$v]);
        }
        foreach ($cache['comdata']['job_edu'] as $v) {

            $job_edu_arr[] =   array('val' => $v, 'name' => $comclass_name[$v]);
        }

        foreach ($cache['comdata']['job_welfare'] as $v) {

            $job_welfare_arr[] =   array('val' => $v, 'name' => $comclass_name[$v]);
        }

        if($_POST['eid']){
            //查询简历信息
            $resumeM = $this->MODEL('resume');
            $expect = $resumeM->getExpect(array('id'=>$_POST['eid']),array('field'=>'`job_classid`,`city_classid`'));

            if (!empty($expect['job_classid'])){

                $job_classid = @explode(',',$expect['job_classid']);

                if(is_array($job_classid)){

                    foreach($job_classid as $v){

                        if($cache['job_name'][$v]){

                            $job_classname[$v]  =  $cache['job_name'][$v];

                        }
                    }
                    $jobnameArr     =  $job_classname;
                }
            }
            if (!empty($expect['city_classid'])){

                $city_classid = @explode(',',$expect['city_classid']);

                if(is_array($city_classid)){

                    foreach($city_classid as $v){

                        if($cache['city_name'][$v]){

                            $city_classname[$v]  =  $cache['city_name'][$v];

                        }
                    }

                    $citynameArr     =  $city_classname;
                }
            }

        }

        $data['jobnameArr'] =   $jobnameArr;
        $data['citynameArr']=   $citynameArr;
        $data['job_exp_arr'] =   $job_exp_arr;
        $data['job_edu_arr'] =   $job_edu_arr;
        $data['job_welfare_arr'] =   $job_welfare_arr;

        $this->render_json(0, 'ok', $data);
    }
    // 职位列表
    function joblist_action(){
        $data       =  array();
        $jobM       =  $this->MODEL('job');

        $search             =   false;
        $where['state']		=	1;
        $where['status']	=	0;
        $where['r_status']	=	1;

        $jobnameArr = array();
        $citynameArr = array();

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

        $jfield   =  '`id`,`uid`,`name`,`provinceid`,`cityid`,`exp`,`edu`,`welfare`,`minsalary`,`maxsalary`,`lastupdate`,`com_name`,`sdate`,`rec`,`rec_time`,`urgent`,`urgent_time`,`xsdate`,`rating`,`zuid`';



        $pageM          =   $this  -> MODEL('page');
        $pages          =   $pageM -> adminPageList('company_job',$where,$_POST['page'],array('limit'=>$_POST['limit']));

        if($pages['total'] > 0){

            $where['orderby']   =   'lastupdate,desc';
            $where['limit']     =   $pages['limit'];

            $jobrows        =   $jobM->getList($where,array('field'=>$jfield,'utype'=>'wxapp'));
            $rows           =   $jobrows['list'];

            foreach($rows as $k=>$v){
                $rows[$k]['webjob_url'] = Url('job',array('c'=>'comapply','id'=>$v['id']));
                if (!empty($v['lastupdate']) && !empty($v['lastupdate_n'])){
                    $beginToday  =  strtotime('today');//今天开始时间戳
                    if($v['lastupdate']>$beginToday){
                        $rows[$k]['lastupdate_n']  =  lastupdateStyle($v['lastupdate']);
                    }
                }
            }
        }

        $data['list']		=	count($rows) > 0 ? $rows : array();
        $data['total']      =   $pages['total'];
        $data['page_sizes'] =   $pages['page_sizes'];
        $data['page_size']  =   $pages['page_size'];
        $this->render_json(0,'',$data);
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
    function getNrStaticData_action(){

        include(CONFIG_PATH.'db.data.php');
        $data['source_arr'] =  $arr_data['source'];

        $this->render_json(0,'',$data);
    }
    // 没有简历用户列表
    function noresume_action(){

        $userInfoM  =   $this -> MODEL('userinfo');
        $resumeM    =   $this -> MODEL('resume');
        $where      =   array('def_job'=>0);
        $memberWhere=   array();
        //条件查询 ：搜索查询
        if($_POST['keyword']){

            $keytype  =   intval($_POST['keytype']);
            $keyword  =   trim($_POST['keyword']);

            if ($keytype == 1){

                $memberWhere['username']    =   array('like',$keyword);
            }elseif ($keytype == 2){

                $where['name']        =	 array('like',$keyword);
            }elseif ($keytype == 3){

                $where['telphone']    =  array('like',$keyword);
            }elseif ($keytype == 4){

                $where['uid'][]       =  array('=',$keyword);
            }elseif ($keytype == 5){

                $memberWhere['login_ip']    =   array('like',$keyword);
            }

        }
        if($_POST['status']){

            $status                   =   intval($_POST['status']);
            $where['r_status']        =  $status == 4 ? 0 : $status;
        }
        if(!empty($_POST['adtime'])){
            if($_POST['adtime']=='1'){

                $memberWhere['reg_date']    =   array('>',strtotime('today'));
            }else{

                $memberWhere['reg_date']    =   array('>',strtotime('-'.intval($_POST['adtime']).' day'));
            }

        }
        if(!empty($_POST['lotime'])){
            if($_POST['lotime']=='1'){

                $memberWhere['login_date']  =   array('>',strtotime('today'));
            }else{

                $memberWhere['login_date']  =   array('>',strtotime('-'.intval($_POST['lotime']).' day'));
            }
        }
        if(!empty($_POST['source'])){

            $source                         =   intval($_POST['source']);
            $memberWhere['source']          =   $source;
        }

        if(!empty($memberWhere)){

            $memberUids         =   array();
            $memberList		    =   $userInfoM->getList($memberWhere,array('field'=>'`uid`'));
            foreach($memberList  as $val){

                $memberUids[]   =   $val['uid'];
            }
            $where['uid']	    =   array('in',pylode(',',$memberUids));
        }

        $pageM			=	$this  -> MODEL('page');
        $pages          =   $pageM->adminPageList('resume', $where,$_POST['page'],array('limit'=>$_POST['limit']));
        if($pages['total'] > 0){
            if(!empty($_POST['order'])){
                if($_POST['t']=='time'){

                    $where['orderby']	=	'lastupdate,'.$_POST['order'];
                }else{

                    $where['orderby']	=	$_POST['t'].','.$_POST['order'];
                }
             }else{

                $where['orderby']		=	'uid';
            }
            $where['limit']				=	$pages['limit'];
            $List                       =   $resumeM -> getResumeList($where,array('utype'=>'admin'));

        }

        $data['list']           =   !empty($List)?$List:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $pages['page_size'];
        $this->render_json(0,'',$data);
    }

    // 没有简历用户备注
    function userlabel_action()
    {

        if (empty($_POST['content'])) {

            $this->render_json(1,'请填写备注内容');
        }

        $resumeM = $this->MODEL('resume');

        $data   =   array(
            'uid'   =>  $_POST['id'],
            'label' =>  $_POST['content'],
            'auid'  =>  $_SESSION['auid']
        );
        $return = $resumeM->userlabel($data);

        $error = $return['errcode']==9?0:1;

        $this->render_json($error,$return['msg']);

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
        $this->render_json(0,'',$return);
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
            $this->render_json(400,'业务员未填写手机号');
        }
        if (!$_POST['uid']) {
            $this->render_json(400,'用户uid不能为空');
        }
        $mobile = trim($_POST['mobile']);
        if (!$mobile || !CheckMobile($mobile)) {
            $this->render_json(400,'手机号格式错误');
        }

        $apiurl = 'https://u.phpyun.com/call';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            $this->render_json(400,'请先完成外呼配置！');
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

        $data = $id ? $id : 0;

        if ($result['code'] == 200) {

            $this->render_json(200,'呼叫成功',$data);
        } else {
            $this->render_json(400,$result['message'],$data);
        }
    }

    function getPaStaticData_action(){

        include(CONFIG_PATH.'db.data.php');
        $source_arr =   $arr_data['source'];
        foreach ($source_arr as $sk => $sv) {
            if (!in_array($sk, array(13, 19, 22))) {
                unset($source_arr[$sk]);
            }
        }
        $crmphoneauthM  =   $this->MODEL('crmphoneauth');

        $data['numarr']         =   $crmphoneauthM->authNum();
        $data['source']         =   !empty($source_arr)?$source_arr:array();
        $this->render_json(0, '', $data);
    }
    function phoneAuthList_action()
    {

        $crmphoneauthM  =   $this->MODEL('crmphoneauth');
        $userInfoM      =   $this->MODEL('userinfo');

        $where      =   '1';

        $keywordStr =   trim($_POST['keyword']);
        $typStr     =   intval($_POST['keytype']);

        if (!empty($keywordStr)) {

            if ($typStr == 1) {

                $where .= " and a.phone like '%$keywordStr%'";
            } elseif ($typStr == 2) {

                $memberUids = array();
                $memberList = $userInfoM->getUserInfoList(array('name' => array('like', $keywordStr)), array('usertype' => 1));
                foreach ($memberList as $val) {
                    $memberUids[] = $val['uid'];
                }

                $where .= " AND a.uid in (" . pylode(',', $memberUids) . ")";
            } elseif ($typStr == 3) {

                $memberUids = array();
                $memberList = $userInfoM->getUserInfoList(array('name' => array('like', $keywordStr)), array('usertype' => 2));
                foreach ($memberList as $val) {
                    $memberUids[] = $val['uid'];
                }
                $where .= " AND a.uid in (" . pylode(',', $memberUids) . ")";
            } elseif ($typStr == 4) {

                $where .= " and a.uid =" . $keywordStr;
            }
        }

        if (isset($_POST['sqtime']) && !empty($_POST['sqtime'])) {

            $start  =   date('Y-m-d', $_POST['sqtime'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['sqtime'][1] / 1000);
            $where  .=  " and a.atime >= ".strtotime($start." 00:00:00")." and a.atime <= ".strtotime($end." 23:59:59");
        }

        if (!empty($_POST['source'])) {

            $source =   intval($_POST['source']);
            $where .=   " and a.source =" . $source;
        }

        if ($_POST['isuid']) {
            if ($_POST['isuid'] == 1) {

                $where .= " and a.uid > 0";
            } else if ($_POST['isuid'] == 2) {

                $where .= " and a.uid = 0";
            }
        }
        if ($_POST['isresume']) {
            if ($_POST['isresume'] == 1) {

                $where .= " AND (EXISTS (SELECT `id`,`uid` FROM `" . $this->def . "resume_expect` b WHERE b.uid=a.uid))";
            } else if ($_POST['isresume'] == 2) {

                $where .= " AND (NOT EXISTS (SELECT `id`,`uid` FROM `" . $this->def . "resume_expect` b WHERE b.uid=a.uid))";
            }
            $where .= " AND a.uid>0 AND a.usertype=1";
        }

        $countSql   =   "select count(*) as num from `".$this->def."crm_phoneauth` a where {$where}";
        $sql        =   "select a.* from `".$this->def."crm_phoneauth` a where {$where}";
        $pageM      =   $this->MODEL('page');

        $perPage = !empty($_POST['limit']) ? intval($_POST['limit']) : intval($this->config['sy_listnum']);
        $pages      =   $pageM->adminPageList('crm_phoneauth', $where,$_POST['page'],array('limit'=>$perPage,'sql'=>$countSql));

        $order      =   '';
        if ($pages['total'] > 0) {
            if (!empty($_POST['order'])) {
                if ($_POST['t'] == 'time') {

                    $order .= " order by a.atime " . $_POST['order'];
                } else {

                    $order .= ' order by a.' . $_POST['t'] . ' ' . $_POST['order'];
                }
            } else {

                $order .= " order by a.atime desc";
            }

            $limit  =   "limit {$pages['limit'][0]},{$pages['limit'][1]}";
            $sql    .=  " {$order} {$limit}";
            $List   =   $crmphoneauthM->getList(array(), array('utype' => 'admin', 'sql' => $sql));
        }

        $data['list']           =   !empty($List)?$List:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']      =   $perPage;

        $this->render_json(0, '', $data);
    }

    function phoneauthRemark_action(){

        if (empty($_POST['content'])){

            $this->render_json(1,'请填写备注内容');
        }

        $id = (int)$_POST['id'];

        $post     =  array(
            'remark'  => trim($_POST['content'])
        );

        $crmphoneauthM  =  $this -> MODEL('crmphoneauth');

        $return   =  $crmphoneauthM -> upPhoneAuth(array('id'=>$id),$post);
        $error = $return['errcode']==9?0:1;
        $this->render_json($error,$return['msg']);
    }

    function delPhoneAuth_action(){

        $crmphoneauthM  =   $this->MODEL('crmphoneauth');

        if (is_array($_POST['del'])) {

            $id = $_POST['del'];
        } else {

            $id = intval($_POST['del']);
        }
        $arr = $crmphoneauthM->delPhoneAuth($id, array('utype' => 'admin'));

        $error = $arr['errcode']==9?0:1;

        $this->render_json($error,$arr['msg']);

    }

    function authContactCustomer_action()
    {
        $adminM = $this->MODEL('admin');
        $staffCallM = $this->MODEL('staffcalllog');
        $adminInfo = $adminM->getAdminUser(array('uid' => $_SESSION['auid']));
        if (!$adminInfo['moblie']) {
            $this->render_json(400,'业务员未填写手机号');
        }

        $mobile = trim($_POST['mobile']);
        if (!$mobile || !CheckMobile($mobile)) {
            $this->render_json(400,'手机号格式错误');
        }

        $apiurl = 'https://u.phpyun.com/call';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            $this->render_json(400,'请先完成外呼配置！');
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

        $data = $id ? $id : 0;

        if ($result['code'] == 200) {
            $this->render_json(200,'呼叫成功',$data);
        } else {
            $this->render_json(400,$result['message'],$data);
        }
    }

    function newResume_action()
    {
        $cacheM = $this->MODEL('cache');
        $cache  = $cacheM->GetCache(array('user','hy'));

        $resumeM =  $this->MODEL('resume');
        $Info    =  $resumeM->getResumeInfo(array('uid'=>$_POST['uid']));

        $userdata        =   $cache['userdata'];
        $userclass_name  =   $cache['userclass_name'];

        $industry_index  =   $cache['industry_index'];
        $industry_name   =   $cache['industry_name'];

        foreach ($userdata as $key => $value) {
            $selectArr  =   array();
            foreach ($value as $k => $v) {
                $selectArr[] = array('val'=>$v,'name'=>$userclass_name[$v]);
            }

            $data[$key] =   $selectArr;
        }

        $selectArr = array();

        foreach ($industry_index as $ik => $iv) {

            $selectArr[] = array('val'=>$iv,'name'=>$industry_name[$iv]);
        }

        $data['industry'] =   $selectArr;

        $data['info'] = $Info;
        $data['user_sex'] = $cache['user_sex'];

        $this->render_json(0,'',$data);
    }

    function saveResume_action()
    {

        if ($_POST) {

            $resumeM    =   $this->MODEL('resume');

            $_POST      =   $this->post_trim($_POST);

            if ($_POST['uid']) {

                $uid    =   intval($_POST['uid']);

                $rData  =   array(

                    'name'      =>  $_POST['resume_name'],
                    'sex'       =>  $_POST['sex'],
                    'edu'       =>  $_POST['edu'],
                    'exp'       =>  $_POST['exp'],
                    'birthday'  =>  $_POST['birthday'],
                    'telphone'  => 	$_POST['telphone'],
                    'living'    => 	$_POST['living'],
                );

                //  求值意向
                $expectData =   array(

                    'height_status'	  =>  0,
                    'r_status'        =>  1,
                    'state'        	  =>  1,
                    'integrity'		  =>  55,
                    'lastupdate'	  =>  time(),
                    'ctime'			  =>  time(),
                    'name'			  =>  $_POST['name'],
                    'hy'			  =>  $_POST['hy'],
                    'job_classid'	  =>  $_POST['job_classid'],
                    'minsalary'		  =>  $_POST['minsalary'],
                    'maxsalary'		  =>  $_POST['maxsalary'],
                    'city_classid'	  =>  $_POST['city_classid'],
                    'type'			  =>  $_POST['type'],
                    'report'		  =>  $_POST['report'],
                    'jobstatus'		  =>  $_POST['jobstatus'],
                    'uid'			  =>  $uid,
                    'edu'             =>  $_POST['edu'],
                    'exp'             =>  $_POST['exp'],
                    'uname'           =>  $_POST['resume_name'],
                    'sex'             =>  $_POST['sex'],
                    'birthday'        =>  $_POST['birthday'],
                    'status'          =>  0
                );

                $workData   =   $eduData    =   array();
                if (!empty($_POST['work_name']) && !empty($_POST['work_sdate']) && !empty($_POST['work_title'])) {
                    $workValue  =   array(

                        'uid'       =>  $uid,
                        'name'      =>  $_POST['workname'],
                        'sdate'     =>  strtotime($_POST['work_sdate']),
                        'edate'     =>  $_POST['work_today'] == 1 ? 0 : strtotime($_POST['work_edate']),
                        'title'     =>  $_POST['work_title'],
                        'content'   =>  $_POST['work_content']
                    );
                    $workData[] =   $workValue;
                }

                if (!empty($_POST['edu_name']) && !empty($_POST['edu_sdate']) && !empty($_POST['work_title'])) {

                    $eduValue   =   array(

                        'uid'       =>  $uid,
                        'name'      =>  $_POST['edu_name'],
                        'sdate'     =>  strtotime($_POST['edu_sdate']),
                        'edate'     =>  strtotime($_POST['edu_edate']),
                        'specialty' =>  $_POST['specialty'],
                        'education' =>  $_POST['education']
                    );

                    $eduData[]  =   $eduValue;
                }
                $return =   $resumeM->addInfo(array('uid' => $uid, 'rData' => $rData, 'eData' => $expectData, 'workData'=> $workData, 'eduData'=> $eduData, 'utype' => 'admin'));

                $error = $return['errcode']==9?0:1;
                $msg = $return['msg'];
            }else{
                $error = 1;
                $msg = '请先选择要添加简历的用户';
            }
        }else{
            $error = 1;
            $msg = '请先填写数据';
        }

        $this->render_json($error,$msg);
    }

    function getResumeInfo_action()
    {
        $data = array();

        if ($_POST['id']){

            $resumeId   =   $_POST['id'];

            $crmM       =   $this->MODEL('crm');
            $resumeInfo =   $crmM->getResumeInfo($resumeId, array('auid' => $_SESSION['auid']));

            $cacheM     =   $this->MODEL('cache');
            $Cache      =   $cacheM->GetCache(array('user', 'hy'));

            $className  =   $Cache['userclass_name'];
            $hyName     =   $Cache['industry_name'];

            $userSex = $userLabel = $userEdu = $userWord = $industryArr = $userReport = $userType = $userJobStatus = $userAudit = $userIng = array();

            foreach ($Cache['user_sex'] as $k=>$v) {

                $userSex[]      =   array('id' => (string)$k, 'name' => $v);
            }
            foreach ($Cache['userdata']['user_edu'] as $v) {

                $userEdu[]      =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['userdata']['user_word'] as $v) {

                $userWord[]     =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['userdata']['user_report'] as $v) {

                $userReport[]   =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['userdata']['user_type'] as $v) {

                $userType[]     =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['userdata']['user_label'] as $v) {

                $userLabel[]    =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['userdata']['user_jobstatus'] as $v) {

                $userJobStatus[]=   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['userdata']['user_audit'] as $v) {

                $userAudit[]    =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['userdata']['user_ing'] as $v) {

                $userIng[]      =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['industry_index'] as $v) {

                $industryArr[]  =   array('id' => $v, 'name' => $hyName[$v]);
            }

            $subscribeM =   $this->MODEL('subscribe');
            $subscribe = $subscribeM->getInfo(array('uid' => $resumeInfo['uid'],'type' =>1,'orderby'=>'id,desc'));

            $data       =   array(
                'Info'          =>  !empty($resumeInfo)?$resumeInfo:array(),
                'userSex'       =>  $userSex,
                'userLabel'     =>  $userLabel,
                'userEdu'       =>  $userEdu,
                'userWord'      =>  $userWord,
                'userReport'    =>  $userReport,
                'userType'      =>  $userType,
                'userJobStatus' =>  $userJobStatus,
                'userAudit'     =>  $userAudit,
                'userIng'       =>  $userIng,
                'industryArr'   =>  $industryArr,
                'salary'        =>  $this->MODEL('resume')->salary(),
                'subscribe'     =>  !empty($subscribe)?$subscribe:array(),
                'issubscribe'   =>  !empty($subscribe)?1:0
            );
        }

        $this->render_json(0,'',$data);
    }
    function getInviteStaticData_action(){

        $crmM = $this->MODEL('crm');

        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $isOutcall = $appkey && $appsecret;

        $defnumarr  =   array('allNum'=>0,'authNum0'=>0,'authNum1'=>0,'authNum2'=>0,'authNum3'=>0);
        $numarr = $crmM->inviteNum();

        $data['numarr']         =   array_merge($defnumarr,$numarr);
        $data['is_outcall'] = $isOutcall;

        $this->render_json(0,'',$data);

    }
    /*
     * 业务员 客勤 面试回访
     */
    function inviteList_action()
    {

        $where  =   array('uid' => array('>', 0), 'isdel' => 9);

        $keywordStr =   $_POST['keyword'];
        $typeStr    =   intval($_POST['keytype']);
        if (!empty($keywordStr)) {
            if ($typeStr == 1) {

                $resumeM    =   $this->MODEL('resume');
                $rList      =   $resumeM->getResumeList(array('name' => array('like', $keywordStr)), array('field' => '`uid`'));
                $muids      =   array();
                if (is_array($rList)) {
                    foreach ($rList as $v) {

                        $muids[]    =   $v['uid'];
                    }
                }
                $where['uid']       =   array('in', pylode(',', $muids));
            } elseif ($typeStr == 2) {

                $where['fname']     =   array('like', $keywordStr);
            } elseif ($typeStr == 3) {

                $where['jobname']   =   array('like', $keywordStr);
            }
        }

        if (isset($_POST['time']) && !empty($_POST['time'])) {
            if ($_POST['time'] == 1) {

                $where['datetime'] = array('>=', strtotime('today'));
            } else {

                $where['datetime'] = array('>=', strtotime('-' . $_POST['time'] . ' day'));
            }
        }

        if (isset($_POST['is_browse']) && !empty($_POST['is_browse'])) {

            $where['is_browse'] = $_POST['is_browse'];
        }

        if (isset($_POST['status']) && $_POST['status'] != '') {

            $where['status'] = $_POST['status'] == 0 ? array('isnull') : $_POST['status'];
        }

        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('userid_msg', $where, $_POST['page'], array('limit' => $_POST['limit']));

        if ($pages['total'] > 0) {
            if (!empty($_POST['order'])) {

                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {

                $where['orderby'] = array('is_browse, asc', 'datetime, desc');
            }

            $where['limit'] = $pages['limit'];
            $crmM = $this->MODEL('crm');
            $List = $crmM->getInviteList($where, array('utype' => 'admin'));
        }

        $data['list'] = !empty($List) ? $List : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        $this->render_json(0, '', $data);
    }

    /*
     * 业务员 客勤 面试回访 标记
     */
    function setStatus_action()
    {

        $crmM       =   $this->MODEL('crm');
        $data       =   array(

            'id'            =>  $_POST['id'],
            'status'        =>  $_POST['status'],
            'status_remark' =>  $_POST['content'],
            'auid'          =>  $_SESSION['auid']
        );
        $return     =   $crmM->setInviteStatus($data);

        $error = $return['errcode']=='9'?0:1;

        $this->render_json($error,$return['msg']);

    }

    /*
     * 业务员 客勤 跟进记录
     */
    function follow_action()
    {

        $where  =   array();

        $keywordStr =   $_POST['keyword'];
        $typeStr    =   intval($_POST['keytype']);
        if (!empty($keywordStr)) {
            if ($typeStr == 1) {

                $resumeM    =   $this->MODEL('resume');
                $rList      =   $resumeM->getResumeList(array('name' => array('like', $keywordStr)), array('field' => '`uid`'));
                $muids      =   array();
                if (is_array($rList)) {
                    foreach ($rList as $v) {

                        $muids[] = $v['uid'];
                    }
                }
                $where['uid']   =   array('in', pylode(',', $muids));
            } elseif ($typeStr == 2) {

                $where['content']   =   array('like', $keywordStr);
            }
        }

        if (!empty($_POST['time'])) {

            if ($_POST['time'] == 1) {

                $where['f_time']  =   array('>=', strtotime('today'));
            } else {

                $where['f_time']  =   array('>=', strtotime('-' . $_POST['time'] . ' day'));
            }

        }

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('resume_follow', $where,$_POST['page'],array('limit'=>$_POST['limit']));

        if ($pages['total'] > 0) {
            if (!empty($_POST['t'])) {

                $where['orderby']   =   $_POST['t'].','.$_POST['order'];
            } else {

                $where['orderby']   =   array('f_time, desc');
            }

            $where['limit']         =   $pages['limit'];

            $crmM = $this->MODEL('crm');
            $List = $crmM->getResumeFollowList($where, array('utype' => 'admin'));
        }

        $data['list']           =   !empty($List)?$List:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $pages['page_size'];
        $this->render_json(0, '', $data);

    }

    /*
     * 业务员 客勤 删除跟进记录
     */
    function delFollow_action()
    {

        $crmM   =   $this->MODEL('crm');
        if (is_array($_POST['del'])) {
            $id =   $_POST['del'];
        } else {
            $id =   intval($_POST['del']);
        }
        $arr    =   $crmM->delResumeFollow($id, array('utype' => 'admin'));
        $error  =   $arr['errcode'] == '9'?0:1;
        $this->render_json($error,$arr['msg']);
    }

    /*
     * 业务员 客勤 拥有简历 管理 跟进记录
     */
    function getFollowList_action()
    {

        if ($_POST['id']){

            $crmM       =   $this->MODEL('crm');

            $resumeId   =   $_POST['id'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array('eid' => $resumeId);

            if ($_POST['followTab'] == 1){
                $where['auid']  =   $_SESSION['auid'];
            }

            $pageM          =   $this->MODEL('page');
            $pages          =   $pageM->adminPageList('resume_follow', $where,$_POST['page'],array('limit'=>$_POST['limit']));

            if ($pages['total'] > 0) {

                $where['orderby']   =   array('f_time, desc');
                $where['limit'] =   $pages['limit'];

                $FollowList     =   $crmM->getResumeFollowList($where, array('utype' => 'crm_com_info'));
            }
        }

        $data['list']           =   !empty($FollowList)?$FollowList:array();
        $data['total']          =   !empty($pages['total'])?$pages['total']:0;
        $data['page_sizes']     =   !empty($pages['page_sizes'])?$pages['page_sizes']:array();
        $data['last_page']      =   !empty($pages['last_page'])?$pages['last_page']:0;

        $this->render_json(0,'',$data);
    }

    /*
     * 业务员 客勤 拥有简历 管理 投递记录
     */
    function getApplyList_action()
    {
        $data = array();

        if ($_POST['id']){

            $crmM       =   $this->MODEL('crm');

            $resumeId   =   $_POST['id'];
            $page       =   $_POST['page'];

            $where      =   array('eid' => $resumeId);

            $pageM          =   $this->MODEL('page');
            $pages          =   $pageM->adminPageList('userid_job', $where,$_POST['page'],array('limit'=>$_POST['limit']));

            if ($pages['total'] > 0) {

                $where['orderby']   =   array('datetime, desc');
                $where['limit']     =   $pages['limit'];

                $ApplyList  =   $crmM->getResumeApplyList($where, array('utype' => 'crm_com_info'));
            }

        }

        $data['list']           =   !empty($ApplyList)?$ApplyList:array();
        $data['total']          =   !empty($pages['total'])?$pages['total']:0;
        $data['page_sizes']     =   !empty($pages['page_sizes'])?$pages['page_sizes']:array();
        $data['page_size']      =   $pages['page_size'];

        $this->render_json(0, '', $data);
    }

    /*
     * 业务员 客勤 拥有简历 管理 求职信息修改
     */
    function saveResumeType_action()
    {

        $resumeM    =   $this->MODEL('resume');

        $_POST      =   $this->post_trim($_POST);
        $data       =   array();
        if ($_POST['uid']) {

            $uid        =   intval($_POST['uid']);
            $type       =   $_POST['type'];

            if ($type == 'Info'){
                $resumeId   =   intval($_POST['id']);
                $rData      =   array(

                    'name'          =>  $_POST['name'],
                    'sex'           =>  $_POST['sex'],
                    'birthday'      =>  $_POST['birthday'],
                    'edu'           =>  $_POST['edu'],
                    'exp'           =>  $_POST['exp'],
                    'description'   =>  $_POST['description'],
                );
                $resume     =   $resumeM->getResumeInfo(array('uid'=>$uid));
                if(!$resume['photo'] || ($resume['defphoto']==2 && $rData['sex']!=$resume['sex'])){
                    $logo   =   $resumeM->deflogo($rData);
                    if($logo!=''){
                        $rData['photo']         = $logo;
                        $rData['defphoto']      = 2;
                        $rData['photo_status']  = 0;
                    }
                }
                if (isset($_POST['chatresumeinfo'])){
                    $mData = array(
                        'moblie' => $_POST['telphone'],
                        'email' => $_POST['email'],
                    );
                    $rData['telphone'] = $_POST['telphone'];
                    $rData['email'] = $_POST['email'];
                    $rData['domicile'] = $_POST['domicile'];
                    $rData['living'] = $_POST['living'];
                    $rData['marriage'] = $_POST['marriage'];
                    $rData['height'] = $_POST['height'];
                    $rData['nationality'] = $_POST['nationality'];
                    $rData['weight'] = $_POST['weight'];
                    $rData['idcard'] = $_POST['idcard'];
                    $rData['address'] = $_POST['address'];
                    $rData['homepage'] = $_POST['homepage'];
                    $rData['qq'] = $_POST['qq'];
                    if ($_FILES['file']['tmp_name']) {
                        $upArr = array(
                            'file' => $_FILES['file'],
                            'dir' => 'user'
                        );
                    }
                    $uploadM = $this->MODEL('upload');
                    if (!empty($upArr)) {
                        $pic = $uploadM->newUpload($upArr);
                    }
                    if (!empty($pic['picurl'])) {
                        $picture = $pic['picurl'];
                    }
                    !empty($picture) && $rData['wxewm'] = $picture;
                    $return = $resumeM->upResumeInfo(array('uid' => $uid), array('mData' => $mData, 'rData' => $rData, 'utype' => 'admin'));
                }else{
                    $return =   $resumeM->upResumeInfo(array('uid' => $uid), array('rData' => $rData));
                }
            } else if ($type == 'Expect'){
                $resumeId   =   intval($_POST['id']);
                $expectDate =  array(

                    'name'			=>	$_POST['name'],
                    'hy'			=>	$_POST['hy'],
                    'job_classid'	=>	$_POST['job_classid'],
                    'minsalary'		=>	$_POST['minsalary'],
                    'maxsalary'		=>	$_POST['maxsalary'],
                    'city_classid'	=>	$_POST['city_classid'],
                    'type'			=>	$_POST['typeid'],
                    'report'		=>	$_POST['report'],
                    'jobstatus'		=>	$_POST['jobstatus'],
                    'lastupdate'	=>	time()
                );

                $return =   $resumeM -> upInfo(array('id'=>$resumeId), array('eData'=>$expectDate,'utype'=>'admin'));
            } else if ($type == 'work'){

                $resumeId   =   intval($_POST['eid']);
                $id         =   intval($_POST['id']);

                $post       =   array(

                    'uid'       =>  $uid,
                    'eid'       =>  $resumeId,
                    'name'      =>  $_POST['name'],
                    'sdate'     =>  strtotime($_POST['sdate']),
                    'edate'     =>  strtotime($_POST['edate']),
                    'title'     =>  $_POST['title'],
                    'content'   =>  $_POST['content']
                );

                if ($id > 0) {
                    $return = $resumeM->addResumeWork($post, array('where' => array('id' => $id), 'utype' => 'admin'));
                } else {
                    $return = $resumeM->addResumeWork($post, array('utype' => 'admin'));
                }
            } else if ($type == 'edu'){

                $resumeId   =   intval($_POST['eid']);
                $id         =   intval($_POST['id']);

                $post       =   array(

                    'uid'       =>  $uid,
                    'eid'       =>  $resumeId,
                    'name'      =>  $_POST['name'],
                    'sdate'     =>  strtotime($_POST['sdate']),
                    'edate'     =>  strtotime($_POST['edate']),
                    'specialty'     =>  $_POST['specialty'],
                    'education' =>  $_POST['education']
                );

                if ($id > 0) {
                    $return = $resumeM->addResumeEdu($post, array('where' => array('id' => $id), 'utype' => 'admin'));
                } else {
                    $return = $resumeM->addResumeEdu($post, array('utype' => 'admin'));
                }
            } else if ($type == 'train'){

                $resumeId   =   intval($_POST['eid']);
                $id         =   intval($_POST['id']);

                $post       =   array(

                    'uid'       =>  $uid,
                    'eid'       =>  $resumeId,
                    'name'      =>  $_POST['name'],
                    'sdate'     =>  strtotime($_POST['sdate']),
                    'edate'     =>  strtotime($_POST['edate']),
                    'title'     =>  $_POST['title'],
                    'content'   =>  $_POST['content']
                );

                if ($id > 0) {
                    $return = $resumeM->addResumeTrain($post, array('where' => array('id' => $id), 'utype' => 'admin'));
                } else {
                    $return = $resumeM->addResumeTrain($post, array('utype' => 'admin'));
                }
            } else if ($type == 'skill'){

                $resumeId   =   intval($_POST['eid']);
                $id         =   intval($_POST['id']);

                $post       =   array(
                    'uid'       =>  $uid,
                    'eid'       =>  $resumeId,
                    'name'      =>  $_POST['name'],
                    'ing'       =>  $_POST['ing'],
                    'longtime'  =>  $_POST['longtime'],
                    'file'      =>  $_FILES['file'],
                );

                if ($id > 0) {
                    $return = $resumeM->addResumeSkill($post, array('where' => array('id' => $id), 'utype' => 'admin'));
                } else {
                    $return = $resumeM->addResumeSkill($post, array('utype' => 'admin'));
                }
            } else if ($type == 'project'){

                $resumeId   =   intval($_POST['eid']);
                $id         =   intval($_POST['id']);

                $post       =   array(

                    'uid'       =>  $uid,
                    'eid'       =>  $resumeId,
                    'name'      =>  $_POST['name'],
                    'sdate'     =>  strtotime($_POST['sdate']),
                    'edate'     =>  strtotime($_POST['edate']),
                    'title'     =>  $_POST['title'],
                    'content'   =>  $_POST['content']
                );

                if ($id > 0) {
                    $return = $resumeM->addResumeProject($post, array('where' => array('id' => $id), 'utype' => 'admin'));
                } else {
                    $return = $resumeM->addResumeProject($post, array('utype' => 'admin'));
                }
            } else if ($type == 'other'){

                $resumeId   =   intval($_POST['eid']);
                $id         =   intval($_POST['id']);

                $post       =   array(

                    'uid'       =>  $uid,
                    'eid'       =>  $resumeId,
                    'name'      =>  $_POST['name'],
                    'content'   =>  $_POST['content']
                );

                if ($id > 0) {
                    $return = $resumeM->addResumeOther($post, array('where' => array('id' => $id), 'utype' => 'admin'));
                } else {
                    $return = $resumeM->addResumeOther($post, array('utype' => 'admin'));
                }
            } else if ($type == 'tag'){
                $resumeId   =   intval($_POST['eid']);
                $tagStr = '';
                if (!empty($_POST['tag']) && is_array($_POST['tag'])) {
                    $tag = array_unique($_POST['tag']);
                    foreach ($tag as $value) {
                        $tagLen = mb_strlen($value);
                        if ($tagLen >= 2 && $tagLen <= 8) {
                            $tagList[] = $value;
                        }
                        if (count($tagList) >= 5) {
                            break;
                        }
                    }
                    $tagStr = implode(',', $tagList);
                }
                $rData = array(
                    'tag' => $tagStr,
                    'description' => $_POST['description'],
                    'lastupdate' => time()
                );
                $return = $resumeM->upResumeInfo(array('uid' => $uid), array('rData' => $rData));
            }

            if (intval($return['errcode']) == 9) {

                $crmM           =   $this->MODEL('crm');
                $resumeInfo     =   $crmM->getResumeInfo($resumeId, array('auid' => $_SESSION['auid']));

                $error = 0;
                $msg = '保存成功';
                $data['Info'] = $resumeInfo;
            } else {
                $error = 1;
                $msg = '操作失败';
            }
        }else{
            $error = 1;
            $msg = '用户不存在';
        }
        $this->render_json($error,$msg,$data);
    }

    /*
     * 业务员 客勤 拥有简历 管理 简历审核
     */
    function statusResume_action()
    {

        $resumeM    =   $this->MODEL('resume');

        $resumeId   =   intval($_POST['id']);
        $uid        =   intval($_POST['uid']);
        $status     =   intval($_POST['status']);
        $statusbody =   trim($_POST['statusbody']);

        if (isset($_POST['lock_status']) && $_POST['lock_status'] == 1){

            $post   =   array(

                'uid'           =>  $uid,
                'lock_status'   =>  1,
                'state'         =>  $status,
                'statusbody'    =>  $statusbody
            );
            $return =   $resumeM->status($resumeId, $post);
        }else {
            $post = array(

                'state' => intval($_POST['status']),
                'statusbody' => trim($_POST['statusbody'])
            );
            $return = $resumeM->statusResume($resumeId, array('post' => $post));
        }

        $error = $return['errcode']=='9'?0:1;

        $this->render_json($error,$return['msg']);

    }

    /*
     * 业务员 客勤 拥有简历 管理 删除求职信息
     */
    function delResumeFb_action()
    {

        $table  =   trim($_POST['table']);
        $tables =   array('skill', 'work', 'project', 'edu', 'training', 'other');

        if (in_array($table, $tables)) {

            $id     =   (int)$_POST['id'];
            $eid    =   (int)$_POST['eid'];
            $uid    =   (int)$_POST['uid'];

            $resumeM    =   $this->MODEL('resume');
            $return     =   $resumeM->delFb($table, array('id' => $id, 'eid' => $eid, 'uid' => $uid), array('utype' => 'admin'));

            if ($return['errcode'] == '9'){
                $error = 0;
                $msg = '操作成功！';
            }else{
                $error = 1;
                $msg = $return['msg'];
            }
        } else {
            $error = 1;
            $msg = '系统异常';
        }

        $this->render_json($error,$msg);
    }
    /*
     * 业务员 客勤 拥有简历 管理 职位订阅
     */
    function saveSub_action(){

        $subscribeM = $this->MODEL('subscribe');

        $data = array(
            'sid'           =>  $_POST['sid'],
            'time'          =>  $_POST['time'],
            'jobclass_id'   =>  $_POST['jobclass_id'],
            'type'          =>  1,
            'uid'           =>  $_POST['uid'],
            'usertype'      =>  1,
            'utype'         =>  'admin'
        );

        $res            =   $subscribeM->createSubscribe($data);
        $error = $res['errcode'] == 9?0:1;
        $this->render_json($error,$res['msg']);
    }

    // 简历投递记录
    public function useridJob_action(){
        $where      =   array('uid' => $_POST['uid'], 'eid' => $_POST['eid']);

        $rows = array();

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('userid_job', $where,$_POST['page'],array('limit'=>$_POST['limit']));
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $jobM = $this->MODEL('job');
            $list = $jobM->getSqJobList($where, array('utype' => 'simple'));

            $jobid = $uids = array();
            foreach ($list as $v) {
                $uids[] = $v['uid'];
                $jobid[] = $v['job_id'];
            }
            $tongjiM = $this->MODEL('tongji');
            $resume = $tongjiM->tjtotal('resume', array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`name`'));

            // 个人会员，要查投递职位的职位类别
            $jobs = $jobM->getList(array('id' => array('in', pylode(',', $jobid))), array('field' => 'id,job1'));

            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['comid'] = $v['com_id'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
                foreach ($resume as $val) {
                    if ($v['uid'] == $val['uid']) {
                        $rows[$k]['name'] = $val['name'];
                    }
                }
                if (isset($jobs)) {
                    foreach ($jobs['list'] as $jv) {
                        if ($v['job_id'] == $jv['id']) {
                            $rows[$k]['jobclassname'] = $jv['job_one_n'];
                        }
                    }
                }
            }
        }

        $data['list']           =   !empty($rows)?$rows:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $pages['page_size'];
        $this->render_json(0, '', $data);

    }

    // 聊天记录
    public function chatlogList_action(){
        $where = array('uid' => $_POST['uid'], 'usertype' => 1, 'fusertype' => 2);

        $rows = array();

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('chat_friend', $where,$_POST['page'],array('limit'=>$_POST['limit']));
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            $where['orderby'] = array('ntime,desc');
            $where['limit'] = $pages['limit'];
            $chatM = $this->MODEL('chat');
            $list = $chatM->getFriendList($where, array('utype' => 'admin'));

            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['countname'];
                $rows[$k]['comid'] = $v['fid'];
                $rows[$k]['jobname'] = '';
                $rows[$k]['comname'] = $v['rcountname'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['ntime']);
                $rows[$k]['beginid'] = $v['beginid'];
            }
        }

        $data['list']           =   !empty($rows)?$rows:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }

    public function  acwxbind_action()
    {
        if (!$_POST['uid']){
            $this->render_json(1, '参数错误，请重试！');
        }
        $time = time();
        $randStr = $time . rand(1000, 9999);
        $cookie = $this->MODEL('cookie');
        $cookie->setCookie('acwxbind', $randStr, $time + 3600);
        $WxM = $this->MODEL('weixin');
        $qrcode = $WxM->applyWxQrcode($randStr, 'acwxbind', $_POST['uid']);
        if (!$qrcode) {
            $this->render_json(1, '二维码获取失败');
        } else {
            $this->render_json(0, 'ok', array('code_url' => $qrcode));
        }
    }

    // 检测二维码扫码绑定情况
    function getacbindstatus_action(){

        if(!isset($_COOKIE['acwxbind'])){
            $this->render_json(1, '扫码绑定失败');
        }
        if (!$_POST['uid']){
            $this->render_json(1, '参数错误，请重试！');
        }
        $WxM = $this->MODEL('weixin');
        $result = $WxM->getWxLoginStatus($_COOKIE['acwxbind'], $_POST['uid']);
        if($result['status'] == 1){
            if (!empty($result['member'])){
                $this->admin_json(0, '扫码绑定成功');
            }else{
                $this->render_json(1, '扫码绑定失败');
            }
        }
    }


}
?>
