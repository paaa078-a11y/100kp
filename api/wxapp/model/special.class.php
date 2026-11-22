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
class special_controller extends wxapp_controller{
	function index_action(){
		$specialM	=	$this->MODEL('special');
		$page		=	$_POST['page'];
		$limit		=	$_POST['limit'];
		$limit		=	!$limit?20:$limit;

		$where['display']   =   1;

		$where['orderby']	=	"sort,DESC";
		// $where['orderby']	=	"id,desc";

		if($page){
			$pagenav		=	($page-1)*$limit;
			$where['limit']	=	array($pagenav,$limit);
		}else{
			$where['limit']	=	array('',$limit);
		}
		$rows		=	$specialM->getSpecialList($where);

		$List		=	$rows['list'];
		if(!empty($List)){
		    foreach ($List as $k=>$v){
		        if($v['wappic_n'] == ''){
		            $List[$k]['wappic_n'] = $v['pic_n'];
                }
            }
        }
		if(is_array($List)&&!empty($List)){
			$data	=	count($List)?$List:array();
			$error	=	1;
		}else{
			$error	=	2;
		}
		$this -> render_json($error,'',$data);
	}

	//专题招聘详情
	function show_action(){
		$id			=	(int)$_POST['id'];
		if(!empty($_POST['uid']) && !empty($_POST['token'])){

    		$member     =   $this->yzToken($_POST['uid'],$_POST['token']);

			$uid        =   $member['uid'];

			$usertype   =   $member['usertype'];

		}

		if(!$id){
			$error	=	2;
		}else{
			$specialM	=	$this -> MODEL('special');
			$JobM		=	$this -> MODEL('job');
			$info		= 	$specialM -> getSpecialOne(array('id'=>$id));
			if($uid && $usertype=='2'){
				$isapply		=	$specialM->getSpecialComOne(array("uid"=>$uid,"sid"=>$id));
			}
			if(is_array($info)&&!empty($info)){
			    
			    if ($info['tpl'] == 'gl.htm'){
			        // 该模板需要所有参会企业uid，来查参会企业相关数据
			        $cuid = array();
			        $coms = $specialM->getSpecialComList(array('sid'=>$id, 'status'=> 1), array('field'=>'`uid`'));
			        foreach ($coms['list'] as $v){
			            $cuid[] = $v['uid'];
			        }
			        // 该模板需要的名企
			        $specomlist['list']  =  $specialM->glFamous(array('sid'=>$info['id'], 'orderby'=>'sort', 'limit'=>12));
			        // 该模板所需的行业
			        $data['hy'] = $specialM->getSpecialHy($cuid);
			        
			    }else{
			        $specomlist	=	$specialM -> getSpecialComList(array('sid'=>$id,'status'=>'1','orderby' => 'sort,DESC'),array('utype'=>'wxapp'));
			    }
			    $info['intro']  =  $this->preghtml($info['intro']);
			    
				$data['isapply']		=	!empty($isapply) ? 1 : 0;
				$data['specomlist']		=	count($specomlist['list'])?$specomlist['list']:array();
				$data['info']			=	count($info)?$info:array();
				$data['applysuccess']	=	count($isapply)?$isapply:array();
			    // app用分享数据
			    if ($this->plat == 'app'){
			        
			        $data['shareData']  =  array(
			            'url'       =>  Url('wap',array('c'=>'special','a'=>'show','id'=>$id)),
			            'title'     =>  $info['title'],
			            'summary'   =>  mb_substr(strip_tags($info['intro']), 0,30,'UTF8'),
			            'imageUrl'  =>  checkpic($info['pic'],$this->config['sy_wx_sharelogo'])
			        );
			    }
			    // 百度小程序用seo
			    if ($this->plat == 'mini') {
                    $this->data     =   array('spename' => $info['title']);;
                    $seo            =   $this->seo('spe_show', '', '', '', false, true);
                    $data['seo']    =   $seo;
                }
				$error	=	1;
			}else{
				$error	=	2;
			}
		}
		$this -> render_json($error,'',$data);
	}

	//专题招聘报名
	function apply_action(){
		$specialM	=	$this -> MODEL('special');
		$user		=	$this -> yzToken(intval($_POST['uid']),$_POST['token']);
		$uid		=	$user['uid'];
		$usertype	=	$user['usertype'];
		$specialid	=	intval($_POST['specialid']);
		$postData	=	array(
			'uid'		=>	$uid,
			'usertype'	=>	$usertype,
			'id'		=>	$specialid,
		);
		$return		=	$specialM->addSpecialComInfo($postData);
		$this -> render_json($return['errcode'],$return['msg']);

	}
	// gl模板查询企业列表
	function getComList_action(){
	    
	    $res = $this->MODEL('special')->glComList($_POST['sid'], $_POST['hy'], $_POST['page'], $_POST['numb']);
	    
	    $this->render_json(0, 'ok', $res);
	}
	// gl模板查询职位列表
	function getJobList_action(){
	    
	    $res = $this->MODEL('special')->glJobList($_POST);
	    
	    $this->render_json(0, 'ok', $res);
	}
	// 通过关键词查职位专题
	function getSpecialJob_action(){
	    
	    $keyword = $this->stringfilter($_POST['keyword']);
	    $sjM  =	$this->MODEL('specialjob');
	    $row  = $sjM->getListByKeyword($keyword);
	    
	    $this->render_json(0, 'ok', array('info'=>$row));
	}

	/*
	 * 职位专题列表
	 */
    function sjList_action(){
        $sjM = $this->MODEL('specialjob');
        $page		=	$_POST['page'];
        $limit		=	$_POST['limit'];
        $limit		=	!$limit?20:$limit;
        $where['display']   =   1;
		if (isset($_GET['istuiguang']) && $_GET['istuiguang'] == '1') {
			//推广
			$where['tuiguang'] = 1;
		}
        $where['orderby']	=	array('sort,desc', 'id,desc');
        if($page){
            $pagenav		=	($page-1)*$limit;
            $where['limit']	=	array($pagenav,$limit);
        }else{
            $where['limit']	=	array('',$limit);
        }
        $List =	$sjM->getList($where, array('need_jobnum'=>1));
        if(!empty($List)){
            foreach ($List as $k=>$v){
                $List[$k]['sjlist_pic_n'] = checkpic($v['sjlist_pic']);
                $List[$k]['ctime_n'] = date('Y-m-d', $v['ctime']);
            }
        }
        if(is_array($List)&&!empty($List)){
            $data	=	count($List)?$List:array();
            $error	=	1;
        }else{
            $error	=	2;
        }
        $this->render_json($error,'',$data);
    }

	// 职位专题详情
	function job_action(){
	    
	    $sjM  =	$this->MODEL('specialjob');
        $info  =  $sjM->getInfo(array('id'=>intval($_POST['id']),'display'=>1));
        if (empty($info)){
            $this->render_json(1, '没有找到该职位专题');
        }
        $data['info'] = $info;

        if ($this->plat == 'mini'){
		    // 小程序用seo
			$this->data     =   array('spe_name' => $info['title']);
            $seo            =  $this->seo('special_job','','','',false, true);
            $data['seo']    =  $seo;
		}
		
	    $this->render_json(0, 'ok', $data);
	}

	function joblist_action(){
        $sjM  =	$this->MODEL('specialjob');
        $page		=	$_POST['page'];
        $limit		=	$_POST['limit'];
        $limit		=	!$limit?20:$limit;

        $info = $sjM->getInfo(array('id'=>intval($_POST['id']),'display'=>1));
        if (empty($info)){
            $this->render_json(1, '没有找到该职位专题');
        }
        $other = array('utype'=>'wxapp');
        if ($info['type'] == 2) {// 自动刷新职位
        	if ($info['next_time'] <= time()) {
                $jobids = $sjM->autoRefreshSpecialJobs($info);
			} else {
                $jobids = $info['jobids'];
			}
			$jobs = array();
        	if ($jobids) {
                $jobs = explode(',', $jobids);
            }
            $manualJobs = array();
			if ($info['manual_jobids']) {
			    $manualJobs = explode(',', $info['manual_jobids']);
            }
            $alljobids = array_merge($jobs, $manualJobs);
			$total = count($alljobids);
        	$where['id'] = array('in', pylode(',', $alljobids));
            $other['auto_add'] = 1;
        } else {
            $where['sid'] = intval($_POST['id']);
            $total = $sjM->getjoblistnum($where);
		}
        $pagetotal = ceil($total/$limit);
        if($page){
            $pagenav		=	($page-1)*$limit;
            $where['limit']	=	array($pagenav,$limit);
        }else{
            $where['limit']	=	array('',$limit);
        }
        $List = $sjM->getjobList($where, $other);
        $data['list']	=	count($List)?$List:array();
        $this->render_json(0, 'ok', $data,$pagetotal);
    }
    function hotjobclass_action(){
        $categoryM	=	$this -> MODEL('category');
        $List = $categoryM->getHotJobClass(array('rec'=>1),'*');

        $this->render_json(0, 'ok', $List);
    }
}
?>
