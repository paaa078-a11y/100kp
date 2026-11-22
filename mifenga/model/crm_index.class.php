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

class crm_index_controller extends adminCommon
{

    function index_action()
    {

        $auid       =   intval($_SESSION['auid']);

        //会员套餐
        $ratingM    =   $this->MODEL('rating');
        $ratinglist =   $ratingM->getList(array('category' => '1', 'service_price' => array('>', '0')), array('field' => '`id`,`name`'));
        $serviceList=   $ratingM->getComServiceList(array('display' => 1), array('detail' => 'yes'));
        $this->yunset(array('ratinglist' => $ratinglist, 'serviceList' => $serviceList));

        $cacheM     =   $this->MODEL('cache');
        $cache      =   $cacheM->GetCache(array('crm'));
        $this->yunset('cache', $cache);

        $AdminM     =   $this->MODEL('admin');
        $where      =   array();
        $adminUser  =   $AdminM->getList($where, array('field' => '`uid`,`username`,`name`'));
        $this->yunset('adminUserList', $adminUser);
        $this->yunset('auid', $auid);

        // 网站所有支付类型
        include(CONFIG_PATH . 'db.data.php');
        $this->yunset('canpay', $arr_data['pay']);
        $this->yunset('orderTypeArr', $arr_data['ordertype']);

        $this->yunset(array('time' => date('Y-m'), 'month' => '（'.(int)date('m').'月）'));
        $this->yuntpl(array('admin/crm_index'));
    }

    function addDeal_action()
    {

        if (!empty($_POST)) {

            $crmM   =   $this->MODEL('crm');

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['auid'];
            $return         =   $crmM->addDeal($data, 1);
        } else {

            $return =   array('errcode' => 8, 'msg' => '操作错误，请重试！');
        }
        echo json_encode($return);
        die;
    }

    function searchcom_action()
    {

        if (isset($_POST['keyword'])) {
            
            $AdminM         =   $this->MODEL('admin');
            $adminUserInfo  =   $AdminM->getAdminUser(array('uid' => $_SESSION['auid']), array('field' => 'org,power,spower'));

            $crmM		=	$this->MODEL('crm');
            $companyM   =   $this->MODEL('company');
            $keyword    =   $this->post_trim($_POST['keyword']);
            
            $where['name']  =   array('like', $keyword);

            $uids[]         =   $_SESSION['auid'];

            if (isset($adminUserInfo) && $adminUserInfo['org'] > 0) {

                $orgInfo    =   $crmM->getOrgInfo(array('id' => $adminUserInfo['org']));
    
                $oIds = $orgIds = $orgIdss = $orgIdsss = array();
    
                if ($adminUserInfo['power'] == '1') {    // 同级部门权限
    
                    $orgIds[]   =   $orgInfo['id'];
                }
    
                if ($adminUserInfo['spower'] == '1') {    // 子部门权限
                    if ($orgInfo['level'] == '1') {
    
                        $orgList    =   $crmM->getOrgList(array('fid' => $adminUserInfo['org']));
                        foreach ($orgList as $ok => $ov) {
                            $orgIdss[]  =   $ov['id'];
                        }
    
                        $orgLists   =   $crmM->getOrgList(array('fid' => array('in', pylode(',', $orgIdss))));
    
                        foreach ($orgLists as $ook => $oov) {
                            $orgIdsss[] =   $oov['id'];
                        }
    
                    } elseif ($orgInfo['level'] == '2') {
    
                        $orgList    =   $crmM->getOrgList(array('fid' => $adminUserInfo['org']));
                        foreach ($orgList as $ok => $ov) {
    
                            $orgIdss[]  =   $ov['id'];
                        }
                    }
                }
    
                $oIds   =   array_merge($orgIds, $orgIdss, $orgIdsss);
    
                $adminUserList  =   $AdminM->getList(array('uid' => array('<>', $_SESSION['auid']), 'org' => array('in', pylode(',', $oIds))), array('field' => '`uid`,`name`,`username`'));
                foreach ($adminUserList as $v) {

                    $uids[]     =   $v['uid'];
                }
            }

            $where['crm_uid'][] =   array('in', pylode(',', $uids));


            $list       =   $companyM->getList($where, array('field' => '`uid`,`name`,`crm_uid`,`crm_status`,`crm_type`'));

            $com        =   $list['list'];

            if (is_array($com) && !empty($com)) {
                foreach ($com as $val) {

                    $data[] =   array('uid' => $val['uid'], 'name' => $val['name'],'crm_uid' => $val['crm_uid'],'crm_status' => $val['crm_status'],'crm_type' => $val['crm_type']);
                }
            }
        }
        echo json_encode($data);
        die;
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

    public function orderprice_action()
    {

        $id         =   intval($_POST['id']);
        $ratingM    =   $this->MODEL('rating');
        $rating     =   $ratingM->getInfo(array('id' => $id));
        echo json_encode($rating);
    }

    /**
     * @desc 工作台 - 客户数据
     */
    function getCustomer_action()
    {

        $crmM   =   $this->MODEL('crm');

        $return =   $crmM->getCustomerData($_SESSION['auid']);

        echo $return;
    }

    /**
     * @desc 工作台 - 消息提醒
     */
    function getNotice_action()
    {

        $crmM   =   $this->MODEL('crm');

        $return =   $crmM->getNotice($_SESSION['auid']);

        echo $return;
    }

    /**
     * @desc 工作台 - 我的简报
     */
    function getWorkReport_action()
    {

        $crmM   =   $this->MODEL('crm');

        if ($_POST['time'] == 1) {//今天

            $sDate  =   mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $eDate  =   mktime(23, 59, 59, date('m'), date('d'), date('Y'));
        } else if ($_POST['time'] == 2) {//昨天

            $sDate  =   mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 86400;
            $eDate  =   mktime(23, 59, 59, date('m'), date('d'), date('Y')) - 86400;
        } else if ($_POST['time'] == 3) {//本周

            $sDate  =   strtotime(date('Y-m-d', strtotime("this week Monday", time())));
            $eDate  =   strtotime(date('Y-m-d', strtotime("this week Sunday", time()))) + 24 * 3600 - 1;
        } else if ($_POST['time'] == 4) {//本月

            $sDate  =   mktime(0, 0, 0, date('m'), 1, date('Y'));
            $eDate  =   mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        }

        $return =   $crmM->getWorkReport(array('sdate' => $sDate, 'edate' => $eDate, 'auid' => $_SESSION['auid']));
        echo json_encode($return);
    }

    function log_action()
    {

        $where  =   $urlarr =   array();

        $crmM   =   $this->MODEL('crm');

        if ($_GET['logType']) {

            $where['type']      =   $_GET['logType'];
            $urlarr['logType']  =    $_GET['logType'];
        }

        if ($_GET['uid']) {

            $where['auid']      =   $_GET['uid'];
            $urlarr['uid']      =   $_GET['uid'];
        }

        if ($_GET['keyword']) {

            $where['content']   =   array('like', $_GET['keyword']);
            $urlarr['keyword']  =   $_GET['keyword'];
        }
		$urlarr         =   $_GET;
        $urlarr['page'] =   '{{page}}';
        $urlarr['c']    =   'log';

        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');
        $pageM          =   $this->MODEL('page');

        $pages          =   $pageM->pageList('crm_log', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            if (isset($_GET['order'])) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   'ctime,desc';
            }

            $where['limit'] =   $pages['limit'];

            $list           =   $crmM->getCrmLogList($where);

            $this->yunset(array('list' => $list));
        }

        $typeArr    =   array('1' => '用户录入', '2' => '客户信息', '3' => '跟进记录' , '4' => '订单数据', '5' => '工作任务', '6' => '工作日志', '7' => '转交客户', '8' => '放弃客户', '9' => '等级状态', '10' => '外出申请', '11' => '联系方式', '12' => '领取客户');
        $this->yunset('typeArr', $typeArr);

        $adminM     =   $this->MODEL('admin');
        $crmUser    =   $adminM->getList(array('is_crm' => '1'));
        $this->yunset('crmUser', $crmUser);
        $this->yuntpl(array('admin/crm_log'));
    }

    /**
     * @desc 删除操作记录
     */
    function delCrmLog_action()
    {

        if ($_POST['del']) {

            $delId  =   $_POST['del'];
        } else if ($_GET['id']) {

            $this->check_token();
            $delId  =   intval($_GET['id']);
        }

        $crmM   =   $this->MODEL('crm');
        $return =   $crmM->delCrmLog($delId);

        if ($return['errcode'] == '9') {

            $this->MODEL('log')->addAdminLog("删除CRM操作记录（ID：" . $delId . "）");
        }

        $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }

    function getKhb_action(){

        $crmM       =   $this->MODEL('crm');

        $crmKhb     =   $crmM->getKhbList($_POST['time']);
        echo json_encode($crmKhb);
    }

    function getJeb_action(){

        $crmM       =   $this->MODEL('crm');

        $crmJeb     =   $crmM->getJebList($_POST['time']);
        echo json_encode($crmJeb);
    }

    function job_action()
    {

        $this->yuntpl(array('admin/crm_job'));
    }

    function getJobData_action()
    {

        if ($_POST) {

            $cacheM = $this->MODEL('cache');
            $Cache = $cacheM->GetCache(array('com'));
            $className = $Cache['comclass_name'];

            $jobEdu = $jobExp = $jobTime = $jobReport = $jobWelfare = array();

            foreach ($Cache['comdata']['job_edu'] as $v) {

                $jobEdu[] = array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['comdata']['job_exp'] as $v) {

                $jobExp[] = array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['comdata']['job_report'] as $v) {

                $jobReport[] = array('id' => $v, 'name' => $className[$v]);
            }
            foreach ($Cache['comdata']['job_welfare'] as $v) {

                $jobWelfare[] = array('id' => $v, 'name' => $className[$v]);
            }

            $timeArr = array(1 => '今日', 2 => '最近三天', 3 => '最近七天', 4 => '最近一月');
            foreach ($timeArr as $k => $v) {
                $jobTime[] = array('id' => $k, 'name' => $v);
            }

            $data = array(

                'jobEdu' => $jobEdu,
                'jobExp' => $jobExp,
                'jobReport' => $jobReport,
                'jobWelfare' => $jobWelfare,
                'jobTime' => $jobTime
            );

            $return = array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($return);
            exit();
        }
    }

    function getJobList_action()
    {

        if ($_POST){

            $crmM       =   $this->MODEL('crm');

            $numWhere   =   array('status' => 0, 'state' => 1, 'r_status' => 1);

            $jobNum     =   $this->obj->select_num('company_job', $numWhere);

            $where      =   array('status' => 0, 'state' => 1, 'r_status' => 1);

            $page       =   $_POST['page'];
            $limit      =   10;

            if (isset($_POST['edu']) && !empty($_POST['edu'])) {

                $where['edu']   =   $_POST['edu'];
            }
            if (isset($_POST['exp']) && !empty($_POST['exp'])) {

                $where['exp']   =   $_POST['exp'];
            }
            if (isset($_POST['report']) && !empty($_POST['report'])) {

                $where['report']=   $_POST['report'];
            }
            if (isset($_POST['job_class']) && !empty($_POST['job_class'])) {

                $where['PHPYUNBTWSTART_A'] = '';
                $where['job1'] = array('findin', $_POST['job_class']);
                $where['job1_son'] = array('findin', $_POST['job_class'], 'OR');
                $where['job_post'] = array('findin', $_POST['job_class'], 'OR');
                $where['PHPYUNBTWEND_A'] = '';
            }
            if (isset($_POST['city_class']) && !empty($_POST['city_class'])) {

                $where['PHPYUNBTWSTART_B'] = '';
                $where['provinceid'] = array('findin', $_POST['city_class']);
                $where['cityid'] = array('findin', $_POST['city_class'], 'OR');
                $where['three_cityid'] = array('findin', $_POST['city_class'], 'OR');
                $where['PHPYUNBTWEND_B'] = '';

            }

            if (isset($_POST['welfare']) && !empty($_POST['welfare'])) {

                $welfare    =   explode('，', $_POST['welfare']);
                $where['PHPYUNBTWSTART_B_1'] = '';
                foreach ($welfare as $wk => $wv) {
                    $where['welfare'][] = array('like', $wv, '');
                }
                $where['PHPYUNBTWEND_B_1'] = '';
            }

            if (isset($_POST['time']) && !empty($_POST['time'])) {
                $timeStr = intval($_POST['time']);
                if ($timeStr == 1) {

                    $where['lastupdate'] = array('>', strtotime('today'));
                } else if ($timeStr == 2) {

                    $where['lastupdate'] = array('>', strtotime('-3 days'));
                } else if ($timeStr == 3) {

                    $where['lastupdate'] = array('>', strtotime('-7 days'));
                } else if ($timeStr == 4) {
                    $where['lastupdate'] = array('>', strtotime('-30 days'));
                }
            }

            if (isset($_POST['salary']) && !empty($_POST['salary'])) {
                $where['minsalary'] = array('>=', intval($_POST['salary']));
            }

            if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
                $where['PHPYUNBTWSTART_C'] = '';
                $where['com_name'] = array('like', trim($_POST['keyword']), '');
                $where['name'] = array('like', trim($_POST['keyword']), 'OR');
                $where['PHPYUNBTWEND_C'] = '';
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

            $jobList    =   $crmM->getJobList($where);

            $data       =   array(

                'jobList'   =>  $jobList,
                'start'     =>  $start,
                'end'       =>  $end,
                'jobNum'    =>  $jobNum,
                'jobTotal'  =>  $jobTotal,
                'pageTotal' =>  $pageTotal
            );

            $res        =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    function getAutoJobList_action()
    {

        if ($_POST) {

            $crmM   =   $this->MODEL('crm');
            $where  =   array('status' => 0, 'state' => 1, 'r_status' => 1);

            if (isset($_POST['edu']) && !empty($_POST['edu'])) {

                $where['edu']   =  $_POST['edu'];
            }
            if (isset($_POST['exp']) && !empty($_POST['exp'])) {

                $where['exp']   =   $_POST['exp'];
            }
            if (isset($_POST['report']) && !empty($_POST['report'])) {

                $where['report']   =   $_POST['report'];
            }
            if (isset($_POST['job_class']) && !empty($_POST['job_class'])) {

                $where['PHPYUNBTWSTART_A'] = '';
                $where['job1'] = array('findin', $_POST['job_class']);
                $where['job1_son'] = array('findin', $_POST['job_class'], 'OR');
                $where['job_post'] = array('findin', $_POST['job_class'], 'OR');
                $where['PHPYUNBTWEND_A'] = '';
            }
            if (isset($_POST['city_class']) && !empty($_POST['city_class'])) {

                $where['PHPYUNBTWSTART_B'] = '';
                $where['provinceid'] = array('findin', $_POST['city_class']);
                $where['cityid'] = array('findin', $_POST['city_class'], 'OR');
                $where['three_cityid'] = array('findin', $_POST['city_class'], 'OR');
                $where['PHPYUNBTWEND_B'] = '';

            }
            if (isset($_POST['time']) && !empty($_POST['time'])) {
                $timeStr = intval($_POST['time']);
                if ($timeStr == 1) {
                    $where['lastupdate'] = array('>', strtotime('today'));
                } else if ($timeStr == 2) {
                    $where['lastupdate'] = array('>', strtotime('-3 days'));
                } else if ($timeStr == 3) {
                    $where['lastupdate'] = array('>', strtotime('-7 days'));
                } else if ($timeStr == 4) {
                    $where['lastupdate'] = array('>', strtotime('-30 days'));
                }
            }

            if (isset($_POST['welfare']) && !empty($_POST['welfare'])) {

                $welfare    =   explode('，', $_POST['welfare']);
                $where['PHPYUNBTWSTART_B_1'] = '';
                foreach ($welfare as $wk => $wv) {
                    $where['welfare'][] =   array('like', $wv, '');
                }
                $where['PHPYUNBTWEND_B_1'] = '';
            }

            if (isset($_POST['salary']) && !empty($_POST['salary'])) {
                $where['minsalary'] = array('>=', intval($_POST['salary']));
            }

            if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
                $where['PHPYUNBTWSTART_C'] = '';
                $where['com_name'] = array('like', trim($_POST['keyword']), '');
                $where['name'] = array('like', trim($_POST['keyword']), 'OR');
                $where['PHPYUNBTWEND_C'] = '';
            }

            $where['orderby'] = array('lastupdate, desc');
            $where['limit'] = 100;

            $jobList=   $crmM->getJobList($where);
            $data   =   array('jobList' => $jobList);
            $res    =   array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    function setRecJob_action()
    {

        if (!empty($_POST)) {

            $crmM   =   $this->MODEL('crm');

            if ($_FILES['file']['tmp_name']) {
                $upArr = array(
                    'file' => $_FILES['file'],
                    'dir' => 'crm'
                );
                $uploadM = $this->MODEL('upload');
                if (!empty($upArr)) {
                    $pic = $uploadM->newUpload($upArr);
                    if (!empty($pic['picurl'])) {
                        $logo = checkpic($pic['picurl']);
                    }
                }
            }

            $data   =   array(

                'title' =>  $_POST['title'],
                'desc'  =>  str_replace(array("\r\n", "\r", "\n"), "", $_POST['desc']),
                'auid'  =>  $_SESSION['auid']
            );

            $data['jobid']  =   $_POST['jobids'];
            $data['auto']   =   $_POST['auto'];

            if ($_POST['auto'] == 1) {

                $data['interval']   =   $_POST['interval'];

                $searchData =   array();
                if (isset($_POST['search_job']) && !empty($_POST['search_job'])) {
                    $searchData['job']      =   $_POST['search_job'];
                }
                if (isset($_POST['search_city']) && !empty($_POST['search_city'])) {
                    $searchData['city']     =   $_POST['search_city'];
                }
                if (isset($_POST['search_keyword']) && !empty($_POST['search_keyword'])) {
                    $searchData['keyword']  =   $_POST['search_keyword'];
                }
                if (isset($_POST['search_edu']) && !empty($_POST['search_edu'])) {
                    $searchData['edu']      =   $_POST['search_edu'];
                }
                if (isset($_POST['search_exp']) && !empty($_POST['search_exp'])) {
                    $searchData['exp']      =   $_POST['search_exp'];
                }
                if (isset($_POST['search_report']) && !empty($_POST['search_report'])) {
                    $searchData['report']   =   $_POST['search_report'];
                }
                if (isset($_POST['search_time']) && !empty($_POST['search_time'])) {
                    $searchData['time']     =   $_POST['search_time'];
                }
                if (isset($_POST['search_welfare']) && !empty($_POST['search_welfare'])) {
                    $searchData['welfare']  =   $_POST['search_welfare'];
                }
                if (isset($_POST['search_salary']) && !empty($_POST['search_salary'])) {
                    $searchData['salary']   =   $_POST['search_salary'];
                }

                $data['search_info']    =   serialize($searchData);
            }

            if (isset($_POST['id']) && !empty($_POST['id'])) {
                if (isset($logo) && !empty($logo)) {
                    $data['logo']   =   $logo;
                }
                $return = $crmM->upRecJob($data, array('id' => $_POST['id']));
            } else {

                $data['logo']   =   !empty($logo) ? $logo : $this->config['sy_crmrecshare_logo'];
                $return = $crmM->addRecJob($data);
            }
            echo json_encode($return);
            die;
        }
    }


    function getShareCode_action()
    {

        if ($_GET['ids']) {

            include_once LIB_PATH.'shortLink.php';
            $str    =   b10to62($_GET['ids']);

            include_once LIB_PATH.'yunqrcode.class.php';

            if ($this->config['sy_seo_rewrite'] == 1) {

                $url = $this->config['sy_weburl'].'/u/'.$str;
            } else {

                $url = $this->config['sy_weburl'].'/index.php?m=upush&str='.$str;
            }
            YunQrcode::generatePng2($url, 4);
        }
    }


    function recJob_action()
    {

        $crmM   =   $this->MODEL('crm');


        $where['auid']  =   $_SESSION['auid'];

        if (isset($_GET['time']) && !empty($_GET['time'])) {

            $timeStr = intval($_GET['time']);

            if ($timeStr == 1) {

                $where['atime'] = array('>=', strtotime(date('Y-m-d')));
            } elseif ($timeStr == 2) {

                $where['PHPYUNBTWSTART_A'] = '';
                $where['atime'][] = array('>=', strtotime('yesterday'), '');
                $where['atime'][] = array('<', strtotime(date('Y-m-d')), '');
                $where['PHPYUNBTWEND_A'] = '';
            } elseif ($timeStr == 3) {

                $sDate = strtotime(date('Y-m-d', strtotime("this week Monday", time())));
                $eDate = strtotime(date('Y-m-d', strtotime("this week Sunday", time()))) + 24 * 3600 - 1;

                $where['PHPYUNBTWSTART_A'] = '';
                $where['atime'][] = array('>=', $sDate, '');
                $where['atime'][] = array('<', $eDate, '');
                $where['PHPYUNBTWEND_A'] = '';
            } elseif ($timeStr == 4) {

                $sDate = mktime(0, 0, 0, date('m'), 1, date('Y'));
                $eDate = mktime(23, 59, 59, date('m'), date('t'), date('Y'));

                $where['PHPYUNBTWSTART_A'] = '';
                $where['atime'][] = array('>=', $sDate, '');
                $where['atime'][] = array('<', $eDate, '');
                $where['PHPYUNBTWEND_A'] = '';
            }

            $urlarr['time'] = $timeStr;
        }

        $urlarr         =   $_GET;
        $urlarr['page'] =   '{{page}}';

        $pageurl    =   Url($_GET['m'], $urlarr, 'admin');
        $pageM      =   $this->MODEL('page');
        $pages      =   $pageM->pageList('crm_job', $where, $pageurl, $_GET['page']);
        if ($pages['total'] > 0) {
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   'atime,desc';
            }

            $where['limit']         =   $pages['limit'];
            $list                   =   $crmM->getRecJobLogList($where);
            $this->yunset(array('rows' => $list));
        }
        $this->yuntpl(array('admin/crm_rec_job'));
    }

    function getRecInfo_action()
    {

        if (!empty($_POST)) {

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->getRecJobInfo(array('id' => $_POST['id']));

            $data   =   array(

                'recInfo' => $return['info'],
                'recJobs' => $return['list']
            );

            $res = array('error' => 0, 'msg' => '', 'data' => $data);
            echo json_encode($res);
            exit();
        }
    }

    function delRecJob_action()
    {

        $del    = $_GET['del'];

        $where['id'] = $del;
        $return['layertype'] = 0;

        $crmM   =   $this->MODEL('crm');
        $return =   $crmM->delRecJobLog($where);

        if ($return['errcode'] == '9') {

            $this->MODEL('log')->addAdminLog("删除职位推广记录（ID：" . $del . "）");
        }

        $this->layer_msg($return['errmsg'], $return['errcode'], $return['layertype'], 'index.php?m=crm_index&c=recJob&time=4');
    }
}
?>