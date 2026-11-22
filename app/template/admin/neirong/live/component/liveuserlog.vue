<template>
    <div class="moduleElHight" style="padding-left: 10px;">
        <div class="moduleElTable" :class="{ 'moduleElTableHig': tableHig }"
             style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe :empty-text="emptytext"
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" ref="multipleTable" v-loading="loading">
                <el-table-column prop="name" label="名称" width="180">
                </el-table-column>
                <el-table-column prop="ip" label="IP">
                </el-table-column>
                <el-table-column prop="ctime_n" label="进入时间">
                </el-table-column>
                <el-table-column prop="utype" label="类型">
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <!--<el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>-->
                <!--<el-button @click="delAllBottom">批量删除</el-button>-->
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
        props: {
            id: {
                type: String,
                default: ''
            },
        },
        data: function () {
            return {
                emptytext: '暂无数据',
                loading: false,
                xid: '',
                tableData: [],
                currentPage: 1,
                perPage: 0,
                pageSizes: [],
                total: 0,
                islook: false,
                tableHig: true,
                prevPage:0
            }
        },
        mounted() {

        },
        components: {
            // 'comadd': httpVueLoader('./livecomadd.vue'),
        },
        watch: {
            id: {
                handler(val, oldVal) {
                    this.xid = val;
                    if (val) {
                        this.getList()
                    }
                },
                immediate: true,
                deep: true,
            },
        },
        methods: {
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
                if (that.xid) {
                    params.xid = that.xid
                }
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=neirong&c=xjhlive&a=userlog', params).then(function (result) {
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
        },
    };
</script>
<style>  .el-dialog__body {
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