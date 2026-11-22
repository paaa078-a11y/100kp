<?php

class app_push_controller extends adminCommon{
    /**
     * 工具 移动端 推送记录
     */
    function index_action(){
        $where = array();
        $type = trim($_POST['type']);
        $keyword = trim($_POST['keyword']);
        if($keyword){
            if($type == "2"){
                $companyM = $this->MODEL('company');
                $rows = $companyM->getChCompanyList(array('name' => array('like', $keyword)), array('field' => 'uid'));
                $uid = array();
                foreach ($rows as $row) {
                    $uid[] = $row['uid'];
                }
                $where['suid'] = array('in', pylode(',', $uid));
            }else{
                $resumeM = $this->MODEL('resume');
                $rows = $resumeM->getResumeList(array('name' => array('like', $keyword)), array('field' => 'uid'));
                $uid = array();
                foreach ($rows as $row) {
                    $uid[] = $row['uid'];
                }
                $where['fuid'] = array('in', pylode(',', $uid));
            }
        }
        if($_POST['ctime']){
            if($_POST['ctime'] == '1'){
                $where['ctime']	= array('>=', strtotime(date("Y-m-d 00:00:00")));
            }else{
                $where['ctime']	= array('>=', strtotime('-'.$_POST['ctime'].'day'));
            }
        }
        if($_POST['result']){
            if($_POST['result'] == '1'){
                $where['result'] = 'ok';
            }else{
                $where['result'] = array('<>','ok');
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('app_push', $where, $page, array('limit' => $pageSize));
        //分页数大于0的情况下 执行列表查询
        $list = array();
        if($pages['total'] > 0){
            //limit order 只有在列表查询时才需要
            if($_POST['order']){
                $where['orderby'] =	$_POST['t'].','.$_POST['order'];
            }else{
                $where['orderby'] =	array('id,desc');
            }
            $where['limit'] = $pages['limit'];
            $pushM = $this->MODEL('push');
            $ListNew = $pushM->getAppPushList($where);
            unset($where['limit']);
            $list = $ListNew['list'];
        }
        $data['list'] = $list;
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }

    function del_action(){
        $pushM = $this->MODEL('push');
        $return	= $pushM->delAppPush($_POST['del']);
        if ($return['errcode'] == 9) {
            $this->admin_json( 0, $return['msg']);
        } else {
            $this->render_json( 1, $return['msg']);
        }
    }
}
?>