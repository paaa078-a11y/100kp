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
class ad_order_controller extends adminCommon
{
    /**
     * 广告订单
     */
    function index_action()
    {
        $pageM = $this->MODEL('page');
        $adM = $this->MODEL('ad');
        $userInfoM = $this->MODEL('userinfo');
        $companyM = $this->MODEL('company');

        if (trim($_POST['keyword']) != "") {
            if ($_POST['type'] == '1') {
                $oWhere['username'] = array('like', trim($_POST['keyword']));
                $orderinfo = $userInfoM->getInfo($oWhere, array('field' => '`uid`'));
                if (is_array($orderinfo)) {
                    foreach ($orderinfo as $val) {
                        $orderuids[] = $val['uid'];
                    }
                }
                $where['comid'] = array('in', pylode(",", $orderuids));
            } elseif ($_POST['type'] == '2') {
                $where['order_id'] = array('like', trim($_POST['keyword']));
            } elseif ($_POST['type'] == '3') {
                $where['adname'] = array('like', trim($_POST['keyword']));
            } elseif ($_POST['type'] == '4') {
                $gWhere['name'] = array('like', trim($_POST['keyword']));
                $g_com = $companyM->getList($gWhere, array('field' => '`uid`'));
                if (is_array($g_com) && !empty($g_com)) {
                    foreach ($g_com['list'] as $v) {
                        $g_uid[] = $v['uid'];
                    }
                }
                $where['comid'] = array('in', pylode(",", $g_uid));
            }
        }

        if ($_POST['end']) {
            if ($_POST['end'] == '1') {
                $where['datetime'] = array('>=', strtotime(date("Y-m-d 00:00:00")));
            } else {
                $where['datetime'] = array('>=', strtotime('-' . $_POST['end'] . 'day'));
            }
        }
        if ($_POST['status']) {
            if ($_POST['status'] == "-1") {
                $where['status'] = '0';
            } else {
                $where['status'] = $_POST['status'];
            }
        }
        //提取分页
        $page = $pageM->page($_POST);
        $pageSize = $pageM->limit($_POST);
        $pages = $pageM->adminPageList('ad_order', $where, $page, array('limit' => $pageSize));
        $pageSizes = $pages['page_sizes'];

        $list = [];
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            //limit order 只有在列表查询时才需要
            $orderby = array();
            if ($_POST['t'] && in_array($_POST['order'], array('asc', 'desc'))) {
                $orderby[] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $orderby = array('status,asc', 'id,desc');
            }
            $where['orderby'] = $orderby;

            $where['limit'] = $pages['limit'];

            $List = $adM->getAdOrderList($where, array('utype' => 'admin'));
            $list = $List['list'];
        }
        $rt = array();
        $rt['integral_pricename'] = $this->config['integral_pricename'];
        $rt['list'] = $list;
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pageSizes;
        $this->render_json(0, '', $rt);
    }

    function sbody_action()
    {
        $adM = $this->MODEL('ad');
        $where['id'] = $_POST['pid'];
        $row = $adM->getAdOrderInfo($where, array('field' => '`statusbody`'));
        $this->render_json(0, '', $row['statusbody']);
    }

    function status_action()
    {
        $adM = $this->MODEL('ad');
        $companyM = $this->MODEL('company');
        $row = $adM->getAdOrderInfo(array('id' => $_POST['pid']));
        $com = $companyM->getInfo($row['uid'], array('field' => 'did'));
        $IntegralM = $this->MODEL('integral');

        if ($_POST['status'] == "1") {
            //已审核
            $value['did'] = $com['did'];
            $value['ad_name'] = $row['ad_name'];
            $value['time_start'] = date("Y-m-d");
            $value['time_end'] = date("Y-m-d", time() + 3600 * 24 * 30 * $row['buy_time']);
            $value['ad_type'] = 'pic';
            $value['pic_url'] = $row['pic_url'];
            $value['pic_src'] = $row['pic_src'];
            $value['class_id'] = $row['aid'];
            $value['is_check'] = '1';
            $value['is_open'] = '1';

            $id = $adM->addAd($value);

            $adM->upOrderAd(array('id' => $_POST['pid']), array('ad_id' => $id));

            $_POST['id'] = $id;

            $adM->model_ad_arr();

        } else if ($_POST['status'] == "2") {
            //未通过
            if ($row['buytype'] == "2") {
                if ($row['order_state'] == 2) {
                    $IntegralM->company_invtal($row['comid'], 2, $row['price'], true, "广告订单未通过审核，退还现金", true, 2, 'packpay');
                }
            } elseif ($row['buytype'] == "1") {
                $IntegralM->company_invtal($row['comid'], 2, $row['integral'], true, "广告订单未通过审核，退还" . $this->config['integral_pricename'], true, 2, 'integral');
            }
        }

        /* 消息前缀 */
        $tagName = '广告订单';

        /* 处理审核信息 */
        if ($_POST['status'] == 2) {
            $statusInfo = $tagName . ':' . $row['order_id'] . ',审核未通过';
            if ($_POST['statusbody']) {
                $statusInfo .= ' , 原因：' . $row['statusbody'];
            }
            $msg = $statusInfo;
        } elseif ($_POST['status'] == 1) {
            $msg = $tagName . ':' . $row['order_id'] . ',已审核通过';
        }

        if (!empty($msg)) {
            //发送系统通知
            $sysmsgM = $this->MODEL('sysmsg');
            $sysmsgM->addInfo(array('uid' => $row['uid'], 'usertype' => 2, 'content' => $msg));

        }
        $id = $adM->upOrderAd(array('id' => $_POST['pid']), array('status' => $_POST['status'], 'statusbody' => $_POST['statusbody']));
        $id ? $this->admin_json(0, "广告订单(ID:" . $_POST['pid'] . ")设置成功！") : $this->render_json(1, "设置失败！");
    }

    function del_action()
    {
        $adM = $this->MODEL('ad');
        if ($_POST['del']) {
            $del = $_POST['del'];
            if ($del) {
                if (is_array($del)) {
                    //批量
                    $puWhere['id'] = array('in', pylode(',', $del));
                    $puWhere['status'] = array('<>', 0);
                    $data['type'] = 'all';
                } else {
                    //单个
                    $puWhere['id'] = $del;
                    $puWhere['status'] = array('<>', 0);
                    $data['type'] = 'one';
                }
                $adM->delAdOrder($puWhere, $data);
                $this->admin_json(0, '广告订单(ID:' . pylode(',', $del) . ')删除成功！');
            } else {
                $this->render_json(1, '请选择您要删除的订单！');
            }
        }

        if (isset($_POST['id'])) {
            //单个
            $where['id'] = $_POST['id'];
            $where['status'] = array('<>', 0);
            $result = $adM->delAdOrder($where, array('type' => 'one'));
            isset($result) ? $this->render_json(0, '订单(ID:' . $_POST['id'] . ')删除成功！') : $this->render_json(2, '删除失败！');
        } else {
            $this->render_json(3, '非法操作！');
        }
    }
}