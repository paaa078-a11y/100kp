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
class xjhlive_controller extends adminCommon
{
    function set_search()
    {
        $search_list    =   array();
        $search_list[]  =   array(
            'param'     =>  'end',
            'name'      =>  '创建时间',
            'value'     =>  array(
                '1'     =>  '今天',
                '3'     =>  '最近三天',
                '7'     =>  '最近七天',
                '15'    =>  '最近半月',
                '30'    =>  '最近一个月'
            )
        );
        $search_list[]  =   array(
            'param'     =>  'livestatus',
            'name'      =>  '直播状态',
            'value'     =>  array(
                '1'     =>  '未开始',
                '3'     =>  '直播中',
                '2'     =>  '已结束'
            )
        );
        $this->yunset('search_list', $search_list);
    }

    function getGroup_action(){
        //提取分站内容
        $cacheM = $this->MODEL('cache');
        $domain = $cacheM->GetCache('domain');
        $rt['Dname'] = (object)$domain['Dname'];
        $rt['preview_url'] = Url('xjhlive', array('c' => 'show', 'id' => ''));
        $this->render_json(0, '', $rt);
    }
    /**
     * @desc 后台  - 宣讲会列表
     */
    function index_action() {
        $where = array();
        if ($_POST['status']) {
            $status = intval($_POST['status']);
            $where['status'] = $status;
        }
        if ($_POST['keyword']) {
            $keyword = trim($_POST['keyword']);
            $where['name'] = array('like', $keyword);
        }
        if ($_POST['end']) {
            if ($_POST['end'] == '1') {
                $where['ctime'] = array('>=', strtotime('today'));
            } else {
                $where['ctime'] = array('>=', strtotime('-'.intval($_POST['end']).' day'));
            }
        }
        if (isset($_POST['livestatus'])) {
            $livestatus = intval($_POST['livestatus']);
            $where['livestatus'] = $livestatus;
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('xjhlive', $where, $page, array('limit' => $pageSize));
        $xjhM  = $this->MODEL('xjhlive');
        // 分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            // limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('status,asc','id,desc');
            }
            $where['limit'] = $pages['limit'];
            $rows = $xjhM->getList($where,array('utype' => 'admin', 'num' => 1));
            $picarr = array();
            foreach($rows as $k => $v){
                if(!empty($v['picarr'])){
                    $picarr[$v['id']] = $v['picarr'];
                }
            }
        }
        $rt['list'] = $rows ? $rows : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $rt['picarr'] = $picarr ? array_values($picarr) : array();
        $this->render_json(0, '', $rt);
    }
    /**
     * 列表切换分站
     */
    function checksitedid_action(){
        if($_POST['uid']){
            $uids =	@explode(',', $_POST['uid']);
            $uid = pylode(',', $uids);
            if($uid){
                $siteDomain = $this->MODEL('site');
                $siteDomain->updDid(array('xjhlive'), array('id' => array('in', $uid)), array('did' => $_POST['did']));
                $this->admin_json(0, '直播宣讲会(ID:'.$_POST['uid'].')分配站点成功！');
            }else{
                $this->render_json(1, '请正确选择需分配数据！');
            }
        }else{
            $this->render_json(1, '参数不全请重试！');
        }
    }

    function add_action(){
        if($_POST['id']){
            $xjhM = $this->MODEL('xjhlive');
            $info = $xjhM -> getInfo(array('id'=>intval($_POST['id'])));
            $rt['info'] = $info;
        }
        $cacheM = $this->MODEL('cache');
        $cache = $cacheM->GetCache(array('city'));
        $domain = $cacheM->GetCache('domain');
        $rt['Dname'] = (object)$domain['Dname'];
        if(empty($cache['city_type'])){
            $rt['cionly'] = 1;
        }
        $rt['playtime'] = array(1=>'1小时',2=>'2小时',4=>'4小时');
        $rt['cache'] = $cache;
        $rt['now'] = date('Y-m-d H:i:s',time());
        $this->render_json(0, '', $rt);
    }
    /**
     * 直播宣讲会保存
     */
    function save_action(){
        $info = $this->post_trim($_POST);
        $newpic = array();
        foreach ($_FILES['pic'] as $nk => $nv) {
            foreach ($nv as $nkk => $nvv) {
                $newpic[$nkk][$nk] = $nvv;
            }
        }
        $post = array(
            'name' => $info['name'],
            'pic' => $newpic,
            'body' => $info['body'],
            'ctime' => time(),
            'stime' => strtotime($info['stime_n']),
            'playtime' => 4, // 6.3后直播时长固定为4小时
            'state' => $info['state'],
            'statetime' => strtotime($info['statetime_n']),
            'playback' => $info['playback'],
            'did' => $info['did'],
            'hits_base' => $info['hits_base'],
            'status' => 1,
            'isopen' => $info['isopen']
        );
        $data = array(
            'post' => $post,
            'id' => intval($info['id'])
        );
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->addXjh($data, 'file');
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    /**
     * 搜索按名称参会企业
     */
    function getZcom_action(){
        $result = array();
        if (!empty($_POST['keyword'])) {
            $where['r_status'] = 1;
            $where['name'] = array('like', trim($_POST['keyword']));
            $where['limit'] = 10;
            $comM = $this->MODEL('company');
            $comArr = $comM->getList($where, array('field' => '`uid`,`name`'));
            if (!empty($comArr)) {
                $comList = $comArr['list'];
                foreach ($comList as $k => $v){
                    $result[$k]['name'] = $v['name'];
                    $result[$k]['value'] = $v['uid'];
                }
            }
        }
        $this->render_json(0, '', $result);
    }

    function com_action(){
        $xjhM = $this->MODEL('xjhlive');
        if($_POST['id']){
            $where['xid'] = intval($_POST['id']);
        }
        if (trim($_POST['keyword'])){
            $companyM = $this->MODEL('company');
            $company = $companyM->getChCompanyList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));
            if($company){
                foreach($company as $v){
                    $uid[] = $v['uid'];
                }
                $where['uid'] = array('in',pylode(',', $uid));
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('xjhlive_com', $where, $page, array('limit' => $pageSize));
        if($pages['total'] > 0){
            $where['orderby'] = array('id,desc');
            $where['limit'] = $pages['limit'];
            $rows = $xjhM->getXjhComList($where, array('utype' => 'admin'));
        }
        $rt['list'] = $rows ? $rows : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }
    function del_action(){
        if($_POST['del']){
            $delID = $_POST['del'];
        }
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->delXjh($delID);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 添加参会企业
     */
    function comadd_action(){
        $xjhM = $this->MODEL('xjhlive');
        $xjh = $xjhM->getInfo(array('id' => intval($_POST['id'])), array('field' => '`id`,`name`'));
        $rt['info'] = $xjh;
        $ComM = $this->MODEL('company');
        $where = array('r_status'=>1);
        $mwhere = array();
        if($_POST['keyword']){
            $keywordStr = trim($_POST['keyword']);
            $typeStr = intval($_POST['type']);
            if (!empty($keywordStr) && $typeStr == 1) {
                $where['PHPYUNBTWSTART_C'] = '';
                $where['name'] = array('like',$keywordStr);
                $where['shortname'] = array('like',$keywordStr,'OR');
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
                $mwhere['reg_date'] = array('>', strtotime('-' . $adtime . ' day'));
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
            if($lotime == 1){
                $mwhere['login_date'] = array('>', strtotime('today'));
            }else if ($lotime < 31){
                $mwhere['login_date'] = array('>', strtotime('-' . $lotime.' day'));
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
                $where['orderby'] =	array('vipetime,desc', 'uid,desc');
            }else if($_POST['time']){
                $where['orderby'] =	array('vipetime,asc');
            }else if($_POST['lotime']){
                $where['orderby'] =	array('login_date,desc');
            }else{
                $where['orderby'] =	'uid,desc';
            }
            $where['limit'] = $pages['limit'];
            $ListNew = $ComM->getList($where, array('utype' => 'admin'));
            // 查询本场网招参会企业，在待添加企业列表中展示，企业是否已参加
            $netcom = $xjhM->getXjhComList(array('xid' => $_POST['id']), array('field' => 'uid'));
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
        $rt['com_icon'] = $this->config['sy_ossurl'] . '/' . $this->config['sy_unit_icon'];
        $rt['today_etime'] = strtotime(date('Y-m-d 23:59:59'));
        $this->render_json(0, '', $rt);
    }
    /**
     * 保存参会企业
     */
    function comaddsave_action(){
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->addXjhCom($_POST);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    /**
     * 删除参会企业
     */
    function delcom_action(){
        $xjhM = $this->MODEL('xjhlive');
        $delID = $_POST['del'];
        $xid = intval($_POST['xid']);
        $return = $xjhM->delXjhCom($delID, $xid);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    function xjhchat_action()
    {
        if (intval($_POST['uid']) > 0) {
            $where['uid']  = intval($_POST['uid']);
        }
        if (intval($_POST['xid']) > 0) {
            $where['xid']  = intval($_POST['xid']);
        }
        if ($_POST['keyword']) {
            $keyword = trim($_POST['keyword']);
            $type = intval($_POST['type']);
            if ($type == '1') {
                $where['fuid'] = array('=', $keyword);
            } else if ($type == '2') {
                $userinfoM = $this->MODEL('userinfo');
                $member = $userinfoM->getList(array('username' => array('like', $keyword)), array('field' => '`uid`'));
                if (!empty($member)){
                    foreach ($member as $v){
                        $fuid[] = $v['uid'];
                    }
                    $where['fuid'] = array('in', pylode(',', $fuid));
                }
            }
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('xjhlive_chat', $where, $page, array('limit' => $pageSize));
        // 分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            // limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('sendTime,desc');
            }
            $where['limit'] = $pages['limit'];
            $chatM = $this->MODEL('chat');
            $List = $chatM->getChatList($where,array('fdata'=>1,'utype'=>'admin'));
        }
        $rt['list'] = $List ? $List : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }
    /**
     * 删除宣讲会聊天
     */
    function msgdel_action()
    {
        $chatM = $this->Model('chat');
        if($_POST['del']){
            $return = $chatM->delXjhchat($_POST['del']);
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        }else{
            $this->render_json(1, "请选择您要删除的信息！");
        }
    }
    /**
     * 禁言
     */
    function forbidden_action()
    {
        if($_POST['del']){
            $chatM = $this->MODEL('chat');
            $row = $chatM->getXjhChat(array('id' => $_POST['del']));
            $data = array(
                'fuid' => $row['fuid'],
                'usertype' => $row['fusertype'],
                'xjhid' => $row['xid']
            );
            $return = $chatM->addXjhchatBlack($data);
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        }else{
            $this->render_json(1, "请选择您要禁言的用户！");
        }
    }
    /**
     * 解除禁言
     */
    function delblack_action()
    {
        if($_POST['del']){
            $chatM = $this->MODEL('chat');
            $row = $chatM->getXjhChat(array('id'=>$_POST['del']));
            $where = array(
                'xid' => $row['xid'],
                'uid' => $row['fuid'],
                'usertype' => $row['fusertype']
            );
            $return = $chatM->delXjhchatBlack('', array('where' => $where));
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        }else{
            $this->render_json(1, "请选择您要解禁的用户！");
        }
    }
    /**
     * 删除封面图片
     */
    function delPic_action(){
        $delID = $_POST['pid'];
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->delXjhPic($delID, array('utype' => 'admin'));
        $this->render_json($return['errcode'] == 9 ? 0 : 1, $return['errcode'] == 9 ? '删除成功' : '删除失败');
    }
    /**
     * 获取直播二维码地址
     */
    function getLive_action(){
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->getLiveQrcode(array('id'=>$_POST['id']));
        if ($return['errcode'] == 9) {
            $this->render_json(0, '', array('url' => $return['url']));
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    /**
     * 开始宣讲会直播
     */
    function startEnd_action(){
        $xjhM = $this->MODEL('xjhlive');
        $xjh = $xjhM->getInfo(array('id' => intval($_POST['id'])), array('field' => '`id`,`livestatus`'));
        if (!empty($xjh)){
            $return = $xjhM->liveStart(array('id' => $xjh['id']));
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        }else{
            $this->render_json(1, "宣讲会不存在");
        }
    }
    /**
     * 关闭宣讲会直播
     */
    function liveEnd_action(){
        $xjhM = $this->MODEL('xjhlive');
        $xjh = $xjhM->getInfo(array('id' => intval($_POST['id'])), array('field' => '`id`,`livestatus`'));
        if (!empty($xjh)){
            $return = $xjhM->liveEnd(array('id' => $xjh['id']));
            if ($return['errcode'] == 9) {
                $this->admin_json(0, $return['msg']);
            } else {
                $this->render_json(1, $return['msg']);
            }
        }else{
            $this->render_json(1, "宣讲会不存在");
        }
    }
    /**
     * 导播服务
     */
    function caster_action(){
        $xid = $_POST['id'];
        $xjhM = $this->MODEL('xjhlive');
        // 查询导播播放地址
        $xjh = $xjhM->getInfo(array('id' => $xid));
        // 查询导播台素材
        $list =	$xjhM->getMaterials(array('xid' => $xid, 'orderby' => 'id,asc'));
        $this->render_json(0, '', array('xjh' => $xjh, 'pgmurl' => $xjh['hls'], 'list' => $list));
    }
    /**
     * 导播素材
     */
    function material_action(){
        $xjhM = $this->MODEL('xjhlive');
        $list = $xjhM->getMaterials(array('xid' => $_POST['id'], 'orderby' => 'id'));
        $this->render_json(0, '', $list);
    }
    function addMaterial_action(){
        $xjhM = $this->MODEL('xjhlive');
        $row = $xjhM->getMaterial(array('id' => $_POST['mid']));
        $this->render_json(0, '', $row);
    }
    function saveMaterial_action(){
        $_POST = $this->post_trim($_POST);
        $xid = $_POST['xid'];
        $xjhM = $this->MODEL('xjhlive');
        $list = $xjhM->getMaterials(array('xid' => $xid));
        if (count($list) >= 8){
            $this->render_json(1, '单个导播台最多可上传8份素材');
        }
        $arr = array(
            'xid' => $xid,
            'name' => $_POST['name'],
            'ctime' => time()
        );
        if (!empty($_FILES['file']['tmp_name'])){
            $result = $xjhM->uploadMaterial($_FILES['file'], $arr);
            if (!empty($result['msg'])){
                $this->render_json(1, $result['msg']);
            }
            $arr['url'] = $result['url'];
            $arr['syncid'] = $result['id'];
        }else{
            if (empty($_POST['id'])){
                $this->render_json(1, '请上传文件');
            }
        }
        if (!empty($_POST['id'])){
            $xjhM->upMaterial(array('id' => $_POST['id']), $arr);
        }else{
            $xjhM->addMaterial($arr);
        }
        $this->admin_json(0, '上传成功');
    }
    /**
     * 设置暖场视频
     */
    function setVideo_action(){
        $data = array(
            'state' => $_POST['state']
        );
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->setCasterVideo(array('id' => $_POST['id'], 'xid' => $_POST['xid']), $data);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    /**
     * 创建导播台
     */
    function createCaster_action(){
        $data = array(
            'xid' => $_POST['id']
        );
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->createCaster($data);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    /**
     * 开启导播台
     */
    function openCaster_action(){
        $data = array(
            'xid' => $_POST['id']
        );
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->openCaster($data);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    /**
     * 导播台切换监视器画面，并切换布局
     */
    function upMonitor_action(){
        $data = array(
            'xid' => $_POST['xid'],
            'mid' => $_POST['mid'],
            'layout' => $_POST['layout']
        );
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->upMonitor($data);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    /**
     * 导播台把画面切换为正常直播画面
     */
    function liveCaster_action(){
        $data = array(
            'xid' => $_POST['xid']
        );
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->liveCaster($data);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    /**
     * 查询是否可以创建宣讲会
     */
    function canAdd_action(){
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->xjhCanAdd();
        $this->render_json(0, '', $return);
    }
    // 查询推流地址
    function getPushurl_action(){
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->getPushUrl(array('id' => $_POST['id']));
        if ($return['errcode'] == 9) {
            $this->render_json(0, '', array('push_url' => $return['live']));
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    // 直播控制台
    function control_action(){
        $xjhM = $this->MODEL('xjhlive');
        $info = $xjhM->getInfo(array('id' => intval($_POST['id'])), array('utype' => 'admin'));
        $info['chat_url'] = $rt['chat_weburl'] = $this->config['sy_chat_weburl'];
        $info['live_poster'] = $this->config['sy_weburl']."/app/template/".$this->config['style'] . '/images/livewating.jpg';
        $info['qn_set'] = $this->config['sy_weburl']."/app/template/".$this->config['style'] . '/images/qiniuset.png';
        $info['mbstyle'] = $this->config['sy_weburl']."/app/template/".$this->config['style'];
        $this->render_json(0, '', $info);
    }
    // 控制台查询企业列表
    function getControlData_action(){
        $xjhM = $this->MODEL('xjhlive');
        $rows = $xjhM->getXjhComList(array('xid' => $_POST['xid'], 'orderby' => array('`status`,ASC','`ctime`,ASC')), array('utype' => 'admin'));
        $chatM = $this->MODEL('chat');
        $black = $chatM->getXjhchatBlackList(array('xid' => $_POST['xid']));
        // 获取正在讲解的企业
        if (!empty($rows)){
            foreach ($rows as $k=>$v){
                if ($v['status'] == 1){
                    $explain = $v;
                    if ($explain['joblist']) {
                        $explain['joblist'] = array_chunk($v['joblist'], 3);
                    }
                }
            }
        }
        $res = array(
            'comlist' => !empty($rows) ? array_chunk($rows, 3) : array(),
            'jylist' => !empty($black) ? $black : array(),
            'explain' => isset($explain) ? $explain: array()
        );
        $this->render_json(0, '', $res);
    }
    // 控制台查询企业列表
    function getControlCom_action(){
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
        $xjhM = $this->MODEL('xjhlive');
        $rows = $xjhM->getXjhComList(array('xid' => $_POST['xid'], 'orderby' => array('`status`,ASC')), array('utype' => 'admin','keyword' => $keyword));
        $this->render_json(0, '', $rows ? array_chunk($rows, 3) : array());
    }
    // 控制台设置讲解企业
    function setExplain_action(){
        $xjhM = $this->MODEL('xjhlive');
        $row = $xjhM->getInfo(array('id' => $_POST['xid']));
        if ($row['livestatus'] == 2){
            $this->render_json(1, '直播已结束，无法设置');
        }
        // 先将正在讲解中的结束
        $xjhM->upXjhCom(array('xid' => $_POST['xid'], 'status' => 1), array('status' => 3, 'etime' => time()));
        // 设置新的讲解企业
        $xjhM->upXjhCom(array('id'=>$_POST['id']),array('status'=>1, 'stime'=>time()));
        $this->admin_json(0, '直播(ID:'.$_POST['id'].')设置讲解企业(ID:'.$_POST['id'].')成功', array('xjhsyncid' => $row['syncid']));
    }
    // 控制台查看聊天记录
    function getXjhChatPage_action()
    {
        $arr = array(
            'xid' => $_POST['xid'],
            'orderby' => '`id`,DESC',
        );
        $page = $_POST['page'];
        $limit = 30;
        if($page){//分页
            $pagenav = ($page - 1) * $limit;
            $arr['limit'] = array($pagenav, $limit);
        }else{
            $arr['limit'] = $limit;
        }
        $chatM = $this->MODEL('chat');
        $return = $chatM->getChatList($arr,array('fdata' => true, 'utype' => 'admin'));
        $list = array();
        foreach($return as $k => $v){
            $list[$k]['id'] = $v['fuid'];
            $list[$k]['username'] = !empty($v['user']) ? $v['user']['username'] : $v['username'];
            $list[$k]['avatar'] = !empty($v['user']) ? $v['user']['avatar'] : $v['avatar'];
            $list[$k]['usertype'] = $v['fusertype'];
            $list[$k]['content'] = $v['content'];
            $list[$k]['msgtype'] = $v['msgtype'];
            $list[$k]['sendTime'] = $v['sendTime'];
            $list[$k]['xjhid'] = 'xjh_' . $_POST['xid'];
            $list[$k]['xid'] = $_POST['xid'];
            $list[$k]['chatid'] = $v['id'];
            $list[$k]['jy'] = 0;
            if (isset($v['job'])){
                $list[$k]['job'] = $v['job'];
            }
        }
        $chatnum = $chatM->getChatNum(array('xid'=>$arr['xid']));
        $ismore = ceil($chatnum/$limit) > $page ? true : false;
        $result = array(
            'list' => $list,
            'ismore' => $ismore,
            'chat_logo' => $this->config['sy_ossurl'] . '/' . $this->config['sy_chat_logo'],
            'web_name' => $this->config['sy_webname']
        );
        $this->render_json(0, '', $result);
    }
    // 未登录用户获取聊天登录参数
    function getUnloginToken_action(){
        // 所有管理员只用一个账号
        $chatM = $this->MODEL('chat');
        $member = $chatM->getMember(array('usertype' => 9,'orderby' => 'id,ASC'));
        if (!empty($member)){
            $auid = $member['uid'];
        }else{
            $auid = $_SESSION['auid'];
        }
        $csM = $this->MODEL('chatcs');
        $data = $csM->getUnloginToken('admin', array('auid' => $auid));
        $this->render_json(0, '', $data);
//        echo json_encode($data);
    }
    // 保存宣讲会聊天记录
    function chatlog_action(){
        // 保存聊天记录
        $arr = array(
            'fuid' => $_SESSION['auid'],
            'fusertype' => 9,
            'ip' => fun_ip_get(),
            'content' => $_POST['content'],
            'timestamp' => $_POST['timestamp'],
            'msgtype' => $_POST['msgtype'],
            'xjhid' => $_POST['xjhid']
        );
        $chatM = $this->MODEL('chat');
        $chatM->xhjChat($arr);
        $this->render_json(0, '');
    }
    // 设置禁言
    function setNochat_action(){
        $id = intval($_POST['xid']);
        $st = 9;
        $msg = '直播(ID: '.$id.')';
        if (isset($_POST['nochat'])){
            $data['nochat'] = $_POST['nochat'];
            if ($_POST['nochat'] == 1){
                $msg .= '设置全部禁言';
            }else{
                $msg .= '解除全部禁言';
            }
        }elseif (isset($_POST['vistor_nochat'])){
            $data['vistor_nochat'] = $_POST['vistor_nochat'];
            if ($_POST['vistor_nochat'] == 1){
                $msg .= '设置游客禁言';
            }else{
                $msg .= '解除游客禁言';
            }
        }
        if (isset($data)){
            $xjhM = $this->MODEL('xjhlive');
            $nid = $xjhM->upXjh(array('id' => $id), $data);
            if (!$nid){
                $st = 8;
            }
        }
        if ($st == 9) {
            $this->admin_json(0, $msg);
        } else {
            $this->render_json(1, $msg);
        }
    }
    // 控制台设置禁言
    function setjy_action(){
        $chatM = $this->MODEL('chat');
        $row = $chatM->getXjhChat(array('id' => $_POST['chatid']));
        $data =	array(
            'fuid' => $row['fuid'],
            'usertype' => $row['fusertype'],
            'xjhid' => $row['xid']
        );
        $return = $chatM->addXjhchatBlack($data);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    // 控制台取消禁言
    function removejy_action(){
        $chatM = $this->MODEL('chat');
        $where = array(
            'uid' => $_POST['uid'],
            'usertype' => $_POST['usertype'],
            'xid' => $_POST['xid']
        );
        $return = $chatM->delXjhchatBlack(null, array('where' => $where));
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    // 控制台统计数据
    function getTj_action(){
        $id = intval($_POST['xid']);
        $xjhM = $this->MODEL('xjhlive');
        $row = $xjhM->getInfo(array('id' => $id));
        $duration = 0;
        $regwhere = array();
        $sqwhere  = array();
        if ($row['livestatus'] == 3){
            // 直播中
            $duration = ceil((time() - $row['stime'])/60);
            $regwhere['reg_date'][]  =  array('>=', $row['stime']);
            $regwhere['reg_date'][]  =  array('<=', time());
            $sqwhere['datetime'][] = array('>=', $row['stime']);
            $sqwhere['datetime'][] = array('<=', time());
        }elseif ($row['livestatus'] == 2){
            // 直播结束
            if (!empty($row['etime'])){
                // 主动点了结束直播(单位：分钟)
                $duration = ceil(($row['etime'] - $row['stime'])/60);
            }else{
                // 没有结束时间，按设置的直播时长计算(单位：分钟)
                $duration = $row['playtime'] * 60;
                $row['etime'] = $row['stime'] + $row['playtime'] * 3600;
            }
            $regwhere['reg_date'][] = array('>=', $row['stime']);
            $regwhere['reg_date'][] = array('<=', $row['etime']);
            $sqwhere['datetime'][] = array('>=', $row['stime']);
            $sqwhere['datetime'][] = array('<=', $row['etime']);
        }
        // 观看记录数量
        $num = $xjhM->getUserlogNum(array('xid' => $id));
        // 入场记录
        $log = $xjhM->getUserlogList(array('xid' => $id, 'orderby' => 'ctime', 'limit' => 10));
        // 注册人数
        if (!empty($regwhere)){
            $userinfoM = $this->MODEL('userinfo');
            $regnum = $userinfoM->getMemberNum($regwhere);
        }
        // 投递简历数
        if (!empty($sqwhere)){
            $jobM = $this->MODEL('job');
            $sqnum = $jobM->getSqJobNum($sqwhere);
        }
        $res = array(
            'pv' => $row['hits'],
            'uv' => $num,
            'onlinenum' => $row['onlinenum'],
            'duration' => $duration,
            'regnum' => isset($regnum) ? $regnum : 0,
            'sqnum' => isset($sqnum) ? $sqnum : 0,
            'log' => $log
        );
        $this->render_json(0, '', $res);
    }
    // 趋势数据
    function tendency_action(){
        $id = intval($_POST['xid']);
        $xjhM = $this->MODEL('xjhlive');
        $logStats = $xjhM->userlogTj($id);
        $this->render_json(0, '', $logStats);
    }
    // 开始直播
    function startlive_action(){
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->liveStart(array('id' => $_POST['id']));
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    //入场记录
    function userlog_action(){
        $id = intval($_POST['xid']);
        $where['xid'] = $id;
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('xjhlive_user', $where, $page, array('limit' => $pageSize));
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('id,desc');
            }
            $where['limit'] = $pages['limit'];
            $where['xid'] = $id;
            $xjhM = $this->MODEL('xjhlive');
            $List = $xjhM->getUserlogList($where);
        }
        $rt['list'] = $List ? $List : array();
        $rt['total'] = intval($pages['total']);
        $rt['perPage'] = $pageSize;
        $rt['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0, '', $rt);
    }
    // 生成回放
    function playback_action(){
        $sql = array(
            'stime' => strtotime($_POST['stime']),
            'etime' => strtotime($_POST['etime'])
        );
        $xjhM = $this->MODEL('xjhlive');
        $return = $xjhM->liveRecord(array('id' => $_POST['id']), $sql);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
    /**
     *
     */
    function comaddsearch_action(){

        $ratingM    =   $this -> MODEL('rating');
        $rating     =   $ratingM -> getList(array( 'category' => '1', 'orderby' => 'sort' ), array('field'=>'`id`,`name`'));
        if(!empty($rating)){
            $ratingarr    =   array();
            foreach($rating  as  $k => $v){
                $ratingarr[$v['id']]   =   $v['name'];
            }
        }
        include(CONFIG_PATH.'db.data.php');

        $source             =   $arr_data['source'];

        $timeSection        =   array('1'=>'今天', '3'=>'3天内', '7'=>'7天内', '15'=>'半月内', '30'=>'1个月内', '31'=> '1-3个月', '32' => '3-6个月', '33' => '6个月-1年', '34' => '1年以上');

        $status             =   array('1'=>'已审核', '2'=>'已锁定', '3'=>'未通过', '4'=>'未审核','5'=>'已暂停');

        $edtime             =   array('1'=>'7天内', '2'=>'一个月内', '3'=>'半年内', '4'=>'一年内', '5'=>'已到期');

        $isrec              =   array('1'=>'是', '2'=>'否', '3'=>'已到期');

        $isgw               =   array('1'=>'已分配', '2'=>'未分配');

        $this -> render_json(0,'',array('ratingarr' => $ratingarr,'sourceArr' => $source, 'timeSection' => $timeSection, 'statusArr' => $status,'edtime'=>$edtime,'isrec'=>$isrec,'isgw'=>$isgw));

    }
}