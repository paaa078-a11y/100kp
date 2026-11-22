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
class finance_withdraw_controller extends adminCommon{

	function index_action(){
		$packM							=	$this -> MODEL('pack');
		
		$where							=   array();
	    $keywordStr						=   trim($_POST['keyword']);
        if (!empty($keywordStr) && $_POST['typeca']=='1') {

            $where['order_id']		=	array('like', $keywordStr);

        }elseif(!empty($keywordStr) && $_POST['typeca']=='2'){
            $UserinfoM				=	$this -> MODEL('userinfo');
            $orderinfo				=	$UserinfoM -> getList(array('username'=>array('like',$keywordStr)),array('field'=>'uid'));
            if (is_array($orderinfo)){
                foreach ($orderinfo as $val){
                    $orderuids[]	=	$val['uid'];
                }
                $where['uid']		=	array('in', pylode(",",$orderuids));
            }
        }
		if($_POST['order_state']!=""){
			$where['order_state']		=	$_POST['order_state'];
		}
		if($_POST['time']){
			if($_POST['time'] == 1){
				$where['time']	=	array('>=',strtotime(date("Y-m-d 00:00:00")));
			}else{
				$where['time']	=	array('>=',strtotime('-'.intval($_POST['time']).' day'));
			}
		}


        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);


        $pageM						=	$this  -> MODEL('page');
        $pages						=	$pageM -> adminPageList('member_withdraw',$where,$page,array('limit' => $pageSize));
		if(!$pages['total']) {
            $this->render_json(0,'暂无数据',['data'=>[],'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
        //limit order 只有在列表查询时才需要
        if($_POST['order']){
            $where['orderby']		=	$_POST['t'].','.$_POST['order'];
        }else{
            $where['orderby']		=	array('id,desc');
        }
        $where['limit']				=	$pages['limit'];
        $rows    					=   $packM -> getList($where,array('utype'=>'admin'));

        $this->render_json(0,'',['data'=>$rows,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']]);

    }


	function setpay_action(){
		$id			=	intval($_POST['id']);

		$packM		=	$this -> MODEL('pack');
        $num = $packM->getWithdrawNum(['id'=>$id]);
        if (!$num){
            $this->render_json(1,'暂无数据',['data'=>[],'total'=>0]);

        }
		$return		=	$packM -> setPay($id);
        if ($return['errcode']==9){
            $this->admin_json(0,$return['msg']);
        }else {
            $this->render_json(1,$return['msg']);
        }
	}
	function del_action(){
        $id  = $_POST['id'];
        if (!$id){
            $this->render_json(1,'参数错误,请重试');
        }
		$packM		=	$this -> MODEL('pack');
		$return		=	$packM -> delWithDraw($id);

        $msg = $return['msg'];
        if ($return['errcode'] == 9){
            $code = 0;
        }else{
            $code = 1;
        }
		$this -> admin_json($code,$msg);
	}
}
?>