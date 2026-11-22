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
class admin_xjhlive_controller extends adminCommon
{

    function set_search()
    {
        
        $search_list    =   array();

        $search_list[]  =   array(
            
            'param'     =>  'end',
            'name'      =>  '创建时间',
            'value'     =>  array(
                
                '1'     =>  '今天',
                '3'     =>  '最近三天',
                '7'     =>  '最近七天',
                '15'    =>  '最近半月',
                '30'    =>  '最近一个月'
            )
        );
        $search_list[]  =   array(
            
            'param'     =>  'livestatus',
            'name'      =>  '直播状态',
            'value'     =>  array(
                
                '1'     =>  '未开始',
                '3'     =>  '直播中',
                '2'     =>  '已结束'
            )
        );

        $this->yunset('search_list', $search_list);
    }

    /**
     * @desc 后台  - 宣讲会列表
     */
    function index_action() {
        
        $this->set_search();
        $where  =  array();
        session_start();
        // 修改成功之后跳转到列表页使用session中记录的上次进入列表页的请求参数
        if (isset($_GET['remember_prev']) && $_GET['remember_prev']) {
            foreach ($_SESSION['prev_xjhlivelist'] as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        // 本次请求的参数保存到session中，供下次修改页面返回列表页使用
        $_SESSION['prev_xjhlivelist'] = $_GET;
        if ($_GET['status']) {

            $status             =   intval($_GET['status']);

            $where['status']    =   $status;

            $urlarr['status']   =   $status;
        }

        if ($_GET['keyword']) {

            $keyword            =   trim($_GET['keyword']);

            $where['name']     =   array('like', $keyword);

            $urlarr['keyword']  =   $keyword;
        }

        if ($_GET['end']) {

            if ($_GET['end'] == '1') {

                $where['ctime'] =   array('>=', strtotime('today'));
                
            } else {

                $where['ctime'] =   array('>=', strtotime('-'.intval($_GET['end']).' day'));
            }
            
            $urlarr['end']      =   $_GET['end'];
        }
        if (isset($_GET['livestatus'])) {
            
            $livestatus             =   intval($_GET['livestatus']);
            
            $where['livestatus']    =   $livestatus;
            
            $urlarr['livestatus']   =   $livestatus;
        }
		$urlarr        	=   $_GET;
        // 删除记住上一次请求参数，防止生成的分页中含有该参数影响页面跳转
        if (isset($urlarr['remember_prev'])) {
            unset($urlarr['remember_prev']);
        }
        $urlarr['page'] = '{{page}}';

        $pageurl = Url($_GET['m'], $urlarr, 'admin');

        // 提取分页
        $pageM = $this->MODEL('page');
        $xjhM  = $this->MODEL('xjhlive');

        $pages = $pageM->pageList('xjhlive', $where, $pageurl, $_GET['page']);

        // 分页数大于0的情况下 执行列表查询

        if ($pages['total'] > 0) {
            // limit order 只有在列表查询时才需要
            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
                
            } else {

                $where['orderby']   =   array('status,asc','id,desc');
                
            }
            
            $where['limit']         =   $pages['limit'];
            
            $rows   =   $xjhM -> getList($where,array('utype'=>'admin','num'=>1));
            $picarr =   array();
            foreach($rows as $k=>$v){
                if(!empty($v['picarr'])){
                    $picarr[$v['id']]=$v['picarr'];
                }
            }
            
            $this   ->  yunset('picarr',$picarr);
            $this   ->  yunset('rows', $rows);
            
            //提取分站内容
            $cacheM  =	$this -> MODEL('cache');
            $domain  =	$cacheM	-> GetCache('domain');
            
            $this -> yunset('Dname', $domain['Dname']);
        }
        
        $this->yuntpl(array('admin/admin_xjhlive'));
        
    }
    /**
     * 列表切换分站
     */
    function checksitedid_action(){
        
        if($_POST['uid']){
            
            $uids	=	@explode(',', $_POST['uid']);
            
            $uid	= 	pylode(',', $uids);
            
            if($uid){
                
                $siteDomain  =  $this -> MODEL('site');
                
                $siteDomain -> updDid(array('xjhlive'), array('id' => array('in', $uid)), array('did' => $_POST['did']));
                
                $this -> ACT_layer_msg('直播宣讲会(ID:'.$_POST['uid'].')分配站点成功！', 9, $_SERVER['HTTP_REFERER']);
                
            }else{
                
                $this -> ACT_layer_msg('请正确选择需分配数据！', 8, $_SERVER['HTTP_REFERER']);
                
            }
            
        }else{
            
            $this -> ACT_layer_msg('参数不全请重试！', 8, $_SERVER['HTTP_REFERER']);
        }
    }
    function add_action(){

        if($_GET['id']){
            $xjhM  =  $this->MODEL('xjhlive');
            $info  =  $xjhM -> getInfo(array('id'=>intval($_GET['id'])));
            $this -> yunset('info',$info);
        }
        $cacheM  =  $this->MODEL('cache');
        
        $cache   =  $cacheM -> GetCache(array('city'));
        $domain  =	$cacheM	-> GetCache('domain');
        
        $this -> yunset('Dname', $domain['Dname']);
        
        if(empty($cache['city_type'])){
            $this->yunset('cionly',1);
        }

        $this->yunset('playtime',array(1=>'1小时',2=>'2小时',4=>'4小时'));
        
        $this->yunset($cache);
        $this->yunset('now',date('Y-m-d H:i:s',time()));
        $this->yuntpl(array('admin/admin_xjhlive_add'));
    }
    /**
     * 直播宣讲会保存
     */
    function save_action(){
        
        $_POST  =  $this->post_trim($_POST);
        
        $post  =  array(
            'name'        =>  $_POST['name'],
            'pic'         =>  $_POST['preview'],
            'body'        =>  $_POST['body'],
            'ctime'       =>  time(),
            'stime'       =>  strtotime($_POST['stime']),
            'playtime'    =>  4, // 6.3后直播时长固定为4小时
            'state'       =>  $_POST['state'],
            'statetime'   =>  strtotime($_POST['statetime']),
            'playback'    =>  $_POST['playback'],
            'did'         =>  $_POST['did'],
            'hits_base'   =>  $_POST['hits_base'],
            'status'      =>  1,
            'isopen'      =>  $_POST['isopen']
        );
        
        $data  =  array(
            'post'   =>  $post,
            'id'     =>  intval($_POST['id'])
        );
        $xjhM   =  $this->MODEL('xjhlive');
        $return =  $xjhM->addXjh($data);
        $backurl = 'index.php?m=admin_xjhlive';
        if (intval($_POST['id'])) {// 修改页面才需要记住列表请求参数
            $backurl .= '&remember_prev=1';
        }
        $this->ACT_layer_msg($return['msg'], $return['errcode'], $backurl);
    }
    /**
     * 搜索按名称参会企业
     */
    function getZcom_action(){
        
        $result    =   array();

        if (!empty($_GET['keyword'])) {
            
            $where['r_status']     =   1;

            $where['name']  =   array('like', trim($_GET['keyword']));
        
            $where['limit']     =   10;
            
            $comM      =   $this->MODEL('company');
            
            $comArr    =   $comM -> getList($where, array('field' => '`uid`,`name`'));
            
            
            
            if (!empty($comArr)) {
                
                $comList   =   $comArr['list'];
                foreach ($comList as $k => $v){
                    $result[$k]['name']    =   $v['name'];
                    $result[$k]['value']   =   $v['uid'];
                }
            }
        }
        echo json_encode($result);die;
    }

    function com_action(){
        
        $xjhM  =  $this->MODEL('xjhlive');
        
        if($_GET['id']){
            
            $where['xid']   =   intval($_GET['id']);
            
            $urlarr['xid']  =   $_GET['id'];

            $info       =   $xjhM -> getInfo(array('id'=>$_GET['id']));
            $this -> yunset('info',$info);
        }
        
        if (trim($_GET['keyword'])){
            
            $companyM  =  $this->MODEL('company');
            $company   =  $companyM->getChCompanyList(array('name'=>array('like',trim($_GET['keyword']))),array('field'=>'uid'));
            
            if($company){
                
                foreach($company as $v){
                    
                    $uid[]  =   $v['uid'];
                }
                $where['uid']   =   array('in',pylode(',', $uid));
            }
            $urlarr["keyword"]  =   $_GET["keyword"];
        }

        $urlarr['c']     =  $_GET['c'];
		$urlarr          =   $_GET;
        $urlarr['page']  =  "{{page}}";
        
        $pageurl  =  Url($_GET['m'],$urlarr,'admin');
        $pageM    =  $this  -> MODEL('page');
        $pages    =  $pageM -> pageList('xjhlive_com',$where,$pageurl,$_GET['page']);

        if($pages['total'] > 0){                     
            
            $where['orderby']  =  array('id,desc');
            
            $where['limit']    =  $pages['limit'];
            
            $rows  =  $xjhM -> getXjhComList($where,array('utype'=>'admin'));
            
        }
        
        $this -> yunset("rows",$rows);
        
        $this -> yuntpl(array('admin/admin_xjhlive_com'));
    
    }
    function del_action(){
        
        if($_POST['del']){
            
            $delID   =  $_POST['del'];
            
        }elseif($_GET['xjhid']){
            
            $this->check_token();
            $delID   =  $_GET['xjhid'];
        }
        
        $xjhM  =  $this -> MODEL('xjhlive');
        
        $return   =  $xjhM -> delXjh($delID);
        
        $this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
    }
    function set_comaddsearch(){

        $ratingM    =   $this -> MODEL('rating');

        $rating     =   $ratingM -> getList(array( 'category' => '1', 'orderby' => 'sort' ), array('field'=>'`id`,`name`'));

        if(!empty($rating)){

            $ratingarr    =   array();

            foreach($rating  as  $k => $v){

                $ratingarr[$v['id']]   =   $v['name'];

            }
        }

        include(CONFIG_PATH.'db.data.php');

        $source             =   $arr_data['source'];

        $timeSection        =   array('1'=>'今天', '3'=>'3天内', '7'=>'7天内', '15'=>'半月内', '30'=>'1个月内', '31'=> '1-3个月', '32' => '3-6个月', '33' => '6个月-1年', '34' => '1年以上');

        $status             =   array('1'=>'已审核', '2'=>'已锁定', '3'=>'未通过', '4'=>'未审核','5'=>'已暂停');

        $edtime             =   array('1'=>'7天内', '2'=>'一个月内', '3'=>'半年内', '4'=>'一年内', '5'=>'已到期');

        $isrec              =   array('1'=>'是', '2'=>'否', '3'=>'已到期');

        $isgw               =   array('1'=>'已分配', '2'=>'未分配');

        $search_list        =   array();

        $search_list[]      =   array('param'=>'rating','name'=>'会员等级','value'=>$ratingarr);

        $search_list[]      =   array('param'=>'time','name'=>'到期时间','value'=>$edtime);

        $search_list[]      =   array('param'=>'status','name'=>'审核状态','value'=>$status);

        $search_list[]      =   array('param'=>'source','name'=>'数据来源','value'=>$source);

        $search_list[]      =   array('param'=>'rec','name'=>'知名企业','value'=>$isrec);

        $search_list[]      =   array('param'=>'gw','name'=>'企业顾问','value'=>$isgw);

        $search_list[]      =   array('param'=>'lotime','name'=>'最近登录','value'=>$timeSection);

        $search_list[]      =   array('param'=>'adtime','name'=>'最近注册','value'=>$timeSection);

        $this -> yunset(array('source' => $source, 'ratingarr' => $ratingarr, 'search_list' => $search_list));

    }
    /**
     * 添加参会企业
     */
    function comadd_action(){
        
        $xjhM  =  $this->MODEL('xjhlive');
        
        $xjh      =  $xjhM->getInfo(array('id'=>intval($_GET['id'])),array('field'=>'`id`,`name`'));
        
        $this -> yunset('info',$xjh);
        $ComM          =   $this -> MODEL('company');

        $where         =   array('r_status'=>1);

        $mwhere        =   array();

        $urlarr        =   $_GET;

        if($_GET['keyword']){

            $keywordStr	   =   trim($_GET['keyword']);

            $typeStr       =   intval($_GET['type']);

            if (!empty($keywordStr) && $typeStr == 1) {

                $where['PHPYUNBTWSTART_C']   = '';
                $where['name']               = array('like',$keywordStr);
                $where['shortname']          = array('like',$keywordStr,'OR');
                $where['PHPYUNBTWEND_C']     = '';

            }elseif (!empty($keywordStr) && $typeStr == 2){

                $mwhere['username']     =   array('like', $keywordStr);

            }else if (!empty($keywordStr) && $typeStr == 3) {

                $where['linkman']       =   array('like', $keywordStr);

            }else if (!empty($keywordStr) && $typeStr == 4) {

                $where['linktel']       =   array('like', $keywordStr);

            }else if (!empty($keywordStr) && $typeStr == 5) {

                $where['linkmail']      =   array('like', $keywordStr);

            }else  if (!empty($keywordStr) && $typeStr == 6){

                $where['uid'][]         =   array('=', $keywordStr);

            }

            $urlarr['type']			=	$typeStr;

            $urlarr['keyword']		=	$keywordStr;

        }

        if ($_GET['status']) {

            $status                 =   intval($_GET['status']);

            if($status == 4){
                $where['r_status']      =   0;
            }else if($status == 5){
                $where['r_status']      =   4;
            }else{
                $where['r_status']      =   $status;
            }

            $urlarr['r_status']     =   $status;

        }

        if ($_GET['adtime']) {

            $adtime    =   intval($_GET['adtime']);

            if($adtime == 1){

                $mwhere['reg_date']     =   array('>',  strtotime('today'));

            }else if ($adtime < 31){

                $mwhere['reg_date']  =   array('>',  strtotime('-'.$adtime.' day'));

            }else if ($adtime == 31){// 1 - 3 个月
                $mwhere['PHPYUNBTWSTART_C']    =   '';
                $mwhere['reg_date'][]  =   array('<',  strtotime('-1 month'));
                $mwhere['reg_date'][]  =   array('>=',  strtotime('-3 month'));
                $mwhere['PHPYUNBTWEND_C']      =   '';
            }else if ($adtime == 32){// 3 - 6个月
                $mwhere['PHPYUNBTWSTART_C']    =   '';
                $mwhere['reg_date'][]  =   array('<',  strtotime('-3 month'));
                $mwhere['reg_date'][]  =   array('>=',  strtotime('-6 month'));
                $mwhere['PHPYUNBTWEND_C']      =   '';
            }else if ($adtime == 33){// 6个月 - 1年
                $mwhere['PHPYUNBTWSTART_C']    =   '';
                $mwhere['reg_date'][]  =   array('<',  strtotime('-6 month'));
                $mwhere['reg_date'][]  =   array('>=',  strtotime('-12 month'));
                $mwhere['PHPYUNBTWEND_C']      =   '';
            }else if ($adtime == 34){// 1年以上
                $mwhere['reg_date']  =   array('<',  strtotime('-1 year'));
            }

            $urlarr['adtime']       =   $adtime;

        }
        if($_GET['lotime']){
            $lotime    =   intval($_GET['lotime']);
            if($lotime ==  1){
                $mwhere['login_date']  =   array('>',  strtotime('today'));
            }else if ($lotime < 31){
                $mwhere['login_date']  =   array('>',  strtotime('-'.$lotime.' day'));
            }else if ($lotime == 31){
                $mwhere['PHPYUNBTWSTART_C']    =   '';
                $mwhere['login_date'][]  =   array('<',  strtotime('-1 month'));
                $mwhere['login_date'][]  =   array('>=',  strtotime('-3 month'));
                $mwhere['PHPYUNBTWEND_C']      =   '';
            }else if ($lotime == 32){
                $mwhere['PHPYUNBTWSTART_C']    =   '';
                $mwhere['login_date'][]  =   array('<',  strtotime('-3 month'));
                $mwhere['login_date'][]  =   array('>=',  strtotime('-6 month'));
                $mwhere['PHPYUNBTWEND_C']      =   '';
            }else if ($lotime == 33){
                $mwhere['PHPYUNBTWSTART_C']    =   '';
                $mwhere['login_date'][]  =   array('<',  strtotime('-6 month'));
                $mwhere['login_date'][]  =   array('>=',  strtotime('-12 month'));
                $mwhere['PHPYUNBTWEND_C']      =   '';
            }else if ($lotime == 34){
                $mwhere['login_date']  =   array('<',  strtotime('-1 year'));
            }

            $urlarr['lotime']          =   $lotime;

        }

        if($_GET['source']){

            $mwhere['source']          =   $_GET['source'];

            $urlarr['source']          =   $_GET['source'];

        }

        $mUids		=	array();

        $UserinfoM	=	$this -> MODEL('userinfo');

        if(!empty($mwhere)){

            $uidList	=	$UserinfoM  ->  getList($mwhere, array('field' => '`uid`'));

            if(!empty($uidList)){

                foreach($uidList as $uv){

                    $mUids[]	=	$uv['uid'];

                }

            }else{

                $mUids			=	array(0);

            }

            $where['uid'][] =	array('in', pylode(',',$mUids));

        }

        if($_GET['rating']){

            $where['rating']   =   $_GET['rating'];

            $urlarr['rating']   =   $_GET['rating'];

        }
        $toDay	=	strtotime(date('Y-m-d'));
        if($_GET['time']){

            $time  =   intval($_GET['time']);

            if($time == 5){

                $where['PHPYUNBTWSTART_A']    =   '';

                $where['vipetime'][]         =   array('>', '0','AND');
                $where['vipetime'][]         =   array('<',  $toDay,'AND');

                $where['PHPYUNBTWEND_A']      =   '';

            }else{

                if($time == 1){

                    $num   =   '+7 day';

                }elseif($time == 2 ){

                    $num   =   '+1 month';

                }elseif($time == 3){

                    $num   =   '+6 month';

                }elseif($time == 4){

                    $num='+1 year';

                }

                $where['PHPYUNBTWSTART_B']    =   '';

                $where['vipetime'][]         =   array('>', time(),'AND');
                $where['vipetime'][]         =   array('<', strtotime($num),'AND');

                $where['PHPYUNBTWEND_B']      =   '';

            }

            $urlarr['time']    =   $time;

        }

        if($_GET['rec']){

            $rec    =   intval($_GET['rec']);

            if($rec == 1){

                $where['rec']       =   '1';

                $where['hottime']   =   array('>', time());

            }elseif ($rec == 2){

                $where['rec']       =   '0';

            }elseif ($rec == 3){

                $where['rec']       =   '1';

                $where['hottime']   =   array('<', time());

            }

            $urlarr['rec']          =   $rec;

        }

        if($_GET['gw']){

            if(intval($_GET['gw']) == 1){

                $where['crm_uid']     =   array('<>', '0');

            }else{

                $where['crm_uid']     =   '0';
            }

            $urlarr['gw']           =   $_GET['gw'];

        }

        $urlarr['page']	=	'{{page}}';

        $pageurl		=	Url($_GET['m'], $urlarr, 'admin');

        //提取分页
        $pageM			=	$this  -> MODEL('page');


        $pages			=	$pageM -> pageList('company', $where, $pageurl, $_GET['page']);

        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){

            //limit order 只有在列表查询时才需要
            if($_GET['order']){

                $where['orderby']		=	$_GET['t'].','.$_GET['order'];
                $urlarr['order']		=	$_GET['order'];
                $urlarr['t']			=	$_GET['t'];
            }else if($_GET['time'] == '5'){

                $where['orderby']		=	array('vipetime,desc','uid,desc');

            }else if($_GET['time']){

                $where['orderby']		=	array('vipetime,asc');

            }else if($_GET['lotime']){

                $where['orderby']		=	array('login_date,desc');

            }else{

                $where['orderby']		=	'uid,desc';
            }

            $where['limit']				=	$pages['limit'];

            $ListNew    =   $ComM -> getList($where,array('utype'=>'admin'));
            // 查询本场网招参会企业，在待添加企业列表中展示，企业是否已参加
            $netcom  = $xjhM->getXjhComList(array('xid'=>$_GET['id']),array('field'=>'uid'));
            if (!empty($netcom)){
                foreach ($netcom as $v){
                    $ncuid[] = $v['uid'];
                }
            }

            foreach ($ListNew['list']  as $key => $val){
                $ListNew['list'][$key]['wxBindmsg'] = $this->wxBindState($val);
                $ListNew['list'][$key]['join'] = 0;
                if (!empty($ncuid)){
                    if (in_array($val['uid'], $ncuid)){
                        $ListNew['list'][$key]['join'] = 1;
                    }
                }
            }

            unset($where['limit']);

            $this -> yunset(array('rows'=>$ListNew['list']));

        }
        $this -> set_comaddsearch();
        $this -> yuntpl(array('admin/admin_xjhlive_comadd'));
    }
    /**
     * 保存参会企业
     */
    function comaddsave_action(){
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->addXjhCom($_POST);

        echo json_encode($return);exit();
    }
    /**
     * 删除参会企业
     */
    function delcom_action(){
        
        $xjhM    =   $this->MODEL('xjhlive');
        
        if($_GET['delid']){
            
            $this -> check_token();
            
            $delID  =  intval($_GET['delid']);
            $xid    =  intval($_GET['xid']);
            
        }elseif($_POST['del']){
            
            $delID  =   $_POST['del'];
            $xid    =  intval($_POST['xid']);
        }
        
        $arr    =   $xjhM -> delXjhCom($delID, $xid);
        
        $this -> layer_msg($arr['msg'], $arr['errcode'], $arr['layertype'],$_SERVER['HTTP_REFERER']);
        
    }
    function xjhchat_action()
    {
        if (intval($_GET['uid']) > 0) {
            
            $where['uid']  = intval($_GET['uid']);
            
            $urlarr['uid'] = intval($_GET['uid']);
        }
        
        if (intval($_GET['xid']) > 0) {
            
            $where['xid']  = intval($_GET['xid']);
            
            $urlarr['xid'] = intval($_GET['xid']);
        }
        
        if ($_GET['keyword']) {
            
            $keyword = trim($_GET['keyword']);
            
            $type    = intval($_GET['type']);
            
            if ($type == '1') {
                
                $where['fuid'] = array('=',$keyword);
                
            } else if ($type == '2') {
                
                $userinfoM  =  $this->MODEL('userinfo');
                
                $member     =  $userinfoM->getList(array('username'=>array('like',$keyword)),array('field'=>'`uid`'));
                
                if (!empty($member)){
                    
                    foreach ($member as $v){
                        $fuid[]  =  $v['uid'];
                    }
                    $where['fuid']  =  array('in',pylode(',', $fuid));
                }
            }
            
            $urlarr['type']    = "" . $type . "";
            
            $urlarr['keyword'] = "" . $keyword . "";
        }
        
        $urlarr['c'] = 'xjhchat';
        $urlarr        	=   $_GET;
        $urlarr['page'] = "{{page}}";
        
        $pageurl = Url($_GET['m'], $urlarr, 'admin');
        
        // 提取分页
        $pageM = $this->MODEL('page');
        
        $pages = $pageM->pageList('xjhlive_chat', $where, $pageurl, $_GET['page']);
        
        // 分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            
            // limit order 只有在列表查询时才需要
            if ($_GET['order']) {
                
                $where['orderby']  =  $_GET['t'] . ',' . $_GET['order'];
                
                $urlarr['order']   =  $_GET['order'];
                
                $urlarr['t']       =  $_GET['t'];
            } else {
                
                $where['orderby'] = array('sendTime,desc');
            }
            
            $where['limit'] = $pages['limit'];
            
            $chatM = $this->MODEL('chat');
            
            $List = $chatM->getChatList($where,array('fdata'=>1,'utype'=>'admin'));
            
            $this->yunset(array('rows' => $List));
        }
        $this->yuntpl(array('admin/admin_xjhlive_chat'));
    }
    /**
     * 删除宣讲会聊天
     */
    function msgdel_action()
    {
        $this->check_token();

        $chatM  =   $this -> Model('chat');

        if($_GET['del']){
            $del=$_GET['del'];

            if($_GET['del']){
                $return =   $chatM -> delXjhchat($_GET['del']);
                
                $this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
            }else{
                $this->layer_msg("请选择您要删除的信息！",8,1,$_SERVER['HTTP_REFERER']);
            }
        }
    }
    /**
     * 禁言
     */
	function forbidden_action()
    {
        $this->check_token();
		
		if($_GET['del']){
			
			$chatM  =  	$this->MODEL('chat');
			$row	=	$chatM->getXjhChat(array('id'=>$_GET['del']));

			$data	=	array(
                'fuid' 	    =>	$row['fuid'],
			    'usertype'  =>  $row['fusertype'],
				'xjhid'	    =>	$row['xid']
			);
			
			$return  =  $chatM->addXjhchatBlack($data);
			
			$this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg("请选择您要禁言的用户！",8,0,$_SERVER['HTTP_REFERER']);
		}
    }
	/**
	 * 解除禁言
	 */
	function delblack_action()
    {
        $this->check_token();
		
		if($_GET['del']){
			
			$chatM  =  	$this->MODEL('chat');
			
			$xjhM  =  	$this->MODEL('xjhlive');
			
			$row	=	$chatM->getXjhChat(array('id'=>$_GET['del']));
			
			$where	=	array(
			    'xid'	    =>	$row['xid'],
				'uid' 	    =>	$row['fuid'],
			    'usertype'  =>  $row['fusertype']
			);
			
			$return  =  $chatM->delXjhchatBlack('',array('where'=>$where));
			
			$this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg("请选择您要解禁的用户！",8,0,$_SERVER['HTTP_REFERER']);
		}
    }
    /**
     * 删除封面图片
     */
	function delPic_action(){
        
	    $delID   =  $_POST['pid'];
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->delXjhPic($delID,array('utype'=>'admin'));
        
        echo $return['errcode'] == 9 ? 1 :2;
    }
    /**
     * 获取直播二维码地址
     */
    function getLive_action(){
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->getLiveQrcode(array('id'=>$_POST['id']));
        
        echo json_encode($return);
    }
    /**
     * 开始宣讲会直播
     */
    function startEnd_action(){
        
        $xjhM    =  $this->MODEL('xjhlive');
        $xjh     =  $xjhM->getInfo(array('id'=>intval($_POST['id'])),array('field'=>'`id`,`livestatus`'));
        
        if (!empty($xjh)){
            
            $return  =  $xjhM->liveStart(array('id'=>$xjh['id']));
            
            $this->layer_msg($return['msg'],$return['errcode']);
            
        }else{
            
            $this->layer_msg("宣讲会不存在",8,0);
        }
    }
	/**
	 * 关闭宣讲会直播
	 */
    function liveEnd_action(){
        
        $xjhM    =  $this->MODEL('xjhlive');
        $xjh     =  $xjhM->getInfo(array('id'=>intval($_POST['id'])),array('field'=>'`id`,`livestatus`'));
        
        if (!empty($xjh)){
            
            $return  =  $xjhM->liveEnd(array('id'=>$xjh['id']));
            
            $this->layer_msg($return['msg'],$return['errcode']);
            
        }else{
            
            $this->layer_msg("宣讲会不存在",8,0);
        }
    }
    /**
     * 导播服务
     */
    function caster_action(){
        
        $xid     =  $_GET['id'];
        $xjhM    =  $this->MODEL('xjhlive');
        // 查询导播播放地址
        $xjh     =  $xjhM->getInfo(array('id'=>$xid));
        // 查询导播台素材
        $list	=	$xjhM->getMaterials(array('xid'=>$xid, 'orderby'=>'id,asc'));
        
        $this->yunset(array('xjh'=>$xjh,'pgmurl'=>$xjh['hls'],'list'=>$list));
        
        $this->yuntpl(array('admin/admin_xjhlive_caster'));
    }
    /**
     * 导播素材
     */
    function material_action(){
        
        $xjhM  =  $this->MODEL('xjhlive');
        $list  =  $xjhM->getMaterials(array('xid'=>$_GET['id'], 'orderby'=>'id'));
        
        $this->yunset('list', $list);
        
        $this->yuntpl(array('admin/admin_xjhlive_material'));
    }
    function addMaterial_action(){
        
        $xjhM  =  $this->MODEL('xjhlive');
        $row   =  $xjhM->getMaterial(array('id'=>$_GET['mid']));
        $this->yunset('row', $row);
        
        $this->yuntpl(array('admin/admin_xjhlive_material_add'));
    }
    function saveMaterial_action(){
        
        $_POST  =  $this->post_trim($_POST);
        
        $xid   =  $_POST['xid'];
        $xjhM  =  $this->MODEL('xjhlive');
        $list  =  $xjhM->getMaterials(array('xid'=>$xid));
        
        if (count($list) >= 8){
            
            $this->ACT_layer_msg('单个导播台最多可上传8份素材', 8, 'index.php?m=admin_xjhlive&c=material&id='. $xid);
        }
        $arr    =  array(
            'xid'     =>  $xid,
            'name'    =>  $_POST['name'],
            'ctime'   =>  time()
        );
        
        if (!empty($_FILES['file']['tmp_name'])){
            
            $result  =  $xjhM->uploadMaterial($_FILES['file'], $arr);
            
            if (!empty($result['msg'])){
                
                $this->ACT_layer_msg($result['msg'], 8, $_SERVER['HTTP_REFERER']);
            }
            
            $arr['url']     =  $result['url'];
            $arr['syncid']  =  $result['id'];
        }else{
            if (empty($_POST['id'])){
                
                $this->ACT_layer_msg('请上传文件', 8);
            }
        }
        
        if (!empty($_POST['id'])){
            
            $xjhM->upMaterial(array('id'=>$_POST['id']),$arr);
            
        }else{
            
            $xjhM->addMaterial($arr);
        }
        
        $this->ACT_layer_msg('上传成功', 9, 'index.php?m=admin_xjhlive&c=material&id='. $xid);
    }
    /**
     * 设置暖场视频
     */
    function setVideo_action(){
        
        $data  =  array(
            'state'   =>  $_POST['state']
        );
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->setCasterVideo(array('id'=>$_POST['id'],'xid'=>$_POST['xid']),$data);
        
        $this->layer_msg($return['msg'], $return['errcode']);
    }
    /**
     * 创建导播台
     */
    function createCaster_action(){
        
        $data  =  array(
            'xid'   =>  $_POST['id']
        );
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->createCaster($data);
        
        $this->layer_msg($return['msg'], $return['errcode']);
    }
    /**
     * 开启导播台
     */
    function openCaster_action(){
        
        $data  =  array(
            'xid'   =>  $_POST['id']
        );
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->openCaster($data);
        
        $this->layer_msg($return['msg'], $return['errcode']);
    }
    /**
     * 导播台切换监视器画面，并切换布局
     */
    function upMonitor_action(){
        
        $data  =  array(
            'xid'     =>  $_POST['xid'],
            'mid'     =>  $_POST['mid'],
            'layout'  =>  $_POST['layout']
        );
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->upMonitor($data);
        
        $this->layer_msg($return['msg'], $return['errcode']);
    }
    /**
     * 导播台把画面切换为正常直播画面
     */
    function liveCaster_action(){
        
        $data  =  array(
            'xid'     =>  $_POST['xid']
        );
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->liveCaster($data);
        
        $this->layer_msg($return['msg'], $return['errcode']);
    }
    /**
     * 查询是否可以创建宣讲会
     */
    function canAdd_action(){
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->xjhCanAdd();
        
        echo json_encode($return);die;
    }
    // 查询推流地址
    function getPushurl_action(){
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->getPushUrl(array('id'=>$_POST['id']));
        
        echo json_encode($return);die;
    }
    // 直播控制台
    function control_action(){
        
        $xjhM  =  $this->MODEL('xjhlive');
        $info  =  $xjhM -> getInfo(array('id'=>intval($_GET['id'])),array('utype'=>'admin'));
        $this -> yunset('info',$info);
        
        $this->yuntpl(array('admin/admin_xjhlive_control'));
    }
    // 控制台查询企业列表
    function getControlData_action(){
        
        $xjhM  =  $this->MODEL('xjhlive');
        $rows  =  $xjhM -> getXjhComList(array('xid'=>$_POST['xid'],'orderby'=>array('`status`,ASC','`ctime`,ASC')),array('utype'=>'admin'));
        
        $chatM =  $this -> MODEL('chat');
        $black =  $chatM->getXjhchatBlackList(array('xid'=>$_POST['xid']));
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
            'jylist' => !empty($black) ? $black : array(),
            'explain' => isset($explain) ? $explain: array()
        );
        
        echo json_encode($res);
    }
    // 控制台查询企业列表
    function getControlCom_action(){
        
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
        
        $xjhM  =  $this->MODEL('xjhlive');
        $rows  =  $xjhM -> getXjhComList(array('xid'=>$_POST['xid'],'orderby'=>array('`status`,ASC')),array('utype'=>'admin','keyword'=>$keyword));
        
        echo json_encode($rows);
    }
    // 控制台设置讲解企业
    function setExplain_action(){
        
        $res   =  array();
        $xjhM  =  $this->MODEL('xjhlive');
        $row   =  $xjhM->getInfo(array('id' => $_POST['xid']));
        if ($row['livestatus'] == 2){
            $this->layer_msg('直播已结束，无法设置', 8);
        }
        // 先将正在讲解中的结束        
        $xjhM->upXjhCom(array('xid'=>$_POST['xid'], 'status'=>1),array('status'=>3, 'etime'=>time()));
        // 设置新的讲解企业
        $nid   =  $xjhM->upXjhCom(array('id'=>$_POST['id']),array('status'=>1, 'stime'=>time()));
        
        $this->layer_msg('直播(ID:'.$_POST['id'].')设置讲解企业(ID:'.$_POST['id'].')成功', 9);
    }
    // 控制台查看聊天记录
    function getXjhChatPage_action()
    {
        $arr    =  array(
            'xid'      =>  $_POST['xid'],
            'orderby'    =>  '`id`,DESC',
        );
        $page               =   $_POST['page'];
        $limit              =   30;
        if($page){//分页
            $pagenav        =   ($page-1)*$limit;
            $arr['limit']   =   array($pagenav,$limit);
        }else{
            $arr['limit']   =   $limit;
        }
        
        $chatM              =  $this -> MODEL('chat');
        $return             =  $chatM->getChatList($arr,array('fdata'=>true,'utype'=>'admin'));
        $list               =  array();
        $jobids             =  array();
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
        $chatnum   =  $chatM->getChatNum(array('xid'=>$arr['xid']));
        $ismore    =  ceil($chatnum/$limit) > $page ? true : false;
        
        $result  =  array(
            'list'    =>  $list,
            'ismore'  =>  $ismore
        );
        
        echo  json_encode($result);die;
    }
    // 未登录用户获取聊天登录参数
    function getUnloginToken_action(){
        
        // 所有管理员只用一个账号
        $chatM   =  $this->MODEL('chat');
        $member = $chatM->getMember(array('usertype'=>9,'orderby'=>'id,ASC'));
        
        if (!empty($member)){
            $auid = $member['uid'];
        }else{
            $auid = $_SESSION['auid'];
        }
        
        $csM   =  $this->MODEL('chatcs');
        $data  =  $csM->getUnloginToken('admin', array('auid'=>$auid));
        
        echo json_encode($data);
    }
    // 保存宣讲会聊天记录
    function chatlog_action(){
        
        // 保存聊天记录
        $arr  =  array(
            'fuid'       =>  $_SESSION['auid'],
            'fusertype'  =>  9,
            'ip'         =>  fun_ip_get(),
            'content'    =>  $_POST['content'],
            'timestamp'  =>  $_POST['timestamp'],
            'msgtype'    =>  $_POST['msgtype'],
            'xjhid'      =>  $_POST['xjhid']
        );
        $chatM =  $this->MODEL('chat');
        $chatM->xhjChat($arr);
        
        echo json_encode(array('error'=>0));
    }
    // 设置禁言
    function setNochat_action(){
        
        $id  = intval($_POST['xid']);
        $st  = 9;
        $msg = '直播(ID: '.$id.')';
        if (isset($_POST['nochat'])){
            $data['nochat'] = $_POST['nochat'];
            
            if ($_POST['nochat'] == 1){
                $msg .= '设置全部禁言';
            }else{
                $msg .= '解除全部禁言';
            }
        }elseif (isset($_POST['vistor_nochat'])){
            $data['vistor_nochat'] = $_POST['vistor_nochat'];
            
            if ($_POST['vistor_nochat'] == 1){
                $msg .= '设置游客禁言';
            }else{
                $msg .= '解除游客禁言';
            }
        }
        if (isset($data)){
            $xjhM  =  $this->MODEL('xjhlive');
            $nid   =  $xjhM->upXjh(array('id'=>$id), $data);
            if (!$nid){
                $st = 8;
            }
        }
        $this->layer_msg($msg, $st);
    }
    // 控制台设置禁言
    function setjy_action(){
        
        $chatM  =  	$this->MODEL('chat');
        $row	=	$chatM->getXjhChat(array('id'=>$_POST['chatid']));
        
        $data	=	array(
            'fuid' 	    =>	$row['fuid'],
            'usertype'  =>  $row['fusertype'],
            'xjhid'	    =>	$row['xid']
        );
        
        $return  =  $chatM->addXjhchatBlack($data);
        
        $this->layer_msg($return['msg'],$return['errcode']);
    }
    // 控制台取消禁言
    function removejy_action(){
        
        $chatM  =  	$this->MODEL('chat');
        
        $where	=	array(
            'uid' 	    =>	$_POST['uid'],
            'usertype'  =>  $_POST['usertype'],
            'xid'	    =>	$_POST['xid']
        );
        
        $return  =  $chatM->delXjhchatBlack(null, array('where'=>$where));
        
        $this->layer_msg($return['msg'],$return['errcode']);
    }
    // 控制台统计数据
    function getTj_action(){
        
        $id	   =  intval($_POST['xid']);
        $xjhM  =  $this->MODEL('xjhlive');
        $row   =  $xjhM->getInfo(array('id' => $id));
        
        $duration = 0;
        $regwhere = array();
        $sqwhere  = array();
        if ($row['livestatus'] == 3){
            // 直播中
            $duration = ceil((time()-$row['stime'])/60);
            $regwhere['reg_date'][]  =  array('>=', $row['stime']); 
            $regwhere['reg_date'][]  =  array('<=', time()); 
            
            $sqwhere['datetime'][] = array('>=', $row['stime']); 
            $sqwhere['datetime'][] = array('<=', time());
            
        }elseif ($row['livestatus'] == 2){
            // 直播结束
            if (!empty($row['etime'])){
                // 主动点了结束直播(单位：分钟)
                $duration = ceil(($row['etime']-$row['stime'])/60);
            }else{
                // 没有结束时间，按设置的直播时长计算(单位：分钟)
                $duration = $row['playtime'] * 60;
                
                $row['etime'] = $row['stime'] + $row['playtime'] * 3600;
            }
            $regwhere['reg_date'][]  =  array('>=', $row['stime']);
            $regwhere['reg_date'][]  =  array('<=', $row['etime']);
            
            $sqwhere['datetime'][] = array('>=', $row['stime']);
            $sqwhere['datetime'][] = array('<=', $row['etime']);
        }
        // 观看记录数量
        $num = $xjhM->getUserlogNum(array('xid'=>$id));
        // 入场记录
        $log = $xjhM->getUserlogList(array('xid'=>$id, 'orderby'=>'ctime','limit'=>10));
        // 注册人数
        if (!empty($regwhere)){
            $userinfoM = $this->MODEL('userinfo');
            $regnum    = $userinfoM->getMemberNum($regwhere);
        }
        // 投递简历数
        if (!empty($sqwhere)){
            $jobM  = $this->MODEL('job');
            $sqnum = $jobM->getSqJobNum($sqwhere);
        }
        
        $res = array(
            'pv'        => $row['hits'],
            'uv'        => $num,
            'onlinenum' => $row['onlinenum'],
            'duration'  => $duration,
            'regnum'    => isset($regnum) ? $regnum : 0,
            'sqnum'     => isset($sqnum) ? $sqnum : 0,
            'log'       => $log
        );

        echo json_encode($res);exit();
    }
    // 趋势数据
    function tendency_action(){
        
        $id	   =  intval($_POST['xid']);
        $xjhM  =  $this->MODEL('xjhlive');
        
        $logStats = $xjhM->userlogTj($id);
        
        echo json_encode($logStats);exit();
    }
    // 开始直播
    function startlive_action(){
        
        $xjhM    =  $this->MODEL('xjhlive');
        $return  =  $xjhM->liveStart(array('id'=>$_POST['id']));
        
        $this->layer_msg($return['msg'],$return['errcode']);
    }
    //入场记录
    function userlog_action(){

        $id   = intval($_GET['xid']);
        $where['xid'] = $id;

        $urlarr        	=   $_GET;

        $urlarr['page']	=	"{{page}}";

        $pageurl=Url($_GET['m'],$urlarr,'admin');

        $pageM	=	$this  -> MODEL('page');

        $pages	=	$pageM -> pageList('xjhlive_user',$where,$pageurl,$_GET['page']);

        if ($pages['total'] > 0) {

            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];

            } else {

                $where['orderby']   =   array('id,desc');

            }

            $where['limit'] = $pages['limit'];
            $where['xid'] = $id;
            $xjhM = $this->MODEL('xjhlive');
            $List = $xjhM->getUserlogList($where);

            $this->yunset('List', $List);
        }
        $this->yuntpl(array('admin/admin_xjhlive_userlog'));
    }
    // 生成回放
    function playback_action(){
        
        $sql = array(
            'stime' => strtotime($_POST['stime']),
            'etime' => strtotime($_POST['etime'])
        );
        
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->liveRecord(array('id'=>$_POST['id']), $sql);
        
        $this->layer_msg($return['msg'],$return['errcode']);
    }
}

?>