<template>
    <div class="moduleElHight">
     <div class="moduleSeachbig">
         <div class="tableSeachInpt">
             <el-input placeholder="输入你要搜索的关键字" @keyup.enter.native="doUserQuery" size="small" prefix-icon="el-icon-search" v-model="search.keyword" clearable>
             </el-input>
         </div>
         <div class="tableSeachInpt tableSeachInptsmall">
             <el-select v-model="search.status" size="small" slot="prepend" placeholder="审核状态" clearable @change="doUserQuery">
                 <el-option v-for="(value,key) in statusArr" :key="key" :label="value" :value="key"></el-option>
             </el-select>
         </div>
         <div class="tableSeachInpt tableSeachInptsmall">
             <el-select v-model="search.end" size="small" slot="prepend" placeholder="申请时间" clearable @change="doUserQuery">
                 <el-option v-for="(value,key) in auditTimes" :key="key" :label="value" :value="key"></el-option>
             </el-select>
         </div>
         <div class="tableSeachInpt">
             <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
         </div>
     </div>
		<div class="admin_datatip">
            <i class="el-icon-document"></i>  
            数据统计：共 {{ data.ltCertAllNum }} 条
            <span class="admin_datatip_n" @click="lockList('0')">未审核：{{ data.ltcertStatusNum1 }} 条 </span>
            <span class="admin_datatip_n" @click="lockList('2')">未通过：{{ data.ltcertStatusNum1 }} 条</span>
            <span class="admin_datatip_n">搜索结果： {{ total }} 条</span>
		</div>
        <div class="moduleElTable" style="border: 1px solid #ebeef5; height: calc(100% - 134px); width: calc(100% - 2px);">
            <el-table :data="tableData" stripe style="width: 100%"
                      @selection-change="selectChange"
                      @sort-change="shortChange"
                      ref="multipleTable"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="uid" label="用户ID" width="120" sortable="custom">
                </el-table-column>
                <el-table-column prop="realname" label="猎头名称" width="100">
                </el-table-column>
                <el-table-column label="查看" width="120" align="center">
					<template slot-scope="scope">
                        <div>
                            <el-avatar :src="scope.row.check"  @click.native="shenhe(scope.row)"></el-avatar>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="ctime" label="申请时间" min-width="140" sortable="custom">
                </el-table-column>
                <el-table-column prop="laiyuan" label="	状态" width="130">
                    <template slot-scope="scope">
                        <template v-if="scope.row.status == 1">
                            <span class="admin_state1">已审核</span>
                        </template>
                        <template v-if="scope.row.status == 0">
                            <span class="admin_state4">未审核</span>
                        </template>
                        <template v-if="scope.row.status == 2">
                            未通过
                        </template>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="shenhe(scope.row)">审核</el-button>
                            <el-button size="mini" type="danger" @click="del(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="isIndeterminate" @change="selectAllBottom">全选</el-checkbox>
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
            <el-dialog title="猎头认证审核" :visible.sync="drawer3" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="500px">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList" v-if="batch == false">
                        <div class="toolClasTite">
                            <span>认证执照：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-avatar :src="detail.check"></el-avatar>
                            <el-button size="mini" @click="lookPhoto">查看原图</el-button>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核操作：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio-group v-model="drawerRadio">
                                <el-radio label="1">正常</el-radio>
                                <el-radio label="2">未通过</el-radio>
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
					<el-button type="primary" @click="drawerSave" :disabled="submitLoading">确 定</el-button>
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

            auditTimes:{
                1	:	'今天',
                3	:	'最近三天',
                7	:	'最近七天',
                15	:	'最近半月',
                30	:	'最近一个月'
            },
            statusArr:{
                1:'已审核',
                2:'未通过',
                0:'未审核'
            },
            uri: "m=user&c=",
            search:{
                status: this.status,
            },
            total: 0,
            page: 1,
            idsArr: [],
            pageSize: 0,
            pageSizes:[],
            checkedAll:false,
            isIndeterminate: false,// checkbox 的不确定状态
            resumePreviewVisible: false,
            select: '',
            value: true,
            drawer: false,
            drawer2: false,
            tableData: [],
            data:{},
            drawer3:false,
            drawerRadio:'',
            drawerTextarea:'',
            detail:{},
            batch:false,
            submitLoading: false,

            prevPage: 0
        }
    },
    created(){
        this.getList();
        this.ltNumFun(); //
    },
    methods: {
        selectAllBottom:function (value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择需要删除的用户!');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_lt_cert&a=del'
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
        batchShenhe: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择需要审核的用户!');
                return
            }
            this.drawerRadio='';
            this.drawerTextarea='';
            this.batch = true;
            this.drawer3 =true;
        },
        selectChange:function (val) {
            this.idsArr = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.idsArr.push(item.uid);
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
        shortChange(e) {
            let orderMap = {ascending: 'asc', descending: 'desc'}
            this.search.t = e.order ? e.prop : null;
            this.search.order = orderMap[e.order];
            this.page = 1;
            this.getList();
        },
        doUserQuery() {
            this.page = 1
            this.getList()
        },
        userPageChange(val) {
            this.page = val
            this.getList()
        },
        handleSizeChange(val) {
            this.pageSize = val;
            this.getList();
        },
        getList:function(){
            let _this = this;
            let url = _this.uri + 'admin_lt_cert&a=index';
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
        ltNumFun:function(){
            let _this = this;
            let url = _this.uri + 'admin_lt_cert&a=ltcertNum';
            httpPost(url, {}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.data = res.data;
                }
            })
        },
        shenhe:function(detail){
            this.detail = detail;
            this.drawerTextarea = detail.statusbody
            this.drawerRadio = detail.status
            this.drawer3=  true;
        },
        lockList:function(status){
            this.search.status = status;
            this.page = 1
            this.getList()
        },
        del: function (detail) {
            let _this = this;
            let url = this.uri + 'admin_lt_cert&a=del';
            delConfirm(this, { id: detail.uid }, function (params) {
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
        lookPhoto:function () {
            window.open(this.detail.check);
        },
        drawerSave:function () {
            let sendData = {
                status :this.drawerRadio,
                statusbody :this.drawerTextarea,
            }
            if (!this.batch){
                sendData.uid =  this.detail.uid;
            }else {
                sendData.uid = this.idsArr.join(',');
            }
            let url = this.uri+'admin_lt_cert&a=status';
            let _this = this;
            _this.submitLoading = true;
            httpPost(url, sendData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg, function(){
                        _this.drawer3 = false;
                        _this.getList()
                    });
                } else {
                    message.error(res.msg);
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                setTimeout(function () {
                    _this.submitLoading = false;
                }, 2000);
            });
        }

    },
};
</script>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 47%;
    margin: 3px 0;
}
</style>