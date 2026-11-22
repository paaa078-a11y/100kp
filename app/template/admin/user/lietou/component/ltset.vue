<template>
    <div class="setUpload">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="180">名称</th>
                    <th width="400">状态</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">审核信息：
                        </div>
                    </td>
                    <td>
                        <div class="tc_checkbox">
                            <el-checkbox v-model="config.lt_status" label="0" true-label="0" false-label="1">猎头会员</el-checkbox>
                        </div>
                        <div class="tc_checkbox">
                            <el-checkbox v-model="config.lt_logo_status" label="1" true-label="1" false-label="0">猎头头像</el-checkbox>
                        </div>
                        <div class="tc_checkbox">
                            <el-checkbox  v-model="config.lt_job_status" label="0" true-label="0" false-label="1">发布职位</el-checkbox>
                        </div>
                        <div class="tc_checkbox">
                            <el-checkbox v-model="config.lt_cert_status" label='1' true-label="1" false-label="0">猎头认证</el-checkbox>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            开启则需审核后展示
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">强制操作：
                        </div>
                    </td>
                    <td>
                        <div class="tc_checkbox">
                            <el-checkbox v-model="config.lt_enforce_info" label="1" true-label="1" false-label="0">完善信息</el-checkbox>
                        </div>
                        <div class="tc_checkbox">
                            <el-checkbox v-model="config.lt_enforce_mobilecert" label="1" true-label="1" false-label="0">手机认证</el-checkbox>
                        </div>
                        <div class="tc_checkbox">
                            <el-checkbox v-model="config.lt_enforce_emailcert" label="1" true-label="1" false-label="0">邮箱认证</el-checkbox>
                        </div>
                        <div class="tc_checkbox">
                            <el-checkbox v-model="config.lt_enforce_licensecert" label="1" true-label="1" false-label="0">猎头认证</el-checkbox>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            强制操作完成才能发布招聘信息
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">单独购买：</div>
                    </td>
                    <td>
                        <el-checkbox-group v-model="lt_single_can">
                            <div class="tc_checkbox">
                                <el-checkbox label="issuejob" key="issuejob">发布职位</el-checkbox>
                            </div>
                            <div class="tc_checkbox">
                                <el-checkbox label="sxjob" key="sxjob">刷新职位</el-checkbox>
                            </div>
                            <div class="tc_checkbox">
                                <el-checkbox label="downresume">下载简历</el-checkbox>
                            </div>
                            <div class="tc_checkbox">
                                <el-checkbox label="chat">{{ config.sy_chat_name }}</el-checkbox>
                            </div>
                        </el-checkbox-group>
                    </td>
                    <td>
                        <div class="TableShuom">
                            提示：已选中的项目可以在套餐外单独使用金额、积分等购买，未选中则必须购买相应的套餐或增值包。
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">推荐人才返利：</div>
                    </td>
                    <td>
                        <el-switch v-model="config.lt_rec_rebates" active-text="开启" inactive-text="关闭"
                                   active-value="1"></el-switch>
                    </td>
                    <td>
                    </td>
                </tr>
                <tr v-if="config.lt_rec_rebates == '1'">
                    <td>
                        <div class="TableTite">返利单位：</div>
                    </td>
                    <td>
                        <el-input v-model="config.lt_rebates_name" placeholder="100"></el-input>
                    </td>
                    <td>
                        <div class="TableShuom">
                            提示：默认为元，例：金币
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">购买会员套餐累加：</div>
                    </td>
                    <td v-if="lt_rows">
                        <el-checkbox-group v-model="lt_rating_add">
                            <el-checkbox v-for="(item,index) in lt_rows" :key="index" :label="item.id">{{item.name}}</el-checkbox>
                        </el-checkbox-group>
                    </td>
                    <td v-else>
                        <div class="iradio_flat_height">
                            <!--     todo -->
                            暂无等级，
                            <a href="index.php?m=userconfig&c=comclass" style="color:red;">添加会员等级</a>
                            <input type="hidden" name="com_rating" value="0">
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            当前会员套餐未到期，购买新会员，选中的套餐数据按累加计算（套餐数量和套餐时间）。
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">猎头注册默认等级：</div>
                    </td>
                    <td v-if="lt_rows">
                        <el-radio-group v-model="config.lt_rating">
                            <el-radio v-for="(item,index) in lt_rows" :key="index" :label="item.id">{{item.name}}</el-radio>
                        </el-radio-group>
                    </td>
                    <td v-else>
                        <div class="iradio_flat_height">
                            <!--     todo -->
                            暂无等级，
                            <a href="index.php?m=userconfig&c=comclass" style="color:red;">添加会员等级</a>
                            <input type="hidden" name="com_rating" value="0">
                        </div>
                    </td>
                    <td>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">猎头会员到期后默认为：</div>
                    </td>
                    <td v-if="lt_rows">
                        <el-select v-model="config.lt_vip_done" placeholder="请选择">
                            <el-option key="0" label="过期会员" value="0"></el-option>
                            <el-option v-for="item in lt_rows" :key="item.id" :value="item.id" :label="item.name"></el-option>
                        </el-select>
                    </td>
                    <td v-else>
                        <div class="iradio_flat_height">
                            <!--     todo -->
                            暂无等级，
                            <a href="index.php?m=userconfig&c=comclass" style="color:red;">添加会员等级</a>
                            <input type="hidden" name="com_rating" value="0">
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                        如设置的不是过期会员，用户现有会员等级到期后按选择的等级设置套餐数量、到期时间
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="save" :disabled="submitLoading">提交</el-button>
        </div>
    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
            submitLoading: false,
            input: '',
            value1: true,
            textarea: '',
            config:{},
            lt_rows:[],
            lt_single_can:[],
            lt_rating_add:[],
            uri: "m=user&c=",
        }
    },
    created(){
        this.ltset();
    },
    methods: {
        ltset:function(){
            let _this = this;
            let url = _this.uri + 'admin_ltset&a=index';
            httpPost(url, {}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    // 有些参数需要
                    _this.lt_rating_add = res.data.lt_rating_add;
                    _this.config = res.data.config;
                    _this.lt_single_can = res.data.lt_single_can;
                    _this.lt_rows = res.data.lt_rows;
                }
            })
        },
        save:function(){

            let _this = this;
            let url = _this.uri + 'admin_ltset&a=save';
            let sendData= {
                lt_status :_this.config.lt_status,
                lt_logo_status : _this.config.lt_logo_status,
                lt_job_status : _this.config.lt_job_status,
                lt_cert_status :  _this.config.lt_cert_status,
                lt_enforce_info : _this.config.lt_enforce_info,
                lt_enforce_emailcert : _this.config.lt_enforce_emailcert,
                lt_enforce_mobilecert : _this.config.lt_enforce_mobilecert,
                lt_enforce_licensecert : _this.config.lt_enforce_licensecert,
                lt_vip_done: _this.config.lt_vip_done,
                lt_rec_rebates : _this.config.lt_rec_rebates,
                lt_rebates_name : _this.config.lt_rebates_name,
                lt_rating : _this.config.lt_rating,
                lt_single_can:_this.lt_single_can,
                lt_rating_add:_this.lt_rating_add.join(',')
            }
            _this.submitLoading = true;
            httpPost(url, sendData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg,_this.ltset())
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                _this.submitLoading = false;
            });
        }
    },
};
</script>
<style scoped></style>
