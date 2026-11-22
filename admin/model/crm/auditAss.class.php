<?php

/**
 * Class auditAss_controller
 *
 * @author shiy
 * @version 7.0
 */

class auditAss_controller extends adminCommon
{

    /*
     * 管理员 查账助手
     */
    public function getAuditList_action()
    {

        $orderM =   $this->MODEL('companyorder');

        $where  =   array('order_state' => 2, 'order_price' => array('>', 0));

        if (isset($_POST['crm_uid']) && intval($_POST['crm_uid']) > 0){

            $where['crm_uid']   =   $_POST['crm_uid'];
        }

        if (isset($_POST['time']) && !empty($_POST['time'])) {

            $start  =   date('Y-m-d', $_POST['time'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['time'][1] / 1000);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['order_time'][]      =   array('>=', strtotime($start . " 00:00:00"));
            $where['order_time'][]      =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $where['groupby']   =   'date';

        $page       =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $defLimit   =   !empty($_POST['defLimit']) ? intval($_POST['defLimit']) : intval($this->config['sy_listnum']);

        $orderArr   =   $orderM->getList($where, array('field' => "sum(order_price) as `revenue`,FROM_UNIXTIME(order_time,'%Y-%m-%d') as `date`"));
        $num        =   count($orderArr);

        $pages['total']     =   $num;
        $pages['page_sizes']=   array($defLimit, $defLimit * 2, $defLimit * 3, $defLimit * 4);
        $pages['limit']     =   array(($page - 1) * $pageSize, $pageSize);
        $pages['page']      =   $page;

        $orderRows  =   array();
        $total      =   intval($pages['total']);
        if ($pages['total'] > 0) {
            if ($_POST['order']) {

                $where['orderby']   =   $_POST['t'] . ',' . $_POST['order'];
                $urlarr['order']    =   $_POST['order'];
                $urlarr['t']        =   $_POST['t'];
            } else {

                $where['orderby']   =   'date';
            }


            $where['limit'] =   array(($page - 1) * $pageSize, $pageSize);

            $orderRows      =   $orderM->getList($where, array('field' => "sum(order_price) as `revenue`,FROM_UNIXTIME(order_time,'%Y-%m-%d') as `date`, FROM_UNIXTIME(order_time,'%d') as `day`, FROM_UNIXTIME(order_time,'%m') as `month`, FROM_UNIXTIME(order_time,'%Y') as `year`"));
        }

        $orderRows  =   $this->getDetail($orderRows);

        $result     =   array(

            'list'          =>  $orderRows,
            'total'         =>  intval($total),
            'pageSize'      =>  $pageSize,
            'pageSizes'     =>  $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }
    /*
     * 查账助手 获取业务员信息
     */
    function getAuditStaticData_action(){

        $adminM     =   $this->MODEL('admin');
        $crmUser    =   $adminM->getList(array(), array('field' => '`uid`,`name`'));
        $data['crmUser'] = $crmUser;
        $this->render_json(0, 'ok', $data);
    }
    private function getDetail($rows)
    {

        $dateArr    =   array();
        $dk         =   0;
        foreach ($rows as $k => $v) {
            $dateArr[]  =   $v['date'];
            $dk     =   $k;
        }

        $timeBegin  =   strtotime($dateArr[$dk]);
        $timeEnd    =   strtotime($dateArr[0]);
        $timeEnd    =   strtotime('+1 day', $timeEnd);

        $orderM     =   $this->MODEL('companyorder');

        $where['order_state']       =   2;

        $where['PHPYUNBTWSTART_A']  =   '';
        $where['order_time'][]      =   array('>=', $timeBegin, 'AND');
        $where['order_time'][]      =   array('<=', $timeEnd, 'AND');
        $where['PHPYUNBTWEND_A']    =   '';

        $where['orderby']           =   'order_time';

        if (isset($_POST['crm_uid']) && intval($_POST['crm_uid']) > 0){

            $where['crm_uid']       =   $_POST['crm_uid'];
        }

        $orders =   $orderM->getList($where, array('field' => '`id`,`uid`,`order_id`, order_price, order_type, order_time, order_remark,`crm_uid`', 'utype' => 'crmaduit'));

        foreach ($rows as $k => $v) {

            foreach ($orders as $ok => $ov) {
                if ($v['date'] == $ov['date']) {

                    $rows[$k]['orderInfo'][]    =   $ov;
                }
            }
        }
        return $rows;
    }

}