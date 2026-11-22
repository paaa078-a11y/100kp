<template>
	<div class="moduleElenAl">
		<div class="moduleSeachs" style="padding: 0 0 8px 0;">
			<div class="moduleElTabInpt">
				<div class="moduleInptList" v-if="tab != 1">
					<el-select size="small" v-model="search.crm_uid" placeholder="业务员" clearable @change="handleSearch">
						<el-option v-for="item in crmUserOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
					<el-select size="small" v-model="search.day" placeholder="任务时间" clearable @change="handleSearch">
						<el-option v-for="item in dayOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
					<el-select size="small" v-model="search.status" placeholder="任务状态" clearable @change="handleSearch">
						<el-option v-for="item in statusOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
					<el-select size="small" v-model="search.type" placeholder="任务类型" clearable @change="handleSearch">
						<el-option v-for="item in typeOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
					<el-input size="small" @keyup.enter.native="handleSearch" placeholder="请输入检索关键词" v-model="search.keyword" class="input-with-select" style="margin-right: 8px; width: 340px;" clearable>
						<el-select v-model="search.stype" slot="prepend" placeholder="请选择">
							<el-option label="客户名称" value="1"></el-option>
							<el-option label="任务描述" value="2"></el-option>
						</el-select>
					</el-input>
				</div>
				<div class="moduleInptList">
					<el-button type="primary" icon="el-icon-search" size="mini" @click="handleSearch">查询</el-button>
				</div>
				<div class="moduleInptList">
					<el-button type="primary" icon="el-icon-document-add" size="mini" @click="addTask">新建任务</el-button>
				</div>
			</div>
		</div>
		<div class="admin_datatip" style="padding: 6px 10px; margin: 0;">
			<i class="el-icon-document"></i>
			总数：{{ allNum }}
			<span class="admin_datatip_n">进行中：{{ status1Num }}</span>
			<span class="admin_datatip_n">已完成：{{ status2Num }}</span>
			<span class="admin_datatip_n">未完成：{{ status3Num }}</span>
			<span class="admin_datatip_n">已取消：{{ status4Num }}</span>
			<span class="admin_datatip_n">搜索结果：{{ total }}</span>
		</div>
		<div class="moduleElTable" :style="ywy == true ? 'padding: 0; width: 100%; height: calc(100% - 190px);' : 'padding: 0; width: 100%; height: calc(100% - 148px);'">
			<el-table :data="tableData" border style="width: 100%" stripe :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
				@selection-change="handleSelectionChange" :default-sort="{ prop: 'id', order: 'descending' }"
				@sort-change='sortChange' ref="taskListTable" v-loading="loading" :empty-text="emptytext">
				<el-table-column type="selection" width="55"></el-table-column>
				<el-table-column label="客户信息" min-width="180">
					<template slot-scope="props">
						<div class="moduleProps">
							<el-link type="text" @click="openComInfo(props)">{{ props.row.name }}</el-link>
							<div class="gsd ">
								<span v-if="props.row.crm_uid > 0">{{ props.row.crm_name }}</span>
								<span v-else>已释放<el-tooltip class="item" effect="dark" :content="'前业务员：'+props.row.pcrmname+'；释放时间：'+props.row.sf_time" placement="right"><i class="el-icon-info" style="margin-left: 4px;"></i></el-tooltip></span>
							</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="任务描述" min-width="300">
					<template slot-scope="props">

						<div class=""><span style="color: #f00 ;" v-html="props.row.content"></span> </div>
						<div class="" style="display: flex;padding-top:5px;"> <el-tag type="info" size="mini" >{{ props.row.type_n }}</el-tag>
								<div class="gsd " style="margin-left: 10px;">
									<span v-if="props.row.auid == props.row.uid"><span style="color:# ;">{{ props.row.aname }}</span> 于 <span>{{ props.row.ctime_n }}</span>添加</span>
									<span v-else><span style="color:   ;">{{ props.row.aname }}</span> 于 <span>{{ props.row.ctime_n }}</span>发布</span>
								</div>
									</div>
					</template>
				</el-table-column>

				<el-table-column prop=" " label="执行者/时间/反馈" min-width="220" sortable="custom">
					<template slot-scope="props">
						<span > {{ props.row.cname }} </span>
							<div class="gsd "><span v-if="props.row.stime" style="display: flex;">{{ props.row.stime_n }}</span></div><div class="moduleProps" v-if="props.row.status == '1'">
							<el-button type="text" @click="statusTask(props.row.id)"><i class="el-icon-edit el-icon--right"></i>反馈</el-button>
						</div>
						<div class="moduleProps" v-else>
							<span  class="gsd ">反馈：{{ props.row.reason }} -{{ props.row.etime_n }}</span>

						</div>
					</template>
				</el-table-column>
				<el-table-column prop=" " label="任务状态" min-width="80"  >
					<template slot-scope="props">
				 <div class="">


				 <el-tag size="mini" v-if="props.row.status == 1">{{ props.row.status_n }}</el-tag>
				 	<el-tag type="success" size="mini" v-else-if="props.row.status == 2">{{ props.row.status_n}}</el-tag>
				 	<el-tag type="danger" size="mini" v-else-if="props.row.status == 3">{{ props.row.status_n}}</el-tag>
				 	<el-tag type="warning" size="mini" v-else-if="props.row.status == 4">{{ props.row.status_n}}</el-tag>
				 	<el-tag type="danger" effect="light" size="mini" v-if="props.row.isExpire == 1"  >已过期</el-tag>

				 </div>
				 </template>
				 	</el-table-column>
				<el-table-column fixed="right" label="操作" width="160">
					<template slot-scope="scope">
						<div class="moduleElTaCaoz" style="display: initial;">
							<el-button size="mini" @click="openComInfo(scope)">管理</el-button>
							<el-button size="mini" @click="delTask(scope)" type="danger">删除</el-button>
						</div>
					</template>
				</el-table-column>
			</el-table>
		</div>

		<div class="modulePaging" style="padding: 0;">
			<div style="margin-left: 10px;">
				<el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange"> 全选</el-checkbox>
				<el-button size="mini" style="margin-left: 20px;" @click="delTaskSel">批量删除</el-button>
			</div>
			<div class="modulePagNum">
				<el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size.sync="pageSize" :page-sizes="pageSizes" layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
			</div>
		</div>

		<div id="recordVoice" class="none"></div>

		<!--弹窗：客户管理-->
		<div class="modluDrawer">
			<el-drawer title="客户管理" :visible.sync="comInfoDrawer" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="85%">
				<div class="modluDrawerKehuguanli">
				<com_info ref="comInfo" :cuid="cuid" v-if="comInfoDrawer"></com_info>
				</div>
			</el-drawer>
		</div>

		<el-dialog title="任务反馈" width="560px" :visible.sync="statusTask_show" :modal-append-to-body="false" append-to-body>
			<div class="drawerModlue" style="padding: 0;">
				<div class="drawerModInfo drawerModFanlid">
					<div class="hydialog_item" style="display: flex; align-items: center;">
						<span class="item_span">标记状态：</span>
						<div class="hydialModFrom">
							<el-radio v-model="statusTask_status" label="2">已完成</el-radio>
							<el-radio v-model="statusTask_status" label="3">未完成</el-radio>
							<el-radio v-model="statusTask_status" label="4">已取消</el-radio>
						</div>
					</div>
					<div class="hydialog_item" style="align-items: initial;">
						<span class="item_span">任务反馈：</span>
						<div class="hydialModFrom">
							<el-input type="textarea" v-model="statusTask_reason" :rows="5"></el-input>
						</div>
					</div>
					<div class="setBasicButn" style="border: none;">
						<el-button type="primary" size="medium" @click="setTaskStatus" :loading="statusTask_load">提交</el-button>
						<el-button type="info" size="medium" @click="statusTask_show = false">取消</el-button>
					</div>
				</div>
			</div>
		</el-dialog>

		<!--建任务-->
		<el-dialog title="新建任务" width="560px" :visible.sync="addTask_show" :modal-append-to-body="false" append-to-body :close-on-click-modal="false">
			<crmnewtask :key="timer" @close-update="closeAddTask" v-if="addTask_show"></crmnewtask>
		</el-dialog>

		<!-- 企业信息弹窗 -->
		<!--<el-dialog title="企业信息" :visible.sync="drawerCominfo" :with-header="true" :modal-append-to-body="false" :show-close="true" width="350px">
			<div v-if="comdetail">
				<div class="wxsettip_small ">客户名称</div>
				<el-input style="width: 300px;" placeholder="客户名称" v-model="comdetail.name" readonly></el-input>
				<div class="wxsettip_small ">会员套餐</div>
				<el-input style="width: 120px;" placeholder="会员套餐" id="externalCall" v-model="comdetail.ratingname" readonly></el-input>
				<el-input style="width: 175px;" placeholder="有效期" id="externalCall" v-model="comdetail.ratingtime" readonly></el-input>
				<div class="wxsettip_small ">所在城市</div>
				<el-input style="width: 300px;" placeholder="所在城市" id="externalCall" v-model="comdetail.cityname" readonly></el-input>
				<div class="wxsettip_small ">联系方式</div>
				<el-input style="width: 120px;" placeholder="联系人" id="externalCall" v-model="comdetail.linkman" readonly></el-input>
				<el-input style="width: 175px;" placeholder="联系电话" id="externalCall" v-model="comdetail.moblie" readonly></el-input>
			</div>
		</el-dialog>-->
	</div>
</template>

<script>
module.exports = {
	data: function () {
		return {
			emptytext: '暂无数据',
			loading: false,
			tab: 0,
			addTask_show: false,
			timer: 0,
			search: {
				crm_uid: '',
				day: '',
				status: '',
				type: '',
				stype: '1',
				keyword: ''
			},
			crmUserOptions: [],
			dayOptions: [
				{ value: '1', label: '今天' },
				{ value: '2', label: '明天' },
				{ value: '3', label: '后天' },
				{ value: '4', label: '一周内' }
			],
			statusOptions: [
				{ value: '1', label: '进行中' },
				{ value: '2', label: '已完成' },
				{ value: '3', label: '未完成' },
				{ value: '4', label: '已取消' }
			],
			typeOptions: [],

			tableData: [],
			total: 0,

			allNum: 0,
			status1Num: 0,
			status2Num: 0,
			status3Num: 0,
			status4Num: 0,
            prevPage: 0,
			currentPage: 1,
			pageSize: 0,
			pageSizes: [],

			// 批量选择
			checkAll: false,
			isIndeterminate: false,
			selectedItem: [],

			comInfoDrawer: false,

			statusTask_id: '',
			statusTask_show: false,
			statusTask_status: '',
			statusTask_reason: '',
			statusTask_load: false,

			drawerCominfo: false,
			comdetail: null,
			cuid: ''
		}
	},
	components: {
		'com_info': httpVueLoader('./comInfo.vue'),
		'crmnewtask': httpVueLoader('./crmnewtask.vue'),
	},
	props: {	//	接受父组件传参
		task_tab: Number,
		ywy: {
		    type: Boolean,
			default: false
		}
	},
	mounted() {	//	页面初始化
		this.getStaticData();
		if (this.tab == 1 || this.tab == 0){
			this.getTaskList();
		}
	},
	watch: {	//	监听值变化
		task_tab: {
			handler(val) {
				this.tab = val;
			},
			deep: true,
			immediate: true
		}
	},
	methods: {
		/*getComDetail: function (uid) {
			var that = this
			httpPost('m=crm&c=task&a=ComDetail', { uid: uid }).then(function (res) {
				that.comdetail = res.data.data
				that.drawerCominfo = true
			}).catch(function (error) {
				console.log(error);
			})
		},*/
		addTask: function () {
			this.timer = new Date().getTime();
			this.addTask_show = true;
		},
		closeAddTask: function () {

			this.getTaskList();
			this.addTask_show = false;
		},
		getTaskList() {
			var that = this;
			var params = JSON.parse(JSON.stringify(this.search));
			params.pageSize = that.pageSize;
			params.page = that.currentPage;
			params.tab = that.tab;
			that.loading = true;
			that.emptytext = "数据加载中";
			let url = ''
			if (this.ywy == true) {
			    url = 'm=crm&c=taskYwy&a=getTaskList'
			} else {
			    url = 'm=crm&c=task&a=getTaskList'
			}
			httpPost(url, params).then(function (res) {
				let data = res.data.data;
				that.tableData = data.list;
				that.total = data.total;

				that.pageSize = parseInt(data.pageSize);
				that.pageSizes = data.pageSizes;
                if(that.prevPage != that.currentPage){
                    that.prevPage = that.currentPage;
                    that.$refs.taskListTable.bodyWrapper.scrollTop = 0;
                }
				that.loading = false;
				if (that.tableData.length === 0) {
					that.emptytext = "暂无数据";
				}
			}).catch(function (error) {
				console.log(error);
			})
		},
		getStaticData:function(){
			var that = this
			let params = {};
			params.tab = that.tab;
            let url = ''
            if (this.ywy == true) {
                url = 'm=crm&c=taskYwy&a=getTaskStaticData'
            } else {
                url = 'm=crm&c=task&a=getTaskStaticData'
            }
			httpPost(url,params,{hideloading:true}).then((result) => {
				var res = result.data;
				if (res.error == 0) {
					that.allNum = res.data.allNum;
					that.status1Num = res.data.status1Num;
					that.status2Num = res.data.status2Num;
					that.status3Num = res.data.status3Num;
					that.status4Num = res.data.status4Num;
					that.crmUserOptions = [];
					res.data.crmUser.forEach(function (item) {
						that.crmUserOptions.push({ value: item.uid, label: item.name })
					});
					that.typeOptions = [];
					res.data.typeOption.forEach(function (item) {
						that.typeOptions.push({ value: item.id, label: item.name })
					});
				}
			});
		},
		sortChange: function (e) {
			this.search.t = e.prop;
			if (e.prop == 'stime_n') {
				this.search.t = 'stime'
			}
			this.search.order = e.order == 'ascending' ? 'asc' : 'desc';
			this.handleSearch();
		},
		handleSearch: function () {
			this.currentPage = 1
			this.getTaskList()
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
			val ? this.$refs.taskListTable.toggleAllSelection() : this.$refs.taskListTable.clearSelection();
		},
		delTask(scope, isMore) {
			var that = this;
			let name = '';
			let idArr = [], nameArr = [];
			let params = {};
			if (isMore) {
				this.selectedItem.forEach((item) => {

					idArr.push(item.id);
					if (nameArr.indexOf(item.cname) == -1) {
						nameArr.push(item.cname);
					}
				});
				name = '（' + nameArr.join('，') + '）';
				params.id = idArr;
			} else {

				name = '（' + scope.row.cname + '）';
				params.id = scope.row.id;
			}

			if (that.tab == 1) {
				delConfirm(this, params, this.delete, '您确定要删除任务信息吗？');
			} else {
				delConfirm(this, params, this.delete, '您确定要删除' + name + '任务信息吗？');
			}
		},
		delTaskSel: function () {
			var that = this;
			if (!that.selectedItem.length) {
				message.error('请选择要删除的任务信息！');
				return;
			}
			this.delTask(null, true);
		},
		delete(params) {
			var self = this;
            let url = ''
            if (this.ywy == true) {
                url = 'm=crm&c=taskYwy&a=delTask'
            } else {
                url = 'm=crm&c=task&a=delTask'
            }
			httpPost(url, params).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					message.success(res.msg, function () {
						self.getTaskList();
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
			this.getTaskList();
		},
		handleCurrentChange(val) {
			this.currentPage = val;
			this.getTaskList();
		},
		openComInfo: function (scope) {
			this.comInfoDrawer = true;
			this.cuid = scope.row.comid
		},
		statusTask: function (id) {
			this.statusTask_id = id;
			this.statusTask_show = true;
		},
		setTaskStatus: function () {
			var self = this;
			var params = {
				id: self.statusTask_id,
				status: self.statusTask_status,
				reason: self.statusTask_reason,
			};
			this.statusTask_load = true;
            let url = ''
            if (this.ywy == true) {
                url = 'm=crm&c=taskYwy&a=setStatus'
            } else {
                url = 'm=crm&c=task&a=setStatus'
            }
			httpPost(url, params).then((response) => {
				this.statusTask_load = false;
				this.statusTask_show = false;
				let res = response.data;
				if (res.error == 0) {
					message.success(res.msg, function () {
						self.getTaskList();
					});
				} else {
					message.error(res.msg);
				}
			}).catch(function (error) {
				console.log(error);
			})
		}
	},
};
</script>
<style>
	.admin_datatip{margin:0 12px;padding:6px 10px;color:#409eff;font-size:13px;background:#e8f4ff}
	.moduleElTable{overflow:hidden;position:relative;height:calc(100% - 130px);padding:0 12px}
	.yuYinBoxWt{width:15px;height:15px;background:url(../../images/bf.png) no-repeat;padding-left:18px}
	.yuYinBoxOnPlay{width:15px;height:15px;background:url(../../images/bfz.gif) no-repeat;padding-left:18px}
	.drawerModFanlid .hydialog_item{overflow:hidden;position:relative;display:flex;align-items:center;width:100%;padding:12px 0}
	.drawerModFanlid .hydialModFrom{overflow:hidden;position:relative;width:calc(100% - 80px)}
</style>