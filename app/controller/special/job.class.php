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
class job_controller extends special_controller{
	// 职位专题列表
	function specialjobIndex_action(){
        $this->seo("spe_job_list");
        $this->yun_tpl(array('specialjob_index'));
	}
	// 职位专题详情
	function index_action(){
	    
	    $id    =  (int)$_GET['id'];
		$sjM   =  $this->MODEL('specialjob');
		$info  =  $sjM->getInfo(array('id'=>$id,'display'=>1));
        if (empty($info)){
            $this->ACT_msg($this->config['sy_weburl'], '没有找到该职位专题');
        }
        $data = array('utype'=>'pc');
        if ($info['type'] == 1) {
        	$where = array('sid'=>$id);
        } else {
            $jobids = $info['jobids'];
            $manualjobids = $info['manual_jobids'] ? explode(',', $info['manual_jobids']) : array();
            if ($info['next_time'] <= time()) {// 自动刷新职位
                $jobids = $sjM->autoRefreshSpecialJobs($info);
            }
            $jobidArr = array();
            if ($jobids) {
                $jobidArr = explode(',', $jobids);
            }
            $alljobids = array_merge($jobidArr, $manualjobids);
            $where['id'] = array('in', pylode(',', $alljobids));
            $data['auto_add'] = 1;// 自动搜索职位
		}
        $rows = $sjM->getjobList($where, $data);
		$this->yunset('rows',$rows);
		$this->yunset('info',$info);
		
		$this->data  =  array('spename'=>$info['title']);
		$this->seo("spe_job");

		$this->yun_tpl(array('job'));
	}
	// 职位列表，通过关键字查询职位专题
	function getSpecialJob_action(){
	    
	    $keyword = $this->stringfilter($_POST['keyword']);
	    $sjM  =	$this->MODEL('specialjob');
	    $row  = $sjM->getListByKeyword($keyword);
	    if (!empty($row)){
	        $row['pcurl'] = Url('special', array('c'=>'job','id'=>$row['id']));
	    }
	    echo json_encode($row);
	}
}
?>