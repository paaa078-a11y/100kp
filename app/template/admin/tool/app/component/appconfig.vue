<template>
    <div class="moduleElHight">
        <div class="tableDome_tip">
            <!--<el-alert title="公众号为认证服务号，到微信公众号平台设置服务器IP白名单" type="success" :closable="false">-->
            <!--</el-alert>-->
        </div>
        <div class=" moduleTable" style="max-height: calc(100% - (60px + 25px));">
            <!-- <el-divider content-position="left">公共设置</el-divider> -->
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="200">名称</th>
                    <th width="150">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">启用APP</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_app_open">
                            </el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>启用APP</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <el-divider content-position="left">推送设置</el-divider>
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="200">名称</th>
                    <th width="400">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">启用推送</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_push_open">
                            </el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>启用推送</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">AppID</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入AppID" v-model="config.sy_push_appid">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>AppID</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">AppKey</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入AppKey" v-model="config.sy_push_appkey">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>AppKey</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">AppSecret</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入AppSecret" v-model="config.sy_push_appsecret">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>AppSecret</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">MasterSecret</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入MasterSecret" v-model="config.sy_push_masterSecret">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>MasterSecret</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <el-divider content-position="left">移动端设置</el-divider>
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="200">名称</th>
                    <th width="150">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">移动端支付功能</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_iospay">
                            </el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>移动端支付功能</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">移动端定位服务功能</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_ioslocation">
                            </el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>移动端定位服务功能</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">微信登录</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_app_wxlogin">
                            </el-switch>
                            <div v-if="config.sy_app_wxlogin" class="chat_hf_showbox">
                                <div class="chat_hf_show">
                                    <el-input v-model="config.sy_app_wxappid" placeholder="请输入APPID"></el-input>
                                </div>
                                <div class="chat_hf_show">
                                    <el-input v-model="config.sy_app_wxsecret" placeholder="请输入AppSecret"></el-input>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>微信登录</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">QQ登录</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_app_qqlogin">
                            </el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>QQ登录</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">微信客服</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-switch v-model="config.sy_app_wxkf">
                            </el-switch>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>微信客服</span>
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
                config: {
                    sy_app_open: false,
                    sy_push_open: false,
                    sy_push_appid: '',
                    sy_push_appkey: '',
                    sy_push_appsecret: '',
                    sy_push_masterSecret: '',
                    sy_iospay: false,
                    sy_ioslocation: false,
                    sy_app_wxkf: false,
                    sy_app_qqlogin: false,
                    sy_app_wxlogin: false,
                    sy_app_wxsecret:'',
                    sy_app_wxappid:''
                },
                saveLoading: false
            }
        },
        mounted() {

        },
        methods: {
            async getinfo() {
                let that = this;
                httpPost('m=tool&c=app_set&a=index', {}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.config = res.data.config
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            async save() {
                let that = this;
                that.saveLoading = true;
                httpPost('m=tool&c=app_set&a=save', that.config).then(function (res) {
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