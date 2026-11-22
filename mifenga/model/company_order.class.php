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

class company_order_controller extends adminCommon
{

	//设置高级搜索功能
	function set_search($type = null)
    {

		include (APP_PATH."/config/db.data.php");
		
		$search_list[]	=	array("param"=>"typezf","name"=>'支付类型',"value"=>$arr_data['pay']);
		$search_list[]	=	array("param"=>"typedd","name"=>'订单类型',"value"=>$arr_data['ordertype']);
		if ($type == 1){
            $ratingM    =   $this->MODEL('rating');
            $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort'), array('field' => '`id`,`name`'));
            if (!empty($rating)) {

                $ratingarr  =   array();
                foreach ($rating as $k => $v) {

                    $ratingarr[$v['id']]    =   $v['name'];
                }
            }
            $search_list[]	=	array("param"=>"rating","name"=>'会员类型',"value"=>$ratingarr);
        }
		$search_list[]	=	array("param"=>"order_state","name"=>'订单状态',"value"=>array("0"=>"支付失败","1"=>"等待付款","2"=>"支付成功","3"=>"等待确认","4"=>"交易关闭"));
		
		$lo_time		=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月','31'=>'当月');
		$search_list[]	=	array("param"=>"time","name"=>'充值时间',"value"=>$lo_time);
		
		$this -> yunset("search_list",$search_list);
	}

	function index_action()
    {

        if (isset($_GET['typedd']) && $_GET['typedd'] == 1) {

            $this->set_search($_GET['typedd']);
        } else {

            $this->set_search();
            unset($_GET['rating']);
        }

		$OrderM							=	$this -> MODEL('companyorder');
        session_start();
        // 修改成功之后跳转到列表页使用session中记录的上次进入列表页的请求参数
        if (isset($_GET['remember_prev']) && $_GET['remember_prev']) {
            foreach ($_SESSION['prev_companyorderlist'] as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        // 本次请求的参数保存到session中，供下次修改页面返回列表页使用
        $_SESSION['prev_companyorderlist'] = $_GET;
		$where							=   array();
	    $urlarr							=   $_GET;
	    $keywordStr						=   trim($_GET['keyword']);
		if($_GET['comid']){
			
			$where['uid']				=	$_GET['comid'];
			
			$where['usertype']			=	'2';
			
			$urlarr['comid']			=	$_GET['comid'];
		}
		if($_GET['typezf']!=""){
			$where['order_type']		=	$_GET['typezf'];

			$urlarr['typezf']			=	$_GET['typezf'];
		}
		if($_GET['typedd']!=""){

			$where['type']				=	$_GET['typedd'];
			$urlarr['typedd']			=	$_GET['typedd'];
			if (isset($_GET['typedd']) && $_GET['typedd'] == 1){

			    if (isset($_GET['rating']) && !empty($_GET['rating'])){

			        $where['rating']    =   $_GET['rating'];
			        $urlarr['rating']   =   $_GET['rating'];
                }
            }
		}

		if($_GET['news_search']){
			if (!empty($keywordStr) && $_GET['typeca']=='1') {

				$where['order_id']		=	array('like', $keywordStr);
			}elseif(!empty($keywordStr) && $_GET['typeca']=='2'){

				$UserinfoM				=	$this -> MODEL('userinfo');
				$orderinfo				=	$UserinfoM -> getList(array('username'=>array('like',$keywordStr)),array('field'=>'uid'));
				
				if (is_array($orderinfo)){
					foreach ($orderinfo as $val){
						$orderuids[]	=	$val['uid'];
					}
					$where['uid']		=	array('in', pylode(",",$orderuids));
				}
			}elseif(!empty($keywordStr) && $_GET['typeca']=='3'){

                $UserinfoM				=	$this -> MODEL('userinfo');
                $mWhere                 =   array(
                    '1' => array('name' =>  array('like', $_GET['keyword'])),
                    '2' => array('name' =>  array('like', $_GET['keyword'])),
                    '3' => array('realname' =>  array('like', $_GET['keyword'])),
                    '4' => array('name' =>  array('like', $_GET['keyword']))
                );
                $m_uids                 =	$UserinfoM -> getUidsByWhere($mWhere);

                $where['uid']		    =	array('in', pylode(",",$m_uids));
            }

			$urlarr['keyword']			=	$keywordStr;
			$urlarr['typeca']			=	$_GET['typeca'];
			$urlarr['news_search']		=	$_GET['news_search'];
		}

		if(isset($_GET['time']) || $_GET['time_start1'] != "" || $_GET['time_end1'] != ""){

			$where['PHPYUNBTWSTART_B']      =   'AND';
			if($_GET['time']){
				if($_GET['time'] == 1){

					$where['order_time'][]	=	array('>=',strtotime(date("Y-m-d 00:00:00")));
				}elseif ($_GET['time'] == 31){
                    $month = get_this_month(time());
                    $where['order_time'][] = array('>=',$month['start_month'],'and');
                    $where['order_time'][] = array('<=',$month['end_month'],'and');
                }else{

					$where['order_time'][]	=	array('>=',strtotime('-'.intval($_GET['time']).' day'));
				}
				$urlarr['time']				=	$_GET['time'];
			}
			if($_GET['time_start1']!=""){

				$where['order_time'][]		=	array('>=',strtotime($_GET['time_start1']." 00:00:00"));
				$urlarr['time_start1']		=	$_GET['time_start1'];
			}
			if($_GET['time_end1']!=""){

				$where['order_time'][]		=	array('<',strtotime($_GET['time_end1']." 23:59:59"));
				$urlarr['time_end1']		=	$_GET['time_end1'];
			}
			$where['PHPYUNBTWEND_B']	    =	'';
		}
		if(isset($_GET['order_state']) && $_GET['order_state']!=""){

			$where['order_state']		=	$_GET['order_state'];
			$urlarr['order_state']		=	$_GET['order_state'];
		}
		if(isset($_GET['fb']) && $_GET['fb']!=""){

			$where['is_invoice']		=	$_GET['fb'];
			$urlarr['fb']				=	$_GET['fb'];
		}

		$urlarr['page'] =	"{{page}}";
        // 删除记住上一次请求参数，防止生成的分页中含有该参数影响页面跳转
        if (isset($urlarr['remember_prev'])) {
            unset($urlarr['remember_prev']);
        }
		$pageurl	    =	Url($_GET['m'],$urlarr,'admin');
		
		$pageM  =	$this->MODEL('page');
		$pages  =	$pageM->pageList('company_order',$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
	        if($_GET['order']){

	            $where['orderby']		=	$_GET['t'].','.$_GET['order'];
	            $urlarr['order']		=	$_GET['order'];
	            $urlarr['t']			=	$_GET['t'];
	        }else{

	            $where['orderby']		=	array('id,desc');
	        }
	        $where['limit']				=	$pages['limit'];
	        $rows    					=   $OrderM -> getList($where,array('utype'=>'admin','field'=>'id,uid,order_id,order_price,type,rating,order_state,order_type,order_time,once_id,crm_uid,usertype'));

	        unset($where['limit']);

		    session_start();
		    $_SESSION['orderXls'] = $where;
	    }
        
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_company_order'));
	}

    function edit_action()
    {

        $OrderM =   $this->MODEL('companyorder');
        $row    =   $OrderM->getInfo(array('id' => $_GET['id']));

        if (isset($row) && $row['coupon']) {

            $couponM    =   $this->MODEL('coupon');
            $coupon     =   $couponM->getCouponListOne(array('id' => $row['coupon']), array('field' => 'id,coupon_amount'));
            if ($coupon) {

                $row['price']               =   number_format(($row['order_price'] + $coupon['coupon_amount']), 2);
                $row['order_price']         =   number_format($row['order_price'], 2);
                $coupon['coupon_amount']    =   number_format($coupon['coupon_amount'], 2);
            }
            $this->yunset('coupon', $coupon);
        }
        $htpics = $OrderM->getOrderHtPicList(array('order_id' => $row['id']));
        $this->yunset('htpics', $htpics);
        $this->yunset('row', $row);
        $this->yuntpl(array('admin/admin_company_order_edit'));
    }

    function save_action()
    {

        $id     =   intval($_POST['id']);
        $OrderM =   $this->MODEL('companyorder');

        $mData  =   array(
            'order_price'   =>  $_POST['order_price'],
            'order_remark'  =>  $_POST['order_remark'],
            'is_invoice'    =>  $_POST['is_invoice']
        );
        $return =   $OrderM->upInfo($id, $mData);

        $this->ACT_layer_msg($return['msg'], $return['errcode'], "index.php?m=company_order&remember_prev=1");
    }

    function setpay_action()
    {
        $this->check_token();

        $id     =   intval($_GET['id']);
        $OrderM =   $this->MODEL('companyorder');
        $return =   $OrderM->setPay($id);
        $this->layer_msg($return['msg'], $return['errcode'], 0, $_SERVER['HTTP_REFERER']);
    }

    function xls_action()
    {

        $where  =   $_SESSION['orderXls'] ? $_SESSION['orderXls'] : array('orderby' => 'id');
        $OrderM =   $this->MODEL('companyorder');

        if ($_POST['uid']) {

            $where['id']    =   array('in', pylode(',', $_POST['uid']));
        }
        if ($_GET['time_start'] != "" || $_GET['time_end'] != "") {

            $where['PHPYUNBTWSTART_B']  =   'AND';
            if ($_GET['time_start'] != "") {
                $where['order_time']    =   array('>=', substr($_POST['time_start'], 0, strlen($_POST['time_start']) - 3));
            }
            if ($_GET['time_end'] != "") {
                $where['order_time']    =   array('<', substr($_POST['time_end'], 0, strlen($_POST['time_end']) - 3));
            }
            $where['PHPYUNBTWEND_B']    =   '';
        }

        $rows   =   $OrderM->getList($where, array('utype' => 'admin', 'field' => 'id,uid,order_id,order_price,type,usertype,order_state,order_type,order_time,once_id,crm_uid'));

        if (!empty($rows)) {

            $this->yunset("list", $rows);

            $this->MODEL('log')->addAdminLog("导出支付订单信息");

            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=order.xls");
            $this->yuntpl(array('admin/admin_order_xls'));
        } else {

            $this->ACT_layer_msg("没有可以导出的订单信息！", 8, $_SERVER['HTTP_REFERER']);
        }
    }

    function del_action()
    {

        $this->check_token();

        $OrderM =   $this->MODEL('companyorder');
        $delID  =   is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];
        $return =   $OrderM->del($delID, array('utype' => 'admin'));
        $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }

    function orderSum_action()
    {
    	$where  =   $_SESSION['orderXls'] ? $_SESSION['orderXls']:array();
    	
        $MsgNum =   $this->MODEL('msgNum');
        echo $MsgNum->orderSum($where);
    }
    /*
     * 上传订单合同图片
     */
    function upload_action(){
        $orderM =	$this->MODEL('companyorder');
        if($_GET['editid']){
            $editrow = $orderM->getOrderHtPicInfo(array('id'=>$_GET['editid']));
            $this->yunset("pic",substr($editrow['pic'],(strrpos($editrow['pic'],'/')+1)));
            $this->yunset("editrow",$editrow);
            $id	= $editrow['order_id'];
        }
        $row = $orderM->getInfo(array('id' => $_GET['id']));
        $this->yunset("row",$row);
        $where['order_id'] = $_GET['id'];
        $urlarr['c'] = $_GET['c'];
        $urlarr = $_GET;
        $urlarr['page']	= "{{page}}";
        $pageurl = Url($_GET['m'],$urlarr,'admin');
        $pageM = $this->MODEL('page');
        $pages = $pageM->pageList('order_ht_pic',$where,$pageurl,$_GET['page']);
        if($pages['total'] > 0){
            $where['limit']	= $pages['limit'];
            $rows = $orderM->getOrderHtPicList($where);
        }
        $this->yunset("rows",$rows);
        $this->yuntpl(array('admin/admin_order_ht_upload'));
    }
    /*
     * 批量上传图片
     */
    function multiupload_action(){
        if($_FILES['file']['tmp_name']!=''){
            // pc端上传
            $upArr = array(
                'file' => $_FILES['file'],
                'dir' => 'order_ht'
            );
            $uploadM = $this->MODEL('upload');
            $pic = $uploadM->newUpload($upArr);
            if (!empty($pic['msg'])){
                echo json_encode(array('error'=> 1, 'msg' => $pic['msg']));
            }elseif (!empty($pic['picurl'])){
                echo json_encode(array('error'=> 0, 'picurl' => $pic['picurl'], 'picurl_n' => checkpic($pic['picurl'])));
            }
        }
    }

    /*
     * 保存订单合同图片
     */
    function uploadsave_action(){
        $orderM =	$this->MODEL('companyorder');
        $_POST = $this->post_trim($_POST);
        if($_POST['add']){
            $nbid = $orderM->addOrderHtPicInfo($_POST);
            isset($nbid) ? $this->ACT_layer_msg("订单合同图片(ID:" . $nbid . ")添加成功！",9, $_SERVER['HTTP_REFERER'],2,1): $this->ACT_layer_msg("添加失败！",8, $_SERVER['HTTP_REFERER']);
        }
    }
    /*
     * 删除订单合同图片
     */
    function htpic_del_action(){
        $ZphM =	$this->MODEL('companyorder');
        $id	= $_GET['delid'];
        if($id){
            $this->check_token();
            $delid = $ZphM->delOrderHtPic(array('id'=>$id));
            $delid ? $this->layer_msg("订单合同图片(ID:".$_GET['delid'].")删除成功！",9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
        }
    }
}
?>