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
class admin_model extends model{

    //微信模板通知类型及通知管理员
    function getWmtype($whereData,$data=array('field'=>'*')){

        $List  =  $this -> select_all('admin_wmtype',$whereData,$data['field']);

        if(!empty($List)){

            foreach($List as $k=>$v){
               $List[$k]['uid_arr']  = $v['uid'] ? @explode(',',$v['uid']) : array();
            }

        }
        
        return $List;
    }

    public function upWmtype($upData=array(),$whereData=array(),$data=array()){
        
        $return['id']  =  $this -> update_once('admin_wmtype',$upData,$whereData);

        return  $return;
    }

    /**
     * 获取管理员列表
     * @param array $whereData
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getList($whereData, $data = array())
    {

        $field  =   $data['field'] ? $data['field'] : '*';
        $List   =   $this->select_all('admin_user', $whereData, $field);

        if (!empty($List)) {

            $uids = $gid = $dids = array();

            foreach ($List as $v) {

                if (!in_array($v['m_id'], $gid)) {
                    $gid[] = $v['m_id'];
                }
                if (!in_array($v['uid'], $uids)) {
                    $uids[] = $v['uid'];
                }
                if($v['did']>0){
                    $dids[] =   $v['did'];
                }
            }

            if ($data['utype'] == 'crm') {  // CRM管理

                $allWhere = $conWhere = array();

                $allWhere['crm_uid']    =   array('in', pylode(',', $uids));
                $allComList =   $this->select_all('company', $allWhere, '`uid`,`crm_uid`,`crm_status`,`isfollow`,`f_time`');

                //  计划跟进客户
                $vitWhere   =   array();
                $vitWhere['auid']   =   array('in', pylode(',', $uids));
                $vitWhere['type']   =   22;
                $vitWhere['status'] =   1;
                $vitList    =   $this->select_all('crm_work_plan', $vitWhere);

                //  成交客户
                $vipWhere   =   array(
                    'crm_uid'   =>  array('in', pylode(',', $uids)),
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
                if ($this->config['sy_crm_deal_open'] == 1) {
                    $vipKh  =   $this->select_all('company', $vipWhere, '`crm_uid`');
                }

                //  订单信息
                $orderWhere =   array();
                $orderWhere['crm_uid']      =   array('in', pylode(',', $uids));
                $orderWhere['order_state']  =   2;
                $orderList  =   $this->select_all('company_order', $orderWhere, '`crm_uid`,`order_price`');

                //所属部门
                $orgList    =   $this->select_all('crmorg');
                foreach ($orgList as $key => $value) {

                    $orgArr[$value['id']] = $value['name'];
                }
            }
            //  权限分组
            $groupList  =   $this->select_all('admin_user_group', array('id' => array('in', pylode(',', $gid))));
            $domainList =   $this->select_all('domain', array('id'=>array('in',pylode(',',$dids))), '`id`,`title`');
            foreach ($List as $k => $v) {

                $List[$k]['kh_n'] = $v['crm_kh'] == 1;
                $List[$k]['status_n'] = $v['status'] == 1;
                foreach ($groupList as $val) {
                    if ($v['m_id'] == $val['id']) {

                        $List[$k]['group_name'] =   $val['group_name'];
                    }
                }
                foreach ($domainList as $dv) {
                    if ($v['did'] == $dv['id']) {

                        $List[$k]['domain_name']    =   $dv['title'];
                    }
                }
                $List[$k]['departName'] = $v['org'] && $orgArr[$v['org']] ? $orgArr[$v['org']] : '--';
                if (!empty($uids)) {

                    $List[$k]['allNum']         =   0;  //  客户总数
                    $List[$k]['concernedNum']   =   0;  //  已跟进
                    $List[$k]['vitNum']         =   0;  //  计划跟进

                    $List[$k]['vipNum']         =   0;  //  成交客户
                    $List[$k]['orderNum']       =   0;  //  签单数
                    $List[$k]['orderPrice']     =   0;  //  签单金额

                    foreach ($allComList as $ack => $acv) {
                        if ($v['uid'] == $acv['crm_uid']) {

                            $List[$k]['allNum']++;

                            if ($acv['isfollow'] == '1') {

                                $List[$k]['concernedNum']++;//  已跟进
                            }
                        }
                    }
                    foreach ($vitList as $val) {
                        if ($v['uid'] == $val['auid']) {

                            $List[$k]['vieNum']++;          //  计划跟进
                        }
                    }
                    if (!empty($vipKh)) {
                        foreach ($vipKh as $val) {
                            if ($v['uid'] == $val['crm_uid']) {

                                $List[$k]['vipNum']++;
                            }
                        }
                    }
                    foreach ($orderList as $val) {
                        if ($v['uid'] == $val['crm_uid']) {

                            $List[$k]['orderNum']++;        //签单数
                            $List[$k]['orderPrice'] += $val['order_price']; //签单金额
                        }
                    }
                }
            }

            foreach ($List as $k => $v) {

                $List[$k]['orderPrice'] =   number_format($v['orderPrice'], 2);

                $List[$k]['syNum']      =   0;
                if ($this->config['sy_crm_deal_open'] == 1){

                    $List[$k]['syNum']  =   $v['num'] > 0 ? $v['num'] - ($v['allNum'] - $v['vipNum']) : '不限';
                }else if ($this->config['sy_crm_deal_open'] == 2){

                    $List[$k]['syNum']  =   $v['num'] > 0 ? $v['num'] - $v['allNum'] : '不限';
                }
            }
        }
        return $List;
    }

    /**
     * 获取管理员权限
     * @param array $whereData
     * @param array $data
     */
    public function getPower($whereData=array()){
        
        $return  =  null;
        
        if (!empty($whereData)){
            
            $adminGroup  =  $this -> select_once('admin_user_group',array('id'=>$whereData['id']));
            
            $return['group_name']  =  $adminGroup['group_name'];
            $return['power']       =  unserialize($adminGroup['group_power']);
        }
        return $return;
    }


    /**
     * 添加管理员
     * @param array $addData
     * @param array $data
     * @return array
     */
    public function addAdminUser($addData=array(),$data=array()){
        //检测用户名重复性
        if (!empty($addData['username'])){
            
            $check  =  $this -> checkUsername($addData['username']);
            
            if ($check['msg']){
                
                return $check;
            }
        }
        if ($addData['password']){
            
            $addData['password']    =  $this -> makePass($addData['password']);
        }

        if (!isset($addData['did'])){
            $addData['did']         =   0;
        }

        $return['id']           =  $this -> insert_into('admin_user',$addData);
        
        if ($return['id']){
            
            $return['msg']      =  '管理员(id:'.$return['id'].')添加成功';
            $return['errcode']  =  '9';
            
        }else{
            $return['msg']      =  '管理员添加失败';
            $return['errcode']  =  '8';
        }
        
        return	$return;
    }

    /**
     * 获取管理员信息
     * @param array $whereData
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getAdminUser($whereData = array(), $data = array())
    {

        $field      =   $data['field'] ? $data['field'] : '*';

        $adminUser  =   $this->select_once('admin_user', $whereData, $field);

        if (isset($data['wap_crm']) && intval($data['wap_crm']) == 1){  //  手机后台：CRM工作台请求

            $adminUser['photo_n']       =   checkpic($adminUser['photo']);

            $groupInfo  =   $this->select_once('admin_user_group', array('id' => $adminUser['m_id']), 'group_name');
            $adminUser['group_name']    =   $groupInfo['group_name'];

            $monthStart =   mktime(0, 0, 0, date('m'), 1, date('Y'));
            $orderInfo  =   $this->select_once('company_order', array('crm_uid' => $adminUser['uid'], 'order_time' => array('>', $monthStart), 'order_state' => 2), 'sum(`order_price`) as total');
            $adminUser['achieve']   =   '￥'.number_format($orderInfo['total'], 2);
        }
        return $adminUser;
    }

    /**
     * 修改管理员
     * @param array $upData
     * @param array $whereData
     * @param array $data
     * @return mixed
     */
    public function upInfo($upData=array(),$whereData=array(),$data=array()){
        
        $return['id']  =  $this -> update_once('admin_user',$upData,$whereData);
        return	$return;
    }

    /**
     * 修改管理员操作
     * @param array $upData
     * @param array $whereData
     * @param array $data
     * @return array
     */
    public function upAdminUser($upData=array(),$whereData=array(),$data=array()){
        
        $msg  =  '';
        //检测用户名重复性
        if (!empty($upData['username'])){
            
            $check  =  $this -> checkUsername($upData['username'],$whereData['uid']);
            
            if ($check['msg']){
                
                return $check;
            }
        }
        //修改密码
        if (isset($upData['password'])){
            if (!empty($upData['password'])){
                
                $return  =  array();
                
                if (isset($data['oldpass'])){
                    if(empty($data['oldpass'])){
                        
                        $return['msg']      =  '原始密码不能为空！';
                        $return['errcode']  =  '8';
                        return $return;
                        
                    }elseif($data['oldpass'] == $upData['password']){
                        
                        $return['msg']      =  '新密码和原始密码一致，不需要修改！';
                        $return['errcode']  =  '8';
                        return $return;
                        
                    }elseif($upData['password'] != $data['okpassword']){
                        
                        $return['msg']      =  '新密码两次输入不一致！';
                        $return['errcode']  =  '8';
                        return $return;
                    }
                    $user    =  $this->select_once('admin_user',array('uid'=>$whereData['uid']));
                    $verify  =  $this->verifyPass($data['oldpass'], $user['password']);
                    
                    if ($verify == false){
                        
                        $return['msg']      =  '原始密码不正确！';
                        $return['errcode']  =  '8';
                        return $return;
                    }
                    if (!empty($return)){
                        
                        return $return;
                    }
                }
                
                $msg                 =  '密码';
                
                $upData['password']  =  $this->makePass($upData['password']);
            }else{
                $return['msg']      =  '新密码不能为空！';
                $return['errcode']  =  '8';
                return $return;
            }
        }
        $return['id']  =  $this -> update_once('admin_user',$upData,$whereData);
        
        if ($return['id']){
            
            $return['msg']      =  '管理员'.$msg.'(ID:'.$whereData['uid'].')修改成功';
            $return['errcode']  =  '9';
            
        }else{
            $return['msg']      =  '管理员'.$msg.'(ID:'.$whereData['uid'].')修改失败';
            $return['errcode']  =  '8';
        }
        return	$return;
    }
    /**
     * 检测管理员用户名重复性
     * @param string $username
     */
    private function checkUsername($username, $uid = ''){
        
        $user    =  $this -> select_once('admin_user',array('username'=>$username));
        
        $return  =  array();
        
        if ($user && ($uid == '' || ($uid !='' && $uid != $user['uid']))){
            $return['msg']      =  '管理员用户名已存在';
            $return['errcode']  =  '8';
        }
        
        return $return;
    }

    /**
     * 删除管理员
     * @param array $whereData
     * @param array $data
     * @return mixed
     */
    public function delAdminUser($whereData=array(),$data=array())
	{
		
        $return['id']  =  $this -> delete_all('admin_user',$whereData, '');
        
        if ($return['id']){
            //如果是业务员，清除企业表crm_uid字段
            $this->update_once('company', array('crm_uid'=>0,'crm_status'=>0), array('crm_uid'=>$whereData['uid']));
            $this->update_once('company_order', array('crm_uid'=>0), array('crm_uid'=>$whereData['uid']));

            $return['msg']      =  '管理员(ID:'.$whereData['uid'].')删除成功';
            $return['errcode']  =  '9';
            
        }else{
            $return['msg']      =  '管理员(ID:'.$whereData['uid'].')删除失败';
            $return['errcode']  =  '8';
        }
        return	$return;
    }
    /**
     * 添加管理员类型
     * @param array $addData
     * @param array $data
     */
    public function addAdminGroup($addData=array(),$data=array()){
        
        $return['id']  =  $this -> insert_into('admin_user_group',$addData);
        
        if ($return['id']){
            
            $return['msg']      =  '管理员类型添加成功';
            $return['errcode']  =  '9';
            
        }else{
            $return['msg']      =  '管理员类型添加失败';
            $return['errcode']  =  '8';
        }
        
        return	$return;
    }
    /**
     * 获取管理员类型信息
     * @param array $whereData
     * @param array $data
     */
    public function getAdminGroup($whereData=array(),$data=array('field'=>'*')){
        
        //处理分站
        if (empty($whereData['did'])){
            
            unset($whereData['did']);
        }
        
        $return  =  $this -> select_once('admin_user_group',$whereData,$data['field']);
        
        return	$return;
    }
    /**
     * 获取管理员类型列表
     * @param array $whereData
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getAdminGroupList($whereData = array(), $data = array('field' => null, 'utype' => null))
    {

        $field  =   $data['field'] ? $data['field'] : '*';
        if (empty($whereData['did'])) {

            unset($whereData['did']);
        }

        $List   =   $this->select_all('admin_user_group', $whereData, $field);

        if ($data['utype'] == 'admin') {
            if (empty($data['uwhere'])) {

                $user = $this->select_all('admin_user', array('did' => 0, 'isdid' => array('=', 1, 'OR'), 'groupby' => 'm_id'), '`m_id`,count(`uid`) as num');
            } else {

                $user = $this->select_all('admin_user', $data['uwhere'], '`m_id`,count(`uid`) as num');
            }

            $domainList =   $this->select_all('domain', '', '`id`,`title`');

            foreach ($List as $k => $v) {

                $List[$k]['num']        =   0;
                $List[$k]['domain_name']=   '--';
                foreach ($user as $val) {
                    if ($v['id'] == $val['m_id']) {

                        $List[$k]['num'] = $val['num'];
                    }
                }
                foreach ($domainList as $dk => $dv){
                    if ($v['did'] == $dv['id']){

                        $List[$k]['domain_name']    =   $dv['title'];
                    }
                }
            }
        }
        return	$List;
    }
    /**
     * 修改管理员类型
     * @param array $addData
     * @param array $whereData
     * @param array $data
     */
    public function upAdminGroup($upData=array(),$whereData=array(),$data=array()){
        
        $return['id']  =  $this -> update_once('admin_user_group',$upData,$whereData);
        
        if ($return['id']){
            
            $return['msg']      =  '管理员类型(ID:'.$whereData['id'].')修改成功';
            $return['errcode']  =  '9';
            
        }else{
            $return['msg']      =  '管理员类型(ID:'.$whereData['id'].')修改失败';
            $return['errcode']  =  '8';
        }
        
        return	$return;
    }
    /**
     * 删除管理员类型
     * @param array $whereData
     * @param array $data
     */
    public function delAdminGroup($whereData=array(),$data=array()){
        
        if (!empty($whereData['id'])){
            $num  =  $this->select_num('admin_user',array('m_id'=>$whereData['id']));
            
            if ($num>0){
                $return['msg']      =  '该管理员类型下有管理员，请先删除相关管理员';
                $return['errcode']  =  '8';
            }else{
				 
                $return['id']  =  $this -> delete_all('admin_user_group',$whereData, '');
                
                if ($return['id']){
                    $return['msg']      =  '管理员类型(ID:'.$whereData['id'].')删除成功';
                    $return['errcode']  =  '9';
                    
                }else{
                    $return['msg']      =  '管理员类型(ID:'.$whereData['id'].')删除失败';
                    $return['errcode']  =  '8';
                }
            }
        }else{
            $return['msg']      =  '请选择需要删除的管理员类型';
            $return['errcode']  =  '8';
        }
        return	$return;
    }
    /**
     * 后台生成密码
     */
    public function makePass($pw){
        
        $adminpw  =  md5(md5($pw));
        
        return $adminpw;
    }
    /**
     * 后台验证密码
     */
    public function verifyPass($postPass, $userPass){
        
        $pw  =  $this->makePass($postPass);
        
        if ($pw == $userPass){
            
            return true;
            
        }else {
            
            return false;
        }
    }

    /**
     * @desc 通知管理员
     * @param string[] $data | 'type' => '通知类型'；'first' =>'通知模板内容，例如：有新的职位需要审核'; 'title' => '通知消息标题'；'crm_uid' => '指定业务员UID'
     */
    public function sendAdminMsg($data = array('type' => '', 'first' => '', 'title' => '', 'crm_uid' => '','customer'=>'','thing'=>''))
    {
        if (!empty($data['first']) && !empty($data['type'])) {

            $wmtype =   $this->select_once('admin_wmtype', array('type' => $data['type']), '`uid`');

            $uidArr =   explode(',', $wmtype['uid']);

            $uidStr =   '';

            if (!empty($uidArr)) {

                if (!in_array($data['crm_uid'], $uidArr) && $data['crm_uid']) {

                    $uidArr[]   =   $data['crm_uid'];
                }
                $uidStr =   pylode(',', $uidArr);
            } else if ($data['crm_uid']){

                $uidStr =   $data['crm_uid'];
            }

            $user   =   $this->select_all('admin_user', array('uid' => array('in', $uidStr)), '`uid`,`name`,`moblie`,`r_status`,`weixin`,`wxid`,`qy_userid`');

            if (!empty($user)) {

                require_once('weixin.model.php');
                $weixinM    =   new weixin_model($this->db, $this->def);

                if ($this->config["sy_admin_wmtype"] == 1) {
                    foreach ($user as $v) {
                        if ($v['wxid']) {

                            $weixinM->sendWxAdmin(array('uid' => $v['uid'], 'type' => $data['type'], 'first' => $data['first'], 'wxid' => $v['wxid'], 'title' => $data['title'],'customer'=>$data['customer'],'thing'=>$data['thing']));
                        }
                    }
                } else if ($this->config["sy_admin_wmtype"] == 2) {

                    $uids = $userids = array();

                    foreach ($user as $v) {
                        if ($v['qy_userid']) {

                            $uids[]     =   $v['uid'];
                            $userids[]  =   $v['qy_userid'];
                        }
                    }

                    $weixinM->sendWxQyAdmin(array('type' => $data['type'], 'first' => $data['first'], 'uids' => $uids, 'userids' => $userids, 'title' => $data['title']));
                }
            }
        }
    }

    /**
     * @desc  获取管理员类型数目
     */
    function getAdmintypeNum($WhereData = array())
    {
        $Sysmsgnum = $this->select_num('admin_user_group', $WhereData);
        return $Sysmsgnum;
    }
    /**
     * @desc  获取管理员数目
     */
    function getAdminNum($WhereData = array())
    {
        $num = $this->select_num('admin_user', $WhereData);
        return $num;
    }
}
?>