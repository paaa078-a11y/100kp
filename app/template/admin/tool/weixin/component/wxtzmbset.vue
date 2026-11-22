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
                                <el-input v-model="configdata.wx_mbsqzw"></el-input>
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
                            <div class="TableTite">职位审核模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input  v-model="configdata.wx_mbzwsh"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">邀请面试模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbyqms"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">兼职报名模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbjzbm"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">兼职审核模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbjzsh"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">简历审核模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbjlsh"></el-input>
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
                                <el-input   v-model="configdata.wx_mbcztx"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> 该模板消息猎头不支持跳转小程序</span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">赏金职位提醒模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbreward"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">订阅通知提醒模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_subscribetpl"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> 
                    <tr v-if="configdata.sy_chat_open==1">
                        <td>
                            <div class="TableTite">不在线时{{configdata.sy_chat_name}}提醒</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input   v-model="configdata.wx_mbchat"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> 该模板消息猎头不支持跳转小程序</span>
                            </div>
                        </td>
                    </tr> 
                    <tr v-if="configdata.sy_chat_open==1">
                        <td>
                            <div class="TableTite">{{configdata.sy_chat_name}}互换电话、微信提醒模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="configdata.wx_mbchat_ex"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> 模板消息猎头不支持跳转小程序</span>
                            </div>
                        </td>
                    </tr> 
                    <tr>
                        <td>
                            <div class="TableTite">身份转换审核通知提醒模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="configdata.wx_userchangetpl"></el-input>
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
                            <div class="TableTite">直播宣讲会开播通知模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="configdata.wx_mbxjhlive"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">视频面试审核模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="configdata.wx_mbspmssh"></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span> </span>
                            </div>
                        </td>
                    </tr> <tr>
                        <td>
                            <div class="TableTite">视频面试通知模板</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="configdata.wx_mbspview"></el-input>
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
            this.$emit('post-set', { type: 'mbset', config: this.configdata })
        }
    },
};
</script>
<style scoped></style>