<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach" style="margin-bottom: 8px; height: initial;">
            <div class="moduleElTabInpt">
                <div class="moduleInptList moduleInptWidt">
                    <el-input size="small" placeholder="搜索关键词" @keyup.enter.native="search" v-model="keyword" class="input-with-select">
                        <el-select v-model="or_type" slot="prepend">
                            <el-option label="公司名" value="1"></el-option>
                            <el-option label="联系人" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="moduleInptList" v-show="tab==2">
                    <el-select size="small" v-model="auid" placeholder="客户经理" clearable @change="search">
                        <el-option v-for="item in ausers" :key="item.uid" :label="item.name" :value="item.uid"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select size="small" v-model="oType" placeholder="订单类型" clearable @change="search">
                        <el-option v-for="(item,key) in orderTypeArr" :key="key" :label="item" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-date-picker size="small" style="width: 230px;" v-model="orTime" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="订单开始" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="search"></el-date-picker>
                </div>

                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                    <el-button size="mini" @click="reset">重置</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable moduleElTablTwo">
            <el-table ref="table" :key="timer" :data="tableData" v-loading="list_loading" border style="width: 100%" :default-sort="{prop: 'id', order: 'descending'}" @sort-change="sortChange" :empty-text="emptytext" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%">
                <el-table-column prop="id" label="编号" width="120" sortable="custom"></el-table-column>
                <el-table-column prop="ortime_n" label="成单时间" width="160">
                    <template slot-scope="props">
                        <span>{{props.row.ortime_n}}</span>
                        <el-tag size="mini" type="info">{{props.row.week_n}}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="客户信息" width="260">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <el-button type="text" @click="openComInfo(props.row.uid)">{{props.row.comname}}</el-button>
                            <span>{{props.row.linkman}} {{props.row.linktel}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="order_price" label="成交金额" width="120">
                    <template slot-scope="props">
                        <span style="color: red;">￥{{props.row.order_price}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="ordertype_n" label="订单类型" width="130"></el-table-column>
                <el-table-column prop="order_remark" label="备注信息" min-width="300"></el-table-column>
                <el-table-column prop="aname" label="业务员" width="120" v-if="tab==2"></el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total" layout="total, sizes, prev, pager, next, jumper"></el-pagination>
            </div>
        </div>
        <!--弹窗：客户管理-->
        <div class="modluDrawer">
            <el-drawer title="客户管理" :visible.sync="comInfoDrawer" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
                <div class="modluDrawerKehuguanli">
                <com_info ref="comInfo" :cuid="cuid" v-if="comInfoDrawer"></com_info>
                </div>
            </el-drawer>
        </div>
    </div>
</template>

<script>
    module.exports = {
        data: function () {
            return {
                emptytext: '暂无数据',
                tableData: [],
                total: 0,
                limit: 0,
                currentPage: 1,
                prevPage: 0,
                page_sizes: [],
                sort_t: '',
                order: '',
                list_loading: false,

                timer: 0,
                tab: '1',
                ausers: [],
                orderTypeArr: [],
                timeOptions: {
                    shortcuts: [{
                        text: '昨天',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24);
                            end.setTime(end.getTime() - 3600 * 1000 * 24);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '今天',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '本周',
                        onClick(picker) {
                            const start = new Date(new Date().setHours(0, 0, 0) - (new Date().getDay() - 1) * 24 * 60 * 60 * 1000);
                            const end = new Date();
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '上周',
                        onClick(picker) {
                            const start = new Date(new Date().setHours(0, 0, 0) - (new Date().getDay() + 6) * 24 * 60 * 60 * 1000);
                            const end = new Date(new Date().setHours(0, 0, 0) + (0 - new Date().getDay()) *24 * 60 * 60 *1000);
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '本月',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date(new Date(new Date().getFullYear(), new Date().getMonth(), 1).setHours(0, 0, 0));
                            picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '上月',
                        onClick(picker) {
                            const end = new Date(new Date(new Date().getFullYear(), new Date().getMonth(), 0).setHours(23, 59, 59, 59));
                            const start = new Date(new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).setHours(0, 0, 0));
                            picker.$emit('pick', [start, end]);
                        }
                    }]
                },

                keyword: '',
                or_type: '1',
                oType: '',
                orTime: '',

                auid: '',

                comInfoDrawer: false,
                cuid: ''
            }
        },
        components: {
            'comlinkinfo': httpVueLoader('./comlinkinfo.vue'),
            'com_info': httpVueLoader('./comInfo.vue'),
        },
        props: {
            deal_tab: Number
        },
        mounted() {
            this.getStaticData();
            if (this.tab == 1 || this.tab == 0){
                this.getDealList();
            }
        },
        watch: {
            deal_tab: {
                handler(val) {
                    this.tab = val;
                },
                deep: true,
                immediate: true
            },
        },
        methods: {
            resetSearch: function () {
                this.orTime = '';
                this.oType = '';
                this.keyword = '';
                this.or_type = '1';
                this.auid = '';
            },
            reset: function () {
                this.resetSearch();
                this.search();
            },
            async getDealList() {
                let that = this;
                let params = {
                    page: that.currentPage,
                    limit: that.limit,
                    t: that.sort_t,
                    order: that.order,
                    keyword: that.keyword,
                    auid: that.auid,
                    orTime: that.orTime,
                    oType: that.oType,
                    or_type: that.or_type,
                    tab: that.tab
                }

                this.list_loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=crm&c=crm_deal', params).then((result) => {
                    this.list_loading = false;
                    var res = result.data;

                    if (res.error == 0) {
                        that.tableData = res.data.list
                        that.total = parseInt(res.data.total)
                        that.page_sizes = res.data.page_sizes;
                        that.limit = res.data.page_size;
                        if(that.prevPage != that.currentPage){
                            that.prevPage = that.currentPage;
                            that.$refs.table.bodyWrapper.scrollTop = 0;
                        }
                        if (that.tableData.length === 0) {
                            that.emptytext = "暂无数据";
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            getStaticData: function () {
                var that = this
                let params = {
                    tab: that.tab
                }
                httpPost('m=crm&c=crm_deal&a=getStaticData', params, {hideloading: true}).then((result) => {
                    var res = result.data;
                    if (res.error == 0) {
                        that.ausers = res.data.ausers
                        that.orderTypeArr = res.data.orderTypeArr
                    }
                });
            },
            search: function () {
                this.currentPage = 1;
                this.getDealList();
            },
            sortChange: function (e) {
                this.sort_t = e.prop;

                this.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.search();
            },

            handleCurrentChange(val) {
                this.currentPage = val;
                this.getDealList()
            },
            handleSizeChange(val) {
                this.currentPage = 1
                this.limit = val
                this.getDealList()
            },
            openComInfo: function (uid) {
                this.comInfoDrawer = true;
                this.cuid = uid
            }
        },
    };
</script>
<style scoped>
    .moduleElTablTwo {height: calc(100% - 89px);}
</style>