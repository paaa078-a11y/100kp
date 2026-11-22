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
class order_controller extends adminCommon
{
    function crm_order_action(){
        $this->yuntpl(array('wapadmin/crm_order'));
    }
    function getOrderStaticData_action(){
        if (!empty($_POST)) {
            $adminM     =   $this->MODEL('admin');
            $crmUser    =   $adminM->getList(array('did'=>0,'is_crm'=>1), array('field' => '`uid`,`name`'));

            $data['crmUser']      =  $crmUser;
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
    }

    function getOrderList_action()
    {
        if (!empty($_POST)) {
            $page   =   $_POST['page'];
            $limit  =   $_POST['limit'];
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
            if (isset($keywordStr) && !empty($keywordStr)) {
                $comM = $this->MODEL('company');
                $comArr = $comM->getList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));

                $uidS = array();
                foreach ($comArr['list'] as $k => $v) {

                    $uidS[] = $v['uid'];
                }
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['uid'] = array('in', pylode(',', $uidS));
                $where['order_id'] = array('like', $keywordStr, 'or');
                $where['PHPYUNBTWEND_A']    =   '';
                $data['keyword'] = $keywordStr;
            }

            if (isset($_POST['timeid']) && !empty($_POST['timeid'])) {
                switch ($_POST['timeid']){
                    case '-1':
                        $start_time = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
                        $end_time   = mktime(23, 59, 59, date('m'), date('d') -1, date('Y'));
                        break;
                    case '1':
                        $start_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                        $end_time   = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                        break;
                    case '7':
                        $start_time = mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'));
                        $end_time   = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                        break;
                    case '30':
                        $start_time = mktime(0, 0, 0, date('m'), date('d') - 30, date('Y'));
                        $end_time   = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                        break;
                    case '90':
                        $start_time = mktime(0, 0, 0, date('m'), date('d') - 90, date('Y'));
                        $end_time   = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                        break;
                    default:
                        break;
                }
                $where['order_time'][]      =   array('>=', $start_time);
                $where['order_time'][]      =   array('<=', $end_time);
            }

            if ($page) {

                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =    array($pagenav, $limit);
            } else {

                $where['limit'] =   $limit;
            }

            if (isset($_POST['order'])) {

                $where['orderby'] = $_POST['t'] . ', ' . $_POST['order'];
            } else {

                $where['orderby'] = array('order_state, ASC', 'id, DESC');
            }
            $list   =   $orderM->getList($where, array('utype' => 'crmdealsp'));

            $data['list'] = count($list) ? $list : [];
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
    }

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
}