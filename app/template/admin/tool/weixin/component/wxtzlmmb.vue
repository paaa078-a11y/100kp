<template>
    <div class="setUpload">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                    <tr align="left">
                        <th width="200">名称</th>
                        <th width="420">状态</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="TableTite">职位申请模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="configdata.wx_mbsqzwlm"></el-input>
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
                            <div class="TableTite">邀请面试模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input  v-model="configdata.wx_mbyqmslm"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">充值提醒模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbcztxlm"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>该模板消息猎头不支持跳转小程序</span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">兼职报名模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbjzbmlm"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">职位、兼职审核通过模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbzwshlm"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">职位、兼职审核未通过模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbzwshsblm"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">不在线时直聊提醒</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="configdata.wx_mbchatlm"></el-input>
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
                            <div class="TableTite">订阅通知模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="configdata.wx_mbsubscribelm"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> 
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="post">提交</el-button>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    props: {
        config: {
            type: Object,
            default: {}
        },
    },
    watch: {
        config: {
            handler(val, oldVal) {
                this.configdata = val;
            },
            immediate: true,
            deep: true,
        }
    },
    data: function () {
        return {
            configdata: {}
        }
    },

    mounted() {

    },
    methods: {
        post: function () {
            this.$emit('post-set', { type: 'lmmbset', config: this.configdata })
        }
    },
};
</script>
<style scoped></style>