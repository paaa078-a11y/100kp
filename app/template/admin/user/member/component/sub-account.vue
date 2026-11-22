<template>
	<div class="moduleElHight">
		<div class="moduleSeachbig">

			<div class="tableSeachInpt tableSeachInptsmall" >
                <el-select v-model="search.status" size="small" slot="prepend" placeholder="用户状态" clearable @change="doUserQuery">
                    <el-option label="正常" value="1"></el-option>
                    <el-option label="锁定" value="2"></el-option>
                </el-select>
			</div>
			<div class="tableSeachInpt tableSeachInptsmall" >
                <el-select v-model="search.ltime" size="small" slot="prepend" placeholder="最近登录" clearable @change="doUserQuery">
                    <el-option v-for="(item,key) in time" :key="key" :label="item.label" :value="item.value"></el-option>
                </el-select>
			</div>
			<div class="tableSeachInpt tableSeachInptsmall" >
                <el-select v-model="search.ctime" size="small" slot="prepend" placeholder="创建时间" clearable @change="doUserQuery">
                    <el-option v-for="(item,key) in time" :key="key" :label="item.label" :value="item.value"></el-option>
                </el-select>
			</div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-input placeholder="请输入搜索内容" size="small" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select">
                	<el-select v-model="search.type" slot="prepend" placeholder="部门">
                		<el-option label="部门" value="1"></el-option>
                		<el-option label="用户名" value="2"></el-option>
                		<el-option label="用户ID" value="3"></el-option>
                	</el-select>
                </el-input>
            </div>
			<div class="tableSeachInpt">
				<el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
			</div>
		</div>

		<div class="admin_datatip">
            <i class="el-icon-document"></i>  数据统计：共 {{memNum.ajaxAll}} 条
            <span class="admin_datatip_n cp_n" @click="lockList">已锁定：{{memNum.ajaxLock}} 条</span>
            <span class="admin_datatip_n">搜索结果： {{total}} 条</span>
		</div>
		<div class="moduleElTable" :class="{ 'moduleElTableHig': tableHig }" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" ref="multipleTable" stripe @selection-change="selectChange" @sort-change="shortChange"
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading" :empty-text="dataText">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="uid" label="用户ID" width="110" sortable="custom"></el-table-column>
                <el-table-column prop="uname" label="子账户"></el-table-column>
                <el-table-column prop="username" label="主账户" min-width="230"></el-table-column>
                <el-table-column label="所属企业 " prop="comname" l min-width="230"  >
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <div class="yhm">{{props.row.comname }}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="部门" min-width="180"></el-table-column>
                <el-table-column prop="login_date_n" label="近期登录"  min-width="150"></el-table-column>
                <el-table-column prop="ctime_n" label="创建时间"  min-width="150"></el-table-column>

                <el-table-column prop="zt" label="状态" width="60" fixed="right">
                    <template slot-scope="props">
                        <div class="admin_state">
                            <span v-if="props.row.status == 1" class="admin_state1">正常</span>
                            <span v-else class="admin_state2">锁定</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="160" fixed="right" align="center">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="small " plain @click="resetPassword(scope.row)">重置密码</el-button>
                            <el-button type="danger" size="small " @click="del(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
		</div>
		<div class="modulePaging">
            <div></div>
            <div class="modulePagNum">
                <el-pagination :total="total" @current-change="userPageChange" :page-size="pageSize" @size-change="userPageSizeChange"
                               :page-sizes="pageSizes"
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
            loading: false,
            dataText: '数据加载中',

			input3: '',
			input: '',
			select: '',
			value: true,
			value1: '',
			checked: '',
			drawer: false,
			czuserdrawer: false,
			seachbutn: false,
			tableHig: true,
			currentPage4: 4,
			tableData: [],
            time: [{
                value: 1,
                label: "今天"
            }, {
                value: 3,
                label: "最近三天"
            }, {
                value: 7,
                label: "最近七天"
            }, {
                value: 15,
                label: "最近半月"
            }, {
                value: 30,
                label: "最近一个月"
            }],
            search:{
                ctime:'',
                ltime:'',
                status:'',
                type:'1',
                keyword:""
            },
            uri: "m=user&c=",
            total: 0,
            page: 1,
            idsArr: [],
            pageSizes:[],
            pageSize: 0,
            memNum:{},

            prevPage: 0
        }
	},
    created() {
        var that = this;
        let params = window.parent.homeapp.$route.params;
        let query = window.parent.homeapp.$route.query;
        
        if (!$.isEmptyObject(query.params)) {
            params = {...params,...query.params};
        }
        
        if (!$.isEmptyObject(params)) {
            delete params.activeName;
            this.getParams(params);
        }
        this.getList();
        this.shenheNumber();
    },
	mounted() {

	},
	methods: {
        shortChange(e) {
            let orderMap = {ascending: 'asc', descending: 'desc'}
            this.search.t = e.order ? e.prop : null;
            this.search.order = orderMap[e.order];
            this.getList();
        },
		getParams:function(params={},search=false){
			var that = this;
			for(let i in params){
				if(typeof that.search[i]!='undefined'){
					that.search[i] = params[i];
				}
			}
			if(search){
				this.getList();
			}
		},
        lockList:function(){
            this.search.status = '2';
            this.page = 1
            this.getList()
        },
        getList:function (){
            let _this = this;
            let url = _this.uri + 'admin_zzh&a=index';
            _this.search.page = this.page;
            _this.search.pageSize = this.pageSize;
            _this.loading = true;
            httpPost(url, _this.search, {hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.tableData = res.data.data;
                    _this.total = res.data.total;
                    _this.loading = false;
                    if(_this.prevPage != _this.page){
                        _this.prevPage = _this.page;
                        _this.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                    }
                    _this.pageSizes =res.data.pageSizes;
                    if (_this.tableData.length === 0) {
                        _this.dataText = "暂无数据";
                    }
                }
            })
        },
        doUserQuery() {
            this.page = 1
            this.getList()
        },
        userPageChange(val) {
            this.page = val
            this.getList()
        },
        userPageSizeChange(val) {
            this.pageSize = val
            this.getList()
        },
        selectChange:function (val) {
            this.idsArr = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.idsArr.push(item.uid);
                });
            }
        },
        // 重置密码
        resetPassword:function (params) {
            let username = params.username;
            let _this = this;
            let url = this.uri + 'admin_zzh&a=reset_pw';
			let msg = '确定要重置密码吗?';
			delConfirm(_this, params, function (params) {
			    httpPost(url, {uid: params.uid}).then(function(res) {
			        if (res.data.error > 0) {
			            message.error(res.data.msg);
			        } else {
			            message.success("用户："+username+" 密码已经重置为123456！", function () {
			                _this.getList();
			            });
			        }
			    })
			}, msg)
        },
        shenheNumber:function(){
            let _this = this;
            let url = this.uri + 'admin_zzh&a=getNum'
            httpPost(url, {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.memNum = res.data;
                }else {
                    message.error(res.msg);
                }
            })
        },
        del:function (detail) {
            let _this = this,
				params = {};
            let url = this.uri + 'admin_zzh&a=del';
			params.del = detail.uid;
			params.pid = detail.comid;
			let msg = '确定要删除?';
			delConfirm(_this, params, function (params) {
			    httpPost(url, params).then(function(res) {
			        if (res.data.error > 0) {
			            message.error(res.data.msg);
			        } else {
			            message.success(res.data.msg, function () {
			                _this.getList();
			            });
			        }
			    })
			}, msg)
        },
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择需要删除的用户!');
                return
            }
            let _this = this,
				params = {};
			params.del = ids;
            let url = this.uri + 'admin_zzh&a=del'
            let msg = '确定要删除?';
            delConfirm(_this, params, function (params) {
                httpPost(url, params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        message.success(res.data.msg, function () {
                            _this.getList();
                        });
                    }
                })
            }, msg);
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
       height: calc(100% - 135px) !important;
   }
   .tableSeachInptsmall .el-input{
       width: initial;
   }
   .tableSeachInptsmall .el-select{
   	   margin-right: 0px !important;
   }
   .el-input-group__prepend{
   	   background-color: #ffffff;
   	   padding: 0 0 0 20px;
   }
</style> 