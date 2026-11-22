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

class crm_deal_controller extends adminCommon
{

    function index_action()
    {

        $orderM =   $this->MODEL('companyorder');
        $where  =   array('order_state' => 2);

        if (isset($_POST['tab']) && !empty($_POST['tab'])){
            $tabStr =   intval($_POST['tab']);
            if ($tabStr == 1){

                $where['crm_uid']   =   $_SESSION['auid'];
            }else if ($tabStr == 2){

                $adminUserList  =   $this->getAdminUser();
                $auidArr        =   array();
                foreach ($adminUserList as $av){
                    $auidArr[]  =   $av['uid'];
                }

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['crm_uid'][]         =   array('>', 0, '');
                $where['crm_uid'][]         =   array('in', pylode(',', $auidArr), '');
                $where['PHPYUNBTWEND_A']    =   '';
            }
        }

        if (isset($_POST['auid']) && !empty($_POST['auid'])){
            $where['crm_uid']   =   $_POST['auid'];
        }

        if (isset($_POST['orTime']) && !empty($_POST['orTime'])) {

            $start  =   date('Y-m-d', $_POST['orTime'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['orTime'][1] / 1000);

            $where['PHPYUNBTWSTART_B']  = '';
            $where['order_time'][]      = array('>=', strtotime($start . " 00:00:00"));
            $where['order_time'][]      = array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_B']    = '';
        }

        if (isset($_POST['oType']) && !empty($_POST['oType'])) {

            $where['type']  =   $_POST['oType'];
        }

        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {

            $TypeStr    =   intval($_POST['or_type']);
            $KeywordStr =   trim($_POST['keyword']);

            if ($TypeStr == 1) {

                $cWhere['name'] = array('like', $KeywordStr);
            } elseif ($TypeStr == 2) {

                $cWhere['linkman'] = array('like', $KeywordStr);
            }

            $comM   =   $this->MODEL('company');
            $comList=   $comM->getList($cWhere, array('field' => '`uid`'));
            foreach ($comList['list'] as $cv) {
                $uids[] =   $cv['uid'];
            }
            $where['uid'] = array('in', pylode(',', $uids));
        }

        $pageM  =   $this->MODEL('page');
        $perPage=   !empty($_POST['limit']) ? intval($_POST['limit']) : intval($this->config['sy_listnum']);
        $pages  =   $pageM->adminPageList('company_order', $where, $_POST['page'], array('limit' => $perPage));
        if ($pages['total'] > 0) {
            if ($_POST['order'] && $_POST['t']) {

                $where['orderby']   =   $_POST['t'].', '.$_POST['order'];
            } else {

                $where['orderby']   =   'id, DESC';
            }
            $where['limit'] = $pages['limit'];
            $orders = $orderM->getList($where, array('utype' => 'crmdeal'));
        }

        $data['list']   =   !empty($orders) ? $orders : array();
        $data['total']  =   $pages['total'];
        $data['page_sizes'] =   $pages['page_sizes'];
        $data['page_size']  =   $perPage;
        $this->render_json(0, '', $data);
    }

    function getStaticData_action()
    {

        include(CONFIG_PATH.'db.data.php');
        $data['orderTypeArr'] = $arr_data['ordertype'];

        if (isset($_POST['tab']) && intval($_POST['tab']) == 2) {
            $crmUser = $this->getAdminUser();
            $data['ausers'] = $crmUser;
        }

        $this->render_json(0, 'ok', $data);
    }

}

?>