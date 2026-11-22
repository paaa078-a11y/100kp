<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" @keyup.enter.native="search" placeholder="输入你要搜索的关键字" v-model="searchForm.keyword"
                              clearable class="input-with-select">
                    </el-input>
                </div>
                <div v-for="(searchItem, searchIndex) in searchList" class="moduleInptList" :key="searchIndex">
                    <el-select size="small" v-model="searchForm[searchItem.param]" slot="prepend" :clearable="true" :placeholder="searchItem.name" @change="search">
                        <el-option v-for="(searchLabel, searchValue) in searchItem.value" :key="searchValue" :label="searchLabel" :value="searchValue"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="admin_datatip">
            <i class="el-icon-document"></i>
            数据统计：共 <span @click="init">{{certAllNum}}</span> 条
            <span class="admin_datatip_n">未审核：<span @click="statusSearch('3')">{{certStatusNum1}}</span> 条 </span>
            <span class="admin_datatip_n">未通过：<span @click="statusSearch('2')">{{certStatusNum2}}</span> 条</span>
            <span class="admin_datatip_n">搜索结果： {{total}} 条</span>
        </div>
        <div class="moduleElTable moduleElMoreInt" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="list" stripe style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
                      @sort-change="sortChange"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="uid" label="用户ID" width="120" sortable="custom">
                </el-table-column>
                <el-table-column prop="name" label="认证机构" min-width="150">
                </el-table-column>
                <el-table-column label="查看" align="center">
                    <template slot-scope="scope">
                        <div>
                            <el-link :underline="false" icon="el-icon-picture" @click="openAudit(scope.row)"></el-link>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="ctime" label="申请时间" align="center" sortable="custom" width="200">
                    <template slot-scope="scope">
                        <div>{{scope.row.ctime_n}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="审核状态" align="center">
                    <template slot-scope="scope">
                        <div class="admin_state">
                            <span v-if="scope.row.status == 1" class="admin_state1">已审核</span>
                            <span v-else-if="scope.row.status == 2" class="admin_state2">未通过</span>
                            <span v-else class="admin_state5">未审核</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="openAudit(scope.row)">审核</el-button>
                            <el-button size="mini" @click="del(scope.$index)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate"
                             @change="checkAll">全选</el-checkbox>
                <el-button @click="batch('del')" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="page" :page-sizes="pageSizes" :page-size="limit"
                               layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <!-- 弹窗 -->
        <div class="modluDrawer">
            <el-dialog title="认证审核" :visible.sync="dialogAudit" :with-header="true" :append-to-body="true"
                :show-close="true" width="500px">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>认证图片：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-image v-if="detail.check" :src="detail.check" style="width: 150px; height: 80px"
                                      :preview-src-list="[detail.check]">
                            </el-image>
                            <span v-else>暂无营业执照</span>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核操作：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio v-model="ruleFormAudit.status" label="1">正常</el-radio>
                            <el-radio v-model="ruleFormAudit.status" label="2">未通过</el-radio>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" :rows="2" placeholder="" v-model="ruleFormAudit.statusbody">
                            </el-input>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogAudit = false">取 消</el-button>
                    <el-button type="primary" @click="submitAudit" :disabled="saveLoading">确 定</el-button>
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
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',
            // 搜索筛选项
            searchList: [],
            searchForm: {
                status: this.status,
            },

            // 列表
            page: 1,
            limit: 0,
            list: [],
            total: 0,
            pageSizes: [],

            // 列表排序
            t: '',
            order: '',

            checkedAll: false, // 全选
            checkedAllIndeterminate: false,
            multipleSelection: [], // 多选值存储
            idArr: [],

            detail: {},

            saveLoading: false,

            // 数据统计
            certAllNum: 0,
            certStatusNum1: 0,
            certStatusNum2: 0,

            // 审核
            dialogAudit: false,
            ruleFormAudit: {},

            prevPage: 0
        }
    },
    mounted() {
        var that = this
        setTimeout(function () {
            that.getSearchFun();
        }, 200)
    },
    created() {
        this.init();
    },
    methods: {
        init() {
            // this.resetSearch();
            this.getCountData();
            this.search();
        },
        getSearchFun:function(){
            let that = this;
            httpPost('m=user&c=peixun_renzheng&a=getSearchData', {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    that.searchList = res.data.search_list;
                }
            })
        },
        resetSearch() {
            this.searchForm = {};
            this.limit = 0;
        },

        statusSearch(status) {
            this.searchForm.status = status;
            this.search();
        },

        getCountData() {
            let that = this;

            httpPost('m=user&c=peixun_renzheng&a=pxcertNum', {}, {hideloading: true}).then(function (response) {
                let res = response.data;

                that.certAllNum = res.certAllNum ? res.certAllNum : 0;
                that.certStatusNum1 = res.certStatusNum1 ? res.certStatusNum1 : 0;
                that.certStatusNum2 = res.certStatusNum2 ? res.certStatusNum2 : 0;
            })
        },

        handleSizeChange(val) {
            this.limit = val;
            this.getList();
        },
        handleCurrentChange(val) {
            this.page = val;
            this.getList();
        },
        sortChange(event) {
            this.t = event.order ? event.prop : '';
            this.order = event.order ? event.order == 'descending' ? 'desc' : 'asc' : '';
            this.search();
        },
        search() {
            this.page = 1;
            this.getList();
        },
        getList() {
            let that = this,
                searchForm = that.searchForm,
                params = {
                    page: that.page,
                    limit: that.limit,
                    t: that.t,
                    order: that.order,
                };
                that.loading = true;

            httpPost('m=user&c=peixun_renzheng', { ...params, ...searchForm }, {hideloading: true}).then(function (response) {
                let res = response.data,
                    data = res.data;

                that.list = data.list;
                that.total = parseInt(data.total);
                that.pageSizes = data.page_sizes;
                if (that.limit === 0) {
                    that.limit = parseInt(data.limit); // 取系统配置默认数量
                }
                if (that.page > data.page) {
                    that.page = parseInt(data.page); // 最后一页被删除后，取最新的页数
                }
                that.loading = false;
                if(that.prevPage != that.page){
                    that.prevPage = that.page;
                    that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                }
                if (that.list.length === 0) {
                    that.dataText = "暂无数据";
                }
            })
        },

        // 批量操作
        handleSelectionChange(val) {
            if (val.length == 0) {
                this.checkedAll = false;
                this.checkedAllIndeterminate = false;
            } else {
                if (val.length === this.list.length) {
                    this.checkedAll = true;
                    this.checkedAllIndeterminate = false;
                } else {
                    this.checkedAll = false;
                    this.checkedAllIndeterminate = true;
                }
            }
            this.multipleSelection = val;
        },
        batch(type) {
            if (this.multipleSelection.length == 0) {
                message.error('请选择要删除的数据');
                return false;
            }

            let idArr = [];
            this.multipleSelection.forEach(function (item) {
                idArr.push(item.uid);
            })
            this.idArr = idArr;

            if (type == 'del') {
                this.del();
            }
        },
        checkAll(val) {
            val ? this.checkedAllIndeterminate = false : '';
            this.$refs.multipleTable.toggleAllSelection();
        },

        del(idx) {
            let that = this,
                params = {},
                msg = '';

            if (typeof idx == 'undefined') { // 批量删除
                params.del = this.idArr;
                msg = '你确定要删除选中项吗？';
            } else {// 单个删除
                params.del = that.list[idx].uid;
                msg = '你确定要删除当前项吗？';
            }

            delConfirm(this, params, function (params) {
                httpPost('m=user&c=peixun_renzheng&a=del', params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        that.getCountData();
                        that.getList();
                        that.$refs.multipleTable.clearSelection();
                        message.success(res.data.msg);
                    }
                })
            }, msg)
        },

        openAudit(row) {
            this.detail = row;
            this.ruleFormAudit = {
                id: row.id,
                uid: row.uid,
                status: row.status,
                statusbody: row.statusbody
            };
            this.dialogAudit = true;
        },

        submitAudit() {
            let that = this,
                params = that.ruleFormAudit;

            if (typeof params.status == 'undefined' || params.status === '' || params.status === '0') {
                message.error('请选择审核状态');
                return false;
            }

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            httpPost('m=user&c=peixun_renzheng&a=status', params).then(function(res) {
                if (res.data.error > 0) {
                    that.saveLoading = false;
                    message.error(res.data.msg);
                } else {
                    that.dialogAudit = false;
                    that.getCountData();
                    that.getList();
                    message.success(res.data.msg, function () {
                        that.$refs.multipleTable.clearSelection();
                        that.saveLoading = false;
                    });
                }
            })
        },
    },
};
</script>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 47%;
    margin: 3px 0;
}
</style>