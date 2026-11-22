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

class workwx_push_controller extends adminCommon
{
    /**
     * 企业群发
     */
    function index_action()
    {
        $where = array();
        $keyStr = trim($_POST['keyword']);
        if (!empty($keyStr)){
            $where['name'] = array('like', $keyStr);
        }
        if ($_POST['chat_type']){
            $where['chat_type'] = $_POST['chat_type'];
        }
        if (isset($_POST['send'])) {
            if ($_POST['send'] == 1) {
                $_POST['send'] = 1;
            } else if ($_POST['send'] == 2) {
                $_POST['send'] = 0;
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('work_message', $where, $page, array('limit' => $pageSize));
        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];
            $workWxM = $this->MODEL('workwx');
            $messageList = $workWxM->getMessageList($where);
        }
        $data['list'] = $messageList ? $messageList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $data['sy_xcxappid'] = $this->config['sy_xcxappid'];
        $data['sy_xcxpath'] = $this->config['sy_xcxpath'];
        $this->render_json(0,'ok', $data);
    }

    /**
     * 查询客户信息
     */
    function getWorkCustomer_action()
    {
        $customerArr = $this->obj->select_all('work_customer', array('groupby' => 'external_userid'), '`external_userid`,`name`');
        $userArr = array();
        foreach ($customerArr as $k => $v){
            $userArr[$k]['value'] = $v['external_userid'];
            $userArr[$k]['label'] = base64_decode($v['name']);
        }
        $this->render_json(0, '', $userArr);
    }

    /**
     * 查询员工信息
     */
    function getWorkUserId_action()
    {
        $workArr = $this->obj->select_all('work_user', array('is_follow' => 1), '`userid`,`name`');
        $userArr = array();
        foreach ($workArr as $k => $v){
            $userArr[$k]['value'] = $v['userid'];
            $userArr[$k]['name'] = !empty($v['name']) ? base64_decode($v['name']) : $v['userid'];
        }
        $this->render_json(0, '', $userArr);
    }

    /**
     * 保存企业群发
     */
    function save_action()
    {
        $workM = $this->MODEL('workwx');
        $data = array();
        $info = $_POST;
        $data['name'] = $info['name'];
        if (!$data['name']) {
            $this->render_json(1, '任务名称不能为空');
        }
        $data['chat_type'] = $info['chat_type'];
        if ($info['chat_type'] == 1) {//  客户
            if (!$info['external_userid_arr']) {
                $this->render_json(1, '请选择客户');
            }
            $data['external_userid'] = implode(',', $info['external_userid_arr']);
        } elseif ($info['chat_type'] == 2) {//  客户群
            if (!$info['sender']) {
                $this->render_json(1, '请选择员工');
            }
            $data['sender'] = trim($info['sender']);
        }
        $data['content'] = $info['content'];
        $data['msgtype'] = $info['msgtype'];
        if (empty($data['content']) && empty($info['msgtype'])) {
            $this->render_json(1, '文本消息内容和附件消息不能同时为空');
        }
        if ($info['msgtype'] == 1){
            if (!$info['pic_url'] && !$_FILES['pic']) {
                $this->render_json(1, '附件图片不能为空');
            }
            // 新上传图片文件处理
            foreach ($_FILES['pic'] as $nk => $nv) {
                foreach ($nv as $nkk => $nvv) {
                    $pic_file[$nkk][$nk] = $nvv;
                }
            }
            if($_FILES['pic']['tmp_name']){
                $upArr = array(
                    'file' => $pic_file[0],
                    'dir' => 'workwx'
                );
            }
            $uploadM = $this->MODEL('upload');
            if (!empty($upArr)) {
                $pic = $uploadM->newUpload($upArr);
                if(!empty($pic['picurl'])){
                    $res = $workM->uploadImg(checkpic($pic['picurl']));
                    $data['pic_url'] = $res['url'];
                }
            }
        } else if ($info['msgtype'] == 2){
            if (empty($info['link_url']) || empty($info['link_title'])) {
                $this->render_json(1, "链接消息：链接和标题不能为空");
            }else{
                $data['link_url'] = $info['link_url'];
                $data['link_title'] = $info['link_title'];
                $data['link_desc'] = $info['link_desc'];
                // 新上传图片文件处理
                foreach ($_FILES['linkpic'] as $nk => $nv) {
                    foreach ($nv as $nkk => $nvv) {
                        $linkpic_file[$nkk][$nk] = $nvv;
                    }
                }
                if($_FILES['linkpic']['tmp_name']){
                    $upArr = array(
                        'file' => $linkpic_file[0],
                        'dir' => 'workwx'
                    );
                }
                $uploadM = $this->MODEL('upload');
                if (!empty($upArr)) {
                    $pic = $uploadM->newUpload($upArr);
                    if(!empty($pic['picurl'])){
                        $data['link_picurl'] = checkpic($pic['picurl']);
                    }
                }
            }
        } else if ($info['msgtype'] == 3){
            if (!$info['mini_title']) {
                $this->render_json(1, "附件小程序标题不能为空");
            }
            $data['mini_title'] = $info['mini_title'];
            $data['mini_appid'] = $info['mini_appid'];
            $data['mini_page'] = $info['mini_page'];
            if (!$info['mini_picurl'] && !$_FILES['minipic']) {
                $this->render_json(1, "附件小程序封面不能为空");
            }
            // 新上传图片文件处理
            foreach ($_FILES['minipic'] as $nk => $nv) {
                foreach ($nv as $nkk => $nvv) {
                    $minipic_file[$nkk][$nk] = $nvv;
                }
            }
            if($_FILES['minipic']['tmp_name']){
                $upArr = array(
                    'file' => $minipic_file[0],
                    'dir' => 'workwx'
                );
            }
            $uploadM = $this->MODEL('upload');
            if (!empty($upArr)) {
                $pic = $uploadM->newUpload($upArr);
                if(!empty($pic['picurl'])){
                    $res = $workM->uploadMedia($pic['picurl']);
                    $data['mini_media'] = $res['media_id'];
                    $data['mini_picurl'] = $pic['picurl'];
                    $data['mini_endtime'] = $res['created_at'] + 86400 * 3;
                }
            }
        }
        if($info['id']){
            $return = $workM->upMessage($data, array('id' => intval($info['id'])));
        }else{
            $return	= $workM->addMessage($data);
        }
        if ($return['errcode'] == 9){
            $this->admin_json(0, $return['errmsg']);
        }else{
            $this->render_json(1, $return['errmsg']);
        }
    }

    /**
     * @desc 删除企业群发
     */
    function delMessage_action()
    {
        $workM = $this->MODEL('workwx');
        $del = $_POST['del'];
        if (is_array($del)) {
            $where['id'] = array('in', pylode(',', $del));
        } else {
            $where['id'] = $del;
        }
        $return = $workM->delMessage($where);
        if ($return['errcode'] == 9) {
            $this->admin_json( 0, $return['errmsg']);
        } else {
            $this->render_json( 1, $return['errmsg']);
        }
    }

    /**
     * @desc 创建企业群发（请求接口）
     */
    function addMsgTemplate_action()
    {
        if ($_POST['id']){
            $workM = $this->MODEL('workwx');
            $return = $workM->addMsgTemplate(array('id' => $_POST['id']));
            echo json_encode($return);die;
        }
    }

    /**
     * 客户欢迎语
     */
    function welcomeList_action()
    {
        $where = array('type' => 1);
        $keyStr = trim($_POST['keyword']);
        if (!empty($keyStr)){
            $where['name'] = array('like', $keyStr);
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('work_welcome', $where, $page, array('limit' => $pageSize));
        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];
            $workWxM = $this->MODEL('workwx');
            $messageList = $workWxM->getWelcomeList($where);
        }
        $data['list'] = $messageList ? $messageList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $data['sy_xcxappid'] = $this->config['sy_xcxappid'];
        $data['sy_xcxpath'] = $this->config['sy_xcxpath'];
        $this->render_json(0,'ok', $data);
    }

    /**
     * 查询客户信息
     */
    function getWorkUser_action()
    {
        $userList = $this->obj->select_all('work_user', array('is_follow' => '1'), '`userid`,`name`');
        if ($_POST['id']) {
            $welcome = $this->obj->select_once('work_welcome', array('id' => $_POST['id'], 'type' => 1), '`userid`');
            $userIdArr = explode(',', $welcome['userid']);
            $welcomeList = $this->obj->select_all('work_welcome', array('type' => 1, 'id' => array('<>', $_POST['id'])), '`userid`');
        }else{
            $welcomeList = $this->obj->select_all('work_welcome', array('type' => 1), '`userid`');
        }
        if (!empty($welcomeList)){
            $userIdInArr = array();
            foreach ($welcomeList as $wk => $wv){
                $userIdInArr = array_merge($userIdInArr, explode(',', $wv['userid']));
            }
        }
        $userArr = array();
        foreach ($userList as $k => $v) {
            $userArr[$k]['value'] = $v['userid'];
            $userArr[$k]['label'] = !empty($v['name']) ? base64_decode($v['name']) : $v['userid'];
            if (isset($userIdArr) && !empty($userIdArr)) {
                foreach ($userIdArr as $uk => $uv) {
                    if ($v['userid'] == $uv) {
                        $userArr[$k]['selected'] = true;
                    }
                }
            }
            if (isset($userIdInArr) && in_array($v['userid'], $userIdInArr)){
                unset($userArr[$k]);
            }
        }
        $this->render_json(0, '', array_values($userArr));
    }

    /**
     * 保存新客户欢迎语
     */
    function saveWelcome_action()
    {
        $workM = $this->MODEL('workwx');
        $data = array();
        $info = $_POST;
        $data['name'] = $info['name'];
        if (!$info['name']) {
            $this->render_json(1, '欢迎语名称不能为空');
        }
        $data['type'] = 1;
        $data['userid'] = implode(',', $info['userid_arr']);
        $data['content'] = $info['content'];
        $data['msgtype'] = $info['msgtype'];
        if (empty($data['content']) && empty($info['msgtype'])) {
            $this->render_json(1, '文本消息内容和附件消息不能同时为空');
        }
        if ($info['msgtype'] == 1){
            if (!$info['pic_url'] && !$_FILES['pic']) {
                $this->render_json(1, '附件图片不能为空');
            }
            // 新上传图片文件处理
            foreach ($_FILES['pic'] as $nk => $nv) {
                foreach ($nv as $nkk => $nvv) {
                    $pic_file[$nkk][$nk] = $nvv;
                }
            }
            if($_FILES['pic']['tmp_name']){
                $upArr = array(
                    'file' => $pic_file[0],
                    'dir' => 'workwx'
                );
            }
            $uploadM = $this->MODEL('upload');
            if (!empty($upArr)) {
                $pic = $uploadM->newUpload($upArr);
                if(!empty($pic['picurl'])){
                    $res = $workM->uploadImg(checkpic($pic['picurl']));
                    $data['pic_url'] = $res['url'];
                }
            }
        } else if ($info['msgtype'] == 2){
            if (empty($info['link_url']) || empty($info['link_title'])){
                $this->render_json(1, '图文附件：链接和标题不能为空');
            } else {
                $data['link_url'] = $info['link_url'];
                $data['link_title'] = $info['link_title'];
                $data['link_desc'] = $info['link_desc'];
                // 新上传图片文件处理
                foreach ($_FILES['linkpic'] as $nk => $nv) {
                    foreach ($nv as $nkk => $nvv) {
                        $linkpic_file[$nkk][$nk] = $nvv;
                    }
                }
                if($_FILES['linkpic']['tmp_name']){
                    $upArr = array(
                        'file' => $linkpic_file[0],
                        'dir' => 'workwx'
                    );
                }
                $uploadM = $this->MODEL('upload');
                if (!empty($upArr)) {
                    $pic = $uploadM->newUpload($upArr);
                    if(!empty($pic['picurl'])){
                        $data['link_picurl'] = checkpic($pic['picurl']);
                    }
                }
            }
        } else if ($info['msgtype'] == 3){
            if (!$info['mini_title']) {
                $this->render_json(1, "附件小程序标题不能为空");
            }
            $data['mini_title'] = $info['mini_title'];
            $data['mini_appid'] = $info['mini_appid'];
            $data['mini_page'] = $info['mini_page'];
            if (!$info['mini_picurl'] && !$_FILES['minipic']) {
                $this->render_json(1, "附件小程序封面不能为空");
            }
            // 新上传图片文件处理
            foreach ($_FILES['minipic'] as $nk => $nv) {
                foreach ($nv as $nkk => $nvv) {
                    $minipic_file[$nkk][$nk] = $nvv;
                }
            }
            if($_FILES['minipic']['tmp_name']){
                $upArr = array(
                    'file' => $minipic_file[0],
                    'dir' => 'workwx'
                );
            }
            $uploadM = $this->MODEL('upload');
            if (!empty($upArr)) {
                $pic = $uploadM->newUpload($upArr);
                if(!empty($pic['picurl'])){
                    $res = $workM->uploadMedia($pic['picurl']);
                    $data['mini_media'] = $res['media_id'];
                    $data['mini_picurl'] = $pic['picurl'];
                    $data['mini_endtime'] = $res['created_at'] + 86400 * 3;
                }
            }
        }
        if($info['id']){
            $return	= $workM->upWelcome($data, array('id' => intval($info['id'])));
        }else{
            $return	= $workM->addWelcome($data);
        }
        if ($return['errcode'] == 9) {
            $this->admin_json( 0, $return['errmsg']);
        } else {
            $this->render_json(1, $return['errmsg']);
        }
    }

    /**
     * @desc 删除欢迎语
     */
    function delWelcome_action()
    {
        $workM = $this->MODEL('workwx');
        $del = $_POST['del'];
        if (is_array($del)) {
            $where['id'] = array('in', pylode(',', $del));
        } else {
            $where['id'] = $del;
        }
        $return = $workM->delWelcome($where);
        if ($return['errcode'] == 9 ) {
            $this->admin_json(0, $return['errmsg']);
        } else {
            $this->render_json(1, $return['errmsg']);
        }
    }

    /**
     * 入群欢迎语
     */
    function welcomeGroupList_action()
    {
        $where = array('type' => 2);
        $keyStr = trim($_POST['keyword']);
        if (!empty($keyStr)){
            $where['name'] = array('like', $keyStr);
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('work_welcome', $where, $page, array('limit' => $pageSize));
        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];
            $workWxM = $this->MODEL('workwx');
            $messageList = $workWxM->getWelcomeGroupList($where);
        }
        $data['list'] = $messageList ? $messageList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $data['sy_xcxappid'] = $this->config['sy_xcxappid'];
        $data['sy_xcxpath'] = $this->config['sy_xcxpath'];
        $this->render_json(0,'ok', $data);
    }

    /**
     * 保存入群欢迎语
     */
    function saveGroupWelcome_action()
    {
        $workM = $this->MODEL('workwx');
        $data = array();
        $info = $_POST;
        $data['name'] = $info['name'];
        $data['type'] = 2;
        $data['notify'] = isset($info['notify']) && $info['notify'] == true ? 1 : 0;
        $data['content'] = $info['content'];
        $data['msgtype'] = $info['msgtype'];
        if (empty($data['content']) && empty($info['msgtype'])) {
            $this->render_json(1, "文本消息内容和附件消息不能同时为空");
        }
        if ($info['msgtype'] == 1){
            if (!$info['pic_url'] && !$_FILES['pic']) {
                $this->render_json(1, '附件图片不能为空');
            }
            // 新上传图片文件处理
            foreach ($_FILES['pic'] as $nk => $nv) {
                foreach ($nv as $nkk => $nvv) {
                    $pic_file[$nkk][$nk] = $nvv;
                }
            }
            if($_FILES['pic']['tmp_name']){
                $upArr = array(
                    'file' => $pic_file[0],
                    'dir' => 'workwx'
                );
            } else {
                $data['pic_url'] = $_POST['pic_url'];
            }
            $uploadM = $this->MODEL('upload');
            if (!empty($upArr)) {
                $pic = $uploadM->newUpload($upArr);
                if(!empty($pic['picurl'])){
                    $res = $workM->uploadImg(checkpic($pic['picurl']));
                    $data['pic_url'] = $res['url'];
                }
            }
        } else if ($info['msgtype'] == 2){
            if (empty($info['link_url']) || empty($info['link_title'])){
                $this->render_json(1, "图文附件：链接和标题不能为空");
            }else{
                $data['link_url'] = $info['link_url'];
                $data['link_title'] = $info['link_title'];
                $data['link_desc'] = $info['link_desc'];
                // 新上传图片文件处理
                foreach ($_FILES['linkpic'] as $nk => $nv) {
                    foreach ($nv as $nkk => $nvv) {
                        $linkpic_file[$nkk][$nk] = $nvv;
                    }
                }
                if($_FILES['linkpic']['tmp_name']){
                    $upArr = array(
                        'file' => $linkpic_file[0],
                        'dir' => 'workwx'
                    );
                    $uploadM = $this->MODEL('upload');
                    if (!empty($upArr)) {
                        $pic = $uploadM->newUpload($upArr);
                        if(!empty($pic['picurl'])){
                            $data['link_picurl'] = checkpic($pic['picurl']);
                        }
                    }
                } else {
                    $data['link_picurl'] = $info['link_picurl'];
                }
            }
        } else if ($info['msgtype'] == 3){
            $data['mini_title'] = $info['mini_title'];
            $data['mini_appid'] = $info['mini_appid'];
            $data['mini_page'] = $info['mini_page'];
            // 新上传图片文件处理
            foreach ($_FILES['minipic'] as $nk => $nv) {
                foreach ($nv as $nkk => $nvv) {
                    $minipic_file[$nkk][$nk] = $nvv;
                }
            }
            if($_FILES['minipic']['tmp_name']){
                $upArr = array(
                    'file' => $minipic_file[0],
                    'dir' => 'workwx'
                );
                $uploadM = $this->MODEL('upload');
                if (!empty($upArr)) {
                    $pic = $uploadM->newUpload($upArr);
                    if(!empty($pic['picurl'])){
                        $res = $workM->uploadMedia($pic['picurl']);
                        $data['mini_media'] = $res['media_id'];
                        $data['mini_picurl'] = $pic['picurl'];
                        $data['mini_endtime'] = $res['created_at'] + 86400 * 3;
                    }
                }
            } else {
                $data['mini_media'] = $info['mini_media'];
            }
        }
        if($info['id']){
            $return = $workM->upGroupWelcome($data, array('id' => intval($info['id']), 'template_id' => $info['template_id']));
        }else{
            $return = $workM->addGroupWelcome($data);
        }
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['errmsg']);
        } else {
            $this->render_json( 1, $return['errmsg']);
        }
    }

    /**
     * @desc 删除入群欢迎语
     */
    function delGroupWelcome_action()
    {
        $workM = $this->MODEL('workwx');
        $del = $_POST['del'];
        if (is_array($del)) {
            $where['id'] = array('in', pylode(',', $del));
        } else {
            $where['id'] = $del;
        }
        $return = $workM->delGroupWelcome($where);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['errmsg']);
        } else {
            $this->render_json(1, $return['errmsg']);
        }
    }
}
?>