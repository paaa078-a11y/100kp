<?php
/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

/**
 * 行为日志操作类
 */
class weblog_model extends model
{

    public $modelType = array(

        'admin'             =>  '后台',
        'index'             =>  'PC端',
        'wap'               =>  'WAP端',
        'app'               =>  'APP',
        'weixin'            =>  '微信小程序',
        'baidu'             =>  '百度小程序',
        'toutiao'           =>  '抖音小程序',

        'member'            =>  'PC会员中心',

        'wap_member'        =>  'WAP会员中心',
        'wapapp_member'     =>  'WAP会员中心',

        'app_member'        =>  'APP会员中心',
        'weixin_member'     =>  '微信小程序会员中心',
        'baidu_member'      =>  '百度小程序会员中心',
        'toutiao_member'    =>  '头条会员中心'
    );

    public $userType    =   array('1' => '个人' , '2' => '企业', '3' => '猎头', '4' => '培训机构');

    /**
     * 添加行为日志
     * @param string $model
     * @param array $data
     */
    public function addWebLog($model, $data = array())
    {

        if($this->config['sy_web_log_switch'] == 1){


            $webData = array(
                'uid'       =>  $data['uid'],
                'usertype'  =>  $data['usertype'],
                'type'      =>  $_SERVER['REQUEST_METHOD']
            );

            if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {

                $webData['is_ajax'] = 1;
            } else if (stripos($_SERVER['QUERY_STRING'], 'type=sq') != false) {

                $webData['is_ajax'] = 1;
            } else {

                $webData['is_ajax'] = 0;
            }

            if ($this->config['sy_seo_rewrite'] == 1) {

                if (preg_match("/yunurl/i", $_SERVER['QUERY_STRING'])) {

                    $mcArr  =   @explode('=', $_SERVER['QUERY_STRING']);
                    $mcArr  =   @explode('-', $mcArr[1]);
                    $isRe   =   1;
                } else {

                    $mcArr  =   @explode('&', $_SERVER['QUERY_STRING']);
                }
            } else {
                $mcArr      =   @explode('&', $_SERVER['QUERY_STRING']);
            }

            $webData['model']   =   $model;

            if ($model == 'wxapp'){                             //   接口

                if ($data['provider'] == 'app') {

                    $webData['model'] = 'app';                  //  APP
                } else if ($data['provider'] == 'weixin') {

                    $webData['model'] = 'weixin';               //  微信小程序
                } else if ($data['provider'] == 'baidu') {

                    $webData['model'] = 'baidu';                //  百度小程序
                } else if ($data['provider'] == 'toutiao') {

                    $webData['model'] = 'toutiao';              //  抖音小程序
                } else {

                    $webData['model'] = 'wap';                  //  h5（WAP）
                }

                foreach ($mcArr as $k => $v) {

                    $mcA    =   @explode('=', $v);

                    if ($mcA[0] == 'h') {

                        if ($data['provider'] == 'app') {

                            $webData['model']   =   'app_member';       //  APP 会员中心
                        } else if ($data['provider'] == 'weixin') {

                            $webData['model']   =   'weixin_member';    //  微信小程序会员中心
                        } else if ($data['provider'] == 'baidu') {

                            $webData['model']   =   'baidu_member';     //  百度小程序会员中心
                        } else if ($data['provider'] == 'toutiao') {

                            $webData['model']   =   'toutiao_member';   //  抖音小程序会员中心
                        }else{

                            $webData['model']   =   'wapapp_member';    //  WAP会员中心
                        }
                    }

                    if ($mcA[0] == 'm') {
                        $webData['m']       =   $mcA[1];
                    }
                    if ($mcA[0] == 'c') {
                        $webData['c']       =   $mcA[1];
                    }
                }

            } else if ($model == 'wap') {

                //   wap模块
                foreach ($mcArr as $k => $v) {
                    if ($this->config['sy_seo_rewrite'] == 1) {
                        if ($isRe == 1) {
                            $mcA = @explode('_', $v);
                        } else {
                            $mcA = @explode('=', $v);
                        }
                    } else {
                        $mcA = @explode('=', $v);
                    }
                    if ($mcA[0] == 'c') {
                        $webData['m'] = $mcA[1];
                    }
                    if ($mcA[0] == 'a') {
                        $webData['c'] = $mcA[1];
                    }
                }
            } else if ($model == 'wap_member') {
                //  wap会员中心
                if ($data['usertype'] == 1) {

                    $webData['m']   =   'user';

                } else if ($data['usertype'] == 2) {

                    $webData['m']   =   'com';
                } else if ($data['usertype'] == 3) {

                    $webData['m']   =   'lt';
                } else if ($data['usertype'] == 4) {

                    $webData['m']   =   'px';
                }
                foreach ($mcArr as $k => $v) {
                    if ($this->config['sy_seo_rewrite'] == 1) {
                        if ($isRe == 1) {

                            $mcA    =   @explode('_', $v);
                        } else {

                            $mcA    =   @explode('=', $v);
                        }
                    } else {

                        $mcA        =   @explode('=', $v);
                    }
                    if ($mcA[0] == 'c') {
                        $webData['c']   =   $mcA[1];
                    }
                }
            } else if ($model == 'member') {
                //   PC端会员中心
                if ($data['usertype'] == 1) {

                    $webData['m']   =   'user';
                } else if ($data['usertype'] == 2) {

                    $webData['m']   =   'com';
                } else if ($data['usertype'] == 3) {

                    $webData['m']   =   'lt';
                } else if ($data['usertype'] == 4) {

                    $webData['m']   =   'px';
                }

                foreach ($mcArr as $k => $v) {
                    if ($this->config['sy_seo_rewrite'] == 1) {
                        if ($isRe == 1) {

                            $mcA    =   @explode('_', $v);
                        } else {

                            $mcA    =   @explode('=', $v);
                        }
                    } else {

                        $mcA        =   @explode('=', $v);
                    }

                    if ($mcA[0] == 'c') {

                        $webData['c']   =   $mcA[1];
                    }
                    if ($mcA[0] == 'act'){

                        $webData['a']   =   $mcA[1];
                    }
                }
            } else if ($model == 'index') {

                //  前台
                //$modelArr   =   array('job', 'resume', 'part', 'company', 'article', 'announcement', 'hr', 'zph', 'ask', 'train', 'lietou', 'school', 'evaluate', 'once', 'tiny', 'redeem', 'map', 'special', 'reward', 'zphnet', 'redeem', 'spview', 'datav', 'gongzhao');

                $modelArr   =   array('advice', 'ajax', 'announcement', 'article', 'ask', 'chat', 'claim', 'company', 'datav', 'evaluate', 'forgetpw', 'friendhelp',
                    'gongzhao', 'hr', 'index', 'invitereg', 'job', 'lietou', 'link', 'login', 'map', 'once', 'part', 'promoter', 'qqconnect', 'redeem', 'register', 'resume',
                    'reward', 'school', 'shop', 'sinaconnect', 'skill', 'special', 'spview', 'subscribe', 'tiny', 'train', 'upload', 'upush', 'video', 'weixin', 'work', 'wxconnect', 'xjhlive', 'zph', 'zphnet');

                foreach ($modelArr as $k => $v) {
                    if (preg_match("/" . $v . "/i", $_SERVER['SCRIPT_NAME'])) {

                        $webData['m']   =   $v;
                    }
                }

                foreach ($mcArr as $k => $v) {

                    if ($this->config['sy_seo_rewrite'] == 1) {
                        if ($isRe == 1) {

                            $mcA    =   @explode('_', $v);
                        } else {

                            $mcA    =   @explode('=', $v);
                        }
                    } else {

                        $mcA        =   @explode('=', $v);
                    }
                    if ($mcA[0] == 'm') {
                        $webData['m']   =   $mcA[1];
                    }
                    if ($mcA[0] == 'c') {
                        $webData['c']   =   $mcA[1];
                    }
                    if ($mcA[0] == 'a'){
                        $webData['a']   =   $mcA[1];
                    }
                }
            }

            $webData['ip']          =   fun_ip_get();
            $webData['time']        =   time();

            if ($model!='admin' && isset($data['auid'])){
                $webData['m']       =   'nolog';
                unset($webData);
            }

            if ($model == 'index') {

                $webData = $this->indexWebLog($webData);
            } else if ($model == 'wap') {

                $webData = $this->wapWebLog($webData);
            } else if ($model == 'wxapp') {

                $webData = $this->wxAppWebLog($webData);
            } else if ($model == 'member') {

                $webData = $this->memberWebLog($webData);
            } else if ($model == 'wap_member'){

                $webData = $this->wapMemberWebLog($webData);
            } else if ($model == 'wapapp_member'){

                $webData = $this->wxAppMemberWebLog($webData);
            }
            if ($model && isset($webData) && !empty($webData)) {
                if (isset($webData) && $webData['usertype'] == 1) {
                    //  浏览职位模块
                    if (($webData['c'] == 'comapply' || ($webData['m'] == 'job' && $webData['c'] == 'show')) && isset($this->config['web_user_model']) && !empty($this->config['web_user_model']) && stripos($this->config['web_user_model'], 'lookjob') !== false) {
                        $nid = $this->insert_into('web_log', $webData);
                    }
                    //  职位搜索模块
                    if (($webData['m'] == 'job' && (!isset($webData['c']) || $webData['c'] == 'search' || $webData['c'] == 'list')) && isset($this->config['web_user_model']) && !empty($this->config['web_user_model']) && stripos($this->config['web_user_model'], 'searchjob') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }

                    //  职位申请模块
                    if (($webData['c'] == 'sq_job' || $webData['c'] == 'apply' || ($webData['c'] == 'comapply' && $webData['is_ajax'] == 1)) && isset($this->config['web_user_model']) && !empty($this->config['web_user_model']) && stripos($this->config['web_user_model'], 'sqjob') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }

                    //  聊天模块
                    if ((($webData['m'] == 'user' && $webData['c'] == 'chat') || ($webData['m'] == 'chat' && $webData['c'] == 'chatLog')) && isset($this->config['web_user_model']) && !empty($this->config['web_user_model']) && stripos($this->config['web_user_model'], 'chat') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }
                }else if (isset($webData) && $webData['usertype'] == 2){

                    //浏览简历
                    if ((($webData['m'] == 'com' && $webData['c'] == 'hr' && $webData['a'] == 'resumeInfo') || ($webData['m'] == 'resume' && $webData['c'] == 'show' && !isset($webData['a']))) && isset($this->config['web_com_model']) && !empty($this->config['web_com_model']) && stripos($this->config['web_com_model'], 'lookresume') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }
                    //邀请面试
                    if (($webData['c'] == 'sava_ajaxresume' || $webData['c'] == 'invitesave') && isset($this->config['web_com_model']) && !empty($this->config['web_com_model']) && stripos($this->config['web_com_model'], 'inviteresume') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }

                    //聊天统计
                    if ((($webData['m'] == 'chat' && $webData['c'] == 'chatLog') || ($webData['m'] == 'com' && $webData['c'] == 'chat')) && isset($this->config['web_com_model']) && !empty($this->config['web_com_model']) && stripos($this->config['web_com_model'], 'chat') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }

                    //简历下载
                    if (($webData['c'] == 'forlink' || $webData['c'] == 'for_link' || $webData['m'] == 'down') && isset($this->config['web_com_model']) && !empty($this->config['web_com_model']) && stripos($this->config['web_com_model'], 'downresume') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }

                    //发布修改职位
                    if ((($webData['c'] == 'jobadd' && $webData['a'] == 'save') || ($webData['m'] == 'job' && $webData['c'] == 'saveJob')) && isset($this->config['web_com_model']) && !empty($this->config['web_com_model']) && stripos($this->config['web_com_model'], 'addjob') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }

                    //推广服务 置顶 推荐 紧急 预约刷新
                    if (($webData['c'] == 'job' && ($webData['a'] == 'jobPromote' || $webData['a'] == 'reserveUp')) && isset($this->config['web_com_model']) && !empty($this->config['web_com_model']) && stripos($this->config['web_com_model'], 'promote') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }

                    if (($webData['m'] == 'fk' && ($webData['c'] == 'getOrder' || $webData['c'] == 'dkzf' || $webData['c'] == 'coupongm')) && isset($this->config['web_com_model']) && !empty($this->config['web_com_model']) && stripos($this->config['web_com_model'], 'promote') !== false) {

                        $params = unserialize($webData['params']);
                        if (in_array($params['server'], ['jobtop', 'jobrec', 'joburgent', 'autojob'])) {

                            $nid = $this->insert_into('web_log', $webData);
                        }
                    }
                    //浏览套餐 增值包
                    if ((($webData['m'] == 'com' && ($webData['c'] == 'right' || $webData['c'] == 'server')) || ($webData['m'] == 'fk' && $webData['c'] == 'server')) && isset($this->config['web_com_model']) && !empty($this->config['web_com_model']) && stripos($this->config['web_com_model'], 'pay') !== false) {

                        $nid = $this->insert_into('web_log', $webData);
                    }
                }
                // 向副表中加入数据
                if ($nid) {
                    $subData    =   array('wid' => $nid, 'uri' => $_SERVER['REQUEST_URI'], 'time' => time());
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $subData['params']  =   serialize($_POST);
                    } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                        $subData['params']  =   serialize($_GET);
                    }
                    $this->insert_into('web_log_sub', $subData);
                }
            }
        }
    }

    /**
     * 添加后台管理员行为日志
     * @param array $data
     */
    public function addAdminWebLog($data = array())
    {
        $model = 'admin';
        $webData = array(
            'uid'       =>  $data['auid'],
        );
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            $webData['is_ajax'] = 1;
        } else if (stripos($_SERVER['QUERY_STRING'], 'type=sq') != false) {
            $webData['is_ajax'] = 1;
        } else {
            $webData['is_ajax'] = 0;
        }
        if ($this->config['sy_seo_rewrite'] == 1) {
            if (preg_match("/yunurl/i", $_SERVER['QUERY_STRING'])) {
                $mcArr  =   @explode('=', $_SERVER['QUERY_STRING']);
                $mcArr  =   @explode('-', $mcArr[1]);
                $isRe   =   1;
            } else {
                $mcArr  =   @explode('&', $_SERVER['QUERY_STRING']);
            }
        } else {
            $mcArr      =   @explode('&', $_SERVER['QUERY_STRING']);
        }
        foreach ($mcArr as $k => $v) {
            if ($this->config['sy_seo_rewrite'] == 1) {
                if ($isRe == 1) {
                    $mcA = @explode('_', $v);
                } else {
                    $mcA = @explode('=', $v);
                }
            } else {
                $mcA = @explode('=', $v);
            }
            if ($mcA[0] == 'm') {
                $webData['m'] = $mcA[1];
            }
            if ($mcA[0] == 'c') {
                $webData['c'] = $mcA[1];
            }
            if ($mcA[0] == 'a'){
                $webData['a'] = $mcA[1];
            }
        }
        $webData['ip'] = fun_ip_get();
        $webData['time'] = time();
        $webData = $this->adminWebLog($webData);
        if ($model && isset($webData) && !empty($webData)) {
            if ($webData['m'] || $webData['c'] || $webData['a']) {
                $webData['uri'] = $_SERVER['REQUEST_URI'];
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $webData['params'] = serialize($_POST);
                } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                    $webData['params'] = serialize($_GET);
                }
                $this->insert_into('admin_web_log', $webData);
            }
        }
    }

    private function indexWebLog($webData = array())
    {

        /* Part Model */
        $noAddArr = array('activate', 'ask', 'call', 'claim', 'cron', 'evaluate', 'forgetpw', 'geetest', 'hr', 'invitereg', 'lietou', 'link', 'promoter', 'qqconnect',
            'redeem', 'shop', 'sinaconnect', 'train', 'upgrade', 'upush', 'weixin', 'work', 'wxconnect', 'nolog');
        if (in_array($webData['m'], $noAddArr)) {
            unset($webData);
        }

        /* Part Ajax */
        $indexAjaxCArr = array('ajax', 'ajax_job', 'ajax_ltjob', 'mapconfig', 'mapconfigdiffdomains', 'show_leftjob', 'get_subject', 'getcity_subscribe', 'getjob_subscribe', 'getsalary_subscribe',
            'RedLoginHead', 'ReadIndexHeader', 'Site', 'SiteCity', 'SiteHy', 'footertj', 'DefaultLoginIndex', 'selsite', 'selcrm', 'xcxQrcode', 'pubqrcode', 'wappubqrcode', 'getcontent', 'wjump',
            'redeem_city', 'ajax_once_city', 'ajax_recommend_interval', 'get_city_option', 'get_subject_option', 'get_job_option', 'get_ltjob_option', 'getbusiness', 'get_school_option', 'msgNum',
            'wxpaycode', 'ajax_day_action_check', 'getPackPrice', 'getVipPrice', 'getJobInfo', 'closeSpview', 'sendSpviewSub', 'sendXjhliveYy', 'sendWxNotice', 'getInviteRegHbList', 'getLogoHb',
            'getWxpubJob', 'getComBySearch', 'checkUsername', 'ajaxComjob');
        $indexAjaxAArr = array('nowpackage', 'gethelpcode');
        if ($webData['m'] == 'ajax') {
            if (in_array($webData['c'], $indexAjaxCArr)) {
                unset($webData);
            }
            if (in_array($webData['a'], $indexAjaxAArr)) {
                unset($webData);
            }
        }

        /* Part Chat */
        $indexChatCArr = array('beginChat', 'goChat', 'pageData', 'getSingleChatOnlineData', 'getAllChatOnlineData', 'getAjaxJobs', 'getChatPage', 'getdown', 'isResume',
            'setMsgStatus', 'setVoiceStatus', 'inviteCheck', 'unSend', 'getToken', 'getXjhChatPage', 'checkdata', 'getmh', 'showUnread', 'getJob', 'resumeExpect');
        if ($webData['m'] == 'chat') {
            if (in_array($webData['c'], $indexChatCArr)) {
                unset($webData);
            }
        }

        /* Part Company */
        $indexCompanyCArr = array('logVideo');
        if ($webData['m'] == 'company') {
            if (in_array($webData['c'], $indexCompanyCArr)) {
                unset($webData);
            }
        }

        /* Part Index */
        $indexIndexCArr = array('GetHits', 'clickhits', 'nlresume');
        if ($webData['m'] == 'index') {
            if (in_array($webData['c'], $indexIndexCArr)) {
                unset($webData);
            }
        }

        /* Part Job */
        $indexJobCArr = array('closeWxtzTips');
        $indexJobAArr = array('GetHits', 'history');
        if ($webData['m'] == 'job') {
            if (in_array($webData['c'], $indexJobCArr)) {
                unset($webData);
            }
            if (in_array($webData['a'], $indexJobAArr)) {
                unset($webData);
            }
        }

        /* Part Once */
        $indexOnceCArr = array('wxurl');
        if ($webData['m'] == 'once') {
            if (in_array($webData['c'], $indexOnceCArr)) {
                unset($webData);
            }
        }

        /* Part Resume */
        $indexResumeCArr = array('showuid');
        $indexResumeAArr = array('GetHits', 'history');
        if ($webData['m'] == 'resume') {
            if (in_array($webData['c'], $indexResumeCArr)) {
                unset($webData);
            }
            if (in_array($webData['a'], $indexResumeAArr)) {
                unset($webData);
            }
        }

        /* Part Special */
        $indexSpecialCArr = array('getSpecialJob');
        if ($webData['m'] == 'special') {
            if (in_array($webData['c'], $indexSpecialCArr)) {
                unset($webData);
            }
        }

        /* Part Spview */
        $indexSpviewCArr = array('ajaxResume');
        if ($webData['m'] == 'spview') {
            if (in_array($webData['c'], $indexSpviewCArr)) {
                unset($webData);
            }
        }

        /* Part xjhlive */
        $indexLiveCArr = array('getAjaxHits', 'GetHits', 'getXjhShowQrcode', 'getXjhlive');
        if ($webData['m'] == 'xjhlive') {
            if (in_array($webData['c'], $indexLiveCArr)) {
                unset($webData);
            }
        }

        /* Part zphnet */
        $indexZphnetCArr = array('getAjaxHits', 'setLook', 'isJoib');
        if ($webData['m'] == 'zphnet') {
            if (in_array($webData['c'], $indexZphnetCArr)) {
                unset($webData);
            }
        }

        return $webData;
    }

    private function wapWebLog($webData = array())
    {

        /* Part Model */
        $noAddArr = array('ask', 'claim', 'crm', 'evaluate', 'geetest', 'jobpack', 'lietou', 'ltindex', 'ltresume', 'post', 'qqconnect', 'recruit', 'redeem', 'sinaconnect',
            'train', 'work', 'wxconnect', 'nolog', 'getVideo');
        if (in_array($webData['m'], $noAddArr)) {
            unset($webData);
        }

        /* Part Ajax */
        $wapAjaxCArr = array('wap_job', 'wap_city', 'regcode', 'regcodeks', 'getjob', 'getltjob', 'getlietouhy', 'getcity', 'schoolcity', 'getbusiness', 'msgNum', 'ajax_url', 'getredeem',
            'ajax_day_action_check', 'pubqrcode', 'gzhqrcode', 'addJobTelLog', 'cityData', 'ageData', 'expData', 'sexData', 'eduData', 'userHyChart',
            'userRegChart', 'comcityData', 'comgmData', 'comxzData', 'comLogChart', 'comJobChart', 'dataShowIndex');
        if ($webData['m'] == 'ajax') {
            if (in_array($webData['c'], $wapAjaxCArr)) {
                unset($webData);
            }
        }

        /* Part Article */
        $wapArticleCArr = array('GetHits');
        if ($webData['m'] == 'article') {
            if (in_array($webData['c'], $wapArticleCArr)) {
                unset($webData);
            }
        }

        /* Part Chat */
        $wapChatCArr = array('goChat', 'beginChat', 'getChatPage', 'unSend', 'getToken', 'getdown', 'isResume', 'setMsgStatus', 'setVoiceStatus', 'prepare', 'unSend', 'getXjhChatPage',
            'checkdata', 'spviewChat', 'getUnloginToken', 'getmh', 'getUseful', 'map', 'getJob', 'greetingSwitch', 'getZdcon');
        if ($webData['m'] == 'chat') {
            if (in_array($webData['c'], $wapChatCArr)) {
                unset($webData);
            }
        }

        /* Part Company*/
        $wapCompanyCArr = array('ajaxLoad', 'telQrcode', 'logVideo');
        if ($webData['m'] == 'company') {
            if (in_array($webData['c'], $wapCompanyCArr)) {
                unset($webData);
            }
        }

        /* Part Index */
        $wapIndexCArr = array('getmq', 'getVideo', 'getCityDomain');
        if ($webData['m'] == 'index') {
            if (in_array($webData['c'], $wapIndexCArr)) {
                unset($webData);
            }
        }

        /* Part Job */
        $wapJobCArr = array('getreport', 'GetHits', 'history', 'ajaxLoad');
        if ($webData['m'] == 'job') {
            if (in_array($webData['c'], $wapJobCArr)) {
                unset($webData);
            }
        }

        /* Part resume */
        $wapResumeCArr = array('history', 'ajaxLoad');
        if ($webData['m'] == 'resume') {
            if (in_array($webData['c'], $wapResumeCArr)) {
                unset($webData);
            }
        }

        /* Part site */
        $wapSiteCArr = array('cache');
        if ($webData['m'] == 'site') {
            if (in_array($webData['c'], $wapSiteCArr)) {
                unset($webData);
            }
        }

        /* Part special */
        $wapSpecialCArr = array('joblist', 'hotjobclass', 'getSpecialJob');
        if ($webData['m'] == 'special') {
            if (in_array($webData['c'], $wapSpecialCArr)) {
                unset($webData);
            }
        }

        /* Part spview */
        $wapSpviewCArr = array('ajaxResume', 'uinfo');
        if ($webData['m'] == 'spview') {
            if (in_array($webData['c'], $wapSpviewCArr)) {
                unset($webData);
            }
        }

        /* Part xjhlive */
        $wapXjhliveCArr = array('xjhyyQrcode', 'GetHits', 'getAjaxHits', 'getXjhlive', 'getcomlist', 'upzan');
        if ($webData['m'] == 'xjhlive') {
            if (in_array($webData['c'], $wapXjhliveCArr)) {
                unset($webData);
            }
        }

        /* Part zphnet */
        $wapZphnetCArr = array('getComList', 'setLook', 'isJoin', 'GetHits');
        if ($webData['m'] == 'zphnet') {
            if (in_array($webData['c'], $wapZphnetCArr)) {
                unset($webData);
            }
        }

        return $webData;
    }

    private function wxAppWebLog($webData = array())
    {

        if ($webData['model'] == 'app_member' || $webData['model'] == 'weixin_member' || $webData['model'] == 'baidu_member' || $webData['model'] == 'toutiao_member' || $webData['model'] == 'wapapp_member') {

            if ($webData['usertype'] == 2){

                /* Part Index */
                $memberIndexCArr = array('hbwx');
                if ($webData['m'] == 'index') {
                    if (in_array($webData['c'], $memberIndexCArr)) {
                        unset($webData);
                    }
                }

                /* Part Jon */
                $memberJobCArr = array('jobnum', 'getJobAddress', 'sendRatingNotice', 'ajaxjobclass', 'jobcolumn');
                if ($webData['m'] == 'job') {
                    if (in_array($webData['c'], $memberJobCArr)) {
                        unset($webData);
                    }
                }

                /* Part Part */
                $memberPartCArr = array('partnum', 'upapply');
                if ($webData['m'] == 'part') {
                    if (in_array($webData['c'], $memberPartCArr)) {
                        unset($webData);
                    }
                }

                /* Part resume */
                $memberResumeCArr = array('resumecolumn');
                if ($webData['m'] == 'resume') {
                    if (in_array($webData['c'], $memberResumeCArr)) {
                        unset($webData);
                    }
                }

                /* Part Set */
                $memberSetCArr = array('gettel', );
                if ($webData['m'] == 'set') {
                    if (in_array($webData['c'], $memberSetCArr)) {
                        unset($webData);
                    }
                }
            }else if ($webData['usertype'] == 1){

                /* Part Index */
                $memberIndexCArr = array('hbwx');
                if ($webData['m'] == 'index') {
                    if (in_array($webData['c'], $memberIndexCArr)) {
                        unset($webData);
                    }
                }

                /* Part Resume */
                $memberResumeCArr = array('isaddresume');
                if ($webData['m'] == 'index') {
                    if (in_array($webData['c'], $memberResumeCArr)) {
                        unset($webData);
                    }
                }
            }

        }

        /* Part Model */
        $noAddArr = array('forgetpw', 'public', 'version');
        if (in_array($webData['m'], $noAddArr)) {
            unset($webData);
        }

        /* Part Article */
        $wxappArticleCArr = array('showOther');
        if ($webData['m'] == 'article') {
            if (in_array($webData['c'], $wxappArticleCArr)) {
                unset($webData);
            }
        }

        /* Part Chat */
        $wxappChatCArr = array('getMine', 'goChat', 'beginChat', 'getmh', 'getChatPage', 'getdown', 'isResume', 'setMsgStatus', 'setVoiceStatus', 'prepare',
            'chatRight', 'getToken', 'getXjhChatPage', 'spviewChat', 'getUnloginToken', 'getJob', 'greetingSwitch');
        if ($webData['m'] == 'chat') {
            if (in_array($webData['c'], $wxappChatCArr)) {
                unset($webData);
            }
        }

        /* Part Company */
        $wxappCompanyCArr = array('comShowOther', 'getBusinessInfo', 'logVideo');
        if ($webData['m'] == 'company') {
            if (in_array($webData['c'], $wxappCompanyCArr)) {
                unset($webData);
            }
        }
        /* Part Fk */
        $wxappFkCArr = array('getTplid', 'getBusinessInfo', 'logVideo', 'xcxfk');
        if ($webData['m'] == 'fk') {
            if (in_array($webData['c'], $wxappFkCArr)) {
                unset($webData);
            }
        }

        /* Part Index */
        $wxappIndexCArr = array('homeOther', 'getFoot', 'getXybox', 'getAd', 'isgzh', 'isJobShowLast', 'getVideo', 'getCityDomain');
        if ($webData['m'] == 'index') {
            if (in_array($webData['c'], $wxappIndexCArr)) {
                unset($webData);
            }
        }

        /* Part Job */
        $wxappJobCArr = array('jobShowOther', 'addJobTelLog', 'getHotIssues');
        if ($webData['m'] == 'job') {
            if (in_array($webData['c'], $wxappJobCArr)) {
                unset($webData);
            }
        }

        /* Part Map */
        $wxappMapCArr = array('staticMap');
        if ($webData['m'] == 'map') {
            if (in_array($webData['c'], $wxappMapCArr)) {
                unset($webData);
            }
        }

        /* Part Once */
        $wxappOnceCArr = array('isadd');
        if ($webData['m'] == 'once') {
            if (in_array($webData['c'], $wxappOnceCArr)) {
                unset($webData);
            }
        }

        /* Part xjhlive */
        $wxappXjhliveCArr = array('setHits', 'getAjaxHits');
        if ($webData['m'] == 'xjhlive') {
            if (in_array($webData['c'], $wxappXjhliveCArr)) {
                unset($webData);
            }
        }


        return $webData;
    }

    private function memberWebLog($webData = array())
    {

        /* Part Model */
        $noAddArr = array('log');
        if (in_array($webData['m'], $noAddArr)) {
            unset($webData);
        }

        if ($webData['usertype'] == 2) {

            /* Part Chat */
            $memberChatCArr = array('single');
            if ($webData['m'] == 'chat') {
                if (in_array($webData['c'], $memberChatCArr)) {
                    unset($webData);
                }
            }

            /* Part Child */
            $memberChildCArr = array('getstatis', 'checkStatis');
            if ($webData['m'] == 'child') {
                if (in_array($webData['c'], $memberChildCArr)) {
                    unset($webData);
                }
            }

            /* Part Coupon */
            $memberCouponCArr = array('getCouponList');
            if ($webData['m'] == 'coupon_list') {
                if (in_array($webData['c'], $memberCouponCArr)) {
                    unset($webData);
                }
            }

            /* Part Down */
            $memberDownCArr = array('ajaxremark');
            if ($webData['m'] == 'down') {
                if (in_array($webData['c'], $memberDownCArr)) {
                    unset($webData);
                }
            }

            /* Part Index */
            $memberIndexCArr = array('couponsee', 'resumeajax');
            if ($webData['m'] == 'index') {
                if (in_array($webData['c'], $memberIndexCArr)) {
                    unset($webData);
                }
            }

            /* Part Info */
            $memberInfoCArr = array('ajaxCheck');
            if ($webData['m'] == 'Info') {
                if (in_array($webData['c'], $memberInfoCArr)) {
                    unset($webData);
                }
            }

            /* Part Jobadd */
            $memberJobaddCArr = array('getJobNum', 'jobCheck');
            if ($webData['m'] == 'jobadd') {
                if (in_array($webData['c'], $memberJobaddCArr)) {
                    unset($webData);
                }
            }

            /* Part Msg */
            $memberMsgCArr = array('getContent');
            if ($webData['m'] == 'msg') {
                if (in_array($webData['c'], $memberMsgCArr)) {
                    unset($webData);
                }
            }

            /* Part Spview */
            $memberSpviewCArr = array('getLineData', 'getResumeDtail', 'getSub', 'spSuccess', 'splog');
            if ($webData['m'] == 'spview') {
                if (in_array($webData['c'], $memberSpviewCArr)) {
                    unset($webData);
                }
            }
        } else if ($webData['usertype'] == 1) {

            /* Part Chat */
            $memberChatCArr = array('single');
            if ($webData['m'] == 'chat') {
                if (in_array($webData['c'], $memberChatCArr)) {
                    unset($webData);
                }
            }

            /* Part Index */
            $memberIndexCArr = array('resumeajax', 'jobajax');
            if ($webData['m'] == 'index') {
                if (in_array($webData['c'], $memberIndexCArr)) {
                    unset($webData);
                }
            }

            /* Part Resume */
            $memberResumeCArr = array('publicdel', 'resumerefresh');
            if ($webData['m'] == 'index') {
                if (in_array($webData['c'], $memberResumeCArr)) {
                    unset($webData);
                }
            }
        }

        return $webData;
    }

    private function wapMemberWebLog($webData = array())
    {
        if ($webData['usertype'] == 2) {

            $wapMemberComCArr = array('isgzh', 'xjhcity', 'getLineData');
            if ($webData['m'] == 'com') {
                if (in_array($webData['c'], $wapMemberComCArr)) {
                    unset($webData);
                }
            }
        } else if ($webData['usertype'] == 1) {

            $wapMemberUserCArr = array('isgzh', 'logstatus');
            if ($webData['m'] == 'user') {
                if (in_array($webData['c'], $wapMemberUserCArr)) {
                    unset($webData);
                }
            }
        }

        return $webData;
    }

    private function adminWebLog($webData = array())
    {
        include(PLUS_PATH.'route_admin.php');
        $m = $webData['m'];
        $c = $webData['c'];
        if ($webData['m'] == 'index') {// 首页
            if (!isset($logTypeM[$m][$c]['name'])) {
                unset($webData);
            }
        } else {
            $a = $webData['a'] ? $webData['a'] : 'index';
            if (!isset($logTypeM[$m][$c][$a]['name'])) {
                unset($webData);
            }
        }
        return $webData;
    }

    /**
     * 查询行为日志
     * @param array $whereData
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWebLog($whereData = array(), $data = array())
    {

        return $this->select_once('web_log', $whereData);
    }

    /**
     * 查询行为日志
     * @param array $whereData
     * @param array $data
     * @return array|bool|false|string|void
     */
    public function getWebLogList($whereData = array(), $data = array())
    {

        $data['field']  =   empty($data['field']) ? '*' : $data['field'];

        $List           =   $this->select_all('web_log', $whereData, $data['field']);

        if (!empty($List)) {

            $List       =   $this->getDataList($List);
        }

        return $List;
    }

    //会员日志后台列表数据处理
    private function getDataList($List)
    {
        $muids = $cuids = $ruids = array();
        $resume = $company = array();
        foreach ($List as $v) {

            $muids[] = $v['uid'];
            if ($v['usertype'] == 1){
                $ruids[] = $v['uid'];
            }elseif ($v['usertype'] == 2){
                $cuids[] = $v['uid'];
            }
        }

        $member = $this->select_all('member', array('uid' => array('in', pylode(',', $muids))), '`uid`,`username`');
        if (!empty($ruids)){
            $resume = $this->select_all('resume', array('uid' => array('in', pylode(',', $ruids))), '`uid`,`name`');
        }
        if (!empty($cuids)){
            $company = $this->select_all('company', array('uid' => array('in', pylode(',', $cuids))), '`uid`,`name`');
        }

        include PLUS_PATH.'route.php';

        foreach ($List as $k => $v) {

            $List[$k]['model_n']    =   $this->modelType[$v['model']];
            $List[$k]['usertype_n'] =   $this->userType[$v['usertype']];
            if ($v['model'] == 'app_member' || $v['model'] == 'weixin_member' || $v['model'] == 'baidu_member' || $v['model'] == 'toutioa_member'){
                $v['model'] =   'wapapp_member';
            }

            if ($v['model'] == 'app' || $v['model'] == 'weixin' || $v['model'] == 'baidu' || $v['model'] == 'toutioa'){
                $v['model'] =   'wxapp';
            }

            if ($v['model'] == 'member'){
                if ($v['usertype'] == 1){

                    if ($v['a']){

                        $List[$k]['action_name']    =   $logTypeUserM[$v['model']][$v['c']][$v['a']]['name'];
                    }elseif ($v['c']){

                        $List[$k]['action_name']    =   $logTypeUserM[$v['model']][$v['c']]['name'];
                    }elseif ($v['m']){

                        $List[$k]['action_name']    =   $logTypeUserM[$v['model']]['name'];
                    }
                }elseif ($v['usertype'] == 2){

                    if ($v['a']){

                        $List[$k]['action_name']    =   $logTypeComM[$v['model']][$v['c']][$v['a']]['name'];
                    }elseif ($v['c']){

                        $List[$k]['action_name']    =   $logTypeComM[$v['model']][$v['c']]['name'];
                    }elseif ($v['m']){

                        $List[$k]['action_name']    =   $logTypeComM[$v['model']]['name'];
                    }
                }
            }else if ($v['model'] == 'wap_member' || $v['model'] == 'wapapp_member'){
                if ($v['usertype'] == 1){

                    if ($v['a']){

                        $List[$k]['action_name']    =   $logTypeUserM[$v['model']][$v['m']][$v['c']][$v['a']]['name'];
                    }elseif ($v['c']){

                        $List[$k]['action_name']    =   $logTypeUserM[$v['model']][$v['m']][$v['c']]['name'];
                    }elseif ($v['m']){

                        $List[$k]['action_name']    =   $logTypeUserM[$v['model']][$v['m']]['name'];
                    }
                }elseif ($v['usertype'] == 2){

                    if ($v['a']){

                        $List[$k]['action_name']    =   $logTypeComM[$v['model']][$v['m']][$v['c']][$v['a']]['name'];
                    }elseif ($v['c']){

                        $List[$k]['action_name']    =   $logTypeComM[$v['model']][$v['m']][$v['c']]['name'];
                    }elseif ($v['m']){

                        $List[$k]['action_name']    =   $logTypeComM[$v['model']][$v['m']]['name'];
                    }
                }
            }else{

                if ($v['a']){

                    $List[$k]['action_name']    =   $logTypeM[$v['model']][$v['m']][$v['c']][$v['a']]['name'];
                }elseif ($v['c']){

                    $List[$k]['action_name']    =   $logTypeM[$v['model']][$v['m']][$v['c']]['name'];
                }elseif ($v['m']){

                    $List[$k]['action_name']    =   $logTypeM[$v['model']][$v['m']]['name'];
                }else{

                    $List[$k]['action_name']    =   $logTypeM[$v['model']]['name'];
                }
            }


            $List[$k]['time_n']     =   date('Y-m-d H:i:s', $v['time']);
            $List[$k]['timeymd_n']  =   date('Y-m-d', $v['time']);
            $List[$k]['timehis_n']  =   date('H:i:s', $v['time']);

            $List[$k]['hi_n']       =   date('H:i', $v['time']);
            $List[$k]['ip']         =   $v['ip'];

            //行为说明

            $List[$k]['info']       =   $List[$k]['model_n'] .' ' . $List[$k]['action_name'];
            foreach ($member as $val) {
                if ($val['uid'] == $v['uid']) {

                    $List[$k]['username']   =   $val['username'];
                }
            }

            foreach ($resume as $val) {
                if ($v['uid'] == $val['uid']) {

                    $List[$k]['name_n']     =   $val['name'];
                }
            }
            foreach ($company as $val) {
                if ($v['uid'] == $val['uid']) {

                    $List[$k]['name_n']     =   $val['name'];
                }
            }
        }
        return $List;
    }

    public function delWeblog($whereData = array(), $data = array())
    {

        $return['layertype'] = 0;

        if (!empty($whereData)) {
            if (!empty($whereData['id']) && $whereData['id'][0] == 'in') {
                $return['layertype']    =   1;
            }
            if ($data['norecycle'] == '1') {    //	数据库清理，不插入回收站

                $return['id']   =   $this->delete_all('web_log', $whereData, $data['limit'], '', '1');
            } else {

                $return['id']   =   $this->delete_all('web_log', $whereData, '');
            }

            $return['msg']      =   '行为日志';
            $return['errcode']  =   $return['id'] ? '9' : '8';
            $return['msg']      =   $return['id'] ? $return['msg'] . '删除成功！' : $return['msg'] . '删除失败！';
        } else {

            $return['msg']      =   '请选择您要删除的行为日志！';
            $return['errcode']  =   8;
        }
        return $return;
    }

    /**
     * @param $datas
     * @return mixed
     */
    function logCount($datas)
    {

        $where['uid']   =   intval($datas['uid']);

        //默认取当天数据
        $today          =   strtotime(date('Y-m-d'));
        if ($datas['time'] == 1) {

            $where['time']  =   array('>=', $today, 'AND');
        } else if ($datas['time'] == 2) {

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['time'][]            =   array('>=', strtotime('-1 day', $today), 'AND');
            $where['time'][]            =   array('<', $today, 'AND');
            $where['PHPYUNBTWEND_A']    =   '';
        } else if ($datas['time'] == 3) {

            $where['time']  =   array('>=', strtotime('-3 day', $today), 'AND');
        } else if ($datas['time'] == 4) {

            $where['time']  =   array('>=', strtotime('-7 day', $today), 'AND');
        }

        if ($datas['times']) {
            $times              =   @explode('~', $datas['times']);
            $where['PHPYUNBTWSTART_B']  =   '';
            $where['time'][]    =   array('>=', strtotime($times[0] . "00:00:00"), 'AND');
            $where['time'][]    =   array('<=', strtotime($times[1] . "23:59:59"), 'AND');
            $where['PHPYUNBTWEND_B']  =   '';
        }

        $count  =   $this->select_num('web_log', $where);
        $size   =   1000;
        $num    =   ceil($count / $size);

        $data['lognum'] = $data['lookjob'] = $data['searchjob'] = $data['sqjob'] = $data['chat'] = $data['lookresume'] = $data['downresume'] = $data['inviteresume'] = $data['addjob'] = $data['promote'] = $data['reserve'] = $data['pay'] = 0;

        $uInfo  =   $this->select_once('member', array('uid' => $datas['uid']), 'uid,username,moblie');

        if ($datas['usertype'] == 1){

            $sInfo  =   $this->select_once('resume', array('uid' => $datas['uid']), 'uid,name');
        }else if ($datas['usertype'] == 2){

            $sInfo  =   $this->select_once('company', array('uid' => $datas['uid']), 'uid,name');
        }

        $userInfo   =   array('uid' => $datas['uid'], 'username' => $uInfo['username'], 's_name' => $sInfo['name'], 'mobile' => $uInfo['moblie']);
        $data['userinfo'] = $userInfo;
        for ($i = 0; $i < $num; $i++) {

            $where['limit']     =   array($i * $size, $size);

            $where['orderby']   =   'id,asc';

            $list   =   $this->select_all('web_log', $where);

            if (!empty($list)) {

                foreach ($list as $key => $value) {

                    $data['lognum']++;

                    if ($key == 0) {
                        $data['startlog']   =   date('Y-m-d H:i:s', $value['time']);
                        $data['user_info']  =   'UID：'.$userInfo['uid']. '，账号：'.$userInfo['username']. '，名称：'.$userInfo['s_name']. '，手机：'.$userInfo['mobile'];

                    }
                    if ($datas['usertype'] == '1') {

                        //浏览职位详情
                        if ($value['c'] == 'comapply' || ($value['m'] == 'job' && $value['c'] == 'show')) {
                            $data['lookjob']++;
                        }
                        //职位搜索
                        if ($value['m'] == 'job' && ($value['c'] == '' || $value['c'] == 'search' || $value['c'] == 'list')) {
                            $data['searchjob']++;
                        }
                        //职位申请
                        if ($value['c'] == 'sq_job' || $value['c'] == 'apply' || ($value['c'] == 'comapply' && $value['is_ajax'] == 1)) {

                            $data['sqjob']++;
                        }

                        //聊天统计
                        if (($value['m'] == 'user' && $value['c'] == 'chat') || ($value['m'] == 'chat' && $value['c'] == 'chatLog')) {
                            $data['chat']++;
                        }

                    } elseif ($datas['usertype'] == '2') {

                        //浏览简历
                        if (($value['m'] == 'resume' && $value['c'] == 'show' && $value['a'] == '') ||($value['m'] == 'com' && $value['c'] == 'hr' && $value['a'] == 'resumeInfo')) {
                            $data['lookresume']++;
                        }

                        //邀请面试
                        if ($value['c'] == 'sava_ajaxresume' || $value['c'] == 'invitesave') {
                            $data['inviteresume']++;
                        }

                        //聊天统计
                        if (($value['m'] == 'chat' && $value['c'] == 'chatLog') || ($value['m'] == 'com' && $value['c'] == 'chat')) {
                            $data['chat']++;
                        }

                        //简历下载
                        if ($value['c'] == 'forlink' || $value['c'] == 'for_link' || $value['m'] == 'down') {
                            $data['downresume']++;
                        }

                        //发布修改职位
                        if (($value['c'] == 'jobadd' && $value['a'] == 'save') || ($value['m'] == 'job' && $value['c'] == 'saveJob')) {
                            $data['addjob']++;
                        }

                        //推广服务 置顶 推荐 紧急 预约刷新
                        if ($value['c'] == 'job' && ($value['a'] == 'jobPromote' || $value['a'] == 'reserveUp')) {
                            $data['promote']++;
                        }

                        if ($value['m'] == 'fk' && ($value['c'] == 'getOrder' || $value['c'] == 'dkzf' || $value['c'] == 'coupongm')){

                            $params =   unserialize($value['params']);
                            if (in_array($params['server'], ['jobtop', 'jobrec', 'joburgent', 'autojob'])){
                                $data['promote']++;
                            }
                        }

                        //浏览套餐 增值包
                        if (($value['m'] == 'com' && ($value['c'] == 'right' || $value['c'] == 'server')) || ($value['m'] == 'fk' && $value['c'] == 'server')) {
                            $data['pay']++;
                        }
                    }
                }
            }
        }

        return $data;

    }

    /**
     * 计划任务：删除行为日志
     */
    public function delLogByCron()
    {

        $this->delete_all('web_log', array('time' => array('<', strtotime('-1 month'))), '', '', '1');
        $this->delete_all('web_log_sub', array('time' => array('<', strtotime('-1 month'))), '', '', '1');
    }

    public function getNum($where)
    {
        return $this->select_num('web_log', $where);
    }
    /**
     * 计划任务：删除管理员轨迹日志
     */
    public function delAdminLogByCron()
    {
        
        $this->delete_all('admin_web_log', array('time' => array('<', strtotime('-1 month'))), '', '', '1');
    }
}

?>