<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input placeholder="请输入搜索内容" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select" size="small" clearable>
                        <el-select v-model="search.type" slot="prepend" placeholder="请选择">
                            <el-option v-for="(value, key) in typeArr" :key="key" :label="value" :value="key"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt">
                    <el-date-picker v-model="value1" type="daterange" size="small" range-separator="至"
                                    start-placeholder="开始日期" value-format="yyyy-MM-dd" clearable
                                    end-placeholder="结束日期" @change="doUserQuery">
                    </el-date-picker>
                </div>
                <div class="tableSeachInpt" style="margin-left: 8px;">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable" :class="{ 'moduleElTableHig': tableHig }"
            style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe  @selection-change="selectChange"
                      @sort-change="shortChange"
                      ref="multipleTable"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"> </el-table-column>
                <el-table-column prop="id" label="用户ID" width="120" sortable="custom"> </el-table-column>
                <el-table-column prop="username" label="用户名" width="150"> </el-table-column>
                <el-table-column prop="content" label="内容" min-width="100" show-overflow-tooltip> </el-table-column>
                <el-table-column prop="ip" label="IP"> </el-table-column>
                <el-table-column prop="ctime_ymd" label="时间"> </el-table-column>
                <el-table-column label="操作" width="80" fixed="right">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button type="danger" size="small " @click="del(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" @change="selectAllBottom" size="mini">全选</el-checkbox>
                <el-button @click="batchDel" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination :total="total" @current-change="userPageChange" :page-size="pageSize"
                               :page-sizes="pageSizes" @size-change="userPageSizeChange"
                               :current-page.sync="page" layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>

    </div>
</template>
    
<script>
module.exports = {
    data: function () {
        return {
            loading:false,
            dataText: '数据加载中',

            checkedAll:false,
            search:{
                keyword:'',
                type:'1',
                utype:3
            },
            input: '',
            select: '',
            value: true,
            value1: '',
            tableHig: true,
            tableData: [
               {
                    id: '02',
                    jobname: '董事长',
                    neirong: '管理员操作：后台职位列表刷新职位',
                    comname: '宿迁鑫潮信息技术有限公司',
                    sxtime: '2023.05.17 13:25',
                    ip: '168.121.5.01',
                    dk: 'PC',

                },
            ],
            typeArr:{
                1:"用户名",
                2:"内容"
            },
            uri: "m=user&c=",
            total: 0,
            page: 1,
            idsArr: [],
            pageSize: 0,
            pageSizes:[],

            prevPage: 0
        }
    },
    created(){
        this.getList();
    },
    methods: {
        selectChange:function (val) {
            this.idsArr = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.idsArr.push(item.id);
                });
            }
            if (_this.idsArr.length == 0) {
                _this.checkedAll = false;
            } else {
                if (_this.idsArr.length == _this.tableData.length) {
                    _this.checkedAll = true;
                } else {
                    _this.checkedAll = false;
                }
            }
        },
        doUserQuery() {
            this.page = 1
            this.getList()
        },
        getList:function(){
            let _this = this;
            let url = _this.uri + 'admin_user_member&a=writtenOffLog';
            _this.search.page = this.page;
            _this.search.pageSize = this.pageSize;
            if (this.value1!=null  && this.value1!=''){
                _this.search.time =   this.value1.join(' ~ ');
            }else{
                _this.search.time = '';
            }
            _this.loading = true;
            httpPost(url, _this.search, {hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.tableData = res.data.data;
                    _this.total = res.data.total;
                    _this.loading = false;
                    _this.pageSizes=res.data.pageSizes;
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
        userPageChange(val) {
            this.page = val
            this.getList()
        },
        userPageSizeChange(val) {
            this.pageSize = val
            this.getList()
        },
        selectAllBottom:function(value){
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        batchDel:function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择需要删除的用户!');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_user_member&a=delwflog'
            delConfirm(this, { del: ids }, function (params) {
                httpPost(url, params).then(function(response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, _this.getList());
                    } else {
                        message.error(res.msg);
                    }
                })
            }, '确定要删除？')
        },
        del:function (detail) {
            let _this = this;
            let url = this.uri + 'admin_user_member&a=delwflog';
            delConfirm(this, { del: detail.id }, function (params) {
                httpPost(url, params).then(function(response) {
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
            console.log(`每页 ${val} 条`);
        },
        handleCurrentChange(val) {
            console.log(`当前页: ${val}`);
        },
        shortChange(e) {
            let orderMap = {ascending: 'asc', descending: 'desc'}
            this.search.t = e.order ? e.prop : null;
            this.search.order = orderMap[e.order];
            this.page = 1;
            this.getList();
        },
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