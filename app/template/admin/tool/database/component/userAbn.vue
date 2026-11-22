<template>
    <div class="moduleElHight">
        <div class="moduleSeachbig">
            <div class="tableSeachInpt tableSeachInptsmalltwo">
                <el-date-picker v-model="search.time" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" @change="handelSearch"></el-date-picker>
            </div>
            <div class="tableSeachInpt">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="handelSearch">查询</el-button>
            </div>
        </div>
        <div class="admin_datatip" v-if="datetime"><i class="el-icon-document"></i> 数据概况截止：{{ datetime }}</div>

        <div class="xwyj_box">
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        简历投递
                        <el-tooltip class="item" effect="dark" content="求职者向企业投递简历情况" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('tdnum')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="applyListLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="applyListData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'tdnum')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="name" label="姓名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        简历投递分类
                        <el-tooltip class="item" effect="dark" content="求职者向不同分类职位投递简历统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('tdclass')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="applyClassLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="applyClassData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'tdclass')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="name" label="姓名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        删除简历投递记录
                        <el-tooltip class="item" effect="dark" content="求职者删除简历投递记录统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('usersqdel')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="applyDelLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="applyDelData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'usersqdel')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="name" label="姓名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        职位浏览
                        <el-tooltip class="item" effect="dark" content="求职者浏览职位统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('lookjob')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="lookJobLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="lookJobData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'lookjob')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="name" label="姓名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        职位拨号
                        <el-tooltip class="item" effect="dark" content="求职者在职位详情页点击拨号次数统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('tellog')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="telLogLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="telLogData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'tellog')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="name" label="姓名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        直聊人数
                        <el-tooltip class="item" effect="dark" content="求职者向不同企业发送聊天统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('chatlog')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="chatLogLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="chatLogData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'chatlog')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="name" label="姓名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        登陆次数
                        <el-tooltip class="item" effect="dark" content="求职者登录次数统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('loginlog')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="loginLogLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="loginLogData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'loginlog')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="name" label="姓名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        ip活跃次数
                        <el-tooltip class="item" effect="dark" content="根据行为轨迹统计同一IP下不同求职者访问量" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('weblog')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="webLogLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="webLogData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'weblog')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="ip" label="IP" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="city" label="IP归属地" class-name="centerCss"></el-table-column>
                        <el-table-column prop="usernum" label="用户量" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        职位隐私号
                        <el-tooltip class="item" effect="dark" content="求职者在职位详情页点拨打电话生成的隐私号次数统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('privacylog')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="privacyLogLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="privacyLogData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'privacylog')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="name" label="姓名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
        </div>

        <div class="modluDrawer">
            <el-drawer :title="listTitle" :visible.sync="listDrawer" :modal-append-to-body="false" append-to-body :show-close="true"
                       :with-header="true" size="75%">
                <div class="drawerTableAll">
                    <div class="draweYujiHead">
                        <div class="drawEuiHedButn">
                            <div class="drawEuiHedTetx">
                                <span>统计时间：</span>
                            </div>
                        </div>
                        <div class="drawEuiHedInpt">
                            <el-date-picker v-model="listSearch.time" type="daterange" align="right" unlink-panels
                                            range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"
                                            :picker-options="listTimeOptions" value-format="timestamp"></el-date-picker>
                            <el-input placeholder="请输入IP" v-model="listkeyword" style="width:200px;margin-left:8px;" clearable v-if="listType == 'weblog'"></el-input>
                            <el-button type="primary" icon="el-icon-search" size="small" style="margin-left: 8px;"
                                       @click="handleSearchList">
                                查询
                            </el-button>
                        </div>
                    </div>
                    <div class="draweYujiBiaog">
                        <el-table :data="listTableData" style="width: 100%" stripe
                                  :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                            <el-table-column prop="uid" label="用户ID" width="180"
                                             v-if="listType != 'weblog'"></el-table-column>
                            <el-table-column prop="username" label="用户名" v-if="listType != 'weblog'"></el-table-column>
                            <el-table-column prop="name" label="名称" v-if="listType != 'weblog'"></el-table-column>
                            <el-table-column prop="ip" label="IP" width="180"
                                             v-if="listType == 'weblog'"></el-table-column>
                            <el-table-column prop="city" label="IP归属地" v-if="listType == 'weblog'"></el-table-column>
                            <el-table-column prop="usernum" label="用户量" v-if="listType == 'weblog'"></el-table-column>
                            <el-table-column prop="num" label="统计" width="180"></el-table-column>
                            <el-table-column label="操作" fixed="right" width="80">
                                <template slot-scope="scope">
                                    <div class="moduleElTaCaoz">
                                        <el-button size="mini" @click="showDetail(scope)">分析</el-button>
                                    </div>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
            </el-drawer>
        </div>

        <div class="modluDrawer">
            <el-drawer :title="detailTitle" :visible.sync="detailDrawer" :modal="false" :modal-append-to-body="false" append-to-body
                       :show-close="true" :with-header="true" size="75%">
                <div class="drawerTableAll" style="height: calc(100% - 50px);">
                    <div class="draweYujiBiaog" style="height: calc(100% - 25px);">
                        <el-table ref="table" :data="detailTableData" style="width: 100%" stripe
                                  :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                            <el-table-column prop="uid" label="求职者UID" width="120"></el-table-column>

                            <el-table-column label="姓名" width="120">
                                <template slot-scope="scope">
                                    <div class="moduleProps" style="color: #2778f8; cursor: pointer;"
                                         @click="showUser(scope)">
                                        <span>{{ scope.row.name }}</span>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column label="企业名称" v-if="dComNameShow">
                                <template slot-scope="scope">
                                    <div class="moduleProps" style="color: #2778f8; cursor: pointer;"
                                         @click="showCom(scope)">
                                        <span>{{ scope.row.comname }}</span>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="jobname" label="职位名称" v-if="dJobNameShow"></el-table-column>
                            <el-table-column prop="jobclassname" label="职位类别"
                                             v-if="dJobClassNameShow"></el-table-column>
                            <el-table-column prop="content" label="端口" v-if="dPortShow"></el-table-column>
                            <el-table-column prop="ip" label="IP" v-if="dIpShow"></el-table-column>
                            <el-table-column prop="city" label="IP归属地" v-if="dIpCityShow"></el-table-column>
                            <el-table-column prop="num" label="总量" v-if="dAllNumShow"></el-table-column>
                            <el-table-column prop="time" label="时间"></el-table-column>
                            <el-table-column label="查看" v-if="dCkShow">
                                <template slot-scope="scope">
                                    <el-button size="mini" @click="showChat(scope.row.beginid)">查看</el-button>
                                </template>
                            </el-table-column>
                        </el-table>

                    </div>
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
                    <el-dialog title="求职者数据" :visible.sync="userInfoBox" :modal="false" :with-header="true"
                               :modal-append-to-body="false" append-to-body :show-close="true" width="650px">
                        <div class="QiuzDataDome">
                            <div class="QiuzDataHead">
                                <div class="QiuzHeadName">{{ userInfo.name }}</div>
                                <div class="QiuzHeadButn">
                                    <el-button type="warning" size="mini" @click="locktype=1,lock_info='',usersddrawer = true">锁定账号</el-button>
                                </div>
                            </div>
                            <div class="QiuzDataTips">
                                <span>用户名：{{ userInfo.username }}</span>
                                <span>联系电话：{{ userInfo.link || '无' }}</span>
                                <span>用户ID：{{ userInfo.uid }}</span>
                                <span>状态：{{ userInfo.status }}</span>
                            </div>
                            <div class="QiuzDataInfo">
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ userInfo.tdnum_num }}</b>
                                        <span>简历投递数量</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ userInfo.tdclass_num }}</b>
                                        <span>简历投递分类</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ userInfo.usersqdel_num }}</b>
                                        <span>删除简历投递记录</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ userInfo.lookjob_num }}</b>
                                        <span>职位浏览</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ userInfo.tellog_num }}</b>
                                        <span>职位拨号</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ userInfo.chatlog_num }}</b>
                                        <span>直聊人数</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ userInfo.loginlog_num }}</b>
                                        <span>登录次数</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ userInfo.privacylog_num }}</b>
                                        <span>职位隐私号</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ userInfo.weblog_num }}</b>
                                        <span>IP活跃次数</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </el-dialog>
                </div>
                <div class="modluDrawer">
                    <el-dialog title="企业数据" :visible.sync="comInfoBox" :modal="false" :with-header="true"
                               :modal-append-to-body="false" append-to-body :show-close="true" width="650px">
                        <div class="QiuzDataDome">
                            <div class="QiuzDataHead">
                                <div class="QiuzHeadName">{{ comInfo.name }}</div>
                                <div class="QiuzHeadButn">
                                    <el-button type="warning" size="mini" @click="locktype=2,lock_info='',usersddrawer = true">锁定账号</el-button>
                                </div>
                            </div>
                            <div class="QiuzDataTips">
                                <span>用户名：{{ comInfo.username }}</span>
                                <span>联系电话：{{ comInfo.link || '无' }}</span>
                                <span>用户ID：{{ comInfo.uid }}</span>
                                <span>状态：{{ comInfo.status }}</span>
                            </div>
                            <div class="QiuzDataInfo">
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.lookresume_num }}</b>
                                        <span>简历浏览</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.downresume_num }}</b>
                                        <span>简历下载</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.downdel_num }}</b>
                                        <span>删除简历下载记录</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.yqms_num }}</b>
                                        <span>邀请面试</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.addjob_num }}</b>
                                        <span>发布职位</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.refreshjob_num }}</b>
                                        <span>刷新职位</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.sqjob_num }}</b>
                                        <span>收到简历</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.sqdel_num }}</b>
                                        <span>删除收到简历记录</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.chatlog_num }}</b>
                                        <span>直聊人数</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.loginlog_num }}</b>
                                        <span>登录次数</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.privacylog_num }}</b>
                                        <span>简历隐私号</span>
                                    </div>
                                </div>
                                <div class="QiuaInfoList">
                                    <div>
                                        <b>{{ comInfo.weblog_num }}</b>
                                        <span>IP活跃次数</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </el-dialog>
                </div>
                <!--锁定用户弹窗-->
                <div class="modluDrawer">
                    <el-dialog title="锁定用户" :visible.sync="usersddrawer"  :modal="false" :with-header="true" :modal-append-to-body="false" append-to-body
                               :show-close="true" width="450px">
                        <div>
                            <div class="wxsettip_small ">锁定说明</div>
                            <el-input placeholder="请输入内容" type="textarea" :rows="2" v-model="lock_info"></el-input>
                        </div>
                        <span slot="footer" class="dialog-footer">
                            <el-button @click="usersddrawer = false">取 消</el-button>
                            <el-button type="primary" @click="lockUserSave" :disabled="saveLoading">确 定</el-button>
                        </span>
                    </el-dialog>
                </div>
            </el-drawer>
        </div>

        <div class="modluDrawer">
            <el-dialog :title="chattitle" :visible.sync="chatshow" :with-header="true" :modal-append-to-body="false" append-to-body
                       :show-close="true" @closed="chatclosed" width="500px">
                <div class="chat_box_all" style="height:500px;">
                    <div>
                        <div id="chat_content" class="chat_sendcontent">
                            <ul v-if="chatlog.length>0">
                                <li v-if="morelog" id="chat_more" class="chat_more" @click="showChat(chatid)">
                                    <div class="chat_comjobtip"><span class="chat_comjobtip_p">查看更多消息</span></div>
                                </li>

                                <li v-for="(msg, ck) in chatlog" :key="ck" class="chat_li">
                                    <div v-if="msg.time" class="chat_sendtime"><span class="chat_sendtime_b">{{msg.time}}</span>
                                    </div>
                                    <!--消息撤回-->
                                    <div v-if="msg.msgtype=='recall'" class="chat_recall chatRecalls"><span
                                            class="chat_recall_b">{{msg.content}}</span></div>
                                    <div v-if="msg.msgtype=='char' || msg.msgtype=='voice' || msg.msgtype=='spview' || msg.msgtype=='map'">
                                        <!--我方消息-->
                                        <div v-if="msg.mine" class="chat_sendcont">
                                            <!--头像-->
                                            <div class="chat_sendcont_pic"><img :src="msg.avatar"></div>
                                            <!--面试邀请-->
                                            <span v-if="msg.inviteid" class="chat_sendcont_p">
                                                <em class="chat_sendcont_yqbox">发送了面试邀请<i class="chat_sendcont_yq"></i></em>
                                            </span>
                                            <!--语音-->
                                            <span v-else-if="msg.msgtype == 'voice'" class="chat_sendcont_p">
                                                <audioyy :src="msg.voice"></audioyy>
                                            </span>
                                            <!--我发的图片-->
                                            <span v-else-if="msg.image" class="chat_sendcont_p">
                                                <img class="chat_photos" :src="msg.image">
                                            </span>

                                            <div v-else-if="msg.msgtype == 'spview'" class="chat_sendcont_p">
                                                <div class="spqxboxtwo" v-if="msg.content == 'closesp'"><i
                                                        class="spqx2"></i>{{msg.fusertype != usertype ? '对方' : ''}}已取消
                                                </div>
                                                <div class="spqxboxtwo" v-else-if="msg.content == 'refused'"><i
                                                        class="spqx2"></i>{{msg.fusertype != usertype ? '对方' : ''}}已拒绝
                                                </div>
                                                <div class="spqxboxtwo" v-else><i class="spqx2"></i>{{msg.content}}
                                                </div>
                                            </div>
                                            <span v-else-if="msg.msgtype == 'map'" class="chat_sendcont_p"><img
                                                    class="chat_photos" :src="msg.url"></span>

                                            <span v-else class="chat_sendcont_p">
                                                <span v-for="(cn, cnk) in msg.content" :key="cnk">
                                                    <!--表情-->
                                                    <img v-if="cn.t == 'emoji'" class="chat_bq_img" :src="cn.n">
                                                    <!--文字-->
                                                    <span v-else-if="cn.t == 'char'">
                                                        <em>{{cn.n}}</em>
                                                    </span>
                                                </span>
                                            </span>
                                        </div>
                                        <!--对方消息-->
                                        <div v-else class="chat_other_say">
                                            <!--头像-->
                                            <div class="chat_other_pic"><img :src="msg.avatar"></div>
                                            <!--面试邀请-->
                                            <span v-if="msg.inviteid" class="chat_sendcont_p2">
                                                <em class="chat_sendcont_jsbox">发送了面试邀请<i
                                                        class="chat_sendcont_jsyq"></i></em>
                                            </span>
                                            <!--语音-->
                                            <span v-else-if="msg.msgtype == 'voice'" class="chat_sendcont_p2">
                                                <audioyy :src="msg.voice"></audioyy>
                                            </span>
                                            <!--我收到的图片-->
                                            <span v-else-if="msg.image" class="chat_sendcont_p2">
                                                <img class="chat_photos" :src="msg.image">
                                            </span>
                                            <div v-else-if="msg.msgtype == 'spview'" class="chat_sendcont_p2">
                                                <div class="spqxbox" v-if="msg.content == 'closesp'"><i
                                                        class="spqx"></i>{{msg.fusertype != usertype ? '对方' : ''}}已取消
                                                </div>
                                                <div class="spqxbox" v-else-if="msg.content == 'refused'"><i
                                                        class="spqx"></i>{{msg.fusertype != usertype ? '对方' : ''}}已拒绝
                                                </div>
                                                <div class="spqxbox" v-else><i class="spqx"></i>{{msg.content}}</div>
                                            </div>
                                            <span v-else-if="msg.msgtype == 'map'" class="chat_sendcont_p2"><img
                                                    class="chat_photos" :src="msg.url"></span>

                                            <span v-else class="chat_sendcont_p2">
                                                <span v-for="(cn, cnk) in msg.content" :key="cnk">
                                                    <!--表情-->
                                                    <img v-if="cn.t == 'emoji'" class="chat_bq_img" :src="cn.n">
                                                    <!--文字-->
                                                    <span v-else-if="cn.t == 'char'">
                                                        <em>{{cn.n}}</em>
                                                    </span>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                    <div v-else-if="msg.msgtype == 'ask'">
                                        <div v-if="msg.mine" class="chat_hdh">
                                            <span><div class="chat_hdh_p">{{msg.asktext}}</div></span>
                                        </div>
                                        <div v-else class="chat_hhyq">
                                            <div class="chat_hhyq_p chat_hhyq_pq">
                                                <i :class="msg.asktype=='tel'?'chat_hhyq_icon':'chat_hhwx_icon'"></i>{{msg.asktext}}
                                            </div>
                                            <div v-if="msg.askstatus == '1'" :id="'hhbtn_'+msg.chatid"
                                                 class="chat_hhyq_bth"><span class="wxyty">已同意</span></div>
                                            <div v-if="msg.askstatus == '2'" :id="'hhbtn_'+msg.chatid"
                                                 class="chat_hhyq_bth"><span class="wxyty">已拒绝</span></div>
                                            <div v-else class="chat_hhyq_bth">
                                                <div class="bh_bth bh_bthline">同意</div>
                                                <span class="bh_bth">拒绝</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else-if="msg.msgtype == 'confirm' && msg.askvalue" class="chat_hhyq">
                                        <div class="chat_hhyq_p"><i
                                                :class="msg.confirmtype=='tel'?'chat_hhyq_icon':'chat_hhwx_icon'"></i>{{msg.askmsg}}{{msg.askvalue}}
                                        </div>
                                        <div class="chat_hhyq_bth">
                                            <span class="">{{msg.confirmtype=='wx'?'复制微信号':'复制手机号'}}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="msg.msgtype == 'refuse' && msg.refusetext" class="chat_hdh">
                                        <span><div class="chat_hdh_p">{{msg.refusetext}}</div></span>
                                    </div>
                                    <div v-else-if="(msg.msgtype == 'job' || msg.msgtype == 'adminjob') && msg.job"
                                         class="chat_yx_job" @click="toShow(msg.job.weburl)">
                                        <span>
                                            <div class="chat_yx_job_name">{{msg.job.name}}</div>
                                            <span class="chat_yx_xz">{{msg.job.job_salary}}</span>
                                            <div class="chat_yx_com_name">{{msg.job.com_name}}</div>
                                            <div class="">
                                                {{msg.job.job_city_one}}{{msg.job.job_city_two?'-'+msg.job.job_city_two:''}}
                                                {{msg.job.job_exp?'.'+msg.job.job_exp+'经验':''}}
                                                {{msg.job.job_edu?'.'+msg.job.job_edu+'学历':''}}
                                            </div>
                                            <div class="chat_yx_time"
                                                 v-if="msg.newjob">{{msg.ctime}} {{msg.newjob_tip}}</div>
                                        </span>
                                    </div>
                                    <div v-else-if="msg.msgtype == 'resume'" class="chat_userresume"
                                         @click="toShow(expect.weburl)">
                                        <span>
                                            <div class="chat_userresume_name">{{expect.uname}}<span
                                                    class="chat_userresume_ys">{{expect.sex_n}},{{expect.age}}岁</span></div>
                                            <div class="chat_userresume_info">{{expect.exp_n}} . {{expect.edu_n}} </div>
                                            <div class="chat_userresume_want">期望职位 <span
                                                    class="chat_userresume_wantjob">{{expect.name}}</span> </div>
                                            <div class="chat_userresume_pic"><img :src="expect.photo"></div>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    window.audioList=[];

    var dsq = [];
    var ctime = '';

    var end = new Date().valueOf();
    var start = accSub(end, accMul(86400, 7000));

    module.exports = {
        props: {
            user_num: Number
        },
        watch: {
            user_num: {
                handler(newValue, oldValue) {
                    if (newValue != oldValue) {
                        this.handelSearch();
                    }
                },
                deep: true,
                immediate: true
            }
        },
        computed: {
            datetime() {
                var ntime = '';
                if (this.applyListLoading == false && this.applyClassLoading == false && this.applyDelLoading == false && this.lookJobLoading == false && this.telLogLoading == false && this.chatLogLoading == false && this.loginLogLoading == false && this.webLogLoading == false && this.privacyLogLoading == false) {
                    ntime = ctime;
                }
                return ntime;
            }
        },
        data: function () {
            return {
                loading: false,
                search: {
                    time: [start, end]
                },

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

                applyListData: [],
                applyListLoading: true,

                applyClassData: [],
                applyClassLoading: true,

                applyDelData: [],
                applyDelLoading: true,

                lookJobData: [],
                lookJobLoading: true,

                telLogData: [],
                telLogLoading: true,

                chatLogData: [],
                chatLogLoading: true,

                loginLogData: [],
                loginLogLoading: true,

                webLogData: [],
                webLogLoading: true,

                privacyLogData: [],
                privacyLogLoading: true,

                listSearch: {
                    time: [start, end]
                },

                listTimeOptions: {
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

                listType: 'tdnum',
                listTitle: '更多简历投递',
                listDrawer: false,
				listkeyword:'',
                listTableData: [],

                detailTitle: '简历投递详情',
                detailDrawer: false,
                detailTableData: [],
                detailType: '',
                detailUid: '',
                detailIp: '',

                total: 0,
                limit: 0,
                currentPage: 1,
				prevPage:0,
                page_sizes: [],

                dComNameShow: false,
                dJobNameShow: false,
                dJobClassNameShow: false,
                dPortShow: false,
                dIpShow: false,
                dIpCityShow: false,
                dAllNumShow: false,
                dTimeShow: false,
                dCkShow: false,

                userInfoBox: false,
                userInfo: [],
                comInfoBox: false,
                comInfo: [],

                chatshow: false,
                chattitle: '聊天',
                chatid: '',
                chatpage: 1,
                expect: [],
                joblist: [],
                chatlog: [],
                morelog: false,

                //
                usersddrawer:false,
                lock_info:'',
                saveLoading:false,
                locktype:1
            }
        },
        components: {
            'audioyy': httpVueLoader('../../../component/audioyy.vue')
        },
        methods: {
            handelSearch() {

                this.applyListLoading = true;
                this.applyClassLoading = true;
                this.applyDelLoading = true;
                this.lookJobLoading = true;
                this.telLogLoading = true;
                this.chatLogLoading = true;
                this.loginLogLoading = true;
                this.webLogLoading = true;
                this.privacyLogLoading = true;

                if (dsq.length > 0) {
                    for (let i = 0; i < dsq.length; i++) {
                        clearTimeout(dsq[i]);
                    }
                    dsq = [];
                }
                this.getUserAbnormal();
            },
            getUserAbnormal: function () {
                let that = this;

                that.getApplyList();
                that.getApplyClass();
                that.getApplyDel();

                let a = setTimeout(function () {

                    that.getLookJob();
                    that.getTelLog();
                    that.getChatLog();
                }, 1000);
                dsq.push(a);
                let b = setTimeout(function () {

                    that.getLoginLog();
                    that.getWebLog();
                    that.getPrivacyLog();
                }, 2000);
                dsq.push(b);
            },
            getApplyList: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getApplyList', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.applyListLoading = false;
                        that.applyListData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getApplyClass: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getApplyClass', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.applyClassLoading = false;
                        that.applyClassData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getApplyDel: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getApplyDel', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.applyDelLoading = false;
                        that.applyDelData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getLookJob: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getLookJob', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.lookJobLoading = false;
                        that.lookJobData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getTelLog: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getTelLog', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.telLogLoading = false;
                        that.telLogData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getChatLog: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                params.usertype = 1;
                httpPost('m=tool&c=abnormal&a=getChatLog', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.chatLogLoading = false;
                        that.chatLogData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getLoginLog: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getLoginLog', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.loginLogLoading = false;
                        that.loginLogData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getWebLog: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getWebLog', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.webLogLoading = false;
                        that.webLogData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getPrivacyLog: function () {

                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getPrivacyLog', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.privacyLogLoading = false;
                        that.privacyLogData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },

            //  更多裂变
            listBoxShow(type) {

                let that = this;
                that.listSearch.time = that.search.time;
                that.listType = type;
				that.listkeyword = '';
                that.handleSearchList();
            },
            handleSearchList: function () {

                let that = this,
                    url = 'm=tool&c=abnormal';

                if (that.listType == 'tdnum') {

                    that.listTitle = '简历投递统计';
                    url = url + '&a=getApplyList';
                } else if (that.listType == 'tdclass') {

                    that.listTitle = '简历投递分类';
                    url = url + '&a=getApplyClass';
                } else if (that.listType == 'usersqdel') {

                    that.listTitle = '删除简历投递记录';
                    url = url + '&a=getApplyDel';
                } else if (that.listType == 'lookjob') {

                    that.listTitle = '职位浏览记录';
                    url = url + '&a=getLookJob';
                } else if (that.listType == 'tellog') {

                    that.listTitle = '职位拨号记录';
                    url = url + '&a=getTelLog';
                } else if (that.listType == 'chatlog') {

                    that.listTitle = '直聊人数统计';
                    url = url + '&a=getChatLog';
                } else if (that.listType == 'loginlog') {

                    that.listTitle = '登录次数统计';
                    url = url + '&a=getLoginLog';
                } else if (that.listType == 'weblog') {

                    that.listTitle = 'IP活跃统计';
                    url = url + '&a=getWebLog';
					that.listSearch.ip = that.listkeyword;
                } else if (that.listType == 'privacylog') {

                    that.listTitle = '职位隐私号记录';
                    url = url + '&a=getPrivacyLog';
                }

                let params = JSON.parse(JSON.stringify(that.listSearch));
                params.usertype = 1;
                params.limit = 50;
                params.list = 1;
                that.loading = true;
                httpPost(url, params).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;
                        that.listTableData = data.list;
                        that.loading = false;
                    }
                });

                that.listDrawer = true;
            },
            showDetail(scope) {
                let that = this;
                that.detailType = that.listType;
                if (that.detailType == 'weblog') {

                    let ip = scope.row.ip.split('.');
                    that.detailIp = ip.join('_');
                } else {

                    that.detailUid = scope.row.uid;
                }
                if (scope.row.name != undefined && scope.row.name != '') {

                    that.detailTitle = typeName(that.detailType) + '详情 - ' + scope.row.name;
                } else {

                    that.detailTitle = typeName(that.detailType) + '详情';
                }
                that.getDetailList();
            },

            //  详情列表
            handleRowClick(row, column, event, type) {

                let that = this,
                    url = 'm=tool&c=abnormal&a=detail';
                that.detailType = type;
                if (that.detailType == 'weblog') {
                    let ip = row.ip.split('.');
                    that.detailIp = ip.join('_');
                } else {
                    that.detailUid = row.uid;
                }
                if (row.name != undefined && row.name != '') {

                    that.detailTitle = typeName(that.detailType) + '详情 - ' + row.name;
                } else {

                    that.detailTitle = typeName(that.detailType) + '详情';
                }
                that.getDetailList();
            },

            handleCurrentChange(val) {

                this.currentPage = val;
                this.getDetailList();
            },
            handleSizeChange(val) {

                this.currentPage = 1;
                this.limit = val;
                this.getDetailList();
            },
            getDetailList() {
                let that = this,
                    url = 'm=tool&c=abnormal&a=detail';

                that.dComNameShow = that.dJobNameShow = that.dJobClassNameShow = that.dPortShow = that.dIpShow = that.dIpCityShow = that.dAllNumShow = that.dTimeShow = that.dCkShow = false;
                if (that.detailType != 'loginlog' && that.detailType != 'weblog') {
                    that.dComNameShow = true;
                }
                if (that.detailType != 'loginlog' && that.detailType != 'chatlog' && that.detailType != 'weblog') {
                    that.dJobNameShow = true;
                }
                if (that.detailType == 'tdclass') {
                    that.dJobClassNameShow = true;
                }
                if (that.detailType == 'loginlog') {
                    that.dPortShow = true;
                }
                if (that.detailType == 'lookjob' || that.detailType == 'tellog' || that.detailType == 'loginlog' || that.detailType == 'weblog') {
                    that.dIpShow = true;
                    that.dIpCityShow = true;
                }
                if (that.detailType == 'weblog') {
                    that.dAllNumShow = true;
                }
                if (that.detailType == 'chatlog') {
                    that.dCkShow = true;
                }

                let params = JSON.parse(JSON.stringify(that.search));
                params.type = that.detailType;
                params.usertype = 1;
                params.page = that.currentPage;
                params.limit = that.limit;

                if (that.listType == 'weblog') {
                    params.ip = that.detailIp;
                } else {
                    params.uid = that.detailUid;
                }
                that.loading = true;
                httpPost(url, params).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;
                        that.detailTableData = data.list;
                        that.total = parseInt(data.total);
                        that.page_sizes = data.page_sizes;
						that.limit = data.page_size;
                        that.loading = false;
						
						if(that.prevPage != that.currentPage){
							that.prevPage = that.currentPage;
							that.$refs.table.bodyWrapper.scrollTop = 0;
						}
                    }
                });
                that.detailDrawer = true;
            },
            showUser(scope) {

                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                params.uid = scope.row.uid;
                httpPost('m=tool&c=abnormal&a=userStatis', params).then(function (res) {
                    if (res.data.error == 0) {
                        let data = res.data.data;
                        that.userInfo = data;
                    }
                });
                that.userInfoBox = true;
            },
            showCom(scope) {

                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                params.uid = scope.row.comid;
                httpPost('m=tool&c=abnormal&a=comStatis', params).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;
                        that.comInfo = data;
                    }
                });
                that.comInfoBox = true;
            },
            showChat(chatid) {
                var that = this;
                that.chatshow = true;
                this.chatid = chatid;
                var params = {beginid: that.chatid, page: this.chatpage};
                httpPost('m=tool&c=chatlog&a=userchat', params).then(function (result) {
                    var res = result.data.data;
                    if (res) {
                        if (res.msg) {
                            message.error(res.msg);
                            return;
                        }
                        if (res.fuser && res.tuser) {
                            that.linkman = res.tuser.nickname;
                            that.chattitle = res.fuser.nickname + '和' + res.tuser.nickname + '的聊天记录';
                        }
                        that.chatpage += 1;

                        var scroll = null;
                        if (chatpage > 2) {
                            // 第二页开始，记录前一次滚动距离
                            scroll = document.getElementById('chat_content').scrollHeight;
                        }

                        if (res.code == 0) {
                            var list = [];
                            if (res.joblist) {
                                that.joblist = res.joblist;
                            }
                            var chatlog = res.data;
                            that.expect = res.expect;
                            if (chatlog.length > 0) {

                                pastSend = chatlog[0].sendTime;
                                that.morelog = false;

                                for (var i in chatlog) {
                                    chat = renderPast(chatlog[i], i);
                                    if (chat) {
                                        list.push(chat);
                                    }
                                    // 最后一条时，如还有历史消息，增加更多消息
                                    if (i == chatlog.length - 1 && res.ismore) {
                                        that.morelog = true;
                                        lastid = chatlog[i]['id'];
                                    }
                                }

                                list.reverse();
                                that.chatlog = list.concat(that.chatlog);

                                if (scroll) {
                                    that.$nextTick(function () {
                                        // 渲染后，滚动到上一页的距离
                                        var newScroll = document.getElementById('chat_content').scrollHeight;
                                        chatScrollTo(newScroll - scroll);
                                    })
                                }
                            } else {
                                that.morelog = false;
                            }
                        }
                        //    moreReady = true;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            chatclosed: function () {
                this.chatpage = 1;
                this.morelog = false;
                this.chatlog = [];
            },
            toShow:function(url){
                if (url && url != '') {
                    window.open(url);
                }
            },
            lockUserSave:function (){
                let _this = this;
                let url = 'm=tool&c=abnormal&a=lock';
                let sendData = {
                    uid:this.userInfo.uid,
                    type:2,
                    lock_info:this.lock_info
                };
                let row = {}
                if(this.locktype == 1){
                    sendData.uid = this.userInfo.uid
                    row.uid=_this.userInfo.uid
                }else{
                    sendData.uid = this.comInfo.uid
                    row.comid=_this.comInfo.uid
                }
                _this.saveLoading = true;
                httpPost(url, sendData).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg)
                        let send={
                            row:row
                        };
                        if(_this.locktype == 1) {
                            _this.showUser(send);
                        }else{
                            _this.showCom(send);
                        }
                    }else {
                        message.error(res.msg);
                    }
                }).finally(function () {
                    _this.saveLoading = false;
                });
                this.usersddrawer = false;
            },
        },
    };

    function typeName(type) {
        if (type == 'tdnum') {
            return '简历投递数量';
        } else if (type == 'tdclass') {
            return '简历投递分类';
        } else if (type == 'usersqdel') {
            return '删除简历投递记录';
        } else if (type == 'lookjob') {
            return '职位浏览';
        } else if (type == 'tellog') {
            return '职位拨号';
        } else if (type == 'chatlog') {
            return '直聊人数';
        } else if (type == 'loginlog') {
            return '登录';
        } else if (type == 'privacylog') {
            return '职位隐私号';
        } else if (type == 'weblog') {
            return 'IP活跃';
        }
    }
</script>
<style scoped>
    .moduleTable {
        max-height: calc(100% - (60px + 10px));
    }

    .moduleElHight {
        overflow-y: auto;
        position: relative;
        width: 100%;
        height: 100%;
    }

    .moduleElHight::-webkit-scrollbar {
        display: none;
    }

    .el-table .el-table__cell {
        padding: 8px 0;
    }

    .draweYujiBiaog .el-table .el-table__cell {
        padding: 12px 0;
    }

    .centerCss {
        text-align: center;
    }

    .firstName {
        color: #2778f8;
    }

    .ThirdNum {
        color: #f00;
        font-weight: bold;
    }
</style>