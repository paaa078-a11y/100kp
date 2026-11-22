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

class free_controller extends common
{

    function index_action()
    {

        if (!$this->uid) {
            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $gqM = $this->MODEL('gqdemand');
        $gqInfo = $gqM->getGqInfo(array('uid' => $this->uid), array('field' => '`uid`'));

        if (empty($gqInfo)) {

            $data = array('uid' => $this->uid, 'name' => $this->username, 'state' => 2);
            $gqM->addGqInfo($data);
        }
        $backurl = Url('wap', array(), 'member');
        $this->yunset('backurl', $backurl);
        $this->yunset('headertitle', '供求任务');
        $this->yuntpl(array('wap/gq_index'));
    }

    function info_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $this->yunset($this->MODEL('cache')->GetCache(array('user', 'city')));
        $this->yunset('headertitle', "技能信息");

        $userInfoM = $this->MODEL('userinfo');


        $gqM = $this->MODEL('gqdemand');
        $gqInfo = $gqM->getGqInfo(array('uid' => $this->uid));

        if (empty($gqInfo)) {

            $data = array('uid' => $this->uid, 'state' => 2);
            $gqM->addGqInfo($data);

            $gqInfo = $this->getGqInfo(array('uid' => $this->uid));
        }

        if ($_POST['submit']) {

            $member = $userInfoM->getInfo(array('uid' => $this->uid));

            if ($member['status'] == 1) {

                $status = $this->config['gq_task_status'];
            } else {

                $status = 0;
            }

            $infoData = array(
                'uid' => $this->uid,
                'name' => $_POST['name'],
                'sex' => $_POST['sex'],
                'provinceid' => $_POST['provinceid'],
                'cityid' => $_POST['cityid'],
                'three_cityid' => $_POST['three_cityid'],
                'services' => str_replace(array('&amp;', 'background-color:#ffffff', 'background-color:#fff', 'white-space:nowrap;'), array('&', 'background-color:', 'background-color:', 'white-space:'), $_POST['services']),
                'salary' => $_POST['salary'],
                'content' => str_replace(array('&amp;', 'background-color:#ffffff', 'background-color:#fff', 'white-space:nowrap;'), array('&', 'background-color:', 'background-color:', 'white-space:'), $_POST['content']),
                'moblie' => $_POST['moblie'],
                'speciality' => $_POST['speciality'],
                'status' => $status,
                'state' => 1,
                'r_status' => $member['status'],
                'lastupdate' => time()
            );

            $return = $gqM->upaddGqinfo(array('uid' => $this->uid), $infoData);

            if ($return['errcode'] == 9) {

                $upKeyArr = array('name', 'sex', 'provinceid', 'cityid', 'three_cityiid', 'salary', 'moblie', 'speciality', 'services', 'content');
                foreach ($gqInfo as $k => $v) {

                    if (in_array($k, $upKeyArr)) {
                        $value = $v ? $v : '';

                        $msg = "修改基本信息";
                        if ($k == 'name' && $value != $infoData[$k]) {

                            $msg .= "；名称：" . $infoData[$k] . " -> " . $value;
                        } else if ($k == 'sex' && $value != $infoData[$k]) {

                            $msg .= "；性别：" . $user_sex[$infoData[$k]] . " -> " . $user_sex[$value];
                        } else if ($k == 'proviceid' && $value != $infoData[$k]) {

                            $msg .= "；一级区域：" . $city_name[$infoData[$k]] . " -> " . $city_name[$value];
                        } else if ($k == 'cityid' && $value != $infoData[$k]) {

                            $msg .= "；二级区域：" . $city_name[$infoData[$k]] . " -> " . $city_name[$value];
                        } else if ($k == 'proviceid' && $value != $infoData[$k]) {

                            $msg .= "；三级级区域：" . $city_name[$infoData[$k]] . " -> " . $city_name[$value];
                        } else if ($k == 'salary' && $value != $infoData[$k]) {

                            $msg .= "；薪资：" . $infoData[$k] . " -> " . $value;
                        } else if ($k == 'moblie' && $value != $infoData[$k]) {

                            $msg .= "；联系电话：" . $infoData[$k] . " -> " . $value;
                        } else if ($k == 'speciality' && $value != $infoData[$k]) {

                            $msg .= "；技能特长：" . $infoData[$k] . " -> " . $value;
                        } else if ($k == 'services' && $value != $infoData[$k]) {

                            $msg .= "；服务信息：" . $infoData[$k] . " -> " . $value;
                        } else if ($k == 'speciality' && $value != $infoData[$k]) {

                            $msg .= "；自我介绍：" . $infoData[$k] . " -> " . $value;
                        }
                    }
                }
                $logM       =   $this->MODEL('log');
                $logContent =   '完善基本信息';
                $logDetail  =   !empty($msg) ? $msg : '修改基本信息';
                $logM->addMemberLog($this->uid, 5, $logContent, 7, 2, $logDetail);
                $data = array('msg' => $return['msg'], 'url' => 'index.php?c=free');
            } else {

                $data = array('msg' => $return['msg']);
            }
            echo json_encode($data);
            die;
        }
        $this->yunset('gqinfo', $gqInfo);
        $this->yuntpl(array('wap/gq_info'));
    }

    function freemoblie_action()
    {

        $gqdM = $this->MODEL('gqdemand');

        $where['moblie'] = $_POST['moblie'];
        $where['uid'] = array('<>', $this->uid);

        $info = $gqdM->getGqInfo($where, array('field' => '`uid`'));

        if ($info['uid']) {
            echo 1;die;
        } else {
            echo 2;die;
        }
    }

    /**
     * 查看发布任务
     */
    function tasklist_action()
    {

        $this->yunset('headertitle', "我发布的任务");

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $gqM = $this->MODEL('gqdemand');

        $where['uid'] = $this->uid;

        $rows = $gqM->getGqtaskList($where, array('type' => 1));

        if (is_array($rows) && $rows) {

            $zp = $sh = $gq = $xj = 0;
            $time = time();
            foreach ($rows as $value) {
                if ($value['status'] == 1 && date('Y-m-d', $value['etime']) >= date('Y-m-d', $time) && $value['state'] == 1) {
                    $zp += 1;
                }
                if ($value['status'] != 1 && date('Y-m-d', $value['etime']) >= date('Y-m-d', $time) && $value['state'] == 1) {
                    $sh += 1;
                }
                if (date('Y-m-d', $value['etime']) < date('Y-m-d', $time) && $value['state'] == 1) {
                    $gq += 1;
                }
                if ($value['status'] == 1 && date('Y-m-d', $value['etime']) >= date('Y-m-d', $time) && $value['state'] == 2) {
                    $xj += 1;
                }
            }
        }

        $this->yunset(array('zp' => $zp, 'sh' => $sh, 'gq' => $gq, 'xj' => $xj));

        $backurl = Url('wap', array('c' => 'free'));
        $this->yunset('backurl', $backurl);
        $this->yunset('time', $time);
        $this->yunset('rows', $rows);
        $this->yuntpl(array('wap/tasklist'));
    }

    /**
     * 发布项目任务和修改任务
     */
    function addtask_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $logM = $this->MODEL('log');
        $userinfoM = $this->MODEL('userinfo');
        $gqM = $this->MODEL('gqdemand');

        if ((int)$this->config['gq_pay_price'] == 0) {

            $start_time = strtotime(date('Y-m-d 00:00:00'));
            $end_time = strtotime(date('Y-m-d 23:59:59'));//开始时间
            $taskwhere['ctime'][] = array('>', $start_time);
            $taskwhere['ctime'][] = array('<', $end_time);
            $taskwhere['uid'] = $this->uid;
            $taskwhere['pay'] = 3;

            if ((int)$this->config['gq_number'] > 0) {

                $mess = $gqM->getTaskNum($taskwhere);
                if ($mess >= $this->config['gq_number']) {

                    $data['msg'] = "一天内只能发布" . $this->config['gq_number'] . "条项目任务！！";
                    $data['url'] = Url('wap', array("c" => 'tasklist'));
                }
            }
        }
        $this->yunset('layer', $data);

        if ($_GET['id']) {

            $rows = $gqM->getGqtaskInfo(array('id' => $_GET['id'], 'uid' => $this->uid), array('type' => 1));
            $taskInfo = $rows;
            if ($rows['content']) {

                $rows['content_t'] = strip_tags($rows['content']);
            }
            $this->yunset('rows', $rows);
        } else {

            if (floatval($this->config['gq_pay_price']) > 0) {

                $orderM = $this->MODEL('companyorder');
                $orderNum = $orderM->getCompanyOrderNum(array('order_state' => 1, 'type' => 29, 'uid' => $this->uid));
                $this->yunset('num', $orderNum);
            }
        }

        $pay = $this->config['gq_pay_price'] > 0 ? 1 : 3;
        $time = date('Y-m-d', time());
        $gqinfo = $gqM->getGqInfo(array('uid' => $this->uid));
        $this->yunset(array('time' => $time, 'pay' => $pay, 'gqinfo' => $gqinfo));

        if ($_POST['submit']) {

            $member = $userinfoM->getInfo(array('uid' => $this->uid));
            $status = $member['status'] == '1' ? $this->config['gq_task_status'] : '0';
            $content = str_replace(array("&amp;", "background-color:#ffffff", "background-color:#fff", "white-space:nowrap;"), array("&", 'background-color:', 'background-color:', 'white-space:'), $_POST['content']);

            $data = array(
                'uid' => $this->uid,
                'name' => $_POST['name'],
                'salary' => $_POST['salary'],
                'edate' => $_POST['edate'],
                'etime' => strtotime($_POST['etime']),
                'content' => $content,
                'link_man' => $_POST['link_man'],
                'link_moblie' => $_POST['link_moblie'],
                'status' => $status,
                'ctime' => isset($rows['ctime']) ? $rows['ctime'] : time(),
                'lastupdate' => time(),
                'state' => $_POST['state'],
                'r_status' => $member['status'],
                'pay' => $_POST['pay']
            );

            $where = array();
            if ($_POST['id']) {

                $where['id'] = $_POST['id'];
            }

            $return = $gqM->upaddGqtask($where, $data, '');

            if ($return['errcode'] == '9') {

                if ($_POST['id']) {

                    $upKeyArr = array('name', 'salary', 'edate', 'etime', 'content', 'link_man', 'link_moblie');

                    foreach ($taskInfo as $k => $v) {

                        if (in_array($k, $upKeyArr)) {
                            $value = $v ? $v : '';

                            $msg = "修改任务信息";
                            if ($k == 'name' && $value != $data[$k]) {

                                $msg .= "；名称：" . $data[$k] . " -> " . $value;
                            } elseif ($k == 'salary' && $value != $data[$k]) {

                                $msg .= "；预算：" . $data[$k] . " -> " . $value . '元';
                            } elseif ($k == 'edate' && $value != $data[$k]) {

                                $msg .= "；工期：" . $data[$k] . " -> " . $value . '天';
                            } elseif ($k == 'etime' && $value != $data[$k]) {

                                $msg .= "；接单截止：" . date('Y-m-d H:i', $data[$k]) . " -> " . date('Y-m-d H:i', $value);
                            } elseif ($k == 'content' && $value != $data[$k]) {

                                $msg .= "；项目需求：" . $data[$k] . " -> " . $value;
                            } elseif ($k == 'link_man' && $value != $data[$k]) {

                                $msg .= "；联系人：" . $data[$k] . " -> " . $value;
                            } elseif ($k == 'link_moblie' && $value != $data[$k]) {

                                $msg .= "；联系电话：" . $data[$k] . " -> " . $value;
                            }
                        }
                    }

                    $logContent =   '项目任务：更新任务';
                    $logDetail  =   !empty($msg) ? $msg : '更新任务信息';
                    $logM->addMemberLog($this->uid, 5, $logContent, 29, 2, $logDetail);
                } else {

                    $logDetail  =   '发布项目任务：《'.$data['name'].'》';
                    $logContent =   '项目任务：发布任务';
                    $logM->addMemberLog($this->uid, 5, $logContent, 29, 1, $logDetail);
                }
            }

            echo json_encode($return);
            die;
        }

        $this->yunset('headertitle', "发布任务");
        $this->yuntpl(array('wap/addtask'));
    }


    /**
     * 删除项目任务
     */
    function deltask_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $gqM = $this->MODEL('gqdemand');
        $task = $this->obj->select_once('gq_task', array('id' => $_GET['id']), 'name');
        $return = $gqM->deltask($_GET['id'], array('uid' => $this->uid));
        if ($return['errcode'] == 9) {

            $logM       =   $this->MODEL('log');
            $logContent =   '项目任务：删除任务';
            $logDetail  =   '删除项目任务（ID：'.$_GET['id'].'，任务名称：'.$task['name'].'）';
            $logM->addMemberLog($this->uid, 5, $logContent, 29, 3, $logDetail);
            echo 1;
            die;
        } else {
            echo 2;
            die;
        }
    }

    function pay_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }


        $gqM = $this->MODEL('gqdemand');

        $row = $gqM->getGqtaskInfo(array('id' => (int)$_GET['id']));

        if (!$row) {
            $this->ACT_msg_wap(Url('wap', array('c' => 'free', 'a' => 'tasklist')), '供求任务信息不存在！', 1, 3);
        }

        if ($this->config['wxpay'] == '1') {
            $paytype['wxpay'] = '1';
        }
        if ($this->config['alipay'] == '1' && $this->config['alipaytype'] == '1') {
            $paytype['alipay'] = '1';
        }

        if ($paytype) {
            $this->yunset("paytype", $paytype);
        }

        $this->get_moblie();
        $this->yunset("headertitle", "供求任务");
        $this->yuntpl(array('wap/gq_pay'));
    }

    function dingdan_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        if ($_POST) {

            $gqM = $this->MODEL('gqdemand');

            $data = array(
                'uid' => $this->uid,
                'usertype' => 5,
                'id' => $_POST['id'],
                'did' => $this->userdid,
                'pay_type' => $_POST['paytype'] == 'wxpay' ? 'wxh5' : $_POST['paytype']
            );
            if (empty($_POST['type'])) {
                $return = $gqM->payTask($data);
            } else {
                $return = $gqM->payTaskRefresh($data);
            }
            if ($return['id']) {

                if ($_POST['paytype'] == 'alipay') {

                    $dingdan = $return['orderid'];

                    if (empty($_POST['type'])) {
                        $price = $this->config['gq_pay_price'];
                    } else {
                        $price = $this->config['gq_refrsh_pay'];
                    }

                    $url = $this->config['sy_weburl'] . '/api/wapalipay/alipayto.php?dingdan=' . $dingdan . '&dingdanname=' . $dingdan . '&alimoney=' . $price;

                } else if ($_POST['paytype'] == 'wxpay') {

                    $url = Url('wap', array('c' => 'free', 'a' => 'wxpay', 'id' => $return['id']));

                }

                header('Location: ' . $url);
                exit();
            } else {
                $this->ACT_msg_wap(Url('wap', array('c' => 'free', 'a' => 'tasklist')), '提交失败！！', 1, 3);
            }

        } else if ($_GET['id']) {

            $companyorderM = $this->MODEL('companyorder');

            $order = $companyorderM->getInfo(array('id' => (int)$_GET['id']));

        }
    }

    function wxpay_action()
    {

        if (!$this->uid) {
            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        if ($_GET['id']) {

            $companyorderM = $this->MODEL('companyorder');

            $data['orderId'] = (int)$_GET['id'];

            if (isset($_GET['paytype'])) {

                $data['paytype'] = $_GET['paytype'];
            }

            $return = $companyorderM->payComOrderByWXWAP($data);

            if ($return['header']) {

                header($return['header']);

                exit();
            } elseif ($return['msg']) {
                $this->ACT_msg_wap($return['url'], $return['msg'], 1, 3);

            } else {
                $this->yunset('jsApiParameters', $return['jsApiParameters']);
            }
            $this->yunset('id', (int)$_GET['id']);

            $this->yunset('headertitle', '微信支付');
        } else {
            $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '参数不正确，请正确填写！', 1, 3);
        }

        $this->yuntpl(array('wap/gq_wxpay'));
    }

    function payment_action()
    {
        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }
        if ($this->config['wxpay'] == '1') {

            $paytype['wxpay'] = '1';

        }
        if ($this->config['alipay'] == '1' && $this->config['alipaytype'] == '1') {

            $paytype['alipay'] = '1';

        }
        if ($paytype) {

            if ($_GET['id']) {//订单

                $orderM = $this->MODEL('companyorder');

                $order = $orderM->getInfo(array('uid' => $this->uid, 'id' => (int)$_GET['id']), array('bank' => 1));
                if (empty($order)) {
                    $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '订单不存在！', 1, 3);
                } elseif ($order['order_state'] != '1') {
                    header("Location:" . Url('wap', array('c' => 'free', 'a' => 'paylog')));
                } else {

                    $this->yunset("order", $order);
                }
            }
            $this->yunset("paytype", $paytype);

            $this->yunset("js_def", 4);

        } else {
            $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '暂未开通手机支付！', 1, 3);
        }
        $this->yunset('headertitle', "订单确认");

        $this->yuntpl(array('wap/gq_payment'));

    }

    function paylog_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $where['uid'] = $this->uid;
        $where['usertype'] = 5;

        $where['orderby'] = 'order_time,desc';

        $urlarr = array("c" => "free", "a" => "paylog", "page" => "{{page}}");

        $pageurl = Url('wap', $urlarr);

        $pageM = $this->MODEL('page');

        $pages = $pageM->pageList('company_order', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['limit'] = $pages['limit'];

            $companyorderM = $this->MODEL('companyorder');

            $rows = $companyorderM->getList($where);

            $this->yunset("rows", $rows);
        }

        include(CONFIG_PATH . "db.data.php");

        $this->yunset("arr_data", $arr_data);

        $this->yunset('backurl', Url('wap', array('c' => 'free', 'a' => 'tasklist')));

        $this->yunset("headertitle", "订单管理");

        $this->yuntpl(array('wap/gq_paylog'));
    }

    function delpaylog_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $orderM = $this->MODEL('companyorder');

        $return = $orderM->del((int)$_GET['id'], array('uid' => $this->uid));

        if ($return['errcode'] == 9) {

            echo 1;
            die;
        } else {
            echo 2;
            die;
        }

    }

    /**
     * 上架下架管理
     */
    function freeset_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $gqM = $this->MODEL('gqdemand');
        $data = array('state' => intval($_GET['state']));
        $nid = $gqM->upGqtaskStatus(array('id' => $_GET['id']), $data);
        if ($nid) {

            $LogM       =   $this->MODEL('log');
            $task       =   $this->obj->select_once('gq_task', array('id' => $_GET['id']), '`name`');
            $logContent =   '项目任务：更新任务状态';
            $logDetail  =   '';
            if ($data['state'] == 1) {

                $logDetail  .=  '任务《' . $task['name'] . '》状态调整：下架 → 上架';
            } elseif ($_GET['state'] == 2) {

                $logDetail  .=  '任务《' . $task['name'] . '》状态调整：上架 → 下架';
            }
            $LogM->addMemberLog($this->uid, 5, $logContent, 29, 2, $logDetail);
            echo 1;
            die;
        } else {
            echo 2;
            die;
        }
    }

    /**
     * 项目任务刷新功能
     */
    public function refrshtask_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $gqM = $this->MODEL('gqdemand');
        if ((int)$this->config['gq_refrsh_pay'] > 0) {

            $datat['msg'] = "刷新项目任务，需要支付" . $this->config['gq_refrsh_pay'] . "元";
            $datat['url'] = 'index.php?c=free&a=pay&id=' . $_GET['id'] . '&type=refresh';
        } else {

            $data   =   array('lastupdate' => time());
            $nid    =   $gqM->upGqtaskStatus(array('id' => $_GET['id']), $data);

            if ($nid) {

                $LogM       =   $this->MODEL('log');
                $logContent =   '项目任务：刷新任务';
                $logDetail  =   '刷新供求任务（ID：'.$_GET['id'].'）';
                $LogM->addMemberLog($this->uid, 5, $logContent, 29, 4, $logDetail);

                $datat['msg']   =   "供求项目任务刷新成功";
                $datat['url']   =   'index.php?c=free&a=tasklist';
            } else {

                $datat['msg']   =   "供求项目任务刷新失败";
            }
        }
        echo json_encode($datat);
        die;
    }


    //查看浏览记录
    function browserlist_action()
    {

        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $gqM = $this->MODEL('gqdemand');

        $uid = $this->uid;

        $where['uid'] = $uid;

        $rows = $gqM->gqbrowseList($where);

        $this->yunset('rows', $rows);

        $this->yunset('headertitle', "浏览供求任务");

        $this->yuntpl(array('wap/gq_browserlist'));


    }

    /**
     * 删除浏览任务
     */
    function delbrowse_action()
    {
        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }

        $gqM    =   $this->MODEL('gqdemand');
        $id     =   $_GET['id'];
        $return =   $gqM->delbrower($id, array('uid' => $this->uid));

        if ($return) {

            $logM       =   $this->MODEL('log');
            $logContent =   '浏览记录：删除记录';
            $logDetail  =   '删除项目任务浏览记录（ID：'.$id.'）';
            $logM->addMemberLog($this->uid, 5, $logContent, 26, 3, $logDetail);
            echo 1;die;
        } else {

            echo 2;die;
        }
    }

    function photo_action()
    {
        if (!$this->uid) {

            $this->ACT_msg_wap(Url('wap', array('c' => 'login')), '请先登录!', 2, 5);
        }
        if ($_POST['submit']) {

            $gqM    =   $this->MODEL('gqdemand');

            $return =   $gqM->upPhoto(array('uid' => $this->uid), array('utype' => 'gq', 'base' => $_POST['uimage']));

            if ($return['errcode'] == '9') {

                $logM       =   $this->MODEL('log');
                $logContent =   '图片信息：上传头像';
                $logM->addMemberLog($this->uid, $this->usertype, $logContent, 16, 1);
            }

            echo $return['errcode'];die;
        }
    }
}

?>