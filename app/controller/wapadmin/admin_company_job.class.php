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

class admin_company_job_controller extends adminCommon
{
    function index_action()
    {

        if (!empty($_POST)) {

            $JobM   = $this->MODEL('job');

            $where  =   $mwhere =   $mUids  =   array();

            $page   =   $_POST['page'];
            $limit  =   $_POST['limit'];

            if (trim($_POST['keyword'])) {

                $where['name']      =   array('like', trim($_POST['keyword']), '');
                $where['com_name']  =   array('like', trim($_POST['keyword']), 'or');
                $data['keyword']    =   trim($_POST['keyword']);
            }
            if ($_POST['status']) {
                $status         =   intval($_POST['status']);
                if ($status == 2) {
                    $where['r_status'] = 2;
                } else {
                    $where['state'] = $status == 4 ? 0 : $status;
                }
            }
            if ($_POST['edu']) {

                $where['edu'] = $_POST['edu'];
            }

            if ($_POST['exp']) {

                $where['exp'] = $_POST['exp'];
            }
            if (isset($_POST['adtime'])) {
                if ($_POST['adtime'] == '1') {
                    $where['sdate'] = array('>', strtotime('today'));
                } else {
                    $where['sdate'] = array('>', strtotime('-' . intval($_POST['adtime']) . ' day'));
                }
            }
            if (isset($_POST['source'])) {
                $where['source'] = intval($_POST['source']);
            }
            if ($_POST['service']) {
                $jtype = trim($_POST['service']);
                if ($jtype == 3) {
                    $where['rec_time'] = array('>', time());
                } else if ($jtype == 1) {
                    $where['urgent_time'] = array('>', time());
                } else if ($jtype == 2) {
                    $where['xsdate'] = array('>', time());
                }
            }
            if ($_POST['uid']) {
                $where['uid']   =   trim($_POST['uid']);
            }
            $where['orderby']   =   'lastupdate,desc';
            if ($page) {
                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =   array($pagenav, $limit);
            } else {
                $where['limit'] =   $limit;
            }
            $list = $JobM->getList($where, array('utype' => 'admin', 'cache' => '1', 'reserve' => 1));
            $data['list'] = count($list['list']) ? $list['list'] : [];
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $this->yuntpl(array('wapadmin/admin_company_job'));
    }

    function statis_action()
    {
        include(CONFIG_PATH . 'db.data.php');
        $source = $arr_data['source'];
        foreach ($source as $k => $v) {
            $sourcearr[] = array('id' => $k, "name" => $v);
        }
        $cacheM = $this->MODEL('cache');
        $CacheList = $cacheM->GetCache(array('com', 'job', 'city'));
        $statusarr = array(array('id' => 1, 'name' => '已审核'), array('id' => 2, 'name' => '已锁定'), array('id' => 3, 'name' => '未通过'), array('id' => 4, 'name' => '未审核'));
        $adtimearr = array(array('id' => 1, 'name' => '今天'), array('id' => 3, 'name' => '最近三天'), array('id' => 7, 'name' => '最近七天'), array('id' => 15, 'name' => '最近半月'), array('id' => 30, 'name' => '最近一个月'));
        $typearr = array(array('id' => 0, 'name' => '类型'), array('id' => 1, 'name' => '紧急职位'), array('id' => 2, 'name' => '置顶职位'), array('id' => 3, 'name' => '推荐职位'));
        $data['source'] = $source;
        $data['sourcearr'] = $sourcearr;
        $data['statusarr'] = $statusarr;
        $data['adtimearr'] = $adtimearr;
        $data['typearr'] = $typearr;
        $data['comdata'] = $CacheList['comdata'];
        $data['comclass_name'] = $CacheList['comclass_name'];
        header('content-type:application/json; charset=utf-8');
        echo json_encode($data);
        die;
    }

    /**
     * @desc 后台 -- 会员 -- 企业 -- 企业管理 / 职位管理 -- 新增  /  修改
     */
    function show_action()
    {
        $JobM       =   $this->MODEL('job');
        $companyM   =   $this->MODEL('company');
        $addressM   =   $this->MODEL('address');

        if ($_POST['show']) {
            if (isset($_POST['id']) && intval($_POST['id']) > 0) {
                $id     =   intval($_POST['id']);
                $info   =   $JobM->getInfo(array('id' => $id), array('lang' => 'isarray'));
                if (!$info) {
                    $this->render_json(1, '参数错误');
                }
                $uid    =   $info['uid'];
            }
            if (intval($_POST['uid'])) {
                $uid    =   intval($_POST['uid']);
            }

            $company    =   $companyM->getInfo($uid, array('field' => '`uid`,`r_status`,`linkman`,`linktel`,`address`,`provinceid`,`cityid`,`three_cityid`,`x`,`y`'));

            $addressList=   $addressM->getAddressList(array('uid' => $uid));

            $this->render_json(0, 'ok', array(
                'company' => $company,
                'addressList' => $addressList,
                'uid' => $uid,
                'jobInfo' => $info
            ));
        }
        if($_POST['update']){

            $company    =   $companyM->getInfo($_POST['uid'], array('field' => '`uid`,`r_status`,`linkman`,`linktel`,`address`,`provinceid`,`cityid`,`three_cityid`,`x`,`y`'));

            $description	=	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'background-color:','background-color:','white-space:'),$_POST['content']);

            if ($_POST['link_id'] == -1){

                $provinceid     =   $company['provinceid'];
                $cityid         =   $company['cityid'];
                $three_cityid   =   $company['three_cityid'];

                $x              =   $company['x'];
                $y              =   $company['y'];

            }else if(intval($_POST['link_id']) > 0){

                $addressInfo    =   $addressM->getAddressInfo(array('id' => $_POST['link_id'], 'uid' => $_POST['uid']));
                $provinceid     =   $addressInfo['provinceid'];
                $cityid         =   $addressInfo['cityid'];
                $three_cityid   =   $addressInfo['three_cityid'];

                $x              =   $addressInfo['x'];
                $y              =   $addressInfo['y'];
            }

            $post	        =	array(
                'name'			=>	$_POST['name'],

                'job1'          =>  intval($_POST['job1']),
                'job1_son'      =>  intval($_POST['job1_son']),
                'job_post'      =>  intval($_POST['job_post']),

                'provinceid'    =>  intval($provinceid),
                'cityid'        =>  intval($cityid),
                'three_cityid'  =>  intval($three_cityid),

                'x'             =>  $x,
                'y'             =>  $y,

                'link_id'       =>  (int)$_POST['link_id'] > 0 ? $_POST['link_id'] : '',
                'is_link'       =>  $_POST['is_link'] ? $_POST['is_link'] : 1,

                'minsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['minsalary']),
                'maxsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['maxsalary']),

                'description'	=>	$description,
                'r_status'      =>	$company['r_status'],
                'hy'            =>  intval($_POST['hy']),
                'number'        =>  intval($_POST['number']),
                'exp'           =>  intval($_POST['exp']),
                'report'        =>  intval($_POST['report']),
                'age'           =>  intval($_POST['age']),
                'sex'           =>  intval($_POST['sex']),
                'edu'           =>  intval($_POST['edu']),
                'is_graduate'   =>  intval($_POST['is_graduate']),
                'marriage'      =>  intval($_POST['marriage']),
                // 'lang'          =>  trim(pylode(',', $_POST['lang'])),
                'lang'          =>  $_POST['lang'],
                'state'      	=> 	$company['r_status']==1 ? 1:0,
                'jobhits'       =>  intval($_POST['jobhits']),
                'jobexpoure'    =>  intval($_POST['jobexpoure']),

                'exp_req'       =>  trim($_POST['exp_req']),
                'edu_req'       =>  trim($_POST['edu_req']),

                'zp_num'        =>  intval($_POST['zp_num']),
                'zp_minage'     =>  intval($_POST['zp_minage']),
                'zp_maxage'     =>  intval($_POST['zp_maxage']),
                'minage_req'    =>  intval($_POST['minage_req']),
                'maxage_req'    =>  intval($_POST['maxage_req']),
                'sex_req'       =>  intval($_POST['sex_req']),
            );

            $data	        =	array(
                'post'			=>	$post,
                'id'			=>	intval($_POST['id']),
                'uid'			=>	$_POST['uid'],
                'utype'			=>	'admin'
            );

            $return	        =	$JobM->addJobInfo($data);

            $this->render_json($return['errcode'], $return['msg']);
        }
        $this->yuntpl(array('wapadmin/admin_company_job_show'));
    }

	function edit_action(){
		include(CONFIG_PATH."db.data.php");		
		$this->yunset("arr_data",$arr_data);
		$this->yunset($this->MODEL('cache')->GetCache(array('city','hy','com','job')));
		$JobM		=	$this -> MODEL('job');
		$CompanyM	=	$this -> MODEL('company');
		if($_GET['id']){
			$row	=	$JobM -> getInfo(array('id'=>intval($_GET['id'])),array('add'=>'yes'));
			$this->yunset("row",$row);
			$this->yunset("lasturl",$_SERVER['HTTP_REFERER']);
			$uid	=	$row['uid'];
		}
		if(intval($_GET['uid'])){ 
          $uid		=	intval($_GET['uid']);	
        }
		$company	=	$CompanyM->getInfo($uid,array('field'=>'`uid`,r_status'));
		$this->yunset('company',$company);
		$this->yunset('uid',$uid);
		if($_POST['update']){
			$postData	=	array(
				'name'			=>	$_POST['name'],
				'hy'            =>  intval($_POST['hy']),
				'job1'          =>  intval($_POST['job1']),
                'job1_son'      =>  intval($_POST['job1_son']),
                'job_post'      =>  intval($_POST['job_post']),
				'provinceid'    =>  intval($_POST['provinceid']),
                'cityid'        =>  intval($_POST['cityid']),
				'three_cityid'  =>  intval($_POST['three_cityid']),
				'minsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['minsalary']),
				'maxsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['maxsalary']),
				'number'        =>  intval($_POST['number']),
				'exp'           =>  intval($_POST['exp']),
                'report'        =>  intval($_POST['report']),
				'sex'           =>  intval($_POST['sex']),
				'edu'           =>  intval($_POST['edu']),
				'marriage'      =>  intval($_POST['marriage']),
				'lang'          =>  trim(pylode(',', $_POST['lang'])),
				'description'	=>	str_replace("&amp;","&",html_entity_decode($_POST['description'],ENT_QUOTES)),
				'r_status'      =>	$company['r_status'],
				'state'      	=> 	$company['r_status']==1 ? 1:0
			);
			$data=array(
				'post'			=>	$postData,
				'id'			=>	intval($_POST['id']),
				'uid'			=>	intval($_POST['uid']),
				'utype'			=>	'admin'
			);
			$return		=	$JobM -> addJobInfo($data);
			if($return['errcode']==9){
				$data['msg']	=	$return['msg'];
				$data['url']	=	'index.php?c=admin_comjob';
				$this->yunset("layer",$data);
			}
		}
		$this->yunset("headertitle","职位操作");
		$this->yuntpl(array('wapadmin/admin_comjob_edit'));
	}

	function del_action(){
		$JobM	=	$this -> Model('job');
		$PackM	=	$this->Model('pack');
	    if($_GET['del']||$_GET['id']){
			$delID	=	is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];
    		if(is_array($_GET['del'])){
    			$layer_type	=	1;
	    	}else{
	    		$layer_type	=	0;
	    	}
			$rewardList	=	$PackM -> getRewardJobList(array('jobid'=>array('in',pylode(',', $delID))));
			$shareList	=   $PackM -> getShareJobList(array('jobid'=>array('in',pylode(',', $delID))),array('utype'=>'admin'));
			if($rewardList ||  $shareList){
				if($rewardList){
					foreach($rewardList as $val){
						$rjobids[]	=	$val['jobid']; 
					}
				}
				if($shareList){
					foreach($shareList as $val){
						$sjobids[]	=	$val['jobid']; 
					}
				}
				if($rjobids && $sjobids){
					$input	=	array_merge($rjobids,$sjobids);//先组合一个$uid;
				}else{
					if($rjobids){
						$input	=	$rjobids;
					}else{
						$input	=	$sjobids;
					}
				}
				$jobid     =  array_merge(array_diff($input,$delID),array_diff($delID,$input));
				$numjobid  =  array_diff_assoc($input,$delID);//获取到重复的值
				$jobnum    =  count($jobid);//删除id
				$rewanum   =  count($numjobid);//无法删除id
				 if($jobnum>0){
				     $addArr	=	$JobM -> delJob($jobid, array('utype'=>'admin'));
                if($rewanum>0){
						$msg	=	"删除成功职位".$jobnum.",删除失败".$rewanum.",原因：还有赏金未处理";
						$this->layer_msg($msg,$addArr['errcode'],$addArr['layertype'],$_SERVER['HTTP_REFERER'],2,1);
					}else{   
						$this->layer_msg( $addArr['msg'],$addArr['errcode'],$addArr['layertype'],$_SERVER['HTTP_REFERER'],2,1);
					}
				}else{
					$this->layer_msg('该职位还有赏金未处理,无法删除！',8,$layer_type,$_SERVER['HTTP_REFERER'],2,1);
				}
			}else{
			    $addArr		=	$JobM -> delJob($delID, array('utype'=>'admin'));
				$this->layer_msg($addArr['msg'],$addArr['errcode'],$addArr['layertype'],'index.php?c=admin_comjob',2,1);
			}
    	}else{
			$this->layer_msg("请选择您要删除的信息！",8);
    	}
	}

    // 职位单个审核，带职位详情
    function jobAudit_action(){
        $jobid =  intval($_GET['id']);
        $JobM  =  $this->MODEL('job');
        $Info  =  $JobM->getInfo(array('id' => $jobid));

        $this->render_json(0, 'ok', array('info' => $Info));
    }

    // 职位审核同步企业审核
    function cjobstatus_action()
    {
        if ($_POST) {

            $id         =   intval($_POST['pid']);
            $uid        =   intval($_POST['uid']);
            $status     =   intval($_POST['status']);
            $statusbody =   trim($_POST['statusbody']);

            $jobM   =   $this->MODEL('job');

            $post   =   array(

                'uid'           =>  $uid,
                'state'         =>  $status,
                'statusbody'    =>  $statusbody
            );

            $return     =   $jobM -> status($id, $post);

            if (isset($_POST['single'])){
                if ($return['errcode'] == 9){
                    // 仅保存
                    $this->render_json(9, $return['msg']);
                }else{
                    $this->render_json(8, $return['msg']);
                }
            }else{
                $this->render_json($return['errcode'], $return['msg']);
            }
        }
    }

    //  职位审核
    function status_action()
    {
        $jobM       =   $this->MODEL('job');

        $statusData = array(
            'state'         =>  intval($_POST['status']),
            'statusbody'    =>  trim($_POST['statusbody'])
        );

        $return = $jobM -> statusJob($_POST['pid'], $statusData);

        if (isset($_POST['single'])){
            if ($return['errcode'] == 9){
                $this->render_json(9, $return['msg']);
            }else{
                $this->render_json(8, $return['msg']);
            }
        }else{
            $this->render_json($return['errcode'], $return['msg']);
        }
    }

    // 	职位置顶
    function xuanshang_action(){
        $id     =   trim($_POST['pid']);

        $data   =   array(
            'top'   =>  intval($_POST['s']),
            'days'  =>  intval($_POST['days'])
        );

        $JobM   =   $this -> MODEL('job');

        $arr    =   $JobM -> addTopJob($id, $data);

        $this->render_json($arr['errcode'], $arr['msg']);
    }

    //  职位推荐
    function recommend_action(){
        $id     =   trim($_POST['pid']);

        $data   =   array(
            'rec'   =>  intval($_POST['s']),
            'days'  =>  intval($_POST['days'])
        );

        $JobM   =   $this -> MODEL('job');

        $arr    =   $JobM -> addRecJob($id, $data);

        $this->render_json($arr['errcode'], $arr['msg']);
    }

    //  职位紧急招聘
    function urgent_action(){
        $id     =   trim($_POST['pid']);

        $data   =   array(
            'urgent'   =>  intval($_POST['s']),
            'days'     =>  intval($_POST['days'])
        );

        $JobM   =   $this -> MODEL('job');

        $arr    =   $JobM -> addUrgentJob($id, $data);

        $this->render_json($arr['errcode'], $arr['msg']);
    }

    // 职位刷新
    function refresh_action()
    {

        $JobM   =   $this->MODEL('job');

        $ids    =   @explode(',', $_POST['ids']);

        $result =   $JobM->upInfo(array('lastupdate' => time()), array('id' => array('in', pylode(',', $ids))));

        if ($result){

            $logM   =   $this->MODEL('log');
            $jobS   =   $logM->getJobBySxLog(array('id' => array('in', pylode(',', $ids))), array('type' => 1, 'field' => 'id,uid'));

            $vData  =   array();

            foreach ($jobS as $k => $v) {

                $vData[$k]['uid']       =   $v['uid'];
                $vData[$k]['usertype']  =   2;
                $vData[$k]['jobid']     =   $v['id'];
                $vData[$k]['type']      =   1;
                $vData[$k]['r_time']    =   time();
                $vData[$k]['port']      =   5;
                $vData[$k]['ip']        =   fun_ip_get();
                $vData[$k]['remark']    =   '管理员操作：手机后台职位列表刷新职位';
            }
            $logM->addJobSxLogS($vData);
        }

        $this->MODEL('log')->addAdminLog("职位(ID" . $_POST['ids'] . "刷新成功");
    }

    // 职位详情
    function detail_action()
    {
        if ($_POST) {
            $JobM = $this->MODEL('job');
            if (empty($_POST['id'])) {
                $this->render_json(1, '参数错误');
            }

            $info = $JobM->getInfo(array('id' => intval($_POST['id'])), array('com' => 'yes', 'link' => 1, 'link_type' => 'admin'));
            if (!$info) {
                $this->render_json(2, '数据异常');
            }

            $info['sdate_n'] = date('Y-m-d H:i:s', $info['sdate']);
            $info['xstime'] = $info['xsdate'] ? date('Y-m-d', $info['xsdate']) : '';
            $info['xs'] = isset($info['xsdate']) && $info['xsdate'] > time() ? 1 : 0;
            $info['recdate'] = $info['rec_time'] ? date('Y-m-d', $info['rec_time']) : '';
            $info['eurgent'] = $info['urgent_time'] ? date('Y-m-d', $info['urgent_time']) : '';
            $info['lang_n'] = $info['lang'] ? explode(',', $info['lang']) : '';

            // 收到简历
            $info['sqjobNum'] = $JobM->getSqJobNum(array('job_id' => $info['id']));
            // 邀请面试
            $info['yqmsNum'] = $JobM->getYqmsNum(array('jobid' => $info['id']));

            $returnData['info'] = $info;

            $ComM = $this->MODEL('company');
            $comInfo = $ComM->getInfo($info['uid']);
            $returnData['comInfo'] = $comInfo;

            $this->render_json(0, 'ok', $returnData);
        }
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        $isOutcall = $appkey && $appsecret;
        $this->yunset('is_outcall', $isOutcall);// 是否配置外呼
        $this->yuntpl(array('wapadmin/admin_company_job_detail'));
    }


    function checkstate_action()
    {
        $jobM       =   $this->MODEL('job');

        if ($_POST['status'] == 1){

            $upData =   array('status' => 0);
            $logMsg = "职位(ID：" . $_POST['job_id'] . ")上架成功";
        }else{

            $upData =   array('status' => 1);
            $logMsg = "职位(ID：" . $_POST['job_id'] . ")下架成功";
        }

        $jobM -> upInfo($upData, array('id' => $_POST['job_id']));

        $this->MODEL('log')->addAdminLog($logMsg);
        $this->render_json(9, '职位状态调整成功');
    }
}
?>