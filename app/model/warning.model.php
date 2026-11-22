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
class warning_model extends model
{

    /**
     * @desc 预警提醒
     * @param int $type : 1-发布职位 2-简历下载 3-发布简历 4-充值 5-短信 6-投递 7-浏览简历 8-浏览职位 9-拨号 10-企业隐私号 11-个人隐私号 12-注册账号同一IP 13-发布职位同一IP 14-创建简历同一IP
     * @param null $uid
	 * @param null $ip
     */
    function warning($type, $uid = null, $ip = null,$data=array())
    {

        $today = strtotime('today');

        if ($type == 1 && $this->config['warning_addjob'] > 0) {    // 发布职位

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {

                $num1 = $this->select_num('company_job', array('uid' => $uid, 'sdate' => array('>', $today)));
                $num2 = $this->select_num('partjob', array('uid' => $uid, 'sdate' => array('>', $today)));
                $num3 = $this->select_num('lt_job', array('uid' => $uid, 'sdate' => array('>', $today)));

                $num = $num1 + $num2 + $num3;
                if ($num >= $this->config['warning_addjob']) {

                    $this->send_warning($type, $uid);
                }
            }
        } elseif ($type == 2 && $this->config['warning_downresume'] > 0) {  // 简历下载

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $num = $this->select_num('down_resume', array('comid' => $uid, 'downtime' => array('>', $today)));

                if ($num >= $this->config['warning_downresume']) {

                    $this->send_warning($type, $uid);
                }
            }
        } elseif ($type == 3 && $this->config['warning_addresume'] > 0) {   // 简历发布

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $num = $this->select_num('resume_expect', array('uid' => $uid, 'ctime' => array('>', $today)));

                if ($num >= $this->config['warning_addresume']) {

                    $this->send_warning($type, $uid);
                }
            }
        } elseif ($type == 4 && $this->config['warning_recharge'] > 0) {    // 充值

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $order = $this->select_all('company_order', array('uid' => $uid, 'order_state' => 2, 'integral' => array('>', 0), 'order_time' => array('>', $today)), 'sum(`integral`) as `total`');

                if (!empty($order)) {

                    $price = ceil($order[0]['total'] / $this->config['integral_proportion']);

                    if ($price >= $this->config['warning_recharge']) {

                        $this->send_warning($type, $uid);
                    }
                }
            }
        } else if ($type == 5 && $this->config['sy_hour_msgnum'] > 0) { // 短信预警

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $time = time() - 3600;
                $num = $this->select_num('moblie_msg', array('ctime' => array('>', $time)));
                $msg = "系统一小时内已发送" . $num . "条短信！";

                if ($num >= $this->config['sy_hour_msgnum']) {

                    $this->send_warning($type, $uid, $msg);
                }
            }
        } else if ($type == 6) {    //每天简历投递

            if ($this->config['warning_sendresume'] > 0) {   //  限制投递数量

                $isWaring = $this->isWarning($type, $uid);

                if (!$isWaring) {
                    $num = $this->select_num('userid_job', array('uid' => $uid, 'datetime' => array('>', $today)));

                    if ($num >= $this->config['warning_sendresume']) {

                        $this->send_warning($type, $uid);
                    }
                }
            }

            if ($this->config['warning_sqjob'] > 0) {   //  限制投递类别

                $isWaring = $this->isWarning($type, $uid);

                if (!$isWaring) {

                    $sqList = $this->select_all('userid_job', array('uid' => $uid, 'datetime' => array('>', $today), 'limit' => 500), 'job_id');

                    $sqJobIds = array();
                    foreach ($sqList as $sqk => $sqv) {
                        $sqJobIds[] = $sqv['job_id'];
                    }

                    $sqJobList = $this->select_all('company_job', array('id' => array('in', pylode(',', $sqJobIds))), 'job1');
                    $jobClassIdArr = array();
                    foreach ($sqJobList as $jk => $jv) {
                        if (!in_array($jv['job1'], $jobClassIdArr)) {
                            $jobClassIdArr[] = $jv['job1'];
                        }
                    }

                    if (count($jobClassIdArr) >= $this->config['warning_sqjob']) {

                        $this->send_warning($type, $uid, '', 1);
                    }
                }
            }
        } else if ($type == 7 && $this->config['warning_lookresume'] > 0) {    //  浏览简历

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $num    =   $this->select_num('look_resume', array('com_id' => $uid, 'datetime' => array('>', $today)));

                if ($num >= $this->config['warning_lookresume']) {

                    $this->send_warning($type, $uid);
                }
            }
        } else if ($type == 8 && $this->config['warning_lookjob'] > 0) {    //  浏览职位

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $num    =   $this->select_num('look_job', array('uid' => $uid, 'datetime' => array('>', $today)));

                if ($num >= $this->config['warning_lookjob']) {

                    $this->send_warning($type, $uid);
                }
            }
        } else if ($type == 9 && $this->config['warning_teljob'] > 0) {    //  求职者拨号

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $num    =   $this->select_num('job_tellog', array('uid' => $uid, 'ctime' => array('>', $today)));

                if ($num >= $this->config['warning_teljob']) {

                    $this->send_warning($type, $uid);
                }
            }
        } else if ($type == 10 && $this->config['warning_privacy_com'] > 0) {    //  查看企业隐私号

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $num    =   $this->select_num('privacy_log', array('uid' => $uid, 'result' => 1, 'type' => 2, 'ctime' => array('>', $today)));

                if ($num >= $this->config['warning_teljob']) {

                    $this->send_warning($type, $uid);
                }
            }
        } else if ($type == 11 && $this->config['warning_privacy_user'] > 0) {    //  查看个人隐私号

            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $num    =   $this->select_num('privacy_log', array('comid' => $uid, 'result' => 1, 'type' => 1, 'ctime' => array('>', $today)));

                if ($num >= $this->config['warning_teljob']) {

                    $this->send_warning($type, $uid);
                }
            }
        } else if ($type == 12 && $this->config['warning_reg_ip'] > 0){ //注册账号同一IP
            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                $num    =   $this->select_num('member', array('reg_ip' => $ip,'reg_date' => array('>', $today)));

                if ($num >= $this->config['warning_reg_ip']) {

                    $this->send_warning($type, $uid);
                }
            }
        } else if ($type == 15){ //违禁词预警
            $isWaring = $this->isWarning($type, $uid);

            if (!$isWaring) {
                
                $this->send_warning($type,$uid,null,0,$data);
                
            }
        }
    }

    private function isWarning($type, $uid)
    {

        $result = false;
        if ($type == 5) {

            $isWarning = $this->select_once('warning', array('type' => $type, 'ctime' => array('>', strtotime('-1 hours'))));
        }else if($type == 15) {

            $isWarning = $this->select_once('warning', array('type' => $type, 'uid' => $uid, 'status'=>1, 'ctime' => array('>', strtotime('today'))));
        }else {

            $isWarning = $this->select_once('warning', array('type' => $type, 'uid' => $uid, 'ctime' => array('>', strtotime('today'))));
        }
        if (!empty($isWarning)) {

            $result = true;
        }
        return $result;
    }

    /**
     * @desc  预警提醒
     * @param int $type 1-发布职位 2-简历下载 3-发布简历 4-充值 5-短信 6-投递 7-浏览简历 8-浏览职位 9-拨号 10-企业隐私号 11-个人隐私号 12-注册账号同一IP 13-发布职位同一IP 14-创建简历同一IP 15-聊天关键词预警 16-聊天人数预警 17-交换联系方式预警
     * @param null $uid
     * @param string $emailcoment
     * @param int $way
     */
    function send_warning($type, $uid = null, $emailcoment = null, $way=0,$data=array())
    {

        $member     = $this->select_once('member', array('uid' => $uid), '`email`,`username`,`usertype`');
        $wid = $this->insert_into('warning',array('usertype' => $member['usertype'], 'type' => $type, 'uid' => $uid, 'status' => 2, 'ctime' => time()));

        $email      =   $member['email'];
        $username   =   trim($member['username']);
        $usertype   =   intval($member['usertype']);

        include_once('resume.model.php');
        $resumeM    =   new resume_model($this->db, $this->def);

        require_once('notice.model.php');
        $notice     =   new notice_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));

        require_once('cookie.model.php');
        $cookie     =   new cookie_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));

        if ($type == 1) { // 发布职位

            $emailcoment    =   '用户：【' . $username . '】发布职位超出规定数目，请检查是否有问题';
            $thing = '发布职位超出规定数目';
            if ($this->config['warning_addjob_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '发布职位超出规定数目'), array('uid' => $uid));

                if ($usertype == 2) {

                    $this->update_once('company', array('r_status' => 2), array('uid' => $uid));

                    $this->update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                } else if ($usertype == 3) {

                    $this->update_once('lt_info', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                }

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '发布职位超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天发布职位限制)';
            }
        } elseif ($type == 2) { // 下载简历

            $emailcoment    =   '用户：【' . $username . '】下载简历超出规定数目，请检查是否有问题';
            $thing = '下载简历超出规定数目';
            if ($this->config['warning_downresume_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '下载简历超出规定数目'), array('uid' => $uid));

                if ($usertype == 2) {

                    $this->update_once('company', array('r_status' => 2), array('uid' => $uid));

                    $this->update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                } else if ($usertype == 3) {

                    $this->update_once('lt_info', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                }

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '下载简历超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天下载简历限制)';
            }
        } elseif ($type == 3) { // 简历发布

            $emailcoment    =   '用户：【' . $username . '】简历发布超出规定数目，请检查是否有问题';
            $thing = '简历发布超出规定数目';
            if ($this->config['warning_addresume_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '简历发布超出规定数目'), array('uid' => $uid));
                $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));

                $resumeM->setExpectState(array('state' => 3, 'r_status' => 2, 'statusbody' => '简历发布超出规定数目'), array('uid' => $uid));

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '简历发布超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天发布简历限制)';
            }
        } elseif ($type == 4) { // 充值

            $emailcoment    =   '用户：【' . $username . '】充值超出规定金额，请检查是否有问题';
            $thing = '充值超出规定金额';
            if ($this->config['warning_recharge_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '充值超出规定金额'), array('uid' => $uid));

                if ($usertype == 1) {

                    $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));
                    $resumeM->setExpectState(array('state' => '0', 'r_status' => 2), array('uid' => $uid));
                } else if ($usertype == 2) {

                    $this->update_once('company', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                } else if ($usertype == 3) {

                    $this->update_once('lt_info', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                }

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '充值超出规定金额', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天充值金额限制)';
            }
        } else if ($type == 5 && $this->config['warning_closemsg_type'] == 1) {

            $this->update_once('admin_config', array('config' => 2, 'name' => 'sy_msg_isopen'));

        } else if ($type == 6) { // 简历投递

            if ($way == 1){

                $emailcoment    =   '用户：【'.$username.'】简历投递超出规定分类，请检查是否有问题';
                $thing = '简历投递超出规定分类';
                if ($this->config['warning_sqjob_type'] == 3){
                    $this->update_once('member', array('status' => 2, 'lock_info' => '简历投递超出限定'), array('uid' => $uid));
                    $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));

                    $resumeM->setExpectState(array('state' => 3, 'r_status' => 2), array('uid' => $uid));

                    $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '简历投递超出限定', 'type' => 'lock'));
                    $cookie->unset_cookie();
                    $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天简历投递超出限制)';
                }
            }else{

                $emailcoment    =   '用户：【'.$username.'】简历投递超出规定数目，请检查是否有问题';
                $thing = '简历投递超出规定数目';
                if ($this->config['warning_sendresume_type'] == 3){
                    $this->update_once('member', array('status' => 2, 'lock_info' => '简历投递超出限定'), array('uid' => $uid));
                    $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));

                    $resumeM->setExpectState(array('state' => 3, 'r_status' => 2), array('uid' => $uid));

                    $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '简历投递超出限定', 'type' => 'lock'), '');
                    $cookie->unset_cookie();
                    $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天简历投递超出限制)';
                }
            }
        } else if ($type == 7){ //  浏览简历

            $emailcoment    =   '用户：【' . $username . '】浏览简历超出规定数目，请检查是否有问题';
            $thing = '浏览简历超出规定数目';
            if ($this->config['warning_lookresume_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '浏览简历超出规定数目'), array('uid' => $uid));

                if ($usertype == 2) {
                    $this->update_once('company', array('r_status' => 2), array('uid' => $uid));

                    $this->update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                } elseif ($usertype == 3) {

                    $this->update_once('lt_info', array('r_status' => 2), array('uid' => $uid));

                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                }

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '浏览简历超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天浏览简历限制)';
            }
        }  else if ($type == 8){ //  浏览职位

            $emailcoment    =   '用户：【' . $username . '】浏览职位超出规定数目，请检查是否有问题';
            $thing = '浏览职位超出规定数目';
            if ($this->config['warning_lookjob_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '浏览职位超出规定数目'), array('uid' => $uid));
                $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));

                $resumeM->setExpectState(array('state' => 3, 'r_status' => 2, 'statusbody' => '浏览职位超出规定数目'), array('uid' => $uid));

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '浏览职位超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天浏览职位限制)';
            }
        } else if ($type == 9){ //  拨号限制

            $emailcoment    =   '用户：【' . $username . '】拨号次数超出规定数目，请检查是否有问题';
            $thing = '拨号次数超出规定数目';
            if ($this->config['warning_teljob_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '拨号次数超出规定数目'), array('uid' => $uid));
                $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));

                $resumeM->setExpectState(array('state' => 3, 'r_status' => 2, 'statusbody' => '拨号次数超出规定数目'), array('uid' => $uid));

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '拨号次数超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天拨号次数限制)';
            }
        } else if ($type == 10){ //  查看企业隐私号

            $emailcoment    =   '用户：【' . $username . '】查看企业隐私号次数超出规定数目，请检查是否有问题';
            $thing = '查看企业隐私号次数超出规定数目';
            if ($this->config['warning_privacy_com_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '查看企业隐私号次数超出规定数目'), array('uid' => $uid));
                $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));

                $resumeM->setExpectState(array('state' => 3, 'r_status' => 2, 'statusbody' => '查看企业隐私号次数超出规定数目'), array('uid' => $uid));

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '查看企业隐私号次数超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天查看企业隐私号次数限制)';
            }
        } else if ($type == 11){ //  查看个人隐私号

            $emailcoment    =   '用户：【' . $username . '】查看个人隐私号次数超出规定数目，请检查是否有问题';
            $thing = '查看个人隐私号超出规定数目';
            if ($this->config['warning_privacy_user_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '查看个人隐私号超出规定数目'), array('uid' => $uid));

                $this->update_once('company', array('r_status' => 2), array('uid' => $uid));

                $this->update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                $this->update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '查看个人隐私号次数超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-每天查看个人隐私号次数限制)';
            }
        } else if ($type == 12){ //  注册账号同一IP

            $emailcoment    =   '用户：【' . $username . '】您注册账号过多，请检查是否有问题';
            $thing = '同一IP每天注册账号超出规定数目';
            if ($this->config['warning_reg_ip_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => '同一IP每天注册账号超出规定数目'), array('uid' => $uid));
                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '同一IP每天注册账号超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-同一IP每天注册账号次数限制)';
            }
        } elseif ($type == 15) { // 聊天关键词预警
            
            $word_str = $contentV = '';
            
            $word_arr = array_keys($data['wordlist']);

            $word_n = implode(',', $word_arr);

            if(count($word_arr)>1){
                $word_str = $word_arr[0].','.$word_arr[1].'等';
            }else{
                $word_str = $word_arr[0];
            }
            
            if($data['bantype'] == 'chat'){

                $bantype_n   =   '聊天内容';
                $contentV    =   '用户与【'.$data['tname'].'】(用户id:'.$data['toid'].')的聊天含有违禁词：'.$word_n;

            }else if($data['bantype'] == 'addjobname'){
                
                $bantype_n = '职位名称';
                
                $contentV    =   '用户发布的职位【'.$word_n.'】(职位id:'.$data['jobid'].')，职位名称含有违禁词：'.$word_n;
                
            }else if($data['bantype'] == 'addjob'){

                $bantype_n = '职位描述';

                $contentV    =   '用户发布的职位【'.$data['jname'].'】(职位id:'.$data['jobid'].')，职位描述含有违禁词：'.$word_n;

            }else if($data['bantype'] == 'partjob'){

                $bantype_n = '兼职内容';

                $contentV    =   '用户发布的兼职【'.$data['jname'].'】(兼职id:'.$data['jobid'].')，兼职内容含有违禁词：'.$word_n;

            }else if($data['bantype'] == 'resume'){

                $bantype_n = '自我介绍';

                $contentV    =   '用户的自我介绍中含有违禁词：'.$word_n;
            }

            $emailcoment    =   '用户：【' . $username . '】'.$bantype_n.'含有违禁词：'.$word_str.'，请检查是否有问题';
            $thing = $bantype_n.'含有违禁词';
            $this->update_once('warning', array('content' =>$contentV), array('id' => $wid));

            if ($this->config['warning_banword_type'] == 1) { // 锁定帐号

                $this->update_once('member', array('status' => 2, 'lock_info' => $bantype_n.'含有违禁词'), array('uid' => $uid));

                if ($usertype == 2) {

                    $this->update_once('company', array('r_status' => 2), array('uid' => $uid));

                    $this->update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));

                }else if($usertype == 1){

                    $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));

                    $resumeM->setExpectState(array('state' => 3, 'r_status' => 2, 'statusbody' => $bantype_n.'含有违禁词'), array('uid' => $uid));
                }

                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => $bantype_n.'含有违禁词', 'type' => 'lock'));
                $cookie->unset_cookie();

                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-'.$bantype_n.'含有违禁词)';
            }
        } else if ($type == 16){ //  聊天人数预警
            $emailcoment = '用户：【' . $username . '】您聊天人数过多，请检查是否有问题';
            $thing = '当日聊天人数超出规定数目';
            if ($this->config['warning_chat_num_type'] == 1) { // 锁定帐号
                $this->update_once('member', array('status' => 2, 'lock_info' => '当日聊天人数超出规定数目'), array('uid' => $uid));
                if ($usertype == 1) {// 个人
                    $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));
                    $resumeM->setExpectState(array('state' => 3, 'r_status' => 2, 'statusbody' => '当日聊天人数超出规定数目'), array('uid' => $uid));
                } else if ($usertype == 2) {// 企业
                    $this->update_once('company', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                }
                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '当日聊天人数超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();
                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-当日聊天人数超出规定数目)';
            }
        } else if ($type == 17){ //  交换联系方式预警
            $emailcoment = '用户：【' . $username . '】您申请交换联系方式过多，请检查是否有问题';
            $thing = '当日申请交换联系方式超出规定数目';
            if ($this->config['warning_exchange_link_type'] == 1) { // 锁定帐号
                $this->update_once('member', array('status' => 2, 'lock_info' => '当日申请交换联系方式超出规定数目'), array('uid' => $uid));
                if ($usertype == 1) {// 个人
                    $this->update_once('resume', array('r_status' => 2), array('uid' => $uid));
                    $resumeM->setExpectState(array('state' => 3, 'r_status' => 2, 'statusbody' => '当日聊天人数超出规定数目'), array('uid' => $uid));
                } else if ($usertype == 2) {// 企业
                    $this->update_once('company', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('company_job', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('partjob', array('r_status' => 2), array('uid' => $uid));
                    $this->update_once('lt_job', array('r_status' => 2), array('uid' => $uid));
                }
                $notice->sendEmailType(array('email' => $email, 'uid' => $uid, 'username' => $username, 'lock_info' => '当日申请交换联系方式超出规定数目', 'type' => 'lock'));
                $cookie->unset_cookie();
                $logContent =   '账户锁定(ID:' . $uid . '。原因：预警-当日申请交换联系方式超出规定数目)';
            }
        }

        require_once('admin.model.php');
        $adminM     =   new admin_model($this->db, $this->def);
        $adminM->sendAdminMsg(array('first' => $emailcoment, 'type' => 2,'customer'=>$username,'thing'=>$thing));

        // 发送邮件提醒管理员
        $emailData  =   array('email' => $this->config['sy_webemail'], 'subject' => '预警提醒', 'content' => $emailcoment);
        $notice->sendEmail($emailData);

        if (!empty($logContent)) {
            // 锁定账号的记录管理员日志
            $data   =   array('uid' => 0, 'username' => '预警', 'content' => $logContent, 'ctime' => time(), 'ip' => fun_ip_get(), 'did' => $this->config['did']);
            $this->insert_into('admin_log', $data);
        }
    }
    
    function addKeywordFile($data=array()){
        
        $banwords = !empty($data['sy_banwords'])?unserialize($data['sy_banwords']):array();
        
        $name   =   array();
        foreach($banwords as $k=>$v){
            $name[] =   $v;
        }
        if(!empty($name)){
            
            $nstr   =   implode("\r\n", $name);
        }else{
            
            $nstr   =   '';
        }
        
        $path   =   DATA_PATH.'sensitive/banword.txt';
        
        $fp = @fopen($path,w);
        @fwrite($fp,"$nstr");
        @fclose($fp);
    }

    function checkBanWord($data=array()){
        
        $uid = $data['uid'];
        $content = $data['content'];

        if($uid && $content){

            include_once(LIB_PATH.'sensitive.class.php');
            $instance = Sensitive::getInstance();
            
            if(file_exists(DATA_PATH.'sensitive/banword.txt')){

                $instance->addSensitiveWords(DATA_PATH.'sensitive/banword.txt');
                $sensitive_res    =   $instance->execBanWordFilter($content);
                
                if($sensitive_res['error']==1){
                    $data['wordlist'] = $sensitive_res['wordList'];
                    $this->warning(15,$uid,fun_ip_get(),$data);
                }
            }
        }
    }
}
?>