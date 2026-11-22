<template>
    <!--会员-企业-企业设置：悬赏职位设置-->
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
                        <div class="TableTite">手机认证申请悬赏职位</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-radio v-model="ruleForm.sy_reward_tel" label="1">开启</el-radio>
                            <el-radio v-model="ruleForm.sy_reward_tel" label="0">关闭</el-radio>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>开启后只有手机经过认证的求职者才可申请悬赏职位</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">同时申请职位</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input v-model="ruleForm.sy_reward_sqnum" placeholder="请输入数字">
                                <span slot="suffix" class="slotspan">份</span>
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>求职者同时可申请悬赏职位的最大数量，计数不包含已结束或已取消的记录</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">悬赏流程期限</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input v-model="ruleForm.sy_reward_hour" placeholder="请输入数字">
                                <span slot="suffix" class="slotspan">小时</span>
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>设定预定义期限，如企业或求职者在规定期限内未回应，则对方有权终止悬赏</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">提现</div>
                    </td>
                    <td>
                        <div class="TableButn">
                            <el-radio v-model="ruleForm.sy_withdraw" label="1">开启</el-radio>
                            <el-radio v-model="ruleForm.sy_withdraw" label="0">关闭</el-radio>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>注:申请支付宝支付时，请先关掉，通过后再开启</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">最低提现额度</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input v-model="ruleForm.sy_withdraw_minmoney" placeholder="请输入数字">
                                <span slot="suffix" class="slotspan">元</span>
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>必须超过相应金额才允许提现</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">提现审核额度</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input v-model="ruleForm.sy_withdraw_money" placeholder="请输入数字">
                                <span slot="suffix" class="slotspan">元</span>
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">超过规定金额必须后台管理员审核后才可提现</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">提现次数</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input v-model="ruleForm.sy_withdraw_num" placeholder="请输入数字">
                                <span slot="suffix" class="slotspan">次</span>
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>说明：设每个账户单日可提现次数</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">提现手续费</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input v-model="ruleForm.sy_withdraw_pound" placeholder="请输入数字">
                                <span slot="suffix" class="slotspan">%</span>
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>每次提现扣除的手续费比例</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">分享赏金职位二维码</div>
                    </td>
                    <td>
                        <div class="TableUpload" style="display: flex;align-items: center;">
                            <el-upload :action="action"
                                :data="{ name: 'sy_reward_sharelogo', path: 'logo', imgid: 'imgreward', source:'back', pytoken: pytoken }"
                                :on-success="(response, file, fileList) =>onSuccess(response,file,fileList,'fileList')"
                                :accept="pic_accept"
                                :show-file-list="false">
                                <el-button size="small" type="primary">上传图片</el-button>
                            </el-upload>
                            <div class="up_sy_logo_div" style="margin-left: 15px;">
                                <el-image v-if="sy_reward_sharelogo" style="width:100px;" :src="sy_reward_sharelogo"
                                    :preview-src-list="sy_reward_sharelogo ? [sy_reward_sharelogo] : []"></el-image>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>请将链接：{{ url_reward }} 生成二维码</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="submitForm('ruleForm')" :disabled="submitLoading">提交</el-button>
        </div>
    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            action: baseUrl + 'm=index&c=layui_upload',
            pytoken: localStorage.getItem("pytoken"),
            sy_weburl: localStorage.getItem("sy_weburl"),
            searchForm: {},
            ruleForm: {
                //手机认证才能申请悬赏职位
                sy_reward_tel: null,//1, 0
                //同时申请职位
                sy_reward_sqnum: '',
                //悬赏流程期限
                sy_reward_hour: '',
                //提现
                sy_withdraw: null,//1, 0
                //最低提现额度
                sy_withdraw_minmoney: null,
                //提现审核额度
                sy_withdraw_money: null,
                //提现次数
                sy_withdraw_num: null,
                //提现手续费
                sy_withdraw_pound: null,
            },
            //分享赏金职位二维码
            sy_reward_sharelogo: '',
            url_reward: '',
            submitLoading: false,
        }
    },
    created() {
        this.getList();
    },
    methods: {
        onSuccess: function (res, file, fileList, arr) {
            fileList.splice(fileList.length - 1, 1);
            if (res.code === 0) {
                this.sy_reward_sharelogo = res.data.url;
                if (fileList.length < 1) {
                    fileList = [{name: '', url: res.data.url}];
                } else {
                    fileList[0]['url'] = res.data.url;
                }
            } else {
                message.error(res.msg)
            }
        },
        getList() {
            let _this = this;
            let params = JSON.parse(JSON.stringify(this.searchForm));
            for (let index in params) {
                (params[index] === '') && (params[index] = null);
            }
            httpPost('m=user&c=company_comset&a=reward', params).then(function (response) {
                let res = response.data;
                if (res.error === 0) {
                    let config = res.data.config;
                    //手机认证才能申请悬赏职位
                    _this.ruleForm.sy_reward_tel = config.sy_reward_tel !== undefined ? config.sy_reward_tel : null;
                    //同时申请职位
                    _this.ruleForm.sy_reward_sqnum = config.sy_reward_sqnum !== undefined ? config.sy_reward_sqnum : '';
                    //悬赏流程期限
                    _this.ruleForm.sy_reward_hour = config.sy_reward_hour !== undefined ? config.sy_reward_hour : '';
                    //提现
                    _this.ruleForm.sy_withdraw = config.sy_withdraw !== undefined ? config.sy_withdraw : null;
                    //最低提现额度
                    _this.ruleForm.sy_withdraw_minmoney = config.sy_withdraw_minmoney !== undefined ? config.sy_withdraw_minmoney : null;
                    //提现审核额度
                    _this.ruleForm.sy_withdraw_money = config.sy_withdraw_money !== undefined ? config.sy_withdraw_money : null;
                    //提现次数
                    _this.ruleForm.sy_withdraw_num = config.sy_withdraw_num !== undefined ? config.sy_withdraw_num : null;
                    //提现手续费
                    _this.ruleForm.sy_withdraw_pound = config.sy_withdraw_pound !== undefined ? config.sy_withdraw_pound : null;
                    //分享赏金职位二维码
                    _this.sy_reward_sharelogo = config.sy_reward_sharelogo;
                    _this.url_reward = res.data.url_reward;
                }
            }).catch(function (error) {
                console.log(error);
            });
        },
        submitForm(formName) {
            // this.$refs[formName].validate((valid) => {if (valid) {}});
            let _this = this;
            let params = JSON.parse(JSON.stringify(this.ruleForm));
            params.config = '提交';
            _this.submitLoading = true;
            httpPost('m=user&c=company_comset&a=savereward', params).then(function (response) {
                let res = response.data;
                if (res.error === 0) {
                    message.success(res.msg);
                    _this.getList();
                } else {
                    message.error(res.msg);
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                _this.submitLoading = false;
            });
        },
        resetForm(formName) {
            //this.$refs[formName].resetFields();
        }
    },
    watch: {
        "ruleForm.sy_reward_sqnum": {
            handler(newValue, oldValue) {
                if (typeof (newValue) == 'string') {
                    newValue = newValue.replace(/[^0-9]/g, '');
                    this.ruleForm.sy_reward_sqnum = newValue;
                }
            },
            deep: true,
            immediate: true
        },
        "ruleForm.sy_reward_hour": {
            handler(newValue, oldValue) {
                if (typeof (newValue) == 'string') {
                    newValue = newValue.replace(/[^0-9.]/g, '');
                    this.ruleForm.sy_reward_hour = newValue;
                }
            },
            deep: true,
            immediate: true
        },
        "ruleForm.sy_withdraw_minmoney": {
            handler(newValue, oldValue) {
                if (typeof (newValue) == 'string') {
                    newValue = newValue.replace(/[^0-9.]/g, '');
                    this.ruleForm.sy_withdraw_minmoney = newValue;
                }
            },
            deep: true,
            immediate: true
        },
        "ruleForm.sy_withdraw_money": {
            handler(newValue, oldValue) {
                if (typeof (newValue) == 'string') {
                    newValue = newValue.replace(/[^0-9.]/g, '');
                    this.ruleForm.sy_withdraw_money = newValue;
                }
            },
            deep: true,
            immediate: true
        },
        "ruleForm.sy_withdraw_num": {
            handler(newValue, oldValue) {
                if (typeof (newValue) == 'string') {
                    newValue = newValue.replace(/[^0-9]/g, '');
                    this.ruleForm.sy_withdraw_num = newValue;
                }
            },
            deep: true,
            immediate: true
        },
        "ruleForm.sy_withdraw_pound": {
            handler(newValue, oldValue) {
                if (typeof (newValue) == 'string') {
                    newValue = newValue.replace(/[^0-9.]/g, '');
                    this.ruleForm.sy_withdraw_pound = newValue;
                }
            },
            deep: true,
            immediate: true
        }
    }
};
</script>
<style scoped></style>