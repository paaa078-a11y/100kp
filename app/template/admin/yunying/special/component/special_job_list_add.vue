<template>
    <div class="jobspecial_box">
        <div class="moduleSeachbig">
            <!--关键字搜索和查询在一起-------------------------------------------------------------------->
            <div class="tableSeakusydsg" style="padding: 2px 0;">
                <el-input v-model="searchForm.keyword" placeholder="请输入搜索内容" class="input-with-select" size="small" prefix-icon="el-icon-search" clearable>
                    <el-select slot="prepend" v-model="searchForm.type" size="small" placeholder="职位名称">
                        <el-option label="公司名称" value="1"></el-option>
                        <el-option label="职位名称" value="2"></el-option>
                        <el-option label="公司福利" value="3"></el-option>
                    </el-select>
                </el-input>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <span class="datetitle">发布时段</span>
                <el-date-picker v-model="searchForm.addtime" class="datearea" type="daterange" value-format="yyyy-MM-dd" size="small" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" @change="handleSearch"></el-date-picker>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <span class="datetitle">更新时段</span>
                <el-date-picker v-model="searchForm.uptime" class="datearea" type="daterange" value-format="yyyy-MM-dd" size="small" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" @change="handleSearch"></el-date-picker>
            </div>
            <div class="tableSeachInpt">
                <!--TODO 7.0 统一类别选择-->
                <job_class @confirm="confirmJob"></job_class>
            </div>
            <div class="tableSeachInpt">
                <!--TODO 7.0 统一城市选择-->
                <city_class @confirm="confirmCity"></city_class>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="searchForm.rating" size="small" slot="prepend" placeholder="会员等级" clearable @change="handleSearch">
                    <el-option v-for="item in ratingData" :key="item.id" :label="item.name" :value="item.id"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="searchForm.edu" size="small" slot="prepend" placeholder="学历要求" clearable @change="handleSearch">
                    <el-option v-for="item in eduData" :key="item.id" :label="item.name" :value="item.id"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="searchForm.exp" size="small" slot="prepend" placeholder="经验要求" clearable @change="handleSearch">
                    <el-option v-for="item in expData" :key="item.id" :label="item.name" :value="item.id"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="handleSearch">查询</el-button>
            </div>
        </div>
        <div class="admin_datatip"><i class="el-icon-document"></i> 数据统计：搜索结果 {{ total }} 条
            <span class="admin_datatip_n">未加入：{{ joinNum }} 条</span>
            <span class="admin_datatip_n">已加入：{{ joinNumOff }} 条 </span>
        </div>
        <div class="tanleFreom">
            <div class="cominfotable">
                <el-table :data="tableData" border style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" ref="multipleTable" @selection-change="handleSelectionChange" @sort-change="shortChange" v-loading="loading">
                    <template slot="empty">
                        <p>{{dataText}}</p>
                    </template>
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column prop="name" label="职位名称" min-width="255">
                        <template slot-scope="scope">
                            <el-link :href="scope.row.joburl" target="_blank" type="primary">{{ scope.row.name }}
                            </el-link>
                        </template>
                    </el-table-column>
                    <el-table-column prop="com_name" label="企业名称" min-width="255">
                        <template slot-scope="scope">
                            <el-link :href="scope.row.comurl" target="_blank" type="primary">{{ scope.row.com_name }}
                            </el-link>
                        </template>
                    </el-table-column>
                    <el-table-column prop="rating_name" label="会员等级"></el-table-column>
                    <el-table-column prop="job_edu" label="学历要求"></el-table-column>
                    <el-table-column prop="job_exp" label="经验要求"></el-table-column>
                    <el-table-column prop="sdate_n" label="发布时间" width="140"></el-table-column>
                    <el-table-column prop="up" label="更新时间" sortable="custom" width="140">
                        <template slot-scope="scope">
                            {{ scope.row.lastupdate_n_n }}
                        </template>
                    </el-table-column>
                    <el-table-column label="参与状态">
                        <template slot-scope="scope">
                            <div v-html="scope.row.join_n"></div>
                        </template>
                    </el-table-column>
                    <el-table-column fixed="right" label="操作" align="center" width="70">
                        <template slot-scope="scope">
                            <div v-show="scope.row.join == 0" class="cz_button">
                                <el-button size="small" @click="addjobRow(scope)">加入</el-button>
                            </div>
                            <div v-show="scope.row.join == 1">-</div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="modulePaging">
                <div>
                    <el-checkbox :indeterminate="isIndeterminate" v-model="checked" @change="selectAllBottom">全选</el-checkbox>
                    <el-button @click="addjobRow(null, true)">批量加入</el-button>
                </div>
                <div class="modulePagNum">
                    <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="searchForm.page" :page-size="searchForm.limit" :page-sizes="pageSizes" layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    module.exports = {
        props: {
            //special_job.id
            id: {type: [String, Number], default: 0},
            ratingData: {type: [Array], default: []},//会员等级
            eduData: {type: [Array], default: []},//学历
            expData: {type: [Array], default: []},//工作经验
        },
        data: function () {
            return {
                loading: false,
                dataText: '数据加载中',

                pytoken: localStorage.getItem("pytoken"),
                searchForm: {
                    page: 1,
                    limit: null,
                    type: '1',
                    keyword: null,//关键字
                    addtime: null,//发布时段
                    uptime: null,//更新时段
                    job_class: null,
                    city_class: null,
                    edu: null,
                    exp: null,
                },
                total: 0,
                tableData: [],
                pageSizes: [],
                checked: false,//全选
                isIndeterminate: false,// checkbox 的不确定状态
                selectedItem: [],

                joinNum: 0,//已加入
                joinNumOff: 0,//未加入
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
                params.addtime = Array.isArray(params.addtime) ? params.addtime.join('~') : null;
                params.uptime = Array.isArray(params.uptime) ? params.uptime.join('~') : null;
                params.sid = this.id;
                for (let index in params) {
                    (params[index] === '') && (params[index] = null);
                }
                _this.loading = true;
                httpPost('m=yunying&c=special_job&a=job_join', params).then(function (response) {
                    let res = response.data;
                    if (res.error === 0) {
                        _this.tableData = res.data.list;
                        _this.total = res.data.total;
                        _this.searchForm.limit = res.data.perPage;
                        _this.pageSizes = res.data.pageSizes;
                        _this.joinNum = res.data.joinNum;
                        _this.joinNumOff = res.data.joinNumOff;
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
            addjobRow(scope, isMore) {
                let params = {};
                if (isMore) {
                    if (!this.selectedItem.length) {
                        message.error('你还未选择任何信息！');
                        return false;
                    }
                    let list = [];
                    for (let item of this.selectedItem) {
                        list.push(item.id);
                    }
                    params.add = list;
                } else {
                    // let index = scope.$index;
                    // this.tableData.splice(index, 1);
                    params.add = [scope.row.id];
                }
                params.sid = this.id;
                delConfirm(this, params, this.addjob, '你确定要加入当前项吗？');
            },
            addjob(params) {
                let _this = this;
                httpPost('m=yunying&c=special_job&a=addjob', params).then(function (response) {
                    let res = response.data;
                    if (res.error === 0) {
                        message.success(res.msg);
                        _this.getList();
                        //更新父页面列表
                        _this.$emit("child-event");
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
            confirmJob(data) {
                this.searchForm.job_class = data.jobId.join(',');
                this.handleSearch();
            },
            confirmCity(data) {
                this.searchForm.city_class = data.cityId.join(',');
                this.handleSearch();
            },
        },
        components: {
            'job_class': httpVueLoader('../../../component/job_class.vue'),
            'city_class': httpVueLoader('../../../component/city_class.vue'),
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
        }
    };
</script>
<style scoped>
    .drawerModInfo::-webkit-scrollbar {display: none;}
    .moduleElenAlRight {overflow: hidden;position: absolute;width: 100%;height: calc(100% - 62px);top: 120px;left: 0;z-index: 2;}
    .datetitle {font-size: 14px;color: #555;height: 32px;display: flex;align-items: center;justify-content: center;box-sizing: border-box;padding: 0 8px;background-color: #f5f7fa;border-width: 1px;border-style: solid;border-right-width: 0;border-color: #dcdfe6;}
    .datearea {border-top-left-radius: 0;border-bottom-left-radius: 0;margin-right: 8px;width: 220px;}
    .jobspecial_box {overflow: hidden;position: relative;width: 100%;height: 100%;}
    .tanleFreom {overflow: hidden;position: relative;width: 100%;height: calc(100% - 180px);}
    .cominfotable {overflow: hidden;position: relative;width: 100%;height: calc(100% - 60px);}
</style>