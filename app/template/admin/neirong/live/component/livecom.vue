<template>
    <div class="moduleElHight" style="padding-left: 10px;">
        <div class="moduleSeachbig" style="width: 100%;">
            <div class="tableSeachInpt">
                <el-input v-model="keyword" clearable placeholder="请输入搜索内容" size="small" prefix-icon="el-icon-search">
                </el-input>
            </div>
            <div class="tableSeachInpt">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
            </div>
            <div class="tableSeachInpt" style="margin-left: auto;">
                <el-button type="primary" plain icon="el-icon-plus" size="mini" @click="getAddComList">添加参会企业
                </el-button>
            </div>
        </div>
        <div class="moduleElTable" :class="{ 'moduleElMoreSixd': tableHig }"
            style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                @selection-change="handleSelectionChange" ref="multipleTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="comname" label="企业名称" min-width="180">
                </el-table-column>
                <el-table-column prop="jobname" label="参会职位" min-width="180" show-overflow-tooltip>
                </el-table-column>
                <el-table-column label="操作" fixed="right" width="150">
                    <template slot-scope="scope">
                        <div class="cz_button" style="margin-top: 10px;">
                            <el-button size="mini">
                                <el-link target="_blank" :href="scope.row.comurl">
                                    预览</el-link>
                            </el-button>
                            <el-button type="danger" size="mini" @click="delrow(scope.row.id)">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>
                <el-button @click="delAllBottom" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    :current-page="currentPage" :page-sizes="pageSizes" :page-size="perPage"
                    layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <!--添加参会企业-->
        <el-drawer title="添加参会企业" :visible.sync="draweraddcom" :modal-append-to-body="false" append-to-body size="80%">
            <div class="tctable_cont">
                <div class="moduleSeachs" style="padding: 0px; justify-content: initial;">
                    <!-- <div class="moduleElSearchInf">
                        <div class="moduleElTabInpt" style="flex-wrap: wrap;">
                            <div class="moduleInptList">
                                <el-input placeholder="输入你要搜索的关键字" v-model="addcom_keyword" class="input-with-select"
                                    clearable>
                                    <el-select v-model="com_type" slot="prepend" placeholder="请选择">
                                        <el-option label="企业名称/简称" value="1"></el-option>
                                        <el-option label="用户名称" value="2"></el-option>
                                        <el-option label="联系人" value="3"></el-option>
                                        <el-option label="联系电话" value="4"></el-option>
                                        <el-option label="用户邮箱" value="5"></el-option>
                                        <el-option label="用户ID" value="6"></el-option>
                                    </el-select>
                                </el-input>
                            </div>
                            <div class="tableSeachInpt">
                                <el-button type="primary" icon="el-icon-search" size="mini" @click="addComSearch">查询
                                </el-button>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="tableSeachInpt">
                        <el-input v-model="addcom_keyword" placeholder="请输入您要搜索的关键字" clearable size="small"
                            prefix-icon="el-icon-search">
                        </el-input>
                    </div> -->
                    <!--    新增 搜索方式       -->
                    <div class="addcanhuiComs">
                        <div class="addcanhuiOnes">
                            <div class="tableSeachInpt">
                                <el-input v-model="addcom_keyword" placeholder="请输入您要搜索的关键字" clearable size="small"
                                    prefix-icon="el-icon-search">
                                </el-input>
                            </div>
                            <div class="tableSeachInpt">
                                <el-select v-model="rating" size="small" slot="prepend" placeholder="会员等级" clearable @change="addComSearch">
                                    <el-option v-for="(value, key) in searchArr.ratingarr" :label="value" :value="key"
                                        :key="key"></el-option>
                                </el-select>
                            </div>
                            <div class="tableSeachInpt">
                                <el-select v-model="time" size="small" slot="prepend" placeholder="到期时间" clearable @change="addComSearch">
                                    <el-option v-for="(value, key) in searchArr.edtime" :label="value" :value="key"
                                        :key="key"></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="addcanhuiTwos">
                            <el-popover placement="top-start" width="380" trigger="click">
                                <div class="addcanhuiGend">
                                    <div class="tableSeachInpt">
                                        <el-select v-model="status" size="small" slot="prepend" placeholder="审核状态"
                                            clearable @change="addComSearch">
                                            <el-option v-for="(value, key) in searchArr.statusArr" :label="value"
                                                :value="key" :key="key"></el-option>
                                        </el-select>
                                    </div>
                                    <div class="tableSeachInpt">
                                        <el-select v-model="source" size="small" slot="prepend" placeholder="数据来源"
                                            clearable @change="addComSearch">
                                            <el-option v-for="(value, key) in searchArr.sourceArr" :label="value"
                                                :value="key" :key="key"></el-option>
                                        </el-select>
                                    </div>
                                    <div class="tableSeachInpt">
                                        <el-select v-model="rec" size="small" slot="prepend" placeholder="知名企业" clearable @change="addComSearch">
                                            <el-option v-for="(value, key) in searchArr.isrec" :label="value" :value="key"
                                                :key="key"></el-option>
                                        </el-select>
                                    </div>
                                    <div class="tableSeachInpt">
                                        <el-select v-model="gw" size="small" slot="prepend" placeholder="企业顾问" clearable @change="addComSearch">
                                            <el-option v-for="(value, key) in searchArr.isgw" :label="value" :value="key"
                                                :key="key"></el-option>
                                        </el-select>
                                    </div>
                                    <div class="tableSeachInpt">
                                        <el-select v-model="lotime" size="small" slot="prepend" placeholder="最近登录"
                                            clearable @change="addComSearch">
                                            <el-option v-for="(value, key) in searchArr.timeSection" :label="value"
                                                :value="key" :key="key"></el-option>
                                        </el-select>
                                    </div>
                                    <div class="tableSeachInpt">
                                        <el-select v-model="adtime" size="small" slot="prepend" placeholder="最近注册"
                                            clearable @change="addComSearch">
                                            <el-option v-for="(value, key) in searchArr.timeSection" :label="value"
                                                :value="key" :key="key"></el-option>
                                        </el-select>
                                    </div>
                                </div>
                                <div class="addcanhuiMore" slot="reference">更多</div>
                            </el-popover>
                        </div>
                    </div>
                    <!-- <div class="tableSeachInpt">
                        <el-select v-model="rating" size="small" slot="prepend" placeholder="会员等级" clearable @change="addComSearch">
                            <el-option v-for="(value, key) in searchArr.ratingarr" :label="value" :value="key"
                                :key="key"></el-option>
                        </el-select>
                    </div>
                    <div class="tableSeachInpt">
                        <el-select v-model="time" size="small" slot="prepend" placeholder="到期时间" clearable @change="addComSearch">
                            <el-option v-for="(value, key) in searchArr.edtime" :label="value" :value="key"
                                :key="key"></el-option>
                        </el-select>
                    </div> -->
                    <!-- <div class="tableSeachInpt">
                        <el-select v-model="status" size="small" slot="prepend" placeholder="审核状态" clearable @change="addComSearch">
                            <el-option v-for="(value, key) in searchArr.statusArr" :label="value" :value="key"
                                :key="key"></el-option>
                        </el-select>
                    </div>
                    <div class="tableSeachInpt">
                        <el-select v-model="source" size="small" slot="prepend" placeholder="数据来源" clearable @change="addComSearch">
                            <el-option v-for="(value, key) in searchArr.sourceArr" :label="value" :value="key"
                                :key="key"></el-option>
                        </el-select>
                    </div>
                    <div class="tableSeachInpt">
                        <el-select v-model="rec" size="small" slot="prepend" placeholder="知名企业" clearable @change="addComSearch">
                            <el-option v-for="(value, key) in searchArr.isrec" :label="value" :value="key"
                                :key="key"></el-option>
                        </el-select>
                    </div>
                    <div class="tableSeachInpt">
                        <el-select v-model="gw" size="small" slot="prepend" placeholder="企业顾问" clearable @change="addComSearch">
                            <el-option v-for="(value, key) in searchArr.isgw" :label="value" :value="key"
                                :key="key"></el-option>
                        </el-select>
                    </div>
                    <div class="tableSeachInpt">
                        <el-select v-model="lotime" size="small" slot="prepend" placeholder="最近登录" clearable @change="addComSearch">
                            <el-option v-for="(value, key) in searchArr.timeSection" :label="value" :value="key"
                                :key="key"></el-option>
                        </el-select>
                    </div>
                    <div class="tableSeachInpt">
                        <el-select v-model="adtime" size="small" slot="prepend" placeholder="最近注册" clearable @change="addComSearch">
                            <el-option v-for="(value, key) in searchArr.timeSection" :label="value" :value="key"
                                :key="key"></el-option>
                        </el-select>
                    </div> -->

                    <div class="tableSeachInpt">
                        <el-button type="primary" icon="el-icon-search" size="mini" @click="addComSearch">查询
                        </el-button>
                    </div>
                    <div class="tableSeachInpt">
                        <!--<el-button type="primary" plain icon="el-icon-plus" size="mini">添加参会企业-->
                        <!--</el-button>-->
                        <!--<el-button type="primary" plain icon="el-icon-plus" size="mini">导出参会企业</el-button>-->
                    </div>
                </div>
                <div class="moduleElTable" :class="{ 'moduleElMoreSixd': tableHig }"
                    style="border: 1px solid #ebeef5; width: calc(100% - 2px); margin-top: 0;">
                    <el-table :data="comlist" style="width: 100%" stripe
                        :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                        @selection-change="handleAddComSelectionChange" ref="addComMultipleTable" :empty-text="emptytext"
                        :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='addComSortChange'
                        v-loading="loading">
                        <el-table-column type="selection" :selectable="checkSelectSet" width="55"></el-table-column>
                        <el-table-column prop="uid" label="用户ID" sortable="custom" width="130"></el-table-column>
                        <el-table-column prop="comname" label="企业Logo">
                            <template slot-scope="scope">
                                <el-image :src="scope.row.logo" style="width:48px;height:48px">
                                    <div slot="error" class="image-slot">
                                        <img :src="com_icon" width="48px" height="48px" />
                                    </div>
                                </el-image>
                            </template>
                        </el-table-column>
                        <el-table-column prop="jobname" label="企业名称" min-width="120" show-overflow-tooltip>
                            <template slot-scope="scope">
                                <div class="">
                                    {{ scope.row.name }}
                                </div>
                                <div v-if="scope.row.status == '2'">
                                    <el-tooltip class="item" effect="dark" content="已锁定" placement="top-start">
                                        <i class="el-icon-lock" style="color: orange"></i>
                                    </el-tooltip>
                                </div>
                                <span
                                    v-if="todayetime <= scope.row.vipetime || (scope.row.vipetime == '0' && scope.row.rating > '0')">{{
                                        scope.row.rating_name }}</span>
                                <b v-else style="color: red;" class="oldrating">
                                    {{ scope.row.oldrating_name ? scope.row.oldrating_name : scope.row.rating_name }}
                                </b>
                                <div v-if="scope.row.vipetime == '0'" class="mt5">不限</div>
                                <div v-else class="mt5" :style="!(todayetime <= scope.row.vipetime) ? 'color: red;' : ''">
                                    {{ scope.row.vipetime_n }}
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="联系电话/认证">
                            <template slot-scope="scope">
                                <div class="moduleProps">
                                    <span class="yhm" v-if="scope.row.linktel || scope.row.linkphone">
                                        {{ scope.row.linktel ? scope.row.linktel : scope.row.linkphone }}
                                    </span>
                                </div>
                                <div class="rz_box">
                                    <el-tooltip v-if="scope.row.moblie_status == '1'" class="item" effect="dark"
                                        content="手机已绑定" placement="top-start">
                                        <el-button type="text" @click="sjrztc = true">
                                            <i class="rzicon rzicon_sjyrz"></i>
                                        </el-button>
                                    </el-tooltip>
                                    <el-tooltip v-else class="item" effect="dark" content="手机未绑定" placement="top-start">
                                        <el-button type="text" @click="sjrztc = true">
                                            <i class="rzicon rzicon_sjwrz"></i>
                                        </el-button>
                                    </el-tooltip>

                                    <el-tooltip
                                        v-if="scope.row.wxid != '' || scope.row.wxopenid != '' || scope.row.app_wxid != ''"
                                        class="item" effect="dark" placement="top-start">
                                        <span slot="content" v-html="scope.row.wxBindmsg"></span>
                                        <el-button type="text" @click="wxrztc = true">
                                            <i class="rzicon rzicon_wxyrz"></i>
                                        </el-button>
                                    </el-tooltip>
                                    <el-tooltip v-else class="item" effect="dark" placement="top-start">
                                        <span slot="content" v-html="scope.row.wxBindmsg"></span>
                                        <el-button type="text" @click="wxrztc = true">
                                            <i class="rzicon rzicon_wxwrz"></i>
                                        </el-button>
                                    </el-tooltip>
                                    <el-tooltip v-if="scope.row.yyzz_status == '1'" class="item" effect="dark"
                                        content="企业资质已认证" placement="top-start">
                                        <el-button type="text" @click="zzrztc = true">
                                            <i class="rzicon rzicon_zzyrz"></i>
                                        </el-button>
                                    </el-tooltip>
                                    <el-tooltip v-else class="item" effect="dark" content="企业资质未认证" placement="top-start">
                                        <el-button type="text" @click="zzrztc = true">
                                            <i class="rzicon rzicon_zzwrz"></i>
                                        </el-button>
                                    </el-tooltip>
                                    <el-tooltip v-if="scope.row.email_status == '1'" class="item" effect="dark"
                                        content="邮箱已认证" placement="top-start">
                                        <el-button type="text" @click="yxrztc = true">
                                            <i class="rzicon rzicon_yxyrz"></i>
                                        </el-button>
                                    </el-tooltip>
                                    <el-tooltip v-else class="item" effect="dark" content="邮箱未认证" placement="top-start">
                                        <el-button type="text" @click="yxrztc = true">
                                            <i class="rzicon rzicon_yxwrz"></i>
                                        </el-button>
                                    </el-tooltip>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="企业顾问">
                            <template slot-scope="scope">
                                <div class="moduleProps">
                                    <span class="gsd">{{ scope.row.crm_uid != '0' ? scope.row.crm_name : '未分配' }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="审核">
                            <template slot-scope="scope">
                                <div class="admin_state">
                                    <span v-if="scope.row.r_status == '1'" class="admin_state1">已审核</span>
                                    <span v-else-if="scope.row.r_status == '3'" class="admin_state2">未通过</span>
                                    <span v-else-if="scope.row.r_status == '2'" class="admin_state3">已锁定</span>
                                    <span v-else-if="scope.row.r_status == '4'" class="admin_state5">已暂停</span>
                                    <span v-else class="admin_state4">待审核</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="职位数">
                            <template slot-scope="scope">
                                <span :style="scope.row.jobnum == '0' ? 'color:red' : ''">{{ scope.row.jobnum }}</span>个
                            </template>
                        </el-table-column>
                        <el-table-column prop="space_n" label="参会情况">
                            <template slot-scope="scope">
                                <span :style="scope.row.join == '1' ? 'color:red' : ''">
                                    {{ scope.row.join == '1' ? '已参加' : '未参加' }}
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column label="操作" fixed="right" width="150" align="center">
                            <template slot-scope="scope">
                                <div class="cz_button">
                                    <el-button size="mini">
                                        <el-link target="_blank" :href="scope.row.comUrl">
                                            预览</el-link>
                                    </el-button>

                                    <el-button
                                        v-if="scope.row.jobnum > 0 && scope.row.r_status == '1' && scope.row.join != '1'"
                                        size="mini" plain @click="addcom(scope.row.uid)">加入
                                    </el-button>
                                    <div v-else style="color: red;">
                                        <span v-if="scope.row.join == '1'">已参加</span>
                                        <span v-if="scope.row.r_status != '1'">企业未审核</span>
                                        <span v-if="scope.row.jobnum <= '0'">无职位</span>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="modulePaging" style="padding-left: 0px;">
                    <div>
                        <el-checkbox v-model="addComCheckedAll" @change="addComSelectAllBottom">全选</el-checkbox>
                        <el-button @click="addcommultiple" size="mini">批量加入</el-button>
                    </div>
                    <div class="modulePagNum">
                        <el-pagination background @size-change="handleAddComSizeChange"
                            @current-change="handleAddComCurrentChange" :current-page="addComCurrentPage"
                            :page-sizes="addComPageSizes" :page-size="addComPerPage"
                            layout="total, sizes, prev, pager, next, jumper" :total="addComTotal">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </el-drawer>
    </div>
</template>
<script>
module.exports = {
    props: {
        id: {
            type: String,
            default: ''
        },
    },
    data: function () {
        return {
            emptytext: '暂无数据',
            loading: false,
            xid: '',
            keyword: '',
            checkedAll: false,
            selectedItem: [],
            tableData: [],
            currentPage: 1,
            perPage: 0,
            pageSizes: [],
            total: 0,
            islook: false,
            tableHig: true,
            draweraddcom: false,
            addcom_keyword: '',
            comlist: [],
            addComCheckedAll: false,
            addComSelectedItem: [],
            addComCurrentPage: 1,
            addComPerPage: 0,
            addComPageSizes: [],
            addComTotal: 0,
            todayetime: 0,
            com_type: '1',
            com_icon: '',
            searchArr: {},
            time: '',
            adtime: '',
            lotime: '',
            gw: '',
            rec: '',
            rating: '',
            status: '',
            source: '',
            prevPage:0,
            prevPage1:0
        }
    },
    mounted() {

    },
    components: {
        // 'comadd': httpVueLoader('./livecomadd.vue'),
    },
    watch: {
        id: {
            handler(val, oldVal) {
                this.xid = val;
                if (val) {
                    this.search()
                }
            },
            immediate: true,
            deep: true,
        },
    },
    methods: {
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
        search() {
            this.currentPage = 1;
            this.getList();
        },
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                pageSize: that.perPage
            }
            if (that.keyword) {
                params.keyword = that.keyword
            }
            if (that.xid) {
                params.id = that.xid
            }
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=neirong&c=xjhlive&a=com', params).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.tableData = res.data.list
                    that.perPage = parseInt(res.data.perPage)
                    that.pageSizes = res.data.pageSizes
                    that.total = parseInt(res.data.total)
                    if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                    }
                    that.loading = false;
                    if (that.tableData.length === 0) {
                        that.emptytext = "暂无数据";
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        delrow(id) {
            delConfirm(this, id, this.delete);
        },
        delAllBottom() {
            if (!this.selectedItem.length) {
                message.error('请选择要删除的数据');
                return false;
            }
            delConfirm(this, this.selectedItem, this.delete);
        },
        async delete(id) {
            let that = this;
            let params = {
                del: id,
                xid: that.xid
            };
            httpPost('m=neirong&c=xjhlive&a=delcom', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg);
                    that.getList();
                    that.$parent.$parent.getList()
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        // 添加参会企业---获取企业列表
        getAddComList() {
            var that = this
            var params = {
                id: that.xid,
                page: that.addComCurrentPage, pageSize: that.addComPerPage,
                time: that.time,
                adtime: that.adtime,
                lotime: that.lotime,
                gw: that.gw,
                rec: that.rec,
                rating: that.rating,
                status: that.status,
                source: that.source,
            }
            if (that.com_type) {
                params.type = that.com_type
                params.keyword = that.addcom_keyword
            }
            if (that.sort_type && that.sort_col) {
                params.order = that.sort_type
                params.t = that.sort_col
            }
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=neirong&c=xjhlive&a=comadd', params, { loading: true }).then(function (response) {
                if (response.data.error == 0) {
                    that.comlist = response.data.data.list
                    that.addComPerPage = parseInt(response.data.data.perPage)
                    that.addComPageSizes = response.data.data.pageSizes
                    that.addComTotal = parseInt(response.data.data.total)
                    that.todayetime = response.data.data.today_etime
                    that.com_icon = response.data.data.com_icon
                    that.getSearchSelect();
                    if(that.prevPage1 != that.addComCurrentPage && that.$refs.addComMultipleTable){
                        that.prevPage1 = that.addComCurrentPage;
                        that.$refs.addComMultipleTable.bodyWrapper.scrollTop = 0;
                    }
                    that.loading = false;
                    that.draweraddcom = true;
                    if (that.comlist.length === 0) {
                        that.emptytext = "暂无数据";
                    }
                } else {
                    message.error('获取企业信息失败');
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        getSearchSelect: async function () {
            let response = await httpPost('m=neirong&c=xjhlive&a=comaddsearch', {}, { hideLoading: true });
            let res = response.data;
            if (res.error == 0) {
                this.searchArr = res.data;
            }

        },
        addComSearch() {
            this.addComCurrentPage = 1;
            this.getAddComList();
        },
        handleAddComSelectionChange(val) {
            this.addComSelectedItem = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.addComSelectedItem.push(item.uid);
                });
            }
            if (_this.addComSelectedItem.length == 0) {
                _this.addComCheckedAll = false;
            } else {
                if (_this.addComSelectedItem.length == _this.comlist.length) {
                    _this.addComCheckedAll = true;
                } else {
                    _this.addComCheckedAll = false;
                }
            }
        },
        addComSelectAllBottom(value) {
            value ? this.$refs.addComMultipleTable.toggleAllSelection() : this.$refs.addComMultipleTable.clearSelection();
        },
        handleAddComSizeChange(val) {
            this.addComPerPage = val;
            this.getAddComList()
        },
        handleAddComCurrentChange(val) {
            this.addComCurrentPage = val;
            this.getAddComList()
        },
        addComSortChange: function (column) {
            if (column.order == 'descending') {
                this.sort_type = 'desc';
            } else if (column.order == 'ascending') {
                this.sort_type = 'asc';
            } else {
                this.sort_type = '';
            }
            this.sort_col = column.prop
            this.addComSearch();
        },
        // 添加参会企业
        addcom(uid) {
            var that = this
            message.confirm('确认添加该企业？', function () {
                var params = {
                    xid: that.xid,
                    comid: uid,
                }
                that.doaddcom(params)
            })
        },
        // 批量添加参会企业
        addcommultiple() {
            var that = this
            if (that.addComSelectedItem.length == 0) {
                message.error('您还未选择参会企业！');
                return false
            }
            message.confirm('确认添加至该直播？', function () {
                var params = {
                    xid: that.xid,
                    comid: that.addComSelectedItem.join(','),
                }
                that.doaddcom(params)
            })
        },
        async doaddcom(params) {
            var that = this
            httpPost('m=neirong&c=xjhlive&a=comaddsave', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg, function () {
                        that.search()
                        that.getAddComList()
                    });
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        // 添加参会企业行数据是否可选中
        checkSelectSet(row, index) {
            return row.jobnum > 0 && row.r_status == '1' && row.join != '1' ? true : false
        },
    },
};
</script>
<style>  .el-dialog__body {
      padding: 0px 20px;
  }

  .wxsettip_small {
      padding: 20px 0
  }

  .yd_ztkyd_active {
      background-color: #409eff;
      color: #fff;
  }

  .admin_web_tip {
      display: inline-block;
      line-height: 16px;
      padding-left: 20px;
      color: #999;
      font-size: 12px;
      padding-top: 8px;
  }
</style>