<?php

/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class users_trust_controller extends adminCommon
{
    function set_search()
    {
        $search[] = array('param' => 'status', 'name' => '审核状态', 'value' => array('1' => '已接受', '3' => '未审核', '2' => '未接受'));

        $lo_time = array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月');

        $search[] = array('param' => 'time', 'name' => '发布时间', 'value' => $lo_time);

        return $search;
    }

    /**
     * 会员-个人-委托简历管理: 委托列表
     */
    function index_action()
    {
        if ($_POST['keyword']) {
            $keytype = intval($_POST['type']);
            $keyword = trim($_POST['keyword']);

            if ($keytype == 1) {
                $resumeM = $this->MODEL('resume');
                $rwhere['name'] = array('like', $keyword);
                $rlist = $resumeM->getResumeList($rwhere, array('field' => '`uid`'));
                if (is_array($rlist)) {
                    foreach ($rlist as $v) {
                        $muids[] = $v['uid'];
                    }
                }

                $where['uid'] = array('in', pylode(',', $muids));
            } elseif ($keytype == 2) {
                $resumeM = $this->MODEL('resume');

                $expect = $resumeM->getExpect(array('name' => array('like', $keyword)), array('field' => 'uid'));

                if ($expect) {
                    foreach ($expect as $v) {
                        $uids[] = $v['uid'];
                    }
                    $where['uid'] = array('in', pylode(',', $uids));
                }
            }
        }
        if ($_POST['status']) {
            $status = intval($_POST['status']);
            $where['status'] = $status == 3 ? 0 : $status;
        }
        if ($_POST['time']) {
            if ($_POST['time'] == '1') {
                $where['add_time'] = array('>', strtotime('today'));
            } else {
                $where['add_time'] = array('>', strtotime('-' . intval($_POST['time']) . ' day'));
            }
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('user_entrust', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        $list = array();
        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = 'uid';
            }
            $where['limit'] = $pages['limit'];

            $userEntrustM = $this->MODEL('userEntrust');

            $list = $userEntrustM->getList($where, array('utype' => 'admin'));
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }
    public function  getSearchData_action(){
        $search_list = $this->set_search();
        $this->render_json(0,'',compact('search_list'));
    }
    /**
     * 会员-个人-委托简历管理: 审核
     */
    function status_action()
    {
        if (empty($_POST['id']) || empty($_POST['status'])) {
            $this->render_json(1, '参数错误');
        }

        $userEntrustM = $this->MODEL('userEntrust');

        $return = $userEntrustM->statusEntrust(array('id' => intval($_POST['id'])), array('post' => array('status' => intval($_POST['status']))));

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员-个人-委托简历管理: 推荐列表
     */
    function recom_action()
    {
        $userEntrustM = $this->MODEL('userEntrust');
        $where['state'] = 1;
        $where['status'] = 0;
        $where['r_status'] = array('<>', 2);

        if ($_POST['keyword']) {
            $keytype = intval($_POST['type']);
            $keyword = trim($_POST['keyword']);

            if ($keytype == 1) {
                $where['com_name'] = array('like', $keyword);
            } elseif ($keytype == 2) {
                $where['name'] = array('like', $keyword);
            }
        }

        $rwhere = $userEntrustM->getRecordWhere(intval($_POST['eid']));

        $where = array_merge($where, $rwhere);

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company_job', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = 'lastupdate';
            }
            $where['limit'] = $pages['limit'];

            $jobM = $this->MODEL('job');

            $List = $jobM->getList($where);

            foreach ($List['list'] as $key => $val) {
                $List['list'][$key]['job_comapply'] = Url('job', array('c' => 'comapply', 'id' => $val['id']));
            }
        }
        $list = !empty($List['list']) ? $List['list'] : array();

        $this->render_json(0, 'ok', compact('search_list', 'list', 'total', 'page_sizes', 'limit', 'page'));
    }

    /**
     * 会员-个人-委托简历管理: 推荐简历
     */
    function directrecom_action()
    {
        if (empty($_POST['eid']) || empty($_POST['jobid']) || empty($_POST['comid'])) {
            $this->render_json(1, '参数错误');
        }

        $userEntrustM = $this->MODEL('userEntrust');

        $where = array(
            'eid' => intval($_POST['eid']),
            'jobid' => intval($_POST['jobid']),
            'comid' => intval($_POST['comid'])
        );

        $return = $userEntrustM->sendRecord($where);

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员-个人-委托简历管理: 删除委托
     */
    function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $userEntrustM = $this->MODEL('userEntrust');

        $return = $userEntrustM->delInfo($_POST['del'], array('utype' => 'admin'));

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员-个人-委托简历管理: 委托列表数据查询
     */
    function trustNum_action()
    {
        $MsgNum = $this->MODEL('msgNum');

        echo $MsgNum->trustNum();
    }
}

?>