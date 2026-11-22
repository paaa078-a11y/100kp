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
class crm_contact_log_controller extends adminCommon
{
    function set_search(){
        $timeLength = array('1' => '大于30秒', '2' => '大于1分钟', '3' => '大于3分钟');
        $search_list = array();
        $search_list[] = array('param'=>'time_length','name'=>'通话时长','value'=>$timeLength);
        $this -> yunset(array('search_list' => $search_list));
    }
    function index_action()
    {
        $AdminM = $this->MODEL('admin');
        // 获取所有业务员
        $adminUserList = $AdminM->getList(array('is_crm' => 1), array('field' => '`uid`,`name`,`username`'));
        $uids = array();
        foreach ($adminUserList as $v) {
            $uids[] = $v['uid'];
        }
        $this->yunset('adminUserList', $adminUserList);

        $where = $urlarr = array();
        $staffcalllogM = $this->MODEL('staffcalllog');
        // 从crm工作台我的简报外呼电话量跳转过来
        if (isset($_GET['fromCrmIndex']) && $_GET['fromCrmIndex'] == 1) {
            if ($_GET['time'] == 1) {//今天
                $stime  =   mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $endTime  =   mktime(23, 59, 59, date('m'), date('d'), date('Y'));
            } else if ($_GET['time'] == 2) {//昨天
                $stime  =   mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 86400;
                $endTime  =   mktime(23, 59, 59, date('m'), date('d'), date('Y')) - 86400;
            } else if ($_GET['time'] == 3) {//本周
                $stime  =   strtotime(date('Y-m-d', strtotime("this week Monday", time())));
                $endTime  =   strtotime(date('Y-m-d', strtotime("this week Sunday", time()))) + 24 * 3600 - 1;
            } else if ($_GET['time'] == 4) {//本月
                $stime  =   mktime(0, 0, 0, date('m'), 1, date('Y'));
                $endTime  =   mktime(23, 59, 59, date('m'), date('t'), date('Y'));
            }
            $where['PHPYUNBTWSTART_A'] = '';
            $where['ctime'][] = array('>=', $stime, 'AND');
            $where['ctime'][] = array('<=', $endTime, 'AND');
            $where['PHPYUNBTWEND_A'] = '';
            $where['uid'] = intval($_SESSION['auid']);
            $urlarr['crm_uid'] = $_SESSION['auid'];
            $this->yunset('crm_uid', $_SESSION['auid']);
        }

        // 不传业务员id时，查自己及全部下属客户的外呼记录
        if (isset($_GET['crm_uid']) && $_GET['crm_uid']) {
            $where['uid'] = intval($_GET['crm_uid']);
        } else if ($uids){
            $where['uid'] = array('in', pylode(',', $uids));
        }

        if (isset($_GET['s_time']) && $_GET['s_time']) {
            $where['PHPYUNBTWSTART_A'] = '';
            if (strpos($_GET['s_time'], '-')){// 开始时间非时间戳
                $stime = strtotime(trim($_GET['s_time']));
            } else {
                $stime = $_GET['s_time'];
            }
            $where['ctime'][] = array('>=', $stime, 'AND');
            if (!isset($_GET['e_time']) || !$_GET['e_time']) {
                $endTime = time();
            } else if (strpos($_GET['e_time'], '-')) {// 结束时间非时间戳
                $endTime = strtotime(trim($_GET['e_time']));
            }else {
                $endTime = $_GET['e_time'];
            }
            $where['ctime'][] = array('<=', $endTime, 'AND');
            $where['PHPYUNBTWEND_A'] = '';
        }
        $this->yunset('s_date', $stime ? date('Y-m-d H:i:s', $stime) : '');
        $this->yunset('e_date', $endTime ? date('Y-m-d H:i:s', $endTime) : '');
        if (isset($_GET['keyword']) && $_GET['keyword']) {
            $keyword = trim($_GET['keyword']);
            if ($_GET['crm_type'] == 2) {// 主叫
                $where['caller'] = array('like', $keyword);
            } else {// 被叫
                $where['callee'] = array('like', $keyword);
            }
        }
        if (isset($_GET['call_status']) && $_GET['call_status'] != -1) {
            $where['call_status'] = $_GET['call_status'];
        }

        // 通话时长筛选
        if (isset($_GET['time_length']) && $_GET['time_length'] == 1) {// 大于30秒
            $seconds = 30;
        } else if (isset($_GET['time_length']) && $_GET['time_length'] == 2) {// 大于1分钟
            $seconds = 60;
        } else if (isset($_GET['time_length']) && $_GET['time_length'] == 3) {// 大于3分钟
            $seconds = 180;
        }
        if (isset($seconds) && $seconds > 0) {
            $where['time_length'] = array('>', $seconds);
        }
        $where['source'] = 1;   //  新增
        $urlarr = $_GET;
        $urlarr['s_time'] = $stime;
        $urlarr['e_time'] = $endTime;
        $urlarr['page'] = '{{page}}';
        $pageurl = Url($_GET['m'], $urlarr, 'admin');
        $pageM = $this->MODEL('page');
        $pages = $pageM->pageList('staff_call_log', $where, $pageurl, $_GET['page']);
        if ($pages['total'] > 0) {
            if ($_GET['order']) {
                $where['orderby'] = $_GET['t'].','.$_GET['order'];
                $urlarr['order'] = $_GET['order'];
                $urlarr['t'] = $_GET['t'];
            } else {
                $where['orderby'] = 'ctime,desc';
            }
            $where['limit'] = $pages['limit'];
            $list = $staffcalllogM->getList($where);
            $this->yunset(array('tasks' => $list));
        }
        $this->yunset('contactOssUrl', $this->config['sy_outcall_oss']);
        $this->set_search();
        $this->yuntpl(array('admin/crm_contact_log'));
    }

    /*
     * 坐席列表
     */
    function staffList_action(){
        $apiurl = 'https://u.phpyun.com/stafflist';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $adminM = $this->MODEL('admin');
        $adminInfos = $adminM->getList(array('is_crm' => 1), array('field' => '`m_id`, `name`, `moblie`'));
        $mobileToName = array();
        $groupArr = array();
        foreach ($adminInfos as $adminInfo) {
            $groupArr[$adminInfo['m_id']] = ['id' => $adminInfo['m_id'], 'name' => $adminInfo['group_name']];
            if ($adminInfo['moblie']) {
                $mobileToName[$adminInfo['moblie']] = $adminInfo['name'];
            }
        }
        $task = array();
        $keyword = trim($_GET['keyword']) ? trim($_GET['keyword']) : '';
        if ($appkey && $appsecret) {
            $params = array(
                'appKey' => $appkey,
                'appSecret' => $appsecret,
                'keyword' => $keyword
            );
            $params['perPage'] = isset($_GET['limit']) && $_GET['limit'] ? $_GET['limit'] : $this->config['sy_listnum'];
            $params['page'] = isset($_GET['page']) && $_GET['page'] > 1 ? $_GET['page'] : 1;
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            $task = $resArr['data'];
            if ($task) {
                foreach ($task as &$v) {
                    $v['name'] = isset($mobileToName[$v['mobile']]) ? $mobileToName[$v['mobile']] : '暂无业务员信息';
                }
            }
        }
        $urlarr = $_GET;
        $urlarr['page'] = '{{page}}';
        $this->yunset(array('tasks' => $task));
        $this->yunset('user_group', $groupArr);
        $this->yuntpl(array('admin/crm_contact_staff_list'));
    }

    /*
     * 获取云呼短信验证码
     */
    function getYhCode_action(){
        $apiurl = 'https://u.phpyun.com/yhcode';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if ($appkey && $appsecret) {
            $params = array(
                'appKey' => $appkey,
                'appSecret' => $appsecret,
                'sid' => $_POST['staff_id'],
                'mobile' => $_POST['mobile']
            );
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            $code = $resArr['code'];
            if ($resArr['code'] == 200) {
                if ($resArr['message'] == '坐席手机已在白名单中，坐席绑定成功') {
                    $code = 201;// 绑定成功，前台刷新页面
                }
            }
            echo json_encode(['code' => $code, 'msg' => $resArr['message']]);exit();
        }
    }

    /*
     * 坐席配置
     */
    function getUsers_action(){
        $adminM = $this->MODEL('admin');
        $adminusers = $adminM->getList(array('is_crm' => 1, 'm_id' => $_POST['group_id']), array('field' => '`uid`, `name`, `moblie`'));
        echo json_encode(['code' => 200, 'data' => $adminusers]);exit();
    }

    /*
     * 绑定坐席
     */
    function bindStaff_action(){
        $apiurl = 'https://u.phpyun.com/bindstaff';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            echo json_encode(['code' => 400, 'msg' => '请先完成外呼配置']);exit();
        }
        if (!$_POST['staff_id']) {
            echo json_encode(['code' => 400, 'msg' => '坐席id不能为空']);exit();
        }
        if (!trim($_POST['mobile'])) {
            echo json_encode(['code' => 400, 'msg' => '绑定的手机号不能为空']);exit();
        }
        if ($appkey && $appsecret) {
            $params = array(
                'appKey' => $appkey,
                'appSecret' => $appsecret,
                'id' => intval($_POST['staff_id']),// 坐席id
                'mobile' => trim($_POST['mobile']),// 要绑定的手机号
                'code' => trim($_POST['code'])
            );
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            if ($resArr['code'] == 200) {
                echo json_encode(['code' => 200, 'msg' => '绑定成功']);exit();
            } else {
                echo json_encode(['code' => 400, 'msg' => $resArr['message']]);exit();
            }
        }
    }

    /*
     * 重新发送坐席验证信息
     */
    function remsg_action(){
        $apiurl = 'https://u.phpyun.com/resetstaffmsg';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            echo json_encode(['code' => 400, 'msg' => '请先完成外呼配置']);exit();
        }
        if (!$_POST['staff_id']) {
            echo json_encode(['code' => 400, 'msg' => '坐席id不能为空']);exit();
        }
        if ($appkey && $appsecret) {
            $params = array(
                'appKey' => $appkey,
                'appSecret' => $appsecret,
                'id' => intval($_POST['staff_id']),// 坐席id
            );
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            if ($resArr['code'] == 200) {
                echo json_encode(['code' => 200, 'msg' => '发送成功']);exit();
            } else {
                echo json_encode(['code' => 400, 'msg' => $resArr['message']]);exit();
            }
        }
    }

    /*
     * 从综合平台获取通话详细
     */
    function upCallLog_action(){
        $callLogM = $this->MODEL('staffcalllog');
//        $where['status'] = array('in', '1,2');
        $where['session_id'] = array('<>', '');
//        $where['limit'] = 500;
//        $where['status'] = array('in', '1,2');
        $where['PHPYUNBTWSTART_A']  	=   '';
        $where['status'][]            =   array('in', '1,2', 'AND');
        $where['oss_record_path'][]            =   array('=', '', 'OR');
        $where['PHPYUNBTWEND_A']  	=   '';
        $where['limit'] = 500;
        $data = $callLogM->getList($where, array('field' => '`session_id`,`id`'));
        if (count($data) == 0) {
            echo json_encode(['code' => 400, 'msg' => '暂无要更新的通话记录！']);exit();
        }
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            echo json_encode(['code' => 400, 'msg' => '请先完成外呼配置！']);exit();
        }
        $sids = array();
        $sessionToId = array();
        foreach ($data as $v) {
            !in_array($v['session_id'], $sids) && array_push($sids, $v['session_id']);
            $sessionToId[$v['session_id']] = $v['id'];
        }
        $apiurl = 'https://u.phpyun.com/upcalldtl';
        $params = array(
            'appKey' => $appkey,
            'appSecret' => $appsecret,
            'sessionIds' => json_encode($sids)
        );
        $res = CurlPost($apiurl, $params, 1);
        $resArr = json_decode($res, 1);
        if ($resArr['code'] != 200) {
            echo json_encode(['code' => 400, 'msg' => $resArr['message']]);exit();
        }
        $upCnt = 0;// 更新数量
        foreach ($resArr['data'] as $v) {
            if (isset($sessionToId[$v['session_id']])) {
                $r = $callLogM->upInfo(array('id' => $sessionToId[$v['session_id']]), array(
                    'time_length' => $v['time_length'],
                    'connect_time' => $v['connect_time'],
                    'disconnect_time' => $v['disconnect_time'],
                    'status' => $v['status'],
                    'call_status' => $v['call_status'],
                    'oss_record_path' => $v['oss_record_path'],
                    'api_res' => $res
                ));
                $r && $upCnt++;
            }
        }
        echo json_encode(['code' => 200, 'msg' => '成功同步' . $upCnt . '条通话记录']);exit();
    }

    //外呼配置
    function config_action(){
        $cacheM = $this->MODEL('cache');
        $options = array('city');
        $cache = $cacheM->GetCache($options);
        $this->yunset($cache);
        $this->yuntpl(array('admin/outcall_config'));
    }

    //保存外呼配置
    function save_action(){
        $configM = $this->MODEL('config');
        if($_POST['config']){
            unset($_POST['config']);
            $apiurl = 'https://u.phpyun.com/getosspre';
            $params = array(
                'appKey' => trim($_POST['sy_outcall_appkey']),
                'appSecret' => trim($_POST['sy_outcall_appsecret'])
            );
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            if ($resArr['code'] != 200) {
                $this->ACT_layer_msg( $resArr['message'],8,1,2,1);
//                echo json_encode(['code' => 400, 'msg' => $resArr['message']]);exit();
            }
            $_POST['sy_outcall_oss'] = $resArr['data'];
            $configM->setConfig($_POST);
            $this->web_config();
            $this->ACT_layer_msg( "外呼配置设置成功！",9,1,2,1);
        }
    }

    /*
     * 外呼数据看板（呼出总数、接通量、无人接听量、呼出总时长）
     */
    function totalTj_action(){
        $callLogM = $this->MODEL('staffcalllog');
        $TimeDate =	$this->day();
        $sdate = $TimeDate['sdate'];
        $edate = $TimeDate['edate'];
        // 日期不带时分秒
        if (strlen($edate) == 10) {
            $edate = $edate . ' 23:59:59';
        }
        if (strlen($sdate) == 10) {
            $sdate = $sdate . ' 00:00:00';
        }
        $where['PHPYUNBTWSTART_A'] = '';
        $where['ctime'][] = array('>=', strtotime($sdate), 'AND');
        $where['ctime'][] = array('<=', strtotime($edate),'AND');
        $where['PHPYUNBTWEND_A'] = '';
        $allCnt = $callLogM->getCallNum($where);
        // call_status 0未知，1主叫挂断，2主叫接通，3已接通
        $jtWhere = $where;
        $jtWhere['call_status'] = 3;
        $jtCnt = $callLogM->getCallNum($jtWhere);// 接通量
        $wrWhere = $where;
        $wrWhere['call_status'] = 2;
        $wrCnt = $callLogM->getCallNum($wrWhere);// 被叫无人接听
        // 获取坐席今日外呼统计信息
        $sql = 'select sum(ceil(time_length/60)) as total_length from ' . $this->def .'staff_call_log where ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate);
        $totalTime = $callLogM->getListBySql($sql, 1);
        $totalTime = isset($totalTime[0]['total_length']) && $totalTime[0]['total_length'] ? $totalTime[0]['total_length'] : 0;
        echo json_encode(array('all_cnt' => $allCnt, 'jt_cnt' => $jtCnt, 'wr_cnt' => $wrCnt, 'total_time' => $totalTime));
        exit();
    }

    /*
     * 数据看板
     */
    function outcall_statistics_action()
    {
        //  获取通话时长包信息
        $apiurl = 'https://u.phpyun.com/staffbalance';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $totalSend = $totalPersonal = 0;
        if ($appkey && $appsecret) {
            $apiParams = array(
                'appKey' => $appkey,
                'appSecret' => $appsecret
            );
            $res = CurlPost($apiurl, $apiParams, 1);
            $resArr = json_decode($res, 1);
            if (isset($resArr['total_send']) && $resArr['total_send']) {
                $totalSend = intval($resArr['total_send']);
            }
            if (isset($resArr['total_personal']) && $resArr['total_personal']) {
                $totalPersonal = intval($resArr['total_personal']);
            }
        }
        $this->yunset('balanceArr', array('total_send' => $totalSend, 'total_personal' => $totalPersonal));
        $now_url = $this->config['sy_weburl'].$_SERVER["REQUEST_URI"];
        $today = date('Y-m-d');
        $this->yunset('today', $today);
		$cacheM	=	$this   -> MODEL('cache');
		$cache	=	$cacheM->GetCache(array('crm'));
		$this->yunset("cache",$cache);
        $this->yunset("lasturl", $now_url);
        $this->yuntpl(array('admin/outcall_statistics'));
    }

    //业务员看板数据
    function staffTj_action(){
        $callLogM = $this->MODEL('staffcalllog');
        $TimeDate =	$this->day();
        $sdate = $TimeDate['sdate'];
        $edate = $TimeDate['edate'];
        // 日期不带时分秒
        if (strlen($edate) == 10) {
            $edate = $edate . ' 23:59:59';
        }
        if (strlen($sdate) == 10) {
            $sdate = $sdate . ' 00:00:00';
        }
        $countSql = 'select count(*) as num from (select uid from ' . $this->def .'staff_call_log where ctime >= ' . strtotime($sdate) . ' and ctime <='. strtotime($edate) .' group by uid) as tmp';
        //分页链接
        $urlarr = $_GET;
        $urlarr['page'] = '{{page}}';
        $pageurl = Url($_GET['m'], $urlarr, 'admin');
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->pageList('staff_call_log', array(), $pageurl, $_GET['page'], '', $countSql);
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            $perPage = $this->config['sy_listnum'];
            $totalPage = ceil($pages['total']/$perPage);
            $pages['per_page'] = $perPage;
            $pages['total_page'] = $totalPage;
            $pages['curr_page'] = $_GET['page'] > 1 ? $_GET['page'] : 1;
            //limit order 只有在列表查询时才需要
            $sql = "select uid,username,count(*) as total_cnt,sum(ceil(time_length/60)) as total_length,count(if(call_status = 3,1,null)) as jt_cnt,count(if(call_status = 2,1,null)) as wr_cnt from `" . $this->def ."staff_call_log` where ctime >= " . strtotime($sdate) . " and ctime <=". strtotime($edate) ." group by uid,username limit {$pages['limit'][0]},{$pages['limit'][1]}";
            $list = $callLogM->getListBySql($sql);
            echo json_encode(array('list' => $list, 'pages' => $pages));
            exit();
        }
    }

    // 获取统计时间
    function day(){
        if ((int) $_GET['days'] > 0) {
            $days =	intval($_GET['days']);
            $sdate = date('Y-m-d', (time() - ($days - 1) * 86400));
            $edate = date('Y-m-d', time());
        } elseif ($_GET['sdate']) {
            $sdate = $_GET['sdate'];
            $days =	ceil(abs(time() - strtotime($sdate)) / 86400);
            if ($_GET['edate']) {
                $edate = $_GET['edate'];
                $days =	ceil(abs(strtotime($edate) - strtotime($sdate)) / 86400);
            }
        } else {// 不传days和起止时间默认查当天
            $days =	1;
            $sdate = $edate = date('Y-m-d');
        }
        return array('sdate' => $sdate, 'days' => $days, 'edate' => $edate);
    }

    /*
     * 外呼统计
     */
    function tj_action(){
        $TimeDate =	$this->day();
        $sdate = $TimeDate['sdate'];
        $edate = $TimeDate['edate'];
        // 日期不带时分秒
        if (strlen($edate) == 10) {
            $edate = $edate . ' 23:59:59';
        }
        if (strlen($sdate) == 10) {
            $sdate = $sdate . ' 00:00:00';
        }
        $days =	$TimeDate['days'];
        // 总电话量
        $callLogM = $this->MODEL('staffcalllog');
        $defaultData = array();
        if ($days > 1) {
            $groupBy = $FieldFormat = "FROM_UNIXTIME(`ctime`,'%m-%d')";
            for($i = 0; $i < $days; $i++){
                $date = date('m-d', strtotime($sdate) + 86400 * $i);
                $defaultData[$date] = 0;
            }
        } else {
            $groupBy = $FieldFormat = "FROM_UNIXTIME(`ctime`,'%H:00')";
            for($i = 0; $i < 24; $i++){
                $hour = $i < 10 ? '0' . $i : $i;
                $defaultData[$hour . ':00'] = 0;
            }
        }
        $sql = 'select count(*) as cnt,' . $groupBy . 'as time from ' . $this->def . 'staff_call_log where ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate) . ' group by ' . $groupBy;
        $data1 = $callLogM->getListBySql($sql, 1);
        $list1 = array();
        foreach ($data1 as $v) {
            $list1[$v['time']] = $v['cnt'];
        }
        $list1 = array_merge($defaultData, $list1);
        ksort($list1);
        // 业务电话
        $sql2 = 'select count(*) as cnt,' . $groupBy . 'as time from ' . $this->def . 'staff_call_log where usertype = 2 and ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate) . ' group by ' . $groupBy;
        $data2 = $callLogM->getListBySql($sql2);
        $list2 = array();
        foreach ($data2 as $v) {
            $list2[$v['time']] = $v['cnt'];
        }
        $list2 = array_merge($defaultData, $list2);
        ksort($list2);
        // 客勤电话
        $sql3 = 'select count(*) as cnt,' . $groupBy . 'as time from ' . $this->def . 'staff_call_log where usertype = 1 and ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate) . ' group by ' . $groupBy;
        $data3 = $callLogM->getListBySql($sql3);
        $list3 = array();
        foreach ($data3 as $v) {
            $list3[$v['time']] = $v['cnt'];
        }
        $list3 = array_merge($defaultData, $list3);
        ksort($list3);
        // 接通量
        $sql4 = 'select count(*) as cnt,' . $groupBy . 'as time,count(if(call_status = 3,1,null)) as jt_cnt from ' . $this->def . 'staff_call_log where ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate) . ' group by ' . $groupBy;
        $data4 = $callLogM->getListBySql($sql4);
        $list4 = array();
        foreach ($data4 as $v) {
            if ($v['cnt'] > 0) {
                $list4[$v['time']] = $v['jt_cnt'];// 接通量
            }
        }
        $list4 = array_merge($defaultData, $list4);
        ksort($list4);
        echo json_encode(array('list1' => $list1, 'list2' => $list2, 'list3' => $list3, 'list4' => $list4));
        exit();
    }

    //下载录音
    function downloadRec_action(){
        $url = $this->config['sy_outcall_oss'] . $_GET['path'];
        $content = CurlGet($url);
//        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . 'record' . time() . '.wav');
        echo $content;
        exit();
    }

    /*
     * 统计页面拨打电话
     */
    function contactMobile_action(){
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
        $resumeM = $this->MODEL('resume');
        $memberWhere['moblie'] = $mobile;
        $memberInfo = $resumeM->getMemberInfo($memberWhere, array('field' => '`uid`,`moblie`,`usertype`'));
        $jumpTp = 0;
        $type = 0;
        $companyInfo = array();
        if (!$memberInfo) {// 未查到用户信息
            $jumpTp = 2;// 外呼成功后跳转到客户录入页
        } else {
            if ($memberInfo['usertype'] == 2) {
                $jumpTp = 1;// 呼叫成功后跳转到跟进页
                $type = 2;// 企业
                $comM = $this->MODEL('company');
                $comWhere['uid'] = $memberInfo['uid'];
                $companyInfo = $comM->getCompanyInfo($comWhere, array('field' => '`uid`,`linktel`,`name`'));
            } else if ($memberInfo['usertype'] == 1) {
                $type = 1;// 个人
            }
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
        file_put_contents(APP_PATH."new_call_log.txt",'客服外呼接口返回信息：('.date('Y-m-d H:i:s').')'.$res.";\n",FILE_APPEND);
        $result = json_decode($res, 1);
        $callLog = array(
            'uid' => $_SESSION['auid'],
            'username' => $adminInfo['name'],
            'session_id' => isset($result['data']['session_id']) ? $result['data']['session_id'] : '',
            'caller' => $params['caller'],
            'callee' => $params['callee'],
            'log_id' => isset($result['data']['log_id']) ? $result['data']['log_id'] : 0,// 综合平台外呼记录id
            'has_session' => isset($result['data']['has_session']) ? $result['data']['has_session'] : 0,// 是否有session_id
            'usertype' => $type,
            'cuid' => isset($memberInfo['uid']) && $memberInfo['uid'] ? $memberInfo['uid'] : 0// 被叫用户id
        );
        $callLog['api_res'] = json_encode(json_decode($res, 1), JSON_UNESCAPED_UNICODE);
        if ($result['code'] == 200) {// 呼叫成功
            $callLog['status'] = 1;
        } else {
            $callLog['status'] = 0;
        }
        $id = $staffCallM->addData($callLog);
        if ($result['code'] == 200) {
            echo json_encode(['code' => 200, 'msg' => '呼叫成功', 'data' => ['log_id' => $id ? $id : 0, 'jump_type' => $jumpTp, 'company' => $companyInfo]]);exit();
        } else {
            echo json_encode(['code' => 400, 'msg' => $result['message'], 'data' => ['log_id' => $id ? $id : 0, 'jump_type' => $jumpTp, 'company' => $companyInfo]]);exit();
        }
    }
}
?>
