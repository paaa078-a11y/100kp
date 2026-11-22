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

class crm_my_customer_controller extends adminCommon
{
    function index_action()
    {

        if (!empty($_POST)) {

            $comM   =   $this->MODEL('company');
            $crmM   =   $this->MODEL('crm');

            $where  =   array();

            $page   =   $_POST['page'];
            $limit  =   $_POST['limit'];

            $where['crm_uid']   =   $_SESSION['wuid'];

            if (trim($_POST['keyword'])) {

                $where['name']  =   array('like', trim($_POST['keyword']));
                $data['keyword']=   trim($_POST['keyword']);
            }
            if (isset($_POST['crmType']) && !empty($_POST['crmType'])) {

                $crmType = intval($_POST['crmType']);
                $where['crm_type'] = $crmType;
            }
            if (isset($_POST['crm_status']) && !empty($_POST['crm_status'])) {

                $crm_status = intval($_POST['crm_status']);
                $where['crm_status'] = $crm_status;
            }
            if (isset($_POST['rating']) && $_POST['rating'] != '') {

                $rating = intval($_POST['rating']);
                $where['rating'] = $rating;
            }
            if (isset($_POST['vipStime']) && $_POST['vipStime']!='') {

                if ($_POST['rating'] == '0') {

                    $where['vipetime']  =   array('<', strtotime("-" . $_POST['vipStime'] . " days"));
                } else {

                    $where['vipstime']  =   array('<', strtotime("-" . $_POST['vipStime'] . " days"));
                }
            }

            if (!empty($_POST['last_Ftime']) && $_POST['last_Ftime'] != '') {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['f_time'][]    =   array('isnull', '', '');
                $where['f_time'][]    =   array('<', strtotime('-'.$_POST['last_Ftime'].' days'), 'OR');
                $where['PHPYUNBTWEND_A']  =   '';
            }

            if (isset($_POST['lastFtime']) && $_POST['lastFtime'] != ''){

                $lastFtime  =   intval($_POST['lastFtime']);

                if ($lastFtime == 1) {

                    $where['PHPYUNBTWSTART_A']  =   '';
                    $where['isfollow']  =   array('=', '0', 'OR');
                    $where['f_time']    =   array('=', '', 'OR');
                    $where['PHPYUNBTWEND_A']  =   '';
                }else {

                    $where['PHPYUNBTWSTART_A'] = '';
                    $where['f_time'][] = array('isnull', '', '');
                    $where['f_time'][] = array('<', strtotime('-' . $lastFtime . ' days'), 'OR');
                    $where['PHPYUNBTWEND_A'] = '';
                }
            }

            if (isset($_POST['kh']) && $_POST['kh']!=''){

                $kh =   intval($_POST['kh']);

                if ($kh == 6){  //  今日登录

                    $where['login_date']    =   array('>=', strtotime(date('Y-m-d')));
                }elseif ($kh == 3){

                    $where['PHPYUNBTWSTART_A']  =   '';
                    $where['f_time'][]          =   array('>', 0);
                    $where['f_time'][]          =   array('<', strtotime('-30 days'));
                    $where['PHPYUNBTWEND_A']    =   '';
                }
            }

            if (isset($_POST['vipetime']) && !empty($_POST['vipetime'])) {

                $etime  =   intval($_POST['vipetime']);
                if ($etime == 5) {
                    $where['PHPYUNBTWSTART_A'] = '';
                    $where['vipetime'][] = array('<', strtotime(date('Y-m-d')), 'AND');
                    $where['vipetime'][] = array('>', '0', 'AND');
                    $where['PHPYUNBTWEND_A'] = '';
                } else {
                    if ($etime == 1) {
                        $num = '+3 days';
                    } else if ($etime == 2) {
                        $num = '+7 days';
                    } elseif ($etime == 3) {
                        $num = '+15 days';
                    } elseif ($etime == 4) {
                        $num = '+1 month';
                    }
                    $where['PHPYUNBTWSTART_A'] = '';
                    $where['vipetime'][] = array('>', time(), 'AND');
                    $where['vipetime'][] = array('<', strtotime($num), 'AND');
                    $where['PHPYUNBTWEND_A'] = '';
                }
            }
            if (isset($_POST['nextFtime']) && !empty($_POST['nextFtime'])) {

                $nextFtime = intval($_POST['nextFtime']);

                $taskWhere = array('type' => 22, 'status' => 1);

                if ($nextFtime == 1) {

                    $taskWhere['PHPYUNBTWSTART_A'] = '';
                    $taskWhere['stime'] = array('<', strtotime(date('Y-m-d 23:59:59')));  //  之前未完成的跟进同步显示
                    $taskWhere['stime'] = array('>', 0);
                    $taskWhere['PHPYUNBTWEND_A'] = '';
                } elseif ($nextFtime == 2) {

                    $taskWhere['PHPYUNBTWSTART_A'] = '';
                    $taskWhere['stime'][] = array('>', strtotime(date('Y-m-d 23:59:59')));
                    $taskWhere['stime'][] = array('<', strtotime(date('Y-m-d 23:59:59')) + 86400);
                    $taskWhere['PHPYUNBTWEND_A'] = '';
                } elseif ($nextFtime == 3) {

                    $taskWhere['PHPYUNBTWSTART_A'] = '';
                    $taskWhere['stime'][] = array('>', time());
                    $taskWhere['stime'][] = array('<', strtotime('+3 day'));
                    $taskWhere['PHPYUNBTWEND_A'] = '';
                } elseif ($nextFtime == 4) {

                    $taskWhere['PHPYUNBTWSTART_A'] = '';
                    $taskWhere['stime'][] = array('>', time());
                    $taskWhere['stime'][] = array('<', strtotime('+1 week'));
                    $taskWhere['PHPYUNBTWEND_A'] = '';
                } elseif ($nextFtime == 5) {

                    $taskWhere['PHPYUNBTWSTART_A'] = '';
                    $taskWhere['stime'][] = array('>', time());
                    $taskWhere['stime'][] = array('<', strtotime('+1 month'));
                    $taskWhere['PHPYUNBTWEND_A'] = '';
                }

                $taskWhere['uid'] = $_SESSION['wuid'];
                $taskList = $crmM->getTaskList($taskWhere, array('field' => '`comid`'));

                $uidArrN = array();
                foreach ($taskList as $tv) {

                    $uidArrN[] = $tv['comid'];
                }

                $where['uid'] = array('in', pylode(',', $uidArrN));
            }

            $where['orderby']   =   'login_date,desc';
            if ($page) {

                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =    array($pagenav, $limit);
            } else {

                $where['limit'] =   $limit;
            }

            $list   =   $comM->getList($where, array('utype' => 'crm'));

            $data['list'] = count($list['list']) ? $list['list'] : [];
            $data['todayStart'] = strtotime('today');
            $data['sy_crm_link'] = $this->config['sy_crm_link'];
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $cacheM = $this->MODEL('cache');
        $crmCache = $cacheM->GetCache(array('crm'));
        // 跟进方式
        $r = array();
        foreach ($crmCache['crmdata']['follow_way'] as $v) {
            $r[] = array('id' => $v, 'name' => $crmCache['crmclass_name'][$v]);
        }
        $this->yunset('followWay', json_encode($r));
        // 任务类型
        $taskTypes = array();
        foreach ($crmCache['crmdata']['task_type'] as $v) {
            $taskTypes[] = array('id' => $v, 'name' => $crmCache['crmclass_name'][$v]);
        }
        $this->yunset('taskType', json_encode($taskTypes));
        // 可选处理人
        $AdminM = $this->MODEL('admin');
        $adminUserList = $AdminM->getList(array(), array('field' => '`uid`,`name`'));
        $adminUsers = array();
        foreach ($adminUserList as $v) {
            array_push($adminUsers, array('id' => $v['uid'], 'name' => $v['name']));
        }
        unset($adminUserList);
        $this->yunset('adminUsers', json_encode($adminUsers));
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $isOutcall = $appkey && $appsecret;
        $this->yunset('is_outcall', $isOutcall);// 是否配置外呼
        $this->yuntpl(array('wapadmin/my_customer'));
    }

    function statis_action(){
        $cacheM     =   $this->MODEL('cache');
        $crmCache   =   $cacheM->GetCache(array('crm'));
        $this->yunset(array('cache' => $crmCache, 'crmClassName' => $crmCache['crmclass_name'], 'crmStatus' => $crmCache['crmdata']['client_status'], 'crmType' => $crmCache['crmdata']['client_type']));

        $crmTypeArr = $crmStatusArr= $ratingarr= $edtimearr= $releasetimearr = $nextftimearr = array();
        if($crmCache['crmdata']['client_status']){
            foreach ($crmCache['crmdata']['client_status'] as $v){
                $crmStatusArr[] = array('id'=>$v,"name"=>$crmCache['crmclass_name'][$v]);
            }
        }
        if($crmCache['crmdata']['client_type']){
            foreach ($crmCache['crmdata']['client_type'] as $v){
                $crmTypeArr[] = array('id'=>$v,"name"=>$crmCache['crmclass_name'][$v]);
            }
        }
        $ratingM        =   $this -> MODEL('rating');

        if (!empty($this->config['com_rating'])) {

            $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`', 'rating' => $this->config['com_rating']));
        } else {

            $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
        }

        if (!empty($rating)) {
            $ratingarr  =   array();
            foreach ($rating as $k => $v) {

                $ratingarr[]    =   array("id" => $v['id'], "name" => $v['name']);
            }
        }
        $edtime         =   array('5' => '已到期', '1' => '三天内', '2' => '七天内', '3' => '十五天内', '4' => '一月内');
        foreach ($edtime as $k=>$v){
            $edtimearr[]=   array('id'=>$k,'name'=>$v);
        }
        $releaseTime=   array('1' => '今天', '2' => '昨天', '3' => '三天内', '4' => '七天内', '5' => '一月内', '6' => '自定义');
        foreach ($releaseTime as $k=>$v){
            $releasetimearr[] = array('id'=>$k,'name'=>$v);
        }
        $nextFtime  =   array('1' => '今天', '2' => '明天', '3' => '三天内', '4' => '七天内', '5' => '一月内');
        foreach ($nextFtime as $k=>$v){
            $nextftimearr[] = array('id'=>$k,'name'=>$v);
        }
        $data['ratingarr'] = $ratingarr;
        $data['crmStatusArr'] = $crmStatusArr;
        $data['crmTypeArr'] = $crmTypeArr;
        $data['edtimearr'] = $edtimearr;
        $data['releasetimearr'] = $releasetimearr;
        $data['nextftimearr'] = $nextftimearr;
        header('content-type:application/json; charset=utf-8');
        echo json_encode($data);die;
    }

    /**
     * @desc 修改客户状态/等级
     */
    function upStatusType_action()
    {
        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['wuid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->upComST($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 客户管理->添加/修改跟进记录
     */
    function addFollow_action()
    {
        if (!empty($_POST)) {
            $data           =   $_POST;
            $data['uid']    =   $_POST['uid'] ? $_POST['uid'] : $_POST['comid'];
            $data['auid']   =   $_SESSION['wuid'];

            $comInfo        =   $this->obj->select_once('company', array('uid' => $data['uid']), '`linkman`,`linktel`,`linkphone`');
            $data['link_man']    =   $comInfo['linkman'];
            $data['link_tel']    =   $comInfo['linktel'] ? $comInfo['linktel'] : $comInfo['linkphone'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->addFollow($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc 新建任务
     */
    function addTask_action()
    {
        if ($_POST) {
            $crmM   =   $this->MODEL('crm');
            $Data   =   array(
                'auid'      =>  $_SESSION['wuid'],
                'uid'       =>  $_POST['taskHuid'],
                'comid'     =>  $_POST['comid'],
                'type'      =>  $_POST['type'],
                'content'   =>  trim($_POST['content'])
            );
            if ($_POST['type'] == '22') {
                $Data['stime']  =   strtotime($_POST['stime']);
                $Data['uid'] = $_SESSION['wuid'];
            }else{
                unset($Data['stime']);
            }
            $return =   $crmM->addWaitingTask($Data);
        }
        echo json_encode($return);
        die();
    }

    /**
     * @desc 操作记录，未用到
     */
    function comlog_action() {
        if ($_POST) {
            $crmM = $this->MODEL('crm');

            $uid = $_POST['uid'];
            $page = $_POST['page'];
            $limit = $_POST['limit'];
            $logWhere['uid'] = $uid;

            $returnData['total'] = $crmM->getComLogNum($logWhere);

            $pagenav = ($page - 1) * $limit;
            $logWhere['limit'] = array($pagenav, $limit);
            $logWhere['orderby'] = 'ctime,desc';

            $returnData['pageTotal'] = ceil($returnData['total']/$limit);
            $returnData['list'] = $crmM->getComLogList($logWhere);

            $this->render_json(0, 'ok', $returnData);
        }
        $this->yuntpl(array('wapadmin/crm_customer_comlog'));
    }

    /**
     * @desc 跟进记录
     */
    function concern_action() {
        if ($_POST) {
            $crmM = $this->MODEL('crm');

            $uid = $_POST['uid'];
            $page = $_POST['page'];
            $limit = $_POST['limit'];
            $followWhere['uid']     =   $uid;
            if (!$_POST['all']) {
                $followWhere['auid']    =   $_SESSION['wuid'];
            }

            $returnData['total'] = $crmM->getConcernNum($followWhere);

            $pagenav = ($page - 1) * $limit;
            $followWhere['limit'] = array($pagenav, $limit);
            $followWhere['orderby'] =   'atime,desc';

            $returnData['pageTotal'] = ceil($returnData['total']/$limit);
            $returnData['list'] = $crmM->getConcernList($followWhere, array('utype' => 'crm'));

            $this->render_json(0, 'ok', $returnData);
        }
        $this->yuntpl(array('wapadmin/crm_customer_concern'));
    }

    /**
     * @desc 联系反馈-> 添加企业备注
     */
    function remarkCom_action()
    {
        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['wuid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->remarkCom($data);

            echo json_encode($return);
            die;
        }
    }

    /**
     * @desc CRM-我的客户 外呼
     */
    function contactCustomer_action()
    {
        $adminM = $this->MODEL('admin');
        $staffCallM = $this->MODEL('staffcalllog');
        $adminInfo = $adminM->getAdminUser(array('uid' => $_SESSION['wuid']));
        if (!$adminInfo['moblie']) {
            echo json_encode(['code' => 400, 'msg' => '业务员未填写手机号']);exit;
        }
        $mobile = trim($_POST['mobile']);
        if (!$mobile || !CheckMobile($mobile)) {
            echo json_encode(['code' => 400, 'msg' => '手机号格式错误']);exit;
        }
        if (!$_POST['uid']) {
            echo json_encode(['code' => 400, 'msg' => '企业uid不能为空']);exit;
        }
        $apiurl = 'https://u.phpyun.com/call';
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            echo json_encode(['code' => 400, 'msg' => '请先完成外呼配置！']);exit();
        }
        $params = array(
            'appKey' => $appkey,
            'appSecret' => $appsecret,
            'caller' => $adminInfo['moblie'],
            'callee' => $mobile
        );
        $res = CurlPost($apiurl, $params, 1);
//        file_put_contents(APP_PATH."new_call_log.txt",'客服外呼接口返回信息：('.date('Y-m-d H:i:s').')'.$res.";\n",FILE_APPEND);
        $result = json_decode($res, 1);
        $callLog = array(
            'uid' => $_SESSION['wuid'],
            'username' => $adminInfo['name'],
            'session_id' => isset($result['data']['session_id']) ? $result['data']['session_id'] : '',
            'caller' => $params['caller'],
            'callee' => $params['callee'],
            'log_id' => isset($result['data']['log_id']) ? $result['data']['log_id'] : 0,// 综合平台外呼记录id
            'has_session' => isset($result['data']['has_session']) ? $result['data']['has_session'] : 0,// 是否有session_id
            'cuid' => $_POST['uid']
        );
        $callLog['api_res'] = json_encode(json_decode($res, 1), JSON_UNESCAPED_UNICODE);
        if ($result['code'] == 200) {// 呼叫成功
            $callLog['status'] = 1;
        } else {
            $callLog['status'] = 0;
        }
        $id = $staffCallM->addData($callLog);
        if ($result['code'] == 200) {
            echo json_encode(['code' => 200, 'msg' => '呼叫成功', 'data' => $id ? $id : 0]);exit();
        } else {
            echo json_encode(['code' => 400, 'msg' => $result['message'], 'data' => $id ? $id : 0]);exit();
        }
    }

    public function add_action()
    {

        if (!empty($_POST)){

            $crmUid =   isset($_POST['crm_uid']) && !empty($_POST['crm_uid']) ? $_POST['crm_uid'] : $_SESSION['wuid'];

            $mPost  =   array(
                'username'      => trim($_POST['linktel']),
                'companyName'   => trim($_POST['name']),
                'moblie'        => trim($_POST['linktel'])
            );

            if ($_POST['password'] == '' || mb_strlen($_POST['password']) < 6 || mb_strlen($_POST['password']) > 20) {

                echo json_encode(array('error' => 2, 'msg' => '账号密码必须大于6位小于20位'));
                die;
            }

            $userInfoM  =   $this->MODEL('userinfo');
            $result     =   $userInfoM->addMemberCheck($mPost);
            if ($result['msg']) {

                echo json_encode(array('error' => 2, 'msg' => $result['msg']));
                die;
            }

            $time       =   time();
            $ip         =   fun_ip_get();
            $pass       =   $_POST['password'];
            $pwdRes     =   $userInfoM->generatePwd(array('password' => $pass));
            $salt       =   $pwdRes['salt'];
            $password   =   $pwdRes['pwd'];

            $mdata  =   array(

                'username'  =>  trim($_POST['linktel']),
                'password'  =>  $password,
                'usertype'  =>  2,
                'salt'      =>  $salt,
                'moblie'    =>  $_POST['linktel'],
                'reg_date'  =>  $time,
                'reg_ip'    =>  $ip,
                'source'    =>  '16',
                'status'    =>  1
            );

            $udata  =   array(

                'name'          =>  $_POST['name'],
                'r_status'      =>  0,
                'linkman'       =>  $_POST['linkman'],
                'linktel'       =>  $_POST['linktel'],
                'crm_uid'       =>  $crmUid,
                'crm_time'      =>  $time,
                'crm_source'    =>  3,
                'crm_type'      =>  $_POST['crm_type'],
                'crm_status'    =>  $_POST['crm_status']
            );

            $sdata  =   array(

                'rating'    =>  $this->config['com_rating']
            );

            $nid    =   $userInfoM->addInfo(array('mdata' => $mdata, 'udata' => $udata, 'sdata' => $sdata));

            if ($nid) {

                $crmM   =   $this->MODEL('crm');

                $logData        =   array(
                    'auid'      =>  $_SESSION['wuid'],
                    'uid'       =>  $nid,
                    'type'      =>  10,
                    'content'   =>  '新客户录入，UID：'.$nid,
                    'ctime'     =>  time()
                );

                if ($crmUid != $_SESSION['wuid']){

                    $logData['remark']  =   '【协助录入】';
                }
                $crmM->addCrmComLog($logData);

                if (!empty($_POST['rating'])) {

                    $dealData   =   array(

                        'uid'           =>  $nid,
                        'rating'        =>  intval($_POST['rating']),
                        'auid'          =>  $_SESSION['wuid'],
                        'type'          =>  1,
                        'order_remark'  =>  '新客户录入',
                        'crm_uid'       =>  $crmUid
                    );
                    $ratingM    =   $this->MODEL('rating');
                    $ratingPrice=   $ratingM->getRatingPrice(array('id' => $_POST['rating']));
                    $dealData['order_price']    =   $ratingPrice;

                    $crmM->addDeal($dealData, 0);
                }

                $cacheM     =   $this->MODEL('cache');
                $crmCache   =   $cacheM->GetCache(array('crm'));

                $lContent   =   "录入新客户：" . $_POST['name'];
                if ($_POST['crm_type']) {

                    $lContent   .=  "，标记客户等级为：".$crmCache['crmclass_name'][$_POST['crm_type']];
                }
                if ($_POST['crm_status']) {
                    $lContent   .=  "，标记客户状态为：".$crmCache['crmclass_name'][$_POST['crm_status']];
                }
                // 添加CRM操作记录；
                $crmM->addCrmLog($lContent, 1, $nid, $crmUid);
            }

            $msg    =   $nid ? '客户录入成功！' : '客户录入失败，请重试！';

            echo json_encode(array('error' => $nid ? 0: 2, 'msg' => $msg));die;
        }

        $this->yuntpl(array('wapadmin/customer_add'));
    }

    public function addWorkLog_action()
    {
        if (!empty($_POST)) {

            $data           =   $_POST;
            $data['auid']   =   $_SESSION['wuid'];

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->addWorkLog($data);

            echo json_encode($return);
            die;
        }
    }

    public function getCrmClass_action()
    {

        if (!empty($_POST)) {

            $crmS = $this->obj->select_all('admin_user', array('is_crm' => 1, 'status' => 1), 'uid,name');

            $cacheM = $this->MODEL('cache');
            $crmCache = $cacheM->GetCache(array('crm'));

            $crmClassName = $crmCache['crmclass_name'];

            $crmStatus = $crmType = array();
            foreach ($crmCache['crmdata']['client_status'] as $k => $v) {

                $crmStatus[] = array('id' => $v, 'name' => $crmClassName[$v]);
            }
            foreach ($crmCache['crmdata']['client_type'] as $k => $v) {

                $crmType[] = array('id' => $v, 'name' => $crmClassName[$v]);
            }

            $ratingM    =   $this->MODEL('rating');
            $ratingArr  =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));

            $data       =   array(

                'crmS'      =>  $crmS,
                'crmStatus' =>  $crmStatus,
                'crmType'   =>  $crmType,
                'ratingArr' =>  $ratingArr
            );

            echo json_encode(array('error' => 0, 'data' => $data));
            die();
        }
    }

    public function com_action()
    {


        if ($_POST) {

            $com_id     =   $_POST['com_id'];

            if (!$com_id) {

                $this->render_json(1, '参数错误');
            }

            $crmM       =   $this->MODEL('crm');
            $manageD    =   $crmM->isManaged(array('uid' => $com_id, 'auid' => $_SESSION['wuid']));

            if ($manageD['code'] == 2) {
                $this->render_json(2, '暂无管理权限！');
            }

            $jobM       =   $this->MODEL('job');
            $companyM   =   $this->MODEL('company');
            $downresumeM=   $this->MODEL('downresume');
            $comaM      =   $this->MODEL('companyaccount');

            $returnData['job_num']          =   $jobM->getJobNum(array('com_id' => $com_id)); // 职位数
            $returnData['invite_num']       =   $jobM->getYqmsNum(array('fid' => $com_id)); // 邀请面试数
            $returnData['sqnum']            =   $jobM->getSqJobNum(array('com_id' => $com_id)); // 收到简历
            $returnData['downresume_num']   =   $downresumeM->getDownNum(array('comid' => $com_id, 'usertype' => 2)); // 下载简历数
            $returnData['child_account_num']=   $comaM->getNum(array('comid' => $com_id));

            $info   =   $companyM->getInfo($com_id, array('crm' => 1, 'auid' => $_SESSION['wuid'], 'logo' => 1));

            $jobInfo=   $this->obj->select_once('company_job', array('uid' => $com_id, 'orderby' => 'lastupdate,desc'), 'name');
            $info['job_name']   =   $jobInfo['name'];

            $info['login_date_n']       =   date("Y-m-d", $info['login_date']);
            $info['reg_date_n']         =   date("Y-m-d", $info['reg_date']);
            $returnData['info']['logo'] =   checkpic($returnData['info']['logo']);
            if ($info['vipetime'] > 0) {
                $info['vipetime_n']     =   date('Y-m-d', $info['vipetime']).' 到期';
            }
            $hits_expoure       =   $companyM->getHitsExpoure($com_id);
            $info['hits_s']     =   $hits_expoure['hits']; // 企业浏览量+职位浏览量
            $returnData['info'] =   $info;


            $cacheM     =   $this->MODEL('cache');
            $crmCache   =   $cacheM->GetCache(array('crm'));

            $returnData['crmType']      =   $crmCache['crmdata']['client_type'];
            $returnData['crmStatus']    =   $crmCache['crmdata']['client_status'];
            $returnData['crmClassName'] =   $crmCache['crmclass_name'];
            $returnData['followWay']    =   $crmCache['crmdata']['follow_way'];

            $returnData['auid']         =   $_SESSION['wuid'];

            $this->render_json(0, 'ok', $returnData);
        }

        $appkey     =   trim($this->config['sy_outcall_appkey']);
        $appsecret  =   trim($this->config['sy_outcall_appsecret']);
        $isOutcall  =   $appkey && $appsecret;
        $this->yunset('is_outcall', $isOutcall);// 是否配置外呼
        $this->yuntpl(array('wapadmin/crm_customer_com'));
    }
}

?>
