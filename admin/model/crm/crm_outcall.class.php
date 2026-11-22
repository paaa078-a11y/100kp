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
class crm_outcall_controller extends adminCommon
{
    private $baseapi = 'https://u.phpyun.com';
    function getLogStaticData_action(){
        $AdminM = $this->MODEL('admin');
        // 获取所有业务员
        $adminUserList = $AdminM->getList(array('is_crm' => 1), array('field' => '`uid`,`name`,`username`'));

        $timeLength = array('1' => '大于30秒', '2' => '大于1分钟', '3' => '大于3分钟');
        $calllist = array('-1'=>'全部','1' => '主叫挂断', '2' => '主叫接通', '3' => '已接通');
        $search_list = array();
        $search_list[] = array('param'=>'time_length','name'=>'通话时长','value'=>$timeLength);
        $search_list[] = array('param'=>'call_status','name'=>'呼叫状态','value'=>$calllist);

        $data['adminUserList'] = $adminUserList;
        $data['search_list'] = $search_list;
        $data['contactOssUrl'] = $this->config['sy_outcall_oss'];
        $this->render_json(0, 'ok', $data);
    }
    function log_action()
    {
        $AdminM = $this->MODEL('admin');
        // 获取所有业务员
        $adminUserList = $AdminM->getList(array('is_crm' => 1), array('field' => '`uid`,`name`,`username`'));
        $uids = array();
        foreach ($adminUserList as $v) {
            $uids[] = $v['uid'];
        }

        $where = $urlarr = array();
        $staffcalllogM = $this->MODEL('staffcalllog');
        // 从crm工作台通话统计加载
        if (!empty($_POST['source']) && $_POST['source'] == 'jobtai') {
            $where['uid'] = intval($_SESSION['auid']); // 查自己的拨号记录
        } else {
            // 不传业务员id时，查自己及全部下属客户的外呼记录
            if (isset($_POST['crm_uid']) && $_POST['crm_uid']) {
                $where['uid'] = intval($_POST['crm_uid']);
            } else if ($uids){
                $where['uid'] = array('in', pylode(',', $uids));
            }
        }
        if (isset($_POST['s_time']) && $_POST['s_time']) {
            $where['PHPYUNBTWSTART_A'] = '';
            if (strpos($_POST['s_time'], '-')){// 开始时间非时间戳
                $stime = strtotime(trim($_POST['s_time']));
            } else {
                $stime = $_POST['s_time'];
            }
            $where['ctime'][] = array('>=', $stime, 'AND');
            if (!isset($_POST['e_time']) || !$_POST['e_time']) {
                $endTime = time();
            } else if (strpos($_POST['e_time'], '-')) {// 结束时间非时间戳
                $endTime = strtotime(trim($_POST['e_time']));
            }else {
                $endTime = $_POST['e_time'];
            }
            $where['ctime'][] = array('<=', $endTime, 'AND');
            $where['PHPYUNBTWEND_A'] = '';
        }
        if (isset($_POST['keyword']) && $_POST['keyword']) {
            $keyword = trim($_POST['keyword']);
            if ($_POST['crm_type'] == 2) {// 主叫
                $where['caller'] = array('like', $keyword);
            } else {// 被叫
                $where['callee'] = array('like', $keyword);
            }
        }
        if (isset($_POST['call_status']) && $_POST['call_status'] != -1) {
            $where['call_status'] = $_POST['call_status'];
        }

        // 通话时长筛选
        if (isset($_POST['time_length']) && $_POST['time_length'] == 1) {// 大于30秒
            $seconds = 30;
        } else if (isset($_POST['time_length']) && $_POST['time_length'] == 2) {// 大于1分钟
            $seconds = 60;
        } else if (isset($_POST['time_length']) && $_POST['time_length'] == 3) {// 大于3分钟
            $seconds = 180;
        }
        if (isset($seconds) && $seconds > 0) {
            $where['time_length'] = array('>', $seconds);
        }
        $where['source'] = 1;   //  新增

        $pageM  =   $this->MODEL('page');
        $pages = $pageM->adminPageList('staff_call_log', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list = [];
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'].','.$_POST['order'];
                $urlarr['order'] = $_POST['order'];
                $urlarr['t'] = $_POST['t'];
            } else {
                $where['orderby'] = 'ctime,desc';
            }
            $where['limit'] = $pages['limit'];
            $list = $staffcalllogM->getList($where);
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    function getStaticData_action(){

        $adminM = $this->MODEL('admin');
        $adminInfos = $adminM->getList(array('is_crm' => 1), array('field' => '`m_id`, `name`, `moblie`'));
        $groupArr = array();

        foreach ($adminInfos as $adminInfo) {
            $groupArr[$adminInfo['m_id']] = ['id' => $adminInfo['m_id'], 'name' => $adminInfo['group_name']];
        }

        $data = array(
            'user_group'=>$groupArr
        );
        $this->render_json(0, 'ok', $data);
    }

    /*
     * 获取云通讯绑定坐席图片验证码
     */
    public function getYtxImgCode_action(){
        $apiurl = $this->baseapi . '/imgcodeytx';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if ($appkey && $appsecret) {
            $params = array(
                'appKey' => $appkey,
                'appSecret' => $appsecret,
            );
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            if ($resArr['code'] == 200) {
                $this->render_json(0, $resArr['message'], $resArr['data']);
            } else {
                $this->render_json(1, '获取验证码失败');
            }
        }
    }

    /*
     * 获取云通讯绑定坐席短信验证码
     */
    public function getYtxMsgCode_action(){
        $apiurl = $this->baseapi . '/msgcodeytx';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if ($appkey && $appsecret) {
            $params = array(
                'appKey' => $appkey,
                'appSecret' => $appsecret,
                'mobile' => trim($_POST['mobile']),// 要绑定的手机号
                'imgcode' => trim($_POST['imgcode']),// 图片验证码
                'uuid' => trim($_POST['uuid']),
            );
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            if ($resArr['code'] == 200) {
                $this->render_json(0, '发送成功');
            } else {
                $this->render_json(1, $resArr['message'] ? $resArr['message'] : '发送失败');
            }
        }
    }

    /*
     * 坐席列表
     */
    function staffList_action(){
        $apiurl = $this->baseapi . '/stafflist';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $adminM = $this->MODEL('admin');
        $adminInfos = $adminM->getList(array('is_crm' => 1), array('field' => '`m_id`, `name`, `moblie`'));
        $mobileToName = array();

        foreach ($adminInfos as $adminInfo) {

            if ($adminInfo['moblie']) {
                $mobileToName[$adminInfo['moblie']] = $adminInfo['name'];
            }
        }
        $task = array();
        $total = 0;
        $params['perPage'] = isset($_POST['limit']) && $_POST['limit'] ? $_POST['limit'] : $this->config['sy_listnum'];
        $params['page'] = isset($_POST['page']) && $_POST['page'] > 1 ? $_POST['page'] : 1;
        $limit  =   $params['perPage'] == '' ? intval($this->config['sy_listnum']) : intval($params['perPage']);

        $keyword = trim($_POST['keyword']) ? trim($_POST['keyword']) : '';
        if ($appkey && $appsecret) {
            $params = array(
                'appKey' => $appkey,
                'appSecret' => $appsecret,
                'keyword' => $keyword
            );
            $params['perPage'] = isset($_POST['limit']) && $_POST['limit'] ? $_POST['limit'] : $this->config['sy_listnum'];
            $params['page'] = isset($_POST['page']) && $_POST['page'] > 1 ? $_POST['page'] : 1;
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            $task = $resArr['data'];
            $page   =   $_POST['page'] < 1 ? 1 : intval($_POST['page']);
            if ($task) {
                $total = $resArr['total'];
                $mpage = ceil($total/$limit); // 计算最后的页数
                $page > $mpage && $page = $mpage;
                foreach ($task as &$v) {
                    $v['name'] = isset($mobileToName[$v['mobile']]) ? $mobileToName[$v['mobile']] : '暂无业务员信息';
                }
            }
        }
        $deflimit = $this->config['sy_listnum'];
        $page_sizes = array(intval($deflimit),2*$deflimit,3*$deflimit,4*$deflimit);
        $data = array(
            'list'=>$task,
            'total'=> $total,
            'page_sizes'=>$page_sizes,
            'limit' => array(($page - 1) * $limit, $limit),
            'page' => $page,
        );
        $this->render_json(0, 'ok', $data);
    }

    /*
     * 获取云呼短信验证码
     */
    function getYhCode_action(){
        $apiurl = $this->baseapi . '/yhcode';
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
            $this->render_json(0, $resArr['message'], array('code' => $code));
        }
    }

    /*
     * 坐席配置
     */
    function getUsers_action(){
        $adminM = $this->MODEL('admin');
        $adminusers = $adminM->getList(array('is_crm' => 1, 'm_id' => $_POST['group_id']), array('field' => '`uid`, `name`, `moblie`'));
        $this->render_json(0, 'ok', array('users' => $adminusers));
    }

    /*
     * 绑定坐席
     */
    function bindStaff_action(){
        $apiurl = $this->baseapi . '/bindstaff';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            $this->render_json(1, '请先完成外呼配置');
        }
        if (!$_POST['staff_id']) {
            $this->render_json(1, '坐席id不能为空');
        }
        if (!trim($_POST['mobile'])) {
            $this->render_json(1, '绑定的手机号不能为空');
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
                $this->admin_json(0, '绑定成功');
            } else {
                $this->render_json(1, $resArr['message']);
            }
        }
    }

    /*
     * 重新发送坐席验证信息
     */
    function remsg_action(){
        $apiurl = $this->baseapi . '/resetstaffmsg';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            $this->render_json(1, '请先完成外呼配置');
        }
        if (!$_POST['staff_id']) {
            $this->render_json(1, '坐席id不能为空');
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
                $this->render_json(0, '发送成功，收到验证信息后，请尽快回复验证码内容进行坐席验证');
            } else {
                $this->render_json(1, $resArr['message']);
            }
        }
    }

    //外呼配置
    function getConfig_action(){
        $sy_outcall_appkey = $this->config['sy_outcall_appkey'];
        $sy_outcall_appsecret = $this->config['sy_outcall_appsecret'];
        $this->render_json(0, 'ok', compact('sy_outcall_appkey', 'sy_outcall_appsecret'));
    }

    //保存外呼配置
    function setConfig_action(){
        $configM = $this->MODEL('config');
        if($_POST['config']){
            unset($_POST['config']);
            $apiurl = $this->baseapi . '/getosspre';
            $params = array(
                'appKey' => trim($_POST['sy_outcall_appkey']),
                'appSecret' => trim($_POST['sy_outcall_appsecret'])
            );
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            if ($resArr['code'] != 200) {
                $this->render_json(1, $resArr['message']);
            }
            $_POST['sy_outcall_oss'] = $resArr['data'];
            $configM->setConfig($_POST);
            $this->web_config();
            $this->render_json(0, '外呼配置设置成功');
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
        $where['source'] = 1;// 1:crm,2:代招
        $allCnt = $callLogM->getCallNum($where);
        // call_status 0未知，1主叫挂断，2主叫接通，3已接通
        $jtWhere = $where;
        $jtWhere['call_status'] = 3;
        $jtCnt = $callLogM->getCallNum($jtWhere);// 接通量
        $wrWhere = $where;
        $wrWhere['call_status'] = 2;
        $wrCnt = $callLogM->getCallNum($wrWhere);// 被叫无人接听
        // 获取坐席今日外呼统计信息
        $sql = 'select sum(ceil(time_length/60)) as total_length from ' . $this->def .'staff_call_log where ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate) . ' and source = 1';
        $totalTime = $callLogM->getListBySql($sql, 1);
        $totalTime = isset($totalTime[0]['total_length']) && $totalTime[0]['total_length'] ? $totalTime[0]['total_length'] : 0;
        $this->render_json(0, 'ok', compact('allCnt', 'jtCnt', 'wrCnt', 'totalTime'));
    }

    /*
     * 数据看板
     */
    function outcall_statistics_action()
    {
        //  获取通话时长包信息
        $apiurl = $this->baseapi . '/staffbalance';
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
        $balanceArr = array('total_send' => $totalSend, 'total_personal' => $totalPersonal);
		$cacheM	=	$this   -> MODEL('cache');
		$cache	=	$cacheM->GetCache(array('crm'));
        $this->render_json(0, 'ok', compact('balanceArr', 'cache'));
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
        $countSql = 'select count(*) as num from (select uid from ' . $this->def .'staff_call_log where ctime >= ' . strtotime($sdate) . ' and ctime <='. strtotime($edate) .' and source = 1 group by uid) as tmp';
        $pageSize = !empty($_POST['limit']) ? intval($_POST['limit']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('staff_call_log', array(), $_POST['page'], array('limit' => $pageSize, 'sql'=>$countSql, 'maxPage' => true));
        extract($pages);
        $limit = $pageSize;
        $list = array();
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            $sql = "select uid,username,count(*) as total_cnt,sum(ceil(time_length/60)) as total_length,count(if(call_status = 3,1,null)) as jt_cnt,count(if(call_status = 2,1,null)) as wr_cnt from `" . $this->def ."staff_call_log` where ctime >= " . strtotime($sdate) . " and ctime <=". strtotime($edate) ." and source = 1 group by uid,username limit {$pages['limit'][0]},{$pages['limit'][1]}";
            $list = $callLogM->getListBySql($sql);

        }
        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    // 获取统计时间
    function day(){
        if ((int) $_POST['days'] > 0) {
            $days =	intval($_POST['days']);
            $sdate = date('Y-m-d', (time() - ($days - 1) * 86400));
            $edate = date('Y-m-d', time());
        } elseif ($_POST['sdate']) {
            $sdate = $_POST['sdate'];
            $days =	ceil(abs(time() - strtotime($sdate)) / 86400);
            if ($_POST['edate']) {
                $edate = $_POST['edate'];
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
        $sql = 'select count(*) as cnt,' . $groupBy . 'as time from ' . $this->def . 'staff_call_log where ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate) . ' and source = 1 group by ' . $groupBy;
        $data1 = $callLogM->getListBySql($sql, 1);
        $list1 = array();
        foreach ($data1 as $v) {
            $list1[$v['time']] = $v['cnt'];
        }
        $list1 = array_merge($defaultData, $list1);
        ksort($list1);
        // 业务电话
        $sql2 = 'select count(*) as cnt,' . $groupBy . 'as time from ' . $this->def . 'staff_call_log where usertype = 2 and ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate) . ' and source = 1 group by ' . $groupBy;
        $data2 = $callLogM->getListBySql($sql2);
        $list2 = array();
        foreach ($data2 as $v) {
            $list2[$v['time']] = $v['cnt'];
        }
        $list2 = array_merge($defaultData, $list2);
        ksort($list2);
        // 客勤电话
        $sql3 = 'select count(*) as cnt,' . $groupBy . 'as time from ' . $this->def . 'staff_call_log where usertype = 1 and ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate) . ' and source = 1 group by ' . $groupBy;
        $data3 = $callLogM->getListBySql($sql3);
        $list3 = array();
        foreach ($data3 as $v) {
            $list3[$v['time']] = $v['cnt'];
        }
        $list3 = array_merge($defaultData, $list3);
        ksort($list3);
        // 接通量
        $sql4 = 'select count(*) as cnt,' . $groupBy . 'as time,count(if(call_status = 3,1,null)) as jt_cnt from ' . $this->def . 'staff_call_log where ctime >= ' . strtotime($sdate) . ' and ctime <= ' . strtotime($edate) . ' and source = 1 group by ' . $groupBy;
        $data4 = $callLogM->getListBySql($sql4);
        $list4 = array();
        foreach ($data4 as $v) {
            if ($v['cnt'] > 0) {
                $list4[$v['time']] = $v['jt_cnt'];// 接通量
            }
        }
        $list4 = array_merge($defaultData, $list4);
        ksort($list4);
        $this->render_json(0, 'ok', compact('list1','list2','list3','list4'));
    }

    //下载录音
    function downloadRec_action(){
        $url = $this->config['sy_outcall_oss'] . $_GET['path'];
        $content = CurlGet($url);
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
            $this->render_json(1, '业务员未填写手机号');
        }
        $mobile = trim($_POST['mobile']);
        if (!$mobile || !CheckMobile($mobile)) {
            $this->render_json(1, '手机号格式错误');
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
        $apiurl = $this->baseapi . '/call';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            $this->render_json(1, '请先完成外呼配置');
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
        $data = array(
            'log_id'=> $id ? $id : 0,
            'jump_type'=> $jumpTp,
            'company'=> $companyInfo
        );
        if ($result['code'] == 200) {
            $this->render_json(0, '呼叫成功',$data);
        } else {
            $this->render_json(1, $result['message'],$data);
        }
    }
}
?>
