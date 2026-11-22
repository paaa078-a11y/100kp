<?php
/*
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
        $data = array();
        $auid       =   intval($_SESSION['auid']);

        //会员套餐
        $ratingM    =   $this->MODEL('rating');
        $ratinglist =   $ratingM->getList(array('category' => '1', 'service_price' => array('>', '0')), array('field' => '`id`,`name`'));
        $serviceList=   $ratingM->getComServiceList(array('display' => 1), array('detail' => 'yes'));

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


        $AdminM     =   $this->MODEL('admin');
        $where      =   array();
        $adminUser  =   $AdminM->getList($where, array('field' => '`uid`,`username`,`name`'));


        //网站所有支付类型
        include(CONFIG_PATH . 'db.data.php');

        $data['auid']           = $auid;
        $data['adminUserList']  = $adminUser;
        $data['ratinglist']     = $ratinglist;
        $data['serviceList']    = $serviceList;
        $data['canpay']         = $arr_data['pay'];
        $data['orderTypeArr']   = $arr_data['ordertype'];

        $this->render_json(0,'',$data);
    }
    function searchcom_action()
    {

        $keywordStr =   trim($_POST['keyword']);
        $data       =   array();
        if (isset($keywordStr) && !empty($keywordStr)){

            $companyM   =   $this->MODEL('company');
            $keyword    =   $this->post_trim($_POST['keyword']);
            $where      =   array('name' => array('like', $keyword));

            $adminUserList  =   $this->getAdminUser();

            if (!empty($adminUserList)){

                $uidArr     =   array();
                foreach ($adminUserList as $v) {

                    $uidArr[]       =   $v['uid'];
                }
                $uidArr[]           =   $_SESSION['auid'];
                $where['crm_uid']   =   array('in', pylode(',', $uidArr));
            }else{

                $where['crm_uid']   =   $_SESSION['auid'];
            }

            $list   =   $companyM->getList($where, array('field' => '`uid`,`name`,`crm_uid`,`crm_status`,`crm_type`'));
            $com    =   $list['list'];
            if (!empty($com) && is_array($com)) {
                foreach ($com as $val) {
                    $data[] =   array('uid' => $val['uid'], 'name' => $val['name'], 'crm_uid' => $val['crm_uid'], 'crm_status' => $val['crm_status'], 'crm_type' => $val['crm_type']);
                }
            }
        }
        $this->render_json(0,'',$data);
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
        $this->render_json(0,'',$crmLink);

    }
    function addDeal_action()
    {

        if (!empty($_POST)) {

            $crmM   =   $this->MODEL('crm');

            $data           =   $_POST;


            if($_FILES['picFiles']['tmp_name']!=''){

                $pic_file = array();

                foreach ($_FILES['picFiles'] as $nk => $nv) {
                    foreach ($nv as $nkk => $nvv) {
                        $pic_file[$nkk][$nk] = $nvv;
                    }
                }

                $uploadM = $this->MODEL('upload');

                foreach ($pic_file as $pk => $pv) {

                    $upArr  =   array(
                        'file'   =>  $pv,
                        'dir'    =>  'order_ht',
                    );

                    $pic =   $uploadM->newUpload($upArr);

                    if (!empty($pic['msg'])){
                        $this->render_json(8,$pic['msg']);
                    }elseif (!empty($pic['picurl'])){
                        $data['htpics'][] = $pic['picurl'];
                    }
                }
            }

            $data['auid']   =   $_SESSION['auid'];
            $return         =   $crmM->addDeal($data, 1);
        } else {

            $return =   array('errcode' => 8, 'msg' => '操作错误，请重试！');
        }
        $this->render_json($return['errcode'],$return['msg']);
    }

    public function orderprice_action()
    {

        $id         =   intval($_POST['id']);
        $ratingM    =   $this->MODEL('rating');
        $rating     =   $ratingM->getInfo(array('id' => $id));

        $this->render_json(0,'',$rating);

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

            $this->render_json(0,'',$data);
        }
    }
    function getJobList_action()
    {

        if ($_POST){

            $crmM   =   $this->MODEL('crm');
            $where  =   array('status' => 0, 'state' => 1, 'r_status' => 1);

            if (isset($_POST['edu']) && !empty($_POST['edu'])) {
                $where['edu'] = $_POST['edu'];
            }
            if (isset($_POST['exp']) && !empty($_POST['exp'])) {
                $where['exp'] = $_POST['exp'];
            }
            if (isset($_POST['report']) && !empty($_POST['report'])) {
                $where['report'] = $_POST['report'];
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
                $welfare = explode('，', $_POST['welfare']);
                $where['PHPYUNBTWSTART_B_1'] = '';
                foreach ($welfare as $wk => $wv) {
                    $where['welfare'][] = array('like', $wv, '');
                }
                $where['PHPYUNBTWEND_B_1'] = '';
            }
            if (isset($_POST['stime']) && !empty($_POST['stime'])) {
                $timeStr = intval($_POST['stime']);
                if ($timeStr == 1) {
                    $where['sdate'] = array('>', strtotime('today'));
                } else if ($timeStr == 2) {
                    $where['sdate'] = array('>', strtotime('-3 days'));
                } else if ($timeStr == 3) {
                    $where['sdate'] = array('>', strtotime('-7 days'));
                } else if ($timeStr == 4) {
                    $where['sdate'] = array('>', strtotime('-30 days'));
                }
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

                $keywordStr =   trim($_POST['keyword']);
                $keywordArr =   explode(' ', $keywordStr);
                if (count($keywordArr) > 1){
                    $where['PHPYUNBTWSTART_C']  =   '';
                    foreach ($keywordArr as $kv){

                        $where['com_name'][]    =   array('like', trim($kv), 'OR');
                        $where['name'][]        =   array('like', trim($kv), 'OR');
                        $where['welfare'][]     =   array('like', trim($kv), 'OR');
                    }
                    $where['PHPYUNBTWEND_C']    =   '';
                }else{

                    $where['PHPYUNBTWSTART_C'] = '';
                    $where['com_name'] = array('like', trim($_POST['keyword']), '');
                    $where['name'] = array('like', trim($_POST['keyword']), 'OR');
                    $where['welfare'] = array('like', trim($_POST['keyword']), 'OR');
                    $where['PHPYUNBTWEND_C'] = '';
                }
            }

            $pageM  =   $this->MODEL('page');
            $pages  =   $pageM->adminPageList('company_job', $where, $_POST['page'], array('limit' => $_POST['limit']));

            if ($pages['total'] > 0) {
                if ($_POST['order']) {
                    if ($_POST['order'] == "desc") {
                        $where['orderby'] = $_POST['t'] . ',desc';
                    } else {
                        $where['orderby'] = $_POST['t'] . ',asc';
                    }
                } else {
                    $where['orderby'] = 'lastupdate,desc';
                }
                $where['limit'] = $pages['limit'];
                $jobList = $crmM->getJobList($where);
            }

            $data       =   array(

                'jobList'   =>  !empty($jobList)?$jobList:array(),
                'total'  =>  $pages['total'],
                'page_sizes' =>  $pages['page_sizes'],
                'page_size' =>  $pages['page_size']
            );

            $this->render_json(0,'',$data);
        }
    }

    // 职位推广记录
    function recJob_action()
    {
        $crmM = $this->MODEL('crm');

        $where['auid'] = $_SESSION['auid'];

        if (!empty($_POST['time'])) {
            $timeStr = intval($_POST['time']);

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
        }

        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('crm_job', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        $list = array();
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
                $urlarr['order'] = $_POST['order'];
                $urlarr['t'] = $_POST['t'];
            } else {
                $where['orderby'] = 'atime,desc';
            }

            $where['limit'] = $pages['limit'];
            $list = $crmM->getRecJobLogList($where);
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    // 职位推广记录删除
    function delRecJob_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $del = $_POST['del'];

        $where['id'] = $del;

        $crmM = $this->MODEL('crm');
        $return = $crmM->delRecJobLog($where);

        if ($return['errcode'] == '9') {
            $this->admin_json(0, "职位推广记录删除（ID：{$del}）成功");
        }
        $this->render_json(1, $return['errmsg']);
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

            $this->render_json(0,'',$data);
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

            if (isset($_POST['stime']) && !empty($_POST['stime'])) {
                $timeStr = intval($_POST['stime']);
                if ($timeStr == 1) {
                    $where['sdate'] = array('>', strtotime('today'));
                } else if ($timeStr == 2) {
                    $where['sdate'] = array('>', strtotime('-3 days'));
                } else if ($timeStr == 3) {
                    $where['sdate'] = array('>', strtotime('-7 days'));
                } else if ($timeStr == 4) {
                    $where['sdate'] = array('>', strtotime('-30 days'));
                }
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
                $keywordStr =   trim($_POST['keyword']);
                $keywordArr =   explode(' ', $keywordStr);
                if (count($keywordArr) > 1){
                    $where['PHPYUNBTWSTART_C']  =   '';
                    foreach ($keywordArr as $kv){

                        $where['com_name'][]    =   array('like', trim($kv), 'OR');
                        $where['name'][]        =   array('like', trim($kv), 'OR');
                        $where['welfare'][]     =   array('like', trim($kv), 'OR');
                    }
                    $where['PHPYUNBTWEND_C']    =   '';
                }else{

                    $where['PHPYUNBTWSTART_C'] = '';
                    $where['com_name'] = array('like', trim($_POST['keyword']), '');
                    $where['name'] = array('like', trim($_POST['keyword']), 'OR');
                    $where['welfare'] = array('like', trim($_POST['keyword']), 'OR');
                    $where['PHPYUNBTWEND_C'] = '';
                }
            }

            $where['orderby'] = array('lastupdate, desc');
            $where['limit'] = 100;

            $jobList=   $crmM->getJobList($where);
            $data   =   array('jobList' => $jobList);

            $this->render_json(0,'',$data);
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
                if (isset($_POST['search_stime']) && !empty($_POST['search_stime'])) {
                    $searchData['stime']    =   $_POST['search_stime'];
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
            $this->render_json($return['errcode'],$return['msg'],$return);
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

    // 右上角欢迎信息
    private function getWelcomeData()
    {
        $hour = date('Gi');
        if ($hour > 2100 || $hour <= 100) {
            $greetings = '夜深了，注意休息';
        }elseif ($hour > 100 && $hour <= 500) {
            $greetings = '凌晨好';
        } elseif ($hour > 500 && $hour <= 830) {
            $greetings = '早上好';
        } elseif ($hour > 830 && $hour <= 1100) {
            $greetings = '上午好';
        } elseif ($hour > 1100 && $hour <= 1300) {
            $greetings = '中午好';
        } elseif ($hour > 1300 && $hour <= 1700) {
            $greetings = '下午好';
        } elseif ($hour > 1700 && $hour <= 1800) {
            $greetings = '傍晚好';
        } elseif ($hour > 1800 && $hour <= 2100) {
            $greetings = '晚上好';
        }
        $data['greetings'] = $greetings; // 问候语

        //查询最近登录时间
        $logM  =  $this->MODEL('log');
        $adminLog = $logM -> getAdminLog(array('uid' => $_SESSION['auid'],'content' => '登录成功','orderby' => 'ctime'));
        $data['login_time'] = date('Y-m-d H:i:s', $adminLog['ctime'] ? $adminLog['ctime'] : time());

        $data['user'] = array(
            'name' => $this->adminShell['name'],
            'username' => $this->adminShell['username'],
        );

        $data['adminUserList'] = $this->getAdminUser();

        return $data;
    }

    // 工作台 今日相关统计
    public function getTodayData_action()
    {
        $data = $this->getDateData();
        $welcomeData = $this->getWelcomeData();

        $return = array_merge($data, $welcomeData);
        $return['user'] = array_merge($data['user'], $welcomeData['user']);

        $return['rating_notice'] = intval($this->obj->select_num('rating_notice', array('crm_uid' => $_POST['crm_uid'] ? $_POST['crm_uid'] : $_SESSION['auid'], 'status' => 1)));

        $this->render_json(0, '', $return);
    }

    // 工作台 我的客户
    // 此方法同 crm_my_customer.class.php 中 jobtaiMykhHandle 方法同步修改查询条件
    public function myCustomer_action()
    {
        $today = strtotime('today'); // 今天
        $crm_uid = $_POST['crm_uid'] ? $_POST['crm_uid'] : $_SESSION['auid'];
        $crm_deal_rating = !empty($this->config['crm_deal_rating']) ? explode(',', $this->config['crm_deal_rating']) : array('-9'); // CRM设置的成交会员  默认值-9，避免SQL报错的
        $week = strtotime('-6 day', $today); // 取一周时间（包含当天）
        $month = strtotime('-1 month +1 day', $today); // 取一个月前的时间（包含当天）

        $companyM = $this->MODEL('company');

        $data['gyx_cycle'] = $this->config['crm_jobtai_gyx_cycle'] ? intval($this->config['crm_jobtai_gyx_cycle']) : 3; // 获取工作台数据周期 - 高意向
        $data['hy_cycle'] = $this->config['crm_jobtai_hy_cycle'] ? intval($this->config['crm_jobtai_hy_cycle']) : 3; // 获取工作台数据周期 - 活跃
        $data['jgq_cycle'] = $this->config['crm_jobtai_jgq_cycle'] ? intval($this->config['crm_jobtai_jgq_cycle']) : 3; // 获取工作台数据周期 - 将过期
        $data['qz_cycle'] = $this->config['crm_jobtai_qz_cycle'] ? intval($this->config['crm_jobtai_qz_cycle']) : 3; // 获取工作台数据周期 - 潜在

        $comWhere = "crm_uid = {$crm_uid} AND (isfollow = 0 OR (isfollow = 1 AND f_time < FTIME))";

        // 高意向,X天内未跟进的企业
        $gyx_cycle = $data['gyx_cycle'] - 1;
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

        // 活跃,X天内未跟进的企业
        $hy_cycle = $data['hy_cycle'] - 1;
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

        // 将过期,X天内未跟进的企业
        $jgq_cycle = $data['jgq_cycle'] - 1;
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

        // 潜在,X天内未跟进的企业
        $qz_cycle = $data['qz_cycle'] - 1;
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

        // 高意向用户 X天内未跟进 +（非成交会员，触发权益提醒 OR 7天内有登录，历史成交过，但未续费的客户）
        $gyxUidArr = array();
        if ($companyUidGyxArr) {
            $gyxList = $this->obj->select_all('rating_notice', array(
                'crm_uid' => $crm_uid,
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
            ), 'uid');// X天内有登录，历史成交过，但未续费的客户
            $gyxUidArr = $gyxOrderList ? array_unique(array_merge($gyxUidArr, array_column($gyxOrderList, 'uid'))) : $gyxUidArr;
        }
        $data['gyx_num'] = $gyxUidArr ? count($gyxUidArr) : 0;

        // 潜在用户
        $data['qz_num'] = 0;
        if ($companyUidQzArr) {
            $qzUidArr = array();
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
                $data['qz_num'] = count($qzUidArr);
            }
        }

        // 活跃用户
        $data['hy_num'] = count($companyUidHyArr);

        // 急需跟进
        // 即将超期被释放
        $releaseSet = $this->obj->select_all('crmset');
        $jxgjUidArr = array();
        if ($releaseSet) {
            $companyListJxgj = $companyM->getChCompanyList(array(
                'crm_uid' => $crm_uid,
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
        $planSql = "SELECT c.comid uid FROM {$this->def}company a JOIN {$this->def}crm_work_plan c ON a.uid = c.comid WHERE a.`crm_uid` = {$crm_uid} AND c.auid = {$crm_uid} AND c.stime < " .strtotime('+1 day', $today) . " AND c.type = 22 AND c.status = 1 GROUP BY c.comid";
        $planList = $this->obj->DB_query_all($planSql, 'all');
        $planUidArr = $planList ? array_column($planList, 'uid') : array();
        $planUidArr && $jxgjUidArr = array_unique(array_merge($jxgjUidArr, $planUidArr));
        $data['jxgj_num'] = $jxgjUidArr ? count($jxgjUidArr) : 0;
        
        // 将到期用户 X天内未跟进，15天内将到期的用户
        $data['jdq_num'] = count($companyUidJdqArr);

        // 标签用户
        $data['bq_num'] = 0;
        $bqsetList = $this->MODEL('crm')->getBqsetList(array('auid' => $crm_uid));
        if ($bqsetList) {
            foreach($bqsetList as $bqval) {
                $fu_cycle = intval($bqval['fu_cycle'])-1; // 包含当天
                $bq_num = $companyM->getCompanyNum(array(
                    'crm_uid' => $crm_uid,
                    'isfollow' => 1,
                    'crm_status' => array('in', pylode(',', $bqval['crm_status'])),
                    'f_time' => array('<', strtotime("-{$fu_cycle} day today"))
                ));
                $data['bq_num'] += intval($bq_num);
            }
        }

        array_walk_recursive($data, function(&$val, $key) {
            $val = intval($val);
        });

        $this->render_json(0, '', $data);
    }

    // 工作台 公海客户
    // 此方法同 crm_customer.class.php 中 jobtaiHandle 方法同步修改查询条件
    public function ghCustomer_action()
    {
        $time = time();
        $today = strtotime('today', $time);
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

        // 新客
        $data['new_num'] = 0;
        $userList = $this->MODEL('userinfo')->getList(array(
            'reg_date' => array('>=', $today),
            'usertype' => 2
        ), array('field' => 'uid'));
        if ($userList) {
            $data['new_num'] = $companyM->getCompanyNum(array_merge($where, array(
                'uid' => array('in', pylode(',', array_column($userList, 'uid'))),
                'name' => array('<>', '')
            )));
        }

        // 高意向客户：非成交会员，触发权益提醒 OR 7天内有登录，历史成交过，但未续费的客户
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
        $data['gyx_num'] = $gyxUidArr ? count($gyxUidArr) : 0;


        // 潜在用户
        $data['qz_num'] = 0;
        $month = strtotime('-1 month +1 day', $today); // 取一个月前的时间 (包含当天)
        $companyListQz = $this->obj->DB_select_all('company', "(`crm_uid` = '' OR `crm_uid` = '0' OR `crm_uid` is null) AND login_date < {$month} AND (((rating = 0 OR rating = {$this->config['com_rating']}) AND vipetime = 0) OR (rating in (" . pylode(',', $crm_deal_rating) . ") OR vipetime > 0 AND vipetime < {$today}))", 'uid');
        if ($companyListQz) {
            $companyUidQzArr = array_column($companyListQz, 'uid');
            $qzUidArr = array();
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
                $data['qz_num'] = count($qzUidArr);
            }
            
        }

        // 活跃用户 一周内登录过的（近期登录），未开通会员或会员已过期
        $data['hy_num'] = $this->obj->DB_select_num('company', "(`crm_uid` = '' OR `crm_uid` = '0' OR `crm_uid` is null) AND login_date >= {$week} AND (((rating = 0 OR rating = {$this->config['com_rating']}) AND vipetime = 0) OR (rating in (" . pylode(',', $crm_deal_rating) . ") OR vipetime > 0 AND vipetime < {$today}))");

        // 到期用户
        $data['dq_num'] = 0;
        if ($crm_deal_rating) {
            $dqSql = "SELECT count(o.uid) num FROM {$this->def}company a JOIN {$this->def}company_order o ON a.uid = o.uid WHERE (a.`crm_uid` = '' OR a.`crm_uid` = '0' OR a.`crm_uid` is null) AND a.vipetime > 0 AND a.vipetime < {$today} AND o.type = 1 AND o.order_state = 2 AND o.rating in (" . pylode(',', $crm_deal_rating) . ") GROUP BY o.uid";
            $orderList = $this->obj->DB_query_all($dqSql, 'all');
            $data['dq_num'] = $orderList ? count($orderList) : 0;
        }

        // 近期释放
        $data['jqsf_num'] = $companyM->getCompanyNum(array_merge($where, array(
            'release_time' => array('>=', $week)
        )));

        array_walk_recursive($data, function(&$val, $key) {
            $val = intval($val);
        });

        $this->render_json(0, '', $data);
    }

    // 工作台 我的客户统计
    // 此方法同 crm_my_customer.class.php 中 jobtaiMykhtjHandle 方法同步修改查询条件
    public function myCustomerMonth_action()
    {
        $today = strtotime('today');
        $month = strtotime(date('Y-m')); // 当月
        $crm_uid = $_POST['crm_uid'] ? $_POST['crm_uid'] : $_SESSION['auid'];

        $crmInfo = $this->obj->select_once('admin_user', array('uid' => $crm_uid), '`num`');    //  客户数量
        $companyM = $this->MODEL('company');

        // 本月新增客户
        $newWhere = array(
            'crm_uid' => $crm_uid,
            'crm_time' => array('>=', $month)
        );
        $new_num = $companyM->getCompanyNum($newWhere);
        $data['new_num'] = $new_num;
        // 客户总量
        $data['all_num'] = $companyM->getCompanyNum(array('crm_uid' => $crm_uid));

        // 本月成交客户
        $data['deal_num'] = 0;
        $data['deal_all_num'] = 0;
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
            $dealNumList = $orderM->getList($dealWhere, array('field' => 'uid'));
            $data['deal_num'] = $dealNumList ? count($dealNumList) : 0;

            // 成交总量
            $cjnumWhere = array(
                'crm_uid' => $crm_uid,
                'rating' => array('in', $this->config['crm_deal_rating']),
                'PHPYUNBTWSTART_A' => '',
                'vipetime' => array(
                    '0' => array('=', '0', ''),
                    '1' => array('>', strtotime('today'), 'OR')
                ),
                'PHPYUNBTWEND_A' => '',
            );
            $cjnum = $companyM->getCompanyNum($cjnumWhere);
            $data['deal_all_num'] = $cjnum ? $cjnum : 0;
        }

        //  剩余客户
        if ($this->config['sy_crm_deal_open'] == 1) {
            $data['sy_num'] = (int)$crmInfo['num'] > 0 ? $crmInfo['num'] - ($data['all_num'] - $data['deal_all_num']) : 0;
        } elseif ($this->config['sy_crm_deal_open'] == 2) {
            $data['sy_num'] = (int)$crmInfo['num'] > 0 ? $crmInfo['num'] - $data['all_num'] : 0;
        }

        // 将到期客户
        // 15天内即将到期的客户
        $data['jdq_num'] = 0;
        if ($this->config['crm_deal_rating']) {
            $jdqWhere = array(
                'crm_uid' => $crm_uid,
                'rating' => array('in', $this->config['crm_deal_rating']),
                'vipetime' => array(array('>=', $today), array('<', strtotime("+15 day", $today)))
            );
            $data['jdq_num'] = $companyM->getCompanyNum($jdqWhere);
        }

        // 本月续费客户
        $data['xf_num'] = 0;
        $dySql = "SELECT uid FROM `{$this->def}company_order` WHERE crm_uid = {$crm_uid} AND `type` = 1 AND `order_state` = 2 AND order_time >= {$month} GROUP BY uid"; // 查询当月开通会员的用户
        $dyList = $this->obj->DB_query_all($dySql, 'all');
        if ($dyList) {
            $dyUids = pylode(',', array_column($dyList, 'uid'));
            $xfSql = "SELECT uid,count(uid) num FROM `{$this->def}company_order` WHERE uid IN ({$dyUids}) AND `type` = 1 AND `order_state` = 2 GROUP BY uid HAVING `num` > 1";
            $xfList = $this->obj->DB_query_all($xfSql, 'all');
            $xf_num = $xfList ? count($xfList) : 0;
            $data['xf_num'] = $xf_num;
        }

        // 本月已到期客户
        $dqWhere = array(
            'crm_uid' => $crm_uid,
            'vipetime' => array(array('>=', $month), array('<', $today))
        );
        $dq_num = $companyM->getCompanyNum($dqWhere);
        $data['dq_num'] = $dq_num;

        // 本月暂停客户
        $ztWhere = array(
            'crm_uid' => $crm_uid,
            'r_status' => 4,
        );
        $data['zt_all_num'] = $companyM->getCompanyNum($ztWhere);
        $ztWhere['zt_time'] = array('>=', $month);
        $zt_num = $companyM->getCompanyNum($ztWhere);
        $data['zt_num'] = $zt_num;

        array_walk_recursive($data, function(&$val, $key) {
            $val = intval($val);
        });

        $this->render_json(0, '', $data);
    }

    // 工作台 我的跟进统计
    public function myFollow_action()
    {
        $today = strtotime('today'); // 今天
        $yesterday = strtotime('yesterday'); // 昨天
        $day7 = strtotime('-6 day', $today); // 7日内（算上今天共七天）
        $month = strtotime(date('Y-m')); // 当月
        $crm_uid = $_POST['crm_uid'] ? $_POST['crm_uid'] : $_SESSION['auid'];

        $crmM = $this->MODEL('crm');

        // 本月跟进(跟进日志数量)以跟进时间查询/
        $followWhere = array(
            'auid' => $crm_uid,
            'ftime' => array('>=', $month)
        );
        $follow_num = $crmM->getConcernNum($followWhere);
        $data['follow_num'] = $follow_num;

        // 昨日跟进/完成率
        $followyWhere = array(
            'auid' => $crm_uid,
            'ftime' => array(array('>=', $yesterday), array('<', $today))
        );
        $followy_num = $crmM->getConcernNum($followyWhere);
        $data['followy_num'] = $followy_num;
        $data['followy_num_ratio'] = $this->completionRate($this->adminShell['follow_num'], $followy_num);

        // 7日内跟进/完成率
        $followd7Where = array(
            'auid' => $crm_uid,
            'ftime' => array('>=', $day7)
        );
        $followd7_num = $crmM->getConcernNum($followd7Where);
        $data['followd7_num'] = $followd7_num;
        $data['followd7_num_ratio'] = $this->completionRate($this->adminShell['follow_num'], $followd7_num, 7);


        $callM = $this->MODEL('staffcalllog');

        // 本月通话
        $callWhere = array(
            'uid' => $crm_uid,
            'ctime' => array('>=', $month),
            'source' => 1
        );
        $call_num = $callM->getCallNum($callWhere);
        $data['call_num'] = $call_num;

        // 昨日通话/完成率
        $callyWhere = array(
            'uid' => $crm_uid,
            'ctime' => array(array('>=', $yesterday), array('<', $today))
        );
        $cally_num = $callM->getCallNum($callyWhere);
        $data['cally_num'] = $cally_num;
        $data['cally_num_ratio'] = $this->completionRate($this->adminShell['call_num'], $cally_num);

        // 7天内通话/完成率
        $calld7Where = array(
            'uid' => $crm_uid,
            'ctime' => array('>=', $day7)
        );
        $calld7_num = $callM->getCallNum($calld7Where);
        $data['calld7_num'] = $calld7_num;
        $data['calld7_num_ratio'] = $this->completionRate($this->adminShell['call_num'], $calld7_num, 7);

        array_walk_recursive($data, function(&$val, $key) {
            $val = intval($val);
        });

        $this->render_json(0, '', $data);
    }

    /**
     * 计算较上个月比率
     * @param $current 本月数据
     * @param $last 上个月数据
     * @return int
     */
    private function lastMonthRatio($current, $last)
    {
        $ratio = 0;
        if ($current) {
            // (本月-上个月)/本月*100
            $ratio = bcmul(bcdiv(bcsub($current, $last), $current, 2), 100);
        }

        return $ratio;
    }

    /**
     * 计算完成率
     * @param $target 目标数量
     * @param $num 实际数量
     * @param $day 天数基数(大于一天取平均)，默认1天
     * @return int
     */
    private function completionRate($target, $num, $day = 1)
    {
        $ratio = 0;
        if ($num && $target) {
            if ($day > 1) {
                // ((实际数量/天数)/目标数量)*100
                $ratio = bcmul(bcdiv(bcdiv($num, $day), $target, 2), 100);
            } else {
                // (实际数量/目标数量)*100
                $ratio = bcmul(bcdiv($num, $target, 2), 100);
            }
        } elseif ($num && !$target) {
            $ratio = 100;
        }

        return $ratio;
    }

    // 工作台 成交榜
    public function getDealRanking_action()
    {
        if ($this->adminShell['jobtai_ranking'] != 1) {
            $this->render_json(403, '榜单无查看权限'); // 榜单无查看权限
        }

        $crmM = $this->MODEL('crm');

        $ranking = $crmM->getJebList($_POST['time']);

        $ranking['data'] = array_slice($ranking['data'], 0, 10);

        $this->render_json(0, '', $ranking);
    }

    // 工作台 新客榜
    public function getNewCustomerRanking_action()
    {
        if ($this->adminShell['jobtai_ranking'] != 1) {
            $this->render_json(403, '榜单无查看权限'); // 榜单无查看权限
        }

        $crmM = $this->MODEL('crm');

        $ranking = $crmM->getKhbList($_POST['time']);

        $ranking['data'] = array_slice($ranking['data'], 0, 10);

        $this->render_json(0, '', $ranking);
    }

    /**
     * 获取客户标签设置
     */
    function getBqset_action() {
        $cacheM = $this->MODEL('cache');
        $cacheData = $cacheM->GetCache('crm');

        $crm_status = array();
        if (!empty($cacheData['crmdata']['client_status'])) {
            foreach($cacheData['crmdata']['client_status'] as $val) {
                $crm_status[] = array(
                    'id' => $val,
                    'name' => $cacheData['crmclass_name'][$val]
                );
            }
        }

        $crm_status_arr = array();
        $list = $this->MODEL('crm')->getBqsetList(array('auid' => !empty($_POST['crm_uid']) ? $_POST['crm_uid'] : $_SESSION['auid']));
        if ($list) {
            foreach ($list as &$val) {
                $crm_status_arr = array_merge($crm_status_arr, $val['crm_status']);
                foreach($val['crm_status'] as $csid) {
                    $val['crm_status_n'] .= (isset($val['crm_status_n']) ? '，' : '') . $cacheData['crmclass_name'][$csid];
                }
            }
        }

        $add = count($crm_status) <= count($crm_status_arr) ? false : true; // 判断是否能添加新规则，如果客户标签都被使用即不可添加

        $this->render_json(0, 'ok', compact('crm_status', 'crm_status_arr', 'list', 'add'));
    }

    /**
     * 客户标签设置保存
     */
    function saveBqset_action() {
        $post = $this->post_trim($_POST);

        if (empty($post['crm_status']) || !is_array($post['crm_status']) || empty($post['fu_cycle'])) {
            $this->render_json(1, '参数错误');
        }

        $srmM = $this -> MODEL('crm');

        $data = array(
            'crm_status' => pylode(',', $post['crm_status']),
            'fu_cycle' => intval($post['fu_cycle'])
        );

        $auid = !empty($_POST['crm_uid']) ? $_POST['crm_uid'] : $_SESSION['auid'];
        
        if (!empty($post['id'])) {
            $return = $srmM->updateBqset($data, array('auid' => $auid, 'id' => $post['id']));
            if ($return) {
                $this->admin_json(0, "标签设置修改(ID:{$post['id']})成功");
            } else {
                $this->render_json(1, '标签设置修改失败');
            }
        } else {
            $data['auid'] = $auid;
            $return = $srmM->addBqset($data);
            if ($return) {
                $this->admin_json(0, "标签设置添加(ID:{$return})成功");
            } else {
                $this->render_json(1, '标签设置添加失败');
            }
        }
    }

    /**
     * 客户标签设置删除
     */
    function delBqset_action() {
        $post = $this->post_trim($_POST);

        if (empty($post['id'])) {
            $this->render_json(1, '参数错误');
        }

        $srmM = $this -> MODEL('crm');
        
        $where['auid'] = !empty($_POST['crm_uid']) ? $_POST['crm_uid'] : $_SESSION['auid'];
        $where['id'] = $post['id'];
        $return = $srmM->delBqset($where);
        if ($return) {
            $this->admin_json(0, "标签设置删除(ID:{$post['id']})成功");
        } else {
            $this->render_json(1, '标签设置删除失败');
        }
    }

    /**
     * 工作台-复盘数据获取
     */
    function getFupanData_action() {
        if (empty($_POST['date'])) {
            $this->render_json(1, '参数错误');
        }

        $data = $this->getDateData($_POST['date'], false);

        $this->render_json(0, '', $data);
    }

    /**
     * 获取指定日期数据 默认查询当天
     * 工作台今日数据和工作台复盘均调用此方法
     * @param string $date 查询日期
     * @param bool $rate 是否需要完成率
     */
    private function getDateData($date = 'today', $rate = true)
    {
        $stime = strtotime($date);
        $etime = strtotime('+1 day', $stime);
        $crm_uid = $_POST['crm_uid'] ? $_POST['crm_uid'] : $_SESSION['auid'];

        $user = !empty($_POST['crm_uid']) ? $this->obj->select_once('admin_user', array('uid' => $crm_uid), 'uid,call_num,tuoxin_num,follow_num,deal_num,month_deal_num') : $this->adminShell;

        $rate && $data['user'] = array(
            'call_num' => $user['call_num'],
            'tuoxin_num' => $user['tuoxin_num'],
            'follow_num' => $user['follow_num'],
            'deal_num' => $user['deal_num'],
            'month_deal_num' => $user['month_deal_num'],
        );

        $companyM = $this->MODEL('company');

        // 今日通话量
        $callM = $this->MODEL('staffcalllog');
        $callWhere = array(
            'uid' => $crm_uid,
            'ctime' => array(array('>=', $stime), array('<', $etime))
        );
        $data['call_num'] = $callM->getCallNum($callWhere); // 通话量
        $rate && $data['call_num_rate'] = $this->completionRate($data['user']['call_num'], $data['call_num']); // 通话量率


        $callWhere['time_length'] = array('>', 10);
        $data['call_num_valid'] = $callM->getCallNum($callWhere); // 有效通话
        $data['call_num_valid_rate'] = bcmul(bcdiv($data['call_num_valid'], $data['call_num'], 2), 100); // 有效通话率

        // 今日拓新量
        $data['tuoxin_num'] = $this->obj->select_num('crm_log', array(
            'auid' => $crm_uid,
            'type' => 1,
            'ctime' => array(array('>=', $stime), array('<', $etime))
        ), 'uid');
        $rate && $data['tuoxin_num_rate'] = $this->completionRate($data['user']['tuoxin_num'], $data['tuoxin_num']); // 拓新率

        // 今日跟进量
        $crmM = $this->MODEL('crm');

        // 本月跟进(跟进日志数量)以跟进时间查询/
        $followWhere = array(
            'auid' => $crm_uid,
            'groupby' => 'uid',
            'ftime' => array(array('>=', $stime), array('<', $etime))
        );
        $followList = $crmM->getConcernList($followWhere, array('field' => 'uid'));
        $data['follow_num'] = $followList ? count($followList) : 0;
        $rate && $data['follow_num_rate'] = $this->completionRate($data['user']['follow_num'], $data['follow_num']); // 跟进率

        // 今日成交额
        $order_price = $this->obj->select_once('company_order', array(
            'crm_uid' => $crm_uid,
            'order_state' => 2,
            'order_time' => array(array('>=', $stime), array('<', $etime))
        ), 'SUM(order_price) as total_price');
        $data['deal_num'] = $order_price && $order_price['total_price'] ? $order_price['total_price'] : 0;
        $rate && $data['deal_num_rate'] = $this->completionRate($data['user']['deal_num'], $data['deal_num']); // 成交率

        // 新增岗位
        $data['job_num'] = 0;
        $jobM = $this->MODEL('job');
        $data['job_num'] = $jobM->getJobNum(array('add_crm' => $crm_uid, 'sdate' => array(array('>=', $stime), array('<', $etime))));
        // $companyList = $companyM->getChCompanyList(array(
        //     'crm_uid' => $crm_uid
        // ), array('field' => 'uid')); // 查询我负责的企业
        // if ($companyList) {
        //     $jobUidArr = array_column($companyList, 'uid');
        //     $data['job_num'] = $jobM->getJobNum(array('uid' => array('in', pylode(',', $jobUidArr)), 'sdate' => array(array('>=', $stime), array('<', $etime))));
        // }

        array_walk_recursive($data, function(&$val, $key) {
            !is_array($val) && $val = intval($val);
        });

        return $data;
    }

    /**
     * 查询外呼日志
     */
    function getOutcallLog_action()
    {
        $where = $urlarr = array();
        $staffcalllogM = $this->MODEL('staffcalllog');
        // 从crm工作台通话统计加载
        if (!empty($_POST['source']) && $_POST['source'] == 'jobtai') {
            $where['uid'] = !empty($_POST['crm_uid']) ? intval($_POST['crm_uid']) : intval($_SESSION['auid']); // 查自己或指定下属的拨号记录
        } else {
            // 不传业务员id时，查自己及全部下属客户的外呼记录
            if (isset($_POST['crm_uid']) && $_POST['crm_uid']) {
                $where['uid'] = intval($_POST['crm_uid']);
            } else {
                $AdminM = $this->MODEL('admin');
                // 获取所有业务员
                $adminUserList = $AdminM->getList(array('is_crm' => 1), array('field' => '`uid`,`name`,`username`'));
                if ($adminUserList) {
                    $uids = array();
                    foreach ($adminUserList as $v) {
                        $uids[] = $v['uid'];
                    }
                    $where['uid'] = array('in', pylode(',', $uids));
                }
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
        $contactOssUrl = $this->config['sy_outcall_oss'];
        $timeLength = array('1' => '大于30秒', '2' => '大于1分钟', '3' => '大于3分钟');
        $calllist = array('-1'=>'全部','1' => '主叫挂断', '2' => '主叫接通', '3' => '已接通');
        $search_list = array();
        $search_list[] = array('param'=>'time_length','name'=>'通话时长','value'=>$timeLength);
        $search_list[] = array('param'=>'call_status','name'=>'呼叫状态','value'=>$calllist);
        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page','search_list','adminUserList','crm_uid','contactOssUrl'));
    }
}