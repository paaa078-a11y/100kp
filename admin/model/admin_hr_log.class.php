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
class admin_hr_log_controller extends adminCommon{
    
	function index_action(){
		
	    if (trim($_GET['keyword'])) {
	        
	        $companyM = $this->MODEL('company');
	        $com = $companyM->getChCompanyList(array('name'=>array('like', trim($_GET['keyword']))), array('field'=>'uid'));
	        if (!empty($com)){
	            $uids = array();
	            foreach ($com as $v){
	                $uids[] = $v['uid'];
	            }
	            $where['uid'] = array('in', pylode(',', $uids));
	        }else{
	            $where['uid'] = '';
	        }
	        $urlarr['keyword']  =   $_GET['keyword'];
	    }
	    
	    $urlarr['c']    =   $_GET['c'];
	    $urlarr         =   $_GET;
	    $urlarr['page'] =   '{{page}}';
	    
	    $pageurl        =   Url($_GET['m'], $urlarr, 'admin');
	    $pageM          =   $this->MODEL('page');
	    $pages          =   $pageM->pageList('hr_log', $where, $pageurl, $_GET['page']);
	    
	    if ($pages['total'] > 0) {
	        
	        $where['limit']     =   $pages['limit'];
	        $hrM                =   $this->MODEL('hrlog');
	        $rows               =   $hrM->getHrLogList($where);
	        $this->yunset(array('rows' => $rows));
	    }
	    
		$this->yuntpl(array('admin/admin_hr_log'));
	}
	function set_action(){

		if($_POST['submit']){

			if ($_FILES['yearreport_pic']['tmp_name']) {

	            $upArr  =   array(
	                'file'  =>  $_FILES['yearreport_pic'],
	                'dir'   =>  'whb'
	            );

	            $uploadM    =   $this->MODEL('upload');

	            $pic        =   $uploadM->newUpload($upArr);

	            if (!empty($pic['msg'])) {

	                $this->ACT_layer_msg($pic['msg'], 8);
	            } elseif (!empty($pic['picurl'])) {

	                $configData['sy_yearreport_pic']   =   $pic['picurl'];
	            }
	        }
	        if ($_FILES['yearreport_tip']['tmp_name']) {

	            $upArr  =   array(
	                'file'  =>  $_FILES['yearreport_tip'],
	                'dir'   =>  'whb'
	            );

	            $uploadM    =   $this->MODEL('upload');

	            $pic        =   $uploadM->newUpload($upArr);

	            if (!empty($pic['msg'])) {

	                $this->ACT_layer_msg($pic['msg'], 8);
	            } elseif (!empty($pic['picurl'])) {

	                $configData['sy_yearreport_tip']   =   $pic['picurl'];
	            }
	        }

	        $configM    =   $this->MODEL('config');

            $configData['sy_yearreport_isopen'] =   $_POST['wopen'] ? 1 : 0;
            $configData['sy_yearreport_ewmtype'] =   $_POST['sy_yearreport_ewmtype'];
            
            $configM->setConfig($configData);

            $this->web_config();

            $this->ACT_layer_msg("设置配置修改成功！", 9, 1, 2, 1);

        }else{

        	$this->yunset('sy_yearreport_tip',checkpic($this->config['sy_yearreport_tip']));

        	$this->yunset('sy_yearreport_pic',checkpic($this->config['sy_yearreport_pic']));

			$this->yuntpl(array('admin/admin_hr_log_set'));
		}

	}
	function setlog_action(){
	    
	    $limit = 1;
	    $lastyear = date('Y', strtotime('-1 year'));
	    $start = strtotime($lastyear. '-01-01');
	    $end   = strtotime($lastyear. '-12-31');
	    // 查询今年登录过的企业
	    $logM = $this->MODEL('log');
	    $frontW = array('usertype' => 2, 'groupby'=>'uid');
	    $frontW['PHPYUNBTWSTART'] = '';
	    $frontW['ctime'][] = array('>', $start);
	    $frontW['ctime'][] = array('<', $end);
	    $frontW['PHPYUNBTWEND'] = '';
	    
	    if(isset($_POST['page'])){//分页
	        $pagenav				=	($_POST['page']-1)*$limit;
	        $where['limit']			=	array($pagenav,$limit);
	    }else{
	        $where['limit']			=	$limit;
	    }
	    
	    $com = $logM->getLoginlogList(array_merge($frontW, $where), array('field'=>'uid'));
	    
	    $total = $logM->getLoginlogList($frontW, array('field'=>'uid'));
	    $remaind = count($total) - intval($_POST['page']);
	    
	    $hrM = $this->MODEL('hrlog');
	    
	    foreach ($com as $v){
	        $hrM->sethrlog($v['uid']);
	    }
	    if ($remaind > 0){
	        echo json_encode(array('error'=>1, 'msg'=>'数据生成中，还剩余'.$remaind.'条', 'page'=>$_POST['page'] + 1));
	    }else{
	        echo json_encode(array('error'=>0, 'msg'=>'数据已全部生成'));
	    }
	}
	function edit_action(){
	    
	    $hrM = $this->MODEL('hrlog');
	    $row = $hrM->gethrlog(array('id'=>$_GET['id']));
	    $this->yunset('row', $row);
	    
	    $this->yuntpl(array('admin/admin_hr_log_edit'));
	}
	function editsave_action(){
	    
	    $id = $_POST['id'];
	    unset($_POST['id']);
	    $hrM = $this->MODEL('hrlog');
	    if (!empty($_POST['lastwork'])){
	        $_POST['lastwork'] = strtotime($_POST['lastwork']);
	    }
	    $hrM->uphrlog(array('id'=>$id), $_POST);
	    
	    echo json_encode(array('error'=>0, 'msg'=>'修改成功'));
	}

    function datashowset_action(){
        if($_POST['submit']){
            $configM = $this->MODEL('config');
            $configData['sy_datashow_title'] = trim($_POST['sy_datashow_title']);
            $configData['sy_datashowhy_base'] = intval($_POST['sy_datashowhy_base']);
            $configData['sy_datashowreg_base'] = intval($_POST['sy_datashowreg_base']);
            $configData['sy_datashowlogin_base'] = intval($_POST['sy_datashowlogin_base']);
            $configData['sy_datashowjob_base'] = intval($_POST['sy_datashowjob_base']);
            $configData['sy_datashow_city_lev'] = $_POST['sy_datashow_city_lev'] ? intval($_POST['sy_datashow_city_lev']) : 2;
            $configM->setConfig($configData);
            $this->web_config();
            $this->ACT_layer_msg("设置配置修改成功！", 9, 1, 2, 1);
        }else{
            $this->yunset('url', $this->config['sy_weburl'] . '/wap/index.php?c=ajax&a=dataShowIndex');
            $this->yuntpl(array('admin/admin_datashow_set'));
        }
    }
}

?>