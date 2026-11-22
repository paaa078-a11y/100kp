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
class subscribe_controller extends user_controller{
    
    function index_action()
    {
        $data = array();
        $subscribeM     =   $this->MODEL('subscribe');

        $page           =   $_POST['page'];
        $where['uid'] 	=   $this->member['uid'];
        $where['type']  =   1;
        $total          =   $subscribeM->getNum($where);

        $where['orderby']   =   'id,desc';

        $limit          =   $_POST['limit'] ? $_POST['limit'] : 10;

        if ($page) {//分页

            $pagenav        =   ($page - 1) * $limit;
            $where['limit'] =   array($pagenav, $limit);
        } else {
            $where['limit'] =   $limit;
        }

        $rows = $subscribeM->getList($where);
        $list = count($rows) ? $rows : array();
        
        $gzhurl = '';

        $userInfoM  =   $this->MODEL('userinfo');
        $userInfo   =   $userInfoM->getInfo(array('uid' =>$this->member['uid']), array('field' => '`subscribe`,`wxid`,`email`,`email_status`'));
        $email_c    =   0;

        if($userInfo['email'] && $userInfo['email_status']==1){
            $email_c    =   1;
        }

        if(empty($list)){
            
            $isSubscribe=   $userInfo['subscribe'];

            if ($userInfo['subscribe'] != 1 && !empty($userInfo['wxid'])){
                $wxM    =   $this->MODEL('weixin');
                $wxUser =   $wxM->getWxUser($userInfo['wxid']);
                $isSubscribe    =   $wxUser['subscribe'];
                $userInfoM->upInfo(array('uid' =>$this->member['uid']),array('subscribe' => $wxUser['subscribe']));
            }

            if($isSubscribe!=1){
                $gzhurl = Url('wap', array('c'=>'ajax','a'=>'gzhqrcode','token'=>$this->member['gzhtoken']));
            }
        }
        $data['list'] = $list;
        $data['isSubscribe'] = !empty($isSubscribe)?$isSubscribe:0;
        $data['gzhurl'] = $gzhurl;

        $data['email_c'] = $email_c;

        $this->render_json(0, '', $data, $total);
            
    }
    
	function del_action(){

		if($_POST['id']){
			
			$id			=	intval($_POST['id']);
			
			$subscribeM     =   $this->MODEL('subscribe');

			$return		=	$subscribeM->del($id, array('uid' => $this->member['uid'], 'usertype' => $this->member['usertype']));

	        $error 		= 	$return['errcode']=='9' ? 1 : 2;
	        $msg		=	$return['msg'];
	    }else{
	    	$error 		= 	2;
	        $msg		=	'数据异常，请重试';
	    }

	    $this->render_json($error,$msg);

	}

	function record_action()
    {

        $subscribeM     =   $this->MODEL('subscribe');

        $page           =   $_POST['page'];
        $where['uid'] 	=   $this->member['uid'];
        $where['type']  =   1;
        $total          =   $subscribeM->getRecordNum($where);

        $where['orderby']   =   'id,desc';

        $limit          =   $_POST['limit'] ? $_POST['limit'] : 10;

        if ($page) {//分页

            $pagenav        =   ($page - 1) * $limit;
            $where['limit'] =   array($pagenav, $limit);
        } else {
            $where['limit'] =   $limit;
        }

        $rows = $subscribeM->getRecordList($where,array('utype'=>'user','uid'=>$this->member['uid'],'usertype'=>$this->member['usertype']));
        $list = count($rows) ? $rows : array();
        $this->render_json(0, '', $list, $total);
    }
    function getSubscribe_action(){

        $subscribeM     =   $this->MODEL('subscribe');

        $info = $subscribeM->getInfo(array('id'=>$_POST['id']));

        $return['info'] = $info;

        $this->render_json(0,'',$return);
    }

    function saveSub_action(){
        
        $subscribeM = $this->MODEL('subscribe');

        $data = array(
            'sid'           =>  $_POST['id'],
            'time'          =>  $_POST['time'],
            'jobclass_id'   =>  $_POST['jobclass_id'],
            'type'          =>  1,
            'provider'      =>  $_POST['provider'],
            'uid'           =>  $this->member['uid'],
            'usertype'      =>  $this->member['usertype'],
            'utype'         =>  'user'
        );
        
        $res            =   $subscribeM->createSubscribe($data);

        echo json_encode($res);die;
    }
	
}