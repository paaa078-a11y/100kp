<template>
    <div class="moduleElHight moduleElHightMent" :style="simple ? '' : 'overflow-y: auto;'" style="height: initial;">
        <!-- 简化版我的客户头部 -->
        <template v-if="simple">
            <div class="moduleElSearchInf" style="background: #fff; overflow: hidden; position: relative; padding: 0 15px;">
                <div class="moduleElTabInpt" style="flex-wrap: wrap;">
                    <div class="moduleInptList moduleInptWidt">
                        <el-input size="small" @keyup.enter.native="search" placeholder="搜索关键词" v-model="keyword"
                            class="input-with-select" clearable>
                            <el-select v-model="ktype" slot="prepend" placeholder="请选择">
                                <el-option label="公司名" value="1"></el-option>
                                <el-option label="联系人" value="2"></el-option>
                                <el-option label="电话" value="3"></el-option>
                            </el-select>
                        </el-input>
                    </div>
                    <div class="moduleInptList" v-if="jttype == 'jxgj' || jttype == 'gyx'">
                        <el-select v-if="jttype == 'jxgj'" size="small" v-model="jtips" placeholder="提醒类型" clearable
                            @change="search">
                            <el-option label="即将释放" value="1"></el-option>
                            <el-option label="超期未成交" value="2"></el-option>
                            <el-option label="超期未跟进" value="3"></el-option>
                            <el-option label="下次跟进" value="4"></el-option>
                        </el-select>
                        <el-select v-if="jttype == 'gyx'" size="small" v-model="jtips" placeholder="提醒类型" clearable
                            @change="search">
                            <el-option label="触发权益提醒" value="1"></el-option>
                            <el-option label="登录未续费客户" value="2"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList">
                        <el-select size="small" v-model="rating" placeholder="会员类型" clearable @change="search">
                            <el-option v-for="(item, key) in ratingArr" :key="key" :label="item.name"
                                :value="item.id"></el-option>
                            <el-option label="过期会员" :value="-1"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" style="display: flex; align-items: center;">
                        <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                        <el-button class="" size="mini" type="primary" @click="addcustomer" icon="el-icon-document-add"
                            style="margin-left: 8px;">新增客户</el-button>
                    </div>
                </div>
            </div>
        </template>
        <!-- 完整版我的客户头部 -->
        <template v-else>
            <el-tabs style="background: #fff; overflow: hidden; position: relative; padding: 0 15px;  " v-model="tabName"
                :before-leave="tabBeforeLeave">
                <el-tab-pane label="全部客户" name="1"></el-tab-pane>
                <el-tab-pane label="急需跟进" name="2"></el-tab-pane>
                <el-tab-pane label="高意向用户" name="3"></el-tab-pane>
                <el-tab-pane label="活跃用户" name="4"></el-tab-pane>
                <el-tab-pane label="将到期用户" name="5"></el-tab-pane>
                <el-tab-pane label="潜在用户" name="6"></el-tab-pane>
                <el-tab-pane label="客户线索" name="7"></el-tab-pane>
                <el-tab-pane name="8">
                    <el-popover ref="popover" v-model="logpopshow" placement="bottom" width="400" trigger="manual">
                        <div class="tabsInfoFlex">
                            <div class="tabsIndispLis">
                                <div class="tabsIndispLis_a" @click="noticeShow()">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon1.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>权益提醒</span>
                                        <b>用户套餐不足</b>
                                    </div>
                                </div>
                                <div class="tabsIndispLis_a" @click="abnormalShow('1')">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon2.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>收到简历</span>
                                        <b>收到简历记录</b>
                                    </div>
                                </div>
                                <div class="tabsIndispLis_a" @click="abnormalShow('2')">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon3.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>简历未查看</span>
                                        <b>企业未查看统计</b>
                                    </div>
                                </div>
                                <div class="tabsIndispLis_a" @click="abnormalShow('3')">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon4.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>简历下载</span>
                                        <b>企业下载数统计</b>
                                    </div>
                                </div>
                                <div class="tabsIndispLis_a" @click="abnormalShow('4')">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon5.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>登录次数</span>
                                        <b>企业登陆平台次数</b>
                                    </div>
                                </div>
                                <div class="tabsIndispLis_a" @click="abnormalShow('5')">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon6.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>被投岗位</span>
                                        <b>岗位被投递统计</b>
                                    </div>
                                </div>
                                <div class="tabsIndispLis_a" @click="abnormalShow('6')">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon7.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>被拨号岗位</span>
                                        <b>岗位被拨号记录</b>
                                    </div>
                                </div>
                                <div class="tabsIndispLis_a" @click="abnormalShow('7')">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon8.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>被拨号企业</span>
                                        <b>企业被拨号记录</b>
                                    </div>
                                </div>
                                <div class="tabsIndispLis_a" @click="abnormalShow('8')">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon9.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>删除简历投递</span>
                                        <b>个人投递后删除投递记录</b>
                                    </div>
                                </div>
                                <div class="tabsIndispLis_a" @click="abnormalShow('9')">
                                    <div class="tabsIndisImg">
                                        <img src="../../images/fxicon10.png">
                                    </div>
                                    <div class="tabsIndisNam">
                                        <span>删除简历下载</span>
                                        <b>企业删除下载记录</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </el-popover>
                    <span slot="label" v-popover:popover>客户行为分析<i
                            :class="logpopshow ? 'el-icon-arrow-up' : 'el-icon-arrow-down'"></i></span>
                </el-tab-pane>
            </el-tabs>

            <div class="moduleElSearchInf" style="background: #fff; overflow: hidden; position: relative; padding: 0 15px;">
                <div class="moduleElTabInpt" style="flex-wrap: wrap;">
                    <div class="moduleInptList moduleInptWidt">
                        <el-input size="small" placeholder="搜索关键词" v-model="keyword" @keyup.enter.native="search"
                            class="input-with-select" clearable>
                            <el-select v-model="ktype" slot="prepend" placeholder="请选择">
                                <el-option label="公司名" value="1"></el-option>
                                <el-option label="联系人" value="2"></el-option>
                                <el-option label="电话" value="3"></el-option>
                                <el-option label="UID" value="4"></el-option>
                                <el-option label="企业地址" value="5"></el-option>
                            </el-select>
                        </el-input>
                    </div>
                    <div class="moduleInptList">
                        <el-select v-if="adminUserList.length > 0" size="small" v-model="crm_uid" placeholder="客户经理"
                            clearable @change="crmUidChange">
                            <el-option label="全部下属" value="all"></el-option>
                            <el-option v-for="item in adminUserList" :key="item.uid" :label="item.name"
                                :value="item.uid"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList">
                        <el-select size="small" v-model="crmType" placeholder="客户等级" clearable @change="search">
                            <el-option v-for="item in client_type" :key="item.val" :label="item.name"
                                :value="item.val"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList">
                        <el-select size="small" v-model="crmStatus" placeholder="客户标签" clearable @change="search">
                            <el-option v-for="item in client_status" :key="item.val" :label="item.name"
                                :value="item.val"></el-option>
                        </el-select>
                    </div>
                    <div v-if="jttype != 'hy'" class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="rating" placeholder="会员类型" clearable @change="search">
                            <el-option v-for="(item, key) in ratingArr" :key="key" :label="item.name"
                                :value="item.id"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="time_type" placeholder="注册时间" clearable @change="search">
                            <el-option label="注册时间" value="1"></el-option>
                            <el-option v-if="jttype != 'gyx' && jttype != 'hy' && jttype != 'qz'" label="登陆时间"
                                value="2"></el-option>
                        </el-select>
                    </div>
                    <div v-if="jttype != 'gyx' && jttype != 'hy' && jttype != 'qz' && time_type == '2'"
                        class="moduleInptList">
                        <el-date-picker size="small" :picker-options="pickerOptions" v-model="logintime" type="daterange"
                            value-format="yyyy-MM-dd" range-separator="至" start-placeholder="登陆开始" end-placeholder="结束日期"
                            style="width: 230px;" @change="search"></el-date-picker>
                    </div>
                    <div v-if="time_type == '1'" class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-date-picker size="small" :picker-options="pickerOptions" v-model="regtime" type="daterange"
                            value-format="yyyy-MM-dd" range-separator="至" start-placeholder="注册开始" end-placeholder="结束日期"
                            style="width: 230px;" @change="search"></el-date-picker>
                    </div>
                    <div v-if="jttype != 'hy' && jttype != 'jdq'" class="moduleInptList"
                        :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="vipetime" placeholder="到期时间" clearable @change="search">
                            <el-option v-for="(item, key) in vip_etimearr" :key="key" :label="item"
                                :value="key"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="yyzz_status" placeholder="资质认证" clearable @change="search">
                            <el-option v-for="(item, key) in yyzz_statusarr" :key="key" :label="item"
                                :value="key"></el-option>
                        </el-select>
                    </div>
                    <div v-if="jttype != 'jxgj'" class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="last_ftime" placeholder="上次跟进" clearable @change="search">
                            <el-option v-for="(item, key) in last_ftimearr" :key="key" :label="item"
                                :value="key"></el-option>
                        </el-select>
                    </div>
                    <div v-if="jttype != 'jxgj'" class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="next_ftime" placeholder="计划跟进" clearable @change="search">
                            <el-option v-for="(item, key) in next_ftimearr" :key="key" :label="item"
                                :value="key"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="hasjob" placeholder="拥有职位" clearable @change="search">
                            <el-option v-for="(item, key) in has_jobarr" :key="key" :label="item" :value="key"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
                        <el-select size="small" v-model="fact_status" placeholder="实地核验" clearable @change="search">
                            <el-option label="是" value="1"></el-option>
                            <el-option label="否" value="2"></el-option>
                        </el-select>
                    </div>
                    <div class="moduleInptList" style="display: flex; align-items: center;">
                        <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                        <el-button class="" size="mini" type="primary" @click="addcustomer" icon="el-icon-document-add"
                            style="margin-left: 8px;">新增客户</el-button>
                    </div>
                    <div class="moduleInptList" :class="{ 'searchbutnKai': seachbutn }">
                        <el-button type="primary" class="zhankai" @click="seachbutn = !seachbutn, tableHig = !tableHig"
                            size="mini" plain>展开<i class="el-icon-arrow-down el-icon--right"></i></el-button>
                        <el-button type="primary" class="shouqi" @click="seachbutn = !seachbutn, tableHig = !tableHig"
                            size="mini" plain>合并<i class="el-icon-arrow-up el-icon--right"></i></el-button>
                    </div>
                </div>
            </div>
        </template>
        <div v-if="!simple" class="kehuquanbuSubs" style="padding:0px 15px 0px 15px">
            <div class="admin_datatip">
                <i class="el-icon-document"></i>
                当前客户：
                <div class="admin_datatip_n">
                    <el-link type="primary" :underline="false" @click="rstatusChange('')">{{ numarr.companyAllNum
                    }}</el-link>位
                </div>
                <div class="admin_datatip_n">未审核：
                    <el-link type="primary" :underline="false" @click="rstatusChange(0)">{{ numarr.companyStatusNum1
                    }}</el-link>
                </div>
                <div class="admin_datatip_n">未通过：
                    <el-link type="primary" :underline="false" @click="rstatusChange(3)">{{ numarr.companyStatusNum2
                    }}</el-link>
                </div>
                <div class="admin_datatip_n">已锁定：
                    <el-link type="primary" :underline="false" @click="rstatusChange(2)">{{ numarr.companyStatusNum3
                    }}</el-link>
                </div>
                <div class="admin_datatip_n">暂停招聘：
                    <el-link type="primary" :underline="false" @click="rstatusChange(4)">{{ numarr.companyStatusNum4
                    }}</el-link>
                </div>
            </div>
        </div>
        <div :class="{ 'moduleElTable': true, 'moduleElMoreInt': !simple, 'moduleElMoreHig': tableHig }"
            style=" width: calc(100% - 30px);  padding: 0 15px; background: #fff;">
            <el-table ref="table" :data="tableData" stripe style="width: 100%; border: 1px solid #ebeef5;"
                @mousedown.native="mouseDownHandler" @mouseup.native="mouseUpHandler" @mousemove.native="mouseMoveHandler"
                v-loading="list_loading" @selection-change="selectionChange"
                :default-sort="{ prop: 'id', order: 'descending' }" :empty-text="emptytext" @sort-change="sortChange"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="uid" label="UID" width="70" sortable="custom"></el-table-column>
                <el-table-column label="客户名称" min-width="210">
                    <template slot-scope="scope">
                        <div class="scopeSpanse">
                            <el-link type=" " @click="openComInfo(scope)">{{ scope.row.name }}</el-link>
                            <template v-if="scope.row.r_status == 0">
                                <span class="shzt" v-if="crm_uid == '' && source != 'jobtai'"
                                    @click="comaudit(scope.row.uid)">未审核</span>
                                <span class="shzt" v-else>未审核</span>
                            </template>
                            <span class="shzt shztsd" v-else-if="scope.row.r_status == 2">已锁定</span>
                            <span class="shzt shztwtg" v-else-if="scope.row.r_status == 3">未通过</span>
                            <span class="shzt shztyzt" v-else-if="scope.row.r_status == 4">已暂停</span>
                            <div>{{ scope.row.username }}</div>
                        </div>
                        <div class="moduCrmIcon">
                            <comcert :key="scope.row.uid" :row="scope.row" :config="config" @close-update="closeComcert">
                            </comcert>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="等级/到期" min-width="130" prop="vipetime" sortable="custom">
                    <template slot-scope="scope">
                        <div class=" ">
                            <span v-if="scope.row.isvip == 1">{{ scope.row.rating_name }}</span>
                            <span v-else>{{ scope.row.oldrating_name ? scope.row.oldrating_name :
                                scope.row.rating_name }}</span>
                            <div>
                                <span :style="scope.row.vipetime_status == '2' ? 'color:#ff2b00;' : ''">{{
                                    scope.row.vipetime_n
                                }}</span>
                                <div v-if="scope.row.r_status != '4' && scope.row.vipetime_status != '0'"
                                    :style="scope.row.vipetime_status == '2' ? 'color:#ff2b00;' : 'color:#409eff;'"> {{
                                        scope.row.vipDay }} </div>
                                <div v-if="scope.row.r_status == '4'" style="color:#ff2b00;">{{ scope.row.ztDay }}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="联系信息" min-width="150">
                    <template slot-scope="scope">
                        <div class=" ">
                            <span>{{ scope.row.linkman }}</span>
                            <div v-if="scope.row.linktel">{{ scope.row.linktel }}</div>
                            <div class="gsd">{{ scope.row.job_city_one }}{{ scope.row.job_city_two }}{{
                                scope.row.job_city_three }}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="注册/登录" min-width="120" prop="login_date" sortable="custom">
                    <template slot-scope="scope">
                        <div class=" ">
                            <span>{{ scope.row.reg_date_n }}</span>
                            <div v-html="scope.row.login_date_n"></div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="职位数" min-width="80">
                    <template slot-scope="scope">
                        <div class=" ">
                            <span>总数：<el-link type="primary" @click="tocomjob({ uid: scope.row.uid })">{{ scope.row.jobnum
                            }}</el-link></span>
                            <div>在招：<el-link type="primary" @click="tocomjob({ uid: scope.row.uid, status: 2 })">{{
                                scope.row.zz_jobnum }}</el-link></div>
                            <el-button @click="addjob(scope.row.uid)" style="padding: 0;" type="text">添加</el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column v-if="jttype == 'jxgj' || jttype == 'gyx'" label="提醒/归属/最后跟进" width="200" prop="f_time"
                    sortable="custom">
                    <template slot-scope="scope">
                        <div class=" ">
                            <div class=""><span class="admin_state5">{{ scope.row.jtips }}</span></div>
                            <div class="gsd"> {{ scope.row.crm_name }}</div>
                            <el-popover placement="left" trigger="hover" v-if="scope.row.c_time">
                                <div style='overflow: hidden;'>
                                    <div
                                        style='font-size:16px;font-weight: bold;color:#333;line-height:40px;background:#f9f9f9;padding-left:10px;'>
                                        最近跟进记录</div>
                                    <div>
                                        <div
                                            style='overflow: hidden;padding-left:10px;line-height:32px;font-size:14px;display: flex;align-items: center; margin:5px 0;'>
                                            <p style='color:#666;'>{{ scope.row.c_name }}</p>
                                            <span
                                                style='color:#666; background: rgba(120, 186, 50, 0.2); color:#78BA32;border-radius: 5px;display: block;margin-left:15px;padding:0 10px;'>{{
                                                    scope.row.c_type }}</span>
                                        </div>
                                        <div
                                            style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
                                            跟进时间：{{ scope.row.c_time }}</div>
                                        <div v-if="scope.row.c_desc"
                                            style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'
                                            v-html="'跟进内容：' + scope.row.c_desc"></div>
                                        <div v-if="scope.row.crm_pic_arr"
                                            style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
                                            <img v-for="(pic, key) in scope.row.crm_pic_arr" :key="key" :src="pic"
                                                width='40' height='40' />
                                        </div>
                                    </div>
                                </div>
                                <div slot="reference" class="gsd">{{ scope.row.lastTime }}</div>
                            </el-popover>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column v-else label="业务员/跟进时间" width="200" prop="f_time" sortable="custom">
                    <template slot-scope="scope">
                        <div class=" ">
                            <div class=""> {{ scope.row.crm_name }}</div>
                            <el-popover placement="left" trigger="hover" v-if="scope.row.c_time">
                                <div style='overflow: hidden;'>
                                    <div
                                        style='font-size:16px;font-weight: bold;color:#333;line-height:40px;background:#f9f9f9;padding-left:10px;'>
                                        最近跟进记录</div>
                                    <div>
                                        <div
                                            style='overflow: hidden;padding-left:10px;line-height:32px;font-size:14px;display: flex;align-items: center; margin:5px 0;'>
                                            <p style='color:#666;'>{{ scope.row.c_name }}</p>
                                            <span
                                                style='color:#666; background: rgba(120, 186, 50, 0.2); color:#78BA32;border-radius: 5px;display: block;margin-left:15px;padding:0 10px;'>{{
                                                    scope.row.c_type }}</span>
                                        </div>
                                        <div
                                            style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
                                            跟进时间：{{ scope.row.c_time }}</div>
                                        <div v-if="scope.row.c_desc"
                                            style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'
                                            v-html="'跟进内容：' + scope.row.c_desc"></div>
                                        <div v-if="scope.row.crm_pic_arr"
                                            style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
                                            <img v-for="(pic, key) in scope.row.crm_pic_arr" :key="key" :src="pic"
                                                width='40' height='40' />
                                        </div>
                                    </div>
                                </div>
                                <div slot="reference" class="gsd"><i class="el-icon-time"></i> {{ scope.row.lastTime }}
                                </div>
                            </el-popover>
                            <el-tooltip class="item" effect="dark" :content="'计划于' + scope.row.nextTime + '再次跟进'"
                                placement="left" v-if="scope.row.nextTime">
                                <div class="gsd" style="color:#f00"><i class="el-icon-alarm-clock"></i> {{
                                    scope.row.nextTime }}</div>
                            </el-tooltip>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="等级/标签/领取" width="180" prop="crm_time" sortable="custom">
                    <template slot-scope="scope">
                        <div class=" ">
                            <div>等级：{{ scope.row.crm_type }}</div>
                            <div>标签：{{ scope.row.crm_status }}</div>
                            <div>领取：{{ scope.row.crm_time_n }}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="160">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" v-if="crm_uid == ''" @click="addfollow(scope.row)">写跟进</el-button>
                            <el-button size="mini" v-if="crm_uid == ''" @click="contact_customer(scope.row)">打电话</el-button>
                            <el-button size="mini" style="padding: 6px 18px;" @click="openPersona(scope)">画像</el-button>
                            <el-button size="mini" style="padding: 6px 18px;" @click="openComInfo(scope)">管理</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging" style="height: initial; flex-wrap: wrap; padding-top: 10px;">
            <div class="modulecz" style="width:100%;">
                <el-checkbox v-model="allchecked" @change="allcheckChange">全选</el-checkbox>
                <el-button size="mini" @click="deliver">转交</el-button>
                <el-button size="mini" @click="giveup">放弃</el-button>
                <el-button size="mini" @click="statustype('status')">客户标签</el-button>
                <el-button size="mini" @click="statustype('type')">客户等级</el-button>
                <el-button v-if="tabName == 7" size="mini" @click="clueMyCustomer">转企业库</el-button>
            </div>
            <div class="modulePagNum" style="padding-top: 8px;">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total"
                    layout="total, sizes, prev, pager, next, jumper"></el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="转交客户" :visible.sync="deliverShow" :with-header="true" append-to-body :show-close="true"
                width="600px">
                <deliverbox @close-update="closeDeliverBox" :cuid="cuid"></deliverbox>
            </el-dialog>
        </div>
        <div class="modluDrawer">
            <el-dialog title="放弃客户" :visible.sync="giveupShow" :with-header="true" append-to-body :show-close="true"
                width="600px">
                <giveupbox @close-update="closeGiveupBox" :cuid="cuid"></giveupbox>
            </el-dialog>
        </div>
        <div class="modluDrawer">
            <el-dialog :title="isst == 'status' ? '客户标签' : '客户等级'" :visible.sync="statustypeShow" :with-header="true"
                append-to-body :show-close="true" width="600px">
                <statustype @close-update="closeStBox" :cuid="cuid" :isst="isst"></statustype>
            </el-dialog>
        </div>
        <!--录客户-->
        <div class="modluDrawer">
            <el-drawer title="录入客户" :visible.sync="addcustomer_show" append-to-body :wrapper-closable="false" size="60%">
                <addcustomer @close-update="closeAddcustomer" v-if="addcustomer_show"></addcustomer>
            </el-drawer>
        </div>
        <!--写跟进-->
        <el-drawer title="联系跟进" :close-on-press-escape="false" :wrapper-closable="false" :visible.sync="follow_show"
            append-to-body size="35%">
            <crmnewfollow :uid_v="follow_uid" :crm_status_v="follow_status" :crm_type_v="follow_type" :name_v="follow_name"
                :calllog_id="follow_calllog_id" @close-update="closeAddfollow" v-if="follow_show"></crmnewfollow>
        </el-drawer>
        <!--权益提醒-->
        <div class="modluDrawer">
            <el-drawer title="权益提醒" :visible.sync="notice_show" append-to-body :show-close="true" :with-header="true"
                size="80%">
                <div class="noticequanyi">
                    <notice :crm_uid="crm_uid" v-if="notice_show"></notice>
                </div>
            </el-drawer>
        </div>
        <!--行为分析日志-->
        <div class="modluDrawer">
            <el-drawer :visible.sync="abnormal_show" append-to-body :show-close="true" :with-header="true" size="80%">
                <div slot="title">
                    <div class="yj_chlose" style="margin-bottom: 5px;">
                        <span style="font-weight: bold; font-size: 18px;">{{ abnormal_title }}</span>
                        <span style="margin-left: 36px;">{{ abnormal_desc }}</span>
                    </div>
                </div>
                <div class="noticequanyi">
                    <abnormal :time_v='abnormal_time' :type_v='abnormal_type' :crm_uid="crm_uid" v-if="abnormal_show">
                    </abnormal>
                </div>
            </el-drawer>
        </div>
        <!--弹窗：客户管理-->
        <div class="modluDrawer">
            <el-drawer title="客户管理" :visible.sync="comInfoDrawer" :append-to-body="true" size="90%">
                <div class="modluDrawerKehuguanli">
                    <com_info ref="comInfo" :cuid="comuid" @child-event="closeComInfo" v-if="comInfoDrawer"></com_info>
                </div>
            </el-drawer>
        </div>
        <!--职位管理-->
        <el-drawer title="职位信息" :visible.sync="drawerAllJob" append-to-body size="95%">
            <div class="jobAllSJudhsk">
                <joball ref="jobAll" style="margin-left:10px;" :uid="all_job_uid" :status="all_job_status" simple
                    v-if="drawerAllJob"></joball>
            </div>
        </el-drawer>
        <!--新增职位提示-->
        <el-drawer title="新增职位" :close-on-press-escape="false" :wrapper-closable="false" :visible.sync="drawerAddJob"
            append-to-body size="60%">
            <addjob ref="jobadd" style="margin-right:10px;" :comid="curr_comid"></addjob>
        </el-drawer>
        <!-- 客户画像 -->
        <div class="modluDrawer">
            <el-drawer title="企业画像" :close-on-press-escape="false" :visible.sync="personaShow" :append-to-body="true"
                size="60%">
                <huaxiang :comid="comuid" v-if="personaShow"></huaxiang>
            </el-drawer>
        </div>
        <!--企业详情审核 ---------------------------------------------------------------------->
        <el-drawer title="企业审核" :visible.sync="comdrawersh" append-to-body :modal-append-to-body="false"
            :close-on-click-modal="false" size="80%">
            <div class="shbox" v-if="comdrawersh">
                <div class="shinfo">
                    <div class="shcomname">{{ curr_com.name }}
                        <el-tag type="danger" size="mini">{{ curr_com.rating_name }}</el-tag>
                    </div>
                    <div class="sh_zwsz_add">
                        <span v-if="curr_com.linkman">联系人：{{ curr_com.linkman }}<span v-if="curr_com.linkjob">({{
                            curr_com.linkjob }})</span></span>
                        <span class="shcomtel_n" v-if="curr_com.linktel">联系手机：{{ curr_com.linktel }}
                            <span>{{ curr_com.infostatus == 1 ? '（公开）' : '（不公开）' }}</span>
                            <span v-if="curr_com.moblie_address">归属地：{{ curr_com.moblie_address }}</span>
                        </span>
                        <span class="shcomtel_n" v-if="curr_com.linkphone">固定电话：{{ curr_com.linkphone }}</span>
                        <span v-if="curr_com.crm_uid != '0'">业务员：{{ curr_com.crm_name }}</span>
                    </div>
                    <div class="shcomtel">
                        <span v-if="curr_com.reg_date_n">注册时间：{{ curr_com.reg_date_n }}</span>
                        <span v-if="curr_com.login_date_n" class="shcomtel_n">最近登录时间：{{ curr_com.login_date_n }} </span>
                        <span v-if="curr_com.login_ip">IP：{{ curr_com.login_ip }}</span>
                    </div>
                    <div class="shshowall">
                        <div class="shshow">
                            <div class="shshow_tit"><i class="el-icon-office-building"></i> 基本资料</div>
                            <div class="shshow_p">
                                <div v-if="curr_com.welfare_n">企业福利：
                                    <el-tag v-for="(item, key) in curr_com.welfare_n" :key="key" size="mini">{{ item
                                    }}</el-tag>
                                </div>
                                <div v-if="curr_com.hy_n">从事行业：{{ curr_com.hy_n }}</div>
                                <div v-if="curr_com.pr_n">企业性质：{{ curr_com.pr_n }}</div>
                                <div v-if="curr_com.mun_n">企业规模：{{ curr_com.mun_n }}</div>
                                <div v-if="curr_com.provinceid">企业地址：{{ curr_com.job_city_one }} {{ curr_com.job_city_two }}
                                    {{ curr_com.job_city_three }} {{ curr_com.address }}
                                </div>
                                <div v-if="curr_com.content" v-html="curr_com.content"></div>
                            </div>
                            <div class="shshow_tit" v-if="curr_com.job_list.length > 0"><i class="el-icon-suitcase-1"></i>
                                招聘岗位
                            </div>
                            <ul class="shshow_joblist" v-if="curr_com.job_list.length > 0">
                                <li v-for="(item, index) in curr_com.job_list" :key="index">
                                    <el-link type="primary" :href="item.joburl" target="_blank" :underline="false">
                                        <div class="shshow_job">{{ item.name }}</div>
                                    </el-link>
                                    <div class="shshow_jobinfo">
                                        <span class="shshow_jobxz">{{ item.job_salary }}</span>
                                        <span class="shshow_line" v-if="item.job_exp">|</span> {{ item.job_exp == '不限' ?
                                            '不限经验' : item.job_exp }}
                                        <span class="shshow_line" v-if="item.job_edu">|</span> {{ item.job_exp == '不限' ?
                                            '不限学历' : item.job_edu }}
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="shcz">
                            <div class="wxsettip_small">企业审核</div>
                            <div v-if="islock" style="margin-bottom: 10px;">
                                <template>
                                    <el-radio v-model="member_status" label="1">正常</el-radio>
                                    <el-radio v-model="member_status" label="2">锁定</el-radio>
                                </template>
                                <div class="wxsettip_small ">锁定状态说明</div>
                                <el-alert :title="lockdesc" type="warning" show-icon :closable="false">
                                </el-alert>
                            </div>
                            <div v-if="member_status != '2'">
                                <template>
                                    <el-radio v-model="rr_status" label="0">未审核</el-radio>
                                    <el-radio v-model="rr_status" label="1">通过</el-radio>
                                    <el-radio v-model="rr_status" label="3">未通过</el-radio>
                                </template>
                                <div class="wxsettip_small ">审核状态说明</div>
                                <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="statusbody">
                                </el-input>
                            </div>
                            <div class="shczbth">
                                <el-button type="primary" @click="comSh(1)">提 交</el-button>
                            </div>
                            <div class=" shczbth" v-if="snum > '0' && member_status != '2'">
                                <el-button type="primary" @click="comSh(2)" plain>提交，并审核下一个</el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </el-drawer>


    </div>
</template>

<script>
module.exports = {
    props: {
        simple: { // 简化我的客户页面，为true没有头部和tab切换，搜索栏仅保留公司名称搜索
            type: Boolean,
            default: false
        },
        paramdata: {
            type: Object,
            default: null
        },
        source: { // 加载来源
            type: String,
            default: ''
        },
        source_data: { // 来源数据
            type: Object,
            default: null
        },
    },
    watch: {
        paramdata: {
            handler(val, oldVal) {
                if (val) {
                    for (let i in val) {
                        this[i] = val[i]
                    }
                    this.resetsearch();
                    this.getList();
                }
            },
            // immediate: true,
            deep: true
        },
        source_data: {
            handler(val, oldVal) {
                this.resetsearch();
                this.sourceDataHandle();
                this.getList();
            }
        }
    },
    data: function () {
        return {
            time_type: '1',
            mouseFlag: false,
            mouseOffset: 0,
            crm_uid: '',
            adminUserList: [],
            orgUid: [],

            emptytext: '暂无数据',
            config: {},
            tabName: '1',
            logpopshow: false,
            client_type: [],
            client_status: [],
            ratingArr: [],
            vip_etimearr: [],
            yyzz_statusarr: [],
            last_ftimearr: [],
            next_ftimearr: [],
            has_jobarr: [],
            login_datearr: [],
            numarr: [],
            clue: '',
            personaShow: false,
            loginDate: '7',
            crmType: '',
            crmStatus: '',
            rating: '',
            vipetime: '',
            yyzz_status: '',
            last_ftime: '',
            next_ftime: '',
            hasjob: '',
            fact_status:'',
            logintime: [],
            regtime: [],
            keyword: '',
            ktype: '1',
            r_status: '',
            lastF: '',
            vipstime: '',
            jtmodule: '',
            jttype: '',
            jtips: '',

            seachbutn: true,
            tableHig: false,
            tableData: [],
            total: 0,
            limit: 0,
            currentPage: 1,
            prevPage: 0,
            page_sizes: [],

            list_loading: false,

            allchecked: false,
            choosedata: [],

            addcustomer_show: false,
            deliverShow: false,
            giveupShow: false,
            statustypeShow: false,
            isst: '',
            cuid: '',

            follow_show: false,
            follow_uid: '',
            follow_status: '',
            follow_type: '',
            follow_name: '',
            follow_calllog_id: '',

            is_outcall: false,

            notice_show: false,

            abnormal_show: false,
            abnormal_title: '',
            abnormal_desc: '',
            abnormal_type: '',
            abnormal_time: '',

            comInfoDrawer: false,
            comuid: '',

            drawerAllJob: false,
            all_job_uid: '',
            all_job_status: '',

            drawerAddJob: false,
            curr_comid: '',

            pickerOptions: {
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
                        const end = new Date(new Date().setHours(0, 0, 0) + (0 - new Date().getDay()) * 24 * 60 * 60 * 1000);
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


            comdrawersh: false,
            curr_com: { uid: '' },
            member_status: '',
            rr_status: '',
            snum: 0,
            statusbody: '',
        }
    },
    components: {
        'deliverbox': httpVueLoader('./deliverbox.vue'),
        'giveupbox': httpVueLoader('./giveupbox.vue'),
        'statustype': httpVueLoader('./statustype.vue'),
        'addcustomer': httpVueLoader('./addcustomer.vue'),
        'crmnewfollow': httpVueLoader('./crmnewfollow.vue'),
        'notice': httpVueLoader('./notice.vue'),
        'abnormal': httpVueLoader('./abnormal.vue'),
        'com_info': httpVueLoader('./comInfo.vue'),
        'comcert': httpVueLoader('./comcert.vue'),
        'huaxiang': httpVueLoader('./huaxiang.vue'),
        'joball': httpVueLoader('../../user/company/component/joball.vue'),
        'addjob': httpVueLoader('../../user/company/component/addjob.vue'),
    },
    mounted() {
        let paramdata = this.paramdata
        if (paramdata) {
            for (let i in paramdata) {
                this[i] = paramdata[i]
            }
        }
        this.sourceDataHandle();
        this.getStaticData();
        this.getList();
        this.getCustomerNum();
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



        // 客户经理选择
        crmUidChange: function () {
            this.search();
            this.getCustomerNum();
        },
        // 新增职位
        addjob: function (id) {
            var that = this
            this.curr_comid = id
            this.drawerAddJob = true
            this.$nextTick(function () {
                that.$refs.jobadd.edit()
            })
        },
        tabBeforeLeave: function (n, o) {

            var that = this;

            if (n == '8') {
                this.logpopshow = !this.logpopshow;
                return false;
            }

            this.resetsearch()

            switch (n) {
                case '1':
                    that.jtmodule = '';
                    that.jttype = '';
                    break;
                case '2':
                    that.jtmodule = 'mykh';
                    that.jttype = 'jxgj';
                    break;
                case '3':
                    that.jtmodule = 'mykh';
                    that.jttype = 'gyx';
                    break;
                case '4':
                    that.jtmodule = 'mykh';
                    that.jttype = 'hy';
                    break;
                case '5':
                    that.jtmodule = 'mykh';
                    that.jttype = 'jdq';
                    break;
                case '6':
                    that.jtmodule = 'mykh';
                    that.jttype = 'qz';
                    break;
                case '7':
                    that.clue = 2;
                    break;
                case '8':
                    return false;
                    break;
                default:
                    that.tabName = '1';
            }
            this.getList();
        },

        resetsearch: function () {
            this.logpopshow = false;
            this.clue = '';
            this.loginDate = '7';
            this.crmType = '';
            this.crmStatus = '';
            this.rating = '';
            this.vipetime = '';
            this.yyzz_status = '';
            this.last_ftime = '';
            this.next_ftime = '';
            this.hasjob = '';
            this.fact_status='';
            this.logintime = [];
            this.regtime = [];
            this.keyword = '';
            this.ktype = '1';
            this.r_status = '';
            this.lastF = '';
            this.vipstime = '';
            this.tableData = [];
            this.total = 0;
            this.limit = 0;
            this.currentPage = 1;
            this.page_sizes = [];
            this.jtips = '';
        },
        async getList() {

            let that = this;
            let params = {
                page: that.currentPage,
                limit: that.limit,
                t: that.sort_t,
                order: that.order,
                keyword: that.keyword,
                loginDate: that.loginDate,
                crmType: that.crmType,
                crm_status: that.crmStatus,
                clue: that.clue,
                rating: that.rating,
                vipetime: that.vipetime,
                yyzz_status: that.yyzz_status,
                lastFtime: that.last_ftime,
                nextFtime: that.next_ftime,
                hasJob: that.hasjob,
                fact_status:that.fact_status,
                ktype: that.ktype,
                r_status: that.r_status,
                lastF: that.lastF,
                vipstime: that.vipstime
            }

            if (this.crm_uid != '') {
                params.crm_uid = this.crm_uid;
                if (this.crm_uid == 'all') {

                    params.orgUids = this.orgUid.join(',');
                }
            }

            if (this.time_type == '2' && this.logintime && this.logintime.length > 0) {
                params.logintime_s = this.logintime[0];
                params.logintime_e = this.logintime[1];
            }
            if (this.time_type == '1' && this.regtime && this.regtime.length > 0) {
                params.regtime_s = this.regtime[0];
                params.regtime_e = this.regtime[1];
            }
            if (this.source) {
                params.source = this.source;
                // 工作台跳转 (我的客户-mykh、我的客户统计-mykhtj)
                if (this.source == 'jobtai' && this.source_data && this.source_data.module && this.source_data.type) {

                    params.jtmodule = this.source_data.module;
                    params.jttype = this.source_data.type;
                    this.jttype = params.jttype;
                }
            } else {
                params.jtmodule = this.jtmodule;
                params.jttype = this.jttype;
                params.source = 'myCustomer';
            }
            if (this.jtips) {
                params.jtips = this.jtips;
            }

            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=crm&c=crm_my_customer', params).then((result) => {
                this.list_loading = false;
                var res = result.data;

                if (res.error == 0) {
                    that.tableData = res.data.list;
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
        getStaticData: function () {
            var that = this
            let params = {};
            if (this.source) {
                params.source = this.source;
            }
            httpPost('m=crm&c=crm_my_customer&a=getStaticData', params, { hideloading: true }).then((result) => {
                var res = result.data;
                if (res.error == 0) {
                    that.config = res.data.config;
                    that.is_outcall = res.data.is_outcall;
                    that.client_type = res.data.client_type;
                    that.client_status = res.data.client_status;
                    that.ratingArr = res.data.ratingArr;
                    that.yyzz_statusarr = res.data.yyzz_statusarr;
                    that.vip_etimearr = res.data.vip_etimearr;
                    that.last_ftimearr = res.data.last_ftimearr;
                    that.next_ftimearr = res.data.next_ftimearr;
                    that.has_jobarr = res.data.has_jobarr;
                    that.login_datearr = res.data.login_datearr;
                    that.adminUserList = res.data.adminUserList;
                    that.adminUserList.forEach(function (item) {
                        that.orgUid.push(item.uid);
                    })
                }
            });
        },
        getCustomerNum: function () {
            var that = this
            let params = {};
            if (this.crm_uid) {
                params.crm_uid = this.crm_uid;
                if (that.crm_uid == 'all') {
                    params.orgUids = this.orgUid.join(',');
                }
            }
            httpPost('m=crm&c=crm_my_customer&a=getCustomerNum', params, { hideloading: true }).then((result) => {
                var res = result.data;
                if (res.error == 0) {
                    that.numarr = res.data.numarr;
                }
            });
        },
        rstatusChange: function (r_status) {
            this.resetsearch();
            this.r_status = r_status;
            this.getList();
        },
        ratChange: function (rid, day, vipstime) {
            this.resetsearch();
            this.rating = rid;
            this.lastF = day;
            this.vipstime = vipstime;
            this.getList();
        },
        reset: function () {
            this.resetsearch();
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
            if (e !== null) {
                this.time = '';
            }
        },
        dateSelectChange: function (e) {
            if (e != '') {
                this.daterange = [];
            }
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
        deliver: function () {
            var _this = this;
            var idarr = [];

            if (this.choosedata.length > 0) {
                for (let i in this.choosedata) {
                    idarr.push(this.choosedata[i].uid);
                }
            } else {
                message.error('请选择要转交的客户！');
                return;
            }
            this.cuid = idarr.join(',');
            this.deliverShow = true;
        },
        closeDeliverBox: function () {
            this.deliverShow = false;
            this.getList();
        },
        giveup: function () {
            var _this = this;
            var idarr = [];

            if (this.choosedata.length > 0) {
                for (let i in this.choosedata) {
                    idarr.push(this.choosedata[i].uid);
                }
            } else {
                message.error('请选择要放弃的客户！');
                return;
            }
            this.cuid = idarr.join(',');
            this.giveupShow = true;
        },
        closeGiveupBox: function () {
            this.giveupShow = false;
            this.getList();
        },
        statustype: function (isst) {

            var _this = this;
            var idarr = [];

            if (this.choosedata.length > 0) {
                for (let i in this.choosedata) {
                    idarr.push(this.choosedata[i].uid);
                }
            } else {
                message.error('请选择要操作的客户！');
                return;
            }
            this.cuid = idarr.join(',');
            this.isst = isst;
            this.statustypeShow = true;
        },
        closeStBox: function () {
            this.statustypeShow = false;
            this.getList();
        },
        clueMyCustomer: function () {

            let _this = this;
            if (_this.choosedata.length == 0) {
                message.error('请选择要转库的线索信息！');
                return;
            } else {
                let idArr = [];
                for (let i in this.choosedata) {
                    idArr.push(this.choosedata[i].uid);
                }
                let params = {};
                params.uids = idArr.join(',');
                httpPost('m=crm&c=crm_my_customer&a=clueCustomer', params, { hideloading: true }).then((result) => {
                    var res = result.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            _this.getList();
                        });
                    } else {
                        message.error(res.msg);
                    }
                });
            }
        },
        loginDateChange: function () {
            if (this.loginDate != '7') {
                this.logintime = [];
                this.search();
            }
        },
        addcustomer: function () {
            this.addcustomer_show = true;
        },
        closeAddcustomer: function () {
            this.addcustomer_show = false;
            this.search();
        },
        addfollow: function (row) {
            this.follow_uid = row.uid;
            this.follow_status = row.crm_status_v;
            this.follow_type = row.crm_type_v;
            this.follow_name = row.name;
            this.follow_calllog_id = '';
            this.follow_show = true;
        },
        closeAddfollow: function () {
            this.follow_show = false;
            this.getList();
        },
        openComInfo: function (scope) {
            this.comInfoDrawer = true;
            this.comuid = scope.row.uid
        },
        closeComInfo() {
            this.comInfoDrawer = false;
            this.getList();
        },
        openPersona: function (scope) {
            this.personaShow = true;
            this.comuid = scope.row.uid
        },
        contact_customer: function (row) {
            var that = this;
            var uid = row.uid;
            var name = row.name;
            var phone = row.linktel;

            message.confirm('确定联系客户吗？', function () {
                startLoading();
                httpPost('m=crm&c=crm_my_customer&a=contactCustomer', { mobile: phone, uid: uid }).then((result) => {
                    endLoading();
                    var res = result.data;
                    if (res.error == 200) {
                        message.success(res.msg, function () {
                            that.follow_uid = uid;
                            that.follow_name = name;
                            that.follow_calllog_id = res.data;
                            that.follow_show = true;
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            })
        },
        noticeShow: function () {
            this.logpopshow = false;
            this.notice_show = true;
        },
        abnormalShow: function (type) {
            this.logpopshow = false;

            if (type == '1') {
                this.abnormal_title = '收到简历';
                this.abnormal_desc = '客户收到简历记录';
            } else if (type == '2') {
                this.abnormal_title = '简历未查看';
                this.abnormal_desc = '客户未查看投递简历统计';
            } else if (type == '3') {
                this.abnormal_title = '简历下载';
                this.abnormal_desc = '客户下载简历数据统计';
            } else if (type == '4') {
                this.abnormal_title = '客户登录';
                this.abnormal_desc = '客户登录平台次数';
            } else if (type == '5') {
                this.abnormal_title = '岗位投递';
                this.abnormal_desc = '客户岗位被投递信息统计';
            } else if (type == '6') {
                this.abnormal_title = '岗位拨号';
                this.abnormal_desc = '岗位被拨号数据统计';
            } else if (type == '7') {
                this.abnormal_title = '企业拨号';
                this.abnormal_desc = '企业被拨号数据统计';
            } else if (type == '8') {
                this.abnormal_title = '投递删除';
                this.abnormal_desc = '简历投递后删除记录';
            } else if (type == '9') {
                this.abnormal_title = '下载删除';
                this.abnormal_desc = '企业删除下载简历记录';
            }
            this.abnormal_type = type;
            this.abnormal_time = '3';
            this.abnormal_show = true;
        },
        tocomjob: function (params) {

            let _this = this
            _this.all_job_uid = params.uid;
            if (params.status) {
                _this.all_job_status = params.status;
            }
            _this.drawerAllJob = true
        },
        closeComcert: function () {
            this.getList();
        },
        sourceDataHandle() {
            let source_data = this.source_data;
            if (!source_data) { // 无数据
                return false;
            }

            if (source_data.crm_uid) {
                this.crm_uid = source_data.crm_uid;
            }
        },

        // 获取企业审核信息
        comaudit: function (uid) {
            var that = this
            httpPost('m=crm&c=crm_my_customer&a=companyAudit', { uid: uid }).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.comdrawersh = true
                    that.curr_com = res.data.Info
                    that.rr_status = that.curr_com.r_status
                    that.member_status = that.curr_com.member_status
                    that.islock = that.rr_status == '2' ? true : false
                    that.statusbody = that.curr_com.statusbody
                    that.lockdesc = that.curr_com.statusbody
                    that.snum = res.data.snum
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        // 企业审核提交
        comSh: function (atype) {
            var that = this
            var params = {
                single: 1,
                status: that.rr_status,
                uid: that.curr_com.uid,
                statusbody: that.statusbody,
                atype: atype,
                crm_check: 1,
            };
            if (this.crm_uid != '') {
                params.crm_uid = this.crm_uid;
                if (this.crm_uid == 'all') {
                    params.orgUids = this.orgUid.join(',');
                }
            }
            if (that.member_status == '1') {
                params.lock_status = that.member_status;
            } else {
                message.error("锁定操作异常！");
                return false;
            }
            that.comShSubmit(params)
        },
        comShSubmit: function (params) {
            var that = this
            httpPost('m=crm&c=crm_my_customer&a=status', params).then(function (result) {
                if (result.data.error == 0) {
                    message.success(result.data.msg, function () {
                        if (result.data.hasOwnProperty("data") && result.data.data.hasOwnProperty("uid") && result.data.data.uid) {
                            that.comaudit(result.data.data.uid)
                        } else {
                            that.getList();
                            if (params.single == 1) {
                                that.comdrawersh = false;
                            } else {
                                that.drawerauditmultiple = false;
                            }
                        }
                    });
                } else {
                    message.error(result.data.msg);
                }
            }).catch(function (e) {
                console.log(e)
            })
        },


    }
};
</script>
<style>
.el-button--primary.is-plain {
    background: #f4f4f5;
    border: none;
}

/* .el-button--primary.is-plain:hover {
        color: #409eff;
    } */

.moduleElMoreInt {
    height: calc(100% - (58px + 120px + 5px)) !important;
}

.moduleElMoreHig {
    height: calc(100% - (50px + 120px + 51px)) !important;
}

.moduleElTaCaoz {
    justify-content: space-between;
}

.customGuanli .el-button {
    /* width: 47%; */
    margin: 3px 0;
}

.customGuanli .el-tabs__content {
    height: calc(100% - 54px - 12px);
    padding: 6px 0;
}

.customFirCoInpt .el-button--primary.is-plain {
    background: #ecf5ff;
    border: 1px solid #b3d8ff;
}

.customFirLog .el-tabs__content {
    overflow: hidden;
    position: relative;
    width: 100%;
    padding: 10px 0;
    height: calc(100% - 70px);
    /* background: #f90; */
}

.tabsIndispLis {
    overflow: hidden;
    position: relative;
}

.tabsIndispLis .el-button {
    overflow: hidden;
    position: relative;
    width: 100%;
    margin: 5px 0;
}

.tabsIndispLis .el-button--primary.is-plain:hover {
    color: #fff;
}

.noticequanyi {
    overflow: hidden;
    position: relative;
    width: 100%;
    height: 100%;
}

.noticequanyi .moduleSeachs {
    padding: 0;
    width: 100%;
}

.noticequanyi .moduleElTabInpt {
    flex-wrap: wrap;
}

.noticequanyi .admin_datatip {
    overflow: hidden;
    position: relative;
    width: 100%;
    margin-top: 10px;
    margin-bottom: 0;
}

.drawerModlue {
    width: initial;
}

.noticequanyi .moduleElTable {
    padding: 0;
    height: calc(100% - (60px + 50px + 30px));
    width: 100%;
    margin-top: 0;
}

.noticequanyi .modulePaging {
    padding: 0;
    width: 100%;
}

.noticequanyi .moduleSeachs {
    margin-bottom: 12px;
}

.admin_datatip_n {
    margin-left: 0px;
    margin-right: 12px;
}

.shzt {
    font-size: 12px;
    background-color: rgba(255, 51, 0, 0.06);
    border: 1px solid rgba(255, 51, 0, 0.5);
    color: #f30;
    padding: 1px 5px;
    cursor: pointer;
}

.shztwtg {
    background-color: #fdf6ec;
    border: 1px solid #faecd8;
    color: #e6a23c;
}

.shztsd {
    background-color: #fef0f0;
    border: 1px solid #fde2e2;
    color: #f56c6c;
}

.shztyzt {
    background-color: #f4f4f5;
    border: 1px solid #e9e9eb;
    color: #909399;
}</style>