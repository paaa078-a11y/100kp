<template>
    <div class="moduleElHight">
        <div class="modulemoreSeach">
            <div class="moduleSeachleft">
                <div class="tableSeachInpt">
                    <el-input v-model="keyword" placeholder="请输入搜索内容" clearable size="small" prefix-icon="el-icon-search">
                    </el-input>
                </div>
                <div class="tableSeachInpt tableSeachInptsmall">
                    <el-select v-model="status" size="small" style="margin-right: 0;" slot="prepend" placeholder="状态" clearable @change="search">
                        <el-option label="已开始" value="1"></el-option>
                        <el-option label="未开始" value="3"></el-option>
                        <el-option label="已结束" value="2"></el-option>
                    </el-select>
                </div>
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
            <div class="moduleSeachButn">
                <div class="tableSeachInpt">
                    <el-button type="primary" icon="el-icon-document-add" size="mini" @click="addzph({ id: '' })">新增网络招聘会
                    </el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable modulElTableThres" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe @selection-change="handleSelectionChange"
                ref="multipleTable" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                v-loading="loading" :empty-text="emptytext">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" width="80" sortable="custom"></el-table-column>
                <el-table-column label="招聘会名称" min-width="250" show-overflow-tooltip>
                    <template slot-scope="scope">
                        <div class="">
                            <el-link type="primary" :href="scope.row.url" target="_blank"> {{ scope.row.title }}</el-link>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="参会企业" min-width="180" show-overflow-tooltip>
                    <template slot-scope="scope">
                        <div style="padding-top: 10px;">
                            <el-button @click="showComList(scope.row)" type="text">
                                <span>{{ scope.row.comnum }}</span><br />
                                <span>查看</span>
                            </el-button>
                            <el-tooltip v-if="scope.row.booking > 0" content="待审核企业" placement="top-end">
                                <el-button @click.native="showComList(scope.row, 3)" type="text">
                                    <el-badge :value="scope.row.booking" :max="99" class="item"></el-badge>
                                </el-button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="参会个人" min-width="180" show-overflow-tooltip>
                    <template slot-scope="scope">
                        <div style="padding-top: 10px;">
                            <el-button type="text" @click="showUserList(scope.row)">
                                <span>{{ scope.row.usernum }}</span><br />
                                <span>查看</span>
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="视频面试" min-width="180" show-overflow-tooltip>
                    <template slot-scope="scope">
                        <div style="padding-top: 10px;">
                            <el-button type="text" @click="showSpLog(scope.row)">
                                <span>{{ scope.row.spnum ? scope.row.spnum : 0 }}次/{{ scope.row.sptime_total ?
                                    scope.row.sptime_total : 0 }}分钟</span><br />
                                <span>查看</span>
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="starttime" label="开始/结束时间" width="180">
                    <template slot-scope="scope">
                        {{ scope.row.starttime }}<br />
                        {{ scope.row.endtime }}
                    </template>
                </el-table-column>
                <el-table-column prop="zd" label="站点" width="75">
                    <template slot-scope="scope">
                        <span>{{ dnamearr[scope.row.did] }}</span>
                        <el-link type="primary" @click="fp(scope.row)">分配</el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="zt" label="状态">
                    <template slot-scope="props">
                        <div class="admin_state">
                            <span class="admin_state1">{{ props.row.is_open == 1 ? '显示' : '隐藏' }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" fixed="right" width="200">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" plain @click="statusSet(scope.row)">状态</el-button>
                            <el-button size="mini" @click="addzph(scope.row)">修改</el-button>
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
                <el-button @click="fpAllBottom" size="mini">批量选择分站</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    :current-page="currentPage" :page-sizes="pageSizes" :page-size="perPage"
                    layout="total, sizes, prev, pager, next, jumper" :total="total" :pager-count="pagerCount">
                </el-pagination>
            </div>
        </div>
        <!--添加招聘会-->
        <el-drawer title="添加招聘会" :visible.sync="draweradd" :modal-append-to-body="false" :show-close="true"
            :with-header="true" size="60%">
            <zphadd ref="zphadd" :cdata="curr_data" :spacearr="spacearr" :dnamearr="dnamearr"
                @child-event-list="handleCloseList"></zphadd>
        </el-drawer>
        <!--视频面试记录-->
        <el-drawer title="视频面试记录" :visible.sync="drawersplog" :modal-append-to-body="false" :show-close="true"
            :with-header="true" size="80%">
            <splog ref="splog" style="padding-left: 10px;"></splog>
        </el-drawer>
        <!--添加参会企业-->
        <el-drawer :title="curr_data.title + ' - 添加参会企业'" :visible.sync="draweraddcom" :modal-append-to-body="false"
            append-to-body size="80%">
            <div class="moduleElHight" style="padding: 0 20px;">
                <div class="moduleSeachleft">
                    <div class="moduleInptList" style="margin-bottom: 8px;">
                        <el-input placeholder="请输入搜索内容" size="small" v-model="addcomsearch_params.keyword" class="input-with-select" clearable>
                            <el-select v-model="addcomsearch_params.type" size="small" slot="prepend" placeholder="企业名称/简称">
                                <el-option label="企业名称/简称" value="1"></el-option>
                                <el-option label="用户名称" value="2"></el-option>
                                <el-option label="联系人" value="3"></el-option>
                                <el-option label="联系电话" value="4"></el-option>
                                <el-option label="用户邮箱" value="5"></el-option>
                                <el-option label="用户ID" value="6"></el-option>
                            </el-select>
                        </el-input>
                    </div>

                    <div class="tableSeachInpt tableSeachInptsmall" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select style="margin-right: 8px;" v-model="addcomsearch_params.time_type" size="small" slot="prepend" placeholder="筛选日期" clearable @change="handleTimeChange">
                            <el-option label="注册时间" value="adtime"></el-option>
                            <el-option label="登录时间" value="lotime"></el-option>
                        </el-select>
                    </div>
                    <div class="tableSeachInpt tableSeachInptsmalltwo" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-date-picker v-model="addcomsearch_params.times" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" :picker-options="timeOptions" value-format="yyyy-MM-dd" size="small" @change="handleTimeChange"></el-date-picker>
                    </div>
                    <div class="tableSeachInpt tableSeachInptsmall" v-for="(searchitem, searchidx) in searchlist" :key="searchidx" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select style="margin-right: 8px;" v-model="addcomsearch_params[searchidx]" size="small" slot="prepend" :placeholder="searchitem.name" clearable @change="addComSearch()">
                            <el-option v-for="(item, index) in searchitem.value" :label="item" :key="index" :value="index"></el-option>
                        </el-select>
                    </div>

                    <div class="tableSeachInpt">
                        <el-button type="primary" icon="el-icon-search" size="mini" @click="addComSearch">查询
                        </el-button>
                    </div>

                    <div class="tableSeachzk" :class="{ 'searchbutnKai': seachbutn }" style="margin-bottom: 8px;">
                        <el-button type="info" class="zhankai" @click="seachbutn = !seachbutn, tableHig = !tableHig"
                                   aria-disabled="false" size="mini" plain>展开<i class="el-icon-arrow-down el-icon--right"></i>
                        </el-button>
                        <el-button type="info" class="shouqi" @click="seachbutn = !seachbutn, tableHig = !tableHig"
                                   aria-disabled="false" size="mini" plain>合并<i class="el-icon-arrow-up el-icon--right"></i>
                        </el-button>
                    </div>

                    <div class="tableSeachInpt">
                        <!--<el-button type="primary" plain icon="el-icon-plus" size="mini">添加参会企业-->
                        <!--</el-button>-->
                        <!--<el-button type="primary" plain icon="el-icon-plus" size="mini">导出参会企业</el-button>-->
                    </div>
                </div>
                <div class="moduleElTable" :class="{ 'moduleElTianjia': seachbutn }" style="height: calc(100% - 145px);">
                    <el-table :data="comlist" style="width: 100%" stripe
                        :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                        @selection-change="handleAddComSelectionChange" ref="addComMultipleTable"
                        :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='addComSortChange'
                        v-loading="loading" :empty-text="emptytext">
                        <el-table-column type="selection" :selectable="checkSelectSet" width="55"></el-table-column>
                        <el-table-column prop="uid" label="用户ID" sortable="custom" width="120"></el-table-column>
                        <el-table-column prop="comname" label="企业Logo">
                            <template slot-scope="scope">
                                <img :src="scope.row.logo" width="48px" height="48px" />
                            </template>
                        </el-table-column>
                        <el-table-column prop="jobname" label="企业名称" min-width="120" show-overflow-tooltip>
                            <template slot-scope="scope">
                                <div class="">
                                    <el-link target="_blank" :href="scope.row.comUrl">{{ scope.row.name }}</el-link>
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
                                    <el-button
                                        v-if="scope.row.jobnum > 0 && scope.row.r_status == '1' && scope.row.join != '1'"
                                        size="small" plain @click="addcom(scope.row.uid)">加入
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
                <div class="modulePaging" style="padding-left:0px;">
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
        <!--参会企业-->
        <el-drawer :title="curr_data.title + ' - 参会企业'" :visible.sync="comdrawer" :modal-append-to-body="false"
            append-to-body size="80%" @close="closeComList">
            <div class="moduleElHight" style="padding: 0 20px;">
                <div class="modulemoreSeach">
                    <div class="moduleSeachleft">
                        <div class="tableSeachInpt tableSeachInptsmall newsinput">
                            <el-select v-model="com_status" size="small" style="margin-right: 0;" slot="prepend" placeholder="审核状态" clearable>
                                <el-option label="已通过" value="1"></el-option>
                                <el-option label="未审核" value="3"></el-option>
                                <el-option label="未通过" value="2"></el-option>
                            </el-select>
                        </div>
                        <div class="tableSeachInpt">
                            <el-input v-model="com_keyword" placeholder="请输入企业名称" clearable size="small"
                                prefix-icon="el-icon-search">
                            </el-input>
                        </div>
                        <div class="tableSeachInpt">
                            <el-button type="primary" icon="el-icon-search" size="mini" @click="comSearch">查询</el-button>
                        </div>
                    </div>
                    <div class="moduleSeachButn">
                        <div class="tableSeachInpt">
                            <el-button type="primary" icon="el-icon-document-add" size="mini" @click="getAddComList">添加参会企业
                            </el-button>
                        </div>
                    </div>
                </div>
                <!--<div class="admin_datatip"><i class="el-icon-document"></i> 参会企业共 120家 参会职位 8789</div>-->
                <div class="moduleElTable"
                    style="border: 1px solid #ebeef5; width: calc(100% - 2px); height: calc(100% - 100px);">
                    <el-table :data="comTableData" style="width: 100%" stripe
                        :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                        @selection-change="handleComSelectionChange" ref="comMultipleTable" @sort-change='comSortChange'
                        :key="comTblKey" v-loading="loading" :empty-text="emptytext">
                        <el-table-column type="selection" width="55"></el-table-column>
                        <el-table-column prop="id" label="编号" sortable="custom"></el-table-column>
                        <el-table-column prop="comname" label="参与企业" min-width="180"
                            show-overflow-tooltip></el-table-column>
                        <el-table-column prop="jobname" label="职位" min-width="180" show-overflow-tooltip></el-table-column>
                        <el-table-column prop="space_n" label="展会区域">
                            <template slot-scope="scope">
                                <span>{{ scope.row.zw != '0' && scope.row.zw != '' ? area[scope.row.zw] : '暂无'
                                }}</span><br />
                                <el-link type="primary" @click="fpzw(scope.row)">分配</el-link>
                            </template>
                        </el-table-column>
                        <el-table-column prop="sort" label="排序" sortable="custom">
                            <template slot-scope="scope">
                                <el-input v-if="scope.row[scope.column.property + 'isShow']"
                                    :ref="scope.column.property + scope.$index" :id="scope.column.property + scope.$index"
                                    v-model="scope.row.sort" @blur="alterData(scope, 1)"></el-input>
                                <span v-else>
                                    {{ scope.row.sort }}
                                    <img src="../../../admin/images/bine.png" alt="" style="margin-left: 4px;" width="14"
                                        height="14" @click="editData(scope, 1)">
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="zt" label="状态">
                            <template slot-scope="props">
                                <div class="admin_state">
                                    <span v-if="props.row.status == '1'" class="admin_state1"> 正常</span>
                                    <span v-else-if="props.row.status == '0'" class="admin_state2"> 未审核</span>
                                    <span v-else-if="props.row.status == '2'" class="admin_state2"> 未通过</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="操作" fixed="right" width="150" align="center">
                            <template slot-scope="scope">
                                <div class="cz_button">
                                    <el-button size="mini" plain @click="cominfo(scope.row)">详情</el-button>
                                    <el-button type="danger" size="mini" @click="comdelrow(scope.row.id)">删除
                                    </el-button>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="modulePaging" style="padding-left:0px;">
                    <div class="bottomButnBull">

                        <div class="bottomButnBlak">
                            <el-checkbox v-model="comCheckedAll" @change="comSelectAllBottom">全选</el-checkbox>
                            <el-button @click="comDelAllBottom" size="mini">批量删除</el-button>
                        </div>
                        <div class="bottomButnNone">
                            <el-popover placement="top-start" width="380" trigger="hover">
                                <div class="bottomButnGend">
                                    <el-button @click="comMultiAudit" size="mini">批量审核</el-button>
                                    <el-button @click="fpzwAllBottom" size="mini">批量分配展位区域</el-button>
                                    <el-button @click="exportComExcel" size="mini">导出企业名单</el-button>
                                </div>
                                <div class="bottomButnMore" slot="reference">更多</div>
                            </el-popover>
                        </div>

                        <!-- <el-checkbox v-model="comCheckedAll" @change="comSelectAllBottom">全选</el-checkbox>
                        <el-button @click="comDelAllBottom" size="mini">批量删除</el-button>
                        <el-button @click="drawercomstatusmultiple = true" size="mini">批量审核</el-button>
                        <el-button @click="fpzwAllBottom" size="mini">批量分配展位区域</el-button>
                        <el-button @click="exportComExcel" size="mini">导出企业名单</el-button> -->
                    </div>
                    <div class="modulePagNum">
                        <el-pagination background @size-change="handleComSizeChange"
                            @current-change="handleComCurrentChange" :current-page="comCurrentPage"
                            :page-sizes="comPageSizes" :page-size="comPerPage"
                            layout="total, sizes, prev, pager, next, jumper" :total="comTotal" :pager-count="pagerCount">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </el-drawer>
        <!--参会企业详情-->
        <el-drawer title="参会企业详情" :visible.sync="comdrawersh" append-to-body :modal-append-to-body="false" size="80%">
            <div class="shbox">
                <div class="shinfo">
                    <div class="shcomname">{{ info.name }}
                        <el-tag type="danger" size="mini">{{ info.rating_name }}</el-tag>
                    </div>
                    <div class="sh_zwsz_add" v-if="zw_n != ''">参会分类：{{ zw_n }}</div>
                    <div class="sh_zwsz">
                        <el-button type="primary" size="mini" plain @click="fpzw(curr_comdata)"><i class="el-icon-edit"></i>
                            展会区域</el-button>
                        <el-button type="primary" size="mini" @click="showComJob()"><i class="el-icon-suitcase-1"></i>
                            参会职位</el-button>
                    </div>
                    <div class="shcomtel">
                        <span v-if="info.linkman">
                            联系人：{{ info.linkman }}<span v-if="info.linkjob">（{{ info.linkjob }}）</span>
                        </span>
                        <span class="shcomtel_n" v-if="info.linktel">
                            联系电话：{{ info.linktel }}
                        </span>
                        <span v-if="info.crm_uid != '0'">
                            业务员：{{ info.crm_name }}
                        </span>
                    </div>
                    <div class="shshowall">
                        <div class="shshow">
                            <div class="shshow_tit"><i class="el-icon-office-building"></i> 基本资料</div>
                            <div class="shshow_p">
                                <div class="" v-if="info.welfare">企业福利：
                                    <el-tag style="margin-right: 5px;" v-for="(item, index) in info.welfare_n" :key="index"
                                        size="mini">
                                        {{ item }}
                                    </el-tag>
                                </div>
                                <div class="" v-if="info.hy">从事行业：{{ info.hy_n }}</div>
                                <div class="" v-if="info.pr">企业性质：{{ info.pr_n }}</div>
                                <div class="" v-if="info.mun">企业规模：{{ info.mun_n }}</div>
                                <div class="" v-if="info.provinceid">企业地址：{{ info.job_city_one }}
                                    {{ info.job_city_two }}
                                    {{ info.job_city_three }} {{ info.address }}
                                </div>
                                <div class="" v-if="info.content" v-html="info.content"></div>
                            </div>
                            <div class="shshow_tit"><i class="el-icon-suitcase-1"></i> 招聘岗位</div>
                            <ul class="shshow_joblist">
                                <li v-for="(item, index) in info.job_list" :key="index">
                                    <el-link type="primary" :underline="false">
                                        <div class="shshow_job">{{ item.name }}</div>
                                    </el-link>
                                    <div class="shshow_jobinfo">
                                        <span class="shshow_jobxz">
                                            <span v-if="item.minsalary != '0'">{{ item.minsalary }}</span>
                                            <span v-if="item.minsalary != '0' && item.maxsalary != '0'">-</span>
                                            <span v-if="item.maxsalary != '0'">{{ item.maxsalary }}</span>
                                        </span>
                                        <span class="shshow_line" v-if="!item.exp_req">| 不限经验</span>
                                        <span class="shshow_line" v-if="!item.edu_req">| 不限学历</span>
                                    </div>
                                    <span class="shshow_zt" v-if="item.ch_n == '已参会'">{{ item.ch_n }}</span>
                                    <span class="shshow_zt shshow_ztno" v-else>{{ item.ch_n }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="shcz">
                            <div class="wxsettip_small ">参会企业审核</div>
                            <template>
                                <el-radio v-model="curr_comstatus" label="1">通过</el-radio>
                                <el-radio v-model="curr_comstatus" label="2">未通过</el-radio>
                            </template>
                            <div class="wxsettip_small ">审核状态说明</div>
                            <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="curr_comstatusbody">
                            </el-input>
                            <div class=" shczbth">
                                <el-button type="primary" @click="comStatusSave(info.zphnet.id)" :disabled="submitLoading">提
                                    交</el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </el-drawer>
        <!--参会个人-->
        <el-drawer :title="curr_data.title + ' - 参会个人'" :visible.sync="draweruser" :modal-append-to-body="false"
            append-to-body size="80%" @close="closeUserList">
            <div class="moduleElHight" style="padding-left: 10px;">
                <div class="moduleSeachbig">
                    <div class="tableSeachInpt">
                        <el-input v-model="user_keyword" placeholder="请输入求职意向" clearable size="small"
                            prefix-icon="el-icon-search">
                        </el-input>
                    </div>
                    <div class="tableSeachInpt">
                        <el-button type="primary" icon="el-icon-search" size="mini" @click="userSearch">查询</el-button>
                    </div>
                    <div class="tableSeachInpt">
                        <!--<el-button type="primary" plain icon="el-icon-plus" size="mini" @click="getAddComList">添加参会企业-->
                        <!--</el-button>-->
                        <!--<el-button type="primary" plain icon="el-icon-plus" size="mini">导出参会企业</el-button>-->
                    </div>
                </div>
                <!--<div class="admin_datatip"><i class="el-icon-document"></i> 参会企业共 120家 参会职位 8789</div>-->
                <div class="moduleElTable"
                    style="border: 1px solid #ebeef5; width: calc(100% - 2px); height: calc(100% - 100px);">
                    <el-table :data="userlist" style="width: 100%" stripe
                        :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%"
                        @selection-change="handleUserSelectionChange" ref="userMultipleTable" :key="userTblKey"
                        v-loading="loading" :empty-text="emptytext">
                        <el-table-column type="selection" width="55"></el-table-column>
                        <el-table-column prop="uname" label="姓名" width="100"></el-table-column>
                        <el-table-column prop="sex_n" label="性别" width="60"></el-table-column>
                        <el-table-column prop="age_n" label="年龄" min-width="60"></el-table-column>
                        <el-table-column prop="exp_n" label="工作经验"></el-table-column>
                        <el-table-column prop="salary" label="期望薪资"></el-table-column>
                        <el-table-column prop="job_classname" label="意向职位" min-width="180" show-overflow-tooltip>
                        </el-table-column>
                        <el-table-column prop="city_classname" label="地区"></el-table-column>
                        <el-table-column label="操作" fixed="right" width="150" align="center">
                            <template slot-scope="scope">
                                <div class="cz_button">
                                    <el-button type="danger" size="mini" @click="userdelrow(scope.row.uid)">删除
                                    </el-button>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="modulePaging">
                    <div>
                        <el-checkbox v-model="userCheckedAll" @change="userSelectAllBottom">全选</el-checkbox>
                        <el-button @click="userDelAllBottom" size="mini">批量删除</el-button>
                    </div>
                    <div class="modulePagNum">
                        <el-pagination background @size-change="handleUserSizeChange"
                            @current-change="handleUserCurrentChange" :current-page="userCurrentPage"
                            :page-sizes="userPageSizes" :page-size="userPerPage"
                            layout="total, sizes, prev, pager, next, jumper" :total="userTotal">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </el-drawer>
        <!--分配展会区域弹窗-->
        <div class="modluDrawer">
            <el-dialog title="分配展会区域" width="500px" :visible.sync="drawerfpzw" append-to-body :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite" style="width: 90px;">
                            <span>企业名称：</span>
                        </div>
                        <div class="toolClasCont">
                            <span>{{ curr_comdata.comname }}</span>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>展会区域：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-select v-model="curr_comdata.zw" placeholder="请选择" filterable>
                                <el-option v-for="(item, index) in area" :key="index" :label="item" :value="index">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawerfpzw = false">取 消</el-button>
                    <el-button type="primary" @click="fpzwSave(1)">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--批量分配展会区域-->
        <div class="modluDrawer">
            <el-dialog title="批量分配展会区域" width="500px" :visible.sync="drawerfpzwmultiple" append-to-body
                :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>展会区域：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-select v-model="multiplezwid" placeholder="请选择" filterable>
                                <el-option v-for="(item, index) in area" :key="index" :label="item" :value="index">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawerfpzwmultiple = false">取 消</el-button>
                    <el-button type="primary" @click="fpzwSave(2)">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--分配站点弹窗-->
        <div class="modluDrawer">
            <el-dialog title="分配站点" width="500px" :visible.sync="drawerfp" :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite" style="width: 90px;">
                            <span>招聘会标题：</span>
                        </div>
                        <div class="toolClasCont">
                            <span>{{ curr_data.title }}</span>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>切换站点：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-select v-model="curr_data.did" placeholder="请选择" filterable>
                                <el-option v-for="item in dnameOptions" :key="item.key" :label="item.val" :value="item.key">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawerfp = false">取 消</el-button>
                    <el-button type="primary" @click="fpSave(1)">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--批量分配站点-->
        <div class="modluDrawer">
            <el-dialog title="批量分配站点" width="300px" :visible.sync="drawerfpmultiple" :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>切换站点：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-select v-model="multipledid" placeholder="请选择" filterable>
                                <el-option v-for="item in dnameOptions" :key="item.key" :label="item.val" :value="item.key">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawerfpmultiple = false">取 消</el-button>
                    <el-button type="primary" @click="fpSave(2)">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--招聘会状态-->
        <div class="modluDrawer">
            <el-dialog title="招聘会状态" :visible.sync="drawerstatus" :with-header="true" :modal-append-to-body="false"
                :show-close="true" width="450px">
                <div class="wxsettip_small ">招聘会名称</div>
                <el-input v-model="curr_data.title" :disabled="true">
                </el-input>
                <div class="wxsettip_small ">招聘会显示状</div>
                <template>
                    <el-radio v-model="curr_data.is_open" label="1">显示</el-radio>
                    <el-radio v-model="curr_data.is_open" label="0">关闭</el-radio>
                </template>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawerstatus = false">取 消</el-button>
                    <el-button type="primary" @click="statusSave">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!--批量审核参会企业-->
        <div class="modluDrawer">
            <el-dialog title="参会企业审核" width="300px" :visible.sync="drawercomstatusmultiple" append-to-body
                :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核操作：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-radio v-model="multiComStatus" label="1">正常</el-radio>
                            <el-radio v-model="multiComStatus" label="2">未通过</el-radio>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>审核说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" v-model="multiComStatusBody" :rows="2" placeholder="请输入内容">
                            </el-input>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawercomstatusmultiple = false">取 消</el-button>
                    <el-button type="primary" @click="multipleComStatusSave">确 定</el-button>
                </span>
            </el-dialog>
        </div>



        <!--参会职位-->
        <div class="modluDrawer">
            <el-dialog title="参会职位" width="300px" :visible.sync="drawercomjob" append-to-body :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand">
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>选择职位：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-select v-model="jobids" filterable remote placeholder="选择职位" multiple>
                                <el-option v-for="item in job_arr" :key="item.id" :label="item.name" :value="item.id">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="drawercomjob = false">取 消</el-button>
                    <el-button type="primary" @click="saveComJob">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>
<script>
module.exports = {
    data: function () {
        return {
            loading: false,
            pagerCount: 5,
            emptytext: '暂无数据',
            submitLoading: false,
            status: '',
            com_status: '',
            com_keyword: '',
            addcom_keyword: '',
            keyword: '',
            user_keyword: '',
            checkedAll: false,
            selectedItem: [],
            tableData: [],
            currentPage: 1,
            perPage: 0,
            pageSizes: [],
            total: 0,
            comCheckedAll: false,
            comSelectedItem: [],
            comTableData: [],
            dnameOptions:{},
            comCurrentPage: 1,
            comPerPage: 0,
            comPageSizes: [],
            comTotal: 0,
            comTblKey: '',
            curr_data: {},
            curr_comdata: {},
            dnamearr: {},
            drawerfp: false,
            drawerfpmultiple: false,
            multipledid: '',
            drawerstatus: false,
            spacearr: [],
            draweradd: false,
            area: {},
            drawerfpzw: false,
            drawerfpzwmultiple: false,
            multiplezwid: '',
            pytoken: localStorage.getItem("pytoken"),
            baseUrl: localStorage.getItem("baseUrl"),
            info: {},
            dtlislook: false,
            comdrawer: false,
            comdrawersh: false,
            curr_comstatus: '',
            curr_comstatusbody: '',
            tableHig: true,
            zw_n: '',
            drawercomstatusmultiple: false,
            multiComStatus: '',
            multiComStatusBody: '',
            draweraddcom: false,
            comlist: [],
            addComCheckedAll: false,
            addComSelectedItem: [],
            addComCurrentPage: 1,
            addComPerPage: 0,
            addComPageSizes: [],
            addComTotal: 0,
            todayetime: 0,
            com_type: '1',
            userlist: [],
            userCheckedAll: false,
            userSelectedItem: [],
            userCurrentPage: 1,
            userPerPage: 0,
            userPageSizes: [],
            userTotal: 0,
            draweruser: false,
            drawersplog: false,
            userTblKey: '',
            islook: false,
            // 参会职业
            drawercomjob: false,
            jobids: [],
            job_arr: [],
            prevPage:0,
            prevPage1:0,
            prevPage2:0,
            prevPage3:0,

            seachbutn: true,
            tableHig: true,
            searchlist: null,
            addcomsearch_params: {
                type: '1',
                keyword: '',
                rating: '',
                time: '',
                status: this.status,
                source: '',
                rec: '',
                gw: '',
                time_type: 'lotime',
                times: [],
                reg_days: 0,
                reg_time: [],
                login_days: 0,
                login_time: [],
            },
            isSearchTime: false,
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
        }
    },
    mounted() {
        this.getGroup();
    },
    components: {
        'zphadd': httpVueLoader('./netzphadd.vue'),
        'splog': httpVueLoader('../../spview/component/splog.vue')
    },
    methods: {
        closeComList(){
            this.comdrawer = false;
            this.search();
        },
        closeUserList(){
            this.draweruser = false;
            this.search();
        },
        showSpLog(data) {
            var that = this
            that.curr_data = data
            that.drawersplog = true
            that.$nextTick(function () {
                that.$refs.splog.zid = that.curr_data.id;
                that.$refs.splog.getList();
            })
        },
        showUserList(data) {
            this.curr_data = data
            this.draweruser = true
            this.user_keyword = ''
            this.userSearch()
        },
        // 获取参会个人列表
        getUserList() {
            var that = this
            var params = { id: that.curr_data.id, page: that.userCurrentPage, pageSize: that.userPerPage }
            if (that.user_keyword) {
                params.keyword = that.user_keyword
            }
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=neirong&c=zphnet&a=user', params, {hideloading: true}).then(function (response) {
                if (response.data.error == 0) {
                    that.userlist = response.data.data.list
                    that.userPerPage = parseInt(response.data.data.perPage)
                    that.userPageSizes = response.data.data.pageSizes
                    that.userTotal = parseInt(response.data.data.total)
                    that.userTblKey = Math.random()
                    that.draweruser = true;
                    if(that.prevPage3 != that.userCurrentPage){
                        that.prevPage3 = that.userCurrentPage;
                        that.$refs.userMultipleTable.bodyWrapper.scrollTop = 0;
                    }
                    that.loading = false;
                    if (that.userlist.length === 0) {
                        that.emptytext = "暂无数据";
                    }
                } else {
                    message.error('获取企业信息失败');
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        userSearch() {
            this.userCurrentPage = 1;
            this.getUserList();
        },
        handleUserSelectionChange(val) {
            this.userSelectedItem = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.userSelectedItem.push(item.uid);
                });
            }
            if (_this.userSelectedItem.length == 0) {
                _this.userCheckedAll = false;
            } else {
                if (_this.userSelectedItem.length == _this.userlist.length) {
                    _this.userCheckedAll = true;
                } else {
                    _this.userCheckedAll = false;
                }
            }
        },
        userSelectAllBottom(value) {
            value ? this.$refs.userMultipleTable.toggleAllSelection() : this.$refs.userMultipleTable.clearSelection();
        },
        handleUserSizeChange(val) {
            this.userPerPage = val;
            this.getUserList()
        },
        handleUserCurrentChange(val) {
            this.userCurrentPage = val;
            this.getUserList()
        },
        userdelrow(id) {
            delConfirm(this, id, this.userDelete);
        },
        userDelAllBottom() {
            if (!this.userSelectedItem.length) {
                message.error('请选择要删除的数据项');
                return false;
            }
            delConfirm(this, this.userSelectedItem, this.userDelete);
        },
        async userDelete(id) {
            let that = this;
            let params = {
                del: id
            };
            httpPost('m=neirong&c=zphnet&a=deluser', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg);
                    that.getList();
                    that.getUserList();
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
        // 添加参会企业---获取企业列表
        getAddComList() {
            var that = this
            var params = { id: that.curr_data.id, page: that.addComCurrentPage, pageSize: that.addComPerPage }
            if (that.addcomsearch_params.type) {
                params.type = that.addcomsearch_params.type
            }
            if (that.addcomsearch_params.keyword) {
                params.keyword = that.addcomsearch_params.keyword
            }
            if (that.addcomsearch_params.rating) {
                params.rating = that.addcomsearch_params.rating
            }
            if (that.addcomsearch_params.rec) {
                params.rec = that.addcomsearch_params.rec
            }
            if (that.addcomsearch_params.time) {
                params.time = that.addcomsearch_params.time
            }
            if (that.addcomsearch_params.status) {
                params.status = that.addcomsearch_params.status
            }
            if (that.addcomsearch_params.source) {
                params.source = that.addcomsearch_params.source
            }
            if (that.addcomsearch_params.gw) {
                params.gw = that.addcomsearch_params.gw
            }
            if (that.addcomsearch_params.time_type != '') {
                params.time_type = that.addcomsearch_params.time_type;
            }
            if (Array.isArray(that.addcomsearch_params.times) && that.addcomsearch_params.times.length == 2) {
                params.times = that.addcomsearch_params.times;
            }


            if (that.sort_type && that.sort_col) {
                params.order = that.sort_type
                params.t = that.sort_col
            }
            that.draweraddcom = true;
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=neirong&c=zphnet&a=comadd', params, {hideloading: true}).then(function (response) {
                if (response.data.error == 0) {

                    that.comlist = response.data.data.list
                    that.addComPerPage = parseInt(response.data.data.perPage)
                    that.addComPageSizes = response.data.data.pageSizes
                    that.addComTotal = parseInt(response.data.data.total)
                    that.todayetime = response.data.data.today_etime

                    if(that.prevPage2 != that.addComCurrentPage){
                        that.prevPage2 = that.addComCurrentPage;
                        that.$refs.addComMultipleTable.bodyWrapper.scrollTop = 0;
                    }
                    that.loading = false;
                    if (that.comlist.length === 0) {
                        that.emptytext = "暂无数据";
                    }
                    that.$refs.addComMultipleTable.doLayout();
                } else {
                    message.error('获取企业信息失败');
                }
            }).catch(function (error) {
                console.log(error);
            })
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

            that.$confirm('确认添加至招聘会？', '温馨提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                var params = {
                    zphid: that.curr_data.id,
                    comid: uid,
                }
                that.doaddcom(params, 'comaddsave')
            })
        },
        // 批量添加参会企业
        addcommultiple() {
            var that = this
            if (that.addComSelectedItem.length == 0) {
                message.error('您还未选择参会企业！');
                return false
            }
            message.confirm('确认添加至招聘会？', function () {
                var params = {
                    zphid: that.curr_data.id,
                    comid: that.addComSelectedItem.join(','),
                }
                that.doaddcom(params, 'muticomAddSave')
            })
        },
        async doaddcom(params, action) {
            var that = this
            var url = 'm=neirong&c=zphnet&a=' + action
            httpPost(url, params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg, function () {
                        that.comSearch()
                        that.getAddComList()
                    });
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        // 参会企业批量审核
        comMultiAudit() {
            if (!this.comSelectedItem.length) {
                message.error('请选择要操作的数据项');
                return false;
            }
            this.drawercomstatusmultiple = true
        },
        // 参会企业批量审核提交
        multipleComStatusSave() {
            var that = this
            if (!that.comSelectedItem.length) {
                message.error('请选择要操作的数据项');
                return false;
            }
            that.comstatus({
                pid: that.comSelectedItem.join(','),
                status: that.multiComStatus,
                status_body: that.multiComStatusBody
            }, 2);
        },
        comStatusSave(id) {
            this.comstatus({ pid: id, status: this.curr_comstatus, statusbody: this.curr_comstatusbody }, 1);
        },
        comstatus(params, tp) {
            var that = this
            this.submitLoading = true;
            httpPost('m=neirong&c=zphnet&a=status', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg, function () {
                        if (tp == 1) {
                            that.dtlislook = false
                            that.comdrawersh = false
                        } else {
                            that.drawercomstatusmultiple = false
                        }
                        that.getComList()
                    });
                } else {
                    message.error('获取参会企业失败');
                }
            }).catch(function (error) {
                console.log(error);
            }).finally(function () {
                that.submitLoading = false;
            });
            if (tp == 1) {
                that.dtlislook = false
                that.comdrawersh = false
            } else {
                that.drawercomstatusmultiple = false
            }
        },
        addzph(data) {
            if (data.id) {
                this.curr_data = JSON.parse(JSON.stringify(data));
                this.curr_data.did = '' + this.curr_data.did;
            } else {
                this.curr_data = { id: '' }
            }
            this.draweradd = true
            this.$nextTick(function () {
                this.$refs.zphadd.reserved_arr = [];
                this.$refs.zphadd.initEditor();
                if (data.sid) {
                    this.$refs.zphadd.cdChange(data.sid)
                }
            })
        },
        statusSet(data) {
            this.curr_data = JSON.parse(JSON.stringify(data));
            this.drawerstatus = true
        },
        statusSave() {
            var that = this
            var params = {
                pid: that.curr_data.id,
                is_open: that.curr_data.is_open
            }
            httpPost('m=neirong&c=zphnet&a=upisopen', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg, function () {
                        that.drawerstatus = false;
                        that.getList();
                    });
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        fp(data) {
            this.curr_data = JSON.parse(JSON.stringify(data))
            this.drawerfp = true
        },
        fpAllBottom() {
            if (!this.selectedItem.length) {
                message.error('请选择要操作的数据项');
                return false;
            }
            this.multipledid = ''
            this.drawerfpmultiple = true
        },
        fpSave(tp) {
            var that = this
            let params = {}
            if (tp == 1) {// 分配站点
                params.uid = that.curr_data.id
                params.did = that.curr_data.did
            } else {// 批量分配站点
                if (!that.selectedItem.length) {
                    message.error('请选择要操作的数据项');
                    return false;
                }
                params.uid = that.selectedItem.join(',')
                params.did = that.multipledid
            }
            httpPost('m=neirong&c=zphnet&a=checksitedid', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg, function () {
                        if (tp == 1) {
                            that.drawerfp = false
                        } else {
                            that.drawerfpmultiple = false
                        }
                        that.getList();
                    });
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        fpzw(data) {
            if (data.zw == '0') {
                data.zw = ''
            }
            this.curr_comdata = data
            this.drawerfpzw = true
        },
        fpzwAllBottom() {
            if (!this.comSelectedItem.length) {
                message.error('请选择要操作的数据项');
                return false;
            }
            this.multiplezwid = ''
            this.drawerfpzwmultiple = true
        },
        fpzwSave(tp) {
            var that = this
            let params = {}
            if (tp == 1) {// 分配站点
                params.zphcomid = that.curr_comdata.id
                params.zw = that.curr_comdata.zw
            } else {// 批量分配站点
                if (!that.comSelectedItem.length) {
                    message.error('请选择要操作的数据项');
                    return false;
                }
                params.zphcomid = that.comSelectedItem.join(',')
                params.zw = that.multiplezwid
            }
            httpPost('m=neirong&c=zphnet&a=setComZw', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg, function () {
                        if (tp == 1) {
                            that.drawerfpzw = false
                        } else {
                            that.drawerfpzwmultiple = false
                        }
                        that.getComList();
                    });
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        handleComSelectionChange(val) {
            this.comSelectedItem = [];
            let _this = this;
            if (val.length) {
                val.forEach(item => {
                    _this.comSelectedItem.push(item.id);
                });
            }
            if (_this.comSelectedItem.length == 0) {
                _this.comCheckedAll = false;
            } else {
                if (_this.comSelectedItem.length == _this.comTableData.length) {
                    _this.comCheckedAll = true;
                } else {
                    _this.comCheckedAll = false;
                }
            }
        },
        comSelectAllBottom(value) {
            value ? this.$refs.comMultipleTable.toggleAllSelection() : this.$refs.comMultipleTable.clearSelection();
        },
        handleComSizeChange(val) {
            this.comPerPage = val;
            this.getComList()
        },
        handleComCurrentChange(val) {
            this.comCurrentPage = val;
            this.getComList()
        },
        comSortChange: function (column) {
            if (column.order == 'descending') {
                this.sort_type = 'desc';
            } else if (column.order == 'ascending') {
                this.sort_type = 'asc';
            } else {
                this.sort_type = '';
            }
            // debugger
            this.sort_col = column.prop
            this.comSearch();
        },
        comSearch() {
            this.comCurrentPage = 1;
            this.getComList();
            this.comGroup();
        },
        async getComList() {
            let that = this;
            let params = {
                type: 2,
                page: that.comCurrentPage,
                pageSize: that.comPerPage
            }
            if (that.com_keyword) {
                params.keyword = that.com_keyword
            }
            if (that.com_status) {
                params.status = that.com_status
            }
            if (that.curr_data.id) {
                params.id = that.curr_data.id
            }
            if (that.sort_type && that.sort_col) {
                params.order = that.sort_type
                params.t = that.sort_col
            }
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=neirong&c=zphnet&a=com', params).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.comTableData = res.data.list
                    that.comPerPage = parseInt(res.data.perPage)
                    that.comPageSizes = res.data.pageSizes
                    that.comTotal = parseInt(res.data.total)
                    if(that.prevPage1 != that.comCurrentPage){
                        that.prevPage1 = that.comCurrentPage;
                        that.$refs.comMultipleTable.bodyWrapper.scrollTop = 0;

                    }
                    that.loading = false;
                    if (that.comTableData.length === 0) {
                        that.emptytext = "暂无数据";
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        async comGroup(){
            let that = this;
            let params = {

            }
            if (that.curr_data.id) {
                params.id = that.curr_data.id
            }
            httpPost('m=neirong&c=zphnet&a=comGroup', params).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.area = res.data.area
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        comdelrow(id) {
            delConfirm(this, id, this.comDelete);
        },
        comDelAllBottom() {
            if (!this.comSelectedItem.length) {
                message.error('请选择要删除的数据项');
                return false;
            }
            delConfirm(this, this.comSelectedItem, this.comDelete);
        },
        async comDelete(id) {
            let that = this;
            let params = {
                del: id
            };
            httpPost('m=neirong&c=zphnet&a=delcom', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg);
                    that.getList();
                    that.getComList();
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        editData(scope) {
            let index = scope.$index;
            let row = scope.row;
            let column = scope.column;
            this.oldData = JSON.parse(JSON.stringify(row));
            let copyRow = JSON.parse(JSON.stringify(row));
            copyRow[column.property + "isShow"] = true;
            this.$set(this.comTableData, index, copyRow);
            this.$nextTick(() => {
                let ref = column.property + index;
                $("#" + ref).focus();
            });
        },
        alterData(scope) {
            if (this.oldData == null) {
                return false;
            }
            let index = scope.$index;
            let row = scope.row;
            let column = scope.column;
            let copyRow = JSON.parse(JSON.stringify(row));
            copyRow[column.property + "isShow"] = false;
            this.$set(this.comTableData, index, copyRow);
            if (row[column.property] === this.oldData[column.property]) {
                return false;
            }
            let _this = this;
            let sendData = { id: row.id };
            sendData[column.property] = row[column.property];
            httpPost('m=neirong&c=zphnet&a=ajaxsort', sendData, { hideloading: true }).then(function (response) {
                let res = response.data;
                if (res.error === 0) {
                    message.success('修改成功');
                } else {
                    message.error('修改失败');
                }
                _this.oldData = null;
                _this.getComList();
            }).catch(function (error) {
                console.log(error);
            });
        },
        showComList(data, status) {
            this.curr_data = data
            this.comdrawer = true
            this.comTblKey = Math.random()
            this.com_keyword = ''
            if (status) {
                this.com_status = '' + status
            } else {
                this.com_status = ''
            }
            this.comSearch()
        },
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
            if (that.status) {
                params.status = that.status
            }
            if (that.type) {
                params.type = that.type
            }
            if (that.sort_type && that.sort_col) {
                params.order = that.sort_type
                params.t = that.sort_col
            }
            that.loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=neirong&c=zphnet&a=index', params, {hideloading: true}).then(function (result) {
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
        async getGroup(){
            let  that = this;
            httpPost('m=neirong&c=zphnet&a=getGroup', {}, {hideloading: true}).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.dnamearr = res.data.Dname
                    // console.log(that.dnamearr)
                    // 数据处理
                    // that.dnameLookup = res.data.Dname; // 用于查值：dnameLookup[did]
                    that.dnameOptions = Object.entries(res.data.Dname)
                      .sort(([k1], [k2]) => (Number(k1) === -1 ? -1 : Number(k2) === -1 ? 1 : 0))
                      .map(([key, val]) => ({ key, val })); // 用于下拉
//   console.log(that.dnamearr)
                    that.spacearr = res.data.space

                    that.searchlist = res.data.search_list;
                    that.ratingarr = res.data.ratingarr;
                    that.source = res.data.source;
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
                message.error('请选择要删除的数据项');
                return false;
            }
            delConfirm(this, this.selectedItem, this.delete);
        },
        async delete(id) {
            let that = this;
            let params = {
                del: id
            };
            httpPost('m=neirong&c=zphnet&a=del', params).then(function (response) {
                if (response.data.error == 0) {
                    message.success(response.data.msg);
                    that.getList();
                } else {
                    message.error(response.data.msg);
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        // 导出参会企业excel
        exportComExcel() {
            var that = this
            if (that.comTableData.length == 0) {
                message.error('没有可以导出的参会企业信息！');
                return false
            }
            message.confirm('确定导出记录吗？', function () {
                var params = {
                    zid: that.curr_data.id,
                    cid: that.comSelectedItem.join(',')
                }
                httpPost('m=neirong&c=zphnet&a=comxlscheck', params).then(function (response) {
                    let res = response.data;
                    if (res.error > 0) {
                        message.error(res.msg);
                    } else {
                        httpPost('m=neirong&c=zphnet&a=comxls', params).then(function (response2) {
                            let res2 = response2.data;
                            if (res2.error > 0) {
                                message.error(res2.msg);
                            } else {
                                that.exportdrawer = false;
                                utilFile.downloadFileByByte(res2.data.file, res2.data.file_name);
                            }
                        })
                    }
                })
            })
        },
        cominfo(data) {
            var that = this
            that.curr_comdata = data
            httpPost('m=neirong&c=zphnet&a=audit', { id: this.curr_comdata.id }).then(function (response) {
                if (response.data.error == 0) {
                    that.info = response.data.data
                    that.zw_n = that.info.zphnet.zw_n
                    that.curr_comstatus = that.info.zphnet.status
                    that.curr_comstatusbody = that.info.zphnet.statusbody
                    // debugger
                    that.dtlislook = true
                    that.comdrawersh = true
                } else {
                    message.error('获取参会企业失败');
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        handleCloseList() {
            this.editdrawer = false;
            this.getList();
        },
        // 设置参会职位
        showComJob() {
            var that = this
            that.job_arr = []
            httpPost('m=neirong&c=zphnet&a=getjoblist', { comid: that.curr_comdata.uid, type: 'admin', zid: that.curr_comdata.zid }).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    if (res.data.rows.list.length > 0) {
                        that.jobids = res.data.jobIds;
                        that.job_arr = res.data.rows.list;
                        that.drawercomjob = true
                    } else {
                        message.error('该企业没有可作展出的职位！');
                    }
                } else {
                    message.error('该企业没有可作展出的职位！');
                }
            }).catch(function (error) {
                console.log(error);
            })
        },
        saveComJob: function () {
            var that = this;
            httpPost('m=neirong&c=zphnet&a=upjob', { zcomid: that.curr_comdata.id, zphjob: that.jobids }).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    message.success(res.msg);
                    that.cominfo(that.curr_comdata);
                    that.getComList();
                } else {
                    message.error(res.msg);
                }
                that.drawercomjob = false;

            }).catch(function (error) {
                console.log(error);
            })
        },
        handleTimeChange() {
            if (this.addcomsearch_params.time_type != '' && Array.isArray(this.addcomsearch_params.times) && this.addcomsearch_params.times.length) {
                this.isSearchTime = true;
                this.addComSearch();
            }
            if (this.isSearchTime && this.addcomsearch_params.time_type == '' && this.addcomsearch_params.times == null){
                this.isSearchTime = false;
                this.addComSearch();
            }
        }
    },
};
</script>
<style>
.shshowall{
    height: calc(100% - 90px);
    position: relative;
    overflow: hidden;
}
.shshow{
    overflow-y: auto;
    position: relative;
    height: 100%;
    min-height: 400px;
}
</style>