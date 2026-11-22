<!--客户列表-->
<template>
    <div class="moduleElHight" style="padding: 0 20px;">
        <!-- 简化版客户公海头部 -->
        <template v-if="simple">
            <div class="moduleElSearchInf">
                <div class="moduleElTabInpt" style="flex-wrap: wrap;">
                    <div class="moduleInptList moduleInptWidt">
                        <el-input size="small" placeholder="搜索关键词" v-model="keyword" class="input-with-select">
                            <el-select v-model="ktype" slot="prepend" placeholder="请选择">
                                <el-option label="公司名" value="1"></el-option>
                                <el-option label="联系人" value="2"></el-option>
                                <el-option label="电话" value="3"></el-option>
                            </el-select>
                        </el-input>
                    </div>
                    <div class="moduleInptList" style="display: flex; align-items: center;">
                        <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                        <!-- <el-button size="mini" @click="reset" style="margin: 0 8px;">重置</el-button> -->
                        <el-button class="" size="mini" style="margin: 0 0 0 8px;" type="primary" @click="addcustomer" icon="el-icon-document-add">新增客户</el-button>
                    </div>
                </div>
            </div>
        </template>
        <!-- 完整版头部 -->
        <template v-else>
            <div class="moduleElSearchInf">
                <div class="moduleElTabInpt" style="flex-wrap: wrap;">
                    <div class="moduleInptList">
                        <el-select size="small" v-model="crm_uid" placeholder="客户经理" clearable @change="search">
                            <el-option v-for="item in adminUserList" :key="item.uid" :label="item.name" :value="item.uid"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList">
                        <el-select size="small" v-model="crmType" placeholder="客户等级" clearable @change="search">
                            <el-option v-for="item in client_type" :key="item.val" :label="item.name" :value="item.val"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList">
                        <el-select size="small" v-model="crmStatus" placeholder="客户标签" clearable @change="search">
                            <el-option v-for="item in client_status" :key="item.val" :label="item.name" :value="item.val"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="rating" placeholder="会员类型" clearable @change="search">
                            <el-option v-for="item in ratingArr" :key="item.id" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="vipEtime" placeholder="到期时间" clearable @change="search">
                            <el-option v-for="(item,key) in vipETimeOpt" :key="key" :label="item.label" :value="item.value"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="yyzz_status" placeholder="资质认证" clearable @change="search">
                            <el-option v-for="(item,key) in rzStatusArr" :key="key" :label="item.label" :value="item.value"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="hasJob" placeholder="拥有职位" clearable @change="search">
                            <el-option v-for="(item, key) in hasJobOptions" :key="key" :label="item.label" :value="item.value"></el-option>
                        </el-select>
                    </div>
                    <div v-show="kh != 4" class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="lfTime" placeholder="上次跟进" clearable @change="search">
                            <el-option v-for="(item,key) in lfTimeOptions" :key="key" :label="item.label" :value="item.value"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="nfTime" placeholder="计划跟进" clearable @change="search">
                            <el-option v-for="(item,key) in nfTimeOptions" :key="key" :label="item.label" :value="item.value"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="fact_status" placeholder="实地核验" clearable @change="search">
                            <el-option label="是" value="1"></el-option>
                            <el-option label="否" value="2"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList">
                        <el-date-picker size="small" style="width: 230px;" v-model="loginDate" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="登录开始" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="search"></el-date-picker>
                    </div>
                    <div class="moduleInptList">
                        <el-date-picker size="small" style="width: 230px;" v-model="regDate" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="注册开始" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="search"></el-date-picker>
                    </div>
                    <div class="moduleInptList moduleInptWidt" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-input size="small" placeholder="搜索关键词" v-model="keyword" class="input-with-select">
                            <el-select v-model="ktype" slot="prepend" placeholder="请选择">
                                <el-option label="公司名" value="1"></el-option>
                                <el-option label="联系人" value="2"></el-option>
                                <el-option label="电话" value="3"></el-option>
                                <el-option label="UID" value="4"></el-option>
                            </el-select>
                        </el-input>
                    </div>
                    <div class="moduleInptList">
                        <el-button size="mini" type="primary" icon="el-icon-search" @click="search">查询</el-button>
                        <el-button size="mini" icon="el-icon-refresh" @click="reset">重置</el-button>
                        <el-button size="mini" type="primary" icon="el-icon-document-add" @click="addcustomer">新增客户</el-button>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnKai': seachbutn }">
                        <el-button type="primary" class="zhankai" @click="seachbutn = !seachbutn, tableHig = !tableHig" size="mini" plain>展开<i class="el-icon-arrow-down el-icon--right"></i></el-button>
                        <el-button type="primary" class="shouqi" @click="seachbutn = !seachbutn, tableHig = !tableHig" size="mini" plain>合并<i class="el-icon-arrow-up el-icon--right"></i></el-button>
                    </div>
                </div>
            </div>
            <div class="admin_datatip">
                <i class="el-icon-document" style="padding: 2px 4px 0 0;"></i>
                {{customerTitle}}：<el-link type="primary" :underline="false" @click="statusChange('')">{{numArr.companyAllNum}}</el-link>
                <span class="admin_datatip_n">未审核：<el-link type="primary" :underline="false" @click="statusChange(1)">{{numArr.companyStatusNum1}}</el-link> </span>
                <span class="admin_datatip_n">未通过：<el-link type="primary" :underline="false" @click="statusChange(3)">{{numArr.companyStatusNum2}}</el-link> </span>
                <span class="admin_datatip_n">已锁定：<el-link type="primary" :underline="false" @click="statusChange(2)">{{numArr.companyStatusNum3}}</el-link> </span>
                <span class="admin_datatip_n">暂停招聘：<el-link type="primary" :underline="false" @click="statusChange(4)">{{numArr.companyStatusNum4}}</el-link> </span>
            </div>
        </template>
        <div :class="{moduleElTable: true, moduleElTableSimple: simple, moduleElMoreF: !tableHig, moduleElMoreT: tableHig}" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table ref="table" :data="tableData" stripe style="width: 100%" v-loading="list_loading" @selection-change="selectionChange" :default-sort="{prop: 'login_date', order: 'descending'}" @sort-change="sortChange" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :empty-text="emptytext">
                <el-table-column type="selection"></el-table-column>
                <el-table-column prop="uid" label="客户ID" sortable="custom" width="90"></el-table-column>
                <el-table-column label="客户名称" min-width="270">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <div style="overflow: hidden;display: flex; align-items: center;">
                                <span style="overflow: hidden;display: block; padding-right: 6px;">{{scope.row.name}}</span>
                                <span style="color:#f00" v-if="scope.row.r_status==0">未审核</span>
                                <span style="color:#f00" v-else-if="scope.row.r_status==2">已锁定</span>
                                <span style="color:#f00" v-else-if="scope.row.r_status==3">未通过</span>
                                <span style="color:#f00" v-else-if="scope.row.r_status==4">已暂停</span>
                            </div>

                            <div>{{scope.row.username}}</div>
                        </div>
                        <div class="moduCrmIcon">
                            <comcert :key="scope.row.uid" :row="scope.row" :config="config" @close-update="closeComcert"></comcert>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="等级 | 到期" prop="vipetime" sortable="custom" width="140">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <span v-if="scope.row.isvip==1">{{scope.row.rating_name}}</span>
                            <span v-else>{{scope.row.oldrating_name?scope.row.oldrating_name:scope.row.rating_name}}</span>
                            <div>
                                <span :style="scope.row.vipetime_status=='2'?'color:#ff2b00;':''">{{scope.row.vipetime_n}}</span>
                                <span v-if="scope.row.r_status!='4' && scope.row.vipetime_status!='0'" style="color:#ff2b00;">({{scope.row.vipDay}})</span>
                                <div v-if="scope.row.r_status=='4'" style="color:#ff2b00;">{{scope.row.ztDay}}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="联系信息" min-width="220">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <span>{{scope.row.linkman}}</span>
                            <span v-if="scope.row.linktel">{{scope.row.linktel}}</span>
                            <span>{{scope.row.job_city_one}}{{scope.row.job_city_two}}{{scope.row.job_city_three}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="注册 | 登录" prop="login_date" sortable="custom" width="140">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <span>{{scope.row.reg_date_n}}</span>
                            <span v-if="scope.row.login_date > 0">{{scope.row.login_date_n}}</span>
                            <span v-else style="color: red;">从未登录</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="职位数" width="120">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <span>总数：<el-link type="primary" @click="tocomjob({uid:scope.row.uid})">{{scope.row.jobnum}}</el-link></span>
                            <span>在招：<el-link type="primary" @click="tocomjob({uid:scope.row.uid,status:2})">{{scope.row.zz_jobnum}}</el-link></span>
                            <el-button @click="showaddjob(scope.row.uid)" style="padding: 0;" type="text">添加</el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="客户|跟进时间" prop="f_time" sortable="custom" min-width="160">
                    <template slot-scope="scope">
                        <div>{{scope.row.crm_type?scope.row.crm_type:'--'}}&nbsp;/&nbsp;{{scope.row.crm_status?scope.row.crm_status:'--'}}</div>
                        <!-- <div>客户标签：{{scope.row.crm_status?scope.row.crm_status:'--'}}</div> -->
                        <div class="moduleProps">
                            <el-popover placement="right" trigger="hover" :disabled="typeof scope.row.c_desc=='undefined' || scope.row.c_desc==''">
                                <div style='overflow: hidden;'>
                                    <div style='font-size:16px;font-weight: bold;color:#333;line-height:40px;background:#f9f9f9;padding-left:10px;'>
                                        最近跟进记录
                                    </div>
                                    <div>
                                        <div style='overflow: hidden;padding-left:10px;line-height:32px;font-size:14px;display: flex;align-items: center; margin:5px 0;'>
                                            <p style='color:#666;'>{{scope.row.c_name}}</p>
                                            <el-tag type="success" size="mini" style="margin-left: 10px;">{{scope.row.c_type}}</el-tag>
                                        </div>
                                        <div style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
                                            跟进时间：{{scope.row.c_time}}
                                        </div>
                                        <div style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;' v-html="'跟进内容：' + scope.row.c_desc"></div>
                                        <div v-if="scope.row.crm_pic_arr" style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
                                            <img v-for="(pic,key) in scope.row.crm_pic_arr" :key="key" :src="pic" width='40' height='40'/>
                                        </div>
                                    </div>
                                </div>
                                <span slot="reference">{{ scope.row.lastTime }}&nbsp;/&nbsp;{{ scope.row.nextTime }}</span>
                            </el-popover>
                            <!-- <span>下次：{{ scope.row.nextTime }}</span> -->
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="crm_time" label="领取时间" sortable="custom"  width="130">
                    <template slot-scope="scope">
                        <div>{{scope.row.crm_name}}</div>
                        <div>{{scope.row.crm_time_n}}</div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="80">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="openComInfo(scope)">管理</el-button><!--管理弹窗-->
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulecz">
                <el-checkbox v-model="allchecked" @change="allCheckChange">全选</el-checkbox>
                <el-button size="mini" @click="clueMyCustomer" v-if="kh=='10'">转企业库</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="currentPage" :page-size.sync="limit" :page-sizes="page_sizes" :total="total" layout="total, sizes, prev, pager, next, jumper"></el-pagination>
            </div>
        </div>
        <!--弹窗：录客户-->
        <div class="modluDrawer">
            <el-drawer title="录入客户" :visible.sync="addcustomer_show" :modal-append-to-body="false" append-to-body :show-close="true" :with-header="true" size="60%">
                <addcustomer @close-update="closeAddcustomer" v-if="addcustomer_show"></addcustomer>
            </el-drawer>
        </div>
        <!--弹窗：客户管理-->
        <div class="modluDrawer">
            <el-drawer title="客户管理" :visible.sync="comInfoDrawer" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
                <div class="modluDrawerKehuguanli">
                    <com_info ref="comInfo" :cuid="comuid" v-if="comInfoDrawer"></com_info>
                </div>
            </el-drawer>
        </div>
        <!--弹窗：职位管理-->
        <div class="modluDrawer">
            <el-drawer title="职位管理" :visible.sync="drawerAllJob" append-to-body size="95%">
                <div>
                    <joball ref="joball" style="margin-left:10px;" :uid="all_job_uid" v-if="drawerAllJob"></joball>
                </div>
            </el-drawer>
        </div>
        <!--弹窗：添加职位-->
        <div class="modluDrawer">
            <el-drawer title="新增职位" :visible.sync="drawerAddJob" append-to-body size="60%">
                <addjob ref="jobadd" style="margin-right:10px;" :comid="curr_comid"></addjob>
            </el-drawer>
        </div>
    </div>
</template>

<script>
    module.exports = {
        props: {
            page_type: String,
            simple: { // 简化页面，为true没有头部和tab切换，搜索栏仅保留公司名称搜索
                type: Boolean,
                default: false
            },
            other_params: {
                type: Object,
                default: function(){
                    return {}
                }
            },
        },
        watch: {
            page_type: {
                handler(val) {
                    this.pageType = val;
                    if (this.pageType == 'all'){
                        this.customerTitle = '客户总数';
                    } else if (this.pageType == 'org'){
                        this.customerTitle = '下属客户';
                    }
                },
                deep: true,
                immediate: true
            },
            other_params: {
                handler(val) {
                    this.crmparams = val
                    this.parseCrm()
                },
                deep: true,
                immediate: true
            },
            tableData: {
                handler(){// 防止表格样式错位
                    if (this.$refs.table) {
                        this.$nextTick(() => {
                            this.$refs.table.doLayout()
                        })
                    }
                },
                deep: true
            }
        },
        data: function () {
            return {
                drawerAllJob: false,
                all_job_uid: '',
                all_job_status: '',

                drawerAddJob: false,
                curr_comid: '',

                emptytext: '暂无数据',
                config:{},
                pageType: 'org',    //  all - 所有客户；org - 下属客户
                customerTitle: '',

                kh: '-1',

                adminUserList: [],
                client_type: [],
                client_status: [],
                ratingArr: [],
                vipETimeOpt: [{value: 1, label: '已到期'},{value: 2, label: '三天之内'},{value: 3, label: '七天之内'},{value: 4, label: '十五天内'},{value: 5, label: '一个月内'}],
                rzStatusArr: [{value: 1, label: '认证通过'},{value: 2, label: '等待审核'},{value: 3, label: '认证失败'},{value: 4, label: '尚未认证'}],
                hasJobOptions: [{value: 1, label: '是'}, {value: 2, label: '否'}],
                lfTimeOptions: [{value: 1, label: '今日跟进'}, {value: 2, label: '昨日跟进'}, {value: 3, label: '三天之前'}, {value: 4, label: '一周之前'}, {value: 5, label: '一月之前'}, {value: 5, label: '三个月前'}],
                nfTimeOptions: [{value: 1, label: '今日跟进'}, {value: 2, label: '明日跟进'}, {value: 3, label: '三天之内'}, {value: 4, label: '一周之内'}, {value: 5, label: '一月之内'}, {value: 5, label: '三个月内'}],

                numArr: [],

                crm_uid: '',
                crmType: '',
                crmStatus: '',
                crm_time: '',

                rating: '',
                vipEtime: '',

                yyzz_status: '',

                lfTime: '',
                nfTime: '',

                hasJob: '',
                fact_status:'',
                loginDate: [],
                regDate: [],
                timeOptions: {
                    shortcuts: [{
                        text: '昨天',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24);
                            end.setTime(end.getTime() - 3600 * 1000 * 24);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '今天',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '本周',
                        onClick(picker) {
                            const start = new Date(new Date().setHours(0, 0, 0) - (new Date().getDay() - 1) * 24 * 60 * 60 * 1000);
                            const end = new Date();
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '上周',
                        onClick(picker) {
                            const start = new Date(new Date().setHours(0, 0, 0) - (new Date().getDay() + 6) * 24 * 60 * 60 * 1000);
                            const end = new Date(new Date().setHours(0, 0, 0) + (0 - new Date().getDay()) *24 * 60 * 60 *1000);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '本月',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date(new Date(new Date().getFullYear(), new Date().getMonth(), 1).setHours(0, 0, 0));
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '上月',
                        onClick(picker) {
                            const end = new Date(new Date(new Date().getFullYear(), new Date().getMonth(), 0).setHours(23, 59, 59, 59));
                            const start = new Date(new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).setHours(0, 0, 0));
                            picker.$emit('pick', [start, end]);
                        }
                    }]
                },

                keyword: '',
                ktype: '1',

                r_status: '',

                seachbutn: true,
                tableHig: false,
                tableData: [],
                total: 0,
                limit: 0,
                prevPage: 0,
                currentPage: 1,
                page_sizes: [],

                list_loading: false,

                allchecked: false,
                choosedata: [],
                islook: false,

                addcustomer_show: false,
                isst: '',
                cuid: '',

                comInfoDrawer: false,
                comuid: '',

                crmparams: null,
                regtime: [],
                isfirst: true,

                // 列表排序
                sort_t: '',
                order: '',
            }
        },
        components: {
            'addcustomer': httpVueLoader('./addcustomer.vue'),
            'com_info': httpVueLoader('./comInfo.vue'),
            'comcert':httpVueLoader('./comcert.vue'),
            'joball':httpVueLoader('../../user/company/component/joball.vue'),
            'addjob':httpVueLoader('../../user/company/component/addjob.vue'),
        },
        mounted() {
            let params = window.parent.homeapp.$route.params;
            if (params.suspend == '1') {    //  暂停会员
                this.r_status = 4;
            }
            if (params.reg == 'today') {    //  今日新增
                const date = new Date();
                this.regDate = [date.getTime(), date.getTime()];
            }
            if (params.vipetime) {          //  到期未续费
                this.vipEtime = params.vipetime;
            }
            if (params.loginDate) {         //  30天未登录
                const date = new Date();
                this.loginDate = [0, date.getTime() - 3600 * 1000 * 24 * 30];
            }
            if (params.kh) {
                this.kh = params.kh;
            }
            if (params.crm_uid) {
                this.crm_uid = params.crm_uid;
            }

            if (this.isfirst) {
                this.getStaticData();
                this.getList();
                this.isfirst = false
            }
        },
        methods: {
            showaddjob: function(uid){
                var that = this
                that.curr_comid = uid
                that.drawerAddJob = true
                that.$nextTick(function () {
                    that.$refs.jobadd.edit()
                })
            },
            parseCrm: function(){
                this.r_status = ''
                this.vipEtime = ''
                this.regDate = []
                this.loginDate = []
                this.kh = '-1'
                if (this.crmparams.suspend == '1') {    //  暂停会员
                    this.r_status = 4;
                }
                if (this.crmparams.reg == 'today') {    //  今日新增
                    const date = new Date();
                    this.regDate = [date.getTime(), date.getTime()];
                }
                if (this.crmparams.vipetime) {          //  到期未续费
                    this.vipEtime = this.crmparams.vipetime;
                }
                if (this.crmparams.loginDate) {         //  30天未登录
                    const date = new Date();
                    this.loginDate = [0, date.getTime() - 3600 * 1000 * 24 * 30];
                }
                if (this.crmparams.kh) {
                    this.kh = this.crmparams.kh;
                }
                if (this.crmparams.crm_uid) {
                    this.crm_uid = this.crmparams.crm_uid;
                }
                if (this.crmparams.crm_time) {
                    this.crm_time = this.crmparams.crm_time;
                }
                this.currentPage = 1
                if (!this.isfirst) {
                    this.getList();
                }
            },
            async getList() {
                let that = this;
                let params = {
                    pageType: that.pageType,
                    kh: that.kh,
                    crm_uid: that.crm_uid,
                    crm_time: that.crm_time,
                    crmType: that.crmType,
                    crmStatus: that.crmStatus,
                    rating: that.rating,
                    vipEtime: that.vipEtime,
                    yyzz_status: that.yyzz_status,
                    hasJob: that.hasJob,
                    fact_status:that.fact_status,
                    lfTime: that.lfTime,
                    nfTime: that.nfTime,
                    loginDate: that.loginDate,
                    regDate: that.regDate,
                    r_status: that.r_status,
                    ktype: that.ktype,
                    keyword: that.keyword,
                    page: that.currentPage,
                    limit: that.limit,
                    t: that.sort_t,
                    order: that.order
                }
                if (this.crmparams.type) {
                    params.jttype = this.crmparams.type
                    params.source = 'dataBoard'
                }
                this.list_loading = true;
                that.emptytext = "数据加载中";

                httpPost('m=crm&c=crm_org_kh', params).then((result) => {
                    this.list_loading = false;
                    var res = result.data;
                    if (res.error == 0) {

                        that.tableData = res.data.list;
                        that.total = parseInt(res.data.total)
                        that.page_sizes = res.data.page_sizes;
                        that.limit = res.data.page_size;
                        if(that.prevPage != that.currentPage){
                            that.prevPage = that.currentPage;
                            that.$refs.table.bodyWrapper.scrollTop = 0;
                        }
                        if (that.tableData.length === 0){
                            that.emptytext = "暂无数据";
                        }
                    }
                    this.islook = true;
                }).catch(function (e) {
                    console.log(e)
                })
            },
            getStaticData(){
                var that = this
                let params = {
                    pageType: that.pageType,
                    crm_uid: that.crm_uid,
                };
                httpPost('m=crm&c=crm_org_kh&a=getIndexStaticData',params,{hideloading:true}).then((result) => {
                    var res = result.data;
                    if (res.error == 0) {
                        that.config = res.data.config;
                        that.adminUserList = res.data.adminUserList;
                        that.numArr = res.data.numarr;
                        that.client_type = res.data.client_type;
                        that.client_status = res.data.client_status;
                        that.ratingArr = res.data.ratingArr;
                    }
                });
            },
            handleClick(tab, event) {

                this.kh = tab._props.name;
                this.getList();
            },
            reset: function () {
                this.resetSearch();
                this.search();
            },
            resetSearch: function () {
                this.kh = '';

                this.crm_uid = '';

                this.crmType = '';
                this.crmStatus = '';

                this.rating = '';
                this.vipEtime = '';

                this.yyzz_status = '';
                this.hasJob = '';
                this.fact_status='';

                this.lfTime = '';
                this.nfTime = '';

                this.loginDate = [];
                this.regDate = [];

                this.keyword = '';
                this.ktype = '1';
                this.r_status = '';
                this.currentPage = 1;
            },
            statusChange: function (r_status) {
                this.resetSearch();
                this.r_status = r_status;
                this.getList();
            },
            search: function () {
                this.currentPage = 1;
                this.getList();
            },
            sortChange: function (e) {
                this.sort_t = e.prop;
                if (e.prop == 'ctime_n') {
                    this.sort_t = 'ctime';
                }
                this.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.search();
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.getList()
            },
            handleSizeChange(val) {
                this.currentPage = 1
                this.limit = val
                this.getList()
            },
            allCheckChange: function () {

                this.$refs.table.toggleAllSelection();
            },
            selectionChange: function (e) {
                if (this.tableData.length != e.length) {
                    this.allchecked = false;
                } else {
                    this.allchecked = true;
                }
                this.choosedata = e;
            },
            addcustomer: function () {
                this.addcustomer_show = true;
            },
            closeAddcustomer: function () {
                this.addcustomer_show = false;
                this.search();
            },
            clueMyCustomer: function () {
                var _this = this;
                var idarr = [];
                if (this.choosedata.length > 0) {
                    for (let i in this.choosedata) {
                        idarr.push(this.choosedata[i].uid);
                    }
                } else {
                    message.error('请选择要转换的线索！');
                    return;
                }
                var params = {
                    uids: idarr,
                    from: '2'
                };

                delConfirm(_this, params, this.cluePost, '确认转企业库？')
            },
            async cluePost(params) {

                let that = this;

                httpPost('m=tool&c=crm_my_customer&a=clueCustomer', params).then(function (result) {

                    var res = result.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            that.getList();
                        });
                        return;
                    } else {
                        message.error(res.msg);
                        return;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            tocomjob: function (params) {
                let _this = this
                _this.all_job_uid = params.uid;
                if (params.status){
                    _this.all_job_status = params.status;
                }
                _this.drawerAllJob = true
            },
            openComInfo: function (scope) {
                this.comInfoDrawer = true;
                this.comuid = scope.row.uid
            },
            closeComcert:function(){
                this.search();
            }
        },
    };
</script>
<style scoped>
    .moduleElMoreF{height:calc(100% - 138px)}
    .moduleElMoreT{height:calc(100% - 138px)}
    .el-button--primary.is-plain{background:#f4f4f5;border:none}
    .el-button--primary.is-plain:hover{color:#409eff}
    .moduleElTaCaoz{justify-content:space-between}
    .customGuanli .el-button{margin:3px 0}
    .customGuanli .el-tabs__content{height:calc(100% - 54px - 12px);padding:6px 0}
    .customFirCoInpt .el-button--primary.is-plain{background:#ecf5ff;border:1px solid #b3d8ff}
    .customFirLog .el-tabs__content{overflow:hidden;position:relative;width:100%;padding:10px 0;height:calc(100% - 70px)}

    .moduleElTableSimple.moduleElMoreF{height:calc(100% - 96px)}
    .moduleElTableSimple.moduleElMoreT{height:calc(100% - 140px)}
</style>