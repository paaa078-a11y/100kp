<template>
    <div class="moduleElHight" style="padding-left: 10px;">
        <div class="moduleSeachs" style="padding-bottom: 0px;">
            <div class="moduleElSearchInf">
                <div class="moduleElTabInpt" size="small" style="flex-wrap: wrap;">
                    <div class="moduleInptList">
                        <el-input placeholder="输入你要搜索的关键字" v-model="keyword" size="small" class="input-with-select" clearable>
                            <el-select v-model="type" slot="prepend" placeholder="请选择">
                                <el-option label="UID" value="1"></el-option>
                                <el-option label="用户名" value="2"></el-option>
                            </el-select>
                        </el-input>
                    </div>
                    <div class="tableSeachInpt">
                        <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="moduleElTable" :class="{ 'moduleElMoreSixd': tableHig }" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" @selection-change="handleSelectionChange" ref="multipleTable" :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='sortChange' v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" width="80" sortable="custom"></el-table-column>
                <el-table-column prop="fuid" label="UID" min-width="180"></el-table-column>
                <el-table-column prop="starttime" label="用户名" width="180">
                    <template slot-scope="scope">
                        <span>{{scope.row.user.username}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="endtime" label="名称" width="180">
                    <template slot-scope="scope">
                        <span>{{scope.row.user.nickname}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="content" label="内容" min-width="180" show-overflow-tooltip>
                </el-table-column>
                <el-table-column prop="ip" label="IP"></el-table-column>
                <el-table-column prop="sendTime_n" label="时间" width="150"></el-table-column>
                <el-table-column label="操作" fixed="right" width="150">
                    <template slot-scope="scope">
                        <div class="cz_button" style="margin-top: 10px;">
                            <el-button v-if="scope.row.black" size="mini" @click="delblack(scope.row.id)">解禁</el-button>
                            <el-button v-else size="mini" @click="forbidden(scope.row.id)">禁言</el-button>
                            <el-button type="danger" size="mini" @click="delrow(scope.row.id)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="delAllBottom" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="currentPage" :page-sizes="pageSizes" :page-size="perPage" layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
    </div>
</template>
<script>
module.exports = {
    props: {
        id: {
            type: String,
            default: ''
        },
    },
    data: function() {
        return {
            emptytext: '暂无数据',
            loading: false,
            xid: '',
            type: '1',
            keyword: '',
            checkedAll: false,
            selectedItem: [],
            tableData: [],
            currentPage: 1,
            perPage: 0,
            pageSizes: [],
            total: 0,
            islook: false,
            sort_type: '',
            sort_col: '',
            tableHig: true,
            prevPage:0
        }
    },
    mounted() {

    },
    components: {
        // 'xczphimg': httpVueLoader('./xczphimg.vue'),
    },
    watch: {
        id: {
            handler(val, oldVal) {
                this.xid = val;
                if (val) {
                    this.search()
                }
            },
            immediate: true,
            deep: true,
        },
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
        sortChange: function(column) {
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
        search() {
            this.currentPage = 1;
            this.getList();
        },
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                pageSize: that.perPage
            }
            if (that.keyword) {
                params.keyword = that.keyword
            }
            if (that.type) {
                params.type = that.type
            }
            if (that.xid) {
                params.xid = that.xid
            }
            if (that.sort_type && that.sort_col) {
                params.order = that.sort_type
                params.t = that.sort_col
            }
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=neirong&c=xjhlive&a=xjhchat', params).then(function(result) {
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
            }).catch(function(e) {
                console.log(e)
            })
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
            httpPost('m=neirong&c=xjhlive&a=msgdel', params).then(function(response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg,that.getList());

                } else {
                    message.error(response.data.msg);
                }
            }).catch(function(error) {
                console.log(error);
            })
        },
        //禁用
        async forbidden(id) {
            let that = this;
            let params = {
                del: id
            };
            httpPost('m=neirong&c=xjhlive&a=forbidden', params).then(function(response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg);
                    that.getList();
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function(error) {
                console.log(error);
            })
        },
        // 解禁
        delblack(id) {
            delConfirm(this, id, this.dodelblack, '确定要解禁？')
        },
        async dodelblack(id) {
            let that = this;
            let params = {
                del: id
            };
            httpPost('m=neirong&c=xjhlive&a=delblack', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg);
                    that.getList();
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        }
    },
};
</script>
<style>
.el-dialog__body {
    padding: 0px 20px;
}

.wxsettip_small {
    padding: 20px 0
}

.yd_ztkyd_active {
    background-color: #409eff;
    color: #fff;
}

.admin_web_tip {
    display: inline-block;
    line-height: 16px;
    padding-left: 20px;
    color: #999;
    font-size: 12px;
    padding-top: 8px;
}
</style>