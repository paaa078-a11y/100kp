<?php

/**
 * Class task_controller
 *
 * @author shiy
 * @version 7.0
 */

class taskYwy_controller extends adminCommon
{

    function getTaskList_action()
    {
        $crmM = $this->MODEL('crm');
        $where = array();
        $tabStr = intval($_POST['tab']);
        if (isset($tabStr) && !empty($tabStr)) {
            if ($tabStr == 1) {
                $where['uid'] = $_SESSION['auid'];
            } elseif ($tabStr == 2) {
                $adminUserList = $this->getAdminUser();  //  下属信息
                $orgUidS = array();
                foreach ($adminUserList as $v) {
                    $orgUidS[] = $v['uid'];
                }
                $where['uid'] = array('in', pylode(',', $orgUidS));
            }
        }
        if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])) {
            $where['uid'] = $_POST['crm_uid'];
        }
        if (isset($_POST['day']) && !empty($_POST['day'])) {
            $today = strtotime(date('Y-m-d'));
            $dayStr = intval($_POST['day']);
            if ($dayStr == 1) {        //  今天
                $sDate = $today;
                $eDate = $today + 86400;
            } elseif ($dayStr == 2) {  //  明天
                $sDate = $today + 86400;
                $eDate = $today + 86400 * 2;
            } elseif ($dayStr == 3) {  //  后天
                $sDate = $today + 86400 * 2;
                $eDate = $today + 86400 * 3;
            } elseif ($dayStr == 4) {  //  一周内
                $sDate = $today;
                $eDate = $today + 86400 * 7;
            }
            $where['PHPYUNBTWSTART_A'] = '';
            $where['stime'][] = array('>=', $sDate, '');
            $where['stime'][] = array('<', $eDate, '');
            $where['PHPYUNBTWEND_A'] = '';
        }
        if (isset($_POST['status']) && !empty($_POST['status'])) {
            $where['status'] = $_POST['status'];
        }
        if (isset($_POST['type']) && !empty($_POST['type'])) {
            $where['type'] = $_POST['type'];
        }
        $keywordStr = trim($_POST['keyword']);
        $typeStr = intval($_POST['stype']);
        if (isset($keywordStr) && !empty($keywordStr)) {
            if ($typeStr == 1){
                $comM = $this->MODEL('company');
                $comArr = $comM->getList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));
                $uidS = array();
                foreach ($comArr['list'] as $k => $v) {
                    $uidS[] = $v['uid'];
                }
                $where['comid'] = array('in', pylode(',', $uidS));
            }else if ($typeStr == 2){
                $where['content'] = array('like', $keywordStr);
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('crm_work_plan', $where, $page, array('limit' => $pageSize));
        $list = array();
        $total = intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {
                $where['orderby'] = $_POST['t'] . ', ' . $_POST['order'];
            } else {
                $where['orderby'] = array('status,asc', 'stime,asc');
            }
            $where['limit'] = $pages['limit'];
            $list = $crmM->getTaskList($where, array('utype' => 'crm'));
        }
        $result = array(
            'list' => $list,
            'total' => intval($total),
            'pageSize' => $pageSize,
            'pageSizes' => $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }

    function getTaskStaticData_action()
    {
        $crmM = $this->MODEL('crm');
        $crmUser = array();
        $tabStr = intval($_POST['tab']);
        if ($tabStr == 2) {
            $crmUser = $this->getAdminUser();
        } else if ($tabStr == 0) {
            $adminM = $this->MODEL('admin');
            $crmUser = $adminM->getList(array('did' => 0, 'is_crm' => 1), array('field' => '`uid`,`name`'));
        }
        if ($tabStr == 1) {
            $taskNumArr = $crmM->getTaskNum($_SESSION['auid']);
        } else {
            $crmUidS = array();
            foreach ($crmUser as $ck => $cv) {
                $crmUidS[] = $cv['uid'];
            }
            $taskNumArr = $crmM->getTaskNum($crmUidS);
        }
        $taskNumArr = json_decode($taskNumArr, 1);
        $cacheM = $this->MODEL('cache');
        $cache = $cacheM->GetCache(array('crm'));
        $taskType = $cache['crmdata']['task_type'];
        $typeOption = array();
        foreach ($taskType as $v) {
            $typeOption[] = array('id' => $v, 'name' => $cache['crmclass_name'][$v]);
        }
        $result = array(
            'allNum' => $taskNumArr['taskAllNum'] ? $taskNumArr['taskAllNum'] : 0,
            'status1Num' => $taskNumArr['taskStatusNum1'] ? $taskNumArr['taskStatusNum1'] : 0,
            'status2Num' => $taskNumArr['taskStatusNum2'] ? $taskNumArr['taskStatusNum2'] : 0,
            'status3Num' => $taskNumArr['taskStatusNum3'] ? $taskNumArr['taskStatusNum3'] : 0,
            'status4Num' => $taskNumArr['taskStatusNum4'] ? $taskNumArr['taskStatusNum4'] : 0,
            'crmUser' => $crmUser,
            'typeOption' => $typeOption
        );
        $this->render_json(0, '', $result);
    }

    function delTask_action()
    {
        $crmM = $this->MODEL('crm');
        $delId = $_POST['id'];
        if (!empty($delId)) {
            $return = $crmM->delTask($delId);
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        } else {
            $this->render_json(1, '参数错误，请重试！');
        }
    }

    function setStatus_action()
    {
        $crmM = $this->MODEL('crm');
        $tValue = array(
            'status' => intval($_POST['status']),
            'reason' => $_POST['reason'],
            'etime' => $_POST['status'] == '2' ? time() : ''
        );
        $data = array(
            'auid' => $_SESSION['auid'],
            'tValue' => $tValue
        );
        $nid = $crmM->upTask($data, array('id' => intval($_POST['id'])));
        if(!empty($nid)){
            $error = 0;
            $msg = '反馈成功';
        }else{
            $error = 1;
            $msg = '反馈失败请重试';
        }
        $this->render_json($error,$msg);
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
        $this->render_json(0,'',$info);

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
        }else{
            $return['errcode'] = '8';
            $return['msg'] = '数据异常请重试';
        }
        $this->render_json($return['errcode'],$return['msg'],$return);
    }

}