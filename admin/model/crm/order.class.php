<?php

/**
 * Class order_controller
 *
 * @author shiy
 * @version 7.0
 */

class order_controller extends adminCommon
{

    //  待审批订单列表
    function getOrderList_action()
    {

        $orderM =   $this->MODEL('companyorder');

        $where  =   array(
            'crm_uid'   =>  array('<>', ''),
            'is_crm'    =>  1,
            'status'    =>  2,
            'type'      =>  1,
            'order_state' => 3
        );

        if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])) {

            $where['crm_uid']  =   $_POST['crm_uid'];
        }

        $keywordStr =   trim($_POST['keyword']);
        $typeStr    =   intval($_POST['type']);
        if (isset($keywordStr) && !empty($keywordStr)) {
            if ($typeStr == 1){

                $comM   =   $this->MODEL('company');
                $comArr =   $comM->getList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));

                $uidS   =   array();
                foreach ($comArr['list'] as $k => $v) {

                    $uidS[]     =   $v['uid'];
                }

                $where['uid']   =   array('in', pylode(',', $uidS));
            }else if ($typeStr == 2){

                $where['order_id']  =   array('like', $keywordStr);
            }
        }

        if (isset($_POST['time']) && !empty($_POST['time'])) {

            $start  =   date('Y-m-d', $_POST['time'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['time'][1] / 1000);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['order_time'][]      =   array('>=', strtotime($start . " 00:00:00"));
            $where['order_time'][]      =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $page       =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('company_order', $where, $page, array('limit' => $pageSize));

        $list   =   array();
        $total  =   intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {

                $where['orderby'] = $_POST['t'] . ', ' . $_POST['order'];
            } else {

                $where['orderby'] = array('order_state, ASC', 'id, DESC');
            }
            $where['limit'] = $pages['limit'];
            $list   =   $orderM->getList($where, array('utype' => 'crmdealsp'));
        }

        $result     =   array(

            'list'          =>  $list,
            'total'         =>  intval($total),
            'pageSize'      =>  $pageSize,
            'pageSizes'     =>  $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }

    //  审批订单
    function approval_action()
    {

        $orderM =   $this->MODEL('companyorder');
        $orderId=   $_POST['id'];
        if (!empty($orderId)) {

            $return =   $orderM->statusOrder($orderId);

            if ($return['errcode'] == '9') {

                $this->admin_json(0, $return['msg']);
            } else {

                $this->render_json(1, $return['msg']);
            }
        } else {

            $this->render_json(1, '参数错误，请重试！');
        }
    }

    //  删除订单
    function delCrmOrder_action()
    {

        $orderM =   $this->MODEL('companyorder');
        $delId  =   $_POST['id'];
        if (!empty($delId)) {

            $return =   $orderM->del($delId, array('utype' => 'admin'));

            if ($return['errcode'] == '9') {

                $this->admin_json(0, $return['msg']);
            } else {

                $this->render_json(1, $return['msg']);
            }
        } else {

            $this->render_json(1, '参数错误，请重试！');
        }
    }

    //  可分配订单列表
    function orderList_action()
    {

        $orderM =   $this->MODEL('companyorder');

        $where  =   array();

        $where['PHPYUNBTWSTART_A']  =   '';
        $where['crm_uid'][]         =   array('=', 0, '');
        $where['crm_uid'][]         =   array('isnull', '', 'or');
        $where['PHPYUNBTWEND_A']    =   '';

        $where['usertype']      =   2;
        $where['order_state']   =   2;

        $keywordStr =   trim($_POST['keyword']);
        $typeStr    =   intval($_POST['type']);
        if (isset($keywordStr) && !empty($keywordStr)) {
            if ($typeStr == 1){

                $comM   =   $this->MODEL('company');
                $comArr =   $comM->getList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));

                $uidS   =   array();
                foreach ($comArr['list'] as $k => $v) {

                    $uidS[]     =   $v['uid'];
                }

                $where['uid']   =   array('in', pylode(',', $uidS));
            }else if ($typeStr == 2){

                $where['order_id']  =   array('like', $keywordStr);
            }
        }

        if (isset($_POST['time']) && !empty($_POST['time'])) {

            $start  =   date('Y-m-d', $_POST['time'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['time'][1] / 1000);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['order_time'][]      =   array('>=', strtotime($start . " 00:00:00"));
            $where['order_time'][]      =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $page       =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('company_order', $where, $page, array('limit' => $pageSize));

        $list   =   array();
        $total  =   intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {

                $where['orderby'] = $_POST['t'] . ', ' . $_POST['order'];
            } else {

                $where['orderby'] = array('order_state, ASC', 'id, DESC');
            }
            $where['limit'] = $pages['limit'];
            $list   =   $orderM->getList($where, array('utype' => 'crmdealfp'));
        }

        $result     =   array(

            'list'          =>  $list,
            'total'         =>  intval($total),
            'pageSize'      =>  $pageSize,
            'pageSizes'     =>  $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }
    function getOrderStaticData_action(){
        
        $adminM     =   $this->MODEL('admin');
        $crmUser    =   $adminM->getList(array('did'=>0,'is_crm'=>1), array('field' => '`uid`,`name`'));
        
        $data['crmUser']      =  $crmUser;

        $this->render_json(0, 'ok', $data);
    }
    //  分配订单
    function allocation_action()
    {

        $orderM =   $this->MODEL('companyorder');
        $orderId=   $_POST['id'];
        $crmUid =   $_POST['crm_uid'];
        if (!empty($orderId) && !empty($crmUid)) {

            if (is_array($orderId)){

                $idStr  =   pylode(',', $orderId);
            }else{

                $idStr  =   $orderId;
            }
            $return =   $orderM->setCrm(array('crm_uid' => $_POST['crm_uid']), array('id' => $idStr));

            $error  =   $return['errcode'] == '9' ? 0 : 1;
            $this->render_json($error, $return['msg']);
        } else {

            $this->render_json(1, '参数错误，请重试！');
        }
    }

}