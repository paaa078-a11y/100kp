<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
                <div class="moduleInptList moduleInptWidt">
                    <el-input placeholder="请输入搜索内容" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select" size="small"
                        clearable>
                        <el-select v-model="search.searchrname" slot="prepend" placeholder="请选择">
                            <el-option label="猎头中介用户名" value="1"></el-option>
                            <el-option label="姓名" value="2"></el-option>
                            <el-option label="意向职位" value="3"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable moduleElTablTwo">
            <el-table :data="tableData" border style="width: 100%" @selection-change="selectChange"
                @sort-change="shortChange" ref="multipleTable"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{ dataText }}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="uid" label="猎头编号" width="120" sortable="custom">
                </el-table-column>
                <el-table-column prop="user" label="拥有者" width="150">
                </el-table-column>
                <el-table-column prop="name" label="姓名" width="140">
                </el-table-column>
                <el-table-column prop="jobname" label="意向职位" width="130">
                </el-table-column>
                <el-table-column prop="edu_n" label="学历" width=" ">
                </el-table-column>
                <el-table-column prop="exp_n" label="工作经验" width="150">
                </el-table-column>
                <el-table-column label="工作地点">
                    <template slot-scope="props">
                        {{ props.row.city_one }}{{ props.row.city_two }}{{ props.row.city_three }}
                    </template>
                </el-table-column>
                <el-table-column prop="salary" label="待遇要求" width="150">
                </el-table-column>
                <el-table-column prop="linktel" label="联系手机" width="150">
                </el-table-column>
                <el-table-column prop=" " label="授权状态" width=" ">
                    <template slot-scope="props">
                        <div class="admin_state">
                            <span v-if="props.row.rewardstatus == '1'" class="admin_state1">推荐中</span>
                            <span v-else class="admin_state1">空闲</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="zt" label="状态">
                    <template slot-scope="props">
                        <div class="admin_state">
                            <span class="admin_state1" v-if="props.row.telstatus == '1'">已授权</span>
                            <span class="admin_state2" v-else>未授权</span>
                            <!--<span class="admin_state3">已锁定</span>
                            <span class="admin_state4">待审核</span>
                            <span class="admin_state5">已暂停</span> -->
                        </div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="160">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" @click="handlePreview(scope.row)">预览</el-button>
                            <el-button type="danger" size="mini" @click="del(scope.row)">删除</el-button>
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
                <el-pagination :total="total" @current-change="userPageChange" :page-size="pageSize" :page-sizes="pageSizes"
                    @size-change="handleSizeChange" :current-page.sync="page"
                    layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-drawer title="简历详情" :append-to-body="true" :visible.sync="resumePreviewVisible" :destroy-on-close="true"
                size="650px">
                <div class="lieTouxusas">
                    <div class="lieTouConts">
                        <div class="lieTouDatas">
                            <div class="lieTouDatList">
                                <span>姓名</span>
                                <div class="divplis">{{ resume.name }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>性别</span>
                                <div class="divplis">{{ resume.sex_n }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>年龄：</span>
                                <div class="divplis">{{ resume.age }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>现居住地：</span>
                                <div class="divplis">{{ resume.living }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>最高学历</span>
                                <div class="divplis">{{ resume.edu_n }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>工作经验</span>
                                <div class="divplis">{{ resume.exp_n }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>联系手机</span>
                                <div class="divplis">{{ resume.linktel }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>意向岗位</span>
                                <div class="divplis">{{ resume.jobname }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>从事行业：</span>
                                <div class="divplis">{{ resume.hy_n }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>期望薪资：</span>
                                <div class="divplis">{{ resume.salary }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>期望城市：</span>
                                <div class="divplis">{{ resume.city_one_n }}{{ resume.city_two_n }}{{ resume.city_three_n }}
                                </div>
                            </div>
                            <div class="lieTouDatList">
                                <span>求职状态：</span>
                                <div class="divplis">{{ resume.jobstatus_n }}</div>
                            </div>
                            <div class="lieTouDatList">
                                <span>工作经历</span>
                                <div class="divplis">
                                    <div v-html="resume.expinfo"></div>
                                </div>

                            </div>
                            <div class="lieTouDatList">
                                <span>教育经历：</span>
                                <div class="divplis">
                                    <div v-html="resume.eduinfo"></div>
                                </div>

                            </div>
                            <div class="lieTouDatList">
                                <span>项目经历：</span>
                                <div class="divplis">
                                    <div v-html="resume.projectinfo"></div>
                                </div>

                            </div>
                            <div class="lieTouDatList">
                                <span>技能特长：</span>
                                <div class="divplis">
                                    <div v-html="resume.skillinfo"></div>
                                </div>

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
            search: {
                searchrname: '1'
            },
            select: '',
            value: true,
            tableData: [],
            checkedAll: false,
            isIndeterminate: false,// checkbox 的不确定状态
            total: 0,
            page: 1,
            idsArr: [],
            pageSize: 0,
            pageSizes: [],
            uri: "m=user&c=",
            resumePreviewVisible: false,
            info: {},
            //
            detail: {},
            resume: {},

            prevPage: 0
        }
    },
    created() {
        this.getList();
    },
    methods: {
        selectAllBottom: function (value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
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
            let url = _this.uri + 'admin_lt_talent&a=index';
            _this.search.page = this.page;
            _this.search.pageSize = this.pageSize;
            _this.loading = true;
            httpPost(url, _this.search, {hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.tableData = res.data.data;
                    _this.total = res.data.total;
                    _this.loading = false;
                    _this.pageSizes = res.data.pageSizes;
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
        del: function (detail) {
            let _this = this;
            let url = this.uri + 'admin_lt_talent&a=del';
            delConfirm(this, { del: detail.id }, function (params) {
                httpPost(url, params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, _this.getList());
                    } else {
                        message.error(res.msg);
                    }
                })
            }, '确定要删除？')
        },
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择要删除的数据');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_lt_talent&a=del'
            delConfirm(this, { del: ids }, function (params) {
                httpPost(url, params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, _this.getList());
                    } else {
                        message.error(res.msg);
                    }
                })
            }, '确定要删除？')
        },
        handleSizeChange(val) {
            this.pageSize = val;
            this.getList();
        },
        handlePreview(scope) {
            let _this = this;
            let url = _this.uri + 'admin_lt_talent&a=show';
            let sendData = {
                id: scope.id,
                uid: scope.uid
            };
            httpPost(url, sendData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.resume = res.data.detail
                }
            })
            this.resumePreviewVisible = true;
        },
    },
};
</script>
<style scoped>
.moduleInptWidt .el-select .el-input {
    width: 150px;
}

</style>