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

class admin_right_controller extends adminCommon
{
    // 网站统计
    function getweb_action(){
        $this->tjl("member","login_log", array('reg_date','ctime'), array('个人注册','个人登录'), array('type'=>1));
    }

    function comtj_action(){
        $this->tjl("member","login_log", array('reg_date','ctime'), array('企业注册','企业登录'), array('type'=>2));
    }

    function tjlData($tablename, $usertype, $field, $days, $sdate, $edate)
    {
        $TongjiM = $this->MODEL('tongji');
        $where['usertype'] = $usertype;
        $field == 'reg_date' && $where['pid'] = 0;
        $where[$field][] = array(">=", $sdate);
        $where[$field][] = array("<=", $edate);
        $where['groupby'] = 'td';
        $where['orderby'] = array('td,desc');
        $statistics = $TongjiM->tjtotal($tablename, $where, array('field' => 'FROM_UNIXTIME(' . $field . ',"%Y%m%d") as td,count(*) as cnt'));

        $list = $dateList = array();

        if (is_array($statistics)) {
            $allNum = 0;

            foreach ($statistics as $key => $value) {
                $allNum += $value['cnt'];
                $dateList[$value['td']] = $value;
            }

            if ($days > 0) {
                for ($i = 0; $i <= $days; $i ++) {
                    $onday	=	date("Ymd", strtotime(' -' . $i . 'day', $edate));
                    $td		=	date('m-d', strtotime(' -' . $i . 'day', $edate));
                    $date	=	date('Y-m-d', strtotime(' -' . $i . 'day', $edate));

                    if (! empty($dateList[$onday])) {
                        $dateList[$onday]['td']		=	$td;
                        $dateList[$onday]['date']	=	$date;
                        $list[$onday]			=	$dateList[$onday];
                    } else {
                        $list[$onday]			=	array('td' => $td,'cnt' => 0,'date' => $date);
                    }
                }
            }
        }
        ksort($list);

        return array('count' => $allNum, 'list' => $list);
    }

    function tjl($tablename1,$tablename2, $field = array(), $name = array(), $data=array()){
        // 拦截没有首页查看统计权限的请求
        if ($this->adminShell['index_lookstatistc'] != 2) {
            $list = array();
            $name = array();
        } else {
            $TimeDate       =       $this->day();
            $sdate          =       $TimeDate['sdate'];
            $edate          =       $TimeDate['edate'];
            $days           =       $TimeDate['days'];
            if ($field[0]) {
                $list[] = $this->tjlData($tablename1, $data['type'], $field[0], $days, strtotime($sdate), strtotime($edate . ' 23:59:59'));
            }
            if ($field[1]) {
                $list[] = $this->tjlData($tablename2, $data['type'], $field[1], $days, strtotime($sdate), strtotime($edate . ' 23:59:59'));
            }
        }
        $this->yunset('list', json_encode($list, JSON_UNESCAPED_UNICODE));
        $this->yunset('name', json_encode($name, JSON_UNESCAPED_UNICODE));
        $this->yuntpl(array('wapadmin/index_tongji'));
    }

    function ujobtj_action(){
        $this->tj("userid_job", array('datetime'), array('简历投递'));
    }
    function yqmstj_action(){
        $this->tj("userid_msg", array('datetime'), array('邀请面试'));
    }
    function downresumetj_action(){
        $this->tj("down_resume", array('downtime'), array('简历下载'));
    }
    function resumetj_action(){
        $this->tj("resume_expect", array('ctime','r_time'), array('简历新增','简历刷新'));
    }
    function newstj_action(){
        $this->tj("news_base", array('datetime'), array('新闻新增'));
    }
    function adtj_action(){
        $this->tj("adclick", array('addtime'), array('广告点击'));
    }
    function jobtj_action(){
        $this->tj("company_job", array('sdate','r_time'), array('职位新增','职位刷新'));
    }
    function wzptj_action(){
        $this->tj("once_job", array('ctime','sxtime'), array('店铺招聘新增','店铺招聘刷新'));
    }
    function wjltj_action(){
        $this->tj("resume_tiny", array('time','lastupdate'), array('普工简历新增','普工简历刷新'));
    }
    function payordertj_action(){
        $this->tj("company_order", array('order_time'), array('充值统计'));
    }
    function chattj_action(){
        $this->tj("chat_log", array('sendTime'), array('聊天统计'));
    }

    function tjData($tablename, $field, $days, $sdate, $edate)
    {
        $TongjiM = $this->MODEL('tongji');

        $where[$field][]	=	array(">=", $sdate);
        $where[$field][]	=	array("<=", $edate);
        $where['groupby']	=	'td';
        $where['orderby']	=	array('td,desc');

        if($tablename == 'chat_log'){
            $field = "{$field}/1000";
        }
        $statistics = $TongjiM->tjtotal($tablename, $where, array('field' => 'FROM_UNIXTIME(' . $field . ',"%Y%m%d") as td,count(*) as cnt'));

        $list = $dateList = array();

        if (is_array($statistics)) {
            $allNum = 0;

            foreach ($statistics as $key => $value) {
                $allNum += $value['cnt'];
                $dateList[$value['td']] = $value;
            }

            if ($days > 0) {
                for ($i = 0; $i <= $days; $i ++) {
                    $onday	=	date("Ymd", strtotime(' -' . $i . 'day', $edate));
                    $td		=	date('m-d', strtotime(' -' . $i . 'day', $edate));
                    $date	=	date('Y-m-d', strtotime(' -' . $i . 'day', $edate));

                    if (! empty($dateList[$onday])) {
                        $dateList[$onday]['td']		=	$td;
                        $dateList[$onday]['date']	=	$date;
                        $list[$onday]			=	$dateList[$onday];
                    } else {
                        $list[$onday] = array('td' => $td,'cnt' => 0,'date' => $date);
                    }
                }
            }
        }
        ksort($list);

        return array('count' => $allNum, 'list' => $list);
    }

    function tj($tablename, $field = array(), $name = array(), $where = ''){
        // 拦截没有首页查看统计权限的请求
        if ($this->adminShell['index_lookstatistc'] != 2) {
            $list = array();
            $name = array();
        } else {
            $TimeDate	=	$this->day();
            $sdate		=	$TimeDate['sdate'];
            $edate		=	$TimeDate['edate'];
            $days		=	$TimeDate['days'];

            if ($field[0]) {
                $list[] = $this->tjData($tablename, $field[0], $days, strtotime($sdate), strtotime($edate . ' 23:59:59'));
            }
            if ($field[1]) {
                if ($tablename == 'resume_expect'){
                    $tablename              =   'resume_refresh_log';
                }
                if ($tablename == 'company_job'){
                    $tablename              =   'job_refresh_log';
                    $loginWhere['type']     =   1;
                }

                $list[] = $this->tjData($tablename, $field[1], $days, strtotime($sdate), strtotime($edate . ' 23:59:59'));
            }
        }
        $this->yunset('list', json_encode($list, JSON_UNESCAPED_UNICODE));
        $this->yunset('name', json_encode($name, JSON_UNESCAPED_UNICODE));
        $this->yuntpl(array('wapadmin/index_tongji'));
    }

    // 时间获取
    function day(){

        if ((int) $_GET['days'] > 0) {

            $days	=	(int) $_GET['days'];
            $sdate	=	date('Y-m-d', (time() - $days * 86400));
            $edate	=	date('Y-m-d');

        } elseif ($_GET['sdate']) {

            $sdate	=	$_GET['sdate'];
            $days	=	ceil(abs(time() - strtotime($sdate)) / 86400);

            if ($_GET['edate']) {

                $edate  =	$_GET['edate'];
                $days	=	ceil(abs(strtotime($edate) - strtotime($sdate)) / 86400);
            }
        } else {

            $days	=	intval(date('d')) - 1;

            $days	=	$days == 0 ? 1 : $days;

            $sdate	=	date('Y-m-d', (time() - $days * 86400));
            $edate	=	date('Y-m-d');
        }
        return array('sdate' => $sdate,'days' => $days,'edate' => $edate);
    }
}
?>