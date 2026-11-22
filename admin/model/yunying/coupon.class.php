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

class coupon_controller extends adminCommon
{
    // 列表
    function index_action()
    {
        if ($_POST['order']) {
            $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
        } else {
            $where['orderby'] = 'id';
        }

        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('coupon', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list = array();
        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];

            $couponM = $this->MODEL('coupon');

            $list = $couponM->getList($where);
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    // 保存
    function save_action()
    {
        $couponM = $this->MODEL('coupon');

        if ($_POST['id']) {
            $where['id'] = $_POST['id'];
        }

        $return = $couponM->setInfo($_POST, $where);

        if ($return['errcode'] == '9') {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    // 删除
    function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '请选择要删除的内容');
        }

        $couponM = $this->MODEL('coupon');

        $return = $couponM->delInfo($_POST['del']);

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    // 获取优惠券列表 - 所有
    function getListAll_action()
    {
        $couponM = $this->MODEL('coupon');
        $list = $couponM->getList();

        $this->render_json(0, 'ok', compact('list'));
    }

    // 赠送优惠券
    function saveGift_action()
    {
        $couponM = $this->MODEL('coupon');
        $return = $couponM->addCouponList($_POST);

        if ($return['errcode'] == '9') {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
}

?>