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

class admin_workwx_list_controller extends adminCommon
{

    function index_action()
    {

        $where  =   array();

        $keywordStr =   trim($_GET['keyword']);
        if (!empty($keywordStr)) {

            $where['userid']    =   array('like', trim($_GET['keyword']));
            $urlarr['keyword']  =   $_GET['keyword'];
        }
        if ($_GET['userid']) {

            $where['userid']    =   $_GET['userid'];
            $urlarr['userid']   =   $_GET['userid'];
        }
        $urlarr         =   $_GET;
        $urlarr['page'] =   "{{page}}";

        $pageurl    =   Url($_GET['m'], $urlarr, 'admin');

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('work_user', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   array('create_time,asc');
            }

            $where['limit'] =   $pages['limit'];

            $workWxM    =   $this->MODEL('workwx');

            $userList   =   $workWxM->getWorkUserIdList($where);

            $this->yunset('list', $userList);
        }

        $this->yuntpl(array('admin/work/user_list'));
    }

    /**
     * @desc 企业员工数量
     */
    function userNum_action()
    {

        $workM  =   $this->MODEL('workwx');

        echo $workM->userNum();
    }

    /**
     * 成员详情
     */
    function info_action()
    {

        $workM  =   $this->MODEL('workwx');

        $userId =   $_GET['id'];

        $start_time =   null;
        $end_time   =   null;

        if ($_GET['time']) {

            $timeArr    =   explode('~', $_GET['time']);
            $end_time   =   strtotime($timeArr[1]);
            $end_time   =   $end_time > strtotime(date('Y-m-d')) ? strtotime(date('Y-m-d')) : $end_time;
            $minStart   =   strtotime('-29 day', $end_time);
            $start_time =   strtotime($timeArr[0]);
            $start_time =   $start_time < $minStart ? $minStart : $start_time;
        }

        $data   =   $workM->getUserBehaviorData($userId, $start_time, $end_time);

        $this->yunset($data);
        $this->yunset(array('id' => $userId));

        $this->yuntpl(array('admin/work/user_info'));
    }

    /**
     * @desc   获取配置了员工客户
     */
    public function syncWorkUserId_action()
    {

        $workWxM = $this->MODEL('workwx');

        $return = $workWxM->syncWorkUserId(array('cursor' => $_POST['cursor'], 'limit' => $_POST['limit']));

        echo json_encode($return);

        exit();;
    }

    /**
     * @desc 批量获取客户详情
     */
    public function syncCustomer_action()
    {

        $workWxM = $this->MODEL('workwx');

        $return = $workWxM->syncCustomerList(array('cursor' => $_POST['cursor'], 'limit' => $_POST['limit'], 'userid' => $_POST['id']));

        echo json_encode($return);

        exit();;
    }

    /**
     * @desc 员工授权二维码（指向 构建网页授权 页面）
     */
    public function getUrlCode_action()
    {

        include_once LIB_PATH."yunqrcode.class.php";
        $url    =   $this->config['sy_weburl'].'/wap/index.php?c=work&a=auth';
        YunQrcode::generatePng2($url, 4);
    }

}

?>