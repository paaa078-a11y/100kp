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
 $script = __FILE__;
if (function_exists('opcache_invalidate')) {
    opcache_invalidate($script, true); // true = force reload
}
class index_controller extends common{
    /**
     * 首页
     */
	function index_action(){
		
		//是否加载区域分站
		if(!$this->config['did'] && $this->config['sy_gotocity']=='1' && !$_COOKIE['gotocity']){


			go_to_city($this->config);//自动跳转分站

		}
		
		if($this->config['sy_jobdir']!=""){

			$jobclassurl	=	$this->config['sy_weburl']."/job/index.php?c=search&";

		}else{

			$jobclassurl	=	$this->config['sy_weburl']."/index.php?m=job&c=search&";

		}

		$this->yunset("jobclassurl",$jobclassurl);
	
		$where['status']	=	1;
		$where['r_status']	=	1;
		$where['rec_resume']=	1;
		if($this->config['did']>0){
			$where['did']	=	$this->config['did'];
		}else{
			$where['PHPYUNBTWSTART']=	'';
			$where['did'][]	=	array('isnull');
			$where['did'][]	=	array('=','0','OR');
			$where['PHPYUNBTWEND']	=	'';
		}
		$resumeM	=	$this->MODEL('resume');
		$topnum 	= 	$resumeM->getExpectNum($where);

		$this->yunset("topnum",$topnum);
		
		$CacheM=$this->MODEL('cache');
		
		$CacheList=$CacheM->GetCache(array('job','city','com'));
		
		$this->yunset($CacheList);

		//首页弹框记录
		$cookieM      =   $this->MODEL('cookie');
        $bannerFlag   =   $_COOKIE['pc_bannerFlag'];
        if (!$bannerFlag) {
        	$cookieM -> setcookie('pc_bannerFlag', true, time() + 3600);
        }
		$this->yunset("bannerFlag", $bannerFlag);

        if ($this->config['sy_web_city_one']) {
            $_GET['provinceid'] = 	$this->config['sy_web_city_one'];
        }
        if ($this->config['sy_web_city_two']) {
            $_GET['cityid'] 	= 	$this->config['sy_web_city_two'];
        }

		if($this->config["did"]){

			$this->seo("index",$this->config['sy_webtitle'],$this->config['sy_webkeyword'],$this->config['sy_webmeta']);

			$CacheM					=				$this->MODEL('cache');

			$DomainList				=				$CacheM->GetCache(array('domain'));

			$site_domain			=				$DomainList['site_domain'];

			if(is_array($site_domain)){

				foreach($site_domain as $d){

					if($d['id']==$this->config["did"]){

						$domain['tpl']	=	$d['tpl'];

					}

				}

			}

			if($domain['tpl']!=""){

				$tpl	=	@explode(".",$domain['tpl']);

				$this->yun_tpl(array($tpl[0]));

			}else{

				$this->yun_tpl(array('siteindex'));

			}

		}else{

			$this->seo("index");

			$this->yun_tpl(array('index'));

		}

	}

    /**
     * 排行榜
     */
	function top_action(){

		$this->seo("top");

		$this->yun_tpl(array('top'));

	}

    /**
     * 手机浏览
     */
	function moblie_action(){

		$this->seo("moblie");

		$this->yun_tpl(array('moblie'));

	}

    /**
     * WAP站引导页
     */
	function wap_action(){

		$this->seo("wap");

		$this->yun_tpl(array('wap'));

	}

    /**
     * 微信公众号关注引导页
     */
	function weixin_action(){

		$this->seo("weixin");

		$this->yun_tpl(array('weixin'));

	}

    /**
     * 小程序引导页
     */
	function xcx_action(){

		$this->seo("xcx");

		$this->yun_tpl(array('android'));

	}

    /**
     * APP下载引导页
     */
	function app_action(){

		$this->seo("app");

		$this->yun_tpl(array('ios'));

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
        
        $did = isset($this->config['did']) ? $this->config['did'] : 0;
	    $whereArticle['limit'] = 100;
        $whereArticle['orderby'] = array('datetime,desc');
        if(!$isMaster){
            $whereArticle['orderby']      = array('did,desc', 'datetime,desc');
            $whereArticle['did'][]        =   array('=', $did);
            $whereArticle['did'][]        =   array('=', '-1', 'OR');
        }
  
	    $nlist = $this->MODEL('article')->getList($whereArticle, array('field' => "`id`,`datetime` as `time`,`datetime`"));
	
	   // var_dump($this->MODEL('article'));exit();
        $rows['news'] = $nlist['list'];

	   
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
         // 设置默认更新频率（如果后台未设置则用 daily）
        $frequency = !empty($_POST['frequency']) ? $_POST['frequency'] : 'daily';

        $show = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";
        if (is_array($rows)) {
            foreach ($rows as $key => $v) {
        if ($key == 'company') {
            foreach ($v as $val) {
                $url = Url('company', array('c' => 'show', "id" => $val['id']));
                $url = str_replace("&", "&amp;", $url);
                $show .= "<url><loc>" . $url . "</loc><lastmod>" . date("Y-m-d\TH:i:s\Z", $val['time']) . "</lastmod><changefreq>" . $frequency . "</changefreq></url>\r\n";
            }
        }
        if ($key == 'job') {
            foreach ($v as $val) {
                $url = Url("job", array("c" => "comapply", "id" => $val['id']));
                $url = str_replace("&", "&amp;", $url);
                $show .= "<url><loc>" . $url . "</loc><lastmod>" . date("Y-m-d\TH:i:s\Z", $val['time']) . "</lastmod><changefreq>" . $frequency . "</changefreq></url>\r\n";
            }
        }
        if ($key == 'news' && $this->config["sy_news_rewrite"] == '2') {
            foreach ($v as $val) {
                $url = $this->config['sy_weburl'] . "/news/" . date("Ymd", $val["datetime"]) . "/" . $val['id'] . ".html";
                $show .= "<url><loc>" . $url . "</loc><lastmod>" . date("Y-m-d\TH:i:s\Z", $val['time']) . "</lastmod><changefreq>" . $frequency . "</changefreq></url>\r\n";
            }
        }
        if ($key == 'news' && $this->config["sy_news_rewrite"] != '2') {
            foreach ($v as $val) {
                $url = Url("article", array("c" => "show", "id" => $val['id']));
                $url = str_replace("&", "&amp;", $url);
                $show .= "<url><loc>" . $url . "</loc><lastmod>" . date("Y-m-d\TH:i:s\Z", $val['time']) . "</lastmod><changefreq>" . $frequency . "</changefreq></url>\r\n";
            }
        }
        if ($key == 'resume') {
            foreach ($v as $val) {
                $url = Url("resume", array("c" => 'show', "id" => $val['id']));
                $url = str_replace("&", "&amp;", $url);
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
     * 站点切换
     */
	function site_action(){

		if($this->config["sy_web_site"]!="1"){

			$this->ACT_msg($_SERVER['HTTP_REFERER'], $msg = "暂未开启多站点模式！");

		}
		
		$siteinfo				=				getLocalCity();

		if($siteinfo){
			
	
			$site				=				1;
			if($siteinfo['mode']=='2'){
						
				$siteinfo['host']=$this -> config['sy_indexdomain'].'/'.$siteinfo['indexdir'].'/';
			}else{
				$siteinfo['host']='http://'.$siteinfo['host'];
			}
			$this->yunset("site",$site);

			$this->yunset("vurl",$siteinfo['host']);

			$this->yunset("siteinfo",$siteinfo);

		}

		$this->seo("index");

		$this->yun_tpl(array('site'));

	}

    /**
     * 退出登陆
     */
	function logout_action(){

		$this->logout();

	}

    /**
     * 资讯详情-浏览量
     */
	function GetHits_action(){
    	if($_GET['id']){

			$articleM		=		$this->MODEL('article');

			$articleM->upBase(array('id'=>(int)$_GET['id']),array('hits'=>array('+',1)));
				
			$info			=		$articleM->getInfo(array('id'=>(int)$_GET['id']),array('field'=>'hits'));
			
			echo "document.write('".$info["hits"]."')";
			
		}
		
    }

    /**
     * 获取单页内容
     */
	function get_action(){

		$descriptionM				=		$this->MODEL('description');
		
		session_start();

		if($_SESSION['auid']==""){

			$row					=		$descriptionM->getDes(array("id"=>(int)$_GET['id'],"is_nav"=>'1'));

		}else{

			$row					=		$descriptionM->getDes(array("id"=>(int)$_GET['id']));

		}
		
		$top						=		"";

		$footer						=		"";
		
		if(is_array($row)){

			if($row['top_tpl']==1){

				$top				=		APP_PATH."/app/template/".$this->config['style']."/header";

			}else if($row['top_tpl']==3){

				 $top				=		APP_PATH."/app/template/".$row['top_tpl_dir'];

			}
			if($row['footer_tpl']==1){

				$footer				=		APP_PATH."/app/template/".$this->config['style']."/footer";

			}else if($row['footer_tpl']==3){

				$footer				=		APP_PATH."/app/template/".$row['footer_tpl_dir'];

			}
			$seo['title']			=		$row['title'];

			$seo['keywords']		=		$row['keyword'];

			$seo['description']		=		$row['descs'];

			$this->yunset("seo",$seo);
			
			$this->yunset("name",$row['name']);

			$this->yunset("content",$row['content']);

			$this->header_desc($row['title'],$row['keyword'],$row['descs']);
			
			$make					=		APP_PATH."/app/template/".$this->config['style']."/make";

			$make_top				=		APP_PATH."/app/template/".$this->config['style']."/make_top";
			
			$this->yuntpl(array($make_top,$top,$make,$footer));

		}else{

			$this->ACT_msg($this->config['sy_weburl'],"请求页面不存在！");
		}
	}

    /**
     * 广告记录
     */
    function clickhits_action()
    {

        if ((int)$_GET['id']) {

            $adM    =   $this->MODEL("ad");

            $id     =   (int)$_GET['id'];

            $ad     =   $adM->getInfo(array('id' => $id), array("field" => "pic_src,id"));

            if (!empty($ad)) {

                $ip =   fun_ip_get();

                if ($this->config['sy_adclick'] > 0) {

                    $num    =   $adM->getAdClickNum(array('ip' => $ip, 'aid' => $id, 'addtime' => array('>', strtotime('-' . $this->config['sy_adclick'] . ' hour'))));

                    if ($num > "0") {

                        header('Location: ' . $ad['pic_src']);
                    }
                }

                $data['aid']    =   $id;
                $data['uid']    =   $this->uid;
                $data['ip']     =   $ip;
                $data['addtime']=   time();
                $nid    =   $adM->addAdClick($data);

                if ($nid) {

                    $adM->upInfo(array('id' => $id), array('hits' => array('+', 1)));
                }
                if (!$ad['pic_src']) {

                    $ad['pic_src']  =   $this->config['sy_weburl'];
                }
                header('Location: ' . $ad['pic_src']);
            }
        }
    }

    /**
     * 跳转未查看简历页面前置处理
     */
    public function nlresume_action()
    {
        if ($this->uid) {
            if ($this->usertype == 2) {// 直接跳转未查看
                if(isMobileUser()){
                    header('Location:' . Url('wap', array('c' => 'hr', 'is_browse' => 1), 'member'));
                } else {
                    header('Location:' . Url('member', array('c' => 'hr', 'state' => 1)));
                }
            } else {
                if(isMobileUser()) {
                    $this->ACT_msg_wap(Url("wap"), "当前登录用户非企业用户", 2, 5);
                } else {
                    $this->ACT_msg('', '当前登录用户非企业用户');
                }
            }
        } else { // 未登录，跳转登录页
            $this->cookie->setcookie('resume_no_look', 1, time()+600); // 记录未查看简历标识 10分钟
            header('Location:' . Url('login'));
        }
    }

}
?>