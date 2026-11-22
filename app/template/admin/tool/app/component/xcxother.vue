<template>
    <div class="moduleElHight">
        <div class="moduleSeachs" style="padding-left:0px;">
            <el-alert title="微信小程序登陆设置" type="success" :closable="false"></el-alert>
            <div class="moduleSeachButn" style="margin-left:12px;">
                <el-button type="primary" icon="el-icon-document-add" size="mini" @click="add">增加</el-button>
            </div>
        </div>
        <div class="moduleTable">
            <table class="tableVue marginbottom20" v-for="(item,key) in configs" :key="key">
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
                            <div class="TableTite">appid</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入appid" v-model="item.appid">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>appid</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">appSecret</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入appSecret" v-model="item.secret">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>appSecret</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">客服链接</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入客服链接" v-model="item.kf">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>客服链接</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">企业ID</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="请输入企业ID" v-model="item.comid">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>企业ID</span>
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
                configs: [{appid:'', secret: '', kf: '', comid: ''}],
                saveLoading: false
            }
        },
        mounted() {

        },
        methods: {
            async getinfo() {
                let that = this;
                httpPost('m=tool&c=xcxset&a=otherindex', {}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.configs = res.data.rows
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            save() {
                let that = this;
                that.saveLoading = true;
                httpPost('m=tool&c=xcxset&a=othersave', that.configs).then(function (res) {
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
            add(){
                this.configs.push({appid:'', secret: '', kf: '', comid: ''})
            }
        },
    };
</script>
<style scoped>
    .marginbottom20 {
        margin-bottom: 20px;
    }

</style>