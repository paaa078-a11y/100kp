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


class privacy_model extends model
{

    function getList($whereData, $data = array())
    {
        $ListNew    =   array();
        $List       =   $this->select_all('privacy_log', $whereData);

        if (!empty($List)) {

            $cuid   =   array();
            $uid    =   array();
            $jobid  =   array();
            foreach ($List as $k => $v) {
                if ($v['comid'] && $v['comid'] > 0) {

                    $cuid[] =   $v['comid'];
                }
                if ($v['uid'] && $v['uid'] > 0) {

                    $uid[]  =   $v['uid'];
                }
                if ($v['jobid'] && $v['jobid'] > 0) {
                    
                    $jobid[]  =   $v['jobid'];
                }
            }

            $alluids    =   array_merge($cuid, $uid);
            $alluids    =   array_unique($alluids);

            require_once('userinfo.model.php');
            $userinfoM  =   new userinfo_model($this->db, $this->def);

            $namelists  =   $userinfoM->getUserList(array('uid' => array('in', pylode(',', $alluids))));

            if (!empty($jobid)){
                $jobid = array_unique($jobid);
                $jobs = $this->select_all('company_job', array('id'=>array('in', pylode(',', $jobid))), 'id,name');
            }
            
            foreach ($namelists as $nk => $nv) {

                $names[$nv['uid']]  =   $nv['name'];
            }

            foreach ($List as $lk => $lv) {

                $List[$lk]['fname'] =   $lv['comid'] ? $names[$lv['comid']] : '管理员';

                if ($lv['uid'] > 0) {

                    $List[$lk]['sname'] =   $names[$lv['uid']];
                } else {

                    $List[$lk]['sname'] =   '';
                }
                if (isset($jobs)){
                    foreach ($jobs as $val){
                        if ($lv['jobid'] == $val['id']){
                            $List[$lk]['jobname'] =   $val['name'];
                        }
                    }
                }

                $List[$lk]['ctime_n'] =   date('Y-m-d H:i:s',$lv['ctime']);
            }

            $ListNew['list'] = $List;
        }

        return $ListNew;
    }

    function delMoblieMsg($whereData, $data)
    {

        if ($data['type'] == 'one') {//单个删除

            $limit = 'limit 1';
        }

        if ($data['type'] == 'all') {//多个删除

            $limit = '';
        }

        if ($data['norecycle'] == '1') {    //	数据库清理，不插入回收站

            $result = $this->delete_all('privacy_log', $whereData, $limit, '', '1');
        } else {

            $result = $this->delete_all('privacy_log', $whereData, $limit);
        }

        return $result;
    }


    //$bindNumberA, $bindNumberB,$comid,$jobid, $uid, $eid, $type
    function setPrivacy($data)
    {
        $privacy = 0;

        $bindNumberA = $data['NumberA'];
        $bindNumberB = $data['NumberB'];

        $logData['comid']       =   $data['comid'];
        $logData['uid']         =   $data['uid'];

        $logData['bindnumber']  =   $bindNumberA;
        $logData['bindnumberB'] =   $bindNumberB;

        $logData['ctime']       =   time();


        if ($data['type'] == '1' && $this->config['sy_privacy_open'] == '1') {  //简历隐私号

            $logData['type']    =   '1';//简历电话
            $logData['eid']     =   $data['eid'];
            $privacy            =   1;
        } elseif ($data['type'] == '2' && $this->config['sy_comprivacy_open'] == '1') { //企业隐私号


            $logData['type']    =   '2';//job
            $logData['jobid']   =   $data['jobid'];
            $privacy            =   2;
        }

        //开启隐私号
        if ($privacy > 0) {

            if (CheckMobile($bindNumberA) && CheckMobile($bindNumberB)) {

                //组装API必需参数
                $appid  =   $this->config['sy_privacy_appid'];

                $token  =   $this->config['sy_privacy_token'];
                $time   =   microtime();
                $sign   =   md5($appid . $token . $time);
                $header =   array(
                    'Content-Type: application/json',
                    'Authorization:' . base64_encode($appid . ":" . $time)
                );
                //查询当前是否有绑定小号 有绑定并且过期时间大于10秒的直接返回
                $this->config['sy_privacy_time']    =   $this->config['sy_privacy_time'] ? $this->config['sy_privacy_time'] : 60;

                $bindTime   =   time() - ($this->config['sy_privacy_time']);
                $isBind     =   $this->select_once('privacy_log', array('result' => 1, 'bindnumber' => $bindNumberA, 'bindnumberB' => $bindNumberB, 'ctime' => array('>=', $bindTime), 'orderby' => 'id,desc'));

                //当前如果有绑定小号的情况下 需要先解除绑定 重置生效时间
                if (!empty($isBind)) {

                    $reTime =   $isBind['ctime'] + $this->config['sy_privacy_time'] - time();

                    //有效期低于10秒直接解绑换新号
                    if ($reTime < 10) {

                        $unbindurl = "https://101.37.133.245:11008/voice/1.0.0/middleNumberUnbind/$appid/$sign";

                        $unbinddata['middleNumber'] =   $isBind['middlenumber'];
                        $unbinddata['bindNumberA']  =   $isBind['bindnumber'];
                        $unbinddata['bindNumberB']  =   $isBind['bindnumberB'];

                        CurlPost($unbindurl, json_encode($unbinddata), 1, $header);
                    } else {

                        $result['middleNumber'] =   $isBind['middlenumber'];
                        $result['prvtime']      =   $reTime;
                    }
                }

                if (!$result['middleNumber']) {

                    $url    =   "https://101.37.133.245:11008/voice/1.0.0/middleNumberAXB/$appid/$sign";

                    $data['bindNumberA']    =   $bindNumberA;
                    $data['bindNumberB']    =   $bindNumberB;
                    $data['maxBindingTime'] =   $this->config['sy_privacy_time'];

                    $return =   CurlPost($url, json_encode($data), 1, $header);
                    $result =   json_decode($return, true);

                    if ($result['result'] == '000000') {

                        $logData['middlenumber']=   $result['middleNumber'];
                        $logData['result']      =   1;
                        $result['prvtime']      =   $this->config['sy_privacy_time'];

                        include_once('warning.model.php');
                        $warningM   =   new warning_model($this->db, $this->def);

                        if ($data['type'] == 1) {

                            $warningM->warning(11, $logData['comid']);
                        }else if($data['type'] == 2){

                            $warningM->warning(10, $logData['uid']);
                        }
                    } else {

                        $logData['result']      =   $result['result'];
                        $logData['message']     =   $result['message'];
                    }

                    $this->insert_into('privacy_log', $logData);
                }
            } else {

                $logData['result']  =   0;
                $logData['message'] =   '手机号格式错误';

                //记录隐私号绑定日志
                $this->insert_into('privacy_log', $logData);
            }
        }

        if ($result['middleNumber']) {

            return array('middleNumber' => $result['middleNumber'], 'prvtime' => $result['prvtime']);
        }
    }
}
?>