<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input placeholder="请输入搜索内容" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select" size="small" clearable>
                        <el-select v-model="search.ltnames" slot="prepend" placeholder="请选择">
                            <el-option label="用户名" value="1"></el-option>
                            <el-option label="职位名称" value="2"></el-option>
                            <el-option label="公司名称" value="3"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.zpstatus" size="small" slot="prepend" placeholder="招聘状态" clearable @change="doUserQuery">
                        <el-option v-for="(value,key) in zpStatus" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.rec" size="small" slot="prepend" placeholder="推荐状态" clearable @change="doUserQuery">
                        <el-option v-for="(value,key) in recArr" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.status" size="small" slot="prepend" placeholder="审核状态" clearable @change="doUserQuery">
                        <el-option v-for="(value,key) in statusArr" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.ltexp" size="small" slot="prepend" placeholder="工作经验" clearable @change="doUserQuery">
                        <el-option v-for="(value,key) in expArr" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
                </div>
            </div>
        </div>
        <div class="admin_datatip">
            <i class="el-icon-document"></i>
            数据统计：共 {{ data.ltjobAllNum }}  条
            <span class="admin_datatip_n" @click="lockList('4')">未审核：{{ data.ltjobStatusNum1 }} 条 </span>
            <span class="admin_datatip_n" @click="lockList('3')">未通过：{{ data.ltjobStatusNum2 }} 条</span>
            <span class="admin_datatip_n">搜索结果： {{ total }} 条</span>
        </div>
        <div class="moduleElTable" style="height: calc(100% - 134px);">
            <el-table :data="tableData" style="width: 100%" stripe
                      @selection-change="selectChange"
                      @sort-change="shortChange"
                      ref="multipleTable"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"> </el-table-column>
                <el-table-column prop="id" label="编号" width="80" sortable="custom"> </el-table-column>
                <el-table-column prop="username" label="用户名" width="150">
                </el-table-column>
                <el-table-column prop="comname" label="职位名称" min-width="200" show-overflow-tooltip>
                    <template slot-scope="props">
                        <el-link @click="tomember(props.row.joburl)">{{ props.row.job_name }}</el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="com_name" label="公司名称" min-width="230"> </el-table-column>
                <el-table-column prop="city" label="职位年薪" min-width="220">
                    <template slot-scope="props">
                        <template v-if="props.row.minsalary && props.row.maxsalary ">
                            ￥{{props.row.minsalary}} -- {{props.row.maxsalary}}万
                        </template>
                        <template v-else-if="props.row.minsalary">
                            ￥{{props.row.minsalary}}万以上
                        </template>
                        <template v-else>
                            面议
                        </template>
                    </template>
                </el-table-column>
                <el-table-column prop="cityid" label="工作地点"> </el-table-column>
                <el-table-column prop="exp" label="工作经验"> </el-table-column>
                <el-table-column prop="zpzt" label="招聘状态">
                    <template slot-scope="props">
					  	<div class="admin_state">
					  		<span v-if="props.row.zp_status == 0" class="admin_state1">招聘中</span>
					  		<span v-else class="admin_state5">已下架</span>
					  	</div>
                    </template>
                </el-table-column>
                <el-table-column label="推荐">
                    <template slot-scope="scope">
					  	<div class="moduleProps">
                            <el-switch v-model="scope.row.rec" active-value="1" inactive-value="0"
                                       @change="recFun(scope.row)">
                            </el-switch>
					  	</div>
                    </template>
                </el-table-column>
                <el-table-column prop=" " label="审核状态">
				    <template slot-scope="props">
                        <div class="admin_state">
                            <span v-if="props.row.status == 1" class="admin_state1">已审核</span>
                            <span v-if="props.row.status == 0" class="admin_state2">未审核</span>
                            <span v-if="props.row.status == 3" class="admin_state2">未通过</span>
                        </div>
				    </template>
                </el-table-column>
                <el-table-column label="操作" width="140" fixed="right">
                    <template slot-scope="scope">
                       <div class="cz_button">
                           <el-button size="mini" @click="drawerFun('shenhe',scope.row)">审核</el-button>
                           <el-button type="danger" size="mini" @click="del(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="isIndeterminate" @change="selectAllBottom" >全选</el-checkbox>
                <el-button @click="batchDel" size="mini">批量删除</el-button>
                <el-button @click="batchShenhe" size="mini">批量审核</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination :total="total" @current-change="userPageChange" :page-size="pageSize"
                               :page-sizes="pageSizes"
                               @size-change="handleSizeChange"
                               :current-page.sync="page" layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="猎头职位审核" :visible.sync="drawer3" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="500px">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核操作：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio-group v-model="drawerRadio">
                                <el-radio label="1">正常</el-radio>
                                <el-radio label="3">未通过</el-radio>
                            </el-radio-group>
                        </div>
                    </div>
                    <template>
                        <div class="toolClasList">
                            <div class="toolClasTite">
                                <span>审核说明：</span>
                            </div>
                            <div class="toolClasCont">
                                <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="drawerTextarea">
                                </el-input>
                            </div>
                        </div>
                    </template>
                </div>
                <span slot="footer" class="dialog-footer">
					<el-button @click="drawer3 = false">取 消</el-button>
					<el-button type="primary" @click="drawerSave">确 定</el-button>
				</span>
            </el-dialog>
        </div>
    </div>

</template>
    
<script>
module.exports = {
    props: {
        status: {type: String, default: ''}
    },
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',
            checkedAll:false,
            isIndeterminate: false,// checkbox 的不确定状态
            drawer3:false,
            search:{
                status: this.status,
                keyword:'',
                ltnames:'1',
            },
            uri: "m=user&c=",
            total: 0,
            page: 1,
            idsArr: [],
            pageSize: 0,
            pageSizes:[],
            input: '',
            select: '',
            value: true,
            drawer: false,
            seachbutn: false,
            tableHig: true,
            recArr: {
                1:'已推荐',
                2:'未推荐'
            },
            zpStatus:{
                2:'招聘中',
                1:'已下架'
            },
            expArr:{},
            statusArr:{
                1:'已审核',
                3:'未通过',
                2:'已锁定',
                4:'未审核'
            },
            tableData: [],
            items: [
                { type: '', label: '正常' },
            ],
            data:{},
            // 弹窗消息
            drawerRadio:'',
            drawerTextarea:'',
            drawerType:'',

            prevPage: 0
        }
    },
    created() {
        this.getList();
        this.getExp();
        this.ltNumFun();
    },
    methods: {
        ltNumFun: function () {
            let _this = this;
            let url = _this.uri + 'admin_lt_job&a=ltjobNum';
            httpPost(url, {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.data = res.data;
                }
            })
        },
        lockList:function(status){
            this.search.status = status;
            this.page = 1
            this.getList()
        },
        getExp:function () {
            let _this = this;
            let url = this.uri + 'admin_lt_job&a=getExp';
            httpPost(url, {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.expArr = res.data;
                }else {
                    message.error(res.msg);
                }
            })

        },
        getList:function(){
            let _this = this;
            let url = _this.uri + 'admin_lt_job&a=index';
            _this.search.page = this.page;
            _this.search.pageSize = this.pageSize;
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
                this.isIndeterminate = false;
            } else {
                if (_this.idsArr.length == _this.tableData.length) {
                    _this.checkedAll = true;
                    this.isIndeterminate = false;
                } else {
                    _this.checkedAll = false;
                    this.isIndeterminate = true;
                }
            }
        },
        doUserQuery() {
            this.page = 1
            this.getList()
        },
        userPageChange(val) {
            this.page = val
            this.getList()
        },
        selectAllBottom:function (value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        handleSizeChange(val) {
            this.pageSize = val;
            this.getList();
        },
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择要删除的数据');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_lt_job&a=del'
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
        shortChange(e) {
            let orderMap = {ascending: 'asc', descending: 'desc'}
            this.search.t = e.order ? e.prop : null;
            this.search.order = orderMap[e.order];
            this.page = 1;
            this.getList();
        },
        tomember(url) {
            window.open(url);
        },
        recFun: function (detail) {
            let _this = this;
            let url = this.uri + 'admin_lt_job&a=recommend';
            let sendData = {
                id: detail.id,
                rec: detail.rec,
                type: 'rec',
            };
            httpPost(url, sendData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg, _this.getList())
                } else {
                    message.error(res.msg);
                }
            })
        },
        // 弹窗
        drawerFun: function (type,detail) {
            if (type != 'batchShenhe') {
                this.drawerTextarea = detail.statusbody
                this.drawerRadio = detail.status
                this.detail = detail;
            }
            this.drawerType = type;
            this.drawer3 = true;
        },
        drawerSave: function () {
            let _this = this;

            let sendData = {
                status: _this.drawerRadio,
                statusbody:_this.drawerTextarea,
            }

            if (this.drawerType == 'batchShenhe') {
                let ids = this.idsArr.join(',');
                sendData.pid = ids
            }else{
                sendData.pid = _this.detail.id
            }
            let url = this.uri + 'admin_lt_job&a=status';
            if (!this.drawerRadio) {
                message.error('请选择认证操作!');
                return;
            }
            httpPost(url, sendData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg, _this.getList());
                } else {
                    message.error(res.msg);
                }
            })
            this.drawer3 = false;
        },
        del: function (detail) {
            let _this = this;
            let url = this.uri + 'admin_lt_job&a=del';
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
        batchShenhe: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择要操作的数据项');
                return
            }
            this.drawerFun('batchShenhe', null)

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