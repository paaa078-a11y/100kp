<template>
    <div class="moduleElHight">
        <div class="tableDome_tip">
            <el-alert title="由于2021-05-27 12:00微信接口更新，目前仅可在粉丝关注公众号后1分钟内推送至多3条信息" type="success" :closable="false">
            </el-alert>
        </div>
        <div class="moduleSeachs">
            <div class="moduleSeachleft">

                <div class="tableSeachInpt" style="margin-bottom: 0px;;">
                    <el-input clearable placeholder="搜索二维码名称" v-model="keyword" size="small"
                              prefix-icon="el-icon-search">
                    </el-input>
                </div>

                <div class="tableSeachInpt" style="margin-bottom: 0px;;">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
            <div class="moduleSeachButn">
                <el-button type="primary" icon="el-icon-document-add" size="mini" @click="editinfo('')">新建渠道码
                </el-button>
            </div>
        </div>

        <div class="moduleElTable">
            <el-table ref="table" :data="tableData" v-loading="list_loading" @selection-change="selectionChange" border
                      style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                      :empty-text="emptytext">
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="编号" width="50">
                </el-table-column>
                <el-table-column prop="title" label="二维码名称" width="200">
                </el-table-column>
                <el-table-column prop="follow_num" label="关注次数" width="100">
                </el-table-column>
                <el-table-column prop="time_n" label="创建时间" width="100">
                </el-table-column>
                <el-table-column prop="expire_seconds_n" label="到期时间	" width="100">
                </el-table-column>
                <el-table-column label="粉丝标签	">
                    <template slot-scope="scope">
                        <div class="tag-group">
                            <el-tag v-for="tag in scope.row.tags_name_arr" :key="tag"
                                    style="margin-left:5px;margin-top:5px"
                                    type="success" size="mini" effect="plain">
                                {{ tag }}
                            </el-tag>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="下载渠道码	" width="100">
                    <template slot-scope="scope">
                        <el-button size="mini" @click="downQdmPic(scope.row.id)" v-if="scope.row.work == 1" type="text">
                            <i class="el-icon-download el-icon--left"></i>下载渠道码
                        </el-button>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="190" fixed="right" header-align="center">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" @click="showInfo(scope.row.id)">详情</el-button>

                            <el-button size="mini" @click="editinfo(scope.row.id)">修改</el-button>
                            <el-button size="mini" @click="deleteinfo(scope.row.id)" type="danger">删除</el-button>
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
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total"
                               layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>

        <div class="modluDrawer">

            <el-drawer title="渠道码二维码详情" :visible.sync="infoshow" :modal-append-to-body="false" :show-close="true"
                       size="70%">
                <qdshow :qid="qid" @todetail="todetail" @downqdm="downQdm"></qdshow>
            </el-drawer>
        </div>

        <div class="modluDrawer">
            <el-drawer title="渠道码" :visible.sync="editshow" :modal-append-to-body="false" :show-close="true" size="950px">
                <qdedit :qid="edit_id" @editclose="editClose"></qdedit>
            </el-drawer>
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
            sy_logo: '',
            sort_t: '',
            order: '',

            infoshow: false,
            qid: '',

            editshow: false,
            edit_id: '',
        }
    },
    components: {
        'qdshow': httpVueLoader('../component/qdshow.vue'),
        'qdedit': httpVueLoader('../component/qdedit.vue')
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

            }

            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=tool&c=qdm&a=index', params, {hideloading: true}).then((result) => {
                this.list_loading = false;
                var res = result.data;

                if (res.error == 0) {
                    that.tableData = res.data.list;
                    that.sy_logo = res.data.sy_logo
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
        showInfo: function (id) {
            this.qid = id;
            this.infoshow = true;
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

            httpPost('m=tool&c=qdm&a=del', params).then(function (result) {

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
        downQdm: function (e) {
            this.downQdmPic(e.id);
        },
        downQdmPic: function (id) {

            httpPost('m=tool&c=qdm&a=downEwm', {id: id}).then(function (result) {

                var res = result.data;
                if (res.error == 0) {
                    var a = document.createElement('a');
                    var event = new MouseEvent('click');
                    a.download = res.data.name;
                    a.href = res.data.picurl;
                    a.dispatchEvent(event);
                } else {
                    message.error(res.msg);
                    return;
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        todetail: function (e) {
            this.infoshow = false;
            this.editinfo(e.id);
        },
        editinfo: function (id) {

            this.edit_id = id;
            this.editshow = true;
        },
        editClose: function () {
            this.editshow = false;
            this.getList()
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
    padding: 0px 5px;
}

.el-dialog__body {
    padding: 0px 20px;
}
</style>