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
class company_evaluate_controller extends adminCommon
{
    //面试评论
    function index_action()
    {
        $pageM = $this->MODEL('page');
        $companyM = $this->MODEL('company');
        $resumeM = $this->MODEL('resume');

        //发送时间
        if ($_POST['end']) {
            if ($_POST['end'] == '1') {
                $where['ctime'] = array('>=', strtotime('today'));
            } else {
                $where['ctime'] = array('>=', strtotime('-' . intval($_POST['end']) . ' day'));
            }
        }
        //回复时间
        if ($_POST['r_time']) {
            if ($_POST['r_time'] == '1') {
                $where['reply_time'] = array('>=', strtotime('today'));
            } else {
                $where['reply_time'] = array('>=', strtotime('-' . intval($_POST['r_time']) . ' day'));
            }
        }
        if ($_POST['keyword']) {
            $keytype = intval($_POST['type']);
            $keyword = trim($_POST['keyword']);
            if ($keytype == 1) {
                //评论人
                $rwhere['name'] = array('like', $keyword);
                $resume = $resumeM->getResumeList($rwhere, array('field' => 'uid,name'));
                foreach ($resume as $v) {
                    $userid[] = $v['uid'];
                }
                $where['uid'] = array('in', pylode(',', $userid));
            } elseif ($keytype == 2) {
                //公司名称
                $cwhere['name'] = array('like', $keyword);
                $ctList = $companyM->getList($cwhere, array('field' => 'uid'));
                $comapant = $ctList['list'];
                foreach ($comapant as $v) {
                    $comid[] = $v['uid'];
                }
                $where['cuid'] = array('in', pylode(',', $comid));
            } elseif ($keytype == 3) {
                //面试过程
                $where['content'] = array('like', $keyword);
            }
        }
        //审核状态
        if (isset($_POST['status'])) {
            $where['status'] = $_POST['status'];
        }
        //提取分页
        $page = $pageM->page($_POST);
        $pageSize = $pageM->limit($_POST);
        $pages = $pageM->adminPageList('company_msg', $where, $page, array('limit' => $pageSize));
        $pageSizes = $pages['page_sizes'];
        $list = array();
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = 'id,desc';
            }

            $where['limit'] = $pages['limit'];
            $list = $companyM->getCompanyMsgList($where, array('utype' => 'admin'));
            foreach ($list as $key => $value) {
                $list[$key]['desscore'] = intval($value['desscore']);
                $list[$key]['hrscore'] = intval($value['hrscore']);
                $list[$key]['comscore'] = intval($value['comscore']);
                $list[$key]['ctime_n'] = date('Y-m-d H:i', $value['ctime']);
                $list[$key]['reply_time_n'] = $value['reply_time'] ? date('Y-m-d H:i', $value['reply_time']) : '';
            }
        }
        $rt = array();
        $rt['list'] = $list;
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pageSizes;
        $this->render_json(0, '', $rt);
    }

    function lockinfo_action()
    {
        $companyM = $this->MODEL('company');
        $msg = $companyM->getCompanyMsgInfo(array('id' => $_POST['cid']), array('field' => 'statusbody'));
        $this->render_json(0, '', trim($msg['statusbody']));
    }

    function status_action()
    {
        $companyM = $this->MODEL('company');
        $post = array(
            'status' => intval($_POST['status']),
            'statusbody' => trim($_POST['statusbody'])
        );
        $cids = @explode(',', $_POST['cid']);
        $return = $companyM->statusComment(array('id' => array('in', pylode(',', $cids))), array('post' => $post));
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg'] . '(ID:' . $_POST['cid'] . ')');
        } else {
            $this->render_json($return['errcode'], $return['msg']);
        }
    }

    function del_action()
    {
        $compannyM = $this->MODEL('company');
        $delID = is_array($_POST['del']) ? $_POST['del'] : $_POST['id'];
        if ($delID) {
            $where['id'] = array('in', pylode(',', $delID));
            $list = $compannyM->getCompanyMsgList($where, array('field' => 'uid'));
            $return = $compannyM->delCompanyMsg($delID);
            if ($list) {
                foreach ($list as $v) {
                    $this->automsg('管理员操作：删除会员（ID：' . $v['uid'] . '）面试评论', $v['uid']);
                }
            }
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json($return['errcode'], $return['msg']);
            }
        } else {
            $this->render_json(8, '非法操作！');
        }
    }

    function show_action()
    {
        $companyM = $this->MODEL("company");
        $id = intval($_POST['id']);
        $info = $companyM->getCompanyMsgInfo(array('id' => $id), array('field' => 'content,othercontent,reply', 'type' => 'admin'));
        $data['content'] = $info['content'];
        $data['othercontent'] = $info['othercontent'];
        $data['reply'] = $info['reply'];
        $this->render_json(0, '', $data);
    }
}