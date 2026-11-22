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

class skill_bm_controller extends adminCommon
{
    // 列表
    function index_action()
    {
        $skillbmM = $this->Model('skillbm');

        $where = array();
        $skillWhere = array();
        if ($_POST['keyword']) {
            if ($_POST['type'] == '1') {
                $skillWhere['title'] = array('like', trim($_POST['keyword']));
            } elseif ($_POST['type'] == '2') {
                $where['name'] = array('like', trim($_POST['keyword']));
            }
            if ($skillWhere) {
                $skills = $skillbmM->getskillList($skillWhere, array('field' => 'id'));
                if (!empty($skills)) {
                    foreach ($skills as $v) {
                        $ids[] = $v['id'];
                    }
                } else {
                    $ids = array(0);
                }
                $where['skill_id'][] = array('in', pylode(',', $ids));
            }
        }
        if (isset($_POST['isread']) && $_POST['isread'] !== '') {
            $where['isread'] = intval($_POST['isread']) == 1 ? 1 : 0;
        }

        $pageM = $this->MODEL('page');
        $list = array();
        $pages = $pageM->adminPageList('skill_bm', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('id,desc');
            }
            $where['limit'] = $pages['limit'];
            $List = $skillbmM->getList($where, array('utype' => 'admin'));
            $list = $List['list'];
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    // 删除
    function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '请选择要删除的内容');
        }

        if (is_array($_POST['del'])) {
            $delID = $_POST['del'];
        } else {
            $delID = intval($_POST['del']);
        }
        $skillbmM = $this->MODEL('skillbm');
        $res = $skillbmM->del($delID);
        if ($res['errcode'] == 9) {
            $this->admin_json(0, $res['msg']);
        } else {
            $this->render_json(1, $res['msg']);
        }
    }
}

?>