<template>
    <!--面试评价-->
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt" style="flex-wrap: wrap;">
                <div class="moduleInptList">
                    <el-input placeholder="输入你要搜索的关键字" @keyup.enter.native="handleSearch" size="small" v-model="searchForm.keyword" class="input-with-select" clearable>
                        <el-select v-model="searchForm.type" slot="prepend" placeholder="请选择">
                            <el-option label="评论" value="1"></el-option>
                            <el-option label="公司名称" value="2"></el-option>
                            <el-option label="面试过程" value="3"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="moduleInptList">
                    <el-select v-model="searchForm.status" size="small" slot="prepend" placeholder="审核状态" clearable @change="handleSearch">
                        <el-option label="未审核" value="0"></el-option>
                        <el-option label="已审核" value="1"></el-option>
                        <el-option label="已拒绝" value="2"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select v-model="searchForm.end" size="small" slot="prepend" placeholder="发送时间" clearable @change="handleSearch">
                        <el-option label="今天" value="1"></el-option>
                        <el-option label="最近三天" value="3"></el-option>
                        <el-option label="最近七天" value="7"></el-option>
                        <el-option label="最近半月" value="15"></el-option>
                        <el-option label="最近一个月" value="30"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select v-model="searchForm.r_time" size="small" slot="prepend" placeholder="回复时间" clearable @change="handleSearch">
                        <el-option label="今天" value="1"></el-option>
                        <el-option label="最近三天" value="3"></el-option>
                        <el-option label="最近七天" value="7"></el-option>
                        <el-option label="最近半月" value="15"></el-option>
                        <el-option label="最近一个月" value="30"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="handleSearch">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable" :class="{ 'moduleElTableHig': tableHig }"
            style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                ref="multipleTable" @selection-change="handleSelectionChange" @sort-change="shortChange" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" sortable="custom" width="80"></el-table-column>
                <el-table-column prop="com_name" label="公司名称" width="200"></el-table-column>
                <el-table-column prop="username" label="评论人"></el-table-column>
                <el-table-column label="描述评分" width="200">
                    <template slot-scope="scope">
                        <el-rate v-model="scope.row.desscore" disabled show-score text-color="#ff9900" score-template="{value}"></el-rate>
                    </template>
                </el-table-column>
                <el-table-column label="面试官评分" width="200">
                    <template slot-scope="scope">
                        <el-rate v-model="scope.row.hrscore" disabled show-score text-color="#ff9900" score-template="{value}"></el-rate>
                    </template>
                </el-table-column>
                <el-table-column label="环境评分" width="200">
                    <template slot-scope="scope">
                        <el-rate v-model="scope.row.comscore" disabled show-score text-color="#ff9900" score-template="{value}"></el-rate>
                    </template>
                </el-table-column>
                <el-table-column prop="content" label="面试过程" min-width="240" show-overflow-tooltip></el-table-column>
                <el-table-column prop="ctime_n" label="评论时间" width="150"></el-table-column>
                <el-table-column label="企业回复" width="100">
                    <template slot-scope="scope">
                        <el-popover v-if="scope.row.reply_time_n" trigger="hover" placement="top">
                            <p>回复内容：{{ scope.row.reply }}</p>
                            <div>回复时间： {{ scope.row.reply_time_n }}</div>
                            <div slot="reference" class="name-wrapper">
                                <el-link>回复内容<i class="el-icon-view el-icon--right"></i></el-link>
                            </div>
                        </el-popover>
                    </template>
                </el-table-column>
                <el-table-column prop="zt" label="状态" fixed="right">
                    <template slot-scope="scope">
                        <div class="admin_state">
                            <span v-if="scope.row.status == '1'" class="admin_state1">已审核</span>
                            <span v-else-if="scope.row.status == '2'" class="admin_state2">未通过</span>
                            <span v-else class="admin_state4">未审核</span>
                            <!--<span class="admin_state1">已审核</span>-->
                            <!--<span class="admin_state2">未通过</span>-->
                            <!--<span class="admin_state3">已锁定</span>-->
                            <!--<span class="admin_state4">待审核</span>-->
                            <!--<span class="admin_state5">已暂停</span>-->
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" header-align="center" width="190" fixed="right">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" @click="handleStatus(scope)">审核</el-button>
                            <el-button size="mini" @click="handleShow(scope)">详情</el-button>
                            <el-button type="danger" size="mini" @click="deleteRow(scope)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox :indeterminate="isIndeterminate" v-model="checked" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="deleteRow(null, true)" size="mini">批量删除</el-button>
                <el-button @click="handleStatus(null, true)" size="mini">批量审核</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    :current-page.sync="searchForm.page" :page-size="searchForm.limit" :page-sizes="pageSizes"
                    layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <!--审核-->
        <div class="modluDrawer">
            <el-dialog :title="titleStatus" :visible.sync="statusVisible" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="450px">
                <div>
                    <div class="wxsettip_small">审核操作</div>
                    <el-radio v-model="ruleFormStatus.status" label="0">未审核</el-radio>
                    <el-radio v-model="ruleFormStatus.status" label="1">已审核</el-radio>
                    <el-radio v-model="ruleFormStatus.status" label="2">未通过</el-radio>
                    <div class="wxsettip_small ">审核说明</div>
                    <el-input type="textarea" :rows="2" placeholder="说明原因" v-model="ruleFormStatus.statusbody"></el-input>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="resetFormStatus('ruleFormStatus')">取 消</el-button>
                    <el-button type="primary" @click="submitFormStatus('ruleFormStatus')" :disabled="submitLoading">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--详情-->
        <div class="modluDrawer">
            <el-dialog title="面试评价" :visible.sync="showVisible" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="450px">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>面试过程：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" :rows="2" placeholder="" v-model="ruleformShow.content"></el-input>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>其他评论：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" :rows="2" placeholder="" v-model="ruleformShow.othercontent"></el-input>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>企业回复：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" :rows="2" placeholder="" v-model="ruleformShow.reply"></el-input>
                        </div>
                    </div>
                </div>
            </el-dialog>
        </div>
    </div>
</template>

<script>
module.exports = {
    props: {
        status: {type: String, default: ''}
    },
    data: function () {
        return {
			loading: false,
            dataText: '数据加载中',
            searchForm: {
                page: 1,
                limit: null,
                type: '1',
                keyword: null,
                status: this.status,
                end: null,
                r_status: null,
            },
            total: 0,
            tableData: [],
            pageSizes: [],
            tableHig: true,
            checked: false,//全选
            isIndeterminate: false,// checkbox 的不确定状态
            selectedItem: [],
            //审核
            statusVisible: false,
            ruleFormStatus: {
                cid: null,
                status: null,
                statusbody: '',
            },
            titleStatus: '面试评价审核',
            //详情
            showVisible: false,
            ruleformShow: {
                content: '',//面试过程
                othercontent: '',//其他评论
                reply: '',//企业回复
            },
            submitLoading: false,

            prevPage: 0
        }
    },
    created() {
        this.getList();
    },
    methods: {
        handleSelectionChange(val) {
            this.selectedItem = val;
            if (this.selectedItem.length == 0) {
                this.isIndeterminate = false;
                this.checked = false;
            } else {
                if (this.selectedItem.length == this.tableData.length) {
                    this.isIndeterminate = false;
                    this.checked = true;
                } else {
                    this.isIndeterminate = true;
                    this.checked = false;
                }
            }
        },
        selectAllBottom(value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        shortChange(e) {
            let orderMap = {ascending: 'asc', descending: 'desc'}
            this.searchForm.t = e.order ? e.prop : null;
            this.searchForm.order = orderMap[e.order];
            this.searchForm.page = 1;
            this.getList();
        },
        handleSizeChange(val) {
            this.searchForm.limit = val;
            this.getList();
        },
        handleCurrentChange(val) {
            this.searchForm.page = val;
            this.getList();
        },
        handleSearch() {
            this.searchForm.page = 1
            this.getList()
        },
        getList() {
            let _this = this;
            let params = JSON.parse(JSON.stringify(this.searchForm));
            for (let index in params) {
                (params[index] === '') && (params[index] = null);
            }
			_this.loading = true;
            httpPost('m=user&c=company_evaluate&a=index', params, {hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error === 0) {
                    _this.tableData = res.data.list;
                    _this.total = res.data.total;
                    _this.searchForm.limit = res.data.perPage;
                    _this.pageSizes = res.data.pageSizes;
					_this.loading = false;
                    if(_this.prevPage != _this.searchForm.page){
                        _this.prevPage = _this.searchForm.page;
                        _this.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                    }
                    if (_this.tableData.length === 0) {
                        _this.dataText = "暂无数据";
                    }
                }
            }).catch(function (error) {
                console.log(error);
            });
        },
        deleteRow(scope, isMore) {
            let params = {};
            if (isMore) {
                if (!this.selectedItem.length) {
                    message.error('请选择要删除的数据');
                    return false;
                }
                let list = [];
                for (let item of this.selectedItem) {
                    list.push(item.id);
                }
                params.del = list;
            } else {
                // let index = scope.$index;
                // this.tableData.splice(index, 1);
                params.id = scope.row.id;
            }

            delConfirm(this, params, this.delete);
        },
        delete(params) {
            let _this = this;
            httpPost('m=user&c=company_evaluate&a=del', params).then(function (response) {
                let res = response.data;
                if (res.error === 0) {
                    message.success('删除成功！');
                    _this.getList();
                } else {
                    message.error('删除失败！');
                }
            }).catch(function (error) {
                console.log(error);
            });
        },
        handleStatus(scope, isMore) {
            if (isMore) {
                if (!this.selectedItem.length) {
                    message.error('您还未选择任何信息！');
                    return false;
                }
                let list = [];
                for (let item of this.selectedItem) {
                    list.push(item.id);
                }
                this.ruleFormStatus.cid = list.join(',');
                this.titleStatus = '批量审核';
                this.statusVisible = true;
            } else {
                this.ruleFormStatus.cid = scope.row.id;
                this.titleStatus = '面试评价审核';
                let _this = this;
                let params = {cid: scope.row.id};
                httpPost('m=user&c=company_evaluate&a=lockinfo', params).then(function (response) {
                    let res = response.data;
                    if (res.error === 0) {
                        _this.ruleFormStatus.statusbody = res.data;
                    }
                    _this.statusVisible = true;
                }).catch(function (error) {
                    console.log(error);
                });
            }
        },
        submitFormStatus(formName) {
            // this.$refs[formName].validate((valid) => {if (valid) {}});
            let _this = this;
            let params = this.ruleFormStatus;
            if (params.status == null) {
                message.error('请选择审核状态');
                return false;
            }
            _this.submitLoading = true;
            httpPost('m=user&c=company_evaluate&a=status', params).then(function (response) {
                let res = response.data;
                if (res.error === 0) {
                    message.success(res.msg);
                    _this.resetFormStatus();
                    _this.getList();
                } else {
                    message.error(res.msg);
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                _this.submitLoading = false;
            });
        },
        resetFormStatus(formName) {
            //this.$refs[formName].resetFields();
            this.ruleFormStatus.cid = null;
            this.ruleFormStatus.status = null;
            this.ruleFormStatus.statusbody = '';
            this.statusVisible = false;
        },
        handleShow(scope) {
            let _this = this;
            let params = {id: scope.row.id};
            httpPost('m=user&c=company_evaluate&a=show', params).then(function (response) {
                let res = response.data;
                if (res.error === 0) {
                    _this.ruleformShow.content = res.data.content;
                    _this.ruleformShow.othercontent = res.data.othercontent;
                    _this.ruleformShow.reply = res.data.reply;
                    _this.showVisible = true;
                } else {
                    message.error('参数错误');
                }
            }).catch(function (error) {
                console.log(error);
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
    height: calc(100% - 90px) !important;
}
</style>