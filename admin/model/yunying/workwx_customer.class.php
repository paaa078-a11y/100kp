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

class workwx_customer_controller extends adminCommon
{
    /*
     * 企业微信 客户列表
     */
    function index_action()
    {
        $where = array();
        $keywordStr = trim($_POST['keyword']);
        if (isset($_POST['keyword']) && !empty($keywordStr)) {
            $where['name'] = array('like', base64_encode($keywordStr));
        }
        if (isset($_POST['userid'])) {
            $where['userid'] = $_POST['userid'];
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('work_customer', $where, $page, array('limit' => $pageSize));
        if($pages['total'] > 0){
            if($_POST['order']){
                $where['orderby']   =   $_POST['t'].','.$_POST['order'];
            }else{
                $where['orderby']   =  array('createtime, desc');
            }
            $where['limit'] = $pages['limit'];
            $workWxM = $this->MODEL('workwx');
            $customerList = $workWxM->getWorkCustomerList($where);
        }
        $data['list'] = $customerList ? $customerList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }

    function index_base_data_action()
    {
        //客户数量统计
        $workM = $this->MODEL('workwx');
        $data['numArr'] = json_decode($workM->customerNum(), 1);
        $this->render_json(0,'ok', $data);
    }

    function info_action()
    {
        if ($_POST['userid']){
            $workM = $this->MODEL('workwx');
            $customerInfo = $workM->getWorkCustomerInfo(array('external_userid' => $_POST['userid']));
        }
        $this->render_json(0, '', $customerInfo ? $customerInfo : array());
    }

    /**
     * @desc 批量获取客户详情
     */
    public function syncCustomer_action()
    {
        $workWxM = $this->MODEL('workwx');
        $return = $workWxM->syncCustomerList(array('cursor' => $_POST['cursor'], 'page' => $_POST['page'], 'limit' => $_POST['limit']));
        echo json_encode($return);
        exit();
    }
}
?>