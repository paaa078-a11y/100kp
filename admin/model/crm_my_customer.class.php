<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2022 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class crm_my_customer_controller extends adminCommon
{

    function set_search()
    {

        $cacheM     =   $this->MODEL('cache');
        $crmCache   =   $cacheM->GetCache(array('crm'));
        $this->yunset(array('cache' => $crmCache, 'crmClassName' => $crmCache['crmclass_name'], 'crmStatus' => $crmCache['crmdata']['client_status'], 'crmType' => $crmCache['crmdata']['client_type']));
        $ratingM    =   $this->MODEL('rating');
        if (!empty($this->config['com_rating'])) {
            $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`', 'rating' => $this->config['com_rating']));
        } else {
            $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
        }
        $vipEtime   =   array('5' => '已到期', '1' => '三天内', '2' => '七天内', '3' => '十五天内', '4' => '一月内');
        $yyzz       =   array('1' => '认证通过', '2' => '等待审核', '3' => '认证失败', '4' => '尚未认证');
        $lastFtime  =   array('1' => '从未跟进', '2' => '今日跟进', '3' => '三天前', '4' => '七天前', '5' => '一月前', '6' => '百日前');
        $nextFtime  =   array('1' => '今天', '2' => '明天', '3' => '三天内', '4' => '七天内', '5' => '一月内');
        $loginDate  =   array('1' => '今天', '2' => '本周', '3' => '本月', '4' => '一个月前', '5' => '三个月前', '6' => '半年前', '7' => '自定义');

        $hasJob     =   array('1' => '是', '2' => '否');
        $this->yunset(array('yyzzStatus' => $yyzz, 'ratingArr' => $ratingArr, 'vipEtime' => $vipEtime, 'lastFtime' => $lastFtime, 'nextFtime' => $nextFtime, 'loginDate' => $loginDate, 'hasJob' => $hasJob));
        $this->yunset('todayStart', strtotime('today'));
    }

    /**
     * @desc - CRM - 我的客户
     */
    function index_action()
    {

        $this->set_search();

        $comM       =   $this->MODEL('company');
        $userInfoM  =   $this->MODEL('userinfo');
        $crmM       =   $this->MODEL('crm');

        $month 	= 	get_this_month(time());

        session_start();
        if (isset($_GET['recrm_prev']) && $_GET['recrm_prev']) {
            foreach ($_SESSION['prev_customerlist'] as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_SESSION['prev_customerlist'] = $_GET;

        $urlarr             =   $_GET;
        $where['crm_uid']   =   $_SESSION['auid'];

        //判断是否有职位
        if (isset($_GET['hasJob'])) {
            if ($_GET['hasJob'] == '1') {
                $where['jobtime'] = array('>', 0);
            } elseif ($_GET['hasJob'] == '2') {
                $where['jobtime'] = 0;
            }
            $urlarr['hasJob'] = $_GET['hasJob'];
        }

        if (isset($_GET['jbtime']) && !empty($_GET['jbtime'])){    //  CRM - 业务员 - 工作台 - 我的简报：传参

            $jbTime         =   (int)$_GET['jbtime'];               //  1-今日；2-昨日；3-本周；4-本月

            if ($jbTime == 1) {

                $where['crm_time']          =   array('>=', strtotime(date('Y-m-d')));
            } elseif ($jbTime == 2) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['crm_time'][]        =   array('>=', strtotime('yesterday'), '');
                $where['crm_time'][]        =   array('<', strtotime(date('Y-m-d')), '');
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif ($jbTime == 3) {

                $sDate  =   strtotime(date('Y-m-d', strtotime("this week Monday", time())));
                $eDate  =   strtotime(date('Y-m-d', strtotime("this week Sunday", time()))) + 24 * 3600 - 1;

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['crm_time'][]        =   array('>=', $sDate, '');
                $where['crm_time'][]        =   array('<', $eDate, '');
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif ($jbTime == 4) {

                $sDate  =   mktime(0, 0, 0, date('m'), 1, date('Y'));
                $eDate  =   mktime(23, 59, 59, date('m'), date('t'), date('Y'));

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['crm_time'][]        =   array('>=', $sDate, '');
                $where['crm_time'][]        =   array('<', $eDate, '');
                $where['PHPYUNBTWEND_A']    =   '';
            }

            $urlarr['jbtime']               =   $_GET['jbtime'];
        }

        if (isset($_GET['kh']) && !empty($_GET['kh'])){ //  CRM - 业务员 - 我的客户 - Tab栏：传参

            $kh =   intval($_GET['kh']);                //  1-联系中；2-今日已跟进；3-三十天未联系；4-从未联系；5-十五天内会员到期；6-登录客户

            if ($kh == 1){

                $where['isfollow']          =   1;
            } elseif ($kh == 2){

                $where['isfollow']          =   1;
                $where['f_time']            =   array('>=', strtotime(date('Y-m-d')));
            } elseif ($kh == 3){

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-30 days'));
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif ($kh == 4){

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['isfollow']          =   array('=', 0, 'OR');
                $where['f_time']            =   array('=', '', 'OR');
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif ($kh == 5){

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['vipetime'][]        =   array('>', time(), 'AND');
                $where['vipetime'][]        =   array('<', strtotime('+15 days'), 'AND');
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif ($kh == 10){

                $where['clue']              =   2;
            }

            $urlarr['kh']                   =   $kh;
        }

        if (isset($_GET['crm_status']) && !empty($_GET['crm_status'])) {

            $crm_status             =   intval($_GET['crm_status']);
            $where['crm_status']    =   $crm_status;
            $urlarr['crm_status']   =   $crm_status;
        }

        if (isset($_GET['crmType']) && !empty($_GET['crmType'])) {

            $crmType                =   intval($_GET['crmType']);
            $where['crm_type']      =   $crmType;
            $urlarr['crmType']      =   $crmType;
        }

        if (isset($_GET['rating']) && $_GET['rating'] != '') {

            $rating                 =   intval($_GET['rating']);
            if($_GET['rating'] > 0){

                $where['rating']        =   $rating;
                if (!isset($where['vipetime'])){

                    $where['PHPYUNBTWSTART_B']  =   '';
                    $where['vipetime'][]        =   array('=', 0, '');
                    $where['vipetime'][]        =   array('>', strtotime('today'), 'OR');
                    $where['PHPYUNBTWEND_B']    =   '';
                }

                if (isset($_GET['vipstime']) && $_GET['vipstime'] != ''){

                    $where['PHPYUNBTWSTART_B1']  =   '';
                    $where['vipstime'][]  =   array('>', 0, '');
                    $where['vipstime'][]  =   array('<', strtotime('-'.$_GET['vipstime'].' days'), '');
                    $where['PHPYUNBTWEND_B1']  =   '';

                    $where['crm_time']  =   array('<', strtotime('-'.$_GET['vipstime'].' days'), '');
                }
            }else{

                if (isset($_GET['vipstime']) && $_GET['vipstime'] != ''){

                    $where['PHPYUNBTWSTART_B1'] =   '';
                    $where['vipetime'][]  =   array('>', 0, '');
                    $where['vipetime'][]  =   array('<', strtotime('-'.$_GET['vipstime'].' days'), '');
                    $where['PHPYUNBTWEND_B1']   =   '';

                    $where['crm_time']          =   array('<', strtotime('-'.$_GET['vipstime'].' days'), '');
                }else{
                    $where['PHPYUNBTWSTART_B']  =   '';
                    $where['vipetime']          =   array(
                        0   =>  array('<', strtotime('today'), ''),
                        1   =>  array('>', 0, '')
                    );
                    $where['PHPYUNBTWEND_B']    =   '';
                }
            }

            $urlarr['rating']       =   $rating;
        }

        if (isset($_GET['vipetime']) && !empty($_GET['vipetime'])) {

            $etime  =   intval($_GET['vipetime']);

            if ($etime == 5) {

                $where['PHPYUNBTWSTART_C']  =   '';
                $where['vipetime'][]        =   array('<', strtotime(date('Y-m-d')), 'AND');
                $where['vipetime'][]        =   array('>', '0', 'AND');
                $where['PHPYUNBTWEND_C']    =   '';
            } else {
                if ($etime == 1) {

                    $num    =   '+3 days';
                } else if ($etime == 2) {

                    $num    =   '+7 days';
                } elseif ($etime == 3) {

                    $num    =   '+15 days';
                } elseif ($etime == 4) {

                    $num    =   '+1 month';
                }

                $where['PHPYUNBTWSTART_C']  =   '';
                $where['vipetime'][]        =   array('>', time(), 'AND');
                $where['vipetime'][]        =   array('<', strtotime($num), 'AND');
                $where['PHPYUNBTWEND_C']    =   '';
            }
            $urlarr['vipetime'] =   $etime;
        }

        if (isset($_GET['yyzz_status']) && !empty($_GET['yyzz_status'])) {

            $yyzz_status    =   intval($_GET['yyzz_status']);

            if ($yyzz_status == 1) {

                $where['yyzz_status']   =   $yyzz_status;
            } else {

                $where['yyzz_status']   =   0;

                $certWhere      =   array();

                if ($yyzz_status == 2) {

                    $certWhere  =   array('type' => 3, 'status' => 0);
                } elseif ($yyzz_status == 3) {

                    $certWhere  =   array('type' => 3, 'status' => 3);
                }elseif ($yyzz_status == 4) {

                    $certWhere  =   array('type' => 3);
                }

                $certList       =   $comM->getCertList($certWhere, array('field' => '`uid`'));
                $uidArrA        =   array();
                foreach ($certList as $cv) {

                    $uidArrA[]  =   $cv['uid'];
                }

                if ($yyzz_status == 4){

                    $where['uid']   =   array('notin', pylode(',', $uidArrA));
                }else{

                    $where['uid']   =   array('in', pylode(',', $uidArrA));
                }
            }

            $urlarr['yyzz_status']  =   $yyzz_status;
        }

        if (isset($_GET['lastF']) && !empty($_GET['lastF'])) {

            $lastF = intval($_GET['lastF']);
            $where['PHPYUNBTWSTART_D'] = '';
            $where['f_time'][] = array('isnull', '', '');
            $where['f_time'][] = array('<', strtotime('-' . $lastF . ' days'), 'OR');
            $where['PHPYUNBTWEND_D'] = '';
            $urlarr['lastF'] = $lastF;
        }

        if (isset($_GET['lastFtime']) && !empty($_GET['lastFtime'])) {

            $lastFtime  =   intval($_GET['lastFtime']);

            if ($lastFtime == 1) {
                $where['PHPYUNBTWSTART_E']  =   '';
                $where['isfollow']          =   array('=', '0', 'OR');
                $where['f_time']            =   array('=', '', 'OR');
                $where['PHPYUNBTWEND_E']    =   '';
            } elseif ($lastFtime == 2) {
                $where['f_time']            =   array('>', strtotime(date('Y-m-d')));
            } elseif ($lastFtime == 3){
                $where['PHPYUNBTWSTART_E']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-3 days'));
                $where['PHPYUNBTWEND_E']    =   '';
            }elseif ($lastFtime == 4){
                $where['PHPYUNBTWSTART_E']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-7 days'));
                $where['PHPYUNBTWEND_E']    =   '';
            }elseif ($lastFtime == 5){
                $where['PHPYUNBTWSTART_E']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-30 days'));
                $where['PHPYUNBTWEND_E']    =   '';
            }elseif ($lastFtime == 6){
                $where['PHPYUNBTWSTART_E']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-100 days'));
                $where['PHPYUNBTWEND_E']    =   '';
            }

            $urlarr['lastFtime']            =   $lastFtime;
        }

        if (isset($_GET['nextFtime']) && !empty($_GET['nextFtime'])) {

            $nextFtime  =   intval($_GET['nextFtime']);

            $taskWhere  =   array('type' => 22, 'status' => 1);

            if ($nextFtime == 1) {

                $taskWhere['PHPYUNBTWSTART_F']  =   '';
                $taskWhere['stime'][]           =   array('<', strtotime(date('Y-m-d 23:59:59')));
                $taskWhere['stime'][]           =   array('>', 0);
                $taskWhere['PHPYUNBTWEND_F']    =   '';
            } elseif ($nextFtime == 2) {

                $taskWhere['PHPYUNBTWSTART_F']  =   '';
                $taskWhere['stime'][]           =   array('>', strtotime(date('Y-m-d 23:59:59')));
                $taskWhere['stime'][]           =   array('<', strtotime(date('Y-m-d 23:59:59')) + 86400);
                $taskWhere['PHPYUNBTWEND_F']    =   '';
            } elseif ($nextFtime == 3) {

                $taskWhere['PHPYUNBTWSTART_F']  =   '';
                $taskWhere['stime'][]           =   array('>', strtotime('today'));
                $taskWhere['stime'][]           =   array('<', strtotime('+3 day'));
                $taskWhere['PHPYUNBTWEND_F']    =   '';
            } elseif ($nextFtime == 4) {

                $taskWhere['PHPYUNBTWSTART_F']  =   '';
                $taskWhere['stime'][]           =   array('>', strtotime('today'));
                $taskWhere['stime'][]           =   array('<', strtotime('+1 week'));
                $taskWhere['PHPYUNBTWEND_F']    =   '';
            } elseif ($nextFtime == 5) {

                $taskWhere['PHPYUNBTWSTART_F']  =   '';
                $taskWhere['stime'][]           =   array('>', strtotime('today'));
                $taskWhere['stime'][]           =   array('<', strtotime('+1 month'));
                $taskWhere['PHPYUNBTWEND_F']    =   '';
            }

            $taskWhere['uid']   =   $_SESSION['auid'];
            $taskList           =   $crmM->getTaskList($taskWhere, array('field' => '`comid`'));
            $uidArrN            =   array();
            foreach ($taskList as $tv) {
                $uidArrN[]      =   $tv['comid'];
            }
            $where['uid']       =   array('in', pylode(',', $uidArrN));
        }

        if (isset($_GET['loginDate']) && !empty($_GET['loginDate'])) {

            $loginDate = intval($_GET['loginDate']);
            if ($loginDate == 1) {

                $where['login_date'] = array('>=', strtotime('today'));
            } else if ($loginDate == 2) {

                $where['login_date'] = array('>=', strtotime('this week 00:00:00'));
            } else if ($loginDate == 3) {

                $where['PHPYUNBTWSTART_C1'] = '';
                $where['login_date'][] = array('>=', $month['start_month']);
                $where['login_date'][] = array('<=', $month['end_month']);
                $where['PHPYUNBTWEND_C1'] = '';
            } else if ($loginDate == 4) {

                $where['login_date'] = array('<', strtotime('-30 days'));
            } else if ($loginDate == 5) {

                $where['login_date'] = array('<', strtotime('-3 month'));
            } else if ($loginDate == 6) {

                $where['login_date'] = array('<', strtotime('-6 month'));
            } else if ($loginDate == 7) {
                if (isset($_GET['loginTime']) && !empty($_GET['loginTime'])){

                    $loginTimeArr   =   explode('至', $_GET['loginTime']);
                    $loginStart     =   $loginTimeArr[0];
                    $loginEnd       =   $loginTimeArr[1];
                    $where['PHPYUNBTWSTART_C1'] = '';
                    $where['login_date'][] = array('>=', strtotime($loginStart.' 00:00:00'));
                    $where['login_date'][] = array('<=', strtotime($loginEnd.' 23:59:59'));
                    $where['PHPYUNBTWEND_C1'] = '';

                    $urlarr['loginTime']    =   $_GET['loginTime'];
                }
            }

            $urlarr['loginDate'] = $loginDate;
        }

        if (isset($_GET['regTime']) && !empty($_GET['regTime'])) {

            $regTimeArr =   explode('至', $_GET['regTime']);

            $regStart   =   strtotime($regTimeArr[0].' 00:00:00');
            $regEnd     =   strtotime($regTimeArr[1].' 23:59:59');

            $mWhere['PHPYUNBTWSTART_H'] =   '';
            $mWhere['reg_date'][]       =   array('>', $regStart, 'AND');
            $mWhere['reg_date'][]       =   array('<', $regEnd, 'AND');
            $mWhere['PHPYUNBTWEND_H']   =   '';
            $memberList =   $userInfoM->getList($mWhere, array('field' => 'uid'));

            $uidArrB    =   array();
            foreach ($memberList as $mv) {
                $uidArrB[]  =   $mv['uid'];
            }

            $where['uid']       =   array('in', pylode(',', $uidArrB));
            $urlarr['regTime']  =   $_GET['regTime'];
        }

        if (isset($_GET['keyword']) && $_GET['keyword']) {

            $keywordStr =   trim($_GET['keyword']);
            $typeStr    =   intval($_GET['crm_type']);

            if (!empty($keywordStr)) {
                if ($typeStr == 1) {

                    $where['name']      =   array('like', $keywordStr);
                } else if ($typeStr == 2) {

                    $where['linkman']   =   array('like', $keywordStr);
                } else if ($typeStr == 3) {

                    $where['linktel']   =   $keywordStr;
                } else if ($typeStr == 4) {

                    $where['uid']       =   $keywordStr;
                }
            }

            $urlarr['crm_type'] =   $typeStr;
            $urlarr['keyword']  =   $keywordStr;
        }

        if(isset($_GET['r_status']) && !empty($_GET['r_status'])){
            $where['r_status']  =   $_GET['r_status'];
        }

        if (isset($_GET['following']) && !empty($_GET['following'])){

            $taskWhere  =   array('type' => 22, 'status' => 1);
            $taskWhere['PHPYUNBTWSTART_I']  =   '';
            $taskWhere['stime'][]   = array('<', strtotime(date('Y-m-d 23:59:59')));
            $taskWhere['stime'][]   = array('>', 0);
            $taskWhere['PHPYUNBTWEND_I']    =   '';
            $taskWhere['uid']   = $_SESSION['auid'];
            $taskList   =   $crmM->getTaskList($taskWhere, array('field' => '`comid`'));
            $uidArrF    =   array();
            foreach ($taskList as $tv) {
                $uidArrF[]  =   $tv['comid'];
            }
            $where['uid']   =   array('in', pylode(',', $uidArrF));
        }

        if (isset($_GET['vip']) && !empty($_GET['vip'])){

            $where['PHPYUNBTWSTART_A1']  =   '';
            $where['rating'][]          =   array('>', 0, '');
            $where['rating'][]          =   array('in', $this->config['crm_deal_rating'], '');
            $where['PHPYUNBTWEND_A1']    =   '';
            $where['PHPYUNBTWSTART_B1']  =   '';
            $where['vipetime'][]        =   array('=', 0, '');
            $where['vipetime'][]        =   array('>', strtotime('today'), 'OR');
            $where['PHPYUNBTWEND_B1']    =   '';
        }

        $urlarr['page'] =   '{{page}}';

        if (isset($urlarr['recrm_prev'])) {
            unset($urlarr['recrm_prev']);
        }

        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');
        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->pageList('company', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            if (isset($_GET['order'])) {
                if (isset($_GET['t'])) {

                    $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                    $urlarr['t']        =   $_GET['t'];
                } else {

                    $where['orderby']   =   'crm_time,desc';
                }

                $urlarr['order']        =   $_GET['order'];
            } else if (isset($order)) {

                $where['orderby']       =   $order . ',DESC';
            }else{

                $where['orderby']       =    'crm_time,DESC';
            }

            $where['limit'] =   $pages['limit'];
            $listA          =   $comM->getList($where, array('utype' => 'crm', 'self' => 1));

            foreach ($listA['list']  as $key => $val){
                $listA['list'][$key]['wxBindmsg'] = $this->wxBindState($val);
            }

            $this->yunset(array('rows' => $listA['list'], 'auid' => intval($_SESSION['auid'])));
        }

        $AdminM         =   $this->MODEL('admin');

        $adminUserList  =   $AdminM->getList(array(), array('field' => '`uid`,`name`'));
        $this->yunset('adminUserList', $adminUserList);

        $crmUser        =   $AdminM->getList(array('is_crm' => '1', 'uid' => array('<>', $_SESSION['auid'])));
        $this->yunset(array('crmUser' => $crmUser));

        $now_url    =   $this->config['sy_weburl'].$_SERVER["REQUEST_URI"];
        $this->yunset("lasturl",$now_url);

        $appkey     =   trim($this->config['sy_outcall_appkey']);
        $appsecret  =   trim($this->config['sy_outcall_appsecret']);
        $isOutcall  =   $appkey && $appsecret;
        $this->yunset('is_outcall', $isOutcall);// 是否配置外呼

        $this->yuntpl(array('admin/crm_customer'));
    }

    /**
     * @desc CRM-我的客户（数量统计）
     */
    function getComNum_action()
    {

        $crmM   =   $this->MODEL('crm');
        echo $crmM->getCustomerNum(array('type' => 1, 'crm_uid' => $_SESSION['auid']));
    }

    /**
     * @desc CRM - 我的客户 - 温馨提示
     */
    function ajaxCrmSetData_action()
    {
        $crmM   =   $this->MODEL('crm');
        $return =   $crmM->getMyCom($_SESSION['auid']);
        echo $return;
        die;
    }

    /**
     * @desc CRM - 我的客户（虚拟号联系客户）
     */
    function contactCustomer_action()
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
        if (!$_POST['uid']) {
            echo json_encode(['code' => 400, 'msg' => '企业uid不能为空']);exit;
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
//        file_put_contents(APP_PATH."new_call_log.txt",'客服外呼接口返回信息：('.date('Y-m-d H:i:s').')'.$res.";\n",FILE_APPEND);
        $result = json_decode($res, 1);
        $callLog = array(
            'uid' => $_SESSION['auid'],
            'username' => $adminInfo['name'],
            'session_id' => isset($result['data']['session_id']) ? $result['data']['session_id'] : '',
            'caller' => $params['caller'],
            'callee' => $params['callee'],
            'log_id' => isset($result['data']['log_id']) ? $result['data']['log_id'] : 0,// 综合平台外呼记录id
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

    /**
     * @desc CRM - 录客户
     */
    function add_action()
    {

        if ($_POST) {

            $clue   =   intval($_POST['clue']);

            $username   =   trim($_POST['moblie']);

            $time   =   time();
            $ip     =   fun_ip_get();
            // 密码为空的时候 随机一个密码
            if (empty($_POST['password'])) {
//                    $_POST['password']  =   '123456';
                $_POST['password']=   rand(111111, 999999);
            }
            if ($clue == 2) {
                if (empty($username)) {
                    $username  =   'clue_'.time();
                }
            }
            $pass   =   $_POST['password'];

            $crmUid =   isset($_POST['crm_uid']) && !empty($_POST['crm_uid']) ? $_POST['crm_uid'] : $_SESSION['auid'];

            $userInfoM  =   $this->MODEL('userinfo');

            if ($clue == 1) {

                if ($_POST['password'] == '' || mb_strlen($_POST['password']) < 6 || mb_strlen($_POST['password']) > 20) {

                    echo json_encode(array('errcode' => 8, 'msg' => '登录密码请控制在6-20位字符'));
                    exit();
                }

                $mPost  =   array(

                    'username'      =>  $username,
                    'companyName'   =>  trim($_POST['name']),
                    'moblie'        =>  trim($_POST['moblie']),
                    'email'         =>  trim($_POST['email'])
                );

                $result     =   $userInfoM->addMemberCheck($mPost);
                if ($result['msg']) {

                    echo json_encode(array('errcode' => 8, 'msg' => $result['msg']));
                    exit();
                }

                if ($this->config['sy_uc_type'] == 'uc_center') {
                    $this->obj->uc_open();
                    $user = uc_get_user($username);
                    if (is_array($user)) {

                        echo json_encode(array('errcode' => 8, 'msg' => '该会员已经存在！'));
                        exit();
                    }
                }

                $password = $salt = '';
                if ($this->config['sy_uc_type'] == 'uc_center') {

                    $uid    =   uc_user_register($username, $pass, $_POST['email']);

                    if ($uid < 0) {
                        switch ($uid) {
                            case '-1' :
                                $data['msg'] = '用户名不合法!';
                                break;
                            case '-2' :
                                $data['msg'] = '包含不允许注册的词语!';
                                break;
                            case '-3' :
                                $data['msg'] = '用户名已经存在!';
                                break;
                            case '-4' :
                                $data['msg'] = 'Email 格式有误!';
                                break;
                            case '-5' :
                                $data['msg'] = 'Email 不允许注册!';
                                break;
                            case '-6' :
                                $data['msg'] = '该 Email 已经被注册!';
                                break;
                        }

                        echo json_encode(array('errcode' => 8, 'msg' => $data['msg']));
                        exit();
                    } else {

                        list($uid, $username, $email, $password, $salt) = uc_get_user($_POST['email'], $pass);
                    }
                } else {

                    $pwdRes     =   $userInfoM->generatePwd(array('password' => $pass));

                    $salt       =   $pwdRes['salt'];
                    $password   =   $pwdRes['pwd'];
                }

            } else if ($clue == 2) {

                $pwdRes =   $userInfoM->generatePwd(array('password' => $pass));

                $salt       =   $pwdRes['salt'];
                $password   =   $pwdRes['pwd'];
            }

            $mdata  =   array(

                'username'  =>  $username,
                'password'  =>  $password,
                'usertype'  =>  2,
                'salt'      =>  $salt,
                'address'   =>  $_POST['address'],
                'moblie'    =>  $_POST['moblie'],
                'email'     =>  $_POST['email'],
                'reg_date'  =>  $time,
                'reg_ip'    =>  $ip,
                'source'    =>  '16',
                'status'    =>  1
            );

            $udata  =   array(

                'name'          =>  $_POST['name'],

                'hy'            =>  $_POST['hy'],
                'pr'            =>  $_POST['pr'],
                'mun'           =>  $_POST['mun'],

                'provinceid'    =>  $_POST['provinceid'],
                'cityid'        =>  $_POST['cityid'],
                'three_cityid'  =>  $_POST['three_cityid'],
                'address'       =>  $_POST['address'],

                'content'       =>  $_POST['content'],

                'linkman'       =>  $_POST['linkman'],
                'linktel'       =>  $_POST['moblie'],
                'linkphone'     =>  $_POST['linkphone'],
                'linkmail'      =>  $_POST['email'],

                'r_status'      =>  $_POST['r_status'],

                'clue'          =>  $clue
            );

            $udata['crm_uid']   =   $crmUid;
            $udata['crm_time']  =   $time;
            $udata['crm_source']=   3;
            $udata['crm_type']  =   $_POST['crm_type'];
            $udata['crm_status']=   $_POST['crm_status'];

            $sdata  =   array(

                'rating'    =>  $this->config['com_rating']
            );

            $nid    =   $userInfoM->addInfo(array('mdata' => $mdata, 'udata' => $udata, 'sdata' => $sdata));

            if ($nid) {

                $crmM   =   $this->MODEL('crm');

                $logData        =   array(
                    'auid'      =>  $_SESSION['auid'],
                    'uid'       =>  $nid,
                    'type'      =>  10,
                    'content'   =>  '新客户录入，UID：'.$nid,
                    'ctime'     =>  time()
                );

                if ($crmUid != $_SESSION['auid']){

                    $logData['remark']  =   '【协助录入】';
                }
                if ($clue == 2){
                    $logData['remark']  .=  '【线索录入】';
                }
                $crmM->addCrmComLog($logData);

                if (!empty($_POST['rating'])) {

                    $dealData   =   array(

                        'uid'           =>  $nid,
                        'rating'        =>  intval($_POST['rating']),
                        'auid'          =>  $_SESSION['auid'],
                        'type'          =>  1,
                        'order_remark'  =>  '新客户录入',
                        'crm_uid'       =>  $crmUid
                    );

                    $ratingM    =   $this->MODEL('rating');
                    $ratingPrice=   $ratingM->getRatingPrice(array('id' => $_POST['rating']));
                    $dealData['order_price']    =   $ratingPrice;

                    $crmM->addDeal($dealData, 0);
                }

                $cacheM     =   $this->MODEL('cache');
                $crmCache   =   $cacheM->GetCache(array('crm'));

                if ($clue == 1){

                    $lContent   =   "录入新客户：".$_POST['name'];
                }else if ($clue == 2){

                    $lContent   =   "录入新线索";
                }

                if ($_POST['crm_type']) {

                    $lContent   .=  "，标记客户等级为：".$crmCache['crmclass_name'][$_POST['crm_type']];
                }
                if ($_POST['crm_status']) {
                    $lContent   .=  "，标记客户状态为：".$crmCache['crmclass_name'][$_POST['crm_status']];
                }

                // 添加CRM操作记录；
                $crmM->addCrmLog($lContent, 1, $nid, $crmUid);
            }

            $msg    =   $nid ? '录入成功！' : '录入失败，请重试！';

            $return =   array(
                'errcode'   =>  $nid ? 9 : 8,
                'msg'       =>  $msg
            );
            echo json_encode($return);exit();
        }

        $ratingM    =   $this->MODEL('rating');
        $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort,asc'), array('field' => '`id`,`name`'));
        $this->yunset('rating_list', $rating);

        $cacheM     =   $this->MODEL('cache');
        $options    =   array('crm', 'hy', 'city', 'com');
        $cache      =   $cacheM->GetCache($options);
        $this->yunset('cache', $cache);

        $adminM     =   $this->MODEL('admin');
        $crmUser    =   $adminM->getList(array('is_crm' => '1', 'uid' => array('<>', $_SESSION['auid'])));
        $this->yunset(array('crmUser' => $crmUser));

        $mobile = isset($_GET['mobile']) && $_GET['mobile'] ? trim($_GET['mobile']) : '';
        $this->yunset('mobile', $mobile);
        $this->yuntpl(array('admin/crm_customer_add'));
    }

    /**
     * @desc CRM - 新增客户 - 用户名检测
     */
    function check_action()
    {

        $moblie   =   trim($_POST['moblie']);
        $userInfoM  =   $this->MODEL('userinfo');
        $check      =   $userInfoM->addMemberCheck(array('moblie' => $moblie));

        echo $check['msg'];
        die;
    }

    /**
     * @desc CRM - 新增客户 - 客户名称提示
     */
    function checkComName_action()
    {

        $comM = $this->MODEL('company');

        $coms = array();

        if (!empty($_POST['companyName'])) {
            $coms = $comM->getKhList(array('name' => array('like', $_POST['companyName'])));
        }
        echo json_encode($coms);
        die;
    }

    /**
     * @desc 权益提醒记录
     */
    function notice_action()
    {

        $where      =   array('crm_uid' => $_SESSION['auid']);

        if (isset($_GET['keyword']) && $_GET['keyword']) {

            $keywordStr =   trim($_GET['keyword']);
            $typeStr    =   intval($_GET['crm_type']);

            if (!empty($keywordStr)) {
                if ($typeStr == 1) {

                    $comList    =   $this->obj->select_all('company', array('name' => array('like', $keywordStr), 'limit' => 500), '`uid`');

                    $comIdArr   =   array();

                    foreach ($comList as $ck => $cv){

                        $comIdArr[] =   $cv['uid'];
                    }

                    $where['uid']   =   array('in', pylode(',', $comIdArr));
                } else if ($typeStr == 2) {

                    $where['uid']   =   $keywordStr;
                }
            }

            $urlarr['crm_type'] =   $typeStr;
            $urlarr['keyword']  =   $keywordStr;
        }

        if (!empty($_GET['send_time'])){

            $timeArr    =   explode('~', $_GET['send_time']);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['send_time'][]       =   array('>=', $timeArr[0], '');
            $where['send_time'][]       =   array('<=', $timeArr[1], '');
            $where['PHPYUNBTWEND_A']    =   '';

            $urlarr['send_time']    =   $_GET['send_time'];
        }

        $urlarr['page'] =   '{{page}}';
        $urlarr['c']    =   'notice';

        $pageurl    =   Url($_GET['m'], $urlarr, 'admin');
        $pageM      =   $this->MODEL('page');
        $pages      =   $pageM->pageList('rating_notice', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'].', '.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   array('status, asc', 'send_time, desc');
            }

            $where['limit'] =   $pages['limit'];

            $crmM   =   $this->MODEL('crm');
            $list   =   $crmM->getNoticeList($where, array('utype' => 'crm'));
            $this->yunset(array('rows' => $list));

            $cacheM     =   $this->MODEL('cache');
            $crmCache   =   $cacheM->GetCache(array('crm'));
            $this->yunset(array('cache' => $crmCache));

            $now_url    =   $this->config['sy_weburl'].$_SERVER["REQUEST_URI"];
            $this->yunset("lasturl",$now_url);
        }

        $this->yuntpl(array('admin/crm_notice'));
    }

    /**
     * @desc 权益通知数量
     */
    function getNoticeNum_action()
    {

        $crmM   =   $this->MODEL('crm');
        echo $crmM->getNoticeNum(array('crm_uid' => $_SESSION['auid']));
    }

    /**
     * @desc 删除权益提醒记录
     */
    function delNotice_action()
    {

        $del    =   $_GET['del'];

        if (is_array($del)) {

            $where['id']            =   array('in', pylode(',', $del));
            $return['layertype']    =   1;
        } else {

            $where['id']            =   $del;
            $return['layertype']    =   0;
        }

        $crmM   =   $this->MODEL('crm');
        $return =   $crmM->delNotice($where);

        if ($return['errcode'] == '9') {

            $this->MODEL('log')->addAdminLog("删除权益提醒（ID：".$del."）");
        }

        $this->layer_msg($return['errmsg'], $return['errcode'], $return['layertype'], 'index.php?m=crm_my_customer&c=notice');
    }

    /**
     * @desc CRM - 客户管理
     */
    function com_action()
    {

        if ($_GET['id']) {

            $comId  =   intval($_GET['id']);

            $crmM   =   $this->MODEL('crm');
            $manageD=   $crmM->isManaged(array('uid' => $comId, 'auid' => $_SESSION['auid']));

            if ($manageD['code'] == 2) {
                $this->ShellMsg("暂无管理权限！");
            }
            $this->yunset('auid', $_SESSION['auid']);
        }

        $this->yunset('contactOssUrl', $this->config['sy_outcall_oss']);
        $this->yuntpl(array('admin/crm_com_info'));
    }

    /**
     * @desc 客户管理 - 基本信息
     */
    function getComInfo_action()
    {

        if ($_POST['id']){

            $comId = $_POST['id'];

            $crmM       =   $this->MODEL('crm');
            $comInfo    =   $crmM->getComInfo($comId, array('auid' => $_SESSION['auid']));
            $comLinks   =   $crmM->getCrmLink(array('uid' => $comId));
            include (CONFIG_PATH.'db.data.php');

            $cacheM     =   $this->MODEL('cache');
            $Cache      =   $cacheM->GetCache(array('crm', 'user'));
            $className  =   $Cache['crmclass_name'];

            $followWay  =   $taskType   =   $clientType =   $clientStatus   =   array();

            foreach ($Cache['crmdata']['follow_way'] as $v) {   //  跟进方式

                $followWay[]    =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['crmdata']['task_type'] as $v) {    //  任务类型

                $taskType[]     =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['crmdata']['client_type'] as $v) {  //  客户等级

                $clientType[]   =   array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['crmdata']['client_status'] as $v) {//  客户状态

                $clientStatus[] =   array('id' => $v, 'name' => $className[$v]);
            }

            $userClassName  =   $Cache['userclass_name'];
            $userEdu    =   $userWord   =   $userLabel =   $integrityArr   =   array();

            foreach ($Cache['userdata']['user_edu'] as $v) {    //  学历要求

                $userEdu[]  =   array('id' => $v, 'name' => $userClassName[$v]);
            }
            foreach ($Cache['userdata']['user_word'] as $v) {   //  工作经验

                $userWord[] =   array('id' => $v, 'name' => $userClassName[$v]);
            }
            foreach ($Cache['userdata']['user_label'] as $v) {  //  简历标签

                $userLabel[]=   array('id' => $v, 'name' => $userClassName[$v]);
            }

            $integrity      = $arr_data['integrity_name'];
            foreach ($integrity as $ik => $iv) {                //  简历完整度
                $integrityArr[] =   array('id' => $ik, 'name' => $iv);
            }

            $stateArr       =   array(0 => '交易失败', 1 => '等待付款', 2 => '支付成功', 3 => '等待确认', 4 => '交易关闭');
            $typeArr        =   $arr_data['ordertype'];
            $oTypeArr       =   $arr_data['pay'];

            $stateNewArr = $typeNewArr = $oTypeNewArr = array();
            foreach ($stateArr as $sk => $sv){                  //  支付状态

                $stateNewArr[]  =   array('id' => $sk, 'name' => $sv);
            }
            foreach ($typeArr as $tk => $tv){                   //  订单类型

                $typeNewArr[]   =   array('id' => $tk, 'name' => $tv);
            }
            foreach ($oTypeArr as $otk => $otv){                //  支付方式

                $oTypeNewArr[]  =   array('id' => $otk, 'name' => $otv);
            }

            $adminM     =   $this->MODEL('admin');
            $crmUser    =   $adminM->getList(array('is_crm' => 1, 'uid' => array('<>', $_SESSION['auid'])), array('field' => '`uid`,`name`'));
            $adminUser  =   $adminM->getList(array('uid' => array('<>', $_SESSION['auid'])), array('field' => '`uid`,`name`'));

            $ratingM    =   $this->MODEL('rating');

            $ratingList =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`,`service_price`,`yh_price`,`time_start`,`time_end`'));

            $ratingArr  =   array();
            foreach ($ratingList as $rk => $rv) {
                $ratingArr[$rk]['id']   =   $rv['id'];
                $ratingArr[$rk]['name'] =   $rv['name'];
                if ($rv['time_start'] < time() && $rv['time_end'] > time()){
                    $ratingArr[$rk]['price']    =   $rv['yh_price'];
                }else{
                    $ratingArr[$rk]['price']    =   $rv['service_price'];
                }
            }


            $data       =   array(
                'Info'          =>  $comInfo,

                'followWay'     =>  $followWay,
                'taskType'      =>  $taskType,

                'clientType'    =>  $clientType,
                'clientStatus'  =>  $clientStatus,

                'stateArr'      =>  $stateNewArr,
                'typeArr'       =>  $typeNewArr,
                'oTypeArr'      =>  $oTypeNewArr,

                'crmUser'       =>  $crmUser,
                'adminUser'     =>  $adminUser,

                'userEdu'       =>  $userEdu,
                'userWord'      =>  $userWord,
                'userLabel'     =>  $userLabel,
                'integrityArr'  =>  $integrityArr,

                'ratingArr'     =>  $ratingArr,

                'links'         =>  $comLinks
            );

            $return     =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($return);
            exit();
        }
    }

    /**
     * @desc 客户管理 - 获取企业多联系方式
     */
    function getLinkList_action()
    {
        if ($_POST['id']){
            $comId = $_POST['id'];
            $crmM       =   $this->MODEL('crm');
            $comLinks = $crmM->getCrmLink(array('uid' => $comId));
            $data       =   array(
                'links' => $comLinks
            );
            $return     =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($return);
            exit();
        }
    }

    /**
     * @desc 客户管理 - 一键登录
     */
    function Imitate_action()
    {

        $userInfoM  =   $this->MODEL('userinfo');

        $member     =   $userInfoM->getInfo(array('uid' => intval($_GET['uid'])), array('field' => '`uid`,`username`,`salt`,`email`,`password`,`usertype`,`did`'));

        $this->cookie->unset_cookie($_SESSION['auid']);
        $this->cookie->add_cookie($member['uid'], $member['username'], $member['salt'], $member['email'], $member['password'], 2, $this->config['sy_logintime'], $member['did'],'1');

        $typeStr    =   trim($_GET['type']);

        $url        =   '';

        if (!empty($typeStr)) {
            if ($typeStr == 'job') {

                $url    =   'index.php?c=' . $typeStr . '&w=1';
            } else {

                $url    =   'index.php?c=' . $typeStr;
            }
        }
        header('Location: ' . $this->config['sy_weburl'] . '/member/' . $url);
    }

    /**
     * @desc 客户管理 - Tab1：跟进记录
     */
    function getFollowList_action()
    {

        if ($_POST['id']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['id'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array('uid' => $comId);

            if ($_POST['followTab'] == 1){
                $where['auid']  =   $_SESSION['auid'];
            }

            $followNum  =   $this->obj->select_num('crmnew_concern', $where);

            $where['orderby']   =   array('ftime, desc');

            $followTotal=   $this->obj->select_num('crmnew_concern', $where);

            $pageTotal  =   ceil($followTotal / $limit);

            $where['limit'] =   array(($page - 1) * $limit, $limit);

            $start      =   ($page - 1) * $limit + 1;

            if ($page < $pageTotal){

                $end    =   $start + 9;
            }else{

                $end    =   $followTotal;
            }

            $FollowList =   $crmM->getConcernList($where, array('utype' => 'crm_com_info'));
            $data       =   array(

                'followList'    =>  $FollowList,
                'start'         =>  $start,
                'end'           =>  $end,
                'followNum'     =>  $followNum,
                'followTotal'   =>  $followTotal,
                'pageTotal'     =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab1：跟进记录
     */
    function getFollowInfo_action()
    {

        if ($_POST['id']) {

            $crmM   =   $this->MODEL('crm');
            $info   =   $crmM->getConcernInfo(array('id' => $_POST['id']));
            $res    =   array('error' => 0, 'info' => $info);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab1：添加跟进
     */
    function addFollow_action()
    {

        if (!empty($_POST)) {

            $data           =   $_POST;

            $data['uid']    =   $_POST['uid'] ? $_POST['uid'] : $_POST['comid'];
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->addFollow($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab1：添加crm企业联系人; Tab3: 添加/修改 客户联系人
     */
    function addCrmLink_action()
    {
        if (!empty($_POST)) {
            $data = $_POST;
            $data['uid'] = $_POST['uid'];
            $data['auid'] = $_SESSION['auid'];
            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->insertCrmLink($data);
            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab1：批注跟进
     */
    function noteFollow_action()
    {

        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];
            $crmM           =   $this->MODEL('crm');
            $return         =   $crmM->noteFollow($data);

            if ($return['errcode'] == 9) {

                $this->MODEL('log')->addAdminLog("批注跟进（ID：" . $_POST['id'] . "）");
            }
            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab1：删除跟进批注
     */
    function delFollowNote_action()
    {

        if (!empty($_POST)) {

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->delFollowNote(array('id' => $_POST['id']));

            if ($return['errcode'] == 9) {

                $this->MODEL('log')->addAdminLog("删除跟进批注（ID：" . $_POST['id'] . "）");
            }
            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab2：短信模板
     */
    function getMsgTpl_action()
    {

        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $msgTpl =   $crmM->getMsgTpl($data);
            foreach ($msgTpl as $k => $v){
                if ($v['name'] == 'msgcrmlink'){

                    $msgContent =   $v['content'];
                }
            }
            $data   =   array(
                'msgTpl'    =>  $msgTpl,
                'msgContent'=>  $msgContent
            );

            $return =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab2：发送短信
     */
    function sendMsg_action()
    {

        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->sendCrmMsg($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab2：短信记录
     */
    function getMsgList_action()
    {

        if ($_POST['uid']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['uid'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array(

                'uid'   =>  $comId,
                'port'  =>  11
            );

            if ($_POST['msgTab'] == 1) {
                $where['cuid']  =   $_SESSION['auid'];
            }

            $msgNum     =   $this->obj->select_num('moblie_msg', $where);

            $where['orderby']   =   array('ctime, desc');

            $msgTotal   =   $this->obj->select_num('moblie_msg', $where);

            $pageTotal  =   ceil($msgTotal / $limit);

            $where['limit'] =   array(($page - 1) * $limit, $limit);

            $start      =   ($page - 1) * $limit + 1;

            if ($page < $pageTotal){

                $end    =   $start + 9;
            }else{

                $end    =   $msgTotal;
            }

            $msgList    =   $crmM->getMsgList($where);
            $data       =   array(

                'msgList'   =>  $msgList,
                'start'     =>  $start,
                'end'       =>  $end,
                'msgNum'    =>  $msgNum,
                'msgTotal'  =>  $msgTotal,
                'pageTotal' =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 客户套餐
     */
    function getComStatic_action()
    {
        if ($_POST['id']) {

            $crmM   =   $this->MODEL('crm');
            $comId  =   $_POST['id'];
            $static =   $crmM->getComStatic($comId);
            $data   =   array('static' => $static);

            $res = array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 更新客户名称
     */
    function upComName_action()
    {
        if (!empty($_POST)) {

            $comId  =   $_POST['id'];
            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->upComName(array('name' => $_POST['name'], 'uid' => $comId, 'auid' => $_SESSION['auid']));

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 更新账户信息
     */
    function upUserInfo_action()
    {
        if (!empty($_POST)) {

            $comId  =   $_POST['id'];
            $crmM   =   $this->MODEL('crm');

            $data   =   array(
                'username'  =>  $_POST['username'],
                'password'  =>  $_POST['password'],
                'uid'       =>  $comId,
                'auid'      =>  $_SESSION['auid']
            );

            $return =   $crmM->upUserInfo($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 更新客户等级/状态
     */
    function upStatusType_action()
    {
        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->upComST($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 暂停招聘
     */
    function stopCom_action()
    {
        if (!empty($_POST)) {

            $data   =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->stopCom($data);
            if ($return['errcode'] == 9){
                $this->MODEL('company')->setComStaticSub($_POST['uid']);
            }
            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 解除暂停
     */
    function openCom_action()
    {
        if (!empty($_POST)) {

            $data   =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->openCom($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 更新联系方式
     */
    function upComLink_action()
    {

        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->upComLink($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 备注企业
     */
    function remarkCom_action()
    {
        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->remarkCom($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 删除客户联系人
     */
    function delCrmLink_action()
    {

        $crmM   =   $this->MODEL('crm');
        $return =   $crmM->delCrmLink(array('id' => $_POST['id'], 'uid' => $_POST['uid'], 'auid' => $_SESSION['auid']));
        echo json_encode($return);
        die;
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 转交客户
     */
    function deliver_action()
    {

        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->deliverUser($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 放弃客户
     */
    function giveUp_action()
    {
        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->giveUpUser($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab4：在招职位
     */
    function getJobList_action()
    {

        if ($_POST['id']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['id'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array('uid' => $comId);

            $jobNum     =   $this->obj->select_num('company_job', $where);
            $job1Num    =   $this->obj->select_num('company_job', array('uid' => $comId, 'state' => 0));
            $job2Num    =   $this->obj->select_num('company_job', array('uid' => $comId, 'state' => 3));
            $statusNum  =   $this->obj->select_num('company_job', array('uid' => $comId, 'status'=> 1));

            if ($_POST['job_keyword']){

                $where['name']  =   array('like', $_POST['job_keyword']);
            }
            $where['orderby']   =   array('lastupdate, desc');

            $jobTotal   =   $this->obj->select_num('company_job', $where);

            $pageTotal  =   ceil($jobTotal / $limit);

            $where['limit'] =   array(($page - 1) * $limit, $limit);

            $start      =   ($page - 1) * $limit + 1;

            if ($page < $pageTotal){

                $end    =   $start + 9;
            }else{

                $end    =   $jobTotal;
            }

            $jobList    =   $crmM->getJobList($where, array('uid' => $comId, 'xcx' => 1));
            $data       =   array(

                'jobList'   =>  $jobList,
                'start'     =>  $start,
                'end'       =>  $end,
                'jobNum'    =>  $jobNum,
                'job1Num'   =>  $job1Num,
                'job2Num'   =>  $job2Num,
                'statusNum' =>  $statusNum,
                'jobTotal'  =>  $jobTotal,
                'pageTotal' =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab4：在招职位 - 上下架
     */
    function setJobStatus_action()
    {

        if (!empty($_POST)) {

            $status =   $_POST['status'] == 1 ? 0 : 1;
            $jobId  =   intval($_POST['jobid']);

            $this->obj->update_once('company_job', array('status' => $status), array('id' => $jobId));

            if ($status == 0) {

                $logMsg = "职位(ID" . $jobId . ")上架成功";
            } else {

                $logMsg = "职位(ID" . $jobId . ")下架成功";
            }
            $this->MODEL('log')->addAdminLog($logMsg);

            echo json_encode(array('error' => 0, 'msg' => ''));
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab4：在招职位 - 推文
     */
    function addTwTask_action()
    {
        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->addTwTask($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab4：在招职位 - 匹配简历
     */
    function getResumeList_action()
    {

        if ($_POST['uid']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['uid'];
            $jobId      =   $_POST['id'];

            $noUidS = $blackUidS = $applyUidS = $inviteUidS = array();
            $blackList  =   $this->obj->select_all('blacklist', array('p_uid' => $comId), '`cuid`');
            if (!empty($blackList)) {
                foreach ($blackList as $v) {
                    $blackUidS[] = $v['c_uid'];
                }
            }
            $applyList  =   $this->obj->select_all('userid_job', array('job_id' => $jobId), '`uid`');
            if (!empty($applyList)) {
                foreach ($applyList as $v) {
                    $applyUidS[] = $v['uid'];
                }
            }
            $inviteList =   $this->obj->select_all('userid_msg', array('fid' => $comId), '`uid`');
            if (!empty($inviteList)) {
                foreach ($inviteList as $v) {
                    $inviteUidS[] = $v['uid'];
                }
            }
            $noUidS =   array_values(array_merge($blackUidS, $applyUidS, $inviteUidS));
            $numWhere   =   array('defaults' => 1, 'state' => 1, 'status' => 1, 'r_status' => 1);

            if (!empty($noUidS)){

                $numWhere['uid']    =   array('notin', @implode(',', $noUidS));
            }
            $resumeNum  =   $this->obj->select_num('resume_expect', $numWhere);

            $where      =   'a.defaults = 1 and a.state = 1 and a.status = 1 and a.r_status = 1';

            if (!empty($noUidS)){

                $where  .=  ' and a.`uid` NOT IN ('.@implode(',', $noUidS).')';
            }

            $city_job_class = '';

            if ($_POST['job_class'] || $_POST['city_class']) {

                include_once(PLUS_PATH.'city.cache.php');
                include_once(PLUS_PATH.'cityparent.cache.php');
                include_once(PLUS_PATH.'job.cache.php');
                include_once(PLUS_PATH.'jobparent.cache.php');

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
                    $urlarr['job_class'] = $_POST['job_class'];
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
                    $urlarr['city_class'] = $_POST['city_class'];
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
                $city_job_class = ",(select `eid` from `" . $this->def . "resume_city_job_class` where $cjwhere) cj";
                $where .= " and a.id = cj.eid";
            }

            //  学历要求
            if ($_POST['edu']) {

                $where  .=  " AND a.`edu` =".$_POST['edu'];
            }

            //  工作经验
            if ($_POST['word']) {

                $where  .=  " AND a.`exp` =".$_POST['word'];
            }

            //  简历标签
            if($_POST['label']){

                $where  .=  ' and a.label='.intval($_POST['label']);
                $urlarr['label']    =   intval($_POST['label']);
            }

            //简历完整度
            if ($_POST['integrity']) {
                if ($_POST['integrity']  ==  55) {

                    $where  .=  " and a.integrity = 55";
                } else if ($_POST['integrity'] == 65) {
                    $where  .=  " and a.integrity = 65";
                } else {
                    $integrity_val = $arr_data['integrity_val'];
                    $where  .=  " and a.integrity >= " . $integrity_val[$_POST['integrity']];
                }
                $urlarr['integrity'] = $_POST['integrity'];
            }

            //简历创建时间
            if ($_POST['time']) {
                $timeStr = intval($_POST['time']);
                if ($timeStr == 1) {
                    $where  .=  " and a.ctime > ".strtotime('today');
                } else if ($timeStr == 2) {
                    $where  .=  " and a.ctime > ".strtotime('-3 days');
                } else if ($timeStr == 3) {
                    $where  .=  " and a.ctime > ".strtotime('-7 days');
                } else if ($timeStr == 4) {
                    $where  .=  " and a.ctime > ".strtotime('-30 days');
                }
            }

            //  关键词搜索
            if($_POST['keyword']){


                $resumeM    =   $this->MODEL('resume');

                $keywordStr =   trim($_POST['keyword']);
                $keywordArr =   explode(' ', $keywordStr);

                if (count($keywordArr) > 1){
                    $workWhere['PHPYUNBTWSTART_work']   =   '';
                    foreach ($keywordArr as $kv){

                        $workWhere['name'][]        =   array('like', trim($kv), 'OR');
                        $workWhere['title'][]        =   array('like', trim($kv), 'OR');
                    }
                    $workWhere['PHPYUNBTWEND_work']     =   '';
                }else{
                    $workWhere  =   array(
                        'name'  =>  array('like', $keywordStr),
                        'title' =>  array('like', $keywordStr, 'OR'),
                    );
                }
                $work       =   $resumeM->getResumeWorks($workWhere, 'eid');
                if ($work) {
                    $eids   =   array();
                    foreach ($work as $v) {
                        $eids[] =   $v['eid'];
                    }
                }
                if (count($keywordArr) > 1){
                    $eduWhere['PHPYUNBTWSTART_edu']   =   '';
                    foreach ($keywordArr as $kv){

                        $eduWhere['name'][]        =   array('like', trim($kv), 'OR');
                        $eduWhere['title'][]        =   array('like', trim($kv), 'OR');
                    }
                    $eduWhere['PHPYUNBTWEND_edu']     =   '';
                }else{
                    $eduWhere   =   array(

                        'name'      =>  array('like', $keywordStr),
                        'specialty' =>  array('like', $keywordStr, 'OR')
                    );
                }
                $edu        =   $resumeM->getResumeEdus($eduWhere, 'eid');
                if ($edu) {
                    $eids   =   array();
                    foreach ($edu as $v) {
                        $eids[] =   $v['eid'];
                    }
                }

                $userInfoM  =   $this->MODEL('userinfo');

                if (count($keywordArr) > 1){
                    $mwhere['PHPYUNBTWSTART_mem']   =   '';
                    foreach ($keywordArr as $kv){

                        $eduWhere['description'][]  =   array('like', trim($kv), 'OR');
                    }
                    $mwhere['PHPYUNBTWEND_mem']     =   '';
                }else{
                    $mwhere['description']  =   array('like', $keywordStr);
                }
                if(!empty($mwhere)){
                    $uidList    =   $userInfoM->getUserInfoList($mwhere, array('usertype' => 1, 'field' => '`uid`'));
                    if(!empty($uidList)){
                        foreach($uidList as $uv){
                            $mUids[]    =   $uv['uid'];
                        }
                    }
                }
                if (count($keywordArr) > 1){

                    $where  .=  " and (";

                    foreach ($keywordArr as $k => $kv){

                        if ($k + 1 < count($keywordArr)){

                            $where  .=  " a.name like '%$kv%' or a.content like '%$kv%' or";
                        }else{

                            $where  .=  " a.name like '%$kv%' or a.content like '%$kv%'";
                        }
                    }
                }else{

                    $where  .=  " and (a.name like '%$keywordStr%' or a.content like '%$keywordStr%'";
                }

                if (!empty($mUids)){
                    $where  .=  " or a.uid in (".pylode(',', $mUids).")";
                }
                if (!empty($eids)){
                    $where  .=  " or a.id in (".pylode(',', $eids).")";
                }
                $where  .=  ")";
            }

            $countSql   =   "select count(*) as num from `".$this->def."resume_expect` a{$city_job_class} where {$where}";

            $resumeArr  =   $this->obj->DB_query_all($countSql);

            $resumeTotal=   $resumeArr['num'] ? $resumeArr['num'] : 0;

            $page       =   $_POST['page'];
            $limit      =   10;
            $pageTotal  =   ceil($resumeTotal / $limit);

            $start      =   ($page - 1) * $limit + 1;
            if ($page < $pageTotal){
                $end    =   $start + 9;
            }else{
                $end    =   $resumeTotal;
            }

            $order      =   ' order by a.lastupdate desc';

            $limitS     =   ($page - 1) * $limit;

            $sql        =   "select a.* from `".$this->def."resume_expect` a{$city_job_class} where {$where} {$order} limit {$limitS},{$limit}";

            $resumeList =   $crmM->getResumeList(array('sql' => $sql));

            $data       =   array(

                'resumeList'    =>  $resumeList,
                'start'         =>  $start,
                'end'           =>  $end,
                'resumeNum'     =>  $resumeNum,
                'resumeTotal'   =>  $resumeTotal,
                'pageTotal'     =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab4：在招职位 - 匹配简历
     */
    function getAutoResumeList_action()
    {

        if ($_POST['uid']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['uid'];
            $jobId      =   $_POST['id'];

            $noUidS = $blackUidS = $applyUidS = $inviteUidS = array();
            $blackList  =   $this->obj->select_all('blacklist', array('p_uid' => $comId), '`cuid`');
            if (!empty($blackList)) {
                foreach ($blackList as $v) {
                    $blackUidS[] = $v['c_uid'];
                }
            }
            $applyList  =   $this->obj->select_all('userid_job', array('job_id' => $jobId), '`uid`');
            if (!empty($applyList)) {
                foreach ($applyList as $v) {
                    $applyUidS[] = $v['uid'];
                }
            }
            $inviteList =   $this->obj->select_all('userid_msg', array('fid' => $comId), '`uid`');
            if (!empty($inviteList)) {
                foreach ($inviteList as $v) {
                    $inviteUidS[] = $v['uid'];
                }
            }
            $noUidS =   array_values(array_merge($blackUidS, $applyUidS, $inviteUidS));

            $where      =   'a.defaults = 1 and a.state = 1 and a.status = 1 and a.r_status = 1';

            if (!empty($noUidS)){

                $where  .=  ' and a.`uid` NOT IN ('.@implode(',', $noUidS).')';
            }

            $city_job_class = '';

            if ($_POST['job_class'] || $_POST['city_class']) {

                include_once(PLUS_PATH.'city.cache.php');
                include_once(PLUS_PATH.'cityparent.cache.php');
                include_once(PLUS_PATH.'job.cache.php');
                include_once(PLUS_PATH.'jobparent.cache.php');

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
                    $urlarr['job_class'] = $_POST['job_class'];
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
                    $urlarr['city_class'] = $_POST['city_class'];
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
                $city_job_class = ",(select `eid` from `" . $this->def . "resume_city_job_class` where $cjwhere) cj";
                $where .= " and a.id = cj.eid";
            }

            //  学历要求
            if ($_POST['edu']) {

                $where  .=  " AND a.`edu` =".$_POST['edu'];
            }

            //  工作经验
            if ($_POST['word']) {

                $where  .=  " AND a.`exp` =".$_POST['word'];
            }

            //  简历标签
            if($_POST['label']){

                $where  .=  ' and a.label='.intval($_POST['label']);
                $urlarr['label']    =   intval($_POST['label']);
            }

            //简历完整度
            if ($_POST['integrity']) {
                if ($_POST['integrity']  ==  55) {

                    $where  .=  " and a.integrity = 55";
                } else if ($_POST['integrity'] == 65) {
                    $where  .=  " and a.integrity = 65";
                } else {
                    $integrity_val = $arr_data['integrity_val'];
                    $where  .=  " and a.integrity >= " . $integrity_val[$_POST['integrity']];
                }
                $urlarr['integrity'] = $_POST['integrity'];
            }

            //简历创建时间
            if ($_POST['time']) {
                $timeStr = intval($_POST['time']);
                if ($timeStr == 1) {
                    $where  .=  " and a.ctime > ".strtotime('today');
                } else if ($timeStr == 2) {
                    $where  .=  " and a.ctime > ".strtotime('-3 days');
                } else if ($timeStr == 3) {
                    $where  .=  " and a.ctime > ".strtotime('-7 days');
                } else if ($timeStr == 4) {
                    $where  .=  " and a.ctime > ".strtotime('-30 days');
                }
            }

            //  关键词搜索
            if($_POST['keyword']){


                $resumeM    =   $this->MODEL('resume');

                $keywordStr =   trim($_POST['keyword']);
                $keywordArr =   explode(' ', $keywordStr);

                if (count($keywordArr) > 1){
                    $workWhere['PHPYUNBTWSTART_work']   =   '';
                    foreach ($keywordArr as $kv){

                        $workWhere['name'][]        =   array('like', trim($kv), 'OR');
                        $workWhere['title'][]        =   array('like', trim($kv), 'OR');
                    }
                    $workWhere['PHPYUNBTWEND_work']     =   '';
                }else{
                    $workWhere  =   array(
                        'name'  =>  array('like', $keywordStr),
                        'title' =>  array('like', $keywordStr, 'OR'),
                    );
                }
                $work       =   $resumeM->getResumeWorks($workWhere, 'eid');
                if ($work) {
                    $eids   =   array();
                    foreach ($work as $v) {
                        $eids[] =   $v['eid'];
                    }
                }
                if (count($keywordArr) > 1){
                    $eduWhere['PHPYUNBTWSTART_edu']   =   '';
                    foreach ($keywordArr as $kv){

                        $eduWhere['name'][]        =   array('like', trim($kv), 'OR');
                        $eduWhere['title'][]        =   array('like', trim($kv), 'OR');
                    }
                    $eduWhere['PHPYUNBTWEND_edu']     =   '';
                }else{
                    $eduWhere   =   array(

                        'name'      =>  array('like', $keywordStr),
                        'specialty' =>  array('like', $keywordStr, 'OR')
                    );
                }
                $edu        =   $resumeM->getResumeEdus($eduWhere, 'eid');
                if ($edu) {
                    $eids   =   array();
                    foreach ($edu as $v) {
                        $eids[] =   $v['eid'];
                    }
                }

                $userInfoM  =   $this->MODEL('userinfo');

                if (count($keywordArr) > 1){
                    $mwhere['PHPYUNBTWSTART_mem']   =   '';
                    foreach ($keywordArr as $kv){

                        $eduWhere['description'][]  =   array('like', trim($kv), 'OR');
                    }
                    $mwhere['PHPYUNBTWEND_mem']     =   '';
                }else{
                    $mwhere['description']  =   array('like', $keywordStr);
                }
                if(!empty($mwhere)){
                    $uidList    =   $userInfoM->getUserInfoList($mwhere, array('usertype' => 1, 'field' => '`uid`'));
                    if(!empty($uidList)){
                        foreach($uidList as $uv){
                            $mUids[]    =   $uv['uid'];
                        }
                    }
                }
                if (count($keywordArr) > 1){

                    $where  .=  " and (";

                    foreach ($keywordArr as $k => $kv){

                        if ($k + 1 < count($keywordArr)){

                            $where  .=  " a.name like '%$kv%' or a.content like '%$kv%' or";
                        }else{

                            $where  .=  " a.name like '%$kv%' or a.content like '%$kv%'";
                        }
                    }
                }else{

                    $where  .=  " and (a.name like '%$keywordStr%' or a.content like '%$keywordStr%'";
                }

                if (!empty($mUids)){
                    $where  .=  " or a.uid in (".pylode(',', $mUids).")";
                }
                if (!empty($eids)){
                    $where  .=  " or a.id in (".pylode(',', $eids).")";
                }
                $where  .=  ")";
            }

            $order      =   ' order by a.lastupdate desc';

            $sql        =   "select a.* from `".$this->def."resume_expect` a{$city_job_class} where {$where} {$order} limit 100";

            $resumeList =   $crmM->getResumeList(array('sql' => $sql));

            $data   =   array('resumeList' => $resumeList);
            $res    =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }


    /**
     * @desc 客户管理 - Tab4：在招职位 - 推送匹配简历
     */
    function setResumeLink_action()
    {

        if (!empty($_POST)) {

            $comId  =   $_POST['uid'];
            $jobId  =   $_POST['jobid'];

            $crmM   =   $this->MODEL('crm');

            $dataValue  =   array(

                'eid'   =>  $_POST['resumeids'],
                'uid'   =>  $comId,
                'jobid' =>  $jobId,
                'auid'  =>  $_SESSION['auid'],
                'auto'  =>  $_POST['auto']
            );

            if ($_POST['auto'] == 1) {

                $dataValue['interval'] = $_POST['interval'];

                $searchData = array();
                if (isset($_POST['search_job']) && !empty($_POST['search_job'])) {
                    $searchData['job'] = $_POST['search_job'];
                }
                if (isset($_POST['search_city']) && !empty($_POST['search_city'])) {
                    $searchData['city'] = $_POST['search_city'];
                }
                if (isset($_POST['search_keyword']) && !empty($_POST['search_keyword'])) {
                    $searchData['keyword'] = $_POST['search_keyword'];
                }
                if (isset($_POST['search_edu']) && !empty($_POST['search_edu'])) {
                    $searchData['edu'] = $_POST['search_edu'];
                }
                if (isset($_POST['search_word']) && !empty($_POST['search_word'])) {
                    $searchData['word'] = $_POST['search_word'];
                }
                if (isset($_POST['search_label']) && !empty($_POST['search_label'])) {
                    $searchData['label'] = $_POST['search_label'];
                }
                if (isset($_POST['search_integrity']) && !empty($_POST['search_integrity'])) {
                    $searchData['integrity'] = $_POST['search_integrity'];
                }
                if (isset($_POST['search_time']) && !empty($_POST['search_time'])) {
                    $searchData['time'] = $_POST['search_time'];
                }
                $dataValue['search_info'] = serialize($searchData);
            }

            if (isset($_POST['id']) && !empty($_POST['id'])){

                $return =   $crmM->upLinkResume($dataValue, array('id' => $_POST['id']));
            }else{

                $return =   $crmM->addLinkResume($dataValue);
            }

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab4：在招职位 - 推送匹配简历 - 发送短信
     */
    function sendResumeMsg_action()
    {

        if (!empty($_POST)) {

            $data           =   $_POST;

            $data['content']=   str_replace('「页面链接」', $_POST['link'], $_POST['content']);
            $data['auid']   =   $_SESSION['auid'];
            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->sendResumeMsg($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理 - Tab5：推荐候选人
     */
    function getRecList_action()
    {

        if ($_POST['uid']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['uid'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array('uid' => $comId);

            $recNum     =   $this->obj->select_num('crm_resume', $where);

            $where['orderby']   =   array('atime, desc');

            $recTotal   =   $this->obj->select_num('crm_resume', $where);

            $pageTotal  =   ceil($recTotal / $limit);

            $where['limit'] =   array(($page - 1) * $limit, $limit);

            $start      =   ($page - 1) * $limit + 1;

            if ($page < $pageTotal){

                $end    =   $start + 9;
            }else{

                $end    =   $recTotal;
            }

            $recList    =   $crmM->getRecLogList($where, array('uid' => $comId, 'type' => 'info'));
            $data       =   array(

                'recList'   =>  $recList,
                'start'     =>  $start,
                'end'       =>  $end,
                'recNum'    =>  $recNum,
                'recTotal'  =>  $recTotal,
                'pageTotal' =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab5：删除推荐记录
     */
    function delRecInfo_action()
    {

        $del    =   $_POST['id'];
        $where['id']            =   $del;
        $return['layertype']    =   0;

        $crmM   =   $this->MODEL('crm');
        $return =   $crmM->delRecResumeLog($where);

        if ($return['errcode'] == '9') {

            $this->MODEL('log')->addAdminLog("删除简历推广（ID：".$del."）");
        }

        echo json_encode($return);
        die;
    }

    /**
     * @desc 客户管理 - Tab5：推荐候选人 - 详情
     */
    function getRecInfo_action(){

        if ($_GET['id']) {

            $this->yunset('id', $_GET['id']);
        }
        if ($_POST){

            $crmM       =   $this->MODEL('crm');
            $return     =   $crmM->getRecInfo(array('id' => $_POST['id']), array('type' => 'info'));

            $data       =   array(

                'recInfo'       =>  $return['info'],
                'recResumes'    =>  $return['list']
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
        $this->yuntpl(array('admin/crm_resume_ts_info'));
    }

    /**
     * @desc 客户管理 - Tab6：推送历史
     */
    function getTwList_action()
    {

        if ($_POST['uid']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['uid'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array('cuid'   =>  $comId);

            $twNum      =   $this->obj->select_num('wxpub_twtask', $where);

            if ($_POST['type']){
                $where['type']  =   $_POST['type'];
            }
            if ($_POST['auid']){
                $where['uid']  =   $_POST['auid'];
            }
            if ($_POST['status']){
                $where['status']=   $_POST['status'] == 1 ? 1 : 0;
            }
            if ($_POST['urgent']){
                $where['urgent']=   $_POST['urgent'];
            }
            if ($_POST['wcmoments']){
                $where['wcmoments']=   $_POST['wcmoments'];
            }
            if ($_POST['gzh']){
                $where['gzh']=   $_POST['gzh'];
            }
            if ($_POST['keyword']){
                $where['jobname']   =   array('like', $_POST['keyword']);
            }

            $where['orderby']   =   array('ctime, desc');

            $twTotal    =   $this->obj->select_num('wxpub_twtask', $where);

            $pageTotal  =   ceil($twTotal / $limit);

            $where['limit'] =   array(($page - 1) * $limit, $limit);

            $start      =   ($page - 1) * $limit + 1;

            if ($page < $pageTotal){

                $end    =   $start + 9;
            }else{

                $end    =   $twTotal;
            }

            $twList     =   $crmM->getTWList($where);
            $data       =   array(

                'twList'    =>  $twList,
                'start'     =>  $start,
                'end'       =>  $end,
                'twNum'     =>  $twNum,
                'twTotal'   =>  $twTotal,
                'pageTotal' =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab7：订单记录
     */
    function getOrderList_action()
    {

        if ($_POST['id']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['id'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array('uid' => $comId);

            $orderNum   =   $this->obj->select_num('company_order', $where);

            if ($_POST['order_state']){
                $where['order_state']   =   $_POST['order_state'];
            }
            if ($_POST['order_type']){
                $where['order_type']    =   $_POST['order_type'];
            }
            if ($_POST['o_type']){
                $where['type']          =   $_POST['o_type'];
            }
            if ($_POST['order_s_time'] && $_POST['order_e_time']){
                $where['PHPYUNBTWSTATR_A']  =   '';
                $where['order_time'][]      =   array('>', strtotime($_POST['order_s_time']), '');
                $where['order_time'][]      =   array('<', strtotime($_POST['order_e_time']) + 86400, '');
                $where['PHPYUNBTWEND_A']    =   '';
            }else if ($_POST['order_s_time']){
                $where['order_time']        =   array('>', strtotime($_POST['order_s_time']));
            }else if ($_POST['order_e_time']){
                $where['order_time']        =   array('<', strtotime($_POST['order_e_time']) + 86400);
            }
            if ($_POST['order_keyword']){
                $where['order_id']          =   $_POST['order_keyword'];
            }

            $where['orderby']   =   array('id, desc');
            $orderTotal =   $this->obj->select_num('company_order', $where);

            $pageTotal  =   ceil($orderTotal / $limit);

            $where['limit'] =   array(($page - 1) * $limit, $limit);

            $start      =   ($page - 1) * $limit + 1;

            if ($page < $pageTotal){

                $end    =   $start + 9;
            }else{

                $end    =   $orderTotal;
            }

            $orderList  =   $crmM->getOrderList($where, array('uid' => $comId));
            $data       =   array(

                'orderList' =>  $orderList,
                'start'     =>  $start,
                'end'       =>  $end,
                'orderNum'  =>  $orderNum,
                'orderTotal'=>  $orderTotal,
                'pageTotal' =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户管理 - Tab7：订单记录 - 新增订单
     */
    function addDeal_action()
    {

        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->addOrder($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     *  @desc 客户管理 - Tab8：用户日志
     */
    function getMemberLogList_action()
    {

        if ($_POST['id']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['id'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array('uid' => $comId, 'usertype' => 2);

            $logNum     =   $this->obj->select_num('member_log', $where);

            if ($_POST['log_s_time'] && $_POST['log_e_time']){

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['ctime'][]           =   array('>', strtotime($_POST['log_s_time']), '');
                $where['ctime'][]           =   array('<', strtotime($_POST['log_e_time']) + 86400, '');
                $where['PHPYUNBTWEND_A']    =   '';
            }else if ($_POST['log_s_time']){

                $where['ctime']             =   array('>', strtotime($_POST['log_s_time']));
            }else if ($_POST['log_e_time']){

                $where['ctime']             =   array('<', strtotime($_POST['log_e_time']) + 86400);
            }

            $where['content']   =   array('like', $_POST['log_keyword']);

            $where['orderby']   =   array('ctime, desc');
            $logTotal   =   $this->obj->select_num('member_log', $where);

            $pageTotal  =   ceil($logTotal / $limit);

            $where['limit'] =   array(($page - 1) * $limit, $limit);

            $start      =   ($page - 1) * $limit + 1;

            if ($page < $pageTotal){

                $end    =   $start + 9;
            }else{

                $end    =   $logTotal;
            }

            $logList    =   $crmM->getMemberLogList($where, array('uid' => $comId));
            $data       =   array(

                'logList'   =>  $logList,
                'start'     =>  $start,
                'end'       =>  $end,
                'logNum'    =>  $logNum,
                'logTotal'  =>  $logTotal,
                'pageTotal' =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     *  @desc 客户管理 - Tab9：用户轨迹
     */
    function getWebLogList_action()
    {

        if ($_POST['id']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['id'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array('uid' => $comId, 'usertype' => 2);

            $webNum     =   $this->obj->select_num('web_log', $where);

            if ($_POST['web_s_time'] && $_POST['web_e_time']){

                $where['PHPYUNBTWSTATR_A']  =   '';
                $where['time'][]            =   array('>', strtotime($_POST['web_s_time']), '');
                $where['time'][]            =   array('<', strtotime($_POST['web_e_time']) + 86400, '');
                $where['PHPYUNBTWEND_A']    =   '';
            }else if ($_POST['web_s_time']){

                $where['time']              =   array('>', strtotime($_POST['web_s_time']));
            }else if ($_POST['web_e_time']){

                $where['time']              =   array('<', strtotime($_POST['web_e_time']) + 86400);
            }

            $where['orderby']   =   array('time, desc');

            $webTotal   =   $this->obj->select_num('web_log', $where);

            $pageTotal  =   ceil($webTotal / $limit);

            $where['limit'] =   array(($page - 1) * $limit, $limit);

            $start      =   ($page - 1) * $limit + 1;

            if ($page < $pageTotal){

                $end    =   $start + 9;
            }else{

                $end    =   $webTotal;
            }

            $webList    =   $crmM->getWebLogList($where, array('uid' => $comId));
            $data       =   array(

                'webList'   =>  $webList,
                'start'     =>  $start,
                'end'       =>  $end,
                'webNum'    =>  $webNum,
                'webTotal'  =>  $webTotal,
                'pageTotal' =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     *  @desc 客户管理 - Tab10：操作记录
     */
    function getCrmComLogList_action()
    {

        if ($_POST['id']){

            $crmM       =   $this->MODEL('crm');

            $comId      =   $_POST['id'];
            $page       =   $_POST['page'];
            $limit      =   10;

            $where      =   array('uid' => $comId);

            $comLogNum  =   $this->obj->select_num('crm_comlog', $where);

            if ($_POST['crm_s_time'] && $_POST['crm_e_time']){

                $where['PHPYUNBTWSTATR_A']  =   '';
                $where['ctime'][]           =   array('>', strtotime($_POST['crm_s_time']), '');
                $where['ctime'][]           =   array('<', strtotime($_POST['crm_e_time']) + 86400, '');
                $where['PHPYUNBTWEND_A']    =   '';
            }else if ($_POST['crm_s_time']){

                $where['ctime']             =   array('>', strtotime($_POST['crm_s_time']));
            }else if ($_POST['crm_e_time']){

                $where['ctime']             =   array('<', strtotime($_POST['crm_e_time']) + 86400);
            }

            $where['orderby']   =   array('ctime, desc');

            $comLogTotal=   $this->obj->select_num('crm_comlog', $where);

            $pageTotal  =   ceil($comLogTotal / $limit);

            $where['limit'] =   array(($page - 1) * $limit, $limit);

            $start      =   ($page - 1) * $limit + 1;

            if ($page < $pageTotal){

                $end    =   $start + 9;
            }else{

                $end    =   $comLogTotal;
            }

            $comLogList =   $crmM->getComLogList($where, array('uid' => $comId));
            $data       =   array(

                'comLogList'    =>  $comLogList,
                'start'         =>  $start,
                'end'           =>  $end,
                'comLogNum'     =>  $comLogNum,
                'comLogTotal'   =>  $comLogTotal,
                'pageTotal'     =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    /**
     * @desc 客户预警信息
     */
    public function abnormal_action()
    {

        if ($_POST){

            $comIds     =   $this->getMyCustomer();

            $typeStr    =   intval($_POST['type']);
            $time       =   intval($_POST['time']);
            $limit      =   isset($post['limit']) ? $post['limit'] : 10;

            $data       =   array('comIds' => $comIds, 'time' => $time, 'limit' => $limit);

            if ($typeStr == 1) {                    //  收到简历

                $this->sqResume($data);
            } elseif ($typeStr == 2) {              //  简历未查看

                $this->notViewResume($data);
            } elseif ($typeStr == 3) {              //  简历下载

                $this->downResume($data);
            } elseif ($typeStr == 4) {              //  客户登录

                $this->loginLog($data);
            } elseif ($typeStr == 5) {              //  被投递岗位

                $this->sqJob($data);
            } elseif ($typeStr == 6) {              //  被拨号岗位

                $this->jobTelLog($data);
            } elseif ($typeStr == 7) {              //  被拨号企业

                $this->comTelLog($data);
            } elseif ($typeStr == 8) {              //  删除简历投递

                $this->sqResumeDel($data);
            } elseif ($typeStr == 9) {              //  删除简历下载

                $this->downResumeDel($data);

            }
        }else{

            $this->yuntpl(array('admin/crm_abnormal_list'));
        }
    }

    //  我的客户UID
    private function getMyCustomer()
    {

        $comList    =   $this->obj->select_all('company', array('crm_uid' => $_SESSION['auid']), '`uid`');
        if (!empty($comList)){
            $comIds =   array();
            foreach ($comList as $k => $v) {
                $comIds[]   =   $v['uid'];
            }
            return  $comIds;
        }
    }

    //  客户信息
    private function getComList($list, $tongjiM)
    {

        $uids       =   array();
        $jobIdArr   =   array();
        foreach ($list as $v) {

            $uids[]     =   $v['uid'];
            $jobIdArr[] =   $v['job_id'];
        }
        $com        =   $tongjiM->tjtotal('company', array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`name`, `login_date`'));
        $member     =   $tongjiM->tjtotal('member', array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`username`'));

        if ($jobIdArr) {
            $job    =   $tongjiM->tjtotal('company_job', array('id' => array('in', pylode(',', $jobIdArr))), array('field' => '`id`,`name`'));
        }

        foreach ($list as $k => $v) {
            foreach ($com as $val) {
                if ($v['uid'] == $val['uid']) {

                    $list[$k]['name']       =   $val['name'];
                    $list[$k]['login_date'] =   date('Y-m-d H:i:s', $val['login_date']);
                }
            }
            foreach ($member as $mval) {
                if ($v['uid'] == $mval['uid']) {
                    $list[$k]['username']   =   $mval['username'];
                }
            }

            if (!empty($job)) {
                foreach ($job as $jval) {
                    if ($v['job_id'] == $jval['id']) {
                        $list[$k]['job_name']   =   $jval['name'];
                    }
                }
            }
        }
        return $list;
    }

    //  查询时间
    private function timeNumber($a, $where, $column)
    {
        if ($a == 1) {
            // 昨天
            $stime = strtotime('yesterday');
            $etime = strtotime('today');
            $where['PHPYUNBTWSTART_A'] = '';
            $where[$column][] = array('>', $stime);
            $where[$column][] = array('<', $etime);
            $where['PHPYUNBTWEND_A'] = '';
        } elseif ($a == 2) {
            // 今天
            $stime = strtotime('today');
            $where[$column] = array('>', $stime);
        } elseif ($a == 3) {
            // 一周内
            $stime = strtotime('-7 day');
            $where[$column] = array('>', $stime);
        } elseif ($a == 4) {
            // 一个月内
            $stime = strtotime('-30 day');
            $where[$column] = array('>', $stime);
        } elseif ($a == 5) {
            // 三个月内
            $stime = strtotime('-90 day');
            $where[$column] = array('>', $stime);
        } else {
            if (stripos($a, '|') != false) {
                // 因分页链接处理过的页码
                $date = explode('|', $a);
                $stime = trim($date[0]);
                $etime = trim($date[1]);
            } else {
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

    //  收到简历
    private function sqResume($data = array())
    {
        $tjM    =   $this->MODEL('tongji');

        $comIds =   $data['comIds'];
        $limit  =   $data['limit'];
        $time   =   $data['time'];

        $where  =   array('com_id' => array('in', pylode(',', $comIds)));
        $where  =   $this->timeNumber($time, $where, 'datetime');  // 默认查一周内数据

        $where['groupby']   =   'com_id';
        $where['orderby']   =   array('num');
        $where['limit']     =   $limit;

        $rows   =   $tjM->tjtotal('userid_job', $where, array('field' => 'COUNT(*) AS `num`,`com_id` AS `uid`'));

        $res['list'] = $this->getComList($rows, $tjM);
        echo json_encode($res);
    }

    //  未查看简历
    private function notViewResume($data = array())
    {

        $tjM    =   $this->MODEL('tongji');

        $comIds =   $data['comIds'];
        $limit  =   $data['limit'];
        $time   =   $data['time'];

        $where  =   array('com_id' => array('in', pylode(',', $comIds)), 'is_browse' => 1);
        $where  =   $this->timeNumber($time, $where, 'datetime');  // 默认查一周内数据

        $where['groupby']   =   'com_id';
        $where['orderby']   =   array('num');
        $where['limit']     =   $limit;

        $rows   =   $tjM->tjtotal('userid_job', $where, array('field' => 'COUNT(*) AS `num`,`com_id` AS `uid`'));

        $res['list']    =   $this->getComList($rows, $tjM);
        echo json_encode($res);
    }

    //  简历下载
    private function downResume($data = array())
    {
        $tjM    =   $this->MODEL('tongji');

        $comIds =   $data['comIds'];
        $limit  =   $data['limit'];
        $time   =   $data['time'];

        $where  =   array('comid' => array('in', pylode(',', $comIds)));
        $where  =   $this->timeNumber($time, $where, 'downtime');  // 默认查一周内数据

        $where['groupby']   =   'comid';
        $where['orderby']   =   array('num');
        $where['limit']     =   $limit;

        $rows   =   $tjM->tjtotal('down_resume', $where, array('field' => 'COUNT(*) AS `num`,`comid` AS `uid`'));

        $res['list'] = $this->getComList($rows, $tjM);
        echo json_encode($res);
    }

    //  客户登录
    private function loginLog($data = array())
    {
        $tjM    =   $this->MODEL('tongji');

        $comIds =   $data['comIds'];
        $limit  =   $data['limit'];
        $time   =   $data['time'];

        $where  =   array('uid' => array('in', pylode(',', $comIds)), 'opera' => 32, 'type' => 1, 'content' => array('like', '登录'), 'usertype' => 2);
        $where  =   $this->timeNumber($time, $where, 'ctime');

        $where['groupby']   =   'uid';
        $where['orderby']   =   array('num');
        $where['limit']     =   $limit;


        $rows   =   $tjM->tjtotal('member_log',$where, array('field'=>'COUNT(*) AS `num`,`uid`'));

        $res['list']    =   $this->getComList($rows, $tjM);
        echo json_encode($res);
    }

    //  职位投递
    private function sqJob($data = array())
    {
        $tjM    =   $this->MODEL('tongji');

        $comIds =   $data['comIds'];
        $limit  =   $data['limit'];
        $time   =   $data['time'];

        $where  =   array('com_id' => array('in', pylode(',', $comIds)));
        $where  =   $this->timeNumber($time, $where, 'datetime');

        $where['groupby']   =   'job_id';
        $where['orderby']   =   array('num');
        $where['limit']     =   $limit;

        $rows   =   $tjM->tjtotal('userid_job',$where, array('field'=>'COUNT(*) AS `num`,`com_id` AS `uid`,`job_id`'));

        $res['list']    =   $this->getComList($rows, $tjM);
        echo json_encode($res);
    }

    //  职位拨号
    private function jobTelLog($data = array())
    {

        $tjM    =   $this->MODEL('tongji');

        $comIds =   $data['comIds'];
        $limit  =   $data['limit'];
        $time   =   $data['time'];

        $where  =   array('comid' => array('in', pylode(',', $comIds)), 'jobid' => array('notnull'));
        $where  =   $this->timeNumber($time, $where, 'ctime');

        $where['groupby']   =   'jobid';
        $where['orderby']   =   array('num');
        $where['limit']     =   $limit;

        $rows   =   $tjM->tjtotal('job_tellog', $where, array('field' => 'COUNT(*) AS `num`,`comid` AS `uid`,`jobid` AS `job_id`'));

        $res['list'] = $this->getComList($rows, $tjM);

        echo json_encode($res);
    }

    //  企业拨号
    private function comTelLog($data = array())
    {

        $tjM    =   $this->MODEL('tongji');

        $comIds =   $data['comIds'];
        $limit  =   $data['limit'];
        $time   =   $data['time'];

        $where  =   array('comid' => array('in', pylode(',', $comIds)));
        $where  =   $this->timeNumber($time, $where, 'ctime');

        $where['groupby']   =   'comid';
        $where['orderby']   =   array('num');
        $where['limit']     =   $limit;

        $rows   =   $tjM->tjtotal('job_tellog', $where, array('field' => 'COUNT(*) AS `num`,`comid` AS `uid`,`jobid` AS `job_id`'));

        $res['list'] = $this->getComList($rows, $tjM);

        echo json_encode($res);
    }

    //  删除申请简历
    private function sqResumeDel($data = array())
    {

        $tjM    =   $this->MODEL('tongji');

        $comIds =   $data['comIds'];
        $limit  =   $data['limit'];
        $time   =   $data['time'];

        $where  =   array('isdel' => 2,'com_id' => array('in', pylode(',', $comIds)));
        $where  =   $this->timeNumber($time, $where, 'datetime');

        $where['groupby']   =   'com_id';
        $where['orderby']   =   array('num');
        $where['limit']     =   $limit;

        $rows   =   $tjM->tjtotal('userid_job', $where, array('field' => 'COUNT(*) AS `num`,`com_id` AS `uid`'));

        $res['list'] = $this->getComList($rows, $tjM);

        echo json_encode($res);
    }

    //  删除下载简历
    private function downResumeDel($data = array())
    {

        $tjM    =   $this->MODEL('tongji');

        $comIds =   $data['comIds'];
        $limit  =   $data['limit'];
        $time   =   $data['time'];

        $where  =   array('isdel' => 2,'comid' => array('in', pylode(',', $comIds)));
        $where  =   $this->timeNumber($time, $where, 'downtime');

        $where['groupby']   =   'comid';
        $where['orderby']   =   array('num');
        $where['limit']     =   $limit;

        $rows   =   $tjM->tjtotal('down_resume', $where, array('field' => 'COUNT(*) AS `num`,`comid` AS `uid`'));

        $res['list'] = $this->getComList($rows, $tjM);

        echo json_encode($res);
    }

    //  预警分析
    public function abnormalDetail_action()
    {
        $typeStr    =   intval($_GET['type']);
        $rows       =   array();

        $title = $desc = '';

        if ($_GET['uid']) {

            $comInfo=   $this->obj->select_once('company', array('uid' => $_GET['uid']), '`name`');
        }
        if ($_GET['job_id']){

            $jobInfo=   $this->obj->select_once('company_job', array('id' => $_GET['job_id']), '`name`,`com_name`');
        }

        if ($typeStr == 1) {

            $rows   =   $this->sqResumeDetail();
            $title  =   '简历投递分析 - '.$comInfo['name'];
        } else if ($typeStr == 2) {

            $rows   =   $this->notViewResumeDetail();
            $title  =   '未查看简历投递分析 - '.$comInfo['name'];
        } else if ($typeStr == 3) {

            $rows   =   $this->downResumeDetail();
            $title  =   '简历下载分析 - '.$comInfo['name'];
        } else if ($typeStr == 4) {

            $rows   =   $this->loginLogDetail();
            $title  =   '客户登录分析 - '.$comInfo['name'];
        } else if ($typeStr == 5) {

            $rows   =   $this->sqResumeDetail();
            $title  =   '职位投递分析 - '.$jobInfo['com_name'].' - '.$jobInfo['name'];
        } else if ($typeStr == 6) {

            $rows   =   $this->telLogDetail();
            $title  =   '岗位拨号分析 - '.$jobInfo['com_name'].' - '.$jobInfo['name'];
        } else if ($typeStr == 7) {

            $rows   =   $this->telLogDetail();
            $title  =   '企业拨号分析 - '.$comInfo['name'];
        } else if ($typeStr == 8) {

            $rows   =   $this->sqResumeDelDetail();
            $title  =   '删除投递分析 - '.$comInfo['name'];
        } else if ($typeStr == 9) {

            $rows   =   $this->downResumeDelDetail();
            $title  =   '删除下载分析 - '.$comInfo['name'];
        }
        $this->yunset(array('rows' => $rows, 'title' => $title, 'desc' => $desc));
        $this->yuntpl(array('admin/crm_abnormal_detail'));
    }

    // 自定义事件格式化
    private function diytime($a)
    {

        if (stripos($a, '|') != false) {

            // 因分页链接处理过的页码
            $date   =   explode('|', $a);
            $stime  =   trim($date[0]);
            $etime  =   trim($date[1]);
        } else {

            // 原始参数
            $date   =   explode('~', $a);
            $stime  =   strtotime(trim($date[0]));
            $etime  =   strtotime(trim($date[1]));
        }

        return $stime.'|'.$etime;
    }

    // 通过IP获取地区
    private function getCityByIp($ip)
    {

        $local  =   '';
        $url    =   "https://whois.pconline.com.cn/ipJson.jsp?ip=" . $ip . "&json=true";
        $ret    =   $this->transCodeToUTF8(curlGet($url));

        if (false !== $ret) {
            $output = json_decode($ret, true);
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
        $encode =   mb_detect_encoding($str, ['ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5', 'EUC-CN']);
        return 'UTF-8' === $encode ? $str : mb_convert_encoding($str, 'UTF-8', $encode);
    }

    //  收到简历、被投递岗位分析
    private function sqResumeDetail()
    {
        $rows   =   array();
        $where  =   array('com_id' => $_GET['uid']);
        if ($_GET['job_id']){

            $where['job_id']    =   $_GET['job_id'];
        }
        // 默认查一周内数据
        $where  =   $this->timeNumber($_GET['time'], $where, 'datetime');
        $urlarr =   $_GET;

        $urlarr['page'] =   '{{page}}';
        if (strlen($urlarr['time']) > 1) {
            $urlarr['time'] =   $this->diytime($urlarr['time']);
        }
        $pageurl    =   Url($_GET['m'], $urlarr, 'admin');

        //提取分页
        $pageM      =   $this->MODEL('page');
        $pages      =   $pageM->pageList('userid_job', $where, $pageurl, $_GET['page']);

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby']   =   array('id,desc');
            $where['limit']     =   $pages['limit'];

            $jobM   =   $this->MODEL('job');
            $list   =   $jobM->getSqJobList($where, array('utype' => 'admin'));

            foreach ($list as $k => $v) {

                $rows[$k]['uid']    =   $v['uid'];
                $rows[$k]['name']   =   $v['name'];
                $rows[$k]['comid']  =   $v['com_id'];
                $rows[$k]['jobname']=   $v['job_name'];
                $rows[$k]['comname']=   $v['com_name'];
                $rows[$k]['time']   =   date('Y-m-d H:i:s', $v['datetime']);
                $rows[$k]['body']   =   $v['body'];
            }
        }
        return $rows;
    }

    //  未查看简历分析
    private function notViewResumeDetail()
    {
        $rows   =   array();
        $where  =   array('com_id' => $_GET['uid'], 'is_browse' => 1);
        // 默认查一周内数据
        $where  =   $this->timeNumber($_GET['time'], $where, 'datetime');
        $urlarr =   $_GET;

        $urlarr['page'] =   '{{page}}';
        if (strlen($urlarr['time']) > 1) {
            $urlarr['time'] =   $this->diytime($urlarr['time']);
        }
        $pageurl    =   Url($_GET['m'], $urlarr, 'admin');

        //提取分页
        $pageM      =   $this->MODEL('page');
        $pages      =   $pageM->pageList('userid_job', $where, $pageurl, $_GET['page']);

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby']   =   array('id,desc');
            $where['limit']     =   $pages['limit'];

            $jobM   =   $this->MODEL('job');
            $list   =   $jobM->getSqJobList($where, array('utype' => 'admin'));

            foreach ($list as $k => $v) {

                $rows[$k]['uid']    =   $v['uid'];
                $rows[$k]['name']   =   $v['name'];
                $rows[$k]['comid']  =   $v['com_id'];
                $rows[$k]['jobname']=   $v['job_name'];
                $rows[$k]['comname']=   $v['com_name'];
                $rows[$k]['time']   =   date('Y-m-d H:i:s', $v['datetime']);
                $rows[$k]['body']   =   $v['body'];
            }
        }
        return $rows;
    }

    //  简历下载分析
    private function downResumeDetail()
    {

        $rows   =   array();
        $where  =   array('comid' => $_GET['uid']);
        $where  =   $this->timeNumber($_GET['time'], $where, 'downtime');
        $urlarr =   $_GET;
        $urlarr['page'] =   '{{page}}';
        if (strlen($urlarr['time']) > 1) {
            $urlarr['time'] =   $this->diytime($urlarr['time']);
        }
        $pageurl=   Url($_GET['m'], $urlarr, 'admin');
        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('down_resume', $where, $pageurl, $_GET['page']);
        if ($pages['total'] > 0) {

            $where['orderby']   =   array('id,desc');
            $where['limit']     =   $pages['limit'];
            $dowM   =   $this->MODEL('downresume');
            $list   =   $dowM->getList($where, array('utype' => 'admin'));

            foreach ($list['list'] as $k => $v) {

                $rows[$k]['uid']    =   $v['uid'];
                $rows[$k]['name']   =   $v['username'];
                $rows[$k]['comid']  =   $v['comid'];
                $rows[$k]['jobname']=   $v['job_name'];
                $rows[$k]['comname']=   $v['com_name'];
                $rows[$k]['time']   =   date('Y-m-d H:i:s', $v['downtime']);
            }
        }
        return $rows;
    }

    //  客户登录分析
    private function loginLogDetail()
    {

        $rows   =   array();

        $where  =   array('opera' => 32, 'type' => 1, 'content' => array('like', '登录'), 'uid' => $_GET['uid'], 'usertype' => 2);
        $where  =   $this->timeNumber($_GET['time'], $where, 'ctime');

        $urlarr =   $_GET;
        $urlarr['page'] =   '{{page}}';
        if (strlen($urlarr['time']) > 1) {
            $urlarr['time'] =   $this->diytime($urlarr['time']);
        }
        $pageurl=   Url($_GET['m'], $urlarr, 'admin');
        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('member_log', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['orderby']   =   array('id,desc');
            $where['limit']     =   $pages['limit'];
            $logM   =   $this->MODEL('log');
            $list   =   $logM->getMemlogList($where, array('utype' => 'admin'));

            $ip     =   array();
            foreach ($list as $k => $v) {

                $rows[$k]['uid']        =   $v['uid'];
                $rows[$k]['jobname']    =   '';
                $rows[$k]['comid']      =   $v['uid'];
                $rows[$k]['content']    =   str_replace(array('登录成功', '账号'), '', $v['content']);
                $rows[$k]['time']       =   date('Y-m-d H:i:s', $v['ctime']);
                $rows[$k]['ip']         =   $v['ip'];
                $rows[$k]['name']       =   '';
                $rows[$k]['comname']    =   $v['comname'];

                $ip[]   =   $v['ip'];
            }
            $city   =   array();
            $ip     =   array_unique($ip);
            foreach ($ip as $v) {

                $city[$v]   =   $this->getCityByIp($v);
            }
            foreach ($list as $k => $v) {
                if ($city[$v['ip']]) {

                    $rows[$k]['city'] = $city[$v['ip']];
                }
            }
        }
        return $rows;
    }

    //  拨号分析
    private function telLogDetail()
    {

        $rows   =   array();
        if ($_GET['job_id']) {

            $where  =   array('jobid' => $_GET['job_id']);
        } elseif ($_GET['uid']) {

            $where  =   array('comid' => $_GET['uid']);
        }

        $where  =   $this->timeNumber($_GET['time'], $where, 'ctime');
        $urlarr =   $_GET;

        $urlarr['page'] =   '{{page}}';

        if (strlen($urlarr['time']) > 1) {

            $urlarr['time'] =   $this->diytime($urlarr['time']);
        }
        $pageurl=   Url($_GET['m'], $urlarr, 'admin');

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('job_tellog', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['orderby']   =   array('id,desc');
            $where['limit']     =   $pages['limit'];

            $jobM   =   $this->MODEL('job');
            $list   =   $jobM->getTelLogs($where, array('utype' => 'admin'));

            $ip     =   array();

            foreach ($list as $k => $v) {

                $rows[$k]['uid']    =   $v['uid'];
                $rows[$k]['name']   =   $v['username'];
                $rows[$k]['comid']  =   $v['comid'];
                $rows[$k]['jobname']=   $v['job_name'];
                $rows[$k]['comname']=   $v['com_name'];
                $rows[$k]['time']   =   date('Y-m-d H:i:s', $v['ctime']);
                $rows[$k]['ip']     =   $v['ip'];

                $ip[]   =   $v['ip'];
            }

            $city   =   array();
            $ip     =   array_unique($ip);
            foreach ($ip as $v) {

                $city[$v] = $this->getCityByIp($v);
            }
            foreach ($list as $k => $v) {
                if ($city[$v['ip']]) {

                    $rows[$k]['city'] = $city[$v['ip']];
                }
            }
        }
        return $rows;
    }

    //  删除申请分析
    private function sqResumeDelDetail()
    {

        $rows   =   array();

        $where  =   array('com_id' => $_GET['uid'], 'isdel' => 2);
        $where  =   $this->timeNumber($_GET['time'], $where, 'datetime');

        $urlarr =   $_GET;

        $urlarr['page'] =   '{{page}}';
        if (strlen($urlarr['time']) > 1) {
            $urlarr['time'] =   $this->diytime($urlarr['time']);
        }
        $pageurl=   Url($_GET['m'], $urlarr, 'admin');

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('userid_job', $where, $pageurl, $_GET['page']);
        if ($pages['total'] > 0) {

            $where['orderby']   =   array('id,desc');
            $where['limit']     =   $pages['limit'];

            $jobM   =   $this->MODEL('job');
            $list   =   $jobM->getSqJobList($where, array('utype' => 'admin'));

            foreach ($list as $k => $v) {

                $rows[$k]['uid']    =   $v['uid'];
                $rows[$k]['name']   =   $v['name'];
                $rows[$k]['comid']  =   $v['com_id'];
                $rows[$k]['jobname']=   $v['job_name'];
                $rows[$k]['comname']=   $v['com_name'];
                $rows[$k]['time']   =   date('Y-m-d H:i:s', $v['datetime']);
            }
        }
        return $rows;
    }

    // 删除简历下载分析
    private function downResumeDelDetail()
    {

        $rows   =   array();
        $where  =   array('comid' => $_GET['uid'], 'isdel' => 2);
        $where  =   $this->timeNumber($_GET['time'], $where, 'downtime');

        $urlarr =   $_GET;
        $urlarr['page'] =   '{{page}}';
        if (strlen($urlarr['time']) > 1) {

            $urlarr['time'] =   $this->diytime($urlarr['time']);
        }
        $pageurl=   Url($_GET['m'], $urlarr, 'admin');

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('down_resume', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['orderby']   =   array('id,desc');
            $where['limit']     =   $pages['limit'];

            $dowM   =   $this->MODEL('downresume');
            $list   =   $dowM->getList($where, array('utype' => 'admin'));

            foreach ($list['list'] as $k => $v) {

                $rows[$k]['uid']    =   $v['uid'];
                $rows[$k]['name']   =   $v['username'];
                $rows[$k]['comid']  =   $v['comid'];
                $rows[$k]['jobname']=   $v['job_name'];
                $rows[$k]['comname']=   $v['com_name'];
                $rows[$k]['time']   =   date('Y-m-d H:i:s', $v['downtime']);
            }
        }
        return $rows;
    }

    function clueCustomer_action()
    {

        if ($_POST['uids']) {

            $uids   =   @explode(',', $_POST['uids']);
            $auid   =   intval($_SESSION['auid']);
            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->clueCustomer($uids, $auid);
            if ($_POST['from'] == 1){
                $return['url']  =   'index.php?m=crm_my_customer';
            }else{
                $return['url']  =   'index.php?m=crm_org_kh';
            }

            echo json_encode($return);
        }
    }

    function getCrmLink_action()
    {

        $uid    =   intval($_POST['uid']);
        $crmM   =   $this->MODEL('crm');
        $crmLink=   $crmM->getCrmLink(array('uid' => $uid));
        $comLink=   $this->obj->select_once('company', array('uid' => $uid), '`linkman`,`linktel`,`linkphone`');
        if (!empty($comLink)) {
            $tel        =   !empty($comLink['linktel']) ? $comLink['linktel'] : $comLink['linkphone'];
            $newArr     =   array('id' => 0, 'link_man' => $comLink['linkman'], 'link_mobile' => $tel);
            array_unshift($crmLink, $newArr);
        }

        echo json_encode($crmLink);
    }

}
?>