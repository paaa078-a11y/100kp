<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" @keyup.enter.native="search" placeholder="请输入内容" v-model="keyword"
                        class="input-with-select">
                        <el-select v-model="keytype" slot="prepend" placeholder="请选择">
                            <el-option label="个人姓名" value="1"></el-option>
                            <el-option label="企业名称" value="2"></el-option>
                            <el-option label="职位名称" value="3"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="moduleInptList">
                    <el-select size="small" v-model="time" placeholder="邀请时间" clearable @change="search">
                        <el-option label="今天" value="1"></el-option>
                        <el-option label="最近三天" value="3"></el-option>
                        <el-option label="最近七天" value="7"></el-option>
                        <el-option label="最近半月" value="15"></el-option>
                        <el-option label="最近一个月" value="30"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select size="small" v-model="is_browse" placeholder="邀请状态" clearable @change="search">
                        <el-option label="未查看" value="1"></el-option>
                        <el-option label="已查看" value="2"></el-option>
                        <el-option label="已同意" value="3"></el-option>
                        <el-option label="已拒绝" value="4"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-select size="small" v-model="status" placeholder="面试结果" clearable @change="search">
                        <el-option label="未标记" value="0"></el-option>
                        <el-option label="已入职" value="1"></el-option>
                        <el-option label="未面试" value="2"></el-option>
                        <el-option label="面试失败" value="3"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>

        </div>
        <div class="admin_datatip">
            <i class="el-icon-document"></i>
            <span class="admin_datatip_n">未标记：<el-link type="primary" :underline="false"
                    @click="statusChange('0')">{{ numarr.authNum0 }}</el-link> </span>
            <span class="admin_datatip_n">已入职：<el-link type="primary" :underline="false"
                    @click="statusChange('1')">{{ numarr.authNum1 }}</el-link> </span>
            <span class="admin_datatip_n">未面试：<el-link type="primary" :underline="false"
                    @click="statusChange('2')">{{ numarr.authNum2 }}</el-link> </span>
            <span class="admin_datatip_n">面试失败：<el-link type="primary" :underline="false"
                    @click="statusChange('3')">{{ numarr.authNum3 }}</el-link> </span>
        </div>
        <div class="moduleElTable" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table ref="table" :key="timer" :data="tableData" v-loading="list_loading"
                @mousedown.native="mouseDownHandler"
                @mouseup.native="mouseUpHandler"
                @mousemove.native="mouseMoveHandler"
                :default-sort="{ prop: 'id', order: 'descending' }" :empty-text="emptytext" @sort-change="sortChange" stripe
                style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }">
                <el-table-column prop="id" label="编号" width="80" sortable="custom">
                </el-table-column>
                <el-table-column label="个人姓名" width="80">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <el-link type="primary" :underline="false" @click="userPreview(props.row.uid)">{{
                                props.row.r_name }}</el-link>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="企业名称" width="240">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <el-link type="primary" :underline="false" :href="props.row.company_url" target="_blank">{{
                                props.row.fname }}</el-link>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="面试职位" width="280">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <el-link type="primary" :underline="false" :href="props.row.job_url" target="_blank">{{
                                props.row.jobname }}</el-link>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="time_n" label="邀请时间" width="160">
                </el-table-column>
                <el-table-column prop="intertime" label="面试时间" width="150">
                </el-table-column>
                <el-table-column label="状态" width="100">
                    <template slot-scope="scope">
                        <el-tag size="small" type="info" v-if="scope.row.is_browse == 1">未查看</el-tag>
                        <el-tag size="small" v-else-if="scope.row.is_browse == 2">已查看</el-tag>
                        <el-tag size="small" type="success" v-else-if="scope.row.is_browse == 3">已同意</el-tag>
                        <el-tag size="small" type="danger" v-else-if="scope.row.is_browse == 4">已拒绝</el-tag>
                    </template>
                </el-table-column>

                <el-table-column prop="msjieguo" label="面试结果" min-width="180">
                    <template slot-scope="scope">
                        <div v-if="scope.row.status == 1"><el-tag size="small" type="success">已入职</el-tag></div>

                        <div v-else-if="scope.row.status == 2">
                            <el-tag size="small" type="warning">
                                未面试
                                <el-tooltip v-if="scope.row.status_remark" class="item" effect="dark"
                                    :content="scope.row.status_remark" placement="right">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </el-tag>
                        </div>
                        <div v-else-if="scope.row.status == 3">
                            <el-tag size="small" type="danger">
                                面试失败
                                <el-tooltip v-if="scope.row.status_remark" class="item" effect="dark"
                                    :content="scope.row.status_remark" placement="right">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </el-tag>
                        </div>
                        <el-button v-else type="text" @click="crmSetStatus(scope.row)" icon="el-icon-edit">标记</el-button>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="120" v-if="is_outcall">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-tooltip class="item" effect="dark" content="点击拨号按钮，可使用虚拟号联系求职者" placement="right">
                                <el-button type=" " size="mini" @click="contact_customer(scope.row)">拨号</el-button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-table-column>

            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total"
                    layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-drawer title="简历预览" :append-to-body="true" :visible.sync="resumePreviewVisible" :destroy-on-close="true"
                size="530px">
                <resume_preview :uid="ruid" v-if="resumePreviewVisible"></resume_preview>
            </el-drawer>
        </div>
        <el-dialog title="面试标记" width="40%" :visible.sync="msshow" :modal-append-to-body="false">
            <div class="mianshihuiangs">
                <div class="dialog_item">
                    <div class="item_span">
                        <span>个人姓名：</span>
                    </div>
                    <div class="drawerModInpt">
                        {{ ms_name }}
                    </div>
                </div>
                <div class="dialog_item">
                    <div class="item_span">
                        <span>面试结果：</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-select v-model="ms_status" placeholder="请选择" style="flex: 1;" clearable>
                            <el-option label="已入职" value="1"></el-option>
                            <el-option label="未面试" value="2"></el-option>
                            <el-option label="面试失败" value="3"></el-option>
                        </el-select>
                    </div>
                </div>
                <div class="dialog_item">
                    <div class="item_span">
                        <span>面试说明：</span>
                    </div>
                    <div class="drawerModInpt">
                        <el-input type="textarea" v-model="ms_content" style="flex: 1;"></el-input>
                    </div>
                </div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="msshow = false">取 消</el-button>
                <el-button type="primary" @click="setStatus">确 定</el-button>
            </span>
        </el-dialog>
    </div>
</template>
<script>
module.exports = {
    data: function () {
        return {
            mouseFlag: false,
            mouseOffset: 0,
            emptytext: '暂无数据',
            numarr: [],
            timer: 0,
            tableData: [],
            total: 0,
            limit: 0,
            currentPage: 1,
            prevPage: 0,
            page_sizes: [],

            list_loading: false,

            allchecked: false,
            choosedata: [],

            is_browse: '',
            time: '',
            status: '',
            keyword: '',
            keytype: '1',
            time: '',
            daterange: [],
            sort_t: '',
            order: '',

            is_outcall: false,

            resumePreviewVisible: false,
            ruid: '',

            ms_id: '',
            ms_name: '',
            ms_status: '',
            ms_content: '',
            msshow: false,
        }
    },
    components: {
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
        statusChange: function (status) {
            this.status = status;
            this.getList();
        },
        getStaticData() {
            var that = this
            let params = {}
            httpPost('m=crm&c=crm_chat&a=getInviteStaticData', params, { hideloading: true }).then((result) => {
                var res = result.data;
                if (res.error == 0) {
                    that.numarr = res.data.numarr;
                    that.is_outcall = res.data.is_outcall;
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
                time: that.time,
                is_browse: that.is_browse,
                status: that.status,
            }

            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=crm&c=crm_chat&a=inviteList', params).then((result) => {
                this.list_loading = false;
                var res = result.data;

                if (res.error == 0) {

                    that.tableData = res.data.list
                    that.total = parseInt(res.data.total)
                    that.page_sizes = res.data.page_sizes
                    that.limit = res.data.page_size
                    if (that.prevPage != that.currentPage) {
                        that.prevPage = that.currentPage;
                        that.$refs.table.bodyWrapper.scrollTop = 0;
                        scrollToTop()
                    }
                    if (that.tableData.length === 0) {
                        that.emptytext = "暂无数据";
                    }
                }
                this.timer = new Date().getTime();
            }).catch(function (e) {
                console.log(e)
            })
        },
        search: function () {
            this.currentPage = 1;
            this.getList();
        },
        sortChange: function (e) {
            this.sort_t = e.prop;

            this.order = e.order == 'ascending' ? 'asc' : 'desc';
            this.search();
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

        userPreview: function (uid) {
            this.ruid = uid;
            this.resumePreviewVisible = true;
        },
        crmSetStatus: function (row) {
            this.ms_id = row.id;
            this.ms_name = row.r_name;
            this.ms_status = '';
            this.ms_content = '';
            this.msshow = true;
        },
        setStatus: function () {

            var that = this;
            var params = {
                id: that.ms_id,
                status: that.ms_status,
                content: that.ms_content
            };
            httpPost('m=crm&c=crm_chat&a=setStatus', params).then(function (result) {
                that.msshow = false;
                var res = result.data;
                if (res.error == 0) {
                    message.success(res.msg, function () { that.getList() });
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

            var phone = row.tel_phone;
            var uid = row.uid;
            var params = {
                mobile: phone,
                uid: uid,
            };

            message.confirm('确定联系客户吗？', function () {
                httpPost('m=crm&c=crm_chat&a=contactCustomer', params).then(function (result) {

                    var res = result.data;
                    if (res.error == '200') {
                        message.success(res.msg);
                        return;
                    } else {
                        message.error(res.msg);
                        return;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            });


        }
    },
};
</script>
<style scoped>.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 57px;
    margin: 3px 0;
}</style>