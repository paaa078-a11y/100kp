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

	function index_action()
    {

		$where              =   array();
		if ($_POST['comid']){
		    $where['comid'] =   $_POST['comid'];
        }

		if($_POST['ltime']){
			if($_POST['ltime']=='1'){

				$mWhere['login_date']	=	array('>',strtotime(date("Y-m-d 00:00:00")));
			}else{

                $mWhere['login_date']	=	array('>',strtotime('-'.(int)$_POST['ltime'].'day'));
			}

			$memberList =   $this->obj->select_all('member', $mWhere, '`uid`');

			$uidArr     =   array();
			foreach ($memberList as $mv){

			    $uidArr[]       =   $mv['uid'];
            }
			$where['uid']       =   array('in', pylode(',', $uidArr));
		}

        if ($_POST['ctime']) {
            if ($_POST['ctime'] == '1') {
                $where['ctime'] =   array('>', strtotime(date("Y-m-d 00:00:00")));
            } else {
                $where['ctime'] =   array('>', strtotime('-'.(int)$_POST['ctime'].' day'));
            }
        }
        if ($_POST['status']) {
            $where['status']    =   $_POST['status'];
        }
        if (trim($_POST['keyword'])) {
            if ($_POST['type'] == 1) {
                $where['name']  =   array('like', trim($_POST['keyword']));
            } elseif ($_POST['type'] == 2) {

                $where['uname'] =   array('like', trim($_POST['keyword']));
            } elseif ($_POST['type'] == 3) {

                $where['uid']   =   trim($_POST['keyword']);
            }
        }
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        $pageM	=	$this  -> MODEL('page');
        $pages	=	$pageM -> adminPageList('company_account',$where,$page,array('limit' => $pageSize));
        if (!$pages['total']) {
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }

        if ($_POST['order']) {
            $where['orderby']   =   $_POST['t'].','.$_POST['order'];
        } else {
            $where['orderby']   =   array('uid,desc');
        }
        $where['limit']         =   $pages['limit'];

        $CompanyaccountM	=	$this->MODEL('companyaccount');
        $List   =   $CompanyaccountM->getList($where, array('utype' => 'admin'));
        $data = array(
            'data'=>$List,'total'=>(int)$pages['total'],
            'pageSizes'=>$pages['page_sizes']
        );
        $this->render_json(0,'',$data);

    }

	function getNum_action()
    {
        $accountM   =   $this->MODEL('companyaccount');
        $arr =  $accountM->getListNumV1();
        $this->render_json('0','',$arr);
    }

    function reset_pw_action()
    {

        $userinfoM  =   $this->MODEL('userinfo');
        $userinfoM->upInfo(array('uid' => intval($_POST['uid'])), array('password' => '123456'));
        $this->MODEL('log')->addAdminLog('子账号(UID:' . $_POST['uid'] . ')重置密码成功');
        $this->render_json('0','操作成功');
    }

    function del_action()
    {

        if (!$_POST['del']){
            $this->render_json(1,'参数错误');
        }
        if(!$_POST['pid']){
            $this->render_json(1,'参数错误');
        }
        $accountM   =   $this->MODEL('companyaccount');
        $return     =   $accountM->delChild(array('uid' => $_POST['del'],'pid'=>$_POST['pid']));
        if ($return['ecode']==9){
            $this->admin_json(0,$return['msg']);
        }else{
            $this->render_json(1,$return['msg']);
        }
    }

}
?>