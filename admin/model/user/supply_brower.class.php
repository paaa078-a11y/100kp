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
class supply_brower_controller extends adminCommon{

    /**
     * @desc 后台供求任务 -- 浏览记录
     */
    function index_action(){
        $gqdemandM    			        =  		$this -> MODEL('gqdemand');
        //查询时间
        if ($_POST['ctime']) {
	        $ctime                      =       intval($_POST['ctime']);
	        if($ctime == 1){
                $where['ctime']         =       array('>',strtotime(date("Y-m-d 00:00:00")));
	        }else {
                $where['ctime']		    =	    array('>',strtotime('-'.$ctime.'day'));	            
	        }
        }
        //根据条件查询
        $typeStr                        =       intval($_POST['type']);
        $keywordStr                     =       trim($_POST['keyword']);
        if (!empty($keywordStr)) {
            if ($typeStr == 2) {
                $mwhere['name']          =       array('like',$keywordStr);
                $gqfbz                   =       $gqdemandM->getGqinfoList($mwhere,array('field'=>'`uid`','type'=>1));
                foreach($gqfbz as $v){
	                $guids[]	         =	    $v['uid'];
                }
                $where['gq_id']          =     array('in',pylode(',',$guids));
            } else{
                $mwhere['name']         =       array('like',$keywordStr);
                $task                   =       $gqdemandM->getGqtaskList($mwhere,array('field'=>'`id`','type'=>1));
                foreach($task as $v){
	                $taskuids[]	        =	    $v['id'];
                }
                $where['task_id']       =       array('in',pylode(',',$taskuids));
            }
        }
        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('gq_browse', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list = array();
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            //limit order 只有在列表查询时才需要
            if($_POST['order']){
                $where['orderby']		=	    $_POST['t'].','.$_POST['order'];
            }else{
                $where['orderby']		=	    array('id,desc');
            }
            $where['limit']				=	    $pages['limit'];
            $list                      =       $gqdemandM->gqbrowseList($where,array('field'=>'`uid`,`id`,`gq_id`,`ctime`,`task_id`'));
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
        
    }
    public function  getSearchData_action(){
        $search_list[]                  =       array('param'=>'ctime','name'=>'浏览时间','value'=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
        $this->render_json(0, 'ok', compact('search_list'));
    }
    /**
     * @desc  删除供求浏览记录
     */
    function del_action(){
        
        $gqdemandM    			    =  		$this -> MODEL('gqdemand');

        if(is_array($_POST['del'])){
            $id                     =       $_POST['del'];
        }else{
            $id                     =       intval($_POST['del']);
        }
        $return                        =       $gqdemandM -> delbrower($id,array('utype'=>'admin'));

        $this->render_json($return['errcode']==9?0:1, $return['msg']);
        
    }
    
}
?>