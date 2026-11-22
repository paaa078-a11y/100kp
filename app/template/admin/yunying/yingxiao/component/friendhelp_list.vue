<template>
	<div class="moduleElHight">
		<div class="moduleSeachbig">
			<div class="tableSeakusydsg" style="padding: 2px 0">
			    <el-input v-model="searchForm.keyword" placeholder="请输入你要搜索的关键字" class="input-with-select" size="small" prefix-icon="el-icon-search" clearable>
			        <el-select slot="prepend" v-model="searchForm.typeca" size="small" placeholder="企业名称">
			            <el-option label="企业名称" :value="1"></el-option>
			            <el-option label="企业ID" :value="2"></el-option>
			        </el-select>
			    </el-input>
			</div>
			<div class="tableSeachInpt tableSeachInptsmalltwo">
				<el-date-picker v-model="daterange" type="daterange" range-separator="至" start-placeholder="开始日期"
					end-placeholder="结束日期" size="samll" @change="search">
				</el-date-picker>
			</div>
			<div v-for="(searchItem, searchIndex) in searchList" :key="searchIndex" class="tableSeachInpt tableSeachInptsmall">
				<el-select v-model="searchForm[searchItem.param]" size="small" slot="prepend" :clearable="true"
					:placeholder="searchItem.name" @change="search">
					<el-option v-for="(searchLabel, searchValue) in searchItem.value" :key="searchValue" :label="searchLabel"
						:value="searchValue"></el-option>
				</el-select>
			</div>

			<div class="tableSeachInpt">
				<el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
			</div>
		</div>

		<div class="admin_datatip">
			<i class="el-icon-document"></i> 数据统计：共 <span @click="getAll">{{ totalAll }}</span> 条
			<span class="admin_datatip_n">助力人数：</span>
			<span @click="getHelp">{{ totalLogAll }} 条</span>
			<span class="admin_datatip_n">搜索结果： {{ total }} 条</span>
		</div>
		<div class="moduleElTable moduleElTablTwo" style="height: calc(100% - 136px);">
			<el-table :data="list" border style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
					  @sort-change="sortChange"
				:header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
				<template slot="empty">
                    <p>{{dataText}}</p>
                </template>
				<el-table-column type="selection" width="55">
				</el-table-column>
				<el-table-column prop="id" label="编号" width="80" sortable="custom">
				</el-table-column>
				<el-table-column prop="comname" label="企业名称" width="240">
				</el-table-column>
				<el-table-column prop="stime_n" label="开始时间" width="180"> </el-table-column>
				<el-table-column prop="etime_n" label="结束时间" width="180"> </el-table-column>
				<el-table-column label="助力人数" width="110" show-overflow-tooltip>
					<template slot-scope="scope">
						<div style=" padding-top: 10px;" @click="openLog(scope.row)">
							<el-badge v-if="scope.row.zlnum > 0" :value="scope.row.zlnum" :max="99" class="item">
								查看
							</el-badge>
							<span v-else>查看</span>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="任务权益">
					<template slot-scope="scope">
						<div v-if="checkPackage(scope.row.package)">
							任务权益：
							<span v-for="(pack,key) in scope.row.package" :key="key">{{ pack.name }}：{{ pack.num }}</span>
						</div>
						<div v-if="checkPackage(scope.row.getpackage)">
							达标权益：
							<span v-for="(getpackage,key) in scope.row.getpackage" :key="key">{{ getpackage.name }}：{{ getpackage.num
							}}</span>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="任务状态" width="100">
					<template slot-scope="scope">
						<div class="admin_state">
							<span class="admin_state2" v-if="scope.row.task_status == 2">已结束</span>
							<span class="admin_state1" v-else>助力中</span>
						</div>
					</template>
				</el-table-column>

				<el-table-column fixed="right" label="操作" width="70">
					<template slot-scope="scope">
						<div class="cz_button">
							<el-button type="danger" size="small " @click="del(scope.$index)">删除</el-button>
						</div>
					</template>
				</el-table-column>
			</el-table>
		</div>
		<div class="modulePaging" style="padding-top: 5px;">
			<div class="modulecz modulePagButn">
				<el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate"
					@change="checkAll">全选</el-checkbox>
				<el-button @click="batch('del')" size="mini">批量删除</el-button>
			</div>
			<div class="modulePagNum">
				<el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
					:current-page="page" :page-sizes="pageSizes" :page-size="limit"
					layout="total, sizes, prev, pager, next, jumper" :total="total">
				</el-pagination>
			</div>
		</div>
		<div class="modluDrawer">
			<el-dialog :title="detail.comname" :visible.sync="dialogLog" :with-header="true" :modal-append-to-body="false"
				:show-close="true" width="600px">
				<div>
					<el-table :data="loglist" style="width: 100%" height="300px" v-loading="loading">
						<template slot="empty">
		                    <p>{{dataText}}</p>
		                </template>
						<el-table-column prop="id" label="序号" width="80">
						</el-table-column>
						<el-table-column label="头像" width="80">
							<template slot-scope="scope">
								<div class="moduleProps">
									<el-avatar :size="30" :src="scope.row.wxpic"></el-avatar>
								</div>
							</template>
						</el-table-column>
						<el-table-column prop="wxname" label="微信名称">
						</el-table-column>
						<el-table-column prop="time" label="助力时间">
						</el-table-column>
					</el-table>
				</div>
			</el-dialog>
		</div>
	</div>
</template>

<script>
module.exports = {
	data: function () {
		return {
			loading: false,
			dataText: '数据加载中',
			// 日期选择
			daterange: '',

			// 搜索筛选项
			searchList: [],
			searchForm: {
				typeca: 1
			},

			// 列表
			page: 1,
			limit: 0,
			list: [],
			totalAll: 0,
			totalLogAll: 0,
			total: 0,
			pageSizes: [],

			checkedAll: false, // 全选
			checkedAllIndeterminate: false,
			multipleSelection: [], // 多选值存储
			idArr: [],

			// 列表排序
			t: '',
			order: '',

			detail: {},

			// 助力人数查看
			dialogLog: false,
			loglist: [],
            prevPage:0
		}
	},

	mounted() {

	},
	created() {
		this.init();
	},
	methods: {
		init() {
			this.resetSearch();
			this.getSum();
			this.search();
			this.getBaseData();
		},

		getSum() {
			let that = this;

			httpPost('m=yunying&c=yingxiao_friendhelp&a=helpSum', {}, {hideloading: true}).then(function (response) {
				let res = response.data,
					data = res.data;

				that.totalAll = data.helpall;
				that.totalLogAll = data.logall;
			})
		},

		getAll() {
			this.resetSearch();
			this.search();
		},

		getHelp() {
			this.resetSearch();
			this.searchForm.hashelp = 1;
			this.search();
		},

		resetSearch() {
			this.searchForm = {
				typeca: 1
			};
			this.limit = 0;
		},

		handleSizeChange(val) {
			this.limit = val;
			this.getList();
		},
		handleCurrentChange(val) {
			this.page = val;
			this.getList();
		},
		sortChange(event) {
			this.t = event.order ? event.prop : '';
			this.order = event.order ? event.order == 'descending' ? 'desc' : 'asc' : '';
			this.search();
		},
		search() {
			this.page = 1;
			let daterange = this.daterange;
			if (daterange) {
				this.searchForm.time_start = formatDate(daterange[0]);
				this.searchForm.time_end = formatDate(daterange[1]);
			} else {
				this.searchForm.time_start = '';
				this.searchForm.time_end = '';
			}
			this.getList();
		},
		getList() {
			let that = this,
				searchForm = that.searchForm,
				params = {
					page: that.page,
					limit: that.limit,
					t: that.t,
					order: that.order,
				};
			that.loading = true;
			httpPost('m=yunying&c=yingxiao_friendhelp&a=list', { ...params, ...searchForm }, {hideloading: true}).then(function (response) {
				let res = response.data,
					data = res.data;

				that.list = data.list;
				that.total = parseInt(data.total);
				that.pageSizes = data.page_sizes;
				if (that.limit === 0) {
					that.limit = parseInt(data.limit); // 取系统配置默认数量
				}
				if (that.page > data.page) {
					that.page = parseInt(data.page); // 最后一页被删除后，取最新的页数
				}
                if(that.prevPage != that.page){
                    that.prevPage = that.page;
                    that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                }
				that.loading = false;
				if (that.list.length === 0) {
                    that.dataText = "暂无数据";
                }
			})
		},
		getBaseData() {
			let _this = this;
			httpPost('m=yunying&c=yingxiao_friendhelp&a=list_base_data', {}, {hideloading: true}).then(function (response) {
				let res = response.data;
				_this.searchList = Object.freeze(res.data.search_list);
			}).catch(function (error) {
				console.log(error);
			});
		},
		// 批量操作
		handleSelectionChange(val) {
			if (val.length == 0) {
				this.checkedAll = false;
				this.checkedAllIndeterminate = false;
			} else {
				if (val.length === this.list.length) {
					this.checkedAll = true;
					this.checkedAllIndeterminate = false;
				} else {
					this.checkedAll = false;
					this.checkedAllIndeterminate = true;
				}
			}
			this.multipleSelection = val;
		},
		batch(type) {
			if (this.multipleSelection.length == 0) {
				message.error('请选择要删除的数据');
				return false;
			}

			let idArr = [];
			this.multipleSelection.forEach(function (item) {
				idArr.push(item.id);
			})
			this.idArr = idArr;

			if (type == 'del') {
				this.del();
			}
		},
		checkAll(val) {
			val ? this.checkedAllIndeterminate = false : '';
			this.$refs.multipleTable.toggleAllSelection();
		},

		del(idx) {
			let that = this,
				params = {},
				msg = '';

			if (typeof idx == 'undefined') { // 批量删除
				params.del = this.idArr;
				msg = '你确定要删除选中项吗？';
			} else {// 单个删除
				params.id = that.list[idx].id;
				msg = '你确定要删除当前项吗？';
			}

			delConfirm(this, params, function (params) {
				httpPost('m=yunying&c=yingxiao_friendhelp&a=del', params).then(function (res) {
					if (res.data.error > 0) {
						message.error(res.data.msg);
					} else {
						message.success(res.data.msg, function () {
							that.$refs.multipleTable.clearSelection();
							that.getList();
							that.getSum();
						});
					}
				})
			}, msg)
		},

		checkPackage(data) {
			if (isArray(data) && data.length == 0) { // 如果是个数组，说明没数据
				return false;
			} else {
				return true;
			}
		},

		openLog(data) {
			let that = this;

			this.detail = data;
			that.loading = true;
			httpPost('m=yunying&c=yingxiao_friendhelp&a=loglist', { id: data.id }).then(function (response) {
				let res = response.data,
					data = res.data;

				that.loglist = data.list;

				that.dialogLog = true;
				that.loading = false;
				if (that.loglist.length === 0) {
                    that.dataText = "暂无数据";
                }
			})
		},
	},
};
</script>
<style scoped></style>