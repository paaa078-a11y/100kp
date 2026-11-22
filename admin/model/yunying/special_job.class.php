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
class special_job_controller extends adminCommon
{
    /**
     * 职位专题
     */
    function index_action(){
        $pageM	=	$this->MODEL('page');
        $sjM	=	$this->MODEL("specialjob");

        if(trim($_POST['keyword'])){
            $where['title']	=	array('like',trim($_POST['keyword']));
        }
        //提取分页
        $page = $pageM->page($_POST);
        $pageSize = $pageM->limit($_POST);
        $pages = $pageM->adminPageList('special_job', $where, $page, array('limit' => $pageSize));
        $pageSizes = $pages['page_sizes'];

        $list = array();
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            //排序
            if ($_POST['t'] && in_array($_POST['order'], array('asc', 'desc'))) {
                $orderby[] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $orderby[] = 'id,desc';
            }
            $where['orderby'] = $orderby;
            $where['limit']  =  $pages['limit'];

            $rows  =  $sjM->getList($where,array('utype'=>'admin'));
            foreach ($rows as $key => $value) {
                $list[] = array(
                    'id' => $value['id'],
                    'title' => $value['title'],
                    'title_url' => $value['title_url'],
                    'display' => $value['display'],
                    'display_switch' => $value['display_switch'],
                    'tuiguang' => $value['tuiguang'],
                    'tuiguang_switch' => $value['tuiguang_switch'],
                    'type' => $value['type'],
                    'jobnum' => $value['jobnum'],
                    'sort' => $value['sort'],
                );
            }
        }
        $rt = array();
        $rt['list'] = $list;
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pageSizes;
        $this->render_json(0, '', $rt);
    }

    function get_base_data_action()
    {
        $CacheM = $this->MODEL('cache');
        $CacheList = $CacheM->GetCache(array('hy', 'com', 'uptime'));
        $ratingM=   $this->MODEL('rating');
        $ratingArr  =   $ratingM->getList(array('category' => 1), array('field' => '`id`,`name`'));
        $CacheList['ratingArr'] =   $ratingArr;
        $this->admin_json(0, '', $CacheList);
    }

    function info_action()
    {
        $sjM = $this->MODEL('specialjob');
        $row = $sjM->getInfo(array('id' => $_POST['id']));
        if ($row) {
            $this->render_json(0, '', $row);
        } else {
            $this->render_json(1, '暂无数据');
        }
    }

    // 职位专题保存
    function save_action()
    {
        $data['title'] = trim($_POST['title']);
        $data['sub_title'] = trim($_POST['sub_title']);
        $data['display'] = $_POST['display'];
        $data['tuiguang'] = $_POST['tuiguang'];
        $data['sort'] = !empty($_POST['sort']) ? intval($_POST['sort']) : 0;

        $keyword = trim($_POST['keyword']);
        $data['keyword'] = str_replace('，', ',', strip_tags($keyword));
        $type = isset($_POST['type']) && intval($_POST['type']) == 2 ? 2 : 1;
        $dataSearch = array();
        $data['type'] = $type;
        if ($type == 2) {//自动搜索职位

            $dataSearch['refresh_minutes'] = intval($_POST['refresh_minutes']);
            $dataSearch['job_class'] = $_POST['job_class_arr'] ? intval(reset($_POST['job_class_arr'])) : 0;
            $dataSearch['city_class'] = $_POST['city_class_arr'] ? intval(reset($_POST['city_class_arr'])) : 0;
            $dataSearch['welfare'] = $_POST['welfare'] ? pylode(',', $_POST['welfare']) : 0;
            $dataSearch['edu'] = $_POST['edu'] ? intval($_POST['edu']) : 0;
            $dataSearch['exp'] = $_POST['exp'] ? intval($_POST['exp']) : 0;
            $dataSearch['sex'] = $_POST['sex'] ? intval($_POST['sex']) : 0;
            $dataSearch['report'] = $_POST['report'] ? intval($_POST['report']) : 0;
            $dataSearch['hy'] = $_POST['hy'] ? pylode(',', $_POST['hy']) : 0;
            $dataSearch['uptime'] = $_POST['uptime'];
            $dataSearch['sdate'] = $_POST['sdate'];
            $dataSearch['rating'] = $_POST['rating'] ? pylode(',', $_POST['rating']) : 0;
            $dataSearch['minsalary'] = $_POST['minsalary'] ? intval($_POST['minsalary']) : 0;
            $dataSearch['maxsalary'] = $_POST['maxsalary'] ? intval($_POST['maxsalary']) : 0;
            $dataSearch['is_zhiding'] = intval($_POST['is_zhiding']);
            $dataSearch['is_tuijian'] = intval($_POST['is_tuijian']);
            $dataSearch['is_jinji'] = intval($_POST['is_jinji']);
            $dataSearch['keyword'] = !empty($_POST['searchKeyword']) ? serialize($_POST['searchKeyword']) : '';
            $dataSearch['next_time'] = time() + intval($_POST['refresh_minutes']) * 60;
        }
        //判断必填
        if (!strlen($data['title'])) {
            $this->render_json(2, '请填写专题名称！');
        }
        if(!empty($_FILES)) {
            $uploadM = $this->MODEL('upload');
            if (!$_POST['pic'] && !$_FILES['pic']['tmp_name']) {
                $this->render_json(8, '请上传PC背景图');
            }
            if (!$_POST['sjlist_pic'] && !$_FILES['sjlist_pic']['tmp_name']) {
                $this->render_json(8, '请上传缩略图');
            }
            // 上传PC背景图
            if ($_FILES['pic']['tmp_name']) {
                $upArrBg = array(
                    'file' => $_FILES['pic'],
                    'dir' => 'special',
                );
                $picBg = $uploadM->newUpload($upArrBg);
                if (!empty($picBg['msg'])) {
                    $this->render_json(8, $picBg['msg']);
                }
                if (!empty($picBg['picurl'])) {
                    $data['pic'] = $picBg['picurl'];
                }
            }
            // 上传缩略图
            if ($_FILES['sjlist_pic']['tmp_name']) {
                $upArrPclist = array(
                    'file' => $_FILES['sjlist_pic'],
                    'dir' => 'special',
                );
                $pcsjlist = $uploadM->newUpload($upArrPclist);
                if (!empty($pcsjlist['msg'])) {
                    $this->render_json(8, $pcsjlist['msg']);
                }
                if (!empty($pcsjlist['picurl'])) {
                    $data['sjlist_pic'] = $pcsjlist['picurl'];
                }
            }
        }

        $sjM = $this->MODEL('specialjob');
        $data['data_search'] = $dataSearch;
        if (empty($_POST['id'])) {
            $nid = $sjM->addInfo($data);
            $name = '职位专题（ID：' . $nid . '）添加';
        } else {
            $nid = $sjM->upInfo(array('id' => $_POST['id']), $data);
            $name = '职位专题（ID：' . $_POST['id'] . '）更新';
        }
        $nid ? $this->admin_json(0, $name . '成功') : $this->render_json(1, $name . '失败');
    }
    // 职位专题下职位列表
    function job_list_action()
    {
        $pageM =  $this->MODEL('page');
        $sjM = $this->MODEL('specialjob');
        $jobM = $this->MODEL('job');
        $list = [];
        $total = 0;
        $isManual = null;
        $where['sid'] = (int)$_POST['id'];
        $special = $sjM->getInfo(array('id' => (int)$_POST['id']), array('field' => '`id`,`title`, `type`'));
        if ($special['type'] == 1) {
            //手动增加职位
            if (!empty($_POST['keyword'])) {
                $keyword = trim($_POST['keyword']);
                $joblist = $jobM->getListId(array('name' => array('like', $keyword)), array('field' => 'id'));
                if (!empty($joblist)) {
                    $jids = array();
                    foreach ($joblist as $v) {
                        $jids[] = $v['id'];
                    }
                    $where['jobid'] = array('in', pylode(',', $jids));
                }
            }
            //提取分页
            $page = $pageM->page($_POST);
            $pageSize = $pageM->limit($_POST);
            $pages = $pageM->adminPageList('special_job_list', $where, $page, array('limit' => $pageSize));
            $pageSizes = $pages['page_sizes'];
            // 分页数大于0的情况下 执行列表查询
            if ($pages['total'] > 0) {
                //排序
                $where['orderby'] = array('ctime,desc');
                //分页
                $where['limit'] = $pages['limit'];
                $list = $sjM->getjobList($where, array('utype' => 'admin'));
            }
        } else {
            //自动搜索职位
            $isManual = isset($_POST['is_manual']) && $_POST['is_manual'] == 1 ? 1 : 0;
            if ($isManual == 1) {// 手动添加的职位列表
                $jobids = $special['manual_jobids'];
            } else {// 自动添加职位
                $jobids = $special['jobids'];
                if ($special['next_time'] <= time()) {// 自动刷新职位
                    $jobids = $sjM->autoRefreshSpecialJobs($special);
                }
            }
            if (!empty($_POST['keyword'])) {
                $keyword = trim($_POST['keyword']);
                $joblist = $jobM->getListId(array('id' => array('in', $jobids), 'name' => array('like', $keyword)), array('field' => 'id'));
                if (!empty($joblist)) {
                    $jids = array();
                    foreach ($joblist as $v) {
                        $jids[] = $v['id'];
                    }
                    $jobids = pylode(',', $jids);
                } else {
                    $jobids = 0;
                }
            }
            $listwhere['id'] = array('in', $jobids);
            //提取分页
            $page = $pageM->page($_POST);
            $pageSize = $pageM->limit($_POST);
            $pages = $pageM->adminPageList('company_job', $listwhere, $page, array('limit' => $pageSize));
            $pageSizes = $pages['page_sizes'];
            // 分页数大于0的情况下 执行列表查询
            if ($pages['total'] > 0) {
                //排序
                $listwhere['orderby'] = array('id,desc');
                $listwhere['limit'] = $pages['limit'];
                $list = $sjM->getjobList($listwhere, array('utype' => 'admin', 'auto_add' => 1));
            }
        }
        $rt = array();
        $rt['list'] = $list;
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pageSizes;
        $this->render_json(0, '', $rt);
    }
    //  选择参加的职位
    function job_join_action(){
        $pageM = $this->MODEL('page');
        //实例化职位类
        $jobM  = $this->MODEL('job');

        $where['state']		=  1;
        $where['status']	=  0;
        $where['r_status']	=  1;
        //搜索条件
        if(!empty($_POST['addtime'])){
            $addtime = explode('~', $_POST['addtime']);
            $where['PHPYUNBTWSTART_A']	=	'';
            $where['sdate'][]			=	array('>',strtotime(trim($addtime[0])));
            $where['sdate'][]			=	array('<',strtotime(trim($addtime[1])));
            $where['PHPYUNBTWEND_A']	=	'';
        }
        if(!empty($_POST['uptime'])){
            $uptime = explode('~', $_POST['uptime']);
            $where['PHPYUNBTWSTART_A']	=	'';
            $where['lastupdate'][]		=	array('>',strtotime(trim($uptime[0])));
            $where['lastupdate'][]		=	array('<',strtotime(trim($uptime[1])));
            $where['PHPYUNBTWEND_A']	=	'';
        }

        if($_POST['rating']){
            $where['rating']=   $_POST['rating'];
        }

        if($_POST['edu']){
            $where['edu']   =   $_POST['edu'];
        }

        if($_POST['exp']){
            $where['exp']   =   $_POST['exp'];
        }

        if($_POST['job_class']){
            $where['PHPYUNBTWSTARTA'] = '';
            $where['job1']	        =	array('findin', $_POST['job_class']);
            $where['job1_son']	    =	array('findin', $_POST['job_class'], 'OR');
            $where['job_post']	    =	array('findin', $_POST['job_class'], 'OR');
            $where['PHPYUNBTWENDa']  = '';
        }
        if($_POST['city_class']){
            $where['PHPYUNBTWSTARTB'] = '';
            $where['provinceid']	=	array('findin', $_POST['city_class']);
            $where['cityid']	    =	array('findin', $_POST['city_class'], 'OR');
            $where['three_cityid']	=	array('findin', $_POST['city_class'], 'OR');
            $where['PHPYUNBTWENDB']  = '';
        }
        if($_POST['keyword']){
            if ($_POST['type']=='1'){
                //公司名称
                $where['com_name']  =	array('like',trim($_POST['keyword']));
            }elseif ($_POST['type']=='2'){
                //职位名称
                $where['name']      =	array('like',trim($_POST['keyword']));
            } elseif ($_POST['type']=='3') {
                //公司福利
                $where['welfare']   =	array('like',trim($_POST['keyword']));
            }
        }
        //提取分页
        $page = $pageM->page($_POST);
        $pageSize = $pageM->limit($_POST);
        $pages = $pageM->adminPageList('company_job', $where, $page, array('limit' => $pageSize));
        $pageSizes = $pages['page_sizes'];
        $total = intval($pages['total']);
        //已加入
        $joinNum = 0;
        //未加入
        $joinNumOff = 0;
        //分页数大于0的情况下 执行列表查询
        if($total > 0){
            $jobid_mullist = $jobM->getListId($where, array('field'=>'id'));
            $jobids_list = array_column($jobid_mullist, 'id');


            //limit order 只有在列表查询时才需要
            $orderby = array();
            if ($_POST['sort'] && in_array($_POST['order'], array('asc', 'desc'))) {
                if($_POST['sort'] == 'up'){
                    $orderby[] = 'lastupdate,' . $_POST['order'];
                } else {
                    $orderby[] = 'lastupdate,desc';
                }
            } else {
                $orderby[] = 'lastupdate,desc';
            }
            $where['orderby'] = $orderby;
            $where['limit'] = $pages['limit'];;

            $ListJob    =   $jobM -> getList($where,array('utype'=>'admin','cache'=>'1','isurl'=>'yes'));
            
            foreach ($ListJob['list'] as $k=>$v){
                $ListJob['list'][$k]['join'] = 0;
                $ListJob['list'][$k]['join_n'] = '<span style="color:red">未参加</span>';
            }


            $sjM    = $this->MODEL('specialjob');
            $special = $sjM->getInfo(array('id'=>(int)$_POST['sid']),array('field'=>'`id`, `type`'));
            if ($special['type'] == 1) {// 手动
                $joined = $sjM->getjobList(array('sid'=>$_POST['sid']), array('utype'=>'admin'));
                if (!empty($joined)){
                    $joinNum = count(array_intersect($jobids_list, $joined));
                    foreach ($ListJob['list'] as $k=>$v){
                        foreach ($joined as $val){
                            if ($v['id'] == $val['jobid']){
                                $ListJob['list'][$k]['join'] = 1;
                                $ListJob['list'][$k]['join_n'] = '<span style="color:green">已参加</span>';
                            }
                        }
                    }
                }
            } else {// 自动
                $jobids = $special['jobids'] ? explode(',', $special['jobids']) : array();
                $manualJobids = $special['manual_jobids'] ? explode(',', $special['manual_jobids']) : array();
                $joined = array_unique(array_merge($jobids, $manualJobids));
                $joinNum = count(array_intersect($jobids_list, $joined));
                foreach ($ListJob['list'] as $k=>$v){
                    foreach ($joined as $val){
                        if ($v['id'] == $val){
                            $ListJob['list'][$k]['join'] = 1;
                            $ListJob['list'][$k]['join_n'] = '<span style="color:green">已参加</span>';
                        }
                    }
                }
            }
        }
        //未加入
        $joinNumOff = max(0, $total - $joinNum);

        $this->render_json(0, '', array('list' => $ListJob['list'] ?: [], 'total' => $total,
            'perPage' => $pageSize, 'pageSizes' => $pageSizes,
            'joinNum' => $joinNum, 'joinNumOff' => $joinNumOff
        ));
    }
    // 职位专题添加职位
    function addjob_action(){
        if (!isset($_POST['sid']) || $_POST['sid'] < 1 ||
            !isset($_POST['add']) || !is_array($_POST['add']) || count($_POST['add']) == 0) {
            $this->render_json(1, '你还未选择任何信息！');
        }
        $sjM = $this->MODEL('specialjob');
        $sjM->addJob($_POST);

        $this->render_json(0, '职位专题添加职位成功');
    }
    // 删除参加的职位
    function deljob_action(){

        $sjM = $this->MODEL('specialjob');

        if($_POST['del']||$_POST['id']||$_POST['sid']){

            if(is_array($_POST['del'])){
                $layer_type	=	1;
                $del		=	pylode(',',$_POST['del']);
            }else{
                $layer_type	=	0;
                $del		=	$_POST['id'];
            }
            $sjM->delJob(array('id'=>array('in',$del)), array('sid' => intval($_POST['sid'])));
            $this->admin_json(0, '职位专题参加的职位(ID:'.$del.')删除成功！');
        }else{
            $this->render_json(1, '请选择您要删除的信息！');
        }
    }
    // 删除职位专题
    function del_action()
    {
        $_POST['id'] = (int)$_POST['id'];

        if ($_POST['del'] || $_POST['id']) {
            if (is_array($_POST['del'])) {
                $layer_type = 1;
                $del = pylode(',', $_POST['del']);
            } else {
                $layer_type = 0;
                $del = $_POST['id'];
            }
            $sjM = $this->MODEL('specialjob');
            $sjM->del(array('id' => array('in', $del)));

            $this->admin_json(0, '职位专题(ID:' . $del . ')删除成功！');
        } else {
            $this->render_json(1, '请选择您要删除的信息！');
        }
    }

    /**
     * 开启或关闭职位专题
     */
    function recommend_action()
    {
        if ($_POST['type'] == "rec_display") {
            $specialM = $this->MODEL('specialjob');
            $data['display'] = $_POST['rec'];
            $where['id'] = $_POST['id'];
            $nid = $specialM->upInfo($where, $data);
            $msg = $_POST['rec'] == 1 ? '开启' : '关闭';
            if ($nid) {
                $this->admin_json(0, "职位专题(ID:" . $_POST['id'] . ")" . $msg . "成功！");
            } else {
                $this->render_json(1, "职位专题(ID:" . $_POST['id'] . ")" . $msg . "成功！");
            }
        }
    }

    /**
     * 职位专题 开启推广/关闭推广
     */
    function tuiguang_action()
    {
        if ($_POST['type'] == "rec_tuiguang") {
            $specialM = $this->MODEL('specialjob');
            $data['tuiguang'] = $_POST['rec'];
            $where['id'] = $_POST['id'];
            $nid = $specialM->upInfo($where, $data);
            $msg = $_POST['rec'] == 1 ? '开启推广' : '关闭推广';
            if ($nid) {
                $this->admin_json(0, "职位专题(ID:" . $_POST['id'] . ")" . $msg . "成功！");
            } else {
                $this->render_json(1, "职位专题(ID:" . $_POST['id'] . ")" . $msg . "失败！");
            }
        }
    }

    /**
     * 职位专题 排序
     */
    function setOrder_action()
    {
        $post = $_POST;
        if ($post['id']) {
            $specialM = $this->MODEL('specialjob');
            $where = array('id' => $post['id']);
            $data = array('sort' => $post['sort']);
            $nid = $specialM->upInfo($where, $data);
            if ($nid) {
                $this->admin_json(0, '职位专题(ID：' . $post['id'] . ', sort=' . $post['sort'] . ')排序更新成功');
            } else {
                $this->render_json(1, '职位专题排序更新失败');
            }
        }
    }
}