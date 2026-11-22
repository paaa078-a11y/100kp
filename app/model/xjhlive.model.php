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
class xjhlive_model extends model{
    /**
     * @desc  发布/修改直播宣讲会
     * @param array $data: post-宣讲会相关字段 id-修改宣讲会Id
     * @return $return
     */
    function addXjh($data = array(), $filetp = 'base')
    {
        
        $post     =   $data['post'];      //  宣讲会数据相关字段
        $id       =   $data['id'];        //  更新宣讲会ID
        
        if($post){
            
            $picurlArr	=	array();
            // 处理封面图片
            if(is_array($post['pic'])){
                
                $picArr		=	$post['pic'];
                
                include_once('upload.model.php');
                $UploadM  =  new upload_model($this->db, $this->def);
                
                foreach($picArr as $pk=>$pv){
                    
                    if (!empty($pv)){
                        
                        $upArr   =  array(
                            'dir'      =>  'xjh',
                            $filetp	   =>  $pv
                        );
                        
                        $result  =  $UploadM -> newUpload($upArr);
                        
                        if (!empty($result['msg'])){
                            
                            $return['msg']      =  $result['msg'];
                            $return['errcode']  =  '8';
                            
                            return $return;
                        }else if(!empty($result['picurl'])){
                            $picurlArr[]  =  $result['picurl'];
                        }
                    }
                }
            }
            unset($post['pic']);
            
            if ($post['state'] == 1){
                
                $post['statetime']  =  time();
                
            }elseif ($post['state'] == 3){
                
                $post['statetime']  =  '';
            }
            
            if (!empty($id)) {
                
                $xjh  =  $this -> select_once('xjhlive', array('id'=>$id), '`stime`,`livestatus`,`caster`');
                
                if ($xjh['stime'] != $post['stime']){
                    // 修改了开始时间的，需要额外判断
                    if ($xjh['stime'] < time()){
                        
                        $return['msg']  =  '宣讲会直播已到开始时间，不支持修改';
                        
                    }else if ($xjh['livestatus'] == 3){
                        
                        $return['msg']  =  '宣讲会直播已开始，不支持修改';
                        
                    }else if ($xjh['livestatus'] == 2){
                        
                        $return['msg']  =  '宣讲会直播已结束，不支持修改';
                        
                    }
                }else if ($xjh['caster'] == 2){
                    
                    $return['msg']  =  '导播台已开启，不支持修改';
                }
                if (!empty($return['msg'])){
                    
                    $return['errcode']  =  8;
                    
                    return $return;
                }
                
                $this->update_once('xjhlive', $post, array('id' => $id));
                $nid	=	$id;
                $msg    =   '更新';
            } else {
                
                $nid    =   $this->insert_into('xjhlive', $post);
                $msg    =   '发布';
            }
            if ($nid) {
                
                if(!empty($picurlArr)){
                    //插入封面多图
                    foreach($picurlArr as $xpk=>$xpv){
                        
                        $picdata	=	array(
                            'xid' 	=>	$nid,
                            'picurl'=>	$xpv,
                            'ctime'	=>	time()
                        );
                        
                        $this->insert_into('xjhlive_pic', $picdata);
                    }
                    
                }
                // 同步处理获取直播播放地址
                $result  =  $this->xjhSync($nid);
                
                if (empty($result['msg'])){
                    
                    $return =   array(
                        'id'        =>  $nid,
                        'errcode'   =>  9,
                        'msg'       =>  $msg.'直播宣讲会成功'
                    );
                }else{
                    
                    $return  =  $result;
                    
                    if (empty($id)) {
                        // 平台发布失败，清除本地记录
                        $this->delete_all('xjhlive', array('id'=>$nid));
                        $this->delete_all('xjhlive_pic', array('xid'=>$nid), '');
                    }
                }
            }
            
        }else{
            
            $return   =   array(
                'errcode' =>  8,
                'msg'     =>  '数据错误'
            );
        }
        
        return $return;
    }
    /**
     * 后台添加参会企业
     */
    public function addXjhCom($data	=	array()){
        
        if (!empty($data['comid'])){
            
            $comid  =  explode(',', $data['comid']);
            $rows   =  $this->select_all('xjhlive_com',array('xid'=>$data['xid']));
            
            if (!empty($rows)){
                
                foreach ($rows as $v){
                    
                    $xc[]  =  $v['uid'];
                }
                
                foreach ($comid as $v){
                    if (!in_array($v, $xc)){
                        
                        $new[]  =  $v;
                    }
                }
            }else{
                
                $new  =  $comid;
            }
            
            if (!empty($new)){
                
                $time       =  time();
                $valueData  =  array();
                
                foreach ($new as $k => $v){
                    
                    $valueData[$k]['uid']    =   $v;
                    $valueData[$k]['xid']    =   $data['xid'];
                    $valueData[$k]['ctime']  =   $time;
                }
                $this->DB_insert_multi('xjhlive_com', $valueData);
                
                $return['errcode']  =  9;
                $return['msg']		=  '添加成功';
                
            }else{
                
                $return['errcode']  =  8;
                $return['msg']		=  '重复添加';
            }
        }else{
            
            $return['errcode']  =  8;
            $return['msg']		=  '请选择要添加的企业';
        }
        return $return;
    }
    function upXjh($where=array(),$data=array()){
        
        if(!empty($where) && !empty($data)){
            
            $xid	=	$this->update_once('xjhlive',$data,$where);
            
            return $xid;
        }
    }

    /**
     * 获取宣讲会列表
     * @param $whereData    查询条件
     * @param string[] $data    自定义处理数组
     * @return array|bool|false|string|void
     */
    function getList($whereData,$data=array('utype'=>'')){
        
        if(!empty($whereData)){
            
            $select		=	$data['field'] ? $data['field'] : '*';
            
            $List		=	$this -> select_all('xjhlive',$whereData,$select);
            
            if(!empty($List)){
                
                foreach($List as $k=>$v){
                    
                    $xjhids[]	=	$v['id'];
                }
                $time	=	time();
                
                $pwhere	=	array(
                    'xid'		=>	array('in',pylode(',',$xjhids)),
                    'orderby' 	=>	'id,ASC'
                );
                $xjhpic   =   $this->select_all("xjhlive_pic",$pwhere);
                $picarr   =   array();
                if(!empty($xjhpic)){
                    
                    foreach($xjhpic as $pk=>$pv){
                        $picarr[$pv["xid"]][] = $pv["picurl"];
                    }
                }
                
                foreach($List as $k=>$v){
                    if (!empty($v['stime'])){
                        $List[$k]['stime_n'] 	= date('Y-m-d H:i',$v['stime']);
                    }
                    if (!empty($v['ctime'])){
                        $List[$k]['ctime_n'] 	= date('Y-m-d H:i',$v['ctime']);
                    }
                    if (!empty($v['statetime'])){
                        $List[$k]['statetime_n']= date('Y-m-d H:i',$v['statetime']);
                    }
                    $List[$k]['picarr']		=	array();
                    
                    if(!empty($picarr[$v['id']])){
                        
                        foreach($picarr[$v['id']] as $pk=>$pv){
                            
                            $picarr[$v['id']][$pk] = checkpic($pv);
                            
                        }
                        
                        $List[$k]['picarr']		=	$picarr[$v['id']];
                        $List[$k]['pic']		=	$picarr[$v['id']][0] ? $picarr[$v['id']][0] : '';
                    }
                    
                    if(isset($v['status']) && $v['status']==0){
                        $List[$k]['status_n'] 	= '未审核';
                    }else if($v['status']==1){
                        $List[$k]['status_n'] 	= '已审核';
                    }else if($v['status']==2){
                        $List[$k]['status_n'] 	= '未通过';
                    }
                    // 正在直播改为手动触发，但正在直播需在直播时间内，不然依然算未开始
                    if (isset($v['livestatus']) && $v['livestatus']==3 && !empty($v['playtime'])){
                        
                        if ($v['stime'] > $time || ($v['stime'] + (3600 * $v['playtime'])) < $time){
                            
                            $v['livestatus']  =  1;
                        }
                    }
                    if($v['livestatus']==1){
                        
                        if (!empty($v['playtime'])){
                            
                            $List[$k]['livestatus_n'] 	= '未开始';
                            
                        }else{
                            $List[$k]['livestatus_n'] 	= '未开始';
                        }
                        $List[$k]['livestatus'] = 1;
                    }else if($v['livestatus']==2){
                        
                        $List[$k]['livestatus_n'] 	= '已结束';
                        $List[$k]['livestatus'] = 2;
                    }else if($v['livestatus']==3){
                        $List[$k]['livestatus_n']  =  '直播中';
                        $List[$k]['livestatus'] = 3;
                    }
                }
                if ($data['utype'] == 'admin'){
                    
                    $List = $this->adminList($List);
                }
                
                if ($data['shortlen'] > 0){
                    foreach($List as $k=>$v){
                        if(mb_strlen($v['name'])>$data['shortlen']){
                            
                            $List[$k]['name']	    =	mb_substr($v['name'],0,$data['shortlen'],'utf-8')."...";
                            $List[$k]['name_t']	=	$v['name'];
                        }
                    }
                }
                
                if ($data['num']){
                    
                    $subList	=	$this->select_all('xjhlive_yy',array('xid'=>array('in',pylode(',',$xjhids)),'groupby'=>'xid'),'`xid`,count(*) AS `num`');
                    
                    if(!empty($subList)){
                        foreach($List as $key=>$val) {
                            
                            $subnum  =  0;
                            
                            foreach($subList as $v) {
                                
                                if($val['id']==$v['xid']){
                                    $subnum  =  $v['num'];
                                }
                            }
                            $List[$key]['subnum']	=	$subnum;
                        }
                    }
                }
            }
            return	$List;
        }
    }
    private function adminList($List){
        
        $uids  =  array();
        foreach ($List as $v){
            
            $xids[]		=	$v['id'];
            $uids[]  =  $v['uid'];
        }
        $com  =  $this->select_all('company',array('uid'=>array('in',pylode(',', array_unique($uids)))),'`uid`,`name`');
        
        $all	=	$this->select_all('xjhlive_com',array('xid'=>array('in', pylode(',', $xids)),'groupby'=>'xid'),'xid,count(*) as num');
        
        foreach($List as $k=>$v){
            
            foreach ($com as $val){
                
                if ($v['uid'] == $val['uid']){
                    
                    $List[$k]['comname']  =  $val['name'];
                }
            }
            
            if(!empty($all)){
                foreach($all as $val){
                    if($v['id'] == $val['xid']){
                        $List[$k]['comnum']	=	$val['num'];	//	 参会企业
                    }
                }
            }
        }
        return $List;
    }

    /**
     * 获取宣讲会详情
     * @param $whereData    查询条件
     * @param int[] $data   liveRecord    获取点播数据    livestatus    获取直播状态
     * @return array|bool|false|string|void
     */
    function getInfo($whereData, $data = array('liveRecord'=>0,'livestatus'=>0)){
        
        if(!empty($whereData)){
            
            $xjh	=	$this -> select_once('xjhlive', $whereData);

            if($xjh['livestatus']==1 && ($xjh['stime']+(3600 * $xjh['playtime']))<time()){
                // 直播未开始，但是已经到结束时间，自动将其状态改成结束
                $this->update_once('xjhlive',array('livestatus'=>2), array('id'=>$xjh['id']));
            }
            
            $field  =  empty($data['field']) ? '*' : $data['field'];
            
            $Info   =  $this->select_once('xjhlive', $whereData, $field);
            
            if(!empty($Info)){
                
                $xjhpics	=	$this -> select_all('xjhlive_pic',array('xid'=>$Info['id']));
                $picarr		=	array();
                if(!empty($xjhpics)){
                    foreach($xjhpics as $pk=>$pv){
                        $picarr[$pk]['id'] 	 =  $pv['id'];
                        $picarr[$pk]['url']  =  checkpic($pv['picurl']);
                    }
                }
                $Info['picarr'] =	$picarr;
                if (!empty($Info['stime'])){
                    $Info['stime_n']  =  date('Y-m-d H:i',$Info['stime']);
                    $Info['stime_m']  =  date('Y-m-d H:i:s',$Info['stime']);
                }
                if (!empty($Info['etime'])){
                    $Info['etime_m']  =  date('Y-m-d H:i:s',$Info['etime']);
                }
                if (!empty($Info['ctime'])){
                    $Info['ctime_n']  =  date('Y-m-d H:i',$Info['ctime']);
                }
                
                $time  =  time();
                $utype =  isset($data['utype']) ? $data['utype'] : '';
                // 正在直播改为手动触发，但正在直播需在直播时间内，不然依然算未开始
                if (isset($Info['livestatus']) && $Info['livestatus']==3 && !empty($Info['playtime']) && $utype != 'admin'){
                    
                    if ($Info['stime'] > $time || ($Info['stime'] + (3600 * $Info['playtime'])) < $time){
                        
                        $Info['livestatus']  =  1;
                    }
                }
                if (!empty($Info['playtime'])){
                    $Info['endtime']  =  $Info['stime'] + (3600 * $Info['playtime']);
                }
                if (!empty($Info['statetime'])){
                    $Info['statetime_n']  =  date('Y-m-d H:i',$Info['statetime']);
                }
                // 直播公告
                $Info['content']    =  $this->config['sy_xjh_notice'];
                //获取所有参会企业信息
                if(isset($data['getcom'])){
                    $xjhcoms	=	$this -> select_all('xjhlive_com',array('xid'=>$Info['id']));
                    
                    if(!empty($xjhcoms)){
                        $cuid	=	array();
                        foreach($xjhcoms as $k=>$v){
                            $cuid[]  =  $v['uid'];
                        }
                        
                        require_once ('company.model.php');
                        $comM 	  =  new company_model($this->db, $this->def);
                        $comlist  =  $comM->getList(
                            array(
                                'uid'		=>	array('in',pylode(',',$cuid)),
                                'r_status'	=>	1,
                                'name'		=>	array('<>','')
                            ),
                            array('field'=>'`uid`,`content`,`name`','logo'=>'1')
                            );
                        
                        if (!empty($comlist['list'])){
                            
                            foreach ($comlist['list'] as $ck=>$cv){
                                $content = strip_tags($cv['content']);
                                if(mb_strlen($content)>100){
                                    $comlist['list'][$ck]['content']  =  mb_substr($content, 0, 100, 'UTF8').'...';
                                }else{
                                    $comlist['list'][$ck]['content']  =  $content;
                                }
                            }
                        }
                    }
                    $Info['comlist']	=	!empty($comlist['list']) ? $comlist['list'] : array();
                }
                
                if (!empty($Info['playurl'])){
                    
                    $Info['hls']  =  $Info['playurl'] . '.m3u8';
                    $Info['flv']  =  $Info['playurl'] . '.flv';
                    unset($Info['playurl']);
                }
                if (isset($data['livestatus']) && $data['livestatus'] == 1){
                    
                    if ($Info['livestatus'] == 2){
                        
                        $Info['errmsg']  =  '宣讲会直播已结束';
                    }
                }
            }
        }
        
        return $Info;
    }

    /**
     * @desc 删除宣讲会
     * @param int|array $delId
     * @param array $data: admin, zdid, uid,userdel
     */
    public function delXjh($delId = null, $data = array())
    {
        if (empty($delId)) {
            
            $return =   array(
                'errcode'   =>  8,
                'msg'       => '请选择要删除的数据！'
            );
        } else {
            
            if (is_array($delId)) {
                
                $delId  =   pylode(',', $delId);
                
                $return['layertype']    =   1;
            } else {
                
                $return['layertype']    =   0;
            }
            
            $delWhere   =   array('id' => array('in', $delId));
            
            $result =   $this->delete_all('xjhlive', $delWhere, '');
            
            if ($result) {
                
                $this->delete_all('xjhlive_black', array('xid' => array('in', $delId)), '');
                
                $this->delete_all('xjhlive_material', array('xid' => array('in', $delId)), '');
                
                $this->delete_all('xjhlive_yy', array('xid' => array('in', $delId)), '');
                
                $this->delete_all('xjhlive_yymsg', array('xid' => array('in', $delId)), '');
                
                $this->delete_all('xjhlive_com', array('xid' => array('in', $delId)), '');
                
                $this->delete_all('xjhlive_chat', array('xid' => array('in', $delId)), '');
                
                $this->delete_all('xjhlive_pic', array('xid' => array('in', $delId)), '');
                
                $postjson  =  array('xid'=>$delId);
                $this->dxRequest('m=xjh&c=delsync', $postjson);
            }
            
            $return['errcode']  =   $result ? 9 : 8;
            
            $msg    = '宣讲会(ID:'.$delId.')';
            
            $return['msg']  =   $result ? $msg.'删除成功！' : $msg.'删除失败！';
        }
        return $return;
    }
    /**
     * @desc 删除宣讲会封面
     * @param int|array $delId
     * @param array $data: admin, zdid, uid,userdel
     */
    public function delXjhPic($delId = null, $data = array())
    {
        if (empty($delId)) {
            
            $return =   array(
                'errcode'   =>  8,
                'msg'       => '请选择要删除的数据！'
            );
        } else {
            
            if (is_array($delId)) {
                
                $delId  =   pylode(',', $delId);
                
                $return['layertype']    =   1;
            } else {
                
                $return['layertype']    =   0;
            }
            
            if ($data['utype'] == 'admin') {
                
                $delWhere   =   array('id' => array('in', $delId));
            } else {
                
                $delWhere['id']     =   array('in', $delId);
                
                $delWhere['uid']    =   $data['uid'];
            }
            
            $result =   $this->delete_all('xjhlive_pic', $delWhere, '');
            
            
            $return['errcode']  =   $result ? 9 : 8;
            
            $msg    = '宣讲会封面';
            
            $return['msg']  =   $result ? $msg.'删除成功！' : $msg.'删除失败！';
        }
        return $return;
    }
    /**
     * 删除参会企业
     */
    public function delXjhCom($delId = null, $xid = ''){
        
        if (empty($delId) || $xid == '') {
            
            $return         =   array('errcode' => 8, 'msg' => '请选择要删除的数据！');
        } else {
            
            if (is_array($delId)) {
                
                $delId                  =   pylode(',', $delId);
                
                $return['layertype']    =   1;
            } else {
                
                $return['layertype']    =   0;
            }
            
            $list  =  $this->select_all('xjhlive_com',array('id'=>array('in',$delId)));
            
            if(!empty($list)){
                
                foreach ($list as $v){
                    
                    $uid[]  =  $v['uid'];
                }
            }
            
            $return['id']       =   $this -> delete_all('xjhlive_com', array('id' => array('in', $delId)), '');
            
            $return['errcode']  =   $return['id'] ? 9 : 8;
            
            $msg                =   '宣讲会参会企业（ID：'.$delId.'）';
            
            $return['msg']      =   $return['id'] ? $msg.'删除成功！' : '删除失败！';
        }
        return $return;
    }
    /**
     * 修改直播参会企业记录
     */
    function upXjhCom($where, $data){
        if(!empty($where) && !empty($data)){
            
            $xid	=	$this->update_once('xjhlive_com',$data,$where);
            
            return $xid;
        }
    }
    /**
     * 查询单个参会企业
     */
    function getXjhCom($where, $data = array()){
        
        $row = $this->select_once('xjhlive_com', $where);
        
        if (!empty($row)){
            // 当前正在讲解的企业数据
            if (isset($data['explain'])){
                
                $com = $this->select_once('company', array('uid'=>$row['uid']), '`name`');
                $job = $this->select_all('company_job', array('uid'=>$row['uid'],'state' => 1, 'status' => 0,'r_status' => 1), '`id`,`name`,`minsalary`,`maxsalary`,`exp`,`edu`,`provinceid`,`cityid`,`three_cityid`');
                
                include ('cache.model.php');
                $cacheM     =   new cache_model($this->db, $this->def);
                $cache      =   $cacheM -> GetCache(array('com','city'));
                
                foreach ($job as $k=>$v){
                    $job[$k]['salary'] = salaryUnit($v['minsalary'], $v['maxsalary'], 4);
                    $job[$k]['salary'] = salaryUnit($v['minsalary'], $v['maxsalary'], 4);
                    if(!empty($v['provinceid'])){
                        $job[$k]['citystr']  =  $cache['city_name'][$v['provinceid']];
                        if(!empty($v['cityid'])){
                            $job[$k]['citystr']  =  $cache['city_name'][$v['cityid']];
                            if(!empty($v['three_cityid'])){
                                $job[$k]['citystr']  =  $cache['city_name'][$v['three_cityid']];
                            }
                        }
                    }
                    if(!empty($v['exp'])){
                        $job[$k]['job_exp']  =	$cache['comclass_name'][$v['exp']];
                    }else{
                        $job[$k]['job_exp']  =	'不限';
                    }
                    if(!empty($v['edu'])){
                        $job[$k]['job_edu']  =	$cache['comclass_name'][$v['edu']];
                    }else{
                        $job[$k]['job_edu']  =	'不限';
                    }
                }
                
                $row['comname'] = $com['name'];
                $row['joblist'] = $job;
            }
        }
        return $row;
    }
    /**
     * 查询参会企业
     */
    function getXjhComList($whereData=array(),$data=array()){
        
        // 根据关键词，先搜企业
        if (isset($data['keyword']) && !empty($data['keyword'])){
            
            $kcom     =  array();
            $company  =  $this -> select_all('company', array('name'=>array('like',$data['keyword'])),'`uid`,`name`,`logo`,`logo_status`,`hy`,`mun`,`pr`');
            
            if (!empty($company)){
                foreach ($company as $v){
                    $kcom[] = $v['uid'];
                }
            }
            $whereData['uid'] = array('in',pylode(',',$kcom));
        }
        
        $field  =  !empty($data['field']) ? $data['field'] : '*';
        
        $List   =  $this->select_all('xjhlive_com',$whereData,$field);
        
        if(!empty($List)){
            
            $uid  =  $zid  =  $xid = array();
            
            foreach($List as $v){
                if($v['uid'] && !in_array($v['uid'] , $uid)){
                    $uid[]  =  $v['uid'];
                }
                if($v['xid'] && !in_array($v['xid'],$xid)){
                    $xid[]  =  $v['xid'];
                }
            }
            
            if ($data['utype'] == 'admin' || $data['utype'] == 'wap'){
                
                if (!isset($company)){
                    $company  =  $this -> select_all('company', array('uid'=>array('in',pylode(',',$uid))),'`uid`,`name`,`logo`,`logo_status`,`hy`,`mun`,`pr`');
                }
                
                $jfield   =  '`id`,`uid`,`name`,`minsalary`,`maxsalary`,`exp`,`edu`,`provinceid`,`cityid`,`three_cityid`,`welfare`,`description`,`zp_num`,`sex`,`edu`,`zp_minage`,`zp_maxage`,`com_name`';
                
                $listA	  =  $this -> select_all('company_job',array('uid'=>array('in',pylode(',',$uid)),'state' => 1, 'status' => 0,'r_status' => 1),$jfield);
                
                if (!empty($listA)){
                    include ('cache.model.php');
                    $cacheM     =   new cache_model($this->db, $this->def);
                    $cache      =   $cacheM -> GetCache(array('com','city','hy'));
                    
                    foreach ($listA as $k=>$v){
                        $listA[$k]['url'] = Url('job', array('c'=>'comapply', 'id'=>$v['id']));
                        $listA[$k]['salary'] = salaryUnit($v['minsalary'], $v['maxsalary'], 4);
                        if(!empty($v['provinceid'])){
                            $listA[$k]['citystr']         =  $cache['city_name'][$v['provinceid']];
                            if(!empty($v['cityid'])){
                                $listA[$k]['citystr']         =  $cache['city_name'][$v['cityid']];
                                if(!empty($v['three_cityid'])){
                                    $listA[$k]['citystr']         =  $cache['city_name'][$v['three_cityid']];
                                }
                            }
                        }
                        if(!empty($v['exp'])){
                            $listA[$k]['job_exp']         =	$cache['comclass_name'][$v['exp']];
                        }else{
                            $listA[$k]['job_exp']         =	'不限';
                        }
                        if(!empty($v['edu'])){
                            $listA[$k]['job_edu']         =	$cache['comclass_name'][$v['edu']];
                        }else{
                            $listA[$k]['job_edu']         =	'不限';
                        }
                        $listA[$k]['job_sex']             = $cache['com_sex'][$v['sex']] == '不限' ? '' : $cache['com_sex'][$v['sex']];
                        if(!empty($v['zp_minage']) && !empty($v['zp_maxage'])){
                            if($v['zp_minage']==$v['zp_maxage']){
                                $listA[$k]['job_age'] = $v['zp_minage']."周岁以上";
                            }else{
                                $listA[$k]['job_age'] = $v['zp_minage']."-".$v['zp_maxage']."周岁";
                            }
                        }elseif(!empty($v['zp_minage'])){
                            $listA[$k]['job_age'] = $v['zp_minage']."周岁以上";;
                        }else{
                            $listA[$k]['job_age'] = '';
                        }
                        if(!empty($v['zp_num'])){
                            $listA[$k]['job_number']      =   $v['zp_num'];
                        }
                        if(!empty($v['welfare'])){
                            $v['welfare'] = str_replace(' ', '',$v['welfare']);
                            $listA[$k]['job_welfare']     =   array_filter(@explode(',', trim($v['welfare'])));
                        }
                        unset($listA[$k]['minsalary']);
                        unset($listA[$k]['maxsalary']);
                        unset($listA[$k]['exp']);
                        unset($listA[$k]['edu']);
                        unset($listA[$k]['provinceid']);
                        unset($listA[$k]['cityid']);
                        unset($listA[$k]['three_cityid']);
                        unset($listA[$k]['welfare']);
                        
                        foreach($company as $val){
                            if($v['uid'] == $val['uid']){
                                if($val['logo_status'] != 0){
                                    $listA[$k]['logo']  =  checkpic($this->config['sy_unit_icon']);
                                }else{
                                    $listA[$k]['logo']  =  checkpic($val['logo'], $this->config['sy_unit_icon']);
                                }
                            }
                        }
                    }
                }
            }
            
            $xjh  =  $this -> select_all('xjhlive',array('id'=>array('in',pylode(',',$xid))),'`id`,`name`');
            
            
            foreach($List as $k => $v){
                
                if ($data['utype'] == 'admin' || $data['utype'] == 'wap'){
                    $List[$k]['ltime']	=	0;
                    if ($v['status'] == 3){
                        // 讲解结束
                        $List[$k]['ltime']	=  ceil(($v['etime'] - $v['stime']) / 60);
                    }elseif ($v['status'] == 1){
                        // 讲解中
                        $List[$k]['ltime']	=  ceil((time() - $v['stime']) / 60);
                    }
                    foreach($company as $val){
                        
                        if($v['uid'] == $val['uid']){
                            
                            $List[$k]['comname']	=	$val['name'];
                            $List[$k]['com_pr']     =   $cache['comclass_name'][$val['pr']];
                            $List[$k]['com_mun']    =   $cache['comclass_name'][$val['mun']];
                            $List[$k]['com_hy']     =   $cache['industry_name'][$val['hy']];
                            // logo没通过审核，用默认logo
                            if($data['utype'] == 'wap' && $val['logo_status'] != 0){
                                $List[$k]['logo']  =  checkpic($this->config['sy_unit_icon']);
                            }else{
                                $List[$k]['logo']  =  checkpic($val['logo'], $this->config['sy_unit_icon']);
                            }
                        }
                    }
                    $jobname   = array();
                    $jobcount  = 0;
                    
                    foreach($listA as $val){
                        
                        if ($v['uid'] == $val['uid']) {
                            $List[$k]['joblist'][]  =  $val;
                            if (count($jobname) < 10){
                                $jobname[]		      =  $val['name'];
                                $List[$k]['jobname']  =  @implode('、',$jobname);
                            }
                            $jobcount++;
                        }
                    }
                    $List[$k]['jobcount']  =  $jobcount;
                }
                foreach($xjh as $val){
                    if($v['xid'] == $val['id']){
                        $List[$k]['xjhname']	=	$val['name'];
                    }
                }
            }
        }
        
        return $List;
    }
    /**
     * @desc 查询宣讲会预定列表
     * $whereData 	查询条件
     * $data 		自定义处理数组
     */
    function getyyList($where=array(),$data=array()){
        
        if(!empty($where)){
            
            $List	=	$this->select_all('xjhlive_yy',$where);
            
            if(!empty($List)){
                
                $uid  =  $xid  =  array();
                
                foreach($List as $k=>$v){
                    
                    if($v['uid'] && !in_array($v['uid'] , $uid)){
                        $uid[]  =  $v['uid'];
                    }
                    if($v['xid'] && !in_array($v['xid'],$xid)){
                        $xid[]  =  $v['xid'];
                    }
                }
                $user     =  $this->select_all('resume', array('uid'=>array('in',pylode(',',$uid))),'`uid`,`name`');
                $xjhList  =  $this->getList(array('id'=>array('in',pylode(',',$xid))));
                
                foreach($List as $key=>$val){
                    
                    $List[$key]['ctime_n']  =  date('Y-m-d H:i', $val['ctime']);
                    
                    foreach ($user as $cv){
                        if ($val['uid'] == $cv['uid']){
                            
                            $List[$key]['name']  =	$cv['name'];
                        }
                    }
                    foreach($xjhList as $k=>$v){
                        
                        if($val['xid'] == $v['id']){
                            
                            $List[$key]['xjhname']	     =	$v['name'];
                            
                            $List[$key]['stime_n']	     =	$v['stime_n'];
                            
                            $List[$key]['livestatus_n']	 =	$v['livestatus_n'];
                            $List[$key]['livestatus']	 =	$v['livestatus'];
                            $List[$key]['wapxjh_url']    =  Url('wap',array('c'=>'xjhlive','a'=>'show','id'=>$v['id']));
                        }
                    }
                }
            }
            
            return $List;
        }
    }
    /**
     * 删除宣讲会预约记录
     * @param int|array $delId
     * @param array $data
     */
    public function delyy($delId, $data = array()){
        
        if (empty($delId)) {
            
            $return         =   array('errcode' => 8, 'msg' => '请选择要取消的数据！');
        } else {
            
            if (is_array($delId)) {
                
                $delId                  =   pylode(',', $delId);
                
                $return['layertype']    =   1;
            } else {
                
                $return['layertype']    =   0;
            }
            
            if ($data['utype'] == 'admin'){
                
                $delWhere	=	array('id' => array('in', $delId));
                
            }else{
                
                $delWhere	=	array('id' => array('in', $delId), 'uid' => $data['uid']);
            }

            $yylist         =   $this->select_all('xjhlive_yy', $delWhere);

            $yids           =   array();

            foreach($yylist as $k=>$v){
                $yids[]     =   $v['id'];
            }
            
            $return['id']       =  $this->delete_all('xjhlive_yy', $delWhere, '');
            
            if ($return['id']) {

                $this -> delete_all('xjhlive_yymsg', array('yid'  => array('in', pylode(',', $yids))), '');
            }

            $return['errcode']  =  $return['id'] ? 9 : 8;
            
            $msg                =  '宣讲会预约记录';
            
            $return['msg']      =  $return['id'] ? $msg.'取消成功！' : $msg.'取消失败！';
        }
        return $return;
    }
    /**
     * 添加宣讲会严禁词
     */
    function addXjhkeyword($name=array(),$data=array()){
        
        if(!empty($name)){
            
            $nwarr	=	$this->select_all('xjhlive_keyword', array('name' => array('in', "'".implode("','", $name)."'")));
            
            if(empty($nwarr)){
                
                foreach($name as $k=>$v){
                    
                    if (!empty($v)){
                        
                        $nid=	$this->insert_into('xjhlive_keyword',array('name'=>$v));
                    }
                }
                
                if($nid){
                    
                    $this->addKeywordFile();
                    
                    $return['msg']      =   '添加成功';
                    
                    $return['errcode']  =   9;
                    
                }else{
                    
                    $return['msg']      =   '数据错误';
                    
                    $return['errcode']  =   8;
                }
            }else{
                
                $return['msg']      =   '该严禁词已添加';
                $return['code']  	=   7;
                $return['errcode']  =   8;
            }
            
        }else{
            $return['msg']      =   '严禁词不能为空';
            
            $return['errcode']  =   8;
        }
        return $return;
    }
    private function addKeywordFile($data=array()){
        
        $zdid 	= 	$data['zdid'] ? $data['zdid'] : 0;
        
        $nwarr	=	$this->select_all('xjhlive_keyword');
        
        $name	=	array();
        foreach($nwarr as $k=>$v){
            $name[]	=	$v['name'];
        }
        if(!empty($name)){
            
            $nstr	=	implode("\r\n", $name);
        }else{
            
            $nstr	=	'';
        }
        
        $path 	= 	DATA_PATH.'sensitive/xjhword.txt';
        
        $fp = @fopen($path,w);
        @fwrite($fp,"$nstr");
        @fclose($fp);
    }
    public function delXjhkeyword($delId = null, $data = array()){
        
        if (empty($delId)) {
            
            $return         =   array('errcode' => 8, 'msg' => '请选择要删除的数据！');
        } else {
            
            if (is_array($delId)) {
                
                $delId                  =   pylode(',', $delId);
                
                $return['layertype']    =   1;
            } else {
                
                $return['layertype']    =   0;
            }
            
            $delWhere	=	array('id' => array('in',$delId));
            
            $return['id']       =   $this -> delete_all('xjhlive_keyword', $delWhere, '');
            
            $this->addKeywordFile();
            
            $return['errcode']  =   $return['id'] ? 9 : 8;
            
            $msg                =   '严禁词';
            
            $return['msg']      =   $return['id'] ? $msg.'删除成功！' : '删除失败！';
        }
        return $return;
    }
    /**
     * 严禁词列表
     */
    public function getXjhkeywordList($whereData , $data = array('field'=>null, 'utype'=> null)) {
        
        $select  =  $data['field'] ? $data['field'] : '*';
        
        $List	 =  $this -> select_all('xjhlive_keyword',$whereData,$select);
        
        return $List;
    }
    /**
     * 添加宣讲会开播通知待发送记录
     */
    function addyyMsg($xjhid = '', $uid = '',$nid){
        
        if($xjhid != ''){
            
            $xjh	=	$this -> select_once('xjhlive', array('id'=>$xjhid),'`id`,`name`,`stime`');
            
            if(!empty($xjh)){
                $stime	=  $xjh['stime']-30*60>time()?$xjh['stime']-30*60:time();
                
                $msgData  =  array(
                    'xid'	   =>  $xjh['id'],
                    'stime'	   =>  $stime,
                    'yid'      =>  $nid,
                    'xjhname'  =>  $xjh['name'],
                    'xjhtime'  =>  $xjh['stime']
                );
                
                $member  =  $this->select_once('member',array('uid'=>$uid),'`uid`,`wxid`');
                
                if (!empty($member)){
                    // 有这些参数，可用来发送通知
                    $msgData['uid']       =  $member['uid'];
                    
                    if (empty($member['wxid'])){
                        
                        $return['wxshow']  =  1;
                    }
                }
                
                $return['id']  =  $this -> insert_into('xjhlive_yymsg', $msgData);
                
                return $return;
            }
        }
    }
    /**
     * 直播宣讲会预约发送微信提醒
     * 后台计划任务文件名：xjhlive_yy.php; url执行方法：index.php?m=ajax&c=sendXjhliveYy
     */
    function sendYymsg(){
        
        $time = time();
        $begined = $this->select_once('xjhlive_yymsg', array('status'=>1,'xjhtime'=>array('<',$time)));
        if (!empty($begined)){
            $this->delete_all('xjhlive_yymsg', array('status'=>1,'xjhtime'=>array('<',$time)), '');
        }
        $list = $this->select_all('xjhlive_yymsg', array('status'=>0,'stime'=>array('<',time())));
        if(!empty($list)){
            $ids = array();
            foreach($list as $val){
                $yids[] = $val['yid'];
            }
            $yids = array_unique($yids);
            $yys = $this->select_all('xjhlive_yy', array('id'=>array('in', pylode(',', $yids))), '`id`,`openid`,`xcx_openid`,`subscribe`,`utype`');
            require_once 'weixin.model.php';
            $weixinM = new weixin_model($this->db, $this->def);
            $sendData = array();
            foreach($list as $key=>$val){
                foreach ($yys as $v){
                    if ($val['yid'] == $v['id']){
                        if ($v['openid']) {
                            $sendData['id'] = $val['id'];
                            $sendData['uid'] = $val['uid'];
                            $sendData['usertype'] = $v['utype'];
                            $sendData['wxid'] = $v['openid'];
                            $sendData['name'] = $val['xjhname'];
                            $sendData['stime'] = $val['xjhtime'];
                            $sendData['url'] = Url('wap', array('c'=>'xjhlive', 'a' => 'show', 'id' => $val['xid']));
                            $sendData['minipath'] = 'pages/xjhlive/show?id=' . $val['xid'];
                            $weixinM->sendWxXjhLiveYy($sendData);
                        } else if ($v['xcx_openid'] && $v['subscribe']) {
                            $subscribeTpl = array(
                                "thing1" => array("value" => mb_substr($val['xjhname'],0,20,'utf-8')),
                                "time3" => array("value" => date('Y-m-d H:i', $val['xjhtime']))
                            );
                            $weixinM->sendWxSubscribe(array('uid' => $val['uid'],'wxid' => $v['xcx_openid'], 'url' => 'pages/xjhlive/show?id=' . $val['xid'], 'tpl_data' => $subscribeTpl, 'tpl_type' => 1, 'utype' => $v['utype']));
                        }

                        $ids[] = $val['id'];
                    }
                }
            }
            if (!empty($ids)){
                $this->update_once('xjhlive_yymsg',array('status'=>1),array('id'=>array('in',pylode(',', $ids))));
            }
        }
    }
    /**
     * 添加预约记录
     * @param array $data
     * @return boolean|unknown
     */
    function addyy($data=array()){
        
        if(!empty($data)){
            $nid	=	$this->insert_into('xjhlive_yy', $data);
        }
        
        return $nid;
        
    }
    /**
     * 获取预约信息
     */
    function getyyInfo($whereData=array(),$data=array()){
        
        if(!empty($whereData)){
            
            $field  =	empty($data['field']) ? '*' : $data['field'];
            
            $Info	=	$this -> select_once('xjhlive_yy', $whereData, $field);
            
            if(!empty($Info)){
                
                $xjh	=	$this->getInfo(array('id'=>$Info['xid']));
                
                $Info['status_n']	=	$xjh['status_n'];
                
                $Info['name']		=	$xjh['name'];
                
                $Info['stime_n']	=	$xjh['stime_n'];
            }
        }
        
        return $Info;
        
    }
    /**
     * 添加宣讲会导播台素材
     */
    function addMaterial($data){
        
        if (!empty($data)){
            
            $this->insert_into('xjhlive_material', $data);
        }
    }
    /**
     * 查询宣讲会导播台素材
     */
    function getMaterial($where){
        
        $row  =  $this->select_once('xjhlive_material', $where);
        
        if (!empty($row['url'])){
            
            $token = $this->base64Encode(array('ak'=>$this->config['sy_xjhlive_appkey'],'t'=>time()));
            
            if (stripos($row['url'], '.mp4')!==false || stripos($row['url'], '.MP4') !== false){
                $row['url'] .= '?vframe/jpg/offset/0/w/140/h/90&token=' .$token;
            }else{
                $row['url'] .= '?token='.$token;
            }
        }
        return $row;
    }
    /**
     * 查询宣讲会导播台素材列表
     */
    function getMaterials($where){
        
        $rows  =  $this->select_all('xjhlive_material', $where);
        
        if (!empty($rows)){
            
            $token = $this->base64Encode(array('ak'=>$this->config['sy_xjhlive_appkey'],'t'=>time()));
            
            foreach ($rows as $k=>$v){
                
                $rows[$k]['video']  =  0;
                if (stripos($v['url'], '.mp4')!==false  || stripos($v['url'], '.MP4')!==false){
                    $rows[$k]['video']  =  1;
                    $rows[$k]['url']  =  $v['url']. '?vframe/jpg/offset/0/w/140/h/90&token=' .$token;
                }else{
                    $rows[$k]['url']  =  $v['url']. '?token=' .$token;
                }
            }
        }
        return $rows;
    }
    /**
     * 宣讲会导播台素材修改
     */
    function upMaterial($where=array(),$data=array()){
        
        if(!empty($where)){
            
            $xid  =  $this->update_once('xjhlive_material',$data,$where);
            
            return $xid;
        }
    }
    /**
     * @desc 删除宣讲会导播台素材
     */
    public function delMaterial($delId = null, $data = array())
    {
        if (empty($delId)) {
            
            $return =   array(
                'errcode'   =>  8,
                'msg'       => '请选择要删除的数据！'
            );
        } else {
            
            if (is_array($delId)) {
                
                $delId  =   pylode(',', $delId);
                
                $return['layertype']    =   1;
            } else {
                
                $return['layertype']    =   0;
            }
            
            $result =   $this->delete_all('xjhlive_material', array('id' => array('in', $delId)), '');
            
            $return['errcode']  =   $result ? 9 : 8;
            
            $msg    = '导播素材';
            
            $return['msg']  =   $result ? $msg.'删除成功！' : $msg.'删除失败！';
        }
        return $return;
    }
    /**
     * 设置导播台暖场视频
     */
    function setCasterVideo($where,$data){
        
        $msg  =  '暖场视频';
        
        if ($data['state'] == 1){
            // 设置
            $startvideo  =  1;
            
        }else{
            // 取消设置
            $startvideo  =  0;
            $msg .=  '取消';
        }
        
        $this->update_once('xjhlive_material', array('startvideo'=>$startvideo), $where);
        
        $row  =  $this->select_once('xjhlive_material',$where, '`id`,xid,`syncid`');
        
        $postjson['mid']         =  $row['syncid'];
        $postjson['xid']         =  $row['xid'];
        $postjson['startvideo']  =  $startvideo;
        
        $url  =  'm=xjh&c=setVideo';
        
        $this->dxRequest($url, $postjson);
        
        $return['msg']      =  $msg . '设置成功';
        $return['errcode']  =  9;
        
        return $return;
    }
    /**
     * 创建导播台
     */
    function createCaster($data = array()){
        
        $xid  =  $data['xid'];
        $xjh  =  $this->select_once('xjhlive', array('id'=>$xid), '`id`,`stime`,`playtime`,`status`,`livestatus`');
        
        if ($xjh['status'] == 0){
            
            $return  =  array('errcode'=>8,'msg'=>'宣讲会审核中');
            
        }elseif ($xjh['status'] == 2){
            
            $return  =  array('errcode'=>8,'msg'=>'宣讲会未通过审核');
            
        }elseif ($xjh['stime'] - 600 >= time()){
            
            $return  =  array('errcode'=>8,'msg'=>'宣讲会开始前10分钟内才可创建导播台');
            
        }elseif ($xjh['livestatus'] == 2){
            
            $return  =  array('errcode'=>8,'msg'=>'宣讲会已结束');
            
        }elseif ($xjh['livestatus'] == 3){
            
            $return  =  array('errcode'=>8,'msg'=>'宣讲会正在直播中，无法创建导播台');
            
        }else{
            
            $material  =  $this->select_once('xjhlive_material',array('xid'=>$xjh['id']));
            
            if (!empty($material)){
                
                // 创建导播台
                $postjson['xid']  =  $xjh['id'];
                
                $url 	  =	 'm=xjh&c=createCaster';
                
                $return   =  $this->dxRequest($url, $postjson);
                
                if (!empty($return['playurl'])){
                    
                    $this->update_once('xjhlive',array('playurl'=>$return['playurl'],'caster'=>1),array('id'=>$xjh['id']));
                    
                    $return  =  array('errcode'=>9,'msg'=>'导播台创建成功');
                }else{
                    $return  =  array('errcode'=>8,'msg'=>$return['msg']);
                }
            }else{
                
                $return  =  array('errcode'=>8,'msg'=>'请先添加导播素材');
            }
        }
        return $return;
    }
    /**
     * 开启导播台
     */
    function openCaster($data = array()){
        
        $xid  =  $data['xid'];
        $xjh  =  $this->select_once('xjhlive', array('id'=>$xid), '`id`');
        
        $postjson['xid']  =  $xjh['id'];
        
        $url 	  =	 'm=xjh&c=openCaster';
        
        $return   =  $this->dxRequest($url, $postjson);
        
        if ($return['errcode'] == 9){
            
            $this->update_once('xjhlive',array('caster'=>2), array('id'=>$xjh['id']));
            
            $return  =  array('errcode'=>9,'msg'=>$return['msg']);
            
        }else{
            
            $return  =  array('errcode'=>8,'msg'=>$return['msg']);
        }
        
        return $return;
    }
    /**
     * 导播台切换监视器画面，并切换布局
     */
    function upMonitor($data = array()){
        
        $xid  =  $data['xid'];
        $xjh  =  $this->select_once('xjhlive', array('id'=>$xid), '`id`');
        
        $postjson['xid']     =  $xjh['id'];
        $postjson['mid']     =  $data['mid'];
        $postjson['layout']  =  $data['layout'];
        
        $url 	  =	 'm=xjh&c=upMonitor';
        
        $return   =  $this->dxRequest($url, $postjson);
        
        if ($return['errcode'] == 9){
            
            $return  =  array('errcode'=>9,'msg'=>'切换成功');
            
        }else{
            
            $return  =  array('errcode'=>8,'msg'=>$return['msg']);
        }
        
        return $return;
    }
    /**
     * 导播台切换监视器画面，并切换布局
     */
    function liveCaster($data = array()){
        
        $xid  =  $data['xid'];
        $xjh  =  $this->select_once('xjhlive', array('id'=>$xid), '`id`');
        
        $postjson['xid']  =  $xjh['id'];
        
        $url 	  =	 'm=xjh&c=liveCaster';
        
        $return   =  $this->dxRequest($url, $postjson);
        
        if ($return['errcode'] == 9){
            
            $return  =  array('errcode'=>9,'msg'=>'切换成功');
            
        }else{
            
            $return  =  array('errcode'=>8,'msg'=>$return['msg']);
        }
        
        return $return;
    }
    /**
     * 上传素材文件
     */
    function uploadMaterial($file, $data = array()){
        
        $xjh  =  $this->select_once('xjhlive', array('id'=>$data['xid']), '`livestatus`');
        
        if($xjh['livestatus'] ==2){
            
            $return  =  array('errcode'=>8,'msg'=>'直播宣讲会已结束');
            
        }else{
            
            if ($file['tmp_name']){
                
                $douxu  =  $this->dxRequest('m=xjh&c=getUpToken');
                
                if ($douxu['errcode'] == 9){
                    
                    require_once APP_PATH.'api/qiniu/oss.php';
                    
                    $oss  =  new qiniuOss();
                    $res  =  $oss->uploadFile($douxu['token'], $file);
                    
                    if (!empty($res['url'])){
                        
                        $postjson['xid']  =  $data['xid'];
                        $postjson['url']  =  $res['url'];
                        
                        $return    =  $this->dxRequest('m=xjh&c=addMaterial', $postjson);
                        
                    }else{
                        
                        $return  =  array('errcode'=>8,'msg'=>'文件上传失败');
                    }
                }else{
                    
                    $return  =  array('errcode'=>8,'msg'=>'上传凭证获取失败');
                }
            }else{
                
                $return  =  array('errcode'=>8,'msg'=>'请上传文件');
            }
        }
        return $return;
    }
    /**
     * 保存直播观看记录
     */
    function setUserlog($data = array()){
        
        if (!empty($data['uid'])){
            // 观看记录同一场次，不重复记录
            $num = $this->select_num('xjhlive_user', array('xid'=>$data['xid'], 'uid'=>$data['uid'], 'usertype'=>$data['usertype']));
            if ($num == 0){
                $data['ip'] = fun_ip_get();
                $data['ctime'] = time();
                $this->insert_into('xjhlive_user', $data);
            }
        }
    }
    /**
     * 查询直播观看记录数量
     */
    function getUserlogList($where){
        
        $List = $this->select_all('xjhlive_user', $where);
        
        if(!empty($List)){
            
            $uid  =  $cuid  =  $luid  =  array();
            
            foreach($List as $k=>$v){
                if ($v['usertype'] == 1){
                    $uid[]   =  $v['uid'];
                    $List[$k]['utype'] = '个人';
                }else if ($v['usertype'] == 2){
                    $cuid[]  =  $v['uid'];
                    $List[$k]['utype'] = '企业';
                }else if ($v['usertype'] == 3){
                    $luid[]  =  $v['uid'];
                    $List[$k]['utype'] = '猎头';
                }else if ($v['usertype'] == 0){
                    $List[$k]['name'] = '游客'.substr($v['uid'], -6);
                    $List[$k]['utype'] = '游客';
                }

            }
            if (!empty($uid)){
                $resume   =	$this->select_all('resume',array('uid'=>array('in',pylode(',',$uid))),'`uid`,`name`,`photo`,`sex`');
            }
            if (!empty($cuid)){
                $company  =	$this->select_all('company',array('uid'=>array('in',pylode(',',$cuid))),'`uid`,`name`,`logo`');
            }
            if (!empty($luid)){
                $lietou   =  $this->select_all('lt_info',array('uid'=>array('in',pylode(',',$luid))),'`uid`,`realname`,`photo`');
            }
            
            foreach($List as $k => $v){
                
                $List[$k]['ctime_n']	=	date('Y-m-d H:i',$v['ctime']);
                if (isset($resume) && !empty($resume)){
                    foreach($resume as $val){
                        if($v['uid'] == $val['uid']){
                            $List[$k]['name']	=	$val['name'];
                        }
                    }
                }
                if (isset($company) && !empty($company)){
                    foreach($company as $val){
                        if($v['uid'] == $val['uid']){
                            $List[$k]['name']	=	$val['name'];
                        }
                    }
                }
                if (isset($lietou) && !empty($lietou)){
                    foreach($lietou as $val){
                        if($v['uid'] == $val['uid']){
                            $List[$k]['name']	=	$val['realname'];
                        }
                    }
                }
            }
        }
        return $List;
    }
    /**
     * 查询直播观看记录数量
     */
    function getUserlogNum($where){
        
        return $this->select_num('xjhlive_user', $where);
    }
    /**
     * 单场观看新入场统计图数据
     */
    function userlogTj($id){
        
        $row   =  $this->getInfo(array('id' => $id));
        if ($row['livestatus'] == 3){
            // 直播中
            $edate = time();
            
        }elseif ($row['livestatus'] == 2){
            // 直播结束
            if (!empty($row['etime'])){
                // 主动点了结束直播
                $edate = $row['etime'];
            }else{
                // 没有结束时间，按设置的直播时长计算
                $edate = $row['stime'] + ($row['playtime'] * 3600);
            }
        }
        $List  =  array();
        $Stats =  $this->select_all('xjhlive_user',array('xid' => $id),'`ctime`');
        
        if (isset($edate) && !empty($Stats)){
            
            $sdate  =   $row['stime'];
            // 总时长分为10个间隔，获取间隔时间
            $times = ceil(abs($edate - $sdate) / 10);
            // 循环得到横坐标
            $List[0]['count'] = 0;
            $List[0]['tjtime'] = date('H:i', $sdate);
            for ($i = 0; $i < 10; $i ++) {
                // 区间段开始时间
                if ($i == 0){
                    $stime  =  $sdate;
                }else{
                    $stime  =  $sdate + $i * $times;
                }
                // 区间段结束时间
                $etime  =  $sdate + (($i + 1) * $times);
                // 统计区间数量
                $count = 0;
                foreach($Stats as $k=>$val){
                    if ($val['ctime'] >= $stime && $val['ctime'] < $etime){
                        $count += 1;
                        unset($Stats[$k]);
                    }
                }
                $List[$i+1]['count'] = $count;
                $List[$i+1]['tjtime'] = date('H:i', $etime);
            }
        }
        
        return $List;
    }
    /**
     * 通过接口查询是否可以创建宣讲会
     */
    function xjhCanAdd(){
        
        $url 	=  'm=xjh&c=canAdd';
        
        $douxu  =  $this->dxRequest($url);
        
        return $douxu;
    }
    /**
     * 通过接口同步宣讲会，获取直播播放地址
     */
    function xjhSync($id){
        
        $xjh  =  $this->select_once('xjhlive',array('id'=>$id),'`id`,`name`,`ctime`,`stime`,`playtime`,`playback`,`state`,`statetime`');
  
        $postjson['xjh']      =  json_encode($xjh);
        
        $url 	  =	 'm=xjh&c=sync';
        
        $douxu    =  $this->dxRequest($url, $postjson);
        
        if (!empty($douxu['playurl'])){
            
            $this->update_once('xjhlive', array('playurl'=>$douxu['playurl'],'syncid'=>$douxu['id']), array('id'=>$xjh['id']));
        }else{
            
            $return  =  array('errcode'=>8,'msg'=>'操作失败：'.$douxu['msg']);
        }
        return $return;
    }
    /**
     * 通过接口获取宣讲会直播地址
     */
    function getLiveQrcode($where){
        
        $xjh  =  $this->select_once('xjhlive', $where, '`id`,`name`,`ctime`,`stime`,`playtime`,`playback`,`livestatus`');
        
        if($xjh['livestatus'] == 2 || ($xjh['stime']+3600*$xjh['playtime']) < time()){
            
            $return  =  array('errcode'=>8,'msg'=>'直播宣讲会已结束');
        }else{
            
            $postjson['xjh']  =  json_encode($xjh);
            
            $url 	  =	 'm=xjh&c=getLiveQrcode';
            
            $douxu    =  $this->dxRequest($url, $postjson);
            
            if (!empty($douxu['pushurl'])){
                
                $return  =  array('errcode'=>9,'msg'=>'直播宣讲会直播地址获取成功','url'=>$douxu['pushurl']);
                
            }else{
                
                $return  =  array('errcode'=>8,'msg'=>'直播宣讲会直播地址获取失败'.$douxu['msg']);
            }
        }
        return $return;
    }
    /**
     * 同步开始直播
     */
    function liveStart($where){
        
        $xjh  =  $this->select_once('xjhlive', $where, '`id`,`stime`,`livestatus`,`playtime`');
        
        if($xjh['livestatus'] == 3){
            
            $return  =  array('errcode'=>8,'msg'=>'直播宣讲会已开始，请不要重复操作');
            
        }elseif($xjh['livestatus'] ==2){
            
            $return  =  array('errcode'=>8,'msg'=>'直播宣讲会已结束');
            
        }else{
            
            $url 	=  'm=xjh&c=liveStart';
            
            $douxu  =  $this->dxRequest($url, array('xid'=>$xjh['id']));
            
            if ($douxu['errcode'] == 9){
                
                $this->update_once('xjhlive', array('livestatus'=>3), array('id'=>$xjh['id']));
                
                $return  =  array('errcode'=>9,'msg'=>'直播(ID:'.$xjh['id'].')开始成功');
                
            }else{
                
                $return  =  array('errcode'=>8,'msg'=>'直播(ID:'.$xjh['id'].')开始失败'.$douxu['msg']);
            }
        }
        return $return;
    }
    /**
     * 同步关闭直播
     */
    function liveEnd($where){
        
        $xjh  =  $this->select_once('xjhlive', $where, '`id`,`stime`,`livestatus`,`caster`,`playtime`');
        
        if($xjh['livestatus'] == 1 && $xjh['stime'] >= time()){
            
            $return  =  array('errcode'=>8,'msg'=>'直播宣讲会未开始');
            
        }elseif($xjh['livestatus'] ==2){
            
            $return  =  array('errcode'=>8,'msg'=>'直播宣讲会已结束');
            
        }else{
            
            $url 	  =	 'm=xjh&c=liveEnd';
            
            $douxu    =  $this->dxRequest($url, array('xid'=>$xjh['id']));
            
            if ($douxu['errcode'] == 9){
                
                $sql  =  array(
                    'livestatus'  =>  2,
                    'etime'       =>  $douxu['etime']
                );
                
                if (!empty($douxu['fname'])){
                    
                    $sql['recordurl']  =  $douxu['fname'];
                }
                if ($xjh['caster'] == 2){
                    
                    $sql['caster']  =  1;
                }
                $this->update_once('xjhlive', $sql, array('id'=>$xjh['id']));
                
                $return  =  array('errcode'=>9,'msg'=>'直播(ID:'.$xjh['id'].')关闭成功');
                
            }else{
                
                $return  =  array('errcode'=>8,'msg'=>'直播(ID:'.$xjh['id'].')关闭失败'.$douxu['msg']);
            }
            // 假如有正在讲解中的企业，需要将之结束
            if(isset($douxu['etime'])){
                // 小程序返回的结束时间
                $jjetime = $douxu['etime'];
            }elseif (($xjh['stime'] + $xjh['playtime'] * 3600) > time()){
                // 手动结束时间
                $jjetime = time();
            }else{
                // 结束时间超过设置直播时长
                $jjetime = $xjh['stime'] + $xjh['playtime'] * 3600;
            }
            $this->update_once('xjhlive_com',array('status'=>3, 'etime'=>$jjetime), array('xid'=>$xjh['id'], 'status'=>1));
        }
        return $return;
    }
    /**
     * 获取宣讲会直播推流地址
     */
    function getPushUrl($where){
        
        $xjh  =  $this->select_once('xjhlive', $where, '`id`,`stime`,`livestatus`,`caster`,`playtime`');

        if ($xjh['stime'] > strtotime('tomorrow') || $xjh['stime'] < strtotime('today')){
            $return  =  array('errcode'=>8,'msg'=>'宣讲会开始当天才可获取');
        }elseif($xjh['livestatus'] == 2){
            $return  =  array('errcode'=>8,'msg'=>'直播宣讲会已结束');
        }elseif ($xjh['caster'] != 0){
            $return  =  array('errcode'=>8,'msg'=>'已创建导播台，不支持获取推流地址');
        }else{
            $url 	  =	 'm=xjh&c=getPushUrl';
            
            $douxu    =  $this->dxRequest($url, array('xid'=>$xjh['id']));
            
            if ($douxu['errcode'] == 9){
                
                $return  =  array('errcode'=>9,'msg'=>'推流地址有效期'.$xjh['playtime'].'小时','live'=>$douxu['live']);
                
            }else{
                
                $return  =  array('errcode'=>8,'msg'=>'获取失败，'.$douxu['msg']);
            }
        }
        return $return;
    }
    /**
     * 手动修改直播回放时间，并重新录制
     */
    function liveRecord($where, $data){
        
        if ($data['stime'] < strtotime('-60 day')){
            return array('errcode'=>8,'msg'=>'不支持生成60天前的直播回放');
        }
        
        $xjh  =  $this->select_once('xjhlive', $where, '`id`,`livestatus`');
        
        if($xjh['livestatus'] !=2){
            
            $return  =  array('errcode'=>8,'msg'=>'直播宣讲会还未结束，无法生成回放');
            
        }else{
            
            $request = array(
                'xid' => $xjh['id'],
                'stime' => $data['stime'],
                'etime' => $data['etime']
            );
            
            $url 	  =	 'm=xjh&c=liveRecord';
            
            $douxu    =  $this->dxRequest($url, $request);
            
            if ($douxu['errcode'] == 9){
                
                if (!empty($douxu['fname'])){
                    
                    $sql = array(
                        'zstime' => $data['stime'],
                        'etime' => $data['etime'],
                        'recordurl' => $douxu['fname']
                    );
                    
                    $this->update_once('xjhlive', $sql, array('id'=>$xjh['id']));
                    
                    $return  =  array('errcode'=>9,'msg'=>'直播(ID:'.$xjh['id'].')回放生成成功');
                }else{
                    
                    $return  =  array('errcode'=>8,'msg'=>'直播(ID:'.$xjh['id'].')回放无法生成');
                }
                
            }else{
                
                $return  =  array('errcode'=>8,'msg'=>'直播(ID:'.$xjh['id'].')回放生成失败，'.$douxu['msg']);
            }
        }
        return $return;
    }
    /**
     * 请求综合平台
     */
    private function dxRequest($url, $postjson = array()){
        
        if (!empty($this->config['sy_xjhlive_appkey']) && !empty($this->config['sy_xjhlive_appsecret'])){
            
            $postjson['appKey']     =  $this->config['sy_xjhlive_appkey'];
            $postjson['appSecret']  =  $this->config['sy_xjhlive_appsecret'];
            $postjson['version']    =  630;
            
            $url      =  'https://trtc.phpyun.com/zphnetv/index.php?'. $url;
            
            $result	  =	 CurlPost($url,$postjson);
            $reponse  =	 json_decode($result, true);
            $return   =  $reponse['data'];
            
        }else{
            
            $return  =  array('errcode'=>8,'msg'=>'请配置宣讲会云导播秘钥');
        }
        return $return;
    }
    /**
     * base64加密
     * @param string $data 需要编码的数组
     * @return string
     */
    private static function base64Encode($data)
    {
        return str_replace('=', '', strtr(base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)), '+/', '-_'));
    }
}
?>