<template>
    <div class="moduleElHight">
        <div class="tableDome_tip">
            <el-alert title="运营人员可以针对微信公众号设置“回复关健词”方式后，用户在微信公众号上回复该关健词可以自动返回已设置的关健词或用户解决日常问题" type="success"
                      :closable="false">
            </el-alert>
        </div>
        <div class="moduleSeachs">
            <div class="moduleSeachleft">
                <div class="moduleInptList">
                    <el-input placeholder="请输入搜索内容" v-model="keyword" size="small" class="input-with-select" clearable>
                        <el-select v-model="type" size="small" slot="prepend" placeholder="搜索类型">
                            <el-option label="个人姓名" value="1"></el-option>
                            <el-option label="企业名称" value="2"></el-option>
                            <el-option label="猎头名称" value="3"></el-option>
                            <el-option label="培训名称" value="4"></el-option>
                            <el-option label="用户UID" value="90"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt tableSeachInptsmalltwo">
                    <el-date-picker v-model="daterange" type="daterange" @change="changedate" range-separator="至"
                                    start-placeholder="开始日期" end-placeholder="结束日期" size="samll">
                    </el-date-picker>
                </div>
                <div class="tableSeachInpt" style="margin-bottom: 0px;;">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>

        </div>

        <div class="moduleElTable">
            <el-table ref="table" :data="tableData" v-loading="list_loading" @selection-change="selectionChange"
                      :default-sort="{prop: 'id', order: 'descending'}" @sort-change="sortChange" border
                      style="width: 100%"
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                      :empty-text="emptytext">
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="uid" label="用户UID" width="80">
                </el-table-column>
                <el-table-column prop="username" label="接收人">
                </el-table-column>
                <el-table-column prop="utype_n" label="模板类型">
                </el-table-column>
                <el-table-column prop="mbconfig_n" label="模板内容">
                </el-table-column>
                <el-table-column label="	模板内容" width="100">
                    <template slot-scope="scope">
                        <el-popover trigger="hover" placement="top">
                            <div v-html="scope.row.body_html"></div>
                            <div slot="reference" class="name-wrapper">
                                <el-tag size="success">查看内容</el-tag>
                            </div>
                        </el-popover>
                    </template>
                </el-table-column>

                <el-table-column prop="wxid" label="微信id">
                </el-table-column>
                <el-table-column prop="ctime_n" label="发送时间" sortable="custom">
                </el-table-column>


                <el-table-column label="发送状态" width="100">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status==0" class="admin_state1">成功</span>
                        <el-popover trigger="hover" placement="top" v-else>
                            <p>失败原因</p>
                            <p>{{ scope.row.msg }}</p>
                            <div slot="reference" class="name-wrapper">
                                <el-tag size="medium" type="danger">发送失败</el-tag>
                            </div>
                        </el-popover>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="80" fixed="right" header-align="center">>
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button type="danger" size=" " @click="deleteinfo(scope.row.id)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <div class="otherPageButn">
            <div class="modulePaging">
                <div class="modulecz modulePagButn">
                    <el-checkbox v-model="allchecked" @change="allcheckChange">全选</el-checkbox>
                    <el-button size="mini" @click="deleteAll">批量删除</el-button>
                    <el-button size="mini" @click="repeatSend">失败重发</el-button>
                </div>
                <div class="modulePagNum">
                    <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                                   :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes"
                                   :total="total"
                                   layout="total, sizes, prev, pager, next, jumper">
                    </el-pagination>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
            emptytext: '暂无数据',
            tableData: [],
            total: 0,
            limit: 0,
            currentPage: 1,
			prevPage:0,
            page_sizes: [],

            list_loading: false,

            allchecked: false,
            choosedata: [],

            keyword: '',
            type: '1',
            state: '',
            time: '',
            daterange: [],
            sort_t: '',
            order: '',
        }
    },

    mounted() {
        this.getList();
    },
    methods: {
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                limit: that.limit,
                t: that.sort_t,
                order: that.order,
                keyword: that.keyword,
                type: that.type,
                state: that.state,
                time: that.time
            }

            if (this.daterange && this.daterange.length > 0) {

                params['date1'] = this.daterange[0].getTime() / 1000;
                params['date2'] = this.daterange[1].getTime() / 1000;
            }

            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=tool&c=wxtz&a=wxmsglist', params, {hideloading: true}).then((result) => {
                this.list_loading = false;
                var res = result.data;

                if (res.error == 0) {
                    that.tableData = res.data.list
                    that.total = parseInt(res.data.total)
                    that.page_sizes = res.data.page_sizes;
                    that.limit = res.data.page_size;
					
					if(that.prevPage != that.currentPage){
						that.prevPage = that.currentPage;
						that.$refs.table.bodyWrapper.scrollTop = 0;
					}
                    if (that.tableData.length === 0) {
                        that.emptytext = "暂无数据";
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        search: function () {
            this.currentPage = 1;
            this.getList();
        },
        sortChange: function (e) {
            this.sort_t = e.prop;
            if (e.prop == 'ctime_n') {
                this.sort_t = 'ctime';
            }
            this.order = e.order == 'ascending' ? 'asc' : 'desc';
            this.search();
        },
        changedate: function (e) {
            if (e !== null) {
                this.time = '';
            }
        },
        dateSelectChange: function (e) {
            if (e != '') {
                this.daterange = [];
            }
        },
        handleCurrentChange(val) {
            this.currentPage = val;
            this.getList()
        },
        handleSizeChange(val) {
            this.currentPage = 1
            this.limit = val
            this.getList()
        },
        allcheckChange: function () {

            this.$refs.table.toggleAllSelection();

        },
        selectionChange: function (e) {
            if (this.tableData.length != e.length) {
                this.allchecked = false;
            } else {
                this.allchecked = true;
            }
            this.choosedata = e;
        },
        deleteinfo: function (id) {
            var _this = this;

            var params = {
                del: id
            };
            delConfirm(_this, params, this.deletePost)
        },
        deleteAll: function () {
            var _this = this;
            var idarr = [];
            if (this.choosedata.length > 0) {
                for (let i in this.choosedata) {
                    idarr.push(this.choosedata[i].id);
                }
            } else {
                message.error('请选择要删除的数据');
                return;
            }
            var params = {
                del: idarr
            };

            delConfirm(_this, params, this.deletePost)
        },
        async deletePost(params) {

            let that = this;

            httpPost('m=tool&c=wxtz&a=delmsg', params).then(function (result) {

                var res = result.data;
                if (res.error == 0) {
                    message.success(res.msg, function () {
                        that.getList()
                    });
                    return;
                } else {
                    message.error(res.msg);
                    return;
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        repeatSend: function () {
            var _this = this;
            var idarr = [];
            if (this.choosedata.length > 0) {
                for (let i in this.choosedata) {
                    idarr.push(this.choosedata[i].id);
                }
            } else {
                message.error('请选择要重发的通知');
                return;
            }
            var params = {
                id: idarr
            };

            delConfirm(_this, params, this.repeatPost, '确定重新发送通知吗？')
        },
        async repeatPost(params) {

            let that = this;

            httpPost('m=tool&c=wxtz&a=msgrepeat', params).then(function (result) {

                var res = result.data;
                if (res.error == 0) {
                    message.success(res.msg, function () {
                        that.getList()
                    });
                    return;
                } else {
                    message.error(res.msg);
                    return;
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        doLayout(){
            if (this.$refs.table) {
                this.$nextTick(() => {
                    this.$refs.table.doLayout();
                })
            }
        }
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

.moduleElTable {
    padding: 0;
    margin: 0;
    height: calc(100% - 136px);
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
    padding: 0px 5px;;
}

.el-dialog__body {
    padding: 0px 20px;
}
</style>