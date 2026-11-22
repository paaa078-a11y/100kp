<template>
    <div class="setUpload">
        <div class="uploadTable">
            <div class="peixunData">
                <span>登录次数： {{member.login_hits}}</span>
                <span>注册IP：{{member.reg_ip}}</span>
                <span>登录IP： {{member.login_ip}}</span>
                <span>注册时间：{{member.reg_date_n}}</span>
                <span>最近登录时间：{{member.login_date_n}}</span>
            </div>
            <table class="tableVue">
                <thead>
                    <tr align="left">
                        <th width="180">名称</th>
                        <th width="480">状态</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="TableTite">用户名</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.username" placeholder="请输入用户名">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>用户名</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">登录密码</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input v-model="ruleForm.password" placeholder="">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>不修改请留空</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">状态</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-radio-group v-model="ruleForm.status">
                                    <el-radio label="1">正常</el-radio>
                                    <el-radio label="2">锁定</el-radio>
                                </el-radio-group>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>状态</span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="ruleForm.status == 2">
                        <td>
                            <div class="TableTite">锁定说明</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input type="textarea" :rows="3" placeholder="请输入锁定说明" v-model="ruleForm.lock_info">
                                </el-input>
                            </div>
                        </td>
                        <td>
                            <div class="TableShuom">
                                <span>锁定说明</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="save">提交</el-button>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    props: {
        uid: String,
        member: Object
    },
    data: function () {
        return {
            ruleForm: {},

            saveLoading: false,
        }
    },
    created() {
        this.setRuleForm();
    },
    methods: {
        setRuleForm() {
            let member = this.member;

            if (typeof member.uid !== 'undefined') {
                this.ruleForm = {
                    uid: member.uid,
                    username: member.username,
                    password: '',
                    status: member.status,
                    lock_info: member.lock_info,
                }
            }
        },

        save() {
            let that = this,
                params = that.ruleForm;

            if (params.username == '') {
                message.warning('请输入用户名');
                return false;
            }

            if (params.status == 2 && params.lock_info == '') {
                message.warning('请输入锁定说明');
                return false;
            }

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            httpPost('m=user&c=peixun_member&a=saveUser', params).then(function (response) {
                let res = response.data;

                that.saveLoading = false;
                if (res.error > 0) {
                    message.error(res.msg);
                } else {
                    that.$emit("child-event");
                    message.success(res.msg);
                }
            })
        },
    },
    watch: {
        uid: function (val, oldVal) {
            this.ruleForm = {};
            this.setRuleForm();
        }
    }
};
</script>
<style scoped></style>