<template>
    <div class="moduleElHight">
        <div class="moduleSeachs">
            <div class="moduleSeachleft">
                <div class="moduleInptList">
                    <el-input placeholder="请输入搜索内容" v-model="keyword" class="input-with-select" size="small"
                              size="small" clearable>
                        <el-select slot="prepend" v-model="type" size="small" placeholder="搜索类型">
                            <el-option label="账户" value="1"></el-option>
                            <el-option label="被屏蔽账户" value="2"></el-option>
                            <el-option label="用户ID" value="3"></el-option>
                            <el-option label="被屏蔽ID" value="4"></el-option>
                        </el-select>
                    </el-input>
                </div>

                <div class="tableSeachInpt" style="margin-bottom: 0px;">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable">
            <el-table ref="table" :data="tableData" v-loading="list_loading" @selection-change="selectionChange"
                      :default-sort="{prop: 'id', order: 'descending'}"
                      @sort-change="sortChange" border style="width: 100%"
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                      :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="uid" label="用户id" width="80"></el-table-column>
                <el-table-column prop="fid" label="被屏蔽id" width="80"></el-table-column>
                <el-table-column prop="countname" label="用户姓名"></el-table-column>
                <el-table-column prop="username" label="账户"></el-table-column>
                <el-table-column prop="rcountname" label="被屏蔽用户姓名"></el-table-column>
                <el-table-column prop="rusername" label="被屏蔽账户"></el-table-column>
                <el-table-column prop="ctime_n" label="屏蔽时间" sortable="custom"
                                 :sort-orders="['descending','ascending']"></el-table-column>
                <el-table-column label="操作" width="80" fixed="right" header-align="center">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button type="danger" @click="deleteinfo(scope.row.id)">解除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulecz">
                <el-checkbox v-model="allchecked" @change="allcheckChange">全选</el-checkbox>
                <el-button size="mini" @click="deleteAll">批量解除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total"
                               layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
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

            sort_t: '',
            order: '',

            editshow: false,
            einfo: {},
            post_loading: false,
        }
    },

    mounted() {
        var that = this;
        let params = window.parent.homeapp.$route.params;
        let query = window.parent.homeapp.$route.query;
        
        if (!$.isEmptyObject(query.params)) {
            params = {...params,...query.params};
        }
        
        if (!$.isEmptyObject(params)) {
            delete params.activeName;
            this.getParams(params);
        }
        this.getList();
    },
    methods: {
        getParams: function (params = {}, search = false) {
            var that = this;
            for (let i in params) {
                that[i] = params[i];
            }
            if (search) {
                this.search();
            }
        },
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                limit: that.limit,
                t: that.sort_t,
                order: that.order,
                keyword: that.keyword,
                type: that.type,
            }

            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=tool&c=chatlog&a=blacklist', params, {hideloading: true}).then((result) => {
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

            this.order = e.order == 'ascending' ? 'asc' : 'desc';
            this.search();
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
                message.error('请选择要解除的数据');
                return;
            }
            var params = {
                del: idarr
            };

            delConfirm(_this, params, this.deletePost, '此操作将解除屏蔽, 是否继续')
        },
        async deletePost(params) {

            let that = this;

            httpPost('m=tool&c=chatlog&a=delblack', params).then(function (result) {

                var res = result.data;
                if (res.error == 0) {
                    message.success(res.msg, function () {
                        that.getList();
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