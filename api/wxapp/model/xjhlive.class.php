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
class xjhlive_controller extends wxapp_controller{
    
    function list_action()
    {
        $return = array();
		$xjhM  =  $this->MODEL('xjhlive');
        $where  = array(
            'state'      => array('<>',3),
            'statetime'  => array('<',time()),
            'isopen'     => 1
        );
        $xjhtab =   (int)$_POST['xjhtab'];
        $csort   =   false;
        if($xjhtab==1){
            $where['livestatus']    =   1;
            $csort   =   true;
        }else if($xjhtab==2){
            $where['livestatus']    =   2;
            $where['playback']      =   1;
        }else if($xjhtab==3){
            $where['livestatus']    =   3;
            $csort   =   true;
        }else{
            $csort   =   true;
        }

        if($_POST['keyword']){
			
			$where['name']	=	array('like',$_POST['keyword']);
		}
		// 处理分站查询条件
		if (!empty($_POST['did'])){
		    $where['did']  =  $_POST['did'];
		}
        $field  =   '`id`,`name`,`livestatus`,`stime`';
        if ($csort){
            // 条件排序，进行中在最上面，接着是未开始，最后是已结束
            $field  .=  ',CASE WHEN `livestatus`<>2  THEN `stime`';
            $field  .=  ' WHEN `livestatus`=2 THEN -1*`stime` END AS `xjh_px`';
            
            $where['orderby'] .=  ' CASE WHEN `livestatus`=3 THEN 0';
            $where['orderby'] .=  ' WHEN `livestatus`=1 THEN 1';
            $where['orderby'] .=  ' WHEN `livestatus`=2 THEN 2';
            $where['orderby'] .=  ' END,`xjh_px` ASC';
        }else{
            $where['orderby'] .=  '`stime`,DESC';
        }

        $page   =  $_POST['page'];
        $limit  =  $_POST['limit'] ? $_POST['limit'] : 10;
        if($page){
            
            $pagenav  =  ($page - 1) * $limit;
            $where['limit']  =  array($pagenav,$limit);
            
        }else{
            
            $where['limit']  =  $limit;
        }
        
        $list  =  $xjhM->getList($where,array('field'=>$field,'shortlen'=>28));
        
        $enum  =  0;
        $error =  0;
        $return['list']  =  $list;
        
        if (!empty($_POST['uid']) && !empty($_POST['token'])){
            
            $member   =  $this->yzToken($_POST['uid'], $_POST['token']);
            
            $resumeM  =  $this->MODEL('resume');
            $enum     =  $resumeM->getExpectNum(array('uid'=>$member['uid']));
        }
        
        $return['enum']  =  $enum;
        
        $this->render_json($error, 'ok', $return);
    }
    /**
     * 查询宣讲会数据
     */
    function xjhShow_action(){
        
        $time   =  time();
        $member =  array();
        $return =  array();
        
        if (!empty($_POST['uid']) && !empty($_POST['token'])){
            
            $member  =  $this->yzToken($_POST['uid'], $_POST['token']);
        }
        
        $xjhM  =  $this->MODEL('xjhlive');
        
        $xjh   =  $xjhM->getInfo(array('id'=>$_POST['xjhid'],'status'=>1),array('liveRecord'=>1,'defaultPic'=>true));
        
        if (!empty($xjh)){
            
            if($xjh['stime']>$time){
                $second         =   $xjh['stime']-$time;
                $xjh['day']     =   floor($second/(3600*24));
                $second         =   $second%(3600*24);
                $xjh['hour']    =   floor($second/3600);
                $second         =   $second%3600; 
                $xjh['minute']  =   floor($second/60);
                $xjh['second']  =   $second%60;
            }
            $xjhM->upXjh(array('id' => $xjh['id']),array('hits'=>array('+',1)));
            $xjh['hits'] = intval($xjh['hits'] + $xjh['hits_base'] + 1);
            
            $return['xjhInfo']  =  $xjh;
            
            
            if (!empty($member)){
                if($member['usertype']==1){//个人用户进入页面判断是否存在简历
                    $resumeM	=	$this->MODEL('resume');
                    $enum		=	$resumeM->getExpectNum(array('uid'=>$member['uid']));
                    $return['enum']  =  $enum;
                }
                // 聊天参数,已登录用户
                if ($this->config['sy_chat_open']==1){
                    $chatM  =  $this->MODEL('chat');
                    $chat   =  $chatM->userinfo(array('uid'=>$member['uid'],'usertype'=>$member['usertype']));
                    
                    $return['yuntoken']  =  $chatM->chatToken($member['uid']);
                    $return['mine']  =  $chat['mine'];
                }
            }else{
                // 聊天参数,未登录用户
                if ($this->config['sy_chat_open']==1){
                    $ct    =  'xjh';
                    $csM   =  $this->MODEL('chatcs');
                    $chat  =  $csM->getUnloginToken($ct);
                    $return['chat']  =  $chat['data'];
                    $return['mine']  =  $chat['mine'];
                }
            }
			
			if($xjh['livestatus']==3){
		      $return['sethit']   =	1;
			}elseif($xjh['livestatus']==2 && $xjh['status']==1){
			    $return['sethit']    =	1;
			}
			
			if($xjh['stime']>$time && $xjh['status']==1){
			    if (!empty($member)){
			        if($xjh['uid'] != $member['uid'] && $member['usertype'] == 1){
			            
			            $sub	=	$xjhM->getyyInfo(array('uid'=>$member['uid'],'xid'=>$xjh['id']));
			            
			            if($sub){
			                
			                $return['sub']  =  2;
			            }else{
			                
			                $return['sub']  =  1;
			            }
			        }
			    }else{
			        $return['sub']  =  1;
			    }
			}
		    // app用分享数据
		    if ($this->plat == 'app'){
		        $return['shareData']  =  array(
		            'url'       =>  Url('wap',array('c'=>'xjhlive','a'=>'show','id'=>$xjh['id'])),
		            'title'     =>  $xjh['name'],
		            'summary'   =>  mb_substr(strip_tags($xjh['body']), 0,30,'UTF8'),
		            'imageUrl'  =>  !empty($xjh['picarr'][0]) ? $xjh['picarr'][0] : checkpic($this->config['sy_logo'])
		        );
		    }
		    // 小程序用seo
		    if ($this->plat == 'mini'){
		        $seodata['xjhlive_title']  =   $xjh['name'];
		        $seodata['xjhlive_desc']   =   $this -> GET_content_desc($xjh['body']);
                $this->data     =   $seodata;
                $seo            =   $this->seo('xjhlive_show', '', '', '', false, true);
                $return['seo']  =   $seo;
		    }
			$return['liveStyle'] = isset($this->config['sy_xjhlive_ext']) ? $this->config['sy_xjhlive_ext'] : 1;
			$subtplM = $this->MODEL('subtpl');
			$tplInfo = $subtplM->getTpl(array('name' => 'xjh_start_tplid'));// 获取宣讲会开播订阅消息模板id
			$return['yy_tpl'] = $tplInfo['tpl_id'] ? $tplInfo['tpl_id'] : '';
            $this->render_json(0,'ok',$return);
        }else{
            
            $this->render_json(-1,'宣讲会异常');
        }
    }
    //直播招聘岗位列表
	function xjhJoblist_action(){

        if($_POST['xjhid']){

            $xid    =   $_POST['xjhid'];

            $xjhM   =   $this->MODEL('xjhlive');

            $info   =   $xjhM->getInfo(array('id'=>$xid));

            $cuid   =   array();

            $xcoms  =   $xjhM->getXjhComList(array('xid'=>$info['id']));
            foreach ($xcoms as $key => $value) {
                $cuid[]  =   $value['uid'];
            }
            
            $page           =   $_POST['page'];
            $maxpage        =   1;
            if(!empty($cuid)){
                $jobM       =   $this->MODEL('job');

                $jWhere['uid']          =   array('in', pylode(',', $cuid));
                $jWhere['r_status']     =   1;
                $jWhere['status']       =   0;
                $jWhere['state']        =   1;
                $jWhere['orderby'][]    =   'uid,desc';
                $jWhere['orderby'][]    =   'id,desc';
                $jData['field']         =   '`id`,`uid`,`name`,`com_name`,`exp`,`edu`,`number`,`minsalary`,`maxsalary`,`provinceid`,`cityid`';

                $num                    =   $jobM -> getJobNum($jWhere);

                $limit                  =   $_POST['limit'] ? intval($_POST['limit']) : 5;
                
                
                if($page){
                    
                    $pagenav            =  ($page - 1) * $limit;
                    $jWhere['limit']    =  array($pagenav,$limit);
                    
                }else{
                    
                    $jWhere['limit']    =  $limit;
                }
                $maxpage        =   intval(ceil($num/$limit));

                $joblist        =   $jobM->getList($jWhere, $jData);
            }
            $data['joblist']    =   !empty($joblist['list']) ? $joblist['list'] : array();
            $data['islast']     =   $maxpage==0 || $maxpage==$page ? 1 : 0;
            $this->render_json(-1,'',$data);
        }else{
            $this->render_json(-1,'参数异常');
        }
    }
    //预约直播
	function xjhSubcribe_action(){
        $xcxopenid = '';
        $uid = 0;
        $utype = 0;// 身份类型默认未知
        if ($_POST['uid'] && $_POST['token']) {
            $member = $this->yzToken($_POST['uid'], $_POST['token']);
            $uid = $_POST['uid'];
            $userM = $this->MODEL('userinfo');
            $memberinfo = $userM->getInfo(array('uid' => $member['uid']));
            $xcxopenid = $memberinfo['wxopenid'];
            $utype = $memberinfo['usertype'] ? $memberinfo['usertype'] : 0;
        }
        if (!$xcxopenid && $_POST['code']) {
            $getdata = $this->getOpenid($_POST['code'], $this->config['sy_xcxappid']);
            $xcxopenid = $getdata['openid'];
        }
		if($xcxopenid){
			$xjhM = $this->MODEL('xjhlive');
            $yyinfo = $xjhM->getyyInfo(array('xid' => $_POST['xjhid'], 'xcx_openid' => $xcxopenid));
            if(!empty($yyinfo)){
                $this->render_json(-1,'您已预约，请勿重复预约');
            }else{
    			$subData	=	array(
    				'uid' => $uid,
                    'xid' => $_POST['xjhid'],
                    'xcx_openid' => $xcxopenid,
                    'subscribe' => 1,
                    'utype' => $utype,
                    'ctime' => time(),
    			);
    			$nid = $xjhM->addyy($subData);
    			if($nid){
    			    $xjhM->addyyMsg($_POST['xjhid'], $uid, $nid);
    			}
    			$this->render_json(0,'ok');
            }

		}else{

            $this->render_json(-1,'数据异常请重试');
        }
	}
    /**
     * 观看次数。（6.3.1起，已废弃）
     */
    function setHits_action()
    {
        $id     =   intval($_POST['xjhid']);
        
        $xjhM   =   $this->MODEL('xjhlive');
             
        $xjhM	->	upXjh(array('id' => $id),array('hits'=>array('+',1)));
        
        // 获取发言人身份
        if (isset($this->member['uid'])){
            $row    =   $xjhM->getInfo(array('id' => $id), array('field' => '`livestatus`'));
            // 直播中、直播结束，存观看记录
            if($row['livestatus']==3 || $row['livestatus']==2){
                
                $log = array('xid' => $id);
                $log['uid'] = $this->member['uid'];
                $log['usertype'] = $this->member['usertype'];
                $xjhM->setUserlog($log);
            }
        }
        
        $this->render_json(0,'ok');
    }
    /**
     * ajax获取观看人数
     */
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
            // 获取发言人身份
            if (isset($this->member['uid'])){
                $log['uid'] = $this->member['uid'];
                $log['usertype'] = $this->member['usertype'];
            }elseif (isset($_POST['chat_u'])){
                // 游客
                $log['uid'] = $_POST['chat_u'];
                $log['usertype'] = 0;
            }
            $xjhM->setUserlog($log);
        }
        $this->render_json(0, 'ok', array('hits'=>$row['hits']));
    }
    /**
     * 宣讲会直播观看人数实时统计
     */
    function liveStatis_action(){
        
        $xjhM   =  $this->MODEL('xjhlive');
        
        $data   =  array(
            'xid'    =>  $_POST['xjhid'],
            'num'    =>  $_POST['num'],
            'ctime'  =>  time()
        );
        
        $xjhM->addLiveStatis($data);
        
        $this->render_json(0,'ok');
    }
    /**
     * 查询直播状态、直播播放地址
     */
    function getXjhlive_action(){
        
        $id		=	$_POST['id'];
        
        $xjhM	=	$this->MODEL('xjhlive');
        
        $info	=	$xjhM->getInfo(array('id'=>$id), array('field'=>'`livestatus`,`playtime`,`stime`,`playurl`,`caster`'));
        
        $this->render_json(0,'ok',$info);
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
            'explain' => isset($explain) ? $explain: array()
        );
        
        $this->render_json(0,'ok',$res);
    }
    // 更新赞数量
    function upzan_action(){
        
        $xjhM  =  $this->MODEL('xjhlive');
        $info	=	$xjhM->getInfo(array('id'=>$_POST['xid']), array('field'=>'`zan`'));
        if ($_POST['zan'] > $info['zan']){
            $rows  =  $xjhM->upXjh(array('id'=>$_POST['xid']),array('zan'=>$_POST['zan']));
        }
        
        $this->render_json(0,'ok');
    }
}
?>