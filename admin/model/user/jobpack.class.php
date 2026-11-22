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
class jobpack_controller extends adminCommon
{
    /**
     * @desc 后台红包推广管理首页
     */
    function index_action(){
        $packM = $this->MODEL('pack');
        $typeStr = intval($_POST['type']);
        $keywordStr = trim($_POST['keyword']);
        if (!empty($keywordStr)) {
            $JobM = $this->MODEL('job');
            if ($typeStr == 1) {
                $job = $JobM -> getList(array('com_name'=>array('like', $keywordStr)), array('field'=>'`uid`'));
                $cuids = array();
                if (is_array($job['list'])) {
                    foreach ($job['list'] as $v) {
                        $cuids[] = $v['uid'];
                    }
                }
                $where['uid'] = array('in', pylode(',', $cuids));
            } elseif ($typeStr == 2) {
                $job = $JobM->getList(array('name'=>array('like', $keywordStr)), array('field' => '`id`'));
                $jobids = array();
                if (is_array($job['list'])) {
                    foreach ($job['list'] as $v) {
                        $jobids[] = $v['id'];
                    }
                }
                $where['jobid'] = array('in', pylode(',', $jobids));
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company_job_share', $where, $page, array('limit' => $pageSize));
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            //limit order 只有在列表查询时才需要
            if($_POST['order']){
                $where['orderby'] =	$_POST['t'].','.$_POST['order'];
            }else{
                $where['orderby'] =	array('stime,desc');
            }
            $where['limit'] = $pages['limit'];
            $shareList = $packM->getShareJobList($where,array('utype'=>'admin'));
        }
        $rt['list'] = $shareList ? $shareList : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }

    /**
     * @desc 后台赏金职位管理  --  悬赏职位
     */
    function reward_action(){
        $packM = $this->MODEL('pack');
        $typeStr = intval($_POST['type']);
        $keywordStr = trim($_POST['keyword']);
        if (!empty($keywordStr)) {
            $JobM = $this->MODEL('job');
            if ($typeStr == 1) {
                $job = $JobM->getList(array('com_name'=>array('like', $keywordStr)), array('field'=>'`uid`'));
                $cuids = array();
                if (is_array($job['list'])) {
                    foreach ($job['list'] as $v) {
                        $cuids[] = $v['uid'];
                    }
                }
                $where['uid'] = array('in', pylode(',', $cuids));
            } elseif ($typeStr == 2) {
                $job = $JobM->getList(array('name'=>array('like', $keywordStr)), array('field'=>'`id`'));
                $jobids = array();
                if (is_array($job['list'])) {
                    foreach ($job['list'] as $v) {
                        $jobids[] = $v['id'];
                    }
                }
                $where['jobid'] = array('in', pylode(',', $jobids));
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company_job_reward', $where, $page, array('limit' => $pageSize));
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            //limit order 只有在列表查询时才需要
            if($_POST['order']){
                $where['orderby'] =	$_POST['t'].','.$_POST['order'];
            }else{
                $where['orderby'] =	array('stime,desc');
            }
            $where['limit'] = $pages['limit'];
            $rewardList = $packM->getRewardJobList($where, array('utype' => 'admin'));
        }
        $rt['list'] = $rewardList ? $rewardList : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }


    /**
     * @desc 后台赏金职位管理  -- 悬赏管理 -- 应聘列表
     */
    function rewardlog_action(){
        $packM = $this->MODEL('pack');
        if($_POST['jobid']){
            $where['jobid'] = intval($_POST['jobid']);
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company_job_rewardlist', $where, $page, array('limit' => $pageSize));
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            $where['orderby'] =	array('datetime,desc');
            $where['limit'] = $pages['limit'];
            $jobRewardList = $packM->getJobRewardList($where, array('utype' => 'admin'));
            foreach ($jobRewardList as $k => $v) {
                if ($v['log']['endmsg']) {
                    $jobRewardList[$k]['log']['loglist'][] = array('info' => $v['log']['endmsg']);
                }
            }
        }
        $rt['list'] = $jobRewardList ? $jobRewardList : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }

    /**
     * @desc 获取相关职位企业等信息
     */
    function getreward_action(){
        if($_POST){
            $M = $this->MODEL('pack');
            $Info = $M->getRewardAll($_POST['rewardid'], 26);
            $this->render_json(0, '', $Info);
        }
    }

    function getarb_action(){
        if($_POST){
            $M = $this->MODEL('pack');
            //获取相关职位企业等信息
            $return	= $M->logStatus((int)$_POST['rewardid'], (int)$_POST['status'], $_SESSION['auid'], 'admin', array('content' => $_POST['content'], 'port' => '5'));
            if($return['error']==''){
                //仲裁操作成功
                $this->render_json(0, '操作成功');
            }else{
                //仲裁操作失败
                $this->render_json(1, $return['error']);
            }
        }
    }

    /**
     * @desc 删除分享职位
     */
    function delshare_action(){
        if (intval($_POST['delid'])) {
            $M = $this->MODEL('pack');
            $addArr = $M->delShareJob('', intval($_POST['delid']), array('utype' => 'admin'));
            if ($addArr['errcode'] == 9) {
                $this->admin_json(0, $addArr['msg']);
            } else {
                $this->render_json(1, $addArr['msg']);
            }
        } else {
            $this->render_json(1, '非法访问');
        }
    }

    /**
     * @desc 删除悬赏职位
     */
    function delreward_action(){
        if(intval($_POST['delid'])){
            $M = $this->MODEL('pack');
            $addArr = $M->delrewardJob('', intval($_POST['delid']), array('utype' => 'admin'));
            if ($addArr['errcode'] == 9) {
                $this->admin_json(0, $addArr['msg']);
            } else {
                $this->render_json(1, $addArr['msg']);
            }
        } else {
            $this->render_json(1, '非法访问');
        }
    }
}