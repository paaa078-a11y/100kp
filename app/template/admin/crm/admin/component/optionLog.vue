<template>
    <div class="moduleElHight" style="padding: 0 10px;">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-select v-model="search.logType" placeholder="请选择操作类型" clearable @change="handleSearch">
                        <el-option v-for="item in logTypeOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select v-model="search.crm_uid" placeholder="请选择客户经理" clearable @change="handleSearch">
                        <el-option v-for="item in crmUserOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-input placeholder="请输入检索关键词" @keyup.enter.native="handleSearch" v-model="search.keyword" class="input-with-select" style="margin-right: 8px; width: 340px;" clearable>
                        <template slot="prepend">操作内容</template>
                    </el-input>
                </div>
                <div class="moduleInptList">
                    <el-date-picker v-model="search.time" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="行为开始日期" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="handleSearch"></el-date-picker>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable moduleElTablTwo">
            <el-table :data="tableData" border style="width: 100%" stripe :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" @selection-change="handleSelectionChange" @sort-change='sortChange' ref="crmOrdeTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" width="100" sortable="custom"></el-table-column>
                <el-table-column prop="aname" label="客户经理" width="120"></el-table-column>
                <el-table-column prop="type_n" label="操作类型" width="160"></el-table-column>
                <el-table-column prop="content" label="操作内容" min-width="250"></el-table-column>
                <el-table-column prop="time_n" label="操作时间" width="150" sortable="custom"></el-table-column>
                <el-table-column fixed="right" label="操作" width="150">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" type="danger" @click="delOptLog(scope)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div style="margin-left: 10px;">
                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange"> 全选 </el-checkbox>
                <el-button  size="mini" style="margin-left: 10px;" @click="delOptLogSel">批量删除</el-button>
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
                    logType: '',
                    crm_uid: '',
                    keyword: '',
                    time: ''
                },
                logTypeOptions: [],
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
                pageSize: 10,
                pageSizes: [],

                // 批量选择
                checkAll: false,
                isIndeterminate: false,
                selectedItem: [],
            }
        },
		mounted() {

			this.getStaticData();

		},
        methods: {
            getOptionList() {
                var that = this;
                var params = JSON.parse(JSON.stringify(this.search));
                params.pageSize = that.pageSize;
                params.page = that.currentPage;
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=crm&c=salesman&a=getOptionLog', params).then(function (res) {
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
			    httpPost('m=crm&c=salesman&a=getOptionStaticData',params,{hideloading:true}).then((result) => {
					var res = result.data;
					if (res.error == 0) {
						var crmUser = res.data.crmUser;
						var typeOptions = res.data.typeOptions;
						that.crmUserOptions = [];
						crmUser.forEach(function (item) {
						    that.crmUserOptions.push({value: item.uid, label: item.name})
						});
						that.logTypeOptions = [];
						typeOptions.forEach(function (item) {
						    that.logTypeOptions.push({value: item.id, label: item.name})
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
                this.getOptionList()
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
            delOptLog(scope, isMore) {
                var that = this;
                let name = '';
                let idArr = [], nameArr = [];
                let params = {};
                if (isMore) {
                    this.selectedItem.forEach((item) => {

                        idArr.push(item.id);
                        if (nameArr.indexOf(item.aname) == -1) {
                            nameArr.push(item.aname);
                        }
                    });
                    name = '（' + nameArr.join('，') + '）';
                    params.id = idArr;
                } else {

                    name = '（' + scope.row.aname + '）';
                    params.id = scope.row.id;
                }

                delConfirm(this, params, this.delete, '您确定要删除业务员'+name+'这些操作记录吗？');
            },
            delOptLogSel: function () {
                var that = this;
                if (!that.selectedItem.length) {
                    message.error('请选择要删除的操作记录');
                    return;
                }
                this.delOptLog(null, true);
            },
            delete(params){
                var self = this;
                httpPost('m=crm&c=salesman&a=delOptLog', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            self.getOptionList();
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
                this.getOptionList();
            },
            handleCurrentChange(val) {
                console.log(`当前页: ${val}`);
                this.currentPage = val
                this.getOptionList();
            }
        },
    };
</script>
<style scoped>
    .moduleElHight .moduleElTable{height: calc(100% - 145px)}
    .moduleInptList .el-select .el-input {width: 140px;}
    .moduleElTabSeach{
        height: initial;
        flex-wrap: wrap;
    }
    .moduleElTabInpt{
        flex-wrap: wrap;
    }
    .moduleInptList{
        padding: 3px 6px 3px 0;
    }
</style>