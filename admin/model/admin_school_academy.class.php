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
class admin_school_academy_controller extends adminCommon{
	function set_search(){
		$search_list[]	=	array("param"=>"change","name"=>'更新时间',"value"=>array("1"=>"今天","3"=>"最近三天","7"=>"最近七天","15"=>"最近半月","30"=>"最近一个月"));
		$this->yunset("search_list",$search_list);
	}
	function index_action(){

		$this->set_search();
		$schoolM		=   $this ->  MODEL('school');
		// 修改成功之后跳转到列表页使用session中记录的上次进入列表页的请求参数
        if (isset($_GET['remember_prev']) && $_GET['remember_prev']) {
            foreach ($_SESSION['prev_school_academy_list'] as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        // 本次请求的参数保存到session中，供下次修改页面返回列表页使用
        $_SESSION['prev_school_academy_list'] = $_GET;
		if($_GET['keyword']){
      
			$keytype	=   intval($_GET['type']);
			$keyword  	=   trim($_GET['keyword']);

			if($keytype==1){

				$where['schoolname'] 	= array('like',$keyword);

			}elseif($keytype==2){

				$where['school_phone'] 	= array('like',$keyword);

			}
			$urlarr['keytype']	=  $keytype;
			$urlarr['keyword']	=  $keyword;
		}
		if($_GET['change']){
			if($_GET['change']=='1'){
				
				$where['lastupdate']  	=  array('>=',strtotime('today'));
			}else{
				$where['lastupdate']	=  array('>=',strtotime('-'.intval($_GET['time']).' day'));
			}
			$urlarr['change']			=	$_GET['change'];
		}
		$urlarr        	=   $_GET;
		$urlarr['page'] = 	'{{page}}';
		// 删除记住上一次请求参数，防止生成的分页中含有该参数影响页面跳转
        if (isset($urlarr['remember_prev'])) {
            unset($urlarr['remember_prev']);
        }
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('school_academy',$where,$pageurl,$_GET['page']);
    
		if($pages['total']  > 0){
       //limit order 只有在列表查询时才需要
			if($_GET['order']){
				
				$where['orderby']	=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']	=	$_GET['order'];
				$urlarr['t']		=	$_GET['t'];
				
			}else{
				$where['orderby']	=	array('lastupdate,desc','id,desc');
			}
		}
		$where['limit']   =   $pages['limit'];
    
		$List   =   $schoolM-> getSchoolAcademyList($where,array('field'=>'`id`,`schoolname`,`provinceid`,`cityid`,`did`,`school_department`,`school_level`,`school_categty`,`schooltag`,`school_phone`,`lastupdate`,`photo`'));
		$this   ->  yunset(array('rows'=>$List['list']));
    
     	$cacheM					         =	$this -> MODEL('cache');
    
	 	$domain					         =	$cacheM	-> GetCache('domain');
	 	$this -> yunset('Dname', $domain['Dname']);
	
		$this->yuntpl(array('admin/school_academy_list'));
	}
    function add_action(){
    
		$schoolM	=	$this ->MODEL('school');
		$id       	=	intval($_GET['id']);
        $return   	=	$schoolM->getSchoolAcademyInfo(array('id'=>$id),array('cache'=>1));
        $this->yunset('show',$return['info']); 
        $this->yunset($return['cache']);
		
        $this->yuntpl(array('admin/school_add'));
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

          $this -> ACT_layer_msg($pic['msg'],8);

        }elseif (!empty($pic['picurl'])){

          $_POST['photo']         =   $pic['picurl'];
        }
      }
      $backurl = 'index.php?m=admin_school_academy';
      if ($_POST['id']){
          $backurl .= '&remember_prev=1';// 修改页面才需要记住列表请求参数
          $nid = $SchoolM->upSchoolAcademy($id,$_POST);
            
          $msg = '修改'; 
      }else{

          $nid=$SchoolM->addSchoolAcademy($_POST);
            
          $msg = '添加'; 
      }
      if ($nid){
        
          $nid = $_POST['id'] ? $_POST['id'] : $nid;
            
          $this->ACT_layer_msg("院校(ID:".$nid.")".$msg."成功！",9,$backurl,2,1);
            
      }else{
        
          $nid = $_POST['id'] ? $_POST['id'] : $nid;
            
          $this->ACT_layer_msg("院校(ID:".$nid.")".$msg."失败！",8,$backurl,2,1);
            
        }
    }
    
    function del_action(){
      
      $this->check_token();
      
      $SchoolM	=	$this -> Model('school');
      
      $delID	=	is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];
      
      $return	=	$SchoolM -> delSchoolAcademy($delID);
      
       if($_GET['id']){
          $this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
      }else{
          $this->layer_msg($return['msg'],$return['errcode'],1,$_SERVER['HTTP_REFERER']);
      }
    }
     function list_action(){
        
      $school   =   $this ->  MODEL('school');  
        
      $id       =   intval($_GET['id']);
        
      $return   =   $school ->   getSchoolAcademyInfo(array('id'=>$id));

      $this->yunset('show',$return['info']);
      
      $this->yuntpl(array('admin/admin_school_list'));
    }
     /**
	 * @desc  店铺招聘分站  分站设置
	 */
	function checksitedid_action(){
	    
	    $id		 =	trim($_POST['uid']);
	    $did		 =	intval($_POST['did']);
	    
	    if(empty($id)){
	        
	        $this -> ACT_layer_msg('参数不全请重试！', 8);
	    }
	    
	    $ids		 =	@explode(',',$_POST['uid']);
	    $id 		 =	pylode(',',$ids);
	    
	    if(empty($id)){
	        
	        $this -> ACT_layer_msg('请正确选择需分配院校！', 8);
	    }
	    
	    $siteM       =  $this->MODEL('site');
	    
	    $didData	 =    array('did' => $did);
	   
	    $siteM -> updDid(array('school_academy'),array('id'=>array('in', $id)),$didData);
	    
	    $this->ACT_layer_msg('院校管理(ID:'.$_POST['uid'].')分配站点成功！', 9, $_SERVER['HTTP_REFERER'],2,1);
	    
	}
   
   
}
?>