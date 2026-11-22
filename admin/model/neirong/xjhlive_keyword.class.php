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
class xjhlive_keyword_controller extends adminCommon
{
    function index_action(){
        $where['id'] = array('>',0);
        if ($_POST['keyword']) {
            $keyword = trim($_POST['keyword']);
            $where['name'] = array('like', $keyword);
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('xjhlive_keyword', $where, $page, array('limit' => $pageSize));
        if ($pages['total'] > 0) {
            $where['orderby'] =	'id,desc';
            $where['limit']	= $pages['limit'];
            $xjhM = $this->MODEL('xjhlive');
            $rows = $xjhM->getXjhkeywordList($where,array('utype' => 'admin'));
        }
        $rt['list'] = $rows ? $rows : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }

    function add_action(){
        $_POST = $this->post_trim($_POST);
        $position = explode('-', $_POST['name']);
        $name = array_unique($position);
        $xjhM = $this->MODEL('xjhlive');
        $result = $xjhM->addXjhkeyword($name);
        if($result['errcode'] == 9){
            $this->admin_json(0, $result['msg']);
        }else{
            $this->render_json(1, $result['msg']);
        }
    }

    function del_action(){
        $delID = $_POST['del'];
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->delXjhkeyword($delID,array('utype' => 'admin'));
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
}