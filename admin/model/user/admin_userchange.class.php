<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2023 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class admin_userchange_controller extends adminCommon
{

    function index_action()
    {

        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {

            $keywordStr =   trim($_POST['keyword']);
            $typeStr    =   intval($_POST['typt']);
            if ($typeStr == 1) {

                $where['name']  =   array('like', $keywordStr);
            } elseif ($typeStr == 2) {

                $where['uid']   =   $keywordStr;
            }
        }
        if (isset($_POST['sqtype']) && !empty($_POST['sqtype'])) {

            $where['type']  =   $_POST['sqtype'];
        }
        if (isset($_POST['status']) && !empty($_POST['status'])) {

            $status =   intval($_POST['status']);
            $where['status']    =   $status == 3 ? 0 : $status;
        }
        if (isset($_POST['presusertype']) && !empty($_POST['presusertype'])) {

            $presusertype = intval($_POST['presusertype']);
            $where['pres_usertype'] = $presusertype;
        }
        if (isset($_POST['applyusertype']) && !empty($_POST['applyusertype'])) {

            $applyusertype = intval($_POST['applyusertype']);
            $where['apply_usertype'] = $applyusertype;
        }
        if (isset($_POST['applytime']) && !empty($_POST['applytime'])) {

            $start  =   date('Y-m-d', $_POST['applytime'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['applytime'][1] / 1000);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['applytime'][]       =   array('>=', strtotime($start . " 00:00:00"));
            $where['applytime'][]       =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $page   =   $_POST['page'];
        $pages  =   $pageM->adminPageList('user_change', $where, $page, array('limit' => $pageSize));
        if (!$pages['total']) {
            $this->render_json(0, '暂无数据', ['data' => [], 'total' => 0, 'pageSizes' => $pages['page_sizes']]);
        }
        if ($_POST['order']) {

            $where['orderby']   =   $_POST['t'].','.$_POST['order'];
        } else {

            $where['orderby']   =   array('status,asc', 'apply_time,desc');
        }

        $where['limit'] =   $pages['limit'];

        $userinfoM  =   $this->MODEL('userinfo');
        $List       =   $userinfoM->getUserChangeList($where, array('field' => '`id`,`uid`,`name`,`pres_usertype`,`apply_usertype`,`apply_time`,`lastupdate`,`status`,`applybody`,`did`,`statusbody`,`type`'));
        foreach ($List as &$v) {
            $v['apply_time_ymd'] = $v['apply_time'] ? date('Y-m-d H:i:s', $v['apply_time']) : '';
        }

        $this->render_json(0, '', array('data' => $List, 'total' => (int)$pages['total'], 'pageSizes' => $pages['page_sizes']));
    }

    /**
     * 申请列表（页面统计数量）
     */
    function memNum_action()
    {

        $MsgNum =   $this->MODEL('msgNum');
        $arr    =   $MsgNum->memchangeNumV1();
        $this->render_json(0, '', $arr);
    }

    function status_action()
    {

        $userinfoM = $this->MODEL('userinfo');

        $id     =   $_POST['id'];
        $status =   intval($_POST['status']);
        if (!$id) {
            $this->render_json('1', '请选择要审核的用户');
        }
        if ($status == "") {
            $this->render_json('1', '请选择审核状态');
        }
        if ($status == 2) {
            $state = 1;
        } else {
            $state = 0;
        }
        $data   =   array('status' => $status, 'statusbody' => $_POST['statusbody'], 'lastupdate' => time(), 'state' => $state);
        $id_arr =   $id;
        if (!is_array($id)) {

            $id_arr = @explode(',', $id);
        }
        if (!empty($id_arr)) {

            $return = $userinfoM->upAllUserChange($id_arr, $data, '5');
        } else {

            $moblie =   $_POST['moblie'];

            //查询手机号码：判断当前用户是哪个类型呢？
            $uid    =   $_POST['uid'];
            $return =   $userinfoM->upUserChange($id, $uid, $moblie, $data, '5');
        }
        if ($return['errcode'] == 9) {

            $this->admin_json(0, $return['msg']);
        } else {

            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 切换身份: 删除
     */
    function del_action()
    {
        if (!$_POST['del']) {

            $this->render_json(1, '参数错误');
        }

        $userInfoM  =   $this->MODEL('userinfo');
        $return     =   $userInfoM->delUserChange($_POST['del']);

        if ($return['errcode'] == 9) {

            $this->render_json(0, $return['msg']);
        } else {

            $this->render_json(1, $return['msg']);
        }
    }

}

?>