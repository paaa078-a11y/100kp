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

class index_controller extends common
{
    /**
     * 首页
     */
    function index_action()
    {
        if ($this->uid && $this->usertype == '1') {

            $resumeM    =   $this->MODEL('resume');

            $expect     =   $resumeM->getExpectByUid($this->uid, array('field' => 'id,status'));
            if (!empty($expect)) {

                $user_resume    =   $resumeM->getUserResumeInfo(array('uid' => $this->uid, 'eid' => $expect['id']), array('field' => '`skill`,`work`,`project`,`edu`,`training`'));
                $resume_yhnum   =   0;
                foreach ($user_resume as $rk => $rv) {
                    if ($rv == 0) {
                        $resume_yhnum++;
                    }
                }
                $this->yunset('resume_yhnum', $resume_yhnum);
            }
            $this->yunset('expect', $expect);
        }

		if ($this->config['sy_web_city_one']) {
            $_GET['provinceid'] = 	$this->config['sy_web_city_one'];
        }
        if ($this->config['sy_web_city_two']) {
            $_GET['cityid'] 	= 	$this->config['sy_web_city_two'];
        }

        $spjNum =   $this->obj->select_num('special_job', array('display' => 1, 'tuiguang' => 1));
        $this->yunset('spjNum', $spjNum);

        include PLUS_PATH.'/tplmoblie.cache.php';

        if ($tplmoblie['wapdiy'] == 1 || $_GET['type'] == 1) {

            $this->get_moblie();

            $tplM   =   $this->MODEL('tpl');

            $nav    =   $tplM->getTplmobliepicList(array('type' => 'nav', 'orderby' => 'sort,asc'));
            $this->yunset(array('nav' => $nav));

            $hd     =   $tplM->getTplmobliepicList(array('type' => 'hd'));
            $this->yunset('hd', $hd);

            $indexnav   =   $tplM->getTplmobliepicList(array('type' => 'indexnav', 'orderby' => 'id,asc'));
            foreach ($indexnav as $k => $v) {

                $indexnav[$k]['indexnavurl']    =   $v['url'];
                if ($k == 0) {
                    $indexnav1  =   $v;
                }
                if ($k == 1) {
                    $indexnav2  =   $v;
                }
                if ($k == 2) {
                    $indexnav3  =   $v;
                }
            }
            $this->yunset('indexnav1', $indexnav1);
            $this->yunset('indexnav2', $indexnav2);
            $this->yunset('indexnav3', $indexnav3);

            $adlist =   $tplM->getTplmobliepicList(array('type' => 'ad'));
            $this->yunset('adlist', $adlist);

            if ($tplmoblie['rewardjobnum'] < 1) {
                $tplmoblie['rewardjobnum'] = 5;
            }
            if ($tplmoblie['hotjobnum'] < 1) {
                $tplmoblie['hotjobnum'] = 8;
            }
            if ($tplmoblie['newjobnum'] < 1) {
                $tplmoblie['newjobnum'] = 5;
            }
            if ($tplmoblie['hotcomnum'] < 1) {
                $tplmoblie['hotcomnum'] = 5;
            }
            if ($tplmoblie['articlenum'] < 1) {
                $tplmoblie['articlenum'] = 5;
            }
            if ($tplmoblie['resumenum'] < 1) {
                $tplmoblie['resumenum'] = 5;
            }
            if ($tplmoblie['urgentjobnum'] < 1) {
                $tplmoblie['urgentjobnum'] = 5;
            }
            if ($tplmoblie['recjobnum'] < 1) {
                $tplmoblie['recjobnum'] = 5;
            }
            if ($tplmoblie['zphnum'] < 1) {
                $tplmoblie['zphnum'] = 5;
            }
            $this->yunset('tplmoblie', $tplmoblie);

            //显示顺序
            if ($tplmoblie['sort']) {

                $sort   =   explode(',', $tplmoblie['sort']);
            } else {

                $sort   =   'hearder,hd,search,nav,indexnav,notice,reglogin,ad,rewardjob,hotjob,newjob,hotcom,recjob,urgentjob,resume,article,zph,jobclass';
                $sort   =   explode(',', $sort);
            }
            $this->yunset('sort', $sort);
            //搜索类型
            $searchurl  =   array(

                array('id' => 1, 'c' => 'job', 'name' => '找工作'),
                array('id' => 2, 'c' => 'resume', 'name' => '找人才'),
                array('id' => 3, 'c' => 'company', 'name' => '找企业'),
                array('id' => 4, 'c' => 'part', 'name' => '找兼职'),
                array('id' => 5, 'c' => 'tiny', 'name' => '普工专区'),
                array('id' => 6, 'c' => 'once', 'name' => '店铺招聘'),
                array('id' => 7, 'c' => 'ltjob', 'name' => '猎头职位'),
                array('id' => 8, 'c' => 'ltjob', 'a' => 'service', 'name' => '委托求职'),
                array('id' => 9, 'c' => 'train', 'name' => '培训课程'),
                array('id' => 10, 'c' => 'train', 'a' => 'agency', 'name' => '培训机构'),
                array('id' => 11, 'c' => 'train', 'a' => 'teacher', 'name' => '培训讲师'),
                array('id' => 12, 'c' => 'ask', 'a' => 'list', 'name' => '互动问答')
            );
            $this->yunset('searchurl', $searchurl);
            $this->yunset($this->MODEL('cache')->GetCache(array('job')));
            if ($this->config["did"]) {

                $this->seo("index", $this->config['sy_webtitle'], $this->config['sy_webkeyword'], $this->config['sy_webmeta']);
            } else {

                $this->seo('index');
            }
            $this->yunset('indexnav', 1);
            $this->yuntpl(array('wap/wap_diy'));
        } else {

            $this->get_moblie();

            if ($this->config["did"]) {

                $this->seo("index", $this->config['sy_webtitle'], $this->config['sy_webkeyword'], $this->config['sy_webmeta']);
            } else {

                $this->seo('index');
            }
            $this->yunset('indexnav', 1);

            $xjhM       =   $this->MODEL('xjhlive');

            $xjh        =   $xjhM->getInfo(array('state'=>array('<>',3),'statetime'=>array('<',time()),'status' => 1, 'livestatus' => 3, 'orderby' => 'stime,desc'));

            if (empty($xjh)){
                $xjh    =   $xjhM->getInfo(array('state'=>array('<>',3),'statetime'=>array('<',time()),'status' => 1, 'livestatus' => 1, 'orderby' => 'stime,asc'));
            }
            //热门职位查询
            $categoryM	=	$this -> MODEL('category');
            $hotclass = $categoryM->getHotJobClass(array('rec'=>1,'orderby'=>'sort,desc'),'`id`,`keyid`,`name`');

            $this->yunset('hotclass', $hotclass);
            if (!empty($xjh)){
                if (!empty($xjh['picarr'])){
                    $xjh['pic']  =  $xjh['picarr'][0]['url'];
                    unset($xjh['picarr']);
                }
                $this->yunset('xjh', $xjh);
            }
            $annM = $this->MODEL('announcement');
            $annum = $annM->getNum();
            $this->yunset('annum', $annum);
            include DATA_PATH.'api/wxapp/tplapp.cache.php';
            if($tplapp['cshow']==1){
				$this->yunset('kfurl', $tplapp['kfurl']);
			}
            //首页弹框广告记录
            $bannerFlag   =   $_COOKIE['wap_bannerFlag'];
            if (!$bannerFlag) {
                $this->cookie->setcookie('wap_bannerFlag', 1, time() + 3600);
            }
            $this->yunset("bannerFlag", $bannerFlag);
            
            $this->yuntpl(array('wap/index'));
        }
    }
    
    // widuu 2025/11
	
	/**
     * 根据 province、cityid 或 three_cityid 获取 fz_type=1 的站点 id 列表
     *
     * @param array $site_domain 站点配置数组
     * @param string|null $province 省份ID（字符串）
     * @param string|null $cityid 城市ID（字符串）
     * @param string|null $three_cityid 三级城市ID（字符串，支持单个或列表）
     * @return array 匹配的 id 列表（整型）
     */
    function getSiteIdsByCondition($province = null, $cityid = null, $three_cityid = null)
    {
        $ids = array();
        include(PLUS_PATH."/domain_cache.php");
 
        foreach ($site_domain as $item) {
            // 只处理 fz_type == '1' 的分站
            if (!isset($item['fz_type']) || $item['fz_type'] !== '1') {
                continue;
            }
    
            // 按 province 过滤
            if ($province !== null && (!isset($item['province']) || $item['province'] +0 !== $province)) {
                continue;
            }
    
            // 按 cityid 过滤
            if ($cityid !== null && (!isset($item['cityid']) || $item['cityid'] + 0 !== $cityid)) {
                continue;
            }
         
            // 按 three_cityid 过滤（支持逗号分隔）
            if ($three_cityid !== null && (!isset($item['three_cityid']) || $item['three_cityid']+0 !== $three_cityid)) {
                continue;
            }
            // 通过所有筛选，加入 id
            $ids[] = isset($item['id']) ? (int)$item['id'] : 0;
        }
    
        return $ids;
    }
    
    function isMainOrWwwDomain($url) {
        // 提取主机名（host）
        if (strpos($url, '://') === false) {
            $host = $url; // 假设输入的是纯域名
        } else {
            $host = parse_url($url, PHP_URL_HOST);
        }
    
        if (!$host) {
            return false;
        }
    
        // 转为小写，统一处理
        $host = strtolower($host);
    
        // 拆分域名部分
        $parts = explode('.', $host);
    
        // 至少要有两个部分：example.com
        if (count($parts) < 2) {
            return false;
        }
    
        // 情况1：主域名（如 example.com）
        if (count($parts) === 2) {
            return true;
        }
    
        // 情况2：www 开头的二级域名（如 www.example.com）
        if (count($parts) === 3 && $parts[0] === 'www') {
            return true;
        }
    
        // 其他情况：xxx.example.com、a.b.example.com 等 → 不符合
        return false;
    }
    
    
	function sitemap_action()
	{
	    $this->cache = false;
	     // 禁用所有缓存
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Content-Type: text/xml; charset=utf-8");
    
        // 清除可能的输出缓冲
        if (ob_get_level()) {
            ob_clean();
        }
        // 切换城市标识
        $cityChange = false;
        // 主站标识
        $isMaster   = false;
        $dids = [];
        if(isset($this->config['did']) && $this->config['did'] + 0 > 0){
            // if($this->config['fz_type'] == 1){
            //     $cityChange = true;
            //     if($this->config['three_cityid'] != 0){
            //         $dids = $this->getSiteIdsByCondition(null, null, $this->config['three_cityid']);
            //     }else if($this->config['cityid'] != 0){
            //         $dids = $this->getSiteIdsByCondition(null, $this->config['cityid']);
            //     }else{
            //         $dids = $this->getSiteIdsByCondition($this->config['province']);
            //     }
            // }else{
            //     $cityChange = false;
            // }
        }else{
            $isMaster = true;
        }
   

	   
        $whereCompany['limit'] = 100;
        // $whereCompany['did']   = $did > 0 ? $did : "isnull";
        if(!$isMaster){
            if($this->config['three_cityid'] > 0){
                $whereCompany['three_cityid'] = $this->config['three_cityid'];
            }
            if($this->config['cityid'] > 0){
                $whereCompany['cityid'] = $this->config['cityid'];
            }
            if($this->config['province'] > 0){
                $whereCompany['provinceid'] = $this->config['province'];
            }
        }
        
        $whereCompany['orderby'] = array('lastupdate,desc');
        $comlist = $this->MODEL('company')->getList($whereCompany, array('field' => "`uid` as `id`,`lastupdate` as `time`"));
        $rows['company'] = $comlist['list'];

         $did = isset($this->config['did']) ? $this->config['did'] : 0;
	    $whereArticle['limit'] = 100;
        $whereArticle['orderby'] = array('lastupdate,desc');
        if(!$isMaster){
            $whereArticle['orderby']      = array('did,desc', 'lastupdate,desc');
            $whereArticle['did'][]        =   array('=', $did);
            $whereArticle['did'][]        =   array('=', '-1', 'OR');
        }
  
	    $nlist = $this->MODEL('article')->getList($whereArticle, array('field' => "`id`,`lastupdate` as `time`,`datetime`"));
	   // var_dump($this->MODEL('article'));exit();
        $rows['news'] = $nlist['list'];
        
        
        $whereJob['limit'] = 100;

        if(!$isMaster){
            if($this->config['three_cityid'] > 0){
                $whereJob['three_cityid'] = $this->config['three_cityid'];
            }
            if($this->config['cityid'] > 0){
                $whereJob['cityid'] = $this->config['cityid'];
            }
            if($this->config['province'] > 0){
                $whereJob['provinceid'] = $this->config['province'];
            }
        }
        $whereJob['sdate'] = array('<', time());
        $whereJob['state'] = 1;
        $whereJob['orderby'] = array('lastupdate,desc');
        $joblist = $this->MODEL('job')->getList($whereJob, array('field' => "`id`,`lastupdate` as `time`"));
        $rows['job'] = $joblist['list'];
        
        
        $where['status'] = 1;
        $where['r_status'] = 1;
        $where['job_classid'] = array('<>', '');
        $where['limit'] = 100;
        if(!$isMaster){
            if($this->config['three_cityid'] > 0){
                $where['three_cityid'] = $this->config['three_cityid'];
            }
            if($this->config['cityid'] > 0){
                $where['cityid'] = $this->config['cityid'];
            }
            if($this->config['province'] > 0){
                $where['provinceid'] = $this->config['province'];
            }
        }
        $order = 'lastupdate';
        $where['orderby'] = array('lastupdate,desc');
        $rlist = $this->MODEL('resume')->getSimpleList($where, array('field' => "`id`,`" . $order . "` as `time`"));
        $rows['resume'] = $rlist;
        // var_dump($rows);
        // 设置默认更新频率
        $frequency = !empty($_POST['frequency']) ? $_POST['frequency'] : 'daily';

        $show = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";
        if (is_array($rows)) {
            foreach ($rows as $key => $v) {
        if ($key == 'company') {
            foreach ($v as $val) {
                $url = Url('wap', array('c' => 'company', 'a' => 'show', "id" => $val['id']));
                $url = str_replace("&", "&amp;", $url);
                if($this->isMainOrWwwDomain($url)){
                    $url = str_replace('/m/', '/wap/', $url);
                }
                $show .= "<url><loc>" . $url . "</loc><lastmod>" . date("Y-m-d\TH:i:s\Z", $val['time']) . "</lastmod><changefreq>" . $frequency . "</changefreq></url>\r\n";
            }
        }
        if ($key == 'job') {
            foreach ($v as $val) {
                $url = Url("wap", array("c" => "job", "a" => "comapply", "id" => $val['id']));
                $url = str_replace("&", "&amp;", $url);
                if($this->isMainOrWwwDomain($url)){
                    $url = str_replace('/m/', '/wap/', $url);
                }
                $show .= "<url><loc>" . $url . "</loc><lastmod>" . date("Y-m-d\TH:i:s\Z", $val['time']) . "</lastmod><changefreq>" . $frequency . "</changefreq></url>\r\n";
            }
        }
        if ($key == 'news' && $this->config["sy_news_rewrite"] == '2') {
            foreach ($v as $val) {
                $url = Url('wap', array('c' => 'article', 'a' => 'show', "id" => $val['id']));
                if($this->isMainOrWwwDomain($url)){
                    $url = str_replace('/m/', '/wap/', $url);
                }
                $show .= "<url><loc>" . $url . "</loc><lastmod>" . date("Y-m-d\TH:i:s\Z", $val['time']) . "</lastmod><changefreq>" . $frequency . "</changefreq></url>\r\n";
            }
        }
        if ($key == 'news' && $this->config["sy_news_rewrite"] != '2') {
            foreach ($v as $val) {
                $url = Url('wap', array('c' => 'article', 'a' => 'show', "id" => $val['id']));
                $url = str_replace("&", "&amp;", $url);
                if($this->isMainOrWwwDomain($url)){
                    $url = str_replace('/m/', '/wap/', $url);
                }
                $show .= "<url><loc>" . $url . "</loc><lastmod>" . date("Y-m-d\TH:i:s\Z", $val['time']) . "</lastmod><changefreq>" . $frequency . "</changefreq></url>\r\n";
            }
        }
        if ($key == 'resume') {
            foreach ($v as $val) {
                $url = Url('wap', array('c' => 'resume', 'a' => 'show', "id" => $val['id']));
                $url = str_replace("&", "&amp;", $url);
                if($this->isMainOrWwwDomain($url)){
                    $url = str_replace('/m/', '/wap/', $url);
                }
                $show .= "<url><loc>" . $url . "</loc><lastmod>" . date("Y-m-d\TH:i:s\Z", $val['time']) . "</lastmod><changefreq>" . $frequency . "</changefreq></url>\r\n";
                    }
                }
            }

            $show .= "</urlset>";
        }
        echo $show;
        exit();
	}

    /**
     * 退出登录
     */
    function loginout_action()
    {
        $this->cookie->unset_cookie();
        $this->wapheader('');
    }

    // 关于我们
    function about_action()
    {

        $descM      =   $this->MODEL('description');
        $content    =   $descM->getDes(array('name' => '关于我们'), array('field' => 'content'));
        $this->yunset('content', $content);
        if ($_GET['fr'] == 'wxapp') {
            $this->yunset('wxapp', 1);
        }
        $this->yunset('headertitle', '关于我们');
        $this->yunset('title', '关于我们');
        $this->yuntpl(array('wap/about'));
    }

    // 联系我们
    function contact_action()
    {
        $descM      =   $this->MODEL('description');
        $content    =   $descM->getDes(array('name' => '联系我们'), array('field' => 'content'));
        $this->yunset('content', $content);
        if ($_GET['fr'] == 'wxapp') {
            $this->yunset('wxapp', 1);
        }
        $this->yunset('headertitle', '联系我们');
        $this->yunset('title', '联系我们');
        $this->yuntpl(array('wap/about'));
    }

    //下载app
    function appDown_action()
    {
        if (preg_match("/(iphone|ipod|ipad)/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
            // 苹果
            include(DATA_PATH . 'api/wxapp/app.config.php');
            if (is_weixin()){
                // 微信内用二维码
                $down = array(
                    'qrcode' => $this->config['sy_ossurl'] .'/' .$this->config['sy_iosu_qcode']
                );
            }else{
                // 其他浏览器用链接
                if (!empty($appconfig['iosurl'])) {
                    // 苹果微信内不支持链接直接打开appstore
                    $down['url'] = $appconfig['iosurl'];
                }
            }
            $down['version'] = $appconfig['iosversion'];
        }else{
            // 安卓
            include(DATA_PATH . 'api/wxapp/app.config.php');
            if (is_weixin()){
                // 微信内用二维码
                $down = array(
                    'qrcode' => $this->config['sy_ossurl'] .'/' .$this->config['sy_androidu_qcode']
                );
            }else{
                // 其他浏览器用链接
                include(DATA_PATH . 'api/wxapp/app.config.php');
                if (!empty($appconfig['androidurl'])) {
                    $down['url'] = $appconfig['androidurl'];
                }
            }
            $down['version'] = $appconfig['androidversion'];
        }
        $this->yunset('down', $down);
        $this->yunset('headertitle', '下载APP');
        $this->yuntpl(array('wap/appdown'));
    }

    // 隐私政策
    function privacy_action()
    {
        $descM      =   $this->MODEL('description');
        $content    =   $descM->getDes(array('name' => '隐私政策'), array('field' => 'content'));
        $this->yunset('content', $content);
        if ($_GET['fr'] == 'wxapp') {
            $this->yunset('wxapp', 1);
        }
        $this->yunset('headertitle', '隐私政策');
        $this->yunset('title', '隐私政策');
        $this->yuntpl(array('wap/about'));
    }

    // 用户协议
    function protocol_action()
    {
        $descM      =   $this->MODEL('description');
        $content    =   $descM->getDes(array('name' => '注册协议'), array('field' => 'content'));
        $this->yunset('content', $content);
        if ($_GET['fr'] == 'wxapp') {
            $this->yunset('wxapp', 1);
        }
        $this->yunset('headertitle', '服务协议');
        $this->yunset('title', '服务协议');
        $this->yuntpl(array('wap/about'));
    }

    // 小程序浮动客服
    function miniContact_action()
    {
        $descM      =   $this->MODEL('description');
        $content    =   $descM->getDes(array('name' => '小程序浮动客服'), array('field' => 'content'));
        $this->yunset('content', $content);
        if ($_GET['fr'] == 'wxapp') {
            $this->yunset('wxapp', 1);
        }
        $this->yunset('headertitle', '在线客服');
        $this->yunset('title', '在线客服');
        $this->yuntpl(array('wap/about'));
    }
    // 人力资源许可证
    function rlzy_action()
    {
        $where['name'][] = array('=', '人力资源许可证');
        $where['name'][] = array('=','人力资源服务许可证','OR');
        
        $descM      =   $this->MODEL('description');
        $content    =   $descM->getDes($where, array('field' => 'content'));
        $this->yunset('content', $content);
        if ($_GET['fr'] == 'wxapp') {
            $this->yunset('wxapp', 1);
        }
        $this->yunset('headertitle', '人力资源许可证');
        $this->yunset('title', '人力资源许可证');
        $this->yuntpl(array('wap/about'));
    }
    // 营业执照
    function yyzz_action()
    {
        $descM      =   $this->MODEL('description');
        $content    =   $descM->getDes(array('name' => '营业执照'), array('field' => 'content'));
        $this->yunset('content', $content);
        if ($_GET['fr'] == 'wxapp') {
            $this->yunset('wxapp', 1);
        }
        $this->yunset('headertitle', '营业执照');
        $this->yunset('title', '营业执照');
        $this->yuntpl(array('wap/about'));
    }
    // 个人信息收集清单
    function geren_action()
    {
        $descM      =   $this->MODEL('description');
        $content    =   $descM->getDes(array('name' => '个人信息收集清单'), array('field' => 'content'));
        $this->yunset('content', $content);
        if ($_GET['fr'] == 'wxapp') {
            $this->yunset('wxapp', 1);
        }
        $this->yunset('headertitle', '个人信息收集清单');
        $this->yunset('title', '个人信息收集清单');
        $this->yuntpl(array('wap/about'));
    }
    // 第三方信息数据共享
    function sanfang_action()
    {
        $descM      =   $this->MODEL('description');
        $content    =   $descM->getDes(array('name' => '第三方信息数据共享'), array('field' => 'content'));
        $this->yunset('content', $content);
        if ($_GET['fr'] == 'wxapp') {
            $this->yunset('wxapp', 1);
        }
        $this->yunset('headertitle', '第三方信息数据共享');
        $this->yunset('title', '第三方信息数据共享');
        $this->yuntpl(array('wap/about'));
    }
    // 首页名企
    function getmq_action(){
        
        $time = time();
        $companyM   =   $this->MODEL('company');
        
        if ($this->config['sy_web_site']=='1') {
            
            if (!empty($this->config['provinceid'])) {
                $hotcomwhere['provinceid']  =   $this->config['provinceid'];
            }
            if (!empty($this->config['cityid'])) {
                $hotcomwhere['cityid']      =   $this->config['cityid'];
            }
            if (!empty($this->config['three_cityid'])) {
                $hotcomwhere['three_cityid']=   $this->config['three_cityid'];
            }
            if (!empty($this->config['hyclass'])) {
                $hotcomwhere['hy']          =   $this->config['hyclass'];
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
            $hcwhere['limit']       =   $_POST['limit'];
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
        }else{
            $hotcom = array();
        }
        echo json_encode($hotcom);
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

        $videoList          =   $videoM->getList($where, array('video' => 1));

        echo json_encode($videoList);
        exit();
    }
    function getCityDomain_action(){

        $siteM = $this->MODEL('site');
        
        $return = $siteM->getCityDomain(array('x'=>$_POST['x'],'y'=>$_POST['y'],'did'=>$this->config["did"]));
        
        echo json_encode($return);exit();
    }
}

?>