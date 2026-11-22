<template>
    <div class="moduleElHight">
        <!--<div class="tableDome_tip">-->
            <!--<el-alert title="可以使用QQ/163等邮箱，465端口需要开启SSL服务，注意邮箱密码不是账户密码一般是独立授权密码" type="success" :closable="false">-->
            <!--</el-alert>-->
        <!--</div>-->
        <div class="moduleTable">
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
                        <div class="TableTite">宣讲会开播模板</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入宣讲会开播模板" v-model="config.xjh_start_tplid">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>宣讲会开播模板</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">面试邀请模板</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入面试邀请模板" v-model="config.interview_tplid">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>面试邀请模板</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">支付成功模板</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入支付成功模板" v-model="config.payment_tplid">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>支付成功模板</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">职位审核模板</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input placeholder="请输入职位审核模板" v-model="config.job_audit_tplid">
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>职位审核模板</span>
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
                config: {xjh_start_tplid:'', interview_tplid: '', payment_tplid: '', job_audit_tplid: ''},
                saveLoading: false
            }
        },
        mounted() {

        },
        methods: {
            async getinfo() {
                let that = this;
                httpPost('m=tool&c=xcxset&a=tplindex', {}).then(function (result) {
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
                httpPost('m=tool&c=xcxset&a=tplsave', that.config).then(function (res) {
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