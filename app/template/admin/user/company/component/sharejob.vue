<template>
    <div class="moduleElHight">
        <div class="moduleSeachbig">
			<div class="tableSeachInpt tableSeachInptsmall tableSeacFromer" style="padding: 2px 0;">
			    <el-input v-model="search_params.keyword" @keyup.enter.native="search" placeholder="请输入搜索内容" size="small" clearable>
					<el-select v-model="search_params.type" size="small" slot="prepend" placeholder="用户名">
					    <el-option label="公司名称" value="1"></el-option>
					    <el-option label="职位名称" value="2"></el-option>
					</el-select>
			    </el-input>
			</div>
            <div class="tableSeachInpt">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
            </div>
        </div>
        <!--<div class="admin_datatip"><i class="el-icon-document"></i> 数据统计：共 400 条<span class="admin_datatip_n">未审核：32 条-->
			<!--</span><span class="admin_datatip_n">未通过：3 条</span> <span class="admin_datatip_n">已锁定：1 条</span> <span-->
                <!--class="admin_datatip_n">搜索结果： 400 条</span>-->
        <!--</div>-->
        <div class="moduleElTable"
             style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }"
                      ref="multipleTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column prop="jobid" label="职位编号" width="110"></el-table-column>
                <el-table-column prop="name" label="职位"></el-table-column>
                <el-table-column prop="com_name" label="企业"></el-table-column>
                <el-table-column prop="sharenum" label="推广量"></el-table-column>
                <el-table-column prop="sjdj" label="赏金单价">
                    <template slot-scope="scope">
                        ￥{{scope.row.packmoney}}
                    </template>
                </el-table-column>
                <el-table-column prop="sjze" label="赏金总额">
                    <template slot-scope="scope">
                        ￥{{scope.row.packprice}}
                    </template>
                </el-table-column>
                <el-table-column prop="sysj" label="剩余赏金">
                    <template slot-scope="scope">
                        ￥{{scope.row.nowprice}}
                    </template>
                </el-table-column>
                <el-table-column prop="lastupdate_n" label="更新时间"></el-table-column>
                <el-table-column label="操作" width="140" fixed="right">
                    <template slot-scope="scope">
                        <div class="cz_button">

                            <el-button type="danger  " size="mini"  @click="delrow(scope.row.jobid)">删除</el-button>

                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <!--<el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>-->
                <!--<el-button>批量删除</el-button>-->
                <!--<el-button>批量关闭</el-button>-->
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange"
                               @current-change="handleCurrentChange"
                               :current-page="currentPage" :page-sizes="pageSizes"
                               :page-size="perPage"
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
                search_params: {
                    type: '1',
                    keyword: '',
                },
                // checkedAll: false,
                // selectedItem: [],
                tableData: [],
                currentPage: 1,
                perPage: 0,
                pageSizes: [],
                total: 0,
                tableHig: true,
                drawertz: false,
                curr_data: null,
                islook: false,

                prevPage: 0
            }
        },
        mounted() {

        },
        created() {
            this.getList();
        },
        methods: {
            getParams: function (params = {}) {
                var that = this;
                for (let i in params) {
                    if (i != 'page' && typeof that.search_params[i] != 'undefined') {
                        that.search_params[i] = params[i];
                    }
                }
            },
            handleSizeChange(val) {
                this.perPage = val;
                scrollToTop()
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
            async getList() {
                let that = this;
                let params = {
                    page: that.currentPage,
                    pageSize: that.perPage
                }
                if (that.search_params.type) {
                    params.type = that.search_params.type
                }
                if (that.search_params.keyword) {
                    params.keyword = that.search_params.keyword
                }
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=user&c=jobpack&a=index', params, {hideloading: true}).then(function (result) {
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
                            scrollToTop()
                        }
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
            async delete(id) {
                let that = this;
                let params = {
                    delid: id
                };
                httpPost('m=user&c=jobpack&a=delshare', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success('操作成功', function(){
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

.tableSeacFromer{
    margin-right: 8px;
}
.tableSeacFromer .el-input-group__prepend{
    padding: 0;
    background: none;
}
.tableSeacFromer .el-select{
    margin-right: 0;
    width: 160px;
}
.tableSeacFromer .el-input{
    margin-right: 0;
}
</style>
