<template>
    <div class="moduleElenAl">
        <div class="moduleSeachs">
            <div class="moduleElTabInpt">
                <div class="moduleInptList" v-if="tab != 1">
                    <el-select size="small" v-model="search.crm_uid" placeholder="业务员" clearable @change="handleSearch">
                        <el-option v-for="item in crmUserOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select size="small" v-model="search.reason" placeholder="外出原因" clearable @change="handleSearch">
                        <el-option v-for="item in reasonOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select size="small" v-model="search.status" placeholder="审批状态" clearable @change="handleSearch">
                        <el-option v-for="item in statusOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-date-picker size="small" style="width: 230px;" v-model="search.time" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="申请开始" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="handleSearch"></el-date-picker>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="handleSearch">查询</el-button>
                </div>
            </div>
            <div class="moduleSeachButn" v-if="tab == 1">
                <el-button type="primary" icon="el-icon-document-add" size="mini" @click="applyOut">外出申请</el-button>
            </div>
        </div>
        <div style="overflow: hidden;position: relative; width: calc(100% - 24px); padding: 0 12px; background: #fff;">
            <div class="admin_datatip">
                <i class="el-icon-document"></i>
                总数: {{allNum}}
                <span class="admin_datatip_n">待审批：{{status1Num}}</span>
                <span class="admin_datatip_n">已同意：{{status2Num}}</span>
                <span class="admin_datatip_n">已拒绝：{{status3Num}}</span>
                <span class="admin_datatip_n">搜索结果：{{total}}</span>
            </div>
        </div>

        <div class="moduleElTable" style="margin-top: 0px;">
            <el-table :data="tableData" border style="width: 100%" stripe :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" @selection-change="handleSelectionChange" :default-sort="{prop: 'status', order: 'ascending'}" @sort-change='sortChange' ref="outLogTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="name" label="申请人员" width="150"></el-table-column>
                <el-table-column prop="reason_n" label="外出原因" width="150"></el-table-column>
                <el-table-column label="客户信息" width="240">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <el-link target="_blank" :href="props.row.com_url" type="primary" :underline="false" v-if="props.row.comname">{{ props.row.comname }}</el-link>
                            <span>联系人：{{ props.row.linkman }}</span>
                            <span>联系电话：{{ props.row.linktel }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="stime_n" label="外出时间" width="160" sortable="custom" :sort-orders="['descending','ascending']"></el-table-column>
                <el-table-column prop="etime_n" label="返回时间" width="160" sortable="custom" :sort-orders="['descending','ascending']"></el-table-column>
                <el-table-column prop="remark" label="备注" min-width="180"></el-table-column>
                <el-table-column prop="ctime_n" label="申请时间" width="140" sortable="custom" :sort-orders="['descending','ascending']"></el-table-column>
                <el-table-column prop="status" label="状态" sortable="custom" :sort-orders="['descending','ascending']">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.status == 1">{{scope.row.status_n}}</el-tag>
                        <el-tag v-else-if="scope.row.status == 2" type="success">{{scope.row.status_n}}</el-tag>
                        <template v-else-if="scope.row.status == 3">
                            <el-tooltip class="item" effect="dark" :content="scope.row.statusbody" placement="right">
                                <el-tag type="danger">{{scope.row.status_n}} <i class="el-icon-warning"></i></el-tag>
                            </el-tooltip>
                        </template>
                    </template>
                </el-table-column>

                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="agreeOut(scope)" v-if="tab != 1 && scope.row.status == 1">同意</el-button>
                            <el-button size="mini" @click="showRefuseBox(scope)" v-if="tab != 1 && scope.row.status == 1">拒绝</el-button>
                            <el-button size="mini" @click="editApply(scope)" v-if="tab==1 && scope.row.status == 1">修改</el-button>
                            <el-button size="mini" @click="delOutLog(scope)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div style="margin-left: 10px;">
                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange"> 全选</el-checkbox>
                <el-button size="mini" style="margin-left: 20px;" @click="delOutLogSel">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size.sync="pageSize" :page-sizes="pageSizes" layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="拒绝外出申请" :visible.sync="refuseBoxShow" :with-header="true" :modal-append-to-body="false" :show-close="true" width="30%" center>
                <span style="margin-bottom: 10px;">请说明拒绝的原因：</span>
                <el-input type="textarea" :rows="4" placeholder="请输入内容" v-model="refuseTxt" style="margin-top: 16px;"></el-input>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="refuseBoxShow = false">取 消</el-button>
                    <el-button type="primary" @click="refuseOut">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <div class="modluDrawer">
            <el-dialog title="外出申请" :visible.sync="applyBoxShow" :with-header="true" :modal-append-to-body="false" :show-close="true" :close-on-click-modal="false" width="400px">
                <div class="drawerModInfo">
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>客户名称：</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-input v-if="outInfo.id" :value="outInfo.cname" disabled></el-input>
                            <el-select v-else v-model="outInfo.uid" filterable placeholder="请选择" clearable>
                                <el-option v-for="item in myCustomer" :key="item.value" :label="item.label" :value="item.value"></el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>外出原因：</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-select v-model="outInfo.reason"  placeholder="请选择外出原因">
                                <el-option v-for="item in reasonOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>外出时间：</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-date-picker style="width: 100%;" v-model="outInfo.stime" type="datetime" placeholder="选择外出时间" value-format="timestamp"></el-date-picker>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>返回时间：</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-date-picker v-model="outInfo.etime" style="width: 100%;" type="datetime" placeholder="选择返回时间" value-format="timestamp"></el-date-picker>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>外出备注：</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-input v-model="outInfo.remark" type="textarea" :rows="3" placeholder="请输入备注信息"></el-input>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="applyBoxShow = false">取 消</el-button>
                    <el-button type="primary" :loading="apply_load" @click="submitApply">确 定</el-button>
                </span>
            </el-dialog>
        </div>
		<!--弹窗：客户管理-->
		<div class="modluDrawer">
			<el-drawer title="客户管理" :visible.sync="comInfoDrawer" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="85%">
                <div class="modluDrawerKehuguanli">
				<com_info ref="comInfo" v-if="comInfoDrawer"></com_info>
                </div>
			</el-drawer>
		</div>
    </div>
</template>

<script>
    module.exports = {
        props: {
            log_tab: Number,
            ywy:{
                type: Boolean,
                default: false
            }
        },
        watch: {
            log_tab: {
                handler(val) {
                    this.tab = val;
                },
                deep: true,
                immediate: true
            }
        },
        data: function () {
            return {
                emptytext: '暂无数据',
                loading: false,
                tab: 0,
                search: {
                    crm_uid: '',
                    reason: '',
                    status: '',
                    time: ''
                },
                crmUserOptions: [],
                reasonOptions: [],
                statusOptions: [{value: '1', label: '待审批'}, {value: '2', label: '已同意'}, {value: '3', label: '已拒绝'}],
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

                tableData: [],
                total: 0,

                allNum: 0,
                status1Num: 0,
                status2Num: 0,
                status3Num: 0,
                prevPage: 0,
                currentPage: 1,
                pageSize: 0,
                pageSizes: [],

                // 批量选择
                checkAll: false,
                isIndeterminate: false,
                selectedItem: [],

                refuseBoxShow: false,
                outID: 0,
                refuseTxt: '',

                applyBoxShow: false,
                myCustomer:[],
                state: '',
                timeout: null,
                outInfo: {
                    id: '',
                    uid: '',
                    reason: '',
                    stime: '',
                    etime: '',
                    remark: ''
                },

				comInfoDrawer: false,

				apply_load:false,
            }
        },
		components: {
			'com_info': httpVueLoader('./comInfo.vue'),
		},
        mounted() {
			this.getStaticData();
            this.getOutLog();
        },
        methods: {
            getOutLog() {
                var that = this;
                var params = JSON.parse(JSON.stringify(this.search));
                params.pageSize = that.pageSize;
                params.page = that.currentPage;
                params.tab = that.tab;
                that.loading = true;
                that.emptytext = "数据加载中";
                let url = ''
                if (this.ywy == true) {
                    url = 'm=crm&c=outLogYwy&a=getOutLog'
                } else {
                    url = 'm=crm&c=outLog&a=getOutLog'
                }
                httpPost(url, params).then(function (res) {
                    let data = res.data.data;
                    that.tableData = data.list;
                    that.total = data.total;

                    that.pageSize = parseInt(data.pageSize);
                    that.pageSizes = data.pageSizes;
                    if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        that.$refs.outLogTable.bodyWrapper.scrollTop = 0;
                    }
                    that.loading = false;
                    if (that.tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
			getStaticData:function(){
				var that = this
				let params = {};
				params.tab = that.tab;
				let url = ''
                if (this.ywy == true) {
                    url = 'm=crm&c=outLogYwy&a=getOutLogStaticData'
                } else {
                    url = 'm=crm&c=outLog&a=getOutLogStaticData'
                }
				httpPost(url,params,{hideloading:true}).then((result) => {
					var res = result.data;
					if (res.error == 0) {
						that.allNum = res.data.allNum;
						that.status1Num = res.data.status1Num;
						that.status2Num = res.data.status2Num;
						that.status3Num = res.data.status3Num;
						that.crmUserOptions = [];
						res.data.crmUser.forEach(function (item) {
						    that.crmUserOptions.push({value: item.uid, label: item.name})
						});
						that.reasonOptions = [];
						res.data.reasonArr.forEach(function (item) {
						    that.reasonOptions.push({value: item.id, label: item.name})
						});
						that.myCustomer = [];
						res.data.customerList.forEach(function (item) {
						    that.myCustomer.push({value: item.uid, label: item.name})
						});
					}
				});
			},
            sortChange: function (e) {
                this.search.t = e.prop;
                if (e.prop == 'stime_n') {
                    this.search.t = 'stime';
                }
                if (e.prop == 'etime_n') {
                    this.search.t = 'etime';
                }
                if (e.prop == 'ctime_n') {
                    this.search.t = 'ctime';
                }
                this.search.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.handleSearch();
            },
            handleSearch: function () {
                this.currentPage = 1
                this.getOutLog()
            },
            handleSelectionChange(val) {
                this.selectedItem = val;
                if (this.selectedItem.length == 0) {
                    this.isIndeterminate = false;
                    this.checkAll = false;
                } else {
                    if (this.selectedItem.length == this.tableData.length) {
                        this.isIndeterminate = false;
                        this.checkAll = true;
                    } else {
                        this.isIndeterminate = true;
                        this.checkAll = false;
                    }
                }
            },
            handleCheckAllChange(val) {
                val ? this.$refs.outLogTable.toggleAllSelection() : this.$refs.outLogTable.clearSelection();
            },
            agreeOut(scope) {
                let _this = this;
                _this.$confirm('是否同意业务员 “' + scope.row.name + '” 的外出申请？', '温馨提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    modal: true,
                    type: 'info'
                }).then(() => {
                    httpPost('m=crm&c=outLog&a=setStatus', {id: scope.row.id, status: 2}).then(function (response) {
                        let res = response.data;
                        if (res.error == 0) {
                            message.success(res.msg, function () {
                                _this.getOutLog();
                            });
                        } else {
                            message.error(res.msg);
                        }
                    }).catch(function (error) {
                        console.log(error);
                    })
                }).catch(() => {
                });
            },
            showRefuseBox(scope){
                let _this = this;
                _this.outId = scope.row.id;
                _this.refuseBoxShow = true;
            },
            refuseOut: function(){
                let _this = this;
                httpPost('m=crm&c=outLog&a=setStatus', {id: _this.outId, status: 3, statusbody: _this.refuseTxt}).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            _this.refuseBoxShow = false;
                            _this.getOutLog();
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            delOutLog(scope, isMore) {
                var that = this;
                let name = '';
                let idArr = [], nameArr = [];
                let params = {};
                if (isMore) {
                    this.selectedItem.forEach((item) => {

                        idArr.push(item.id);
                        if (nameArr.indexOf(item.name) == -1) {
                            nameArr.push(item.name);
                        }
                    });
                    name = '（' + nameArr.join('，') + '）';
                    params.id = idArr;
                } else {
                    name = '（' + scope.row.name + '）';
                    params.id = scope.row.id;
                }
                if (that.tab == 1){

                    delConfirm(this, params, this.delete, '您确定要删除外出申请记录吗？');
                }else{

                    delConfirm(this, params, this.delete, '您确定要删除'+name+'外出申请记录吗？');
                }
            },
            delOutLogSel: function () {
                var that = this;
                if (!that.selectedItem.length) {
                    message.error('请选择要删除的外出申请记录！');
                    return;
                }
                this.delOutLog(null, true);
            },
            delete(params) {
                var self = this;
                let url = ''
                if (this.ywy == true) {
                    url = 'm=crm&c=outLogYwy&a=delOutLog'
                } else {
                    url = 'm=crm&c=outLog&a=delOutLog'
                }
                httpPost(url, params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            self.getOutLog();
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            handleSizeChange(val) {
                this.pageSize = val;
                this.getOutLog();
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.getOutLog();
            },
            applyOut: function () {
                let _this = this;
                _this.outInfo = {
                    id: '',
                    uid: '',
                    reason: '',
                    stime: '',
                    etime: '',
                    remark: ''
                };
                _this.applyBoxShow = true;
            },
            editApply: function(scope){
                let _this = this;
                _this.outInfo = {
                    id: scope.row.id,
                    uid: scope.row.uid,
                    cname: scope.row.comname,
                    reason: scope.row.reason,
                    stime: scope.row.stime * 1000,
                    etime: scope.row.etime * 1000,
                    remark: scope.row.remark
                }
                _this.applyBoxShow = true;
            },
            submitApply: function () {
                var that = this;

                if (that.outInfo.uid == ''){
                     return message.error('客户信息不存在！');
                }
                if (that.outInfo.reason == ''){
                    return message.error('请选择外出原因！');
                }
                if (that.outInfo.stime == ''){
                    return message.error('请选择外出时间！');
                }else{
                    let now = Date.parse(new Date());
                    if(that.outInfo.stime < now){

                        return message.error('外出时间必须大于现在时间');
                    }
                }

				that.apply_load = true;
                httpPost('m=crm&c=outLogYwy&a=applyOut', that.outInfo).then(function (response) {
					that.apply_load = false;
                    let res = response.data;
					if (res.error == 0) {
                        message.success(res.msg, function () {
                            that.applyBoxShow = false;
                            that.getOutLog();
                        });
                    } else {
                        message.error(res.msg);
                    }
                });
            },
        }
    };
</script>
<style scoped>
    .moduleElTable {
        height: calc(100% - (60px + 50px + 12px + 12px + 30px));
    }

    .moduleInptList .el-select .el-input {
        width: 140px;
    }
</style>