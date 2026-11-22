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
class admin_app_set_controller extends adminCommon
{

    function index_action()
    {

        include(DATA_PATH.'api/wxapp/tplapp.cache.php');
        include(DATA_PATH.'api/wxapp/tplappmodel.cache.php');
        if (isset($tplapp)) {

            $wxAppM  =  $this->MODEL('wxapp');
            $row     =   $wxAppM->getTplApp(array('name'=>'isnew'));
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
            $this->yunset('tplapp', $tplapp);

            if ($tplapp['hdid'] || $tplapp['adhdid']) {
                $time = time();
                include(PLUS_PATH . 'pimg_cache.php');

                if (isset($ad_label)) {
                    if ($ad_label[$tplapp['hdid']]) {
                        foreach ($ad_label[$tplapp['hdid']] as $hk => $hv) {

                            if ($hv['type'] == 'pic' && $hv['start'] < $time && $hv['end'] > $time) {

                                $hd[$hk]['pic'] = $hv['pic'];
                            }
                        }
                        $this->yunset('hd', $hd);
                    }
                    if ($ad_label[$tplapp['adhdid']]) {
                        foreach ($ad_label[$tplapp['adhdid']] as $hk => $hv) {

                            if ($hv['type'] == 'pic' && $hv['start'] < $time && $hv['end'] > $time) {

                                $adhd = $hv['pic'];
                            }
                        }

                        $this->yunset('adhd', $adhd);
                    }
                }
            }

        }

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
                $navsort[] = $v['navsort'];
            }

            array_multisort($navsort, SORT_ASC, SORT_NUMERIC, $nav);

            $this->yunset('nav', $nav);
            $navarr = array_chunk($nav, 5);

            $this->yunset('navarr', $navarr);

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
            $this->yunset('footnav', $footnav);

            if ($tplapp['sort']) {

                $sort = explode(',', $tplapp['sort']);
            } else {
                $sort = 'search,member,nav,notice,login,hotcom,hotjob,ad,job,zph,article,syszz,connect,foot';
                $sort = explode(',', $sort);
            }
            $this->yunset('sort', $sort);
        }

        $this->yunset('navigation', $tplappmodel);

        // 广告类别
        $adClassList = $this->MODEL('ad')->getAdClassList(array(
            'place' => 2,
            'orderby' => 'orders,desc'
        ), array('field' => '`id`,`class_name`,`place`'));
        $this->yunset('adClassList', $adClassList['list']);

        $this->yuntpl(array('admin/admin_app_set'));
    }

    function setSave_action()
    {

        if ($_POST['submit']) {

            include(DATA_PATH.'api/wxapp/tplapp.cache.php');
            $_POST  =   $this->post_trim($_POST);

            //APP风格
            // if ($_POST['type'] == 'header') {
            //     $data['color']  =   $_POST['color'];
            // }
            // 自定义主题
            if ($_POST['type'] == 'theme') {
                $data['maincolor']  =   $_POST['maincolor'];
                $data['secondcolor']  =   $_POST['secondcolor'];
            }
            //搜索
            if ($_POST['type'] == 'search') {
                $data   =   array(
                    'searchshow'=>  $_POST['searchshow'] ? 1 : 2,
                    'searchstyle'    =>  $_POST['searchstyle'],
                    'search'    =>  $_POST['search']
                );
            }
            //幻灯广告
            if ($_POST['type'] == 'hd') {
                $data   =   array(
                    'hdshow'    =>  $_POST['hdshow'],
                    'hdid'      =>  $_POST['hdid']
                );
            }
            //会员快捷操作
            if ($_POST['type'] == 'member') {
                $data   =   array(
                    'membershow'=>  $_POST['membershow'] ? 1 : 2,
                    'slideadstyle' =>  $_POST['slideadstyle'],
                    'hdid'      =>  $_POST['hdid']
                );
            }
            //会员快捷操作
            if ($_POST['type'] == 'ad') {
                $data   =   array(
                    'adshow'    =>  $_POST['adshow'] ? 1 : 2,
                    'adhdid'    =>  $_POST['adhdid']
                );
            }
            //导航
            if ($_POST['type'] == 'nav') {
                $data = array(
                    'navshow' => $_POST['navshow'] ? 1 : 2,
                    'navstyle' => $_POST['navstyle']
                );

                $this->delpic('nav', $_POST['navid']);
                foreach ($_FILES['navpic'] as $nk => $nv) {
                    foreach ($nv as $nkk => $nvv) {
                        $navpic_file[$nkk][$nk]  =  $nvv;
                    }
                }
                $oldpic = array();
                foreach ($_POST['navid'] as $k=>$v){
                    foreach ($nav as $val){
                        if ($v == $val['id']){
                            $oldpic[] = $val['pic'];
                        }
                    }
                }
                $wxAppM =   $this->MODEL('wxapp');

                for ($j = 0; $j < count($_POST['navname']); $j++) {

                    $pictures   =   $this->imgarray(array('dir' => 'appdiy', 'file' => $navpic_file[$j]), $oldpic[$j]);

                    $navid = $_POST['navid'][$j] ? $_POST['navid'][$j] : $_POST['navcid'][$j];
                    $arr        =   array(
                        'name'      =>  $_POST['navname'][$j],
                        'url'       =>  $_POST['navurl'][$j],
                        'type'      =>  'nav',
                        'pic'       =>  $pictures,
                        'id'        =>  $_POST['navid'][$j],
                        'display'   =>  $_POST['navdisplay'][$j],
                        'navsort'   =>  $_POST['navsort'][$j],
                        'navtype'   =>  $_POST['navtype'.$navid],
                        'nav_appid'   =>  $_POST['navappid'][$j],
                    );
                    $wxAppM->saveAllTplApp(array('id' => $arr['id']), $arr);
                }
                $wxAppM->tplappCache();
            }
            //大导航
            // if ($_POST['type'] == 'navbig') {
            //     $this->delpic('navbig', $_POST['navbigid']);
            //
            //     foreach ($_FILES['navbigpic'] as $nbk => $nbv) {
            //         foreach ($nbv as $nbkk => $nbvv) {
            //
            //             $navbigpic_file[$nbkk][$nbk]    =   $nbvv;
            //         }
            //     }
            //     $oldpic = array();
            //     foreach ($_POST['navbigid'] as $k=>$v){
            //         foreach ($navbig as $val){
            //             if ($v == $val['id']){
            //                 $oldpic[] = $val['pic'];
            //             }
            //         }
            //     }
            //     $wxAppM =   $this->MODEL('wxapp');
            //
            //     for ($j = 0; $j < count($_POST['navbigname']); $j++) {
            //
            //         $pictures   =   $this->imgarray(array('dir' => 'appdiy', 'file' => $navbigpic_file[$j]), $oldpic[$j]);
            //
            //         $arr        = array(
            //
            //             'name'      =>  $_POST['navbigname'][$j],
            //             'desc'      =>  $_POST['navbigdesc'][$j],
            //             'url'       =>  $_POST['navbigurl'][$j],
            //             'type'      =>  'navbig',
            //             'pic'       =>  $pictures,
            //             'id'        =>  $_POST['navbigid'][$j],
            //             'display'   =>  $_POST['navbigdisplay'][$j],
            //             'navsort'   =>  $_POST['navbigsort'][$j]
            //         );
            //         $wxAppM->saveAllTplApp(array('id' => $arr['id']), $arr);
            //     }
            //     $wxAppM->tplappCache();
            // }
            //公告
            if ($_POST['type'] == 'notice') {
                foreach ($_FILES['noticestyleimg'] as $nk => $nv) {
                    foreach ($nv as $nkk => $nvv) {
                        $noticestyleimg[$nkk][$nk]  =  $nvv; // 上传数据重新整理
                    }
                }

                $noticestyleimgold = !empty($tplapp['noticestyleimg']) ? explode(',', $tplapp['noticestyleimg']) : array();

                $imgArr = array();
                foreach ($noticestyleimg as $imgkey => $imgval) {
                    $imgArr[] = $this->imgarray(
                        array(
                            'dir' => 'appdiy',
                            'file' => $imgval
                        ),
                        $noticestyleimgold ? $noticestyleimgold[$imgkey] : ''
                    );
                }

                $data   =   array(
                    'notice'    =>  $_POST['notice'] ? 1 : 2,
                    'noticestyle'    =>  $_POST['noticestyle'],
                    'noticestyleimg'    =>  implode(',', $imgArr)
                );
            }
            // 登录
            if ($_POST['type'] == 'login') {
                $login_icon1   =   $this->imgarray(array('dir' => 'appdiy', 'file' => $_FILES['login_icon1']), !empty($tplapp['login_icon1']) ? $tplapp['login_icon1'] : '');
                $login_icon2   =   $this->imgarray(array('dir' => 'appdiy', 'file' => $_FILES['login_icon2']), !empty($tplapp['login_icon2']) ? $tplapp['login_icon2'] : '');

                $data   =   array(
                    'login' =>  $_POST['login'] ? 1 : 2,
                    'login_title1' =>  $_POST['login_title1'],
                    'login_subtitle1' =>  $_POST['login_subtitle1'],
                    'login_icon1' =>  $login_icon1,
                    'login_title2' =>  $_POST['login_title2'],
                    'login_subtitle2' =>  $_POST['login_subtitle2'],
                    'login_icon2' =>  $login_icon2,
                );
            }
            //名企
            if ($_POST['type'] == 'hotcom') {
                if ($_POST['hotcomnum'] == '' || $_POST['hotcomnum'] <= 0) {
                    $this->ACT_layer_msg('请填写名企显示数量！', 8);
                }
                $data   =   array(
                    'hotcom'    =>  $_POST['hotcom'] ? 1 : 2,
                    'hotcomstyle' =>  $_POST['hotcomstyle'],
                    'hotcomnum' =>  $_POST['hotcomnum'],
                    'hotcommore'=>  $_POST['hotcommore'] ? 1 : 2,
                );
            }
            //热门职位
            if ($_POST['type'] == 'hotjob') {
                if ($_POST['hotjobnum'] == '' || $_POST['hotjobnum'] <= 0) {
                    $this->ACT_layer_msg('请填写热门职位显示数量！', 8);
                }
                $data   =   array(
                    'hotjob'    =>  $_POST['hotjob'] ? 1 : 2,
                    'hotjobmore'=>  $_POST['hotjobmore'] ? 1 : 2,
                    'hotjobnum' =>  $_POST['hotjobnum']
                );
            }
            //职位列表
            if ($_POST['type'] == 'job') {
                // if ($_POST['jobnum'] == '' || $_POST['jobnum'] <= 0) {
                //     $this->ACT_layer_msg('请填写职位显示数量！', 8);
                // }
                // if ($_POST['jobcom'] != 1) {
                //     $_POST['jobcom']    =   2;
                // }
                // if ($_POST['jobsalary'] != 1) {
                //     $_POST['jobsalary'] =   2;
                // }
                // if ($_POST['jobcity'] != 1) {
                //     $_POST['jobcity']   =   2;
                // }
                // if ($_POST['jobdate'] != 1) {
                //     $_POST['jobdate']   =   2;
                // }
                // if ($_POST['jobwelfare'] != 1) {
                //     $_POST['jobwelfare']=   2;
                // }

                $data   =   array(
                    'job'       =>  $_POST['job'] ? 1 : 2,
                    'jobstyle'  =>  $_POST['jobstyle'],
                    'newjob'  =>  $_POST['newjob'] ? 1 : 2,
                    'newjobnum'  =>  $_POST['newjobnum'],
                    'recjob'  =>  $_POST['recjob'] ? 1 : 2,
                    'recjobnum'  =>  $_POST['recjobnum'],
                    'urgjob'  =>  $_POST['urgjob'] ? 1 : 2,
                    'urgjobnum'  =>  $_POST['urgjobnum'],
                    'mapjob'  =>  $_POST['mapjob'] ? 1 : 2,
                    'mapjobnum'  =>  $_POST['mapjobnum'],
                    'jobsort' => implode(',', $_POST['jobsort']),
                    // 'jobcom'    =>  $_POST['jobcom'],
                    // 'jobsalary' =>  $_POST['jobsalary'],
                    // 'jobcity'   =>  $_POST['jobcity'],
                    // 'jobdate'   =>  $_POST['jobdate'], // 没用处
                    // 'jobwelfare'=>  $_POST['jobwelfare'], // 没用处
                    'jobmore'   =>  $_POST['jobmore'] ? 1 : 2,
                    // 'jobnum'    =>  $_POST['jobnum'],
                );
            }
            //站长资质
            if ($_POST['type'] == 'syszz'){
                $data   =   array(
                    'syszzmore'    =>  $_POST['syszzmore'] ? 1 : 2,
                );
            }
            //招聘会
            if ($_POST['type'] == 'zph') {
                if ($_POST['zphnum'] == '' || $_POST['zphnum'] <= 0) {
                    $this->ACT_layer_msg('请填写招聘会显示数量！', 8);
                }
                // if ($_POST['zphtime'] != 1) {
                //     $_POST['zphtime']   =   2;
                // }
                // if ($_POST['zphplace'] != 1) {
                //     $_POST['zphplace']  =   2;
                // }

                $data   =   array(

                    'zphshow'   =>  $_POST['zphshow'] ? 1 : 2,
                    // 'zphtime'   =>  $_POST['zphtime'],
                    // 'zphplace'  =>  $_POST['zphplace'],
                    'zphmore'   =>  $_POST['zphmore'] ? 1 : 2,
                    'zphnum'    =>  $_POST['zphnum']
                );
            }
            //资讯
            if ($_POST['type'] == 'article') {
                if ($_POST['articlenum'] == '' || $_POST['articlenum'] <= 0) {
                    $this->ACT_layer_msg('请填写资讯显示数量！', 8);
                }
                $data   =   array(
                    'articleshow'   =>  $_POST['articleshow'] ? 1 : 2,
                    'articletype'   =>  $_POST['articletype'] ? $_POST['articletype'] : 1,
                    'articlemore'   =>  $_POST['articlemore'] ? 1 : 2,
                    'articlenum'    =>  $_POST['articlenum'],
                );
            }
            //职位类别
            // if ($_POST['type'] == 'jobclass') {
            //     $data   =   array(
            //         'jobclassone'       =>  $_POST['jobclassone'],
            //         'jobclassonenum'    =>  $_POST['jobclassonenum'],
            //         'jobclassonenumall' =>  $_POST['jobclassonenumall'],
            //
            //         'jobclasstwo'       =>  $_POST['jobclasstwo'],
            //         'jobclasstwonum'    =>  $_POST['jobclasstwonum'],
            //         'jobclasstwonumall' =>  $_POST['jobclasstwonumall'],
            //
            //         'jobclassmore'      =>  $_POST['jobclassmore']
            //     );
            // }
            //底部关于我们
            if ($_POST['type'] == 'connect') {
                $kficon   =   $this->imgarray(array('dir' => 'appdiy', 'file' => $_FILES['kficon']), !empty($tplapp['kficon']) ? $tplapp['kficon'] : '');

                $data   =   array(
                    'cshow'   =>  $_POST['cshow'] ? 1 : 2,
                    'kfurl'   =>  $_POST['kfurl'],
                    'kfcomid' =>  $_POST['kfcomid'],
                    'kficon' =>  $kficon
                );
            }
            //底部导航
            if ($_POST['type'] == 'foot') {
                $data   =   array(
                    // 'footshow' => $_POST['footshow'] ? 1 : 2,
                    'footstyle' => $_POST['footstyle']
                );

                $this->delpic('foot', $_POST['footid']);
                foreach ($_FILES['footpic'] as $fk => $fv) {
                    foreach ($fv as $fkk => $fvv) {

                        $footpic_file[$fkk][$fk] = $fvv;
                    }
                }
                foreach($_FILES['footchoosed'] as $fck=>$fcv){

                    foreach($fcv as $fckk=>$fcvv){

                        $footpic_file_cur[$fckk][$fck] = $fcvv;
                    }
                }
                $oldpic = $oldcurpic = array();
                foreach ($_POST['footid'] as $k=>$v){
                    foreach ($footnav as $val){
                        if ($v == $val['id']){
                            $arr = explode(',', $val['pic']);
                            if (is_array($arr)){
                                $oldpic[] = $arr[0];
                                $oldcurpic[] = $arr[1];
                            }else{
                                $oldpic[] = $val['pic'];
                            }
                        }
                    }
                }
                $wxAppM =   $this->MODEL('wxapp');

                for ($j = 0; $j < count($_POST['footname']); $j++) {

                    $pic     =  $this->imgarray(array('dir'=>'appdiy', 'file'=>$footpic_file[$j]), $oldpic[$j]);

                    $loadcur =  isset($oldcurpic[$j]) ? $oldcurpic[$j] : '';
                    $curpic  =  $this->imgarray(array('dir'=>'appdiy', 'file'=>$footpic_file_cur[$j]), $loadcur);

                    $arr    =   array(
                        'name'      =>  $_POST['footname'][$j],
                        'type'      =>  'foot',
                        'pic'		=>	$pic . ',' . $curpic,
                        'id'        =>  $_POST['footid'][$j],
                        'url'       =>  $_POST['footurl'][$j]
                    );
                    $wxAppM->saveAllTplApp(array('id' => $arr['id']), $arr);
                }
                $wxAppM->tplappCache('foot');
            }

            if (isset($data)) {

                $this->savetplapp($data);
            }
            $this->ACT_layer_msg('保存成功！', 9, $_SERVER['HTTP_REFERER']);
        }
    }

    function sort_action()
    {
        $wxAppM =   $this->MODEL('wxapp');

        $return =   $wxAppM->sortTplApp($_POST['sort']);

        $wxAppM->tplappCache();

        echo $return;
        die;
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

            $UploadM    =   $this->MODEL('upload');
            $upArr      =   array(
                'file'  =>  $data['file'],
                'dir'   =>  $data['dir']
            );

            $res  =  $UploadM->newUpload($upArr);
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
    function delpic($type, $post)
    {

        $wxAppM =   $this->MODEL('wxapp');
        $wxAppM->delTplApp($type, $post);
    }

    /**
     * 保存到tplapp里
     * @param $data
     */
    function savetplapp($data)
    {
        $wxAppM =   $this->MODEL('wxapp');

        $wxAppM->saveTplApp($data);

        $wxAppM->tplappCache();
    }

    function makeCache_action()
    {

        $wxAppM =   $this->MODEL('wxapp');
        $wxAppM->makeCache();
        $wxAppM->makeBaiduCache();
        $wxAppM->makeWxSeoCache();
        $wxAppM->tplappCache();
    }

    /**
     * 小程序SEO配置
     */
    function seo_action(){

        include(CONFIG_PATH."db.data.php");

        if (isset($arr_data)){
            // 保留需要的seo类型
            $seomodel = array();
            foreach ($arr_data['seomodel'] as $k=>$v){
                if (in_array($k, array('index','job','resume','company','part','article','zph','once','tiny','map','special','spview','xjhlive','zph','skill'))){
                    $seomodel[$k] = $v;
                }
            }
            $arr_data['seomodel'] = $seomodel;
            $this->yunset('arr_data',$arr_data);
        }

        $action =	$_GET[action]?$_GET[action]:"index";
        $wxAppM =   $this->MODEL('wxapp');
        $rows	=   $wxAppM -> getWxSeoList(array('seomodel'=>$action));
        $this->yunset('rows', $rows);

        $this->yuntpl(array('admin/admin_xcx_wx_seo'));
    }

    /**
     * 小程序SEO添加
     */
    function addSeo_action(){

        include(CONFIG_PATH."db.data.php");

        if (isset($arr_data)){

            // 去掉用不到的数据
            unset($arr_data['seoconfig']['public']['webkeyword']);
            unset($arr_data['seoconfig']['public']['webdesc']);

            // 保留需要的seo类型
            $seomodel = array();
            foreach ($arr_data['seomodel'] as $k=>$v){
                if (in_array($k, array('index','job','resume','company','part','article','zph','once','tiny','map','special','spview','xjhlive','skill'))){
                    $seomodel[$k] = $v;
                }
            }
            $arr_data['seomodel'] = $seomodel;
            $this->yunset('arr_data',$arr_data);
        }

        $wxAppM  =  $this->MODEL('wxapp');
        $info    =  $wxAppM->getWxSeo(array('id'=>$_GET['id']));

        $this->yunset('info', $info);

        $this->yuntpl(array('admin/admin_xcx_wx_seoadd'));
    }

    /**
     * 小程序SEO保存
     */
    function saveSeo_action(){

        $post   =   array(
            'name'      =>  $_POST['name'],
            'seomodel'  =>  $_POST['seomodel'],
            'ident'     =>  $_POST['ident'],
            'title'     =>  $_POST['title'],
            'lastupdate'=>  time(),
            'share_pic' =>  $_POST['share_pic'],
            'keywords'  =>  $_POST['keywords'],
            'description'=> $_POST['description']
        );


        if($_FILES['file']['tmp_name']){
            $upArr    =  array(
                'file'  =>  $_FILES['file'],
                'dir'   =>  'xcxshare'
            );

            $uploadM  =  $this->MODEL('upload');

            $pic      =  $uploadM->newUpload($upArr);

            if (!empty($pic['msg'])){

                $this->ACT_layer_msg($pic['msg'],8);

            }elseif (!empty($pic['picurl'])){

                $post['share_pic']   =   $pic['picurl'];
            }
        }


        $wxAppM     =   $this->MODEL('wxapp');
        if ($_POST['id']){
            $return =   $wxAppM->upWxSeo(array('id'=>$_POST['id']), $post);
        }else{
            $return =   $wxAppM->addWxSeo($post);
        }
        $this->ACT_layer_msg($return['msg'], $return['errcode'], 'index.php?m=admin_app_set&c=seo', 2, 1);
    }

    /**
     * 小程序SEO删除
     */
    function delSeo_action(){

        if ($_GET['id']){

            $this->check_token();

            $wxAppM  =  $this->MODEL('wxapp');
            $res     =  $wxAppM->delWxSeo(array('id'=>$_GET['id']));
            $this->layer_msg($res['msg'], $res['errcode']);
        }else{

            $this->layer_msg('请选择要删除的SEO', 8);
        }
    }
}

?>