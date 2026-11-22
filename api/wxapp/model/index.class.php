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
class index_controller extends wxapp_controller{
    /**
     * 首页
     */
    function index_action()
    {
        $time       = time();
        $firstHome  = $_POST['firstHome'];
        $data       = array();
        $navurl     = array();
        $navsort    = array();
        $gy         = array();

        if ($firstHome == 1){
            //首页自定义设置
            include(DATA_PATH.'api/wxapp/tplapp.cache.php');
            include(DATA_PATH.'api/wxapp/tplappmodel.cache.php');

            foreach($tplappmodel as $uk=>$uv){
                $navurl[$uv['id']] = $uv['url'];
            }
            //导航
            include(CONFIG_PATH.'db.data.php');
            $spview_web   =  isset($arr_data['modelconfig']['spview']) && isset($this->config['sy_spview_web']) ? $this->config['sy_spview_web'] : 2;
            $skill_web    =  isset($arr_data['modelconfig']['skill']) && isset($this->config['sy_skill_web']) ? $this->config['sy_skill_web'] : 2;
            if ($this->platform == 'ios' && $this->plat == 'app'){
                // IOS APP 不支持视频面试
                $spview_web = 2;
            }
            $xjhlive_web  =  isset($arr_data['modelconfig']['xjhlive']) && isset($this->config['sy_xjhlive_web']) ? $this->config['sy_xjhlive_web'] : 2;
            // 是否是小程序端
            if ($this->plat == 'mini'){
                $is_mini = true;
            }else{
                $is_mini = false;
            }
            foreach ($nav as $nk => $nv) {
                if (($nv['url']=='14'||$nv['url']=='8') && $this->config['sy_iospay'] == 2){
                    unset($nav[$nk]);
                }elseif (($nv['url']=='8' || $nv['url']=='14') && $is_mini){
                    // 小程序内屏蔽赏金职位、红包职位
                    unset($nav[$nk]);
                }elseif ($nv['url']=='19' && $xjhlive_web == 2){
                    // 直播宣讲会模块未开启，需屏蔽。百度小程序、头条小程序不支持观看直播(6.3.2版本开始，放开限制)
                    unset($nav[$nk]);
                }elseif ($nv['url']=='20' && ($spview_web == 2 || in_array($_POST['provider'], array('baidu','toutiao','alipay')))){
                    // 视频面试模块未开启，需屏蔽。百度小程序、头条小程序、支付宝小程序不支持视频面试
                    unset($nav[$nk]);
                }elseif ($nv['url']=='23' && ($skill_web == 2 || in_array($_POST['provider'], array('baidu','toutiao','alipay')))){
                    // 技能提升模块未开启，需屏蔽。百度、头条、支付宝小程序不支持在线课程售卖
                    unset($nav[$nk]);
                }elseif ($nv['url']=='17' && (($_POST['provider'] == 'app' && $this->config['sy_ioslocation']==2) || $_POST['provider'] == 'toutiao')){
                    //附近职位模块，需屏蔽。 1、APP关闭定位服务功能，2、抖音小程序
                    unset($nav[$nk]);
                }elseif (!in_array($_POST['provider'], array('weixin','app')) && $nv['navtype'] == 1){
                    // 不是微信小程序、APP，并且类型是打开小程序时删除导航
                    unset($nav[$nk]);
                } elseif ($nv['url']=='24' && $_POST['provider'] == 'app') {
                    // app屏蔽求职助手
                    unset($nav[$nk]);
                }else{
                    if($nv['display']!=1){
                        unset($nav[$nk]);
                    }else{

                        $nav[$nk]['pic']     =  checkpic($nv['pic']);

                        if($nv['navtype']==1 && $nv['url'] && $nv['nav_appid'] && in_array($_POST['provider'], array('weixin', 'app'))){
                            $nav[$nk]['nav_appid']   =  $nv['nav_appid'];
                            $nav[$nk]['nav_ysid']    =  isset($nv['nav_ysid']) ? $nv['nav_ysid'] : '';
                            $navsort[]  =  $nv['navsort'];
                        }else if(!empty($navurl[$nv['url']])){
                            $nav[$nk]['url']     =  $navurl[$nv['url']];
                            $navsort[]  =  $nv['navsort'];
                        }else{
                            unset($nav[$nk]);
                        }
                    }
                }
            }
            // 高级服务，金刚位显示5个导航，普通的是4个
            // $navnum = $data['videoWeb'] == 2 ? 4 : 5;
            $navnum = 5; // 后台DIY已经改成5个
            array_multisort($navsort,SORT_ASC,SORT_NUMERIC,$nav);
            $navarr			 =	array_chunk($nav,$navnum);
            $data['navArr']	 =	$navarr;
            if (!empty($navarr)){
                $tplapp['nav']  =  '1';
            }else{
                $tplapp['navshow']  =  2; // 此参数是新加的，配合后台导航组件显示和隐藏
                $tplapp['nav']  =  '2';
            }
            $tplapp['limit']     =  isset($tplapp['jobnum']) ? $tplapp['jobnum'] : 10;
            // 公告图片数据处理
            if (!empty($tplapp['noticestyleimg'])) {
                $tplapp['noticestyleimg'] = explode(',', $tplapp['noticestyleimg']);
                array_walk($tplapp['noticestyleimg'], function (&$value, $key) {
                    $value = strpos($value, 'upload') ? checkpic($value) : getAdminPath($value); // 上传过的数据特殊处理
                });
            } else {
                $tplapp['noticestyleimg'] = array(
                    getAdminPath('images/appdiy_gg.png'),
                    getAdminPath('images/appdiy_gg2.png'),
                    getAdminPath('images/appdiy_gg3.png')
                ); // 默认图片
            }
            if (!empty($tplapp['jobsort'])) {
                $tplapp['jobsort'] = explode(',', $tplapp['jobsort']);
            } else {
                $tplapp['jobsort'] = array('newjob', 'urgjob', 'recjob', 'mapjob'); // 默认职位排序
            }
            // 登录/注册图标
            $tplapp['login_icon1'] = !empty($tplapp['login_icon1']) && strpos($tplapp['login_icon1'], 'upload') ? checkpic($tplapp['login_icon1']) : getAdminPath('images/appdiy_login1.png');
            $tplapp['login_icon2'] = !empty($tplapp['login_icon2']) && strpos($tplapp['login_icon2'], 'upload') ? checkpic($tplapp['login_icon2']) : getAdminPath('images/appdiy_login2.png');
            // 客服图标
            $tplapp['kficon'] = !empty($tplapp['kficon']) && strpos($tplapp['kficon'], 'upload') ? checkpic($tplapp['kficon']) : getAdminPath('images/appdiy_kefu.png');
            $data['tplapp']	 	 =	$tplapp;

            $data['diysort']	 =	@explode(',',$tplapp['sort']);

            $data['contact'] = 0;
            if ($tplapp['cshow'] == '1'){
                $data['contact'] = 1;
            }
            //首页自定义关于我们等
            if ($tplapp['private'] == '1'){
                $gy[] = array('name'=>$tplappgy[1]['name'],'url'=>$tplappgy[1]['url']);
            }
            if ($tplapp['advice'] == '1'){
                $gy[] = array('name'=>$tplappgy[2]['name'],'url'=>$tplappgy[2]['url']);
            }
            if ($tplapp['advice'] == '1'){
                $gy[] = array('name'=>$tplappgy[3]['name'],'url'=>$tplappgy[3]['url']);
            }
            $syszz = array('sy_webcopyright'=>$this->config['sy_webcopyright'],'sy_webrecord'=>$this->config['sy_webrecord'],'sy_websecord'=>$this->config['sy_websecord'],'sy_hrlicense'=>$this->config['sy_hrlicense'],'sy_perfor'=>$this->config['sy_perfor']);
            $data['gy']  =  $gy;
            $data['syszz'] = $syszz;
            $data['webname']  =  $this->config['sy_webname'];
            if (!empty($this->config['sy_wapdomain'])){
                $data['wapurl']  =  $this->config['sy_wapdomain'];
            }
            $data['joblast'] = $this->isJobShowLast($tplapp);
        }
        $data['sy_web_site'] = $this->config['sy_web_site'];
        $data['sy_gotocity'] = $this->config['sy_gotocity'];
        // 默认分站
        $data['indexcity']  =  $this->config['sy_web_site'] == 1 ? $this->config['sy_indexcity'] : '';
        $data['applocation']  =  $this->config['sy_ioslocation'];
	    // 小程序用seo
        if ($this->plat == 'mini'){
            $seo            =  $this->seo('index','','','',false, true);
            $data['seo']    =  $seo;
	    }
        $this->render_json(0,'ok',$data);
    }

    // 首页其他内容处理
    function homeOther_action()
    {
        include(DATA_PATH.'api/wxapp/tplapp.cache.php');
        // 处理分站查询条件
        if (!empty($_POST['did'])){

            $domain  =  $this->getDomain($_POST['did']);
        }
        include_once PLUS_PATH.'pimg_cache.php';
        $time  =  time();
        // 广告
        if($tplapp['membershow']=='1'){
            $adid   = $tplapp['hdid'] ? $tplapp['hdid'] : 50;
            $adpics = $this->ad_wxapp(array('class_id'=>$adid), $ad_label);
            $data['ad']  =  $adpics;
        }
        // 名企下方广告
        if($tplapp['adshow']=='1'){
            $classId = !empty($tplapp['adhdid']) ? $tplapp['adhdid'] : 503;
            $banner  = $this->ad_wxapp(array('class_id'=>$classId), $ad_label);
            $classId2 = !empty($tplapp['adhdid2']) ? $tplapp['adhdid2'] : 600;
            $banner2 =$this->ad_wxapp(array('class_id'=>$classId2), $ad_label);
            $classId3 = !empty($tplapp['adhdid3']) ? $tplapp['adhdid3'] : 601;
            $banner3 =$this->ad_wxapp(array('class_id'=>$classId3), $ad_label);
            $data['adBanner']  =  $banner;
            $data['adBanner2']  =  $banner2;
            $data['adBanner3']  =  $banner3;
        }

        // 小程序首页弹出广告
        $ejectBanner = $this->ad_wxapp(array('name'=>'popup_ad'), $ad_label, 1);
        $data['ejectBanner']  =  $ejectBanner;

        // 搜索关键字
        $key_name  = array();
        if ($tplapp['searchshow'] == 1){

            $hotKeyM  =  $this->MODEL('hotkey');
            $rows = $hotKeyM->getList(array('type'=>3,'limit'=>20,'orderby'=>'num'),array('field'=>'`key_name`'));

            foreach($rows as $k=>$v) {
                $key_name[]  =  $v['key_name'];
            }
        }
        $data['hotkey'] = $key_name;
        // 公告
        $ggList  = array();
        if ($tplapp['notice'] == 1){

            $ggM    =  $this->MODEL('announcement');

            if (!empty($_POST['did'])){


				$gwhere['PHPYUNBTWSTART']  =  '';
				$gwhere['did'][]             =  array('=',$_POST['did'],'OR');
				$gwhere['did'][]           =  array('=','-1','OR');
				$gwhere['PHPYUNBTWEND']    =  '';

            }
            //公告开始时间
            $gwhere['PHPYUNBTWSTART_A'] = array();
            $gwhere['startime'][]  =  array('<=',$time,'OR');
            $gwhere['startime'][]  =  array('=',0,'OR');
            $gwhere['startime'][]  =  array('isnull','','OR');
            $gwhere['PHPYUNBTWEND_A'] = array();

            $gwhere['PHPYUNBTWSTART_B'] = array();
            $gwhere['endtime'][]  =  array('>',$time,'AND');
            $gwhere['endtime'][]  =  array('=',0,'OR');
            $gwhere['endtime'][]  =  array('isnull','','OR');
            $gwhere['PHPYUNBTWEND_B'] = array();

            $gwhere['limit']    =  5;
            $gwhere['orderby']  =  'startime,desc';

            $gg  =  $ggM->getList($gwhere,array('field'=>'`id`,`title`,`startime`,`datetime`','utype'=>'wxapp'));

            if (!empty($gg['list'])){
                $ggList =   $gg['list'];
            }
        }
        $data['ggList'] =   $ggList;
        //首页名企
        if ($tplapp['hotcom'] == '1') {

            $companyM   =   $this->MODEL('company');

            if (!empty($_POST['did'])) {

                if (isset($domain['provinceid']) && !empty($domain['provinceid'])) {
                    $hotcomwhere['provinceid']  =   $domain['provinceid'];
                }
                if (isset($domain['cityid']) && !empty($domain['cityid'])) {
                    $hotcomwhere['cityid']      =   $domain['cityid'];
                }
                if (isset($domain['three_cityid']) && !empty($domain['three_cityid'])) {
                    $hotcomwhere['three_cityid']=   $domain['three_cityid'];
                }
                if (isset($domain['hyclass']) && !empty($domain['hyclass'])) {
                    $hotcomwhere['hy']          =   $domain['hyclass'];
                }
            }

            $hotcomwhere['hottime']     =   array('>', $time);
            $hotcomwhere['r_status']    =   1;

            $hcom                       =   $companyM->getChCompanyList($hotcomwhere, array('field' => '`uid`,`name`,`shortname`'));

            if (!empty($hcom)) {
                foreach ($hcom as $v) {
                    $hcuid[]    =   $v['uid'];
                }
                $hcwhere['uid'] =   array('in', pylode(',', $hcuid));
                $hcwhere['time_start']  =   array('<', $time);
                $hcwhere['time_end']    =   array('>', $time);
                $hcwhere['limit']       =   $tplapp['hotcomnum'] ? $tplapp['hotcomnum'] : 12;
                if($this->config['hotcom_top'] == 1){
                    // 职位更新时间(职位修改时，会更新名企表lastupdate字段)
                    $hcwhere['orderby']  =  'lastupdate,DESC';
                }elseif($this->config['hotcom_top'] == 2){
                    // 随机
                    $hcwhere['orderby']  =  'rand()';
                }else{
                    // 后台手动设置
                    $hcwhere['orderby']  =  'sort';
                };
                //  查询基础条件是通过$hcom，传过去直接复用
                $hotcom = $companyM->getHotJobList($hcwhere, array('utype' => 'wxapp', 'field' => '`uid`,`hot_pic`','hcom'=>$hcom));
                if (!empty($hotcom) && isset($_POST['hotcom'])){
                    if (empty($tplapp['hotcomstyle']) || $tplapp['hotcomstyle'] == 1) {
                        $hotcom = array_chunk($hotcom, 4);
                    }
                }
            }else{
                $hotcom = array();
            }
        }
        $data['comlist']    =  count($hotcom) > 0 ? $hotcom : array();
        //热门职位
        if ($tplapp['hotjob'] == '1'){
            $categoryM	=	$this -> MODEL('category');
            $hotjobclass = $categoryM->getHotJobClass(array('rec'=>1,'limit'=>$tplapp['hotjobnum'],'orderby'=>'sort'),'`id`,`keyid`,`name`');
        }
        $data['hotjobclass'] = count($hotjobclass) > 0 ? $hotjobclass : array();
        //首页自定义招聘会
        if($tplapp['zphshow']=='1'){

            $zphM   =  $this->MODEL('zph');
            $zphone =  $zphM->getInfo(array('unix_timestamp(`endtime`)'=>array('>',$time)),array('field'=>'`id`'));

            $field  =  '`id`,`title`,`starttime`,`endtime`,`address`,`is_themb`,`is_themb_wap`';

            $zwhere =   array();

            if (!empty($zphone)){
                $field  .=  ',CASE WHEN unix_timestamp(`endtime`)>'.$time.' THEN unix_timestamp(`starttime`)';
                $field  .=  ' WHEN unix_timestamp(`endtime`)<'.$time.' THEN -1*unix_timestamp(`starttime`) END AS `zph_px`';

                $zwhere['orderby'] .=  ' CASE WHEN unix_timestamp(`starttime`)<"'.$time.'" AND unix_timestamp(`endtime`)>"'.$time.'" THEN 0';
                $zwhere['orderby'] .=  ' WHEN unix_timestamp(`starttime`)>"'.$time.'" THEN 1';
                $zwhere['orderby'] .=  ' WHEN unix_timestamp(`endtime`)<"'.$time.'" THEN 2';
                $zwhere['orderby'] .=  ' END,`zph_px` ASC';
            }else{

                $zwhere['orderby'] .=  'unix_timestamp(`starttime`)';
            }
            if (!empty($_POST['did'])){
                $zwhere['did']  =  $_POST['did'];
            }
            $zwhere['is_open'] = 1;
            $zwhere['limit'] 	=  $tplapp['zphnum'] ? $tplapp['zphnum']:5;

            $zphrows        =  $zphM -> getList($zwhere, array('field'=>$field));
        }
        $data['zphlist']    =  count($zphrows)>0 ? $zphrows : array();
        //首页自定义资讯
        if($tplapp['articleshow']=='1'){

            if ($tplapp['articletype']=='1' || $tplapp['articletype']=='2') {//app首页自定义-咨询类型如果是图文则只查询有图的
                $awhere['newsphoto']   =  array('<>','');
            }

            if (!empty($_POST['did'])){
				$awhere['PHPYUNBTWSTART']  =  '';
				$awhere['did'][]           =  array('=',$_POST['did'],'OR');
				$awhere['did'][]           =  array('=','-1','OR');
				$awhere['PHPYUNBTWEND']    =  '';

            }
            //新闻资讯开始时间
            $awhere['PHPYUNBTWSTART_A'] = array();
            $awhere['starttime'][]  =  array('<=',$time,'OR');
            $awhere['starttime'][]  =  array('=',0,'OR');
            $awhere['starttime'][]  =  array('isnull','','OR');
            $awhere['PHPYUNBTWEND_A'] = array();
            //结束时间
            $awhere['PHPYUNBTWSTART_B'] = array();
            $awhere['endtime'][]  =  array('>',$time,'AND');
            $awhere['endtime'][]  =  array('=',0,'OR');
            $awhere['endtime'][]  =  array('isnull','','OR');
            $awhere['PHPYUNBTWEND_B'] = array();

            $awhere['orderby']  =  'starttime,desc';

            $awhere['limit']  =  $tplapp['articlenum'] ? $tplapp['articlenum']:5;

            $articleM     =  $this->MODEL('article');
            $articlerows  =  $articleM->getList($awhere,array('field'=>'`id`,`title`,`newsphoto`,`description`,`starttime`'));
        }
        $data['articlelist']    =  count($articlerows['list'])>0 ? $articlerows['list'] :array();

        $data['qqdt']     =  !empty($this->config['sy_qqdt']) ? $this->config['sy_qqdt'] : 2;
        $data['wxlogin']  =  !empty($this->config['sy_app_wxlogin']) ? $this->config['sy_app_wxlogin'] : 2;
        $data['qqlogin']  =  !empty($this->config['sy_app_qqlogin']) ? $this->config['sy_app_qqlogin'] : 2;

        // 获取微信客服参数
        $wxkf             =  $this->getWxkf($tplapp, $_POST['appid']);
        $data['kfurl']    =  $wxkf['kfurl'];
        $data['kfcomid']  =  $wxkf['kfcomid'];

        $data['app_wxkf'] =  !empty($this->config['sy_app_wxkf']) ? $this->config['sy_app_wxkf'] : 2;
        $data['rct_index']=  !empty($this->config['sy_recruit_index_desc']) ? $this->config['sy_recruit_index_desc'] : '进厂找工作';
        // 微信小程序视频号id
        if ($_POST['provider'] == 'weixin'){
            include(DATA_PATH.'api/wxpay/wxpay_data.php');
            if (!empty($wxpaydata['sy_sphid'])){
                $data['sphid'] = $wxpaydata['sy_sphid'];
            }
        }

        $data['videoWeb']  =  !empty($this->config['sy_video_open']) ? $this->config['sy_video_open'] : 2;;

        if($tplapp['jobspecshow']=='1') {
            $specialJobM = $this->MODEL('specialjob');
            $specialJobList = $specialJobM->getList(array('display' => 1, 'tuiguang' => 1,'orderby'=>array('sort,desc','id'),'limit' => $tplapp['jobspecnum'] ? $tplapp['jobspecnum'] :3));
            $data['spList'] = $specialJobList;
        }

        $this->render_json(0,'ok',$data);
    }
    // 底部导航查询
    function getFoot_action()
    {
        include(DATA_PATH.'api/wxapp/tplapp.cache.php');
    	// 消息
        if (!empty($_POST['uid']) && !empty($_POST['token'])){

            $member   =  $this->yzToken($_POST['uid'], $_POST['token']);
            $msgNumM  =  $this->MODEL('msgNum');
            $msg      =  $msgNumM->getmsgNum($member['uid'],$member['usertype'],array('from' => 'wxapp'));
            $sysnum   =  $msg['msgNum'];

            if($member['usertype']==2 && $_POST['couponOT']){//查询即将过期的优惠券
                $couponM        =   $this -> MODEL('coupon');
                $couponNum      =   $couponM->getCouponNum(
                    array(
                        'uid'       => $member['uid'],
                        'status'    => 1,
                        'validity'  => array(array('>', time(),'AND'),array('<', strtotime('+ '.$this->config['sy_couponday'].' days'),'AND'))
                    )
                );
            }
            if (isset($this -> config['sy_chat_open'])){
                if ($this -> config['sy_chat_open'] == 1){

                    $chatM  =  $this->MODEL('chat');
                    $chat   =  $chatM->userinfo(array('uid'=>$member['uid'],'usertype'=>$member['usertype']));

                    $data['mine']      =  $chat['mine'];
                    $data['yuntoken']  =  $chatM->chatToken($member['uid']);
                }
            }
            if($this -> config['sy_iospay']=='2'){
            	$sysnum -=$msg['jobpackNum'];
            }

        }else{
            $sysnum   =  0;
        }
        if($_POST['firstFoot']=='1'){
        	foreach ($footnav as $k=>$v){
        	    $arr = explode(',', $v['pic']);
        	    $footnav[$k]['pic'] = checkpic($arr[0]);
        	    if (count($arr) > 1){
        	        $footnav[$k]['curpic'] = checkpic($arr[1]);
        	    }
		    }
		    $footnav = count($footnav) ? $footnav : array();

	        $data['footnav']  =  $footnav;
        }
        $data['iosfk']    =  isset($this->config['sy_iospay']) ? $this->config['sy_iospay'] : 2;
        $data['fkstr']    =  array('fkal'=>'支付宝','fkwx'=>'微信支付');
        $data['sysnum']   =  $sysnum;
        $data['couponNum']=  $couponNum ? $couponNum : 0;
        $data['footstyle'] = !empty($tplapp['footstyle']) ? $tplapp['footstyle'] : 1;
        $this->render_json(0,'',$data);
    }
	/**
	 * 身份切换申请相关数据获取和校验
	 */
    function applytype_action(){
        $user           =   $this->yzToken((int)$_POST['uid'],$_POST['token']);
        $memberM        =   $this -> MODEL('userinfo');
        $uid            =   $user['uid'];
        $usertype       =   $user['usertype'];

        $applyusertype  =   $usertype==1 ? 2 : 1;

        $applyContent   =   trim($_POST['applyContent']) ? trim($_POST['applyContent']) : '';

        $res            =   $memberM->checkChangeApply($uid,$applyusertype,$applyContent);

        $wxqcode        =   checkpic($this->config['sy_wx_qcode']);

        if($res['errcode']==6){

            $res['errcode']  =   2;

        }

        $data           =   array(
            'msg'       =>  $res['msg'],
            'errcode'   =>  $res['errcode'],
            'user'      =>  !empty($res['user']) ? $res['user'] :array(),
            'wxqcode'   =>  $wxqcode,
            'webtel'    =>  $this->config['sy_freewebtel']
        );

        $this           ->  render_json(0,'',$data);
    }

    /**
     * 意见反馈
     */
    function advice_action(){

        $data       =   array(
            'webtel'  =>  $this->config['sy_freewebtel'],

        );

        $user           =   $this->yzToken((int)$_POST['uid'],$_POST['token']);
        if($user['uid']){
            $userinfoM  = $this->MODEL('userinfo');
            $meminfo    = $userinfoM->getUserInfo(array('uid'=>$user['uid']),array('usertype'=>$user['usertype']));
            $username = $mobile = '';
            if($user['usertype']==1){
                $username = $meminfo['name'];
                $mobile = $meminfo['telphone'];
            }else if($user['usertype']==2){
                $username = $meminfo['name'];
                $mobile = $meminfo['linktel'];
            }else if($user['usertype']==3){
                $username = $meminfo['realname'];
                $mobile = $meminfo['moblie'];
            }else if($user['usertype']==4){
                $username = $meminfo['name'];
                $mobile = $meminfo['linktel'];
            }
            $data['advice_name'] = $username;
            $data['advice_mobile'] = $mobile;
        }

        $data['sy_advice_mobilecode'] = $this->config['sy_advice_mobilecode'];
        $data['sy_msg_isopen'] = $this->config['sy_msg_isopen'];
        $data['sy_msg_code'] = $this->config['sy_msg_code'];

        $this->render_json(1,'',$data);
    }

    /**
     * 意见反馈-发送动态码
     */
    function sendAdviceMsg_action()
    {
        $moblie			=		$_POST['moblie'];

        $this->checkMcsdk($moblie);

        $noticeM        =       $this->MODEL('notice');

        $result =   $noticeM->sendCode($moblie, 'code', $this->port,array(), 6, 90, 'msg');
        if($result['error']==1){
            $errcode    =   1;
        }else{
            $errcode    =   2;
            $msg        =   $result['msg'];
        }
        $this->render_json($errcode,$msg);
    }
	/**
	 * 意见反馈-保存
	 */
	function saveadvice_action()
	{
		$data		=	array(
			'username'	=>	$_POST['username'],
			'infotype'	=>	$_POST['infotype'],
			'content'	=>	$_POST['content'],
			'mobile'	=>	$_POST['mobile'],
            'advice_code'=> $_POST['code'],
			'utype'		=>	'wxapp'
		);
		$adviceM	=	$this->MODEL('advice');
		$return		=	$adviceM->addInfo($data);
		if($return['errcode'] == 9){
			$errcode	=	1;
		}else{
			$errcode	=	2;
		}
		$this->render_json($errcode,$return['msg']);
	}
	/**
	 * 首页首次进入app提示
	 */
	function getXybox_action()
	{
	    include(DATA_PATH.'api/wxapp/app.config.php');

	    $data['title']    =  $appconfig['xyboxtitle'];
	    $data['content']  =  $appconfig['xyboxcontent'];

	    $this->render_json(0, 'ok', $data);
	}
	/**
	 * app退出登录，清除推送标识
	 */
	public function tc_action(){
	    $userInfoM  =  $this->MODEL('userinfo');
	    $userInfoM -> upInfo(array('uid'=>intval($_POST['uid'])), array('clientid'=>'','deviceToken'=>''));
	    $this->render_json(0, 'ok');
	}

    //小程序我的 查询广告位
    public function getAd_action()
    {

        $data['list']  =  $this->ad_wxapp(array('name'=>$_POST['flag']), null, 1);

        $this -> render_json(0, 'ok', $data);
    }
    /**
     * 职位列表是否显示且在自定义排序最下方
     */
    private function isJobShowLast($tplapp){
        $customizeSort = array();// 自定义模板排序
        if (isset($tplapp)) {
            $rt = 1;
            if ($tplapp['job'] != 1) {// 隐藏职位列表
                $rt = 0;
            } else {// 职位列表为显示状态
                if ($tplapp['sort']) {
                    $sort = explode(',', $tplapp['sort']);
                } else {
                    $sort = 'search,member,nav,jobspec,notice,login,hotcom,hotjob,ad,job,zph,article,syszz,connect,foot';
                    $sort = explode(',', $sort);
                }
                foreach ($sort as $v) {
                    in_array($v, ['hotcom', 'hotjob', 'login', 'ad', 'job', 'zph', 'article']) && array_push($customizeSort, $v);
                }
                $jobIndex = array_search('job', $customizeSort);
                $showAble = array('hotcom' => 'hotcom', 'hotjob' => 'hotjobmore', 'login' => 'login', 'ad' => 'adshow', 'job' => 'job', 'zph' => 'zphshow', 'article' => 'articleshow');
                if ($jobIndex < 6) {// 职位列表非最后一个
                    for($i = $jobIndex + 1; $i < count($customizeSort); $i++){
                        if ($tplapp[$showAble[$customizeSort[$i]]] == 1) {// 职位列表后有其他显示项
                            $rt = 0;
                            break;
                        }
                    }
                }
            }
        }
        return $rt;
    }
    // 判断用户有没有关注公众号
    function isgzh_action(){

        $this->yzToken($_POST['uid'], $_POST['token']);

        $subscribe = 0;
        $wxloginid = 'weixin_gzhid_'. $this->member['uid'];

        if (empty($this->member['spid'])){

            $userInfoM  =  $this->MODEL('userinfo');
            $member     =  $userInfoM->getInfo(array('uid'=>$this->member['uid']),array('field'=>'`wxid`,`subscribe`'));

            if ($member['subscribe'] == 1){

                $subscribe  =   1;
            }else{
                // 查询识别记录
                $weixinM=   $this->MODEL('weixin');
                $log    =   $weixinM->getWxQrcode(array('wxloginid'=>$wxloginid, 'status'=>2, 'orderby'=>array('id,DESC')),array('field'=>'wxid'));

                if(!empty($this->member['wxid'])){
                    // 账号已绑定微信公众号
                    if (!empty($log['wxid']) && $this->member['wxid'] != $log['wxid']){
                        // 绑定的微信不是识别二维码的微信，提示用户是否要换绑
                        $subscribe = 2;
                    }else{
                        $res = $weixinM->getWxUser($this->member['wxid']);
                        if (isset($res['subscribe'])){
                            $subscribe = $res['subscribe'];
                        }
                    }
                }
            }

        }else{
            // 子账号，不需要提示强制关注公众号
            $subscribe = 1;
        }
        $return = array('subscribe'=>$subscribe);

        $this->render_json(0, 'ok', $return);
    }

    function getVideo_action()
    {

        $videoM =   $this->MODEL('video');

        $where  =   array(
            'rec'       =>  1,
            'display'   =>  1
        );

        $page   =   $_POST['page'];
        $limit  =   1;
        if ($page) {

            $pagenav        =   ($page - 1) * $limit;
            $where['limit'] =   array($pagenav, $limit);
        } else {

            $where['limit'] =   $limit;
        }

        $where['orderby']   =   'RAND()';

        $videoList = $videoM->getList($where, array('video' => 1));

        $return['list'] =   $videoList;

        $this->render_json(0, 'ok', $return);
    }
    function getCityDomain_action(){

        $siteM = $this->MODEL('site');

        $return = $siteM->getCityDomain(array('x'=>$_POST['x'],'y'=>$_POST['y'],'did'=>$_POST['did']));

        $this->render_json($return['error'], 'ok', $return);
    }
    // 会员中心底部单页面
    function webShow_action(){

        if ($_POST['name'] == 'rlzy'){
            $where['name'][] = array('=', '人力资源许可证');
            $where['name'][] = array('=','人力资源服务许可证','OR');
        }elseif ($_POST['name'] == 'yyzz'){
            $where = array('name'=>'营业执照');
        }
        if (isset($where)){

            $descM = $this->MODEL('description');
            $row   = $descM->getDes($where, array('field' => 'content'));

            $content  =  str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $row['content']);
            $content  =  htmlspecialchars_decode($content);

            preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$content,$res);

            if(!empty($res[3])){
                foreach($res[3] as $v){
                    if(strpos($v,'http:')===false && strpos($v,'https:')===false){

                        $content  =  str_replace($v,$this->config['sy_ossurl'].$v,$content);
                    }
                }
            }
            $row['content']  =  $content;

            $this->render_json(0,'ok',$row);
        }else{
            $this->render_json(-1, '参数异常');
        }
    }
}
?>
