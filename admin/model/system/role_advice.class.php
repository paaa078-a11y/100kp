<?php

class role_advice_controller extends adminCommon{

    /*
     * 意见建议列表
     */
    function index_action(){
        $apiurl = 'https://u.phpyun.com/advicelist';
        $total = 0;
        $keyword = trim($_POST['keyword']) ? trim($_POST['keyword']) : '';
        $perPage = isset($_POST['perPage']) && $_POST['perPage'] ? $_POST['perPage'] : $this->config['sy_listnum'];
        $page = isset($_POST['page']) && $_POST['page'] > 1 ? $_POST['page'] : 1;
        $params = array(
            'page' => $page,
            'perPage' => $perPage,
            'weburl' => $this->config['sy_weburl'],
            'keyword' => $keyword
        );
        $res = CurlPost($apiurl, $params, 1);
        $resArr = json_decode($res, 1);
        $task = $resArr['data'];
        foreach ($task as $k => $v) {
            $task[$k]['content'] = stripslashes($v['content']);
        }
        if ($task) {
            $total = $resArr['total'];
            $mpage = ceil($total/$perPage); // 计算最后的页数
            $page > $mpage && $page = $mpage;
        }
        $deflimit = $this->config['sy_listnum'];
        $page_sizes = array(intval($deflimit),2*$deflimit,3*$deflimit,4*$deflimit);
        $data = array(
            'list'=>$task,
            'total'=> $total,
            'pageSizes'=>$page_sizes,
            'perPage' => $perPage,
        );
        $this->render_json(0, 'ok', $data);
    }

    /**
     * 管理员-管理员类型-添加、修改保存
     */
    function save_action()
    {
        $apiurl = 'https://u.phpyun.com/adviceadd';
        if (!$_POST['title']) {
            $this->render_json(1, '请填写标题');
        }
        if (!$_POST['plat']) {
            $this->render_json(1, '请选择适用端');
        }
        if (!$_POST['content']) {
            $this->render_json(1, '请填写描述');
        }
        $params = array(
            'id' => $_POST['id'],
            'weburl' => $this->config['sy_weburl'],
            'title'   =>  $_POST['title'],
            'plat'   =>  $_POST['plat'],
            'content' => str_replace(array('&amp;','background-color:#ffffff','background-color:#fff','white-space:nowrap;'), array('&','background-color:','background-color:','white-space:'), $_POST['content']),
        );
        $res = CurlPost($apiurl, $params, 1);
        $resArr = json_decode($res, 1);
        if ($resArr['code'] == 200) {
            $this->render_json(0, $resArr['message']);
        } else {
            $this->render_json(1, $resArr['message']);
        }
    }
    /*
     * 删除管理员类型
     */
    function del_action()
    {
        $apiurl = 'https://u.phpyun.com/advicedel';
        $del = $_POST['id'];
        if($del){
            $ids = explode(',', $del);
            $params = array(
                'weburl' => $this->config['sy_weburl'],
                'id'   =>  json_encode($ids),
            );
            $res = CurlPost($apiurl, $params, 1);
            $resArr = json_decode($res, 1);
            if ($resArr['code'] == 200) {
                $this->admin_json(0, '删除成功');
            } else {
                $this->render_json(1, '删除失败');
            }
        }else{
            $this->render_json(1, '参数错误,请重试');
        }
    }
}
?>