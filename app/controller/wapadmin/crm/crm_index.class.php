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

        if ($_POST) {

            $adminM =   $this->MODEL('admin');

            $uInfo  =   $adminM->getAdminUser(array('uid' => $_SESSION['wuid']), array('wap_crm' => 1, 'field' => 'uid,m_id,name,photo'));

            header('content-type:application/json; charset=utf-8');
            echo json_encode($uInfo);
            die;
        }

        $this->yuntpl(array('wapadmin/crm_index'));
    }

    public function getNotice_action()
    {

        if (!empty($_POST))
        {

            $crmM   =   $this->MODEL('crm');

            $msg    =   $crmM->getNotice($_SESSION['wuid']);

            header('content-type:application/json; charset=utf-8');
            echo json_encode($msg);
            die;
        }
    }

    public function getCrmClass_action()
    {

        if (!empty($_POST)) {

            $adminUser = $this->obj->select_all('admin_user', array('status' => 1), 'uid,name');

            $cacheM = $this->MODEL('cache');

            $crmCache = $cacheM->GetCache(array('crm'));

            $crmClassName = $crmCache['crmclass_name'];
            $crmStatus = $crmType = $followWay = $taskType = array();

            foreach ($crmCache['crmdata']['follow_way'] as $v) {

                $followWay[] = array('id' => $v, 'name' => $crmClassName[$v]);
            }
            foreach ($crmCache['crmdata']['client_type'] as $v) {
                $crmType[] = array('id' => $v, 'name' => $crmClassName[$v]);
            }
            foreach ($crmCache['crmdata']['client_status'] as $v) {
                $crmStatus[] = array('id' => $v, 'name' => $crmClassName[$v]);
            }
            foreach ($crmCache['crmdata']['task_type'] as $v) {

                $taskType[] = array('id' => $v, 'name' => $crmClassName[$v]);
            }

            $ratingM        =   $this -> MODEL('rating');

            if (!empty($this->config['com_rating'])) {

                $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`,`service_price`,`yh_price`,`time_start`,`time_end`', 'rating' => $this->config['com_rating']));
            } else {

                $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`,`service_price`,`yh_price`,`time_start`,`time_end`'));
            }

            $ratingArr      =   array();
            foreach ($rating as $k => $v) {

                if ($v['time_start'] < time() && $v['time_end'] > time()){

                    $ratingArr[]=   array('id' => $v['id'], 'name' => $v['name'], 'price' => $v['yh_price']);
                }else{

                    $ratingArr[]=   array('id' => $v['id'], 'name' => $v['name'], 'price' => $v['service_price']);
                }
            }

            include(CONFIG_PATH . 'db.data.php');
            $canPay =   $orderType  =   array();
            foreach ($arr_data['pay'] as $k => $v) {
                $canPay[]   =   array('id' => $k, 'name' => $v);
            }
            foreach ($arr_data['ordertype'] as $k => $v) {
                $orderType[]    =   array('id' => $k, 'name' => $v);
            }
            $data = array(

                'adminUser' => $adminUser,
                'followWay' => $followWay,
                'crmType' => $crmType,
                'crmStatus' => $crmStatus,
                'taskType' => $taskType,
                'canPay'    =>  $canPay,
                'orderType' =>  $orderType,
                'ratingArr' =>  $ratingArr
            );

            echo json_encode(array('error' => 0, 'data' => $data));
            die();
        }
    }

    public function getJebList_action()
    {
        if ($this->adminShell['jobtai_ranking'] != 1) {
            $this->render_json(403);
        }

        $month                      =   mktime(0,0,0,date('m'),1,date('Y'));
        $orderM                     =   $this->MODEL('companyorder');

        $where['order_state']	    =	2;
        $where['order_time']	    =	array('>=', $month);
        $where['type']	            =	array('<>', 6);
        $monPrice	                =	$orderM->getList($where,array('field'=>'sum(order_price) as `total`'));
        $list['month_price']        =   $monPrice[0]['total'] ? $monPrice[0]['total'] : 0;

        if (floatval($monPrice[0]['total']) > 0){

            $crmM                   =   $this->MODEL('crm');
            $mtime                  =   date('Y-m');

            $list['jebList']        =   $crmM->getJebList($mtime);
        }

        $this->render_json(0, '', $list);
    }

    public function searchCom_action()
    {

        if (isset($_POST['name'])) {

            $AdminM         =   $this->MODEL('admin');
            $adminUserInfo  =   $AdminM->getAdminUser(array('uid' => $_SESSION['wuid']), array('field' => 'org,power,spower'));

            $crmM		=	$this->MODEL('crm');
            $companyM   =   $this->MODEL('company');
            $keyword    =   $this->post_trim($_POST['name']);

            $where['name']  =   array('like', $keyword);

            $uids[]         =   $_SESSION['wuid'];

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

                $adminUserList  =   $AdminM->getList(array('uid' => array('<>', $_SESSION['wuid']), 'org' => array('in', pylode(',', $oIds))), array('field' => '`uid`,`name`,`username`'));
                foreach ($adminUserList as $v) {

                    $uids[]     =   $v['uid'];
                }
            }

            $where['crm_uid'][] =   array('in', pylode(',', $uids));


            $list       =   $companyM->getList($where, array('field' => '`uid`,`name`,`crm_uid`,`crm_status`,`crm_type`'));

            $com        =   $list['list'];

            $data = array();
            if (is_array($com) && !empty($com)) {
                foreach ($com as $val) {

                    $data[] =   array(
                        'uid' => $val['uid'],
                        'name' => $val['name'],
                        'crm_uid' => $val['crm_uid'],
                        'crm_status' => $val['crm_status'],
                        'crm_type' => $val['crm_type'],
                        'crm_status_n' => $val['crm_status_n'],
                        'crm_type_n' => $val['crm_type_n']
                    );
                }
            }

            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
    }

    /**
     * @desc 录订单
     */
    function addDeal_action()
    {
        if ($_POST) {

            $crmM           =   $this->MODEL('crm');
            $data           =   $_POST;
            $data['auid']   =   $_SESSION['wuid'];
            $return         =   $crmM->addDeal($data, 1);
        }
        echo json_encode($return);
        die();
    }
}

?>