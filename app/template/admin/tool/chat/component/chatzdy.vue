<template>
    <div class="moduleElHight">
        <div class="cyy_nav">
            <div class="">
                <el-radio-group v-model="tabPosition" @input="tablist">
                    <el-radio-button v-for="(item,key) in type_arr" :label="key" :key="key">{{item}}</el-radio-button>
                </el-radio-group>
            </div>
            <div class="tableSeachInpt">
                <el-button type="primary" icon="el-icon-document-add" size="mini" @click="addinfo()">新增常用语</el-button>
            </div>
        </div>
        <div class="moduleElTable">
            <el-table :data="tableData" border style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading" :empty-text="emptytext">
                <el-table-column prop="id" label="编号" width="80">
                </el-table-column>
                <el-table-column prop="content" label="内容"> </el-table-column>
                <el-table-column prop="sort" label="排序"> </el-table-column>
                <el-table-column label="操作" width="140" fixed="right">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="small " type=" " @click="editinfo(scope.row)">修改</el-button>
                            <el-button type="danger" size="small " @click="deleteinfo(scope.row.id)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modluDrawer">
            <el-dialog title="自定义常用语" :visible.sync="editshow" :with-header="true" :modal-append-to-body="false" :show-close="true" width="450px">
                <div class="hydialog_item">
                    <span class="hydialogName">类别：</span>
                    <div class="hydialogDiv">
                        <el-select v-model="einfo.type" placeholder="请选择">
                            <el-option v-for="(tv,tk) in type_arr" :key="tk" :label="tv" :value="tk"></el-option>
                        </el-select>
                    </div>
                </div>
                <div class="hydialog_item">
                    <span class="hydialogName">内容：</span>
                    <div class="hydialogDiv">
                        <el-input type="textarea" :rows="2" placeholder="刚看了您发布的职位，我很感兴趣，希望您可以看下我的简历" v-model="einfo.content"></el-input>
                    </div>
                </div>
                <div class="hydialog_item">
                    <span class="hydialogName">排序：</span>
                    <div class="hydialogDiv">
                        <el-input v-model="einfo.sort" style="flex: 1;" onKeyUp="this.value=this.value.replace(/[^0-9.]/g,'')"></el-input>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="editshow = false">取 消</el-button>
                    <el-button type="primary" @click="saveinfo" :loading="post_loading">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>
<script>
module.exports = {
    data: function() {
        return {
            loading: false,
            emptytext: '暂无数据',
            islook: false,

            tabPosition: '1',
            tableData: [],
            type_arr: [],
            list: [],

            editshow: false,
            einfo: {},
            post_loading: false,
        }
    },

    created: function() {
        this.getList();
    },
    methods: {
        async getList() {
            let that = this;
            let params = {};
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=tool&c=chatcyy&a=usefulSet', params, {hideloading: true}).then((result) => {

                var res = result.data;

                if (res.error == 0) {
                    that.list = res.data.list;
                    that.type_arr = res.data.type_arr;
                    that.tablist();
                }
                that.loading = false;
            }).catch(function(e) {
                console.log(e)
            })
        },
        tablist() {
            var that = this;
            if (typeof that.list[that.tabPosition] != 'undefined') {
                that.tableData = that.list[that.tabPosition];
            } else {
                that.tableData = [];
            }
            if (that.tableData.length === 0){
                that.emptytext = "暂无数据";
            }
        },
        deleteinfo: function(id) {
            var _this = this;

            var params = {
                del: id
            };
            delConfirm(_this, params, this.deletePost)
        },
        async deletePost(params) {

            let that = this;

            httpPost('m=tool&c=chatcyy&a=customdel', params).then(function(result) {

                var res = result.data;
                if (res.error == 0) {
                    message.success(res.msg, function() { that.getList() });
                    return;
                } else {
                    message.error(res.msg);
                    return;
                }
            }).catch(function(e) {
                console.log(e)
            })
        },
        editinfo: function(row) {

            this.einfo = deepClone(row);
            this.editshow = true;
        },
        addinfo: function() {
            var that = this;
            this.einfo = {
                id: '',
                content: '',
                type: that.tabPosition,
                sort: 0
            };
            this.editshow = true;
        },
        saveinfo: function() {

            var that = this,
                param = {};
            if (this.einfo.type > 0) {
                param.type = this.einfo.type;
            } else {
                message.warning('请选择常用语类别');
                return;
            }
            if (this.einfo.content != '') {
                param.content = this.einfo.content;
            } else {
                message.warning('请输入常用语内容');
                return;
            }

            param.id = this.einfo.id;

            param.sort = this.einfo.sort;

            that.post_loading = true;

            httpPost('m=tool&c=chatcyy&a=usefulSetAdd', param).then((result) => {
                var res = result.data;
                if (res.error == 0) {
                    message.success(res.msg, () => {
                        that.editshow = false;
                        that.tabPosition = this.einfo.type;
                        that.getList();
                    });
                } else {
                    message.error(res.msg);
                }
            }).catch(function(e) {
                console.log(e)
            }).finally(function () {
                setTimeout(function () {
                    that.post_loading = false;
                }, 2000);
            });
        }
    },
};
</script>
<style scoped>
.moduleTable {
    max-height: calc(100% - (60px + 10px));
}

.hydialog_item .hydialogName {
    overflow: hidden;
    display: block;
    width: 50px;
}
.hydialogDiv{
	overflow: hidden;
	position: relative;
	width: calc(100% - 55px);
	padding-left: 5px;
}
.hydialogDiv .el-select{
	overflow: hidden;
	position: relative;
	width: 100%;
}
.hydialogDiv .el-input{
	width: 100%;
}
</style>