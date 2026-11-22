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
class supply_set_controller extends adminCommon{
    /**
     * 会员-供求-供求设置:供求设置
     */
	function index_action(){

        $config = array();

        $config['gq_status'] = $this->config['gq_status'] ? $this->config['gq_status'] : '1';
        $config['pic_maxsize'] = $this->config['pic_maxsize'] ? $this->config['pic_maxsize'] : 5;
        $config['gq_number'] = $this->config['gq_number'] == null? 0 : $this->config['gq_number'];
        $config['gq_task_link'] = $this->config['gq_task_link'];
        $config['gq_info_link'] = $this->config['gq_info_link'];
        $config['gq_fast_status'] = $this->config['gq_fast_status'];
        $config['gq_task_status'] = $this->config['gq_task_status'];
        $config['gq_pay_price'] = $this->config['gq_pay_price']==null? 0 : $this->config['gq_pay_price'];
        $config['gq_photo_status'] = $this->config['gq_photo_status'];
        $config['gq_refrsh_pay'] = $this->config['gq_refrsh_pay'] == null ? 0: $this->config['gq_refrsh_pay'];
        $config['gq_photo'] = $this->config['gq_photo'];
        $config['sy_ossurl'] = $this->config['sy_ossurl'];
        $this->render_json(0, 'ok', compact('config'));
	}
	  
	function save_action(){
        $uploadM    =   $this->MODEL('upload');
		if($_POST['config']){
		    $post 					    =  		array(
                'gq_status'             => 	 	$_POST['gq_status'],
				'gq_number'             => 	 	$_POST['gq_number'],//供求每天发布多少需求
				'gq_task_link'          =>  	$_POST['gq_task_link'],//联系方式是否公开（发布任务）
				'gq_info_link'          =>  	$_POST['gq_info_link'],//供求入住客户
				'gq_fast_status'        =>  	$_POST['gq_fast_status'],//供求用户审核
				'gq_task_status'        =>  	$_POST['gq_task_status'],//供求发布任务户审核
				'gq_pay_price'          =>  	$_POST['gq_pay_price'],//发布任务多少钱
				'gq_photo_status'       =>  	$_POST['gq_photo_status'],//上传头像审核
				'gq_refrsh_pay'     	=>  	$_POST['gq_refrsh_pay'],//刷新金额
			);
            if ($_FILES['logoFile']['tmp_name'] != '') {
                $upArr  =   array(

                    'file'  =>  $_FILES['logoFile'],
                    'dir'   =>  'logo'
                );
                $pic    =   $uploadM->newUpload($upArr);

                if (!empty($pic['msg'])) {

                    $this->render_json(1, $pic['msg']);
                } elseif (!empty($pic['picurl'])) {

                    $post['gq_photo'] = $pic['picurl'];
                }
            }
		    $configM  =  $this -> MODEL('config');
		    $configM -> setConfig($post);
		    $this -> web_config();
            $msg    =   '保存成功';
            $this->admin_json(0, $msg);
		}else{
            $msg    =   '保存失败';
            $this->admin_json(1, $msg);
        }

	}
	
}
?>