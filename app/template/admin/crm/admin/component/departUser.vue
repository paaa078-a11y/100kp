<template>
    <div class="moduleElHight" style="padding: 0 10px;">
        <div class="moduleElTabSeach">
            <div class="moduleSeachButn">
                <el-button type="primary" icon="el-icon-document-add" size="mini" @click="addNewUser">添加部门成员</el-button>
            </div>
        </div>
        <div class="moduleElTable moduleElTablTwo">
            <el-table :data="tableData" border style="width: 100%" stripe :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" @selection-change="handleSelectionChange" @sort-change='sortChange' ref="orgUserTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="uid" label="工号" sortable="custom"></el-table-column>
                <el-table-column prop="name" label="姓名"></el-table-column>
                <el-table-column prop="moblie" label="手机号"></el-table-column>
                <el-table-column prop="weixin" label="微信"></el-table-column>
                <el-table-column prop="qq" label="Q Q" ></el-table-column>
                <el-table-column label="部门权限">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.power_n" active-color="#1890FF" inactive-color="#B8BDC9" @change="changePower(scope, 'power')"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="子部门权限">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.spower_n" active-color="#1890FF" inactive-color="#B8BDC9" @change="changePower(scope, 'spower')"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="150">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" type="danger" @click="delOrgUser(scope)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div style="margin-left: 10px;">
                <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange"> 全选 </el-checkbox>
                <el-button  size="mini" style="margin-left: 10px;" @click="delOrgUserSel">批量删除</el-button>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="成员调配" :visible.sync="userBoxShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" width="602px">
                <el-transfer :titles="['业务员', '部门成员']" filterable filter-placeholder="请输入关键字" v-model="selUser" :data="userData"></el-transfer>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="userBoxShow = false">取 消</el-button>
                    <el-button type="primary" @click="saveOrgUser">确 定</el-button>
                </div>
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
                orgId: 0,
                allUser: [],
                tableData: [],
                total: 0,

                orgUid: [],

                // 批量选择
                checkAll: false,
                isIndeterminate: false,
                selectedItem: [],

                powerVal: '',

                userBoxShow: false,
                userData: [],
                selUser: []

            }
        },
        methods: {
            getUserList() {
                var that = this;
                var params = {};
                params.orgId = that.orgId;
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=crm&c=orgStr&a=getUserList', params).then(function (res) {
                    let data = res.data.data;
                    that.tableData = data.orgUser;
                    that.orgUid = data.orgUid;
                    that.allUser = data.allUser;
                    that.total = data.total;
                    that.loading = false;
                    if (that.tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            addNewUser: function(){

                let _this = this;

                _this.userData = [];
                _this.selUser = [];

                _this.allUser.forEach(function (item) {
                    if (!_this.userData.find(v => v.name === item.name)){
                        _this.userData.push({
                            key: item.uid,
                            label: item.name
                        });
                    }
                });
                _this.selUser = _this.orgUid;
                _this.userBoxShow = true;
            },
            saveOrgUser: function (){

                let _this = this;
                let params = {};
                params.orgId = _this.orgId;
                params.uid = _this.selUser;
                httpPost('m=crm&c=orgStr&a=saveOrgUser', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {

                            _this.getUserList();
                            _this.userBoxShow = false;
                        });
                    } else {

                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            sortChange: function (e) {
                this.search.t = e.prop;
                this.search.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.handleSearch();
            },
            handleSearch: function () {
                this.currentPage = 1
                this.getUserList()
            },
            changePower: function(e, type){

                let _this = this;

                if (type == 'power'){

                    _this.powerVal = !e.row.power_n ? 2 : 1;
                    let powerBefore = e.row.power;
                    if (powerBefore == _this.powerVal) {
                        return;
                    }
                }else if (type == 'spower'){

                    _this.powerVal = !e.row.spower_n ? 2 : 1;
                    let powerBefore = e.row.spower;
                    if (powerBefore == _this.powerVal) {
                        return;
                    }
                }

                let param = {type: type, power: _this.powerVal, uid: e.row.uid};
                httpPost('m=crm&c=orgStr&a=setPower', param).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {

                        _this.getUserList();
                        message.success(res.msg);
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            handleSelectionChange(val) {
                this.selectedItem = val;
                if (this.selectedItem.length == 0) {
                    this.isIndeterminate = false;
                    this.checkAll = false;
                } else {
                    if (this.selectedItem.length == this.tableData.length) {
                        this.isIndeterminate = false;
                        this.checkAll = true;
                    } else {
                        this.isIndeterminate = true;
                        this.checkAll = false;
                    }
                }
            },
            handleCheckAllChange(val) {
                val ? this.$refs.orgUserTable.toggleAllSelection() : this.$refs.orgUserTable.clearSelection();
            },
            delOrgUser(scope, isMore) {
                var that = this;
                let name = '';
                let idArr = [], nameArr = [];
                let params = {};
                if (isMore) {
                    this.selectedItem.forEach((item) => {
                        idArr.push(item.uid);
                        nameArr.push(item.name);
                    });
                    name = '（' + nameArr.join('，') + '）';
                    params.uid = idArr;
                } else {

                    name = '（' + scope.row.name + '）';
                    params.uid = scope.row.uid;
                }

                delConfirm(this, params, this.delete, '您确定要删除部门成员'+name+'吗？');
            },
            delOrgUserSel: function () {
                var that = this;
                if (!that.selectedItem.length) {
                    message.error('请选择要删除的部门成员');
                    return;
                }
                this.delOrgUser(null, true);
            },
            delete(params){
                var self = this;
                httpPost('m=crm&c=orgStr&a=delOrgUser', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            self.getUserList();
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            }
        },
    };
</script>
<style scoped>
    .moduleElHight .moduleElTable{height: calc(100% - 105px)}
    .moduleInptList .el-select .el-input {width: 140px;}
</style>