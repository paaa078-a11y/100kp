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

    function getData_action(){

        $AdminM         =   $this->MODEL('admin');

        $cacheM         =   $this->MODEL('cache');
        $cache          =   $cacheM->GetCache(array('crm'));

        $crmdata        =   $cache['crmdata'];
        $crmclass_name  =   $cache['crmclass_name'];

        foreach ($crmdata as $key => $value) {
            $selectArr  =   array();
            foreach ($value as $k => $v) {
                $selectArr[] = array('val'=>$v,'name'=>$crmclass_name[$v]);
            }

            $data[$key] =   $selectArr;
        }

        // $adminUserList  =   $AdminM->getList(array(), array('field' => '`uid`,`name`'));

        $crmUser        =   $AdminM->getList(array('is_crm' => '1', 'uid' => array('<>', $_SESSION['auid'])));

        $data['crmUser']=   !empty($crmUser)?$crmUser:array();

        $this->render_json(0, '', $data);
    }

    /**
     * @desc - CRM - 我的客户
     */
    function index_action()
    {

        $comM       =   $this->MODEL('company');
        $userInfoM  =   $this->MODEL('userinfo');
        $crmM       =   $this->MODEL('crm');

        $month      =   get_this_month(time());
        $todayStart =   strtotime('today');

        if (isset($_POST['crm_uid']) && intval($_POST['crm_uid']) > 0) {// 下属管理员uid

            $where['crm_uid'] = intval($_POST['crm_uid']);
        } else if ($_POST['crm_uid'] == 'all') {                        // 全部下属

            $where['PHPYUNBTWSTART_W']  =   '';
            $where['crm_uid'][]         =   array('>', 0, '');
            $where['crm_uid'][]         =   array('in', $_POST['orgUids'], '');
            $where['PHPYUNBTWEND_W']    =   '';
        } else {

            $where['crm_uid']   =   $_SESSION['auid'];
        }

        if (isset($_POST['jtmodule']) && !empty($_POST['jtmodule'])) {

            $jtmodule   =   $_POST['jtmodule'];
            $jttype     =   $_POST['jttype'];
            $source     =   $_POST['source'];

            if ($jtmodule == 'mykh') {          // 点击我的客户
                $uidArrM = $this->jobtaiMykhHandle($jttype, $source);

                if ($jttype == 'jxgj' || $jttype == 'gyx') {
                    if (!empty($_POST['jtips'])) { // 搜索检索ID
                        $jtips = $_POST['jtips'];
                        $uidArrM = array_filter($uidArrM, function($var) use ($jtips) {
                            return $var['type'] == $jtips;
                        });
                    }
                    $uidArrU = array_keys($uidArrM);
                    $where['uid'] = array('in', pylode(',', $uidArrU));
                } else {
                    $uidArrU = $uidArrM;
                    $where['uid'] = array('in', pylode(',', $uidArrU));
                }

            } elseif ($jtmodule == 'mykhtj') {  // 点击我的客户统计

                $where['uid'] = array('in', pylode(',', $this->jobtaiMykhtjHandle($jttype)));
            }
        }

        if (isset($_POST['clue']) && !empty($_POST['clue'])) {
            $where['clue']  = intval($_POST['clue']);
        }

        if (isset($_POST['crmType']) && !empty($_POST['crmType'])) {

            $where['crm_type']  =   intval($_POST['crmType']);
        }

        if (isset($_POST['crm_status']) && !empty($_POST['crm_status'])) {

            $where['crm_status']=   intval($_POST['crm_status']);
        }

        if (isset($_POST['rating']) && !empty($_POST['rating'])) {

            $rating                 =   intval($_POST['rating']);
            if ($_POST['rating'] == -1) {
                $where['PHPYUNBTWSTART_RD']  =   '';
                $where['vipetime'][]        =   array('<', strtotime(date('Y-m-d')), 'AND');
                $where['vipetime'][]        =   array('>', '0', 'AND');
                $where['PHPYUNBTWEND_RD']    =   '';
            } elseif($_POST['rating'] > 0){

                $where['rating']        =   $rating;
                if (!isset($where['vipetime'])){

                    $where['PHPYUNBTWSTART_B']  =   '';
                    $where['vipetime'][]        =   array('=', 0, '');
                    $where['vipetime'][]        =   array('>', strtotime('today'), 'OR');
                    $where['PHPYUNBTWEND_B']    =   '';
                }

                if (isset($_POST['vipstime']) && $_POST['vipstime'] != ''){

                    $where['PHPYUNBTWSTART_B1']  =   '';
                    $where['vipstime'][]  =   array('>', 0, '');
                    $where['vipstime'][]  =   array('<', strtotime('-'.$_POST['vipstime'].' days'), '');
                    $where['PHPYUNBTWEND_B1']  =   '';

                    $where['crm_time']  =   array('<', strtotime('-'.$_POST['vipstime'].' days'), '');
                }
            }else{

                if (isset($_POST['vipstime']) && $_POST['vipstime'] != ''){

                    $where['PHPYUNBTWSTART_B1'] =   '';
                    $where['vipetime'][]  =   array('>', 0, '');
                    $where['vipetime'][]  =   array('<', strtotime('-'.$_POST['vipstime'].' days'), '');
                    $where['PHPYUNBTWEND_B1']   =   '';

                    $where['crm_time']          =   array('<', strtotime('-'.$_POST['vipstime'].' days'), '');
                }else{
                    $where['PHPYUNBTWSTART_B']  =   '';
                    $where['vipetime']          =   array(
                        0   =>  array('<', strtotime('today'), ''),
                        1   =>  array('>', 0, '')
                    );
                    $where['PHPYUNBTWEND_B']    =   '';
                }
            }

        }

        if (isset($_POST['vipetime']) && !empty($_POST['vipetime'])) {

            $etime  =   intval($_POST['vipetime']);

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
                $where['vipetime'][]        =   array('>=', strtotime('today'), 'AND');
                $where['vipetime'][]        =   array('<', strtotime($num, strtotime('tomorrow')), 'AND');
                $where['PHPYUNBTWEND_C']    =   '';
            }
        }

        if (isset($_POST['yyzz_status']) && !empty($_POST['yyzz_status'])) {

            $yyzz_status    =   intval($_POST['yyzz_status']);

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
                    if (!empty($uidArrU)) {
                        in_array($cv['uid'], $uidArrU) && $uidArrA[] = $cv['uid'];
                    } else {
                        $uidArrA[] = $cv['uid'];
                    }
                }

                if ($yyzz_status == 4){

                    $where['uid']   =   array('notin', pylode(',', $uidArrA));
                }else{

                    $where['uid']   =   array('in', pylode(',', $uidArrA));
                }
            }

        }

        if (isset($_POST['lastF']) && !empty($_POST['lastF'])) {

            $lastF = intval($_POST['lastF']);
            $where['PHPYUNBTWSTART_D'] = '';
            $where['f_time'][] = array('isnull', '', '');
            $where['f_time'][] = array('<', strtotime('-' . $lastF . ' days'), 'OR');
            $where['PHPYUNBTWEND_D'] = '';
        }

        if (isset($_POST['lastFtime']) && !empty($_POST['lastFtime'])) {

            $lastFtime  =   intval($_POST['lastFtime']);

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

        }

        if (isset($_POST['nextFtime']) && !empty($_POST['nextFtime'])) {

            $nextFtime  =   intval($_POST['nextFtime']);

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
                if (!empty($uidArrU)) {
                    in_array($tv['comid'], $uidArrU) && $uidArrN[] = $tv['comid'];
                } else {
                    $uidArrN[] = $tv['comid'];
                }
            }
            $where['uid']       =   array('in', pylode(',', $uidArrN));
        }

        if (isset($_POST['hasJob']) && !empty($_POST['hasJob'])) {
            $hasJob =   intval($_POST['hasJob']);
            if ($hasJob == 1) {
                $where['jobtime']   =   array('>', 0);
            } elseif ($hasJob == 2) {
                $where['jobtime']   =   0;
            }
        }
        if($_POST['fact_status']){
            if($_POST['fact_status'] == '1'){
                $where['fact_status'] = 1;
            }elseif($_POST['fact_status'] == '2'){
                $where['fact_status'] = 0;
            }
        }
        if (isset($_POST['loginDate']) && !empty($_POST['loginDate'])) {

            $loginDate = intval($_POST['loginDate']);
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
                if (!empty($_POST['logintime_s']) && !empty($_POST['logintime_e'])){

                    $loginStart     =   $_POST['logintime_s'];
                    $loginEnd       =   $_POST['logintime_e'];
                    $where['PHPYUNBTWSTART_C1'] = '';
                    $where['login_date'][] = array('>=', strtotime($loginStart.' 00:00:00'));
                    $where['login_date'][] = array('<=', strtotime($loginEnd.' 23:59:59'));
                    $where['PHPYUNBTWEND_C1'] = '';
                }
            }
        }

        if (!empty($_POST['regtime_s']) && !empty($_POST['regtime_e'])) {

            $regStart   =   strtotime($_POST['regtime_s'].' 00:00:00');
            $regEnd     =   strtotime($_POST['regtime_e'].' 23:59:59');

            $mWhere['PHPYUNBTWSTART_H'] =   '';
            $mWhere['reg_date'][]       =   array('>', $regStart, 'AND');
            $mWhere['reg_date'][]       =   array('<', $regEnd, 'AND');
            $mWhere['PHPYUNBTWEND_H']   =   '';
            $memberList =   $userInfoM->getList($mWhere, array('field' => 'uid'));

            $uidArrB    =   array();
            foreach ($memberList as $mv) {
                if (!empty($uidArrU)) {
                    in_array($mv['uid'], $uidArrU) && $uidArrB[] = $mv['uid'];
                } else {
                    $uidArrB[] = $mv['uid'];
                }
            }

            $where['uid']       =   array('in', pylode(',', $uidArrB));
        }

        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {

            $keywordStr =   trim($_POST['keyword']);
            $typeStr    =   intval($_POST['ktype']);

            $typeStr == 4 && $where['uid'] = '-1';
            if ($typeStr == 1) {

                $where['name']      =   array('like', $keywordStr);
            } elseif ($typeStr == 2) {

                $where['linkman']   =   array('like', $keywordStr);
            } elseif ($typeStr == 3) {

                $where['linktel']   =   $keywordStr;
            } elseif ($typeStr == 4) {
                if (!empty($uidArrU)) {
                    in_array($keywordStr, $uidArrU) && $where['uid'] = $keywordStr;
                } else {
                    $where['uid'] = $keywordStr;
                }
            } elseif ($typeStr == 5) {
                $where['address'] = array('like', $keywordStr);
            }
        }

        if (isset($_POST['r_status']) && $_POST['r_status'] != '') {
            $where['r_status']  =   $_POST['r_status'];
        }

        $perPage = !empty($_POST['limit']) ? intval($_POST['limit']) : intval($this->config['sy_listnum']);
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company', $where, $_POST['page'], array('limit' => $perPage));

        if ($pages['total'] > 0) {
            if (isset($_POST['order'])) {
                if (isset($_POST['t'])) {

                    $where['orderby'] = $_POST['t'].', '.$_POST['order'];
                } else {

                    $where['orderby'] = 'crm_time,desc';
                }
            } else {
                $where['orderby'] = 'crm_time, DESC';
                if (!empty($_POST['jttype']) && $_POST['jttype'] == 'hy') {
                    $where['orderby'] = 'login_date, DESC';
                }
            }

            $where['limit'] =   $pages['limit'];
            $listA          =   $comM->getList($where, array('utype' => 'crm', 'self' => 1));
            $list   =   !empty($listA['list'])?$listA['list']:array();
            foreach ($list as $k => $v) {
                $list[$k]['wxBindmsg']  =   $this->wxBindState($v);
                $list[$k]['isvip']      =   $todayStart < $v['vipetime'] || ($v['vipetime'] == '0' && $v['rating'] == '0') ? 1 : 0;
                $list[$k]['vipetime_n'] =   date('Y-m-d', $v['vipetime']);
                $list[$k]['vipetime_status'] = 0;
                if ($v['vipetime'] >= $todayStart) {
                    $list[$k]['vipetime_status'] = 1;
                } else if ($v['vipetime'] < $todayStart && $v['vipetime'] != 0) {
                    $list[$k]['vipetime_status'] = 2;
                } else {
                    $list[$k]['vipetime_n'] = '永久';
                }
                $list[$k]['jobnum']     =   $v['jobnum'] > 0 ? $v['jobnum'] : 0;
                $list[$k]['zz_jobnum']  =   $v['zz_jobnum'] > 0 ? $v['zz_jobnum'] : 0;
                $list[$k]['crm_type']   =   $v['crm_type'] ? $v['crm_type'] : '--';
                $list[$k]['crm_status'] =   $v['crm_status'] ? $v['crm_status'] : '--';
                $list[$k]['crm_time_n'] =   date('Y-m-d', $v['crm_time']);
                $list[$k]['crm_pic_arr']=   !empty($v['c_pic_arr']) ? explode('&', $v['c_pic_arr']) : array();

                !empty($uidArrM) && $list[$k]['jtips'] = $uidArrM[$v['uid']]['tips'];
            }
        }

        $data['list']       =   !empty($list) ? $list : array();
        $data['total']      =   $pages['total'];
        $data['page_sizes'] =   $pages['page_sizes'];
        $data['page_size']  =   $perPage;
        $this->render_json(0, '', $data);
    }

    function getCustomerNum_action()
    {

        $crmM       =   $this->MODEL('crm');

        $defnumarr  =   array('companyAllNum' => 0, 'companyStatusNum1' => 0, 'companyStatusNum2' => 0, 'companyStatusNum3' => 0, 'companyStatusNum4' => 0);

        if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])){

            if ($_POST['crm_uid'] == 'all'){

                $crmUidArr  =   $_POST['orgUids'];
                $numarr     =   $crmM->getCustomerNum(array('type' => 2, 'crm_uid' => $crmUidArr));
            } else {

                $numarr     =   $crmM->getCustomerNum(array('type' => 1, 'crm_uid' => $_POST['crm_uid']));
            }
        }else{

            $numarr     =   $crmM->getCustomerNum(array('type' => 1, 'crm_uid' => $_SESSION['auid']));
        }

        $data['numarr'] = array_merge($defnumarr, $numarr);

        $this->render_json(0, '', $data);
    }

    function getStaticData_action()
    {

        $crmUserList=   $this->getAdminUser();
        $appkey     =   trim($this->config['sy_outcall_appkey']);
        $appsecret  =   trim($this->config['sy_outcall_appsecret']);
        $isOutcall  =   $appkey && $appsecret;

        if (!isset($_POST['source']) || $_POST['source'] != 'jobtai') {

            $cacheM =   $this->MODEL('cache');
            $cache  =   $cacheM->GetCache(array('crm'));
            $crmdata        =   $cache['crmdata'];
            $crmclass_name  =   $cache['crmclass_name'];

            foreach ($crmdata as $key => $value) {
                $selectArr  =   array();
                foreach ($value as $k => $v) {
                    $selectArr[]    =   array('val' => $v, 'name' => $crmclass_name[$v]);
                }
                $data[$key] =   $selectArr;
            }
            $vipEtime   =   array('5' => '已到期', '1' => '三天内', '2' => '七天内', '3' => '十五天内', '4' => '一月内');
            $yyzz       =   array('1' => '认证通过', '2' => '等待审核', '3' => '认证失败', '4' => '尚未认证');
            $lastFtime  =   array('1' => '从未跟进', '2' => '今日跟进', '3' => '三天前', '4' => '七天前', '5' => '一月前', '6' => '百日前');
            $nextFtime  =   array('1' => '今天', '2' => '明天', '3' => '三天内', '4' => '七天内', '5' => '一月内');
            $loginDate  =   array('1' => '今天', '2' => '本周', '3' => '本月', '4' => '一个月前', '5' => '三个月前', '6' => '半年前', '7' => '自定义');
            $hasJob     =   array('1' => '是', '2' => '否');
        }

        $ratingM        =   $this->MODEL('rating');
        if (!empty($this->config['com_rating'])) {
            $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`', 'rating' => $this->config['com_rating']));
        } else {
            $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
        }

        // 下属客户
        $data['adminUserList']  =   $crmUserList;
        $data['yyzz_statusarr'] =   $yyzz;
        $data['ratingArr']      =   $ratingArr;
        $data['vip_etimearr']   =   $vipEtime;
        $data['last_ftimearr']  =   $lastFtime;
        $data['next_ftimearr']  =   $nextFtime;
        $data['login_datearr']  =   $loginDate;
        $data['has_jobarr']     =   $hasJob;
        $data['config']         =   array(
            'com_free_status'   =>  $this->config['com_free_status'],
            'com_social_credit' =>  $this->config['com_social_credit'],
            'com_cert_owner'    =>  $this->config['com_cert_owner'],
            'com_cert_wt'       =>  $this->config['com_cert_wt'],
            'com_cert_other'    =>  $this->config['com_cert_other'],
        );
        $data['is_outcall']     =   $isOutcall;
        $this->render_json(0, 'ok', $data);
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
            $this->render_json(400, '业务员未填写手机号');exit;
        }
        $mobile = trim($_POST['mobile']);
        if (!$mobile || !CheckMobile($mobile)) {
            $this->render_json(400, '手机号格式错误');exit;
        }
        if (!$_POST['uid']) {
            $this->render_json(400, '企业uid不能为空');exit;
        }

        $apiurl = 'https://u.phpyun.com/call';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            $this->render_json(400, '请先完成外呼配置！');exit;
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

            $data = $id ? $id : 0;

            $this->render_json(200,'呼叫成功',$data);exit;
        } else {

            $data = $id ? $id : 0;

            $this->render_json(400,$result['message'],$data);exit;
        }
    }

    function add_action()
    {

        $ratingM    =   $this->MODEL('rating');
        $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort,asc'), array('field' => '`id`,`name`'));

        $cacheM     =   $this->MODEL('cache');
        $cache      =   $cacheM->GetCache(array('crm','com','hy'));

        $adminM     =   $this->MODEL('admin');
        $crmUser    =   $adminM->getList(array('is_crm' => '1', 'uid' => array('<>', $_SESSION['auid'])));

        $client_type=   array();
        foreach ($cache['crmdata']['client_type'] as $k => $v) {
            $client_type[] = array(
                'val'=>$v,
                'name'=>$cache['crmclass_name'][$v],
            );
        }

        $client_status=   array();
        foreach ($cache['crmdata']['client_status'] as $sk => $sv) {
            $client_status[] = array(
                'val'=>$sv,
                'name'=>$cache['crmclass_name'][$sv],
            );
        }

        $industry_index=   array();
        foreach ($cache['industry_index'] as $hk => $hv) {
            $industry_index[] = array(
                'val'=>$hv,
                'name'=>$cache['industry_name'][$hv],
            );
        }
        $jobpr=   array();
        foreach ($cache['comdata']['job_pr'] as $pk => $pv) {
            $jobpr[] = array(
                'val'=>$pv,
                'name'=>$cache['comclass_name'][$pv],
            );
        }
        $job_mun=   array();
        foreach ($cache['comdata']['job_mun'] as $mk => $mv) {
            $job_mun[] = array(
                'val'=>$mv,
                'name'=>$cache['comclass_name'][$mv],
            );
        }

        $data['client_type']    = $client_type;
        $data['client_status']  = $client_status;
        $data['job_mun']        = $job_mun;
        $data['jobpr']          = $jobpr;
        $data['industry_index'] = $industry_index;
        $data['rating_list']    = $rating;
        $data['crmUser']        = $crmUser;

        $this->render_json(0,'',$data);
    }

    /**
     * @desc CRM - 录客户
     */
    function addsave_action()
    {

        if ($_POST) {

            $clue   =   intval($_POST['clue']);

            $username   =   trim($_POST['moblie']);

            $time   =   time();
            $ip     =   fun_ip_get();
            // 密码为空的时候 随机一个密码
            if (empty($_POST['password'])) {

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

                if (mb_strlen($_POST['password']) < 6 || mb_strlen($_POST['password']) > 20) {

                    $this->render_json(1,'登录密码请控制在6-20位字符');
                }

                $mPost  =   array(

                    'username'      =>  $username,
                    'companyName'   =>  trim($_POST['name']),
                    'moblie'        =>  trim($_POST['moblie']),
                    'email'         =>  trim($_POST['email'])
                );

                $result     =   $userInfoM->addMemberCheck($mPost);
                if ($result['msg']) {
                    $this->render_json(1,$result['msg']);
                }

                if ($this->config['sy_uc_type'] == 'uc_center') {
                    $this->obj->uc_open();
                    $user = uc_get_user($username);
                    if (is_array($user)) {
                        $this->render_json(1,'该会员已经存在！');
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
                        $this->render_json(1,$data['msg']);
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

            $udata = array_filter($udata);

            $nid    =   $userInfoM->addInfo(array('mdata' => $mdata, 'udata' => $udata, 'sdata' => $sdata));

            if ($nid) {

                $crmM   =   $this->MODEL('crm');

                $logData        =   array(
                    'auid'      =>  $_SESSION['auid'],
                    'uid'       =>  $nid,
                    'type'      =>  10,
                    'content'   =>  '录入新客户（UID：'.$nid.'）',
                    'ctime'     =>  time()
                );
                if ($crmUid != $_SESSION['auid']){
                    $logData['remark']  =   '协助录入；';
                }
                if ($clue == 2){
                    $logData['remark']  .=  '当前录入线索库';
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
                    $lContent   .=  "，标记客户标签为：".$crmCache['crmclass_name'][$_POST['crm_status']];
                }

                // 添加CRM操作记录；
                $crmM->addCrmLog($lContent, 1, $nid, $crmUid);
            }

            $msg    =   $nid ? '录入成功！' : '录入失败，请重试！';


            $errcode   =  $nid ? 0 : 1;



            $this->render_json($errcode,$msg);

        }

    }

    /**
     * @desc CRM - 新增客户 - 用户名检测
     */
    function check_action()
    {

        $moblie   =   trim($_POST['moblie']);
        $userInfoM  =   $this->MODEL('userinfo');
        $check      =   $userInfoM->addMemberCheck(array('moblie' => $moblie));

        if(!empty($check['msg'])){
            $error = 1;
            $msg = $check['msg'];
        }else{
            $error = 0;
            $msg = '';
        }

        $this->render_json($error,$msg);
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

        $this->render_json(0,'',$coms);
    }

    /**
     * @desc 权益提醒记录
     */
    function notice_action()
    {

        if (!isset($_POST['all']) || intval($_POST['all']) != 1) {

            if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])) {

                $where = array('crm_uid' => $_POST['crm_uid']);
            } else {

                $where = array('crm_uid' => $_SESSION['auid']);
            }
        }

        if (isset($_POST['keyword']) && $_POST['keyword']) {

            $keywordStr =   trim($_POST['keyword']);
            $typeStr    =   intval($_POST['crm_type']);

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

        }
        if($_POST['status']){
            $where['status']   =   $_POST['status'];
        }
        if (!empty($_POST['send_time_s']) && !empty($_POST['send_time_e'])){

            $send_time_s = strtotime($_POST['send_time_s'].' 00:00:00');
            $send_time_e = strtotime($_POST['send_time_e'].' 23:59:59');

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['send_time'][]       =   array('>=', $send_time_s, '');
            $where['send_time'][]       =   array('<=', $send_time_e, '');
            $where['PHPYUNBTWEND_A']    =   '';
        }
        $crmM   =   $this->MODEL('crm');
        $pageM  =   $this->MODEL('page');

        $pages  =   $pageM->adminPageList('rating_notice', $where, $_POST['page'], array('limit' => $_POST['limit']));

        if ($pages['total'] > 0) {

            if ($_POST['order']) {

                $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                $urlarr['order']    =   $_POST['order'];
                $urlarr['t']        =   $_POST['t'];
            } else {

                $where['orderby']   =   array('status, asc', 'send_time, desc');
            }

            $where['limit'] =   $pages['limit'];

            $list   =   $crmM->getNoticeList($where, array('utype' => 'crm'));
        }


        $data['list']       =   !empty($list) ? $list : array();
        $data['total']      =   $pages['total'];
        $data['page_sizes'] =   $pages['page_sizes'];
        $data['page_size'] =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }

    function getNoticeStaticData_action()
    {

        $crmM   =   $this->MODEL('crm');

        if (!isset($_POST['all']) || intval($_POST['all']) != 1) {
            if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])) {

                $data['numarr'] = $crmM->getNoticeNum(array('crm_uid' => $_POST['crm_uid']));
            } else {

                $data['numarr'] = $crmM->getNoticeNum(array('crm_uid' => $_SESSION['auid']));
            }
        } else {

            $data['numarr'] = $crmM->getNoticeNum();
        }
        $this->render_json(0, 'ok', $data);
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

        $this->render_json($return['errcode'],$return['errmsg']);

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
            foreach ($Cache['crmdata']['client_status'] as $v) {//  客户标签

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
            $adminUser  =   $adminM->getList(array('uid' => array('>', 0)), array('field' => '`uid`,`name`'));

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


            $dsh    =   $wtg    =   $yxj    =   0;
            $dsh    =   $this->obj->select_num('company_job', array('uid' => $comId, 'state' => 0));
            $wtg    =   $this->obj->select_num('company_job', array('uid' => $comId, 'state' => 3));
            $yxj    =   $this->obj->select_num('company_job', array('uid' => $comId, 'status' => 1));


            $wts    =   $yts    =   0;
            $wts    =   $this->obj->select_num('wxpub_twtask', array('cuid' => $comId, 'status' => 0));
            $yts    =   $this->obj->select_num('wxpub_twtask', array('cuid' => $comId, 'status' => 1));

            $data       =   array(
                'Info'          =>  $comInfo,
                'auid'          =>  $_SESSION['auid'],

                'noticeCode'    =>  Url('ajax',array('c'=>'pubqrcode','toc'=>'nlresume','totype' => 'wxpubtool')),
                'noticeUrl'     =>  Url('index', array('c' => 'nlresume')),

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

                'links'         =>  $comLinks,

                'dsh'           =>  $dsh,
                'wtg'           =>  $wtg,
                'yxj'           =>  $yxj,

                'wts'           =>  $wts,
                'yts'           =>  $yts,
            );

            $this->render_json(0, '', $data);
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
            $this->render_json(0, '', $data);
        }
    }

    /**
     * @desc 客户管理 - 一键登录
     */
    function Imitate_action()
    {

        $userinfoM = $this->MODEL('userinfo');
        $member = $userinfoM->getInfo(array('uid'=> intval($_POST['uid'])),array('field'=>'`uid`,`username`,`salt`,`email`,`password`,`usertype`,`did`'));
        $this->cookie->unset_cookie($_SESSION['auid']);
        $this->cookie->add_cookie($member['uid'],$member['username'],$member['salt'],$member['email'],$member['password'],2,$this->config['sy_logintime'],$member['did'],'1');
        $typeStr = trim($_POST['type']);
        $url = '';
        if(!empty($typeStr)){
            if ($typeStr == 'job') {
                $url = 'index.php?c='.$typeStr;
            }else{
                $url = 'index.php?c='.$typeStr;
            }
        }
        $logM = $this->MODEL('log');
        $content = '管理员'.$_SESSION['ausername'].'登录企业账户(ID:'.$member['uid'].')';
        $logM->addAdminLog($content);
        $this->render_json(0, '', $this->config['sy_weburl'].'/member/'.$url);
    }

    /**
     * @desc 客户管理 - Tab1：跟进记录
     */
    function getFollowList_action()
    {

        if ($_POST['id']){

            $crmM   =   $this->MODEL('crm');
            $where  =   array('uid' => $_POST['id']);
            if ($_POST['followTab'] == 1) {

                $where['auid']  =   $_SESSION['auid'];
            }
            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('crmnew_concern', $where, $_POST['page'], array('limit' => $_POST['limit']));
            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['t']) {

                        $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                    }
                } else {

                    $where['orderby']   =   'ftime, desc';
                }
                $where['limit'] =   $pages['limit'];
                $FollowList     =   $crmM->getConcernList($where, array('utype' => 'crm_com_info'));
            }

            $data   =   array(

                'followList'    =>  $FollowList,
                'total'         =>  $pages['total'],
                'page_sizes'    =>  $pages['page_sizes'],
                'page_size'     =>  $pages['page_size'],
                'last_page'     =>  $pages['last_page']
            );
            $this->render_json(0, '', $data);
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
            $this->render_json(0, '', $info);
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

            if ($return['errcode'] == 9){

                $this->admin_json(0,$return['msg']);
            }else{

                $this->render_json(1,$return['msg']);
            }
        }
    }

    /**
     * @desc 客户管理 - Tab1：添加crm企业联系人; Tab3: 添加/修改 客户联系人
     */
    function addCrmLink_action()
    {

        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];
            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->insertCrmLink($data);
            if ($return['errcode'] == 9){

                $this->admin_json(0, $return['msg'], array('nid' => $return['nid']));
            } else if ($return['errcode'] == 8){

                $this->render_json(1, $return['msg']);
            } else if ($return['errcode'] == 7){

                $this->render_json(2, $return['msg']);
            }
        } else {

            $this->render_json(1, '数据错误请重试!');
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

                $this->admin_json(0, "批注成功（跟进ID：" . $_POST['id'] . "）", $return);
            }else{

                $this->render_json(1, $return['msg']);
            }
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
                $this->admin_json(0, '跟进批注（ID：' . $_POST['id'] . '）删除成功');
            } else {
                $this->render_json(1, $return['msg']);
            }
        }
    }

    /**
     * @desc 客户管理 - Tab2：短信模板
     */
    function getMsgTpl_action()
    {

        if (!empty($_POST)) {

            $data   =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $msgTpl =   $crmM->getMsgTpl($data);
            foreach ($msgTpl as $k => $v) {
                if ($v['name'] == 'msgcrmlink') {

                    $msgContent =   $v['content'];
                    $msgClass   =   $v['id'];
                }
            }
            $data   =   array(

                'msgTpl'    =>  $msgTpl,
                'msgContent'=>  $msgContent,
                'msgClass'  =>  $msgClass
            );
            $this->render_json(0, '', $data);
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

            if ($return['errcode'] == 9){

                $this->admin_json(0, $return['msg']);
            }else{

                $this->render_json(1, $return['msg']);
            }
        }
    }

    /**
     * @desc 客户管理 - Tab2：短信记录
     */
    function getMsgList_action()
    {

        if ($_POST['uid']){

            $crmM   =   $this->MODEL('crm');
            $where  =   array('uid' => $_POST['uid'], 'port' => 11);
            if ($_POST['msgTab'] == 1) {

                $where['cuid']  =   $_SESSION['auid'];
            }

            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('moblie_msg', $where, $_POST['page'], array('limit' => $_POST['limit']));
            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['t']) {

                        $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                    }
                } else {

                    $where['orderby']   =   'ctime, desc';
                }
                $where['limit'] =   $pages['limit'];
                $msgList    =   $crmM->getMsgList($where);
            }
            $data   =   array(

                'msgList'   =>  $msgList,
                'total'     =>  $pages['total'],
                'page_sizes'=>  $pages['page_sizes'],
                'page_size'=>  $pages['page_size']
            );
            $this->render_json(0, '', $data);
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
            $this->render_json(0, '', $data);
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
            if ($return['errcode'] == 9) {

                $this->admin_json(0, $return['msg']);
            } else {

                $this->render_json(1, $return['msg']);
            }
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

            if ($return['errcode'] == 9) {

                $this->admin_json(0, $return['msg']);
            } else {

                $this->render_json(1, $return['msg']);
            }
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 更新客户等级/标签
     */
    function upStatusType_action()
    {
        if (!empty($_POST)) {

            $data = $_POST;
            $data['auid'] = $_SESSION['auid'];

            $crmM = $this->MODEL('crm');
            $return = $crmM->upComST($data);
        } else {

            $return['errcode'] = 8;
            $return['msg'] = '数据错误请重试';
        }
        if ($return['errcode'] == 9) {

            $this->admin_json(0, $return['msg']);
        } else {

            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 暂停招聘
     */
    function stopCom_action()
    {
        if (!empty($_POST)) {

            $res = $this->MODEL('company')->jugdeSuspend(array('uid'=>$_POST['uid']));

            if ($res['errcode'] != 9) {
                $this->render_json(1, $res['msg']);
            }

            $data   =   $_POST;
            $data['auid']   =   $_SESSION['auid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->stopCom($data);

            if ($return['errcode'] == 9){
                $this->MODEL('company')->setComStaticSub($_POST['uid']);
                $this->admin_json(0, $return['msg'], $return);
            } else {

                $this->render_json(1, $return['msg']);
            }
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
            if ($return['errcode'] == 9){

                $this->admin_json(0, $return['msg']);
            }else{

                $this->render_json(1, $return['msg']);
            }
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

            if ($return['errcode'] == 9){

                $this->admin_json(0, '客户（UID：'.$_POST['uid'].'）联系方式更新成功');
            }else{
                $this->render_json(0, $return['msg']);
            }
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

            if ($return['errcode'] == 9){

                $this->admin_json(0, $return['msg']);
            }else{

                $this->render_json(1, $return['msg']);
            }
        }
    }

    /**
     * @desc 客户管理 - Tab3：客户详情 - 删除客户联系人
     */
    function delCrmLink_action()
    {

        $crmM   =   $this->MODEL('crm');
        $return =   $crmM->delCrmLink(array('id' => $_POST['id'], 'uid' => $_POST['uid'], 'auid' => $_SESSION['auid']));

        if ($return['errcode'] == 9){

            $this->admin_json(0, '客户联系方式（ID：'.$_POST['id'].'）删除成功');
        }else{

            $this->render_json(1, $return['msg']);
        }
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

        }else{
            $return['errcode'] = 8;
            $return['msg'] = '数据错误请重试';
        }
        if ($return['errcode'] == 9) {

            $this->render_json(0, $return['msg']);
        } else {

            $this->render_json(1, $return['msg']);
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

        }else{
            $return['errcode'] = 8;
            $return['msg'] = '数据错误请重试';
        }
        if ($return['errcode'] == 9) {

            $this->render_json(0, $return['msg']);
        } else {

            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * @desc 客户管理 - Tab4：在招职位
     */
    /*function getJobList_action()
    {

        if ($_POST['id']){

            $crmM   =   $this->MODEL('crm');


            $where  =   array('uid' => $_POST['id']);
            if ($_POST['keyword']){

                $where['name']  =   array('like', $_POST['keyword']);
            }

            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('company_job', $where, $_POST['page'], array('limit' => $_POST['limit']));
            $jobList = array();
            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['t']) {
                        $_POST['t']         =   $_POST['t'] == 'lastＵpdate' ? 'lastupdate' : $_POST['t'];
                        $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                    }
                } else {
                    $where['orderby']   =   'id, desc';
                }
                $where['limit'] = $pages['limit'];
                $jobList = $crmM->getJobList($where, array('uid' => $_POST['id'], 'xcx' => 1));
            }
            $data   =   array(

                'jobList'   =>  $jobList,
                'total'     =>  $pages['total'],
                'page_sizes'=>  $pages['page_sizes'],
                'page_size'=>  $pages['page_size']
            );
            $this->render_json(0, '', $data);
        }
    }*/

    /**
     * @desc 客户管理 - Tab4：在招职位 - 上下架
     */
    /*function setJobStatus_action()
    {

        if (!empty($_POST)) {

            $status =   $_POST['status'] == 2 ? 0 : 1;
            $jobId  =   intval($_POST['id']);
            $this->obj->update_once('company_job', array('status' => $status), array('id' => $jobId));
            if ($status == 0) {
                $logMsg = "职位(ID" . $jobId . ")上架成功";
            } else {
                $logMsg = "职位(ID" . $jobId . ")下架成功";
            }
            $this->admin_json(0, $logMsg);
        }
    }*/

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
            if ($return['errcode'] == 9){
                $this->admin_json(0, $return['msg']);
            }else {
                $this->render_json(1, $return['msg']);
            }
        }
    }

    /**
     * @desc 客户管理 - Tab4：在招职位 - 匹配简历
     */
    function getResumeList_action()
    {

        if ($_POST['uid']){

            $resumeM    =   $this->MODEL('resume');

            $comId      =   $_POST['uid'];
            $jobId      =   $_POST['id'];

            $noUidS     =   $blackUidS  =   $applyUidS  =   $inviteUidS =   array();

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

            $noUidS     =   array_values(array_merge($blackUidS, $applyUidS, $inviteUidS));
            $numWhere   =   array('defaults' => 1, 'state' => 1, 'status' => 1, 'r_status' => 1);

            if (!empty($noUidS)){

                $numWhere['uid']    =   array('notin', @implode(',', $noUidS));
            }

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

            if ($_POST['edu']) {

                $where  .=  " AND a.`edu` =".$_POST['edu'];
            }

            if ($_POST['word']) {

                $where  .=  " AND a.`exp` =".$_POST['word'];
            }

            if($_POST['label']){

                $where  .=  ' and a.label='.intval($_POST['label']);
            }

            if ($_POST['integrity']) {
                if ($_POST['integrity']  ==  55) {

                    $where  .=  " and a.integrity = 55";
                } else if ($_POST['integrity'] == 65) {
                    $where  .=  " and a.integrity = 65";
                } else {
                    include(CONFIG_PATH . 'db.data.php');
                    $integrity_val = $arr_data['integrity_val'];
                    $where  .=  " and a.integrity >= " . $integrity_val[$_POST['integrity']];
                }
            }

            if ($_POST['time']) {
                $timeStr    =   intval($_POST['time']);
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

            if ($_POST['keyword']) {

                $keywordStr =   trim($_POST['keyword']);
                $keywordArr =   explode(' ', $keywordStr);

                if (count($keywordArr) > 1) {
                    $workWhere['PHPYUNBTWSTART_work']   =   '';
                    foreach ($keywordArr as $kv) {
                        $workWhere['name'][]    =   array('like', trim($kv), 'OR');
                        $workWhere['title'][]   =   array('like', trim($kv), 'OR');
                    }
                    $workWhere['PHPYUNBTWEND_work']     =   '';
                } else {
                    $workWhere  =   array(
                        'name'  =>  array('like', $keywordStr),
                        'title' =>  array('like', $keywordStr, 'OR'),
                    );
                }
                $work   =   $resumeM->getResumeWorks($workWhere, 'eid');
                if ($work) {
                    $eids   =   array();
                    foreach ($work as $v) {
                        $eids[] =   $v['eid'];
                    }
                }

                if (count($keywordArr) > 1) {
                    $eduWhere['PHPYUNBTWSTART_edu'] =   '';
                    foreach ($keywordArr as $kv) {
                        $eduWhere['name'][]         =   array('like', trim($kv), 'OR');
                        $eduWhere['specialty'][]    =   array('like', trim($kv), 'OR');
                    }
                    $eduWhere['PHPYUNBTWEND_edu']   = '';
                } else {
                    $eduWhere   =   array(
                        'name'      =>  array('like', $keywordStr),
                        'specialty' =>  array('like', $keywordStr, 'OR')
                    );
                }
                $edu    =   $resumeM->getResumeEdus($eduWhere, 'eid');
                if ($edu) {
                    $eids   =   array();
                    foreach ($edu as $v) {
                        $eids[] =   $v['eid'];
                    }
                }

                $userInfoM  =   $this->MODEL('userinfo');

                if (count($keywordArr) > 1) {
                    $mwhere['PHPYUNBTWSTART_mem']   =   '';
                    foreach ($keywordArr as $kv) {
                        $eduWhere['description'][]  =   array('like', trim($kv), 'OR');
                    }
                    $mwhere['PHPYUNBTWEND_mem']     =   '';
                } else {
                    $mwhere['description']  =   array('like', $keywordStr);
                }
                if (!empty($mwhere)) {
                    $uidList=   $userInfoM->getUserInfoList($mwhere, array('usertype' => 1, 'field' => '`uid`'));
                    if (!empty($uidList)) {
                        foreach ($uidList as $uv) {
                            $mUids[] = $uv['uid'];
                        }
                    }
                }

                if (count($keywordArr) > 1) {
                    $where  .=  " and (";
                    foreach ($keywordArr as $k => $kv) {
                        if ($k + 1 < count($keywordArr)) {
                            $where .= " a.name like '%$kv%' or a.content like '%$kv%' or";
                        } else {
                            $where .= " a.name like '%$kv%' or a.content like '%$kv%'";
                        }
                    }
                } else {
                    $where  .=  " and (a.name like '%$keywordStr%' or a.content like '%$keywordStr%'";
                }

                if (!empty($mUids)) {
                    $where  .=  " or a.uid in (" . pylode(',', $mUids) . ")";
                }

                if (!empty($eids)) {
                    $where  .=  " or a.id in (" . pylode(',', $eids) . ")";
                }

                $where  .=  ")";
            }

            $countSql = "select count(*) as num from `".$this->def."resume_expect` a{$city_job_class} where {$where}";

            $pageM  =   $this->MODEL('page');
            $pages          =   $pageM->adminPageList('resume_expect', $where,$_POST['page'],array('limit'=>$_POST['limit'],'sql'=>$countSql));
            $order = '';
            //分页数大于0的情况下 执行列表查询
            if ($pages['total'] > 0) {
                //limit order 只有在列表查询时才需要
                if ($_POST['order']) {
                    if ($_POST['t'] == 'time') {
                        $order .= "order by a.lastupdate " . $_POST['order'];
                    } else {
                        $order .= 'order by a.' . $_POST['t'] . ' ' . $_POST['order'];
                    }
                } else {
                    $order .= 'order by a.lastupdate desc';
                }
                $sql =  "select a.* from `" . $this->def . "resume_expect` a{$city_job_class} where {$where} {$order} limit {$pages['limit'][0]},{$pages['limit'][1]}";
                $List=  $resumeM->getList(array(), array('cache' => 1, 'utype' => 'admin', 'sql' => $sql, 'work' => 1, 'chat_num' => 1));
                $resumeList =  $List['list'];
            }
            $data       =   array(

                'resumeList'    =>  $resumeList,
                'total'  =>  $pages['total'],
                'page_sizes' =>  $pages['page_sizes'],
                'page_size' =>  $pages['page_size']
            );

            $this->render_json(0, '', $data);
        }
    }

    /**
     * @desc 客户管理 - Tab4：在招职位 - 匹配简历
     */
    function getAutoResumeList_action()
    {

        if ($_POST['uid']){

            $resumeM    =   $this->MODEL('resume');

            $comId      =   $_POST['uid'];
            $jobId      =   $_POST['id'];

            $noUidS     =   $blackUidS  =   $applyUidS  =   $inviteUidS =   array();

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

            $noUidS     =   array_values(array_merge($blackUidS, $applyUidS, $inviteUidS));
            $numWhere   =   array('defaults' => 1, 'state' => 1, 'status' => 1, 'r_status' => 1);

            if (!empty($noUidS)){

                $numWhere['uid']    =   array('notin', @implode(',', $noUidS));
            }

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

            if ($_POST['edu']) {

                $where  .=  " AND a.`edu` =".$_POST['edu'];
            }

            if ($_POST['word']) {

                $where  .=  " AND a.`exp` =".$_POST['word'];
            }

            if($_POST['label']){

                $where  .=  ' and a.label='.intval($_POST['label']);
            }

            if ($_POST['integrity']) {
                if ($_POST['integrity']  ==  55) {

                    $where  .=  " and a.integrity = 55";
                } else if ($_POST['integrity'] == 65) {
                    $where  .=  " and a.integrity = 65";
                } else {
                    include(CONFIG_PATH . 'db.data.php');
                    $integrity_val = $arr_data['integrity_val'];
                    $where  .=  " and a.integrity >= " . $integrity_val[$_POST['integrity']];
                }
            }

            if ($_POST['time']) {
                $timeStr    =   intval($_POST['time']);
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

            if ($_POST['keyword']) {

                $keywordStr =   trim($_POST['keyword']);
                $keywordArr =   explode(' ', $keywordStr);

                if (count($keywordArr) > 1) {
                    $workWhere['PHPYUNBTWSTART_work']   =   '';
                    foreach ($keywordArr as $kv) {
                        $workWhere['name'][]    =   array('like', trim($kv), 'OR');
                        $workWhere['title'][]   =   array('like', trim($kv), 'OR');
                    }
                    $workWhere['PHPYUNBTWEND_work']     =   '';
                } else {
                    $workWhere  =   array(
                        'name'  =>  array('like', $keywordStr),
                        'title' =>  array('like', $keywordStr, 'OR'),
                    );
                }
                $work   =   $resumeM->getResumeWorks($workWhere, 'eid');
                if ($work) {
                    $eids   =   array();
                    foreach ($work as $v) {
                        $eids[] =   $v['eid'];
                    }
                }

                if (count($keywordArr) > 1) {
                    $eduWhere['PHPYUNBTWSTART_edu'] =   '';
                    foreach ($keywordArr as $kv) {
                        $eduWhere['name'][]         =   array('like', trim($kv), 'OR');
                        $eduWhere['specialty'][]    =   array('like', trim($kv), 'OR');
                    }
                    $eduWhere['PHPYUNBTWEND_edu']   = '';
                } else {
                    $eduWhere   =   array(
                        'name'      =>  array('like', $keywordStr),
                        'specialty' =>  array('like', $keywordStr, 'OR')
                    );
                }
                $edu    =   $resumeM->getResumeEdus($eduWhere, 'eid');
                if ($edu) {
                    $eids   =   array();
                    foreach ($edu as $v) {
                        $eids[] =   $v['eid'];
                    }
                }

                $userInfoM  =   $this->MODEL('userinfo');
                if (count($keywordArr) > 1) {
                    $mwhere['PHPYUNBTWSTART_mem']   =   '';
                    foreach ($keywordArr as $kv) {
                        $eduWhere['description'][]  =   array('like', trim($kv), 'OR');
                    }
                    $mwhere['PHPYUNBTWEND_mem']     =   '';
                } else {
                    $mwhere['description']  =   array('like', $keywordStr);
                }
                if (!empty($mwhere)) {
                    $uidList=   $userInfoM->getUserInfoList($mwhere, array('usertype' => 1, 'field' => '`uid`'));
                    if (!empty($uidList)) {
                        foreach ($uidList as $uv) {
                            $mUids[] = $uv['uid'];
                        }
                    }
                }

                if (count($keywordArr) > 1) {
                    $where  .=  " and (";
                    foreach ($keywordArr as $k => $kv) {
                        if ($k + 1 < count($keywordArr)) {
                            $where .= " a.name like '%$kv%' or a.content like '%$kv%' or";
                        } else {
                            $where .= " a.name like '%$kv%' or a.content like '%$kv%'";
                        }
                    }
                } else {
                    $where  .=  " and (a.name like '%$keywordStr%' or a.content like '%$keywordStr%'";
                }

                if (!empty($mUids)) {
                    $where  .=  " or a.uid in (" . pylode(',', $mUids) . ")";
                }

                if (!empty($eids)) {
                    $where  .=  " or a.id in (" . pylode(',', $eids) . ")";
                }

                $where  .=  ")";
            }

            $order      =   ' order by a.lastupdate desc';
            $sql        =   "select a.* from `".$this->def."resume_expect` a{$city_job_class} where {$where} {$order} limit 100";
            $List       =   $resumeM->getList(array(), array('cache' => 1, 'utype' => 'admin', 'sql' => $sql, 'work' => 1, 'chat_num' => 1));
            $resumeList =  $List['list'];
            $data       =   array(
                'resumeList'    =>  $resumeList
            );
            $this->render_json(0, '', $data);
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

            if ($return['errcode'] == 9){

                $this->admin_json(0, $return['msg'], array('wap_url' => $return['wap_url']));
            } else {

                $this->render_json(1, $return['msg']);
            }
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

            if ($return['errcode'] == 9){

                $this->admin_json(0, $return['msg']);
            }else{
                $this->render_json(1, $return['msg']);
            }
        }
    }

    /**
     * @desc 客户管理 - Tab5：推荐候选人
     */
    function getRecList_action()
    {

        if ($_POST['id']){

            $crmM   =   $this->MODEL('crm');
            $comId  =   $_POST['id'];
            $where  =   array('uid' => $comId);
            if (isset($_POST['time']) && !empty($_POST['time'])) {

                $start  =   strtotime($_POST['time'].' 00:00:00');
                $end    =   strtotime($_POST['time'].' 23:59:59');
                $where['PHPYUNBTWSTART']    =   '';
                $where['atime'][]           =   array('>=', $start, '');
                $where['atime'][]           =   array('<=', $end, '');
                $where['PHPYUNBTWEND']      =   '';
            }
            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('crm_resume', $where, $_POST['page'], array('limit' => $_POST['limit']));
            $recList = array();
            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['t']) {
                        $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                    }
                } else {
                    $where['orderby']   =   'id, desc';
                }
                $where['limit'] = $pages['limit'];
                $recList=   $crmM->getRecLogList($where, array('uid' => $comId, 'type' => 'info'));
            }

            $data   =   array(
                'recList'   =>  $recList,
                'total'     =>  $pages['total'],
                'page_sizes'=>  $pages['page_sizes'],
                'page_size'=>  $pages['page_size']
            );
            $this->render_json(0, '', $data);
        }
    }

    /**
     * @desc 客户管理 - Tab5：删除推荐记录
     */
    function delRecResume_action()
    {

        if (isset($_POST['id']) && !empty($_POST['id'])){

            $idStr  =   pylode(',', $_POST['id']);

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->delRecResumeLog(array('id' => array('in', $idStr)));
            if ($return['errcode'] == 9){

                $this->admin_json(0, '简历推广记录（ID：'.$idStr.'）删除成功');
            }else{

                $this->render_json(1, $return['msg']);
            }
        }
    }

    /**
     * @desc 客户管理 - Tab5：推荐候选人 - 详情
     */
    function getRecInfo_action()
    {
        if ($_POST) {

            $crmM       =   $this->MODEL('crm');
            $return     =   $crmM->getRecInfo(array('id' => $_POST['id']), array('type' => 'info'));

            $data       =   array(

                'recInfo'       =>  $return['info'],
                'recResumes'    =>  $return['list']
            );
            $this->render_json(0, '', $data);
        }
    }

    /**
     * @desc 客户管理 - Tab6：推送历史
     */
    function getTwList_action()
    {

        if ($_POST['id']){

            $crmM   =   $this->MODEL('crm');
            $comId  =   $_POST['id'];
            $where  =   array('cuid' => $comId);


            if (isset($_POST['type']) && !empty($_POST['type'])){
                $where['type']  =   $_POST['type'];
            }
            if (isset($_POST['auid']) && !empty($_POST['auid'])){
                $where['auid']  =   $_POST['auid'];
            }
            if (isset($_POST['status']) && !empty($_POST['status'])){
                $where['status']=   $_POST['status'] == 1 ? 1 : 0;
            }
            if (isset($_POST['urgent']) && !empty($_POST['urgent'])){
                $where['urgent']    =   $_POST['urgent'];
            }
            if (isset($_POST['wcmoments']) && !empty($_POST['wcmoments'])){
                $where['wcmoments'] =   $_POST['wcmoments'];
            }
            if (isset($_POST['gzh']) && !empty($_POST['gzh'])){
                $where['gzh']       =   $_POST['gzh'];
            }
            if (isset($_POST['keyword']) && !empty($_POST['keyword'])){
                $where['jobname']   =   array('like', $_POST['keyword']);
            }
            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('wxpub_twtask', $where, $_POST['page'], array('limit' => $_POST['limit']));
            $twList = array();
            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['t']) {
                        $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                    }
                } else {
                    $where['orderby']   =   'id, desc';
                }
                $where['limit'] = $pages['limit'];
                $twList     =   $crmM->getTWList($where);
            }
            $data       =   array(

                'twList'    =>  $twList,
                'total'     =>  $pages['total'],
                'page_sizes'=>  $pages['page_sizes'],
                'page_size'=>  $pages['page_size']
            );

            $this->render_json(0, '', $data);
        }
    }

    /**
     * @desc 客户管理 - Tab7：订单记录
     */
    function getOrderList_action()
    {

        if ($_POST['id']){

            $crmM   =   $this->MODEL('crm');
            $comId  =   $_POST['id'];
            $where  =   array('uid' => $comId);

            if (isset($_POST['order_state']) && !empty($_POST['order_state'])) {
                $where['order_state']   =   $_POST['order_state'];
            }
            if (isset($_POST['order_type']) && !empty($_POST['order_type'])) {
                $where['order_type']    =   $_POST['order_type'];
            }
            if (isset($_POST['type']) && !empty($_POST['type'])) {
                $where['type']          =   $_POST['type'];
            }
            if (isset($_POST['time']) && !empty($_POST['time'])){

                $start  =   strtotime($_POST['time'][0]);
                $end    =   strtotime($_POST['time'][1].' 23:59:59');
                $where['PHPYUNBTWSTART']=   '';
                $where['order_time'][]  =   array('>=', $start, '');
                $where['order_time'][]  =   array('<=', $end, '');
                $where['PHPYUNBTWEND']  =   '';
            }
            if (isset($_POST['keyword']) && !empty($_POST['keyword'])){
                $where['order_id']      =   $_POST['keyword'];
            }
            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('company_order', $where, $_POST['page'], array('limit' => $_POST['limit']));
            $orderList = array();
            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['t']) {
                        $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                    }
                } else {
                    $where['orderby']   =   'id, desc';
                }
                $where['limit'] = $pages['limit'];
                $orderList      =   $crmM->getOrderList($where, array('uid' => $comId));
            }

            $data       =   array(
                'orderList' =>  $orderList,
                'total'     =>  $pages['total'],
                'page_sizes'=>  $pages['page_sizes'],
                'page_size'=>  $pages['page_size']
            );
            $this->render_json(0, '', $data);
        }
    }

    /**
     *  @desc 客户管理 - Tab8：用户日志
     */
    function getMemberLogList_action()
    {

        if ($_POST['id']){

            $crmM   =   $this->MODEL('crm');
            $comId  =   $_POST['id'];
            $where  =   array('uid' => $comId, 'usertype' => 2);
            if (isset($_POST['time']) && !empty($_POST['time'])){

                $start  =   strtotime($_POST['time'][0]);
                $end    =   strtotime($_POST['time'][1].' 23:59:59');
                $where['PHPYUNBTWSTART']=   '';
                $where['ctime'][]       =   array('>=', $start, '');
                $where['ctime'][]       =   array('<=', $end, '');
                $where['PHPYUNBTWEND']  =   '';
            }
            if (isset($_POST['keyword']) && !empty($_POST['keyword'])){
                $where['content']       =   array('like', $_POST['keyword']);
            }
            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('member_log', $where, $_POST['page'], array('limit' => $_POST['limit']));
            $logList = array();
            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['t']) {
                        $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                    }
                } else {
                    $where['orderby']   =   'id, desc';
                }
                $where['limit'] = $pages['limit'];
                $logList    =   $crmM->getMemberLogList($where, array('uid' => $comId));
            }

            $data       =   array(
                'logList'   =>  $logList,
                'total'     =>  $pages['total'],
                'page_sizes'=>  $pages['page_sizes'],
                'page_size'=>  $pages['page_size']
            );
            $this->render_json(0, '', $data);
        }
    }

    /**
     *  @desc 客户管理 - Tab9：用户轨迹
     */
    function getWebLogList_action()
    {

        if ($_POST['id']){

            $crmM   =   $this->MODEL('crm');

            $comId  =   $_POST['id'];
            $where  =   array('uid' => $comId, 'usertype' => 2);
            if (isset($_POST['time']) && !empty($_POST['time'])){

                $start  =   strtotime($_POST['time'][0]);
                $end    =   strtotime($_POST['time'][1].' 23:59:59');
                $where['PHPYUNBTWSTART']=   '';
                $where['time'][]        =   array('>=', $start, '');
                $where['time'][]        =   array('<=', $end, '');
                $where['PHPYUNBTWEND']  =   '';
            }

            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('web_log', $where, $_POST['page'], array('limit' => $_POST['limit']));
            $webList = array();
            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['t']) {
                        $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                    }
                } else {
                    $where['orderby']   =   'id, desc';
                }
                $where['limit'] = $pages['limit'];

                $webList    =   $crmM->getWebLogList($where, array('uid' => $comId));
            }

            $data       =   array(
                'webLogList'    =>  $webList,
                'total'         =>  $pages['total'],
                'page_sizes'    =>  $pages['page_sizes'],
                'page_size'    =>  $pages['page_size']
            );
            $this->render_json(0, '', $data);
        }
    }

    /**
     *  @desc 客户管理 - Tab10：操作记录
     */
    function getCrmComLogList_action()
    {

        if ($_POST['id']){

            $crmM   =   $this->MODEL('crm');
            $comId  =   $_POST['id'];
            $where  =   array('uid' => $comId);
            if (isset($_POST['time']) && !empty($_POST['time'])){

                $start  =   strtotime($_POST['time'][0]);
                $end    =   strtotime($_POST['time'][1].' 23:59:59');
                $where['PHPYUNBTWSTART']=   '';
                $where['ctime'][]       =   array('>=', $start, '');
                $where['ctime'][]       =   array('<=', $end, '');
                $where['PHPYUNBTWEND']  =   '';
            }
            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('crm_comlog', $where, $_POST['page'], array('limit' => $_POST['limit']));
            $optLogList = array();
            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['t']) {
                        $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                    }
                } else {
                    $where['orderby']   =   'id, desc';
                }
                $where['limit'] = $pages['limit'];
                $optLogList     =   $crmM->getComLogList($where, array('uid' => $comId));
            }

            $data       =   array(
                'optLogList'    =>  $optLogList,
                'total'         =>  $pages['total'],
                'page_sizes'    =>  $pages['page_sizes'],
                'page_size'    =>  $pages['page_size']
            );
            $this->render_json(0, '', $data);
        }
    }

    /**
     * @desc 客户预警信息
     */
    public function abnormal_action()
    {

        if (isset($_POST['all']) && $_POST['all'] == 1) {                   //  全部下属客户

            $crmUser    =   $this->getAdminUser();
            $crmUidArr  =   array();
            foreach ($crmUser as $k => $v) {

                $crmUidArr[]    =   $v['uid'];
            }
            $comIds = $this->getMyCustomer(array('in', pylode(',', $crmUidArr)));
        } else if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])) { //  下属客户

            $comIds = $this->getMyCustomer($_POST['crm_uid']);
        } else {                                                            //  我的客户

            $comIds = $this->getMyCustomer($_SESSION['auid']);
        }

        $typeStr=   intval($_POST['type']);
        $time   =   intval($_POST['time']);
        $limit  =   isset($post['limit']) ? $post['limit'] : 10;

        $data   =   array('comIds' => $comIds, 'time' => $time, 'limit' => $limit);

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
    }

    //  我的客户UID
    private function getMyCustomer($crmUid)
    {

        $comList    =   $this->obj->select_all('company', array('crm_uid' => $crmUid), '`uid`');
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

        $this->render_json(0,'',$res);
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
        $this->render_json(0,'',$res);
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
        $this->render_json(0,'',$res);
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
        $this->render_json(0,'',$res);
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
        $this->render_json(0,'',$res);
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

        $this->render_json(0,'',$res);
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

        $this->render_json(0,'',$res);
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

        $this->render_json(0,'',$res);
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

        $this->render_json(0,'',$res);
    }

    public function getAbDStaticData_action(){

        $typeStr    =   intval($_POST['type']);

        $title = $desc = '';

        if ($_POST['uid']) {

            $comInfo=   $this->obj->select_once('company', array('uid' => $_POST['uid']), '`name`');
        }
        if ($_POST['job_id']){

            $jobInfo=   $this->obj->select_once('company_job', array('id' => $_POST['job_id']), '`name`,`com_name`');
        }

        if ($typeStr == 1) {

            $title  =   '简历投递分析 - '.$comInfo['name'];
        } else if ($typeStr == 2) {

            $title  =   '未查看简历投递分析 - '.$comInfo['name'];
        } else if ($typeStr == 3) {

            $title  =   '简历下载分析 - '.$comInfo['name'];
        } else if ($typeStr == 4) {

            $title  =   '客户登录分析 - '.$comInfo['name'];
        } else if ($typeStr == 5) {

            $title  =   '职位投递分析 - '.$jobInfo['com_name'].' - '.$jobInfo['name'];
        } else if ($typeStr == 6) {

            $title  =   '岗位拨号分析 - '.$jobInfo['com_name'].' - '.$jobInfo['name'];
        } else if ($typeStr == 7) {

            $title  =   '企业拨号分析 - '.$comInfo['name'];
        } else if ($typeStr == 8) {

            $title  =   '删除投递分析 - '.$comInfo['name'];
        } else if ($typeStr == 9) {

            $title  =   '删除下载分析 - '.$comInfo['name'];
        }

        $data['title']      =   $title;
        $this->render_json(0,'',$data);
    }

    //  预警分析
    public function abnormalDetail_action()
    {
        $typeStr    =   intval($_POST['type']);
        $rows       =   array();

        if ($typeStr == 1) {

            $rows   =   $this->sqResumeDetail();
        } else if ($typeStr == 2) {

            $rows   =   $this->notViewResumeDetail();
        } else if ($typeStr == 3) {

            $rows   =   $this->downResumeDetail();
        } else if ($typeStr == 4) {

            $rows   =   $this->loginLogDetail();
        } else if ($typeStr == 5) {

            $rows   =   $this->sqResumeDetail();
        } else if ($typeStr == 6) {

            $rows   =   $this->telLogDetail();
        } else if ($typeStr == 7) {

            $rows   =   $this->telLogDetail();
        } else if ($typeStr == 8) {

            $rows   =   $this->sqResumeDelDetail();
        } else if ($typeStr == 9) {

            $rows   =   $this->downResumeDelDetail();
        }
        $data['list']       =   $rows['list'];
        $data['total']      =   $rows['total'];
        $data['page_sizes'] =   $rows['page_sizes'];
        $data['page_size']  =   $pages['page_size'];

        $this->render_json(0,'',$data);

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
        $data = $rows   =   array();
        $where  =   array('com_id' => $_POST['uid']);
        if ($_POST['job_id']){

            $where['job_id']    =   $_POST['job_id'];
        }
        // 默认查一周内数据
        $where  =   $this->timeNumber($_POST['time'], $where, 'datetime');

        //提取分页
        $pageM      =   $this->MODEL('page');
        $pages          =   $pageM->adminPageList('userid_job', $where,$_POST['page'],array('limit'=>$_POST['limit']));
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
        $data['list']           =   !empty($rows)?$rows:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];

        return $data;
    }

    //  未查看简历分析
    private function notViewResumeDetail()
    {
        $data = $rows   =   array();
        $where  =   array('com_id' => $_POST['uid'], 'is_browse' => 1);
        // 默认查一周内数据
        $where  =   $this->timeNumber($_POST['time'], $where, 'datetime');

        //提取分页
        $pageM      =   $this->MODEL('page');
        $pages      =   $pageM->adminPageList('userid_job', $where,$_POST['page'],array('limit'=>$_POST['limit']));
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
        $data['list']           =   !empty($rows)?$rows:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];

        return $data;
    }

    //  简历下载分析
    private function downResumeDetail()
    {

        $rows   =   array();
        $where  =   array('comid' => $_POST['uid']);
        $where  =   $this->timeNumber($_POST['time'], $where, 'downtime');

        $pageM  =   $this->MODEL('page');
        $pages      =   $pageM->adminPageList('down_resume', $where,$_POST['page'],array('limit'=>$_POST['limit']));

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
        $data['list']           =   !empty($rows)?$rows:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];

        return $data;
    }

    //  客户登录分析
    private function loginLogDetail()
    {

        $rows   =   array();

        $where  =   array('opera' => 32, 'type' => 1, 'content' => array('like', '登录'), 'uid' => $_POST['uid'], 'usertype' => 2);
        $where  =   $this->timeNumber($_POST['time'], $where, 'ctime');

        $pageM  =   $this->MODEL('page');
        $pages      =   $pageM->adminPageList('member_log', $where,$_POST['page'],array('limit'=>$_POST['limit']));
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
        $data['list']           =   !empty($rows)?$rows:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];

        return $data;
    }

    //  拨号分析
    private function telLogDetail()
    {

        $rows   =   array();
        if ($_POST['job_id']) {

            $where  =   array('jobid' => $_POST['job_id']);
        } elseif ($_POST['uid']) {

            $where  =   array('comid' => $_POST['uid']);
        }

        $where  =   $this->timeNumber($_POST['time'], $where, 'ctime');

        $pageM  =   $this->MODEL('page');
        $pages      =   $pageM->adminPageList('member_log', $where,$_POST['page'],array('limit'=>$_POST['limit']));
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
        $data['list']           =   !empty($rows)?$rows:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];

        return $data;
    }

    //  删除申请分析
    private function sqResumeDelDetail()
    {

        $rows   =   array();

        $where  =   array('com_id' => $_POST['uid'], 'isdel' => 2);
        $where  =   $this->timeNumber($_POST['time'], $where, 'datetime');

        $pageM  =   $this->MODEL('page');
        $pages      =   $pageM->adminPageList('member_log', $where,$_POST['page'],array('limit'=>$_POST['limit']));
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
        $data['list']           =   !empty($rows)?$rows:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];

        return $data;
    }

    // 删除简历下载分析
    private function downResumeDelDetail()
    {

        $rows   =   array();
        $where  =   array('comid' => $_POST['uid'], 'isdel' => 2);
        $where  =   $this->timeNumber($_POST['time'], $where, 'downtime');

        $pageM  =   $this->MODEL('page');
        $pages      =   $pageM->adminPageList('down_resume', $where,$_POST['page'],array('limit'=>$_POST['limit']));
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
        $data['list']           =   !empty($rows)?$rows:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];

        return $data;
    }

    function clueCustomer_action()
    {

        if (isset($_POST['uids']) && !empty($_POST['uids'])) {

            $uids   =   @explode(',', $_POST['uids']);
            $auid   =   intval($_SESSION['auid']);
            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->clueCustomer($uids, $auid);

            if ($return['errcode'] == '9') {

                $this->admin_json(0, '线索信息（uid:' . $_POST['uid'] . '）入库成功');
            } else {

                $this->render_json(1, $return['msg']);
            }
        } else {

            $error  = 1;
            $msg    = '请选择要转换的线索';
            $this->render_json($error, $msg);
        }
    }

    /**
     * @desc 企业画像
     */
    function comPersona_action()
    {

        if ($_POST['id']){

            $comId = $_POST['id'];

            $crmM       =   $this->MODEL('crm');
            $comInfo    =   $crmM->getComInfo($comId, array('auid' => $_SESSION['auid']));

            $persona    =   $crmM->comPersona($comId, array('auid' => $_SESSION['auid']));

            $data       =   array(
                'comInfo'          =>  $comInfo,
                'persona'          =>  $persona,
            );

            $this->render_json(0, '', $data);
        }
    }

    public function getChartData_action(){

        $comid = $_POST['comid'];

        $echartsday = intval($_POST['echartsday'])>0?$_POST['echartsday']:7;

        $return =   $xAxis  =   $scanAll = $scanNumArr= array();

        if($comid){


            if($echartsday>1){
                $date   =   date('Y-m-d',strtotime('-'.($echartsday-1).' day'));
                $sdate  =   strtotime($date.' 00:00:00');
                $edate  =   strtotime(date('Y-m-d').' 23:59:59');
                $field  =   'FROM_UNIXTIME(`time`,"%Y%m%d") as td,`id`';
            }else{
                $sdate  =   strtotime('today');
                $edate  =   strtotime('tomorrow');
                $field  =   'FROM_UNIXTIME(`time`,"%H") as td,`id`';
            }

            $where['uid']     = $comid;
            $where['usertype']= 2;
            $where['orderby'] = array('td,desc');
            $where['time'][]  = array(">=", $sdate);
            $where['time'][]  = array("<", $edate);
            $crmM              = $this -> MODEL('crm');

            $scanAll_data       = $crmM->getWebLogList($where,array('field'=>$field));

            foreach ($scanAll_data as $k1 => $v1) {

                $scanAll[$v1['td']][] = $v1['id'];
            }

            $count = $echartsday==1?24:$echartsday;

            for ($i = 1; $i <= $count; $i ++) {

                $onday  =   date("Ymd", strtotime(' -' . ($count-$i) . 'day', $edate));
                $td     =   date('m-d', strtotime(' -' . ($count-$i) . 'day', $edate));

                if($echartsday==1){
                    $h = $i-1;
                    $onday = $h<10 ? '0'.$h : $h;
                    $td = $onday.':00';
                }

                $xAxis[] = $td;

                $scanNum = !empty($scanAll[$onday])?count(array_unique($scanAll[$onday])):0;

                $scanNumArr[] = $scanNum;

            }

        }

        $return['xAxis']            =   $xAxis;
        $return['scanNumArr']       =   $scanNumArr;

        $this->render_json(0, '', $return);
    }

    /**
     * 工作台跳转我的客户，参数处理
     * 此方法同 crm_index.class.php 中 myCustomer 方法同步修改查询条件
     * @param $type 处理的数据类型
     * @return array|int[]
     */
    private function jobtaiMykhHandle($type, $source)
    {
        $today = strtotime('today'); // 今天
        $crm_deal_rating = !empty($this->config['crm_deal_rating']) ? explode(',', $this->config['crm_deal_rating']) : array('-9'); // CRM设置的成交会员  默认值-9，避免SQL报错的
        $week = strtotime('-6 day', $today); // 取一周时间（包含当天）
        $month = strtotime('-1 month +1 day', $today); // 取一个月前的时间（包含当天）

        $companyM = $this->MODEL('company');

        if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])) {
            if ($_POST['crm_uid'] == 'all') {
                $crmUid = array('in', $_POST['crm_uid']);
            } else {
                $crmUid = $_POST['crm_uid'];
            }
        } else {
            $crmUid = $_SESSION['auid'];
        }


        $uidArr = [-1];

        if ($source == 'myCustomer') {
            $comWhere = "crm_uid = {$crmUid}";
        } else if ($source == 'jobtai') {
            //  工作台数据统计跟进周期
            $comWhere = "crm_uid = {$crmUid} AND (isfollow = 0 OR (isfollow = 1 AND f_time < FTIME))";
        }

        switch ($type) {
            case 'gyx':
                // 高意向用户 X天内未跟进 +（非成交会员，触发权益提醒 OR 7天内有登录，历史成交过，但未续费的客户）
                $gyx_cycle = $this->config['crm_jobtai_gyx_cycle'] ? intval($this->config['crm_jobtai_gyx_cycle']) : 3; // 获取工作台数据周期 - 高意向
                $gyx_cycle = $gyx_cycle - 1;
                $startTimeGyx = strtotime("-{$gyx_cycle} day", $today); // 高意向
                $companyListGyx = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeGyx, $comWhere), 'uid,login_date,rating,vipetime');
                $companyUidGyxArr = array(); // 高意向 非成交会员
                $companyUidGyxDqHyArr = array(); // 到期且活跃
                if ($companyListGyx) {
                    foreach ($companyListGyx as $ckey => $cval) {
                        if (!in_array($cval['rating'], $crm_deal_rating)) { // 高意向 非成交会员
                            $companyUidGyxArr[] = $cval['uid'];
                        }
                        if ($cval['login_date'] >= $week && $cval['vipetime'] && $cval['vipetime'] < $today && in_array($cval['rating'], $crm_deal_rating)) { // 会员等级在CRM设置的成交会员等级中 过期 且1周内有登录
                            $companyUidGyxDqHyArr[] = $cval['uid'];
                        }
                    }
                }

                $gyxUidArr = array();
                $gyxUidList = array();
                if ($companyUidGyxArr) {
                    $gyxList = $this->obj->select_all('rating_notice', array(
                        'crm_uid' => $crmUid,
                        'groupby' => 'uid',
                        'uid' => array('in', pylode(',', $companyUidGyxArr)),
                        'send_time' => array('>=', $startTimeGyx)
                    ), 'uid'); // // X天未跟进，触发权益提醒
                    if ($gyxList) {
                        foreach ($gyxList as $gyxVal) {
                            $gyxUidArr[] = $gyxVal['uid'];
                            $gyxUidList[$gyxVal['uid']] = array(
                                "type" => "1",
                                "tips" => '非会员，权益提醒'
                            );
                        }
                    }
                }
                if ($companyUidGyxDqHyArr) {
                    $gyxOrderList = $this->obj->select_all('company_order', array(
                        'uid' => array('in', pylode(',', $companyUidGyxDqHyArr)),
                        'type' => 1,
                        'order_state' => 2,
                        'groupby' => 'uid',
                    ), 'uid');// X天内有登录，历史成交过，但未续费的客户
                    $gyxUidArr = $gyxOrderList ? array_unique(array_merge($gyxUidArr, array_column($gyxOrderList, 'uid'))) : $gyxUidArr;
                    if ($gyxOrderList) {
                        foreach ($gyxOrderList as $gyxOVal) {
                            $gyxUidArr[] = $gyxOVal['uid'];
                            $gyxUidList[$gyxOVal['uid']] = array(
                                "type" => "2",
                                "tips" => '过期会员，近期登陆'
                            );
                        }
                    }
                }
                $gyxUidArr && $uidArr = $gyxUidList;
                break;
            case 'qz':
                // 潜在用户
                $qz_cycle = $this->config['crm_jobtai_qz_cycle'] ? intval($this->config['crm_jobtai_qz_cycle']) : 3; // 获取工作台数据周期 - 潜在
                $qz_cycle = $qz_cycle - 1;
                $startTimeQz = strtotime("-{$qz_cycle} day", $today); // 潜在
                $companyListQz = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeQz, $comWhere), 'uid,login_date,rating,vipetime');
                $companyUidQzArr = array(); // 一个月未登录的企业（长期未登录）
                if ($companyListQz) {
                    foreach ($companyListQz as $ckey => $cval) {
                        if ($cval['login_date'] < $month) {
                            if (!in_array($cval['rating'], $crm_deal_rating)) { // 非成交会员，即为未开通会员
                                $companyUidQzArr[] = $cval['uid'];
                            } elseif ($cval['vipetime'] && $cval['vipetime'] < $today) { // 会员过期
                                if ($crm_deal_rating && in_array($cval['rating'], $crm_deal_rating)) { // 会员等级在CRM设置的成交会员等级中
                                    $companyUidQzArr[] = $cval['uid'];
                                }
                            }
                        }
                    }
                }

                if ($companyUidQzArr) {
                    $qzUidArr = array();
                    $qz_num = 0;
                    $jobM = $this->MODEL('job');
                    // 有发布中的岗位
                    $jobList = $jobM->getListId(array(
                        'uid' => array('in', pylode(',', $companyUidQzArr)),
                        'state' => 1,
                        'status' => 0,
                        'groupby' => 'uid'
                    ), array('field' => 'uid,count(uid) as num'));
                    if ($jobList) {
                        $qzUidArr = array_column($jobList, 'uid');
                        $qz_num = count($qzUidArr);
                    }

                    $qzUidArr && $uidArr = $qzUidArr;
                }
                break;
            case 'hy':
                // 活跃用户
                $hy_cycle = $this->config['crm_jobtai_hy_cycle'] ? intval($this->config['crm_jobtai_hy_cycle']) : 3; // 获取工作台数据周期 - 活跃
                $hy_cycle = $hy_cycle - 1;
                $startTimeHy = strtotime("-{$hy_cycle} day", $today); // 活跃
                $companyListHy = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeHy, $comWhere), 'uid,login_date,rating,vipetime');
                $companyUidHyArr = array(); // 一周内登录过的（近期登录），未开通会员或会员已过期
                if ($companyListHy) {
                    foreach ($companyListHy as $ckey => $cval) {
                        if ($cval['login_date'] >= $week) {
                            if (!in_array($cval['rating'], $crm_deal_rating)) { // 非成交会员，即为未开通会员
                                $companyUidHyArr[] = $cval['uid'];
                            } elseif ($cval['vipetime'] && $cval['vipetime'] < $today && in_array($cval['rating'], $crm_deal_rating)) { // 会员过期 并且会员等级在CRM设置的成交会员等级中
                                $companyUidHyArr[] = $cval['uid'];
                            }
                        }
                    }
                }

                $companyUidHyArr && $uidArr = $companyUidHyArr;
                break;
            case 'jxgj':
                // 急需跟进
                // 即将超期被释放
                $releaseSet = $this->obj->select_all('crmset');
                $jxgjUidArr = array();
                if ($releaseSet) {
                    $companyListJxgj = $companyM->getChCompanyList(array(
                        'crm_uid' => $crmUid,
                        'r_status' => array('<>', '4'), // 不包含暂停的企业
                        'clue' => 1, // 不包含线索库
                        'crm_time' => array('<', strtotime('-1 day', $today)), // 不查最新认领的
                        // 'PHPYUNBTWSTART_A' => '',
                        // 'f_time' => array(
                        //     array('<', strtotime('-1 day', $today)), // 不查最新跟进的
                        //     array('isnull', '', 'OR')
                        // ),
                        // 'PHPYUNBTWEND_A' => '',
                    ), array('field' => 'uid,rating,vipstime,vipetime,crm_time,f_time'));
                    $jxgjUidList = array();
                    if ($companyListJxgj) {
                        foreach ($companyListJxgj as $jxkey => $jxval) {
                            foreach ($releaseSet as $rskey => $rsval) {
                                if ($jxval['rating'] > 0 && ($jxval['vipetime'] >= $today || $jxval['vipetime'] == 0)) { // 有效的会员
                                    $rrating = $jxval['rating'];
                                } else {// 过期会员
                                    $rrating = 999;
                                }

                                if ($rsval['release_day'] > 3) { // 释放 提前3天提醒 未被及时释放的也可查出
                                    $reday = intval($rsval['release_day']) - 3;
                                    $retime = strtotime("-{$reday} day", $today);
                                    if (in_array($rrating, @explode(',', $rsval['rating']))
                                        && ((!empty($jxval['f_time']) && $jxval['f_time'] < $retime) ||
                                            ($jxval['crm_time'] < $retime && empty($jxval['f_time'])))) {
                                        $jxgjUidArr[] = $jxval['uid'];
                                        $jxgjUidList[$jxval['uid']] = array(
                                            "type" => "1",
                                            "tips" => "即将释放（{$rsval['release_day']}天未跟进）"
                                        );
                                        break;
                                    }
                                }
                                if ($rsval['deal_day'] > 3) { // 成交 提前3天提醒 未被及时释放的也可查出
                                    $deday = intval($rsval['deal_day']) - 3;
                                    $detime = strtotime("-{$deday} day", $today);
                                    if ($jxval['crm_time'] < $detime && in_array($rrating, @explode(',', $rsval['rating']))
                                        && (($rrating == 999 && $jxval['vipetime'] > 0 && $jxval['vipetime'] < $detime)
                                            || ($rrating != 999 && $jxval['vipstime'] < $detime))) {
                                        $jxgjUidArr[] = $jxval['uid'];
                                        $jxgjUidList[$jxval['uid']] = array(
                                            "type" => "2",
                                            "tips" => "即将释放（{$rsval['deal_day']}天未成交）"
                                        );
                                        break;
                                    }
                                }
                                if ($rsval['follow_day'] > 0) { // 跟进
                                    $fetime = strtotime("-{$rsval['follow_day']} day");
                                    if (in_array($rrating, @explode(',', $rsval['rating']))
                                        && (($jxval['crm_time'] < $fetime && empty($jxval['f_time']))
                                            || (!empty($jxval['f_time']) && $jxval['f_time'] < $fetime))) {
                                        $jxgjUidArr[] = $jxval['uid'];
                                        $jxgjUidList[$jxval['uid']] = array(
                                            "type" => "3",
                                            "tips" => "{$rsval['follow_day']}天未跟进"
                                        );
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                // 查询设置下次跟进时间并且未跟进的客户
                $planSql = "SELECT c.comid uid,c.stime FROM {$this->def}company a JOIN {$this->def}crm_work_plan c ON a.uid = c.comid WHERE a.`crm_uid` = {$crmUid} AND c.auid = {$crmUid} AND c.stime < " .strtotime('+1 day', $today) . " AND c.type = 22 AND c.status = 1 GROUP BY c.comid";
                $planList = $this->obj->DB_query_all($planSql, 'all');
                if ($planList) {
                    foreach ($planList as $planVal) {
                        $jxgjUidArr[] = $planVal['uid'];
                        if (!isset($jxgjUidList[$planVal['uid']])) {
                            $jxgjUidList[$planVal['uid']] = array(
                                "type" => "4",
                                "tips" => '下次跟进（' . date('Y-m-d', $planVal['stime']) . '）'
                            );
                        }
                    }
                }
                $jxgjUidArr && $uidArr = $jxgjUidList;
                break;
            case 'jdq':
                // 将到期用户
                $jgq_cycle = $this->config['crm_jobtai_jgq_cycle'] ? intval($this->config['crm_jobtai_jgq_cycle']) : 3; // 获取工作台数据周期 - 将过期
                $jgq_cycle = $jgq_cycle - 1;
                $startTimeJgq = strtotime("-{$jgq_cycle} day", $today); // 将过期
                $companyListJgq = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeJgq, $comWhere), 'uid,login_date,rating,vipetime');
                $companyUidJdqArr = array(); // X天内未跟进，15天内将到期的用户
                if ($companyListJgq) {
                    foreach ($companyListJgq as $ckey => $cval) {
                        if ($cval['vipetime'] && $cval['vipetime'] >= $today && $cval['vipetime'] < strtotime("+15 day", $today)) {
                            if ($crm_deal_rating && in_array($cval['rating'], $crm_deal_rating)) { // 会员等级在CRM设置的成交会员等级中
                                $companyUidJdqArr[] = $cval['uid'];
                            }
                        }
                    }
                }

                $companyUidJdqArr && $uidArr = $companyUidJdqArr;
                break;
            case 'bq':
                $bqsetList = $this->MODEL('crm')->getBqsetList(array('auid' => $crmUid));
                if ($bqsetList) {
                    $bqUidArr = array();
                    foreach($bqsetList as $bqval) {
                        $fu_cycle = intval($bqval['fu_cycle'])-1; // 包含当天
                        $bqComList = $companyM->getChCompanyList(array(
                            'crm_uid' => $crmUid,
                            'isfollow' => 1,
                            'crm_status' => array('in', pylode(',', $bqval['crm_status'])),
                            'f_time' => array('<', strtotime("-{$fu_cycle} day today"))
                        ), array('field' => 'uid'));
                        $bqComList && $bqUidArr = array_merge($bqUidArr, array_column($bqComList, 'uid'));
                    }
                    $bqUidArr && $uidArr = $bqUidArr;
                }
                break;
        }

        return $uidArr;
    }

    /**
     * 工作台跳转我的客户统计，参数处理 (本月数据)
     * 此方法同 crm_index.class.php 中 myCustomerMonth 方法同步修改查询条件
     * @param $type 处理的数据类型
     * @return array|int[]
     */
    private function jobtaiMykhtjHandle($type)
    {
        $today = strtotime('today');
        $month = strtotime(date('Y-m')); // 当月
        $crm_uid = $_POST['crm_uid'] ? $_POST['crm_uid'] : $_SESSION['auid'];

        $companyM = $this->MODEL('company');

        $uidArr = [-1];

        switch ($type) {
            case 'new':
                // 本月新增客户
                $newWhere = array(
                    'crm_uid' => $crm_uid,
                    'crm_time' => array('>=', $month)
                );
                $newList = $companyM->getChCompanyList($newWhere, array('field' => 'uid'));
                $newList && $uidArr = array_column($newList, 'uid');
                break;
            case 'deal':
                // 本月成交客户
                if (!empty($this->config['crm_deal_rating'])) {
                    $orderM = $this->MODEL('companyorder');
                    $dealWhere = array(
                        'crm_uid' => $crm_uid,
                        'type' => 1,
                        'order_state' => 2,
                        'rating' => array('in', $this->config['crm_deal_rating']),
                        'order_time' => array('>=', $month),
                        'groupby' => 'uid'
                    );
                    $dealList = $orderM->getList($dealWhere, array('field' => 'uid'));
                    $dealList && $uidArr = array_column($dealList, 'uid');
                }
                break;
            case 'jdq':
                // 将到期客户
                // 15天内即将到期的客户
                if (!empty($this->config['crm_deal_rating'])) {
                    $jdqWhere = array(
                        'crm_uid' => $crm_uid,
                        'rating' => array('in', $this->config['crm_deal_rating']),
                        'vipetime' => array(array('>=', $today), array('<', strtotime("+15 day", $today)))
                    );
                    $jdqList = $companyM->getChCompanyList($jdqWhere, array('field' => 'uid'));
                    $jdqList && $uidArr = array_column($jdqList, 'uid');
                }
                break;
            case 'xf':
                // 本月续费客户
                $dySql = "SELECT uid FROM `{$this->def}company_order` WHERE crm_uid = {$crm_uid} AND `type` = 1 AND `order_state` = 2 AND order_time >= {$month} GROUP BY uid"; // 查询当月开通会员的用户
                $dyList = $this->obj->DB_query_all($dySql, 'all');
                if ($dyList) {
                    $dyUids = pylode(',', array_column($dyList, 'uid'));
                    $xfSql = "SELECT uid,count(uid) num FROM `{$this->def}company_order` WHERE uid IN ({$dyUids}) AND `type` = 1 AND `order_state` = 2 GROUP BY uid HAVING `num` > 1";
                    $xfList = $this->obj->DB_query_all($xfSql, 'all');
                    $xfList && $uidArr = array_column($xfList, 'uid');
                }
                break;
            case 'dq':
                // 本月已到期客户
                $dqWhere = array(
                    'crm_uid' => $crm_uid,
                    'vipetime' => array(array('>=', $month), array('<', $today))
                );
                $dqList = $companyM->getChCompanyList($dqWhere, array('field' => 'uid'));
                $dqList && $uidArr = array_column($dqList, 'uid');
                break;
            case 'zt':
                // 本月暂停客户
                $ztWhere = array(
                    'crm_uid' => $crm_uid,
                    'r_status' => 4,
                    'zt_time' => array('>=', $month)
                );
                $ztList = $companyM->getChCompanyList($ztWhere, array('field' => 'uid'));
                $ztList && $uidArr = array_column($ztList, 'uid');
                break;
        }

        return $uidArr;
    }

    function comloginlog_action(){
        $logM = $this->MODEL('log');
        $memberM = $this->MODEL('userinfo');

        if ($_POST['utype']) {
            $where['usertype'] = trim($_POST['utype']);
        } else {
            $where['usertype'] = 1;
        }
        if (intval($_POST['uid'])) {
            $where['uid'] = intval($_POST['uid']);
        }
        if (trim($_POST['keyword'])) {
            if ($_POST['type'] == 1) {
                $member = $memberM->getList(array('username' => array('like', trim($_POST['keyword']))), array('field' => '`uid`,`username`'));
                foreach ($member as $v) {
                    $uid[] = $v['uid'];
                }
                $where['uid'] = array('in', pylode(",", $uid));
            } elseif ($_POST['type'] == 2) {
                $where['content'] = array('like', trim($_POST['keyword']));
            } elseif ($_POST['type'] == 3) {
                $where['uid'] = trim($_POST['keyword']);
            }
        }
        if ($_POST['times']) {
            $time = $_POST['times'];
            $time_begin = $time[0]? date('Y-m-d',strtotime(str_replace(array('T','Z'),' ',$time[0]))): date('Y-m-d', strtotime('-30 days'));
            $time_end  = $time[1] ? date('Y-m-d',strtotime(str_replace(array('T','Z'),' ',$time[1]))):date('Y-m-d');
            $where['ctime'][] = array('>=', strtotime($time_begin . "00:00:00"));
            $where['ctime'][] = array('<=', strtotime($time_end . "23:59:59"));
        }
        //内容搜索
        if (trim($_POST['content'])) {
            $where['content'] = array('like', trim($_POST['content']));
        }
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM  =   $this  -> MODEL('page');
        $pages  =   $pageM -> adminPageList('login_log',$where,$page,array('limit' => $pageSize));
        if (!$pages['total']) {
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
        if ($_POST['order']) {
            $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
        } else {
            $where['orderby'] = array('id,desc');
        }
        $where['limit'] = $pages['limit'];
        $List = $logM->getLoginlogList($where, array('utype' => 'admin'));
        foreach ($List  as  &$value){
            $value['ctime_ymd'] = $value['ctime']?date('Y-m-d H:i:s',$value['ctime']):'';
        }
        $this->render_json(0,'',['data'=>$List,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);
    }

    function set_comsearch($CacheList = array())
    {
        include(CONFIG_PATH . 'db.data.php');
        $source = $arr_data['source'];
        $this->yunset('source', $source);
        if (!$CacheList) {
            $cacheM = $this->MODEL('cache');
            $CacheList = $cacheM->GetCache(array('com', 'job', 'city'));
            $setArr = array('comdata' => $CacheList['comdata'], 'comclass_name' => $CacheList['comclass_name'], 'job_name' => $CacheList['job_name'], 'city_name' => $CacheList['city_name']);
            $this->yunset($setArr);
        }
        $comdata = $CacheList['comdata'];
        $comclass_name = $CacheList['comclass_name'];
        foreach ($comdata['job_edu'] as $k => $v) {
            $edu[$v] = $comclass_name[$v];
        }
        foreach ($comdata['job_exp'] as $k => $v) {
            $exp[$v] = $comclass_name[$v];
        }
        $ratingM = $this->MODEL('rating');
        $rating = $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
        if (!empty($rating)) {
            $ratingarr = array();
            foreach ($rating as $k => $v) {
                $ratingarr[$v['id']] = $v['name'];
            }
        }
        $search_list = array();
        $search_list['state'] = array('name' => '审核状态', 'value' => array('1' => '已审核', '4' => '未审核', '3' => '未通过', '2' => '已锁定'));
        $search_list['status'] = array('name' => '招聘状态', 'value' => array('1' => '已下架', '2' => '招聘中'));
        $search_list['jtype'] = array('name' => '职位类型', 'value' => array('urgent' => '紧急职位', 'xuanshang' => '置顶职位', 'rec' => '推荐职位'));
        $search_list['exp'] = array('name' => '工作经验', 'value' => $exp);
        $search_list['edu'] = array('name' => '学历要求', 'value' => $edu);
        $search_list['source'] = array('name' => '数据来源', 'value' => $source);
        $search_list['adtime'] = array('name' => '发布时间', 'value' => array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月', '31' => '当月', '-1' => '昨天', '-7' => '本周'));
        $search_list['rating'] = array('name' => '会员等级', 'value' => $ratingarr);
        $search_list['openautho'] = array('name' => '开放权限', 'value' => array('1'=>'默认','2'=>'开放'));
        $search_list['recruit'] = array('name' => '申请代招', 'value' => array('1'=>'是'));
        $search_list['is_depower'] = array('name' => '是否降权', 'value' => array('1'=>'是','2'=>'否'));
        return array('search_list' => $search_list);
    }

    function comjoblist_action()
    {
        if (isset($_POST['fromCrmIndex']) && $_POST['fromCrmIndex'] == 1) {
            $comM = $this->MODEL('company');
            $coms = $comM->getChCompanyList(array('crm_uid' => array('=', intval($_SESSION['auid']))), array('field' => '`uid`'));
            $comuids = array();
            foreach ($coms as $ck => $cv) {
                $comuids[] = $cv['uid'];
            }
            $where['uid'] = array('in', pylode(',', $comuids));
        }
        if (isset($_POST['state']) && !empty($_POST['state'])) {
            $state = intval($_POST['state']);
            if ($state == 2) {
                $where['r_status'] = 2;
            } else {
                $where['state'] = $state == 4 ? 0 : $state;
            }
        }
        if (isset($_POST['openautho'])){
            $where['linkopen'] = intval($_POST['openautho']);
        }
        if (isset($_POST['status']) && !empty($_POST['status'])) {
            $status = intval($_POST['status']);
            $where['status'] = $status == 2 ? 0 : $status;
        }
        if (isset($_POST['jtype']) && !empty($_POST['jtype'])) {
            $jtype = trim($_POST['jtype']);
            if ($jtype == 'rec') {
                $where['rec_time'] = array('>', time());
            } else if ($jtype == 'urgent') {
                $where['urgent_time'] = array('>', time());
            } else if ($jtype == 'xuanshang') {
                $where['xsdate'] = array('>', time());
            }
        }
        if (isset($_POST['edu']) && !empty($_POST['edu'])) {
            $where['edu'] = $_POST['edu'];
        }
        if (isset($_POST['exp']) && !empty($_POST['exp'])) {
            $where['exp'] = $_POST['exp'];
        }
        if (isset($_POST['source']) && !empty($_POST['source'])) {
            $where['source'] = intval($_POST['source']);
        }
        if (isset($_POST['adtime']) && !empty($_POST['adtime'])) {
            if ($_POST['adtime'] == '1') {
                $where['sdate'] = array('>', strtotime('today'));
            } else if ($_POST['adtime'] == '31') {
                $month = get_this_month(time());
                $where['PHPYUNBTWSTART_A'] = '';
                $where['sdate'][] = array('>=', $month['start_month'], 'AND');
                $where['sdate'][] = array('<=', $month['end_month'], 'AND');
                $where['PHPYUNBTWEND_A'] = '';
            } else if ($_POST['adtime'] == '-1') {
                $sTime = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 86400;
                $eTime = mktime(23, 59, 59, date('m'), date('d'), date('Y')) - 86400;
                $where['PHPYUNBTWSTART_A'] = '';
                $where['sdate'][] = array('>=', $sTime, '');
                $where['sdate'][] = array('<=', $eTime, '');
                $where['PHPYUNBTWEND_A'] = '';
            } else if ($_POST['adtime'] == '-7') {
                $sTime = strtotime(date('Y-m-d', strtotime("this week Monday", time())));
                $eTime = strtotime(date('Y-m-d', strtotime("this week Sunday", time()))) + 24 * 3600 - 1;
                $where['PHPYUNBTWSTART_A'] = '';
                $where['sdate'][] = array('>=', $sTime, '');
                $where['sdate'][] = array('<=', $eTime, '');
                $where['PHPYUNBTWEND_A'] = '';
            } else {
                $where['sdate'] = array('>', strtotime('-' . intval($_POST['adtime']) . ' day'));
            }
        }
        if (isset($_POST['rating']) && !empty($_POST['rating'])) {
            $where['rating'] = $_POST['rating'];
        }
        if (isset($_POST['job_class']) && !empty($_POST['job_class'])) {
            $where['PHPYUNBTWSTARTA'] = '';
            $where['job1'] = array('findin', $_POST['job_class']);
            $where['job1_son'] = array('findin', $_POST['job_class'], 'OR');
            $where['job_post'] = array('findin', $_POST['job_class'], 'OR');
            $where['PHPYUNBTWENDA'] = '';
        }
        if (isset($_POST['city_class']) && !empty($_POST['city_class'])) {
            $where['PHPYUNBTWSTARTB'] = '';
            $where['provinceid'] = array('findin', $_POST['city_class']);
            $where['cityid'] = array('findin', $_POST['city_class'], 'OR');
            $where['three_cityid'] = array('findin', $_POST['city_class'], 'OR');
            $where['PHPYUNBTWENDB'] = '';
        }
        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
            $keywordStr = trim($_POST['keyword']);
            $typeStr = intval($_POST['type']);
            if ($typeStr == 1) {
                $where['com_name'] = array('like', $keywordStr);
            } elseif ($typeStr == 2) {
                $where['name'] = array('like', $keywordStr);
            } elseif ($typeStr == 3) {
                $where['id'] = $keywordStr;
            } elseif ($typeStr == 4) {
                $where['add_ip'] = array('like', $keywordStr);
            }
        }
        if (isset($_POST['is_reserve']) && !empty($_POST['is_reserve'])) {
            $where['is_reserve'] = intval($_POST['is_reserve']);
        }
        if (isset($_POST['uid']) && !empty($_POST['uid'])) {
            $where['uid'] = intval($_POST['uid']);
        }
        if (isset($_POST['recruit']) && !empty($_POST['recruit'])){
            $applyList = $this->obj->select_all('recruit_apply', array('status' => 0), '`jobid`');
            $aJobIdS = array();
            foreach ($applyList as $ak=>$av){
                $aJobIdS[] = $av['jobid'];
            }
            $where['id'] = array('in', pylode(',', $aJobIdS));
        }
        if (!empty($_POST['is_depower'])) {
            $where['is_depower'] = intval($_POST['is_depower']);
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company_job', $where, $page, array('limit' => $pageSize));
        if ($pages['total'] > 0) {
            if (isset($_POST['order']) && !empty($_POST['order'])) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = 'lastupdate,desc';
            }
            $where['limit'] = $pages['limit'];
            $JobM = $this->MODEL('job');
            $ListJob = $JobM->getList($where, array('utype' => 'admin', 'cache' => '1', 'isurl' => 'yes', 'reserve' => 1, 'crm_user' => 1));
            unset($where['limit']);
            $_SESSION['jobXls'] = $where;
        }

        $rt['list'] = $ListJob['list'] ? $ListJob['list'] : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];

        $this->render_json(0, '', $rt);

    }

    function sqlog_action()
    {
        $JobM = $this->MODEL('job');

        $typeStr = intval($_POST['type']);
        $keywordStr = trim($_POST['keyword']);
        if (!empty($keywordStr)) {
            if ($typeStr == 1) {
                //职位名称
                $where['job_name'] = array('like', $keywordStr);
            } elseif ($typeStr == 2) {
                //公司名称
                $where['com_name'] = array('like', $keywordStr);
            } elseif ($typeStr == 3) {
                //个人姓名
                $resumeM = $this->MODEL('resume');
                $rwhere['name'] = array('like', $keywordStr);
                $rlist = $resumeM->getResumeList($rwhere, array('field' => '`uid`'));
                if (is_array($rlist)) {
                    foreach ($rlist as $v) {
                        $muids[] = $v['uid'];
                    }
                }
                $where['uid'] = array('in', pylode(',', $muids));
            }
        }
        //是否查看
        if ($_POST['browse']) {
            $where['is_browse'] = intval($_POST['browse']);
        }
        //申请时间
        if ($_POST['time']) {
            if ($_POST['time'] == '1') {
                $where['datetime'] = array('>=', strtotime(date('Y-m-d 00:00:00')));
            } else {
                $where['datetime'] = array('>=', strtotime('-' . intval($_POST['time']) . ' day'));
            }
        }
        //  申请职位ID
        if (isset($_POST['job_id']) && !empty($_POST['job_id'])) {
            $where['job_id'] = $_POST['job_id'];
        }
        //  申请企业ID
        if (isset($_POST['com_id']) && !empty($_POST['com_id'])) {
            $where['com_id'] = $_POST['com_id'];
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $page = $pageM->page($_POST);
        $pageSize = $pageM->limit($_POST);
        $pages = $pageM->adminPageList('userid_job', $where, $page, array('limit' => $pageSize));
        $pageSizes = $pages['page_sizes'];
        $list = array();
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('id,desc');
            }

            $where['limit'] = $pages['limit'];

            $rows = $JobM->getSqJobList($where, array('utype' => 'admin'));
            foreach ($rows as $value) {
                $list[] = array(
                    'id' => $value['id'],
                    'uid' => $value['uid'],
                    'job_name' => $value['job_name'],
                    'job_url' => $value['job_url'],
                    'com_name' => $value['com_name'],
                    'com_url' => $value['com_url'],
                    'username_n' => $value['username_n'],
                    'eid' => $value['eid'],
                    'telphone' => $value['telphone'],
                    'telphone_url' => $value['telphone_url'],
                    'is_browse' => $value['is_browse'],
                    'datetime' => $value['datetime'],
                    'datetime_n_n' => $value['datetime_n_n'],
                    'isdel_n' => $value['isdel_n'],
                );
            }
        }
        $rt = array();
        $rt['list'] = $list;
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pageSizes;
        $this->render_json(0, '', $rt);
    }

    function useridmsg_action()
    {
        $JobM = $this->MODEL('job');

        $typeStr = intval($_POST['type']);
        $keywordStr = trim($_POST['keyword']);
        if (!empty($keywordStr)) {
            if ($typeStr == 1) {
                //姓名
                $resumeM = $this->MODEL('resume');
                $rwhere['name'] = array('like', $keywordStr);
                $rlist = $resumeM->getResumeList($rwhere, array('field' => '`uid`'));
                if (is_array($rlist)) {
                    foreach ($rlist as $v) {
                        $muids[] = $v['uid'];
                    }
                }
                $where['uid'] = array('in', pylode(',', $muids));
            } elseif ($typeStr == 2) {
                //邀约企业
                $where['fname'] = array('like', $keywordStr);
            } elseif ($typeStr == 3) {
                //邀请标题
                $where['title'] = array('like', $keywordStr);
            } elseif ($typeStr == 4) {
                //邀请内容
                $where['content'] = array('like', $keywordStr);
            }
        }
        //是否查看
        if ($_POST['browse']) {
            $where['is_browse'] = intval($_POST['browse']);
        }
        //邀约时间
        if ($_POST['time']) {
            if ($_POST['time'] == '1') {
                $where['datetime'] = array('>=', strtotime(date('Y-m-d 00:00:00')));
            } else {
                $where['datetime'] = array('>=', strtotime('-' . intval($_POST['time']) . ' day'));
            }
        }
        //岗位ID
        if (isset($_POST['jobid']) && !empty($_POST['jobid'])) {
            $where['jobid'] = intval($_POST['jobid']);
        }
        if (isset($_POST['comid']) && !empty($_POST['comid'])) {
            $where['fid'] = intval($_POST['comid']);
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $page = $pageM->page($_POST);
        $pageSize = $pageM->limit($_POST);
        $pages = $pageM->adminPageList('userid_msg', $where, $page, array('limit' => $pageSize));
        $pageSizes = $pages['page_sizes'];
        $list = array();
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('id,desc');
            }
            $where['limit'] = $pages['limit'];
            $rows = $JobM->getYqmsList($where, array('utype' => 'admin'));
            foreach ($rows as $value) {
                $list[] = array(
                    'id' => $value['id'],
                    'realname' => $value['realname'],
                    'name' => $value['name'],
                    'jobname' => $value['jobname'],
                    'job_url' => $value['job_url'],
                    'fname' => $value['fname'],
                    'com_url' => $value['com_url'],
                    'content' => $value['content'],
                    'datetime' => $value['datetime'],
                    'datetime_n' => $value['datetime_n'],
                    'is_browse' => $value['is_browse'],
                    'isdel_n' => $value['isdel_n'],
                    'eid' => $value['eid'],
                    'uid' => $value['uid'],
                );
            }
        }
        $rt = array();
        $rt['list'] = $list;
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pageSizes;
        $this->render_json(0, '', $rt);
    }

    function down_action()
    {
        //从企业列表更多过来的
        if ($_POST['comid']) {
            $comid = intval($_POST['comid']);
            $where['comid'] = $comid;
            $where['usertype'] = 2;
        }
        if ($_POST['keyword']) {
            $keytype = intval($_POST['type']);
            $keyword = trim($_POST['keyword']);
            if ($keytype == 1) {
                //搜企业/猎头用户名
                $userinfoM = $this->MODEL('userinfo');
                $cw['username'] = array('like', $keyword);
                $cw['PHPYUNBTWSTART'] = '';
                $cw['usertype'][] = array('=', 2);
                $cw['usertype'][] = array('=', 3, 'OR');
                $cw['PHPYUNBTWEND'] = '';
                $mcom = $userinfoM->getList($cw, array('field' => 'uid'));
                if ($mcom) {
                    $uids = array();
                    foreach ($mcom as $v) {
                        $uids[] = $v['uid'];
                    }
                    $where['comid'] = array('in', pylode(',', $uids));
                } else {
                    $where['comid'] = "";
                }
            } elseif ($keytype == 2) {
                //公司
                $companyM = $this->MODEL('company');
                $com = $companyM->getList(array('name' => array('like', $keyword)), array('field' => 'uid'));
                $uids = array();
                if ($com) {
                    foreach ($com['list'] as $v) {
                        $uids[] = $v['uid'];
                    }
                }
                //猎头名称
                $lietouM = $this->MODEL('lietou');
                $lt = $lietouM->getList(array('realname' => array('like', $keyword)), array('field' => 'uid'));
                if ($lt) {
                    foreach ($lt as $v) {
                        $uids[] = $v['uid'];
                    }
                }
                if (!empty($uids)) {
                    $where['comid'] = array('in', pylode(',', $uids));
                } else {
                    $where['comid'] = "";
                }
            } elseif ($keytype == 3) {
                //个人用户名
                $userinfoM = $this->MODEL('userinfo');
                $muser = $userinfoM->getList(array('username' => array('like', $keyword), 'usertype' => 1), array('field' => 'uid'));
                if ($muser) {
                    $uids = array();
                    foreach ($muser as $v) {
                        $uids[] = $v['uid'];
                    }
                    $where['uid'] = array('in', pylode(',', $uids));
                } else {
                    $where['uid'] = "";
                }
            } elseif ($keytype == 4) {
                //姓名/手机号
                $nametel = $this->MODEL('resume');
                $nt['PHPYUNBTWSTART'] = '';
                $nt['telphone'] = array('like', $keyword);
                $nt['name'] = array('like', $keyword, 'OR');
                $nt['PHPYUNBTWEND'] = '';
                $nametels = $nametel->getResumeList($nt, array('field' => 'uid'));
                if ($nametels) {
                    $uid = array();
                    foreach ($nametels as $v) {
                        $uid[] = $v['uid'];
                    }
                    $where['uid'] = array('in', pylode(',', $uid));
                } else {
                    $where['uid'] = "";
                }
            }
        }
        if ($_POST['time']) {
            $time = intval($_POST['time']);
            if ($time == 1) {
                $where['downtime'] = array('>', strtotime('today'));
            } else {
                $where['downtime'] = array('>', strtotime('-' . $time . ' day'));
            }
        }
        //提取分页
        $pageM = $this->MODEL('page');
        $page = $pageM->page($_POST);
        $pageSize = $pageM->limit($_POST);
        $pages = $pageM->adminPageList('down_resume', $where, $page, array('limit' => $pageSize));
        $pageSizes = $pages['page_sizes'];

        $downM = $this->MODEL('downresume');
        $list = array();
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = 'id';
            }
            $where['limit'] = $pages['limit'];
            $List = $downM->getList($where, array('utype' => 'admin'));
            $list = $List['list'];
        }
        $rt = array();
        $rt['list'] = $list;
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pageSizes;
        $this->render_json(0, '', $rt);
    }

    function getFollowLog_action()
    {

        $crmM   =   $this->MODEL('crm');

        $where  =   array();

        $tabStr =   intval($_POST['tab']);
        if (isset($tabStr) && !empty($tabStr)) {
            if ($tabStr == 1) {

                $where['auid']  =   $_SESSION['auid'];
            } elseif ($tabStr == 2) {

                $adminUserList  =   $this->getAdminUser();  //  下属信息
                $orgUidS        =   array();
                foreach ($adminUserList as $v) {
                    $orgUidS[]  =   $v['uid'];
                }
                $where['auid']  =   array('in', pylode(',', $orgUidS));
            }
        }

        if (isset($_POST['crm_uid']) && intval($_POST['crm_uid']) > 0) {

            $where['auid']  =   $_POST['crm_uid'];
        }else if ($_POST['crm_uid'] == 'all') {                        // 全部下属

            $where['PHPYUNBTWSTART_W']  =   '';
            if (isset($where['auid'])){
                unset($where['auid']);
            }
            $where['auid'][]         =   array('>', 0, '');
            $where['auid'][]         =   array('in', $_POST['orgUids'], '');
            $where['PHPYUNBTWEND_W']    =   '';
        }
        if (isset($_POST['comid']) && !empty($_POST['comid'])) {

            $where['uid']  =   $_POST['comid'];
        }
        if (isset($_POST['follow_type']) && !empty($_POST['follow_type'])) {

            $where['type']  =   $_POST['follow_type'];
        }

        $keywordStr =   trim($_POST['keyword']);
        $typeStr    =   intval($_POST['type']);
        if (isset($keywordStr) && !empty($keywordStr)) {

            if ($typeStr == 1){

                $comM   =   $this->MODEL('company');
                $comArr =   $comM->getList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));

                $uidS   =   array();
                foreach ($comArr['list'] as $k => $v) {
                    $uidS[]     =   $v['uid'];
                }

                $where['uid']   =   array('in', pylode(',', $uidS));
            }else if ($typeStr == 2){

                $where['content']   =   array('like', $keywordStr);
            }
        }

        if (isset($_POST['time']) && !empty($_POST['time'])) {

            $start  =   date('Y-m-d', $_POST['time'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['time'][1] / 1000);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['ftime'][]           =   array('>=', strtotime($start . " 00:00:00"));
            $where['ftime'][]           =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $page   =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('crmnew_concern', $where, $page, array('limit' => $pageSize));

        $list   =   array();
        $total  =   intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {

                $where['orderby'] = $_POST['t'] . ', ' . $_POST['order'];
            } else {

                $where['orderby'] = 'id,desc';
            }
            $where['limit'] = $pages['limit'];
            $list   =   $crmM->getConcernList($where, array('utype' => 'crm'));
        }

        $result     =   array(

            'list'          =>  $list,
            'total'         =>  intval($total),
            'pageSize'      =>  $pageSize,
            'pageSizes'     =>  $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }

    function getFollowStaticData_action(){
        $tabStr =   intval($_POST['tab']);

        if (isset($tabStr) && !empty($tabStr)) {
            if ($tabStr == 2) {

                $adminUserList  =   $this->getAdminUser();  //  下属信息
            }
        }

        $adminM     =   $this->MODEL('admin');
        if (!empty($orgUidS)){

            $crmUser    =   $adminUserList;
        }else{
            $crmUser    =   $adminM->getList(array(), array('field' => '`uid`,`name`'));
        }

        $cacheM     =   $this->MODEL('cache');
        $cache      =   $cacheM->GetCache(array('crm'));
        $followType =   $cache['crmdata']['follow_way'];
        $followWay  =   array();
        foreach ($followType as $v){
            $followWay[]    =   array('id' => $v, 'name' => $cache['crmclass_name'][$v]);
        }

        $data['contactOssUrl']    =  $this->config['sy_outcall_oss'];
        $data['followType']       =  $followWay;
        $data['crmUser']          =  $crmUser;
        $this->render_json(0, 'ok', $data);
    }

    function company_order_action()
    {

        $OrderM = $this->MODEL('companyorder');
        $where = array();
        $keywordStr = trim($_POST['keyword']);
        if($_POST['comid']){
            $where['uid'] = $_POST['comid'];
            $where['usertype'] = '2';
        }
        if($_POST['is_crm']==1){
            $where['is_crm'] = 1;
        }
        if($_POST['order_type']!=''){
            $where['type'] = $_POST['order_type'];
        }
        if($_POST['typezf']!=""){
            $where['order_type'] = $_POST['typezf'];
        }
        if($_POST['typedd']!=""){
            $where['type'] = $_POST['typedd'];
            if (isset($_POST['typedd']) && $_POST['typedd'] == 1){
                if (isset($_POST['rating']) && !empty($_POST['rating'])){
                    $where['rating'] = $_POST['rating'];
                }
            }
        }
        if($_POST['news_search']){
            if (!empty($keywordStr) && $_POST['typeca']=='1') {
                $where['order_id'] = array('like', $keywordStr);
            }elseif(!empty($keywordStr) && $_POST['typeca']=='2'){
                $UserinfoM = $this->MODEL('userinfo');
                $orderinfo = $UserinfoM->getList(array('username'=>array('like',$keywordStr)),array('field'=>'uid'));
                if (is_array($orderinfo)){
                    foreach ($orderinfo as $val){
                        $orderuids[] = $val['uid'];
                    }
                    $where['uid'] = array('in', pylode(",",$orderuids));
                }
            }elseif(!empty($keywordStr) && $_POST['typeca']=='3'){
                $UserinfoM = $this->MODEL('userinfo');
                $mWhere = array(
                    '1' => array('name' => array('like', $_POST['keyword'])),
                    '2' => array('name' => array('like', $_POST['keyword'])),
                    '3' => array('realname' => array('like', $_POST['keyword'])),
                    '4' => array('name' => array('like', $_POST['keyword']))
                );
                $m_uids = $UserinfoM->getUidsByWhere($mWhere);
                $where['uid'] = array('in', pylode(",",$m_uids));
            }
        }
        if(isset($_POST['time']) || $_POST['time_start1'] != "" || $_POST['time_end1'] != ""){
            $where['PHPYUNBTWSTART_B']      =   'AND';
            if($_POST['time']){
                if($_POST['time'] == 1){
                    $where['order_time'][]  =   array('>=',strtotime(date("Y-m-d 00:00:00")));
                }elseif ($_POST['time'] == 31){
                    $month = get_this_month(time());
                    $where['order_time'][] = array('>=',$month['start_month'],'and');
                    $where['order_time'][] = array('<=',$month['end_month'],'and');
                }else{
                    $where['order_time'][] = array('>=',strtotime('-'.intval($_POST['time']).' day'));
                }
            }
            if($_POST['time_start1']!=""){
                $where['order_time'][] = array('>=',strtotime($_POST['time_start1']." 00:00:00"));
            }
            if($_POST['time_end1']!=""){
                $where['order_time'][] = array('<',strtotime($_POST['time_end1']." 23:59:59"));
            }
            $where['PHPYUNBTWEND_B'] = '';
        }
        if(isset($_POST['order_state']) && $_POST['order_state']!=""){
            $where['order_state'] = $_POST['order_state'];
        }
        if(isset($_POST['fb']) && $_POST['fb']!=""){
            $where['is_invoice'] = $_POST['fb'];
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company_order', $where, $page, array('limit' => $pageSize));
        if($pages['total'] > 0){
            if($_POST['order']){
                $where['orderby'] = $_POST['t'].','.$_POST['order'];
            }else{
                $where['orderby'] = array('id,desc');
            }
            $where['limit'] = $pages['limit'];
            $rows = $OrderM->getList($where,array('utype'=>'admin','field'=>'id,uid,order_id,order_price,type,rating,order_state,order_type,order_time,once_id,crm_uid,usertype,order_remark'));
            unset($where['limit']);

        }
        $rt['list'] = $rows ? $rows : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);

    }

    function chatfriends_action(){

        $noUsername = true;
        $where = array();

        $chatM = $this->MODEL('chat');

        if ($_POST['uid']){

            $where['uid']  =  $_POST['uid'];
        }
        if ($_POST['usertype']){

            $where['usertype']  =  $_POST['usertype'];
        }

        if ($noUsername){
            $pageM          =   $this->MODEL('page');
            $pages          =   $pageM->adminPageList('chat_friend', $where,$_POST['page'],array('limit'=>$_POST['limit']));

            if ($pages['total'] > 0) {

                if($_POST['order']){

                    if($_POST['order']=="desc"){

                        $where['orderby']   =   $_POST['t'].',desc';

                    }else{

                        $where['orderby']   =   $_POST['t'].',asc';

                    }

                }else{

                    $where['orderby']       =   'id,desc';

                }

                $where['limit'] =   $pages['limit'];
                $chatList   =  $chatM->getFriendList($where,array('utype'=>'admin'));
            }
        }

        $data['list']           =   !empty($chatList)?$chatList:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']     =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }

    // 收到简历-分析-详情页求职者数据统计弹窗
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

        $this->render_json(0,'',$res);
    }

    // 收到简历-分析-详情页企业数据统计弹窗
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

        $this->render_json(0,'',$res);
    }

    // 收到简历-分析-锁定账号
    function lock_action(){

        $userinfoM  =  $this -> MODEL('userinfo');

        $post       =  array(
            'status'     =>  2,
            'lock_info'  =>  trim($_POST['lock_info'])
        );

        $return = $userinfoM->lock(array('uid'=>intval($_POST['uid'])),array('post'=>$post));

        $this->render_json($return['errcode'],$return['msg']);

    }

    // 新审核，增加企业详情
    function companyAudit_action(){
        $uid = intval($_POST['uid']);
        $ComM = $this->MODEL('company');
        $userinfoM = $this->MODEL('userinfo');
        $Info = $ComM->getInfo($uid, array('edit' => 1,'jlist' => 1, 'ywy'=>1));
        $member = $userinfoM->getInfo(array('uid' => $uid),array('field' => 'lock_info,status,login_address,login_ip,moblie_address,reg_date,login_date'));
        $Info['statusbody'] = trim($member['lock_info']);
        $Info['member_status'] = $member['status'];
        $Info['login_ip'] = $member['login_ip'];
        $Info['login_address'] = $member['login_address'];
        $Info['moblie_address'] = $member['moblie_address'];
        $Info['reg_date_n'] = $member['reg_date'] ? date('Y-m-d H:i', $member['reg_date']) : '';
        $Info['login_date_n'] = $member['login_date'] ? date('Y-m-d H:i', $member['login_date']) : '';
        $rt['Info'] = $Info;
        // 待审核数量
        $snum = $ComM->getCompanyNum(array('r_status' => 0, 'uid' => array('<>', $uid)));
        $rt['snum'] = $snum;
        $StatisM = $this->MODEL('statis');
        $statis = $StatisM->getInfo($uid, array('usertype'=>2));
        $ratingM = $this->MODEL('rating');
        $rating_list = $ratingM->getList(array('category' => '1'), array('field'=>'`id`,`name`'));
        $cacheM = $this->MODEL('cache');
        $options = array('com','city','hy');
        $cache = $cacheM->GetCache($options);
        $rt['cache'] = $cache;
        $rt['statis'] = $statis;
        $rt['rating_list'] = $rating_list;
        $this->render_json(0, '', $rt);
    }
    /**
     * @desc   后台企业列表 -- 会员审核
     */
    function status_action(){
        $userinfoM = $this->MODEL('userinfo');
        $post = array(
            'status' => intval($_POST['status']),
            'lock_info' => trim($_POST['statusbody']),
            'lock_status' => isset($_POST['lock_status']) ? intval($_POST['lock_status']) : '',
            'single' => isset($_POST['single']) ? $_POST['single'] : '',
        );
        $uids = @explode(',', $_POST['uid']);
        $return = $userinfoM->status(array('uid' => array('in', pylode(',', $uids)), 'usertype' => 2), array('post' => $post));
        if (isset($_POST['single'])){
            if ($return['errcode'] == 9){
                if($_POST['atype'] == 1){
                    // 仅保存
                    $this->render_json(0, $return['msg']);
                }else{
                    // 下一个待审核企业
                    $where  = array('r_status'=>0,'orderby'=>array('uid,desc'));
                    if(isset($_POST['crm_check']) && $_POST['crm_check']){
                        if (isset($_POST['crm_uid']) && intval($_POST['crm_uid']) > 0) {// 下属管理员uid

                            $where['crm_uid'] = intval($_POST['crm_uid']);
                        } else if ($_POST['crm_uid'] == 'all') {                        // 全部下属

                            $where['PHPYUNBTWSTART_W']  =   '';
                            $where['crm_uid'][]         =   array('>', 0, '');
                            $where['crm_uid'][]         =   array('in', $_POST['orgUids'], '');
                            $where['PHPYUNBTWEND_W']    =   '';
                        } else {
                            $where['crm_uid']   =   $_SESSION['auid'];
                        }
                    }
                    $ComM = $this->MODEL('company');
                    $row = $ComM->getCompanyInfo($where, array('field'=>'uid'));
                    if (!empty($row)){
                        $this->render_json(0, $return['msg'], array('uid' => $row['uid']));
                    }else{
                        $this->render_json(0, $return['msg']);
                    }
                }
            }else{
                $this->render_json(1, $return['msg']);
            }
        }else{
            if($return['errcode']==9){
                $this->admin_json(0, $return['msg']);
            }else{
                $this->render_json(1, $return['msg']);
            }
        }
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
}
?>