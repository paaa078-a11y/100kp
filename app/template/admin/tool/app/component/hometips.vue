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
                            <div class="TableTite">提示标题</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入提示标题" v-model="config.xyboxtitle">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">提示内容</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input type="textarea" :rows="5" placeholder="请输入提示内容" v-model="config.xyboxcontent">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>提示内容</span>
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
                xyboxtitle: '',
                xyboxcontent: '',
            },
            saveLoading: false
        }
    },
    mounted() {

    },
    methods: {
        async getinfo() {
            let that = this;
            httpPost('m=tool&c=app_set&a=xybox', {}).then(function (result) {
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
            httpPost('m=tool&c=app_set&a=savexybox', that.config).then(function (res) {
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