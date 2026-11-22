	<?php

/*
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class crm_my_performance_controller extends adminCommon
{

    private $chartType = 'line';

    private function getTotal($timeBegin = '', $timeEnd = '')
    {
        $uids = array();
        if($_POST['depart']=='1'){
            $admindata      =   $this->getAuser();
            $uids           =   $admindata['adminuids'];
        }
        $comM               =   $this->MODEL('company');
        $where              =   array();
        if($_POST['depart']=='1'){
            if($_POST['crmmanager'] && $_POST['crmmanager'] != 'all'){
                $where['crm_uid']   =   $_POST['crmmanager'];
            }else{
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['crm_uid'][]         =   array('<>',0,'AND');
                $where['crm_uid'][]         =   array('in',pylode(',',$uids),'AND');
                $where['PHPYUNBTWEND_A']    =   '';
            }
        }else{
            $where['crm_uid']   =   $_SESSION["auid"];
        }
        if ($timeBegin != '') {
            $where['crm_time'][]    =   array('>=', $timeBegin);
            $where['crm_time'][]    =   array('<=', $timeEnd);
        }
        $comlist            =   $comM->getList($where, array('field' => 'count(uid) as `num`'));
        $row                =   $comlist['list'];
        $all                =   isset($row[0]['num']) && $row[0]['num'] > 0 ? $row[0]['num'] : 0;   //  所有客户
        $cwhere             =   array();
        if($_POST['depart']=='1'){
            if($_POST['crmmanager'] && $_POST['crmmanager'] != 'all'){
                $cwhere['crm_uid']  =   $_POST['crmmanager'];
            }else{
                $cwhere['PHPYUNBTWSTART_A']  =   '';
                $cwhere['crm_uid'][]         =  array('<>',0,'AND');
                $cwhere['crm_uid'][]         =  array('in',pylode(',',$uids),'AND');
                $cwhere['PHPYUNBTWEND_A']    =   '';
            }
        }else{
            $cwhere['crm_uid']  =   $_SESSION["auid"];
        }
        $cwhere['isfollow'] =   '0';
        if ($timeBegin != '') {
            $cwhere['crm_time'][] = array('>=', $timeBegin);
            $cwhere['crm_time'][] = array('<=', $timeEnd);
        }
        $comlist            =   $comM->getList($cwhere, array('field' => 'count(uid) as `num`'));
        $row                =   $comlist['list'];
        $new                =   isset($row[0]['num']) && $row[0]['num'] > 0 ? $row[0]['num'] : 0;   //  待跟进客户
        $attention          =   $all - $new;    //  已近跟进客户
        return array( $all, $new, $attention);
    }

    /*
     * 业务员 我的业绩
     */
    public function index_action()
    {
        $admindata      =   $this->getAuser();
        $uids           =   $admindata['adminuids'];
        $adminUserList  =   $admindata['adminUserList'];
        list ($all, $new, $attention) = $this->getTotal(strtotime(date('Y-m-01 00:00:00', time())), time());
        $tpldata[]  =   array('time' => '本月', 'all' => $all, 'new' => $new, 'attention' => $attention);
        $timeEnd    =   strtotime(date('Y-m-d 00:00:00', time()));
        $week       =   date('w');
        if ($week == 0)
            $week = 7;
        $week --;
        $monday     =   strtotime("-{$week} day", $timeEnd);
        list ($all, $new, $attention)   =   $this->getTotal($monday, $timeEnd);
        $tpldata[]  =   array('time' => '本周', 'all' => $all, 'new' => $new, 'attention' => $attention);
        $all        =   $this->getTotal($monday, $timeEnd);
        list ($all, $new, $attention)   =   $this->getTotal(strtotime('-1 day', $timeEnd), $timeEnd);
        $tpldata[]  =   array('time' => '昨日', 'all' => $all, 'new' => $new, 'attention' => $attention);
        list ($all, $new, $attention) = $this->getTotal();
        $tpldata[]  =   array('time' => '累计', 'all' => $all, 'new' => $new, 'attention' => $attention);
        if ($_POST['time']!='' || $_POST['stime'] || $_POST['etime']) {
            if ($_POST['time']) {
                if ($_POST['time'] == - 1) {
                    $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y'));
                    $timeEnd    =   time();
                    $title      =   "客户统计 - 今天";
                } elseif ($_POST['time'] == 1) {
                    $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y'))  - 86400;
                    $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y')) - 86400;
                    $title      =   "客户统计 - 昨天";
                } else {
                    $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('Y')) - $_POST['time'] * 86400;
                    $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                    $title      =   "客户统计 - {$_POST['time']}天内";
                }
            }
            if ($_POST['stime']) {
                $stime          =   explode('-', $_POST['stime']);
                $etime          =   explode('-', $_POST['etime']);
                $timeBegin      =   mktime(0, 0, 0, $stime[1], $stime[2], $stime[0]);
                $timeEnd        =   mktime(23, 59, 59, $etime[1], $etime[2], $etime[0]);
                $title          =   "客户统计 - {$_POST['stime']}~{$_POST['etime']}";
            }
        } else {
            $title              =   "客户统计 - 全部数据";
        }
        $names  =   $values =   $dataGroupNames =   array();
        $where  =   array();
        if($_POST['depart']=='1'){
            if($_POST['crmmanager'] && $_POST['crmmanager'] != 'all'){
                $where['crm_uid']   =   $_POST['crmmanager'];
            }else{
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['crm_uid'][]         =   array('<>',0,'AND');
                $where['crm_uid'][]         =   array('in',pylode(',',$uids),'AND');
                $where['PHPYUNBTWEND_A']    =   '';
            }
        }else{
            $where['crm_uid']       =   $_SESSION["auid"];
        }
        if ($timeBegin != '') {
            $where['crm_time'][]    =   array('>=', $timeBegin);
            $where['crm_time'][]    =   array('<=', $timeEnd);
        }
        $where['groupby']   =   'date';
        $where['orderby']   =   'date';
        $companyM   =   $this->MODEL('company');
        $comlist    =   $companyM -> getList($where, array('field' => "count(uid) as `num`, from_unixtime(crm_time,'%Y-%m-%d') as `date`"));
        $row        =   $comlist['list'];
        $totalAll   =   0;
        if (count($row) < 1) {
            $all    =   array(
                'names'     =>  array(),
                'values'    =>  array()
            );
        } else {
            $allNames   =   array();
            $allValues  =    array();
            foreach ($row as $r) {
                $allNames[]     =   $r['date'];
                $allValues[]    =   $r['num'];
                $totalAll += $r['num'];
            }
            $all = array(
                'names'     =>  $allNames,
                'values'    =>  $allValues
            );
        }
        $cwhere                 =   array();
        if($_POST['depart']=='1'){
            if($_POST['crmmanager'] && $_POST['crmmanager'] != 'all'){
                $cwhere['crm_uid']  =   $_POST['crmmanager'];
            }else{
                $cwhere['PHPYUNBTWSTART_A']  =   '';
                $cwhere['crm_uid'][]         =  array('<>',0,'AND');
                $cwhere['crm_uid'][]         =  array('in',pylode(',',$uids),'AND');
                $cwhere['PHPYUNBTWEND_A']    =   '';
            }
        }else{
            $cwhere['crm_uid']      =   $_SESSION["auid"];
        }
        $cwhere['isfollow']     =   '0';
        if ($timeBegin != '') {
            $cwhere['crm_time'][]   =   array('>=', $timeBegin);
            $cwhere['crm_time'][]   =   array('<=', $timeEnd);
        }
        $cwhere['groupby']      =   'date';
        $cwhere['orderby']      =   'date';
        $comlist    =   $companyM -> getList($cwhere, array('field' => "count(uid) as `num`, from_unixtime(crm_time,'%Y-%m-%d') as `date`"));
        $row        =   $comlist['list'];
        $totalNew   =   0;
        if (count($row) < 1) {
            $new    =   array(
                'names'     =>  array(),
                'values'    =>  array()
            );
        } else {
            $newNames   =   array();
            $newValues  =   array();
            foreach ($row as $r) {
                $newNames[]     =   $r['date'];
                $newValues[]    =   $r['num'];
                $totalNew += $r['num'];
            }
            $new    =   array(
                'names'     =>  $newNames,
                'values'    =>  $newValues
            );
        }
        $names  =   array_merge($all['names'], $new['names']);
        $names  =   array_unique($names);
        sort($names);
        $allValues  =   $newValues  =   array();
        $allK       =   $newK       =   0;
        foreach ($names as $n) {
            if (in_array($n, $all['names'])) {
                $allValues[]    =   $all['values'][$allK];
                $allK ++;
            } else {
                $allValues[]    =   0;
            }
            if (in_array($n, $new['names'])) {
                $newValues[]    =   $new['values'][$newK];
                $newK ++;
            } else {
                $newValues[] = 0;
            }
            $attentionValues[] = end($allValues) - end($newValues);
        }
        $all['values']  =   $allValues;
        $new['values']  =   $newValues;
        $values[]   =   array(
            'name'          =>  '客户总数',
            'type'          =>  'line',
            'data'          =>  $all['values'],
            'stack'         =>  'total',
            'symbol'        =>  'circle',//拐点样式
            'symbolSize'    =>  10,
        );
        $values[]   =   array(
            'name'      =>  '未跟进',
            'type'      =>  'bar',
            'data'      =>  $new['values'],
            'stack'     =>  'total',
            'barWidth'  =>  '18'
        );
        $values[]   =   array(
            'name' =>   '已跟进',
            'type' =>   'bar',
            'data' =>   $attentionValues,
            'stack'     =>  'total',
            'barWidth'  =>  '18'
        );
        $tableData = $xAxis = $series = array();
        $xAxis = $names;
        $series = $values;
        foreach ($names as $k => $date) {
            $tableData[] = array(
                'name'=>$date,
                'cnum'=>$values[0]['data'][$k],
                'dnum'=>$values[1]['data'][$k],
                'ynum'=>$values[2]['data'][$k],
            );
        }
        $tableData[] = array(
            'name'=>'总计',
            'cnum'=>$totalAll,
            'dnum'=>$totalNew,
            'ynum'=>($totalAll - $totalNew),
        );
        $data['adminUserList'] = !empty($adminUserList)?$adminUserList:array();
        $data['tpldata'] =   $tpldata;
        $data['title'] = $title;
        $data['tableData']= $tableData;
        $data['xAxis'] = $xAxis;
        $data['series'] = $series;
        $this->render_json(0, '', $data);
    }

    /*
     * 业务员 我的业绩 跟进记录统计
     */
    function concern_action()
    {
        $uids = array();
        if($_POST['depart']=='1'){
            $admindata      =   $this->getAuser();
            $uids           =   $admindata['adminuids'];
        }
        if ($_POST['time'] || $_POST['stime'] || $_POST['etime']) {
            if ($_POST['time']) {
                if ($_POST['time'] == - 1) {
                    $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y'));
                    $timeEnd    =   time();
                    $title      =   "跟进记录统计 - 今天";
                } elseif ($_POST['time'] == 1) {
                    $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y')) - 86400;
                    $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y')) - 86400;
                    $title      =   "跟进记录统计 - 昨天";
                } else {
                    $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('Y')) - $_POST['time'] * 86400;
                    $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                    $title      =   "跟进记录统计 - {$_POST['time']}天内";
                }
            }
            if ($_POST['stime']) {
                $stime          =   explode('-', $_POST['stime']);
                $etime          =   explode('-', $_POST['etime']);
                $timeBegin      =   mktime(0, 0, 0, $stime[1], $stime[2], $stime[0]);
                $timeEnd        =   mktime(23, 59, 59, $etime[1], $etime[2], $etime[0]);
                $title          =   "跟进记录统计 - {$_POST['stime']}~{$_POST['etime']}";
            }
        } else {
            $title = "跟进记录统计 - 全部数据";
        }
        $names  =   $values =   $where  =   array();
        if($_POST['depart']=='1'){
            if($_POST['crmmanager'] && $_POST['crmmanager'] != 'all'){
                $where['auid']  =   $_POST['crmmanager'];
            }else{
                $where['auid']  =   array('in',pylode(',',$uids));
            }
        }else{
            $where['auid']  =   $_SESSION["auid"];
        }
        if ($timeBegin != '') {
            $where['atime'][]   =   array('>=', $timeBegin);
            $where['atime'][]   =   array('<=', $timeEnd);
        }
        $where['groupby']   =   'type';
        $where['orderby']   =   'type';
        $crmM   =   $this->MODEL('crm');
        $row    =   $crmM->getConcernList($where, array('field' => 'count(id) as `num`, `type`'));
        $total  =   0;
        foreach ($row as $r) {
            $names[]        =   $r['type'];
            $rr['value']    =   $r['num'];
            $rr['name']     =   $r['type'];
            $values[]       =   $rr;
            $total += $r['num'];
        }
        include (PLUS_PATH . "crm.cache.php");
        $tableData = $series = array();
        foreach ($values as $k => $v) {
            $tableData[] = array(
                'name'  =>  $crmclass_name[$v['name']],
                'value'   =>  floor($v['value'])
            );
        }
        $series = $tableData;
        $tableData[] = array(
            'name'=>'总计',
            'value'=>$total,
        );
        $data['title'] = $title;
        $data['tableData']= $tableData;
        $data['series'] = $series;
        $this->render_json(0, '', $data);
    }

    private function getAmountTotal($timeBegin = '', $timeEnd = '')
    {
        $uids = array();
        if($_POST['depart']=='1'){
            $admindata      =   $this->getAuser();
            $uids           =   $admindata['adminuids'];
        }
        $where  =   array();
        if($_POST['depart']=='1'){
            if($_POST['crmmanager'] && $_POST['crmmanager'] != 'all'){
                $where['crm_uid']   =   $_POST['crmmanager'];
            }else{
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['crm_uid'][]         =   array('<>',0,'AND');
                $where['crm_uid'][]         =   array('in',pylode(',',$uids),'AND');
                $where['PHPYUNBTWEND_A']    =   '';
            }
        }else{
            $where['crm_uid']       =   $_SESSION["auid"];
        }
        $where['order_state']   =   '2';
        if ($timeBegin != '') {
            $where['order_time'][]  =   array('>=', $timeBegin);
            $where['order_time'][]  =   array('<=', $timeEnd);
        }
        $companyorderM  =   $this->MODEL('companyorder');
        $row            =   $companyorderM->getList($where, array('field' => 'sum(order_price) as `num`'));
        $all            =   isset($row[0]['num']) && $row[0]['num'] > 0 ? $row[0]['num'] : 0;
        return array($all);
    }

    /*
     * 业务员 我的业绩 成交金额统计
     */
    public function amount_action()
    {
        $uids = array();
        if($_POST['depart']=='1'){
            $admindata      =   $this->getAuser();
            $uids           =   $admindata['adminuids'];
        }
        $data = array();
        list ($all) =   $this->getAmountTotal(strtotime(date('Y-m-01 00:00:00', time())), time());
        $month_num     =   $all;
        $timeEnd    =   strtotime(date('Y-m-d 00:00:00', time()));
        $week       = date('w');
        if ($week == 0)
            $week   =   7;
        $week --;
        $monday     =   strtotime("-{$week} day", $timeEnd);
        list ($all) =   $this->getAmountTotal($monday, $timeEnd);
        $week_num     =   $all;
        $all        =   $this->getAmountTotal($monday, $timeEnd);
        list ($all) =   $this->getAmountTotal(strtotime('-1 day', $timeEnd), $timeEnd);
        $yestoday_num     =   $all;
        list ($all) =   $this->getAmountTotal();
        $total_num     =   $all;
        if ($_POST['time'] || $_POST['stime'] || $_POST['etime']) {
            if ($_POST['time']) {
                if ($_POST['time'] == - 1) {
                    $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y'));
                    $timeEnd    =   time();
                    $title      =   "成交金额统计 - 今天";
                } elseif ($_POST['time'] == 1) {
                    $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('y')) - 86400;
                    $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y')) - 86400;
                    $title      =   "成交金额统计 - 昨天";
                } else {
                    $timeBegin  =   mktime(0, 0, 0, date('m'), date('d'), date('Y')) - $_POST['time'] * 86400;
                    $timeEnd    =   mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                    $title      =   "成交金额统计 - {$_POST['time']}天内";
                }
            }
            if ($_POST['stime']) {
                $stime          =   explode('-', $_POST['stime']);
                $etime          =   explode('-', $_POST['etime']);
                $timeBegin      =   mktime(0, 0, 0, $stime[1], $stime[2], $stime[0]);
                $timeEnd        =   mktime(23, 59, 59, $etime[1], $etime[2], $etime[0]);
                $title          =   "成交金额统计 - {$_POST['stime']}~{$_POST['etime']}";
            }

            list ($all) =   $this->getAmountTotal($timeBegin, $timeEnd);
            $total_num     =   $all;
        } else {
            $title = "成交金额统计 - 全部数据";
        }
        $names  =   $values =  array();
        $where  =   array();
        if($_POST['depart']=='1'){
            if($_POST['crmmanager'] && $_POST['crmmanager'] != 'all'){
                $where['crm_uid']   =   $_POST['crmmanager'];
            }else{
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['crm_uid'][]         =   array('<>',0,'AND');
                $where['crm_uid'][]         =   array('in',pylode(',',$uids),'AND');
                $where['PHPYUNBTWEND_A']    =   '';
            }
        }else{
            $where['crm_uid']       =   $_SESSION["auid"];
        }
        if ($timeBegin != '') {
            $where['order_time'][]  =   array('>=', $timeBegin);
            $where['order_time'][]  =   array('<=', $timeEnd);
        }
        $where['order_state']   =   '2';
        $where['groupby']   =   'date';
        $where['orderby']   =   'date';
        $companyorderM      =   $this->MODEL('companyorder');
        $row                =   $companyorderM->getList($where, array('field' => "sum(order_price) as `num`, from_unixtime(order_time,'%Y-%m-%d') as `date`"));
        $totalAll   =   0;
        if (count($row) < 1) {
            $in =   array(
                'names'     =>  array(),
                'values'    =>  array()
            );
        } else {
            $inNames    =   array();
            $inValues   =   array();
            foreach ($row as $r) {
                $inNames[]  =   $r['date'];
                $inValues[] =   $r['num'];
                $totalAll += $r['num'];
            }
            $in =   array(
                'names'     =>  $inNames,
                'values'    =>  $inValues
            );
        }
        $names  =   array_merge($in['names']);
        $names  =   array_unique($names);
        sort($names);
        $xAxis = $names;
        $inValues   =   array();
        $inK        =   0;
        foreach ($names as $n) {
            if (in_array($n, $in['names'])) {
                $inValues[] =   sprintf("%.2f",$in['values'][$inK]);
                $inK ++;
            } else {
                $inValues[] =   0;
            }
        }
        $in['values']       =   $inValues;
        $series             =   $inValues;
        $values[]   =   array(
            'name' =>   '成交金额',
            'type' =>   $this->chartType,
            'data' =>   $in['values']
        );
        $tableData = array();
        foreach ($names as $nk => $nv) {
            $tableData[] = array(
                'name'=>$nv,
                'value'=>$values[0]['data'][$nk]
            );
        }
        $tableData[] = array(
            'name'=>'成交金额',
            'value'=>sprintf("%.2f",$totalAll),
        );
        $data['month_num'] = $month_num;
        $data['week_num'] = $week_num;
        $data['yestoday_num'] = $yestoday_num;
        $data['total_num'] = $total_num;
        $data['title'] = $title;
        $data['tableData']= $tableData;
        $data['xAxis'] = $xAxis;
        $data['series'] = $series;
        $this->render_json(0, '', $data);
    }

    private function getAuser()
    {

        $adminUserList  =   $this->getAdminUser();
        $uids           =   array();
        foreach ($adminUserList as $v) {
            $uids[]     =   $v['uid'];
        }
        $data['adminuids']      =   $uids;
        $data['adminUserList']  =   $adminUserList;
        return $data;
    }
}
?>