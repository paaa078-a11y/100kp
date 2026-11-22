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
class supply_task_controller extends adminCommon{

    /**
     * @desc 后台供发布求任务 -- 浏览记录
     */

    function index_action(){
        $gqdemandM    			        =  		     $this -> MODEL('gqdemand');
         //查询状态
        if($_POST['status']){
		    $status                     =            intval($_POST['status']);
		    if($status == 3){
		        $where['status']      =            '0';
		    }elseif($status == 2){
		       $where['status']       =             '2';
		    }elseif($status==1){
                $where['status']      =             '1';
		    }
        }
        //招聘状态
        if($_POST['state']){
		    $state                      =               intval($_POST['state']);
		    if($state    ==      1){
		        $where['etime']         =               array('>',time());
            }else{
		        $where['etime']        =               array('<',time());
            }
		}
        //查询时间
        if ($_POST['ctime']) {
	        $ctime                      =               intval($_POST['ctime']);
	        if($ctime == 1){
                $where['ctime']         =               array('>',  strtotime('today'));
	        }else {
	            $where['ctime']         =               array('>', strtotime('-'.$ctime.' day'));
	        }
        }
        //根据条件查询
        $typeStr                        =               intval($_POST['type']);
        $keywordStr                     =               trim($_POST['keyword']);
        if (!empty($keywordStr)) {
            if ($typeStr == 2) {
                $where['link_man']       =              array('like',$keywordStr);
            } else{
                $where['name']           =              array('like',$keywordStr);
            }
        }

        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('gq_task', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list = array();
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            //limit order 只有在列表查询时才需要
            if($_POST['order']){
                $where['orderby']		=	            $_POST['t'].','.$_POST['order'];
            }else{
                $where['orderby']		=	            array('status,asc','id,desc');   
            }
            $where['limit']				=	            $pages['limit'];
            $list                     =               $gqdemandM->getGqtaskList($where);
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }
    public function  getSearchData_action(){
        $search_list[]              =           array('param' => 'status',   'name'=>'审核状态', 'value' =>  array('3'=>'待审核','1'=>'已审核','2'=>'未通过'));
        $search_list[]              =           array('param' => 'state',  'name'=>'招聘状态', 'value' =>  array('1'=>'招聘中','2'=>'已过期'));
        $search_list[]              =           array('param' => 'ctime',   'name'=>'发布时间', 'value' =>  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
        $this->render_json(0, 'ok', compact('search_list'));
    }
    /**
     * @desc  删除供求发布记录
     */
    function del_action(){
        
        $gqdemandM    			        =  		        $this -> MODEL('gqdemand');
        $id                         =               $_POST['del'];
        $return                            =               $gqdemandM -> deltask($id,array('utype'=>'admin'));
        $this->render_json($return['errcode']==9?0:1, $return['msg']);
    }
  
    //浏览
    public function info_action(){

        $gqdemandM    	                =  		        $this -> MODEL('gqdemand');
        if($_POST['id']){
            $id                         =               intval($_POST['id']);
            $where['id']                =               $id;
            $info                       =               $gqdemandM   ->  getGqtaskInfo($where, array('type' =>1));
            $time                       =               time();
        }

        $this->render_json(0, 'ok',compact('info', 'time'));

    }
    //审核说明  type=>1:表示不查询里面联系表信息    type     =>  null   表示查询联系表信息
    public function  getStatusBody_action(){

        $gqdemandM    	                =  		        $this -> MODEL('gqdemand');
        $id                             =               intval($_POST['id']);
        $where['id']                    =               $id;
        $tinfo                          =               $gqdemandM-> getGqtaskInfo($where,array('field'=>'`statusbody`','type'=>1));
        $statusbody = trim($tinfo['statusbody']);
        $this->render_json(0, 'ok', compact('statusbody'));
    }

    //审核任务
    public function status_action()
	{
        $gqdemandM	=	$this -> MODEL('gqdemand');
        $id			= $_POST['id'];
        if($_POST['status']  ==""){
            $this->render_json(1, '请选择审核状态');
        }
        if($id){
            $updata	=	array(
                'status'	=>	$_POST['status'],
                'statusbody'=>	$_POST['statusbody']
            );
            $return	=	$gqdemandM -> upGqtaskStatus($id,$updata);
            $this->admin_json($return['errcode']==9?0:1, $return['msg']);
        }else{
            $this->render_json(1, '非法操作');
        }
    }

    //修改保存
    public function savetask_action(){
        $gqdemandM    	                =  		         $this -> MODEL('gqdemand');
        if($_POST['update']){
		    $id                         =              intval($_POST['id']);
		    if (!empty($id)) {
                $data                   =               array(
                    'name'              =>              $_POST['name'],
                    'salary'            =>              $_POST['salary'],
                    'edate'             =>              $_POST['edate'],
                    'etime'             =>              strtotime($_POST['etime']),
                    'content'           =>              str_replace(array('&amp;','background-color:#ffffff','background-color:#fff','white-space:nowrap;'),array('&','background-color:','background-color:','white-space:'),$_POST['content']),
                    'link_man'          =>              $_POST['link_man'],
                    'link_moblie'       =>              $_POST['link_moblie'],
                    'lastupdate'        =>              time(),
                    'hits'              =>              $_POST['hits']
                );
                $where['id']            =               $id;
                $return                 =               $gqdemandM -> upaddGqtask($where, $data,1);

                $this->admin_json($return['errcode']==9?0:1, $return['msg']);
		    }else{
                $this->render_json(1, '非法操作');
            }
        }
    }

    /**
	 * @desc 获取项目发布任务数量
	 */
	function gqtaskNum_action(){

        $MsgNum             =           $this->MODEL('msgNum');
        
        $data =  $MsgNum->taskNum();
        $this->render_json(0, 'ok', json_decode($data));
	}
    
}
?>