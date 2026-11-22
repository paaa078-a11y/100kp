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
class splog_controller extends adminCommon
{
    /**
     * 视频记录
     */
    function index_action(){
        $spM = $this->MODEL('spview');
        if($_POST['id']){
            $where['zid'] =	intval($_POST['id']);
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('spview_log', $where, $page, array('limit' => $pageSize));
        if($pages['total'] > 0){
            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $rows = $spM->getSpLogList($where, array('utype' => 'admin'));
        }
        $data['list'] = $rows ? $rows : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }
    function getNumber_action(){

        $spM = $this->MODEL('spview');
        $data['num_arr'] = json_decode($spM->spLogNum($_POST['id'] ? intval($_POST['id']) : ''), 1);// 通话记录数据统计
        $this->render_json(0,'ok', $data);

    }
    //删除链接
    function delSplog_action(){
        $spM = $this->MODEL('spview');
        $delID = $_POST['del'];
        $arr = $spM->delSplog($delID, array('utype' => 'admin'));
        if ($arr['errcode'] == 9) {
            $this->admin_json(0, $arr['msg']);
        } else {
            $this->render_json(1, $arr['msg']);
        }
    }
}