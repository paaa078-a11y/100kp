<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" @keyup.enter.native="search" v-model="searchForm.keyword" class="input-with-select" placeholder="输入你要搜索的关键字" clearable>
                        <el-select v-model="searchForm.type" slot="prepend" placeholder="请选择">
                            <el-option label="课程名称" :value="1"></el-option>
                            <el-option label="地址" :value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div v-for="(searchItem, searchIndex) in searchList" :key="searchIndex" class="moduleInptList">
                    <el-select size="small" v-model="searchForm[searchItem.param]" slot="prepend" :clearable="true" :placeholder="searchItem.name" @change="search">
                        <el-option v-for="(searchLabel, searchValue) in searchItem.value" :key="searchValue" :label="searchLabel" :value="searchValue"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="admin_datatip">
            <i class="el-icon-document"></i>
            数据统计：共 <span @click="init">{{subjectAllNum}}</span> 条
            <span class="admin_datatip_n">未审核：<span @click="statusSearch('3')">{{subjectStatusNum1}}</span> 条 </span>
            <span class="admin_datatip_n">未通过：<span @click="statusSearch('2')">{{subjectStatusNum2}}</span> 条</span>
            <span class="admin_datatip_n">搜索结果： {{total}} 条</span>
        </div>
        <div class="moduleElTable moduleElMoreFives" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="list" stripe style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange" @sort-change="sortChange" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="编号" width="120" sortable="custom">
                </el-table-column>
                <el-table-column prop="username" label="用户名" width="180">
                </el-table-column>
                <el-table-column label="课程名称" min-width="230">
                    <template slot-scope="scope">
                        <el-link :underline="false" @click="openShow(scope.row)">{{scope.row.name}}</el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="hours" label="课时" width="100">
                </el-table-column>
                <el-table-column prop="price" label="价格" width="120">
                </el-table-column>
                <el-table-column label="收费方式" width="120">
                    <template slot-scope="scope">
                        <span v-if="scope.row.isprice == 1">在线收费</span>
                        <span v-else>到场收费</span>
                    </template>
                </el-table-column>
                <el-table-column label="城市" width="180">
                    <template slot-scope="scope">
                        <div>{{scope.row.city}} {{scope.row.threecity}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="ctime" label="发布时间" width="150" sortable="custom">
                    <template slot-scope="scope">
                        <div>{{scope.row.ctime_n}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="状态" min-width="120">
                    <template slot-scope="scope">
                        <div class="admin_state">
                            <span v-if="scope.row.status == 1" class="admin_state1">已审核</span>
                            <span v-else-if="scope.row.status == 2" class="admin_state2">未通过</span>
                            <span v-else class="admin_state5">未审核</span>
                            <i v-if="scope.row.r_status == 2" class="el-icon-lock admin_state3"></i>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="推荐" min-width="120">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <el-switch v-model="scope.row.rec" active-color="#13ce66" inactive-color="#ff4949" active-value="1" inactive-value="0" @change="changeRec($event, scope.row)">
                            </el-switch>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="180">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="openAudit(scope.row)">审核</el-button>
                            <el-button size="mini" @click="openEdit(scope.row)">修改</el-button>
                            <el-button size="mini" @click="del(scope.$index)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate" @change="checkAll">全选</el-checkbox>
                <el-button @click="batch('del')" size="mini">批量删除</el-button>
                <el-button @click="batch('audit')" size="mini">批量审核</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page="page" :page-sizes="pageSizes" :page-size="limit" layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <!-- 弹窗 -->
        <div class="modluDrawer">
            <el-dialog title="培训课程审核" :visible.sync="dialogAudit" :with-header="true" :modal-append-to-body="false" :show-close="true" width="500px">
                <div class="toolClasDia fenpeizhand" v-if="detail.id && detail.r_status != 1">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>培训机构：</span>
                        </div>
                        <div class="toolClasCont">
                            {{detail.username}}
                            <span v-if="detail.r_status == 0" style="color: red">【未审核】</span>
                            <span v-else-if="detail.r_status == 3" style="color: red">【未通过】</span>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>讲师审核：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio v-model="ruleFormAudit.r_status" label="1">正常</el-radio>
                            <el-radio v-model="ruleFormAudit.r_status" label="3">未通过</el-radio>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>机构审核：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-checkbox v-model="ruleFormAudit.istb" disabled :true-label="1" :false-label="0">同步</el-checkbox>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>同步说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-alert title="培训机构身份状态根据课程状态同步审核" type="info" :closable="false" show-icon>
                            </el-alert>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="ruleFormAudit.statusbody">
                            </el-input>
                        </div>
                    </div>
                </div>
                <div class="toolClasDia fenpeizhand" v-else>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核操作：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio v-model="ruleFormAudit.status" label="1">正常</el-radio>
                            <el-radio v-model="ruleFormAudit.status" label="2">未通过</el-radio>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="ruleFormAudit.statusbody">
                            </el-input>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogAudit = false">取 消</el-button>
                    <el-button type="primary" @click="submitAudit">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <div class="modluDrawer">
            <el-drawer title="培训课程信息" :visible.sync="drawerEdit" :modal-append-to-body="false" :show-close="true" :with-header="true" size="55%" @opened="openedEdit">
                <div class="drawerModInfo drawerModInfoOne">
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>课程名称</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-input v-model="ruleFormEdit.name" placeholder=""></el-input>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>课程类别</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-select v-model="ruleFormEdit.nid" placeholder="请选择" @change="changeSubject">
                                <el-option v-for="subject in subjectList" :key="subject.id" :label="subject.name" :value="subject.id">
                                </el-option>
                            </el-select>
                            <el-select v-model="ruleFormEdit.tnid" placeholder="请选择" style="margin-left: 8px;">
                                <el-option v-for="subjectTwo in subjectTwoList" :key="subjectTwo.id" :label="subjectTwo.name" :value="subjectTwo.id">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>培训地点</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-select v-model="ruleFormEdit.provinceid" placeholder="请选择" @change="changeCity($event, 1)">
                                <el-option v-for="item in provinceList" :key="item.id" :label="item.name" :value="item.id">
                                </el-option>
                            </el-select>
                            <el-select v-model="ruleFormEdit.cityid" placeholder="请选择" style="margin: 0 8px;" @change="changeCity($event, 2)">
                                <el-option v-for="item in cityList" :key="item.id" :label="item.name" :value="item.id">
                                </el-option>
                            </el-select>
                            <el-select v-model="ruleFormEdit.threecityid" placeholder="请选择" clearable>
                                <el-option v-for="item in regionList" :key="item.id" :label="item.name" :value="item.id">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>培训地址</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-input v-model="ruleFormEdit.address" placeholder=""></el-input>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>课程学时</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-input v-model="ruleFormEdit.hours" placeholder=""></el-input>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>培训费用</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-input v-model="ruleFormEdit.price" placeholder=""></el-input>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>收费方式</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-select v-model="ruleFormEdit.isprice" placeholder="请选择">
                                <el-option key="1" label="在线支付" value="1"></el-option>
                                <el-option key="2" label="到场支付" value="2"></el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>报名电话</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-input v-model="ruleFormEdit.moblie" placeholder="" @input="inputMoblie($event, 'ruleFormEdit', 'moblie')"></el-input>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>适合人群</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-input type="textarea" :rows="2" placeholder="" v-model="ruleFormEdit.crowd">
                            </el-input>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>课程优势</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-input type="textarea" :rows="2" placeholder="" v-model="ruleFormEdit.superiority">
                            </el-input>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>课程介绍</span>
                        </div>
                        <div class="drawerModInpt">
                            <div id="editor—wrapper" style="border: 1px solid #ccc;width: 100%;">
                                <div id="toolbar-container"></div>
                                <div id="editor-container" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>开课类型</span>
                        </div>
                        <div class="drawerModInpt drawerModlxings">
                            <el-checkbox-group v-model="ruleFormEdit.type" size="small">
                                <el-checkbox v-for="(subjecttype,idx) in subjecttypeList" :key="idx" :label="subjecttype.id" border>
                                    {{subjecttype.name}}
                                </el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </div>
                    <div class="drawerModLis" v-if="teacherList.length > 0">
                        <div class="drawerModTite">
                            <span>主讲老师</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-checkbox-group v-model="ruleFormEdit.teachid" size="small">
                                <el-checkbox v-for="(teacher,tkey) in teacherList" :key="tkey" :label="teacher.id" border>{{teacher.name}}</el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </div>
                    <div class="drawerModLis">
                        <div class="drawerModTite">
                            <span>课程缩略图</span>
                        </div>
                        <div class="drawerModInpt">
                            <el-upload class="upload-demo" :accept="pic_accept" list-type="picture" action="" :auto-upload="false" :on-change="handleChangePic" :show-file-list="false">
                                <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                                <img class="el-upload-list__item-thumbnail" width="150" height="90" style="padding-left: 5px;" v-if="ruleFormEdit.pic_n" :src="ruleFormEdit.pic_n" />
                            </el-upload>
                        </div>
                        <div class="drawerModTips">
                            <el-alert title="尺寸：480*270px" type="info" show-icon :closable="false">
                            </el-alert>
                        </div>
                    </div>
                </div>
                <div class="setBasicButn" style="border: none;">
                    <el-button type="primary" size="medium" @click="saveEdit" :disabled="saveLoading">提交</el-button>
                </div>
            </el-drawer>
        </div>
    </div>
</template>
<script>
    var editorInterval = null;
module.exports = {
    props: {
        status: { type: String, default: '' }
    },
    data: function() {
        return {
            pic_accept: localStorage.getItem("pic_accept"),
            loading: false,
            dataText: '数据加载中',
            // 搜索筛选项
            searchList: [],
            searchForm: {
                type: 1,
                status: this.status,
            },

            // 列表
            page: 1,
            limit: 0,
            list: [],
            total: 0,
            pageSizes: [],

            // 列表排序
            t: '',
            order: '',

            checkedAll: false, // 全选
            checkedAllIndeterminate: false,
            multipleSelection: [], // 多选值存储
            idArr: [],

            detail: {},

            subjectAllNum: 0,
            subjectStatusNum1: 0,
            subjectStatusNum2: 0,

            saveLoading: false,

            // 审核
            dialogAudit: false,
            ruleFormAudit: {},

            // 编辑
            drawerEdit: false,
            ruleFormEdit: {},
            teacherList: [],
            subjectList: [],
            subjectTwoList: [],
            subjecttypeList: [],
            subjecttypeChildList: [],
            provinceList: [],
            cityList: [],
            regionList: [],

            prevPage: 0
        }
    },

    mounted() {
        var that = this
        setTimeout(function () {
            that.getSearchFun();
        }, 200)
    },
    created() {
        this.init();
    },
    methods: {
        init() {
            // this.resetSearch();
            this.getCountData();
            this.search();
        },
        getSearchFun:function(){
            let that = this;
            httpPost('m=user&c=peixun_subject&a=getSearchData', {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    that.searchList = res.data.search_list;
                }
            })
        },
        resetSearch() {
            this.searchForm = {
                type: 1
            };
            this.limit = 0;
        },

        statusSearch(status) {
            this.searchForm.status = status;
            this.search();
        },

        getCountData() {
            let that = this;

            httpPost('m=user&c=peixun_subject&a=subjectNum', {}, { hideloading: true }).then(function(response) {
                let res = response.data;

                that.subjectAllNum = res.subjectAllNum ? res.subjectAllNum : 0;
                that.subjectStatusNum1 = res.subjectStatusNum1 ? res.subjectStatusNum1 : 0;
                that.subjectStatusNum2 = res.subjectStatusNum2 ? res.subjectStatusNum2 : 0;
            })
        },

        handleSizeChange(val) {
            this.limit = val;
            this.getList();
        },
        handleCurrentChange(val) {
            this.page = val;
            this.getList();
        },
        sortChange(event) {
            this.t = event.order ? event.prop : '';
            this.order = event.order ? event.order == 'descending' ? 'desc' : 'asc' : '';
            this.search();
        },
        search() {
            this.page = 1;
            this.getList();
        },
        getList() {
            let that = this,
                searchForm = that.searchForm,
                params = {
                    page: that.page,
                    limit: that.limit,
                    t: that.t,
                    order: that.order,
                };
            that.loading = true;

            httpPost('m=user&c=peixun_subject', { ...params, ...searchForm }, {hideloading: true}).then(function(response) {
                let res = response.data,
                    data = res.data;

                that.list = data.list;
                that.total = parseInt(data.total);
                that.pageSizes = data.page_sizes;
                if (that.limit === 0) {
                    that.limit = parseInt(data.limit); // 取系统配置默认数量
                }
                if (that.page > data.page) {
                    that.page = parseInt(data.page); // 最后一页被删除后，取最新的页数
                }
                that.loading = false;
                if(that.prevPage != that.page){
                    that.prevPage = that.page;
                    that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                }
                if (that.list.length === 0) {
                    that.dataText = "暂无数据";
                }
            })
        },

        // 批量操作
        handleSelectionChange(val) {
            if (val.length == 0) {
                this.checkedAll = false;
                this.checkedAllIndeterminate = false;
            } else {
                if (val.length === this.list.length) {
                    this.checkedAll = true;
                    this.checkedAllIndeterminate = false;
                } else {
                    this.checkedAll = false;
                    this.checkedAllIndeterminate = true;
                }
            }
            this.multipleSelection = val;
        },
        batch(type) {
            if (this.multipleSelection.length == 0 && type == 'del') {
                message.error('请选择要删除的数据');
                return false;
            } else if (this.multipleSelection.length == 0) {
                message.error('请选择要操作的数据项');
                return false;
            }

            let idArr = [];
            this.multipleSelection.forEach(function(item) {
                idArr.push(item.id);
            })
            this.idArr = idArr;

            if (type == 'del') {
                this.del();
            } else if (type == 'audit') {
                this.openAudit();
            }
        },
        checkAll(val) {
            val ? this.checkedAllIndeterminate = false : '';
            this.$refs.multipleTable.toggleAllSelection();
        },

        del(idx) {
            let that = this,
                params = {},
                msg = '';

            if (typeof idx == 'undefined') { // 批量删除
                params.del = this.idArr;
                msg = '你确定要删除选中项吗？';
            } else { // 单个删除
                params.del = that.list[idx].id;
                msg = '你确定要删除当前项吗？';
            }

            delConfirm(this, params, function(params) {
                httpPost('m=user&c=peixun_subject&a=del', params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        that.getList();
                        that.$refs.multipleTable.clearSelection();
                        message.success(res.data.msg);
                    }
                })
            }, msg)
        },

        changeRec(val, row) {
            let that = this;

            httpPost('m=user&c=peixun_subject&a=rec', { id: row.id, rec: val }).then(function(res) {
                if (res.data.error > 0) {
                    message.error(res.data.msg);
                }
            })
        },

        openAudit(row) {
            let that = this;

            if (typeof row != 'undefined') { // 单个审核
                that.detail = row;
                if (row.r_status != 1) {
                    if (row.r_status == 2) {
                        message.error('用户已锁定,无法审核相关信息');
                        return false;
                    }

                    that.ruleFormAudit = {
                        id: row.id,
                        uid: row.uid,
                        r_status: row.r_status,
                        istb: 1,
                        statusbody: row.statusbody,
                    };
                } else {
                    that.ruleFormAudit = {
                        id: row.id,
                        status: row.status,
                        statusbody: row.statusbody,
                    };
                }
            } else { // 批量审核
                that.detail = {};
                that.ruleFormAudit = {
                    id: this.idArr,
                    status: '',
                    statusbody: '',
                };
            }
            that.dialogAudit = true;
        },

        submitAudit() {
            let that = this,
                params = that.ruleFormAudit,
                url = 'm=user&c=peixun_subject&a=status';

            if (typeof that.detail.r_status !== 'undefined' && that.detail.r_status != 1) { // 账号同步审核
                url = 'm=user&c=peixun_subject&a=subjectstatus';
                if (typeof params.r_status == 'undefined' || params.r_status === '' || params.r_status === '0') {
                    message.error('请选择审核状态');
                    return false;
                }
            } else {
                if (typeof params.status == 'undefined' || params.status === '' || params.status === '0') {
                    message.error('请选择审核状态');
                    return false;
                }
            }

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            httpPost(url, params).then(function(res) {
                that.saveLoading = false;

                if (res.data.error > 0) {
                    message.error(res.data.msg);
                } else {
                    that.dialogAudit = false;
                    that.getList();
                    message.success(res.data.msg, function() {
                        that.$refs.multipleTable.clearSelection();
                    });
                }
            })
        },

        getCity(level) {
            let that = this;

            httpPost('m=common&c=cache&a=getCity', {
                provinceid: that.ruleFormEdit.provinceid,
                cityid: that.ruleFormEdit.cityid,
                level: typeof level !== "undefined" ? level : ''
            }, { hideLoading: true }).then(function(response) {
                let res = response.data,
                    data = res.data;

                if (typeof data.provinceList !== "undefined") {
                    that.provinceList = data.provinceList;
                }
                if (typeof data.cityList !== "undefined") {
                    that.cityList = data.cityList;
                }
                if (typeof data.regionList !== "undefined") {
                    that.regionList = data.regionList;
                }
            })
        },

        getSubject() {
            let that = this;

            httpPost('m=user&c=peixun_subject&a=getSubject', { nid: that.ruleFormEdit.nid }, { hideLoading: true }).then(function(response) {
                let res = response.data,
                    data = res.data;

                that.subjectTwoList = data.subjectList;
            })
        },

        changeCity(val, level) {
            if (level == 1) {
                this.ruleFormEdit.provinceid = val;
                this.ruleFormEdit.cityid = '';
                this.ruleFormEdit.threecityid = '';
            } else if (level == 2) {
                this.ruleFormEdit.cityid = val;
                this.ruleFormEdit.threecityid = '';
            }
            this.getCity(level);
        },

        changeSubject(val) {
            this.ruleFormEdit.nid = val;
            this.ruleFormEdit.tnid = '';
            this.getSubject();
        },

        inputMoblie(val, form, key) {
            this.$data[form][key] = val.replace(/[^0-9-]/g, '');
        },

        openEdit(row) {
            let that = this;

            httpPost('m=user&c=peixun_subject&a=getInfo', { id: row.id }, { hideLoading: true }).then(function(response) {
                let res = response.data,
                    data = res.data;

                if (res.error > 0) {
                    message.error(res.msg);
                    return false;
                }

                that.teacherList = data.teacherList;
                that.subjectList = data.subjectList;
                that.subjecttypeList = data.subjecttypeList;

                let info = data.info;
                that.ruleFormEdit = {
                    id: row.id,
                    uid: row.uid,
                    name: info.name,
                    nid: info.nid > 0 ? info.nid : '',
                    tnid: info.tnid > 0 ? info.tnid : '',
                    provinceid: info.provinceid > 0 ? info.provinceid : '',
                    cityid: info.cityid > 0 ? info.cityid : '',
                    threecityid: info.threecityid > 0 ? info.threecityid : '',
                    address: info.address,
                    hours: info.hours,
                    price: info.price,
                    isprice: info.isprice,
                    moblie: info.moblie,
                    crowd: info.crowd,
                    superiority: info.superiority,
                    type: info.type,
                    teachid: info.teachid,
                    r_status: info.r_status,
                    content: info.content,
                    pic_n: info.pic,
                };

                that.getSubject();
                that.getCity();
                that.drawerEdit = true;
                if (editor) {
                    editor.setHtml(row.content);
                }
            })
        },

        openedEdit() {
            editorInterval = setInterval(()=>{
                if (editor !== null){
                    clearInterval(editorInterval);
                }else{
                    let editorConfig = {
                        MENU_CONF: {
                            uploadImage: {
                                server: baseUrl + 'm=index&c=uploadfile',
                                fieldName: 'file'
                            }
                        }
                    };
                    editor = createEditor({
                        selector: '#editor-container',
                        html: '',
                        config: editorConfig,
                        mode: 'simple'
                    });

                    let toolbar = createToolbar({
                        editor,
                        selector: '#toolbar-container',
                        config: {
                            'toolbarKeys': [
                                "bold",
                                "underline",
                                "italic",
                                "clearStyle",
                                "|",
                                "bulletedList",
                                "numberedList",
                                "justifyLeft",
                                "justifyRight",
                                "justifyCenter",
                                "emotion",
                                {
                                    iconSvg: "<svg viewBox=\"0 0 1024 1024\"><path d=\"M959.877 128l0.123 0.123v767.775l-0.123 0.122H64.102l-0.122-0.122V128.123l0.122-0.123h895.775zM960 64H64C28.795 64 0 92.795 0 128v768c0 35.205 28.795 64 64 64h896c35.205 0 64-28.795 64-64V128c0-35.205-28.795-64-64-64zM832 288.01c0 53.023-42.988 96.01-96.01 96.01s-96.01-42.987-96.01-96.01S682.967 192 735.99 192 832 234.988 832 288.01zM896 832H128V704l224.01-384 256 320h64l224.01-192z\"></path></svg>",
                                    key: "group-image",
                                    menuKeys: ['insertImage', 'uploadImage'],
                                    title: "图片"
                                }
                            ]
                        },
                        mode: 'simple'
                    });

                    editor.setHtml(this.ruleFormEdit.content); // 首次打开抽屉时初始数据
                }
            },300);

            
        },

        // 上传时触发
        handleChangePic(file, fileList) {
            this.$set(this.ruleFormEdit, 'file', file.raw);
            this.$set(this.ruleFormEdit, 'pic_n', file.url);
        },

        saveEdit() {
            let that = this,
                ruleForm = that.ruleFormEdit,
                content = editor.getHtml(),
                formData = new FormData();

            if (ruleForm.name === '') {
                message.error('请输入课程名称');
                return false;
            }
            if (ruleForm.tnid === '') {
                message.error('请选择课程类别');
                return false;
            }
            if (that.provinceList.length > 0 && ruleForm.provinceid === '') {
                message.error('请选择培训地点');
                return false;
            }
            if (that.cityList.length > 0 && ruleForm.cityid === '') {
                message.error('请选择培训地点');
                return false;
            }
            // if (that.regionList.length > 0 && ruleForm.threecityid === '') {
            //     message.warning('请选择培训地点');
            //     return false;
            // }
            if (ruleForm.address === '') {
                message.error('请输入培训地址');
                return false;
            }
            if (ruleForm.hours === '') {
                message.error('请输入课程学时');
                return false;
            }
            if (ruleForm.price === '') {
                message.error('请输入培训费用');
                return false;
            }
            if (ruleForm.isprice === '') {
                message.error('请选择收费方式');
                return false;
            }
            if (ruleForm.moblie === '') {
                message.error('请输入报名电话');
                return false;
            }

            let mobileReg = /^\d{3,4}-?\d{7,8}$/;
            var phoneReg = /^[1][3456789]\d{9}$/;
            if (!mobileReg.test(ruleForm.moblie) && !phoneReg.test(ruleForm.moblie)) {
                message.error('报名电话格式错误');
                return false;
            }

            if (ruleForm.crowd === '') {
                message.error('请输入适合人群');
                return false;
            }
            if (content == '' || content == '<p><br></p>') {
                message.error('请输入课程介绍');
                return false;
            }
            if (ruleForm.type.length === 0) {
                message.error('请选择开课类型');
                return false;
            }

            $.each(ruleForm, function(key, value) {
                if (key != 'content' && key != 'pic_n') {
                    formData.append(key, value);
                }
            });

            if (that.saveLoading) {
                return false;
            }
            that.saveLoading = true;

            formData.append('content', content);

            httpPost('m=user&c=peixun_subject&a=save', formData).then(function(response) {
                let res = response.data;

                if (res.error > 0) {
                    that.saveLoading = false;
                    message.error(res.msg);
                } else {
                    that.drawerEdit = false;
                    that.getList();
                    message.success(res.msg, function() {
                        that.saveLoading = false;
                    });
                }
            })
        },

        openShow(row) {
            window.open(row.show_url);
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

.el-checkbox.is-bordered.el-checkbox--small {
    height: 29px;
}

.el-checkbox {
    margin-right: 0;
}

.drawerModInfo {
    overflow-y: auto;
    height: initial;
    max-height: calc(100% - (80px + 12px));
}
.drawerModlxings .el-checkbox.is-bordered.el-checkbox--small{
    margin: 3px 8px 3px 0;
}
</style>