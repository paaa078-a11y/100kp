<template>
    <div class="moduleElHight" :class="searchClass == 'drawer' ? 'pad20' : ''">
        <div class="tableDome_tip"><!--文案描述--></div>
        <div class="moduleSeachs">
            <div class="moduleSeachleft">
                <div class="moduleInptList">
                    <el-input placeholder="请输入搜索内容" v-model="keyword" class="input-with-select" size="small" clearable>
                        <el-select v-model="type" slot="prepend" placeholder="搜索类型">
                            <el-option label="发送人用户名" value="1"></el-option>
                            <el-option label="接收人用户名" value="2"></el-option>
                            <el-option label="内容" value="3"></el-option>
                            <el-option label="企业名称" value="4"></el-option>
                            <el-option label="姓名" value="5"></el-option>
                            <el-option label="手机号" value="6"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt tableSeachInptsmalltwo" style="margin-right: 0px;;">
                    <el-date-picker v-model="daterange" type="daterange" value-format="yyyy-MM-dd" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" size="samll" @change="search"></el-date-picker>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall" style="margin-bottom: 0px;">
                    <el-select v-model="status" size="small" slot="prepend" placeholder="查看状态" clearable @change="search">
                        <el-option label="已读" value="1"></el-option>
                        <el-option label="未读" value="2"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt" style="margin-bottom: 0px;;">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable">
            <el-table ref="table" :data="tableData" v-loading="list_loading" @selection-change="selectionChange" :default-sort="{prop: 'id', order: 'descending'}" @sort-change="sortChange" border style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" width="80" sortable="custom" :sort-orders="['descending','ascending']"></el-table-column>
                <el-table-column label="发送人" width="280">
                    <template slot-scope="scope">
                        <div class="chat_sftag">
                            <div class="chat_sftag_r">
                                <div>
                                    <el-tag size="mini" v-if="scope.row.fusertype=='3'">猎头</el-tag>
                                    <el-tag size="mini" v-else-if="scope.row.fusertype=='2'">企业</el-tag>
                                    <el-tag size="mini" type="success" v-else-if="scope.row.fusertype=='1'">个人</el-tag>
                                    {{ scope.row.fname_n }}
                                </div>
                                <div style="padding-top:8px;">
                                    <div>用户名：{{ scope.row.fusername }}</div>
                                    <div>手机号：{{ scope.row.fmoblie }}</div>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="接收人" width="300">
                    <template slot-scope="scope">
                        <div class="chat_sftag">
                            <div class="chat_sftag_r">
                                <div>
                                    <el-tag size="mini" v-if="scope.row.tusertype=='3'">猎头</el-tag>
                                    <el-tag size="mini" v-else-if="scope.row.tusertype=='2'">企业</el-tag>
                                    <el-tag size="mini" type="success" v-else-if="scope.row.tusertype=='1'">个人</el-tag>
                                    {{ scope.row.tname_n }}
                                </div>
                                <div style="padding-top:8px;">
                                    用户名：{{ scope.row.tusername }}
                                    <div>手机号：{{ scope.row.tmoblie }}</div>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="neirong" label="聊天内容" min-width="300">
                    <template slot-scope="scope">
                        <div v-if="scope.row.mtype=='img'">
                            <el-tooltip class="item" effect="dark" content="提示文字" placement="top-start">
                                <el-button type="info" icon="el-icon-picture" circle></el-button>
                                <template slot="content">
                                    <el-image :src="scope.row.content" fit="contain"></el-image>
                                </template>
                            </el-tooltip>
                        </div>
                        <div v-else-if="scope.row.mtype=='voice'">
                            <audioyy :src="scope.row.content"></audioyy>
                        </div>
                        <div v-else>
                            <div v-if="scope.row.content.length<30">{{ scope.row.content }}</div>
                            <div v-else>{{ scope.row.content.slice(0, 30) }}...
                                <el-link type="primary" @click="detailmsg(scope.row.content)">更多</el-link>
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column prop="sendTime_n" label="时间" width="150" sortable="custom" :sort-orders="['descending','ascending']"></el-table-column>
                <el-table-column label="状态" width="100">
                    <template slot-scope="scope">
                        <el-tag type="success" size="mini" v-if="scope.row.status == 1">已读</el-tag>
                        <el-tag type="danger" size="mini" v-else-if="scope.row.status == 2">未读</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="140" fixed="right" header-align="center">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="small " plain @click="showChat(scope.row.id)">查看</el-button>
                            <el-button type="danger" size=" " @click="deleteinfo(scope.row.id)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulecz">
                <el-checkbox v-model="allchecked" @change="allcheckChange">全选</el-checkbox>
                <el-button size="mini" @click="deleteAll">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total" layout="total, sizes, prev, pager, next, jumper"></el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog :title="chattitle" :visible.sync="chatshow" :with-header="true" :modal-append-to-body="false" append-to-body :show-close="true" width="500px">
                <chatlog :chatid="chatid" v-if="chatshow" @set-title="settitle"></chatlog>
            </el-dialog>
        </div>
    </div>
</template>

<script>

    window.audioList = [];
    module.exports = {
        props: {
            keyword_v: {
                type: String,
                default: ''
            },
            searchclass: {
                type: String,
                default: ''
            }
        },
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
                status: '',
                daterange: [],
                sort_t: '',
                order: '',

                chatshow: false,
                chatid: '',
                chattitle: '聊天',

                islook: false,
                searchClass: ''
            }
        },
        components: {
            'chatlog': httpVueLoader('./chatlog.vue'),
            'audioyy': httpVueLoader('../../../component/audioyy.vue')
        },
        watch: {
            keyword_v: {
                handler(val) {
                    if (val) {
                        this.keyword = val;
                        this.type = '2';
                        this.status = '2';
                    }
                },
                immediate: true,
                deep: true,
            },
            searchclass: {
                handler(val) {
                    this.searchClass = val;
                    if (val == 'drawer') {
                        this.getList();
                    }
                },
                immediate: true,
                deep: true,
            },
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
                    status: that.status
                }
                if (this.daterange && this.daterange.length > 0) {

                    params['date1'] = this.daterange[0];
                    params['date2'] = this.daterange[1];
                }
                this.list_loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=tool&c=chatlog&a=index', params, {hideloading: true}).then((result) => {
                    this.list_loading = false;
                    var res = result.data;
                    if (res.error == 0) {

                        that.tableData = res.data.list
                        that.total = parseInt(res.data.total)
                        that.page_sizes = res.data.page_sizes
                        that.limit = res.data.page_size
						
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

                if (e.prop == 'sendTime_n') {
                    this.sort_t = 'sendTime';
                }
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
                httpPost('m=tool&c=chatlog&a=dellog', params).then(function (result) {
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
            detailmsg(msg) {
                this.$alert(msg, {
                    confirmButtonText: '确定',
                });
            },
            showChat: function (id) {
                var that = this;
                this.chatid = id;
                that.chatshow = true;
            },
            settitle: function (obj) {
                let title = obj.title;
                if(obj.title.length>20){
                    title = obj.title.slice(0,20)+"...";
                }
                this.chattitle = title;
            },
            doLayout() {
                if (this.$refs.table) {
                    this.$nextTick(() => {
                        this.$refs.table.doLayout();
                    })
                }
            }
        }
    };
</script>
<style scoped>
    .moduleSeachmore{padding:0}
    .moduleSeachs{padding:0 0 10px 0;width:100%}
    .tableSeachInptsmalltwo{margin-bottom:0;margin-right:12px}
    .tableSeachInptsmalltwo .el-input__inner{height:32px;line-height:32px;width:260px;padding:0 5px}
    .el-dialog__body{padding:0 20px}
    .pad20{padding:0 20px}
    .moduleInptList .el-select .el-input{width:130px}
</style>