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
class xjhlive_controller extends common{
	
    function index_action()
    {
        $this->yunset('backurl',Url('wap'));
        $this->yunset('headertitle','直播宣讲会');
        $this->seo('xjhlive');
        $this->yuntpl(array('wap/xjhlive'));
    }
    function show_action()
    {
        if($_GET['id']){
            $id		=	$_GET['id'];
            
            $xjhM	=	$this->MODEL('xjhlive');
            
            $info	=	$xjhM->getInfo(array('id'=>$id),array('defaultPic'=>true));
            
            if (empty($info)){
                $this->ACT_msg($_SERVER['HTTP_REFERER'], '宣讲会不存在！');
            }else if($info['status']==0){
                $this->ACT_msg($_SERVER['HTTP_REFERER'], '宣讲会审核中！');
            }else if($info['status']==2){
                $this->ACT_msg($_SERVER['HTTP_REFERER'], '宣讲会未通过审核！');
            }
            
            if($info['livestatus']==1 && $info['status']==1 && $info['stime']>time()){
                
                if(!$this->uid){
                    $this->yunset('substatus',1);
                } else {
                    $sub = $xjhM->getyyInfo(array('uid'=>$this->uid,'xid'=>$info['id']));
                    if($sub){
                        // 已预约
                        $substatus  =  2;
                    }else{
                        $substatus  =  1;
                    }
                    $this->yunset('substatus',$substatus);
                }
            }
            
            if($info['livestatus']==3 || $info['livestatus']==2){
                $this->yunset('hit',1);
            }
            
            $this->yunset('info',$info);
            
            if($this->uid){
                 $chatM  =  	$this->MODEL('chat');
                 
                 $blacknum	=	$chatM->getXjhchatBlackNum(array('uid'=>$this->uid,'xid'=>$id));
                 
                 if($blacknum>0){
                 
                    $this->yunset('black',1);
                 }
                 if (in_array($this->usertype, array(1, 2, 3))){
                     
                     $this->yunset('chatRight', 1);
                 }
            }
            
            if($this->usertype==1){//个人用户进入页面判断是否存在简历
                $resumeM	=	$this->MODEL('resume');
                $expect		=	$resumeM->getExpect(array('uid'=>$this->uid),array('field'=>'id'));
                $this->yunset('eid',$expect['id']);
            }
        }
        include CONFIG_PATH.'db.data.php';
        unset($arr_data['sex'][3]);
        $this->yunset('sexData', $arr_data['sex']);
        
        $this->yunset('headertitle','直播宣讲会');
        
        $data['xjhlive_title']  =   $info['name'];
        $data['xjhlive_desc']   =   $this -> GET_content_desc($info['body']);
        $this -> data       =   $data;
        
        $this->seo('xjhlive_show');
        $this->yuntpl(array('wap/xjhlive_show'));
    }
	
	/**
     * 扫码预约宣讲会
     */
    function xjhyyQrcode_action(){
        $WxM = $this->MODEL('weixin');
        $qrcode = $WxM->pubWxQrcode('xjh',$_GET['id']);
        if(isset($qrcode)){
            $imgStr = CurlGet($qrcode);
            header("Content-Type:image/png");
            echo $imgStr;
        }
    }
	
    function xjhSubcribe_action(){
        
        $uid	=	$this->uid;
        $usertype	=	$this->usertype;
        
        if($uid && $usertype==1){
            
            $xjhM  =  $this->MODEL('xjhlive');
            
            $yyinfo=    $xjhM->getyyInfo(array('uid'=>$uid,'xid'=>$_POST['xjhid']));
            
            if(!empty($yyinfo)){
                $data['error']  =  7;
            }else{
                $subData	=	array(
                    'uid'		=>  $uid,
                    'xid'		=>  $_POST['xjhid'],
                    'ctime'		=>  time(),
                );
                $nid	=	$xjhM->addyy($subData);
                
                if($nid){
                    
                    $return  =  $xjhM->addyyMsg($_POST['xjhid'],$uid,$nid);
                    
                    if (!empty($return['wxshow'])){
                        
                        $data['wxshow']  =  1;
                    }
                }
                
                $data['error']  =  9;
            }
            
        }else{
            
            $data['error']  =  8;
        }
        
        echo json_encode($data);die;
    }
    function playback_action()
    {
        $this->seo('xjhlive_playback');
        $this->yuntpl(array('wap/xjhlive_playback'));
    }
    /**
     * 观看次数
     */
    function GetHits_action()
    {
        $id = intval($_GET['id']);
        if (empty($id)) {
            echo 'document.write(0)';
            die();
        }
        
        $xjhM   =   $this->MODEL('xjhlive');
        $info	=	$xjhM->getInfo(array('id'=>$id),array('field'=>'livestatus,hits_base'));
        
        $xjhM->upXjh(array('id'=>$id),array('hits'=>array('+',1)));
        $hits   =   $xjhM->getInfo(array('id' => $id), array('field' => '`hits`'));
        
        $hits['hits'] = $hits['hits'] + $info['hits_base'];
        echo 'document.write('.$hits['hits'].')';
        die();
    }
    function getAjaxHits_action(){
        
        $id		=	intval($_POST['id']);
        $xjhM   =   $this->MODEL('xjhlive');
        $row    =   $xjhM->getInfo(array('id' => $id), array('field' => '`hits`,`onlinenum`,`livestatus`,`hits_base`'));
        
        $row['hits'] = $row['hits'] + $row['hits_base'];
        // 保存直播最高观看人数
        if ($_POST['num'] > $row['onlinenum']){
            $xjhM->upXjh(array('id'=>$id),array('onlinenum'=>$_POST['num']));
        }
        // 直播中、直播结束，存观看记录
        if($row['livestatus']==3 || $row['livestatus']==2){
            
            $log = array('xid' => $id);
            
            if ($this->uid && $this->usertype && in_array($this->usertype, array(1,2,3))){
                
                $log['uid'] = $this->uid;
                $log['usertype'] = $this->usertype;
                
            }elseif (isset($_COOKIE['chat_u'])){
                
                $log['uid'] = $_COOKIE['chat_u'];
                $log['usertype'] = 0;
            }
            $xjhM->setUserlog($log);
        }
        
        echo json_encode(array('hits'=>$row['hits']));exit();
    }
    /**
     * 查询直播状态、直播播放地址
     */
    function getXjhlive_action(){
        
        $id		=	$_POST['id'];
        
        $xjhM	=	$this->MODEL('xjhlive');
        
        $info	=	$xjhM->getInfo(array('id'=>$id), array('field'=>'`livestatus`,`playtime`,`stime`,`playurl`,`caster`'));
        
        echo json_encode($info);die;
    }
    // 参会企业列表
    function getcomlist_action(){
        
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
        
        $xjhM  =  $this->MODEL('xjhlive');
        $rows  =  $xjhM -> getXjhComList(array('xid'=>$_POST['xid'],'orderby'=>array('`status`,ASC','`ctime`,ASC')),array('utype'=>'wap','keyword'=>$keyword));
        // 获取正在讲解的企业
        if (!empty($rows)){
            foreach ($rows as $k=>$v){
                if ($v['status'] == 1){
                    $explain = $v;
                }
            }
        }
        $res = array(
            'comlist' => !empty($rows) ? $rows : array(),
            'explain' => isset($explain) ? $explain: array(),
            'notice' => $this->config['sy_xjh_notice']
        );
        
        echo json_encode($res);
    }
    // 更新赞数量
    function upzan_action(){
        
        $xjhM  =  $this->MODEL('xjhlive');
        $info	=	$xjhM->getInfo(array('id'=>$_POST['xid']), array('field'=>'`zan`'));
        if ($_POST['zan'] > $info['zan']){
            $rows  =  $xjhM->upXjh(array('id'=>$_POST['xid']),array('zan'=>$_POST['zan']));
        }
        
        echo json_encode(array('error'=>1,));
    }
}
?>