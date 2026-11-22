<?php

/**
 * Class dataBoard_controller
 *
 * @author shiy
 * @version 7.0
 */

class dataBoard_controller extends adminCommon
{

    public function index_action()
    {

        $crmM   =   $this->MODEL('crm');

        $where['is_crm']=   1;

        $where['PHPYUNBTWSTART_A']  =   '';
        $where['did'][] =   array('=', 0, '');
        $where['did'][] =   array('isnull', '', 'or');
        $where['PHPYUNBTWEND_A']    =   '';

        if (isset($_POST['order'])) {

            $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
        } else {

            $where['orderby']   =   'uid,desc';
        }

        $list   =   $crmM->getCrmList($where);

        $dataArr=   array();

        $data   =   array('list'  =>  $list, 'dataArr' => $dataArr);
        $this->render_json(0, '', $data);
    }

    public function getCrmData_action()
    {


        $crmM   =   $this->MODEL('crm');

        $return =   $crmM->getManageData();

        $this->render_json(0, '', $return);

    }

    /*
     * 数据看板 数据汇总、数据提醒
     */
    public function getCrmDataV2_action()
    {
        $crmM = $this->MODEL('crm');
        $hzdata = $crmM->getManageDataV2();
        $dgjdata = $this->dgj();
        $this->render_json(0, '', array_merge($hzdata, $dgjdata));
    }

    // 待跟进汇总
    private function dgj()
    {
        $today = strtotime('today'); // 今天
        $week = strtotime('-6 day', $today); // 取一周时间（包含当天）
        $month = strtotime('-1 month +1 day', $today); // 取一个月前的时间（包含当天）

        $companyM = $this->MODEL('company');

        $data['gyx_cycle'] = $this->config['crm_jobtai_gyx_cycle'] ? intval($this->config['crm_jobtai_gyx_cycle']) : 3; // 获取工作台数据周期 - 高意向
        $data['hy_cycle'] = $this->config['crm_jobtai_hy_cycle'] ? intval($this->config['crm_jobtai_hy_cycle']) : 3; // 获取工作台数据周期 - 活跃
        $data['jgq_cycle'] = $this->config['crm_jobtai_jgq_cycle'] ? intval($this->config['crm_jobtai_jgq_cycle']) : 3; // 获取工作台数据周期 - 将过期
        $data['qz_cycle'] = $this->config['crm_jobtai_qz_cycle'] ? intval($this->config['crm_jobtai_qz_cycle']) : 3; // 获取工作台数据周期 - 潜在

        $comWhere = "crm_uid > 0 AND (isfollow = 0 OR (isfollow = 1 AND f_time < FTIME))";

        // crm设置 成交会员
        $rating = $this->MODEL('rating');
        $ratings = $rating->getList(array('category' => 1, 'orderby' => array('sort,desc')));
        $cjRatingIds = array();// 成交会员id
        $fcjRatingIds = array();// 非成交会员id
        if ($this->config['crm_deal_rating']) {
            $cjRatingIds = explode(',', $this->config['crm_deal_rating']);
        }
        foreach ($ratings as $v) {
            !in_array($v['id'], $cjRatingIds) && array_push($fcjRatingIds, $v['id']);
        }
        // 高意向用户
        // 非成交会员、触发权益提醒、7天未跟进
        // 高意向,X天内未跟进的企业
        $gyx_cycle = $data['gyx_cycle'] - 1;
        $startTimeGyx = strtotime("-{$gyx_cycle} day", $today); // 高意向
        $companyListGyx = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeGyx, $comWhere), 'uid,login_date,rating,vipetime');
        $companyUidGyxArr = array(); // 高意向 非成交会员
        $companyUidGyxDqHyArr = array(); // 到期且活跃
        if ($companyListGyx) {
            foreach ($companyListGyx as $ckey => $cval) {
                if (!in_array($cval['rating'], $cjRatingIds)) { // 高意向 非成交会员
                    $companyUidGyxArr[] = $cval['uid'];
                }
                if ($cval['login_date'] >= $week && $cval['vipetime'] && $cval['vipetime'] < $today && in_array($cval['rating'], $cjRatingIds)) { // 会员等级在CRM设置的成交会员等级中 过期 且1周内有登录
                    $companyUidGyxDqHyArr[] = $cval['uid'];
                }
            }
        }
        // 高意向用户 X天内未跟进 +（非成交会员，触发权益提醒 OR 7天内有登录，历史成交过，但未续费的客户）
        $gyxUidArr = array();
        if ($companyUidGyxArr) {
            $gyxList = $this->obj->select_all('rating_notice', array(
                'crm_uid' => array('>', 0),
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
            ), 'uid,count(uid) as num');// 7天内有登录，历史成交过，但未续费的客户
            $gyxUidArr = $gyxOrderList ? array_unique(array_merge($gyxUidArr, array_column($gyxOrderList, 'uid'))) : $gyxUidArr;
        }
        $data['gyx_num'] = $gyxUidArr ? count($gyxUidArr) : 0;

        // 潜在用户
        // 潜在,X天内未跟进的企业
        $qz_cycle = $data['qz_cycle'] - 1;
        $startTimeQz = strtotime("-{$qz_cycle} day", $today); // 潜在
        $companyListQz = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeQz, $comWhere), 'uid,login_date,rating,vipetime');
        $companyUidQzArr = array(); // 一个月未登录的企业（长期未登录）
        if ($companyListQz) {
            foreach ($companyListQz as $ckey => $cval) {
                if ($cval['login_date'] < $month) {
                    if (!in_array($cval['rating'], $cjRatingIds)) { // 非成交会员，即为未开通会员
                        $companyUidQzArr[] = $cval['uid'];
                    } elseif ($cval['vipetime'] && $cval['vipetime'] < $today) { // 会员过期
                        if ($cjRatingIds && in_array($cval['rating'], $cjRatingIds)) { // 会员等级在CRM设置的成交会员等级中
                            $companyUidQzArr[] = $cval['uid'];
                        }
                    }
                }
            }
        }
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
        $hy_cycle = $data['hy_cycle'] - 1;
        $startTimeHy = strtotime("-{$hy_cycle} day", $today); // 活跃
        $companyListHy = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeHy, $comWhere), 'uid,login_date,rating,vipetime');
        $companyUidHyArr = array(); // 一周内登录过的（近期登录），未开通会员或会员已过期
        if ($companyListHy) {
            foreach ($companyListHy as $ckey => $cval) {
                if ($cval['login_date'] >= $week) {
                    if (!in_array($cval['rating'], $cjRatingIds)) { // 非成交会员，即为未开通会员
                        $companyUidHyArr[] = $cval['uid'];
                    } elseif ($cval['vipetime'] && $cval['vipetime'] < $today && in_array($cval['rating'], $cjRatingIds)) { // 会员过期 并且会员等级在CRM设置的成交会员等级中
                        $companyUidHyArr[] = $cval['uid'];
                    }
                }
            }
        }
        $data['hy_num'] = count($companyUidHyArr);
        // 急需跟进
        // 即将超期被释放
        $releaseSet = $this->obj->select_all('crmset');
        $jxgjUidArr = array();
        if ($releaseSet) {
            $companyListJxgj = $companyM->getChCompanyList(array(
                'crm_uid' => array('>', 0),
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
        $planSql = "SELECT c.comid uid FROM {$this->def}company a JOIN {$this->def}crm_work_plan c ON a.uid = c.comid WHERE a.`crm_uid` > 0 AND c.auid > 0 AND c.stime < " .strtotime('+1 day', $today) . " AND c.type = 22 AND c.status = 1 GROUP BY c.comid";
        $planList = $this->obj->DB_query_all($planSql, 'all');
        $planUidArr = $planList ? array_column($planList, 'uid') : array();
        $planUidArr && $jxgjUidArr = array_unique(array_merge($jxgjUidArr, $planUidArr));
        $data['jxgj_num'] = $jxgjUidArr ? count($jxgjUidArr) : 0;

        // 到期用户
        $jgq_cycle = $data['jgq_cycle'] - 1;
        $startTimeJgq = strtotime("-{$jgq_cycle} day", $today); // 将过期
        $companyListJgq = $this->obj->DB_select_all('company', str_replace('FTIME', $startTimeJgq, $comWhere), 'uid,login_date,rating,vipetime');
        if ($companyListJgq) {
            foreach ($companyListJgq as $ckey => $cval) {
                if (in_array($cval['rating'], $cjRatingIds) && $cval['vipetime'] && $cval['vipetime'] < $today) { // 到期
                    $companyUidDqArr[] = $cval['uid'];
                }
                if ($cval['vipetime'] && $cval['vipetime'] >= $today && $cval['vipetime'] < strtotime("+15 day", $today)) { // 将到期
                    if ($cjRatingIds && in_array($cval['rating'], $cjRatingIds)) { // 会员等级在CRM设置的成交会员等级中
                        $companyUidJdqArr[] = $cval['uid'];
                    }
                }
            }
        }

        $data['dq_num'] = 0;
        if ($companyUidDqArr) {
            $orderList = $this->obj->select_all('company_order', array(
                'uid' => array('in', pylode(',', $companyUidDqArr)),
                'type' => 1,
                'order_state' => 2,
                'groupby' => 'uid'
            ), 'uid');
            $data['dq_num'] = $orderList ? count($orderList) : 0; // X天未跟进，历史成交过，但未续费的客户
        }

        // 待续费，将到期
        $data['gj_dxf_num'] = count($companyUidJdqArr);
        array_walk_recursive($data, function(&$val, $key) {
            $val = intval($val);
        });

        return $data;
    }

    /*
     * 数据看板 业务报表
     */
    public function getYwData_action()
    {
        $crmM = $this->MODEL('crm');
        if (!$_POST['y_m']) {
            $start = strtotime(date('Y-m-01 00:00:00', time()));// 本月开始时间戳
            $end = strtotime(date('Y-m-01 23:59:59', strtotime('+1 month'))) - 86400;// 本月结束时间戳
        } else {
            $start = strtotime($_POST['y_m'] . '-01 00:00:00');// 计算传入年月的开始时间戳
            $end = strtotime(date('Y-m-01 23:59:59', $start + 31 * 86400)) - 86400;// 计算传入年月的结束时间戳
        }
        $return = $crmM->getCrmUserYwData($start, $end);
        $this->render_json(0, '', $return);
    }
    /*
     * 数据看板 效率报表
     */
    public function getXlData_action()
    {
        $crmM = $this->MODEL('crm');
        $workstart = '07:00';
        $workend = '22:00';
        if ($this->config['crm_work_time']) {
            $worktime = explode('~', $this->config['crm_work_time']);
            $workstart = $worktime[0];
            $workend = $worktime[1];
        }
        if (!$_POST['y_m_d']) {
            $start = strtotime(date('Y-m-d ' . $workstart . ':00', time()));// 当天开始时间戳
            $end = strtotime(date('Y-m-d ' . $workend . ':00', time()));// 当天结束时间戳
        } else {
            $start = strtotime($_POST['y_m_d'] . ' ' . $workstart . ':00');// 计算传入日期的开始时间戳
            $end = strtotime($_POST['y_m_d'] . ' ' . $workend . ':00');// 计算传入日期的结束时间戳
        }
        $return = $crmM->getCrmUserXlData($start, $end);
        $this->render_json(0, '', $return);
    }

    /*
     * 数据看板 效率报表 详情图表
     */
    public function dtlchat_action(){
        $crmM = $this->MODEL('crm');
        $data = $crmM->getCrmUserGjChatData($_POST['uid'], $_POST['day']);
        $data1 = $crmM->getCrmUserGjCntData($_POST['uid'], $_POST['day']);
        $cntnum = array();
        $cntkeys = array();
        foreach ($data1 as $v) {
            $cntkeys[] = $v['name'] ? $v['name'] : '';
            $cntnum[] = $v['num'] ? $v['num'] : 0;
        }
        $this->render_json(0, '', array('qxt_keys' => array_keys($data), 'qxt_val' => array_values($data), 'cnt_keys' => $cntkeys, 'cnt_val' => $cntnum));
    }

    /*
     * 数据看板 效率报表 轨迹动态
     */
    public function linedata_action(){
        $crmM = $this->MODEL('crm');
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        if(intval($_POST['uid'])){
            $where['uid'] = $_POST['uid'];
        }
        $start = strtotime($_POST['time'] . ' 00:00:00');
        $end = strtotime($_POST['time'] . ' 23:59:59');
        if ($start && $end) {
            $where['PHPYUNBTWSTART'] = '';
            $where['time'][] = array('>=', $start);
            $where['time'][] = array('<=', $end);
            $where['PHPYUNBTWEND'] = '';
        }
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('admin_web_log', $where, $page, array('limit' => $pageSize));
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            $where['orderby'] =	'time,desc';
            $where['limit'] = $pages['limit'];
            $List = $crmM->getGjLineData($where);
            unset($where['limit']);
        }
        $rt['list'] = $List ? $List : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }
}