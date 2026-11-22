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
class alipayapi_model extends model{
    
    /**
     * @desc   支付宝就业API 职位推送
     *
     */
    public function jobSync($where = array(), $data = array())
    {
        
        @include(APP_PATH.'/api/aop/AopClient.php');
        @include(APP_PATH.'/api/aop/AopCertification.php');
        @include(APP_PATH.'/api/aop/request/AlipayEbppIndustryJobSyncRequest.php');
        
        include(DATA_PATH.'api/alipay/alipay_aop.php');
        
        $aop = new AopClient ();
        
        //ini_set("display_errors", "On");//打开错误提示
        //ini_set("error_reporting",E_ALL);//显示所有错误
        
        if($alipaydata['sy_synctype'] == '1'){//测试环境
            
            $aop->gatewayUrl = 'http://openapi.stable.dl.alipaydev.com/gateway.do';
            $aop->appId = '2018062260108473';
            $aop->rsaPrivateKey = "MIIEpAIBAAKCAQEAkoIP/hZD0Ms7XhYEsqwwrSN4eeg2zE9iOheTnEWsvZVl/qh80Ug0NMOmC1JNUJ8Lh3IScp5HuSLqNZpC0w+WBOHrI3PKmctXmwZ2pJU7nxrA99U81zoDVzRk/kVI7xFf4wrbKLQM/pRhXQN3QEeFRKCHHiyxeTp68dgo+tDRttu2LiS1mkre3DmtUQjgPnlp5nsF7chwKe0BIz3kLKjvIQyNaVpb6uxqmcH6QDk+hnRb8jkexk6gn+zssEFiTaWbx3iQZl3EGFP+xbpzRotzV4SWfqMLhiYRN8JrdPQlx4WqWRe4uUNT4ym7F0ZdJ+AhtTNmSn4CvACaukbcGiVr8wIDAQABAoIBABx0qJiISImwP4qpqiXt7dJjFk5PPP132bNAywnKOp/j+Zh5ryyS4pjw+zottRiYm4umK1EZzwJ1dpoMqTgHSfMO0jXjkpFGfnB80+Rpm9fuQVahkxyWhvmUEp2UZXMRo9ccT67bMb7soPCI8MuKWKcYqsXAloPeSOzaODNnBaSHQoTdeV879SE/tnK1rYm5vfsPkZQyJ5Bed0hhWHJ6eysoItTeTEKf017cYy5A2qTdIDeEnt2nz5kcgIlGu+bX3K5Fqj+G9FyVUVTHLi4Xlq9XuxeczYZVw4EhfuAgwYzQzpL8orIZXyOIlquV3HoMEeyBOAZ7LvtuCuYMmRV+Q8ECgYEA1c0z/C6hpnb0bxEsXAT/T7A41uuRbMboaxe7rFSRCAUKhrMb/CK5o260YdEvJJYNKAkfgk5slFYBuyugzROYNsR+ISBy6bH7rBjGeWWKb5GT5HVQnQLc4H1T6wg5aZsYZiHV9fztvbYfg0Fgjp0MkDG88158otOjE/q3NGVN9eECgYEAr2y0qpqs2mj+iXpxnlyGmPqjBywh+zsn/Uz/1qDeVJqfyBB2JBKHaFQQwIsdT1dHQ5WpFE64nPPmSxWWACsdO2DFh+LDaqYvf4rk1TrtaROVI48qoM8e60kAodFGRoeQsfgAdwA6xew0q2SeY7EB3aTyEGGdEalQqyDUrN+NNFMCgYEAkK4NWMFBmMF0xdhMOXUURJvOyvmcbozn6NilooeToWav6dk4vdXN49/dZzhEe7Ci9I2/6dEiPvs4rq1mYoC5B47Of6JSwPw1gA8/+BSTqs5L01TkGWw/hhapfCoTl/hheXO6M31WcIPXLV5HdhqmzskpwVMWaqVm/PUzltxa9mECgYEApa5swAjQIoQxixSrBJ9svEVT/m0vNwG7FiBILaTH924GZMUvX4hkfSOQotmA+DN9ahaKl1R2Rj6RDe84ObZ+qFsQlPOWDz8f8zzyPXWnTquhGvGXmEOmdx/+ydZGokxdW8jYX4xG9jHN2OZyNcOy+GxV9yTKXSjluIqMcy28RmMCgYAwY1BDC8rXGSC4bkiuU+0UVWm0BLdRHYC23TaWwfGgbiHz7hB7Z3hI2JJXaJj8MERbI933YRV/3U1QOlvzrOzmzjK5eFVtYEfDRWsZYgYZ/fG0AFISLnxO8usQAiXWkxIuG/GioRGDBthkXbnNhP6IzZZ0r2FMKjMKKYzNd2xvnw==";
            $aop->alipayrsaPublicKey="MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvrrfh6MpywjtSAF9+w2IOUizb+Y0qojIucmADlB6SCQpGYS6j0vSGBsqvk55RQZu3BVgCpwKu3Fe/HH5IGCXb8MYn8rvAhx97otBssVqqS3ruc1RMNmP6Mu8lHnV0pzYESHIHe20ATBdID7+NToV6h7vAKEf3wdyNJBB/nJ7VTvF0HywQ6djsnjySppwg5XTrA2IvWhxHDbRxwSzrvuBw4JDg6U6A4VdrIn54b32FPcCnRZSus+EIXDB2i5+eq6gODPd2gNRHet4mLdOLva0gpmLh7JFX4GYGvwqpgX+x1SFfoBGIsNghG/tU0eTD3p0fA4fA8uQgdNtT35bGn2ogQIDAQAB";
        }elseif($alipaydata['sy_synctype'] == '2'){//预发环境
            
            $aop->gatewayUrl = 'https://openapipre.alipay.com/gateway.do';//预发环境
            $aop->appId = $alipaydata['sy_xcxAppid'];
            $aop->rsaPrivateKey = $alipaydata['sy_privateKey'];
            $aop->alipayrsaPublicKey=$alipaydata['sy_publicKey'];
        }elseif($alipaydata['sy_synctype'] == '3'){//预发环境
            
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';//正式环境
            $aop->appId = $alipaydata['sy_xcxAppid'];
            $aop->rsaPrivateKey = $alipaydata['sy_privateKey'];
            $aop->alipayrsaPublicKey=$alipaydata['sy_publicKey'];
        }else{
            
            return true;
        }
        
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new AlipayEbppIndustryJobSyncRequest ();
        
        //查询符合条件的职位进行推送
        if(empty($where)){//默认读取
            
            $syncday =	$alipaydata['sy_syncday']>0?$alipaydata['sy_syncday']:365;
            
            $syncnum =	$alipaydata['sy_syncday']>0?$alipaydata['sy_syncnum']:1000;
            
            $where['state']			=	1;
            $where['status']		=	0;
            $where['r_status']		=	1;
            $where['lastupdate']	=	array('>=',(time() - $syncday*86400));
            $where['orderby']		=	'lastupdate,desc';
            $where['limit']			=	$syncnum;
        }
        
        $jobList = $this -> select_all('company_job', $where, "`id`,`uid`,`name`,`com_name`,`job1`,`job1_son`, `job_post`,`provinceid`,`cityid`,`three_cityid`,`state`,`sdate`,`status`,`r_status`,`zp_minage`,`zp_maxage`,`zp_num`,`welfare`,`minsalary`,`maxsalary`,`x`,`y`");
        
        if(!empty($jobList)){
            
            @include(PLUS_PATH.'citycode.cache.php');
            @include(PLUS_PATH.'jobsname.cache.php');
            foreach($jobList as $value){
                if($value['link_id']){
                    $link[]  = $value['link_id'];
                }
                $comid[$value['uid']] = $value['uid'];
            }
            if(!empty($link)){
                
                $LinkList = $this->select_all('company_job_link',array('id' =>array('in',implode(',',$link))), 'id,link_man,link_moblie,link_phone');
                
                foreach($LinkList as $value){
                    
                    $linkInfo['id'] = $value;
                }
            }
            if(!empty($comid)){
                
                $comList = $this->select_all('company',array('uid' =>array('in',implode(',',$comid))), '`uid`,`name`,`provinceid`,`cityid`,`three_cityid`,`address`');
                
                foreach($comList as $value){
                    
                    $linkComInfo[$value['uid']] = $value;
                }
            }
            
            foreach($jobList as $value){

                if ($value['job_post'] && $job_sname[$value['job_post']]) {

                    $job_type = $job_sname[$value['job_post']];
                } elseif ($value['job1_son'] && $job_sname[$value['job1_son']]) {

                    $job_type = $job_sname[$value['job1_son']];
                } else {

                    $job_type = $job_sname[$value['job1']];
                }

                if($value['link_id'] && $linkInfo[$value['link_id']]['link_address']){
                    
                    $value['address'] = $linkInfo[$value['link_id']]['link_address'];
                }else{
                    
                    $value['address'] = $linkComInfo[$value['uid']]['address'];
                }

                if ($city_code[$value['three_cityid']]) {

                    $region_code = $city_code[$value['three_cityid']];
                } elseif ($city_code[$value['cityid']]) {

                    $region_code = $city_code[$value['cityid']];
                } elseif ($city_code[$value['provinceid']]) {

                    $region_code = $city_code[$value['provinceid']];
                }
                
                if($value['x'] && $value['y']){
                    
                    $xyArr = $this ->bdToGaode($value['x'],$value['y']);
                    $xy = ' ,"geo":"'.$xyArr['lng'].','.$xyArr['lat'].'"';
                }else{
                    
                    $xy ='';
                }
                
                if($value['max_age']){
                    $age = $value['min_age'].",".$value['max_age'];
                }elseif($value['min_age']){
                    $age = $value['min_age'].",不限";
                }else{
                    $age = "不限,不限";
                }
                
                if($value['maxsalary']){
                    $salary = $value['minsalary'].",".$value['maxsalary'];
                }elseif($value['minsalary']){
                    $salary = $value['minsalary'].",".$value['minsalary'];
                }else{
                    $salary = "";
                }
                
                if($value['welfare']){
                    
                    $welfare = ',job_features:["'.str_replace(',','","',$value['welfare']).'"]';
                }else{
                    
                    $welfare ='';
                }
                if($value['status'] == '1' || $value['state']!=1 || $value['r_status']!=1){
                    
                    $hire_status = 'CLOSED';
                }else{
                    
                    $hire_status = 'HIRING';
                }
                
                if(!$region_code || !$job_type || !$value['address']){
                    continue;
                }
                
                $request->setBizContent("{" .
                    "  \"out_job_id\":\"".$value['id']."\"," .
                    "  \"job_name\":\"".$value['name']."\"," .
                    "  \"job_type\":\"".$job_type."\"," .
                    "  \"work_online\":false," .
                    "  \"address\":{" .
                    "    \"region_code\":\"".$region_code."\"," .
                    "    \"detail\":\"".$value['address']."\"" . $xy .
                    "  }," .
                    "  \"job_detail_url\":\"alipays://platformapi/startapp?appId=".$alipaydata['sy_xcxAppid']."&page=pages/job/show?id=".$value['id']."\"," .
                    "  \"priority\":2," .
                    "  \"employer_type\":\"COMPANY\"," .
                    "  \"employer_name\":\"".$value['com_name']."\"," .
                    "  \"hire_status\":\"".$hire_status."\"," .
                    "  \"start_date\":\"".date('Ymd',$value['sdate'])."\"," .
                    "  \"expired_date\":\"".date('Ymd',(time()+86400*180))."\"," .
                    "  \"part_time_mode\":\"QUANZHI\"," .
                    "  \"age\":\"".$age."\"," .
                    "  \"gender\":\"ALL\"," .
                    "  \"salary\":\"".$salary."\"," .
                    "  \"pay_period\":\"月结\"," .
                    "  \"count\":10" .$welfare.
                    "}");
                
                $result = $aop->execute ( $request);
                
                $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;
                
                if(!empty($resultCode)&&$resultCode == 10000){
                    //测试环境下 需要打印接口返回信息
                    if($alipaydata['sy_synctype'] == '1'){
                        print_r($result);
                    }
                    
                    file_put_contents(APP_PATH."sync_success.txt",$value['id']." ".$value['name']." ".$value['com_name'] ." ".$result->$responseNode->sub_msg." ".$result->$responseNode->job_id." \n",FILE_APPEND);
                    
                    echo $value['id']." ".$value['name']." ".$value['com_name']." 成功! ".date('H:i:s')."<pre>";
                } else {
                    //非正式环境下 错误信息拦截
                    if($alipaydata['sy_synctype'] != '3'){
                        print_r($result);die;
                    }

                    file_put_contents(APP_PATH."sync_fail.txt", $result->$responseNode->code." ".$result->$responseNode->sub_msg." ".$value['id']." \n",FILE_APPEND);
                    
                    echo $value['id']." ".$value['name']." ".$value['com_name']." 失败! ".date('H:i:s')."<pre>";
                }
            }
        }
    }
    
    /*职位申请 应聘信息同步
     * jobid 投递职位ID
     * sqid  投递记录ID
     * squid 投递用户UID
     * isbrowser 区分是投递 还是 投递状态变更 1为变更状态为 沟通中
     */
    public function jobSqSync($data = array())
    {
        
        if($data['jobid'] && $data['sqid']){
            
            
            @include_once(APP_PATH.'/api/aop/AopClient.php');
            @include_once(APP_PATH.'/api/aop/AopCertification.php');
            @include_once(APP_PATH.'/api/aop/request/AlipayEbppIndustryJobApplySyncRequest.php');
            @include_once(APP_PATH.'/api/aop/request/AlipaySystemOauthTokenRequest.php');
            include(DATA_PATH.'api/alipay/alipay_aop.php');
            
            $aop = new AopClient ();
            
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = $alipaydata['sy_xcxAppid'];
            $aop->rsaPrivateKey = $alipaydata['sy_privateKey'];
            $aop->alipayrsaPublicKey=$alipaydata['sy_publicKey'];
            
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='UTF-8';
            $aop->format='json';
            
            if($data['isbrowser'] =='1'){
                
                //状态变更为沟通中
                $apply_status = 'PROCESSING';
                if($data['squid']){
                    $memberInfo = $this -> select_once('member',array('uid'=>(int)$data['squid']),"`uid`,`zfbtoken`");
                    $data['access_token'] = $memberInfo['zfbtoken'];
                }
            }else{
                
                //首次投递 需要用户授权拿到token进行调用
                $apply_status = 'APPLYING';
                if(!empty($data['access_token'])){
                    
                    $this -> update_once('member',array('zfbtoken'=>$data['access_token']),array('uid'=>$data['squid']));
                }else{
                    //1 获取用户user_id
                    $t_request = new AlipaySystemOauthTokenRequest();
                    $t_request->setGrantType("authorization_code");
                    $t_request->setCode($data['code']);
                    $t_result = $aop->execute($t_request);
                    
                    //file_put_contents(APP_PATH."zfb.txt",json_encode($t_result)." 获取user_id \n",FILE_APPEND);
                    
                    $responseNode = str_replace(".", "_", $t_request->getApiMethodName()) . "_response";
                    $resultCode = $t_result->$responseNode->code;
                    $data['access_token'] = $t_result->$responseNode->access_token;
                    
                    if(isset($data['access_token'])){
                        
                        $this -> update_once('member',array('zfbtoken'=>$data['access_token']),array('uid'=>$data['squid']));
                    }
                }
                
                //file_put_contents(APP_PATH."zfb.txt",$data['access_token']." access_token数据 \n",FILE_APPEND);
            }
            
            //获取授权token 同步应聘信息
            if($data['access_token']){
                
                if($alipaydata['sy_synctype'] != '3'){//预发环境
                    
                    $aop->gatewayUrl = 'https://openapipre.alipay.com/gateway.do';//预发环境
                }
                
                unset($data['code']);
                
                $request = new AlipayEbppIndustryJobApplySyncRequest();
                
                $biz = "{" .
                    "  \"out_apply_id\":\"".$data['sqid']."\"," .
                    "  \"out_job_id\":\"".$data['jobid']."\"," .
                    "  \"apply_status\":\"".$apply_status."\"," .
                    "  \"apply_time\":\"".date('Y-m-d H:i:s')."\"," .
                    "  \"apply_info_url\":\"alipays://platformapi/startapp?appId=".$alipaydata['sy_xcxAppid']."&page=pages/job/show?id=".$data['jobid']."\"" .
                    "}";
                
                $request->setBizContent("{" .
                    "  \"out_apply_id\":\"".$data['sqid']."\"," .
                    "  \"out_job_id\":\"".$data['jobid']."\"," .
                    "  \"apply_status\":\"".$apply_status."\"," .
                    "  \"apply_time\":\"".date('Y-m-d H:i:s')."\"," .
                    "  \"apply_info_url\":\"alipays://platformapi/startapp?appId=".$alipaydata['sy_xcxAppid']."&page=pages/job/show?id=".$data['jobid']."\"" .
                    "}");
                
                $result = $aop->execute ( $request , $data['access_token'] );
                
                $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;
                if(!empty($resultCode)&&$resultCode == 10000){
                    if($alipaydata['sy_synctype'] != '3'){
                        
                        file_put_contents(APP_PATH."zfb.txt",$data['jobid']." 成功 \n",FILE_APPEND);
                    }else{
                        
                        return true;
                    }
                } else {
                    
                    if($alipaydata['sy_synctype'] != '3'){
                        
                        file_put_contents(APP_PATH."zfb.txt",$biz." 失败1 \n",FILE_APPEND);
                        file_put_contents(APP_PATH."zfb.txt",json_encode($aop)." 失败1 \n",FILE_APPEND);
                        file_put_contents(APP_PATH."zfb.txt",json_encode($result)." 失败1 \n",FILE_APPEND);
                    }else{
                        
                        return true;
                    }
                }
            }else{
                if($alipaydata['sy_synctype'] != '3'){
                    
                    file_put_contents(APP_PATH."zfb.txt",json_encode($aop)." 失败2 \n",FILE_APPEND);
                    file_put_contents(APP_PATH."zfb.txt",json_encode($t_result)." 失败2 \n",FILE_APPEND);
                }else{
                    
                    return true;
                }
            }
        }
    }
    
    function bdToGaode($longitude, $latitude)
    {
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $longitude - 0.0065;
        $y = $latitude - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $longitude = $z * cos($theta);
        $latitude = $z * sin($theta);
        $data['lng'] = round($longitude, 6);
        $data['lat'] = round($latitude, 6);
        return $data;
    }
	/**
     * 支付宝发送订阅消息
     *
     */
    public function sendTemplate($data = array())
    {
        
        @include(APP_PATH.'/api/aop/AopClient.php');
        @include(APP_PATH.'/api/aop/request/AlipayOpenAppMiniTemplatemessageSendRequest.php');
        @include(DATA_PATH."api/alipay/alipay_aop.php");
        
        $aop = new AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $alipaydata['sy_xcxAppid'];
        $aop->rsaPrivateKey = $alipaydata['sy_privateKey'];
        $aop->alipayrsaPublicKey = $alipaydata['sy_publicKey'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='utf-8';
        $aop->format='json';
        
        $biz = array(
            'page' => $data['url'],
            'data' => $data['tpl_data'],
            'to_user_id' => $data['userid'],
            'user_template_id' => '9ffd612c22504ec8bce1422704feb6f3'
        );
        $request = new AlipayOpenAppMiniTemplatemessageSendRequest();
        $request->setBizContent(json_encode($biz));
        
        $result = $aop->execute($request);
        
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            
            return false;
            
        } else {
            
            return array('msg'=>$result->$responseNode->sub_msg);
        }
    }
    /**
     * 芝麻工作证-职得。信用服务开通/授权
     *
     */
    public function zhimaAgreement($data = array())
    {
        @include(APP_PATH.'/api/aop/AopClient.php');
        @include(APP_PATH.'/api/aop/request/ZhimaCreditPayafteruseCreditagreementSignRequest.php');
        @include(DATA_PATH."api/alipay/alipay_aop.php");
        
        $aop = new AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $alipaydata['sy_xcxAppid'];
        $aop->rsaPrivateKey = $alipaydata['sy_privateKey'];
        $aop->alipayrsaPublicKey = $alipaydata['sy_publicKey'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='utf-8';
        $aop->format='json';
        
        $biz = array(
            'out_agreement_no' => $data['out_agreement_no'],
            'zm_service_id'    => $alipaydata['zm_service_id'],
            'category_id'      => 'ZHIMA_AUTH',
            'product_code'     => 'ZHIMA_AUTH',
            'extra_param'      =>  array(
                'kaAppContext' => array(
                    'kaJobId'        => $data['jobid'],
                    'onceToken'      => $data['onceToken'],
                    'requireModules' => 'BASIC_INFO,PHONE,WORK_EXPERIENCE,EDUCATION_DEGREE',
                    'sourceFrom'     => 'CLOUDRESUME'
                ),
                'moduleScene' => 'EMPLOYMENT'
            )
        );
        $request = new ZhimaCreditPayafteruseCreditagreementSignRequest();
        $request->setBizContent(json_encode($biz));
        
        $result = $aop->pageExecute($request, 'GET');
        
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(isset($result->$responseNode->sub_msg)){
            
            return array('msg'=>$result->$responseNode->sub_msg);
            
        } else {
            
            $arr = parse_url($result);
            return $arr['query'];
        }
    }
    /**
     * 芝麻工作证-职得。智能简历基本数据查询
     *
     */
    public function zhimaJobworth($data = array())
    {
        @include(APP_PATH.'/api/aop/AopClient.php');
        @include(APP_PATH.'/api/aop/request/ZhimaCustomerJobworthCloudresumeQueryRequest.php');
        @include(DATA_PATH."api/alipay/alipay_aop.php");
        
        $aop = new AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $alipaydata['sy_xcxAppid'];
        $aop->rsaPrivateKey = $alipaydata['sy_privateKey'];
        $aop->alipayrsaPublicKey = $alipaydata['sy_publicKey'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='utf-8';
        $aop->format='json';
        
        $biz = array(
            'conn_key'  => $data['out_agreement_no'],
            'once_token' => $data['onceToken']
        );
        $request = new ZhimaCustomerJobworthCloudresumeQueryRequest();
        $request->setBizContent(json_encode($biz));
        
        $result = $aop->execute($request);
        
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            // 职位数据
            $jobid = intval($data['jobid']);
            $jobfield = '`com_name`,`name`,`uid`,`is_link`,`is_message`,`is_email`,`hy`,`job1`,`job1_son`,`job_post`,`provinceid`,`cityid`,`three_cityid`,`minsalary`,`maxsalary`';
            $comjob	= $this->select_once('company_job', array('id'=>$jobid),$jobfield);
            // 处理简历数据
            $res = $result->$responseNode;
            if(empty($res->user_name)){
                return array('msg'=>'职得工作证未授权基本信息');
            }
            if (!empty($res->phone)){
                $member = $this->select_once('member', array('moblie' => $res->phone));
                if (!empty($member)) {
                    // 有账号
                    if ($member['usertype'] != 1){
                        
                        $marr  =  array(
                            'uid'       =>  $member['uid'],
                            'usertype'  =>  1,
                            'opera'     =>  11,
                            'type'      =>  1,
                            'ip'        =>  fun_ip_get(),
                            'ctime'     =>  time(),
                            'did'       =>  $member['did']
                        );
                        $rnum = $this->select_num('resume', array('uid' => $member['uid']));
                        if ($rnum == 0){
                            // 当前没有个人身份，增加个人身份
                            include('userinfo.model.php');
                            $userinfoM  =  new userinfo_model($this->db, $this->def);
                            
                            $userinfoM->activUser($member['uid'],1,$member);
                            
                            $marr['content'] = '用户通过职得工作证，增加个人身份';
                        }else{
                            $marr['content'] = '用户通过职得工作证，切换为个人身份';
                            $this->update_once('member', array('usertype'=>1), array('uid' => $member['uid']));
                        }
                        // 添加会员日志
                        $this->insert_into('member_log',$marr);
                    }
                    // 有账号，需查下是否已投递
                    $shenqing = $this->select_once('userid_job', array('uid' => $member['uid'], 'job_id' => $jobid, 'orderby' => 'id,desc'));
                    if(!empty($shenqing)){
                        if($shenqing['isdel'] == 9 && $shenqing['is_browse'] != '6'){
                            
                            return array('msg'=>'您已经投递过该职位，请不要重复投递');
                        }else{
                            // 判断简历投递重复周期
                            $sq_resume_interval = $this->config['sq_resume_interval'];
                            if ($sq_resume_interval && $sq_resume_interval > 0) {
                                if(strtotime("+{$sq_resume_interval} day", $shenqing['datetime']) >= time()){
                                    return array('msg'=>'您最近申请过该职位，请勿重复投递');
                                }
                            }
                        }
                    }
                    $userid = $member['uid'];
                    $token          = md5($member['username'].$member['password'].$member['salt'].'1');
                    $return['user'] = array('uid'=>$userid,'usertype'=>1,'token'=>$token);
                }else{
                   // 没有账号,注册新账号
                    $ip     =  fun_ip_get();
                    $time   =  time();
                    $salt   =  substr(uniqid(rand()),-6);
                    $rand   =  mt_rand(111111,999999);
                    
                    $mdata  =  array(
                        'username'       =>  $res->phone,
                        'password'       =>  passCheck($rand,$salt),
                        'salt'           =>  $salt,
                        'moblie'         =>  $res->phone,
                        'moblie_status'  =>  1,
                        'usertype'       =>  1,
                        'reg_date'       =>  $time,
                        'reg_ip'         =>  $ip,
                        'login_date'     =>  $time,
                        'login_ip'       =>  $ip,
                        'status'         =>  1,
                        'source'         =>  $data['source'],
                        'did'            =>  $this->config['did'],
                        'login_address'  =>  getIpAddress($ip),
                        'moblie_address' =>  getMoblieAddress($res->phone),
                    );
                    $userid  =  $this->insert_into('member', $mdata);
                    
                    if ($userid){
                        require_once('warning.model.php');
                        $warningM       =   new warning_model($this->db, $this->def);
                        $warningM->warning(12, $userid,$ip);//预警提醒
                        
                        //处理注册赠送优惠券
                        if($this->config['reg_coupon']){
                            $coupon						=		$this->select_once('coupon',array('id'=>$this->config['reg_coupon']));
                            if($coupon&&is_array($coupon)){
                                $cdata['uid']				=		$userid;
                                $cdata['number']			=		$time;
                                $cdata['ctime']				=		$time;
                                $cdata['coupon_id']			=		$coupon['id'];
                                $cdata['coupon_name']		=		$coupon['name'];
                                $cdata['validity']			=		$time+$coupon['time']*86400;
                                $cdata['coupon_amount']		=		$coupon['amount'];
                                $cdata['coupon_scope']		=		$coupon['scope'];
                                $this->insert_into('coupon_list',$cdata);
                            }
                        }
                        // 增加个人身份
                        require_once('userinfo.model.php');
                        $userinfoM  =  new userinfo_model($this->db, $this->def);
                        
                        $userinfoM->activUser($userid,1,array(), true);
                        
                        $token          = md5($mdata['username'].$mdata['password'].$salt.'1');
                        $return['user'] = array('uid'=>$userid,'usertype'=>1,'token'=>$token);
                        
                    }else{
                        //增加错误日志
                        require_once ('errlog.model.php');
                        $errlogM = new errlog_model($this->db, $this->def);
                        $errlogM -> addErrorLog('', 1, '通过工作证注册账号失败');
                        
                        return array('msg'=>'通过工作证注册账号失败');
                    }
                }
                require_once('resume.model.php');
                $resumeM  =  new resume_model($this->db, $this->def);
                // 查询是否有简历
                $enum = $this->select_num('resume_expect', array('uid' => $userid));
                if ($enum == 0){

                    // 没有简历，通过智能简历基本数据查询返回数据，创建简历
                    include PLUS_PATH."user.cache.php";
                    include PLUS_PATH."job.cache.php";

                    // 匹配工作经验
                    foreach($userdata['user_word'] as $v){
                        $user_word[$v]	=	$userclass_name[$v];
                    }

                    if (isset($res->work_start_date)){

                        $workYear   =   date('Y')-date('Y',substr($res->work_start_date, 0, 11));
                        $exp    =   $this->locoytostr($user_word, $workYear);
                    }

                    $experience =   isset($res->work_start_date) ? 1 : 2;

                    //简历工作经历数据
                    $workData   =   array();
                    if (!empty($res->work_experiences)) {
                        $minWorkStart   =   0;
                        foreach ($res->work_experiences as $v) {
                            $workData[] =   array(
                                'uid'       =>  $userid,
                                'name'      =>  $v->job_name,
                                'sdate'     =>  substr($v->work_start_time, 0, 10),
                                'edate'     =>  $v->work_end_time != '-1' ? substr($v->work_end_time, 0, 10) : 0,
                                'title'     =>  $v->company_name,
                                'content'   =>  $v->work_desc
                            );
                            $minWorkStart   =   $minWorkStart == 0 || $minWorkStart > $v->work_start_time ? $v->work_start_time : $minWorkStart;
                        }
                        if ($minWorkStart > 0 && $experience == 2) {

                            $workYear = date('Y') - date('Y', substr($minWorkStart, 0, 11));
                            $exp    =   $this->locoytostr($user_word, $workYear);
                        }
                    }

                    // 匹配学历
                    foreach($userdata['user_edu'] as $v){
                        $user_edu[$v]	=	$userclass_name[$v];
                    }
                    $eduData    =   array();
                    //简历教育经历数据
                    if (!empty($res->education_experiences)){
                        foreach ($res->education_experiences as $v){
                            $education = '';
                            if (!empty($v->degree)){
                                $degreeStr  =   $this->degreeToStr($v->degree);
                                $education  =   $this->locoytostr($user_edu, $degreeStr);
                            }
                            $month = strlen($v->month) == 1 ? '0'.$v->month : $v->month;
                            $eduData[] = array(
                                'uid'       => $userid,
                                'name'      => $v->school_name,
                                'sdate'     => strtotime(($v->year-3).'0901'),
                                'edate'     => strtotime($v->year.$month.'01'),
                                'specialty' => $v->major,
                                'education' => $education
                            );
                        }
                    }
                    
                    $rData = array(
                        'name' 		=> $res->user_name,
                        'sex' 		=> $res->gender == '男' ? 1 : 2,
                        'birthday' 	=> date('Y-m', strtotime($res->birthday)),
                        'edu' 		=> isset($education) ? $education : array_shift($user_edu), // 没取到值，改成取第一个
                        'exp' 		=> !empty($exp) ? $exp : array_shift($user_word), // 没取到值，改成取第一个
                        'telphone' 	=> $res->phone,
                        'login_date'=> time(),
                        'statusbody'=> '职得工作证创建简历'
                    );
                    //简历求职意向数据
                    if ($comjob['job_post']) {
                        $job_classid = $comjob['job_post'];
                    } elseif ($comjob['job1_son']) {
                        $job_classid = $comjob['job1_son'];
                    } else {
                        $job_classid = $comjob['job1'];
                    }
                    if ($comjob['three_cityid']){
                        $city_classid =	$comjob['three_cityid'];
                    }elseif($comjob['cityid']){
                        $city_classid =	$comjob['cityid'];
                    }else{
                        $city_classid =	$comjob['provinceid'];
                    }
                    $eData = array(
                        'lastupdate' 	=> time(),
                        'height_status' => 0,
                        'uid' 			=> $userid,
                        'ctime' 		=> time(),
                        'name' 			=> $job_name[$job_classid],
                        'hy' 			=> $comjob['hy'],
                        'job_classid' 	=> $job_classid,
                        'city_classid' 	=> $city_classid,
                        'minsalary' 	=> $comjob['minsalary'],
                        'maxsalary' 	=> $comjob['maxsalary'],
                        'type' 			=> $userdata['user_type'][0],
                        'report' 		=> $userdata['user_report'][0],
                        'jobstatus' 	=> $userdata['user_jobstatus'][0],
                        'state' 		=> resumeTimeState($this->config['resume_status']),
                        'r_status' 		=> 1,
                        'edu' 			=> $rData['edu'],
                        'exp' 			=> $rData['exp'],
                        'sex' 			=> $rData['sex'],
                        'birthday' 		=> $rData['birthday'],
                        'source' 		=> $data['source']
                    );
                    $addArr	= 	array(
                        'uid' 		=> $userid,
                        'rData' 	=> $rData,
                        'eData' 	=> $eData,
                        'workData' 	=> $workData,
                        'eduData' 	=> $eduData,
                        'utype' 	=> 'user'
                    );
                    
                    $add = $resumeM->addInfo($addArr);
                    $eid = $add['id'];
                }else{
                    $expect = $resumeM->getExpectByUid($userid);
                    $eid = $expect['id'];
                }
                // 申请职位
                $value	=	array(
                    'job_id'	=>	$jobid,
                    'com_name'	=>	$comjob['com_name'],
                    'job_name'	=>	$comjob['name'],
                    'com_id'	=>	$comjob['uid'],
                    'uid'		=>	$userid,
                    'eid'		=>	$eid,
                    'resume_state'=>$eData['state'],
                    'datetime'	=>	time()
                );
                include('job.model.php');
                $jobM  =  new job_model($this->db, $this->def);
                $nid = $jobM->addSqJob($value, array('comjob'=>$comjob));
                // 职位申请 应聘信息同步
                $apidata = array(
                    'sqid'  => $nid,
                    'squid' => $userid,
                    'jobid' => $jobid,
                    'code'  => $data['code']
                );
                $this->jobSqSync($apidata);
                
                return $return;
            }else{
                return array('msg'=>'支付宝未绑定手机号');
            }
        } else {
            
            return array('msg'=>$result->$responseNode->sub_msg);
        }
    }
    /**
     * 字符串相似度匹配
     * @param $arr 类别
     * @param $str 要匹配的字符串
     * @param $isarr 返回单个还是数组
     */
    function locoytostr($arr, $str, $isarr = false,$locoy_rate = 60)
    {
        $str_array = $this->tostring($str);
        
        if (is_array($str_array)) {
            foreach ($arr as $key => $list) {
                if (is_numeric($str)){
                    // 如果要处理的字符串是全是数字，要过滤掉缓存中的汉字
                    $list = preg_replace('/([\x80-\xff]*)/i','',$list);
                }
                $h = 0;
                foreach ($str_array as $val) {
                    if (substr_count($list, $val))
                        $h ++;
                }
                $categoryname_array = $this->tostring($list);
                $j = round($h / count($categoryname_array) * 100, 2);
                if($j >= $locoy_rate && $j<=100){
                    $rows[$key] = $j;
                }
            }
            arsort($rows);
            // 返回要返回单个还是数组
            if ($isarr){
                return $rows;
            }else{
                return key($rows);
            }
        } else {
            return false;
        }
    }
    function tostring($string)
    {
        $length = strlen($string);
        $retstr = array();
        for ($i = 0; $i < $length; $i ++) {
            $retstr[] = ord($string[$i]) > 127 ? $string[$i] . $string[++ $i] : $string[$i];
        }
        return $retstr;
    }

    /**
     * 学历枚举值转换中文字符
     * @param string $degree
     * @return string
     */
    function degreeToStr($degree = 'BACHELOR')
    {

        $degreeArr  =   array(
            'PRIMARY_SCHOOL'        => '小学',
            'JUNIOR_HIGH_SCHOOL'    => '初中',
            'SENIOR_HIGH_SCHOOL'    => '高中',
            'HIGHSCHOOL_AND_BELOW'  => '高中及以下',
            'POLYTECHNIC'           => '中专',
            'COLLEGE'               => '大专',
            'BACHELOR'              => '本科',
            'MASTER'                => '硕士',
            'DOCTOR_AND_ABOVE'      => '博士及以上',
            'OTHERS'                => '其他'
        );

        return $degreeArr[$degree];
    }
}
?>