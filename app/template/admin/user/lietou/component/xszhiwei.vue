<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input placeholder="请输入搜索内容" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select" size="small" clearable>
                        <el-select v-model="search.type" slot="prepend" placeholder="请选择">
                            <el-option label="推荐人" value="1"></el-option>
                            <el-option label="接收人" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.look" size="small" slot="prepend" placeholder="信息状态" clearable @change="doUserQuery">
                        <el-option v-for="(value, key) in looks" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.end" size="small" slot="prepend" placeholder="推荐时间" clearable @change="doUserQuery">
                        <el-option v-for="(value, key) in times" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.reply" size="small" slot="prepend" placeholder="回复时间" clearable @change="doUserQuery">
                        <el-option v-for="(value, key) in times" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable" :class="{ 'moduleElTableHig': tableHig }">
            <el-table :data="tableData" style="width: 100%" stripe @selection-change="selectChange"
                @sort-change="shortChange" ref="multipleTable"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"> </el-table-column>
                <el-table-column prop="id" label="编号" width="120" sortable="custom"> </el-table-column>
                <el-table-column prop="username" label="推荐人" width="150"> </el-table-column>
                <el-table-column prop="rname" label="接收人" min-width="100" show-overflow-tooltip> </el-table-column>
                <el-table-column prop="job_name" label="悬赏职位"> </el-table-column>
                <el-table-column prop="com_name" label="需求单位"></el-table-column>
                <el-table-column prop=" " label="悬赏金额" width="150">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            {{ scope.row.rebates }}{{ unit }}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="人才姓名" width="120" show-overflow-tooltip> </el-table-column>
                <el-table-column prop="datetime" label="推荐时间" sortable="custom" width="150"> </el-table-column>
                <el-table-column prop="reply_time" label="回复时间" sortable="custom" width="150"> </el-table-column>
                <el-table-column label="状态">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status == '1'" class="admin_state1">已查看</span>
                        <span v-else-if="scope.row.status == '2'" class="admin_state2">已试用</span>
                        <span v-else-if="scope.row.status == '0'" class="admin_state3">未查看</span>
                        <span v-else-if="scope.row.status == '3'" class="admin_state4">未通过</span>
                        <span v-else class="admin_state">已返利</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="140" fixed="right">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" plain @click="show(scope.row)">查看</el-button>
                            <el-button type="danger" size="mini " @click="del(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="isIndeterminate"
                    @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="batchDel" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination :total="total" @current-change="userPageChange" :page-size="pageSize"
                               :page-sizes="pageSizes"
                               @size-change="handleSizeChange"
                               :current-page.sync="page" layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-drawer title="猎头悬赏" :visible.sync="drawer4" :with-header="true" :modal-append-to-body="false" size="700px">
                <div class="lieTouxusas">
                    <div class="lieTouConts">
                        <div class="lieTouConTite">
                            <span>基本信息</span>
                        </div>
                        <div class="lieTouDatas">
                            <div class="lieTouDatList">
                                <span>姓名</span>
                                <div class="divplis">{{ resume.uname }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>性别</span>
                                <div class="divplis">{{ resume.sex }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>出生年月</span>
                                <div class="divplis">{{ resume.birthday }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>最高学历</span>
                                <div class="divplis">{{ resume.job_edu }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>工作经验</span>
                                <div class="divplis">{{ resume.job_exp }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>联系手机</span>
                                <div class="divplis">{{ resume.telphone }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>联系邮箱</span>
                                <div class="divplis">{{ resume.email }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="lieTouConts">
                        <div class="lieTouConTite">
                            <span>求职意向</span>
                        </div>
                        <div class="lieTouDatas">
                            <div class="lieTouDatList">
                                <span>从事行业</span>
                                <div class="divplis">{{ resume.job_hy }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>期望职位</span>
                                <div class="divplis">{{ resume.jobname }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>期望城市</span>
                                <div class="divplis">{{ resume.city }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>期望薪资</span>
                                <div class="divplis">{{resume.rsalary}}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>工作性质</span>
                                <div class="divplis">{{ resume.job_type }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>到岗时间</span>
                                <div class="divplis">{{ resume.job_report }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="lieTouConts">
                        <div class="lieTouConTite">
                            <span>推荐理由</span>
                        </div>
                        <div class="lieTouDatas">
                            <div class="lieTouDatList">
                                <span>推荐描述</span>
                                <div class="divplis">{{ rebate.content }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="lieTouConts" v-if="rebate.reply">
                        <div class="lieTouConTite">
                            <span>接收人回复:</span>
                        </div>
                        <div class="lieTouDatas">
                            <div class="lieTouDatList">
                                <span>回复内容：</span>
                                <div class="divplis">{{ rebate.reply }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </el-drawer>
        </div>

    </div>
</template>
    
<script>
module.exports = {
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',
            checkedAll: false,
            isIndeterminate: false,// checkbox 的不确定状态
            input: '',
            select: '',
            value: true,
            drawer: false,
            drawer4: false,
            seachbutn: false,
            tableHig: true,
            tableData: [],
            search: {
                type:'1'
            },
            times: {
                1: '今天',
                3: '最近三天',
                7: '最近七天',
                15: '最近半月',
                30: '最近一个月'
            },
            looks: {
                0: '未查看',
                1: '已查看',
                2: '已试用',
                3: '未通过',
                4: '已返利'
            },
            uri: "m=user&c=",
            total: 0,
            page: 1,
            idsArr: [],
            pageSize: 0,
            pageSizes:[],
            unit: '',
            // 详情
            rebate:{},
            resume:{},

            prevPage: 0
        }
    },
    created() {
        this.getList();
        this.returnConfig();
    },
    methods: {
        returnConfig: function () {
            let _this = this;
            let url = _this.uri + 'admin_lt_xuanshang&a=returnConfig';
            httpPost(url, {}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.unit = res.data.unit;
                }
            })
        },
        doUserQuery() {
            this.page = 1
            this.getList()
        },
        userPageChange(val) {
            this.page = val
            this.getList()
        },
        getList: function () {
            let _this = this;
            let url = _this.uri + 'admin_lt_xuanshang&a=index';
            _this.search.page = this.page;
            _this.search.pageSize = this.pageSize;
            _this.loading = true;
            httpPost(url, _this.search, {hideloading: true}).then(function (response) {
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
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择要删除的数据');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_lt_xuanshang&a=del'
            delConfirm(this, { del: ids }, function (params) {
                httpPost(url, params).then(function(response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, _this.getList());
                    } else {
                        message.error(res.msg);
                    }
                })
            }, '确定要删除？')
        },
        selectChange: function (val) {
            this.idsArr = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.idsArr.push(item.id);
                });
            }
            if (_this.idsArr.length == 0) {
                _this.checkedAll = false;
                this.isIndeterminate = false;
            } else {
                if (_this.idsArr.length == _this.tableData.length) {
                    _this.checkedAll = true;
                    this.isIndeterminate = false;
                } else {
                    _this.checkedAll = false;
                    this.isIndeterminate = true;
                }
            }
        },
        shortChange(e) {
            let orderMap = { ascending: 'asc', descending: 'desc' }
            this.search.t = e.order ? e.prop : null;
            this.search.order = orderMap[e.order];
            this.page = 1;
            this.getList();
        },
        selectAllBottom: function (value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        handleSizeChange(val) {
            this.pageSize = val;
            this.getList();
        },
        handleCurrentChange(val) {
            console.log(`当前页: ${val}`);
        },
        del: function (detail) {
            let _this = this;
            let url = this.uri + 'admin_lt_xuanshang&a=del';

            delConfirm(this, { del: detail.id }, function (params) {
                httpPost(url, params).then(function(response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, _this.getList());
                    } else {
                        message.error(res.msg);
                    }
                })
            }, '确定要删除？')
        },
        show:function(detail){
            let _this = this;
            let url = this.uri + 'admin_lt_xuanshang&a=show';
            httpPost(url, { id: detail.id }).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.resume = res.data.resume
                    _this.rebate = res.data.rebate
                    _this.drawer4 = true;
                } else {
                    message.error(res.msg);
                }
            })
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
    height: calc(100% - 90px) !important;
}
</style> 