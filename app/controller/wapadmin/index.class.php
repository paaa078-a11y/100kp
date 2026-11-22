<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class index_controller extends adminCommon
{

	function index_action(){ 
		if($_POST['login_sub']){
		    $this -> wapadmin_get_user_login($_POST['username'], $_POST['password'], 'index.php');
		}else{

			if($_SESSION['wuid']){
				//验证登录信息
                $this -> admin_get_user_shell($_SESSION['wuid'], $_SESSION['wshell']);
                
                if (!isset($this->adminShell)) {
            
                    $this->yuntpl(array('wapadmin/login'));
                    
                    die;
                }
                if (!empty($_POST)){
                    $jobM		=	$this->MODEL('job');
                    $resumeM	=	$this->MODEL('resume');
                    $companyM	=	$this->MODEL('company');
                    $companyorderM = $this->MODEL('companyorder');
                    $userinfoM	=	$this->MODEL('userinfo');
                    $comorderM	=	$this->MODEL('companyorder');

                    /*************登录以后调用数据Start***************/
                    $today		=	strtotime('today');

                    $month      =   mktime(0,0,0,date('m'),1,date('Y'));

                     // 今日新增会员数
                    $where['reg_date']	        =	array(">=",$today);
                    $list['member_num']			=	$userinfoM->getMemberNum($where);
                    //企业会员(今日)
                    $tocomwh['usertype']		=	'2';
                    $tocomwh['pid']		        =	0;
                    $tocomwh['reg_date']		=	array('>=',$today);
                    $list['com_num_now']	    =	$userinfoM -> getMemberNum($tocomwh);
                    //职位数(今日)
                    $list['job_num_now']	    =	$jobM -> getJobNum(array('sdate'=>array('>=',$today)));
                    //个人会员(今日)
                    $touserwh['usertype']		=	'1';
                    $touserwh['reg_date']		=	array('>=',$today);
                    $list['user_num_now']	    =	$userinfoM -> getMemberNum($touserwh);

                    $list['resume_num_now']	    =	$resumeM -> getExpectNum(array('ctime'=>array('>=',$today)));//简历数(今日)

                    // 今日收益
                    $owhere['order_state']	    =	2;
                    $owhere['order_time']	    =	array('>=',$today);
                    $owhere['type']	            =	array('<>', 6);
                    $moneyTotal	                =	$comorderM->getList($owhere,array('field'=>'sum(order_price) as `total`'));
                    $list['money_total']        =   $moneyTotal[0]['total'] ? $moneyTotal[0]['total'] : 0;

                    //  本月收益
                    $mwhere['order_state']	    =	2;
                    $mwhere['order_time']	    =	array('>=', $month);
                    $mwhere['type']	            =	array('<>', 6);
                    $monPrice	                =	$comorderM->getList($mwhere,array('field'=>'sum(order_price) as `total`'));
                    $list['month_price']        =   $monPrice[0]['total'] ? $monPrice[0]['total'] : 0;
                    if (floatval($monPrice[0]['total']) > 0){

                        $crmM                   =   $this->MODEL('crm');
                        $mtime                  =   date('Y-m');

                        $list['jebList']        =   $crmM->getJebList($mtime);
                    }



                    $list['resume_num_dsh']		=	$resumeM -> getExpectNum(array('state'=>'0'));//待审核个人简历
                    $list['com_num_dsh']		=	$companyM -> getCompanyNum(array('r_status'=>'0','clue' => 1));//待审核企业会员
                    $list['job_num_dsh']		=	$jobM -> getJobNum(array('state'=>'0'));//待审核职位
                    $list['comcert_num_dsh']	=	$companyM -> getCertNum(array('type'=>'3','status'=>'0'));//待审核企业认证
                    $list['comorder_num_dsp']	=	$companyorderM->getCompanyOrderNum(array(
                        'crm_uid'   =>  array('<>', ''),
                        'is_crm'    =>  1,
                        'status'    =>  2,
                        'type'      =>  1,
                        'order_state' => 3
                    ));//待审批订单

                    $adminM    =  $this -> MODEL('admin');

                    $power     =  $adminM -> getAdminUser(array('uid'=>$_SESSION['wuid']),array('wap_crm'=>1));

                    $list['name'] = $power['name'];
                    $list['group_name'] = $power['group_name'];
                    header('content-type:application/json; charset=utf-8');
                    echo json_encode($list);die;

                    /*************登录以后调用数据END***************/
                }

                $adminM    =  $this -> MODEL('admin');
                $power     =  $adminM -> getPower(array('id'=>$this->adminShell['m_id']));

                //判断是否业务员 并且无管理权限
                if((in_array('216',$power['power']) && in_array('220',$power['power']) && !in_array('226',$power['power'])) && $this->adminShell['index_lookstatistc']==1){
                        $this->yuntpl(array('wapadmin/crm_index'));
                }else{
                    $this->yunset('index_lookstatistc',$this->adminShell['index_lookstatistc']);
                    $this->yuntpl(array('wapadmin/index'));
                }
			}else{
				$this->yuntpl(array('wapadmin/login'));
			}
		}
	}

	function getStatis_action()
    {
        header('content-type:application/json; charset=utf-8');
        if ($this->adminShell['index_lookstatistc'] != 2) {
            echo json_encode(array());die;
        }
        $jobM		=	$this->MODEL('job');
        $userinfoM	=	$this->MODEL('userinfo');
        $resumeM	=	$this->MODEL('resume');
        $companyM	=	$this->MODEL('company');
        $downresumeM=	$this->MODEL('downresume');
        $chatM	    =	$this->MODEL('chat');

        $today      =   date('Y-m-d');
        if (empty($_POST['adtime'])) {

            $date   =   array('>', strtotime($today));
        } else {
            if ($_POST['adtime'] == 1) { // 昨日单独处理

                $date[] =   array('>=', strtotime($today .' -' . (int)$_POST['adtime'] . 'day'));
                $date[] =   array('<', strtotime($today));
            } else {

                $date   =   array('>=', strtotime('-' . (int)$_POST['adtime'] . 'day'));
            }
        }

        $comregwhere = array('usertype' => 2, 'pid' => 0, 'reg_date' => $date);
        $comloginwhere = array('usertype' => 2, 'login_date' => $date);
        $userregwhere = array('usertype' => 1, 'reg_date' => $date);
        $userloginwhere = array('usertype' => 1, 'login_date' => $date);
        $adresumewhere = array('ctime' => $date);
        $upresumewhere = array('lastupdate' => $date);
        $adjobwhere = array('sdate' => $date);
        $upjobwhere = array('lastupdate' => $date);

        $sqjobwhere = array('datetime' => $date);
        $yqmswhere = array('datetime' => $date);
        $downresumewhere = array('downtime' => $date);
        $chatwhere = array('sendTime' => $date);

        $data['com_reg_num']    =   $userinfoM->getMemberNum($comregwhere);
        $data['com_num']        =   $companyM->getCompanyNum();
        $data['user_num']       =   $resumeM->getResumeNum();
        $data['com_log_num']    =   $userinfoM->getMemberNum($comloginwhere);
        $data['com_adjob_num']  =   $jobM->getJobNum($adjobwhere);
        $data['com_upjob_num']  =   $jobM->getJobNum($upjobwhere);
        $data['com_sqjob_num']  =   $jobM->getSqJobNum($sqjobwhere);
        $data['com_yqms_num']   =   $jobM->getYqmsNum($yqmswhere);
        $data['com_downresume_num'] =   $downresumeM->getDownNum($downresumewhere);
        $data['com_chat_num']   =   $chatM->getChatLogNum($chatwhere);
        $data['user_reg_num']   =   $userinfoM->getMemberNum($userregwhere);
        $data['user_log_num']   =   $userinfoM->getMemberNum($userloginwhere);
        $data['user_adres_num'] =   $resumeM->getExpectNum($adresumewhere);
        $data['user_upres_num'] =   $resumeM->getExpectNum($upresumewhere);

        echo json_encode($data);die;
    }

    function logout_action()
    {
        unset($_SESSION['authcode']);
        unset($_SESSION['wuid']);
        unset($_SESSION['wusername']);
        unset($_SESSION['wshell']);
        unset($_SESSION['md']);
        unset($_SESSION['tooken']);
        unset($_SESSION['xsstooken']);
        $this->layer_msg("您已成功退出！", 9, 0, "index.php");
    }

    public function tongji_action()
    {

        $this->yuntpl(array('wapadmin/index_tongji'));
	}
}
?>