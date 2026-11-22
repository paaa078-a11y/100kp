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

class crm_org_kh_controller extends adminCommon
{

    function index_action()
    {

        $Company    =   $this->MODEL('company');
        $userInfoM  =   $this->MODEL('userinfo');
        $crmM       =   $this->MODEL('crm');
        $AdminM     =   $this->MODEL('admin');

        $month 	    = 	get_this_month(time());
        $todayStart =   strtotime('today');
        $pageType   =   trim($_POST['pageType']);

        if (isset($_POST['crm_uid']) && intval($_POST['crm_uid']) > 0) {
            $crm_uid = intval($_POST['crm_uid']);
        }

        $adminUserList  =   array();
        if ($pageType == 'all'){        //  客户列表

            $adminUserList  =   $AdminM->getList(array('is_crm' => 1), array('field' => '`uid`,`name`,`username`'));
        }else if ($pageType == 'org'){  //  下属客户

            $adminUserList  =   $this->getAdminUser();
        }

        $uids       =   array();        //  业务员UID
        foreach ($adminUserList as $ak => $av) {

            $uids[] =   $av['uid'];
        }


        if ($pageType == 'org') {

            $where['PHPYUNBTWSTART_A']  = '';
            $where['crm_uid'][]         =   array('>', 0, 'AND');
            $where['crm_uid'][]         =   array('in', pylode(',', $uids), 'AND');
            $where['PHPYUNBTWEND_A']    =   '';
        }

        if (isset($_POST['kh']) && !empty($_POST['kh'])) {

            $kh =   intval($_POST['kh']);
            if ($kh == 1) {

                $where['isfollow'] = 1;
            } elseif ($kh == 2) {

                $where['isfollow'] = 1;
                $where['f_time'] = array('>=', strtotime(date('Y-m-d')));
            } elseif ($kh == 3) {

                $where['PHPYUNBTWSTART_A1'] = '';
                $where['f_time'][] = array('>', 0);
                $where['f_time'][] = array('<', strtotime('-30 days'));
                $where['PHPYUNBTWEND_A1'] = '';
            } elseif ($kh == 4) {

                $where['PHPYUNBTWSTART_A1'] = '';
                $where['isfollow'] = array('=', '0', 'OR');
                $where['f_time'] = array('=', '', 'OR');
                $where['PHPYUNBTWEND_A1'] = '';
            } elseif ($kh == 5) {

                $where['PHPYUNBTWSTART_A1'] = '';
                $where['vipetime'][] = array('>', time(), 'AND');
                $where['vipetime'][] = array('<', strtotime('+15 days'), 'AND');
                $where['PHPYUNBTWEND_A1'] = '';
            } elseif ($kh == 6) {

                $where['login_date'] = array('>=', strtotime('today'));
            } elseif ($kh == 10) {

                $where['clue'] = 2;
            } else if ($kh == 11) {// 15天未跟进
                $where['PHPYUNBTWSTART_A1'] = '';
                $where['f_time'][] = array('>', 0);
                $where['f_time'][] = array('<', strtotime('-15 days'));
                $where['PHPYUNBTWEND_A1'] = '';
            }
        }

        if (isset($crm_uid)) {

            $where['crm_uid'] = $crm_uid;
        }

        if (isset($_POST['crmType']) && !empty($_POST['crmType'])) {

            $where['crm_type'] = intval($_POST['crmType']);
        }

        if (isset($_POST['crm_status']) && !empty($_POST['crm_status'])) {

            $where['crm_status'] = intval($_POST['crm_status']);
        }

        if (isset($_POST['rating']) && !empty($_POST['rating'])) {

            $rating =   intval($_POST['rating']);
            if($_POST['rating'] > 0){

                $where['rating']        =   $rating;
                if (!isset($where['vipetime'])){

                    $where['PHPYUNBTWSTART_B']  =   '';
                    $where['vipetime'][]        =   array('=', 0, '');
                    $where['vipetime'][]        =   array('>', strtotime('today'), 'OR');
                    $where['PHPYUNBTWEND_B']    =   '';
                }
            }else{

                $where['PHPYUNBTWSTART_B']  =   '';
                $where['vipetime']          =   array(
                    0   =>  array('<', strtotime('today'), ''),
                    1   =>  array('>', 0, '')
                );
                $where['PHPYUNBTWEND_B']    =   '';
            }
        }

        if (isset($_POST['vipEtime']) && !empty($_POST['vipEtime'])) {
            $etime  =   intval($_POST['vipEtime']);
            if ($etime == 1) {

                $where['PHPYUNBTWSTART_C']  =   '';
                $where['vipetime'][]        =   array('<', strtotime(date('Y-m-d')), 'AND');
                $where['vipetime'][]        =   array('>', '0', 'AND');
                $where['PHPYUNBTWEND_C']    =   '';
            } else {
                if ($etime == 2) {

                    $num    =   '+3 days';
                } else if ($etime == 3) {

                    $num    =   '+7 days';
                } elseif ($etime == 4) {

                    $num    =   '+15 days';
                } elseif ($etime == 5) {

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

                $certWhere  =   array();

                if ($yyzz_status == 2) {

                    $certWhere  =   array('type' => 3, 'status' => 0);
                } elseif ($yyzz_status == 3) {

                    $certWhere  =   array('type' => 3, 'status' => 3);
                } elseif ($yyzz_status == 4) {

                    $certWhere  =   array('type' => 3);
                }

                $certList   =   $Company->getCertList($certWhere, array('field' => '`uid`'));
                $uidArrA    =   array();
                foreach ($certList as $cv) {

                    $uidArrA[]  =   $cv['uid'];
                }

                if ($yyzz_status == 4) {

                    $where['uid']   =   array('notin', pylode(',', $uidArrA));
                } else {

                    $where['uid']   =   array('in', pylode(',', $uidArrA));
                }
            }

        }

        if (isset($_POST['hasJob']) && !empty($_POST['hasJob'])) {

            $where['jobtime']   =   intval($_POST['hasJob']) == 1 ? array('>', 0) : 0;
        }
        if($_POST['fact_status']){
            if($_POST['fact_status'] == '1'){
                $where['fact_status'] = 1;
            }elseif($_POST['fact_status'] == '2'){
                $where['fact_status'] = 0;
            }
        }
        if (isset($_POST['lfTime']) && !empty($_POST['lfTime'])) {

            $lfTime = intval($_POST['lfTime']);

            if ($lfTime == 1) { // 今日跟进

                $where['f_time'] = array('>', strtotime(date('Y-m-d')));
            } elseif ($lfTime == 2) {   //  昨日跟进

                $today      =   strtotime(date('Y-m-d'));
                $yesterday  =   $today - 86400;
                $where['PHPYUNBTWSTART_D'] = '';
                $where['f_time'][]  =   array('>=', $yesterday);
                $where['f_time'][]  =   array('<', $today);
                $where['PHPYUNBTWEND_D'] = '';
            } elseif ($lfTime == 3) {

                $where['PHPYUNBTWSTART_D'] = '';
                $where['f_time'][] = array('>', 0);
                $where['f_time'][] = array('<', strtotime('-3 days'));
                $where['PHPYUNBTWEND_D'] = '';
            } elseif ($lfTime == 4) {

                $where['PHPYUNBTWSTART_D'] = '';
                $where['f_time'][] = array('>', 0);
                $where['f_time'][] = array('<', strtotime('-7 days'));
                $where['PHPYUNBTWEND_D'] = '';
            } elseif ($lfTime == 5) {

                $where['PHPYUNBTWSTART_D'] = '';
                $where['f_time'][] = array('>', 0);
                $where['f_time'][] = array('<', strtotime('-1 month'));
                $where['PHPYUNBTWEND_D'] = '';
            } elseif ($lfTime == 6) {

                $where['PHPYUNBTWSTART_D'] = '';
                $where['f_time'][] = array('>', 0);
                $where['f_time'][] = array('<', strtotime('-3 month'));
                $where['PHPYUNBTWEND_D'] = '';
            }
        }

        if (isset($_POST['nfTime']) && !empty($_POST['nfTime'])) {

            $nfTime     =   intval($_POST['nfTime']);
            $taskWhere  =   array('type' => 22, 'status' => 1);
            if ($nfTime == 1) {

                $taskWhere['PHPYUNBTWSTART']=   '';
                $taskWhere['stime'][]       =   array('>', 0);
                $taskWhere['stime'][]       =   array('<', strtotime(date('Y-m-d 23:59:59')));
                $taskWhere['PHPYUNBTWEND']  =   '';
            }else if ($nfTime == 2) {

                $taskWhere['PHPYUNBTWSTART']=   '';
                $taskWhere['stime'][]       =   array('>', strtotime(date('Y-m-d 23:59:59')));
                $taskWhere['stime'][]       =   array('<', strtotime(date('Y-m-d 23:59:59')) + 86400);
                $taskWhere['PHPYUNBTWEND']  =   '';
            }else if ($nfTime == 3) {

                $taskWhere['PHPYUNBTWSTART']=   '';
                $taskWhere['stime'][]       =   array('>', strtotime('today'));
                $taskWhere['stime'][]       =   array('<', strtotime('+3 days'));
                $taskWhere['PHPYUNBTWEND']  =   '';
            }else if ($nfTime == 4) {

                $taskWhere['PHPYUNBTWSTART']=   '';
                $taskWhere['stime'][]       =   array('>', strtotime('today'));
                $taskWhere['stime'][]       =   array('<', strtotime('+7 days'));
                $taskWhere['PHPYUNBTWEND']  =   '';
            }else if ($nfTime == 5) {

                $taskWhere['PHPYUNBTWSTART']=   '';
                $taskWhere['stime'][]       =   array('>', strtotime('today'));
                $taskWhere['stime'][]       =   array('<', strtotime('+1 month'));
                $taskWhere['PHPYUNBTWEND']  =   '';
            }else if ($nfTime == 6) {

                $taskWhere['PHPYUNBTWSTART']=   '';
                $taskWhere['stime'][]       =   array('>', strtotime('today'));
                $taskWhere['stime'][]       =   array('<', strtotime('+3 month'));
                $taskWhere['PHPYUNBTWEND']  =   '';
            }
            $taskWhere['uid']   =   $_SESSION['auid'];
            $taskList   =   $crmM->getTaskList($taskWhere, array('field' => '`comid`'));
            $uidArrN    =   array();
            foreach ($taskList as $tv) {
                $uidArrN[]  =   $tv['comid'];
            }
            $where['uid']   =   array('in', pylode(',', $uidArrN));
        }

        if (isset($_POST['loginDate']) && !empty($_POST['loginDate'])) {

            $start  =   date('Y-m-d', $_POST['loginDate'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['loginDate'][1] / 1000);

            $where['PHPYUNBTWSTART_G']  =   '';
            $where['login_date'][]      =   array('>=', strtotime($start . " 00:00:00"));
            $where['login_date'][]      =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_G']    =   '';
        }

        if (isset($_POST['regDate']) && !empty($_POST['regDate'])) {

            $start  =   date('Y-m-d', $_POST['regDate'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['regDate'][1] / 1000);

            $mWhere['PHPYUNBTWSTART']   =   '';
            $mWhere['reg_date'][]       =   array('>=', strtotime($start . " 00:00:00"));
            $mWhere['reg_date'][]       =   array('<=', strtotime($end . " 23:59:59"));
            $mWhere['PHPYUNBTWEND']     =   '';
            $memberList =   $userInfoM->getList($mWhere, array('field' => 'uid'));

            $uidArrB    =   array();
            foreach ($memberList as $mv) {
                $uidArrB[]  =   $mv['uid'];
            }

            $where['uid']   =   array('in', pylode(',', $uidArrB));
        }

        if(isset($_POST['r_status']) && !empty($_POST['r_status'])) {

            $where['r_status']  =   $_POST['r_status'] == 1 ? 0 : $_POST['r_status'];
        }

        if (isset($_POST['ktype']) && isset($_POST['keyword'])) {

            $keywordStr =   trim($_POST['keyword']);
            $typeStr    =   intval($_POST['ktype']);

            if (!empty($keywordStr)) {
                if ($typeStr == 1) {

                    $where['name'] = array('like', $keywordStr);
                } else if ($typeStr == 2) {

                    $where['linkman'] = array('like', $keywordStr);
                } else if ($typeStr == 3) {

                    $where['linktel'] = $keywordStr;
                } else if ($typeStr == 4) {

                    $where['uid'] = $keywordStr;
                }
            }
        }

        if ($_POST['crm_time']) {
            $timeCt = explode('-', $_POST['crm_time']);
            $monthCt = (int)$timeCt[1];

            $sTimeCt = mktime(0, 0, 0, $monthCt, 1, $timeCt[0]);
            $eTimeCt = mktime(23, 59, 59, $monthCt + 1, 0, $timeCt[0]);

            $where['PHPYUNBTWSTART_CRM_TIME'] = '';
            $where['crm_time'][] = array('>=', $sTimeCt, '');
            $where['crm_time'][] = array('<', $eTimeCt, '');
            $where['PHPYUNBTWEND_CRM_TIME'] = '';
        }

        //  CRM - 管理员 - 数据看板
        if (!empty($_POST['source']) && $_POST['source'] == 'dataBoard') {
            $jttype = $_POST['jttype'];
            // 待跟进汇总
            if (in_array($jttype, array('jxgj', 'gyx', 'hy', 'gq', 'qz', 'dxf'))) {
                $where['uid'] = array('in', pylode(',', $this->gjhzKhHandle($jttype)));
                if (($jttype == 'dxf' || $jttype == 'gq') && $this->config['crm_deal_rating']) {
                    $where['rating'] = array('in', $this->config['crm_deal_rating']);
                }
            }
            // 数据汇总
            if ($jttype == 'data_gh') {// 公海客户
                $where['crm_uid'] = 0;
            } else if ($jttype == 'data_xs') {// 线索数
                $where['clue'] = 2;
            } else if ($jttype == 'data_cj') {// 成交客户
                $where['PHPYUNBTWSTART_CJ'] = '';
                $where['vipetime'][] = array('=', '0', '');
                $where['vipetime'][] = array('>', strtotime('today'), 'OR');
                $where['PHPYUNBTWEND_CJ'] = '';
                if ($this->config['crm_deal_rating']) {
                    $where['rating'] = array('in', $this->config['crm_deal_rating']);
                }
            } else if ($jttype == 'data_gq') {// 过期客户
                $where['PHPYUNBTWSTART_GQ'] = '';
                $where['vipetime'][] = array('>', '0', '');
                $where['vipetime'][] = array('<', strtotime('today'));
                $where['PHPYUNBTWEND_GQ'] = '';
                if ($this->config['crm_deal_rating']) {
                    $where['rating'] = array('in', $this->config['crm_deal_rating']);
                }
            } else if ($jttype == 'data_dxf') {// 待续费客户
                // 获取数据统计跟进周期
                $cycle = 15; // 默认15天
                $today = strtotime('today'); // 今天
                $where['PHPYUNBTWSTART_DXF'] = '';
                $where['vipetime'][] = array('>=', $today, '');
                $where['vipetime'][] = array('<', strtotime("+{$cycle} day", $today), '');
                $where['PHPYUNBTWEND_DXF'] = '';
                if ($this->config['crm_deal_rating']) {
                    $where['rating'] = array('in', $this->config['crm_deal_rating']);
                }
            }
        }
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company', $where, $_POST['page'], array('limit' => $_POST['limit']));
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                if ($_POST['t']) {

                    $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                } else {

                    $where['orderby']   =   'login_date, desc';
                }
            } else {
                $where['orderby']   =   'uid, desc';
            }
            $where['limit'] =   $pages['limit'];
            $comList        =   $Company->getList($where, array('utype' => 'crm', 'self' => 1));
            $list = !empty($comList['list'])?$comList['list']:array();
            foreach ($list as $k => $v) {

                $list[$k]['wxBindmsg'] = $this->wxBindState($v);

                if ($todayStart < $v['vipetime'] || ($v['vipetime'] == '0' && $v['rating'] == '0')) {

                    $list[$k]['isvip']  =   1;
                } else {

                    $list[$k]['isvip']  =   0;
                }

                $list[$k]['vipetime_n']     =   date('Y-m-d', $v['vipetime']);
                $list[$k]['vipetime_status']=   0;
                if ($v['vipetime'] >= $todayStart) {

                    $list[$k]['vipetime_status']    =   1;
                } else if ($v['vipetime'] < $todayStart && $v['vipetime'] != 0) {

                    $list[$k]['vipetime_status']    =   2;
                } else {

                    $list[$k]['vipetime_n']         =   '永久';
                }

                $list[$k]['jobnum']     =   $v['jobnum'] > 0 ? $v['jobnum'] : 0;
                $list[$k]['zz_jobnum']  =   $v['zz_jobnum'] > 0 ? $v['zz_jobnum'] : 0;
                $list[$k]['crm_type']   =   $v['crm_type'] ? $v['crm_type'] : '--';
                $list[$k]['crm_status'] =   $v['crm_status'] ? $v['crm_status'] : '--';
                $list[$k]['crm_pic_arr']=   !empty($v['c_pic_arr']) ? explode('&', $v['c_pic_arr']) : array();
            }
        }
        $data['list']   =   !empty($list) ? $list : array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']      =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }
    function getIndexStaticData_action(){

        $pageType   =   trim($_POST['pageType']);
        if (isset($_POST['crm_uid']) && intval($_POST['crm_uid']) > 0) {
            $crm_uid = intval($_POST['crm_uid']);
        }

        $AdminM     =   $this->MODEL('admin');
        $crmM       =   $this->MODEL('crm');
        $cacheM     =   $this->MODEL('cache');

        $adminUserList  =   array();
        if ($pageType == 'all'){        //  客户列表

            $adminUserList  =   $AdminM->getList(array('is_crm' => 1), array('field' => '`uid`,`name`,`username`'));
        }else if ($pageType == 'org'){  //  下属客户

            $adminUserList  =   $this->getAdminUser();
        }
        $uids       =   array();        //  业务员UID
        foreach ($adminUserList as $ak => $av) {

            $uids[] =   $av['uid'];
        }

        $defnumarr      =   array('companyAllNum' => 0, 'companyStatusNum1' => 0, 'companyStatusNum2' => 0, 'companyStatusNum3' => 0, 'companyStatusNum4' => 0);
        if (!empty($uids)) {
            if (isset($crm_uid) || $pageType == 'org') { // 指定客户ID 或者 下属客户
                $numarr =   $crmM->getCustomerNum(array('type' => 2, 'crm_uid' => pylode(',', isset($crm_uid) ? $crm_uid : $uids)));
            } elseif ($pageType == 'all') {
                $numarr =   $crmM->getCustomerNum();
            }
        }

        $ratingM                =   $this->MODEL('rating');
        $ratingArr              =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));


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
        $data['ratingArr']      =   $ratingArr;
        $data['numarr'] =   !empty($numarr) ? array_merge($defnumarr, $numarr) : $defnumarr;
        $data['adminUserList']  =  $adminUserList;
        $data['config']         =   array(
            'com_free_status'   =>  $this->config['com_free_status'],
            'com_social_credit' =>  $this->config['com_social_credit'],
            'com_cert_owner'    =>  $this->config['com_cert_owner'],
            'com_cert_wt'       =>  $this->config['com_cert_wt'],
            'com_cert_other'    =>  $this->config['com_cert_other'],
        );
        $this->render_json(0, 'ok', $data);
    }

    /**
     * 数据看板跳转待跟进汇总客户列表，参数处理
     * 此方法同 dataBoard.class.php 中 dgj 方法同步修改查询条件
     * @param $type 处理的数据类型
     */
    private function gjhzKhHandle($type)
    {
        $today = strtotime('today'); // 今天
        $week = strtotime('-6 day', $today); // 取一周时间（包含当天）
        $month = strtotime('-1 month +1 day', $today); // 取一个月前的时间（包含当天）;

        $companyM = $this->MODEL('company');

        $comWhere = "crm_uid > 0 AND (isfollow = 0 OR (isfollow = 1 AND f_time < FTIME))";

        // crm设置 成交会员
        $rating = $this->MODEL('rating');
        $ratings = $rating->getList(array('category' => 1, 'orderby' => array('sort,desc')));
        $cjRatingIds = array();// 成交会员id
        $fcjRatingIds = array();// 非成交会员id
        if ($this->config['crm_deal_rating']) {
            $cjRatingIds = explode(',', $this->config['crm_deal_rating']);
        }
        foreach ($ratings as $v) {
            !in_array($v['id'], $cjRatingIds) && array_push($fcjRatingIds, $v['id']);
        }

        if ($type == 'gq' || $type == 'dxf') {
            $jgq_cycle = $this->config['crm_jobtai_jgq_cycle'] ? intval($this->config['crm_jobtai_jgq_cycle']) : 3; // 获取工作台数据周期 - 将过期
            $jgq_cycle = $jgq_cycle - 1;
            $startTimeJgq = strtotime("-{$jgq_cycle} day", $today); // 将过期
            $companyListJgq = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeJgq, $comWhere), 'uid,login_date,rating,vipetime');
            $companyUidDqArr = array(); // 会员到期企业
            $companyUidJdqArr = array(); // 将到期
            if ($companyListJgq) {
                foreach ($companyListJgq as $ckey => $cval) {
                    if (in_array($cval['rating'], $cjRatingIds) && $cval['vipetime'] && $cval['vipetime'] < $today) { // 到期
                        $companyUidDqArr[] = $cval['uid'];
                    }
                    if ($cval['vipetime'] && $cval['vipetime'] >= $today && $cval['vipetime'] < strtotime("+15 day", $today)) { // 将到期
                        if ($cjRatingIds && in_array($cval['rating'], $cjRatingIds)) { // 会员等级在CRM设置的成交会员等级中
                            $companyUidJdqArr[] = $cval['uid'];
                        }
                    }
                }
            }
        }

        $uidArr = [-1];
        switch ($type) {
            case 'gyx':
                // 高意向用户
                // 非成交会员、触发权益提醒、7天未跟进
                $gyx_cycle = $this->config['crm_jobtai_gyx_cycle'] ? intval($this->config['crm_jobtai_gyx_cycle']) : 3; // 获取工作台数据周期 - 高意向
                $gyx_cycle = $gyx_cycle - 1;
                $startTimeGyx = strtotime("-{$gyx_cycle} day", $today); // 高意向
                $companyListGyx = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeGyx, $comWhere), 'uid,login_date,rating,vipetime');
                $companyUidGyxArr = array(); // 高意向 非成交会员
                $companyUidGyxDqHyArr = array(); // 到期且活跃
                if ($companyListGyx) {
                    foreach ($companyListGyx as $ckey => $cval) {
                        if (!in_array($cval['rating'], $cjRatingIds)) { // 高意向 非成交会员
                            $companyUidGyxArr[] = $cval['uid'];
                        }
                        if ($cval['login_date'] >= $week && $cval['vipetime'] && $cval['vipetime'] < $today && in_array($cval['rating'], $cjRatingIds)) { // 会员等级在CRM设置的成交会员等级中 过期 且1周内有登录
                            $companyUidGyxDqHyArr[] = $cval['uid'];
                        }
                    }
                }
                // 高意向用户 X天内未跟进 +（非成交会员，触发权益提醒 OR 7天内有登录，历史成交过，但未续费的客户）
                $gyxUidArr = array();
                if ($companyUidGyxArr) {
                    $gyxList = $this->obj->select_all('rating_notice', array(
                        'crm_uid' => array('>', 0),
                        'groupby' => 'uid',
                        'uid' => array('in', pylode(',', $companyUidGyxArr)),
                        'send_time' => array('>=', $startTimeGyx)
                    ), 'uid'); // // X天未跟进，触发权益提醒
                    $gyxUidArr = $gyxList ? array_column($gyxList, 'uid') : array();
                }
                if ($companyUidGyxDqHyArr) {
                    $gyxOrderList = $this->obj->select_all('company_order', array(
                        'uid' => array('in', pylode(',', $companyUidGyxDqHyArr)),
                        'type' => 1,
                        'order_state' => 2,
                        'groupby' => 'uid',
                    ), 'uid,count(uid) as num');// 7天内有登录，历史成交过，但未续费的客户
                    $gyxUidArr = $gyxOrderList ? array_unique(array_merge($gyxUidArr, array_column($gyxOrderList, 'uid'))) : $gyxUidArr;
                }
                $uidArr = $gyxUidArr;
                break;
            case 'qz':
                // 潜在用户
                // 潜在,X天内未跟进的企业
                $qz_cycle = $this->config['crm_jobtai_qz_cycle'] ? intval($this->config['crm_jobtai_qz_cycle']) : 3; // 获取工作台数据周期 - 潜在
                $qz_cycle = $qz_cycle - 1;
                $startTimeQz = strtotime("-{$qz_cycle} day", $today); // 潜在
                $companyListQz = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeQz, $comWhere), 'uid,login_date,rating,vipetime');
                $companyUidQzArr = array(); // 一个月未登录的企业（长期未登录）
                if ($companyListQz) {
                    foreach ($companyListQz as $ckey => $cval) {
                        if ($cval['login_date'] < $month) {
                            if (!in_array($cval['rating'], $cjRatingIds)) { // 非成交会员，即为未开通会员
                                $companyUidQzArr[] = $cval['uid'];
                            } elseif ($cval['vipetime'] && $cval['vipetime'] < $today) { // 会员过期
                                if ($cjRatingIds && in_array($cval['rating'], $cjRatingIds)) { // 会员等级在CRM设置的成交会员等级中
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
                            if (!in_array($cval['rating'], $cjRatingIds)) { // 非成交会员，即为未开通会员
                                $companyUidHyArr[] = $cval['uid'];
                            } elseif ($cval['vipetime'] && $cval['vipetime'] < $today && in_array($cval['rating'], $cjRatingIds)) { // 会员过期 并且会员等级在CRM设置的成交会员等级中
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
                        'crm_uid' => array('>', 0),
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
                                        break;
                                    }
                                }
                                if ($rsval['follow_day'] > 0) { // 跟进
                                    $fetime = strtotime("-{$rsval['follow_day']} day");
                                    if (in_array($rrating, @explode(',', $rsval['rating']))
                                        && (($jxval['crm_time'] < $fetime && empty($jxval['f_time']))
                                            || (!empty($jxval['f_time']) && $jxval['f_time'] < $fetime))) {
                                        $jxgjUidArr[] = $jxval['uid'];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                // 查询设置下次跟进时间并且未跟进的客户
                $planSql = "SELECT c.comid uid FROM {$this->def}company a JOIN {$this->def}crm_work_plan c ON a.uid = c.comid WHERE a.`crm_uid` > 0 AND c.auid > 0 AND c.stime < " .strtotime('+1 day', $today) . " AND c.type = 22 AND c.status = 1 GROUP BY c.comid";
                $planList = $this->obj->DB_query_all($planSql, 'all');
                $planUidArr = $planList ? array_column($planList, 'uid') : array();
                $planUidArr && $jxgjUidArr = array_unique(array_merge($jxgjUidArr, $planUidArr));
                $jxgjUidArr && $uidArr = $jxgjUidArr;
                break;
            case 'gq':
                // 过期用户
                if ($companyUidDqArr) {
                    $orderList = $this->obj->select_all('company_order', array(
                        'uid' => array('in', pylode(',', $companyUidDqArr)),
                        'type' => 1,
                        'order_state' => 2,
                        'groupby' => 'uid'
                    ), 'uid');
                    $orderList && $uidArr = array_column($orderList, 'uid'); // 3天未跟进，历史成交过，但未续费的客户
                }
                break;
            case 'dxf':
                // 待续费用户，将到期
                $companyUidJdqArr && $uidArr = $companyUidJdqArr;
                break;
        }
        return $uidArr;
    }
}

?>