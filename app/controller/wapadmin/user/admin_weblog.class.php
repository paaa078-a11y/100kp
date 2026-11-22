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
class admin_weblog_controller extends adminCommon
{
    function index_action()
    {
        if ($_POST) {

            $webLogM    =   $this->MODEL('weblog');
            $logCount   =   $webLogM->logCount(array('uid' => $_POST['uid'], 'time' => $_POST['time'], 'times' => $_POST['times'], 'usertype' => $_POST['usertype']));
            $data['logcount']   =   $logCount;
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $this->yuntpl(array('wapadmin/admin_weblog'));
    }

    function loglist_action()
    {
        if ($_POST['uid']) {

            $webLogM    =   $this->MODEL('weblog');

            $where['uid']   =   intval($_POST['uid']);

            $page       =   $_POST['page'];
            $limit      =   $_POST['limit'];

            //默认取当天数据
            $today      =   strtotime(date('Y-m-d'));

            if ($_POST['time'] == 1) {

                $where['time']  =   array('>=', $today, 'AND');
            } else if ($_POST['time'] == 2) {

                $where['PHPYUNBTWSTART_A'] = '';
                $where['time'][] = array('>=', strtotime('-1 day', $today), 'AND');
                $where['time'][] = array('<', $today, 'AND');
                $where['PHPYUNBTWEND_A'] = '';
            } else if ($_POST['time'] == 3) {

                $where['time'] = array('>=', strtotime('-3 day', $today), 'AND');
            } else if ($_POST['time'] == 4) {

                $where['time'] = array('>=', strtotime('-7 day', $today), 'AND');
            }

            if ($_POST['times']) {

                $times = @explode('~', $_POST['times']);
                $where['PHPYUNBTWSTART_B'] = '';
                $where['time'][] = array('>=', strtotime($times[0] . "00:00:00"), 'AND');
                $where['time'][] = array('<=', strtotime($times[1] . "23:59:59"), 'AND');
                $where['PHPYUNBTWEND_B'] = '';
            }

            if ($page) {

                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =   array($pagenav, $limit);
            } else {

                $where['limit'] =   $limit;
            }
            $where['orderby']   =   'id';

            $list           =   $webLogM->getWebLogList($where);
            $data['list']   =   count($list) ? $list : [];
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
    }
}

?>