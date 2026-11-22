<template>
    <div class="moduleHeights">
        <div class="moduleSeachbig">
            <div class="tableSeachInpt">
                <el-input v-model="searchForm.keyword" placeholder="搜索职位名称" size="small" prefix-icon="el-icon-search" clearable></el-input>
            </div>
            <div class="tableSeachInpt">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="handleSearch">查询</el-button>
            </div>
            <div class="tableSeachInpt">
                <el-button type="primary" plain icon="el-icon-plus" @click="addVisiable = true" size="mini">添加专题职位</el-button>
            </div>
        </div>
        <div class="moduleHeights">
            <div class="moduleElTable">
                <el-table :data="tableData" border style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" ref="multipleTable" @selection-change="handleSelectionChange" v-loading="loading">
                    <template slot="empty">
                        <p>{{dataText}}</p>
                    </template>
                    <el-table-column v-if="type == '1' || (type == '2' && is_manual == '1')" type="selection" width="55"></el-table-column>
                    <el-table-column prop="name" label="职位名称"width="240">
                        <template slot-scope="scope">
                            <el-link :href="scope.row.name_url" target="_blank" type="primary">{{ scope.row.name }}</el-link>
                        </template>
                    </el-table-column>
                    <el-table-column prop="com_name" label="企业名称" width="220">
                        <template slot-scope="scope">
                            <el-link :href="scope.row.com_name_url" target="_blank" type="primary">{{ scope.row.com_name }}</el-link>
                        </template>
                    </el-table-column>
                    <el-table-column prop="rating_name" label="会员等级"></el-table-column>
                    <el-table-column prop="job_edu" label="学历要求"></el-table-column>
                    <el-table-column prop="job_exp" label="经验要求"></el-table-column>
                    <el-table-column prop="status_n" label="职位状态">
                        <template slot-scope="scope">
                            <div :style="scope.row.status_n != '招聘中' ? 'color: red;' : ''">{{ scope.row.status_n }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="sdate_n" label="发布时间" width="140"></el-table-column>
                    <el-table-column prop="lastupdate_n" label="更新时间" width="140"></el-table-column>
                    <el-table-column v-if="type == '1' || (type == '2' && is_manual == '1')" fixed="right" label="操作" align="center" width="70">
                        <template slot-scope="scope">
                            <div class="cz_button">
                                <el-button type="danger" size="mini" @click="deleteRow(scope)">删除</el-button>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="modulePaging">
                <div>
                    <el-checkbox v-if="type == '1' || (type == '2' && is_manual == '1')" :indeterminate="isIndeterminate" v-model="checked" @change="selectAllBottom">全选</el-checkbox>
                    <el-button v-if="type == '1' || (type == '2' && is_manual == '1')" size="mini" @click="deleteRow(null, true)">批量删除</el-button>
                </div>
                <div class="modulePagNum">
                    <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="searchForm.page" :page-size="searchForm.limit" :page-sizes="pageSizes" layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
                </div>
            </div>
        </div>

        <!--添加职位-->
        <el-drawer title="添加专题职位" :visible.sync="addVisiable" :modal-append-to-body="false" append-to-body size="89%">
            <special_job_list_add :id="id" :rating-data="ratingData" :edu-data="eduData" :exp-data="expData" @child-event="$emit('child-event')"></special_job_list_add>
        </el-drawer>
    </div>
</template>

<script>
    module.exports = {
        props: {
            //special_job.id
            id: {type: [String, Number], default: 0},
            type: {type: [String, Number], default: 0},
            is_manual: {type: [String, Number], default: 0},
            ratingData: {type: [Array], default: []},//会员等级
            eduData: {type: [Array], default: []},//学历
            expData: {type: [Array], default: []},//工作经验
            counter: {type: [Number], default: 0},
        },
        data: function () {
            return {
                loading: false,
                dataText: '数据加载中',
                pytoken: localStorage.getItem("pytoken"),
                searchForm: {
                    page: 1,
                    limit: null,
                    keyword: null,//关键字
                },
                total: 0,
                tableData: [],
                pageSizes: [],
                checked: false,//全选
                isIndeterminate: false,// checkbox 的不确定状态
                selectedItem: [],
                addVisiable: false,//添加专题职位
                prevPage:0,
            }
        },
        created: function () {
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
                this.searchForm.sort = e.order ? e.prop : null;
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
                this.scrollTop();
            },
            scrollTop(){
                if(this.prevPage != this.searchForm.page){
                    this.prevPage = this.searchForm.page;
                    this.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                }
            },
            handleSearch() {
                this.searchForm.page = 1
                this.getList()
            },
            getList() {
                let _this = this;
                let params = JSON.parse(JSON.stringify(this.searchForm));
                params.id = this.id;
                for (let index in params) {
                    (params[index] === '') && (params[index] = null);
                }

                let url = 'm=yunying&c=special_job&a=job_list';
                if (this.type == 1) {
                    //手动增加职位
                } else {
                    //自动搜索职位
                    if (this.is_manual == 1) {
                        params.is_manual = 1;
                        url = 'm=yunying&c=special_job&a=job_list&is_manual=1';
                    } else {
                        url = 'm=yunying&c=special_job&a=job_list';
                    }
                }
                _this.loading = true;
                httpPost(url, params).then(function (response) {
                    let res = response.data;
                    if (res.error === 0) {
                        _this.tableData = res.data.list;
                        _this.total = res.data.total;
                        _this.searchForm.limit = res.data.perPage;
                        _this.pageSizes = res.data.pageSizes;
                        if(_this.prevPage != _this.searchForm.page){
                            _this.prevPage = _this.searchForm.page;
                            _this.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                        }
                        _this.loading = false;
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
                        list.push(item.jobid);
                    }
                    params.del = list;
                } else {
                    // let index = scope.$index;
                    // this.tableData.splice(index, 1);
                    params.id = scope.row.jobid;
                }
                params.sid = this.id;
                delConfirm(this, params, this.delete);
            },
            delete(params) {
                let _this = this;
                httpPost('m=yunying&c=special_job&a=deljob', params).then(function (response) {
                    let res = response.data;
                    if (res.error === 0) {
                        message.success(res.msg);
                        //调用父组件方法，最终更新列表
                        _this.$emit("child-event");
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
        },
        components: {
            'special_job_list_add': httpVueLoader('./special_job_list_add.vue'),
        },
        watch: {
            id: {
                handler: function (newValue, oldValue) {
                    if (newValue) {
                        this.getList();
                    }
                },
                deep: true,
                immediate: true
            },
            counter: {
                handler: function (newValue, oldValue) {
                    if (newValue > 0) {
                        this.getList();
                    }
                },
                deep: true,
                immediate: true
            },
        }
    };
</script>
<style scoped>
    .drawerModInfo::-webkit-scrollbar {display: none;}
    .moduleElenAlRight {overflow: hidden;position: absolute;width: 100%;height: calc(100% - 62px);top: 120px;left: 0;z-index: 2;}
    .moduleHeights {overflow: hidden;position: relative;width: 100%;height: 100%;}
    .el-tabs {position: relative;width: 100%;height: 100%;}
</style>