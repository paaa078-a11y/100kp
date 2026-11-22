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

class workwx_group_controller extends adminCommon
{
    /*
     * 企业微信群聊列表
     */
    function index_action()
    {
        if (!empty($_POST['keyword'])) {
            $where['name'] = array('like', trim($_POST['keyword']));
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('work_group', $where, $page, array('limit' => $pageSize));
        if($pages['total'] > 0){
            //排序
            if($_GET['order']){
                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
            }else{
                $where['orderby']   =  'id';
            }
            $where['limit'] = $pages['limit'];
            $workWxM = $this->MODEL('workwx');
            $groupChatList = $workWxM->getGroupChatList($where);
        }
        $data['list'] = $groupChatList ? $groupChatList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }
    /*
     * 群聊详情
     */
    function info_action(){
        if ($_POST['chat_id']){
            $workM = $this->MODEL('workwx');
            $groupInfo = $workM->getGroupChatInfo(array('chat_id' => $_POST['chat_id']));
            $data['groupInfo'] = $groupInfo;
            $workM = $this->MODEL('workwx');
            $data['numArr'] = json_decode($workM->groupMemberNum($_POST['chat_id']), 1);
            $where = array('chat_id' => $_POST['chat_id']);
            $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
            $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
            //提取分页
            $pageM = $this->MODEL('page');
            $pages = $pageM->adminPageList('work_group_member', $where, $page, array('limit' => $pageSize));
            if($pages['total'] > 0){
                if($_GET['order']){
                    $where['orderby'] = $_GET['t'].','.$_GET['order'];
                }else{
                    $where['orderby'] = 'join_time, desc';
                }
                $where['limit'] = $pages['limit'];
                $workWxM = $this->MODEL('workwx');
                $groupMemberList = $workWxM->getGroupMemberList($where);
            }
        }
        $data['list'] = $groupMemberList ? $groupMemberList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }

    /**
     * 同步客户群列表
     */
    public function sync_action()
    {
        $workWxM = $this->MODEL('workwx');
        $return = $workWxM->syncGroupChatList(array('cursor' => $_POST['cursor'], 'page' => $_POST['page'], 'limit' => $_POST['limit']));
        echo json_encode($return);
        exit();
    }

    /**
     * 同步客户群
     */
    public function syncInfo_action()
    {
        if ($_POST){
            $workWxM = $this->MODEL('workwx');
            $workWxM->syncGroupInfo(array('chat_id' => $_POST['chat_id']));
            $this->render_json(0, '群公告信息同步成功');
        }
    }
}
?>