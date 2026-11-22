<?php

/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/

class yingxiao_friendhelp_controller extends adminCommon
{
    // 参数配置
    function index_action()
    {
        $config = $this->config;
        $config = array(
            'sy_help_open' => $config['sy_help_open'],
            'sy_help_time' => $config['sy_help_time'],
            'sy_help_breakjobnum' => $config['sy_help_breakjobnum'],
            'sy_help_breakjobnum_zl' => $config['sy_help_breakjobnum_zl'],
            'sy_help_jobnum' => $config['sy_help_jobnum'],
            'sy_help_jobnum_zl' => $config['sy_help_jobnum_zl'],
            'sy_help_urgentnum' => $config['sy_help_urgentnum'],
            'sy_help_urgentnum_zl' => $config['sy_help_urgentnum_zl'],
            'sy_help_recnum' => $config['sy_help_recnum'],
            'sy_help_recnum_zl' => $config['sy_help_recnum_zl'],
            'sy_help_topnum' => $config['sy_help_topnum'],
            'sy_help_topnum_zl' => $config['sy_help_topnum_zl'],
            'sy_help_inviteresume' => $config['sy_help_inviteresume'],
            'sy_help_inviteresume_zl' => $config['sy_help_inviteresume_zl'],
            'sy_help_downresume' => $config['sy_help_downresume'],
            'sy_help_downresume_zl' => $config['sy_help_downresume_zl']
        );

        $this->render_json(0, 'ok', compact('config'));
    }

    // 参数配置-保存
    function save_action()
    {
        if (empty($_POST)) {
            $this->render_json(1, '参数错误');
        }

        $configM = $this->MODEL('config');

        $_POST = $this->post_trim($_POST);

        unset($_POST['pytoken']);

        $configM->setConfig($_POST);

        $this->web_config();

        $this->admin_json(0, '助力设置成功');
    }

    //设置高级搜索功能
    function set_search()
    {
        $search_list[] = array("param" => "help_state", "name" => '助力状态', "value" => array("1" => "助力中", "2" => "已结束"));

        $lo_time = array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月');
        $search_list[] = array("param" => "time", "name" => '助力发布', "value" => $lo_time);

        return $search_list;
    }

    // 好友助力记录
    function list_action()
    {
        $helpM = $this->MODEL('friendhelp');

        $where = array();
        $keywordStr = trim($_POST['keyword']);

        if ($_POST['help_state']) {
            if ($_POST['help_state'] == 2) {
                $where['etime'][] = array('<', time());
            } else {
                $where['etime'][] = array('>=', time());
            }
        }
        if ($keywordStr) {
            if ($_POST['typeca'] == 2) {
                $where['comid'] = array('=', $keywordStr);
            } else {
                $companyM = $this->MODEL('company');
                $companyList = $companyM->getList(array('name' => array('like', $keywordStr)), array('field' => 'uid'));

                $minfo = $companyList['list'];

                $uids = array();

                if ($minfo) {
                    foreach ($minfo as $mv) {
                        $uids[] = $mv['uid'];
                    }
                }

                $where['comid'] = array('in', pylode(',', $uids));
            }
        }
        if ($_POST['time'] || $_POST['time_start'] != "" || $_POST['time_end'] != "") {
            $where['PHPYUNBTWSTART_B'] = 'AND';
            if ($_POST['time']) {
                if ($_POST['time'] == 1) {
                    $where['stime'][] = array('>=', strtotime(date("Y-m-d 00:00:00")));
                } else {
                    $where['stime'][] = array('>=', strtotime('-' . intval($_POST['time']) . ' day'));
                }
            }
            if ($_POST['time_start'] != "") {
                $where['stime'][] = array('>=', strtotime($_POST['time_start'] . " 00:00:00"));
            }
            if ($_POST['time_end'] != "") {
                $where['stime'][] = array('<', strtotime($_POST['time_end'] . " 23:59:59"));
            }
            $where['PHPYUNBTWEND_B'] = '';
        }
        if ($_POST['hashelp'] == 1) {
            $where['zlnum'] = array('>', 0);
        }

        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('friend_help', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list = array();
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('id,desc');
            }
            $where['limit'] = $pages['limit'];

            $rows = $helpM->getList($where);
            $list = $rows['list'] ? $rows['list'] : [];
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    function list_base_data_action()
    {
        $search_list = $this->set_search();
        $this->render_json(0, 'ok', compact('search_list'));
    }

    // 获取助力记录总数
    function helpSum_action()
    {
        $MsgNum = $this->MODEL('msgNum');
        $this->render_json(0, 'ok', $MsgNum->helpSum());
    }

    // 查看助力日志
    function loglist_action()
    {
        if (empty($_POST['id'])) {
            $this->render_json(0, '参数错误');
        }

        $helpM = $this->MODEL('friendhelp');
        $list = $helpM->getLogList(array('pid' => intval($_POST['id'])));

        $this->render_json(0, 'ok', compact('list'));
    }

    // 助力记录删除
    function del_action()
    {
        $helpM = $this->Model('friendhelp');

        $delID = $_POST['id'] ? intval($_POST['id']) : $_POST['del'];

        $return = $helpM->delHelp($delID);

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
}

?>