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
class admin_zzh_controller extends adminCommon
{

    private function set_search()
    {

        $search_list[]  =  array('param'=>'ltime','name'=>'最近登录','value'=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
        $search_list[]  =  array('param'=>'ctime','name'=>'创建时间','value'=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
        $search_list[]  =  array('param'=>'status','name'=>'账号状态','value'=>array('1'=>'正常','2'=>'锁定'));
        $this->yunset('search_list',$search_list);
    }

    function index_action()
    {

        $this->set_search();

        $CompanyaccountM	=	$this->MODEL('companyaccount');

        $where              =   array();

        if ($_GET['comid']){

            $where['comid'] =   $_GET['comid'];
            $urlarr['comid'] =   $_GET['comid'];
        }

        if($_GET['ltime']){
            if($_GET['ltime']=='1'){

                $mWhere['login_date']	=	array('>',strtotime(date("Y-m-d 00:00:00")));
            }else{

                $mWhere['login_date']	=	array('>',strtotime('-'.(int)$_GET['ltime'].'day'));
            }

            $memberList =   $this->obj->select_all('member', $mWhere, '`uid`');

            $uidArr     =   array();
            foreach ($memberList as $mk => $mv){

                $uidArr[]       =   $mv['uid'];
            }

            $where['uid']       =   array('in', pylode(',', $uidArr));
            $urlarr['lotime']   =	$_GET['ltime'];
        }

        if ($_GET['ctime']) {
            if ($_GET['ctime'] == '1') {

                $where['ctime'] =   array('>', strtotime(date("Y-m-d 00:00:00")));
            } else {

                $where['ctime'] =   array('>', strtotime('-'.(int)$_GET['ctime'].' day'));
            }
            $urlarr['ctime']    =   $_GET['ctime'];
        }

        if ($_GET['status']) {

            $where['status']    =   $_GET['status'];
            $urlarr['status']   =   $_GET['status'];
        }

        if (trim($_GET['keyword'])) {
            if ($_GET['type'] == 1) {

                $where['name']  =   array('like', trim($_GET['keyword']));
            } elseif ($_GET['type'] == 2) {

                $where['uname'] =   array('like', trim($_GET['keyword']));
            } elseif ($_GET['type'] == 3) {

                $where['uid']   =   trim($_GET['keyword']);
            }

            $urlarr['keyword']  =   $_GET['keyword'];
            $urlarr['type']     =   $_GET['type'];
        }

        $urlarr        	=   $_GET;
        $urlarr['page']	=	'{{page}}';
        $pageurl		=	Url($_GET['m'],$urlarr,'admin');
        $pageM			=	$this->MODEL('page');
        $pages			=	$pageM->pageList('company_account',$where,$pageurl,$_GET['page']);
        if ($pages['total'] > 0) {

            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   array('uid,desc');
            }

            $where['limit']         =   $pages['limit'];

            $List   =   $CompanyaccountM->getList($where, array('utype' => 'admin'));

            $this->yunset("rows", $List);
        }
        $this->yuntpl(array('admin/admin_zzh'));
    }

    function getNum_action()
    {

        $accountM   =   $this->MODEL('companyaccount');

        $return     =   $accountM->getListNum();
        echo $return;
        exit();
    }

    function reset_pw_action()
    {

        $this->check_token();

        $userinfoM  =   $this->MODEL('userinfo');
        $userinfoM->upInfo(array('uid' => intval($_GET['uid'])), array('password' => '123456'));
        $this->MODEL('log')->addAdminLog('子账号(UID:' . $_GET['uid'] . ')重置密码成功');
        echo '1';
    }

    function del_action()
    {

        $accountM   =   $this->MODEL('companyaccount');
        $return     =   $accountM->delChild(array('uid' => $_GET['del']));
        $this->layer_msg($return['msg'], $return['ecode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }

}
?>