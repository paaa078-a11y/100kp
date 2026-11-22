<template>
    <!--系统-类别-培训分类：开课类型-->
    <div class="fl_cont">
        <div class="fl_cont_categoryHeader">
            <div></div>
            <div>
                <el-button type="primary" @click="addVisible = true" icon="el-icon-document-add" size="mini">添加类型</el-button>
            </div>
        </div>
        <div class="fl_cont_table">
            <el-table :data="tableData" stripe border style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" ref="multipleTable"
                @selection-change="handleSelectionChange" height="calc(100% - 60px)" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label=" 编号" width="100">
                </el-table-column>
                <el-table-column prop="name" label="开课类型名称(点击修改)">
                    <template slot-scope="scope">
                        <el-input v-if="scope.row[scope.column.property + 'isShow']" :ref="scope.column.property + scope.$index"
                            :id="scope.column.property + scope.$index" v-model="scope.row.name" @blur="alterData(scope)"></el-input>
                        <span v-else>
                            {{ scope.row.name }}<img @click="editData(scope)" class="editIcon" src="../../../admin/images/bine.png" alt=""
                            style="margin-left: 4px;" width="14" height="14">
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="sort" label="排序(越大越在前)">
                    <template slot-scope="scope">
                        <el-input v-if="scope.row[scope.column.property + 'isShow']" :ref="scope.column.property + scope.$index"
                            :id="scope.column.property + scope.$index" v-model="scope.row.sort" @blur="alterData(scope, 'int')"
                            onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"></el-input>
                        <span v-else>
                            {{ scope.row.sort }}<img @click="editData(scope)" class="editIcon" src="../../../admin/images/bine.png" alt=""
                            style="margin-left: 4px;" width="14" height="14">
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="70">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" @click="deleteRow(scope)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <div class="pageFenyAlls">
                <div class="modulecz modulePagButn">
                    <el-checkbox :indeterminate="isIndeterminate" v-model="checked" @change="selectAllBottom">全选</el-checkbox>
                    <el-button @click="deleteRow(null, true)" size="mini">批量删除</el-button>
                </div>
            </div>
        </div>
        <el-dialog title="添加类型" width="30%" :visible.sync="addVisible" :modal-append-to-body="false">
            <div class="hydialog_item">
                <span>类型名称：</span>
                <el-input type="textarea" v-model="ruleForm.name" style="flex: 1;"></el-input>
            </div>
            <i class="el-icon-info" style="margin-top: 10px;">说明：可以添加多条分类（请按回车Enter键换行，一行一个)</i>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="submitForm('ruleForm')" :disabled="submitLoading">添加</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
            emptytext: '暂无数据',
            tableData: [], //表格数据
            checked: false,
            isIndeterminate: false,// checkbox 的不确定状态
            selectedItem: [],
            oldData: null,
            addVisible: false,
            ruleForm: {
                name: '',
            },
            submitLoading: false,
            loading: false
        };
    },
    mounted() {
        this.getList();
    },
    methods: {
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
            _this.loading = true;
            _this.emptytext = "数据加载中";
            httpPost('m=system&c=category_px_subject_type&a=index').then(function (response) {
                let res = response.data;
                let list = [];
                _this.tableData = res.data;
                _this.loading = false;
                if (_this.tableData.length === 0){
                    _this.emptytext = "暂无数据";
                }
            }).catch(function (error) {
                console.log(error);
            });
        },
        submitForm(formName) {
            let _this = this;
            let params = JSON.parse(JSON.stringify(this.ruleForm));
            params.name = params.name.split("\n").join("-");

            if (params.name == '') {
                message.error('类别名称不能为空！');
                return;
            }
            _this.submitLoading = true;
            httpPost('m=system&c=category_px_subject_type&a=add', params).then(function (response) {
                let res = response.data;
                if (res.error === 0) {
                    message.success('添加成功！');
                    _this.getList();
                    _this.clearForm();
                    _this.addVisible = false;
                } else if (res.error === 1) {
                    message.error('添加失败！');
                } else if (res.error === 2) {
                    message.error('已有此类别，请重新输入！');
                } else {
                    message.error(res.msg);
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                _this.submitLoading = false;
            });
        },
        clearForm() {
            this.ruleForm.name = '';
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
                params.delType = 'all';
                params.del = list;
            } else {
                // let index = scope.$index;
                // this.tableData.splice(index, 1);
                params.delType = 'one';
                params.delid = scope.row.id;
            }

            delConfirm(this, params, this.delete);
        },
        delete(params) {
            let _this = this;
            httpPost('m=system&c=category_px_subject_type&a=del', params).then(function (response) {
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
            httpPost('m=system&c=category_px_subject_type&a=ajax', sendData, {hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error === 0) {
                    message.success('修改成功');
                } else {
                    message.error('修改失败');
                }
                _this.oldData = null;
                _this.getList();
            }).catch(function (error) {
                console.log(error);
            });
        },
    },
};
</script>
<style scoped>
.cell span {
    display: flex;
    align-items: center;
}

.el-tabs__header {
    display: none !important;
}

.el-tabs__content {
    padding: 0;
}

.dialog_item {
    margin-top: 25px;
    display: flex;
}

.item_span {
    width: 75px;
    text-align: right;
    display: block;
}

.hydialog_item {
    display: flex;
}

.editIcon {
    padding-left: 5px;
    cursor: pointer;
}

.fl_cont {
    overflow: hidden;
    position: relative;
    width: 100%;
    height: 100%;
}

.fl_cont_categoryHeader {
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
}

.fl_cont_table {
    overflow: hidden;
    position: relative;
    width: 100%;
    height: calc(100% - 38px);
}

.fl_cont_table .el-table {
    overflow: hidden;
    position: relative;
    width: 100%;
}
	.el-table .el-table__cell {
	    padding: 12px 0;
	}
</style>