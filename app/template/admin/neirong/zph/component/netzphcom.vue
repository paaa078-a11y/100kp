<template>
    <div class="moduleElHight">
        <div class="moduleSeachs">
            <div class="moduleSeachInpt">
                <el-input placeholder="请输入搜索内容" size="small" style="margin-right: 8px; width: 340px;" v-model="com_keyword"
                          clearable class="input-with-select">
                    <el-select v-model="type" slot="prepend" placeholder="请选择">
                        <el-option label="招聘会名称" value="1"></el-option>
                        <el-option label="会场" value="2"></el-option>
                    </el-select>
                </el-input>
                <el-select v-model="com_status" size="small" slot="prepend" clearable placeholder="状态" style="margin-right: 10px; text-align: left;" @change="comSearch">
                    <el-option label="已通过" value="1"></el-option>
                    <el-option label="未审核" value="3"></el-option>
                    <el-option label="未通过" value="2"></el-option>
                </el-select>
                <el-button type="primary" icon="el-icon-search" size="mini" @click="comSearch">查询</el-button>
            </div>
        </div>
        <div class="admin_datatip"><i class="el-icon-document"></i> 可以实现区域、展位等进行自主设置，企业可在线付费报名参加招聘会等操作
        </div>
        <div class="moduleElTable moduleElMoreLive"
             style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="comTableData" style="width: 100%" stripe
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                      @selection-change="handleComSelectionChange" ref="comMultipleTable"
                      :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='comSortChange'
                      :key="comTblKey" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" sortable="custom"></el-table-column>
                <el-table-column prop="title" label="招聘会" min-width="180"
                                 show-overflow-tooltip></el-table-column>
                <el-table-column prop="comname" label="参与企业" min-width="180"
                                 show-overflow-tooltip></el-table-column>
                <el-table-column prop="jobname" label="职位" min-width="180"
                                 show-overflow-tooltip></el-table-column>
                <el-table-column label="展会区域">
                    <template slot-scope="scope">
<!--                        <span>{{ scope.row.zw != '0' && scope.row.zw != '' ? tblarea[scope.row.zw] : '暂无' }}</span><br/>-->
                        <span>{{scope.row.area_name}}</span><br/>
                        <el-link type="primary" @click="fpzw(scope.row)">分配</el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="sort" label="排序" sortable="custom">
                    <template slot-scope="scope">
                        <el-input v-if="scope.row[scope.column.property + 'isShow']"
                                  :ref="scope.column.property + scope.$index"
                                  :id="scope.column.property + scope.$index"
                                  v-model="scope.row.sort" @blur="alterData(scope, 1)"></el-input>
                        <span v-else>
                                    {{ scope.row.sort }}
                                    <img src="../../../admin/images/bine.png" alt="" style="margin-left: 4px;"
                                         width="14"
                                         height="14" @click="editData(scope, 1)">
                                </span>
                    </template>
                </el-table-column>
                <el-table-column prop="zt" label="状态">
                    <template slot-scope="props">
                        <div class="admin_state">
                            <span v-if="props.row.status == '1'" class="admin_state1"> 已通过</span>
                            <span v-else-if="props.row.status == '0'" class="admin_state2"> 未审核</span>
                            <span v-else-if="props.row.status == '2'" class="admin_state2"> 未通过</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" fixed="right" width="150" align="center">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" plain @click="cominfo(scope.row)">详情</el-button>
                            <el-button type="danger" size="mini" @click="comdelrow(scope.row.id)">删除
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="comCheckedAll" @change="comSelectAllBottom">全选</el-checkbox>
                <el-button @click="comDelAllBottom" size="mini">批量删除</el-button>
                <el-button @click="multiAudit" size="mini">批量审核</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleComSizeChange"
                               @current-change="handleComCurrentChange"
                               :current-page="comCurrentPage" :page-sizes="comPageSizes"
                               :page-size="comPerPage"
                               layout="total, sizes, prev, pager, next, jumper" :total="comTotal">
                </el-pagination>
            </div>
        </div>
        <el-drawer title="参会企业详情" :visible.sync="comdrawersh" append-to-body :modal-append-to-body="false" size="80%">

            <zphdetail :infoold="info" :zw_n="zw_n" ref="zphview" :zphname="curr_comdata.zphname" :is_net ='1' :space_n="curr_comdata.space_n" @setcomjob="showComJob()" @fpzw="fpzw()" @comsave="comsave"></zphdetail>
        </el-drawer>
        <!--批量审核参会企业-->
        <div class="modluDrawer">
            <el-dialog title="参会企业审核" width="300px" :visible.sync="drawercomstatusmultiple" append-to-body
                       :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核操作：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio v-model="multiComStatus" label="1">正常</el-radio>
                            <el-radio v-model="multiComStatus" label="2">未通过</el-radio>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" v-model="multiComStatusBody" :rows="2" placeholder="请输入内容">
                            </el-input>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawercomstatusmultiple = false">取 消</el-button>
                    <el-button type="primary" @click="multipleComStatusSave">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--分配展会区域弹窗-->
        <div class="modluDrawer">
            <el-dialog title="分配展会区域" width="300px" :visible.sync="drawerfpzw" append-to-body
                       :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite" style="width: 90px;">
                            <span>企业名称：</span>
                        </div>
                        <div class="toolClasCont">
                            <span>{{curr_comdata.comname}}</span>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>展会区域：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-select v-model="curr_comdata.zw" placeholder="请选择" filterable clearable>
                                <el-option v-for="(item,index) in area" :key="index" :label="item"
                                           :value="index">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawerfpzw = false">取 消</el-button>
                    <el-button type="primary" @click="fpzwSave(1)">确 定</el-button>
                </span>
            </el-dialog>
        </div>

        <!--参会职位-->
        <div class="modluDrawer">
            <el-dialog title="参会职位" width="300px" :visible.sync="drawercomjob" append-to-body :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>选择职位：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-select v-model="jobids" filterable remote placeholder="选择职位" multiple>
                                <el-option v-for="item in job_arr" :key="item.id" :label="item.name" :value="item.id">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawercomjob = false">取 消</el-button>
                    <el-button type="primary" @click="saveComJob">确 定</el-button>
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
                type: '1',
                com_status: '',
                com_keyword: '',
                comCheckedAll: false,
                comSelectedItem: [],
                comTableData: [],
                comCurrentPage: 1,
                comPerPage: 0,
                comPageSizes: [],
                comTotal: 0,
                comTblKey: '',
                curr_comdata: {},
                info: {},
                dtlislook: false,
                comdrawer: false,
                comdrawersh: false,
                curr_comstatus: '',
                curr_comstatusbody: '',
                tableHig: true,
                zw_n: '',
                drawercomstatusmultiple: false,
                multiComStatus: '',
                multiComStatusBody: '',
                drawerfpzw: false,
                area: {},
                tblarea:{},
                islook: false,
                submitLoading:false,

                drawercomjob:false,
                jobids:[],
                prevPage:0,
                job_arr:[]
            }
        },
        mounted() {

        },
        components: {
            'zphdetail': httpVueLoader('./zphdetail.vue'),
        },
        methods: {
            // 参会企业批量审核
            multiAudit: function(){
                if (!this.comSelectedItem.length) {
                    message.error('请选择要操作的数据项');
                    return false;
                }
                this.drawercomstatusmultiple = true
            },
            // 参会企业批量审核提交
            multipleComStatusSave() {
                var that = this
                if (!that.comSelectedItem.length) {
                    message.error('请选择要操作的数据项');
                    return false;
                }
                that.comstatus({
                    pid: that.comSelectedItem.join(','),
                    status: that.multiComStatus,
                    status_body: that.multiComStatusBody
                }, 2);
            },
            comStatusSave(id) {
                this.comstatus({pid: id, status: this.curr_comstatus, statusbody: this.curr_comstatusbody}, 1);
            },
            comstatus(params, tp) {
                var that = this
                this.submitLoading = true;
                httpPost('m=neirong&c=zphnet&a=status', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success(response.data.msg, function () {
                            that.getList()
                        });
                    } else {
                        message.error('获取参会企业失败');
                    }
                }).catch(function (error) {
                    console.log(error);
                }).finally(function () {
                    that.submitLoading = false;
                });
                if (tp == 1) {
                    that.dtlislook = false
                    that.comdrawersh = false
                } else {
                    that.drawercomstatusmultiple = false
                }
            },
            handleComSelectionChange(val) {
                this.comSelectedItem = [];
                let _this = this;
                if (val.length) {
                    val.forEach(item => {
                        _this.comSelectedItem.push(item.id);
                    });
                }
                if (_this.comSelectedItem.length == 0) {
                    _this.comCheckedAll = false;
                } else {
                    if (_this.comSelectedItem.length == _this.comTableData.length) {
                        _this.comCheckedAll = true;
                    } else {
                        _this.comCheckedAll = false;
                    }
                }
            },
            comSelectAllBottom(value) {
                value ? this.$refs.comMultipleTable.toggleAllSelection() : this.$refs.comMultipleTable.clearSelection();
            },
            handleComSizeChange(val) {
                this.comPerPage = val;
                this.getList()
            },
            handleComCurrentChange(val) {
                this.comCurrentPage = val;
                this.getList()
            },
            comSortChange: function (column) {
                if (column.order == 'descending') {
                    this.sort_type = 'desc';
                } else if (column.order == 'ascending') {
                    this.sort_type = 'asc';
                } else {
                    this.sort_type = '';
                }
                this.sort_col = column.prop
                this.comSearch();
            },
            comSearch() {
                this.comCurrentPage = 1;
                this.getList();
            },
            async getList() {
                let that = this;
                let params = {
                    type: that.type,
                    page: that.comCurrentPage,
                    pageSize: that.comPerPage
                }
                if (that.com_keyword) {
                    params.keyword = that.com_keyword
                }
                if (that.com_status) {
                    params.status = that.com_status
                }
                if (that.sort_type && that.sort_col) {
                    params.order = that.sort_type
                    params.t = that.sort_col
                }
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=neirong&c=zphnet&a=com', params, {hideloading: true}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.comTableData = res.data.list
                        that.comPerPage = parseInt(res.data.perPage)
                        that.comPageSizes = res.data.pageSizes
                        that.comTotal = parseInt(res.data.total)
                        that.tblarea = res.data.area
                        if(that.prevPage != that.comCurrentPage){
                            that.prevPage = that.comCurrentPage;
                            that.$refs.comMultipleTable.bodyWrapper.scrollTop = 0;
                        }
                        that.loading = false;
                        if (that.comTableData.length === 0){
                            that.emptytext = "暂无数据";
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            comdelrow(id) {
                delConfirm(this, id, this.comDelete);
            },
            comDelAllBottom() {
                if (!this.comSelectedItem.length) {
                    message.error('请选择要删除的数据项');
                    return false;
                }
                delConfirm(this, this.comSelectedItem, this.comDelete);
            },
            async comDelete(id) {
                let that = this;
                let params = {
                    del: id
                };
                httpPost('m=neirong&c=zphnet&a=delcom', params).then(function (response) {
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
            editData(scope) {
                let index = scope.$index;
                let row = scope.row;
                let column = scope.column;
                this.oldData = JSON.parse(JSON.stringify(row));
                let copyRow = JSON.parse(JSON.stringify(row));
                copyRow[column.property + "isShow"] = true;
                this.$set(this.comTableData, index, copyRow);
                this.$nextTick(() => {
                    let ref = column.property + index;
                    $("#" + ref).focus();
                });
            },
            alterData(scope) {
                if (this.oldData == null) {
                    return false;
                }
                let index = scope.$index;
                let row = scope.row;
                let column = scope.column;
                let copyRow = JSON.parse(JSON.stringify(row));
                copyRow[column.property + "isShow"] = false;
                this.$set(this.comTableData, index, copyRow);
                if (row[column.property] === this.oldData[column.property]) {
                    return false;
                }
                let _this = this;
                let sendData = {id: row.id};
                sendData[column.property] = row[column.property];
                httpPost('m=neirong&c=zphnet&a=ajaxsort', sendData, {hideloading: true}).then(function (response) {
                    let res = response.data;
                    if (res.error === 0) {
                        message.success('修改成功');
                    } else {
                        message.error('修改失败');
                    }
                    _this.oldData = null;
                    _this.getList();
                }).catch(function (error) {
                    console.log(error);
                });
            },
            cominfo(data) {
                var that = this
                that.curr_comdata = data
                httpPost('m=neirong&c=zphnet&a=audit', {id: this.curr_comdata.id}).then(function (response) {
                    if (response.data.error == 0) {
                        that.info = response.data.data
                        that.zw_n = that.info.zphnet.zw_n
                        that.curr_comstatus = that.info.zphnet.status
                        that.curr_comstatusbody = that.info.zphnet.statusbody
                        // debugger
                        that.dtlislook = true
                        that.comdrawersh = true
                    } else {
                        message.error('获取参会企业失败');
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            fpzw() {
                var that = this
                let data =  that.curr_comdata;
                if (data.zw == '0') {
                    data.zw = ''
                }
                httpPost('m=neirong&c=zphnet&a=getArea', {id: data.zid}).then(function (response) {
                    if (response.data.error == 0) {
                        that.area = response.data.data.area
                        that.drawerfpzw = true
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            fpzwSave(tp) {
                var that = this
                let params = {}
                params.zphcomid = that.curr_comdata.id
                params.zw = that.curr_comdata.zw
                httpPost('m=neirong&c=zphnet&a=setComZw', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success(response.data.msg, function () {
                            that.drawerfpzw = false
                            that.getList();
                        });
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },

            // 设置参会职位
            showComJob(){
                var that = this
                that.job_arr = []
                httpPost('m=neirong&c=zphnet&a=getZphJoblist', {id: this.curr_comdata.id,comid: that.curr_comdata.uid,type:'admin',zid:that.curr_comdata.zid}).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        if (res.data.rows.length > 0) {
                            that.jobids = res.data.jobIds;
                            that.job_arr = res.data.rows;
                            that.drawercomjob = true
                        } else {
                            message.error('该企业没有可作展出的职位！');
                        }
                    } else {
                        message.error('该企业没有可作展出的职位！');
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            saveComJob:function(){
                var that = this;
                httpPost('m=neirong&c=zphnet&a=upjob', {zcomid: that.curr_comdata.id,zphjob:that.jobids}).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg);
                        that.cominfo(that.curr_comdata);
                        that.getList();
                    } else {
                        message.error(res.msg);
                    }
                    that.drawercomjob = false;
                }).catch(function (error) {
                    console.log(error);
                })
            },
            comsave:function (e) {
                this.comstatus(e.params, e.tp);
            }
        },
    };
</script>
<style scoped></style>
