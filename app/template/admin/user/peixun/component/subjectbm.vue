<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" placeholder="输入你要搜索的关键字" @keyup.enter.native="search" v-model="searchForm.keyword" clearable class="input-with-select">
                        <el-select v-model="searchForm.type" slot="prepend" placeholder="请选择">
                            <el-option label="客户名称" :value="1"></el-option>
                            <el-option label="课程名称" :value="2"></el-option>
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
            <el-table :data="list" stripe style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
                      @sort-change="sortChange"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="编号" width="120" sortable="custom">
                </el-table-column>
                <el-table-column prop="name" label="客户名称" width="230">
                </el-table-column>
                <el-table-column prop="subname" label="课程名称" min-width="160">
                    <template slot-scope="scope">
                        <el-link :underline="false" @click="openPage(scope.row.subject_url)">{{scope.row.subname}}</el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="trainname" label="机构名称" min-width="140">
                    <template slot-scope="scope">
                        <el-link :underline="false" @click="openPage(scope.row.train_url)">{{scope.row.trainname}}</el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="price" label="课程费用" width="130">
                </el-table-column>
                <el-table-column prop="isprice_n" label="收费方式" width="130">
                </el-table-column>
                <el-table-column prop="ctime" label="时间" width="180" sortable="custom">
                    <template slot-scope="scope">
                        <div>{{scope.row.ctime_n}}</div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="180">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="openDetail(scope.$index)">查看</el-button>
                            <el-button size="mini" @click="pay(scope.$index)" v-if="scope.row.isprice == 1 && (scope.row.order_state == 1 || scope.row.order_state == 3)">付款</el-button>
                            <el-button size="mini" @click="del(scope.$index)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate"
                             @change="checkAll">全选</el-checkbox>
                <el-button @click="batch('del')" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="page" :page-sizes="pageSizes" :page-size="limit"
                               layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <!-- 弹窗 -->
        <div class="modluDrawer">
            <el-drawer title="培训课程报名详情" :visible.sync="drawerDetail" :append-to-body="true" :show-close="true"
                :with-header="true" size="35%">
                <div class="drawerModInfo drawerModInfoOne">
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>客户名称：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.name}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>课程名称：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{subject.name}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>机构名称：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.trainname}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>培训价格：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{subject.price}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>报名方式：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{subject.isprice == 1 ? '在线收费' : '到场收费'}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>联系手机：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.phone}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>报名时间：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.ctime_n}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>备注：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{detail.content}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis" v-if="subject.isprice == 1">
                        <div class="drawerModTite">
                            <span>订单号：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span>{{bmorder.order_id}}</span>
                        </div>
                    </div>
                    <div class="drawerModLis" v-if="subject.isprice == 1">
                        <div class="drawerModTite">
                            <span>支付状态：</span>
                        </div>
                        <div class="drawerModInpt">
                            <span v-html="bmorder.order_state_n"></span>
                        </div>
                    </div>
                </div>
                <div class="setBasicButn" style="border: none; width: calc(100% - 40px); padding: 0 20px;">
                    <el-button type="primary" size="medium" @click="pay(key)"
                               v-if="subject.isprice == 1 && (bmorder.order_state == 1 || bmorder.order_state == 3)">确认付款</el-button>
                    <el-button type="primary" size="medium" @click="del(key)">删除</el-button>
                    <el-button type="primary" size="medium" plain @click="drawerDetail = false">返回</el-button>
                </div>
            </el-drawer>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    data: function () {
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

            saveLoading: false,

            // 培训课程报名详情
            drawerDetail: false,
            key: 0,
            subject: {},
            bmorder: {},

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
            httpPost('m=user&c=peixun_subjectbm&a=getSearchData', {},{hideloading: true}).then(function (response) {
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

            httpPost('m=user&c=peixun_subjectbm', { ...params, ...searchForm }, {hideloading: true}).then(function (response) {
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
            this.multipleSelection.forEach(function (item) {
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
            } else {// 单个删除
                params.del = that.list[idx].id;
                msg = '你确定要删除当前项吗？';
            }

            delConfirm(this, params, function (params) {
                httpPost('m=user&c=peixun_subjectbm&a=del', params).then(function(res) {
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
            let that = this,
                params = {
                    id: that.list[idx].id
                };

            httpPost('m=user&c=peixun_subjectbm&a=getInfo', params).then(function(response) {
                let res = response.data,
                    data = res.data;
                if (res.error > 0) {
                    message.error(res.data.msg);
                } else {
                    that.detail = data.info ? data.info : {};
                    that.subject = data.subject ? data.subject : {};
                    that.bmorder = data.order ? data.order : {};

                    that.key = idx;
                    that.drawerDetail = true;
                }
            })
        },

        pay(idx) {
            let that = this,
                params = {
                    id: that.list[idx].id
                };

            message.confirm('确认缴纳培训课程费用，是否确定？', function() {
                httpPost('m=user&c=peixun_subjectbm&a=setpay', params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        that.drawerDetail = false;
                        that.getList();
                        that.$refs.multipleTable.clearSelection();
                        message.success(res.data.msg);
                    }
                })
            })
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
.drawerModInpt{
    overflow: hidden;
    position: relative;
    font-size: 14px;
    color: #707070;
}
</style>