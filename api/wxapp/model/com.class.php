<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class com_controller extends wxapp_controller{
    function company_satic($uid){
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$uid."'");
		if($statis['rating']){
			$rating=$this->obj->DB_select_once("company_rating","`id`='".$statis['rating']."'");
		}
		$statis['rating_type'] = $rating['type'];
		if($statis['vip_etime']<time()){
			/*
			*时间会员、带有结束日期的套餐会员，如果已经过期了，设置为【过期会员或者当前会员】
			*没有结束日期的套餐会员则根据剩余数量判断
			*LGL
			*/
			if($statis['vip_etime']>'1'){
				$nums=0;
			}else if($statis['rating_type']=='1'  &&  $statis['vip_etime']<'1'){
				$nums=1;
			}
			if($nums<1){
				if($this->config['com_vip_done']=='0'){
					$data['job_num']=$data['down_resume']=$data['invite_resume']=$data['editjob_num']=$data['breakjob_num']=$data['part_num']=$data['editpart_num']=$data['breakpart_num']=$data['zph_num']=$data['lt_job_num']=$data['lt_editjob_num']=$data['lt_down_resume']=$data['lt_breakjob_num']='0';
					$data['oldrating_name']=$statis['rating_name'];
					$statis['rating_name']=$data['rating_name']="过期会员";
					$where['uid']=$uid;
					$this->obj->update_once("company_statis",$data,$where);
				}elseif ($this->config['com_vip_done']=='1'){
					$ratingM = $this->MODEL('rating');
					$rat_value=$ratingM->rating_info($this->config['com_rating'],$uid);
					$this->obj->DB_update_all("company_statis",$rat_value,"`uid`='".$uid."'");
				}
			}
		}
		if($statis['autotime']>=time()){
			$statis['auto'] = 1;
		}
		if($statis['vip_etime']>time() || $statis['vip_etime']==0){
			if($statis['rating_type']=="2"){
			    $addltjobnum=$addjobnum=$addpartnum=$refreshjobnum='1';
			}else{
			    if($statis['breakjob_num']>0){
			        $refreshjobnum='1';
			    }else{
			        if($this->config['com_integral_online']=='1' || $this->config['com_integral_online']=='3'){
			            if (intval($statis['integral']) >= intval($this->config['integral_jobefresh']*$this->config['integral_proportion'])){
			                $refreshjobnum='2';
			            }else{
			                $refreshjobnum='3';
			            }
			        }else{
			            $refreshjobnum='0';
			        }
			    }
				if($statis['job_num']>0){
					$addjobnum='1';
				}else{
				    if($this->config['com_integral_online']=='1' || $this->config['com_integral_online']=='3'){
				        if (intval($statis['integral']) >= intval($this->config['integral_job']*$this->config['integral_proportion'])){
					        $addjobnum='2';
					        $addjobmsg='发布职位需要'.$this->config['integral_job']*$this->config['integral_proportion'].$this->config['integral_pricename'].',是否继续？';
					    }else{
					        $addjobnum='3';
					        $addjobmsg='发布职位需要'.$this->config['integral_job']*$this->config['integral_proportion'].$this->config['integral_pricename'].',您的'.$this->config['integral_pricename'].'不足';
					    }
				    }elseif ($this->config['com_integral_online']=='2'){
				        $addjobnum='4';
				        $addjobmsg='发布职位将消费'.$this->config['integral_job'].'元,是否继续？';
				    }else{
						$addjobnum='0';
					}
				}
				if($statis['part_num']>0){
					$addpartnum='1';
				}else{
				    if($this->config['com_integral_online']=='1' || $this->config['com_integral_online']=='3'){
				        if(intval($statis['integral']) >= intval($this->config['integral_partjob']*$this->config['integral_proportion'])){
					        $addpartnum='2';
					        $addpartmsg='发布兼职需要'.$this->config['integral_partjob']*$this->config['integral_proportion'].$this->config['integral_pricename'].',是否继续？';
					    }else{
					        $addpartnum='3';
					        $addpartmsg='发布兼职需要'.$this->config['integral_partjob']*$this->config['integral_proportion'].$this->config['integral_pricename'].',您的'.$this->config['integral_pricename'].'不足';
					    }
				    }elseif ($this->config['com_integral_online']=='2'){
				        $addpartnum='4';
				        $addpartmsg='发布兼职将消费'.$this->config['integral_partjob'].'元,是否继续？';
				    }else{
						$addpartnum='0';
					}
				}
			}
		}else {
		    if($this->config['com_integral_online']=='1' || $this->config['com_integral_online']=='3'){
		        if (intval($statis['integral']) >= intval($this->config['integral_job']*$this->config['integral_proportion'])){
		            $addjobnum='2';
		            $addjobmsg='发布职位需要'.$this->config['integral_job']*$this->config['integral_proportion'].$this->config['integral_pricename'].',是否继续？';
		        }else{
		            $addjobnum='3';
		            $addjobmsg='发布职位需要'.$this->config['integral_job']*$this->config['integral_proportion'].$this->config['integral_pricename'].',您的'.$this->config['integral_pricename'].'不足';
		        }
		        if(intval($statis['integral']) >= intval($this->config['integral_partjob']*$this->config['integral_proportion'])){
		            $addpartnum='2';
		            $addpartmsg='发布兼职需要'.$this->config['integral_partjob']*$this->config['integral_proportion'].$this->config['integral_pricename'].',是否继续？';
		        }else{
		            $addpartnum='3';
		            $addpartmsg='发布兼职需要'.$this->config['integral_partjob']*$this->config['integral_proportion'].$this->config['integral_pricename'].',您的'.$this->config['integral_pricename'].'不足';
		        }
		    }elseif ($this->config['com_integral_online']=='2'){
		        $addjobnum='4';
		        $addjobmsg='发布职位将消费'.$this->config['integral_job'].'元,是否继续？';
		        
		        $addpartnum='4';
		        $addpartmsg='发布兼职将消费'.$this->config['integral_partjob'].'元,是否继续？';
			}else{
				$addjobnum=$addpartnum='0';
			}
		} 
		$statis['refreshjobnum']=$refreshjobnum;
		$statis['addjobnum']=$addjobnum;
		$statis['addjobmsg']=$addjobmsg;
		$statis['addpartnum']=$addpartnum;
		$statis['addpartmsg']=$addpartmsg;
		$statis['pay_format']=number_format($statis['pay'],2);
		$statis['integral_format']=number_format($statis['integral']);
		$statis['addjobintegral']=$this->config['integral_job']*$this->config['integral_proportion'];
		$statis['addpartintegral']=$this->config['integral_partjob']*$this->config['integral_proportion'];
		$statis['sxjobintegral']=$this->config['integral_partjob']*$this->config['integral_proportion'];
		$statis['pricename']=$this->config['integral_pricename'];
		return $statis;
	}
	function status_action(){
	    $uid = intval($_POST['uid']);
	    $companyM=$this->MODEL('company');
	    $company = $companyM->GetCompanyInfo(array('uid'=>$uid));
	    if($this->config['com_enforce_mobilecert']==1 && $company['moblie_status']!=1){
	        $this->errjson('请先认证手机号',10);
	    }
	    if($this->config['com_enforce_licensecert']==1 && $company['yyzz_status']!=1){
	        $this->errjson('请先认证营业执照',11);
	    }
	    if($this->config['com_enforce_emailcert']==1 && $company['email_status']!=1){
	        $this->errjson('请先认证邮箱',12);
	    }
	    $companyM=$this->MODEL('company');
	    $result = $companyM->comVipDayActionCheck($_POST['type'],$uid);
	    $static = $this->company_satic($uid);
	    if ($_POST['type']=='addjob'){
	        $result['addjobnum']=$static['addjobnum'];
	        $result['addjobmsg']=$static['addjobmsg'];
	    }elseif ($_POST['type']=='addpart'){
	        $result['addpartnum']=$static['addpartnum'];
	        $result['addpartmsg']=$static['addpartmsg'];
	    }
	    echo json_encode($result);die;
	}
	function getinfo_action(){//获取企业信息
	    $uid=(int)$_POST['uid'];
	    $row = $this->obj->DB_select_once("company","`uid`='".$uid."'");
	    if((!is_array($row) || empty($row)) && $_POST['uid']){
	        $data['error']=2;
	        echo json_encode($data);die;
	    }
	    $com = $this->obj->DB_select_once("member","`uid`='".$uid."'");
	    if((!is_array($com) || empty($com)) && $_POST['uid']){
	        $data['error']=2;
	        echo json_encode($data);die;
	    }
	    $statis=$this->company_satic($uid);
	    $userid_job_num=$this->obj->DB_select_num("userid_job","`com_id`='".$uid."'and `is_browse`='1'");
	    //$sxnum=$this->obj->DB_select_num("sysmsg","`fa_uid`='".$uid."'and `remind_status`='0'");
	    $job_num = $this->obj->DB_select_num("company_job","`uid`='".$uid."' and `state`= 1 and `status`<> 1 and `r_status` <> 2");
	    $row_talent_num=$this->obj->DB_select_num("talent_pool","`cuid`='".$uid."'");
	    $data=array();
	    if ($row && is_array($row)){
	        $data['userid_job']	=$userid_job_num;
	        $data['job_num']	=$job_num;
	        $data['talent_num']	=$row_talent_num;
	        $data['username']	=$com['username'];
	        $data['rating_name']=$statis['rating_name'];
	        $data['vip_stime']=date('Y-m-d',$statis['vip_stime']);
	        if ($statis['vip_etime']==0){
	            $data['vip_etime'] = '永久';
	        }else{
	            $data['vip_etime']=date('Y-m-d',$statis['vip_etime']);
	        }
	        $data['uid']		=$row['uid'];
	        $data['name']		=mb_substr($row['name'], 0,16,'utf-8');
	        $data['shortname']		=mb_substr($row['shortname'], 0,16,'utf-8');
	        $data['hy']			=$row['hy'];
	        $data['pr']			=$row['pr'];
	        $data['province']	=$row['provinceid'];
	        $data['city']		=$row['cityid'];
	        $data['threecity']  =$row['three_cityid'];
	        $data['mun']		=$row['mun'];
	        $data['sdate']		=$row['sdate'];
	        $data['money']		=$row['money'];
	        $data['content']	=strip_tags($row['content']);
	        $data['zip']		=$row['zip'];
	        $data['com_login_link']=$this->config['com_login_link'];
	        $data['address']	=$row['address'];
	        $data['linkman']	=$row['linkman'];
	        $linkphone=@explode('-',$row['linkphone']);
	        $data['phoneone']	=$linkphone['0'];
	        $data['phonetwo']	=$linkphone[1];
	        $data['phonethree']	=$linkphone[2];
	        $data['linktel']		=$row['linktel'];
	        $data['linkjob']		=$row['linkjob'];
	        $data['linkqq']		=$row['linkqq'];
	        $data['linkmail']	=$row['linkmail'];
	        $data['x']			=$row['x'];
	        $data['y']			=$row['y'];
	        $data['payd']		=$row['payd'];
	        $data['lastupdate']	=$row['lastupdate'];
	        $data['jobtime']		=$row['jobtime'];
	        $data['r_status']	=$row['r_status'];
	        $data['firmpic']		=$row['firmpic'];
	        $data['rec']			=$row['rec'];
	        $data['hits']		=$row['hits'];
	        $data['ant_num']		=$row['ant_num'];
	        $data['pl_time']		=$row['pl_time'];
	        $data['pl_status']	=$row['pl_status'];
	        $data['infostatus']	=$row['infostatus'];
	        $data['order']		=$row['order'];
	        $data['admin_remark']=$row['admin_remark'];
	        $data['email_dy']	=$row['email_dy'];
	        $data['msg_dy']		=$row['msg_dy'];
	        $data['website']		=$row['website'];
	        $data['busstops']	=$row['busstops'];
	        $data['pr']        =$row['pr'];
	        $data['mun']       =$row['mun'];
	        $data['hy']        =$row['hy'];
	        $data['moblie_status']=$row['moblie_status'];
	        $data['email_status']=$row['email_status'];
	        $data['yyzz_status']  =$row['yyzz_status'];
	        $data['content']     =str_replace(array("/\r\n\r\n\r\n/","&nbsp;"), array("\r\n",""),strip_tags($row['content']));
	        $data['lastupdate_n']=date("Y-m-d",$row['lastupdate']);
	        if ($row['moneytype']==2){
	            $data['moneytype_n']="美元";
	        }else{
	            $data['moneytype_n']="人民币";
	        }
	        if ($row['infostatus']==2){
	            $data['infostatus_n']="不公开";
	        }else{
	            $data['infostatus_n']="公开";
	        }
	        if($row['comqcode'] && (file_exists(APP_PATH.$row['comqcode']))){
	            $data['comqcode']   =$this->config['sy_weburl'].$row['comqcode'];
	        }
	        if($row['logo'] && (file_exists(str_replace('./',APP_PATH,$row['logo'])))){
	            $data['logo_n']=str_replace("./",$this->config['sy_weburl']."/",$row['logo']);
	        }else{
	            $data['logo_n']=$this->config['sy_weburl'].'/'.$this->config['sy_unit_icon'];
	        }
	        $comWelfare=array();
	        if($row['welfare']!=""){
	            $comWelfare=@explode(",",$row['welfare']);
	        }
	        include PLUS_PATH."com.cache.php";
	        //福利缓存数据
	        if(is_array($comdata['job_welfare'])){
	            foreach($comdata['job_welfare'] as $k=>$v){
	                $welfareData[]=$comclass_name[$v];
	            }
	        }
	        $data['welfare']=$comWelfare;
	        $welfareAll =array_unique(array_merge($comWelfare,$welfareData));
	        foreach($welfareAll as $k=>$v){
	            $data['welfareAll'][$k]['name']=$v;
	            if(in_array($v,$comWelfare)){
	                $data['welfareAll'][$k]['ischecked']=1;
	            }else{
	                $data['welfareAll'][$k]['ischecked']=0;
	            }
	        }
	        $comcert = $this->obj->DB_select_once("company_cert","`uid`='".$uid."' and `type`='3'");
	        $data['cert_pic']=$comcert['check'];
	        
	        $data['refreshjobnum']= $statis['refreshjobnum'];
	        $data['sxjobintegral']= $statis['sxjobintegral'];
	        $data['pricename']= $statis['pricename'];
	        $data['integral']=$statis['integral'];
	        $data['error']=1;
	    }
	    echo json_encode($data);die;
	}
	function joblist_action(){
	    $uid=(int)$_POST['uid'];
	    $rows=$this->obj->DB_select_all("company_job","`uid`='".$uid."'");
	    if(is_array($rows) && !empty($rows)){
	        $uids=array();
	        $jobids=array();
	        foreach($rows as $k=>$v){
	            $uids[]=$v['uid'];
	            $jobids[]=$v['id'];
	            $data2[$k]['id']=$v['id'];
	            $data2[$k]['name']=$v['name'];
	            $data2[$k]['lastupdate_n']=date("Y-m-d",$v['lastupdate']);
	            $data2[$k]['jobhits']=$v['jobhits'];
	            $data2[$k]['state']=$v['state'];
	            $data2[$k]['status']=$v['status'];
	            if($v['state']==0){
	                $data2[$k]['state_n']='未审核';
	            }elseif ($v['state']==1){
	                $data2[$k]['state_n']='已审核';
	            }elseif ($v['state']==2){
	                $data2[$k]['state_n']='未通过';
	            }
	            $data2[$k]['urgent']=$v['urgent'];
	        }
	        $userid_job=$this->obj->DB_select_all("userid_job","`job_id` in (".pylode(',',$jobids).") group by `job_id`","count(`id`) as num,`job_id`");
	        foreach($rows as $key=>$va){
	            $data2[$key]['userid_job']=0;
	            foreach($userid_job as $v){
	                if($va['id']==$v['job_id']){
	                    $data2[$key]['userid_job']=$v['num'];
	                }
	            }
	        }
	    }
	    $data['list']=count($data2)>0?$data2:array();
	    $data['server']=array(
	        'id'=>array(1,2,3,4),
	        'name'=>array('自动刷新','职位置顶','职位推荐','紧急招聘')
	    );
	    echo json_encode($data);die;
	}
	function jobadd_action(){//发布职位
	    $_POST=$this->post_trim($_POST);
	    $company=$this->obj->DB_select_once("company","`uid`='".$_POST['uid']."'");
	    if($company['name']==''){
	        $data['error']=3;
	        $data['msg']='请先完善基本信息';
	        echo json_encode($data);die;
	    }
	    if($_POST['submit']){
	        if(!$_POST['name']||!$_POST['provinceid']||!$_POST['uid']){
	            $data['error']=3;
	            $data['msg']='请完善信息';
	            echo json_encode($data);die;
	        }
	        if($_POST['maxsalary'] && (int)$_POST['minsalary']>(int)$_POST['maxsalary']){
	            $data['error']=3;
	            $data['msg']='最高薪资不能比最低薪资小';
	            echo json_encode($data);die;
	        }
	        $uid=intval($_POST['uid']);
	        $member=$this->obj->DB_select_once("member","`uid`='".$uid."'","`status`,`did`");
	        if($member['status']!="1"){
	            $_POST['state']=0;
	        }else{
	            $_POST['state']=$this->config['com_job_status'];
	        }
	        $_POST['name']=$this->stringfilter($_POST['name']);
	        $_POST['description']=$this->stringfilter($_POST['description']);
	        $com=$this->obj->DB_select_once("company","`uid`='".$uid."'","`name`,`logo`,`provinceid`,`pr`,`mun`,`r_status`,`x`,`y`");
	        $_POST['x']=$com['x'];
	        $_POST['y']=$com['y'];
	        $_POST['sdate']=time();
	        $statis=$this->obj->DB_select_once("company_statis","`uid`='".$uid."'","`rating`");
	        $_POST['com_name']=$com['name'];
	        $_POST['com_logo']=$com['logo'];
	        $_POST['com_provinceid']=$com['provinceid'];
	        $_POST['pr']=$com['pr'];
	        $_POST['mun']=$com['mun'];
	        $_POST['rating']=$statis['rating'];
	        $_POST['source']=13;
	        $_POST['r_status']=$com['r_status']!=2?1:2;
	        $islink=(int)$_POST['islink'];
	        $link_type=$islink;
	        if($islink<3){
	            $linktype=$islink;
	            $islink=1;
	        }else{
	            $islink=0;
	        }
	        $isemail=(int)$_POST['isemail'];
	        $emailtype=$isemail;
	        if($isemail<3){
	            $isemail=1;
	        }else{
	            $isemail=0;
	        }
	        if($_POST['salary_type']){
	            $_POST['minsalary']=$_POST['maxsalary']=0;
	        }
	        $_POST['is_link']=$islink;
	        $_POST['link_type']=$linktype;
	        $_POST['is_email']=$isemail;
	        $link_moblie=$_POST['link_moblie'];
	        $email=$_POST['email'];
	        $link_man=$this->stringfilter($_POST['link_man']);
	        unset($_POST['link_moblie']);
	        unset($_POST['islink']);
	        unset($_POST['isemail']);
	        unset($_POST['link_man']);
	        unset($_POST['email']);
	        $id= intval($_POST['id']);
	        unset($_POST['id']);
	        $joblink=array();
	        $joblink[]="`email`='".trim($email)."',`is_email`='".$isemail."',`email_type`='".$emailtype."'";
	        if($linktype==2){
	            $joblink[]="`link_man`='".$link_man."',`link_moblie`='".$link_moblie."'";
	        }
	        if ($link_type){
	            $joblink[]="`link_type`='".$link_type."'";
	        }
	        if(!$id){
	            $statis=$this->company_satic($uid);
	            if ($statis['addjobnum']==0){
	                $data['error']=3;
	                $data['msg']='发布职位套餐已用完';
	                echo json_encode($data);die;
	            }elseif ($statis['addjobnum']==3){
	                $data['error']=3;
	                $data['msg']="您的".$this->config['integral_pricename']."不足！";
	                echo json_encode($data);die;
	            }
	            $this->get_com(1,$uid);
	            $_POST['uid']=$uid;
	            $_POST['did']=$member['did'];
	            $_POST['lastupdate']=time();
	            $nid=$this->obj->insert_into("company_job",$_POST);
	            $name="添加";
	            if($nid){
	                $joblink[]="`uid`='".$uid."',`jobid`='".(int)$nid."'";
	                $this->obj->DB_insert_once("company_job_link",@implode(',',$joblink));
	                if($this->config['com_job_status']=="1"){
	                    $this->send_dingyue($nid,2);
	                }
	                $this->obj->update_once("company",array("jobtime"=>$_POST['lastupdate']),array("uid"=>$uid));
	                $state_content = "发布了新职位 <a href=\"".$this->config['sy_weburl']."/index.php?m=com&c=comapply&id=$nid\" target=\"_blank\">".$_POST['name']."</a>。";
	                $this->addstate($state_content,2,$uid);
	                if($this->config['com_job_status']=="0"){
	                    $this->member_log("发布了新职位 ".$_POST['name'], $uid, 2, 1, 1);
	                    $data['error']=1;//发布成功，等待审核
	                    $data['msg']='发布成功，等待审核';
	                }else{
	                    $data['error']=1;//发布成功
	                    $data['msg']='发布成功';
	                }
	                echo json_encode($data);die;
	            }else{
	                $data['error']=3;//发布失败
	                $data['msg']='发布失败';
	                echo json_encode($data);die;
	            }
	        }else{
	            $where['id']=$id;
	            $where['uid']=$uid;
	            unset($_POST['uid']);
	            $rows=$this->obj->DB_select_once("company_job","`id`='".$id."'");
	            $nid=$this->obj->update_once("company_job",$_POST,$where);
	            if($nid){
	                delfiledir("../data/upload/tel/".$uid);
	                $linkid=$this->obj->DB_select_once("company_job_link","`uid`='".$uid."' and `jobid`='".$id."'","id");
	                if($linkid['id']){
	                    $this->obj->DB_update_all("company_job_link",@implode(',',$joblink),"`id`='".$linkid['id']."'");
	                }else{
	                    $joblink[]="`uid`='".$uid."',`jobid`='".(int)$id."'";
	                    $this->obj->DB_insert_once("company_job_link",@implode(',',$joblink));
	                }
	                if($this->config['com_job_status']=="0"){
	                    $data['error']=1;//更新成功，等待审核
	                    $data['msg']='更新成功，等待审核';
	                }else{
	                    $data['error']=1;//更新成功
	                    $data['msg']='更新成功';
	                }
	                $this->member_log("更新职位 ".$_POST['name'], $uid, 2, 1, 2);
	                echo json_encode($data);die;
	            }else{
	                $data['error']=3;//更新失败
	                $data['msg']='更新失败';
	                echo json_encode($data);die;
	            }
	        }
	    }else{
	        include PLUS_PATH."/city.cache.php";
	        include PLUS_PATH."/com.cache.php";
	        include PLUS_PATH."/industry.cache.php";
	        include PLUS_PATH."/job.cache.php";
	        include(CONFIG_PATH."db.data.php");
	        if($_POST['id']){
	            $row=$this->obj->DB_select_once("company_job","`id`='".(int)$_POST['id']."' and `uid`='".(int)$_POST['uid']."'");
	        }
	        if($row['id']){
	            $data['id']=$row['id'];
	            $data['name']=$row['name'];
	            $data['jobone']=$row['job1'];
	            $data['jobson']=$row['job1_son'];
	            $data['jobpost']=$row['job_post'];
	            $data['province']=$row['provinceid'];
	            $data['city']=$row['cityid'];
	            $data['threecity']=$row['three_cityid'];
	            $data['minsalary']=$row['minsalary'];
	            $data['maxsalary']=$row['maxsalary'];
	            
	            $data['description']=preg_replace(array("/\r\n\r\n\r\n/"), array("\r\n"),strip_tags($row['description']));
	            if($_POST['id']){
	                $job_link=$this->obj->DB_select_once("company_job_link","`jobid`='".(int)$_POST['id']."' and `uid`='".(int)$_POST['uid']."'");
	            }
	            $data['hy']=$row['hy'];
	            $data['number']=$row['number'];
	            $data['exp']=$row['exp'];
	            $data['report']=$row['report'];
	            $data['age']=$row['age'];
	            $data['sex']=$row['sex'];
	            $data['edu']=$row['edu'];
	            $data['marriage']=$row['marriage'];
	            if($row['lang']!=""){
	                $lang= @explode(",",$row['lang']);
	                $data['lang']=$lang;
	            }
	        }
	        if($row['id'] && !$row['minsalary'] && !$row['maxsalary']){
	            $data['salarytype']=1;
	        }else{
	            $data['salarytype']='';
	        }
	        if($company['linktel']){
	            $data['linktel']=$company['linktel'];
	        }elseif($company['linkphone']){
	            $data['linktel']=$company['linkphone'];
	        }
	        $data['linkman']=$company['linkman'];
	        $data['linkmail']=$company['linkmail'];
	        $data['islink']=$job_link['link_type']?$job_link['link_type']:1;
	        $data['isemail']=$job_link['email_type']?$job_link['email_type']:1;
	        $data['link_man']=$job_link['link_man']?$job_link['link_man']:'';
	        $data['link_moblie']=$job_link['link_moblie']?$job_link['link_moblie']:'';
	        $data['email']=$job_link['email']?$job_link['email']:'';
	        $data['error']=1;
	        echo json_encode($data);die;
	    }
	}

	/*wxapp基本信息保存*/
	function saveinfo_action(){
	    $_POST=$this->post_trim($_POST);
		if(!$_POST['name']||!$_POST['hy']||!$_POST['pr']||!$_POST['cityid']||!$_POST['mun']||!$_POST['address']||!$_POST['linktel']||!$_POST['content']||!$_POST['uid']){
			$data['error']=3;
			$data['msg']='请完善信息';
			echo json_encode($data);die;
		}else{
		    $_POST['name']=$this->stringfilter($_POST['name']);
		    $_POST['shortname']=$this->stringfilter($_POST['shortname']);
			$row=$this->obj->DB_select_once("company","`uid`='".$_POST['uid']."'");
			$comname=$this->obj->DB_select_all('company',"`uid`<>'".$_POST['uid']."' and `name`='".$_POST['name']."'","`uid`");
			if($comname){
				$data['error']=3;
				$data['msg']='企业全称已存在';
				echo json_encode($data);die;
			}
			if($row['moblie_status']==1){//已绑定手机,不许修改
				unset($_POST['linktel']);
			}else{
				if($row['moblie_status']!=1){
				    $mobile=$this->obj->DB_select_num("member","`uid`<>'".$_POST['uid']."' and `moblie`='".$_POST['linktel']."'","`uid`");
					if($mobile>0){
						$data['error']=3;
						$data['msg']='手机已存在';
						echo json_encode($data);die;
					}else{
						$mvalue['moblie']=$_POST['linktel'];
					}
				}
			}
			if($row['email_status']==1){//已绑定邮箱,不许修改
				unset($_POST['linkmail']);
			}else{
				if($row['email_status']!=1&&!empty($_POST['linkmail'])){
					$email=$this->obj->DB_select_num("member","`uid`<>'".$_POST['uid']."' and `email`='".$_POST['linkmail']."'","`uid`");
					if($email>0){
						$data['error']=3;
						$data['msg']='邮箱已存在';
						echo json_encode($data);die;
					}else{
						$mvalue['email']=$_POST['linkmail'];
					}
				}
			}
			if($row['yyzz_status']=='1'){//营业执照已认证，不让修改
				$_POST['name'] = $row['name'];
			}
			$cert_email = $this->obj->DB_select_once("company_cert","`uid`='".$_POST['uid']."' and `type`='1'");
			if(is_array($cert_email)){
				if($cert_email['check'] != $_POST['linkmail']){
					$_POST['email_status'] = "0";
					$this->obj->DB_delete_all("company_cert","`id`='".$cert_email['id']."'");
				}
			}
		 	//是否修改验证手机
			$cert_tel = $this->obj->DB_select_once("company_cert","`uid`='".$_POST['uid']."' and `type`='2'");
			if(is_array($cert_tel)){
				if($cert_tel['check'] != $_POST['linktel']){
					$_POST['moblie_status'] = "0";
					$this->obj->DB_delete_all("company_cert","`id`='".$cert_tel['id']."'");
				}
			}
			$_POST['content']=$this->stringfilter($_POST['content']);
			$_POST['address']=$this->stringfilter($_POST['address']);
			$_POST['linkman']=$this->stringfilter($_POST['linkman']);
			$_POST['linkjob']=$this->stringfilter($_POST['linkjob']);
			$_POST['busstops']=$this->stringfilter($_POST['busstops']);
			$linkphone=array();
			if($_POST['phonetwo']){
				if($_POST['phoneone']){
					$linkphone[]=$_POST['phoneone'];
					unset($_POST['phoneone']);
				}
				if($_POST['phonetwo']){
					$linkphone[]=$_POST['phonetwo'];
					unset($_POST['phonetwo']);
				} 
				if($_POST['phonethree']){
					$linkphone[]=$_POST['phonethree'];
					unset($_POST['phonethree']);
				} 
			}
			$_POST['linkphone']=@implode('-',$linkphone);
			$where['uid']=(int)$_POST['uid'];
			$_POST['content'] = str_replace(array("&amp;","background-color:#ffffff","background-color:#fff"),array("&",'',''),html_entity_decode($_POST['content'],ENT_QUOTES));
			if($_FILES['photos']['tmp_name']){
				$UploadM=$this->MODEL('upload');
			    $upload=$UploadM->Upload_pic("../../data/upload/company/",false);
			    $pictures=$upload->picture($_FILES['photos']);
			    $UploadM->picmsg($pictures,$_SERVER['HTTP_REFERER']);
			    $pictures = str_replace("../../data/upload/company","/data/upload/company",$pictures);
			    $_POST['comqcode']=$pictures;
				if($row['comqcode'] && (file_exists(str_replace('./',APP_PATH,$row['comqcode'])))){
					unlink_pic("../.".$row['comqcode']);
				}
			}
			$_POST['lastupdate']=mktime();
			delfiledir("../data/upload/tel/".$_POST['uid']);
			//unset($_POST['submit']);
			$nid=$this->obj->update_once("company",$_POST,$where);
			if($nid){
				if(!empty($mvalue)){
					$this->obj->update_once('member',$mvalue,$where);
				}
				$sql2['com_name']=$_POST['name'];
				$sql2['pr']=$_POST['pr'];
				$sql2['mun']=$_POST['mun'];
				$sql2['com_provinceid']=$_POST['provinceid'];
				$this->obj->update_once("company_job",$sql2,$where);
				$sql['com_name']=$_POST['name'];
				if($row['name']!=$_POST['name']){
					$this->obj->update_once("partjob",$sql,array("uid"=>$_POST['uid']));
					$this->obj->update_once("userid_job",$sql,array("com_id"=>$_POST['uid']));
					$this->obj->update_once("fav_job",$sql,array("com_id"=>$_POST['uid']));
					$this->obj->update_once("report",array("r_name"=>$_POST['name']),array("c_uid"=>(int)$_POST['uid']));
					$this->obj->update_once("blacklist",$sql,array("c_uid"=>$_POST['uid']));
					$this->obj->update_once("msg",$sql,array("job_uid"=>$_POST['uid']));
				}
				if($row['lastupdate']<1){
					if($this->config['integral_userinfo_type']=="1"){
						$auto=true;
					}else{
						$auto=false;
					}
					$this->sendredpack(array('type'=>'4','uid'=>$_POST['uid']));
					$IntegralM=$this->MODEL('integral');
					$IntegralM->company_invtal($_POST['uid'],$this->config['integral_userinfo'],$auto,"首次填写基本资料",true,2,'integral',25);
				}
				$data['error']=1;
				$data['msg']='信息更新成功';
				echo json_encode($data);die;
			}else{
				$data['error']=2;
				$data['msg']='信息更新失败';
				echo json_encode($data);die;
			}
		}
	}
	/*wxapp保存LOGO*/
	function savephoto_action(){
		if(!$_POST['uid']){
			$data['error']=3;
			$data['msg']='参数不正确';
		}else{
			if($_FILES['photos']['tmp_name']){
				$UploadM=$this->MODEL('upload');
			    $upload=$UploadM->Upload_pic("../../data/upload/company/",false);
			    $pictures=$upload->picture($_FILES['photos']);
			    $UploadM->picmsg($pictures,$_SERVER['HTTP_REFERER']);
			    $logo = str_replace("../../data/upload/company","./data/upload/company",$pictures);
			}
			$company=$this->obj->DB_select_once("company","`uid`='".$_POST['uid']."'","`logo`");
			$comsql['logo']=$logo;
			if ($this->config['com_logo_status']==1){
			    $logo='';
			    $comsql['logo_status']=1;
			}else{
			    $comsql['logo_status']=0;
			}
			$companyM = $this->MODEL('company');
			$nid = $companyM->UpdateCompany($comsql,array('uid'=>$_POST['uid']));
			$this->obj->DB_update_all("answer","`pic`='".$logo."'","`uid`='".$_POST['uid']."'");
			$this->obj->DB_update_all("question","`pic`='".$logo."'","`uid`='".$_POST['uid']."'");
			$this->obj->DB_update_all("company_job","`com_logo`='".$logo."'","`uid`='".$_POST['uid']."'");
			if(!$company['logo']){
			    $this->MODEL('integral')->get_integral_action($_POST['uid'],"integral_avatar","上传LOGO");
			}else{
			    unlink_pic(str_replace("./data/upload",APP_PATH."data/upload",$company['logo']));
			}
			if($nid){
				$data['error']=1;
				$data['msg']='上传成功';
			}else{
				$data['error']=2;
				$data['msg']='上传失败';
			}
		}
		echo json_encode($data);die;
	}
	function savepw_action(){//修改密码
	    $_POST=$this->post_trim($_POST);
		if(!$_POST['oldpassword']||!$_POST['password']||!$_POST['uid']){
			$data['error']=3;
			echo json_encode($data);die;
		}else{
		    $uid = (int)$_POST['uid'];
			$info = $this->obj->DB_select_once("member","`uid`='".$uid."'");
			if(is_array($info)){
				$oldpass = md5(md5($_POST['oldpassword']).$info['salt']);
				if($info['password']!=$oldpass){
					$data['error']=2;
					echo json_encode($data);die;
				}
				if($this->config['sy_uc_type']=="uc_center" && $info['name_repeat']!="1"){
					$this->uc_open();
					$ucresult= uc_user_edit($info['username'], $_POST['oldpassword'], $_POST['password'], "","1");
					if($ucresult == -1) {
						$data['error']=2;
						echo json_encode($data);die;
					}
				}else{
					$salt = substr(uniqid(rand()), -6);
					$pass2 = md5(md5($_POST['password']).$salt);
					$sql['password']=$pass2;
					$sql['salt']=$salt;
					$where['uid']=$uid;
					$this->obj->update_once("member",$sql,$where);
				}
				$this->member_log("修改密码", $uid, 2, 8);
				$data['error']=1;//修改成功,请重新登录！
				echo json_encode($data);die;
			}
		}
	}
	function paylist_action(){//消费记录
		$where=1;
		$sdate=$_POST['sdate'];
		$edate=$_POST['edate'];
		$page=$_POST['page'];
		$limit=$_POST['limit'];
		$order=$_POST['order'];
		$limit=!$limit?10:$limit;
		if($_POST['uid']){
			$where.=" and `com_id`='".(int)$_POST['uid']."'";
		}
		if($sdate){//开始时间
			$where.=" and `pay_time`>'".strtotime($sdate)."'";
		}
		if($edate){//结束时间
			$where.=" and `pay_time`<'".strtotime($edate)."'";
		}
		if($order){//排序
			$where.=" order by ".$order;
		}else{
			$where.=" order by id desc";
		}
		if($page){//分页
			$pagenav=($page-1)*$limit;
			$where.=" limit $pagenav,$limit";
		}else{
			$where.=" limit $limit";
		}
		$rows=$this->obj->DB_select_all("company_pay",$where);
		if(is_array($rows) && !empty($rows)){
			foreach($rows as $key=>$va){
				$list[$key]['id']			=$va['id'];
				$list[$key]['order_id']	=$va['order_id'];
				$list[$key]['order_price']	=$va['order_price'];
				$list[$key]['pay_time']	=$va['pay_time'];
				$list[$key]['pay_state']	=$va['pay_state'];
				$list[$key]['com_id']		=$va['com_id'];
				$list[$key]['pay_remark']	=$va['pay_remark'];
				$list[$key]['type']			=$va['type'];
			}
			foreach($list as $k=>$v){
				if(is_array($v)){
					foreach($v as $key=>$val){
						$list[$k][$key]=isset($val)?$val:'';
					}
				}else{
					$list[$k]=isset($v)?$v:'';
				}
			}
			$data['list']=count($list)?$list:array();
			$data['error']=1;
		}else{
			$data['error']=2;
		}
		echo json_encode($data);die;
	}
	function hrlist_action(){//申请职位的人才
		$where="`com_id`='".(int)$_POST['uid']."'";
		$order=$_POST['order'];
		$keyword=$this->stringfilter($_POST['keyword']);
		if($keyword){//关键字
			$rows=$this->obj->DB_select_all("resume","`name` like '%".$keyword."%'","uid,`sex`");
			if(is_array($rows) && !empty($rows)){
				foreach($rows as $v){
					$uidarr[]=$v['uid'];
				}
			}
			$where.=" and uid in (".pylode(',',$uidarr).")";
		}
		$total=$this->obj->DB_select_num("userid_job",$where);
		if($order){//排序
			$where.=" order by ".$order;
		}else{
			$where.=" order by id desc";
		}
		$rows=$this->obj->DB_select_all("userid_job",$where);
		if(is_array($rows) && !empty($rows)){
			$eid=array();
			foreach($rows as $k=>$v){
				$uid[]=$v['uid'];
				if(in_array($v['eid'],$eid)==false){
					$eid[]=$v['eid'];
				}
			}

			$userrows=$this->obj->DB_select_all("resume","`uid` in (".pylode(",",$uid).")","uid,name,sex,exp,edu,birthday");
			$resume=$this->obj->DB_select_all("resume_expect","`id` in (".pylode(",",$eid).")","id,job_classid");
			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".(int)$_POST['uid']."' and `uid` in (".pylode(",",$uid).")","uid,jobid");
			include(PLUS_PATH."user.cache.php");
			include(PLUS_PATH."job.cache.php");
			foreach($rows as $key=>$va){
				foreach($userrows as $val){
					if($va[uid]==$val[uid]){
						$list[$key]['uname']=$val['name'];
						if($val['birthday']){
							$birthday=@explode('-',$val['birthday']);
							$list[$key]['age']=date("Y")-$birthday[0];
						}
						$list[$key]['exp_n']=$userclass_name[$val['exp']];
						$list[$key]['edu_n']=$userclass_name[$val['edu']];
					}
				}
				foreach($resume as $v){
					$jobname=array();
					if($v['id']==$va['eid']){
						$jobclassid=@explode(',',$v['job_classid']);
						foreach($jobclassid  as $jval){
							$jobname[]=$job_name[$jval];
						} 
						$list[$key]['job_n']=@implode(',',$jobname);
					}
				}
				foreach($userid_msg as $val){
				    if($va['uid']==$val['uid'] && $val['jobid']==$va['job_id']){
				        $list[$key]['userid_msg']=1;
				    }
				}
				$list[$key]['id']			=$va['id'];
				$list[$key]['uid']			=$va['uid'];
				$list[$key]['job_id']		=$va['job_id'];
				$list[$key]['job_name']	=$va['job_name'];
				//$list[$key]['com_name']	=$va['com_name'];
				//$list[$key]['com_id']		=$va['com_id'];
				$list[$key]['eid']			=$va['eid'];
				$list[$key]['type']			=$va['type'];
				$list[$key]['is_browse']	=$va['is_browse'];
				$list[$key]['datetime_n'] =date("Y-m-d",$va['datetime']);	
			}
			foreach($list as $k=>$v){
				if(is_array($v)){
					foreach($v as $key=>$val){
						$list[$k][$key]=isset($val)?$val:'';
					}
				}else{
					$list[$k]=isset($v)?$v:'';
				}
			}
			$data['list']=count($list)?$list:array();
			$data['error']=1;
		}else{
			$data['error']=2;
		}
		echo json_encode($data);die;
	}
	function hrset_action(){
		if(!$_POST['uid'] || !$_POST['id']||!$_POST['browse']){
			$data['error']=3;
			$data['msg']='参数不正确';
			echo json_encode($data);die;
		}
		$id=(int)$_POST['id'];
		$browse=(int)$_POST['browse'];
		$uid=(int)$_POST['uid'];
		if($id&&$browse){
		    $row = $this->obj->DB_select_once("userid_job","`id`='".$id."' and `com_id`='".$uid."'","`uid`,`eid`,`job_id`,`type`");
		    if($row['type']==1){
		        $this->obj->DB_update_all("company_job","`operatime`='".time()."'","`id`='".$row['job_id']."' and `uid`='".$uid."'");
		    }elseif($row['type']==2){
		        $this->obj->DB_update_all("lt_job","`operatime`='".time()."'","`id`='".$row['job_id']."' and `uid`='".$uid."'");
		    }
			$nid=$this->obj->update_once("userid_job",array('is_browse'=>$browse),array("com_id"=>$uid,"id"=>$id));
			$this->member_log("更改申请职位状态（ID:".$id."）", $uid, 2, 6, 2);
			if($browse==4){
			    $resumeexp=$this->obj->DB_select_once("resume_expect","`id`='".$row['eid']."' and `r_status`<>'2' and `status`='1'",'uid,uname');
				$member=$this->obj->DB_select_once("member","`uid`='".$uid."'",'username');
				$uid=$this->obj->DB_select_once("resume","`uid`='".$resumeexp['uid']."'","telphone,email");
				if($row['type']==1){
				    $comjob=$this->obj->DB_select_once("company_job","`uid`='".$uid."' and `id`='".$row['job_id']."'","name,com_name");
				}else{
				    $comjob=$this->obj->DB_select_once("lt_job","`uid`='".$uid."' and `id`='".$row['job_id']."'","`job_name` as `name`,com_name");
				}
				$data1['uid']=$resumeexp['uid'];
				$data1['cname']=$member['username'];
				$data1['name']=$resumeexp['uname'];
				$data1['type']="sqzwhf";
				$data1['cuid']=$uid;
				$data1['company']=$comjob['com_name'];
				$data1['jobname']=$comjob['name'];
				if($this->config['sy_msg_sqzwhf']=='1'&&$uid["telphone"]&&$this->config["sy_msguser"]&&$this->config["sy_msgpw"]&&$this->config["sy_msgkey"]&&$this->config['sy_msg_isopen']=='1'){
					$data1["moblie"]=$uid["telphone"];
				}
				if($this->config['sy_email_sqzwhf']=='1'&&$uid["email"]&&$this->config['sy_email_set']=="1"){
					$data1["email"]=$uid["email"];
				}
				if($data1["email"]||$data1['moblie']){
					$notice = $this->MODEL('notice');
					$notice->sendEmailType($data1);
					$notice->sendSMSType($data1);
				}
			}
			if($nid){
				$data['error']=1;
			}else{
				$data['error']=2;
				$data['msg']='设置失败';
			}
		}
		echo json_encode($data);die;
	}
	function hr_del_action(){
		//$_POST = $this->CheckAppUser();
		if(!$_POST['uid'] || !$_POST['ids']){
			$data['error']=3;
			$data['msg']='参数不正确';
			echo json_encode($data);die;
		}
		$id=$this->obj->DB_delete_all("userid_job","`com_id`='".(int)$_POST['uid']."' and `id` in (".$_POST['ids'].")","");
		if($id){
			$data['error']=1;
		}else{
			$data['error']=2;
			$data['msg']='删除失败';
		}
		echo json_encode($data);die;
	}
	function downlist_action(){//已下载简历的人才
		$where=1;
		$order=$_POST['order'];
		$keyword=$this->stringfilter($_POST['keyword']);
		if($_POST['uid']){
			$where.=" and `comid`='".(int)$_POST['uid']."'";
		}
		if($keyword){//关键字
			$rows=$this->obj->DB_select_all("resume","`name` like '%".$keyword."%'","uid");
			if(is_array($rows) && !empty($rows)){
				foreach($rows as $v){
					$uidarr[]=$v['uid'];
				}
			}
			$where.=" and uid in (".pylode(',',$uidarr).")";
		}
		$total=$this->obj->DB_select_num("down_resume",$where);
		if($order){//排序
			$where.=" order by ".$order;
		}else{
			$where.=" order by id desc";
		}
		$rows=$this->obj->DB_select_all("down_resume",$where);
		if(is_array($rows) && !empty($rows)){
		    $day=array();
			include PLUS_PATH."/user.cache.php";
			include PLUS_PATH."/job.cache.php";
			foreach($rows as $k=>$v){
				$uid[]=$v['uid'];		
				$day[$k]['id']=$v['id'];
				$day[$k]['downtime']=date("Y-m-d",$v['downtime']);	
			}	
			foreach ($day as $value) {
				if(isset($res[$value['downtime']])){
					unset($value['downtime']);
				}else{
					$res[$value['downtime']] = $value;
				}
			}
	
			$resume=$this->obj->DB_select_all("resume","`uid` in (".pylode(",",$uid).")","`uid`,`name`,`edu`,`exp`,`sex`,`birthday`,`def_job` as `eid`");
			$userrows=$this->obj->DB_select_all("resume_expect","`uid` in (".pylode(",",$uid).")","`id`,`uid`,`name`,`sex`,`edu`,`exp`,`birthday`,`job_classid`,`uname`");
			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".(int)$_POST['uid']."' and `uid` in (".pylode(",",$uid).")","uid");
	        
			foreach($rows as $key=>$va){	
				foreach($res as $v){
					if($v['id']==$va['id']){
						$list[$key]['downtime_n'] 	=$v['downtime'];	
					} 
				}
				foreach($resume as $val){
					if($va['uid']==$val['uid']){
						$list[$key]['name']	=$val['name'];
						$list[$key]['sex']	=$val['sex'];
						$list[$key]['edu_n']	=$userclass_name[$val['edu']];
						$list[$key]['exp_n']	=$userclass_name[$val['exp']];
						if($val['birthday']){
							$birthday=@explode('-',$val['birthday']);
							$list[$key]['age']=date("Y")-$birthday[0];
				        }
						
					}
				}
				foreach($userrows as $value){
					if($va['uid']==$value['uid']){	
					$list[$key]['uname']	=$value['uname'];	
						if($value['job_classid']){
							$job=@explode(',', $value['job_classid']);
							$jobn=array();
							foreach($job as $k){
								$jobn[]=$job_name[$k];
							}
							$list[$key]['job_n']=@implode(',',$jobn);
						}
					}
				}
				foreach($userid_msg as $v){
					if($va['uid']==$v['uid']){
						$list[$key]['userid_msg']=1;
					}
				}
				$list[$key]['id']			=$va['id'];
				$list[$key]['uid']			=$va['uid'];
				$list[$key]['eid']			=$va['eid'];
				$list[$key]['comid']		=$va['comid'];		
			}
			foreach($list as $k=>$v){
				if(is_array($v)){
					foreach($v as $key=>$val){
						$list[$k][$key]=isset($val)?$val:'';
					}
				}else{
					$list[$k]=isset($v)?$v:'';
				}
			}
			$data['list']=count($list)?$list:array();
			$data['total']=$total;
			$data['error']=1;
		}else{
			$data['error']=2;
		}
		echo json_encode($data);die;
	}
	function down_del_action(){
		//$_POST = $this->CheckAppUser();
		if(!$_POST['uid'] || !$_POST['ids']){
			$data['error']=3;
			$data['msg']='参数不正确';
			echo json_encode($data);die;
		}
		$uid = (int)$_POST['uid'];
		$nid=$this->obj->DB_delete_all("down_resume","`id`='".(int)$_POST['ids']."' and `comid`='".$uid."'"," ");
		if($nid){
		    $this->member_log("删除已下载简历记录（ID:".(int)$_POST['ids']."）", $uid, 2, 3, 3);
			$data['error']=1;
		}else{
			$data['error']=2;
			$data['msg']='删除失败';
		}
		echo json_encode($data);die;
	}
	//wxapp收藏简历记录
	function talentpoollist_action(){
		$where="`cuid`='".(int)$_POST['uid']."'";
		$keyword=$this->stringfilter($_POST['keyword']);
		if($keyword){//关键字
			$rows=$this->obj->DB_select_all("resume","`name` like '%".$keyword."%'","`uid`");
			if(is_array($rows) && !empty($rows)){
				foreach($rows as $v){
					$uidarr[]=$v['uid'];
				}
			}
			$where.=" and `uid` in (".@implode(',',$uidarr).")";
		}
		$total=$this->obj->DB_select_num("talent_pool",$where);
		$where.=" order by id desc";
		$rows=$this->obj->DB_select_all("talent_pool",$where);
		if(is_array($rows) && !empty($rows)){
		    $day=array();
			foreach($rows as $k=>$v){
				$uid[]=$v['uid'];
				$day[$k]['id']=$v['id'];
				$day[$k]['ctime']=date("Y-m-d",$v['ctime']);
			}
			foreach ($day as $value) {
				if(isset($res[$value['ctime']])){
					unset($value['ctime']);
				}else{
					$res[$value['ctime']] = $value;
				}
			}

			$userrows=$this->obj->DB_select_all("resume","`uid` in (".pylode(",",$uid).")","`uid`,`name`,`sex`,`edu`,`exp`,`def_job`,`birthday`");
			$eids=array();
			foreach($userrows as $v){
				if(in_array($v['def_job'],$eids)==false){
					$eids[]=$v['def_job'];
				}
			}
			$expect=$this->obj->DB_select_all("resume_expect","`id` in (".pylode(",",$eids).")","`id`,`uid`,`job_classid`");
			include(PLUS_PATH."job.cache.php");
			if($expect&&is_array($expect)){
				foreach($expect as $k=>$v){
					$job_classid=@explode(',',$v['job_classid']);
					$jobname=array();
					foreach($job_classid as $v){
						$jobname[]=$job_name[$v];
					}
					$expect[$k]['jobname']=implode(',',$jobname);
				}
			}
			$useridmsg=$this->obj->DB_select_all("userid_msg","`fid`='".(int)$_POST['uid']."' and `uid` in (".pylode(",",$uid).")","`uid`");
			$userid=array();
			foreach($useridmsg as $v){
				$userid[]=$v['uid'];
			}
			include(PLUS_PATH."user.cache.php");
			foreach($rows as $key=>$va){
				foreach($res as $vv){
					if($vv['id']==$va['id']){
						$list[$key]['ctime_n'] 	=$vv['ctime'];	
					} 
				}
				foreach($userrows as $val){
					if($va[uid]==$val[uid]){
						$list[$key]['uname']	=$val['name'];
						$list[$key]['sex']	=$val['sex'];
						if($val['birthday']){
							$birthday=@explode('-',$val['birthday']);
							$list[$key]['age']=date("Y")-$birthday[0];
				        }
						$list[$key]['exp_n']	=$userclass_name[$val['exp']];
						$list[$key]['edu_n']	=$userclass_name[$val['edu']];
					}
				}
				foreach($expect as $v){
					if($v['uid']==$va['uid']){
						$list[$key]['job_n']=$v['jobname'];
					}
				}
				if(in_array($va['uid'],$userid)){
					$list[$key]['userid_msg']=1;
				}else{
					$list[$key]['userid_msg']=0;
				}
				$list[$key]['id']			=$va['id'];
				$list[$key]['uid']			=$va['uid'];
				$list[$key]['eid']			=$va['eid'];
				$list[$key]['comid']		=$va['cuid'];
			}
			foreach($list as $k=>$v){
				if(is_array($v)){
					foreach($v as $key=>$val){
						$list[$k][$key]=isset($val)?$val:'';
					}
				}else{
					$list[$k]=isset($v)?$v:'';
				}
			}
			$data['list']=count($list)?$list:array();
			$data['total']=$total;
			$data['error']=1;
		}else{
			$data['error']=2;
		}
		echo json_encode($data);die;
	}
	//wxapp删除收藏简历记录
	function talentpooldel_action(){
	    $id=intval($_POST['ids']);
	    if(!$_POST['uid'] || !$id){
			$data['error']=3;
			$data['msg']='参数不正确';
			echo json_encode($data);die;
		}
		$uid = (int)$_POST['uid'];
		$id=$this->obj->DB_delete_all("talent_pool","`cuid`='".$uid."' and `id`='".$id."'");
		if($id){
		    $this->member_log("删除收藏简历记录（ID:".$id."）", $uid, 2,5,3);
			$data['error']=1;
			$data['msg']='删除成功';
		}else{
			$data['error']=2;
			$data['msg']='删除失败';
		}
		echo json_encode($data);die;
	}
	//wxapp面试邀请记录
	function invitelist_action(){//已邀请人才
		$where="`fid`='".(int)$_POST['uid']."'";
		$page=$_POST['page'];
		$limit=$_POST['limit'];
		$order=$_POST['order'];
		$keyword=$this->stringfilter($_POST['keyword']);
		$limit=!$limit?10:$limit; 
		if($keyword){//关键字
			$rows=$this->obj->DB_select_all("resume","`name` like '%".$keyword."%'","uid");
			if(is_array($rows) && !empty($rows)){
				foreach($rows as $v){
					$uidarr[]=$v['uid'];
				}
				$uidarr=pylode(',',$uidarr);
			}
			$where.=" and uid in (".$uidarr.")";
		}
		$total=$this->obj->DB_select_num("userid_msg",$where);
		if($order){//排序
			$where.=" order by ".$order;
		}else{
			$where.=" order by id desc";
		}
		if($page){//分页
			$pagenav=($page-1)*$limit;
			$where.=" limit $pagenav,$limit";
		}else{
			$where.=" limit $limit";
		}
		$rows=$this->obj->DB_select_all("userid_msg",$where);
		if(is_array($rows) && !empty($rows)){			
		    include PLUS_PATH."/user.cache.php";
			foreach($rows as $v){
				$uid[]=$v['uid'];
			}

			$userrows=$this->obj->DB_select_all("resume","`uid` in (".pylode(",",$uid).")","`name`,`sex`,`edu`,`uid`,def_job,`exp`,`birthday`");
			if($userrows&&is_array($userrows)){
				$eid=array();
				foreach($userrows as $v){ 
					$eid[]=$v['def_job'];
				}
				$expect=$this->obj->DB_select_all("resume_expect","`id` in (".pylode(",",$eid).")","`id`,`uid`,`job_classid`");
				include(PLUS_PATH."job.cache.php");
				if($expect&&is_array($expect)){
					foreach($expect as $k=>$v){
						$job_classid=@explode(',',$v['job_classid']);
						$jobname=array();
						foreach($job_classid as $v){
							$jobname[]=$job_name[$v];
						}
						$expect[$k]['jobname']=implode(',',$jobname);
					}
				}
			}
			
			foreach($rows as $key=>$va){
				foreach($userrows as $val){
					if($va[uid]==$val[uid]){
						$list[$key]['name']	=$val['name'];
						$list[$key]['sex']	=$val['sex'];
						$list[$key]['eid']	=$val['def_job'];
						$list[$key]['edu_n']	=$userclass_name[$val['edu']];
						$list[$key]['exp_n']	=$userclass_name[$val['exp']];
						if($val['birthday']){
							$birthday=@explode('-',$val['birthday']);
							$list[$key]['age']=date("Y")-$birthday[0];
				        }
					}
				}
				foreach($expect as $v){
					if($v['uid']==$va['uid']){
						$list[$key]['job_classid']=$v['jobname'];
					}
				}
				$list[$key]['id']			=$va['id'];
				$list[$key]['uid']			=$va['uid'];
				$list[$key]['title']		=$va['title'];
				$list[$key]['fid']			=$va['fid'];
				$list[$key]['fname']		=$va['fname'];
				$list[$key]['address']		=$va['address'];
				$list[$key]['linkman']		=$va['linkman'];
				$list[$key]['linktel']		=$va['linktel'];
				
				$list[$key]['is_browse']	=$va['is_browse'];
				$list[$key]['intertime']	=$va['intertime'];
				$list[$key]['content']		=$va['content'];
				$list[$key]['jobname']		=$va['jobname'];
				$list[$key]['datetime_n'] 	=date("Y-m-d",$va['datetime']);

			}
			foreach($list as $k=>$v){
				if(is_array($v)){
					foreach($v as $key=>$val){
						$list[$k][$key]=isset($val)?$val:'';
					}
				}else{
					$list[$k]=isset($v)?$v:'';
				}
			}
			$data['list']=count($list)?$list:array();
			$data['total']=$total;
			$data['error']=1;

		}else{
			$data['error']=2;
		}
		echo json_encode($data);die;
	}
	//wxapp删除面试邀请记录
	function invite_del_action(){
	    $id = intval($_POST['ids']);
	    if(!$_POST['uid'] || !$id){
			$data['error']=3;
			$data['msg']='参数不正确';
			echo json_encode($data);die;
		}
		$uid = (int)$_POST['uid'];
		$id=$this->obj->DB_delete_all("userid_msg","`id`='".$id."' and `fid`='".$uid."'");
		if($id){
		    $this->member_log("删除面试邀请记录（ID:".$id."）", $uid, 2,4,3);
			$data['error']=1;
		}else{
			$data['error']=2;
			$data['msg']='删除失败';
		}
		echo json_encode($data);die;
	}
	
	function msglist_action(){//求职咨询
		$where=1;
		$page=$_POST['page'];
		$limit=$_POST['limit'];
		$order=$_POST['order'];
		$limit=!$limit?10:$limit;
		if($_POST['uid']){
			$where.=" and `job_uid`='".(int)$_POST['uid']."' and `del_status`<>'1'";
		}
		if($order){//排序
			$where.=" order by ".$order;
		}else{
			$where.=" order by id desc";
		}
		if($page){//分页
			$pagenav=($page-1)*$limit;
			$where.=" limit $pagenav,$limit";
		}else{
			$where.=" limit $limit";
		}
		$rows=$this->obj->DB_select_all("msg",$where);
		if(is_array($rows) &&!empty($rows)){
			foreach($rows as $key=>$va){
				$list[$key]['id']			=$va['id'];
				$list[$key]['uid']			=$va['uid'];
				$list[$key]['content']		=$va['content'];
				$list[$key]['username']	=$va['username'];
				$list[$key]['jobid']		=$va['jobid'];
				$list[$key]['job_name'] 	=$va['job_name'];
				$list[$key]['datetime'] 	=$va['datetime'];
				$list[$key]['reply'] 		=$va['reply'];
			}
			foreach($list as $k=>$v){
				if(is_array($v)){
					foreach($v as $key=>$val){
						$list[$k][$key]=isset($val)?$val:'';
					}
				}else{
					$list[$k]=isset($v)?$v:'';
				}
			}
			$data['list']=count($list)?$list:array();
			$data['error']=1;
		}else{
			$data['error']=2;
		}
		echo json_encode($data);die;
	}

	//会员积分处理
	//1添加职位，2修改职位,3刷新职位
	function get_com($type,$uid){
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$uid."'");
		if($statis['rating']){
		    $rating = $this->obj->DB_select_once('company_rating','`id`="'.$statis['rating'].'"','`type`');
		    if ($statis['vip_etime']>time() || $statis['vip_etime']==0){
		        if ($rating['type']==1){
		            if($type==1){
		                if($statis['job_num']>0){
		                    $value="`job_num`=`job_num`-1";
		                }else{
		                    if($this->config['com_integral_online']=="1" || $this->config['com_integral_online']=="3"){
		                        $this->intergal($type,$statis,$uid);
		                    }else{
		                        $data['error']=2;//会员发布职位用完.
		                        $data['msg']='会员发布职位用完';
		                        echo json_encode($data);die;
		                    }
		                }
		            }elseif($type==3){
		                if($statis['breakjob_num']>0){
		                    $value="`breakjob_num`=`breakjob_num`-1";
		                }else{
		                    if($this->config['com_integral_online']=="1" || $this->config['com_integral_online']=="3"){
		                        $this->intergal($type,$statis,$uid);
		                    }else{
		                        $data['error']=2;//会员刷新职位用完
		                        $data['msg']='会员刷新职位用完';
		                        echo json_encode($data);die;
		                    }
		                }
		            }
		            if($value){
		                $this->obj->DB_update_all("company_statis",$value,"uid='".$uid."'");
		            }
		        }
		    }else{
		        $data['error']=2;
		        $data['msg']='会员已过期';
		        echo json_encode($data);die;
		    }
		}else{
			$this->intergal($type,$statis,$uid);
		}
	}
	// 积分 扣除
	function intergal($type,$statis,$uid){
		if($type==1 && $this->config['integral_job']){
			if($this->config['integral_job_type']=="1"){
				$auto=true;
			}else{
			    if($statis['integral']<$this->config['integral_job']*$this->config['integral_proportion']){
					$data['error']=2;//积分不够
					$data['msg']='积分不够';
					echo json_encode($data);die;
				}
				$auto=false;
			}
			$nid=$this->MODEL('integral')->company_invtal($uid,$this->config['integral_job']*$this->config['integral_proportion'],$auto,"发布职位");//积分操作记录
		}elseif($type==2 && $this->config['integral_jobedit']){
			if($this->config['integral_job_type']=="1")
			{
				$auto=true;
			}else{
				if($statis['integral']<$this->config['integral_jobedit']){
					$data['error']=2;//积分不够
					$data['msg']='积分不够';
					echo json_encode($data);die;
				}
				$auto=false;
			}
			$nid=$this->MODEL('integral')->company_invtal($uid,$this->config['integral_jobedit'],$auto,"修改职位");//积分操作记录
		}elseif($type==3 && $this->config['integral_jobefresh']){
			if($this->config['integral_job_type']=="1")
			{
				$auto=true;
			}else{
			    if($statis['integral']<$this->config['integral_jobefresh']*$this->config['integral_proportion']){
					$data['error']=2;//积分不够
					$data['msg']='积分不够';
					echo json_encode($data);die;
				}
				$auto=false;
			}
			$nid=$this->MODEL('integral')->company_invtal($uid,$this->config['integral_jobefresh']*$this->config['integral_proportion'],$auto,"刷新职位");//积分操作记录
		}
	}
	function deljob_action(){//删除职位
		if(!$_POST['ids'] || !$_POST['uid']){
			$data['error']=3;//参数不正确
			$data['msg']="参数不正确";
			echo json_encode($data);die;
		}
		$ids=@explode(",",$_POST['ids']);
		$delid=pylode(",",$ids);
		$uid=(int)$_POST['uid'];
		$nid=$this->obj->DB_delete_all("company_job","`id` in (".$delid.") and `uid`='".$uid."'"," ");
		$newest=$this->obj->DB_select_once("company_job","`uid`='".$uid."' order by lastupdate DESC");
	    $this->obj->update_once("company",array("jobtime"=>$newest['lastupdate']),array("uid"=>$uid));
	    if($nid){
	        $this->member_log("删除职位", $uid, 2,1,3);
	    	$data['error']=1;//删除成功
	    }else{
	    	$data['error']=2;
			$data['msg']="删除失败";
	    }
		echo json_encode($data);die;
	}
	function refresh_action(){
	    $uid = $_POST['uid'];
	    if($_POST['jobid']){
	        $jobids = intval($_POST['jobid']);
	    }else{
	        $jobs=$this->obj->DB_select_all("company_job","`state`=1 and `r_status`<>2 and `status`<>1 and `uid`='".$uid."'");//招聘中职位
	        if($jobs && is_array($jobs)){//获取职位ID
	            foreach($jobs as $key=>$v){
	                $ids[]=$v['id'];
	            }
	            $jobids ="".@pylode(",",$ids)."";
	        }
	    }
	    if($jobids=='' || !$jobids){
	        $this->errjson('您暂无发布中的职位',11);
	    }
	    
	    $statis = $this->company_satic($uid);
	    //检查是否达到每日最大操作次数
	    $companyM=$this->MODEL('company');
	    $result = $companyM->comVipDayActionCheck('refreshjob',$uid);
	    if($result['status']!=1){
	        $this->errjson($result['msg'],10);
	    }
	    $refresh['jobid']=$jobids;
	    $refresh['uid']=$uid;
	    $comtcM=$this->MODEL('comtc');
	    $return = $comtcM->refresh_job($refresh);
	    if($return['status']){
	        //职位刷新成功
	        $data['error']=0;
	        $data['errmsg']=$return['msg'];
	        $data['lastupdate']=date('Y-m-d',time());
	        echo json_encode($data);die;
	    }else if($return['status']==2){
	        //套餐不足，金额消费
	        $this->errjson($return['msg'],12);
	    }else{
	        //职位刷新失败
	        $this->errjson($return['msg'],13);
	    }
	}
	//wxapp浏览简历记录
	function look_resume_action(){//简历浏览记录 
		if(!$_POST['uid']){
			$data['error']=3;
			echo json_encode($data);die;
		}
		$where.="`com_id`='".(int)$_POST['uid']."' and `com_status`='0' order by `datetime` desc";
		$rows=$this->obj->DB_select_all("look_resume",$where);
		if(is_array($rows) && !empty($rows)){
			$resume_id=$uids=$day=array();
			foreach($rows as $k=>$v){
				$com_id[] = $v['com_id'];
				if(in_array($v['resume_id'],$resume_id)==false){
					$resume_id[] = $v['resume_id'];
				} 
				if(in_array($v['uid'],$uids)==false){
					$uids[] = $v['uid'];
				} 
				$day[$k]['id']=$v['id'];
				$day[$k]['datetime']=date("Y-m-d",$v['datetime']);
			}
			foreach ($day as $value) {
				if(isset($res[$value['datetime']])){
					unset($value['datetime']);
				}else{
					$res[$value['datetime']] = $value;
				}
			}
			
			$com=$this->obj->DB_select_all("company","`uid` IN (".pylode(",",$com_id).")","uid,name");
			$expect=$this->obj->DB_select_all("resume_expect","`id` in (".pylode(",",$resume_id).")","`uname`,`sex`,`birthday`,`exp`,`edu`,`id`,`uid`,`job_classid`"); 
			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".(int)$_POST['uid']."' and `uid` in (".pylode(",",$uids).")","uid");
			if(is_array($expect)){
				include(PLUS_PATH."job.cache.php");
				include(PLUS_PATH."user.cache.php");
				foreach($rows as $key=>$val){
					foreach($res as $vv){
						if($vv['id']==$val['id']){
							$list[$key]['datetime_n'] =$vv['datetime'];	
						} 
					}
					foreach($expect as $va){ 
						if($val['resume_id']==$va['id']){ 
							$jobname=array(); 
							$jobclassid=@explode(',',$va['job_classid']);
							foreach($jobclassid  as $jval){
								$jobname[]=$job_name[$jval];
							}
							$rows[$key]['job_n']=@implode(',',$jobname);
							$rows[$key]['uname']=$va['uname']; 
							$rows[$key]['uid']=$va['uid'];
							$rows[$key]['resume_id']=$va['id'];
							$rows[$key]['exp']=$userclass_name[$va['exp']];
							$rows[$key]['edu']=$userclass_name[$va['edu']];
							if($va['birthday']){
								$birthday=@explode('-',$va['birthday']);
								$rows[$key]['age']=date("Y")-$birthday[0];
							}else{
								$rows[$key]['age']="保密";
							}
						}
					}
					foreach($userid_msg as $v){
						if($val['uid']==$v['uid']){
							$rows[$key]['userid_msg']=1;
						}
					}
				}
			}
			foreach($rows as $key=>$va){
				$list[$key]['id']			=$va['id'];
				$list[$key]['uid']			=$va['uid'];
				$list[$key]['resume_id']	=$va['resume_id'];
				$list[$key]['job_n']		=$va['job_n'];
				$list[$key]['exp_n']		=$va['exp']; 
				$list[$key]['edu_n']		=$va['edu']; 
				$list[$key]['uname']		=$va['uname'];
				$list[$key]['age']			=$va['age']; 
				$list[$key]['userid_msg']	=$va['userid_msg'];
			}
			foreach($list as $k=>$v){
				if(is_array($v)){
					foreach($v as $key=>$val){
						$list[$k][$key]=isset($val)?$val:'';
					}
				}else{
					$list[$k]=isset($v)?$v:'';
				}
			}
			$data['list']=count($list)?$list:array();
			$data['error']=1;
		}else{
			$data['error']=2;
		}
		echo json_encode($data);die;
	}
	//wxapp删除浏览简历记录
	function look_resume_del_action(){//删除简历浏览记录
		if(!$_POST['uid'] || !$_POST['ids']){
			$data['error']=3;
			$data['msg']='参数不正确';
			echo json_encode($data);die;
		}
		$uid = (int)$_POST['uid'];
		$id=$this->obj->DB_update_all("look_resume","`com_status`='1'","`com_id`='".$uid."' and `id` in (".$_POST['ids'].")","");
		if($id){
		    $this->member_log("删除浏览的简历记录（ID:".(int)$_POST['ids']."）", $uid, 2,26,3);
			$data['error']=1;
		}else{
			$data['error']=2;
			$data['msg']='删除失败';
		}
		echo json_encode($data);die;
	}
	//wxapp谁看过我记录
	function look_job_action(){//职位浏览记录	
		if(!$_POST['uid']){
			$data['error']=3;
			echo json_encode($data);die;
		}
		$where.="`com_id`='".(int)$_POST['uid']."' and `com_status`='0' order by `datetime` desc";
		$rows=$this->obj->DB_select_all("look_job",$where);
		if(is_array($rows) && !empty($rows)){
		    $day=array();
			foreach($rows as $key=>$v){
				$uid[]=$v['uid'];
				$jobid[]=$v['jobid'];
				$day[$key]['id']=$v['id'];
				$day[$key]['datetime']=date("Y-m-d",$v['datetime']);
			}
			foreach ($day as $value) {
				if(isset($res[$value['datetime']])){
					unset($value['datetime']);
				}else{
					$res[$value['datetime']] = $value;
				}
			}

			$cjob=$this->obj->DB_select_all("company_job","`id`in(".pylode(',',$jobid).")","`name`,`id`");
			$resume=$this->obj->DB_select_all("resume","`uid` in (".pylode(",",$uid).") and `def_job`>0","`uid`,`name`,`edu`,`exp`,`sex`,`birthday`,`def_job` as `eid`");
			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".$_POST['uid']."' and `uid` in (".pylode(",",$uid).")","uid");
			include(PLUS_PATH."user.cache.php");
			include(PLUS_PATH."job.cache.php");
			foreach($resume as $val){
				$eid[]=$val['eid'];
				$lookuid[]=$val['uid'];
			}
			$looklist=$this->obj->DB_select_all("look_job","`uid` in (".pylode(",",$lookuid).") and `com_id`='".(int)$_POST['uid']."' and `com_status`='0' order by `datetime` desc");
			$expect=$this->obj->DB_select_all("resume_expect","`id` in (".pylode(",",$eid).")","`id`,`uid`,`salary`,job_classid");
			foreach($looklist as $key=>$val){
				foreach($res as $vv){
					if($vv['id']==$val['id']){
						$list[$key]['datetime_n'] 	=$vv['datetime'];	
					} 
				}
				foreach($expect as $v){
					if($val['uid']==$v['uid']){
						$looklist[$key]['eid']=$v['id'];
						if($v['job_classid']!=""){
							$job_classid=@explode(",",$v['job_classid']);
							$jobname=array();
							foreach($job_classid as $vv){
								$jobname[]=$job_name[$vv];
							}
							$looklist[$key]['job_n']=implode(',',$jobname);
						}
					}
				}
				foreach($resume as $va){
					if($val['uid']==$va['uid']){
						$looklist[$key]['exp_n']=$userclass_name[$va['exp']];
						$looklist[$key]['edu_n']=$userclass_name[$va['edu']];
						$looklist[$key]['name']=$va['name'];
						if($va['birthday']){
							$birthday=@explode('-',$va['birthday']);
							$looklist[$key]['age']=date("Y")-$birthday[0];
						}
					}
				}
				foreach($userid_msg as $va){
					if($val['uid']==$va['uid']){
						$looklist[$key]['userid_msg']=1;
					}
				}
				foreach($cjob as $va){
					if($val['jobid']==$va['id']){
						$looklist[$key]['jobname']=$va['name'];
					}
				}
			}
			
			foreach($looklist as $key=>$va){
				$list[$key]['id']			=$va['id'];
				$list[$key]['uid']			=$va['uid'];
				$list[$key]['eid']			=$va['eid'];
				$list[$key]['jobname']		=$va['jobname']; 
				$list[$key]['job_n']		=$va['job_n'];
				$list[$key]['exp_n']		=$va['exp_n']; 
				$list[$key]['edu_n']		=$va['edu_n']; 
				$list[$key]['uname']		=$va['name']; 
				$list[$key]['age']			=$va['age']; 
				$list[$key]['userid_msg']	=$va['userid_msg'];
			}
			foreach($list as $k=>$v){
				if(is_array($v)){
					foreach($v as $key=>$val){
						$list[$k][$key]=isset($val)?$val:'';
					}
				}else{
					$list[$k]=isset($v)?$v:'';
				}
			}
			$data['list']=count($list)?$list:array();
			$data['error']=1;
		}else{
			$data['error']=2;
		}
		echo json_encode($data);die;
	}
	//wxapp删除谁看过我记录
	function look_job_del_action(){//删除职位浏览记录
		//$_POST = $this->CheckAppUser();
		if(!$_POST['uid'] || !$_POST['ids']){
			$data['error']=3;
			$data['msg']='参数不正确';
			echo json_encode($data);die;
		}
		$uid = (int)$_POST['uid'];
		$id=$this->obj->DB_update_all("look_job","`com_status`='1'","`com_id`='".$uid."' and `id` in (".$_POST['ids'].")","");
		if($id){
		    $this->member_log("删除谁看过我简历记录（ID:".(int)$_POST['ids']."）", $uid, 2,26,3);
			$data['error']=1;
			$data['msg']='删除成功';
		}else{
			$data['error']=2;
			$data['msg']='删除失败';
		}
		echo json_encode($data);die;
	}
	
	/*wxapp职位管理页面上架下架*/
	function ztjob_action(){
		if(!$_POST['id']){
			$data['error']=3;
			$data['msg']='参数不正确';
		}else{			
		    $uid = (int)$_POST['uid'];
		    if($_POST['status']==2){
		        $statis=$this->obj->DB_select_once('company_statis','`uid`='.$uid.'','`vip_etime`');
		        if($statis['vip_etime']>0 && $statis['vip_etime']<time()){
		            $data['error']=3;
		            $data['msg']='会员已到期，无法上架，请先升级会员！';
		            echo json_encode($data);die;
		        }
				$_POST['status']=0;
			}
			$id=$this->obj->DB_update_all("company_job","`status`='".(int)$_POST['status']."'","`uid`='".$uid."' and `id`='".(int)$_POST['id']."'");
			if($id){
			    $this->member_log("修改职位发布状态", $uid, 2,1,2);
				$data['error']=1;
			}else{
				$data['error']=2;
				$data['msg']='设置失败';
			}
		}
		echo json_encode($data);die;
	}
    function rating_action(){
	    $uid=(int)$_POST['uid'];
	    $data=array();
	    $com=$this->obj->DB_select_once("company","`uid`='".$uid."'");
	    $statis=$this->obj->DB_select_once("company_statis","`uid`='".$uid."'");
	    $discount=$this->obj->DB_select_once("company_rating","`id`=".$statis['rating']."","discount");
	    $data['statis']['comname']=$com['name'];
	    $data['statis']['discount']=$discount['discount'];
	    $data['statis']['rating_name']=$statis['rating_name'];
	    $data['statis']['vip_stime']=date("Y-m-d",$statis['vip_stime']);
	    if ($statis['vip_etime']==0){
	        $data['statis']['vip_etime']='永久';
	    }else{
	        $data['statis']['vip_etime']=date("Y-m-d",$statis['vip_etime']);
	    }

	    $rows=$this->obj->DB_select_all("company_rating","`category`='1' and `display`='1' order by `sort` desc");
	    if($rows&&is_array($rows)){
	        $mrating=array();
	        foreach ($rows as $k=>$v){
	            if ($v['service_time']>0){
	                $rname=array();
	                if($v['job_num']>0)        {$rname[]='发布职位:'.$v['job_num'].'份';}
	                if($v['breakjob_num']>0)   {$rname[]='刷新职位:'.$v['breakjob_num'].'份';}
	                if($v['resume']>0)         {$rname[]='下载简历:'.$v['resume'].'份';}
	                if($v['interview']>0)      {$rname[]='邀请面试:'.$v['interview'].'份';}
	                if($v['part_num']>0)       {$rname[]='发布兼职职位:'.$v['part_num'].'份';}
	                if($v['breakpart_num']>0)  {$rname[]='刷新兼职职位:'.$v['breakpart_num'].'份';}
	                if($v['msg_num']>0)        {$rname[]='短信数:'.$v['msg_num'].'份';}
	                if ($v['type']==1){
	                    $mrating[$k]['id']=$v['id'];
	                    $mrating[$k]['service_time']=$v['service_time'];
	                    $mrating[$k]['name']=$v['name'];
	                    if ($v['time_start']<time() && $v['time_end']>time()){
	                        $mrating[$k]['price']=$v['yh_price'];
	                    }else{
	                        $mrating[$k]['price']=$v['service_price'];
	                    }
	                    $mrating[$k]['rname']=@implode('+',$rname);
	                }else{
	                    $trating[$k]['id']=$v['id'];
	                    $trating[$k]['service_time']=$v['service_time'];
	                    $trating[$k]['name']=$v['name'];
	                    if ($v['time_start']<time() && $v['time_end']>time()){
	                        $trating[$k]['price']=$v['yh_price'];
	                    }else{
	                        $trating[$k]['price']=$v['service_price'];
	                    }
	                    $trating[$k]['rname']=@implode('+',$rname);
	                }
	            }
	        }
	    }
	    $service=$this->obj->DB_select_all("company_service","`display`='1'","id,name");
	    $details=$this->obj->DB_select_all("company_service_detail","1 order by (service_price+0) asc");
	    $dlist=array();
	    foreach ($details as $k=>$v){
	        $sname=array();
	        if($v['job_num']>0)        {$sname[]='发布职位:'.$v['job_num'].'份';}
	        if($v['breakjob_num']>0)   {$sname[]='刷新职位:'.$v['breakjob_num'].'份';}
	        if($v['resume']>0)         {$sname[]='下载简历:'.$v['resume'].'份';}
	        if($v['interview']>0)      {$sname[]='邀请面试:'.$v['interview'].'份';}
	        if($v['part_num']>0)       {$sname[]='发布兼职职位:'.$v['part_num'].'份';}
	        if($v['breakpart_num']>0)  {$sname[]='刷新兼职职位:'.$v['breakpart_num'].'份';}
	        $dlist[$k]['sname']=@implode('+',$sname);
	        if ($discount['discount']>0){
	            $dlist[$k]['disprice']=$v['service_price']*$discount['discount']*0.01;
	        }else{
	            $dlist[$k]['disprice']=$v['service_price'];
	        }
	        $dlist[$k]['price']=$v['service_price'];
	        $dlist[$k]['type']=$v['type'];
	        $dlist[$k]['id']=$v['id'];
	    }
	    $services=array();
	    foreach ($service as $k=>$val){
	        foreach ($dlist as $v){
	            if ($val['id']==$v['type']){
	                $name=$val['name'];
	                $services[$name][]=$v;
	            }
	        }
	    }
	    $data['services']=count($services)?$services:array();
	    $data['mrating']=count($mrating)?$mrating:array();
	    $data['trating']=count($trating)?$trating:array();
	    echo json_encode($data);die;
	}
    function pay_action(){
	    $rtype=(int)$_POST['rtype'];
	    $id=(int)$_POST['id'];
        $data=$this->getSerives($id, $rtype);
	    echo json_encode($data);die;
	}
	function dingdan_action(){
	    $getdata=$this->getOpenid($_POST['code']);
	    if($getdata['openid']){
	        $id=(int)$_POST['id'];
	        $uid=(int)$_POST['uid'];
	        $rtype=(int)$_POST['rtype'];
	        if($rtype==2){
	            if($this->config['integral_min_recharge'] && $_POST['price_int']<$this->config['integral_min_recharge']){
	                $this->errjson('充值不得低于'.$this->config['integral_min_recharge'].$this->config['integral_priceunit'].$this->config['integral_pricename'],11);
	            }
	            $order['type']='2';
	            $price = $_POST['price_int']/$this->config['integral_proportion'];
	            $price=floor($price*100)/100;
	            $order['integral']=$_POST['price_int'];
	        }else{
	            $serives=$this->getSerives($id, $rtype,$uid);
	            if ($serives['errmsg']){
	                $this->errjson($serives['errmsg'],10);
	            }
	            if ($rtype==1){
	                $order['type']='1';
	            }else{
	                $order['type']='5';
	            }
	            $price = $serives['price'];
	            $order['rating']=$id;
	        }
	        $time = time();
	        $dingdan=$time.rand(10000,99999);
	        $order['order_id']=$dingdan;
	        $order['order_price']=$price;
	        $order['order_time']=$time;
	        $order['order_state']="1";
	        $order['order_type']="wxpay";
	        $order['order_remark']=trim($_POST['remark']);
	        $order['uid']=$uid;
	        $orderid=$this->obj->insert_into("company_order",$order);
	        if($orderid){
	            $this->member_log("下单成功,订单ID".$orderid, $uid, 2, 88);
	            require_once(LIB_PATH.'wxOrder.function.php');
	            $data = wxXcxOrder(array('body'=>'充值','id'=>$dingdan,'url'=>$this->config['sy_weburl'],'total_fee'=>$price,'openid'=>$getdata['openid']));
	            echo $data;die;
	        }else{
	            $data['errmsg']="提交失败，请重新提交订单";
	            echo json_encode($data);die;
	        }
	    }else{
            if ($getdata['errcode']){
                $data['errmsg']='出错了，请重试';
            }else{
                $data['errmsg']='参数错误';
            }
        }
	}
	function getSerives($id,$rtype,$uid){
	    $data=array();
	    if ($rtype==1){
	        $rating=$this->obj->DB_select_once("company_rating","`id`=".$id."","name,service_price,time_start,time_end,yh_price");
	        $data['name']=$rating['name'];
	    	if ($rating['time_start']<time() && $rating['time_end']>time()){
	            $data['price']=$rating['yh_price'];
	        }else{
	            $data['price']=$rating['service_price'];
	        }
	    }else{
	        $statis = $this->obj->DB_select_once("company_statis","`uid`='".$uid."'","`rating`,`vip_etime`");
	        if($statis['vip_etime']>0 && $statis['vip_etime']<time()){
	            $data['errmsg']='会员已过期，请先购买会员';
	            return $data;
	        }
	        $detail=$this->obj->DB_select_once("company_service_detail","`id`=".$id."","`type`,`service_price`");
	        $service=$this->obj->DB_select_once("company_service","`id`=".$detail['type']."","`name`");
	        $ratinginfo  =  $this->obj->DB_select_once("company_rating","`id`='".$statis['rating']."'");
	        if($ratinginfo['service_discount']>0 && $ratinginfo['service_discount']<100){
	            $price = round($detail['service_price']*($ratinginfo['service_discount']/100),2);
	        }else{
	            $price = $detail['service_price'];
	        }
	        
	        $data['name']=$service['name'];
	        $data['price']=$price;
	    }
	    return $data;
	}
	//营业执照查询
	function getyyzz_action(){
	    $comcert = $this->obj->DB_select_once("company_cert","`uid`='".$_POST['uid']."' and `type`='3'");
	    if($comcert['check']){
	        if (strpos($comcert['check'], './data')!==false){
	            $comcert['check']=str_replace("./data",$this->config['sy_weburl']."/data",$comcert['check']);
	        }else{
	            if (strpos($comcert['check'], 'http')!==false){
	                $comcert['check']=str_replace("/data",$this->config['sy_weburl']."/data",$comcert['check']);
	            }
	        }
	        $return = array(
	            'url'=>$comcert['check'],
	            'status'=>$comcert['status'],
	            'error'=>1
	        );
	    }else{
	        $return = array('error'=>2);
	    }
	    echo json_encode($return);die;
	}
	//上传营业执照
	function savecert_action(){
	    if(!$_POST['uid']){
	        $data['error']=3;
	        $data['msg']='参数不正确';
	    }else{
	        if($_FILES['photos']['tmp_name']){
	            $UploadM=$this->MODEL('upload');
	            $upload=$UploadM->Upload_pic("../../data/upload/cert/",false);
	            $pictures=$upload->picture($_FILES['photos']);
	            $UploadM->picmsg($pictures,$_SERVER['HTTP_REFERER']);
	            $img = str_replace("../../data/upload","./data/upload",$pictures);
	        }
	        if($this->config['com_cert_status']=="1"){
	            $status=0;
	        }else{
	            $status=1;
	        }
	        $cert=$this->obj->DB_select_once("company_cert","`uid`='".$_POST['uid']."' and `type`=3","`check`");
	        if ($cert){
	            $nid = $this->obj->update_once('company_cert',array('check'=>$img,'status'=>$status,'ctime'=>time()),array('uid'=>$_POST['uid'],'type'=>3));
	        }else{
	            $nid = $this->obj->insert_into('company_cert',array('uid'=>$_POST['uid'],'status'=>$status,'type'=>3,'check'=>$img,'ctime'=>time()));
	        }
	        if($nid){
	            if($cert['check'] && (file_exists(str_replace('./',APP_PATH,$cert['check'])))){
	                unlink_pic("../.".$cert['check']);
	            }
	            $this->obj->DB_update_all("company","`yyzz_status`='".$status."'","`uid`='".$_POST['uid']."'");
	            $data['error']=1;
	            $data['msg']='上传成功';
	        }else{
	            $data['error']=2;
	            $data['msg']='上传失败';
	        }
	    }
	    echo json_encode($data);die;
	}
	function resumecolumn_action(){
	    $uid = intval($_POST['uid']);
	    //应聘简历数
	    $sqnum=$this->obj->DB_select_num("userid_job","`com_id`='".$uid."'");
	    $list['sqnum']=$sqnum;
	    
	    $userid_jobnum=$this->obj->DB_select_num("userid_job","`com_id`='".$uid."'and `is_browse`='1'");
	    $list['userid_jobnum']=$userid_jobnum;
	    
	    //面试邀请数
	    $userid_msgnum=$this->obj->DB_select_num("userid_msg","`fid`='".$uid."'");
	    $list['userid_msgnum']=$userid_msgnum;
	    //浏览简历数
	    $looknum=$this->obj->DB_select_num("look_resume","`com_id`='".$uid."'and `com_status`='0'");
	    $list['looknum']=$looknum;
	    //收藏简历数
	    $talentnum=$this->obj->DB_select_num("talent_pool","`cuid`='".$uid."'");
	    $list['talentnum']=$talentnum;
	    //下载简历数
	    $downnum=$this->obj->DB_select_num("down_resume","`comid`='".$uid."'");
	    $list['downnum']=$downnum;
	    //关注我的人才数
	    $atnnum=$this->obj->DB_select_num("atn","`sc_uid`='".$uid."'");
	    $list['atnnum']=$atnnum;
	    //被浏览的职位数
	    $lookjobnum=$this->obj->DB_select_num("look_job","`com_id`='".$uid."' and `com_status`='0'");
	    $list['lookjobnum']=$lookjobnum;
	    
	    $data['list'] = $list;
	    $data['error'] = 0;
	    echo json_encode($data);die;
	}
	function sysnews_action(){//消息
	    $uid = intval($_POST['uid']);
	    //应聘职位
	    $userid_job=$this->obj->DB_select_once("userid_job","`com_id`='".$uid."' and `is_browse`='1' order by datetime desc","`job_name`,`uid`,`datetime`");
	    $resume=$this->obj->DB_select_once("resume","`uid`='".$userid_job['uid']."'","`name`");
	    $userid_job['name'] = $resume['name'];
	    if($userid_job){
	        $userid_job['datetime'] = date("Y-m-d",$userid_job['datetime']);
	    }
	    $list['userid']=$userid_job;
	    $userid_jobnum=$this->obj->DB_select_num("userid_job","`com_id`='".$uid."'and `is_browse`='1'");
	    $list['userid_jobnum']=$userid_jobnum;
	    //私信
	    $sxrows=$this->obj->DB_select_once("sysmsg","`fa_uid`='".$uid."' order by `id` desc");
	    if($sxrows){
	        $sxrows['ctime'] = date("Y-m-d",$sxrows['ctime']);
	        $sxrows['content'] = strip_tags($sxrows['content']);
	    }
	    $list['sxrows']=$sxrows;
	    $sxnum=$this->obj->DB_select_num("sysmsg","`fa_uid`='".$uid."'and `remind_status`='0'");
	    $list['sxnum']=$sxnum;
	    $data['list']=$list;
	    $data['error']=0;
	    echo json_encode($data);die;
	}
	//私信
	function sxnews_action(){
	    $uid = intval($_POST['uid']);
	    $where="`fa_uid`='".$uid."' order by `id` desc limit 100";
	    $rows=$this->obj->DB_select_all("sysmsg",$where);
	    if(is_array($rows)){
	        $patten = array("\r\n", "\n", "\r");
	        foreach($rows as $key=>$value){
	            $ids[]=$value['id'];
	            $rows[$key]['ctime'] = date("Y-m-d",$value['ctime']);
	            $content = strip_tags($value['content']);
	            $content = str_replace('，点击查看公司详情>>', '。', $content);
	            $content = str_replace('。点击查看详情>>', '。', $content);
	            $content = str_replace('，点击查看>>', '。', $content);
	            $rows[$key]['content'] = str_replace($patten, "<br/>", $content);
	            $rows[$key]['remind_status'] = 1;
	            $this->obj->DB_update_all('sysmsg','`remind_status`=1',"`id` in (".pylode(',', $ids).")" );
	        }
	    }
	    $data['list']=count($rows)?$rows:array();
	    $data['error']=0;
	    echo json_encode($data);die;
	}
	function delsxnews_action(){//私信删除
	    $uid = intval($_POST['uid']);
	    if($_POST['id']){
	        $nid = $this->obj->DB_delete_all("sysmsg","`id`='".(int)$_POST['id']."' and `fa_uid`='".$uid."'");
	        if($nid){
	            $this->member_log("删除系统消息",$uid,2,18,3);
	            $data['error']=0;
	            $data['errmsg']='删除成功！';
	        }else{
	            $data['error']=-1;
	            $data['errmsg']='删除失败！';
	        }
	    }
	    echo json_encode($data);die;
	}
	//手机认证,发送短信；
	function mobliecert_action(){
	    if(!$this->config["sy_msguser"] || !$this->config["sy_msgpw"] || !$this->config["sy_msgkey"]||$this->config['sy_msg_isopen']!='1'){
	        $this->errjson('没有配置短信,请联系管理员',10);
	    }
	    if($this->config['sy_msg_cert']=="2"){
	        $this->errjson('短信通知已关闭，请联系管理员',11);
	    }else{
	        $moblie=$_POST['moblie'];
	        $num=$this->obj->DB_select_num("moblie_msg","`moblie`='".$moblie."' and `ctime`>'".strtotime('today')."'");
	        if($num>=$this->config['moblie_msgnum']){
	            $this->errjson('同一手机号一天发送次数已超',12);
	        }
	        $ip=fun_ip_get();
	        $ipnum=$this->obj->DB_select_num("moblie_msg","`ip`='".$ip."' and `ctime`>'".strtotime('today')."'");
	        if($ipnum>=$this->config['ip_msgnum']){
	            $this->errjson('同一ip一天发送次数已超',13);
	        }
	        $uid=intval($_POST['uid']);
	        $fdata=$this->forsend(array('uid'=>$uid,'usertype'=>2));
	        $randstr=rand(100000,999999);
	        $data['uid']=$uid;
	        $data['name']=$fdata['name'];
	        $data['type']="cert";
	        $data['moblie']=$moblie;
	        $data['code']=$randstr;
	        $data['date']=date("Y-m-d");
	        $notice = $this->MODEL('notice');
	        $result = $notice->sendSMSType($data);
	        $data['error']=0;
	        if($result['status']=='1'){
	            $sql['status']=0;
	            $sql['step']=1;
	            $sql['check']=$moblie;
	            $sql['check2']=$randstr;
	            $sql['ctime']=time();
	            $row=$this->obj->DB_select_once("company_cert","`uid`='".$uid."' and type='2'");
	            //更新之前认证
	            if(is_array($row)){
	                $where['uid']=$uid;
	                $where['type']='2';
	                $this->obj->update_once("company_cert",$sql,$where);
	                $this->member_log("更新手机认证",$uid,2,13,2);
	            }else{
	                $member=$this->obj->DB_select_once("member","`uid`='".$uid."'","`did`");
	                //添加新认证
	                $sql['uid']=$uid;
	                $sql['did']=$member['did'];
	                $sql['type']=2;
	                $this->obj->insert_into("company_cert",$sql);
	                $this->member_log("添加手机认证",$uid,2,13,1);
	            }
	        }
	    }
	    echo json_encode($data);die;
	}
	function bindingbox_action(){
	    $uid = intval($_POST['uid']);
	    if($_POST['id']=='tel'){
	        $row = $this->obj->DB_select_once("company_cert","`uid`='".$uid."' and `check`='".$_POST['moblie']."'");
	        if($row && is_array($row)){
	            if($row['check2']!=$_POST['code']){
	                $data['error']=10;
	                $data['errmsg']='手机验证码不正确';
	            }else{
	                //清除号码以前认证
	                $this->obj->DB_update_all("resume","`moblie_status`='0',`lastupdate`='".time()."'","`telphone`='".$row['check']."'");
	                $this->obj->DB_update_all("company","`moblie_status`='0'","`linktel`='".$row['check']."'");
	                //清除号码以前认证end
	                $this->obj->DB_update_all("member","`moblie`='".$row['check']."'","`uid`='".$uid."'");
	                $this->obj->DB_update_all("company","`linktel`='".$row['check']."',`moblie_status`='1'","`uid`='".$uid."'");
	                $this->obj->DB_update_all("company_cert","`status`='1'","`uid`='".$uid."' and `check2`='".$_POST['code']."'");
	                $this->member_log("手机绑定",$uid,2,13,1);
	                $pay=$this->obj->DB_select_once("company_pay","`pay_remark`='手机绑定' and `com_id`='".$uid."'");
	                if(empty($pay)){
	                    $this->MODEL('integral')->get_integral_action($uid,"integral_mobliecert","手机绑定");
	                }
	                $data['error']=0;
	                $data['errmsg']='手机绑定成功';
	            }
	        }else{
	            $data['errmsg']='操作错误';
	        }
	    }elseif ($_POST['id']=='email'){
	        if($this->config['sy_email_set']!="1"){
	            $data['error']=10;
	            $data['errmsg']='邮件没有配置，请联系管理员';
	        }elseif($this->config['sy_email_cert']=="2"){
	            $data['error']=11;
	            $data['errmsg']='邮件通知已关闭，请联系管理员';
	        }else{
	            $email=$_POST['email'];
	            $randstr=rand(10000000,99999999);
	            $sql['status']=0;
	            $sql['step']=1;
	            $sql['check']=$email;
	            $sql['check2']=$randstr;
	            $sql['ctime']=time();
	            $row=$this->obj->DB_select_once("company_cert","`uid`='".$uid."' and type='1'");
	            if(is_array($row)){//更新之前认证
	                $where['uid']=$uid;
	                $where['type']='1';
	                $this->obj->update_once("company_cert",$sql,$where);
	                $this->member_log("更新邮箱认证",$uid,2,13,2);
	            }else{//添加新认证
	                $member=$this->obj->DB_select_once("member","`uid`='".$uid."'","`did`");
	                $sql['uid']=$uid;
	                $sql['did']=$member['did'];
	                $sql['type']=1;
	                $this->obj->insert_into("company_cert",$sql);
	                $this->member_log("添加邮箱认证",$uid,2,13,1);
	            }
	            //发送邮件
	            $base=base64_encode($uid."|".$randstr."|".$this->config['coding']);
	            $fdata=$this->forsend(array('uid'=>$uid,'usertype'=>2));
	            $data['uid']=$uid;
	            $data['name']=$fdata['name'];
	            $data['type']="cert";
	            $data['email']=$email;
	            $url=Url("qqconnect",array('c'=>'cert','id'=>$base),"1");
	            $data['url']="<a href='".$url."'>点击认证</a> 如果您不能在邮箱中直接打开，请复制该链接到浏览器地址栏中直接打开：".$url;
	            $data['date']=date("Y-m-d");
	            $notice = $this->MODEL('notice');
	            $notice->sendEmailType($data);
	            $data['error']=0;
	        }
	    }
	    echo json_encode($data);die;
	}
	function pwd_action(){
	    $uid = intval($_POST['uid']);
	    $member=$this->obj->DB_select_once("member","`uid`='".$uid."'","`password`,`salt`");
	    $pw=md5(md5($_POST['oldpwd']).$member['salt']);
	    if($pw!=$member['password']){
	        $this->errjson('原密码不正确，请重新输入',10);
	    }else if(mb_strlen($_POST['newpwd'])<6 || mb_strlen($_POST['newpwd'])>20){
	        $this->errjson('密码长度应在6-20位',11);
	    }else if($_POST['newpwd']!=$_POST['confirmpwd']){
	        $this->errjson('新密码和确认密码不一致',12);
	    }else if($this->config['sy_uc_type']=="uc_center" && $member['name_repeat']!="1"){
	        $this->uc_open();
	        $ucresult= uc_user_edit($member['username'], $_POST['oldpwd'], $_POST['newpwd'], "","1");
	        if($ucresult == -1){
	            $this->errjson('旧密码不正确，请重新输入',13);
	        }
	    }else{
	        $salt = substr(uniqid(rand()), -6);
	        $pass = md5(md5($_POST['newpwd']).$salt);
	        $this->obj->DB_update_all("member","`password`='".$pass."',`salt`='".$salt."'","`uid`='".$uid."'");
	        $this->member_log("修改密码",$uid,2,8);
	        $this->errjson('密码修改成功，请重新登录',0);
	    }
	}
	function getserver_action(){
	    $uid = intval($_POST['uid']);
	    $jobid=$_POST['id'];
	    $serverid=intval($_POST['serverid']);
	    if($serverid==1){
	        $data['price']=$this->config['job_auto'];
	        $data['servername']='自动刷新';
	        $job = $this->obj->DB_select_once("company_job","`id`='".$jobid."' and `uid`='".$uid."'","`name`,`autotime`");
	        if($job['autotime']>1){
	            $info['tgdate']=date("Y-m-d",$job['autotime']);
	        }else{
	            $info['tgdate']='暂未推广';
	        }
	    }elseif ($serverid==2){
	        $data['price']=$this->config['integral_job_top'];
	        $data['servername']='职位置顶';
	        $job = $this->obj->DB_select_once("company_job","`id`='".$jobid."' and `uid`='".$uid."'","`name`,`xsdate`");
	        if($job['xsdate']>1){
	            $info['tgdate']=date("Y-m-d",$job['xsdate']);
	        }else{
	            $info['tgdate']='暂未推广';
	        }
	    }elseif ($serverid==3){
	        $data['price']=$this->config['com_recjob'];
	        $data['servername']='职位推荐';
	        $job = $this->obj->DB_select_once("company_job","`id`='".$jobid."' and `uid`='".$uid."'","`name`,`rec_time`");
	        if($job['rec_time']>1){
	            $info['tgdate']=date("Y-m-d",$job['rec_time']);
	        }else{
	            $info['tgdate']='暂未推广';
	        }
	    }elseif ($serverid==4){
	        $data['price']=$this->config['com_urgent'];
	        $data['servername']='紧急招聘';
	        $job = $this->obj->DB_select_once("company_job","`id`='".$jobid."' and `uid`='".$uid."'","`name`,`urgent_time`");
	        if($job['urgent_time']>1){
	            $info['tgdate']=date("Y-m-d",$job['urgent_time']);
	        }else{
	            $info['tgdate']='暂未推广';
	        }
	    }elseif ($serverid==7){
	        $data['price']=$this->config['integral_down_resume'];
	        $data['servername']='下载简历';
	    }elseif ($serverid==8){
	        $data['price']=$this->config['integral_job'];
	        $data['servername']='发布职位';
	    }elseif ($serverid==9){
	        $data['price']=$this->config['integral_partjob'];
	        $data['servername']='发布兼职';
	    }
	    $info['name']=$job['name'];
	    $data['info']=$info;
	    $data['webtel']=$this->config['sy_freewebtel'];
	    $data['error'] = 0;
	    echo json_encode($data);die;
	}
	function setServer_action(){
	    $getdata=$this->getOpenid($_POST['code']);
	    if($getdata['openid']){
	        $uid = intval($_POST['uid']);
	        $member=$this->obj->DB_select_once("member","`uid` = '".$uid."'","`uid`,`usertype`,`username`,`did`");
	        $data['uid']=$member['uid'];
	        $data['usertype']=$member['usertype'];
	        $data['username']=$member['username'];
	        $data['did']=$member['did'];
	        $data['paytype']='wxpay';
	        $compayM=$this->MODEL('compay');
	        
	        if($_POST['serverid']==1){
	            $data['jobautoids']=$_POST['id'];
	            $data['rdays']=$_POST['rdays'];
	            $return = $compayM->buyAutoJob($data);
	            $msg="购买职位自动刷新";
	        }elseif($_POST['serverid']==2){
	            $data['zdjobid']=$_POST['id'];
	            $data['xsdays']=$_POST['rdays'];
	            $return = $compayM->buyZdJob($data);
	            $msg="购买职位置顶";
	            
	        }elseif($_POST['serverid']==3){
	            $data['recjobid']=$_POST['id'];
	            $data['recdays']=$_POST['rdays'];
	            $return = $compayM->buyRecJob($data);
	            $msg="购买职位推荐";
	            
	        }elseif($_POST['serverid']==4){
	            $data['ujobid']=$_POST['id'];
	            $data['udays']=$_POST['rdays'];
	            $return = $compayM->buyUrgentJob($data);
	            $msg="购买紧急招聘";
	        }elseif($_POST['serverid']==7){
	            $data['eid']=$_POST['id'];
	            $return = $compayM->buyDownresume($data);
	            $msg="购买简历下载";
	        }elseif($_POST['serverid']==8){
	            $data['issuejob']=1;
	            $return = $compayM->buyIssueJob($data);
	            $msg="购买职位发布";
	        }elseif($_POST['serverid']==9){
	            $data['issuepart']=1;
	            $return = $compayM->buyIssuePart($data);
	            $msg="购买兼职发布";
	        }
	        if($return['order']['order_id'] && $return['order']['id']){
	            $dingdan = $return['order']['order_id'];
	            $price = $return['order']['order_price'];
	            $id = $return['order']['id'];
	            $this->member_log($msg.",订单ID".$dingdan, $member['uid'], $member['usertype'],88);
	            require_once(LIB_PATH.'wxOrder.function.php');
	            $data = wxXcxOrder(array('body'=>'充值','id'=>$dingdan,'url'=>$this->config['sy_weburl'],'total_fee'=>$price,'openid'=>$getdata['openid']));
	            echo $data;die;
	        }else{
	            $data['errmsg']=$return['error'];
	            echo json_encode($data);die;
	        }
	    }else{
	        if ($getdata['errcode']){
	            $data['errmsg']='出错了，请重试';
	        }else{
	            $data['errmsg']='参数错误';
	        }
	    }
	}
	function payclass_action(){
	    include(PLUS_PATH."integralclass.cache.php");
	    $data['class_index'] = $integralclass_index?$integralclass_index:array();
	    $data['class_name'] = $integralclass_name?$integralclass_name:array();
	    $data['class_discount'] = $integralclass_discount?$integralclass_discount:array();
	    $class_price=array();
	    if($integralclass_index){
	        foreach ($integralclass_index as $v){
	            $discount=100;
	            if($integralclass_discount[$v]>0){
	                $discount = $integralclass_discount[$v];
	            }
	            $class_price[$v]=round($integralclass_name[$v]/$this->config['integral_proportion']*$discount/100,2);
	        }
	        $data['first'] = $integralclass_index[0];
	        $data['firstprice'] = $class_price[$integralclass_index[0]];
			$data['firstjf'] = $integralclass_name[$integralclass_index[0]];
	    }
	    $data['class_price'] = $class_price;
	    $config=array(
	        'name'   =>$this->config['integral_pricename'],
	        'unit'   =>$this->config['integral_priceunit'],
	        'proportion'  =>$this->config['integral_proportion'],
	        'min_recharge'=>$this->config['integral_min_recharge']
	    );
	    $data['config'] = $config;
	    echo json_encode($data);die;
	}
}
?>