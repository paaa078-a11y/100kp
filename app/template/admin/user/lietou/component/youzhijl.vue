<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input placeholder="请输入搜索内容" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select"  size="small" clearable>
                        <el-select v-model="search.searchrname" slot="prepend" placeholder="请选择">
                            <el-option label="用户名" value="1"></el-option>
                            <el-option label="期望职位" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.rec" size="small" slot="prepend" placeholder="推荐状态" clearable @change="doUserQuery">
                        <el-option label="已推荐 " value="1"></el-option>
                         <el-option label="未推荐 " value="2"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.searchtype" size="small" slot="prepend" placeholder="工作性质" clearable @change="doUserQuery">
                        <el-option v-for="(value,key) in reportarr" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.status" size="small" slot="prepend" placeholder="审核状态" clearable @change="doUserQuery">
                        <el-option v-for="(value,key) in statusArr" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.verify" size="small" slot="prepend" placeholder="审核时间" clearable @change="doUserQuery">
                        <el-option v-for="(value,key) in auditTimes" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.searchreport" size="small" slot="prepend" placeholder="到岗时间" clearable @change="doUserQuery">
                        <el-option v-for="(value,key) in typearr" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
                </div>
            </div>

        </div>
		<div class="admin_datatip">
			<i class="el-icon-document"></i>
			数据统计：共 {{ data.resumeAllNum }} 条
			<span class="admin_datatip_n" @click="lockList('1')">未审核：{{ data.resumeStatusNum1 }} 条 </span>
			<span class="admin_datatip_n" @click="lockList('3')">未通过：{{ data.resumeStatusNum2 }} 条</span>
			<span class="admin_datatip_n" @click="lockList('4')">已锁定：{{ data.resumeStatusNum3 }} 条</span>
			<span class="admin_datatip_n">搜索结果： {{ total }} 条</span>
		</div>
        <div class="moduleElTable" style="height: calc(100% - 134px);">
            <el-table :data="tableData" border style="width: 100%"
                      @selection-change="selectChange"
                      @sort-change="shortChange"
                      ref="multipleTable"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="简历编号" width="130" sortable="custom"></el-table-column>
                <el-table-column prop="username" label="用户名" min-width="180"></el-table-column>
                <el-table-column prop="name" label="期望职位" width="140"></el-table-column>
                <el-table-column prop="job_classname" label="意向职位" width="130"></el-table-column>
                <el-table-column  label="基本信息" width="140">
                    <template slot-scope="props">
                        <template v-if="props.row.minsalary && props.row.maxsalary">
                            {{props.row.minsalary}}-{{props.row.maxsalary}}
                        </template>
                        <template v-else-if="props.row.minsalary && !props.row.maxsalary">
                            {{props.row.minsalary}}以上
                        </template>
                        <template v-else>
                            面议
                        </template><br>
                        {{props.row.type_n}}<br>
                        {{props.row.report_n}}
                    </template>
                </el-table-column>
                <el-table-column prop=" " label="工作地点" width="140">
                    <template slot-scope="props">
                        <template v-if="props.row.city_n">{{props.row.city_n}}</template>
                        <template v-else>{{props.row.city_classname}}</template>
                    </template>
                </el-table-column>
                <el-table-column prop=" " label="推荐" width="140">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <el-switch v-model="scope.row.rec" active-value="1" inactive-value="0"
                                       @change="recFun(scope.row)">
                            </el-switch>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="status_time" label="审核时间" width="160" sortable="custom">
                </el-table-column>  
                <el-table-column prop="zt" label="状态">
                	<template slot-scope="props">
                        <template v-if="props.row.r_status == 1">
                            <template v-if="props.row.height_status == 2">
                                <span class="admin_state1">已审核</span>
                            </template>
                            <template v-else-if="props.row.height_status == 1">
                                <span class="admin_state4">待审核</span>
                            </template>
                            <template v-else>未通过</template>
                        </template>
                        <template v-else-if="props.row.r_status == 2">
                            <template v-if="props.row.height_status == 2">
                                <span class="admin_state3">已锁定</span>
                            </template>
                            <template v-else-if="props.row.height_status == 1">
                                <span class="admin_state4">待审核</span>
                            </template>
                            <template v-else>未通过</template>
                        </template>
                        <template v-else-if="props.row.r_status == 3">
                            <template v-if="props.row.height_status == 1">
                                <span class="admin_state4">待审核</span>
                            </template>
                            <template v-else>未通过</template>
                        </template>
                        <template v-else>
                            <span class="admin_state4">待审核</span>
                        </template>
                	</template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="200">
                	<template slot-scope="scope">
                	    <div class="cz_button">
                			<el-button size="mini" @click="drawerFun('shenhe',scope.row)">审核</el-button>
                			<el-button size="mini" @click="handlePreview(scope)">预览</el-button>
                			<el-button type="danger" size="mini" @click="del(scope.row)">删除</el-button>
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
            <el-dialog title="猎头职位审核" :visible.sync="drawer3" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="500px">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核操作：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio-group v-model="drawerRadio">
                                <el-radio label="2">正常</el-radio>
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
					<el-button type="primary" @click="drawerSave" :disabled="submitLoading">确 定</el-button>
				</span>
            </el-dialog>
        </div>
        <div class="modluDrawer">
            <el-drawer title="简历预览" :append-to-body="true" :visible.sync="resumePreviewVisible" :destroy-on-close="true" size="530px">
                <resume_preview :id="info.id" :uid="info.uid"></resume_preview>
            </el-drawer>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    props:{
        reportarr:Object,
        typearr:Object,
        status: {type: String, default: ''}
    },
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',
            select: '',
            value: true,
            drawer3:false,
            drawerRadio:'',
            drawerTextarea:'',
            drawer: false,
            tableData: [],
            recArr:{
                1:"已推荐",
                2:"未推荐"
            },
            auditTimes:{
                1	:	'今天',
                3	:	'最近三天',
                7	:	'最近七天',
                15	:	'最近半月',
                30	:	'最近一个月'
            },
            statusArr:{
                2:'已审核',
                3:'未通过',
                4:'已锁定',
                1:'未审核'
            },
            uri: "m=user&c=",
            search:{
                status: this.status
            },
            total: 0,
            page: 1,
            idsArr: [],
            pageSize: 0,
            pageSizes:[],
            checkedAll:false,
            isIndeterminate: false,// checkbox 的不确定状态
            resumePreviewVisible: false,
            submitLoading: false,
            info:{},
            data:{},

            prevPage: 0

        }
    },
    created(){
        this.getList();
        this.ltNumFun();

    },
    components: {
        'resume_preview': httpVueLoader('../../../component/resume_preview.vue'),
    },
    methods: {
        selectAllBottom:function (value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择要删除的数据');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_height_user&a=del'
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
                message.error('请选择要操作的数据项');
                return
            }
            this.drawerFun('batchShenhe', null)
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
            let url = _this.uri + 'admin_height_user&a=index';
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
        // 弹窗
        drawerFun: function (type,detail) {
            this.drawerTextarea = '';
            this.drawerRadio = '';
            if (type != 'batchShenhe') {
                this.detail = detail;
                this.drawerTextarea = detail.statusbody;
                this.drawerRadio = detail.height_status;
            }
            this.drawerType = type;
            this.drawer3 = true;
        },
        del: function (detail) {
            let _this = this;
            let url = this.uri + 'admin_height_user&a=del';
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
        recFun: function (detail) {
            let _this = this;
            let url = this.uri + 'admin_height_user&a=recommend';
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
        handleCurrentChange(val) {
            console.log(`当前页: ${val}`);
        },
        drawerSave:function(){
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
            let url = this.uri + 'admin_height_user&a=status';
            if (!this.drawerRadio) {
                message.error('请选择认证操作!');
                return;
            }
            _this.submitLoading = true;
            httpPost(url, sendData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg, _this.getList());
                } else {
                    message.error(res.msg);
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                _this.submitLoading = false;
            });
            this.drawer3 = false;
        },
        handlePreview(scope) {
            this.info = scope.row;
            this.resumePreviewVisible = true;
        },
        ltNumFun:function(){
            let _this = this;
            let url = _this.uri + 'admin_height_user&a=ltjobNum';
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
    },
};
</script>
<style scoped></style>