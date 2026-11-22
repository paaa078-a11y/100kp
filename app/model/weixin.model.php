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
class weixin_model extends model{
   
    protected 	$zdMsgType = array(
					'text'=>'文本消息',
					'image'=>'图片消息',
					'news'=> '图文消息',
					'xcx'=>'小程序卡片'
				);
   /******微信消息函数**********/
	function myMsg($wxid='')
	{
		$userBind = $this->isBind($wxid);
		
		if($userBind['bindtype']=='1')
		{
			$Return['centerStr'] = "<Content><![CDATA[您最新没有新的消息！]]></Content>";
			
		}else{

			$Return['centerStr'] = $userBind['cenetrTpl'];
		}
		$Return['MsgType']   = 'text';
		return $Return;
	}

    /**
     * @desc   引用log类，添加用户日志
     * @param $uid
     * @param $usertype
     * @param $content
     * @param string $opera
     * @param string $type
     * @param string $detail
     * @return void
     */
    private function addMemberLog($uid, $usertype, $content, $opera = '', $type = '', $detail = '')
    {
        require_once('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return $LogM->addMemberLog($uid, $usertype, $content, $opera, $type, $detail);
    }

	/**
	 * @param $uid
	 * @param $userType
	 * @param $port
	 * @param $type
	 * @param array $jobIdS
	 * @param null $remark
	 * @param int $freeNum
	 */
    private function addJobSxLogs($uid, $userType, $port, $type, $jobIdS = array(), $remark = null, $freeNum = 0)
    {

        $vData  =   array();
        foreach ($jobIdS as $k => $v) {

            $vData[$k]['uid']       =   $uid;
            $vData[$k]['usertype']  =   $userType;
            $vData[$k]['jobid']     =   $v;
            $vData[$k]['type']      =   $type;
            $vData[$k]['r_time']    =   time();
            $vData[$k]['port']      =   $port;
            $vData[$k]['ip']        =   fun_ip_get();
            $vData[$k]['remark']    =   $remark;
			$vData[$k]['free']    	=   $k < $freeNum ? 1 : 2;
			$vData[$k]['free_num'] 	=   $k < $freeNum ? 1 : 0;
        }

        $this->DB_insert_multi('job_refresh_log', $vData);
    }

    /**
     * @desc 套餐消耗明细
     * @param array $data
     * @return mixed
     */
    private function addStatisDetail($data = array())
    {
        require_once('statis.model.php');
        $statisM = new statis_model($this->db, $this->def);
        return $statisM->addStatisDetail($data);
    }
	
	/******微信邀请面试通知**********/
	function Audition($wxid='')
	{
		$userBind = $this->isBind($wxid);

		if($userBind['bindtype']=='1')
		{
			$Aud = $this->select_all('userid_msg', array('uid' => $userBind['uid'],'isdel'=>9,'orderby' => array('datetime, desc'), 'limit' => 5));
			
			if(is_array($Aud) && !empty($Aud))
			{
				foreach($Aud as $key=>$value)
				{
					$Info['title'] = "【".$value['fname']."】邀您面试\n邀请时间：".date('Y-m-d H:i:s',$value['datetime']);
					$Info['pic']   = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
					$Info['url']   = $this->config['sy_wapdomain']."/member/index.php?c=invite";
					$List[]        = $Info;
				}
				$Msg['title'] = '面试邀请';
				$Msg['pic']	= checkpic('',$this->config['sy_wx_logo']);
				$Msg['url'] = $this->config['sy_wapdomain']."/member/index.php?c=invite";
				$Return['centerStr'] = $this->Handle($List,$Msg);
				$Return['MsgType']   = 'news';

			}else{

				$Return['centerStr'] ='<Content><![CDATA['.'最近暂无面试邀请'.']]></Content>';
				$Return['MsgType']   = 'text';
			}
			return $Return;
		}else{
			$Return['MsgType']   = 'text';
			$Return['centerStr'] = $userBind['cenetrTpl'];
			return $Return;
		}
	}
	/******微信职位申请通知**********/
	function ApplyJob($wxid='')
	{
		$userBind = $this->isBind($wxid,2);
		if($userBind['bindtype']=='1')
		{
			$Apply = $this->select_all('userid_job', array('com_id' => $userBind['uid'],'isdel'=>9,'is_browse' => 1, 'orderby' => array('datetime, desc'), 'limit' => 5));
			
			if(is_array($Apply) && !empty($Apply))
			{
				foreach($Apply as $key=>$value)
				{
					$uid[] = $value['uid'];
				}
				//查询用户
				$userList = $this->select_all('resume', array('uid' => array('in', pylode(',', $uid))), '`uid`,`name`,`edu`,`exp`');
				if(is_array($userList)){
					
					foreach($userList as $key=>$value)
					{
						$resumeList[$value['uid']] = $value;
					}
				}
				include(PLUS_PATH."/user.cache.php");
				foreach($Apply as $key=>$value)
				{
					$Info['title'] = "【".$resumeList[$value['uid']]['name']."】".$userclass_name[$resumeList[$value['uid']]['edu']]."/".$userclass_name[$resumeList[$value['uid']]['exp']]."工作经验\n向您发布的职位：".$value['job_name']."\n投递一份简历\n投递时间：".date('Y-m-d H:i',$value['datetime']);
					$Info['pic']   = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
					$Info['url']   = $this->config['sy_wapdomain']."/member/index.php?c=hr";
					$List[]        = $Info;
				}
				$Msg['title'] = '简历投递';
				$Msg['pic']	= checkpic('',$this->config['sy_wx_logo']);
				$Msg['url'] = $this->config['sy_wapdomain']."/member/index.php?c=hr";
				$Return['centerStr'] = $this->Handle($List,$Msg);
				$Return['MsgType']   = 'news';

			}else{

				$Return['centerStr'] ='<Content><![CDATA['.'最近暂无简历投递'.']]></Content>';
				$Return['MsgType']   = 'text';
			}
			
			return $Return;
		}else{
			$Return['MsgType']   = 'text';
			$Return['centerStr'] = $userBind['cenetrTpl'];
			return $Return;
		}
	}
	/******兼职报名通知**********/
	function PartApply($wxid='')
	{
		$userBind = $this->isBind($wxid,2);
		if($userBind['bindtype']=='1')
		{
			//
			$Apply = $this->select_all('part_apply', array('comid' => $userBind['uid'], 'status' => 1, 'orderby' => array('ctime,desc'), 'limit' => 5));
			
			if(is_array($Apply) && !empty($Apply))
			{
				foreach($Apply as $key=>$value)
				{
					$uid[] = $value['uid'];
					$jobid[] = $value['jobid'];
				}
				//查询兼职职位
				$partJob = $this->select_all('partjob', array('uid' => $userBind['uid'], 'id' => array('in', pylode(',', $jobid))),'`id`,`name`');

				if(is_array($partJob)){
					
					foreach($partJob as $key=>$value)
					{
						$jobname[$value['id']] = $value['name'];
					}
				}
				//查询用户
				$userList = $this->select_all('resume', array('uid' => array('in', pylode(',', $uid))), '`uid`,`name`,`edu`,`exp`');
				if(is_array($userList)){
					
					foreach($userList as $key=>$value)
					{
						$resumeList[$value['uid']] = $value;
					}
				}
				include(PLUS_PATH."/user.cache.php");
				foreach($Apply as $key=>$value)
				{
					$Info['title'] = "【".$resumeList[$value['uid']]['name']."】".$userclass_name[$resumeList[$value['uid']]['edu']]."/".$userclass_name[$resumeList[$value['uid']]['exp']]."工作经验\n报名兼职：".$jobname[$value['jobid']]."\n报名时间：".date('Y-m-d H:i',$value['ctime']);
					$Info['pic']   = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
					$Info['url']   = $this->config['sy_wapdomain']."/member/index.php?c=partapply";
					$List[]        = $Info;
				}
				$Msg['title'] = '兼职报名';
				$Msg['pic']	= checkpic('',$this->config['sy_wx_logo']);
				$Msg['url'] = $this->config['sy_wapdomain']."/member/index.php?c=partapply";
				$Return['centerStr'] = $this->Handle($List,$Msg);
				$Return['MsgType']   = 'news';

			}else{

				$Return['centerStr'] ='<Content><![CDATA[最近暂无报名]]></Content>';
				$Return['MsgType']   = 'text';
			}
			
			return $Return;
		}else{
			$Return['MsgType']   = 'text';
			$Return['centerStr'] = $userBind['cenetrTpl'];
			return $Return;
		}
	}
	/******微信简历查看记录**********/
	function lookResume($wxid='')
	{
		$userBind = $this->isBind($wxid);
		if($userBind['bindtype']=='1')
		{
			$Aud = $this->select_all('look_resume', array('uid' => $userBind['uid'], 'com_id' => array('>', 0), 'orderby' => array('datetime, desc'), 'limit' => 5));
			if(is_array($Aud) && !empty($Aud))
			{
				
				foreach($Aud as $key=>$value)
				{
					$comid[] = $value['com_id'];
				}
				$comids =pylode(',',$comid);
		
				if($comids){
					$comList = $this->select_all('company', array('uid' => array('in', $comids)), '`uid`,`name`');
					if(is_array($comList)){
						foreach($comList as $key=>$value)
						{
							$comname[$value['uid']] = $value['name'];
						}
					}
					foreach($Aud as $key=>$value)
					{
						$Info['title'] = "查看企业：【".$comname[$value['com_id']]."】\n查看时间：".date('Y-m-d H:i:s',$value['datetime']);
						$Info['pic']   = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
						$Info['url']   = $this->config['sy_wapdomain']."/member/index.php?c=look";
						$List[]        = $Info;
					}
					$Msg['title'] = '最近查看我的简历';
					$Msg['pic']	= checkpic('',$this->config['sy_wx_logo']);
					$Msg['url'] = $this->config['sy_wapdomain']."/member/index.php?c=look";
					$Return['centerStr'] = $this->Handle($List,$Msg);
					$Return['MsgType']   = 'news';
				}else{
					$Return['centerStr']='<Content><![CDATA[已经很久没公司查看您的简历了！]]></Content>';
					$Return['MsgType']   = 'text';
				}
			}else{

				$Return['centerStr']='<Content><![CDATA[已经很久没公司查看您的简历了！]]></Content>';
				$Return['MsgType']   = 'text';
			}
			return $Return;

		}else{

			
			$Return['MsgType']   = 'text';
			$Return['centerStr'] = $userBind['cenetrTpl'];
			return $Return;
		}
	}
	/******微信刷新简历**********/
	function refResume($wxid='')
	{
		$userBind = $this->isBind($wxid);
		if($userBind['bindtype']=='1')
		{	
			$Resume = $this->select_num('resume_expect', array('uid' => $userBind['uid']));
			
			if($Resume>0)
			{
				$this->update_once('resume_expect', array('lastupdate' => time()), array('uid' => $userBind['uid']));
				$Return['centerStr']="<Content><![CDATA[简历刷新成功\n刷新时间:".date('Y-m-d H:i:s')."]]></Content>";

			}else{

				$Return['centerStr']='<Content><![CDATA[请先完善您的简历！]]></Content>';
				
			}
		}else{

			$Return['centerStr'] = $userBind['cenetrTpl'];
			
		}
		$Return['MsgType']   = 'text';
		return $Return;
	}

	/******微信刷新职位**********/
    function refJob($wxid = '')
    {
        $userBind   =   $this->isBind($wxid, 2);

        if ($userBind['bindtype'] == '1') {

            //查询正常职位数量
            $jobList =   $this->select_all('company_job', array('uid' => $userBind['uid'], 'state' => 1, 'status' => 0, 'r_status' => 1), 'id');
            $jobNum =   count($jobList);
            if ($jobNum > 0) {
                //查询用户数量以及积分
                $membeStatis    =   $this->select_once('company_statis', array('uid' => $userBind['uid']));
                $ratingInfo		=	$this->select_once('company_rating', array('id' => $membeStatis['rating']), '`freerefresh_num`');

                /**
                 * 存在每日免费刷新套餐数量
                 */
                $freeNum    =   0;
                if (intval($ratingInfo['freerefresh_num']) > 0){

                    //  今日已经免费刷新职位数量
                    $freedArr   =   $this->select_once('job_refresh_log', array('uid' => $userBind['uid'], 'free' => 1, 'r_time' => array('>=', strtotime('today'))), 'sum(free_num) as `num`');
                    $freedNum   =   $freedArr['num'];

                    //  今日剩余免费刷新数量
                    $freeNum    =   intval($ratingInfo['freerefresh_num']) - $freedNum;
                }
                $refIntegral    =   $this->config['integral_jobefresh'] * $this->config['integral_proportion'] * $jobNum;

                $jobIdArr       =   array();
                foreach ($jobList as $k => $v){

                    $jobIdArr[] =   $v['id'];
                }

                //判断会员模式 如果是时间会员则判断是否到期 如果套餐会员则判断有效期以及数量
                if ($membeStatis['rating_type'] == '2') {
                    if (isVip($membeStatis['vip_etime'])) {//有效期之内

                        $currentNum     =   $this->select_num('job_refresh_log', array('uid' => $userBind['uid'], 'free' => 2, 'r_time' => array('>=', strtotime('today'))));   //  今日刷新数目（套餐+消费）

                        if ($currentNum < ($membeStatis['breakjob_num'] + $freeNum)){

                            $upNum	=	$membeStatis['breakjob_num'] + $freeNum - $currentNum;

                            if ($upNum > $jobNum) {

                                $this->update_once('company_job', array('lastupdate' => time()), array('uid' => $userBind['uid'], 'state' => 1, 'status' => 0, 'r_status' => 1));
                                $msg = '职位刷新完成，本次共刷新' . $jobNum . "个职位！";

                                $this->addMemberLog($userBind['uid'], 2, '职位刷新：微信菜单刷新', 1, 4, '刷新职位（ID：' . pylode(',', $jobIdArr) . '）');

                                $this->addJobSxLogs($userBind['uid'], 2, 2, 1, $jobIdArr, '微信菜单刷新职位', $freeNum);
                            }else{

                                $msg  	=   "今日最多还可以刷新'.$upNum.'个职位！";
                            }
                        }else{

                            $msg        =   "今日可刷新数量已用完！";
                        }
                    } else {//不在有效期 使用积分

                        $useIntegral = 1;
                    }
                } else {//套餐模式

                    if (isVip($membeStatis['vip_etime']) && ($membeStatis['breakjob_num'] + $freeNum) >= $jobNum) {

                        $this->update_once('company_job', array('lastupdate' => time()), array('uid' => $userBind['uid'], 'state' => 1, 'status' => 0, 'r_status' => 1));

                        //扣除套餐数量
                        $subNum =   $jobNum - $freeNum > 0 ? $jobNum - $freeNum : 0;
                        $this->update_once('company_statis', array('breakjob_num' => array('-', $subNum)), array('uid' => $userBind['uid']));

                        $msg = '职位刷新完成，本次共刷新'.$jobNum."个职位！";
                        $this->addMemberLog($userBind['uid'], 2, "职位刷新：微信菜单刷新", 1, 4, '刷新职位（ID：'.pylode(',', $jobIdArr).'）');
                        $this->addJobSxLogs($userBind['uid'], 2, 2, 1, $jobIdArr, '微信菜单刷新职位', $freeNum);

                        $payDetail      =   '刷新操作，消耗刷新套餐数量：'.$jobNum;
                        $this->addStatisDetail(array('uid' => $userBind['uid'], 'type' => 2, 'num' => $jobNum, 'detail' => $payDetail, 'uri' => $_SERVER['REQUEST_URI']));
                    } else {//数量不足 使用积分

                        $useIntegral = 1;
                    }
                }
                if ($useIntegral == '1') {
                    if ($this->config['com_integral_online'] == '1') {//开启积分模式的情况下
                        if ($this->config['integral_jobefresh_type'] == '2') {//刷新职位减积分

                            //判断积分是否充足
                            if (intval($membeStatis['integral']) >= $refIntegral) {

                                $this->update_once('company_job', array('lastupdate' => time()), array('uid' => $userBind['uid'], 'state' => 1, 'status' => 0, 'r_status' => 1));
                                //扣除积分
                                $this->update_once('company_statis', array('integral' => array('-', $refIntegral)), array('uid' => $userBind['uid']));
                                $msg    =   '职位刷新完成，本次共刷新' . $jobNum . "个职位！";
                                $this->addMemberLog($userBind['uid'], 2, '职位刷新：微信菜单刷新', 1, 4, '刷新职位（ID：'.pylode(',', $jobIdArr).'）');
								
								include_once ('integral.model.php');
								$integral   =   new integral_model($this->db, $this->def);
								$integral -> company_invtal($userBind['uid'], $userBind['usertype'], $refIntegral, false, '微信菜单刷新职位（ID：'.pylode(',', $jobIdArr).'）', true, 2, 'integral', 27);

                                $this->addJobSxLogs($userBind['uid'], 2, 2, 1, $jobIdArr, '微信菜单刷新职位');
                            } else {

                                $msg    =   "本次刷新共需" . $refIntegral . "" . $this->config['integral_pricename'] . "，请先充值" . $this->config['integral_pricename'] . "！";
                            }
                        } else {
                            //批量刷新不走加积分模式 不符合逻辑 屏蔽操作 防止刷积分
                            $msg        =   "权限不足，升级会员，享受更多服务！";
                        }
                    } else {

                        $msg            =   "权限不足，升级会员，享受更多服务！";
                    }
                }
            } else {

                $msg    =   '您没有正在招聘的职位！';
            }
            $Return['centerStr'] = '<Content><![CDATA[' . $msg . ']]></Content>';
        } else {

            $Return['centerStr'] = $userBind['cenetrTpl'];

        }
        $Return['MsgType'] = 'text';
        return $Return;
    }

	/******微信职位搜索**********/
	function searchJob($keyword){
   	 	require_once ('hotkey.model.php');
    	$HotkeyM = new hotkey_model($this->db, $this->def);
		$keyword = trim($keyword);
		include(PLUS_PATH."/city.cache.php");
		if($keyword){
			$keywords = @explode(' ',$keyword);
			if(is_array($keywords)){
				foreach($keywords as $key=>$value){
					$iscity = 0;
					if($value!=''){
						foreach($city_name as $k=>$v){
							if(strpos($v,trim($value))!==false){
								$CityId[] = $k;
								$iscity = 1;
							}
						}
						if($iscity==0){
							$searchJob[] = "(`name` LIKE '%".trim($value)."%') OR (`com_name` LIKE '%". trim($value) ."%')";
						}
					}
				}
				foreach($keywords as $v){
					$keylist[] = "'".$v."'";
				}
				$hotkeynamewhere['key_name']		=   array('in', implode(',', $keylist));
				$hotkeynamewhere['type']			=   8;
				$hotkeynamelist   =  $HotkeyM->getList($hotkeynamewhere,array('field'=>'id,key_name'));
				if($hotkeynamelist && is_array($hotkeynamelist)){
					foreach($keywords as $v){
						foreach($hotkeynamelist as $val){
							if($val['key_name'] ==$v){
								$ids[]		=		$val['id'];
							}else{
								$keywordval[]	=	$v;
							}
						}
					}
				}else{
					foreach($keywords as $v){
						$keywordval[]	=	$v;
					}
				}
				$keywordfirst = array_unique($keywordval);
				if($ids){
					$upHotData   =   array( 
						'num'       =>  array('+',1),
						'wxtime'	=>	time()
					);
					$hotkeywhere['id']		=   array('in', pylode(',', $ids));
					$hotkeywhere['type']	=  8;
					$HotkeyM->upHotkey($hotkeywhere,$upHotData);
				}
				
				if($keywordfirst){
					foreach($keywordfirst as $v){
						$data        	=     array(
							'key_name'  =>    $v,
							'type'      =>    8,
							'num'       =>    1,
							'wxtime'	=>	time()
						);
						$HotkeyM->addInfo($data);
					}
					
				}
         		
				//添加
				$searchWhere = "`state`='1' AND `sdate`<='".time()."' AND `status`='0' AND `r_status`='1'";
				
				if(!empty($searchJob))
				{
					$searchWhere .=  " AND (".implode(' OR ',$searchJob).")";
				}
				if(!empty($CityId))
				{
					$City_id = pylode(',',$CityId);
					$searchWhere .= " AND (`provinceid` IN (".$City_id.") OR `cityid` IN (".$City_id.") OR `three_cityid` IN (".$City_id."))";
				}
 				$jobList = $this->DB_select_all("company_job",$searchWhere." order by `lastupdate` desc limit 5","`id`,`name`,`com_name`,`com_logo`");
 				
 				
			}
			
		}
		if(is_array($jobList) && !empty($jobList)){
			foreach($jobList as $key=>$value){
				$Info['title'] = "【".$value['name']."】\n".$value['com_name'];
				$Info['pic'] = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
				$Info['url'] = Url("wap",array('c'=>'job','a'=>'comapply','id'=>$value['id']));
				$List[]     = $Info;
			}
			$Msg['title'] = '查看与【'.$keyword. '】相关的职位';
			$Msg['pic']	= checkpic($value['com_logo'],$this->config['sy_wx_logo']);
			$Msg['url'] = Url('wap',array('c'=>'job','keyword'=>urlencode($keyword)));
			$Return['centerStr'] = $this->Handle($List,$Msg);
			$Return['MsgType']   = 'news';
		}else{
			if($this->config['wx_search_no']){
		        $Return['centerStr'] = '<Content><![CDATA['.$this->config['wx_search_no'].']]></Content>';
		    }else{
		        
		        $Return['centerStr'] = '<Content><![CDATA[未找到合适的职位！]]></Content>';
		    }
			$Return['MsgType']   = 'text';
		}
		return $Return;
	}
	/******微信场景码 图文**********/
	function sendPubLink($wxloginid){
   	 	
		$wxlogintype	=	explode('_',$wxloginid);
		if($wxlogintype[1] == 'jobid'){
			$jobid = $wxlogintype[2];

			$jobInfo = $this -> select_once("company_job",array('id'=>$jobid));
			if(is_array($jobInfo) && !empty($jobInfo)){
			
				$Msg['title'] = "招聘:".$jobInfo['name']." - ".$jobInfo['com_name'];
				$Msg['desc']  = strip_tags($jobInfo['description']);
				$Msg['pic']	  = checkpic($jobInfo['com_logo'],$this->config['sy_wx_sharelogo']);
				$Msg['url']   = Url('wap',array('c'=>'job','a'=>'comapply','id'=>$jobInfo['id']));
				
			}
		}elseif($wxlogintype[1] == 'companyid'){

			$comid		=	$wxlogintype[2];

			$comInfo	=	$this -> select_once("company",array('uid'=>$comid));
			if(is_array($comInfo) && !empty($comInfo)){
			
				$Msg['title'] = $comInfo['name'];
				$Msg['desc']  = strip_tags($comInfo['content']);
				$Msg['pic']	  = checkpic($comInfo['logo'],$this->config['sy_wx_logo']);
				$Msg['url']   = Url('wap',array('c'=>'company','a'=>'show','id'=>$comInfo['uid']));
				
			}
		}elseif($wxlogintype[1] == 'resumeid'){

			$resid		=	$wxlogintype[2];

			$resInfo	=	$this -> select_once("resume_expect",array('id'=>$resid));

			if(is_array($resInfo) && !empty($resInfo)){

				require_once ('resume.model.php');
				$expectM	=	new resume_model($this->db, $this->def);
				$expect		=   $expectM -> getExpect(array('id'=>$resInfo['id']),array('needCache'=>1));
				$sex		=	$expect['sex'] == '1' ? '男' : '女';
				$Msg['title'] = "意向岗位：".$resInfo['name'];
				$Msg['desc']  = "性别:".$sex."，学历:".$expect['edu_n']."，工作经验:".$expect['exp_n']."，期望薪资：".$expect['salary']."，期望工作地区:".$expect['city_classname'];
				$Msg['pic']	  = checkpic($resInfo['photo'],$this->config['sy_wx_logo']);
				$Msg['url']   = Url('wap',array('c'=>'resume','a'=>'show','id'=>$resInfo['id']));
				
			}
		}elseif($wxlogintype[1] == 'articleid'){

			$newsid		=	$wxlogintype[2];

			$newsInfo	=	$this -> select_once("news_base",array('id'=>$newsid));
			$newsconInfo	=	$this -> select_once("news_content",array('nbid'=>$newsid));
			if(is_array($newsInfo) && !empty($newsInfo)){
			
			    $content = strip_tags($newsInfo['content']);
			    $content = str_replace(array('&quot;', '&nbsp;', '<>'), array('', '', ''), $content);
			    
			    $Msg['title'] = $newsInfo['title'];
			    $Msg['desc']  = mb_substr($content,0,80,'utf-8');
				
				$Msg['pic']	  = checkpic($newsInfo['newsphoto'],$this->config['sy_wx_logo']);
				$Msg['url']   = Url('wap',array('c'=>'article','a'=>'show','id'=>$newsInfo['id']));
				
			}
		}elseif($wxlogintype[1] == 'announcementid'){

			$annouceid		=	$wxlogintype[2];

			$annouceInfo	=	$this -> select_once("admin_announcement",array('id'=>$annouceid));
			if(is_array($annouceInfo) && !empty($annouceInfo)){
			
			    $content = strip_tags($annouceInfo['content']);
			    $content = str_replace(array('&quot;', '&nbsp;', '<>'), array('', '', ''), $content);
			    
			    $Msg['title'] = $annouceInfo['title'];
			    $Msg['desc']  = mb_substr($content,0,80,'utf-8');
				
				$Msg['pic']	  = checkpic('',$this->config['sy_wx_logo']);
				$Msg['url']   = Url('wap',array('c'=>'announcement','id'=>$annouceInfo['id']));
				
			}
		}elseif ($wxlogintype[1] == 'jobtelid' && $this -> config['com_link_look'] == '2'){
            // 微信扫码查看职位联系方式
            $jobid = $wxlogintype[2];
            $job = $this->select_once('company_job', array('id' => $jobid), '`uid`,`name`,`is_link`,`link_id`');
            $com = $this->select_once('company', array('uid' => $job['uid']), '`name`,`linkphone`,`linktel`,`linkman`,`address`');
            if ($job['is_link'] == 1) {
                // 默认联系方式
                $tel = !empty($com['linktel']) ? $com['linktel'] : $com['linkphone'];
                $address = $com['address'];
                $linkman = $com['linkman'];
            } elseif ($job['is_link'] == 2) {
                // 新联系方式
                $link = $this->select_once('company_job_link', array('id' => $job['link_id']));
                if (isset($link)) {
                    $tel = !empty($link['link_moblie']) ? $link['link_moblie'] : $link['link_phone'];
                    $address = $link['link_address'];
                    $linkman = $link['link_man'];
                }
            }
            if (!empty($tel) || !empty($address)) {
                $comurl = Url('wap', array('c' => 'company', 'a' => 'show', 'id' => $job['uid']));
                $joburl = Url('wap', array('c' => 'job', 'a' => 'comapply', 'id' => $jobid));
                $Return['centerStr'] = "<Content><![CDATA[企业：<a href=\"" . $comurl . "\">" . $com['name'] . "</a>\n职位：<a href=\"" . $joburl . "\">" . $job['name'] . "</a>";
                if (!empty($linkman)) {
                    $Return['centerStr'] .= "\n联系人：" . $linkman;
                }
                if (!empty($tel)) {
                    $Return['centerStr'] .= "\n联系电话：" . $tel;
                }
                if (!empty($address)) {
                    $Return['centerStr'] .= "\n地址：" . $address;
                }

                $Return['centerStr'] .= ']]></Content>';
            } else {
                $Return['centerStr'] = '<Content><![CDATA[联系方式获取失败]]></Content>';
            }
            $Return['MsgType'] = 'text';
            return $Return;
        }elseif ($wxlogintype[1] == 'parttelid'){
            // 微信扫码查看职位联系方式
            $partid     =   $wxlogintype[2];
            $part       =   $this->select_once('partjob', array('id' => $partid), '`uid`,`name`,`com_name`,`linkman`,`linktel`,`address`');
            $tel        =   $part['linktel'];
            $address    =   $part['address'];
            $linkman    =   $part['linkman'];

            if (!empty($tel) || !empty($address)) {
                $comurl = Url('wap', array('c' => 'company', 'a' => 'show', 'id' => $part['uid']));
                $joburl = Url('wap', array('c' => 'part', 'a' => 'show', 'id' => $partid));
                $Return['centerStr'] = "<Content><![CDATA[企业：<a href=\"" . $comurl . "\">" . $part['com_name'] . "</a>\n兼职：<a href=\"" . $joburl . "\">" . $part['name'] . "</a>";
                if (!empty($linkman)) {
                    $Return['centerStr'] .= "\n联系人：" . $linkman;
                }
                if (!empty($tel)) {
                    $Return['centerStr'] .= "\n联系电话：" . $tel;
                }
                if (!empty($address)) {
                    $Return['centerStr'] .= "\n地址：" . $address;
                }

                $Return['centerStr'] .= ']]></Content>';
            } else {
                $Return['centerStr'] = '<Content><![CDATA[联系方式获取失败]]></Content>';
            }
            $Return['MsgType']   = 'text';
            return $Return;
        }elseif ($wxlogintype[1] == 'comtelid'){
		    // 微信扫码查看职位联系方式
		    $comid  =  $wxlogintype[2];
		    $com    =  $this->select_once('company',array('uid'=>$comid),'`name`,`linkphone`,`linktel`,`linkman`,`address`');
		    
		    if (!empty($com['linkphone']) || !empty($com['linktel'])){
		        $tel = !empty($com['linkphone']) ? $com['linkphone'] : $com['linktel'];
		        $Return['centerStr'] = "<Content><![CDATA[企业：".$com['name'];
		        if (!empty($com['linkman'])){
		            $Return['centerStr'] .= "\n联系人：" . $com['linkman'];
		        }
		        if (!empty($com['linkphone']) || !empty($com['linktel'])){
		            $Return['centerStr'] .= "\n联系电话：" . $tel;
		        }
		        if (!empty($com['address'])){
		            $Return['centerStr'] .= "\n地址：" . $com['address'];
		        }
		        
		        $Return['centerStr'] .=']]></Content>';
		    }else{
		        $Return['centerStr'] = '<Content><![CDATA[联系方式获取失败]]></Content>';
		    }
		    $Return['MsgType']   = 'text';
		    return $Return;
		}elseif($wxlogintype[1] == 'partid'){
		    // 兼职场景码
		    $partid = $wxlogintype[2];
		    
		    $part = $this -> select_once('partjob',array('id'=>$partid),'id,uid,name,com_name,content');
		    if(is_array($part) && !empty($part)){
		        $com = $this -> select_once('company',array('uid'=>$part['uid']),'id,uid,name,com_name,content');
		        $Msg['title'] = "招聘:".$part['name']." - ".$part['com_name'];
		        $Msg['desc']  = strip_tags($part['content']);
		        $Msg['url']   = Url('wap',array('c'=>'part','a'=>'show','id'=>$part['id']));
		        // 判断logo
		        if ((!empty($com['logo'])  && $com['logo_status']==0)){
		            $Msg['pic'] = checkpic($com['logo'],$this->config['sy_wx_logo']);
		        }else{
		            $Msg['pic'] = checkpic($this->config['sy_wx_logo']);
		        }
		    }
		}elseif($wxlogintype[1] == 'ruid'){
            // 邀请注册
            $uid = $wxlogintype[2];

            $member = $this -> select_once('member',array('uid'=>$uid),'uid,username');
            $Msg['title'] = "您的好友{$member['username']}，邀请您加入{$this->config['sy_webname']}，一起找工作招人才！";
            $Msg['pic']	  = checkpic('',$this->config['sy_wx_logo']);
            $Msg['url']   = Url('wap',array('c'=>'register','uid'=>$uid));
            
		}elseif($wxlogintype[1] == 'gongzhaoid'){
		    // 公招
		    $gzid		=	$wxlogintype[2];
		    
		    $gzInfo	=	$this -> select_once("gongzhao",array('id'=>$gzid));
		    if(is_array($gzInfo) && !empty($gzInfo)){
		        
		        $content = strip_tags($gzInfo['content']);
		        $content = str_replace(array('&quot;', '&nbsp;', '<>'), array('', '', ''), $content);
		        
		        $Msg['title'] = $gzInfo['title'];
		        $Msg['desc']  = mb_substr($content,0,80,'utf-8');
		        
		        $Msg['pic']	  = checkpic('',$this->config['sy_wx_logo']);
		        $Msg['url']   = Url('wap',array('c'=>'gongzhao','a'=>'show','id'=>$gzInfo['id']));
		    }
		}elseif($wxlogintype[1] == 'zphid'){
            // 现场招聘会
            $zphid = $wxlogintype[2];
            include_once ('zph.model.php');
            $zphM = new zph_model($this->db, $this->def);
            $zphInfo = $zphM->getInfo(array("id"=>$zphid), array('pic'=>1));
            if(is_array($zphInfo) && !empty($zphInfo)){
                $Msg['title'] = $zphInfo['title'];
                $Msg['desc'] = mb_substr($zphInfo['body'],0,80,'utf-8');
                $Msg['pic'] = $zphInfo['is_themb_wap_n'];
                $Msg['url'] = Url('wap',array('c'=>'zph','a'=>'show','id'=>$zphid));
            }
        }

		if(!empty($Msg)){
			$Return['centerStr'] = $this->Handle(array(),$Msg);
			$Return['MsgType']   = 'news';
		}else{
			$Return['centerStr'] = '<Content><![CDATA[二维码已失效！]]></Content>';
			$Return['MsgType']   = 'text';
		}
		return $Return;
	}
	/******微信场景码 小程序**********/
	function getXcxPubLink($wxloginid){
   	 	
		$wxlogintype	=	explode('_',$wxloginid);
		if($wxlogintype[1] == 'jobid'){
			$jobid = $wxlogintype[2];

			$jobInfo = $this -> select_once("company_job",array('id'=>$jobid));
			if(is_array($jobInfo) && !empty($jobInfo)){
			
				$Msg['title'] = "招聘:".$jobInfo['name']." - ".$jobInfo['com_name'];
				$Msg['appid']			=	$this->config['sy_xcxappid'];
				$Msg['thumb_media_id']	=	$this->config['sy_xcxmedia'];
				$Msg['pagepath']   = '/pages/job/show?id='.$jobInfo['id'];
				
			}
		}elseif($wxlogintype[1] == 'companyid'){

			$comid		=	$wxlogintype[2];

			$comInfo	=	$this -> select_once("company",array('uid'=>$comid));
			if(is_array($comInfo) && !empty($comInfo)){
			
				$Msg['title']			= $comInfo['name'];
				
				$Msg['appid']			=	$this->config['sy_xcxappid'];
				
				$Msg['pagepath']		= '/pages/company/show?id='.$comInfo['uid'];
				$Msg['thumb_media_id']	=	$this->config['sy_xcxmedia'];
				
			}
		}elseif($wxlogintype[1] == 'resumeid'){

			$resid		=	$wxlogintype[2];

			$resInfo	=	$this -> select_once("resume_expect",array('id'=>$resid));

			if(is_array($resInfo) && !empty($resInfo)){

				require_once ('resume.model.php');
				$expectM	=	new resume_model($this->db, $this->def);
				$expect		=   $expectM -> getExpect(array('id'=>$resInfo['id']),array('needCache'=>1));
		
				$Msg['title'] = "意向岗位：".$resInfo['name']."学历:".$expect['edu_n']."，工作经验:".$expect['exp_n']."，期望薪资：".$expect['salary']."，期望工作地区:".$expect['city_classname'];
				
				$Msg['appid']			=	$this->config['sy_xcxappid'];
				
				$Msg['pagepath']		= '/pages/resume/show?id='.$resInfo['id'];
				$Msg['thumb_media_id']	=	$this->config['sy_xcxmedia'];
				
			}
		}elseif($wxlogintype[1] == 'articleid'){

			$newsid		=	$wxlogintype[2];

			$newsInfo	=	$this -> select_once("news_base",array('id'=>$newsid));
			$newsconInfo	=	$this -> select_once("news_content",array('nbid'=>$newsid));
			if(is_array($newsInfo) && !empty($newsInfo)){
			
				$Msg['title'] = $newsInfo['title'];
				
				$Msg['appid']			=	$this->config['sy_xcxappid'];
				
				$Msg['pagepath']		= '/pages/article/show?id='.$newsInfo['id'];
				$Msg['thumb_media_id']	=	$this->config['sy_xcxmedia'];
				
			}
		}elseif($wxlogintype[1] == 'announcementid'){

			$annouceid		=	$wxlogintype[2];

			$annouceInfo	=	$this -> select_once("admin_announcement",array('id'=>$annouceid));
			if(is_array($annouceInfo) && !empty($annouceInfo)){
			
				$Msg['title'] = $annouceInfo['title'];
				
				$Msg['appid']			=	$this->config['sy_xcxappid'];
				
				$Msg['pagepath']		= '/pages/gonggao/show?id='.$annouceInfo['id'];
				$Msg['thumb_media_id']	=	$this->config['sy_xcxmedia'];
				
			}
		}elseif($wxlogintype[1] == 'partid'){
		    // 兼职场景码
		    $partid = $wxlogintype[2];
		    
		    $part = $this -> select_once('partjob',array('id'=>$partid),'id,uid,name,com_name');
		    if(is_array($part) && !empty($part)){
		        
		        $Msg['title'] = "招聘:".$part['name']." - ".$part['com_name'];
		        $Msg['appid']			=	$this->config['sy_xcxappid'];
		        $Msg['thumb_media_id']	=	$this->config['sy_xcxmedia'];
		        $Msg['pagepath']   = '/pages/part/show?id='.$part['id'];
		    }
		}elseif($wxlogintype[1] == 'ruid'){
            // 邀请注册
            $uid = $wxlogintype[2];

            $member = $this -> select_once('member',array('uid'=>$uid),'uid,username');
            $Msg['title'] = "您的好友{$member['username']}，邀请您加入{$this->config['sy_webname']}，一起找工作招人才！";
            $Msg['appid']			=	$this->config['sy_xcxappid'];
            $Msg['thumb_media_id']	=	$this->config['sy_xcxmedia'];
            $Msg['pagepath']        =   '/pages/register/register?from=' . $uid;
            
		}elseif($wxlogintype[1] == 'gongzhaoid'){
		    // 公招
		    $gzid		=	$wxlogintype[2];
		    
		    $gzInfo	=	$this -> select_once("gongzhao",array('id'=>$gzid));
		    if(is_array($gzInfo) && !empty($gzInfo)){
		        
		        $Msg['title']           =  $gzInfo['title'];
		        $Msg['appid']			=  $this->config['sy_xcxappid'];
		        
		        $Msg['pagepath']		=  '/pages/gongzhao/show?id='.$gzInfo['id'];
		        $Msg['thumb_media_id']	=  $this->config['sy_xcxmedia'];
		    }
		}elseif($wxlogintype[1] == 'zphid'){
            //现场招聘会
            $zphid = $wxlogintype[2];
            $zphInfo = $this->select_once("zhaopinhui",array('id'=>$zphid));
            if(is_array($zphInfo) && !empty($zphInfo)){
                $Msg['title'] = $zphInfo['title'];
                $Msg['appid'] = $this->config['sy_xcxappid'];
                $Msg['pagepath'] = '/pages/zph/show?id='.$zphid;
                $Msg['thumb_media_id'] = $this->config['sy_xcxmedia'];
            }
        }
		return $Msg;
	}
	/******微信关键字匹配**********/
	function searchKeyword($wxid,$keyword)
	{
		global $config;

		if(!$config){
			include(PLUS_PATH.'config.php');
		}

		$centerStr = '';

		$keyword = trim($keyword);

		if($keyword){

			$zd = $this->select_all('wxzdkeyword');
			

			if(!empty($zd)){
                foreach ($zd as $key=>$value){
                    $exkey = @explode(',',$value['keyword']);
                    
                    foreach ($exkey as $v){
                        if(stripos($keyword,$v)!==false){
                            
                            $zdid = $value['id'];
                            break;
                        
                        }
                        
                    }
                    
                    
                }
			
				
				if(!empty($zdid)){
				    
				    $keywordList = $this->select_all('wxzdcon', array('kid' => $zdid,'orderby'=>array('sort,desc','id,desc')));
					//读取微信 token
					$Token = getToken();
					
					//客服消息发送接口链接
					$wxUrl = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$Token;

					foreach($keywordList as $k=>$v){

						if($v['content']!=''){

							$templateDate = array();

							if($v['msgtype']=='image'){
								$msgtype = 'image';
								$content = unserialize($v['content']);

								if($content['media_id']){

									$templateDate['image'] = array(
										'media_id'=>$content['media_id']
									);
								}

							}else if($v['msgtype']=='xcx'){
								$msgtype = 'miniprogrampage';
								$content = unserialize($v['content']);

								if($content){
									
									$templateDate['miniprogrampage'] = array(
										"title"			=>	$content['xcx_title'],
								        "appid"			=>	$content['xcx_appid'],
								        "pagepath"		=>	$content['xcx_pagepath'],
								        "thumb_media_id"=>	$content['media_id']
									);
								}

							}else if($v['msgtype']=='text'){
								$msgtype = 'text';
								$templateDate['text'] = array(
									'content'=>$v['content']
								);
								
							}

							if(!empty($templateDate)){

								$centerStr = 1;

								$templateDate['touser'] = $wxid;
								$templateDate['msgtype'] = $msgtype;

								$CurlReturn  = CurlPost($wxUrl,json_encode($templateDate,JSON_UNESCAPED_UNICODE));
								$return    = json_decode($CurlReturn,true);
							}
						}
					}

				}
			}
		}	
		$Return['centerStr'] = $centerStr;
		return $Return;				
	}
	
	
	/******微信用户绑定**********/
	function bindUser($wxid='')
	{
	
		$bindType = $this->isBind($wxid);
		$Return['MsgType']   = 'text';
		$Return['centerStr'] = $bindType['cenetrTpl'];
		return $Return;
		
	}

    /**
     * @desc 获取用户基本信息
     * @param $wxid
     * @return mixed
     */
    function getWxUser($wxid)
    {
        global $config;
        $Token  =   getToken();
        $Url    =   'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$Token.'&openid='.$wxid.'&lang=zh_CN';
        $CurlReturn =   CurlPost($Url);
        $UserInfo   =   json_decode($CurlReturn, true);
        return $UserInfo;
    }

	/******微信账户绑定判断**********/
	function isBind($wxid='',$usertype=1)
	{	
	
		if($wxid){
			
			$UserInfo    = $this->getWxUser($wxid);
			$unionid	 = $UserInfo['unionid'];
			
			if (!empty($unionid)){
			    
			    $User  =  $this->select_once('member',array('wxid'=>$wxid,'unionid'=>array('=',$unionid,'OR')),'`uid`,`username`,`usertype`,`wxid`,`unionid`');
			    
			}else{
			    
			    $User  =  $this->select_once('member',array('wxid'=>$wxid),'`uid`,`username`,`usertype`,`wxid`');
			}
			
			if($User['unionid']!='' && $User['wxid']!=$wxid)//原先已绑定开放平台 但未绑定公众号
			{
			    $this->update_once('member', array('wxid' => $wxid,'wxbindtime'=>time()), array('uid' => $User['uid']));
				$User['wxid']=$wxid;
			}
			if (empty($User['unionid']) && !empty($unionid)){  //原先只绑定公众号，没绑定开放平台
			    $this->update_once('member', array('unionid' => $unionid), array('uid' => $User['uid']));
			    $User['unionid']=$unionid;
			}
		}
		if(isset($User) && $User['uid']>0)
		{
			$urlLogin = $this->config['sy_wapdomain']."/index.php?c=login&bind=1&wxid=".$wxid."&unionid=".$unionid;
			if($User['usertype']!=$usertype)
			{
				switch($usertype){
					case '1':
						$User['cenetrTpl'] = "<Content><![CDATA[您的".$this->config['sy_webname']."帐号：".$User['username']."为企业帐号，请登录您的个人帐号进行绑定！ \n\n\n 您也可以<a href=\"".$urlLogin."\">点击这里</a>进行绑定其他帐号]]></Content>";
					break;
					case '2':
						$User['cenetrTpl'] = "<Content><![CDATA[您的".$this->config['sy_webname']."帐号：".$User['username']."为个人帐号，请登录您的企业帐号进行绑定！ \n\n\n 您可以<a href=\"".$urlLogin."\">点击这里</a>进行解绑定其他帐号]]></Content>";
					break;

				}
				
			}else{
				$User['bindtype'] = '1';
				$User['cenetrTpl'] = "<Content><![CDATA[您的".$this->config['sy_webname']."帐号：".$User['username']."已成功绑定！ \n\n\n 您也可以<a href=\"".$urlLogin."\">点击这里</a>进行解绑或绑定其他帐号]]></Content>";
			}
			
		}else{

			//$urlLogin = Url("wap",array("c"=>"login","wxid"=>$wxid,"unionid"=>$unionid));
			$urlLogin = $this->config['sy_wapdomain']."/index.php?c=login&wxid=".$wxid."&unionid=".$unionid;
			$User['cenetrTpl'] = '<Content><![CDATA[您还没有绑定帐号，<a href="'.$urlLogin.'">点击这里</a>进行绑定!]]></Content>';
		}

		return $User;
	}
	/******微信推荐职位**********/
	function recJob()
	{
		
		$time	=	time();
		$JobList	=	$this -> select_all('company_job', array('sdate' => array( '<=', $time) , 'status' => 0, 'r_status' => 1, 'rec_time' => array('>', $time), 'orderby' => 'lastupdate,desc', 'limit' => 5), '`id`,`name`,`com_name`,`lastupdate`');
		


		if(is_array($JobList) && !empty($JobList))
		{
			foreach($JobList as $key=>$value)
			{
				$Info['title'] = "【".$value['name']."】\n".$value['com_name'];
				$Info['pic'] = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
				$Info['url'] = Url("wap",array('c'=>'job','a'=>'comapply','id'=>$value['id']));
				$List[]        = $Info;
			}
			$Msg['title'] = '推荐职位';
			$Msg['pic']	=	checkpic('',$this->config['sy_wx_logo']);
			$Msg['url'] = Url("wap",array('c'=>'job'));
			$Return['centerStr'] = $this->Handle($List,$Msg);
			$Return['MsgType']   = 'news';
			
		}else{
			$Return['centerStr'] ='<Content><![CDATA[没有合适的职位！]]></Content>';
			$Return['MsgType']   = 'text';
		}
		
		return $Return;
	}
	
	/******微信回复图文消息模板构造**********/
	function Handle($List,$Msg)
	{

		$articleTpl = '<Content><![CDATA['.$Msg['title'].']]></Content>';

		$articleTpl .= '<ArticleCount>'.(count($List)+1).'</ArticleCount><Articles>';

		$centerTpl = "<item>
		<Title><![CDATA[%s]]></Title>  
		<Description><![CDATA[%s]]></Description>
		<PicUrl><![CDATA[%s]]></PicUrl>
		<Url><![CDATA[%s]]></Url>
		</item>";
		
		$articleTpl.=sprintf($centerTpl,$Msg['title'],$Msg['desc'],$Msg['pic'],$Msg['url']); 
		if(!empty($List)){
			foreach($List as $value)
			{	
				$articleTpl.=sprintf($centerTpl,$value['title'],'',$value['pic'],$value['url']);
			}
		}
		
		$articleTpl .= '</Articles>';
		return $articleTpl;
	}
	/******微信来源验证**********/
	function valid($echoStr,$signature,$timestamp,$nonce)
	{
      if($this->checkSignature($signature,$timestamp,$nonce)){
      	echo $echoStr;	
      	exit;
      }
	}
	
	/******微信验证函数**********/
	function checkSignature($signature, $timestamp,$nonce)
	{   		
		$token = $this->config['wx_token'];
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature  && $token!=''){
			return true;
		}else{
			return false;
		}
	}
	/******微信数组转换**********/
	function ArrayToString($obj,$withKey=true,$two=false)
	{
		if(empty($obj))	return array();
		$objType=gettype($obj);
		if ($objType=='array') {
			$objstring = "array(";
			foreach ($obj as $objkey=>$objv) {
				if($withKey)$objstring .="\"$objkey\"=>";
				$vtype =gettype($objv) ;
				if ($vtype=='integer') {
				  $objstring .="$objv,";
				}else if ($vtype=='double'){
				  $objstring .="$objv,";
				}else if ($vtype=='string'){
				  $objv= str_replace('"',"\\\"",$objv);
				  $objstring .="\"".$objv."\",";
				}else if ($vtype=='array'){
				  $objstring .="".$this->ArrayToString($objv,false).",";
				}else if ($vtype=='object'){
				  $objstring .="".$this->ArrayToString($objv,false).",";
				}else {
				  $objstring .="\"".$objv."\",";
				}
			}
			$objstring = substr($objstring,0,-1)."";
			return $objstring.")\n";
		}
	}
	function markLog($wxid,$wxuser,$content,$reply){
        
	    $this->insert_into('wxlog', array('wxid' => $wxid, 'wxuser' => $wxuser, 'content' => $content, 'reply' => $reply, 'time' => time()));
	}

	//微信通知列表
	
	public function getWxmsgList($whereData, $data = array()) {
	    
	    $field  =  empty($data['field']) ? '*' : $data['field'];
	    
	    $List   =  $this -> select_all('wx_msg', $whereData, $field);
	    


	    if(!empty($List)){

	    	$adminuids	=	array();
	    	$muids	=	array();
	    	$adminname	=	array();
	    	$membername	=	array();
	    	
	    	foreach($List as $k=>$v){

	    		if($v['utype']==99){//后台管理员

	    			$adminuids[] = $v['uid'];
	    		}
	    	}

	    	if(!empty($adminuids)){
	    	    $adminuids =  array_unique($adminuids);
	    		$admins = $this -> select_all('admin_user',array('uid'=>array('in',pylode(',',$adminuids))));

	    		foreach($admins as $ak=>$av){

	    			$adminname[$av['uid']] =  $av['username'];
	    		}
	    	}
	    	// 按用户类型处理列表中用户数据
	    	$List  =  $this->getUserByUsertype($List);
	    	
	    	foreach($List as $lk=>$lv){
	    		
	    		if($lv['utype']=='99'){

	    			$List[$lk]['username']  = 	$adminname[$lv['uid']];

	    			$List[$lk]['utype_n']	=	'管理员';

	    		} else{

	    			if($lv['utype']=='1'){
	    				$List[$lk]['utype_n']	=	'个人';
	    			}else if($lv['utype']=='2'){
	    				$List[$lk]['utype_n']	=	'企业';
	    			}else if($lv['utype']=='3'){
	    				$List[$lk]['utype_n']	=	'猎头';
	    			}else if($lv['utype']=='4'){
	    				$List[$lk]['utype_n']	=	'培训';
	    			}
	    		}

	    		$List[$lk]['ctime_n']  		=	date('Y-m-d H:i:s',$lv['ctime']);

	    		$body_arr  					=	unserialize($lv['body']);
	    		
	    		$body_html 					= 	'';

	    		foreach($body_arr as $bv){
	    			$body_html .= $bv.'</br>';
	    		}	

	    		$List[$lk]['body_html'] 	=	$body_html;
	    		
	    		include (CONFIG_PATH.'db.data.php');
	    		if ($lv['mbconfig'] == 'wxmbdiy'){
	    		    $List[$lk]['mbconfig_n'] 	=	'推广营销';
	    		}else{
	    		    $List[$lk]['mbconfig_n'] 	=	$arr_data['wxmbType'][$lv['mbconfig']];
	    		}
	    	}
	    }

	    return $List;
	}
	//企业微信通知列表
	
	public function getQyWxmsgList($whereData, $data = array()) {
	    
	    $field  =  empty($data['field']) ? '*' : $data['field'];
	    
	    $List   =  $this -> select_all('wx_qymsg', $whereData, $field);
	    


	    if(!empty($List)){

	    	$adminuids	=	array();
	    	$adminname	=	array();
	    	
	    	foreach($List as $k=>$v){

	    		$adminuids 	= 	array_merge($adminuids,explode(',',$v['uids']));
	    		$adminuids	=	array_unique($adminuids);
	    	}

	    	if(!empty($adminuids)){

	    		$admins = $this -> select_all('admin_user',array('uid'=>array('in',pylode(',',$adminuids))));

	    		foreach($admins as $ak=>$av){

	    			$adminname[$av['uid']] =  $av['username'];

	    		}
	    	}


	    	foreach($List as $lk=>$lv){
	    		
	    		$uids = explode(',',$lv['uids']);
	    		$touser=explode(',',$lv['touser']);
	    		$username	=	array();

	    		if(!empty($uids)){

	    			foreach ($uids as $key => $value) {

	    				if(isset($adminname[$value])){

	    					$username[]  = 	$adminname[$value];

	    				}
	    			}
	    		}
	    		if(count($username)>3){
	    			$List[$lk]['username'] 		= 	implode(',',array_slice($username,0,3));
	    			$List[$lk]['username_all']	=	implode(',',$username);
	    		}else{
	    			$List[$lk]['username']		=	implode(',',$username);
	    		}
    			
	    		if(count($touser)>3){
	    			$List[$lk]['tousers'] 		= 	implode(',',array_slice($touser,0,3));
	    			$List[$lk]['tousers_all']	=	implode(',',$touser);
	    		}else{
	    			$List[$lk]['tousers']		=	implode(',',$touser);
	    		}
    			

	    		$List[$lk]['ctime_n']  		=	date('Y-m-d H:i:s',$lv['ctime']);

	    		$body_arr  					=	unserialize($lv['body']);
	    		
	    		$body_html 					= 	'';

	    		foreach($body_arr as $bv){

	    			preg_match_all('/<div\s[^>]+>.*?<\/div>/',$bv,$out);
	    			
	    			if(!empty($out[0])){
	    				foreach ($out[0] as $ok => $ov) {
	    					$body_html .= strip_tags($ov).'</br>';
	    				}
	    				
	    			}else{
	    				$body_html .= strip_tags($bv).'</br>';
	    			}
	    			
	    		}	

	    		$List[$lk]['body_html'] 	=	$body_html;
	    		
	    	}
	    }

	    return $List;
	}
	//删除微信模板消息
	public function delWxmsg($data = array()) {
	    
	    if(!empty($data['where'])){
	        
	        $where	=  $data['where'];
		
	        $nid	=  $this -> delete_all('wx_msg', $where, '');
	        
	        return $nid;
	    }
	}
	public function delQyWxmsg($data = array()){

		if(!empty($data['where'])){
	        
	        $where	=  $data['where'];
		
	        $nid	=  $this -> delete_all('wx_qymsg', $where, '');
	        
	        return $nid;
	    }
	}
	/** 
	 * @wxid    微信公众号绑定的唯一识别ID
	 * @tempid	不同消息模板的识别ID，具体在公众号中查看
	 * @url     消息模板点击链接：一般指向触屏版
	 * @data    消息模板具体内容
     */
	function sendWxTemplate($wxid,$tempid,$url,$data,$sdata=array(), $minipath = ''){

        global $config;
		if(!$config){
			include(PLUS_PATH.'config.php');
		}
	    
		if(!empty($wxid)){
			//读取微信 token
			$Token = getToken();
			
			//模板消息发送接口链接
			$wxUrl = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$Token;
			
			//构建模板参数
			$templateDate = array("touser"=>$wxid,
								  "template_id"=>$tempid,
								  "topcolor"=>"#FF0000",
								  "data"=>$data
							);

            if($minipath && !empty($config['wx_xxtz_tz']) && $config['wx_xxtz_tz'] == 2 && !empty($config['sy_xcxappid'])){
                $templateDate['miniprogram']['appid'] = $config['sy_xcxappid'];
                $templateDate['miniprogram']['pagepath'] = $minipath;
            }else{
                $templateDate['url'] = $url;
            }
			
			$CurlReturn  =  CurlPost($wxUrl,json_encode($templateDate));
			$return      =  json_decode($CurlReturn,true);

            if($sdata['uid'] && $sdata['utype']){

                $sdata['status']  =  $return['errcode'];
                $sdata['msg']     =  $return['errmsg'];

                $this->insert_into('wx_msg',$sdata);
            }
		}else{
			$return['errcode']= -1;
			$return['errmsg']='未关注公众号';
		}

		return $return;
    }

    /**
     * 职位申请通知 微信消息模板
     * @wxid    微信公众号用户绑定的唯一识别ID
     * @uid     申请人用户ID
     * @jobid   申请职位ID
     */
    function sendWxJob($uid,$jobid){

        global $config;
        if($config['wx_xxtz']!='1'  || (!$config['wx_mbsqzw'] && !$config['wx_mbsqzwlm']))
        {
            return true;
        }
        if($uid && $jobid)
        {
            //验证传入的职位ID （此处主要考虑 批量申请）
            if(is_array($jobid))
            {
                $Jids = pylode(",",$jobid);

            }else{

                $Jids = pylode(",",@explode(',',$jobid));

            }

            //提取绑定微信的用户
            $comList = $this->select_all('company_job', array('id' => array('in', pylode(',', $Jids))), '`uid`,`com_name`,`name`');

            if(is_array($comList) && !empty($comList))
            {
                $Mid    =   array();
                foreach($comList as $value){

                    $Mid[]					= $value['uid'];
                    $Comname[$value['uid']] = $value['com_name'];
                    $Jobname[$value['uid']][] = $value['name'];
                }

                $usertList  =   $this->select_all('member', array('uid' => array('in', pylode(',', $Mid))), '`uid`,`wxid`');

                if(is_array($usertList) && !empty($usertList))
                {

                    //查询申请用户基本信息
                    $Expect = $this->select_once('resume_expect', array('uid' => intval($uid), 'defaults' => 1));
                    include PLUS_PATH."/city.cache.php";
                    include PLUS_PATH."/user.cache.php";
                    foreach($usertList as $value){

                        //转换数组中字符编码，微信只支持UTF-8
                        $First		= $Comname[$value['uid']].'，您好，您发布的职位：'.@implode(',',$Jobname[$value['uid']]).' 收到一份新简历!';
                        $Iname		= $Expect['uname'];
                        $Edu		= $userclass_name[$Expect['edu']];
                        $Exp		= $userclass_name[$Expect['exp']];
                        if($Expect['city_classid']){
                            $cityids = @explode(',',$Expect['city_classid']);
                            $CityName = array();
                            foreach($cityids as $citykey=>$cityvalue){

                                $CityName[] = $city_name[$cityvalue];
                            }
                            $City = implode(',',$CityName);
                        }
                        //$City		= $city_name[$Expect['provinceid']]." ".$city_name[$Expect['cityid']]." ".$city_name[$Expect['three_cityid']];

                        $Sarlary	= salaryUnit($Expect['minsalary'], $Expect['maxsalary']);
                        $Remark		= '详情请登录 '.$config['sy_webname'].' 及时查阅!';
                        $Url 		= $this->config['sy_wapdomain']."/member/index.php?c=hr";
                        if (!empty($config['wx_mbsqzwlm'])){
                        	$Exp		=   strlen($Exp)>5?mb_substr($Exp,0,5,'utf-8'):$Exp;
                            // 类目模板
                            $Tempid = $config['wx_mbsqzwlm'];
                            $jobName=	@implode(',',$Jobname[$value['uid']]);
                            $TempDate['thing1']	           = array('value'=>$Iname,'color'=>'');//投递人
                            $TempDate['thing2']	           = array('value'=>mb_substr($jobName, 0, 18),'color'=>'');//投递职位
                            $TempDate['short_thing7']	   = array('value'=>mb_substr($Exp, 0, 18),'color'=>'');//工作年限
                            $TempDate['character_string9'] = array('value'=>$Sarlary == '面议' ? '-' : preg_replace("/[^0-9-]/", "", $Sarlary),'color'=>'');//期望薪资
                            $TempDate['thing10']	       = array('value'=>mb_substr($City, 0, 18),'color'=>'');//期望工作地

                            $tbody	=	array(
                                0	=>	'投递人：'.$Iname,
                                1	=>	'投递职位：'.mb_substr($jobName, 0, 18),
                                2	=>	'工作年限：'.$Exp,
                                3	=>	'期望薪资：'.$Sarlary == '面议' ? '-' : preg_replace("/[^0-9-]/", "", $Sarlary),
                                4	=>	'期望工作地：'.mb_substr($City, 0, 18),
                                5   =>  $Url
                            );
                        }else{
                            // 历史模板
                            $Tempid = $config['wx_mbsqzw'];

                            $TempDate['first']	= array('value'=>$First,'color'=>'');//标题
                            $TempDate['keyword1']	= array('value'=>$Iname,'color'=>'');//姓名
                            $TempDate['keyword2']	= array('value'=>$Edu,'color'=>'');//学历
                            $TempDate['keyword3']	= array('value'=>$Exp,'color'=>'');//工作经验
                            $TempDate['keyword4']	= array('value'=>$City,'color'=>'');//工作经验
                            $TempDate['keyword5']	= array('value'=>$Sarlary,'color'=>'');//薪资待遇
                            $TempDate['remark']	= array('value'=>$Remark,'color'=>'');//备注信息

                            $tbody	=	array(
                                0	=>	$First,
                                1	=>	'求职者：'.$Iname,
                                2	=>	'学历：'.$Edu,
                                3	=>	'工作经验：'.$Exp,
                                4	=>	'工作地点：'.$City,
                                5	=>	'需求薪资：'.$Sarlary,
                                6	=>	$Remark,
                                7   =>  $Url
                            );
                        }

                        $minipath = 'pson/pages/commember/hr/index';

                        $sdata	=	array(
                            'uid'		=>	$value['uid'],
                            'utype'		=>	2,
                            'wxid'		=>	$value['wxid'],
                            'ctime'		=>	time(),
                            'mbconfig'	=>	'wx_mbsqzw',
                            'body'		=>	serialize($tbody)
                        );

                        $result = $this->sendWxTemplate($value['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
                        if ($result['errcode'] != 0 ){//判断是否发送成功，成功errcode=0
                            return false;
                        }else{
                            return true;
                        }
                    }
                }
            }
        }

    }
    /**
     * 面试邀请 微信消息模板
     * @id      面试邀请通知信息
     * @jobid   申请职位ID
     */
    function sendWxresume($data){

        global $config;
        if($config['wx_xxtz']!='1' || (!$config['wx_mbyqms'] && !$config['wx_mbyqmslm']))
        {
            return true;
        }
        if($data['uid'])
        {
            //提取绑定微信的用户
            $userInfo    =   $this->select_once('member', array('uid' => $data['uid']), '`uid`,`username`,`wxid`');

            if(is_array($userInfo))
            {
                //转换数组中字符编码，微信只支持UTF-8
                $First		= $userInfo['username'].'，恭喜你!您收到公司的面试邀请啦！';
                $Job		= mb_substr($data['jobname'], 0, 18);
                $Company	= $data['fname'];
                $Time		= $data['intertime'];
                $Address	= mb_substr($data['address'], 0, 20);
                $Contact	= $data['linkman'];
                $Tel		= $data['linktel'];
                $Remark		= $data['content'].'详情请登录 '.$config['sy_webname'].' 及时查阅!';
                $Url 		= $this->config['sy_wapdomain']."/member/index.php?c=invite";
                if (!empty($config['wx_mbyqmslm'])){
                    // 类目模板
                    $Tempid = $config['wx_mbyqmslm'];

                    $TempDate['thing1']	= array('value'=>$Job,'color'=>'');//职位名称
                    $TempDate['thing2']	= array('value'=>$Company,'color'=>'');//公司名称
                    $TempDate['time3']	= array('value'=>$Time,'color'=>'');//面试时间
                    $TempDate['thing4']	= array('value'=>$Address,'color'=>'');//面试地点
                    $TempDate['thing5']	= array('value'=>$Contact,'color'=>'');//联系人

                    $tbody	=	array(
                        0	=>	'职位名称：'.$Job,
                        1	=>	'公司名称：'.$Company,
                        2	=>	'面试时间：'.$Time,
                        3	=>	'面试地点：'.$Address,
                        4	=>	'联系人：'.$Contact,
                        5   =>  $Url
                    );
                }else{
                    // 历史模板
                    $Tempid = $config['wx_mbyqms'];

                    $TempDate['first']	 = array('value'=>$First,'color'=>'');//标题
                    $TempDate['job']	 = array('value'=>$Job,'color'=>'');//面试职位
                    $TempDate['company'] = array('value'=>$Company,'color'=>'');//面试公司
                    $TempDate['time']	 = array('value'=>$Time,'color'=>'');//面试时间
                    $TempDate['address'] = array('value'=>$Address,'color'=>'');//面试地址
                    $TempDate['contact'] = array('value'=>$Contact,'color'=>'');//联系人
                    $TempDate['tel']	 = array('value'=>$Tel,'color'=>'');//联系电话
                    $TempDate['remark']	 = array('value'=>$Remark,'color'=>'');//面试信息

                    $tbody	=	array(
                        0	=>	$First,
                        1	=>	'面试职位：'.$Job,
                        2	=>	'所属公司：'.$Company,
                        3	=>	'面试时间：'.$Time,
                        4	=>	'面试地点：'.$Address,
                        5	=>	'联系人：'.$Contact,
                        6	=>	'联系电话：'.$Tel,
                        7	=>	$Remark,
                        8   =>  $Url
                    );
                }

                $minipath = 'pson/pages/usermember/invite/index';

                $sdata	=	array(
                    'uid'		=>	$userInfo['uid'],
                    'utype'		=>	1,
                    'wxid'		=>	$userInfo['wxid'],
                    'ctime'		=>	time(),
                    'mbconfig'	=>	'wx_mbyqms',
                    'body'		=>	serialize($tbody)
                );

                $result = $this->sendWxTemplate($userInfo['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
                if ($result['errcode'] != 0){
                    return false;
                }else{
                    return true;
                }
            }
        }
    }

    /**
     * 充值支付 微信消息模板
     */
    function sendWxPay($data){

        global $config;
        if(!$config){
            include(PLUS_PATH.'config.php');
        }
        if($config['wx_xxtz']!='1' || (!$config['wx_mbcztx'] && !$config['wx_mbcztxlm']))
        {
            return true;
        }
        if($data['wxid'])
        {
            //转换数组中字符编码，微信只支持UTF-8
            $First		= $data['first'];
            $UserName	= $data['username'];
            $Order		= $data['order'];
            $Price		= $data['price'];
            $Time		= $data['time'];
            $PayType	= $data['paytype'];
            $Info		= $data['info'];
            $Remark		= '感谢您的支持,详情请登录 '.$config['sy_webname'].' !';
            $Url 		= $this->config['sy_wapdomain'] . '/member/index.php?c=paylog';
            if (!empty($config['wx_mbcztxlm'])){
                // 类目模板
                $Tempid = $config['wx_mbcztxlm'];

                $TempDate['thing1']	            = array('value'=>$Info,'color'=>'');//套餐名称
                $TempDate['amount2']	        = array('value'=>$Price,'color'=>'');//套餐价格
                $TempDate['time3']	            = array('value'=>$Time,'color'=>'');//充值时间
                $TempDate['character_string4']	= array('value'=>$Order,'color'=>'');//订单编号

                $tbody	=	array(
                    0	=>	'套餐名称：'.$Info,
                    1	=>	'套餐价格：'.$Price,
                    2	=>	'充值时间：'.$Time,
                    3	=>	'订单编号：'.$Order,
                    4   =>  $Url
                );
            }else{
                // 历史模板
                $Tempid = $config['wx_mbcztx'];

                $TempDate['first']	= array('value'=>$First,'color'=>'');//标题
                $TempDate['keyword1']	= array('value'=>$UserName,'color'=>'');//用户名
                $TempDate['keyword2']	= array('value'=>$Price,'color'=>'');//支付金额
                $TempDate['keyword3']	= array('value'=>$Info,'color'=>'');//消费类型
                $TempDate['keyword4']	= array('value'=>$PayType,'color'=>'');//支付方式
                $TempDate['keyword5']	= array('value'=>$Time,'color'=>'');//支付时间
                $TempDate['remark']	= array('value'=>$Remark,'color'=>'');

                $tbody	=	array(
                    0	=>	$First,
                    1	=>	'用户名：'.$UserName,
                    2	=>	'支付金额：'.$Price,
                    3	=>	'消费类型：'.$Info,
                    4	=>	'支付方式：'.$PayType,
                    5	=>	'支付时间：'.$Time,
                    6	=>	$Remark,
                    7   =>  $Url
                );
            }
            $minipath = '';
            if ($data['usertype'] == 1) { // 个人
                $minipath = 'pson/pages/usermember/finance/fklog';
            } elseif ($data['usertype'] == 2) { // 企业
                $minipath = 'pson/pages/commember/finance/fklog';
            } elseif ($data['usertype'] == 3) { // 猎头
            } elseif ($data['usertype'] == 4) { // 培训
                $Url = Url('wap',array('c'=>'login'));
            }

            $sdata	=	array(
                'uid'		=>	$data['uid'],
                'utype'		=>	$data['usertype'],
                'wxid'		=>	$data['wxid'],
                'ctime'		=>	time(),
                'mbconfig'	=>	'wx_mbcztx',
                'body'		=>	serialize($tbody)
            );

            $result = $this->sendWxTemplate($data['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
            if ($result['errcode'] !=0 ){
                return false;
            }else{
                return true;
            }
        }
    }

    /**
     * 职位审核 微信消息模板
     */
    function sendWxJobStatus($data){
        global $config;
        if($config['wx_xxtz']!='1')
        {
            return true;
        }
        // 职位审核通过（因类目模板和历史模板不同）
        if ($data['state'] == 1 && !$config['wx_mbzwsh'] && !$config['wx_mbzwshlm']){
            return true;
        }
        // 职位审核未通过（因类目模板和历史模板不同）
        if ($data['state'] == 3 && !$config['wx_mbzwsh'] && !$config['wx_mbzwshsblm']){
            return true;
        }

        if($data['jobid'])
        {
            //提取绑定微信的用户
            $User = $this->select_all('company_job', array('id' => array('in', pylode(',', $data['jobid']))), '`uid`,`com_name`,`name`,`lastupdate`');

            if(is_array($User)){
                foreach($User as $key=>$value){

                    $JobName[$value['uid']][] = $value['name'];
                    $Uid[] = $value['uid'];
                    $ComName[$value['uid']] = $value['com_name'];
                    $UpTime[$value['uid']] = date('Y年m月d日 H:i', $value['lastupdate']);
                }

                $Uid = array_unique($Uid);
                switch($data['state']){
                    case '0':$Status='未审核';
                        break;
                    case '1':$Status='审核通过';
                        break;
                    case '3':$Status='审核不通过';
                        break;
                }
                $Member = $this->select_all('member', array('uid' => array('in', pylode(',', $Uid))), '`wxid`,`uid`');

                if(is_array($Member)){
                    foreach($Member as $key=>$value){

                        $data['first'] = '尊敬的 '.$ComName[$value['uid']].',您好!您发布的职位有一条新的状态通知！';
                        //转换数组中字符编码，微信只支持UTF-8
                        $First		= $data['first'];
                        $JobName	= @implode(',',$JobName[$value['uid']]);
                        $Body		= $data['statusbody'];

                        $Remark		= '详情请登录 '.$config['sy_webname'].' !';
                        $Url 		= $this->config['sy_wapdomain'] . '/member/index.php?c=job';
                        if ($data['state'] == 1 && !empty($config['wx_mbzwshlm'])){
                            // 类目模板，审核通过
                            $Tempid = $config['wx_mbzwshlm'];

                            $TempDate['thing1'] = array('value'=>$JobName,'color'=>'');//职位名称
                            $TempDate['time2']  = array('value'=>$UpTime[$value['uid']],'color'=>'');//提交时间

                            $tbody	=	array(
                                0	=>	'职位名称：'.$JobName,
                                1	=>	'提交时间：'.$UpTime[$value['uid']],
                                2   =>  $Url
                            );
                        }elseif ($data['state'] == 3 && !empty($config['wx_mbzwshsblm'])){
                            // 类目模板，审核未通过
                            $Tempid = $config['wx_mbzwshsblm'];

                            $TempDate['thing1'] = array('value'=>$JobName,'color'=>'');//职位名称
                            $TempDate['time2']  = array('value'=>$UpTime[$value['uid']],'color'=>'');//提交时间
                            $TempDate['const3'] = array('value'=>$Body,'color'=>'');//驳回原因

                            $tbody	=	array(
                                0	=>	'职位名称：'.$JobName,
                                1	=>	'提交时间：'.$UpTime[$value['uid']],
                                2	=>	'驳回原因：'.$Body,
                                3   =>  $Url
                            );
                        }else{
                            // 历史模板
                            $Tempid = $config['wx_mbzwsh'];

                            $TempDate['first']	= array('value'=>$First,'color'=>'');//标题
                            $TempDate['keyword1']	= array('value'=>$JobName,'color'=>'');//审核职位
                            $TempDate['keyword2']	= array('value'=>$Status,'color'=>'');//审核状态
                            $TempDate['keyword3']	= array('value'=>$Body,'color'=>'');//具体原因
                            $TempDate['remark']	= array('value'=>$Remark,'color'=>'');

                            $tbody	=	array(
                                0	=>	$First,
                                1	=>	'职位名称：'.$JobName,
                                2	=>	'审核结果：'.$Status,
                                3	=>	'原因：'.$Body,
                                4	=>	$Remark,
                                5   =>  $Url
                            );
                        }

                        $minipath = 'pson/pages/commember/job/index';

                        $sdata	=	array(
                            'uid'		=>	$value['uid'],
                            'utype'		=>	2,
                            'wxid'		=>	$value['wxid'],
                            'ctime'		=>	time(),
                            'mbconfig'	=>	'wx_mbzwsh',
                            'body'		=>	serialize($tbody)
                        );

                        $result = $this->sendWxTemplate($value['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
                        if ($result['errcode'] != 0 ){//判断是否发送成功，成功errcode=0
                            return false;
                        }else{
                            return true;
                        }
                    }
                }
            }
        }
    }
    /**
     * 兼职审核 微信消息模板
     */
    function sendWxPartJobStatus($data){
        global $config;
        if($config['wx_xxtz']!='1')
        {
            return true;
        }
        // 兼职审核通过（因类目模板和历史模板不同）
        if ($data['state'] == 1 && !$config['wx_mbjzsh'] && !$config['wx_mbzwshlm']){
            return true;
        }
        // 兼职审核未通过（因类目模板和历史模板不同）
        if ($data['state'] == 3 && !$config['wx_mbjzsh'] && !$config['wx_mbzwshsblm']){
            return true;
        }

        if($data['jobid'])
        {
            //提取绑定微信的用户
            $User = $this->select_all('partjob', array('id' => array('in', pylode(',', $data['jobid']))), '`uid`,`com_name`,`name`,`lastupdate`');

            if(is_array($User)){
                foreach($User as $key=>$value){

                    $JobName[$value['uid']][] = $value['name'];
                    $Uid[] = $value['uid'];
                    $ComName[$value['uid']] = $value['com_name'];
                    $UpTime[$value['uid']] = date('Y年m月d日 H:i', $value['lastupdate']);
                }

                $Uid = array_unique($Uid);
                switch($data['state']){
                    case '0':$Status='未审核';
                        break;
                    case '1':$Status='审核通过';
                        break;
                    case '3':$Status='审核不通过';
                        break;
                }
                $Member = $this->select_all('member', array('uid' => array('in', pylode(',', $Uid))), '`wxid`,`uid`');

                if(is_array($Member)){
                    foreach($Member as $key=>$value){

                        $data['first'] = '尊敬的 '.$ComName[$value['uid']].',您好!您发布的兼职有一条新的状态通知！';
                        //转换数组中字符编码，微信只支持UTF-8
                        $First		= $data['first'];
                        $JobName	= @implode(',',$JobName[$value['uid']]);
                        $Body		= $data['statusbody'];

                        $Remark		= '详情请登录 '.$config['sy_webname'].' !';
                        $Url 		= $this->config['sy_wapdomain'] . '/member/index.php?c=part';
                        if ($data['state'] == 1 && !empty($config['wx_mbzwshlm'])){
                            // 类目模板，审核通过
                            $Tempid = $config['wx_mbzwshlm'];

                            $TempDate['thing1'] = array('value'=>$JobName,'color'=>'');//职位名称
                            $TempDate['time2']  = array('value'=>$UpTime[$value['uid']],'color'=>'');//提交时间
                            $tbody	=	array(
                                0	=>	'职位名称：'.$JobName,
                                1	=>	'提交时间：'.$UpTime[$value['uid']],
                                2   =>  $Url
                            );
                        }elseif ($data['state'] == 3 && !empty($config['wx_mbzwshsblm'])){
                            // 类目模板，审核未通过
                            $Tempid = $config['wx_mbzwshsblm'];

                            $TempDate['thing1'] = array('value'=>$JobName,'color'=>'');//职位名称
                            $TempDate['time2']  = array('value'=>$UpTime[$value['uid']],'color'=>'');//提交时间
                            $TempDate['const3'] = array('value'=>$Body,'color'=>'');//驳回原因
                            $tbody	=	array(
                                0	=>	'职位名称：'.$JobName,
                                1	=>	'提交时间：'.$UpTime[$value['uid']],
                                2	=>	'驳回原因：'.$Body,
                                3   =>  $Url
                            );
                        }else{
                            // 历史模板
                            $Tempid = $config['wx_mbjzsh'];

                            $TempDate['first']	= array('value'=>$First,'color'=>'');//标题
                            $TempDate['keyword1']	= array('value'=>$JobName,'color'=>'');//审核职位
                            $TempDate['keyword2']	= array('value'=>$Status,'color'=>'');//审核状态
                            $TempDate['keyword3']	= array('value'=>$Body,'color'=>'');//具体原因
                            $TempDate['remark']	= array('value'=>$Remark,'color'=>'');

                            $tbody	=	array(
                                0	=>	$First,
                                1	=>	'职位名称：'.$JobName,
                                2	=>	'审核结果：'.$Status,
                                3	=>	'原因：'.$Body,
                                4	=>	$Remark,
                                5   =>  $Url
                            );
                        }

                        $minipath = 'pson/pages/commember/part/index';

                        $sdata	=	array(
                            'uid'		=>	$value['uid'],
                            'utype'		=>	2,
                            'wxid'		=>	$value['wxid'],
                            'ctime'		=>	time(),
                            'mbconfig'	=>	'wx_mbjzsh',
                            'body'		=>	serialize($tbody)
                        );

                        $result = $this->sendWxTemplate($value['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
                        if ($result['errcode'] !=0 ){
                            return false;
                        }else{
                            return true;
                        }
                    }
                }
            }
        }
    }
    /**
     * 兼职报名 微信消息模板
     */
    function sendWxPart($data){
        global $config;
        if($config['wx_xxtz']!='1' || (!$config['wx_mbjzbm'] && !$config['wx_mbjzbmlm']))
        {
            return true;
        }
        if($data['wxid'])
        {
            //提取绑定微信的用户

            //转换数组中字符编码，微信只支持UTF-8
            $First		= $data['first'];
            $jobname	= $data['jobname'];
            $user		= $data['user'];
            $usertel	= $data['usertel'];
            $time		= $data['time'];
            $Remark		= '感谢您的支持,详情请登录 '.$config['sy_webname'].' !';
            $Url 		= $this->config['sy_wapdomain'] . "/member/index.php?c=partapply&partid=" . $data['jobid'];
            if (!empty($config['wx_mbjzbmlm'])){
                // 类目模板
                $Tempid = $config['wx_mbjzbmlm'];

                $TempDate['thing2']	        = array('value'=>$jobname,'color'=>'');//岗位名称
                $TempDate['time3']	        = array('value'=>$time,'color'=>'');//时间
                $TempDate['thing4']	        = array('value'=>$time,'color'=>'');//姓名
                $TempDate['phone_number5']	= array('value'=>$usertel,'color'=>'');//手机号

                $tbody	=	array(
                    0	=>	'岗位名称：'.$jobname,
                    1	=>	'时间：'.$time,
                    2	=>	'姓名：'.$user,
                    3	=>	'手机号：'.$usertel,
                    4   =>  $Url
                );
            }else{
                // 历史模板
                $Tempid = $config['wx_mbjzbm'];

                $TempDate['first']	= array('value'=>$First,'color'=>'');//标题
                $TempDate['keyword1']	= array('value'=>$jobname,'color'=>'');//用兼职名称
                $TempDate['keyword2']	= array('value'=>$user,'color'=>'');//姓名
                $TempDate['keyword3']	= array('value'=>$usertel,'color'=>'');//电话
                $TempDate['keyword4']	= array('value'=>$time,'color'=>'');//时间
                $TempDate['remark']	= array('value'=>$Remark,'color'=>'');

                $tbody	=	array(
                    0	=>	$First,
                    1	=>	'兼职名称：'.$jobname,
                    2	=>	'姓名：'.$user,
                    3	=>	'电话：'.$usertel,
                    4	=>	'时间：'.$time,
                    5	=>	$Remark,
                    6   =>  $Url
                );
            }
            $minipath = 'pson/pages/commember/partapply/index?partid=' . $data['jobid'];

            $sdata	=	array(
                'uid'		=>	$data['uid'],
                'utype'		=>	2,
                'wxid'		=>	$data['wxid'],
                'ctime'		=>	time(),
                'mbconfig'	=>	'wx_mbjzbm',
                'body'		=>	serialize($tbody)
            );

            $result = $this->sendWxTemplate($data['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
            if ($result['errcode'] !=0 ){
                return false;
            }else{
                return true;
            }
        }
    }

	//赏金职位流程通知
	function sendWxReward($data){
	    global $config;
		if($config['wx_xxtz']!='1' || !$config['wx_mbreward'])
		{
			return true;
		}
		if($data['wxid'])
		{
		
			$Tempid = $config['wx_mbreward'];
			
			
			$First		= $data['first'];
			$jobName	= $data['jobname'];
			$Status		= $data['statusinfo'];
			$Remark		= $data['remark'];
			$rinfo		= $data['rinfo'];
			$TempDate['first']	= array('value'=>$First,'color'=>'');
			$TempDate['keyword1']	= array('value'=>$jobName,'color'=>'');
			$TempDate['keyword2']	= array('value'=>$rinfo,'color'=>'');//申请信息
			$TempDate['keyword3']	= array('value'=>date('Y-m-d H:i:s'),'color'=>'');//申请时间
			$TempDate['keyword4']	= array('value'=>$Status,'color'=>'');
			$TempDate['remark']	= array('value'=>$Remark,'color'=>'');

			if ($data['usertype'] == 1) {
                $Url = $this->config['sy_wapdomain'] . '/member/index.php?c=rebates&type=cy';
                $minipath = 'pson/pages/usermember/rewardlog/index';
            } elseif ($data['usertype'] == 2) {
                $Url = $this->config['sy_wapdomain'] . '/member/index.php?c=jobpack&t=r';
                $minipath = 'pson/pages/commember/jobpack/index';
            }
			
			$tbody	=	array(
				0	=>	$First,
				1	=>	'招聘职位：'.$jobName,
				2	=>	'申请信息：'.$rinfo,
				3	=>	'申请时间：'.date('Y-m-d H:i:s'),
				4	=>	'当前进度：'.$Status,
				5	=>	$Remark,
			    6   =>  $Url
			);

			$sdata	=	array(
				'uid'		=>	$data['uid'],
				'utype'		=>	$data['usertype'],
				'wxid'		=>	$data['wxid'],
				'ctime'		=>	time(),
				'mbconfig'	=>	'wx_mbreward',
				'body'		=>	serialize($tbody)
			);

			$result = $this->sendWxTemplate($data['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
			if ($result['errcode'] != 0 ){//判断是否发送成功，成功errcode=0
				return false;
			}else{
				return true;
			}
		}
    }
	/** 
	 * 管理员通知 微信消息模板
	 *	$data=array(
			'first'=>通知内容
			'type'=>通知类型标题
			'title'=>自定义标题,优先级别大于type
	 	)
     */
	function sendWxAdmin($data=array()){
		global $config;

		if(!$config['wx_mbadmin'] && !$config['wx_mbadminlm'])
		{
			return true;
		}

		if($data['wxid'])
		{
			$wmtype		=	$this -> select_all('admin_wmtype', array('type' =>array('<>','')), '`type`,`name`');

			$titleType	=	array();

			foreach($wmtype as $wk=>$wv){

				$titleType[$wv['type']] = $wv['name'];

			}

			
			//转换数组中字符编码，微信只支持UTF-8
			$First		= $data['first'];
			
			$Title		= $data['title'] ? $data['title'] : ($titleType[$data['type']] ? $titleType[$data['type']] : '消息通知');
			$Remark		= '请及时登录管理后台进行处理!';
			$Url = $config['sy_weburl'] . '/wapadmin/';
			if(!empty($config['wx_mbadminlm'])){
				$Tempid 	=	$config['wx_mbadminlm'];
				$First		=   strlen($data['thing'])>19?mb_substr($data['thing'],0,15,'utf-8').'...':$data['thing'];

				if($data['customer']){
					$customer = strlen($data['customer'])>19?mb_substr($data['customer'],0,15,'utf-8').'...':$data['customer'];
				}else{
					$customer = '-';
				}
				$TempDate['thing7']	    = array('value'=>$Title,'color'=>'');//项目
			    $TempDate['thing3']	    = array('value'=>$First,'color'=>'');//结果
			    $TempDate['thing4']	    = array('value'=>$customer,'color'=>'');//客户
			    $tbody	=	array(
			        0	=>	'异常项目：'.$Title,
			        1	=>	'异常结果：'.$First,
			        2	=>	'客户名称：'.$customer,
			        3	=>	$Url,
			    );
			}else{

				$Tempid 	=	$config['wx_mbadmin'];

				$TempDate['first']		= array('value'=>'业务预警通知','color'=>'');//通知内容
				$TempDate['keyword1']	= array('value'=>$Title,'color'=>'');//通知类别
				$TempDate['keyword2']	= array('value'=>'后台','color'=>'');//通知范围
				$TempDate['keyword3']	= array('value'=>date('Y/m/d H:i'),'color'=>'');//发布时间
				$TempDate['keyword4']	= array('value'=>$First,'color'=>'');//通知内容
				$TempDate['remark']		= array('value'=>$Remark,'color'=>'');
				
				$tbody	=	array(
					0	=>	'业务预警通知',
					1	=>	'预警类别：'.$Title,
					2	=>	'预警范围：后台',
					3	=>	'发布时间：'.date('Y/m/d H:i'),
					4	=>	'预警内容：'.$First,
					5	=>	$Remark,
					6   =>  $Url
				);

			}

			$sdata	=	array(
				'uid'		=>	$data['uid'],
				'utype'		=>	99,
				'wxid'		=>	$data['wxid'],
				'ctime'		=>	time(),
				'mbconfig'	=>	'wx_mbadmin',
				'body'		=>	serialize($tbody)
			);
			
			$this->sendWxTemplate($data['wxid'],$Tempid,$Url,$TempDate,$sdata);
		}
	}
	
	/** 
	 * 发送订阅通知 微信消息模板
	 * $data['uid'] 要发送的用户id
	 * 
	 * wx_subscribetpl 订阅通知模板参数
     */
	function sendSubscribe($data = array()){
		global $config;
		$res			=	array(
			'errcode'	=>	8,
			'msg'		=>	''
		);
		if($config['wx_xxtz'] != 1 || (empty($config['wx_subscribetpl']) && empty($config['wx_mbsubscribelm']))){
			$res['msg']	=	'还未开通微信通知!';
			return $res;
		}
		//参数判断
		if(empty($data['uid']) || empty($data['content'])){
			$res['msg']	=	'缺少发送的信息!';
			return $res;
		}

		//获取用户的微信id
		$userInfo		=	$this -> select_once('member', array('uid' => $data['uid']), '`uid`,`usertype`,`wxid`');
		if(empty($userInfo) || empty($userInfo['wxid'])){
			$res['msg']	=	'该用户还未绑定微信!';
			return $res;
		}
		$Url = $this->config['sy_wapdomain'] . '/member/index.php?c=subscribeRecord&id='.$data['sid'];
		if (!empty($config['wx_mbsubscribelm'])){
            // 类目模板
            $Tempid = $config['wx_mbsubscribelm'];

            $thing29		=   strlen($data['content'])>19?mb_substr($data['content'],0,15,'utf-8').'...':$data['content'];

            $TempDate['thing16']	= array('value'=>$data['thing16'],'color'=>'');
            $TempDate['time17']	    = array('value'=>date('Y-m-d H:i:s'),'color'=>'');
            $TempDate['thing29']	= array('value'=>$thing29,'color'=>'');
            
            $tbody	=	array(
                0	=>	'活动名称：'.$data['thing16'],
                1	=>	'时间：'.date('Y-m-d H:i:s'),
                2	=>	'预约服务：'.$thing29,
                3	=>	$Url,
            );
        }else{
			$Tempid 		=	$config['wx_subscribetpl'];
				
			//转换数组中字符编码，微信只支持UTF-8
			$First			=	$data['First'] ? $data['First'] : '订阅通知';
			$content		=	$data['content'];
			$Remark			=	'感谢您的订阅,详情请登录 '.$config['sy_webname'].' !';

			$TempDate['first']		=	array('value' => $First, 'color'=>'');
			$TempDate['keyword1']	=	array('value' => $content, 'color'=>'');
			$TempDate['keyword2']	=	array('value' => '永久或进入会员中心关闭订阅', 'color'=>'');
			$TempDate['keyword3']	=	array('value' => date('Y-m-d H:i:s'), 'color'=>'');
			$TempDate['remark']		=	array('value' => $Remark, 'color'=>'');
			
	        $minipath = 'pson/pages/subscribe/record?id='.$data['sid'];
			
			$tbody	=	array(
				0	=>	$First,
				1	=>	'订阅内容：'.$content,
			    2   =>  '有效期：永久或进入会员中心关闭订阅',
			    3   =>  '时间：'.date('Y-m-d H:i:s'),
				4	=>	$Remark,
			    5   =>  $Url
			);
		}

        $sdata	=	array(
			'uid'		=>	$userInfo['uid'],
			'utype'		=>	$userInfo['usertype'],
			'wxid'		=>	$userInfo['wxid'],
			'ctime'		=>	time(),
			'mbconfig'	=>	'wx_subscribetpl',
			'body'		=>	serialize($tbody)
		);

		$this->sendWxTemplate($userInfo['wxid'], $Tempid, $Url, $TempDate,$sdata, $minipath);

		$res['errcode']	=	9;
		return $res;
	}

	//获取用户登录二维码
	function applyWxQrcode($wxloginid='', $type='', $uid = ''){
	    
		global $config;
		$ticket = '';
		if($config['wx_author']=='1'){
		    
			if($wxloginid){
				//查询识别ID对应的二维码是否存在或失效
				$wxqrcode  =  $this->select_once('wxqrcode', array('wxloginid' => $wxloginid, 'status' => 0));
				
				if(!empty($wxqrcode) && $wxqrcode['time'] >= (time()-86000)){
					$ticket  =  "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($wxqrcode['ticket']);
					return $ticket;
				}
			}
			
			if($type=='admin_wxloginid'){
				$randStr =   'adminlogin_'.time().rand(1000,9999);
			}else{
				$randStr =   time().rand(1000,9999);
			}
			
			$Url     =   'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.getToken();
			    
			$Data['expire_seconds']  =   86400;          //有效时间一天
			$Data['action_name']     =   'QR_STR_SCENE'; //临时二维码
			//$Data['action_info']['scene']['scene_id']   =   1000;   //登录场景值ID
			$Data['action_info']['scene']['scene_str']   =   $randStr;   //生成识别cookie串
			
			$CurlReturn      =   CurlPost($Url,json_encode($Data));
			
			//{" ticket":"gqen8twaaaaaaaaaas5odhrwoi8vd2vpeglulnfxlmnvbs9xlzayu1poumcwci05mluxvtrsne5wy28aagsey8nzawsauqea",
			// "expire_seconds":86400,"url":"http:\="" \="" weixin.qq.com\="" q\="" 02szhrg0r-92u1u4r4npco"}
			$Return          =   json_decode($CurlReturn,true);
			//插入数据库
			if($Return['ticket']){
				
			    $warr    =   array('wxloginid' => $randStr, 'ticket' => $Return['ticket'], 'time' => time(), 'status' => 0);
				
			    if($type=='wxadminbind' && isset($_SESSION['auid'])){
				    
					$warr['auid'] = $_SESSION['auid'];
				}else{
				    
					$warr['uid']  = $uid;
				}
				$this->insert_into('wxqrcode', $warr);
				//生成cookie
				if($type==''){
					$type = 'wxloginid';
				}
				require_once('cookie.model.php');
				$cookie 					= 		new cookie_model($this->db,$this->def);
				$cookie -> setCookie($type,$randStr,time()+86000);
				
				$ticket = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($Return['ticket']);
			}
		}
		return $ticket;
    }

	//微信发布工具各类二维码
	function pubWxQrcode($c,$id,$type = ''){
	    
		global $config;
		
		if($c == 'job'){
			$scene_str = $type."_jobid_".$id;
		}elseif($c == 'resume'){
			$scene_str = $type."_resumeid_".$id;
		}elseif($c == 'company'){
			$scene_str = $type."_companyid_".$id;
		}elseif($c == 'article'){
			$scene_str = $type."_articleid_".$id;
		}elseif($c == 'announcement'){
			$scene_str = $type."_announcementid_".$id;
		}elseif ($c == 'jobtel'){
            // 微信扫码获取联系方式，限定$type是weixin
            $scene_str = "weixin_jobtelid_".$id;
        }elseif ($c == 'parttel'){
            // 微信扫码获取联系方式，限定$type是weixin
            $scene_str = "weixin_parttelid_".$id;
        }elseif ($c == 'comtel'){
		    // 微信扫码获取联系方式，限定$type是weixin
		    $scene_str = "weixin_comtelid_".$id;
		}elseif($c == 'part'){
		    $scene_str = $type."_partid_".$id;
		}elseif($c == 'register'){
            $scene_str = $type."_ruid_".$id;
		}elseif($c == 'gongzhao'){
		    // 公招
		    $scene_str = $type."_gongzhaoid_".$id;
		}elseif($c == 'gzh'){
		    // 关注公众号
		    $scene_str = "weixin_gzhid_".$id;
		} elseif ($c == 'xjh') {
		    // 预约宣讲会
            $scene_str = $type."_xjhid_".$id;
        } elseif ($c == 'zph') {
            // 现场招聘会
            $scene_str = $type."_zphid_".$id;
        }

		//查询识别ID对应的二维码是否存在或失效
		$wxqrcode   =   $this->select_once('wxqrcode', array('wxloginid' => $scene_str));
		if(!empty($wxqrcode)){
		
			if($wxqrcode['time'] >= (time()- 86400)){//留出容错时间,一天内不重复生成
				$ticket =  "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($wxqrcode['ticket']);
				return $ticket;
			}else{
				$this	->	delete_all('wxqrcode',array('wxloginid'=>$scene_str), '', '', 1);
			}
		}
		$Url     =   'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.getToken();
		

		$Data['expire_seconds']  =   2591000;          //有效时间30天
		$Data['action_name']     =   'QR_STR_SCENE'; //临时二维码
		$Data['action_info']['scene']['scene_str']   =   $scene_str;   //场景值
		
		$CurlReturn      =   CurlPost($Url,json_encode($Data));
		
		
		$Return          =   json_decode($CurlReturn,true);
		//插入数据库
		if($Return['ticket']){
			
			$warr    =   array('wxloginid' => $scene_str, 'ticket' => $Return['ticket'], 'time' => time(), 'status' => 0);
			
			$this->insert_into('wxqrcode', $warr);
			
			$ticket = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($Return['ticket']);
		}
		
		return $ticket;
    }
    function isAdminWxlogin($wxid,$wxloginid){

  		$res = array('result'=>false);

		global $config;

		$wxqrcode = $this->select_once('wxqrcode', array('wxloginid' => $wxloginid, 'status' => 0));

		$adminuser  	=  $this -> select_once('admin_user',array('wxid'=>$wxid));
		
        if(!empty($adminuser)){
		    
		    $upData = array('status' => 1, 'wxid' => $wxid);
		    
		    $this->update_once('wxqrcode', $upData, array('wxloginid' => $wxloginid));

		    $res['result'] = true;
		    
		}else{

			$res['msg'] = '您的微信暂未绑定管理员账号';

		}
		return $res;
	}
	function isWxlogin($wxid,$wxloginid){
		global $config;
		$wxqrcode = $this->select_once('wxqrcode', array('wxloginid' => $wxloginid, 'status' => 0));

		if($wxqrcode['auid']>0){
			$user  	=  $this -> select_once('admin_user',array('uid'=>$wxqrcode['auid']));
			
			if($user){
                $this->update_once('admin_user',array('wxid'=>''),array('wxid'=>$wxid));// 绑定前先清空微信绑定的其他账户信息
				$this->update_once('admin_user',array('wxid'=>$wxid),array('uid'=>$user['uid']));
				$this->update_once('wxqrcode', array('status' => 1, 'wxid' => $wxid), array('wxloginid' => $wxloginid));
				return array('result'=>true,'type'=>'amminwxbind');
			}else{
				return array('result'=>false,'type'=>'amminwxbind');
			}
			
		}elseif($wxqrcode['uid'] > 0){
		    // 已存在账号扫码绑定
		    $userBind = $this->bindByUid($wxid, $wxqrcode['uid']);
		    if($userBind['result']){
		        $upData = array('status' => 1, 'wxid' => $wxid, 'unionid' => $userBind['unionid']);
		        if (!empty($userBind['unionid'])){
		            $upData['unionid'] = $userBind['unionid'];
		        }
		        $this->update_once('wxqrcode', $upData, array('wxloginid' => $wxloginid));
		    }
		    return array('result'=>$userBind['result'],'type'=>'userwxbind');
		}else{
			$userBind = $this->isBind($wxid);
			
			if(isset($userBind['uid']) && $userBind['uid']>0){
			    // 微信已绑定账号，转登录
			    $upData = array('status' => 1, 'wxid' => $wxid, 'unionid' => $userBind['unionid']);
			    if (!empty($userBind['unionid'])){
			        $upData['unionid'] = $userBind['unionid'];
			    }
			    $this->update_once('wxqrcode', $upData, array('wxloginid' => $wxloginid));
			    return true;
				
			}else{
			    // 微信未绑定账号，转注册
			    $upData = array('status' => 1, 'wxid' => $wxid, 'unionid' => $userBind['unionid']);
			    if (!empty($userBind['unionid'])){
			        $upData['unionid'] = $userBind['unionid'];
			    }
			    $this->update_once('wxqrcode', $upData, array('wxloginid' => $wxloginid));
			    
			    $return  =  array('result'=>false);
			    if($wxloginid){
			        // 没有绑定的，扫码直接注册
			        if ($this->config['reg_real_name_check'] == 1){
			            // 后台设置实名注册的，要绑定手机号
			            $return['type']  =  'regphone';
			        }else{
			            
			            $return['type']  =  'regbindacount';
			        }
			    }
			    
			    return $return;
			}
		}
	}
	/**
	 * 微信扫码直接绑定账号
	 */
	function bindByUid($wxid = '', $uid = '')
	{
	    $return = array('result'=>false,'unionid'=>'');
	    if ($uid != ''){
	        $member = $this->select_once('member', array('uid'=>$uid), '`uid`,`usertype`,`wxid`,`unionid`,`wxbindtime`');
	        if (!empty($member)){
	            $wxuser = $this->getWxUser($wxid);
	            
	            $upData['wxid'] = $wxid;
	            if (!empty($wxuser['unionid'])){
	                $upData['unionid'] = $wxuser['unionid'];
	                $return['unionid'] = $wxuser['unionid'];
	            }
	            if(empty($member['wxbindtime'])){
	                $upData['wxbindtime'] = time();
                    include_once ('integral.model.php');
                    $integralM  =  new integral_model($this->db, $this->def);
                    $integralM->invtalCheck($uid,$member['usertype'],'integral_bind_wx','微信扫码绑定',28);
	            }
	            if (!empty($upData)){
	                // 先将其他账号绑定此微信记录清空
	                $qlwhere['wxid']  =  $wxid;
	                if (!empty($wxuser['unionid'])){
	                    $qlwhere['unionid']  =  array('=',$wxuser['unionid'],'OR');;
	                }
	                $this->update_once('member', array('wxid'=>'', 'unionid'=>''), $qlwhere);
	                // 保存新的微信参数
	                $this->update_once('member', $upData, array('uid'=>$member['uid']));
	                //会员日志，记录扫码绑定
	                include_once ('log.model.php');
	                $LogM  =  new log_model($this->db, $this->def);
	                $LogM->addMemberLog($member['uid'],$member['usertype'], '账号认证：微信绑定', 12, 1, '微信扫码绑定成功');
	                
	                $return['result'] = true;
	            }
	        }
	    }
	    return $return;
	}
	function creatacount($wxloginid, $uid = ''){

		$return  =  array('status'=>0);

		if($wxloginid){
			//判断是否扫码
			$status = $this->select_once('wxqrcode', array('wxloginid' => $wxloginid, 'status' => 1));
			//根据扫码ID 读取用户
			if($status['wxid'] || $status['unionid']){
			    
			    if (!empty($status['unionid'])){
			        
			        $member  =  $this->select_once('member',array('wxid'=>$status['wxid'],'unionid'=>array('=',$status['unionid'],'OR')));
			        $this->update_once('member',array('login_date'=>time(),'unionid'=>$status['unionid']),array('uid'=>$member['uid']));
			    }else{
			        
			        $member  =  $this->select_once('member',array('wxid'=>$status['wxid']));
			        $this->update_once('member',array('login_date'=>time()),array('uid'=>$member['uid']));
			    }
			    
			    if ($uid == '' && empty($member) && $this->config['reg_real_name_check'] != 1){
			        // 非会员中心扫码绑定
			        // 未设置实名注册，微信未绑定账号的，直接注册账号
			        $wdata  =  array(
			            'openid'   =>  $status['wxid'],
			            'unionid'  =>  $status['unionid'],
			            'source'   =>  9
			        );
			        
			        require_once('userinfo.model.php');
			        $userinfoM  =  new userinfo_model($this->db,$this->def);
			        $result  =  $userinfoM->fastReg($wdata, '', 'weixin');
			        
			        if ($result['errcode'] == 9){
			            
			            $member  =  $userinfoM->getInfo(array('uid'=>$result['uid']));
			        }
			    }
			    $return  =  array('status'=>1, 'member'=>$member);
			}
		}
		return $return;
	}
	function getWxLoginStatus($wxloginid, $uid = ''){
	    // 未扫码
	    $return  =  array('status'=>0);
		if($wxloginid){
			//判断是否扫码
			$status = $this->select_once('wxqrcode', array('wxloginid' => $wxloginid, 'status' => 1));
			//根据扫码ID 读取用户
			if($status['wxid'] || $status['unionid']){
			    $ip = fun_ip_get();
			    $upLogin = array(
			    	'login_ip' => $ip,
					'login_date'=> time()
				);
			    if (!empty($status['unionid'])){
			        
			        $member  =  $this->select_once('member',array('wxid'=>$status['wxid'],'unionid'=>array('=',$status['unionid'],'OR')));
                    if ($member['login_ip'] != $ip || $member['login_address'] =='') {
                        $upLogin['login_address'] = getIpAddress($ip);
                    }
                    $upLogin['unionid'] = $status['unionid'];
			        $this->update_once('member',$upLogin,array('uid'=>$member['uid']));
			    }else{
			        
			        $member  =  $this->select_once('member',array('wxid'=>$status['wxid']));
                    if ($member['login_ip'] != $ip || $member['login_address'] =='') {
                        $upLogin['login_address'] = getIpAddress($ip);
                    }
			        $this->update_once('member',$upLogin,array('uid'=>$member['uid']));
			    }
			    if (!empty($member['usertype'])){
			        
			        include_once ('log.model.php');
			        $LogM  =  new log_model($this->db, $this->def);
                    include_once ('integral.model.php');
                    $integralM  =  new integral_model($this->db, $this->def);

			        if (empty($status['uid'])){
			            // 微信扫码登录的，有身份的账号，登录需记录登录日志，处理登录积分
			            //会员日志，记录手动登录
			            $LogM->addMemberLog($member['uid'],$member['usertype'], '账号登录：微信扫码登录', 32, 1);
                        $integralM->invtalCheck($member['uid'],$member['usertype'],'integral_bind_wx','微信扫码绑定',28);
			            $logtime  =  date("Ymd",$member['login_date']);
			            $nowtime  =  date("Ymd",time());
			            if($logtime!=$nowtime){
			                // 登录积分
			                $integralM->invtalCheck($member['uid'],$member['usertype'],'integral_login','会员登录',22);
			                // 登录日志
			                $logdata['content']	  =  '微信扫码登录';
			                $logdata['uid']		  =  $member['uid'];
			                $logdata['usertype']  =  $member['usertype'];
			                $logdata['did']		  =  $member['did'];
			                
			                $LogM->addLoginlog($logdata);
			            }
			        }
			    }
			   
			    $return  =  array('status'=>1, 'member'=>$member);
			}
		}
		return $return;
	}
	/**
	* 添加wxnav数量
	* $setData 	自定义处理数组
	*
	*/
	
	function getWxNavNum($whereData){

		if(!empty($whereData)){
			
			$num	=	$this -> select_num('wxnav',$whereData);
			
		}

		return $num;
	
	}
	
	/**
	* 添加wxnav数据
	* $setData 	自定义处理数组
	*
	*/
	function addWxNavInfo($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('wxnav',$setData);
			
		}

		return $nid;
	
	}

	/**
	* 更新wxnav数据
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/
	function upWxNavInfo($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('wxnav',$data,$whereData);
			
		}
		return $nid;
	}
	
	function getWxNavList($whereData,$data=array()){
		
		$navlist	=	array();
		
		$List		=	$this->select_all("wxnav",$whereData);
		
		if(is_array($List)){
			
			foreach($List as $value){
				
				if($value['keyid']=='0' || $value['keyid']==''){
					
					$navlist[$value['id']]			=	$value;
					
				}
			}
			
			foreach($List as $val){
				
				foreach($navlist as $key=>$v){
					
					if($v['id']==$val['keyid']){
						
						$navlist[$key]['list'][]	=	$val;
					}
				}
			}
		
		}
		
		return	$navlist;
		
	}
	
	function creatWxNavList($whereData,$data=array()){
		
		$CreatNav 	= 	array();
		
		$navList	=	$this->getWxNavList($whereData);
		
		if(is_array($navList))	{
			
			$i	=	0;
			
			foreach($navList as $key=>$value){
				
				$t	=	0;
				
				$CreatNav['button'][$i]['name']	=	urlencode(trim($value['name']));
				
				if(!empty($value['list'])){

					foreach($value['list'] as $k=>$v){
						
							$CreatNav['button'][$i]['sub_button'][$t]['name'] 		= 	urlencode(trim($v['name']));
						
						if($v['type']=='view'){
							
							$CreatNav['button'][$i]['sub_button'][$t]['type'] 		= 	'view';
							
							$CreatNav['button'][$i]['sub_button'][$t]['url'] 		= 	trim($v['url']);
							
						}elseif($v['type']=='click'){
							
							$CreatNav['button'][$i]['sub_button'][$t]['type'] 		= 	'click';
							
							$CreatNav['button'][$i]['sub_button'][$t]['key']		=	urlencode(trim($v['key']));
							
						}elseif($v['type']=='miniprogram'){
							
							$CreatNav['button'][$i]['sub_button'][$t]['type'] 		= 	'miniprogram';
							
							$CreatNav['button'][$i]['sub_button'][$t]['url']		= 	urlencode(trim($v['url']));
							
							$CreatNav['button'][$i]['sub_button'][$t]['appid']		=	trim($v['appid']);
							
							$CreatNav['button'][$i]['sub_button'][$t]['pagepath']	= 	trim($v['apppage']);
							
						}
						$t++;
					}
					
				}else{

					if($value['type']=='view'){
						
						$CreatNav['button'][$i]['type']		= 	'view';
						
						$CreatNav['button'][$i]['url'] 		= 	trim($value['url']);
						
					}elseif($value['type']=='click'){
						
						$CreatNav['button'][$i]['type'] 	= 	'click';
						
						$CreatNav['button'][$i]['key'] 		= 	urlencode(trim($value['key']));
						
					}elseif($value['type']=='miniprogram'){
						
						$CreatNav['button'][$i]['type'] 	= 	'miniprogram';
						
						$CreatNav['button'][$i]['url'] 		= 	urlencode(trim($value['url']));
						
						$CreatNav['button'][$i]['appid'] 	= 	trim($value['appid']);
						
						$CreatNav['button'][$i]['pagepath'] = 	trim($value['apppage']);
						
					}
				}

				$i++;
				
			}
 			
		}
		
		return $CreatNav;
		
	}
	
	function delWxNav($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
		
		$result			=	$this	->	delete_all('wxnav',$whereData,$limit);
		
		return	$result;
		
	}
	// 查询单条微信扫码
	function getWxQrcode($whereData,$data=array()){
	    
	    $fieid = isset($data['field']) ? $data['field'] : '*';
	    
	    $row  =  $this->select_once('wxqrcode', $whereData, $fieid);
	    
	    return $row;
	}
	function getWxQrcodeList($whereData,$data=array()){
		 
		//提取用户类
		
		$ListNew	=	array();
		
		$List		=	$this -> select_all('wxqrcode',$whereData);
		
		
		if(!empty($List)){
			
			foreach($List as $k=>$v){
				
			    if(!empty($v['wxid'])){
			        $wxid[]  =  $v['wxid'];
			    }
			    $List[$k]['time_n'] = date('Y-m-d H:i:s',$v['time']);
			}
			if (!empty($wxid)){
			    $member  =  $this->select_all('member',array('wxid'=>array('in',"'".@implode("','",$wxid)."'")),'`wxid`,`username`,`usertype`');
			    
			    foreach($List as $key=>$value){
			        
			        foreach($member as $v){
			            
			            if($value['wxid'] == $v['wxid']){
			                
			                $List[$key]['username']	= $v['username'];
			                
			                $List[$key]['usertype'] = $v['usertype'];
			            }
			        }
			    }
			}
			$ListNew['list']  =  $List;
		}
        
		return $ListNew;
    }
	
	function delWxqrcode($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
		
		$result			=	$this	->	delete_all('wxqrcode',$whereData,$limit, '', 1);
		
		return	$result;
		
	}
	
	function getWxzdkeywordList($whereData,$data=array()){
		$ListNew	=	array();
		$List		=	$this -> select_all('wxzdkeyword',$whereData);

		if(!empty( $List )){
			// foreach ($List as $k => $v) {
			// 	$List[$k]['msgtype_n'] = $this->zdMsgType[$v['msgtype']];
			// }
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
	}
	
	function getWxzdkeyword($whereData,$data=array()){
		
		$wxzdKeyword	=	$this -> select_once('wxzdkeyword',$whereData);

		$wxzdKeyword['conarr'] = array();
		
		if(!empty($wxzdKeyword)){

			$conarr	=	$this -> select_all('wxzdcon',array('kid'=>$wxzdKeyword['id'],'orderby'=>array('sort,desc','id,asc')));

			if(!empty($conarr)){

				foreach($conarr as $k=>$v){

					$conarr[$k]['ctime_n'] = date('Y-m-d H:i',$v['time']);

					if($v['msgtype']=='image'){

						$conarr[$k]['msgtype_n'] = '图片';

						$content_arr  =	unserialize($v['content']);

						unset($conarr[$k]['content']);

						$conarr[$k]['media_id'] = $content_arr['media_id'];

						$conarr[$k]['image_n'] = checkpic($content_arr['image_n']);

						$conarr[$k]['newimage'] = '';

					}else if($v['msgtype']=='xcx'){

						$conarr[$k]['msgtype_n'] = '小程序卡片';

						$content_arr  =	unserialize($v['content']);

						unset($conarr[$k]['content']);

						$conarr[$k]['xcx_title'] = $content_arr['xcx_title'];
						
						$conarr[$k]['xcx_appid'] = $content_arr['xcx_appid'];

						$conarr[$k]['xcx_pagepath'] = $content_arr['xcx_pagepath'];

						$conarr[$k]['media_id'] = $content_arr['media_id'];

						$conarr[$k]['image_n'] = checkpic($content_arr['image_n']);

						$conarr[$k]['newimage'] = '';

					}else{
						$conarr[$k]['msgtype_n'] = '文本';

						$conarr[$k]['media_id'] = '';

						$conarr[$k]['image_n'] = '';

						$conarr[$k]['newimage'] = '';
					}
				}

				$wxzdKeyword['conarr'] = $conarr;
			}
		}

		return	$wxzdKeyword;
	
	}
	
	function addWxzdkeyword($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('wxzdkeyword',$setData);
			
		}

		return $nid;
	
	}

	function upWxzdkeyword($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('wxzdkeyword',$data,$whereData);
			
		}

		return $nid;
	}
	
	function delWxzdkeyword($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
		$row			=	$this	->	select_all('wxzdkeyword',$whereData);

		$kid_arr = array();

		foreach($row as $k=>$v){
			$kid_arr[] = $v['id'];
		}

		$result			=	$this	->	delete_all('wxzdkeyword',$whereData,$limit);
		
		$result			=	$this	->	delete_all('wxzdcon',array('kid'=>array('in',pylode(',',$kid_arr))),'');

		return	$result;
		
	}
	function addWxzdCon($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('wxzdcon',$setData);
			
		}

		return $nid;
	
	}
	function upWxzdCon($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('wxzdcon',$data,$whereData);
			
		}

		return $nid;
	}
	function delWxzdCon($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
		
		$result			=	$this	->	delete_all('wxzdcon',$whereData,$limit);
		
		return	$result;
		
	}

    public function upWxlogin($whereData,$data){
    
        $nid    =   $this -> update_once('wxqrcode', $data, $whereData);
        return $nid;
    }
    /**
     * 在线时直聊提醒  微信消息模板
     */
    function sendWxChat($data){
        
        global $config;
        if(!$config){
            include(PLUS_PATH.'config.php');
        }
        if($config['wx_xxtz']!='1' || (!$config['wx_mbjzbm'] && !$config['wx_mbchatlm']))
        {
            return true;
        }
        if($data['wxid'])
        {
            $First		= '新的'.$this->config['sy_chat_name'].'提醒';
            $Name	    = $data['name'];
            $Expect	    = $data['expect'];
            $Time		= $data['time'];
            $Remark		= '感谢您的支持,详情请登录 '.$config['sy_webname'].' !';
            $Url        = Url('wap',array('c'=>'chat','id'=>$data['fuid'],'type'=>$data['ftype']));
            
            if (!empty($config['wx_mbchatlm'])){
                // 类目模板
                $Tempid = $config['wx_mbchatlm'];
                
                $TempDate['thing2'] = array('value'=>$Name,'color'=>'');//姓名
                $TempDate['thing4'] = array('value'=>$First,'color'=>'');//聊天提醒
                $TempDate['time5']	= array('value'=>$Time,'color'=>'');//时间
                
                $tbody	=	array(
                    0	=>	'访客姓名：'.$Name,
                    1	=>	'访客事由：'.$First,
                    2	=>	'访问时间：'.$Time,
                    4   =>  $Url
                );
            }else{
                // 历史模板
                $Tempid = $config['wx_mbchat'];
                
                $TempDate['first']	    = array('value'=>$First,'color'=>'');//标题
                $TempDate['keyword1']	= array('value'=>$Name,'color'=>'');//姓名
                $TempDate['keyword2']	= array('value'=>$Expect,'color'=>'');//适合意向
                $TempDate['keyword3']	= array('value'=>$Time,'color'=>'');//联系时间
                $TempDate['remark']	    = array('value'=>$Remark,'color'=>'');
                
                $tbody	=	array(
                    0	=>	$First,
                    1	=>	'姓名：'.$Name,
                    2	=>	'适合意向：'.$Expect,
                    3	=>	'联系时间：'.$Time,
                    4	=>	$Remark,
                    5   =>  $Url
                );
            }
            
            $minipath = '';
            if ($data['usertype'] == 1 || $data['usertype'] == 2) {
                $minipath = "pson/pages/chat/show?id={$data['fuid']}&type={$data['ftype']}";
            }
            
            $sdata	=	array(
				'uid'		=>	$data['uid'],
				'utype'		=>	$data['usertype'],
				'wxid'		=>	$data['wxid'],
				'ctime'		=>	time(),
				'mbconfig'	=>	'wx_mbchat',
				'body'		=>	serialize($tbody)
			);

            $result = $this->sendWxTemplate($data['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
            if ($result['errcode'] != 0 ){//判断是否发送成功，成功errcode=0
                return false;
            }else{
                return true;
            }
        }
	}
	
	/** 
	 * 切换身份审核 微信消息模板
     */
	function sendWxUsercahnge($data){
		global $config;
		if($config['wx_xxtz']!='1' || !$config['wx_userchangetpl']){
			return true;
		}
		if($data['uid'] && $data['id'] ){
			$Tempid = $config['wx_userchangetpl'];
			$user				=		$this->select_once('user_change',array('id' =>$data['id'],'uid'=>$data['uid']),'`uid`,`name`,`pres_usertype`,`apply_usertype`,`status`,`statusbody`');
			switch($user['status']){
				case '1':$Status='审核通过';
				break;
				case '2':$Status='审核不通过';
				break;
			}
			$Member = $this->select_once('member', array('uid' => $user['uid']), '`wxid`,`uid`,`usertype`');
			$First  = '尊敬的用户'.$user['name'].',您好!您申请转换身份有一条新的消息通知！';
			$TempDate['first']	= array('value'=>$First,'color'=>'');//标题
			$TempDate['keyword1']	= array('value'=>$user['name'],'color'=>'');//用户名
			$TempDate['keyword2']	= array('value'=>$Status,'color'=>'');//审核状态
			if($user['statusbody'] && $user['status']==2){
				$Remark			=	 $user['statusbody'];
				$TempDate['remark']	= array('value'=>$Remark,'color'=>'');
			}

			$Url = $this->config['sy_wapdomain'] . '/member';
            $minipath = '';
			if ($data['usertype'] == 1 || $data['usertype'] == 2) {
                $minipath = 'pages/login/login';
            }

			$tbody	=	array(
				0	=>	$First,
				1	=>	'用户名：'.$user['name'],
				2	=>	'审核结果：'.$Status,
				3	=>	$Remark,
			    4   =>  $Url
			);

			$sdata	=	array(
				'uid'		=>	$Member['uid'],
				'utype'		=>	$Member['usertype'],
				'wxid'		=>	$Member['wxid'],
				'ctime'		=>	time(),
				'mbconfig'	=>	'wx_userchangetpl',
				'body'		=>	serialize($tbody)
			);

            $result = $this->sendWxTemplate($Member['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
            if ($result['errcode'] !=0 ){//判断是否发送成功，成功errcode=0
                return false;
            }else{
                return true;
            }
		}
	}
	/**
	 * 宣讲会直播即将开播通知  微信消息模板,本地宣讲会提醒,非都需
	 */
	function sendWxXjhLiveYy($data){
	    
	    global $config;
	    if(!$config){
	        include(PLUS_PATH.'config.php');
	    }
	    if($config['wx_xxtz']!='1' || !$config['wx_mbxjhlive'])
	    {
	        return true;
	    }
	    if($data['wxid'])
	    {
	        $Tempid = $config['wx_mbxjhlive'];
	        
	        $First	 =  '您预约的宣讲会直播即将开始';
	        $Name	 =  $data['name'];
	        $Time	 =  date('Y-m-d H:i', $data['stime']);
	        $Remark  =  '感谢您的支持，点击详情查看宣讲会直播!';
	        
	        $TempDate['first']	   =  array('value'=>$First,'color'=>'');
	        $TempDate['keyword1']  =  array('value'=>$Name,'color'=>'');//会议主题
	        $TempDate['keyword2']  =  array('value'=>$Time,'color'=>'');//开始时间
	        $TempDate['remark']	   =  array('value'=>$Remark,'color'=>'');

	        $Url = $data['url'];
            $minipath = $data['minipath'];

	        $tbody	=	array(
				0	=>	$First,
				1	=>	'会议主题：'.$Name,
				2	=>	'开始时间：'.$Time,
				3	=>	$Remark,
	            4   =>  $Url
			);

	        $sdata	=	array(
				'uid'		=>	$data['uid'],
				'utype'		=>	$data['usertype'],
				'wxid'		=>	$data['wxid'],
				'ctime'		=>	time(),
				'mbconfig'	=>	'wx_mbxjhlive',
				'body'		=>	serialize($tbody)
			);

            $result  =  $this->sendWxTemplate($data['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
            if ($result['errcode'] !=0 ){//判断是否发送成功，成功errcode=0
                return false;
            }else{
                return true;
            }
	        
	    }
	}
	/** 
	 * 视频面试审核 微信消息模板
     */
	function sendWxSpviewStatus($data){
		global $config;
		if($config['wx_xxtz']!='1' || !$config['wx_mbspmssh']){
			return true;
		}
		if($data['id']){
			$Tempid = $config['wx_mbspmssh'];
			$spview				=		$this->select_once('spview',array('id' =>$data['id']),'`uid`,`remark`,`status`,`statusbody`');
			switch($spview['status']){
				case '1':$Status='审核通过';
				break;
				case '2':$Status='审核不通过';
				break;
			}
			$Member = $this->select_once('member', array('uid' => $spview['uid']), '`wxid`,`uid`,`usertype`');
			$company = $this->select_once('company', array('uid' => $spview['uid']), '`name`');

			$First  = '尊敬的用户,您好!您发布的视频面试有一条新的消息通知！';
			$TempDate['first']	= array('value'=>$First,'color'=>'');//标题
			$TempDate['keyword1']	= array('value'=>$company['name'],'color'=>'');//企业名称
			$TempDate['keyword2']	= array('value'=>$Status,'color'=>'');//审核状态
			if($spview['statusbody'] && $spview['status']==2){
				$Remark			=	 $spview['statusbody'];
				$TempDate['remark']	= array('value'=>$Remark,'color'=>'');
			}

			$Url = $this->config['sy_wapdomain'] . '/member/index.php?c=spview';
            $minipath = 'pson/pages/commember/spview/index';

			$tbody	=	array(
				0	=>	$First,
				1	=>	'用户名：'.$company['name'],
				2	=>	'审核结果：'.$Status,
				3	=>	$Remark,
			    4   =>  $Url
			);

			$sdata	=	array(
				'uid'		=>	$Member['uid'],
				'utype'		=>	$Member['usertype'],
				'wxid'		=>	$Member['wxid'],
				'ctime'		=>	time(),
				'mbconfig'	=>	'wx_mbspmssh',
				'body'		=>	serialize($tbody)
			);

            $result = $this->sendWxTemplate($Member['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
            if ($result['errcode'] !=0 ){//判断是否发送成功，成功errcode=0
                return false;
            }else{
                return true;
            }
		}
	}
	/**
	 * 视频面试通知  微信消息模板
	 */
	function sendWxSpview($data){
	    
	    global $config;
	    if(!$config){
	        include(PLUS_PATH.'config.php');
	    }
	    if($config['wx_xxtz']!='1' || !$config['wx_mbspview'])
	    {
	        return true;
	    }
	    
        $Tempid = $config['wx_mbspview'];
        
        $First		=  $data['title'];
        $JobName	=  $data['jobname'];
        $Time		=  $data['sptime'];
        $Type		=  $data['sptype'];
        $Remark		=  '感谢您的支持，点击详情查看视频面试!';
        $Uname		=  $data['name'];
        
        $TempDate['first']		=	array('value'=>$First,'color'=>'');
        $TempDate['keyword1']	=	array('value'=>$JobName,'color'=>'');	//应聘职位
        $TempDate['keyword2']	=	array('value'=>$Time,'color'=>'');		//面试时间
        $TempDate['keyword3']	=	array('value'=>$Type,'color'=>'');		//面试形式
        $TempDate['keyword4']	=	array('value'=>$Remark,'color'=>'');	//面试备注
        $TempDate['keyword5']	=	array('value'=>$Uname,'color'=>'');		//通知人

        $Url = $data['url'];
        $minipath = $data['minipath'];
        
        $tbody	=	array(
			0	=>	$First,
			1	=>	'应聘职位：'.$JobName,
			2	=>	'面试时间：'.$Time,
			3	=>	'面试形式：'.$Type,
			4	=>	'面试说明：'.$Remark,
			5	=>	'通知人：'.$Uname,
            6   =>  $Url
		);

        $sdata	=	array(
			'uid'		=>	$data['uid'],
			'utype'		=>	$data['usertype'],
			'wxid'		=>	$data['wxid'],
			'ctime'		=>	time(),
			'mbconfig'	=>	'wx_mbspview',
			'body'		=>	serialize($tbody)
		);

        $result = $this->sendWxTemplate($data['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);
        if ($result['errcode'] !=0 ){//判断是否发送成功，成功errcode=0
            return false;
        }else{
            return true;
        }
	}
	/**
	 * 聊天-互换电话、微信号提醒  微信消息模板
	 */
	function sendWxChatEx($data){
	    
	    global $config;
	    if(!$config){
	        include(PLUS_PATH.'config.php');
	    }
	    if($config['wx_xxtz']!='1' || !$config['wx_mbchat_ex'])
	    {
	        return true;
	    }
	    
	    $Tempid = $config['wx_mbchat_ex'];
	    
	    $First		=  $data['title'];
	    $content	=  $data['name'].'通过'.$this->config['sy_chat_name'].'邀请你互换'.$data['ask'];
	    $Time		=  $data['asktime'];
	    $Remark		=  '感谢您的支持，点击详情查看!';
	    
	    $TempDate['first']		=	array('value'=>$First,'color'=>'');
	    $TempDate['keyword1']	=	array('value'=>$content,'color'=>'');	//服务内容
	    $TempDate['keyword2']	=	array('value'=>$Time,'color'=>'');		//时间
	    $TempDate['remark']	    =	array('value'=>$Remark,'color'=>'');	//详情
	    
	    $Url = $data['url'];
	    $minipath = $data['minipath'];
	    
	    $tbody	=	array(
	        0	=>	$First,
	        1	=>	'服务内容：'.$content,
	        2	=>	'请求时间：'.$Time,
	        3   =>  '详情:'.$Remark,
	        4   =>  $Url
	    );
	    
	    $sdata	=	array(
	        'uid'		=>	$data['uid'],
	        'utype'		=>	$data['usertype'],
	        'wxid'		=>	$data['wxid'],
	        'ctime'		=>	time(),
	        'mbconfig'	=>	'wx_mbchat_ex',
	        'body'		=>	serialize($tbody)
	    );
	    
	    $result = $this->sendWxTemplate($data['wxid'],$Tempid,$Url,$TempDate,$sdata, $minipath);

		if ($result['errcode'] != 0 ){//判断是否发送成功，成功errcode=0
			return false;
		}else{
			return true;
		}

	}

	/**
	 * 微信发送客服消息
	 */
	function sendCustom($wxid,$type='text',$data = array()){
	    

			global $config;
			if(!$config){
				include(PLUS_PATH.'config.php');
			}
		    
			if($wxid){

				//构建模板参数
					$templateDate = array("touser"=>$wxid,
									  "msgtype"=>$type
					);

				if($type == 'text'){
					if($data['info'] == ''){
						if($this->config['wx_welcom']){
							$info = $this->config['wx_welcom'];
						}else{
							$info = "欢迎您关注".$this->config['sy_webname']."！\n 1：您可以直接回复关键字如【销售】、【销售 XX公司】查找您想要的职位\n绑定您的账户体验更多精彩功能\n感谢您的关注！";
						}
					}else{
						$info = $data['info'];
					}
					
					$templateDate['text']	=	array('content' => $info);
				
				}elseif($type == 'image'){
					if(empty($data)){
						
						$data['media_id'] = $this->config['sy_wxcom_picmedia'];
						
					}
					
					$templateDate['image']	=	$data;
				
				}elseif($type == 'miniprogrampage'){

					if(empty($data)){

						$data['title']			=	$this->config['sy_xcxname'];
						$data['appid']			=	$this->config['sy_xcxappid'];
						$data['pagepath']		=	$this->config['sy_xcxpath'];
						$data['thumb_media_id']	=	$this->config['sy_xcxmedia'];
						
					}
					
					$templateDate['miniprogrampage']	=	$data;
				
				}
				//读取微信 token
				$Token = getToken();
				
				//客服消息发送接口链接
				$wxUrl = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$Token;
				
				
				
				$CurlReturn  = CurlPost($wxUrl,json_encode($templateDate,JSON_UNESCAPED_UNICODE));
				$return    = json_decode($CurlReturn,true);
			}

			return $return;

	}
	
    /**
     * 推广营销发送公众号模板消息
     */
	function sendwxtpl($post){
		
		$status = 0;
		//检查信息完整度
		if($post['wxtplid'] && $post['first'] && $post['keyword1'] && $post['keyworddata1']){
			
		    $where = array('wxid' => array('<>',''));
		    $whereStr = "`wxid` <> ''";
		    
		    if($post['activ'] != 'all'){
		        // 用户活跃度
		        $where['login_date']  =  array('<=',(time() - (int)$post['activ']*86400));
		        $whereStr = " AND `login_date` <= ".(time() - (int)$post['activ']*86400);
		    }
		    
			if($post['utype'] == 'allbind'){
				
			    $wxMemberNum  =  $this -> select_num('member',$where);
				
			}elseif($post['utype'] == '5'){
				
					
				$username			=  @explode(',',$post['userarr']);
				$where['username']  =  array('in_s',implode("','",$username));

				$wxMemberNum  =  $this -> select_num('member',$where);

			}else{

                if($post['utype'] == '1'){

                    if($post['utz'] == 'birthday'){//生日祝福
                        // 废弃生日通知
                        // $numField		=	"count(*) as num";
                        //
                        // $wxSql			=	"`".$this->def."member` where ".$whereStr." AND usertype = 1 AND uid IN ( SELECT uid FROM `".$this->def."resume` WHERE date_format(`birthday`,'%m%d')=date_format(now(),'%m%d'))";
                        //
                        // $numberAll		=	$this->DB_query_all("SELECT $numField FROM  $wxSql");
                        //
                        // $wxMemberNum	=	$numberAll['num'];

                    }elseif($post['utz'] == 'expect'){//提醒发布简历

                        $numField		=	"count(*) as num";

                        $wxSql			=	"`".$this->def."member` where ".$whereStr." AND usertype = 1 AND uid IN ( SELECT uid FROM `".$this->def."resume` where `resumetime` = 0)";

                        $numberAll		=	$this->DB_query_all("SELECT $numField FROM  $wxSql");

                        $wxMemberNum	=	$numberAll['num'];

                    }else{

                        $numField		=	"count(*) as num";

                        $wxSql			=	"`".$this->def."member` where ".$whereStr." AND usertype = 1";

                        $numberAll		=	$this->DB_query_all("SELECT $numField FROM  $wxSql");

                        $wxMemberNum	=	$numberAll['num'];

                    }
                }elseif($post['utype'] == '2'){

                    if($post['ctz'] == 'endvip'){   //提醒会员到期

                        $numField		=	"count(*) as num";

                        $wxSql			=	"`".$this->def."member` where ".$whereStr." AND usertype = 2 AND uid IN ( SELECT uid FROM `".$this->def."company_statis` WHERE `vip_etime` >".time()." AND `vip_etime` < ".strtotime('+7 day').")";

                        $numberAll		=	$this->DB_query_all("SELECT $numField FROM  $wxSql");

                        $wxMemberNum	=	$numberAll['num'];

                    }elseif($post['utype'] == '2' && $post['ctz'] == 'jobadd'){//提醒发布职位
                        
                        $numField		=	"count(*) as num";

                        $wxSql			=	"`".$this->def."member` where ".$whereStr." AND usertype = 2 AND uid IN ( SELECT uid FROM `".$this->def."company` where `jobtime` = 0)";

                        $numberAll		=	$this->DB_query_all("SELECT $numField FROM  $wxSql");

                        $wxMemberNum	=	$numberAll['num'];

                    }else{
                        $numField		=	"count(*) as num";

                        $wxSql			=	"`".$this->def."member` where ".$whereStr." AND usertype = 2";

                        $numberAll		=	$this->DB_query_all("SELECT $numField FROM  $wxSql");

                        $wxMemberNum	=	$numberAll['num'];
                    }
                }else{
				    // 猎头、培训
				    $where['usertype']	=  (int)$post['utype'];
				    //查询符合条件的用户
				    $wxMemberNum  =  $this->select_num('member',$where);
				}
			}
			if($wxMemberNum > 0){
				
				//循环次数 
				$page  =  intval($post['page']);
				$size  =  200;
				$num   =  ceil($wxMemberNum/$size); 

				$success = $error = 0;
				
				if(isset($wxSql)){
				    $limit	   =  " LIMIT ".($size*($page-1)) . ",".$size;
				    $wxMember  =  $this->DB_query_all("SELECT `uid`,`wxid`,`usertype` FROM  $wxSql".$limit,"all");
				}else{
				    
				    $where['limit']	= array(($size*($page-1)),$size);
				    $wxMember  =  $this->select_all('member',$where,"`uid`,`usertype`,`wxid`");
				}
				
				foreach($wxMember as $key=>$value){
				    
				    $sendReturn	=	$this -> sendTpl($value['uid'],$value['wxid'],$value['usertype'],$post);
				    
				    if($sendReturn['errcode'] > 0){
				        
				        $error++;
				    }else{
				        $success++;
				    }
				}
				$msg = '';
                if ($page < $num){
                    $msg = '发送中...';
                    $status = 3;
                }else{
                    $status = 1;
                }
                $msg  .=  '已发送模板消息成功：'.$success.'条';
                if ($error){
                    $msg .= ',失败：'.$error.'条';
                }
			}else{
				
				$msg  =  '没有符合条件的用户！';
			}
		}else{
			
			$msg  =  "请按要求填写相关内容！";
		}
		return array('status' => $status,'msg'=>$msg);
	}

	function sendTpl($uid,$wxid,$usertype,$post){
		
		//构建模板消息数组
		$TempDate['first']				= array('value'=>$post['first'],'color'=>'');//标题
		
		
		$TempDate[$post['keyword1']]	= array('value'=>$post['keyworddata1'],'color'=>'');
		
		if($post['keyword2']){
			$TempDate[$post['keyword2']]	= array('value'=>$post['keyworddata2'],'color'=>'');
		}

		if($post['keyword3']){
			$TempDate[$post['keyword3']]	= array('value'=>$post['keyworddata3'],'color'=>'');
		}

		if($post['keyword4']){
			$TempDate[$post['keyword4']]	= array('value'=>$post['keyworddata4'],'color'=>'');
		}

		if($post['keyword5']){
			$TempDate[$post['keyword5']]	= array('value'=>$post['keyworddata5'],'color'=>'');
		}

		$TempDate['remark']	= array('value'=>$post['remark'],'color'=>'');//备注信息

		if($post['url']){
			$Url = $post['url'];
		}else{
			$Url = $this->config['sy_wapdomain'];
		}
		
		
		$sdata	=	array(
			'uid'		=>	$uid,
			'utype'		=>	$usertype,
			'wxid'		=>	$wxid,
			'ctime'		=>	time(),
			'mbconfig'	=>	'wxmbdiy',
			'body'		=>	serialize($post)
		);
		
		$sendReturn	=	$this->sendWxTemplate($wxid,$post['wxtplid'],$Url,$TempDate,$sdata);

		
		
		return $sendReturn;
	}


	function upMedia($picpath, $upArr = array()){
		//启用OSS需要先获取远程图片流临时保存至本地
		if ($this->config['sy_oss'] == 1){
			$path =	DATA_PATH.'upload/wx/xcxpic'.time().'.jpg';
            include_once(LIB_PATH.'upload.class.php');
            $wxupload = new Upload();
            // 开启oss，直接使用上传文件时的临时文件上传，无需从oss文件路径下载
            if ($upArr) {
                $dir = DATA_PATH.'upload/wx/';
                require_once('upload.model.php');
                $uploadM = new upload_model($this->db, $this->def);
                $upload = $uploadM->Upload_pic($dir);
                $pictype = isset($upArr['type']) ? $upArr['type'] : '';
                $path = $upload->picture($upArr['file'], $pictype);
                list($width,$height,$type,$attr) = getimagesize($path);
                $path =	$wxupload->makeThumb($path,$width,$height,'',true);
            }
		}else{
			$path = APP_PATH.$picpath;
		}
		if($path){
			$token = getToken();
			$url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$token.'&type=image';
			$ch	= curl_init();
			 //兼容5.0-5.6版本的curl
			 if (class_exists('\CURLFile')) {
				 curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
				 $data = array('media' => new \CURLFile($path));//
			 } else {
				 if (defined('CURLOPT_SAFE_UPLOAD')) {
					 curl_setopt($ch,CURLOPT_SAFE_UPLOAD,false);
				 }
				 $data = array('media'=>'@'.$path);
			 }
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1 );
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if (curl_errno($ch)) {
			   // echo 'Errno'.curl_error($ch);die;
			}
			$result = curl_exec($ch);
			curl_close($ch);
			$res = json_decode($result,true);
			return array('media_id'=>$res['media_id'],'url'=>$res['url'],'errmsg' => $res['errmsg']);
		}
	}

	/** 
	 * 管理员通知 微信消息模板
	 *	$data=array(
	 		'userids'=>接收通知的企业账户数组
			'first'=>通知内容
			'type'=>通知类型标题
			'title'=>自定义标题,优先级别大于type
	 	)
     */
	function sendWxQyAdmin($data=array()){

		global $config;

		if($config['sy_admin_wmtype']!='2')
		{
			return true;
		}

		if(isset($data['userids']) && !empty($data['userids']))
		{
			$wmtype		=	$this -> select_all('admin_wmtype', array('type' =>array('<>','')), '`type`,`name`');

			$titleType	=	array();

			foreach($wmtype as $wk=>$wv){

				$titleType[$wv['type']] = $wv['name'];

			}


			//转换数组中字符编码，微信只支持UTF-8
			$First		= '<div class="gray">'.date('Y/m/d H:i').'</div> <div class="normal">'.$data['first'].'</div><div class="highlight">请及时登录管理后台进行处理</div>';

			$Title		= $data['title'] ? $data['title'] : ($titleType[$data['type']] ? $titleType[$data['type']] : '消息通知');
			
			//构建模板参数
			$templateDate = array(
				"touser"	=>	implode('|',$data['userids']),
				"msgtype"	=>	'textcard',
				"agentid"	=>	$config['wx_qy_agentid'],
				"textcard"	=>	array(
					'title'			=>	$Title,
					'description'	=>	$First,
					'url'			=>	$config['sy_weburl'] . '/wapadmin/'
				)
			);
			$sdata = array(
				'uids'	=>	implode(',',$data['uids']),
				'touser'=>	implode(',',$data['userids']),
				'body'	=>	serialize(array($Title,$First)),
				'ctime'	=>	time()
			);
			$this->sendQyWxTemplate($templateDate,$sdata);
		}
	}

    /**
     * @param array $templateDate
     * @param array $sdata
     * @return mixed
     */
    function sendQyWxTemplate($templateDate = array(), $sdata = array())
    {

        global $config;
        if (!$config) {
            include(PLUS_PATH . 'config.php');
        }

        //读取微信 token
        $Token  =   getWxQyToken('msg');

        //企业微信消息发送接口链接
        $qyUrl  =   'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=' . $Token;


        $CurlReturn =   CurlPost($qyUrl, json_encode($templateDate));
        $return =   json_decode($CurlReturn, true);

        $sdata['status']    =   $return['errcode'];
        if ($return['errcode'] != 0) {
            $sdata['msg']   =   $return['errmsg'];
        }
        if ($return['errcode'] == 0 && !empty($return['invaliduser'])) {
            $sdata['msg']   =   '以下企业微信账户不在可见范围：' . str_replace('|', '，', $return['invaliduser']);
        }

        $this->insert_into('wx_qymsg', $sdata);

        return $return;
    }
	/**
	 * 按用户类型处理列表中用户数据
	 */
	private function getUserByUsertype($rows = array()){
	    
	    $ru = $cu = $lu = $pu = array();
	    
	    foreach ($rows as $v){
	        if ($v['utype'] == 1){
	            $ru[] = $v['uid'];
	        }
	        if ($v['utype'] == 2){
	            $cu[] = $v['uid'];
	        }
	        if ($v['utype'] == 3){
	            $lu[] = $v['uid'];
	        }
	        if ($v['utype'] == 4){
	            $pu[] = $v['uid'];
	        }
	    }
	    if (!empty($ru)){
	        $resume  =  $this->select_all('resume', array('uid'=>array('in',pylode(',', array_unique($ru)))),'`uid`,`name`');
	    }
	    if (!empty($cu)){
	        $company =  $this->select_all('company', array('uid'=>array('in',pylode(',', array_unique($cu)))),'`uid`,`name`');
	    }
	    if (!empty($lu)){
	        $ltinfo  =  $this->select_all('lt_info', array('uid'=>array('in',pylode(',', array_unique($lu)))),'`uid`,`realname` AS `name`');
	    }
	    if (!empty($pu)){
	        $pxinfo  =  $this->select_all('px_train', array('uid'=>array('in',pylode(',', array_unique($pu)))),'`uid`,`name`');
	    }
	    foreach ($rows as $k=>$v){
	        $rows[$k]['username'] = '';
	        
	        if (isset($resume)){
	            foreach ($resume as $rv){
	                if ($v['utype'] == 1 && $v['uid'] == $rv['uid']){
	                    $rows[$k]['username'] = $rv['name'];
	                }
	            }
	        }
	        if (isset($company)){
	            foreach ($company as $cv){
	                if ($v['utype'] == 2 && $v['uid'] == $cv['uid']){
	                    $rows[$k]['username'] = $cv['name'];
	                }
	            }
	        }
	        if (isset($ltinfo)){
	            foreach ($ltinfo as $lv){
	                if ($v['utype'] == 3 && $v['uid'] == $lv['uid']){
	                    $rows[$k]['username'] = $lv['name'];
	                }
	            }
	        }
	        if (isset($pxinfo)){
	            foreach ($pxinfo as $pv){
	                if ($v['utype'] == 4 && $v['uid'] == $pv['uid']){
	                    $rows[$k]['username'] = $pv['name'];
	                }
	            }
	        }
	    }
	    
	    return $rows;
	}
	/**
	 * 微信通知重发
	 */
	function msgrepeat($id){
	    
	    if(is_array($id)){
	        $where['id']  =	 array('in',pylode(',',$id));
	    }else{
	        $where['id']  =	 (int)$id;
	    }
	    $where['status']  =	 array('<>',0);
	    $where['wxid']	  =	 array('<>','');
	    $where['body']    =  array('<>','');
	    
	    //查询失败通知
	    $rows  =  $this->select_all('wx_msg',$where);
	    
	    if(!empty($rows)){
	        global $config;
	        if(!$config){
	            include(PLUS_PATH.'config.php');
	        }
	        //读取微信 token
	        $Token = getToken();
	        //模板消息发送接口链接
	        $wxUrl = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$Token;
	        
	        $successid = $failid = array();
	        $msg = $codeMsg ='';
	        foreach ($rows as $v){
	            $Url = '';
	            $TempDate = array();
	            $tbody = unserialize($v['body']);
	            if ($v['mbconfig'] != 'wxmbdiy'){
	                // 有固定模板ID的通知
	                $Tempid = $config[$v['mbconfig']];
	                // 判断最后一个参数是否是url
	                $endstr = end($tbody);
	                if(preg_match("#(http|https)://(.*\.)?.*\..*#i",$endstr)){
	                    $Url = $endstr;
	                    array_pop($tbody);
	                }
	                $count = count($tbody);
	                foreach ($tbody as $key=>$val){
	                    if ($key == 0){
	                        $TempDate['first']  = array('value'=>$val,'color'=>'');
	                    }elseif ($key == ($count - 1)){
	                        // 将数组中url去掉后，最后一项就是备注
	                        $TempDate['remark'] = array('value'=>$val,'color'=>'');
	                    }else{
	                        $TempDate['keyword'.$key] = array('value'=>$val,'color'=>'');
	                    }
	                }
	            }else{
	                // 后台-运营-推广营销-公众号模板消息
	                foreach ($tbody as $key=>$val){
	                    if (!empty($val)){
	                        if ($key == 'wxtplid'){
	                            $Tempid = $val;
	                        }elseif ($key == 'first'){
	                            $TempDate['first'] = array('value'=>$val,'color'=>'');
	                        }elseif ($key == 'keyword1'){
	                            $TempDate[$tbody['keyword1']] = array('value'=>$tbody['keyworddata1'],'color'=>'');
	                        }elseif ($key == 'keyword2'){
	                            $TempDate[$tbody['keyword2']] = array('value'=>$tbody['keyworddata2'],'color'=>'');
	                        }elseif ($key == 'keyword3'){
	                            $TempDate[$tbody['keyword3']] = array('value'=>$tbody['keyworddata3'],'color'=>'');
	                        }elseif ($key == 'keyword4'){
	                            $TempDate[$tbody['keyword4']] = array('value'=>$tbody['keyworddata4'],'color'=>'');
	                        }elseif ($key == 'keyword5'){
	                            $TempDate[$tbody['keyword5']] = array('value'=>$tbody['keyworddata5'],'color'=>'');
	                        }elseif ($key == 'remark'){
	                            $TempDate['remark'] = array('value'=>$val,'color'=>'');
	                        }elseif ($key == 'url'){
	                            $Url = $val;
	                        }
	                    }
	                }
	            }

                $minipath = '';
	            if (empty($Url)){
	                if($v['mbconfig'] == 'wx_mbsqzw'){
	                    $Url = $this->config['sy_wapdomain']."/member/index.php?c=hr";
                        $minipath = 'pson/pages/commember/hr/index';
	                }elseif($v['mbconfig'] == 'wx_mbyqms'){
	                    $Url = $this->config['sy_wapdomain']."/member/index.php?c=invite";
                        $minipath = 'pson/pages/usermember/invite/index';
                    }elseif($v['mbconfig'] == 'wx_mbcztx'){
                        $Url = $this->config['sy_wapdomain'] . '/member/index.php?c=paylog';
                        if ($v['utype'] == 1) { // 个人
                            $minipath = 'pson/pages/usermember/finance/fklog';
                        } elseif ($v['utype'] == 2) { // 企业
                            $minipath = 'pson/pages/commember/finance/fklog';
                        } elseif ($v['utype'] == 3) { // 猎头
                        } elseif ($v['utype'] == 4) { // 培训
                            $Url = Url('wap',array('c'=>'login'));
                        }
                    }elseif($v['mbconfig'] == 'wx_mbzwsh'){
                        $Url = $this->config['sy_wapdomain'] . '/member/index.php?c=job';
                        $minipath = 'pson/pages/commember/job/index';
                    }elseif($v['mbconfig'] == 'wx_mbjzsh'){
                        $Url = $this->config['sy_wapdomain'] . '/member/index.php?c=part';
                        $minipath = 'pson/pages/commember/part/index';
                    }elseif($v['mbconfig'] == 'wx_mbjlsh'){
                        $Url = $this->config['sy_wapdomain'].'/member/index.php?c=resume';
                        $minipath = 'pson/pages/usermember/resume/index';
                    }elseif($v['mbconfig'] == 'wx_mbjzbm'){
                        // 没有兼职职位ID，跳列表
                        $Url = $this->config['sy_wapdomain'] . "/member/index.php?c=part";
                        $minipath = 'pson/pages/commember/part/index';
                    }elseif($v['mbconfig'] == 'wx_mbreward'){
                        if ($v['utype'] == 1) {
                            $Url = $this->config['sy_wapdomain'] . '/member/index.php?c=rebates&type=cy';
                            $minipath = 'pson/pages/usermember/rewardlog/index';
                        } elseif ($v['utype'] == 2) {
                            $Url = $this->config['sy_wapdomain'] . '/member/index.php?c=jobpack&t=r';
                            $minipath = 'pson/pages/commember/jobpack/index';
                        }
	                }elseif(in_array($v['mbconfig'],array('wx_subscribetpl','wx_userchangetpl'))){
	                    // $Url = Url('wap',array('c'=>'login'));
                        $Url = $this->config['sy_wapdomain'] . '/member';
                        $minipath = 'pages/login/login';
	                }elseif($v['mbconfig'] == 'wx_mbadmin'){
	                    $Url = '';
	                }elseif(in_array($v['mbconfig'],array('wx_mbchat','wx_mbchat_ex'))){
	                    $Url = Url('wap',array('c'=>'chat','a'=>'chatList','fr'=>'tz'));
                        $minipath = 'pages/sysmsg/index';
	                }elseif($v['mbconfig'] == 'wx_mbxjhlive'){
	                    $Url = Url('wap', array('c'=>'xjhlive'));
                        $minipath = 'pages/xjhlive/xjhlive';
                    }elseif($v['mbconfig'] == 'wx_mbspmssh'){
                        $Url = $this->config['sy_wapdomain'] . '/member/index.php?c=spview';
                        $minipath = 'pson/pages/commember/spview/index';
	                }elseif($v['mbconfig'] == 'wx_mbspview'){
	                    $Url = Url('wap', array('c'=>'spview'));
	                    $minipath = 'pages/spview/spview';
	                }elseif($v['mbconfig'] == 'wxmbdiy'){
	                    $Url = $this->config['sy_wapdomain'];
	                }
	            }
	            if (!empty($Tempid)){
	                //构建模板参数
	                $templateDate = array(
	                    'touser'      => $v['wxid'],
	                    'template_id' => $Tempid,
	                    'url'         => $Url,
	                    'topcolor'    => '#FF0000',
	                    'data'        => $TempDate
	                );

                    if($minipath && !empty($config['wx_xxtz_tz']) && $config['wx_xxtz_tz'] == 2 && !empty($config['sy_xcxappid'])){
                        $templateDate['miniprogram']['appid'] = $config['sy_xcxappid'];
                        $templateDate['miniprogram']['pagepath'] = $minipath;
                    }else{
                        $templateDate['url'] = $Url;
                    }
	                
	                $CurlReturn  = CurlPost($wxUrl,json_encode($templateDate));
	                $return    = json_decode($CurlReturn,true);
	                
	                if ($return['errcode'] == 0){
	                    $successid[] = $v['id'];
	                }else {
	                    $failid[] = $v['id'];
	                    $codeMsg  .= $return['errcode'].' ';
	                }
	            }else{
	                $failid[] = $v['id'];
	                $codeMsg  .= '模板ID为空 ';
	            }
	        }
	        if(!empty($successid)){
	            
	            $this->update_once('wx_msg', array('status'=>0,'msg'=>'ok'),array('id'=> array('in',implode(',',$successid))));
	            $msg .= '本次微信通知重发成功：'.count($successid).'条';
	        }
	        if(!empty($failid) && !empty($codeMsg)){
	            $msg .=',失败：'.count($failid).'条,错误码：'.$codeMsg;
	        }
	    }else{
	        $msg = '没有需要重发的通知';
	    }
	    return $msg;
	}

    /**
     * 简历审核 微信消息模板
     * @param $data
     * @return bool
     */
    function sendWxResumeStatus($data = array())
    {

        global $config;
        if ($config['wx_xxtz'] != '1' || !$config['wx_mbjlsh']) {
            return true;
        }
        if ($data['resume_id']) {

            $Tempid =   $config['wx_mbjlsh'];

            $User   =   $this->select_all('resume_expect', array('id' => array('in', pylode(',', $data['resume_id']))), '`uid`,`name`,`uname`');
            if (is_array($User)) {


                $Uid    =   array();
                $ResumeName =   $UserName   =   array();
                foreach ($User as $key => $value) {

                    $ResumeName[$value['uid']][]    =   $value['name'];
                    $Uid[]                          =   $value['uid'];
                    $UserName[$value['uid']]        =   $value['uname'];
                }
                $Uid    =   array_unique($Uid);
                switch ($data['state']) {
                    case '1':
                        $Status = '审核通过';
                        break;
                    case '3':
                        $Status = '审核未通过';
                        break;
                }

                $Member =   $this->select_all('member', array('uid' => array('in', pylode(',', $Uid))), '`wxid`,`uid`');

                if (is_array($Member)) {
                    foreach ($Member as $key => $value) {

                        $data['first']  =   '尊敬的 '.$UserName[$value['uid']].'!您的简历审核结果如下，敬请留意！';
                        $First          =   $data['first'];
                        $ResumeName     =   @implode(',', $ResumeName[$value['uid']]);
                        $statusResult   =   $Status;
                        $statusTime     =   date('Y-m-d H:i');
                        $Body           =   $data['statusbody'];

                        $Remark         =   '详情请登录 '.$config['sy_webname'].' !';

                        $TempDate['first']      =   array('value' => $First, 'color' => '');
                        $TempDate['keyword1']   =   array('value' => $ResumeName, 'color' => '');
                        $TempDate['keyword2']   =   array('value' => $statusTime, 'color' => '');
                        $TempDate['keyword3']   =   array('value' => $statusResult, 'color' => '');
                        $TempDate['keyword4']   =   array('value' => $Body, 'color' => '');
                        $TempDate['remark']     =   array('value' => $Remark, 'color' => '');

                        $Url        =   $this->config['sy_wapdomain'].'/member/index.php?c=resume';
                        $minipath   =   'pson/pages/usermember/resume/index';

                        $tbody  =   array(
                            0 => $First,
                            1 => '简历名称：'.$ResumeName,
                            2 => '审核时间：'.$statusTime,
                            3 => '审核结果：'.$statusResult,
                            4 => '审核原因：'.$Body,
                            5 => $Remark,
                            6 => $Url
                        );

                        $sData  =   array(
                            'uid'       =>  $value['uid'],
                            'utype'     =>  1,
                            'wxid'      =>  $value['wxid'],
                            'ctime'     =>  time(),
                            'mbconfig'  =>  'wx_mbjlsh',
                            'body'      =>  serialize($tbody)
                        );
                        $result =   $this->sendWxTemplate($value['wxid'], $Tempid, $Url, $TempDate, $sData, $minipath);
                        if ($result['errcode'] != 0) {

                            return false;
                        } else {

                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * 发送一次性订阅消息
     * sendWxSubscribe(array('uid'=>$user['uid'],'wxid' => $user['openid'], 'url'=> $url,'tpl_data' => $subscribeTpl))
     * @wxid        小程序openid
     * @tpl_data	消息内容
     */
    function sendWxSubscribe($data){
        global $config;
        if(!$config){
            include(PLUS_PATH.'config.php');
        }
        $tplid = '';
        require_once('subtpl.model.php');
        $subtplM = new subtpl_model($this->db, $this->def);
        if ($data['tpl_type'] == 1) {// 宣讲会开播订阅消息
            $tp = '宣讲会开播提醒';
            $tplInfo = $subtplM->getTpl(array('name' => 'xjh_start_tplid'));// 获取宣讲会开播订阅消息模板id
            $tplid = $tplInfo['tpl_id'] ? $tplInfo['tpl_id'] : '';
        } else if ($data['tpl_type'] == 2) {// 邀请面试订阅消息
            $tp = '邀请面试通知';
            $tplInfo = $subtplM->getTpl(array('name' => 'interview_tplid'));// 获取邀请面试订阅消息模板id
            $tplid = $tplInfo['tpl_id'] ? $tplInfo['tpl_id'] : '';
		} else if ($data['tpl_type'] == 3) {// 支付成功订阅消息
            $tp = '支付成功通知';
            $tplInfo = $subtplM->getTpl(array('name' => 'payment_tplid'));// 获取支付成功订阅消息模板id
            $tplid = $tplInfo['tpl_id'] ? $tplInfo['tpl_id'] : '';
		} else if ($data['tpl_type'] == 4) {// 职位审核通知
            $tp = '职位审核通知';
            $tplInfo = $subtplM->getTpl(array('name' => 'job_audit_tplid'));// 获取职位审核订阅消息模板id
            $tplid = $tplInfo['tpl_id'] ? $tplInfo['tpl_id'] : '';
        }
        if(empty($tplid)){
            $res['errcode']	= 1;
            $res['msg']	= '请先配置' . $tp . '订阅模板!';
            return $res;
        }
        //参数判断
        if(empty($data['wxid']) || empty($data['tpl_data'])){
            $res['errcode']	= 1;
            $res['msg']	=	'缺少发送的信息!';
            return $res;
        }
        //模板消息发送接口链接
        require_once('xcx.model.php');
        $xcxM = new xcx_model($this->db, $this->def);
        $wxUrl = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$xcxM->getWxxcxToken();
        //构建模板参数
        $msg = array(
            "touser" => $data['wxid'],
            "template_id" => $tplid,
            "data" => $data['tpl_data']
        );
        if ($data['url']) {
            $msg['page'] = $data['url'];
        }
        if ($data['tpl_type'] == 1) {
            /*
             * 直播内容 {{thing1.DATA}}
             * 开播时间 {{time3.DATA}}
             */
            $tbody = array(
                0 => '直播开始提醒通知',
                1 => '预警类别：直播开始提醒',
                2 => '预警范围：小程序',
                3 => '发送时间：'.date('Y-m-d H:i:s'),
                4 => '直播内容:'.$data['tpl_data']['thing1']['value'] . ',开播时间:' . $data['tpl_data']['time3']['value'],
                5 => $data['url']
            );
        } else if ($data['tpl_type'] == 2) {
            /*
             * 职位名称 {{thing1.DATA}}
             * 邀约人 {{thing6.DATA}}
             * 面试时间 {{time3.DATA}}
             * 面试地点 {{thing4.DATA}}
             * 温馨提示 {{thing8.DATA}}
             */
            $tbody = array(
                0 => '邀请面试通知',
                1 => '预警类别：邀请面试通知',
                2 => '预警范围：小程序',
                3 => '发送时间：'.date('Y-m-d H:i:s'),
                4 => '职位名称:' . $data['tpl_data']['thing1']['value'] . ',邀约人:' . $data['tpl_data']['thing6']['value'] . ',面试时间:' . $data['tpl_data']['time3']['value'] . ',面试地点:' . $data['tpl_data']['thing4']['value'] . ',温馨提示:' . $data['tpl_data']['thing8']['value'],
                5 => $data['url']
            );
		} else if ($data['tpl_type'] == 3) {
            /*
             * 订单内容 {{thing4.DATA}}
             * 支付金额 {{amount2.DATA}}
             * 下单时间 {{time3.DATA}}
             */
            $tbody = array(
                0 => '支付成功通知',
                1 => '预警类别：支付成功通知',
                2 => '预警范围：小程序',
                3 => '发送时间：'.date('Y-m-d H:i:s'),
                4 => '订单内容:'.$data['tpl_data']['thing4']['value'] .',支付金额:'. $data['tpl_data']['amount2']['value'] . ',下单时间:' . $data['tpl_data']['time3']['value'],
                5 => $data['url']
            );
		} else if ($data['tpl_type'] == 4) {
            /*
             * 职位名称 {{thing1.DATA}}
             * 审核时间 {{time3.DATA}}
             * 审核状态 {{phrase4.DATA}}
             */
            $tbody = array(
                0 => '职位审核通知',
                1 => '预警类别：职位审核通知',
                2 => '预警范围：小程序',
                3 => '发送时间：'.date('Y-m-d H:i:s'),
                4 => '职位名称:'.$data['tpl_data']['thing1']['value'] .',审核时间:'. $data['tpl_data']['time3']['value'] . ',审核状态:' . $data['tpl_data']['phrase4']['value'],
                5 => $data['url']
            );
        }
        $sdata = array(
            'uid' => $data['uid'],
            'utype' => $data['utype'],
            'wxid' => $data['wxid'],
            'ctime' => time(),
            'tpl_name' => $tplInfo['name'],
            'body' => serialize($tbody)
        );
        $CurlReturn = CurlPost($wxUrl,json_encode($msg));
        $return = json_decode($CurlReturn,true);
        $sdata['status'] = $return['errcode'];
        $sdata['msg'] = $return['errmsg'];
        $this->insert_into('wx_subscribe_msg',$sdata);
        return $return;
    }

    //微信订阅消息列表
    public function getWxSubscribeMsgList($whereData, $data = array()) {
        $field = empty($data['field']) ? '*' : $data['field'];
        $List = $this->select_all('wx_subscribe_msg', $whereData, $field);
        if(!empty($List)){
            $muids = array();
            foreach ($List as $v) {
                $v['uid'] && !in_array($v['uid'], $muids) && array_push($muids, $v['uid']);
            }
            // 按用户类型处理列表中用户数据
            $List = $this->getUserByUsertype($List);
            foreach($List as $lk=>$lv){
                if ($lv['utype'] == 0) {
                    $List[$lk]['utype_n']	=	'未知';
                } else if($lv['utype']=='1'){
                    $List[$lk]['utype_n']	=	'个人';
                }else if($lv['utype']=='2'){
                    $List[$lk]['utype_n']	=	'企业';
                }else if($lv['utype']=='3'){
                    $List[$lk]['utype_n']	=	'猎头';
                }else if($lv['utype']=='4'){
                    $List[$lk]['utype_n']	=	'培训';
                }
                $List[$lk]['ctime_n'] =	date('Y-m-d H:i:s',$lv['ctime']);
                $body_arr =	unserialize($lv['body']);
                $body_html = '';
                foreach($body_arr as $bv){
                    $body_html .= $bv.'</br>';
                }
                $List[$lk]['body_html'] = $body_html;
                if ($lv['tpl_name'] == 'xjh_start_tplid'){// 小程序订阅模板目前只有宣讲会开播
                    $List[$lk]['mbconfig_n'] = '宣讲会开播预约';
                } else if ($lv['tpl_name'] == 'interview_tplid') {// 邀请面试订阅通知
                    $List[$lk]['mbconfig_n'] = '邀请面试';
				} else if ($lv['tpl_name'] == 'payment_tplid') {// 支付成功订阅通知
                    $List[$lk]['mbconfig_n'] = '支付成功通知';
				} else if ($lv['tpl_name'] == 'job_audit_tplid') {// 职位审核订阅通知
                    $List[$lk]['mbconfig_n'] = '职位审核通知';
                }
            }
        }
        return $List;
    }
    //删除小程序一次性订阅消息
    public function delWxsubmsg($data = array()) {
        if(!empty($data['where'])){
            $where = $data['where'];
            $nid = $this->delete_all('wx_subscribe_msg', $where, '');
            return $nid;
        }
    }
}
?>