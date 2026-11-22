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

    function set_search()
    {

        $cacheM     =   $this->MODEL('cache');
        $crmCache   =   $cacheM->GetCache(array('crm'));
        $this->yunset(array('cache' => $crmCache, 'crmClassName' => $crmCache['crmclass_name'], 'crmStatus' => $crmCache['crmdata']['client_status'], 'crmType' => $crmCache['crmdata']['client_type']));
        $ratingM    =   $this->MODEL('rating');
        $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
        $vipEtime   =   array('5' => '已到期', '1' => '三天内', '2' => '七天内', '3' => '十五天内', '4' => '一月内');
        $yyzz       =   array('1' => '认证通过', '2' => '等待审核', '3' => '认证失败', '4' => '尚未认证');
        $lastFtime  =   array('1' => '从未跟进', '2' => '今日跟进', '3' => '三天前', '4' => '七天前', '5' => '一月前', '6' => '百日前');
        $nextFtime  =   array('1' => '今天', '2' => '明天', '3' => '三天内', '4' => '七天内', '5' => '一月内');
        $loginDate  =   array('1' => '今天', '2' => '本周', '3' => '本月', '4' => '一个月前', '5' => '三个月前', '6' => '半年前', '7' => '自定义');
        $hasJob     =   array('1' => '是', '2' => '否');
        $this->yunset(array('yyzzStatus' => $yyzz, 'ratingArr' => $ratingArr, 'vipEtime' => $vipEtime, 'lastFtime' => $lastFtime, 'nextFtime' => $nextFtime, 'loginDate' => $loginDate, 'hasJob' => $hasJob));
        $this->yunset('todayStart', strtotime('today'));
    }

    private function getAdminUser()
    {

        $AdminM         =   $this->MODEL('admin');
        $adminUserInfo  =   $AdminM->getAdminUser(array('uid' => $_SESSION['auid']), array('field' => 'org,power,spower'));
        $adminUserList  =   array();
        if (isset($adminUserInfo) && $adminUserInfo['org'] > 0) {

            $crmM       =   $this->MODEL('crm');
            $orgInfo    =   $crmM->getOrgInfo(array('id' => $adminUserInfo['org']));

            $orgIds = $orgIdss = $orgIdsss = array();

            if ($adminUserInfo['power'] == '1') {    // 同级部门权限

                $orgIds[] = $orgInfo['id'];
            }

            if ($adminUserInfo['spower'] == '1') {    // 子部门权限
                if ($orgInfo['level'] == '1') {

                    $orgList = $crmM->getOrgList(array('fid' => $adminUserInfo['org']));
                    foreach ($orgList as $ok => $ov) {
                        $orgIdss[] = $ov['id'];
                    }

                    $orgLists = $crmM->getOrgList(array('fid' => array('in', pylode(',', $orgIdss))));
                    foreach ($orgLists as $ook => $oov) {
                        $orgIdsss[] = $oov['id'];
                    }

                } elseif ($orgInfo['level'] == '2') {

                    $orgList = $crmM->getOrgList(array('fid' => $adminUserInfo['org']));
                    foreach ($orgList as $ok => $ov) {

                        $orgIdss[] = $ov['id'];
                    }
                }
            }

            $orgIdArr = array_merge($orgIds, $orgIdss, $orgIdsss);

            $adminUserList = $AdminM->getList(array('uid' => array('<>', $_SESSION['auid']), 'org' => array('in', pylode(',', $orgIdArr))), array('field' => '`uid`,`name`,`username`'));
        }

        return $adminUserList;
    }

    function index_action()
    {

        $this->set_search();

        $Company    =   $this->MODEL('company');
        $userInfoM  =   $this->MODEL('userinfo');
        $crmM       =   $this->MODEL('crm');

        $month 	= 	get_this_month(time());

        $adminUserList  =   $this->getAdminUser();

        $this->yunset('adminUserList', $adminUserList);
        $uids       =   array();
        foreach ($adminUserList as $ak => $av){
            $uids[] =   $av['uid'];
        }

        if (isset($_GET['crm_uid']) && $_GET['crm_uid'] > 0) {

            $where['crm_uid']   =   $_GET['crm_uid'];
            $urlarr['crm_uid']  =   $_GET['crm_uid'];
        } else {

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['crm_uid'][]         =   array('>', 0, 'AND');
            $where['crm_uid'][]         =   array('in', pylode(',', $uids), 'AND');
            $where['PHPYUNBTWEND_A']    =   '';
        }

        //判断是否有职位
        if (isset($_GET['hasJob'])) {
            if ($_GET['hasJob'] == '1') {
                $where['jobtime'] = array('>', 0);
            } elseif ($_GET['hasJob'] == '2') {
                $where['jobtime'] = 0;
            }
            $urlarr['hasJob'] = $_GET['hasJob'];
        }

        if (isset($_GET['kh']) && !empty($_GET['kh'])) {

            $kh =   intval($_GET['kh']);
            if ($kh == 1) {

                $where['isfollow']  =   1;
            } elseif ($kh == 2) {

                $where['isfollow']  =   1;
                $where['f_time']    =   array('>=', strtotime(date('Y-m-d')));
            } elseif ($kh == 3) {

                $where['PHPYUNBTWSTART_A1']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-30 days'));
                $where['PHPYUNBTWEND_A1']    =   '';
            } elseif ($kh == 4) {

                $where['PHPYUNBTWSTART_A1']  =   '';
                $where['isfollow']          =   array('=', '0', 'OR');
                $where['f_time']            =   array('=', '', 'OR');
                $where['PHPYUNBTWEND_A1']    =   '';
            } elseif ($kh == 5) {

                $where['PHPYUNBTWSTART_A1']  =   '';
                $where['vipetime'][]        =   array('>', time(), 'AND');
                $where['vipetime'][]        =   array('<', strtotime('+15 days'), 'AND');
                $where['PHPYUNBTWEND_A1']    =   '';
            } elseif ($kh == 10){

                $where['clue']              =   2;
            }

            $urlarr['kh']   =   $kh;
        }

        if (isset($_GET['crmType']) && !empty($_GET['crmType'])) {

            $crmType    =   intval($_GET['crmType']);
            $where['crm_type']  =   $crmType;
            $urlarr['crmType']  =   $crmType;
        }

        if (isset($_GET['crm_status']) && !empty($_GET['crm_status'])) {

            $crm_status =   intval($_GET['crm_status']);
            $where['crm_status']    =   $crm_status;
            $urlarr['crm_status']   =   $crm_status;
        }

        if (isset($_GET['rating']) && !empty($_GET['rating'])) {

            $rating =   intval($_GET['rating']);
            if($_GET['rating'] > 0){

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
            $urlarr['rating']   =   $rating;
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

            $urlarr['yyzz_status']  =   $yyzz_status;
        }

        if (isset($_GET['lastFtime']) && !empty($_GET['lastFtime'])) {

            $lastFtime  =   intval($_GET['lastFtime']);

            if ($lastFtime == 1) {
                $where['PHPYUNBTWSTART_D']  =   '';
                $where['isfollow']          =   array('=', '0', 'OR');
                $where['f_time']            =   array('=', '', 'OR');
                $where['PHPYUNBTWEND_D']    =   '';
            } elseif ($lastFtime == 2) {
                $where['f_time']            =   array('>', strtotime(date('Y-m-d')));
            } elseif ($lastFtime == 3){
                $where['PHPYUNBTWSTART_D']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-3 days'));
                $where['PHPYUNBTWEND_D']    =   '';
            }elseif ($lastFtime == 4){
                $where['PHPYUNBTWSTART_D']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-7 days'));
                $where['PHPYUNBTWEND_D']    =   '';
            }elseif ($lastFtime == 5){
                $where['PHPYUNBTWSTART_D']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-30 days'));
                $where['PHPYUNBTWEND_D']    =   '';
            }elseif ($lastFtime == 6){
                $where['PHPYUNBTWSTART_D']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-100 days'));
                $where['PHPYUNBTWEND_D']    =   '';
            }

            $urlarr['lastFtime']            =   $lastFtime;
        }

        if (isset($_GET['nextFtime']) && !empty($_GET['nextFtime'])) {

            $nextFtime  =   intval($_GET['nextFtime']);

            $taskWhere  =   array('type' => 22, 'status' => 1);

            if ($nextFtime == 1) {

                $taskWhere['PHPYUNBTWSTART_E']  =   '';
                $taskWhere['stime'][]           =   array('<', strtotime(date('Y-m-d 23:59:59')));  //  之前未完成的跟进同步显示
                $taskWhere['stime'][]           =   array('>', 0);
                $taskWhere['PHPYUNBTWEND_E']    =   '';
            } elseif ($nextFtime == 2) {

                $taskWhere['PHPYUNBTWSTART_E']  =   '';
                $taskWhere['stime'][]           =   array('>', strtotime(date('Y-m-d 23:59:59')));
                $taskWhere['stime'][]           =   array('<', strtotime(date('Y-m-d 23:59:59')) + 86400);
                $taskWhere['PHPYUNBTWEND_E']    =   '';
            } elseif ($nextFtime == 3) {

                $taskWhere['PHPYUNBTWSTART_E']  =   '';
                $taskWhere['stime'][]           =   array('>', strtotime('today'));
                $taskWhere['stime'][]           =   array('<', strtotime('+3 day'));
                $taskWhere['PHPYUNBTWEND_E']    =   '';
            } elseif ($nextFtime == 4) {

                $taskWhere['PHPYUNBTWSTART_E']  =   '';
                $taskWhere['stime'][]           =   array('>', strtotime('today'));
                $taskWhere['stime'][]           =   array('<', strtotime('+1 week'));
                $taskWhere['PHPYUNBTWEND_E']    =   '';
            } elseif ($nextFtime == 5) {

                $taskWhere['PHPYUNBTWSTART_E']  =   '';
                $taskWhere['stime'][]           =   array('>', strtotime('today'));
                $taskWhere['stime'][]           =   array('<', strtotime('+1 month'));
                $taskWhere['PHPYUNBTWEND_E']    =   '';
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

        if ($_GET['keyword']) {

            $keywordStr =   trim($_GET['keyword']);
            $typeStr    =   intval($_GET['crm_type']);

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
            $urlarr['crm_type'] = $typeStr;
            $urlarr['keyword']  = $keywordStr;
        }

        if(isset($_GET['r_status'])){
            $where['r_status']       =   $_GET['r_status'];
        }

        $urlarr         =   $_GET;
        $urlarr['page'] =   '{{page}}';

        $pageurl    =   Url($_GET['m'], $urlarr, 'admin');
        $pageM      =   $this->MODEL('page');

        $pages  =   $pageM->pageList('company', $where, $pageurl, $_GET['page']);
        if ($pages['total'] > 0) {
            if ($_GET['order']) {
                if ($_GET['t']) {

                    $where['orderby']   =   $_GET['t'].', '.$_GET['order'];
                    $urlarr['t']        =   $_GET['t'];
                } else {

                    $where['orderby']   =   'login_date, desc';
                }
                $urlarr['order']    =   $_GET['order'];
            }
            $where['limit'] =   $pages['limit'];
            $comList        =   $Company->getList($where, array('utype' => 'crm', 'self' => 1));
            foreach ($comList['list'] as $key => $val) {

                $comList['list'][$key]['wxBindmsg'] =   $this->wxBindState($val);
            }
            $this->yunset('rows', $comList['list']);
        }
        $this->yuntpl(array('admin/crm_org_kh'));
    }

    function getComNum_action()
    {

        $crmM           =   $this->MODEL('crm');

        $adminUserList  =   $this->getAdminUser();

        $uids           =   array();
        foreach ($adminUserList as $ak => $av) {
            $uids[]     =   $av['uid'];
        }

        if (!empty($uids)){

            echo $crmM->getCustomerNum(array('type' => 2, 'crm_uid' => pylode(',', $uids)));
        }
    }
}

?>