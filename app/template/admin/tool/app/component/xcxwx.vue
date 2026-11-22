<template>
    <div class="moduleElHight">
        <div class="tableDome_tip">
            <el-alert
                    title="微信小程序设置"
                    type="success"
                    description="" :closable="false">
                1、配置完成，可在小程序里面快捷登录！<br/>
                2、配置完成，请联系授权客服提交代码！
            </el-alert>
        </div>
        <div class="moduleTable" style="max-height: calc(100% - (70px + 80px));">
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="200">名称</th>
                    <th width="500">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">xcxAPPID</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入xcxAPPID" v-model="config.sy_xcxappid">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>绑定支付的小程序APPID（必须配置，小程序平台可查看）</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">xcxAppSecret</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入xcxAppSecret" v-model="config.sy_xcxsecret">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>绑定支付的小程序AppSecret（必须配置，小程序平台可查看）</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">原始ID</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入原始ID" v-model="config.sy_xcxysid">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>微信小程序原始ID（使用视频面试功能必须配置，小程序平台-设置-基本设置中可查看）</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">微信小程序二维码</div>
                    </td>
                    <td>
                        <div class="TableUpload">
                            <el-upload class="upload-demo" :accept="pic_accept" :action="uploadurl" :on-success="handleQrcodeSuccess"
                                       list-type="picture" :show-file-list="false" :data="qrcodeparams">
                                <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                                <img class="el-upload-list__item-thumbnail" width="140" height="140" style="padding-left: 5px;" v-if="sy_weixinxcxu_qcode" :src="sy_weixinxcxu_qcode"/>
                                <!-- <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div> -->
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>微信小程序二维码</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">小程序名称</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入小程序名称" v-model="config.sy_xcxname">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>仅针对公众号关注回复、场景二维码生效。公众号必须关联小程序</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">小程序默认页面</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入小程序默认页面" v-model="config.sy_xcxpath">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>仅针对公众号关注回复、场景二维码生效，默认pages/index/index。公众号必须关联小程序</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">小程序封面</div>
                    </td>
                    <td>
                        <div class="TableImage">
                            <el-upload class="upload-demo" :action="uploadurl" accept=".jpg,.png,.jpeg,.gif" :on-success="handleXcxPicSuccess"
                                       list-type="picture" :show-file-list="false" :data="xcxpicparams">
                                <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                                <img class="el-upload-list__item-thumbnail" width="140" style="padding-left: 5px;" v-if="sy_xcxpic"
                                     :src="sy_xcxpic"/>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>封面尺寸：520 * 416</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">微信小程序码展示</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_xcxQrcode">
                            </el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>电脑端职位详细页和企业详细页小程序码展示，只支持已发布的小程序</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">IOS支付功能</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_wxxcx_iospay">
                            </el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>IOS平台不支持虚拟产品购买，提交审核请关闭，否则将被驳回</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">微信开放平台</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input v-model="config.mini_wxopen" readonly>
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>用于快速排查小程序是否绑定开放平台。开放平台绑定后需有用户在小程序内使用微信登录才能触发</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">视频号ID</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入视频号ID" v-model="config.sy_sphid">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>以“sph”开头的id，可在视频号助手获取。配置后，微信视频号直播时，微信小程序首页可显示打开视频号直播入口，方面用户快速进入。</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="save" :loading="saveLoading">提交</el-button>
        </div>
    </div>
</template>
<script>
    module.exports = {
        data: function () {
            return {
                pic_accept: localStorage.getItem("pic_accept"),
                config: {
                    sy_xcxappid: '',
                    sy_xcxsecret: '',
                    sy_xcxysid: '',
                    sy_xcxname: '',
                    sy_xcxpath: '',
                    sy_xcxQrcode: false,
                    sy_wxxcx_iospay: false,
                    mini_wxopen: '',
                    sy_sphid: ''
                },
                sy_xcxpic: '',
                sy_weixinxcxu_qcode: '',
                uploadurl: baseUrl + 'm=index&c=layui_upload',
                qrcodeparams: {name: 'sy_weixinxcxu_qcode', path: 'logo', imgid: 'imgweixin', pytoken: localStorage.getItem("pytoken")},
                xcxpicparams: {name: 'sy_xcxpic', path: 'logo', imgid: 'imgxcxpic', pytoken: localStorage.getItem("pytoken")},
                saveLoading: false
            }
        },
        mounted() {

        },
        methods: {
            handleQrcodeSuccess(res) {
                if (res.code == 0) {
                    this.sy_weixinxcxu_qcode = res.data.url
                } else {
                    this.$message.error(res.msg);
                }
            },
            handleXcxPicSuccess(res) {
                if (res.code == 0) {
                    this.sy_xcxpic = res.data.url
                } else {
                    this.$message.error(res.msg);
                }
            },
            async getinfo() {
                let that = this;
                httpPost('m=tool&c=xcxset&a=index', {}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.config = res.data.config
                        that.sy_xcxpic = res.data.sy_xcxpic
                        that.sy_weixinxcxu_qcode = res.data.sy_weixinxcxu_qcode
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            save() {
                let that = this;
                that.saveLoading = true;
                httpPost('m=tool&c=xcxset&a=save', that.config).then(function (res) {
                    if (res.data.error == 0) {
                        message.success(res.data.msg);
                    } else {
                        message.error(res.data.msg);
                    }
                }).finally(function () {
                    setTimeout(function () {
                        that.saveLoading = false;
                    }, 2000);
                });
            },
        },
    };
</script>
<style scoped></style>