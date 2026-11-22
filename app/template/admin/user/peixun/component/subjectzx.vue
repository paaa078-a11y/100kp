<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" placeholder="输入你要搜索的关键字" @keyup.enter.native="search" v-model="searchForm.keyword" clearable class="input-with-select">
                        <el-select v-model="searchForm.type" slot="prepend" placeholder="请选择">
                            <el-option label="咨询人" :value="1"></el-option>
                            <el-option label="咨询内容" :value="3"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div v-for="(searchItem, searchIndex) in searchList" :key="searchIndex" class="moduleInptList">
                    <el-select size="small" v-model="searchForm[searchItem.param]" slot="prepend" :clearable="true" :placeholder="searchItem.name" @change="search">
                        <el-option v-for="(searchLabel, searchValue) in searchItem.value" :key="searchValue" :label="searchLabel" :value="searchValue"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable moduleElMoreSixd" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="list" stripe style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange" @sort-change="sortChange" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="编号" width="120" sortable="custom">
                </el-table-column>
                <el-table-column prop="zname" label="咨询人" width="230">
                </el-table-column>
                <el-table-column prop="pxname" label="咨询机构" min-width="160">
                    <template slot-scope="scope">
                        <el-link :underline="false" @click="openPage(scope.row.train_url)">{{scope.row.pxname}}</el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="phone" label="手机号" min-width="100">
                </el-table-column>
                <el-table-column prop="content" label="咨询内容" width="200" :show-overflow-tooltip="true">
                </el-table-column>
                <el-table-column prop="ctime" label="咨询时间" width="140">
                    <template slot-scope="scope">
                        <div>{{scope.row.ctime_n}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="reply" label="回复内容" width="200" :show-overflow-tooltip="true">
                    <template slot-scope="scope">
                        <div>
                            <span v-if="scope.row.reply">{{scope.row.reply}}</span>
                            <span v-else>未回复</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="reply_time" label="回复时间" width="140">
                    <template slot-scope="scope">
                        <div>
                            <span v-if="scope.row.reply_time">{{scope.row.reply_time_n}}</span>
                            <span v-else>未回复</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="openDetail(scope.$index)">查看</el-button>
                            <el-button size="mini" @click="del(scope.$index)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate" @change="checkAll">全选</el-checkbox>
                <el-button @click="batch('del')" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="page" :page-sizes="pageSizes" :page-size="limit" layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <!-- 弹窗 -->
        <div class="modluDrawer">
            <el-drawer title="咨询留言详情" :visible.sync="drawerDetail" :append-to-body="true" :show-close="true" :with-header="true" size="35%">
                <div class="drawerModInfo drawerModInfoOne">
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>咨询人：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.zname}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>手机号：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.phone}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>咨询机构：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.pxname}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>咨询内容：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.content}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>咨询时间：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.ctime_n}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>回复内容：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.reply}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>回复时间：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.reply_time_n}}</span>
                        </div>
                    </div>
                </div>
                <div class="setBasicButn" style="border: none;">
                    <el-button type="primary" size="medium" @click="del(key)">删除</el-button>
                    <el-button type="primary" size="medium" plain @click="drawerDetail = false">返回</el-button>
                </div>
            </el-drawer>
        </div>
    </div>
</template>
<script>
module.exports = {
    data: function() {
        return {
            loading: false,
            dataText: '数据加载中',
            // 搜索筛选项
            searchList: [],
            searchForm: {},

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

            // 咨询留言详情
            drawerDetail: false,
            key: 0,

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
            this.resetSearch();
            this.search();
        },
        getSearchFun:function(){
            let that = this;
            httpPost('m=user&c=peixun_subjectzx&a=getSearchData', {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    that.searchList = res.data.search_list;
                }
            })
        },
        resetSearch() {
            this.searchForm = {
                type: 1
            };
            this.limit = 0;
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

            httpPost('m=user&c=peixun_subjectzx', { ...params, ...searchForm }, {hideloading: true}).then(function(response) {
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
            if (this.multipleSelection.length == 0 && type == 'del') {
                message.error('请选择要删除的数据');
                return false;
            }else if (this.multipleSelection.length == 0){
                message.error('请选择要操作的数据项');
                return false;
            }

            let idArr = [];
            this.multipleSelection.forEach(function(item) {
                idArr.push(item.id);
            })
            this.idArr = idArr;

            if (type == 'del') {
                this.del();
            } else if (type == 'audit') {
                this.openAudit();
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
            } else { // 单个删除
                params.del = that.list[idx].id;
                msg = '你确定要删除当前项吗？';
            }

            delConfirm(this, params, function(params) {
                httpPost('m=user&c=peixun_subjectzx&a=del', params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        that.drawerDetail = false;
                        that.getList();
                        that.$refs.multipleTable.clearSelection();
                        message.success(res.data.msg);
                    }
                })
            }, msg)
        },

        openDetail(idx) {
            this.detail = this.list[idx];
            this.key = idx;
            this.drawerDetail = true;
        },

        openPage(url) {
            window.open(url);
        },
    },
};
</script>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    /* width: 47%; */
    margin: 3px 0;
}

.drawerModInpt {
    overflow: hidden;
    position: relative;
    font-size: 14px;
    color: #707070;
}
</style>