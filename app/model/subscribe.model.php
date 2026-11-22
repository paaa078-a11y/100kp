<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2024 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class subscribe_model extends model
{

    /**
     * @desc   引用log类，添加用户日志
     * @param $uid
     * @param $usertype
     * @param $content
     * @param string $opera
     * @param string $type
     * @return void
     */
    private function addMemberLog($uid, $usertype, $content, $opera = '', $type = '')
    {
        require_once('log.model.php');
        $LogM   =   new log_model($this->db, $this->def);
        return $LogM->addMemberLog($uid, $usertype, $content, $opera = '', $type = '');
    }

    /**
     * @desc   获取缓存数据
     * @param $options
     * @return array|string|void
     */
    private function getClass($options)
    {

        if (!empty($options)) {

            include_once('cache.model.php');
            $cacheM =   new cache_model($this->db, $this->def);
            $cache  =   $cacheM->GetCache($options);
            return $cache;
        }
    }

    /**
     * 获取订阅列表
     * @param $whereData
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getList($whereData, $data = array())
    {

        $List   =   $this->select_all('subscribe', $whereData);
        if (!empty($List)) {

            $cache  =   $this->getClass(array('job', 'city', 'user'));

            $uid_arr = $userData = array();

            foreach ($List as $key => $val) {
                if ($val['uid'] && !in_array($val['uid'], $uid_arr)) {

                    $uid_arr[] = $val['uid'];
                }
            }
            if (!empty($uid_arr)) {

                $utype  =   !empty($data['utype']) ? $data['utype'] : '';

                include_once('userinfo.model.php');
                $userInfoM  =   new userinfo_model($this->db, $this->def);
                $userList   =   $userInfoM->getList(array('uid' => array('in', pylode(',', $uid_arr))), array(), $utype);

                foreach ($userList as $uk => $uv) {
                    $userData[$uv['uid']] = $uv;
                }
            }

            foreach ($List as $k => $v) {

                $List[$k]['uname'] = !empty($userData[$v['uid']]['countname']) ? $userData[$v['uid']]['countname'] : '';

                $jobname = $cityname = array();

                $jobNameStr = $cityNameStr = $salary = '';

                if ($v['type'] == 1) {

                    $jobClass = explode(',', $v['jobclass_id']);
                    foreach ($jobClass as $jk => $jv) {

                        $jobname[]  =   $cache['job_name'][$jv];
                    }
                    $jobNameStr    =   @implode(',', $jobname);
                } else {

                    $jobname[]  =   $cache['job_name'][$v['job1']];
                    $jobname[]  =   $cache['job_name'][$v['job1_son']];
                    if ($cache['job_name'][$v['job_post']]) {
                        $jobname[]  = $cache['job_name'][$v['job_post']];
                    }
                    $jobNameStr=   @implode(',', $jobname);
                    $cityname[] =   $cache['city_name'][$v['provinceid']];
                    if ($cache['city_name'][$v['cityid']]) {
                        $cityname[] =   $cache['city_name'][$v['cityid']];
                    }
                    if ($cache['city_name'][$v['three_cityid']]) {
                        $cityname[] = $cache['city_name'][$v['three_cityid']];
                    }
                    $cityNameStr = @implode('-', $cityname);
                    $salary = salaryUnit($v['minsalary'], $v['maxsalary']);
                }

                $List[$k]['jobname']    =   $jobNameStr;
                $List[$k]['city_name']  =   $cityNameStr;
                $List[$k]['salary']     =   $salary;
            }
        }

        return $List;
    }

    public function getInfo($where = array(), $data = array())
    {

        $select =   $data['field'] ? $data['field'] : '*';
        $Info   =   $this->select_once('subscribe', $where, $select);
        if (!empty($Info)) {

            $cache = $this->getClass(array('job', 'city', 'user'));

            if ($Info['type'] == 1) {

                $jobname = $jobnameArr = array();

                $jobNameStr = '';

                $jobclass = explode(',', $Info['jobclass_id']);

                foreach ($jobclass as $jk => $jv) {
                    $jobname[] = $cache['job_name'][$jv];
                    $jobnameArr[$jv] = $cache['job_name'][$jv];
                }

                $jobNameStr = @implode(',', $jobname);
                $Info['jobname_n'] = $jobNameStr;
                $Info['jobclass'] = $jobclass;
                $Info['jobnameArr'] = $jobnameArr;
            } else if ($Info['type'] == 2) {

                $jobNameStr = '';

                if ($Info['job1']) {
                    $jobNameStr .= $cache['job_name'][$Info['job1']];
                }
                if ($Info['job1_son']) {
                    $jobNameStr .= ' ' . $cache['job_name'][$Info['job1_son']];
                }
                if ($Info['job_post']) {
                    $jobNameStr .= ' ' . $cache['job_name'][$Info['job_post']];
                }

                $Info['jobname_n'] = $jobNameStr;

                $cityNameStr = '';

                if ($Info['provinceid']) {
                    $cityNameStr .= $cache['city_name'][$Info['provinceid']];
                }
                if ($Info['cityid']) {
                    $cityNameStr .= ' ' . $cache['city_name'][$Info['cityid']];
                }
                if ($Info['three_cityid']) {
                    $cityNameStr .= ' ' . $cache['city_name'][$Info['three_cityid']];
                }

                $Info['cityname_n'] = $cityNameStr;

            }
        }
        return $Info;
    }

    function upInfo($data = array(), $Where = array())
    {

        return $this->update_once('subscribe', $data, $Where);
    }

    function addInfo($data = array())
    {

        return $this->insert_into('subscribe', $data);
    }

    public function getNum($where = array())
    {

        return $this->select_num('subscribe', $where);
    }

    public function getRecordNum($where = array())
    {

        return $this->select_num('subscriberecord', $where);
    }

    /**
     * 获取订阅记录列表subscriberecord
     * @param $whereData
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getRecordList($whereData, $data = array())
    {

        $List   =   $this->select_all('subscriberecord', $whereData);

        if (!empty($List)) {

            require_once('job.model.php');
            $jobM   =   new job_model($this->db, $this->def);

            require_once('resume.model.php');
            $resumeM=   new resume_model($this->db, $this->def);

            $jobIdArr   =   array();
            $ridArr     =   array();

            foreach ($List as $key => $value) {
                if ($value['content']) {
                    $conentArr = explode(',', $value['content']);
                    if ($value['type'] == 1) {
                        $jobIdArr = array_unique(array_merge($conentArr, $jobIdArr));
                    } else if ($value['type'] == 2) {
                        $ridArr = array_unique(array_merge($conentArr, $ridArr));
                    }
                }
            }
            if (!empty($jobIdArr)) {
                $jobwhere = array('id' => array('in', pylode(',', $jobIdArr)), 'state' => 1, 'status' => 0, 'r_status' => 1);
                $jobs = $jobM->getList($jobwhere, array('utype' => $data['utype'], 'isurl' => 'yes'));
                $jobData = array();
                foreach ($jobs['list'] as $jk => $jv) {
                    $jobData[$jv['id']] = $jv;
                }
            }
            if (!empty($ridArr)) {
                $rwhere = array('id' => array('in', pylode(',', $ridArr)), 'state' => 1, 'status' => 1, 'r_status' => 1);
                $downresume_where = array();
                if ($data['utype'] == 'user' && $data['uid']) {
                    $downresume_where['comid'] = $data['uid'];
                    $downresume_where['usertype'] = $data['usertype'];
                }
                $resumes = $resumeM->getList(
                    $rwhere,
                    array(
                        'utype' => $data['utype'],
                        'url' => 'yes',
                        'withResumeField' => 'uid,name,nametype,tag,sex,moblie_status,edu,exp,defphoto,description,photo,resume_photo,phototype,birthday,photo_status,def_job',
                        'downresume_where' => $downresume_where,
                    )
                );
                $resumeData = array();
                foreach ($resumes['list'] as $rk => $rv) {
                    $resumeData[$rv['id']] = $rv;
                }
            }

            foreach ($List as $k => $v) {

                $sendcon = array();

                if ($v['content']) {

                    $conentArr = explode(',', $v['content']);

                    foreach ($conentArr as $ck => $cv) {

                        if ($v['type'] == 1 && !empty($jobData[$cv])) {

                            $senddata = !empty($jobData[$cv]) ? $jobData[$cv] : array();

                            $wapurl = Url('wap', array("c" => "job", 'a' => 'comapply', "id" => $cv));

                        } else if ($v['type'] == 2 && !empty($resumeData[$cv])) {

                            $senddata = !empty($resumeData[$cv]) ? $resumeData[$cv] : array();

                            $wapurl = Url('wap', array('c' => 'resume', 'a' => 'show', 'id' => $cv));
                        }

                        if (!empty($senddata)) {
                            $sendcon[] = $senddata;
                        }
                    }
                }

                $List[$k]['sendcon'] = $sendcon;

                if ($v['time']) {
                    $List[$k]['time_n'] = date('Y-m-d H:m', $v['time']);
                }
            }
        }
        return $List;
    }


    public function del($id, $data)
    {

        $limit = 'limit 1';
        $return['layertype'] = 0;

        if (!empty($id)) {

            if (is_array($id)) {

                $id = pylode(',', $id);
                $return['layertype'] = 1;
                $limit = '';
            }
            if ($data['uid']) {
                $delWhere = array('uid' => $data['uid'], 'id' => array('in', $id));
            } else {
                $delWhere = array('id' => array('in', $id));
            }

            $nid = $this->delete_all('subscribe', $delWhere, $limit);

            if ($nid) {

                $this->delete_all('subscriberecord', array('sid' => array('in', $id)), $limit);
                $this->addMemberLog($data['uid'], $data['usertype'], "订阅管理：删除订阅（ID:" . $id . "）", 5, 3);
                $return['msg'] = '删除成功';
                $return['errcode'] = '9';
            } else {
                $return['msg'] = '删除失败';
                $return['errcode'] = '8';
            }
        } else {
            $return['msg'] = '请选择您要删除的记录';
            $return['errcode'] = '8';
        }
        return $return;
    }

    public function delrecord($id, $data)
    {

        $limit = 'limit 1';
        $return['layertype'] = 0;

        if (!empty($id)) {

            if (is_array($id)) {

                $id = pylode(',', $id);
                $return['layertype'] = 1;
                $limit = '';
            }
            if ($data['uid']) {
                $delWhere = array('uid' => $data['uid'], 'id' => array('in', $id));
            } else {
                $delWhere = array('id' => array('in', $id));
            }

            $nid = $this->delete_all('subscriberecord', $delWhere, $limit);

            if ($nid) {

                $return['msg'] = '删除成功';
                $return['errcode'] = '9';
            } else {
                $return['msg'] = '删除失败';
                $return['errcode'] = '8';
            }
        } else {
            $return['msg'] = '请选择您要删除的记录';
            $return['errcode'] = '8';
        }
        return $return;
    }

    /**
     * 应用中增加订阅
     * 判断了是增加或是修改
     * @param array $data
     * @return array
     */
    public function createSubscribe($data = array())
    {

        $provider = !empty($data['provider']) ? $data['provider'] : '';
        unset($data['provider']);
        $res = array('errcode' => 8, 'msg' => '');
        if ($data['utype'] != 'admin') {
            if (empty($data['uid'])) {
                $res['msg'] = '您还未登录！';
                return $res;
            }
        }

        //个人 企业用户才可订阅
        if (empty($data['usertype']) || !in_array($data['usertype'], array(1, 2))) {
            $res['msg'] = '只有个人和企业用户才可订阅！';
            return $res;
        }

        $sid = intval($data['sid']);
        $data['type'] = intval($data['type']);

        //判断订阅类型
        if ($data['usertype'] != $data['type']) {
            $res['msg'] = '订阅类型错误！';
            return $res;
        }

        //用户绑定的微信信息
        $userField = '`email`,`wxid`, `wxopenid`,`email`,`email_status`';
        $userInfo = $this->select_once('member', array('uid' => $data['uid']), $userField);
        if (empty($userInfo)) {
            $res['msg'] = '用户信息错误！';
            return $res;
        }

        if ($data['type'] == 1) {
            $jobsub = $this->getInfo(array('uid' => $data['uid'], 'type' => 1), '`id`');
            if (!empty($jobsub)) {
                $res['msg'] = '每个账号只能设置一个职位订阅！';
                return $res;
            }
        }

        //未传入订阅的id时，通过uid type判断是否已订阅
        if (empty($sid)) {
            $oldField = array('field' => '`id`');
            $oldSub = $this->getInfo(array('uid' => $data['uid'], 'type' => $data['type']), $oldField);
            if (!empty($oldSub)) {
                $sid = $oldSub['id'];
            }
        }
        if ($data['utype'] != 'admin') {

            if ($provider != '') {

                if ($provider == 'weixin') {

                    if (empty($userInfo['wxid']) && empty($userInfo['wxopenid'])) {

                        $res['msg'] = '请先绑定微信公众号';

                        return $res;
                    }
                } else {
                    if (empty($userInfo['email']) || $userInfo['email_status'] != 1) {

                        $res['msg'] = '请先前往设置页面认证绑定邮箱！';

                        return $res;
                    }
                }

            } else {
                if (empty($userInfo['wxid']) && empty($userInfo['wxopenid'])) {
                    require_once('weixin.model.php');
                    $wxM = new weixin_model($this->db, $this->def);
                    $qrcode = $wxM->applyWxQrcode($_POST['wxloginid'], '', $data['uid']);
                    if (empty($qrcode)) {
                        $res['msg'] = '暂未绑定微信公众号，无法订阅！';
                    } else {
                        $res['msg'] = '马上绑定微信, 接收订阅信息！';
                    }
                    return $res;
                }
            }

        }


        //处理参数
        $data['email'] = $userInfo['email'];
        $data['ctime'] = time();
        $data['stime'] = strtotime('+'.$data['time'].' day');

        unset($data['usertype']);
        unset($data['utype']);

        if (empty($sid)) {

            $sid = $this->addInfo($data);

        } else {
            $uid = $data['uid'];
            unset($data['sid']);
            unset($data['uid']);

            $this->upInfo($data, array('id' => $sid, 'uid' => $uid));
        }

        $res['errcode'] = 9;
        $res['msg'] = '订阅成功！';
        return $res;

    }

    /**
     * 发送订阅
     * @param array $data|type 1-订阅职位，2-订阅人才，limit 发送订阅信息的数量默认为5
     * @return array
     * @throws SmartyException
     */
    public function sendSubscribe($data = array())
    {

        global $phpyun;

        require_once('userinfo.model.php');
        $userinfoM = new userinfo_model($this->db, $this->def);

        require_once('notice.model.php');
        $noticeM = new notice_model($this->db, $this->def);

        require_once('weixin.model.php');
        $wxM = new weixin_model($this->db, $this->def);

        require_once('job.model.php');
        $jobM = new job_model($this->db, $this->def);

        require_once('resume.model.php');
        $resumeM = new resume_model($this->db, $this->def);

        $res = array('errcode' => 8, 'msg' => '');
        if (empty($data['type']) || !in_array($data['type'], array(1, 2))) {
            $res['msg'] = '缺少订阅类型参数!';
            return $res;
        }

        //设置默认订阅数量
        $data['limit'] = empty($data['limit']) ? 10 : intval($data['limit']);

        //获取需要发送的订阅
        $subscribe  =   $this->select_all('subscribe', array('type' => $data['type'], 'time' => array('>', 0), 'stime' => array('<', time()), 'orderby' => 'stime,asc', 'limit' => $data['limit']));
        if (empty($subscribe)) {
            $res['msg'] = '暂无订阅数据!';
            return $res;
        }
        $userData = $uid_arr = array();
        foreach ($subscribe as $sval) {
            if ($sval['uid'] && !in_array($sval['uid'], $uid_arr)) {
                $uid_arr[] = $sval['uid'];
            }
        }
        $userlist = $userinfoM->getList(array('uid' => array('in', pylode(',', $uid_arr))));
        foreach ($userlist as $uk => $uv) {
            $userData[$uv['uid']] = $uv;
        }
        $currentTime    =   time();
        foreach ($subscribe as $sv) {

            $sendStatus1 = true;
            $sendStatus2 = true;

            $whereData  =   array();

            //1-订阅职位，2-订阅人才
            if ($sv['type'] == 2) {

                //增加更新时间条件
                if (!empty($sv['cycle_time']) && $sv['cycle_time'] > 0) {

                    $beginTime = $sv['cycle_time'] * 86400;
                    $whereData['lastupdate'] = array('>=', $currentTime - $beginTime);
                }

                if (!empty($sv['minsalary'])) {
                    $whereData['minsalary'] = array('>=', $sv['minsalary']);
                }
                if (!empty($sv['maxsalary'])) {
                    $whereData['maxsalary'] = array('<=', $sv['maxsalary']);
                }

                if (!empty($sv['three_cityid'])) {
                    $whereData['city_classid'] = array('findin', $sv['three_cityid']);
                } elseif (!empty($sv['cityid'])) {
                    $whereData['city_classid'] = array('findin', $sv['cityid']);
                } elseif (!empty($sv['provinceid'])) {
                    $whereData['city_classid'] = array('findin', $sv['provinceid']);
                }
                if (!empty($sv['job_post'])) {
                    $whereData['job_classid'] = array('findin', $sv['job_post']);
                } elseif (!empty($sv['job1_son'])) {
                    $whereData['job_classid'] = array('findin', $sv['job1_son']);
                } elseif (!empty($sv['job1'])) {
                    $whereData['job_classid'] = array('findin', $sv['job1']);
                }

                $whereData['state'] = 1;
                $whereData['status'] = 1;
                $whereData['r_status'] = 1;
                $whereData['orderby'] = 'lastupdate';

                $resumes = $resumeM->getList($whereData, array('withResumeField' => 'uid,name,nametype,tag,sex,moblie_status,edu,exp,defphoto,description,photo,resume_photo,phototype,birthday,photo_status,def_job'));
                $subList = $resumes['list'];
            } elseif ($sv['type'] == 1) {

                if (!empty($sv['jobclass_id'])) {

                    $whereData['PHPYUNBTWSTART'] = '';
                    $whereData['job1'] = array('in', $sv['jobclass_id']);
                    $whereData['job1_son'] = array('in', $sv['jobclass_id'], 'OR');
                    $whereData['job_post'] = array('in', $sv['jobclass_id'], 'OR');
                    $whereData['PHPYUNBTWEND'] = '';
                }

                $whereData['state'] = 1;
                $whereData['status'] = 0;
                $whereData['r_status'] = 1;
                $whereData['orderby'] = 'lastupdate';

                $jobs = $jobM->getList($whereData);
                $subList = $jobs['list'];
            }

            //没查询到数据直接pass
            if (empty($subList)) {
                continue;
            }

            //订阅的职位 简历名称
            $nameArr = array();
            $idArr = array();
            foreach ($subList as $val) {
                $nameArr[] = $val['name'];
                $idArr[] = $val['id'];
            }
            $contentStr =   @implode(',', $nameArr);
            $idStr      =   @implode(',', $idArr);

            $FirstType  =   $sv['type'] == 1 ? '职位' : '人才';
            //发送微信通知信息
            $wxRes      =   $wxM->sendSubscribe(array(
                'uid'       =>  $sv['uid'],
                'First'     =>  '您在'.$this->config['sy_webname'].'上订阅的'.$FirstType.'信息',
                'content'   =>  $contentStr,
                'sid'       =>  $sv['id'],
                'thing16'   =>  '您订阅的'.$FirstType.'信息',
            ));
            if ($wxRes['errcode'] == 8) {
                $sendStatus1 = false;
            }

            //邮件通知 邮箱已认证的才发送
            if (!empty($userData[$sv['uid']]['email']) && $userData[$sv['uid']]['email_status'] == 1) {

                $emailData  =   array();
                $info       =   array(
                    'email'         =>  $userData[$sv['uid']]['email'],
                    'type'          =>  $sv['type'],
                    'type_n'        =>  $FirstType,
                    'webname'       =>  $this->config['sy_webname'],
                    'sy_webemail'   =>  $this->config['sy_webemail'],
                    'sy_freewebtel' =>  $this->config['sy_freewebtel'],
                    'sy_wx_qcode'   =>  checkpic($this->config['sy_wx_qcode'])
                );
                $phpyun->assign('info', $info);
                $phpyun->assign('subList', $subList);
                $contents = $phpyun->fetch(TPL_PATH.$this->config['style'].'/subscribe/sendemail.htm', time());

                $emailData['email'] = $userData[$sv['uid']]['email'];
                $emailData['subject'] = '您在'.$this->config['sy_webname'].'上订阅的'.$FirstType.'信息';
                $emailData['content'] = $contents;
                //入库字段
                $emailData['uid']   =   $sv['uid'];
                $emailData['cname'] =   "系统";
                $sendid =   $noticeM->sendEmail($emailData);

                if ($sendid['status'] == -1) {
                    $sendStatus2 = false;
                }
            }

            if ($sendStatus1 || $sendStatus2) {
                //保存相关订阅记录
                $recordArr = array('sid' => $sv['id'], 'uid' => $sv['uid'], 'type' => $sv['type'], 'content' => $idStr, 'time' => $currentTime);
                $this->insert_into('subscriberecord', $recordArr);
                //  更新下次发送时间
                $this->update_once('subscribe', array('stime' => strtotime('+'.$sv['time'].' day', $sv['stime'])), array('id' => $sv['id']));
            }
        }
        $res['errcode'] = 9;
        return $res;
    }

}

?>