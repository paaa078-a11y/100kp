<template>
    <div class="moduleElHight" style="padding: 0 10px;">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input placeholder="请输入检索关键词" @keyup.enter.native="handleSearch" v-model="search.keyword" class="input-with-select" style="margin-right: 8px; width: 340px;" clearable>
                        <el-select v-model="search.type" slot="prepend" placeholder="请选择">
                            <el-option label="客户名称" value="1"></el-option>
                            <el-option label="企业 UID" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable moduleElTablTwo">
            <el-table :data="tableData" border style="width: 100%" stripe :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" @selection-change="handleSelectionChange" @sort-change='sortChange' ref="crmOrdeTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="uid" label="UID" width="100" sortable="custom"></el-table-column>
                <el-table-column prop="name" label="客户名称" width="260"></el-table-column>
                <el-table-column prop="login_date_n" label="登录时间" width="160" sortable="custom"></el-table-column>
                <el-table-column prop="time_n" label="最近跟进" min-width="250" sortable="custom"></el-table-column>
                <el-table-column fixed="right" label="操作" width="150">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" type="danger" @click="shiftCom(scope)">转移</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div style="margin-left: 10px;">
                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange"> 全选 </el-checkbox>
                <el-button  size="mini" style="margin-left: 10px;" @click="shiftComSel" type="danger">转移</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size.sync="pageSize" :page-sizes="pageSizes" layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="转移客户" :visible.sync="shiftDrawer" :with-header="true" append-to-body :modal-append-to-body="false" :show-close="true" width="30%">

                <el-alert title="客户被转交后，原客户经理将不能拥有客户操作权限" type="warning" :closable="false"></el-alert>

                <div style="display: flex; flex-direction: column; margin-top: 16px;">
                    <span>客户经理：</span>
                    <el-select v-model="newCrmUid" placeholder="请选择客户经理" clearable style="margin: 16px 0;">
                        <el-option v-for="item in crmUserOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
                    </el-select>
                    <span>备注信息：</span>
                    <el-input type="textarea" :rows="4" placeholder="请输入内容" v-model="shiftRemark" style="margin-top: 16px;"></el-input>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="shiftDrawer = false">取 消</el-button>
                    <el-button type="primary" @click="shiftCustomer">确 定</el-button>
                </span>
            </el-dialog>
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
                    crm_uid: 0,
                    type: '1',
                    keyword: ''
                },
                crmUserOptions: [],
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

                shiftDrawer: false,
                shiftRemark: '',
                newCrmUid: '',
                customerUid: '',
            }
        },
		mounted() {
			this.getStaticData();
		},
        methods: {
			getStaticData(){
			    var that = this
				let params = {
					way:'shift',
					crm_uid:that.search.crm_uid
				};
			    httpPost('m=crm&c=salesman&a=getCustomerStaticData',params,{hideloading:true}).then((result) => {
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
            getCustomer() {
                var that = this;
                var params = JSON.parse(JSON.stringify(this.search));
                params.way = 'shift';
                params.pageSize = that.pageSize;
                params.page = that.currentPage;
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=crm&c=salesman&a=getCustomer', params).then(function (res) {
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
            sortChange: function (e) {
                this.search.t = e.prop;
                if(e.prop == 'login_date_n'){
                    this.search.t = 'login_date';
                }
                if(e.prop == 'time_n'){
                    this.search.t = 'f_time';
                }
                this.search.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.handleSearch();
            },
            handleSearch: function () {
                this.currentPage = 1
                this.getCustomer()
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
            shiftCom(scope, isMore) {
                var that = this;
                let name = '';
                let idArr = [], nameArr = [];
                let params = {};
                if (isMore) {
                    this.selectedItem.forEach((item) => {

                        idArr.push(item.uid);
                        if (nameArr.indexOf(item.name) == -1) {
                            nameArr.push(item.name);
                        }
                    });
                    name = '（' + nameArr.join('，') + '）';
                    params.id = idArr;
                } else {

                    name = '（' + scope.row.name + '）';
                    params.id = scope.row.uid;
                }

                delConfirm(this, params, this.shift, '您确定要转移客户'+name+'吗？');
            },
            shiftComSel: function () {
                var that = this;
                if (!that.selectedItem.length) {
                    message.error('请选择要转移的客户信息');
                    return;
                }
                this.shiftCom(null, true);
            },
            shift(params){

                let _this = this;
                _this.customerUid = params.id;
                _this.shiftDrawer = true;
            },
            shiftCustomer: function () {
                let _this = this;

                let params = {};
                params.uid = _this.search.crm_uid;

                params.comid = _this.customerUid;
                if (_this.newCrmUid == ''){

                    message.error('请选择接收转移客户的客户经理！');
                    return false;
                }else{
                    params.new_uid = _this.newCrmUid;
                }
                params.remark = _this.shiftRemark;
                httpPost('m=crm&c=salesman&a=shiftCustomer', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            _this.getCustomer();
                            _this.shiftDrawer = false;
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            handleSizeChange(val) {
                this.getCustomer();
            },
            handleCurrentChange(val) {
			    this.currentPage = val
                this.getCustomer();
            }
        },
    };
</script>
<style scoped>
    .moduleElHight .moduleElTable{height: calc(100% - 105px)}
    .moduleInptList .el-select .el-input {width: 140px;}
</style>