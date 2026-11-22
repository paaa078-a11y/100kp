<template>
    <div class="moduleElHight">
        <div class="modulemoreSeach">
            <div class="moduleSeachleft">
                <div class="tableSeachInpt">
                    <el-input placeholder="请输入搜索内容" v-model="keyword" clearable size="small" prefix-icon="el-icon-search">
                    </el-input>
                </div>
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
            <div class="moduleSeachButn">
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-document-add" size="mini" @click="toadd('')">新增区域</el-button>
                </div>
            </div>
        </div>

        <div class="admin_datatip"><i class="el-icon-document"></i> 可以实现区域、展位等进行自主设置，企业可在线付费报名参加招聘会等操作
        </div>
        <div class="moduleElTable moduleElMoreLive"
             style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe @selection-change="handleSelectionChange"
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" ref="multipleTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" width="80" sortable="custom"></el-table-column>
                <el-table-column prop="name" label="名称">
                    <template slot-scope="scope">
                        <el-input v-if="scope.row[scope.column.property + 'isShow']"
                                  :ref="scope.column.property + scope.$index" :id="scope.column.property + scope.$index"
                                  v-model="scope.row.name" @blur="alterData(scope, 1)"></el-input>
                        <span v-else>一级分类：{{scope.row.name}}
                            <img src="../../../admin/images/bine.png" alt="" style="margin-left: 4px;" width="14"
                                 height="14" @click="editData(scope, 1)">
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="sort" label="排序">
                    <template slot-scope="scope">
                        <el-input v-if="scope.row[scope.column.property + 'isShow']"
                                  :ref="scope.column.property + scope.$index" :id="scope.column.property + scope.$index"
                                  v-model="scope.row.sort" @blur="alterData(scope, 1)"></el-input>
                        <span v-else>{{ scope.row.sort }}
                            <img src="../../../admin/images/bine.png" alt="" style="margin-left: 4px;" width="14"
                                 height="14" @click="editData(scope, 1)">
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" fixed="right" width="200">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="small" plain @click="todtl(scope.row.id)">详细</el-button>
                            <el-button type="danger" size="mini" @click="delrow(scope.row.id, 1)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="delAllBottom(1)" size="mini">批量删除</el-button>
            </div>
        </div>
        <!-- 场地详细 -->
        <el-drawer title="场地详细" :visible.sync="drawerdtl" append-to-body :modal-append-to-body="false" size="80%">
            <div class="tctable_cont">
                <div class="moduleSeachs">
                    <div class="">

                    </div>
                    <div class="nrtopbtn">
                        <el-button type="primary" icon="el-icon-document-add" @click="toadd('')" size="mini">添加区域
                        </el-button>
                    </div>
                </div>
                <el-table :data="spaceTableData" style="width: 100%;" row-key="id" border
                          default-expand-all :tree-props="{children: 'children', hasChildren: 'hasChildren'}"
                          :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="calc(100% - 55px)" @selection-change="handleSubTblSelectionChange" ref="multipleSubTable" v-loading="loading" :empty-text="emptytext">
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column prop="id" label="编号" width="180">
                    </el-table-column>
                    <el-table-column label="名称" property="name">
                        <template slot-scope="scope">
                            <el-input v-if="scope.row[scope.column.property + 'isShow']"
                                      :ref="'edit_' + scope.column.property + scope.$index" :id="'edit_' + scope.column.property + scope.$index"
                                      v-model="scope.row.name" @blur="alterData(scope, 2)"></el-input>
                            <span v-else>{{ scope.row.name }}
                            <img src="../../../admin/images/bine.png" alt="" style="margin-left: 4px;" width="14"
                                 height="14" @click="editData(scope, 2)">
                        </span>
                        </template>
                    </el-table-column>
                    <el-table-column label="排序" property="sort">
                        <template slot-scope="scope">
                            <el-input v-if="scope.row[scope.column.property + 'isShow']"
                                      :ref="scope.column.property + scope.$index" :id="scope.column.property + scope.$index"
                                      v-model="scope.row.sort" @blur="alterData(scope, 2)"></el-input>
                            <span v-else>
                                {{ scope.row.sort }}
                                <img src="../../../admin/images/bine.png" alt="" style="margin-left: 4px;" width="14"
                                     height="14" @click="editData(scope, 2)">
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="140" fixed="right">
                        <template slot-scope="scope">
                            <div class="cz_button">
                                <el-button type="danger" size="mini"
                                           @click="delrow(scope.row.id, 2)">删除
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="modulePaging" style="padding-left: 20px;">
                <div class="">
                    <div class="">
                        <el-checkbox v-model="checkedSubTblAll" @change="selectSubTblAllBottom">全选</el-checkbox>
                        <el-button @click="delAllBottom(2)" size="mini">批量删除</el-button>
                    </div>
                </div>
            </div>
        </el-drawer>
        <!--新增区域-->
        <div class="modluDrawer">
            <el-dialog title="分配展会区域" width="400px" :visible.sync="draweradd" append-to-body
                       :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite" style="width: 90px;">
                            <span>类别选择：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio v-model="type" label="1">一级类别</el-radio>
                            <el-radio v-model="type" label="2">二级类别</el-radio>
                        </div>
                    </div>
                    <div class="toolClasList" v-if="type == '2'">
                        <div class="toolClasTite">
                            <span>一级分类：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-select v-model="fir_id" placeholder="请选择">
                                <el-option label="请选择" value="">
                                </el-option>
                                <el-option v-for="item in tableData" :key="item.id" :label="item.name" :value="item.id">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>区域名称：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input  type="textarea" :rows="2" placeholder="请输入区域名称" v-model="name">
                            </el-input>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span></span>
                        </div>
                        <div class="toolClasCont">
                            <el-alert title="可以添加多条分类（请按回车键换行，一行一个）" type="info" show-icon
                                      :closable="false">
                            </el-alert>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="draweradd = false">取 消</el-button>
                    <el-button type="primary" @click="save">确 定</el-button>
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
                type: '',
                name: '',
                fir_id: '',
                keyword: '',
                checkedAll: false,
                checkedSubTblAll: false,
                selectedItem: [],
                tableData: [],
                spaceTableData: [],
                curr_data: {},
                draweradd: false,
                drawerdtl: false,
                secClass: [],
                tableHig: true,
                del_type: 1,
                del_id: '',
                islook: false
            }
        },
        mounted() {

        },
        methods: {
            todtl(id){
                this.del_id = id
                this.getdtlinfo()
            },
            getdtlinfo(){
                var that = this
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=neirong&c=zphnet&a=classManage', {id: that.del_id}, {hideloading: true}).then(function (response) {
                    let res = response.data;
                    if (res.error === 0) {
                        that.spaceTableData = res.data.class
                        if (that.spaceTableData.length > 0) {
                            that.drawerdtl = true
                        } else {
                            that.drawerdtl = false
                        }
                        that.loading = false;
                    } else {
                        message.error('修改失败');
                    }
                    if (that.spaceTableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
            handleSubTblSelectionChange(val) {
                this.selectedItem = [];
                let _this = this;
                if (val.length) {
                    val.forEach(item => {
                        _this.selectedItem.push(item.id);
                    });
                }
                if (_this.selectedItem.length == 0) {
                    _this.checkedSubTblAll = false;
                } else {
                    var all_len = 1
                    if (this.spaceTableData[0].children) {
                        this.spaceTableData[0].children.forEach((row) => {
                            if (row.children) {
                                all_len += row.children.length
                            }
                        })
                        all_len += this.spaceTableData[0].children.length
                    }
                    if (_this.selectedItem.length == all_len) {
                        _this.checkedSubTblAll = true;
                    } else {
                        _this.checkedSubTblAll = false;
                    }
                }
            },
            selectSubTblAllBottom(value) {
                // 处理嵌套数据选中
                if (this.spaceTableData[0].children) {
                    this.spaceTableData[0].children.forEach((row) => {
                        if (row.children) {
                            row.children.forEach((subrow) => {
                                if(this.selectedItem.indexOf(subrow.id) < 0) {// 三级分类选中处理
                                    this.$refs.multipleSubTable.toggleRowSelection(subrow, value)
                                    if (value) {
                                        this.selectedItem.push(subrow.id)
                                    }
                                }
                            })
                        }
                        if(this.selectedItem.indexOf(row.id) < 0) {// 二级分类选中处理
                            this.$refs.multipleSubTable.toggleRowSelection(row, value)
                            if (value) {
                                this.selectedItem.push(row.id)
                            }
                        }
                    })
                }
                if (!value) {
                    this.selectedItem = []
                }
                value ? this.$refs.multipleSubTable.toggleAllSelection() : this.$refs.multipleSubTable.clearSelection();
            },
            editData(scope, type) {
                let index = scope.$index;
                let row = scope.row;
                let column = scope.column;
                this.oldData = JSON.parse(JSON.stringify(row));
                let copyRow = JSON.parse(JSON.stringify(row));
                if (type == 1) {
                    copyRow[column.property + "isShow"] = true;
                    this.$set(this.tableData, index, copyRow);
                } else {
                    if (row.keyid == '0') {// 一级分类修改
                        this.spaceTableData[0][column.property + "isShow"] = true;
                    } else {// 二级分类修改
                        this.spaceTableData[0].children.forEach((item)=>{
                            if (item.id == row.id) {
                                item[column.property + "isShow"] = true
                            }
                        })
                    }
                    var tmp = deepClone(this.spaceTableData)
                    this.spaceTableData = tmp
                }
                this.$nextTick(() => {
                    let ref = null
                    if (type == 1) {
                        ref = column.property + index;
                    } else {
                        ref = 'edit'+column.property + index;
                    }
                    $("#" + ref).focus();
                });
            },
            alterData(scope, type) {
                if (this.oldData == null) {
                    return false;
                }
                let index = scope.$index;
                let row = scope.row;
                let column = scope.column;
                let copyRow = JSON.parse(JSON.stringify(row));
                if (type == 1) {
                    copyRow[column.property + "isShow"] = false;
                    this.$set(this.tableData, index, copyRow);
                } else {// 管理弹窗表格修改名称、排序
                    if (row.keyid == '0') {// 一级分类修改
                        this.spaceTableData[0][column.property + "isShow"] = false;
                    } else {// 二级分类修改
                        this.spaceTableData[0].children.forEach((item)=>{
                            if (item.id == row.id) {
                                item[column.property + "isShow"] = false
                            }
                        })
                    }
                    var tmp = deepClone(this.spaceTableData)
                    this.spaceTableData = tmp
                }
                if (row[column.property] === this.oldData[column.property]) {
                    return false;
                }
                let _this = this;
                let sendData = {id: row.id};
                sendData[column.property] = row[column.property];
                httpPost('m=neirong&c=zphnet&a=ajax', sendData, {hideloading: true}).then(function (response) {
                    let res = response.data;
                    if (res.error === 0) {
                        message.success('修改成功');
                    } else {
                        message.error('修改失败');
                    }
                    _this.oldData = null;
                    // if (type == 1) {
                    _this.getList();
                    // }
                }).catch(function (error) {
                    console.log(error);
                });
            },
            toadd(tp){
                var that = this
                that.fir_id = ''
                that.name = ''
                if (tp) {
                    that.type = tp
                }
                that.draweradd = true
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
                this.getList()
            },
            async getList() {
                let that = this;
                let params = {}
                if (that.keyword) {
                    params.keyword = that.keyword
                }
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=neirong&c=zphnet&a=getClass', params, {hideloading: true}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.tableData = res.data.position
                        that.srcList = res.data.pics
                        that.loading = false;
                        if (that.tableData.length === 0){
                            that.emptytext = "暂无数据";
                        }
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            delrow(id, type) {
                this.del_type = type
                delConfirm(this, id, this.delete);
            },
            delAllBottom(type) {
                if (!this.selectedItem.length) {
                    message.error('请选择要删除的数据项');
                    return false;
                }
                this.del_type = type
                delConfirm(this, this.selectedItem, this.delete);
            },
            async delete(id) {
                let that = this;
                let params = {
                    del: id
                };
                httpPost('m=neirong&c=zphnet&a=delZphnetClass', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success(response.data.msg, function(){
                            if (that.del_type == 2) {
                                that.getdtlinfo();
                            }
                            that.getList();
                        });
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            async save() {
                let that = this;
                if (that.name == '') {
                    message.error('请填写区域名称');
                    return false;
                }
                if (that.type == '2' && that.fir_id == '') {
                    message.error('请选择一级分类');
                    return false;
                }
                httpPost('m=neirong&c=zphnet&a=saveClass', {ctype: that.type, nid: that.fir_id,name: that.name.split("\n").join("-")}).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            that.draweradd = false
                            if (that.type == 1) {
                                that.search();
                            } else {
                                that.del_id = that.fir_id;
                                that.getdtlinfo();
                            }
                        });
                    } else {
                        message.error(res.msg);
                    }
                });
            },
        },
    };
</script>
<style scoped></style>