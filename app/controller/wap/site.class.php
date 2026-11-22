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
class site_controller extends common{
    function index_action(){
        if($this->config["sy_web_site"]!="1"){
            $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], $msg = "暂未开启多站点模式！",2,5);
        }

        $siteinfo = getLocalCity();
        if ($siteinfo) {
            if ($siteinfo['mode'] == '2') {
                $siteinfo['host'] = rtrim($this->config['sy_indexdomain'], '/') . '/' . trim($siteinfo['indexdir'], '/') . '/';
            } else {
                $siteinfo['host'] = getprotocol($this->config['sy_weburl']) . $siteinfo['host'];
            }
            $this->yunset('siteinfo', $siteinfo);
            $this->yunset('vurl', $siteinfo['host']);
        }

        $siteCityData = get_site_city_groups();
        $this->yunset('province_groups', $siteCityData['groups']);
        $this->yunset('province_list', $siteCityData['provinces']);
        $localHotCities = array();
        $localProvinceId = $siteinfo && !empty($siteinfo['province']) ? (int)$siteinfo['province'] : 0;
        foreach ($siteCityData['groups'] as $provinces) {
            foreach ($provinces as $province) {
                if (($localProvinceId && $province['id'] == $localProvinceId) || (!$localProvinceId && $province['name'] == '福建')) {
                    $localHotCities = array_slice($province['cities'], 0, 16);
                    break 2;
                }
            }
        }
        if (empty($localHotCities)) {
            $localHotCities = array_slice($siteCityData['cities'], 0, 16);
        }
        $currentCityName = $siteinfo && !empty($siteinfo['cityname'])
            ? $siteinfo['cityname']
            : (!empty($this->config['cityname']) ? $this->config['cityname'] : $this->config['sy_indexcity']);
        $this->yunset('local_hot_cities', $localHotCities);
        $this->yunset('current_city_name', $currentCityName);
        $this->yunset("headertitle","分站");
        $this->seo("index");
        $this->yuntpl(array('wap/site'));
    }
    function cache_action(){
		global $db,$db_config;
		include(PLUS_PATH."domain_cache.php");
		include(PLUS_PATH."config.php");
		
		$hy_site = array();
		foreach($site_domain as $k=>$v){
			if($v['fz_type']=='2' && $v['is_show'] == '1'){
			    
			    if($v['mode']=='2'){
			        $v['url']=$config['sy_weburl'].'/'.$v['indexdir'].'/';
			    }else{
			        $protocol=getprotocol($config['sy_weburl']);
			        $v['url']=$protocol.$v['host'];
			    }
			    $hy_site[]=$v;
			} 
		}
		$cityData = get_site_city_groups();
		$return['province_groups'] = $cityData['groups'];
		$return['provinces'] = $cityData['provinces'];
		$return['cities'] = $cityData['cities'];
		$return['hy'] = $hy_site;
		echo json_encode($return);
	}

    function domain_action(){
        $id=(int)$_POST['id'];
        include(PLUS_PATH."domain_cache.php");
        include(PLUS_PATH."city.cache.php");
        $host =  $this->protocol.$_SERVER['HTTP_HOST'];
        if(strpos($host,$this->config['sy_onedomain'])!==false){
            $domainUrl  = $this->config['sy_onedomain'];
        }else{
            $domainUrlAll  = parse_url($host);
            $domainUrl = $domainUrlAll['host'];
        }

        if($id>=1){
            $domain=array();
   
            foreach($site_domain as $key=>$val){
                if($val['fz_type']==1){
                    // 从最高级开始匹配，匹配到，循环就不再执行
                    if ($val['province']==$id){
                        $domain = $val;
                        break;
                    }elseif ($val['cityid']==$id){
                        $domain = $val;
                        break;
                    }elseif($val['three_cityid']==$id){
                        $domain = $val;
                    }

                }elseif ($val['fz_type']==2 && $val['id']==$id){
                    $domain=$val;
                }
            }
            if($domain){
                $parseDate['did']=$domain['id'];
                $parseDate['fz_type']=$domain['fz_type'];
                if($parseDate['fz_type']=='1'){
                    if($domain['three_cityid']>0){
                        $parseDate['province']		=	$domain['province'];
                        $parseDate['cityid']		=	$domain['cityid'];
                        $parseDate['three_cityid']	=	$domain['three_cityid'];
                        $parseDate['cityname']		=	$city_name[$domain['three_cityid']];
                    }elseif($domain['cityid']>0){
                        $parseDate['province']		=	$domain['province'];
                        $parseDate['cityid']		=	$domain['cityid'];
                        $parseDate['three_cityid']	=	0;
                        $parseDate['cityname']		=	$city_name[$domain['cityid']];
                    }elseif($domain['province']){
                        $parseDate['province']		=	$domain['province'];
                        $parseDate['cityid']		=	0;
                        $parseDate['three_cityid']	=	0;
                        $parseDate['cityname']		=	$city_name[$domain['province']];
                    }
                    setcookies('hyclass',time()-86400,$this->config['sy_onedomain']);
                }else if($parseDate['fz_type']=='2'&&$domain['hy']){
                    $parseDate['hyclass']       =   $domain['hy'];
                    $parseDate['cityname']		=	$domain['webname'];
                    setcookies(
                        array(
                            'province'=>'',
                            'cityid'=>'',
                            'three_cityid'=>''
                        ),time()-86400,"/"
                    );
                }
                if($domain['webname']){$parseDate['sy_webname']  =	$domain['webname'];}
                if($domain['webtitle']){$parseDate['sy_webtitle']  =	$domain['webtitle'];}
                if($domain['weblogo']){$parseDate['sy_logo']  =	$domain['weblogo'];}
                if($domain['webkeyword']){$parseDate['sy_webkeyword']  =	$domain['webkeyword'];}
                if($domain['webmeta']){$parseDate['sy_webmeta']  =	$domain['webmeta'];}
                if($domain['style']){$parseDate['style']  =	$domain['style'];}
                $parseDate['sy_wapurl']  =	$host.'/wap';



                $this->config = array_merge($this->config,$parseDate);

                foreach($parseDate as $key=>$value){
                    $this->cookie->SetCookie($key,$value,time()+86400);
                }
            }
        }
        
        if(!$id){
	        $c = explode(".", $domainUrl);
	        if(count($c) == 3){
	            $domainUrl = str_replace($c[0], 'www', $domainUrl);
	        }
	    }
	  


        if(!$parseDate){
            setcookies(
                array(
                    'sy_wapurl'=>'',
                    'did'=>'',
                    'fz_type'=>'',
                    'province'=>'',
                    'cityid'=>'',
                    'hyclass'=>'',
                    'three_cityid'=>'',
                    'cityname'=>'',
                    'sy_webkeyword'=>'',
                    'sy_logo'=>'',
                    'style'=>'',
                    'sy_webtitle'=>'',
                    'sy_webmeta'=>'',
                    'sy_webname'=>''

                ),time()-86400,$domainUrl);

        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && 
        (strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false ||
         strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)) {
            $parse = parse_url($this->config['sy_wapdomain'].'/');
		    $this->config['sy_wapdomain'] = str_replace($parse['host'],$domainUrl, $this->config['sy_wapdomain'].'/');
		    die(json_encode(['url' =>$this->config['sy_wapdomain']]));
		    exit();
        }
    }
}
?>
