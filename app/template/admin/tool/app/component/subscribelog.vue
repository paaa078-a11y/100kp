<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach" style="margin-bottom: 0;">
            <div class="moduleElTabInpt">
                <div class="tableSeackellsd">
                    <el-input v-model="keyword" placeholder="请输入搜索内容" size="small" prefix-icon="el-icon-search" clearable>
                        <el-select v-model="search_type" size="small" slot="prepend" placeholder="个人姓名" width="200">
                            <el-option label="个人姓名" value="1"></el-option>
                            <el-option label="企业名称" value="2"></el-option>
                            <el-option label="猎头名称" value="3"></el-option>
                            <el-option label="培训名称" value="4"></el-option>
                            <el-option label="用户ID" value="90"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt tableSeachInptsmalltwo" style="margin-top:10px;">
                    <el-date-picker v-model="time" type="daterange" range-separator="至" start-placeholder="开始日期"
                                    end-placeholder="结束日期" value-format="yyyy-MM-dd" style="margin-right: 10px; text-align: left;" @change="search">
                    </el-date-picker>
                </div>
                <div class="tableSeachInpt" style="margin-bottom: 0px;;">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable moduleElTablTwo">
            <el-table :data="tableData" border style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" @selection-change="handleSelectionChange" ref="multipleTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="uid" label="用户UID" width="140">
                </el-table-column>
                <el-table-column label="接收人/类型" width="180">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <span>{{ props.row.username }}</span>
                            <span v-if="props.row.utype_n">[{{ props.row.utype_n }}]</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="mbconfig_n" label="模板类型" width="150">
                </el-table-column>
                <el-table-column label="模板内容" min-width="360">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top">
                            <p>模板内容</p>
                            <p v-html="scope.row.body_html"></p>
                            <div slot="reference" class="name-wrapper">
                                <el-link>查看内容<i class="el-icon-view el-icon--right"></i></el-link>
                            </div>
                        </el-popover>
                    </template>
                </el-table-column>
                <el-table-column prop="wxid" label="微信id" width="120">
                </el-table-column>
                <el-table-column prop="ctime_n" label="发送时间" width="170">
                </el-table-column>
                <el-table-column label="发送状态" width="120">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top" v-if="scope.row.status != 0">
                            <p>失败原因</p>
                            <p>{{ scope.row.status }}，{{ scope.row.msg }}</p>
                            <div slot="reference" class="name-wrapper">
                                <el-tag size="medium" type="danger">失败</el-tag>
                            </div>
                        </el-popover>
                        <el-tag v-else size="medium" type="success">成功</el-tag>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="80">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button type="danger" size="small" @click="delrow(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
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
</template>

<script>
module.exports = {
    data: function () {
        return {
            loading: false,
            emptytext: '暂无数据',
            search_type: '1',
            time: '',
            keyword: '',
            currentPage: 1,
			prevPage:0,
            perPage: 0,
            pageSizes: [],
            total: 0,
            checkedAll: false,
            selectedItem: [],
            tableData: [],
        }
    },
    mounted() {

    },
    methods: {
        search() {
            this.currentPage = 1;
            this.getList();
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
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                pageSize: that.perPage
            }
            if (that.time) {
                params.date = that.time[0] + '~' + that.time[1]
            }
            if (that.search_type) {
                params.type = that.search_type
            }
            if (that.keyword) {
                params.keyword = that.keyword
            }
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=tool&c=xcxset&a=tplmsglist', params, {hideloading: true}).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.tableData = res.data.list
                    that.perPage = parseInt(res.data.perPage)
                    that.pageSizes = res.data.pageSizes
                    that.total = parseInt(res.data.total)
                    that.loading = false;
					
					if(that.prevPage != that.currentPage){
						that.prevPage = that.currentPage;
						that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
					}
                    if (that.tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        delrow(row) {
            delConfirm(this, row.id, this.delete);
        },
        delAllBottom() {
            if (!this.selectedItem.length) {
                message.error('请选择要删除的数据')
                return false;
            }
            delConfirm(this, this.selectedItem, this.delete);
        },
        async delete(Ids) {
            let that = this;
            let params = {
                del: Ids
            };
            httpPost('m=tool&c=xcxset&a=delTplmsg', params).then(function (response) {
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
<style>
.setDomeInte .el-tabs__content{
    padding: 2px 0 12px 0;
}
</style>