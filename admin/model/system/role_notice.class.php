<?php

class role_notice_controller extends adminCommon{
    protected $qywx_state = 'weblogin@phpyun';
    /**
     * 管理员-微信通知
     */
    function wxAdminMsgList_action(){
        $weixinM = $this->MODEL('weixin');
        $where['utype'] = 99;
        if ($_POST['keyword']) {
            $keyword = trim($_POST['keyword']);
            if ($_POST['type'] == 1) {
                $uids = array();
                $adminM = $this->MODEL('admin');
                $uList = $adminM->getList(array('username' => array('like', $keyword)));
                foreach ($uList as $uk => $uv) {
                    if ($uv['uid']) {
                        $uids[] = $uv['uid'];
                    }
                }
                $where['uid'] = array('in', pylode(',', $uids));
            }else if ($_POST['type'] == 2){
                $where['uid'] = $keyword;
            }
        }
        if ($_POST['date']) {
            $times = @explode('~', $_POST['date']);
            $where['PHPYUNBTWSTART_A'] = '';
            $where['ctime'][] = array('>=', strtotime($times[0]." 00:00:00"));
            $where['ctime'][] = array('<', strtotime($times[1]." 23:59:59"));
            $where['PHPYUNBTWEND_A'] = '';
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('wx_msg', $where, $page, array('limit' => $pageSize));
        $wxMsgList = array();
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'].','.$_POST['order'];
            } else {
                $where['orderby'] = 'id';
            }
            $where['limit'] = $pages['limit'];
            $wxMsgList = $weixinM->getWxmsgList($where);
        }
        $data['list'] = $wxMsgList;
        $data['total'] = intval($pages['total']);
        $data['wx_mbadmin'] = $this->config['wx_mbadmin'] ? $this->config['wx_mbadmin'] : '';
        $data['wx_mbadminlm'] = $this->config['wx_mbadminlm'] ? $this->config['wx_mbadminlm'] : '';
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }

    /**
     * 管理员-微信通知-删除
     */
    function delWxAdminMsg_action()
    {
        $weixinM = $this->Model('weixin');
        if (is_array($_POST['del'])) {
            $delid = @implode(',', $_POST['del']);
            $where['id'] = array('in', $delid);
        } else {
            $delid = $_POST['del'];
            $where['id'] = $delid;
        }
        $where['id'] = array('in', $delid);
        $del = $weixinM->delWxmsg(array('where' => $where));
        if ($del) {
            $code = 0;
            $msg = '微信通知消息(ID:' . $delid . ')删除成功！';
        } else {
            $code = 1;
            $msg = '删除失败！';
        }
        $this->admin_json($code, $msg);
    }
    /*
     * 通知设置保存
     */
    function wmTypeSet_action()
    {
        if ($_POST['msgconfig']) {
            $_POST = $this->post_trim($_POST);
            //实例化
            $configM = $this->MODEL('config');
            unset($_POST['msgconfig']);
            $configM->setConfig($_POST);
            $this->web_config();
            $this->admin_json(0, '通知方式配置更新成功！');
        }
    }
    /**
     * 微信模板消息通知列表
     */
    function qywxMsgList_action()
    {
        $weixinM = $this->MODEL('weixin');
        if ($_POST['keyword']) {
            $keyword = trim($_POST['keyword']);
            $type = intval($_POST['type']);
            if ($type == 1) {
                $uids = array();
                $mwhere['username'] = array('like', $keyword);
                $adminM = $this->MODEL('admin');
                $ulist = $adminM->getList($mwhere);
                foreach ($ulist as $uk => $uv) {
                    if ($uv['uid']) {
                        $uids[] = $uv['uid'];
                    }
                }
                $where['uids'] = array('findin', pylode(',', $uids));
            } else {
                $where['touser'] = array('like', $keyword);
            }
        }
        if (($_POST['date'])) {
            $times = @explode('~', $_POST['date']);
            $where['ctime'][] = array('>=', strtotime($times[0] . " 00:00:00"));
            $where['ctime'][] = array('<', strtotime($times[1] . " 23:59:59"));
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('wx_qymsg', $where, $page, array('limit' => $pageSize));
        $wxMsgList = array();
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'].','.$_POST['order'];
            } else {
                $where['orderby'] = 'id';
            }
            $where['limit'] = $pages['limit'];
            $wxMsgList = $weixinM->getQyWxmsgList($where);
        }
        $data['list'] = $wxMsgList;
        $data['total'] = intval($pages['total']);
        $data['wx_qy_corpid'] = $this->config['wx_qy_corpid'] ? $this->config['wx_qy_corpid'] : '';
        $data['wx_qy_agentid'] = $this->config['wx_qy_agentid'] ? $this->config['wx_qy_agentid'] : '';
        $data['wx_qy_secret'] = $this->config['wx_qy_secret'] ? $this->config['wx_qy_secret'] : '';
        $data['qy_app_id'] = $this->config['wx_qy_corpid'];
        $data['agent_id'] = isset($this->config['wx_photo_agentId']) ? $this->config['wx_photo_agentId'] : $this->config['wx_qy_agentid'];

        include(PLUS_PATH."/admindir.php");
        $data['redirect_uri'] = UrlEncode($this->config['sy_weburl'] . '/'.$admindir.'/#/notice?type=three');
        $data['state'] = $this->qywx_state;
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }
    /*
     * 删除企业微信消息通知
     */
    function delQymsg_action(){
        $weixinM = $this->Model('weixin');
        if(is_array($_POST['del'])){
            $delid = @implode(',',$_POST['del']);
            $where['id'] = array('in',$delid);
        }else{
            $delid = (int)$_POST['del'];
            $where['id'] = $delid;
        }
        $del = $weixinM->delQyWxmsg(array('where'=>$where));
        if ($del) {
            $code = 0;
            $msg = '企微通知消息(ID:' . $delid . ')删除成功！';
        } else {
            $code = 1;
            $msg = '删除失败！';
        }
        $this->admin_json($code, $msg);
    }

    //管理员 微信通知设置 管理员通知
    function wxMsgSet_action()
    {
        $adminM = $this->MODEL('admin');
        $twhere['orderby'] = 'id,asc';
        $wxmsgType = $adminM->getWmtype($twhere);
        $where['orderby'] = array('m_id,asc', 'uid,asc');
        $adminlist = $adminM->getList($where);
        $tmp = array();
        foreach($adminlist as $v){
            $tmp[$v['uid']] = $v['username'];
        }
        foreach ($wxmsgType as $k=>$v) {
            $uname_arr = array();
            foreach ($v['uid_arr'] as $vv) {
                $tmp[$vv] && $uname_arr[] = $tmp[$vv];
            }
            $wxmsgType[$k]['uname_arr'] = $uname_arr;
        }
        $this->render_json(0,'ok', array('msg_type' => $wxmsgType, 'admins' => $adminlist, 'wm_type' => $this->config['sy_admin_wmtype']));
    }
    // 管理员 微信通知设置 管理员通知 保存设置
    function wxMsgSave_action()
    {
        if ($_POST['wmid']) {
            $type = $_POST['wmid'];// 要通知的类型
            $adminArr = !empty($_POST['checkeduids']) ? $_POST['checkeduids'] : array();// 确认通知的管理员uid
            $adminM = $this->MODEL('admin');
            $where['orderby'] = 'uid,asc';
            $adminlist = $adminM->getList($where);
            $uids = array();
            foreach ($adminlist as $k => $v) {// 所有管理员
                $uids[] = $v['uid'];
            }
            //验证提交的管理员uid是否真的存在
            $yuids = array();
            if (!empty($adminArr)) {
                foreach ($uids as $uk => $uv) {
                    if (in_array($uv, $adminArr)) {
                        $yuids[] = $uv;
                    }
                }
            }
            $ywhere = array('type' => $type);
            $mbuid = !empty($yuids) ? pylode(',', $yuids) : '';
            $post = array('uid' => $mbuid);
            $return = $adminM->upWmtype($post, $ywhere);
        }
        if ($_POST['wmType']){
            $configM = $this->MODEL('config');
            $configM->setConfig(array('sy_admin_wmtype' => $_POST['wmType']));
            $this->web_config();
            $return =   true;
        }
        if ($return) {
            $this->admin_json(0, '设置成功');
        } else {
            $this->admin_json(1, '设置失败请重试');
        }
    }
}
?>