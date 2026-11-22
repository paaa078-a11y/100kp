<?php

class diy_controller extends adminCommon{
    /**
     * 工具 移动端 模板DIY app模板
     */
    function app_action(){
        $tplapp = array();
        $hd = array();
        $adhd = array();
        include(DATA_PATH.'api/wxapp/tplapp.cache.php');
        include(DATA_PATH.'api/wxapp/tplappmodel.cache.php');
        if ($tplapp) {
            $wxAppM = $this->MODEL('wxapp');
            $row = $wxAppM->getTplApp(array('name'=>'isnew'));
            if (!empty($row)){
                $tplapp['isnew'] = 1;
            }
            if (empty($tplapp['adhdid'])){
                $tplapp['adhdid'] = 503;
            }
            if (!empty($tplapp['jobsort'])) {
                $tplapp['jobsort'] = explode(',', $tplapp['jobsort']);
            } else {
                $tplapp['jobsort'] = array('newjob', 'urgjob', 'recjob', 'mapjob'); // 默认职位排序
            }
            // 公告图片数据处理
            if (!empty($tplapp['noticestyleimg'])) {
                $tplapp['noticestyleimg'] = explode(',', $tplapp['noticestyleimg']);
                array_walk($tplapp['noticestyleimg'], function (&$value, $key) {
                    $value = strpos($value, 'upload') ? checkpic($value) : getAdminPath($value); // 上传过的数据特殊处理
                });
            } else {
                $tplapp['noticestyleimg'] = array(
                    getAdminPath('images/appdiy_gg.png'),
                    getAdminPath('images/appdiy_gg2.png'),
                    getAdminPath('images/appdiy_gg3.png')
                ); // 默认图片
            }
            // 登录/注册图标
            $tplapp['login_icon1'] = !empty($tplapp['login_icon1']) && strpos($tplapp['login_icon1'], 'upload') ? checkpic($tplapp['login_icon1']) : getAdminPath('images/appdiy_login1.png');
            $tplapp['login_icon2'] = !empty($tplapp['login_icon2']) && strpos($tplapp['login_icon2'], 'upload') ? checkpic($tplapp['login_icon2']) : getAdminPath('images/appdiy_login2.png');
            // 客服图标
            $tplapp['kficon'] = !empty($tplapp['kficon']) && strpos($tplapp['kficon'], 'upload') ? checkpic($tplapp['kficon']) : getAdminPath('images/appdiy_kefu.png');
            if ($tplapp['hdid'] || $tplapp['adhdid'] || $tplapp['adhdid2'] || $tplapp['adhdid3']) {
                $time = time();
                include(PLUS_PATH . 'pimg_cache.php');
                if (isset($ad_label)) {
                    if ($ad_label[$tplapp['hdid']]) {
                        foreach ($ad_label[$tplapp['hdid']] as $hk => $hv) {
                            if ($hv['type'] == 'pic' && $hv['start'] < $time && $hv['end'] > $time) {
                                $hd[$hk]['pic'] = $hv['pic'];
                            }
                        }
                    }
                    if ($ad_label[$tplapp['adhdid']]) {
                        foreach ($ad_label[$tplapp['adhdid']] as $hk => $hv) {
                            if ($hv['type'] == 'pic' && $hv['start'] < $time && $hv['end'] > $time) {
                                $adhd = $hv['pic'];
                            }
                        }
                    }
                    if ($ad_label[$tplapp['adhdid2']]) {
                        foreach ($ad_label[$tplapp['adhdid2']] as $hk => $hv) {
                            if ($hv['type'] == 'pic' && $hv['start'] < $time && $hv['end'] > $time) {
                                $adhd2 = $hv['pic'];
                            }
                        }
                    }
                    if ($ad_label[$tplapp['adhdid3']]) {
                        foreach ($ad_label[$tplapp['adhdid3']] as $hk => $hv) {
                            if ($hv['type'] == 'pic' && $hv['start'] < $time && $hv['end'] > $time) {
                                $adhd3 = $hv['pic'];
                            }
                        }
                    }
                }
            }
        }
        $data['tplapp'] = $tplapp;
        $data['hd'] = $hd;
        $data['adhd'] = $adhd;
        $data['adhd2'] = $adhd2;
        $data['adhd3'] = $adhd3;
        $navsort = $mid = array();
        if (isset($tplappmodel)) {
            foreach ($tplappmodel as $v) {
                $mid[] = $v['id'];
            }
            //导航
            include_once(CONFIG_PATH.'db.data.php');
            $zphnet_web   =  isset($arr_data['modelconfig']['zphnet']) && isset($this->config['sy_zphnet_web']) ? $this->config['sy_zphnet_web'] : 2;
            $spview_web   =  isset($arr_data['modelconfig']['spview']) && isset($this->config['sy_spview_web']) ? $this->config['sy_spview_web'] : 2;
            $xjhlive_web  =  isset($arr_data['modelconfig']['xjhlive']) && isset($this->config['sy_xjhlive_web']) ? $this->config['sy_xjhlive_web'] : 2;
            foreach ($nav as $nk => $nv) {
                if ($zphnet_web == 2 && $nv['url']=='18'){
                    // 网络招聘会模块未开启，需屏蔽
                    unset($nav[$nk]);
                }elseif ($xjhlive_web == 2 && $nv['url']=='19'){
                    // 直播宣讲会模块未开启，需屏蔽。百度小程序、头条小程序不支持观看直播
                    unset($nav[$nk]);
                }elseif ($spview_web == 2 && $nv['url']=='20'){
                    // 视频面试模块未开启，需屏蔽。百度小程序、头条小程序不支持视频面试
                    unset($nav[$nk]);
                }
            }
            foreach ($nav as $k => $v) {
                $nav[$k]['pic'] = checkpic($v['pic']);
                $nav[$k]['news_class'] = $v['news_class'] ? $v['news_class'] : '';
                $nav[$k]['display_n'] = $v['display'] == '1'? true : false;
                $nav[$k]['navtype'] = $v['navtype'] == '1' ? '1' : '0';
                $navsort[] = $v['navsort'];
            }
            array_multisort($navsort, SORT_ASC, SORT_NUMERIC, $nav);
            $data['nav'] = $nav;
            $navarr = array_chunk($nav, 5);
            $data['navarr'] = $navarr;
        }
        if (isset($footnav)) {
            $midbutton = array();
            foreach ($footnav as $k => $v) {
                $arr = explode(',', $v['pic']);
                $footnav[$k]['pic'] = checkpic($arr[0]);
                if (count($arr) > 1){
                    $footnav[$k]['curpic'] = checkpic($arr[1]);
                }
                if($v['url']=='midButton'){
                    $midbutton[] = $footnav[$k];
                    unset($footnav[$k]);
                }
            }
            if(!empty($midbutton)){
                array_splice($footnav, 2, 0,$midbutton);
            }
            $data['footnav'] = $footnav;
            if ($tplapp['sort']) {
                $sort = explode(',', $tplapp['sort']);
            } else {
                $sort = 'search,member,nav,jobspec,notice,login,hotcom,hotjob,ad,job,zph,article,syszz,sysyjlogin,connect,foot';
                $sort = explode(',', $sort);
            }
            $sortablearr = array('ad', 'login', 'hotcom', 'hotjob', 'article', 'zph', 'job');
            $newsort = array();
            $tmp = array(
                'ad' => '通栏广告',
                'login' => '登录/注册',
                'hotcom' => '名企展示',
                'hotjob' => '热门职位（系统-类别-职位类别-首页推荐）',
                'article' => '资讯',
                'zph' => '招聘会',
                'job' => '职位列表'
            );
            foreach ($sort as $v) {
                in_array($v, $sortablearr) && $newsort[] = array('label' => $tmp[$v], 'value' => $v);
            }
            $data['newsort'] = $newsort;
        }
        $data['navigation'] = $tplappmodel;
        // 广告类别
        $adClassList = $this->MODEL('ad')->getAdClassList(array(
            'place' => 2,
            'orderby' => 'orders,desc'
        ), array('field' => '`id`,`class_name`,`place`'));
        $data['adClassList'] = $adClassList['list'];

        // 获取新闻类别
        $newsClassList = $this->MODEL('article')->getClass(array('isson' => 1));
        $newsClass = array();
        if ($newsClassList['one_class']) {
            foreach ($newsClassList['one_class'] as $nckey => $ncval) {
                if ($newsClassList['two_class'] && !empty($newsClassList['two_class'][$ncval['id']])) {
                    $ncval['children'] = array_merge($newsClassList['two_class'][$ncval['id']]);
                    unset($newsClassList['two_class'][$ncval['id']]);
                }
                $newsClass[] = $ncval;
            }
        }
        $data['newsClassList'] = $newsClass;
        $data['accept'] = $this->config['pic_type'];
        $this->render_json(0,'ok', $data);
    }

    /**
     * 图片数组处理
     * @param null[] $data
     * @return
     */
    private function imgarray($data = array('file' => null, 'dir' => null), $old = '')
    {
        $return =   '';
        if ($data['file']['tmp_name'] != '') {
            $UploadM = $this->MODEL('upload');
            $upArr = array(
                'file' => $data['file'],
                'dir' => $data['dir']
            );
            $res = $UploadM->newUpload($upArr);
            if (isset($res['picurl'])){
                $return = $res['picurl'];
            }
        }
        if (empty($return) && !empty($old)){
            $return = $old;
        }
        return $return;
    }

    /**
     * 删除未包含在最新提交的数据
     * @param $type
     * @param $post
     */
    function delnav($type, $post)
    {
        $wxAppM = $this->MODEL('wxapp');
        $wxAppM->delTplApp($type, $post);
    }

    /**
     * 保存到tplapp里
     * @param $data
     */
    function savetplapp($data)
    {
        $wxAppM = $this->MODEL('wxapp');
        $wxAppM->saveTplApp($data);
        $wxAppM->tplappCache();
    }

    /*
     * app模板拖动排序
     */
    function appsort_action()
    {
        $wxAppM = $this->MODEL('wxapp');
        $wxAppM->sortTplApp($_POST['sort']);
        $wxAppM->tplappCache();
        $this->render_json(0, '保存成功！');
    }

    /**
     * 工具 移动端 小程序配置 微信小程序保存
     */
    function save_action()
    {
        include(DATA_PATH.'api/wxapp/tplapp.cache.php');
        $_POST = $this->post_trim($_POST);
        //APP风格

        // 自定义主题
        if ($_POST['type'] == 'theme') {
            $data['maincolor'] = $_POST['maincolor'];
            $data['secondcolor'] = $_POST['secondcolor'];
        }
        //搜索
        if ($_POST['type'] == 'search') {
            $data = array(
                'searchshow' => $_POST['searchshow'] == 'true' ? 1 : 2,
                'searchstyle' => $_POST['searchstyle'],
                'search' => $_POST['search']
            );
        }
        //幻灯广告
        if ($_POST['type'] == 'hd') {
            $data = array(
                'hdshow' => $_POST['hdshow'] == 'true' ? 1 : 2 ,
                'hdid' => $_POST['hdid']
            );
        }
        //会员快捷操作
        if ($_POST['type'] == 'member') {
            $data = array(
                'membershow' => $_POST['membershow'] == 'true' ? 1 : 2,
                'slideadstyle' => $_POST['slideadstyle'],
                'hdid' => $_POST['hdid']
            );
        }
        //通栏广告
        if ($_POST['type'] == 'ad') {
            $data = array(
                'adshow' => $_POST['adshow'] == 'true' ? 1 : 2,
                'adhdid' => $_POST['adhdid'],
                'adhdid2' => $_POST['adhdid2'],
                'adhdid3' => $_POST['adhdid3'],
            );
        }
        //导航
        if ($_POST['type'] == 'nav') {
            $data = array(
                'navshow' => $_POST['navshow'] == 'true' ? 1 : 2,
                'navstyle' => $_POST['navstyle']
            );
            $navidarr = array();
            $postnav = json_decode(stripslashes($_POST['nav']), 1);
            // 新上传图片文件处理
            foreach ($_FILES['navpic'] as $nk => $nv) {
                foreach ($nv as $nkk => $nvv) {
                    $navpic_file[$nkk][$nk] = $nvv;
                }
            }
            $newpickey = 0;// 新菜单图标key
            foreach ($postnav as $k => $v) {
                if (isset($v['id']) && $v['id']) {
                    $navidarr[] = $v['id'];
                }
                if ($v['new_pic']) {
                    $postnav[$k]['new_pic_file'] = $navpic_file[$newpickey];
                    $newpickey++;
                }
            }
            // 删除多余的菜单
            $this->delnav('nav', $navidarr);
            $wxAppM = $this->MODEL('wxapp');
            foreach ($postnav as $nav) {
                $pictures = $this->imgarray(array('dir' => 'appdiy', 'file' => $nav['new_pic_file'] ? $nav['new_pic_file'] : null), $nav['pic']);
                $arr = array(
                    'name' => $nav['name'],
                    'url' => $nav['url'],
                    'news_class' => $nav['news_class'],
                    'type' => 'nav',
                    'pic' => $pictures,
                    'id' => $nav['id'] ? $nav['id'] : '',
                    'display' => $nav['display_n'] == true ? 1 : 2,
                    'navsort' => $nav['navsort'],
                    'navtype' => $nav['navtype'],
                    'nav_appid' => $nav['nav_appid'],
                    'nav_ysid' => $nav['nav_ysid']
                );
                $wxAppM->saveAllTplApp(array('id' => $arr['id']), $arr);
            }
            $wxAppM->tplappCache();
        }
        //职位专题
        if ($_POST['type'] == 'jobspec') {
            $data = array(
                'jobspecshow' => $_POST['jobspecshow'] == 'true' ? 1 : 2,
                'jobspecnum' => intval($_POST['jobspecnum'])
            );
        }
        //公告
        if ($_POST['type'] == 'notice') {
            foreach ($_FILES['gigpic'] as $nk => $nv) {
                foreach ($nv as $nkk => $nvv) {
                    $noticestyleimg[$nkk][$nk] = $nvv; // 上传数据重新整理
                }
            }
            $imgArr = !empty($tplapp['noticestyleimg']) ? explode(',', $tplapp['noticestyleimg']) : array();
            $newggpickeys = $_POST['newggpickeys'] ? json_decode(stripslashes($_POST['newggpickeys']), 1) : array();
            foreach ($noticestyleimg as $imgkey => $imgval) {
                $imgArr[$newggpickeys[$imgkey]] = $this->imgarray(
                    array(
                        'dir' => 'appdiy',
                        'file' => $imgval
                    ),
                    $imgArr ? $imgArr[$imgkey] : ''
                );
            }
            $data = array(
                'notice' => $_POST['notice'] == 'true' ? 1 : 2,
                'noticestyle' => $_POST['noticestyle'],
                'noticestyleimg' => implode(',', $imgArr)
            );
        }
        // 登录
        if ($_POST['type'] == 'login') {
            $login_icon1 = $this->imgarray(array('dir' => 'appdiy', 'file' => $_FILES['login_icon1']), !empty($tplapp['login_icon1']) ? $tplapp['login_icon1'] : '');
            $login_icon2 = $this->imgarray(array('dir' => 'appdiy', 'file' => $_FILES['login_icon2']), !empty($tplapp['login_icon2']) ? $tplapp['login_icon2'] : '');
            $data = array(
                'login' => $_POST['login'] == 'true' ? 1 : 2,
                'login_title1' => $_POST['login_title1'],
                'login_subtitle1' => $_POST['login_subtitle1'],
                'login_icon1' => $login_icon1,
                'login_title2' => $_POST['login_title2'],
                'login_subtitle2' => $_POST['login_subtitle2'],
                'login_icon2' => $login_icon2,
            );
        }
        //名企
        if ($_POST['type'] == 'hotcom') {
            if ($_POST['hotcomnum'] == '' || $_POST['hotcomnum'] <= 0) {
                $this->render_json(1, '请填写名企显示数量！');
            }
            $data = array(
                'hotcom' => $_POST['hotcom'] == 'true' ? 1 : 2,
                'hotcomstyle' => $_POST['hotcomstyle'],
                'hotcomnum' => $_POST['hotcomnum'],
                'hotcommore' => $_POST['hotcommore'] == 'true' ? 1 : 2,
            );
        }
        //热门职位
        if ($_POST['type'] == 'hotjob') {
            if ($_POST['hotjobnum'] == '' || $_POST['hotjobnum'] <= 0) {
                $this->render_json(1, '请填写热门职位显示数量！');
            }
            $data = array(
                'hotjob' => $_POST['hotjob'] == 'true' ? 1 : 2,
                'hotjobmore' => $_POST['hotjobmore'] == 'true' ? 1 : 2,
                'hotjobnum' => $_POST['hotjobnum']
            );
        }
        //职位列表
        if ($_POST['type'] == 'job') {
            $jobsort = json_decode(stripslashes($_POST['jobsort']), 1);
            $data = array(
                'job' => $_POST['job'] == 'true' ? 1 : 2,
                'jobstyle' => $_POST['jobstyle'],
                'newjob' => $_POST['newjob'] == 'true' ? 1 : 2,
                'newjobnum' => $_POST['newjobnum'],
                'recjob' => $_POST['recjob'] == 'true' ? 1 : 2,
                'recjobnum' => $_POST['recjobnum'],
                'urgjob' => $_POST['urgjob'] == 'true' ? 1 : 2,
                'urgjobnum' => $_POST['urgjobnum'],
                'mapjob' => $_POST['mapjob'] == 'true' ? 1 : 2,
                'mapjobnum' => $_POST['mapjobnum'],
                'jobsort' => implode(',', $jobsort),
                'jobmore' => $_POST['jobmore'] ? 1 : 2,
            );
        }
        //站长资质
        if ($_POST['type'] == 'syszz'){
            $data = array(
                'syszzmore' => $_POST['syszzmore'] == 'true' ? 1 : 2,
            );
        }
        //一键登陆
        if ($_POST['type'] == 'sysyjlogin'){
            $data = array(
                'sysyjlogin' => $_POST['sysyjlogin'] == 'true' ? 1 : 2,
            );
        }
        //招聘会
        if ($_POST['type'] == 'zph') {
            if ($_POST['zphnum'] == '' || $_POST['zphnum'] <= 0) {
                $this->render_json(1, '请填写招聘会显示数量！');
            }
            $data = array(
                'zphshow' => $_POST['zphshow'] == 'true' ? 1 : 2,
                'zphmore' => $_POST['zphmore'] == 'true' ? 1 : 2,
                'zphnum' => $_POST['zphnum']
            );
        }
        //资讯
        if ($_POST['type'] == 'article') {
            if ($_POST['articlenum'] == '' || $_POST['articlenum'] <= 0) {
                $this->render_json(1, '请填写资讯显示数量！');
            }
            $data = array(
                'articleshow' => $_POST['articleshow'] == 'true' ? 1 : 2,
                'articletype' => $_POST['articletype'] ? $_POST['articletype'] : 1,
                'articlemore' => $_POST['articlemore'] == 'true' ? 1 : 2,
                'articlenum' => $_POST['articlenum'],
            );
        }
        //浮动客服
        if ($_POST['type'] == 'connect') {
            $kficon = $this->imgarray(array('dir' => 'appdiy', 'file' => $_FILES['kficon']), !empty($tplapp['kficon']) ? $tplapp['kficon'] : '');
            $data = array(
                'cshow' => $_POST['cshow'] == 'true' ? 1 : 2,
                'kfurl' => $_POST['kfurl'],
                'kfcomid' => $_POST['kfcomid'],
                'kficon' => $kficon
            );
        }
        //底部导航
        if ($_POST['type'] == 'foot') {
            $data = array(
                // 'footshow' => $_POST['footshow'] == 'true' ? 1 : 2,
                'footstyle' => $_POST['footstyle']
            );
            $footidarr = array();
            $postfootnav = json_decode(stripslashes($_POST['footnav']), 1);
            // 新上传图片文件处理
            foreach ($_FILES['footpic'] as $fk => $fv) {
                foreach ($fv as $fkk => $fvv) {
                    $footpic_file[$fkk][$fk] = $fvv;
                }
            }
            foreach($_FILES['footchoosed'] as $fck=>$fcv){
                foreach($fcv as $fckk => $fcvv){
                    $footpic_file_cur[$fckk][$fck] = $fcvv;
                }
            }
            $footpicarr = array();
            foreach ($footnav as $v) {
                $footpicarr[$v['id']] = $v['pic'];
            }
            $newpickey = 0;// 未选中图标key
            $newcurrpickey = 0;// 选中图标key
            foreach ($postfootnav as $k => $v) {
                if (isset($v['id']) && $v['id']) {
                    $footidarr[] = $v['id'];
                }
                if ($v['new_pic']) {
                    $postfootnav[$k]['new_pic_file'] = $footpic_file[$newpickey];
                    $newpickey++;
                }
                if ($v['new_currpic']) {
                    $postfootnav[$k]['new_currpic_file'] = $footpic_file_cur[$newcurrpickey];
                    $newcurrpickey++;
                }
                $arr = explode(',', $footpicarr[$v['id']]);
                if (is_array($arr)){
                    $postfootnav[$k]['old_pic'] = $arr[0];
                    $postfootnav[$k]['old_pic_curr'] = $arr[1];
                }else{
                    $postfootnav[$k]['old_pic'] = $v['pic'];
                }
            }
            $this->delnav('foot', $footidarr);
            $wxAppM = $this->MODEL('wxapp');
            foreach ($postfootnav as $v) {
                $pic = $this->imgarray(array('dir' => 'appdiy', 'file' => $v['new_pic_file'] ? $v['new_pic_file'] : ''), $v['old_pic']);
                $curpic = $this->imgarray(array('dir' => 'appdiy', 'file' => $v['new_currpic_file'] ? $v['new_currpic_file'] : ''), $v['old_pic_curr']);
                $arr = array(
                    'name' => $v['name'],
                    'type' => 'foot',
                    'pic' => $pic . ',' . $curpic,
                    'id' => $v['id'] ? $v['id'] : '',
                    'url' => $v['url']
                );
                $wxAppM->saveAllTplApp(array('id' => $arr['id']), $arr);
            }
            $wxAppM->tplappCache('foot');
        }
        if (isset($data)) {
            $this->savetplapp($data);
        }
        $this->admin_json(0, "移动端模板(type:{$_POST['type']})保存成功");
    }

    /**
     * 生成缓存
     */
    function makeCache_action()
    {
        $wxAppM = $this->MODEL('wxapp');
        $wxAppM->makeCache();
        $wxAppM->makeBaiduCache();
        $wxAppM->makeWxSeoCache();
        $wxAppM->tplappCache();
    }
    /**
     * 工具 移动端 模板DIY wap模板
     */
    function wap_action(){
        $tplM =	$this->MODEL('tpl');
        $nav = $tplM->getTplmobliepicList(array('type'=>'nav','orderby'=>'sort,asc'));
        foreach($nav as $k=>$v){
            $nav[$k]['pic'] = checkpic($v['pic']);
        }
        $data['nav'] = $nav;
        $hd = $tplM->getTplmobliepicList(array('type'=>'hd'));
        foreach($hd as $k=>$v){
            $hd[$k]['old_pic'] = $v['pic'];
            $hd[$k]['pic'] = checkpic($v['pic']);
        }
        $data['hd'] = $hd;
        $indexnav =	$tplM->getTplmobliepicList(array('type' => 'indexnav', 'orderby' => 'id,asc'));
        foreach($indexnav as $k => $v){
            $indexnav[$k]['pic'] = checkpic($v['pic']);
        }
        foreach($indexnav as $k => $v){
            if($k == 0){
                $indexnav1 = $v;
            }
            if($k==1){
                $indexnav2 = $v;
            }
            if($k==2){
                $indexnav3 = $v;
            }
        }
        $data['indexnav1'] = isset($indexnav1) ? $indexnav1 : array();
        $data['indexnav2'] = isset($indexnav2) ? $indexnav2 : array();
        $data['indexnav3'] = isset($indexnav3) ? $indexnav3 : array();
        $adlist	= $tplM->getTplmobliepicList(array('type' => 'ad'));
        foreach($adlist as $k => $v){
            $adlist[$k]['pic'] = checkpic($v['pic']);
        }
        $data['adlist'] = $adlist;
        include PLUS_PATH."/tplmoblie.cache.php";
        !isset($tplmoblie['search']) && $tplmoblie['search'] = '请输入职位关键字，如：会计...';
        !isset($tplmoblie['searchurl']) && $tplmoblie['searchurl'] = '';
        !isset($tplmoblie['searchshow']) && $tplmoblie['searchshow'] = '1';
        !isset($tplmoblie['notice']) && $tplmoblie['notice'] = '1';
        !isset($tplmoblie['indexnav']) && $tplmoblie['indexnav'] = '1';
        !isset($tplmoblie['hdshow']) && $tplmoblie['hdshow'] = '1';
        !isset($tplmoblie['site']) && $tplmoblie['site'] = '1';
        !isset($tplmoblie['reglogin']) && $tplmoblie['reglogin'] = '1';
        !isset($tplmoblie['reglogindesc']) && $tplmoblie['reglogindesc'] = '您尚未登录，马上登录轻松管理信息';
        !isset($tplmoblie['login']) && $tplmoblie['login'] = '登陆';
        !isset($tplmoblie['reg']) && $tplmoblie['reg'] = '注册';
        !isset($tplmoblie['logincolor']) && $tplmoblie['logincolor'] = '#1c99ef';
        !isset($tplmoblie['regcolor']) && $tplmoblie['regcolor'] = '#ff6a6a';
        !isset($tplmoblie['ad']) && $tplmoblie['ad'] = '1';
        !isset($tplmoblie['rewardjob']) && $tplmoblie['rewardjob'] = '1';
        !isset($tplmoblie['rewardjobcss']) && $tplmoblie['rewardjobcss'] = '1';
        !isset($tplmoblie['rewardjobmore']) && $tplmoblie['rewardjobmore'] = '1';
        !isset($tplmoblie['rewardjobnum']) && $tplmoblie['rewardjobnum'] = '5';
        !isset($tplmoblie['rewardjobcom']) && $tplmoblie['rewardjobcom'] = '1';
        !isset($tplmoblie['rewardjobsalary']) && $tplmoblie['rewardjobsalary'] = '1';
        !isset($tplmoblie['rewardjobreward']) && $tplmoblie['rewardjobreward'] = '1';
        !isset($tplmoblie['rewardjobdate']) && $tplmoblie['rewardjobdate'] = '1';
        $tplmoblie['rewardjobcom'] = $tplmoblie['rewardjobcom'] == 1;
        $tplmoblie['rewardjobsalary'] = $tplmoblie['rewardjobsalary'] == 1;
        $tplmoblie['rewardjobreward'] = $tplmoblie['rewardjobreward'] == 1;
        $tplmoblie['rewardjobdate'] = $tplmoblie['rewardjobdate'] == 1;
        !isset($tplmoblie['hotjob']) && $tplmoblie['hotjob'] = '1';
        !isset($tplmoblie['hotjobmore']) && $tplmoblie['hotjobmore'] = '1';
        !isset($tplmoblie['hotjobnum']) && $tplmoblie['hotjobnum'] = '8';
        // 最新岗位
        !isset($tplmoblie['newjob']) && $tplmoblie['newjob'] = '1';
        !isset($tplmoblie['newjobmore']) && $tplmoblie['newjobmore'] = '1';
        !isset($tplmoblie['newjobnum']) && $tplmoblie['newjobnum'] = '5';
        !isset($tplmoblie['newjobcom']) && $tplmoblie['newjobcom'] = '1';
        !isset($tplmoblie['newjobsalary']) && $tplmoblie['newjobsalary'] = '1';
        !isset($tplmoblie['newjobcity']) && $tplmoblie['newjobcity'] = '1';
        !isset($tplmoblie['newjobdate']) && $tplmoblie['newjobdate'] = '1';
        !isset($tplmoblie['newjobwelfare']) && $tplmoblie['newjobwelfare'] = '1';
        $tplmoblie['newjobcom'] = $tplmoblie['newjobcom'] == 1;
        $tplmoblie['newjobsalary'] = $tplmoblie['newjobsalary'] == 1;
        $tplmoblie['newjobcity'] = $tplmoblie['newjobcity'] == 1;
        $tplmoblie['newjobdate'] = $tplmoblie['newjobdate'] == 1;
        $tplmoblie['newjobwelfare'] = $tplmoblie['newjobwelfare'] == 1;
        // 名企展示
        !isset($tplmoblie['hotcom']) && $tplmoblie['hotcom'] = '1';
        !isset($tplmoblie['hotcommore']) && $tplmoblie['hotcommore'] = '1';
        !isset($tplmoblie['hotcomnum']) && $tplmoblie['hotcomnum'] = '5';
        !isset($tplmoblie['hotcomlogo']) && $tplmoblie['hotcomlogo'] = '1';
        !isset($tplmoblie['hotcomhy']) && $tplmoblie['hotcomhy'] = '1';
        !isset($tplmoblie['hotcomcity']) && $tplmoblie['hotcomcity'] = '1';
        $tplmoblie['hotcomlogo'] = $tplmoblie['hotcomlogo'] == 1;
        $tplmoblie['hotcomhy'] = $tplmoblie['hotcomhy'] == 1;
        $tplmoblie['hotcomcity'] = $tplmoblie['hotcomcity'] == 1;
        // 推荐职位
        !isset($tplmoblie['recjob']) && $tplmoblie['recjob'] = '1';
        !isset($tplmoblie['recjobmore']) && $tplmoblie['recjobmore'] = '1';
        !isset($tplmoblie['recjobnum']) && $tplmoblie['recjobnum'] = '5';
        !isset($tplmoblie['recjobcom']) && $tplmoblie['recjobcom'] = '1';
        !isset($tplmoblie['recjobsalary']) && $tplmoblie['recjobsalary'] = '1';
        !isset($tplmoblie['recjobcity']) && $tplmoblie['recjobcity'] = '1';
        !isset($tplmoblie['recjobdate']) && $tplmoblie['recjobdate'] = '1';
        !isset($tplmoblie['recjobwelfare']) && $tplmoblie['recjobwelfare'] = '1';
        $tplmoblie['recjobcom'] = $tplmoblie['recjobcom'] == 1;
        $tplmoblie['recjobsalary'] = $tplmoblie['recjobsalary'] == 1;
        $tplmoblie['recjobcity'] = $tplmoblie['recjobcity'] == 1;
        $tplmoblie['recjobdate'] = $tplmoblie['recjobdate'] == 1;
        $tplmoblie['recjobwelfare'] = $tplmoblie['recjobwelfare'] == 1;
        // 紧急职位
        !isset($tplmoblie['urgentjob']) && $tplmoblie['urgentjob'] = '1';
        !isset($tplmoblie['urgentjobmore']) && $tplmoblie['urgentjobmore'] = '1';
        !isset($tplmoblie['urgentjobnum']) && $tplmoblie['urgentjobnum'] = '5';
        !isset($tplmoblie['urgentjobcom']) && $tplmoblie['urgentjobcom'] = '1';
        !isset($tplmoblie['urgentjobsalary']) && $tplmoblie['urgentjobsalary'] = '1';
        !isset($tplmoblie['urgentjobcity']) && $tplmoblie['urgentjobcity'] = '1';
        !isset($tplmoblie['urgentjobdate']) && $tplmoblie['urgentjobdate'] = '1';
        !isset($tplmoblie['urgentjobwelfare']) && $tplmoblie['urgentjobwelfare'] = '1';
        $tplmoblie['urgentjobcom'] = $tplmoblie['urgentjobcom'] == 1;
        $tplmoblie['urgentjobsalary'] = $tplmoblie['urgentjobsalary'] == 1;
        $tplmoblie['urgentjobcity'] = $tplmoblie['urgentjobcity'] == 1;
        $tplmoblie['urgentjobdate'] = $tplmoblie['urgentjobdate'] == 1;
        $tplmoblie['urgentjobwelfare'] = $tplmoblie['urgentjobwelfare'] == 1;
        // 最新简历
        !isset($tplmoblie['resume']) && $tplmoblie['resume'] = '1';
        !isset($tplmoblie['resumepic']) && $tplmoblie['resumepic'] = '1';
        !isset($tplmoblie['resumemore']) && $tplmoblie['resumemore'] = '1';
        !isset($tplmoblie['resumenum']) && $tplmoblie['resumenum'] = '5';
        !isset($tplmoblie['resumeexp']) && $tplmoblie['resumeexp'] = '1';
        !isset($tplmoblie['resumecity']) && $tplmoblie['resumecity'] = '1';
        !isset($tplmoblie['resumeedu']) && $tplmoblie['resumeedu'] = '1';
        !isset($tplmoblie['resumeexpect']) && $tplmoblie['resumeexpect'] = '1';
        $tplmoblie['resumeexp'] = $tplmoblie['resumeexp'] == 1;
        $tplmoblie['resumecity'] = $tplmoblie['resumecity'] == 1;
        $tplmoblie['resumeedu'] = $tplmoblie['resumeedu'] == 1;
        $tplmoblie['resumeexpect'] = $tplmoblie['resumeexpect'] == 1;
        // 职场资讯
        !isset($tplmoblie['article']) && $tplmoblie['article'] = '1';
        !isset($tplmoblie['articleclass']) && $tplmoblie['articleclass'] = '1';
        !isset($tplmoblie['articlecss']) && $tplmoblie['articlecss'] = '1';
        !isset($tplmoblie['articlemore']) && $tplmoblie['articlemore'] = '1';
        !isset($tplmoblie['articlenum']) && $tplmoblie['articlenum'] = '5';
        // 招聘会
        !isset($tplmoblie['zph']) && $tplmoblie['zph'] = '1';
        !isset($tplmoblie['zphmore']) && $tplmoblie['zphmore'] = '1';
        !isset($tplmoblie['zphnum']) && $tplmoblie['zphnum'] = '5';
        // 职位类别
        //一级分类
        !isset($tplmoblie['jobclassone']) && $tplmoblie['jobclassone'] = '1';
        !isset($tplmoblie['jobclassonenum']) && $tplmoblie['jobclassonenum'] = '';
        !isset($tplmoblie['jobclassonenumall']) && $tplmoblie['jobclassonenumall'] = '1';
        // 二级分类
        !isset($tplmoblie['jobclasstwo']) && $tplmoblie['jobclasstwo'] = '1';
        !isset($tplmoblie['jobclasstwonum']) && $tplmoblie['jobclasstwonum'] = '';
        !isset($tplmoblie['jobclasstwonumall']) && $tplmoblie['jobclasstwonumall'] = '1';
        $data['tplmoblie'] = $tplmoblie;
        if( $tplmoblie['sort']){
            $sort = explode(',', $tplmoblie['sort']);
        }else{
            $sort = "hearder,hd,search,nav,indexnav,notice,reglogin,ad,rewardjob,hotjob,newjob,hotcom,recjob,urgentjob,resume,article,zph,jobclass";
            $sort = explode(',', $sort);
        }
        $data['sort'] = $sort;
        $navall	= $this->MODEL('navigation')->getNavList(array('nid' => '20','display' => 1));
        foreach ($navall as $k=>$val){
            if($val['url'] != 'index.php' && $val['url'] != '/'){
                $navigation[$k] = $val;
            }
        }
        $data['navigation'] = isset($navigation) ? $navigation : array();
        $searchurl=array(
            array('id'=>'1','c'=>'job','name'=>'找工作'),
            array('id'=>'2','c'=>'resume','name'=>'找人才'),
            array('id'=>'3','c'=>'company','name'=>'找企业'),
            array('id'=>'4','c'=>'part','name'=>'找兼职'),
            array('id'=>'5','c'=>'tiny','name'=>'普工专区'),
            array('id'=>'6','c'=>'once','name'=>'店铺招聘'),
            array('id'=>'9','c'=>'train','name'=>'培训课程'),
            array('id'=>'10','c'=>'train','a'=>'agency','name'=>'培训机构'),
            array('id'=>'11','c'=>'train','a'=>'teacher','name'=>'培训讲师'),
            array('id'=>'8','c'=>'ltjob','a'=>'service','name'=>'委托求职'),
            array('id'=>'12','c'=>'ask','a'=>'list','name'=>'互动问答')
        );
        $data['searchurl'] = $searchurl;
        $data['sy_wap_logo'] = $this->config['sy_ossurl'] . '/' . $this->config['sy_wap_logo'];
        $data['preview_index_url'] = $this->config['sy_weburl'] . '/index.php?m=ajax&c=wappubqrcode&type=1';
        $data['accept'] = $this->config['pic_type'];
        $this->render_json(0, 'ok', $data);
    }

    //wap模板 保存到tplmoblie里
    function savetplmoblie($data,$type){
        $this->MODEL('tpl')->setTplmoblie($data);
        $this->tplmoblie_cache();
    }
    //生成缓存tplmoblie.cache.php
    function tplmoblie_cache(){
        $tplmoblie = $this->MODEL('tpl')->getTplmoblieList('');
        if(is_array($tplmoblie)){
            foreach($tplmoblie as $v){
                $tplmobliearr[$v['name']] = $v['config'];
            }
            made_web(PLUS_PATH.'tplmoblie.cache.php', ArrayToString($tplmobliearr),'tplmoblie');
        }
    }
    //wap模板 删除未包含在最新提交的数据
    function wapdelpic($type, $post){
        $tplM =	$this->MODEL('tpl');
        $tpltype = $tplM->getTplmobliepicList(array('type' => $type));
        foreach($tpltype as $k => $v){
            if(!in_array($v['id'], $post)){
                $tplM->delTplmobliepic($v['id']);
            }
        }
    }
    //wap模板 图片数组处理
    private function wapimgarray($data = array('file' => null, 'dir' => null, 'type' => null, 'base' => null, 'preview' => null)){
        if($data['file']['tmp_name'] != ''){
            $UploadM = $this->MODEL('upload');
            $upArr = array(
                'file' => $data['file'],
                'dir' => $data['dir'],
            );
            $return = $UploadM->newUpload($upArr);
        }
        return $return['picurl'];
    }
    //wap模板 保存图片
    function wapsaveall($post){
        $tplM =	$this->MODEL('tpl');
        $value['name'] = $post['name'];
        $value['url'] =	$post['url'];
        $value['sort'] = $post['sort'];
        $value['type'] = $post['type'];
        if($post['desc']){
            $value['desc'] = $post['desc'];
        }
        if($post['temp_name'] != ''){
            if($post['pic']){
                $value['pic'] =	$post['pic'];
            }
        }
        //if($post['name'] =='' && $post['url']==''){
        //	$value=1;
        //}
        if($post['id']){
            if($value==1){
                $tplM->delTplmobliepic($post['id']);
            }else{
                $tplM->upTplmobliepic($value,array('id'=>$post['id']));
            }
        }else{
            if($value!=1){
                $tplM->addTplmobliepic($value);
            }
        }
    }
    /*
     * wap模板 开启、关闭
     */
    function wapdiy_action(){
        $this->MODEL('tpl')->setTplmoblie(array('wapdiy' => $_POST['wapdiy'] == 'true' ? 1 : 2));
        $this->tplmoblie_cache();
        if($_POST['wapdiy'] == true){
            //关闭时更新缓存
            $this->del_dir("../data/templates_c",1);
            $this->del_dir("../data/cache",1);
            /*生成四位JS，css识别码*/
            $cachecode = rand(1000,9999);
            $this->MODEL('config')->setConfig(array('cachecode' => $cachecode));
            $this->web_config();
        }
        $this->render_json(0, $_POST['wapdiy'] == 'true' ? '开启成功！' : '关闭成功！');
    }
    /*
     * wap端模板保存
     */
    function wapsave_action(){
        $_POST = $this->post_trim($_POST);
        //头部
        if($_POST['type'] == 'hearder'){
            $data=array(
                'color' => $_POST['color'],
                'site' => $_POST['site'],
                'logo' => $_POST['logo'],
                'heardercss' => $_POST['heardercss']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //幻灯片
        if($_POST['type'] == 'hd'){
            // 新上传图片文件处理
            foreach ($_FILES['hdpic'] as $fk => $fv) {
                foreach ($fv as $fkk => $fvv) {
                    $hdpic_file[$fkk][$fk] = $fvv;
                }
            }
            $hdidarr = array();
            $posthd = json_decode(stripslashes($_POST['hd']), 1);
            $newpickey = 0;// 幻灯片图标key
            foreach ($posthd as $k => $v) {
                if (isset($v['id']) && $v['id']) {
                    $hdidarr[] = $v['id'];
                }
                if ($v['new_pic']) {
                    $posthd[$k]['new_pic_file'] = $hdpic_file[$newpickey];
                    $newpickey++;
                }
            }
            $this->wapdelpic('hd', $hdidarr);
            //幻灯片
            foreach ($posthd as $k => $v) {
                $arr = array(
                    'name' => $v['name'],
                    'url' => $v['url'],
                    'type' => 'hd',
                    'id' =>	$v['id'],
                    'temp_name' => ''
                );
                if ($v['new_pic_file']) {
                    $arr['pic'] = $this->wapimgarray(array('dir'=>'wapdiy','file'=>$v['new_pic_file']));
                    $arr['temp_name'] = $v['new_pic_file']['tmp_name'];
                }
                $this->wapsaveall($arr);
            }
            $data=array(
                'hdshow' =>	$_POST['hdshow']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //搜索
        if($_POST['type'] == 'search'){
            $data = array(
                'searchshow' => $_POST['searchshow'],
                'search' => $_POST['search'],
                'searchurl' => $_POST['searchurl']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //导航
        if($_POST['type']=='nav'){
            if($_FILES['navpic']){
                foreach($_FILES['navpic'] as $nk => $nv){
                    foreach($nv as $nkk=>$nvv){
                        $navpic_file[$nkk][$nk] = $nvv;
                    }
                }
            }
            $navidarr = array();
            $postnav = json_decode(stripslashes($_POST['nav']), 1);
            $newpickey = 0;// 导航图标key
            foreach ($postnav as $k => $v) {
                if (isset($v['id']) && $v['id']) {
                    $navidarr[] = $v['id'];
                }
                if ($v['new_pic']) {
                    $postnav[$k]['new_pic_file'] = $navpic_file[$newpickey];
                    $newpickey++;
                }
            }
            $this->wapdelpic('nav',$navidarr);
            foreach ($postnav as $v) {
                $arr = array(
                    'name' => $v['name'],
                    'url' => $v['url'],
                    'sort' => $v['sort'],
                    'type' => 'nav',
                    'id' => $v['id'],
                    'temp_name' => ''
                );
                if ($v['new_pic_file']) {
                    $arr['pic'] = $this->wapimgarray(array('dir'=>'wapdiy','file'=>$v['new_pic_file']));
                    $arr['temp_name'] = $v['new_pic_file']['tmp_name'];
                }
                $this->wapsaveall($arr);
            }
        }
        //公告
        if($_POST['type'] == 'notice'){
            $data = array(
                'notice' => $_POST['notice']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //图片导航
        if($_POST['type'] == 'indexnav'){
            for($k = 1;$k <= 3; $k++){
                if ($k == 1) {
                    $curindexnav = json_decode(stripslashes($_POST['indexnav1']), 1);
                    $file = $_FILES['indexnavpic1'];
                } else if ($k == 2) {
                    $curindexnav = json_decode(stripslashes($_POST['indexnav2']), 1);
                    $file = $_FILES['indexnavpic2'];
                } else if ($k == 3) {
                    $curindexnav = json_decode(stripslashes($_POST['indexnav3']), 1);
                    $file = $_FILES['indexnavpic3'];
                }
                $arr = array(
                    'name' => $curindexnav['name'],
                    'url' => $curindexnav['url'],
                    'desc' => $curindexnav['desc'],
                    'type' => 'indexnav',
                    'id' =>	$curindexnav['id'],
                    'temp_name' => ''
                );
                if ($_FILES['indexnavpic' . $k]) {
                    $arr['pic'] = $this->wapimgarray(array('dir' => 'wapdiy','file' => $file));
                    $arr['temp_name'] = $file['tmp_name'];
                }
                $this->wapsaveall($arr);
            }
            $data = array(
                'indexnav' => $_POST['indexnav']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //登录注册
        if($_POST['type'] == 'reglogin'){
            $data = array(
                'reglogin' => $_POST['reglogin'],
                'reglogindesc' => $_POST['reglogindesc'],
                'login' => $_POST['login'],
                'reg' => $_POST['reg'],
                'logincolor' =>	$_POST['logincolor'],
                'regcolor' => $_POST['regcolor']
            );
            $this->savetplmoblie($data, $_POST['type']);
        }
        //广告
        if($_POST['type'] == 'ad'){
            if($_FILES['adlistpic']){
                foreach($_FILES['adlistpic'] as $nk => $nv){
                    foreach($nv as $nkk => $nvv){
                        $adlistpic_file[$nkk][$nk] = $nvv;
                    }
                }
            }
            $adidarr = array();
            $postadlist = json_decode(stripslashes($_POST['adlist']), 1);
            $newpickey = 0;// 导航图标key
            foreach ($postadlist as $k => $v) {
                if (isset($v['id']) && $v['id']) {
                    $adidarr[] = $v['id'];
                }
                if ($v['new_pic']) {
                    $postadlist[$k]['new_pic_file'] = $adlistpic_file[$newpickey];
                    $newpickey++;
                }
            }
            $this->wapdelpic('ad', $adidarr);
            //广告
            foreach ($postadlist as $v) {
                $arr = array(
                    'name' => $v['name'],
                    'url' => $v['url'],
                    'type' => 'ad',
                    'id' => $v['id'],
                    'temp_name' => ''
                );
                if ($v['new_pic_file']) {
                    $arr['pic'] = $this->wapimgarray(array('dir'=>'wapdiy','file'=>$v['new_pic_file']));
                    $arr['temp_name'] = $v['new_pic_file']['tmp_name'];
                }
                $this->wapsaveall($arr);
            }
            $data = array(
                'ad' =>	$_POST['ad']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //赏金职位
        if($_POST['type'] == 'rewardjob'){
            $data = array(
                'rewardjob' => $_POST['rewardjob'],
                'rewardjobcss' => $_POST['rewardjobcss'],
                'rewardjobcom' => $_POST['rewardjobcom'] == 'true' ? 1 : 2,
                'rewardjobsalary' => $_POST['rewardjobsalary'] == 'true' ? 1 : 2,
                'rewardjobreward' => $_POST['rewardjobreward'] == 'true' ? 1 : 2,
                'rewardjobdate' => $_POST['rewardjobdate'] == 'true' ? 1 : 2,
                'rewardjobmore' => $_POST['rewardjobmore'],
                'rewardjobnum' => $_POST['rewardjobnum']
            );
            $this->savetplmoblie($data, $_POST['type']);
        }
        //热门职位
        if($_POST['type'] == 'hotjob'){
            $data = array(
                'hotjob' =>	$_POST['hotjob'],
                'hotjobmore' =>	$_POST['hotjobmore'],
                'hotjobnum'	=> $_POST['hotjobnum']
            );
            $this->savetplmoblie($data, $_POST['type']);
        }
        //最新职位
        if($_POST['type'] == 'newjob'){
            $data = array(
                'newjob' =>	$_POST['newjob'],
                'newjobcom' => $_POST['newjobcom'] == 'true' ? '1' : '2',
                'newjobsalary' => $_POST['newjobsalary'] == 'true' ? '1' : '2',
                'newjobcity' =>	$_POST['newjobcity'] == 'true' ? '1' : '2',
                'newjobdate' =>	$_POST['newjobdate'] == 'true' ? '1' : '2',
                'newjobwelfare'	=> $_POST['newjobwelfare'] == 'true' ? '1' : '2',
                'newjobmore' => $_POST['newjobmore'],
                'newjobnum' => $_POST['newjobnum']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //名企展示
        if($_POST['type'] == 'hotcom'){
            $data = array(
                'hotcom' =>	$_POST['hotcom'],
                'hotcomlogo' =>	$_POST['hotcomlogo'] == 'true' ? '1' : '2',
                'hotcomhy' => $_POST['hotcomhy'] == 'true' ? '1' : '2',
                'hotcomcity' =>	$_POST['hotcomcity'] == 'true' ? '1' : '2',
                'hotcommore' =>	$_POST['hotcommore'],
                'hotcomnum' => $_POST['hotcomnum']
            );
            $this->savetplmoblie($data, $_POST['type']);
        }
        //推荐职位
        if($_POST['type'] == 'recjob'){
            $data = array(
                'recjob' =>	$_POST['recjob'],
                'recjobcom' => $_POST['recjobcom'] == 'true' ? '1' : '2',
                'recjobsalary' => $_POST['recjobsalary'] == 'true' ? '1' : '2',
                'recjobcity' =>	$_POST['recjobcity'] == 'true' ? '1' : '2',
                'recjobdate' =>	$_POST['recjobdate'] == 'true' ? '1' : '2',
                'recjobwelfare'	=> $_POST['recjobwelfare'] == 'true' ? '1' : '2',
                'recjobmore' =>	$_POST['recjobmore'],
                'recjobnum' => $_POST['recjobnum']
            );
            $this->savetplmoblie($data, $_POST['type']);
        }
        //紧急职位
        if($_POST['type'] == 'urgentjob'){
            $data = array(
                'urgentjob' => $_POST['urgentjob'],
                'urgentjobcom' => $_POST['urgentjobcom'] == 'true' ? '1' : '2',
                'urgentjobsalary' => $_POST['urgentjobsalary'] == 'true' ? '1' : '2',
                'urgentjobcity' => $_POST['urgentjobcity'] == 'true' ? '1' : '2',
                'urgentjobdate' => $_POST['urgentjobdate'] == 'true' ? '1' : '2',
                'urgentjobwelfare' => $_POST['urgentjobwelfare'] == 'true' ? '1' : '2',
                'urgentjobmore' => $_POST['urgentjobmore'],
                'urgentjobnum' => $_POST['urgentjobnum']
            );
            $this->savetplmoblie($data, $_POST['type']);
        }
        //最新职位
        if($_POST['type'] == 'resume'){
            $data=array(
                'resume' =>	$_POST['resume'],
                'resumepic' => $_POST['resumepic'],
                'resumeexp' => $_POST['resumeexp'] == 'true' ? '1' : '2',
                'resumecity' =>	$_POST['resumecity'] == 'true' ? '1' : '2',
                'resumeedu' => $_POST['resumeedu'] == 'true' ? '1' : '2',
                'resumeexpect' => $_POST['resumeexpect'] == 'true' ? '1' : '2',
                'resumemore' =>	$_POST['resumemore'],
                'resumenum' => $_POST['resumenum']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //职场资讯
        if($_POST['type'] == 'article'){
            $data = array(
                'article' => $_POST['article'],
                'articleclass' => $_POST['articleclass'],
                'articlecss' =>	$_POST['articlecss'],
                'articlemore' => $_POST['articlemore'],
                'articlenum' =>	$_POST['articlenum']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //招聘会
        if($_POST['type'] == 'zph'){
            $data = array(
                'zph' => $_POST['zph'],
                'zphmore' => $_POST['zphmore'],
                'zphnum' =>	$_POST['zphnum']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        //职位类别
        if($_POST['type'] == 'jobclass'){
            $data = array(
                'jobclassone' => $_POST['jobclassone'],
                'jobclassonenum' =>	$_POST['jobclassonenum'],
                'jobclassonenumall'	=> $_POST['jobclassonenumall'],
                'jobclasstwo' => $_POST['jobclasstwo'],
                'jobclasstwonum' =>	$_POST['jobclasstwonum'],
                'jobclasstwonumall'	=> $_POST['jobclasstwonumall']
            );
            $this->savetplmoblie($data,$_POST['type']);
        }
        $this->admin_json(0, "WAP模板(type:{$_POST['type']})保存成功！");
    }
    //上移下移
    function wapsort_action(){
        $sort=implode(',', $_POST['sort']);
        $this->MODEL('tpl')->setTplmoblie(array('sort' => $sort));
        $this->tplmoblie_cache();
        $this->render_json(0, '保存成功！');
    }
    // wap重置样式
    function clean_action(){
        $tplM =	$this->MODEL('tpl');
        $tplM -> delTplmoblie('');
        @unlink(PLUS_PATH.'tplmoblie.cache.php');
        $this->admin_json(0, '重置样式成功！');
    }
}
?>