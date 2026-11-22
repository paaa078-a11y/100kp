<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2022 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class map_controller extends wxapp_controller
{
	//附近职位列表
	function list_action(){
	    $x	=	$_POST['x'];
	    $y	=	$_POST['y'];
	    // 根据sql距离计算公式计算距离并排序
        $select =   "`id`,`uid`,`name`,`x`,`y`,`provinceid`,`cityid`,`three_cityid`,`com_logo`,`exp`,`edu`,`minsalary`,`welfare`,`maxsalary`,`lastupdate`, `is_link`, 6371 * acos(cos(radians(" . $y . ")) * cos(radians(`y`)) * cos(radians(`x`) - radians(" . $x . ")) + sin(radians(" . $y . ")) * sin(radians(`y`))) AS `distance`";
        
        $page   =   $_POST['page'] ? $_POST['page'] : 1;

        if ($this->config['sy_indexpage']) {
            $indexPageNum   =   ceil($this->config['sy_indexpage'] / 10);
            // 超出前台列表最大展示数量，不返回职位数据
            if ($page > $indexPageNum) {
                $data['list']  =   array();
                $this->render_json(0,'ok',$data);
            }
        }
        $limit      =   isset($_POST['limit']) ? $_POST['limit'] : 10;
        $pagenav    =   ($page - 1) * $limit;

        $where      =   array(
            'state'     =>  1,
            'r_status'  =>  1,
            'status'    =>  0,
            'x'         =>  array('>', 0),
            'y'         =>  array('>', 0),
            'orderby'   =>  'distance, asc',
            'limit'     =>  array($pagenav,$limit)
        );

        if (!empty($_POST['did'])) {
            // 按分站城市查询
            $domain  =  $this->getDomain($_POST['did'], true);
            if (isset($domain['didcity'])){
                if (!empty($domain['provinceid'])){
                    $where['provinceid']  =  $domain['provinceid'];
                }
                if (!empty($domain['cityid'])){
                    $where['cityid']  =  $domain['cityid'];
                }
                if (!empty($domain['three_cityid'])){
                    $where['three_cityid']  =  $domain['three_cityid'];
                }
            }
            
        }

        if ($this->config['sy_datacycle_job'] > 0) {

            $where['lastupdate']    =   array('>', strtotime('-'.$this->config['sy_datacycle_job'].' day'));
        }

        $jobM       =   $this->MODEL('job');
	    $jobListA   =   $jobM -> getList($where, array('field' => $select, 'link' => 'yes', 'from'=> 'wap_map'));
        
        $rows       =   $jobListA['list'];

        if (!empty($rows)) {
        
            $uids       =   array();
            
            foreach ($rows as $v) {
                
                $uids[] = $v['uid'];
            }
            
            $comM       =   $this->MODEL('company');
            
            $comListA   =   $comM->getList(array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`name`,`shortname`,`address`'));

            $list       =   array();
            
            foreach ($rows as $k => $v) {
                
                $list[$k]['id']         =   $v['id'];
                $list[$k]['name']       =   mb_substr($v['name'], 0, 16, 'utf-8');
                $list[$k]['salary_n']   =   $v['job_salary'];
                $list[$k]['job_edu'] = $v['job_edu'];
                $list[$k]['job_exp'] = $v['job_exp'];
                !empty($v['job_city_one']) && $list[$k]['job_city_one'] = $v['job_city_one'];
                !empty($v['job_city_two']) && $list[$k]['job_city_two'] = $v['job_city_two'];
                !empty($v['job_city_three']) && $list[$k]['job_city_three'] = $v['job_city_three'];
                if(!empty($v['citystr'])) {
                    $list[$k]['citystr'] = $v['citystr'];
                }
                if(!empty($v['address'])){
                    $list[$k]['address']  =  $v['address'];
                }

                $list[$k]['comlogo'] = $v['com_logo_n'];

                $list[$k]['x']          =  $v['x'];
                $list[$k]['y']          =  $v['y'];
                
                if ($v['welfare_n']) {
                    $list[$k]['welfare_n']=   $v['welfare_n'];
                }

                if ($v['distance'] <= 1) {
                    $list[$k]['dis']    =   ceil($v['distance'] * 1000) . 'm';
                } else {
                    $list[$k]['dis']    =   round($v['distance'], 2) . 'km';
                }
                
                
                foreach ($comListA['list'] as $val) {
                    
                    if ($val['uid'] == $v['uid']) {
                        
                        if ($v['shortname']) {
                            
                            $list[$k]['com_name']   =   mb_substr($val['shortname'], 0, 16, 'utf-8');
                        } else {
                            
                            $list[$k]['com_name']   =   mb_substr($val['name'], 0, 16, 'utf-8');
                        }
                        if(empty($v['address'])){
                            
                            $list[$k]['address']    =   $val['address'];
                        }
                    }
                }
            }
        }
        $data['list']   =   count($list) > 0 ? $list : array();
        // 小程序用seo
        if ($this->plat == 'mini'){
            $seo            =  $this->seo('map','','','',false, true);
            $data['seo']    =  $seo;
        }
        
	    $this->render_json(0,'ok',$data);
	}
	// 静态图
	function staticMap_action(){
	    
	    $data['lng']        =   $_POST['lng'];
	    $data['lat']        =   $_POST['lat'];
        $data['copyright']  =   1;
        $data['width']      =   320;
        $data['height']     =   130;
        $data['zoom']       =   14;
        $data['refer']      =   $this->config['sy_weburl'];

        $return = curlMappic($data);

        $res = array();

        if($return['error']==0){

        	$res['url']=checkpic($return['url']);
        	
        }

        $this->render_json($return['error'],$return['msg'],$res);

	}
	
}
?>