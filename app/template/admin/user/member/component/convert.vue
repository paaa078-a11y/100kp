<template>
    <div class="moduleElHight">
        <div class="moduleSeachbig">
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-input placeholder="请输入搜索的关键字" size="small" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select" clearable>
                    <el-select v-model="search.type" slot="prepend" placeholder="用户名">
                        <el-option label="用户名" value="1"></el-option>
                        <el-option label="用户ID" value="2"></el-option>
                    </el-select>
                </el-input>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="search.sqtype" size="small" slot="prepend" placeholder="申请方式" clearable @change="doUserQuery">
                    <el-option label="用户申请" value="1"></el-option>
                    <el-option label="管理员转换" value="2"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="search.status" size="small" slot="prepend" placeholder="审核状态" clearable @change="doUserQuery">
                    <el-option v-for="(item,key) in statusArr" :key="key" :label="item.label" :value="item.value"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="search.presusertype" size="small" slot="prepend" placeholder="当前会员" clearable @change="doUserQuery">
                    <el-option v-for="(item,key) in usertype" :key="key" :label="item.label" :value="item.value"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="search.applyusertype" size="small" slot="prepend" placeholder="申请会员" clearable @change="doUserQuery">
                    <el-option v-for="(item,key) in usertype" :key="key" :label="item.label" :value="item.value"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt">
                <el-date-picker size="small" style="width: 230px;" v-model="search.applytime" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="申请开始" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="doUserQuery"></el-date-picker>
            </div>
            <div class="tableSeachInpt" style="margin-left: 6px;">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
            </div>
        </div>
        <div class="admin_datatip">
            <i class="el-icon-document"></i> 数据统计：共 {{memNum.memAllNum}} 条
            <span class="admin_datatip_n cp_n" @click="lockList">待确认：{{memNum.memStatusNum3}} 条 </span>
            <span class="admin_datatip_n">搜索结果： {{total}} 条</span>
        </div>
        <div class="moduleElTable" :class="{ 'moduleElTableHig': tableHig }" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe @selection-change="selectChange" ref="multipleTable" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" @sort-change="shortChange" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="uid" label="用户ID" width="120" sortable="custom"></el-table-column>
                <el-table-column prop="name" label="用户名"></el-table-column>
                <el-table-column prop="moblie" label="手机号"></el-table-column>
                <el-table-column prop="usersf" label="申请方式" width="130">
                    <template slot-scope="props">
                        <div class="user_sf">
                            <span v-if="props.row.type==1" class="user_sf1">用户申请</span>
                            <span v-else class="user_sf2">管理员转换</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="pres_usertype" label="当前类型"></el-table-column>
                <el-table-column prop="apply_usertype" label="申请类型"></el-table-column>
                <el-table-column prop="apply_time_ymd" label="申请时间" width="160"></el-table-column>
                <el-table-column prop="zt" label="状态">
                    <template slot-scope="props">
                        <div class="admin_state">
                            <span v-if="props.row.status == '1'" class="admin_state1">已同意</span>
                            <span v-else-if="props.row.status == '2'" class="admin_state2">已拒绝</span>
                            <span v-else class="admin_state3">待确认</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="140" fixed="right">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="small " v-if="scope.row.status !='1'" plain @click="shenheFun(scope.row)">
                                审核
                            </el-button>
                            <el-button type="danger" size="mini" @click="del(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="batchDel" size="mini">批量删除</el-button>
                <el-button @click="batchshenheFun" size="mini">批量审核</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination :total="total" @current-change="userPageChange"
                               :page-sizes="pageSizes" @size-change="handleSizeChange"
                               :page-size="pageSize" :current-page.sync="page" layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
        <!--切换身份弹窗-->
        <div class="modluDrawer">
            <el-dialog title="申请切换身份操作" :visible.sync="userdfdrawer" :append-to-body="true" width="450px">
                <div>
                    <template v-if="shenheStatus">
                        <div class="wxsettip_small ">当前类型</div>
                        <el-input placeholder="企业用户" v-model="detail.pres_usertype" :disabled="true"></el-input>
                        <div class="wxsettip_small ">申请类型</div>
                        <el-input placeholder="123456" v-model="detail.apply_usertype" :disabled="true"></el-input>
                        <div class="wxsettip_small ">申请说明</div>
                        <el-input placeholder="请输入申请说明内容" type="textarea" :rows="2" v-model="detail.applybody" :disabled="true"></el-input>
                        <div class="wxsettip_small ">转换类型</div>
                    </template>
                    <template>
                        <el-radio-group v-model="state">
                            <el-radio label="1">同意转换</el-radio>
                            <el-radio label="2">拒绝转换</el-radio>
                        </el-radio-group>
                    </template>
                    <div class="wxsettip_small ">状态说明</div>
                    <el-input placeholder="请输入内容" type="textarea" :rows="2" v-model="textarea"></el-input>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="userdfdrawer = false">取 消</el-button>
                    <el-button type="primary" @click="handlUserType" :disabled="saveLoading">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>
<script>
module.exports = {
    props: {
        status: {type: String, default: ''}
    },
    data: function() {
        return {
            loading: false,
            dataText: '数据加载中',
            checkedAll: false,
            input: '',
            select: '',
            value: true,
            drawer: false,
            drawer2: false,
            userdfdrawer: false,
            seachbutn: false,
            tableHig: true,
            tableData: [],
            search: {
                status: this.status,
                applytime: [],
                sqtype: '',
                presusertype: '',
                applyusertype: '',
                keyword: '',
                type: '1'
            },
            statusArr: [{
                value: '3',
                label: "待确认"
            }, {
                value: '1',
                label: "已同意"
            }, {
                value: '2',
                label: "已拒绝"
            }],
            usertype: [{
                value: '1',
                label: "个人会员"
            }, {
                value: '2',
                label: "企业会员"
            }, {
                value: '3',
                label: "猎头会员"
            }, {
                value: '4',
                label: "培训会员"
            }],
            timeOptions: {
                shortcuts: [{
                    text: '昨天',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24);
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
                    text: '最近一周',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近一个月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近三个月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                        picker.$emit('pick', [start, end]);
                    }
                }]
            },
            idsArr: [],
            total: 0,
            page: 1,
            pageSize: 0,
            pageSizes:[],
            memNum: {},
            textarea: '',
            shenheStatus: false,
            uri: "m=user&c=",
            state: '1',
            detail: {},

            saveLoading: false,

            prevPage: 0
        }
    },
    created() {
        var that = this;
        let params = window.parent.homeapp.$route.params;
        let query = window.parent.homeapp.$route.query;
        
        if (!$.isEmptyObject(query.params)) {
            params = {...params,...query.params};
        }
        
        if (!$.isEmptyObject(params)) {
            delete params.activeName;
            this.getParams(params);
        }
        this.getList();
        this.shenheNumber();
    },
    methods: {
		getParams:function(params={},search=false){
			var that = this;
			for(let i in params){
				if(typeof that.search[i]!='undefined'){
					that.search[i] = params[i];
				}
			}
			if(search){
				this.getList();
			}
		},
        shortChange(e) {
            let orderMap = { ascending: 'asc', descending: 'desc' }
            this.search.t = e.prop;
            this.search.order = orderMap[e.order];
            this.search.page = 1;
            this.getList();
        },
        selectAllBottom(value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        lockList: function() {
            this.search.status = '2';
            this.page = 1
            this.getList()
        },
        selectChange: function(val) {
            this.idsArr = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.idsArr.push(item.id);
                });
            }
        },
        doUserQuery() {
            this.page = 1
            this.getList()
        },
        userPageChange(val) {
            this.page = val
            this.getList()
        },
        userPageSizeChange(val) {
            this.pageSize = val
            this.getList()
        },
        getList: function() {
            let _this = this;
            let url = _this.uri + 'admin_userchange&a=index';
            _this.search.page = this.page;
            _this.search.pageSize = this.pageSize;
            _this.loading = true;
            httpPost(url, _this.search, {hideloading: true}).then(function(response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.tableData = res.data.data;
                    _this.total = res.data.total;
                    _this.loading = false;
                    _this.pageSizes =res.data.pageSizes;
                    if(_this.prevPage != _this.page){
                        _this.prevPage = _this.page;
                        _this.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                    }
                    if (_this.tableData.length === 0) {
                        _this.dataText = "暂无数据";
                    }
                }
            })
        },
        shenheNumber: function() {
            let _this = this;
            let url = this.uri + 'admin_userchange&a=memNum'
            httpPost(url, {}, {hideloading: true}).then(function(response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.memNum = res.data;
                } else {
                    message.error(res.msg);
                }
            })
        },
        handleSizeChange(val) {
            this.pageSize = val;
            this.getList();
        },
        handleCurrentChange(val) {
            console.log(`当前页: ${val}`);
        },
        del: function(detail) {
            let _this = this,
                params = {};
            params.del = detail.id;
            let url = this.uri + 'admin_userchange&a=del';
            let msg = '确定要删除?';
            delConfirm(_this, params, function(params) {
                httpPost(url, params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        message.success(res.data.msg, function() {
                            _this.getList();
                        });
                    }
                })
            }, msg);
        },
        batchDel: function() {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择需要删除的用户!');
                return
            }
            let _this = this,
                params = {};
            params.del = ids;
            let url = this.uri + 'admin_userchange&a=del'
            let msg = '确定要删除?';
            delConfirm(_this, params, function(params) {
                httpPost(url, params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        message.success(res.data.msg, function() {
                            _this.getList();
                        });
                    }
                })
            }, msg);
        },
        batchshenheFun: function() {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择批量审核的数据!');
                return
            }
            this.userdfdrawer = true;
            this.shenheStatus = false;
            this.textarea = ''
            this.state = '1'
        },
        batchshenhe: function() {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择批量审核的数据!');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_userchange&a=status'
            let sendData = {
                id: this.idsArr,
                status: this.state,
                statusbody: this.textarea
            };
            httpPost(url, sendData).then(function(response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg, function() {
                        _this.userdfdrawer = false;
                        _this.getList();
                    });
                } else {
                    message.error(res.msg);
                }
            })
        },
        shenheFun: function(detail) {
            this.detail = detail;
            this.textarea = ''
            this.state = '1'
            this.userdfdrawer = true;
            this.shenheStatus = true;
        },
        handlUserType: function() {
            let url = this.uri + 'admin_userchange&a=status'

            let ids = this.idsArr;
            let sendData = {
                status: this.state,
                statusbody: this.textarea
            };
            if (!ids.length) {
                sendData.id = this.detail.id;
                sendData.uid = this.detail.uid;
            } else {
                sendData.id = this.idsArr;
            }
            let _this = this;
            _this.saveLoading = true;
            httpPost(url, sendData).then(function(response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.userdfdrawer = false;
                    message.success(res.msg, function() {
                        _this.getList();
                    });

                } else {
                    message.error(res.msg);
                }
            }).finally(function() {
                _this.saveLoading = false;
            });
        }
    },
};
</script>
<style scoped>
.moduleElHight .moduleElTable {
    padding: 0;
    margin: 0;
    height: calc(100% - 110px);
    width: 100%;
}

.moduleElTableHig {
    height: calc(100% - 135px) !important;
}

.tableSeachInptsmall .el-input {
    width: initial;
}

.tableSeachInptsmall .el-select {
    margin-right: 0px !important;
}

.el-input-group__prepend {
    background-color: #ffffff;
    padding: 0 0 0 20px;
}
</style>