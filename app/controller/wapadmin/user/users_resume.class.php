<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2022 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class users_resume_controller extends adminCommon
{
    function index_action()
    {
        if (!empty($_POST)) {

            $resumeM    =   $this->MODEL('resume');

            $where      =   array();
            $page       =   $_POST['page'];
            $limit      =   $_POST['limit'];

            if (trim($_POST['keyword'])) {

                $keywordStr                 =   trim($_POST['keyword']);
                $where['PHPYUNBTWSTART_A']  =   '';
                $where['name']              =   array('like', $keywordStr);
                $where['uname']             =   array('like', $keywordStr, 'or');
                $where['PHPYUNBTWEND_B']    =   '';
                $data['keyword']            =   $keywordStr;
            }
            if ($_POST['status']) {

                $status =   intval($_POST['status']);
                if ($status == 2) {

                    $where['r_status']  =   2;
                } else {

                    $where['state']     =   $status == 4 ? 0 : $status;
                }
            }

            if ($_POST['adtime']) {

                $adtime =   intval($_POST['adtime']);
                if ($adtime == 1) {

                    $where['ctime']     =   array('>', strtotime('today'));
                } else {

                    $where['ctime']     =   array('>', strtotime('-' . $adtime . ' day'));
                }
            }
            if ($_POST['service']) {

                $service    =   intval($_POST['service']);
                if ($service == 1) {

                    $where['top']       =   1;
                    $where['topdate']   =   array('>', time());
                } elseif ($service == 2) {

                    $where['rec_resume']=   1;
                }
            }

            if ($_POST['edu']) {

                $where['edu'] = $_POST['edu'];
            }

            if ($_POST['exp']) {

                $where['exp'] = $_POST['exp'];
            }
            if ($_POST['source']) {
                $where['source'] = intval($_POST['source']);
            }
            $where['orderby']   =   'lastupdate,desc';
            if ($page) {

                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =   array($pagenav, $limit);
            } else {

                $where['limit'] =   $limit;
            }
            $list   =   $resumeM->getList($where, array('cache' => 1, 'utype' => 'admin'));

            $data['list'] = count($list['list']) ? $list['list'] : [];
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $this->yuntpl(array('wapadmin/admin_resume'));
    }

	function statis_action(){
        include(CONFIG_PATH.'db.data.php');
        $source         =  $arr_data['source'];
        foreach ($source as $k=>$v){
            $sourcearr[] = array('id'=>$k,"name"=>$v);
        }
        $statusarr = array(array('id'=>1,'name'=>'已审核'),array('id'=>2,'name'=>'已锁定'),array('id'=>3,'name'=>'未通过'),array('id'=>4,'name'=>'未审核'));
        $adtimearr = array(array('id'=>1,'name'=>'今天'),array('id'=>3,'name'=>'最近三天'),array('id'=>7,'name'=>'最近七天'),array('id'=>15,'name'=>'最近半月'),array('id'=>30,'name'=>'最近一个月'));
        $typearr = array(array('id'=>1,'name'=>'置顶'),array('id'=>2,'name'=>'推荐'));
        $cacheM		=	$this -> MODEL('cache');
        $CacheList	=	$cacheM -> GetCache(array('user', 'job', 'city'));
        $data['userdata'] = $CacheList['userdata'];
        $data['userclass_name'] = $CacheList['userclass_name'];
        $data['source'] = $source;
        $data['sourcearr'] = $sourcearr;
        $data['statusarr'] = $statusarr;
        $data['adtimearr'] = $adtimearr;
        $data['typearr'] = $typearr;
        $labelarr = array();
        foreach ($CacheList['userdata']['user_label'] as $k=>$v){
            $labelarr[] = array('text'=>$CacheList['userclass_name'][$v],'value'=>$v);
        }
        $data['labelarr'] = $labelarr;
        header('content-type:application/json; charset=utf-8');
        echo json_encode($data);die;
    }

    /**
     * 会员-个人-简历管理: 简历备注
     */
    function label_action()
    {
        $resumeM    =   $this->MODEL('resume');
        $data       =   array(

            'id'        =>  $_POST['id'],
            'uid'       =>  $_POST['uid'],
            'label'     =>  $_POST['label'],
            'content'   =>  $_POST['content'],
            'auid'      =>  $_SESSION['wuid']
        );
        $return     =   $resumeM->label($data);
        $this->layer_msg($return['msg'], 9, 0, '', '', $return['errcode']);
    }

    //简历详情
    function show_action(){
        if (!empty($_POST)) {
            //查询数据
            include(CONFIG_PATH.'db.data.php');
            $source         =  $arr_data['source'];
            $resumeM = $this->MODEL('resume');
            if ($_POST['id']){
                $info     =  $resumeM->getInfoByEid(array('eid'=>$_POST['id']));

                //简历数据
                $return = $resumeM->getInfo(array('uid'=>$info['uid'],'eid'=>$info['id'],'tb'=>'all','needCache'=>1));
                $data = array(
                    'expect'          =>   $return['expect'],
                    'edu'             =>   $return['edu'],
                    'other'           =>   $return['other'],
                    'project'         =>   $return['project'],
                    'skill'           =>   $return['skill'],
                    'training'        =>   $return['training'],
                    'work'            =>   $return['work'],
                    'show'            =>   $return['show'],
                    'industry_index'  =>   $return['cache']['industry_index'],
                    'industry_name'   =>   $return['cache']['industry_name'],
                    'userdata'        =>   $return['cache']['userdata'],
                    'userclass_name'  =>   $return['cache']['userclass_name'],
                    'user_sex'        =>   $return['cache']['user_sex'],
                );
                $tparr = array();
                foreach ($return['cache']['userdata']['user_audit'] as $k=>$v){
                    $tparr[] = $return['cache']['userclass_name'][$v];
                }
                $data['tparr'] = $tparr;
                $data['info'] = $info;
                //面试通知数
                $JobM			=	$this -> MODEL('job');
                $yqnum		=	$JobM -> getYqmsNum(array('uid'=>$info['uid']));
                $data['yqnum'] = $yqnum;
                //投递职位数
                $sq_nums				=	$JobM->getSqJobNum(array('uid'=>$info['uid']));
                $data['sqnum'] = $sq_nums;
                //浏览记录
                $lookresumeM      =  $this -> MODEL('lookresume');
                $lknum = $lookresumeM->getLookNum(array('resume_id'=>$_POST['id']));
                $data['lknum'] = $lknum;

                $data['source'] = $source;
            }

            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);die;
        }
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $isOutcall = $appkey && $appsecret;
        $this->yunset('is_outcall', $isOutcall);// 是否配置外呼
        $this->yuntpl(array('wapadmin/admin_resume_show'));
    }

    //简历修改
    function edit_action(){
        if ($_POST){
            $ResumeM	=	$this->MODEL('resume');
            $eid	=	(int)$_POST['eid'];
            $data	=	$ResumeM->getInfo(array('eid'=>$eid,'uid'=>$_POST['uid'],'tb'=>'all','needCache'=>1));

            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);die;
        }
        $cacheM	=	$this->MODEL('cache');
        $cache	=	$cacheM -> GetCache(array('city','job'));

        $this -> yunset($cache);
        $this->yuntpl(array('wapadmin/admin_resume_edit'));
    }
    function editresumeson_action()
    {
        $ResumeM = $this->MODEL('resume');

        if ($_POST['table'] == 'desc') {

            $desc = $ResumeM->getResumeInfo(array('uid' => $_POST['uid']), array('field' => 'description,tag'));


            if ($desc['tag']) {
                $tag = @explode(',', $desc['tag']);
            }

            $desc['tag'] = $tag ? $tag : array();

            //系统的标签
            $cacheM = $this->MODEL('cache');

            $cacheList = $cacheM->GetCache('user');
            foreach ($cacheList['userdata']['user_tag'] as $v) {
                if (!in_array($cacheList['userclass_name'][$v], $tag) && $cacheList['userclass_name'][$v]) {
                    $usertag[] = $cacheList['userclass_name'][$v];
                }
            }
            $desc['usertag'] = $usertag ? $usertag : array();

            $data['list'] = $desc;

            include PLUS_PATH . "introduce.cache.php";

            if (!empty($introduce_index)) {
                $nid = $introduce_index[0];

                $info['name'] = $introduce_name[$nid];

                $info['id'] = $nid;

                $introduce_con = str_replace(array('&quot;', '&nbsp;', '<>'), array('', '', ''), $introduce_content[$nid]);
                $introduce_con = htmlspecialchars_decode($introduce_con);

                $info['content'] = $introduce_con;

            }
            $data['info'] = !empty($info) ? $info : array();

        } elseif ($_POST['table'] == 'show') {

            $data['list'] = $ResumeM->getResumeShowList(array('eid' => $_POST['eid'], 'uid' => $_POST['uid']));

        } else {

            $id = $_POST['id'];

            $table = 'resume_' . $_POST['table'];

            $row = $ResumeM->getFb($table, $id, $_POST['uid']);

            $data['list'] = !empty($row) ? $row : array();
        }
        header('content-type:application/json; charset=utf-8');
        echo json_encode($data);
        die;
    }
	function saveinfo_action(){
		$resumeM	=	$this->MODEL('resume');
		$uid   		=  intval($_POST['uid']);
	    $rData  	=  array(
	        'name'         => 	 $_POST['name'],
	        'sex'          =>  	$_POST['sex'],
	        'edu'          =>  	$_POST['edu'],
	        'living'       =>  	$_POST['living'],
	        'exp'          =>  	$_POST['exp'],
	        'birthday'     =>  	$_POST['birthday'],
	    );
		$resume 	= 	$resumeM->getResumeInfo(array('uid'=>$uid));
		if(!$resume['photo'] || ($resume['defphoto']==2 && $rData['sex']!=$resume['sex'])){
			$logo   =   $resumeM->deflogo($rData);
			if($logo!=''){
				$rData['photo'] 		= $logo;
				$rData['defphoto'] 		= 2;
				$rData['photo_status'] 	= 0;
			}
		}
	    $return   =  $resumeM -> upResumeInfo(array('uid'=>$uid),array('rData'=>$rData));
        $this -> layer_msg($return['msg'], 9,0,'','',$return['errcode']);
	}
	function saveexpect_action(){
		$resumeM	=	$this->MODEL('resume');
		if($_POST){
			$eid	=	intval($_POST['eid']);
	        $uid	=	intval($_POST['uid']);
			if($eid){
				$expectDate	=	array(
					'name'			=>	$_POST['name'],
					"hy"			=>	$_POST['hy'],
					"job_classid"	=>	$_POST['jobclassid'],
					"minsalary"		=>	$_POST['minsalary'],
					"maxsalary"		=>	$_POST['maxsalary'],
					"city_classid"	=>	$_POST['city_classid'],
					"report"		=>	$_POST['report'],
					"type"			=>	$_POST['type'],
					"jobstatus"		=>	$_POST['jobstatus'],
					"lastupdate"	=>	time(),
				);
			}
			$return   =  $resumeM -> upInfo(array('id'=>$eid), array('eData'=>$expectDate,'utype'=>'admin'));
            $this->render_json($return['errcode'], $return['msg']);
		}
	}
	function skill_action(){
		$resumeM    =   $this -> MODEL('resume');

        $id    		=   intval($_POST['id']);
        if ($_POST['preview']){

            $upArr   =  array(
                'dir'      =>  'user',
                'type'     =>  'skill',
                'base'     =>  $_POST['preview'],
            );
            $result  =  $this -> upload($upArr);
            if (!empty($result['msg'])){
                $data['msg']   =  $result['msg'];
            }elseif (!empty($result['picurl'])){
                $pictures  =  $result['picurl'];
            }
        }

        $postData	=	array(
            'uid'		=>	intval($_POST['uid']),
            'eid'		=>	intval($_POST['eid']),
            'name'		=>	$_POST['name'],
            'longtime'	=>	$_POST['longtime'],
            'ing'		=>	$_POST['ing'],
        );
        if(isset($pictures)){
            $postData['pic']	=	$pictures;
        }


        $return	 =	$resumeM -> addResumeSkill($postData,array('where'=>array('id'=>intval($_POST['id'])),'utype'=>'admin'));

        $this->render_json($return['errcode'], $return['msg']);
	}
	function work_action(){
		$resumeM    =   $this -> MODEL('resume');
		$id    		=   intval($_POST['id']);
		$postData	=	array(
			'uid'		=>	intval($_POST['uid']),
			'eid'		=>	intval($_POST['eid']),
			'name'		=>  $_POST['name'],
			'sdate'		=>	strtotime($_POST['sdate']),
	        'edate'		=>  strtotime($_POST['edate']),
			'title'		=>  $_POST['title'],
			'content'	=>	trim($_POST['content'])
		);
        $return	 =	$resumeM -> addResumeWork($postData,array('where'=>array('id'=>intval($_POST['id'])),'utype'=>'admin'));

        $this->render_json($return['errcode'], $return['msg']);
	}
	function project_action(){
		$resumeM    =   $this -> MODEL('resume');
		$id    		=   intval($_POST['id']);
		$postData	=	array(
			'uid'		=>	intval($_POST['uid']),
			'eid'		=>	intval($_POST['eid']),
			'name'		=>  $_POST['name'],
			'sdate'		=>	strtotime($_POST['sdate']),
	        'edate'		=>  strtotime($_POST['edate']),
			'title'		=>  $_POST['title'],
			'content'	=>	trim($_POST['content'])
		);
		$return	 =	$resumeM -> addResumeProject($postData,array('where'=>array('id'=>intval($_POST['id'])),'utype'=>'admin'));

        $this->render_json($return['errcode'], $return['msg']);
	}
	function edu_action(){
		$resumeM    =   $this -> MODEL('resume');
		$id    		=   intval($_POST['id']);
		$postData	=	array(
			'uid'		=>	intval($_POST['uid']),
			'eid'		=>	intval($_POST['eid']),
			'name'		=>  $_POST['name'],
			'sdate'		=>	strtotime($_POST['sdate']),
	        'edate'		=>  strtotime($_POST['edate']),
			'title'		=>  $_POST['title'],
			'education'	=>	$_POST['education'],
	        'specialty' =>  $_POST['specialty'],
		);

        $return	 =	$resumeM -> addResumeEdu($postData,array('where'=>array('id'=>intval($_POST['id'])),'utype'=>'admin'));

        $this->render_json($return['errcode'], $return['msg']);
	}
	function training_action(){
		$resumeM    =   $this -> MODEL('resume');
		$id    		=   intval($_POST['id']);
		$postData	=	array(
			'uid'		=>	intval($_POST['uid']),
			'eid'		=>	intval($_POST['eid']),
			'name'		=>  $_POST['name'],
			'sdate'		=>	strtotime($_POST['sdate']),
	        'edate'		=>  strtotime($_POST['edate']),
			'title'		=>  $_POST['title'],
			'content'	=>	trim($_POST['content'])
		);
		$return	 =	$resumeM -> addResumeTrain($postData,array('where'=>array('id'=>intval($_POST['id'])),'utype'=>'admin'));

        $this->render_json($return['errcode'], $return['msg']);
	}
	function other_action()
	{
		$resumeM    =   $this -> MODEL('resume');
		$id    		=   intval($_POST['id']);
		$postData	=	array(
			'uid'		=>	intval($_POST['uid']),
			'eid'		=>	intval($_POST['eid']),
			'name'		=>  $_POST['name'],
			'content'	=>	trim($_POST['content'])
		);
		$return	 =	$resumeM -> addResumeOther($postData,array('where'=>array('id'=>intval($_POST['id'])),'utype'=>'admin'));

        $this->render_json($return['errcode'], $return['msg']);
	}
	
	function savedesc_action(){
        $ResumeM	=	$this->MODEL('resume');
        $_POST	=	$this->post_trim($_POST);
        if($_POST['tag']){

            $tag	=	array_unique(@explode(',',$_POST['tag']));

            foreach($tag as $value){

                $tagLen	=	mb_strlen($value);

                if($tagLen>=2 && $tagLen<=8){

                    $tagList[]	=	$value;
                }
                if(count($tagList)>=5){
                    break;
                }
            }
            $tagStr = implode(',',$tagList);
        }

        $rData	=	array(
            'tag'			=>	$tagStr,
            'description'	=>	$_POST['description'],
            'lastupdate'	=>	time()
        );
        if(resumeTimeState($this->config['user_revise_state']) == '0'){
            $rData['state'] = 0 ;
        }
        $return	=	$ResumeM->upResumeInfo(array('uid'=>$_POST['uid']),array('rData'=>$rData,'utype'=>'admin'));
        $this -> layer_msg($return['msg'], 9,0,'','',$return['errcode']);
    }
    function delResumeFb_action()
    {
        $table			=	$_POST['table'];

        $fbwhere['eid']	=	intval($_POST['eid']);
        $fbwhere['id']	=	$_POST['id'];
        $fbwhere['uid']	=	$_POST['uid'];

        $ResumeM		=	$this->MODEL('resume');

        $return			=	$ResumeM->delFb($table,$fbwhere,array('utype'=>'admin'));
        header('content-type:application/json; charset=utf-8');
        echo json_encode($return);die;
    }
    function showSave_action(){

        $ResumeM	=	$this->MODEL('resume');
        if ($_POST['uimage']){

            $upArr   =  array(
                'dir'      =>  'user',
                'type'     =>  'show',
                'base'     =>  $_POST['uimage'],
            );
            $result  =  $this -> upload($upArr);

            if (!empty($result['msg'])){

                $data['error'] = 2;
                $data['msg'] = $result['msg'];
                $this -> layer_msg($data['msg'], 9,0,'','',$data['error']);

            }elseif (!empty($result['picurl'])){

                $_POST['picurl']  =  $result['picurl'];

            }
        }else{
            $this -> layer_msg('请上传图片', 9,0,'','',2);
        }

        $rinfo	=	$ResumeM->getResumeInfo(array('uid'=>$_POST['uid']),array('field'=>'r_status'));
        $adata	=	array(
            'picurl'	=>	$_POST['picurl'],
            'status'  	=>  $rinfo['r_status']==0?1:$this->config['rshow_photo_status']
        );
        $reusmeData['showData'][] = $adata;

        $adata['ctime']	=	time();
        $adata['uid']	=	$_POST['uid'];
        $adata['eid']	=	$_POST['eid'];
        $nid			=	$ResumeM->upResumeTable('resume_show','',$adata,array('utype'=>'user'));

        $list  =  $ResumeM->getResumeShowList(array('uid'=>$_POST['uid'],'eid'=>(int)$_POST['eid']));

        $udata['show']	=  count($list);
        $ResumeM->upUserResume($udata,array('eid'=>(int)$_POST['eid'],'uid'=>$_POST['uid']));

        if(!empty($reusmeData) && $nid){

            $pass = $ResumeM->resumeConCheck($reusmeData,array('uid'=>$_POST['uid'],'usertype'=>1,'eid'=>(int)$_POST['eid'],'source'=>$_POST['source']));

            if(!$pass){
                $ResumeM->upResumeTable('resume_show',array('id'=>$nid,'uid'=>$_POST['uid']),array('status'=>1, 'eid' => $_POST['eid']),array('utype'=>'user'));
            }
        }
        $nid? $data['msg']	=	'个人作品保存成功！' : $data['msg']='个人作品保存失败！';
        $nid? $data['error']	=	'1' : $data['error'] = '2';
        $this -> layer_msg($data['msg'], 9,0,'','',$data['error']);
    }

    /**
     * 处理单个图片上传
     * @param file/需上传文件; dir/上传目录; type/上传图片类型; base/需上传base64; preview/pc预览即上传
     */
    private function upload($data = array('file'=>null,'dir'=>null,'type'=>null,'base'=>null,'preview'=>null)){

        $UploadM  =  $this	  ->	MODEL('upload');
        $upArr    =  array(
            'file'     =>  $data['file'],
            'dir'      =>  $data['dir'],
            'type'     =>  $data['type'],
            'base'     =>  $data['base'],
            'preview'  =>  $data['preview']
        );
        $return  =  $UploadM -> newUpload($upArr);

        return $return;
    }
	function status_action(){//简历审核
		$resumeM   =   $this -> MODEL('resume');
		$postData      =   array(
            'lock_status'   =>  !empty($_POST['lock_status']) ? $_POST['lock_status'] : 0,
	        'state'        =>  intval($_POST['status']),
	        'statusbody'   =>  trim($_POST['statusbody'])
	    );
		$return  =  $resumeM -> statusResume($_POST['id'],array('post'=>$postData));
        $nextUrl =  $this->nextNeedStatusUrl();
        $this -> layer_msg($return['msg'], 9,0,$nextUrl,'',$return['errcode']);
	}
    private function nextNeedStatusUrl(){
        // 下一个待审核简历
        $resumeM  =  $this->MODEL('resume');
        $row      =  $resumeM->getExpect(array('state'=>0,'r_status'=>array('<>',2),'orderby'=>array('lastupdate,DESC')), array('field'=>'id'));
        if (!empty($row)){
            return 'index.php?m=user&c=users_resume&a=show&id='.$row['id'];
        }else{
            return '';
        }
    }
    /**
     * 会员-个人-简历管理: 同步审核用户
     */
    function resumestatus_action()
    {
        if ($_POST) {

            $id         =   intval($_POST['id']);
            $uid        =   intval($_POST['uid']);
            $status     =   intval($_POST['status']);
            $statusbody =   trim($_POST['statusbody']);

            $resumeM    =   $this->MODEL('resume');

            $post   =   array(
                'uid'           =>  $uid,
                'lock_status'   =>  !empty($_POST['lock_status']) ? $_POST['lock_status'] : 0,
                'state'         =>  $status,
                'statusbody'    =>  $statusbody
            );

            $return     =   $resumeM -> status($id, $post);
            $nextUrl    =   $this->nextNeedStatusUrl();
            $this -> layer_msg($return['msg'], 9,0,$nextUrl,'',$return['errcode']);
        }
    }
    /**
     * 会员-个人-简历管理: 置顶简历
     */
    function top_action(){
        $id   =  trim($_POST['eid']);
        if ($_POST['s']){
            $post  =  array(
                'top'      =>  0,
                'topdate'  =>  0
            );
        }else{
            $post  =  array(
                'top'      =>  1,
                'topdate'  =>  strtotime('+'.intval($_POST['addday']).' day')
            );
        }
        $resumeM  =  $this -> MODEL('resume');
        $return   =  $resumeM -> topResume($id,$post);
        $this -> layer_msg($return['msg'], 9,0,'','',$return['errcode']);
    }
    /**
     * 会员-个人-简历管理: 推荐简历
     */
    function rec_action(){
        $id   =  trim($_POST['id']);

        $rec  =  $_POST['rec'];
        $resumeM  =  $this -> MODEL('resume');

        $return   =  $resumeM -> recResume($id,$rec);

        $this -> layer_msg($return['msg'], 9,0,'','',$return['errcode']);
    }

    /**
     * 会员-个人-简历管理: 刷新简历
     */
    function refresh_action()
    {

        if ($_GET['id']) {

            $id =   intval($_GET['id']);
        } elseif ($_POST['ids']) {

            $id =   trim($_POST['ids']);
        }
        $resumeM=   $this->MODEL('resume');

        $return =   $resumeM->refreshResume($id);

        $this->render_json($return['errcode'], $return['msg']);
    }
}
?>