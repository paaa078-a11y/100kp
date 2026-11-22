<template>
    <div class="moduleElHight">
        <div class="moduleSeachbig">
            <div class="tableSeachInpt tableSeachInptsmalltwo">
                <el-date-picker v-model="search.time" type="daterange" align="right" unlink-panels range-separator="至"
                                start-placeholder="开始日期" end-placeholder="结束日期" :picker-options="timeOptions"
                                value-format="timestamp" @change="handelSearch">
                </el-date-picker>
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
                        简历浏览
                        <el-tooltip class="item" effect="dark" content="企业浏览简历次数统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('lookresume')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="lookResumeLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="lookResumeData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'lookresume')"
                              :row-style="{ 'cursor': 'pointer' }" >
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        投递简历未查看
                        <el-tooltip class="item" effect="dark" content="投递简历未查看企业统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('notviewresume')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="notViewResumeLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="notViewResumeData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'notviewresume')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                        <el-table-column prop="login_date_n" label="最后登录时间" class-name="centerCss"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        简历下载
                        <el-tooltip class="item" effect="dark" content="企业下载简历次数统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('downresume')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="downResumeLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="downResumeData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'downresume')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        删除简历下载记录
                        <el-tooltip class="item" effect="dark" content="企业删除下载简历次数统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('downdel')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="downDelLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="downDelData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'downdel')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        邀请面试
                        <el-tooltip class="item" effect="dark" content="企业邀请求职者面试次数统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('yqms')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="inviteLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="inviteData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'yqms')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        发布职位
                        <el-tooltip class="item" effect="dark" content="企业发布职位数量统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('addjob')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="addJobLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="addJobData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'addjob')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        刷新职位
                        <el-tooltip class="item" effect="dark" content="企业刷新职位次数统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('refreshjob')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="refreshJobLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="refreshJobData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'refreshjob')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        收到简历
                        <el-tooltip class="item" effect="dark" content="企业收到求职者投递简历数量统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('sqjob')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="sqJobLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="sqJobData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'sqjob')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        删除简历投递记录
                        <el-tooltip class="item" effect="dark" content="企业删除简历投递记录统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('usersqdel')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="sqJobDelLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="sqJobDelData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'usersqdel')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        直聊人数
                        <el-tooltip class="item" effect="dark" content="企业向不同求职者发送聊天统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('comchatlog')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="chatLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="chatData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'comchatlog')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        登录次数
                        <el-tooltip class="item" effect="dark" content="企业登录次数统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('comloginlog')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="loginLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="loginData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'conloginlog')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        IP活跃次数
                        <el-tooltip class="item" effect="dark" content="根据行为轨迹统计同一IP下不同企业访问量" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('comweblog')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="webLogLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="webLogData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'comweblog')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="ip" label="IP" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="city" label="IP归属地" class-name="centerCss "></el-table-column>
                        <el-table-column prop="usernum" label="用户量" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        简历隐私号
                        <el-tooltip class="item" effect="dark" content="企业在简历详情页点拨打电话生成的隐私号次数统计"
                                    placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('jobprivacylog')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="jobPrivacyLogLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="jobPrivacyLogData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'jobprivacylog')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        被投递岗位
                        <el-tooltip class="item" effect="dark" content="企业职位被个人用户投递数量统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('jobsq')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="jobSqLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="jobSqData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'jobsq')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="name" label="企业名称" class-name="centerCss"></el-table-column>
                        <el-table-column prop="job_name" label="职位名称"
                                         class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        被拨号岗位
                        <el-tooltip class="item" effect="dark" content="企业职位被个人用户拨号数量统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('jobtel')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="jobTelLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="jobTelData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'jobtel')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="name" label="企业名称" class-name="centerCss"></el-table-column>
                        <el-table-column prop="job_name" label="职位名称"
                                         class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
            <div class="xwyj_box_list">
                <div class="xwyj_box_list_tit">
                    <div class="">
                        被拨号企业
                        <el-tooltip class="item" effect="dark" content="企业被个人用户拨号数量统计" placement="right-start">
                            <i class="xwyj_box_n el-icon-info"> </i>
                        </el-tooltip>
                    </div>
                    <div class="xwyj_box_list_titmore">
                        <el-button type="text" icon="el-icon-view" @click="listBoxShow('companytel')">更多</el-button>
                    </div>
                </div>
                <div class="xwyj_list">
                    <el-table class="xwyj_table" v-loading="comTelLoading" element-loading-text="拼命加载中"
                              element-loading-spinner="el-icon-loading" element-loading-background="rgba(0, 0, 0, 0.8)"
                              :data="comTelData" :height="440"
                              :header-cell-style="{ 'background': '#F5F7FA', 'color': '#555', 'text-align': 'center', 'padding': '7px 0' }"
                              @row-click="(row, column, e) => handleRowClick(row, column, e, 'companytel')"
                              :row-style="{ 'cursor': 'pointer' }">
                        <el-table-column prop="username" label="用户名" class-name="centerCss firstName"></el-table-column>
                        <el-table-column prop="name" label="企业名称" class-name="centerCss"></el-table-column>
                        <el-table-column prop="num" label="总量" class-name="centerCss ThirdNum"></el-table-column>
                    </el-table>
                </div>
            </div>
        </div>

        <div class="modluDrawer">
            <el-drawer :title="listTitle" :visible.sync="listDrawer" :modal-append-to-body="false" :show-close="true"
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
                            <el-input placeholder="请输入IP" v-model="listkeyword" style="width:200px;margin-left:8px;" clearable v-if="listType == 'comweblog'"></el-input>
                            <el-button type="primary" icon="el-icon-search" size="small" style="margin-left: 8px;"
                                       @click="handleSearchList">
                                查询
                            </el-button>
                        </div>
                    </div>
                    <div class="draweYujiBiaog">
                        <el-table :data="listTableData" style="width: 100%" stripe
                                  :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%">

                            <el-table-column prop="uid" label="用户ID" width="180"
                                             v-if="listType != 'comweblog'"></el-table-column>
                            <el-table-column prop="username" label="用户名"
                                             v-if="listType != 'comweblog'"></el-table-column>
                            <el-table-column prop="name" label="名称" v-if="listType != 'comweblog'"></el-table-column>
                            <el-table-column prop="job_name" label="职位名称"
                                             v-if="listType == 'jobsq' || listType == 'jobtel'"></el-table-column>
                            <el-table-column prop="job_name" label="拨号岗位"
                                             v-if="listType == 'companytel'"></el-table-column>
                            <el-table-column prop="login_date" label="企业最后登录时间"
                                             v-if="listType == 'notviewresume'"></el-table-column>
                            <el-table-column prop="ip" label="IP" width="180"
                                             v-if="listType == 'comweblog'"></el-table-column>
                            <el-table-column prop="city" label="IP归属地" v-if="listType == 'comweblog'"></el-table-column>
                            <el-table-column prop="usernum" label="用户量"
                                             v-if="listType == 'comweblog'"></el-table-column>
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
            <el-drawer :title="detailTitle" :visible.sync="detailDrawer" :modal="false" :modal-append-to-body="false"
                       :show-close="true"
                       :with-header="true" size="75%">
                <div class="drawerTableAll" style="height: calc(100% - 50px);">
                    <div class="draweYujiBiaog" style="height: calc(100% - 25px);">
                        <el-table ref="table" :data="detailTableData" style="width: 100%" stripe
                                  :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                            <el-table-column prop="uid" label="求职者UID" width="120"></el-table-column>

                            <el-table-column label="姓名" width="120" v-if="dNameShow">
                                <template slot-scope="scope">
                                    <div class="moduleProps" style="color: #2778f8; cursor: pointer;"
                                         @click="showUser(scope)">
                                        <span>{{ scope.row.name }}</span>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column label="企业名称">
                                <template slot-scope="scope">
                                    <div class="moduleProps" style="color: #2778f8; cursor: pointer;"
                                         @click="showCom(scope)">
                                        <span>{{ scope.row.comname }}</span>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="jobname" label="职位名称" v-if="dJobNameShow"></el-table-column>
                            <el-table-column label="简历查看状态" v-if="dStateShow">
                                <template slot-scope="scope">
                                    <span v-if="scope.row.body != ''">取消申请，原因：{{ scope.row.body }}</span>
                                    <span v-else>未查看</span>
                                </template>
                            </el-table-column>
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
                               :modal-append-to-body="false" :show-close="true" width="650px">
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
                    <el-dialog title="求职者数据" :visible.sync="comInfoBox" :modal="false" :with-header="true"
                               :modal-append-to-body="false" :show-close="true" width="650px">
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
            </el-drawer>
        </div>

        <div class="modluDrawer">
            <el-dialog :title="chattitle" :visible.sync="chatshow" :with-header="true" :modal-append-to-body="false"
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

        <!--锁定用户弹窗-->
        <div class="modluDrawer">
            <el-dialog title="锁定用户" :visible.sync="usersddrawer" :with-header="true" :modal-append-to-body="false"
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

    </div>
</template>
<script>
    window.audioList = [];
    var dsq = [];
    var ctime = '';
    var end = new Date().valueOf();
    var start = accSub(end, accMul(86400, 7000));
    module.exports = {
        props: {
            com_num: Number
        },
        watch: {
            com_num: {
                handler(newValue, oldValue) {
                    if (newValue != oldValue && newValue != 0) {
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
                if (this.lookResumeLoading == false && this.notViewResumeLoading == false && this.downResumeLoading == false && this.downDelLoading == false && this.inviteLoading == false && this.addJobLoading == false && this.refreshJobLoading == false && this.sqJobLoading == false && this.sqJobDelLoading == false && this.chatLoading == false && this.loginLoading == false && this.webLogLoading == false && this.jobPrivacyLogLoading == false && this.jobSqLoading == false && this.jobTelLoading == false && this.comTelLoading == false) {
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

                lookResumeData: [],
                lookResumeLoading: true,
                notViewResumeData: [],
                notViewResumeLoading: true,
                downResumeData: [],
                downResumeLoading: true,
                downDelData: [],
                downDelLoading: true,
                inviteData: [],
                inviteLoading: true,
                addJobData: [],
                addJobLoading: true,
                refreshJobData: [],
                refreshJobLoading: true,
                sqJobData: [],
                sqJobLoading: true,
                sqJobDelData: [],
                sqJobDelLoading: true,
                chatData: [],
                chatLoading: true,
                loginData: [],
                loginLoading: true,
                webLogData: [],
                webLogLoading: true,
                jobPrivacyLogData: [],
                jobPrivacyLogLoading: true,
                jobSqData: [],
                jobSqLoading: true,
                jobTelData: [],
                jobTelLoading: true,
                comTelData: [],
                comTelLoading: true,

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

                listType: 'lookresume',
                listTitle: '更多浏览记录',
                listDrawer: false,
				listkeyword:'',
                listTableData: [],

                detailTitle: '简历浏览详情',
                detailDrawer: false,
                detailTableData: [],
                detailType: '',
                detailUid: '',
                detailIp: '',
                detailJobId: '',
                detailComId: '',

                total: 0,
                limit: 0,
                currentPage: 1,
				prevPage:0,
                page_sizes: [],

                dNameShow: false,
                dJobNameShow: false,
                dStateShow: false,
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

                this.lookResumeLoading = true;
                this.notViewResumeLoading = true;
                this.downResumeLoading = true;
                this.downDelLoading = true;
                this.inviteLoading = true;
                this.addJobLoading = true;
                this.refreshJobLoading = true;
                this.sqJobLoading = true;
                this.sqJobDelLoading = true;
                this.chatLoading = true;
                this.loginLoading = true;
                this.webLogLoading = true;
                this.jobPrivacyLogLoading = true;
                this.jobSqLoading = true;
                this.jobTelLoading = true;
                this.comTelLoading = true;

                if (dsq.length > 0) {
                    for (let i = 0; i < dsq.length; i++) {
                        clearTimeout(dsq[i]);
                    }
                    dsq = [];
                }
                this.getComAbnormal();
            },
            getComAbnormal: function () {
                let that = this;

                that.getLookResume();
                that.getNotViewResume();
                that.getDownResume();
                that.getDownDel();
                let a = setTimeout(function () {
                    that.getInvite();
                    that.getAddJob();
                    that.getRefreshJob();
                }, 1000);
                dsq.push(a);
                let b = setTimeout(function () {

                    that.getSqJob();
                    that.getSqJobDel();
                    that.getChatList();
                }, 2000);
                dsq.push(b);

                let c = setTimeout(function () {
                    that.getLoginLog();
                    that.getWebLog();
                    that.getPrivacyLog();
                }, 3000);
                dsq.push(c);

                let d = setTimeout(function () {
                    that.getJobSq();
                    that.getJobTel();
                    that.getComTel();
                }, 4000);
                dsq.push(d);
            },

            getLookResume: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getLookResume', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.lookResumeLoading = false;
                        that.lookResumeData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getNotViewResume: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getNotViewResume', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.notViewResumeLoading = false;
                        that.notViewResumeData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getDownResume: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getDownResume', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.downResumeLoading = false;
                        that.downResumeData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getDownDel: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getDownDel', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.downDelLoading = false;
                        that.downDelData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getInvite: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getInvite', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.inviteLoading = false;
                        that.inviteData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getAddJob: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getAddJob', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.addJobLoading = false;
                        that.addJobData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getRefreshJob: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getRefreshJob', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.refreshJobLoading = false;
                        that.refreshJobData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getSqJob: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getSqJob', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.sqJobLoading = false;
                        that.sqJobData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getSqJobDel: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getSqJobDel', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.sqJobDelLoading = false;
                        that.sqJobDelData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getChatList: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                params.usertype = 2;
                httpPost('m=tool&c=abnormal&a=getChatList', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.chatLoading = false;
                        that.chatData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getLoginLog: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getComLoginLog', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.loginLoading = false;
                        that.loginData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getWebLog: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getComWebLog', params, {hideloading: true}).then(function (res) {
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
                httpPost('m=tool&c=abnormal&a=getJobPrivacyLog', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.jobPrivacyLogLoading = false;
                        that.jobPrivacyLogData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getJobSq: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getJobSq', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.jobSqLoading = false;
                        that.jobSqData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getJobTel: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getJobTel', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.jobTelLoading = false;
                        that.jobTelData = data.list;
                        if (data.ctime) {
                            ctime = data.ctime;
                        }
                    }
                });
            },
            getComTel: function () {
                let that = this;
                let params = JSON.parse(JSON.stringify(that.search));
                httpPost('m=tool&c=abnormal&a=getComTel', params, {hideloading: true}).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;

                        that.comTelLoading = false;
                        that.comTelData = data.list;
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

                if (that.listType == 'lookresume') {

                    that.listTitle = '简历浏览统计';
                    url = url + '&a=getLookResume';
                } else if (that.listType == 'notviewresume') {

                    that.listTitle = '简历未查看统计';
                    url = url + '&a=getNotViewResume';
                } else if (that.listType == 'downresume') {

                    that.listTitle = '简历下载统计';
                    url = url + '&a=getDownResume';
                } else if (that.listType == 'downdel') {

                    that.listTitle = '删除简历下载记录';
                    url = url + '&a=getDownDel';
                } else if (that.listType == 'yqms') {

                    that.listTitle = '邀请面试记录';
                    url = url + '&a=getInvite';
                } else if (that.listType == 'addjob') {

                    that.listTitle = '发布职位信息';
                    url = url + '&a=getAddJob';
                } else if (that.listType == 'refreshjob') {

                    that.listTitle = '刷新职位记录';
                    url = url + '&a=getRefreshJob';
                } else if (that.listType == 'sqjob') {

                    that.listTitle = '收到简历信息';
                    url = url + '&a=getSqJob';
                } else if (that.listType == 'usersqdel') {

                    that.listTitle = '删除收到简历记录';
                    url = url + '&a=getSqJobDel';
                } else if (that.listType == 'comchatlog') {

                    that.listTitle = '直聊人数信息';
                    url = url + '&a=getChatList';
                } else if (that.listType == 'comloginlog') {

                    that.listTitle = '登录次数';
                    url = url + '&a=getComLoginLog';
                } else if (that.listType == 'comweblog') {

                    that.listTitle = 'IP活跃次数';
					that.listSearch.ip = that.listkeyword;
                    url = url + '&a=getComWebLog';
                } else if (that.listType == 'jobprivacylog') {

                    that.listTitle = '简历隐私号';
                    url = url + '&a=getJobPrivacyLog';
                } else if (that.listType == 'jobsq') {

                    that.listTitle = '被投递岗位信息';
                    url = url + '&a=getJobSq';
                } else if (that.listType == 'jobtel') {

                    that.listTitle = '被拨号岗位信息';
                    url = url + '&a=getJobTel';
                } else if (that.listType == 'companytel') {

                    that.listTitle = '被拨号企业信息';
                    url = url + '&a=getComTel';
                }

                let params = JSON.parse(JSON.stringify(that.listSearch));
                params.usertype = 2;
                params.limit = 50;
                params.list = 1;
                httpPost(url, params).then(function (res) {
                    if (res.data.error == 0) {

                        let data = res.data.data;
                        that.listTableData = data.list;
                    }
                });

                that.listDrawer = true;
            },
            showDetail(scope) {
                let that = this;
                that.detailType = that.listType;
                if (that.detailType == 'comweblog') {

                    let ip = scope.row.ip.split('.');
                    that.detailIp = ip.join('_');
                } else if (that.detailType == 'jobsq' || that.detailType == 'jobtel') {

                    that.detailJobId = scope.row.job_id;
                } else if (that.detailType == 'jobsq') {

                    that.detailComId = scope.row.uid;
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

                that.dNameShow = that.dJobNameShow = that.dStateShow = that.dPortShow = that.dIpShow = that.dIpCityShow = that.dAllNumShow = that.dTimeShow = that.dCkShow = false;
                if (that.detailType != 'addjob' && that.detailType != 'refreshjob' && that.detailType != 'comloginlog' && that.detailType != 'comweblog') {
                    that.dNameShow = true;
                }
                if (that.detailType != 'lookresume' && that.detailType != 'downresume' && that.detailType != 'jobprivacylog' && that.detailType != 'comchatlog' && that.detailType != 'comloginlog' && that.detailType != 'comweblog' && that.detailType != 'downdel' && that.detailType != 'companytel') {
                    that.dJobNameShow = true;
                }
                if (that.detailType == 'notviewresume') {
                    that.dStateShow = true;
                }
                if (that.detailType == 'comloginlog') {
                    that.dPortShow = true;
                }
                if (that.detailType == 'lookresume' || that.detailType == 'comloginlog' || that.detailType == 'comweblog') {
                    that.dIpShow = true;
                    that.dIpCityShow = true;
                }
                if (that.detailType == 'comweblog') {
                    that.dAllNumShow = true;
                }
                if (that.detailType == 'comchatlog') {
                    that.dCkShow = true;
                }

                let params = JSON.parse(JSON.stringify(that.search));
                params.type = that.detailType;
                params.usertype = 2;
                params.page = that.currentPage;
                params.limit = that.limit;

                if (that.listType == 'comweblog') {
                    params.ip = that.detailIp;
                } else if (that.listType == 'jobsq' || that.listType == 'jobtel') {
                    params.job_id = that.detailJobId;
                } else if (that.listType == 'companytel') {
                    params.com_id = that.detailComId;
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
            toShow: function (url) {
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
                console.log(this.locktype);
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
            }
        },
    };

    function typeName(type) {
        if (type == 'comchatlog') {
            return '直聊人数';
        } else if (type == 'comloginlog') {
            return '登录次数';
        } else if (type == 'privacylog') {
            return '职位隐私号';
        } else if (type == 'comweblog') {
            return 'IP活跃次数';
        } else if (type == 'lookresume') {
            return '简历浏览';
        } else if (type == 'notviewresume') {
            return '投递简历未查看';
        } else if (type == 'downresume') {
            return '简历下载';
        } else if (type == 'yqms') {
            return '邀请面试';
        } else if (type == 'addjob') {
            return '发布职位';
        } else if (type == 'refreshjob') {
            return '刷新职位';
        } else if (type == 'sqjob') {
            return '收到简历';
        } else if (type == 'jobprivacylog') {
            return '简历隐私号';
        } else if (type == 'usersqdel') {
            return '删除简历投递记录';
        } else if (type == 'downdel') {
            return '删除简历下载记录';
        } else if (type == 'sqdel') {
            return '删除收到简历记录';
        } else if (type == 'jobsq') {
            return '被投递岗位';
        } else if (type == 'jobtel') {
            return '被拨号岗位';
        } else if (type == 'companytel') {
            return '被拨号企业';
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

    .centerCss {
        text-align: center;
    }

    .firstName {
        color: #409eff;
    }

    .ThirdNum {
        color: #f00;
        font-weight: bold;
    }
</style>