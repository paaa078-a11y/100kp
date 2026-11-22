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
class zphnet_controller extends adminCommon
{
    //设置高级搜索功能
    function set_search(){
        $search_list[]	=	array('param' => 'status','name' => '状态','value' => array('3' => '未开始','1' => '已开始','2' => '已结束'));
        $this -> yunset('search_list',$search_list);
    }

    function index_action(){
        if($_POST['keyword']){
            $where['title'] = array('like', trim($_POST['keyword']));
        }
        if($_POST['status'] == '3'){
            $where['starttime'] = array('unixtime', '>', time());
        }elseif($_POST['status'] == '1'){
            $where['starttime'] = array('unixtime', '<', time());
            $where['endtime'] =	array('unixtime', '>', time());
        }elseif($_POST['status'] == '2'){
            $where['endtime'] =	array('unixtime','<',time());
        }
        //排序
        if($_POST['order']){
            $where['orderby'] = $_POST['t'].','.$_POST['order'];
        }else{
            $where['orderby'] = 'id';
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('zphnet', $where, $page, array('limit' => $pageSize));
        $zphnetM = $this->MODEL('zphnet');
        if($pages['total'] > 0){
            $where['limit'] = $pages['limit'];
            $rows = $zphnetM->getList($where,array('utype'=>'admin'));
            $class = $zphnetM->getClassList(array('keyid'=>0));
            foreach ($class as $v){
                $area[$v['id']] = $v['name'];
            }
            $this->yunset('area',$area);
        }
        $rt['list'] = $rows ? $rows : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];

        $this->render_json(0, '', $rt);
    }

    function getGroup_action(){
        //来源
        $sres = $this->set_comaddsearch();
        $source = $sres['source'];
        //会员信息
        $ratingarr = $sres['ratingarr'];
        //搜索数据
        $search_list = $sres['search_list'];
        $rt = array(
            'source' => $source,
            'ratingarr' => $ratingarr,
            'search_list' => $search_list,
        );
        //提取分站内容
        $cacheM = $this->MODEL('cache');
        $domain = $cacheM->GetCache('domain');
        // widuu 2025/11
        $Dnamea = [-1 => '全站'];
        $DnameRight = (array)($domain['Dname'] ?? []); 
        $Dname = (array)$Dnamea + $DnameRight;
        $rt['Dname'] = (object) $Dname;
        // $rt['Dname'] = (object)$domain['Dname'];
        // end widuu
        
        
        $where = array('keyid'=>0);
        $zphnetM = $this->MODEL('zphnet');
        $class = $zphnetM->getClassList($where);
        $rt['space'] = $class;
        $this->render_json(0, '', $rt);

    }

    //后台修改参会企业职位
    function upjob_action(){
        if ($_POST['zphjob']){
            $value = array(
                'jobid' => pylode(',', $_POST['zphjob'])
            );
            $zphM = $this->MODEL('zphnet');
            $nid = $zphM->upZphnetComSort($_POST['zcomid'], $value);
            if ($nid) {
                $this->admin_json(0, "招聘会企业职位修改成功！");
            } else {
                $this->render_json(1, "招聘会企业职位修改失败！");
            }
        }else{
            $this->render_json(1, "请选择参会职位！");
        }
    }
    //根据选择的企业展示前台可以显示的职位列表
    function getjoblist_action(){
        $JobM =	$this->MODEL('job');
        $comid = intval($_POST['comid']);
        $type = $_POST['type'];

        if($type=='admin'){
            $zphnetM = $this->MODEL('zphnet');
            $comzph = $zphnetM->getZphnetCom(array('zid'=>$_POST['zid'],'uid'=>$comid));
            $jobids = $comzph['jobid']?explode(',',$comzph['jobid']):array();
            $rows = $JobM->getList(array('uid' => $comid, 'state' => '1', 'r_status' => array('<>', '2'), 'status' => array('<>', '1')));
            $arr = array(
                'jobIds' =>$jobids,
                'rows'=>$rows
            );
        }else {
            $rows = $JobM->getList(array('uid' => $comid, 'state' => '1', 'r_status' => array('<>', '2'), 'status' => array('<>', '1')));
            $arr = array(
                'rows'=>$rows
            );
        }
        $this->render_json(0,'',$arr);
    }

    function save_action(){
        $zphnetM = $this->MODEL('zphnet');
        $info = $this->post_trim($_POST);
        if (!$info['title']) {
            $this->render_json(1, '招聘会标题不能为空');
        }
        if($_FILES){
            $upData = [];
            foreach ($_FILES as $key => $value) {
                if ($value['tmp_name']) {
                    $upData[$key]['file'] = $_FILES[$key];
                    $upData[$key]['dir'] = 'zhaopinhui';
                }
            }
            $arr = [
                'sl' => 'pic',
                'hf' => 'banner',
                'wapsl' => 'pic_wap',
                'waphf' => 'banner_wap',
            ];
            if ($upData) {
                $uploadM = $this->MODEL('upload');
                foreach ($upData as $key => $value) {
                    $upRes = $uploadM->newUpload($value);
                    if ($upRes && !empty($upRes['msg'])) {
                        $return['msg'] = $upRes['msg'] ? $upRes['msg'] : $upRes['msg'];
                        $this->render_json(1, $return['msg']);
                    } else {
                        $info[$arr[$key]] = $upRes['picurl'];
                    }
                }
            }
        }
        if(strtotime($info['starttime']) > strtotime($info['endtime'])){
            $this->render_json(1, '开始时间不得大于结束时间');
        }
        $info['body'] = str_replace("&amp;","&", $info['body']);
        if($info['id']){
            $nbid =	$zphnetM->upInfo(array('id' => intval($info['id'])), $info);
            if (isset($nbid)) {
                $this->admin_json(0, "网络招聘会(ID:".$info['id'].")修改成功！");
            } else {
                $this->render_json(1, "网络招聘会(ID:".$info['id'].")修改失败！");
            }
        }else{
            $nbid =	$zphnetM->addInfo($info);
            if (isset($nbid)) {
                $this->admin_json(0, "网络招聘会(ID:".$nbid.")添加成功！");
            } else {
                $this->render_json(1, "网络招聘会(ID:$nbid)添加失败！");
            }
        }
    }

    function del_action(){
        $delID = $_POST['del'];
        $zphnetM = $this->MODEL('zphnet');
        $return	= $zphnetM->delZph($delID);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

   
    function user_action(){
        $zphnetM = $this->MODEL('zphnet');
        if($_POST['id']){
            $where['zid'] =	intval($_POST['id']);
        }
        if (trim($_POST['keyword'])){
            $resumeM = $this->MODEL('resume');
            $resume = $resumeM->getList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));
            if($resume['list']){
                $uid = array();
                foreach($resume['list'] as $v){
                    !in_array($v['uid'], $uid) && array_push($uid, $v['uid']);
                }
                $where['uid'] =	array('in',pylode(',', $uid));
            }
        }
        $where['usertype'] = 1;
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('zphnet_user', $where, $page, array('limit' => $pageSize));
        if($pages['total'] > 0){
            $where['limit'] = $pages['limit'];
            $rows = $zphnetM->getZphnetUser($where,array('resume' => $where['limit'], 'keyword' => trim($_POST['keyword']), 'utype' => 'admin'));
        }
        $rt['list'] = $rows ? $rows : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }
    function com_action(){
        $zphnetM = $this->MODEL('zphnet');
        if($_POST['id']){
            $where['zid'] =	intval($_POST['id']);
        }
        if($_POST['status']){
            if($_POST['status'] == "3"){
                $where['status'] = 0;
            }elseif($_POST['status'] == "1"){
                $where['status'] = 1;
            }elseif($_POST['status'] == "2"){
                $where['status'] = 2;
            }
        }
        if (trim($_POST['keyword'])){
            if ($_POST['type'] == 2) {
                $companyM = $this->MODEL('company');
                $company = $companyM->getChCompanyList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));
                if($company){
                    foreach($company as $v){
                        $uid[] = $v['uid'];
                    }
                    $where['uid'] =	array('in',pylode(',', $uid));
                } else {
                    $where['uid'] = '-1';
                }
            } else {
                $zphs = $zphnetM->getList(array('title'=> array('like', trim($_POST['keyword']))), array('field' => 'id'));
                if ($zphs) {
                    foreach($zphs as $v){
                        $zid[] = $v['id'];
                    }
                    $where['zid'] =	array('in',pylode(',', $zid));
                } else {
                    $where['zid'] = '-1';
                }
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('zphnet_com', $where, $page, array('limit' => $pageSize));
        if($pages['total'] > 0){
            if($_POST['order']){
                $where['orderby'] =	$_POST['t'] . ',' . $_POST['order'];
            }else{
                $where['orderby'] = array('status,asc','id,desc');
            }
            $where['limit']	= $pages['limit'];
            $rows = $zphnetM->getZphnetComList($where,array('utype'=>'admin'));
        }

        if ($rows){
            $class = $zphnetM->getClassList(array('keyid'=>0));
            foreach ($class as $v){
                $area[$v['id']] = $v['name'];
            }
            foreach ($rows as &$v){
                $v['area_name'] = isset($area[$v['zw']])?$area[$v['zw']]:'暂无';
            }
        }

        $rt['list'] = $rows ? $rows : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];

        $this->render_json(0, '', $rt);
    }
    function comGroup_action(){
        if ($_POST['id']) {
            $zphnetM = $this->MODEL('zphnet');

            $zphnet = $zphnetM->getInfo(array('id' => intval($_POST['id'])),array('field' => '`zw`,`title`'));
            $noarea = 0;
            if (!empty($zphnet['zw'])){
                $class = $zphnetM->getClassList(array('keyid' => $zphnet['zw']));
                $area = array();
                foreach ($class as $v){
                    $area[$v['id']] = $v['name'];
                }
                if(empty($area)){
                    $noarea = 2;
                }
                $rt['area'] = $area;
            }else{
                $noarea = 1;
            }
            $rt['noarea'] = $noarea;
            $rt['zphnet'] = $zphnet;
        }
        $this->render_json(0, '', $rt);
    }

    function getArea_action(){
        $zphnetM = $this->MODEL('zphnet');
        $zphnet = $zphnetM->getInfo(array('id' => intval($_POST['id'])),array('field' => '`zw`,`title`'));
        $rt['area'] = array();
        if (!empty($zphnet['zw'])){
            $class = $zphnetM->getClassList(array('keyid' => $zphnet['zw']));
            $area = array();
            foreach ($class as $v){
                $area[$v['id']] = $v['name'];
            }
            $rt['area'] = $area;
        }
        $this->render_json(0, '', $rt);
    }


    function status_action(){
        $zphnetM = $this->MODEL('zphnet');
        $data['status']	= $_POST['status'];
        $data['statusbody']	= trim($_POST['statusbody']);
        $id	= @explode(",",$_POST['pid']);
        $nid = $zphnetM->zphnetComStatus($id, $data, array('utype' => 'admin'));
        if ($nid){
            $this->admin_json(0, "网络招聘会报名企业(ID:".$_POST['pid'].")审核设置成功！");
        }else{
            $this->render_json(1, "设置失败！");
        }
    }

    /*
     * 参会企业审核
     */
    function audit_action(){
        $id = intval($_POST['id']);
        $zphnetM = $this->MODEL('zphnet');
        $ComM = $this->MODEL('company');
        $zphnetCom = $zphnetM->getZphnetCom(array('id' => $id), array('field' => 'id,uid,status,statusbody,jobid,zw'));
        $zphnetCom['zw_n'] = '';
        if ($zphnetCom['zw']) {
            $zw = $zphnetM->getClass(array('id' => $zphnetCom['zw']));
            $zphnetCom['zw_n'] = $zw['name'];
        }
        $Info = $ComM->getInfo($zphnetCom['uid'], array('jlist' => 1, 'ywy'=>1));
        $Info['jobid_arr'] = $zphnetCom['jobid'] ? explode(',', $zphnetCom['jobid']) : array();
        foreach ($Info['job_list'] as $k => $v) {
            if (!empty($Info['jobid_arr'])) {
                if (in_array($v['id'], $Info['jobid_arr'])) {
                    $Info['job_list'][$k]['ch_n'] = '已参会';
                } else {
                    $Info['job_list'][$k]['ch_n'] = '未参会';
                }
            } else {
                $Info['job_list'][$k]['ch_n'] = '已参会';
            }
        }
        $Info['zphnet'] = $zphnetCom;
        $Info['zph'] = $zphnetCom;
        $this->render_json(0, '', $Info);
    }

    // 个人信息导出前检测
    function userxlscheck_action(){
        if($_POST['zid']&&$_POST['cid']) {
            $resumeM = $this->MODEL('resume');
            $where['usertype'] = 1;
            $rwhere = array('uid' => array('in', $_POST['cid']),'defaults' => 1);
            $rows = $resumeM->getList($rwhere,array('utype'=>'zphnet'));
            if (!$rows) {
                $this->render_json(1, '没有可以导出的网络招聘会个人信息！');
            }
        }else{
            $this->render_json(1, '没有可以导出的参会个人信息！');
        }
    }
    function userxls_action(){
        if($_POST['zid']&&$_POST['cid']) {
            $resumeM = $this->MODEL('resume');
            $where['usertype'] = 1;
            $rwhere = array('uid' => array('in', $_POST['cid']),'defaults' => 1);
            $rows = $resumeM->getList($rwhere,array('utype'=>'zphnet'));
            $this->yunset('list', $rows['list']);
            $this->MODEL('log')->addAdminLog('导出参与网络招聘会个人信息');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=zphnetuser.xls');
            $this->yuntpl(array('admin/admin_zphnet_userxls'));
        }
    }

    /*
     * 参会企业导出前检测
     */
    function comxlscheck_action(){
        $zphnetM = $this->MODEL('zphnet');
        if ($_POST['zid']) {
            if ($_POST['cid']) {
                $zcwhere = array('id' => array('in', $_POST['cid']));
            } else {
                $zcwhere = array('zid' => $_POST['zid']);
            }
            $rows = $zphnetM->getZphnetComList($zcwhere);
            if ($rows) {
                $this->render_json(0, '');
            } else {
                $this->render_json(1, '没有可以导出的参会企业信息！');
            }
        }
    }

    private function getA_ZZ(){
        $letter = range('A', 'Z');
        $x = $letter;
        $y = $letter;
        foreach ($x as $xv) {
            foreach ($y as $yv) {
                $letter[] = $xv . $yv;
            }
        }
        return $letter;
    }
    private function getfiled_index($letters, $search){
        $rt = false;
        foreach ($letters as $k => $v) {
            if ($search == $v) {
                $rt = $k;
                break;
            }
        }
        return $rt;
    }

    /*
     * 导出表格单元格样式设置
     */
    private function setXlsCellStyle($objPHPExcel, $col1, $row1, $col2, $row2, $isbold = 1, $h_center = 1, $v_center = 1) {
        if ($isbold == 1) {
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col1, $row1, $col2, $row2)->getFont()->setBold(true);
        }
        if ($h_center == 1) {
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col1, $row1, $col2, $row2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        if ($v_center == 1) {
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col1, $row1, $col2, $row2)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
    }

    //参会企业导出
    function comxls_action()
    {
        $letters = $this->getA_ZZ();
        $zphnetM = $this->MODEL('zphnet');
        $CompanyM = $this->MODEL('company');
        $JobM = $this->MODEL('job');
        if ($_POST['zid']) {
            if ($_POST['cid']) {
                $zcwhere = array('id' => array('in', $_POST['cid']));
            } else {
                $zcwhere = array('zid' => $_POST['zid']);
            }
            $rows = $zphnetM->getZphnetComList($zcwhere);
            if (!empty($rows)){
                $cacheM = $this->MODEL('cache');
                $cache = $cacheM->getCache(array('com'));
                $comclass_name = $cache['comclass_name'];
                $jobids = $jobuids = $comids = array();
                $title = '';
                foreach ($rows as $key => $val) {
                    $comids[] = $val['uid'];
                    if ($val['jobid']) {
                        $jobids[] = $val['jobid'];
                    } else {
                        $jobuids[] = $val['uid'];
                    }
                    $title = $val['title'];
                }
                $comField = '`uid`,`name`,`mun`,`content`,`address`,`linktel`,`linkman`,`linkphone`,`welfare`,`money`,`moneytype`';
                $companys = $CompanyM->getChCompanyList(array('uid' => array('in', @implode(',', $comids))), array('field' => $comField));
                $jobField = '`id`,`uid`,`name`,`zp_num`,`minsalary`,`maxsalary`,`exp`,`edu`,`provinceid`,`cityid`,`three_cityid`';
                $jobWhere['state'] = 1;
                $jobWhere['status'] = 0;
                $jobWhere['r_status'] = 1;
                $jobWhere['PHPYUNBTWSTART'] = '';
                $jobWhere['uid'] = array('in', pylode(',', $jobuids));
                $jobWhere['id'] = array('in', pylode(',', $jobids), 'OR');
                $jobWhere['PHPYUNBTWEND'] = '';
                $jobsA = $JobM->getList($jobWhere, array('field' => $jobField));
                $jobs = $jobsA['list'];
                foreach ($companys as $k => $va) {
                    $companys[$k]['content'] = trim(strip_tags($va['content']));
                    $companys[$k]['mun'] = $comclass_name[$va['mun']];
                    foreach ($jobs as $val) {
                        if ($va['uid'] == $val['uid']) {
                            $companys[$k]['jobs'][] = $val;
                        }
                    }
                }
                $comfieldsList = [
                    'name' => '公司名称',
                    'address' => '地址',
                    'linkphone' => '联系电话',
                    'linkman' => '联系人',
                    'linktel' => '联系手机',
                    'content' => '公司简介',
                    'welfare' => '福利待遇',
                    'mun' => '公司规模',
                    'money' => '注册资金',
                ];
                $jobfieldsList = [
                    'index' => '',
                    'name' => '招聘岗位',
                    'zp_num' => '招聘人数',
                    'job_salary' => '薪酬',
                    'job_exp' => '经验要求',
                    'job_edu' => '学历要求',
                    'address' => '工作地点'
                ];
                include_once LIB_PATH . 'libs/PHPExcel.php';
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0);
                $width = 20;
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth($width); // 设置列宽
                $objPHPExcel->getActiveSheet()->setCellValue('A1', $title . ' - 参会企业信息'); // 设置表头
                $objPHPExcel->getActiveSheet()->mergeCells('A1:I1'); // 表头列合并
                // 头部样式设置
                $this->setXlsCellStyle($objPHPExcel, 0,1, 8, 1);
                $curr_row = 1;
                foreach ($companys as $key => $val) {
                    $curr_row++;//新开一行记录参会企业序号 表头 （参会企业1、2、3...）
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth($width); // 设置列宽
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $curr_row, '参会企业' . ($key + 1)); // 设置表头
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curr_row . ':I'.$curr_row); // 表头列合并
                    // 单元格加粗、居中设置
                    $this->setXlsCellStyle($objPHPExcel, 0,$curr_row, 8, $curr_row);
                    $curr_row++;//新开一行记录企业信息表头 (公司名称、地址...)
                    $colindex = $this->getfiled_index($letters, 'A');
                    // 循环企业信息字段
                    foreach ($comfieldsList as $com_v) {
                        $objPHPExcel->getActiveSheet()->getColumnDimension($letters[$colindex])->setWidth($width); // 设置列宽
                        $objPHPExcel->getActiveSheet()->setCellValue($letters[$colindex] . $curr_row, $com_v); // 设置表头
                        $colindex++;
                    }
                    // 单元格加粗、居中设置
                    $this->setXlsCellStyle($objPHPExcel, 0,$curr_row, 8, $curr_row);
                    $curr_row++;//新开一行记录企业信息数据
                    $colindex = $this->getfiled_index($letters, 'A');
                    // 循环企业信息字段
                    foreach ($comfieldsList as $com_k => $com_v) {
                        if ($com_k == 'money') {
                            if ($val['money'] > 0) {
                                $text = $val['money'];
                                if ($val['moneytype'] == 1) {
                                    $text .= '万元';
                                } else if ($val['moneytype'] == 2) {
                                    $text .= '万美元';
                                }
                            } else {
                                $text = '';
                            }
                        } else if ($com_k == 'content') {
                            $text = mb_substr($val['content'], 0, 50);
                        } else {
                            $text = $val[$com_k] ? $val[$com_k] : '';
                        }
                        if (in_array($com_k, array('linkphone', 'linktel'))) { // 数字转字符的字段
                            $objPHPExcel->getActiveSheet()->setCellValueExplicit($letters[$colindex] . $curr_row, $text, PHPExcel_Cell_DataType::TYPE_STRING); // 转类型
                        } else {
                            $objPHPExcel->getActiveSheet()->setCellValue($letters[$colindex] . $curr_row, $text); // 动态表格内容
                        }
                        $colindex++;
                    }
                    $curr_row++;//新开一行记录岗位信息表头 （岗位信息）
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth($width); // 设置列宽
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $curr_row, '岗位信息'); // 设置表头
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curr_row . ':I'.$curr_row); // 表头列合并
                    // 单元格加粗、居中设置
                    $this->setXlsCellStyle($objPHPExcel, 0,$curr_row, 8, $curr_row);
                    $curr_row++;//新开一行记录企业信息表头 (公司名称、地址...)
                    $colindex = $this->getfiled_index($letters, 'A');
                    // 循环企业信息字段
                    foreach ($jobfieldsList as $job_v) {
                        $objPHPExcel->getActiveSheet()->getColumnDimension($letters[$colindex])->setWidth($width); // 设置列宽
                        $objPHPExcel->getActiveSheet()->setCellValue($letters[$colindex] . $curr_row, $job_v); // 设置表头
                        $colindex++;
                    }
                    // 头部样式设置
                    // 单元格加粗、居中设置
                    $this->setXlsCellStyle($objPHPExcel, 0,$curr_row, 8, $curr_row);
                    $colindex = $this->getfiled_index($letters, 'A');
                    foreach ($val['jobs'] as $job_k => $job) {
                        $curr_row++;//岗位信息数据
                        // 循环职位信息字段
                        foreach ($jobfieldsList as $job_k => $job_v) {
                            if ($job_k == 'address') {
                                $text = $job['job_city_one'] . '-' . $job['job_city_two'];
                                if ($job['job_city_three']) {
                                    $text .=  '-' . $job['job_city_three'];
                                }
                            } else if ($job_k == 'index') {
                                $text = '岗位' . $job_k + 1;
                            } else {
                                $text = $job[$job_k] ? $job[$job_k] : '';
                            }
                            if (in_array($job_k, array('zp_number'))) { // 数字转字符的字段
                                $objPHPExcel->getActiveSheet()->setCellValueExplicit($letters[$colindex] . $curr_row, $text, PHPExcel_Cell_DataType::TYPE_STRING); // 转类型
                            } else {
                                $objPHPExcel->getActiveSheet()->setCellValue($letters[$colindex] . $curr_row, $text); // 动态表格内容
                            }
                            $colindex++;
                        }
                    }
                    $curr_row++;//岗位信息结束留空一行
                }
                // 设置边框
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框
                        ),
                    ),
                );
                $objPHPExcel->getActiveSheet()->getStyle('A1:I'.($curr_row - 1))->applyFromArray($styleArray);
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                ob_start();
                $objWriter->save('php://output');
                $xlsData = ob_get_contents();
                ob_end_clean();
                $data = [
                    'file' => base64_encode($xlsData),
                    'file_name' => '网络招聘会参会企业信息' . date('YmdHis') . '.xlsx'
                ];
                return $this->admin_json(0, "导出报名网络招聘会信息", $data);
            } else {
                $this->render_json(1, '暂无符合条件的企业数据');
            }
        }
    }

    function deluser_action(){
        $zphnetM = $this->MODEL('zphnet');
        $delID = $_POST['del'];
        $arr = $zphnetM->delZphnetUser($delID);
        if ($arr['errcode'] == 9) {
            $this->admin_json(0, $arr['msg']);
        } else {
            $this->render_json(1, $arr['msg']);
        }
    }
    //删除链接
    function delcom_action(){
        $zphnetM = $this->MODEL('zphnet');
        $delID = $_POST['del'];
        $arr = $zphnetM->delZphnetCom($delID);
        if ($arr['errcode'] == 9) {
            $this->admin_json(0, $arr['msg']);
        } else {
            $this->render_json(1, $arr['msg']);
        }
    }

    function checksitedid_action(){
        if($_POST['uid']){
            $uids = @explode(',', $_POST['uid']);
            $uid = pylode(',', $uids);
            if($uid){
                $siteDomain	= $this->MODEL('site');
                $siteDomain->updDid(array('zphnet'), array('id' => array('in', $uid)), array('did' => $_POST['did']));
                $siteDomain->updDid(array('zphnet_com'),  array('zid' => array('in', $uid)),array('did' => $_POST['did']));
                $this->admin_json(0, '网络招聘会(ID:'.$_POST['uid'].')分配站点成功！');
            }else{
                $this->render_json(1, '请正确选择需分配数据！');
            }
        }else{
            $this->render_json(1, '参数不全请重试！');
        }
    }

    function set_comaddsearch(){
        $ratingM = $this->MODEL('rating');
        $rating = $ratingM->getList(array( 'category' => '1', 'orderby' => 'sort' ), array('field'=>'`id`,`name`'));
        if(!empty($rating)){
            $ratingarr = array();
            foreach($rating as $k => $v){
                $ratingarr[$v['id']] = $v['name'];
            }
        }
        include(CONFIG_PATH.'db.data.php');
        $source = $arr_data['source'];
        $timeSection = array('1'=>'今天', '3'=>'3天内', '7'=>'7天内', '15'=>'半月内', '30'=>'1个月内', '31'=> '1-3个月', '32' => '3-6个月', '33' => '6个月-1年', '34' => '1年以上');
        $status = array('1'=>'已审核', '2'=>'已锁定', '3'=>'未通过', '4'=>'未审核','5'=>'已暂停');
        $edtime = array('1'=>'7天内', '2'=>'一个月内', '3'=>'半年内', '4'=>'一年内', '5'=>'已到期');
        $isrec = array('1'=>'是', '2'=>'否', '3'=>'已到期');
        $isgw = array('1'=>'已分配', '2'=>'未分配');
        $search_list = array();
        $search_list['rating'] = array('name' => '会员等级', 'value' => $ratingarr);
        $search_list['time'] = array('name' => '到期时间', 'value' => $edtime);
        $search_list['status'] = array('name' => '审核状态', 'value' => $status);
        $search_list['source'] = array('name' => '数据来源', 'value' => $source);
        $search_list['rec'] = array('name' => '知名企业', 'value' => $isrec);
        $search_list['gw'] = array('name' => '企业顾问', 'value' => $isgw);
        return array('source' => $source, 'ratingarr' => $ratingarr, 'search_list' => $search_list);
    }

    function comadd_action(){
        $ComM = $this->MODEL('company');
        $where = array('r_status' => 1);
        $mwhere = array();
        if($_POST['keyword']){
            $keywordStr = trim($_POST['keyword']);
            $typeStr = intval($_POST['type']);
            if (!empty($keywordStr) && $typeStr == 1) {
                $where['PHPYUNBTWSTART_C'] = '';
                $where['name'] = array('like', $keywordStr);
                $where['shortname'] = array('like', $keywordStr, 'OR');
                $where['PHPYUNBTWEND_C'] = '';
            }elseif (!empty($keywordStr) && $typeStr == 2){
                $mwhere['username'] = array('like', $keywordStr);
            }else if (!empty($keywordStr) && $typeStr == 3) {
                $where['linkman'] = array('like', $keywordStr);
            }else if (!empty($keywordStr) && $typeStr == 4) {
                $where['linktel'] = array('like', $keywordStr);
            }else if (!empty($keywordStr) && $typeStr == 5) {
                $where['linkmail'] = array('like', $keywordStr);
            }else if (!empty($keywordStr) && $typeStr == 6){
                $where['uid'][] = array('=', $keywordStr);
            }
        }
        if ($_POST['status']) {
            $status = intval($_POST['status']);
            if($status == 4){
                $where['r_status'] = 0;
            }else if($status == 5){
                $where['r_status'] = 4;
            }else{
                $where['r_status'] = $status;
            }
        }
        if ($_POST['adtime']) {
            $adtime = intval($_POST['adtime']);
            if($adtime == 1){
                $mwhere['reg_date'] = array('>', strtotime('today'));
            }else if ($adtime < 31){
                $mwhere['reg_date'] = array('>', strtotime('-'.$adtime.' day'));
            }else if ($adtime == 31){// 1 - 3 个月
                $mwhere['PHPYUNBTWSTART_C'] = '';
                $mwhere['reg_date'][] = array('<', strtotime('-1 month'));
                $mwhere['reg_date'][] = array('>=', strtotime('-3 month'));
                $mwhere['PHPYUNBTWEND_C'] = '';
            }else if ($adtime == 32){// 3 - 6个月
                $mwhere['PHPYUNBTWSTART_C'] = '';
                $mwhere['reg_date'][] = array('<', strtotime('-3 month'));
                $mwhere['reg_date'][] = array('>=', strtotime('-6 month'));
                $mwhere['PHPYUNBTWEND_C'] = '';
            }else if ($adtime == 33){// 6个月 - 1年
                $mwhere['PHPYUNBTWSTART_C'] = '';
                $mwhere['reg_date'][] = array('<', strtotime('-6 month'));
                $mwhere['reg_date'][] = array('>=', strtotime('-12 month'));
                $mwhere['PHPYUNBTWEND_C'] = '';
            }else if ($adtime == 34){// 1年以上
                $mwhere['reg_date'] = array('<', strtotime('-1 year'));
            }
        }
        if($_POST['lotime']){
            $lotime = intval($_POST['lotime']);
            if($lotime ==  1){
                $mwhere['login_date'] = array('>', strtotime('today'));
            }else if ($lotime < 31){
                $mwhere['login_date'] = array('>', strtotime('-'.$lotime.' day'));
            }else if ($lotime == 31){
                $mwhere['PHPYUNBTWSTART_C'] = '';
                $mwhere['login_date'][] = array('<', strtotime('-1 month'));
                $mwhere['login_date'][] = array('>=', strtotime('-3 month'));
                $mwhere['PHPYUNBTWEND_C'] = '';
            }else if ($lotime == 32){
                $mwhere['PHPYUNBTWSTART_C'] = '';
                $mwhere['login_date'][] = array('<', strtotime('-3 month'));
                $mwhere['login_date'][] = array('>=', strtotime('-6 month'));
                $mwhere['PHPYUNBTWEND_C'] = '';
            }else if ($lotime == 33){
                $mwhere['PHPYUNBTWSTART_C'] = '';
                $mwhere['login_date'][] = array('<', strtotime('-6 month'));
                $mwhere['login_date'][] = array('>=', strtotime('-12 month'));
                $mwhere['PHPYUNBTWEND_C'] = '';
            }else if ($lotime == 34){
                $mwhere['login_date'] = array('<', strtotime('-1 year'));
            }
        }
        if($_POST['source']){
            $mwhere['source'] = $_POST['source'];
        }
        $mUids = array();
        $UserinfoM = $this->MODEL('userinfo');
        if(!empty($mwhere)){
            $uidList = $UserinfoM->getList($mwhere, array('field' => '`uid`'));
            if(!empty($uidList)){
                foreach($uidList as $uv){
                    $mUids[] = $uv['uid'];
                }
            }else{
                $mUids = array(0);
            }
            $where['uid'][] = array('in', pylode(',', $mUids));
        }
        if($_POST['rating']){
            $where['rating'] = $_POST['rating'];
        }
        $toDay = strtotime(date('Y-m-d'));
        if($_POST['time']){
            $time = intval($_POST['time']);
            if($time == 5){
                $where['PHPYUNBTWSTART_A'] = '';
                $where['vipetime'][] = array('>', '0', 'AND');
                $where['vipetime'][] = array('<', $toDay, 'AND');
                $where['PHPYUNBTWEND_A'] = '';
            }else{
                if($time == 1){
                    $num = '+7 day';
                }elseif($time == 2 ){
                    $num = '+1 month';
                }elseif($time == 3){
                    $num = '+6 month';
                }elseif($time == 4){
                    $num = '+1 year';
                }
                $where['PHPYUNBTWSTART_B'] = '';
                $where['vipetime'][] = array('>', time(), 'AND');
                $where['vipetime'][] = array('<', strtotime($num), 'AND');
                $where['PHPYUNBTWEND_B'] = '';
            }
        }
        if($_POST['rec']){
            $rec = intval($_POST['rec']);
            if($rec == 1){
                $where['rec'] = '1';
                $where['hottime'] = array('>', time());
            }elseif ($rec == 2){
                $where['rec'] = '0';
            }elseif ($rec == 3){
                $where['rec'] = '1';
                $where['hottime'] = array('<', time());
            }
        }
        if($_POST['gw']){
            if(intval($_POST['gw']) == 1){
                $where['crm_uid'] = array('<>', '0');
            }else{
                $where['crm_uid'] = '0';
            }
        }

        if (is_array($_POST['times']) && count($_POST['times']) == 2) {
            if ($_POST['time_type'] == 'lotime') {
                $where['PHPYUNBTWSTART_C'] = '';
                $where['login_date'][] = array('>=', strtotime($_POST['times'][0] . ' 00:00:00'));
                $where['login_date'][] = array('<=', strtotime($_POST['times'][1] . ' 23:59:59'));
                $where['PHPYUNBTWEND_C'] = '';
            }
            if ($_POST['time_type'] == 'adtime') {
                $mwhere['PHPYUNBTWSTART_C'] = '';
                $mwhere['reg_date'][] = array('>=', strtotime($_POST['times'][0] . ' 00:00:00'));
                $mwhere['reg_date'][] = array('<=', strtotime($_POST['times'][1] . ' 23:59:59'));
                $mwhere['PHPYUNBTWEND_C'] = '';
            }
        }

        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company', $where, $page, array('limit' => $pageSize));
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            //limit order 只有在列表查询时才需要
            if($_POST['order']){
                $where['orderby'] =	$_POST['t'] . ',' . $_POST['order'];
            }else if($_POST['time'] == '5'){
                $where['orderby'] =	array('vipetime,desc','uid,desc');
            }else if($_POST['time']){
                $where['orderby'] =	array('vipetime,asc');
            }else if($_POST['lotime']){
                $where['orderby'] =	array('login_date,desc');
            }else{
                $where['orderby'] =	'uid,desc';
            }
            $where['limit'] = $pages['limit'];
            $ListNew = $ComM->getList($where,array('utype' => 'admin'));
            // 查询本场网招参会企业，在待添加企业列表中展示，企业是否已参加
            $zphnetM = $this->MODEL('zphnet');
            $netcom = $zphnetM->getZphnetComList(array('zid' => $_POST['id']), array('field' => 'uid'));
            if (!empty($netcom)){
                foreach ($netcom as $v){
                    $ncuid[] = $v['uid'];
                }
            }
            foreach ($ListNew['list'] as $key => $val){
                $ListNew['list'][$key]['wxBindmsg'] = $this->wxBindState($val);
                $ListNew['list'][$key]['join'] = 0;
                if (!empty($ncuid)){
                    if (in_array($val['uid'], $ncuid)){
                        $ListNew['list'][$key]['join'] = 1;
                    }
                }
            }
            unset($where['limit']);
        }
        $rt['list'] = $ListNew['list'] ? $ListNew['list'] : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];

        $rt['today_etime'] = strtotime(date('Y-m-d 23:59:59'));
        $this->render_json(0, '', $rt);
    }

    //保存单个参会企业
    function comaddsave_action(){
        $zphnetM = $this->MODEL('zphnet');
        $_POST['uid'] =	intval($_POST['comid']);
        $_POST['zid'] =	intval($_POST['zphid']);
        $return = $zphnetM->addZphnetCom($_POST);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    //保存多个参会企业
    function muticomAddSave_action(){
        $zphnetM = $this->MODEL('zphnet');
        $data['comid'] = $_POST['comid'];
        $data['zid'] = intval($_POST['zphid']);
        $return = $zphnetM->addZphnetMutiCom($data);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    // 添加推荐企业分类
    function getClass_action(){
        $zphnetM = $this->MODEL('zphnet');
        $where = array('keyid' => 0, 'orderby' => 'sort,desc');

        if (isset($_POST['keyword']) && $_POST['keyword'] !== '') {
            $where['name'] = array('like', $_POST['keyword']);
        }

        $position = $zphnetM->getClassList($where);
        $this->render_json(0, '', array('position' => $position));
    }
    //分类管理
    function classManage_action()
    {
        $zphnetM = $this->MODEL('zphnet');
        //查询子类别
        if($_POST['id']){
            $id	= $_POST['id'];
            $whereOne['id'] = $id;
            $whereTwo['keyid'] = $id;
            $whereTwo['orderby'] = 'sort,desc';
            $onejob = $zphnetM->getClass($whereOne);
            $twojob = $zphnetM->getClassList($whereTwo);
            $onejob['children'] = $twojob;
        }
        $rt['class'] = array($onejob);
        $this->render_json(0, '', $rt);
    }

    //添加推荐类别保存
    function saveClass_action(){
        $arr = array(
            'ctype' => $_POST['stype'],
            'name' => explode('-', $_POST['name']),
            'keyid' => $_POST['nid']
        );
        $zphnetM = $this->MODEL('zphnet');
        $return = $zphnetM->addClass($arr);
        if ($return['msg'] == 2) {
            $msg = '添加成功';
            $err = 0;
        } else if ($return['msg'] == 1) {
            $msg = '类别名称已存在';
            $err = 1;
        } else {
            $msg = '添加失败';
            $err = 1;
        }
        $this->render_json($err, $msg);
    }

    // 推荐类别名称修改
    function ajax_action()
    {
        if($_POST['id']){
            $zphnetM = $this->MODEL('zphnet');
            if (!empty($_POST['name'])){
                $uparr['name'] = $_POST['name'];
            }
            if (!empty($_POST['sort'])){
                $uparr['sort'] = $_POST['sort'];
            }
            $zphnetM->upClass(array('id' => $_POST['id']), $uparr);
            $this->render_json(0, '修改成功');
        }
    }

    // 删除推荐类别
    function delZphnetClass_action()
    {
        if($_POST['del']){
            $delid = $_POST['del'];
        }
        $zphnetM = $this->MODEL('zphnet');
        $return = $zphnetM->delClass($delid);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    // 参会企业分配展会区域
    function setComZw_action()
    {
        $zphnetM = $this->MODEL('zphnet');
        $return = $zphnetM->upZphnetCom(array('id' => array('in', $_POST['zphcomid'])), array('zw' => $_POST['zw']));
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    //招聘会前台显示情况修改
    function upisopen_action(){
        if($_POST['pid']) {
            $zphnetM = $this->MODEL('zphnet');
            $return = $zphnetM->upIsOpen($_POST['pid'], $_POST['is_open']);
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->admin_json(1, $return['msg']);
            }
        }
    }

    function ajaxsort_action(){
        if($_POST['id']){
            $zphnetM = $this->MODEL('zphnet');
            $uparr['sort'] = intval($_POST['sort']);
            $zphnetM->upZphnetCom(array('id' => $_POST['id']), $uparr);
            $this->render_json(0, '操作成功');
        }
    }


    //根据选择的企业展示前台可以显示的职位列表
    function getZphJoblist_action(){

        $JobM	=	$this->MODEL('job');

        $comid 	= 	intval($_POST['comid']);
        $type = $_POST['type'];
        if($type=='admin'){
            $zphnetM = $this -> MODEL('zphnet');
            $comzph = $zphnetM->getZphnetCom(array('id'=>$_POST['id']));
            $jobids = $comzph['jobid']?explode(',',$comzph['jobid']):array();
            $rows = $JobM->getList(array('uid' => $comid, 'state' => '1', 'r_status' => array('<>', '2'), 'status' => array('<>', '1')));
            if (empty($jobids) && $rows['list']){
                foreach ($rows['list'] as $v){
                    $jobids[] = $v['id'];
                }
            }
            $arr = array(
                'jobIds' =>$jobids,
                'rows'=>$rows['list']?$rows['list']:array()
            );
        }else {
            $rows = $JobM->getList(array('uid' => $comid, 'state' => '1', 'r_status' => array('<>', '2'), 'status' => array('<>', '1')));
            $arr = array(
                'rows'=>$rows['list']
            );
        }
        $this->render_json(0,'',$arr);
    }


}