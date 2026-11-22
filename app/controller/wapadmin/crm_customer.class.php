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

class crm_customer_controller extends adminCommon
{
    function index_action()
    {

        if (!empty($_POST)) {

            $comM   =   $this->MODEL('company');

            $where  =   array();

            $page   =   $_POST['page'];
            $limit  =   $_POST['limit'];

            $where['crm_uid']   =   0;
            if (trim($_POST['keyword'])) {

                $keywordStr                 = trim($_POST['keyword']);
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['name']              =   array('like', $keywordStr);
                $where['linktel']           =   array('like', $keywordStr, 'or');
                $where['PHPYUNBTWEND_A']    =   '';
                $data['keyword']            =   $keywordStr;
            }

            if (isset($_POST['crmType']) && !empty($_POST['crmType'])) {

                $crmType    =   intval($_POST['crmType']);
                $where['crm_type']  =   $crmType;
            }
            if (isset($_POST['crm_status']) && !empty($_POST['crm_status'])) {

                $crm_status =   intval($_POST['crm_status']);
                $where['crm_status']=   $crm_status;
            }
            if (isset($_POST['rating']) && $_POST['rating'] != '') {

                $rating =   intval($_POST['rating']);
                $where['rating']    =   $rating;
            }
            if (isset($_POST['vipetime']) && !empty($_POST['vipetime'])) {

                $etime  =   intval($_POST['vipetime']);
                if ($etime == 5) {

                    $where['PHPYUNBTWSTART_A'] = '';
                    $where['vipetime'][] = array('<', strtotime(date('Y-m-d')), 'AND');
                    $where['vipetime'][] = array('>', '0', 'AND');
                    $where['PHPYUNBTWEND_A'] = '';
                } else {
                    if ($etime == 1) {
                        $num = '+3 days';
                    } else if ($etime == 2) {
                        $num = '+7 days';
                    } elseif ($etime == 3) {
                        $num = '+15 days';
                    } elseif ($etime == 4) {
                        $num = '+1 month';
                    }
                    $where['PHPYUNBTWSTART_A'] = '';
                    $where['vipetime'][] = array('>', time(), 'AND');
                    $where['vipetime'][]    = array('<', strtotime($num), 'AND');
                    $where['PHPYUNBTWEND_A'] = '';
                }
            }
            if (!empty($_POST['releaseTime'])) {
                $releaseTime = intval($_POST['releaseTime']);
                if ($releaseTime != 6) {
                    if ($releaseTime == 1) {
                        $where['release_time'] = array('>=', strtotime(date('Y-m-d')));
                    } elseif ($releaseTime == 2) {
                        $where['PHPYUNBTWSTART_A'] = '';
                        $where['release_time'][] = array('>=', strtotime('yesterday'));
                        $where['release_time'][] = array('<', strtotime(date('Y-m-d')));
                        $where['PHPYUNBTWEND_A'] = '';
                    } elseif ($releaseTime == 3) {
                        $where['PHPYUNBTWSTART_A'] = '';
                        $where['release_time'][] = array('>=', strtotime('-3 days'));
                        $where['release_time'][] = array('<', time());
                        $where['PHPYUNBTWEND_A'] = '';
                    } elseif ($releaseTime == 4) {
                        $where['PHPYUNBTWSTART_A'] = '';
                        $where['release_time'][] = array('>=', strtotime('-7 days'));
                        $where['release_time'][] = array('<', time());
                        $where['PHPYUNBTWEND_A'] = '';
                    } elseif ($releaseTime == 5) {
                        $where['PHPYUNBTWSTART_A'] = '';
                        $where['release_time'][] = array('>=', strtotime('-30 days'));
                        $where['release_time'][] = array('<', time());
                        $where['PHPYUNBTWEND_A'] = '';
                    }
                }
                if ($_POST['releaseStart'] || $_POST['releaseEnd']) {
                    $where['PHPYUNBTWSTART_A'] = '';
                    $where['release_time'][] = array('>=', strtotime($_POST['releaseStart']), '');
                    $where['release_time'][] = array('<', strtotime($_POST['releaseEnd']) + 86400, '');
                    $where['PHPYUNBTWEND_A'] = '';
                }
            }
            $where['orderby']   =   'login_date,desc';
            if ($page) {

                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =   array($pagenav, $limit);
            } else {

                $where['limit'] =   $limit;
            }
            $list   =   $comM->getList($where, array('utype' => 'crm'));
            $data['list'] = count($list['list']) ? $list['list'] : [];
            $data['todayStart'] = strtotime('today');
            $data['sy_crm_link'] = $this->config['sy_crm_link'];
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $this->yuntpl(array('wapadmin/crm_customer'));
    }

    function statis_action(){
        $cacheM     =   $this->MODEL('cache');
        $crmCache   =   $cacheM->GetCache(array('crm'));
        $this->yunset(array('cache' => $crmCache, 'crmClassName' => $crmCache['crmclass_name'], 'crmStatus' => $crmCache['crmdata']['client_status'], 'crmType' => $crmCache['crmdata']['client_type']));

        $crmTypeArr = $crmStatusArr= $ratingarr= $edtimearr= $releasetimearr = $nextftimearr = array();
        if($crmCache['crmdata']['client_status']){
            foreach ($crmCache['crmdata']['client_status'] as $v){
                $crmStatusArr[] = array('id'=>$v,"name"=>$crmCache['crmclass_name'][$v]);
            }
        }
        if($crmCache['crmdata']['client_type']){
            foreach ($crmCache['crmdata']['client_type'] as $v){
                $crmTypeArr[] = array('id'=>$v,"name"=>$crmCache['crmclass_name'][$v]);
            }
        }
        $ratingM        =   $this -> MODEL('rating');

        if (!empty($this->config['com_rating'])) {

            $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`', 'rating' => $this->config['com_rating']));
        } else {

            $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
        }

        if (!empty($rating)) {
            $ratingarr  =   array();
            foreach ($rating as $k => $v) {

                $ratingarr[]    =   array("id" => $v['id'], "name" => $v['name']);
            }
        }
        $edtime         =   array('5' => '已到期', '1' => '三天内', '2' => '七天内', '3' => '十五天内', '4' => '一月内');
        foreach ($edtime as $k=>$v){
            $edtimearr[]=   array('id'=>$k,'name'=>$v);
        }
        $releaseTime=   array('1' => '今天', '2' => '昨天', '3' => '三天内', '4' => '七天内', '5' => '一月内', '6' => '自定义');
        foreach ($releaseTime as $k=>$v){
            $releasetimearr[] = array('id'=>$k,'name'=>$v);
        }
        $nextFtime  =   array('1' => '今天', '2' => '明天', '3' => '三天内', '4' => '七天内', '5' => '一月内');
        foreach ($nextFtime as $k=>$v){
            $nextftimearr[] = array('id'=>$k,'name'=>$v);
        }
        $data['ratingarr'] = $ratingarr;
        $data['crmStatusArr'] = $crmStatusArr;
        $data['crmTypeArr'] = $crmTypeArr;
        $data['edtimearr'] = $edtimearr;
        $data['releasetimearr'] = $releasetimearr;
        $data['nextftimearr'] = $nextftimearr;
        header('content-type:application/json; charset=utf-8');
        echo json_encode($data);die;
    }

    function claimCom_action()
    {
        if ($_POST['uids']) {

            $uids   =   @explode(',', $_POST['uids']);
            $wuid   =   intval($_SESSION['wuid']);
            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->claimCom($uids, $wuid);
            echo json_encode($return);
        }
    }
}

?>
