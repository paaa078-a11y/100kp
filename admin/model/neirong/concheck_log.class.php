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
class concheck_log_controller extends adminCommon
{
    function set_search()
    {
        /* @var $arr_data */
        include(CONFIG_PATH . "db.data.php");

        $search_list[] = array("param" => "result", "name" => '检测结果', "value" => array("1" => "合规", "2" => "不合规"));

        $search_list[] = array("param" => "status", "name" => '处理状态', "value" => array("0" => "待处理", "1" => "已处理", "2" => '无需处理'));

        $ports = array();
        foreach ($arr_data['source'] as $key => $value) {
            if (in_array($key, array(1, 2, 3, 13, 19, 22))) {
                $ports[$key] = $value;
            }
        }

        $search_list[] = array("param" => "source", "name" => '内容来源', "value" => $ports);

        return $search_list;
    }
    function  getGroup_action(){
        $data['search_list'] = $this->set_search();
        $this->render_json(0,'ok', $data);

    }
    function index_action(){
        $concheckM = $this->MODEL('concheck');
        $where = array();
        $noUsername = true;
        if($_POST['keyword']){
            $keyword = trim($_POST['keyword']);
            $type =	intval($_POST['type']);
            if ($type=='1'){
                $userInfoM = $this->MODEL('userinfo');
                $member = $userInfoM->getList(array('username' => array('like', $keyword)), array('field' => '`uid`'));
                if (!empty($member)){
                    $muids = array();
                    foreach ($member as $v){
                        $muids[] = $v['uid'];
                    }
                    $where['uid'] = array('in',pylode(',', $muids));
                }else{
                    $noUsername = false;
                }
            }elseif($type=='2'){
                $where['content'] =	array('like',$keyword);
            }
        }
        if(($_POST['date'])&&$_POST['date']!='undefined~undefined'){
            $times = @explode('~', $_POST['date']);
            $where['ctime'][]	= array('>=', strtotime($times[0] . " 00:00:00") );
            $where['ctime'][]	= array('<', strtotime($times[1] . " 23:59:59"));
        }
        if($_POST['status']=='0'){
            $where['status'] = 0;
        }else if($_POST['status']){
            $where['status'] = $_POST['status'];
        }
        if($_POST['result']){
            if($_POST['result'] == '1'){
                $where['result'] = 1;
            }else{
                $where['result'] = array('<>',1);
            }
        }
        if($_POST['source']){
            $where['source'] = $_POST['source'];
        }
        if($_POST['history']){
            $coninfo = $concheckM->getInfo(array('id' => $_POST['history']));
            $where['ctype']	= $coninfo['ctype'];
            $where['cid'] =	$coninfo['cid'];
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('concheck_log', $where, $page, array('limit' => $pageSize));
        if($pages['total'] > 0){
            if($_POST['order']) {
                $where['orderby'] =	$_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] =	'id';
            }
            $where['limit']	= $pages['limit'];
            $logList = $concheckM->getLogList($where);
        }

        $data['list'] = $logList ? $logList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }
    // 删除
    function del_action(){
        $concheckM = $this->Model('concheck');
        $return	= $concheckM->delConCheck($_POST['del']);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    // 处理
    function status_action()
    {
        $concheckM = $this->MODEL('concheck');
        $statusData = array(
            'status' => intval($_POST['status']),
            'statusbody' => trim($_POST['statusbody'])
        );
        $return = $concheckM->statusConCheck($_POST['pid'], $statusData);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
}