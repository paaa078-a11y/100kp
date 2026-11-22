<template>
    <div class="moduleElHight">
        <div class="tableDome_tip">
            <!--<el-alert title="可以使用QQ/163等邮箱，465端口需要开启SSL服务，注意邮箱密码不是账户密码一般是独立授权密码" type="success" :closable="false">-->
            <!--</el-alert>-->
        </div>
        <div class="moduleTable" style="max-height: calc(100% - (60px + 25px));">
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
                            <div class="TableTite">IOS版本名称</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入IOS版本名称" v-model="config.iosversion">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>新版本未上架成功前不要改动</span>
                            </div>
                        </td>
                    </tr>
					<tr>
                        <td>
                            <div class="TableTite">IOS更新链接</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入IOS更新链接" v-model="config.iosurl">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>一般不需要改动</span>
                            </div>
                        </td>
                    </tr>
					<tr>
                        <td>
                            <div class="TableTite">Android版本名称</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入Android版本名称" v-model="config.androidversion">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>新版本未上架成功前不要改动</span>
                            </div>
                        </td>
                    </tr>
					<tr>
                        <td>
                            <div class="TableTite">Android更新链接</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入Android更新链接" v-model="config.androidurl">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>一般不需要改动</span>
                            </div>
                        </td>
                    </tr>
					<tr>
                        <td>
                            <div class="TableTite">更新标题</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入更新标题" v-model="config.apptitle">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>有信息版本改动时 修改版本号</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">更新说明</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input type="textarea" :rows="2" placeholder="请输入更新说明" v-model="config.appcontent">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>有新的版本更新时可以适当改动</span>
                            </div>
                        </td>
                    </tr>
					<tr>
                        <td>
                            <div class="TableTite">强制更新</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-switch v-model="config.sy_app_upforce" active-color="#13ce66" inactive-color="#ff4949">
                                </el-switch>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>大的版本升级时，可以选择强制更新</span>
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
                iosversion: '',
                iosurl: '',
                androidversion: '',
                androidurl: '',
                apptitle: '',
                appcontent: '',
                sy_app_upforce: false
            },
            saveLoading: false
        }
    },
    mounted() {

    },
    methods: {
        async getinfo() {
            let that = this;
            httpPost('m=tool&c=app_set&a=version', {}).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.config = res.data.config
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        save() {
            let that = this;
            that.saveLoading = true;
            httpPost('m=tool&c=app_set&a=saveversion', that.config).then(function (res) {
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