<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class admin_special_job_controller extends adminCommon{
	function index_action(){
	    
		$sjM	=	$this->MODEL("specialjob");
        session_start();
        // 修改成功之后跳转到列表页使用session中记录的上次进入列表页的请求参数
        if (isset($_GET['remember_prev']) && $_GET['remember_prev']) {
            foreach ($_SESSION['prev_specialjoblist'] as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        // 本次请求的参数保存到session中，供下次修改页面返回列表页使用
        $_SESSION['prev_specialjoblist'] = $_GET;
		if(trim($_GET['keyword'])){
			$where['title']	=	array('like',trim($_GET['keyword']));
		}

		//分页链接
		$urlarr        	=   $_GET;
		$urlarr['page']	=	'{{page}}';
        // 删除记住上一次请求参数，防止生成的分页中含有该参数影响页面跳转
        if (isset($urlarr['remember_prev'])) {
            unset($urlarr['remember_prev']);
        }
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');

		//排序
		if($_GET['order']){

			$where['orderby'] = $_GET['t'].','.$_GET['order'];
			$urlarr['order']  =	$_GET['order'];
			$urlarr['t']	  =	$_GET['t'];

		}else{
			$where['orderby']  = 'id,desc';
		}

		//提取分页
		$pageM  =  $this  -> MODEL('page');
		$pages  =  $pageM -> pageList('special_job',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

		    $where['limit']  =  $pages['limit'];

		    $List  =  $sjM->getList($where,array('utype'=>'admin'));
			$this->yunset("rows",$List);
		}

		$this->yuntpl(array('admin/admin_special_job'));
	}

	function add_action(){
        // 搜索条件
        $CacheM		=	$this->MODEL('cache');
        $CacheList	=	$CacheM->GetCache(array('hy','com','uptime'));
        $this->yunset($CacheList);
		$sjM = $this->MODEL('specialjob');
        $row = $sjM->getInfo(array('id'=>$_GET['id']));
		
        $this->yunset('row', $row);
		$this->yuntpl(array('admin/admin_special_job_add'));
	}
	// 职位专题保存
	function save_action(){

	    $data['title'] = trim($_POST['title']);
	    $data['sub_title'] = trim($_POST['sub_title']);
	    $data['display'] = $_POST['display'];
        $data['tuiguang'] = $_POST['tuiguang'];

	    $keyword         = trim($_POST['keyword']);
	    $data['keyword'] = str_replace('，', ',', strip_tags($keyword));
		$type = isset($_POST['type']) && intval($_POST['type']) == 2 ? 2 : 1;
        $dataSearch = array();
        $data['type'] = $type;
		if ($type == 2) {//自动搜索职位

			$dataSearch['refresh_minutes'] = intval($_POST['refresh_minutes']);
            $dataSearch['job_class'] = $_POST['job_class'] ? intval($_POST['job_class']) : 0;
            $dataSearch['city_class'] = $_POST['city_class'] ? intval($_POST['city_class']) : 0;
            $dataSearch['welfare'] = $_POST['welfare'] ? pylode(',', $_POST['welfare']) : 0;
            $dataSearch['edu'] = $_POST['edu'] ? intval($_POST['edu']) : 0;
            $dataSearch['exp'] = $_POST['exp'] ? intval($_POST['exp']) : 0;
            $dataSearch['sex'] = $_POST['sex'] ? intval($_POST['sex']) : 0;
            $dataSearch['report'] = $_POST['report'] ? intval($_POST['report']) : 0;
            $dataSearch['hy'] = $_POST['hy'] ? pylode(',', $_POST['hy']) : 0;
            $dataSearch['uptime'] = $_POST['uptime'];
            $dataSearch['minsalary'] = $_POST['minsalary'] ? intval($_POST['minsalary']) : 0;
            $dataSearch['maxsalary'] = $_POST['maxsalary'] ? intval($_POST['maxsalary']) : 0;
            $dataSearch['is_zhiding'] = intval($_POST['is_zhiding']);
            $dataSearch['is_tuijian'] = intval($_POST['is_tuijian']);
            $dataSearch['is_jinji'] = intval($_POST['is_jinji']);
            $dataSearch['keyword'] = !empty($_POST['searchKeyword']) ? serialize($_POST['searchKeyword']) : '';
		}
		if(!empty($_FILES)){
		    $uploadM  =  $this->MODEL('upload');
		    if (!$_POST['pic'] && !$_FILES['bg']['tmp_name']) {
                $this->ACT_layer_msg('请上传PC背景图',8);
			}

            if (!$_POST['sjlist_pic'] && !$_FILES['pcsjlist']['tmp_name']) {
                $this->ACT_layer_msg('请上传占位图',8);
            }
            // 上传PC背景图
            if ($_FILES['bg']['tmp_name']) {
                $upArrBg    =  array(
                    'file'  	=>  $_FILES['bg'],
                    'dir'   	=>  'special',
                );
                $picBg  =  $uploadM->newUpload($upArrBg);//背景图
                if (!empty($picBg['msg'])){
                    $this->ACT_layer_msg($picBg['msg'],8);
                }
                if (!empty($picBg['picurl'])){
                    $data['pic']  =  $picBg['picurl'];
                }
			}
			// 上传PC职位列表占位图
			if ($_FILES['pclist']['tmp_name']) {
                $upArrPclist    =  array(
                    'file'  	=>  $_FILES['pclist'],
                    'dir'   	=>  'special',
                );
                $pclist  =  $uploadM->newUpload($upArrPclist);//wap背景图
                if (!empty($pclist['msg'])){
                    $this->ACT_layer_msg($pclist['msg'],8);
                }
                if (!empty($pclist['picurl'])){
                    $data['list_pic']  =  $pclist['picurl'];
                }
			}
            // 上传PC专题列表占位图
			if ($_FILES['pcsjlist']['tmp_name']) {
                $upArrPclist    =  array(
                    'file'  	=>  $_FILES['pcsjlist'],
                    'dir'   	=>  'special',
                );
                $pcsjlist  =  $uploadM->newUpload($upArrPclist);//wap背景图
                if (!empty($pcsjlist['msg'])){
                    $this->ACT_layer_msg($pcsjlist['msg'],8);
                }
                if (!empty($pcsjlist['picurl'])){
                    $data['sjlist_pic']  =  $pcsjlist['picurl'];
                }
			}
	 	}
	 	$sjM	=	$this->MODEL('specialjob');
		$backurl = 'index.php?m=admin_special_job';
		$data['data_search'] = $dataSearch;
		if(empty($_POST['id'])){
		    $nid	=	$sjM->addInfo($data);
	        $name	=	'职位专题（ID：'.$nid.'）添加';
	    }else{
            $backurl .= '&remember_prev=1';// 修改页面才需要记住列表请求参数
	        $nid	=	$sjM->upInfo(array('id'=>$_POST['id']),$data);
	        $name	=	'职位专题（ID：'.$_POST['id'].'）更新';
	    }
		$nid?$this->ACT_layer_msg($name.'成功！',9,$backurl,2,1):$this->ACT_layer_msg($name.'失败！',8,$backurl);
	}
	// 职位专题下职位列表
	function job_list_action(){
		$sjM	=	$this->MODEL('specialjob');
		$where['sid']	=	(int)$_GET['id'];
        $special = $sjM->getInfo(array('id'=>(int)$_GET['id']),array('field'=>'`id`,`title`, `type`'));
        if ($special['type'] == 1) {
            if (!empty($_GET['keyword'])){
                $keyword = trim($_GET['keyword']);
                $jobM    = $this->MODEL('job');
                $joblist = $jobM -> getListId(array('name'=>array('like',$keyword)), array('field'=>'id'));
                if (!empty($joblist)){
                    $jids = array();
                    foreach ($joblist as $v){
                        $jids[] = $v['id'];
                    }
                    $where['jobid'] = array('in', pylode(',', $jids));
                }
            }
            //排序
            $where['orderby'] =	array('ctime,desc');
            // 分页链接
            $urlarr = $_GET;
            $urlarr['page']	= '{{page}}';
            $urlarr['c'] = $_GET['c'];
            $urlarr['id'] =	$_GET['id'];
            $pageurl = Url($_GET['m'],$urlarr,'admin');
            // 提取分页
            $pageM = $this->MODEL('page');
            $pages = $pageM->pageList('special_job_list',$where,$pageurl,$_GET['page']);
            // 分页数大于0的情况下 执行列表查询
            if($pages['total'] > 0){
                $where['limit'] = $pages['limit'];
                $List =	$sjM->getjobList($where,array('utype'=>'admin'));
                $this->yunset("rows",$List);
            }
        } else {
            $isManual = isset($_GET['is_manual']) && $_GET['is_manual'] == 1 ? 1 : 0;
            if ($isManual == 1) {// 手动添加的职位列表
                $jobids = $special['manual_jobids'];
            } else {// 自动添加职位
                $jobids = $special['jobids'];
                if ($special['next_time'] <= time()) {// 自动刷新职位
                    $jobids = $sjM->autoRefreshSpecialJobs($special);
                }
            }
			$listwhere['id'] = array('in', $jobids);
            //排序
            $listwhere['orderby'] =	array('id,desc');
            // 分页链接
            $urlarr = $_GET;
            $urlarr['page']	= '{{page}}';
            $urlarr['c'] = $_GET['c'];
            $urlarr['id'] =	$_GET['id'];
            $pageurl = Url($_GET['m'],$urlarr,'admin');
            // 提取分页
            $pageM = $this->MODEL('page');
            $pages = $pageM->pageList('company_job',$listwhere,$pageurl,$_GET['page']);
            // 分页数大于0的情况下 执行列表查询
            if($pages['total'] > 0){
                $listwhere['limit'] = $pages['limit'];
                $List =	$sjM->getjobList($listwhere,array('utype'=>'admin', 'auto_add'=>1));
                $this->yunset("rows",$List);
            }
        }
		$this->yunset("special",$special);
		$this->yuntpl(array('admin/admin_special_job_list'));
	}
	//  选择参加的职位
	function job_join_action(){
	    //实例化职位类
	    $JobM  = $this->MODEL('job');
	    
	    $where['state']		=  1;
	    $where['status']	=  0;
	    $where['r_status']	=  1;
	    //搜索条件
	    if(!empty($_GET['addtime'])){
	        
	        $addtime = explode('~', $_GET['addtime']);
	        
	        $where['PHPYUNBTWSTART_A']	=	'';
	        $where['sdate'][]			=	array('>',strtotime(trim($addtime[0])));
	        $where['sdate'][]			=	array('<',strtotime(trim($addtime[1])));
	        $where['PHPYUNBTWEND_A']	=	'';
	        
	        $urlarr['addtime']  =  $_GET['addtime'];
	    }
	    if(!empty($_GET['uptime'])){
	        
	        $uptime = explode('~', $_GET['uptime']);
	        
	        $where['PHPYUNBTWSTART_A']	=	'';
	        $where['lastupdate'][]		=	array('>',strtotime(trim($uptime[0])));
	        $where['lastupdate'][]		=	array('<',strtotime(trim($uptime[1])));
	        $where['PHPYUNBTWEND_A']	=	'';
	        
	        $urlarr['uptime']  =  $_GET['uptime'];
	    }
	    
	    if($_GET['edu']){
            $where['edu']   =   $_GET['edu'];
	        $urlarr['edu']	=   $_GET['edu'];
	    }
	    
	    if($_GET['exp']){
            $where['exp']   =   $_GET['exp'];
	        $urlarr['exp']	=   $_GET['exp'];
	    }
	    
	    if($_GET['job_class']){
	        
	        $where['PHPYUNBTWSTARTA'] = '';
	        $where['job1']	        =	array('findin', $_GET['job_class']);
	        $where['job1_son']	    =	array('findin', $_GET['job_class'], 'OR');
	        $where['job_post']	    =	array('findin', $_GET['job_class'], 'OR');
	        $where['PHPYUNBTWENDa']  = '';
	        
	        $urlarr['job_class']	=	$_GET['job_class'];
	    }
	    if($_GET['city_class']){
	        
	        $where['PHPYUNBTWSTARTB'] = '';
	        $where['provinceid']	=	array('findin', $_GET['city_class']);
	        $where['cityid']	    =	array('findin', $_GET['city_class'], 'OR');
	        $where['three_cityid']	=	array('findin', $_GET['city_class'], 'OR');
	        $where['PHPYUNBTWENDB']  = '';
	        
	        $urlarr['city_class']	=	$_GET['city_class'];
	    }
	    
	    if($_GET['keyword']){
	        
	        if ($_GET['type']=='1'){
	            
	            $where['com_name']  =	array('like',trim($_GET['keyword']));
	            
	        }elseif ($_GET['type']=='2'){
	            
	            $where['name']      =	array('like',trim($_GET['keyword']));
	        } elseif ($_GET['type']=='3') {
                $where['welfare']   =	array('like',trim($_GET['keyword']));
            }

	        $urlarr['type']			=	$_GET['type'];
	        
	        $urlarr['keyword']		=	$_GET['keyword'];
	    }
	    //分页链接
	    $urlarr        	=   $_GET;
	    $urlarr['page']	=	'{{page}}';
	    
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('company_job',$where,$pageurl,$_GET['page']);
	    
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        //limit order 只有在列表查询时才需要
	        if($_GET['order']){
	            if ($_GET['t'] == 'add'){
	                $where['orderby']	=	'sdate,'.$_GET['order'];
	            }elseif ($_GET['t'] == 'up'){
	                $where['orderby']	=	'lastupdate,'.$_GET['order'];
	            }
	            $urlarr['order']		=	$_GET['order'];
	            $urlarr['t']			=	$_GET['t'];
	            
	        }else{
	            
	            $where['orderby']		=	'lastupdate,desc';
	            
	        }
	        
	        $where['limit']				=	$pages['limit'];
	        
	        $ListJob    =   $JobM -> getList($where,array('utype'=>'admin','cache'=>'1','isurl'=>'yes'));
            foreach ($ListJob['list'] as $k=>$v){
                $ListJob['list'][$k]['join'] = '<span style="color:red">未参加</span>';
            }
	        $CacheList	=	$ListJob['cache'];
	        
	        $sjM    = $this->MODEL('specialjob');
            $special = $sjM->getInfo(array('id'=>(int)$_GET['sid']),array('field'=>'`id`, `type`'));
            if ($special['type'] == 1) {// 手动
                $joined = $sjM->getjobList(array('sid'=>$_GET['sid']), array('utype'=>'admin'));
                if (!empty($joined)){
                    foreach ($ListJob['list'] as $k=>$v){
                        foreach ($joined as $val){
                            if ($v['id'] == $val['jobid']){
                                $ListJob['list'][$k]['join'] = '<span style="color:green">已参加</span>';
                            }
                        }
                    }
                }
            } else {// 自动
                $jobids = $special['jobids'] ? explode(',', $special['jobids']) : array();
                $manualJobids = $special['manual_jobids'] ? explode(',', $special['manual_jobids']) : array();
                $joined = array_unique(array_merge($jobids, $manualJobids));
                foreach ($ListJob['list'] as $k=>$v){
                    foreach ($joined as $val){
                        if ($v['id'] == $val){
                            $ListJob['list'][$k]['join'] = '<span style="color:green">已参加</span>';
                        }
                    }
                }
            }

	        $setArr	=	array(
	            'rows'			=>	$ListJob['list'],
	            'cache'			=>	$CacheList,
	            'comdata'		=>	$CacheList['comdata'],
	            'comclass_name' =>	$CacheList['comclass_name'],
	            'job_name'		=>	$CacheList['job_name'],
	            'city_name'		=>	$CacheList['city_name']
	        );
	        $this -> yunset($setArr);
	        
	    }else{
	        $cacheM		=	$this -> MODEL('cache');
	        $CacheList	=	$cacheM -> GetCache(array('com', 'job', 'city'));
	        
	        $setArr	=	array(
	            'comdata'		=>	$CacheList['comdata'],
	            'comclass_name'=>	$CacheList['comclass_name'],
	            'job_name'		=>	$CacheList['job_name'],
	            'city_name'		=>	$CacheList['city_name']
	        );
	        $this -> yunset($setArr);
	    }
	    
	    $this->yuntpl(array('admin/admin_special_job_join'));
	}
	// 职位专题添加职位
	function addjob_action(){
	    
	    $sjM = $this->MODEL('specialjob');
	    $sjM->addJob($_POST);
	    
	    $this->ACT_layer_msg('职位专题添加职位成功', 9, 1, 2, 1);
	}
	// 删除参加的职位
	function deljob_action(){
	    
	    $this->check_token();
		$sjM = $this->MODEL('specialjob');

	    if($_GET['del']||$_GET['id']||$_GET['sid']){

    		if(is_array($_GET['del'])){
    			$layer_type	=	1;
				$del		=	pylode(',',$_GET['del']);
	    	}else{
	    		$layer_type	=	0;
	    		$del		=	$_GET['id'];
	    	}
	    	$sjM->delJob(array('id'=>array('in',$del)), array('sid' => intval($_GET['sid'])));

			$this->layer_msg('职位专题参加的职位(ID:'.$del.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']);
    	}else{
			$this->layer_msg("请选择您要删除的信息！",8,1);
    	}
	}
	// 删除职位专题
	function del_action(){
	    
	    $this->check_token();
	    $sjM = $this->MODEL('specialjob');

		$_GET['id']  =  (int)$_GET['id'];

		if($_GET['del']||$_GET['id']){
			if(is_array($_GET['del'])){
				$layer_type	=	1;
				$del		=	pylode(',',$_GET['del']);
			}else{
				$layer_type	=	0;
				$del		=	$_GET['id'];
			}
			$sjM->del(array('id'=>array('in',$del)));

			$this->layer_msg('职位专题(ID:'.$del.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg('请选择您要删除的信息！',8,1);
		}
	}

	/*
	 * 开启或关闭职位专题
	 */
    function recommend_action(){
        $specialM	=	$this->MODEL('specialjob');
        $logM		=	$this->MODEL('log');
        if($_POST['type']=="rec_display"){
            $data['display']	=	$_POST['rec'];
            $where['id']		=	$_POST['id'];
            $nid = $specialM->upInfo($where,$data);
            $msg = $_POST['rec'] == 1 ? '开启' : '关闭';
            $logM->addAdminLog("职位专题(ID:".$_POST['id'].")" . $msg);
        }
        echo $nid?1:0;die;

    }

    /**
     * 职位专题 开启推广/关闭推广
     */
    function tuiguang_action(){
        $specialM	=	$this->MODEL('specialjob');
        $logM		=	$this->MODEL('log');
        if($_POST['type']=="rec_tuiguang"){
            $data['tuiguang']	=	$_POST['rec'];
            $where['id']		=	$_POST['id'];
            $nid = $specialM->upInfo($where,$data);
            $msg = $_POST['rec'] == 1 ? '开启推广' : '关闭推广';
            $logM->addAdminLog("职位专题(ID:".$_POST['id'].")" . $msg);
        }
        echo $nid?1:0;die;
    }
}
?>