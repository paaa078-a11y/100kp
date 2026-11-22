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

class crm_waitingtask_controller extends adminCommon
{

    function set_search()
    {

        $cacheM     =   $this->MODEL('cache');
        $cache      =   $cacheM->GetCache(array('crm'));
        $this->yunset('cache', $cache);

        $timeArr    =   array('1' => '今天', '2' => '明天', '3' => '后天', '4' => '一周内');
        $statusArr  =   array('1' => '进行中', '2' => '已完成', '3' => '未完成', '4' => '已取消');
        $this->yunset(array('timeArr' => $timeArr, 'statusArr' => $statusArr));
    }

    function index_action()
    {

        $this->set_search();

        $crmM   =   $this->MODEL('crm');

        $today  =   strtotime('today');

        $where  =   $urlarr =   array();

        $where['uid']   =   $_SESSION['auid'];

        if (isset($_GET['time']) && !empty($_GET['time'])) {

            $timeStr    =   intval($_GET['time']);

            if ($timeStr == 1) {        //  今天

                $sDate  =   $today;
                $eDate  =   $today + 86400;
            } elseif ($timeStr == 2) {  //  明天

                $sDate  =   $today + 86400;
                $eDate  =   $today + 86400 * 2;
            } elseif ($timeStr == 3) {  //  后天

                $sDate  =   $today + 86400 * 2;
                $eDate  =   $today + 86400 * 3;
            } elseif ($timeStr == 4) {  //  一周内

                $sDate  =   $today + 86400;
                $eDate  =   $today + 86400 * 7;
            }
            $where['PHPYUNBTWSTART_A'] = '';
            $where['stime'][] = array('>=', $sDate, '');
            $where['stime'][] = array('<', $eDate, '');
            $where['PHPYUNBTWEND_A'] = '';

            $urlarr['time'] =   $timeStr;
        }

        if (isset($_GET['status']) && !empty($_GET['status'])) {

            $status =   intval($_GET['status']);

            $where['status']    =   $status;
            $urlarr['status']   =   $status;
        }

        if (isset($_GET['type']) && !empty($_GET['type'])) {

            $type           =   intval($_GET['type']);
            $where['type']  =   $type;
            $urlarr['type'] =   $type;
        }

        if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {

            $KeywordStr =   trim($_GET['keyword']);

            $comList    =   $this->obj->select_all('company', array('name' => array('like', $KeywordStr)), '`uid`');
            if (!empty($comList)) {
                $comIds =   array();
                foreach ($comList as $ck => $cv) {
                    $comIds[]   =   $cv['uid'];
                }
            }

            if (!empty($comIds)) {

                $where['PHPYUNBTWSTART_B']  =   '';
                $where['content']           =   array('like', $KeywordStr, '');
                $where['comid']             =   array('in', pylode(',', $comIds), 'OR');
                $where['PHPYUNBTWEND_B']    =   '';
            } else {

                $where['content']   =   array('like', $KeywordStr);
            }

            $urlarr['keyword']      =   $KeywordStr;
        }

        $urlarr         =   $_GET;
        $urlarr['page'] =   '{{page}}';

        $pageurl=   Url($_GET['m'], $urlarr, 'admin');
        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('crm_work_plan', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   array('status,asc', 'stime,asc');
            }

            $where['limit']         =   $pages['limit'];
            $list   =   $crmM->getTaskList($where, array('utype' => 'crm'));
            $this->yunset(array('tasks' => $list));
        }
        $this->yuntpl(array('admin/crm_waitingtask'));
    }

    function getTaskNum_action()
    {

        $crmM = $this->MODEL('crm');

        echo $crmM->getTaskNum($_SESSION['auid']);
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

    /**
     * @desc 部门下属任务
     */
    function depart_action()
    {

        $this->set_search();

        $crmM   =   $this->MODEL('crm');

        $adminUserList  =   $this->getAdminUser();

        $this->yunset('crmUser', $adminUserList);

        $uids   =   array();
        foreach ($adminUserList as $ak => $av) {
            $uids[] =   $av['uid'];
        }

        $where = $urlarr = array();

        if (!empty($_GET['crmuid'])) {

            $where['uid']       =   $_GET['crmuid'];
            $urlarr['crmuid']   =   $_GET['crmuid'];
        } else {

            $where['uid']       =   array('in', pylode(',', $uids));
        }

        $today  =   strtotime(date('Y-m-d'));

        if (isset($_GET['time']) && !empty($_GET['time'])) {

            $timeStr    =   intval($_GET['time']);

            if ($timeStr == 1) {        //  今天

                $sDate  =   $today;
                $eDate  =   $today + 86400;
            } elseif ($timeStr == 2) {  //  明天

                $sDate  =   $today + 86400;
                $eDate  =   $today + 86400 * 2;
            } elseif ($timeStr == 3) {  //  后天

                $sDate  =   $today + 86400 * 2;
                $eDate  =   $today + 86400 * 3;
            } elseif ($timeStr == 4) {  //  一周内

                $sDate  =   $today + 86400;
                $eDate  =   $today + 86400 * 7;
            }
            $where['PHPYUNBTWSTART_A'] = '';
            $where['stime'][] = array('>=', $sDate, '');
            $where['stime'][] = array('<', $eDate, '');
            $where['PHPYUNBTWEND_A'] = '';

            $urlarr['time'] =   $timeStr;
        }

        if (isset($_GET['status']) && !empty($_GET['status'])) {

            $status =   intval($_GET['status']);

            $where['status']    =   $status;
            $urlarr['status']   =   $status;
        }

        if (isset($_GET['type']) && !empty($_GET['type'])) {

            $type           =   intval($_GET['type']);
            $where['type']  =   $type;
            $urlarr['type'] =   $type;
        }

        if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {

            $KeywordStr =   trim($_GET['keyword']);

            $comList    =   $this->obj->select_all('company', array('name' => array('like', $KeywordStr)), '`uid`');
            if (!empty($comList)) {
                $comIds =   array();
                foreach ($comList as $ck => $cv) {
                    $comIds[]   =   $cv['uid'];
                }
            }

            if (!empty($comIds)) {

                $where['PHPYUNBTWSTART_B']  =   '';
                $where['content']           =   array('like', $KeywordStr, '');
                $where['comid']             =   array('in', pylode(',', $comIds), 'OR');
                $where['PHPYUNBTWEND_B']    =   '';
            } else {

                $where['content']   =   array('like', $KeywordStr);
            }

            $urlarr['keyword']      =   $KeywordStr;
        }

        $urlarr         =   $_GET;
        $urlarr['page'] =   '{{page}}';
        $urlarr['c']    =   $_GET['c'];

        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('crm_work_plan', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   array('status,asc', 'stime,asc');
            }
            $where['limit'] =   $pages['limit'];

            $list   =   $crmM->getTaskList($where, array('utype' => 'crm'));

            $this->yunset('tasks', $list);
        }
        $this->yuntpl(array('admin/crm_waitingtask_depart'));
    }

    function getDepartTaskNum_action()
    {

        $crmM           =   $this->MODEL('crm');

        $adminUserList  =   $this->getAdminUser();

        $uids           =   array();
        foreach ($adminUserList as $ak => $av) {
            $uids[]     =   $av['uid'];
        }
        echo $crmM->getTaskNum($uids);
    }

    /**
     * @desc  任务状态设置
     */
    function setStatus_action()
    {

        $crmM   =   $this->MODEL('crm');
        $tValue =   array(
            'status'    =>  intval($_POST['status']),
            'reason'    =>  $_POST['reason'],
            'etime'     =>  $_POST['status'] == '2' ? time() : ''
        );

        $data   =   array(
            'auid'  =>  $_SESSION['auid'],
            'tValue'=>  $tValue
        );

        $nid    =   $crmM->upTask($data, array('id' => intval($_POST['id'])));

        if ($nid) {
            echo 1;
            die();
        }
    }

    function delTask_action()
    {

        $this->check_token();

        $crmM   =   $this->Model('crm');
        $delID  =   intval($_GET['id']);
        $return =   $crmM->delTask(array('id' => $delID), array('auid' => $_SESSION['auid']));
        $this->layer_msg($return['msg'], $return['errcode'], 0, $_SERVER['HTTP_REFERER'], 2, 1);
    }

    function add_action()
    {

        if ($_POST) {

            $crmM = $this->MODEL('crm');

            $Data = array(

                'auid' => $_SESSION['auid'],
                'uid' => $_POST['taskHuid'],
                'comid' => $_POST['comid'],
                'type' => $_POST['type'],
                'content' => $_POST['content']
            );

            if ($_POST['type'] == '22') {
                $Data['stime'] = $_POST['stime'];
            } else {
                unset($Data['stime']);
            }
            $return = $crmM->addWaitingTask($Data);
        }

        echo json_encode($return);
        die();
    }

    function detail_action()
    {
        $crmM = $this->MODEL('crm');
        $info = $crmM->getTaskInfo(array('id' => intval($_POST['id'])));

        $companyM = $this->MODEL('company');
        $cominfo = $companyM->getInfo($info['comid'], array(
            'field' => '`uid`,`name`'
        ));
        $info['comname'] = $cominfo['name'];
        echo json_encode($info);
    }

    function ComDetail_action()
    {

        $uid = intval($_POST['uid']);

        $companyM = $this->MODEL('company');

        $info = $companyM->getInfo($uid, array('field' => '`uid`,`name`,`linktel`,`linkphone`,`provinceid`,`cityid`,`three_cityid`,`linkman`'));

        $info['cityname'] = $info['job_city_one'] . ' ' . $info['job_city_two'] . ' ' . $info['job_city_three'];

        if ($info['linktel']) {

            $info['moblie'] = $info['linktel'];
        } else {

            $info['moblie'] = $info['linkphone'];
        }

        $statisM = $this->MODEL('statis');
        $statis = $statisM->getInfo((int)$_POST['uid'], array('usertype' => 2, 'field' => '`rating_name`,`vip_etime`'));

        if ($statis) {
            $info['ratingname'] = $statis['rating_name'];
            if ($statis['vip_etime']) {

                $info['ratingtime'] = date('Y-m-d', $statis['vip_etime']);
            } else {

                $info['ratingtime'] = '永久会员';
            }
        }
        echo json_encode($info);
    }

    function reason_action()
    {
        $crmM = $this->MODEL('crm');
        $info = $crmM->getTaskInfo(array('id' => intval($_POST['id'])), array('field' => '`reason`'));
        echo $info['reason'];
    }
}

?>