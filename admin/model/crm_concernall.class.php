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

class crm_concernall_controller extends adminCommon
{

    function index_action()
    {

        $crmM   =   $this->MODEL('crm');
        $where  =   array();

        if (isset($_GET['uid']) && !empty($_GET['uid'])) {

            $where['auid'] = $_GET['uid'];
            $urlarr['uid'] = $_GET['uid'];
        }

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
                $where['ftime'][]   =   array('>', $startTime, '');
                $where['ftime'][]   =   array('<', $endTime, '');
                $where['PHPYUNBTWEND_A']    =   '';
            } elseif (!empty($startTime)) {

                $where['ftime'] =   array('>', $startTime);
            } elseif (!empty($endTime)) {

                $where['ftime'] =   array('<', $endTime);
            }

            $urlarr['day']  =   $_GET['day'];
        }

        if (isset($_GET['linktype']) && !empty($_GET['linktype'])) {

            $where['type']      =   $_GET['linktype'];
            $urlarr['linktype'] =   $_GET['linktype'];
        }

        if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {

            $keywordStr =   trim($_GET['keyword']);
            $comM       =   $this->MODEL('company');
            $companyA   =   $comM->getList(array('name' => array('like', $keywordStr)), array('field' => 'uid'));

            $uids       =   array();
            foreach ($companyA['list'] as $ck => $cv) {
                $uids[] =   $cv['uid'];
            }

            if (!empty($uids)) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['uid']       =   array('in', pylode(',', $uids), '');
                $where['content']   =   array('like', $keywordStr, 'OR');
                $where['PHPYUNBTWEND_A']    =   '';
            } else {

                $where['content']   =   array('like', $keywordStr);
            }
            $urlarr['keyword']      =   $_GET['keyword'];
        }

        $urlarr         = $_GET;
        $urlarr['page'] = '{{page}}';

        $pageurl    =   Url($_GET['m'], $urlarr, 'admin');
        $pageM      =   $this->MODEL('page');
        $pages      =   $pageM->pageList('crmnew_concern', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   'atime';
            }

            $where['limit']         =   $pages['limit'];

            $list = $crmM->getConcernList($where, array('utype' => 'crm'));
            $this->yunset(array('rows' => $list));
        }

        $adminM =   $this->MODEL('admin');
        $auser  =   $adminM->getList(array(), array('field' => '`uid`,`name`'));
        $this->yunset('auser', $auser);

        $cacheM =   $this->MODEL('cache');
        $cache  =   $cacheM->GetCache(array('crm'));
        $this->yunset('cache', $cache);

        $this->yunset('contactOssUrl', $this->config['sy_outcall_oss']);

        $this->yuntpl(array('admin/crm_concernall'));
    }

    function del_action()
    {

        $this->check_token();

        $crmM   =   $this->Model('crm');
        $delID  =   $_GET['id'] ? intval($_GET['id']) : $_GET['del'];
        $err    =   $crmM->delConcern($delID);
        $this->layer_msg($err['msg'], $err['errcode'], $err['layertype'], $_SERVER['HTTP_REFERER'], 2, 1);
    }
}

?>