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
class finance_invoice_controller extends adminCommon{

    function index_action(){
        $InvoiceM					=	$this -> MODEL('invoice');
        $where         				=   array();

        $keywordStr	   				=   trim($_POST['keyword']);
        if (!empty($keywordStr)) {
            $where['order_id']		=	array('like', $keywordStr);
        }
        if($_POST['status']!=""){
            $where['status']		=	$_POST['status'];
        }

        if($_POST['time']){
            if($_POST['time'] == 1){
                $where['addtime']	=	array('>=',strtotime(date("Y-m-d 00:00:00")));
            }else{
                $where['addtime']	=	array('>=',strtotime('-'.intval($_POST['time']).' day'));
            }
        }
        $page  = $_POST['page'];
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM						=	$this  -> MODEL('page');
        $pages						=	$pageM -> adminPageList('invoice_record',$where,$page,array('limit' => $pageSize));
        if(!$pages['total']) {
            $this->render_json(0,'暂无数据',['data'=>array(),'total'=>0,'pageSizes'=>$pages['page_sizes']]);
        }
        //limit order 只有在列表查询时才需要
        if($_POST['order']){
            $where['orderby']	=	$_POST['t'].','.$_POST['order'];
        }else{
            $where['orderby']	=	array('status,asc');
        }
        $where['limit']			=	$pages['limit'];
        $rows    				=   $InvoiceM -> getRecordList($where,array('utype'=>'admin','field'=>'id,oid,price,addtime,title,style,uid,link_moblie,email,status,statusbody'));
        foreach($rows as &$v){
            $v['addtime']  = date('Y-m-d',$v['addtime']);
            $v['title_jie']  = strlen($v['title'])>20?mb_substr($v['title'],0,20):$v['title'];
        }

        $this->render_json(0,'',array('data'=>$rows,'total'=>(int)$pages['total'],'pageSizes'=>$pages['page_sizes']));

    }
    function status_action(){
        $InvoiceM	=	$this -> MODEL('invoice');
        if (!$_POST['pid']){
            $this->render_json(1,'参数错误,请重试');
        }
        $pid = $_POST['pid'];
        if(!is_array($pid)){
            $pid = [$pid];
        }
        $post		=	array(
            'status'     =>  intval($_POST['status']),
            'statusbody' =>  trim($_POST['statusbody'])
        );

        $return    =  $InvoiceM -> setStatus(array('id' => array('in',pylode(",",$pid))),array('post'=>$post));
        if ($return['errcode'] == '9'){
            $this->admin_json(0,$return['msg']);
        }else{
            $this->render_json(1,$return['msg']);
        }
    }

    function show_action(){

        $id			=	intval($_POST['id']);
        if(!$id){
            $this->render_json(1,'参数错误请重试');
        }
        $InvoiceM	=	$this -> MODEL('invoice');
        $row		=	$InvoiceM ->getRecordInfo($id);
        if(!$row){
            $this->render_json(1,'数据不存在');
        }
        $statusArr =array(
            0=>'未审核',
            1=>'已审核',2=>'未通过',3=>'已打印',4=>'已邮寄'
        );
        $row['addtime_ymd'] = $row['addtime']?date('Y-m-d H:i:s',$row['addtime']):'-';
        $row['type_name'] = $row['type'] == 1?'普票':'增值税票';
        $row['style_name'] = $row['style'] == 1?'纸质':'电子';
        $row['status_name'] = $statusArr[$row['status']]?$statusArr[$row['status']]:'';
        $this->render_json(0,'',$row);
    }
    function del_action(){

        $InvoiceM	=	$this -> MODEL('invoice');
        $delID		=	is_array($_POST['del']) ? $_POST['del'] : $_POST['id'];
        if (!$delID){
            $this->render_json(1,'请选择要删除的数据信息');
        }
        $return		=	$InvoiceM -> del($delID);

        if ($return['errcode'] == 9){
            $this->admin_json(0,$return['msg']);
        }else{
            $this->admin_json(1,$return['msg']);
        }
    }
}
?>