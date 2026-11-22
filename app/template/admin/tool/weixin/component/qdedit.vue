<template>
    <div class="drawerModlue" v-loading="addloading" style="display: table;">

        <div class="qdmAddNeirzas">
            <div class="qdmAddNeirList">
                <div class="qdmAddNeirName">
                    <span>二维码名称：</span>
                </div>
                <div class="qdmAddNeirData" style="max-width: 500px;">
                    <el-input type="text" placeholder="请输入标题" v-model="title" maxlength="20" show-word-limit
                        :disabled="id == '' ? false : true"></el-input>
                </div>
            </div>
            <div class="qdmAddNeirList">
                <div class="qdmAddNeirName">
                    <span>有效期：</span>
                </div>
                <div class="qdmAddNeirData">
                    <el-radio-group v-model="row.action_name" :disabled="id == '' ? false : true">
                        <el-radio label="QR_LIMIT_STR_SCENE">永久（最多创建10万个）</el-radio>
                        <el-radio label="QR_STR_SCENE">30天</el-radio>
                    </el-radio-group>
                </div>
            </div>
            <div class="qdmAddNeirList qdmAddNeiTior">
                <div class="qdmAddNeirName">
                    <span>粉丝标签：</span>
                </div>
                <div class="qdmAddNeirData">
                    <div class="qdmAddNeirTips">
                        <div class="qdmAdNeiTipTite">
                            <span>选择下方标签即可给扫描此渠道码的粉丝打标签，方便粉丝管理。</span>
                        </div>
                        <div class="qdmAdNeiTipList">
                            <div class="tipsxuaBiaqs">
                                <p>已选标签：</p>
                                <div class="tipsxuanz">
                                    <template v-for="(tag, tKey) in communityTags">
                                        <div v-if="tag.checked" :key="tKey">
                                            <el-tooltip class="item" effect="dark" :disabled="tag.name.length < 10"
                                                :content="tag.name" placement="left-start">
                                                <span :id="'tag_' + tag.id" @click="clickTag(tag.id, false)"
                                                    class="tag_checked">{{ tag.name }}</span>
                                            </el-tooltip>
                                        </div>
                                    </template>
                                    <div>
                                        <span @click="showTags()" class="tag_nochecked">选择标签</span>
                                    </div>
                                </div>
                            </div>

                            <div class="tipsxuaFlis" v-show="tagshow">

                                <div class="tipsxuanz">
                                    <div class="tipsxuaspanse">
                                        <div class="tipsxuaAdd" v-show="addTagShow">
                                            <div class="tipsxuaLayuInt">
                                                <el-input size="mini" placeholder="请输入标签" v-model="tag_input"> </el-input>
                                            </div>
                                            <div class="jusyydsddsr">
                                                <el-button type="primary" size="mini" @click="addtag" style="padding: 0;">添加标签</el-button>
                                            </div>
                                        </div>
                                    </div>
                                    <span @click="showAddTag" v-show="!addTagShow" class="tag_nochecked spanuip">+新建标签</span>

                                    <el-tooltip v-for="ctag in communityTags" :key="ctag.id" class="item"
                                        effect="dark" :disabled="ctag.name.length < 10" :content="ctag.name"
                                        placement="left-start">
                                        <span :id="'ctag_' + ctag.id" @click="clickTag(ctag.id, !ctag.checked)"
                                            :class="ctag.checked ? 'tag_checked' : 'tag_nochecked'">{{ ctag.name }}</span>
                                    </el-tooltip>
                                </div>
                                <div class="tipsxShoqi">
                                    <a href="javascript:void(0)" @click="showTags()">收起 <img
                                            src="../../../admin/images/vipup.png" alt=""> </a>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="qdmAddNeirList">
                <div class="qdmAddNeirName">
                    <span>关注后回复：</span>
                </div>
                <div class="qdmAddNeirData">
                    <el-radio-group v-model="row.reply" @input="replyFun">
                        <el-radio label="0">不回复</el-radio>
                        <el-radio label="1">新建特定回复</el-radio>
                    </el-radio-group>

                </div>
                <div class="qdmAddNeirShuom">
                    <span>若公众号有在公众号后台或其他第三方平台设置回复内容,将会重复推送，为避免打扰用户，请按需设置</span>
                </div>
            </div>
            <div class="qdmAddNeirTuison" v-show="replyShow">
                <div class="AddNeirTuisonOne">
                    <div class="ddNeirTuisonName">
                        <span>回复方式：</span>
                    </div>
                    <div class="ddNeirTuisonData">
                        <el-radio-group v-model="row.reptype" @input="reptypeFun">
                            <el-radio label="0">全部回复（最多回复3条信息）</el-radio>
                            <el-radio label="1">随机回复一条（最多新建8条信息）</el-radio>
                        </el-radio-group>
                    </div>
                </div>
                <div class="AddNeirTuisoTite">
                    <span>推送内容：</span>
                </div>
                <div class="AddNeirTuisoTabs">
                    <div class="tuisongTabNav">
                        <ul id="contag">

                            <draggable v-model="conarr" chosen-class="chosen" handle=".divtuodong" force-fallback="true"
                                group="conarr" animation="300" @sort="dragSort">
                                <transition-group>
                                    <li :class="item.id == conid ? 'tuisongTabNavCur sort' : 'sort'"
                                        v-for="(item, cKey) in conarr" :data-index="cKey" :key="item.id"
                                        @click="tabcon(cKey)">
                                        <span class="divtexts">
                                            消息{{ cKey + 1 }} {{ item.msgtype_n }}
                                        </span>

                                        <el-tooltip class="item" effect="dark" content="长按图标可拖动排序" placement="right-start">
                                            <div class="divtuodong">
                                                <img src="../../../admin/images/appset_tz.png">
                                            </div>
                                        </el-tooltip>
                                    </li>
                                </transition-group>
                            </draggable>
                        </ul>
                        <ul>
                            <li v-if="conarr.length < 8" @click="tabcon('')">新建+</li>
                        </ul>
                    </div>
                    <div class="tuisongTabCont">

                        <div class="tuisTabContList tuisTabContOn" v-for="(item, cKey) in conarr" :key="item.id"
                            v-show="item.id == conid">
                            <div class="tuisTabContInfo">
                                <div class="tuisTabContData">
                                    <div class="tuisTabContDanx">
                                        <span>消息类型：</span>

                                        <el-radio-group v-model="item.msgtype" @input="msgtypeFun">
                                            <el-radio label="text">文字</el-radio>
                                            <el-radio label="image">图片</el-radio>
                                            <el-radio label="news">图文</el-radio>
                                            <el-radio label="xcx">小程序卡片</el-radio>
                                        </el-radio-group>
                                    </div>

                                    <!--文字类型-->
                                    <div v-if="item.msgtype == 'text'">
                                        <div class="tuisTabConInpy" style="align-items: initial; flex-wrap: wrap;">
                                            <div class="spantite" style="width: initial;">
                                                <span>回复内容：</span>
                                            </div>
                                            <div class="spancont" style="width: 100%; margin-top: 8px;">
                                                <el-input type="textarea" :rows="2" placeholder="请输入回复内容"
                                                    v-model="item.content" @input="setconval" maxlength="255"
                                                    show-word-limit></el-input>
                                            </div>
                                        </div>
                                    </div>
                                    <!--图文类型-->
                                    <div v-if="item.msgtype == 'news'">
                                        <div class="tuisTabConInpy">
                                            <div class="spantite">
                                                <span>标题：</span>
                                            </div>
                                            <div class="spancont">
                                                <el-input type="text" placeholder="请输入标题" v-model="item.news_title"
                                                    @input="setconval" maxlength="255"></el-input>
                                            </div>
                                        </div>
                                        <div class="tuisTabConInpy" style="align-items: initial;">
                                            <div class="spantite">
                                                <span>描述：</span>
                                            </div>
                                            <div class="spancont">
                                                <el-input type="textarea" :rows="2" placeholder="请输入描述"
                                                    v-model="item.news_content" @input="setconval"
                                                    maxlength="255"></el-input>
                                            </div>


                                        </div>
                                        <div class="tuisTabConInpy layui-form-item">
                                            <div class="spantite">
                                                <span>链接：</span>
                                            </div>
                                            <div class="spancont">
                                                <el-input type="text" placeholder="请输入标题" v-model="item.news_url"
                                                    @input="setconval" maxlength="255"></el-input>
                                            </div>


                                        </div>
                                    </div>
                                    <!--小程序卡片-->
                                    <div v-if="item.msgtype == 'xcx'">
                                        <el-alert :closable="false" style="margin-top: 12px;">
                                            <span>发送小程序卡片需与当前公众号关联，前往微信公众平台-公众号-广告与服务-小程序管理可查看关联小程序。<el-link type="primary"
                                                    href="https://mp.weixin.qq.com/"
                                                    target="_blank">查看已关联的小程序</el-link></span>
                                        </el-alert>
                                        <div class="tuisTabConInpy">
                                            <div class="spantite">
                                                <span class="spantite">卡片标题：</span>
                                            </div>
                                            <div class="spancont">
                                                <el-input type="text" placeholder="请输入标题" v-model="item.xcx_title"
                                                    @input="setconval" maxlength="255"></el-input>
                                            </div>


                                        </div>
                                        <div class="tuisTabConInpy">
                                            <div class="spantite">
                                                <span class="spantite">小程序AppID：</span>
                                            </div>
                                            <div class="spancont">
                                                <el-input type="text" placeholder="请填写小程序AppID" v-model="item.xcx_appid"
                                                    @input="setconval" maxlength="255"></el-input>
                                            </div>


                                        </div>
                                        <div class="tuisTabConInpy">
                                            <div class="spantite">
                                                <span class="spantite">小程序路径：</span>
                                            </div>
                                            <div class="spancont">
                                                <el-input type="text" placeholder="请输入跳转小程序页面示例: pages/index/index"
                                                    v-model="item.xcx_pagepath" @input="setconval"
                                                    maxlength="255"></el-input>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="tuisTabConImage"
                                        v-show="item.msgtype == 'image' || item.msgtype == 'xcx' || item.msgtype == 'news'">
                                        <div class="spantite">
                                            <span>{{ item.msgtype == 'xcx' ? '封面图' : '图片' }}：</span>
                                        </div>
                                        <div class="spancont">
                                            <el-upload :action="uploadAction" class="avatar-uploader" :auto-upload="false"
												:show-file-list="false" :multiple="false" :accept="pic_accept"
                                                :on-change="choosePic" list-type="picture">
                                                <div v-if="item.image_n">
                                                    <el-image style="width: 88px; height: 88px; margin-top: 8px;"
                                                        :src="item.image_n" fit="contain"></el-image>
                                                </div>

                                                <el-button slot="trigger" size="small" type="primary">选取文件</el-button>
                                                <div slot="tip" class="el-upload__tip" v-show="item.msgtype == 'xcx'">
                                                    建议大小520*416，大小2M</div>
                                                <div slot="tip" class="el-upload__tip" v-show="item.msgtype == 'news'">
                                                    支持JPG、PNG格式，较好的效果为大图360*200，小图200*200</div>
                                            </el-upload>
                                        </div>




                                    </div>
                                </div>

                            </div>
                            <div class="tuisTabConClose">
                                <el-button type="primary" @click="delcon(cKey)" icon="el-icon-delete-solid"
                                    plain>删除此条信息</el-button>
                            </div>
                        </div>
                    </div>
                    <div class="qudmshowPhone">
                        <div class="qudmshoPhoCont">

                            <div class="qudmshoPhoData">
                                <ul>
                                    <li v-for="(pre, pKey) in preCon" :key="pKey">
                                        <div class="qudmshoPhoLogo">
                                            <img :src="sy_logo" alt="">
                                        </div>
                                        <!--预览-文字-->
                                        <div class="qudmshoPhoInfo" v-if="pre.msgtype == 'text'">
                                            <div class="qudmshoNumber">
                                                <span>{{ pre.content }}</span>
                                            </div>
                                        </div>
                                        <!--预览-图片-->
                                        <div class="qudmshoPhoInfo" v-if="pre.msgtype == 'image'">
                                            <div class="qudmshoNumber">
                                                <img :src="pre.image_n" alt="">
                                            </div>
                                        </div>
                                        <!--预览-图文-->
                                        <div class="qudmshoPhoInfo" v-if="pre.msgtype == 'news'">
                                            <div class="qudmshoNumber">
                                                <div class="qudmshoNumCom">
                                                    <div class="qudmshoCompaName">
                                                        <span>{{ pre.news_title }}</span>
                                                    </div>
                                                    <div class="qudmshoCompaInfo">
                                                        <div class="qudmshoCompZaiy">
                                                            <span>{{ pre.news_content }}</span>
                                                        </div>
                                                        <div class="qudmshoCompLogo">
                                                            <img :src="pre.image_n" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--预览-小程序-->
                                        <div class="qudmshoPhoInfo" v-if="pre.msgtype == 'xcx'">
                                            <div class="qudmshoNumber">
                                                <div class="qudmshoNumCom qudmshoNumXicx">
                                                    <div class="qudmshoComGons">
                                                        <img src="../../../admin/images/appdiy_login1.png" alt="">
                                                        <span>{{ sy_webname }}</span>
                                                    </div>
                                                    <div class="qudmshoCompaName">
                                                        <span>{{ pre.xcx_title }}</span>
                                                    </div>
                                                    <div class="qudmshoCompaInfo">
                                                        <div class="qudmshoCompLogo">
                                                            <img :src="pre.image_n" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="qudmshoComBotto">
                                                        <img src="../../../admin/images/xiaochengx.png" alt="">
                                                        <span>小程序</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="overflow: hidden; position: relative; width: 100%; display: flex; align-items: center;justify-content: center; padding: 15px;">
            <el-button type="primary" style="width: 150px;" @click="checkform" :loading="post_loading">保存</el-button>
        </div>


    </div>
</template>
    
<script>
const defaultcon = {
    id: '',
    msgtype: 'text',
    sort: '',
    msgtype_n: '文本',
    media_id: '',
    image_n: '',
    newimage: '',
    image_v: '',//只用于图文消息的原始图片数据判断是否原始值有图片
    content: '',
    news_title: '',
    news_content: '',
    news_url: '',
    xcx_title: '',
    xcx_appid: '',
    xcx_pagepath: '',

    isadd: 1,
    addpic: 0,
};
module.exports = {
    props: {
        qid: {
            type: [String, Number],
            default: ''
        },
    },
    watch: {
        qid: {
            handler(val, oldVal) {
                this.id = val;
                this.getInfo();
            },
            immediate: true,
        },
        conarr: {
            handler(val, oldVal) {
                this.addable = this.conarr.length < 8 ? true : false;
            },
        },
    },
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            id: '',
            sy_webname: '',
            sy_logo: '',
            addloading: false,

            row: {},
            title: '',
            conarr: [],
            conid: '',

            replyShow: false,
            con_post: [],
            del_idarr: [],

            preCon: [],//预览回复内容

            tagshow: false,
            addTagShow: false,
            tag_input: '',
            communityTags: [],//微信公众号的所有标签 包含页面里新加的 结构{id:xx,name:xx,checked:false,isadd:false}
            tagid_arr: [],//打开页面前 已选的tagid

            addable: true,
            conkey: '0',

            newpic: [],

            post_loading: false,
            uploadAction: baseUrl + 'm=common&c=common_upload'
        }
    },
    components: {
        'draggable': window.vuedraggable,
    },
    methods: {
        async getInfo() {

            var self = this;

            let params = {
                id: self.id,
            }

            this.addloading = true;
            httpPost('m=tool&c=qdm&a=getInfo', params).then((result) => {
                this.addloading = false;
                var data = result.data;

                if (data.error == 0) {

                    var res = data.data;

                    if (typeof res.row != 'undefined') {

                        self.row = res.row;

                        self.tagid_arr = res.row.tagid_arr.map(Number);

                        self.title = res.row.title;

                        self.replyShow = res.row.reply == '1' ? true : false;

                        var conarr = res.row.conarr;

                        for (let i in conarr) {
                            for (let key in defaultcon) {
                                conarr[i][key] = typeof conarr[i][key] != 'undefined' ? conarr[i][key] : defaultcon[key];
                            }
                            conarr[i].isadd = 0;
                            conarr[i].addpic = 0;
                        }

                        self.conarr = conarr;

                        if (res.row.reply == '1') {
                            self.tabcon(0);
                        }

                    } else {
                        self.row = {
                            reply: 0,
                            reptype: 0,
                        };
                    }

                    var communityTags = [];

                    if (res.communityTags) {

                        var checked = false;

                        for (let i in res.communityTags) {

                            checked = self.tagid_arr.indexOf(res.communityTags[i].id) == -1 ? false : true;

                            res.communityTags[i]['checked'] = checked;

                            res.communityTags[i]['isadd'] = false;

                            communityTags.push(res.communityTags[i]);
                        }
                    }

                    self.communityTags = communityTags;

                    self.sy_logo = res.sy_logo;

                    self.sy_webname = res.sy_webname;

                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        previewCon: function () {

            var that = this;

            var conarr = deepClone(this.conarr);

            var preCon = [];

            var reptype = this.row.reptype;

            if (reptype == 1) {

                for (let i in conarr) {
                    if (conarr[i].id == this.conid) {
                        preCon.push(conarr[i]);
                        break;
                    }
                }
            } else {
                preCon = conarr.slice(0, 3);
            }
            this.preCon = preCon;
        },
        tabcon: function (ckey) {

            var self = this;
            self.conid = '';
            var msgtype = 'text';

            if (ckey !== '' && self.conarr[ckey]) {
                var con = self.conarr[ckey];
                self.conid = con.id;
                msgtype = con.msgtype;
                self.conkey = '' + ckey;
            } else {

                if (self.conarr.length >= 8) {
                    message.warning('最多只能添加8条消息！'); return;
                }
                var randnum = parseInt(Math.random() * 1000);
                var con = deepClone(defaultcon);
                con.id = randnum;
                con.sort = self.conarr.length;
                self.conarr.push(con);
                self.conid = randnum;
                self.conkey = '' + (self.conarr.length - 1);
            }

            this.previewCon();

        },
        replyFun: function () {

            this.replyShow = this.row.reply == 1 ? true : false;

            var conarr = deepClone(this.conarr);
            if (this.row.reply == 1) {
                var tkey = '';
                if (conarr.length > 0) {
                    tkey = 0;
                }
                this.tabcon(tkey);
            }
            this.previewCon();
        },
        reptypeFun: function () {

            this.previewCon();
        },
        msgtypeFun: function (val) {
            var ckey = parseInt(this.conkey);
            var conarr = deepClone(this.conarr);

            var msgtype = val;
            var msgtype_n = '文本';
            if (msgtype == 'image') {
                msgtype_n = '图片';
            } else if (msgtype == 'news') {
                msgtype_n = '图文';
            } else if (msgtype == 'xcx') {
                msgtype_n = '小程序';
            }
            conarr[ckey].msgtype_n = msgtype_n;

            this.conarr = conarr;

            this.previewCon();
        },
        choosePic: function (file, fileList) {

            var conkey = parseInt(this.conkey);
            var conarr = deepClone(this.conarr);
            conarr[conkey].image_n = file.url;
            conarr[conkey].newimage = file.url;
            conarr[conkey].addpic = 1;
            this.newpic.push({ id: conarr[conkey].id, file: file.raw });
            this.conarr = conarr;
            this.previewCon();
        },
        delcon: function (key) {
            var self = this;
            var conarr = deepClone(self.conarr);
            var del_idarr = deepClone(self.del_idarr);
            if (conarr[key].isadd == 0) {
                del_idarr.push(conarr[key].id);
            }
            self.del_idarr = del_idarr;
            self.conarr.splice(key, 1);
            var tabcon_key = self.conarr.length > 0 ? 0 : '';
            self.tabcon(tabcon_key);
            this.previewCon();
        },
        //拖动排序，推送内容数组重排
        dragSort: function () {

            var conarr = deepClone(this.conarr);

            for (let i in conarr) {
                conarr[i].sort = i;
            }
            this.conarr = conarr;
            this.previewCon();
        },
        setconval: function (e) {

            this.previewCon();
        },
        //新加标签 不能和已有标签重复
        addtag: function () {
            var self = this;
            if(self.tag_input.trim()==''){
                message.warning('请填写标签！');
                return false;
            }
            var communityTags = deepClone(this.communityTags);
            var exist = communityTags.filter(function (item, index, arr) {
                return item.name.trim() == self.tag_input.trim()
            });

            if (exist.length > 0) {
                message.warning('该标签已存在！');
                return false;
            }

            var random = new Date().getTime() + '' + parseInt(Math.random() * 1000);

            this.communityTags.push({
                id: random,
                name: this.tag_input,
                isadd: true,
                checked: true
            });

            this.tag_input = '';
        },
        clickTag: function (tagid, checked) {

            var self = this;

            var communityTags = deepClone(this.communityTags);

            if (checked) {//最多只能选20个标签
                var checkedtags = communityTags.filter(function (item, index, arr) {
                    return item.checked;
                });
                if (checkedtags.length == 20) {
                    message.warning('最多只能选20个标签！'); return;
                    return false;
                }
            }

            for (let i in communityTags) {

                if (communityTags[i].id == tagid) {

                    communityTags[i].checked = checked;

                }
            }

            this.communityTags = communityTags;
        },

        showAddTag: function () {
            this.addTagShow = !this.addTagShow;
        },
        showTags: function () {
            this.tagshow = !this.tagshow;

            this.addTagShow = !this.tagshow;

        },
        checkform: function () {

            var self = this;

            var conarr = deepClone(self.conarr);
            var row = deepClone(self.row);
            var id = '';
            var msgtype = '';
            var errmsg = '';
            var isadd = 0;
            var con_post = [];

            var action_name = row.action_name;

            if (self.title.trim() == '') {
                message.warning('请输入二维码名称！');
                return false;
            } else if (typeof action_name == 'undefined' || action_name == '') {
                message.warning('请选择二维码有效期！');
                return false;
            }

            for (let i in conarr) {

                errmsg = '消息' + parseInt(i + 1) + '的';

                id = conarr[i].id;

                msgtype = conarr[i].msgtype;

                isadd = conarr[i].isadd;

                var conval = {
                    id: id,
                    msgtype: msgtype,
                    isadd: isadd,
                    sort: conarr[i].sort,
                    addpic: conarr[i].addpic,
                };

                if (msgtype == 'image') {

                    if (conarr[i].media_id == '' && conarr[i].newimage == '') {
                        message.warning(errmsg + '图片不能为空！');
                        return false;
                    }
                    conval.newimage = conarr[i].newimage;
                    conval.media_id = conarr[i].media_id;

                } else if (msgtype == 'news') {

                    if (conarr[i].news_title == '') {
                        message.warning(errmsg + '图文标题不能为空！');
                        return false;
                    }
                    if (conarr[i].news_content == '') {
                        message.warning(errmsg + '图文描述不能为空！');
                        return false;
                    }
                    if (conarr[i].newimage == '' && conarr[i].image_v == '') {
                        message.warning(errmsg + '图文图片不能为空！');
                        return false;
                    }
                    if (conarr[i].news_url == '') {
                        message.warning(errmsg + '图文链接不能为空！');
                        return false;
                    }

                    conval.news_title = conarr[i].news_title;
                    conval.news_content = conarr[i].news_content;
                    conval.news_url = conarr[i].news_url;
                    conval.newimage = conarr[i].newimage;
                    conval.image_v = conarr[i].image_v;
                    conval.image_n = conarr[i].image_n;

                } else if (msgtype == 'xcx') {

                    if (conarr[i].xcx_title == '') {
                        message.warning(errmsg + '卡片标题不能为空！');
                        return false;
                    }
                    if (conarr[i].xcx_appid == '') {
                        message.warning(errmsg + '小程序AppID不能为空！');
                        return false;
                    }
                    if (conarr[i].xcx_pagepath == '') {
                        message.warning(errmsg + '小程序路径不能为空！');
                        return false;
                    }
                    if (conarr[i].media_id == '' && conarr[i].newimage == '') {
                        message.warning(errmsg + '小程序封面图不能为空！');
                        return false;
                    }

                    conval.xcx_title = conarr[i].xcx_title;
                    conval.xcx_appid = conarr[i].xcx_appid;
                    conval.xcx_pagepath = conarr[i].xcx_pagepath;
                    conval.newimage = conarr[i].newimage;
                    conval.media_id = conarr[i].media_id;
                    conval.image_n = conarr[i].image_n;

                } else {

                    if (conarr[i].content == '') {
                        message.warning(errmsg + '文本内容不能为空！');
                        return false;
                    }

                    conval.content = conarr[i].content;
                }

                con_post.push(conval);

                self.con_post = con_post;
            }
            self.saveform();

        },
        saveform: function () {
            var self = this;

            var params = new FormData();
            var row = deepClone(self.row);
            var newpic = self.newpic;

            var con_post = [];

            var communityTags = deepClone(self.communityTags);

            var checkedtags = communityTags.filter(function (item, index, arr) {
                return item.checked;
            });

            if (row.reply == '1') {

                con_post = deepClone(self.con_post);

                for (let i in con_post) {
                    if (con_post[i].msgtype == 'text') {
                        con_post[i].content = con_post[i].content.replace(/"/g, '&quot;').replace(/'/g, '&apos;');
                    } else if (con_post[i].msgtype == 'news') {
                        con_post[i].news_title = con_post[i].news_title.replace(/"/g, '&quot;').replace(/'/g, '&apos;');
                        con_post[i].news_content = con_post[i].news_content.replace(/"/g, '&quot;').replace(/'/g, '&apos;');
                    } else if (con_post[i].msgtype == 'xcx') {
                        con_post[i].xcx_title = con_post[i].xcx_title.replace(/"/g, '&quot;').replace(/'/g, '&apos;');
                    }
                    if (con_post[i].addpic == 1) {
                        for (let j in newpic) {
                            if (newpic[j].id == con_post[i].id) {
                                params.append('newpic[]', newpic[j].file);
                            }
                        }
                    }
                }
            }


            var conpost = JSON.stringify(con_post);

            var tagspost = JSON.stringify(checkedtags);

            params.append('id', self.id);
            params.append('title', self.title);
            params.append('action_name', self.row.action_name);
            params.append('reply', self.row.reply);
            params.append('reptype', self.row.reptype);
            params.append('content', conpost);
            params.append('preview', tagspost);
            params.append('del_idarr', self.del_idarr);

            self.post_loading = true;

            httpPost('m=tool&c=qdm&a=saveWxqd'
                , params
                , {
                    headers: { 'Content-Type': 'multipart/form-data' },
                }
            ).then((result) => {

                self.post_loading = false;

                var res = result.data;
                if (res.error == 0) {
                    message.success(res.msg, () => {
                        self.$emit('editclose');
                    });
                } else {
                    message.error(res.msg);
                }

            }).catch(function (e) {
                console.log(e)
            })

        },
    },
};
</script>
<style scoped>
.moduleSeachmore {
    padding: 0px;
}

.moduleSeachs {
    padding: 0px 0px 12px 0px;
    width: 100%;
}

.moduleElTable {
    padding: 0;
    margin: 0;
    height: calc(100% - 136px);
    width: 100%;
}

.tableSeachInptsmalltwo {
    margin-bottom: 0px;
    margin-right: 12px;
}

.tableSeachInptsmalltwo .el-input__inner {
    height: 32px;
    line-height: 32px;
    width: 260px;
    padding: 0px 5px;
    ;
}

.el-dialog__body {
    padding: 0px 20px;
}
</style>