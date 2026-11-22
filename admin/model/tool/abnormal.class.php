<?php

/**
 * Class abnormal_controller
 *
 * @author shiy
 * @version 7.0
 */

class abnormal_controller extends adminCommon
{

    //  时间条件
    private function timeNumber($timeArr, $where, $column)
    {

        if (!empty($timeArr)) {

            $startTime = date('Y-m-d', $timeArr[0] / 1000);
            $endTime = date('Y-m-d', $timeArr[1] / 1000);

            $sTime = strtotime($startTime);
            $eTime = strtotime($endTime) + 86400 - 1;

            $where['PHPYUNBTWSTART_A'] = '';
            $where[$column][] = array('>', $sTime);
            $where[$column][] = array('<', $eTime);
            $where['PHPYUNBTWEND_A'] = '';
        } else {

            $sTime = strtotime('-7 day');
            $where[$column] = array('>', $sTime);
        }
        return $where;
    }

    //  获取个人信息
    private function getUser($list, $tjM)
    {
        $uids = array();
        foreach ($list as $v) {

            $uids[] = $v['uid'];
        }

        $resume = $tjM->tjtotal('resume', array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`name`'));
        $member = $tjM->tjtotal('member', array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`username`'));

        foreach ($list as $k => $v) {
            foreach ($resume as $val) {
                if ($v['uid'] == $val['uid']) {
                    $list[$k]['name'] = $val['name'];
                }
            }
            foreach ($member as $mval) {
                if ($v['uid'] == $mval['uid']) {
                    $list[$k]['username'] = $mval['username'];
                }
            }
        }
        return $list;
    }

    //  获取企业信息
    private function getCom($list, $tjM)
    {
        $uids = array();
        $jobIdArr = array();
        foreach ($list as $v) {
            $uids[] = $v['uid'];
            $jobIdArr[] = $v['job_id'];
        }
        $com = $tjM->tjtotal('company', array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`name`, `login_date`'));
        $member = $tjM->tjtotal('member', array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`username`'));

        if ($jobIdArr) {
            $job = $tjM->tjtotal('company_job', array('id' => array('in', pylode(',', $jobIdArr))), array('field' => '`id`,`name`'));
        }

        foreach ($list as $k => $v) {
            foreach ($com as $val) {
                if ($v['uid'] == $val['uid']) {
                    $list[$k]['name'] = $val['name'];
                    $list[$k]['login_date'] = date('Y-m-d H:i:s', $val['login_date']);
                    $list[$k]['login_date_n'] = date('Y-m-d', $val['login_date']);
                }
            }
            foreach ($member as $mval) {
                if ($v['uid'] == $mval['uid']) {
                    $list[$k]['username'] = $mval['username'];
                }
            }

            if (!empty($job)) {
                foreach ($job as $jval) {
                    if ($v['job_id'] == $jval['id']) {
                        $list[$k]['job_name'] = $jval['name'];
                    }
                }
            }
        }
        return $list;
    }

    //  通过IP获取地区
    private function getCityByIp($ip)
    {

        $local = '';
        $url = "https://whois.pconline.com.cn/ipJson.jsp?ip=" . $ip . "&json=true";
        $ret = $this->transCodeToUTF8(curlGet($url));

        if (false !== $ret) {

            $output = json_decode($ret, true);
            if (isset($output['city']) && $output['city']) {

                $local = str_replace(array('市', '县', '区'), '', $output['city']);
            } else if (isset($output['pro']) && $output['pro']) {

                $local = str_replace('省', '', $output['pro']);
            }
        }
        return $local;
    }

    //  转utf8
    private function transCodeToUTF8($str)
    {
        $encode = mb_detect_encoding($str, ['ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5', 'EUC-CN']);
        return 'UTF-8' === $encode ? $str : mb_convert_encoding($str, 'UTF-8', $encode);
    }

    /**
     *  简历投递
     */
    public function getApplyList_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'tdnum');
        $rows = $gad['list'];

        if (empty($rows) || isset($_POST['list'])) {

            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby' => 'uid', 'orderby' => array('num'), 'limit' => $limit);

            $where = $this->timeNumber($_POST['time'], $where, 'datetime');
            $rows = $tjM->tjtotal('userid_job', $where, array('field' => 'COUNT(*) AS num,uid'));
            if (!empty($rows) && !isset($_POST['list'])) {

                $tjM->setabnormal($_POST['time'], 'tdnum', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getUser($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     *  投递分类
     */
    public function getApplyClass_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'tdclass');

        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {

            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array();

            $where = $this->timeNumber($_POST['time'], $where, 'datetime');

            // 查询简历投递记录
            $ujobs = $tjM->tjtotal('userid_job', $where, array('field' => 'uid,job_id'));


            // 根据投递记录查询所有被投递的职位
            $jobid = $sortlist = $classlist = array();
            foreach ($ujobs as $v) {
                $jobid[] = $v['job_id'];
            }
            $jobid = array_unique($jobid);
            $jobs = $tjM->tjtotal('company_job', array('id' => array('in', pylode(',', $jobid))), array('field' => 'id,job1'));
            $jobArr =   array();
            foreach ($jobs as $jv){
                $jobArr[$jv['id']]   =   $jv['job1'];
            }
            // 处理所有被投递的职位数据，获得职位类别数组。按一级类别处理
            foreach ($ujobs as $k => $v) {
                $classlist[$v['uid']][] = $jobArr[$v['job_id']];
            }
            // 统计求职者投递分类数量
            foreach ($classlist as $k => $v) {
                $newclass = array_unique($v);
                $sortlist[$k] = count($newclass);
            }
            // 调整排序，得到按投递职位类别总量排序的数组
            arsort($sortlist);
            // 按需展示条数获取统计数据
            $rows = array();
            $i = 0;
            foreach ($sortlist as $k => $v) {
                if ($i < $limit) {
                    $rows[] = array('uid' => $k, 'num' => $v);
                    $i++;
                } else {
                    break;
                }
            }
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'tdclass', $rows);
                $gad['ctime'] = time();
            }
        }
        $res['list'] = $this->getuser($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     *  投递删除
     */
    public function getApplyDel_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'usersqdel');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {

            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('isdel' => 1, 'groupby' => 'uid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');
            $rows = $tjM->tjtotal('userid_job', $where, array('field' => 'COUNT(*) AS num,uid'));
            if (!empty($rows) && !isset($_POST['list'])) {

                $tjM->setabnormal($_POST['time'], 'usersqdel', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getuser($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     *  职位浏览
     */
    public function getLookJob_action()
    {
        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'lookjob');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {

            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby' => 'uid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('look_job', $where, array('field' => 'COUNT(*) AS num,uid'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'lookjob', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getuser($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     *  拨号记录
     */
    public function getTelLog_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'tellog');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('uid' => array('>', 0), 'groupby' => 'uid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'ctime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('job_tellog', $where, array('field' => 'COUNT(*) AS num,uid'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'tellog', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getuser($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 聊天人数
     */
    public function getChatLog_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'chatlog');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('usertype' => $_POST['usertype'], 'fusertype' => 2, 'groupby' => 'uid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'ntime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('chat_friend', $where, array('field' => 'COUNT(*) AS `num`,`uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'chatlog', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getuser($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     *  登录次数
     */
    public function getLoginLog_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'loginlog');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array(
                'opera' => 32,
                'type' => 1,
                'content' => array('like', '登录'),
                'usertype' => 1,
                'groupby' => '`uid`',
                'orderby' => array('num'),
                'limit' => $limit
            );
            $where = $this->timeNumber($_POST['time'], $where, 'ctime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('member_log', $where, array('field' => 'COUNT(*) AS `num`,`uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'loginlog', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getuser($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * IP活跃
     */
    public function getWebLog_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'weblog');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array(
                'usertype' => 1,
                'groupby' => '`ip`',
                'orderby' => array('num'),
                'limit' => $limit
            );

            if (trim($_POST['ip'])) {
                $where['ip'] = trim($_POST['ip']);
            }

            $where = $this->timeNumber($_POST['time'], $where, 'time');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('web_log', $where, array('field' => 'COUNT(*) AS `num`,`ip`'));

            foreach ($rows as $k => $v) {
                $rows[$k]['city'] = $this->getCityByIp($v['ip']);
                // 按IP查询用户量
                $logwhere = array(
                    'ip' => $v['ip'],
                    'usertype' => 1,
                    'groupby' => 'uid'
                );
                $logwhere = $this->timeNumber($_POST['time'], $logwhere, 'time');
                $logs = $tjM->tjtotal('web_log', $logwhere, array('field' => '`uid`'));
                $rows[$k]['usernum'] = count($logs);
            }
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'weblog', $rows);
                $gad['ctime'] = time();
            }
        }
        $res['list'] = $rows;
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 职位隐私号
     */
    public function getPrivacyLog_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'privacylog');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('jobid' => array('>', 0), 'groupby' => 'uid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'ctime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('privacy_log', $where, array('field' => 'COUNT(*) AS num,uid'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'privacylog', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getuser($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 简历浏览
     */
    public function getLookResume_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'lookresume');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby' => 'com_id', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('look_resume', $where, array('field' => 'COUNT(*) AS `num`,`com_id` AS `uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'lookresume', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getCom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 简历投递未查看
     */
    public function getNotViewResume_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'notviewresume');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('is_browse' => 1, 'groupby' => 'com_id', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');
            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('userid_job', $where, array('field' => 'COUNT(*) AS `num`,`com_id` AS `uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'notviewresume', $rows);
                $gad['ctime'] = time();
            }
        }
        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {

            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }
        $this->render_json(0, '', $res);
    }

    /**
     * 简历下载
     */
    public function getDownResume_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'downresume');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby' => 'comid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'downtime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('down_resume', $where, array('field' => 'COUNT(*) AS `num`,`comid` AS `uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'downresume', $rows);
                $gad['ctime'] = time();
            }
        }
        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }
        $this->render_json(0, '', $res);
    }

    /**
     * 删除简历下载记录
     */
    public function getDownDel_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'downdel');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('isdel' => 2, 'groupby' => 'comid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'downtime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('down_resume', $where, array('field' => 'COUNT(*) AS `num`,`comid` AS `uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'downdel', $rows);
                $gad['ctime'] = time();
            }
        }
        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     *  邀请面试
     */
    public function getInvite_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'yqms');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby' => 'fid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('userid_msg', $where, array('field' => 'COUNT(*) AS `num`,`fid` AS `uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'yqms', $rows);
                $gad['ctime'] = time();
            }
        }
        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }
        $this->render_json(0, '', $res);
    }

    /**
     * 发布职位
     */
    public function getAddJob_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'addjob');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby' => 'uid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'sdate');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('company_job', $where, array('field' => 'COUNT(*) AS num,uid'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'addjob', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 刷新职位
     */
    public function getRefreshJob_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'refreshjob');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('type' => 1, 'groupby' => 'uid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'r_time');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('job_refresh_log', $where, array('field' => 'COUNT(*) AS num,uid'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'refreshjob', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 收到简历
     */
    public function getSqJob_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'sqjob');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby' => 'com_id', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('userid_job', $where, array('field' => 'COUNT(*) AS `num`,`com_id` AS `uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'sqjob', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 企业删除投递简历记录
     */
    public function getSqJobDel_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'comsqdel');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('isdel' => 2, 'groupby' => 'com_id', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');

            $rows = $tjM->tjtotal('userid_job', $where, array('field' => 'COUNT(*) AS num,`com_id` AS `uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'comsqdel', $rows);
                $gad['ctime'] = time();
            }
        }
        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 直聊人数
     */
    public function getChatList_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'comchatlog');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('usertype' => $_POST['usertype'], 'fusertype' => 1, 'groupby' => 'uid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'ntime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('chat_friend', $where, array('field' => 'COUNT(*) AS `num`,`uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'comchatlog', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 登录统计
     */
    public function getComLoginLog_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'comloginlog');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array(
                'opera' => 32,
                'type' => 1,
                'content' => array('like', '登录成功'),
                'usertype' => 2,
                'groupby' => '`uid`',
                'orderby' => array('num'),
                'limit' => $limit
            );
            $where = $this->timeNumber($_POST['time'], $where, 'ctime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('member_log', $where, array('field' => 'COUNT(*) AS `num`,`uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'comloginlog', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 企业IP活跃统计
     */
    public function getComWebLog_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'comweblog');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {

            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('usertype' => 2, 'groupby' => '`ip`', 'orderby' => array('num'), 'limit' => $limit);
            if (isset($_POST['ip']) && !empty($_POST['ip'])) {
                $where['ip'] = trim($_POST['ip']);
            }
            $where = $this->timeNumber($_POST['time'], $where, 'time');
            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('web_log', $where, array('field' => 'COUNT(*) AS `num`,`ip`'));
            foreach ($rows as $k => $v) {
                $rows[$k]['city'] = $this->getCityByIp($v['ip']);
                // 按IP查询用户量
                $logwhere = array('ip' => $v['ip'], 'usertype' => 2, 'groupby' => 'uid');
                $logwhere = $this->timeNumber($_POST['time'], $logwhere, 'time');
                $logs = $tjM->tjtotal('web_log', $logwhere, array('field' => '`uid`'));
                $rows[$k]['usernum'] = count($logs);
            }
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'comweblog', $rows);
                $gad['ctime'] = time();
            }
        }
        $res['list'] = $rows;
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }
        $this->render_json(0, '', $res);
    }

    /**
     * 简历隐私号
     */
    public function getJobPrivacyLog_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'jobprivacylog');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('eid' => array('>', 0), 'groupby' => 'comid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'ctime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('privacy_log', $where, array('field' => 'COUNT(*) AS `num`,`comid` AS `uid`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'jobprivacylog', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 被投递岗位
     */
    public function getJobSq_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'jobsq');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby' => 'job_id', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'datetime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('userid_job', $where, array('field' => 'COUNT(*) AS `num`,`com_id` AS `uid`,`job_id`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'jobsq', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 被拨号岗位
     */
    public function getJobTel_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'jobtel');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('jobid' => array('notnull'), 'groupby' => 'jobid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'ctime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('job_tellog', $where, array('field' => 'COUNT(*) AS `num`,`comid` AS `uid`,`jobid` AS `job_id`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'jobtel', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 被拨号企业
     */
    public function getComTel_action()
    {

        $tjM = $this->MODEL('tongji');
        $gad = $tjM->getAbnormalData($_POST['time'], 'companytel');
        $rows = $gad['list'];
        if (empty($rows) || isset($_POST['list'])) {
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
            $where = array('groupby' => 'comid', 'orderby' => array('num'), 'limit' => $limit);
            $where = $this->timeNumber($_POST['time'], $where, 'ctime');

            $tjM = $this->MODEL('tongji');
            $rows = $tjM->tjtotal('job_tellog', $where, array('field' => 'COUNT(*) AS `num`,`comid` AS `uid`,`jobid` AS `job_id`'));
            if (!empty($rows) && !isset($_POST['list'])) {
                $tjM->setabnormal($_POST['time'], 'companytel', $rows);
                $gad['ctime'] = time();
            }
        }

        $res['list'] = $this->getcom($rows, $tjM);
        if (isset($gad['ctime'])) {
            $res['ctime'] = date('Y-m-d H:i:s', $gad['ctime']);
        }

        $this->render_json(0, '', $res);
    }

    /**
     * 异常统计分析详情
     */
    public function detail_action()
    {
        if ($_POST['type'] == 'tdnum' || $_POST['type'] == 'tdclass' || $_POST['type'] == 'sqjob' || $_POST['type'] == 'jobsq') {

            $return = $this->userid_job();
        } elseif ($_POST['type'] == 'usersqdel' || $_POST['type'] == 'sqdel') {

            $return = $this->sqdel();
        } elseif ($_POST['type'] == 'lookjob') {

            $return = $this->lookjob();
        } elseif ($_POST['type'] == 'tellog' || $_POST['type'] == 'jobtel' || $_POST['type'] == 'companytel') {

            $return = $this->jobtellog();
        } elseif ($_POST['type'] == 'chatlog' || $_POST['type'] == 'comchatlog') {

            $return = $this->chatlog();
        } elseif ($_POST['type'] == 'loginlog' || $_POST['type'] == 'comloginlog') {

            $return = $this->loginlog();
        } elseif ($_POST['type'] == 'lookresume') {

            $return = $this->lookresume();
        } elseif ($_POST['type'] == 'notviewresume') {

            $return = $this->notviewresume();
        } elseif ($_POST['type'] == 'downresume') {

            $return = $this->downresume();
        } elseif ($_POST['type'] == 'downdel') {

            $return = $this->downdel();
        } elseif ($_POST['type'] == 'yqms') {

            $return = $this->yqms();
        } elseif ($_POST['type'] == 'addjob') {

            $return = $this->addjob();
        } elseif ($_POST['type'] == 'refreshjob') {

            $return = $this->refreshjob();
        } elseif ($_POST['type'] == 'privacylog' || $_POST['type'] == 'jobprivacylog') {

            $return = $this->privacylog();
        } elseif ($_POST['type'] == 'weblog' || $_POST['type'] == 'comweblog') {

            $return = $this->weblogtj();
        }

        $this->render_json(0, '', $return);
    }

    // 异常统计分析详情-简历投递
    private function userid_job()
    {
        if ($_POST['usertype'] == 2) {
            if ($_POST['job_id']) {
                $where = array('job_id' => $_POST['job_id']);
            } else {
                $where = array('com_id' => $_POST['uid']);
            }
        } else {
            $where = array('uid' => $_POST['uid']);
        }

        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('userid_job', $where, $_POST['page'], array('limit' => $_POST['limit']));
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                if ($_POST['order'] == "desc") {

                    $where['orderby'] = $_POST['t'] . ',desc';
                } else {

                    $where['orderby'] = $_POST['t'] . ',asc';
                }
            } else {

                $where['orderby'] = 'id,desc';
            }
            $where['limit'] = $pages['limit'];
            $jobM = $this->MODEL('job');
            $list = $jobM->getSqJobList($where, array('utype' => 'simple'));
            $jobid = $uids = array();
            foreach ($list as $v) {
                $uids[] = $v['uid'];
                $jobid[] = $v['job_id'];
            }
            $tjM = $this->MODEL('tongji');
            $resume = $tjM->tjtotal('resume', array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`name`'));
            if ($_POST['usertype'] == 1) {
                $jobs = $jobM->getList(array('id' => array('in', pylode(',', $jobid))), array('field' => 'id,job1'));
            }
            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['comid'] = $v['com_id'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
                foreach ($resume as $val) {
                    if ($v['uid'] == $val['uid']) {
                        $rows[$k]['name'] = $val['name'];
                    }
                }
                if (isset($jobs)) {
                    foreach ($jobs['list'] as $jv) {
                        if ($v['job_id'] == $jv['id']) {
                            $rows[$k]['jobclassname'] = $jv['job_one_n'];
                        }
                    }
                }
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 个人、企业删除投递简历记录
    private function sqdel()
    {

        if ($_POST['usertype'] == 2) {
            $where = array('isdel' => 2);
        } else {
            $where = array('isdel' => 1);
        }
        $where['PHPYUNBTWSTART_A'] = '';
        $where['com_id'] = array('=', $_POST['uid']);
        $where['uid'] = array('=', $_POST['uid'], 'OR');
        $where['PHPYUNBTWEND_A'] = '';
        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('userid_job', $where, $_POST['page'], array('limit' => $_POST['limit']));
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $jobM = $this->MODEL('job');
            $list = $jobM->getSqJobList($where, array('utype' => 'admin'));
            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['name'];
                $rows[$k]['comid'] = $v['com_id'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-职位浏览
    private function lookjob()
    {

        $rows = array();
        $where = array('uid' => $_POST['uid']);
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('look_job', $where, $_POST['page'], array('limit' => $_POST['limit']));
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $jobM = $this->MODEL('job');
            $list = $jobM->getLookJobList($where, array('utype' => 'admin'));

            $ip = array();
            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['comid'] = $v['com_id'];
                $rows[$k]['name'] = $v['name'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
                $rows[$k]['ip'] = $v['ip'];
                $ip[] = $v['ip'];
            }
            $city = array();
            $ip = array_unique($ip);
            foreach ($ip as $v) {
                $city[$v] = $this->getCityByIp($v);
            }
            foreach ($list as $k => $v) {
                if ($city[$v['ip']]) {
                    $rows[$k]['city'] = $city[$v['ip']];
                }
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-职位拨号记录
    private function jobtellog()
    {

        $rows = array();
        if ($_POST['job_id']) {
            $where = array('jobid' => $_POST['job_id']);
        } elseif ($_POST['com_id']) {
            $where = array('comid' => $_POST['com_id']);
        } else {
            $where = array('uid' => $_POST['uid']);
        }
        $where = $this->timeNumber($_GET['time'], $where, 'ctime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('job_tellog', $where, $_POST['page'], array('limit' => $_POST['limit']));

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $jobM = $this->MODEL('job');
            $list = $jobM->getTelLogs($where, array('utype' => 'admin'));

            $ip = array();
            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['username'];
                $rows[$k]['comid'] = $v['comid'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['ctime']);
                $rows[$k]['ip'] = $v['ip'];
                $ip[] = $v['ip'];
            }
            $city = array();
            $ip = array_unique($ip);
            foreach ($ip as $v) {
                $city[$v] = $this->getCityByIp($v);
            }
            foreach ($list as $k => $v) {
                if ($city[$v['ip']]) {
                    $rows[$k]['city'] = $city[$v['ip']];
                }
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-聊天记录
    private function chatlog()
    {

        if ($_POST['usertype'] == 2) {
            $where = array('fid' => $_POST['uid'], 'fusertype' => $_POST['usertype'], 'usertype' => 1);
        } else {
            $where = array('uid' => $_POST['uid'], 'usertype' => $_POST['usertype'], 'fusertype' => 2);
        }
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'ntime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('chat_friend', $where, $_POST['page'], array('limit' => $_POST['limit']));

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('ntime,desc');
            $where['limit'] = $pages['limit'];
            $chatM = $this->MODEL('chat');
            $list = $chatM->getFriendList($where, array('utype' => 'admin'));

            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['countname'];
                $rows[$k]['comid'] = $v['fid'];
                $rows[$k]['jobname'] = '';
                $rows[$k]['comname'] = $v['rcountname'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['ntime']);
                $rows[$k]['beginid'] = $v['beginid'];
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-登录次数
    private function loginlog()
    {

        $rows = array();
        $where = array(
            'opera' => 32,
            'type' => 1,
            'content' => array('like', '登录成功'),
            'uid' => $_POST['uid'],
            'usertype' => $_POST['usertype']
        );
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'ctime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('member_log', $where, $_POST['page'], array('limit' => $_POST['limit']));

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $logM = $this->MODEL('log');
            $list = $logM->getMemlogList($where, array('utype' => 'admin'));

            $ip = array();
            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['jobname'] = '';
                $rows[$k]['comid'] = $v['uid'];
                $rows[$k]['content'] = str_replace(array('登录成功', '账号'), '', $v['content']);
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['ctime']);
                $rows[$k]['ip'] = $v['ip'];
                if ($_POST['usertype'] == 2) {
                    $rows[$k]['name'] = '';
                    $rows[$k]['comname'] = $v['comname'];
                } else {
                    $rows[$k]['name'] = $v['rname'];
                    $rows[$k]['comname'] = '';
                }
                $ip[] = $v['ip'];
            }
            $city = array();
            $ip = array_unique($ip);
            foreach ($ip as $v) {
                $city[$v] = $this->getCityByIp($v);
            }
            foreach ($list as $k => $v) {
                if ($city[$v['ip']]) {
                    $rows[$k]['city'] = $city[$v['ip']];
                }
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-简历浏览
    private function lookresume()
    {

        $rows = array();
        $where = array('com_id' => $_POST['uid']);
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('look_resume', $where, $_POST['page'], array('limit' => $_POST['limit']));

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $lokM = $this->MODEL('lookresume');
            $list = $lokM->getList($where, array('utype' => 'admin'));

            $ip = array();
            foreach ($list['list'] as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['username'];
                $rows[$k]['comid'] = $v['com_id'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
                $rows[$k]['ip'] = $v['ip'];
                $ip[] = $v['ip'];
            }
            $city = array();
            $ip = array_unique($ip);
            foreach ($ip as $v) {
                $city[$v] = $this->getCityByIp($v);
            }
            foreach ($list['list'] as $k => $v) {
                if ($city[$v['ip']]) {
                    $rows[$k]['city'] = $city[$v['ip']];
                }
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-简历未浏览
    private function notviewresume()
    {
        $rows = array();
        $where = array('com_id' => $_POST['uid'], 'is_browse' => 1);
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('userid_job', $where, $_POST['page'], array('limit' => $_POST['limit']));

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $jobM = $this->MODEL('job');
            $list = $jobM->getSqJobList($where, array('utype' => 'admin'));

            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['name'];
                $rows[$k]['comid'] = $v['com_id'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
                $rows[$k]['body'] = $v['body'];
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-简历下载
    private function downresume()
    {

        $rows = array();
        $where = array('comid' => $_POST['uid']);
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'downtime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('down_resume', $where, $_POST['page'], array('limit' => $_POST['limit']));
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $dowM = $this->MODEL('downresume');
            $list = $dowM->getList($where, array('utype' => 'admin'));

            foreach ($list['list'] as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['username'];
                $rows[$k]['comid'] = $v['comid'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['downtime']);
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-邀请面试
    private function yqms()
    {

        $rows = array();
        $where = array('fid' => $_POST['uid']);
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'datetime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('userid_msg', $where, $_POST['page'], array('limit' => $_POST['limit']));

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $jobM = $this->MODEL('job');
            $list = $jobM->getYqmsList($where, array('utype' => 'admin'));

            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['name'];
                $rows[$k]['comid'] = $v['fid'];
                $rows[$k]['jobname'] = $v['jobname'];
                $rows[$k]['comname'] = $v['fname'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['datetime']);
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-发布职位
    private function addjob()
    {

        $rows = array();
        $where = array('uid' => $_POST['uid']);
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'sdate');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company_job', $where, $_POST['page'], array('limit' => $_POST['limit']));

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $jobM = $this->MODEL('job');
            $list = $jobM->getList($where);

            foreach ($list['list'] as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = '';
                $rows[$k]['comid'] = $v['uid'];
                $rows[$k]['jobname'] = $v['name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['sdate']);
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-刷新职位
    private function refreshjob()
    {

        $rows = array();
        $where = array('uid' => $_POST['uid'], 'type' => 1);
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'r_time');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('job_refresh_log', $where, $_POST['page'], array('limit' => $_POST['limit']));
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $logM = $this->MODEL('log');
            $list = $logM->getSxJobLogList($where);

            foreach ($list as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = '';
                $rows[$k]['comid'] = $v['uid'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['r_time']);
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-隐私号
    private function privacylog()
    {

        if ($_POST['usertype'] == 2) {
            $where = array('comid' => $_POST['uid'], 'eid' => array('>', 0));
        } else {
            $where = array('uid' => $_POST['uid'], 'jobid' => array('>', 0));
        }
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'ctime');
        $rows = array();
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('privacy_log', $where, $_POST['page'], array('limit' => $_POST['limit']));

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $priM = $this->MODEL('privacy');
            $list = $priM->getList($where);

            foreach ($list['list'] as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['sname'];
                $rows[$k]['comid'] = $v['comid'];
                $rows[$k]['comname'] = $v['fname'];
                $rows[$k]['jobname'] = $v['jobname'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['ctime']);
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    // 异常统计分析详情-IP活跃次数
    private function weblogtj()
    {

        $arr = explode('_', $_POST['ip']);
        $rows = array();
        $where = array(
            'ip' => implode('.', $arr),
            'usertype' => $_POST['usertype'],
            'groupby' => 'uid',
            'orderby' => array('num')
        );
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'time');

        $wblM = $this->MODEL('weblog');
        $list = $wblM->getWebLogList($where, array('field' => 'COUNT(*) AS `num`,`uid`,`usertype`,`ip`,`time`'));

        $ip = array();
        foreach ($list as $k => $v) {
            $rows[$k]['uid'] = $v['uid'];
            $rows[$k]['jobname'] = '';
            $rows[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
            $rows[$k]['ip'] = $v['ip'];
            $rows[$k]['num'] = $v['num'];
            if ($_POST['usertype'] == 2) {
                $rows[$k]['name'] = '';
                $rows[$k]['comname'] = $v['name_n'];
                $rows[$k]['comid'] = $v['uid'];
            } else {
                $rows[$k]['name'] = $v['name_n'];
                $rows[$k]['comname'] = '';
            }
            $ip[] = $v['ip'];
        }
        $city = array();
        $ip = array_unique($ip);
        foreach ($ip as $v) {
            $city[$v] = $this->getCityByIp($v);
        }
        foreach ($list as $k => $v) {
            if ($city[$v['ip']]) {
                $rows[$k]['city'] = $city[$v['ip']];
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        return $data;
    }

    // 异常统计分析详情-删除简历下载记录
    private function downdel()
    {

        $rows = array();
        $where = array('comid' => $_POST['uid'], 'isdel' => 2);
        // 默认查一周内数据
        $where = $this->timeNumber($_POST['time'], $where, 'downtime');
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('down_resume', $where, $_POST['page'], array('limit' => $_POST['limit']));

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {

            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $dowM = $this->MODEL('downresume');
            $list = $dowM->getList($where, array('utype' => 'admin'));

            foreach ($list['list'] as $k => $v) {
                $rows[$k]['uid'] = $v['uid'];
                $rows[$k]['name'] = $v['username'];
                $rows[$k]['comid'] = $v['comid'];
                $rows[$k]['jobname'] = $v['job_name'];
                $rows[$k]['comname'] = $v['com_name'];
                $rows[$k]['time'] = date('Y-m-d H:i:s', $v['downtime']);
            }
        }
        $data['list'] = !empty($rows) ? $rows : array();
        $data['total'] = $pages['total'];
        $data['page_sizes'] = $pages['page_sizes'];
        $data['page_size'] = $pages['page_size'];
        return $data;
    }

    /**
     * 详情页求职者数据统计弹窗
     */
    public function userStatis_action()
    {

        $tjM = $this->MODEL('tongji');

        $user = $tjM->tjtonce('resume', array('uid' => $_POST['uid']), array('field' => '`uid`,`name`,`telphone`,`def_job`'));
        $member = $tjM->tjtonce('member', array('uid' => $_POST['uid']), array('field' => '`uid`,`username`,`status`'));

        $res = array('eid' => $user['def_job'], 'uid' => $user['uid'], 'name' => $user['name'], 'username' => $member['username']);
        if (!empty($user['telphone'])) {

            $res['link'] = $user['telphone'];
        }
        if ($member['status'] == 1) {
            $res['status'] = '已审核';
        } elseif ($member['status'] == 2) {
            $res['status'] = '已锁定';
        }
//        $res['time'] = $this->datatime($_POST['time']);

        // 简历投递数量
        $tdnumw = array('uid' => $_POST['uid']);
        $tdnumw = $this->timeNumber($_POST['time'], $tdnumw, 'datetime');
        $res['tdnum_num'] = $tjM->tjtotalnum('userid_job', $tdnumw);
        // 简历投递分类
        $tdclassw = array('uid' => $_POST['uid']);
        $tdclassw = $this->timeNumber($_POST['time'], $tdclassw, 'datetime');
        $ujobs = $tjM->tjtotal('userid_job', $tdclassw, array('field' => 'uid,job_id'));
        // 根据投递记录查询所有被投递的职位
        $jobid = $classlist = array();
        foreach ($ujobs as $v) {
            $jobid[] = $v['job_id'];
        }
        $jobid = array_unique($jobid);
        $jobs = $tjM->tjtotal('company_job', array('id' => array('in', pylode(',', $jobid))), array('field' => 'id,job1,job1_son,job_post'));
        // 处理所有被投递的职位数据，获得职位类别数组。按一级类别处理
        foreach ($ujobs as $k => $v) {
            foreach ($jobs as $val) {
                if ($v['job_id'] == $val['id']) {
                    $classlist[] = $val['job1'];
                }
            }
        }
        // 统计求职者投递分类数量
        $newclass = array_unique($classlist);
        $res['tdclass_num'] = count($newclass);
        // 删除简历投递记录
        $usersqdelw = array('uid' => $_POST['uid'], 'isdel' => 1);
        $usersqdelw = $this->timeNumber($_POST['time'], $usersqdelw, 'datetime');
        $res['usersqdel_num'] = $tjM->tjtotalnum('userid_job', $usersqdelw);
        // 职位浏览
        $lookjobw = array('uid' => $_POST['uid']);
        $lookjobw = $this->timeNumber($_POST['time'], $lookjobw, 'datetime');
        $res['lookjob_num'] = $tjM->tjtotalnum('look_job', $lookjobw);
        // 职位拨号
        $tellogw = array('uid' => $_POST['uid']);
        $tellogw = $this->timeNumber($_POST['time'], $tellogw, 'ctime');
        $res['tellog_num'] = $tjM->tjtotalnum('job_tellog', $tellogw);
        // 直聊人数
        $chatlogw = array('uid' => $_POST['uid'], 'usertype' => 1, 'fusertype' => 2);
        $chatlogw = $this->timeNumber($_POST['time'], $chatlogw, 'ntime');
        $res['chatlog_num'] = $tjM->tjtotalnum('chat_friend', $chatlogw);
        // 登录次数
        $loginlogw = array('uid' => $_POST['uid'], 'opera' => 0, 'type' => 0, 'content' => array('like', '登录成功'), 'usertype' => 1);
        $loginlogw = $this->timeNumber($_POST['time'], $loginlogw, 'ctime');
        $res['loginlog_num'] = $tjM->tjtotalnum('member_log', $loginlogw);
        // 职位隐私号
        $privacylogw = array('uid' => $_POST['uid'], 'jobid' => array('>', 0));
        $privacylogw = $this->timeNumber($_POST['time'], $privacylogw, 'ctime');
        $res['privacylog_num'] = $tjM->tjtotalnum('privacy_log', $privacylogw);
        // IP活跃次数
        $weblogw = array('uid' => $_POST['uid'], 'usertype' => 1);
        $weblogw = $this->timeNumber($_POST['time'], $weblogw, 'time');
        $res['weblog_num'] = $tjM->tjtotalnum('web_log', $weblogw);

        $this->render_json(0, '', $res);
    }

    /**
     * 详情页招聘企业数据统计弹窗
     */
    public function comStatis_action()
    {

        $tjM = $this->MODEL('tongji');

        $com = $tjM->tjtonce('company', array('uid' => $_POST['uid']), array('field' => '`uid`,`name`,`linkphone`,`linktel`,`r_status`'));
        $member = $tjM->tjtonce('member', array('uid' => $_POST['uid']), array('field' => '`uid`,`username`'));

        $res = array('uid' => $com['uid'], 'name' => $com['name'], 'username' => $member['username']);
        if (!empty($com['linktel'])) {
            $res['link'] = $com['linktel'];
        } elseif (!empty($com['linkphone'])) {
            $res['link'] = $com['linkphone'];
        }
        if ($com['r_status'] == 0) {
            $res['status'] = '未审核';
        } elseif ($com['r_status'] == 1) {
            $res['status'] = '已审核';
        } elseif ($com['r_status'] == 2) {
            $res['status'] = '已锁定';
        } elseif ($com['r_status'] == 3) {
            $res['status'] = '审核未通过';
        }
        $res['url'] = Url('company', array('c' => 'show', 'id' => $com['uid']));
        // 简历浏览
        $lookresumew = array('com_id' => $_POST['uid']);
        $lookresumew = $this->timeNumber($_POST['time'], $lookresumew, 'datetime');
        $res['lookresume_num'] = $tjM->tjtotalnum('look_resume', $lookresumew);
        // 简历下载
        $downresumew = array('comid' => $_POST['uid']);
        $downresumew = $this->timeNumber($_POST['time'], $downresumew, 'downtime');
        $res['downresume_num'] = $tjM->tjtotalnum('down_resume', $downresumew);
        // 删除简历下载记录
        $downdelw = array('comid' => $_POST['uid'], 'isdel' => 2);
        $downdelw = $this->timeNumber($_POST['time'], $downdelw, 'downtime');
        $res['downdel_num'] = $tjM->tjtotalnum('down_resume', $downdelw);
        // 邀请面试
        $yqmsw = array('fid' => $_POST['uid']);
        $yqmsw = $this->timeNumber($_POST['time'], $yqmsw, 'datetime');
        $res['yqms_num'] = $tjM->tjtotalnum('userid_msg', $yqmsw);
        // 发布职位
        $addjobw = array('uid' => $_POST['uid']);
        $addjobw = $this->timeNumber($_POST['time'], $addjobw, 'sdate');
        $res['addjob_num'] = $tjM->tjtotalnum('company_job', $addjobw);
        // 刷新职位
        $refreshjobw = array('uid' => $_POST['uid'], 'usertype' => 2);
        $refreshjobw = $this->timeNumber($_POST['time'], $refreshjobw, 'r_time');
        $res['refreshjob_num'] = $tjM->tjtotalnum('job_refresh_log', $refreshjobw);
        // 收到简历
        $sqjobw = array('com_id' => $_POST['uid']);
        $sqjobw = $this->timeNumber($_POST['time'], $sqjobw, 'datetime');
        $res['sqjob_num'] = $tjM->tjtotalnum('userid_job', $sqjobw);
        // 删除收到简历记录
        $sqdelw = array('com_id' => $_POST['uid'], 'isdel' => 2);
        $sqdelw = $this->timeNumber($_POST['time'], $sqdelw, 'datetime');
        $res['sqdel_num'] = $tjM->tjtotalnum('userid_job', $sqdelw);
        // 直聊人数
        $chatlogw = array('uid' => $_POST['uid'], 'usertype' => 2, 'fusertype' => 1);
        $chatlogw = $this->timeNumber($_POST['time'], $chatlogw, 'ntime');
        $res['chatlog_num'] = $tjM->tjtotalnum('chat_friend', $chatlogw);
        // 登录次数
        $loginlogw = array('uid' => $_POST['uid'], 'opera' => 0, 'type' => 0, 'content' => array('like', '登录成功'), 'usertype' => 2);
        $loginlogw = $this->timeNumber($_POST['time'], $loginlogw, 'ctime');
        $res['loginlog_num'] = $tjM->tjtotalnum('member_log', $loginlogw);
        // 简历隐私号
        $privacylogw = array('comid' => $_POST['uid'], 'eid' => array('>', 0));
        $privacylogw = $this->timeNumber($_POST['time'], $privacylogw, 'ctime');
        $res['privacylog_num'] = $tjM->tjtotalnum('privacy_log', $privacylogw);
        // IP活跃次数
        $weblogw = array('uid' => $_POST['uid'], 'usertype' => 2);
        $weblogw = $this->timeNumber($_POST['time'], $weblogw, 'time');
        $res['weblog_num'] = $tjM->tjtotalnum('web_log', $weblogw);

        $this->render_json(0, '', $res);
    }

    // 锁定账号
    function lock_action(){


        $uid = $_POST['uid'];
        if (!$uid){
            $this->render_json(0, '参数错误,请重试');
        }
        $userinfoM  =  $this -> MODEL('userinfo');

        $post       =  array(
            'status'     =>  2,
            'lock_info'  =>  trim($_POST['lock_info'])
        );

        $return = $userinfoM->lock(array('uid'=>intval($_POST['uid'])),array('post'=>$post));
        if ($return['errcode'] == 9) {
            $this->render_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
}

?>