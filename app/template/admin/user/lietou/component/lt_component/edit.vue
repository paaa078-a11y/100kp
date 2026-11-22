<template>
    <div class="zujiantanchus">
        <div class="uploadTable">
            <table class="tableVue">
                <thead>
                    <tr align="left">
                        <th width="120">名称</th>
                        <th width=" ">状态</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="TableTite">真实姓名</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="" v-model="detail.realname"></el-input>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">企业全称</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="" v-model="detail.com_name"></el-input>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">联系电话</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="" v-model="detail.moblie"></el-input>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">公司座机</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="" v-model="detail.phone"></el-input>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">E-mail</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input placeholder="" v-model="detail.email"></el-input>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">所在地区</div>
                        </td>
                        <td>
                            <div class="TableSelect" style="display: flex;align-items: center;">
                                <el-cascader v-model="cityArrModel" :options="cityoptions" @change="cityChoose"
                                    :props="{ checkStrictly: true }" clearable></el-cascader>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">工作经验</div>
                        </td>
                        <td>
                            <div class="TableSelect">
                                <el-select v-model="detail.exp" placeholder="请选择">
                                    <el-option v-for="(value, key) in ltdata.lt_exp" :key="key" :label="ltclass_name[value]"
                                        :value="value">
                                    </el-option>
                                </el-select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">目前头衔</div>
                        </td>
                        <td>
                            <div class="TableSelect">
                                <el-select v-model="detail.title" placeholder="请选择">
                                    <el-option v-for="(value, key) in ltdata.lt_title" :key="key"
                                        :label="ltclass_name[value]" :value="value">
                                    </el-option>
                                </el-select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">擅长行业</div>
                        </td>
                        <td>
                            <div class="TableSelect">
                                <lthy_class multiple :max="5" @confirm="confirmHy" :selected="detail.hynameArr">
                                </lthy_class>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">擅长职位</div>
                        </td>
                        <td>
                            <div class="TableSelect">
                                <ltjob_class multiple :max="5" @confirm="confirmJob" :selected="detail.jobnameArr">
                                </ltjob_class>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">合作过的客户</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="detail.client"></el-input>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="TableTite">顾问介绍</div>
                        </td>
                        <td>
                            <div class="TableInpt">
                                <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="detail.content"></el-input>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
        <div class="setBasicButn" style="border: none; height: 80px;">
            <el-button type="primary" size="medium" @click="saveLietou" :loading="saveLoading">提交</el-button>
        </div>
    </div>
</template>
<script>
module.exports = {
    props: {
        uid: String,
    },
    data: function () {
        return {
            options: [],
            input: '',
            select: '',
            value: true,
            drawer: false,
            drawer2: false,
            seachbutn: false,
            tableHig: true,
            detail: {},
            cityoptions: [],
            items: [
                { type: '', label: '正常' },
            ],
            uri: "m=user&c=",
            ltdata: [],
            ltclass_name: [],
            cityArrModel: [],
            saveLoading: false
        }
    },
    watch: {
        uid: {
            handler(val) {
                this.getLitouInfo();
            },
            deep: true,
            immediate: true
        }
    },
    components: {
        'lthy_class': httpVueLoader('../../../../component/lthy_class.vue'),
        'ltjob_class': httpVueLoader('../../../../component/ltjob_class.vue'),
    },
    created() {
        this.cityoptions = cityCascader();
        this.workAndtitle();
    },
    methods: {
        workAndtitle: function () {
            let _this = this;
            let url = this.uri + "admin_lt_member&a=workAndtitle";
            httpPost(url, {}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.ltdata = res.data.ltdata;
                    _this.ltclass_name = res.data.ltclass_name;
                } else {
                    message.error(res.msg);
                }
            })
        },
        getLitouInfo: function () {
            let _this = this;
            let url = this.uri + "admin_lt_member&a=edit"
            httpPost(url, { id: this.uid }).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.detail = res.data;
                    _this.cityArrModel = res.data.cityArrModel;
                } else {
                    message.error(res.msg);
                }
            })
        },
        confirmHy(data) {
            this.detail.hy = data.hyId.join(',');
        },
        confirmJob(data) {
            this.detail.job = data.jobId.join(',');
        },
        saveLietou: function () {
            let _this = this;
            let url = this.uri + "admin_lt_member&a=save";
            _this.saveLoading = true;
            httpPost(url, _this.detail).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg);
                } else {
                    message.error(res.msg);
                }
                // _this.$emit("save-getlist");
            }).finally(function () {
                setTimeout(function () {
                    _this.saveLoading = false;
                }, 2000);
            });

        },
        cityChoose: function (val) {
            console.log(val);
            var provinceid = cityid = three_cityid = '';

            if (val.length > 0) {
                provinceid = val[0];
                cityid = val.length > 1 ? val[1] : '';
                three_cityid = val.length > 2 ? val[2] : '';
            }
            this.provinceid = provinceid;
            this.cityid = cityid;
            this.three_cityid = three_cityid;

        },
    },
    watch: {
        uid: {
            handler(newValue, oldValue) {
                console.log(newValue, oldValue);
                if (newValue != oldValue) {
                    this.getLitouInfo();
                    this.workAndtitle();
                }
            },
            deep: true,
            immediate: true
        }
    }
};
</script>