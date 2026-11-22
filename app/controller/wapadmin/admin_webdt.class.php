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

class admin_webdt_controller extends adminCommon
{
    function index_action()
    {
        if (!empty($_POST)) {

            $UserinfoM  =   $this->MODEL('userinfo');
            $TongjiM    =   $this->MODEL('tongji');
            $JobM       =   $this->MODEL('job');
            $ResumeM    =   $this->MODEL('resume');

            //查询数据
            $where      =   array();
            $page       =   $_POST['page'];
            $limit      =   $_POST['limit'];

            $tablename  =   '';
            $type       =   $_POST['type'];

            if ($type == 'downresumedt') {

                $where['orderby'] = array('downtime,desc');
                $tablename = 'down_resume';
            } elseif ($type == 'useridmsgdt') {

                $where['orderby'] = array('datetime,desc');
                $tablename = 'userid_msg';
            } elseif ($type == 'useridjobdt') {

                $where['orderby'] = array('datetime,desc');
                $tablename = 'userid_job';
            } elseif ($type == 'lookjobdt') {

                $where['orderby'] = array('datetime,desc');
                $tablename = 'look_job';
            } elseif ($type == 'lookresumedt') {

                $where['orderby'] = array('datetime,desc');
                $tablename = 'look_resume';
            } elseif ($type == 'favjobdt') {

                $where['orderby'] = array('datetime,desc');
                $tablename = 'fav_job';
            } elseif ($type == 'companylogdt') {

                $where['orderby'] = array('ctime,desc');
                $tablename = 'member_log';
                $where['usertype'] = 2;
            } elseif ($type == 'resumelogdt') {

                $where['orderby'] = array('ctime,desc');
                $tablename = 'member_log';
                $where['usertype'] = 1;
            }

            if ($page) {

                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =   array($pagenav, $limit);
            } else {

                $where['limit'] =   $limit;
            }
            $List   =   $TongjiM->tjtotal($tablename, $where);
            if (is_array($List)) {
                foreach ($List as $v) {
                    if (!empty($v['uid'])) {
                        $uid[] = $v['uid'];
                    }
                    if (!empty($v['comid'])) {
                        if ($v['usertype'] == 2) {
                            $comid[] = $v['comid'];
                        } else {
                            $ltid[] = $v['comid'];
                        }
                    }
                    if (!empty($v['com_id'])) {
                        $com_id[] = $v['com_id'];
                    }
                    if (!empty($v['jobid'])) {
                        $jobid[] = $v['jobid'];
                    }
                }
                $uidwhere['uid'] = array('in', pylode(',', array_unique($uid)));
                if ($type == 'companylogdt') {

                    $member2 = $UserinfoM->getUserList($uidwhere);
                } else {

                    $member2 = $UserinfoM->getUserInfoList($uidwhere, array('usertype' => 1, 'field' => '`uid`,`name`'));
                }
                if (!empty($comid)) {

                    $comidwhere['uid'] = array('in', pylode(',', array_unique($comid)));
                    $member = $UserinfoM->getUserInfoList($comidwhere, array('usertype' => 2, 'field' => '`uid`,`name`'));
                } else {

                    $com_idwhere['uid'] = array('in', pylode(',', array_unique($com_id)));
                    $member3 = $UserinfoM->getUserList($com_idwhere);
                }
                if (!empty($ltid)) {

                    $ltwhere = array('uid' => array('in', pylode(',', array_unique($ltid))));
                    $memberLt = $UserinfoM->getUserInfoList($ltwhere, array('usertype' => 3, 'field' => '`uid`,`realname`'));
                }

                $resumewhere['uid'] = array('in', pylode(',', array_unique($uid)));
                $resume = $ResumeM->getResumeList($resumewhere);

                $jobwhere['id'] = array('in', pylode(',', array_unique($jobid)));
                $jobA = $JobM->getList($jobwhere);
                $job = $jobA['list'];

                foreach ($List as $k => $v) {

                    foreach ($resume as $val) {
                        if ($v['uid'] == $val['uid']) {
                            $List[$k]['username'] = $val['name'];
                        }
                    }
                    foreach ($job as $val) {
                        if ($v['jobid'] == $val['id']) {
                            $List[$k]['jobname'] = $val['name'];
                        }
                    }
                    foreach ($member as $val) {
                        if ($v['comid'] == $val['uid']) {
                            $List[$k]['comusername'] = $val['name'];
                        }
                    }
                    foreach ($member2 as $val) {
                        if ($v['uid'] == $val['uid']) {
                            $List[$k]['username'] = $val['name'];
                        }
                    }
                    foreach ($member3 as $val) {
                        if ($v['com_id'] == $val['uid']) {
                            $List[$k]['comusername'] = $val['name'];
                        }
                    }
                    if (!empty($memberLt)) {
                        foreach ($memberLt as $val) {
                            if ($v['comid'] == $val['uid']) {
                                $List[$k]['comusername'] = $val['realname'];
                            }
                        }
                    }
                    $time   =   time() - $v['downtime'];

                    if ($time > 86400 && $time < 604800) {

                        $List[$k]['time'] = ceil($time / 86400) . "天前";
                    } elseif ($time > 3600 && $time < 86400) {

                        $List[$k]['time'] = ceil($time / 3600) . "小时前";
                    } elseif ($time > 60 && $time < 3600) {

                        $List[$k]['time'] = ceil($time / 60) . "分钟前";
                    } elseif ($time < 60) {

                        $List[$k]['time'] = "刚刚";
                    } else {

                        $List[$k]['time'] = date("Y-m-d", $v['downtime']);
                    }

                    $times  =   time() - $v['datetime'];

                    if ($times > 86400 && $times < 604800) {

                        $List[$k]['times'] = ceil($times / 86400) . "天前";
                    } elseif ($times > 3600 && $times < 86400) {

                        $List[$k]['times'] = ceil($times / 3600) . "小时前";
                    } elseif ($times > 60 && $times < 3600) {

                        $List[$k]['times'] = ceil($times / 60) . "分钟前";
                    } elseif ($times < 60) {

                        $List[$k]['times'] = "刚刚";
                    } else {

                        $List[$k]['times'] = date("Y-m-d", $v['datetime']);
                    }

                    $timess =   time() - $v['order_time'];

                    if ($timess > 86400 && $timess < 604800) {

                        $List[$k]['timess'] = ceil($timess / 86400) . "天前";
                    } elseif ($timess > 3600 && $timess < 86400) {

                        $List[$k]['timess'] = ceil($timess / 3600) . "小时前";
                    } elseif ($timess > 60 && $timess < 3600) {

                        $List[$k]['timess'] = ceil($timess / 60) . "分钟前";
                    } elseif ($timess < 60) {

                        $List[$k]['timess'] = "刚刚";
                    } else {

                        $List[$k]['timess'] = date("Y-m-d", $v['order_time']);
                    }
                    if ($v['ctime']) {

                        $List[$k]['ctime_n']    =   date('Y-m-d H:i:s', $v['ctime']);
                    }
                }
            }
            $data['list'] = $List;
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $this->yuntpl(array('wapadmin/admin_webdt'));
    }
}

?>