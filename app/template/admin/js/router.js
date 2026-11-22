// import Customer from './business/index.html'
//创建 router 实例，然后传 `routes` 配置

const view_path = "../app/template/admin/";
const indexPath = localStorage.getItem('indexPath');

const router = new VueRouter({
    routes: [
        {
            path: '/',
            redirect: indexPath ? indexPath : '/index',
        }, {
            path: '/admin_nav',
            name: 'admin_nav', //后台导航设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/admin_nav.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },
        {
            path: '/version',
            name: 'version', //升级记录
            component: {
                template: '<iframe src="' + view_path + 'system/set/version.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },
        {
            path: '/navmap',
            name: 'navmap', //网站地图
            component: {
                template: '<iframe src="' + view_path + 'system/set/navmap.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },
        {
            path: '/index',
            name: 'index', //首页
            component: {
                template: '<iframe id="index" src="' + view_path + 'index/index/index.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },
        {
            path: '/set',
            name: 'set', //网站设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/index.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/moduleset',
            name: 'moduleset', //模块设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/moduleset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yemainset',
            name: 'yemainset', //页面设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/yemainset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/navigation',
            name: 'navigation', //导航设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/navigation.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/payset',
            name: 'payset', //支付设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/payset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/seoset',
            name: 'seoset', //SEO设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/seoset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/jifenset',
            name: 'jifenset', //积分设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/jifenset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/regset',
            name: 'regset', //注册设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/regset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/warning',
            name: 'warning', //预警设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/warning.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/tplset',
            name: 'tplset', //模板设置
            component: {
                template: '<iframe src="' + view_path + 'system/set/tplset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/cron',
            name: 'cron', //计划任务
            component: {
                template: '<iframe src="' + view_path + 'system/set/cron.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/guanjianci',
            name: 'guanjianci', //关键字管理
            component: {
                template: '<iframe src="' + view_path + 'system/set/guanjianci.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/member_index',
            name: 'member_index', //会员分类
            component: {
                template: '<iframe src="' + view_path + 'system/category/member_index.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/city',
            name: 'city', //城市管理
            component: {
                template: '<iframe src="' + view_path + 'system/category/city.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/industry',
            name: 'industry', //行业类别
            component: {
                template: '<iframe src="' + view_path + 'system/category/industry.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/job_class',
            name: 'job_class', //职位类别
            component: {
                template: '<iframe src="' + view_path + 'system/category/job_class.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/lt_index',
            name: 'lt_index', //猎头分类
            component: {
                template: '<iframe src="' + view_path + 'system/category/lt_index.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/schoolclass',
            name: 'schoolclass', //校园分类
            component: {
                template: '<iframe src="' + view_path + 'system/category/schoolclass.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/reason',
            name: 'reason', //举报原因
            component: {
                template: '<iframe src="' + view_path + 'system/category/reason.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/singlepage',
            name: 'singlepage', //单页面管理
            component: {
                template: '<iframe src="' + view_path + 'system/single/singlepage.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/singleclass',
            name: 'singleclass', //单页面类别
            component: {
                template: '<iframe src="' + view_path + 'system/single/singleclass.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/domainList',
            name: 'domainList', //分站管理
            component: {
                template: '<iframe src="' + view_path + 'system/domain/domainList.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/domainAdminList',
            name: 'domainAdminList', //分站管理员
            component: {
                template: '<iframe src="' + view_path + 'system/domain/domainAdminList.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/feedback',
            name: 'feedback', //意见反馈
            component: {
                template: '<iframe src="' + view_path + 'system/info/feedback.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/errorlog',
            name: 'errorlog', //错误日志
            component: {
                template: '<iframe src="' + view_path + 'system/info/errorlog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/systeminfo',
            name: 'systeminfo', //系统消息
            component: {
                template: '<iframe src="' + view_path + 'system/info/system.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/friendlink',
            name: 'friendlink', //友情链接
            component: {
                template: '<iframe src="' + view_path + 'system/set/friendlink.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/myaccount',
            name: 'myaccount', //我的帐号
            component: {
                template: '<iframe src="' + view_path + 'system/role/myaccount.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/user',
            name: 'user', //管理员列表
            component: {
                template: '<iframe src="' + view_path + 'system/role/user.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/logrecord',
            name: 'logrecord', //管理员日志
            component: {
                template: '<iframe src="' + view_path + 'system/role/logrecord.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/notice',
            name: 'notice', //微信通知设置
            component: {
                template: '<iframe src="' + view_path + 'system/role/notice.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/ugroup',
            name: 'ugroup', //管理员类型
            component: {
                template: '<iframe src="' + view_path + 'system/role/ugroup.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/partclass',
            name: 'partclass', //兼职分类
            component: {
                template: '<iframe src="' + view_path + 'system/category/partclass.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/introduce_class',
            name: 'introduce_class', //自我介绍
            component: {
                template: '<iframe src="' + view_path + 'system/category/introduce_class.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/peixun_index',
            name: 'peixun_index', //培训分类
            component: {
                template: '<iframe src="' + view_path + 'system/category/peixun_index.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/shopreward',
            name: 'shopreward', //商品管理
            component: {
                template: '<iframe src="' + view_path + 'yunying/shop/shopreward.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/shoplist',
            name: 'shoplist', //兑换记录
            component: {
                template: '<iframe src="' + view_path + 'yunying/shop/shoplist.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/shopclass',
            name: 'shopclass', //商品分类
            component: {
                template: '<iframe src="' + view_path + 'yunying/shop/shopclass.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/shopset',
            name: 'shopset', //商品设置
            component: {
                template: '<iframe src="' + view_path + 'yunying/shop/shopset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/ad',
            name: 'ad', //广告管理
            component: {
                template: '<iframe src="' + view_path + 'yunying/ad/ad.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/ad_order',
            name: 'ad_order', //广告订单
            component: {
                template: '<iframe src="' + view_path + 'yunying/ad/ad_order.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/ad_class',
            name: 'ad_class', //广告类别
            component: {
                template: '<iframe src="' + view_path + 'yunying/ad/ad_class.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/fapiaoguan',
            name: 'fapiaoguan', //发票管理
            component: {
                template: '<iframe src="' + view_path + 'yunying/caiwu/fapiaoguan.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/xiaofei',
            name: 'xiaofei', //消费日志
            component: {
                template: '<iframe src="' + view_path + 'yunying/caiwu/xiaofei.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/xiaofeitj',
            name: 'xiaofeitj', //消费统计
            component: {
                template: '<iframe src="' + view_path + 'yunying/caiwu/xiaofeitj.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/chongzhidd',
            name: 'chongzhidd', //充值订单
            component: {
                template: '<iframe src="' + view_path + 'yunying/caiwu/chongzhidd.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/shangjintx',
            name: 'shangjintx', //赏金提现
            component: {
                template: '<iframe src="' + view_path + 'yunying/caiwu/shangjintx.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/houtaicz',
            name: 'houtaicz', //后台充值
            component: {
                template: '<iframe src="' + view_path + 'yunying/caiwu/houtaicz.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/shouzhitj',
            name: 'shouzhitj', //收支统计
            component: {
                template: '<iframe src="' + view_path + 'yunying/caiwu/shouzhitj.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/couponprepaid',
            name: 'couponprepaid', //充值卡管理
            component: {
                template: '<iframe src="' + view_path + 'yunying/coupon/prepaid.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/couponlist',
            name: 'couponlist', //优惠券记录
            component: {
                template: '<iframe src="' + view_path + 'yunying/coupon/list.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/coupon',
            name: 'coupon', //优惠券管理
            component: {
                template: '<iframe src="' + view_path + 'yunying/coupon/index.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/special',
            name: 'special', //招聘专题
            component: {
                template: '<iframe src="' + view_path + 'yunying/special/special.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/special_job',
            name: 'special_job', //职位专题
            component: {
                template: '<iframe src="' + view_path + 'yunying/special/special_job.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/zhcguanjianci',
            name: 'zhcguanjianci', // TODO 专场招聘 功能待开发
            component: {
                template: '<iframe src="' + view_path + 'yunying/zhuanchang/zhcguanjianci.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/zhuancanzp',
            name: 'zhuancanzp', // TODO 关键词 功能待开发
            component: {
                template: '<iframe src="' + view_path + 'yunying/zhuanchang/zhuancanzp.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/reportchat',
            name: 'reportchat', //举报聊天
            component: {
                template: '<iframe src="' + view_path + 'yunying/jubao/reportchat.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/reportresume',
            name: 'reportresume', //举报简历
            component: {
                template: '<iframe src="' + view_path + 'yunying/jubao/reportresume.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/reportask',
            name: 'reportask', //举报问答
            component: {
                template: '<iframe src="' + view_path + 'yunying/jubao/reportask.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/reportjob',
            name: 'reportjob', //举报职位
            component: {
                template: '<iframe src="' + view_path + 'yunying/jubao/reportjob.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/reportadvise',
            name: 'reportadvise', //举报顾问
            component: {
                template: '<iframe src="' + view_path + 'yunying/jubao/reportadvise.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/reportxjh',
            name: 'reportxjh', //举报宣讲会
            component: {
                template: '<iframe src="' + view_path + 'yunying/jubao/reportxjh.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yingxiao_tuiguang',
            name: 'yingxiao_tuiguang', //推广营稍
            component: {
                template: '<iframe src="' + view_path + 'yunying/yingxiao/tuiguang.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yingxiao_subscribe',
            name: 'yingxiao_subscribe', //订阅管理
            component: {
                template: '<iframe src="' + view_path + 'yunying/yingxiao/subscribe.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yingxiao_friendhelp',
            name: 'yingxiao_friendhelp', //好友助力
            component: {
                template: '<iframe src="' + view_path + 'yunying/yingxiao/friendhelp.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yingxiao_hbconfig',
            name: 'yingxiao_hbconfig', //海报设置
            component: {
                template: '<iframe src="' + view_path + 'yunying/yingxiao/hbconfig.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/tuiguangren',
            name: 'tuiguangren', //推广人
            component: {
                template: '<iframe src="' + view_path + 'yunying/yingxiao/tuiguangren.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yingxiao_video',
            name: 'yingxiao_video', //视频探秘
            component: {
                template: '<iframe src="' + view_path + 'yunying/yingxiao/video.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yingxiao_hrlog',
            name: 'yingxiao_hrlog', //年度报告
            component: {
                template: '<iframe src="' + view_path + 'yunying/yingxiao/hrlog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/qiweiset',
            name: 'qiweiset', //企微配置
            component: {
                template: '<iframe src="' + view_path + 'yunying/qiwei/qiweiset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/qwyuang',
            name: 'qwyuang', //员工列表
            component: {
                template: '<iframe src="' + view_path + 'yunying/qiwei/qwyuang.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/qwkehu',
            name: 'qwkehu', //微群客户
            component: {
                template: '<iframe src="' + view_path + 'yunying/qiwei/qwkehu.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/qwqunliao',
            name: 'qwqunliao', //群聊列表
            component: {
                template: '<iframe src="' + view_path + 'yunying/qiwei/qwqunliao.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/qwkehuqun',
            name: 'qwkehuqun', //客户群活码
            component: {
                template: '<iframe src="' + view_path + 'yunying/qiwei/qwqrcode.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/qwkhqdmao',
            name: 'qwkhqdmao', //客户渠道码
            component: {
                template: '<iframe src="' + view_path + 'yunying/qiwei/qwcode.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/qwinfo',
            name: 'qwinfo', //消息推送
            component: {
                template: '<iframe src="' + view_path + 'yunying/qiwei/qwpush.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/skill',
            name: 'skill', //课程列表
            component: {
                template: '<iframe src="' + view_path + 'yunying/skill/skill.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/skillclass',
            name: 'skillclass', //分类管理
            component: {
                template: '<iframe src="' + view_path + 'yunying/skill/skillclass.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/skillbm',
            name: 'skillbm', //报名记录
            component: {
                template: '<iframe src="' + view_path + 'yunying/skill/skillbm.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/newsmanage',
            name: 'newsmanage', //新闻管理
            component: {
                template: '<iframe src="' + view_path + 'neirong/news/newsmanage.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/newslb',
            name: 'newslb', //新闻类别
            component: {
                template: '<iframe src="' + view_path + 'neirong/news/newslb.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/newssx',
            name: 'newssx', //新闻属性
            component: {
                template: '<iframe src="' + view_path + 'neirong/news/newssx.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/cpsj',
            name: 'cpsj', //测评试卷
            component: {
                template: '<iframe src="' + view_path + 'neirong/cp/cpsj.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/cpfl',
            name: 'cpfl', //测评类别
            component: {
                template: '<iframe src="' + view_path + 'neirong/cp/cpfl.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/lymanage',
            name: 'lymanage', //测评留言
            component: {
                template: '<iframe src="' + view_path + 'neirong/cp/lymanage.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yhjl',
            name: 'yhjl', //测评日志
            component: {
                template: '<iframe src="' + view_path + 'neirong/cp/yhjl.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/generate_page',
            name: 'generate_page', //页面生成
            component: {
                template: '<iframe src="' + view_path + 'tool/generate/generate_page.html" class="iframeAlls" frameborder="0"></iframe>',
            },
        }, {
            path: '/generate_baipin',
            name: 'generate_baipin', //百度百聘
            component: {
                template: '<iframe src="' + view_path + 'tool/generate/generate_baipin.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/generate_cache',
            name: 'generate_cache', //生成缓存
            component: {
                template: '<iframe src="' + view_path + 'tool/generate/generate_cache.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/weixinrecord',
            name: 'weixinrecord', //用户日志
            component: {
                template: '<iframe src="' + view_path + 'tool/weixin/weixinrecord.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/fabutool',
            name: 'fabutool', //发布工具
            component: {
                template: '<iframe src="' + view_path + 'tool/weixin/fabutool.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/addpubtemp',
            name: 'addpubtemp', //修改发布工具
            component: {
                template: '<iframe src="' + view_path + 'tool/weixin/addpubtemp.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/weixinred',
            name: 'weixinred', //微信红包
            component: {
                template: '<iframe src="' + view_path + 'tool/weixin/weixinred.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/wxtz',
            name: 'wxtz', //通知模板
            component: {
                template: '<iframe src="' + view_path + 'tool/weixin/wxtz.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },{
            path: '/weixinmenu',
            name: 'weixinmenu', //微信菜单
            component: {
                template: '<iframe src="' + view_path + 'tool/weixin/weixinmenu.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/qdm',
            name: 'qdm', //渠道码
            component: {
                template: '<iframe src="' + view_path + 'tool/weixin/qdm.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/generate_xml',
            name: 'generate_xml', //生成XML
            component: {
                template: '<iframe src="' + view_path + 'tool/generate/generate_xml.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/fastlogin',
            name: 'fastlogin', //快捷登录
            component: {
                template: '<iframe src="' + view_path + 'tool/login/fastlogin.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/admin_uc',
            name: 'admin_uc', //登录整合
            component: {
                template: '<iframe src="' + view_path + 'tool/login/admin_uc.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/emaillog',
            name: 'emaillog', //邮件记录
            component: {
                template: '<iframe src="' + view_path + 'tool/email/emaillog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/emailset',
            name: 'emailset', //邮件设置
            component: {
                template: '<iframe src="' + view_path + 'tool/email/emailset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/messagelog',
            name: 'messagelog', //短信记录
            component: {
                template: '<iframe src="' + view_path + 'tool/message/messagelog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/messageset',
            name: 'messageset', //短信设置
            component: {
                template: '<iframe src="' + view_path + 'tool/message/messageset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/privacylog',
            name: 'privacylog', //隐私号日志
            component: {
                template: '<iframe src="' + view_path + 'tool/privacy/privacylog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/chatset',
            name: 'chatset', //聊天设置
            component: {
                template: '<iframe src="' + view_path + 'tool/chat/chatset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/chatcyy',
            name: 'chatcyy', //常用语
            component: {
                template: '<iframe src="' + view_path + 'tool/chat/chatcyy.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/chatzdhf',
            name: 'chatzdhf', //自动回复
            component: {
                template: '<iframe src="' + view_path + 'tool/chat/chatzdhf.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/chatlog',
            name: 'chatlog', //聊天记录
            component: {
                template: '<iframe src="' + view_path + 'tool/chat/chatlog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/groupmsg',
            name: 'groupmsg', //群发消息
            component: {
                template: '<iframe src="' + view_path + 'tool/chat/groupmsg.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/privacyset',
            name: 'privacyset', //隐私号设置
            component: {
                template: '<iframe src="' + view_path + 'tool/privacy/privacyset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/diy',
            name: 'diy', //模板DIY
            component: {
                template: '<iframe src="' + view_path + 'tool/app/diy.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/xcxseo',
            name: 'xcxseo', //小程序SEO
            component: {
                template: '<iframe src="' + view_path + 'tool/app/xcx_seo.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/jobhelper',
            name: 'jobhelper', //求职助手
            component: {
                template: '<iframe src="' + view_path + 'tool/app/job_helper.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/xcxset',
            name: 'xcxset', //微信小程序
            component: {
                template: '<iframe src="' + view_path + 'tool/app/xcxset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },
        {
            path: '/otherxcxset',
            name: 'otherxcxset', //其他小程序
            component: {
                template: '<iframe src="' + view_path + 'tool/app/other_xcx.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },
        {
            path: '/extraxcxset',
            name: 'extraxcxset', //额外小程序
            component: {
                template: '<iframe src="' + view_path + 'tool/app/extra_xcx.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },{
            path: '/pushlog',
            name: 'pushlog', //推送记录
            component: {
                template: '<iframe src="' + view_path + 'tool/app/pushlog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },
        {
            path: '/appset',
            name: 'appset', //APP配置
            component: {
                template: '<iframe src="' + view_path + 'tool/app/appset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/database',
            name: 'database', //数据库
            component: {
                template: '<iframe src="' + view_path + 'tool/database/database.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/dataCollection',
            name: 'dataCollection', //数据采集
            component: {
                template: '<iframe src="' + view_path + 'tool/database/dataCollection.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/dataExcel',
            name: 'dataExcel', //数据导入
            component: {
                template: '<iframe src="' + view_path + 'tool/database/dataExcel.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/dataCall',
            name: 'dataCall', //数据调用
            component: {
                template: '<iframe src="' + view_path + 'tool/database/dataCall.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/dataBoard',
            name: 'dataBoard', //数据看板
            component: {
                template: '<iframe src="' + view_path + 'tool/database/dataBoard.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/dataRecycle',
            name: 'dataRecycle', //回收站
            component: {
                template: '<iframe src="' + view_path + 'tool/database/dataRecycle.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/dataOss',
            name: 'dataOss', //OSS设置
            component: {
                template: '<iframe src="' + view_path + 'tool/database/dataOss.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/dataView',
            name: 'dataView', //数据大屏
            component: {
                template: '<iframe src="' + view_path + 'tool/database/dataView.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/abnormal',
            name: 'abnormal', //行为预警
            component: {
                template: '<iframe src="' + view_path + 'tool/database/abnormal.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/gsdConfig',
            name: 'gsdConfig', //归属地配置
            component: {
                template: '<iframe src="' + view_path + 'tool/database/gsdConfig.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/xczph',
            name: 'xczph', //现场招聘会
            component: {
                template: '<iframe src="' + view_path + 'neirong/zph/xczph.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/chcompany',
            name: 'chcompany', //参会企业
            component: {
                template: '<iframe src="' + view_path + 'neirong/zph/chcompany.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/zphaddress',
            name: 'zphaddress', //招聘会场地
            component: {
                template: '<iframe src="' + view_path + 'neirong/zph/zphaddress.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/netzph',
            name: 'netzph', //网络招聘会
            component: {
                template: '<iframe src="' + view_path + 'neirong/zph/netzph.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/toolbox_doc',
            name: 'toolbox_doc', //工具箱
            component: {
                template: '<iframe src="' + view_path + 'neirong/toolbox/doc.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/toolbox_class',
            name: 'toolbox_class', //工具箱类别
            component: {
                template: '<iframe src="' + view_path + 'neirong/toolbox/class.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/announcement',
            name: 'announcement', //公告管理
            component: {
                template: '<iframe src="' + view_path + 'neirong/announcement/index.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/addgg',
            name: 'addgg', //添加公告
            component: {
                template: '<iframe src="' + view_path + 'neirong/gg/addgg.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/question',
            name: 'question', //问答管理
            component: {
                template: '<iframe src="' + view_path + 'neirong/question/index.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/question_class',
            name: 'question_class', //问答类别
            component: {
                template: '<iframe src="' + view_path + 'neirong/question/class.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/question_config',
            name: 'question_config', //问答设置
            component: {
                template: '<iframe src="' + view_path + 'neirong/question/config.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/livemanage',
            name: 'livemanage', //直播管理
            component: {
                template: '<iframe src="' + view_path + 'neirong/live/livemanage.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yymanage',
            name: 'yymanage', //预约管理
            component: {
                template: '<iframe src="' + view_path + 'neirong/live/yymanage.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/yjcmanage',
            name: 'yjcmanage', //严禁词
            component: {
                template: '<iframe src="' + view_path + 'neirong/live/yjcmanage.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/liveset',
            name: 'liveset', //直播设置
            component: {
                template: '<iframe src="' + view_path + 'neirong/live/liveset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/jcset',
            name: 'jcset', //检测设置
            component: {
                template: '<iframe src="' + view_path + 'neirong/nrjc/jcset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/jcjl',
            name: 'jcjl', //检测记录
            component: {
                template: '<iframe src="' + view_path + 'neirong/nrjc/jcjl.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/msmanage',
            name: 'msmanage', //面试管理
            component: {
                template: '<iframe src="' + view_path + 'neirong/spview/msmanage.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/spset',
            name: 'spset', //视频设置
            component: {
                template: '<iframe src="' + view_path + 'neirong/spview/spset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/spjl',
            name: 'spjl', //视频记录
            component: {
                template: '<iframe src="' + view_path + 'neirong/spview/spjl.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/gzmanage',
            name: 'gzmanage', //公招管理
            component: {
                template: '<iframe src="' + view_path + 'neirong/gz/gzmanage.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/addgz',
            name: 'addgz', //添加公招
            component: {
                template: '<iframe src="' + view_path + 'neirong/gz/addgz.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/usercrm',
            name: 'usercrm', //会员列表
            component: {
                template: '<iframe src="' + view_path + 'user/member/usercrm.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/usergj',
            name: 'usergj', //行为轨迹
            component: {
                template: '<iframe src="' + view_path + 'user/member/usergj.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/userloginlog',
            name: 'userloginlog', //登录日志
            component: {
                template: '<iframe src="' + view_path + 'user/member/userloginlog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/userlog',
            name: 'userlog', //会员日志
            component: {
                template: '<iframe src="' + view_path + 'user/member/userlog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/peixun_teacher',
            name: 'peixun_teacher', //培训讲师
            component: {
                template: '<iframe src="' + view_path + 'user/peixun/teacher.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/peixun_subject',
            name: 'peixun_subject', //培训课程
            component: {
                template: '<iframe src="' + view_path + 'user/peixun/subject.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/peixun_config',
            name: 'peixun_config', //培训设置
            component: {
                template: '<iframe src="' + view_path + 'user/peixun/config.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/peixun_user',
            name: 'peixun_user', //培训用户
            component: {
                template: '<iframe src="' + view_path + 'user/peixun/user.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/peixun_renzheng',
            name: 'peixun_renzheng', //认证&审核
            component: {
                template: '<iframe src="' + view_path + 'user/peixun/renzheng.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/peixun_news',
            name: 'peixun_news', //培训新闻
            component: {
                template: '<iframe src="' + view_path + 'user/peixun/news.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/ltjob',
            name: 'ltjob', //猎头职位
            component: {
                template: '<iframe src="' + view_path + 'user/lietou/ltjob.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/ltrz',
            name: 'ltrz', //认证&审核
            component: {
                template: '<iframe src="' + view_path + 'user/lietou/ltrz.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/ltset',
            name: 'ltset', //猎头设置
            component: {
                template: '<iframe src="' + view_path + 'user/lietou/ltset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/lttc',
            name: 'lttc', //增值套餐
            component: {
                template: '<iframe src="' + view_path + 'user/lietou/lttc.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/ltuser',
            name: 'ltuser', //猎头用户
            component: {
                template: '<iframe src="' + view_path + 'user/lietou/ltuser.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/ltresume',
            name: 'ltresume', //猎头简历
            component: {
                template: '<iframe src="' + view_path + 'user/lietou/ltresume.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/school',
            name: 'school', //院校管理
            component: {
                template: '<iframe src="' + view_path + 'user/school/school.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/schoolxjh',
            name: 'schoolxjh', //宣讲会
            component: {
                template: '<iframe src="' + view_path + 'user/school/schoolxjh.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/weipin_once',
            name: 'weipin_once', //店铺招聘
            component: {
                template: '<iframe src="' + view_path + 'user/weipin/once.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/weipin_tiny',
            name: 'weipin_tiny', //普工简历
            component: {
                template: '<iframe src="' + view_path + 'user/weipin/tiny.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/renzheng',
            name: 'renzheng', //认证&审核
            component: {
                template: '<iframe src="' + view_path + 'user/users/renzheng.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/resume',
            name: 'resume', //简历管理
            component: {
                template: '<iframe src="' + view_path + 'user/users/resume.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/userscrm',
            name: 'userscrm', //个人用户
            component: {
                template: '<iframe src="' + view_path + 'user/users/userscrm.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/userset',
            name: 'userset', //个人设置
            component: {
                template: '<iframe src="' + view_path + 'user/users/userset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/xingwei',
            name: 'xingwei', //行为记录
            component: {
                template: '<iframe src="' + view_path + 'user/users/xingwei.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/zixun',
            name: 'zixun', //求职咨询
            component: {
                template: '<iframe src="' + view_path + 'user/users/zixun.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/supplyset',
            name: 'supplyset', //供求设置
            component: {
                template: '<iframe src="' + view_path + 'user/gongqiu/supplyset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/supplyuser',
            name: 'supplyuser', //供求用户
            component: {
                template: '<iframe src="' + view_path + 'user/gongqiu/supplyuser.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/supplytask',
            name: 'supplytask', //项目任务
            component: {
                template: '<iframe src="' + view_path + 'user/gongqiu/supplytask.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },{
            path: '/supplybrower',
            name: 'supplybrower', //任务浏览
            component: {
                template: '<iframe src="' + view_path + 'user/gongqiu/supplybrower.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/supplypic',
            name: 'supplypic', //图片管理
            component: {
                template: '<iframe src="' + view_path + 'user/gongqiu/supplypic.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/companycrm',
            name: 'companycrm', //企业用户
            component: {
                template: '<iframe src="' + view_path + 'user/company/companycrm.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/companyrz',
            name: 'companyrz', //认证&审核
            component: {
                template: '<iframe src="' + view_path + 'user/company/companyrz.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/companyjob',
            name: 'companyjob', //职位管理
            component: {
                template: '<iframe src="' + view_path + 'user/company/companyjob.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/companylog',
            name: 'companylog', //职位日志
            component: {
                template: '<iframe src="' + view_path + 'user/company/companylog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/companyjoblog',
            name: 'companyjoblog', //行为记录
            component: {
                template: '<iframe src="' + view_path + 'user/company/companyjoblog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/companyms',
            name: 'companyms', //面试管理
            component: {
                template: '<iframe src="' + view_path + 'user/company/companyms.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/companyset',
            name: 'companyset', //企业设置
            component: {
                template: '<iframe src="' + view_path + 'user/company/companyset.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/companyvip',
            name: 'companyvip', //套餐服务
            component: {
                template: '<iframe src="' + view_path + 'user/company/companyvip.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/jobtai',
            name: 'jobtai', //工作台
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/jobtai.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/kehugh',
            name: 'kehugh', //客户公海
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/kehugh.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/mykehu',
            name: 'mykehu', //我的客户
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/mykehu.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/myyeji',
            name: 'myyeji', //我的业绩
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/myyeji.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/followLog',
            name: 'followLog', //联系跟进
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/followLog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/taskList',
            name: 'taskList', //待办任务
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/taskList.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/outLog',
            name: 'outLog', //外出申请
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/outLog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/workLog',
            name: 'workLog', //工作日志
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/workLog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/mydingdan',
            name: 'mydingdan', //成交订单
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/mydingdan.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/crm_chat',
            name: 'crm_chat', //客勤
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/crm_chat.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/crmDataBoard',
            name: 'crmDataBoard', //数据看板
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/dataBoard.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/crmManage',
            name: 'crmManage', //CRM管理
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/crmManage.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/allFollowLog',
            name: 'allFollowLog', //跟进记录
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/followLog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/releaseLog',
            name: 'releaseLog', //释放记录
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/releaseLog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/crmOrder',
            name: 'crmOrder', //订单审批
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/crmOrder.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/allOutLog',
            name: 'allOutLog', //外出审批
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/outLog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/allTaskList',
            name: 'allTaskList', //待办任务
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/taskList.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/allWorkLog',
            name: 'allWorkLog', //工作日志
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/workLog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/crmAudit',
            name: 'crmAudit', //查账助手
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/auditAss.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/orgStructure',
            name: 'orgStructure', ///组织架构
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/orgStructure.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/crmClass',
            name: 'crmClass', //分类管理
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/crmClass.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/crmConfig',
            name: 'crmConfig', //CRM设置
            component: {
                template: '<iframe src="' + view_path + 'crm/admin/crmConfig.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/outCallStatistics',
            name: 'outCallStatistics', //数据看板
            component: {
                template: '<iframe src="' + view_path + 'crm/waihu/outCallStatistics.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/outCallLog',
            name: 'outCallLog', //外呼记录
            component: {
                template: '<iframe src="' + view_path + 'crm/waihu/outCallLog.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/outCallStaffList',
            name: 'outCallStaffList', //坐席信息
            component: {
                template: '<iframe src="' + view_path + 'crm/waihu/outCallStaffList.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/outCallSet',
            name: 'outCallSet', //外呼配置
            component: {
                template: '<iframe src="' + view_path + 'crm/waihu/outCallSet.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }, {
            path: '/rctJob',
            name: 'rctJob', //招聘管理
            component: {
                template: '<iframe src="' + view_path + 'recruit/rct/rctJob.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },{
            path: '/rctUser',
            name: 'rctUser', //求职管理
            component: {
                template: '<iframe src="' + view_path + 'recruit/rct/rctUser.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },{
            path: '/rctClass',
            name: 'rctClass', //代招类别
            component: {
                template: '<iframe src="' + view_path + 'recruit/rct/rctClass.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },
        {
            path: '/rctConfig',
            name: 'rctConfig', //代招设置
            component: {
                template: '<iframe src="' + view_path + 'recruit/rct/rctConfig.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },{
            path: '/rctApply',
            name: 'rctApply', //代招申请
            component: {
                template: '<iframe src="' + view_path + 'recruit/rct/rctApply.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },{
            path: '/recJob',
            name: 'recJob', // 职位推广 （未在导航表中，CRM-业务员-工作台-我的简报-职位推广）菜单占用ID：-101
            component: {
                template: '<iframe src="' + view_path + 'crm/yewuyuan/recJob.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        },{
            path: '/advice',
            name: 'advice', //意见建议
            component: {
                template: '<iframe src="' + view_path + 'system/role/advice.html" class="iframeAlls" frameborder="0"></iframe>'
            },
        }
    ]
})

