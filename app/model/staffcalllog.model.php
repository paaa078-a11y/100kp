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
class staffcalllog_model extends model{

    /*
     * 新增外呼记录
     */
    function addData($data){
        $data['ctime'] = time();
        $r = $this->insert_into("staff_call_log", $data);
        return $r;
    }

    /**
     * @desc 修改记录
     */
    public function upInfo($whereData, $data = array()){
        if(!empty($whereData)) {
            if ($data && is_array($data)){
                if (isset($whereData['id']) && $whereData['id']) {
                    $where = array('id' => intval($whereData['id']));
                }
                $nid = $this->update_once('staff_call_log',$data, $where);
            }
            return $nid;
        }
    }

    /**
     * 查询全部信息
     * @param 引用字段：$whereData：条件  2:$data:查询字段
     *
     */
    public function getList($whereData, $data = array()) {
        $data['field'] = empty($data['field']) ? '*' : $data['field'];
        $list =	$this->select_all('staff_call_log', $whereData, $data['field']);
        if (!empty($list)) {
            /* 处理后台所需数据 */
            include (APP_PATH."/config/db.data.php");
            $c_uids = array();// company表uid
            $r_uids = array();// resume表uid
            $m_uids = array();// member表uid
            foreach ($list as $value) {
                if ($value['usertype'] == 2) {
                    $value['cuid'] > 0 && !in_array($value['cuid'], $c_uids) && array_push($c_uids, $value['cuid']);
                } else if ($value['usertype'] == 1) {
                    $value['cuid'] > 0 && !in_array($value['cuid'], $r_uids) && array_push($r_uids, $value['cuid']);
                } else {
                    $value['cuid'] > 0 && array_push($m_uids, $value['cuid']);
                }
            }
            $companys = $resumes = $members = array();
            if (!empty($c_uids)) {
                $companysTmp = $this->select_all('company', array('uid' => array('in', pylode(',', $c_uids))), '`uid`,`name`');
                foreach ($companysTmp as $v) {
                    $companys[$v['uid']] = $v['name'];
                }
            }
            if (!empty($r_uids)) {
                $resumesTmp = $this->select_all('resume', array('uid' => array('in', pylode(',', $r_uids))), '`uid`,`name`');
                foreach ($resumesTmp as $v) {
                    $resumes[$v['uid']] = $v['name'];
                }
            }
            if (!empty($m_uids)) {
                $membersTmp = $this->select_all('member', array('uid' => array('in', pylode(',', $m_uids))), '`uid`,`username`,`usertype`');
                foreach ($membersTmp as $v) {
                    if ($v['usertype'] == 1) {
                        $type = '个人';
                    } else if($v['usertype'] == 2) {
                        $type = '企业';
                    } else if($v['usertype'] == 3) {
                        $type = '猎头';
                    } else if($v['usertype'] == 4) {
                        $type = '培训';
                    }
                    $members[$v['uid']] = $v['username'] . ' [' . $type . ']';
                }
            }
            foreach ($list as $k => $v){
                if ($v['usertype'] == 2) {// 企业
                    $companyName = isset($companys[$v['cuid']]) ? $companys[$v['cuid']] : '企业未入驻';
                    $companyName == '' && $companyName = '未完善企业信息';
                    $list[$k]['callee_name'] = $companyName;
                } else if ($v['usertype'] == 1) {// 个人
                    $resumeName = isset($resumes[$v['cuid']]) ? $resumes[$v['cuid']] : '个人未入驻';
                    $resumeName == '' && $resumeName = '未完善个人信息';
                    $list[$k]['callee_name'] = $resumeName;
                } else {
                    $list[$k]['callee_name'] = isset($members[$v['cuid']]) ? $members[$v['cuid']] : '未入驻';
                }
                $list[$k]['ctime_n']	= date('Y-m-d H:i:s',$v['ctime']);
                $list[$k]['connect_time_n']	= date('Y-m-d H:i:s',$v['connect_time']);
                $list[$k]['disconnect_time_n']	= date('Y-m-d H:i:s',$v['disconnect_time']);
                $list[$k]['time_length_n'] = $this->timeFormat($v['time_length']);
                if ($v['has_session'] == 0) {
                    $errMsg = '呼叫失败';
                    if ($v['api_res']) {
                        $apiRes = json_decode($v['api_res'], 1);
                        $errMsg = isset($apiRes['message']) && $apiRes['message'] ? $apiRes['message'] : '呼叫失败';
                    }
                    $list[$k]['err_msg'] = $errMsg;
                }
                if (isset($data['utype']) && $data['utype'] == 'admin') {
                    //状态
//                    $list[$k]['audit_time_n'] = $v['audit_time'] ? date('Y-m-d H:i:s',$v['audit_time']) : '-';
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
     * 获取记录详情
     * $whereData 查询条件
     * $data 自定义处理数组
     */
    public function getInfo($where, $data=array()){
        $field = empty($data['field']) ? '*' : $data['field'];
        if (!empty($where)) {
            if (isset($where['id']) && $where['id']) {
                $info =	$this->select_once('staff_call_log',array('id' => intval($where['id'])), $field);
            } else if (isset($where['seq']) && $where['seq']) {
                $info =	$this->select_once('staff_call_log',array('seq' => $where['seq']), $field);
            }
            if($info && is_array($info)){
                return $info;
            }
        }
    }

    /*
     * 删除记录
     */
    public function del($id){
        if (!$id) {
            $return['msg'] = '请选择要删除的记录';
            $return['errcode'] = '8';
            return $return;
        }
        $info = $this->select_once('staff_call_log', array('id'=>intval($id)));
        if (!$info) {
            $return['msg'] = '外呼记录(ID:' . $id . ')不存在';
            $return['errcode'] = '8';
            return $return;
        }
        $r = $this->delete_all('staff_call_log', array('id'=>$info['id']));
        if($r){
            $return['msg'] = '外呼记录(ID:' . $id . ')删除成功';
            $return['errcode'] = '9';
        } else {
            $return['msg'] = '外呼记录(ID:' . $id . ')删除失败';
            $return['errcode'] = '8';
        }
        return $return;
    }

    // 外呼次数数目
    function getCallNum($Where = array()){
        return $this->select_num('staff_call_log', $Where);
    }

    /*
     * 更新外呼记录
     */
    function refreshCallLog(){
        $where['session_id'] = array('<>', '');
        $where['PHPYUNBTWSTART_A'] = '';
        $where['status'][] = array('in', '1,2', 'AND');
        $where['oss_record_path'][] = array('=', '', 'OR');
        $where['PHPYUNBTWEND_A'] = '';
        $where['limit'] = 500;
        $data = $this->getList($where, array('field' => '`session_id`,`id`'));
        if (count($data) == 0) {
            return ['code' =>  400, 'msg' => '暂无要更新的通话记录！'];
        }
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            return ['code' =>  400, 'msg' => '请先完成外呼配置！'];
        }
        $sids = array();
        $sessionToId = array();
        foreach ($data as $v) {
            !in_array($v['session_id'], $sids) && array_push($sids, $v['session_id']);
            $sessionToId[$v['session_id']] = $v['id'];
        }
        $apiurl = 'https://u.phpyun.com/upcalldtl';
        $params = array(
            'appKey' => $appkey,
            'appSecret' => $appsecret,
            'sessionIds' => json_encode($sids)
        );
        $res = CurlPost($apiurl, $params, 1);
        $resArr = json_decode($res, 1);
        if ($resArr['code'] != 200) {
            return ['code' =>  400, 'msg' => $resArr['message']];
        }
        $upCnt = 0;// 更新数量
        foreach ($resArr['data'] as $v) {
            if (isset($sessionToId[$v['session_id']])) {
                $r = $this->upInfo(array('id' => $sessionToId[$v['session_id']]), array(
                    'time_length' => $v['time_length'],
                    'connect_time' => $v['connect_time'],
                    'disconnect_time' => $v['disconnect_time'],
                    'status' => $v['status'],
                    'call_status' => $v['call_status'],
                    'oss_record_path' => $v['oss_record_path'],
                    'api_res' => $res
                ));
                $r && $upCnt++;
            }
        }
        return ['code' =>  200, 'msg' => '成功同步' . $upCnt . '条通话记录'];
    }

    /*
     * 根据log_id更新外呼记录
     */
    function refreshCallLogByLogId(){
//        $where['has_session'] = 1;
//        $where['PHPYUNBTWSTART_A'] = '';
//        $where['status'][] = array('in', '1,2', 'AND');
//        $where['oss_record_path'][] = array('=', '', 'OR');
//        $where['PHPYUNBTWEND_A'] = '';
//        $where['limit'] = 500;
        $sql = 'SELECT `log_id`,`id` FROM `' . $this->def . 'staff_call_log` WHERE has_session = 1 and (status in (1,2) or (time_length > 0 and oss_record_path = ""))';
        $data = $this->DB_query_all($sql, 'all');
//        $data = $this->getList($where, array('field' => '`log_id`,`id`'));
        if (count($data) == 0) {
            return ['code' =>  400, 'msg' => '暂无要更新的通话记录！'];
        }
        $appkey = trim($this->config['sy_outcall_appkey']);
        $appsecret = trim($this->config['sy_outcall_appsecret']);
        if (!$appkey || !$appsecret) {
            return ['code' =>  400, 'msg' => '请先完成外呼配置！'];
        }
        $logIds = array();
        $logIdToId = array();
        foreach ($data as $v) {
            !in_array($v['log_id'], $logIds) && array_push($logIds, $v['log_id']);
            $logIdToId[$v['log_id']] = $v['id'];
        }
        $apiurl = 'https://u.phpyun.com/newupcalldtl';
        $params = array(
            'appKey' => $appkey,
            'appSecret' => $appsecret,
            'logIds' => json_encode($logIds)
        );
        $res = CurlPost($apiurl, $params, 1);
        $resArr = json_decode($res, 1);
        if ($resArr['code'] != 200) {
            return ['code' =>  400, 'msg' => $resArr['message']];
        }
        $upCnt = 0;// 更新数量
        foreach ($resArr['data'] as $v) {
            if (isset($logIdToId[$v['id']])) {
                $r = $this->upInfo(array('id' => $logIdToId[$v['id']]), array(
                    'time_length' => $v['time_length'],
                    'connect_time' => $v['connect_time'],
                    'disconnect_time' => $v['disconnect_time'],
                    'status' => $v['status'],
                    'call_status' => $v['call_status'],
                    'oss_record_path' => $v['oss_record_path'],
//                    'api_res' => $res
                ));
                $r && $upCnt++;
            }
        }
        return ['code' =>  200, 'msg' => '成功同步' . $upCnt . '条通话记录'];
    }

    // 根据原生sql获取坐席外呼统计信息
    function getListBySql($sql, $isTj = 0){
        $list = $this->DB_query_all($sql, 'all');
        if (!empty($list)) {
            if ($isTj == 0) {
                foreach ($list as &$v) {
                    if ($v['total_cnt'] > 0) {
                        $v['avg_length'] = $v['jt_cnt'] ? round($v['total_length']/$v['jt_cnt'], 2) : '-';
                        $v['jt_rate'] = sprintf('%.2f', ($v['jt_cnt']/$v['total_cnt'])*100);
                    } else {
                        $v['avg_length'] = '-';
                        $v['jt_rate'] = '-';
                    }
                }
            }
        }
        return $list;
    }
}
?>