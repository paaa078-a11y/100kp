<template>
    <div class="moduleElHight">
        <div class="moduleSeachbig">
            <div class="tableSeachInpt tableSeachInptsmalltwo">
                <el-date-picker v-model="search.time" type="daterange" range-separator="至" start-placeholder="开始日期"
                                value-format="yyyy-MM-dd" end-placeholder="结束日期" size="mini" @change="doUserQuery">
                </el-date-picker>
            </div>
			<div class="tableSeachInpt tableSeachInptsmall" style="padding: 2px 0;">
				<el-input placeholder="请输入你要搜索的关键字" @keyup.enter.native="doUserQuery" size="small" v-model="search.keyword" class="input-with-select" clearable>
					<el-select v-model="search.type" slot="prepend" placeholder="用户名">
						<el-option label="用户名" value="1"></el-option>
					</el-select>
				</el-input>
			</div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="search.usertype" size="small" slot="prepend" placeholder="用户类型" clearable @change="doUserQuery">
                    <el-option label="个人用户" value="1"></el-option>
                    <el-option label="企业用户" value="2"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt tableSeachInptsmall">
                <el-select v-model="search.times" size="small" slot="prepend" placeholder="行为日期" clearable @change="doUserQuery">
                    <el-option v-for="(item,key) in timeArr" :key="key" :label="item.label" :value="item.value"></el-option>
                </el-select>
            </div>
            <div class="tableSeachInpt">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
            </div>
        </div>

        <div class="moduleElTable" :class="{ 'moduleElTableHig': tableHig }"
             style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe @selection-change="selectChange" ref="multipleTable"
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" @sort-change="shortChange" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="uid" label="用户ID" width="110" sortable="custom"></el-table-column>
                <el-table-column prop="username" label="名称/用户名" width="150">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <div class="username">{{ props.row.username }}</div>
                            <span class="yhm"> {{ props.row.usertype_n }}用户</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="name_n" label="名称" width="260"></el-table-column>
                <el-table-column label="行为事件/行为链接">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <div class="username">{{ props.row.action_name }}</div>
                            <span class="yhm"> {{ props.row.uri }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="IP/来源" width="140">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <div class="username">{{ props.row.ip }}</div>
                            <span class=" "> {{ props.row.model_n }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="time_n" label="行为时间" width="210"></el-table-column>
                <el-table-column label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" @click="fenxiFun(scope.row)">分析</el-button>
                            <el-button type="danger" size="mini" @click="del(scope.row)">删除
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="batchDel" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination :total="total" @current-change="userPageChange" :page-size="pageSize"
                               :page-sizes="pageSizes" @size-change="handleSizeChange"
                               :current-page.sync="page" layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-drawer title="行为分析" :visible.sync="fenxidrawer" :modal-append-to-body="false" size="80%">
                <div class="xingweiConts">
                    <div class="xingweTips">
                        <span>说明：以下统计仅为对应动作触发次数，不代表实际操作成功，如：投递简历 20次，代表该用户点击简历投递20次，实际简历投递数以职位记录中简历投递记录为准</span>
                    </div>
                    <div class="xingweTitle">
                        <span>首次活跃 {{dataCount.startlog?dataCount.startlog:''}}</span>
                    </div>
                    <div class="xingweTitle">
                        <span>用户信息 {{dataCount.user_info?dataCount.user_info:''}}</span>
                    </div>
                    <div class="xingweiDatas">
                        <div class="xingweiDaComs" v-if="fenxiDetail.usertype == '1'">
                            <div class="xingweiDaTite">
                                <span>求职者数据</span>
                            </div>
                            <div class="xingweiDaCis">
                                <div>
                                    <b>0</b>
                                    <span>累计点击次数</span>
                                </div>
                            </div>
                            <div class="xingweiDaList">
                                <div>搜索职位<span>{{dataCount.searchjob?dataCount.searchjob:0}}</span>次</div>
                                <div>浏览职位<span>{{dataCount.lookjob?dataCount.lookjob:0}}</span>次</div>
                                <div>投递简历<span>{{dataCount.sqjob?dataCount.sqjob:0}}</span>次</div>
                                <div>聊天沟通<span>{{dataCount.chat?dataCount.chat:0}}</span>次</div>
                            </div>
                        </div>
                        <div class="xingweiDaComs"   v-if="fenxiDetail.usertype == '2'">
                            <div class="xingweiDaTite">
                                <span>企业数据</span>
                            </div>
                            <div class="xingweiDaCis">
                                <div>
                                    <b>0</b>
                                    <span>累计点击次数</span>
                                </div>
                            </div>
                            <div class="xingweiDaList">
                                <div>浏览简历<span>{{dataCount.lookresume?dataCount.lookresume:0}}</span>次</div>
                                <div>邀请面试<span>{{dataCount.inviteresume?dataCount.inviteresume:0}}</span>次</div>
                                <div>聊天沟通<span>{{dataCount.chat?dataCount.chat:0}}</span>次</div>
                                <div>下载简历<span>{{dataCount.downresume?dataCount.downresume:0}}</span>次</div>
                                <div>发布修改职位<span>{{dataCount.addjob?dataCount.addjob:0}}</span>次</div>
                                <div>推广服务<span>{{dataCount.promote?dataCount.promote:0}}</span>次</div>
                                <div>浏览套餐/增值包<span>{{dataCount.pay?dataCount.pay:0}}</span>次</div>
                            </div>
                        </div>
                        <div class="xingweiDaTime">
                            <div class="xingweiDaTite">
                                <span>访问时间轴</span>
                            </div>
                            <div class="xingweiToubu">
                                <div class="xingweiFwtim">
                                    <span>访问时间：</span>
                                </div>
                                <div class="xingweiFwTips">
                                    <span :class="activeClass == '2'? 'fwCur':''" @click="getLogFun('2')">昨日</span>
                                    <span :class="activeClass == '1'? 'fwCur':''" @click="getLogFun('1')">今日</span>
                                    <span :class="activeClass == '3'? 'fwCur':''" @click="getLogFun('3')">3天内</span>
                                    <span :class="activeClass == '4'? 'fwCur':''" @click="getLogFun('4')">1周内</span>
                                </div>
                                <div class="xingweiFwInpt">
                                    <el-date-picker v-model="value1" type="daterange" size="small" range-separator="至"
                                                    start-placeholder="开始日期" @change="timeChange" value-format="yyyy-MM-dd"
                                                    end-placeholder="结束日期">
                                    </el-date-picker>
                                </div>
                            </div>
                            <div class="xingweiBuzous">
                                <el-timeline :reverse="reverseone">
                                    <el-timeline-item
                                            v-for="(activity, index) in logList"
                                            :key="index"
                                            :timestamp="activity.timehis_n + '('+activity.timeymd_n+')['+activity.ip+']'">
                                        {{ activity.info }}
                                    </el-timeline-item>
                                </el-timeline>
                                <template v-if="pageCode == '2'">
                                    <template v-if="logList.length>0">没有更多了</template>
                                </template>
                                <template v-if="pageCode == '1'">
                                    <h3 @click="getloglist">加载更多...</h3>
                                </template>
                            </div>
                        </div>
                    </div>
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
            checkedAll:false,
            uri: "m=user&c=",
            total: 0,
            page: 1,
            value1:'',
            times:'',
            idsArr: [],
            pageSize: 0,
            input: '',
            select: '',
            value: true,
            fenxidrawer: false,
            seachbutn: false,
            tableHig: true,
            reverseone: true,
            search: {
                time: '',
                type: '1',
                keyword: '',
                usertype: '',
            },
            tableData: [],
            userType: [
                {
                    value: 1,
                    label: "个人用户"
                }, {
                    value: 2,
                    label: "企业用户"
                }
            ],
            timeArr: [{
                value: 1,
                label: "今天"
            }, {
                value: 2,
                label: "昨天"
            }, {
                value: 3,
                label: "最近三天"
            }, {
                value: 7,
                label: "最近七天"
            }],
            // 弹窗内容
            activeClass:'',
            fenxiDetail:{},
            dataCount:{},
            logList:[],
            pagenav:0,
            pageCode:'',
            xialaStatus:true,
            pageSizes:[],

            prevPage: 0
        }
    },
    created() {
        this.getList();
    },
    methods: {
		shortChange(e) {
			let orderMap = {ascending: 'asc', descending: 'desc'}
			this.search.t = e.prop;
			this.search.order = orderMap[e.order];
			this.page = 1;
			this.getList();
		},
        selectChange: function (val) {
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
        getList: function () {
            let _this = this;
            let url = _this.uri + 'admin_weblog&a=index';
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
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择需要删除的用户');
                return
            }
            let _this = this,
				params = {};
			params.del = ids;
            let url = this.uri + 'admin_weblog&a=del'
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
        del: function (param) {
            let _this = this,
				params = {};
			params.del = param.id;
            let url = this.uri + 'admin_weblog&a=del';
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
        fenxiFun:function(row){
            this.fenxiDetail = row;
            this.getLogFun('1');
            // 清空列表数据
            this.logList = [];
            this.pagenav = '';
            this.pageCode ='';
            this.value1='';
            this.times = '';
            this.fenxidrawer = true;
        },
        getLogFun(type){
            this.activeClass = type;

            this.value1 = '';
            this.times = '';

            this.getloglist()
            this.getlogtj()
        },
        getloglist:function () {
            let _this = this;
            let url = this.uri + 'admin_weblog&a=getloglist';
            httpPost(url, {uid: _this.fenxiDetail.uid,time:_this.activeClass,pagenav:_this.pagenav,times:_this.times}).then(function (response) {
                let res = response.data;
                if (res.error == 0){
                    _this.pageCode = res.data.code;
                    if (res.data.code == 1){
                        _this.pagenav = res.data.pagenav;
                        _this.logList = _this.logList.concat(res.data.list)
                    }
                }
            })
        },
        getlogtj:function () {
            let _this = this;
            let url = this.uri + 'admin_weblog&a=getlogtj';
            if (_this.times){
                this.activeClass ='';
            }
            httpPost(url, {uid: _this.fenxiDetail.uid,usertype:_this.fenxiDetail.usertype,time:_this.activeClass,times:_this.times}).then(function (response) {
                let res = response.data;
                if (res.error==0){
                    _this.dataCount = res.data;
                }
            })
        },
        timeChange:function () {
            this.times = this.value1.join(' ~ ');
            this.activeClass = '';
            this.pagenav = 0;
            this.logList = [];
            this.getloglist()
            this.getlogtj()
        },
        // 全选
        selectAllBottom(value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
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