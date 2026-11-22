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
class skill_list_controller extends adminCommon{

     function set_search(){
        $search_list[]    =   array('param' => 'addtime','name'=>'发布时间', 'value' =>  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月','31'=>'当月'));
        $search_list[]    =   array('param' => 'last_update','name'=>'更新时间', 'value' =>  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月','31'=>'当月'));
        $this->yunset('search_list',$search_list);
     }

     function index_action(){

        $skillM					=	$this -> Model('skill');

        $this->set_search();
        if($_GET['keyword']){
            if ($_GET['type']=='1'){
                $where['title']  =	array('like',trim($_GET['keyword']));
            }
            $urlarr['type']			=	$_GET['type'];
            $urlarr['keyword']		=	$_GET['keyword'];
        }
        $month = get_this_month(time());
        if ($_GET['addtime']) {
            $addtime    =   intval($_GET['addtime']);
            if($addtime == 1){// 今天
                $where['addtime']     =   array('>',  strtotime('today'));
            }else if ($addtime < 31){// 近 $addtime 天
                $where['addtime']  =   array('>',  strtotime('-'.$addtime.' day'));
            }else if ($addtime == 31){// 当月
                $where['PHPYUNBTWSTART_C']    =   '';
                $where['addtime'][]  =   array('>=',  $month['start_month']);
                $where['addtime'][]  =   array('<=', $month['end_month']);
                $where['PHPYUNBTWEND_C']      =   '';
            }
            $urlarr['addtime']       =   $addtime;
        }

        if ($_GET['last_update']) {
            $last_update    =   intval($_GET['last_update']);
            if($last_update == 1){// 今天
                $where['last_update']     =   array('>',  strtotime('today'));
            }else if ($last_update < 31){// 近 $last_update 天
                $where['last_update']  =   array('>',  strtotime('-'.$last_update.' day'));
            }else if ($last_update == 31){// 当月
                $where['PHPYUNBTWSTART_C']    =   '';
                $where['last_update'][]  =   array('>=',  $month['start_month']);
                $where['last_update'][]  =   array('<=', $month['end_month']);
                $where['PHPYUNBTWEND_C']      =   '';
            }
            $urlarr['last_update']       =   $last_update;
        }

        $urlarr        	=   $_GET;
        $urlarr['page']	=	'{{page}}';
        $pageurl		=	Url($_GET['m'],$urlarr,'admin');
        $pageM			=	$this  -> MODEL('page');
        $pages			=	$pageM -> pageList('skill',$where,$pageurl,$_GET['page']);
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
            $List = $skillM->getList($where);
            $this->yunset('rows', $List['list']);

        }
        $this -> yuntpl(array('admin/admin_skill_list'));
    }

    public function skills_action(){

        $skillM  =  $this -> Model('skill');

        if($_GET['id']){
            $Info  =  $skillM -> getInfo(array('id'=>$_GET['id']));

            $this -> yunset('info', $Info);
            $this -> yunset('lasturl', $_SERVER['HTTP_REFERER']);
        }

        //课程类别
        $categoryM = $this->MODEL('skillcategory');
        $lessonSons = $categoryM->getSonByVariable('lesson');
        $lessonArr = array();
        foreach ($lessonSons as $v) {
            $lessonArr[] = array('id' => $v['id'], 'name' => $v['name']);
        }
        $this->yunset('lesson', $lessonArr);

        $this -> yuntpl(array('admin/admin_skill_add'));
    }

    public function addskill_action()
    {
        $postData  =  $this -> post_trim($_POST);
        $skillM  =  $this -> MODEL('skill');

        if($_FILES){
            $upData = [];
            foreach ($_FILES as $key => $value) {
                if ($value['tmp_name']) {
                    $upData[$key]['file'] = $_FILES[$key];
                    $upData[$key]['dir'] = 'skill';
                }
            }
            
            if ($upData) {
                $uploadM  =  $this->MODEL('upload');
                foreach ($upData as $key => $value) {
                    $upRes = $uploadM->newUpload($value);
                    if ($upRes && !empty($upRes['msg'])) {
                        $return['msg']  =  $upRes['msg'] ? $upRes['msg'] : $upRes['msg'];
                        $this -> ACT_layer_msg($return['msg'],8);
                    } else {
                        $postData[$key] = $upRes['picurl'];
                    }
                }
            }
        }
        if (CheckMobile($_POST['telphone']) == false) {

            $this->ACT_layer_msg("手机号码格式错误", 8);
        }
        $return  =  $skillM -> addskill($postData);
        $url = Url('skill_list', null, 'admin');
        if($return['errcode'] == 9 && !empty($return['data'])){
            $this -> ACT_layer_msg($return['msg'], 9, $url, 2, 1);
        }else{
            $this -> ACT_layer_msg($return['msg'], 8, $url);
        }
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
        $skillM = $this->MODEL('skill');
        $res = $skillM->del($delID);
        if($res['errcode'] == 9){
            $this->layer_msg($res['msg'],$res['errcode'],$res['layertype'],$_SERVER['HTTP_REFERER'],2,1);
        }else{
            $this->layer_msg( $res['msg'],$res['errcode'],$res['layertype'],$_SERVER['HTTP_REFERER'],2,1);
        }
    }
}
?>