<!--推简历-->
<template>
    <div class="drawerModlue" v-loading="pageLoading">
        <div class="tuijianPoster">
            <div class="againMatcObile">
                <div class="againMobileRame">
                    <div class="againMobileTops">
                        <div class="againMobileTopOne">
                            <span>{{ time }}</span>
                        </div>
                        <div class="againMobileTopTwo">
                            <span>推荐简历</span>
                        </div>
                        <div class="againMobileTopOne">
                            <img src="../../images/moblie3.png" alt="">
                        </div>
                    </div>
                    <div class="againMobileLeiron">
                        <div class="againMobileList" v-for="(item, recK) in recResume" :key="recK" v-show="recNum > 0">
                            <div class="againMobileToux">
                                <el-avatar :size="50" :src="item.photo_n"></el-avatar>
                            </div>
                            <div class="againMobileData">
                                <div class="againMobiDataTitle">
                                    <el-link type="primary" :underline="false" :href="item.job_url" target="_blank">{{item.uname }}</el-link>
                                    <span class="moneyspn">{{ item.name }}</span>
                                </div>
                                <div class="againMobiDataTips">
                                    <span>{{item.age_n}}岁</span>
                                    <span>{{item.sex_n}}</span>
                                    <span>{{item.edu_n}}学历</span>
                                    <span>{{item.exp_n}}经验</span>
                                </div>
                            </div>
                            <div class="againMobileClose" @click="removeResume(recK)" v-show="auto_search != '1'">
                                <img src="../../../admin/images/qxicon.png" alt="">
                            </div>
                        </div>
                        <div class="againMobileLeNone" v-show="recNum == 0">
                            <div class="againleLeNoneIng">
                                <img src="../../../admin/images/zwtip.png" alt="">
                                <span>请从右侧挑选简历</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="overflow: hidden; position: relative; width: 100%; display: flex; align-items: center; justify-content: center;">
                    <el-button size="small" @click="setTgShow(1)" >复制文案</el-button>
                    <el-button type="primary" size="small" @click="setTgShow(2)">发送短信</el-button>
                </div>
            </div>
            <div class="posterCompany">
                <el-alert :closable="false" title="在这里为企业挑选匹配的简历发送给HR查看" type="warning" show-icon></el-alert>
                <div class="moduleSeachs">
                    <div class="moduleSeachleft moduleSeacJianlid">
                        <div class="eacJianlidiv">
                            <!--7.0 统一类别选择-->
                            <job_class @confirm="confirmJobSearch" :selected="jobClassSelected"></job_class>
                        </div>
                        <div class="eacJianlidiv">
                            <!--7.0 统一城市选择-->
                            <city_class @confirm="confirmCitySearch" :selected="cityClassSelected"></city_class>
                        </div>
                        <div class="eacJianlidiv">
                            <el-input v-model="searchKeyword" placeholder="请输入搜索内容" prefix-icon="el-icon-search"></el-input>
                        </div>
                        <div class="eacJianlidiv">
                            <el-button type="primary" icon="el-icon-search" size="small" @click="handleSearch">查询</el-button>
                        </div>
                        <div class="eacJianlidiv">
                            <el-select v-model="searchEdu" @change="upData" placeholder="学历要求" clearable>
                                <el-option v-for="(item, key) in userEdu" :key="key" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </div>
                        <div class="eacJianlidiv">
                            <el-select v-model="searchWord" @change="upData" placeholder="工作经验" clearable>
                                <el-option v-for="(item, key) in userWord" :key="key" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </div>
                        <div class="eacJianlidiv">
                            <el-select v-model="searchLabel" @change="upData" placeholder="简历标签" clearable>
                                <el-option v-for="(item, key) in userLabel" :key="key" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </div>
                        <div class="eacJianlidiv">
                            <el-select v-model="searchIntegrity" @change="upData" placeholder="简历完整度" clearable>
                                <el-option v-for="(item, key) in integrityArr" :key="key" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </div>
                        <div class="eacJianlidiv">
                            <el-select v-model="searchTime" @change="upData" placeholder="创建时间" clearable>
                                <el-option v-for="(item, key) in userTime" :key="key" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </div>
                        <div class="eacJianlidiv">
                            <el-select v-model="auto_search" @change="autoChange" placeholder="推广方式" clearable>
                                <el-option label="手动添加" value="2"></el-option>
                                <el-option label="自动匹配" value="1"></el-option>
                            </el-select>
                        </div>
                        <div v-show="auto_search == '1'" class="eacJianlidiv">
                            <el-input v-model="interval" placeholder="更新频率（分钟）" autocomplete="off"></el-input>
                        </div>
                    </div>
                </div>

                <div class="moduleElTable">
                    <el-table ref="table" :data="resumeList" v-loading="tableLoading" border style="width: 100%" :show-header="false" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" @selection-change="selectionChange" :empty-text="emptytext">
                        <el-table-column type="selection" width="55" v-if="auto_search != '1'"></el-table-column>
                        <el-table-column>
                            <template slot-scope="scope">
                                <div style="display: flex;">
                                    <el-avatar :size="50" :src="scope.row.photo_n"></el-avatar>
                                    <div class="tuiJianLeiSubject">
                                        <div class="tuiJianLeiTite">
                                            <el-button type="text">{{ scope.row.uname}}</el-button>
                                            <div class="tuiJianLeiComName">{{ scope.row.name }}</div>
                                        </div>
                                        <div class="tuiJianLeiTipes">
                                            <span>{{scope.row.age_n}}岁</span>
                                            <el-divider direction="vertical"></el-divider>
                                            <span>{{scope.row.sex_n}}</span>
                                            <el-divider direction="vertical"></el-divider>
                                            <span>{{scope.row.edu_n}}学历</span>
                                            <el-divider direction="vertical"></el-divider>
                                            <span>{{scope.row.exp_n}}经验</span>
                                            <el-divider direction="vertical"></el-divider>
                                            <span>{{scope.row.city_n}}</span>
                                            <el-divider direction="vertical"></el-divider>
                                            <span>{{scope.row.salary}}</span>
                                            <el-divider direction="vertical"></el-divider>
                                            <span>{{scope.row.report_n}}</span>
                                            <el-divider direction="vertical"></el-divider>
                                            <span>{{scope.row.type_n}}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column width="200">
                            <template slot-scope="scope">
                                <el-progress :percentage="parseInt(scope.row.integrity)"></el-progress>
                                <div>
                                    <span>{{ scope.row.lastupdate_n }}</span>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="modulePaging">
                    <div class="modulecz modulePagButn" v-show="auto_search != '1'">
                        <el-checkbox v-model="allCheckEd" @change="allCheckChange">全选</el-checkbox>
                        <span style="margin-left: 10px;" v-show="chooseData.length > 0">已选 <span style="color: #ff2b00">{{chooseData.length }}</span> 份简历</span>
                        <el-button size="mini" @click="addResume">添加</el-button>
                    </div>
                    <div class="modulePagNum">
                        <el-pagination :pager-count="pagerCount" background @current-change="handleCurrentChange" :current-page="currentPage" :page-size.sync="limit" :page-sizes="page_sizes" :total="resumeTotal" layout="total, prev, pager, next, jumper"></el-pagination>
                    </div>
                </div>
            </div>
        </div>

        <!-- 复制文案对话框 -->
        <div class="modluDrawer">
            <el-dialog title="复制文案" :visible.sync="copyLinkShow" :append-to-body="true" width="30%">
                <el-form status-icon label-position="top" label-width="100px">
                    <span>复制以下文案，以短信方式或通过社交软件推送给客户：</span>
                    <span>这是为您挑选的简历。点击链接查看：<el-link type="primary" :underline="false" :href="tsWapUrl" target="_blank">「页面链接」</el-link></span>
                    <el-form-item>
                        <el-button class="copyRecUrl" data-clipboard-action="copy" :data-clipboard-text="'这是为您挑选的简历。点击链接查看：'+tsWapUrl" @click="handleCopyUrl('copyRecUrl')" type="primary">复制文案</el-button>
                        <el-button @click="copyLinkShow = false">取消</el-button>
                    </el-form-item>
                </el-form>
            </el-dialog>
        </div>

        <!-- 发送短信对话框 -->
        <div class="modluDrawer">
            <el-dialog title="发送短信" :visible.sync="sendLinkShow" :append-to-body="true" width="30%">
                <el-form status-icon label-position="top" label-width="100px">
                    <el-form-item label="短信发送至">
                        <el-input v-model="linkInfo" :disabled="true"></el-input>
                    </el-form-item>
                    <el-form-item label="短信内容">
                        <el-input type="textarea" v-model="msgContent"></el-input>
                        <el-button type="text" @click="addLink">添加链接</el-button>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="sendResumeMsg">发送短信</el-button>
                        <el-button @click="sendLinkShow = false">取消</el-button>
                    </el-form-item>
                </el-form>
            </el-dialog>
        </div>
    </div>
</template>

<script>

    module.exports = {
        props: {
            match_obj: {
                type: Object,
                default: {}
            },
        },
        watch: {
            match_obj: {
                handler(v) {
                    this.logId = v.logId;

                    this.jobId = v.jobId;
                    this.uid = v.uid;

                    this.matchLinkMan = v.linkMan;
                    this.matchLinkTel = v.linkTel;

                    this.searchJobClass = v.jobClassId;
                    this.searchCityClass = v.cityClassId;
                    if (v.jobClassName) {
                        this.jobClassSelected[v.jobClassId] = v.jobClassName;
                    }
                    if (v.cityClassName) {
                        this.cityClassSelected[v.cityClassId] = v.cityClassName;
                    }
                    this.userEdu = v.userEdu;
                    this.userWord = v.userWord;
                    this.userLabel = v.userLabel;
                    this.userTime = v.userTime;
                    this.integrityArr = v.integrityArr;
                },
                immediate: true
            },
        },
        components: {
            'job_class': httpVueLoader('../../component/job_class.vue'),
            'city_class': httpVueLoader('../../component/city_class.vue'),
        },
        data: function () {
            return {
				pagerCount: 5,
                emptytext: '暂无数据',
                pageLoading: false,
                tableLoading: false,

                logId: '',

                jobId: '',
                uid: '',

                keyword: '',
                userEdu: [],
                userWord: [],
                userLabel: [],
                userTime: [],
                integrityArr: [],
                searchKeyword: '',
                searchJobClass: '',
                searchCityClass: '',
                jobClassSelected: {},
                cityClassSelected: {},
                searchEdu: '',
                searchWord: '',
                searchLabel: '',
                searchTime: '',
                searchIntegrity: '',

                auto_search: '',
                interval: '',

                resumeList: [],
                resumeTotal: 0,
                limit: 0,
				page_sizes:[],
                prevPage: 0,
                currentPage: 1,

                time: '',
                timeInterval: null,

                recResume: [],
                recNum: 0,
                tsWapUrl: '',
                copyLinkShow: false,
                sendLinkShow: false,
                matchLinkMan: '',
                matchLinkTel: '',
                linkInfo: '',
                msgContent: '这是为您挑选的简历。点击链接查看：「页面链接」',

                allCheckEd: false,
                chooseData: [],
            }
        },
        created: function () {
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

            if (this.logId > 0) {

                this.getRecLogInfo();
            } else {

                this.getResumeList();
            }
        },
        beforeDestroy() {
            clearInterval(this.timeInterval)
        },
        methods: {
            getRecLogInfo: function () {
                var self = this;
                httpPost('m=crm&c=crm_my_customer&a=getRecInfo', {id: self.logId}).then((result) => {
                    var res = result.data;
                    if (res.error == 0) {

                        self.recResume = res.data.recResumes;
                        self.recNum = res.data.recResumes.length;

                        self.auto_search = res.data.recInfo.auto;

                        if (res.data.recInfo.auto == 1) {

                            self.searchJobClass = res.data.recInfo.search_job;
                            self.searchCityClass = res.data.recInfo.search_city;
                            self.searchKeyword = res.data.recInfo.search_keyword;
                            self.searchEdu = res.data.recInfo.search_edu;
                            self.searchWord = res.data.recInfo.search_word;
                            self.searchIntegrity = res.data.recInfo.search_integrity;
                            self.searchTime = res.data.recInfo.search_time;
                            self.interval = res.data.recInfo.interval;
                        }

                        self.getResumeList();

                        if (typeof res.data.recInfo.re_search != 'undefined' && res.data.recInfo.re_search == '1') {
                            self.getAutoResumeList();
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },

            getResumeList: function () {
                var self = this;
                let params = {
                    uid: self.uid,
                    job_class: self.searchJobClass,
                    city_class: self.searchCityClass,
                    edu: self.searchEdu,
                    word: self.searchWord,
                    label: self.searchLabel,
                    integrity: self.searchIntegrity,
                    time: self.searchTime,
                    keyword: self.searchKeyword,
                    page: self.currentPage,
                    limit: self.limit
                };
                if (self.jobId > 0){
                    params.id = self.jobId;
                }
                this.tableLoading = true;
                self.emptytext = "数据加载中";
                httpPost('m=crm&c=crm_my_customer&a=getResumeList', params).then((result) => {
                    this.tableLoading = false;
                    var res = result.data;
                    if (res.error == 0) {

                        self.resumeList = res.data.resumeList;
						self.page_sizes = res.data.page_sizes;
						self.limit = res.data.page_size;
                        self.resumeTotal = parseInt(res.data.total)
                        if(self.prevPage != self.currentPage){
                            self.prevPage = self.currentPage;
                            self.$refs.table.bodyWrapper.scrollTop = 0;
                        }
                        if (self.resumeList.length === 0){
                            self.emptytext = "暂无数据";
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            getAutoResumeList: function () {
                var self = this;

                let params = {
                    uid: self.uid,
                    job_class: self.searchJobClass,
                    city_class: self.searchCityClass,
                    edu: self.searchEdu,
                    word: self.searchWord,
                    label: self.searchLabel,
                    integrity: self.searchIntegrity,
                    time: self.searchTime,
                    keyword: self.searchKeyword
                };
                if (self.jobId > 0){
                    params.id = self.jobId;
                }
                httpPost('m=crm&c=crm_my_customer&a=getAutoResumeList', params).then((result) => {

                    var res = result.data;
                    if (res.error == 0) {
                        self.recResume = res.data.resumeList;
                        self.recNum = res.data.resumeList.length;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },

            autoChange: function () {
                this.$refs.table.clearSelection();
                if (this.auto_search == 1) {
                    this.getAutoResumeList()
                } else {
                    this.recResume = [];
                    this.recNum = 0;
                }
            },
            upData: function () {
                this.getResumeList();
                if (this.auto_search == 1) {
                    this.getAutoResumeList();
                }
            },
            handleSearch() {
                this.currentPage = 1
                this.getResumeList();
                if (this.auto_search == 1) {
                    this.getAutoResumeList();
                }
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.getResumeList();
            },
            allCheckChange: function () {

                this.$refs.table.toggleAllSelection();
            },
            selectionChange: function (e) {
                if (this.resumeList.length != e.length) {
                    this.allCheckEd = false;
                } else {
                    this.allCheckEd = true;
                }
                this.chooseData = e;
            },
            addResume: function () {
                var _this = this;
                var recResume = deepClone(this.recResume);
                var recResumeId = [];
                for (let i in recResume) {
                    recResumeId.push(recResume[i].id);
                }
                if (this.chooseData.length > 0) {
                    for (let i in this.chooseData) {
                        if (recResumeId.indexOf(this.chooseData[i].id) == -1) {
                            recResume.push(this.chooseData[i]);
                        }
                    }
                    this.recResume = recResume;
                    this.recNum = recResume.length;
                } else {

                    message.error('请选择要推荐的数据！');
                    return;
                }
            },
            getRecWapUrl: function (type){
                let self = this;
                var param = {};
                if (self.auto_search == 1){

                    param.auto	=	1;
                    param.interval	=	self.interval;

                    param.search_job = self.searchJobClass;
                    param.search_city = self.searchCityClass;
                    param.search_keyword = self.searchKeyword;
                    param.search_edu = self.searchEdu;
                    param.search_word = self.searchWord;
                    param.search_label = self.searchLabel;
                    param.search_integrity = self.searchIntegrity;
                    param.search_time = self.searchTime;
                }else{

                    param.auto	=	2;
                }
                var resumeIdArr = [];
                self.recResume.forEach(item => {
                    resumeIdArr.push(item.id);
                });
                param.uid = self.uid;
                param.jobid = self.jobId;
                param.resumeids = resumeIdArr.join(',');
                if (self.logId > 0) {
                    param.id = self.logId;
                }
                httpPost('m=crm&c=crm_my_customer&a=setResumeLink', param).then((result) => {
                    var res = result.data;
                    if (res.error == 0){
                        self.tsWapUrl = res.data.wap_url;
                        if (type == 1){

                            self.copyLinkShow = true;
                        }else if (type == 2){

                            self.linkInfo = self.matchLinkMan + ' '+ self.matchLinkTel;
                            self.sendLinkShow = true;
                        }
                    }else{
                        message.error(res.msg);
                        return;
                    }
                });
            },
            setTgShow: function (type) {
                var self = this;

                if (self.auto_search == 1) {
                    if (self.interval == '') {
                        message.error('推送简历自动匹配，更新频率不能为空');
                        return false;
                    }
                    if (self.searchJobClass == '' && self.searchCityClass == '' && self.searchKeyword == '' && self.searchEdu == '' && self.searchWord == '' && self.searchLabel == '' && self.searchTime == '' && self.searchIntegrity.length == 0) {
                        message.error('自动匹配简历，搜索条件不能全部为空');
                        return false;
                    } else {
                        self.getRecWapUrl(type);
                    }
                } else {
                    if (self.recNum > 0) {
                        self.getRecWapUrl(type);
                    } else {
                        message.error('请先添加推广简历');
                        return false;
                    }
                }
            },
            handleCopyUrl: function (target) {
                let clipboard = new ClipboardJS('.'+target); // 获取点击按钮的元素
                /* 注意此事件监听是异步的   */
                clipboard.on('success', (e) => {
                    e.clearSelection();
                    // 释放内存
                    clipboard.destroy();
                    message.success('复制成功');
                });
                // 复制失败
                clipboard.on('error', (e) => {
                    // 释放内存
                    clipboard.destroy();
                    message.error('该浏览器不支持自动复制');
                });
            },
            addLink: function () {
                let _this = this;
                _this.msgContent = _this.msgContent + '「页面链接」';
            },
            sendResumeMsg: function(){
                var self = this;
                var param = {};
                param.uid = self.uid;
                param.mobile = self.matchLinkTel;
                param.content = self.msgContent;
                param.link = self.tsWapUrl;
                httpPost('m=crm&c=crm_my_customer&a=sendResumeMsg', param).then((result) => {
                    var res = result.data;
                    if (res.error == 0){
                        message.success(res.msg, function () {
                            self.sendLinkShow = false;
                        })
                    }else {
                        message.error(res.msg);
                        return;
                    }
                });
            },
            removeResume: function (reck) {
                var recResume = deepClone(this.recResume);
                recResume.splice(reck, 1);
                this.recResume = recResume;
                this.recNum = recResume.length;
            },
            resetResumeList: function () {
                this.currentPage = 1
                this.getResumeList();
                if (this.auto_search == 1) {
                    this.getAutoResumeList();
                }
            },

            // 搜索职位选择
            confirmJobSearch(data) {
                this.searchJobClass = data.jobId.join(',');
            },
            // 搜索城市选择
            confirmCitySearch(data) {
                this.searchCityClass = data.cityId.join(',');
            },
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
    .moduleSeachleft{flex-wrap:wrap}
    .moduleSeachleft .el-select{margin-right:6px;margin-bottom:5px}
</style>