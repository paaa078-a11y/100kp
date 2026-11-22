<template>
    <div class="moduleElenAl">
        <div class="moduleElTable" style=" margin-top:0px; height: calc(100% - 62px);">
            <div class="admin_datatip"><i class="el-icon-document"></i> 有效视频次数：{{spLogAllNum}} 通话时长：{{spLogAlltime}} 平均单场时长：{{spLogAvgNum}}</div>
            <el-table :data="tableData" :default-sort="{prop: 'date', order: 'descending'}" stripe border
                      style="width: 100%;" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="calc(100% - 42px)"
                      @selection-change="handleSelectionChange" ref="multipleTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"> </el-table-column>
                <el-table-column prop="id" label="ID" width="90">
                </el-table-column>
                <el-table-column label="房间号" prop="roomid" width="90">
                </el-table-column>
                <el-table-column label="企业" property="comname" min-width="160" show-overflow-tooltip>
                </el-table-column>
                <el-table-column label="求职者" property="runame">
                </el-table-column>
                <el-table-column label="通话时长" prop="sptime_n">
                </el-table-column>
                <el-table-column label="开始时间" prop="ctime_n">
                </el-table-column>
                <el-table-column label="面试岗位" prop="jobname" min-width="160" show-overflow-tooltip>
                </el-table-column>
                <el-table-column label="来源渠道" prop="ly">
                    <template slot-scope="scope">
                        <span v-if="scope.row.sid > 0">企业面试间</span>
                        <span v-else-if="scope.row.zid > 0">网络招聘会</span>
                        <span v-else>在线聊天</span>
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
                <el-button @click="delAllBottom" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="currentPage" :page-sizes="pageSizes" :page-size="perPage"
                               layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
    </div>
</template>
<script>
    module.exports = {
        data: function () {
            return {
                emptytext: '暂无数据',
                loading: false,
                zid: 0,
                spLogAllNum: 0,
                spLogAlltime: 0,
                spLogAvgNum: 0,
                checkedAll: false,
                selectedItem: [],
                tableData: [],
                currentPage: 1,
                perPage: 0,
                pageSizes: [],
                total: 0,
                prevPage:0
            }
        },
        mounted() {
            this.getNumber();
        },
        methods: {
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
            async getNumber(){
                let that = this;
                let params = {};
                if (that.zid > 0) {
                    params.id = that.zid
                }
                httpPost('m=neirong&c=splog&a=getNumber', params, {hideloading: true}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        if (res.data.num_arr.spLogAllNum) {
                            that.spLogAllNum = res.data.num_arr.spLogAllNum
                        }
                        if (res.data.num_arr.spLogAlltime) {
                            that.spLogAlltime = res.data.num_arr.spLogAlltime
                        }
                        if (res.data.num_arr.spLogAvgNum) {
                            that.spLogAvgNum = res.data.num_arr.spLogAvgNum
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            async getList() {
                let that = this;
                let params = {
                    page: that.currentPage,
                    pageSize: that.perPage
                }
                if (that.zid > 0) {
                    params.id = that.zid
                }
                if (that.type) {
                    params.type = that.type
                }
                if (that.status) {
                    params.status = that.status
                }
                if (that.keyword) {
                    params.keyword = that.keyword
                }
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=neirong&c=splog&a=index', params, {hideloading: true}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.tableData = res.data.list
                        that.perPage = parseInt(res.data.perPage)
                        that.pageSizes = res.data.pageSizes
                        that.total = parseInt(res.data.total)
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
            delrow(id) {
                delConfirm(this, id, this.delete);
            },
            delAllBottom() {
                if (!this.selectedItem.length) {
                    message.error('请选择要删除的数据项');
                    return false;
                }
                delConfirm(this, this.selectedItem, this.delete);
            },
            async delete(id) {
                let that = this;
                let params = {
                    del: id
                };
                httpPost('m=neirong&c=splog&a=delSplog', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success(response.data.msg, function(){
                            that.getList();
                        });
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