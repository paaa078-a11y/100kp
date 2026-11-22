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
class index_controller extends adminCommon{
    
    /**
     * @desc后台首页
     */
    function index_action($set = ''){
    
        global $db_config;
        
        $this -> yunset('db_config', $db_config);
        
        $_POST  =  $this -> post_trim($_POST);
        
        if (! empty($_POST['username']) && ! empty($_POST['password'])) {
            
            if (strpos($this -> config['code_web'], '后台登录') !== false) {
                
                if ($this -> config['code_kind'] > 2) {
                    
					$check	=	verifytoken($this->config);
                    // 极验验证
                    if ($check['code'] != '200') {
                        $this -> ACT_layer_msg($check['msg'], 8, 'index.php');
                    } else {
                        $this -> admin_get_user_login($_POST['username'], $_POST['password'], 'index.php');
                    }
                    
                } else {
                    
                    if (md5(strtolower($_POST['authcode'])) === $_SESSION['authcode']) {
                        
                        unset($_SESSION['authcode']);
                        
                        $this -> admin_get_user_login($_POST['username'], $_POST['password'], 'index.php');
                        
                    } else {
                        
                        unset($_SESSION['authcode']);
                        
                        $this -> ACT_layer_msg('验证码错误！', 8, 'index.php');
                    }
                    
                }
                
            } else {
                
                $this -> admin_get_user_login($_POST['username'], $_POST['password'], 'index.php');
            }
            
        }
        
        if (!isset($this->adminShell)) {
            
            $this->yuntpl(array('admin/login'));
            
            die;
        }
        
        // 权限配置
        $adminM  =  $this -> MODEL('admin');
        
        $this->adminPower  =  $adminM -> getPower(array('id'=>$this->adminShell['m_id']));

        $this -> yunset('power', $this->adminPower['power']);
        // 判断否有crm客勤权限
        if(in_array('857',$this->adminPower['power'])){
            $this->yunset('crm_chat', 1);
        }
        // 判断是否业务员 并且无管理权限
        if(in_array('216',$this->adminPower['power']) && !in_array('226',$this->adminPower['power'])){

			$this -> yunset('indexurl', 'index.php?m=crm_index');
		}else{

			$this -> yunset('indexurl', 'index.php?m=admin_right');
		}
        
        $nav_user  =  array(
            'name'        =>  $this->adminPower['name'],
            'group_name'  =>  $this->adminPower['group_name']
        );
        
        //查询最近登录时间
        $logM  =  $this->MODEL('log');

        $adminLog  =  $logM -> getAdminLog(array('uid' => $_SESSION['auid'],'content' => '登录成功','orderby' => 'ctime'));
        
        // 导航配置
        $navM      =  $this -> MODEL('navigation');

        $navList   =  $navM -> getAdminNavList(array('display'=>array('<>',1),'id'=>array('in', pylode(',', $this->adminPower['power'])),'orderby'=>'sort,ASC'),array('utype'=>'power'));

        // 自定义快捷菜单
        $existCustomizeNavs = $navM->select_once('admin_customize_navs',array('auid' => $_SESSION['auid']));
        if ($existCustomizeNavs) {
            $navIdArr = array();
            $navIds = json_decode($existCustomizeNavs['nav_ids'], 1);
            foreach ($navIds as $navId) {
                in_array($navId, $this->adminPower['power']) && array_push($navIdArr, $navId);
            }
            $menu = $navM->select_all('admin_navigation',array('display'=>array('<>',1),'id'=>array('in', pylode(',', $navIdArr)),'orderby'=>'sort,ASC'),'id,keyid,name,classname,url');
            // 自定义菜单过滤后为空
            if (!$menu) {
                $menu = $navList['menu'];
            }
        } else {
            $menu = $navList['menu'];
        }

        $setarr    =  array(
            'one_menu'        =>  $navList['one_menu'],
            'two_menu'        =>  $navList['two_menu'],
            'navigation'      =>  $navList['navigation'],
            'menu'            =>  $menu,
            'nav_user'        =>  $nav_user,
            'admin_lasttime'  =>  $adminLog['ctime'] ? $adminLog['ctime'] : time()
        );
        $customizeIds = array();
        foreach ($menu as $m) {
            !in_array($m['id'], $customizeIds) && array_push($customizeIds, $m['id']);
        }
        $this->yunset('customize_ids', $customizeIds);

        $this -> yunset($setarr);
        
        // 获取默认页面
        if (is_array($navList['navigation'])) {
            
            foreach ($navList['navigation'] as $v) {
                
                $navigation_url_id[]  =  $v['id'];
            }
            
            $navigation_url = $this -> GET_web_default($navigation_url_id, $this->adminPower['power']);

            
        }
        
        $this -> yunset('navigation_url', $navigation_url);
        
        if ($set == '') {
            
            $this->yuntpl(array('admin/index'));
            
        }
    }
    /**
     * @desc 首页-管理员退出
     */
    function logout_action(){
        
        $this -> adminlogout();
        
        $this -> layer_msg('您已成功退出！', 9, 0, 'index.php');
    }
    
    /**
     *@desc 首页-清除缓存
     */
    function del_cache_action(){
        
        $cache   =  $this->del_dir('../data/templates_c', 1);
        
        /* 生成四位JS，css识别码 */
        $configM  =  $this->MODEL('config');
        
        $configM -> cacheCode();
        
        $this -> web_config();
        
        if ($cache == true) {
            
            $this -> layer_msg('更新成功！', 9, 0, 'index.php');
            
        } else {
            
            $this -> layer_msg('更新失败,请检查是否有权限！', 8, 0, 'index.php');
        }
    }
    /**
     * 首页-后台地图
     */
    function map_action(){
        
        $this -> index_action(1);
        
        $this -> yuntpl(array('admin/admin_map'));
    }
    /**
     * 获取页面标题
     */
    function topmenu_action(){
        
        $id  =  (int) $_GET['id'];
        // 解决ie9 $.get $.post 回调函数的返回值为undefine
        header('Content-Type: text/html; charset=UTF-8');
        
        if ($id == '1000') {
            echo '管理首页';
        } else {
            $arr  =  $this -> GET_web_check($id);
            
            $n    =  explode('-', $arr);
            
            unset($n[count($n) - 1]);
            
            echo implode('-', $n);
        }
    }
    /**
     * 首页-设置快捷导航
     */
    function shortcut_menu_action(){
        
        $navM = $this->MODEL('navigation');
        $navM->setCustomizeNav($_POST['chk_value'], $_SESSION['auid']);
        echo 1;
        die;
    }
    /**
     * 首页-查询相关统计数据
     */
    function msgNum_action(){
        
        $MsgNum  =  $this -> MODEL('msgNum');
        echo $MsgNum->msgNum();
    }
    function indexIssue_action(){

        $MsgNum  =  $this -> MODEL('msgNum');
        $msgdata = $MsgNum->msgNum();

        $msgarr = json_decode($msgdata,true);
        
        $issueData = array();

        if($msgarr['company']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['company'],
                'name'  =>  '待审核企业',
                'url'   =>  'index.php?m=admin_company&status=4'
            );
        }
        if($msgarr['company_job']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['company_job'],
                'name'  =>  '待审核职位',
                'url'   =>  'index.php?m=admin_company_job&state=4'
            );
        }
        if($msgarr['partjob']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['partjob'],
                'name'  =>  '待审核兼职',
                'url'   =>  'index.php?m=admin_partjob&state=4'
            );
        }

        if($msgarr['company_cert']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['company_cert'],
                'name'  =>  '待审核企业资质',
                'url'   =>  'index.php?m=comcert&status=3'
            );
        }
        if($msgarr['comlogo']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['comlogo'],
                'name'  =>  '待审核企业LOGO',
                'url'   =>  'index.php?m=admin_company_pic&status=1'
            );
        }
        if($msgarr['comshow']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['comshow'],
                'name'  =>  '待审核企业环境',
                'url'   =>  'index.php?m=admin_company_pic&c=show&status=1'
            );
        }
        if($msgarr['combanner']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['combanner'],
                'name'  =>  '待审核企业横幅',
                'url'   =>  'index.php?m=admin_company_pic&c=banner&status=1'
            );
        }
        if($msgarr['company_product']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['company_product'],
                'name'  =>  '待审核企业产品',
                'url'   =>  'index.php?m=productnews&status=3'
            );
        }
        if($msgarr['invoiceNum']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['invoiceNum'],
                'name'  =>  '待审核企业发票',
                'url'   =>  'index.php?m=invoice&status=0'
            );
        }
        if($msgarr['company_news']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['company_news'],
                'name'  =>  '待审核企业新闻',
                'url'   =>  'index.php?m=productnews&c=comnews&status=3'
            );
        }
        if($msgarr['resume_expect']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['resume_expect'],
                'name'  =>  '待审核简历',
                'url'   =>  'index.php?m=admin_resume&status=4'
            );
        }
        if($msgarr['resumetrust']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['resumetrust'],
                'name'  =>  '待审核委托简历',
                'url'   =>  'index.php?m=admin_trust&status=3'
            );
        }
        if($msgarr['errlog']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['errlog'],
                'name'  =>  '错误日志',
                'url'   =>  'index.php?m=errlog'
            );
        }
        if($msgarr['zphnetcomtrust']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['zphnetcomtrust'],
                'name'  =>  '待处理网招报名',
                'url'   =>  'index.php?m=admin_zphnet'
            );
        }
        if($msgarr['ltheightuser']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['ltheightuser'],
                'name'  =>  '待审核优质简历',
                'url'   =>  'index.php?m=height_user&status=1'
            );
        }
        if($msgarr['usercertNum']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['usercertNum'],
                'name'  =>  '待审核个人认证',
                'url'   =>  'index.php?m=usercert&status=2'
            );
        }
        if($msgarr['ltNum']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['ltNum'],
                'name'  =>  '待审核猎头会员',
                'url'   =>  'index.php?m=admin_lt_member&status=4'
            );
        }
        if($msgarr['ltcert']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['ltcert'],
                'name'  =>  '待审核猎头认证',
                'url'   =>  'index.php?m=admin_lt_cert&status=0'
            );
        }
        if($msgarr['ltjob']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['ltjob'],
                'name'  =>  '待审核猎头职位',
                'url'   =>  'index.php?m=admin_lt_job&status=4'
            );
        }
        if($msgarr['ltlogo']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['ltlogo'],
                'name'  =>  '待审核猎头LOGO',
                'url'   =>  'index.php?m=admin_lt_pic&status=1'
            );
        }
        if($msgarr['trainNum']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['trainNum'],
                'name'  =>  '待审核培训会员',
                'url'   =>  'index.php?m=train_member&status=4'
            );
        }
        if($msgarr['teacher']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['teacher'],
                'name'  =>  '待审核培训师',
                'url'   =>  'index.php?m=teacher&status=3'
            );
        }
        if($msgarr['subject']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['subject'],
                'name'  =>  '待审核培训课程',
                'url'   =>  'index.php?m=admin_subject&status=3'
            );
        }
        if($msgarr['traincert']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['traincert'],
                'name'  =>  '待审核培训认证',
                'url'   =>  'index.php?m=traincert&status=3'
            );
        }
        if($msgarr['trainnews']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['trainnews'],
                'name'  =>  '待审核培训新闻',
                'url'   =>  'index.php?m=trainnews&status=3'
            );
        }
        if($msgarr['pxlogo']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['pxlogo'],
                'name'  =>  '待审核培训LOGO',
                'url'   =>  'index.php?m=admin_px_pic&status=1'
            );
        }
        if($msgarr['pxshow']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['pxshow'],
                'name'  =>  '待审核培训环境',
                'url'   =>  'index.php?m=admin_px_pic&c=show&status=1'
            );
        }
        if($msgarr['pxbanner']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['pxbanner'],
                'name'  =>  '待审核培训横幅',
                'url'   =>  'index.php?m=admin_px_pic&c=banner&status=1'
            );
        }
        if($msgarr['appealnum']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['appealnum'],
                'name'  =>  '会员申诉',
                'url'   =>  'index.php?m=admin_appeal'
            );
        }
        if($msgarr['once_job']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['once_job'],
                'name'  =>  '待审核店铺招聘',
                'url'   =>  'index.php?m=admin_once&status=3'
            );
        }
        if($msgarr['tiny']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['tiny'],
                'name'  =>  '待审核普工简历',
                'url'   =>  'index.php?m=admin_tiny&status=2'
            );
        }
        if($msgarr['withdrawNum']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['withdrawNum'],
                'name'  =>  '待审核赏金提现',
                'url'   =>  'index.php?m=admin_withdraw&order_state=0'
            );
        }

        if($msgarr['schooljob']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['schooljob'],
                'name'  =>  '待审核应届生职位',
                'url'   =>  'index.php?m=admin_school_graduate&state=4'
            );
        }
        if($msgarr['schoolxjh']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['schoolxjh'],
                'name'  =>  '待审核校招宣讲会',
                'url'   =>  'index.php?m=admin_school_xjh&status=3'
            );
        }
        if($msgarr['zphcom']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['zphcom'],
                'name'  =>  '待审核参会企业',
                'url'   =>  'index.php?m=zhaopinhui&c=com&status=3'
            );
        }
        if($msgarr['ask']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['ask'],
                'name'  =>  '待审核问答',
                'url'   =>  'index.php?m=admin_question&status=0'
            );
        }
        if($msgarr['order']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['order'],
                'name'  =>  '待处理充值订单',
                'url'   =>  'index.php?m=company_order&order_state=1'
            );
        }
        if($msgarr['adorder']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['adorder'],
                'name'  =>  '待审核广告订单',
                'url'   =>  'index.php?m=ad_order&status=-1'
            );
        }
        if($msgarr['reportjob']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['reportjob'],
                'name'  =>  '待处理举报职位',
                'url'   =>  'index.php?m=report'
            );
        }
        if($msgarr['reportresume']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['reportresume'],
                'name'  =>  '待处理举报简历',
                'url'   =>  'index.php?m=report&ut=2'
            );
        }
        if($msgarr['reportask']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['reportask'],
                'name'  =>  '待处理举报问答',
                'url'   =>  'index.php?m=report&type=1'
            );
        }
        if($msgarr['reportgw']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['reportgw'],
                'name'  =>  '待处理投诉顾问',
                'url'   =>  'index.php?m=report&type=2'
            );
        }
        if($msgarr['reportxjh']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['reportxjh'],
                'name'  =>  '待处理校招宣讲会',
                'url'   =>  'index.php?m=report&type=3'
            );
        }
        if($msgarr['userpic']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['userpic'],
                'name'  =>  '待审核个人头像',
                'url'   =>  'index.php?m=admin_user_pic&status=1'
            );
        }
        if($msgarr['usershow']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['usershow'],
                'name'  =>  '待审核作品案例',
                'url'   =>  'index.php?m=admin_user_pic&c=show&status=1'
            );
        }
        if($msgarr['gqinfo']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['gqinfo'],
                'name'  =>  '待审核供求会员',
                'url'   =>  'index.php?m=admin_gqmember&status=4'
            );
        }
        if($msgarr['gqpic']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['gqpic'],
                'name'  =>  '待审核供求头像',
                'url'   =>  'index.php?m=admin_gqinfo_pic&status=1'
            );
        }
        if($msgarr['gqtask']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['gqtask'],
                'name'  =>  '待审核供求任务',
                'url'   =>  'index.php?m=admin_gqtask&status=3'
            );
        }
        if($msgarr['linkNum']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['linkNum'],
                'name'  =>  '待审核友情链接',
                'url'   =>  'index.php?m=link&state=2'
            );
        }
        if($msgarr['redeem']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['redeem'],
                'name'  =>  '待审核商品兑换',
                'url'   =>  'index.php?m=reward_list&status=-1'
            );
        }
        if($msgarr['specialcom']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['specialcom'],
                'name'  =>  '待审核企业专题',
                'url'   =>  'index.php?m=special'
            );
        }
        if($msgarr['userchangenum']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['userchangenum'],
                'name'  =>  '待审核身份转换会员',
                'url'   =>  'index.php?m=admin_userchange&status=3'
            );
        }
        if($msgarr['handlenum']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['handlenum'],
                'name'  =>  '待处理意见反馈',
                'url'   =>  'index.php?m=admin_message&status=1'
            );
        }
        if($msgarr['logout']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['logout'],
                'name'  =>  '待处理注销账号',
                'url'   =>  'index.php?m=admin_member_logout&status=1'
            );
        }
        if($msgarr['spview']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['spview'],
                'name'  =>  '待审核视频面试',
                'url'   =>  'index.php?m=admin_spview&status=3'
            );
        }
        if($msgarr['company_msg']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['company_msg'],
                'name'  =>  '待审核面试评价',
                'url'   =>  'index.php?m=com_pl&status=0'
            );
        }
        if($msgarr['yqmb_msg']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['yqmb_msg'],
                'name'  =>  '待审核面试模板',
                'url'   =>  'index.php?m=admin_yqmb&status=0'
            );
        }
        if($msgarr['usermsg_msg']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['usermsg_msg'],
                'name'  =>  '待审核求职咨询',
                'url'   =>  'index.php?m=admin_msg&status=0'
            );
        }
        if($msgarr['answer_msg']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['answer_msg'],
                'name'  =>  '待审核问答回复',
                'url'   =>  'index.php?m=admin_question&c=getanswer&status=0'
            );
        }
        if($msgarr['answerreview_msg']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['answerreview_msg'],
                'name'  =>  '待审核问答评论',
                'url'   =>  'index.php?m=admin_question&c=getcomment&status=0'
            );
        } 
        if($msgarr['concheck_msg']>0){
            $issueData[] = array(
                'num'   =>  $msgarr['concheck_msg'],
                'name'  =>  '待处理内容检测',
                'url'   =>  'index.php?m=admin_concheck_log&status=0'
            );
        }  
        if($msgarr['warning']>0){
            $issueData[] = array(
                'num' => $msgarr['warning'],
                'name' => '预警',
                'url' => 'index.php?m=warning&status=2'
            );
        }
        if($msgarr['skill_bm_msg']>0){
            $issueData[] = array(
                'num' => $msgarr['skill_bm_msg'],
                'name' => '技能提升报名',
                'url' => 'index.php?m=skill_bm&isread=0'
            );
        }
        $issue  =  count($issueData)>0?array_chunk($issueData,4):array();
        
        echo json_encode($issue);exit();
    }

    function wxbind_action(){
        $WxM=$this->MODEL('weixin');
        $qrcode = $WxM->applyWxQrcode($_COOKIE['wxadminbind'],'wxadminbind');
        if(!$qrcode){
            echo 0;
        }else{
            echo $qrcode;
        }
    }
    function getwxbindstatus_action(){
        if($_SESSION['auid']){
            $adminM  =  $this -> MODEL('admin');
            $uadmin = $adminM ->  getAdminUser(array('uid'=>$_SESSION['auid']));
            if($uadmin['wxid']!=''){
                echo 1;
            }else{
                echo 0;
            }
        }
    }

    /**
     * 获取手机号归属地
     *users:王旭
     *Data:2023/5/15
     *Time:9:35
     */
    function getMobileAddress_action(){
        if ($_POST['uid'] && $_POST['moblie']) {
            $moblieaddress = getMoblieAddress($_POST['moblie']);
            $userinfoM = $this->MODEL('userinfo');
            $userinfoM->upInfo(array('uid'=>$_POST['uid']),array('moblie_address'=>$moblieaddress));
            if ($moblieaddress){
                $msg = $moblieaddress;
            }elseif($this->config['sy_mobile'] == 2){
                $msg = '请先开启归属地查询';
            }else{
                $msg = '查询失败';
            }
            echo json_encode(array('errcode' => $moblieaddress ? 9 : 8, 'msg' => $msg));
            die;
        }
    }

    /**
     * 获取ip归属地
     *users:王旭
     *Data:2023/5/15
     *Time:9:36
     */
    function getIpAddress_action(){
        if ($_POST['uid'] && $_POST['ip']) {
            $ipaddress = getIpAddress($_POST['ip']);
            $userinfoM = $this->MODEL('userinfo');
            $userinfoM->upInfo(array('uid'=>$_POST['uid']),array('login_address'=>$ipaddress));
            if ($ipaddress){
                $msg = $ipaddress;
            }elseif($this->config['sy_ip'] == 2){
                $msg = '请先开启归属地查询';
            }else{
                $msg = '查询失败';
            }
            echo json_encode(array('errcode' => $ipaddress ? 9 : 8, 'msg' => $msg));
            die;
        }
    }
}
?>