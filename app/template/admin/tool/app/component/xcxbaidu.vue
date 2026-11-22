<template>
    <div class="moduleElHight">
        <div class="tableDome_tip">
            <!--<el-alert title="公众号为认证服务号，到微信公众号平台设置服务器IP白名单" type="success" :closable="false"></el-alert>-->
        </div>
        <div class=" moduleTable"  style="max-height: calc(100% - (60px + 10px));">
            <el-divider content-position="left">百度小程序登录设置</el-divider>
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="200">名称</th>
                    <th width="450">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">appKey</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入appKey" v-model="config.sy_bdlogin_appKey">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>配置后可以使用百度联合登录。到智能小程序平台，管理-基础设置-开发设置中查看</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">appSecret</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入appSecret" v-model="config.sy_bdlogin_appSecret">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>配置后可以使用百度联合登录。到智能小程序平台，管理-基础设置-开发设置中查看</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <el-divider content-position="left">百度小程序支付设置</el-divider>
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="200">名称</th>
                    <th width="450">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">dealId</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入dealId" v-model="config.sy_dealId">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>支付开通后，到智能小程序平台，管理-功能管理-支付管理中查看</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">appKey</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入appKey" v-model="config.sy_appKey">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>支付开通后，到智能小程序平台，管理-功能管理-支付管理中查看</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">开发者私钥</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input type="textarea" :rows="2" placeholder="请输入开发者私钥" v-model="config.sy_privateKey">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>必须配置，通过openssl工具生成</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">小程序平台公钥</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input type="textarea" :rows="2" placeholder="请输入小程序平台公钥" v-model="config.sy_publicKey">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>必须配置，支付开通后到智能小程序平台，支付管理中查看（注意是平台公钥，不是开发者公钥）</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">百度小程序二维码</div>
                    </td>
                    <td>
                        <div class="TableUpload">
                            <el-upload class="upload-demo" :accept="pic_accept" :action="uploadurl" :on-success="handleQrcodeSuccess"
                                       list-type="picture" :show-file-list="false" :data="qrcodeparams">
                                <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                                <img class="el-upload-list__item-thumbnail" width="140" style="padding-left: 5px;" v-if="sy_baiduxcxu_qcode"
                                     :src="sy_baiduxcxu_qcode"/>
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>百度小程序二维码</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">IOS支付功能</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_baidu_iospay">
                            </el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>IOS平台不支持虚拟产品购买，提交审核请关闭，否则将被驳回</span>
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
                    sy_bdlogin_appKey: '',
                    sy_bdlogin_appSecret: '',
                    sy_dealId: '',
                    sy_appKey: '',
                    sy_privateKey: '',
                    sy_publicKey: '',
                    sy_baidu_iospay: false
                },
                sy_baiduxcxu_qcode: '',
                uploadurl: baseUrl + 'm=index&c=layui_upload',
                qrcodeparams: {name: 'sy_baiduxcxu_qcode', imgid: 'imgbaidu', path: 'logo', pytoken: localStorage.getItem("pytoken")},
                saveLoading: false
            }
        },
        mounted() {

        },
        methods: {
            handleQrcodeSuccess(res) {
                if (res.code == 0) {
                    this.sy_baiduxcxu_qcode = res.data.url
                } else {
                    this.$message.error(res.msg);
                }
            },
            async getinfo() {
                let that = this;
                httpPost('m=tool&c=xcxset&a=baiduindex', {}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.config = res.data.config
                        that.sy_baiduxcxu_qcode = res.data.sy_baiduxcxu_qcode
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            save() {
                let that = this;
                that.saveLoading = true;
                httpPost('m=tool&c=xcxset&a=baidusave', that.config).then(function (res) {
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