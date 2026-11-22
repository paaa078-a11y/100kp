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

class crm_model extends model{

    /**
     * @desc   引用cache类
     * @param array $whereData
     * @return array|string|void
     */
    private function getCache($whereData = array())
    {
        require_once ('cache.model.php');
        $CacheM = new cache_model($this->db, $this->def);

        return  $CacheM -> GetCache($whereData);
    }

    /**
     * @desc 新增业务员任务
     * @param array $data
     * @return array
     */
    public function addWaitingTask($data = array()) {

        if (!empty($data)) {

            if(empty($data['auid']) || empty($data['uid']) || empty($data['comid'])){
                $return = array(
                    'errcoder'  =>  8,
                    'msg'       =>  '参数错误，请重试！'
                );
            }

            if (empty($data['type'])) {
                $return = array(
                    'errcoder'  =>  8,
                    'msg'       =>  '请选择任务类型！'
                );
            }

            if (empty($data['stime']) && $data['type']=="22") {
                $return = array(
                    'errcoder'  =>  8,
                    'msg'       =>  '请填写任务时间！'
                );
            }

            if (empty($data['content'])) {
                $return = array(
                    'errcoder'  =>  8,
                    'msg'       =>  '请填写任务描述！'
                );
            }

            $auid   =   intval($data['auid']);
            $uid    =   intval($data['uid']);
            $comid  =   intval($data['comid']);

            $value  =   array(

                'auid'      =>  $auid,
                'uid'       =>  $uid,
                'comid'     =>  $comid,
                'type'      =>  intval($data['type']),
                'content'   =>  $data['content'],
                'ctime'     =>  time(),
                'status'    =>  '1',
            );

            if($data['stime']){
                $value['stime'] =   $data['stime'];
            }

            $cache          =   $this -> GetCache(array('crm'));

            $return['id']   =   $this->insert_into('crm_work_plan', $value);

            $lContent       =   '添加任务；任务ID：'.$return['id'].'，任务类型：'.$cache['crmclass_name'][$data['type']];
            $this->addCrmLog($lContent, 5, $comid, $auid);

            $return['errcode']  =   $return['id'] ? 9 : 8;
            $msg                =   $return['id'] ? '成功' : '失败';
            $return['msg']      =   '任务新建'.$msg;

            return $return;
        }
    }


    function getTaskNum($uids)
    {
        if (!empty($uids)) {

            if (is_array($uids)) {

                $crmUid = pylode(',', $uids);
            } else {

                $crmUid = $uids;
            }

            $arr    =   array();
            //  任务总数
            $taskAllNum =   $this->select_num('crm_work_plan', array('uid' => array('in', $crmUid)));
            if ($taskAllNum > 0) {
                $arr['taskAllNum']  =   $taskAllNum;
            }
            //  进行中
            $taskStatusNum1 =   $this->select_num('crm_work_plan', array('uid' => array('in', $crmUid), 'status' => 1));
            if ($taskStatusNum1 > 0) {
                $arr['taskStatusNum1']  =   $taskStatusNum1;
            }
            //  已完成
            $taskStatusNum2 =   $this->select_num('crm_work_plan', array('uid' => array('in', $crmUid), 'status' => 2));
            if ($taskStatusNum2 > 0) {
                $arr['taskStatusNum2']  =   $taskStatusNum2;
            }
            //  未完成
            $taskStatusNum3 =   $this->select_num('crm_work_plan', array('uid' => array('in', $crmUid), 'status' => 3));
            if ($taskStatusNum3 > 0) {
                $arr['taskStatusNum3']  =   $taskStatusNum3;
            }
            //  已取消
            $taskStatusNum4 =   $this->select_num('crm_work_plan', array('uid' => array('in', $crmUid), 'status' => 4));
            if ($taskStatusNum4 > 0) {
                $arr['taskStatusNum4']  =   $taskStatusNum4;
            }

            return json_encode($arr);
        }
    }

    /**
     * @desc 查询任务记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    function  getTaskInfo($where = array(), $data = array())
    {

        $select =   $data['field'] ? $data['field'] : '*';

        $Info   =   $this -> select_once('crm_work_plan', $where, $select);
        if($Info['stime']){
            $Info['stime_n']   =  date('Y-m-d H:i:s',$Info['stime']);
        }
        return $Info;
    }


    /**
     * @desc 查询任务记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    function getTaskList($where = array(), $data = array())
    {

        $select     =   $data['field'] ? $data['field'] : '*';
        $taskList   =   $this->select_all('crm_work_plan', $where, $select);

        $auids = $uids = $comids = array();
        if (!empty($taskList) && isset($data['utype']) && $data['utype'] == 'crm') {

            $cache  =   $this->GetCache(array('crm'));
            foreach ($taskList as $value) {
                if (!in_array($value['comid'], $comids)) {
                    $comids[]   =   $value['comid'];
                }
                if (!in_array($value['uid'], $uids)) {
                    $uids[]     =   $value['uid'];
                }
                if (!in_array($value['auid'], $auids)) {
                    $auids[]    =   $value['auid'];
                }
            }

            $auids      =   array_unique(array_merge($auids, $uids));

            $comList    =   $this->select_all('company', array('uid' => array('in', pylode(',', $comids))), '`uid`,`name`,`crm_uid`,`pcrmuid`,`release_time`');
            foreach ($comList as $ck => $cv){
                if (!empty($cv['crm_uid']) && !in_array($cv['crm_uid'], $auids)){
                    $auids[]    =   $cv['crm_uid'];
                }
                if (!empty($cv['pcrmuid']) && !in_array($cv['pcrmuid'], $auids)){
                    $auids[]    =   $cv['pcrmuid'];
                }
            }

            $adminList  =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $auids))), '`uid`,`name`');

            $weekArray  =   array('日', '一', '二', '三', '四', '五', '六');

            foreach ($taskList as $k => $v) {

                $taskList[$k]['ctime_n']    =   date('Y-m-d', $v['ctime']);

                if (intval($v['stime']) > 0) {

                    $sTime  =   date('Y年n月j日', $v['stime']).'（周'.$weekArray[date("w", $v['stime'])].'）';
                    $sTime  .=  date('H:i', $v['stime']) != '00:00' ? date('G点', $v['stime']): '';
                    $taskList[$k]['stime_n']    =   $sTime;
                }
                if (intval($v['etime']) > 0) {

                    $eTime  =   date('Y年n月j日', $v['etime']).'（周'.$weekArray[date("w", $v['etime'])].'）';
                    $eTime  .=  date('H:i', $v['etime']) != '00:00' ? date('G点', $v['etime']): '';
                    $taskList[$k]['etime_n']    =   $eTime;
                }

                if ($v['status'] == 1){
                    $taskList[$k]['status_n']   =   '进行中';
                } elseif ($v['status'] == 2){
                    $taskList[$k]['status_n']   =   '已完成';
                } elseif ($v['status'] == 3){
                    $taskList[$k]['status_n']   =   '未完成';
                } elseif ($v['status'] == 4){
                    $taskList[$k]['status_n']   =   '已取消';
                }

                $taskList[$k]['type_n']         =   $cache['crmclass_name'][$v['type']];
                $taskList[$k]['reason']         =   !empty($v['reason']) ? $v['reason'] : '--';
                $taskList[$k]['isExpire']       =   0;  //  是否过期
                if ($v['stime'] < time() && $v['status'] != 2){ //  未完成状态，执行时间小于当前时间，则已过期

                    $taskList[$k]['isExpire']   =   1;
                }

                foreach ($comList as $uv) {
                    if ($v['comid'] == $uv['uid']) {
                        $taskList[$k]['name'] = $uv['name'];
                        $taskList[$k]['crm_uid'] = $uv['crm_uid'];
                        $taskList[$k]['pcrmuid'] = intval($uv['pcrmuid']) > 0 ? $uv['pcrmuid'] : 0;
                        $taskList[$k]['sf_time'] = intval($uv['pcrmuid']) > 0 && intval($uv['release_time']) > 0 ? date('Y-m-d H:i', $uv['release_time']) : '';
                    }
                }
            }

            foreach ($taskList as $k => $v) {

                $taskList[$k]['cname']   =   '';
                $taskList[$k]['aname']   =   '';
                $taskList[$k]['crm_name']=   '';
                $taskList[$k]['pcrmname']=   '';

                foreach ($adminList as $av) {

                    if ($v['uid'] == $av['uid']) {
                        $taskList[$k]['cname']  =   $av['name'];    //  执行者
                    }
                    if ($v['auid'] == $av['uid']) {
                        $taskList[$k]['aname']  =   $av['name'];    //  发布者
                    }
                    if (intval($v['crm_uid']) > 0 && $v['crm_uid'] == $av['uid']) {
                        $taskList[$k]['crm_name']   =   $av['name'];//  客户经理
                    }
                    if (intval($v['pcrmuid']) > 0 && $v['pcrmuid'] == $av['uid']) {
                        $taskList[$k]['pcrmname']   =   $av['name'];//  释放前客户经理
                    }
                }
            }
        }
        return $taskList;
    }

    /**
     * @desc 更新任务
     * @param array $data :tValue->更新数据 :auid 业务员uid
     * @param array $where
     * @return bool
     */
    function upTask($data = array(), $where = array())
    {

        if (!empty($data)) {

            $taskId =   $where['id'];

            $tValue =   $data['tValue'];
            $result =   $this->update_once('crm_work_plan', $tValue, $where);

            if ($result) {

                $auid   =   $data['auid'];

                if ($tValue['status'] == '2') {
                    $status_n   =   '完成';
                } elseif ($tValue['status'] == '3') {
                    $status_n   =   '未完成';
                } else {
                    $status_n   =   '取消';
                }

                $lContent   =   "标记任务；任务（ID：".$taskId."）状态 -> ".$status_n;
                $this->addCrmLog($lContent, 5, '', $auid);
            }
        }

        return $result;
    }

    /**
     * @desc 删除待办任务
     * @param $delId
     * @param array $data
     * @return array
     */
    function delTask($delId, $data = array())
    {

        if (is_array($delId)) {

            $delId  =   pylode(',', $delId);
        }

        $result     =   $this->delete_all('crm_work_plan', array('id' => array('in', $delId)), '');

        if ($result) {

            $lContent   =   "删除工作任务（ID：".$delId."）";
            $this->addCrmLog($lContent, 5, '', $data['auid']);
            $return     =   array('errcode' => 9, 'msg' => '删除成功');
        } else {

            $return     =   array('errcode' => 8, 'msg' => '删除失败');
        }

        return $return;
    }

    /**
     * @desc    新增业务员跟进记录
     * @param array $data
     * @return bool
     */
    public function addConcern($data = array()) {

        if (!empty($data)) {

            $value  =   array(

                'uid'       =>  intval($data['uid']),
                'comid'     =>  intval($data['comid']),
                'time'      =>  strtotime($data['time']),
                'type'      =>  intval($data['type']),
                'content'   =>  $data['content'],
                'status'    =>  intval($data['status']),
                'note'      =>  $data['note'],
                'atime'     =>  time()
            );

            $nid    =   $this -> insert_into('crmnew_concern', $value);

            if ($nid) {
                $this->update_once('company', array('isfollow' => 1), array('uid' => $data['comid']));
            }
            return  $nid;
        }
    }

    /**
     * @desc  查询跟进记录数量
     * @param array $whereData
     * @return array|bool|false|string|void
     */
    function getConcernNum($whereData = array())
    {

        return $this -> select_num('crmnew_concern', $whereData);

    }

    /**
     * @desc 查询跟进记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    function getConcernInfo($where = array(), $data = array())
    {

        $select =   $data['field'] ? $data['field'] : '*';
        $info   =   $this->select_once('crmnew_concern', $where, $select);
        $info['time_n']    =   date('Y-m-d H:i:s', $info['ftime']);

        $crmLink=   $this->select_once('crm_link', array('link_man' => $info['link_man'], 'link_mobile' => $info['link_tel']), '`id`');

        if (!empty($crmLink)) {

            $info['link_id']    =   $crmLink['id'];
        } else {

            $info['link_id']    =   -1;
        }

        if ($info['taskid'] > 0){

            $taskInfo   =   $this->select_once('crm_work_plan', array('id' => $info['taskid']));
            $info['taskTime']   =   date('Y-m-d H:i:s', $taskInfo['stime']);
            $info['n_time']   =  $taskInfo['stime'];
        }

        return $info;
    }

    /**
     * @desc 查询跟进记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    function getConcernList($where = array(), $data = array())
    {

        $select         =   $data['field'] ? $data['field'] : '*';

        $concernList    =   $this -> select_all('crmnew_concern', $where, $select);

        if (!empty($concernList)) {

            if(isset($data['utype']) && $data['utype'] == 'crm'){

                $cache  =   $this->GetCache(array('crm'));

                $comids  =   $auids  =  $callLogIds = array();

                foreach ($concernList as $value){
                    $value['call_log_id'] && !in_array($value['call_log_id'], $callLogIds)  && $callLogIds[] = $value['call_log_id'];
                    $comids[]   =   $value['uid'];
                    $auids[]    =   $value['auid'];
                }

                $comList    =   $this->select_all('company', array('uid' => array('in', pylode(',', $comids))),'`uid`,`name`, `linkman`,`linktel`,`crm_uid`,`pcrmuid`,`release_time`');

                foreach ($comList as $ck => $cv){
                    if (!in_array($cv['crm_uid'], $auids)){

                        $auids[]    =   $cv['crm_uid'];
                    }
                    if (!empty($cv['pcrmuid']) && !in_array($cv['pcrmuid'], $auids)){
                        $auids[]    =   $cv['pcrmuid'];
                    }
                }
                $ausers     =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $auids))),'`uid`,`name`');
                $callLogs   =   $this->select_all('staff_call_log', array('id' => array('in', pylode(',', $callLogIds))));
                $idToPath   =   array();
                foreach ($callLogs as $logV) {
                    $idToPath[$logV['id']]['path'] = $logV['oss_record_path'] ? $logV['oss_record_path'] : '';
                    $idToPath[$logV['id']]['time_length'] = $logV['time_length'] ? $logV['time_length'] : 0;
                }
                foreach ($concernList as $k => $v){

                    if($v['type']){
                        $concernList[$k]['type_n']  =   $cache['crmclass_name'][$v['type']];
                    }

                    if($v['ftime']){
                        $concernList[$k]['ftime_n'] =   date('Y-m-d H:i:s', $v['ftime']);
                    }else{
                        $concernList[$k]['ftime_n'] =   date('Y-m-d H:i:s', $v['atime']);
                    }

                    $content    =   str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $v['content']);
                    $content    =   htmlspecialchars_decode($content);
                    preg_match_all('<img(.*?)src=\"(.+?)\".*?>',$content,$res);

                    $imgArr     =   array();
                    if(!empty($res[2])){
                        foreach($res[2] as $rv){
                            if(strpos($rv,'https:')===false && strpos($rv,'http:')===false){
                                $imgUrl     =   checkpic($rv);
                            }else{
                                $imgUrl     =   $rv;
                            }
                            $imgArr[]       =   $imgUrl;
                        }
                    }
                    $concernList[$k]['content'] =   strip_tags($content);
                    $concernList[$k]['imgArr']  =   $imgArr;
                    $concernList[$k]['atime_n'] =   date('Y-m-d H:i:s', $v['atime']);

                    foreach ($comList as $ck=>$cv){

                        if ($v['uid'] == $cv['uid']) {

                            $concernList[$k]['crm_uid'] =   $cv['crm_uid'];
                            $concernList[$k]['pcrmuid'] =   intval($cv['pcrmuid']) > 0 ? $cv['pcrmuid'] : 0;
                            $concernList[$k]['sf_time'] =   intval($cv['pcrmuid']) > 0 && intval($cv['release_time']) > 0 ? date('Y-m-d H:i', $cv['release_time']) : '';
                            $concernList[$k]['com_name']=   $cv['name'];
                            $concernList[$k]['name']    =   !empty($cv['name']) ? $cv['name'] : '<em style="color: #FFCC33;">客户信息未完善</em>';
                            $concernList[$k]['linkman'] =   $cv['linkman'];
                            $concernList[$k]['moblie']  =   $cv['linktel'];

                            if (empty($v['link_man']) && empty($v['link_tel'])){

                                $concernList[$k]['link_man']    =   $cv['linkman'];
                                $concernList[$k]['link_tel']    =   $cv['linktel'];
                            }
                        }
                    }
                    foreach ($ausers as $av){
                        if ($v['auid'] == $av['uid']) {

                            $concernList[$k]['aname']   =   $av['name'];
                        }
                    }
                    $concernList[$k]['oss_record_path'] = '';
                    $concernList[$k]['time_length_n'] = 0;
                    if (isset($idToPath[$v['call_log_id']]['path']) && $idToPath[$v['call_log_id']]['path']) {
                        $concernList[$k]['oss_record_path'] =  $idToPath[$v['call_log_id']]['path'];
                    }
                    if (isset($idToPath[$v['call_log_id']]['time_length']) && $idToPath[$v['call_log_id']]['time_length']) {
                        $concernList[$k]['time_length_n'] = $this->timeFormat($idToPath[$v['call_log_id']]['time_length']);
                    }
                    if ($v['is_note'] == 1){

                        $concernList[$k]['noteContent'] =   strip_tags($v['note_content']);
                    }

                    if (isset($v['crm_type'])) {
                        $concernList[$k]['crm_type_n'] = $v['crm_type'] ? $cache['crmclass_name'][$v['crm_type']] : ''; // 客户等级
                    }
                    if (isset($v['crm_status'])) {
                        $concernList[$k]['crm_status_n'] = $v['crm_status'] ? $cache['crmclass_name'][$v['crm_status']] : ''; // 客户标签
                    }
                }
                foreach ($concernList as $k => $v){
                    $concernList[$k]['pcrmname']    =   '--';
                    foreach ($ausers as $av){
                        if ($v['crm_uid'] == $av['uid']){
                            $concernList[$k]['crm_name']=   $av['name'];
                        }
                        if (intval($v['pcrmuid']) > 0 && $v['pcrmuid'] == $av['uid']){
                            $concernList[$k]['pcrmname']=   $av['name'];
                        }
                    }
                }
            }

            if (isset($data['utype']) && $data['utype'] == 'crm_com_info'){

                $concernList    =   $this->getComInfoFollowList($concernList);
            }
        }
        return $concernList;
    }

    /**
     * @desc 客户管理新页面 - 跟进记录
     * @param array $list
     * @return array
     */
    private function getComInfoFollowList($list = array())
    {

        if (!empty($list)) {

            $cache  =   $this->GetCache(array('crm'));

            $comIdArr   =   array();
            $aUidArr    =   array();
            $fIdArr     =   array();
            $pUidArr    =   array();
            $taskIdArr  =   array();
            $callLogIds =   array();
            foreach ($list as $k => $v) {

                if (!in_array($v['uid'], $comIdArr) && empty($v['link_man'])){

                    $comIdArr[] =   $v['uid'];
                }
                if (!in_array($v['auid'], $aUidArr)){

                    $aUidArr[] =   $v['auid'];
                }
                $fIdArr[]   =   $v['id'];

                if ($v['is_note'] == 1 && !in_array($v['note_uid'], $pUidArr)){
                    $pUidArr[]  =   $v['note_uid'];
                }
                $taskIdArr[]    =   $v['taskid'];

                $v['call_log_id'] && !in_array($v['call_log_id'], $callLogIds)  && $callLogIds[] = $v['call_log_id'];
            }

            $comList    =   $this->select_all('company', array('uid' => array('in', pylode(',', $comIdArr))), '`uid`,`linkman`,`linkjob`,`linktel`,`linkphone`');
            $adminList  =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $aUidArr))), '`uid`,`name`');
            $noteUser   =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $pUidArr))), '`uid`,`name`');
            $imgList    =   $this->select_all('crmnew_concern_pic', array('fid' => array('in', pylode(',', $fIdArr))));
            $taskList   =   $this->select_all('crm_work_plan', array('id' => array('in', pylode(',', $taskIdArr))), '`id`,`stime`');

            if (!empty($callLogIds)) {
                $callLogs = $this->select_all('staff_call_log', array('id' => array('in', pylode(',', $callLogIds))));
                $idToPath = array();
                foreach ($callLogs as $logV) {
                    $idToPath[$logV['id']]['path'] = $logV['oss_record_path'] ? $logV['oss_record_path'] : '';
                    $idToPath[$logV['id']]['time_length'] = $logV['time_length'] ? $logV['time_length'] : 0;
                }
            }
            foreach ($list as $k => $v) {

                $list[$k]['up']         =   $v['atime'] > strtotime('today') ? 1 : 2;

                $list[$k]['f_type_n']   =   $cache['crmclass_name'][$v['type']];        //  跟进方式
                foreach ($adminList as $ak => $av) {

                    if ($av['uid'] == $v['auid']){

                        $list[$k]['f_user'] =   $av['name'];                            //  跟进人员
                    }
                }
				if($v['ftime']){
					$list[$k]['f_time_n']   =   date('Y-m-d H:i:s', $v['ftime']);    //  跟进时间
				}
                if (empty($v['link_man'])){
                    foreach ($comList as $ck => $cv){

                        if ($v['uid'] == $cv['uid'] ){

                            $list[$k]['link_man']   =   $cv['linkman'];
                            $list[$k]['link_job']   =   $cv['linkjob'];
                            $list[$k]['link_tel']   =   $cv['linktel'] ? $cv['linktel'] : $cv['linkphone'];
                        }
                    }
                }

                $content    =   str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $v['content']);
                $content    =   htmlspecialchars_decode($content);
                preg_match_all('<img(.*?)src=\"(.+?)\".*?>',$content,$res);

                $imgArr     =   array();
                if(!empty($res[2])){
                    foreach($res[2] as $rv){
                        if(strpos($rv,'https:')===false && strpos($rv,'http:')===false){
                            $imgUrl     =   checkpic($rv);
                        }else{
                            $imgUrl     =   $rv;
                        }
                        $imgArr[]       =   $imgUrl;
                    }
                }
                $list[$k]['content_n']  =   strip_tags($content);
                if (!empty($imgList)){
                    foreach ($imgList as $ik => $iv){
                        if ($v['id'] == $iv['fid']){
                            $imgArr[]   =   checkpic($iv['picurl']);
                        }
                    }
                }
                $list[$k]['imgArr']     =   $imgArr;

                if ($v['is_note'] == 1){
                    foreach ($noteUser as $nk => $nv) {
                        if ($nv['uid'] == $v['note_uid']){
                            $list[$k]['note_user']  =   $nv['name'];    //  批注人员
                        }
                    }
                    $list[$k]['noteTime']   =   date('Y-m-d H:i', $v['note_time']);

                    $noteContent        =   str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $v['note_content']);
                    $noteContent        =   htmlspecialchars_decode($noteContent);
                    preg_match_all('<img(.*?)src=\"(.+?)\".*?>',$noteContent,$noteRes);

                    $noteImgArr         =   array();
                    if(!empty($noteRes[2])){
                        foreach($noteRes[2] as $nrv){
                            if(strpos($nrv,'https:')===false && strpos($nrv,'http:')===false){
                                $noteImgUrl =   checkpic($nrv);
                            }else{
                                $noteImgUrl =   $nrv;
                            }
                            $noteImgArr[]   =   $noteImgUrl;
                        }
                    }
                    $list[$k]['noteContent']=   strip_tags($noteContent);
                    $list[$k]['noteImgArr'] =   $noteImgArr ;
                }

                if (!empty($taskList)){
                    foreach ($taskList as $tk => $tv){

                        if ($v['taskid'] == $tv['id']){

                            $list[$k]['task_time']  =   date('Y-m-d H:i', $tv['stime']);
                        }
                    }
                }

                if (isset($idToPath[$v['call_log_id']]['path']) && $idToPath[$v['call_log_id']]['path']) {
                    $list[$k]['oss_record_path'] =  $idToPath[$v['call_log_id']]['path'];
                }
                if (isset($idToPath[$v['call_log_id']]['time_length']) && $idToPath[$v['call_log_id']]['time_length']) {
                    $list[$k]['time_length_n'] = $this->timeFormat($idToPath[$v['call_log_id']]['time_length']);
                }

                if (isset($v['crm_type'])) {
                    $list[$k]['crm_type_n'] = $v['crm_type'] ? $cache['crmclass_name'][$v['crm_type']] : ''; // 客户等级
                }
                if (isset($v['crm_status'])) {
                    $list[$k]['crm_status_n'] = $v['crm_status'] ? $cache['crmclass_name'][$v['crm_status']] : ''; // 客户标签
                }
            }
        }
        return $list;
    }

    // 格式化通话时长
    private function timeFormat($time){
        $hour = floor($time/3600);
        $leftTime = $time%3600;// 去除小时后余下的时间
        $minute = floor($leftTime/60);
        $second = $leftTime%60;// 去除分钟后余下的时间
        $hourStr = $hour >= 10 ? $hour : '0' . $hour;
        $minuteStr = $minute >= 10 ? $minute : '0' . $minute;
        $secondStr = $second >= 10 ? $second : '0' . $second;
        return $hourStr . ':' . $minuteStr . ':' . $secondStr;
    }

    /**
     * @desc    删除跟进记录
     * @param $delId
     * @param array $data
     * @return array
     */
    public function delConcern($delId, $data = array())
    {

        if (empty($delId)) {

            $return =   array(

                'errcode'   =>  8,
                'msg'       =>  '请选择要删除的数据！',
            );
        } else {
            if (is_array($delId)) {

                $delId                  =   pylode(',', $delId);
                $return['layertype']    =   1;
            } else {

                $return['layertype']    =   0;
            }

            $nid    =   $this->delete_all('crmnew_concern', array('id' => array('in', $delId)), '');

            //  同步跟新任务数据关联的跟进记录ID
            if ($nid) {

                $this->update_once('crm_work_plan', array('cid' => '0'), array('cid' => array('in', $delId)));

                if (!empty($data['auid'])) {

                    $lContent   =   "删除跟进记录（ID：" . $delId . "）";
                    $this->addCrmLog($lContent, 3, '', $data['auid']);
                }
            }

            $return['msg']      =   '跟进记录';
            $return['errcode']  =   $nid ? '9' : '8';
            $return['msg']      .=  $nid ? '删除成功！' : '删除失败！';
        }

        return $return;
    }

    /**
     * @desc CRM 业务员  录入订单
     * @param array $data
     * @param int $is_crm
     * @return array
     */
    public function addDeal($data = array(), $is_crm = 0)
    {

        if (!empty($data)) {

            if (empty($data['uid']) || empty($data['auid'])) {

                $return =   array('errcode' => 8, 'msg' => '参数错误，请重试');
            }

            if ($is_crm == 1){

                if (empty($data['order_type'])) {
                    $return =   array(
                        'errcode'   =>  8,
                        'msg'       =>  '请选择支付方式'
                    );
                }

                if (empty($data['type'])) {
                    $return =   array(
                        'errcode'   =>  8,
                        'msg'       =>  '请选择订单类型'
                    );
                }else if ($data['type'] == 1){
                    if (empty($data['rating'])) {
                        $return =   array(
                            'errcode'   =>  8,
                            'msg'       =>  '请选择会员套餐'
                        );
                    }
                }
                $status         =   2;
            }else{

                $status         =   0;
            }

            if (!empty($return)) {

                return $return;
            }

            $uid    =   intval($data['uid']);
            $auid   =   intval($data['auid']);

            $comInfo=   $this->select_once('company', array('uid' => $uid), '`crm_uid`');
            $crm_uid=   intval($comInfo['crm_uid']);

            $dingdan    =   time().rand(10000, 99999);
            $orderData  =   array(
                'uid'           =>  $uid,
                'order_id'      =>  $dingdan,
                'order_type'    =>  $data['order_type'],
                'order_price'   =>  round($data['order_price'], 2),
                'order_time'    =>  time(),
                'order_remark'  =>  $data['order_remark'],
                'type'          =>  $data['type'],
                'rating'        =>  $data['rating'],
                'integral'      =>  $data['integral'],
                'crm_uid'       =>  $crm_uid,
                'usertype'      =>  2,
                'did'           =>  $this->config['did'],
                'is_crm'        =>  $is_crm,
                'status'        =>  $status
            );
            if ($data['type'] == 1){

                $ratingInfo                 =   $this->select_once('company_rating',array('id' => $data['rating']),'`name`');
                $orderData['order_remark'] .=   $orderData['order_remark'] ? "，" : "";
                $orderData['order_remark'] .=   "会员信息：".$ratingInfo['name'];
            }
            if ($is_crm == 1){  // 业务员开通订单

                if ($data['type'] == 1 && !empty($data['rating'])){ //  业务员录入会员套餐订单，根据传递的业务模式逻辑判断处理

                    if ($data['deal_model'] == 1) {                 //  需要添加业务处理

                        $orderData['order_state'] = 3;
                    }elseif ($data['deal_model'] == 2){             //  不用业务处理

                        $orderData['order_state'] = 2;
                        //  去掉业务关键字段信息
                        unset($orderData['rating']);
                    }
                }else{                                              //  业务员录入其他订单，没有业务逻辑，仅仅作为账单数据

                    $orderData['order_state']   =   2;
                }
            }else{                                                  // 业务员录入客户选择订单，需要管理员确认到账

                $orderData['order_state']   =   3;
            }

            $return['id']       =   $this->insert_into('company_order', $orderData);

            if ($return['id']) {
                // 保存合同图片
                $time = time();
                $htpics = array();
                if (isset($data['uimage'])){//  手机站后台订单上传合同图片
                    foreach ($data['uimage'] as $k => $v) {
                        $upArr = array(
                            'dir' => 'order_ht',
                            'base' => $v,
                        );
                        $result = $this->upload($upArr);
                        if (!empty($result['picurl'])){
                            $htpics[$k]['picurl'] = $result['picurl'];
                            $htpics[$k]['order_id'] = $return['id'];
                            $htpics[$k]['ctime'] = $time;
                        }
                    }
                } else if (isset($data['htpics'])) {// pc后台订单上传合同图片
                    foreach ($data['htpics'] as $k => $v){
                        $htpics[$k]['picurl'] = $v;
                        $htpics[$k]['order_id'] = $return['id'];
                        $htpics[$k]['ctime'] = $time;
                    }
                }
                $htpics && $this->DB_insert_multi('order_ht_pic', $htpics);

                $content    =   '录入新订单（ID：'.$return['id'].'）';
                $remark     =   '';
                if ($crm_uid != $auid) {
                    $remark .=  '协助录入；';
                }
                if (!empty($orderData['order_remark'])) {
                    $remark .=  '订单备注：'.$orderData['order_remark'];
                }
                $logData        =   array(
                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  3,
                    'content'   =>  $content,
                    'remark'    =>  $remark,
                    'ctime'     =>  time()
                );
                $this->addCrmComLog($logData);
                $lContent   =   '录入订单：'.$dingdan;
                if ($crm_uid != $auid){
                    $lContent   .=  '；协助录入';
                }
                $this->addCrmLog($lContent, 4, $uid, $auid);
            }

            $return['errcode']  =   $return['id'] ? 9 : 8;
            $msg                =   $return['id'] ? '录入成功' : '录入失败，请重试！';
            $return['msg']      =   '客户订单'.$msg;
            return $return;
        }
    }

    /**
     * @desc CRM释放记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getReleaseLogList($where = array(), $data = array())
    {

        $field  =   $data['field'] ? $data['field'] : '*';
        $List   =   $this->select_all('crm_release_log', $where, $field);
        if (!empty($List)) {

            $auids = $uids = array();
            foreach ($List as $v) {

                if (!in_array($v['auid'], $auids)) {

                    $auids[]    =   $v['auid'];
                }
                if (!in_array($v['uid'], $uids)) {

                    $uids[]     =   $v['uid'];
                }
            }
            $crmUserList    =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $auids))), '`uid`,`name`');
            $companyList    =   $this->select_all('company', array('uid' => array('in', pylode(',', $uids))), '`uid`,`name`');

            foreach ($List as $k => $v) {

                $List[$k]['time_n'] = date('Y-m-d H:i', $v['ctime']);

                foreach ($crmUserList as $val) {
                    if ($v['auid'] == $val['uid']) {

                        $List[$k]['name'] = $val['name'];
                    }
                }
                foreach ($companyList as $val) {
                    if ($v['uid'] == $val['uid']) {

                        $List[$k]['comname'] = $val['name'];
                    }
                }
            }
        }
        return $List;
    }

    /**
     * @desc    删除CRM释放记录
     *
     * @param $delId
     * @param array $data
     * @return array
     */
    public function delReleaseLog($delId, $data = array())
    {

        if (empty($delId)) {

            $return =   array('errcode' => 8, 'msg' => '请选择要删除的数据！',);
        } else {

            if (is_array($delId)) {

                $delId  =   pylode(',', $delId);
                $return['layertype'] = 1;
            } else {

                $return['layertype'] = 0;
            }

            $nid    =   $this->delete_all('crm_release_log', array('id' => array('in', $delId)), '');

            $return['errcode']  =   $nid ? 9 : 8;
            $msg                =   '释放记录（ID：'.$delId.'）删除';
            $return['msg']      =   $nid ? $msg.'成功！' : $msg.'失败！';
        }
        return $return;
    }

    /**
     * @desc 获取crm工作日志列表
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWorkLogList($where = array(), $data = array())
    {

        $field  =   $data['field'] ? $data['field'] : '*';

        $List   =   $this->select_all('crm_work_log', $where, $field);
        if (!empty($List)) {
            foreach ($List as $v) {
                $auids[]    =   $v['auid'];
            }
            $users  =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $auids))), '`uid`,`name`');
            foreach ($List as $k => $v) {

                $List[$k]['time_n'] =   date('Y-m-d H:i', $v['ctime']);
                foreach ($users as $val) {
                    if ($v['auid'] == $val['uid']) {

                        $List[$k]['name']   =   $val['name'];
                    }
                }
            }
        }
        return $List;
    }

    /**
     * @desc    新增工作日志
     * @param array $data
     * @return array
     */
    public function addWorkLog($data = array())
    {

        if (!empty($data)) {

            $return =   array();

            $value  =   array(

                'auid'      =>  intval($data['auid']),
                'title'     =>  trim($data['title']),
                'content'   =>  $data['content'],
                'ctime'     =>  time()
            );
            $return['id']   =   $this->insert_into('crm_work_log', $value);
            $lContent       =   "添加工作日志（ID：" . $return['id']."）";
            $this->addCrmLog($lContent, 6, '', $data['auid']);

            if ($return['id']) {

                $return['errcode']  =   9;
                $return['msg']      =   '添加成功';
            } else {

                $return['errcode']  =   8;
                $return['msg']      =   '添加失败';
            }
            return $return;
        }
    }

    /**
     * @desc 查询任务记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    function getWorkLogInfo($where = array(), $data = array())
    {

        $select = $data['field'] ? $data['field'] : '*';
        return $this->select_once('crm_work_log', $where, $select);
    }

    /**
     * @desc  删除工作日志
     * @param $delId
     * @param array $data
     * @return array
     */
    public function delWorkLog($delId, $data = array())
    {

        if (empty($delId)) {

            $return =   array('errcode' => 8, 'msg' => '请选择要删除的数据！');
        } else {
            if (is_array($delId)) {

                $delId = pylode(',', $delId);
                $return['layertype'] = 1;
            } else {

                $return['layertype'] = 0;
            }

            $nid    =   $this->delete_all('crm_work_log', array('id' => array('in', $delId)), '');

            if ($nid && !empty($data['auid'])) {

                $lContent = "删除工作日志（ID：" . $delId . "）";
                $this->addCrmLog($lContent, 6, '', $data['auid']);
            }

            $return['msg']      =   '工作日志';
            $return['errcode']  =   $nid ? 9 : 8;
            $return['msg']      .=  $nid ? '删除成功！' : '删除失败！';
        }
        return $return;
    }

    function getOutNum($uids)
    {
        if (!empty($uids)) {

            if (is_array($uids)) {
                $crmUid = pylode(',', $uids);
            } else {
                $crmUid = $uids;
            }

            $arr = array();
            $allNum = $this->select_num('crm_out', array('auid' => array('in', $crmUid)));
            if ($allNum > 0) {
                $arr['allNum'] = $allNum;
            }
            $status1Num = $this->select_num('crm_out', array('auid' => array('in', $crmUid), 'status' => 1));
            if ($status1Num > 0) {
                $arr['status1Num'] = $status1Num;
            }
            $status2Num = $this->select_num('crm_out', array('auid' => array('in', $crmUid), 'status' => 2));
            if ($status2Num > 0) {
                $arr['status2Num'] = $status2Num;
            }
            $status3Num = $this->select_num('crm_out', array('auid' => array('in', $crmUid), 'status' => 3));
            if ($status3Num > 0) {
                $arr['status3Num'] = $status3Num;
            }

            return json_encode($arr);
        }
    }

    /**
     * @desc    获取外出申请列表
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getOutList($where = array(), $data = array())
    {
        $field  =   $data['field'] ? $data['field'] : '*';

        $List   =   $this->select_all('crm_out', $where, $field);

        if (!empty($List)) {

            $uids   =   $comids =   array();

            foreach ($List as $v) {
                if ($v['auid'] && !in_array($v['auid'], $uids)) {
                    $uids[]     = $v['auid'];
                }
                if ($v['uid'] && !in_array($v['uid'], $comids)) {
                    $comids[]   = $v['uid'];
                }
            }

            $user   =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $uids))), '`uid`,`name`');
            $com    =   $this->select_all('company', array('uid' => array('in', pylode(',', $comids))), '`uid`,`name`,`linkman`,`linktel`');
            $cache  =   $this->getCache(array('crm'));
            foreach ($List as $k => $v) {

                $List[$k]['stime_n']    =   $v['stime'] > 0 ? date('Y-m-d H:i', $v['stime']) : '--';
                $List[$k]['etime_n']    =   $v['etime'] > 0 ? date('Y-m-d H:i', $v['etime']) : '--';

                $List[$k]['ctime_n']    =   date('Y-m-d H:i', $v['ctime']);

                $List[$k]['reason_n']   =   $cache['crmclass_name'][$v['reason']];
                if ($v['status'] == 1) {

                    $List[$k]['status_n']   =   '待审批';
                } elseif ($v['status'] == 2) {

                    $List[$k]['status_n']   =   '已同意';
                } elseif ($v['status'] == 3) {

                    $List[$k]['status_n']   =   '已拒绝';
                }

                foreach ($user as $val) {
                    if ($v['auid'] == $val['uid']) {
                        $List[$k]['name'] = $val['name'];
                    }
                }

                foreach ($com as $value) {
                    if ($v['uid'] == $value['uid']) {
                        $List[$k]['comname'] = $value['name'];
                        $List[$k]['linkman'] = $value['linkman'];
                        $List[$k]['linktel'] = $value['linktel'];
                    }
                }
                $List[$k]['com_url'] = Url('company', array('c' => 'show', 'id' => $v['uid']));
            }
        }
        return $List;
    }

    /**
     * @desc    新增外出申请
     * @param array $data
     * @return array
     */
    public function addOut($data = array())
    {

        if (!empty($data)) {

            $value  =   array(
                'auid'      =>  intval($data['auid']),
                'uid'       =>  intval($data['uid']),
                'reason'    =>  intval($data['reason']),
                'remark'    =>  $data['remark'],
                'stime'     =>  $data['stime'],
                'etime'     =>  $data['etime'],
                'status'    =>  1,
                'ctime'     =>  time()
            );

            $result     =   $this->insert_into('crm_out', $value);
            $cache      =   $this -> GetCache(array('crm'));
            $lContent   =   "申请外出（ID：".$result."）；原因：".$cache['crmclass_name'][$data['reason']];
            $this->addCrmLog($lContent, 10, $data['uid'], $data['auid']);

            if ($result){

                $return =   array('errcode' => 9, 'msg' => '外出申请（ID：'.$result.'）添加成功!');
            }else{

                $return =   array('errcode' => 8, 'msg' => '外出申请修改失败!');
            }
            return $return;
        }
    }

    /**
     * @desc    更新外出记录
     * @param array $data
     * @param array $where
     * @param array $extraData
     * @return bool
     */
    function upOut($data = array(), $where = array(), $extraData = array())
    {

        if (!empty($extraData)) {

            $value  =   array(
                'uid'       =>  intval($data['uid']),
                'reason'    =>  intval($data['reason']),
                'remark'    =>  $data['remark'],
                'stime'     =>  $data['stime'],
                'etime'     =>  $data['etime'],
                'status'    =>  1,
                'ctime'     =>  time()
            );
            $result     =   $this->update_once('crm_out', $value, $where);
            $cache      =   $this -> GetCache(array('crm'));
            $lContent   =   "修改外出申请（ID：".$result."）；原因：".$cache['crmclass_name'][$data['reason']];
            $this->addCrmLog($lContent, 10, $data['uid'], $data['auid']);

            if ($result){

                $return =   array('errcode' => 9, 'msg' => '外出申请（ID：'.$where['id'].'）修改成功!');
            }else{

                $return =   array('errcode' => 8, 'msg' => '外出申请修改失败!');
            }
        } else {

            $return =   $this->update_once('crm_out', $data, $where);
        }
        return $return;
    }
    /**
     * @desc   删除外出申请
     */
    public function delOut($delId, $data = array()){

        if(empty($delId)){

            $return =   array(
                'errcode'   =>  8,
                'msg'       =>  '请选择要删除的数据！',
            );
        }else{

            if(is_array($delId)){

                $delId  =   pylode(',',$delId);
                $return['layertype']    =   1;
            }else{

                $return['layertype']    =   0;
            }

            $nid    =   $this -> delete_all('crm_out',array('id' => array('in',$delId)),'');

            if($nid && !empty($data['auid'])){
                $lContent   =   "取消外出申请（ID：".$delId."）";
                $this -> addCrmLog($lContent, 10, '', $data['auid']);
            }

            $return['msg']      =   '外出申请';
            $return['errcode']  =   $nid ? '9' :'8';
            $return['msg']      .=  $nid ? '取消成功！' : '取消失败！';
        }

        return  $return;
    }

    /**
     * @desc CRM客户记录
     * @param array $data
     * @return bool
     */
    function addCrmComLog($data = array())
    {
        if (!empty($data)){
            $value  =   array(

                'auid'      =>  $data['auid'],
                'uid'       =>  $data['uid'],
                'type'      =>  $data['type'],
                'content'   =>  $data['content'],
                'remark'    =>  $data['remark'],
                'ctime'     =>  time()
            );
            return $this->insert_into('crm_comlog', $value);
        }
    }

    /**
     * @desc 获取客户操作记录条数
     * @param array $where
     * @return array|bool|false|string|void
     */
    function getComLogNum($where = array()) {
        return $this->select_num('crm_comlog', $where, 'id');
    }

    /**
     * @desc 获取客户操作记录
     * @param array $where
     * @return array|bool|false|string|void
     */
    function getComLogInfo($where = array(), $data = array()) {
        $field = $data['field'] ? $data['field'] : '*';
        return $this->select_once('crm_comlog', $where, $field);
    }

    /**
     * @desc 获取客户操作记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    function getComLogList($where = array(), $data = array())
    {

        $field  =   $data['field'] ? $data['field'] : '*';
        $logList=   array();
        if (!empty($where)) {

            $logList=   $this->select_all('crm_comlog', $where, $field);
            $comids =   $auids  =   array();
            foreach ($logList as $value) {

                $comids[]   =   $value['uid'];
                $auids[]    =   $value['auid'];
            }

            $comList    =   $this->select_all('company', array('uid' => array('in', pylode(',', $comids))), '`uid`,`name`, `linkman`,`linktel`');
            $ausers     =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $auids))), '`uid`,`name`');
            foreach ($logList as $k => $v) {

                $logList[$k]['ctime_n'] =   date('Y-m-d H:i:s', $v['ctime']);

                //  TODO: 数据归纳
                if ($v['type'] == 1) {

                    $logList[$k]['type_n'] = '【转交客户】';
                } else if ($v['type'] == 2) {

                    $logList[$k]['type_n'] = '【放弃】';
                } else if ($v['type'] == 3) {

                    $logList[$k]['type_n'] = '【开通订单】';
                } else if ($v['type'] == 4) {

                    $logList[$k]['type_n'] = '【跟进/访问】';
                } else if ($v['type'] == 5) {

                    $logList[$k]['type_n'] = '【修改企业资料】';
                } else if ($v['type'] == 6) {

                    $logList[$k]['type_n'] = '【修改联系方式】';
                } else if ($v['type'] == 7) {

                    $logList[$k]['type_n'] = '【' . $v['content'] . '】';
                } else if ($v['type'] == 8) {

                    $logList[$k]['type_n'] = '【最新反馈备注】';
                } else if ($v['type'] == 9) {

                    $logList[$k]['type_n'] = $v['content'];
                }

                $content    =   str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $v['remark']);
                $content    =   htmlspecialchars_decode($content);
                preg_match_all('<img(.*?)src=\"(.+?)\".*?>',$content,$res);

                $imgArr     =   array();
                if(!empty($res[2])){
                    foreach($res[2] as $rv){
                        if(strpos($rv,'https:')===false && strpos($rv,'http:')===false){
                            $imgUrl     =   checkpic($rv);
                        }else{
                            $imgUrl     =   $rv;
                        }
                        $imgArr[]       =   $imgUrl;
                    }
                }
                $logList[$k]['remark']  =   strip_tags($content);
                if (!empty($imgList)){
                    foreach ($imgList as $ik => $iv){
                        if ($v['id'] == $iv['fid']){
                            $imgArr[]   =   checkpic($iv['picurl']);
                        }
                    }
                }
                $logList[$k]['imgArr']     =   $imgArr;

                foreach ($comList as $cv) {
                    if ($v['uid'] == $cv['uid']) {

                        $logList[$k]['name']    =   $cv['name'];
                        $logList[$k]['linkman'] =   $cv['linkman'];
                        $logList[$k]['moblie']  =   $cv['linktel'];
                    }
                }
                foreach ($ausers as $av) {
                    if ($v['auid'] == $av['uid']) {

                        $logList[$k]['aname']   =   $av['name'];
                    }
                }
            }
        }
        return $logList;
    }

    /**
     * @desc 客户管理 -> 联系跟进记录添加
     * @param array $data |uid 客户（企业）ID; auid 操作人UID;
     * @return array
     */
    function addFollow($data = array())
    {

        if (!empty($data)) {

            if (empty($data['uid'])) {
                return array('errcode' => 8, 'msg' => '参数错误，请重试！');
            }
            if (empty($data['type'])) {
                return array('errcode' => 8, 'msg' => '请选择跟进方式！');
            }
            if (empty($data['remark'])) {
                return array('errcode' => 8, 'msg' => '请填写跟进描述！');
            }
            if (empty($data['link_man']) || empty($data['link_tel'])) {
                return array('errcode' => 8, 'msg' => '请选择联系人信息！');
            }
            if ($data['follow'] == 1 && !empty($data['next_time']) && strtotime($data['next_time']) < time()) {
                return array('errcode' => 8, 'msg' => '下次跟进时间应该在当前时间之后！');
            }

            $auid       =   intval($data['auid']);
            $uid        =   intval($data['uid']);

            $comInfo    =   $this->select_once('company', array('uid' => $data['uid']), '`uid`, `crm_uid`,`linkman`,`linktel`,`linkphone`,`crm_status`,`crm_type`');

            if (empty($data['ftime'])) {
                $data['ftime']  =   date('Y-m-d H:i:s');
            }
            if ($auid != $comInfo['crm_uid']) {

                $crmInfo=   $this->select_once('admin_user', array('uid' => $comInfo['crm_uid']), 'org');
                $auids  =   array();    //  有编辑跟进权限的UID
                $lastOrg=   $this->select_once('crmorg', array('id' => $crmInfo['org']), 'id, level, fid');
                if ($lastOrg['level'] == 3) {

                    $threeCrmArr    =   $this->select_all('admin_user', array('org' => $lastOrg['id'], 'power' => 1), 'uid');
                    $twoOrg         =   $this->select_once('crmorg', array('id' => $lastOrg['fid']), 'id, fid');
                    $twoCrmArr      =   $this->select_all('admin_user', array('org' => $twoOrg['id'], 'spower' => 1), 'uid');
                    $oneCrmArr      =   $this->select_all('admin_user', array('org' => 1, 'spower' => 1), 'uid');
                } else if ($lastOrg['level'] == 2) {

                    $twoCrmArr      =   $this->select_all('admin_user', array('org' => $lastOrg['id'], 'power' => 1), 'uid');
                    $oneCrmArr      =   $this->select_all('admin_user', array('org' => 1, 'spower' => 1), 'uid');
                } else if ($lastOrg['level'] == 1) {

                    $oneCrmArr      =   $this->select_all('admin_user', array('org' => 1, 'spower' => 1), 'uid');
                }

                if (isset($threeCrmArr)) {
                    foreach ($threeCrmArr as $three) {
                        $auids[] = $three['uid'];
                    }
                }
                if (isset($twoCrmArr)) {
                    foreach ($twoCrmArr as $two) {
                        $auids[] = $two['uid'];
                    }
                }
                if (isset($oneCrmArr)) {
                    foreach ($oneCrmArr as $one) {
                        $auids[] = $one['uid'];
                    }
                }
                if (!in_array($auid, $auids)) {
                    return array('errcode' => 8, 'msg' => '非自己客户，暂无权限添加跟进！');
                }
            }

            $value      =   array(
                'uid'       =>  $uid,
                'auid'      =>  $auid,  //  最新逻辑，谁录入，就是谁的记录
                'type'      =>  intval($data['type']),
                'content'   =>  $data['remark'],
                'ftime'     =>  strtotime($data['ftime']),
                'atime'     =>  time()
            );

            if (isset($data['link_man'])) {

                $value['link_man']  =   $data['link_man'];
                $value['link_tel']  =   $data['link_tel'];
            } else {

                $value['link_man']  =   $comInfo['linkman'];
                $value['link_tel']  =   !empty($comInfo['linktel']) ? $comInfo['linktel'] : $comInfo['linkphone'];
            }

            if (!empty($data['crm_type']) && $comInfo['crm_type'] != $data['crm_type']) { // 没传或者不变更，不重新记录
                $value['crm_type']  =   $data['crm_type'];
            }
            if (!empty($data['crm_status']) && $comInfo['crm_status'] != $data['crm_status']) { // 没传或者不变更，不重新记录
                $value['crm_status']    =   $data['crm_status'];
            }

            if (isset($data['call_log_id']) && !empty($data['call_log_id'])) {

                $value['call_log_id']   =   intval($data['call_log_id']);
            } else {

                $callLog    =   $this->select_once('staff_call_log', array('cuid' => $uid, 'uid' => $auid, 'usertype' => 2, 'ctime' => array('>', strtotime('today')), 'orderby' => 'ctime,desc'), '`id`');
                if (!empty($callLog)) {

                    $log    =   $this->select_once('crmnew_concern', array('call_log_id' => $callLog['id']), '`id`');
                    if (empty($log)) {

                        $value['call_log_id'] = $callLog['id'];
                    }
                }
            }

            if (isset($data['id']) && !empty($data['id'])) {

                $cInfo      =   $this->select_once('crmnew_concern', array('id' => $data['id']), '`taskid`');

                $result     =   $this->update_once('crmnew_concern', $value, array('id' => $data['id']));
                $follow_id  =   $data['id'];
            } else {

                $result     =   $this->insert_into('crmnew_concern', $value);
                $follow_id  =   $result;
            }

            if ($result) {

                if ($data['follow'] == 1 && !empty($data['next_time'])) {

                    if (isset($data['id']) && !empty($data['id'])){

                        $taskValue  =   array(
                            'auid'      =>  $auid,
                            'uid'       =>  $comInfo['crm_uid'],
                            'comid'     =>  $uid,
                            'type'      =>  22, //  跟进任务类型字段  值22 跟进任务
                            'content'   =>  strip_tags($data['remark']),
                            'stime'     =>  strtotime($data['next_time']),
                            'ctime'     =>  time(),
                            'cid'       =>  $data['id']
                        );
                        if (!empty($cInfo['taskid'])){

                            $taskId =   $this->update_once('crm_work_plan', $taskValue, array('id' => $cInfo['taskid']));
                        }else{

                            $taskId =   $this->insert_into('crm_work_plan', $taskValue);
                            $this->update_once('crmnew_concern', array('taskid' => $taskId), array('id' => $data['id']));
                        }
                    }else{

                        $taskValue  =   array(
                            'auid'      =>  $auid,
                            'uid'       =>  $comInfo['crm_uid'],
                            'comid'     =>  $uid,
                            'type'      =>  22, //  跟进任务类型字段  值22 跟进任务
                            'content'   =>  strip_tags($data['remark']),
                            'stime'     =>  strtotime($data['next_time']),
                            'ctime'     =>  time(),
                            'cid'       =>  $result
                        );
                        $taskId =   $this->insert_into('crm_work_plan', $taskValue);
                        $this->update_once('crmnew_concern', array('taskid' => $taskId), array('id' => $result));
                    }
                }

                //  更新跟进内容，取消跟进任务
                if (!empty($cInfo['taskid']) && $data['follow'] == 2){

                    $this->delete_all('crm_work_plan', array('id' => $cInfo['taskid'], 'status' => 1));
                    $this->update_once('crmnew_concern', array('taskid' => 0), array('id' => $data['id']));
                }

                //  手机站后台跟进上传图片
                if (isset($data['uimage'])){
                    $html     =   '';
                    foreach ($data['uimage'] as $k => $v) {
                        $upArr  =   array('dir' => 'crm', 'base' => $v,);
                        $result =   $this->upload($upArr);
                        if (!empty($result['picurl'])) {
                            $html   .=  '<img src="'.checkpic($result['picurl']).'">';
                        }
                    }
                    $this->update_once('crmnew_concern', array('content' => $data['remark'].$html), array('id' => $follow_id));
                }
                $content    =   '跟进客户，新增跟进记录（ID：'.$follow_id.'）；';
                if ($data['follow'] == 1 && !empty($data['next_time'])) {
                    $content.=  '预约下次跟进，任务时间：'.$data['next_time'];
                }

                $remark     =   '';
                if ($auid != $comInfo['crm_uid']) {
                    $remark .=  '协助跟进；';
                }
                $remark     .=   "跟进内容：".$data['remark'];
                $logData    =   array(

                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  4,
                    'content'   =>  $content,
                    'remark'    =>  $remark
                );
                $this->addCrmComLog($logData);

                //  业务员操作记录
                $lContent   =   '跟进客户，添加跟进记录（ID：'.$follow_id.'）';
                if ($auid != $comInfo['crm_uid']) {
                    $lContent   .=  '；协助添加';
                }
                $this->addCrmLog($lContent, 3, $uid, $auid);

                $companyData = array('isfollow' => 1, 'f_time' => time());
                !empty($value['crm_type']) && $companyData['crm_type'] = $value['crm_type'];
                !empty($value['crm_status']) && $companyData['crm_status'] = $value['crm_status'];
                $this->update_once('company', $companyData, array('uid' => $uid));

                $todayEnd   =   strtotime(date('Y-m-d 23:59:59'));
                $taskWhere  =   array(
                    'type'      =>  22,
                    'status'    =>  1,
                    'comid'     =>  $uid,
                    'PHPYUNBTWSTART_A'  =>  '',
                    'stime' =>  array(
                        0   =>  array('<', $todayEnd, ''),
                        1   =>  array('>', 0, '')
                    ),
                    'PHPYUNBTWEND_A'    =>  ''
                );
                $this->update_once('crm_work_plan', array('status' => 2, 'reason' => '添加跟进，同步调整任务状态', 'etime' => time()), $taskWhere);

                $return['errcode']  =   9;
                $return['msg']      =   $data['id'] > 0 ? '跟进记录（ID:'.$follow_id.'）更新成功！' : '跟进记录（ID:'.$follow_id.'）添加成功';
            }else{

                $return['errcode']  =   8;
                $return['msg']      =   $data['id'] > 0 ? '跟进记录更新失败！' : '跟进记录添加失败';
            }
            return $return;
        }
    }

    /**
     * @param null[] $data
     * @return array|mixed|string
     */
    private function upload($data = array('file' => null, 'dir' => null, 'type' => null, 'base' => null, 'preview' => null))
    {

        include_once('upload.model.php');
        $UploadM    =   new upload_model($this->db, $this->def);
        $upArr      =   array(
            'file'      =>  $data['file'],
            'dir'       =>  $data['dir'],
            'type'      =>  $data['type'],
            'base'      =>  $data['base'],
            'preview'   =>  $data['preview']
        );
        $return =   $UploadM->newUpload($upArr);
        return $return;
    }


    /**
     * @desc 公众号通知业务员 crm
     * @desc $this -> MODEL('admin')->sendCrmWxMsg();
     * @param string $auid
     * @param string[] $data
     */
    public function sendCrmWxMsg($auid='',$data=array('type'=>'','first'=>'','title'=>'','customer'=>'','thing'=>''))
    {
        if ($auid && $data['first']) {
            if($auid!='' && $auid>0){
                $awhere   =   array('uid' => $auid);
            }else{
                //$awhere   =   array('m_id' => 1);
                if($data['type']){
                    $wmtype   =   $this->select_once('admin_wmtype', array('type' =>$data['type']), '`uid`');
                }
                if($wmtype['uid']){
                    $awhere   =   array('uid' => array('in',$wmtype['uid']));
                }

            }

            if(!empty($awhere)){
                $user   =   $this->select_all('admin_user', $awhere, '`uid`,`name`,`moblie`,`r_status`,`wxid`,`qy_userid`');
            }


            if (! empty($user)) {

                require_once ('weixin.model.php');
                $weixinM = new weixin_model($this->db, $this->def);

                if($this->config["sy_admin_wmtype"]==1){

                    foreach ($user as $v) {

                        if ($v['wxid']) {

                            $weixinM->sendWxAdmin(array(
                                'uid'   =>  $v['uid'],
                                'type'  =>  $data['type'],
                                'first' =>  $data['first'],
                                'wxid'  =>  $v['wxid'],
                                'title' =>  $data['title'],
                                'customer' =>  $data['customer'],
                                'thing' =>  $data['thing'],
                            ));
                        }
                    }
                }else if($this->config["sy_admin_wmtype"]==2){

                    $uids = $userids = array();

                    foreach ($user as $v) {

                        if ($v['qy_userid']) {
                            $uids[]    = $v['uid'];
                            $userids[] = $v['qy_userid'];

                        }
                    }

                    $weixinM->sendWxQyAdmin(array(
                        'type'  =>  $data['type'],
                        'first' =>  $data['first'],
                        'userids'  =>  $userids,
                        'uids'  =>  $uids,
                        'title' =>  $data['title'],
                    ));
                }


            }
        }
    }

    /**
     * @desc 客户数量（总数、待审核、已锁定、未通过、暂停招聘）
     * @param array $data
     * @return false|string
     */
    public function getCustomerNum($data = array())
    {

        $arr = $where = array();

        if ($data['type'] == 1) {

            $where['crm_uid']   =   $data['crm_uid'];
        } else if ($data['type'] == 2) {

            $where['crm_uid']   =   array('in', $data['crm_uid']);
        } else if ($data['type'] == 3) {

            $where  =   array(
                'PHPYUNBTWSTART_A' => '',
                'crm_uid' => array(
                    0 => array('=', 0, ''),
                    1 => array('isnull', '', 'OR')
                ),
                'PHPYUNBTWEND_A' => ''
            );
        } else if ($data['type'] == 4) {

            if (!empty($data['crm_uid'])) {

                $where['crm_uid'] = $data['crm_uid'];
            }
        }
        $companyAllNum  =   $this->select_num('company', $where);
        if ($companyAllNum > 0) {
            $arr['companyAllNum'] = $companyAllNum;
        }

        $where['r_status'] = 0;
        $companyStatusNum1 = $this->select_num('company', $where);
        if ($companyStatusNum1 > 0) {
            $arr['companyStatusNum1'] = $companyStatusNum1;
        }

        $where['r_status'] = 3;
        $companyStatusNum2 = $this->select_num('company', $where);
        if ($companyStatusNum2 > 0) {
            $arr['companyStatusNum2'] = $companyStatusNum2;
        }

        $where['r_status'] = 2;
        $companyStatusNum3 = $this->select_num('company', $where);
        if ($companyStatusNum3 > 0) {
            $arr['companyStatusNum3'] = $companyStatusNum3;
        }

        $where['r_status'] = 4;
        $companyStatusNum4 = $this->select_num('company', $where);
        if ($companyStatusNum4 > 0) {
            $arr['companyStatusNum4'] = $companyStatusNum4;
        }
        return $arr;
    }

    /**
     * @desc 认领客户
     * @param array $uids
     * @param null $auid
     * @return string[]
     */
    function claimCom($uids = array(), $auid = null){

        if(empty($uids) || empty($auid)){

            $return =   array('errcode' => '8', 'msg' => '领取失败，参数错误');
        }else{
            $comNum =   count($uids);
            $aInfo  =   $this->select_once('admin_user', array('uid' => $auid), '`num`,`name`,`is_crm`');

            if ($this->config['sy_crm_receive_grant'] == 1 && $aInfo['is_crm'] != 1) { // CRM设置-领取客户权限-限制业务员
                return array('errcode' => '8', 'msg' => '领取失败，账户未开通业务员');
            }

            if ((int)$aInfo['num'] > 0) {

                $allKhNum   =   $this->select_num('company', array('crm_uid' => $auid));

                if ($this->config['sy_crm_deal_open'] == 1 && !empty($this->config['crm_deal_rating'])) {

                    $vipKhNum   =   $this->select_num('company', array('crm_uid' => $auid, 'rating' => array('in', $this->config['crm_deal_rating']), 'vipetime' => array('>', strtotime('today'))));

                    $khNum  =   $allKhNum - $vipKhNum;
                }else{

                    $khNum  =   $allKhNum;
                }
                if ($khNum >= (int)$aInfo['num']) {

                    return array('errcode' => '8', 'msg' => '客户数量已达上限，暂时无法领取更多客户');
                } else if (($khNum+$comNum) > intval($aInfo['num'])){

                    $syKhNum    =   intval($aInfo['num']) - $khNum;
                    return array('errcode' => '8', 'msg' => '领取失败！当前最多还可以领取'.$syKhNum.'个客户~');
                }
            }

            if ($this->config['sy_crm_hour'] > 0) {  //  主动认领客户，每小时限制数量

                $hourNum    =   $this->select_num('company', array('crm_uid' => $auid, 'crm_source' => 1, 'crm_time' => array('>=', strtotime('-1 hours'))));

                if ($hourNum >= $this->config['sy_crm_hour']) {

                    return array('errcode' => '8', 'msg' => '每小时最多领取'.$this->config['sy_crm_hour'].'位客户，暂时无法领取更多客户');
                } else {

                    $hourNum    =   $this->select_num('crm_comlog', array('auid' => $auid, 'type' => 12, 'ctime' => array('>=', strtotime('-1 hours'))));

                    if ($hourNum >= $this->config['sy_crm_hour']) {

                        return array('errcode' => '8', 'msg' => '每小时最多领取'.$this->config['sy_crm_hour'].'位客户，暂时无法领取更多客户');
                    }
                }
            }

            if ($comNum == 1){

                $comInfo    =   $this->select_once('company', array('uid' => $uids[0]), '`crm_uid`,`crm_time`,`rating`,`release_time`,`pcrmuid`');
                if ((int)$comInfo['crm_uid'] > 0 && (int)$comInfo['crm_time'] > 0){

                    return array('errcode' => '8', 'msg' => '领取失败！客户已分配业务员');
                }
                if ($comInfo['pcrmuid'] == $auid && (int)$comInfo['release_time'] > 0){

                    $claim  =   $this->select_all('crmset', array('claim_day' => array('>', '0')));

                    if (!empty($claim)) {

                        $comInfo['rating']  =   $comInfo['rating'] == 0 ? 999 : $comInfo['rating'];

                        foreach ($claim as $sk => $sv) {
                            if (in_array($comInfo['rating'], explode(',', $sv['rating'])) && $comInfo['release_time'] > strtotime("-" . $sv['claim_day'] . " day")) {

                                return array('errcode' => '8', 'msg' => '领取失败！刚释放资源客户，近期无法领取');
                            }
                        }
                    }
                }
                $time   =   time();
                $result =   $this->update_once('company', array('crm_uid' => $auid, 'crm_time' => $time, 'crm_source' => 1), array('uid' => $uids[0]));

                if ($result) {

                    $logData            =   array();
                    $logData['auid']    =   $auid;
                    $logData['uid']     =   $uids[0];
                    $logData['type']    =   12;
                    $logData['content'] =   '领取公海客户（UID：'.$uids[0].'）';
                    $this->addCrmComLog($logData);

                    $lContent           =   "领取客户（UID：".$uids[0]."）";
                    $this->addCrmLog($lContent, 12, $uids[0], $auid);
                }

                $return     =   array('errcode' => $result ? '9' : '8', 'msg' => $result ? '客户领取成功！' : '客户领取失败！', 'crm_name' => $aInfo['name'], 'crm_uid' => $auid, 'crm_time_n' => date('Y-m-d H:i:s', $time));
            }else{

                $newUidArr  =   $uids;
                $comS       =   $this->select_all('company', array('crm_uid' => array('>', 0), 'crm_time' => array('>', 0), 'uid' => array('in', pylode(',', $uids))), 'uid');
                if (!empty($comS)) {

                    $uidArrOne  =   array();
                    foreach ($comS as $ck1 => $cv1) {

                        $uidArrOne[]    =   $cv1['uid'];
                    }

                    $newUidArr  =   array_diff($uids, $uidArrOne);
                }

                // 释放资源，限定期限不能领取；
                $comAll =   $this->select_all('company', array('pcrmuid' => $auid, 'release_time' => array('>', '0') ,'uid' => array('in', pylode(',', $newUidArr))), '`uid`,`rating`,`release_time`');

                if (!empty($comAll)) {

                    $claim  =   $this->select_all('crmset', array('claim_day' => array('>', '0')));

                    if (!empty($claim)) {

                        $uidArrTwo  =   array();
                        foreach ($claim as $sk => $sv) {
                            foreach ($comAll as $ck2 => $cv2) {

                                $cv2['rating']  =   $cv2['rating'] == '0' ? '999' : $cv2['rating'];

                                if (in_array($cv2['rating'], explode(',', $sv['rating'])) && $cv2['release_time'] > strtotime("-" . $sv['claim_day'] . " day")) {

                                    $uidArrTwo[] = $cv2['uid'];
                                }
                            }
                        }

                        if (!empty($uidArrTwo)){

                            $newUidArr  =   array_diff($newUidArr, $uidArrTwo);
                        }
                    }
                }

                if(!empty($newUidArr)){

                    $result =   $this->update_once('company', array('crm_uid' => $auid, 'crm_time' => time(), 'crm_source' => 1), array('uid' => array('in', pylode(',', $newUidArr))));

                    if ($result) {

                        $logData    =   array();

                        foreach ($newUidArr as $k => $v){

                            $logData[$k]['auid']    =   $auid;
                            $logData[$k]['uid']     =   $v;
                            $logData[$k]['type']    =   12;
                            $logData[$k]['content'] =   '领取公海客户（UID：'.pylode(',', $newUidArr).'）';
                            $logData[$k]['ctime']   =   time();
                        }

                        $this->DB_insert_multi('crm_comlog', $logData);

                        $lContent   =   "领取客户（UID：".pylode(',', $newUidArr)."）";
                        $this->addCrmLog($lContent, 12, '', $auid);
                    }

                    $fail   =   $success    =   0;
                    if (!empty($uidArrOne)){

                        $fail   =   $fail + count($uidArrOne);
                    }
                    if (!empty($uidArrTwo)){

                        $fail   =   $fail + count($uidArrTwo);
                    }
                    $success    =   count($newUidArr);

                    if ($fail > 0){

                        $msg    =   '领取完成：成功 '.$success.'，失败 '.$fail;
                    }else{

                        $msg    =   '客户领取成功！';
                    }

                    $return     =   array('errcode' => $result ? '9' : '8', 'msg' => $result ? $msg : '客户领取失败！');
                }else{

                    $msg        =   '客户领取失败！';
                    if (!empty($uidArrOne)){
                        $msg    .=  '部分客户已分配业务员！';
                    }
                    if (!empty($uidArrTwo)){
                        $msg    .=  '部分客户刚释放，近期无法领取！';
                    }
                    $return     =   array('errcode' => '8', 'msg' => $msg);
                }
            }
        }

        return $return;
    }

    /**
     *  计划任务
     *
     *  CRM资源释放：1 未跟进客户  2 会员未变动客户
     */
    function releaseCom(){

        $releaseFCom    =   array();
        $releaseRCom    =   array();

        $rComArr        =   array();
        $dComArr        =   array();

        /*
         * 释放周期逻辑：1-非暂停客户 && 2-领取时间超过释放周期 && 3-跟进时间超过释放周期
         */
        $crmReleaseSet  =   $this->select_all('crmset', array('release_day' => array('>', '0')));
        foreach ($crmReleaseSet as $k => $v) {
            if((int)$v['release_day'] > 0){

                $rWhere[$k]['crm_uid']  =   array('>', '0');
                $rWhere[$k]['r_status'] =   array('<>', '4');
                $rWhere[$k]['rating']   =   array('in', str_replace('999', '0', $v['rating']));
                $rWhere[$k]['crm_time'] =   array('<', strtotime('-'.$v["release_day"].' days'));
                $rWhere[$k]['PHPYUNBTWSTART_A']  =   '';
                $rWhere[$k]['f_time'][]         =   array('<', strtotime('-'.$v["release_day"].' days'));
                $rWhere[$k]['f_time'][]         =   array('isnull', '', 'or');
                $rWhere[$k]['PHPYUNBTWEND_A']    =   '';
				$rWhere[$k]['clue'] 	=   1;
                $comArr =   $this->select_all('company', $rWhere[$k], '`uid`,`crm_uid`');
                if (!empty($comArr)) {

                    $rComArr    =   array_merge($rComArr, $comArr);
                }
            }
        }
        if (!empty($rComArr)) {
            foreach ($rComArr as $rk => $rv) {

                $releaseFCom[$rk]['uid']    =   $rv['uid'];
                $releaseFCom[$rk]['crm_uid']=   $rv['crm_uid'];
            }
        }

        /*
         *  成交限定周期
         */
        $crmDealSet     =   $this->select_all('crmset', array('deal_day' => array('>', '0')));
        foreach ($crmDealSet as $k => $v) {

            if ((int)$v['deal_day'] > 0) {

                $dWhere[$k]['crm_uid']  =   array('>', '0');
                $dWhere[$k]['r_status'] =   array('<>', '4');
                $dWhere[$k]['crm_time'] =   array('<', strtotime('-'.$v["deal_day"].' days'), '');
				$dWhere[$k]['clue'] 	=   1;
                if (!strpos($v['rating'], '999')) {    //  存在过期会员

                    $dealArr    =   explode(',', $v['rating']);

                    foreach ($dealArr as $dk => $dv){

                        if ($dv == '999'){

                            $dWhere[$k]['rating']               =   0;
                            $dWhere[$k]['vipetime']             =   array('<', strtotime('-'.$v["deal_day"].' days'), '');
                        }else{

                            $dWhere[$k]['rating']               =   $dv;
                            $dWhere[$k]['vipstime']             =   array('<', strtotime('-'.$v["deal_day"].' days'), '');
                        }
                        $comArr =   $this->select_all('company', $dWhere[$k], '`uid`,`crm_uid`');
                        if (!empty($comArr)) {
                            $dComArr    =   array_merge($dComArr, $comArr);
                        }
                    }
                } else {

                    $dWhere[$k]['rating']       =   array('in', $v['rating']);
                    $dWhere[$k]['vipstime']     =   array('<', strtotime('-'.$v["deal_day"].' days'), '');
                    $comArr                     =   $this->select_all('company', $dWhere[$k], '`uid`,`crm_uid`');
                    if (!empty($comArr)) {
                        $dComArr    =   array_merge($dComArr, $comArr);
                    }
                }
            }
        }

        if (!empty($dComArr)) {
            foreach ($dComArr as $dk => $dv) {

                $releaseRCom[$dk]['uid']    =   $dv['uid'];
                $releaseRCom[$dk]['crm_uid']=   $dv['crm_uid'];
            }
        }

        $releaseCom =   array_merge($releaseFCom, $releaseRCom);

        if (!empty($releaseCom)){

            $releaseComIds  =   array();

            $crmBhData      =   $this->select_all('crmset', array('bh_day' => array('>', '0')));
            if (!empty($crmBhData)){

                foreach ($releaseCom as $rk => $rv){
                    if (!in_array($rv['uid'], $releaseComIds)){

                        $releaseComIds[]    =   $rv['uid'];
                    }
                }

                $noReleaseComIds    =   array();
                foreach ($crmBhData as $cbk => $cbv) {

                    $bWhere[$cbk]['oldrating']  =   array('in', $cbv['rating']);
                    $bWhere[$cbk]['vip_etime']  =   array('>', strtotime('- '.$cbv['bh_day'].' day'));
                    $bWhere[$cbk]['uid']        =   array('in', pylode(',', $releaseComIds));
                    $newCom                     =   $this->select_all('company_statis', $bWhere[$cbk], '`uid`');

                    if (!empty($newCom)){
                        foreach ($newCom as $newK => $newV){

                            $noReleaseComIds[]  =   $newV['uid'];
                        }
                    }
                }
                if (!empty($noReleaseComIds)){

                    foreach ($releaseCom as $rk => $rv){

                        if (in_array($rv['uid'], $noReleaseComIds)){
                            unset($releaseCom[$rk]);
                        }
                    }
                }
            }
            $releaseCom     =   array_values($releaseCom);

            $crmUid     =   array();
            $crmList    =   $this->select_all('admin_user', array('crm_kh' => 1), '`uid`');
            if (!empty($crmList)) {
                foreach ($crmList as $ck => $cv) {
                    $crmUid[]   =   $cv['uid'];
                }
                foreach ($releaseCom as $k1 => $v1) {
                    if (in_array($v1['crm_uid'], $crmUid)) {

                        unset($releaseCom[$k1]);
                    }
                }
            }

            $newReleaseComIds   =   array();
            foreach ($releaseCom as $Rk => $Rv){

                if (!in_array($Rv['uid'], $newReleaseComIds)){

                    $this->update_once('company', array('crm_uid' => '0', 'crm_time' => '0', 'pcrmuid' => $Rv['crm_uid'], 'release_time' => time()), array('uid' => $Rv['uid']));
                    $this->insert_into('crm_release_log', array('auid' => $Rv['crm_uid'], 'uid' => $Rv['uid'], 'ctime' => time(), 'content' => '释放计划任务'));
                    $newReleaseComIds[]    =   $Rv['uid'];
                }
            }
        }
    }

    /**
     *  添加CRM操作日志
     *
     * @param $content 内容
     * @param string $type
     * @param string $uid 操作对象，客户uid
     * @param string $auid 业务员UID
     */
    function addCrmLog($content, $type, $uid, $auid)
    {

        if ($auid && $type && $content) {

            $data   =   array(

                'uid'       =>  $uid,
                'content'   =>  $content,
                'type'      =>  $type,
                'auid'      =>  $auid,
                'ctime'     =>  time()
            );
            $this->insert_into('crm_log', $data);
        }
    }

    /**
     *  查询CRM操作日志
     * @param array $where  查询条件
     * @param array $data
     * @return array|bool|false|string|void
     */
    function getCrmLogList($where = array(), $data = array()){

        $field      =   $data['field'] ? $data['field'] : '*';

        $logList    =   array();

        if (!empty($where)) {

            $typeArr    =   array(
                '1' => '录入客户',
                '2' => '客户资料',
                '3' => '客户跟进',
                '4' => '客户订单',
                '5' => '工作任务',
                '6' => '工作日志',
                '7' => '转交客户',
                '8' => '放弃客户',
                '9' => '等级标签',
                '10' => '外出申请',
                '11' => '联系方式',
                '12' => '领取客户',
                '13' => '推文任务',
                '14' => '发送短信',
                '15' => '简历推送',
                '16' => '岗位推广',
                '17' => '客户备注',
                '18' => '暂停招聘'
            );

            $logList    =   $this->select_all('crm_log', $where, $field);

            $auids      =   array();

            foreach ($logList as $value){

                $auids[]    =   $value['auid'];
            }

            $ausers     =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $auids))),'`uid`,`name`');

            foreach ($logList as $k => $v){

                $logList[$k]['time_n']  =   date('Y-m-d H:i', $v['ctime']);
                $logList[$k]['type_n']  =   $typeArr[$v['type']];
                foreach ($ausers as $av){

                    if ($v['auid'] == $av['uid']) {
                        $logList[$k]['aname']   =   $av['name'];
                    }
                }
            }
        }

        return $logList;
    }

    /**
     *  删除CRM操作日志
     * @param $delId
     * @param array $data
     * @return array
     */
    function delCrmLog($delId, $data=array()){

        if (empty($delId)) {

            $return     =   array( 'errcode' => 8, 'msg' => '请选择要删除的数据！');
        } else {

            if (is_array($delId)) {

                $delId  =   pylode(',', $delId);

                $return['layertype']    =   1;
            } else {

                $return['layertype']    =   0;
            }

            $result             =   $this -> delete_all('crm_log', array('id' => array('in', $delId)), '');

            $return['msg']      =   'CRM 操作记录';
            $return['errcode']  =   $result ? '9' : '8';
            $return['msg']      =   $result ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
        }

        return $return;
    }

    /**
     * @desc 查询资源配置数据
     * @param $where
     * @param $data
     * @return array|bool|false|string|void
     */
    function getCrmsetList($where = array(), $data = array())
    {

        $field  =   empty($data['field']) ? '*' : $data['field'];
        $List   =   $this->select_all('crmset', $where, $field);
        if (!empty($List)) {

            require_once 'rating.model.php';
            $ratingM    =   new rating_model($this->db, $this->def);
            $ratings    =   $ratingM->getList(array('category' => '1'), '`id`, `name`');
            $ratingArr  =   array();
            foreach ($ratings as $v) {

                $ratingArr[$v['id']]    =   $v['name'];
            }

            $ratingArr['999']   =   '过期会员';

            foreach ($List as $key => $value) {

                $ratingIDArr    =   @explode(',', $value['rating']);
                $ratingName     =   array();
                $ratingList     =   array();
                foreach ($ratingIDArr as $rv) {
                    if ($ratingArr[$rv]) {

                        $ratingName[] = $ratingArr[$rv];
                        $ratingList[] = array('id' => $rv, 'name' => $ratingArr[$rv]);
                    }
                }
                $List[$key]['ratingList']   =   $ratingList;
                $List[$key]['ratingIdArr']  =   $ratingIDArr;
                $List[$key]['rating_name']  =   @implode('，', $ratingName);
            }

        }

        return $List;
    }

    /**
     *
     * @desc 查询资源配置数据
     * @param $where
     * @param $data
     * @return array|bool|false|string|void
     */
    function getCrmsetInfo($where = array(), $data = array())
     {

        $data['field']  =   empty($data['field']) ? '*' : $data['field'];

        return $this -> select_once('crmset', $where, $data['field']);
    }

    /**
     * @desc 添加资源配置数据
     * @param $data
     * @return string[]
     */
    function addCrmset($data = array()){

        if (!empty($data)){

            $id     =   $data['id'];
            $value  =   $data['value'];

            if (!empty($id)){

                $return['id']   =   $this->update_once('crmset', $value, array('id' => $id));
            }else{

                $return['id']   =   $this -> insert_into('crmset', $value);
            }

            $return['errcode']  =   $return['id'] ? '9' : '8';
            $return['msg']      =   $return['id'] ? '资源配置成功！' : '资源配置失败！';
        }else{

            $return =   array('errcode' => '8', 'msg' => '参数错误！');
        }

        return $return;
    }

    /**
     * @desc 删除CRM资源池配置数据
     * @param $delId
     * @return string[]
     */
    function delCrmset($delId = null)
    {

        if ($delId == null) {

            $return =   array(

                'errcode'   =>  '8',
                'msg'       =>  '参数错误'
            );
        } else {

            $result =   $this->delete_all('crmset', array('id' => $delId), '');

            $return['errcode']  =   $result ? '9' : '8';
            $return['msg']      =   $result ? '配置数据删除成功！' : '配置数据删除失败！';
        }

        return $return;
    }

    /*function addCrmOrg($data = array()){

        if (!empty($data)){

            $vData  =   array(

                'name'  =>  $data['name'],
                'level' =>  $data['level'],
                'fid'   =>  $data['fid'] ? $data['fid'] : '0',
                'sort'  =>  $data['sort']
            );

            $return['id']       =   $this->insert_into('crmorg', $vData);
            $return['errcode']  =   $return['id'] ? '9' : '8';
            $return['msg']      =   $return['id'] ? '添加成功！' : '添加失败！';
        }else{

            $return['errcode']  =   '8';
            $return['msg']      =   '参数错误！';
        }

        return $return;
    }*/

    function upCrmOrg($id, $data = array()){

        if (!empty($id) && !empty($data)){

            $result =   $this->update_once('crmorg', $data, array('id' => $id));
            $return['errcode']  =   $result ? '9' : '8';
            $return['msg']      =   $result ? '更新成功！' : '更新失败！';
        }else{

            $return['errcode']  =   '8';
            $return['msg']      =   '参数错误！';
        }

        return $return;
    }

    function getOrgInfo($where = array(), $data = array()){

        $select =   $data['field'] ? $data['field'] : '*';

        if (!empty($where)){

            $orgInfo    =   $this->select_once('crmorg', $where, $select);

            if ($orgInfo['level'] == '3'){

                $fOrg   =   $this->select_once('crmorg', array('id' => $orgInfo['fid']));   // 二级

                $ffOrg  =   $this->select_once('crmorg', array('id' => $fOrg['fid']));      //  一级

                $orgInfo['fname']   =   $fOrg['name'];
                $orgInfo['ffname']  =   $ffOrg['name'];
                $orgInfo['ffid']    =   $ffOrg['id'];

            }elseif ($orgInfo['level'] == '2'){

                $fOrg   =   $this->select_once('crmorg', array('id' => $orgInfo['fid']));   //  一级
                $orgInfo['fname']   =   $fOrg['name'];
            }

            if ((int)$orgInfo['level']<3){

                $orgSList       =   $this->select_all('crmorg', array('fid' => $orgInfo['id']), $select);
                foreach ($orgSList as $ok => $ov) {
                    $orgSList[$ok]['num']   =   $this->select_num('admin_user', array('org' => $ov['id']));
                }
                $orgInfo['list']=   $orgSList;
            }

            // 部门成员
            $musers             =   $this -> select_all('admin_user', array('org' => $orgInfo['id']));
            foreach ($musers as $mv) {
                $muserids[]     =   $mv['uid'];
            }
            $aWhere             =   array(
                'is_crm'            =>  '1',
                'PHPYUNBTWSTART_A'  =>  '',
                'org'               =>  array(
                    '0' =>  array('=', $orgInfo['id'], 'OR'),
                    '1' =>  array('isnull', '', 'OR'),
                    '2' =>  array('=', '0', 'OR')
                ),
                'PHPYUNBTWEND_A'    =>  ''
            );
            $ausers             =   $this -> select_all('admin_user', $aWhere, '`uid`,`name`');

            $orgInfo['musers']  =   $musers;
            $orgInfo['muserids']=   $muserids;
            $orgInfo['ausers']  =   $ausers;
        }

        return $orgInfo;
    }

    function getOrgList($where = array(), $data = array())
    {

        $field  =   empty($data['field']) ? '*' : $data['field'];

        $list   =   $this->select_all('crmorg', $where, $field);

        if (!empty($list) && isset($data['type']) && $data['type'] == 'org') {

            $orgOne =   $orgTwo =   $orgThree   =   array();

            foreach ($list as $k => $v) {

                $list[$k]['num']=   $this->select_num('admin_user', array('org' => $v['id']));
            }

            foreach ($list as $k => $v) {
                if ($v['level'] == '1') {

                    $orgOne[]   =   $v;
                } else if ($v['level'] == '2') {

                    $orgTwo[]   =   $v;
                } else if ($v['level'] == '3') {

                    $orgThree[] =   $v;
                }
            }
            foreach ($orgTwo as $k2 => $v2) {
                foreach ($orgThree as $k3 => $v3) {
                    if ($v2['id'] == $v3['fid']) {

                        $orgTwo[$k2]['children'][] = $v3;
                    }
                }
            }

            foreach ($orgOne as $k1 => $v1) {
                foreach ($orgTwo as $k2 => $v2) {
                    if ($v1['id'] == $v2['fid']) {

                        $orgOne[$k1]['children'][] = $v2;
                    }
                }
            }


            return $orgOne;
        }else{
            return $list;
        }
    }

    function configCrmOrg($data = array())
    {

        if (!empty($data)) {

            $id     =   intval($data['id']);
            $puser  =   $this->select_all('admin_user', array('org' => $id), '`uid`');  //  部门成员
            if ($puser) {
                $puids  =   array();
                foreach ($puser as $pv) {

                    $puids[] = $pv['uid'];
                }
                $this->update_once('admin_user', array('org' => ''), array('uid' => array('in', pylode(',', $puids))));
            }
            $result =   $this->update_once('admin_user', array('org' => $id), array('uid' => array('in', pylode(',', $data['uids']))));
            $code   =   $result ? 1 : 2;
        } else {
            $code = 3;
        }
        return $code;
    }

    function delOrgUser($delId, $data=array()){

        if (empty($delId)) {

            $return     =   array( 'errcode' => 8, 'msg' => '请选择要删除的成员数据！');
        } else {

            if (is_array($delId)) {

                $delId  =   pylode(',', $delId);

                $return['layertype']    =   1;

            } else {

                $return['layertype']    =   0;
            }

            $result =   $this -> update_once('admin_user', array('org' => '', 'power' => '', 'spower' => ''), array('uid' => array('in', $delId)));

            $return['msg']      =   '部门成员（UID：'.$delId.'）';

            $return['errcode']  =   $result ? 9 : 8;

            $return['msg']      =   $result ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
        }

        return $return;
    }

    function delOrg($delId){

        if (empty($delId)) {

            $return =   array( 'errcode' => 8, 'msg' => '参数错误，请重试！');
        } else {

            $return['layertype']    =   0;

            $org    =   $this -> select_once('crmorg', array('id' => $delId));

            $result =   $this -> delete_all('crmorg', array('id' => $delId), '');

            if ($result){

                $this -> update_once('admin_user', array('org' => '', 'power' => '', 'spower' => ''), array('org' => $delId));

                if ($org['level'] == '2'){

                    $orgS   =   $this->select_all('crmorg', array('fid' => $delId), '`id`');
                    foreach ($orgS as $v) {
                        $oid[]  =   $v['id'];
                    }
                    $this->delete_all('crmorg', array('fid' => $delId), '');
                    $this->update_once('admin_user', array('org' => '', 'power' => '', 'spower' => ''), array('org' => array('in', pylode(',', $oid))));
                }
            }

            $return['msg']      =   'CRM部门';
            $return['org']      =   $result ? $org['fid'] : $delId;
            $return['errcode']  =   $result ? 9 : 8;
            $return['msg']      =   $result ? $return['msg'].'（ID：'.$delId.'）删除成功！' : $return['msg'].'删除失败！';
        }

        return $return;
    }

    function configCrmPower($uid = null, $data = array()){

        if ($uid!=null && !empty($data)) {

            $result =   $this->update_once('admin_user', $data, array('uid' => $uid));

            $code   =   $result ? 1 : 2;
        }else{
            $code   =   3;
        }

        return $code;
    }

    /**
     * @desc CRM 工作台 消息提醒
     * @param $crmId
     * @return false|string
     */
    function getNotice($crmId)
    {
        $today = strtotime('today');

        $comList            =   $this->select_all('company', array(
            'crm_uid' => $crmId,
            'r_status' => array('<>', '4'), // 不包含暂停的企业
            'clue' => 1, // 不包含线索库
            'crm_time' => array('<', strtotime('-1 day', $today)), // 不查最新认领的
        ), '`uid`,`f_time`,`crm_time`, `rating`,`rating_name`,`vipstime`,`vipetime`');

        include PLUS_PATH.'comrating.cache.php';

        $ratingArr          =   array();
        foreach ($comrat as $k => $v) {
            $ratingArr[$v['id']]    =   $v['name'];
        }
        $ratingArr['999']   =   '过期会员';

        if (isset($comList) && !empty($comList)){

            $ids            =   array();
            foreach ($comList as $k1 => $v1){
                $ids[]      =   $v1['uid'];
            }



            $toFollowList   =   $this->select_all('crm_work_plan', array('uid' => $crmId, 'type' => '22', 'status' => '1', 'stime' => array('<', strtotime(date('Y-m-d 23:59:59'))), 'comid' => array('in', pylode(',', $ids))),'`comid`');
            $toFUids        =   array();
            foreach ($toFollowList as $toV) {
                $toFUids[$toV['comid']]  =   $toV['comid'];
            }

            //  今天需要跟进数量
            $todayFollowNum =   count($toFUids) > 0 ? count($toFUids) : 0;

            //  30天未登录
            $noLoginWhere   =   array(
                'uid'       =>  array('in', pylode(',', $ids)),
                'login_date'=>  array('<', strtotime('-30 day'))
            );
            $noLoginNum     =   $this->select_num('company', $noLoginWhere);

            //  到期未续费
            $endWhere       =   array(
                'uid'               =>  array('in', pylode(',', $ids)),
                'PHPYUNBTWSTART_A'  =>  '',
                'vipetime'          =>  array(
                    '0' =>  array('>', '0', ''),
                    '1' =>  array('<', strtotime('today'), '')
                ),
                'PHPYUNBTWEND_A' => ''
            );
            $vipedNum       =   $this->select_num('company', $endWhere);

            //  15天内到期客户
            $willWhere      =   array(
                'uid'               =>  array('in', pylode(',', $ids)),
                'PHPYUNBTWSTART_A'  =>  '',
                'vipetime'          =>  array(
                    '0' =>  array('>', strtotime('today'), ''),
                    '1' =>  array('<', strtotime('+15 days'), '')
                ),
                'PHPYUNBTWEND_A' => ''
            );
            $willDoneNum    =   $this->select_num('company', $willWhere);

            //  从未跟进客户
            $noWhere        =   array(
                'uid'               =>  array('in', pylode(',', $ids)),
                'PHPYUNBTWSTART_A'  =>  '',
                'f_time'            =>  array('=', '', 'or'),
                'isfollow'          =>  array('=', '0', 'or'),
                'PHPYUNBTWEND_A'    =>  ''
            );
            $noFollowNum    =   $this->select_num('company', $noWhere);

            //  30天未跟进客户
            $recentWhere    =   array(
                'uid'               =>  array('in', pylode(',', $ids)),
                'PHPYUNBTWSTART_A'  =>  '',
                'f_time'            =>  array(
                    0   =>  array('>', 0),
                    1   =>  array('<', strtotime('-30 days'))
                ),
                'PHPYUNBTWEND_A'    =>  ''
            );
            $recentFollowNum=   $this->select_num('company', $recentWhere);

            //  今日登录客户
            $loginWhere     =   array(
                'uid'               =>  array('in', pylode(',', $ids)),
                'login_date'        =>  array('>', strtotime(date('Y-m-d')))
            );
            $loginNum       =   $this->select_num('company', $loginWhere);

            //  CRM设置：X天未跟进，释放提醒
            //$releaseHtml    =   '';
            $releaseSet     =   $this->select_all('crmset', array('release_day' => array('>', '3'))); // 提前3天提醒
            $releaseData    =   array();
            foreach ($releaseSet as $ck => $cv) {
                $reday = intval($cv['release_day']) - 3;
                $retime = strtotime("-{$reday} day", $today);
                foreach ($comList as $k => $v) {
                    if ((!empty($v['f_time']) && $v['f_time'] < $retime) || ($v['crm_time'] < $retime && empty($v['f_time']))) {
                        if ($v['rating'] > 0 && ($v['vipetime'] >= $today || $v['vipetime'] == 0)) {

                            if (in_array($v['rating'], @explode(',', $cv['rating']))) {

                                $releaseData[$v['rating']]['num']++;
                                $releaseData[$v['rating']]['day']    = $cv['release_day'];
                                $releaseData[$v['rating']]['rating'] = $v['rating'];
                                $releaseData[$v['rating']]['name']   = $ratingArr[$v['rating']];

                            }
                        }else{

                            if (in_array('999', @explode(',', $cv['rating']))) {

                                $releaseData[999]['num']++;
                                $releaseData[999]['day']    = $cv['release_day'];
                                $releaseData[999]['rating'] = 0;
                                $releaseData[999]['name']   = $ratingArr['999'];

                            }
                        }
                    }
                }
            }

            //  CRM设置：X天会员未变更，释放提醒
            $dealSet        =   $this->select_all('crmset', array('deal_day' => array('>', '3'))); // 提前3天提醒
            $dealData       =   array();
            foreach ($dealSet as $ck => $cv) {
                $deday = intval($cv['deal_day']) - 3;
                $detime = strtotime("-{$deday} day", $today);
                foreach ($comList as $k => $v) {
                    if ($v['crm_time'] < $detime){

                        if ($v['rating'] > 0 && ($v['vipetime'] >= $today || $v['vipetime'] == 0)) {
                            if (in_array($v['rating'], @explode(',', $cv['rating']))){
                                if ($v['vipstime'] < $detime && isset($v['vipstime'])){

                                    $dealData[$v['rating']]['num']++;
                                    $dealData[$v['rating']]['day']    = $cv['deal_day'];
                                    $dealData[$v['rating']]['rating'] = $v['rating'] == '999' ? '0' : $v['rating'];
                                    $dealData[$v['rating']]['name']   = $ratingArr[$v['rating']];

                                }
                            }
                        }else{
                            if($v['rating'] == '999' && in_array('999', @explode(',', $cv['rating']))){
                                if($v['vipetime'] > 0 && $v['vipetime'] < $detime) {

                                    $dealData[$v['rating']]['num']++;
                                    $dealData[$v['rating']]['day']    = $cv['deal_day'];
                                    $dealData[$v['rating']]['rating'] = '0';
                                    $dealData[$v['rating']]['name']   = $ratingArr[$v['rating']];

                                }
                            }
                        }
                    }
                }
            }

            //  CRM设置：X天未跟进，提醒跟进
            $followSet      =   $this->select_all('crmset', array('follow_day' => array('>', '0')));
            $followData     =   array();
            foreach ($followSet as $ck => $cv) {
                $fetime = strtotime("-" . $cv['follow_day'] . " day");
                foreach ($comList as $k => $v) {

                    if ($v['rating'] > 0 && ($v['vipetime'] >= $today || $v['vipetime'] == 0)) {
                        if (in_array($v['rating'], @explode(',', $cv['rating'])) && (($v['crm_time'] < $fetime && empty($v['f_time']))
                                || (!empty($v['f_time']) && $v['f_time'] < $fetime))) {

                            $followData[$v['rating']]['num']++;
                            $followData[$v['rating']]['day']    = $cv['follow_day'];
                            $followData[$v['rating']]['rating'] = $v['rating'];
                            $followData[$v['rating']]['name']   = $ratingArr[$v['rating']];

                        }
                    }else{

                        if (in_array('999', @explode(',', $cv['rating'])) && (($v['crm_time'] < $fetime && empty($v['f_time']))
                                || (!empty($v['f_time']) && $v['f_time'] < $fetime))) {

                            $followData[999]['num']++;
                            $followData[999]['day']    = $cv['follow_day'];
                            $followData[999]['rating'] = 0;
                            $followData[999]['name']   = $ratingArr['999'];

                        }
                    }
                }
            }

            $noticeNum      =   $this->select_num('rating_notice', array('crm_uid' => $crmId));

            return array('todayFollowNum' => $todayFollowNum, 'noLoginNum' => $noLoginNum, 'vipedNum' => (int)$vipedNum, 'willDoneNum' => (int)$willDoneNum, 'noFollowNum' => (int)$noFollowNum, 'loginNum' => (int)$loginNum, 'recentFollowNum' => (int)$recentFollowNum, 'followHtml' => $followHtml,'followData' => $followData, 'releaseData' => $releaseData, 'dealData' => $dealData, 'noticeNum' => $noticeNum);
        }else{

            return array('todayFollowNum' => 0, 'noLoginNum' => 0, 'vipedNum' => 0, 'willDoneNum' => 0, 'noFollowNum' => 0, 'loginNum' => 0, 'recentFollowNum' => 0,'followData' => array(), 'releaseData' => array(), 'dealData' => array(), 'noticeNum' => 0);
        }
    }

    /**
     * @desc CRM 工作台 新增客户榜
     * @param $time
     * @return mixed
     */
    function getKhbList($time){

        $timeA  =   explode('-', $time);
        $month  =   (int)$timeA[1];

        $sTime  =   mktime(0, 0, 0, $month, 1, $timeA[0]);
        $eTime  =   mktime(23, 59, 59, $month+1, 0, $timeA[0]);

        $where  =   array(
            'crm_uid'           =>  array('>', 0),
            'PHPYUNBTWSTART_A'  =>  '',
            'crm_time'          =>  array(
                0   =>  array('>=', $sTime, ''),
                1   =>  array('<', $eTime, ''),
            ),
            'PHPYUNBTWEND_A'    =>  '',
            'groupby'           =>  'crm_uid',
            'orderby'           =>  'khnum, desc'
        );
        $khb    =   $this->select_all('company', $where, '`crm_uid`, count(`uid`) as khnum');

        $nWhere =   array(
            'crm_uid'           =>  array('>', 0),
            'PHPYUNBTWSTART_A'  =>  '',
            'crm_time'          =>  array(
                '0'   =>  array('>=', $sTime, ''),
                '1'   =>  array('<', $eTime, ''),
            ),
            'PHPYUNBTWEND_A'    =>  ''
        );
        $kbNUm  =   $this->select_num('company', $nWhere);

        if (!empty($khb)){

            $crmUids    =   array();
            foreach ($khb as $k => $v) {
                $crmUids[]  =   $v['crm_uid'];
            }

            $crmUsers   =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $crmUids))), '`name`,`uid`');

            foreach ($khb as $k => $v) {
                foreach ($crmUsers as $ck => $cv) {
                    if ($v['crm_uid'] ==   $cv['uid']){

                        $khb[$k]['name']    =   $cv['name'];
                        $khb[$k]['per']     =   round($v['khnum'] / $kbNUm * 100,2)."%";
                        $khb[$k]['url']     =   'index.php?m=crm_salesman_list&c=customer_list&auid='.$cv['uid'].'&crm_time='.$time;
                    }
                }
            }
        }

        $result['data']  = $khb;
        $result['total'] = $kbNUm;

        return  $result;

    }

    /**
     * @desc CRM 工作台 成交金额榜
     * @param $time
     * @return mixed
     */
    function getJebList($time){

        $timeA  =   explode('-', $time);
        $month  =   (int)$timeA[1];

        $sTime  =   mktime(0, 0, 0, $month, 1, $timeA[0]);
        $eTime  =   mktime(23, 59, 59, $month+1, 0, $timeA[0]);

        $start  =   date('Y-m-d', $sTime);
        $end    =   date('Y-m-d', $eTime);

        $where  =   array(
            'order_state'       =>  2,
            'PHPYUNBTWSTART_A'  =>  '',
            'order_time'        =>  array(
                '0' =>  array('>=', $sTime, ''),
                '1' =>  array('<', $eTime, '')
            ),
            'PHPYUNBTWEND_A'    =>  '',
            'groupby'           =>  'crm_uid',
            'orderby'           =>  'total, desc'
        );

        $jeb    =   $this->select_all('company_order', $where, '`crm_uid`, sum(`order_price`) as total');

        $nWhere =   array(
            'order_state'       =>  2,
            'PHPYUNBTWSTART_A'  =>  '',
            'order_time'        =>  array(
                '0' =>  array('>=', $sTime, ''),
                '1' =>  array('<', $eTime, '')
            ),
            'PHPYUNBTWEND_A'    =>  ''
        );
        $jeArr  =   $this->select_all('company_order', $nWhere, 'sum(`order_price`) as total');
        if (!empty($jeb)){

            $crmUids    =   array();
            foreach ($jeb as $k => $v) {
                if ($v['crm_uid'] > 0){
                    $crmUids[]  =   $v['crm_uid'];
                }
            }

            $crmUsers   =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $crmUids))), '`name`,`uid`');
            foreach ($jeb as $k => $v) {
                if ($v['crm_uid'] > 0) {
                    foreach ($crmUsers as $ck => $cv) {
                        if ($v['crm_uid'] == $cv['uid']) {

                            $jeb[$k]['name'] = $cv['name'];
                            $jeb[$k]['per'] = round($v['total'] / $jeArr[0]['total'] * 100, 2) . "%";
                            $jeb[$k]['url'] = 'index.php?m=crm_audit&uid='.$cv['uid'].'&time=2&stime='.$start.'&etime='.$end;
                        }
                    }
                }else{

                    $jeb[$k]['name']    =   '系统';
                    $jeb[$k]['per']     =   round($v['total'] / $jeArr[0]['total'] * 100,2)."%";
                    $jeb[$k]['url']     =   'index.php?m=crm_audit&uid=-1&time=2&stime='.$start.'&etime='.$end;
                }
            }
        }
        $result['data']  = $jeb;
        $result['total'] = isset($jeArr[0]['total']) ? $jeArr[0]['total'] : 0;

        return  $result;
    }

    /**
     * @desc CRM - 数据看板
     * @param array $where
     * @return array|bool|false|string|void
     */
    function getCrmList($where = array())
    {

        $rows   =   $this->select_all('admin_user', $where);

        if (!empty($rows)){

            $allKh      =   $this->select_all('company', array('crm_uid' => array('>', 0)), '`crm_uid`');

            $noFList    =   $this->select_all('company', array('isfollow' => 0), '`crm_uid`');

            $taskList   =   $this->select_all('crm_work_plan', array('type' => 22, 'status' => 1), '`uid`');

            $newList    =   $this->select_all('member', array('usertype' => 2, 'pid' => 0, 'reg_date' => array('>', strtotime(date('Y-m-d')))), '`uid`');

            $newUids    =   array();
            foreach ($newList as $nk=>$nv) {
                $newUids[]  =   $nv['uid'];
            }
            $newKh      =   $this->select_all('company', array('crm_uid' => array('>', 0), 'uid' => array('in', pylode(',', $newUids))), '`crm_uid`');

            $newFollow  =   $this->select_all('company', array('crm_uid' => array('>', 0), 'f_time' => array('>', strtotime(date('Y-m-d')))),'`crm_uid`');

            $willWhere  =   array(
                'crm_uid' => array('>', 0),
                'PHPYUNBTWSTART_A'  =>  '',
                'vipetime' => array(
                    '0'   =>  array('<', strtotime('+15 days'), 'and'),
                    '1'   =>  array('>', strtotime('today'), 'and'),
                ),
                'PHPYUNBTWEND_A'  =>  ''
            );
            $willDoneKh =   $this->select_all('company', $willWhere, '`crm_uid`');
            $recentFollowKh =   $this->select_all('company', array('crm_uid' => array('>', 0), 'f_time' => array('>', strtotime('-7 days'))), '`crm_uid`');

            $vipWhere   =   array(
                'crm_uid'   =>  array('>', 0),
                'PHPYUNBTWSTART_A'  =>  '',
                'rating'    =>  array(
                    '0' =>  array('>', 0),
                    '1' =>  array('in', $this->config['crm_deal_rating'])
                ),
                'PHPYUNBTWEND_A'    =>  '',
                'PHPYUNBTWSTART_B'  =>  '',
                'vipetime'  =>  array(
                    0   =>  array('>', strtotime('today'), ''),
                    1   =>  array('=', 0, 'OR')
                ),
                'PHPYUNBTWEND_B'    =>  '',
            );

            $vipKh      =   $this->select_all('company', $vipWhere, '`crm_uid`');

            $dealList   =   $this->select_all('company_order',  array('crm_uid' => array('>', 0), 'groupby' => 'crm_uid'), '`crm_uid`, count(*) as `num`, sum(`order_price`) as `amount`');

            if ($this->config['sy_free_id'] > 0) {
                $freeKh =   $this->select_all('company', array('crm_uid' => array('>', 0), 'rating' => $this->config['sy_free_id']), '`crm_uid`');
            }

            $vipDoneWhere   =   array(
                'crm_uid'           =>  array('>', 0),
                'PHPYUNBTWSTART_A'  =>  '',
                'vipetime'    => array(
                    '0' =>  array('>', 0, ''),
                    '1' =>  array('<', strtotime('today'), '')
                ),
                'PHPYUNBTWEND_A'  =>  ''
            );
            $vipDoneKh  =   $this->select_all('company', $vipDoneWhere,'`crm_uid`');

            foreach ($rows as $k => $v) {

                $rows[$k]['allNum']             =   0;
                $rows[$k]['noFollowNum']        =   0;
                $rows[$k]['followNum']          =   0;
                $rows[$k]['newNum']             =   0;
                $rows[$k]['newFollowNum']       =   0;
                $rows[$k]['willDoneNum']        =   0;
                $rows[$k]['recentFollowNum']    =   0;
                $rows[$k]['vipNum']             =   0;
                $rows[$k]['dealNum']            =   0;
                $rows[$k]['dealAmount']         =   0.00;
                $rows[$k]['freeNum']            =   0;
                $rows[$k]['vipDoneNum']         =   0;

                if (!empty($allKh)) {
                    foreach ($allKh as $val) {
                        if ($v['uid'] == $val['crm_uid']) {
                            $rows[$k]['allNum']++;
                        }
                    }
                }
                if (!empty($noFList)) {
                    foreach ($noFList as $val) {
                        if ($v['uid'] == $val['crm_uid']) {
                            $rows[$k]['noFollowNum']++;
                        }
                    }
                }
                if (!empty($taskList)) {
                    foreach ($taskList as $val) {
                        if ($v['uid'] == $val['uid']) {
                            $rows[$k]['followNum']++;
                        }
                    }
                }
                if (!empty($newKh)) {
                    foreach ($newKh as $val) {
                        if ($v['uid'] == $val['crm_uid']) {
                            $rows[$k]['newNum']++;
                        }
                    }
                }
                if (!empty($newFollow)) {
                    foreach ($newFollow as $val) {
                        if ($v['uid'] == $val['crm_uid']) {
                            $rows[$k]['newFollowNum']++;
                        }
                    }
                }
                if (!empty($willDoneKh)) {
                    foreach ($willDoneKh as $val) {
                        if ($v['uid'] == $val['crm_uid']) {
                            $rows[$k]['willDoneNum']++;
                        }
                    }
                }
                if (!empty($recentFollowKh)) {
                    foreach ($recentFollowKh as $val) {
                        if ($v['uid'] == $val['crm_uid']) {
                            $rows[$k]['recentFollowNum']++;
                        }
                    }
                }
                if (!empty($vipKh)) {
                    foreach ($vipKh as $val) {
                        if ($v['uid'] == $val['crm_uid']) {
                            $rows[$k]['vipNum']++;
                        }
                    }
                }
                if (!empty($dealList)) {
                    foreach ($dealList as $val) {
                        if ($v['uid'] == $val['crm_uid']) {

                            $rows[$k]['dealNum']    =   $val['num'];
                            $rows[$k]['dealAmount'] =   number_format($val['amount'], 2);
                        }
                    }
                }
                if (!empty($freeKh)) {
                    foreach ($freeKh as $val) {
                        if ($v['uid'] == $val['crm_uid']) {
                            $rows[$k]['freeNum']++;
                        }
                    }
                }
                if (!empty($vipDoneKh)) {
                    foreach ($vipDoneKh as $val) {
                        if ($v['uid'] == $val['crm_uid']) {

                            $rows[$k]['vipDoneNum']++;
                        }
                    }
                }
            }

            foreach ($rows as $k => $v){
                $rows[$k]['syNum']      =   0;
                if ($this->config['sy_crm_deal_open'] == 1){

                    $rows[$k]['syNum']  =   $v['num'] > 0 ? $v['num'] - ($v['allNum'] - $v['vipNum']) : '不限';
                }else if ($this->config['sy_crm_deal_open'] == 2){

                    $rows[$k]['syNum']  =   $v['num'] > 0 ? $v['num'] - $v['allNum'] : '不限';
                }
            }
        }

        return $rows;
    }

    /**
     * @desc CRM - 管理员 - 工作看板
     * @return array
     */
    function getManageData()
    {

        //  客户总数
        $allCustomerNum =   $this->select_num('company');

        //  公海客户
        $pubCustomerNum =   $this->select_num('company', array('crm_uid' => 0));

        //  待回访客户
        $visitTask      =   $this->select_all('crm_work_plan', array('type' => 22, 'status' => 1, 'groupby' => 'comid'));
        $reVisitNum     =   count($visitTask);

        //  暂停客户
        $suspendNum     =   $this->select_num('company', array('r_status' => 4));

        //  今日新增
        $newNum         =   $this->select_num('member', array('usertype' => 2, 'pid' => 0, 'reg_date' => array('>', strtotime(date('Y-m-d')))));

        //  今日跟进
        $todayFollow    =   $this->select_all('crmnew_concern', array('ftime' => array('>', strtotime(date('Y-m-d'))), 'groupby' => 'uid'));
        $newFollow      =   count($todayFollow);

        //  成交金额
        $orderAllPrice  =   $this->select_once('company_order', array('order_price' => array('>', '0'), 'order_state' => 2, 'crm_uid' => array('>', 0)), 'sum(`order_price`) as `amount`');
        $dealAmount     =   floatval($orderAllPrice['amount']) > 0 ? number_format($orderAllPrice['amount'], 2) : '0.00';

        //  今日成交
        $orderNewPrice  =   $this->select_once('company_order', array('order_price' => array('>', '0'), 'order_state' => 2, 'crm_uid' => array('>', 0), 'order_time' => array('>', strtotime(date('Y-m-d')))), 'sum(`order_price`) as `amount`');
        $dealNew        =   floatval($orderNewPrice['amount']) > 0 ? number_format($orderNewPrice['amount'], 2) : '0.00';

        //  15天内到期客户
        $willWhere      =   array(

            'PHPYUNBTWSTART_A'  =>  '',
            'vipetime'          =>  array(
                '0' =>  array('>', strtotime('today'), ''),
                '1' =>  array('<', strtotime('+15 days'), '')
            ),
            'PHPYUNBTWEND_A' => ''
        );
        $willDoneNum    =   $this->select_num('company', $willWhere);

        //  到期未续费
        $endWhere       =   array(
            'PHPYUNBTWSTART_A'  =>  '',
            'vipetime'          =>  array(
                '0' =>  array('>', '0', ''),
                '1' =>  array('<', strtotime('today'), '')
            ),
            'PHPYUNBTWEND_A' => ''
        );
        $doneNum        =   $this->select_num('company', $endWhere);

        //  30天未跟进
        $where['PHPYUNBTWSTART_A']  =   '';
        $where['f_time'][]          =   array('>', 0);
        $where['f_time'][]          =   array('<', strtotime('-30 days'));
        $where['PHPYUNBTWEND_A']    =   '';
        $recentFollow   =   $this->select_num('company', $where);

        //  从未跟进
        $noFollow       =   $this->select_num('company', array('isfollow' => '0'));

        //  30天未登录
        $noLogin        =   $this->select_num('company', array('login_date' => array('<', strtotime('-30 days'))));

        $ratingNum      =   $this->select_num('rating_notice', array());

        $arr = array(
            'allCustomerNum' => $allCustomerNum,
            'pubCustomerNum' => $pubCustomerNum,
            'reVisitNum' => $reVisitNum,
            'suspendNum' => $suspendNum,

            'newNum' => $newNum,
            'newFollow' => $newFollow,
            'dealAmount' => $dealAmount,
            'dealNew' => $dealNew,

            'willDoneNum' => $willDoneNum,
            'doneNum' => $doneNum,

            'recentFollow' => $recentFollow,
            'noFollow' => $noFollow,

            'noLogin' => $noLogin,
            'ratingNum' => $ratingNum
        );

        return  $arr;
    }

    /**
     * @desc CRM - 管理员 - 数据看板 数据汇总、数据提醒
     * @return array
     */
    function getManageDataV2()
    {
        //  客户总数
        $allCustomerNum = $this->select_num('company');
        //  今日新增
        $newNum = $this->select_num('member', array('usertype' => 2, 'pid' => 0, 'reg_date' => array('>', strtotime(date('Y-m-d')))));
        //  昨日新增
        $yesterdayNewWhere = array(
            'usertype' => 2,
            'pid' => 0,
            'PHPYUNBTWSTART_A' => '',
            'reg_date' => array(
                '0' => array('>', strtotime(date('Y-m-d 00:00:00', strtotime('-1 days'))), ''),
                '1' => array('<', strtotime(date('Y-m-d 23:59:59', strtotime('-1 days'))), '')
            ),
            'PHPYUNBTWEND_A' => ''
        );
        $yesterdayNewNum = $this->select_num('member', $yesterdayNewWhere);
        //  公海客户
        $pubCustomerNum = $this->select_num('company', array('crm_uid' => 0));
        //  今日成交
        $orderNewPrice = $this->select_once('company_order', array('order_price' => array('>', '0'), 'order_state' => 2, 'crm_uid' => array('>', 0), 'order_time' => array('>', strtotime(date('Y-m-d')))), 'sum(`order_price`) as `amount`');
        $dealNew = floatval($orderNewPrice['amount']) > 0 ? number_format($orderNewPrice['amount'], 2) : '0.00';
        //  昨日成交
        $yesterdayOrderWhere = array(
            'order_price' => array('>', '0'),
            'order_state' => 2,
            'crm_uid' => array('>', 0),
            'PHPYUNBTWSTART_A' => '',
            'order_time' => array(
                '0' => array('>', strtotime(date('Y-m-d 00:00:00', strtotime('-1 days'))), ''),
                '1' => array('<', strtotime(date('Y-m-d 23:59:59', strtotime('-1 days'))), '')
            ),
            'PHPYUNBTWEND_A' => ''
        );
        $yesterdayOrderNewPrice = $this->select_once('company_order', $yesterdayOrderWhere, 'sum(`order_price`) as `amount`');
        $yesterdayDealNew = floatval($yesterdayOrderNewPrice['amount']) > 0 ? number_format($yesterdayOrderNewPrice['amount'], 2) : '0.00';
        //  今日跟进
        $newFollow = $this->select_num('crmnew_concern', array('ftime' => array('>', strtotime(date('Y-m-d')))));
        //  昨日跟进
        $yesterdayFollowWhere = array(
            'PHPYUNBTWSTART_A' => '',
            'ftime' => array(
                '0' => array('>', strtotime(date('Y-m-d 00:00:00', strtotime('-1 days'))), ''),
                '1' => array('<', strtotime(date('Y-m-d 23:59:59', strtotime('-1 days'))), '')
            ),
            'PHPYUNBTWEND_A' => ''
        );
        $yesterdayNewFollow = $this->select_num('crmnew_concern', $yesterdayFollowWhere);
        // 今日通话
        $todayCall = $this->select_num('staff_call_log', array('ctime' => array('>', strtotime(date('Y-m-d')))));
        //  昨日通话
        $yesterdayCallWhere = array(
            'PHPYUNBTWSTART_A' => '',
            'ctime' => array(
                '0' => array('>', strtotime(date('Y-m-d 00:00:00', strtotime('-1 days'))), ''),
                '1' => array('<', strtotime(date('Y-m-d 23:59:59', strtotime('-1 days'))), '')
            ),
            'PHPYUNBTWEND_A' => ''
        );
        $yesterdayCall = $this->select_num('staff_call_log', $yesterdayCallWhere);
        // 数据统计跟进周期内到期客户
        // 获取数据统计跟进周期
        $cycle = 15; // 默认15天
        $today = strtotime('today'); // 今天
        $willWhere = array(
            'PHPYUNBTWSTART_A' => '',
            'vipetime' => array(
                '0' => array('>=', $today, ''),
                '1' => array('<', strtotime("+{$cycle} day", $today), '')
            ),
            'PHPYUNBTWEND_A' => ''
        );
        if ($this->config['crm_deal_rating']) {
            $willWhere['rating'] = array('in', $this->config['crm_deal_rating']);
        }
        $willDoneNum = $this->select_num('company', $willWhere);

        // 过期客户
        $endWhere = array(
            'PHPYUNBTWSTART_A' => '',
            'vipetime' => array(
                '0' => array('>', '0', ''),
                '1' => array('<', strtotime('today'), '')
            ),
            'PHPYUNBTWEND_A' => ''
        );
        if ($this->config['crm_deal_rating']) {
            $endWhere['rating'] = array('in', $this->config['crm_deal_rating']);
        }
        $doneNum = $this->select_num('company', $endWhere);

        // 成交客户
        $cjWhere = array(
            'PHPYUNBTWSTART_A' => '',
            'vipetime' => array(
                '0' => array('=', '0', ''),
                '1' => array('>', strtotime('today'), 'OR')
            ),
            'PHPYUNBTWEND_A' => ''
        );
        if ($this->config['crm_deal_rating']) {
            $cjWhere['rating'] = array('in', $this->config['crm_deal_rating']);
        }
        $cjNum = $this->select_num('company', $cjWhere);
        // 线索客户
        $xsWhere = array('clue' => 2);
        $xsNum = $this->select_num('company', $xsWhere);
        //  15天未跟进
//        $where['PHPYUNBTWSTART_A'] = '';
//        $where['f_time'][] = array('>', 0);
//        $where['f_time'][] = array('<', strtotime('-15 days'));
//        $where['PHPYUNBTWEND_A'] = '';
//        $recentFollow = $this->select_num('company', $where);
        //  从未跟进
//        $noFollow = $this->select_num('company', array('isfollow' => '0'));
        // 权益提醒
//        $ratingNum = $this->select_num('rating_notice', array());
        $arr = array(
            'allCustomerNum' => $allCustomerNum,// 客户总数
            'newNum' => $newNum,// 今日新增
            'yesterdayNewNum' => $yesterdayNewNum,// 昨日新增
            'newNumRate' => $yesterdayNewNum > 0 ? sprintf('%.2f', 100*($newNum - $yesterdayNewNum)/$yesterdayNewNum) : '',
            'pubCustomerNum' => $pubCustomerNum,// 公海客户
//            'dealAmount' => $dealAmount,// 成交总额
            'dealNew' => $dealNew,// 今日成交
            'dealNewRate' => $yesterdayDealNew > 0 ? sprintf('%.2f', 100*($dealNew - $yesterdayDealNew)/$yesterdayDealNew) : '',
            'newFollow' => $newFollow,// 今日跟进
            'newFollowRate' => $yesterdayNewFollow ? sprintf('%.2f', 100*($newFollow - $yesterdayNewFollow)/$yesterdayNewFollow) : '',
            'newCall' => $todayCall,// 今日通话
            'newCallRate' => $yesterdayCall ? sprintf('%.2f', 100*($todayCall - $yesterdayCall)/$yesterdayCall) : '',
//            'noLogin' => $noLogin,// 30天未登录
            'willDoneNum' => $willDoneNum,// 待续费客户
            'doneNum' => $doneNum,// 过期客户
            'cjNum' => $cjNum,// 成交客户
            'xsNum' => $xsNum,// 线索客户
//            'recentFollow' => $recentFollow,// 15天未跟进
//            'noFollow' => $noFollow,// 从未跟进
//            'ratingNum' => $ratingNum// 权益提醒
        );
        return  $arr;
    }

    /*
     * 业务员效率报表
     * $start 工作时间开始时间
     * $end 工作时间结束时间
     */
    function getCrmUserXlData($start, $end)
    {
        $crmAdminType = $this->config['crm_admin_type'] ? explode(',', $this->config['crm_admin_type']) : [];
        if ($crmAdminType)  {
            $adminWhere['m_id'] = array('in', pylode(',', $crmAdminType));
        }
        $adminWhere['is_crm'] = 1;
        $adminWhere['groupby'] = 'uid';
        // 统计日期开始、结束时间
        $dayStart = strtotime(date('Y-m-d 00:00:00', $start));
        $dayEnd = strtotime(date('Y-m-d 23:59:59', $end));
        $users = $this->select_all('admin_user', $adminWhere,'`uid`,`name`,`call_num`,`follow_num`,`tuoxin_num`,`deal_num`,`photo`');
        $uids = array();
        foreach ($users as $v) {
            $uids[] = $v['uid'];
        }
        // 通话量、有效通话
        $callnumArr = $this->getOutCallTj($uids, $dayStart, $dayEnd);
        // 跟进量
        $follownumArr = $this->getFollowTj($uids, $dayStart, $dayEnd);
        // 拓新
        $newnumArr = $this->getTxTj($uids, $dayStart, $dayEnd);
        //  成交额
        $orderWhere = array(
            'crm_uid' => array('in', pylode(',', $uids)),
            'order_price' => array('>', '0'),
            'order_state' => 2,
            'PHPYUNBTWSTART_A' =>  '',
            'order_time' => array(
                0 => array('>=', $dayStart, ''),
                1 => array('<=', $dayEnd, '')
            ),
            'PHPYUNBTWEND_A' =>  '',
            'groupby' => 'crm_uid'
        );
        $orderPrice = $this->select_all('company_order', $orderWhere, '`crm_uid`,sum(`order_price`) as `amount`');
        $orderpriceArr = array();
        foreach ($orderPrice as $k => $v) {
            $orderPrice[$k]['amount'] = floatval($v['amount']) > 0 ? $v['amount'] : '0.00';
            $orderpriceArr[$v['crm_uid']] = $orderPrice[$k];
        }
        // 新增岗位
        $jobnumArr = $this->getJobTj($uids, $dayStart, $dayEnd);
        $rt = array();

        // 管理员行为分析计算最大空闲、空闲指数、活跃指数
        $logWhere = array(
            'uid' => array('in', pylode(',', $uids)),
            'PHPYUNBTWSTART_A' =>  '',
            'time' => array(
                0 => array('>=', $start, ''),
                1 => array('<=', $end, '')
            ),
            'PHPYUNBTWEND_A' =>  '',
            'orderby' => 'time,asc'
        );
        // 获取所有业务员行为日志
        $logs = $this->select_all('admin_web_log', $logWhere, '`uid`,`time`');
        // 休息时间
        $resttime = array();
        if ($this->config['crm_rest_time']) {
            $restTimeRange = explode('~', $this->config['crm_rest_time']);
            $resttime = array(
                strtotime(date('Y-m-d ' . $restTimeRange[0] . ':00', $start)),
                strtotime(date('Y-m-d ' . $restTimeRange[1] . ':00', $end))
            );
        }
        $crmLoss = array();
        foreach ($logs as $v) {
            if ($resttime && $v['time'] >= $resttime[0] && $v['time'] <= $resttime[1]) {//跳过休息时间段的记录
                continue;
            }
            $crmLoss[$v['uid']][] = $v;
        }
        $kxdata = array();
        foreach ($crmLoss as $crm_uid => $v) {
            $kxdata[$crm_uid] = $this->getKxIndexAndMaxRange($v, $start, $end, $resttime);
        }
        $workendtime = $end;//下班时间点
        if (date('Y-m-d') == date('Y-m-d', $start) && time() < $end) {// 当天下班时间点前需要将结束时间改为当前时间戳计算空闲指数
            $end = time();
        }
        $defKxIdx = floor(($end -  $start)/600);
        $defKxDis = sprintf('%.1f', ($end - $start)/3600);
        $defKxRange = date('H:i',$start) . '~'.date('H:i',$end);
        if ($resttime) {
            if ($end > $resttime[1] && $end < $workendtime) {// 结束时间在下午上班时间段
                $afternoon = $end - $resttime[1];
                $morning = $resttime[0] - $start;
                $defKxIdx = floor(($morning +  $afternoon)/600);
                if ($afternoon > $morning) {
                    $defKxDis = sprintf('%.1f', $afternoon/3600);
                    $defKxRange = date('H:i', $resttime[1]) . '~'.date('H:i', $end);
                } else {
                    $defKxDis = sprintf('%.1f', $morning/3600);
                    $defKxRange = date('H:i', $start) . '~'.date('H:i', $resttime[0]);
                }
            } else if ($end >= $resttime[0] && $end <= $resttime[0]) {// 结束时间在午休时间段
                $morning = $resttime[0] - $start;
                $defKxIdx = floor($morning/600);
                $defKxDis = sprintf('%.1f', $morning/3600);
                $defKxRange = date('H:i', $start) . '~'.date('H:i', $resttime[0]);
            } else if ($end < $start) {// 结束时间在上班前时间段
                $defKxIdx = 0;
                $defKxDis = 0;
                $defKxRange = '';
            } else {
                $morning = $end - $start;
                $defKxIdx = floor($morning/600);
                $defKxDis = sprintf('%.1f', $morning/3600);
                $defKxRange = date('H:i', $start) . '~'.date('H:i', $end);
            }
        }
        foreach ($users as $k => $v) {
            $yxnum = $callnumArr[$v['uid']]['yx_num'] ? $callnumArr[$v['uid']]['yx_num'] : 0;
            $totalcallnum = $callnumArr[$v['uid']]['num'] ? $callnumArr[$v['uid']]['num'] : 0;
            $follownum = $follownumArr[$v['uid']]['num'] ? $follownumArr[$v['uid']]['num'] : 0;
            $newnum = $newnumArr[$v['uid']]['num'] ? $newnumArr[$v['uid']]['num'] : 0;
            $pricenum = $orderpriceArr[$v['uid']]['amount'] ? $orderpriceArr[$v['uid']]['amount'] : 0;
            $call_num_rate = $v['call_num'] > 0 ? floatval(sprintf('%.1f', 100*$totalcallnum/$v['call_num'])) : 0;
            $call_num_rate > 100 && $call_num_rate = 100;
            $follow_num_rate = $v['follow_num'] > 0 ? floatval(sprintf('%.1f', 100*$follownum/$v['follow_num'])) : 0;
            $follow_num_rate > 100 && $follow_num_rate = 100;
            $new_num_rate = $v['tuoxin_num'] > 0 ? floatval(sprintf('%.1f', 100*$newnum/$v['tuoxin_num'])) : 0;
            $new_num_rate > 100 && $new_num_rate = 100;
            $avgRate = ($call_num_rate + $follow_num_rate + $new_num_rate)/3;
            $avgRateColorArr = $this->getColor($avgRate);
            $callNumColorArr = $this->getColor($call_num_rate);
            $followNumColorArr = $this->getColor($follow_num_rate);
            $newNumColorArr = $this->getColor($new_num_rate);

            $yx_rate = $totalcallnum > 0 ? floatval(sprintf('%.1f', 100 * $yxnum/$totalcallnum)) : 0;
            $yx_rate > 100 && $yx_rate = 100;

            $price_rate = $v['deal_num'] > 0 ? floatval(sprintf('%.1f', 100*$pricenum/$v['deal_num'])) : 0;
            $price_rate > 100 && $price_rate = 100;

            $rt[$k] = array(
                'uid' => $v['uid'],
                'name' => $v['name'],
                'photo' => checkpic($v['photo']),
                'target_call_num' => $v['call_num'],
                'target_follow_num' => $v['follow_num'],
                'target_tuoxin_num' => $v['tuoxin_num'],
                'target_deal_num' => $v['deal_num'],
                'yx_call_num' => $yxnum,
                'yx_rate' => $yx_rate,
                'total_call_num' => $totalcallnum,
                'call_num_rate' => $call_num_rate,
                'call_num_color' => $callNumColorArr['color'],
                'follow_num' => $follownum,
                'follow_num_rate' => $follow_num_rate,
                'follow_num_color' => $followNumColorArr['color'],
                'new_num' => $newnum,
                'new_num_rate' => $new_num_rate,
                'new_num_color' => $newNumColorArr['color'],
                'avg_rate' => floatval(sprintf('%.1f', $avgRate)),
                'avg_rate_color_val' => $avgRateColorArr['val'],
                'price_num' => floatval($pricenum) > 0 ? str_replace('.00', '', number_format($pricenum, 2)) : 0,
                'price_num_rate' => $price_rate,
                'job_num' => $jobnumArr[$v['uid']],
                'logintime' => $crmLoss[$v['uid']][0]['time'] ? date('H:i:s', $crmLoss[$v['uid']][0]['time']) : '-',
                'hot_index' => $crmLoss[$v['uid']] ? count($crmLoss[$v['uid']]) : 0,
                'kx_index' => $kxdata[$v['uid']] ? $kxdata[$v['uid']]['kx_index'] : $defKxIdx,
                'kx_dis' => $kxdata[$v['uid']] ? $kxdata[$v['uid']]['dis'] : $defKxDis,
                'kx_range' => $kxdata[$v['uid']] ? $kxdata[$v['uid']]['range'] : $defKxRange,
            );
        }
        // 根据平均进度增长率倒序排序
        usort($rt, function($prev, $next){
            if ($prev['avg_rate'] == $next['avg_rate']) {
                return 0;
            } else {
                return $next['avg_rate'] > $prev['avg_rate'] ? 1 : -1;
            }
        });
        return $rt;
    }

    //数据看板效率报表占比颜色
    private function getColor($rate){
        if ($rate > 90) {
            $color = 'rate_green';
            $colorVal = '#53C590';
        } else if ($rate >= 60 && $rate < 90) {
            $color = 'rate_orange';
            $colorVal = '#FBBA61';
        } else {
            $color = 'rate_red';
            $colorVal = '#EC5F5F';
        }
        return array('color' => $color, 'val' => $colorVal);
    }

    /*
     * 获取跟进统计信息
     */
    private function getFollowTj($uids, $start, $end){
        $follownumArr = array();
        foreach ($uids as $uid) {
            $followWhere = array(
                'auid' => $uid,
                'PHPYUNBTWSTART_A' =>  '',
                'atime' => array(
                    0 => array('>=', $start, ''),
                    1 => array('<=', $end, '')
                ),
                'PHPYUNBTWEND_A' =>  '',
            );
            $gjdata = $this->select_all('crmnew_concern', $followWhere, '`uid`');
            $follownumArr[$uid] = array('crm_uid' => $uid, 'num' => count(array_unique(array_column($gjdata, 'uid'))));
        }
        return $follownumArr;
    }

    /*
     * 获取外呼通话统计信息
     */
    private function getOutCallTj($uids, $start, $end){
        $callWhere = array(
            'uid' => array('in', pylode(',', $uids)),
            'PHPYUNBTWSTART_A' =>  '',
            'ctime' => array(
                0 => array('>=', $start, ''),
                1 => array('<=', $end, '')
            ),
            'PHPYUNBTWEND_A' =>  '',
            'groupby' => 'uid'
        );
        // 超过10秒的记为有效通话
        $callnum = $this->select_all('staff_call_log', $callWhere, '`uid`,sum(if(`time_length` > 10, ceil(`time_length`/60), 0)) as `yx_minute`,sum(if(`time_length` > 0, ceil(`time_length`/60), 0)) as `total_minute`,sum(if(`time_length` > 10, 1, 0)) as `yx_num`,count(*) as num');
        $callnumArr = array();
        foreach ($callnum as $v){
            $callnumArr[$v['uid']] = $v;
        }
        return $callnumArr;
    }

    /*
     * 获取拓新统计信息
     */
    private function getTxTj($uids, $start, $end){
        $newWhere = array(
            'auid' => array('in', pylode(',', $uids)),
            'type' => 1,// 录入客户
            'PHPYUNBTWSTART_A' =>  '',
            'ctime' => array(
                0 => array('>=', $start, ''),
                1 => array('<=', $end, '')
            ),
            'PHPYUNBTWEND_A' =>  '',
            'groupby' => 'auid'
        );
        $newnum = $this->select_all('crm_log', $newWhere, '`auid`,count(*) as num');
        $newnumArr = array();
        foreach ($newnum as $v) {
            $newnumArr[$v['auid']] = $v;
        }
        return $newnumArr;
    }

    private function getJobTj($uids, $start, $end){
        $cuids = array();
        $crmuid2cuids = array();
        $cuid2crmuid = array();
        foreach ($uids as $crmuid) {
            $companyList = $this->select_all('company', array('crm_uid' => $crmuid), '`uid`');
            $crmuid2cuids[$crmuid] = array_column($companyList, 'uid');
            foreach ($crmuid2cuids[$crmuid] as $vv) {
                $cuid2crmuid[$vv] = $crmuid;
                !in_array($vv, $cuids) && array_push($cuids, $vv);
            }
        }
        $jobWhere = array(
            'uid' => array('in', pylode(',', $cuids)),
            'PHPYUNBTWSTART_A' =>  '',
            'sdate' => array(
                0 => array('>=', $start, ''),
                1 => array('<=', $end, '')
            ),
            'PHPYUNBTWEND_A' =>  '',
        );
        $jobs = $this->select_all('company_job', $jobWhere, '`id`,`uid`');
        $jobnumArr = array();
        foreach ($uids as $v) {
            $jobnumArr[$v] = 0;
        }
        foreach ($jobs as $v) {
            $jobnumArr[$cuid2crmuid[$v['uid']]]++;
        }
        return $jobnumArr;
    }


    /*
     * 业务员业务报表
     */
    function getCrmUserYwData($start, $end)
    {
        $crmAdminType = $this->config['crm_admin_type'] ? explode(',', $this->config['crm_admin_type']) : [];
        if ($crmAdminType)  {
            $adminWhere['m_id'] = array('in', pylode(',', $crmAdminType));
        }
        $adminWhere['is_crm'] = 1;
        $adminWhere['groupby'] = 'uid';
        $users = $this->select_all('admin_user', $adminWhere,'`uid`,`name`,`num`');
        $uids = array();
        foreach ($users as $v) {
            $uids[] = $v['uid'];
        }
        //  客户总数
        $khnumWhere = array(
            'crm_uid' => array('in', pylode(',', $uids)),
            'groupby' => 'crm_uid'
        );
        $khnum = $this->select_all('company', $khnumWhere, '`crm_uid`,count(*) as num');
        $khnumArr = array();
        foreach ($khnum as $v){
            $khnumArr[$v['crm_uid']] = $v;
        }
        // 成交客户（crm设置中 成交会员 配置的套餐类型）
        $cjnumWhere = array(
            'crm_uid' => array('in', pylode(',', $uids)),
            'rating' => array('in', $this->config['crm_deal_rating']),
            'PHPYUNBTWSTART_A' => '',
            'vipetime' => array(
                '0' => array('=', '0', ''),
                '1' => array('>', strtotime('today'), 'OR')
            ),
            'PHPYUNBTWEND_A' => '',
            'groupby' => 'crm_uid'
        );
        $cjnum = $this->select_all('company', $cjnumWhere, '`crm_uid`,count(*) as num');
        $cjnumArr = array();
        foreach ($cjnum as $v){
            $cjnumArr[$v['crm_uid']] = $v;
        }
        // 暂停客户
        $ztnumWhere = array(
            'crm_uid' => array('in', pylode(',', $uids)),
            'r_status' => 4,
            'groupby' => 'crm_uid'
        );
        $ztnum = $this->select_all('company', $ztnumWhere, '`crm_uid`,count(*) as num');
        $ztnumArr = array();
        foreach ($ztnum as $v){
            $ztnumArr[$v['crm_uid']] = $v;
        }
        // 到期客户
        $dqnumWhere = array(
            'crm_uid' => array('in', pylode(',', $uids)),
            'PHPYUNBTWSTART_A' => '',
            'vipetime' => array(
                '0' => array('>', '0', ''),
                '1' => array('<', strtotime('today'), '')
            ),
            'PHPYUNBTWEND_A' => '',
            'groupby' => 'crm_uid'
        );
        $dqnum = $this->select_all('company', $dqnumWhere, '`crm_uid`,count(*) as num');
        $dqnumArr = array();
        foreach ($dqnum as $v){
            $dqnumArr[$v['crm_uid']] = $v;
        }
        // 体验客户（crm设置中 体验会员 配置的套餐类型）
        $tynumWhere = array(
            'crm_uid' => array('in', pylode(',', $uids)),
            'rating' => array('in', $this->config['sy_free_id']),
            'PHPYUNBTWSTART_B' => '',
            'vipetime' => array(
                '0' => array('=', '0', ''),
                '1' => array('>', strtotime('today'), 'OR')
            ),
            'PHPYUNBTWEND_B' => '',
            'groupby' => 'crm_uid'
        );
        $tynum = $this->select_all('company', $tynumWhere, '`crm_uid`,count(*) as num');
        $tynumArr = array();
        foreach ($tynum as $v){
            $tynumArr[$v['crm_uid']] = $v;
        }
        // 本月跟进
        $follownumArr = $this->getFollowTj($uids, $start, $end);
        // 本月 通话量、有效通话
        $callnumArr = $this->getOutCallTj($uids, $start, $end);
        // 拓新
        $newnumArr = $this->getTxTj($uids, $start, $end);
        // 本月新增
        $addWhere = array(
            'crm_uid' => array('in', pylode(',', $uids)),
            'PHPYUNBTWSTART_A' =>  '',
            'crm_time' => array(
                0 => array('>=', $start, ''),
                1 => array('<=', $end, '')
            ),
            'PHPYUNBTWEND_A' =>  '',
            'groupby' => 'crm_uid'
        );
        $addNum = $this->select_all('company', $addWhere, '`crm_uid`,count(*) as `num`');
        $addnumArr = array();
        foreach ($addNum as $v){
            $addnumArr[$v['crm_uid']] = $v;
        }
        // 新增岗位
        $jobnumArr = $this->getJobTj($uids, $start, $end);
        $rt = array();
        foreach ($users as $k => $v) {
            $rt[$k] = array(
                'uid' => $v['uid'],
                'name' => $v['name'],
                'kh_num' => $khnumArr[$v['uid']]['num'] ? $khnumArr[$v['uid']]['num'] : 0,
                'cj_num' => $cjnumArr[$v['uid']]['num'] ? $cjnumArr[$v['uid']]['num'] : 0,
                'zt_num' => $ztnumArr[$v['uid']]['num'] ? $ztnumArr[$v['uid']]['num'] : 0,
                'dq_num' => $dqnumArr[$v['uid']]['num'] ? $dqnumArr[$v['uid']]['num'] : 0,
                'ty_num' => $tynumArr[$v['uid']]['num'] ? $tynumArr[$v['uid']]['num'] : 0,
                'follow_num' => $follownumArr[$v['uid']]['num'] ? $follownumArr[$v['uid']]['num'] : 0,
                'yx_call_minutes' => $callnumArr[$v['uid']]['yx_minute'] ? $callnumArr[$v['uid']]['yx_minute'] : 0,// 本月有效通话时长
                'total_call_minutes' => $callnumArr[$v['uid']]['total_minute'] ? $callnumArr[$v['uid']]['total_minute'] : 0,// 本月总通话时长
                'add_num' => $addnumArr[$v['uid']]['num'] ? $addnumArr[$v['uid']]['num'] : 0,// 本月新增
                'new_num' => $newnumArr[$v['uid']]['num'] ? $newnumArr[$v['uid']]['num'] : 0,// 拓新量
                'job_num' => $jobnumArr[$v['uid']],// 新增岗位
            );
            if ($this->config['sy_crm_deal_open'] == 1) {// 排除成交客户
                $rt[$k]['sy_num'] = $v['num'] > 0 ? $v['num'] - ($khnumArr[$v['uid']]['num'] - $cjnumArr[$v['uid']]['num']) : '不限';
            } else {
                $rt[$k]['sy_num'] = $v['num'] > 0 ? $v['num'] - $khnumArr[$v['uid']]['num'] : '不限';
            }
        }
        return $rt;
    }

    /*
     * 根据时分返回整十时间格式字符串
     */
    private function getTimeStrByHM($hour, $minute){
        if ($minute <= 10) {
            return $hour . ':10';
        } else if ($minute > 10 && $minute <= 20) {
            return $hour . ':20';
        } else if ($minute > 20 && $minute <= 30) {
            return $hour . ':30';
        } else if ($minute > 30 && $minute <= 40) {
            return $hour . ':40';
        } else if ($minute > 40 && $minute <= 50) {
            return $hour . ':50';
        } else {
            $hourStr = $hour + 1 >= 10 ? $hour + 1 : '0' . ($hour + 1);
            return $hourStr . ':00';
        }
    }

    /*
     * 业务员效率报表-轨迹曲线图
     */
    function getCrmUserGjChatData($uid, $day)
    {
        $startHour = 7;
        $endHour = 22;
        $startMinute = '00';
        $endMinute = '00';
        // crm设置 工作时间
        if ($this->config['crm_work_time']) {
            $worktime = explode('~', $this->config['crm_work_time']);
            $startTime = explode(':', $worktime[0]);
            $endTime = explode(':', $worktime[1]);
            $startHour = intval($startTime[0]);
            $endHour = intval($endTime[0]);
            $startMinute = $startTime[1];
            $endMinute = $endTime[1];
        }
        $startHourStr = $startHour >= 10 ? $startHour : '0'.$startHour;
        $endHourStr = $endHour >= 10 ? $endHour : '0'.$endHour;
        $start = strtotime($day . ' ' . $startHourStr . ':' . $startMinute . ':00');// 计算传入日期的开始时间戳
        $end = strtotime($day . ' ' . $endHourStr . ':' . $endMinute . ':00');// 计算传入日期的结束时间戳
        // 图表x轴信息
        $x_arr = array();
        for($h = $startHour; $h < $endHour; $h++) {
            if ($h == $startHour) {
                for($m = $startMinute + (10 - $startMinute%10); $m < 60; $m += 10){
                    $x_arr[] = ($h < 10 ? '0' . $h : $h) . ':' . $m;
                }
            } else {
                for($m = 10; $m < 60; $m += 10){
                    $x_arr[] = ($h < 10 ? '0' . $h : $h) . ':' . $m;
                }
            }
            $x_arr[] = ($h + 1 < 10 ? '0' . ($h + 1) : $h + 1) . ':00';
        }
        array_unshift($x_arr, $startHourStr . ':' . $startMinute);
        $data = array();
        // 初始化各时段初始值
        foreach($x_arr as $v) {
            $data[$v] = 0;
        }
        $logWhere = array(
            'uid' => $uid,
            'PHPYUNBTWSTART_A' =>  '',
            'time' => array(
                0 => array('>=', $start, ''),
                1 => array('<=', $end, '')
            ),
            'PHPYUNBTWEND_A' =>  '',
            'orderby' => 'time,asc'
        );
        // 获取所有业务员行为日志
        $logs = $this->select_all('admin_web_log', $logWhere, '`time`');
        foreach ($logs as $v) {
            $hour = date('H', $v['time']);
            $minute = date('i', $v['time']);
            $data[$this->getTimeStrByHM($hour, $minute)]++;
        }
        return $data;
    }

    // 管理员行为轨迹列表
    public function getGjLineData( $whereData ,$data = array()) {
        $select = isset($data['field']) ? $data['field'] : '*';
        $List = $this->select_all('admin_web_log', $whereData, $select);
        if (!empty($List)) {
            include(PLUS_PATH.'route_admin.php');
//            /* @var $admindir */
//            include(PLUS_PATH."/admindir.php");
//            $dir = $admindir ? $admindir : 'admin';
            foreach ($List as $k => $v){
                if(isset($v['time'])){
                    $List[$k]['time_n']  = date('H:i:s', $v['time']) . ' (' . date('Y-m-d', $v['time']) . ')';
                }
                // 行为轨迹名称
//                $uri = str_replace('/' . $dir . '/index.php?', '', $v['uri']);
//                $uriArr = explode('&', $uri);
//                $m = str_replace('m=', '', $uriArr[0]);
//                $c = str_replace('c=', '', $uriArr[1]);
//                $a = $uriArr[2] ? str_replace('a=', '', $uriArr[2]) : 'index';
                $m = $v['m'];
                $c = $v['c'];
                $a = $v['a'] ? $v['a'] : 'index';
                if ($m == 'index') {
                    $List[$k]['name'] = $logTypeM[$m][$c]['name'] ? $logTypeM[$m][$c]['name'] : '';
                } else {
                    $List[$k]['name'] = $logTypeM[$m][$c][$a]['name'] ? $logTypeM[$m][$c][$a]['name'] : '';
                }
                $newName = $this->getPageName($v, $m, $c, $a);
                if ($newName != '') {
                    $List[$k]['name'] = $newName;
                }
            }
        }
        return $List;
    }

    /*
     * 行为轨迹名称
     */
    private function getPageName($v, $m, $c, $a){
        $params = unserialize($v['params']);
        $name = '';
        if ($m == 'crm') {
            if ($c == 'crm_my_customer' && $a == 'index') {
                if ($params['jtmodule'] == 'mykh' && $params['jttype'] == 'jxgj') {
                    $name = '查看我的客户-急需跟进列表';
                } else if ($params['jtmodule'] == 'mykh' && $params['jttype'] == 'gyx') {
                    $name = '查看我的客户-高意向用户列表';
                } else if ($params['jtmodule'] == 'mykh' && $params['jttype'] == 'hy') {
                    $name = '查看我的客户-活跃用户列表';
                } else if ($params['jtmodule'] == 'mykh' && $params['jttype'] == 'jdq') {
                    $name = '查看我的客户-将到期用户列表';
                } else if ($params['jtmodule'] == 'mykh' && $params['jttype'] == 'qz') {
                    $name = '查看我的客户-潜在用户列表';
                } else if ($params['clue'] == '2') {
                    $name = '查看我的客户-客户线索列表';
                } else {
                    $name = '查看我的客户-全部客户列表';
                }
            } else if ($c == 'crm_my_customer' && $a == 'notice') {
                $name = '查看我的客户-客户行为分析-权益提醒';
            } else if ($c == 'crm_my_customer' && $a == 'abnormal') {
                if ($params['type'] == 1) {
                    $name = '查看我的客户-客户行为分析-收到简历';
                } else if ($params['type'] == 2) {
                    $name = '查看我的客户-客户行为分析-简历未查看';
                } else if ($params['type'] == 3) {
                    $name = '查看我的客户-客户行为分析-简历下载';
                } else if ($params['type'] == 4) {
                    $name = '查看我的客户-客户行为分析-登陆次数';
                } else if ($params['type'] == 5) {
                    $name = '查看我的客户-客户行为分析-被投递岗位';
                } else if ($params['type'] == 6) {
                    $name = '查看我的客户-客户行为分析-被拨号岗位';
                } else if ($params['type'] == 7) {
                    $name = '查看我的客户-客户行为分析-被拨号企业';
                } else if ($params['type'] == 8) {
                    $name = '查看我的客户-客户行为分析-删除简历投递';
                } else if ($params['type'] == 9) {
                    $name = '查看我的客户-客户行为分析-删除简历下载';
                }
            } else if ($c == 'task' && $a == 'getTaskList') {
                if ($params['tab'] == 1) {
                    $name = '查看代办任务-我的任务';
                } else if ($params['tab'] == 2) {
                    $name = '查看代办任务-下属任务';
                }
            } else if ($c == 'followLog' && $a == 'getFollowLog') {
                if ($params['tab'] == 1) {
                    $name = '查看联系跟进-我的跟进';
                } else if ($params['tab'] == 2) {
                    $name = '查看联系跟进-下属跟进';
                }
            } else if ($c == 'crm_customer' && $a == 'index') {
                if ($params['jttype'] == 'new') {
                    $name = '查看客户公海-新客户列表';
                } else if ($params['jttype'] == 'gyx') {
                    $name = '查看客户公海-高意向客户列表';
                } else if ($params['jttype'] == 'hy') {
                    $name = '查看客户公海-活跃客户列表';
                } else if ($params['jttype'] == 'dq') {
                    $name = '查看客户公海-到期客户列表';
                } else if ($params['jttype'] == 'qz') {
                    $name = '查看客户公海-潜在客户列表';
                } else if ($params['jttype'] == 'jqsf') {
                    $name = '查看客户公海-近期释放列表';
                } else if ($params['clue'] == 1) {
                    $name = '查看客户公海-入库企业列表';
                } else if ($params['clue'] == 2) {
                    $name = '查看客户公海-线索库列表';
                }
            } else if ($c == 'crm_my_performance' && $a == 'index') {
                $name = '查看我的业绩';
            } else if ($c == 'crm_my_performance' && $a == 'concern') {
                $name = '查看我的业绩-跟进记录统计';
            } else if ($c == 'crm_my_performance' && $a == 'amount') {
                $name = '查看我的业绩-成交金额统计';
            } else if ($c == 'workLog' && $a == 'getWorkLog') {
                $name = '查看工作日志列表';
            } else if ($c == 'outLog' && $a == 'getOutLog') {
                $name = '查看外出申请列表';
            } else if ($c == 'crm_deal' && $a == 'index') {
                if ($params['tab'] == 1) {
                    $name = '查看订单记录-我的订单列表';
                } else if ($params['tab'] == 2) {
                    $name = '查看订单记录-下属订单列表';
                }
            } else if ($c == 'crm_chat' && $a == 'index') {
                $name = '查看客勤-拥有简历列表';
            } else if ($c == 'crm_chat' && $a == 'noresume') {
                $name = '查看客勤-没有简历列表';
            } else if ($c == 'crm_chat' && $a == 'phoneAuthList') {
                $name = '查看客勤-手机号授权列表';
            } else if ($c == 'crm_chat' && $a == 'inviteList') {
                $name = '查看客勤-面试回访列表';
            } else if ($c == 'crm_chat' && $a == 'follow') {
                $name = '查看客勤-跟进记录列表';
            }
            if ($params['source'] == 'jobtai') {
                $name = '工作台-' . $name;
            }
        }
        return $name;
    }

    /*
     * 业务员效率报表-详情图表 最多访问
     */
    function getCrmUserGjCntData($uid, $day)
    {
        $startHour = 7;
        $endHour = 22;
        $startHourStr = $startHour >= 10 ? $startHour : '0'.$startHour;
        $endHourStr = $endHour >= 10 ? $endHour : '0'.$endHour;
        $start = strtotime($day . ' ' . $startHourStr . ':00:00');// 计算传入日期的开始时间戳
        $end = strtotime($day . ' ' . $endHourStr . ':00:00');// 计算传入日期的结束时间戳
        $logWhere = array(
            'uid' => $uid,
            'PHPYUNBTWSTART_A' =>  '',
            'time' => array(
                0 => array('>=', $start, ''),
                1 => array('<=', $end, '')
            ),
            'PHPYUNBTWEND_A' =>  '',
            'groupby' => 'uri',
            'orderby' => 'num,desc',
            'limit' => 10
        );
        // 获取业务员行为轨迹次数前10名
        $logs = $this->select_all('admin_web_log', $logWhere, '`m`,`c`,`a`,count(*) as num');
        include(PLUS_PATH.'route_admin.php');
//        /* @var $admindir */
//        include(PLUS_PATH."/admindir.php");
//        $dir = $admindir ? $admindir : 'admin';
        foreach ($logs as $k => $v) {
//            $uri = str_replace('/' . $dir . '/index.php?', '', $v['uri']);
//            $uriArr = explode('&', $uri);
//            $m = str_replace('m=', '', $uriArr[0]);
//            $c = str_replace('c=', '', $uriArr[1]);
//            $a = $uriArr[2] ? str_replace('a=', '', $uriArr[2]) : 'index';
            $m = $v['m'];
            $c = $v['c'];
            $a = $v['a'] ? $v['a'] : 'index';
            if ($m == 'index') {
                $logs[$k]['name'] = $logTypeM[$m][$c]['name'] ? $logTypeM[$m][$c]['name'] : '';
            } else {
                $logs[$k]['name'] = $logTypeM[$m][$c][$a]['name'] ? $logTypeM[$m][$c][$a]['name'] : '';
            }
            $newName = $this->getPageName($v, $m, $c, $a);
            if ($newName != '') {
                $logs[$k]['name'] = $newName;
            }
        }
        return $logs;
    }

    /*
     * 获取空闲指数及最大空闲区段
     * $logs 单个业务员的行为记录数组
     * $start 开始时间
     * $end 结束时间
     * $resttime 休息时间段（时间戳数组）
     */
    private function getKxIndexAndMaxRange($logs, $start, $end, $resttime = array()){
        $kxindex = 0;
        $range = '';
        $dis = 600;// 超过10分钟算空闲
        $lasttime = 0;

        foreach ($logs as $k => $log) {
            // 计算最大空闲时段
            if ($k == 0) {
                if ($resttime && $log['time'] > $resttime[1]) {// 首条记录在午休结束时间之后
                    $dis = $resttime[0] - $start;// 上午空闲时长
                    // 记录时间距午休结束时间超过超过上午工作时长,记录新的空闲时长
                    if ($log['time'] - $resttime[1] > $dis) {
                        $dis = $log['time'] - $resttime[1];
                        $range = date('H:i', $resttime[1]) . '-' . date('H:i', $log['time']);
                    } else {
                        $range = date('H:i', $start) . '-' . date('H:i', $resttime[0]);
                    }
                } else {
                    if ($log['time'] - $start > 600) {
                        $dis = $log['time'] - $start ;
                        $range = date('H:i', $start) . '-' . date('H:i', $log['time']);
                    }
                }
            } else {
                $compareTime = $logs[$k - 1]['time'];// 默认取上一条记录的时间作为当前记录空闲时长的比较基数
                $biggerTime = $log['time'];
                if ($resttime) {// 有午休时间段
                    // 当前记录和上一条记录中间包含午休时间段
                    if ($log['time'] > $resttime[1] && $logs[$k - 1]['time'] < $resttime[0]) {
                        $morningDis = $resttime[0] - $logs[$k - 1]['time'];
                        $afternoonDis = $log['time'] - $resttime[1];
                        if ($morningDis > $afternoonDis) {
                            $compareTime = $logs[$k - 1]['time'];
                            $biggerTime = $resttime[0];
                        } else {
                            $compareTime = $resttime[1];
                        }
                    }
                }
                if ($biggerTime - $compareTime > $dis) {
                    $dis = $biggerTime - $compareTime;
                    $range = date('H:i', $compareTime) . '-' . date('H:i', $biggerTime);
                }
            }
            // 计算空闲指数
            if ($lasttime == 0) {
                $newCompareTime = $start;
                if ($resttime && $log['time']  > $resttime[1]) {// 首条记录在午休结束后
                    $newCompareTime = $resttime[1];
                    $kxindex = floor(($resttime[1] - $start)/600);// 开始上班时间至午休前的工作时间全部算作上午的空闲指数
                }
                if ($log['time'] - $newCompareTime > 600) {// 距开始时间超过10分钟
                    $kxindex += floor(($log['time'] - $newCompareTime)/600);
                }
                $lasttime = $newCompareTime + ceil(($log['time'] - $newCompareTime)/600) * 600;
            } else {
                if ($log['time'] < $lasttime) {// 上一个统计截止时间之前的记录直接跳过
                    continue;
                } else {
                    if ($log['time'] - $lasttime > 600) {// 距上次查询时间节点超过10分钟
                        $kxindex += floor(($log['time'] - $lasttime)/600);
                    }
                    $lasttime += ceil(($log['time'] - $lasttime)/600) * 600;
                    if ($resttime[0] == $lasttime ) {// 空闲基准时间 等于午休开始时间，空闲基准时间调整为午休结束时间
                        $lasttime = $resttime[1];
                    }
                }
            }
            if ($lasttime >= $end) {
                break;
            }
        }
        // 防止因为业务员登陆后一直没有其他操作导致最大空闲获取失败
        if ($range == '') {
            $cnt = count($logs);
            $now = time();
            $endtime = $now;
            if ($now < $start) {// 当前未到开始上班时间
                $endtime = $start;
            } else {
                if ($resttime) {// 有午休时间段
                    if ($now > $resttime[0] && $now < $resttime[1]) {// 当前时间在午休时间
                        $endtime = $resttime[0];
                    }
                }
                if ($now > $end) {// 当前时间已过上班结束时间
                    $endtime = $end;
                }
            }
            if ($endtime - $logs[$cnt - 1]['time'] > 600) {// 结束时间距最后一条行为记录时间超过10分钟
                $dis = $endtime - $logs[$cnt - 1]['time'];
                $range = date('H:i', $logs[$cnt - 1]['time']) . '-' . date('H:i', $endtime);
            }
        }
        return array(
            'kx_index' => $kxindex,
            'dis' => $dis > 600 ? sprintf('%.1f', $dis/3600) : '',
            'range' => $range
        );
    }

    function getStatisData($data = array())
    {

        $timeBegin  =   $data['timeBegin'];
        $timeEnd    =   $data['timeEnd'];

        if (!empty($data['timeBegin'])){
            $allWhere   =   array(
                'crm_uid'       => array('>', 0),
                'PHPYUNBTWSTART_A' =>  '',
                'crm_time'      =>  array(
                    0   =>  array('>=', $timeBegin, ''),
                    1   =>  array('<=', $timeEnd, '')
                ),
                'PHPYUNBTWEND_A'   =>  ''
            );
        }else{
            $allWhere   =   array('crm_uid' => array('>', 0));
        }
        $allNum     =   $this->select_num('company', $allWhere);

        if (!empty($data['timeBegin'])) {
            $concernWhere = array(
                'crm_uid' => array('>', 0),
                'isfollow' => '0'
            );
        }else{
            $concernWhere   =   array('crm_uid' => array('>', 0), 'isfollow' => '0');
        }

        $concernNum =   $this->select_num('company', $concernWhere);

        if (!empty($data['timeBegin'])) {
            $concernedWhere = array(
                'crm_uid' => array('>', 0),
                'isfollow' => '1',
                'PHPYUNBTWSTART_A' => '',
                'f_time' => array(
                    0 => array('>=', $timeBegin, ''),
                    1 => array('<=', $timeEnd, '')
                ),
                'PHPYUNBTWEND_A' => ''
            );
        }else{
            $concernedWhere =   array('crm_uid' => array('>', 0), 'isfollow' => '1');
        }
        $concernedNum =   $this->select_num('company', $concernedWhere);

        if (!empty($timeBegin)) {

            $endT           =   mktime(23, 59, 59, date('m'), date('d'), date('Y'));
            if ($timeBegin == $endT || $timeEnd <= $endT){

                $vitWhere   =   array(
                    'type'      =>  22,
                    'status'    =>  1,
                    'PHPYUNBTWSTART_A' => '',
                    'stime' => array(
                        0 => array('<=', $timeEnd, ''),
                        1 => array('isnull','','OR')
                    ),
                    'PHPYUNBTWEND_A' => ''
                );
            }else{

                $vitWhere   =   array(
                    'type'      =>  22,
                    'status'    =>  1,
                    'PHPYUNBTWSTART_A' => '',
                    'stime' => array(
                        0 => array('>=', $timeBegin, ''),
                        1 => array('<=', $timeEnd, '')
                    ),
                    'PHPYUNBTWEND_A' => ''
                );
            }
        }else{

            $vitWhere =   array('type' => 22, 'status' => 1);
        }
        $visitNum   =   $this->select_num('crm_work_plan', $vitWhere);

        if (!empty($timeBegin)) {
            $orderWhere = array(
                'crm_uid' => array('>', 0),
                'order_state' => 2,
                'PHPYUNBTWSTART_A' => '',
                'order_time' => array(
                    0 => array('>=', $timeBegin, ''),
                    1 => array('<=', $timeEnd, '')
                ),
                'PHPYUNBTWEND_A' => ''
            );
        }else{
            $orderWhere =   array( 'crm_uid' => array('>', 0), 'order_state' => 2);
        }
        $orderNum   =   $this->select_num('company_order', $orderWhere);

        $orderPrice =   $this->select_once('company_order', $orderWhere, 'sum(`order_price`) as `pricesum`');

        $arr        =   array(
            'allNum'        =>  $allNum,
            'concernNum'    =>  $concernNum,
            'concernedNum'  =>  $concernedNum,
            'visitNum'      =>  $visitNum,
            'orderNum'      =>  $orderNum,
            'orderPrice'    =>  $orderPrice['pricesum']
        );

        return  $arr;
    }

    /**
     * @desc 检查客户管理权限
     * @param array $data
     * @return array
     */
    public function isManaged($data = array())
    {

        $return =   array();

        $uid    =   $data['uid'];
        $auid   =   $data['auid'];

        $com    =   $this->select_once('company', array('uid' => $uid), 'crm_uid');

        if ($com['crm_uid'] == $auid || $this->config['sy_crm_link'] == 1) {

            $return['code'] =   1;  //  自己的客户有管理权限
        } else {

            $crmUser    =   $this->select_once('admin_user', array('uid' => $com['crm_uid']), '`uid`, `org`, `power`, `spower`');

            if (!empty($crmUser['org'])) {

                $auser  =   $this->select_once('admin_user', array('uid' => $auid), '`uid`, `org`, `power`, `spower`');

                if (!empty($auser['org'])) {

                    if ($auser['org'] == $crmUser['org']) {

                        if ($auser['power'] == 1) {

                            $return['code'] =   1;          //  同级组织架构有部门权限，可以管理
                        }else{

                            $return['code'] =   2;          //  同级组织架构无部门权限，不可以管理
                        }
                    } else if ($auser['spower'] == 1) {     //  有子部门管理权限

                        $crmOrgInfo =   $this->select_once('crmorg', array('id' => $crmUser['org']));
                        $aOrgInfo   =   $this->select_once('crmorg', array('id' => $auser['org']));

                        if ($aOrgInfo['level'] > $crmOrgInfo['level']){

                            $return['code'] =   2;          //  当前客户业务员所有较高级组织架构，无权限管理
                        }else if ($aOrgInfo['level'] == 1){

                            $sOrgList   =   $this->select_all('crmorg', array('fid' => $aOrgInfo['id']));

                            $sids       =   array();
                            foreach ($sOrgList as $k => $v) {
                                $sids[] =   $v['id'];
                            }

                            if ($crmOrgInfo['level'] == 2){

                                if (in_array($crmOrgInfo['id'], $sids)) {

                                    $return['code'] =   1;      //  上级部门权限，可管理
                                } else {

                                    $return['code'] =   2;      //  无上级组织联系，不可管理
                                }
                            }elseif ($crmOrgInfo['level'] == 3){

                                if (in_array($crmOrgInfo['fid'], $sids)) {

                                    $return['code'] =   1;      //  上级部门权限，可管理
                                } else {

                                    $return['code'] =   2;      //  无上级组织联系，不可管理
                                }
                            }
                        }else if ($aOrgInfo['level'] == 2){

                            if ($crmOrgInfo['fid'] == $aOrgInfo['id']){

                                $return['code'] =   1;      //  直属上下级，有权限管理
                            }else{

                                $return['code'] =   2;      //  无上下级组织关系，无权限管理
                            }
                        }
                    } else {

                        $return['code'] =   2;              //  无下级部门管理权限
                    }
                } else {

                    $return['code'] =   2;                  //  无上下级组织架构，无权管理
                }
            } else {

                $return['code'] =   2;                      //  无上下级组织架构，无权管理
            }
        }

        return $return;
    }

    /**
     * @var string[] |
     */
    public $TypeArr =   array('1' => '职位上架', '2' => '职位刷新', '3' => '下载简历', '4' => '邀请面试', '5' => '职位置顶', '6' => '职位推荐', '7' => '紧急招聘', '8' => '聊天沟通');

    /**
     * @desc 权益提醒
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getNoticeList($where = array(), $data = array())
    {

        $field  =   $data['field'] ? $data['field'] : '*';
        $List   =   $this->select_all('rating_notice', $where, $field);

        $uidArr =   array();
        $cuids  =   array();
        foreach ($List as $k => $v){
            if (!in_array($v['uid'], $uidArr)){

                $uidArr[]   =   $v['uid'];
            }
            if (!in_array($v['crm_uid'], $cuids)){

                $cuids[]    =   $v['crm_uid'];
            }
        }

        if (!empty($cuids)){
            $crmUsers       =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $cuids))), '`uid`, `name`');
            $auserArr       =   array();
            foreach ($crmUsers as $cak=>$cav){
                $auserArr[$cav['uid']]  =   $cav['name'];
            }
        }

        $comList        =   $this->select_all('company', array('uid' => array('in', pylode(',', $uidArr))), '`uid`,`name`,`crm_status`,`crm_type`');

        $concernArr     =   $this->select_all('crmnew_concern', array('uid' => array('in', pylode(',', $uidArr)),'groupby' => 'uid'), 'MAX(`id`) as `id`');
        $concernIdArr   =   array();
        foreach ($concernArr as $ck => $cv) {

            $concernIdArr[] =   $cv['id'];
        }

        $concernList    =   $this->select_all('crmnew_concern', array('id' => array('in', pylode(',', $concernIdArr))), '`id`, `uid`, `atime`, `content`, `type`, `auid`');
        $concernPicList =   $this->select_all('crmnew_concern_pic', array('fid' => array('in', pylode(',', $concernIdArr))));

        if (!empty($concernPicList)){
            foreach ($concernList as $conk => $conv){
                foreach ($concernPicList as $conpk => $conpv) {
                    if ($conv['id'] == $conpv['fid']){

                        $concernList[$conk]['pic_a'][]  =   checkpic($conpv['picurl']);
                    }
                }
            }
        }

        $options=   array('crm');
        $cache  =   $this->getCache($options);

        $idArr  =   array();

        foreach ($List as $k => $v){

            foreach ($comList as $ck => $cv){

                if ($v['uid'] == $cv['uid']){

                    $List[$k]['com_name']   =   $cv['name'];
                    $List[$k]['crm_type']   =   $cv['crm_type'];
                    $List[$k]['crm_status'] =   $cv['crm_status'];
                }
            }

            $List[$k]['crmName']        =   $auserArr[$v['crm_uid']];

            $List[$k]['follow_time']    =   '--';

            if (!empty($concernList)){

                foreach ($concernList as $conk => $conv) {

                    if ($v['crm_uid'] == $conv['auid'] && $v['uid'] == $conv['uid']){

                        $List[$k]['follow_time']    =   date('Y-m-d H:i', $conv['atime']);
                        $List[$k]['c_desc']         =   strip_tags($conv['content']);
                        $List[$k]['c_type']         =   $cache['crmclass_name'][$conv['type']];
                        $List[$k]['c_name']         =   $auserArr[$conv['auid']];
                        if (!empty($conv['pic_a'])) {
                            $List[$k]['c_pic_arr']  =   implode('&', $conv['pic_a']);
                        }

                        if ($conv['atime'] > $v['send_time'] && $v['status'] == 1){

                            $v['status']=   2;
                            $idArr[]    =   $v['id'];
                        }
                    }
                }
            }

            $List[$k]['type_n']         =   $this->TypeArr[$v['type']].'套餐不足';
            $List[$k]['status_n']       =   $v['status'] == 1 ? '尚未跟进' : '已跟进';
            $List[$k]['send_time_n']    =   date('Y-m-d H:i', $v['send_time']);
        }

        if (!empty($idArr)){

            $this->update_once('rating_notice', array('status' => 2), array('id' => array('in', pylode(',', $idArr))));
        }

        return  $List;
    }

    public function getNoticeNum($where = array())
    {

        $allNum     =   $this->select_num('rating_notice', $where);

        $where['status']    =   1;
        $statusNum  =   $this->select_num('rating_notice', $where);

        $arr        =   array(
            'ajaxAll'   =>  $allNum,
            'ajaxStatus'=>  $statusNum
        );

        return $arr;
    }

    /**
     * @desc 删除权益提醒
     * @param array $where
     * @return mixed
     */
    public function delNotice($where = array())
    {	$return['layertype']    =   0;
		if (!empty($where)) {
			if (!empty($where['id']) && $where['id'][0] == 'in') {
                $return['layertype']    =   1;
            }
			$nid    =   $this->delete_all('rating_notice', $where, '');
		}
        $return['errmsg']   =   '权益提醒';
        $return['errcode']  =   $nid ? 9 : 8;
        $return['errmsg']   =  $nid ? '删除成功！' : '删除失败！';
		return $return;
    }

    /**
     * 根据查询字段判断，需要引用缓存文件，再引用
     * @param string $field     待查询字段
     * @param array $cacheField 需引用缓存的字段
     * @param array $need       需要引用的缓存
     * @return array
     */
    function needCache($field = '', $cacheField = array(), $need = array()){

        $cache     = array();
        $needCache = false;

        if ($field == '*'){
            $needCache = true;
        }else{
            foreach ($cacheField as $v){
                if (stripos($field, $v) !== false){
                    $needCache = true;
                }
            }
        }
        if ($needCache){

            include_once ('cache.model.php');
            $cacheM  =  new cache_model($this->db, $this->def);
            $cache   =  $cacheM -> GetCache($need);
        }
        return $cache;
    }

    function wxBindState($wxBind)
    {

        if (!empty($wxBind['wxid'])) {
            if (!empty($wxBind['unionid'])) {
                $wxGzhMsg = '公众号已绑定，微信开放平台已绑定';
            } else {
                $wxGzhMsg = '公众号已绑定';
            }
        } else {
            $wxGzhMsg = '公众号未绑定';
        }
        $wxBindMsg = $wxGzhMsg;
        if (!empty($this->config['sy_xcxappid'])) {
            if (!empty($wxBind['wxopenid'])) {
                if ($this->config['mini_wxopen'] == 1) {
                    $wxXcxMsg = '微信小程序已绑定，微信开放平台已绑定';
                } else {
                    $wxXcxMsg = '微信小程序已绑定';
                }
            } else {
                $wxXcxMsg = '微信小程序未绑定';
            }
            $wxBindMsg .= '</br>' . $wxXcxMsg;
        }
        if ($this->config['sy_app_open'] == 1) {
            if (!empty($wxBind['app_wxid'])) {
                $wxAppMsg = 'APP微信已绑定';
            } else {
                $wxAppMsg = 'APP微信未绑定';
            }
            $wxBindMsg .= '</br>' . $wxAppMsg;
        }
        return $wxBindMsg;
    }
    public function comPersona($comId = null, $data = array()){

        $now = time();
        $week_s = strtotime("-1 week");
        $month_s = strtotime("-1 month");

        $persona = array();

        $field      =   isset($data['field']) ? $data['field'] : '*';
        $Info       =   $this->select_once('company', array('uid' => $comId), $field);

        $member     =   $this->select_once('member', array('uid' => $comId), '`username`,`usertype`,`wxid`,`wxopenid`,`app_wxid`,`reg_date`');
        //总登录次数
        $loginaW = array('uid' => $comId);
        $loginaW['ctime'] = array('>=', $member['reg_date']);
        $loginAllNum = $this->select_num('login_log', $loginaW);

        //一周内登录次数
        $loginwW = array('uid' => $comId);
        $loginwW['ctime'] = array('>=', $week_s);
        $loginWeekNum = $this->select_num('login_log', $loginwW);
        //一月内登录次数
        $loginmW = array('uid' => $comId);
        $loginmW['ctime'] = array('>=', $month_s);
        $loginMonthNum = $this->select_num('login_log', $loginmW);
        //职位总数
        $joballnum  = $this->select_num('company_job', array('uid'=>$comId));
        //在招职位数
        $zpjobwhere              =   array();
        $zpjobwhere['uid']       =   $comId;
        $zpjobwhere['status']    =   0;
        $zpjobwhere['r_status']  =   1;
        $zpjobwhere['state']     =   1;
        $zpjobnum = $this -> select_num('company_job', $zpjobwhere);

        $huoyue_arr = $keyword_arr = array();

        if($loginWeekNum || $loginMonthNum){

            //简历搜索记录
            $keywordlogWhere = array('uid' => $comId,'usertype'=>'2');
            $keywordlogWhere['ctime'] = array('>=', $month_s);
            if($loginWeekNum){
                $keywordlogWhere['ctime'] = array('>=', $week_s);
            }
            $keywordlogWhere['groupby'] = 'keyword';
            $keywordlogWhere['orderby'] = array('cnt,desc');
            $keywordlogfield  = '`keyword`,count(*) as cnt';
            $keywordlogs = $this->select_all('keyword_log',$keywordlogWhere,$keywordlogfield);

            foreach ($keywordlogs as $kk => $kv) {

                $keyword_arr[]   =   $kv['keyword'];

                if($kk>=2){
                    break;
                }
            }
        }

        //活跃时间段
        $hyWhere = array('uid' => $comId);
        $hyWhere['limit'] = 500;
        $hyWhere['groupby'] = 'td';
        $hyWhere['orderby'] = array('cnt,desc');
        //SELECT FROM_UNIXTIME(time,"%H:00") as td,count(*) as cnt FROM `phpyun_web_log` WHERE `uid`='20032'  GROUP  BY td ORDER BY cnt DESC
        $field  = 'FROM_UNIXTIME(`ctime`,"%H") as td,count(*) as cnt';
        $hylogs = $this->select_all('login_log', $hyWhere,$field);
        $huoyue_data = array();
        foreach ($hylogs as $wk => $wv) {

            $huoyue_data[]   =   intval($wv['td']);

            if($wk>=2){
                break;
            }
        }

        if(!empty($huoyue_data)){
            $huoyue_arr = $this->combineHour($huoyue_data);
        }
        //收到简历
        $sqNum                  =   $this->select_num('userid_job', array('com_id' => $comId));
        //一周收到简历
        $sqWeekNum              =   $this->select_num('userid_job', array('com_id' => $comId,'datetime'=>array('>=', $week_s)));
        //未查看简历
        $unReadNum              =   $this->select_num('userid_job', array('com_id' => $comId,'is_browse' => 1));
        //邀请面试数
        $yqNum                  =   $this->select_num('userid_msg', array('fid' => $comId));
        //下载简历数
        $downNum                =   $this->select_num('down_resume', array('comid' => $comId));
        //免费下载简历数
        $freedownNum            =   $this->select_num('freedown_resume', array('comid' => $comId));
        //聊天好友数
        $chatfriendNum          =   $this->select_num('chat_friend', array('uid' => $comId));
        //跟进次数
        $followNum              =   $this->select_num('crmnew_concern', array('uid' => $comId));
        //最后一次跟进
        $lastFollow             =   $this->select_once('crmnew_concern', array('uid' => $comId, 'orderby' => 'atime, desc'), '`ftime`,`auid`');

        $persona['reg_date']        =   !empty($member)?date('Y年m月d日 H:i', $member['reg_date']):'';//注册时间
        $persona['regdays']         =   round((time()-$member['reg_date'])/3600/24);//注册至今天数
        $persona['loginAllNum']     =   $loginAllNum;//注册至今天登陆次数

        $persona['loginDNum']       =   0;
        $persona['loginDtype']      =   '月';
        if($loginWeekNum>0 || $loginMonthNum>0){
            $persona['loginDNum']   =   $loginWeekNum>0?$loginWeekNum:$loginMonthNum;
            $persona['loginDtype']  =   $loginWeekNum>0?'周':'月';
        }

        $coData = array();
        require_once ('companyorder.model.php');
        $companyorderM = new companyorder_model($this->db, $this->def);
        $crmorder_list = $companyorderM->getList(array('uid'=>$comId,'usertype'=>2,'type'=>1,'rating'=>array('>',0),'order_state'=>2,'orderby'=>'id,desc'),array('utype'=>'admin'));
        foreach ($crmorder_list as $cok => $cov) {
            $coData[] = date('Y-m-d', $cov['order_time']).' '.$cov['rating_name_n'];
            if($cok>=2){
                break;
            }
        }

        $persona['joballnum']       =   $joballnum;//所有职位数
        $persona['zpjobnum']        =   $zpjobnum;// 在招职位数
        $persona['sqNum']           =   $sqNum;// 收到简历数
        $persona['sqWeekNum']       =   $sqWeekNum;// 收到简历数
        $persona['unReadNum']       =   $unReadNum;// 简历未查看数
        $persona['readRate']        =   $sqNum>0?round(($sqNum-$unReadNum)/$sqNum*100,2).'％':'100%';//查看率
        $persona['yqNum']           =   $yqNum;// 邀请面试数
        $persona['downNum']         =   $downNum+$freedownNum;// 下载简历数
        $persona['chatfriendNum']   =   $chatfriendNum;// 聊天好友数
        $persona['followNum']       =   $followNum;// 跟进数
        $persona['lastfollowDate']  =   !empty($lastFollow)?date('Y年m月d日 H时i分',$lastFollow['ftime']):'';// 最后一次跟进时间
        $persona['huoyueData']      =   !empty($huoyue_arr)?implode('、',$huoyue_arr):'';// 活跃时间段
        $persona['keywordData']     =   !empty($keyword_arr)?implode('、',$keyword_arr):'';//最常搜索简历关键字
        $persona['coNum']           =   count($crmorder_list);//合作次数
        $persona['coData']          =   !empty($coData)?implode('、',$coData):'';//合作内容

        return $persona;
    }
    private function combineHour($a=array()){

        $return = array();

        $b = array();
        $t = array();
        sort($a);
        $lastindex_a = count($a)-1;
        foreach ($a as $k=>$v) {

            if(count($t)==1){
                if(($v-1)==$t[0]){
                    $t[] = $v;
                    if($lastindex_a==$k){
                        $b[] = $t;
                    }
                }else{
                    $t[] = $t[0]+1;
                    $b[] = $t;

                    $t = array();
                    $t[] = $v;
                    if($lastindex_a==$k){
                        $t[] = $t[0]+1;
                        $b[] = $t;
                    }
                }
            }else if(count($t)==2){
                if(($v-1)==$t[1]){
                    $t[1] = $v;
                    if($lastindex_a==$k){
                        $b[] = $t;
                    }
                }else{
                    $b[] = $t;

                    $t = array();
                    $t[] = $v;
                    if($lastindex_a==$k){
                        $t[] = $t[0]+1;
                        $b[] = $t;
                    }
                }
            }else{
                $t[] = $v;
                if($lastindex_a==$k){
                    $t[] = $t[0]+1;
                    $b[] = $t;
                }
            }

        }
        foreach ($b as $bk => $bv) {
            $return[] = $bv[0].':00-'.$bv[1].':00';
        }
        return $return;
    }
    /**
     * @desc CRM 客户管理 详情页
     * @param null $comId
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getComInfo($comId = null, $data = array())
    {

        $field      =   isset($data['field']) ? $data['field'] : '*';
        $Info       =   $this->select_once('company', array('uid' => $comId), $field);

        $crmInfo    =   $this->select_once('admin_user', array('uid' => $Info['crm_uid']), '`name`,`moblie`');

        // 查询字段中包含的，才引用缓存文件
        $cacheField =   array('crm_status','crm_type','provinceid','cityid','three_cityid','hy','pr','mun');
        $cache      =   $this->needCache($field, $cacheField, array('hy','city','com', 'crm'));

        $member     =   $this->select_once('member', array('uid' => $comId), '`username`,`usertype`,`wxid`,`wxopenid`,`app_wxid`,`reg_date`');

        if (!empty($Info)){

            $auid   =   $data['auid'];

            $Info['logo_n']         =   checkpic($Info['logo'], $this->config['sy_unit_icon']);
            $Info['username']       =   $member['username'];
            $Info['usertype']       =   $member['usertype'];

            $Info['crm_status_n']   =   $Info['crm_status'] ? $cache['crmclass_name'][$Info['crm_status']] : '未设置标签';
            $Info['crm_type_n']     =   $Info['crm_type'] ? $cache['crmclass_name'][$Info['crm_type']] : "未标记等级";

            $Info['crm_name']       =   $Info['crm_uid'] > 0 ? $crmInfo['name'] : '暂未绑定';

            $Info['crm_time_n']     =   date('Y-m-d H:i:s', $Info['crm_time']);
            $Info['follow_num']     =   $this->select_num('crmnew_concern', array('uid' => $comId));

            $lastFollow             =   $this->select_once('crmnew_concern', array('uid' => $comId, 'orderby' => 'atime, desc'), '`ftime`,`auid`');
            $followUser             =   $this->select_once('admin_user', array('uid' => $lastFollow['auid']), '`name`');
            if ($Info['f_time'] > 0){

                $Info['f_time_n']   =   date('Y-m-d H:i:s', $Info['f_time']);
                $Info['follow_user']=   $followUser['name'];
            }else{

                if (!empty($lastFollow)){

                    $Info['f_time_n']   =   date('Y-m-d H:i:s', $lastFollow['ftime']);
                    $Info['follow_user']=   $followUser['name'];
                }else{

                    $Info['f_time_n']   =   '--';
                    $Info['follow_user']=   '--';
                }
            }

            if ($Info['zt_time'] > 0) {

                $Info['zt_time_n']  =   date('Y-m-d H:i:s', $Info['zt_time']);
                $Info['zt_days']    =   intval((time() - $Info['zt_time']) / 86400);
            } else {

                $Info['zt_time_n']  =   '--';
                $Info['zt_days']    =   0;
            }

            $Info['vipstime_n']     =   date('Y-m-d', $Info['vipstime']);
            $Info['vipetime_n']     =   $Info['vipetime'] > 0 ? date('Y-m-d', $Info['vipetime']) : '永久';

            if ($Info['vipetime'] >= strtotime(date('Y-m-d'))) {

                $Info['vipDay']     =   format_datetime($Info['vipetime'], 2, 3, '');

            } elseif ($Info['vipetime'] < strtotime(date('Y-m-d')) && $Info['vipetime'] != '0') {

                $Info['vipDay']     =   format_datetime($Info['vipetime'], 2, 4, '');
            } elseif ($Info['vipetime'] == 0){

                $Info['vipDay']     =   '永久';
            }

            $Info['isVip']          =   isVip($Info['vipetime']) ? 1 : 2;

            $Info['wx_status']      =   $member['wxid'] || $member['wxopenid'] || $member['app_wxid'] ? 1 : 2;
            $Info['wxBindMsg']      =   $this->wxBindState($member);
            $Info['wxid']           =   $member['wxid'];
            $Info['wxopenid']       =   $member['wxopenid'];
            $Info['app_wxid']       =   $member['app_wxid'];

            $Info['login_day']      =   $Info['login_date'] ? date('Y-m-d', $Info['login_date']) : '近期未登录';
            $Info['reg_date']       =   date('Y-m-d', $member['reg_date']);

            //  收到简历
            $sqNum                  =   $this->select_num('userid_job', array('com_id' => $comId));
            $Info['sqNum']          =   intval($sqNum);

            //  未查看简历
            $unReadNum              =   $this->select_num('userid_job', array('com_id' => $comId, 'isdel' => 9, 'is_browse' => 1));
            $Info['unReadNum']      =   intval($unReadNum);

            //  查看/浏览简历
            $lookNum                =   $this->select_num('look_resume', array('com_id' => $comId));
            $Info['lookNum']        =   $lookNum;

            //  下载简历
            $downNum                =   $this->select_num('down_resume', array('comid' => $comId, 'isdel' => 9));
            $freedownNum            =   $this->select_num('freedown_resume', array('comid' => $comId));
            $Info['downNum']        =   intval($downNum)+intval($freedownNum);

            //  未读聊天信息
            $day                    =   !empty($this->config['sy_chat_day']) ? $this->config['sy_chat_day'] : 30;
            $time                   =   strtotime('-' . $day . ' day') * 1000;
            $unReadChatNum          =   $this->select_num('chat_log', array('to' => $comId, 'tusertype' => 2, 'status' => 2,'recall'=>'1','sendTime' => array('>', $time)));
            $Info['unReadChatNum']  =   intval($unReadChatNum);

            $jobNum                 =   $this->select_num('company_job', array('uid' => $comId, 'status' => 0, 'r_status' => 1, 'state' => 1));
            $Info['jobNum']         =   $jobNum;

            $recNum                 =   $this->select_num('company_job', array('uid' => $comId, 'status' => 0, 'r_status' => 1, 'state' => 1, 'rec' => 1, 'rec_time' => array('>', time())));
            $Info['recJobNum']      =   $recNum;

            $topNum                 =   $this->select_num('company_job', array('uid' => $comId, 'status' => 0, 'r_status' => 1, 'state' => 1, 'xsdate' => array('>', time())));
            $Info['topJobNum']      =   $topNum;

            $urgentNum              =   $this->select_num('company_job', array('uid' => $comId, 'status' => 0, 'r_status' => 1, 'state' => 1, 'urgent' => 1, 'urgent_time' => array('>', time())));
            $Info['urgentJobNum']   =   $urgentNum;

            $autoNum                =   $this->select_num('company_job', array('uid' => $comId, 'status' => 0, 'r_status' => 1, 'state' => 1, 'urgent' => 1, 'autotime' => array('>', time())));
            $Info['autoJobNum']     =   $autoNum;

            $allJobNum              =   $this->select_num('company_job', array('uid' => $comId));
            $Info['allJobNum']      =   $allJobNum;

            $Info['phone_n']        =   !empty($Info['linktel']) ? $Info['linktel'] : $Info['linkphone'];

            $task                   =   $this->select_once('crm_work_plan', array('type' => 22, 'status' => 1, 'comid' => $Info['uid'], 'uid' => $auid, 'orderby' => 'stime,desc'), '`id`,`stime`,`content`');

            if (!empty($task)){
                $Info['taskTime']   =   date('Y-m-d', $task['stime']);
                $Info['taskContent']=   $task['content'];
                $toNight            =   strtotime(date('Y-m-d 23:59:59'));
                if ($task['stime'] < $toNight){

                    $Info['task']   =   1;  //  未完成
                }else{

                    $Info['task']   =   2;  //  未开始
                }
            }

            $Info['taskNum']        =   $this->select_num('wxpub_twtask', array('cuid' => $comId, 'type' => 2, 'status' => 0));

            if ($Info['lastupdate'] < strtotime('-3 days')){

                $Info['update_day'] =   1;
            }
        }

        return  $Info;
    }

    /**
     * 跟进批注
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function noteFollow($data = array())
    {

        $return =   array();

        if (!empty($data)) {

            $upData =   array(

                'is_note'       =>  1,
                'note_uid'      =>  $data['auid'],
                'note_time'     =>  time(),
                'note_content'  =>  $data['content']
            );

            $result =   $this->update_once('crmnew_concern', $upData, array('id' => $data['id']));

            if ($result) {

                $return['is_note']      =   1;

                $user   =   $this->select_once('admin_user', array('uid' => $data['auid']),'`name`');

                $return['note_user']    =   $user['name'];
                $return['noteTime']     =   date('Y-m-d H:i', $upData['note_time']);

                $noteContent        =   str_replace(array('&quot;','&nbsp;','<>', '\"'), array('','','', '"'), $upData['note_content']);
                $noteContent        =   htmlspecialchars_decode($noteContent);
                preg_match_all('<img(.*?)src=\"(.+?)\".*?>',$noteContent,$Res);
                $noteImgArr =   array();
                if (!empty($Res[2])) {
                    foreach ($Res[2] as $rv) {
                        if (strpos($rv, 'https:') === false && strpos($rv, 'http:') === false) {
                            $noteImgUrl =   checkpic($rv);
                        } else {
                            $noteImgUrl =   $rv;
                        }
                        $noteImgArr[]   =   $noteImgUrl;
                    }
                }
                $return['noteContent']  =   strip_tags($noteContent);
                $return['noteImgArr']   =   $noteImgArr;

                $comInfo    =   $this->select_once('company', array('uid' => $data['uid']), '`name`, `crm_uid`');
                $this->sendNoteMsg($comInfo['crm_uid'], array('title' => '跟进批注', 'first' => '您有新的跟进批注信息，跟进客户「'.$comInfo['name'].'」','customer'=>$comInfo['name'],'thing'=>'您有新的跟进批注信息'));


                $return['errcode']      =   9;
                $return['errmsg']       =   '批注成功！';
            } else {

                $return = array('errcode' => 8, 'errmsg' => '批注失败，请稍候重试！');
            }
        } else {

            $return = array('errcode' => 8, 'errmsg' => '参数错误，请售后重试！');
        }

        return $return;
    }

    /**
     * 删除跟进批注
     * @param array $where
     * @return array|bool|false|string|void
     */
    public function delFollowNote($where = array())
    {

        $return['layertype']    =   0;

        if (!empty($where)) {

            $upData =   array(

                'is_note'       =>  2,
                'note_auid'     =>  0,
                'note_time'     =>  0,
                'note_content'  =>  ''
            );

            $result =   $this->update_once('crmnew_concern', $upData, $where);
        }
        $return['errmsg']   =   '跟进批注';
        $return['errcode']  =   $result ? 9 : 8;
        $return['errmsg']   =   $result ? '删除成功！' : '删除失败！';
        return $return;
    }

    /**
     * @desc 业务通知
     * @param string $auid
     * @param string[] $data
     */
    private function sendNoteMsg($auid = '', $data = array('first' => '', 'title' => '','customer'=>'','thing'=>''))
    {

        if ($auid && $data['first']) {

            $user   =   $this->select_once('admin_user', array('uid' => $auid), '`uid`,`name`,`moblie`,`r_status`,`wxid`,`qy_userid`');

            if (!empty($user)) {

                require_once('weixin.model.php');
                $weixinM    =   new weixin_model($this->db, $this->def);

                if ($this->config['sy_admin_wmtype'] == 1) {

                    if ($user['wxid']) {

                        $this->sendWxAdmin(array('uid' => $user['uid'], 'wxid' => $user['wxid'], 'title' => $data['title'], 'first' => $data['first'], 'customer' => $data['customer'], 'thing' => $data['thing']));
                    }
                } else if ($this->config['sy_admin_wmtype'] == 2) {

                    $uids   =   $userids    =   array();
                    if ($user['qy_userid']) {

                        $uids[]     =   $user['uid'];
                        $userids[]  =   $user['qy_userid'];
                    }

                    $weixinM->sendWxQyAdmin(array('title' => $data['title'], 'first' => $data['first'], 'userids' => $userids, 'uids' => $uids));
                }
            }
        }
    }

    /**
     * 管理员通知跟进批注提醒
     *
     * @param array $data | 'title'=>自定义标题, 'first'=>通知内容
     * @return bool
     */
    private function sendWxAdmin($data = array())
    {
        global $config;
        if(!$config['wx_mbadmin'] && !$config['wx_mbadminlm'])
        {
            return true;
        }
        if($data['wxid'])
        {
            $wmtype	= $this->select_all('admin_wmtype', array('type' =>array('<>','')), '`type`,`name`');
            $titleType = array();
            foreach($wmtype as $wk=>$wv){
                $titleType[$wv['type']] = $wv['name'];
            }
            //转换数组中字符编码，微信只支持UTF-8
            $First      = $data['first'];
            $Title = $data['title'] ? $data['title'] : ($titleType[$data['type']] ? $titleType[$data['type']] : '消息通知');
            $Remark = '请及时登录管理后台「客户管理 - 跟进记录」进行处理!';
            if(!empty($config['wx_mbadminlm'])){
                $Tempid = $config['wx_mbadminlm'];

                $First      =   strlen($data['thing'])>19?mb_substr($data['thing'],0,15,'utf-8').'...':$data['thing'];

                if($data['customer']){
                    $customer = strlen($data['customer'])>19?mb_substr($data['customer'],0,15,'utf-8').'...':$data['customer'];
                }else{
                    $customer = '-';
                }

                $TempDate['thing7']	= array('value'=>$Title,'color'=>'');//项目
                $TempDate['thing3']	= array('value'=>$First,'color'=>'');//结果
                $TempDate['thing4'] = array('value'=>$customer,'color'=>'');//客户
                $tbody = array(
                    0 => '异常项目：'.$Title,
                    1 => '异常结果：'.$First,
                    2 => '客户名称：'.$customer,
                );
            }else{
                $Tempid = $config['wx_mbadmin'];
                $TempDate['first'] = array('value'=>'业务预警通知','color'=>'');//通知内容
                $TempDate['keyword1'] = array('value'=>$Title,'color'=>'');//通知类别
                $TempDate['keyword2'] = array('value'=>'「CRM - 客户管理 - 跟进记录」','color'=>'');//通知范围
                $TempDate['keyword3'] = array('value'=>date('Y/m/d H:i'),'color'=>'');//发布时间
                $TempDate['keyword4'] = array('value'=>$First,'color'=>'');//通知内容
                $TempDate['remark'] = array('value'=>$Remark,'color'=>'');
                $Url = $config['sy_weburl'] . '/wapadmin/';
                $tbody = array(
                    0 => '业务预警通知',
                    1 => '预警类别：'.$Title,
                    2 => '预警范围：「CRM - 客户管理 - 跟进记录」',
                    3 => '发布时间：'.date('Y/m/d H:i'),
                    4 => '预警内容：'.$First,
                    5 => $Remark,
                    6 => $Url
                );
            }
            $sdata = array(
                'uid' => $data['uid'],
                'utype' => 99,
                'wxid' => $data['wxid'],
                'ctime' => time(),
                'mbconfig' => 'wx_mbadmin',
                'body' => serialize($tbody)
            );
            require_once('weixin.model.php');
            $wxM = new weixin_model($this->db, $this->def);
            $wxM->sendWxTemplate($data['wxid'],$Tempid,$Url,$TempDate,$sdata);
        }
    }


    /**
     * 获取crm企业联系人
     * @param array $whereData
     * @return array|bool|false|string|void
     */
    public function getCrmLink($whereData = array())
    {
        $list = $this->select_all('crm_link', $whereData, 'id,link_man, link_mobile');
        return $list;
    }

    /**
     * @desc 新增/更新客户联系方式
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function insertCrmLink($data = array())
    {

        $mobile = $data['link_mobile'];
        $linkMan = $data['link_man'];
        $uid = $data['uid'];

        if (isset($data['add']) && $data['add'] == 1) {  //  业务确认继续添加

            $return =   $this->setCrmLink($data);
            return $return;
        } else {
            if (empty($linkMan)) {

                return array('errcode' => 8, 'msg' => '请填写联系人姓名');
            } else if (empty($mobile)) {

                return array('errcode' => 8, 'msg' => '请填写联系人手机号码');
            } else if (!CheckMobile($mobile)) {

                return array('errcode' => 8, 'msg' => '请填写正确的手机号码');
            } else {

                $comInfo    =   $this->select_once('company', array('linktel' => $mobile), '`uid`,`name`');
                if (!empty($comInfo)) {
                    if ($comInfo['uid'] == $uid) {

                        return array('errcode' => 8, 'msg' => '客户联系方式已存在，请重新添加！');
                    } else {

                        return array('errcode' => 7, 'msg' => $mobile.' 手机号码已被企业：'.$comInfo['name'].' 使用，请确认是否继续？');
                    }
                } else {

                    if (isset($data['id']) && intval($data['id']) > 0) {

                        $crmLink    =   $this->select_once('crm_link', array('link_mobile' => $mobile, 'id' => array('<>', $data['id'])), '`uid`');
                    } else {

                        $crmLink    =   $this->select_once('crm_link', array('link_mobile' => $mobile), '`uid`');
                    }

                    if (!empty($crmLink)) {

                        if ($crmLink['uid'] == $uid) {

                            return array('errcode' => 8, 'msg' => '客户联系方式已存在，请重新添加！');
                        } else {

                            $comInfo = $this->select_once('company', array('uid' => $crmLink['uid']), '`name`');
                            return array('errcode' => 7, 'msg' => $mobile.' 手机号码已被企业：'.$comInfo['name'].' 使用，请确认是否继续？');
                        }
                    } else {

                        $return =   $this->setCrmLink($data);

                        return $return;
                    }
                }
            }
        }
    }

    /**
     * @desc Private 新增、更新 客户联系方式
     * @param array $data
     * @return array
     */
    private function setCrmLink($data = array())
    {

        $mobile = $data['link_mobile'];
        $linkMan = $data['link_man'];
        $uid = $data['uid'];
        $auid = $data['auid'];

        if (isset($data['id']) && intval($data['id']) > 0) { // 更新客户联系方式

            $comInfo    =   $this->select_once('company', array('uid' => $uid), '`linkman`, `linktel`');
            $crmLink    =   $this->select_once('crm_link', array('id' => $data['id']));
            $result     =   $this->update_once('crm_link', array('link_man' => $linkMan, 'link_mobile' => $mobile, 'auid' => $auid), array('id' => $data['id']));
            if ($result) {

                $remark =   '';
                if ($data['link_man'] != $crmLink['link_man']) {
                    $remark .=  '联系人：'.$crmLink['link_man'].' → '.$data['link_man'];
                }
                if ($data['link_mobile'] != $crmLink['link_mobile']) {
                    $remark .=  '联系电话：'.$crmLink['link_mobile'].' → '.$data['link_mobile'];
                }
                if (empty($comInfo['linktel'])) {

                    $this->update_once('company', array('linkman' => $linkMan, 'linktel' => $mobile), array('uid' => $uid));
                    $remark .=  '（完善客户基本信息）';
                }
                $logData    =   array(

                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  6,
                    'content'   =>  '更新客户联系方式',
                    'remark'    =>  $remark
                );
                $this->addCrmComLog($logData);

                $lContent   =   '更新联系方式；'.$remark;
                $this->addCrmLog($lContent, 11, $uid, $auid);
                return array('errcode' => 9, 'msg' => '更新成功！');
            } else {

                return array('errcode' => 8, 'msg' => '更新失败！');
            }
        } else {                                              //  新增客户联系方式

            $comInfo    =   $this->select_once('company', array('uid' => $uid), '`linkman`, `linktel`');
            if (empty($comInfo['linktel'])) {

                $result =   $this->update_once('company', array('linkman' => $linkMan, 'linktel' => $mobile), array('uid' => $uid));
                $nid    =   -1;
            } else {

                $result =   $this->insert_into('crm_link', array('uid' => $uid, 'link_man' => $linkMan, 'link_mobile' => $mobile, 'auid' => $auid));
                $nid    =   $result;
            }
            if ($result) {

                $remark =   '联系人：'.$linkMan.'，联系电话：'.$mobile;
                if ($nid == -1) {
                    $remark .=  '（完善客户基本信息）';
                }
                $logData    =   array(

                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  6,
                    'content'   =>  '新增客户联系方式',
                    'remark'    =>  $remark
                );
                $this->addCrmComLog($logData);
                $lContent   =   '新增客户联系方式；'.$remark;
                $this->addCrmLog($lContent, 11, $uid, $auid);
                return array('errcode' => 9, 'msg' => '添加成功！', 'nid' => $nid);
            } else {

                return array('errcode' => 8, 'msg' => '添加失败！');
            }
        }
    }

    /**
     * @desc 删除CRM客户联系人
     * @param array $data
     * @return array
     */
    function delCrmLink($data = array()){

        if (empty($data)) {

            $return =   array('errcode' => 8, 'msg' => '参数错误，请重试！');
        } else {

            $id     =   intval($data['id']);
            $uid    =   intval($data['uid']);
            $auid   =   intval($data['auid']);

            $crmLink=   $this->select_once('crm_link', array('id' => $id));
            $result =   $this->delete_all('crm_link', array('id' => $id, 'uid' => $uid), '');

            if ($result){
                $remark     =   'ID：'.$id.'，联系人：'.$crmLink['link_man'].'；手机号码：'.$crmLink['link_mobile'];
                $logData    =   array(

                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  6,
                    'content'   =>  '删除客户联系方式',
                    'remark'    =>  $remark
                );
                $this->addCrmComLog($logData);

                $lContent = '删除联系方式；'.$remark;
                $this->addCrmLog($lContent, 11, $uid, $auid);
                return  array('errcode' => 9, 'msg' => '联系方式删除成功！');
            }else{

                return  array('errcode' => 8, 'msg' => '联系方式删除失败！');
            }
        }

        return $return;
    }

    /**
     * @desc 获取短信模板
     * @param array $data : uid, auid, tp;
     * @return array
     */
    function getMsgTpl($data = array())
    {

        if (!empty($data)) {

            if (empty($data['uid']) || empty($data['auid'])) {

                return array('errcode' => 8, 'msg' => '参数错误，请重试');
            }

            $uid    =   intval($data['uid']);
            $auid   =   intval($data['auid']);

            $msgData    =   array();
            $crm        =   $this->select_once('admin_user', array('uid' => $auid), '`name`,`moblie`');
            $member     =   $this->select_once('member', array('uid' => $uid), '`username`');

            $msgData['aname']   =   $crm['name'];
            $msgData['aphone']  =   $crm['moblie'];
            $msgData['username']=   $member['username'];

            $tplList    =   $this->select_all('templates', array('name' => array('like' , 'msgcrm')));

            foreach ($tplList as $k => $v)
            {

                $tplList[$k]['content'] =   $this->_tpl($v['content'], $msgData);

                if ($v['name'] == 'msgcrmlink'){

                    $tplList[$k]['title']   =   '联系通知';
                } else if ($v['name'] == 'msgcrmreg'){

                    $tplList[$k]['title']   =   '联系注册';
                } else if ($v['name'] == 'msgcrmtlogin'){

                    $tplList[$k]['title']   =   '提醒登录';
                } else if ($v['name'] == 'msgcrmtvip'){

                    $tplList[$k]['title']   =   '会员到期';
                } else if ($v['name'] == 'msgcrmtresume'){

                    $tplList[$k]['title']   =   '有新简历';
                } else if ($v['name'] == 'msgcrmresume'){

                    unset($tplList[$k]);
                }
            }
            return $tplList;
        }
    }

    private function _tpl($tpl, $data)
    {

        $re     =   array("{webname}", "{weburl}", "{webtel}");
        $re2[]  =   $this->config['sy_webname'];
        $re2[]  =   $this->config['sy_weburl'];
        $re2[]  =   $this->config['sy_freewebtel'];
        $tpl    =   str_replace($re, $re2, $tpl);

        foreach ($data as $k => $v) {

            $tpl    =   str_replace("{".$k."}", $v, $tpl);
        }
        return $tpl;
    }

    /**
     * @desc CRM管理客户，发送短信
     * @param array $data
     * @return array
     */
    function sendCrmMsg($data = array())
    {

        if (!empty($data)) {

            if (empty($data['uid']) || empty($data['auid']) || empty($data['mobile']) || empty($data['content'])) {

                return array('errcode' => 8, 'msg' => '参数错误，请重试！');
            }

            $auid   =   intval($data['auid']);
            $uid    =   intval($data['uid']);

            require_once 'notice.model.php';
            $noticeM=   new notice_model($this->db, $this->def);
            $msgData=   array('mobile' => $data['mobile'], 'content' => $data['content'], 'uid' => $data['uid'], 'cuid' => $data['auid'], 'port' => '11');
            $res    =   $noticeM->sendSMS($msgData);

            if ($res['status'] == 1){

                $comInfo    =   $this->select_once('company', array('uid' => $data['uid']), '`crm_uid`');
                $logData    =   array(

                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  11,
                    'content'   =>  '发送短信给客户',
                    'remark'    =>  '短信内容：'.$data['remark']
                );
                $this->addCrmComLog($logData);

                $lContent   =   "发送短信给客户；短信内容：".$data['remark'];
                if ($comInfo['crm_uid'] != $auid){

                    $lContent   =   $lContent.'；协助发送';
                }
                $this->addCrmLog($lContent, 14, $uid, $auid);

                $return['errcode']  =   9;
                $return['msg']      =   '短信发送成功';
            }else{

                $return['errcode']  =   8;
                $return['msg']      =   $res['msg'];
            }
            return $return;
        }
    }

    /**
     * @desc CMR 客户管理 短信记录
     *
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getMsgList($where = array(), $data = array())
    {

        $field  =   isset($data['field']) ? $data['field'] : '*';
        $list   =   $this->select_all('moblie_msg', $where, $field);

        if (!empty($list)) {

            $auidS  =   array();

            foreach ($list as $k => $v) {

                if (!in_array($v['cuid'], $auidS)){
                    $auidS[]    =   $v['cuid'];
                }
            }

            $adminUser  =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $auidS))), '`uid`,`name`');

            foreach ($list as $k => $v){

                $list[$k]['ctime_n']    =   date('Y-m-d H:i:s', $v['ctime']);
                foreach ($adminUser as $ak => $av){
                    if ($v['cuid'] == $av['uid']){

                        $list[$k]['name']   =   $av['name'];
                    }
                }
            }
        }

        return $list;
    }

    /**
     * @param null $comId
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getComStatic($comId = null, $data = array())
    {

        $field  =   isset($data['field']) ? $data['field'] : '*';
        $Static =   $this->select_once('company_statis', array('uid' => $comId), $field);

        if ($Static['rating_type'] == 2){

            $today          =   strtotime('today');

            $currentNum1    =   $this->select_num('company_job', array('uid' => $comId, 'sdate' => array('>=', $today)));
            $currentNum2    =   $this->select_num('partjob', array('uid' => $comId, 'addtime' => array('>=', $today)));
            $currentNum3    =   $this->select_num('lt_job', array('uid' => $comId, 'lastupdate' => array('>=', $today)));


            $jobCurrentNum  =   $currentNum1 + $currentNum2 + $currentNum3;
            $jobDNum        =   $Static['job_num'] - $jobCurrentNum;
            $Static['crm_job_num']  =   intval($jobDNum) > 0 ? $jobDNum : 0;    //  今日可发布职位数

            $sxCurrentNum   =   $this->select_num('job_refresh_log', array('uid' => $comId, 'r_time' => array('>=', $today)));
            $sxDNum         =   $Static['breakjob_num'] - $sxCurrentNum;
            $Static['crm_sx_num']   =   intval($sxDNum) > 0 ? $sxDNum : 0;      //  今日可刷新职位数

            $yqCurrentNum   =   $this->select_num('userid_msg', array('fid' => $comId, 'datetime' => array('>=', $today)));
            $yqDNum         =   $Static['invite_resume'] - $yqCurrentNum;
            $Static['crm_yq_num']   =   intval($yqDNum) > 0 ? $yqDNum : 0;      //  今日可邀请面试数

            $downCurrentNum =   $this->select_num('down_resume', array('comid' => $comId, 'downtime' => array('>=', $today)));
            $downDNum       =   $Static['down_resume'] - $downCurrentNum;
            $Static['crm_down_num'] =   intval($downDNum) > 0 ? $downDNum : 0;      //  今日可邀请面试数

            $zphCurrentNum1 =   $this->select_num('zhopinhui_com', array('uid' => $comId, 'ctime' => array('>=', $today)));
            $zphCurrentNum2 =   $this->select_num('zphnet_com', array('uid' => $comId, 'ctime' => array('>=', $today)));
            $zphCurrentNum  =   $zphCurrentNum1 + $zphCurrentNum2;
            $zphDNum        =   $Static['zph_num'] - $zphCurrentNum;
            $Static['crm_zph_num']  =   intval($zphDNum) > 0 ? $zphDNum : 0;    //  今日可报名招聘会数

            $spCurrentNum   =   $this->select_num('spview', array('uid' => $comId, 'ctime' => array('>=', $today)));
            $spDNum         =   $Static['spview_num'] - $spCurrentNum;
            $Static['crm_sp_num']   =   intval($spDNum) > 0 ? $spDNum : 0;      //  今日可发布视频面试数

            $chatCurrentNum =   $this->select_num('chat_right', array('comid' => $comId,'daymf'=>array('<>',1),'ctime' => array('>=', $today)));
            $chatDNum       =   $Static['chat_num'] - $chatCurrentNum;
            $Static['crm_chat_num'] =   intval($chatDNum) > 0 ? $chatDNum : 0;  //  今日可聊天数
        }else{

            $currentNum1    =   $this->select_num('company_job', array('uid' => $comId, 'status' => 0));
            $currentNum2    =   $this->select_num('partjob', array('uid' => $comId, 'status' => 0));
            $currentNum3    =   $this->select_num('lt_job', array('uid' => $comId, 'zp_status' => 0));
            $jobCurrentNum  =   $currentNum1 + $currentNum2 + $currentNum3;
            $jobDNum        =   $Static['job_num'] - $jobCurrentNum;
            $Static['crm_job_num']  =   intval($jobDNum) > 0 ? $jobDNum : 0;    //  今日可上架职位数
        }

        return $Static;
    }

    /**
     * @desc CRM 客户管理 在招职位
     * @param array $where
     * @param array $data
     * @return array
     */
    public function getJobList($where = array(), $data = array())
    {

        $field  =   isset($data['field']) ? $data['field'] : '*';
        $list   =   $this->select_all('company_job', $where, $field);

        if (!empty($list)){

            $options    =   array('job', 'city', 'com');

            $cache      =   $this->getClass($options);

            $linkIdArr  =   array();
            foreach ($list as $k => $v){

                if ($v['link_id'] > 0){

                    $linkIdArr[]    =   $v['link_id'];
                }
            }

            if (!empty($linkIdArr)){

                $linkList   =   $this->select_all('company_job_link', array('id' => array('in', pylode(',', $linkIdArr))), '`id`,`link_man`,`link_moblie`');
                $jobLink    =   array();
                foreach ($linkList as $lk => $lv){

                    $jobLink[$lv['id']]['link_man'] =   $lv['link_man'];
                    $jobLink[$lv['id']]['link_tel'] =   $lv['link_moblie'];
                }
            }

            $comInfo    =   $this->select_once('company', array('uid' => $data['uid']), '`linkman`,`linktel`,`linkphone`');

            $defLinkTel =   '';
            $defLinkMan =   $comInfo['linkman'];
            if (CheckMobile($comInfo['linktel'])) {

                $defLinkTel =   $comInfo['linktel'];
            } else if (CheckMobile($comInfo['linkphone'])) {

                $defLinkTel =   $comInfo['linkphone'];
            }

            if (isset($data['xcx']) && intval($data['xcx']) == 1){

                require_once('xcx.model.php');
                $xcxM       =   new xcx_model($this->db, $this->def);
            }

            $sqList     =   $this->select_all('userid_job', array('com_id' => $data['uid'], 'groupby' => 'job_id', 'having' => array('num' => array('>', 0))), '`job_id`,count(*) as `num`');
            $twList     =   $this->select_all('wxpub_twtask', array('cuid' => $data['uid'], 'type' => 1, 'status' => 1, 'groupby' => 'jobid', 'having' => array('num' => array('>', 0))), '`jobid`,count(*) as `num`');
            $taskList   =   $this->select_all('wxpub_twtask', array('cuid' => $data['uid'], 'type' => 1, 'status' => 0), '`jobid`');

            foreach ($list as $k => $v) {

                $list[$k]['sq_num']     =   0;
                $list[$k]['tw_num']     =   0;

                $list[$k]['taskNum']    =   0;

                if ($v['status'] == 0){

                    $list[$k]['isZp']       =   true;
                    $list[$k]['status_n']   =   '招聘中';
                }else if ($v['status'] == 1){

                    $list[$k]['isZp']       =   false;
                    $list[$k]['status_n']   =   '已下架';
                }

                $list[$k]['update_day'] =   0;
                if ($v['lastupdate'] < strtotime('-3 days')){
                    $list[$k]['update_day'] =   1;
                }
                $list[$k]['update_n']   =   date('Y-m-d H:i', $v['lastupdate']);
                $list[$k]['sdate_n']    =   date('Y-m-d H:i', $v['sdate']);

                if (!empty($v['welfare'])) {
                    $v['welfare']           =   str_replace(' ', '', $v['welfare']);
                    $list[$k]['welfare_n']  =   array_filter(@explode(',', trim($v['welfare'])));
                }

                $list[$k]['salary_n']   =   salaryUnit($v['minsalary'], $v['maxsalary']);

                if (!empty($v['three_cityid'])) {

                    $list[$k]['city_n'] =   $cache['city_name'][$v['provinceid']] . ' - ' . $cache['city_name'][$v['cityid']] . ' - ' . $cache['city_name'][$v['three_cityid']];
                    $list[$k]['cityStr']=   $cache['city_name'][$v['three_cityid']];
                } else if (!empty($v['cityid'])) {

                    $list[$k]['city_n'] =   $cache['city_name'][$v['provinceid']] . ' - ' . $cache['city_name'][$v['cityid']];
                    $list[$k]['cityStr']=   $cache['city_name'][$v['cityid']];
                } else if (!empty($v['provinceid'])) {

                    $list[$k]['city_n'] =   $cache['city_name'][$v['provinceid']];
                    $list[$k]['cityStr']=   $cache['city_name'][$v['provinceid']];
                }

                if (!empty($v['edu'])) {

                    $list[$k]['edu_n']  =   $cache['comclass_name'][$v['edu']];
                } else {

                    $list[$k]['edu_n']  =   '不限';
                }

                if (!empty($v['exp'])) {

                    $list[$k]['exp_n']  =   $cache['comclass_name'][$v['exp']];
                } else {

                    $list[$k]['exp_n']  =   '不限';
                }

                $list[$k]['job_url']    =   Url('job', array('c' => 'comapply', 'id' => $v['id']));


                if (isset($data['xcx']) && intval($data['xcx']) == 1) {
                    $xcxUrl             =   $xcxM->getUrlLink(array('type' => 'job', 'id' => $v['id']));
                    $list[$k]['xcx_url']=   !empty($xcxUrl) ? $xcxUrl : '';
                }

                if (!empty($v['job_post'])) {

                    $list[$k]['jobClassId'] =   $v['job_post'];
                } elseif (!empty($v['job1_son'])) {

                    $list[$k]['jobClassId'] =   $v['job1_son'];
                } elseif (!empty($v['job1'])) {

                    $list[$k]['jobClassId'] =   $v['job1'];
                }
                $list[$k]['jobClassId_n'] =   $cache['job_name'][$list[$k]['jobClassId']];

                if (!empty($v['three_cityid'])) {

                    $list[$k]['cityClassId']=   $v['three_cityid'];
                } elseif (!empty($v['cityid'])) {

                    $list[$k]['cityClassId']=   $v['cityid'];
                } elseif (!empty($v['provinceid'])) {

                    $list[$k]['cityClassId']=   $v['provinceid'];
                }
                $list[$k]['cityClassId_n'] = $cache['city_name'][$list[$k]['cityClassId']];

                if (!empty($sqList)){
                    foreach ($sqList as $sk => $sv){
                        if ($v['id'] == $sv['job_id']){

                            $list[$k]['sq_num'] =   $sv['num'];
                        }
                    }
                }
                if (!empty($twList)){
                    foreach ($twList as $tk => $tv){

                        if ($v['id'] == $tv['jobid']){

                            $list[$k]['tw_num'] =   $tv['num'];
                        }
                    }
                }
                if (!empty($taskList)){
                    foreach ($taskList as $tak => $tav) {
                        if ($tav['jobid'] == $v['id']){
                            $list[$k]['taskNum']++;
                        }
                    }
                }

                if ($v['is_link'] == 1) {

                    $list[$k]['link_man']   =   $defLinkMan;
                    $list[$k]['link_tel']   =   $defLinkTel;
                } else if ($v['is_link'] == 2) {

                    $list[$k]['link_man']   =   $jobLink[$v['link_id']]['link_man'] ? $jobLink[$v['link_id']]['link_man'] : $defLinkMan;
                    $list[$k]['link_tel']   =   $jobLink[$v['link_id']]['link_tel'] ? $jobLink[$v['link_id']]['link_tel'] : $defLinkTel;
                } else if ($v['is_link'] == 3) {

                    if ($v['link_id'] > 0) {

                        $list[$k]['link_man']   =   $jobLink[$v['link_id']]['link_man'] ? $jobLink[$v['link_id']]['link_man'] : $defLinkMan;
                        $list[$k]['link_tel']   =   $jobLink[$v['link_id']]['link_tel'] ? $jobLink[$v['link_id']]['link_tel'] : $defLinkTel;
                    } else {

                        $list[$k]['link_man']   =   $defLinkMan;
                        $list[$k]['link_tel']   =   $defLinkTel;
                    }
                }
            }
        }
        return $list;
    }

    /**
     * @desc CRM 客户管理 订单记录
     * @param array $where
     * @param array $data
     * @return array
     */
    public function getOrderList($where = array(), $data = array())
    {

        $field      =   isset($data['field']) ? $data['field'] : '*';
        $list       =   $this->select_all('company_order', $where, $field);

        if (!empty($list)) {

            include(CONFIG_PATH . 'db.data.php');
            $stateArr       =   array(0 => '交易失败', 1 => '等待付款', 2 => '支付成功', 3 => '等待确认', 4 => '交易关闭');
            $typeArr        =   $arr_data['ordertype'];
            $orderTypeArr   =   $arr_data['pay'];

            foreach ($list as $k => $v) {

                $list[$k]['order_state_n']  =   $stateArr[$v['order_state']];
                $list[$k]['type_n']         =   $typeArr[$v['type']];
                $list[$k]['order_type_n']   =   $orderTypeArr[$v['order_type']];
                $list[$k]['order_time_n']   =   date('Y-m-d H:i', $v['order_time']);
            }
        }

        return $list;
    }

    /**
     * @desc 客户管理 - 新增订单
     * @param array $data
     * @return array
     */
    public function addOrder($data = array())
    {

        if (empty($data['uid']) || empty($data['auid']) || empty($data['type'])|| empty($data['order_type'])){

            return array('errcode' => 8, 'msg' => '参数错误，请重试！');
        } else if ($data['type'] == 1 && (empty($data['rating']) || empty($data['deal_model']))) {

            return array('errcode' => 8, 'msg' => '参数错误，请重试！');
        }else{

            $auid   =   intval($data['auid']);
            $uid    =   intval($data['uid']);

            $comInfo=   $this->select_once('company', array('uid' => $uid), '`crm_uid`');

            $dingdan    =   time().rand(10000, 99999);
            $orderData  =   array(
                'uid'           =>  $uid,
                'order_id'      =>  $dingdan,
                'order_type'    =>  $data['order_type'],
                'order_price'   =>  round($data['order_price'], 2),
                'order_time'    =>  time(),
                'order_remark'  =>  $data['order_remark'],
                'type'          =>  $data['type'],
                'rating'        =>  $data['rating'],
                'integral'      =>  $data['integral'],
                'crm_uid'       =>  $comInfo['crm_uid'],
                'usertype'      =>  2,
                'did'           =>  $this->config['did'],
                'is_crm'        =>  1,
                'status'        =>  2
            );
            if ($data['type'] == 1){

                $ratingInfo                 =   $this->select_once('company_rating',array('id' => $data['rating']),'`name`');
                $orderData['order_remark'] .=   $orderData['order_remark'] ? "，" : "";
                $orderData['order_remark'] .=   "会员信息：".$ratingInfo['name'];
            }

            if ($data['type'] == 1 && !empty($data['rating'])){ //  业务员录入会员套餐订单，根据传递的业务模式逻辑判断处理
                if ($data['deal_model'] == 1) {                 //  需要添加业务处理
                    $orderData['order_state'] = 3;
                }elseif ($data['deal_model'] == 2){             //  不用业务处理
                    $orderData['order_state'] = 2;
                    //  去掉业务关键字段信息
                    unset($orderData['rating']);
                }
            }else{                                              //  业务员录入其他订单，没有业务逻辑，仅仅作为账单数据
                $orderData['order_state']   =   2;
            }

            $nid    =   $this->insert_into('company_order', $orderData);

            if ($nid){
                // 保存合同图片
                $time = time();
                $htpics = array();
				if (isset($data['htpics'])) {
	                foreach ($data['htpics'] as $k => $v){
	                    $htpics[$k]['picurl'] = $v;
	                    $htpics[$k]['order_id'] = $nid;
	                    $htpics[$k]['ctime'] = $time;
	                }
				}
                $htpics && $this->DB_insert_multi('order_ht_pic', $htpics);
                $content    =   '录入新订单（ID：'.$nid.'）';
                $remark     =   '';
                if ($comInfo['crm_uid'] != $auid) {

                    $remark .=  '协助录入；';
                }
                if (!empty($orderData['order_remark'])) {
                    $remark .=  '订单备注：'.$orderData['order_remark'];
                }
                $logData        =   array(
                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  3,
                    'content'   =>  $content,
                    'remark'    =>  $remark
                );
                $this->addCrmComLog($logData);

                $lContent   =   '录入订单：'.$dingdan;
                if ($comInfo['crm_uid'] != $auid){

                    $lContent   .=   '；协助录单';
                }
                $this->addCrmLog($lContent, 4, $uid, $auid);

                $return['errcode']  =   9;
                $return['msg']      =   '订单录入成功';
            }else{

                $return['errcode']  =   8;
                $return['msg']      =   '订单录入失败';
            }

            return $return;
        }
    }

    /**
     * @desc CRM 客户管理 用户日志
     * @param array $where
     * @param array $data
     * @return array
     */
    public function getMemberLogList($where = array(), $data = array())
    {

        $field      =   isset($data['field']) ? $data['field'] : '*';
        $list       =   $this->select_all('member_log', $where, $field);
        if (!empty($list)) {
            foreach ($list as $k => $v) {

                $list[$k]['ctime_n']    =   date('Y-m-d H:i:s', $v['ctime']);
            }
        }
        return $list;
    }
    /**
     * @desc CRM 客户管理 用户轨迹
     * @param array $where
     * @param array $data
     * @return array
     */
    public function getWebLogList($where = array(), $data = array())
    {

        require_once ('weblog.model.php');
        $LogM = new weblog_model($this->db, $this->def);
        return $LogM->getWebLogList($where, $data);
    }

    /**
     * @desc 修改客户名称
     * @param array $data
     * @return array
     */
    function upComName($data = array())
    {

        if (!empty($data)) {

            if (empty($data['uid']) || empty($data['name']) || empty($data['auid'])) {

                return array('errcode' => 8, 'msg' => '参数错误，请重试');
            }

            $comNum =   $this->select_num('company', array('name' => $data['name']));
            if ($comNum > 0){

                return array('errcode' => 8, 'msg' => '客户名称已存在，请确认后调整');
            }

            $comInfo    =   $this->select_once('company', array('uid' => $data['uid']), '`name`,`crm_uid`');

            $result     =   $this->update_once('company', array('name' => $data['name']), array('uid' => $data['uid']));

            if ($result) {

                $this->update_once('company_job', array('com_name' => $data['name']), array('uid' => $data['uid']));
                $logData    =   array(

                    'auid'      =>  $data['auid'],
                    'uid'       =>  $data['uid'],
                    'type'      =>  5,
                    'content'   =>  '完善客户信息',
                    'remark'    =>  "客户名称调整：".$comInfo['name'].' → '.$data['name']
                );
                $this->addCrmComLog($logData);

                $lContent   =   '完善客户信息；客户名称调整：'.$comInfo['name'].'→'.$data['name'];
                if ($data['auid'] != $comInfo['crm_uid']){
                    $lContent .= '；协助更新';
                }
                $this->addCrmLog($lContent, 2, $data['uid'], $data['auid']);
            }

            $return['errcode']  =   $result ? 9 : 8;
            $return['msg']      =   $result ? '客户（'.$data['uid'].'）名称更新成功' : '客户名称更新失败';
            return $return;
        }
    }

    /**
     * @param $pwdData
     * @return array
     */
    private function generatePwd($pwdData)
    {

        $pwdRes =   array();
        if (empty($pwdData['password'])) {
            return $pwdRes;
        }
        if (empty($pwdData['salt'])) {
            $salt   =   substr(uniqid(rand()), -6);
        } else {
            $salt   =   $pwdData['salt'];
        }
        $pass           =   passCheck($pwdData['password'], $salt);
        $pwdRes['pwd']  =   $pass;
        $pwdRes['salt'] =   $salt;
        return $pwdRes;
    }

    /**
     * @desc 修改账号信息（用户名，密码）
     * @param array $data
     * @return array
     */
    public function upUserInfo($data = array())
    {
        if (!empty($data)) {

            if (empty($data['uid']) || empty($data['auid']) || (empty($data['username']) && empty($data['password']))) {

                return array('errcode' => 8, 'msg' => '参数错误，请重试');
            }
            if (!empty($data['username'])) {
                if (CheckRegUser($data['username']) == false && CheckRegEmail($data['username']) == false) {

                    return array('errcode' => 8, 'msg' => '用户名包含特殊字符');
                }
                if (!checkRegnameLegal($data['username'])) {

                    return array('errcode' => 8, 'msg' => '该用户名禁止使用！');
                }
                $regInfo    =   $this->select_once('member', array('username' => trim($data['username'])), '`uid`');
                if (!empty($regInfo) && $regInfo['uid'] != $data['uid']) {

                    return array('errcode' => 8, 'msg' => '用户名已被使用');
                }

                $username   =   $data['username'];
            }
            if (!empty($data['password'])) {

                $pwMsg  =   regPassWordComplex($data['password']);

                if ($pwMsg != '') {

                    return array('errcode' => 8, 'msg' => $pwMsg);
                }

                require_once 'userinfo.model.php';
                $userInfoM  =   new userinfo_model($this->db, $this->def);

                $passA      =   $userInfoM->generatePwd(array('password' => $data['password']));
                $password   =   $passA['pwd'];
                $salt       =   $passA['salt'];
            }

            $comInfo    =   $this->select_once('company', array('uid' => $data['uid']), '`crm_uid`');
            $userInfo   =   $this->select_once('member', array('uid' => $data['uid']), '`uid`,`username`,`password`,`salt`');

            $upData     =   array();
            $msgSub     =   '';
            if (isset($username)){

                $upData['username'] =   $username;
                $msgSub             =   '用户名调整：'.$userInfo['username'].' → '.$username.'；';
            }
            if (isset($password) && isset($salt)){

                $upData['password'] =   $password;
                $upData['salt']     =   $salt;
                $msgSub            .=   '密码调整为：'.$data['password'];
            }

            $result     =   $this->update_once('member', $upData, array('uid' => $data['uid']));

            if ($result) {

                $logData    =   array(

                    'auid'      =>  $data['auid'],
                    'uid'       =>  $data['uid'],
                    'type'      =>  5,
                    'content'   =>  '完善客户信息',
                    'remark'    =>  $msgSub
                );
                $this->addCrmComLog($logData);

                $lContent   =   '完善客户信息；修改账号信息：'.$msgSub;

                if ($data['auid'] != $comInfo['crm_uid']){
                    $lContent .= '；协助更新';
                }
                $this->addCrmLog($lContent, 2, $data['uid'], $data['auid']);
            }

            $return['errcode']  =   $result ? 9 : 8;
            $return['msg']      =   $result ? '账号（'.$data['uid'].'）信息更新成功' : '账号信息更新失败';
            return $return;
        }
    }

    /**
     * @desc 修改客户标签/等级
     * @param array $data |uid 客户（企业）ID; auid 操作人UID; st 客户标签/等级ID st_n 客户标签/等级名称
     * @return array
     */
    function upComST($data = array()){

        if (!empty($data)){

            if (empty($data['st'])) {
                return array('errcode' => 8, 'msg' => $data['isSt'] == 'status' ? '请选择客户标签' : '请选择客户等级');
            }
            if (empty($data['uid'])) {
                return array('errcode' => 8, 'msg' => '请选择需要修改等级的客户');
            }

            $uid    =   explode(',', $data['uid']);

            $auid   =   intval($data['auid']);

            $st     =   intval($data['st']);
            $st_n   =   $data['st_n'];
            $remark =   $data['remark'];

            $value  =   $data['isSt'] == 'status' ? array('crm_status' => $st) : array('crm_type' => $st);

            $result =   $this->update_once('company', $value, array('uid' => array('in', pylode(',', $uid))));

            if ($result) {

                $logData    =   array();

                $type       =   $data['isSt'] == 'status' ? 9 : 7;
                $content    =   $data['isSt'] == 'status' ? '调整客户标签为：【'.$st_n.'】' : '调整客户等级为：【'.$st_n.'】';

                foreach ($uid as $k => $v) {

                    $logData[$k]['auid']    =   $auid;
                    $logData[$k]['uid']     =   $v;
                    $logData[$k]['type']    =   $type;
                    $logData[$k]['content'] =   $content;
                    $logData[$k]['remark']  =   $remark;
                    $logData[$k]['ctime']   =   time();
                }

                $this->DB_insert_multi('crm_comlog', $logData);

                $lContent   =   $data['isSt'] == 'status' ? '客户标签；标记客户标签为：【'.$st_n.'】' : '客户等级；标记客户等级为：【'.$st_n.'】';

                $this->addCrmLog($lContent, 9, '', $auid);
            }

            $return['errcode']  =   $result ? 9 : 8;
            $msg                =   $data['isSt'] == 'status' ?'客户标签修改' : '客户等级标记';
            $msg                .=   $result ? '成功' : '失败';
            $return['msg']      =   $msg;
            return $return;
        }
    }

    /**
     * @desc 暂停招聘
     * @param array $data
     * @return array
     */
    public function stopCom($data = array())
    {

        if (!empty($data)) {

            if (empty($data['uid']) || empty($data['auid'])) {

                return array('errcode' => 8, 'msg' => '参数错误，请重试');
            }

            $comInfo    =   $this->select_once('company', array('uid' => $data['uid']), '`crm_uid`, `rating`, `vipetime`, `r_status`');

            if ($comInfo['rating'] == 0 || !isVip($comInfo['vipetime'])){

                return array('errcode' => 8, 'msg' => '企业会员已到期，无需操作暂停招聘');
            }
            if ($comInfo['r_status'] == 2){

                return array('errcode' => 8, 'msg' => '企业已锁定，请先解锁后操作暂停');
            }
            if ($comInfo['r_status'] == 4){

                return array('errcode' => 8, 'msg' => '企业已暂停，无需重复设置');
            }

            $zt_time    =   time();
            $result     =   $this->update_once('company', array('r_status' => 4, 'zt_time' => $zt_time), array('uid' => $data['uid']));

            if ($result) {

                $this->update_once('company_job', array('r_status' => 4), array('uid' => $data['uid']));
                $this->update_once('partjob', array('r_status' => 4), array('uid' => $data['uid']));
                $this->update_once('company_job', array('r_status' => 4), array('uid' => $data['uid']));
                $this->update_once('school_xjh',array('r_status' => 4), array('uid' => $data['uid']));
                $this->update_once('lt_job', array('r_status' => 4), array('uid' => $data['uid'], 'usertype' => 2));

                $logData    =   array(

                    'auid'      =>  $data['auid'],
                    'uid'       =>  $data['uid'],
                    'type'      =>  14,
                    'content'   =>  '暂停招聘',
                    'remark'    =>  $data['auid'] != $comInfo['crm_uid'] ? '协助操作' : ''
                );
                $this->addCrmComLog($logData);

                $lContent   =   '暂停企业招聘';
                if ($data['auid'] != $comInfo['crm_uid']){
                    $lContent .= '；协助调整';
                }
                $this->addCrmLog($lContent, 18, $data['uid'], $data['auid']);
            }
            require_once ('log.model.php');
            $logM = new log_model($this->db, $this->def);

            $logContent = '企业会员暂停设置成功(ID:' .$data['uid']. ')';

            $logM -> addAdminLog($logContent);


            $return['errcode']  =   $result ? 9 : 8;
            $return['msg']      =   $result ? '暂停招聘设置成功' : '暂停招聘设置失败';
            $return['zt_time_n']=   date('Y-m-d H:i:s', $zt_time);
            $return['zt_days']  =   intval((time() - $zt_time) / 86400);

            return $return;
        }
    }

    /**
     * @desc 解除暂停
     * @param array $data
     * @return array
     */
    public function openCom($data = array())
    {

        require_once ('company.model.php');
        $companyM = new company_model($this->db, $this->def);

        $cdata = array(
            'uid'       =>  $data['uid'],
            'from'      =>  'crm',
            'addzttime' =>  $data['add']
        );

        $res = $companyM->setupcom($cdata);

        if($res['errcode']==9){
            $logData    =   array(

                'auid'      =>  $data['auid'],
                'uid'       =>  $data['uid'],
                'type'      =>  14,
                'content'   =>  '解除企业暂停招聘'
            );
            $this->addCrmComLog($logData);
            $this->addCrmLog('解除企业暂停招聘', 18, $data['uid'], $data['auid']);
        }
        return $res;
    }

    /**
     * @desc 修改客户联系方式
     * @param array $data
     * @return array
     */
    function upComLink($data = array())
    {

        if (!empty($data)){

            if (empty($data['uid']) || empty($data['auid']) || empty($data['linkman']) || empty($data['address'])  || (empty($data['linktel']) && empty($data['linkphone']))) {
                return array('errcode' => 8, 'msg' => '参数错误，请重试');
            }
            if (!empty($data['linktel']) && !CheckMobile($data['linktel'])) {
                return array('errcode' => 8, 'msg' => '客户联系手机格式错误，请重新填写');
            }
            if (!empty($data['linkphone']) && !CheckTell($data['linkphone'])) {
                return array('errcode' => 8, 'msg' => '客户固话格式错误，请重新填写');
            }
            if (!empty($data['linkmail']) && !CheckRegEmail($data['linkmail'])) {
                return array('errcode' => 8, 'msg' => '客户邮箱格式错误，请重新填写');
            }

            $uid    =   intval($data['uid']);
            $auid   =   intval($data['auid']);

            $checkData  =   array();
            if (!empty($data['linktel'])){
                $checkData['moblie'] = $data['linktel'];
            }
            if (!empty($data['linkmail'])){
                $checkData['email'] = $data['linkmail'];
            }

            if (!empty($checkData)){
                include_once('userinfo.model.php');
                $uInfoM =   new userinfo_model($this->db, $this->def);
                $return =   $uInfoM->addMemberCheck($checkData, $uid,'admin');
                if (!empty($return['msg'])){
                    return array('errcode' => 8, 'msg' => $return['msg']);
                }
            }

            $upData         =   array(
                'linkman'   =>  $data['linkman'],
                'linkjob'   =>  $data['linkjob'],
                'linktel'   =>  $data['linktel'],
                'linkphone' =>  $data['linkphone'],
                'linkmail'  =>  $data['linkmail'],
                'address'   =>  $data['address']
            );

            $comInfo    =   $this->select_once('company', array('uid' => $uid), '`uid`,`linkman`,`linkjob`,`linktel`,`linkphone`,`linkmail`,`crm_uid`, `address`');

            $result     =   $this->update_once('company', $upData, array('uid' => $uid));

            if ($result) {

                $remark     =   '联系人：'.$data['linkman'].'，职称：'.$data['linkjob'].'，手机：'.$data['linktel'].'，固话：'.$data['linkphone'].'，邮箱：'.$data['linkmail'].'，地址：'.$data['address'];
                $logData    =   array(
                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  6,
                    'content'   =>  '更新企业联系方式',
                    'remark'    =>  $comInfo['crm_uid'] != $auid ? '协助调整；'.$remark : $remark
                );
                $this->addCrmComLog($logData);

                $keyArr     =   array('linkman','linkjob','linktel','linkphone','linkmail','address');
                $nameArr    =   array('linkman' => '联系人', 'linkjob' => '职称', 'linktel' => '手机', 'linkphone' => '固话', 'linkmail' => '邮箱', 'address' => '地址');
                $lContent   =   "";

                foreach($comInfo as $ck => $cv){
                    if($cv != $data[$ck] && in_array($ck, $keyArr)){
                        $lContent   .=  $nameArr[$ck]."：".$cv." -> ".$data[$ck]."；";
                    }
                }
                if ($lContent != "") {

                    $lContent   =   "更新企业联系方式：" . $lContent;
                    if ($comInfo['crm_uid'] != $auid){

                        $lContent   =   $lContent.'；协助修改';
                    }
                    $this->addCrmLog($lContent, 11, $uid, $auid);
                }
            }

            $return['errcode'] = $result ? 9 : 8;
            $msg = $result ? '成功' : '失败';
            $return['msg'] = '客户联系方式修改'.$msg;

            return $return;
        }
    }

    /**
     * @desc 联系反馈-》添加备注
     * @param array $data |uid 客户（企业）ID; auid 操作人UID;
     * @return array
     */
    function remarkCom($data = array()){

        if (!empty($data)){

            if (empty($data['uid'])) {
                return array('errcode' => 8, 'msg' => '参数错误，请重试！');
            }

            $uid        =   intval($data['uid']);
            $auid       =   intval($data['auid']);
            $remark     =   $data['crm_remark'];

            $comInfo    =   $this->select_once('company', array('uid' => $uid), '`crm_uid`');

            $result     =   $this->update_once('company', array('crm_remark' => $remark), array('uid' => $uid));
            if ($result) {

                if ($comInfo['crm_uid'] != $auid){
                    $remark .=  '（协助填写）';
                }
                $logData    =   array(
                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  8,
                    'content'   =>  '添加客户备注',
                    'remark'    =>  $remark
                );
                $this->addCrmComLog($logData);

                $lContent   =   "备注客户：" . $remark;
                if ($comInfo['crm_uid'] != $auid){

                    $lContent   =   $lContent.'；协助填写';
                }
                $this->addCrmLog($lContent, 17, $uid, $auid);
            }

            $return['errcode']  =   $result ? 9 : 8;
            $msg                =   $result ? '成功' : '失败';
            $return['msg']      =   '客户备注'.$msg;

            return $return;
        }
    }

    /**
     * @desc 放弃客户
     * @param array $data |uid 客户（企业）ID; auid 操作人UID
     * @return array
     */
    function giveUpUser($data = array())
    {

        if (!empty($data)) {

            if (empty($data['uid']) || empty($data['auid']) || empty($data['remark'])) {

                return array('errcode' => 8, 'msg' => '参数错误，请重试');
            }

            $auid   =   intval($data['auid']);

            $uid    =   explode(',', $data['uid']);

            if (count($uid) == 1){

                $comInfo    =   $this->select_once('company', array('uid' => $data['uid']), '`crm_uid`');

                if ($comInfo['crm_uid'] != $auid){

                    return array('errcode' => 8, 'msg' => '只有企业绑定业务员本人才可以放弃客户');
                }
            }

            $remark =   $data['remark'];

            $result =   $this->update_once('company', array('crm_uid' => '0', 'crm_time' => '0', 'pcrmuid' => $auid, 'release_time' => time()), array('crm_uid' => $auid, 'uid' => array('in', pylode(',', $uid))));

            if ($result) {

                $this->delete_all('crm_work_plan', array('comid' => array('in', pylode(',', $uid))), '');
                $logData    =   array();

                foreach ($uid as $k => $v) {

                    $logData[$k]['auid']    =   $auid;
                    $logData[$k]['uid']     =   $v;
                    $logData[$k]['type']    =   2;
                    $logData[$k]['content'] =   "放弃客户（UID：".pylode(',', $uid)."）";
                    $logData[$k]['remark']  =   $remark;
                    $logData[$k]['ctime']   =   time();
                }

                $this->DB_insert_multi('crm_comlog', $logData);

                $lContent = "放弃客户（UID：" . pylode(',', $uid) . "）";
                $this->addCrmLog($lContent, 8, '', $auid);
            }

            $return['errcode'] = $result ? 9 : 8;
            $msg = $result ? '成功' : '失败';
            $return['msg'] = '放弃客户' . $msg;
            return $return;
        }
    }

    /**
     * @desc 转交客户
     * @param array $data |crm_uid 转移到顾问UID; uid 客户（企业）ID; auid 转移顾问本人UID
     * @return array
     */
    function deliverUser($data = array()){

        if (!empty($data)){

            if (empty($data['crm_uid'])) {
                return array('errcode' => 8, 'msg' => '请选择转交客户顾问');
            }
            if (empty($data['uid'])) {
                return array('errcode' => 8, 'msg' => '请选择需要转交的客户');
            }

            $auid       =   intval($data['auid']);
            $uid        =   explode(',', $data['uid']);

            $crm_uid    =   intval($data['crm_uid']);
            $crm_name   =   trim($data['crm_name']);

            $aInfo  =   $this->select_once('admin_user', array('uid' => $crm_uid), '`num`,`name`,`is_crm`');

            // if ($this->config['sy_crm_receive_grant'] == 1 && $aInfo['is_crm'] != 1) { // CRM设置-领取客户权限-限制业务员
            //     return array('errcode' => '8', 'msg' => '转交失败，被转交账户未开通业务员');
            // }

            if ((int)$aInfo['num'] > 0) {

                $allKhNum   =   $this->select_num('company', array('crm_uid' => $crm_uid));

                if ($this->config['sy_crm_deal_open'] == 1 && !empty($this->config['crm_deal_rating'])) {

                    $vipKhNum   =   $this->select_num('company', array('crm_uid' => $crm_uid, 'rating' => array('in', $this->config['crm_deal_rating']), 'vipetime' => array('>', strtotime('today'))));

                    $khNum  =   $allKhNum - $vipKhNum;
                }else{

                    $khNum  =   $allKhNum;
                }

                if ($khNum >= (int)$aInfo['num']) {

                    return array('errcode' => '8', 'msg' => '客户数量已达上限，暂时无法转交新的客户~');
                } else if (($khNum + count($uid)) >= (int)$aInfo['num']){

                    $syKhNum    =   intval($aInfo['num']) - $khNum;
                    return array('errcode' => '8', 'msg' => '转交失败！当前最多还可以转移'.$syKhNum.'个客户给：'.$crm_name);
                }
            }

            if (count($uid) == 1){

                $comInfo=   $this->select_once('company', array('uid' => $data['uid']), '`crm_uid`');
                if ($comInfo['crm_uid'] != $auid){

                    return array('errcode' => 8, 'msg' => '只有企业绑定业务员本人才可以转交客户');
                }
            }

            $remark =   $data['remark'];

            $result =   $this->update_once('company', array('crm_uid' => $crm_uid, 'crm_time' => time(), 'pcrmuid' => $auid, 'crm_source' => 4), array('crm_uid' => $auid, 'uid' => array('in', pylode(',', $uid))));
            if ($result) {

                $logData    =   array();

                foreach ($uid as $k => $v){

                    $logData[$k]['auid']    =   $auid;
                    $logData[$k]['uid']     =   $v;
                    $logData[$k]['type']    =   1;
                    $logData[$k]['content'] =   "转交客户（UID：".pylode(',', $uid)."）给".$crm_name;
                    $logData[$k]['remark']  =   $remark;
                    $logData[$k]['ctime']   =   time();
                }

                $this->DB_insert_multi('crm_comlog', $logData);

                $lContent   =   "转交客户（UID：".pylode(',', $uid)."）给".$crm_name;
                $this->addCrmLog($lContent, 7, '', $auid);
            }

            $return['errcode']  =   $result ? 9 : 8;
            $msg                =   $result ? '成功' : '失败';
            $return['msg']      =   '客户转交'.$msg;
            return $return;
        }
    }

    /**
     * @desc 职位推文
     * @param array $data
     * @return array
     */
    public function addTwTask($data = array())
    {

        if (empty($data['cuid']) || empty($data['auid'])){
            return  array('errcode' => 8, 'msg' => '参数错误，请重试');
        }

        $auid       =   $data['auid'];
        $uid        =   $data['cuid'];

        $comInfo    =   $this->select_once('company', array('uid' => $data['cuid']), '`name`,`lastupdate`,`crm_uid`');

        $time       =   time();

        if (isset($data['jobid'])) {

            $jobIdArr   =   explode(',', $data['jobid']);
            $jobList    =   $this->select_all('company_job', array('uid' => $uid, 'id' => array('in', pylode(',', $jobIdArr))), '`id`,`name`,`com_name`,`sdate`');
            $valueData  =   array();

            foreach ($jobList as $k => $v) {

                $valueData[$k]['jobid']     =   $v['id'];
                $valueData[$k]['cuid']      =   $uid;
                $valueData[$k]['jobname']   =   $v['name'];
                $valueData[$k]['comname']   =   $v['com_name'];
                $valueData[$k]['jobsdate']  =   $v['sdate'];
                $valueData[$k]['auid']      =   $auid;
                $valueData[$k]['content']   =   trim($data['content']);
                $valueData[$k]['urgent']    =   $data['urgent'] == 2 ? 1 : 0;
                $valueData[$k]['wcmoments'] =   $data['wcmoments'] == 2 ? 1 : 0;
                $valueData[$k]['gzh']       =   $data['gzh'] == 2 ? 1 : 0;
                $valueData[$k]['ctime']     =   $time;
                $valueData[$k]['status']    =   0;
                $valueData[$k]['type']      =   1;
            }

            $valueData  =   array_values($valueData);

            $result     =   $this->DB_insert_multi('wxpub_twtask', $valueData);
        }else{

            $valueData  =   array(
                'cuid'      =>  $uid,
                'comname'   =>  $comInfo['name'],
                'jobsdate'  =>  $comInfo['lastupdate'],
                'auid'      =>  $auid,
                'content'   =>  trim($data['content']),
                'urgent'    =>  $data['urgent'] == 2 ? 1 : 0,
                'wcmoments'  =>  $data['wcmoments'] == 2 ? 1 : 0,
                'gzh'       =>  $data['gzh'] == 2 ? 1 : 0,
                'ctime'     =>  $time,
                'status'    =>  0,
                'type'      =>  2
            );
            $result     =   $this->insert_into('wxpub_twtask', $valueData);
        }

        if ($result) {

            if (isset($data['jobid'])){
                $content    =   '添加职位推文（ID：'.$data['jobid'].'）';
            }else{
                $content    =   '添加企业推文（ID：'.$uid.'）';
            }
            $logData    =   array(
                'auid'      =>  $auid,
                'uid'       =>  $uid,
                'type'      =>  15,
                'content'   =>  $content,
                'remark'    =>  $comInfo['crm_uid'] != $auid ? '协助添加' : ''
            );
            $this->addCrmComLog($logData);

            $lContent   =   $content;
            if ($comInfo['crm_uid'] != $auid){

                $lContent   =   $lContent.'；协助添加    ';
            }
            $this->addCrmLog($lContent, 13, $uid, $auid);
        }

        $return['errcode']  =   $result ? 9 : 8;
        $return['msg']      =   $result ? '推文任务添加成功' : '推文任务添加失败';

        return $return;
    }

    /**
     * @desc 推送历史
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getTWList($where = array(), $data = array())
    {

        $field  =   isset($data['field']) ? $data['field'] : '*';
        $list   =   $this->select_all('wxpub_twtask', $where, $field);

        if (!empty($list)) {

            $auidS  =   array();
            $jobIdS =   array();
            $comIdS =   array();
            foreach ($list as $k => $v) {
                if (!in_array($v['auid'], $auidS)){
                    $auidS[]    =   $v['auid'];
                }
                if (!in_array($v['jobid'], $jobIdS)){
                    $jobIdS[]   =   $v['jobid'];
                }
                if (!in_array($v['cuid'], $comIdS)){
                    $comIdS[]   =   $v['cuid'];
                }
            }

            $adminUser  =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $auidS))), '`uid`,`name`');

            $jobList    =   $this->select_all('company_job', array('id' => array('in', pylode(',', $jobIdS))), '`id`,`status`');

            $comTwList  =   $this->select_all('wxpub_twtask', array('status' => 1, 'type' => 2, 'cuid' => array('in', pylode(',', $comIdS)), 'groupby' => 'cuid', 'having' => array('num' => array('>', 0))), '`cuid`,count(*) as `num`');
            $jobTwList  =   $this->select_all('wxpub_twtask', array('status' => 1, 'type' => 1, 'jobid' => array('in', pylode(',', $jobIdS)), 'groupby' => 'jobid', 'having' => array('num' => array('>', 0))), '`jobid`,count(*) as `num`');

            foreach ($list as $k => $v) {

                $list[$k]['sdate_n']    =   date('Y-m-d H:i', $v['jobsdate']);
                $list[$k]['status_n']   =   $v['status'] == 1 ? '已推送' : '未推送';
                $list[$k]['ctime_n']    =   date('Y-m-d H:i', $v['ctime']);
                $list[$k]['etime_n']    =   $v['etime'] > 0 ? date('Y-m-d H:i', $v['etime']) : '--';
                $list[$k]['tsNum']      =   0;

                if ($v['type'] == 1){

                    $list[$k]['name']   =   $v['jobname'];
                    $list[$k]['type_n'] =   '职位推文';
                }else{

                    $list[$k]['name']   =   $v['comname'];
                    $list[$k]['type_n'] =   '企业推文';
                }

                foreach ($jobList as $jk => $jv){
                    if ($v['jobid'] == $jv['id']){
                        if ($jv['status'] == 1){

                            $list[$k]['jobStatus']  =   '已下架';
                        }
                    }
                }

                foreach ($adminUser as $ak => $av){
                    if ($v['auid'] == $av['uid']){

                        $list[$k]['aname']  =   $av['name'];
                    }
                }

                foreach ($comTwList as $ck => $cv){
                    if ($v['cuid'] == $cv['cuid'] && $v['type'] == 2){

                        $list[$k]['tsNum']  =   $cv['num'];
                    }
                }

                foreach ($jobTwList as $tk => $tv){
                    if ($v['jobid'] == $tv['jobid'] && $v['type'] == 1){

                        $list[$k]['tsNum']  =   $tv['num'];
                    }
                }
            }
        }

        return $list;
    }

    /**
     * @param $options
     * @return array|string|void
     */
    private function getClass($options)
    {
        if (!empty($options)) {

            include_once('cache.model.php');
            $cacheM =   new cache_model($this->db, $this->def);
            return $cacheM->GetCache($options);
        }
    }

    public function getResumeList($data = array())
    {

        if (!empty($data['sql'])) {

            $List   =   $this->DB_query_all($data['sql'], 'all');

            if (!empty($List)) {

                $cache  =   $this->getClass(array('user', 'city', 'hy', 'job'));

                $eidS   =   array();
                foreach ($List as $v) {

                    $eidS[] =   $v['id'];
                }

                $resume_city    =   $this->select_all('resume_cityclass',array('eid'=>array('in',pylode(',', $eidS))),'eid,provinceid,cityid,three_cityid');

                foreach ($List as $k => $v) {

                    $icon   =   $v['sex'] == 1 ? $this->config['sy_member_icon'] : $this->config['sy_member_iconv'];
                    $List[$k]['photo_n']    =   checkpic($v['photo'], $icon);

                    if($v['lastupdate']){
                        $ltime				=  $v['lastupdate'];
                        //今天开始时间戳
                        $beginToday			=  strtotime('today');
                        //昨天开始时间戳
                        $beginYesterday		=  strtotime('yesterday');
                        //一周内时间戳
                        if($ltime>$beginYesterday && $ltime<$beginToday){

                            $List[$k]['lastupdate_n']	=	"昨天";
                        }elseif($ltime>$beginToday){

                            $List[$k]['lastupdate_n']	=	lastupdateStyle($ltime);
                        }else{

                            $List[$k]['lastupdate_n']	=	date("Y-m-d",$ltime);
                        }
                    }

                    $List[$k]['age_n']      =   date('Y') - date('Y', strtotime($v['birthday']));
                    $List[$k]['sex_n']      =   $cache['user_sex'][$v['sex']];
                    $List[$k]['edu_n']      =   $cache['userclass_name'][$v['edu']];
                    $List[$k]['exp_n']      =   $cache['userclass_name'][$v['exp']];
                    $List[$k]['salary']     =   salaryUnit($v['minsalary'], $v['maxsalary']);
                    $List[$k]['report_n']   =   $cache['userclass_name'][$v['report']];
                    $List[$k]['type_n']     =   $cache['userclass_name'][$v['type']];

                    if ($resume_city){

                        $city_classid   =   explode(',',$v['city_classid']);

                        foreach($resume_city as $val){

                            if($v['id'] == $val['eid']){

                                if($val['provinceid'] == $city_classid[0]){

                                    $List[$k]['city_n']  =  $cache['city_name'][$city_classid[0]];
                                }else if($val['cityid']==$city_classid[0]){

                                    $List[$k]['city_n']  =  $cache['city_name'][$val['provinceid']].'-'.$cache['city_name'][$city_classid[0]];
                                }else if($val['three_cityid']==$city_classid[0]){

                                    $List[$k]['city_n']  =  $cache['city_name'][$val['provinceid']].'-'.$cache['city_name'][$val['cityid']].'-'.$cache['city_name'][$city_classid[0]];
                                }
                            }
                        }
                    }
                }
            }
            return $List;
        }
    }

    /**
     * @desc 添加推荐候选人
     * @param array $data
     * @return array
     */
    public function addLinkResume($data = array())
    {

        if (empty($data['eid']) || empty($data['uid']) || empty($data['auid'])){

            return array('errcode' => 8, 'msg' => '参数错误，请重试！');
        } else {

            $auid   =   intval($data['auid']);
            $uid    =   intval($data['uid']);
            $jobId  =   intval($data['jobid']);

            $eidArr =   explode(',', $data['eid']);

            $enum   =   count($eidArr);

            $comInfo=   $this->select_once('company', array('uid' => $uid), '`crm_uid`');

            $value  =   array(

                'uid'   =>  $uid,
                'jobid' =>  $jobId,
                'eid'   =>  $data['eid'],
                'enum'  =>  $enum,
                'auid'  =>  $comInfo['crm_uid'],
                'atime' =>  time(),
                'ids'   =>  '1'.time().mt_rand(00, 99)
            );

            if ($data['auto'] == 1){

                $value['auto']          =   1;
                $value['interval']      =   $data['interval'];
                $value['uptime']        =   time();
                $value['search_info']   =   $data['search_info'];
            }else{

                $value['auto']          =   2;
                $value['interval']      =   '';
                $value['uptime']        =   '';
                $value['search_info']   =   '';
            }

            $nid    =   $this->insert_into('crm_resume', $value);

            if ($nid) {

                $logData    =   array(
                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  13,
                    'content'   =>  '推荐简历，推荐记录ID：'.$nid,
                    'remark'    =>  $comInfo['crm_uid'] != $auid ? '协助推送' : ''
                );

                $this->addCrmComLog($logData);

                $lContent   =   '推荐简历；推荐记录ID：'.$nid;
                if ($comInfo['crm_uid'] != $auid){

                    $lContent   =   $lContent.'（协助推送）';
                }
                $this->addCrmLog($lContent, 15, $uid, $auid);

                include_once LIB_PATH.'shortLink.php';
                $str    =   b10to62($value['ids']);
                if ($this->config['sy_seo_rewrite'] == 1) {

                    $wapUrl = $this->config['sy_weburl'].'/u/'.$str;
                } else {

                    $wapUrl = $this->config['sy_weburl'].'/index.php?m=upush&str='.$str;
                }

                $return =   array(
                    'errcode'   =>  9,
                    'msg'       =>  '推送连接设置成功',
                    'wap_url'   =>  $wapUrl
                );
            } else {

                $return =   array(
                    'errcode'   =>  8,
                    'msg'       =>  '推送连接设置失败'
                );
            }
            return $return;
        }
    }

    /**
     * @desc 更新推荐记录
     * @param array $data
     * @param array $where
     * @return array
     */
    public function upLinkResume($data = array(), $where = array())
    {

        if (empty($data['eid']) || empty($data['uid']) || empty($data['auid']) || empty($where)){

            return array('errcode' => 8, 'msg' => '参数错误，请重试！');
        } else {

            $recInfo=   $this->select_once('crm_resume', $where);

            $auid   =   intval($data['auid']);
            $uid    =   intval($data['uid']);
            $jobId  =   intval($data['jobid']);

            $eidArr =   explode(',', $data['eid']);

            $enum   =   count($eidArr);

            $comInfo=   $this->select_once('company', array('uid' => $uid), '`crm_uid`');

            $value  =   array(

                'uid'   =>  $uid,
                'jobid' =>  $jobId,
                'eid'   =>  $data['eid'],
                'enum'  =>  $enum,
                'auid'  =>  $comInfo['crm_uid'],
                'atime' =>  time()
            );

            if ($data['auto'] == 1){

                $value['auto']          =   1;
                $value['interval']      =   $data['interval'];
                $value['uptime']        =   time();
                $value['search_info']   =   $data['search_info'];
            }else{

                $value['auto']          =   2;
                $value['interval']      =   '';
                $value['uptime']        =   '';
                $value['search_info']   =   '';
            }

            $result =   $this->update_once('crm_resume', $value, $where);

            if ($result) {

                $logData    =   array(
                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  13,
                    'content'   =>  '推荐简历，更新推荐数据（ID：'.$recInfo['id'].'）',
                    'remark'    =>  $comInfo['crm_uid'] != $auid ? '协助推送' : ''
                );

                $this->addCrmComLog($logData);

                $lContent   =   '推荐简历，更新推荐内容（ID：'.$recInfo['id'].'）';
                if ($comInfo['crm_uid'] != $auid){

                    $lContent   =   $lContent.'（协助推送）';
                }
                $this->addCrmLog($lContent, 15, $uid, $auid);

                include_once LIB_PATH.'shortLink.php';
                $str    =   b10to62($recInfo['ids']);
                if ($this->config['sy_seo_rewrite'] == 1) {

                    $wapUrl = $this->config['sy_weburl'].'/u/'.$str;
                } else {

                    $wapUrl = $this->config['sy_weburl'].'/index.php?m=upush&str='.$str;
                }

                $return =   array(
                    'errcode'   =>  9,
                    'msg'       =>  '推送连接设置成功',
                    'wap_url'   =>  $wapUrl
                );
            } else {

                $return =   array(
                    'errcode'   =>  8,
                    'msg'       =>  '推送连接设置失败'
                );
            }
            return $return;
        }
    }

    /**
     * @desc CRM管理客户，发送推荐候选人连接
     * @param array $data
     * @return array
     */
    function sendResumeMsg($data = array())
    {

        if (!empty($data)) {

            if (empty($data['uid']) || empty($data['auid']) || empty($data['mobile'])|| empty($data['content'])) {

                return array('errcode' => 8, 'msg' => '参数错误，请重试！');
            }

            $auid   =   intval($data['auid']);
            $uid    =   intval($data['uid']);

            require_once 'notice.model.php';
            $noticeM=   new notice_model($this->db, $this->def);

            $msgData    =   array(
                'mobile'    =>  $data['mobile'],
                'content'   =>  $data['content'],
                'uid'       =>  $uid,
                'cuid'      =>  $auid,
                'port'      =>  5
            );

            $res    =   $noticeM->sendSMS($msgData);

            if ($res['status'] == 1){

                $comInfo    =   $this->select_once('company', array('uid' => $data['uid']), '`crm_uid`');

                $logData    =   array(

                    'auid'      =>  $auid,
                    'uid'       =>  $uid,
                    'type'      =>  11,
                    'content'   =>  '发送短信给客户',
                    'remark'    =>  '推荐简历，短信内容：'.$data['content']
                );
                $this->addCrmComLog($logData);
                $lContent   =   "推送简历，发送推文短信：".$data['content'];
                $this->addCrmLog($lContent, 14, $uid, $auid);

                $return['errcode']  =   9;
                $return['msg']      =   '短信发送成功';
            }else{

                $return['errcode']  =   8;
                $return['msg']      =   $res['msg'];
            }
            return $return;
        }
    }

    /**
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getRecLogList($where = array(), $data = array())
    {

        $field  =   $data['field'] ? $data['field'] : '*';
        $List   =   $this->select_all('crm_resume', $where, $field);

        if (!empty($List)) {

            if (isset($data['type']) && $data['type'] == 'info'){
                $eidArr =   $uidArr =   array();
                foreach ($List as $k => $v){
                    $eidArr[]   =   array_merge($eidArr, explode(',', $v['eid']));
                    if (!in_array($v['uid'], $uidArr)){

                        $uidArr[]   =   $v['uid'];
                    }
                }
                $eidArr     =   array_unique($eidArr);
                $downList   =   $this->select_all('down_resume', array('eid' => array('in', pylode(',', $eidArr)), 'comid' => array('in', pylode(',', $uidArr))));
            }

            include_once LIB_PATH.'shortLink.php';
            foreach ($List as $k => $v) {

                $List[$k]['time_n'] = date('Y-m-d H:i:s', $v['atime']);
                $List[$k]['date_n'] = date('Y-m-d', $v['atime']);
                $str = b10to62($v['ids']);
                if ($this->config['sy_seo_rewrite'] == 1) {

                    $url = $this->config['sy_weburl'].'/u/'.$str;
                } else {

                    $url = $this->config['sy_weburl'].'/index.php?m=upush&str='.$str;
                }
                $List[$k]['url'] = $url;

                $List[$k]['downNum']    =   0;
                if (!empty($downList)){
                    foreach ($downList as $dk => $dv){

                        if ($dv['downtime'] > $v['atime'] && $dv['comid'] == $v['uid'] && in_array($dv['eid'], explode(',', $v['eid']))){

                            $List[$k]['downNum']++;
                        }
                    }
                }
            }
        }
        return $List;
    }

    private function autoUpResume($data = array())
    {

        $search =   unserialize($data['search_info']);

        $comId      =   $data['uid'];
        $jobId      =   $data['id'];

        $noUidS = $blackUidS = $applyUidS = $inviteUidS = array();

        $blackList  =   $this->select_all('blacklist', array('p_uid' => $comId), '`cuid`');
        if (!empty($blackList)) {
            foreach ($blackList as $v) {
                $blackUidS[] = $v['c_uid'];
            }
        }
        $applyList  =   $this->select_all('userid_job', array('job_id' => $jobId), '`uid`');
        if (!empty($applyList)) {
            foreach ($applyList as $v) {
                $applyUidS[] = $v['uid'];
            }
        }
        $inviteList =   $this->select_all('userid_msg', array('fid' => $comId), '`uid`');
        if (!empty($inviteList)) {
            foreach ($inviteList as $v) {
                $inviteUidS[] = $v['uid'];
            }
        }
        $noUidS     =   array_values(array_merge($blackUidS, $applyUidS, $inviteUidS));

        $where = 'a.defaults = 1 and a.state = 1 and a.status = 1 and a.r_status = 1';

        if (!empty($noUidS)){

            $where  .=  ' and a.`uid` NOT IN ('.@implode(',', $noUidS).')';
        }

        $city_job_class = '';

        if ($search['job'] || $search['city']) {

            include_once(PLUS_PATH.'city.cache.php');
            include_once(PLUS_PATH.'cityparent.cache.php');
            include_once(PLUS_PATH.'job.cache.php');
            include_once(PLUS_PATH.'jobparent.cache.php');
            $city_col = $job_col = '';
            $cjwhere = '';
            if ($search['job']) {
                if ($job_parent[$search['job']] == '0') {

                    $job_col = "job1";
                    $cjwhere .= "$job_col = {$search['job']}";
                } elseif (in_array($job_parent[$_POST['job_class']], $job_index)) {

                    $job_col = "job1_son";
                    $cjwhere .= "$job_col = {$search['job']}";
                } elseif ($job_parent[$_POST['job_class']] > 0) {

                    $job_col = "job_post";
                    $cjwhere .= "$job_col = {$search['job']}";
                }
            }
            if ($search['city']) {

                $cjand = $cjwhere ? ' AND ' : '';

                if ($city_parent[$search['city']] == '0') {

                    $city_col = "provinceid";
                    $cjwhere .= "{$cjand}$city_col = {$search['city']}";
                } elseif (in_array($city_parent[$search['city']], $city_index)) {

                    $city_col = "cityid";
                    $cjwhere .= "{$cjand}$city_col = {$search['city']}";
                } elseif ($city_parent[$search['city']] > 0) {

                    $city_col = "three_cityid";
                    $cjwhere .= "{$cjand}$city_col = {$search['city']}";
                }
            }
            if ($city_col || $job_col) {
                if ($city_col && $job_col) {

                    $cjwhere .= " AND {$city_col}_{$job_col}_num = 1";
                } elseif ($city_col) {

                    $cjwhere .= " AND {$city_col}_num = 1";
                } elseif ($job_col) {

                    $cjwhere .= " AND {$job_col}_num = 1";
                }
            }
            $city_job_class = ",(select `eid` from `" . $this->def . "resume_city_job_class` where $cjwhere) cj";
            $where .= " and a.id = cj.eid";
        }
        if ($search['edu']) {
            $where .= " AND a.`edu` =" . $search['edu'];
        }
        if ($search['word']) {
            $where .= " AND a.`exp` =" . $search['word'];
        }
        if ($search['label']) {
            $where .= ' and a.label=' . intval($search['label']);
        }
        if ($search['integrity']) {
            if ($_POST['integrity'] == 55) {
                $where .= " and a.integrity = 55";
            } else if ($_POST['integrity'] == 65) {
                $where .= " and a.integrity = 65";
            } else {
                $integrity_val = $arr_data['integrity_val'];
                $where .= " and a.integrity >= " . $integrity_val[$search['integrity']];
            }
        }
        if ($search['time']) {
            $timeStr = intval($search['time']);
            if ($timeStr == 1) {
                $where .= " and a.ctime > " . strtotime('today');
            } else if ($timeStr == 2) {
                $where .= " and a.ctime > " . strtotime('-3 days');
            } else if ($timeStr == 3) {
                $where .= " and a.ctime > " . strtotime('-7 days');
            } else if ($timeStr == 4) {
                $where .= " and a.ctime > " . strtotime('-30 days');
            }
        }
        if ($search['keyword']) {

            require_once ('resume.model.php');
            $resumeM = new resume_model($this->db, $this->def);

            $keywordStr = trim($search['keyword']);
            $keywordArr = explode(' ', $keywordStr);

            if (count($keywordArr) > 1) {
                $workWhere['PHPYUNBTWSTART_work'] = '';
                foreach ($keywordArr as $kv) {

                    $workWhere['name'][] = array('like', trim($kv), 'OR');
                    $workWhere['title'][] = array('like', trim($kv), 'OR');
                }
                $workWhere['PHPYUNBTWEND_work'] = '';
            } else {
                $workWhere = array(
                    'name' => array('like', $keywordStr),
                    'title' => array('like', $keywordStr, 'OR'),
                );
            }
            $work = $resumeM->getResumeWorks($workWhere, 'eid');
            if ($work) {
                $eids = array();
                foreach ($work as $v) {
                    $eids[] = $v['eid'];
                }
            }
            if (count($keywordArr) > 1) {
                $eduWhere['PHPYUNBTWSTART_edu'] = '';
                foreach ($keywordArr as $kv) {

                    $eduWhere['name'][] = array('like', trim($kv), 'OR');
                    $eduWhere['title'][] = array('like', trim($kv), 'OR');
                }
                $eduWhere['PHPYUNBTWEND_edu'] = '';
            } else {
                $eduWhere = array(

                    'name' => array('like', $keywordStr),
                    'specialty' => array('like', $keywordStr, 'OR')
                );
            }
            $edu = $resumeM->getResumeEdus($eduWhere, 'eid');
            if ($edu) {
                $eids = array();
                foreach ($edu as $v) {
                    $eids[] = $v['eid'];
                }
            }

            require_once ('userinfo.model.php');
            $userInfoM = new userinfo_model($this->db, $this->def);

            if (count($keywordArr) > 1) {
                $mwhere['PHPYUNBTWSTART_mem'] = '';
                foreach ($keywordArr as $kv) {

                    $eduWhere['description'][] = array('like', trim($kv), 'OR');
                }
                $mwhere['PHPYUNBTWEND_mem'] = '';
            } else {
                $mwhere['description'] = array('like', $keywordStr);
            }
            if (!empty($mwhere)) {
                $uidList = $userInfoM->getUserInfoList($mwhere, array('usertype' => 1, 'field' => '`uid`'));
                if (!empty($uidList)) {
                    foreach ($uidList as $uv) {
                        $mUids[] = $uv['uid'];
                    }
                }
            }
            if (count($keywordArr) > 1) {

                $where .= " and (";

                foreach ($keywordArr as $k => $kv) {

                    if ($k + 1 < count($keywordArr)) {

                        $where .= " a.name like '%$kv%' or a.content like '%$kv%' or";
                    } else {

                        $where .= " a.name like '%$kv%' or a.content like '%$kv%'";
                    }
                }
            } else {

                $where .= " and (a.name like '%$keywordStr%' or a.content like '%$keywordStr%'";
            }

            if (!empty($mUids)) {
                $where .= " or a.uid in (" . pylode(',', $mUids) . ")";
            }
            if (!empty($eids)) {
                $where .= " or a.id in (" . pylode(',', $eids) . ")";
            }
            $where .= ")";
        }

        $countSql   =   "select count(*) as num from `".$this->def."resume_expect` a{$city_job_class} where {$where}";
        $resumeArr  =   $this->DB_query_all($countSql);
        $resumeTotal=   $resumeArr['num'] ? $resumeArr['num'] : 0;

        $order      =   ' order by a.lastupdate desc';
        if ($resumeTotal > 100){

            $sql    =   "select a.* from `" . $this->def . "resume_expect` a{$city_job_class} where {$where} {$order} limit 100";
        }else{

            $sql    =   "select a.* from `" . $this->def . "resume_expect` a{$city_job_class} where {$where} {$order} limit ".$resumeTotal;
        }

        $resumeList =   $this->getResumeList(array('sql' => $sql));

        $resumeIdArr=   array();
        foreach ($resumeList as $k => $v){
            $resumeIdArr[] =   $v['id'];
        }

        if (!empty($resumeIdArr)) {

            $this->update_once('crm_resume', array('uptime' => time(), 'eid' => implode(',', $resumeIdArr), 'enum' => count($resumeIdArr)), array('id' => $data['id']));

            $Info = array();
            $Info['eid'] = implode(',', $resumeIdArr);
            $Info['enum'] = count($resumeIdArr);
            return $Info;
        }
    }

    /**
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getRecInfo($where = array(), $data = array())
    {

        $return =   array('info' => array(), 'list' => array());

        $field  =   $data['field'] ? $data['field'] : '*';
        $Info   =   $this->select_once('crm_resume', $where, $field);

        if (!empty($Info)) {

            $cache          =   $this->getClass(array('user', 'city', 'job'));
            include CONFIG_PATH.'db.data.php';
            $searchInfo     =   unserialize($Info['search_info']);

            if (isset($searchInfo['job'])){
                $Info['search_job']     =   $searchInfo['job'];
                $Info['search_job_name']=   $cache['job_name'][$searchInfo['job']];
            }
            if (isset($searchInfo['city'])){
                $Info['search_city'] =   $searchInfo['city'];
            }
            if (isset($searchInfo['keyword'])){
                $Info['search_keyword'] =   $searchInfo['keyword'];
            }
            if (isset($searchInfo['edu'])){
                $Info['search_edu'] =   $searchInfo['edu'];
                $Info['search_edu_name'] =   $cache['userclass_name'][$searchInfo['edu']];
            }
            if (isset($searchInfo['word'])){
                $Info['search_word'] =   $searchInfo['word'];
                $Info['search_word_name'] =   $cache['userclass_name'][$searchInfo['word']];
            }
            if (isset($searchInfo['label'])){
                $Info['search_label'] =   $searchInfo['label'];
                $Info['search_label_name'] =   $cache['userclass_name'][$searchInfo['label']];
            }
            if (isset($searchInfo['integrity'])){
                $Info['search_integrity'] =   $searchInfo['integrity'];
                $Info['search_label_name'] =   $arr_data['integrity_name'][$searchInfo['integrity']];
            }
            if (isset($searchInfo['time'])) {

                $Info['search_time'] = $searchInfo['time'];
                if ($searchInfo['time'] == 1) {

                    $Info['search_time_name'] = '今天';
                } else if ($searchInfo['time'] == 2) {

                    $Info['search_time_name'] = '最近三天';
                } else if ($searchInfo['time'] == 3) {

                    $Info['search_time_name'] = '最近七天';
                } else if ($searchInfo['time'] == 4) {

                    $Info['search_time_name'] = '最近一月';
                }
            }
            if ($Info['auto'] == 1 && $Info['uptime'] < strtotime('-'.$Info['interval'].' minutes')){

                //  更新简历信息
                $resumeData    =   $this->autoUpResume($Info);

                if (!empty($resumeData)) {

                    $Info['eid']  =   $resumeData['eid'];
                    $Info['enum']   =   $resumeData['enum'];
                }
                $Info['re_search']  =   1;
            }

            $crmInfo        =   $this->select_once('admin_user', array('uid' => $Info['auid']), '`name`,`moblie`,`photo`,`weixin`,`ewm`');

            $Info['crm_photo']  =   checkpic($crmInfo['photo']);
            $Info['crm_name']   =   $crmInfo['name'];
            $Info['crm_phone']  =   $crmInfo['moblie'];

            if (empty($crmInfo['ewm']) && empty($crmInfo['weixin'])){

                $Info['w_show']     =   2;
                if (!empty($crmInfo['ewm'])) {
                    $Info['crm_ewm']=   checkpic($crmInfo['ewm']);
                }
                if (!empty($crmInfo['weixin'])) {
                    $Info['crm_wx'] =   $crmInfo['weixin'];
                }
            }else{

                $Info['w_show']     =   1;
                $Info['crm_ewm']    =   checkpic($crmInfo['ewm']);
                $Info['crm_wx']     =   $crmInfo['weixin'];
            }

            $Info['crm_address']    =   $this->config['sy_webadd'];


            include_once LIB_PATH.'shortLink.php';
            $str            =   b10to62($Info['ids']);
            if ($this->config['sy_seo_rewrite'] == 1) {

                $Info['link'] = $this->config['sy_weburl'].'/u/'.$str;
            } else {

                $Info['link'] = $this->config['sy_weburl'].'/index.php?m=upush&str='.$str;
            }

            if (isset($data['from']) && $data['from'] == 'u'){

                $List   =   $this->select_all('resume_expect', array('id' => array('in', $Info['eid']), 'defaults' => 1, 'state' => 1, 'status' => 1, 'r_status' => 1, 'orderby' => 'lastupdate,desc'));
            }else{

                $List   =   $this->select_all('resume_expect', array('id' => array('in', $Info['eid']), 'orderby' => 'lastupdate'));
            }

            if (!empty($List)) {

                $cache  =   $this->getClass(array('user', 'city', 'hy', 'job'));

                $eidS   =   array();
                foreach ($List as $v) {

                    $eidS[] = $v['id'];
                }

                $resume_city = $this->select_all('resume_cityclass', array('eid' => array('in', pylode(',', $eidS))), 'eid,provinceid,cityid,three_cityid');

                foreach ($List as $k => $v) {

                    if ($k == 0) {

                        $Info['time_n']     =   lastupdateStyle($v['lastupdate']);
                    }
                    $List[$k]['url']        =   Url('wap', array('c' => 'resume', 'a' => 'show', 'id' => $v['id']));

                    $icon                   =   $v['sex'] == 1 ? $this->config['sy_member_icon'] : $this->config['sy_member_iconv'];
                    $List[$k]['photo_n']    =   checkpic($v['photo'], $icon);

                    if ($v['lastupdate']) {

                        $ltime              =   $v['lastupdate'];

                        $beginToday         =   strtotime('today');         //今天开始时间戳

                        $beginYesterday     =   strtotime('yesterday');     //昨天开始时间戳
                        //一周内时间戳
                        if ($ltime > $beginYesterday && $ltime < $beginToday) {

                            $List[$k]['lastupdate_n']   =   '昨天';
                        } elseif ($ltime > $beginToday) {

                            $List[$k]['lastupdate_n']   =   lastupdateStyle($ltime);
                        } else {

                            $List[$k]['lastupdate_n']   =   date('Y-m-d', $ltime);
                        }
                    }

                    $List[$k]['age_n']      =   date('Y') - date('Y', strtotime($v['birthday']));
                    $List[$k]['sex_n']      =   $cache['user_sex'][$v['sex']];
                    $List[$k]['edu_n']      =   $cache['userclass_name'][$v['edu']];
                    $List[$k]['exp_n']      =   $cache['userclass_name'][$v['exp']];
                    $List[$k]['salary']     =   salaryUnit($v['minsalary'], $v['maxsalary']);
                    $List[$k]['report_n']   =   $cache['userclass_name'][$v['report']];
                    $List[$k]['type_n']     =   $cache['userclass_name'][$v['type']];

                    if ($resume_city) {

                        $cityClassId        =   explode(',', $v['city_classid']);

                        foreach ($resume_city as $val) {

                            if ($v['id'] == $val['eid']) {

                                if ($val['provinceid'] == $cityClassId[0]) {

                                    $List[$k]['city_n'] =   $cache['city_name'][$cityClassId[0]];
                                } else if ($val['cityid'] == $cityClassId[0]) {

                                    $List[$k]['city_n'] =   $cache['city_name'][$val['provinceid']].'-'.$cache['city_name'][$cityClassId[0]];
                                } else if ($val['three_cityid'] == $cityClassId[0]) {

                                    $List[$k]['city_n'] =   $cache['city_name'][$val['provinceid']].'-'.$cache['city_name'][$val['cityid']].'-'.$cache['city_name'][$cityClassId[0]];
                                }
                            }
                        }
                    }
                }

                $List       =   $this->getEduWorkData($List, $cache['userclass_name']);
            }

            if (isset($data['type']) && $data['type'] == 'info'){

                $Info['downNum']   =   $this->select_num('down_resume', array('eid' => array('in', $Info['eid']), 'comid' => $Info['uid'], 'downtime' => array('>', $Info['atime'])));
            }
            if (isset($data['from']) && $data['from'] == 'u') {
                $Info['showNum'] = count($List);
            }
            $return['list'] =   $List;

            $return['info'] =   $Info;
        }

        return $return;
    }

    /**
     * @desc 查询简历：教育经历、工作经历
     * @param $List
     * @param $className
     * @return mixed
     */
    private function getEduWorkData($List, $className)
    {

        $eidS       =   array();
        foreach ($List as $v) {

            $eidS[] =   $v['id'];
        }

        if (!empty($eidS)) {

            $eduList    =   $this->select_all('resume_edu', array('eid' => array('in', pylode(',', $eidS))));
            $workList   =   $this->select_all('resume_work', array('eid' => array('in', pylode(',', $eidS))));
        }

        $EduWorkList    =   array();

        if (!empty($eduList)) {

            $eduListNew =   array();
            foreach ($eduList as $key => $value) {

                $eduListNew[$value['eid']][] = $value;
            }
            foreach ($eduListNew as $ek => $ev) {
                $eduMin     =   0;
                $eduMax     =   0;
                $eduTitle   =   array();
                $education  =   array();
                foreach ($ev as $v1) {

                    $eduMin =   $v1['sdate'];
                    if ($v1['edate'] == 0) {

                        $eduMax =   0;
                    } elseif ($eduMax < $v1['edate']) {

                        $eduMax =   $v1['edate'];
                    }
                    $education[]=   $className[$v1['education']];
                    $eduTitle[] =   $v1['specialty'];
                }
                $return =   array();
                $return['edu_min']      =   date('Y.m', $eduMin);
                $return['edu_max']      =   $eduMax == 0 ? '至今' : date('Y', $eduMax);
                $return['education']    =   @implode(',', $education);
                $return['edu_time']     =   $return['edu_min'] . "-" . $return['edu_max'];
                $return['edu_specialty']=   @implode('、', $eduTitle);
                if ($return['edu_specialty']) {
                    $EduWorkList[$ek]['edu_content']    =   $return['education'].'学历 · '.$return['edu_specialty'].' · 毕业于'.$return['edu_max'].'年';
                } else {
                    $EduWorkList[$ek]['edu_content']    =   $return['education'].'学历 · 毕业于'.$return['edu_max'].'年';
                }
            }
        }

        if (!empty($workList)) {

            $workListNew    =   array();

            foreach ($workList as $key => $value) {

                $workListNew[$value['eid']][] = $value;
            }
            foreach ($workListNew as $wk => $wv) {

                $whour  =   0;
                $hour   =   array();
                $time   =   time();
                $workMin    =   0;
                $workMax    =   0;
                $workNum    =   count($wv);
                $workTitle  =   array();
                foreach ($wv as $v) {

                    if ($v['edate']) {
                        $workTime   =   ceil(($v['edate'] - $v['sdate']) / (30 * 86400));
                    } else {
                        $workTime   =   ceil(($time - $v['sdate']) / (30 * 86400));
                    }
                    /* 计算每份工作时长(按月) */
                    $workMin    =   $v['sdate'];

                    if ($v['edate'] == 0) {

                        $workMax    =   0;
                    } elseif ($workMax < $v['edate']) {

                        $workMax    =   $v['edate'];
                    }

                    $workTitle[]    =   $v['title'];

                    $hour[] =   $workTime;
                    $whour  +=  $workTime;
                }

                $workAvg    =   ceil($whour / count($hour));

                $return     =   array();

                $return['work_num']     =   $workNum > 0 ? $workNum : 0;
                $return['work_avg']     =   $workAvg > 11 ? avgToYm($workAvg) : $workAvg;
                $return['work_min']     =   date('Y.m', $workMin);
                $return['work_max']     =   $workMax == 0 ? '至今' : date('Y.m', $workMax);
                $return['work_title']   =   @implode(',', $workTitle);
                $return['work_time']    =   $return['workMin'] . '-' . $return['workMax'];
                if ($return['work_avg'] > 0) {
                    if ($return['work_title'] != '') {
                        $EduWorkList[$wk]['work_content']   =   ' 参加过'.$return['work_num'].'份工作 · 涉及' . $return['work_title'].'等岗位';
                    } else {
                        $EduWorkList[$wk]['work_content']   =   '参加过'.$return['work_num'].'份工作';
                    }
                }
            }
        }

        if (!empty($EduWorkList)) {

            foreach ($List as $k => $v) {

                $List[$k]['work_content'] = '';
                $List[$k]['edu_content'] = '';

                foreach ($EduWorkList as $ewk => $ewv) {

                    if ($ewk == $v['id']) {

                        $List[$k]['work_content']   =   $ewv['work_content'] ? $ewv['work_content'] : '';
                        $List[$k]['edu_content']    =   $ewv['edu_content'] ? $ewv['edu_content'] : '';
                    }
                }
            }
        }
        return $List;
    }

    /**
     * @desc 删除推送机简历记录
     * @param array $where
     * @return mixed
     */
    public function delRecResumeLog($where = array())
    {
        if (empty($where)) {

            return  array('errcode' => 8, 'msg' => '参数错误，请重试！');
        } else {

            $nid    =   $this->delete_all('crm_resume', $where, '');

            $msg    =   '简历推广记录';
            $return['errcode']  =   $nid ? 9 : 8;
            $return['errmsg']   =   $nid ? $msg.'删除成功！' : $msg.'删除失败！';
            return $return;
        }
    }

    private function autoUpJob($data = array())
    {

        $search =   unserialize($data['search_info']);

        $where  =   array('status' => 0, 'state' => 1, 'r_status' => 1);

        if (isset($search['edu']) && !empty($search['edu'])) {

            $where['edu'] = $search['edu'];
        }
        if (isset($search['exp']) && !empty($search['exp'])) {

            $where['exp'] = $search['exp'];
        }
        if (isset($search['report']) && !empty($search['report'])) {

            $where['report']    =   $search['report'];
        }
        if (isset($search['job']) && !empty($search['job'])) {

            $where['PHPYUNBTWSTART_A'] = '';
            $where['job1'] = array('findin', $search['job_class']);
            $where['job1_son'] = array('findin', $search['job_class'], 'OR');
            $where['job_post'] = array('findin', $search['job_class'], 'OR');
            $where['PHPYUNBTWEND_A'] = '';
        }
        if (isset($search['city']) && !empty($search['city'])) {

            $where['PHPYUNBTWSTART_B'] = '';
            $where['provinceid'] = array('findin', $search['city']);
            $where['cityid'] = array('findin', $search['city'], 'OR');
            $where['three_cityid'] = array('findin', $search['city'], 'OR');
            $where['PHPYUNBTWEND_B'] = '';
        }

        if (isset($search['stime']) && !empty($search['stime'])) {
            $timeStr = intval($search['stime']);
            if ($timeStr == 1) {
                $where['sdate'] = array('>', strtotime('today'));
            } else if ($timeStr == 2) {
                $where['sdate'] = array('>', strtotime('-3 days'));
            } else if ($timeStr == 3) {
                $where['sdate'] = array('>', strtotime('-7 days'));
            } else if ($timeStr == 4) {
                $where['sdate'] = array('>', strtotime('-30 days'));
            }
        }

        if (isset($search['time']) && !empty($search['time'])) {
            $timeStr = intval($search['time']);
            if ($timeStr == 1) {
                $where['lastupdate'] = array('>', strtotime('today'));
            } else if ($timeStr == 2) {
                $where['lastupdate'] = array('>', strtotime('-3 days'));
            } else if ($timeStr == 3) {
                $where['lastupdate'] = array('>', strtotime('-7 days'));
            } else if ($timeStr == 4) {
                $where['lastupdate'] = array('>', strtotime('-30 days'));
            }
        }

        if (isset($search['welfare']) && !empty($search['welfare'])) {

            $cache  = $this->getClass(array('com'));
            $welfare = explode('，', $search['welfare']);
            $where['PHPYUNBTWSTART_B_1'] = '';
            foreach ($welfare as $wk => $wv) {
                $where['welfare'][] =   array('like', $cache['comclass_name'][$wv], '');
            }
            $where['PHPYUNBTWEND_B_1'] = '';
        }

        if (isset($search['keyword']) && !empty($search['keyword'])) {

            $keywordStr =   trim($search['keyword']);
            $keywordArr =   explode(' ', $keywordStr);

            if (count($keywordArr) > 1){

                $where['PHPYUNBTWSTART_C'] = '';
                foreach ($keywordArr as $kv){
                    $where['com_name'][]    =   array('like', trim($kv), 'OR');
                    $where['name'][]        =   array('like', trim($kv), 'OR');
                    $where['welfare'][]     =   array('like', trim($kv), 'OR');
                }
                $where['PHPYUNBTWEND_C'] = '';
            }else{

                $where['PHPYUNBTWSTART_C'] = '';
                $where['com_name'] = array('like', trim($search['keyword']), '');
                $where['name'] = array('like', trim($search['keyword']), 'OR');
                $where['welfare'] = array('like', trim($search['keyword']), 'OR');
                $where['PHPYUNBTWEND_C'] = '';
            }
        }

        if (isset($search['salary']) && !empty($search['max_salary'])){

            $where['minsalary'] =   array('>=', $search['salary']);
        }

        $jobNum     =   $this->select_num('company_job', $where);
        $where['orderby']   =   array('lastupdate, desc');
        $where['limit'] =   $jobNum > 100 ? 100 : $jobNum;

        $jobList    =   $this->select_all('company_job', $where);

        $jobIdArr   =   array();
        foreach ($jobList as $k => $v){
            $jobIdArr[] =   $v['id'];
        }

        if (!empty($jobIdArr)) {

            $this->update_once('crm_job', array('uptime' => time(), 'jobid' => implode(',', $jobIdArr), 'jnum' => count($jobIdArr)), array('id' => $data['id']));

            $Info = array();
            $Info['jobid'] = implode(',', $jobIdArr);
            $Info['jnum'] = count($jobIdArr);
            return $Info;
        }
    }

    /**
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getRecJobInfo($where = array(), $data = array())
    {

        $return =   array('info' => array(), 'list' => array());

        $field  =   $data['field'] ? $data['field'] : '*';
        $Info   =   $this->select_once('crm_job', $where, $field);

        if (!empty($Info)) {

            $cache          =   $this->getClass(array('com', 'city', 'job'));

            $searchInfo     =   unserialize($Info['search_info']);

            if (isset($searchInfo['job'])){
                $Info['search_job']     =   $searchInfo['job'];
                $Info['search_job_name']=   $cache['job_name'][$searchInfo['job']];
            }
            if (isset($searchInfo['city'])){
                $Info['search_city'] =   $searchInfo['city'];
                $Info['search_city_name']=   $cache['job_name'][$searchInfo['city']];
            }
            if (isset($searchInfo['keyword'])){
                $Info['search_keyword'] =   $searchInfo['keyword'];
            }
            if (isset($searchInfo['edu'])){
                $Info['search_edu'] =   $searchInfo['edu'];
                $Info['search_edu_name'] =   $cache['comclass_name'][$searchInfo['edu']];
            }
            if (isset($searchInfo['exp'])){
                $Info['search_exp'] =   $searchInfo['exp'];
                $Info['search_exp_name'] =   $cache['comclass_name'][$searchInfo['exp']];
            }
            if (isset($searchInfo['report'])){
                $Info['search_report'] =   $searchInfo['report'];
                $Info['search_report_name'] =   $cache['comclass_name'][$searchInfo['report']];
            }
            if (isset($searchInfo['stime'])) {

                $Info['search_stime'] = $searchInfo['stime'];
                if ($searchInfo['stime'] == 1) {

                    $Info['search_stime_name'] = '今天';
                } else if ($searchInfo['stime'] == 2) {

                    $Info['search_stime_name'] = '最近三天';
                } else if ($searchInfo['stime'] == 3) {

                    $Info['search_stime_name'] = '最近七天';
                } else if ($searchInfo['stime'] == 4) {

                    $Info['search_stime_name'] = '最近一月';
                }
            }
            if (isset($searchInfo['time'])) {

                $Info['search_time'] = $searchInfo['time'];
                if ($searchInfo['time'] == 1) {

                    $Info['search_time_name'] = '今天';
                } else if ($searchInfo['time'] == 2) {

                    $Info['search_time_name'] = '最近三天';
                } else if ($searchInfo['time'] == 3) {

                    $Info['search_time_name'] = '最近七天';
                } else if ($searchInfo['time'] == 4) {

                    $Info['search_time_name'] = '最近一月';
                }
            }

            if (isset($searchInfo['welfare'])){
                $Info['search_wel']     =   $searchInfo['welfare'];
                $welfareArr =   explode('，', $searchInfo['welfare']);
                $welfareNameArr = array();
                foreach ($welfareArr as $wk => $wv){
                    $welfareNameArr[]   =   $cache['comclass_name'][$wv];
                }
                $Info['search_welfare'] =   implode('，', $welfareNameArr);
            }

            if (isset($searchInfo['salary'])){
                $Info['search_salary']  =   $searchInfo['salary'];
            }

            if ($Info['auto'] == 1 && $Info['uptime'] < strtotime('-'.$Info['interval'].' minutes')){

                //  更新职位信息
                $jobData    =   $this->autoUpJob($Info);

                if (!empty($jobData)) {

                    $Info['jobid']  =   $jobData['jobid'];
                    $Info['jnum']   =   $jobData['jnum'];
                }
                $Info['re_search']  =   1;
            }

            $crmInfo        =   $this->select_once('admin_user', array('uid' => $Info['auid']), '`name`,`moblie`,`photo`,`weixin`,`ewm`');

            $Info['crm_photo']  =   checkpic($crmInfo['photo']);
            $Info['crm_name']   =   $crmInfo['name'];
            $Info['crm_phone']  =   $crmInfo['moblie'];

            if (empty($crmInfo['ewm']) && empty($crmInfo['weixin'])){

                $Info['w_show']     =   2;
                if (!empty($crmInfo['ewm'])) {
                    $Info['crm_ewm']=   checkpic($crmInfo['ewm']);
                }
                if (!empty($crmInfo['weixin'])) {
                    $Info['crm_wx'] =   $crmInfo['weixin'];
                }
            }else{

                $Info['w_show']     =   1;
                $Info['crm_ewm']    =   checkpic($crmInfo['ewm']);
                $Info['crm_wx']     =   $crmInfo['weixin'];
            }

            $Info['crm_address']    =   $this->config['sy_webadd'];

            include_once LIB_PATH.'shortLink.php';
            $str            =   b10to62($Info['ids']);

            if ($this->config['sy_seo_rewrite'] == 1) {

                $Info['link'] = $this->config['sy_weburl'].'/u/'.$str;
            } else {

                $Info['link'] = $this->config['sy_weburl'].'/index.php?m=upush&str='.$str;
            }

            if (isset($data['from']) && $data['from'] == 'u') {

                $List   =   $this->select_all('company_job', array('id' => array('in', $Info['jobid']), 'status' => 0, 'r_status' => 1, 'state' => 1, 'orderby' => 'lastupdate,desc'));
            } else {

                $List   =   $this->select_all('company_job', array('id' => array('in', $Info['jobid']), 'orderby' => 'lastupdate'));
            }

            if (!empty($List)) {

                $jobIds =   array();
                foreach ($List as $v) {

                    $jobIds[] = $v['id'];
                }

                foreach ($List as $k => $v) {

                    if ($k == 0){
                        $Info['time_n']     =   lastupdateStyle($v['lastupdate']);
                    }

                    $List[$k]['url']        =   Url('wap', array('c' => 'job', 'a' => 'comapply', 'id' => $v['id']));

                    if ($v['lastupdate']) {

                        $ltime              =   $v['lastupdate'];

                        $beginToday         =   strtotime('today');         //今天开始时间戳

                        $beginYesterday     =   strtotime('yesterday');     //昨天开始时间戳
                        //一周内时间戳
                        if ($ltime > $beginYesterday && $ltime < $beginToday) {

                            $List[$k]['lastupdate_n']   =   '昨天';
                        } elseif ($ltime > $beginToday) {

                            $List[$k]['lastupdate_n']   =   lastupdateStyle($ltime);
                        } else {

                            $List[$k]['lastupdate_n']   =   date('Y-m-d', $ltime);
                        }
                    }

                    $List[$k]['edu_n']      =   $cache['comclass_name'][$v['edu']];
                    $List[$k]['exp_n']      =   $cache['comclass_name'][$v['exp']];
                    $List[$k]['salary_n']   =   salaryUnit($v['minsalary'], $v['maxsalary']);

                    if (!empty($v['welfare'])) {
                        $v['welfare']           =   str_replace(' ', '', $v['welfare']);
                        $List[$k]['welfare_n']  =   array_filter(@explode(',', trim($v['welfare'])));
                    }

                    if (!empty($v['three_cityid'])) {

                        $List[$k]['city_n'] =   $cache['city_name'][$v['provinceid']] . ' - ' . $cache['city_name'][$v['cityid']] . ' - ' . $cache['city_name'][$v['three_cityid']];
                        $List[$k]['cityStr']=   $cache['city_name'][$v['three_cityid']];
                    } else if (!empty($v['cityid'])) {

                        $List[$k]['city_n'] =   $cache['city_name'][$v['provinceid']] . ' - ' . $cache['city_name'][$v['cityid']];
                        $List[$k]['cityStr']=   $cache['city_name'][$v['cityid']];
                    } else if (!empty($v['provinceid'])) {

                        $List[$k]['city_n'] =   $cache['city_name'][$v['provinceid']];
                        $List[$k]['cityStr']=   $cache['city_name'][$v['provinceid']];
                    }
                }
            }

            if (isset($data['from']) && $data['from'] == 'u') {
                $Info['showNum'] = count($List);
            }

            $return['list'] =   $List;
            $return['info'] =   $Info;
        }

        return $return;
    }

    /**
     * @desc 添加推荐职位
     * @param array $data
     * @return array
     */
    public function addRecJob($data = array())
    {

        if (empty($data['jobid']) || empty($data['auid'])){

            return array('errcode' => 8, 'msg' => '参数错误，请重试！');
        } else {

            $auid       =   intval($data['auid']);
            $jobIdArr   =   explode(',', $data['jobid']);
            $jnum       =   count($jobIdArr);

            $value      =   array(

                'jobid' =>  $data['jobid'],
                'jnum'  =>  $jnum,
                'auid'  =>  $data['auid'],
                'atime' =>  time(),
                'ids'   =>  '2'.time().mt_rand(00, 99),
                'title' =>  $data['title'],
                'desc'  =>  $data['desc'],
                'logo'  =>  $data['logo']
            );

            if ($data['auto'] == 1){

                $value['auto']          =   1;
                $value['interval']      =   $data['interval'];
                $value['uptime']        =   time();
                $value['search_info']   =   $data['search_info'];
            }

            $nid    =   $this->insert_into('crm_job', $value);

            if ($nid) {

                $lContent   =   '推广职位，推荐记录（ID：'.$nid.'）';
                $this->addCrmLog($lContent, 16, 0, $auid);
            }

            $return['errcode']  =   $nid ? 9 : 8;
            $return['msg']      =   $nid ? '职位推荐设置成功' : '职位推荐设置失败';
            $return['ids']      =   $value['ids'];

            return $return;
        }
    }

    /**
     * @desc 更新职位推广
     * @param array $data
     * @param array $where
     * @return array
     */
    public function upRecJob($data = array(), $where = array())
    {

        if (empty($data['jobid']) || empty($data['auid']) || empty($where)){

            return array('errcode' => 8, 'msg' => '参数错误，请重试！');
        } else {

            $recInfo    =   $this->select_once('crm_job', $where);

            $auid       =   intval($data['auid']);
            $jobIdArr   =   explode(',', $data['jobid']);
            $jnum       =   count($jobIdArr);
            $value      =   array(

                'jobid' =>  $data['jobid'],
                'jnum'  =>  $jnum,
                'auid'  =>  $data['auid'],
                'atime' =>  time(),
                'title' =>  $data['title'],
                'desc'  =>  $data['desc']
            );

            if (isset($data['logo'])){
                $value['logo']  =   $data['logo'];
            }

            if ($data['auto'] == 1){

                $value['auto']          =   1;
                $value['interval']      =   $data['interval'];
                $value['uptime']        =   time();
                $value['search_info']   =   $data['search_info'];
            }else{

                $value['auto']          =   2;
                $value['interval']      =   '';
                $value['uptime']        =   '';
                $value['search_info']   =   '';
            }

            $result =   $this->update_once('crm_job', $value, $where);

            if ($result) {

                $lContent   =   '推广职位，更新推广信息（ID：'.$recInfo['id'].'）';
                $this->addCrmLog($lContent, 16, 0, $auid);
            }

            $return['errcode']  =   $result ? 9 : 8;
            $return['msg']      =   $result ? '职位推荐更新成功' : '职位推荐更新失败';
            $return['ids']      =   $recInfo['ids'];

            return $return;
        }
    }

    /**
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getRecJobLogList($where = array(), $data = array())
    {

        $field  =   $data['field'] ? $data['field'] : '*';
        $List   =   $this->select_all('crm_job', $where, $field);

        if (!empty($List)) {

            include_once LIB_PATH.'shortLink.php';
            foreach ($List as $k => $v) {

                $List[$k]['auto_n'] =   $v['auto'] == 1 ? '自动匹配' : '手动添加';

                if ($v['auto'] == 1 && $v['uptime'] < strtotime('-'.$v['interval'].' minutes')) {

                    $jobData    =   $this->autoUpJob($v);
                    if (!empty($jobData['jobid'])){
                        $List[$k]['jobid']  =   $jobData['jobid'];
                        $List[$k]['jnum']   =   $jobData['jnum'];
                    }
                }

                $List[$k]['time_n'] = date('Y-m-d H:i:s', $v['atime']);

                $str = b10to62($v['ids']);

                if ($this->config['sy_seo_rewrite'] == 1) {

                    $url = $this->config['sy_weburl'] . '/u/' . $str;
                } else {

                    $url = $this->config['sy_weburl'] . '/index.php?m=upush&str=' . $str;
                }
                $List[$k]['url'] = $url;
            }
        }
        return $List;
    }

    /**
     * @desc 删除推送岗位记录
     * @param array $where
     * @return mixed
     */
    public function delRecJobLog($where = array())
    {
        if (empty($where)) {

            return array('errcode' => 8, 'msg' => '参数错误，请重试！');
        } else {

            $nid = $this->delete_all('crm_job', $where, '');
            $return['errmsg'] = '职位推广记录';
            $return['errcode'] = $nid ? 9 : 8;
            $return['errmsg'] = $nid ? '删除成功！' : '删除失败！';
            return $return;
        }
    }

    /**
     * @desc 转换企业库
     * @param array $uids
     * @param null $auid
     * @return array|string[]
     */
    function clueCustomer($uids = array(), $auid = null)
    {

        $return =   array('errcode' => 8, 'msg' => '参数异常，请重试');

        if (empty($uids) || empty($auid)) {

            $return =   array('errcode' => '8', 'msg' => '转换失败，参数错误');
        } else {

            $result =   $this->update_once('company', array('clue' => 1), array('clue' => 2, 'uid' => array('in', pylode(',', $uids))));

            if ($result) {

                require_once ('log.model.php');
                $logM = new log_model($this->db, $this->def);

                $logContent = '线索库转企业库(UID:' .pylode(',', $uids). ')';

                $logM -> addAdminLog($logContent);
                $return = array('errcode' => '9', 'msg' => '转换成功');

            } else {
                $return = array('errcode' => '8', 'msg' => '转换失败，请稍候重试');
            }
        }

        return $return;
    }

    function inviteNum()
    {

        $arr    =   array();

        $allNum =   $this->select_num('userid_msg', array('uid' => array('>', 0), 'isdel' => 9));
        if ($allNum > 0) {
            $arr['allNum'] = $allNum;
        }

        $authNum0   =   $this->select_num('userid_msg', array('uid' => array('>', 0), 'isdel' => 9, 'status' => array('isnull')));
        if ($authNum0 > 0) {
            $arr['authNum0'] = $authNum0;
        }

        $authNum1   =   $this->select_num('userid_msg', array('uid' => array('>', 0), 'isdel' => 9, 'status' => 1));
        if ($authNum1 > 0) {
            $arr['authNum1'] = $authNum1;
        }

        $authNum2   =   $this->select_num('userid_msg', array('uid' => array('>', 0), 'isdel' => 9, 'status' => 2));
        if ($authNum2 > 0) {
            $arr['authNum2'] = $authNum2;
        }

        $authNum3   =   $this->select_num('userid_msg', array('uid' => array('>', 0), 'isdel' => 9, 'status' => 3));
        if ($authNum3 > 0) {
            $arr['authNum3'] = $authNum3;
        }

        return $arr;
    }

    /**
     * @desc 客勤 - 邀请面试
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getInviteList($where = array(), $data = array())
    {

        $field  =   isset($data['field']) ? $data['field'] : '*';

        $List   =   $this->select_all('userid_msg', $where, $field);

        $uidArr =   array();
        foreach ($List as $k => $v) {

            if (!in_array($v['uid'], $uidArr)){

                $uidArr[]   =   $v['uid'];
            }
        }

        $resumeList =   $this->select_all('resume', array('uid' => array('in', pylode(',', $uidArr))), '`uid`,`name`,`telphone`');
        foreach ($List as $k => $v){

            $List[$k]['company_url'] =  Url('company', array('c' => 'show', 'id' => $v['fid']));

            $List[$k]['job_url'] =  Url('job', array('c' => 'comapply', 'id' => $v['jobid']));

            $List[$k]['time_n'] =   date('Y-m-d', $v['datetime']);

            foreach ($resumeList as $rk => $rv){

                if ($v['uid'] == $rv['uid']){

                    $List[$k]['r_name']     =   $rv['name'];
                    $List[$k]['tel_phone']  =   $rv['telphone'];
                }
            }
        }


        return $List;
    }

    function setInviteStatus($data = array())
    {

        if (!empty($data)){

            $result =   $this->update_once('userid_msg', array('status' => intval($data['status']), 'status_remark' => $data['status_remark']), array('id' => $data['id']));

            $return =   array(

                'errcode'   =>  $result ? 9 : 8,
                'msg'       =>  $result ? '标记成功' : '标记失败'
            );
        }else{
            $return =   array('errcode' => 8, 'msg' => '参数错误');
        }

        return  $return;
    }

    /**
     * @desc 客勤 - 备注记录
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getResumeFollowList($where = array(), $data = array())
    {

        $field  =   isset($data['field']) ? $data['field'] : '*';

        $List   =   $this->select_all('resume_follow', $where, $field);

        $uidArr =   $crmUid =   array();
        foreach ($List as $k => $v) {

            if (!in_array($v['uid'], $uidArr)){

                $uidArr[]   =   $v['uid'];
            }
            if (!in_array($v['auid'], $crmUid)){

                $crmUid[]   =   $v['auid'];
            }
        }

        $resumeList =   $this->select_all('resume', array('uid' => array('in', pylode(',', $uidArr))), '`uid`,`name`');
        $crmList    =   $this->select_all('admin_user', array('uid' => array('in', pylode(',', $crmUid))), '`uid`,`name`');
        foreach ($List as $k => $v){

            $List[$k]['time_n'] =   date('Y-m-d H:i', $v['f_time']);

            foreach ($resumeList as $rk => $rv){
                if ($v['uid'] == $rv['uid']){

                    $List[$k]['r_name']     =   $rv['name'];
                }
            }
            foreach ($crmList as $ck => $cv){
                if ($v['auid'] == $cv['uid']){

                    $List[$k]['c_name']     =   $cv['name'];
                }
            }
        }
        return $List;
    }

    /**
     * @desc 删除备注记录
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function delResumeFollow($id, $data = array())
    {

        $return = array();

        if (!empty($id)) {

            $where  =   array();
            if (!empty($id)) {
                if (is_array($id)) {

                    $ids = $id;
                    $return['layertype'] = 1;
                } else {

                    $ids = @explode(',', $id);
                    $return['layertype'] = 0;
                }

                $ids = pylode(',', $ids);
                $where['id'] = array('in', $ids);
            }

            if ($data['norecycle'] == '1') {        //	数据库清理，不插入回收站

                $return['id'] = $this->delete_all('resume_follow', $where, $data['limit'], '', '1');
            } else {

                $return['id'] = $this->delete_all('resume_follow', $where, '');
            }

            $return['msg'] = '客勤备注记录(ID:' . pylode(',', $id) . ')';
            $return['errcode'] = $return['id'] ? '9' : '8';
            $return['msg'] = $return['id'] ? $return['msg'] . '删除成功！' : $return['msg'] . '删除失败！';
        } else {

            $return['msg'] = '请选择您要删除的数据！';
            $return['errcode'] = 8;
        }

        return $return;
    }

    public function getResumeInfo($resumeId, $data = array())
    {

        require_once 'resume.model.php';
        $resumeM = new resume_model($this->db, $this->def);
        $resume = $resumeM->getInfoByEid(array('eid' => $resumeId, 'from' => 'crm_resume_info'));

        return  $resume;
    }

    /**
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getResumeApplyList($where = array(), $data = array())
    {

        $field  =   isset($data['field']) ? $data['field'] : '*';

        $List   =   $this->select_all('userid_job', $where, $field);

        foreach ($List as $k => $v){

            $List[$k]['time_n'] =   date('Y-m-d H:i', $v['datetime']);

        }
        return $List;
    }

    /**
     * 获取标签设置列表
     */
    public function getBqsetList($where)
    {
        $list = $this->select_all('crm_bqset', $where);

        if ($list) {
            foreach ($list as &$val) {
                $val['crm_status'] = explode(',', $val['crm_status']);
            }
        }

        return $list ? $list : array();
    }

    /**
     * 添加标签设置
     */
    public function addBqset($data)
    {
        return $this->insert_into('crm_bqset', $data);
    }

    /**
     * 修改标签设置
     */
    public function updateBqset($data, $where)
    {
        return $this->update_once('crm_bqset', $data, $where);
    }

    /**
     * 删除标签设置
     */
    public function delBqset($where)
    {
        return $this->delete_all('crm_bqset', $where);
    }
}
?>