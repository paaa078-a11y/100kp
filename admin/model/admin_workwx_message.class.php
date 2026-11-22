<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2022 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class admin_workwx_message_controller extends adminCommon
{

    /**
     * 企业群发
     */
    function index_action()
    {

        $search_list[]  =   array("param" => "chat_type", "name" => '推送类型', "value" => array("1" => "客户", "2" => "客户群"));
        $search_list[]  =   array("param" => "send", "name" => '是否推送', "value" => array("1" => "已推送", "2" => "未推送"));
        $this->yunset("search_list", $search_list);

        $where          =   array();

        $keyStr =   trim($_GET['keyword']);
        if (!empty($keyStr)){

            $where['name']      =   array('like', $keyStr);
            $urlarr['keyword']  =   $_GET['keyword'];
        }

        if ($_GET['chat_type']){

            $where['chat_type'] =   $_GET['chat_type'];
            $urlarr['chat_type']=   $_GET['chat_type'];
        }

        if (isset($_GET['send'])) {
            if ($_GET['send'] == 1) {

                $where['send']  =   1;
            } else if ($_GET['send'] == 2) {

                $where['send']  =   0;
            }

            $urlarr['send']     =   $_GET['send'];
        }

        $urlarr         =   $_GET;

        $urlarr['page'] =   "{{page}}";
        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');

        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->pageList('work_message', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['limit'] =   $pages['limit'];

            $workWxM        =   $this->MODEL('workwx');
            $messageList    =   $workWxM->getMessageList($where);
            $this->yunset('list', $messageList);
        }

        $this->yuntpl(array('admin/work/message'));
    }

    /**
     * 创建企业群发
     */
    function add_action()
    {

        $workM      =   $this->MODEL('workwx');

        if ($_GET['id']){

            $info   =   $workM->getMessageInfo(array('id' => $_GET['id']));

            $this->yunset('info', $info);
        }

        $this->yuntpl(array('admin/work/add_message'));
    }

    /**
     * 查询客户信息
     */
    function getWorkCustomer_action()
    {

        $customerArr=   $this->obj->select_all('work_customer', array('groupby' => 'userid'), '`userid`,`name`');

        if ($_POST['id'])
        {

            $task   =   $this->obj->select_once('work_message', array('id' => $_POST['id']), '`external_userid`');

            $userIdArr  =   explode(',', $task['external_userid']);
        }

        $userArr    =   array();
        foreach ($customerArr as $k => $v){

            $userArr[$k]['value']   =   $v['userid'];
            $userArr[$k]['name']    =   base64_decode($v['name']);

            if (isset($userIdArr) && !empty($userIdArr)){
                foreach ($userIdArr as $uk => $uv){
                    if ($v['userid'] == $uv){

                        $userArr[$k]['selected']    =   true;
                    }
                }
            }
        }
        echo json_encode($userArr);die;
    }

    /**
     * 查询员工信息
     */
    function getWorkUserId_action()
    {

        $workArr    =   $this->obj->select_all('work_user', array('is_follow' => 1), '`userid`,`name`');

        if ($_POST['id'])
        {

            $task   =   $this->obj->select_once('work_message', array('id' => $_POST['id']), '`sender`');

            $userIdArr  =   explode(',', $task['sender']);
        }

        $userArr    =   array();
        foreach ($workArr as $k => $v){

            $userArr[$k]['value']   =   $v['userid'];
            $userArr[$k]['name']    =   !empty($v['name']) ? base64_decode($v['name']) : $v['userid'];

            if (isset($userIdArr) && !empty($userIdArr)){
                foreach ($userIdArr as $uk => $uv){
                    if ($v['userid'] == $uv){

                        $userArr[$k]['selected']    =   true;
                    }
                }
            }
        }
        echo json_encode($userArr);die;
    }

    /**
     * 保存企业群发
     */
    function save_action()
    {

        $workM = $this->MODEL('workwx');

        $data = array();

        $data['name'] = $_POST['name'];
        $data['chat_type'] = $_POST['chat_type'];

        if ($_POST['chat_type'] == 1) {                  //  客户

            $data['external_userid'] = $_POST['userid'];
        } elseif ($_POST['chat_type'] == 2) {             //  客户群

            $data['sender'] = $_POST['sender'];
        }

        $data['content'] = $_POST['content'];
        $data['msgtype'] = $_POST['msgtype'];

        if (empty($data['content']) && empty($_POST['msgtype'])) {

            $this->ACT_layer_msg("文本消息内容和附件消息不能同时为空", 8, "index.php?m=admin_workwx_message");
        }

        if ($_POST['msgtype'] == 1){

            if ($_FILES['pic1']['tmp_name']) {
                $upArr  =   array(
                    'file'  =>  $_FILES['pic1'],
                    'dir'   =>  'workwx'
                );
            }
            $uploadM    =   $this->MODEL('upload');

            if (!empty($upArr)) {

                $pic    =   $uploadM->newUpload($upArr);

                if(!empty($pic['picurl'])){

                    $res    =   $workM->uploadImg(checkpic($pic['picurl']));

                    $data['pic_url']    =   $res['url'];
                }
            }
        } else if ($_POST['msgtype'] == 2){

            if (empty($_POST['link_url']) || empty($_POST['link_title']))
            {
                $this->ACT_layer_msg("链接消息：链接和标题不能为空",8,"index.php?m=admin_workwx_message");
            }else{

                $data['link_url']   =   $_POST['link_url'];
                $data['link_title'] =   $_POST['link_title'];

                $data['link_desc']  =   $_POST['link_desc'];

                if ($_FILES['pic2']['tmp_name']) {
                    $upArr  =   array(
                        'file'  =>  $_FILES['pic2'],
                        'dir'   =>  'workwx'
                    );
                }

                $uploadM    =   $this->MODEL('upload');

                if (!empty($upArr)) {

                    $pic    =   $uploadM->newUpload($upArr);

                    if(!empty($pic['picurl'])){

                        $data['link_picurl']    =   checkpic($pic['picurl']);
                    }
                }
            }
        } else if ($_POST['msgtype'] == 3){

            $data['mini_title'] =   $_POST['mini_title'];
            $data['mini_appid'] =   $_POST['mini_appid'];
            $data['mini_page']  =   $_POST['mini_page'];

            if ($_FILES['pic3']['tmp_name']) {
                $upArr  =   array(
                    'file'  =>  $_FILES['pic3'],
                    'dir'   =>  'workwx'
                );
            }
            $uploadM    =   $this->MODEL('upload');

            if (!empty($upArr)) {

                $pic    =   $uploadM->newUpload($upArr);

                if(!empty($pic['picurl'])){

                    $res    =   $workM->uploadMedia($pic['picurl']);

                    $data['mini_media']     =   $res['media_id'];
                    $data['mini_picurl']    =   $pic['picurl'];
                    $data['mini_endtime']   =   $res['created_at'] + 86400 * 3;
                }
            }
        }

        if($_POST['id']){

            $return =	$workM->upMessage($data, array('id'=>intval($_POST['id'])));
        }else{

            $return	=	$workM->addMessage($data);
        }

        if ($return['errcode'] == 9){

            $this->ACT_layer_msg($return['errmsg'],9,"index.php?m=admin_workwx_message",2,1);
        }else{

            $this->ACT_layer_msg($return['errmsg'],8,"index.php?m=admin_workwx_message");
        }
    }

    /**
     * @desc 删除企业群发
     */
    function delMessage_action()
    {

        $workM  =   $this->MODEL('workwx');

        $del    =   $_GET['del'];

        if (is_array($del)) {

            $where['id']    =   array('in', pylode(',', $del));
        } else {

            $where['id']    =   $del;
        }

        $return =   $workM->delMessage($where);

        $this->layer_msg($return['errmsg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }

    /**
     * @desc 创建企业群发（请求接口）
     */
    function addMsgTemplate_action()
    {

        if ($_POST['id']){

            $workM  =   $this->MODEL('workwx');

            $return =   $workM->addMsgTemplate(array('id' => $_POST['id']));

            echo json_encode($return);die;
        }
    }

    /**
     * 新客户欢迎语
     */
    function welcomeList_action()
    {

        $where  =   array('type' => 1);

        $keyStr =   trim($_GET['keyword']);
        if (!empty($keyStr)){

            $where['name']      =   array('like', $keyStr);
            $urlarr['keyword']  =   $_GET['keyword'];
        }

        $urlarr         =   $_GET;

        $urlarr['page'] =   "{{page}}";
        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');
        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->pageList('work_welcome', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['limit'] =   $pages['limit'];

            $workWxM        =   $this->MODEL('workwx');
            $messageList    =   $workWxM->getWelcomeList($where);
            $this->yunset('list', $messageList);
        }

        $this->yuntpl(array('admin/work/welcome_list'));
    }

    /**
     * 添加/修改 欢迎语
     */
    function addWelcome_action()
    {

        $workM      =   $this->MODEL('workwx');

        if ($_GET['id']){

            $info   =   $workM->getWelcomeInfo(array('id' => $_GET['id'], 'type' => 1));

            $this->yunset('info', $info);
        }

        $this->yuntpl(array('admin/work/add_welcome'));
    }

    /**
     * 查询客户信息
     */
    function getWorkUser_action()
    {

        $userList       =   $this->obj->select_all('work_user', array('is_follow' => '1'), '`userid`,`name`');

        if ($_POST['id'])
        {
            $welcome    =   $this->obj->select_once('work_welcome', array('id' => $_POST['id'], 'type' => 1), '`userid`');
            $userIdArr  =   explode(',', $welcome['userid']);

            $welcomeList    =   $this->obj->select_all('work_welcome', array('type' => 1, 'id' => array('<>', $_POST['id'])), '`userid`');
        }else{

            $welcomeList    =   $this->obj->select_all('work_welcome', array('type' => 1), '`userid`');
        }

        if (!empty($welcomeList)){

            $userIdInArr=   array();

            foreach ($welcomeList as $wk => $wv){

                $userIdInArr    =   array_merge($userIdInArr, explode(',', $wv['userid']));
            }
        }

        $userArr    =   array();
        foreach ($userList as $k => $v) {

            $userArr[$k]['value']   =   $v['userid'];
            $userArr[$k]['name']    =   !empty($v['name']) ? base64_decode($v['name']) : $v['userid'];

            if (isset($userIdArr) && !empty($userIdArr)) {
                foreach ($userIdArr as $uk => $uv) {
                    if ($v['userid'] == $uv) {

                        $userArr[$k]['selected']    =   true;
                    }
                }
            }

            if (isset($userIdInArr) && in_array($v['userid'], $userIdInArr)){
                unset($userArr[$k]);
            }
        }

        echo json_encode(array_values($userArr));
        die;
    }

    /**
     * 保存新客户欢迎语
     */
    function saveWelcome_action()
    {

        $workM  = $this->MODEL('workwx');

        $data   = array();

        $data['name']   =   $_POST['name'];
        $data['type']   =   1;

        $data['userid'] =   $_POST['userid'];
        $data['content']=   $_POST['content'];

        $data['msgtype']=   $_POST['msgtype'];

        if (empty($data['content']) && empty($_POST['msgtype'])) {

            $this->ACT_layer_msg("文本消息内容和附件消息不能同时为空", 8, "index.php?m=admin_workwx_message&c=welcomeList");
        }

        if ($_POST['msgtype'] == 1){

            if ($_FILES['pic1']['tmp_name']) {
                $upArr  =   array(
                    'file'  =>  $_FILES['pic1'],
                    'dir'   =>  'workwx'
                );
            }
            $uploadM    =   $this->MODEL('upload');

            if (!empty($upArr)) {

                $pic    =   $uploadM->newUpload($upArr);

                if(!empty($pic['picurl'])){

                    $res    =   $workM->uploadImg(checkpic($pic['picurl']));

                    $data['pic_url']    =   $res['url'];
                }
            }
        } else if ($_POST['msgtype'] == 2){

            if (empty($_POST['link_url']) || empty($_POST['link_title']))
            {
                $this->ACT_layer_msg("图文附件：链接和标题不能为空",8,"index.php?m=admin_workwx_message&c=welcomeList");
            }else{

                $data['link_url']   =   $_POST['link_url'];
                $data['link_title'] =   $_POST['link_title'];
                $data['link_desc']  =   $_POST['link_desc'];

                if ($_FILES['pic2']['tmp_name']) {
                    $upArr  =   array(
                        'file'  =>  $_FILES['pic2'],
                        'dir'   =>  'workwx'
                    );
                }

                $uploadM    =   $this->MODEL('upload');

                if (!empty($upArr)) {

                    $pic    =   $uploadM->newUpload($upArr);

                    if(!empty($pic['picurl'])){

                        $data['link_picurl']    =   checkpic($pic['picurl']);
                    }
                }
            }
        } else if ($_POST['msgtype'] == 3){

            $data['mini_title'] =   $_POST['mini_title'];
            $data['mini_appid'] =   $_POST['mini_appid'];
            $data['mini_page']  =   $_POST['mini_page'];

            if ($_FILES['pic3']['tmp_name']) {
                $upArr  =   array(
                    'file'  =>  $_FILES['pic3'],
                    'dir'   =>  'workwx'
                );
            }
            $uploadM    =   $this->MODEL('upload');

            if (!empty($upArr)) {

                $pic    =   $uploadM->newUpload($upArr);

                if(!empty($pic['picurl'])){

                    $res    =   $workM->uploadMedia($pic['picurl']);

                    $data['mini_media']     =   $res['media_id'];
                    $data['mini_picurl']    =   $pic['picurl'];
                    $data['mini_endtime']   =   $res['created_at'] + 86400 * 3;
                }
            }
        }

        if($_POST['id']){

            $return	=	$workM->upWelcome($data, array('id'=>intval($_POST['id'])));
        }else{

            $return	=	$workM->addWelcome($data);
        }

        if ($return['errcode'] == 9){

            $this->ACT_layer_msg($return['errmsg'],9,"index.php?m=admin_workwx_message&c=welcomeList",2,1);
        }else{

            $this->ACT_layer_msg($return['errmsg'],8,"index.php?m=admin_workwx_message&c=welcomeList");
        }
    }

    /**
     * @desc 删除欢迎语
     */
    function delWelcome_action()
    {

        $workM  =   $this->MODEL('workwx');

        $del    =   $_GET['del'];

        if (is_array($del)) {

            $where['id']    =   array('in', pylode(',', $del));
        } else {

            $where['id']    =   $del;
        }

        $return =   $workM->delWelcome($where);

        $this->layer_msg($return['errmsg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }

    /**
     * 客户群欢迎语
     */
    function welcomeGroupList_action()
    {

        $where  =   array('type' => 2);

        $keyStr =   trim($_GET['keyword']);
        if (!empty($keyStr)){

            $where['name']      =   array('like', $keyStr);
            $urlarr['keyword']  =   $_GET['keyword'];
        }

        $urlarr         =   $_GET;

        $urlarr['page'] =   "{{page}}";
        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');
        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->pageList('work_welcome', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['limit'] =   $pages['limit'];

            $workWxM        =   $this->MODEL('workwx');
            $messageList    =   $workWxM->getWelcomeGroupList($where);
            $this->yunset('list', $messageList);
        }

        $this->yuntpl(array('admin/work/welcome_group_list'));
    }

    /**
     * 添加/修改 客户群欢迎语
     */
    function addGroupWelcome_action()
    {

        $workM      =   $this->MODEL('workwx');

        if ($_GET['id']){

            $info   =   $workM->getWelcomeGroupInfo(array('id' => $_GET['id'], 'type' => 2));

            $this->yunset('info', $info);
        }

        $this->yuntpl(array('admin/work/add_group_welcome'));
    }

    /**
     * 保存群聊欢迎语
     */
    function saveGroupWelcome_action()
    {

        $workM  = $this->MODEL('workwx');

        $data   = array();

        $data['name']   =   $_POST['name'];
        $data['type']   =   2;

        $data['notify'] =   isset($_POST['notify']) ? 1 : 0;

        $data['content']=   $_POST['content'];

        $data['msgtype']=   $_POST['msgtype'];

        if (empty($data['content']) && empty($_POST['msgtype'])) {

            $this->ACT_layer_msg("文本消息内容和附件消息不能同时为空", 8, "index.php?m=admin_workwx_message&c=welcomeList");
        }

        if ($_POST['msgtype'] == 1){

            if ($_FILES['pic1']['tmp_name']) {
                $upArr  =   array(
                    'file'  =>  $_FILES['pic1'],
                    'dir'   =>  'workwx'
                );

                $uploadM    =   $this->MODEL('upload');

                if (!empty($upArr)) {

                    $pic    =   $uploadM->newUpload($upArr);

                    if(!empty($pic['picurl'])){

                        $res    =   $workM->uploadImg(checkpic($pic['picurl']));

                        $data['pic_url']    =   $res['url'];
                    }
                }
            }else{

                $data['pic_url']    =   $_POST['pic_url'];
            }
        } else if ($_POST['msgtype'] == 2){

            if (empty($_POST['link_url']) || empty($_POST['link_title']))
            {
                $this->ACT_layer_msg("图文附件：链接和标题不能为空",8,"index.php?m=admin_workwx_message&c=welcomeList");
            }else{

                $data['link_url']   =   $_POST['link_url'];
                $data['link_title'] =   $_POST['link_title'];
                $data['link_desc']  =   $_POST['link_desc'];

                if ($_FILES['pic2']['tmp_name']) {
                    $upArr  =   array(
                        'file'  =>  $_FILES['pic2'],
                        'dir'   =>  'workwx'
                    );
                    $uploadM    =   $this->MODEL('upload');

                    if (!empty($upArr)) {

                        $pic    =   $uploadM->newUpload($upArr);

                        if(!empty($pic['picurl'])){

                            $data['link_picurl']    =   checkpic($pic['picurl']);
                        }
                    }
                }else{

                    $data['link_picurl']    =   $_POST['link_picurl'];
                }
            }
        } else if ($_POST['msgtype'] == 3){

            $data['mini_title'] =   $_POST['mini_title'];
            $data['mini_appid'] =   $_POST['mini_appid'];
            $data['mini_page']  =   $_POST['mini_page'];

            if ($_FILES['pic3']['tmp_name']) {
                $upArr  =   array(
                    'file'  =>  $_FILES['pic3'],
                    'dir'   =>  'workwx'
                );

                $uploadM    =   $this->MODEL('upload');

                if (!empty($upArr)) {

                    $pic    =   $uploadM->newUpload($upArr);

                    if(!empty($pic['picurl'])){

                        $res    =   $workM->uploadMedia($pic['picurl']);

                        $data['mini_media']     =   $res['media_id'];
                        $data['mini_picurl']    =   $pic['picurl'];
                        $data['mini_endtime']   =   $res['created_at'] + 86400 * 3;
                    }
                }
            }else{

                $data['mini_media']     =   $_POST['mini_media'];
            }
        }

        if($_POST['id']){

            $return =	$workM->upGroupWelcome($data, array('id'=>intval($_POST['id']), 'template_id' => $_POST['template_id']));
        }else{

            $return =	$workM->addGroupWelcome($data);
        }
        if ($return['errcode'] == 9){

            $this->ACT_layer_msg($return['errmsg'],$return['errcode'],"index.php?m=admin_workwx_message&c=welcomeGroupList",2,1);
        }else{

            $this->ACT_layer_msg($return['errmsg'],$return['errcode'],"index.php?m=admin_workwx_message&c=welcomeGroupList");
        }
    }

    /**
     * @desc 删除欢迎语
     */
    function delGroupWelcome_action()
    {

        $workM  =   $this->MODEL('workwx');

        $del    =   $_GET['del'];

        if (is_array($del)) {

            $where['id']    =   array('in', pylode(',', $del));
        } else {

            $where['id']    =   $del;
        }

        $return =   $workM->delGroupWelcome($where);

        $this->layer_msg($return['errmsg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }

}

?>