<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class crm_audit_controller extends adminCommon
{

    function index_action()
    {

        $companyorderM  =   $this->MODEL('companyorder');

        $where = $urlarr = array();

        $where['order_state']   =   2;
        $where['order_price']   =   array('>', 0);

        if (isset($_GET['iscrm']) && $_GET['iscrm'] == 1){

            $where['crm_uid']   =   array('>', 0);
            $urlarr['iscrm']    =   $_GET['iscrm'];
        }

        if (isset($_GET['time']) && !empty($_GET['time'])) {

            $time   =   intval($_GET['time']);

            if ($time == -1) {

                $timeBegin  =   strtotime('today');
                $timeEnd    =   time();
            } elseif ($time == 1) {

                $timeBegin  =   strtotime('today') - 86400;
                $timeEnd    =   strtotime('today');
            } else if ($time == 2) {

                if (isset($_GET['stime']) && !empty($_GET['etime'])) {
                    $timeBegin = strtotime($_GET['stime']);
                    $urlarr['stime'] = $_GET['stime'];
                }
                if (isset($_GET['etime']) && !empty($_GET['etime'])) {
                    $timeEnd = strtotime($_GET['etime']) + 86400;
                    $urlarr['etime'] = $_GET['etime'];
                }
            } else {

                $timeBegin  =   time() - $time * 86400;
                $timeEnd    =   time();
            }

            if (!empty($timeBegin) && !empty($timeEnd)) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['order_time'][]  =   array('>', $timeBegin, '');
                $where['order_time'][]  =   array('<', $timeEnd, '');
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif (!empty($timeBegin)) {

                $where['order_time']    =   array('>', $timeBegin);
            } elseif (!empty($timeEnd)) {

                $where['order_time']    =   array('<', $timeEnd);
            }

            $urlarr['time']             =   $_GET['time'];
        }

        if ((int)$_GET['uid'] > 0){

            $where['crm_uid']   =   $_GET['uid'];
            $urlarr['uid']      =   $_GET['uid'];
        }else if ($_GET['uid'] == '-1'){

            $where['PHPYUNBTWSTART_B']  =   '';
            $where['crm_uid'][]         =   array('=', 0, '');
            $where['crm_uid'][]         =   array('isnull', '', 'OR');
            $where['PHPYUNBTWEND_B']    =   '';
            $urlarr['uid']              =   '-1';
        }

        $where['groupby']   =   'date';
        $urlarr             =   $_GET;
        $urlarr['page']     =   '{{page}}';
        $pageurl            =   Url($_GET['m'], $urlarr, 'admin');

        $limit  =   10;
        $page   =   $_GET['page'] < 1 ? 1 : $_GET['page'];

        $rows   =   $companyorderM->getList($where, array('field' => "sum(order_price) as `num`,FROM_UNIXTIME(order_time,'%Y-%m-%d') as `date`"));
        $num    =   count($rows);
        $pages['total'] =   $num;

        if ($num > $limit) {

            $pagenav        =   Page($page, $num, $limit, $pageurl, $notpl = false, $this->tpl, 'pagenav');
            $pages['pages'] =   ceil($num / $limit);
            $this->yunset('pagenav', $pagenav);
        }

        if ($pages['total'] > 0) {
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   'date';
            }
            $orderAll       =   $companyorderM->getList($where, array('field' => "sum(order_price) as `num`,FROM_UNIXTIME(order_time,'%Y-%m-%d') as `date`, FROM_UNIXTIME(order_time,'%d') as `day`, FROM_UNIXTIME(order_time,'%m') as `month`, FROM_UNIXTIME(order_time,'%Y') as `year`"));

            $where['limit'] =   array(($page - 1) * $limit, $limit);
            $orderRows      =   $companyorderM->getList($where, array('field' => "sum(order_price) as `num`,FROM_UNIXTIME(order_time,'%Y-%m-%d') as `date`, FROM_UNIXTIME(order_time,'%d') as `day`, FROM_UNIXTIME(order_time,'%m') as `month`, FROM_UNIXTIME(order_time,'%Y') as `year`"));
        }

        //支出
        $owhere['order_state']  =   1;

        if ($_GET['time']) {
            if ($_GET['time'] == -1) {

                $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y'));
                $timeEnd    =   time();
            } elseif ($_GET['time'] == 1) {

                $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y')) - 86400;
                $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y')) - 86400;
            } else {

                $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('Y')) - $_GET['time'] * 86400;
                $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y'));
            }
            $owhere['PHPYUNBTWSTART_A'] =  '';
            $owhere['time'][]           =   array('>=', $timeBegin, '');
            $owhere['time'][]           =   array('<=', $timeEnd, '');
            $owhere['PHPYUNBTWEND_A']   =  '';
        }

        if ($_GET['stime']) {

            $stime  =   explode('-', $_GET['stime']);
            $etime  =   explode('-', $_GET['etime']);

            $timeBegin  =   mktime(0, 0, 0, $stime[1], $stime[2], $stime[0]);
            $timeEnd    =   mktime(23, 59, 59, $etime[1], $etime[2], $etime[0]);
            $owhere['PHPYUNBTWSTART_A'] =  '';
            $owhere['time'][]           =   array('>=', $timeBegin, '');
            $owhere['time'][]           =   array('<=', $timeEnd, '');
            $owhere['PHPYUNBTWEND_A']   =  '';
        }

        if ((int)$_GET['uid'] > 0){

            $owhere['crm_uid']  =   $_GET['uid'];
        }else if ($_GET['uid'] == '-1'){

            $owhere['PHPYUNBTWSTART_B']  =   '';
            $owhere['crm_uid'][]         =   array('=', 0, '');
            $owhere['crm_uid'][]         =   array('isnull', '', 'OR');
            $owhere['PHPYUNBTWEND_B']    =   '';
        }

        $owhere['groupby']      =   'date';

        $fieldo =   "sum(order_price) as `num`,from_unixtime(`time`,'%Y-%m-%d') as `date`";
        $packM  =   $this->MODEL('pack');
        $out    =   $packM->getList($owhere, array('field' => $fieldo));
        $outArr =   array();

        foreach ($out as $r) {
            $outArr [$r['date']]    =   $r['num'];

        }

        $swhere['order_state']      =   2;
        $swhere['crm_uid']          =   array('>', 0);
        if ($_GET['time']) {
            if ($_GET['time'] == -1) {

                $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y'));
                $timeEnd    =   time();
            } elseif ($_GET['time'] == 1) {

                $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y')) - 86400;
                $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y')) - 86400;
            } else {

                $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('Y')) - $_GET['time'] * 86400;
                $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y'));
            }
            $swhere['PHPYUNBTWSTART_A']     =  '';
            $swhere['order_time'][]         =   array('>=', $timeBegin, '');
            $swhere['order_time'][]         =   array('<=', $timeEnd, '');
            $swhere['PHPYUNBTWEND_A']       =  '';
        }
        if ($_GET['stime']) {

            $stime  =   explode('-', $_GET['stime']);
            $etime  =   explode('-', $_GET['etime']);

            $timeBegin  =   mktime(0, 0, 0, $stime[1], $stime[2], $stime[0]);
            $timeEnd    =   mktime(23, 59, 59, $etime[1], $etime[2], $etime[0]);
            $swhere['PHPYUNBTWSTART_A']     =  '';
            $swhere['order_time'][]         =   array('>=', $timeBegin, '');
            $swhere['order_time'][]         =   array('<=', $timeEnd, '');
            $swhere['PHPYUNBTWEND_A']       =  '';
        }
        if ((int)$_GET['uid'] > 0){

            $swhere['crm_uid']  =   $_GET['uid'];
        }else if ($_GET['uid'] == '-1'){

            $swhere['PHPYUNBTWSTART_B']  =   '';
            $swhere['crm_uid'][]         =   array('=', 0, '');
            $swhere['crm_uid'][]         =   array('isnull', '', 'OR');
            $swhere['PHPYUNBTWEND_B']    =   '';
        }
        $swhere['groupby']  =   'date';
        $fields             =   "sum(order_price) as `num`, from_unixtime(`order_time`,'%Y-%m-%d') as `date`";
        $salesmanOrder      =   $companyorderM->getList($swhere, array('field' => $fields));
        $salesmanOrderArr   =   array();
        foreach ($salesmanOrder as $r) {
            $salesmanOrderArr[$r['date']]   =   $r['num'];
        }

        $inTotal            =   0.00;
        $outTotal           =   0.00;
        foreach ($orderAll as &$ar) {

            if (!isset($outArr[$r['date']])) {

                $ar['out']   =   0.00;
                $ar['total'] =   $ar['num'];
            } else {

                $ar['out']   =   $outArr[$ar['date']];
                $ar['total'] =   $ar['num'] - $ar['out'];
            }

            $inTotal        =   bcadd($inTotal , $ar['num'], 2);
            $outTotal       =   bcadd($outTotal, $ar['out'], 2);
        }
        foreach ($orderRows as &$r) {

            if (!isset($outArr[$r['date']])) {

                $r['out']   =   0.00;
                $r['total'] =   $r['num'];
            } else {

                $r['out']   =   $outArr[$r['date']];
                $r['total'] =   $r['num'] - $r['out'];
            }
            $r['salesmanNum']   =   isset($salesmanOrderArr[$r['date']]) ? $salesmanOrderArr[$r['date']] : 0;
        }

        $orderRows  =   $this->getDetail($orderRows);
        $this->yunset('order', $orderRows);
        $this->yunset(array('inTotal' => $inTotal, 'outTotal' => $outTotal));

        $adminM         =   $this->MODEL('admin');
        $auser          =   $adminM->getList(array(), array('field' => '`uid`,`name`'));
        $this->yunset('auser', $auser);

        $this->yuntpl(array('admin/crm_audit'));
    }

    private function getDetail($rows){

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

        if (isset($_GET['iscrm']) && $_GET['iscrm'] == 1){

            $where['crm_uid']       =   array('>', 0);
        }

        $where['order_state']       =   2;
        $where['PHPYUNBTWSTART_A']  =   '';
        $where['order_time'][]      =   array('>=', $timeBegin, 'AND');
        $where['order_time'][]      =   array('<=', $timeEnd, 'AND');
        $where['PHPYUNBTWEND_A']    =   '';
        $where['orderby']           =   'order_time';

        if ((int)$_GET['uid'] > 0){

            $where['crm_uid']   =   $_GET['uid'];
        }else if ($_GET['uid'] == '-1'){

            $where['PHPYUNBTWSTART_B']  =   '';
            $where['crm_uid'][]         =   array('=', 0, '');
            $where['crm_uid'][]         =   array('isnull', '', 'OR');
            $where['PHPYUNBTWEND_B']    =   '';
        }
        $orders     =   $orderM->getList($where, array('field' => '`id`,`uid`,`order_id`, order_price, order_type, order_time,`crm_uid`', 'utype' => 'crmaduit'));

        foreach ($rows as $k => $v) {
            foreach ($orders as $ok => $ov) {
                if ($v['date'] == $ov['date']){
                    $rows[$k]['orders'][]   =   $ov;
                }
            }
        }
        return $rows;
    }
}

