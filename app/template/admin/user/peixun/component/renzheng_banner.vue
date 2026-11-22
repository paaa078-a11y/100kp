<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" @keyup.enter.native="search" placeholder="输入你要搜索的关键字" v-model="searchForm.keyword"
                              clearable class="input-with-select">
                        <el-select v-model="searchForm.type" slot="prepend" placeholder="请选择">
                            <el-option label="机构全称" :value="1"></el-option>
                            <el-option label="用户ID" :value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div v-for="(searchItem, searchIndex) in searchList" :key="searchIndex" class="moduleInptList">
                    <el-select size="small" v-model="searchForm[searchItem.param]" slot="prepend" :clearable="true" :placeholder="searchItem.name" @change="search">
                        <el-option v-for="(searchLabel, searchValue) in searchItem.value" :key="searchValue" :label="searchLabel" :value="searchValue"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable moduleElMoreSixd" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="list" stripe style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="uid" label="用户ID" width="120">
                </el-table-column>
                <el-table-column prop="name" label="机构名称" width="350">
                </el-table-column>
                <el-table-column label="图片" align="center">
					<template slot-scope="scope">
                        <div>
                            <el-image :src="scope.row.pic" style="width: 360px; height: 48px"
                                      :preview-src-list="[scope.row.pic]">
                            </el-image>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="审核状态" align="center">
                    <template slot-scope="scope">
                        <div class="admin_state">
                            <span v-if="scope.row.status == 0" class="admin_state1">已审核</span>
                            <span v-else-if="scope.row.status == 2" class="admin_state2">未通过</span>
                            <span v-else-if="scope.row.status == 1" class="admin_state5">未审核</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="220">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="openAudit(scope.row)">审核</el-button>
                            <el-button size="mini" @click="openEdit(scope.row)">修改</el-button>
                            <el-button size="mini" @click="del(scope.$index)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate"
                             @change="checkAll">全选</el-checkbox>
                <el-button @click="batch('del')" size="mini">批量删除</el-button>
                <el-button @click="batch('audit')" size="mini">批量审核</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="page" :page-sizes="pageSizes" :page-size="limit"
                               layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <!-- 弹窗 -->
        <div class="modluDrawer">
            <el-dialog title="培训横幅审核" :visible.sync="dialogAudit" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="500px">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核操作：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio v-model="ruleFormAudit.status" label="0">正常</el-radio>
                            <el-radio v-model="ruleFormAudit.status" label="2">未通过</el-radio>
                        </div>
                        <div class="toolClasTipse">
                            <el-alert title="审核未通过将删除图片" type="warning" show-icon :closable="false">
                            </el-alert>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" :rows="2" placeholder="" v-model="ruleFormAudit.statusbody">
                            </el-input>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogAudit = false">取 消</el-button>
                    <el-button type="primary" @click="submitAudit" :disabled="saveLoading">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <div class="modluDrawer">
            <el-dialog title="修改机构横幅" :visible.sync="dialogEdit" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="500px">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>机构全称：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input :value="detail.name" disabled></el-input>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>上传图片：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-upload class="avatar-uploader" list-type="picture" action="" :auto-upload="false"
                                       :on-change="handleChangePic" :show-file-list="false" :accept="pic_accept">
                                <img v-if="ruleFormEdit.pic" :src="ruleFormEdit.pic" class="avatar">
                                <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                            </el-upload>
                        </div>
                        <div class="toolClasTipse">
                            <el-alert title="说明：横幅的尺寸为1200*160" type="info" show-icon :closable="false">
                            </el-alert>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogEdit = false">取 消</el-button>
                    <el-button type="primary" @click="submitEdit" :disabled="saveLoading">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    props: {
        status: {type: String, default: ''}
    },
    data: function () {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            loading: false,
            dataText: '数据加载中',
            // 搜索筛选项
            searchList: [],
            searchForm: {
                type: 1,
                status: parseInt(this.status),
            },

            // 列表
            page: 1,
            limit: 0,
            list: [],
            total: 0,
            pageSizes: [],

            checkedAll: false, // 全选
            checkedAllIndeterminate: false,
            multipleSelection: [], // 多选值存储
            idArr: [],

            detail: {},

            saveLoading: false,

            // 审核
            dialogAudit: false,
            ruleFormAudit: {},

            // 修改
            dialogEdit: false,
            ruleFormEdit: {},

            prevPage: 0
        }
    },

    mounted() {
        var that = this
        setTimeout(function () {
            that.getSearchFun();
        }, 200)
    },
    created() {
        if (this.status == ''){
            this.searchForm.status = '';
        }
        this.init();
    },
    methods: {
        init() {
            // this.resetSearch();
            this.search();
        },
        getSearchFun:function(){
            let that = this;
            httpPost('m=user&c=peixun_renzheng&a=getBannerSearchData', {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    that.searchList = res.data.search_list;
                }
            })
        },
        resetSearch() {
            this.searchForm = {
                type: 1
            };
            this.limit = 0;
        },

        handleSizeChange(val) {
            this.limit = val;
            this.getList();
        },
        handleCurrentChange(val) {
            this.page = val;
            this.getList();
        },
        search() {
            this.page = 1;
            this.getList();
        },
        getList() {
            let that = this,
                searchForm = that.searchForm,
                params = {
                    page: that.page,
                    limit: that.limit
                };
                that.loading = true;

            httpPost('m=user&c=peixun_renzheng&a=banner', { ...params, ...searchForm }, {hideloading: true}).then(function (response) {
                let res = response.data,
                    data = res.data;

                that.list = data.list;
                that.total = parseInt(data.total);
                that.pageSizes = data.page_sizes;
                if (that.limit === 0) {
                    that.limit = parseInt(data.limit); // 取系统配置默认数量
                }
                if (that.page > data.page) {
                    that.page = parseInt(data.page); // 最后一页被删除后，取最新的页数
                }
                that.loading = false;
                if(that.prevPage != that.page){
                    that.prevPage = that.page;
                    that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                }
                if (that.list.length === 0) {
                    that.dataText = "暂无数据";
                }
            })
        },

        // 批量操作
        handleSelectionChange(val) {
            if (val.length == 0) {
                this.checkedAll = false;
                this.checkedAllIndeterminate = false;
            } else {
                if (val.length === this.list.length) {
                    this.checkedAll = true;
                    this.checkedAllIndeterminate = false;
                } else {
                    this.checkedAll = false;
                    this.checkedAllIndeterminate = true;
                }
            }
            this.multipleSelection = val;
        },
        batch(type) {
            if (this.multipleSelection.length == 0 && type == 'del') {
                message.error('请选择要删除的数据');
                return false;
            }else if (this.multipleSelection.length == 0){
                message.error('请选择要操作的数据项');
                return false;
            }

            let idArr = [];
            this.multipleSelection.forEach(function (item) {
                idArr.push(item.id);
            })
            this.idArr = idArr;

            if (type == 'del') {
                this.del();
            } else if (type == 'audit') {
                this.openAudit();
            }
        },
        checkAll(val) {
            val ? this.checkedAllIndeterminate = false : '';
            this.$refs.multipleTable.toggleAllSelection();
        },

        del(idx) {
            let that = this,
                params = {
                    type: 'banner'
                },
                msg = '';

            if (typeof idx == 'undefined') { // 批量删除
                params.del = this.idArr;
                msg = '你确定要删除选中项吗？';
            } else {// 单个删除
                params.del = that.list[idx].id;
                msg = '你确定要删除当前项吗？';
            }

            delConfirm(this, params, function (params) {
                httpPost('m=user&c=peixun_renzheng&a=delPic', params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        that.getList();
                        that.$refs.multipleTable.clearSelection();
                        message.success(res.data.msg);
                    }
                })
            }, msg)
        },

        openAudit(row) {
            if (typeof row !== 'undefined') {
                this.detail = row;
                this.ruleFormAudit = {
                    id: row.id,
                    status: row.status,
                    statusbody: row.statusbody
                };
            } else {
                this.detail = {};
                this.ruleFormAudit = {
                    id: this.idArr,
                    status: '',
                    statusbody: ''
                };
            }

            this.dialogAudit = true;
        },

        submitAudit() {
            let that = this,
                params = that.ruleFormAudit;

            if (typeof params.status == 'undefined' || params.status === '' || params.status == 1) {
                message.error('请选择审核状态');
                return false;
            }

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            httpPost('m=user&c=peixun_renzheng&a=bannerStatus', params).then(function(res) {
                if (res.data.error > 0) {
                    that.saveLoading = false;
                    message.error(res.data.msg);
                } else {
                    that.dialogAudit = false;
                    that.getList();
                    message.success(res.data.msg, function () {
                        that.$refs.multipleTable.clearSelection();
                        that.saveLoading = false;
                    });
                }
            })
        },

        openEdit(row) {
            this.detail = row;
            this.ruleFormEdit = {
                type: 'banner',
                id: row.id,
                pic: row.pic
            };
            this.dialogEdit = true;
        },

        // 上传时触发
        handleChangePic(file, fileList) {
            this.$set(this.ruleFormEdit, 'file', file.raw);
            this.$set(this.ruleFormEdit, 'pic', file.url);
        },

        submitEdit() {
            let that = this,
                ruleForm = that.ruleFormEdit,
                formData = new FormData();

            if (typeof ruleForm.file === 'undefined' || ruleForm.file == '') {
                message.error('请上传图片');
                return false;
            }

            that.saveLoading = true;

            $.each(ruleForm, function (key, value) {
                if (key != 'pic') {
                    formData.append(key, value);
                }
            });

            httpPost('m=user&c=peixun_renzheng&a=uploadsave', formData).then(function (res) {
                if (res.data.error > 0) {
                    that.saveLoading = false;
                    message.error(res.data.msg);
                } else {
                    that.dialogEdit = false;
                    that.getList();
                    message.success(res.data.msg, function () {
                        that.saveLoading = false;
                    });
                }
            })
        },
    },
};
</script>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 60px;
    margin: 3px 0;
}

/* 上传样式开始 */
.avatar-uploader .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.avatar-uploader .el-upload:hover {
    border-color: #409EFF;
}

.avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 360px;
    height: 48px;
    line-height: 48px;
    text-align: center;
}

.avatar {
    width: 360px;
    height: 48px;
    display: block;
}
/* 上传样式结束 */

.fenpeizhand .toolClasList{
    flex-wrap: wrap;
}
.toolClasTipse{
    overflow: hidden;
    position: relative;
    padding-left: 75px;
    width: calc(100% - 75px);
}
.toolClasTipse .el-alert{
    overflow: hidden;
    position: relative;
    padding: 6px 0;
    background: none;
}
</style>