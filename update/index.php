<?php
set_time_limit(0);
include("../global.php");

$version = 'v7.0.1 VIP版(20240122)';

$query = $db->query("SELECT * FROM ".$db_config[def]."version WHERE 1 ORDER BY id DESC");

$vr = $db->fetch_array($query);

if($vr['version'] == $version){
    echo $version."升级文件已运行！";exit();
}

if($_GET[step]!="11"){
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>正在升级中.... - - Powered by PHPYun.</title>
        
<link href="'.$config[sy_weburl].'/template/member/style/msg.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="content" style="background:f5f5f5;">
  <div class="content-title" style="width:100%"><span style="width:100%">正在升级中,已进行'.$_GET[step].'/11</span></div>
  <div style="border:1px solid #ccc; float:left;width:100%;">
    <dl style="width:100%">
       <p style="text-align:center;"><img src="loading.gif"></p><br>
	   <p style="text-align:center;"><font color="red">本次更新时间较长,请不要中途中断,以免升级失败,请耐心等待！</font></p>
    </dl>
</div>
</div>
</body>
</html>';
};
if($_GET[step]==""){
    
    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=1';</script>";
}
/****************************第一步：新增数据字段************************************/
if($_GET[step]=="1"){

    
    $db->query("ALTER TABLE `$db_config[def]company_job` ADD COLUMN `add_crm` int(11) DEFAULT '0'");
    $db->query("ALTER TABLE `$db_config[def]subscribe` ADD COLUMN `stime` int(10) DEFAULT '0'");
    $db->query("ALTER TABLE `$db_config[def]hr_log` ADD COLUMN `uptime` int(10) DEFAULT '0'");
    $db->query("ALTER TABLE `$db_config[def]special_job` ADD COLUMN `sort` int(10) UNSIGNED NOT NULL DEFAULT '0'");
    
    $db->query("UPDATE `" . $db_config[def] . "ad_class` SET `class_name`='移动端首页通栏广告（一） 700＊90' WHERE `id`=503");
    
    $query2 = $db->query("SELECT `id` FROM ".$db_config[def]."ad_class WHERE `id` = '600'");
    $row2   = $db->fetch_array($query2);
    if (!isset($row2['id'])){
        $db->query("INSERT INTO `$db_config[def]ad_class`(`id`, `class_name`, `orders`, `href`, `integral_buy`, `place`, `type`, `btype`, `x`, `y`, `remark`) VALUES
            (600, '移动端首页通栏广告（二） 345＊90', 0, '', '', 2, 1, 0, '', '',NULL);");
        $db->query("INSERT INTO `$db_config[def]ad_class`(`id`, `class_name`, `orders`, `href`, `integral_buy`, `place`, `type`, `btype`, `x`, `y`, `remark`) VALUES
            (601, '移动端首页通栏广告（三） 225＊90', 0, '', '', 2, 1, 0, '', '',NULL);");
    }
    $query3 = $db->query("SELECT `id` FROM ".$db_config[def]."tplapp WHERE `name` = 'adhdid2'");
    $row3   = $db->fetch_array($query3);
    if (!isset($row3['id'])){
        $db->query("INSERT INTO `$db_config[def]tplapp`(`id`, `name`, `config`,`pic`, `type`, `url`, `display`, `navsort`, `desc`) VALUES
            (NULL, 'adhdid2', '600', NULL, 'index', NULL, 0, 0, '');");
        $db->query("INSERT INTO `$db_config[def]tplapp`(`id`, `name`, `config`,`pic`, `type`, `url`, `display`, `navsort`, `desc`) VALUES
            (NULL, 'adhdid3', '601', NULL, 'index', NULL, 0, 0, '');");
        $db->query("INSERT INTO `$db_config[def]tplapp`(`id`, `name`, `config`,`pic`, `type`, `url`, `display`, `navsort`, `desc`) VALUES
            (NULL, 'sysyjlogin', '1', NULL, 'index', NULL, 0, 0, '');");
    }
    
    $db->query("CREATE TABLE `$db_config[def]chat_zh`  (
        `id` int(25) NOT NULL AUTO_INCREMENT,
        `fid` int(11) NULL DEFAULT NULL,
        `fusertype` int(1) NULL DEFAULT NULL,
        `toid` int(11) NULL DEFAULT NULL,
        `tousertype` int(1) NULL DEFAULT NULL,
        `beginid` varchar(50) NULL DEFAULT NULL,
        `ctime` int(11) NULL DEFAULT NULL,
        PRIMARY KEY (`id`) USING BTREE,
        INDEX `beginid`(`beginid`) USING BTREE
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");
    
    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=2';</script>";
}
/****************************第二步：更新后台导航***************************/
if ($_GET['step'] == 2) {
    
    $db->query("UPDATE `" . $db_config[def] . "hr_log` SET uptime=ctime WHERE `uid`>0");
    
    $db->query("TRUNCATE TABLE  `$db_config[def]admin_navigation`");
    
    $db->query("INSERT INTO `$db_config[def]admin_navigation` (`id`, `name`, `keyid`, `url`, `menu`, `classname`, `sort`, `display`, `dids`, `level`, `path`) VALUES
        (1, '会员', 0, '', 1, '0', 1, 0, 1, 1, ''),
        (2, '内容', 0, '', 1, '0', 2, 0, 1, 1, ''),
        (3, '运营', 0, '', 1, '0', 3, 0, 1, 1, ''),
        (4, '工具', 0, '', 1, '0', 4, 0, 1, 1, ''),
        (5, '系统', 0, '', 1, '0', 5, 0, 1, 1, ''),
        (6, '企业', 1, '', 1, 'nav_company', 1, 0, 1, 2, ''),
        (7, '个人', 1, '', 1, 'nav_user', 2, 0, 1, 2, ''),
        (8, '猎头', 1, '', 0, 'nav_lt', 3, 0, 1, 2, ''),
        (9, '培训', 1, '', 0, 'nav_py', 4, 0, 1, 2, ''),
        (10, '新闻', 2, '', 0, 'nav_news', 1, 0, 1, 2, ''),
        (11, '测评', 2, '', 0, 'nav_cp', 2, 0, 1, 2, ''),
        (12, '招聘会', 2, '', 0, 'nav_zph', 3, 0, 1, 2, ''),
        (13, '工具箱', 2, '', 0, 'nav_gjx', 4, 0, 1, 2, ''),
        (14, '公告', 2, '', 0, 'nav_gg', 5, 0, 1, 2, ''),
        (15, '问答', 2, '', 0, 'nav_ask', 6, 0, 1, 2, ''),
        (16, '企业用户', 6, 'index.php?m=user&c=company', 2, '0', 1, 0, 1, 3, '/companycrm'),
        (17, '个人用户', 7, 'index.php?m=user&c=users_member', 1, '0', 1, 0, 1, 3, '/userscrm'),
        (18, '猎头用户', 8, 'index.php?m=user&c=admin_lt_member', 1, '0', 1, 0, 1, 3, '/ltuser'),
        (19, '猎头职位', 8, 'index.php?m=user&c=admin_lt_job', 1, '0', 2, 0, 1, 3, '/ltjob'),
        (20, '猎头简历', 8, 'index.php?m=user&c=admin_height_user', 1, '0', 3, 0, 1, 3, '/ltresume'),
        (21, '认证&审核', 8, 'index.php?m=user&c=admin_lt_cert', 1, '0', 4, 0, 1, 3, '/ltrz'),
        (22, '悬赏', 19, 'index.php?m=user&c=admin_lt_xuanshang', 1, '0', 3, 0, 1, 4, ''),
        (23, '增值套餐', 8, 'index.php?m=user&c=admin_lt_rating', 1, '0', 6, 0, 1, 3, '/lttc'),
        (24, '猎头设置', 8, 'index.php?m=user&c=admin_ltset', 1, '0', 7, 0, 1, 3, '/ltset'),
        (25, '培训用户', 9, 'index.php?m=user&c=peixun_member', 1, '0', 1, 0, 1, 3, '/peixun_user'),
        (26, '培训讲师', 9, 'index.php?m=user&c=peixun_teacher', 1, '0', 2, 0, 1, 3, '/peixun_teacher'),
        (27, '培训课程', 9, 'index.php?m=user&c=peixun_subject', 1, '0', 3, 0, 1, 3, '/peixun_subject'),
        (28, '认证&审核', 9, 'index.php?m=user&c=peixun_renzheng', 1, '0', 4, 0, 1, 3, '/peixun_renzheng'),
        (29, '课程报名', 27, 'index.php?m=user&c=peixun_subjectbm', 1, '0', 5, 0, 1, 4, ''),
        (30, '培训新闻', 9, 'index.php?m=user&c=peixun_news', 1, '0', 6, 0, 1, 3, '/peixun_news'),
        (31, '课程咨询', 27, 'index.php?m=user&c=peixun_subjectzx', 1, '0', 7, 0, 1, 4, ''),
        (32, '培训设置', 9, 'index.php?m=user&c=peixun_config', 1, '0', 8, 0, 1, 3, '/peixun_config'),
        (33, '简历管理', 7, 'index.php?m=user&c=users_resume', 2, '0', 2, 0, 1, 3, '/resume'),
        (34, '认证&审核', 7, 'index.php?m=user&c=users_usercert', 1, '0', 3, 0, 1, 3, '/renzheng'),
        (35, '委托简历', 33, 'index.php?m=user&c=users_trust', 1, '0', 4, 0, 1, 4, ''),
        (36, '普工简历', 156, 'index.php?m=user&c=weipin_tiny', 1, '0', 2, 0, 1, 3, '/weipin_tiny'),
        (37, '求职咨询', 7, 'index.php?m=user&c=users_msg', 1, '0', 6, 0, 1, 3, '/zixun'),
        (38, '简历记录', 7, 'index.php?m=user&c=users_userlog', 1, '0', 7, 0, 1, 3, '/xingwei'),
        (39, '个人设置', 7, 'index.php?m=user&c=users_userset', 1, '0', 8, 0, 1, 3, '/userset'),
        (40, '职位管理', 6, 'index.php?m=user&c=company_job', 2, '0', 2, 0, 1, 3, '/companyjob'),
        (41, '名企信息', 16, 'index.php?m=user&c=hotjob', 1, '0', 3, 0, 1, 4, ''),
        (42, '兼职管理', 40, 'index.php?m=user&c=partjob', 1, '0', 15, 0, 1, 4, ''),
        (43, '悬赏赏金职位', 40, 'index.php?m=user&c=jobpack', 1, '0', 16, 0, 1, 4, ''),
        (44, '企业产品审核', 47, 'index.php?m=user&c=company_product', 1, '0', 6, 0, 1, 4, ''),
        (45, '企业新闻审核', 47, 'index.php?m=user&c=company_news', 1, '0', 6, 0, 1, 4, ''),
        (46, '评价&模板', 6, 'index.php?m=user&c=company_evaluate', 1, '0', 8, 0, 1, 3, '/companyms'),
        (47, '认证&审核', 6, 'index.php?m=user&c=company_cert', 1, '0', 9, 0, 1, 3, '/companyrz'),
        (48, '店铺招聘', 156, 'index.php?m=user&c=weipin_once', 1, '0', 1, 0, 1, 3, '/weipin_once'),
        (49, '招聘专题', 343, 'index.php?m=yunying&c=special_special', 1, '0', 1, 0, 1, 3, '/special'),
        (50, '职位记录', 6, 'index.php?m=user&c=company_comlog', 1, '0', 12, 0, 1, 3, '/companyjoblog'),
        (52, '套餐服务', 6, 'index.php?m=user&c=company_comrating', 1, '0', 14, 0, 1, 3, '/companyvip'),
        (53, '企业设置', 6, 'index.php?m=user&c=company_comset', 2, '0', 15, 0, 1, 3, '/companyset'),
        (54, '新闻管理', 10, 'index.php?m=neirong&c=news', 2, '0', 1, 0, 1, 3, '/newsmanage'),
        (55, '添加/修改', 54, 'index.php?m=neirong&c=news&a=addnews', 1, '0', 2, 0, 1, 4, ''),
        (56, '新闻类别', 10, 'index.php?m=neirong&c=news&a=group', 1, '0', 3, 0, 1, 3, '/newslb'),
        (57, '工具箱', 13, 'index.php?m=neirong&c=toolbox_doc', 1, '0', 1, 0, 1, 3, '/toolbox_doc'),
        (58, '工具箱类别', 13, 'index.php?m=neirong&c=toolbox_class', 1, '0', 2, 0, 1, 3, '/toolbox_class'),
        (59, '公告管理', 14, 'index.php?m=neirong&c=announcement', 2, '0', 1, 0, 1, 3, '/announcement'),
        (61, '现场招聘会', 12, 'index.php?m=neirong&c=zhaopinhui', 1, '0', 1, 0, 1, 3, '/xczph'),
        (62, '添加/修改', 61, 'index.php?m=neirong&c=zhaopinhui&a=add', 1, '0', 1, 0, 1, 4, ''),
        (63, '参会企业', 61, 'index.php?m=neirong&c=zhaopinhui&a=com', 1, '0', 2, 0, 1, 4, '/chcompany'),
        (64, '招聘会场地', 61, 'index.php?m=neirong&c=zph_space', 1, '0', 3, 0, 1, 4, '/zphaddress'),
        (65, '意见反馈', 198, 'index.php?m=system&c=info_feedback', 1, '0', 1, 0, 1, 3, '/feedback'),
        (66, '问答管理', 15, 'index.php?m=neirong&c=question', 1, '0', 1, 0, 1, 3, '/question'),
        (68, '系统消息', 198, 'index.php?m=system&c=info_systeminfo', 1, '0', 4, 0, 1, 3, '/systeminfo'),
        (69, '测评试卷', 11, 'index.php?m=neirong&c=evaluate', 1, '0', 1, 0, 1, 3, '/cpsj'),
        (70, '添加/修改', 69, 'index.php?m=neirong&c=evaluate&a=add', 1, '0', 2, 0, 1, 4, ''),
        (71, '测评类别', 11, 'index.php?m=neirong&c=evaluate&a=group', 1, '0', 3, 0, 1, 3, '/cpfl'),
        (72, '测评留言', 11, 'index.php?m=neirong&c=evaluate&a=message', 1, '0', 5, 0, 1, 3, '/lymanage'),
        (73, '类别', 5, '', 0, 'nav_lb', 4, 0, 1, 2, ''),
        (74, '商城', 3, '', 0, 'nav_shop', 8, 0, 1, 2, ''),
        (75, '广告', 3, '', 0, 'nav_guanggao', 1, 0, 1, 2, ''),
        (76, '商品管理', 74, 'index.php?m=yunying&c=shop_reward', 1, '0', 1, 0, 1, 3, '/shopreward'),
        (77, '兑换记录', 74, 'index.php?m=yunying&c=shop_list', 1, '0', 2, 0, 1, 3, '/shoplist'),
        (78, '优惠券管理', 166, 'index.php?m=yunying&c=coupon', 1, '0', 3, 0, 1, 3, '/coupon'),
        (79, '优惠券记录', 166, 'index.php?m=yunying&c=coupon_list', 1, '0', 2, 0, 1, 3, '/couponlist'),
        (81, '充值卡管理', 166, 'index.php?m=yunying&c=coupon_prepaid', 1, '0', 6, 0, 1, 3, '/couponprepaid'),
        (82, '商品分类', 74, 'index.php?m=yunying&c=shop_class', 1, '0', 7, 0, 1, 3, '/shopclass'),
        (83, '广告管理', 75, 'index.php?m=yunying&c=ad', 2, '0', 1, 0, 1, 3, '/ad'),
        (85, '广告订单', 75, 'index.php?m=yunying&c=ad_order', 1, '0', 3, 0, 1, 3, '/ad_order'),
        (86, '广告类别', 75, 'index.php?m=yunying&c=ad_class', 1, '0', 4, 0, 1, 3, '/ad_class'),
        (87, '个人会员', 1026, 'index.php?m=system&c=category_userclass', 1, '0', 1, 0, 1, 3, '/member_index'),
        (88, '城市管理', 73, 'index.php?m=system&c=category_city', 1, '0', 2, 0, 1, 3, '/city'),
        (89, '行业类别', 73, 'index.php?m=system&c=category_industry', 1, '0', 3, 0, 1, 3, '/industry'),
        (91, '企业会员', 1026, 'index.php?m=system&c=category_comclass', 1, '0', 5, 0, 1, 3, ''),
        (92, '职位类别', 73, 'index.php?m=system&c=category_job_class', 1, '0', 4, 0, 1, 3, '/job_class'),
        (93, '兼职分类', 73, 'index.php?m=system&c=category_partclass', 1, '0', 6, 0, 1, 3, '/partclass'),
        (94, '猎头会员分类', 1027, 'index.php?m=system&c=category_ltclass', 1, '0', 7, 0, 1, 3, ''),
        (95, '猎头行业分类', 1027, 'index.php?m=system&c=category_lthy_class', 1, '0', 8, 0, 1, 3, ''),
        (96, '猎头职位分类', 1027, 'index.php?m=system&c=category_ltjob_class', 1, '0', 9, 0, 1, 3, ''),
        (97, '问答类别', 15, 'index.php?m=neirong&c=question_class', 1, '0', 2, 0, 1, 3, '/question_class'),
        (98, '举报原因', 73, 'index.php?m=system&c=category_reason', 1, '0', 8, 0, 1, 3, '/reason'),
        (99, '课程类别', 1028, 'index.php?m=system&c=category_px_subject_class', 1, '0', 12, 0, 1, 3, ''),
        (100, '开课类型', 1028, 'index.php?m=system&c=category_px_subject_type', 1, '0', 13, 0, 1, 3, ''),
        (101, '单页面类别', 195, 'index.php?m=system&c=singleclass', 1, '0', 14, 0, 1, 3, '/singleclass'),
        (102, '管理员', 5, '', 0, 'nav_gly', 5, 0, 1, 2, ''),
        (103, '生成', 4, '', 0, 'nav_sc', 0, 0, 1, 2, ''),
        (104, '我的帐号', 102, 'index.php?m=system&c=role_myuser', 2, '0', 1, 0, 1, 3, '/myaccount'),
        (105, '管理员列表', 102, 'index.php?m=system&c=role_user', 1, '0', 2, 0, 1, 3, '/user'),
        (110, '管理员日志', 102, 'index.php?m=system&c=role_logrecord', 1, '0', 3, 0, 1, 3, '/logrecord'),
        (109, '会员日志', 401, 'index.php?m=user&c=admin_memberlog', 1, '0', 7, 0, 1, 3, '/userlog'),
        (112, '管理员类型', 102, 'index.php?m=system&c=role_ugroup', 1, '0', 4, 0, 1, 3, '/ugroup'),
        (113, '页面生成', 103, 'index.php?m=tool&c=generate_page', 1, '0', 1, 0, 1, 3, '/generate_page'),
        (117, '生成缓存', 103, 'index.php?m=tool&c=generate_cache', 1, '0', 5, 0, 1, 3, '/generate_cache'),
        (120, '生成XML', 103, 'index.php?m=tool&c=generate_xml&a=archive', 1, '0', 8, 0, 1, 3, '/generate_xml'),
        (121, '设置', 5, '', 0, 'nav_set', 1, 0, 1, 2, ''),
        (122, '微信', 4, '', 1, 'nav_weixin', 0, 0, 1, 2, ''),
        (123, '数据', 4, '', 1, 'nav_data', 0, 0, 1, 2, ''),
        (124, '微信菜单', 122, 'index.php?m=tool&c=weixinmenu', 1, '0', 2, 0, 1, 3, '/weixinmenu'),
        (125, '微信红包', 122, 'index.php?m=tool&c=weixinred', 1, '0', 3, 0, 1, 3, '/weixinred'),
        (127, '微信通知', 122, 'index.php?m=tool&c=wxtz', 1, '', 8, 0, 1, 3, '/wxtz'),
        (128, '用户日志', 122, 'index.php?m=tool&c=weixinrecord', 1, '0', 4, 0, 1, 3, '/weixinrecord'),
        (132, '数据库', 123, 'index.php?m=tool&c=database', 1, '0', 1, 0, 1, 3, '/database'),
        (133, '数据采集', 123, 'index.php?m=tool&c=dataCollection', 1, '0', 2, 0, 1, 3, '/dataCollection'),
        (134, '数据导入', 123, 'index.php?m=tool&c=dataExcel', 1, '0', 3, 0, 1, 3, '/dataExcel'),
        (135, '数据调用', 123, 'index.php?m=tool&c=dataCall', 1, '0', 4, 0, 1, 3, '/dataCall'),
        (142, '网站设置', 121, 'index.php?m=system&c=set_config', 1, '0', 1, 0, 1, 3, '/set'),
        (143, '模块设置', 121, 'index.php?m=system&c=set_module', 1, '0', 2, 0, 1, 3, '/moduleset'),
        (144, '页面设置', 121, 'index.php?m=system&c=set_web_config', 1, '0', 3, 0, 1, 3, '/yemainset'),
        (145, '导航设置', 121, 'index.php?m=system&c=set_navigation', 1, '0', 4, 0, 1, 3, '/navigation'),
        (187, '短信设置', 186, 'index.php?m=tool&c=messageset', 1, '0', 1, 0, 1, 3, '/messageset'),
        (148, '支付设置', 121, 'index.php?m=system&c=set_payset', 1, '0', 7, 0, 1, 3, '/payset'),
        (149, 'SEO设置', 121, 'index.php?m=system&c=set_seo', 1, '0', 8, 0, 1, 3, '/seoset'),
        (151, '积分设置', 121, 'index.php?m=system&c=set_integral', 1, '0', 9, 0, 1, 3, '/jifenset'),
        (152, '注册设置', 121, 'index.php?m=system&c=set_regset', 1, '0', 10, 0, 1, 3, '/regset'),
        (153, '网站地图', 121, 'index.php?m=system&c=set_navmap', 1, '0', 11, 0, 1, 3, 'navmap'),
        (154, '添加/修改', 59, 'index.php?m=neirong&c=announcement&a=add', 1, '0', 2, 0, 1, 4, '/addgg'),
        (156, '微聘', 1, '', 1, 'nav_once', 6, 0, 1, 2, ''),
        (158, '登录', 4, '', 1, 'nav_login', 4, 0, 1, 2, ''),
        (159, '快捷登录', 158, 'index.php?m=tool&c=fastlogin', 1, '0', 1, 0, 1, 3, '/fastlogin'),
        (160, '登录整合', 158, 'index.php?m=tool&c=admin_uc', 1, '0', 2, 0, 1, 3, '/admin_uc'),
        (161, '财务', 3, '', 0, 'nav_cw', 0, 0, 1, 2, ''),
        (162, '充值订单', 161, 'index.php?m=yunying&c=finance_company_order', 1, '0', 1, 0, 1, 3, '/chongzhidd'),
        (163, '消费日志', 161, 'index.php?m=yunying&c=finance_company_pay', 1, '0', 3, 0, 1, 3, '/xiaofei'),
        (164, '后台充值', 161, 'index.php?m=yunying&c=finance_recharge', 1, '0', 6, 0, 1, 3, '/houtaicz'),
        (165, '发票管理', 161, 'index.php?m=yunying&c=finance_invoice', 1, '0', 2, 0, 1, 3, '/fapiaoguan'),
        (166, '优惠券', 3, '', 1, 'nav_reward', 10, 0, 1, 2, ''),
        (167, '赏金提现', 161, 'index.php?m=yunying&c=finance_withdraw', 1, '0', 5, 0, 1, 3, '/shangjintx'),
        (168, '数据看板', 123, 'index.php?m=tool&c=dataBoard', 1, '0', 5, 0, 1, 3, '/dataBoard'),
        (169, '回收站', 123, 'index.php?m=tool&c=dataRecycle', 1, '0', 6, 0, 1, 3, '/dataRecycle'),
        (170, '分站', 5, '', 1, 'nav_fz', 8, 0, 1, 2, ''),
        (171, '分站管理', 170, 'index.php?m=system&c=domain_list', 1, '0', 1, 0, 1, 3, '/domainList'),
        (172, '分站设置', 171, 'index.php?m=system&c=domain_list&a=configSave', 1, '0', 1, 0, 1, 4, ''),
        (173, '分站管理员', 170, 'index.php?m=system&c=domain_group', 1, '0', 2, 0, 1, 3, '/domainAdminList'),
        (174, '计划任务', 121, 'index.php?m=system&c=set_cron', 1, '0', 11, 0, 1, 3, '/cron'),
        (175, '预警设置', 121, 'index.php?m=system&c=warning', 1, '0', 12, 0, 1, 3, '/warning'),
        (176, '举报', 3, '', 1, 'nav_jb', 6, 0, 1, 2, ''),
        (177, '营销', 3, '', 1, 'nav_yx', 7, 0, 1, 2, ''),
        (178, '举报职位', 176, 'index.php?m=yunying&c=report_job', 1, '0', 1, 0, 1, 3, '/reportjob'),
        (179, '举报简历', 176, 'index.php?m=yunying&c=report_resume', 1, '0', 2, 0, 1, 3, '/reportresume'),
        (180, '举报问答', 176, 'index.php?m=yunying&c=report_ask', 1, '0', 3, 0, 1, 3, '/reportask'),
        (181, '举报顾问', 176, 'index.php?m=yunying&c=report_advise', 1, '0', 5, 0, 1, 3, '/reportadvise'),
        (182, '推广营稍', 177, 'index.php?m=yunying&c=yingxiao_tuiguang', 1, '0', 1, 0, 1, 3, '/yingxiao_tuiguang'),
        (183, '订阅管理', 177, 'index.php?m=yunying&c=yingxiao_subscribe', 1, '', 2, 0, 1, 3, '/yingxiao_subscribe'),
        (184, '账号申诉记录', 402, 'index.php?m=user&c=admin_appeal', 1, '', 6, 0, 1, 4, ''),
        (185, '邮件', 4, '', 1, 'nav_email', 6, 0, 1, 2, ''),
        (186, '短信', 4, '', 1, 'nav_msg', 3, 0, 1, 2, ''),
        (188, '短信记录', 186, 'index.php?m=tool&c=messagelog', 1, '0', 3, 0, 1, 3, '/messagelog'),
        (189, '邮件设置', 185, 'index.php?m=tool&c=emailset', 1, '0', 2, 0, 1, 3, '/emailset'),
        (190, '邮件记录', 185, 'index.php?m=tool&c=emaillog', 1, '0', 3, 0, 1, 3, '/emaillog'),
        (193, '关键字管理', 121, 'index.php?m=system&c=set_guanjianci', 1, '', 11, 0, 1, 3, '/guanjianci'),
        (194, '友情链接', 121, 'index.php?m=system&c=set_friendlink', 1, '', 12, 0, 1, 3, '/friendlink'),
        (195, '单页面', 5, '', 1, 'nav_desc', 7, 0, 1, 2, ''),
        (196, '单页面管理', 195, 'index.php?m=system&c=singlepage', 1, '0', 0, 0, 1, 3, '/singlepage'),
        (198, '信息', 5, '', 1, 'nav_xx', 9, 0, 1, 2, ''),
        (204, '新闻属性', 10, 'index.php?m=neirong&c=news&a=type', 1, '0', 5, 0, 1, 3, '/newssx'),
        (205, '消费统计', 161, 'index.php?m=yunying&c=finance_statis', 1, '0', 4, 0, 1, 3, '/shouzhitj'),
        (206, '添加/修改', 76, 'index.php?m=yunying&c=shop_reward&a=add', 1, '0', 3, 0, 1, 4, ''),
        (212, 'CRM', 0, '', 1, '', 6, 0, 1, 1, ''),
        (213, '业务员', 212, '', 0, 'nav_ywy', 1, 0, 1, 2, ''),
        (216, '工作台', 213, 'index.php?m=crm&c=crm_index', 3, '', 1, 0, 1, 3, '/jobtai'),
        (217, '待办任务', 213, 'index.php?m=crm&c=taskYwy', 1, '', 3, 0, 1, 3, '/taskList'),
        (218, '联系跟进', 213, 'index.php?m=crm&c=followLogYwy', 1, '', 4, 0, 1, 3, '/followLog'),
        (219, '客户公海', 213, 'index.php?m=crm&c=crm_customer', 1, '', 5, 0, 1, 3, '/kehugh'),
        (220, '我的客户', 213, 'index.php?m=crm&c=crm_my_customer', 1, '', 2, 0, 1, 3, '/mykehu'),
        (225, '工作日志', 213, 'index.php?m=crm&c=workLogYwy', 1, '', 7, 0, 1, 3, '/workLog'),
        (226, '管理员', 212, '', 1, 'nav_gly', 4, 0, 1, 2, ''),
        (227, '分类管理', 226, 'index.php?m=crm&c=crmClass', 1, '', 11, 0, 1, 3, '/crmClass'),
        (228, '查账助手', 226, 'index.php?m=crm&c=auditAss', 1, '', 9, 0, 1, 3, '/crmAudit'),
        (231, 'CRM管理', 226, 'index.php?m=crm&c=salesman', 1, '', 2, 0, 1, 3, '/crmManage'),
        (337, '校园分类', 73, 'index.php?m=system&c=category_schoolclass', 1, '0', 5, 0, 1, 3, '/schoolclass'),
        (339, '校园', 1, '', 1, 'nav_school', 7, 0, 1, 2, ''),
        (340, '院校管理', 339, 'index.php?m=user&c=school_academy', 1, '0', 0, 0, 1, 3, '/school'),
        (342, '宣讲会', 339, 'index.php?m=user&c=school_xjh', 1, '0', 0, 0, 1, 3, '/schoolxjh'),
        (341, '应届生职位', 340, 'index.php?m=user&c=school_graduate', 1, '0', 0, 0, 1, 4, ''),
        (343, '专题', 3, '', 1, 'nav_sp', 4, 0, 1, 2, ''),
        (344, '添加/修改', 49, 'index.php?m=yunying&c=special_special&a=add', 1, '0', 2, 0, 1, 4, ''),
        (349, '简历库', 20, 'index.php?m=user&c=admin_lt_talent', 1, '', 9, 0, 1, 4, ''),
        (350, '聊天', 4, '', 0, 'nav_chat', 3, 0, 1, 2, ''),
        (351, '聊天设置', 350, 'index.php?m=tool&c=chatset', 1, '', 1, 0, 1, 3, '/chatset'),
        (352, '聊天记录', 350, 'index.php?m=tool&c=chatlog', 1, '', 2, 0, 1, 3, '/chatlog'),
        (353, '举报宣讲会', 176, 'index.php?m=yunying&c=report_xjh', 1, '', 6, 0, 1, 3, '/reportxjh'),
        (359, '问答设置', 15, 'index.php?m=neirong&c=question&a=config', 1, '0', 3, 0, 1, 3, '/question_config'),
        (361, '测评日志', 11, 'index.php?m=neirong&c=evaluate&a=record', 1, '', 6, 0, 1, 3, '/yhjl'),
        (363, '移动端', 4, '', 0, 'nav_app', 3, 0, 1, 2, ''),
        (364, 'APP配置', 363, 'index.php?m=tool&c=app_set', 1, '', 1, 0, 1, 3, '/appset'),
        (365, 'APP推送记录', 363, 'index.php?m=tool&c=app_push', 1, '', 2, 0, 1, 3, '/pushlog'),
        (366, '模板DIY', 363, 'index.php?m=tool&c=diy', 1, '', 4, 0, 1, 3, '/diy'),
        (367, '微信小程序', 363, 'index.php?m=tool&c=xcxset', 1, '', 3, 0, 1, 3, '/xcxset'),
        (382, 'OSS设置', 123, 'index.php?m=tool&c=dataOss', 1, '0', 7, 0, 1, 3, '/dataOss'),
        (387, '我的业绩', 213, 'index.php?m=crm&c=crm_my_performance', 1, '', 6, 0, 1, 3, '/myyeji'),
        (389, '订单管理', 226, 'index.php?m=crm&c=order', 1, '', 5, 0, 1, 3, '/crmOrder'),
        (390, '外出申请', 213, 'index.php?m=crm&c=outLogYwy', 1, '', 8, 0, 1, 3, '/outLog'),
        (391, '外出审批', 226, 'index.php?m=crm&c=outLog', 1, '', 6, 0, 1, 3, '/allOutLog'),
        (392, '工作日志', 226, 'index.php?m=crm&c=workLog', 1, '', 8, 0, 1, 3, '/allWorkLog'),
        (393, '供求', 1, '', 0, 'nav_demand', 8, 0, 1, 2, ''),
        (394, '供求用户', 393, 'index.php?m=user&c=supply_user', 1, '0', 1, 0, 1, 3, '/supplyuser'),
        (395, '待办任务', 226, 'index.php?m=crm&c=task', 1, '', 7, 0, 1, 3, '/allTaskList'),
        (396, '跟进记录', 226, 'index.php?m=crm&c=followLog', 1, '', 3, 0, 1, 3, '/allFollowLog'),
        (397, '供求设置', 393, 'index.php?m=user&c=supply_set', 1, '', 5, 0, 1, 3, '/supplyset'),
        (398, '项目任务', 393, 'index.php?m=user&c=supply_task', 1, '', 2, 0, 1, 3, '/supplytask'),
        (399, '任务浏览', 393, 'index.php?m=user&c=supply_brower', 1, '', 3, 0, 1, 3, '/supplybrower'),
        (400, '图片管理', 393, 'index.php?m=user&c=supply_pic', 1, '', 4, 0, 1, 3, '/supplypic'),
        (401, '会员', 1, '', 0, 'nav_member', 0, 0, 1, 2, ''),
        (402, '会员列表', 401, 'index.php?m=user&c=admin_member', 1, '', 1, 0, 1, 3, '/usercrm'),
        (403, '登录日志', 401, 'index.php?m=user&c=admin_loginlog', 1, '', 4, 0, 1, 3, '/userloginlog'),
        (404, '百度百聘', 103, 'index.php?m=tool&c=generate_baipin&a=add', 1, '', 9, 0, 1, 3, '/generate_baipin'),
        (405, '删除', 402, 'index.php?m=user&c=admin_member&a=del', 1, '', 1, 0, 1, 4, ''),
        (406, '修改', 402, 'index.php?m=user&c=admin_member&a=editSave', 1, '', 2, 0, 1, 4, ''),
        (407, '重置密码', 402, 'index.php?m=user&c=admin_member&a=reset_pw', 1, '', 3, 0, 1, 4, ''),
        (408, '删除日志', 403, 'index.php?m=user&c=admin_loginlog&a=dellog', 1, '', 0, 0, 1, 4, ''),
        (409, '企业删除', 16, 'index.php?m=user&c=company&a=del', 1, '', 0, 0, 1, 4, ''),
        (410, '企业修改', 16, 'index.php?m=user&c=company&a=edit', 1, '', 0, 0, 1, 4, ''),
        (412, '修改套餐', 16, 'index.php?m=user&c=company&a=uprating', 1, '', 0, 0, 1, 4, ''),
        (413, '批量导出', 16, 'index.php?m=user&c=company&a=xls', 1, '', 0, 0, 1, 4, ''),
        (414, '企业认证', 16, 'index.php?m=user&c=company&a=comcert', 1, '', 0, 0, 1, 4, ''),
        (415, '职位删除', 40, 'index.php?m=user&c=company_job&a=del', 1, '', 1, 0, 1, 4, ''),
        (416, '职位添加/修改', 40, 'index.php?m=user&c=company_job&a=add', 1, '', 2, 0, 1, 4, ''),
        (417, '职位置顶', 40, 'index.php?m=user&c=company_job&a=xuanshang', 1, '', 4, 0, 1, 4, ''),
        (418, '职位推荐', 40, 'index.php?m=user&c=company_job&a=recommend', 1, '', 5, 0, 1, 4, ''),
        (419, '职位紧急', 40, 'index.php?m=user&c=company_job&a=urgent', 1, '', 6, 0, 1, 4, ''),
        (420, '批量导出', 40, 'index.php?m=user&c=company_job&a=xls', 1, '', 7, 0, 1, 4, ''),
        (421, '名企修改', 16, 'index.php?m=user&c=hotjob&a=save', 1, '', 0, 0, 0, 4, ''),
        (422, '名企删除', 16, 'index.php?m=user&c=hotjob&a=del', 1, '', 0, 0, 0, 4, ''),
        (423, '兼职删除', 40, 'index.php?m=user&c=partjob&a=del', 1, '', 16, 0, 0, 4, ''),
        (425, '删除分享', 40, 'index.php?m=user&c=jobpack&a=delshare', 1, '', 17, 0, 0, 4, ''),
        (426, '删除赏金', 40, 'index.php?m=user&c=jobpack&a=delreward', 1, '', 18, 0, 0, 4, ''),
        (429, '面试模板', 46, 'index.php?m=user&c=company_interview', 1, '', 10, 0, 1, 4, ''),
        (430, '认证删除', 47, 'index.php?m=user&c=company_cert&c=del', 1, '', 0, 0, 1, 4, ''),
        (432, '图片审核', 47, 'index.php?m=user&c=company_pic', 1, '', 0, 0, 0, 4, ''),
        (431, '图片删除', 47, 'index.php?m=user&c=company_pic&a=del', 1, '', 0, 0, 0, 4, ''),
        (433, '删除职位申请', 50, 'index.php?m=user&c=company_comlog&a=deluseridjob', 1, '', 0, 0, 1, 4, ''),
        (434, '删除面试邀请', 50, 'index.php?m=user&c=company_comlog&a=deluseridmsg', 1, '', 0, 0, 1, 4, ''),
        (435, '删除职位浏览', 50, 'index.php?m=user&c=company_comlog&a=dellookjob', 1, '', 0, 0, 1, 4, ''),
        (436, '删除兼职报名', 50, 'index.php?m=user&c=company_comlog&a=delpartapply', 1, '', 0, 0, 1, 4, ''),
        (437, '删除职位收藏', 50, 'index.php?m=user&c=users_userlog&a=delfavjob', 1, '', 0, 0, 1, 4, ''),
        (438, '套餐设置', 52, 'index.php?m=user&c=company_comrating&a=saveclass', 1, '', 0, 0, 1, 4, ''),
        (439, '增值包设置', 52, 'index.php?m=user&c=company_comrating&a=saves', 1, '', 0, 0, 1, 4, ''),
        (440, '删除', 54, 'index.php?m=neirong&c=news&a=delnews', 1, '', 0, 0, 1, 4, ''),
        (441, '删除', 56, 'index.php?m=neirong&c=news&a=delgroup', 1, '', 0, 0, 1, 4, ''),
        (442, '转移类别', 56, 'index.php?m=neirong&c=news&a=changeSon', 1, '', 0, 0, 1, 4, ''),
        (443, '删除', 69, 'index.php?m=neirong&c=evaluate&a=delevaluate', 1, '', 0, 0, 1, 4, ''),
        (444, '删除', 71, 'index.php?m=neirong&c=evaluate&c=delgroup', 1, '', 0, 0, 1, 4, ''),
        (445, '删除', 72, 'index.php?m=neirong&c=evaluate&a=delmsg', 1, '', 0, 0, 1, 4, ''),
        (446, '删除记录', 361, 'index.php?m=neirong&c=evaluate&a=delevaluatelog', 1, '', 0, 0, 1, 4, ''),
        (447, '删除', 61, 'index.php?m=neirong&c=zhaopinhui&a=del', 1, '', 4, 0, 1, 4, ''),
        (448, '删除参会企业', 61, 'index.php?m=neirong&c=zhaopinhui&a=delcom', 1, '', 5, 0, 0, 4, ''),
        (449, '删除场地', 61, 'index.php?m=neirong&c=zph_space&a=del', 1, '', 6, 0, 0, 4, ''),
        (450, '展位新增', 61, 'index.php?m=neirong&c=zph_space&a=add', 1, '', 7, 0, 0, 4, ''),
        (451, '展位修改', 61, 'index.php?m=neirong&c=zph_space&a=ajax', 1, '', 8, 0, 0, 4, ''),
        (455, '删除', 59, 'index.php?m=neirong&c=announcement&a=del', 1, '', 0, 0, 1, 4, ''),
        (456, '修改问答', 66, 'index.php?m=neirong&c=question&a=save', 1, '', 0, 0, 1, 4, ''),
        (457, '删除问答', 66, 'index.php?m=neirong&c=question&a=del', 1, '', 0, 0, 1, 4, ''),
        (458, '推荐问答', 66, 'index.php?m=neirong&c=question&a=recommend', 1, '', 0, 0, 1, 4, ''),
        (459, '修改问答类别', 97, 'index.php?m=neirong&c=question_class&a=add', 1, '', 0, 0, 1, 4, ''),
        (460, '删除问答类别', 97, 'index.php?m=neirong&c=question_class&a=del', 1, '', 0, 0, 1, 4, ''),
        (464, '批量生成', 81, 'index.php?m=yunying&c=coupon_prepaid&a=add', 1, '', 0, 0, 1, 4, ''),
        (465, '修改', 81, 'index.php?m=yunying&c=coupon_prepaid&a=upcard', 1, '', 0, 0, 1, 4, ''),
        (466, '删除', 81, 'index.php?m=yunying&c=coupon_prepaid&a=del', 1, '', 0, 0, 1, 4, ''),
        (467, '删除', 165, 'index.php?m=yunying&c=finance_invoice&a=del', 1, '', 0, 0, 1, 4, ''),
        (468, '删除', 163, 'index.php?m=yunying&c=finance_company_pay&a=del', 1, '', 0, 0, 1, 4, ''),
        (469, '删除', 162, 'index.php?m=yunying&c=finance_company_order&a=del', 1, '', 0, 0, 1, 4, ''),
        (470, '确认付款', 162, 'index.php?m=yunying&c=finance_company_order&a=setpay', 1, '', 0, 0, 1, 4, ''),
        (471, '修改金额', 162, 'index.php?m=yunying&c=finance_company_order&a=save', 1, '', 0, 0, 1, 4, ''),
        (472, '导出', 162, 'index.php?m=yunying&c=finance_company_order&a=xls', 1, '', 0, 0, 1, 4, ''),
        (473, '删除', 83, 'index.php?m=yunying&c=ad&a=del', 1, '', 0, 0, 1, 4, ''),
        (474, '删除', 86, 'index.php?m=yunying&c=ad_class&a=del', 1, '', 0, 0, 1, 4, ''),
        (475, '删除', 76, 'index.php?m=yunying&c=shop_reward&a=del', 1, '', 0, 0, 1, 4, ''),
        (476, '删除', 77, 'index.php?m=yunying&c=shop_list&a=del', 1, '', 0, 0, 1, 4, ''),
        (477, '修改', 82, 'index.php?m=yunying&c=shop_list&a=ajax', 1, '', 0, 0, 1, 4, ''),
        (478, '删除', 82, 'index.php?m=yunying&c=shop_class&a=del', 1, '', 0, 0, 1, 4, ''),
        (479, '删除', 49, 'index.php?m=yunying&c=special_special&a=del', 1, '', 0, 0, 1, 4, ''),
        (480, '删除简历', 179, 'index.php?m=yunying&c=report_resume&a=delresume', 1, '', 0, 0, 1, 4, ''),
        (481, '恢复数据', 169, 'index.php?m=tool&c=dataRecycle&a=recover', 1, '', 0, 0, 1, 4, ''),
        (482, '清空/删除', 169, 'index.php?m=tool&c=dataRecycle&a=delRecycle', 1, '', 0, 0, 1, 4, ''),
        (483, '清除日志', 128, 'index.php?m=tool&c=weixinrecord&a=clearwx', 1, '', 0, 0, 1, 4, ''),
        (484, '取消绑定', 128, 'index.php?m=tool&c=weixinrecord&a=deluser', 1, '', 0, 0, 0, 4, ''),
        (485, '删除聊天记录', 352, 'index.php?m=tool&c=chatlog&a=del', 1, '', 0, 0, 1, 4, ''),
        (486, '清理聊天记录', 352, 'index.php?m=tool&c=chatlog&a=clean', 1, '', 0, 0, 1, 4, ''),
        (487, '修改', 18, 'index.php?m=user&c=admin_lt_member&a=edit', 1, '', 0, 0, 1, 4, ''),
        (488, '删除猎头', 18, 'index.php?m=user&c=admin_lt_member&a=del', 1, '', 0, 0, 1, 4, ''),
        (489, '删除解绑日志', 18, 'index.php?m=user&c=admin_lt_member&a=delwflog', 1, '', 0, 0, 1, 4, ''),
        (490, '删除日志', 18, 'index.php?m=user&c=admin_lt_member&a=memberlogdel', 1, '', 0, 0, 1, 4, ''),
        (491, '删除登录日志', 18, 'index.php?m=user&c=admin_lt_member&a=loginLogDel', 1, '', 0, 0, 1, 4, ''),
        (492, '删除', 19, 'index.php?m=user&c=admin_lt_job&a=del', 1, '', 2, 0, 1, 4, ''),
        (493, '推荐', 19, 'index.php?m=user&c=admin_lt_job&a=recommend', 1, '', 1, 0, 1, 4, ''),
        (494, '删除', 20, 'index.php?m=user&c=admin_height_user&a=del', 1, '', 0, 0, 1, 4, ''),
        (495, '简历推荐', 20, 'index.php?m=user&c=admin_height_user&a=recommend', 1, '', 0, 0, 1, 4, ''),
        (496, '认证删除', 21, 'index.php?m=user&c=admin_lt_cert&a=del', 1, '', 0, 0, 1, 4, ''),
        (497, '删除悬赏', 19, 'index.php?m=user&c=admin_lt_xuanshang&a=del', 1, '', 4, 0, 1, 4, ''),
        (498, '套餐删除', 23, 'index.php?m=user&c=admin_lt_rating&a=delrating', 1, '', 0, 0, 1, 4, ''),
        (499, '修改', 23, 'index.php?m=user&c=admin_lt_rating&a=saveclass', 1, '', 0, 0, 1, 4, ''),
        (500, '增值包类型删除', 23, 'index.php?m=user&c=admin_lt_rating&a=dels', 1, '', 0, 0, 1, 4, ''),
        (501, '增值包修改', 23, 'index.php?m=user&c=admin_lt_rating&a=saves', 1, '', 0, 0, 1, 4, ''),
        (502, '增值包删除', 23, 'index.php?m=user&c=admin_lt_rating&a=delt', 1, '', 0, 0, 1, 4, ''),
        (504, '图片删除', 21, 'index.php?m=user&c=admin_lt_pic&a=del', 1, '', 0, 0, 0, 4, ''),
        (503, '图片审核', 21, 'index.php?m=user&c=admin_lt_pic', 1, '', 0, 0, 0, 4, ''),
        (505, '简历库删除', 20, 'index.php?m=user&c=admin_lt_talent&a=del', 1, '', 0, 0, 0, 4, ''),
        (506, '修改', 25, 'index.php?m=user&c=peixun_member&a=edit', 1, '', 0, 0, 1, 4, ''),
        (508, '删除', 25, 'index.php?m=user&c=peixun_member&a=del', 1, '', 0, 0, 1, 4, ''),
        (509, '删除日志', 25, 'index.php?m=user&c=peixun_member&a=logDel', 1, '', 0, 0, 1, 4, ''),
        (510, '修改', 26, 'index.php?m=user&c=peixun_teacher&a=save', 1, '', 0, 0, 1, 4, ''),
        (511, '删除', 26, 'index.php?m=user&c=peixun_teacher&a=del', 1, '', 0, 0, 1, 4, ''),
        (512, '推荐', 26, 'index.php?m=user&c=peixun_teacher&a=rec', 1, '', 0, 0, 1, 4, ''),
        (513, '修改', 27, 'index.php?m=user&c=peixun_subject&a=save', 1, '', 0, 0, 1, 4, ''),
        (514, '删除', 27, 'index.php?m=user&c=peixun_subject&a=del', 1, '', 0, 0, 1, 4, ''),
        (515, '推荐', 27, 'index.php?m=user&c=peixun_subject&a=rec', 1, '', 0, 0, 1, 4, ''),
        (516, '删除', 28, 'index.php?m=user&c=peixun_renzheng&a=del', 1, '', 0, 0, 1, 4, ''),
        (517, '课程报名删除', 27, 'index.php?m=user&c=peixun_subjectbm&a=del', 1, '', 0, 0, 0, 4, ''),
        (518, '课程报名确认付款', 27, 'index.php?m=user&c=peixun_subjectbm&a=setpay', 1, '', 0, 0, 0, 4, ''),
        (519, '删除', 30, 'index.php?m=user&c=peixun_news&a=del', 1, '', 0, 0, 1, 4, ''),
        (520, '图片修改', 28, 'index.php?m=user&c=peixun_renzheng&a=uploadsave', 1, '', 0, 0, 0, 4, ''),
        (521, '图片删除', 28, 'index.php?m=user&c=peixun_renzheng&a=delPic', 1, '', 0, 0, 0, 4, ''),
        (522, '审核图片', 28, 'index.php?m=user&c=peixun_renzheng&a=status', 1, '', 0, 0, 0, 4, ''),
        (523, '审核环境', 28, 'index.php?m=user&c=peixun_renzheng&a=showStatus', 1, '', 0, 0, 0, 4, ''),
        (524, '审核横幅', 28, 'index.php?m=user&c=peixun_renzheng&a=bannerStatus', 1, '', 0, 0, 0, 4, ''),
        (525, '课程咨询删除', 27, 'index.php?m=user&c=peixun_subjectzx&a=del', 1, '', 0, 0, 0, 4, ''),
        (526, '修改', 48, 'index.php?m=user&c=weipin_once&a=save', 1, '', 0, 0, 1, 4, ''),
        (527, '删除', 48, 'index.php?m=user&c=weipin_once&a=del', 1, '', 0, 0, 1, 4, ''),
        (528, '批量延期', 48, 'index.php?m=user&c=weipin_once&a=ctime', 1, '', 0, 0, 1, 4, ''),
        (529, '店铺设置', 48, 'index.php?m=user&c=weipin_once&a=onceset', 1, '', 0, 0, 1, 4, ''),
        (530, '修改', 36, 'index.php?m=user&c=weipin_tiny&a=save', 1, '', 0, 0, 1, 4, ''),
        (531, '删除', 36, 'index.php?m=user&c=weipin_tiny&a=del', 1, '', 0, 0, 1, 4, ''),
        (532, '普工设置', 36, 'index.php?m=user&c=weipin_tiny&a=set', 1, '', 0, 0, 1, 4, ''),
        (533, '修改', 342, 'index.php?m=user&c=school_xjh&a=save', 1, '', 0, 0, 1, 4, ''),
        (534, '删除', 342, 'index.php?m=user&c=school_xjh&a=del', 1, '', 0, 0, 1, 4, ''),
        (535, '院校修改', 340, 'index.php?m=user&c=school_academy&a=saveacademy', 1, '', 7, 0, 1, 4, ''),
        (536, '院校删除', 340, 'index.php?m=user&c=school_academy&a=del', 1, '', 8, 0, 1, 4, ''),
        (537, '职位修改', 340, 'index.php?m=user&c=school_graduate&a=show', 1, '', 1, 0, 0, 4, ''),
        (538, '职位删除', 340, 'index.php?m=user&c=school_graduate&a=del', 1, '', 2, 0, 0, 4, ''),
        (539, '职位推荐', 340, 'index.php?m=user&c=school_graduate&a=recommend', 1, '', 3, 0, 0, 4, ''),
        (540, '职位紧急', 340, 'index.php?m=user&c=school_graduate&a=urgent', 1, '', 4, 0, 0, 4, ''),
        (541, '批量导出', 340, 'index.php?m=user&c=school_graduate&a=xls', 1, '', 5, 0, 0, 4, ''),
        (542, '转移类别', 340, 'index.php?m=user&c=school_graduate&a=saveclass', 1, '', 6, 0, 0, 4, ''),
        (544, '修改用户', 394, 'index.php?m=user&c=supply_user&a=edit', 1, '', 0, 0, 1, 4, ''),
        (545, '删除用户', 394, 'index.php?m=user&c=supply_user&a=del', 1, '', 0, 0, 1, 4, ''),
        (550, '修改项目', 398, 'index.php?m=user&c=supply_task&a=info', 1, '', 0, 0, 1, 4, ''),
        (551, '删除项目', 398, 'index.php?m=user&c=supply_task&a=del', 1, '', 0, 0, 1, 4, ''),
        (552, '删除记录', 399, 'index.php?m=user&c=supply_brower&a=del', 1, '', 0, 0, 1, 4, ''),
        (553, '修改图片', 400, 'index.php?m=user&c=supply_pic&a=savePhoto', 1, '', 0, 0, 1, 4, ''),
        (554, '审核图片', 400, 'index.php?m=user&c=supply_pic&a=status', 1, '', 0, 0, 1, 4, ''),
        (555, '删除图片', 400, 'index.php?m=user&c=supply_pic&a=del', 1, '', 0, 0, 1, 4, ''),
        (556, '删除', 227, 'index.php?m=crm&c=crmClass&a=delClass', 1, '', 0, 0, 1, 4, ''),
        (557, '修改', 227, 'index.php?m=crm&c=crmClass&a=ajaxData', 1, '', 0, 0, 1, 4, ''),
        (558, '分配客户', 231, 'index.php?m=crm&c=salesman&a=assignCustomer', 1, '', 0, 0, 1, 4, ''),
        (559, '设为离职', 231, 'index.php?m=crm&c=salesman&c=setCrmStatus', 1, '', 0, 0, 1, 4, ''),
        (560, '修改', 105, 'index.php?m=system&c=role_user&a=save', 1, '', 0, 0, 0, 4, ''),
        (561, '转移客户', 231, 'index.php?m=crm&c=salesman&a=shiftCustomer', 1, '', 0, 0, 1, 4, ''),
        (563, '删除日志', 392, 'index.php?m=crm&c=workLog&a=delWorkLog', 1, '', 0, 0, 1, 4, ''),
        (564, '任务设置', 395, 'index.php?m=crm&c=task&a=setStatus', 1, '', 0, 0, 1, 4, ''),
        (565, '外出审批', 391, 'index.php?m=crm&c=outLog&a=setStatus', 1, '', 0, 0, 1, 4, ''),
        (566, '审批删除', 391, 'index.php?m=crm&c=outLog&a=delOutLog', 1, '', 0, 0, 1, 4, ''),
        (567, '跟进删除', 396, 'index.php?m=crm&c=followLog&a=delFollowLog', 1, '', 0, 0, 1, 4, ''),
        (570, '反馈', 217, 'index.php?m=crm&c=taskYwy&a=setStatus', 1, '', 0, 0, 1, 4, ''),
        (572, '删除', 218, 'index.php?m=crm&c=followLogYwy&a=delFollowLog', 1, '', 0, 0, 1, 4, ''),
        (573, '删除', 225, 'index.php?m=crm&c=workLogYwy&a=delWorkLog', 1, '', 0, 0, 1, 4, ''),
        (574, '申请取消', 390, 'index.php?m=crm&c=outLogYwy&a=delOutLog', 1, '', 0, 0, 1, 4, ''),
        (575, '修改', 17, 'index.php?m=user&c=users_member&a=editSave', 1, '', 0, 0, 1, 4, ''),
        (576, '删除', 17, 'index.php?m=user&c=users_member&a=del', 1, '', 0, 0, 1, 4, ''),
        (577, '日志删除', 17, 'index.php?m=user&c=users_member&a=logDel', 1, '', 0, 0, 1, 4, ''),
        (578, '简历刷新', 33, 'index.php?m=user&c=users_resume&a=refresh', 1, '', 0, 0, 1, 4, ''),
        (579, '简历推荐', 33, 'index.php?m=user&c=users_resume&a=rec', 1, '', 0, 0, 1, 4, ''),
        (580, '简历置顶', 33, 'index.php?m=user&c=users_resume&a=top', 1, '', 0, 0, 1, 4, ''),
        (581, '简历备注', 33, 'index.php?m=user&c=users_resume&a=label', 1, '', 0, 0, 1, 4, ''),
        (582, '简历删除', 33, 'index.php?m=user&c=users_resume&a=delResume', 1, '', 0, 0, 1, 4, ''),
        (583, '简历导出', 33, 'index.php?m=user&c=users_resume&a=xls', 1, '', 0, 0, 1, 4, ''),
        (584, '删除', 34, 'index.php?m=user&c=users_usercert&a=del', 1, '', 0, 0, 1, 4, ''),
        (585, '删除', 37, 'index.php?m=user&c=users_msg&a=del', 1, '', 0, 0, 1, 4, ''),
        (586, '删除下载记录', 38, 'index.php?m=user&c=users_userlog&a=deldown', 1, '', 0, 0, 1, 4, ''),
        (587, '删除推送记录', 38, 'index.php?m=user&c=users_userlog&a=deltrust', 1, '', 0, 0, 1, 4, ''),
        (588, '删除浏览记录', 38, 'index.php?m=user&c=users_userlog&a=dellook', 1, '', 0, 0, 1, 4, ''),
        (589, '头像/作品审核', 34, 'index.php?m=user&c=users_pic', 1, '', 0, 0, 0, 4, ''),
        (590, '头像删除', 34, 'index.php?m=user&c=users_pic&a=delPhoto', 1, '', 0, 0, 0, 4, ''),
        (592, '作品删除', 34, 'index.php?m=user&c=users_pic&a=delShow', 1, '', 0, 0, 0, 4, ''),
        (594, '好友助力', 177, 'index.php?m=yunying&c=yingxiao_friendhelp', 1, '', 8, 0, 1, 3, '/yingxiao_friendhelp'),
        (595, '商品设置', 74, 'index.php?m=yunying&c=shop_set', 1, '', 8, 0, 1, 3, '/shopset'),
        (596, '子账户', 402, 'index.php?m=user&c=admin_zzh', 1, '', 4, 0, 1, 4, ''),
        (601, '转换身份申请', 402, 'index.php?m=user&c=admin_userchange', 1, '', 7, 0, 1, 4, ''),
        (602, '自我介绍', 73, 'index.php?m=system&c=category_introduce_class', 1, '0', 10, 0, 1, 3, '/introduce_class'),
        (801, 'CRM设置', 226, 'index.php?m=crm&c=config', 1, '', 12, 0, 1, 3, '/crmConfig'),
        (805, '订单记录', 213, 'index.php?m=crm&c=crm_deal', 1, '', 8, 0, 1, 3, '/mydingdan'),
        (802, '网络招聘会', 12, 'index.php?m=neirong&c=zphnet', 1, '', 5, 0, 1, 3, '/netzph'),
        (806, '会员注销记录', 402, 'index.php?m=user&c=admin_member_logout', 1, '', 5, 0, 1, 4, ''),
        (809, '修改', 104, 'index.php?m=system&c=role_myuser&a=savePass', 1, '', 0, 0, 1, 4, ''),
        (810, '解绑微信', 104, 'index.php?m=system&c=role_myuser&a=delAdminWxBind', 1, '', 0, 0, 1, 4, ''),
        (811, '解绑企微', 104, 'index.php?m=system&c=role_myuser&a=delAdminQyUserId', 1, '', 0, 0, 1, 4, ''),
        (814, '错误日志', 198, 'index.php?m=system&c=info_errorlog', 1, '0', 4, 0, 1, 3, '/errorlog'),
        (815, '直播', 2, '', 0, 'nav_xjh', 7, 0, 1, 1, ''),
        (816, '直播管理', 815, 'index.php?m=neirong&c=xjhlive', 1, '', 1, 0, 1, 3, '/livemanage'),
        (817, '预约管理', 815, 'index.php?m=neirong&c=xjhlive_yy', 1, '', 2, 0, 1, 3, '/yymanage'),
        (818, '严禁词', 815, 'index.php?m=neirong&c=xjhlive_keyword', 1, '', 3, 0, 1, 3, '/yjcmanage'),
        (819, '直播设置', 815, 'index.php?m=neirong&c=xjhlive_set', 1, '', 4, 0, 1, 3, '/liveset'),
        (821, '删除评价', 46, 'index.php?m=user&c=company_evaluate&a=del', 1, '', 20, 0, 1, 4, ''),
        (822, '发布工具', 122, 'index.php?m=tool&c=fabutool', 1, '', 9, 0, 1, 1, '/fabutool'),
        (823, '视频面试', 2, '', 0, 'nav_spview', 8, 0, 1, 1, ''),
        (824, '面试管理', 823, 'index.php?m=neirong&c=spview', 1, '', 1, 0, 1, 3, '/msmanage'),
        (827, '微信通知设置', 102, 'index.php?m=system&c=role_notice', 1, '', 7, 0, 1, 1, '/notice'),
        (829, '视频设置', 823, 'index.php?m=neirong&c=spviewset', 1, '', 2, 0, 1, 3, '/spset'),
        (830, '分配顾问', 16, 'index.php?m=user&c=company&a=checkguwen', 1, '', 0, 0, 1, 4, ''),
        (838, '组织架构', 226, 'index.php?m=crm&c=orgStr', 1, '', 10, 0, 1, 3, '/orgStructure'),
        (839, '客户列表', 866, 'index.php?m=crm&c=crm_org_kh', 1, '', 2, 0, 0, 4, '/orgKh'),
        (840, '视频记录', 823, 'index.php?m=neirong&c=splog', 1, '', 9, 0, 1, 3, '/spjl'),
        (843, '拨号记录', 50, 'index.php?m=user&c=company_comlog&a=jobtellog', 1, '', 13, 0, 1, 4, ''),
        (844, '隐私号', 4, '', 1, 'nav_prv', 7, 0, 1, 1, ''),
        (845, '隐私号设置', 844, 'index.php?m=tool&c=privacyset', 1, '', 1, 0, 1, 3, '/privacyset'),
        (846, '隐私号日志', 844, 'index.php?m=tool&c=privacylog', 1, '', 2, 0, 1, 3, '/privacylog'),
        (847, '释放记录', 226, 'index.php?m=crm&c=releaseLog', 1, '', 4, 0, 1, 3, '/releaseLog'),
        (852, '常用语', 350, 'index.php?m=tool&c=chatcyy', 1, '', 4, 0, 1, 3, '/chatcyy'),
        (853, '内容检测', 2, '', 1, 'nav_nc', 7, 0, 1, 1, ''),
        (854, '检测设置', 853, 'index.php?m=neirong&c=concheck_config', 1, '', 1, 0, 1, 3, '/jcset'),
        (855, '检测记录', 853, 'index.php?m=neirong&c=concheck_log', 1, '', 2, 0, 1, 3, '/jcjl'),
        (857, '客勤', 213, 'index.php?m=crm&c=crm_chat', 1, '', 11, 0, 1, 3, '/crm_chat'),
        (860, '举报聊天', 176, 'index.php?m=yunying&c=report_chat', 1, '', 7, 0, 1, 3, '/reportchat'),
        (861, '删除收藏记录', 38, 'index.php?m=user&c=company_comlog&a=deltalentpool', 1, '', 0, 0, 1, 4, ''),
        (862, '海报设置', 177, 'index.php?m=yunying&c=yingxiao_hbconfig', 1, '', 9, 0, 1, 3, '/yingxiao_hbconfig'),
        (863, '数据大屏', 123, 'index.php?m=tool&c=dataView', 1, '', 8, 0, 1, 1, '/dataView'),
        (865, '行为轨迹', 401, 'index.php?m=user&c=admin_weblog', 1, '', 7, 0, 1, 3, '/usergj'),
        (866, '数据看板', 226, 'index.php?m=crm&c=dataBoard', 1, '', 1, 0, 1, 3, '/crmDataBoard'),
        (867, '添加自动回复', 124, 'index.php?m=tool&c=weixinmenu&a=saveZdKeyword', 1, '', 0, 0, 0, 4, ''),
        (868, '删除自动回复', 124, 'index.php?m=tool&c=weixinmenu&a=delkeyword', 1, '', 0, 0, 0, 4, ''),
        (902, '公招', 2, '', 0, 'nav_gz', 9, 0, 1, 1, ''),
        (903, '公招管理', 902, 'index.php?m=neirong&c=gongzhao', 2, '', 1, 0, 1, 3, '/gzmanage'),
        (904, '添加/修改', 903, 'index.php?m=neirong&c=gongzhao&a=add', 1, '', 2, 0, 1, 4, '/addgz'),
        (905, '添加简历', 33, 'index.php?m=user&c=users_resume&a=add', 1, '', 0, 0, 1, 4, ''),
        (906, '站点分配', 17, 'index.php?m=user&c=users_member&a=checksitedid', 1, '', 0, 0, 1, 4, ''),
        (907, '添加会员', 17, 'index.php?m=user&c=users_member&a=add', 1, '', 0, 0, 1, 4, ''),
        (908, '重置密码', 17, 'index.php?m=user&c=users_member&a=reset_pw', 1, '', 0, 0, 1, 4, ''),
        (909, '修改', 33, 'index.php?m=user&c=users_resume&a=editResume', 1, '', 0, 0, 1, 4, ''),
        (910, '审核', 33, 'index.php?m=user&c=users_resume&a=status', 1, '', 0, 0, 1, 4, ''),
        (911, '添加企业', 16, 'index.php?m=user&c=company&a=add', 1, '', 0, 0, 1, 4, ''),
        (914, '重置密码', 16, 'index.php?m=user&c=company&a=reset_companypassword', 1, '', 0, 0, 1, 4, ''),
        (915, '站点分配', 16, 'index.php?m=user&c=company&a=checksitedid', 1, '', 0, 0, 1, 4, ''),
        (916, '审核', 16, 'index.php?m=user&c=company&a=status', 1, '', 0, 0, 1, 4, ''),
        (917, '职位审核', 40, 'index.php?m=user&c=company_job&a=status', 1, '', 3, 0, 1, 4, ''),
        (918, '浏览量修改', 40, 'index.php?m=user&c=company_job&a=upjobhits', 1, '', 8, 0, 1, 4, ''),
        (919, '招聘状态修改', 40, 'index.php?m=user&c=company_job&a=checkstate', 1, '', 9, 0, 1, 4, ''),
        (920, '生成海报', 40, 'index.php?m=user&c=company_job&a=whb', 1, '', 10, 0, 1, 4, ''),
        (921, '推文任务', 40, 'index.php?m=user&c=company_job&a=addTuiWenTask', 1, '', 11, 0, 1, 4, ''),
        (922, '匹配', 40, 'index.php?m=user&c=company_job&a=matching', 1, '', 12, 0, 1, 4, ''),
        (924, '批量刷新', 40, 'index.php?m=user&c=company_job&a=refresh', 1, '', 13, 0, 1, 4, ''),
        (925, '转移类别', 40, 'index.php?m=user&c=company_job&a=saveclass', 1, '', 14, 0, 1, 4, ''),
        (926, '认证', 17, 'index.php?m=user&c=users_member&a=usercert', 1, '', 0, 0, 1, 4, ''),
        (927, '企微', 3, '', 1, 'nav_work', 9, 0, 1, 1, ''),
        (928, '企微配置', 927, 'index.php?m=yunying&c=workwx_config', 1, '', 1, 0, 1, 3, '/qiweiset'),
        (929, '群聊列表', 927, 'index.php?m=yunying&c=workwx_group', 1, '', 4, 0, 1, 3, '/qwqunliao'),
        (930, '职位专题', 343, 'index.php?m=yunying&c=special_job', 1, '', 3, 0, 1, 3, '/special_job'),
        (932, '客户列表', 927, 'index.php?m=yunying&c=workwx_customer', 1, '', 3, 0, 1, 3, '/qwkehu'),
        (933, '外呼', 212, '', 0, 'nav_wh', 5, 0, 1, 2, ''),
        (934, '外呼记录', 933, 'index.php?m=crm&c=crm_outcall&a=log', 1, '', 2, 0, 1, 3, '/outCallLog'),
        (935, '坐席信息', 933, 'index.php?m=crm&c=crm_outcall&a=staffList', 1, '', 3, 0, 1, 3, '/outCallStaffList'),
        (936, '外呼配置', 933, 'index.php?m=crm&c=crm_outcall&a=getConfig', 1, '', 4, 0, 1, 3, '/outCallSet'),
        (937, '数据看板', 933, 'index.php?m=crm&c=crm_outcall', 1, '', 1, 0, 1, 3, '/outCallStatistics'),
        (938, '员工列表', 927, 'index.php?m=yunying&c=workwx_list', 1, '', 2, 0, 1, 3, '/qwyuang'),
        (940, '客户群活码', 927, 'index.php?m=yunying&c=workwx_qrcode', 1, '', 5, 0, 1, 3, '/qwkehuqun'),
        (941, '客户渠道码', 927, 'index.php?m=yunying&c=workwx_code', 1, '', 6, 0, 1, 3, '/qwkhqdmao'),
        (942, '消息推送', 927, 'index.php?m=yunying&c=workwx_push', 1, '', 7, 0, 1, 3, '/qwinfo'),
        (943, '技能提升', 3, '', 1, 'nav_skill', 10, 0, 1, 1, ''),
        (944, '课程列表', 943, 'index.php?m=yunying&c=skill', 1, '', 1, 0, 1, 3, '/skill'),
        (945, '报名记录', 943, 'index.php?m=yunying&c=skill_bm', 1, '', 2, 0, 1, 3, '/skillbm'),
        (946, '自动回复', 350, 'index.php?m=tool&c=chatzdhf', 1, '', 5, 0, 1, 3, '/chatzdhf'),
        (947, '行为预警', 123, 'index.php?m=tool&c=abnormal', 1, '', 9, 0, 1, 3, '/abnormal'),
        (1012, '分类管理', 943, 'index.php?m=yunying&c=skill_class', 1, '', 3, 0, 1, 3, '/skillclass'),
        (1014, '年度报告', 177, 'index.php?m=yunying&c=yingxiao_hrlog', 1, '', 10, 0, 1, 3, '/yingxiao_hrlog'),
        (1017, '转企业库', 219, 'index.php?m=crm_customer&c=clueCustomer', 1, '', 1, 0, 1, 4, ''),
        (1019, '新增简历', 857, 'index.php?m=crm&c=crm_chat&a=saveResume', 1, ' ', 1, 0, 1, 4, ''),
        (1021, '归属地配置', 123, 'index.php?m=tool&c=gsdConfig', 1, '', 10, 0, 1, 3, '/gsdConfig'),
        (1022, '删除跟进记录', 857, 'index.php?m=crm&c=crm_chat&a=delFollow', 1, ' ', 1, 0, 1, 4, ''),
        (1024, '管理', 857, 'index.php?m=crm&c=crm_chat&a=getResumeInfo', 1, ' ', 2, 0, 1, 4, ''),
        (1026, '会员分类', 73, '', 1, '', 1, 0, 1, 3, '/member_index'),
        (1027, '猎头分类', 73, '', 1, '', 7, 0, 1, 3, '/lt_index'),
        (1028, '培训分类', 73, '', 1, '', 9, 0, 1, 3, '/peixun_index'),
        (1029, '模板设置', 121, 'index.php?m=system&c=set_tplset', 1, '', 3, 0, 1, 1, '/tplset'),
        (1030, '刷新日志', 6, 'index.php?m=user&c=company_job_refresh_log', 1, '', 8, 0, 1, 1, '/companylog'),
        (1047, '其他小程序', 363, 'index.php?m=tool&c=xcxset', 1, '', 3, 0, 1, 3, '/otherxcxset'),
        (1049, '过期用户', 16, 'index.php?m=user&c=company_expire', 1, '0', 3, 0, 1, 4, ''),
        (1050, '会员日志', 18, 'index.php?m=user&c=admin_lt_log', 1, '0', 1, 0, 1, 4, ''),
        (1051, '解绑记录', 18, 'index.php?m=user&c=admin_user_member&a=writtenOffLog', 1, '', 0, 0, 1, 4, ''),
        (1061, '子账号', 16, 'index.php?m=user&c=zzh', 1, '0', 1, 0, 0, 4, ''),
        (1062, '积分管理', 16, 'index.php?m=user&c=company_pay', 1, '0', 1, 0, 0, 4, ''),
        (1063, '充值订单', 16, 'index.php?m=user&c=company_order', 2, '0', 1, 0, 0, 4, '/chongzhidd'),
        (1064, '小程序SEO', 363, 'index.php?m=tool&c=app_set&a=seo', 1, '', 3, 0, 1, 3, '/xcxseo'),
        (1065, '求职助手', 363, 'index.php?m=tool&c=appset&a=career_counselor', 1, '', 3, 0, 1, 3, '/jobhelper'),
        (1067, '数据风向标', 168, 'index.php?m=tool&c=dataBoard&a=fenxiabiao', 1, '0', 0, 0, 1, 4, '');");
    
    // 超级管理员权限
    $group = $db->DB_select_once("admin_user_group","1 order by id asc");
    
    $db->query('UPDATE `' . $db_config[def] . 'admin_user_group` SET `group_power`=\'a:493:{i:0;s:1:"5";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"2";i:4;s:1:"4";i:5;s:3:"212";i:6;s:3:"195";i:7;s:3:"339";i:8;s:1:"7";i:9;s:1:"6";i:10;s:1:"8";i:11;s:1:"9";i:12;s:3:"156";i:13;s:3:"343";i:14;s:2:"10";i:15;s:2:"13";i:16;s:2:"14";i:17;s:2:"12";i:18;s:3:"198";i:19;s:2:"15";i:20;s:2:"11";i:21;s:2:"74";i:22;s:2:"75";i:23;s:3:"102";i:24;s:3:"103";i:25;s:3:"123";i:26;s:3:"121";i:27;s:3:"186";i:28;s:3:"158";i:29;s:3:"161";i:30;s:3:"170";i:31;s:3:"176";i:32;s:3:"177";i:33;s:3:"213";i:34;s:3:"350";i:35;s:3:"363";i:36;s:3:"393";i:37;s:3:"401";i:38;s:3:"815";i:39;s:3:"823";i:40;s:3:"844";i:41;s:3:"853";i:42;s:3:"226";i:43;s:3:"902";i:44;s:3:"927";i:45;s:3:"933";i:46;s:3:"943";i:47;s:2:"73";i:48;s:3:"166";i:49;s:3:"122";i:50;s:3:"185";i:51;s:3:"196";i:52;s:3:"101";i:53;s:3:"340";i:54;s:3:"342";i:55;s:2:"17";i:56;s:2:"33";i:57;s:2:"34";i:58;s:2:"37";i:59;s:2:"38";i:60;s:2:"39";i:61;s:2:"16";i:62;s:2:"40";i:63;s:2:"46";i:64;s:4:"1030";i:65;s:2:"47";i:66;s:2:"50";i:67;s:2:"52";i:68;s:2:"53";i:69;s:2:"18";i:70;s:2:"19";i:71;s:2:"20";i:72;s:2:"21";i:73;s:2:"23";i:74;s:2:"24";i:75;s:2:"25";i:76;s:2:"26";i:77;s:2:"27";i:78;s:2:"28";i:79;s:2:"30";i:80;s:2:"32";i:81;s:2:"48";i:82;s:2:"36";i:83;s:2:"49";i:84;s:3:"930";i:85;s:2:"54";i:86;s:2:"56";i:87;s:3:"204";i:88;s:2:"57";i:89;s:2:"58";i:90;s:2:"59";i:91;s:2:"61";i:92;s:3:"802";i:93;s:2:"65";i:94;s:2:"68";i:95;s:3:"814";i:96;s:2:"66";i:97;s:2:"97";i:98;s:3:"359";i:99;s:2:"69";i:100;s:2:"71";i:101;s:2:"72";i:102;s:3:"361";i:103;s:2:"76";i:104;s:2:"77";i:105;s:2:"82";i:106;s:3:"595";i:107;s:2:"83";i:108;s:2:"85";i:109;s:2:"86";i:110;s:3:"104";i:111;s:3:"105";i:112;s:3:"110";i:113;s:3:"112";i:114;s:3:"827";i:115;s:3:"113";i:116;s:3:"117";i:117;s:3:"120";i:118;s:3:"404";i:119;s:3:"132";i:120;s:3:"133";i:121;s:3:"134";i:122;s:3:"135";i:123;s:3:"168";i:124;s:3:"169";i:125;s:3:"382";i:126;s:3:"863";i:127;s:3:"947";i:128;s:4:"1021";i:129;s:3:"142";i:130;s:3:"143";i:131;s:3:"144";i:132;s:4:"1029";i:133;s:3:"145";i:134;s:3:"148";i:135;s:3:"149";i:136;s:3:"151";i:137;s:3:"152";i:138;s:3:"153";i:139;s:3:"174";i:140;s:3:"193";i:141;s:3:"175";i:142;s:3:"194";i:143;s:3:"187";i:144;s:3:"188";i:145;s:3:"159";i:146;s:3:"160";i:147;s:3:"162";i:148;s:3:"165";i:149;s:3:"163";i:150;s:3:"205";i:151;s:3:"167";i:152;s:3:"164";i:153;s:3:"171";i:154;s:3:"173";i:155;s:3:"178";i:156;s:3:"179";i:157;s:3:"180";i:158;s:3:"181";i:159;s:3:"353";i:160;s:3:"860";i:161;s:3:"182";i:162;s:3:"183";i:163;s:3:"594";i:164;s:3:"862";i:165;s:4:"1014";i:166;s:3:"216";i:167;s:3:"220";i:168;s:3:"217";i:169;s:3:"218";i:170;s:3:"219";i:171;s:3:"387";i:172;s:3:"225";i:173;s:3:"390";i:174;s:3:"805";i:175;s:3:"857";i:176;s:3:"351";i:177;s:3:"352";i:178;s:3:"852";i:179;s:3:"946";i:180;s:3:"364";i:181;s:3:"365";i:182;s:3:"367";i:183;s:4:"1047";i:184;s:4:"1064";i:185;s:4:"1065";i:186;s:3:"366";i:187;s:3:"394";i:188;s:3:"398";i:189;s:3:"399";i:190;s:3:"400";i:191;s:3:"397";i:192;s:3:"402";i:193;s:3:"403";i:194;s:3:"109";i:195;s:3:"865";i:196;s:3:"816";i:197;s:3:"817";i:198;s:3:"818";i:199;s:3:"819";i:200;s:3:"824";i:201;s:3:"829";i:202;s:3:"840";i:203;s:3:"845";i:204;s:3:"846";i:205;s:3:"854";i:206;s:3:"855";i:207;s:3:"866";i:208;s:3:"231";i:209;s:3:"396";i:210;s:3:"847";i:211;s:3:"389";i:212;s:3:"391";i:213;s:3:"395";i:214;s:3:"392";i:215;s:3:"228";i:216;s:3:"838";i:217;s:3:"227";i:218;s:3:"801";i:219;s:3:"903";i:220;s:3:"928";i:221;s:3:"938";i:222;s:3:"932";i:223;s:3:"929";i:224;s:3:"940";i:225;s:3:"941";i:226;s:3:"942";i:227;s:3:"937";i:228;s:3:"934";i:229;s:3:"935";i:230;s:3:"936";i:231;s:3:"944";i:232;s:3:"945";i:233;s:4:"1012";i:234;s:4:"1026";i:235;s:2:"88";i:236;s:2:"89";i:237;s:2:"92";i:238;s:3:"337";i:239;s:2:"93";i:240;s:4:"1027";i:241;s:2:"98";i:242;s:4:"1028";i:243;s:3:"602";i:244;s:2:"79";i:245;s:2:"78";i:246;s:2:"81";i:247;s:3:"124";i:248;s:3:"125";i:249;s:3:"128";i:250;s:3:"127";i:251;s:3:"822";i:252;s:3:"189";i:253;s:3:"190";i:254;s:3:"341";i:255;s:3:"537";i:256;s:3:"538";i:257;s:3:"539";i:258;s:3:"540";i:259;s:3:"541";i:260;s:3:"542";i:261;s:3:"535";i:262;s:3:"536";i:263;s:3:"408";i:264;s:3:"409";i:265;s:3:"410";i:266;s:3:"412";i:267;s:3:"413";i:268;s:3:"414";i:269;s:3:"421";i:270;s:3:"422";i:271;s:3:"830";i:272;s:3:"911";i:273;s:3:"914";i:274;s:3:"915";i:275;s:3:"916";i:276;s:4:"1061";i:277;s:4:"1062";i:278;s:4:"1063";i:279;s:2:"41";i:280;s:4:"1049";i:281;s:3:"430";i:282;s:3:"432";i:283;s:3:"431";i:284;s:2:"44";i:285;s:2:"45";i:286;s:3:"433";i:287;s:3:"434";i:288;s:3:"435";i:289;s:3:"436";i:290;s:3:"437";i:291;s:3:"843";i:292;s:3:"438";i:293;s:3:"439";i:294;s:3:"440";i:295;s:2:"55";i:296;s:3:"441";i:297;s:3:"442";i:298;s:3:"443";i:299;s:2:"70";i:300;s:3:"444";i:301;s:3:"445";i:302;s:3:"446";i:303;s:3:"455";i:304;s:3:"154";i:305;s:3:"456";i:306;s:3:"457";i:307;s:3:"458";i:308;s:3:"459";i:309;s:3:"460";i:310;s:3:"464";i:311;s:3:"465";i:312;s:3:"466";i:313;s:3:"467";i:314;s:3:"468";i:315;s:3:"469";i:316;s:3:"470";i:317;s:3:"471";i:318;s:3:"472";i:319;s:3:"473";i:320;s:3:"474";i:321;s:3:"475";i:322;s:3:"206";i:323;s:3:"476";i:324;s:3:"477";i:325;s:3:"478";i:326;s:3:"479";i:327;s:3:"344";i:328;s:3:"480";i:329;s:3:"481";i:330;s:3:"482";i:331;s:3:"483";i:332;s:3:"484";i:333;s:3:"485";i:334;s:3:"486";i:335;s:3:"487";i:336;s:3:"488";i:337;s:3:"489";i:338;s:3:"490";i:339;s:3:"491";i:340;s:4:"1051";i:341;s:4:"1050";i:342;s:3:"494";i:343;s:3:"495";i:344;s:3:"349";i:345;s:3:"496";i:346;s:3:"504";i:347;s:3:"503";i:348;s:3:"498";i:349;s:3:"499";i:350;s:3:"500";i:351;s:3:"501";i:352;s:3:"502";i:353;s:3:"506";i:354;s:3:"508";i:355;s:3:"509";i:356;s:3:"510";i:357;s:3:"511";i:358;s:3:"512";i:359;s:3:"513";i:360;s:3:"514";i:361;s:3:"515";i:362;s:2:"29";i:363;s:2:"31";i:364;s:3:"516";i:365;s:3:"520";i:366;s:3:"521";i:367;s:3:"522";i:368;s:3:"523";i:369;s:3:"524";i:370;s:3:"519";i:371;s:3:"526";i:372;s:3:"527";i:373;s:3:"528";i:374;s:3:"529";i:375;s:3:"530";i:376;s:3:"531";i:377;s:3:"532";i:378;s:3:"533";i:379;s:3:"534";i:380;s:3:"544";i:381;s:3:"545";i:382;s:3:"550";i:383;s:3:"551";i:384;s:3:"552";i:385;s:3:"553";i:386;s:3:"554";i:387;s:3:"555";i:388;s:3:"556";i:389;s:3:"557";i:390;s:3:"558";i:391;s:3:"559";i:392;s:3:"561";i:393;s:3:"560";i:394;s:3:"563";i:395;s:3:"564";i:396;s:3:"565";i:397;s:3:"566";i:398;s:3:"567";i:399;s:3:"570";i:400;s:3:"572";i:401;s:3:"573";i:402;s:3:"574";i:403;s:3:"575";i:404;s:3:"576";i:405;s:3:"577";i:406;s:3:"906";i:407;s:3:"907";i:408;s:3:"908";i:409;s:3:"926";i:410;s:3:"578";i:411;s:3:"579";i:412;s:3:"580";i:413;s:3:"581";i:414;s:3:"582";i:415;s:3:"583";i:416;s:3:"905";i:417;s:3:"909";i:418;s:3:"910";i:419;s:2:"35";i:420;s:3:"584";i:421;s:3:"589";i:422;s:3:"590";i:423;s:3:"592";i:424;s:3:"585";i:425;s:3:"586";i:426;s:3:"587";i:427;s:3:"588";i:428;s:3:"861";i:429;s:3:"809";i:430;s:3:"810";i:431;s:3:"811";i:432;s:3:"867";i:433;s:3:"868";i:434;s:4:"1067";i:435;s:2:"62";i:436;s:2:"63";i:437;s:2:"64";i:438;s:3:"447";i:439;s:3:"448";i:440;s:3:"449";i:441;s:3:"450";i:442;s:3:"451";i:443;s:2:"87";i:444;s:2:"91";i:445;s:3:"172";i:446;s:3:"405";i:447;s:3:"406";i:448;s:3:"407";i:449;s:3:"596";i:450;s:3:"806";i:451;s:3:"184";i:452;s:3:"601";i:453;s:3:"415";i:454;s:3:"416";i:455;s:3:"917";i:456;s:3:"417";i:457;s:3:"418";i:458;s:3:"419";i:459;s:3:"420";i:460;s:3:"918";i:461;s:3:"919";i:462;s:3:"920";i:463;s:3:"921";i:464;s:3:"922";i:465;s:3:"924";i:466;s:3:"925";i:467;s:2:"42";i:468;s:2:"43";i:469;s:3:"423";i:470;s:3:"425";i:471;s:3:"426";i:472;s:3:"493";i:473;s:3:"492";i:474;s:2:"22";i:475;s:3:"497";i:476;s:4:"1017";i:477;s:4:"1019";i:478;s:4:"1022";i:479;s:4:"1024";i:480;s:3:"839";i:481;s:3:"904";i:482;s:2:"94";i:483;s:2:"95";i:484;s:2:"96";i:485;s:3:"429";i:486;s:3:"821";i:487;s:2:"99";i:488;s:3:"100";i:489;s:3:"505";i:490;s:3:"517";i:491;s:3:"518";i:492;s:3:"525";}\' WHERE `id`='.$group['id'].'');
    
    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=3';</script>";
}
/***************************索引优化************************************/

if($_GET[step]=="3"){
   
	$db->query("ALTER TABLE `$db_config[def]resume_expect` ADD INDEX(ctime)");
	$db->query("ALTER TABLE `$db_config[def]resume_expect` ADD INDEX(height_status)");


    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=4';</script>";
}

if($_GET[step]=="4"){

	$db->query("ALTER TABLE `$db_config[def]member` ADD INDEX(reg_date)");
	$db->query("ALTER TABLE `$db_config[def]member` ADD INDEX(login_date)");
	$db->query("ALTER TABLE `$db_config[def]member` ADD INDEX(wxid)");
	$db->query("ALTER TABLE `$db_config[def]member` ADD INDEX(wxbindtime)");
	$db->query("ALTER TABLE `$db_config[def]member` ADD INDEX(appealtime)");


    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=5';</script>";
}
if($_GET[step]=="5"){
   
	$db->query("ALTER TABLE `$db_config[def]userid_job` ADD INDEX(`datetime`)");
	$db->query("ALTER TABLE `$db_config[def]userid_msg` ADD INDEX(uid)");
	$db->query("ALTER TABLE `$db_config[def]userid_msg` ADD INDEX(datetime)");
	$db->query("ALTER TABLE `$db_config[def]userid_msg` ADD INDEX(jobid)");
	$db->query("ALTER TABLE `$db_config[def]down_resume` ADD INDEX(downtime)");




    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=6';</script>";
}
if($_GET[step]=="6"){
   
	$db->query("ALTER TABLE `$db_config[def]resume` ADD INDEX(photo_status)");
	$db->query("ALTER TABLE `$db_config[def]resume` ADD INDEX(idcard_pic)");

    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=7';</script>";
}
if($_GET[step]=="7"){
   
	$db->query("ALTER TABLE `$db_config[def]company` ADD INDEX(clue)");
	$db->query("ALTER TABLE `$db_config[def]company` ADD INDEX(logo_status)");
	$db->query("ALTER TABLE `$db_config[def]company` ADD INDEX(crm_uid)");
	$db->query("ALTER TABLE `$db_config[def]company` ADD INDEX(vipetime)");
	$db->query("ALTER TABLE `$db_config[def]company` ADD INDEX(rec)");

	$db->query("ALTER TABLE `$db_config[def]company_job` ADD INDEX(sdate)");

	$db->query("ALTER TABLE `$db_config[def]company_order` ADD INDEX(order_state)");

	$db->query("ALTER TABLE `$db_config[def]rating_notice` ADD INDEX(uid)");
	$db->query("ALTER TABLE `$db_config[def]rating_notice` ADD INDEX(crm_uid)");

    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=8';</script>";
}
if($_GET[step]=="8"){
   
	$db->query("ALTER TABLE `$db_config[def]chat_log` ADD INDEX(sendtime)");

    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=9';</script>";
}

if($_GET[step]=="9"){
   
	$db->query("ALTER TABLE `$db_config[def]staff_call_log` ADD INDEX(uid)");
	$db->query("ALTER TABLE `$db_config[def]staff_call_log` ADD INDEX(cuid)");
	$db->query("ALTER TABLE `$db_config[def]staff_call_log` ADD INDEX(ctime)");

	$db->query("ALTER TABLE `$db_config[def]crmnew_concern` ADD INDEX(auid)");
	$db->query("ALTER TABLE `$db_config[def]crmnew_concern` ADD INDEX(uid)");
	$db->query("ALTER TABLE `$db_config[def]crmnew_concern` ADD INDEX(ftime)");
	$db->query("ALTER TABLE `$db_config[def]crmnew_concern` ADD INDEX(atime)");

    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=10';</script>";
}




/****************************第三步  清空缓存************************************/
if($_GET[step]=="10"){
    $delfiles="../data/templates_c";
    $dh=@opendir($delfiles);
    while($file=@readdir($dh)){
        if($file!="."&&$file!=".."){
            $fullpath=$delfiles."/".$file;
            @unlink($fullpath);
        }
    }
    @closedir($dh);
    echo "<script>location.href='$config[sy_weburl]/update/index.php?step=11';</script>";
}
/****************************第四步 升级完成************************************/
if($_GET[step]=="11"){
    
    $db->query('INSERT INTO `'.$db_config[def].'version` SET `version`="v7.0.1 VIP版(20240122)",`code`="7.0.1",`ctime`='.time());
    
    echo "数据库升级成功！请删除/update/ 目录 根据以下提示继续操作！";
    echo "<pre>";
    echo "1：进入后台 工具-生成-<font color='red'>生成缓存</font>";
    echo "<pre>";
    echo "2：进入后台 清除缓存";
    echo "<pre>";
    echo "3：其他各项配置按需修改";
    echo "<pre>";
}
?>