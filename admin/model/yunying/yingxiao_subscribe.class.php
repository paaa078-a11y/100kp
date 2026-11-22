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

class yingxiao_subscribe_controller extends adminCommon
{
    // 列表
    function index_action()
    {
        $subscribeM = $this->MODEL('subscribe');

        if ($_POST['end']) {
            if ($_POST['end'] == '1') {
                $where['ctime'] = array('>=', strtotime('today'));
            } else {
                $where['ctime'] = array('>', strtotime('-' . intval($_POST['end']) . ' day'));
            }
        }

        if ($_POST['type']) {
            $type = intval($_POST['type']);
            $where['type'] = $type;
        }
        if ($_POST['state']) {
            $state = intval($_POST['state']);
            $where['status'] = $state == 2 ? 0 : $state;
        }
        if ($_POST['keyword']) {
            $keyword = trim($_POST['keyword']);
            $where['email'] = array('like', $keyword);
        }

        //提取分页
        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('subscribe', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list = array();
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('ctime,desc');
            }

            $where['limit'] = $pages['limit'];
            $list = $subscribeM->getList($where, array('utype' => 'admin'));
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    // 删除订阅
    function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $subscribeM = $this->MODEL('subscribe');

        $del = $_POST['del'];

        $return = $subscribeM->del($del,'');

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    // 发送记录
    function recordlist_action()
    {
        $subscribeM = $this->MODEL('subscribe');

        if ($_POST['id']) {
            $where['sid'] = intval($_POST['id']);
        }

        //排序
        if ($_POST['order']) {
            if ($_POST['t'] == 'id' || $_POST['t'] == 'sort') {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            }
        } else {
            $where['orderby'] = array('id,desc');
        }

        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('subscriberecord', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];
            $list = $subscribeM->getRecordList($where);
        } else {
            $list = array();
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    // 删除发送记录
    function delrecord_action()
    {
        if (is_array($_POST['del'])) {
            $del = $_POST['del'];
        } else {
            $del = intval($_POST['del']);
        }

        $subscribeM = $this->MODEL('subscribe');

        $return = $subscribeM->delrecord($del,'');

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
}

?>