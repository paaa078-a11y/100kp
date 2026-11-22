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
class workwx_model extends model
{

    /**
     * @desc 私有方法，获取企业微信Token
     * @param $type
     * @return mixed
     */
    private function getToken($type)
    {

        global $config;
        if (!$config) {
            include(PLUS_PATH.'config.php');
        }
        return getWxQyToken($type);
    }

    /**
     * @desc  验证URL有效性
     * @param array $data
     * @return string
     */
    public function valid($data = array())
    {

        include_once APP_PATH.'api/work/WXBizMsgCrypt.php';

        $encodingAesKey =   $this->config['wx_contact_EncodingAESKey'];
        $token          =   $this->config['wx_contact_token'];
        $corpId         =   $this->config['wx_qy_corpid'];

        $sVerifyMsgSig      =   $data['msg_signature'];
        $sVerifyTimeStamp   =   $data['timestamp'];
        $sVerifyNonce       =   $data['nonce'];
        $sVerifyEchoStr     =   $data['echostr'];

        // 需要返回的明文
        $sEchoStr   =   "";

        $wxcpt      =   new WXBizMsgCrypt($token, $encodingAesKey, $corpId);

        $errCode    =   $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

        if ($errCode == 0) {

            // 验证URL成功，将sEchoStr返回
            echo $sEchoStr; exit();
        } else {

            //  print("ERR: ".$errCode."\n\n");
        }
    }

    /**
     * @desc 事件回调
     * @param array $data
     * @param string $sReqData
     * @return void
     */
    public function callBackEvent($data = array(), $sReqData = '')
    {
        include_once APP_PATH.'api/work/WXBizMsgCrypt.php';

        $encodingAesKey =   $this->config['wx_contact_EncodingAESKey'];
        $token          =   $this->config['wx_contact_token'];
        $corpId         =   $this->config['wx_qy_corpid'];

        $sReqMsgSig     =   $data['msg_signature'];
        $sReqTimeStamp  =   $data['timestamp'];
        $sReqNonce      =   $data['nonce'];

        $wxcpt          =   new WXBizMsgCrypt($token, $encodingAesKey, $corpId);

        $sMsg           =   "";  // 解析之后的明文
        $errCode        =   $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);

        if ($errCode == 0) {

            libxml_disable_entity_loader(true);
            $sMsgObj    =   simplexml_load_string($sMsg, 'SimpleXMLElement', LIBXML_NOCDATA);

            $sMsgJsonObj=   json_encode($sMsgObj);
            $sMsgArr    =   json_decode($sMsgJsonObj, true);

            $event      =   $sMsgArr['Event'];
            $changeType =   $sMsgArr['ChangeType'];

            if ($event == 'change_external_contact') {                      //  **客户回调事件

                if ($changeType == 'add_external_contact') {                    //  1、添加企业客户事件

                    $this->addExternalContact($sMsgArr);
                } else if ($changeType == 'edit_external_contact') {            //  2、编辑企业客户事件

                } else if ($changeType == 'add_half_external_contact') {        //  3、外部联系人免验证添加成员事件

                    $this->addHalfExternalContact($sMsgArr);
                } else if ($changeType == 'del_external_contact') {             //  4、删除企业客户事件【员工删除客户】

                    $this->delExternalContact($sMsgArr);
                } else if ($changeType == 'del_follow_user') {                  //  5、删除跟进成员事件【客户删除员工】

                    $this->delFollowUser($sMsgArr);
                } else if ($changeType == 'transfer_fail') {                    //  6、客户接替失败事件

                }
            }else if ($event == 'change_external_chat'){                    //  **客户群回调事件

                if ($changeType == 'create') {                                  //  1、客户群创建事件

                    $this->createGroup($sMsgArr);
                } else if ($changeType == 'update') {                           //  2、客户群变更事件

                    $UpdateDetail = $sMsgArr['UpdateDetail'];                         //  ***变更详情

                    if ($UpdateDetail == 'add_member') {                            //  2-1、成员入群

                        $this->addGroupMember($sMsgArr);
                    } else if ($UpdateDetail == 'del_member') {                    //  2-2、成员退群

                        $this->delGroupMember($sMsgArr);
                    } else if ($UpdateDetail == 'change_owner') {                 //  2-3、群主变更

                        $this->changeGroupOwner($sMsgArr);
                    } else if ($UpdateDetail == 'change_name') {                  //  2-4、群名变更

                        $this->changeGroupName($sMsgArr);
                    } else if ($UpdateDetail == 'change_notice') {                //  2-5、群公告变更

                        $this->changeGroupNotice($sMsgArr);
                    }
                } else if ($changeType == 'dismiss') {                          //  3、客户群解散事件

                    $this->disMiss($sMsgArr);
                }
            } else if ($event == 'change_external_tag'){                    //  **客户标签回调事件

                if ($changeType == 'create') {                                  //  1、企业客户标签创建事件

                } elseif ($changeType == 'update') {                            //  1、企业客户标签变更事件

                } elseif ($changeType == 'delete') {                            //  1、企业客户标签删除事件

                } elseif ($changeType == 'shuffle') {                           //  1、企业客户标签去重事件

                }
            }

            $this->addCallBack($sMsgArr);

            echo ''; exit();
        } else {

            print("ERR: ".$errCode."\n\n");
        }
    }

    /**
     * @desc 企业微信上传图片【返回永久图片url】
     * @param $pic_path
     * @return mixed
     */
    public function uploadImg($pic_path)
    {

        $Token      =   $this->getToken('link');
        $qyUrl      =   "https://qyapi.weixin.qq.com/cgi-bin/media/uploadimg?access_token=".$Token;

        $file_data  =   array("media"  => new \CURLFile($pic_path));

        $CurlReturn =   CurlPost($qyUrl, $file_data);
        $return     =   json_decode($CurlReturn, true);

        return  $return;
    }

    /**
     * @desc 企业微信上传素材【返回media_id：三天有效期】
     * @param $pic_path
     * @return mixed
     */
    public function uploadMedia($pic_path)
    {

        //启用OSS需要先获取远程图片流临时保存至本地
        if ($this->config['sy_oss'] == 1) {

            $path   =   DATA_PATH.'upload/workxwx/'.time().'.png';
            $uimage =   curlget($this->config['sy_ossurl'].'/'.$pic_path);
            if ($uimage) {

                file_put_contents($path, $uimage);
                include_once(LIB_PATH.'upload.class.php');
                $wxupload = new Upload();
                list($width, $height, $type, $attr) = getimagesize($path);
                $path   =   $wxupload->makeThumb($path, $width, $height, '', true);
            }
        } else {

            $path   =   APP_PATH.$pic_path;
        }

        $Token      =   $this->getToken('link');
        $qyUrl      =   "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token=".$Token."&type=image";

        $file_data  =   array("media"  => new \CURLFile($path));

        $CurlReturn =   CurlPost($qyUrl, $file_data);
        $return     =   json_decode($CurlReturn, true);

        return  $return;
    }

    /**
     * @desc 计划任务：更新群聊欢迎语素材（小程序封面MEDIA_ID）
     */
    public function upTemporaryMedia()
    {

        $welcomeList    =   $this->select_all('work_welcome', array('type' => 2, 'msgtype' => 3, 'mini_endtime' => array('>', time())));

        if (!empty($welcomeList)){

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/group_welcome_template/edit?access_token='.$Token;

            foreach ($welcomeList as $k => $v) {

                $res    =   $this->uploadMedia($v['mini_picurl']);

                $postData   =   array();

                $postData['template_id']=   $v['template_id'];
                $postData['text']       =   array(
                    'content'   =>  str_replace('{用户昵称}', '%NICKNAME%', $v['content'])
                );
                $postData['miniprogram'] = array(

                    'title'         =>  $v['mini_title'],
                    'pic_media_id'  =>  $res['media_id'],
                    'appid'         =>  $v['mini_appid'],
                    'page'          =>  $v['mini_page']
                );

                $postJson   =   str_replace(' ', '', json_encode($postData));
                $CurlReturn =   CurlPost($qyUrl, $postJson);
                $return     =   json_decode($CurlReturn, true);

                if ($return['errcode'] == 0 && $return['errmsg'] == 'ok'){

                    $this->update_once('work_welcome', array('mini_media' => $res['media_id'], 'mini_endtime' => $res['created_at'] + 86400 * 3), array('id' => $v['id']));
                }
            }
        }
    }

    /**
     * @desc 同步企业员工userID
     * @param array $data
     * @return mixed
     */
    public function syncWorkUserId($data = array())
    {

        $Token  =   $this->getToken('contact'); //  通讯录Token
        $ygUrl  =   'https://qyapi.weixin.qq.com/cgi-bin/user/list_id?access_token='.$Token;    //  获取成员ID列表

        $postData   =   array('cursor' => $data['cursor'], 'limit' => $data['limit']);
        $postJson   =   str_replace(' ', '', json_encode($postData));
        $CurlReturn =   CurlPost($ygUrl, $postJson);
        $return     =   json_decode($CurlReturn, true);

        if ($return['errcode'] == 0 && $return['errmsg'] == 'ok' && !empty($return['dept_user'])) {

            $deptUser   =   $return['dept_user'];
            $Token      =   $this->getToken('link');    //  客户联系Token
            $followUrl  =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get_follow_user_list?access_token='.$Token;    //  获取配置了客户联系功能的成员列表

            $CurlReturn =   CurlGet($followUrl);
            $returnNew  =   json_decode($CurlReturn, true);
            $followUser =   array();
            if ($returnNew['errcode'] == 0 && $returnNew['errmsg'] == 'ok' && !empty($returnNew['follow_user'])) {

                $followUser =   $returnNew['follow_user'];
            }

            $userList   =   $this->select_all('work_user'); //  已经同步员工信息

            $newData    =   array();

            if (!empty($userList)) {

                $userIdArr  =   array();    //  已经同步员工userid
                $upOneArr   =   array();    //  需要更新配置‘客户联系’
                $upTwoArr   =   array();    //  需要取消配置‘客户联系’

                foreach ($userList as $uk => $uv) {

                    $userIdArr[]    =   $uv['userid'];
                }
                foreach ($deptUser as $k => $v) {
                    if (in_array($v['userid'], $userIdArr)){
                        foreach ($userList as $uk => $uv){
                            if (in_array($v['userid'], $followUser) && !in_array($v['userid'], $upOneArr) && $uv['is_follow'] != 1){
                                $upOneArr[] =   $v['userid'];
                            }
                            if (!in_array($v['userid'], $followUser) && !in_array($v['userid'], $upTwoArr) && $uv['is_follow'] == 1){
                                $upTwoArr[] =   $v['userid'];
                            }
                        }
                    }else{

                        $newData[$k]['userid']      =   $v['userid'];
                        $newData[$k]['is_follow']   =   in_array($v['userid'], $followUser) ? 1 : 2;
                        $newData[$k]['create_time'] =   time();
                    }
                }
            } else {

                foreach ($deptUser as $k => $v) {

                    $newData[$k]['userid']      =   $v['userid'];
                    $newData[$k]['is_follow']   =   in_array($v['userid'], $followUser) ? 1 : 2;
                    $newData[$k]['create_time'] =   time();
                }
            }

            if (!empty($newData)) {

                $newValue   =   array_values($newData);
                $this->DB_insert_multi('work_user', $newValue);
            }

            if (!empty($upOneArr)) {
                $newUpOneArr    =   array();
                foreach ($upOneArr as $oneV){
                    $newUpOneArr[]  =   "'".$oneV."'";
                }
                $this->update_once('work_user', array('is_follow' => 1), array('userid' => array('in', implode(',', $newUpOneArr))));
            }
            if (!empty($upTwoArr)) {
                $newUpTwoArr    =   array();
                foreach ($upTwoArr as $twoV){
                    $newUpTwoArr[]  =   "'".$twoV."'";
                }
                $this->update_once('work_user', array('is_follow' => 2), array('userid' => array('in', implode(',', $newUpTwoArr))));
            }
        }

        return $return;
    }

    /**
     * @desc 查询企员工列表
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWorkUserIdList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';
        $list   =   $this->select_all('work_user', $where, $filed);
        if (!empty($list)) {

            $userIds    =   array();
            foreach ($list as $k => $v) {
                if (!in_array($v['userid'], $userIds)) {

                    $userIds[]  =   $v['userid'];
                }
            }

            $newUserIds =   array();
            foreach ($userIds as $v){
                $newUserIds[]   =   "'".$v."'";
            }

            $customerList   =   $this->select_all('work_customer', array('userid' => array('in', implode(',', $newUserIds))), 'userid');

            foreach ($list as $k => $v) {

                $list[$k]['name_n'] =   $v['name'] ? base64_decode($v['name']) : '';
                $list[$k]['follow'] =   $v['is_follow'] == 1 ? '<img style="padding-right: 3px;" src="images/state2.png"> 已配置' : '<img style="padding-right: 3px;" src="images/state1.png"> 未配置';

                $list[$k]['customer_num']   =   0;
                foreach ($customerList as $ck => $cv) {
                    if ($v['userid'] == $cv['userid']) {

                        $list[$k]['customer_num']++;
                    }
                }

                $list[$k]['create_time_n']  =   date('Y-m-d H:i', $v['create_time']);
            }
        }
        return $list;
    }

    /**
     * @desc 员工列表数量统计
     * @return false|string
     */
    function userNum()
    {

        $arr    =   array();

        $allNum =   $this->select_num('work_user');
        $arr['allNum']  =   $allNum;

        $sqNum  =   $this->select_num('work_user', array('is_follow' => 1));
        $arr['sqNum']   =   $sqNum;

        return json_encode($arr);
    }

    /**
     * @desc 员工详情【获取「联系客户统计」数据】
     * @param $userId
     * @param $start_time
     * @param $end_time
     * @return array|bool|mixed
     */
    public function getUserBehaviorData($userId, $start_time = null, $end_time = null)
    {

        $Token      =   $this->getToken('link');
        $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get_user_behavior_data?access_token='.$Token;

        $end_time   =   $end_time ? $end_time : strtotime(date('Y-m-d'));
        $start_time =   $start_time ? $start_time : strtotime('- 29 day', $end_time);

        $postData   = array(
            'userid'    =>  [$userId],
            'start_time'=>  $start_time,
            'end_time'  =>  $end_time
        );

        $postJson   =   str_replace(' ', '', json_encode($postData));
        $CurlReturn =   CurlPost($qyUrl, $postJson);
        $return     =   json_decode($CurlReturn, true);


        if ($return['errcode'] == 0 && $return['errmsg'] == 'ok') {

            $behavior_data          =   $return['behavior_data'];

            $chat_cnt               =   0;
            $message_cnt            =   0;
            $negative_feedback_cnt  =   0;
            $new_apply_cnt          =   0;
            $new_contact_cnt        =   0;

            foreach ($behavior_data as $k => $v) {

                $chat_cnt               +=  $v['chat_cnt'];
                $message_cnt            +=  $v['message_cnt'];
                $negative_feedback_cnt  +=  $v['negative_feedback_cnt'];
                $new_apply_cnt          +=  $v['new_apply_cnt'];
                $new_contact_cnt        +=  $v['new_contact_cnt'];

                $behavior_data[$k]['time_n']    =   date('Y-m-d', $v['stat_time']);
            }

            $list   =   array(
                'chat_cnt'              =>  $chat_cnt,
                'message_cnt'           =>  $message_cnt,
                'negative_feedback_cnt' =>  $negative_feedback_cnt,
                'new_apply_cnt'         =>  $new_apply_cnt,
                'new_contact_cnt'       =>  $new_contact_cnt,
                'behavior_data'         =>  $behavior_data
            );

        } else {

            $list   =   array(
                'chat_cnt'              =>  0,
                'message_cnt'           =>  0,
                'negative_feedback_cnt' =>  0,
                'new_apply_cnt'         =>  0,
                'new_contact_cnt'       =>  0,
                'behavior_data'         =>  array()
            );
        }
		$workuser   =   $this->select_once('work_user', array('userid' => $userId), '`userid`, `name`, `mobile`');
        $workuser['name'] && $workuser['name']  =   base64_decode($workuser['name']);
        $list['work_user']  =   $workuser;
        return $list;
    }

    /**
     * @var array 客户来源
     */
    public $addWayArr = array(0 => '未知来源', 1 => '扫码二维码', 2 => '搜索手机号', 3 => '名片分享', 4 => '群聊', 5 => '手机通讯录', 6 => '微信联系人', 8 => '安装第三方应用时自动添加的客服人员', 9 => '搜索邮箱', 10 => '视频号添加', 11 => '通过日程参与人添加', 12 => '通过会议参与人添加', 13 => '添加微信好友对应的企业微信', 14 => '通过智慧硬件专属客服添加', 201 => '内部成员共享', 202 => '管理员/负责人分配');

    /**
     * @desc 同步客户列表信息 【批量获取客户详情：获取指定成员添加的客户信息列表。】
     * @param array $data: cursor-分页查询游标；limit-返回最大记录数（50-100）；userid-企业成员userid
     * @return mixed
     */
    public function syncCustomerList($data = array())
    {

        $Token = $this->getToken('link');    //  客户联系Token
        $qyUrl = 'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/batch/get_by_user?access_token='.$Token;   //  批量获取客户详情

        if (!empty($data['userid'])){

            $postData   =   array(
                'userid_list'   =>  [$data['userid']],
                'limit'         =>  $data['limit']
            );
        }else{

            $limit      =   array(($data['page'] - 1) * 100, 100);
            $workUser   =   $this->select_all('work_user', array('is_follow' => 1, 'orderby' => 'id, asc', 'limit' => $limit), 'userid');
            $workNum    =   $this->select_num('work_user', array('is_follow' => 1));
            $userIdArr  =   array();
            foreach ($workUser as $wk => $wv){
                $userIdArr[]    =   $wv['userid'];
            }
            $postData   =   array('userid_list' => $userIdArr, 'limit' => $data['limit']);
        }

        if (isset($data['cursor']) && !empty($data['cursor'])){

            $postData['cursor'] =   $data['cursor'];
        }

        $postJson   =   str_replace(' ', '', json_encode($postData));
        $CurlReturn =   CurlPost($qyUrl, $postJson);
        $return     =   json_decode($CurlReturn, true);

        if ($return['errcode'] == 0 && $return['errmsg'] == 'ok' && !empty($return['external_contact_list']))
        {

            $external_contact_list   =   $return['external_contact_list'];

            $newCustomer    =   array();

            foreach ($external_contact_list as $k => $v){

                $customer   =   $this->select_once('work_customer', array('external_userid' => $v['external_contact']['external_userid'], 'userid' => $v['follow_info']['userid']));

                if (empty($customer)){

                    $newCustomer[$k]['external_userid'] =   $v['external_contact']['external_userid'];
                    $newCustomer[$k]['name']            =   base64_encode($v['external_contact']['name']);
                    $newCustomer[$k]['avatar']          =   $v['external_contact']['avatar'];
                    $newCustomer[$k]['type']            =   $v['external_contact']['type']; //  1-微信用户；2-企业微信用户
                    $newCustomer[$k]['gender']          =   $v['external_contact']['gender'];
                    $newCustomer[$k]['unionid']         =   $v['external_contact']['unionid'];

                    $newCustomer[$k]['position']        =   $v['external_contact']['position'];
                    $newCustomer[$k]['corp_name']       =   $v['external_contact']['corp_name'];
                    $newCustomer[$k]['corp_full_name']  =   $v['external_contact']['corp_full_name'];

                    $newCustomer[$k]['userid']          =   $v['follow_info']['userid'];
                    $newCustomer[$k]['remark']          =   $v['follow_info']['remark'];
                    $newCustomer[$k]['description']     =   $v['follow_info']['description'];
                    $newCustomer[$k]['createtime']      =   $v['follow_info']['createtime'];

                    $newCustomer[$k]['state']           =   $v['follow_info']['state'];
                    $newCustomer[$k]['add_way']         =   $v['follow_info']['add_way'];
                    $newCustomer[$k]['uid']             =   0;
                    $newCustomer[$k]['usertype']        =   '';
                    $newCustomer[$k]['delete_time']     =   '';
                    $newCustomer[$k]['del_way']         =   '';

                    if ($v['external_contact']['unionid']) {

                        $user   =   $this->select_once('member', array('unionid' => $v['external_contact']['unionid']), '`uid`,`usertype`');
                        if (!empty($user)) {

                            $newCustomer[$k]['uid']     =   $user['uid'];
                            $newCustomer[$k]['usertype']=   $user['usertype'];
                        }
                    }
                }else{

                    $upData =   array(
                        'name'              =>  base64_encode($v['external_contact']['name']),
                        'type'              =>  $v['external_contact']['type'],
                        'avatar'            =>  $v['external_contact']['avatar'],
                        'gender'            =>  $v['external_contact']['gender'],
                        'unionid'           =>  $v['external_contact']['unionid'],

                        'position'          =>  $v['external_contact']['position'],
                        'corp_name'         =>  $v['external_contact']['corp_name'],
                        'corp_full_name'    =>  $v['external_contact']['corp_full_name'],

                        'remark'            =>  $v['follow_info']['remark'],
                        'description'       =>  $v['follow_info']['description'],
                        'createtime'        =>  $v['follow_info']['createtime'],

                        'state'             =>  $v['follow_info']['state'],
                        'add_way'           =>  $v['follow_info']['add_way']
                    );
                    if ($v['external_contact']['unionid']){

                        $user   =   $this->select_once('member', array('unionid' => $v['external_contact']['unionid']), '`uid`,`usertype`');
                        if (!empty($user)) {

                            $upData['uid']      =   $user['uid'];
                            $upData['usertype'] =   $user['usertype'];
                        }
                    }

                    $this->update_once('work_customer', $upData, array('external_userid' => $v['external_contact']['external_userid'], 'userid' => $v['follow_info']['userid']));
                }
            }

            if (!empty($newCustomer)){

                $this->DB_insert_multi('work_customer', array_values($newCustomer));
            }

            $newReturn      =   array(
                'errcode'   =>  $return['errcode'],
                'errmsg'    =>  $return['errmsg']
            );

            if (isset($return['next_cursor'])){

                $newReturn['cursor']=   $return['next_cursor'];
                $newReturn['page']  =   $data['page'];
            }
            if (empty($return['next_cursor']) && $workNum > 100){

                $newReturn['page']  =   $data['page'] + 1;
            }
        }else{

            $newReturn      =   array(
                'errcode'   =>  $return['errcode'],
                'errmsg'    =>  $return['errmsg']
            );
        }

        return  $newReturn;
    }

    /**
     * @desc 查询客户列表信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWorkCustomerList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_customer', $where, $filed);

        $externalUserIdArr  =   array();
        $unionidArr         =   array();

        foreach ($list as $k => $v) {

            if (!in_array($v['external_userid'], $externalUserIdArr)) {

                $externalUserIdArr[]    =   "'".$v['external_userid']."'";
            }

            if (!empty($v['unionid']) && !in_array($v['unionid'], $unionidArr)) {

                $unionidArr[]   =   "'".$v['unionid']."'";
            }
        }

        $groupMemberList    =   $this->select_all('work_group_member', array('userid' => array('in', implode(',', $externalUserIdArr))), '`userid`,`chat_id`');

        if (!empty($unionidArr)) {

            $memberList     =   $this->select_all('member', array('unionid' => array('in', implode(',', $unionidArr))), '`uid`,`usertype`, `unionid`, `reg_date`');
            if (!empty($memberList)){

                $cuids  =   array();
                $ruids  =   array();

                foreach ($memberList as $mk => $mv)
                {
                    if ($mv['usertype'] == 1){

                        $ruids[]    =   $mv['uid'];
                    }elseif ($mv['usertype'] == 2){

                        $cuids[]    =   $mv['uid'];
                    }
                }

                if (!empty($cuids)){
                    $comList    =   $this->select_all('company', array('uid' => array('in', pylode(',', $cuids))), '`uid`, `name`');
                }
                if (!empty($ruids)){
                    $resumeList =   $this->select_all('resume', array('uid' => array('in', pylode(',', $ruids))), '`uid`, `name`, `def_job`');
                }

                foreach ($memberList as $mk => $mv) {

                    if (!empty($comList)) {
                        foreach ($comList as $ck => $cv) {
                            if ($mv['uid'] == $cv['uid']) {

                                $memberList[$mk]['com_name'] = $cv['name'];
                            }
                        }
                    }

                    if (!empty($resumeList)) {
                        foreach ($resumeList as $rk => $rv) {
                            if ($mv['uid'] == $rv['uid']) {

                                $memberList[$mk]['r_name']  = $rv['name'];
                                $memberList[$mk]['def_job'] = $rv['def_job'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($list  as $k => $v) {

            $list[$k]['name']       =   base64_decode($v['name']);
            $list[$k]['chat_num']   =   0;

            $list[$k]['del_n']      =   '<img style="padding-right: 3px;" src="images/state2.png"> 未流失';
            if ($v['delete_time'] > 0){

                $list[$k]['del_n']  =   '<img style="padding-right: 3px;" src="images/state1.png"> 已流失';
            }

            if ($v['type'] == 1){

                $list[$k]['name_sub']       =   '<span style="color:#2DB34A"> @微信</span>';
            }elseif ($v['type'] == 2){
                if (!empty($v['corp_full_name'])) {

                    $list[$k]['name_sub']   =   '<span style="color:#F58E23"> @'.$v['corp_full_name'].'</span>';
                } else if (!empty($v['corp_name'])) {

                    $list[$k]['name_sub']   =   '<span style="color:#F58E23"> @'.$v['corp_name'].'</span>';
                } else {

                    $list[$k]['name_sub']   =   '<span style="color:#F58E23"> @企业微信</span>';
                }
            }

            if (!empty($groupMemberList)) {
                foreach ($groupMemberList as $gk => $gv){
                    if ($v['external_userid'] == $gv['userid']){

                        $list[$k]['chat_num']++;
                    }
                }
            }

            if (!empty($memberList)){
                foreach ($memberList as $mk => $mv){
                    if (!empty($v['unionid']) && $v['unionid'] == $mv['unionid'])
                    {

                        $list[$k]['uid']            =   $mv['uid'];
                        if ($mv['usertype'] == 1){

                            $list[$k]['usertype']   =   1;

                            $list[$k]['r_name']     =   $mv['r_name'];
                            $list[$k]['def_job']    =   $mv['def_job'];
                        }
                        if ($mv['usertype'] == 2){

                            $list[$k]['usertype']   =   2;
                            $list[$k]['com_name']   =   $mv['com_name'];
                        }

                        $list[$k]['reg_date_n']     =   date('Y-m-d H:i', $mv['reg_date']);
                    }
                }
            }
        }
        return  $list;
    }

    /**
     * @desc 客户列表数量统计
     * @param null $chatId
     * @return false|string
     */
    function customerNum($chatId = null)
    {

        $arr = array();

        $customerNum        =   $this->select_num('work_customer');
        $arr['customerNum'] =   $customerNum;

        $customerArr        =   $this->select_all('work_customer', array('groupby' => 'external_userid'), 'MAX(`id`) as `id`');
        $customerIdArr      =   array();
        foreach ($customerArr as $ck => $cv) {

            $customerIdArr[]=   $cv['id'];
        }
        $qcNum              =   $this->select_num('work_customer', array('id' => array('in', pylode(',', $customerIdArr))));
        $arr['qcNum']       =   $qcNum;

        $regNum             =   $this->select_num('work_customer', array('uid' => array('>', 0), 'id' => array('in', pylode(',', $customerIdArr))));
        $arr['regNum']      =   $regNum;

        $newNum             =   $this->select_num('work_customer', array('createtime' => array('>', strtotime(date('Y-m-d'))), 'id' => array('in', pylode(',', $customerIdArr))));
        $arr['newNum']      =   $newNum;

        $delNum             =   $this->select_num('work_customer', array('delete_time' => array('>', strtotime(date('Y-m-d')))));
        $arr['delNum']      =   $delNum;

        return json_encode($arr);
    }

    /**
     * @desc 查询客户详情信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWorkCustomerInfo($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';
        $info   =   $this->select_once('work_customer', $where, $filed);

        $info['name']   =   base64_decode($info['name']);

        if (!empty($info['unionid'])) {

            $member     =   $this->select_once('member', array('unionid' => $info['unionid']), '`uid`,`moblie`,`reg_date`');
            if (!empty($member)) {

                $comInfo    =   $this->select_once('company', array('uid' => $member['uid']), '`name`');
                $userInfo   =   $this->select_once('resume', array('uid' => $member['uid']), '`name`,`def_job`');

                if (!empty($userInfo['def_job'])) {

                    $resumeInfo =   $this->select_once('resume_expect', array('id' => $userInfo['def_job']), 'integrity');
                }

                $info['is_reg']     =   1;
                $info['reg_date_n'] =   date('Y-m-d H:i', $member['reg_date']);
                $info['com_name']   =   !empty($comInfo) ? $comInfo['name'] : '未绑定企业';
                $info['resume_name']=   !empty($userInfo['def_job']) ? $userInfo['name'] : '未创建简历';

                $info['integrity']  =   !empty($resumeInfo) ? $resumeInfo['integrity'] : '未创建简历';
                $info['moblie']     =   !empty($member['moblie']) ? $member['moblie'] : '尚未绑定手机号';
            } else {

                $info['is_reg']     =   2;
            }
        } else {

            $info['is_reg']         =   2;
        }

        $all        =   $this->select_all('work_customer', $where, '`add_way`, `userid`,`createtime`');
        $userIds    =   array();
        foreach ($all as $ak => $av) {
            if (!in_array($av['userid'], $userIds)){

                $userIds[]  =   "'".$av['userid']."'";
            }
        }
        $userList   =   $this->select_all('work_user', array('userid' => array('in', @implode(',', $userIds))), '`userid`, `name`');

        foreach ($all as $ak => $av) {
            $all[$ak]['way_n']          =   $this->addWayArr[$av['add_way']];
            $all[$ak]['create_time_n']  =   date('Y-m-d H:i', $av['createtime']);
            foreach ($userList as $uk => $uv){
                if ($av['userid'] == $uv['userid']){

                    $all[$ak]['name_n'] =   $uv['name'] ? base64_decode($uv['name']) : '';
                }
            }
        }

        $info['userList']   =   $all;

        $chatList           =   $this->select_all('work_group_member', array('userid' => $info['external_userid']), '`chat_id`,`join_time`');
        if (!empty($chatList)) {

            $chatIds        =   array();
            foreach ($chatList as $ck => $cv) {

                $chatIds[]  =   "'".$cv['chat_id']."'";
            }

            $groupList      =   $this->select_all('work_group', array('chat_id' => array('in', implode(',', $chatIds))), '`chat_id`,`name`,`owner`');

            foreach ($chatList as $ck => $cv) {

                $chatList[$ck]['join_time_n']   =   date('Y-m-d H:i', $cv['join_time']);

                foreach ($groupList as $gk => $gv) {

                    if ($cv['chat_id'] == $gv['chat_id']) {

                        $chatList[$ck]['name']  =   $gv['name'];
                        $chatList[$ck]['owner'] =   $gv['owner'];
                    }
                }
            }
        }

        $info['chatList']   =   $chatList;
        return $info;
    }

    /**
     * @desc 获取客户群列表
     * @param array $data
     * @return array
     */
    public function syncGroupChatList($data = array())
    {

        $limit      =   array(($data['page'] - 1) * 100, 100);
        $workUser   =   $this->select_all('work_user', array('is_follow' => 1, 'orderby' => 'id, asc', 'limit' => $limit), 'userid');
        $workNum    =   $this->select_num('work_user', array('is_follow' => 1));
        if (!empty($workUser)) {

            $userIds    =   array();
            foreach ($workUser as $wk => $wv) {

                $userIds[]  =   $wv['userid'];
            }
        }

        $Token      =   $this->getToken('link');    //  客户联系Token
        $groupUrl   =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/groupchat/list?access_token='.$Token;    //  获取客户群列表

        if (!empty($userIds)) {

            $postData['owner_filter']   =   array('userid_list' => $userIds);
        }

        if (isset($data['cursor']) && !empty($data['cursor'])) {

            $postData['cursor'] =   $data['cursor'];
        }

        $postData['limit']  =   $data['limit'];

        $postJson   =   json_encode($postData);
        $CurlReturn =   CurlPost($groupUrl, $postJson);
        $return     =   json_decode($CurlReturn, true);
        if ($return['errcode'] == 0 && $return['errmsg'] == 'ok' && !empty($return['group_chat_list'])) {

            $groupChatList  =   $return['group_chat_list'];
            $workGroup      =   $this->select_all('work_group', array(), '`id`, `chat_id`');

            if (!empty($workGroup)) {

                $workChatIds    =   array();
                foreach ($workGroup as $wk => $wv) {

                    $workChatIds[]  =   $wv['chat_id'];
                }

                foreach ($groupChatList as $gk => $gv) {

                    $groupChatInfo  =   $this->syncGroupChatInfo(array('chat_id' => $gv['chat_id']));   //  客户群详情
                    $memberList     =   $groupChatInfo['group_chat']['member_list'];
                    if (!in_array($gv['chat_id'], $workChatIds)) {

                        $newData    =   array();    //  新客户群
                        $newMData   =   array();    //  新客户群成员

                        foreach ($memberList as $mk => $mv) {

                            $newMData[$gk.$mk]['chat_id']   =   $gv['chat_id'];
                            $newMData[$gk.$mk]['userid']    =   $mv['userid'];
                            $newMData[$gk.$mk]['type']      =   $mv['type'];
                            $newMData[$gk.$mk]['unionid']   =   $mv['unionid'];

                            $newMData[$gk.$mk]['join_time'] =   $mv['join_time'];
                            $newMData[$gk.$mk]['join_scene']=   $mv['join_scene'];
                            $newMData[$gk.$mk]['invitor']   =   $mv['invitor']['userid'];

                            $newMData[$gk.$mk]['nickname']  =   base64_encode($mv['group_nickname']);
                            $newMData[$gk.$mk]['name']      =   base64_encode($mv['name']);
                        }

                        $newData[$gk]['chat_id']    =   $gv['chat_id'];
                        $newData[$gk]['name']       =   $groupChatInfo['group_chat']['name'];
                        $newData[$gk]['owner']      =   $groupChatInfo['group_chat']['owner'];
                        $newData[$gk]['create_time']=   $groupChatInfo['group_chat']['create_time'];
                        $newData[$gk]['status']     =   $gv['status'];
                        $newData[$gk]['notice']     =   $groupChatInfo['group_chat']['notice'];
                    } else {

                        $upData =   array(
                            'name'  =>  $groupChatInfo['group_chat']['name'],
                            'owner' =>  $groupChatInfo['group_chat']['owner'],
                            'status'=>  $gv['status'],
                            'notice'=>  $groupChatInfo['group_chat']['notice']
                        );
                        $this->update_once('work_group', $upData, array('chat_id' => $gv['chat_id']));

                        $newMData       =   array();
                        $memUserIdArr   =   array();

                        $customerList   =   $this->select_all('work_group_member', array('chat_id' => $gv['chat_id']), '`userid`, `join_time`,`unionid`,`delete_time`');
                        $cuserid        =   array();
                        if (!empty($customerList)) {
                            foreach ($customerList as $ck => $cv) {

                                $cuserid[]  =   $cv['userid'];
                            }
                        }

                        foreach ($memberList as $mk => $mv) {

                            $memUserIdArr[] =   $mv['userid'];

                            if (!in_array($mv['userid'], $cuserid)) {

                                $newMData[$mk]['chat_id']   =   $groupChatInfo['group_chat']['chat_id'];
                                $newMData[$mk]['userid']    =   $mv['userid'];
                                $newMData[$mk]['type']      =   $mv['type'];
                                $newMData[$mk]['unionid']   =   $mv['unionid'];
                                $newMData[$mk]['join_time'] =   $mv['join_time'];
                                $newMData[$mk]['join_scene']=   $mv['join_scene'];
                                $newMData[$mk]['state']     =   $mv['state'];
                                $newMData[$mk]['invitor']   =   $mv['invitor']['userid'];
                                $newMData[$mk]['name']      =   base64_encode($mv['name']);
                                $newMData[$mk]['nickname']  =   base64_encode($mv['group_nickname']);
                            } else {

                                foreach ($customerList as $ck => $cv) {

                                    if (($mv['join_time'] != $cv['join_time'] || $mv['unionid'] != $cv['unionid'] || $mv['state'] != $cv['state']) && $mv['userid'] == $cv['userid']) {

                                        $upMData    = array(

                                            'type'      =>  $mv['type'],
                                            'unionid'   =>  $mv['unionid'],
                                            'join_time' =>  $mv['join_time'],
                                            'join_scene'=>  $mv['join_scene'],
                                            'state'     =>  $mv['state'],
                                            'invitor'   =>  $mv['invitor']['userid'],
                                            'name'      =>  base64_encode($mv['name']),
                                            'nickname'  =>  base64_encode($mv['group_nickname'])
                                        );
                                        $this->update_once('work_group_member', $upMData, array('userid' => $mv['userid'], 'chat_id' => $groupChatInfo['group_chat']['chat_id']));
                                    }
                                }
                            }
                        }

                        foreach ($customerList as $ck => $cv) {
                            if (!in_array($cv['userid'], $memUserIdArr) && ($cv['delete_time'] == 0 || empty($cv['delete_time']))) {

                                $delData[] = $cv['userid'];
                            }
                        }
                    }
                }

                if (!empty($newData)) {

                    $this->DB_insert_multi('work_group', array_values($newData));
                }
                if (!empty($newMData)) {

                    $this->DB_insert_multi('work_group_member', array_values($newMData));
                }
                if (!empty($delData)) {
                    //  同步客户群信息，清理已退群信息
                    $newDelData =   array();
                    foreach ($delData as $nDelV) {
                        $newDelData[]   =   "'".$nDelV."'";
                    }
                    $this->update_once('work_group_member', array('delete_time' => time(), 'quit_scene' => 2), array('userid' => array('in', implode(',', $newDelData))));
                }
            } else {

                $newData    =   array();
                $newMData   =   array();

                foreach ($groupChatList as $gk => $gv) {

                    $groupChatInfo  =   $this->syncGroupChatInfo(array('chat_id' => $gv['chat_id']));
                    $memberList     =   $groupChatInfo['group_chat']['member_list'];

                    foreach ($memberList as $mk => $mv) {

                        $newMData[$gk.$mk]['chat_id']   =   $gv['chat_id'];
                        $newMData[$gk.$mk]['userid']    =   $mv['userid'];
                        $newMData[$gk.$mk]['type']      =   $mv['type'];
                        $newMData[$gk.$mk]['unionid']   =   $mv['unionid'];
                        $newMData[$gk.$mk]['join_time'] =   $mv['join_time'];
                        $newMData[$gk.$mk]['join_scene']=   $mv['join_scene'];
                        $newMData[$gk.$mk]['invitor']   =   $mv['invitor']['userid'];
                        $newMData[$gk.$mk]['nickname']  =   base64_encode($mv['group_nickname']);
                        $newMData[$gk.$mk]['name']      =   base64_encode($mv['name']);
                    }

                    $newData[$gk]['chat_id']    =   $gv['chat_id'];
                    $newData[$gk]['name']       =   $groupChatInfo['group_chat']['name'];
                    $newData[$gk]['owner']      =   $groupChatInfo['group_chat']['owner'];
                    $newData[$gk]['create_time']=   $groupChatInfo['group_chat']['create_time'];
                    $newData[$gk]['notice']     =   $groupChatInfo['group_chat']['notice'];
                    $newData[$gk]['status']     =   $gv['status'];
                }

                $this->DB_insert_multi('work_group', array_values($newData));
                $this->DB_insert_multi('work_group_member', array_values($newMData));
            }

            $newReturn = array(
                'errcode' => $return['errcode'],
                'errmsg' => $return['errmsg']
            );

            if (isset($return['next_cursor'])) {

                $newReturn['cursor'] = $return['next_cursor'];
                $newReturn['page'] = $data['page'];
            }

            if (empty($return['next_cursor']) && $workNum > 100) {

                $newReturn['page'] = $data['page'] + 1;
            }
        } else {

            $newReturn = array(
                'errcode' => $return['errcode'],
                'errmsg' => $return['errmsg']
            );
        }

        return $newReturn;
    }

    /**
     * @desc 获取客户群详情
     * @param array $data
     * @return mixed
     */
    private function syncGroupChatInfo($data = array())
    {

        $Token      =   $this->getToken('link');
        $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/groupchat/get?access_token='.$Token;

        $postData   =   array(
            'chat_id'   => $data['chat_id'],
            'need_name' =>  1
        );
        $postJson   =   str_replace(' ', '', json_encode($postData));
        $CurlReturn =   CurlPost($qyUrl, $postJson);
        $return     =   json_decode($CurlReturn, true);

        return  $return;
    }

    /**
     * @desc 查询群聊列表信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getGroupChatList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_group', $where, $filed);

        if (!empty($list)){

            $chatIds    =   array();
            $unionIds   =   array();
            $ownerS     =   array();
            foreach ($list as $k => $v) {

                if (!in_array($v['chat_id'], $chatIds)) {

                    $chatIds[]  =   "'".$v['chat_id']."'";
                }
                if (!in_array($v['owner'], $ownerS)){

                    $ownerS[]   =   "'".$v['owner']."'";
                }
            }

            $ownerList  =   $this->select_all('work_group_member', array('chat_id' => array('in', implode(',', $chatIds)), 'userid' => array('in', implode(',', $ownerS))), '`userid`, `name`');

            $allList    =   $this->select_all('work_group_member', array('chat_id' => array('in', implode(',', $chatIds)), 'groupby' => 'chat_id'), 'count(`chat_id`) as `num`, chat_id');

            $realWhere  =   array(
                'chat_id'           =>  array('in', implode(',', $chatIds)),
                'PHPYUNBTWSTART_A'  =>  '',
                'delete_time'       =>  array(
                    0 => array('isnull', '', ''),
                    1 => array('=', '0', 'OR')
                ),
                'PHPYUNBTWEND_A'    =>  '',
                'groupby'           =>  'chat_id'
            );
            $realList   =   $this->select_all('work_group_member', $realWhere, 'count(`chat_id`) as `num`, chat_id');
            $newList    =   $this->select_all('work_group_member', array('chat_id' => array('in', implode(',', $chatIds)), 'join_time' => array('>', strtotime(date('Y-m-d'))),  'groupby' => 'chat_id'), 'count(`chat_id`) as `num`, chat_id');
            $delList    =   $this->select_all('work_group_member', array('chat_id' => array('in', implode(',', $chatIds)), 'delete_time' => array('>', strtotime(date('Y-m-d'))),  'groupby' => 'chat_id'), 'count(`chat_id`) as `num`, chat_id');

            $uniList    =   $this->select_all('work_group_member', array('chat_id' => array('in', implode(',', $chatIds)), 'unionid' => array('<>', '')), '`chat_id`, `unionid`');
            $unionId2ChatId = array();
            foreach ($uniList as $uk => $uv) {
                $unionId2ChatId[$uv['unionid']] = $uv['chat_id'];
                if (!in_array($uv['unionid'], $unionIds) && !empty($uv['unionid'])){

                        $unionIds[] =   "'".$uv['unionid']."'";
                }
            }

            if (!empty($unionIds)) {

                $memberList =   $this->select_all('member', array('unionid' => array('in', implode(',', $unionIds))), '`unionid`,`uid`');

                $uids = array();
                $uid2UnionId = array();
                foreach ($memberList as $v) {
                    $uid2UnionId[$v['uid']] = $v['unionid'];
                    !in_array($v['uid'], $uids) && array_push($uids, $v['uid']);
                }

                $resumeExpList = $this->select_all('resume_expect', array('uid' => array('in', implode(',', $uids)),'groupby' => 'uid'),'count(`id`) as `num`,`uid`');

                foreach ($resumeExpList as $k => $v) {
                    $unionid = $uid2UnionId[$v['uid']];
                    $resumeExpList[$k]['chat_id'] = $unionId2ChatId[$unionid];
                }
                foreach ($uniList as $uk => $uv) {
                    foreach ($memberList as $mk => $mv) {
                        if ($uv['unionid'] == $mv['unionid']){

                            $uniList[$uk]['reg']    =   1;
                        }
                    }
                }

                $regList = array();
                foreach ($uniList as $uk => $uv)
                {

                    if ($uv['reg'] == 1){

                        $regList[$uv['chat_id']]['num']++;
                        $regList[$uv['chat_id']]['chat_id']  =   $uv['chat_id'];
                    }
                }
                $regList =   array_values($regList);
            }

            foreach ($list as $k => $v) {

                if (empty($v['name'])){

                    $list[$k]['name']   =   '群聊';
                }else{

                    $list[$k]['name']   =   $v['name'];
                }

                $list[$k]['number']     =   0;
                $list[$k]['real_num']   =   0;
                $list[$k]['new_num']    =   0;
                $list[$k]['del_num']    =   0;
                $list[$k]['reg_num']    =   0;
                $list[$k]['exp_num']    =   0;

                foreach ($ownerList as $ok => $ov) {
                    if ($v['owner'] == $ov['userid']){

                        $list[$k]['owner_name'] =   base64_decode($ov['name']);
                    }
                }

                foreach ($allList as $ack => $acv) {
                    if ($v['chat_id'] == $acv['chat_id']){

                        $list[$k]['number'] =   $acv['num'];    //  群总人数
                    }
                }
                foreach ($realList as $rk => $rv) {
                    if ($v['chat_id'] == $rv['chat_id']){

                        $list[$k]['real_num'] =   $rv['num'];    //  实际留存人数
                    }
                }
                if (!empty($newList)){
                    foreach ($newList as $nck => $ncv) {
                        if ($v['chat_id'] == $ncv['chat_id']){

                            $list[$k]['new_num']=   $ncv['num'];    //  今日新增
                        }
                    }
                }
                if (!empty($delList)){
                    foreach ($delList as $dk => $dv) {
                        if ($v['chat_id'] == $dv['chat_id']){

                            $list[$k]['del_num']=   $dv['num'];     //  今日退出
                        }
                    }
                }
                if (!empty($regList)){
                    foreach ($regList as $rk => $rv){
                        if ($v['chat_id'] == $rv['chat_id']){

                            $list[$k]['reg_num']=   $rv['num'];     //  注册用户
                        }
                    }
                }
                // 简历数统计
                if (!empty($resumeExpList)){
                    foreach ($resumeExpList as $ev) {
                        if ($v['chat_id'] == $ev['chat_id']){
                            $list[$k]['exp_num'] += $ev['num'];     //  简历数
                        }
                    }
                }
                $list[$k]['time_n'] =   date('Y-m-d H:i', $v['create_time']);
            }
        }

        return  $list;
    }

    /**
     * @desc 同步客户群信息
     * @param array $data
     * @param array $exData
     * @return bool
     */
    public function syncGroupInfo($data = array(), $exData = array())
    {

        if (!empty($data)){

            $groupChatInfo  =   $this->syncGroupChatInfo($data);    //  客户群详情

            if ($groupChatInfo['errcode'] == 0){

                $memberList     =   $groupChatInfo['group_chat']['member_list'];

                $newMData       =   array();

                $memUserIdArr   =   array();

                if (empty($exData['group_owner']) && empty($exData['group_name']) && empty($exData['group_notice'])){

                    $customerList   =   $this->select_all('work_group_member', array('chat_id' => $data['chat_id']), '`userid`,`join_time`,`unionid`,`delete_time`');
                    if (!empty($customerList)){

                        $cuserid    =   array();

                        foreach ($customerList as $ck => $cv){

                            $cuserid[]  =   $cv['userid'];
                        }
                    }

                    foreach ($memberList as $mk => $mv) {

                        $memUserIdArr[] =   $mv['userid'];

                        if (!in_array($mv['userid'], $cuserid)){

                            $newMData[$mk]['chat_id']   =   $groupChatInfo['group_chat']['chat_id'];
                            $newMData[$mk]['userid']    =   $mv['userid'];
                            $newMData[$mk]['type']      =   $mv['type'];
                            $newMData[$mk]['unionid']   =   $mv['unionid'];
                            $newMData[$mk]['join_time'] =   $mv['join_time'];
                            $newMData[$mk]['join_scene']=   $mv['join_scene'];
                            $newMData[$mk]['state']     =   $mv['state'];
                            $newMData[$mk]['invitor']   =   $mv['invitor']['userid'];

                            $newMData[$mk]['name']      =   base64_encode($mv['name']);
                            $newMData[$mk]['nickname']  =   base64_encode($mv['group_nickname']);
                        }

                        foreach ($customerList as $ck => $cv){

                            if ($mv['join_time'] != $cv['join_time'] && $mv['userid'] == $cv['userid']){

                                $upMData    =   array(
                                    'type'          =>  $mv['type'],
                                    'unionid'       =>  $mv['unionid'],
                                    'join_time'     =>  $mv['join_time'],
                                    'join_scene'    =>  $mv['join_scene'],
                                    'state'         =>  $mv['state'],
                                    'invitor'       =>  $mv['invitor']['userid'],
                                    'name'          =>  base64_encode($mv['name']),
                                    'nickname'      =>  base64_encode($mv['group_nickname']),
                                    'delete_time'   =>  '',
                                    'quit_scene'    =>  ''
                                );

                                $this->update_once('work_group_member', $upMData, array('userid' => $mv['userid'], 'chat_id' => $data['chat_id']));
                            }
                        }
                    }

                    foreach ($customerList as $ck => $cv){
                        if (!in_array($cv['userid'], $memUserIdArr) && ($cv['delete_time'] == 0 || empty($cv['delete_time']))){
                            $delData[]  =   "'".$cv['userid']."'";
                        }
                    }
                }

                $upData     =   array(

                    'name'      =>  $groupChatInfo['group_chat']['name'],
                    'owner'     =>  $groupChatInfo['group_chat']['owner'],
                    'status'    =>  $groupChatInfo['group_chat']['status'],
                    'notice'    =>  $groupChatInfo['group_chat']['notice']
                );
                if (!empty($newMData)){

                    $this->DB_insert_multi('work_group_member', array_values($newMData));
                }
                if (!empty($delData)){
                    //  同步客户群信息，清理已退群信息
                    $this->update_once('work_group_member', array('delete_time' => time(), 'quit_scene' => 2), array('userid' => array('in', implode(',', $delData))));
                }
                if (!empty($exData)){

                    if (isset($exData['join_scene'])){

                        $stateArr   =   array();
                        foreach ($newMData as $nk => $nv){
                            if (!empty($nv['state']) && !in_array($nv['state'], $stateArr)){

                                $stateArr[] =   "'".$nv['state']."'";
                            }
                        }

                        if (!empty($stateArr)){

                            $QRCodeList =   $this->select_all('work_qrcode', array('state' => array('in', implode(',', $stateArr))), '`config`');

                            if (!empty($QRCodeList)){

                                foreach ($QRCodeList as $qk => $qv){

                                    $this->getQRCodeByConfigId($qv['config']);
                                }
                            }
                        }
                    }

                    if (isset($exData['quit_scene'])) {     //  退群

                        $delMemberList  =   $this->select_all('work_group_member', array('chat_id' => $data['chat_id']), '`userid`,`delete_time`');

                        if (!empty($delMemberList)) {

                            $delUserIdArr = array();
                            foreach ($delMemberList as $dk => $dv) {
                                if (!in_array($dv['userid'], $memUserIdArr) && ($dv['delete_time'] == 0 || empty($dv['delete_time']))){

                                    $delUserIdArr[] = "'".$dv['userid']."'";
                                }
                            }

                            $this->update_once('work_group_member', array('delete_time' => $exData['create_time'], 'quit_scene' => $exData['quit_scene']), array('chat_id' => $data['chat_id'], 'userid' => array('in', implode(',', $delUserIdArr))));
                        }
                    }
                }

                $result     =   $this->update_once('work_group', $upData, array('chat_id' => $data['chat_id']));
            }else if($groupChatInfo['errcode'] == '49008'){ //  客户去已解散

                $result     =   $this->delete_all('work_group', array('chat_id' => $data['chat_id']));
                $this->delete_all('work_group_member', array('chat_id' => $data['chat_id']), '');
            }
        }

        return  $result;
    }

    /**
     * @desc 客户群详情
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getGroupChatInfo($where = array(), $data = array())
    {

        $filed      =   isset($data['filed']) ? $data['filed'] : '*';

        $chatInfo   =   $this->select_once('work_group', $where, $filed);

        $chatMember =   $this->select_all('work_group_member', array('chat_id' => $chatInfo['chat_id'], 'type' => 1), '`userid`, `name`');

        $memberNameArr          =   array();
        foreach ($chatMember as $k => $v){

            $memberNameArr[]    =   base64_decode($v['name']);

            if ($v['userid'] == $chatInfo['owner']){

                $chatInfo['owner_name'] =   base64_decode($v['name']);
            }
        }

        $chatInfo['memberName'] =   implode('、', $memberNameArr);

        return  $chatInfo;
    }

    /**
     * @desc 客户群 群成员信息  【待调整】
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getGroupMemberList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_group_member', $where, $filed);

        if (!empty($list)){

            $groupInfo  =   $this->select_once('work_group', array('chat_id' => $where['chat_id']), '`owner`');

            $invitorS   =   array();
            $unioindS   =   array();

            foreach ($list as $k => $v) {

                if (!in_array($v['invitor'], $invitorS))
                {
                    $invitorS[] =   "'".$v['invitor']."'";
                }
                if (!empty($v['unionid']) && !in_array($v['unionid'], $unioindS)) {

                    $unioindS[] =   "'".$v['unionid']."'";
                }
            }

            $invitList  =   $this->select_all('work_group_member', array('userid' => array('in', implode(',', $invitorS))), '`userid`, `name`');
            $newInvitor =   array();
            foreach ($invitList as $ik => $iv)
            {

                $newInvitor[$iv['userid']]  =   base64_decode($iv['name']);
            }

            $memberList =   $this->select_all('member', array('unionid' => array('in', implode(',', $unioindS))), '`uid`,`usertype`,`unionid`');
            if (!empty($memberList))
            {
                $ruids  =   array();
                foreach ($memberList as $mk => $mv) {

                    if ($mv['usertype'] == 1) {

                        $ruids[]    =   $mv['uid'];
                    }
                }

                if (!empty($ruids)){

                    $resumeList =   $this->select_all('resume_expect', array('uid' => array('in', pylode(',', $ruids)), 'defaults' => 1), 'uid, integrity');
                    $sqList     =   $this->select_all('userid_job', array('uid' => array('in', pylode(',', $ruids)), 'groupby' => 'uid'), 'count(`uid`) as num, uid');
                }

                foreach ($memberList as $mk => $mv) {

                    if (!empty($resumeList)){
                        foreach ($resumeList as $rk => $rv) {
                            if ($mv['uid'] == $rv['uid']){

                                $memberList[$mk]['integrity']   =   $rv['integrity'];
                            }
                        }
                    }

                    if (!empty($sqList)){
                        foreach ($sqList as $sk => $sv){
                            if ($mv['uid'] == $sv['uid']){

                                $memberList[$mk]['sq_num']   =   $sv['num'];
                            }
                        }
                    }
                }
            }

            foreach ($list as $k => $v) {

                $list[$k]['name']   =   base64_decode($v['name']);

                if ($v['userid'] == $groupInfo['owner']){

                    $list[$k]['type_n']     =   '<span style="color:#267EF0"> 群主</span>';
                }else if ($v['type'] == 1){

                    $list[$k]['type_n']     =   '<span style="color:#267EF0"> 成员</span>';
                }else if ($v['type'] == 2){
                    if (!empty($v['unionid'])) {

                        $list[$k]['type_n'] = '<span style="color:#2DB34A"> @微信</span>';
                    }else{

                        $list[$k]['type_n'] = '<span style="color:#F58E23"> @企业微信</span>';
                    }
                }
                $list[$k]['is_reg']         =   '未注册';
                $list[$k]['integrity']      =   '--';
                $list[$k]['sq_num']         =   '--';
                $list[$k]['sq_uid']         =   '';
                foreach ($memberList as $mk => $mv) {

                    if (!empty($v['unionid']) && $v['unionid'] == $mv['unionid']){
                        $list[$k]['is_reg']     =   '已注册';

                        if ($mv['integrity']){

                            $list[$k]['integrity']  =   $mv['integrity'] ? $mv['integrity'].'%' : '--';
                        }else{

                            $list[$k]['integrity']  =   '--';
                        }

                        if ($mv['sq_num']){

                            //$url                    =   'index.php?m=admin_comlog&uid='.$mv['uid'];

                            $list[$k]['sq_num']     =   $mv['sq_num'];
                            $list[$k]['sq_uid']     =   $mv['uid'];
                        }else{

                            $list[$k]['sq_num']     =   '--';
                        }
                    }
                }

                $list[$k]['join_time_n']    =   date('Y-m-d H:i', $v['join_time']);

                if ($v['join_scene'] == 1) {
                    if (!empty($v['invitor'])) {
                        $list[$k]['scene_n']    =   '由 <span style="color:#2DB34A">'.$newInvitor[$v['invitor']].'</span> 邀请入群';
                    } else {
                        $list[$k]['scene_n']    =   '由群成员邀请入群（直接邀请入群）';
                    }
                } elseif ($v['join_scene'] == 2) {
                    if (!empty($v['invitor'])) {
                        $list[$k]['scene_n']    =   '由 <span style="color:#2DB34A">'.$newInvitor[$v['invitor']].'</span> 邀请入群';
                    } else {
                        $list[$k]['scene_n']    =   '由群成员邀请入群（通过邀请链接入群）';
                    }
                } elseif ($v['join_scene'] == 3) {
                    if (!empty($v['invitor'])) {
                        $list[$k]['scene_n']    =   '通过扫描 <span style="color:#2DB34A">'.$newInvitor[$v['invitor']].'</span> 分享的二维码扫码进群';
                    } else {
                        $list[$k]['scene_n']    =   '通过扫描群二维码入群';
                    }
                }

                if ($v['delete_time'] > 0) {

                    $quit_n =   '【已退群】';
                    if ($v['quit_scene'] == 0) {
                        $quit_n .=  '自己退群';
                    } elseif ($v['quit_scene'] == 1) {
                        $quit_n .=  '群主/群管理移除';
                    } elseif ($v['quit_scene'] == 2) {
                        $quit_n .=  '退群通知不及时，同步清理';
                    }
                    $list[$k]['quit_n'] =   $quit_n.'；'.date('Y-m-d H:i', $v['delete_time']);
                } else {

                    $list[$k]['quit_n'] =   '未退群';
                }
            }
        }
        return  $list;
    }

    /**
     * @desc 群聊详情页面数据统计
     * @param $chatId
     * @return false|string
     */
    public function groupMemberNum($chatId)
    {

        $arr = array();

        $totalNum = $this->select_num('work_group_member', array('chat_id' => $chatId));
        $arr['totalNum'] = $totalNum;

        $where  =   array(
            'chat_id'           =>  $chatId,
            'PHPYUNBTWSTART_A'  =>  '',
            'delete_time'       =>  array(
                0   =>  array('isnull', '', ''),
                1   =>  array('=', 0, 'OR')
            ),
            'PHPYUNBTWEND_A'    =>  ''
        );
        $realNum = $this->select_num('work_group_member', $where);
        $arr['realNum'] = $realNum;

        $inNum = $this->select_num('work_group_member', array('chat_id' => $chatId, 'type' => 1));
        $arr['inNum'] = $inNum;

        $newNum = $this->select_num('work_group_member', array('chat_id' => $chatId, 'join_time' => array('>', strtotime(date('Y-m-d')))));
        $arr['newNum'] = $newNum;

        $delNum = $this->select_num('work_group_member', array('chat_id' => $chatId, 'delete_time' => array('>', strtotime(date('Y-m-d')))));
        $arr['delNum'] = $delNum;

        $uniList = $this->select_all('work_group_member', array('chat_id' => $chatId, 'unionid' => array('notnull')), '`unionid`');

        $unionidS = array();
        foreach ($uniList as $k => $v) {

            if (!empty($v['unionid']) && !in_array($v['unionid'], $unionidS)) {

                $unionidS[] = "'".$v['unionid']."'";
            }
        }
        $arr['regNum'] = $this->select_num('member', array('unionid' => array('in', implode(',', $unionidS))));

        return json_encode($arr);
    }

    /**
     * @desc 客户群活码查询 群聊信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWorkGroup($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_group', $where, $filed);

        if (!empty($list)){

            $chatIds    =   array();
            foreach ($list as $k => $v){

                $chatIds[]  =   "'".$v['chat_id']."'";
            }

            $mWhere     =   array(
                'chat_id' => array('in', implode(',', $chatIds)),
                'PHPYUNBTWSTART_A'  =>  '',
                'delete_time'   =>  array(
                    0   =>  array('isnull', '', ''),
                    1   =>  array('=', 0, 'OR')
                ),
                'PHPYUNBTWEND_A'    =>  '',
                'groupby'   =>  'chat_id'
            );

            $memberList =   $this->select_all('work_group_member', $mWhere, 'count(`chat_id`) as `num`, chat_id');

            if (isset($data['extra_id']) && !empty($data['extra_id'])) {

                $qrCodeInfo =   $this->select_once('work_qrcode', array('id' => $data['extra_id']), '`chat_id_list`');

                $chatIdListArr  =   explode(',', $qrCodeInfo['chat_id_list']);
            }

            foreach ($list as $k => $v){

                foreach ($memberList as $mk => $mv){

                    if ($v['chat_id'] == $mv['chat_id']){

                        if ((int)$mv['num'] >= 200){
                            if (!isset($chatIdListArr) || !in_array($v['chat_id'], $chatIdListArr))
                            unset($list[$k]);
                        }else {
                            $list[$k]['number'] = $mv['num'];    //  群总人数
                        }
                    }
                }
            }

            $list = array_values($list);
        }

        return $list;
    }

    /**
     * @desc 客户群活码列表
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getChatQRCodeList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_qrcode', $where, $filed);

        if (!empty($list))
        {
            $stateArr   =   array();

            foreach ($list as $k => $v){
                if (empty($v['qr_code'])){

                    $list[$k]['qr_code']    =   $this->getQRCodeByConfigId($v['config_id']);
                }
				if ($v['xcx_icon']) {
                    $list[$k]['xcx_icon'] = checkpic($v['xcx_icon']);
                }
                $list[$k]['create_time_n']  =   date('Y-m-d H:i', $v['create_time']);
                $list[$k]['chat_id_list_arr'] = explode(',', $v['chat_id_list']);

                $stateArr[] =   "'".$v['state']."'";
            }

            $allList        =   $this->select_all('work_group_member', array('state' => array('in', implode(',', $stateArr)), 'groupby' => 'state'), 'count(`state`) as `num`, state');

            $delList        =   $this->select_all('work_group_member', array('state' => array('in', implode(',', $stateArr)), 'delete_time' => array('>', 0), 'groupby' => 'state'), 'count(`state`) as `num`, state');

            foreach ($list as $k => $v) {

                if ($v['scene'] == 1){

                    $list[$k]['scene_n']    =   '小程序';
                }elseif ($v['scene'] == 2){

                    $list[$k]['scene_n']    =   '二维码';
                }

                $list[$k]['out_num'] = 0;
                $list[$k]['out_num'] = 0;
                $list[$k]['in_num'] = 0;

                foreach ($allList as $ack => $acv) {
                    if ($v['state'] == $acv['state']) {

                        $list[$k]['in_num']     =   $acv['num'];    //  入群人数
                    }
                }
                foreach ($delList as $dck => $dcv) {
                    if ($v['state'] == $dcv['state']) {

                        $list[$k]['out_num']    =   $dcv['num'];    //  退群人数
                    }
                }
            }
        }

        return $list;
    }

    /**
     * @desc 客户群活码详情
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getChatQRCodeInfo($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';
        $info   =   $this->select_once('work_qrcode', $where, $filed);
        return $info;
    }

    /**
     * @desc 客户群活码数据分析
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getQRCodeCustomerList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_group_member', $where, $filed);

        return  $list;
    }

    /**
     * @desc 查询客户群活码【获取客户群进群方式配置】
     * @param $configId
     * @return mixed
     */
    private function getQRCodeByConfigId($configId)
    {

        $Token      =   $this->getToken('link');
        $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/groupchat/get_join_way?access_token='.$Token;

        $postData   =   array('config_id' => $configId);

        $CurlReturn =   CurlPost($qyUrl, json_encode($postData));
        $return     =   json_decode($CurlReturn, true);


        if ($return['errcode'] == 0 && $return['errmsg'] == 'ok'){

            $this->update_once('work_qrcode', array('qr_code' => $return['join_way']['qr_code'], 'chat_id_list' => implode(',', $return['join_way']['chat_id_list'])), array('config_id' => $configId));
        }

        return  $return['join_way']['qr_code'];
    }

    /**
     * @desc 创建企业群活码【配置客户入群联系方式】
     * @param array $data
     * @return bool
     */
    public function addChatQRCode($data = array())
    {

        if (!empty($data)){

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/groupchat/add_join_way?access_token='.$Token;

            $postData   =   array(
                'scene'             =>  $data['scene'],
                'remark'            =>  $data['remark'],
                'auto_create_room'  =>  $data['auto_create_room'] == 1 ? 1 : 0,
                'chat_id_list'      =>  explode(',', $data['chat_id_list']),
                'state'             =>  $data['state']
            );

            if ($data['auto_create_room'] == 1){
                $postData['room_base_id']   =   $data['room_base_id'];
                $postData['room_base_name'] =   $data['room_base_name'];
            }

            $CurlReturn =   CurlPost($qyUrl, json_encode($postData));
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok') {

                if ($data['xcx_use'] == 1){

                    $this->update_once('work_qrcode', array('xcx_use' => 0));
                }

                $value  =   array(
                    'name'              =>  $data['name'],
                    'config_id'         =>  $return['config_id'],
                    'scene'             =>  $data['scene'],
                    'remark'            =>  $data['remark'],
                    'auto_create_room'  =>  $data['auto_create_room'],
                    'room_base_name'    =>  $data['room_base_name'],
                    'room_base_id'      =>  $data['room_base_id'],
                    'chat_id_list'      =>  $data['chat_id_list'],
                    'state'             =>  $data['state'],
                    'xcx_use'           =>  $data['xcx_use'],
                    'xcx_icon'          =>  $data['xcx_icon'],
                    'xcx_title'         =>  $data['xcx_title'],
                    'create_time'       =>  time()
                );
                $nid    =   $this->insert_into('work_qrcode', $value);

                if (intval($data['xcx_use']) == 1){

                    $this->update_once('work_qrcode', array('xcx_use' => 0), array('id' => array('<>', $nid)));
                }

                $return =   array('errcode' => 9, 'errmsg' => '客户群活码（ID：'.$nid.'）添加成功！');
            }else{

                $return =   array('errcode' => 8, 'errmsg' => '客户群活码添加失败！错误码：'.$return['errcode'].'；提示：'.$return['errmsg']);
            }
        }else{

            $return =   array('errcode' => 8, 'errmsg' => '参数错误，请稍候重试！');
        }
        return  $return;
    }

    /**
     * @desc 更新客户群活码【更新客户群进群方式配置】
     * @param array $data
     * @param array $where
     * @return array|mixed
     */
    public function upChatQRCode($data = array(), $where = array())
    {

        if (!empty($data)){

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/groupchat/update_join_way?access_token='.$Token;

            $postData   =   array(
                'config_id'         =>  $data['config_id'],
                'scene'             =>  $data['scene'],
                'remark'            =>  $data['remark'],
                'auto_create_room'  =>  $data['auto_create_room'] == 1 ? 1 : 0,
                'chat_id_list'      =>  explode(',', $data['chat_id_list']),
                'state'             =>  $data['state']
            );

            if ($data['auto_create_room'] == 1){
                $postData['room_base_id']   =   $data['room_base_id'];
                $postData['room_base_name'] =   $data['room_base_name'];
            }

            $CurlReturn =   CurlPost($qyUrl, json_encode($postData));
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok') {

                $value  =   array(
                    'name'              =>  $data['name'],
                    'remark'            =>  $data['remark'],
                    'scene'             =>  $data['scene'],
                    'auto_create_room'  =>  $data['auto_create_room'],
                    'room_base_name'    =>  $data['room_base_name'],
                    'room_base_id'      =>  $data['room_base_id'],
                    'chat_id_list'      =>  $data['chat_id_list'],
                    'state'             =>  $data['state'],
                    'qr_code'           =>  '',
                    'xcx_use'           =>  $data['xcx_use'],
                    'xcx_icon'          =>  $data['xcx_icon'],
                    'xcx_title'         =>  $data['xcx_title'],
                    'up_time'           =>  time()
                );
                $this->update_once('work_qrcode', $value, $where);

                if (intval($data['xcx_use']) == 1){

                    $this->update_once('work_qrcode', array('xcx_use' => 0), array('id' => array('<>', $where['id'])));
                }
                $return =   array('errcode' => 9, 'errmsg' => '客户群活码（ID：'.$where['id'].'）更新成功！');
            }else{

                $return =   array('errcode' => 8, 'errmsg' => '客户群活码（ID：'.$where['id'].'）更新失败！错误码：'.$return['errcode'].'；提示：'.$return['errmsg']);
            }
        }else{

            $return =   array('errcode' => 8, 'errmsg' => '参数错误，请稍候重试！');
        }
        return  $return;
    }

    /**
     * @desc 删除客户群活码【删除客户群进群方式配置】
     * @param array $where
     * @return mixed
     */
    public function delChatQRCode($where = array())
    {
        $return['layertype']    =   0;

        if (!empty($where)) {

            if (!empty($where['id']) && $where['id'][0] == 'in') {
                $return['layertype']    =   1;
            }

            $qrCodeInfo =   $this->select_once('work_qrcode', $where, '`config_id`');

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/groupchat/del_join_way?access_token='.$Token;

            $postData   =   array(
                'config_id' =>  $qrCodeInfo['config_id'],
            );

            $CurlReturn =   CurlPost($qyUrl, json_encode($postData));
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok') {

                $this->delete_all('work_qrcode', $where, '');

                $return['errmsg']   =   '客户群活码删除成功！';
                $return['errcode']  =   9;
            }else{

                $return['errmsg']   =   '客户群活码删除失败！错误码：'.$return['errcode'].'；提示：'.$return['errmsg'];
                $return['errcode']  =   8;
            }
        } else {

            $return['errmsg']       =   '请选择您要删除的客户群活码信息！';
            $return['errcode']      =   8;
        }

        return $return;
    }

    /**
     * @desc 客户渠道码列表
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getChatCodeList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_code', $where, $filed);

        if (!empty($list))
        {
            $stateArr   =   array();

            foreach ($list as $k => $v){

                $list[$k]['create_time_n']  =   date('Y-m-d H:i', $v['create_time']);

                $stateArr[] =   "'".$v['state']."'";
				if ($v['user']) {
                    $list[$k]['user_arr'] = explode(',', $v['user']);
                }
			}

            $customerList   =   $this->select_all('work_customer', array('state' => array('in', implode(',', $stateArr)), 'groupby' => 'state'), 'count(state) as `num`, `state`');
            $customerDList  =   $this->select_all('work_customer', array('state' => array('in', implode(',', $stateArr)), 'delete_time' => array('>', 0), 'groupby' => 'state'), 'count(state) as `num`, `state`');

            foreach ($list as $k => $v) {

                $list[$k]['num']    =   0;
                $list[$k]['dnum']   =   0;
                if (!empty($customerList)){
                    foreach ($customerList as $ck => $cv) {
                        if ($v['state'] == $cv['state']) {

                            $list[$k]['num']    =   $cv['num'];
                        }
                    }
                }
                if (!empty($customerDList)){
                    foreach ($customerDList as $dk => $dv) {
                        if ($v['state'] == $dv['state']) {

                            $list[$k]['num']    =   $dv['num'];
                        }
                    }
                }
            }
        }

        return $list;
    }

    /**
     * @desc 客户渠道码详情
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getChatCodeInfo($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $info   =   $this->select_once('work_code', $where, $filed);

        return $info;
    }

    /**
     * @desc 客户渠道码数据分析
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getCodeCustomerList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_customer', $where, $filed);

        return  $list;
    }

    /**
     * @desc 创建客户渠道码【配置客户联系「联系我」方式】
     * @param array $data
     * @return bool
     */
    public function addChatCode($data = array())
    {

        if (!empty($data)){

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/add_contact_way?access_token='.$Token;

            $postData   =   array(
                'type'              =>  $data['type'],
                'scene'             =>  2,
                'remark'            =>  $data['remark'],
                'skip_verify'       =>  $data['skip_verify'] == 1 ? false : true,
                'state'             =>  $data['state'],
                'user'              =>  explode(',', $data['user'])
            );


            $CurlReturn =   CurlPost($qyUrl, json_encode($postData));
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok') {

                $value  =   array(
                    'name'              =>  $data['name'],
                    'config_id'         =>  $return['config_id'],
                    'type'              =>  $data['type'],
                    'scene'             =>  2,
                    'remark'            =>  $data['remark'],
                    'skip_verify'       =>  $data['skip_verify'],
                    'state'             =>  $data['state'],
                    'qr_code'           =>  $return['qr_code'],
                    'user'              =>  $data['user'],
                    'create_time'       =>  time()
                );
                $nid    =   $this->insert_into('work_code', $value);

                $return =   array('errcode' => 9, 'errmsg' => '客户渠道码（ID'.$nid.'）添加成功！');
            }else{

                $return =   array('errcode' => 8, 'errmsg' => '客户渠道码添加失败！错误码：'.$return['errcode'].'；提示：'.$return['errmsg']);
            }
        }else{

            $return     =   array('errcode' => 8, 'errmsg' => '参数错误，请稍候重试！');
        }

        return  $return;
    }

    /**
     * @desc 更新客户渠道码【配置客户联系「联系我」方式】
     * @param array $data
     * @param array $where
     * @return array|mixed
     */
    public function upChatCode($data = array(), $where = array())
    {

        if (!empty($data)){

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/update_contact_way?access_token='.$Token;

            $postData   =   array(
                'config_id'         =>  $data['config_id'],
                'remark'            =>  $data['remark'],
                'skip_verify'       =>  $data['skip_verify'] == 1 ? false : true,
                'state'             =>  $data['state'],
                'user'              =>  explode(',', $data['user'])
            );

            $CurlReturn =   CurlPost($qyUrl, json_encode($postData));
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok') {

                $value  =   array(
                    'name'              =>  $data['name'],
                    'config_id'         =>  $data['config_id'],
                    'remark'            =>  $data['remark'],
                    'skip_verify'       =>  $data['skip_verify'],
                    'state'             =>  $data['state'],
                    'user'              =>  $data['user'],
                    'up_time'           =>  time()
                );
                $this->update_once('work_code', $value, $where);

                $return =   array('errcode' => 9, 'errmsg' => '客户渠道码（ID'.$where['id'].'）添加成功！');
            }else{

                $return =   array('errcode' => 8, 'errmsg' => '客户渠道码（ID'.$where['id'].'）更新失败！错误码：'.$return['errcode'].'；提示：'.$return['errmsg']);
            }
        }else{

            $return     =   array('errcode' => 8, 'errmsg' => '参数错误，请稍候重试！');
        }

        return  $return;
    }

    /**
     * @desc 删除客户渠道码【删除企业已配置的「联系我」方式】
     * @param array $where
     * @return mixed
     */
    public function delChatCode($where = array())
    {
        $return['layertype']    =   0;

        if (!empty($where)) {

            if (!empty($where['id']) && $where['id'][0] == 'in') {
                $return['layertype']    =   1;
            }

            $codeInfo   =   $this->select_once('work_code', $where, '`config_id`');

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/del_contact_way?access_token='.$Token;

            $postData   =   array(
                'config_id' =>  $codeInfo['config_id'],
            );

            $CurlReturn =   CurlPost($qyUrl, json_encode($postData));
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok') {

                $this->delete_all('work_code', $where, '');

                $return['errmsg']   =   '客户渠道码删除成功！';
                $return['errcode']  =   9;
            }else{

                $return['errmsg']   =   '客户渠道码删除失败！错误码：'.$return['errcode'].'；提示：'.$return['errmsg'];
                $return['errcode']  =   8;
            }
        } else {

            $return['errmsg']       =   '请选择您要删除的客户渠道码信息！';
            $return['errcode']      =   8;
        }

        return $return;
    }

    /**
     * @desc 查询消息推送任务列表信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getMessageList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_message', $where, $filed);

        if (!empty($list)) {

            foreach ($list as $k => $v) {
				$list[$k]['external_userid_arr'] = $v['external_userid'] ? explode(',', $v['external_userid']) : array();// 客户信息
                $list[$k]['chat_type_n']    =   $v['chat_type'] == 1 ? '群发客户' : '群发客户群';

                if (!empty($v['content'])){

                    if ($v['msgtype'] == 0){

                        $list[$k]['msgtype_n']  =   '文本消息';
                    }elseif ($v['msgtype'] == 1){

                        $list[$k]['msgtype_n']  =   '文本消息 + 附件（图片）';
                    }else if ($v['msgtype'] == 2){

                        $list[$k]['msgtype_n']  =   '文本消息 + 附件（链接）';
                    }else if ($v['msgtype'] == 3){

                        $list[$k]['msgtype_n']  =   '文本消息 + 附件（小程序）';
                    }
                }else{

                    if ($v['msgtype'] == 1){

                        $list[$k]['msgtype_n']  =   '附件（图片）';
                    }else if ($v['msgtype'] == 2){

                        $list[$k]['msgtype_n']  =   '附件（链接）';
                    }else if ($v['msgtype'] == 3){

                        $list[$k]['msgtype_n']  =   '附件（小程序）';
                    }
                }

                $list[$k]['create_time_n']  =   date('Y-m-d H:i', $v['create_time']);
				$list[$k]['mini_picurl_n']  =   $v['mini_picurl'] ? checkpic($v['mini_picurl']) : '';

                if ($v['send'] == 1){

                    $list[$k]['send_time_n']    =   date('Y-m-d H:i', $v['send_time']);
                }else{

                    $list[$k]['send_time_n']    =   '--';
                }

            }
        }

        return  $list;
    }

    /**
     * @desc 查询消息推送任务信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getMessageInfo($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $info   =   $this->select_once('work_message', $where, $filed);

        $info['mini_picurl_n']  =   checkpic($info['mini_picurl']);

        return  $info;
    }

    /**
     * @desc 添加群发任务【招聘平台】
     * @param array $data
     * @return array
     */
    public function addMessage($data = array())
    {

        if (!empty($data)) {


            $data['create_time'] = time();

            $nid    =   $this->insert_into('work_message', $data);

            $return['errcode']  =   $nid ? 9 : 8;
            $return['errmsg']   =   $nid ? '企业群发创建成功！' : '企业群发创建失败！';
        }else{

            $return =   array('errcode' => 8, 'errmsg' => '参数错误，请稍候重试！');
        }

        return  $return;
    }

    /**
     * @desc 修改群发任务【招聘平台】
     * @param array $data
     * @param array $where
     * @return array
     */
    public function upMessage($data = array(), $where = array())
    {

        if (!empty($data) && !empty($where)){

            $result =   $this->update_once('work_message', $data, $where);

            $return['errcode']  =   $result ? 9 : 8;
            $return['errmsg']   =   $result ? '企业群发更新成功！' : '企业群发更新失败！';
        }else {

            $return = array('errcode' => 8, 'errmsg' => '参数错误，请稍候重试！');
        }
        return  $return;
    }

    /**
     * @desc 创建企业群发【企业微信】
     * @param array $data
     * @return mixed
     */
    public function addMsgTemplate($data = array())
    {

        $info   =   $this->select_once('work_message', array('id' => $data['id']));

        if (!empty($info))
        {

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/add_msg_template?access_token='.$Token;

            $postData   =   array();

            if ($info['chat_type'] == 1){

                $postData['chat_type']          =   'single';
                $postData['external_userid']    =   explode(',', $info['external_userid']);
            }else if ($info['chat_type'] == 2){

                $postData['chat_type']  =   'group';
                $postData['sender']     =   $info['sender'];
            }

            $postData['text']   =   array(

                'content'   =>  $info['content']
            );

            if ($info['msgtype'] == 1) {

                $postData['attachments'][]  =   array(
                    'msgtype'   =>  'image',
                    'image'     =>  array(
                        'pic_url'   =>  $info['pic_url']
                    )
                );
            } else if ($info['msgtype'] == 2) {

                $postData['attachments'][]  =   array(
                    'msgtype'   =>  'link',
                    'link'      =>  array(
                        'title' =>  $info['link_title'],
                        'picurl'=>  $info['link_picurl'],
                        'desc'  =>  $info['link_desc'],
                        'url'   =>  $info['link_url']
                    )
                );
            } else if ($info['msgtype'] == 3) {

                $postData['attachments'][]  =   array(
                    'msgtype'       =>  'miniprogram',
                    'miniprogram'   =>  array(
                        'title'         =>  $info['mini_title'],
                        'pic_media_id'  =>  $info['mini_media'],
                        'appid'         =>  $info['mini_appid'],
                        'page'          =>  $info['mini_page']
                    )
                );
            }

            $postJson   =   str_replace(' ', '', json_encode($postData));
            $CurlReturn =   CurlPost($qyUrl, $postJson);
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok'){

                $upData =   array(
                    'send'      =>  1,
                    'send_time' =>  time(),
                    'msgid'     =>  $return['msgid']
                );

                if (!empty($return['fail_list']))
                {
                    $upData['fail_list']    =   implode(',', $return['fail_list']);
                }

                $this->update_once('work_message', $upData, array('id' => $data['id']));
            }

            return  $return;
        }
    }

    /**
     * @desc 删除推送任务信息【招聘平台】
     * @param array $where
     * @return mixed
     */
    public function delMessage($where = array())
    {

        $return['layertype']    =   0;

        if (!empty($where)) {

            if (!empty($where['id']) && $where['id'][0] == 'in') {
                $return['layertype']    =   1;
            }

            $return['id']       =   $this->delete_all('work_message', $where, '');
            $return['errmsg']   =   '推送任务';
            $return['errcode']  =   $return['id'] ? '9' : '8';
            $return['errmsg']   =   $return['id'] ? $return['errmsg'].'删除成功！' : $return['errmsg'].'删除失败！';
        } else {

            $return['errmsg']   =   '请选择您要删除的推送信息！';
            $return['errcode']  =   8;
        }

        return $return;
    }

    /**
     * @desc 新客户欢迎语列表
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWelcomeList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_welcome', $where, $filed);

        if (!empty($list)) {

            $userIdArr  =   array();
            foreach ($list as $k => $v){

                $userIdArr  =   array_merge($userIdArr, explode(',', $v['userid']));
            }
            $userIdNew      =   array();
            foreach ($userIdArr as $v){
                $userIdNew[]    =   "'".$v."'";
            }

            $userList       =   $this->select_all('work_user', array('userid' => array('in', implode(',', $userIdNew))), '`userid`,`name`');

            foreach ($list as $k => $v) {
				$list[$k]['userid_arr'] = $v['userid'] ? explode(',', $v['userid']) : array();
                if (!empty($v['content'])){

                    if ($v['msgtype'] == 0){

                        $list[$k]['msgtype_n']  =   '文本消息';
                    }elseif ($v['msgtype'] == 1){

                        $list[$k]['msgtype_n']  =   '文本消息 + 附件（图片）';
                    }else if ($v['msgtype'] == 2){

                        $list[$k]['msgtype_n']  =   '文本消息 + 附件（链接）';
                    }else if ($v['msgtype'] == 3){

                        $list[$k]['msgtype_n']  =   '文本消息 + 附件（小程序）';
                    }
                }else{

                    if ($v['msgtype'] == 1){

                        $list[$k]['msgtype_n']  =   '附件（图片）';
                    }else if ($v['msgtype'] == 2){

                        $list[$k]['msgtype_n']  =   '附件（链接）';
                    }else if ($v['msgtype'] == 3){

                        $list[$k]['msgtype_n']  =   '附件（小程序）';
                    }
                }

                $list[$k]['create_time_n']  =   date('Y-m-d H:i', $v['create_time']);
                $list[$k]['up_time_n']      =   $v['up_time'] ? date('Y-m-d H:i', $v['up_time']) : '--';
				$list[$k]['mini_picurl_n'] = $v['mini_picurl'] ? checkpic($v['mini_picurl']) : '';

                $userIdList =   explode(',', $v['userid']);
                $userNameA  =   array();
                foreach ($userIdList as $uk => $uv){
                    foreach ($userList as $uuk => $uuv){
                        if ($uv == $uuv['userid']){

                            $userNameA[]    =   !empty($uuv['name']) ? base64_decode($uuv['name']) : $uuv['userid'];
                        }
                    }
                }
                $list[$k]['u_name'] =   implode(',', $userNameA);
            }
        }

        return  $list;
    }

    /**
     * @desc 新客户欢迎语详情
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWelcomeInfo($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $info   =   $this->select_once('work_welcome', $where, $filed);

        $info['mini_picurl_n']  =   checkpic($info['mini_picurl']);

        return  $info;
    }

    /**
     * @desc 添加新客户欢迎语【招聘平台】
     * @param array $data
     * @return array
     */
    public function addWelcome($data = array())
    {

        if (!empty($data)){

            $data['create_time']=   time();
            $data['up_time']    =   time();

            $nid                =   $this->insert_into('work_welcome', $data);

            $return['errcode']  =   $nid ? 9 : 8;
            $msg                =   '新客户欢迎语';
            $return['errmsg']   =   $nid ? $msg.'添加成功！' : $msg.'添加失败！';
        }else{

            $return =   array('errcode' => 8, 'errmsg'  =>  '参数错误，请稍候重试！');
        }

        return  $return;
    }

    /**
     * @desc 修改新客户欢迎语【招聘平台】
     * @param array $data
     * @param array $where
     * @return array
     */
    public function upWelcome($data = array(), $where = array())
    {

        if (!empty($data) && !empty($where)) {

            $data['up_time']    =   time();
            $result             =   $this->update_once('work_welcome', $data, $where);

            $return['errcode']  =   $result ? 9 : 8;
            $msg                =   '新客户欢迎语（ID：'.$where['id'].'）';
            $return['errmsg']   =   $result ? $msg.'更新成功！' : $msg.'更新失败！';
        }else{

            $return =   array('errcode' => 8, 'errmsg' => '参数错误，请稍候重试！');
        }

        return  $return;
    }

    /**
     * @desc 删除欢迎语信息【招聘平台】
     * @param array $where
     * @return mixed
     */
    public function delWelcome($where = array())
    {

        $return['layertype']    =   0;

        if (!empty($where)) {

            if (!empty($where['id']) && $where['id'][0] == 'in') {
                $return['layertype']    =   1;
            }

            $result             =   $this->delete_all('work_welcome', $where, '');
            $msg                =   '新客户欢迎语';
            $return['errcode']  =   $result ? '9' : '8';
            $return['errmsg']   =   $result ? $msg.'删除成功！' : $msg.'删除失败！';
        } else {

            $return['errmsg']      =   '请选择您要删除的欢迎语信息！';
            $return['errcode']  =   8;
        }

        return $return;
    }

    /**
     * @desc 查询群聊迎语列表信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWelcomeGroupList($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $list   =   $this->select_all('work_welcome', $where, $filed);

        if (!empty($list)) {


            foreach ($list as $k => $v) {

                if (!empty($v['content'])){

                    if ($v['msgtype'] == 0){

                        $list[$k]['msgtype_n']  =   '文本消息';
                    }elseif ($v['msgtype'] == 1){

                        $list[$k]['msgtype_n']  =   '文本消息 + 附件（图片）';
                    }else if ($v['msgtype'] == 2){

                        $list[$k]['msgtype_n']  =   '文本消息 + 附件（链接）';
                    }else if ($v['msgtype'] == 3){

                        $list[$k]['msgtype_n']  =   '文本消息 + 附件（小程序）';
                    }
                }else{

                    if ($v['msgtype'] == 1){

                        $list[$k]['msgtype_n']  =   '附件（图片）';
                    }else if ($v['msgtype'] == 2){

                        $list[$k]['msgtype_n']  =   '附件（链接）';
                    }else if ($v['msgtype'] == 3){

                        $list[$k]['msgtype_n']  =   '附件（小程序）';
                    }
                }

                $list[$k]['create_time_n']  =   date('Y-m-d H:i', $v['create_time']);
                $list[$k]['up_time_n']      =   $v['up_time'] ? date('Y-m-d H:i', $v['up_time']) : '--';
            }
        }

        return  $list;
    }

    /**
     * @desc 查询新客户欢迎语信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWelcomeGroupInfo($where = array(), $data = array())
    {

        $filed  =   isset($data['field']) ? $data['field'] : '*';

        $info   =   $this->select_once('work_welcome', $where, $filed);

        $info['mini_picurl_n']  =   checkpic($info['mini_picurl']);

        return  $info;
    }

    /**
     * @desc 添加群聊欢迎语素材【企业微信+招聘平台】
     * @param array $data
     * @return bool
     */
    public function addGroupWelcome($data = array())
    {

        if (!empty($data)){

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/group_welcome_template/add?access_token='.$Token;

            $postData   =   array();

            $postData['text']   =   array(

                'content'   =>  str_replace('{用户昵称}', '%NICKNAME%', $data['content'])
            );

            if ($data['msgtype'] == 1) {

                $postData['image']   =   array(
                    'pic_url'   =>  $data['pic_url']
                );
            } else if ($data['msgtype'] == 2) {

                $postData['link']   =   array(
                    'title'     =>  $data['link_title'],
                    'picurl'    =>  $data['link_picurl'],
                    'desc'      =>  $data['link_desc'],
                    'url'       =>  $data['link_url']
                );
            } else if ($data['msgtype'] == 3) {

                $postData['miniprogram']    =   array(

                    'title'         =>  $data['mini_title'],
                    'pic_media_id'  =>  $data['mini_media'],
                    'appid'         =>  $data['mini_appid'],
                    'page'          =>  $data['mini_page']
                );
            }

            $postData['notify'] =   $data['notify'];

            $postJson   =   str_replace(' ', '', json_encode($postData));
            $CurlReturn =   CurlPost($qyUrl, $postJson);
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok'){

                $data['template_id']    =   $return['template_id'];
                $data['create_time']    =   time();
                $this->insert_into('work_welcome', $data);

                $return['errmsg']   =   '入群欢迎语素材添加成功！';
                $return['errcode']  =   9;
            }else{

                $return['errmsg']   =   '入群欢迎语素材添加失败！错误码：'.$return['errcode'].'；提示：'.$return['errmsg'];
                $return['errcode']  =   8;
            }
        }else{

            $return['errcode']  =   8;
            $return['errmsg']   =   '参数错误，请稍候重试！';
        }

        return $return;
    }

    /**
     * @desc 修改群聊欢迎语素材【企业微信+招聘平台】
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function upGroupWelcome($data = array(), $where = array())
    {

        if (!empty($data) && !empty($where)){

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/group_welcome_template/edit?access_token='.$Token;

            $postData   =   array();

            $postData['template_id']    =   $where['template_id'];

            $postData['text']   =   array(

                'content'   =>  str_replace('{用户昵称}', '%NICKNAME%', $data['content'])
            );

            if ($data['msgtype'] == 1) {

                $postData['image']   =   array(
                    'pic_url'   =>  $data['pic_url']
                );
            } else if ($data['msgtype'] == 2) {

                $postData['link']   =   array(
                    'title'     =>  $data['link_title'],
                    'picurl'    =>  $data['link_picurl'],
                    'desc'      =>  $data['link_desc'],
                    'url'       =>  $data['link_url']
                );
            } else if ($data['msgtype'] == 3) {

                $postData['miniprogram']    =   array(

                    'title'         =>  $data['mini_title'],
                    'pic_media_id'  =>  $data['mini_media'],
                    'appid'         =>  $data['mini_appid'],
                    'page'          =>  $data['mini_page']
                );
            }

            $postJson   =   str_replace(' ', '', json_encode($postData));
            $CurlReturn =   CurlPost($qyUrl, $postJson);
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok'){

                $data['up_time']    =   time();
                $this->update_once('work_welcome', $data, $where);

                $return['errmsg']   =   '群聊欢迎语素材更新成功！';
                $return['errcode']  =   9;
            }else{

                $return['errmsg']   =   '群聊欢迎语素材更新失败！错误码：'.$return['errcode'].'；提示：'.$return['errmsg'];
                $return['errcode']  =   8;
            }
        }else{

            $return['errcode']  =   8;
            $return['errmsg']   =   '参数错误，请稍候重试！';
        }

        return $return;
    }

    /**
     * @desc 删除群聊欢迎语素材【企业微信+招聘平台】
     * @param array $where
     * @return mixed
     */
    public function delGroupWelcome($where = array())
    {

        $return['layertype']    =   0;

        if (!empty($where)) {

            if (!empty($where['id']) && $where['id'][0] == 'in') {
                $return['layertype']    =   1;
            }

            $groupWel   =   $this->select_once('work_welcome', $where, '`template_id`');

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/group_welcome_template/del?access_token='.$Token;

            $postData   =   array();

            $postData['template_id']    =   $groupWel['template_id'];

            $postJson   =   str_replace(' ', '', json_encode($postData));
            $CurlReturn =   CurlPost($qyUrl, $postJson);
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok'){

                $this->delete_all('work_welcome', $where, '');

                $return['errmsg']   =   '群聊欢迎语素材删除成功！';
                $return['errcode']  =   9;
            }else{

                $return['errmsg']   =   '群聊欢迎语素材删除失败！错误码：'.$return['errcode'].'；提示：'.$return['errmsg'];
                $return['errcode']  =   8;
            }
        } else {

            $return['errmsg']      =   '请选择您要删除的群聊欢迎语素材！';
            $return['errcode']  =   8;
        }

        return $return;
    }

    /**
     * @desc 添加外部联系人
     * @param array $data
     * @return mixed
     */
    private function addExternalContact($data = array())
    {

        $UserId         =   $data['UserID'];
        $ExternalUserID =   $data['ExternalUserID'];

        $customer       =   $this->select_once('work_customer', array('external_userid' => $ExternalUserID, 'userid' => $UserId));

        //  同步客户信息
        if (empty($customer))
        {

            $return =   $this->syncCustomer($ExternalUserID);
            if ($return['errcode'] == 0) {

                foreach ($return['follow_user'] as $k => $v){

                    $newCustomer[$k]['external_userid'] = $return['external_contact']['external_userid'];
                    $newCustomer[$k]['name'] = base64_encode($return['external_contact']['name']);
                    $newCustomer[$k]['avatar'] = $return['external_contact']['avatar'];
                    $newCustomer[$k]['type'] = $return['external_contact']['type']; //  1-微信用户；2-企业微信用户
                    $newCustomer[$k]['gender'] = $return['external_contact']['gender'];
                    $newCustomer[$k]['unionid'] = $return['external_contact']['unionid'];

                    $newCustomer[$k]['position'] = $return['external_contact']['position'];
                    $newCustomer[$k]['corp_name'] = $return['external_contact']['corp_name'];
                    $newCustomer[$k]['corp_full_name'] = $return['external_contact']['corp_full_name'];

                    $newCustomer[$k]['userid'] = $v['userid'];
                    $newCustomer[$k]['remark'] = $v['remark'];
                    $newCustomer[$k]['description'] = $v['description'];
                    $newCustomer[$k]['createtime'] = $v['createtime'];

                    $newCustomer[$k]['state'] = $v['state'];
                    $newCustomer[$k]['add_way'] = $v['add_way'];

                    $newCustomer[$k]['uid'] = 0;
                    $newCustomer[$k]['usertype'] = '';
                    $newCustomer[$k]['delete_time'] = '';
                    $newCustomer[$k]['del_way'] = '';

                    if ($return['external_contact']['unionid']) {

                        $user   =   $this->select_once('member', array('unionid' => $return['external_contact']['unionid']), '`uid`,`usertype`');
                        if (!empty($user)) {

                            $newCustomer[$k]['uid'] = $user['uid'];
                            $newCustomer[$k]['usertype'] = $user['usertype'];
                        }
                    }
                }

                $this->DB_insert_multi('work_customer', array_values($newCustomer));
            }
        }

        if (isset($data['WelcomeCode']) && !empty($data['WelcomeCode'])){

            $WelcomeCode    =   $data['WelcomeCode'];

            $welcome        =   $this->select_once('work_welcome', array('type' => 1, 'userid' => array('findin', $WelcomeCode), 'orderby' => 'up_time'));
            if (!empty($welcome))
            {
                if ($welcome['msgtype'] == 3 && $welcome['mini_endtime'] > time())
                {

                    $res    =   $this->uploadMedia($welcome['mini_picurl']);
                    $welcome['mini_media']  =   $res['media_id'];
                    $this->update_once('work_welcome', array('mini_media' => $res['media_id'], 'mini_endtime' => $res['created_at'] + 8600 * 3), array('id' => $welcome['id']));
                }

                $Token      =   $this->getToken('link');
                $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/send_welcome_msg?access_token='.$Token;

                $postData   =   array(
                    'welcome_code'  =>  $WelcomeCode
                );
                if (!empty($welcome['content'])){
                    $postData['text']   =   array(
                        'content'   =>  str_replace('{用户昵称}', '%NICKNAME%', $welcome['content'])
                    );
                }
                if ($welcome['msgtype'] == 1) {

                    $postData['attachments']    =   array(
                        'msgtype'   =>  'image',
                        'image'     =>  array(
                            'pic_url'   =>  $welcome['pic_url']
                        )
                    );
                } elseif ($welcome['msgtype'] == 2) {

                    $postData['attachments']    =   array(
                        'msgtype'   =>  'link',
                        'link'      =>  array(
                            'title' =>  $welcome['link_title'],
                            'picurl'=>  $welcome['link_picurl'],
                            'desc'  =>  $welcome['link_desc'],
                            'url'   =>  $welcome['link_url']
                        )
                    );
                } elseif ($welcome['msgtype'] == 3) {

                    if ($welcome['mini_endtime'] < time()){
                        $postData['attachments']    =   array(
                            'msgtype'       =>  'miniprogram',
                            'miniprogram'   =>  array(
                                'title'         =>  $welcome['mini_title'],
                                'pic_media_id'  =>  $welcome['mini_media'],
                                'appid'         =>  $welcome['link_desc'],
                                'page'          =>  $welcome['link_url']
                            )
                        );
                    }
                }
                $postJson   =   str_replace(' ', '', json_encode($postData));
                $CurlReturn =   CurlPost($qyUrl, $postJson);
                $return     =   json_decode($CurlReturn, true);
                //  TODO

                return $return['errcode'];
            }
        }
    }

    /**
     * @desc 外部联系人免验证添加成员事件
     * @param array $data
     * @return mixed
     */
    private function addHalfExternalContact($data = array())
    {

        $UserId         =   $data['UserID'];
        $ExternalUserID =   $data['ExternalUserID'];

        $customer       =   $this->select_once('work_customer', array('external_userid' => $ExternalUserID, 'userid' => $UserId));

        //  同步客户信息
        if (empty($customer))
        {
            $return =   $this->syncCustomer($ExternalUserID);
            if ($return['errcode'] == 0) {

                foreach ($return['follow_user'] as $k => $v){

                    $newCustomer[$k]['external_userid'] = $return['external_contact']['external_userid'];
                    $newCustomer[$k]['name'] = base64_encode($return['external_contact']['name']);
                    $newCustomer[$k]['avatar'] = $return['external_contact']['avatar'];
                    $newCustomer[$k]['type'] = $return['external_contact']['type']; //  1-微信用户；2-企业微信用户
                    $newCustomer[$k]['gender'] = $return['external_contact']['gender'];
                    $newCustomer[$k]['unionid'] = $return['external_contact']['unionid'];

                    $newCustomer[$k]['position'] = $return['external_contact']['position'];
                    $newCustomer[$k]['corp_name'] = $return['external_contact']['corp_name'];
                    $newCustomer[$k]['corp_full_name'] = $return['external_contact']['corp_full_name'];

                    $newCustomer[$k]['userid'] = $v['userid'];
                    $newCustomer[$k]['remark'] = $v['remark'];
                    $newCustomer[$k]['description'] = $v['description'];
                    $newCustomer[$k]['createtime'] = $v['createtime'];

                    $newCustomer[$k]['state'] = $v['state'];
                    $newCustomer[$k]['add_way'] = $v['add_way'];

                    $newCustomer[$k]['uid'] = 0;
                    $newCustomer[$k]['usertype'] = '';
                    $newCustomer[$k]['delete_time'] = '';
                    $newCustomer[$k]['del_way'] = '';

                    if ($return['external_contact']['unionid']) {

                        $user   =   $this->select_once('member', array('unionid' => $return['external_contact']['unionid']), '`uid`,`usertype`');
                        if (!empty($user)) {

                            $newCustomer[$k]['uid'] = $user['uid'];
                            $newCustomer[$k]['usertype'] = $user['usertype'];
                        }
                    }
                }

                $this->DB_insert_multi('work_customer', array_values($newCustomer));
            }
        }

        if (isset($data['WelcomeCode']) && !empty($data['WelcomeCode'])){

            $WelcomeCode    =   $data['WelcomeCode'];

            $welcome        =   $this->select_once('work_welcome', array('type' => 1, 'userid' => array('findin', $WelcomeCode), 'orderby' => 'up_time'));
            if (!empty($welcome))
            {
                if ($welcome['msgtype'] == 3 && $welcome['mini_endtime'] > time())
                {

                    $res    =   $this->uploadMedia($welcome['mini_picurl']);
                    $welcome['mini_media']  =   $res['media_id'];
                    $this->update_once('work_welcome', array('mini_media' => $res['media_id'], 'mini_endtime' => $res['created_at'] + 8600 * 3), array('id' => $welcome['id']));
                }

                $Token      =   $this->getToken('link');
                $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/send_welcome_msg?access_token='.$Token;

                $postData   =   array(
                    'welcome_code'  =>  $WelcomeCode
                );
                if (!empty($welcome['content'])){
                    $postData['text']   =   array(
                        'content'   =>  str_replace('{用户昵称}', '%NICKNAME%', $welcome['content'])
                    );
                }
                if ($welcome['msgtype'] == 1) {

                    $postData['attachments']    =   array(
                        'msgtype'   =>  'image',
                        'image'     =>  array(
                            'pic_url'   =>  $welcome['pic_url']
                        )
                    );
                } elseif ($welcome['msgtype'] == 2) {

                    $postData['attachments']    =   array(
                        'msgtype'   =>  'link',
                        'link'      =>  array(
                            'title' =>  $welcome['link_title'],
                            'picurl'=>  $welcome['link_picurl'],
                            'desc'  =>  $welcome['link_desc'],
                            'url'   =>  $welcome['link_url']
                        )
                    );
                } elseif ($welcome['msgtype'] == 3) {

                    if ($welcome['mini_endtime'] < time()){
                        $postData['attachments']    =   array(
                            'msgtype'       =>  'miniprogram',
                            'miniprogram'   =>  array(
                                'title'         =>  $welcome['mini_title'],
                                'pic_media_id'  =>  $welcome['mini_media'],
                                'appid'         =>  $welcome['link_desc'],
                                'page'          =>  $welcome['link_url']
                            )
                        );
                    }
                }
                $postJson   =   str_replace(' ', '', json_encode($postData));
                $CurlReturn =   CurlPost($qyUrl, $postJson);
                $return     =   json_decode($CurlReturn, true);
                //  TODO

                return $return['errcode'];
            }
        }
    }

    /**
     * @desc 删除企业客户
     * @param array $data
     */
    private function delExternalContact($data = array())
    {

        $externalUserId =   $data['ExternalUserID'];
        $userId         =   $data['UserID'];

        $this->update_once('work_customer', array('delete_time' => $data['CreateTime'], 'del_way' => 1), array('external_userid' => $externalUserId, 'userid' => $userId));
    }

    /**
     * @desc 删除跟进成员
     * @param array $data
     */
    private function delFollowUser($data = array())
    {
        $externalUserId =   $data['ExternalUserID'];
        $userId         =   $data['UserID'];

        $this->update_once('work_customer', array('delete_time' => $data['CreateTime'], 'del_way' => 2), array('external_userid' => $externalUserId, 'userid' => $userId));
    }

    /**
     * @desc 创建客户群
     * @param array $data
     */
    private function createGroup($data = array())
    {

        $ChatId =   $data['ChatId'];

        $group  =   $this->select_once('work_group', array('chat_id' => $ChatId));

        if (empty($group)){

            $Token      =   $this->getToken('link');
            $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/groupchat/get?access_token='.$Token;

            $postData   =   array(
                'chat_id'   => $ChatId,
                'need_name' =>  1
            );
            $postJson   =   str_replace(' ', '', json_encode($postData));
            $CurlReturn =   CurlPost($qyUrl, $postJson);
            $return     =   json_decode($CurlReturn, true);

            if ($return['errcode'] == 0 && $return['errmsg'] == 'ok'){

                $vData  =   array(

                    'chat_id'       =>  $ChatId,
                    'name'          =>  $return['group_chat']['name'],
                    'owner'         =>  $return['group_chat']['owner'],
                    'create_time'   =>  $return['group_chat']['create_time'],
                    'status'        =>  $return['group_chat']['status'],
                    'notice'        =>  $return['group_chat']['notice']
                );

                $this->insert_into('work_group', $vData);

                $memberList =   $return['group_chat']['member_list'];

                if (!empty($memberList)){

                    $newMData   =   array();

                    foreach ($memberList as $mk => $mv) {

                        $newMData[$mk]['chat_id']   =   $ChatId;
                        $newMData[$mk]['userid']    =   $mv['userid'];
                        $newMData[$mk]['type']      =   $mv['type'];
                        $newMData[$mk]['unionid']   =   $mv['unionid'];
                        $newMData[$mk]['join_time'] =   $mv['join_time'];
                        $newMData[$mk]['join_scene']=   $mv['join_scene'];
                        $newMData[$mk]['state']     =   $mv['state'];
                        $newMData[$mk]['invitor']   =   $mv['invitor']['userid'];

                        $newMData[$mk]['name']      =   base64_encode($mv['name']);
                        $newMData[$mk]['nickname']  =   base64_encode($mv['group_nickname']);
                    }

                    $this->DB_insert_multi('work_group_member', array_values($newMData));
                }
            }
        }
    }

    /**
     * @desc 成员入群
     * @param array $data
     */
    private function addGroupMember($data = array())
    {

        $ChatId     =   $data['ChatId'];
        $JoinScene  =   $data['JoinScene'];
        $this->syncGroupInfo(array('chat_id' => $ChatId), array('join_scene' => $JoinScene));
    }

    /**
     * @desc 成员退群
     * @param array $data | QuitScene : 1-群主/群管理员移出; 0-自己退群
     */
    private function delGroupMember($data = array())
    {

        $ChatId         =   $data['ChatId'];
        $QuitScene      =   $data['QuitScene'];
        $MemChangeCnt   =   $data['MemChangeCnt'];
        $CreateTime     =   $data['CreateTime'];

        $this->syncGroupInfo(array('chat_id' => $ChatId), array('quit_scene' => $QuitScene, 'mem_change_cnt' => $MemChangeCnt, 'create_time' => $CreateTime));
    }

    /**
     * @desc 群主变更
     * @param array $data
     */
    private function changeGroupOwner($data = array())
    {

        $ChatId     =   $data['ChatId'];
        $this->syncGroupInfo(array('chat_id' => $ChatId), array('group_owner' => 1));
    }

    /**
     * @desc 群名变更
     * @param array $data
     */
    private function changeGroupName($data = array())
    {

        $ChatId     =   $data['ChatId'];
        $this->syncGroupInfo(array('chat_id' => $ChatId), array('group_name' => 1));
    }

    /**
     * @desc 群公告变更
     * @param array $data
     */
    private function changeGroupNotice($data = array())
    {

        $ChatId     =   $data['ChatId'];
        $this->syncGroupInfo(array('chat_id' => $ChatId), array('group_notice' => 1));
    }

    /**
     * @desc 回调事件：客户群解散事件
     * @param array $data
     */
    private function disMiss($data = array())
    {

        $ChatId     =   $data['ChatId'];

        $this->delete_all('work_group', array('chat_id' => $ChatId), '');
        $this->delete_all('work_group_member', array('chat_id' => $ChatId), '');
    }

    /**
     * @desc 回调事件记录
     * @param array $data
     * @return bool
     */
    private function addCallBack($data)
    {

        $value  =   array(
            'tousername'    =>  $data['ToUserName'],
            'fromusername'  =>  $data['FromUserName'],
            'create_time'   =>  $data['CreateTime'],
            'msgtype'       =>  $data['MsgType'],
            'event'         =>  $data['Event'],
            'changetype'    =>  $data['ChangeType'],
            'params'        =>  json_encode($data)
        );

        $result =   $this->insert_into('work_callback_event', $value);

        return $result;
    }

    /**
     * @desc 同步客户详情
     * @param null $userId
     * @return mixed
     */
    private function syncCustomer($userId = null)
    {

        $Token      =   $this->getToken('link');
        $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get?access_token='.$Token.'&external_userid='.$userId;
        $CurlReturn =   CurlGet($qyUrl);
        $return     =   json_decode($CurlReturn, true);

        return  $return;
    }

    public $uTypeN  =   array(
        0   =>  '无身份',
        1   =>  '求职个人',
        2   =>  '招聘企业',
        3   =>  '猎头顾问',
        4   =>  '培训机构'
    );

    /**
     * @desc 查询客户详细信息
     * @param null $userId
     * @return array
     */
    public function getCustomerInfo($userId = null)
    {

        $externalInfo   =   $this->syncCustomer($userId);

        if ($externalInfo['errcode'] == 0 && $externalInfo['errmsg'] == 'ok' && $externalInfo['external_contact']){

            $externalContact    =   $externalInfo['external_contact'];
            $followUser         =   $externalInfo['follow_user'];

            $new_externalUserid =   $userId;
            $new_name           =   $externalContact['name'];
            $new_avatar         =   $externalContact['avatar'];
            $new_type           =   $externalContact['type'];
            $new_gender         =   $externalContact['gender'];
            $new_unionid        =   $externalContact['unionid'];
            $new_position       =   $externalContact['position'];
            $new_corpFullName   =   $externalContact['corp_full_name'];
            $new_corpName       =   $externalContact['corp_name'];
            $new_uid            =   0;
            $new_utype          =   '';

            if ($new_type == 1 && !empty($new_unionid)) {

                $user   =   $this->select_once('member', array('unionid' => $new_unionid), 'uid, usertype, reg_date, login_date');

                if (!empty($user)){

                    $new_uid    =   $user['uid'];
                    $new_utype  =   $user['usertype'];
                }
            }

            $customerInfo       =   array(

                'external_userid'   =>  $new_externalUserid,
                'name'              =>  $new_name,
                'avatar'            =>  $new_avatar,
                'type'              =>  $new_type,
                'gender'            =>  $new_gender,
                'unionid'           =>  $new_unionid,
                'position'          =>  $new_position,
                'corp_full_name'    =>  $new_corpFullName,
                'corp_name'         =>  $new_corpName,
                'uid'               =>  $new_uid,
                'usertype'          =>  $new_utype
            );

            $customerInfo['type_n'] =   $customerInfo['type'] == 2 ? $customerInfo['corp_full_name'] ? $customerInfo['corp_full_name'] : $customerInfo['corp_name'] : '微信';

            if ($customerInfo['uid'] > 0){

                $customerInfo['is_reg']         =   1;
                $customerInfo['reg_date_n']     =   date('Y-m-d H:i', $user['reg_date']);
                $customerInfo['login_date_n']   =   date('Y-m-d H:i', $user['login_date']);
                $customerInfo['utype_n']        =   $this->uTypeN[(int)$user['usertype']];

                if ($customerInfo['usertype'] == 1) {

                    $customerInfo['subInfo']    =   $this->getSubInfo($customerInfo);
                    $lookWhere                  =   array('uid' => $customerInfo['uid'], 'orderby' => 'datetime, desc', 'limit' => 10);
                    $lookList                   =   $this->getLookList($lookWhere);
                    $customerInfo['lookList']   =   $lookList;
                }
                $customerInfo['subInfo']        =   $this->getSubInfo($customerInfo);
            }else{

                $customerInfo['is_reg']         =   2;
                $customerInfo['utype_n']        =   '未注册';
            }

            $customerList   =   $this->select_all('work_customer', array('external_userid' => $userId));

            if (empty($customerList)) {
                if (count($followUser) > 1){

                    $newCustomerData    =   array();
                    foreach ($followUser as $k => $v){

                        $newCustomerData[$k]['external_userid'] =   $new_externalUserid;
                        $newCustomerData[$k]['name']            =   base64_encode($new_name);
                        $newCustomerData[$k]['avatar']          =   $new_avatar;
                        $newCustomerData[$k]['type']            =   $new_type;
                        $newCustomerData[$k]['gender']          =   $new_gender;
                        $newCustomerData[$k]['unionid']         =   $new_unionid;
                        $newCustomerData[$k]['position']        =   $new_position;
                        $newCustomerData[$k]['corp_full_name']  =   $new_corpFullName;
                        $newCustomerData[$k]['corp_name']       =   $new_corpName;

                        $newCustomerData[$k]['userid']          =   $v['userid'];
                        $newCustomerData[$k]['remark']          =   $v['remark'];
                        $newCustomerData[$k]['description']     =   $v['description'];
                        $newCustomerData[$k]['createtime']      =   $v['createtime'];

                        $newCustomerData[$k]['uid']             =   $new_uid;
                        $newCustomerData[$k]['usertype']        =   $new_utype;
                        $newCustomerData[$k]['add_way']         =   $v['add_way'];
                        $newCustomerData[$k]['state']           =   $v['state'];
                    }
                    $this->DB_insert_multi('work_customer', array_values($newCustomerData));
                }else{

                    $newCustomer        =   array(

                        'external_userid'   =>  $new_externalUserid,
                        'name'              =>  base64_encode($new_name),
                        'avatar'            =>  $new_avatar,
                        'type'              =>  $new_type,
                        'gender'            =>  $new_gender,
                        'unionid'           =>  $new_unionid,
                        'position'          =>  $new_position,
                        'corp_full_name'    =>  $new_corpFullName,
                        'corp_name'         =>  $new_corpName,
                        'userid'            =>  $followUser[0]['userid'],
                        'remark'            =>  $followUser[0]['remark'],
                        'description'       =>  $followUser[0]['description'],
                        'createtime'        =>  $followUser[0]['createtime'],
                        'uid'               =>  $new_uid,
                        'usertype'          =>  $new_utype,
                        'add_way'           =>  $followUser[0]['add_way'],
                        'state'             =>  $followUser[0]['state']
                    );
                    $this->insert_into('work_customer', $newCustomer);
                }
            }else{

                if (count($followUser) > 1){

                    $oldUserIdArr   =   array();
                    $newUserIdArr   =   array();

                    foreach ($customerList as $ck => $cv){

                        $oldUserIdArr[] =   $cv['userid'];
                    }
                    foreach ($followUser as $fk => $fv){

                        $newUserIdArr[] =  $fv['userid'];
                    }

                    $addUserIdArr   =   array_diff($newUserIdArr, $oldUserIdArr);
                    $delUserIdArr   =   array_diff($oldUserIdArr, $newUserIdArr);

                    if (!empty($addUserIdArr)){

                        $newCustomerData    =   array();
                        foreach ($followUser as $fk => $fv){

                            if (in_array($fv['userid'], $addUserIdArr)){

                                $newCustomerData[$fk]['external_userid']=   $new_externalUserid;
                                $newCustomerData[$fk]['name']           =   base64_encode($new_name);
                                $newCustomerData[$fk]['avatar']         =   $new_avatar;
                                $newCustomerData[$fk]['type']           =   $new_type;
                                $newCustomerData[$fk]['gender']         =   $new_gender;
                                $newCustomerData[$fk]['unionid']        =   $new_unionid;
                                $newCustomerData[$fk]['position']       =   $new_position;
                                $newCustomerData[$fk]['corp_full_name'] =   $new_corpFullName;
                                $newCustomerData[$fk]['corp_name']      =   $new_corpName;

                                $newCustomerData[$fk]['userid']         =   $fv['userid'];
                                $newCustomerData[$fk]['remark']         =   $fv['remark'];
                                $newCustomerData[$fk]['description']    =   $fv['description'];
                                $newCustomerData[$fk]['createtime']     =   $fv['createtime'];

                                $newCustomerData[$fk]['uid']            =   $new_uid;
                                $newCustomerData[$fk]['usertype']       =   $new_utype;
                                $newCustomerData[$fk]['add_way']        =   $fv['add_way'];
                                $newCustomerData[$fk]['state']          =   $fv['state'];
                            }
                        }

                        $this->DB_insert_multi('work_customer', array_values($newCustomerData));
                    }

                    if (!empty($delUserIdArr)){

                        $this->delete_all('work_customer',array('userid' => array('in', implode(',', $delUserIdArr))));
                    }

                    $upData =   array(
                        'name'          =>  base64_encode($new_name),
                        'avatar'        =>  $new_avatar,
                        'type'          =>  $new_type,
                        'gender'        =>  $new_gender,
                        'uninonid'      =>  $new_unionid,
                        'position'      =>  $new_position,
                        'corp_full_name'=>  $new_corpFullName,
                        'corp_name'     =>  $new_corpName,
                        'uid'           =>  $new_uid,
                        'usertype'      =>  $new_utype
                    );

                    foreach ($customerList as $ck => $cv){
                        foreach ($followUser as $fk => $fv){
                            if ($cv['userid'] == $fv['userid']){

                                $upData['remark']       =   $fv['remark'];
                                $upData['description']  =   $fv['description'];
                                $upData['createtime']   =   $fv['createtime'];
                                $upData['add_way']      =   $fv['add_way'];
                                $upData['state']        =   $fv['state'];

                                $this->update_once('work_customer', $upData, array('external_userid' => $new_externalUserid, 'userid' => $fv['userid']));
                            }
                        }
                    }
                }else{

                    if(count($customerList) > 1){

                        $this->delete_all('work_customer', array('external_userid' => $userId));
                        $newCustomer        =   array(

                            'external_userid'   =>  $new_externalUserid,
                            'name'              =>  base64_encode($new_name),
                            'avatar'            =>  $new_avatar,
                            'type'              =>  $new_type,
                            'gender'            =>  $new_gender,
                            'unionid'           =>  $new_unionid,
                            'position'          =>  $new_position,
                            'corp_full_name'    =>  $new_corpFullName,
                            'corp_name'         =>  $new_corpName,
                            'userid'            =>  $followUser[0]['userid'],
                            'remark'            =>  $followUser[0]['remark'],
                            'description'       =>  $followUser[0]['description'],
                            'createtime'        =>  $followUser[0]['createtime'],
                            'uid'               =>  $new_uid,
                            'usertype'          =>  $new_utype,
                            'add_way'           =>  $followUser[0]['add_way'],
                            'state'             =>  $followUser[0]['state']
                        );
                        $this->insert_into('work_customer', $newCustomer);
                    }else{

                        $upCustomer         =   array(
                            'name'          =>  base64_encode($new_name),
                            'avatar'        =>  $new_avatar,
                            'type'          =>  $new_type,
                            'gender'        =>  $new_gender,
                            'unionid'       =>  $new_unionid,
                            'position'      =>  $new_position,
                            'corp_full_name'=>  $new_corpFullName,
                            'corp_name'     =>  $new_corpName,
                            'userid'        =>  $followUser[0]['userid'],
                            'remark'        =>  $followUser[0]['remark'],
                            'description'   =>  $followUser[0]['description'],
                            'createtime'    =>  $followUser[0]['createtime'],
                            'add_way'       =>  $followUser[0]['add_way'],
                            'state'         =>  $followUser[0]['state'],
                            'uid'           =>  $new_uid,
                            'usertype'      =>  $new_utype
                        );
                        $this->update_once('work_customer', $upCustomer, array('external_userid' => $userId));
                    }
                }
            }

            $return     =   array(

                'errcode'   =>  1,
                'errmsg'    =>  'ok',
                'info'      =>  $customerInfo
            );
        }else{

            $return     =   array(

                'errcode'   =>  $externalInfo['errcode'],
                'errmsg'    =>  $externalInfo['errmsg']
            );
        }

        return $return;
    }

    /**
     * @param array $customerInfo
     * @return mixed
     */
    private function getSubInfo($customerInfo = array())
    {

        $resumeInfo         =   $this->select_once('resume', array('uid' => $customerInfo['uid']), 'name, telphone, birthday, sex');

        $subInfo['name']    =   $resumeInfo['name'] ? $resumeInfo['name'] : $customerInfo['name'];
        $subInfo['mobile']  =   $resumeInfo['telphone'] ? $resumeInfo['telphone'] : '--';

        if ($resumeInfo['birthday']) {

            $age            =   date('Y') - date('Y', strtotime($resumeInfo['birthday']));
            $subInfo['age'] =   $age.'岁';
        }else{

            $subInfo['age'] =   '--';
        }
        if ($resumeInfo['sex'] == 1) {

            $subInfo['sex'] =   '男';
        } elseif ($resumeInfo['sex'] == 2) {

            $subInfo['sex'] =   '女';
        } else {

            $subInfo['sex'] =   '';
        }

        $expectInfo         =   $this->select_once('resume_expect', array('uid' => $customerInfo['uid'], 'defaults' => 1), 'id, name, minsalary, maxsalary, content, integrity');

        if ($expectInfo) {

            $subInfo['isResume']    =   1;
            $subInfo['expect_name'] =   $expectInfo['name'];
            $subInfo['content']     =   $expectInfo['content'];
            $subInfo['integrity']   =   intval($expectInfo['integrity']);

            if (intval($expectInfo['integrity']) < 100){

                $subResume  =   $this->select_once('user_resume', array('eid' => $expectInfo['id'], 'uid' => $customerInfo['uid']));

                if ($subResume['work'] == 0) {

                    $subInfo['wstitle'] =   '工作经历';
                } elseif ($subResume['edu'] == 0) {

                    $subInfo['wstitle'] =   '教育经历';
                } elseif ($subResume['project'] == 0) {

                    $subInfo['wstitle'] =   '项目经历';
                } elseif ($subResume['training'] == 0) {

                    $subInfo['wstitle'] =   '培训经历';
                } elseif ($subResume['skill'] == 0) {

                    $subInfo['wstitle'] =   '职业技能';
                } elseif ($subResume['other'] == 0) {

                    $subInfo['wstitle'] =   '其他信息';
                }
            }

        }else{

            $subInfo['isResume']    =   2;
            $subInfo['expect_name'] =   '--';
        }

        if (!empty($expectInfo['minsalary']) && !empty($expectInfo['maxsalary'])){

            $subInfo['salary']      =   $expectInfo['minsalary'].' - '.$expectInfo['maxsalary'];
        }else if (!empty($expectInfo['minsalary']) && empty($expectInfo['maxsalary'])){

            $subInfo['salary']      =   $expectInfo['minsalary'];
        }else{

            $subInfo['salary']      =   '--';
        }

        return  $subInfo;
    }

    /**
     * @desc 用户画像-浏览记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getLookList($where = array(), $data = array())
    {

        $field  =   isset($data['field']) ? $data['field'] : '*';

        $List   =   $this->select_all('look_job', $where, $field);

        if (!empty($List)){

            $jobIds =   array();
            foreach ($List as $k => $v) {
                if (!in_array($v['jobid'], $jobIds)){

                    $jobIds[]   =   $v['jobid'];
                }
            }

            $jobList    =   $this->select_all('company_job', array('id' => array('in', pylode(',', $jobIds))), 'id, name, com_name');

            $weeks      =   ['星期一','星期二','星期三','星期四','星期五','星期六','星期日'];
            foreach ($List as $k => $v){

                $List[$k]['time_n'] =   date('H:i', $v['datetime']);

                $List[$k]['week_day'] =   $weeks[date('w', $v['datetime'])].' '.date('Y-m-d', $v['datetime']);

                foreach ($jobList as $jk => $jv) {

                    if ($v['jobid'] == $jv['id']){

                        $List[$k]['job_name']   =   $jv['name'];
                        $List[$k]['com_name']   =   $jv['com_name'];
                    }
                }
            }
        }

        return  $List;
    }

    /**
     * @desc 用户画像-投递记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getSqList($where = array(), $data = array())
    {

        $field  =   isset($data['field']) ? $data['field'] : '*';

        $List   =   $this->select_all('userid_job', $where, $field);

        if (!empty($List)){

            $weeks      =   ['星期一','星期二','星期三','星期四','星期五','星期六','星期日'];
            foreach ($List as $k => $v){

                $List[$k]['time_n'] =   date('H:i', $v['datetime']);
                $List[$k]['week_day'] =   $weeks[date('w', $v['datetime'])].' '.date('Y-m-d', $v['datetime']);
            }
        }

        return  $List;
    }

    /**
     * @desc 用户画像-职位列表
     * @param $uid
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getMatchJobList($uid, $data = array())
    {

        $resumeExpect   =   $this->select_once('resume_expect', array('uid' => $uid, 'defaults' => 1), '`job_classid`,`city_classid`');

        $where          =   array('state' => 1, 'status' => 0, 'r_status' => 1);

        /*if ($resumeExpect['job_classid'] != "") {

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['job_post']          =   array('in', $resumeExpect['job_classid']);
            $where['job1_son']          =   array('in', $resumeExpect['job_classid'], 'or');
            $where['PHPYUNBTWEND_A']    =   '';
        }
        if ($resumeExpect['city_classid'] != "") {

            $where['PHPYUNBTWSTART_B']  =   '';
            $where['provinceid']        =   array('in', $resumeExpect['city_classid']);
            $where['cityid']            =   array('in', $resumeExpect['city_classid'], 'or');
            $where['three_cityid']      =   array('in', $resumeExpect['city_classid'], 'or');
            $where['PHPYUNBTWEND_B']    =   '';
        }*/

        $where['lastupdate']            =   array('>', strtotime('-30 day'));

        $whereNew   =   array_merge($where, $data);

        $jobList    =   $this->select_all('company_job', $whereNew);

        foreach ($jobList as $k => $v) {

            $jobList[$k]['url']         =   Url('wap', array('c' => 'job', 'a' => 'comapply', 'id' => $v['id']));
            $jobList[$k]['up_time']     =   lastupdateStyle($v['lastupdate']);
            $jobList[$k]['salary_n']    =   salaryUnit($v['minsalary'], $v['maxsalary']);
        }

        return $jobList;
    }

    /**
     * @desc 用户画像-行为轨迹
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getLogList($where = array(), $data = array())
    {

        require_once 'weblog.model.php';
        $webLogM    =   new weblog_model($this->db, $this->def);

        $List       =   $webLogM->getWebLogList($where, $data);

        $weeks      =   ['星期一','星期二','星期三','星期四','星期五','星期六','星期日'];
        foreach ($List as $k => $v){

            $List[$k]['week_day'] =   $weeks[date('w', $v['time'])].' '.date('Y-m-d', $v['time']);
        }
        return $List;
    }

    /**
     * @desc 用户画像-简历备注
     * @param array $upData
     * @param array $whereData
     * @return array
     */
    public function remarkResume($upData = array(), $whereData = array())
    {
        $return     =   array('errcode' => 8, 'msg' => '系统异常，请重试！');
        if (!empty($upData) && !empty($whereData)) {

            $result =   $this->update_once('resume_expect', $upData, $whereData);

            if ($result) {

                $return =   array('errcode' => 9, 'msg' => '备注成功！');
            } else {

                $return =   array('errcode' => 8, 'msg' => '备注失败，请稍候重试！');
            }

        }
        return $return;
    }

    /**
     * @desc 扫码授权登录，获取访问用户身份
     * @param $code
     * @return mixed
     */
    public function getUserInfo($code)
    {

        $Token      =   $this->getToken('photo');
        $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token='.$Token.'&code='.$code;
        $CurlReturn =   CurlGet($qyUrl);
        $return     =   json_decode($CurlReturn, true);

        return  $return;
    }

    /**
     * @desc 企业微信扫码授权成功，绑定企业UserId
     * @param array $upData
     * @param array $whereData
     */
    public function upQyUserInfo($upData = array(), $whereData = array())
    {

        $this->update_once('admin_user', $upData, $whereData);
    }

    /**
     * @desc 网页授权登录，获取访问用户身份
     * @param $code
     * @return mixed
     */
    public function getAuthUserInfo($code)
    {

        $Token      =   $this->getToken('photo');
        $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/auth/getuserinfo?access_token='.$Token.'&code='.$code;
        $CurlReturn =   CurlGet($qyUrl);
        $return     =   json_decode($CurlReturn, true);

        return  $return;
    }

    /**
     * @desc 网页授权登录，获取访问用户敏感信息
     * @param $user_ticket
     * @return mixed
     */
    public function getAuthUserDetail($user_ticket)
    {

        $Token      =   $this->getToken('photo');
        $qyUrl      =   'https://qyapi.weixin.qq.com/cgi-bin/auth/getuserdetail?access_token='.$Token;
        $postData['user_ticket'] = $user_ticket;

        $postJson   =   str_replace(' ', '', json_encode($postData));
        $CurlReturn =   CurlPost($qyUrl, $postJson);
        $return     =   json_decode($CurlReturn, true);

        if ($return['errcode'] == 0){

            $this->update_once('work_user', array('avatar' => $return['avatar'], 'mobile' => $return['mobile']), array('userid' => $return['userid']));
        }

        return  $return;
    }

}
?>