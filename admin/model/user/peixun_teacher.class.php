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

class peixun_teacher_controller extends adminCommon
{
    /**
     * 设置高级搜索功能
     * 高级搜索参数
     */
    public function set_search()
    {
        $search_list[] = array(
            'param' => 'status',
            'name' => '审核状态',
            'value' => array(
                1 => '已审核',
                3 => '未审核',
                2 => '未通过',
                4 => '已锁定'
            )
        );
        return $search_list;
    }

    /**
     * 会员- 培训 - 培训师
     * 2019-06-04 hjy
     */
    public function index_action()
    {


        $teWhereData = array();
        $keywordStr = trim($_POST['keyword']);

        if (!empty($keywordStr)) {
            $teWhereData['name'] = array('like', $keywordStr);
        }
        //审核状态条件
        if (!empty($_POST['status'])) {
            if ($_POST['status'] == 3) {
                $teWhereData['status'] = array('=', 0);
                $teWhereData['r_status'] = array('=', 1);
            } else if ($_POST['status'] == 4) {
                $teWhereData['r_status'] = array('>', 1);
            } else {
                $teWhereData['status'] = array('=', $_POST['status']);
                $teWhereData['r_status'] = array('=', 1);
            }
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('px_teacher', $teWhereData, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        //分页数大于0的情况下 执行列表查询
        $list = array();
        $pxM = $this->MODEL('train');

        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if (!empty($_POST['order']) && !empty($_POST['t'])) {
                $teWhereData['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $teWhereData['orderby'] = array('r_status,asc', 'status,asc', 'id,desc');
            }

            $teWhereData['limit'] = $pages['limit'];
            $list = $pxM->getTeaList($teWhereData, array('utype' => 'admin'));
        }
        if (!empty($list)) {
            $pxuid = array();
            foreach ($list as $lv) {
                $pxuid[] = $lv['uid'];
            }
            $bcWhereData = array('uid' => array('in', pylode(',', $pxuid)));
            //补充用户相关的信息
            $pxField = '`uid`, `name` AS train_name';
            $pxList = $pxM->getList($bcWhereData, array('field' => $pxField));
            $pxListIndex = array();
            if (!empty($pxList)) {
                foreach ($pxList as $pxV) {
                    $pxListIndex[$pxV['uid']] = $pxV;
                }
            }
            //补充进相关信息
            foreach ($list as $Lk => $Lv) {
                if (isset($pxListIndex[$Lv['uid']])) {
                    $list[$Lk] = array_merge($list[$Lk], $pxListIndex[$Lv['uid']]);
                }

                $list[$Lk]['show_url'] = Url('train', array('c' => 'teamshow', 'id' => $Lv['uid'], 'nid' => $Lv['id']));
            }
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    // 审核 - 同步审核培训机构
    function teacherstatus_action()
    {
        if (empty($_POST['id']) || empty($_POST['uid']) || empty($_POST['r_status'])) {
            $this->render_json(1, '参数错误');
        }

        $id = intval($_POST['id']);
        $uid = intval($_POST['uid']);
        $status = intval($_POST['r_status']);
        $statusbody = trim($_POST['statusbody']);

        $pxM = $this->MODEL('train');

        $post = array(
            'uid' => $uid,
            'status' => $status == 3 ? 2 : 1,
            'statusbody' => $statusbody
        );

        $return = $pxM->statusTeacher($id, $post);

        if ($return['errcode'] === 0) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员- 培训 - 培训师
     * 审核培训师
     * 2019-06-05 hjy
     */
    public function status_action()
    {
        $postData = $this->post_trim($_POST);
        if (empty($postData['id']) || empty($postData['status'])) {
            $this->render_json(1, '参数错误');
        }

        $postData['id'] = pylode(',', $postData['id']);

        $pxM = $this->MODEL('train');
        $upData = array();
        $upData['status'] = $postData['status'];
        $upData['statusbody'] = $postData['statusbody'];
        $upid = $pxM->upTeaInfo(array('id' => array('in', $postData['id']), 'r_status' => 1), $upData);

        $px_subj = $pxM->getSubList(array('teachid' => array('in', $postData['id'], 'r_status' => 1)), array('field' => '`uid`, `name`'));

        $TeaList = $pxM->getTeaList(array('id' => array('in', $postData['id'], 'r_status' => 1)), array('field' => '`uid`, `name`,`id`'));

        if (!empty($px_subj)) {
            if ($postData['status'] != 1) {
                $pxM->upSubInfo(array('teachid' => array('in', $postData['id'], 'r_status' => 1), array('status' => $postData['status'])));
            }
        }

        if (!empty($TeaList)) {
            /* 消息前缀 */
            $tagName = '培训师';

            $sysmsgM = $this->MODEL('sysmsg');

            foreach ($TeaList as $v) {
                $uids[] = $v['uid'];

                /* 处理审核信息 */
                if ($upData['status'] == 2) {
                    $statusInfo = $tagName . ':' . $v['name'] . '审核未通过';

                    if ($upData['statusbody']) {
                        $statusInfo .= ' , 原因：' . $upData['statusbody'];
                    }

                    $msg[$v['uid']][] = $statusInfo;
                } elseif ($upData['status'] == 1) {
                    $msg[$v['uid']][] = $tagName . ':' . $v['name'] . '已审核通过';
                }
            }

            $sysmsgM->addInfo(array('uid' => $uids, 'usertype' => 4, 'content' => $msg));
        }

        if ($upid) {
            $pxtecahwhere['id'] = array('in', $postData['id']);
            $pxtecahnum = $pxM->getPxTeacherNum($pxtecahwhere);
            if ($pxtecahnum > 1) {
                $pxtecahtwhere['id'] = array('in', $postData['id']);
                $pxtecahtwhere['r_status'] = 1;
                $pxtecahtnum = $pxM->getPxTeacherNum($pxtecahtwhere);
                $pxtecahwwhere['id'] = array('in', $postData['id']);
                $pxtecahwwhere['r_status'] = array('<>', 1);
                $pxtecahwnum = $pxM->getPxTeacherNum($pxtecahwwhere);
                if ($pxtecahwnum > 0) {
                    $msg = '培训讲师批量审核成功' . $pxtecahtnum . '条，失败' . $pxtecahwnum . '条原因:培训账户未审核';
                } else {
                    $msg = '培训讲师批量审核成功(ID:' . $postData['id'] . ')';
                }
                $this->admin_json(0, $msg);
            } else {
                $pxwwhere['id'] = array('in', $postData['id']);
                $pxwwhere['r_status'] = array('<>', 1);
                $pxtnum = $pxM->getPxTeacherNum($pxwwhere);
                if ($pxtnum > 0) {
                    $this->admin_json(1, '培训讲师审核(ID:' . $postData['id'] . ')失败，原因:培训账户未审核');
                } else {
                    $this->admin_json(0, '培训讲师审核(ID:' . $postData['id'] . ')设置成功');
                }
            }
        } else {
            $this->render_json(1, '培训讲师审核设置失败');
        }
    }

    /**
     * 会员- 培训 - 培训师
     * 设置推荐
     * 2019-06-05 hjy
     */
    public function rec_action()
    {
        $pxId = intval($_POST['id']);
        $pxRec = intval($_POST['rec']);
        $upData = array();
        $upData['rec'] = $pxRec;
        $pxM = $this->MODEL('train');
        $whereData = array('id' => array('=', $pxId));
        $info = $pxM->getTeaInfo($whereData, array('field' => '`uid`, `name`'));
        if (empty($info)) {
            $this->render_json(1, '设置失败');
        }
        $nid = $pxM->upTeaInfo($whereData, $upData);
        $sysmsgM = $this->MODEL('sysmsg');
        if ($nid && $pxRec == 1) {
            $sysmsgM->addInfo(array('content' => '管理员设置：推荐培训师' . $info['name'], 'usertype' => 4, 'uid' => $info['uid']));
        } elseif ($nid && $pxRec == 0) {
            $sysmsgM->addInfo(array('content' => '管理员操作：取消培训师推荐' . $info['name'], 'usertype' => 4, 'uid' => $info['uid']));
        }

        if ($nid) {
            $this->admin_json(0, "培训师(ID:{$pxId})推荐设置成功");
        } else {
            $this->render_json(1, "培训师推荐设置失败");
        }
    }

    // 获取编辑需要的缓存
    public function getCache_action()
    {
        $cacheData = $this->MODEL('cache')->GetCache(array('subject', 'hy'));

        /**
         * @var $subject_index
         * @var $subject_name
         * @var $industry_index
         * @var $industry_name
         */
        extract($cacheData);

        $subjectList = array();
        if (!empty($subject_index)) {
            foreach ($subject_index as $sival) {
                $subjectList[] = ['id' => $sival, 'name' => $subject_name[$sival]];
            }
        }

        $hyList = array();
        if (!empty($industry_index)) {
            foreach ($industry_index as $ival) {
                $hyList[] = ['id' => $ival, 'name' => $industry_name[$ival]];
            }
        }
        $search_list = $this->set_search();
        $this->render_json(0, 'ok', compact('subjectList', 'hyList','search_list'));
    }

    /**
     * 会员- 培训 - 培训师 - 修改
     * 修改培训师
     * 2019-06-05 hjy
     */
    public function save_action()
    {
        $pxM = $this->MODEL('train');
       
        $postData = $this->post_trim($_POST);
        $id = intval($postData['id']);
        $teacher = $pxM->getTeaInfo(array('id' => array('=', $id)), array('field' => '`pic`'));
        if (empty($teacher)) {
            $this->render_json(1, '参数错误');
        }
        unset($postData['id']);

        $postData['file'] = $_FILES['file'];
        if ($_POST['r_status'] == 1) {
            $postData['status'] = 1;
        } else {
            $postData['status'] = 0;
        }
        $nid = $pxM->upTeaInfo(array('id' => array('=', $id)), $postData);
        if ($nid) {
            $this->admin_json(0, '培训讲师（ID：' . $id . '）信息修改成功');
        } else {
            $this->render_json(1, '培训讲师信息修改失败');
        }
    }

    /**
     * 会员- 培训 - 培训师
     * 删除培训师
     * 2019-06-05 hjy
     */
    public function del_action()
    {
        $del = $_POST['del'];
        if (is_array($del)) {
            $layer_type = 1;
            $del = pylode(',', $del);
        } else {
            $layer_type = 0;
        }
        if (empty($del)) {
            $this->render_json(1, '参数错误');
        }
        $pxM = $this->MODEL('train');
        $list = $pxM->getTeaList(array('id' => array('in', $del)), array('field' => '`uid`, `name`, `pic`'));
        if (!empty($list)) {
            $sysmsgM = $this->MODEL('sysmsg');
            $pxM->delTea(array('id' => array('in', $del)));
            foreach ($list as $v) {
                $sysmsgM->addInfo(array('content' => '管理员操作：删除培训讲师《' . $v['name'] . '》', 'usertype' => 4, 'uid' => $v['uid']));
            }
        }
        $this->admin_json(0, '培训师(ID:' . $del . ')删除成功');
    }

    /**
     * 会员- 培训 - 培训师
     * 数据统计
     * 2019-06-05 hjy
     */
    public function teacherNum_action()
    {
        $MsgNum = $this->MODEL('msgNum');
        echo $MsgNum->teacherNum();
    }


}