<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach"  style="margin-bottom: 8px; height: initial;">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-select size="small" v-model="search.crm_uid" placeholder="业务员" clearable @change="handleSearch">
                        <el-option v-for="item in crmUserOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList" style="padding-right: 0;">
                    <el-input size="small" @keyup.enter.native="handleSearch" placeholder="请输入检索关键词" v-model="search.keyword" class="input-with-select" style="margin-right: 8px; width: 340px;" clearable>
                        <el-select v-model="search.type" slot="prepend" placeholder="请选择">
                            <el-option label="客户名称" value="1"></el-option>
                            <el-option label="订单编号" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="moduleInptList">
                    <el-date-picker size="small" style="width: 230px;" v-model="search.time" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="申请开始" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="handleSearch"></el-date-picker>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="handleSearch">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable moduleElTablTwo">
            <el-table :data="tableData" border style="width: 100%" stripe :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" @selection-change="handleSelectionChange" @sort-change='sortChange' ref="crmOrdeTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="ID" width="80" sortable="custom"></el-table-column>
                <el-table-column prop=" " label="客户名称" min-width="260">
					  <template slot-scope="props">
						    <div>{{ props.row.comname }}</div>
							  <div class="gsd ">{{ props.row.aname }}</div>
					 </template>
				</el-table-column>
                <el-table-column label="订单详情" min-width="140">
                    <template slot-scope="props">
                        <div class=" ">

                            <div style="color:#f00;font-size:16px;">￥{{ props.row.order_price }}</div>
                            <div>类型：{{ props.row.type_n }}</div>

                        </div>
                    </template>
                </el-table-column>
				<el-table-column label="支付方式" min-width="170">
				    <template slot-scope="props">
				<div> {{ props.row.order_type_n }}</div>
				<div class="gsd ">单号{{ props.row.order_id }}</div>
				</template>
				  </el-table-column>

                <el-table-column label="支付状态" width="120">
                    <template slot-scope="props">
                        <div class=" " style="width: 72px;">
                            <el-tag size="mini" v-if="props.row.order_state == 1" style="width: 110px;">{{ props.row.order_state_n }}</el-tag>
                            <el-tag size="mini"  v-else-if="props.row.order_state == 2" type="success">{{ props.row.order_state_n }}</el-tag>
                            <el-tag size="mini"  v-else-if="props.row.order_state == 3" type="warning">{{ props.row.order_state_n }}</el-tag>
                            <el-tag size="mini"  v-else type="danger">{{ props.row.order_state_n }}</el-tag>
                        </div>
                    </template>
                </el-table-column>
				<el-table-column prop="order_remark" label="备注" min-width="220"></el-table-column>

                <el-table-column prop="time_n" label="申请时间" width="160" sortable="custom"></el-table-column>
                <el-table-column fixed="right" label="操作" width="150">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="approvalOrder(scope)">审批</el-button>
                            <el-button size="mini" type="danger" @click="delCrmOrder(scope)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div style="margin-left: 10px;">
                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange"> 全选 </el-checkbox>
                <el-button  size="mini" style="margin-left: 10px;" @click="delCrmOrderSel">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size.sync="pageSize" :page-sizes="pageSizes" layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
            </div>
        </div>
    </div>
</template>

<script>
    module.exports = {
        data: function () {
            return {
                emptytext: '暂无数据',
                loading: false,
                search: {
                    crm_uid: '',
                    type: '1',
                    keyword: '',
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

                currentPage: 1,
                prevPage: 0,
                pageSize: 0,
                pageSizes: [],

                // 批量选择
                checkAll: false,
                isIndeterminate: false,
                selectedItem: [],
            }
        },
        mounted() {
			this.getStaticData();
            this.getCrmOrderList();
        },
        methods: {
            getCrmOrderList() {
                var that = this;
                var params = JSON.parse(JSON.stringify(this.search));
                params.pageSize = that.pageSize;
                params.page = that.currentPage;
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=crm&c=order&a=getOrderList', params).then(function (res) {
                    let data = res.data.data;
                    that.tableData = data.list;
                    that.total = data.total;
                    that.pageSize = parseInt(data.pageSize);
                    that.pageSizes = data.pageSizes;
                    if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        that.$refs.crmOrdeTable.bodyWrapper.scrollTop = 0;
                    }
                    that.loading = false;
                    if (that.tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
			getStaticData(){
			    var that = this
				let params = {};
			    httpPost('m=crm&c=order&a=getOrderStaticData',params,{hideloading:true}).then((result) => {
					var res = result.data;
					if (res.error == 0) {
						var crmUser = res.data.crmUser;
						that.crmUserOptions = [];
						crmUser.forEach(function (item) {
						    that.crmUserOptions.push({value: item.uid, label: item.name})
						});

					}
			    });
			},
            sortChange: function (e) {
                this.search.t = e.prop;
                if(e.prop == 'time_n'){
                    this.search.t = 'order_time';
                }
                this.search.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.handleSearch();
            },
            handleSearch: function () {
                this.currentPage = 1
                this.getCrmOrderList()
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
                val ? this.$refs.crmOrdeTable.toggleAllSelection() : this.$refs.crmOrdeTable.clearSelection();
            },
            approvalOrder(scope) {
                var that = this;
                let name = '';
                let params = {};
                name = '（' + scope.row.comname + '）';
                params.id = scope.row.id;
                delConfirm(this, params, this.approval, '您确定要审批客户'+name+'订单记录吗？');
            },
            approval: function(params){
                var self = this;
                httpPost('m=crm&c=order&a=approval', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            self.getCrmOrderList();
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            delCrmOrder(scope, isMore) {
                var that = this;
                let name = '';
                let idArr = [], nameArr = [];
                let params = {};
                if (isMore) {
                    this.selectedItem.forEach((item) => {

                        idArr.push(item.id);
                        if (nameArr.indexOf(item.comname) == -1) {
                            nameArr.push(item.comname);
                        }
                    });
                    name = '（' + nameArr.join('，') + '）';
                    params.id = idArr;
                } else {

                    name = '（' + scope.row.comname + '）';
                    params.id = scope.row.id;
                }

                delConfirm(this, params, this.delete, '您确定要删除客户'+name+'订单记录吗？');
            },
            delCrmOrderSel: function () {
                var that = this;
                if (!that.selectedItem.length) {
                    message.error('请选择要删除的客户订单记录');
                    return;
                }
                this.delCrmOrder(null, true);
            },
            delete(params){
                var self = this;
                httpPost('m=crm&c=order&a=delCrmOrder', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            self.getCrmOrderList();
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            handleSizeChange(val) {
                console.log(`每页 ${val} 条`);
                this.getCrmOrderList();
            },
            handleCurrentChange(val) {
                this.currentPage = val
                console.log(`当前页: ${val}`);
                this.getCrmOrderList();
            }
        },
    };
</script>
<style scoped>
    .moduleElHight .moduleElTable{height: calc(100% - 80px)}
    .moduleInptList .el-select .el-input {width: 140px;}
</style>