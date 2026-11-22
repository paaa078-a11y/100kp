<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input placeholder="请输入搜索内容" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select" size="small" clearable>
                        <el-select v-model="search.type" size="small" slot="prepend" placeholder="企业名称">
                            <el-option label="公司名称" value="1"></el-option>
                            <el-option label="用户ID" value="2"></el-option>
                            <el-option label="姓名" value="3"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="search.status" size="small" slot="prepend" placeholder="招聘状态" clearable @change="doUserQuery">
                        <el-option v-for="(value,key) in statusArr" :key="key" :label="value" :value="key"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable" style="border: 1px solid #ebeef5; height: calc(100% - 102px); width: calc(100% - 2px);">
            <el-table :data="tableData" stripe style="width: 100%" @selection-change="selectChange" @sort-change="shortChange" ref="multipleTable"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="realname" label="名称" min-width="200"></el-table-column>
                <el-table-column label="查看" width="160" align="center">
					<template slot-scope="scope">
                        <div>
                            <el-image shape="square" style="width: 80px; height: 80px" :src="scope.row.photo_big"></el-image>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="usinfo" label="审核状态" min-width="200">
                    <template slot-scope="scope">
                        <template v-if="scope.row.photo_status == 0">
                            <span class="admin_state1">已审核</span>
                        </template>
                        <template v-if="scope.row.photo_status == 1">
                            <span class="admin_state4">未审核</span>
                        </template>
                        <template v-if="scope.row.photo_status == 2">
                            未通过
                        </template>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="220">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="shenhe(scope.row)">审核</el-button>
                            <el-button size="mini" @click="editFun(scope.row)">修改</el-button>
                            <el-button size="mini" type="danger" @click="del(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging" style="padding-top: 10px;">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="isIndeterminate" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="batchDel" size="mini">批量删除</el-button>
                <el-button @click="batchShenhe" size="mini">批量审核</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination :total="total" @current-change="userPageChange"
                               :page-size="pageSize" :page-sizes="pageSizes" @size-change="userPageSizeChange"
                               :current-page.sync="page" layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="修改猎头头像" :visible.sync="drawer2" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="500px">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>姓名：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input  placeholder="请输入内容" v-model="detail.realname" :disabled="true">
                            </el-input>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>上传图片：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-upload class="avatar-uploader" list-type="picture" :accept="pic_accept" action=""
                                       :auto-upload="false" :on-change="handleChangeWxewm" :show-file-list="false">
                                <img v-if="detail.photo_big_n" :src="detail.photo_big_n" class="avatar">
                                <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                            </el-upload>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
					<el-button @click="drawer2 = false">取 消</el-button>
					<el-button type="primary" @click="updateInfo">确 定</el-button>
				</span>
            </el-dialog>
        </div>
        <div class="modluDrawer">
            <el-dialog title="审核图片" :visible.sync="drawer3" :with-header="true" :modal-append-to-body="false"
                       :show-close="true" width="500px">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核操作：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio-group v-model="drawerRadio">
                                <el-radio label="0">正常</el-radio>
                                <el-radio label="2">未通过</el-radio>
                            </el-radio-group>
                        </div>
                    </div>
                    <template>
                        <div class="toolClasList">
                            <div class="toolClasTite">
                                <span>审核说明：</span>
                            </div>
                            <div class="toolClasCont">
                                <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="drawerTextarea">
                                </el-input>
                            </div>
                        </div>
                    </template>
                </div>
                <span slot="footer" class="dialog-footer">
					<el-button @click="drawer3 = false">取 消</el-button>
					<el-button type="primary" @click="drawerSave" :disabled="submitLoading">确 定</el-button>
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
            checkedAll:false,
            isIndeterminate: false,// checkbox 的不确定状态
            statusArr:{
                0 : '已审核',
                1 : '未审核'
            },
            tableData: [],
            uri: "m=user&c=",
            search:{
                type:'1',
                status: this.status,
            },
            total: 0,
            page: 1,
            idsArr: [],
            pageSize: 0,
            pageSizes:[],
            drawerRadio:"",
            drawerTextarea:"",
            drawer3:false,
            drawer2:false,
            edit:{},
            detail:{
                realname: '',
                photo_big_n:'',
                uid:''
            },
            submitLoading: false,

            prevPage: 0
        }
    },
    created(){
        this.getList();
    },
    methods: {
        selectAllBottom:function (value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择要删除的数据');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_lt_pic&a=del'
            delConfirm(this, { del: ids }, function (params) {
                httpPost(url, params).then(function(response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, _this.getList());
                    } else {
                        message.error(res.msg);
                    }
                })
            }, '确定要删除？')
        },
        batchShenhe: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择要操作的数据项');
                return
            }
            this.drawerRadio='';
            this.drawerTextarea='';
            this.batch = true;
            this.drawer3 =true;
        },
        selectChange:function (val) {
            this.idsArr = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.idsArr.push(item.uid);
                });
            }
            if (_this.idsArr.length == 0) {
                _this.checkedAll = false;
                this.isIndeterminate = false;
            } else {
                if (_this.idsArr.length == _this.tableData.length) {
                    _this.checkedAll = true;
                    this.isIndeterminate = false;
                } else {
                    _this.checkedAll = false;
                    this.isIndeterminate = true;
                }
            }
        },
        shortChange(e) {
            let orderMap = {ascending: 'asc', descending: 'desc'}
            this.search.t = e.order ? e.prop : null;
            this.search.order = orderMap[e.order];
            this.page = 1;
            this.getList();
        },
        doUserQuery() {
            this.page = 1
            this.getList()
        },
        userPageChange(val) {
            this.page = val
            this.getList()
        },
        userPageSizeChange(val) {
            this.pageSize = val
            this.getList()
        },
        getList:function(){
            let _this = this;
            let url = _this.uri + 'admin_lt_pic&a=index';
            _this.search.page = this.page;
            _this.search.pageSize = this.pageSize;
            _this.loading  =true;
            httpPost(url, _this.search, {hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.tableData = res.data.data;
                    _this.total = res.data.total;
                    _this.loading = false;
                    _this.pageSizes =res.data.pageSizes;
                    if(_this.prevPage != _this.page){
                        _this.prevPage = _this.page;
                        _this.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                    }
                    if (_this.tableData.length === 0) {
                        _this.dataText = "暂无数据";
                    }
                }
            })
        },
        shenhe:function(detail){
            this.detail = detail;
            this.drawerTextarea = detail.photo_statusbody
            this.drawerRadio = detail.photo_status
            this.drawer3=  true;
        },
        del: function (detail) {
            let _this = this;
            let url = this.uri + 'admin_lt_pic&a=del';
            delConfirm(this, { del: detail.uid }, function (params) {
                httpPost(url, params).then(function(response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, _this.getList());
                    } else {
                        message.error(res.msg);
                    }
                })
            }, '确定要删除？')
        },
        drawerSave:function () {
            let sendData = {
                status :this.drawerRadio,
                statusbody :this.drawerTextarea,
            }
            if (this.drawerRadio != '0' && this.drawerRadio != '2') {
                message.error('请选择操作审核');
                return;
            }
            if (!this.batch){
                sendData.sid =  this.detail.uid;
            }else {
                sendData.sid = this.idsArr.join(',');
            }
            let url = this.uri+'admin_lt_pic&a=status';
            let _this = this;
            _this.submitLoading = true;

            httpPost(url, sendData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg, _this.getList());
                } else {
                    message.error(res.msg);
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                _this.submitLoading = false;
            });
            this.drawer3 = false;
        },
        editFun:function(detail){
            this.detail.realname = detail.realname;
            this.detail.photo_big_n = detail.photo_big;
            this.detail.uid = detail.uid;
            this.drawer2=  true;
        },
        // 上传时触发
        handleChangeWxewm(file, fileList) {
            console.log(fileList);
            this.edit.file = file.raw;
            this.$set(this.detail, 'photo_big_n', file.url);
        },
        updateInfo:function(){
            let _this = this;
            _this.edit.type = 'photo';
            _this.edit.id = this.detail.uid;
            let formData = new FormData();
            $.each(_this.edit, function (key, value) {
                formData.append(key, value);
            });
            let url = this.uri+'admin_lt_pic&a=uploadsave';
            httpPost(url, formData).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg, _this.getList());
                } else {
                    message.error(res.msg);
                }
            })
            this.drawer2 = false;
        }

    },
};
</script>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
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
    width: 148px;
    height: 148px;
    line-height: 148px;
    text-align: center;
}
.avatar {
    width: 148px;
    height: 148px;
    display: block;
}

.moduleElTaCaoz .el-button {
    width: 60px;
    margin: 3px 0;
}
</style>