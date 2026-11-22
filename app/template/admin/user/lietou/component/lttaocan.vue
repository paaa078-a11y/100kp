<template>
    <div class="moduleElHight">
        <div class="moduleZenzTaoc">
            <div class="moduleZzTacTite">
                <span>增值套餐服务</span>
            </div>
            <div class="moduleZzTacButn">
                <el-button type="primary" size="mini" icon="el-icon-document-add" @click="title='设置会员等级';detail = {};drawer2 = true">设置会员等级</el-button>
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
                <el-table-column prop="name" label="会员名称" width="180">
                </el-table-column>
                <el-table-column prop="type_n" label="会员模式" width="140">
                </el-table-column>
                <el-table-column prop="service_price" label="购买金额" width="130">
                </el-table-column>
                <el-table-column prop="fwtime" label="服务时间" width="130">
                    <template slot-scope="scope">
                        <template v-if="scope.row.service_time">{{scope.row.service_time}}天</template>
                        <template v-else>不限</template>
                    </template>
                </el-table-column>
                <el-table-column prop="tacinfo" label="套餐信息" min-width="230">
                    <template slot-scope="scope">
                        <div class="moduleProtacin">
                            <span>上架职位：{{scope.row.lt_job_num}}份</span>
                            <template v-if="scope.row.type == 1">
                                <span>刷新职位：{{scope.row.lt_breakjob_num}}份</span>
                                <span>下载简历：{{scope.row.lt_resume}}份</span>
                                <span>{{sy_chat_name}}：{{scope.row.chat_num}}位</span>
                            </template>
                            <template v-else-if="scope.row.type == 2">
                                <span>刷新职位：
                                    <template v-if="scope.row.lt_breakjob_num ==0">不限</template>
                                    <template v-else>每日{{scope.row.lt_breakjob_num}}份</template></span>
                                <span>下载简历：
                                    <template v-if="scope.row.lt_resume ==0">不限</template>
                                    <template v-else>每日{{scope.row.lt_resume}}份</template></span>
                                <span>{{sy_chat_name}}：
                                    <template v-if="scope.row.chat_num ==0">不限</template>
                                    <template v-else>每日{{scope.row.chat_num}}份</template></span>
                            </template>
                        </div>
                        
                    </template>
                </el-table-column>
                <el-table-column prop="zhuatai" label="	状态" width="130">
                    <template slot-scope="scope">
                        <template v-if="scope.row.display == 1">
                            <span class="admin_state1">已启用</span>
                        </template>
                        <template v-else>
                            <span class="admin_state4">未启用</span>
                        </template>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="editFun(scope.row)">编辑</el-button>
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
            <div class="modulePagNum">
                <el-drawer :title="title" :visible.sync="drawer2" :modal-append-to-body="false" :show-close="true"
                           :with-header="true" size="60%">
                    <taocan-edit :detail_v="detail" :coupon="coupon" :category="category" @child-event="getList"></taocan-edit>
                </el-drawer>

            </div>
        </div>
        <div class="modluDrawer">
        </div>
    </div>
</template>

<script>
module.exports = {
    props:{
        sy_chat_name:String,
        coupon:Array
    },
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',

            checkedAll:false,
            isIndeterminate: false,// checkbox 的不确定状态
            input: '',
            value1: '',
            select: '',
            value3: '',
            value: true,
            currentPage4: 4,
            drawer: false,
            drawer2: false,
            textarea: '',

            tableData: [],
            idsArr: [],
            uri: "m=user&c=",
            detail:{
                explains:''
            },
            title:'',
            category:'2'
        }
    },
    components: {
        'taocan-edit': httpVueLoader('./lt_component/taocanEdit.vue')
    },
    created(){
        this.getList();
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
        getList:function(){
            let _this = this;
            _this.drawer2 = false;
            let url = _this.uri + 'admin_lt_rating&a=index';
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
            let url = this.uri + 'admin_lt_rating&a=delrating';
            delConfirm(this, { del: detail.id }, function (params) {
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
                message.error('请选择要删除的数据');
                return
            }
            let _this = this;
            let url = this.uri + 'admin_lt_rating&a=delrating'
            console.log(ids);
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
        editFun:function (detail) {
            this.detail =JSON.parse(JSON.stringify(detail)) ;
            this.detail.explains = this.detail.explains == null? '': this.detail.explains
            this.title = '编辑会员等级';
            this.drawer2 = true;
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

.drawerModInfo{
    /* height: calc(100% - 90px); */
    overflow-y: auto;
    position: relative;
    max-height: calc(100% - 90px);
}
</style>