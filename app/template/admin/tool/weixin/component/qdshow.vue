<template>
    <div class="moduleElHight" v-loading="addloading">
        <div class="qudmshowCont">
            <div class="qudmshowshanb">
                <div class="qudmshowData">
                    <div class="qudmshowTite">
                        <span>渠道码二维码详情</span>
                    </div>
                    <div class="qudmshowErwm">
                        <div class="qudmshowErwImg">
                            <img v-if="row.work == 1" :src="row.qrcode" alt=""><!--有效二维码-->
                            <img v-else src="../../../admin/images/erm.png" alt=""><!--二维码失效-->
                        </div>
                        <div class="qudmshowErwIno">
                            <span class="spanqdm">{{ row.title }}</span>
                            <span class="spanqdm" v-if="row.expire_seconds == 0">该二维码永久有效</span>
                            <span class="spanqdm" v-else>该二维码有效期截止{{ row.expire_seconds_n }}</span>
                            <el-button type="primary" plain @click="downQdm(row.id)" v-if="row.work == 1">下载渠道码</el-button>
                        </div>
                    </div>
                    <div class="qudmshowBiaq">
                        <h3>粉丝标签</h3>
                        <div v-if="row.tags_name_arr" class="qudmshowTpis">
                            <el-tag v-for="tag in row.tags_name_arr" :key="tag" style="margin-left:5px;margin-top:5px"
                                type="success" size="mini" effect="plain">
                                {{ tag }}
                            </el-tag>
                        </div>
                        <div v-else class="qudmshowTpis">
                            未设置标签
                        </div>
                    </div>

                    <div class="qudmshowText">
                        <span class="soaoen">
                            回复方式：
                            <span v-if="row.reply == 1">{{ row.reptype == 1 ? '随机回复一条' : '全部回复' }}</span>
                            <span v-else>不回复</span>
                        </span>{{ row.reply == 1 ? '（回复内容见右图）' : '' }}
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
            <div class="qudmshowAnniu">
                <div class="layui-btn-container">
                    <el-button type="primary" plain @click="todetail">修改</el-button>
                </div>
            </div>
        </div>
    </div>
</template>
    
<script>
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
    },
    data: function () {
        return {
            id: '',
            qdarr: [],
            addloading: false,

            row: {},
            conarr: [],
            sy_logo: '',
            preCon: [],
            sy_webname: '',
        }
    },

    methods: {
        async getInfo() {

            var self = this;

            var qdarr = deepClone(self.qdarr);
            var row = null;

            for (let i in qdarr) {
                if (qdarr[i].id == self.id) {

                    row = qdarr[i];
                    break;
                }
            }

            if (!row) {
                let params = {
                    id: self.id,
                }

                this.addloading = true;
                httpPost('m=tool&c=qdm&a=getInfo', params).then((result) => {
                    this.addloading = false;
                    var res = result.data;

                    if (res.error == 0) {

                        var row = res.data.row;

                        self.sy_logo = res.data.sy_logo;

                        self.sy_webname = res.data.sy_webname;

                        self.qdarr.push(row);

                        self.row = row;

                        self.conarr = row.conarr;

                        self.previewCon();

                    }
                }).catch(function (e) {
                    console.log(e)
                })
            } else {

                self.row = row;

                self.conarr = row.conarr;

                self.previewCon();
            }

        },
        previewCon: function () {

            var that = this;

            var conarr = deepClone(this.conarr);

            var preCon = [];

            if (that.row.reply == 1) {

                if (that.row.reptype == 1) {

                    var conlen = conarr.length;

                    var index = Math.floor((Math.random() * conlen))

                    preCon.push(conarr[index]);

                } else {
                    preCon = conarr.slice(0, 3);
                }

                this.preCon = preCon;
            }

        },
        downQdm: function () {
            this.$emit('downqdm', { id: this.id });
        },
        todetail: function () {
            this.$emit('todetail', { id: this.id });
        }
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