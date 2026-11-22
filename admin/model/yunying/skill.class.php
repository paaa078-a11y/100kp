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

class skill_controller extends adminCommon
{
    function set_search()
    {
        $search_list[] = array('param' => 'addtime', 'name' => '发布时间', 'value' => array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月', '31' => '当月'));
        $search_list[] = array('param' => 'last_update', 'name' => '更新时间', 'value' => array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月', '31' => '当月'));

        return $search_list;
    }

    // 列表
    function index_action()
    {
        $skillM = $this->Model('skill');

        if ($_POST['keyword']) {
            if ($_POST['type'] == '1') {
                $where['title'] = array('like', trim($_POST['keyword']));
            }
        }
        $month = get_this_month(time());
        if ($_POST['addtime']) {
            $addtime = intval($_POST['addtime']);
            if ($addtime == 1) {// 今天
                $where['addtime'] = array('>', strtotime('today'));
            } else if ($addtime < 31) {// 近 $addtime 天
                $where['addtime'] = array('>', strtotime('-' . $addtime . ' day'));
            } else if ($addtime == 31) {// 当月
                $where['PHPYUNBTWSTART_C'] = '';
                $where['addtime'][] = array('>=', $month['start_month']);
                $where['addtime'][] = array('<=', $month['end_month']);
                $where['PHPYUNBTWEND_C'] = '';
            }
        }

        if ($_POST['last_update']) {
            $last_update = intval($_POST['last_update']);
            if ($last_update == 1) {// 今天
                $where['last_update'] = array('>', strtotime('today'));
            } else if ($last_update < 31) {// 近 $last_update 天
                $where['last_update'] = array('>', strtotime('-' . $last_update . ' day'));
            } else if ($last_update == 31) {// 当月
                $where['PHPYUNBTWSTART_C'] = '';
                $where['last_update'][] = array('>=', $month['start_month']);
                $where['last_update'][] = array('<=', $month['end_month']);
                $where['PHPYUNBTWEND_C'] = '';
            }
        }

        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('skill', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list = array();
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('id,desc');
            }
            $where['limit'] = $pages['limit'];
            $List = $skillM->getList($where);
            $list = $List['list'];
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    function index_base_data_action()
    {
        $search_list = $this->set_search();
        $this->render_json(0, 'ok', compact('search_list'));
    }

    /**
     * 课程添加
     */
    public function add_action()
    {
        if ($_POST['id']) {
            $skillM = $this->Model('skill');
            $data['info'] = $skillM->getInfo(array('id' => $_POST['id']));
        }

        //课程类别
        $categoryM = $this->MODEL('skillcategory');
        $lessonSons = $categoryM->getSonByVariable('lesson');
        $lessonArr = array();
        foreach ($lessonSons as $v) {
            $lessonArr[] = array('id' => $v['id'], 'name' => $v['name']);
        }
        $data['lesson'] = $lessonArr;

        $this->render_json(0, 'ok', $data);
    }

    /**
     * 课程保存
     */
    public function save_action()
    {
        $postData = $this->post_trim($_POST);
        $skillM = $this->MODEL('skill');

        if ($_FILES) {
            $upData = [];
            foreach ($_FILES as $key => $value) {
                if ($value['tmp_name']) {
                    $upData[$key]['file'] = $_FILES[$key];
                    $upData[$key]['dir'] = 'skill';
                }
            }

            if ($upData) {
                $uploadM = $this->MODEL('upload');
                foreach ($upData as $key => $value) {
                    $upRes = $uploadM->newUpload($value);
                    if ($upRes && !empty($upRes['msg'])) {
                        $this->render_json(1, $upRes['msg']);
                    } else {
                        $postData[$key] = $upRes['picurl'];
                    }
                }
            }
        }
        if (CheckMobile($_POST['telphone']) == false) {
            $this->render_json(1, '手机号码格式错误');
        }
        $return = $skillM->addskill($postData);
        if ($return['error'] === 0) {
            $this->admin_json($return['error'], $return['msg']);
        } else {
            $this->render_json($return['error'], $return['msg']);
        }
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

        $skillM = $this->MODEL('skill');
        $res = $skillM->del($delID);
        if ($res['errcode'] === 0) {
            $this->admin_json($res['errcode'], $res['msg']);
        } else {
            $this->render_json($res['errcode'], $res['msg']);
        }
    }
}

?>