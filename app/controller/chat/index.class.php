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

class index_controller extends common{
    function goChat_action()
    {
        
        if ($this -> config['sy_chat_open'] == 1){
            
            if ($this->usertype && in_array($this->usertype, array(1,2,3))){
                
                $chatM  =  $this->MODEL('chat');
                $chat   =  $chatM->userinfo(array('uid'=>$this->uid,'usertype'=>$this->usertype));
                $token  =  $chatM->chatToken($this->uid);
                
                $chat['yuntoken']  =  $token;
                
                $chat['usefullist']   = $chatM->getChatUsefulList(array('uid'=>$this->uid,'usertype'=>$this->usertype,'type'=>0), array('utype'=>'user'));

                $chat['zdconlist']   = $chatM->getChatZdConList(array('uid'=>$this->uid,'usertype'=>$this->usertype,'orderby'=>'sort,desc'), array('utype'=>'user'));

                echo json_encode($chat);
            }
        }
    }
    //开始聊天，先发个信息作为开始
    function beginChat_action()
    {
        if ($this->uid && $this->usertype){
            
            $data   =  array(
                'toid'       =>  $this->uid,
                'fromid'     =>  (int)$_POST['id'],
                'tusertype'  =>  $this->usertype,
                'fusertype'  =>  $_POST['usertype'],
                'jobid'      =>  $_POST['jobid'],
                'jobtype'    =>  $_POST['jobtype'],
                'timestamp'  =>  $_POST['timestamp']
            );
            
            $chatM   =  $this->MODEL('chat');
            
            $return  =  $chatM -> beginChat($data);
            
            echo json_encode($return);
        }
    }
    // 切换聊天对象，获取数据
    function pageData_action(){

        if ($this->uid && $this->usertype){
            
            $chatM  =  $this -> MODEL('chat');
            
            $return =  array();
            $return['adminChat'] = '';
            if ($_POST['id'] && $_POST['type'] && $this->uid && $this->usertype){
                
                if (substr($_POST['id'], 0, 1) == 'a'){
                    // 聊天对象是管理员
                    $uid = str_replace('a', '', $_POST['id']);
                    $return['adminChat'] = 1;
                }else{
                    $uid = intval($_POST['id']);
                }
                
                $chat  =  $chatM->userinfo(array('uid'=>$uid,'usertype'=>intval($_POST['type']),'nowid'=>$this->uid,'nowtype'=>$this->usertype));
                
                $return['receive'] = $chat['mine'];
                
                $br  =  $chatM->getBeginid(array('fromid'=>$uid,'toid'=>$this->uid,'fusertype'=>$chat['mine']['usertype'],'tusertype'=>$this->usertype));
                
                if (!empty($br['beginid'])){
                    $chatM->upChatLog(array('status'=>1),array('beginid'=>$br['beginid'],'to'=>$this->uid,'tusertype'=>$this->usertype,'status'=>2));
                }
            }
            

            $chatmem      =  $chatM -> getMember(array('uid'=>$this->uid,'usertype'=>$this->usertype),array('field'=>'`greeting_switch`,`zd_switch`'));

            $return['greeting_switch'] = $chatmem['greeting_switch'];

            $return['zd_switch'] = $chatmem['zd_switch'];
            //企业自动回复约束会员等级
            $is_zdrat = 0;
            if($this->usertype==2 && !empty($this->config['sy_chat_zdrat'])){

                $statisM = $this->MODEL('statis');
                $statis = $statisM->getInfo($this->uid,array('usertype'=>2));

                $sy_chat_zdrat_arr = explode(',',$this->config['sy_chat_zdrat']);
                if(in_array($statis['rating'],$sy_chat_zdrat_arr)){
                    $is_zdrat = 1;
                }
            }

            // 招呼
            $type = $this->usertype == 1 ? 4 : 3;
            
            $greeting = $chatM->getUsefulSet(array('type'=>$type, 'orderby'=>'sort'));

            $greeting_self = $chatM->getChatUseful(array('uid'=>$this->uid,'usertype'=>$this->usertype,'type'=>1,'orderby'=>'id,desc'));
            $greeting_id = $greeting_self['id']?$greeting_self['id']:'';
            if(!empty($greeting_self)){
                $greeting['content'] = $greeting_self['content'];

            }
            $return['config'] = array(
                'sy_chat_exphone'   =>  $this->config['sy_chat_exphone'],
                'sy_spview_web'     =>  $this->config['sy_spview_web'],
            );
            $return['greeting_id'] = $greeting_id;
            $return['greeting'] = $greeting['content'];
            $return['is_zdrat'] = $is_zdrat;
            $return['errcode'] = 1;
        }else{
            $return['errcode'] = 2;
        }

        echo json_encode($return);exit();
    }
    // 获取单个聊天记录
    function getSingleChatOnlineData_action(){

        if ($this->uid && $this->usertype){
            if($_POST['totype']==9){
                $_POST['toid'] = str_replace('a','',$_POST['toid']);
            }
            $data   =  array(
                'uid'       =>  $this->uid,
                'usertype'  =>  $this->usertype,
                'toid'      =>  $_POST['toid'],
                'totype'    =>  $_POST['totype'],
            );

            $chatM   =  $this->MODEL('chat');
            
            $return  =  $chatM -> getSingleChatOnlineData($data);

            //与对方最新的职位消息，用于企业pc端发送职位地址
            $lastjob_id = '';
            if($this->usertype==2){
                
                $chatlogjobwhere = array(
                    'beginid'=> $return['beginid'],
                    'msgtype'=> 'job',
                    'sendTime'=>array(">=",strtotime('-1 week')*1000),
                    'orderby'=>'id,desc'
                );

                $chatjobmsg  =  $chatM -> getChatLog($chatlogjobwhere);
                
                if(!empty($chatjobmsg)){

                    if (strpos($chatjobmsg['content'],'jobid=')!==false){
                            
                        $lastjob_id = str_replace('jobid=', '', $chatjobmsg['content']);

                    }
                }
            }
            
            $return['lastjob_id'] = $lastjob_id;
            
            echo json_encode($return);die;
        }
    }
    // 按列表获取聊天数据
    function getAllChatOnlineData_action(){

        if ($this->uid && $this->usertype){

            $data   =  array(
                'uid'       =>  $this->uid,
                'usertype'  =>  $this->usertype,
            );

            $chatM   =  $this->MODEL('chat');
            
            $return  =  $chatM -> getAllChatOnlineData($data);
            
            $myself   =  $chatM->userinfo(array('uid'=>$this->uid,'usertype'=>$this->usertype,'history'=>1));
            
            //与对方最新的职位消息，用于企业pc端发送职位地址
            $lastjob_arr = array();

            if($this->usertype==2){
                $comM = $this->MODEL('company');

                $company = $comM->getInfo($this->uid);

                if($company['mappic']){
                    $company['mappic'] = checkpic($company['mappic']);
                }
                $chatlogjobwhere = array(
                    'beginid'=> array('in',"'".@implode("','",$return['beginid'])."'"),
                    'msgtype'=> 'job',
                    'sendTime'=>array(">=",strtotime('-1 week')*1000),
                    'orderby'=>'id,asc'
                );

                $chatjobmsg  =  $chatM -> getChatLogList($chatlogjobwhere);
                
                foreach ($chatjobmsg as $jmk => $jmv) {

                    if (strpos($jmv['content'],'jobid=')!==false){
                            
                        $jobid = str_replace('jobid=', '', $jmv['content']);

                        if($jmv['fusertype']==1){
                            $lastjob_arr[$jmv['from']] = $jobid;
                        }else if($jmv['tusertype']==1){
                            $lastjob_arr[$jmv['to']] = $jobid;
                        }
                        
                    }
                }

                $return['mine']['joblist'] = $this->getJobLinkAddress($this->uid,$return['mine']['joblist']);
                $return['company'] = $company;
            }
            
            $return['lastjob_arr'] = $lastjob_arr;
            
            $return['rows'] = $myself['history'];
            
            echo json_encode($return);die;
        }
    }
    function getJobLinkAddress($uid='',$joblist=array()){

        if(!empty($joblist)){

            $jobM = $this->MODEL('job');
            $comM = $this->MODEL('company');

            $company = $comM->getInfo($uid);

            $linkIdArr = array();
            $jobids = array();
            foreach ($joblist as $jk => $jv) {
                if($jv['id'] && !in_array($jv['id'],$jobids)){
                    $jobids[] = $jv['id'];
                }
                if($jv['link_id'] && !in_array($jv['link_id'],$linkIdArr)){
                    $linkIdArr[] = $jv['link_id'];
                }
            }

            $joblink    =   $jobM -> getComJobLinkList(array(
                'id' => array('in', pylode(',', $linkIdArr)),
                'jobid'=>array('in', pylode(',', $jobids), 'OR') // 老业务逻辑会存在job_id，固此条件保留
            ),array('field'=>'*'));

            foreach ($joblist as $k => $v) {
                $joblist[$k]['job_address']     =  $company['address'];
                $joblist[$k]['job_mappic']      =  checkpic($company['mappic']);
                $joblist[$k]['job_x']           =  $company['x'];
                $joblist[$k]['job_y']           =  $company['y'];
                foreach($joblink as $jlkey => $jlval){
                    if($v['link_id']==$jlval['id'] || $v['id']==$jlval['jobid']){
                        if($jlval['x'] && $jlval['y']){
                            $joblist[$k]['job_address']     =  $jlval['link_address'];
                            $joblist[$k]['job_mappic']      =  checkpic($jlval['mappic']);
                            $joblist[$k]['job_x']           =  $jlval['x'];
                            $joblist[$k]['job_y']           =  $jlval['y'];
                        }
                    }
                }
            }
        }
        return $joblist;
    }
    // 静态图
    function staticMap_action(){
        
        $data['lng']        =   $_POST['lng'];
        $data['lat']        =   $_POST['lat'];
        $data['copyright']  =   1;
        $data['width']      =   320;
        $data['height']     =   130;
        $data['zoom']       =   14;
        $data['refer']      =   $this->config['sy_weburl'];

        $return = curlMappic($data);

        $res = array();

        if($return['error']==0){

            $res['url']=checkpic($return['url']);
            $res['error'] = 1;
        }else{
            $res['error'] = -1;
            $res['msg'] = $return['msg'];
        }
        echo json_encode($res);die;
    
    }
    //个人用户切换聊天对象时，异步查出对方所有职位
    function getAjaxJobs_action(){

        $toid      =  intval($_POST['toid']);
        $totype    =  intval($_POST['totype']);

        if($this->uid && $this->usertype && $toid){
            
            $return = array();

            if($totype==2){

                $jobM  =  $this->MODEL('job');
                $jobs  =  $jobM->getList(array('uid'=>$toid,'state'=>1,'r_status'=>1,'status'=>0,'limit'=>50),array('chat'=>1,'field'=>'`id`,`uid`,`name`,`com_name`,`provinceid`,`cityid`,`minsalary`,`maxsalary`,`exp`,`edu`'));

                if (!empty($jobs['list'])){
                    
                    $joblist = array();
                    foreach ($jobs['list'] as $k=>$v){
                       
                       $joblist[$v['id']] = $v;
                       $joblist[$v['id']]['weburl'] = Url('job',array('c'=>'comapply','id'=>$v['id']));
                    }
                    $return['joblist']  =  $joblist;
                }

            }elseif($totype==3){

                $ltjobM  =  $this->MODEL('lietoujob');
                $jobs    =  $ltjobM->getList(array('uid'=>$toid,'status'=>1,'zp_status'=>0,'r_status'=>1));
           
                foreach ($jobs as $k=>$v){
                   
                    $joblist[$v['id']]['id']            =  $v['id'];
                    $joblist[$v['id']]['name']          =  $v['job_name'];
                    $joblist[$v['id']]['com_name']      =  $v['com_name'];
                    $joblist[$v['id']]['job_salary']    =  $v['salary'];
                    $joblist[$v['id']]['citystr']       =  isset($v['citystr']) ? $v['citystr'] : '';
                    $joblist[$v['id']]['job_exp']       =  $v['exp_n'];
                    $joblist[$v['id']]['job_edu']       =  $v['edu_n'];
                }

                $return['joblist']  =  $joblist;

            }
            
            echo json_encode($return);die;
        }
    }
    //添加聊天发送记录
    function chatLog_action()
    {
        $uid     =  intval($_POST['to']);
        $chatM   =  $this -> MODEL('chat');
        //条件聊天判断时否有和个人聊天的权利
        $tcarr = array(
            'uid'      => $this->uid,
            'spid'     => $this->spid,
            'username' => $this->username,
            'usertype' => $this->usertype,
            'chatid'   => $uid
        );
        $comtcM  =  $this->MODEL('comtc');
        $res     =  $comtcM->chatRight($tcarr);
        
        if ($res['error'] == 0){
            $arr     =  array(
                'toid'       =>  $_POST['to'],
                'tusertype'  =>  $_POST['totype'],
                'fromid'     =>  $this->uid,
                'fusertype'  =>  $this->usertype,
                'content'    =>  $_POST['content'],
                'timestamp'  =>  $_POST['timestamp'],
                'msgtype'    =>  $_POST['msgtype'],
                'nowid'      =>  $_POST['nowid'],
                'nowtype'    =>  $_POST['nowtype'],
                'source'     =>  1
            );
            
            $return  =  $chatM -> chatLog($arr);
        }else{
            $return  =  $res;
        }
        echo json_encode($return);die;
    }
    /**
     * 聊天记录分页
     */
    function getChatPage_action()
    {
        if ($this->uid && $this->usertype){
            
            $arr    =  array(
                'toid'       =>  $_POST['id'],
                'tusertype'  =>  $_POST['totype'],
                'fromid'     =>  $this->uid,
                'fusertype'  =>  $this->usertype,
                'page'       =>  $_POST['page'],
                'lastid'     =>  $_POST['lastid']
            );
            
            $chatM   =  $this -> MODEL('chat');
            
            $return  =  $chatM -> getChatPage($arr);
            
            echo  json_encode($return);die;
        }
    }
    /**
     * 判断简历是否被下载
     */
    function getdown_action()
    {

        $arr = array(
            'eid'       =>  $_POST['eid'],
            'toid'      =>  $_POST['toid'],
            'nowtype'   =>  $_POST['nowtype'],
            'fromid'    =>  $this->uid,
            'fusertype' =>  $this->usertype,
            'zid'       =>  $_POST['zid']
        );
        if ($this->spid){
            $arr['spid']    =   $this->spid;
        }
        
        $chatM  =  $this->MODEL('chat');
        
        $return  =  $chatM -> comToChat($arr);
        
        echo json_encode($return);
    }
    /**
     * 发送图片信息
     */
    function uploadImage_action()
    {
        if ($this->uid && $this->usertype){
            
            $UploadM  =  $this -> MODEL('upload');
            
            $upArr  =  array(
                'file' =>  $_FILES['file'],
                'dir'  =>  'chat'
            );
            $result  =  $UploadM->newUpload($upArr);
            
            if (!empty($result['msg'])){
                $return  =  array(
                    'code'  =>  1,
                    'msg'   =>  $result['msg'],
                    'data'  =>  array()
                );
            }else{
                $pic  =  $result['picurl'];
                
                $result['picurl']  =  checkpic($pic);
                
                $return = array(
                    'code' => 0,
                    'msg' => '',
                    'data' => array('url'=>$result['picurl'])
                );
            }
        }else{
            
            $return['msg']       =  '请先登录';
            $return['code']      =  1;
        }
        echo json_encode($return);
    }
    //条件聊天时，判断个人是否有简历，并判断是否申请过该企业职位/已被企业邀请面试
    function isResume_action(){
        
        $arr  =  array(
            'uid'       =>  $this->uid,
            'usertype'  =>  $this->usertype,
            'comid'     =>  $_POST['id'],
            'jobtype'   =>  $_POST['jobtype'],
            'jobid'     =>  $_POST['jobid'],
            'nowtype'   =>  $_POST['nowtype'],
            'zid'       =>  $_POST['zid']
        );
        
        $chatM  =  $this->MODEL('chat');
        
        $return  =  $chatM -> userToChat($arr);
        
        echo json_encode($return);die;
    }
    // 收到消息即设为已读
    function setMsgStatus_action()
    {
        
        $chatM   =  $this -> MODEL('chat');
        
        $arr     =  array(
            'toid'       =>  $this->uid,
            'fromid'     =>  $_POST['id'],
            'tusertype'  =>  $this->usertype,
            'fusertype'  =>  $_POST['type'],
            'nowid'      =>  $_POST['nowid'],
            'nowtype'    =>  $_POST['nowtype']
        );
        
        $return  =  $chatM->setMsg($arr);
        
        echo json_encode($return);die;
    }
    // 设置语音聊天记录
    function setVoiceStatus_action()
    {
        
        $chatM  =  $this -> MODEL('chat');
        
        $chatid =   intval($_POST['chatid']);
        
        $arr     =  array(
            'id'            =>  $chatid,
            'fromid'        =>  intval($_POST['id']),
            'fusertype'     =>  $_POST['type'],
            'toid'          =>  $this->uid,
            'tusertype'     =>  $this->usertype,
            'nowid'         =>  $this->uid,
            'nowtype'       =>  $this->usertype,
        );
        $return = $chatM -> setVoiceStatus($arr);

        echo json_encode($return);die;
    }
    // 不感兴趣
    function delChatLog_action()
    {
        $return  =  array();
        
        if ($this->uid && $this->usertype){
            
            $arr = array(
                'toid'      => $_POST['to'],
                'tusertype' => $_POST['type'],
                'fromid'    => $this->uid,
                'fusertype' => $this->usertype
            );
            
            $chatM  =  $this->MODEL('chat');
            $chatM->delChatLog($arr);
            
        }else{
            
            $return  =  array('errcode'=>8,'errmsg'=>'请先登录');
        }
        echo json_encode($return);die;
    }
    // 由简历/职位详情页进入,发预定消息
    function prepare_action()
    {
        $pArr   =  array(
            'uid'       =>  $this->uid,
            'usertype'  =>  $this->usertype,
            'toid'      =>  intval($_POST['toid']),
            'totype'    =>  intval($_POST['totype'])
        );
        
        $chatM  =  $this->MODEL('chat');
        
        $list   =  $chatM -> getPrepare($pArr);
        
        echo json_encode($list);die;
    }
    // 查询邀请面试情况
    function inviteCheck_action(){
        
        $jobM  =  $this->MODEL('job');
        
        $where  =  array('uid'=>intval($_POST['id']));
        
        if (!empty($_POST['jid'])){
            
            $where['jobid']  =  intval($_POST['jid']);
        }
        $where['isdel'] = 9;
        $num      =  $jobM->getYqmsNum($where);
        
        $resumeM  =  $this->MODEL('resume');
        
        $resume   =  $resumeM->getResumeInfo(array('uid'=>intval($_POST['id'])),array('field'=>'`name`'));
        
        if ($num > 0){
            $data['msg']  =  '您已邀请其面试，前往查看邀请记录？';
        }else{
            $data['msg']  =  '';
        }
        $data['name']  =  $resume['name'];
        
        echo json_encode($data);die;
    }
    /**
     * 用户不在线，收到消息，提醒用户
     */
    function unSend_action()
    {
        $chatM   =  $this -> MODEL('chat');
        
        $arr     =  array(
            'fromid'    =>  $this->uid,
            'fusertype' =>  $this->usertype,
            'toid'      =>  $_POST['toid'],
            'tusertype' =>  $_POST['totype'],
            'nowtype'   =>  $_POST['nowtype'],
			'port'		=>	'1',
            'chatid'    =>  $_POST['chatid'],
            'source'    =>  1
        );
        
        $return  =  $chatM->setUnSend($arr);

        echo json_encode($return);die;
    }
    /**
     * 获取聊天token
     */
    function getToken_action()
    {
        if ($this->uid && $this->usertype && in_array($this->usertype, array(1,2,3))){
            // 已登录用户
            $chatM  =  $this->MODEL('chat');
            $chat   =  $chatM->userinfo(array('uid'=>$this->uid,'usertype'=>$this->usertype));
            $token  =  $chatM->chatToken($this->uid);
            $chat['yuntoken']  =  $token;
            
        }else{
            // 未登录用户
            $ct    =  (trim($_POST['chattype']));
            $csM   =  $this->MODEL('chatcs');
            $chat  =  $csM->getUnloginToken($ct);
        }
        
        echo json_encode($chat);
    }
    /**
     * 宣讲会聊天记录分页
     */
    function getXjhChatPage_action()
    {
        $arr    =  array(
            'xid'      =>  $_POST['xjhid'],
            'orderby'    =>  '`id`,DESC',
        );
        $page               =   $_POST['page'];
        $limit              =   10;
        if($page){//分页
            $pagenav        =   ($page-1)*$limit;
            $arr['limit']   =   array($pagenav,$limit);
        }else{
            $arr['limit']   =   $limit;
        }
        
        //筛除被禁言用户
        $buids              =   array();
        $chatM              =   $this -> MODEL('chat');
        $blacklist          =   $chatM ->getXjhchatBlackList(array('xjhid'=>(int)$_POST['xjhid']));
        
        foreach ($blacklist as $bk => $bv) {
            
            if($bv['uid'] && !in_array($bv['uid'],$buids)){
                
                $buids[]    =   $bv['uid'];
            }
        }
        
        if(!empty($buids)){
            $arr['fuid']    =   array('notin',pylode(',',$buids));
        }
        //筛除被禁言用户end
        
        $xjhM               =  $this->MODEL('xjhlive');
        $xjh                =  $xjhM->getInfo(array('id'=>(int)$_POST['xjhid']));
        $return             =  $chatM->getChatList($arr,array('fdata'=>true,'sensitive'=>true,'lastid'=>$_POST['lastid']));
        
        $list   =   array();
        foreach($return as $k=>$v){
            $list[$k]['id']         =   $v['fuid'];
            $list[$k]['username']   =   !empty($v['user'])?$v['user']['username']:$v['username'];
            $list[$k]['avatar']     =   !empty($v['user'])?$v['user']['avatar']:$v['avatar'];
            $list[$k]['usertype']   =   $v['fusertype'];
            $list[$k]['content']    =   $v['content'];
            $list[$k]['msgtype']    =   $v['msgtype'];
            $list[$k]['sendTime']   =   $v['sendTime'];
            $list[$k]['xjhid']      =   'xjh_'.$_POST['xid'];
            $list[$k]['xid']        =   $_POST['xid'];
            $list[$k]['chatid']     =   $v['id'];
            $list[$k]['jy']         =   0;
            if (isset($v['job'])){
                $list[$k]['job']    =   $v['job'];
            }
        }
        foreach($list as $k=>$v){
            if($v['id']!=$xjh['uid']){
                $list[$k]['black']	=	1;
            }
        }
        
        $chatnum   =  $chatM->getChatNum(array('xid'=>$arr['xid']));
        
        $ismore    =  ceil($chatnum/$limit) > $page ? true : false;
        
        $result  =  array(
            'list'    =>  $list,
            'ismore'  =>  $ismore
        );
        
        echo  json_encode($result);die;
    }
    
    /**
     * 宣讲会发言，关键词检测
     */
    function checkdata_action(){
        
        $xjhM   =  	$this -> MODEL('xjhlive');
        
        $xjh	=	$xjhM -> getInfo(array('id'=>$_POST['xjhid']),array('field'=>'`id`,`nochat`,`vistor_nochat`'));
        
        if(!empty($xjh)){
            if ($xjh['nochat'] == 1){
                echo json_encode(array('errcode'=>8,'msg'=>'本场直播禁止发言'));die;
            }
            // 获取发言人身份
            if ($this->usertype && in_array($this->usertype, array(1,2,3))){
                $uid = $this->uid;
                $usertype = $this->usertype;
            }elseif (isset($_COOKIE['chat_u'])){
                // 游客
                if ($xjh['vistor_nochat'] == 1){
                    echo json_encode(array('errcode'=>8,'msg'=>'本场直播游客禁止发言，您登录后即可发言'));die;
                }
                $uid = $_COOKIE['chat_u'];
                $usertype = 0;
            }
            
            $chatM     =  $this->MODEL('chat');
            $blacknum  =  $chatM->getXjhchatBlackNum(array('uid'=>$uid,'xid'=>$xjh['id']));
            
            if ($blacknum > 0){
                
                echo json_encode(array('errcode'=>8,'msg'=>'您已被禁言'));die;
                
            }else{
                
                if($_POST['content']){
                    $content	=	'';
                    include(LIB_PATH.'sensitive.class.php');
                    $instance = Sensitive::getInstance();
                    
                    if(file_exists(DATA_PATH.'sensitive/xjhword.txt')){
                        $instance->addSensitiveWords(DATA_PATH.'sensitive/xjhword.txt');
                        $content    =   $instance->execFilter($_POST['content']);
                    }else{
                        $content 	=	$_POST['content'];
                    }
                    
                    // 保存聊天记录
                    $arr     =  array(
                        'fuid'       =>  $uid,
                        'fusertype'  =>  $usertype,
                        'ip'         =>  fun_ip_get(),
                        'content'    =>  $_POST['content'],
                        'timestamp'  =>  $_POST['timestamp'],
                        'msgtype'    =>  'char',
                        'xjhid'      =>  $_POST['xjhid']
                    );
                    $chatM->xhjChat($arr);
                    
                    $return['msgcontent']	=	$content;
                    
                    if ($this->usertype == 1){
                        // 个人用户，发送时不完全展示姓名
                        $resumeM  =  $this->MODEL('resume');
                        $resume   =  $resumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'`name`,`sex`'));
                        
                        $return['nickname']  =  $resume['sex'] == 1 ? mb_substr($resume['name'], 0, 1, 'utf-8').'先生' : mb_substr($resume['name'], 0, 1, 'utf-8').'女士';
                    }
                }else{
                    
                    $return['msgcontent']	=	'';
                }
            }
            
            echo json_encode($return);die;
        }
    }
    // 视频面试操作记录聊天记录
    function spviewChat_action(){
        
        if ($_POST['to']){
            $arr     =  array(
                'content'    =>  $_POST['content'],
                'timestamp'  =>  $_POST['timestamp'],
                'msgtype'    =>  'spview'
            );
            if ($_POST['content'] == 'refused'){
                // 拒绝视频，发送方改为对方
                $arr['toid']       =  $this->uid;
                $arr['tusertype']  =  $this->usertype;
                $arr['fromid']     =  $_POST['to'];
                $arr['fusertype']  =  $_POST['totype'];
                $arr['nowid']      =  $_POST['to'];
                $arr['nowtype']    =  $_POST['totype'];
            }elseif ($_POST['content'] == 'closesp'){
                // 取消视频，发送方改为自己
                $arr['toid']       =  $_POST['to'];
                $arr['tusertype']  =  $_POST['totype'];
                $arr['fromid']     =  $this->uid;
                $arr['fusertype']  =  $this->usertype;
                $arr['nowid']      =  $this->uid;
                $arr['nowtype']    =  $this->usertype;
            }
            
            $chatM   =  $this -> MODEL('chat');
            
            $return  =  $chatM -> chatLog($arr);
            
            echo json_encode($return);
        }
    }
    // 会员中心查询聊天对象列表
    function getmh_action(){
        
        if ($this->uid  && $this->usertype){
            
            $page     =  intval($_POST['page']);
            
            $chatM    =  $this->MODEL('chat');
            
            $history  =  $chatM->getHistory($this->uid, $this->usertype, $page);

            $data   =  array(
                'uid'       =>  $this->uid,
                'usertype'  =>  $this->usertype,
                'page'      =>  $page
            );

            $return  =  $chatM -> getAllChatOnlineData($data);
            
        }
        if(!empty($history)){
            echo json_encode(array('error'=>0,'history'=>$history,'chatstorage'=>$return['toall']));
        }else{
            echo json_encode(array('error'=>1));
        }
    }
    function showUnread_action(){
        if ($this->uid  && $this->usertype){
            
            $page     =  intval($_POST['page']);
            
            $chatM    =  $this->MODEL('chat');
            
            $history  =  $chatM->getHistory($this->uid, $this->usertype, 0,'all',array('allunread'=>1));

            $data   =  array(
                'uid'       =>  $this->uid,
                'usertype'  =>  $this->usertype,
                'allunread' =>  1,
            );

            $return  =  $chatM -> getAllChatOnlineData($data);
            
        }
       
        echo json_encode(array('error'=>0,'history'=>$history,'chatstorage'=>$return['toall']));
        
    }
    // 换电话、微信状态查询
    function checkCanAsk_action(){

        $chatM  =   $this->MODEL('chat');

        $data   =   array(
            'uid'       =>  $this->uid,
            'usertype'  =>  $this->usertype,
            'toid'      =>  intval($_POST['toid']),
            'totype'    =>  intval($_POST['totype']),
            'ask'       =>  $_POST['ask'],
            'askvalue'  =>  $_POST['askvalue'],
        );

        $return =   $chatM->checkCanAsk($data);
        echo json_encode($return);
    }
    // 同意换电话、微信
    function confirmAsk_action(){

        $data['uid']        = $this->uid;
        $data['usertype']   = $this->usertype;
        $data['toid']       = $_POST['toid'];
        $data['totype']     = $_POST['totype'];
        $data['ask']        = $_POST['ask'];
        $data['askvalue']   = $_POST['askvalue'];
        $data['chatid']     = $_POST['chatid'];
        $chatM  =   $this->MODEL('chat');
        $return =   $chatM->confirmAsk($data);

        echo json_encode($return);
    }
    // 拒绝换电话、微信
    function refuseAsk_action(){
        $data['uid']        = $this->uid;
        $data['usertype']   = $this->usertype;
        $data['toid']       = $_POST['toid'];
        $data['totype']     = $_POST['totype'];
        $data['ask']        = $_POST['ask'];
        $data['chatid']     = $_POST['chatid'];
        $chatM  =   $this->MODEL('chat');
        $return =   $chatM->refuseAsk($data);

        echo json_encode($return);
    }
    // 查询单个职位
    function getJob_action(){
        
        $jobM = $this->MODEL('job');
        
        $job  = $jobM->getInfo(array('id'=>$_POST['jobid']),array('field'=>'id,name,com_name,job1,job1_son,job_post,provinceid,cityid,three_cityid,exp,edu,minsalary,maxsalary'));
        
        $job['weburl'] = Url('job',array('c'=>'comapply','id'=>$job['id']));
        
        echo json_encode($job);die;
    }

    // 删除常用语
    function delUseful_action(){
        
        $chatM  =  $this->MODEL('chat');
        $res    =  $chatM->delChatUseful(array('id'=>$_POST['id'],'uid'=>$this->uid,'usertype'=>$this->usertype));
        
        echo json_encode($res);
    }
    // 常用语保存
    function usefulSave_action(){
        
        $data = array(
            'uid'      => $this->uid,
            'usertype' => $this->usertype,
            'content'  => trim($_POST['content']),
            'type'     => trim($_POST['type']),
        );
        
        $chatM  =  $this->MODEL('chat');
        
        if (empty($_POST['id'])){
            $res  =  $chatM->addChatUseful($data);
        }else{
            $res  =  $chatM->upChatUseful(array('id'=>$_POST['id']), $data);
        }
        
        echo json_encode($res);
    }
    function zdconSave_action(){

        $data = array(
            'uid'       =>  $this->uid,
            'usertype'  =>  $this->usertype,
            'content'   =>  trim($_POST['content']),
            'type'      =>  trim($_POST['type']),
            'keyword'   =>  trim($_POST['zdconkeyword']),
            'sort'      =>  trim($_POST['sort']),
            'time'      =>  time()
        );
        
        $chatM  =  $this->MODEL('chat');
        
        if (empty($_POST['id'])){
            $res  =  $chatM->addChatZdcon($data);
        }else{

            $res  =  $chatM->upChatZdcon(array('id'=>$_POST['id']), $data);
        }
        
        echo json_encode($res);
    }
    function delZdcon_action(){
        
        $chatM  =  $this->MODEL('chat');
        $res    =  $chatM->delChatZdcon(array('id'=>$_POST['id'],'uid'=>$this->uid,'usertype'=>$this->usertype));
        
        echo json_encode($res);
    }
    // 消息撤回
    function recall_action()
    {
        $return  =  array();
        
        if ($this->uid && $this->usertype){
            
            $where = array(
                'id'        => $_POST['chatid'],
                'to'        => $_POST['toid'],
                'tusertype' => $_POST['type'],
                'from'      => $this->uid,
                'fusertype' => $this->usertype
            );
            
            $chatM  =  $this->MODEL('chat');
            $chatM->upChatLog(array('recall'=>2), $where);
            
        }else{
            
            $return  =  array('errcode'=>8,'msg'=>'请先登录');
        }
        echo json_encode($return);die;
    }

     // 问候开关
    function greetingSwitch_action(){
        
        $return  =  array();
        
        if ($this->uid && $this->usertype){
            
            $chatM = $this->MODEL('chat');

            $data = array(
                'uid'      => $this->uid,
                'usertype' => $this->usertype
            );
            
            $chatM  =  $this->MODEL('chat');

            $return = $chatM->greetingSwitch($data);
            
        }else{
            
            $return  =  array('errcode'=>8,'msg'=>'请先登录');
        }
        
        echo json_encode($return);die;
    }

    function zdSwitch_action(){
        
        $return  =  array();
        
        if ($this->uid && $this->usertype){
            
            $chatM = $this->MODEL('chat');

            $data = array(
                'uid'      => $this->uid,
                'usertype' => $this->usertype
            );
            
            $chatM  =  $this->MODEL('chat');

            $return = $chatM->zdSwitch($data);
            
        }else{
            
            $return  =  array('errcode'=>8,'msg'=>'请先登录');
        }
        
        echo json_encode($return);die;
    }

    // 获取简历信息
    function resumeExpect_action()
    {
        $ruid = $_POST['uid'];
        $eid = $_POST['eid'];

        $resumeM = $this->MODEL('resume');

        // 如果传入的是ruid, 则先获取eid
        if (!empty($ruid)) {
            $rwhere	=	array('uid' => $ruid, 'r_status' => 1);
            $def_job    =   $resumeM->getResumeInfo($rwhere, array('field' => '`def_job`'));
            if (empty($def_job)) {
                exit(json_encode(array('error' => 1, 'msg' => '没有找到该人才')));
            } else if ($def_job['def_job'] < 1) {
                exit(json_encode(array('error' => 1, 'msg' => '还没有创建简历')));
            } else if ($def_job['def_job']) {
                $eid = $def_job['def_job'];
            }
        }

        $resume_expect      =   $resumeM->getInfoByEid(array('eid' => $eid, 'uid' => $this->uid, 'usertype' => $this->usertype, 'spid' => $this->spid));

        echo json_encode(array('data' => $resume_expect, 'error' => 0));
    }
}
?>