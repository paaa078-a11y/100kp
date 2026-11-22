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
class admin_ltset_controller extends adminCommon{
	/**
	 * 会员-猎头- 猎头设置
	 * 猎头设置
	 * 2019-06-03 hjy
	 */
    public function index_action()
    {
        $ratingM        =   $this->MODEL('rating');
        $rating_list    =   $ratingM->getList(array('category' => 2, 'orderby' => 'sort, asc'));
        $lt_single_can  =   $this->config['lt_single_can']?@explode(',', $this->config['lt_single_can']):array();
        $lt_rating_add  =   @explode(',', $this->config['lt_rating_add']);

        $config = array(
            'lt_status'=>$this->config['lt_status'],
            'lt_cert_status'=>$this->config['lt_cert_status'],
            'lt_job_status'=>$this->config['lt_job_status'],
            'lt_logo_status'=>$this->config['lt_logo_status'],
            'lt_enforce_licensecert'=>$this->config['lt_enforce_licensecert'],
            'lt_enforce_emailcert'=>$this->config['lt_enforce_emailcert'],
            'lt_enforce_mobilecert'=>$this->config['lt_enforce_mobilecert'],
            'lt_enforce_info'=>$this->config['lt_enforce_info'],
            'sy_chat_name'=>$this->config['sy_chat_name'],
            'lt_rec_rebates'=>$this->config['lt_rec_rebates'],
            'lt_rebates_name'=>$this->config['lt_rebates_name'],
            'lt_rating'=>$this->config['lt_rating'],
            'lt_vip_done'=>$this->config['lt_vip_done'],
        );

        $this->render_json(0,'',array('lt_rating_add'=>$lt_rating_add,'config'=>$config,
            'lt_single_can'=>$lt_single_can,'lt_rows'=>$rating_list));
    }


    public function getLogo_action(){
        $sy_lt_icon = $this->config['sy_ossurl']."/".$this->config['sy_lt_icon'];
        $this->render_json(0,'',['sy_lt_icon'=>$sy_lt_icon]);
    }
	/**
	 * 会员-猎头- 猎头设置
	 * 头像设置
	 * 2019-06-03 hjy
	 */

	public function logo_action(){
			

			$this -> web_config();
            $this->render_json(0,"会员头像配置设置成功！");
	}
	
	/**
	 * 会员-猎头- 猎头设置
	 * 保存设置
	 * 2019-06-03 hjy
	 */
	public function save_action(){
	    $prefix = '猎头设置';
	    isset($_POST['integral_ltcert']) && $prefix = '猎头积分';


		$configM					=	$this -> MODEL('config');
		$postData					=	$this -> post_trim($_POST);

		if(isset($postData['lt_single_can'])){
			$postData['lt_single_can'] = $postData['lt_single_can'] ? @implode(',', $postData['lt_single_can']) : '';
		}else{
			$postData['lt_single_can'] = '';
		}

		$configM -> setConfig($postData);

		$this -> web_config();
        $this->render_json(0,$prefix . "配置修改成功！");
	}

}
?>