<?php

/**
 * Class outLog_controller
 *
 * @author shiy
 * @version 7.0
 */

class outLog_controller extends adminCommon
{

    function getOutLog_action()
    {

        $crmM   =   $this->MODEL('crm');

        $where          =   array();
        if (isset($_POST['tab']) && intval($_POST['tab']) == 1){

            $where['auid']  =   $_SESSION['auid'];
        }

        if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])) {

            $where['auid']  =   $_POST['crm_uid'];
        }

        if (isset($_POST['reason']) && !empty($_POST['reason'])) {

            $where['reason']=   $_POST['reason'];
        }

        if (isset($_POST['status']) && !empty($_POST['status'])) {

            $where['status']=   $_POST['status'];
        }

        if (isset($_POST['time']) && !empty($_POST['time'])) {

            $start  =   date('Y-m-d', $_POST['time'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['time'][1] / 1000);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['ctime'][]           =   array('>=', strtotime($start . " 00:00:00"));
            $where['ctime'][]           =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $page   =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('crm_out', $where, $page, array('limit' => $pageSize));

        $list   =   array();
        $total  =   intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {

                $where['orderby'] = $_POST['t'].', '.$_POST['order'];
            } else {

                $where['orderby'] = array('status, asc', 'ctime,desc');
            }
            $where['limit'] = $pages['limit'];
            $list   =   $crmM->getOutList($where, array('utype' => 'crm'));
        }

        $cacheM     =   $this->MODEL('cache');
        $cache      =   $cacheM->GetCache(array('crm'));
        $outReason  =   $cache['crmdata']['crm_outclass'];
        $reasonArr  =   array();
        foreach ($outReason as $v){
            $reasonArr[]    =   array('id' => $v, 'name' => $cache['crmclass_name'][$v]);
        }

        $result     =   array(

            'list'          =>  $list,
            'total'         =>  intval($total),
            'pageSize'      =>  $pageSize,
            'pageSizes'     =>  $pages['page_sizes'],
        );

        $this->render_json(0, '', $result);
    }
    function getOutLogStaticData_action(){

        $crmM   =   $this->MODEL('crm');

        $crmUser    =   array();

        $customerList   =   array();

        if (isset($_POST['tab']) && intval($_POST['tab']) == 1) {

            $outNumArr = $crmM->getOutNum($_SESSION['auid']);

            $customerList   =   $this->obj->select_all('company', array('crm_uid' => $_SESSION['auid'], 'orderby' => 'f_time,desc'), '`uid`,`name`');
            
        } else {

            $adminM = $this->MODEL('admin');
            $crmUser = $adminM->getList(array('did'=>0,'is_crm'=>1), array('field' => '`uid`,`name`'));
            foreach ($crmUser as $v) {

                $crmUidS[] = $v['uid'];
            }
            $outNumArr = $crmM->getOutNum($crmUidS);
        }

        $outNumArr  =   json_decode($outNumArr,1);

        $cacheM     =   $this->MODEL('cache');
        $cache      =   $cacheM->GetCache(array('crm'));
        $outReason  =   $cache['crmdata']['crm_outclass'];
        $reasonArr  =   array();
        foreach ($outReason as $v){
            $reasonArr[]    =   array('id' => $v, 'name' => $cache['crmclass_name'][$v]);
        }

        $result     =   array(

            'allNum'        =>  $outNumArr['allNum'] ?  $outNumArr['allNum'] : 0,
            'status1Num'    =>  $outNumArr['status1Num'] ?  $outNumArr['status1Num'] : 0,
            'status2Num'    =>  $outNumArr['status2Num'] ?  $outNumArr['status2Num'] : 0,
            'status3Num'    =>  $outNumArr['status3Num'] ?  $outNumArr['status3Num'] : 0,
            'crmUser'       =>  $crmUser,
            'reasonArr'     =>  $reasonArr,
            'customerList'  =>  $customerList
        );

        $this->render_json(0, '', $result);
    }

    function setStatus_action()
    {

        $crmM   =   $this->MODEL('crm');
        $outId  =   $_POST['id'];
        if (!empty($outId)) {

            $upData =   array('status' => $_POST['status'],'statusbody' => trim($_POST['statusbody']));
            $result =   $crmM->upOut($upData, array('id' => $outId));

            $statusStr  =   intval($_POST['status']);

            if ($statusStr ==  2){
                $msg    =   '外出申请（ID：'.$outId.'）审批成功';
            }elseif ($statusStr == 3){
                $msg    =   '拒绝外出（ID：'.$outId.'）审批成功';
            }
            if ($result) {
                $this->admin_json(0, $msg);
            } else {

                $this->render_json(1, '审批失败！');
            }
        } else {

            $this->render_json(1, '参数错误，请重试！');
        }
    }

    function delOutLog_action()
    {

        $crmM   =   $this->MODEL('crm');
        $delId  =   $_POST['id'];
        if (!empty($delId)) {

            $return =   $crmM->delOut($delId);

            if ($return['errcode'] == 9) {

                $this->admin_json(0, $return['msg']);
            } else {

                $this->render_json(1, $return['msg']);
            }
        } else {

            $this->render_json(1, '参数错误，请重试！');
        }
    }
}