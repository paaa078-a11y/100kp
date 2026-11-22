<!--权益提醒-->
<template>
    <div class="drawerModlue">
        <div class="moduleSeachs" v-if="source != 'comInfo'">
            <div class="moduleElTabInpt" style="width: 100%;">
                <div class="moduleInptList">
                    <el-date-picker v-model="daterange" :picker-options="pickerOptions" type="daterange" value-format="yyyy-MM-dd" align="right" unlink-panels range-separator="至" start-placeholder="发送开始日期" end-placeholder="发送结束日期" clearable></el-date-picker>
                </div>
                <div class="moduleInptList moduleInptWidt">
                    <el-input @keyup.enter.native="search" placeholder="搜索关键词" v-model="keyword" class="input-with-select" clearable>
                        <el-select v-model="crm_type" slot="prepend" placeholder="请选择">
                            <el-option label="公司名" value="1"></el-option>
                            <el-option label="UID" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" @click="search">查询</el-button>
                </div>
                <div class="admin_datatip">
                    <i class="el-icon-document"></i>
                    <span class="admin_datatip_n">总数：<el-link type="primary" :underline="false" @click="reset()">{{numarr.ajaxAll}}</el-link> </span>
                    <span class="admin_datatip_n">未跟进：<el-link type="primary" :underline="false" @click="statusChange(3)">{{numarr.ajaxStatus}}</el-link> </span>
                </div>
            </div>
        </div>
        <div :class="moduleElTable">
            <el-table ref="table" :data="tableData" v-loading="list_loading" border style="width: 100%" stripe :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" @selection-change="selectionChange" @sort-change='sortChange' :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column v-if="source != 'comInfo'" prop="uid" label="客户ID" width="100" sortable="custom"></el-table-column>
                <el-table-column v-if="source != 'comInfo'" label="客户名称" width="250">
                    <template slot-scope="scope">
                        <div>{{scope.row.username}}</div>
                        <el-button type="text" @click="openComInfo(scope)">{{scope.row.com_name}}</el-button><!--管理弹窗-->
                    </template>
                </el-table-column>
                <el-table-column prop="type_n" label="消息类型" min-width="100"></el-table-column>
                <el-table-column prop="send_time_n" label="发送时间" min-width="60" sortable="custom"></el-table-column>
                <el-table-column prop="type_n" label="跟进状态" min-width="50">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status=='1'" class="admin_state1">未跟进</span>
                        <span v-else class="admin_state2">已跟进</span>
                    </template>
                </el-table-column>
                <el-table-column label="上次跟进" width="200">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <el-popover placement="right" trigger="hover" :disabled="scope.row.c_desc=='' || typeof scope.row.c_desc=='undefined'">
                                <div style='overflow: hidden;'>
                                    <div style='font-size:16px;font-weight: bold;color:#333;line-height:40px;background:#f9f9f9;padding-left:10px;'>最近跟进记录</div>
                                    <div>
                                        <div style='overflow: hidden;padding-left:10px;line-height:32px;font-size:14px;display: flex;align-items: center; margin:5px 0;'>
                                            <p style='color:#666;'>{{scope.row.c_name}}</p>
                                            <span style='color:#666; background: rgba(120, 186, 50, 0.2); color:#78BA32;border-radius: 5px;display: block;margin-left:15px;padding:0 10px;'>{{scope.row.c_type}}</span>
                                        </div>
                                        <div style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>跟进时间：{{scope.row.follow_time}}</div>
                                        <div style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;' v-html="'跟进内容：' + scope.row.c_desc"></div>
                                    </div>
                                </div>
                                <span slot="reference">上次：{{ scope.row.follow_time }}</span>
                            </el-popover>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="150">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz" style="display: initial;">
                            <el-button type="primary" style="width: calc(100% - 13px);" size="small" @click="addfollow(scope.row)">写跟进</el-button>
                            <el-button size="small" @click="openComInfo(scope)">管理</el-button><!--管理弹窗-->
                            <el-button type="danger" size="small" @click="deleteinfo(scope.row.id)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div style="margin-left: 10px;">
                <el-checkbox v-model="allchecked" @change="allcheckChange">全选</el-checkbox>
                <el-button size="mini" @click="deleteAll">删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total" layout="total, sizes, prev, pager, next, jumper"></el-pagination>
            </div>
        </div>
        <!--弹窗：写跟进-->
        <div class="modluDrawer">
            <el-drawer title="联系跟进" :visible.sync="follow_show" :modal-append-to-body="false" append-to-body :wrapper-closable="false" size="60%">
                <crmnewfollow :uid_v="follow_uid" :crm_status_v="follow_status" :crm_type_v="follow_type" :name_v="follow_name" :calllog_id="follow_calllog_id" @close-update="closeAddfollow" v-if="follow_show"></crmnewfollow>
            </el-drawer>
        </div>
        <!--弹窗：客户管理-->
        <div class="modluDrawer">
            <el-drawer title="客户管理" :visible.sync="comInfoDrawer" :append-to-body="true" :modal-append-to-body="false" size="80%">
                <div class="modluDrawerKehuguanli">
                <com_info ref="comInfo" :cuid="cuid" v-if="comInfoDrawer"></com_info>
                </div>
            </el-drawer>
        </div>
    </div>
</template>

<script>
    module.exports = {
        props: {
            all: {
                type: Number,
                default: 0
            },
            crm_uid: {
                type: String,
                default: ''
            },
            crm_name:{
                type:String,
                default:''
            },
            crm_type:{
                type:String,
                default:'1'
            },
            source:{
                type:String,
                default:''
            }
        },
        watch: {
            crm_uid: function (val, oldVal) {
                this.reset();
            }
        },
        data: function () {
            return {
                emptytext: '暂无数据',
                numarr: {},
                tableData: [],
                total: 0,
                limit: 0,
                currentPage: 1,
                prevPage: 0,
                page_sizes: [],

                list_loading: false,

                allchecked: false,
                choosedata: [],

                keyword: '',
                daterange: [],
                sort_t: '',
                order: '',
                status: '',
                pickerOptions: {
                    disabledDate(time) {
                        if (time.getTime() > new Date().getTime()) {
                            return true;
                        }
                    }
                },

                follow_show: false,
                follow_uid: '',
                follow_status: '',
                follow_type: '',
                follow_name: '',
                follow_calllog_id: '',

                comInfoDrawer: false,
                cuid: '',
                moduleElTable:'moduleElTable'
            }
        },
        components: {
            'crmnewfollow': httpVueLoader('./crmnewfollow.vue'),
            'com_info': httpVueLoader('./comInfo.vue'),
        },

        mounted() {
            this.keyword = this.crm_name;
            if(this.source != 'comInfo'){
                this.getStaticData();
            }else {
                this.moduleElTable = 'moduleElTable1';
            }
            this.getList();
        },
        methods: {
            reset: function () {
                this.resetSearch();
                this.getList();
            },
            statusChange: function () {
                this.resetSearch();
                this.status = '1';
                this.getList();
            },
            resetSearch() {
                this.keyword = '';
                this.crm_type = '1';
                this.daterange = [];
                this.sort_t = '';
                this.order = '';
                this.status = '';
                this.currentPage = 1;
            },
            async getList() {
                let that = this;
                let params = {
                    page: that.currentPage,
                    limit: that.limit,
                    t: that.sort_t,
                    order: that.order,
                    keyword: that.keyword,
                    crm_type: that.crm_type,
                    status: that.status,
                }
                if (parseInt(that.all) == 1 || that.crm_uid == 'all') {
                    params.all = 1;
                }
                if (that.crm_uid != 'all' && parseInt(that.crm_uid) > 0){
                    params.crm_uid = parseInt(that.crm_uid);
                }

                if (this.daterange && this.daterange.length > 0) {

                    params['send_time_s'] = this.daterange[0];
                    params['send_time_e'] = this.daterange[1];
                }

                this.list_loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=crm&c=crm_my_customer&a=notice', params).then((result) => {
                    this.list_loading = false;
                    var res = result.data;

                    if (res.error == 0) {
                        that.tableData = res.data.list
                        that.total = parseInt(res.data.total)
                        that.page_sizes = res.data.page_sizes;
                        that.limit = res.data.page_size;
                        if(that.prevPage != that.currentPage){
                            that.prevPage = that.currentPage;
                            that.$refs.table.bodyWrapper.scrollTop = 0;
                        }
                        if (that.tableData.length === 0) {
                            that.emptytext = "暂无数据";
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            getStaticData: function () {
                var that = this
                let params = {};
                if (parseInt(that.all) == 1 || that.crm_uid == 'all') {
                    params.all = 1;
                }
                if (that.crm_uid != 'all' && parseInt(that.crm_uid) > 0){
                    params.crm_uid = parseInt(that.crm_uid);
                }
                httpPost('m=crm&c=crm_my_customer&a=getNoticeStaticData', params, {hideloading: true}).then((result) => {
                    var res = result.data;
                    if (res.error == 0) {
                        that.numarr = res.data.numarr;
                    }
                });
            },
            search: function () {
                this.currentPage = 1;
                this.getList();
            },
            sortChange: function (e) {
                this.sort_t = e.prop;
                if (e.prop == 'send_time_n') {
                    this.sort_t = 'send_time';
                }
                this.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.search();
            },
            changedate: function (e) {
                if (e !== null) {
                    this.time = '';
                }
            },
            dateSelectChange: function (e) {
                if (e != '') {
                    this.daterange = [];
                }
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
            allcheckChange: function () {

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
            deleteinfo: function (id) {
                var _this = this;
                var params = {
                    del: id
                };
                delConfirm(_this, params, this.deletePost)
            },
            deleteAll: function () {
                var _this = this;
                var idarr = [];
                if (this.choosedata.length > 0) {
                    for (let i in this.choosedata) {
                        idarr.push(this.choosedata[i].id);
                    }
                } else {
                    message.error('请选择要删除的数据！');
                    return;
                }
                var params = {
                    del: idarr
                };

                delConfirm(_this, params, this.deletePost)
            },
            async deletePost(params) {
                let that = this;
                httpPost('m=crm&c=crm_my_customer&a=delNotice', params).then(function (result) {
                    var res = result.data;
                    if (res.error == 9) {
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
            addfollow: function (row) {
                this.follow_uid = row.uid;
                this.follow_status = row.crm_status;
                this.follow_type = row.crm_type;
                this.follow_name = row.com_name;
                this.follow_show = true;
            },
            closeAddfollow: function () {
                this.follow_show = false;
                this.search();
            },
            openComInfo: function (scope) {
                this.comInfoDrawer = true;
                this.cuid = scope.row.uid
            }
        }
    };
</script>
<style scoped>
    .moduleInptList .el-select .el-input {
        width: 140px;
    }
    .moduleElTable1 {
        overflow: hidden;
        position: relative;
        width: calc(100% - 24px);
        height: calc(100% - ( 50px + 12px + 12px));
        margin-top: 12px;
        background: var(--bg-color2);
        padding: 12px 12px 0 12px;
    }
</style>