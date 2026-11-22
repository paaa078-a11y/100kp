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

class coupon_list_controller extends adminCommon
{
    //设置高级搜索功能
    function set_search()
    {
        $search_list[] = array("param" => "status", "name" => '消费状态', "value" => array("1" => "未消费", "2" => "已消费", "3" => "已到期"));
        $search_list[] = array("param" => "end", "name" => '到期时间', "value" => array("1" => "今天", "3" => "最近三天", "7" => "最近七天", "15" => "最近半月", "30" => "最近一个月"));
        $search_list[] = array("param" => "change", "name" => '消费时间', "value" => array("1" => "今天", "3" => "最近三天", "7" => "最近七天", "15" => "最近半月", "30" => "最近一个月"));
        $search_list[] = array("param" => "receive", "name" => '获赠时间', "value" => array('1' => '一天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月'));

        return $search_list;
    }

    // 列表
    function index_action()
    {
        $couponM = $this->MODEL('coupon');

        $upwhere['validity'] = array('<', time());
        $upwhere['status'] = '1';

        $couponM->upCouponList($upwhere, array('status' => '3'));

        if ($_POST['status']) {
            $where['status'] = $_POST['status'];
        }
        if ($_POST['change']) {
            if ($_POST['change'] == '1') {
                $where['xf_time'] = array('>=', strtotime(date("Y-m-d 00:00:00")));
            } else {
                $where['xf_time'] = array('>=', strtotime('-' . $_POST['change'] . 'day'));
            }
        }
        if ($_POST['end']) {
            if ($_POST['end'] == '1') {
                $where['validity'][] = array('<=', strtotime(date("Y-m-d 11:59:59")));
                $where['validity'][] = array('>=', strtotime(date("Y-m-d 00:00:00")));
            } else {
                $where['validity'][] = array('<=', strtotime('+' . $_POST['end'] . 'day'));
                $where['validity'][] = array('>=', time());
            }
        }
        if ($_POST['receive']) {
            if ($_POST['receive'] == '1') {
                $where['ctime'] = array('>=', strtotime(date("Y-m-d 00:00:00")));
            } else {
                $where['ctime'] = array('>=', strtotime('-' . $_POST['receive'] . 'day'));
            }
        }
        if (trim($_POST['keyword'])) {
            if ($_POST['type'] == '1') {
                $userinfoM = $this->MODEL('userinfo');

                $m_uid = $userinfoM->getList(array('username' => array('like', trim($_POST['keyword']))), array('field' => '`uid`'));

                if (is_array($m_uid) && !empty($m_uid)) {
                    foreach ($m_uid as $k) {
                        $m_id[] = $k['uid'];
                    }
                }

                $where['uid'] = array('in', pylode(',', $m_id));
            } elseif ($_POST['type'] == '2') {
                $where['number'] = array('like', trim($_POST['keyword']));
            } elseif ($_POST['type'] == '3') {
                $where['coupon_name'] = array('like', trim($_POST['keyword']));
            }
        }
        if ($_POST['order']) {
            $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
        } else {
            $where['orderby'] = 'id';
        }

        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('coupon_list', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        $list = [];
        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];

            $list = $couponM->getCouponList($where, array('utype' => 'admin'));
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    function index_base_data_action()
    {
        $search_list = $this->set_search();
        $this->render_json(0, 'ok', compact('search_list'));
    }

    // 删除
    function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '请选择要删除的内容');
        }

        $couponM = $this->MODEL('coupon');

        $del = $_POST['del'];

        if (is_array($del)) {
            $del = @implode(',', $del);
            $where['id'] = array('in', $del);
        } else {
            $where['id'] = $del;
        }

        $return = $couponM->delCouponList($where);

        if ($return['cod'] == 9) {
            $this->admin_json(0, '优惠券记录(ID:' . $del . ')删除成功');
        } else {
            $this->render_json(1, '优惠券记录删除失败');
        }
    }
}

?>