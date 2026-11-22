<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
            <div class="moduleInptList">
                <el-input placeholder="请输入用户名/ID" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select" size="small" clearable>
                    <el-select v-model="search.type" slot="prepend" placeholder="请选择">
                        <el-option v-for="(item,key) in typelist" :key="key" :label="item.label" :value="item.value"></el-option>
                    </el-select>
                </el-input>
            </div>
            <div class="tableSeachInpt">
                <el-input placeholder="请输入内容" @keyup.enter.native="doUserQuery" size="small" prefix-icon="el-icon-search" v-model="search.content" clearable>
                </el-input>
            </div>
            <div class="tableSeachInpt tableSeachInptsmalltwo">
                <el-date-picker v-model="daterange" type="daterange" range-separator="至" start-placeholder="开始日期"
                                end-placeholder="结束日期" size="mini" clearable @change="doUserQuery">
                </el-date-picker>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="search.operas" size="small" slot="prepend" placeholder="操作内容" clearable @change="doUserQuery">
                    <el-option v-for="(value,key) in operasArr" :key="key" :label="value" :value="key"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="search.parrs" size="small" slot="prepend" placeholder="操作类型" clearable @change="doUserQuery">
                    <el-option label="增加" value="1"></el-option>
                    <el-option label="修改" value="2"></el-option>
                    <el-option label="删除" value="3"></el-option>
                    <el-option label="刷新" value="4"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="search.end" size="small" slot="prepend" placeholder="操作时间" clearable @change="doUserQuery">
                    <el-option v-for="(item,key) in time" :key="key" :label="item.label" :value="item.value"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
            </div>
                </div>
        </div>


        <div class="moduleElTable" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe
                      @selection-change="selectChange" ref="multipleTable"
                      @sort-change="shortChange"
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="uid" label="用户ID" width="120" sortable="custom"></el-table-column>
                <el-table-column prop="username" label="用户名" width="150">
                </el-table-column>
                <el-table-column prop="zzh" label="猎头名称" min-width="100" show-overflow-tooltip>
                    <template slot-scope="scope" >
                        <el-link  :href="scope.row.lietou_url" target="_blank">{{scope.row.ltname}}</el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="neirong" label="内容" min-width="180" show-overflow-tooltip>
                    <template slot-scope="scope">
                        {{scope.row.content}}
                        <template v-if="scope.row.sub_n">
                            ；{{scope.row.sub_n}}
                        </template>
                    </template>
                </el-table-column>
                <el-table-column prop="ip" label="IP" width="110"></el-table-column>
                <el-table-column prop="ctime_ymd" label="操作时间" width="150"></el-table-column>
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
                <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="batchDel" size="mini">批量删除</el-button></div>
            <div class="modulePagNum">
                <el-pagination :total="total" @current-change="userPageChange" :page-size="pageSize"
                               :page-sizes="pageSizes"
                               @size-change="handleSizeChange"
                               :current-page.sync="page" layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
    </div>
</template>

<script>
module.exports = {
    props:{
        typelist:Array,
        time:Array,
        weburl:String,
        uid:String
    },
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',
            checkedAll:false,
            daterange:'',
            search:{
                content:'',
                keyword:'',
                parrs:'',
                end:"",
                time:"",
                type:1,
                operas:''
            },
            operasArr:{
                10: '职位',
                88: '财务',
                3: '下载简历',
                5: '收藏关注',
                6: '应聘委托',
                7: '基本信息',
                8: '修改密码',
                11: '修改账号',
                12: '账号认证',
                16: '图片',
                17: '积分兑换',
                18: '消息',19: '问答',24: '优惠券',25: '悬赏推荐',26: '浏览'
            },
            checked: '',
            seachbutn: false,
            tableHig: true,
            tableData: [],
            items: [
                { type: '', label: '正常' },
            ],
            idsArr:[],
            total:0,
            page: 1,
            pageSize: 0,
            pageSizes:[],
            uri: "m=user&c=",

            prevPage: 0
        }
    },
    created() {
        if(this.uid){
            this.search.type = 3;
            this.search.keyword = this.uid;
        }
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
            let daterange = this.daterange;
            if (daterange) {
                this.search.time_start = formatDate(daterange[0]);
                this.search.time_end = formatDate(daterange[1]);
            } else {
                this.search.time_start = '';
                this.search.time_end = '';
            }
            this.getList()
        },
        userPageChange(val) {
            this.page = val
            this.getList()
        },
        handleSizeChange(val) {
            this.pageSize = val
            this.getList()
        },
        getList:function (){
            let _this = this;
            let url = _this.uri + 'admin_lt_log&a=index';
            _this.search.page = this.page;
            _this.search.pageSize = this.pageSize;
            _this.search.utype = 3;
            _this.loading = true;
            httpPost(url, _this.search, {hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.tableData = res.data.data;
                    _this.total = res.data.total;
                    _this.loading = false;
                    _this.pageSizes =res.data.pageSizes;
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
        del:function (detail) {
            let _this = this;
            let url = this.uri + 'admin_lt_log&a=delLog';
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
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择需要删除的企业日志!');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_lt_log&a=delLog'

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
        selectAllBottom(value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
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
    height: calc(100% - 95px) !important;
}

</style> 