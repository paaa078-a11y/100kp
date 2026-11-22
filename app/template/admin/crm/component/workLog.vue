<template>
	<div class="moduleElenAl">
		<div class="moduleSeachs">
			<div class="moduleElTabInpt">
				<div class="moduleInptList" v-if="tab != 1">
					<el-select size="small" v-model="search.crm_uid" placeholder="业务员" clearable @change="handleSearch">
						<el-option v-for="item in crmUserOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
					<el-input @keyup.enter.native="handleSearch" size="small" placeholder="请输入日志内容检索" v-model="search.keyword" clearable>
						<template slot="prepend">日志内容</template>
					</el-input>
				</div>
				<div class="moduleInptList">
					<el-date-picker size="small" style="width: 230px;" v-model="search.time" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="日志开始" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="handleSearch"></el-date-picker>
				</div>
				<div class="moduleInptList">
					<el-button type="primary" icon="el-icon-search" size="mini" @click="handleSearch">查询</el-button>
				</div>
			</div>
			<div class="moduleSeachButn" v-if="tab == 1">
				<el-button type="primary" icon="el-icon-document-add" size="mini" @click="addworklog">新建日志</el-button>
			</div>
		</div>
		<div class="moduleElTable">
			<el-table :data="tableData" border style="width: 100%" stripe
					  :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%"
					  @selection-change="handleSelectionChange" :default-sort="{prop: 'time_n', order: 'descending'}"
					  @sort-change='sortChange' ref="workLogTable" v-loading="loading" :empty-text="emptytext">
				<el-table-column type="selection" width="55"></el-table-column>
				<el-table-column prop="name" label="业务员" width="170"></el-table-column>
				<el-table-column prop="time_n" label="录入时间" width="170" sortable="custom" :sort-orders="['descending','ascending']"></el-table-column>
				<el-table-column prop="content" label="日志内容" min-width="200"></el-table-column>
				<el-table-column fixed="right" label="操作" width="130">
					<template slot-scope="scope">
						<div class="moduleElTaCaoz" style="display: initial;">
							<el-button size="mini" @click="delWorkLog(scope)" type="danger">删除</el-button>
						</div>
					</template>
				</el-table-column>
			</el-table>
		</div>
		<div class="modulePaging">
		<div style="margin-left: 10px;">
			<el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange"> 全选 </el-checkbox>
			<el-button size="mini" style="margin-left: 20px;" @click="delWorkLogSel">批量删除</el-button>
		</div>
		<div class="modulePagNum">
			<el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size.sync="pageSize" :page-sizes="pageSizes" layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
		</div>

		<!--写日志-->
		<el-dialog title="新建工作日志" width="40%" :visible.sync="worklog_show" :modal-append-to-body="false" append-to-body :close-on-click-modal="false">
			<crmnewworklog :key="timer" @close-update="closeAddworklog"></crmnewworklog>
		</el-dialog>

	</div>
	</div>
</template>

<script>
	module.exports = {
		props: {
			log_tab: Number,
			ywy: {
			    type: Boolean,
				default: false
			}
		},
		watch: {
			log_tab: {
				handler(val) {
					this.tab = val;
				},
				deep: true,
				immediate: true
			}
		},
		data: function () {
			return {
				emptytext: '暂无数据',
				loading: false,
				tab: 0,
				search: {
					time: '',
					keyword: '',
					crm_uid: ''
				},
				crmUserOptions: [],
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

				tableData: [],
				total: 0,
                prevPage: 0,
				currentPage: 1,
				pageSize: 0,
				pageSizes: [],

				// 批量选择
				checkAll: false,
				isIndeterminate: false,
				selectedItem: [],

				timer:0,
				worklog_show:false,
			}
		},
		components: {
		    'crmnewworklog': httpVueLoader('./crmnewworklog.vue'),
		},
		mounted(){
			this.getStaticData();
			this.getWorkLog();
		},
		methods: {
			getWorkLog() {
				var that = this;
				var params = JSON.parse(JSON.stringify(this.search));
				params.pageSize = that.pageSize;
				params.page = that.currentPage;
				params.tab = that.tab;
				that.loading = true;
				that.emptytext = "数据加载中";
				let url = ''
				if (this.ywy == true) {
                    url = 'm=crm&c=workLogYwy&a=getWorkLog'
				} else {
				    url = 'm=crm&c=workLog&a=getWorkLog'
				}
				httpPost(url, params).then(function (res) {
					let data = res.data.data;
					that.tableData = data.list;
					that.total = data.total;
					that.pageSize = parseInt(data.pageSize);
					that.pageSizes = data.pageSizes;
                    if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        that.$refs.workLogTable.bodyWrapper.scrollTop = 0;
                    }
					that.loading = false;
					if (that.tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
				}).catch(function (error) {
					console.log(error);
				})
			},
			getStaticData:function(){
				var that = this
				let params = {};
                let url = ''
                if (this.ywy == true) {
                    url = 'm=crm&c=workLogYwy&a=getWorkLogStaticData'
                } else {
                    url = 'm=crm&c=workLog&a=getWorkLogStaticData'
                }
				httpPost(url,params,{hideloading:true}).then((result) => {
					var res = result.data;
					if (res.error == 0) {
						that.crmUserOptions = [];
						res.data.crmUser.forEach(function (item) {
							that.crmUserOptions.push({value: item.uid, label: item.name})
						});
					}
				});
			},
			sortChange: function (e) {
				this.search.t = e.prop;
				if(e.prop == 'time_n'){
					this.search.t = 'ctime';
				}
				this.search.order = e.order == 'ascending' ? 'asc' : 'desc';
				this.handleSearch();
			},
			handleSearch: function () {
				this.currentPage = 1
				this.getWorkLog()
			},
			handleSelectionChange(val) {
				this.selectedItem = val;
				if (this.selectedItem.length == 0) {
					this.isIndeterminate = false;
					this.checkAll = false;
				} else {
					if (this.selectedItem.length == this.tableData.length) {
						this.isIndeterminate = false;
						this.checkAll = true;
					} else {
						this.isIndeterminate = true;
						this.checkAll = false;
					}
				}
			},
			handleCheckAllChange(val) {
				val ? this.$refs.workLogTable.toggleAllSelection() : this.$refs.workLogTable.clearSelection();
			},
			delWorkLog(scope, isMore) {
				var that = this;
				let name = '';
				let idArr = [], nameArr = [];
				let params = {};
				if (isMore) {
					this.selectedItem.forEach((item) => {

						idArr.push(item.id);
						if (nameArr.indexOf(item.name) == -1) {
							nameArr.push(item.name);
						}
					});
					name = '（' + nameArr.join('，') + '）';
					params.id = idArr;
				} else {
					name = '（' + scope.row.name + '）';
					params.id = scope.row.id;
				}
				if (that.tab == 1){
					delConfirm(this, params, this.delete, '您确定要删除工作日志吗？');
				}else{
					delConfirm(this, params, this.delete, '您确定要删除'+name+'工作日志吗？');
				}
			},
			delWorkLogSel: function () {
				var that = this;
				if (!that.selectedItem.length) {
					message.error('请选择要删除的工作日志数据！');
					return;
				}
				this.delWorkLog(null, true);
			},
			delete(params){
				var self = this;
                let url = ''
                if (this.ywy == true) {
                    url = 'm=crm&c=workLogYwy&a=delWorkLog'
                } else {
                    url = 'm=crm&c=workLog&a=delWorkLog'
                }
				httpPost(url, params).then(function (response) {
					let res = response.data;
					if (res.error == 0) {
						message.success(res.msg, function () {
							self.getWorkLog();
						});
					} else {
						message.error(res.msg);
					}
				}).catch(function (error) {
					console.log(error);
				})
			},
			handleSizeChange(val) {
				this.pageSize = val;
				this.getWorkLog();
			},
			handleCurrentChange(val) {
				this.currentPage = val;
				this.getWorkLog();
			},
			addworklog:function(){
				this.timer = new Date().getTime();
				this.worklog_show = true;
			},
			closeAddworklog:function(){
				this.worklog_show = false;
				this.handleSearch();
			},
		}
	};
</script>
<style scoped>
	.moduleInptList .el-select .el-input {
		width: 140px;
	}
</style>