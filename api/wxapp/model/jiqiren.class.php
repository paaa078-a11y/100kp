<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2023 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class jiqiren_controller extends wxapp_controller
{
    private $salaryArr = array(
        array('value'=>'1' ,'label'=>'2000以上','min'=>2000),
        array('value'=>'2' ,'label'=>'4000以上','min'=>4000),
        array('value'=>'3' ,'label'=>'6000以上','min'=>6000),
        array('value'=>'4' ,'label'=>'8000以上','min'=>8000),
        array('value'=>'5' ,'label'=>'10000以上','min'=>10000),
    );
    function index_action(){
        
        include(CONFIG_PATH.'db.data.php');
        unset($arr_data['sex'][3]);
        
        include(DATA_PATH.'api/wxapp/tplapp.cache.php');
        include(DATA_PATH.'plus/job.cache.php');

        $jobclass = array();
        if (!empty($tplapp['career_counselor_job_classid'])) {
            $jobclass = explode(',', $tplapp['career_counselor_job_classid']);
        } else {
            /* @var $job_two */
            include(PLUS_PATH.'jobfs.cache.php');
            if ($job_two) {
                if (count($job_two) > 5) {
                    $jobTwoKeyArr = array_rand($job_two, 5);
                    foreach ($jobTwoKeyArr as $jtVal) {
                        $jobclass[] = $job_two[$jtVal];
                    }
                } else {
                    $jobclass = $job_two;
                }
            }
        }

        $jobclassArr = array();
        foreach ($jobclass as $v){
            $jobclassArr[] = array(
                'id'      => $v,
                'name'    => $job_name[$v],
                'checked' => false
            );
        }
        
        $data = array(
            'jobclassArr'  => $jobclassArr,
            'salaryArr'    => $this->salaryArr,
            'sexArr'       => $arr_data['sex'],
            'name'         => $tplapp['career_counselor_name'],
            'logo'         => !empty($tplapp['career_counselor_avatar']) ? checkpic($tplapp['career_counselor_avatar']) : getAdminPath('images/qzzs_avatar.png'),
            'userlogo'     => !empty($tplapp['career_counselor_user_avatar']) ? checkpic($tplapp['career_counselor_user_avatar']) : getAdminPath('images/qzzs_user_avatar.png'),
            'gw'           => array(
                'name'  => $tplapp['career_counselor_name'],
                'greet' => $tplapp['career_counselor_greet']
            ),
            'share'        => array(
                'title' => $tplapp['career_counselor_share_title'],
                'image' => checkpic($tplapp['career_counselor_share_img'])
            )
        );
        
        if (!empty($_POST['uid']) && !empty($_POST['token'])){
            
            $this->yzToken($_POST['uid'], $_POST['token']);
            
            $resumeM = $this->MODEL('resume');
            $expect	 = $resumeM->getExpectByUid($this->member['uid'], array('needCache'=>true));
            
            $resume  = $resumeM->getResumeInfo(array('uid'=>$this->member['uid']));
            
            $data['sex']      = $resume['sex_n'];
            $data['birthday'] = $resume['birthday'];
            $data['jobclass'] = $expect['job_classname'];
            $data['salary']   = $expect['salary'];
            $data['eid']      = $expect['id'];
            
            $data['info'] = array(
                'jobclassid' => $expect['job_classid'],
                'sex'        => $resume['sex'],
                'birthday'   => $resume['birthday'],
                'minsalary'  => $expect['minsalary'],
                'maxsalary'  => $expect['maxsalary']
            );
        }
        
        
        $this->render_json(0, 'ok', $data);
    }
    function jiqirenJob_action(){
        
        $where['state']		=	1;
        $where['status']	=	0;
        $where['r_status']	=	1;
        
        $job1 = $jobson = $jobpost = array();
        $jobclass = explode(',', $_POST['jobclassid']);
        include(PLUS_PATH.'jobparent.cache.php');
        foreach ($jobclass as $v){
            //获取当前职位级别，根据级别来处理，获得其他级别的数据
            $lev  =  getLev($v,$job_parent);
            if ($lev == 1){
                $job1[]   =  $v;
            }elseif ($lev == 2){
                $jobson[] =  $v;
                $job1[]   =  $job_parent[$v];
            }elseif ($lev == 3){
                $jobpost  =  $v;
                $jobson[] =  $job_parent[$v];
                $job1[]   =  $job_parent[$job_parent[$v]];
            }
        }
        
        if (!empty($jobpost)){
            $where['job_post']  =  array('in', pylode(',', $jobpost));
        }elseif (!empty($jobson)){
            $where['job1_son']  =  array('in', pylode(',', $jobson));
        }elseif (!empty($job1)){
            $where['job1']      =  array('in', pylode(',', $job1));
        }
        if (!empty($_POST['minsalary'])){
            // 到简历页面修改信息后返回的
            $salary = array(
                'minsalary' => $_POST['minsalary']
            );
        }else{
            
            foreach ($this->salaryArr as $k=>$v){
                if ($v['value'] == $_POST['salary']){
                    $salary = array(
                        'minsalary' => $v['min']
                    );
                }
            }
        }
        if ($salary['minsalary'] > 0){
            
            $where['PHPYUNBTWSTART_A']  =  '';
            $where['minsalary']         =  array('>=', $salary['minsalary']);
            $where['maxsalary']         =  array('>=', $salary['minsalary'], 'OR');
            $where['PHPYUNBTWEND_A']	=	'';
        }
        $where['orderby'] =	'lastupdate,desc';
        $where['limit']	  =	10;
        
        $jfield   =  '`id`,`uid`,`name`,`provinceid`,`cityid`,`three_cityid`,`exp`,`edu`,`welfare`,`minsalary`,`maxsalary`,`lastupdate`,`com_name`,`com_logo`';
        
        $jobM    = $this->MODEL('job');
        $jobrows = $jobM->getList($where,array('field'=>$jfield,'utype'=>'wxapp', 'member'=>'user'));
        $rows	 = $jobrows['list'];
        
        $this->render_json(0, 'ok', array('list'=>$rows));
    }
    
    function creatResume_action(){
        
        if (!empty($_POST['uid']) && !empty($_POST['token'])){
            $this->yzToken($_POST['uid'], $_POST['token']);
        }else{
            if (!empty($_POST['phone'])){
                $userinfoM  =  $this->MODEL('userinfo');
                $row = $userinfoM->getInfo(array('moblie'=>$_POST['phone']),array('field'=>'uid,usertype'));
                
                if (empty($row['uid'])){
                    // 未注册
                    $openid  =  $_POST['openid'];
                    if (!empty($_POST['unionid']) && $_POST['unionid'] != 'undefinded'){
                        $unionid  =  $_POST['unionid'];
                    }
                    
                    $ckData['from']         =  'loginfo';
                    $ckData['moblie']       =  $_POST['phone'];
                    $ckData['provider']     =  $_POST['provider'];
                    $ckData['source']       =  28;
                    $ckData['openid']       =  $openid;
                    $ckData['unionid']      =  isset($unionid) ? $unionid : '';
                    $ckData['usertype']     =  1;
                    
                    $res  =  $userinfoM->fastReg($ckData,'wxxcx',$_POST['provider']);
                    
                    $back = $userinfoM->activUser($res['uid'], 1, array(), true);
                    
                }elseif (empty($row['usertype'])){
                    // 未选择身份
                    $back = $userinfoM->activUser($row['uid'], 1, array(), true);
                }
                // 用户登录信息
                if (isset($back)){
                    $data['user'] = $back;
                    $this->member['uid'] = $back['uid'];
                }else{
                    $this->render_json(-1, '账号注册失败');
                }
            }else{
                $this->render_json(-1, '缺少手机号');
            }
        }
        
        $salary    = array();
        foreach ($this->salaryArr as $k=>$v){
            if ($v['value'] == $_POST['salary']){
                $salary = array(
                    'minsalary' => $v['min'],
                    'maxsalary' => $v['max']
                );
            }
        }
        
        $rData	    =	array(
            'sex'		=>	$_POST['sex'],
            'birthday'  =>	$_POST['birthday']
        );
        
        include PLUS_PATH."user.cache.php";
        
        $eData      =   array(
            'lastupdate'	 =>  time(),
            'height_status'  =>	 0,
            'uid'		     =>	 $this->member['uid'],
            'ctime'			 =>	 time(),
            'name'			 =>	 $_POST['name'],
            'job_classid'	 =>	 $_POST['jobclassid'],
            'minsalary'		 =>	 $salary['minsalary'],
            'maxsalary'		 =>	 $salary['maxsalary'],
            'state'		 	 =>	 0,
            'r_status'		 =>	 1,
            'sex'            =>  $_POST['sex'],
            'birthday'       =>	 $_POST['birthday'],
            'type' 			 =>  $userdata['user_type'][0],
            'report' 		 =>  $userdata['user_report'][0],
            'jobstatus' 	 =>  $userdata['user_jobstatus'][0],
            'source'         =>  28
        );
        $addArr     =   array(
            'uid'       =>  $this->member['uid'],
            'rData'     =>  $rData,
            'eData'     =>  $eData,
            'workData'  =>  $workData,
            'eduData'   =>  $eduData,
            'proData'   =>  $proData,
            'utype'     =>  'user',
            'source'	=>	28
        );
        
        $resumeM        =	$this->MODEL('resume');
        $return         =   $resumeM->addInfo($addArr);
        
        $data['error']	=	$return['errcode'] == 9 ? 1 : 2;
        $data['msg']	=	$return['msg'];
        $data['eid']	=	isset($return['id']) ? $return['id'] : '';
        
        $this->render_json($data['error'],$data['msg'],$data);
    }
    /**
     * 修改求职意向
     */
    function upExpect_action(){
        
        $this->yzToken($_POST['uid'], $_POST['token']);
        
        $eid      =  (int)$_POST['eid'];
        $error    =  2; 
        
        if($eid){
            
            $expectDate  =  array(
                'job_classid' => $_POST['classid'],
                'lastupdate'  => time()
            );
            if(resumeTimeState($this->config['user_revise_state']) == '0'){
                $expectDate['state'] = 0 ;
            }
            $resumeM  =  $this->MODEL('resume');
            $return   =  $resumeM->upInfo(array('id'=>$eid,'uid'=>$this->member['uid']), array('eData'=>$expectDate,'utype'=>'user','source'=>$_POST['source'], 'sxlog' => 1, 'port' => $this->port));
            
            $error	  =	$return['errcode']  == 9?1:2;
        }
        
        $this->render_json($error,$return['msg']);
    }
    /**
     * 查询是否有简历
     */
    function getResume_action(){
        
        $this->yzToken($_POST['uid'], $_POST['token']);
        
        $resumeM = $this->MODEL('resume');
        $expect	 = $resumeM->getExpectByUid($this->member['uid'], array('needCache'=>true));
        
        if (empty($expect)){
            // 没有简历
            $data = array();
        }else{
            // 有简历
            $resume  = $resumeM->getResumeInfo(array('uid'=>$this->member['uid']));
            
            $data['sex']      = $resume['sex_n'];
            $data['birthday'] = $resume['birthday'];
            $data['jobclass'] = $expect['job_classname'];
            $data['salary']   = $expect['salary'];
            $data['eid']      = $expect['id'];
            
            $data['info'] = array(
                'jobclassid' => $expect['job_classid'],
                'sex'        => $resume['sex'],
                'birthday'   => $resume['birthday'],
                'minsalary'  => $expect['minsalary'],
                'maxsalary'  => $expect['maxsalary']
            );
        }
        
        $this->render_json(0, 'ok', $data);
    }
}
?>