<template>
    <div class="moduleElHight">
        <div class="moduleZenzTaoc">
            <div class="moduleZzTacTite">
                <span></span>
            </div>
            <div class="moduleZzTacButn">
                <el-button type="primary" size="mini" icon="el-icon-setting" plain
                    @click="detail={display:'2'};drawer2 = true">设置增值类型</el-button>
                <el-button type="primary" size="mini" icon="el-icon-document-add" @click="zzbag={};drawer3 = true">设置增值包</el-button>

            </div>
        </div>
        <div class="admin_datatip">
            <i class="el-icon-document"></i>
            <span>
                猎头可设置的等级包括：普通会员，高级会员，钻石会员等等，按照实际情况去设置等级的区分，会员等级需满足的条件和享受的优惠
            </span>
        </div>
        <div class="moduleElTable moduleElMoreInt" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" stripe style="width: 100%"
                      @selection-change="selectChange"
                      ref="multipleTable"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" width="120"></el-table-column>
                <el-table-column prop="name" label="服务名称" width="120"></el-table-column>
                <el-table-column label="状态" min-width="240">
                    <template slot-scope="props">
                        <template v-if="props.row.display == 1">
                            已启用
                            <el-button type="text" style="padding: 0;" @click="onstatus(props.row.id,'2')" :disabled="submitLoading">下架此服务</el-button>
                        </template>
                        <template v-else>
                            未启用
                            <el-button type="text" style="padding: 0;" @click="onstatus(props.row.id,'1')" :disabled="submitLoading">启用此服务</el-button>
                        </template>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="130">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="getDetailId(scope.row)">详情</el-button>
                            <el-button size="mini" type="danger" @click="del(scope.row)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="isIndeterminate" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="batchDel" size="mini">批量删除</el-button>
            </div>
        </div>
        <div class="modluDrawer">
            <el-drawer title="设置增值类型" :visible.sync="drawer2" :modal-append-to-body="false" append-to-body :show-close="true"
                :with-header="true" size="45%">
                <div class="drawerModlue">
                    <div class="drawerModInfo drawerModInfoOne">
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>增值包名称</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input v-model="detail.name" placeholder="请输入内容"></el-input>
                            </div>
                            <div class="drawerModTips">
                                <el-alert title="如：下载增值包" type="info" show-icon :closable="false">
                                </el-alert>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>显示状态</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-radio-group v-model="detail.display">
                                    <el-radio  label="1">启用</el-radio>
                                    <el-radio  label="2">不启用</el-radio>
                                </el-radio-group>
                            </div>
                            <div class="drawerModTips">
                                <el-alert title="前台是否可见" type="info" show-icon
                                    :closable="false">
                                </el-alert>
                            </div>
                        </div>
                    </div>
                    <div class="setBasicButn" style="border: none;">
                        <el-button type="primary" size="medium" @click="savezz" :disabled="submitLoading">提交</el-button>
                    </div>
                </div>
            </el-drawer>
        </div>
        <div class="modluDrawer">
            <el-drawer title="设置增值包" :visible.sync="drawer3" :with-header="true" :modal-append-to-body="false" append-to-body :show-close="true" size="60%">
                <div class="drawerModlue">
                    <div class="drawerModInfo drawerModInfoOne">
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>增值类型</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-select v-model="zzbag.type" placeholder="请选择">
                                    <el-option v-for="item in tableData" :key="item.id" :label="item.name"
                                        :value="item.id">
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="drawerModLis">
                            <div class="drawerModTite">
                                <span>套餐设置</span>
                            </div>
                            <div class="drawerModInpt">
                                <el-input v-model="zzbag.service_price" placeholder="请输入数字" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                                    <template slot="prepend">服务价格</template>
                                    <template slot="append">元</template>
                                </el-input>
                                <el-input v-model="zzbag.resume" placeholder="请输入数字" style="margin-left: 10px;" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                                    <template slot="prepend">下载简历</template>
                                    <template slot="append">份</template>
                                </el-input>
                            </div>
                            
                            <div class="drawerModTips" style="display: flex; align-items: center; margin-top: 12px;">
                                <el-input v-model="zzbag.lt_job_num" placeholder="请输入数字" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                                    <template slot="prepend">发布职位</template>
                                    <template slot="append">份</template>
                                </el-input>
                                <el-input v-model="zzbag.lt_breakjob_num" placeholder="请输入数字" style="margin-left: 10px;" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                                    <template slot="prepend">刷新职位</template>
                                    <template slot="append">份</template>
                                </el-input>
                            </div>
                        </div>
                    </div>
                    <div class="setBasicButn" style="border: none;">
                        <el-button type="primary" size="medium" @click="savebag" :disabled="submitLoading">提交</el-button>
                    </div>
                </div>
            </el-drawer>
        </div>
        <div class="modluDrawer">
            <el-drawer title="增值包详情" :visible.sync="drawer4" :modal-append-to-body="false" :show-close="true" append-to-body
                       :with-header="true" size="70%">
                <div style="overflow: hidden; position: relative; height: 100%; padding: 0 20px;">
                    <div class="admin_datatip">
                        <i class="el-icon-document"></i>
                        <span>该页面您设置的增值服务详细信息，可对增值服务进行选择查看，编辑，删除等操作。</span>
                    </div>
                    <div class="admin_datatip">
                        <el-select v-model="zzfwId" size="small" slot="prepend" placeholder="增值类型：" @change="changezzFw" clearable>
                            <el-option v-for="item in tableData" :key="item.id" :label="item.name"
                                    :value="item.id">
                            </el-option>
                        </el-select>
                    </div>
                    <div class="moduleElTable" style="border: 1px solid #ebeef5; width: initial; height: calc(100% - 155px); padding: 0;">
                        <el-table :data="zztableData" stripe style="width: 100%"
                                @selection-change="selectzzChange"
                                ref="multipleTablezz"
                                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                            <template slot="empty">
                                <p>{{dataText}}</p>
                            </template>
                            <el-table-column type="selection" width="55"></el-table-column>
                            <el-table-column prop="id" label="编号"></el-table-column>
                            <el-table-column prop="service_price" label="服务价格"></el-table-column>
                            <el-table-column prop="resume" label="下载简历"></el-table-column>
                            <el-table-column prop="lt_job_num" label="发布职位"></el-table-column>
                            <el-table-column prop="lt_breakjob_num" label="刷新职位"></el-table-column>
                            <el-table-column fixed="right" label="操作" width="140">
                                <template slot-scope="scope">
                                    <div class="moduleElTaCaoz">
                                        <el-button size="mini" @click="editzz(scope.row)">编辑</el-button>
                                        <el-button size="mini" @click="delt(scope.row)" type="danger">删除</el-button>
                                    </div>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                    <div class="modulePaging">
                        <div>
                            <el-checkbox v-model="zzcheckedAll" :indeterminate="zzisIndeterminate" @change="zzselectAllBottom">全选</el-checkbox>
                            <el-button @click="batchDelt" size="mini">批量删除</el-button>
                        </div>
                    </div>
                </div>
            </el-drawer>
        </div>
    </div>
</template>
    
<script>
module.exports = {
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',
            submitLoading: false,
            checkedAll:false,
            isIndeterminate: false,// checkbox 的不确定状态
            input: '',
            value3: '',
            textarea: '',
            tableData: [],
            idsArr: [],
            uri: "m=user&c=",
            select: '',
            value: true,
            drawer: false,
            drawer2: false,
            drawer3: false,
            radio:'',
            detail:{
                name:'',
                display:''
            },
            zzbag:{},

            // 详细信息
            drawer4:false,
            zzdetail:{},
            zzfwId:'',
            zzidsArr:[],
            zzcheckedAll:false,
            zzisIndeterminate: false,// checkbox 的不确定状态
            zztableData :[],
        }
    },
    created(){
        this.getList();
    },
    mounted() {

    },
    methods: {
        selectAllBottom:function (value) {
            value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
        },
        selectChange:function (val) {
            this.idsArr = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.idsArr.push(item.id);
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
        getList:function () {
            let _this = this;
            let url = _this.uri + 'admin_lt_rating&a=server';
            _this.loading = true;
            httpPost(url, {}, {hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.tableData = res.data.data;
                    _this.loading = false;
                    if (_this.tableData.length === 0) {
                        _this.dataText = "暂无数据";
                    }
                }
            })
        },
        del: function (detail) {
            let _this = this;
            let url = this.uri + 'admin_lt_rating&a=dels';
            delConfirm(this, { id: detail.id }, function (params) {
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
        batchDel: function () {
            let ids = this.idsArr;
            if (!ids.length) {
                message.error('请选择需要删除的用户!');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_lt_rating&a=dels'
            delConfirm(this, { id: ids }, function (params) {
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
        onstatus:function (id,status) {
            let _this = this;
            let url = this.uri + 'admin_lt_rating&a=opera';
            _this.submitLoading = true;
            httpPost(url, { id: id,display:status }).then(function (response) {
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
        },
        savezz:function(){
            let _this = this;
            let url = this.uri + 'admin_lt_rating&a=save';
            if (isEmpty(_this.detail.name)){
                message.error('增值包名称不能为空！');
                return;
            }
            _this.submitLoading = true;
            httpPost(url, _this.detail).then(function (response) {
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
            this.drawer2 = false;
        },
        savebag:function(){
            let _this = this;
            let url = this.uri + 'admin_lt_rating&a=saves';
            _this.submitLoading = true;
            httpPost(url, _this.zzbag).then(function (response) {
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
        editzz:function (detail) {
            this.zzbag = detail;
            this.zzbag.type  = this.zzfwId;
            this.drawer3 = true ;
        },
        // 增值服务套餐
        selectzzChange:function (val) {
            this.zzidsArr = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.zzidsArr.push(item.id);
                });
            }
            if (_this.zzidsArr.length == 0) {
                _this.zzcheckedAll = false;
                this.zzisIndeterminate = false;
            } else {
                if (_this.zzidsArr.length == _this.zztableData.length) {
                    _this.zzcheckedAll = true;
                    this.zzisIndeterminate = false;
                } else {
                    _this.zzcheckedAll = false;
                    this.zzisIndeterminate = true;
                }
            }
        },
        zzselectAllBottom:function (value) {
            value ? this.$refs.multipleTablezz.toggleAllSelection() : this.$refs.multipleTablezz.clearSelection();
        },
        getDetailId:function(detail){
            this.zzdetail = detail;
            this.zzfwId = detail.id;
            this.detailList();
            this.drawer4 = true;
        },
        changezzFw:function (e){
            this.zzfwId = e;
            this.detailList();
        },
        detailList:function () {
            //  获取增值包id
            let _this = this;
            let url = this.uri + 'admin_lt_rating&a=list';
            _this.loading = true;
            httpPost(url, {id:this.zzfwId}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    _this.zztableData = res.data;
                    _this.loading = false;
                    if (_this.zztableData.length === 0) {
                        _this.dataText = "暂无数据";
                    }
                }
            })
        },
        delt:function (detail) {
            let _this = this;
            let url = this.uri + 'admin_lt_rating&a=delt';
            delConfirm(this, {del:detail.id}, function (params) {
                httpPost(url, params).then(function(response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, _this.detailList());
                    } else {
                        message.error(res.msg);
                    }
                })
            }, '确定要删除？')
        },
        batchDelt: function () {
            let ids = this.zzidsArr;
            if (!ids.length) {
                message.error('请选择要删除的数据');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_lt_rating&a=delt'
            delConfirm(this, { del: ids }, function (params) {
                httpPost(url, params).then(function(response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, _this.detailList());
                    } else {
                        message.error(res.msg);
                    }
                })
            }, '确定要删除？')

        },
    },
};
</script>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    /* width: 47%; */
    margin: 3px 0;
}

.moduleZenzTaoc {
    overflow: hidden;
    position: relative;
    width: 100%;
    padding-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>