<template>
    <div class="moduleElHight">
        <div class="moduleSeachs">
            <div class="moduleSeachInpt">
                <el-input v-model="keyword" placeholder="请输入搜索内容" class="input-with-select" size="small" style="margin-right: 8px; width: 340px;" prefix-icon="el-icon-search" clearbale>
                    <el-select v-model="search_type" slot="prepend" placeholder="接收人">
                        <el-option label="接收人" value="1"></el-option>
                        <el-option label="账号ID" value="2"></el-option>
                    </el-select>
                </el-input>
                <el-date-picker v-model="time" size="small" type="daterange" range-separator="至"
                    start-placeholder="开始日期" end-placeholder="结束日期" value-format="yyyy-MM-dd"
                    style="margin-right: 10px; text-align: left; width: 260px;" @change="search">
                </el-date-picker>
                <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
            </div>
            <el-button type="primary" icon="el-icon-document-add" size="mini" @click="set_drawer = true">微信通知设置</el-button>
        </div>
        <div class="moduleElTable">
            <el-table :data="tableData" border style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                @selection-change="handleSelectionChange" ref="multipleTable"
                :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='sortChange' v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column sortable="custom" prop="id" label="编号" width="80">
                </el-table-column>
                <el-table-column prop="uid" label="账号ID">
                </el-table-column>
                <el-table-column prop="username" label="接收人">
                </el-table-column>
                <el-table-column prop="ctime_n" label="发送时间">
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
                        <div class="cz_button">
                            <el-button size="mini" @click="delrow(scope.row)" type="danger">删除</el-button>
                        </div>
                        
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulecz modulePagButn">
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
        <div class="tck_setbox">
            <el-dialog title="微信模板通知" :visible.sync="set_drawer" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="450px">
                <div>
                    <div class="wxsettip_small ">历史模板设置</div>
                    <el-input v-model="wx_mbadmin" placeholder="请输入提醒模板"></el-input>
					<div class="wxsettip_small ">类目模板设置</div>
					<el-input v-model="wx_mbadminlm" placeholder="请输入提醒模板"></el-input>
                    <div class="wxsettip">微信模板消息通知设置教程
                        <el-link href="http://www.phpyun.com/bbs/thread-10851-1-1.html" target="_blank">查看教程<i
                                class="el-icon-view el-icon--right"></i></el-link>
                    </div>
                    <div class="wxsettip">要想收到微信模板通知，需要后台管理员
                        <el-link icon="el-icon-edit" @click="getcode">绑定微信</el-link>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="set_drawer = false">取 消</el-button>
                    <el-button type="primary" @click="submitMb" :disabled="saveLoading">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <div class="tck_setbox">
            <el-dialog title="扫描二维码" :visible.sync="code" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="300px">
                <div class="code_img">
                    <img :src="code_img" width="200" height="200">
                </div>
                <div class="code_p">请使用微信扫描二维码绑定</div>
            </el-dialog>
        </div>
        <div class="tck_setbox">
            <el-dialog title="扫描企微二维码" :visible.sync="qycode" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="300px">
                <div id="qyQrCode" class="code_img">

                </div>
                <div class="code_p">请使用企业微信扫描二维码绑定</div>
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
            wx_mbadmin: '',
			wx_mbadminlm:'',
            time: '',
            keyword: '',
            code: false,
            qycode: false,
            code_img: '',
            codesetval: null,
            sort_type: '',
            sort_col: '',
            saveLoading: false,
        }
    },
    created: function () {
        this.getList();
    },
    mounted() {

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
            httpPost('m=system&c=role_notice&a=wxAdminMsgList', params).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.tableData = res.data.list
                    that.group = res.data.group
                    that.week = res.data.week
                    that.perPage = parseInt(res.data.perPage)
                    that.pageSizes = res.data.pageSizes
                    that.total = parseInt(res.data.total)
                    that.wx_mbadmin = res.data.wx_mbadmin;
					that.wx_mbadminlm = res.data.wx_mbadminlm;
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
            if (!that.wx_mbadmin && !that.wx_mbadminlm) {
                message.error('请填写提醒模板');
                return false;
            }
            that.saveLoading = true;
            httpPost('m=system&c=role_notice&a=wmTypeSet', { wx_mbadmin: that.wx_mbadmin,wx_mbadminlm:that.wx_mbadminlm, msgconfig: 1 }).then(function (res) {
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
            httpPost('m=system&c=role_notice&a=delWxAdminMsg', params).then(function (response) {
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
        // 获取微信绑定二维码
        async getcode() {
            var that = this;
            httpPost('m=index&c=wxbind', {}).then(function (result) {
                var res = result.data;
                if (res.error == 0) {
                    that.code_img = res.data.code_url;
                    that.code = true;
                    that.codesetval = setInterval(function(){
                        that.wxbindstatus()
                    }, 2000);
                } else {
                    message.error(res.msg);
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        async wxbindstatus() {
            var that = this;
            httpPost('m=index&c=getwxbindstatus', {}).then(function (result) {
                var res = result.data;
                if (res.error == 0) {
                    message.success('绑定成功', function () {
                        that.code = false;
                        clearInterval(that.codesetval);
                    });
                }
            }).catch(function (e) {
                console.log(e)
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

.name-wrapper .el-link {
    color: #1989fa
}

.el-dialog__body {
    padding: 10px 20px;
}</style>