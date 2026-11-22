<template>
    <div class="setUpload">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                    <tr align="left">
                        <th width="180">名称</th>
                        <th width="560">状态</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="TableTite">视频开关</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-switch v-model="ruleForm.sy_video_open"
                                           active-value="1" inactive-value="2">
                                </el-switch>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>视频开关</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">上传视频最大限制</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="ruleForm.video_max_size" @input="inputIntNumber($event, 'ruleForm', 'video_max_size')" placeholder="">
                                    <span slot="suffix" class="slotspan"> M </span>
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>单位：M；不填或填写0，默认设为500M</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">账号ID</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="ruleForm.video_ali_userid" placeholder=""></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>提示：</span>
                                <a href="https://ram.console.aliyun.com/" target="_blank">查看账号ID</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">AccessKey ID</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="ruleForm.video_ali_access_key_id" placeholder=""></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>提示：</span>
                                <a href="https://ram.console.aliyun.com/" target="_blank">阿里云密钥获取</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">AccessKey Secret</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="ruleForm.video_ali_access_key_secret" placeholder=""></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">转码模板ID</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="ruleForm.video_ali_tplid" placeholder=""></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>提示：自定义转码视频格式请选择“.mp4”格式</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">视频分类ID</div>
                        </td>
                        <td>
                            <div class="TableButn">
                                <el-input v-model="ruleForm.video_ali_cid" placeholder=""></el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span></span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="save" :disabled="saveLoading">提交</el-button>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    props: {
        list: Object
    },
    data: function () {
        return {
            ruleForm: {},

            saveLoading: false,
        }
    },

    mounted() {

    },
    created() {
        this.init();
    },
    methods: {
        init() {
            this.getData();
        },

        inputIntNumber(val, form, key) {
            this.$data[form][key] = val.replace(/[^0-9]/g,'');
        },

        getData() {
            let that = this;
            httpPost('m=yunying&c=yingxiao_video&a=config').then(function (response) {
                let res = response.data,
                    data = res.data;

                that.ruleForm = data.config;
            })
        },

        save() {
            let that = this,
                ruleForm = that.ruleForm;

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            httpPost('m=yunying&c=yingxiao_video&a=configSave', ruleForm).then(function (response) {
                let res = response.data;

                that.saveLoading = false;
                if (res.error > 0) {
                    message.error(res.msg);
                } else {
                    message.success(res.msg);
                }
            })
        },
    },
};
</script>
<style scoped></style>