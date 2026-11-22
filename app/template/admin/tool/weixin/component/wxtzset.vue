<template>
    <div class="setUpload">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                    <tr align="left">
                        <th width="180">名称</th>
                        <th width="260">状态</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                   
                    <tr>
                        <td>
                            <div class="TableTite">微信消息通知</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-switch v-model="configdata.wx_xxtz"   active-value="1" inactive-value="0"></el-switch>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>开关打开时的带背景色</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">消息跳转方式</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-radio v-model="configdata.wx_xxtz_tz" label="1">wap</el-radio>
                                <el-radio v-model="configdata.wx_xxtz_tz" label="2">小程序</el-radio>
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
                             <div class="TableTite">短信邮件通知</div>
                         </td>
                         <td>
                             <div class="TableButn">
                               <el-switch v-model="configdata.sy_msg_tz"  active-value="1" inactive-value="2"></el-switch>
                             </div>
                         </td>
                         <td>
                             <div class="TableShuom">
                                 <span>微信模板消息通知成功的情况下，是否继续发送短信以及邮件消息通知</span>
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
            this.$emit('post-set', { type: 'set', config: this.configdata })
        }
    },
};
</script>
<style scoped></style>