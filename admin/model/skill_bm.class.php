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
class skill_bm_controller extends adminCommon{

    function index_action(){
        $skillbM					=	$this -> Model('skillbm');

        $where = array();
        $SWhere = array();
        if($_GET['keyword']){
            if ($_GET['type']=='1') {
                $SWhere['title'] = array('like',trim($_GET['keyword']));
            } elseif ($_GET['type']=='2'){
                $where['name'] = array('like', trim($_GET['keyword']));
            }
            if ($SWhere) {
                $skills = $skillbM->getskillList($SWhere, array('field' => 'id'));
                if(!empty($skills)){
                    foreach($skills as $v){
                        $ids[] = $v['id'];
                    }
                }else{
                    $ids = array(0);
                }
                $where['skill_id'][] = array('in', pylode(',',$ids));
            }
            $urlarr['type']	= $_GET['type'];
            $urlarr['keyword'] = $_GET['keyword'];
        }

        $urlarr        	=   $_GET;
        $urlarr['page']	=	'{{page}}';
        $pageurl		=	Url($_GET['m'],$urlarr,'admin');
        $pageM			=	$this  -> MODEL('page');
        $pages			=	$pageM -> pageList('skill_bm',$where,$pageurl,$_GET['page']);
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_GET['order']) {
                $where['orderby'] = $_GET['t'] . ',' . $_GET['order'];
            } else {
                $where['orderby'] = array('id,desc');
            }
            $where['limit'] = $pages['limit'];
            $urlarr['order'] = $_GET['order'];
            $urlarr['t'] = $_GET['t'];
            $List = $skillbM->getList($where);

            $this->yunset('rows', $List['list']);

        }

        $this->yuntpl(array('admin/admin_skill_bm'));
    }

    function del_action(){
        if($_GET['del']){
            $this->check_token();
            if (is_array($_GET['del'])){
                $delID = $_GET['del'];
            }else{
                $delID = intval($_GET['del']);
            }
        }else if($_POST['del']){
            $delID = $_POST['del'];
        }
        $skillM = $this->Model('skillbm');
        $res = $skillM->del($delID);
        if($res['errcode'] == 9){
            $this->layer_msg($res['msg'],$res['errcode'],$res['layertype'],$_SERVER['HTTP_REFERER'],2,1);
        }else{
            $this->layer_msg( $res['msg'],$res['errcode'],$res['layertype'],$_SERVER['HTTP_REFERER'],2,1);
        }
    }



}