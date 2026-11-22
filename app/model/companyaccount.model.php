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
class companyaccount_model extends model{


    // 获取套餐数据信息
    private function getStatisInfo($uid, $data = array())
    {
        require_once ('statis.model.php');

        $StatisM    =   new statis_model($this->db, $this->def);

        return $StatisM -> getInfo($uid, $data);
    }

    // 添加会员日志
    private function addMemberLog($uid, $usertype, $content, $opera = '', $type = '', $detail = '')
    {
        require_once('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return $LogM->addMemberLog($uid, $usertype, $content, $opera, $type, $detail);
    }

    /**
     * @data    添加company_account 子账号数据
     */
    public function addInfo($data = array()) {

        $nid	=	$this -> insert_into('company_account', $data);
        return $nid;
    }

    /**
     * @data    修改company_account 子账号数据
     */
    public function updInfo($where = array(), $data = array()) {

        $nid	=	$this -> update_once('company_account', $data, $where);
        return $nid;

    }

    /**
     * @desc 子账户列表信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getList($where = array(), $data = array())
    {

        $select =   $data['field'] ? $data['field'] : '*';
        $List   =   $this->select_all('company_account', $where, $select);

        if (!empty($List)){

            $uidArr =   array();
            foreach ($List as $k => $v){

                $uidArr[]   =   $v['uid'];
                $List[$k]['ctime_n']    =   date('Y-m-d', $v['ctime']);
            }

            $memberList =   $this->select_all('member', array('uid' => array('in', pylode(',', $uidArr))), '`uid`,`login_date`');

            foreach ($List as $k => $v) {
                foreach ($memberList as $mk => $mv){

                    if ($v['uid'] == $mv['uid']){

                        $List[$k]['login_date_n']   =   (int)$mv['login_date'] > 0 ? date('Y-m-d H:i', $mv['login_date']) : '从未登录';
                    }
                }
            }
        }

        return $List;
    }

    /**
     * @desc company_account 子账号数据信息
     * @param array $where
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getInfo($where = array(), $data = array())
    {

        $select =   $data['field'] ? $data['field'] : '*';
        $Info   =   $this->select_once('company_account', $where, $select);
        return $Info;
    }

    /**
     * @desc company_account 子账号数量
     * @param array $where
     * @return array|bool|false|string|void
     */
    public function getNum($where = array())
    {

        return $this->select_num('company_account', $where);
    }

    public function getListNum()
    {

        $arr        =   array();
        $ajaxAll    =   $this->select_num('member', array('pid' => array('>', 0)));
        if ($ajaxAll > 0) {

            $arr['ajaxAll'] =   $ajaxAll;
        }
        $ajaxLock   =   $this->select_num('member', array('status' => 2, 'pid' => array('>', 0)));
        if ($ajaxLock > 0) {

            $arr['ajaxLock']=   $ajaxLock;
        }
        return json_encode($arr);
    }
    public function getListNumV1()
    {
        $arr        =   array();
        $arr['ajaxAll']    =   $this->select_num('company_account');
        $arr['ajaxLock']   =   $this->select_num('company_account', array('status' => 2));
        return $arr;
    }



    /**
     * @desc 添加company_account 子账号数据；创建完子账号，增加 company_statis信息
     * @param array $data
     * @return array
     */
    public function createAccount($data = array()){
        $res    =   array(
            'errcode'   =>  8,
            'msg'       =>  ''
        );
        if(empty($data['comid'])){
            $res['msg'] =   '缺少主账号信息!';
            return $res;
        }

        //增加company_statis信息
        require_once ('statis.model.php');
        $statisM		=	new statis_model($this->db, $this->def);
        $fatherData     =   $statisM -> getInfo($data['comid'], array('usertype' => 2));
        if(empty($fatherData)){
            $res['msg'] =   '主账号套餐信息错误!';
            return $res;
        }

        //创建子账号
        $accountId      =   $this -> addInfo($data);
        if(empty($accountId)){
            $res['msg'] =   '子账号创建失败!';
            return $res;
        }

        //补充主账号的信息
        $sonStatisData  =   array();
        $sonStatisData['rating'] = $fatherData['rating'];
        $sonStatisData['rating_name'] = $fatherData['rating_name'];
        $sonStatisData['rating_type'] = $fatherData['rating_type'];
        $sonStatisData['vip_stime'] = $fatherData['vip_stime'];
        $sonStatisData['vip_etime'] = $fatherData['vip_etime'];
        $sonStatisData['uid'] = $accountId;

        $statisM->addInfo($sonStatisData, array('usertype' => 2));

        $res['errcode'] = 9;
        return $res;
    }

    /**
     * @desc   删除子账户
     *         子账号套餐归还主账号
     *         删除相关数据表信息：member、company_statis、company_account
     *
     * @param array $data ['uid'] 子账号 ['pid'] 注账号
     * @return array
     */
    public function delChild($data = array())
    {

        if (empty($data['uid']) || empty($data['pid'])) {

            $res    =   array('ecode' => 8, 'msg' => '参数UID错误');
            return $res;
        } else {

            $pid    =   intval($data['pid']);
            $uid    =   intval($data['uid']);
        }

        $field      =   '`breakjob_num`, `down_resume`, `invite_resume`, `top_num`, `rec_num`, `urgent_num`, `zph_num`,`chat_num`';
        $statisChild=   $this->getStatisInfo($uid, array('usertype' => 2, 'field' => $field));

        $res['id']  =   $this->delete_all('member', array('uid' => $uid), '');

        if ($res['id']) {
            if (!empty($statisChild) && is_array($statisChild)) {

                $logDetail      =   '';
                $upStatisData   =   array();
                if (intval($statisChild['breakjob_num']) > 0){

                    $upStatisData['breakjob_num']   =   array('+', intval($statisChild['breakjob_num']));
                    $logDetail  .=  '，刷新职位数 + ：'.intval($statisChild['breakjob_num']);
                }
                if (intval($statisChild['down_resume']) > 0){

                    $upStatisData['down_resume']    =   array('+', intval($statisChild['down_resume']));
                    $logDetail  .=  '，下载简历数 + ：'.intval($statisChild['down_resume']);
                }
                if (intval($statisChild['invite_resume']) > 0){

                    $upStatisData['invite_resume']  =   array('+', intval($statisChild['invite_resume']));
                    $logDetail  .=  '，邀请面试数 + ：'.intval($statisChild['invite_resume']);
                }
                if (intval($statisChild['top_num']) > 0){

                    $upStatisData['top_num']        =   array('+', intval($statisChild['top_num']));
                    $logDetail  .=  '，置顶职位数 + ：'.intval($statisChild['top_num']);
                }
                if (intval($statisChild['rec_num']) > 0){

                    $upStatisData['rec_num']        =   array('+', intval($statisChild['rec_num']));
                    $logDetail  .=  '，职位推荐数 + ：'.intval($statisChild['rec_num']);
                }
                if (intval($statisChild['urgent_num']) > 0){

                    $upStatisData['urgent_num']     =   array('+', intval($statisChild['urgent_num']));
                    $logDetail  .=  '，紧急招聘数 + ：'.intval($statisChild['urgent_num']);
                }
                if (intval($statisChild['zph_num']) > 0){

                    $upStatisData['zph_num']        =   array('+', intval($statisChild['zph_num']));
                    $logDetail  .=  '，报名招聘会数 + ：'.intval($statisChild['zph_num']);
                }
                if (intval($statisChild['chat_num']) > 0){

                    $upStatisData['chat_num']       =   array('+', intval($statisChild['chat_num']));
                    $logDetail  .=  '，'.$this->config['sy_chat_name'].'数 + ：'.intval($statisChild['zph_num']);
                }

                if (!empty($upStatisData) && !empty($logDetail)){

                    $this->update_once('company_statis', $upStatisData, array('uid' => $pid));

                    $logDetail  =   '删除子账号，主体账号套餐变化'.$logDetail;
                }
            }

            $this->delete_all('company_statis', array('uid' => $uid), '');
            $this->delete_all('company_account', array('uid' => $uid), '');

            $this->addMemberLog($pid, 2, '子账号删除：子账号（UID：'.$uid.'）', 27, 3, $logDetail);

            $res    =   array('ecode' => 9, 'msg' => '子账号删除成功！');
        }else{

            $res    =   array('ecode' => 8, 'msg' => '子账号删除失败！');
        }
        return $res;
    }

    /**
     * @desc 激活、锁定子账号
     * @param array $data
     * @return array
     */
    function bindChildInfo($data = array())
    {

        if ($data['type'] == 'bind') {

            $status =   1;
            $msg    =   '企业(ID:'.$data['uid'].')激活子账号(ID:'.$data['pid'].')';
        } else {

            $status =   2;
            $msg    =   '企业(ID:'.$data['uid'].')锁定子账号(ID:'.$data['pid'].')';
        }

        $nid        =   $this->update_once('member', array('status' => $status), array('uid' => $data['uid'], 'pid' => $data['pid']));


        if ($nid) {

            $res    =   array('ecode' => 9, 'msg' => $msg.'成功');
        } else {

            $res    =   array('ecode' => 8, 'msg' => $msg.'失败');
        }

        return $res;
    }
}
?>