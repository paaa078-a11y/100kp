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
class crm_releaselog_controller extends adminCommon
{

    /**
     * @desc 释放记录；
     */
    function index_action()
    {

        $crmM   =   $this->MODEL('crm');

        $where  =   array();

        if (isset($_GET['day']) && !empty($_GET['day'])){

            $day            =   intval($_GET['day']);

            $startTime      =   $endTime    =   '';

            if ($day == 1) {        //  今天

                $startTime  =   strtotime('today');
                $endTime    =   time();
            } else if ($day == 2) { //  昨天

                $startTime  =   strtotime('today') - 86400;
                $endTime    =   strtotime('today');
            } else if ($day == 3) { //  本周

                $startTime  =   strtotime(date('Y-m-d', strtotime("this week Monday", time())));
                $endTime    =   strtotime(date('Y-m-d', strtotime("this week Sunday", time()))) + 24 * 3600 - 1;
            } else if ($day == 4) { //本月

                $startTime  =   mktime(0, 0, 0, date('m'), 1, date('Y'));
                $endTime    =   mktime(23, 59, 59, date('m'), date('t'), date('Y'));
            } else if ($day == 5){

                if (isset($_GET['stime']) && !empty($_GET['etime'])) {
                    $startTime  =   strtotime($_GET['stime']);
                    $urlarr['stime']    =   $_GET['stime'];
                }
                if (isset($_GET['etime']) && !empty($_GET['etime'])) {
                    $endTime    =   strtotime($_GET['etime']) + 86400;
                    $urlarr['etime']    =   $_GET['etime'];
                }
            }

            if (!empty($startTime) && !empty($endTime)) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['ctime'][]   =   array('>', $startTime, '');
                $where['ctime'][]   =   array('<', $endTime, '');
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif (!empty($startTime)) {

                $where['ctime'] =   array('>', $startTime);
            } elseif (!empty($endTime)) {

                $where['ctime'] =   array('<', $endTime);
            }

            $urlarr['day']  =   $_GET['day'];
        }

        if ($_GET['uid']) {

            $where['auid']  =   $_GET['uid'];
            $urlarr['uid']  =   $_GET['uid'];
        }

        if ($_GET['keyword']) {

            $comM       =   $this->MODEL('company');
            $companyA   =   $comM->getList(array('name' => array('like', trim($_GET['keyword']))), array('field' => 'uid'));

            if (!empty($companyA['list'])) {
                $uids   =   array();
                foreach ($companyA['list'] as $ck => $cv) {
                    $uids[]     =   $cv['uid'];
                }

                $where['uid']   =   array('in', pylode(',', $uids));
            }
        }

        $urlarr         =   $_GET;
        $urlarr['page'] =   '{{page}}';
        $pageurl=   Url($_GET['m'], $urlarr, 'admin');
        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('crm_release_log', $where, $pageurl, $_GET['page']);
        if ($pages['total'] > 0) {
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   array('ctime,desc','id,desc');
            }
            $where['limit']         =   $pages['limit'];

            $list   =   $crmM->getReleaseLogList($where);

            $this->yunset(array('tasks' => $list));
        }

        $adminM =   $this->MODEL('admin');
        $auser  =   $adminM->getList(array(), array('field' => '`uid`,`name`'));
        $this->yunset('auser', $auser);

        $this->yuntpl(array('admin/crm_releaselog'));
    }

    /**
     * 删除释放记录
     */
    function del_action()
    {

        $this->check_token();

        $crmM   =   $this->MODEL('crm');
        $return =    $crmM->delReleaseLog((int)$_GET['id']);
        $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }

}

?>