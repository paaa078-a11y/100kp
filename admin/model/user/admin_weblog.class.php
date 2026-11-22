<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class admin_weblog_controller extends adminCommon
{

    function index_action()
    {

        $webLogM    =   $this->MODEL('weblog');
        $UserinfoM  =   $this->MODEL('userinfo');

        $where['usertype']  =   array('in', '1,2');
        if (trim($_POST['keyword'])) {
            if ($_POST['type'] == 1) {
                $username   =   $UserinfoM->getList(array('username' => array('like', trim($_POST['keyword']))), array('field' => '`uid`,`username`'));
                if (!empty($username)) {
                    foreach ($username as $val) {
                        $muids[]    =   $val['uid'];
                    }
                    $where['uid']   =   array('in', pylode(',', $muids));
                }else{
                    $where['uid']   =   '';
                }
            }
        }
        if ($_POST['usertype']) {

            $where['usertype']  =   $_POST['usertype'];
        }

        if ($_POST['times']) {

            $today  =   strtotime(date('Y-m-d'));
            if ($_POST['times'] == 1) {

                $where['time']  =   array('>=', $today, 'AND');
            } else if ($_POST['times'] == 2) {

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['time']  =   array('>=', strtotime('-1 day', $today), 'AND');
                $where['time']  =   array('<', $today, 'AND');
                $where['PHPYUNBTWEND_A']    =   '';
            } else if ($_POST['times'] == 3) {

                $where['time']  =   array('>=', strtotime('-3 day', $today), 'AND');
            } else if ($_POST['times'] == 7) {

                $where['time']  =   array('>=', strtotime('-7 day', $today), 'AND');
            }
        }

        if ($_POST['time']) {
//            $times              =   @explode('~', $_POST['time']);
            $times = $_POST['time'];
            $where['PHPYUNBTWSTART_A']  =   '';
            $where['time'][]    =   array('>=', strtotime($times[0] . " 00:00:00"), 'AND');
            $where['time'][]    =   array('<=', strtotime($times[1] . " 23:59:59"), 'AND');
            $where['PHPYUNBTWEND_A']  =   '';
        }
        $pageM			=	$this  -> MODEL('page');
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pages	=	$pageM -> adminPageList('web_log',$where,$page,array('limit' => $pageSize));
        if(!$pages['total']){
            $this->render_json(0,'暂无数据',array('data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']));
        }
        if ($_POST['order']) {

            $where['orderby']   =   $_POST['t'] . ',' . $_POST['order'];
        } else {
            $where['orderby']   =   'id,desc';
        }
        $where['limit'] =   $pages['limit'];
        $rows           =   $webLogM->getWebLogList($where, array('utype' => 'admin'));

        $this->render_json(0,'',['data'=>$rows,
            'total'=>(int)$pages['total'],
            'pageSizes'=>$pages['page_sizes']
        ]);
    }

    function getloglist_action()
    {

        if (!$_POST['uid']) {
            $this->render_json('0','参数错误');

        }
        $webLogM        =   $this->MODEL('weblog');

        $where['uid']   =   intval($_POST['uid']);
        //默认取当天数据
        $today          =   strtotime(date('Y-m-d'));
        if ($_POST['time'] == 1) {
            $where['time']  =   array('>=', $today, 'AND');
        } else if ($_POST['time'] == 2) {
            $where['PHPYUNBTWSTART_A']  =   '';
            $where['time'][]            =   array('>=', strtotime('-1 day', $today), 'AND');
            $where['time'][]            =   array('<', $today, 'AND');
            $where['PHPYUNBTWEND_A']    =   '';
        } else if ($_POST['time'] == 3) {
            $where['time']  =   array('>=', strtotime('-3 day', $today), 'AND');
        } else if ($_POST['time'] == 4) {
            $where['time']  =   array('>=', strtotime('-7 day', $today), 'AND');
        }

        if ($_POST['times']) {
            $times              =   @explode('~', $_POST['times']);
            $where['PHPYUNBTWSTART_B']  =   '';
            $where['time'][]    =   array('>=', strtotime($times[0] . "00:00:00"), 'AND');
            $where['time'][]    =   array('<=', strtotime($times[1] . "23:59:59"), 'AND');
            $where['PHPYUNBTWEND_B']  =   '';
        }
        $pagenav            =   $_POST['pagenav'] > 0 ? $_POST['pagenav'] : 0;

        $where['limit']     =   array($pagenav * $this->config['sy_listnum'], $this->config['sy_listnum']);
        $where['orderby']   =   'id';

        $rows               =   $webLogM->getWebLogList($where);

        if (!empty($rows)) {

            $List['code']       =   1;
            $List['list']       =   $rows;
            $List['pagenav']    =   $pagenav + 1;
        } else {

            $List['code']       =   2;
            $List['list']  = [];
        }
        $this->render_json(0,'',$List);


    }

    function getlogtj_action()
    {

        if (!$_POST['uid']) {
            $this->render_json('0','参数错误');
        }
        $webLogM    =   $this->MODEL('weblog');
        $logCount   =   $webLogM->logCount(array('uid' => $_POST['uid'], 'time' => $_POST['time'], 'times' => $_POST['times'], 'usertype' => $_POST['usertype']));
        $this->render_json(0,'',$logCount);
    }

    function del_action(){
        if (!$_POST['del']){
            $this->render_json(1,'参数错误');
        }
        if (is_array($_POST['del'])){
            $id = pylode(',', $_POST['del']);
        }else{
            $id = $_POST['del'];
        }
        $where = array('id'=>array('in',$id));
        $res   = $this->MODEL('weblog')->delWeblog($where, array('norecycle' => 1));
        if ($res['errcode']==9){
            $this->render_json(0,$res['msg']);
        }else{
            $this->render_json(1,$res['msg']);
        }
    }

    function config_action()
    {
        include(CONFIG_PATH."db.data.php");

        $userModelArr   =   explode(',', $this->config['web_user_model']);
        $comModelArr   =   explode(',', $this->config['web_com_model']);

        $this->render_json('0','',array('userModelArr'=>$userModelArr,'comModelArr'=>$comModelArr,'userModel'=>$arr_data['web_user_model'],'comModel' => $arr_data['web_com_model'],'sy_web_log_switch'=>$this->config['sy_web_log_switch']?$this->config['sy_web_log_switch']:'2'));

    }


    function setConfig_action(){

        $configM = $this->MODEL('config');
        $configM->setConfig($_POST);
        $this->web_config();
        $this->admin_json(0, '行为轨迹配置设置成功');

    }

}

?>