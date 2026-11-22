<template>
    <div class="drawerModlue" v-loading="pageloading">
        <div class="tuijianPoster">
            <div class="againMatcObile">
                <div class="againMobileRame">
                    <div class="againMobileTops">
                        <div class="againMobileTopOne">
                            <span>{{ time }}</span>
                        </div>
                        <div class="againMobileTopTwo">
                            <span>推荐职位</span>
                        </div>
                        <div class="againMobileTopOne">
                            <img src="../../images/moblie3.png" alt="">
                        </div>
                    </div>
                    <div class="againMobileLeiron">
                        <div class="againMobileList" v-for="(item, recK) in recJob" :key="recK" v-show="recNum > 0">
                            <div class="againMobileData">
                                <div class="againMobiDataTitle">
                                    <el-link type="primary" :underline="false" :href="item.job_url" target="_blank">{{item.name }}</el-link>
                                    <span class="moneyspn">{{ item.salary_n }}</span>
                                </div>
                                <div class="againMobiDataTips">
                                    <span>{{ item.cityStr }}</span>
                                    <span>{{ item.edu_n }}学历</span>
                                    <span>{{ item.exp_n }}经验</span>
                                </div>
                                <div class="againMobiDatConpany">
                                    <span>{{ item.com_name }}</span>
                                </div>
                            </div>
                            <div class="againMobileClose" @click="removeJob(recK)" v-show="auto_search != '1'">
                                <img src="../../../admin/images/qxicon.png" alt="">
                            </div>
                        </div>
                        <div class="againMobileLeNone" v-show="recNum == 0">
                            <div class="againleLeNoneIng">
                                <img src="../../../admin/images/zwtip.png" alt="">
                                <span>请从右侧挑选职位</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="againMobileButon" @click="addInfo">
                    <span class="faduanx scerweims">分享朋友圈</span>
                </div>
            </div>
            <div class="posterCompany">
                <el-alert :closable="false" title="在这里为客户精选职位进行推广，您可以筛选职位手动添加，也可以选择搜索条件自动匹配职位分享" type="warning" show-icon></el-alert>
                <div class="moduleSeachs">
                    <div class="moduleSeachleft">
                        <div class="tableSeachInpt" style="margin-bottom: 0px;">
                            <job_class @confirm="confirmJobSearch" :selected="jobSelected"></job_class>
                        </div>
                        <div class="tableSeachInpt" style="margin-bottom: 0px;">
                            <city_class @confirm="confirmCitySearch" :selected="citySelected"></city_class>
                        </div>
                        <div class="tableSeachInpt" style="margin-bottom: 5px;;">
                            <el-input v-model="searchKeyword" placeholder="请输入搜索内容" prefix-icon="el-icon-search"></el-input>
                        </div>
                        <div class="tableSeachInpt" style="margin-bottom: 6px;;">
                            <el-button type="primary" icon="el-icon-search" size="small" @click="search">查询</el-button>
                        </div>
                        <el-select v-model="searchEdu" @change="upData" placeholder="学历要求" clearable>
                            <el-option v-for="(item, key) in jobEdu" :key="key" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="searchExp" @change="upData" placeholder="工作经验" clearable>
                            <el-option v-for="(item, key) in jobExp" :key="key" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="searchReport" @change="upData" placeholder="到岗时间" clearable>
                            <el-option v-for="(item, key) in jobReport" :key="key" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="searchSTime" @change="upData" placeholder="发布日期" clearable>
                            <el-option v-for="(item, key) in jobTime" :key="key" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="searchTime" @change="upData" placeholder="更新日期" clearable>
                            <el-option v-for="(item, key) in jobTime" :key="key" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="searchSalary" @change="upData" placeholder="最低薪资" clearable>
                            <el-option v-for="(item, key) in jobSalary" :key="key" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="search_wel" @change="jobWelChange" placeholder="企业福利" multiple collapse-tags clearable>
                            <el-option v-for="(item, key) in jobWelfare" :key="key" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="auto_search" @change="autoChange" placeholder="推广方式" clearable>
                            <el-option label="手动添加" value="2"></el-option>
                            <el-option label="自动匹配" value="1"></el-option>
                        </el-select>
                        <div class="tableSeachInpt" style="margin-bottom: 0px;;" v-show="auto_search == '1'">
                            <el-input v-model="interval" placeholder="更新频率" autocomplete="off" size="small">
                                <template slot="append">分钟</template>
                            </el-input>
                        </div>
                    </div>
                </div>
                <div class="moduleElTable">
                    <el-table ref="table" :data="jobList" v-loading="tableloading" border style="width: 100%" :show-header="false" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" @selection-change="selectionChange" :empty-text="emptytext">
                        <el-table-column type="selection" width="55" v-if="auto_search != '1'"></el-table-column>
                        <el-table-column>
                            <template slot-scope="scope">
                                <el-link type="primary" :href="scope.row.job_url" target="_blank">{{ scope.row.name }}</el-link>
                                <div class="tuiJianLeiComName">{{ scope.row.com_name }}</div>
                            </template>
                        </el-table-column>
                        <el-table-column>
                            <template slot-scope="scope">
                                <span style="color: red;">{{ scope.row.salary_n }}</span>
                                <div><span>{{ scope.row.city_n }} | {{ scope.row.edu_n }}学历 | {{ scope.row.exp_n }}经验</span></div>
                                <el-tag type="success" size="small" v-for="(wel, wk) in scope.row.welfare_n" :key="wk">{{ wel }}</el-tag>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="modulePaging">
                    <div class="modulecz modulePagButn" v-show="auto_search != '1'" style="display: flex; align-items: center;">
                        <el-checkbox v-model="allchecked" @change="allcheckChange">全选</el-checkbox>
                        <div style="overflow: hidden; position: relative; font-size: 14px; color: #606266;">
                            <span style="margin-left: 10px;" v-show="choosedata.length > 0">已选 <span style="color: #ff2b00">{{choosedata.length }}</span> 份职位</span>
                        </div>
                        <el-button size="mini" @click="addJob">添加</el-button>
                    </div>
                    <div class="modulePagNum">
                        <el-pagination background @current-change="handleCurrentChange" :page-size.sync="limit" :page-sizes="page_sizes" :current-page="currentPage" :total="jobTotal" layout="total, sizes, prev, pager, next, jumper" :pager-count="pagerCount"></el-pagination>
                    </div>
                </div>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="" :visible.sync="QrCodeShow" append-to-body width="250px" center>
                <div style="text-align: center">
                    <img :src="shareCode" width="200" height="200">
                </div>
                <div style="text-align: center">
                    <span>二维码分享</span>
                </div>
            </el-dialog>
        </div>
        <el-dialog title="分享设置" width="500px" :visible.sync="shareBoxShow" :modal-append-to-body="false" append-to-body>
            <div class="recJobPopup">
                <div class="recJobPoCont layui-form">
                    <div class="recJobPoIput">
                        <div class="recJobFenxis">
                            <span class="recJobTites">分享标题：</span>
                            <div class="recJobFromde">
                                <el-input v-model="shareTitle" autocomplete="off" size="small"></el-input>
                            </div>
                        </div>
                        <div class="recJobFenxis">
                            <span class="recJobTites">分享描述：</span>
                            <div class="recJobFromde">
                                <el-input type="textarea" :rows="2" v-model="shareDesc" autocomplete="off"></el-input>
                            </div>
                        </div>
                        <div class="recJobFenxis">
                            <span class="recJobTites">分享图片：</span>
                            <div class="recJobFromde">
                                <el-upload class="avatar-uploader" :accept="pic_accept" :action="uploadAction" :show-file-list="false" :on-change="logoshareChange">
                                    <img v-if="logoShare" :src="logoShare" class="avatar">
                                    <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                                </el-upload>
                            </div>
                        </div>
                        <div class="recJobFenxis">
                            <div class="recJobFrmBurn">
                                <el-button size="mini" @click="getCode" :loading="shareloading">确认</el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script>

    module.exports = {
        props: {
            lid: {
                type: String,
                default: ''
            },
        },
        watch: {
            lid: {
                handler(n, v) {
                    this.logId = n;
                },
                immediate: true
            },
        },
        data: function () {
            return {
                pic_accept: localStorage.getItem("pic_accept"),
                pagerCount: 5,
                emptytext: '暂无数据',
                pageloading: false,
                tableloading: false,
                logId: '',

                keyword: '',
                jobEdu: [],
                jobExp: [],
                jobReport: [],
                jobWelfare: [],
                jobTime: [],
                jobSalary: [
                    {id: 2000, name: '2000元'},
                    {id: 4000, name: '4000元'},
                    {id: 6000, name: '6000元'},
                    {id: 8000, name: '8000元'},
                    {id: 10000, name: '10000元'},
                ],
                jobSelected: {},
                citySelected: {},
                searchJobClass: '',
                searchCityClass: '',
                searchEdu: '',
                searchExp: '',
                searchReport: '',
                searchTime: '',
                searchSTime: '',
                searchWel: '',
                searchKeyword: '',
                searchSalary: '',
                search_wel: [],
                searchWelName: '',
                interval: '',
                auto_search: '',

                jobList: [],
                jobTotal: 0,
                page_sizes: [],
                limit: 0,
                prevPage: 0,
                currentPage: 1,

                time: '',
                timeInterval: null,
                recJob: [],
                recNum: 0,
                shareTitle: '',
                shareDesc: '',
                logoShare: '',
                newlogoShare: null,
                shareloading: false,
                shareBoxShow: false,

                QrCodeShow: false,
                pytoken: '',
                baseUrl: '',
                shareCode: '',

                allchecked: false,
                choosedata: [],
                uploadAction: baseUrl + 'm=common&c=common_upload'
            }
        },
        components: {
            'job_class': httpVueLoader('../../component/job_class.vue'),
            'city_class': httpVueLoader('../../component/city_class.vue'),
        },
        created: function () {

            this.pytoken = localStorage.getItem('pytoken')
            this.baseUrl = localStorage.getItem('baseUrl')

            var d = new Date();
            var h = d.getHours(),
                m = d.getMinutes();
            if (h < 10) {
                h = '0' + h;
            }
            if (m < 10) {
                m = '0' + m;
            }
            this.time = h + ':' + m;
            clearInterval(this.timeInterval)
            this.timeInterval = setInterval(() => {
                let date = new Date();
                let hour = date.getHours(),
                    minutes = date.getMinutes();
                if (hour < 10) {
                    hour = '0' + hour;
                }
                if (minutes < 10) {
                    minutes = '0' + minutes;
                }
                this.time = hour + ':' + minutes;
            }, 1000);

            this.getData();
            if (this.logId > 0) {
                this.getRecLogInfo();
            } else {
                this.getJobList();
            }
        },
        beforeDestroy() {
            clearInterval(this.timeInterval)
        },
        methods: {
            // 搜索职位选择
            confirmJobSearch(data) {
                this.searchJobClass = data.jobId.join(',');
            },
            // 搜索城市选择
            confirmCitySearch(data) {
                this.searchCityClass = data.cityId.join(',');
            },
            getData: function () {
                var self = this;
                this.pageloading = true;
                httpPost('m=crm&c=crm_index&a=getJobData', {type: 'job'}).then((result) => {

                    this.pageloading = false;

                    var res = result.data;
                    if (res.error == 0) {
                        self.jobEdu = res.data.jobEdu
                        self.jobExp = res.data.jobExp
                        self.jobReport = res.data.jobReport
                        self.jobWelfare = res.data.jobWelfare
                        self.jobTime = res.data.jobTime
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            getRecLogInfo: function () {
                var self = this;

                httpPost('m=crm&c=crm_index&a=getRecInfo', {id: self.logId}).then((result) => {
                    var res = result.data;
                    if (res.error == 0) {

                        self.recJob = res.data.recJobs;
                        self.recNum = res.data.recJobs.length;
                        self.shareTitle = res.data.recInfo.title;
                        self.shareDesc = res.data.recInfo.desc;
                        self.logoShare = res.data.recInfo.logo;
                        self.auto_search = res.data.recInfo.auto;

                        if (res.data.recInfo.auto == 1) {

                            if (typeof res.data.recInfo.search_job != 'undefined' && res.data.recInfo.search_job != '') {
                                self.jobselected[res.data.recInfo.search_job] = res.data.recInfo.search_job_name;
                                self.searchJobClass = res.data.recInfo.search_job;
                            }
                            if (typeof res.data.recInfo.search_city != 'undefined' && res.data.recInfo.search_city != '') {
                                self.cityselected[res.data.recInfo.search_city] = res.data.recInfo.search_city_name;
                                self.searchCityClass = res.data.recInfo.search_city;
                            }
                            self.searchKeyword = res.data.recInfo.search_keyword;
                            self.searchEdu = res.data.recInfo.search_edu;
                            self.searchExp = res.data.recInfo.search_exp;
                            self.searchReport = res.data.recInfo.search_report;
                            self.searchTime = res.data.recInfo.search_time;
                            self.searchSTime = res.data.recInfo.search_stime;
                            self.search_wel = res.data.recInfo.search_wel != '' ? res.data.recInfo.search_wel.split("，") : [];
                            self.searchWelName = res.data.recInfo.search_welfare;
                            self.searchSalary = res.data.recInfo.search_salary;
                            self.interval = res.data.recInfo.interval;
                        }

                        self.getJobList();

                        if (typeof res.data.recInfo.re_search != 'undefined' && res.data.recInfo.re_search == '1') {
                            self.getAutoJobList();
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })

            },
            getJobList: function () {
                var self = this;

                let params = {
                    page: self.currentPage,
                    limit: self.limit,
                    keyword: self.searchKeyword,
                    job_class: self.searchJobClass,
                    city_class: self.searchCityClass,
                    edu: self.searchEdu,
                    exp: self.searchExp,
                    report: self.searchReport,
                    time: self.searchTime,
                    stime: self.searchSTime,
                    welfare: self.searchWelName,
                    salary: self.searchSalary,
                };
                this.tableloading = true;
                self.emptytext = "数据加载中";
                httpPost('m=crm&c=crm_index&a=getJobList', params).then((result) => {
                    this.tableloading = false;
                    var res = result.data;
                    if (res.error == 0) {
                        self.jobList = res.data.jobList;
                        self.jobTotal = parseInt(res.data.total);
                        self.page_sizes = res.data.page_sizes;
                        self.limit = res.data.page_size;
                        if(self.prevPage != self.currentPage){
                            self.prevPage = self.currentPage;
                            self.$refs.table.bodyWrapper.scrollTop = 0;
                        }
                        if (self.jobList.length === 0) {
                            self.emptytext = "暂无数据";
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            getAutoJobList: function () {
                var self = this;

                let params = {
                    keyword: self.searchKeyword,
                    job_class: self.searchJobClass,
                    city_class: self.searchCityClass,
                    edu: self.searchEdu,
                    exp: self.searchExp,
                    report: self.searchReport,
                    time: self.searchTime,
                    stime: self.searchSTime,
                    welfare: self.searchWelName,
                    salary: self.searchSalary,
                };

                httpPost('m=crm&c=crm_index&a=getAutoJobList', params).then((result) => {

                    var res = result.data;
                    if (res.error == 0) {
                        self.recJob = res.data.jobList;
                        self.recNum = res.data.jobList.length;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            jobWelChange: function () {
                var jobWelfare = deepClone(this.jobWelfare);
                var search_wel = deepClone(this.search_wel);
                var searchWelNameArr = [];
                var searchWelName = '';
                for (let i in jobWelfare) {
                    if (search_wel.indexOf(jobWelfare[i].id) != -1) {
                        searchWelNameArr.push(jobWelfare[i].name);
                    }
                }
                if (searchWelNameArr.length > 0) {
                    searchWelName = searchWelNameArr.join('，');
                }
                this.searchWelName = searchWelName;
                this.upData();
            },
            autoChange: function () {

                this.$refs.table.clearSelection();

                if (this.auto_search == 1) {
                    this.getAutoJobList();
                } else {
                    this.recJob = [];
                    this.recNum = 0;
                }
            },
            upData: function () {
                this.getJobList();
                if (this.auto_search == 1) {
                    this.getAutoJobList();
                }
            },
            search() {
                this.currentPage = 1
                this.getJobList()
            },

            handleCurrentChange(val) {
                this.currentPage = val;
                this.getJobList()
            },

            allcheckChange: function () {

                this.$refs.table.toggleAllSelection();

            },
            selectionChange: function (e) {
                if (this.jobList.length != e.length) {
                    this.allchecked = false;
                } else {
                    this.allchecked = true;
                }
                this.choosedata = e;
            },
            addJob: function () {
                var _this = this;
                var recJob = deepClone(this.recJob);
                var recJobid = [];
                for (let i in recJob) {
                    recJobid.push(recJob[i].id);
                }
                if (this.choosedata.length > 0) {
                    for (let i in this.choosedata) {
                        if (recJobid.indexOf(this.choosedata[i].id) == -1) {
                            recJob.push(this.choosedata[i]);
                        }
                    }
                    this.recJob = recJob;
                    this.recNum = recJob.length;
                } else {
                    message.error('请选择要推荐的数据！');
                    return;
                }
            },
            addInfo: function () {
                var self = this;

                if (self.auto_search == 1) {
                    if (self.interval == '') {
                        message.warning('推广职位自动匹配，更新频率不能为空');
                        return false;
                    }
                    if (self.searchJobClass == '' && self.searchCityClass == '' && self.searchKeyword == '' && self.searchEdu == '' && self.searchExp == '' && self.searchReport == '' && self.searchTime == '' && self.searchSTime == '' && self.search_wel.length == 0 && self.searchSalary == '') {
                        message.warning('自动匹配职位，搜索条件不能全部为空');
                        return false;
                    } else {
                        self.shareBoxShow = true;
                    }
                } else {

                    if (self.recJob.length > 0) {

                        self.shareBoxShow = true;
                    } else {
                        message.warning('请先添加推广职位');
                        return false;
                    }
                }
            },
            removeJob: function (reck) {
                var recJob = deepClone(this.recJob);
                recJob.splice(reck, 1);
                this.recJob = recJob;
                this.recNum = recJob.length;
            },
            getCode: function () {
                var self = this;

                var params = new FormData();
                if (this.newlogoShare) {
                    params.append('file', this.newlogoShare);
                }
                var jobIdArr = [];
                self.recJob.forEach(item => {
                    jobIdArr.push(item.id);
                });
                params.append('jobids', jobIdArr.join(','));

                if (self.autoSearch == 1) {

                    params.append('auto', '1');
                    params.append('interval', self.interval);

                    params.append('search_job', self.searchJobClass);
                    params.append('search_city', self.searchCityClass);
                    params.append('search_keyword', self.searchKeyword);
                    params.append('search_edu', self.searchEdu);
                    params.append('search_exp', self.searchExp);
                    params.append('search_report', self.searchReport);
                    var search_welfare = self.search_wel.length > 0 ? self.search_wel.join('，') : '';
                    params.append('search_welfare', search_welfare);
                    params.append('search_salary', self.searchSalary);
                    params.append('search_time', self.searchTime);
                    params.append('search_stime', self.searchSTime);
                } else {

                    params.append('auto', '2');
                }
                params.append('jobids', jobIdArr.join(','));
                params.append('title', self.shareTitle);
                params.append('desc', self.shareDesc);
                if (parseInt(self.logId) > 0) {
                    params.append('id', self.logId);
                }
                this.shareloading = true;
                httpPost('m=crm&c=crm_index&a=setRecJob', params).then((result) => {
                    this.shareloading = false;
                    var res = result.data;
                    if (res.error == 9) {

                        self.shareCode = `${self.baseUrl}?m=crm&c=crm_index&a=getShareCode&ids=${res.data.ids}&pytoken=${self.pytoken}`;
                        self.shareBoxShow = false;
                        self.QrCodeShow = true;
                        self.resetJobList();
                    } else {
                        message.error(data.msg);
                        return false;
                    }
                }).catch(function (e) {
                    console.log(e)
                })

            },
            resetJobList: function () {
                this.currentPage = 1
                this.getJobList();
                if (this.auto_search == 1) {
                    this.getAutoJobList();
                }
            },
            jobclass_search: function (query) {

                this.jobclass_list = jobcityclass_slist({
                    keyword: query,
                    type: 'jobclass',
                    choosed: [this.searchJobClass]
                });

            },
            cityclass_search: function (query) {

                this.cityclass_list = jobcityclass_slist({
                    keyword: query,
                    type: 'cityclass',
                    choosed: [this.searchCityClass]
                });

            },
            logoshareChange: function (file) {
                this.logoShare = URL.createObjectURL(file.raw);
                this.newlogoShare = file.raw;
            }
        }
    };
</script>
<style scoped>
    .drawerModInfo::-webkit-scrollbar{display:none}
    .drawerModlue{overflow:hidden;position:relative;width:100%;height:100%}
    .drawerModInfo{overflow-y:auto;height:calc(100% - 80px)}
    .moduleSeachs{width:100%;padding:12px 0 0 0}
    .moduleElTable{height:calc(100% - (180px + 60px + 12px + 12px));padding:0;width:100%}
    .moduleSeachs{flex-wrap:wrap}
    .moduleSeachleft{margin-bottom:8px;flex-wrap:wrap}
    .moduleSeachleft .el-select{margin-right:6px;margin-bottom:5px}
    .cell .el-tag{margin-right: 5px; margin-bottom: 4px;}
</style>