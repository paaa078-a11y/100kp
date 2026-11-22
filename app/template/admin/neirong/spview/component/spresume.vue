<template>
    <el-drawer title="预约简历" :visible.sync="showresume" :modal-append-to-body="false" append-to-body size="80%">
        <div class="ql_cont">
            <div class="moduleSeachs" style="padding: 12px 0; width: 100%;">
                <div class="moduleSeachleft">
                    <div class="tableSeackellsd" style="padding: 2px 0;">
                        <el-input v-model="keyword" placeholder="输入你要搜索的关键字" class="input-with-select" size="small"
                            prefix-icon="el-icon-search" clearable>
                            <el-select v-model="type" size="small" slot="prepend" placeholder="个人姓名">
                                <el-option label="个人姓名" value="1"></el-option>
                                <el-option label="职位名称" value="2"></el-option>
                            </el-select>
                        </el-input>
                    </div>
                    <div class="newsbtnbox">
                        <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                    </div>
                </div>
            </div>
            <div class="moduleElTable" style="padding: 0; width: 100%;">
                <el-table :data="tableData" :default-sort="{ prop: 'id', order: 'descending' }" stripe border
                          style="width: 100%;" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }"
                          height="100%" @sort-change="sortChange"
                          @selection-change="handleSelectionChange" ref="multipleTable" v-loading="loading" :empty-text="emptytext">
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column prop="id" label="编号" width="90">
                    </el-table-column>
                    <el-table-column label="个人信息" width="260">
                        <template slot-scope="scope">
                            <el-link :href="weburl + '/index.php?m=resume&c=show&uid=' + scope.row.uid" target="_blank">{{scope.row.name}}</el-link><br/>
                                <span style="display:inline;">{{scope.row.sex ? scope.row.sex : '保密'}}&nbsp;|&nbsp;</span>
                                <span style="display:inline;">{{scope.row.age}}岁&nbsp;|&nbsp;</span>
                                <span style="display:inline;">{{scope.row.exp}}经验&nbsp;|&nbsp;</span>
                                <span style="display:inline;">{{scope.row.edu}}</span>学历
                        </template>
                    </el-table-column>
                    <el-table-column label="预约职位" min-width="160" show-overflow-tooltip>
                        <template slot-scope="scope">
                            <el-link :href="weburl + '/index.php?m=job&c=comapply&id=' + scope.row.jobid" target="_blank">{{scope.row.jobname}}</el-link>
                            <div>预约时间：{{scope.row.ctime_n}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="jobname" label="面试状态" min-width="160" show-overflow-tooltip>
                        <template slot-scope="scope">
                            <span v-if="scope.row.status == '1'" style="color:#5FB878;">正在面试</span>
                            <span v-else-if="scope.row.status == '2'" style="color:green;">已面试</span>
                            <span v-else>未面试</span>
                            <div v-if="scope.row.rtime_n">{{scope.row.rtime_n}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column fixed="right" label="操作" width="80">
                        <template slot-scope="scope">
                            <div class="cz_button">
                                <el-button type="danger" size="mini" @click="delrow(scope.row.id)">删除</el-button>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="modulePaging">
                <div class="">
                    <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                    <el-button size="mini" @click="delAllBottom">批量删除</el-button>
                </div>
                <div class="modulePagNum">
                    <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                                   :current-page="currentPage" :page-sizes="pageSizes" :page-size="perPage"
                                   layout="total, sizes, prev, pager, next, jumper" :total="total">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-drawer>
</template>
<script>
    module.exports = {
        props: {},
        data: function () {
            return {
                emptytext: '暂无数据',
                loading: false,
                showresume: false,
                checkedAll: false,
                selectedItem: [],
                tableData: [],
                currentPage: 1,
                perPage: 0,
                pageSizes: [],
                total: 0,
                sid: '',
                type: '1',
                keyword: '',
                sort_type: '',
                sort_col: '',
                weburl: localStorage.getItem("sy_weburl"),
                prevPage:0,
            }
        },
        mounted() {
        },
        methods: {
            sortChange: function (column) {
                if (column.order == 'descending') {
                    this.sort_type = 'desc';
                } else if (column.order == 'ascending') {
                    this.sort_type = 'asc';
                } else {
                    this.sort_type = '';
                }
                this.sort_col = column.prop
                this.search();
            },
            async getList() {
                let that = this;
                var params = {
                    page: that.currentPage,
                    pageSize: that.perPage
                }
                if (!that.sid) {
                    message.error('sid不能为空');
                    return false
                } else {
                    params.sid = this.sid
                }
                if (that.type) {
                    params.type = that.type
                }
                if (that.keyword) {
                    params.keyword = that.keyword
                }
                if (that.sort_type && that.sort_col) {
                    params.order = that.sort_type
                    params.t = that.sort_col
                }
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=neirong&c=spview&a=spresume', params, {hideloading: true}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.perPage = parseInt(res.data.perPage)
                        that.pageSizes = res.data.pageSizes
                        that.total = parseInt(res.data.total)
                        that.tableData = res.data.list
                        that.weburl = res.data.weburl
                        that.showresume = true;

                        if(that.prevPage != that.currentPage){
                            that.prevPage = that.currentPage;
                            that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                        }
                        that.loading = false;
                        if (that.tableData.length === 0){
                            that.emptytext = "暂无数据";
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            handleSelectionChange(val) {
                this.selectedItem = [];
                let _this = this;
                if (val.length) {
                    val.forEach(item => {
                        _this.selectedItem.push(item.id);
                    });
                }
                if (_this.selectedItem.length == 0) {
                    _this.checkedAll = false;
                } else {
                    if (_this.selectedItem.length == _this.tableData.length) {
                        _this.checkedAll = true;
                    } else {
                        _this.checkedAll = false;
                    }
                }
            },
            selectAllBottom(value) {
                value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
            },
            handleSizeChange(val) {
                this.perPage = val;
                this.getList()
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.getList()
            },
            search() {
                this.currentPage = 1;
                this.getList();
            },
            delrow(id) {
                delConfirm(this, id, this.delete);
            },
            delAllBottom() {
                if (!this.selectedItem.length) {
                    message.error('请选择要删除的数据');
                    return false;
                }
                delConfirm(this, this.selectedItem, this.delete);
            },
            async delete(id) {
                let that = this;
                let params = {
                    del: id
                };
                httpPost('m=neirong&c=spview&a=delSub', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success('操作成功');
                        that.getList();
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
        },
    };
</script>
<style scoped>
    .ql_cont {
        padding: 0 20px;
    }
</style>