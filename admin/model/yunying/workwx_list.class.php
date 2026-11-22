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

class workwx_list_controller extends adminCommon
{
    /*
     * 企业微信 员工列表
     */
    function index_action()
    {
        $where = array();
        $keywordStr = trim($_POST['keyword']);
        if (!empty($keywordStr)) {
            $where['userid'] = array('like', trim($_POST['keyword']));
        }
        if ($_POST['userid']) {
            $where['userid'] = $_POST['userid'];
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('work_user', $where, $page, array('limit' => $pageSize));
        if ($pages['total'] > 0) {
            if ($_GET['order']) {
                $where['orderby'] = $_POST['t'].','.$_POST['order'];
            } else {
                $where['orderby'] = array('create_time,asc');
            }
            $where['limit'] = $pages['limit'];
            $workWxM = $this->MODEL('workwx');
            $userList = $workWxM->getWorkUserIdList($where);
        }
        $data['list'] = $userList ? $userList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }

    function index_base_data_action()
    {
        //企业员工数量
        $workM = $this->MODEL('workwx');
        $data['numArr'] = json_decode($workM->userNum(), 1);
        $this->render_json(0,'ok', $data);
    }
    /**
     * 成员详情
     */
    function info_action()
    {
        $workM = $this->MODEL('workwx');
        $userId = $_POST['id'];
        $start_time = null;
        $end_time = null;
        if ($_POST['time']) {
            $timeArr = explode('~', $_POST['time']);
            $end_time = strtotime($timeArr[1]);
            $end_time = $end_time > strtotime(date('Y-m-d')) ? strtotime(date('Y-m-d')) : $end_time;
            $minStart = strtotime('-29 day', $end_time);
            $start_time = strtotime($timeArr[0]);
            $start_time = $start_time < $minStart ? $minStart : $start_time;
        }
        $data = $workM->getUserBehaviorData($userId, $start_time, $end_time);
        $this->render_json(0, '', $data);
    }
    /**
     * @desc   获取配置了员工客户
     */
    public function syncWorkUserId_action()
    {
        $workWxM = $this->MODEL('workwx');
        $return = $workWxM->syncWorkUserId(array('cursor' => $_POST['cursor'], 'limit' => $_POST['limit']));
        echo json_encode($return);
        exit();
    }
    /**
     * @desc 批量获取客户详情
     */
    public function syncCustomer_action()
    {
        $workWxM = $this->MODEL('workwx');
        $return = $workWxM->syncCustomerList(array('cursor' => $_POST['cursor'], 'limit' => $_POST['limit'], 'userid' => $_POST['id']));
        echo json_encode($return);
        exit();
    }
    /**
     * @desc 员工授权二维码（指向 构建网页授权 页面）
     */
    public function getUrlCode_action()
    {
        include_once LIB_PATH."yunqrcode.class.php";
        $url = $this->config['sy_weburl'].'/wap/index.php?c=work&a=auth';
        YunQrcode::generatePng2($url, 4);
    }
}
?>