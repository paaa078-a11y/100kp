<?php

/**
 * Class workLog_controller
 *
 * @author shiy
 * @version 7.0
 */

class workLogYwy_controller extends adminCommon
{

    function getWorkLog_action()
    {
        $crmM = $this->MODEL('crm');
        $where = array();
        if (isset($_POST['tab']) && intval($_POST['tab']) == 1){
            $where['auid'] = $_SESSION['auid'];
        }
        if (isset($_POST['crm_uid']) && !empty($_POST['crm_uid'])) {
            $where['auid'] = $_POST['crm_uid'];
        }
        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
            $where['content'] = array('like', $_POST['keyword']);
        }
        if (isset($_POST['time']) && !empty($_POST['time'])) {
            $start = date('Y-m-d', $_POST['time'][0]/1000);
            $end = date('Y-m-d', $_POST['time'][1]/1000);
            $where['PHPYUNBTWSTART_A'] = '';
            $where['ctime'][] = array('>=', strtotime($start . " 00:00:00"));
            $where['ctime'][] = array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A'] = '';
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('crm_work_log', $where, $page, array('limit' => $pageSize));
        $list = array();
        $total = intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {
                $where['orderby'] = $_POST['t'] . ', ' . $_POST['order'];
            } else {
                $where['orderby'] = 'id,desc';
            }
            $where['limit'] = $pages['limit'];
            $list = $crmM->getWorkLogList($where, array('utype' => 'crm'));
        }
        $result = array(
            'list' => $list,
            'total' => intval($total),
            'pageSize' => $pageSize,
            'pageSizes' => $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }

    function getWorkLogStaticData_action(){
        $adminM = $this->MODEL('admin');
        $crmUser = $adminM->getList(array('did'=>0,'is_crm'=>1), array('field' => '`uid`,`name`'));
        $result = array(
            'crmUser' => $crmUser
        );
        $this->render_json(0, '', $result);
    }

    function delWorkLog_action()
    {
        $crmM = $this->MODEL('crm');
        $delId = $_POST['id'];
        if (!empty($delId)) {
            $return = $crmM->delWorkLog($delId);
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        } else {
            $this->render_json(1, '参数错误，请重试！');
        }
    }

    function add_action(){
        if (!$_POST['logtitle']){
            $this->render_json(1, '请输入日志标题!');
        }
        if (!$_POST['logcontent']){
            $this->render_json(1, '请输入日志内容!');
        }
        $data = array(
            'auid' => $_SESSION['auid'],
            'title' => $_POST['logtitle'],
            'content' => $_POST['logcontent']
        );
        $crmM = $this->MODEL('crm');
        $return	= $crmM->addWorkLog($data);
        if ($return['errcode'] == 9){
            $this->admin_json(0,$return['msg']);
        }else{
            $this->render_json(1,$return['msg']);
        }
    }
}