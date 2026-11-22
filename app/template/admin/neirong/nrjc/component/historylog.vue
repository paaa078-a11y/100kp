<template>
    <div class="moduleElenAl" style="position: relative;">
        <div class="moduleSeachs">
            <div class="moduleSeachleft">
                <div class="tableSeackellsd" style="padding: 2px 0;">
                    <el-input v-model="keyword" placeholder="请输入搜索内容" class="input-with-select" size="small" prefix-icon="el-icon-search" clearable>
                        <el-select v-model="type" size="small" slot="prepend" placeholder="用户名">
                            <el-option label="用户名" value="1"></el-option>
                            <el-option label="内容" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt newsinput" style="margin-bottom: 0px; margin-right: 12px;">
                    <el-date-picker v-model="time" type="daterange" size="small" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
                </div>
                <div v-for="(searchItem, searchIndex) in searchList" :key="searchIndex" class="tableSeachInpt tableSeachInptsmall newsinput" style="margin-bottom: 0px;">
                    <el-select v-model="searchForm[searchItem.param]" slot="prepend" :clearable="true" :placeholder="searchItem.name" size="small" @change="search">
                        <el-option v-for="(searchLabel, searchValue) in searchItem.value" :key="searchValue" :label="searchLabel" :value="searchValue"></el-option>
                    </el-select>
                </div>
                <div class="newsbtnbox" style="margin-bottom: 0px;margin-left: 10px;">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable">
            <el-table :data="tableData" stripe border :default-sort="{ prop: 'id', order: 'descending' }" @sort-change="sortChange" style="width: 100%;" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" @selection-change="handleSelectionChange" ref="multipleTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" width="90" sortable="custom"></el-table-column>
                <el-table-column label="用户名/企业名称(姓名)">
                    <template slot-scope="scope">
                        <span>{{ scope.row.username }}</span>
                        <span>{{ scope.row.name }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="ctype_n" label="修改简介" width="280"></el-table-column>
                <el-table-column label="内容" prop="content" width="100">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top">
                            <p v-if="scope.row.type == '1'" v-html="scope.row.content_n"></p>
                            <img v-else :src="scope.row.content_n">
                            <div slot="reference" class="name-wrapper">
                                <el-link>查看内容<i class="el-icon-view el-icon--right"></i></el-link>
                            </div>
                        </el-popover>
                    </template>
                </el-table-column>
                <el-table-column label="来源" prop="source_n" width="100"></el-table-column>
                <el-table-column label="检测结果" prop="jcresult">
                    <template slot-scope="scope">
                        <div v-if="scope.row.result == '1'"><span style="color:green">合规</span></div>
                        <div v-else>
                            <span style="color:red;display:inline-block">不合规</span>
                            <div style="display:inline-block">
                            <el-popover trigger="hover" placement="right">
                                <p>{{scope.row.message}}</p>
                                <div slot="reference" class="name-wrapper">
                                    <i class="el-icon-question el-icon--right"></i>
                                </div>
                            </el-popover></div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="处理状态" prop="clstate">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status == '0'" class="admin_wtg"> 待处理</span>
                        <span v-else-if="scope.row.status == '1'" class="admin_ysh"> 已处理</span>
                        <span v-else-if="scope.row.status == '2'" class="admin_wxcl">无需处理</span>
                    </template>
                </el-table-column>
                <el-table-column label="时间" prop="ctime_n" sortable="custom">
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="small " plain @click="setstatus(scope.row)">处理</el-button>
                            <el-button type="danger" size="small " @click="delrow(scope.row.id)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="">
                <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="delAllBottom" size="mini">批量删除</el-button>
                <el-button @click="setstatusmultiple" size="mini">处理状态</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="currentPage" :page-sizes="pageSizes" :page-size="perPage" layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
            </div>
        </div>
        <!--处理弹窗-->
        <div class="modluDrawer">
            <el-dialog title="设置处理状态" width="500px" :visible.sync="drawerstatus" :modal-append-to-body="false" :append-to-body="true">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>处理状态：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio v-model="curstatus" label="0">待处理</el-radio>
                            <el-radio v-model="curstatus" label="1">已处理</el-radio>
                            <el-radio v-model="curstatus" label="2">无需处理</el-radio>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>处理说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="curr_data.statusbody">
                            </el-input>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawerstatus = false">取 消</el-button>
                    <el-button type="primary" @click="statusSave(1)" :disabled="saveLoading">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--批量处理弹窗-->
        <div class="modluDrawer">
            <el-dialog title="批量设置处理状态" width="500px" :visible.sync="drawermultiplestatus" :modal-append-to-body="false" :append-to-body="true">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>处理状态：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio v-model="multiplestatus" label="0">待处理</el-radio>
                            <el-radio v-model="multiplestatus" label="1">已处理</el-radio>
                            <el-radio v-model="multiplestatus" label="2">无需处理</el-radio>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>处理说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="multiplestatusbody">
                            </el-input>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawermultiplestatus = false">取 消</el-button>
                    <el-button type="primary" @click="statusSave(2)" :disabled="saveLoading">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    module.exports = {
        props: {
            history_id: {
                type: String,
                default: {}
            },
        },
        data: function () {
            return {
                emptytext: '暂无数据',
                loading: false,
                saveLoading: false,

                type: '1',
                keyword: '',
                time: '',
                checkedAll: false,
                selectedItem: [],
                tableData: [],
                currentPage: 1,
                perPage: 0,
                pageSizes: [],
                total: 0,
                drawerstatus: false,
                curr_data: [],
                historyid: '',
                islook: false,
                drawermultiplestatus: false,
                multiplestatus: '',
                multiplestatusbody: '',
                sort_type: '',
                sort_col: '',

                searchForm: {},
                searchList: [],
                curstatus: '',
                prevPage:0
            }
        },
        watch: {
            history_id: {
                handler(val) {
                    this.historyid = val;
                    this.getGroup();
                    this.getList();
                },
                immediate: true,
                deep: true,
            },
        },
        methods: {
            sortChange: function (column) {
                if (column.order == 'descending') {
                    this.sort_type = 'desc';
                } else if (column.order == 'ascending') {
                    this.sort_type = 'asc';
                } else {
                    this.sort_type = '';
                }
                this.sort_col = column.prop
                if (this.sort_col == 'ctime_n') {
                    this.sort_col = 'ctime'
                }
                this.search();
            },
            setstatus(data){
                this.curstatus = data.status;
                this.drawerstatus = true
            },
            setstatusmultiple(){
                if (!this.selectedItem.length) {
                    message.error('请选择要处理的数据项');
                    return false;
                }
                this.multiplestatus = ''
                this.multiplestatusbody = ''
                this.drawermultiplestatus = true
            },
            history(id){
                this.historyid = id
                this.search()
            },
            handleSelectionChange(val) {
                this.selectedItem = [];
                let _this = this;
                if (val.length) {
                    val.forEach(item => {
                        _this.selectedItem.push(item.id);
                    });
                }
                if (_this.selectedItem.length == 0) {
                    _this.checkedAll = false;
                } else {
                    if (_this.selectedItem.length == _this.tableData.length) {
                        _this.checkedAll = true;
                    } else {
                        _this.checkedAll = false;
                    }
                }
            },
            selectAllBottom(value) {
                value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
            },
            handleSizeChange(val) {
                this.perPage = val;
                this.getList()
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.getList()
            },
            search() {
                this.currentPage = 1;
                this.getList();
            },
            async getGroup(){
                let that = this;
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=neirong&c=concheck_log&a=getGroup', {}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.searchList = res.data.search_list;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            async getList() {
                let that = this,
                    searchForm = that.searchForm;
                let params = {
                    ...searchForm,
                    page: that.currentPage,
                    pageSize: that.perPage
                }
                if (that.type) {
                    params.type = that.type
                }
                if (that.keyword) {
                    params.keyword = that.keyword
                }
                if (that.historyid) {
                    params.history = that.historyid
                }
                if (that.time) {
                    params.date = that.time[0] + '~' + that.time[1]
                }
                if (that.sort_type && that.sort_col) {
                    params.order = that.sort_type
                    params.t = that.sort_col
                }
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=neirong&c=concheck_log&a=index', params).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.tableData = res.data.list
                        that.perPage = parseInt(res.data.perPage)
                        that.pageSizes = res.data.pageSizes
                        that.total = parseInt(res.data.total)
                        if(that.prevPage != that.currentPage){
                            that.prevPage = that.currentPage;
                            that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                        }
                        that.loading = false;
                        if (that.tableData.length === 0){
                            that.emptytext = "暂无数据";
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            delrow(id) {
                delConfirm(this, id, this.delete);
            },
            delAllBottom() {
                if (!this.selectedItem.length) {
                    message.error('请选择要删除的数据项');
                    return false;
                }
                delConfirm(this, this.selectedItem, this.delete,'你确定要删除选中项吗');
            },
            async delete(id) {
                let that = this;
                let params = {
                    del: id
                };
                httpPost('m=neirong&c=concheck_log&a=del', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success(response.data.msg);
                        that.getList();
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            // 处理
            async statusSave(tp) {
                var that = this
                let params = {}
                if (tp == 1) {
                    params.pid = that.curr_data.id
                    params.status = that.curstatus
                    params.statusbody = that.curr_data.statusbody
                } else {
                    if (!that.selectedItem.length) {
                        message.error('请选择要处理的数据项');
                        return false;
                    }
                    params.pid = that.selectedItem.join(',')
                    if (that.multiplestatus == '') {
                        message.error('请选择处理状态');
                        return false;
                    }
                    params.status = that.multiplestatus
                    params.statusbody = that.multiplestatusbody
                }

                if (that.saveLoading) {
                    return false;
                }
                that.saveLoading = true;

                httpPost('m=neirong&c=concheck_log&a=status', params).then(function (response) {
                    if (response.data.error == 0) {
                        that.drawerstatus = false;
                        that.drawermultiplestatus = false;
                        message.success(response.data.msg, function () {
                            that.saveLoading = false;
                        });
                    } else {
                        that.saveLoading = false;
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
        }
    };
</script>
<style>
    .cell span {
        display: flex;
        align-items: center;
    }
</style>