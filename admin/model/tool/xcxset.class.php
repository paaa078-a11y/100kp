<?php

class xcxset_controller extends adminCommon{
    /**
     * 工具 移动端 小程序配置 微信小程序
     */
    function index_action(){
        $wxpaydata = array();
        @include(DATA_PATH.'api/wxpay/wxpay_data.php');
        $data['config'] = array(
            'sy_xcxappid' => $wxpaydata['sy_xcxappid'] ? $wxpaydata['sy_xcxappid'] : '',
            'sy_xcxsecret' => $wxpaydata['sy_xcxsecret'] ? $wxpaydata['sy_xcxsecret'] : '',
            'sy_xcxysid' => $wxpaydata['sy_xcxysid'] ? $wxpaydata['sy_xcxysid'] : '',
            'sy_xcxname' => $this->config['sy_xcxname'] ? $this->config['sy_xcxname'] : '',
            'sy_xcxpath' => $this->config['sy_xcxpath'] ? $this->config['sy_xcxpath'] : '',
            'sy_xcxQrcode' => $this->config['sy_xcxQrcode'] == 1,
            'sy_wxxcx_iospay' => $this->config['sy_wxxcx_iospay'] == 1,
            'mini_wxopen' => $this->config['mini_wxopen'] ? '已绑定' : '未绑定',
            'sy_sphid' => $wxpaydata['sy_sphid'] ? $wxpaydata['sy_sphid'] : '',
        );
        $data['sy_weixinxcxu_qcode'] = $this->config['sy_weixinxcxu_qcode'] ? $this->config['sy_ossurl'] . '/' . $this->config['sy_weixinxcxu_qcode'] : '';
        $data['sy_xcxpic'] = $this->config['sy_xcxpic'] ? $this->config['sy_ossurl'] . DS . $this->config['sy_xcxpic'] : '';
        $this->render_json(0,'ok', $data);
    }

    /**
     * 工具 移动端 小程序配置 微信小程序保存
     */
    function save_action()
    {
        @include(DATA_PATH.'api/wxpay/wxpay_data.php');
        $_POST = $this->post_trim($_POST);
        //小程序用
        $wxpaydata['sy_xcxappid'] =	$_POST['sy_xcxappid'];
        $wxpaydata['sy_xcxsecret'] = $_POST['sy_xcxsecret'];
        $wxpaydata['sy_xcxysid'] = $_POST['sy_xcxysid'];
        $wxpaydata['sy_sphid'] = $_POST['sy_sphid'];
        made_web(DATA_PATH.'api/wxpay/wxpay_data.php',ArrayToString($wxpaydata),'wxpaydata');
        $configM = $this->MODEL('config');
        $data['sy_xcxappid'] =  $_POST['sy_xcxappid'];
        $data['sy_wxxcx_iospay'] = isset($_POST['sy_wxxcx_iospay']) && $_POST['sy_wxxcx_iospay'] == 'true' ? 1 : 2;
//        $data['sy_xcx_contact'] = isset($_POST['sy_xcx_contact']) && $_POST['sy_xcx_contact'] ? 1 : 2;
        $data['sy_xcxQrcode'] = isset($_POST['sy_xcxQrcode']) && $_POST['sy_xcxQrcode'] == 'true' ? 1 : 2;
        $data['sy_xcxname'] = $_POST['sy_xcxname'];
        $data['sy_xcxpath'] = $_POST['sy_xcxpath'];
        $configM->setConfig($data);
        $this->web_config();
        $this->admin_json(0, "微信小程序配置成功！");
    }

    /**
     * 工具 移动端 小程序配置 百度小程序
     */
    function baiduindex_action(){
        @include(DATA_PATH . 'api/baidu/baidu_data.php');
        $data['config'] = array(
            'sy_bdlogin_appKey' => $baiduData['sy_bdlogin_appKey'] ? $baiduData['sy_bdlogin_appKey'] : '',
            'sy_bdlogin_appSecret' => $baiduData['sy_bdlogin_appSecret'] ? $baiduData['sy_bdlogin_appSecret'] : '',
            'sy_dealId' => $baiduData['sy_dealId'] ? $baiduData['sy_dealId'] : '',
            'sy_appKey' => $baiduData['sy_appKey'] ? $baiduData['sy_appKey'] : '',
            'sy_privateKey' => $baiduData['sy_privateKey'] ? $baiduData['sy_privateKey'] : '',
            'sy_publicKey' => $baiduData['sy_publicKey'] ? $baiduData['sy_publicKey'] : '',
            'sy_baidu_iospay' => $this->config['sy_baidu_iospay'] == 1,
        );
        $data['sy_baiduxcxu_qcode'] = $this->config['sy_baiduxcxu_qcode'] ? $this->config['sy_ossurl'] . '/' . $this->config['sy_baiduxcxu_qcode'] : '';
        $this->render_json(0,'ok', $data);
    }
    /**
     * 工具 移动端 小程序配置 百度小程序保存
     */
    function baidusave_action(){
        @include(DATA_PATH . 'api/baidu/baidu_data.php');
        $_POST = $this->post_trim($_POST);
        $str = str_replace("\r\n", '', $_POST['sy_privateKey']);
        $baidu = array(
            'sy_appKey' => $_POST['sy_appKey'],
            'sy_dealId' => $_POST['sy_dealId'],
            'sy_privateKey' => str_replace(PHP_EOL, '', $str),
            'sy_publicKey' => $_POST['sy_publicKey'],
            'sy_baidu_iospay' => isset($_POST['sy_baidu_iospay']) && $_POST['sy_baidu_iospay'] == 'true' ? 1 : 2
        );
        foreach ($baidu as $v) {
            if (empty($v)) {
                $this->render_json(1, '请将参数配置齐');
            }
        }
        $baidu['sy_bdlogin_appKey'] = $_POST['sy_bdlogin_appKey'];
        $baidu['sy_bdlogin_appSecret'] = $_POST['sy_bdlogin_appSecret'];
        made_web(DATA_PATH . 'api/baidu/baidu_data.php', ArrayToString($baidu), 'baiduData');
        unset($baidu['sy_weburl']);
        $configM = $this->MODEL('config');
        $configM->setConfig($baidu);
        $this->web_config();
        $this->admin_json(0, "百度小程序配置成功！");
    }
    /**
     * 工具 移动端 小程序配置 支付宝小程序
     */
    function alipayindex_action(){
        @include(DATA_PATH.'api/alipay/alipay_aop.php');
        $data['config'] = array(
            'sy_xcxAppid' => $alipaydata['sy_xcxAppid'] ? $alipaydata['sy_xcxAppid'] : '',
            'sy_appKey' => $alipaydata['sy_appKey'] ? $alipaydata['sy_appKey'] : '',
            'sy_privateKey' => $alipaydata['sy_privateKey'] ? $alipaydata['sy_privateKey'] : '',
            'sy_publicKey' => $alipaydata['sy_publicKey'] ? $alipaydata['sy_publicKey'] : '',
            'sy_syncday' => $alipaydata['sy_syncday'] ? $alipaydata['sy_syncday'] : '',
            'sy_syncnum' => $alipaydata['sy_syncnum'] ? $alipaydata['sy_syncnum'] : '',
            'sy_synctype' => $alipaydata['sy_synctype'] ? $alipaydata['sy_synctype'] : '0',
            'zm_service_id' => $alipaydata['zm_service_id'] ? $alipaydata['zm_service_id'] : '',
        );
        $data['sy_baiduxcxu_qcode'] = $this->config['sy_baiduxcxu_qcode'] ? $this->config['sy_ossurl'] . '/' . $this->config['sy_baiduxcxu_qcode'] : '';
        $this->render_json(0,'ok', $data);
    }
    /**
     * 工具 移动端 小程序配置 支付宝小程序保存
     */
    function alipaysave_action(){
        @include(DATA_PATH.'api/alipay/alipay_aop.php');
        $_POST = $this->post_trim($_POST);
        $alipay = array(
            'sy_weburl' => $this->config['sy_weburl'],
            'sy_xcxAppid' => $_POST['sy_xcxAppid'],
            'sy_appKey' => $_POST['sy_appKey'],
            'sy_privateKey' => $_POST['sy_privateKey'],
            'sy_publicKey' => $_POST['sy_publicKey'],
            'sy_syncday' => $_POST['sy_syncday'],
            'sy_syncnum' => $_POST['sy_syncnum'],
            'sy_synctype' => $_POST['sy_synctype'],
            'zm_service_id' =>  $_POST['zm_service_id'],
        );
        made_web(DATA_PATH.'api/alipay/alipay_aop.php', ArrayToString($alipay), 'alipaydata');
        $this->web_config();
        $this->admin_json(0, "支付宝小程序配置成功！");
    }
    /**
     * 工具 移动端 小程序配置 抖音小程序
     */
    function ttindex_action(){
        @include(DATA_PATH.'api/bytedance/tt_data.php');
        $data['config'] = array(
            'sy_tt_appId' => $ttData['sy_tt_appId'] ? $ttData['sy_tt_appId'] : '',
            'sy_tt_secret' => $ttData['sy_tt_secret'] ? $ttData['sy_tt_secret'] : '',
            'sy_tt_phoneauth' => $this->config['sy_tt_phoneauth'] == 1,
        );
        $data['sy_ttxcxu_qcode'] = $this->config['sy_ttxcxu_qcode'] ? $this->config['sy_ossurl'] . '/' . $this->config['sy_ttxcxu_qcode'] : '';
        $this->render_json(0,'ok', $data);
    }
    /**
     * 工具 移动端 小程序配置 抖音小程序保存
     */
    function ttsave_action(){
        @include(DATA_PATH.'api/bytedance/tt_data.php');
        $_POST = $this->post_trim($_POST);
        $tt = array(
            'sy_tt_appId' => $_POST['sy_tt_appId'],
            'sy_tt_secret' => $_POST['sy_tt_secret'],
        );
        foreach ($tt as $v){
            if (empty($v)){
                $this->render_json(1, '请将参数配置齐');
            }
        }
        $tt['sy_tt_phoneauth'] = isset($_POST['sy_tt_phoneauth']) && $_POST['sy_tt_phoneauth'] == 'true' ? 1 : 0;
        made_web(DATA_PATH.'api/bytedance/tt_data.php',ArrayToString($tt),'ttData');
        unset($tt['sy_weburl']);
        $configM = $this->MODEL('config');
        $configM->setConfig($tt);
        $this->web_config();
        $this->admin_json(0, "抖音小程序配置成功！");
    }
    /**
     * 工具 移动端 小程序配置 其他小程序
     */
    function otherindex_action(){
        $wxpaydata = array();
        @include(DATA_PATH.'api/wxpay/wxpay_data.php');
        $data['rows'] = array(array('appid' => '', 'secret' => '', 'kf' => '', 'comid' => ''));
        if (!empty($wxpaydata['sy_xcx_other'])){
            $str = $wxpaydata['sy_xcx_other'];
            $arr = explode('|', $str);
            $rows = array();
            foreach ($arr as $k=>$v){
                $tmp = explode(',', $v);
                $rows[] = array(
                    'appid' => $tmp[0],
                    'secret' => $tmp[1],
                    'kf' => $tmp[2],
                    'comid' => $tmp[3]
                );
            }
            $data['rows'] = $rows;
        }
        $this->render_json(0,'ok', $data);
    }
    /**
     * 工具 移动端 小程序配置 其他小程序保存
     */
    function othersave_action(){
        @include(DATA_PATH.'api/wxpay/wxpay_data.php');
        $other = array();
        foreach ($_POST as $v) {
            $other[] = implode(',', $v);
        }
        $wxpaydata['sy_xcx_other'] = implode('|', $other);
        made_web(DATA_PATH.'api/wxpay/wxpay_data.php',ArrayToString($wxpaydata),'wxpaydata');
        $this->admin_json(0, "其他小程序配置成功！");
    }
    /**
     * 工具 移动端 小程序配置 订阅消息模板
     */
    function tplindex_action(){
        $tplM =	$this->MODEL("subtpl");
        $list =	$tplM->getTpls();
        $data = array();
        foreach ($list['list'] as $v) {
            $data[$v['name']] = $v['tpl_id'];
        }
        $this->render_json(0,'ok', array('config' => $data));
    }
    /**
     * 工具 移动端 小程序配置 订阅消息模板保存
     */
    function tplsave_action(){
        $tplM = $this->MODEL('subtpl');
        $tplData = array(
            array('name' => 'xjh_start_tplid', 'tpl_id' => trim($_POST['xjh_start_tplid'])),
            array('name' => 'interview_tplid', 'tpl_id' => trim($_POST['interview_tplid'])),
            array('name' => 'payment_tplid', 'tpl_id' => trim($_POST['payment_tplid'])),
            array('name' => 'job_audit_tplid', 'tpl_id' => trim($_POST['job_audit_tplid'])),
        );
        $nid = $tplM->addTpl($tplData);
        if($nid > 0){
            $this->admin_json(0, '订阅模板配置成功！');
        }else{
            $this->render_json(1, "订阅模板配置失败，请重试！");
        }
    }
    /**
     * 工具 移动端 小程序配置 订阅消息记录
     */
    function tplmsglist_action(){
        $weixinM = $this->MODEL('weixin');
        if ($_POST['keyword']) {
            $keyword = trim($_POST['keyword']);
            $type = intval($_POST['type']);
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
//                $where['utype'] = $type;
                $where['uid'] = $keyword;
            }
        }else{
            $where['utype'] = array('<', 99);
        }
        if (($_POST['date'])) {
            $times = @explode('~', $_POST['date']);
            $where['PHPYUNBTWSTART_A'] = '';
            $where['ctime'][] = array('>=', strtotime($times[0]." 00:00:00"));
            $where['ctime'][] = array('<', strtotime($times[1]." 23:59:59"));
            $where['PHPYUNBTWEND_A'] = '';
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('wx_subscribe_msg', $where, $page, array('limit' => $pageSize));
        $wxMsgList = array();
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = 'id';
            }
            $where['limit'] = $pages['limit'];
            $wxMsgList = $weixinM->getWxSubscribeMsgList($where);
        }
        $data['list'] = $wxMsgList;
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }
    /**
     * 工具 移动端 小程序配置 订阅消息记录-删除
     */
    function delTplMsg_action()
    {
        $weixinM = $this->Model('weixin');
        if (is_array($_POST['del'])) {
            $delid = @implode(',', $_POST['del']);
            $where['id'] = array('in', $delid);
        } else {
            $delid = $_POST['del'];
            $where['id'] = $delid;
        }
        $where['id'] = array('in', $delid);
        $del = $weixinM->delWxsubmsg(array('where' => $where));
        $del ? $this->admin_json(0, '微信订阅消息(ID:' . $delid . ')删除成功！') : $this->render_json(1, '删除失败！');
    }

    /**
     * 小程序SEO配置
     */
    function seo_action(){
        include(CONFIG_PATH."db.data.php");
        if (isset($arr_data)){
            // 去掉用不到的数据
            unset($arr_data['seoconfig']['public']['webkeyword']);
            unset($arr_data['seoconfig']['public']['webdesc']);
            // 保留需要的seo类型
            $seomodel = array();
            foreach ($arr_data['seomodel'] as $k=>$v){
                if (in_array($k, array('index','job','resume','company','part','article','zph','once','tiny','map','special','spview','xjhlive','zph','skill'))){
                    $seomodel[$k] = $v;
                }
            }
            $arr_data['seomodel'] = $seomodel;

            $arr_data['seoconfig']['job']['company_sname'] = '企业简称';
            $arr_data['seoconfig']['company']['company_sname'] = '企业简称';

        }
        $action = $_POST['action'] ? $_POST['action'] : "index";
        $wxAppM = $this->MODEL('wxapp');
        $rows = $wxAppM->getWxSeoList(array('seomodel' => $action));
        foreach ($rows as $k => $v) {
            $rows[$k]['lastupdate_n'] = date('Y-m-d H:i', $v['lastupdate']);
            $rows[$k]['share_pic_n'] = checkpic($v['share_pic']);
        }
        $rt['seomodel'] = $seomodel;
        $rt['seoconfig'] = $arr_data['seoconfig'];
        $rt['list'] = $rows;
        $this->render_json(0, '', $rt);
    }

    /**
     * 小程序SEO保存
     */
    function saveSeo_action(){
        $post = array(
            'name' => $_POST['name'],
            'seomodel' => $_POST['seomodel'],
            'ident' => $_POST['ident'],
            'title' => $_POST['title'],
            'lastupdate' => time(),
            'share_pic' => $_POST['share_pic'],
            'keywords' => $_POST['keywords'],
            'description' => $_POST['description']
        );
        if(!empty($_FILES)){
            if($_FILES['xcxshare_file']['tmp_name']){
                $upArrXcxshare = array(
                    'file' => $_FILES['xcxshare_file'],
                    'dir' => 'xcxshare'
                );
            }
            $uploadM  =  $this->MODEL('upload');
            if(!empty($upArrXcxshare )){
                $pic = $uploadM->newUpload($upArrXcxshare );
            }
            if (!empty($pic['msg'])) {
                $this->render_json(1, $pic['msg']);
            }
            if (!empty($pic['picurl'])){
                $post['share_pic'] = $pic['picurl'];
            }
        }
        $wxAppM = $this->MODEL('wxapp');
        if ($_POST['id']){
            $return = $wxAppM->upWxSeo(array('id' => $_POST['id']), $post);
        }else{
            $return = $wxAppM->addWxSeo($post);
        }
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 小程序SEO删除
     */
    function delSeo_action(){
        if ($_POST['del']){
            $wxAppM = $this->MODEL('wxapp');
            $res = $wxAppM->delWxSeo(array('id' => $_POST['del']));
            if ($res['errcode'] == 9) {
                $this->admin_json(0, $res['msg']);
            } else {
                $this->render_json(1, $res['msg']);
            }
        }else{
            $this->render_json(1, '请选择要删除的SEO');
        }
    }
    
}
?>