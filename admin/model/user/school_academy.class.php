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
class school_academy_controller extends adminCommon{

    function index_action(){
        $schoolM		=   $this ->  MODEL('school');
		if($_POST['keyword']){
			$keytype	=   intval($_POST['type']);
			$keyword  	=   trim($_POST['keyword']);

			if($keytype==1){
				$where['schoolname'] 	= array('like',$keyword);
			}elseif($keytype==2){
				$where['school_phone'] 	= array('like',$keyword);
			}
		}
		if($_POST['change']){
			if($_POST['change']=='1'){
				$where['lastupdate']  	=  array('>=',strtotime('today'));
			}else{
				$where['lastupdate']	=  array('>=',strtotime('-'.intval($_POST['time']).' day'));
			}
		}
        if($_POST['order']){
            $where['orderby']	=	$_POST['t'].','.$_POST['order'];
        }else{
            $where['orderby']	=	array('lastupdate,desc','id,desc');
        }
        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('school_academy', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list =array();
        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];
            $List   =   $schoolM-> getSchoolAcademyList($where);
            $list = $List['list'];
        }


        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
	}
    public function  getCacheData_action(){
        $cacheM	 =	$this -> MODEL('cache');
        $cache	 =	$cacheM	-> GetCache(array('school','city','domain'));
        $cache['Dname'] = (object)$cache['Dname'];
        $search_list[]	=  array("param"=>"change","name"=>'更新时间',"value"=>array("1"=>"今天","3"=>"最近三天","7"=>"最近七天","15"=>"最近半月","30"=>"最近一个月"));
        $this->render_json(0, 'ok', compact('cache','search_list'));
    }

    function saveacademy_action(){ 

      	$SchoolM  =   $this -> MODEL('school');

      	$id       =   intval($_POST['id']);
      	if($_FILES['file']['tmp_name']!=''){
       	 	// pc端上传
       	 	$upArr    =  array(
          		'file'  =>  $_FILES['file'],
          		'dir'   =>  'school'
            );

            $uploadM  =  $this->MODEL('upload');
            $pic      =  $uploadM->newUpload($upArr);

            if (!empty($pic['msg'])){
                $this->render_json(1, $pic['msg']);
            }elseif (!empty($pic['picurl'])){
                $_POST['photo']         =   $pic['picurl'];
            }
        }

        if ($_POST['id']){
            $nid = $SchoolM->upSchoolAcademy($id,$_POST);
            $msg = '修改';
        }else{
            $nid=$SchoolM->addSchoolAcademy($_POST);
            $msg = '添加';
        }
        if ($nid){
            $nid = $_POST['id'] ? $_POST['id'] : $nid;
            $this->admin_json(0, "院校(ID:".$nid.")".$msg."成功！");
        }else{
            $nid = $_POST['id'] ? $_POST['id'] : $nid;
            $this->admin_json(1, "院校(ID:".$nid.")".$msg."失败！");
        }
    }
    
    function del_action(){
        $SchoolM	=	$this -> Model('school');
      
        $delID	=	$_POST['del'];
      
        $return	=	$SchoolM -> delSchoolAcademy($delID);
        $this->admin_json($return['errcode']==9?0:1, $return['msg']);
    }

     /**
	 * @desc  店铺招聘分站  分站设置
	 */
	function checksitedid_action(){
	    
	    $id		 =	trim($_POST['uid']);
	    $did		 =	intval($_POST['did']);
	    
	    if(empty($id)){
            $this->render_json(1, '参数不全请重试');
	    }
	    
	    $ids		 =	@explode(',',$_POST['uid']);
	    $id 		 =	pylode(',',$ids);
	    
	    if(empty($id)){
            $this->render_json(1, '请正确选择需分配院校');
	    }
	    
	    $siteM       =  $this->MODEL('site');
	    
	    $didData	 =    array('did' => $did);
	   
	    $siteM -> updDid(array('school_academy'),array('id'=>array('in', $id)),$didData);
        $this->admin_json(0, '院校分配站点成功');
	}
    function selsite_action(){

        $result = array();

        if ($_POST['keyword'] == '主站' || $_POST['keyword'] == '总站') {
            $result['id'] = 0;
            $result['webname'] = $_POST['keyword'];
            $this->render_json(0, 'ok', compact('result'));
        } else {
            $siteM = $this->MODEL('site');
            $where['title']	= array('like',$_POST['keyword']);
            $Site = $siteM->getList($where,array('field'=>'`id`,`title`'));
            if(is_array($Site) && !empty($Site)){
                foreach($Site as $value){
                    $result[] = array('id'=>$value['id'],'webname'=>$value['title']);
                }
                $this->render_json(0, 'ok', compact('result'));
            }else{
                $this->render_json(1, '暂无信息');
            }
        }

    }
   
}
?>