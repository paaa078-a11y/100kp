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

class subscribe_controller extends company
{
    function index_action()
    {

        $where['uid']   =   $this->uid;
        $where['type']  =   2;

        $urlarr['c']    =   $_GET['c'];
        $urlarr['page'] =   '{{page}}';
        $pageurl        =   Url('member', $urlarr);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('subscribe', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['orderby']   =   'id';
            $where['limit']     =   $pages['limit'];

            $subscribeM =   $this->MODEL('subscribe');
            $List       =   $subscribeM->getList($where);
            $this->yunset("rows", $List);
        }

        $this->company_satic();
        $this->public_action();
        $this->com_tpl('subscribe');
    }

    function toSubscribe_action()
    {

        $userInfoM  =   $this->MODEL('userinfo');
        $userInfo   =   $userInfoM->getInfo(array('uid' => $this->uid), array('field' => '`subscribe`,`wxid`'));

        $isSubscribe=   $userInfo['subscribe'];
        if ($userInfo['subscribe'] != 1 && !empty($userInfo['wxid'])){
            $wxM    =   $this->MODEL('weixin');
            $wxUser =   $wxM->getWxUser($userInfo['wxid']);
            $isSubscribe    =   $wxUser['subscribe'];
            $this->obj->update_once('member', array('subscribe' => $wxUser['subscribe']), array('uid' => $this->uid));
        }

        echo $isSubscribe;die;
    }

    function record_action()
    {

        $where['uid']   =   $this->uid;
        $where['type']  =   2;

        $urlarr['c']    =   $_GET['c'];
        $urlarr['act']  =   $_GET['act'];
        $urlarr['page'] =   '{{page}}';

        $pageurl    =   Url('member', $urlarr);
        $pageM      =   $this->MODEL('page');
        $pages      =   $pageM->pageList('subscriberecord', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['orderby']   =   'id';
            $where['limit']     =   $pages['limit'];

            $subscribeM =   $this->MODEL('subscribe');
            $List       =   $subscribeM->getRecordList($where);
            $this->yunset("rows", $List);
        }
        $this->public_action();
        $this->com_tpl('subscriberecord');
    }

    function del_action()
    {

        if ($_GET['id']) {

            $id =   intval($_GET['id']);
        } elseif ($_POST['delid']) {

            $id =   $_POST['delid'];
        }

        $subscribeM =   $this->MODEL('subscribe');
        $return     =   $subscribeM->del($id, array('uid' => $this->uid, 'usertype' => $this->usertype));
        $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }
}

?>