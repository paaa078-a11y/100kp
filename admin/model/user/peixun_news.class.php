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

class peixun_news_controller extends adminCommon
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
                '1' => '已审核',
                '3' => '未审核',
                '2' => '未通过',
                '4' => '已锁定'
            )
        );

        return $search_list;
    }

    /**
     * 会员 - 培训 - 培训新闻
     * 2019-06-06 hjy
     */
    public function index_action()
    {

        $pxWhereData = array();

        $keywordStr = trim($_POST['keyword']);
        $typeStr = trim($_POST['type']);
        $pxM = $this->MODEL('train');

        //新闻标题条件
        if (!empty($keywordStr) && $typeStr == 2) {
            $pxWhereData['title'] = array('like', $keywordStr);
        }
        $uidArr = array();
        if (!empty($keywordStr) && $typeStr == 1) {
            $trows = $pxM->getList(array('name' => array('like', $keywordStr)), array('field' => '`uid`'));
            if (!empty($trows)) {
                foreach ($trows as $val) {
                    $uidArr[] = $val['uid'];
                }
            } else {
                $uidArr = array(0);
            }
        }
        if (!empty($uidArr)) {
            $pxWhereData['uid'] = array('in', pylode(',', $uidArr));
        }

        if (!empty($_POST['status'])) {
            if ($_POST['status'] == 3) {
                $pxWhereData['status'] = array('=', 0);
            } else if ($_POST['status'] == 4) {
                $pxWhereData['r_status'] = array('=', 2);
            } else {
                $pxWhereData['status'] = array('=', $_POST['status']);
            }
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('px_train_news', $pxWhereData, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        //分页数大于0的情况下 执行列表查询
        $list = array();
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if (!empty($_POST['order']) && !empty($_POST['t'])) {
                $pxWhereData['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $pxWhereData['orderby'] = array('r_status,asc', 'status,asc', 'uid,desc');
            }

            $pxWhereData['limit'] = $pages['limit'];
            $list = $pxM->getPxnewsList($pxWhereData);

            foreach ($list as $key => $val) {
                $list[$key]['news_url'] = Url('train', array('c' => 'newsshow', 'id' => $val['uid'], 'nid' => $val['id']));
                $list[$key]['train_url'] = Url('train', array('c' => 'agencyshow', 'id' => $val['uid']));
            }
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }
    public function  getSearchData_action(){
        $search_list = $this->set_search();
        $this->render_json(0,'',compact('search_list'));
    }
    // 新闻审核-同步审核培训机构
    function newstatus_action()
    {
        if (empty($_POST['id']) || empty($_POST['uid'])) {
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

        $return = $pxM->statusNews($id, $post);

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员 - 培训 - 培训新闻
     * 保存审核信息
     * 2019-06-06 hjy
     */
    public function status_action()
    {
        $_POST = $this->post_trim($_POST);
        if (empty($_POST['id'])) {
            $this->render_json(1, '参数错误');
        }

        $ids = pylode(',', $_POST['id']);

        $pxM = $this->MODEL('train');
        $list = $pxM->getPxnewsList(array('id' => array('in', $ids), 'r_status' => 1), array('field' => '`uid`, `title`'));
        $upData = array();
        $upData['status'] = $_POST['status'];
        $upData['statusbody'] = $_POST['statusbody'];
        $id = $pxM->upPxnewsInfo(array('id' => array('in', $ids), 'r_status' => 1), $upData);
        if (!empty($id)) {
            if (is_array($list) && $list) {
                $sysmsgM = $this->MODEL('sysmsg');
                /* 消息前缀 */
                $tagName = '新闻';
                //发送会员通知
                foreach ($list as $v) {
                    $uids[] = $v['uid'];

                    /* 处理审核信息 */
                    if ($upData['status'] == 2) {
                        $statusInfo = $tagName . ':' . $v['title'] . '审核未通过 ';
                        if ($upData['statusbody']) {
                            $statusInfo .= ', 原因：' . $upData['statusbody'];
                        }
                        $msg[$v['uid']][] = $statusInfo;
                    } elseif ($upData['status'] == 1) {
                        $msg[$v['uid']][] = $tagName . ':' . $v['title'] . '已审核通过';
                    }
                }

                $sysmsgM->addInfo(array('uid' => $uids, 'usertype' => 4, 'content' => $msg));
            }

            $pxwhere['id'] = array('in', $ids);
            $pxnum = $pxM->getPxTrainNewsNum($pxwhere);
            if ($pxnum > 1) {
                $pxtwhere['id'] = array('in', $ids);
                $pxtwhere['r_status'] = 1;
                $pxtnum = $pxM->getPxTrainNewsNum($pxtwhere);
                $pxwwhere['id'] = array('in', $ids);
                $pxwwhere['r_status'] = array('<>', 1);
                $pxwnum = $pxM->getPxTrainNewsNum($pxwwhere);
                if ($pxwnum > 0) {
                    $msg = '培训新闻批量审核成功' . $pxtnum . '条，失败' . $pxwnum . '条原因:培训账户未审核';
                } else {
                    $msg = '培训新闻批量审核成功(ID:' . $ids . ')';
                }
                $this->admin_json(0, $msg);
            } else {
                $pxwwhere['id'] = array('in', $ids);
                $pxwwhere['r_status'] = array('<>', 1);
                $pxtnum = $pxM->getPxTrainNewsNum($pxwwhere);

                if ($pxtnum > 0) {
                    $this->render_json(1, '培训新闻审核(ID:' . $ids . ')失败，原因:培训账户未审核');
                } else {
                    $this->admin_json(0, '培训新闻审核(ID:' . $ids . ')设置成功');
                }
            }
        } else {
            $this->render_json(1, '培训新闻审核设置失败');
        }
    }

    /**
     * 会员 - 培训 - 培训新闻
     * 删除新闻
     * 2019-06-06 hjy
     */
    public function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $del = $_POST['del'];
        $ids = pylode(',', $del);

        $pxM = $this->MODEL('train');
        $list = $pxM->getPxnewsList(array('id' => array('in', $ids)), array('field' => '`uid`, `title`'));
        if (empty($list)) {
            $this->render_json(1, '数据异常');
        }

        $sysmsgM = $this->MODEL('sysmsg');
        foreach ($list as $v) {
            $sysmsgM->addInfo(array('content' => '管理员删除新闻《' . $v['title'] . '》', 'usertype' => 4, 'uid' => $v['uid']));
        }
        $did = $pxM->delPxnews(array('id' => array('in', $ids)));
        if (!empty($did)) {
            $this->admin_json(0, '培训新闻(ID:' . $ids . ')删除成功');
        } else {
            $this->render_json(1, '培训新闻删除失败');
        }
    }
}

?>