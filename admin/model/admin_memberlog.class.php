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

class admin_memberlog_controller extends adminCommon
{

    private function userLogSearch()
    {
        $opera      =   array('88' => '财务', '2' => '简历', '6' => '申请', '5' => '收藏/关注', '7' => '基本信息', '11' => '修改账号', '8' => '修改密码', '12' => '账号认证', '16' => '图片', '17' => '积分兑换', '18' => '消息', '19' => '问答', '23' => '举报', '25' => '悬赏推荐', '26' => '浏览');
        $search[]   =   array('param' => 'operas', 'name' => '操作内容', 'value' => $opera);

        $parr       =   array('1' => '增加', '2' => '修改', '3' => '删除', '4' => '刷新');
        $search[]   =   array('param' => 'parrs', 'name' => '操作类型', 'value' => $parr);

        $ad_time    =   array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月');
        $search[]   =   array("param" => "end", "name" => '操作时间', "value" => $ad_time);

        $this->yunset('search_list', $search);
    }

    private function comLogSearch()
    {

        $opera      =   array('1' => '职位', '9' => '兼职', '88' => '财务', '3' => '下载简历', '23' => '举报', '4' => '邀请面试', '5' => '收藏/关注', '6' => '申请/报名', '7' => '基本信息', '8' => '修改密码', '11' => '修改账号', '12' => '账号认证', '14' => '招聘会/专题', '15' => '地图设置', '16' => '图片', '17' => '积分兑换', '18' => '消息', '19' => '问答', '24' => '优惠券', '25' => '悬赏推荐', '26' => '浏览/屏蔽');
        $search[]   =   array('param' => 'operas', 'name' => '操作内容', 'value' => $opera);

        $parr       =   array('1' => '增加', '2' => '变更', '3' => '删除', '4' => '刷新');
        $search[]   =   array('param' => 'parrs', 'name' => '操作类型', 'value' => $parr);

        $ad_time    =   array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月');
        $search[]   =   array("param" => "end", "name" => '操作时间', "value" => $ad_time);

        $this->yunset('search_list', $search);
    }

    private function ltLogSearch()
    {

        $opera      =   array('10' => '职位', '88' => '财务', '3' => '下载简历', '5' => '收藏关注', '6' => '应聘委托', '7' => '基本信息', '8' => '修改密码', '11' => '修改账号', '12' => '账号认证', '16' => '图片', '17' => '积分兑换', '18' => '消息', '19' => '问答', '24' => '优惠券', '25' => '悬赏推荐', '26' => '浏览');
        $search[]   =   array('param' => 'operas', 'name' => '操作内容', 'value' => $opera);

        $parr       =   array('1' => '增加', '2' => '修改', '3' => '删除', '4' => '刷新');
        $search[]   =   array('param' => 'parrs', 'name' => '操作类型', 'value' => $parr);

        $ad_time    =   array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月');
        $search[]   =   array("param" => "end", "name" => '操作时间', "value" => $ad_time);

        $this->yunset('search_list', $search);
    }

    private function pxLogSearch()
    {
        $opera      =   array(7 => '基本信息', 8 => '修改密码', 11 => '修改账号', 12 => '账号认证', 20 => '讲师', 21 => '课程', 6 => '预约报名', 22 => '新闻', 16 => '图片', 17 => '积分兑换', 18 => '消息', 19 => '问答');
        $search[]   =   array('param' => 'operas', 'name' => '操作内容', "value" => $opera);

        $ad_time    =   array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月');
        $search[]   =   array("param" => "end", "name" => '操作时间', "value" => $ad_time);

        $this->yunset('search_list', $search);
    }

    private function gqLogSearch()
    {

        $parr       =   array('1' => '增加', '3' => '删除', '4' => '刷新');
        $search[]   =   array('param' => 'parrs', 'name' => '操作类型', 'value' => $parr);

        $ad_time    =   array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月');
        $search[]   =   array("param" => "end", "name" => '操作时间', "value" => $ad_time);

        $this->yunset('search_list', $search);
    }

    function index_action()
    {

        $logM       =   $this->MODEL('log');
        $memberM    =   $this->MODEL('userinfo');

        if ($_GET['utype']) {

            $where['usertype']  =   trim($_GET['utype']);
            $urlarr['utype']    =   $_GET['utype'];
        } else {

            $where['usertype']  =   1;
        }
        if (isset($_GET['uid'])) {

            $where['uid']       =   intval($_GET['uid']);
            $urlarr['uid']      =   $_GET['uid'];
        }

        $keywordStr =   trim($_GET['keyword']);

        if (!empty($keywordStr)) {
            if ($_GET['type'] == 1) {

                $member         =   $memberM->getList(array('username' => array('like', $keywordStr)), array('field' => '`uid`,`username`'));
                foreach ($member as $v) {
                    $uid[]      =   $v['uid'];
                }
                $where['uid']   =   array('in', pylode(",", $uid));

            } elseif ($_GET['type'] == 3) {

                $where['uid']   =   $keywordStr;
            }

            $urlarr['keyword']  =   $keywordStr;
            $urlarr['type']     =   $_GET['type'];
        }

        $contentStr =   trim($_GET['content']);

        if (!empty($contentStr)) {

            $logDetailList      =   $this->obj->select_all('member_log_detail', array('detail' => array('like', $contentStr)), 'log_id');

            if (!empty($logDetailList)){

                $logIds         =   array();
                foreach ($logDetailList as $lk => $lv){

                    $logIds[]   =   $lv['log_id'];
                }

                $where['PHPYUNBTWSTART_A']  =   '';
                $where['id']        =   array('in', pylode(',', $logIds), '');
                $where['content']   =   array('like', $contentStr, 'OR');
                $where['PHPYUNBTWEND_A']    =   '';
            }else{

                $where['content']   =   array('like', $contentStr);
            }

            $urlarr['content']  =   $_GET['content'];
        }

        if (!empty($_GET['end'])) {

            if ($_GET['end'] == '1') {

                $where['ctime'] =   array('>=', strtotime(date("Y-m-d 00:00:00")));
            } else {

                $where['ctime'] =   array('>=', strtotime('-' . (int)$_GET['end'] . 'day'));
            }
            $urlarr['end']      =   $_GET['end'];
        }

        if (!empty($_GET['operas'])){

            $operaStr   =   intval($_GET['operas']);

            $operaSql 	= 	array(
                '1'		=>	array('name' => array('职位')),
                '2'		=>	array('name' => array('创建','简历', '经历')),
                '3'		=>	array('name' => array('下载')),
                '4'	 	=>	array('name' => array('邀请')),
                '5'	 	=>	array('name' => array('收藏', '关注', '备注')),
                '6'		=>	array('name' => array('申请', '报名', '应聘', '委托')),
                '7'		=>	array('name' => array('基本信息')),
                '8'		=>	array('name' => array('修改密码')),
                '9'		=>	array('name' => array('兼职')),
                '10'    =>	array('name' => array('猎头职位')),
                '11'    =>	array('name' => array('用户名', '身份')),
                '12'    =>	array('name' => array('账号认证', '解除', '绑定', '验证', '资质', '执照', '认证')),
                '14'    =>	array('name' => array('招聘会', '专题')),
                '15'    =>	array('name' => array('地图', '助力')),
                '16'	=>	array('name' => array('图片', '头像', 'LOGO', '环境', '产品', '新闻', '二维码', '横幅')),
                '17' 	=>	array('name' => array('兑换', '积分'), 'realId' => 17),
                '18'	=>	array('name' => array('回复', '咨询', '留言', '系统消息')),
                '19' 	=>	array('name' => array('问答')),
                '20'	=>	array('name' => array('培训师', '讲师')),
                '21'	=>	array('name' => array('课程')),
                '22'	=>	array('name' => array('新闻')),
                '23'	=>	array('name' => array('举报')),
                '24' 	=>	array('name' => array('优惠券')),
                '25' 	=>	array('name' => array('悬赏', '推送')),
                '26' 	=>	array('name' => array('浏览', '黑名单')),
                '27' 	=>	array('name' => array('子账号')),
                '29' 	=>	array('name' => array('项目')),
                '30' 	=>	array('name' => array('聊天', '直聊')),
                '88' 	=>	array('name' => array('订单'))
            );

            if (array_key_exists($operaStr, $operaSql)) {

                if (count($operaSql[$operaStr]['name']) == 1){

                    $where['content']           =   array('like', $operaSql[$operaStr]['name'][0]);
                }else{

                    $where['PHPYUNBTWSTART']    =   '';
                    foreach ($operaSql[$operaStr]['name'] as $oV) {
                        $where['content'][]     =   array('like', $oV, 'OR');
                    }
                    $where['PHPYUNBTWEND']      =   '';
                }
            } elseif (!empty($operasStr)) {

                $where['opera']             =   $operaStr;
            }
        }

        if (isset($_GET['parrs'])) {

            $where['type']      =   intval($_GET['parrs']);
            $urlarr['parrs']    =   $_GET['parrs'];
        }

        if (!empty($_GET['time'])) {

            $time               =   @explode('~', $_GET['time']);
            $where['ctime'][]   =   array('>=', strtotime($time[0] . "00:00:00"));
            $where['ctime'][]   =   array('<=', strtotime($time[1] . "23:59:59"));
            $urlarr['time']     =   $_GET['time'];
        }

        $urlarr         =   $_GET;
        $urlarr['page'] =   '{{page}}';

        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->pageList('member_log', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            if ($_GET['order']) {

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =   $_GET['order'];
                $urlarr['t']        =   $_GET['t'];
            } else {

                $where['orderby']   =   array('id,desc');
            }
            $where['limit']         =   $pages['limit'];

            $List   =   $logM->getMemlogList($where, array('utype' => 'admin'));
            $this->yunset(array('rows' => $List));
        }

        if ($_GET['utype'] == 2){

            $this->comLogSearch();
        }elseif ($_GET['utype'] == 3){

            $this->ltLogSearch();
        }elseif ($_GET['utype'] == 4){

            $this->pxLogSearch();
        }elseif ($_GET['utype'] == 5){

            $this->gqLogSearch();
        }else{

            $this->userLogSearch();
        }

        $this->yunset("type", $_GET['type']);
        $this->yuntpl(array('admin/admin_member_log'));
    }

    function delLog_action()
    {
        $this->check_token();

        $logM   =   $this->MODEL('log');

        if ($_GET['del'] == 'allcom') {

            $where['usertype']  =   2;
            $logM->delMemlog($where);
            $this->layer_msg('已清空企业日志！', 9, 0, $_SERVER['HTTP_REFERER']);
        } elseif ($_GET['del'] == 'alluser') {

            $where['usertype']  =   1;
            $logM->delMemlog($where);
            $this->layer_msg('已清空个人日志！', 9, 0, $_SERVER['HTTP_REFERER']);
        } elseif ($_GET['del'] == 'alllt') {

            $where['usertype']  =   3;
            $logM->delMemlog($where);
            $this->layer_msg('已清空猎头日志！', 9, 0, $_SERVER['HTTP_REFERER']);
        } elseif ($_GET['del'] == 'alltrain') {

            $where['usertype']  =   4;
            $logM->delMemlog($where);
            $this->layer_msg('已清空培训日志！', 9, 0, $_SERVER['HTTP_REFERER']);
        } elseif ($_GET['del'] == 'allgq') {

            $where['usertype']  =   5;
            $logM->delMemlog($where);
            $this->layer_msg('已清空供求日志！', 9, 0, $_SERVER['HTTP_REFERER']);
        } elseif ($_GET['del']) {

            $del    =   $_GET['del'];
            if (is_array($del)) {

                $where['id']    =   array('in', pylode(',', $del));
            } else {

                $where['id']    =   $del;
            }

            $return =   $logM->delMemlog($where);
            $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
        }
    }
}

?>