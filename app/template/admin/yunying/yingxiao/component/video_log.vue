<template>
    <div class="moduleElHight" style="padding: 0 20px;">
        <div class="moduleElTable">
            <el-table :data="list" border style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
                      :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="编号" width="80">
                </el-table-column>
                <el-table-column prop="time_n" label="播放时间" width="160">
                </el-table-column>
                <el-table-column prop="ip" label="播放IP" width="160">
                </el-table-column>
                <el-table-column prop="port_n" label="播放端口" width="160">
                </el-table-column>
                <el-table-column label="播放人信息" width="">
                    <template slot-scope="scope">
                        <div>
                            <span v-if="scope.row.uid > 0">{{scope.row.u_type}}</span>
                            {{scope.row.name}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="140" align="center">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button type="danger" size="mini" @click="del(scope.$index)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate" @change="checkAll">全选
                </el-checkbox>
                <el-button @click="batch('del')" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="page" :page-sizes="pageSizes" :page-size="limit"
                               layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
    </div>
</template>

<script>
    module.exports = {
        props: ['id'],
        data: function () {
            return {
                loading: false,
                dataText: '数据加载中',
                // 列表
                page: 1,
                limit: 0,
                list: [],
                total: 0,
                pageSizes: [],

                checkedAll: false, // 全选
                checkedAllIndeterminate: false,
                multipleSelection: [], // 多选值存储
                idArr: [],
                prevPage:0
            }
        },
        created: function () {
            this.getList();
        },
        methods: {
            handleSizeChange(val) {
                this.limit = val;
                this.getList();
            },
            handleCurrentChange(val) {
                this.page = val;
                this.getList();
            },
            search() {
                this.page = 1;
                this.getList();
            },
            getList() {
                let that = this,
                    params = {
                        page: that.page,
                        limit: that.limit,
                        id: that.id
                    };
                that.loading = true;

                httpPost('m=yunying&c=yingxiao_video&a=log', params).then(function (response) {
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
                    if(that.prevPage != that.page){
                        that.prevPage = that.page;
                        that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                    }
                    that.loading = false;
                    if (that.list.length === 0) {
                        that.dataText = "暂无数据";
                    }
					
					that.$nextTick(()=>{
						that.$refs.multipleTable.doLayout();
					})
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
                    idArr.push(item.id);
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
                    params.id = that.list[idx].id;
                    msg = '你确定要删除当前项吗？';
                }

                delConfirm(this, params, function (params) {
                    httpPost('m=yunying&c=yingxiao_video&a=delLog', params).then(function (res) {
                        if (res.data.error > 0) {
                            message.error(res.data.msg);
                        } else {
                            message.success(res.data.msg, function () {
                                that.$refs.multipleTable.clearSelection();
                                that.getList();
                            });
                        }
                    })
                }, msg)
            },
        },
        watch: {
            id: function (val, oldVal) {
                this.getList();
            },
			
        }
    };
</script>
<style scoped></style>