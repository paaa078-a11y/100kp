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
class xjhlive_yy_controller extends adminCommon
{
    function index_action(){
        $xjhM = $this->MODEL('xjhlive');
        $where = array();
        if($_POST['keyword']){
            $keyword = trim($_POST['keyword']);
            $type = intval($_POST['type']);
            if ($type == '1'){
                $where['uid'] = $keyword;
            }else if($type == '2'){
                $xjh = $xjhM->getList(array('name' => array('like', $keyword)), array('field' => 'id'));
                foreach($xjh as $key => $val){
                    $ids[] = $val['id'];
                }
                $where['xid'] = array('in',pylode(',',$ids));
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('xjhlive_yy', $where, $page, array('limit' => $pageSize));
        // 分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            // limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('ctime,desc','id,desc');
            }
            $where['limit'] = $pages['limit'];
            $rows = $xjhM->getyyList($where);
        }
        $rt['list'] = $rows ? $rows : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }

    function del_action()
    {
        $xjhM = $this->Model('xjhlive');
        if($_POST['del']){
            $return = $xjhM->delyy($_POST['del'], array('utype' => 'admin'));
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        }else{
            $this->render_json(1, "请选择您要删除的信息！");
        }
    }
}