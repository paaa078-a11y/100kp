<template>
    <div class="moduleElHight">
        <div class="moduleSeachs">
            <div class="moduleSeachInpt">
                <el-input v-model="keyword" style="margin-right: 8px; width: 340px;" placeholder="请输入搜索内容" class="input-with-select" size="small" prefix-icon="el-icon-search" clearable>
                    <el-select v-model="search_type" size="small" slot="prepend" placeholder="接收人">
                        <el-option label="接收人" value="1"></el-option>
                        <el-option label="企微账号" value="2"></el-option>
                    </el-select>
                </el-input>
                <el-date-picker v-model="time" type="daterange" size="small" range-separator="至" start-placeholder="开始日期"
                    end-placeholder="结束日期" value-format="yyyy-MM-dd" style="margin-right: 10px; text-align: left; width: 260px;" @change="search">
                </el-date-picker>
                <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
            </div>
            <div class="moduleSeachButn">
                <el-button type="primary" icon="el-icon-document-add" size="mini" @click="set_drawer = true">企业微信通知设置
                </el-button>
            </div>
        </div>
        <div class="moduleElTable">
            <el-table :data="tableData" border style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                @selection-change="handleSelectionChange" ref="multipleTable"
                :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='sortChange' v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" sortable="custom" label="编号" width="80">
                </el-table-column>
                <el-table-column label="接收人">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top" v-if="scope.row.username_all">
                            <p>{{ scope.row.username_all }}</p>
                            <div slot="reference" class="name-wrapper">
                                <el-link>
                                    <div style="display: flex; align-items: center; overflow: hidden;position: relative;">
                                        <span>{{ scope.row.username }}</span>
                                        <img src="../../images/bg_wechat_help.png" style="margin-left: 6px;" alt="">
                                    </div>

                                </el-link>
                            </div>
                        </el-popover>
                        <span v-else>{{ scope.row.username }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="企业微信账号">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top" v-if="scope.row.tousers_all">
                            <p>{{ scope.row.tousers_all }}</p>
                            <div slot="reference" class="name-wrapper">
                                <el-link>
                                    <div style="display: flex; align-items: center; overflow: hidden;position: relative;">
                                        <span>{{ scope.row.tousers }}</span>
                                        <img src="../../images/bg_wechat_help.png" style="margin-left: 6px;" alt="">
                                    </div>
                                </el-link>
                            </div>
                        </el-popover>
                        <span v-else>{{ scope.row.tousers }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="发送内容" width="100">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top">
                            <p>发送内容</p>
                            <p v-html="scope.row.body_html"></p>
                            <div slot="reference" class="name-wrapper">
                                <el-link>查看内容<i class="el-icon-view el-icon--right"></i></el-link>
                            </div>
                        </el-popover>
                    </template>
                </el-table-column>
                <el-table-column prop="ctime_n" label="发送时间" width="180">
                </el-table-column>
                <el-table-column label="发送状态" width="100">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top" v-if="scope.row.status != 0">
                            <p>失败原因</p>
                            <p>{{ scope.row.status }}，{{ scope.row.msg }}</p>
                            <div slot="reference" class="name-wrapper">
                                <el-tag size="medium" type="danger">发送失败</el-tag>
                            </div>
                        </el-popover>
                        <el-tag v-else size="medium" type="success">发送成功</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="80">
                    <template slot-scope="scope">
                        <el-button size="mini" type="danger" @click="delrow(scope.row)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                <el-button size="mini" @click="delAllBottom">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    :current-page="currentPage" :page-sizes="pageSizes" :page-size="perPage"
                    layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="企业微信通知设置" :visible.sync="set_drawer" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="540px">
                <div>
                    <div class="wxsettip_small ">
                        企业ID
                        <el-tooltip class="item" effect="dark" content="在企业微信管理端->“我的企业”－“企业信息”下查看“企业ID”（需要有管理员权限）"
                            placement="right-start"><i class="el-icon-warning-outline"></i>
                            <el-button>右上</el-button>
                        </el-tooltip>
                    </div>
                    <el-input v-model="wx_qy_corpid" placeholder="wwf7201607f63ee1d4"></el-input>
                    <div class="wxsettip_small ">应用AgentId
                        <el-tooltip class="item" effect="dark" content="在企业微信管理端->“应用管理”->“应用”->“自建”，点进某个应用，即可看到agentid"
                            placement="right-start"><i class="el-icon-warning-outline"></i>
                            <el-button>右上</el-button>
                        </el-tooltip>
                    </div>
                    <el-input v-model="wx_qy_agentid" placeholder="1000003"></el-input>
                    <div class="wxsettip_small ">应用SCERET
                        <el-tooltip class="item" effect="dark" content="在企业微信管理端->“应用管理”->“应用”->“自建”，点进某个应用，即可看到。"
                            placement="right-start"><i class="el-icon-warning-outline"></i>
                            <el-button>右上</el-button>
                        </el-tooltip>
                    </div>
                    <el-input v-model="wx_qy_secret" placeholder="acBp9nze21qPPITtv8WlAbXxutukvFYOpm_PnES9nLg"></el-input>
                    <div class="wxsettip">要想收到企业微信通知，需要后台管理员绑定企业微信，并在对应企业微信应用中将该用户加入可见范围</div>
                    <div class="wxsettip">企业微信绑定需要在上述AgentId对应应用里设置授权域名
                        <el-link icon="el-icon-edit" href="./myaccount.html">绑定企业微信</el-link>
                    </div>
                    <div class="wxsettip">可见范围设置：
                        <el-link href="https://work.weixin.qq.com"
                            target="_blank">企业微信管理端</el-link>->“应用管理”->点进上述应用AgentId对应应用，即可编辑可见范围
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="set_drawer = false">取 消</el-button>
                    <el-button type="primary" @click="submitMb" :disabled="saveLoading">确 定</el-button>
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
            search_type: '1',
            set_drawer: false,
            currentPage: 1,
            prevPage: 0,
            perPage: 0,
            pageSizes: [],
            total: 0,
            checkedAll: false,
            selectedItem: [],
            tableData: [],
            wx_qy_corpid: '',
            wx_qy_agentid: '',
            wx_qy_secret: '',
            time: '',
            keyword: '',

            sort_type: '',
            sort_col: '',
            saveLoading: false,
        }
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
            this.search();
        },
        search() {
            this.currentPage = 1;
            this.getList();
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
            this.getList();
        },
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                pageSize: that.perPage
            }
            if (that.time) {
                params.date = that.time[0] + '~' + that.time[1]
            }
            if (that.search_type) {
                params.type = that.search_type
            }
            if (that.keyword) {
                params.keyword = that.keyword
            }
            if (that.sort_type && that.sort_col) {
                params.order = that.sort_type
                params.t = that.sort_col
            }
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=system&c=role_notice&a=qywxMsgList', params).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.tableData = res.data.list
                    that.perPage = parseInt(res.data.perPage)
                    that.pageSizes = res.data.pageSizes
                    that.total = parseInt(res.data.total)
                    that.wx_qy_corpid = res.data.wx_qy_corpid
                    that.wx_qy_agentid = res.data.wx_qy_agentid
                    that.wx_qy_secret = res.data.wx_qy_secret
                    if (that.prevPage != that.currentPage) {
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
        submitMb() {
            let that = this;
            if (!that.wx_qy_corpid) {
                message.error('请填写企业ID');
                return false;
            }
            if (!that.wx_qy_agentid) {
                message.error('请填写应用AgentId');
                return false;
            }
            if (!that.wx_qy_secret) {
                message.error('请填写应用SCERET');
                return false;
            }
            that.saveLoading = true;
            httpPost('m=system&c=role_notice&a=wmTypeSet', {
                wx_qy_corpid: that.wx_qy_corpid,
                wx_qy_agentid: that.wx_qy_agentid,
                wx_qy_secret: that.wx_qy_secret,
                msgconfig: 1
            }).then(function (res) {
                if (res.data.error == 0) {
                    that.set_drawer = false;
                    message.success(res.data.msg,function(){
                        that.getList();
                    });
                } else {
                   message.error(res.data.msg);
                }
            }).finally(function () {
                setTimeout(function () {
                    that.saveLoading = false;
                }, 2000);
            });
        },
        delrow(row) {
            delConfirm(this, row.id, this.delete);
        },
        delAllBottom() {
            if (!this.selectedItem.length) {
                this.$message({ showClose: true, message: '请选择要删除的数据', type: 'warning' });
                return false;
            }
            delConfirm(this, this.selectedItem, this.delete);
        },
        async delete(Ids) {
            let that = this;
            let params = {
                del: Ids
            };
            httpPost('m=system&c=role_notice&a=delQymsg', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success('操作成功');
                    that.getList();
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
    },
};
</script>
<style scoped>
.moduleSeachmore {
    padding: 0px;
}

.moduleSeachs {
    padding: 0px 0px 12px 0px;
    width: 100%;
}

.moduleElHight .moduleElTable {
    padding: 0;
    margin: 0;
    height: calc(100% - (60px + 0px + 12px + 24px));
    width: 100%;
}

.tableSeachInptsmalltwo {
    margin-bottom: 0px;
    margin-right: 12px;
}

.tableSeachInptsmalltwo .el-input__inner {
    height: 32px;
    line-height: 32px;
    width: 260px;
    padding: 0px 5px;
    ;
}

.el-dialog__body {
    padding: 0px 20px;
}
</style>