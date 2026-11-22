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

    function set_search()
    {

        $orders     =   array('1' => '客户 UID', '2' => '登录时间', '3' => '到期时间', '4' => '跟进时间');
        $ratingM    =   $this->MODEL('rating');
        if (!empty($this->config['com_rating'])) {
            $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`', 'rating' => $this->config['com_rating']));
        } else {
            $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
        }
        $vipEtime   =   array('5' => '已到期', '1' => '三天内', '2' => '七天内', '3' => '十五天内', '4' => '一月内');

        $cacheM     =   $this->MODEL('cache');
        $crmCache   =   $cacheM->GetCache(array('crm'));
        $this->yunset(array('cache' => $crmCache, 'crmClassName' => $crmCache['crmclass_name'], 'crmStatus' => $crmCache['crmdata']['client_status'], 'crmType' => $crmCache['crmdata']['client_type']));

        $yyzz       =   array('1' => '认证通过', '2' => '等待审核', '3' => '认证失败', '4' => '尚未认证');
        $releaseTime=   array('1' => '今天', '2' => '昨天', '3' => '三天内', '4' => '七天内', '5' => '一月内');
        $lastFtime  =   array('1' => '从未跟进', '2' => '今天跟进', '3' => '三天前', '4' => '七天前', '5' => '一月前', '6' => '百日前');

        $this->yunset(array('orders' => $orders, 'ratingArr' => $ratingArr, 'vipEtime' => $vipEtime, 'yyzzStatus' => $yyzz, 'releaseTime' => $releaseTime, 'lastFtime' => $lastFtime));
        $this->yunset('todayStart', strtotime('today'));
    }

    function index_action()
    {

        $this->set_search();

        $comM       =   $this->MODEL('company');
        $userInfoM  =   $this->MODEL('userinfo');

        $urlarr     =   $_GET;

        $where['PHPYUNBTWSTART_A']  =   '';
        $where['crm_uid'][]         =   array('=', '', '');
        $where['crm_uid'][]         =   array('=', 0, 'OR');
        $where['crm_uid'][]         =   array('isnull', '', 'OR');
        $where['PHPYUNBTWEND_A']    =   '';

        if (isset($_GET['rating']) && !empty($_GET['rating'])) {

            $rating                 =   intval($_GET['rating']);
            $where['rating']        =   $rating;
            $urlarr['rating']       =   $rating;
        }

        if (isset($_GET['vipetime']) && !empty($_GET['vipetime'])) {

            $etime  =   intval($_GET['vipetime']);

            if ($etime == 5) {

                $where['PHPYUNBTWSTART_B']  =   '';
                $where['vipetime'][]        =   array('<', strtotime(date('Y-m-d')), '');
                $where['vipetime'][]        =   array('>', 0, 'AND');
                $where['PHPYUNBTWEND_B']    =   '';
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

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['vipetime'][]        =   array('>', strtotime(date('Y-m-d')), '');
                $where['vipetime'][]        =   array('<', strtotime($num), 'AND');
                $where['PHPYUNBTWEND_A']    =   '';
            }

            $urlarr['vipetime'] =   $etime;
        }

        if (isset($_GET['crmType']) && !empty($_GET['crmType'])) {

            $crmType                =   intval($_GET['crmType']);
            $where['crm_type']      =   $crmType;
            $urlarr['crmType']      =   $crmType;
        }

        if (isset($_GET['crm_status']) && !empty($_GET['crm_status'])) {

            $crm_status             =   intval($_GET['crm_status']);
            $where['crm_status']    =   $crm_status;
            $urlarr['crm_status']   =   $crm_status;
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

            $urlarr['yyzz_status']  =   $yyzz_status;
        }

        if (!empty($_GET['loginStart']) && !empty($_GET['loginEnd'])) {

            $loginStart =   strtotime($_GET['loginStart']);
            $loginEnd   =   strtotime($_GET['loginEnd']) + 86400;

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['login_date'][]      =   array('>', $loginStart, 'AND');
            $where['login_date'][]      =   array('<', $loginEnd, 'AND');
            $where['PHPYUNBTWEND_A']    =   '';

            $urlarr['loginStart']   =   $_GET['loginStart'];
            $urlarr['loginEnd']     =   $_GET['loginEnd'];
        } else if (!empty($_GET['loginStart']) && empty($_GET['loginEnd'])) {

            $loginStart             =   strtotime($_GET['loginStart']);
            $where['login_date']    =   array('>', $loginStart);
            $urlarr['loginStart']   =   $_GET['loginStart'];
        } else if (empty($_GET['loginStart']) && !empty($_GET['loginEnd'])) {

            $loginEnd               =   strtotime($_GET['loginEnd']) + 86400;
            $where['login_date']    =   array('<', $loginEnd, 'AND');
            $urlarr['loginEnd']     =   $_GET['loginEnd'];
        }

        if (!empty($_GET['regStart']) && !empty($_GET['regEnd'])) {

            $regStart   =   strtotime($_GET['regStart']);
            $regEnd     =   strtotime($_GET['regEnd']) + 86400;

            $mWhere['PHPYUNBTWSTART_A'] =   '';
            $mWhere['reg_date'][]       =   array('>', $regStart, 'AND');
            $mWhere['reg_date'][]       =   array('<', $regEnd, 'AND');
            $mWhere['PHPYUNBTWEND_A']   =   '';
            $memberList                 =   $userInfoM->getList($mWhere, array('field' => 'uid'));

            $uidArrB    =   array();
            foreach ($memberList as $mv) {
                $uidArrB[]  =   $mv['uid'];
            }

            $where['uid']   =   array('in', pylode(',', $uidArrB));

            $urlarr['regStart'] =   $_GET['regStart'];
            $urlarr['regEnd']   =   $_GET['regEnd'];
        } else if (!empty($_GET['regStart']) && empty($_GET['regEnd'])) {

            $regStart           =   strtotime($_GET['regStart']);
            $mWhere['reg_date'] =   array('>', $regStart);
            $memberList         =   $userInfoM->getList($mWhere, array('field' => 'uid'));
            $uidArrB            =   array();
            foreach ($memberList as $mv) {
                $uidArrB[]      =   $mv['uid'];
            }
            $where['uid']       =   array('in', pylode(',', $uidArrB));
            $urlarr['regStart'] =   $_GET['regStart'];
        } else if (empty($_GET['regStart']) && !empty($_GET['regEnd'])) {

            $regEnd             =   strtotime($_GET['regEnd']) + 86400;
            $mWhere['reg_date'] =   array('<', $regEnd);
            $memberList         =   $userInfoM->getList($mWhere, array('field' => 'uid'));
            $uidArrB            =   array();
            foreach ($memberList as $mv) {
                $uidArrB[]      =   $mv['uid'];
            }
            $where['uid']       =   array('in', pylode(',', $uidArrB));
            $urlarr['regEnd']   =   $_GET['regEnd'];
        }

        if (isset($_GET['lastStart']) || isset($_GET['lastEnd'])) {

            if (!empty($_GET['lastStart']) && !empty($_GET['lastEnd'])) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['f_time'][]          =   array('>=', strtotime($_GET['lastStart']), '');
                $where['f_time'][]          =   array('<', strtotime($_GET['lastEnd']) + 86400, '');
                $where['PHPYUNBTWEND_A']    =   '';
            } else if (!empty($_GET['lastStart']) && empty($_GET['lastEnd'])) {

                $where['f_time']            =   array('>=', strtotime($_GET['lastStart']));
            } else if (empty($_GET['lastStart']) && !empty($_GET['lastEnd'])) {

                $where['f_time'][]          =   array('<', strtotime($_GET['lastEnd']) + 86400);
            }

            $urlarr['lastStart']    =   $_GET['lastStart'];
            $urlarr['lastEnd']      =   $_GET['lastEnd'];
            unset($_GET['lastFtime']);
            $urlarr['lastFtime']    =   '';
        }

        if (isset($_GET['lastFtime']) && !empty($_GET['lastFtime'])) {

            $lastFtime              =   intval($_GET['lastFtime']);
            if ($lastFtime == 1) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['isfollow']          =   array('=', '0', 'OR');
                $where['f_time']            =   array('=', '', 'OR');
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif ($lastFtime == 2) {

                $where['f_time']            =   array('>', strtotime(date('Y-m-d')));
            } elseif ($lastFtime == 3) {

                $where['PHPYUNBTWSTART_A']  = '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-3 days'));
                $where['PHPYUNBTWEND_A'] = '';
            } elseif ($lastFtime == 4) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-7 days'));
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif ($lastFtime == 5) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-30 days'));
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif ($lastFtime == 6) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['f_time'][]          =   array('>', 0);
                $where['f_time'][]          =   array('<', strtotime('-100 days'));
                $where['PHPYUNBTWEND_A']    =   '';
            }
            $urlarr['lastFtime']    =   $lastFtime;
        }

        if (isset($_GET['releaseStart']) || isset($_GET['releaseEnd'])) {

            if (!empty($_GET['releaseStart']) && !empty($_GET['releaseEnd'])) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['release_time'][]    =   array('>=', strtotime($_GET['releaseStart']), '');
                $where['release_time'][]    =   array('<', strtotime($_GET['releaseEnd']) + 86400, '');
                $where['PHPYUNBTWEND_A']    =   '';
            } else if (!empty($_GET['releaseStart']) && !empty($_GET['releaseEnd'])) {

                $where['release_time']      =   array('>=', strtotime($_GET['releaseStart']));
            } else if (!empty($_GET['releaseStart']) && !empty($_GET['releaseEnd'])) {

                $where['release_time']      =   array('<', strtotime($_GET['releaseEnd']) + 86400);
            }

            $urlarr['releaseStart'] =   $_GET['releaseStart'];
            $urlarr['releaseEnd']   =   $_GET['releaseEnd'];
            unset($_GET['releaseTime']);
            $urlarr['releaseTime']  =   '';
        }

        if (isset($_GET['releaseTime']) && !empty($_GET['releaseTime'])) {

            $releaseTime                =   intval($_GET['releaseTime']);
            $where['PHPYUNBTWSTART_A']  =   '';
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
            }
            $where['PHPYUNBTWEND_A']    =   '';

            $urlarr['releaseTime']      =   $releaseTime;
        }

        if (isset($_GET['keyword']) && $_GET['keyword']) {

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
            $urlarr['keyword'] = $keywordStr;
        }

        if (isset($_GET['r_status'])) {
            $where['r_status']  =   $_GET['r_status'];
        }

        if(isset($_GET['r_status'])){
            $where['r_status']       =   $_GET['r_status'];
        }

        $urlarr['page'] =   '{{page}}';
        $pageurl    =   Url($_GET['m'], $urlarr, 'admin');

        $pageM      =   $this->MODEL('page');
        $pages      =   $pageM->pageList('company', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            if (isset($_GET['order'])) {
                if (isset($_GET['t'])) {
                    $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                    $urlarr['t']        =   $_GET['t'];
                } else {
                    $where['orderby']   =   'login_date,desc';
                }
                $urlarr['order']        =   $_GET['order'];
            }else{
                $where['orderby']       =   'login_date,desc';
            }

            $where['limit'] =   $pages['limit'];
            $listA = $comM->getList($where, array('utype' => 'crm'));

            foreach ($listA['list'] as $key => $val) {
                $listA['list'][$key]['wxBindmsg'] = $this->wxBindState($val);
            }

            $this->yunset(array('rows' => $listA['list'], 'auid' => intval($_SESSION['auid'])));
        }

        $adminM         =   $this->MODEL('admin');

        $adminUserList  =   $adminM->getList(array(), array('field' => '`uid`,`name`'));
        $this->yunset('adminUserList', $adminUserList);

        $crmUser        =   $adminM->getList(array('is_crm' => '1', 'uid' => array('<>', $_SESSION['auid'])));
        $this->yunset(array('crmUser' => $crmUser));

        $this->yuntpl(array('admin/crm_customer_all'));
    }

    function getComNum_action()
    {

        $crmM   =   $this->MODEL('crm');

        echo $crmM->getPubCustomerNum();
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
            echo json_encode($return);
        }
    }
}

?>
