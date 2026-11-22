<template>
	<div :class="{moduleElenAl: true, cjAmountDrawer: drawer}">
		<div class="moduleSeachs">
			<div class="moduleElTabInpt">
				<div class="moduleInptList">
					<el-select size="small" v-model="search.crm_uid" placeholder="请选择业务员" clearable :disabled="source === 'jobtai'" @change="handleSearch">
						<el-option v-for="item in crmUserOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
					<el-date-picker size="small" style="width: 230px;" v-model="search.time" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="订单开始" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="handleSearch"></el-date-picker>
				</div>
				<div class="moduleInptList">
					<el-button type="primary" icon="el-icon-search" size="mini" @click="handleSearch">查询</el-button>
				</div>
			</div>
		</div>
		<div class="moduleElTable">
			<el-table ref="auditAssTable" :data="tableData" row-key="date" border stripe highlight-current-row :empty-text="emptytext" style="width: 100%" @row-click="handleRowClick" @expand-change="expandChange" height="100%" v-loading="loading">
				<el-table-column type="expand">
					<template slot-scope="scope">
						<el-table :data="scope.row.orderInfo" size="mini">
							<el-table-column prop="crm_name" label="业务员" align="center" width="200px;"></el-table-column>
							<el-table-column prop="order_price" label="金额" align="center" width="200px;">
								<template slot-scope="props"><span style="color: #ea240e;">￥{{props.row.order_price}}</span></template>
							</el-table-column>
							<el-table-column prop="time_n" label="时间节点" align="center" width="200px;"></el-table-column>
							<el-table-column prop="order_type_n" label="付款渠道" align="center" width="200px;"></el-table-column>
							<el-table-column prop="name" label="关联客户" align="center" width="200px;"></el-table-column>
							<el-table-column prop="order_remark" label="备注信息"></el-table-column>
						</el-table>
					</template>
				</el-table-column>
				<el-table-column prop="date" label="日期" width="200px;"></el-table-column>
				<el-table-column prop="revenue" label="收入">
					<template slot-scope="scope"><span style="color: #12ac7b;">￥{{scope.row.revenue}}</span></template>
				</el-table-column>
				<el-table-column label="操作"><el-button type="text">查看详情</el-button></el-table-column>
			</el-table>
		</div>
		<div class="modulePaging">
			<div class="modulePagNum">
				<el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
							   :current-page.sync="currentPage" :page-size.sync="pageSize" :page-sizes="pageSizes"
							   layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
			</div>
		</div>
	</div>
</template>

<script>
	module.exports = {
		props: {
            drawer: {
                type: Boolean, // 是否是抽屉方式打开组件，默认否
                default: false
            },
			source: { // 加载来源
				type: String,
				default: ''
			},
            source_data: { // 来源数据
                type: Object,
                default: null
            },
		},
		watch: {
            source_data: {
                handler(val, oldVal) {
                    this.sourceDataHandle();
                    this.handleSearch();
                }
            },
		},
		mounted(){
			this.getStaticData();
            this.sourceDataHandle();
            this.init()
		},
		data: function () {
			return {
				emptytext: '暂无数据',
				loading: false,
                search: {
                    crm_uid: '',
                    time: ''
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
                revenue: '',
                revenueAll: '',
                prevPage: 0,
                currentPage: 1,
                pageSize: 0,
                pageSizes: [],

                // 批量选择
                checkAll: false,
                isIndeterminate: false,
                selectedItem: [],
			}
		},
		methods: {
            sourceDataHandle() {
                let val = this.source_data;
                if (val) {
                    if (val.mtime) {
                        this.timeHandle(val.mtime);
                    }
                    if (val.crm_uid) {
                        this.search.crm_uid = val.crm_uid;
                    }
                }
            },
            timeHandle: function (date) {
                let now = new Date(date);
                // 获取当前月份的第一天
                let firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                // 获取当前月份的最后一天
                let lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                this.search.time = [firstDay.getTime(), lastDay.getTime()];
            },
			init: function(){
				let query = window.parent.homeapp.$route.query;

				if (!$.isEmptyObject(query)) {
					if(typeof query.crm_uid!='undefined'){
						this.search.crm_uid = query.crm_uid;
					}
					if(typeof query.mtime!='undefined'){
						this.timeHandle(query.mtime);
					}
				}

                this.getAuditList();
			},
            getAuditList() {
                var that = this;
                var params = JSON.parse(JSON.stringify(this.search));
                params.pageSize = that.pageSize;
                params.page = that.currentPage;
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=crm&c=auditAss&a=getAuditList', params).then(function (res) {
                    let data = res.data.data;
                    that.tableData = data.list;
                    that.total = data.total;
                    that.pageSize = parseInt(data.pageSize);
                    that.pageSizes = data.pageSizes;
                    if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        that.$refs.auditAssTable.bodyWrapper.scrollTop = 0;
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
				let params = {}
				httpPost('m=crm&c=auditAss&a=getAuditStaticData',params,{hideloading:true}).then((result) => {
					var res = result.data;
					if (res.error == 0) {
						var crmUser = res.data.crmUser;
						that.crmUserOptions = [];
						crmUser.forEach(function (item) {
						    that.crmUserOptions.push({ value: item.uid, label: item.name })
						});
					}
				});
			},
            handleSearch: function () {
                this.currentPage = 1
                this.getAuditList()
            },
            /** 点击主表格行，获取子表格明细 */
            expandChange(row) {
                this.$refs.auditAssTable.setCurrentRow(row)
            },
            // 点击主表行展开明细
            handleRowClick(row, event, column) {
                this.$refs.auditAssTable.toggleRowExpansion(row)
            },
            handleSizeChange(val) {
                this.pageSize = val;
                this.getAuditList()
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.getAuditList()
            },
		},
	};
</script>
<style scoped>
    .cjAmountDrawer {
        overflow: hidden;
        position: relative;
        width: 100%;
        height: 100%;
    }
	.cjAmountDrawer .modulePaging {
		width: 100%;
		padding: 0;
	}
    .moduleInptList .el-select{
        width: 160px;
    }
    .moduleInptList .el-select .el-input{
        width: initial;
    }
</style>