<template>
    <div class="setUpload">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                <tr align="left">
                    <th width="180">名称</th>
                    <th width="400">明细</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div class="TableTite">轮值安排</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-radio-group v-model="baseConfig.sy_crm_duty">
                                <el-radio label="1">时间</el-radio>
                                <el-radio label="2">区域</el-radio>
                            </el-radio-group>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>时间分配 - 按照周一至周末值班人员顺序分配</span>
                            <br>
                            <span>区域分配 - 按照人员指定区域划分顺序分配（仅针对企业分配：企业完善基本信息选择对应地区时绑定业务员）</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="TableTite">公海信息</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-radio-group v-model="baseConfig.sy_crm_link">
                                <el-radio label="1">开放</el-radio>
                                <el-radio label="2">关闭</el-radio>
                            </el-radio-group>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>关闭信息：业务员无法查看公海客户联系方式，无法进入公海客户管理页面</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">领取限制</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-input type="number" v-model="baseConfig.sy_crm_hour" min="0" placeholder="请填写领取数量"></el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>1个小时内，领取客户数量 + 接受转交客户数量不超过N个，0为不限</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">排除成交限制</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-radio-group v-model="baseConfig.sy_crm_deal_open">
                                <el-radio label="1">开启</el-radio>
                                <el-radio label="2">关闭</el-radio>
                            </el-radio-group>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>排除成交限制，成交会员将不再计入客户数量限制名额中，剩余名额 = 限定数量 - （当前客户数 - 已成交客户）</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">领取客户权限</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-radio-group v-model="baseConfig.sy_crm_receive_grant">
                                <el-radio label="1">限制业务员</el-radio>
                                <el-radio label="2">不限制</el-radio>
                            </el-radio-group>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>开启限制业务员后，只有账户开通业务员的才能领取客户</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">体验会员</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <el-select v-model="baseConfig.sy_free_id" placeholder="请选择会员套餐">
                                <el-option v-for="item in ratingArr" :key="item.value" :label="item.label" :value="item.value"></el-option>
                            </el-select>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>站点会员套餐体系中，免费体验会员，用于数据统计</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">成交会员</div>
                    </td>
                    <td>
                        <div class="TableInpt w_400">
                            <el-select v-model="dealRatingArr" multiple placeholder="请选择">
                                <el-option v-for="item in ratingArr" :key="item.value" :label="item.label" :value="item.value"></el-option>
                            </el-select>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>业务员成交客户数量统计、业务员客户数量限制使用</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">数据统计跟进周期</div>
                    </td>
                    <td>
                        <div class="TableInpt TableInpGenjin">
                            <el-input type="number" min="1" v-model="baseConfig.crm_jobtai_gyx_cycle" placeholder="输入数字">
                                <template slot="prepend">高意向用户</template>
                                <template slot="append">天</template>
                            </el-input>
                            <el-input type="number" min="1" v-model="baseConfig.crm_jobtai_hy_cycle" placeholder="输入数字">
                                <template slot="prepend">活跃用户</template>
                                <template slot="append">天</template>
                            </el-input>
                            <el-input type="number" min="1" v-model="baseConfig.crm_jobtai_jgq_cycle" placeholder="输入数字">
                                <template slot="prepend">将过期用户</template>
                                <template slot="append">天</template>
                            </el-input>
                            <el-input type="number" min="1" v-model="baseConfig.crm_jobtai_qz_cycle" placeholder="输入数字">
                                <template slot="prepend">潜在用户</template>
                                <template slot="append">天</template>
                            </el-input>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>工作台、数据看板查询X天内未跟进的客户; 为空, 默认3天; 最小值为1</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">上班时间</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <template>
                                <el-time-select
                                        placeholder="起始时间"
                                        v-model="workStartTime"
                                        :picker-options="{
                                          start: '06:00',
                                          step: '00:10',
                                          end: '22:00'
                                        }">
                                </el-time-select>
                                <el-time-select style="margin-left:10px;"
                                        placeholder="结束时间"
                                        v-model="workEndTime"
                                        :picker-options="{
                                          start: '06:00',
                                          step: '00:10',
                                          end: '22:00',
                                          minTime: workStartTime
                                        }">
                                </el-time-select>
                            </template>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>上班时间</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">休息时间</div>
                    </td>
                    <td>
                        <div class="TableInpt">
                            <template>
                                <el-time-select
                                        placeholder="起始时间"
                                        v-model="restStartTime"
                                        :picker-options="{
                                          start: '06:00',
                                          step: '00:10',
                                          end: '22:00',
                                          minTime: workStartTime,
                                          maxTime: workEndTime
                                        }">
                                </el-time-select>
                                <el-time-select style="margin-left:10px;"
                                        placeholder="结束时间"
                                        v-model="restEndTime"
                                        :picker-options="{
                                          start: '06:00',
                                          step: '00:10',
                                          end: '22:00',
                                          minTime: restStartTime
                                        }">
                                </el-time-select>
                            </template>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>上班时间中的休息时间，例如午休</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">数据看板业务员类型</div>
                    </td>
                    <td>
                        <div class="TableInpt w_400">
                            <el-select v-model="baseConfig.crm_admin_type" filterable multiple placeholder="请选择业务员类型">
                                <el-option v-for="(item,key) in adminTypes" :key="key" :label="item.group_name" :value="item.id"></el-option>
                            </el-select>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>控制数据看板中进度报表、效率报表和业务报表需要展示的业务员类型，未配置默认展示所以业务员</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="TableTite">推广图片</div>
                    </td>
                    <td>
                        <div class="TableUpload">
                            <el-upload class="upload-demo" :accept="pic_accept" action='' :auto-upload="false" :show-file-list="false" :on-change="upShareChange">
                                <el-button slot="trigger" size="small" type="primary">选取LOGO</el-button>
                                <img class="el-upload-list__item-thumbnail" v-if="baseConfig.sy_crmrecshare_logo" :src="shareLogo" alt="分享LOGO" width="110" height="110" style="margin-left: 25px;" />
                            </el-upload>
                        </div>
                    </td>
                    <td>
                        <div class="TableShuom">
                            <span>业务员推广职位时，分享到朋友圈的默认LOGO</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="setBaseConfig" :disabled="btDisabled">提交</el-button>
        </div>
    </div>
</template>

<script>
    module.exports = {
        data: function () {
            return {
                pic_accept: localStorage.getItem("pic_accept"),
                baseConfig: {
                    sy_crm_duty: '',
                    sy_free_id: '',
                    sy_crm_link: '',
                    sy_crm_hour: 0,
                    sy_crm_deal_open: '',
                    sy_crm_receive_grant: '',
                    crm_deal_rating: '',
                    crm_jobtai_gyx_cycle: '',
                    crm_jobtai_hy_cycle: '',
                    crm_jobtai_jgq_cycle: '',
                    crm_jobtai_qz_cycle: '',
                    sy_crmrecshare_logo: '',
                    crm_admin_type: '',
                },
                workStartTime: '',
                workEndTime: '',
                restStartTime: '',
                restEndTime: '',
                dealRatingArr: [],
                ratingArr: [],

                shareLogo: '',
                logoFile: '',

                picPreUrl: '',
                picType: '',
                picMaxSize: '',
                btDisabled: false,
                adminTypes: []
            }
        },
        mounted(){
            this.getCrmConfig();
        },
        methods: {
            async getCrmConfig() {
                let that = this;
                let res = await httpPost('m=crm&c=config&a=baseConfig');
                if (res.data.error == 0) {
                    let data = res.data.data;
                    that.baseConfig = data.base;
                    that.workStartTime = that.baseConfig.workStartTime
                    that.workEndTime = that.baseConfig.workEndTime
                    that.restStartTime = that.baseConfig.restStartTime
                    that.restEndTime = that.baseConfig.restEndTime
                    if (data.base.crm_deal_rating != null) {
                        that.dealRatingArr = data.base.crm_deal_rating.split(',');
                    }
                    data.ratingList.forEach(function (item) {
                        that.ratingArr.push({value: item.id, label: item.name});
                    })
                    that.picPreUrl = data.picPreUrl;

                    that.shareLogo = that.picPreUrl+'/'+that.baseConfig.sy_crmrecshare_logo;
                    that.picMaxSize = data.picMaxSize;
                    that.picType = data.picType;
                    that.adminTypes = data.adminTypes
                 }
            },
            checkFile:function(file){
                let that = this;
                //  判断图片类型
                let picTypeArr = this.picType.split(',');
                let isImage = false;
                picTypeArr.forEach(item=>{
                    if (file.raw.type === 'image/'+item){
                        isImage = true;
                    }
                });
                if (!isImage) {
                    message.error('上传图片只能是 （'+this.picType+'） 格式!');
                    return false;
                }
                //  判断图片大小
                let isLtNumM = file.size / 1024 / 1024 < this.picMaxSize;
                if (!isLtNumM) {
                    message.error('上传图片大小不能超过 '+this.picMaxSize+'MB!');
                    return false;
                }
            },
            upShareChange(file) {
                var that =  this;
                that.checkFile(file);
                this.shareLogo = URL.createObjectURL(file.raw);
                this.logoFile = file.raw;
            },
            setBaseConfig: function () {

                var _this = this;

                let formData = new FormData();

                if (_this.baseConfig.crm_jobtai_gyx_cycle !== '' && _this.baseConfig.crm_jobtai_gyx_cycle < 1) {
                    message.error('工作台高意向用户数据周期不能小于1');
                    return false;
                }
                if (_this.baseConfig.crm_jobtai_hy_cycle !== '' && _this.baseConfig.crm_jobtai_hy_cycle < 1) {
                    message.error('工作台活跃用户数据周期不能小于1');
                    return false;
                }
                if (_this.baseConfig.crm_jobtai_jgq_cycle !== '' && _this.baseConfig.crm_jobtai_jgq_cycle < 1) {
                    message.error('工作台将过期用户数据周期不能小于1');
                    return false;
                }
                if (_this.baseConfig.crm_jobtai_qz_cycle !== '' && _this.baseConfig.crm_jobtai_qz_cycle < 1) {
                    message.error('工作台潜在用户数据周期不能小于1');
                    return false;
                }

                if (_this.baseConfig.sy_crm_duty == '') {

                    message.error('请选择业务员轮值安排');
                    return false;
                } else {

                    formData.append('sy_crm_duty', _this.baseConfig.sy_crm_duty);
                }
                formData.append('sy_free_id', _this.baseConfig.sy_free_id);
                formData.append('sy_crm_link', _this.baseConfig.sy_crm_link);
                formData.append('sy_crm_hour', _this.baseConfig.sy_crm_hour);
                formData.append('sy_crm_deal_open', _this.baseConfig.sy_crm_deal_open);
                formData.append('sy_crm_receive_grant', _this.baseConfig.sy_crm_receive_grant);
                formData.append('crm_deal_rating', _this.dealRatingArr.join(','));
                formData.append('crm_jobtai_gyx_cycle', _this.baseConfig.crm_jobtai_gyx_cycle);
                formData.append('crm_jobtai_hy_cycle', _this.baseConfig.crm_jobtai_hy_cycle);
                formData.append('crm_jobtai_jgq_cycle', _this.baseConfig.crm_jobtai_jgq_cycle);
                formData.append('crm_jobtai_qz_cycle', _this.baseConfig.crm_jobtai_qz_cycle);
                formData.append('crm_admin_type', _this.baseConfig.crm_admin_type);
                let worktime = '', resttime = ''
                if (_this.workStartTime && _this.workEndTime) {
                    worktime = _this.workStartTime + ',' + _this.workEndTime
                }
                if (_this.restStartTime && _this.restEndTime) {
                    resttime = _this.restStartTime + ',' + _this.restEndTime
                }
                formData.append('worktime', worktime);
                formData.append('resttime', resttime);
                if (this.logoFile.length !== 0) {
                    formData.append('logoFile', this.logoFile);
                }
                formData.append('baseConfig', '1');
                _this.btDisabled = true
                httpPost('m=crm&c=config&a=saveConfig', formData).then(function (res) {
                    if (res.data.error == 0) {
                        message.success(res.data.msg);
                    } else {
                        message.error(res.data.msg);
                    }
                }).finally(function(){
                    _this.btDisabled = false
                });
            }
        }
    };
</script>
<style scoped>
    /* .el-input__inner{padding: 0 0 0 10px;} */
    .w_400 .el-input--suffix{width: 400px;}
</style>