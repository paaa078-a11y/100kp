<template>
    <div class="drawerModlue" v-loading="addloading" style="display: table;">
        <div class="drawerModInfo">
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>专题名称：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="ruleForm.title" placeholder="请输入内容" clearable></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>副标题：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="ruleForm.sub_title" placeholder="" clearable></el-input>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>是否启用：</span>
                </div>
                <div class="drawerModInpt">
                    <el-radio v-model="ruleForm.display" label="1">启用</el-radio>
                    <el-radio v-model="ruleForm.display" label="0">关闭</el-radio>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>是否推广：</span>
                </div>
                <div class="drawerModInpt">
                    <el-radio v-model="ruleForm.tuiguang" label="1">启用</el-radio>
                    <el-radio v-model="ruleForm.tuiguang" label="0">关闭</el-radio>
                </div>
                <div class="drawerModTips">
                    <el-alert title="开启推广，首页和搜索才展示" type="info" show-icon :closable="false"></el-alert>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>职位方式：</span>
                </div>
                <div class="drawerModInpt">
                    <el-radio v-model="ruleForm.type" label="1">手动增加职位</el-radio>
                    <el-radio v-model="ruleForm.type" label="2">自动搜索职位</el-radio>
                </div>
            </div>

            <template v-if="ruleForm.type == '2'">
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>职位推广：</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-checkbox v-model="ruleForm.is_zhiding">置顶</el-checkbox>
                        <el-checkbox v-model="ruleForm.is_tuijian">推荐</el-checkbox>
                        <el-checkbox v-model="ruleForm.is_jinji">紧急</el-checkbox>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>更新频率：</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input v-model="ruleForm.refresh_minutes" placeholder="请输入内容"
                            onkeyup="this.value=this.value.replace(/[^0-9]/g,'')" clearable>
                            <template slot="append">分钟</template>
                        </el-input>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>公司福利：</span>
                    </div>
                    <div class="drawerModInpt drawerModCheckb">
                        <el-checkbox-group v-model="ruleForm.welfare">
                            <el-checkbox v-for="(item) in welfareData" :label="item.id" :key="item.id" size="mini" border>
                                {{ item.name }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>公司行业：</span>
                    </div>
                    <div class="drawerModInpt drawerModCheckb">
                        <el-checkbox-group v-model="ruleForm.hy">
                            <el-checkbox v-for="(item) in industryData" :label="item.id" :key="item.id" size="mini" border>
                                {{ item.name }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>其他要求：</span>
                    </div>
                    <div class="drawerModInpt" style="display: flex;align-items: center;">
                        <el-select v-model="ruleForm.edu" placeholder="学历要求" clearable>
                            <el-option v-for="item in eduData" :key="item.id" :label="item.name"
                                :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="ruleForm.exp" placeholder="经验要求" style="margin-left: 10px;" clearable>
                            <el-option v-for="item in expData" :key="item.id" :label="item.name"
                                :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="ruleForm.sex" placeholder="性别要求" style="margin: 0 10px;" clearable>
                            <el-option v-for="item in sexData" :key="item.id" :label="item.name"
                                :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="ruleForm.report" placeholder="到岗时间" clearable>
                            <el-option v-for="item in reportData" :key="item.id" :label="item.name"
                                :value="item.id"></el-option>
                        </el-select>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>薪资要求：</span>
                    </div>
                    <div class="drawerModInpt" style="display: flex; align-items: center;">
                        <div class="drawerModSpans">
                            <span data-min="" data-max="" @click="handleSalary"
                                :class="!ruleForm.minsalary && !ruleForm.maxsalary ? 'butnCur' : ''">全部</span>
                            <span data-min="2000" data-max="4000" @click="handleSalary"
                                :class="ruleForm.minsalary == 2000 && ruleForm.maxsalary == 4000 ? 'butnCur' : ''">2000-4000</span>
                            <span data-min="4000" data-max="6000" @click="handleSalary"
                                :class="ruleForm.minsalary == 4000 && ruleForm.maxsalary == 6000 ? 'butnCur' : ''">4000-6000</span>
                            <span data-min="6000" data-max="8000" @click="handleSalary"
                                :class="ruleForm.minsalary == 6000 && ruleForm.maxsalary == 8000 ? 'butnCur' : ''">6000-8000</span>
                            <span data-min="8000" data-max="10000" @click="handleSalary"
                                :class="ruleForm.minsalary == 8000 && ruleForm.maxsalary == 10000 ? 'butnCur' : ''">8000-10000</span>
                            <span data-min="100000" data-max="" @click="handleSalary"
                                :class="ruleForm.minsalary == 10000 ? 'butnCur' : ''">10000以上</span>
                        </div>
                        <div class="drawerModShur">
                            <el-input v-model="ruleForm.minsalary" placeholder="" size="mini"
                                onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                            <i class="el-icon-minus"></i>
                            <el-input v-model="ruleForm.maxsalary" placeholder="" size="mini"
                                onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                        </div>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>更新时间：</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-radio v-model="ruleForm.uptime" label="1">今日</el-radio>
                        <el-radio v-model="ruleForm.uptime" label="2">最近三天</el-radio>
                        <el-radio v-model="ruleForm.uptime" label="3">最近七天</el-radio>
                        <el-radio v-model="ruleForm.uptime" label="4">最近一个月</el-radio>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>发布日期：</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-radio v-model="ruleForm.sdate" label="1">今日</el-radio>
                        <el-radio v-model="ruleForm.sdate" label="2">最近三天</el-radio>
                        <el-radio v-model="ruleForm.sdate" label="3">最近七天</el-radio>
                        <el-radio v-model="ruleForm.sdate" label="4">最近一个月</el-radio>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>会员等级：</span>
                    </div>
                    <div class="drawerModInpt drawerModCheckb">
                        <el-checkbox-group v-model="ruleForm.rating">
                            <el-checkbox v-for="(item) in ratingData" :label="item.id" :key="item.id" size="mini" border>
                                {{ item.name }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>搜索关键词：</span>
                    </div>
                    <div class="drawerModInpt">
                        <div class="sousuoGuancis">
                            <div class="sousuoGuanTips">
                                <el-tag :key="skwindex" v-for="(skwitem, skwindex) in ruleForm.searchKeyword" closable
                                        :disable-transitions="false" @close="handleSkwClose(skwindex)">
                                    {{ skwitem }}
                                </el-tag>
                                <el-input class="input-new-tag" v-if="skwVisible" v-model="skwValue" ref="saveTagInput"
                                    size="small" @keyup.enter.native="handleSkwConfirm" @blur="handleSkwConfirm">
                                </el-input>
                                <el-button v-else class="button-new-tag" size="small" @click="showSkwInput">添加</el-button>
                            </div>
                            <div class="sousuoGuanSuomd">
                                <span>回车确定输入内容，点击下方保存按钮后生效。</span>
                                <span>将按设置的关键词搜索职位，支持设置多个。不建议超过5个，将影响搜索效率。</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="drawerModLis">
                    <div class="drawerModTite">
                        <span>搜索条件：</span>
                    </div>
                    <div class="drawerModInpt" style="display: flex; align-items: center;">
                        <!--TODO 7.0 统一类别选择-->
                        <job_class @confirm="confirmJob" :selected="jobSelected"></job_class>
                        <!--TODO 7.0 统一城市选择-->
                        <city_class @confirm="confirmCity" :selected="citySelected"></city_class>
                    </div>
                </div>
            </template>

            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>PC图片：</span>
                </div>
                <div class="drawerModInpt" style="display: flex;align-items: center;">
                    <el-upload :accept="pic_accept" :action="uploadAction" :on-change="uploadChangePic"
                        :show-file-list="false">
                        <el-button size="small" type="primary">上传图片</el-button>
                    </el-upload>
                    <div class="up_sy_logo_div" style="margin-left: 15px;">
                        <el-image v-if="ruleForm.pic" style="width:200px;" :src="ruleForm.pic"
                            :preview-src-list="ruleForm.pic ? [ruleForm.pic] : []"></el-image>
                    </div>
                </div>
                <div class="drawerModTips">
                    <el-alert title="尺寸：1920px * 380px；PC端专题背景图、职位列表推广展位图；" type="info" show-icon
                        :closable="false"></el-alert>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>缩略图：</span>
                </div>
                <div class="drawerModInpt" style="display: flex;align-items: center;">
                    <el-upload :accept="pic_accept" :action="uploadAction" :on-change="uploadChangeSjlist_pic"
                        :show-file-list="false">
                        <el-button size="small" type="primary">上传图片</el-button>
                    </el-upload>
                    <div class="up_sy_logo_div" style="margin-left: 15px;">
                        <el-image v-if="ruleForm.sjlist_pic" style="width:200px;" :src="ruleForm.sjlist_pic"
                            :preview-src-list="ruleForm.sjlist_pic ? [ruleForm.sjlist_pic] : []"></el-image>
                    </div>
                </div>
                <div class="drawerModTips">
                    <el-alert title="尺寸：800px * 400px；专题列表展示图，移动端职位列表占位图、专题详情背景图，新入口展示图；" type="info" show-icon
                        :closable="false"></el-alert>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>排序：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input v-model="ruleForm.sort" placeholder="" clearable></el-input>
                </div>
                <div class="drawerModTips">
                    <el-alert title="值越大，权重越高" type="info" show-icon :closable="false"></el-alert>
                </div>
            </div>
            <div class="drawerModLis">
                <div class="drawerModTite">
                    <span>匹配关键词：</span>
                </div>
                <div class="drawerModInpt">
                    <el-input type="textarea" rows="4" placeholder="" v-model="ruleForm.keyword"></el-input>
                </div>
                <div class="drawerModTips">
                    <el-alert title="以，分隔。找工作列表搜索时，如果匹配到专题招聘设置的关键词，展示相关专题招聘入口" type="info" show-icon
                        :closable="false"></el-alert>
                </div>
            </div>
        </div>
        <div class="setBasicButn" style="border: none;">
            <el-button type="primary" size="medium" @click="submitForm('ruleForm')" :disabled="submitLoading">提交</el-button>
        </div>
    </div>
</template>
<script>
let emptyForm = {
    id: 0,
    title: '',
    sub_title: '',
    display: '1',
    tuiguang: '1',
    type: '1',
    is_zhiding: false,
    is_tuijian: false,
    is_jinji: false,
    refresh_minutes: '',//更新频率
    welfare: [],
    hy: [],
    edu: null,
    exp: null,
    sex: null,
    report: null,//到岗时间
    minsalary: null,
    maxsalary: null,
    uptime: null,//更新时间
    sdate: null,//发布日期
    rating: [],//会员等级
    searchKeyword: [], // 搜索关键词
    job_class_arr: [],
    city_class_arr: [],
    pic: '',//PC图片
    sjlist_pic: '',//缩略图
    keyword: '',
    sort: '',
};
module.exports = {
    props: {
        //special_job.id
        id: { type: [String, Number], default: 0 },
        welfareData: { type: [Array], default: [] },//福利
        industryData: { type: [Array], default: [] },//行业
        eduData: { type: [Array], default: [] },//学历
        expData: { type: [Array], default: [] },//工作经验
        sexData: { type: [Array], default: [] },//性别
        reportData: { type: [Array], default: [] },//到岗时间
        ratingData: { type: [Array], default: [] },//会员等级
    },
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            ruleForm: JSON.parse(JSON.stringify(emptyForm)),
            addloading: false,
            submitLoading: false,
            filePic: {},
            fileSjlist_pic: {},

            jobSelected: null,
            citySelected: null,

            // 搜索关键词
            skwVisible: false,
            skwValue: '',
            uploadAction: baseUrl + 'm=common&c=common_upload'
        }
    },
    components: {
        'job_class': httpVueLoader('../../../component/job_class.vue'),
        'city_class': httpVueLoader('../../../component/city_class.vue'),
    },
    created: function () {
        console.log('special_job_edit created');
    },
    methods: {
        // 搜索关键词
        handleSkwClose(index) {
            this.ruleForm.searchKeyword.splice(index, 1);
        },
        showSkwInput() {
            this.skwVisible = true;
            this.$nextTick(_ => {
                this.$refs.saveTagInput.$refs.input.focus();
            });
        },
        handleSkwConfirm() {
            let skwValue = this.skwValue;
            if (skwValue) {
                this.ruleForm.searchKeyword.push(skwValue);
            }
            this.skwVisible = false;
            this.skwValue = '';
        },

        uploadChangePic(file) {
            if (file.status !== 'success') return;
            this.ruleForm.pic = URL.createObjectURL(file.raw);
            // 复刻文件信息
            this.filePic = file;
        },
        uploadChangeSjlist_pic(file) {
            if (file.status !== 'success') return;
            this.ruleForm.sjlist_pic = URL.createObjectURL(file.raw);
            // 复刻文件信息
            this.fileSjlist_pic = file;
        },
        getInfo() {
            let _this = this;
            let params = {
                id: _this.id
            }
            this.ruleForm = JSON.parse(JSON.stringify(emptyForm));
            this.addloading = true;
            httpPost('m=yunying&c=special_job&a=info', params).then((response) => {
                _this.addloading = false;
                let res = response.data;
                if (res.error == 0) {
                    if (_this.id != '') {
                        _this.ruleForm.id = res.data.id;
                        _this.ruleForm.title = res.data.title;
                        _this.ruleForm.sub_title = res.data.sub_title;
                        _this.ruleForm.tuiguang = res.data.tuiguang;
                        _this.ruleForm.type = res.data.type;
                        if (res.data.type == '2') {
                            _this.ruleForm.is_zhiding = res.data.is_zhiding == '1' ? true : false;
                            _this.ruleForm.is_tuijian = res.data.is_tuijian == '1' ? true : false;
                            _this.ruleForm.is_jinji = res.data.is_jinji == '1' ? true : false;
                            _this.ruleForm.refresh_minutes = res.data.refresh_minutes;
                            _this.ruleForm.welfare = res.data.welfare_arr;
                            _this.ruleForm.hy = res.data.hy_arr;
                            _this.ruleForm.edu = res.data.edu > 0 ? res.data.edu : null;
                            _this.ruleForm.exp = res.data.exp > 0 ? res.data.exp : null;
                            _this.ruleForm.sex = res.data.sex > 0 ? res.data.sex : null;
                            _this.ruleForm.report = res.data.report > 0 ? res.data.report : null;
                            _this.ruleForm.minsalary = res.data.minsalary > 0 ? res.data.minsalary : null;
                            _this.ruleForm.maxsalary = res.data.maxsalary > 0 ? res.data.maxsalary : null;
                            _this.ruleForm.uptime = res.data.uptime;
                            _this.ruleForm.sdate = res.data.sdate;
                            _this.ruleForm.rating = res.data.rating_arr;
                            _this.ruleForm.searchKeyword = res.data.searchKeyword; // 搜索关键词
                            _this.ruleForm.job_class_arr = res.data.job_class > 0 ? [res.data.job_class] : [];
                            _this.ruleForm.city_class_arr = res.data.city_class > 0 ? [res.data.city_class] : [];

                            if (res.data.job_class != '' && res.data.job_class != '0') {
                                _this.jobSelected = {};
                                _this.jobSelected[res.data.job_class] = res.data.job_class_n;
                            }
                            if (res.data.city_class != '' && res.data.city_class != '0') {
                                _this.citySelected = {};
                                _this.citySelected[res.data.city_class] = res.data.city_class_n;
                            }
                        }
                        _this.ruleForm.pic = res.data.pic_n;
                        _this.ruleForm.sjlist_pic = res.data.sjlist_pic_n;
                        _this.ruleForm.keyword = res.data.keyword;
                        _this.ruleForm.sort = res.data.sort;
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        confirmJob(data) {
            this.ruleForm.job_class_arr = data.jobId;
        },
        confirmCity(data) {
            this.ruleForm.city_class_arr = data.cityId;
        },
        submitForm(formName) {
            let _this = this;
            let params = JSON.parse(JSON.stringify(this.ruleForm));
            params.is_zhiding = params.is_zhiding ? 1 : 0;
            params.is_tuijian = params.is_tuijian ? 1 : 0;
            params.is_jinji = params.is_jinji ? 1 : 0;

            if (params.title == '') {
                message.error('请填写专题名称！');
                return false;
            }
            if (params.type == 2) {
                //自动搜索职位时
                if (!params.refresh_minutes) {
                    message.error('自动搜索职位时，更新频率不能为空！');
                    return false;
                }
                if (!params.welfare.length && !params.hy.length && !params.edu && !params.exp && !params.sex && !params.report &&
                    !params.minsalary && !params.maxsalary && !params.uptime && !params.sdate && !params.rating.length && params.job_class_arr.length == 0
                    && params.city_class_arr.length == 0 && params.searchKeyword.length == 0) {
                    message.error('自动搜索职位时，搜索条件不能全为空！');
                    return false;
                }
            }
            if (params.pic == '') {
                message.error('请上传PC图片！');
                return false;
            }
            if (params.sjlist_pic == '') {
                message.error('请上传缩略图！');
                return false;
            }
            let formData = new FormData();
            Object.keys(params).forEach((key) => {
                if (Array.isArray(params[key])) {
                    params[key].forEach((v) => {
                        formData.append(key + '[]', v);
                    });
                } else {
                    formData.append(key, params[key]);
                }
            });
            if (this.filePic.hasOwnProperty('raw')) {
                formData.append('pic', this.filePic.raw);
            }
            if (this.fileSjlist_pic.hasOwnProperty('raw')) {
                formData.append('sjlist_pic', this.fileSjlist_pic.raw);
            }
            _this.submitLoading = true;
            httpPost('m=yunying&c=special_job&a=save', formData).then((response) => {
                let res = response.data;
                if (res.error === 0) {
                    message.success(res.msg);
                    _this.$emit("child-event");
                } else {
                    message.error(res.msg);
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                _this.submitLoading = false;
            });
        },
        handleSalary(event) {
            let min = event.currentTarget.dataset.min;
            let max = event.currentTarget.dataset.max;
            //TODO 设置兄弟元素为“未选中”
            $(".butnCur").each(function (index, ele) {
                $(ele).removeClass("butnCur")
            });
            //设置当前元素为“选中”
            event.currentTarget.className = "butnCur";
            this.ruleForm.minsalary = min;
            this.ruleForm.maxsalary = max;
        },
    },
    watch: {
        id: {
            handler: function (newValue, oldValue) {
                console.log('special_job_edit id', newValue)
                if (newValue) {
                    this.getInfo();
                } else {
                    this.ruleForm = JSON.parse(JSON.stringify(emptyForm));
                }
                this.filePic = {};
                this.fileSjlist_pic = {};
            },
            deep: true,
            immediate: true
        }
    }
};
</script>
<style scoped>
.drawerModInfo::-webkit-scrollbar {
    display: none;
}

.drawerModCheckb .el-checkbox {
    margin: 4px 5px;
}

.drawerModCheckb .el-checkbox.is-bordered+.el-checkbox.is-bordered {
    margin: 4px 5px;
}

.drawerModSpans {
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
}

.drawerModSpans span {
    overflow: hidden;
    padding: 0 6px;
    height: 26px;
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #414a60;
    border-radius: 4px;
    cursor: pointer;
}

.drawerModSpans span.butnCur {
    color: #fff;
    background-color: #2778f8;
}

.drawerModShur {
    overflow: hidden;
    position: relative;
    width: 200px;
    display: flex;
    align-items: center;
}
</style>