<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach" style="flex-wrap: wrap; height: initial;">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" @keyup.enter.native="search" placeholder="请输入内容" v-model="keyword"
                        class="input-with-select" clearable>
                        <el-select v-model="keytype" slot="prepend" placeholder="请选择">
                            <el-option label="手机号码" value="1"></el-option>
                            <el-option label="个人姓名" value="2"></el-option>
                            <el-option label="企业名称" value="3"></el-option>
                            <el-option label="用户ID" value="4"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="moduleInptList">
                    <el-select size="small" v-model="source" placeholder="数据来源" clearable @change="search">
                        <el-option v-for="(item, index) in source_arr" :key="index" :label="item" :value="index"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select size="small" v-model="isuid" placeholder="是否注册" clearable @change="search">
                        <el-option label="已注册" value="1"></el-option>
                        <el-option label="未注册" value="2"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select size="small" v-model="isresume" placeholder="拥有简历" clearable @change="search">
                        <el-option label="已创建" value="1"></el-option>
                        <el-option label="未创建" value="2"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-date-picker size="small" v-model="sqtime" type="daterange" @change="search" range-separator="至"
                        start-placeholder="授权开始" end-placeholder="结束日期" :picker-options="timeOptions"
                        value-format="timestamp" style="width: 280px;"></el-date-picker>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>

        </div>
        <div class="admin_datatip" style=" margin-top: 8px;">
            <i class="el-icon-document"></i>
            <span class="admin_datatip_n">已注册：<el-link type="primary" :underline="false"
                    @click="statusChange('1')">{{ numarr.authNum1 }}</el-link> </span>
            <span class="admin_datatip_n">未注册：<el-link type="primary" :underline="false"
                    @click="statusChange('2')">{{ numarr.authNum2 }}</el-link> </span>
            <span class="admin_datatip_n">已发布简历：<el-link type="primary" :underline="false"
                    @click="statusChange('3')">{{ numarr.authNum3 }}</el-link> </span>
        </div>
        <div class="moduleElTable" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table ref="table" :data="tableData" v-loading="list_loading" @selection-change="selectionChange"
                @mousedown.native="mouseDownHandler"
                @mouseup.native="mouseUpHandler"
                @mousemove.native="mouseMoveHandler"
                :default-sort="{ prop: 'id', order: 'descending' }" @sort-change="sortChange" stripe style="width: 100%"
                :empty-text="emptytext" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" width="120" sortable="custom"></el-table-column>
                <el-table-column label="用户信息" width="230">
                    <template slot-scope="scope">
                        <div><em
                                style="color:#009688;font-weight: bold;">「{{ scope.row.usertype_n }}」</em>{{ scope.row.name_n }}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="phone" label="手机号" width="130"></el-table-column>
                <el-table-column label="简历" width="100">
                    <template slot-scope="scope">
                        <div class="moduleProps" v-if="scope.row.usertype == '1'">
                            <el-button v-if="scope.row.resume > 0" type="text" style="padding: 0;"
                                @click="userPreview(scope.row.uid)">预览</el-button>
                            <el-button v-else type="text" style="padding: 0;"
                                @click="addresume(scope.row.uid)">添加</el-button>
                        </div>
                        <div v-else>--</div>
                    </template>
                </el-table-column>
                <el-table-column prop="atime_n" label="授权时间" width="140"></el-table-column>
                <el-table-column prop="source_n" label="来源" width="160"></el-table-column>
                <el-table-column prop="num" label="授权次数" width="100"></el-table-column>
                <el-table-column prop="remark" label="备注信息" min-width="140"></el-table-column>
                <el-table-column fixed="right" label="操作" width="180">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-tooltip class="item" effect="dark" content="点击拨号按钮，可使用虚拟号联系求职者" placement="right">
                                <el-button type=" " size="mini" @click="contact_customer(scope.row)">拨号</el-button>
                            </el-tooltip>
                            <el-button size="mini" @click="bzbox(scope.row)">{{ scope.row.remark ? '查备' : '备注' }}</el-button>
                            <el-button type="danger" size="mini" @click="deleteinfo(scope.row.id)">删除</el-button>
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
                    layout="total, sizes, prev, pager, next, jumper"></el-pagination>
            </div>
        </div>
        <el-dialog title="备注" width="400px" :visible.sync="bzshow" :modal-append-to-body="false">
            <div class="dialog_item">
                <div class="drawerModInpt" style="width: 100%; padding: 0;">
                    <el-input type="textarea" v-model="bz_content" style="flex: 1;"></el-input>
                </div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="bzshow = false">取 消</el-button>
                <el-button type="primary" @click="setBz">确 定</el-button>
            </span>
        </el-dialog>
        <div class="modluDrawer">
            <el-drawer title="简历预览" :append-to-body="true" :visible.sync="resumePreviewVisible" :destroy-on-close="true"
                size="530px">
                <resume_preview :uid="ruid" v-if="resumePreviewVisible"></resume_preview>
            </el-drawer>
        </div>
        <div class="modluDrawer">
            <el-drawer title="添加简历" :modal-append-to-body="false" append-to-body :visible.sync="addresumeVisible"
                :destroy-on-close="true" :wrapper-closable="false" size="50%">
                <addresume :uid="aruid" @close-update="closeAddresume" v-if="addresumeVisible"></addresume>
            </el-drawer>
        </div>
    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
            mouseFlag: false,
            mouseOffset: 0,
            emptytext: '暂无数据',
            numarr: '',
            source_arr: [],
            timeOptions: {
                shortcuts: [{
                    text: '昨天',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24);
                        end.setTime(end.getTime() - 3600 * 1000 * 24);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '今天',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '本周',
                    onClick(picker) {
                        const start = new Date(new Date().setHours(0, 0, 0) - (new Date().getDay() - 1) * 24 * 60 * 60 * 1000);
                        const end = new Date();
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '上周',
                    onClick(picker) {
                        const start = new Date(new Date().setHours(0, 0, 0) - (new Date().getDay() + 6) * 24 * 60 * 60 * 1000);
                        const end = new Date(new Date().setHours(0, 0, 0) + (0 - new Date().getDay()) *24 * 60 * 60 *1000);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '本月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date(new Date(new Date().getFullYear(), new Date().getMonth(), 1).setHours(0, 0, 0));
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '上月',
                    onClick(picker) {
                        const end = new Date(new Date(new Date().getFullYear(), new Date().getMonth(), 0).setHours(23, 59, 59, 59));
                        const start = new Date(new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).setHours(0, 0, 0));
                        picker.$emit('pick', [start, end]);
                    }
                }]
            },
            tableData: [],
            total: 0,
            limit: 0,
            currentPage: 1,
            page_sizes: [],

            list_loading: false,

            allchecked: false,
            choosedata: [],

            keyword: '',
            keytype: '1',
            state: '',
            source: '',
            isuid: '',
            isresume: '',
            sqtime: [],
            sort_t: '',
            order: '',

            bzshow: false,
            bzid: '',
            bz_content: '',

            resumePreviewVisible: false,
            ruid: '',

            addresumeVisible: false,
            aruid: '',

            prevPage: 0
        }
    },
    components: {
        'addresume': httpVueLoader('../../component/addresume.vue'),
        'resume_preview': httpVueLoader('../../../component/resume_preview.vue'),
    },
    mounted() {
        this.getStaticData();
        this.getList();
    },
    methods: {
        mouseDownHandler(e) {
            this.mouseOffset = e.clientX;
            this.mouseFlag = true;
        },
        mouseUpHandler(e) {
            this.mouseFlag = false;
        },
        mouseMoveHandler(e) {
            // 这里面需要注意，通过ref需要那个那个包含table元素的父元素
            let divData = this.$refs.table.bodyWrapper;
            if (this.mouseFlag) {
                // 设置水平方向的元素的位置
                divData.scrollLeft -= (- this.mouseOffset + (this.mouseOffset = e.clientX));
            }
        },
        getStaticData() {
            var that = this
            let params = {}
            httpPost('m=crm&c=crm_chat&a=getPaStaticData', params, { hideloading: true }).then((result) => {
                var res = result.data;
                if (res.error == 0) {
                    that.source_arr = res.data.source
                    that.numarr = res.data.numarr
                }
            });
        },
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                limit: that.limit,
                t: that.sort_t,
                order: that.order,
                keyword: that.keyword,
                keytype: that.keytype,
                source: that.source,
                isuid: that.isuid,
                isresume: that.isresume,
                sqtime: that.sqtime
            }

            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=crm&c=crm_chat&a=phoneAuthList', params).then((result) => {
                this.list_loading = false;
                var res = result.data;

                if (res.error == 0) {
                    that.tableData = res.data.list
                    that.total = parseInt(res.data.total)
                    that.page_sizes = res.data.page_sizes;
                    that.limit = res.data.page_size;
                    if (that.prevPage != that.currentPage) {
                        that.prevPage = that.currentPage;
                        that.$refs.table.bodyWrapper.scrollTop = 0;
                        scrollToTop()
                    }
                    if (that.tableData.length === 0) {
                        that.emptytext = "暂无数据";
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        },

        reset: function () {
            this.keyword = '';
            this.keytype = '1';
            this.state = '';
            this.source = '';
            this.isuid = '';
            this.isresume = '';
            this.sqtime = [];
        },
        statusChange: function (status) {
            this.reset();
            if (status == '1') {
                this.isuid = '1';
            } else if (status == '2') {
                this.isuid = '2';
            } else if (status == '3') {
                this.isresume = '1'
            }
            this.search();
        },
        search: function () {
            this.currentPage = 1;
            this.getList();
        },
        sortChange: function (e) {
            this.sort_t = e.prop;
            if (e.prop == 'ctime_n') {
                this.sort_t = 'ctime';
            }
            this.order = e.order == 'ascending' ? 'asc' : 'desc';
            this.search();
        },
        changedate: function (e) {

        },
        handleCurrentChange(val) {
            this.currentPage = val;
            this.getList()
        },
        handleSizeChange(val) {
            this.currentPage = 1
            this.limit = val
            scrollToTop()
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
                message.error('请选择要删除的数据！');
                return;
            }
            var params = {
                del: idarr
            };

            delConfirm(_this, params, this.deletePost)
        },
        async deletePost(params) {

            let that = this;

            httpPost('m=crm&c=crm_chat&a=delPhoneAuth', params).then(function (result) {

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
        contact_customer: function (row) {

            var phone = row.phone;
            var uid = row.uid;
            var usertype = row.usertype;
            var resume = row.resume;

            var params = {
                mobile: phone,
                uid: uid,
                usertype: usertype
            };

            message.confirm('确定联系客户吗？', function () {
                httpPost('m=crm&c=crm_chat&a=authContactCustomer', params).then(function (result) {

                    var res = result.data;
                    if (res.error == '200') {
                        if (usertype == 1 && resume == 0) {
                            message.success(res.msg, function () {
                                that.addresume(uid);
                            });
                        } else {
                            message.success(res.msg);
                            return;
                        }

                    } else {
                        message.error(res.msg);
                        return;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            });
        },
        bzbox: function (row) {
            this.bzid = row.id;

            var bz_content = '';
            if (row.remark) {
                bz_content = row.remark;
            }
            this.bz_content = bz_content;

            this.bzshow = true;
        },
        setBz: function () {

            var that = this;
            if (that.bz_content == '') {
                message.warning('请填写备注内容');
                return;
            }
            var params = {
                id: that.bzid,
                content: that.bz_content
            };
            httpPost('m=crm&c=crm_chat&a=phoneauthRemark', params).then((result) => {
                this.bzshow = false;
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
        userPreview: function (uid) {
            this.ruid = uid;
            this.resumePreviewVisible = true;
        },
        addresume: function (uid) {
            this.aruid = uid;
            this.addresumeVisible = true;
        },
        closeAddresume: function () {
            this.aruid = '';
            this.addresumeVisible = false;
            this.getList();
        },
    },
};
</script>
<style>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 47%;
    margin: 3px 0;
}

.moduleIphonesq {
    height: calc(100% - 134px) !important;
}

@media (max-width: 1400px) {
    .moduleIphonesq {
        height: calc(100% - 175px) !important;
    }
}</style>