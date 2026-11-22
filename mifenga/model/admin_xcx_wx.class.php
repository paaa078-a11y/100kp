<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class admin_xcx_wx_controller extends adminCommon{
    
    function index_action()
    {
        $wxpaydata = array();
        @include(DATA_PATH.'api/wxpay/wxpay_data.php');
        
        if($_POST['pay_config']){
            
            //小程序用
            $wxpaydata['sy_xcxappid']		=	trim($_POST['sy_xcxappid']);
            
            $wxpaydata['sy_xcxsecret']		=	trim($_POST['sy_xcxsecret']);
            
            $wxpaydata['sy_xcxysid']		=	trim($_POST['sy_xcxysid']);
            
            $wxpaydata['sy_sphid']		    =	trim($_POST['sy_sphid']);
            
            made_web(DATA_PATH.'api/wxpay/wxpay_data.php',ArrayToString($wxpaydata),'wxpaydata');
            
            $configM  =  $this->MODEL('config');

            $data['sy_xcxappid'] = trim($_POST['sy_xcxappid']);

            $data['sy_wxxcx_iospay']  =  isset($_POST['sy_wxxcx_iospay']) ? 1 : 2;
            $data['sy_xcx_contact']  =  isset($_POST['sy_xcx_contact']) ? 1 : 2;
            $data['sy_xcxQrcode']  =  isset($_POST['sy_xcxQrcode']) ? 1 : 2;
            
			$data['sy_xcxname'] = trim($_POST['sy_xcxname']);

			$data['sy_xcxpath'] = trim($_POST['sy_xcxpath']);


            $configM -> setConfig($data);
            
            $this->web_config();
            
            $this->ACT_layer_msg('微信小程序配置成功！',9,$_SERVER['HTTP_REFERER'],2,1);
        }
        $this->yunset('wxpaydata',$wxpaydata);

        $this->yuntpl(array('admin/admin_xcx_wx'));
    }
    
    function getQrcode_action(){
        
        if ($_GET['type']){
            
            $jobM  =  $this->MODEL('job');
            
            $job   =  $jobM->getInfo(array('state'=>1,'status'=>0,'r_status'=>1,'orderby'=>'id,desc'),array('field'=>'`id`'));
            
            if (!empty($job)){
                
                $data  =  array(
                    'type'  =>  $_GET['type'],
                    'id'    =>  $job['id']
                );
                $xcxM    =  $this->MODEL('xcx');
                
                $return  =  $xcxM->getQrcode($data);
                
                echo json_encode($return);die();
            }
        }
    }

    /*
     * 订阅模板列表
     */
    function tplindex_action(){
        $tplM =	$this->MODEL("subtpl");
        $list =	$tplM->getTpls();
        $data = array();
        foreach ($list['list'] as $v) {
            $data[$v['name']] = $v['tpl_id'];
        }
        $this->yunset("data", $data);
        $this->yuntpl(array('admin/admin_subtpl'));
    }

    /**
     * 工具-移动端-微信小程序:订阅消息模板:添加模板保存
     */
    function tplsave_action(){
        if($_POST['msgconfig']){
            $tplM = $this->MODEL('subtpl');
            $tplData = array(
                array('name' => 'xjh_start_tplid', 'tpl_id' => trim($_POST['xjh_start_tplid'])),
                array('name' => 'interview_tplid', 'tpl_id' => trim($_POST['interview_tplid'])),
                array('name' => 'payment_tplid', 'tpl_id' => trim($_POST['payment_tplid'])),
                array('name' => 'job_audit_tplid', 'tpl_id' => trim($_POST['job_audit_tplid'])),
            );
            $nid = $tplM->addTpl($tplData);
            if($nid > 0){
                $this->ACT_layer_msg('订阅模板配置成功！',9,'index.php?m=admin_xcx_wx&c=tplindex');
            }else{
                $this->ACT_layer_msg('订阅模板配置失败，请重试！',8);
            }
        }
    }

    /*
     * 工具-移动端-微信小程序:订阅消息列表
     */
    function subTplMsgIndex_action(){
        $weixinM = $this->MODEL('weixin');
        if ($_GET['keyword']) {
            $keyword = trim($_GET['keyword']);
            $type = intval($_GET['type']);
            $uids = array();
            if (in_array($type, array(1, 2, 3, 4))) {
                $UserinfoM = $this->MODEL('userinfo');
                $marr = array(
                    1 => array('name' => array('like', $keyword)),
                    2 => array('name' => array('like', $keyword)),
                    3 => array('realname' => array('like', $keyword)),
                    4 => array('name' => array('like', $keyword))
                );
                $ulist = $UserinfoM->getUserInfoList($marr[$type], array('usertype' => $type, 'field' => 'uid'));
                foreach ($ulist as $uk => $uv) {
                    if ($uv['uid']) {
                        $uids[] = $uv['uid'];
                    }
                }
                $where['utype'] = $type;
                $where['uid'] = array('in', pylode(',', $uids));
            } elseif ($type == 90) {
                //$where['utype'] = $type;
                $where['uid'] = $keyword;
            }
            $urlarr['type'] = "".$type."";
            $urlarr['keyword'] = "".$keyword."";
        }else{
            $where['utype'] = array('<', 99);
        }
        if (($_GET['date'])) {
            $times = @explode('~', $_GET['date']);
            $where['PHPYUNBTWSTART_A'] = '';
            $where['ctime'][] = array('>=', strtotime($times[0]." 00:00:00"));
            $where['ctime'][] = array('<', strtotime($times[1]." 23:59:59"));
            $where['PHPYUNBTWEND_A'] = '';
            $urlarr['date'] = $_GET['date'];
        }
        $urlarr = $_GET;
        $urlarr['page'] = "{{page}}";
        $urlarr['c'] = "subTplMsgIndex";
        $pageurl = Url($_GET['m'], $urlarr, 'admin');
        $pageM = $this->MODEL('page');
        $pages = $pageM->pageList('wx_subscribe_msg', $where, $pageurl, $_GET['page']);
        if ($pages['total'] > 0) {
            if ($_GET['order']) {
                $where['orderby'] = $_GET['t'] . ',' . $_GET['order'];
                $urlarr['order'] = $_GET['order'];
                $urlarr['t'] = $_GET['t'];
            } else {
                $where['orderby'] = 'id';
            }
            $where['limit'] = $pages['limit'];
            $wxMsgList = $weixinM->getWxSubscribeMsgList($where);
            $this->yunset('rows', $wxMsgList);
        }
        $this->yuntpl(array('admin/admin_xcx_subtpllist'));
    }
    /*
     * 工具-移动端-微信小程序:订阅消息列表(删除)
     */
    function delsubmsg_action()
    {
        $weixinM = $this->Model('weixin');
        if (is_array($_POST['del'])) {
            $delid = @implode(',', $_POST['del']);
            $layer_type = 1;
        } else {
            $this->check_token();
            $delid = (int)$_GET['id'];
            $layer_type = 0;
        }
        $where['id'] = array('in', $delid);
        $del = $weixinM->delWxsubmsg(array('where' => $where));
        $del ? $this->layer_msg('微信订阅消息(ID:' . $delid . ')删除成功！', 9, $layer_type, $_SERVER['HTTP_REFERER']) : $this->layer_msg('删除失败！', 8, $layer_type, $_SERVER['HTTP_REFERER']);
    }
    /**
     * 检查访问地址
     */
    function check_token(){
        if ($this->config['sy_iscsrf'] != '2') {
            if ($_SESSION['pytoken'] != $_GET['pytoken'] || ! $_SESSION['pytoken']) {
                unset($_SESSION['pytoken']);
                $this -> ACT_layer_msg('来源地址非法！', 8, 'index.php');
                exit();
            }
        }
    }
}

?>