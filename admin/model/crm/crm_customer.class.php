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

class crm_customer_controller extends adminCommon
{

    function index_action()
    {

        $comM       =   $this->MODEL('company');
        $userInfoM  =   $this->MODEL('userinfo');

        $month 	    = 	get_this_month(time());
        $todayStart =   strtotime('today');

        $where['PHPYUNBTWSTART_A']  =   '';
        $where['crm_uid'][]         =   array('=', '', '');
        $where['crm_uid'][]         =   array('=', 0, 'OR');
        $where['crm_uid'][]         =   array('isnull', '', 'OR');
        $where['PHPYUNBTWEND_A']    =   '';

        if (isset($_POST['jttype']) && !empty($_POST['jttype'])) {

            // 部分可以直接查询，不需要走工作台参数处理方法
            $jttype =   $_POST['jttype'];

            if ($jttype == 'new') { // 当天新注册的企业，前端进行了日期条件拼接

            } elseif ($jttype == 'jqsf') { // 一周内释放的用户

                $where['release_time'] = array('>=', strtotime('-6 day today')); // 包含今天
            } else {

                $where['uid'] = array('in', pylode(',', $this->jobtaiHandle($jttype)));
            }
        }

        if (isset($_POST['rating']) && !empty($_POST['rating'])) {

            $rating                 =   intval($_POST['rating']);
            if($_POST['rating'] > 0){

                $where['rating']        =   $rating;
                $where['PHPYUNBTWSTART_B']  =   '';
                $where['vipetime'][]        =   array('=', 0, '');
                $where['vipetime'][]        =   array('>', strtotime('today'), 'OR');
                $where['PHPYUNBTWEND_B']    =   '';
            }else{

                $where['PHPYUNBTWSTART_B']  =   '';
                $where['vipetime']          =   array(
                    0   =>  array('<', strtotime('today'), ''),
                    1   =>  array('>', 0, '')
                );
                $where['PHPYUNBTWEND_B']    =   '';
            }
        }

        if (isset($_POST['vipetime']) && !empty($_POST['vipetime'])) {

            $etime  =   intval($_POST['vipetime']);

            if ($etime == 5) {

                $where['PHPYUNBTWSTART_C'] = '';
                $where['vipetime'][] = array('<', strtotime(date('Y-m-d')), '');
                $where['vipetime'][] = array('>', 0, 'AND');
                $where['PHPYUNBTWEND_C'] = '';
            } else {

                if ($etime == 1) {
                    $num = '+3 days';
                } else if ($etime == 2) {
                    $num = '+7 days';
                } elseif ($etime == 3) {
                    $num = '+15 days';
                } elseif ($etime == 4) {
                    $num = '+1 month';
                }
                $where['PHPYUNBTWSTART_C'] = '';
                $where['vipetime'][] = array('>=', strtotime('today'), '');
                $where['vipetime'][] = array('<', strtotime($num, strtotime('tomorrow')), 'AND');
                $where['PHPYUNBTWEND_C'] = '';
            }
        }

        if (isset($_POST['crmType']) && !empty($_POST['crmType'])) {

            $where['crm_type']  =   intval($_POST['crmType']);
        }

        if (isset($_POST['crm_status']) && !empty($_POST['crm_status'])) {

            $where['crm_status']    =   intval($_POST['crm_status']);
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
                $certList   =   $comM->getCertList($certWhere, array('field' => '`uid`'));
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
                if (isset($_POST['loginTime']) && !empty($_POST['loginTime'])){

                    $loginTimeArr   =   explode('至', $_POST['loginTime']);
                    $loginStart     =   $loginTimeArr[0];
                    $loginEnd       =   $loginTimeArr[1];
                    $where['PHPYUNBTWSTART_D'] = '';
                    $where['login_date'][] = array('>=', strtotime($loginStart.' 00:00:00'));
                    $where['login_date'][] = array('<=', strtotime($loginEnd.' 23:59:59'));
                    $where['PHPYUNBTWEND_D'] = '';

                }
            }
        }

        if (isset($_POST['regtime_s']) && !empty($_POST['regtime_e'])) {
            $regStart   =   strtotime($_POST['regtime_s'].' 00:00:00');
            $regEnd     =   strtotime($_POST['regtime_e'].' 23:59:59');

            $mWhere['PHPYUNBTWSTART_E'] =   '';
            $mWhere['reg_date'][]       =   array('>', $regStart, 'AND');
            $mWhere['reg_date'][]       =   array('<', $regEnd, 'AND');
            $mWhere['PHPYUNBTWEND_E']   =   '';
            $memberList =   $userInfoM->getList($mWhere, array('field' => 'uid'));

            $uidArrB    =   array();
            foreach ($memberList as $mv) {
                $uidArrB[]  =   $mv['uid'];
            }

            $where['uid']       =   array('in', pylode(',', $uidArrB));
        }

        if (isset($_POST['lastFtime']) && !empty($_POST['lastFtime'])) {

            $lastFtime = intval($_POST['lastFtime']);
            if ($lastFtime == 1) {

                $where['PHPYUNBTWSTART_F'] = '';
                $where['isfollow'] = array('=', '0', 'OR');
                $where['f_time'] = array('=', '', 'OR');
                $where['PHPYUNBTWEND_F'] = '';
            } elseif ($lastFtime == 2) {

                $where['f_time'] = array('>', strtotime(date('Y-m-d')));
            } elseif ($lastFtime == 3) {

                $where['PHPYUNBTWSTART_F'] = '';
                $where['f_time'][] = array('>', 0);
                $where['f_time'][] = array('<', strtotime('-3 days'));
                $where['PHPYUNBTWEND_F'] = '';
            } elseif ($lastFtime == 4) {

                $where['PHPYUNBTWSTART_F'] = '';
                $where['f_time'][] = array('>', 0);
                $where['f_time'][] = array('<', strtotime('-7 days'));
                $where['PHPYUNBTWEND_F'] = '';
            } elseif ($lastFtime == 5) {

                $where['PHPYUNBTWSTART_F'] = '';
                $where['f_time'][] = array('>', 0);
                $where['f_time'][] = array('<', strtotime('-30 days'));
                $where['PHPYUNBTWEND_F'] = '';
            } elseif ($lastFtime == 6) {

                if (!empty($_POST['lastdate_s']) && !empty($_POST['lastdate_e'])) {

                    $lastStart = $_POST['lastdate_s'];
                    $lastEnd = $_POST['lastdate_e'];
                    $where['PHPYUNBTWSTART_F'] = '';
                    $where['f_time'][] = array('>=', strtotime($lastStart . ' 00:00:00'));
                    $where['f_time'][] = array('<=', strtotime($lastEnd . ' 23:59:59'));
                    $where['PHPYUNBTWEND_F'] = '';

                }
            }
        }

        if ((isset($_POST['releaseStart'])&& !empty($_POST['releaseStart'])) || (isset($_POST['releaseEnd']) && !empty($_POST['releaseEnd']))) {

            if (!empty($_POST['releaseStart']) && !empty($_POST['releaseEnd'])) {

                $where['PHPYUNBTWSTART_H']  =   '';
                $where['release_time'][]    =   array('>=', strtotime($_POST['releaseStart']), '');
                $where['release_time'][]    =   array('<', strtotime($_POST['releaseEnd']) + 86400, '');
                $where['PHPYUNBTWEND_H']    =   '';
            } else if (!empty($_POST['releaseStart']) && !empty($_POST['releaseEnd'])) {

                $where['release_time']      =   array('>=', strtotime($_POST['releaseStart']));
            } else if (!empty($_POST['releaseStart']) && !empty($_POST['releaseEnd'])) {

                $where['release_time']      =   array('<', strtotime($_POST['releaseEnd']) + 86400);
            }

            unset($_POST['releaseTime']);
        }

        if (isset($_POST['releaseTime']) && !empty($_POST['releaseTime'])) {

            $releaseTime                =   intval($_POST['releaseTime']);
            $where['PHPYUNBTWSTART_G']  =   '';
            $where['release_time'][]    =   array('>', 0, '');

            if ($releaseTime == 1) {

                $where['release_time'][]=   array('>=', strtotime(date('Y-m-d')));
            } elseif ($releaseTime == 2) {

                $where['release_time'][]=   array('>=', strtotime('yesterday'));
                $where['release_time'][]=   array('<', strtotime(date('Y-m-d')));
            } elseif ($releaseTime == 3) {

                $where['release_time'][]=   array('>=', strtotime('-3 days'));
                $where['release_time'][]=   array('<', time());
            } elseif ($releaseTime == 4) {

                $where['release_time'][]=   array('>=', strtotime('-7 days'));
                $where['release_time'][]=   array('<', time());
            } elseif ($releaseTime == 5) {

                $where['release_time'][]=   array('>=', strtotime('-30 days'));
                $where['release_time'][]=   array('<', time());
            } else if ($releaseTime == 6){

                if (!empty($_POST['releasedate_s']) && !empty($_POST['releasedate_s'])){
                    $releaseDateStart   =   $_POST['releasedate_s'];
                    $releaseDateEnd     =   $_POST['releasedate_s'];

                    $where['release_time'][]=   array('>=', strtotime($releaseDateStart.' 00:00:00'));
                    $where['release_time'][]=   array('<', strtotime($releaseDateEnd.' 23:59:59'));
                }
            }
            $where['PHPYUNBTWEND_G']    =   '';

        }

        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {

            $keywordStr =   trim($_POST['keyword']);
            $typeStr    =   intval($_POST['ktype']);

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

        if (isset($_POST['r_status']) && !empty($_POST['r_status'])) {
            $where['r_status']  =   $_POST['r_status'] != 1 ? $_POST['r_status'] : 0;
        }

        if (isset($_POST['clue']) && !empty($_POST['clue'])) {

            $where['clue']  =   $_POST['clue'];
        }
        if($_POST['fact_status']){
            if($_POST['fact_status'] == '1'){
                $where['fact_status'] = 1;
            }elseif($_POST['fact_status'] == '2'){
                $where['fact_status'] = 0;
            }
        }
        $perPage = !empty($_POST['limit']) ? intval($_POST['limit']) : intval($this->config['sy_listnum']);
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company', $where, $_POST['page'], array('limit' => $perPage));

        if ($pages['total'] > 0) {
            if (isset($_POST['order'])) {
                if (isset($_POST['t'])) {
                    $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
                } else {
                    $where['orderby']   =   'login_date,desc';
                }
            } else {
                $where['orderby']       =   'login_date,desc';
            }
            $where['limit'] =   $pages['limit'];
            $listA  =   $comM->getList($where, array('utype' => 'crm'));
            $list   =   !empty($listA['list']) ? $listA['list'] : array();
            foreach ($list as $k => $v) {

                $list[$k]['wxBindmsg']  =   $this->wxBindState($v);
                $list[$k]['isvip']      =   $todayStart < $v['vipetime'] || ($v['vipetime'] == '0' && $v['rating'] > '0') ? 1 : 0;
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
            }
        }

        $data['list']   =   !empty($list) ? $list : array();
        $data['total']  =   $pages['total'];
        $data['page_sizes'] =   $pages['page_sizes'];
        $data['page_size']  =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }

    function getStaticData_action(){
        $crmM   =   $this->MODEL('crm');
        $cacheM     =   $this->MODEL('cache');
        $cache      =   $cacheM->GetCache(array('crm'));

        $crmdata        = $cache['crmdata'];
        $crmclass_name  = $cache['crmclass_name'];
        foreach ($crmdata as $key => $value) {
            $selectArr = array();
            foreach ($value as $k => $v) {
                $selectArr[] = array('val'=>$v,'name'=>$crmclass_name[$v]);
            }

            $data[$key] = $selectArr;
        }

        $ratingM    =   $this->MODEL('rating');
        if (!empty($this->config['com_rating'])) {
            $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`', 'rating' => $this->config['com_rating']));
        } else {
            $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
        }

        $vipEtime   =   array('5' => '已到期', '1' => '三天内', '2' => '七天内', '3' => '十五天内', '4' => '一月内');
        $yyzz       =   array('1' => '认证通过', '2' => '等待审核', '3' => '认证失败', '4' => '尚未认证');
        $releaseTime=   array('1' => '今天', '2' => '昨天', '3' => '三天内', '4' => '七天内', '5' => '一月内', '6' => '自定义');
        $lastFtime  =   array('1' => '从未跟进', '2' => '今天跟进', '3' => '三天前', '4' => '七天前', '5' => '一月前', '6' => '自定义');
        $loginDate  =   array('1' => '今天', '2' => '本周', '3' => '本月', '4' => '一个月前', '5' => '三个月前', '6' => '半年前', '7' => '自定义');
        
        $defnumarr  =   array('companyAllNum'=>0,'companyStatusNum1'=>0,'companyStatusNum2'=>0,'companyStatusNum3'=>0,'companyStatusNum4'=>0);
        
        $numarr     =   $crmM->getCustomerNum(array('type' => 3));

        $data['yyzz_statusarr'] =   $yyzz;
        $data['ratingArr']      =   $ratingArr;
        $data['releaseTime_arr']=   $releaseTime;
        $data['vip_etimearr']   =   $vipEtime;
        $data['last_ftimearr']  =   $lastFtime;
        $data['login_datearr']  =   $loginDate;
        $data['numarr']         =   array_merge($defnumarr,$numarr);
        $data['sy_crm_link']    =   $this->config['sy_crm_link'];
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
     * @desc  认领客户
     */
    function claimCom_action()
    {

        if ($_POST['uids']) {

            $uids   =   @explode(',', $_POST['uids']);
            $auid   =   intval($_SESSION['auid']);
            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->claimCom($uids, $auid);
            
            $error  =   $return['errcode']=='9'?0:1;
            $msg    =   $return['msg'];
        }else{
            $error  =   1;
            $msg    =   '请选择要领取的客户';
        }

        $this->render_json($error,$msg);
    }

    function clueCustomer_action()
    {

        if ($_POST['uids']) {

            $uids   =   @explode(',', $_POST['uids']);
            $auid   =   intval($_SESSION['auid']);
            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->clueCustomer($uids, $auid);
            $error  =   $return['errcode']=='9'?0:1;
            $msg    =   $return['msg'];
        }else{
            $error  =   1;
            $msg    =   '请选择要领取的客户';
        }

        $this->render_json($error,$msg);
    }

    /**
     * 工作台跳转我的客户，参数处理
     * 此方法同 crm_index.class.php 中 ghCustomer 方法同步修改查询条件
     * @param $type 处理的数据类型
     */
    private function jobtaiHandle($type)
    {
        $today = strtotime('today');
        $week = strtotime('-1 week +1 day', $today); // 取一周时间 (包含当天)
        $crm_deal_rating = !empty($this->config['crm_deal_rating']) ? explode(',', $this->config['crm_deal_rating']) : array('-9'); // CRM设置的成交会员  默认值-9，避免SQL报错的

        $companyM = $this->MODEL('company');

        // 公用公海查询条件和公海列表保持一致
        $where = array(
            'PHPYUNBTWSTART_A' => '',
            'crm_uid' => array(
                array('=', '', ''),
                array('=', 0, 'OR'),
                array('isnull', '', 'OR'),
            ),
            'PHPYUNBTWEND_A' => '',
        );

        $uidArr = [-1];

        switch ($type) {
            case 'gyx':
                // 高意向用户：非成交会员，触发权益提醒 OR 7天内有登录，历史成交过，但未续费的客户
                $gyx_cycle = $this->config['crm_jobtai_gyx_cycle'] ? intval($this->config['crm_jobtai_gyx_cycle']) : 3; // 获取工作台数据周期 - 高意向
                $gyx_cycle = $gyx_cycle - 1;
                $startTimeGyx = strtotime("-{$gyx_cycle} day", $today); // 高意向
                $gyxUidArr = array();
                $companyGyxNdList = $companyM->getChCompanyList(array_merge($where, array(
                    'rating' => array('notin', pylode(',', $crm_deal_rating)),
                    'PHPYUNBTWSTART_B' => '',
                    'vipetime' => array(array('=', 0, ''), array('>=', $today, 'or')),
                    'PHPYUNBTWEND_B' => '',
                )), array('field' => 'uid,login_date,rating,vipetime')); // 非成交会员
                if ($companyGyxNdList) {
                    $gyxList = $this->obj->select_all('rating_notice', array(
                        'groupby' => 'uid',
                        'uid' => array('in', pylode(',', array_column($companyGyxNdList, 'uid'))),
                        'send_time' => array('>=', $startTimeGyx)
                    ), 'uid'); // 触发权益提醒
                    $gyxUidArr = $gyxList ? array_column($gyxList, 'uid') : array();
                }
                // 7天内有登录，历史成交过，但未续费的客户
                $gyxDqSql = "SELECT count(o.uid) num FROM {$this->def}company a JOIN {$this->def}company_order o ON a.uid = o.uid WHERE (a.`crm_uid` = '' OR a.`crm_uid` = '0' OR a.`crm_uid` is null) AND a.login_date >= {$week} AND a.vipetime > 0 AND a.vipetime < {$today} AND o.type = 1 AND o.order_state = 2 AND o.rating in (" . pylode(',', $crm_deal_rating) . ") GROUP BY o.uid";
                $gyxOrderList = $this->obj->DB_query_all($gyxDqSql, 'all');
                $gyxUidArr = $gyxOrderList ? array_unique(array_merge($gyxUidArr, array_column($gyxOrderList, 'uid'))) : $gyxUidArr;
                $gyxUidArr && $uidArr = $gyxUidArr;
                break;
            case 'qz':
                // 潜在用户
                $month = strtotime('-1 month +1 day', $today); // 取一个月前的时间 (包含当天)
                $companyListQz = $this->obj->DB_select_all('company', "(`crm_uid` = '' OR `crm_uid` = '0' OR `crm_uid` is null) AND login_date < {$month} AND (((rating = 0 OR rating = {$this->config['com_rating']}) AND vipetime = 0) OR (rating in (" . pylode(',', $crm_deal_rating) . ") OR vipetime > 0 AND vipetime < {$today}))", 'uid');
                if ($companyListQz) {
                    $companyUidQzArr = array_column($companyListQz, 'uid');
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
                // 活跃用户 一周内登录过的（近期登录），未开通会员或会员已过期
                $hyList = $this->obj->DB_select_all('company', "(`crm_uid` = '' OR `crm_uid` = '0' OR `crm_uid` is null) AND login_date >= {$week} AND (((rating = 0 OR rating = {$this->config['com_rating']}) AND vipetime = 0) OR (rating in (" . pylode(',', $crm_deal_rating) . ") OR vipetime > 0 AND vipetime < {$today}))", 'uid');
                $hyList && $uidArr = array_column($hyList, 'uid');
                break;
            case 'dq':
                if (!empty($this->config['crm_deal_rating'])) {
                    $dqSql = "SELECT o.uid,count(o.uid) num FROM {$this->def}company a JOIN {$this->def}company_order o ON a.uid = o.uid WHERE (a.`crm_uid` = '' OR a.`crm_uid` = '0' OR a.`crm_uid` is null) AND a.vipetime > 0 AND a.vipetime < {$today} AND o.type = 1 AND o.order_state = 2 AND o.rating in (" . pylode(',', $crm_deal_rating) . ") GROUP BY o.uid";
                    $orderList = $this->obj->DB_query_all($dqSql, 'all');
                    $orderList && $uidArr = array_column($orderList, 'uid');
                }
                break;
        }

        return $uidArr;
    }
}

?>
