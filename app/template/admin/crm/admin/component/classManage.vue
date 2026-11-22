<template>
    <div id="crmTClassApp" class="moduleElenAl">
        <div class="moduleSeachs">
            <div>CRM分类</div>
            <div class="nrtopbtn">
                <el-button class="" type="primary" size="mini" icon="el-icon-document-add" @click="addVisible = true">添加分类</el-button>
            </div>
        </div>
        <div class="moduleElTable">
            <el-table :data="tableData" stripe border style="width: 100%;" height="100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" ref="multipleTable" @selection-change="handleSelectionChange" v-loading="loading">
                <el-table-column type="selection" width="55" :selectable="checkSelectAble"></el-table-column>
                <el-table-column prop="id" label="分类编号" width="180"></el-table-column>
                <el-table-column label="分类名称(点击修改)" property="name">
                    <template slot-scope="scope">
                        <el-input v-if="scope.row[scope.column.property + 'isShow']" :ref="scope.column.property + scope.$index" :id="scope.column.property + scope.$index" v-model="scope.row.name" @blur="alterData(scope)"></el-input>
                        <span v-else>
                        <template v-if="scope.$index == 0">一级分类：{{ scope.row.name }}</template>
                        <template v-else>
                            &emsp;&emsp;┗{{ scope.row.name }}<img @click="editData(scope)" class="editIcon" src="../../../admin/images/bine.png" alt="" style="margin-left: 4px;" width="14" height="14">
                        </template>
                    </span>
                    </template>
                </el-table-column>
                <el-table-column label="排序(越小越在前)" property="sort">
                    <template slot-scope="scope">
                        <el-input v-if="scope.row[scope.column.property + 'isShow']" :ref="scope.column.property + scope.$index" :id="scope.column.property + scope.$index" v-model="scope.row.sort" @blur="alterData(scope, 'int')" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                        <span v-else>
                        <template v-if="scope.$index == 0"></template>
                        <template v-else>{{ scope.row.sort }}<img @click="editData(scope)" class="editIcon" src="../../../admin/images/bine.png" alt="" style="margin-left: 4px;" width="14" height="14"></template>
                    </span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="80">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button type="danger" size="mini" @click="deleteRow(scope)" v-if="idArr.indexOf(scope.row.id)==-1">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="">
                <div class="">
                    <el-checkbox :indeterminate="isIndeterminate" v-model="checked" @change="selectAllBottom">全选</el-checkbox>
                    <el-button @click="deleteRow(null, true)" size="mini">批量删除</el-button>
                </div>
            </div>
        </div>
        <el-dialog title="CRM分类" width="40%" :visible.sync="addVisible" append-to-body :modal-append-to-body="false">
            <class_add :position="position" @child-event-getlist="getList"></class_add>
        </el-dialog>
    </div>
</template>

<script>
    module.exports = {
        props:{
            cid: {
                type: String,
                default: ''
            }
        },
        watch:{
            cid: {
                handler(val) {
                    this.id = val;
                    if (this.id) {
                        this.getList();
                    }
                },
                deep: true,
                immediate: true
            }
        },
        data: function () {
            return {
                loading: false,
                id: 0,
                tableData: [], //表格数据
                checked: false,
                isIndeterminate: false,// checkbox 的不确定状态
                selectedItem: [],
                position: [],//一级分类列表
                addVisible: false,
                oldData: null,
                idArr: ['1','2','12','21','22','43']
            }
        },
        components: {
            'class_add': httpVueLoader('./classAdd.vue'),
        },
        methods: {
            checkSelectAble: function(e){
                let _this = this;
                if (_this.idArr.indexOf(e.id) > -1){
                    return false
                }else{
                    return true;
                }
            },
            editData(scope) {
                let index = scope.$index;
                let row = scope.row;
                let column = scope.column;
                this.oldData = JSON.parse(JSON.stringify(row));
                let copyRow = JSON.parse(JSON.stringify(row));
                copyRow[column.property + "isShow"] = true;
                this.$set(this.tableData, index, copyRow);
                this.$nextTick(() => {
                    let ref = column.property + index;
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
                if (type === 'int') {
                    row[column.property] = row[column.property].replace(/[^0-9]/g, '');
                }
                let copyRow = JSON.parse(JSON.stringify(row));
                copyRow[column.property + "isShow"] = false;
                this.$set(this.tableData, index, copyRow);
                if (row[column.property] === this.oldData[column.property]) {
                    return false;
                }
                let _this = this;
                let sendData = {id: row.id};
                sendData[column.property] = row[column.property];
                httpPost('m=crm&c=crmClass&a=ajaxData', sendData, {hideloading: true}).then(function (response) {
                    _this.oldData = null;
                    _this.getList();
                }).catch(function (error) {
                    console.log(error);
                });
            },

            handleSelectionChange(val) {
                this.selectedItem = val;
                if (this.selectedItem.length == 0) {
                    this.isIndeterminate = false;
                    this.checked = false;
                } else {
                    if (this.selectedItem.length == this.tableData.length) {
                        this.isIndeterminate = false;
                        this.checked = true;
                    } else {
                        this.isIndeterminate = true;
                        this.checked = false;
                    }
                }
            },
            selectAllBottom(value) {
                value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
            },
            getList() {
                this.addVisible = false;
                let _this = this;
                let params = {id: this.id};
                _this.loading = true;
                httpPost('m=crm&c=crmClass&a=upClass', params).then(function (response) {
                    let res = response.data;
                    let list = [];
                    if (res.data.class1) {
                        list.push(res.data.class1);
                    }
                    for (let item of res.data.class2) {
                        list.push(item);
                    }
                    _this.tableData = list;
                    _this.position = res.data.position;
                    _this.loading = false;
                }).catch(function (error) {
                    console.log(error);
                });
            },
            deleteRow(scope, isMore) {
                let params = {};
                if (isMore) {
                    if (!this.selectedItem.length) {
                        message.error('请选择要删除的数据');
                        return false;
                    }
                    let list = [];
                    for (let item of this.selectedItem) {
                        list.push(item.id);
                    }
                    params.delType = 'more';
                    params.del = list;
                } else {
                    params.delType = 'single';
                    params.delid = scope.row.id;
                }
                delConfirm(this, params, this.delete);
            },
            delete(params) {
                let _this = this;
                httpPost('m=crm&c=crmClass&a=delClass', params).then(function (response) {
                    let res = response.data;
                    if (res.error === 0) {
                        message.success('删除成功！');
                        _this.getList();
                    } else {
                        message.error('删除失败！');
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
        },
    };
</script>
<style scoped>
    .moduleElHight .moduleElTable{height: calc(100% - 105px)}
    .moduleInptList .el-select .el-input {width: 140px;}
	.el-table .el-table__cell { padding: 12px 0; }
</style>