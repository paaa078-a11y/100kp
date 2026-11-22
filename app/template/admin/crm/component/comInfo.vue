<template>
    <div class="drawerModlue" v-if="islook">
        <div class="companySubtanc">
            <div class="companySubtaneir">
                <div class="crm_com_info">
                    <div class="crm_comlogo"><img :src="comInfo.logo_n" width="60" height="60"></div>
                    <div class="crm_com_infobox">
                        <div class="crm_com_name"> {{ comInfo.name ? comInfo.name : '企业尚未完善资料' }}
                            <a :href="'https://www.tianyancha.com/search?key=' + comInfo.name" target="_blank"><img src="\../app/template/admin/images/tianyanc.png" height="16"></a>
                        </div>
                        <div class="crm_com_grade">会员等级：
                            <el-tag type="success" size="mini">{{ comInfo.rating_name }}</el-tag>
                            <span class="cominfo_dq" v-if="comInfo.vipetime">{{ comInfo.vipetime_n }}到期</span>
                            <el-divider direction="vertical"></el-divider>
                            所属销售：<el-tag size="mini" type="info">{{comInfo.crm_name }}</el-tag>
                        </div>
                        <div class="crm_com_yhdata"><span>注册时间：{{ comInfo.reg_date }} </span>
                            <el-divider direction="vertical"></el-divider>
                            <span class=" " v-if="comInfo.reg_ip"> 注册IP：{{ comInfo.reg_ip }} </span>
                            <el-divider direction="vertical" v-if="comInfo.reg_ip"></el-divider>
                            <span class=" ">最近登录 ：{{ comInfo.login_day }} </span>
                            <el-divider direction="vertical"></el-divider>
                            <span>跟进记录：<el-button type="text" @click="jumpFollow">{{ comInfo.follow_num }}</el-button></span>
                            <el-divider direction="vertical"></el-divider>
                            <span>收到简历：<el-button type="text" @click="showApplyJobBox">{{comInfo.sqNum }}</el-button></span>
                            <el-divider direction="vertical"></el-divider>
                            <span class=" ">聊天未读：<el-button type="text" @click="chatListBoxShow = true">{{ comInfo.unReadChatNum }}</el-button></span>
                        </div>
                        <div class="crm_com_infobth">
                            <el-button v-if="comInfo.clue == 2" type="primary" size="mini" @click="clueCustomer"><i class="el-icon-refresh"></i>转企业库</el-button>
                            <el-button type="primary" size="mini" @click="addfollow"><i class="el-icon-edit"></i>写跟进</el-button>
                            <el-button type="primary" size="mini" @click="jumpToMember(comInfo.uid)"><i class="el-icon-school"></i> 进入企业中心</el-button>
                        </div>
                    </div>
                </div>
                <el-tabs v-model="activeName" type="card" @tab-click="handleClick">
                    <el-tab-pane label="概况信息" name="followLog">
                        <div class="jltx" v-if="comInfo.unReadNum > 0">
                            <span class=" jltx_n"><i class="el-icon-warning"></i> 简历提醒</span>
                            <el-divider direction="vertical"></el-divider>
                            该企业有 <span class="jltx_n">{{ comInfo.unReadNum }}</span> 份简历未查看，请及时提醒企业查看
                            <el-button type="text" icon="el-icon-s-promotion" size="mini" @click="resumeNoticeShow">立即提醒</el-button>
                        </div>
                        <div class="crm_cominfo_show">
                            <div class="crm_cominfo_left">
                                <div class="crm_cominfo_tit"><i class="el-icon-s-shop"></i> 基本信息</div>
                                <el-descriptions class="margin-top" :column="1" size="size" border>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 企业名称</div>
                                        </template>
                                        <div class="crm_com_nr"> {{ comInfo.name }}
                                            <el-button type="text" icon="el-icon-edit" size="mini" @click="upComName">编辑</el-button>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 企业认证</div>
                                        </template>
                                        <div class="crm_com_rzicon">
                                            <el-tooltip v-if="comInfo.yyzz_status == '1'" class="item" effect="dark" content="营业执照已认证" placement="top-start">
                                                <i class="rzicon rzicon_zzyrz"></i>
                                            </el-tooltip>
                                            <el-tooltip v-else class="item" effect="dark" content="营业执照未认证" placement="top-start">
                                                <i class="rzicon rzicon_zzwrz"></i>
                                            </el-tooltip>
                                            <el-tooltip v-if="comInfo.moblie_status == '1'" class="item" effect="dark" content="手机已认证" placement="top-start">
                                                <i class="rzicon rzicon_sjyrz"></i>
                                            </el-tooltip>
                                            <el-tooltip v-else class="item" effect="dark" content="手机未认证" placement="top-start">
                                                <i class="rzicon rzicon_sjwrz"></i>
                                            </el-tooltip>
                                            <el-tooltip v-if="comInfo.email_status == '1'" class="item" effect="dark" content="邮箱已认证" placement="top-start">
                                                <i class="rzicon rzicon_yxyrz"></i>
                                            </el-tooltip>
                                            <el-tooltip v-else class="item" effect="dark" content="邮箱未认证" placement="top-start">
                                                <i class="rzicon rzicon_yxwrz"></i>
                                            </el-tooltip>
                                            <el-tooltip v-if="comInfo.wxid != '' || comInfo.wxopenid != '' || comInfo.app_wxid != ''" class="item" effect="dark" content="微信已认证" placement="top-start">
                                                <i class="rzicon rzicon_wxyrz"></i>
                                            </el-tooltip>
                                            <el-tooltip v-else class="item" effect="dark" content="微信未认证" placement="top-start">
                                                <i class="rzicon rzicon_wxwrz"></i>
                                            </el-tooltip>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 企业账号</div>
                                        </template>
                                        {{ comInfo.username }}
                                        <el-button type="text" icon="el-icon-edit" size="mini" @click="upAccountInfo">编辑</el-button>
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 注册时间</div>
                                        </template>
                                        {{ comInfo.reg_date }}
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 最近登录</div>
                                        </template>
                                        {{ comInfo.login_day }}
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 企业地址</div>
                                        </template>
                                        {{ comInfo.address }}
                                        <el-button type="text" icon="el-icon-edit" size="mini" @click="upDefLink">编辑</el-button>
                                    </el-descriptions-item>
                                </el-descriptions>
                                <div class="crm_cominfo_tit"><i class="el-icon-s-custom"></i> 相关联系人
                                    <div class="crm_cominfo_titcz">
                                        <el-button type="text" size="mini" class="el-icon-document-add" @click="newCrmLink">新增联系人</el-button>
                                    </div>
                                </div>
                                <el-descriptions :column="1" size="size" border>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 联系人</div>
                                        </template>
                                        <div class="crm_com_nr"> {{ comInfo.linkman }}
                                            <el-divider direction="vertical"></el-divider>
                                            电话: {{ comInfo.linktel }}
                                            <el-button type="text" icon="el-icon-edit" size="mini" @click="upDefLink">编辑</el-button>
                                        </div>
                                    </el-descriptions-item>
                                    <!--新增的-->
                                    <el-descriptions-item v-for="(item, lk) in linkList" :key="lk" v-show="linkNum > 0">
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 联系人</div>
                                        </template>
                                        <div class="crm_com_nr"> {{ item.link_man }}
                                            <el-divider direction="vertical"></el-divider>
                                            电话:{{ item.link_mobile }}
                                            <el-button type="primary" icon="el-icon-edit" circle size="mini" @click="upCrmLink(item.id, item.link_man, item.link_mobile, lk)"></el-button>
                                            <el-button type="danger" icon="el-icon-delete" circle size="mini" @click="delCrmLink(item.id, item.link_man, item.link_mobile, lk)"></el-button>
                                        </div>
                                    </el-descriptions-item>
                                </el-descriptions>
                                <div class="crm_cominfo_tit"><i class="el-icon-s-claim"></i> 业务相关信息</div>
                                <el-descriptions class="margin-top" :column="1" size="mini" border>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 会员等级</div>
                                        </template>
                                        <div class="crm_com_nr">
                                            {{ comInfo.rating_name }}
                                            <el-button type="text" icon="el-icon-edit" size="mini" v-if="comInfo.r_status != 4" @click="stopZp">暂停</el-button>
                                            <template v-if="comInfo.r_status == 4">
                                                <el-divider direction="vertical"></el-divider>
                                                <span style="color: #f60;">{{ comInfo.zt_time_n }} 暂停招聘</span>
                                                <el-button type="text" @click="beginZp"><i class="el-icon-thumb"></i>解除暂停</el-button>
                                            </template>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item v-if="comInfo.vipetime">
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 到期时间</div>
                                        </template>
                                        {{ comInfo.vipetime_n }}
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 客户等级</div>
                                        </template>
                                        <div class="crm_com_nr">
                                            <el-tag size="mini">{{ comInfo.crm_type_n }}</el-tag>
                                            <el-button type="text" icon="el-icon-edit" size="mini" @click="upCrmType">编辑</el-button>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 客户标签</div>
                                        </template>
                                        <div class="crm_com_nr">
                                            <el-tag size="mini">{{ comInfo.crm_status_n }}</el-tag>
                                            <el-button type="text" icon="el-icon-edit" size="mini" @click="upCrmStatus">编辑</el-button>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 所属销售</div>
                                        </template>
                                        <div class="crm_com_nr">
                                            <el-tag size="mini" type="info">{{ comInfo.crm_name }}</el-tag>
                                            <el-button type="text" size="mini" icon="el-icon-sort" @click="deliverShow">变更</el-button>
                                            <el-button type="text" size="mini" icon="el-icon-circle-close" @click="giveUpShow">放弃</el-button>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item v-if="comInfo.crm_time > 0">
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 领取时间</div>
                                        </template>
                                        {{ comInfo.crm_time_n }}
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 最后跟进人</div>
                                        </template>
                                        {{ comInfo.follow_user }}
                                    </el-descriptions-item>
                                    {{ comInfo.crm_name }}
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 最后跟进时间</div>
                                        </template>
                                        {{ comInfo.f_time_n }}
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini"> 备注</div>
                                        </template>
                                        {{ comInfo.crm_remark }}
                                        <el-button type="text" class="el-icon-edit" @click="remarkCom"></el-button>
                                    </el-descriptions-item>
                                </el-descriptions>
                                <div class="crm_cominfo_tit"><i class="el-icon-s-cooperation"></i> 招聘相关信息</div>
                                <el-descriptions class="margin-top" :column="1" size="mini" border>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini crm_com_titmZhiwe"> 职位信息</div>
                                        </template>
                                        <div class="crm_com_nr crm_com_nrhiwei">
                                            <span>职位总数：{{ comInfo.allJobNum }} 个；</span>
                                            <span>在招：{{ comInfo.jobNum }} 个；</span>
                                            <span>推荐：{{ comInfo.recJobNum }} 个；</span>
                                            <span>置顶：{{ comInfo.topJobNum }} 个；</span>
                                            <span>急聘：{{ comInfo.urgentJobNum }} 个</span>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item>
                                        <template slot="label">
                                            <div class="crm_com_titmini crm_com_titmZhiwe"> 简历信息</div>
                                        </template>
                                        <div class="crm_com_nr crm_com_nrhiwei"><span>收到简历：{{ comInfo.sqNum }} 份；</span>
                                            <span>查看简历：{{ comInfo.lookNum }} 份；</span>
                                            <span>下载简历：{{ comInfo.downNum }} 份；</span>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item :span="4" v-if="staticinfo.rating_type > 0">
                                        <template slot="label">
                                            <div class="crm_com_titmini crm_com_titmZhiwe"> 剩余套餐</div>
                                        </template>
                                        <div class="crm_com_nr crm_com_nrhiwei">
                                            <template v-if="staticinfo.rating_type == 1">
                                                <span>可上架职位：{{ staticinfo.crm_job_num }} 个；</span>
                                                <span>刷新职位数：{{ staticinfo.breakjob_num }} 个；</span>
                                                <span>邀请面试数：{{ staticinfo.invite_resume }} 个；</span>
                                                <span>置顶天数：{{ staticinfo.top_num }} 天；</span>
                                                <span>紧急天数：{{ staticinfo.urgent_num }} 天；</span>
                                                <span>推荐天数：{{ staticinfo.rec_num }} 天；</span>
                                                <span>招聘会数：{{ staticinfo.zph_num }} 次；</span>
                                                <span>子账号数：{{ staticinfo.sons_num }} 个；</span>
                                                <span>直聊数：{{ staticinfo.chat_num }} 个；</span>
                                                <span>视频面试数：{{ staticinfo.spview_num }} 个；</span>
                                                <span> 群发次数：{{ staticinfo.group_num }} 次；</span>
                                            </template>
                                            <template v-else-if="staticinfo.rating_type == 2">
                                                <span>可上架职位：{{ staticinfo.crm_job_num }} 个；</span>
                                                <span>今日可刷新职位数：{{ staticinfo.crm_sx_num }} 个；</span>
                                                <span>今日可邀请面试数：{{ staticinfo.crm_yq_num }} 个；</span>
                                                <span>置顶天数：{{ staticinfo.top_num }} 天；</span>
                                                <span>紧急天数：{{ staticinfo.urgent_num }} 天；</span>
                                                <span>推荐天数：{{ staticinfo.rec_num }} 天；</span>
                                                <span>今日可报名招聘会数：{{ staticinfo.crm_zph_num }} 次；</span>
                                                <span>子账号数：{{ staticinfo.sons_num }} 个；</span>
                                                <span>今日可直聊数：{{ staticinfo.crm_chat_num }} 个；</span>
                                                <span>今日可发布视频面试数：{{ staticinfo.crm_sp_num }} 个；</span>
                                                <span>群发次数：{{ staticinfo.group_num }} 次；</span>
                                            </template>
                                        </div>
                                    </el-descriptions-item>
                                </el-descriptions>
                            </div>
                            <div class="crm_cominfo_right">
                                <div class="crm_cominfo_gjjl">
                                    <div class="crm_cominfo_tit"><i class="el-icon-s-management"></i> 跟进信息</div>
                                    <div>
                                        <el-radio-group v-model="followTab" size="mini" @input="handleFollowRadio">
                                            <el-radio-button label="1">我的跟进记录</el-radio-button>
                                            <el-radio-button label="2">历史跟进记录</el-radio-button>
                                        </el-radio-group>
                                    </div>
                                    <div>
                                        <template>
                                            <el-skeleton :loading="followLoading" animated>
                                                <template slot="template">
                                                    <div style="padding: 14px 0;">
                                                        <div style="display: flex; margin-bottom: 6px;">
                                                            <el-skeleton-item variant="text"></el-skeleton-item>
                                                        </div>
                                                        <el-skeleton-item variant="p" style="height: 40px;"></el-skeleton-item>
                                                        <div style="display: flex;">
                                                            <el-skeleton-item variant="text" style="margin-right: 16px;"></el-skeleton-item>
                                                            <el-skeleton-item variant="text" style="width: 30%;"></el-skeleton-item>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template>
                                                    <div v-if="followTotal > 0">
                                                        <div v-for="(item, fk) in followList" :key="fk">
                                                            <div class="crm_com_gjbox">
                                                                <div class="crm_com_gjlist">
                                                                    <i class="crm_com_gjtime_q"></i>
                                                                    <div class="crm_com_gjtime">{{ item.f_time_n }}
                                                                        <el-tag type="success" size="mini">{{item.f_type_n }}</el-tag>
                                                                        <span class="crm_com_gjtime_gjr">跟进人：{{item.f_user }}</span>
                                                                    </div>
                                                                    <div class="crm_com_gjcont">
                                                                        <div class="crm_com_gj_p"> <span class="crm_com_gj_p_t">{{item.content_n }}</span></div>
                                                                        <div class="demo-image__preview" v-if="item.imgArr.length > 0">
                                                                            <el-image style="width: 50px; height: 50px" :src="item.imgArr[0]" :preview-src-list="item.imgArr"></el-image>
                                                                        </div>
                                                                        <div class="crm_com_gj_tel">
                                                                            联系人：{{ item.link_man }}<span v-if="item.link_job">【{{item.link_job }}】</span>
                                                                            <span class="crm_com_gj_tel_n">联系电话：{{ item.link_tel}}</span>
                                                                        </div>
                                                                        <div class="crm_com_gj_r">
                                                                            <span v-if="item.task_time">下次跟进：{{ item.task_time}}</span>
                                                                        </div>
                                                                        <!--修改跟进记录-->
                                                                        <el-button type="text" icon="el-icon-edit" size="mini" v-if="item.is_note == 2 && auid != item.auid" @click="noteShow(item.id, fk)">批注</el-button>
                                                                        <el-button type="text" icon="el-icon-edit" size="mini" v-if="item.up == 1 && auid == item.auid" @click="upFollow(item.id)">修改</el-button>
                                                                        <!--修改跟进记录-->
                                                                        <div v-if="item.is_note == 1">
                                                                            <div class="crm_com_gj_r">
                                                                                <span>{{ item.note_user }}于{{item.noteTime }}进行批注；</span>
                                                                                <el-button v-if="auid == item.note_uid" type="primary" icon="el-icon-edit" circle size="mini" @click="noteShow(item.id, fk, item.note_content)"></el-button>
                                                                                <el-button v-if="auid == item.note_uid" type="danger" icon="el-icon-delete" circle size="mini" @click="delNote(item.id, fk)"></el-button>
                                                                            </div>
                                                                            <div class="crm_com_pznr">
                                                                                <span>{{ item.noteContent }}</span>
                                                                                <div class="demo-image__preview" v-if="item.noteImgArr.length > 0">
                                                                                    <el-image style="width: 100px; height: 100px" :src="item.noteImgArr[0]" :preview-src-list="item.noteImgArr"></el-image>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modulePaging" v-show="followLast_page > 1">
                                                            <div class="modulePagNum">
                                                                <el-pagination background @size-change="handleFollowSizeChange" @current-change="handleFollowCurrentChange" :current-page="followCurrentPage" :page-size.sync="followLimit" :page-sizes="followPageSizes" :total="followTotal" layout="total, sizes, prev, pager, next, jumper"></el-pagination>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <el-empty description="暂无跟进记录" v-else></el-empty>
                                                </template>
                                            </el-skeleton>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane name="jobList">
                        <span slot="label">在招职位 {{ comInfo.jobNum }} </span>
                        <joball ref="joball" :uid="comInfo.uid" @job-ts-resume="matchResume" simple tsjl></joball>
                    </el-tab-pane>
                    <el-tab-pane label="客户日志" name="userLog">
                        <div style="display: flex;">
                            <div style="display: flex;">
                                <el-date-picker v-model="logSearch.time" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" value-format="yyyy-MM-dd" class="w_260"></el-date-picker>
                                <el-input placeholder="请输入内容关键词" v-model="logSearch.keyword" clearable class="w_180 ml_5"></el-input>
                                <el-button size="mini" type="primary" class="ml_5" @click="handleLogSearch">搜索</el-button>
                            </div>
                        </div>
                        <div class="crm_comtable" :class="{ 'moduleElMoreHig': false }">
                            <el-table :data="logList" stripe style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='logSortChange' ref="logTable" v-loading="loadinglog" :empty-text="emptytextlog" border>
                                <el-table-column prop="id" label="编号" width="80" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column prop="content" label="内容"></el-table-column>
                                <el-table-column prop="ip" label="IP" width="120"></el-table-column>
                                <el-table-column prop="ctime_n" label="时间" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                            </el-table>
                        </div>
                        <div class="modulePaging">
                            <div class="modulePagNum">
                                <div class="modulePagNum" style="margin: 0 auto;">
                                    <el-pagination background @size-change="handleLogSizeChange" @current-change="handleLogCurrentChange" :current-page.sync="logCurrentPage" :page-size="logLimit" :page-sizes="logPageSizes" layout="total, sizes, prev, pager, next, jumper" :total="logTotal"></el-pagination>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="权益提醒" name="quanyi">
                        <notice crm_type="1" :crm_name="comInfo.name" source="comInfo"></notice>
                    </el-tab-pane>
                    <el-tab-pane label="发送短信" name="msgList">
                        <div class="faduanxins">
                            <div class="faduanxileft">
                                <el-form ref="msgForm" :model="msgForm" label-width="80px">
                                    <el-form-item label="短信分类">
                                        <el-radio-group v-model="msgClassTab" size="small" @input="handleMsgClassRadio">
                                            <el-radio-button v-for="(item, key) in msgTpl" :key="key" :label="item.id">{{ item.title }}</el-radio-button>
                                        </el-radio-group>
                                    </el-form-item>
                                    <el-form-item label="短信内容">
                                        <el-input type="textarea" v-model="msgForm.content"></el-input>
                                    </el-form-item>
                                    <el-form-item label="手机号码">
                                        <el-input v-model="msgForm.mobile"></el-input>
                                    </el-form-item>
                                    <el-form-item>
                                        <el-button type="primary" @click="sendMsg">发送短信</el-button>
                                    </el-form-item>
                                    <el-form-item>
                                        <el-alert title="温馨小提示" type="info" description="如果给客户打电话，客户可能因为各种情况而不愿意接，次数多了，对方还会嫌弃，可能因为频繁打电话，引起对方的反感；可以选择发送短信给客户更容易接受" :closable="false"></el-alert>
                                    </el-form-item>
                                </el-form>
                            </div>
                            <div class="faduanxiright">
                                <div>
                                    <el-radio-group v-model="msgTab" size="mini" @input="handleMsgRadio">
                                        <el-radio-button label="1">我的发送记录</el-radio-button>
                                        <el-radio-button label="2">历史发送记录</el-radio-button>
                                    </el-radio-group>
                                </div>
                                <div style="margin-top: 6px;">
                                    <template>
                                        <el-skeleton style="width: 320px;" :loading="msgLoading" animated>
                                            <template slot="template">
                                                <div style="padding: 14px 0;">
                                                    <div style="display: flex; margin-bottom: 6px;">
                                                        <el-skeleton-item variant="text"></el-skeleton-item>
                                                    </div>
                                                    <el-skeleton-item variant="p" style="height: 40px;"></el-skeleton-item>
                                                    <div style="display: flex;">
                                                        <el-skeleton-item variant="text" style="margin-right: 16px;"></el-skeleton-item>
                                                        <el-skeleton-item variant="text" style="width: 30%;"></el-skeleton-item>
                                                    </div>
                                                </div>
                                            </template>
                                            <template>
                                                <div class="crm_com_gjbox" style="    margin-left: 10px;">
                                                    <div v-if="msgTotal > 0">
                                                        <div v-for="(item, key) in msgList" :key="key">
                                                            <div class="crm_com_gjlist" style="margin-bottom: 12px;">
                                                                <i class="crm_com_gjtime_q"></i>
                                                                <div class="crm_com_gjtime">{{ item.ctime_n }}
                                                                    <span class="crm_com_gjtime_gjr">操作人：{{ item.name}}</span>
                                                                </div>
                                                                <div class="crm_com_gjcont">
                                                                    <div class="crm_com_gj_p"><span class="crm_com_gj_p_t">{{ item.content }}</span></div>
                                                                    <div class="crm_com_gj_tel"><span class="crm_com_gj_tel_n">接收电话：{{ item.moblie}}</span></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modulePaging">
                                                            <div class="modulePagNum">
                                                                <el-pagination background @size-change="handleMsgSizeChange" @current-change="handleMsgCurrentChange" :page-size="msgLimit" :current-page="msgCurrentPage" :page-sizes="msgPageSizes" :total="msgTotal" layout="total, sizes, prev, pager, next, jumper"></el-pagination>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <el-empty description="暂无短信记录" v-else></el-empty>
                                                </div>
                                            </template>
                                        </el-skeleton>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="推荐简历" name="recUser">
                        <div style="display: flex;">
                            <div style="display: flex;">
                                <el-date-picker v-model="recSearch.time" type="date" value-format="yyyy-MM-dd" placeholder="选择推送日期"></el-date-picker>
                                <el-button size="mini" icon="el-icon-search" type="primary" style="margin-left: 20px;" @click="handleRecSearch">搜索</el-button>
                                <el-button size="mini" icon="el-icon-s-promotion" type="primary" style="margin-left: 20px;" @click="matchResume">推简历</el-button>
                            </div>
                        </div>
                        <div class="admin_datatip" style="margin-top: 10px;">
                            <i class="el-icon-document"></i> 数据统计：推送总数 {{ recTotal }} 条
                        </div>
                        <div class="crm_comtable" :class="{ 'moduleElMoreHig': false }" style="height: calc(100% - 134px);">
                            <el-table :data="recList" stripe style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" @selection-change="handleRecSelectionChange" :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='recSortChange' ref="recTable" v-loading="loadingrec" :empty-text="emptytextrec" border height="100%">
                                <el-table-column type="selection" width="55"></el-table-column>
                                <el-table-column prop="id" label="编号" width="80" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column prop="time_n" label="推送时间" width="160" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column label="推送链接" width="150">
                                    <template slot-scope="scope">
                                        <el-link type="primary" :href="scope.row.url" target="_blank">点击预览</el-link>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="enum" label="关联简历" width="120"></el-table-column>
                                <el-table-column prop="downNum" label="电话查看数" width="120"></el-table-column>
                                <el-table-column prop="hits" label="点击浏览数" width="120" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column label="操作">
                                    <template slot-scope="scope">
                                        <div class="moduleElTaCaoz" style="float: left;">
                                            <el-button size="mini" @click="showRecResume(scope)">详情</el-button>
                                            <el-button size="mini" @click="matchResume(scope.row.id)">修改</el-button>
                                            <el-button size="mini" type="danger" @click="delRecResume(scope)">删除</el-button>
                                        </div>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                        <div class="modulePaging">
                            <div class="modulecz">
                                <el-checkbox :indeterminate="isIndeterminateRec" v-model="isRecCheckAll" @change="handleRecCheckAllChange">全选</el-checkbox>
                                <el-button size="mini" @click="delRecResumeSel">批量删除</el-button>
                            </div>
                            <div class="modulePagNum">
                                <div class="modulePagNum" style="margin: 0 auto;">
                                    <el-pagination background @size-change="handleRecSizeChange" @current-change="handleRecCurrentChange" :current-page.sync="recCurrentPage" :page-size="recLimit" :page-sizes="recPageSizes" layout="total, sizes, prev, pager, next, jumper" :total="recTotal"></el-pagination>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="推送历史" name="twList">
                        <div style="display: flex; overflow: hidden; position: relative; align-items: center; flex-wrap: wrap;">
                            <div style="display: flex; overflow: hidden; position: relative; align-items: center; flex-wrap: wrap;">
                                <el-select v-model="twSearch.type" clearable placeholder="推文类型" class="w_110">
                                    <el-option v-for="(item, key) in twTypeOptions" :key="key" :label="item.label" :value="item.value"></el-option>
                                </el-select>
                                <el-select v-model="twSearch.auid" clearable placeholder="创建人" class="w_110 ml_5">
                                    <el-option v-for="(item, key) in adminUser" :key="key" :label="item.name" :value="item.uid"></el-option>
                                </el-select>
                                <el-select v-model="twSearch.status" clearable placeholder="推送状态" class="w_110 ml_5">
                                    <el-option v-for="(item, key) in twStatusOptions" :key="key" :label="item.label" :value="item.value"></el-option>
                                </el-select>
                                <el-input placeholder="请输入名称关键词" v-model="twSearch.keyword" clearable class="w_260 ml_5"></el-input>
                                <span @click="tagSearch('urgent')" :class="twSearch.urgent == 1 ? 'urgent_search_y' : 'urgent_search_n'" title="加急" class="ml_5"></span>
                                <span @click="tagSearch('wcmoments')" :class="twSearch.wcmoments == 1 ? 'wcmoments_search_y' : 'wcmoments_search_n'" title="朋友圈" class="ml_5"></span>
                                <span @click="tagSearch('gzh')" :class="twSearch.gzh == 1 ? 'gzh_search_y' : 'gzh_search_n'" title="公众号" class="ml_5"></span>
                                <el-button size="mini" type="primary" class="ml_5" @click="handleTwSearch">搜索</el-button>
                                <el-button size="mini" class="ml_5" @click="handleResetTwSearch">重置</el-button>
                                <el-button size="mini" type="primary" icon="el-icon-s-promotion" style="margin-left: 8px;" @click="addComTw">推企业</el-button>
                            </div>
                        </div>
                        <div class="admin_datatip" style="margin: 5px 0 5px 8px;">
                            <i class="el-icon-document"></i> 数据统计：推送总数 {{ twTotal }} 条
                            <span class="admin_datatip_n">已推送：{{ yts }} 条 </span>
                            <span class="admin_datatip_n">未推送：{{ wts }} 条</span>
                        </div>
                        <div class="crm_comtable" :class="{ 'moduleElMoreHig': false }" style="height: calc(100% - 134px);">
                            <el-table :data="twList" stripe style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='twSortChange' ref="twTable" v-loading="loadingtw" :empty-text="emptytexttw" border>
                                <el-table-column prop="id" label="ID" width="80" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column prop="type_n" label="推文类型" width="100"></el-table-column>
                                <el-table-column label="推文标签" width="120">
                                    <template slot-scope="props">
                                        <el-image :src="props.row.urgent == 1 ? twJp_h : twJp"></el-image>
                                        <el-image :src="props.row.wcmoments == 1 ? twPyq_h : twPyq"></el-image>
                                        <el-image :src="props.row.gzh == 1 ? twGzh_h : twGzh"></el-image>
                                    </template>
                                </el-table-column>
                                <el-table-column label="信息名称" width="200">
                                    <template slot-scope="props">
                                        <div class="moduleProps">
                                            <span style="color: #426ef3;">{{ props.row.name }}<span v-if="props.row.jobStatus" style="font-size: 12px;display: inline-block;color: #909399;">{{ props.row.jobStatus }}</span></span>
                                            <span v-if="props.row.type == 1">{{ props.row.sdate_n }} 发布</span>
                                            <span v-if="props.row.type == 2">{{ props.row.sdate_n }} 更新</span>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="status_n" label="状态" width="120">
                                    <template slot-scope="props">
                                        <el-tag :type="props.row.status == 1 ? 'success' : 'info'">{{props.row.status_n }}</el-tag>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="aname" label="创建人" min-width="120"></el-table-column>
                                <el-table-column prop="ctime_n" label="创建时间" min-width="150" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column prop="tsNum" label="推送历史" min-width="80"></el-table-column>
                                <el-table-column prop="etime_n" label="推送时间" min-width="120" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column prop="content" label="备注"></el-table-column>
                            </el-table>
                        </div>
                        <div class="modulePaging">
                            <div class="modulePagNum">
                                <div class="modulePagNum" style="margin: 0 auto;">
                                    <el-pagination background @size-change="handleTwSizeChange" @current-change="handleTwCurrentChange" :current-page.sync="twCurrentPage" :page-size="twLimit" :page-sizes="twPageSizes" layout="total, sizes, prev, pager, next, jumper" :total="twTotal"></el-pagination>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="订单记录" name="orderList">
                        <div style="display: flex;">
                            <div style="display: flex;">
                                <el-select v-model="orderSearch.order_state" clearable placeholder="订单状态" class="w_110">
                                    <el-option v-for="(item, key) in orderStateOptions" :key="key" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                                <el-select v-model="orderSearch.order_type" clearable placeholder="付款方式" class="w_110 ml_5">
                                    <el-option v-for="(item, key) in oTypeOptions" :key="key" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                                <el-select v-model="orderSearch.type" clearable placeholder="订单类型" class="w_110 ml_5">
                                    <el-option v-for="(item, key) in orderTypeOptions" :key="key" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                                <el-date-picker v-model="orderSearch.time" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" value-format="yyyy-MM-dd" class="w_260 ml_5"></el-date-picker>
                                <el-input placeholder="请输入订单号" v-model="orderSearch.keyword" clearable class="w_150 ml_5"></el-input>
                                <el-button size="mini" class="ml_5" type="primary" @click="handleOrderSearch">搜索</el-button>
                                <el-button size="mini" class="ml_5" @click="handleResetOrderSearch">重置</el-button>
                                <el-button size="mini" class="ml_5" type="primary" icon="el-icon-plus" @click="addNewOrder">新增订单</el-button>
                            </div>
                        </div>
                        <div class="crm_comtable" :class="{ 'moduleElMoreHig': false }">
                            <el-table :data="orderList" stripe style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :default-sort="{ prop: 'order_id', order: 'descending' }" @sort-change='orderSortChange' ref="orderTable" v-loading="loadingorder" :empty-text="emptytextorder" border>
                                <el-table-column prop="order_id" label="订单编号" width="180" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column label="订单类型" width="140">
                                    <template slot-scope="props">
                                        <el-tag type="info">{{ props.row.type_n }}</el-tag>
                                    </template>
                                </el-table-column>
                                <el-table-column label="支付方式" width="140">
                                    <template slot-scope="props">
                                        <el-tag>{{ props.row.order_type_n }}</el-tag>
                                    </template>
                                </el-table-column>
                                <el-table-column label="订单状态" width="120">
                                    <template slot-scope="props">
                                        <el-tag v-if="props.row.order_state == 1">{{ props.row.order_state_n }}</el-tag>
                                        <el-tag type="success" v-if="props.row.order_state == 2">{{props.row.order_state_n }}</el-tag>
                                        <el-tag type="info" v-if="props.row.order_state == 3">{{props.row.order_state_n }}</el-tag>
                                        <el-tag type="danger" v-if="props.row.order_state == 0">{{props.row.order_state_n }}</el-tag>
                                        <el-tag type="warning" v-if="props.row.order_state == 4">{{props.row.order_state_n }}</el-tag>
                                    </template>
                                </el-table-column>
                                <el-table-column label="订单金额" width="120">
                                    <template slot-scope="props">
                                        <span style="color: red;">￥ {{ props.row.order_price }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="order_time_n" label="订单时间" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column prop="order_remark" label="备注"></el-table-column>
                            </el-table>
                        </div>
                        <div class="modulePaging">
                            <div class="modulePagNum">
                                <div class="modulePagNum" style="margin: 0 auto;">
                                    <el-pagination background @size-change="handleOrderSizeChange" @current-change="handleOrderCurrentChange" :current-page.sync="orderCurrentPage" :page-size="orderLimit" :page-sizes="orderPageSizes" layout="total, sizes, prev, pager, next, jumper" :total="orderTotal"></el-pagination>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="操作日志" name="optLog">
                        <div style="display: flex;">
                            <div style="display: flex;">
                                <el-date-picker v-model="optLogSearch.time" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" value-format="yyyy-MM-dd" class="w_260"></el-date-picker>
                                <el-button size="mini" type="primary" class="ml_5" @click="handleOptLogSearch">搜索</el-button>
                            </div>
                        </div>
                        <div class="crm_comtable" :class="{ 'moduleElMoreHig': false }">
                            <el-table :data="optLogList" stripe style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :default-sort="{ prop: 'ctime', order: 'descending' }" @sort-change='optLogSortChange' ref="optLogTable" v-loading="loadingoptlog" :empty-text="emptytextoptlog" border>
                                <el-table-column prop="ctime_n" label="操作时间" width="180" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column prop="aname" label="操作人员" width="120"></el-table-column>
                                <el-table-column prop="content" label="描述" width="400"></el-table-column>
                                <el-table-column prop="remark" label="备注"></el-table-column>
                            </el-table>
                        </div>
                        <div class="modulePaging">
                            <div class="modulePagNum">
                                <div class="modulePagNum" style="margin: 0 auto;">
                                    <el-pagination background @size-change="handleOptLogSizeChange" @current-change="handleOptLogCurrentChange" :current-page.sync="optLogCurrentPage" :page-size="optLogLimit" :page-sizes="optLogPageSizes" layout="total, sizes, prev, pager, next, jumper" :total="optLogTotal"></el-pagination>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="行为轨迹" name="webLog">
                        <div style="display: flex;">
                            <div style="display: flex;">
                                <el-date-picker v-model="webLogSearch.time" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" value-format="yyyy-MM-dd" class="w_260"></el-date-picker>
                                <el-button size="mini" type="primary" class="ml_5" @click="handleWebLogSearch">搜索</el-button>
                            </div>
                        </div>
                        <div class="crm_comtable" :class="{ 'moduleElMoreHig': false }">
                            <el-table :data="webLogList" stripe style="width: 100%" :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :default-sort="{ prop: 'id', order: 'descending' }" @sort-change='webLogSortChange' ref="webLogTable" v-loading="loadingweblog" :empty-text="emptytextweblog" border>
                                <el-table-column prop="id" label="编号" width="120" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                                <el-table-column prop="action_name" label="行为事件"></el-table-column>
                                <el-table-column prop="ip" label="IP" width="150"></el-table-column>
                                <el-table-column prop="time_n" label="时间节点" width="200" sortable="custom" :sort-orders="['descending', 'ascending']"></el-table-column>
                            </el-table>
                        </div>
                        <div class="modulePaging">
                            <div class="modulePagNum">
                                <div class="modulePagNum" style="margin: 0 auto;">
                                    <el-pagination background @size-change="handleWebLogSizeChange" @current-change="handleWebLogCurrentChange" :current-page.sync="webLogCurrentPage" :page-size="webLogLimit" :page-sizes="webLogPageSizes" layout="total, sizes, prev, pager, next, jumper" :total="webLogTotal"></el-pagination>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                </el-tabs>
            </div>
        </div>

        <!-- 简历提醒对话框 -->
        <div class="modluDrawer">
            <el-dialog title="简历提醒" :visible.sync="resumeNoticeBoxShow" :append-to-body="true" width="30%" center>
                <div>方法一: 复制二维码发送给企业，企业识别二维码后即可查看简历</div>
                <div>方法二: 复制链接直接发给企业查看简历</div>
                <div style="display: flex; align-items: center; justify-content: center;">
                    <el-image style="width: 160px; height: 160px; margin-top: 25px; border: 1px solid #eee; border-radius: 2px;" :src="resumeNoticeCode"></el-image>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button type="primary" class="copyUrl" data-clipboard-action="copy" :data-clipboard-text="resumeNoticeUrl" @click="handleCopyUrl('copyUrl')">复制链接</el-button>
                </span>
            </el-dialog>
        </div>
        <!-- 转交客户对话框 -->
        <div class="modluDrawer">
            <el-dialog title="转交客户" :visible.sync="deliverBoxShow" :append-to-body="true" width="35%">
                <el-alert title="警告客户被转交后将不能拥有客户操作权限" type="warning" show-icon :closable="false"></el-alert>
                <el-form status-icon label-width="100px" style="margin-top: 10px;">
                    <el-form-item label="客户经理">
                        <el-select v-model="newCrmUid" placeholder="请选择新客户经理">
                            <el-option v-for="(item, key) in crmUser" :key="key" :label="item.name" :value="item.uid"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="备注说明">
                        <el-input type="textarea" v-model="deliverRemark"></el-input>
                    </el-form-item>
                    <div class="crm_tc_bth">
                        <el-button @click="deliverBoxShow = false" size="mini">取消</el-button>
                        <el-button type="primary" @click="deliverCustomer" size="mini">确认</el-button>
                    </div>
                </el-form>
            </el-dialog>
        </div>
        <!-- 放弃客户对话框 -->
        <div class="modluDrawer">
            <el-dialog title="放弃客户" :visible.sync="giveUpBoxShow" :append-to-body="true" width="35%">
                <el-alert title="警告客户被放弃后将不能拥有客户操作权限" type="warning" show-icon :closable="false"></el-alert>
                <div style="padding-top:15px;">
                    <el-input type="textarea" v-model="giveUpRemark" placeholder="说一说为什么放弃呢~"></el-input>
                </div>
                <div class="crm_tc_bth">
                    <el-button @click="giveUpBoxShow = false" size="mini">取消</el-button>
                    <el-button type="primary" @click="giveUpCustomer" size="mini">确认</el-button>
                </div>
            </el-dialog>
        </div>
        <!-- 解除暂停对话框 -->
        <div class="modluDrawer">
            <el-dialog title="解除企业暂停" :visible.sync="beginBoxShow" width="30%">
                <span>{{ beginComTips }}</span>
                <span slot="footer" class="dialog-footer">
                    <el-button type="primary" @click="setBegin(1)">是</el-button>
                    <el-button type="danger" @click="setBegin(2)">否</el-button>
                    <el-button @click="beginBoxShow = false">取 消</el-button>
                </span>
            </el-dialog>
        </div>
        <!--收到简历弹出框-->
        <el-drawer title="岗位申请记录" :visible.sync="applyJobBoxShow" append-to-body size="80%">
            <companyjoblog ref="companyjoblog" :searchcomid="comInfo.uid" searchclass="drawer" v-if="applyJobBoxShow"></companyjoblog>
        </el-drawer>
        <!--聊天记录弹出框-->
        <el-drawer title="聊天记录" :visible.sync="chatListBoxShow" :modal-append-to-body="false" append-to-body size="80%">
            <chatlist ref="chatlist" :keyword_v="comInfo.username" searchclass="drawer" v-if="chatListBoxShow"></chatlist>
        </el-drawer>
        <!--写跟进-->
        <el-drawer title="联系跟进" :close-on-press-escape="false" :wrapper-closable="false" :visible.sync="follow_show" :modal-append-to-body="false" append-to-body :show-close="true" :with-header="true" size="600px">
            <crmnewfollow :fid="follow_id" :uid_v="follow_uid" :crm_status_v="follow_status" :crm_type_v="follow_type" :name_v="follow_name" :calllog_id="follow_calllog_id" @close-update="closeAddfollow" v-if="follow_show"></crmnewfollow>
        </el-drawer>
        <!-- 批注对话框 -->
        <div class="modluDrawer">
            <el-dialog title="跟进批注" :visible.sync="noteBoxShow" :append-to-body="true" width="30%">
                <el-form>
                    <div style="border: 1px solid #ccc;">
                        <div id="toolbarNote"></div>
                        <div id="editorNote" style="height: 300px;"></div>
                    </div>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="cancelNoteShow">取 消</el-button>
                    <el-button type="primary" @click="saveNoteFollow">确 定</el-button>
                </div>
            </el-dialog>
        </div>
        <!-- 修改账号信息对话框 -->
        <div class="modluDrawer">
            <el-dialog title="账号信息" :visible.sync="accountBoxShow" :append-to-body="true" width="40%">
                <el-form status-icon label-width="60px">
                    <el-form-item label="用户名">
                        <el-input v-model="accountForm.username" placeholder="请输入新用户名" clearable></el-input>
                    </el-form-item>
                    <el-form-item label="新密码">
                        <el-input type="password" v-model="accountForm.password" autocomplete="off" placeholder="请输入新的密码，不做修改请留空" clearable></el-input>
                    </el-form-item>
                    <div class="crm_tc_bth">
                        <el-button @click="accountBoxShow = false" size="mini">取消</el-button>
                        <el-button type="primary" @click="saveAccount" size="mini">提交</el-button>
                    </div>
                </el-form>
            </el-dialog>
        </div>
        <!-- 修改企业名称对话框 -->
        <div class="modluDrawer">
            <el-dialog title="企业名称" :visible.sync="nameBoxShow" :append-to-body="true" width="30%">
                <el-form status-icon label-position="top" label-width="100px">
                    <el-input v-model="newComName" placeholder="请输入新的企业名称" clearable></el-input>
                    <div class="crm_tc_bth">
                        <el-button @click="nameBoxShow = false" size="mini">取消</el-button>
                        <el-button type="primary" @click="saveComName" size="mini">提交</el-button>
                    </div>
                </el-form>
            </el-dialog>
        </div>
        <!-- 修改客户等级对话框 -->
        <div class="modluDrawer">
            <el-dialog title="客户等级" :visible.sync="crmTypeBoxShow" :append-to-body="true" width="40%">
                <el-form status-icon label-position="left" label-width="80px">
                    <el-form-item label="客户等级">
                        <el-select v-model="newCrmType" placeholder="请选择客户等级">
                            <el-option v-for="(item, key) in clientType" :key="key" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="备注说明">
                        <el-input type="textarea" v-model="crmTypeRemark"></el-input>
                    </el-form-item>
                    <div class="crm_tc_bth">
                        <el-button @click="crmTypeBoxShow = false" size="mini">取消</el-button>
                        <el-button type="primary" @click="saveCrmType" size="mini">提交</el-button>
                    </div>
                </el-form>
            </el-dialog>
        </div>
        <!-- 修改客户标签对话框 -->
        <div class="modluDrawer">
            <el-dialog title="客户标签" :visible.sync="crmStatusBoxShow" :append-to-body="true" width="40%">
                <el-form status-icon label-position="left" label-width="80px">
                    <el-form-item label="客户标签">
                        <el-select v-model="newCrmStatus" placeholder="请选择客户标签">
                            <el-option v-for="(item, key) in clientStatus" :key="key" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="备注说明">
                        <el-input type="textarea" v-model="crmStatusRemark"></el-input>
                    </el-form-item>
                    <div class="crm_tc_bth">
                        <el-button @click="crmStatusBoxShow = false" size="mini">取消</el-button>
                        <el-button type="primary" @click="saveCrmStatus" size="mini">提交</el-button>
                    </div>
                </el-form>
            </el-dialog>
        </div>
        <!-- 客户备注对话框 -->
        <div class="modluDrawer">
            <el-dialog title="客户备注" :visible.sync="comRemarkBoxShow" :append-to-body="true" width="30%">
                <el-form status-icon label-width="100px">
                    <el-form-item label-width="0">
                        <el-input type="textarea" v-model="comRemark"></el-input>
                    </el-form-item>
                    <div class="crm_tc_bth">
                        <el-button @click="comRemarkBoxShow = false" size="mini">取消</el-button>
                        <el-button type="primary" @click="saveComRemark" size="mini">提交</el-button>
                    </div>
                </el-form>
            </el-dialog>
        </div>
        <!-- 修改默认联系方式对话框 -->
        <div class="modluDrawer">
            <el-dialog title="联系方式" :visible.sync="defLinkBoxShow" :append-to-body="true" width="40%">
                <el-form label-width="80px" ref="linkForm" :model="linkForm">
                    <el-form-item label="姓名">
                        <el-input v-model="linkForm.linkman" placeholder="联系人姓名" clearable></el-input>
                    </el-form-item>
                    <el-form-item label="职称">
                        <el-input v-model="linkForm.linkjob" placeholder="联系人职称 如：经理" clearable></el-input>
                    </el-form-item>
                    <el-form-item label="手机号">
                        <el-input v-model="linkForm.linktel" placeholder="联系人手机号码" clearable></el-input>
                    </el-form-item>
                    <el-form-item label="固定电话">
                        <el-input v-model="linkForm.linkphone" placeholder="固定电话" clearable></el-input>
                    </el-form-item>
                    <el-form-item label="联系邮箱">
                        <el-input v-model="linkForm.linkmail" placeholder="联系人邮箱" clearable></el-input>
                    </el-form-item>
                    <el-form-item label="公司地址">
                        <el-input v-model="linkForm.address" placeholder="请输入公司地址" clearable></el-input>
                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="cancelDefLinkShow" size="mini">取 消</el-button>
                    <el-button type="primary" @click="saveDefLink" size="mini">确 定</el-button>
                </div>
            </el-dialog>
        </div>
        <!-- 修改新联系方式对话框 -->
        <div class="modluDrawer">
            <el-dialog :title="linkDialogTitle" :visible.sync="linkBoxShow" :append-to-body="true" width="40%">
                <el-form label-width="80px">
                    <el-form-item label="姓名">
                        <el-input v-model="newLinkMan" placeholder="联系人姓名" clearable></el-input>
                    </el-form-item>
                    <el-form-item label="手机号">
                        <el-input v-model="newLinkMobile" placeholder="联系人手机号" clearable></el-input>
                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="cancelLinkShow" size="mini">取 消</el-button>
                    <el-button type="primary" @click="saveLink" size="mini">确 定</el-button>
                </div>
            </el-dialog>
        </div>
        <!-- 企业发送至推文弹窗 -->
        <div class="modluDrawer">
            <el-dialog :title="twTitle" :visible.sync="twBoxShow" :with-header="true" :append-to-body="true" :show-close="true" width="450px">
                <div>
                    <div class="wxsettip_small">{{ twTypeName }}</div>
                    <span style="color: #409eff;">{{ twName }}</span>
                    <div class="wxsettip_small ">标签</div>
                    <el-checkbox-group v-model="twTag">
                        <el-checkbox label="加急"></el-checkbox>
                        <el-checkbox label="朋友圈"></el-checkbox>
                        <el-checkbox label="公众号"></el-checkbox>
                    </el-checkbox-group>
                    <div class="wxsettip_small ">备注</div>
                    <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="twRemark"></el-input>
                    <div class="tw_tip" v-show="twTipsShow">
                        <el-alert :title="twTips" type="warning" :closable="false"></el-alert>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="twBoxShow = false">取 消</el-button>
                    <el-button type="primary" @click="setTwTask">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <!-- 推送简历弹出框 -->
        <div class="modluDrawer">
            <el-drawer title="匹配简历" :visible.sync="tsResumeBoxShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="85%">
                <crmresume :key="timer" :match_obj="matchObj"></crmresume>
            </el-drawer>
        </div>
        <!-- 推荐简历详情弹窗 -->
        <div class="modluDrawer">
            <el-drawer title="推荐简历详情" :visible.sync="recBoxShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="70%">
                <div class="tuijianliDome">
                    <div class="tuijianliTops">
                        <div class="tuijianliTopText">
                            推送时间：{{ recResumeInfo.time_n }}，简历 {{ recResumeInfo.enum }} 份，合计点击 {{ recResumeInfo.hits }} 次，电话被查看 {{ recResumeInfo.downNum }} 次
                        </div>
                        <el-button type="primary" class="copyRecUrl" data-clipboard-action="copy" :data-clipboard-text="recResumeInfo.link" @click="handleCopyUrl('copyRecUrl')" size="mini">复制链接</el-button>
                    </div>
                    <div v-for="(item, key) in recResumeList" :key="key" class="tuijianliReuan">
                        <div class="tuijianliToux">
                            <el-avatar :size="50" :src="item.photo_n"></el-avatar>
                        </div>
                        <div class="tuijianliData">
                            <div class="tuijianliDtaInfo">
                                <div class="tuijianliDtaName">
                                    <el-button type="text" @click="previewResume(item.id, item.uid)">{{ item.uname}}</el-button>
                                    <span class="spanNames">{{ item.name }}</span>
                                </div>
                                <div class="tuijianliDtaJindu">
                                    <el-progress :percentage="parseInt(item.integrity)" :stroke-width="8"></el-progress>
                                    <span class="spanTime">{{ item.lastupdate_n }}</span>
                                </div>
                            </div>
                            <div class="tuijianliDtaSueju">
                                <span>{{ item.age_n }}岁</span>
                                <el-divider direction="vertical"></el-divider>
                                <span>{{ item.sex_n }}</span>
                                <el-divider direction="vertical"></el-divider>
                                <span>{{ item.edu_n }}学历</span>
                                <el-divider direction="vertical"></el-divider>
                                <span>{{ item.edu_n }}经验</span>
                                <el-divider direction="vertical"></el-divider>
                                <span>{{ item.city_n }}</span>
                                <el-divider direction="vertical"></el-divider>
                                <span>{{ item.salary }}</span>
                                <el-divider direction="vertical"></el-divider>
                                <span>{{ item.report_n }}</span>
                                <el-divider direction="vertical"></el-divider>
                                <span>{{ item.type_n }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </el-drawer>
        </div>
        <!-- 简历预览弹窗 -->
        <div class="modluDrawer">
            <el-drawer title="简历预览" :append-to-body="true" :visible.sync="resumePreviewVisible" :destroy-on-close="true" size="530px">
                <resume_preview :id="resumeInfo.eid" :uid="resumeInfo.uid"></resume_preview>
            </el-drawer>
        </div>
        <!-- 录订单弹框 -->
        <div class="modluDrawer">
            <el-dialog title="录订单" width="560px" :visible.sync="newDealShow" :modal-append-to-body="false" append-to-body>
                <crmnewdeal :uid_v="comInfo.uid" :name_v="comInfo.name" @close-update="closeNewDeal"></crmnewdeal>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    let editor_content = null,
        toolbar_content = null;
    const {
        createEditor,
        createToolbar
    } = window.wangEditor;

    let noteEditor = null,
        noteToolbar = null,editorInterval = null;

    let timestamp = Date.parse(new Date()); //  当前时间戳 13位
    module.exports = {
        data: function () {
            return {
                infoTab: 1,
                jobTab: 1,
                userLogTab: 1,
                webLogTab: 1,
                msgListTab: 1,
                recUserTab: 1,
                twTab: 1,
                orderTab: 1,
                optLogTab: 1,

                loadingjob: false,
                emptytextjob: '暂无数据',
                loadingrec: false,
                emptytextrec: '暂无数据',
                loadingtw: false,
                emptytexttw: '暂无数据',
                loadingorder: false,
                emptytextorder: '暂无数据',
                loadinglog: false,
                emptytextlog: '暂无数据',
                loadingweblog: false,
                emptytextweblog: '暂无数据',
                loadingoptlog: false,
                emptytextoptlog: '暂无数据',

                uid: '',
                comInfo: {},
                adminUser: [],
                crmUser: [],
                auid: 0,

                resumeNoticeBoxShow: false,
                resumeNoticeCode: '',
                resumeNoticeUrl: '',

                beginBoxShow: false,
                beginComTips: '',

                deliverBoxShow: false,
                newCrmUid: '',
                newCrmName: '',
                deliverRemark: '',

                giveUpBoxShow: false,
                giveUpRemark: '',

                applyJobBoxShow: false,

                chatListBoxShow: false,

                clientStatus: [],
                clientType: [],
                followWay: [],
                links: [],

                activeName: 'followLog',

                taskDesc: '',
                followForm: {
                    followId: 0,
                    crm_status: '',
                    crm_type: '',
                    type: '',
                    f_time: timestamp,
                    link_id: '',
                    is_next: false,
                    n_time: ''
                },
                followContent: '',

                newLinkShow: false,
                newLinkMan: '',
                newLinkMobile: '',
                isContinue: 1,
                btnFollowTxt: '新增',

                followTab: 1,
                followList: [],
                followTotal: 0,
                followLimit: 0,
                followCurrentPage: 1,
                followPageSizes: [],
                followLast_page: 1,
                followLoading: true,

                noteBoxShow: false,
                noteId: 0,
                noteKey: 0,
                noteContent: '',

                msgClassTab: '',
                msgTpl: [],
                msgForm: {
                    content: '',
                    mobile: ''
                },
                msgTab: 1,
                msgList: [],
                msgTotal: 0,
                msgLimit: 0,
                msgCurrentPage: 1,
                msgPageSizes: [],
                msgLoading: true,

                staticinfo: [],
                accountBoxShow: false,
                accountForm: {
                    username: '',
                    password: ''
                },

                nameBoxShow: false,
                newComName: '',

                crmTypeBoxShow: false,
                newCrmType: '',
                newCrmTypeName: '',
                crmTypeRemark: '',

                crmStatusBoxShow: false,
                newCrmStatus: '',
                newCrmStatusName: '',
                crmStatusRemark: '',

                comRemarkBoxShow: false,
                comRemark: '',

                linkList: [],
                linkNum: '',

                defLinkBoxShow: false,
                linkForm: {
                    linkman: '',
                    linkjob: '',
                    linktel: '',
                    linkphone: '',
                    linkmail: '',
                    address: ''
                },

                linkDialogTitle: '新增客户联系人',
                linkBoxShow: false,
                linkId: '',
                linkKey: '',

                twBoxShow: false,
                twTitle: '',
                twJobId: '',
                twType: 0,
                twTypeName: '',
                twName: '',
                twTag: [],
                twRemark: '',
                twTipsShow: false,
                twTips: '',

                tsResumeBoxShow: false,
                timer: 0,
                matchObj: {
                    logId: '',
                    jobId: '',
                    uid: '',
                    linkMan: '',
                    linkTel: '',
                    jobClassId: '',
                    cityClassId: '',
                    userEdu: [],
                    userWord: [],
                    userLabel: [],
                    integrityArr: [],
                    userTime: [{
                        id: 1,
                        name: '今日'
                    }, {
                        id: 2,
                        name: '最近三天'
                    }, {
                        id: 3,
                        name: '最近7天'
                    }, {
                        id: 4,
                        name: '最近一月'
                    }]
                },

                recSearch: {
                    time: '',
                    sort_t: '',
                    order: ''
                },
                recList: [],
                recTotal: 0,
                recLimit: 0,
                prevRecPage: 0,
                recCurrentPage: 1,
                recPageSizes: [],

                isRecCheckAll: false,
                isIndeterminateRec: false,
                recSelectedItem: [],

                recBoxShow: false,
                recResumeList: [],
                recResumeInfo: {},

                resumeInfo: {},
                resumePreviewVisible: false,

                twGzh: "../../images/tw_gzh.png",
                twGzh_h: '../../images/tw_gzh_h.png',
                twJp: '../../images/tw_jp.png',
                twJp_h: '../../images/tw_jph.png',
                twPyq: '../../images/tw_pyq.png',
                twPyq_h: '../../images/tw_pyq_h.png',

                twSearch: {
                    type: '',
                    auid: '',
                    status: '',
                    keyword: '',
                    urgent: 0,
                    wcmoments: 0,
                    gzh: 0,
                    sort_t: '',
                    order: ''
                },
                twTypeOptions: [{value: 1, label: '职位推文'}, {value: 2, label: '企业推文'}],
                twStatusOptions: [{value: 1, label: '已推送'}, {value: 2, label: '未推送'}],
                twList: [],
                twTotal: 0,
                yts: 0,
                wts: 0,
                twLimit: 0,
                prevTwPage: 0,
                twCurrentPage: 1,
                twPageSizes: [],

                orderSearch: {
                    order_state: '',
                    order_type: '',
                    type: '',
                    time: '',
                    keyword: '',
                    sort_t: '',
                    order: ''
                },
                orderStateOptions: [],
                orderTypeOptions: [],
                oTypeOptions: [],
                orderList: [],
                orderTotal: 0,
                orderLimit: 0,
                prevOrderPage: 0,
                orderCurrentPage: 1,
                orderPageSizes: [],
                newDealShow: false,

                logSearch: {
                    time: '',
                    keyword: '',
                    sort_t: '',
                    order: ''
                },
                logList: [],
                logTotal: 0,
                logLimit: 0,
                prevLogPage: 0,
                logCurrentPage: 1,
                logPageSizes: [],

                webLogSearch: {
                    time: '',
                    keyword: '',
                    sort_t: '',
                    order: ''
                },
                webLogList: [],
                webLogTotal: 0,
                webLogLimit: 0,
                prevWebLogPage: 0,
                webLogCurrentPage: 1,
                webLogPageSizes: [],
                webLogLoading: true,

                optLogSearch: {
                    time: '',
                    keyword: '',
                    sort_t: '',
                    order: ''
                },
                optLogList: [],
                optLogTotal: 0,
                optLogLimit: 0,
                prevOptLogPage: 0,
                optLogCurrentPage: 1,
                optLogPageSizes: [],
                optLogLoading: true,

                follow_show: false,
                follow_id: '',
                follow_uid: '',
                follow_status: '',
                follow_type: '',
                follow_name: '',
                follow_calllog_id: '',

                islook: false
            }
        },
        components: {
            'joball': httpVueLoader('../../user/company/component/joball.vue'),
            'companyjoblog': httpVueLoader('../../user/company/component/comlog_index.vue'),
            'chatlist': httpVueLoader('../../tool/chat/component/chatlist.vue'),
            'resume_preview': httpVueLoader('../../component/resume_preview.vue'),
            'crmnewdeal': httpVueLoader('../component/crmnewdeal.vue'),
            'crmresume': httpVueLoader('../component/crm_resume.vue'),
            'crmnewfollow': httpVueLoader('./crmnewfollow.vue'),
            'notice': httpVueLoader('./notice.vue'),
        },
        props: {
            cuid: {
                type: String,
                default: ''
            }
        },
        watch: {
            cuid: {
                handler(val) {
                    this.uid = val;
                    if (this.uid != '') {
                        this.islook = false
                        this.getFollowLog();
                        this.getComInfo();
                        this.getStatic();
                        this.getCrmLink();
                    }
                },
                deep: true,
                immediate: true
            }
        },
		beforeDestroy() {
			noteEditor = null;
			noteToolbar = null;
			editorInterval = null;
		},
        methods: {
            handleClick: function (tab) {
                if (tab.name == 'followLog') {

                } else if (tab.name == 'jobList') {

                } else if (tab.name == 'userLog') {
                    if (this.userLogTab == 1) {

                        this.getLogList();
                        this.userLogTab++;
                    }
                } else if (tab.name == 'webLog') {
                    if (this.webLogTab == 1) {

                        this.getWebLogList();
                        this.webLogTab++;
                    }
                } else if (tab.name == 'msgList') {

                    this.msgForm.mobile = this.comInfo.linktel;

                    if (this.msgListTab == 1) {

                        this.getMsgTpl();
                        this.getMsgList();
                        this.msgListTab++;
                    }
                } else if (tab.name == 'recUser') {
                    if (this.recUserTab == 1) {

                        this.getRecList();
                        this.recUserTab++;
                    }
                } else if (tab.name == 'twList') {
                    if (this.twTab == 1) {

                        this.getTwList();
                        this.twTab++;
                    }
                } else if (tab.name == 'orderList') {
                    if (this.orderTab == 1) {

                        this.getOrderList();
                        this.orderTab++;
                    }
                } else if (tab.name == 'optLog') {
                    if (this.optLogTab == 1) {

                        this.getOptLogList();
                        this.optLogTab++
                    }
                }
            },

            getComInfo: function () {
                var that = this
                httpPost('m=crm&c=crm_my_customer&a=getComInfo', {
                    id: that.uid
                }).then(function (result) {
                    var res = result.data;
                    if (res.error == 0) {
                        that.dsh = res.data.dsh;
                        that.wtg = res.data.wtg;
                        that.yxj = res.data.yxj;

                        that.yts = res.data.yts;
                        that.wts = res.data.wts;

                        that.comInfo = res.data.Info;
                        that.comInfo.crm_type = parseInt(that.comInfo.crm_type) > 0 ? that.comInfo
                            .crm_type : '';
                        that.comInfo.crm_status = parseInt(that.comInfo.crm_status) > 0 ? that.comInfo
                            .crm_status : '';
                        that.auid = res.data.auid;

                        that.adminUser = [];
                        res.data.adminUser.forEach(function (item) {

                            that.adminUser.push({
                                uid: item.uid,
                                name: item.name
                            });
                        })
                        that.crmUser = [];
                        res.data.crmUser.forEach(function (item) {

                            that.crmUser.push({
                                uid: item.uid,
                                name: item.name
                            });
                        })

                        that.resumeNoticeCode = res.data.noticeCode;
                        that.resumeNoticeUrl = res.data.noticeUrl;

                        that.clientStatus = res.data.clientStatus;
                        that.clientType = res.data.clientType;
                        that.followWay = res.data.followWay;

                        that.orderStateOptions = res.data.stateArr;
                        that.orderTypeOptions = res.data.typeArr;
                        that.oTypeOptions = res.data.oTypeArr;

                        that.matchObj.userEdu = res.data.userEdu;
                        that.matchObj.userWord = res.data.userWord;
                        that.matchObj.userLabel = res.data.userLabel;
                        that.matchObj.integrityArr = res.data.integrityArr;

                        if (that.comInfo.task) {
                            if (that.comInfo.task == 1) {
                                that.taskDesc = '您有一条跟进任务尚未完成，任务时间为 ' + that.comInfo.taskTime + '，任务描述：' +
                                    that.comInfo.taskContent + '，请及时跟进';
                            } else if (that.comInfo.task == 2) {
                                that.taskDesc = '您有一个新跟进任务，任务时间为 ' + that.comInfo.taskTime + '，任务描述：' +
                                    that.comInfo.taskContent + '，请及时跟进';
                            }
                        }
                        that.followForm.crm_status = that.comInfo.crm_status;
                        that.followForm.crm_type = that.comInfo.crm_type;

                        that.links = res.data.links;
                        that.links.unshift({
                            id: -1,
                            link_man: that.comInfo.linkman,
                            link_mobile: that.comInfo.linktel
                        });
                        that.followForm.link_id = -1;

                        that.islook = true

                    } else {

                        message.error('获取企业信息失败')
                        return false
                    }
                }).catch(function (e) {
                }).finally(function () {
                })
            },

            jumpToMember: function (uid) {
                let tmpWin = window.open('', '_blank')
                var params = {
                    uid: uid
                }
                httpPost('m=crm&c=crm_my_customer&a=Imitate', params).then(function (result) {
                    var res = result.data;
                    if (res.error == 0) {
                        tmpWin.location = res.data
                    }
                }).catch(function (e) {
                    tmpWin.close()
                })
            },

            resumeNoticeShow: function () {
                let _this = this;
                _this.resumeNoticeBoxShow = true;
            },
            handleCopyUrl: function (target) {
                let clipboard = new ClipboardJS('.' + target); // 获取点击按钮的元素
                /* 注意此事件监听是异步的   */
                clipboard.on('success', (e) => {
                    e.clearSelection();
                    // 释放内存
                    clipboard.destroy();
                    message.success('复制成功');
                });
                // 复制失败
                clipboard.on('error', (e) => {
                    // 释放内存
                    clipboard.destroy();
                    message.error('该浏览器不支持自动复制');
                });
            },

            stopZp: function () {
                let _this = this;
                if (_this.comInfo.isVip == 2) {
                    message.error('会员已到期，无需设置暂停招聘');
                    return;
                } else if (_this.comInfo.r_status == 2) {
                    message.error('企业已锁定，请先解锁后暂停');
                    return false;
                } else {
                    _this.$confirm('确定暂停企业招聘？', '温馨提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        modal: true,
                        type: 'warning'
                    }).then(() => {
                        let params = {
                            uid: _this.comInfo.uid
                        };
                        httpPost('m=crm&c=crm_my_customer&a=stopCom', params).then(function (result) {
                            var res = result.data;
                            if (res.error == 0) {

                                message.success(res.msg, function () {
                                    _this.comInfo.r_status = 4;
                                    _this.comInfo.zt_time_n = res.zt_time_n;
                                    _this.comInfo.zt_days = res.zt_days;
                                });
                            } else {
                                message.error(res.msg);
                                return;
                            }
                        }).catch(function (e) {
                        })
                    }).catch(() => {
                    });
                }
            },
            beginZp: function () {
                let _this = this;
                if (_this.comInfo.zt_days > 0) {

                    _this.beginComTips = '该客户已暂停 ' + _this.comInfo.zt_days + ' 天，是否为客户延续会员有效期？';
                    _this.beginBoxShow = true;
                } else {
                    _this.$confirm('确定解除企业暂停？', '温馨提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        modal: true,
                        type: 'warning'
                    }).then(() => {
                        let params = {
                            uid: _this.comInfo.uid
                        };
                        httpPost('m=crm&c=crm_my_customer&a=openCom', params).then(function (result) {
                            var res = result.data;
                            if (res.error == 0) {
                                message.success(res.msg, function () {
                                    _this.comInfo.r_status = 1;
                                });
                            } else {
                                message.error(res.msg);
                                return;
                            }
                        }).catch(function (e) {
                        })
                    }).catch(() => {
                    });
                }
            },
            setBegin: function (type) {
                let _this = this;
                let params = {
                    uid: _this.comInfo.uid
                };
                if (type == 1) {
                    params.add = 1;
                }
                httpPost('m=crm&c=crm_my_customer&a=openCom', params).then(function (result) {
                    var res = result.data;
                    if (res.error == 0) {
                        _this.comInfo.r_status = 1;
                    } else {
                        message.error(res.msg);
                        return;
                    }
                }).catch(function (e) {
                })
            },

            deliverShow: function () {

                let _this = this;
                if (_this.comInfo.crm_uid != _this.auid) {

                    message.error('只有企业客户经理本人才可以转交客户哦~');
                    return;
                } else {

                    let _this = this;
                    _this.newCrmUid = '';
                    _this.newCrmName = '';
                    _this.deliverRemark = '';
                    _this.deliverBoxShow = true;
                }
            },
            deliverCustomer: function () {
                let _this = this;
                if (_this.newCrmUid == '') {

                    message.error('请选择转交客户经理！');
                    return;
                } else if (_this.deliverRemark == '') {

                    message.error('请添加转交备注说明！');
                    return;
                } else {
                    _this.crmUser.forEach(function (item) {
                        if (item.uid == _this.newCrmUid) {

                            _this.newCrmName = item.name;
                        }
                    })
                    let params = {};
                    params.uid = _this.comInfo.uid;
                    params.crm_uid = _this.newCrmUid;
                    params.crm_name = _this.newCrmName;
                    params.remark = _this.deliverRemark;
                    httpPost('m=crm&c=crm_my_customer&a=deliver', params).then(function (result) {
                        var res = result.data;
                        if (res.error == 0) {
                            message.success(res.msg, function () {

                                _this.deliverBoxShow = false;
                                _this.$emit("child-event");
                            });
                        } else {

                            message.error(res.msg);
                            return;
                        }
                    }).catch(function (e) {
                    })
                }
            },
            giveUpShow: function () {
                let _this = this;
                if (_this.comInfo.crm_uid != _this.auid) {

                    message.error('只有企业客户经理本人才可以放弃客户哦~');
                    return;
                } else {

                    let _this = this;
                    _this.giveUpRemark = '';
                    _this.giveUpBoxShow = true;
                }
            },
            giveUpCustomer: function () {
                let _this = this;
                if (_this.giveUpRemark == '') {

                    message.error('请填写放弃客户的里有呢~');
                    return;
                } else {
                    let params = {};
                    params.uid = _this.comInfo.uid;
                    params.remark = _this.giveUpRemark;
                    httpPost('m=crm&c=crm_my_customer&a=giveUp', params).then(function (result) {
                        var res = result.data;
                        if (res.error == 0) {
                            message.success(res.msg, function () {

                                _this.giveUpBoxShow = false;
                                _this.$emit("child-event");
                            });
                        } else {

                            message.error(res.msg);
                            return;
                        }
                    }).catch(function (e) {
                    })
                }
            },

            jumpFollow: function () {
                let _this = this;
                _this.activeName = 'followLog';
                _this.getFollowLog();
            },
            handleFTime(val) {
                let timestamp = Date.parse(new Date()); //  当前时间戳 13位
                if (timestamp < val) {

                    message.error('跟进时间应不超过当前时间~');
                    this.followForm.f_time = timestamp;
                    return false;
                }
            },

            addNewLink: function () {

                this.newLinkMan = '';
                this.newLinkMobile = '';
                this.newLinkShow = true;
            },
            saveNewLink: function () {
                let that = this;
                if (that.newLinkMan == '') {
                    message.error('请填写联系人姓名~');
                    return false;
                }
                if (that.newLinkMobile == '') {
                    message.error('请填写联系人手机号~');
                    return false;
                } else if (!isjsMobile(that.newLinkMobile)) {
                    message.error('手机号码格式不正确哦~');
                    return false;
                }
                let params = {
                    uid: that.uid,
                    link_man: that.newLinkMan,
                    link_mobile: that.newLinkMobile,
                }
                if (that.isContinue == 2) {
                    params.add = 1;
                }
                httpPost('m=crm&c=crm_my_customer&a=addCrmLink', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        if (data.data.nid) {

                            that.links.push({
                                id: data.data.nid,
                                link_man: that.newLinkMan,
                                link_mobile: that.newLinkMobile
                            });
                            that.followForm.link_id = data.data.nid;
                        }
                        message.success(data.msg, function () {
                            that.newLinkMan = '';
                            that.newLinkMobile = '';
                            that.newLinkShow = false;
                        });
                    } else if (data.error == 2) {
                        that.$confirm(data.msg, '温馨提示', {
                            confirmButtonText: '继续添加',
                            cancelButtonText: '更换号码',
                            modal: true,
                            type: 'warning'
                        }).then(() => {

                            that.isContinue = 2;
                            that.addNewLink();
                        }).catch(() => {

                            that.newLinkMobile = '';
                        });
                    } else {
                        message.error(data.msg);
                        return false;
                    }
                }).catch(function (e) {
                })
            },
            cancelNewLink: function () {
                this.newLinkMan = '';
                this.newLinkMobile = '';
                this.newLinkShow = false;
            },
            handleNTime(val) {
                let timestamp = Date.parse(new Date()); //  当前时间戳 13位
                if (timestamp > val) {

                    message.error('下次跟进时间应超过当前时间哦~');
                    this.followForm.n_time = '';
                    return false;
                }
            },

            handleFollowRadio(val) {
                this.followTab = val;
                this.followCurrentPage = 1;
                this.getFollowLog();
            },
            upFollow: function (id) {
                var comInfo = this.comInfo;
                this.follow_id = id;
                this.follow_uid = comInfo.uid;
                this.follow_status = '';
                this.follow_type = '';
                this.follow_name = comInfo.name;
                this.follow_calllog_id = '';
                this.follow_show = true;
            },
            noteShow: function (id, key, content) {
                let _this = this;
                _this.noteBoxShow = true;
                _this.noteId = id;
                _this.noteKey = key;
                _this.noteContent = content;
				_this.initNoteEditor()
				
                
            },
            initNoteEditor() {
                var that = this
				clearInterval(editorInterval);
				editorInterval = setInterval(()=>{
					if (noteEditor !== null){
						clearInterval(editorInterval);
						if (that.noteContent != '') {
						    noteEditor.setHtml(that.noteContent);
						} else {
						    noteEditor.setHtml('');
						}
					}else{
						let editorConfig = {
						    MENU_CONF: {
						        uploadImage: {
						            server: baseUrl + 'm=index&c=uploadfile',
						            fieldName: 'file'
						        }
						    }
						};
						if (!noteEditor) {
						    noteEditor = createEditor({
						        selector: '#editorNote',
						        html: '',
						        config: editorConfig,
						        mode: 'simple'
						    });
						}
						if (!noteToolbar) {
						    noteToolbar = createToolbar({
						        editor: noteEditor,
						        selector: '#toolbarNote',
						        config: {
						            // 去除工具栏key
						            excludeKeys: ['blockquote', 'header1', 'header2', 'header3', '|', 'bold',
						                'underline', 'italic', 'through', 'color', 'bgColor', 'clearStyle', '|',
						                'bulletedList', 'numberedList', 'todo', 'justifyLeft', 'justifyRight',
						                'justifyCenter', '|', 'insertLink', 'insertVideo', 'insertTable',
						                'codeBlock', '|', 'undo', 'redo', '|', 'fullScreen'
						            ]
						        },
						        mode: 'simple'
						    });
						}
					}
				},300);
            },
            cancelNoteShow: function () {

                let _this = this;
                _this.noteBoxShow = false;
                _this.noteId = 0;
                _this.noteKey = 0;
                _this.noteContent = '';
                noteEditor.setHtml('');
            },
            saveNoteFollow: function () {
                let that = this;
                let params = {
                    id: that.noteId,
                    uid: that.uid,
                    content: noteEditor.getHtml(),
                }
                httpPost('m=crm&c=crm_my_customer&a=noteFollow', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {
                        message.success(data.msg, function () {

                            that.followList[that.noteKey].is_note = data.data.is_note;
                            that.followList[that.noteKey].note_user = data.data.note_user;
                            that.followList[that.noteKey].noteTime = data.data.noteTime;
                            that.followList[that.noteKey].noteContent = data.data.noteContent;
                            that.followList[that.noteKey].note_content = params.content;
                            that.followList[that.noteKey].noteImgArr = data.data.noteImgArr;

                            that.cancelNoteShow();
                        });
                    } else {
                        message.error(data.msg);
                        return false;
                    }
                }).catch(function (e) {
                })
            },
            delNote: function (id, key) {
                let _this = this;
                _this.$confirm('确定删除该条批注信息？', '温馨提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    modal: true,
                    type: 'warning'
                }).then(() => {
                    _this.noteKey = key;
                    httpPost('m=crm&c=crm_my_customer&a=delFollowNote', {
                        id: id
                    }).then(function (res) {
                        var data = res.data;
                        if (data.error == 0) {
                            message.success(data.msg, function () {

                                _this.followList[that.noteKey].is_note = 2;
                                _this.followList[that.noteKey].note_user = '';
                                _this.followList[that.noteKey].noteTime = '';
                                _this.followList[that.noteKey].noteContent = '';
                                _this.followList[that.noteKey].note_content = '';
                                _this.followList[that.noteKey].noteImgArr = [];
                                _this.noteKey = 0;
                            });
                        } else {
                            message.error(data.msg);
                            return false;
                        }
                    }).catch(function (e) {
                    })
                }).catch(() => {
                });
            },
            handleFollowSizeChange(val) {

                this.followCurrentPage = 1;
                this.followLimit = val;
                this.getFollowLog();
            },
            handleFollowCurrentChange(val) {

                this.followCurrentPage = val;
                this.getFollowLog();
            },
            getFollowLog: function () {
                let that = this;
                let params = {
                    id: that.uid,
                    followTab: that.followTab,
                    page: that.followCurrentPage,
                    limit: that.followLimit
                }
                httpPost('m=crm&c=crm_my_customer&a=getFollowList', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        that.followList = data.data.followList;
                        that.followTotal = parseInt(data.data.total)
                        that.followPageSizes = data.data.page_sizes
                        that.followLimit = data.data.page_size;
                        that.followLast_page = data.data.last_page
                        that.followLoading = false;
                    } else {
                        message.error('获取企业跟进记录失败')
                        return false
                    }
                }).catch(function (e) {
                })
            },

            handleMsgClassRadio(val) {
                let _this = this;
                _this.msgTpl.forEach(function (item) {
                    if (val == item.id) {

                        _this.msgForm.content = item.content;
                    }
                })
            },
            getMsgTpl: function () {
                let that = this;
                let params = {
                    uid: that.uid
                }
                httpPost('m=crm&c=crm_my_customer&a=getMsgTpl', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        that.msgTpl = data.data.msgTpl;
                        that.msgForm.content = data.data.msgContent;
                        that.msgClassTab = data.data.msgClass;
                    } else {
                        message.error('获取短信发送记录失败')
                        return false
                    }
                }).catch(function (e) {
                })
            },
            sendMsg: function () {
                let _this = this;
                let params = {
                    uid: _this.uid,
                    content: _this.msgForm.content,
                    mobile: _this.msgForm.mobile
                }
                httpPost('m=crm&c=crm_my_customer&a=sendMsg', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {
                        message.success(data.msg, function () {

                            _this.getMsgList();
                        })
                    } else {
                        message.error(data.msg)
                        return false
                    }
                }).catch(function (e) {
                })
            },
            handleMsgRadio(val) {

                this.msgTab = val;
                this.msgCurrentPage = 1;
                this.getMsgList();
            },
            handleMsgSizeChange(val) {

                this.msgCurrentPage = 1;
                this.msgLimit = val;
                this.getMsgList();
            },
            handleMsgCurrentChange(val) {

                this.msgCurrentPage = val;
                this.getMsgList()
            },
            getMsgList: function () {
                let that = this;
                let params = {
                    uid: that.uid,
                    msgTab: that.msgTab,
                    page: that.msgCurrentPage,
                    limit: that.msgLimit
                }
                httpPost('m=crm&c=crm_my_customer&a=getMsgList', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        that.msgList = data.data.msgList;
                        that.msgTotal = parseInt(data.data.total)
                        that.msgPageSizes = data.data.page_sizes
                        that.msgLimit = data.data.page_size
                        that.msgLoading = false;
                    } else {
                        message.error('获取短信发送记录失败')
                        return false
                    }
                }).catch(function (e) {
                })
            },

            getStatic: function () {
                let _this = this;
                httpPost('m=crm&c=crm_my_customer&a=getComStatic', {
                    id: _this.uid
                }).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {
                        _this.staticinfo = data.data.static;
                    } else {
                        message.error('获取客户信息失败');
                        return false
                    }
                }).catch(function (e) {
                })
            },

            upAccountInfo: function () {
                let _this = this;
                _this.accountForm.username = _this.comInfo.username;
                _this.accountForm.password = '';
                _this.accountBoxShow = true;
            },
            saveAccount: function () {
                let _this = this;
                if (_this.accountForm.username == '' && _this.accountForm.password == '') {
                    message.error('账号新用户名或者新密码不能全为空哦~');
                    return;
                } else if (_this.accountForm.password == '' && _this.accountForm.username == _this.comInfo
                    .username) {
                    message.error('账号用户名未发现变动~');
                    return;
                } else {

                    let params = {};
                    params.id = _this.comInfo.uid;
                    params.username = _this.accountForm.username;
                    params.password = _this.accountForm.password;

                    httpPost('m=crm&c=crm_my_customer&a=upUserInfo', params).then(function (res) {
                        var data = res.data;
                        if (data.error == 0) {
                            message.success(data.msg, function () {

                                _this.comInfo.username = _this.accountForm.username;
                                _this.accountBoxShow = false;
                            });
                        } else {
                            message.error(data.msg);
                            return false;
                        }
                    }).catch(function (e) {
                    })
                }
            },
            upComName: function () {
                let _this = this;
                _this.newComName = _this.comInfo.name;
                _this.nameBoxShow = true;
            },
            saveComName: function () {
                let _this = this;
                if (_this.newComName == '') {
                    message.error('新企业名称不能为空哦~');
                    return;
                } else if (_this.comInfo.name == _this.newComName) {
                    message.error('企业名称未发现变动~');
                    return;
                } else {

                    let params = {};
                    params.id = _this.comInfo.uid;
                    params.name = _this.newComName;
                    httpPost('m=crm&c=crm_my_customer&a=upComName', params).then(function (res) {
                        var data = res.data;
                        if (data.error == 0) {
                            message.success(data.msg, function () {

                                _this.comInfo.name = _this.newComName;
                                _this.nameBoxShow = false;
                            });
                        } else {
                            message.error(data.msg);
                            return false;
                        }
                    }).catch(function (e) {
                    })
                }
            },
            upCrmType: function () {
                let _this = this;
                _this.newCrmType = parseInt(_this.comInfo.crm_type) > 0 ? _this.comInfo.crm_type : '';
                _this.crmTypeRemark = '';
                _this.crmTypeBoxShow = true;
            },
            saveCrmType: function () {
                let _this = this;
                if (_this.newCrmType == _this.comInfo.crm_type) {

                    message.error('客户等级未发现变动哦~');
                    return;
                } else {

                    _this.clientType.forEach(function (item) {
                        if (item.id == _this.newCrmType) {
                            _this.newCrmTypeName = item.name;
                        }
                    })
                    let params = {};
                    params.uid = _this.comInfo.uid;
                    params.st = _this.newCrmType;
                    params.st_n = _this.newCrmTypeName;
                    params.remark = _this.crmTypeRemark;
                    params.isSt = 'type';

                    httpPost('m=crm&c=crm_my_customer&a=upStatusType', params).then(function (res) {
                        var data = res.data;
                        if (data.error == 0) {
                            message.success(data.msg, function () {

                                _this.comInfo.crm_type = _this.newCrmType;
                                _this.comInfo.crm_type_n = _this.newCrmTypeName;
                                _this.crmTypeBoxShow = false;
                            });
                        } else {
                            message.error(data.msg);
                            return false;
                        }
                    }).catch(function (e) {
                    })
                }
            },
            upCrmStatus: function () {
                let _this = this;
                _this.newCrmStatus = _this.comInfo.crm_status;
                _this.crmStatusRemark = '';
                _this.crmStatusBoxShow = true;
            },
            saveCrmStatus: function () {
                let _this = this;
                if (_this.newCrmStatus == _this.comInfo.crm_status) {

                    message.error('客户标签未发现变动哦~');
                    return;
                } else {

                    _this.clientStatus.forEach(function (item) {
                        if (item.id == _this.newCrmStatus) {
                            _this.newCrmStatusName = item.name;
                        }
                    })
                    let params = {};
                    params.uid = _this.comInfo.uid;
                    params.st = _this.newCrmStatus;
                    params.st_n = _this.newCrmStatusName;
                    params.remark = _this.crmStatusRemark;
                    params.isSt = 'status';

                    httpPost('m=crm&c=crm_my_customer&a=upStatusType', params).then(function (res) {
                        var data = res.data;
                        if (data.error == 0) {
                            message.success(data.msg, function () {

                                _this.comInfo.crm_status = _this.newCrmStatus;
                                _this.comInfo.crm_status_n = _this.newCrmStatusName;
                                _this.crmStatusBoxShow = false;
                            });
                        } else {
                            message.error(data.msg);
                            return false;
                        }
                    }).catch(function (e) {
                    })
                }
            },

            remarkCom: function () {
                let _this = this;
                _this.comRemarkBoxShow = true;
            },
            saveComRemark: function () {
                let _this = this;
                if (_this.comRemark == '') {

                    message.error('请填写备注内容哦~');
                    return;
                } else if (_this.comRemark == _this.comInfo.crm_remark) {

                    message.error('备注内容未做调整~');
                    return;
                } else {

                    let params = {};
                    params.uid = _this.comInfo.uid;
                    params.crm_remark = _this.comRemark;

                    httpPost('m=crm&c=crm_my_customer&a=remarkCom', params).then(function (res) {
                        var data = res.data;
                        if (data.error == 0) {
                            message.success(data.msg, function () {

                                _this.comInfo.crm_remark = _this.comRemark;
                                _this.comRemarkBoxShow = false;
                            });
                        } else {
                            message.error(data.msg);
                            return false;
                        }
                    }).catch(function (e) {
                    })
                }
            },

            getCrmLink: function () {
                let _this = this;
                httpPost('m=crm&c=crm_my_customer&a=getLinkList', {
                    id: _this.uid
                }).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {
                        _this.linkList = data.data.links;
                        _this.linkNum = data.data.links.length;
                    } else {
                        message.error('获取客户联系方式失败');
                        return false
                    }
                }).catch(function (e) {
                })
            },

            upDefLink: function () {
                let _this = this;
                _this.linkForm = {

                    linkman: _this.comInfo.linkman,
                    linkjob: _this.comInfo.linkjob,
                    linktel: _this.comInfo.linktel,
                    linkphone: _this.comInfo.linkphone,
                    linkmail: _this.comInfo.linkmail,
                    address: _this.comInfo.address
                }
                _this.defLinkBoxShow = true;
            },
            cancelDefLinkShow: function () {
                let _this = this;
                _this.defLinkBoxShow = false;
            },
            saveDefLink: function () {
                let _this = this;
                if (_this.linkForm.linkman == _this.comInfo.linkman && _this.linkForm.linkjob == _this.comInfo
                        .linkjob && _this.linkForm.linktel == _this.comInfo.linktel && _this.linkForm.linkphone ==
                    _this.comInfo.linkphone && _this.linkForm.linkmail == _this.comInfo.linkmail && _this.linkForm
                        .address == _this.comInfo.address) {

                    message.error('联系方式未发生改变');
                    return false;
                } else if (_this.linkForm.linkman == '') {

                    message.error('联系人信息不能为空');
                    return false;
                } else if (_this.linkForm.linktel == '' && _this.linkForm.linkphone == '') {

                    message.error('联系号码不能为空');
                    return false;
                } else if (_this.linkForm.address == '') {

                    message.error('公司地址不能为空');
                    return false;
                }

                let params = {};
                params.uid = _this.uid;
                params.linkman = _this.linkForm.linkman;
                params.linkjob = _this.linkForm.linkjob;
                params.linktel = _this.linkForm.linktel;
                params.linkphone = _this.linkForm.linkphone;
                params.linkmail = _this.linkForm.linkmail;
                params.address = _this.linkForm.address;

                httpPost('m=crm&c=crm_my_customer&a=upComLink', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {
                        message.success(data.msg, function () {

                            _this.comInfo.linkman = _this.linkForm.linkman;
                            _this.comInfo.linkjob = _this.linkForm.linkjob;
                            _this.comInfo.linktel = _this.linkForm.linktel;
                            _this.comInfo.linkphone = _this.linkForm.linkphone;
                            _this.comInfo.linkmail = _this.linkForm.linkmail;
                            _this.comInfo.address = _this.linkForm.address;
                            _this.links.forEach(function (item, key) {
                                if (item.id == -1) {
                                    _this.links[key].link_man = _this.linkForm.linkman;
                                    _this.links[key].link_mobile = _this.linkForm.linktel;
                                }
                            })
                            _this.defLinkBoxShow = false;
                        });
                    } else {
                        message.error(data.msg);
                        return false;
                    }
                }).catch(function (e) {
                })
            },

            newCrmLink: function () {
                let _this = this;
                _this.linkDialogTitle = '新增客户联系人';
                _this.newLinkMan = '';
                _this.newLinkMobile = '';
                _this.linkBoxShow = true;
            },
            upCrmLink: function (id, linkMan, linkMobile, key) {
                let _this = this;
                _this.linkDialogTitle = '更新客户联系人';
                _this.newLinkMan = linkMan;
                _this.newLinkMobile = linkMobile;
                _this.linkId = id;
                _this.linkKey = key;
                _this.linkBoxShow = true;
            },
            cancelLinkShow: function () {
                let _this = this;
                _this.linkDialogTitle = '新增客户联系人';
                _this.newLinkMan = '';
                _this.newLinkMobile = '';
                _this.linkId = '';
                _this.linkKey = '';
                _this.linkBoxShow = false;
            },
            saveLink: function () {
                let that = this;
                if (that.newLinkMan == '') {
                    message.error('请填写联系人姓名~');
                    return false;
                }
                if (that.newLinkMobile == '') {
                    message.error('请填写联系人手机号~');
                    return false;
                } else if (!isjsMobile(that.newLinkMobile)) {
                    message.error('手机号码格式不正确哦~');
                    return false;
                }
                let params = {
                    uid: that.uid,
                    link_man: that.newLinkMan,
                    link_mobile: that.newLinkMobile,
                }
                if (that.linkId > 0) {
                    params.id = that.linkId;
                }
                if (that.isContinue == 2) {
                    params.add = 1;
                }
                httpPost('m=crm&c=crm_my_customer&a=addCrmLink', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {
                        if (data.data.nid) {

                            that.links.push({
                                id: data.data.nid,
                                link_man: that.newLinkMan,
                                link_mobile: that.newLinkMobile
                            });
                            that.linkList.push({
                                id: data.data.nid,
                                link_man: that.newLinkMan,
                                link_mobile: that.newLinkMobile
                            });
                        } else {

                            that.links.forEach(function (item, key) {
                                if (item.id == that.linkId) {
                                    that.links[key].link_man = that.newLinkMan;
                                    that.links[key].link_mobile = that.newLinkMobile;
                                }
                            })
                            that.linkList.forEach(function (item, key) {
                                if (item.id == that.linkId) {
                                    that.linkList[key].link_man = that.newLinkMan;
                                    that.linkList[key].link_mobile = that.newLinkMobile;
                                }
                            })
                        }
                        message.success(data.msg, function () {

                            that.newLinkMan = '';
                            that.newLinkMobile = '';
                            that.linkBoxShow = false;
                        });
                    } else if (data.error == 2) {
                        that.$confirm(data.msg, '温馨提示', {
                            confirmButtonText: '继续添加',
                            cancelButtonText: '更换号码',
                            modal: true,
                            type: 'warning'
                        }).then(() => {

                            that.isContinue = 2;
                            that.saveLink();
                        }).catch(() => {

                            that.newLinkMobile = '';
                        });
                    } else {
                        message.error(data.msg);
                        return false;
                    }
                }).catch(function (e) {
                })
            },
            delCrmLink: function (id, linkMan, linkMobile, key) {
                let _this = this;
                _this.linkKey = key;
                let params = {
                    uid: _this.uid,
                    id: id
                }
                _this.$confirm('确定删除该客户联系方式？', '温馨提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    httpPost('m=crm&c=crm_my_customer&a=delCrmLink', params).then(function (res) {
                        var data = res.data;
                        if (data.error == 0) {
                            message.success(data.msg, function () {

                                _this.linkList.splice(_this.linkKey, 1);
                                _this.linkNum--;

                                _this.links.splice(_this.links.indexOf({
                                    id: id,
                                    link_man: linkMan,
                                    link_mobile: linkMobile
                                }), 1);
                            });
                        } else {

                            message.error(data.msg);
                            return false
                        }
                    }).catch(function (e) {
                    })
                }).catch(() => {
                    return false;
                });
            },

            addComTw: function () {

                let _this = this;
                if (_this.comInfo.taskNum > 0) {
                    _this.$confirm('该企业尚有推文未发送，是否继续添加？', '温馨提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        _this.twTitle = '添加企业到社群推文助手';
                        _this.twType = 2;
                        _this.twTypeName = '企业名称';
                        _this.twName = _this.comInfo.name;
                        if (_this.comInfo.update_day == 1) {
                            _this.twTips = '企业已超过3天没有更新，请确认无误后添加';
                            _this.twTipsShow = true;
                        }
                        _this.twBoxShow = true;
                    }).catch(() => {
                        return false;
                    });
                } else {

                    _this.twTitle = '添加企业到社群推文助手';
                    _this.twType = 2;
                    _this.twTypeName = '企业名称';
                    _this.twName = _this.comInfo.name;
                    if (_this.comInfo.update_day == 1) {
                        _this.twTips = '企业已超过3天没有更新，请确认无误后添加';
                        _this.twTipsShow = true;
                    }
                    _this.twBoxShow = true;
                }
            },

            setTwTask: function () {
                let _this = this;
                let param = {};

                if (_this.twType == 2) {

                    param.cuid = _this.uid;
                    param.type = 2;
                } else if (_this.twType == 1) {
                    param.cuid = _this.uid;
                    param.type = 1;
                    param.jobid = _this.twJobId;
                }
                param.urgent = 1;
                param.wcmoments = 1;
                param.gzh = 1;
                _this.twTag.forEach(function (item) {
                    if (item == '加急') {
                        param.urgent = 2;
                    }
                    if (item == '朋友圈') {
                        param.wcmoments = 2;
                    }
                    if (item == '公众号') {
                        param.gzh = 3;
                    }
                });
                param.content = _this.twRemark;
                httpPost('m=crm&c=crm_my_customer&a=addTwTask', param).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            _this.twBoxShow = false;
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            showApplyJobBox: function () {
                let _this = this;
                _this.applyJobBoxShow = true;
            },

            matchResume: function (row) {

                let _this = this;
                _this.timer = new Date().getTime();

                var logId = '',
                    jobId = '',
                    jobClassId = '',
                    cityClassId = '';
                if (typeof row === 'object') {
                    jobId = row.id;
                    jobClassId = row.jobClassId;
                    cityClassId = row.cityClassId;
                    _this.matchObj.jobClassName = row.jobClassId_n;
                    _this.matchObj.cityClassName = row.cityClassId_n;
                } else if (typeof row === 'string') {
                    logId = row;
                }

                _this.matchObj.logId = logId;
                _this.matchObj.jobId = jobId;
                _this.matchObj.linkMan = _this.comInfo.linkman;
                _this.matchObj.linkTel = _this.comInfo.linktel;

                _this.matchObj.jobClassId = jobClassId;
                _this.matchObj.cityClassId = cityClassId;
                _this.matchObj.uid = _this.comInfo.uid;

                _this.tsResumeBoxShow = true;
            },

            handleRecSearch: function () {
                let _this = this;
                _this.recCurrentPage = 1;
                _this.getRecList();
            },
            recSortChange: function (e) {
                this.recSearch.sort_t = e.prop;
                if (e.prop == 'time_n') {
                    this.recSearch.sort_t = 'atime';
                }
                this.recSearch.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.recCurrentPage = 1;
                this.getRecList();
            },
            handleRecSelectionChange(val) {
                this.recSelectedItem = val;
                if (this.recSelectedItem.length == 0) {
                    this.isIndeterminateRec = false;
                    this.isRecCheckAll = false;
                } else {
                    if (this.recSelectedItem.length == this.recList.length) {
                        this.isIndeterminateRec = false;
                        this.isRecCheckAll = true;
                    } else {
                        this.isIndeterminateRec = true;
                        this.isRecCheckAll = false;
                    }
                }
            },
            handleRecCheckAllChange(val) {
                val ? this.$refs.recTable.toggleAllSelection() : this.$refs.recTable.clearSelection();
            },
            handleRecSizeChange(val) {

                this.recCurrentPage = 1;
                this.recLimit = val;
                this.getRecList();
            },
            handleRecCurrentChange(val) {
                this.recCurrentPage = val;
                this.getRecList()
            },
            getRecList: function () {
                let that = this;
                let params = {

                    id: that.uid,
                    time: that.recSearch.time,
                    page: that.recCurrentPage,
                    limit: that.recLimit,
                    t: that.recSearch.sort_t,
                    order: that.recSearch.order
                }
                that.loadingrec = true;
                that.emptytextrec = '数据加载中';
                httpPost('m=crm&c=crm_my_customer&a=getRecList', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        that.recList = data.data.recList;
                        that.recTotal = parseInt(data.data.total)
                        that.recPageSizes = data.data.page_sizes
                        that.recLimit = data.data.page_size
                        if (that.prevRecPage != that.recCurrentPage) {
                            that.prevRecPage = that.recCurrentPage;
                            that.$refs.recTable.bodyWrapper.scrollTop = 0;
                        }
                        that.loadingrec = false;
                        if (that.recList.length == 0) {
                            that.emptytextrec = '暂无数据';
                        }
                    } else {
                        message.error('获取推荐简历信息失败')
                        return false
                    }
                }).catch(function (e) {
                })
            },
            showRecResume: function (scope) {
                let _this = this;
                let params = {
                    id: scope.row.id,
                }
                httpPost('m=crm&c=crm_my_customer&a=getRecInfo', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        _this.recResumeInfo = data.data.recInfo;
                        _this.recResumeList = data.data.recResumes;
                        _this.recBoxShow = true;
                    } else {

                        message.error('获取推荐简历信息失败')
                        return false
                    }
                }).catch(function (e) {
                })

            },
            previewResume: function (id, uid) {

                let _this = this;
                _this.resumeInfo.eid = id;
                _this.resumeInfo.uid = uid;
                _this.resumePreviewVisible = true;
            },
            delRecResume: function (scope, isMore) {
                var that = this;
                let str = '';
                let idArr = [],
                    dateArr = [];
                let params = {};
                if (isMore) {
                    this.recSelectedItem.forEach((item) => {

                        idArr.push(item.id);
                        if (dateArr.indexOf(item.date_n) == -1) {
                            dateArr.push(item.date_n);
                        }
                    });

                    str = '（' + dateArr.join('，') + '）';
                    params.id = idArr;
                } else {

                    str = '（' + scope.row.date_n + '）';
                    params.id = scope.row.id;
                }

                delConfirm(this, params, this.delRec, '您确定要删除：' + str + ' 推广信息吗？');
            },
            delRecResumeSel: function () {
                var that = this;
                if (!that.recSelectedItem.length) {
                    message.error('请选择要删除的推广数据');
                    return;
                }
                this.delRecResume(null, true);
            },
            delRec: function (params) {
                var self = this;
                httpPost('m=crm&c=crm_my_customer&a=delRecResume', params).then(function (response) {
                    let res = response.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            self.getRecList();
                        });
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },

            handleTwSearch: function () {
                let _this = this;
                _this.twCurrentPage = 1;
                _this.getTwList();
            },
            handleResetTwSearch: function () {
                let _this = this;
                _this.twSearch.type = '';
                _this.twSearch.auid = '';
                _this.twSearch.status = '';
                _this.twSearch.keyword = '';
                _this.twSearch.urgent = 0;
                _this.twSearch.wcmoments = 0;
                _this.twSearch.gzh = 0;
                _this.twSearch.sort_t = '';
                _this.twSearch.order = '';
                _this.twCurrentPage = 1;
                _this.getTwList();
            },
            tagSearch: function (type) {
                this.twSearch[type] = this.twSearch[type] == 1 ? 0 : 1;
            },
            twSortChange: function (e) {
                this.twSearch.sort_t = e.prop;
                if (e.prop == 'ctime_n') {
                    this.twSearch.sort_t = 'ctime';
                }
                if (e.prop == 'etime_n') {
                    this.twSearch.sort_t = 'etime';
                }
                this.twSearch.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.twCurrentPage = 1;
                this.getTwList();
            },
            handleTwSizeChange(val) {

                this.twCurrentPage = 1;
                this.twLimit = val;
                this.getTwList();
            },
            handleTwCurrentChange(val) {
                this.twCurrentPage = val;
                this.getTwList()
            },
            getTwList: function () {
                let that = this;
                let params = {

                    id: that.uid,
                    type: that.twSearch.type,
                    auid: that.twSearch.auid,
                    status: that.twSearch.status,
                    keyword: that.twSearch.keyword,
                    urgent: that.twSearch.urgent ? 1 : 0,
                    wcmoments: that.twSearch.wcmoments ? 1 : 0,
                    gzh: that.twSearch.gzh ? 1 : 0,
                    page: that.twCurrentPage,
                    limit: that.twLimit,
                    t: that.twSearch.sort_t,
                    order: that.twSearch.order
                }
                that.loadingtw = true;
                that.emptytexttw = '数据加载中';
                httpPost('m=crm&c=crm_my_customer&a=getTwList', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        that.twList = data.data.twList;
                        that.twTotal = parseInt(data.data.total)

                        that.twPageSizes = data.data.page_sizes
                        that.twLimit = data.data.page_size
                        if (that.prevTwPage != that.twCurrentPage) {
                            that.prevTwPage = that.twCurrentPage;
                            that.$refs.twTable.bodyWrapper.scrollTop = 0;
                        }
                        that.loadingtw = false;
                        if (that.twList.length == 0) {
                            that.emptytexttw = '暂无数据';
                        }
                    } else {
                        message.error('获取推送信息失败')
                        return false
                    }
                }).catch(function (e) {
                })
            },

            handleOrderSearch: function () {
                let _this = this;
                _this.orderCurrentPage = 1;
                _this.getOrderList();
            },
            handleResetOrderSearch: function () {
                let _this = this;

                _this.orderSearch.order_state = '';
                _this.orderSearch.order_type = '';
                _this.orderSearch.type = '';
                _this.orderSearch.time = '';
                _this.orderSearch.keyword = '';
                _this.orderSearch.sort_t = '';
                _this.orderSearch.order = '';
                _this.orderCurrentPage = 1;
                _this.getOrderList();
            },
            orderSortChange: function (e) {
                this.orderSearch.sort_t = e.prop;
                if (e.prop == 'order_time_n') {
                    this.orderSearch.sort_t = 'order_time';
                }
                this.orderSearch.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.orderCurrentPage = 1;
                this.getOrderList();
            },
            handleOrderSizeChange(val) {

                this.orderCurrentPage = 1;
                this.orderLimit = val;
                this.getOrderList();
            },
            handleOrderCurrentChange(val) {
                this.orderCurrentPage = val;
                this.getOrderList()
            },
            getOrderList: function () {
                let that = this;
                let params = {

                    id: that.uid,
                    order_state: that.orderSearch.order_state,
                    order_type: that.orderSearch.order_type,
                    type: that.orderSearch.type,
                    time: that.orderSearch.time,
                    keyword: that.orderSearch.keyword,
                    page: that.orderCurrentPage,
                    limit: that.orderLimit,
                    t: that.orderSearch.sort_t,
                    order: that.orderSearch.order
                }
                that.loadingorder = true;
                that.emptytextorder = '数据加载中';
                httpPost('m=crm&c=crm_my_customer&a=getOrderList', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        that.orderList = data.data.orderList;
                        that.orderTotal = parseInt(data.data.total)
                        that.orderPageSizes = data.data.page_sizes
                        that.orderLimit = data.data.page_size
                        if (that.prevOrderPage != that.orderCurrentPage) {
                            that.prevOrderPage = that.orderCurrentPage;
                            that.$refs.orderTable.bodyWrapper.scrollTop = 0;
                        }
                        that.orderLoading = false;
                        that.loadingorder = false;
                        if (that.orderList.length == 0) {
                            that.emptytextorder = '暂无数据';
                        }
                    } else {
                        message.error('获取订单信息失败')
                        return false
                    }
                }).catch(function (e) {
                })
            },
            addNewOrder: function () {
                let _this = this;
                _this.newDealShow = true;
            },
            closeNewDeal: function () {

                let _this = this;
                _this.getOrderList();
                _this.newDealShow = false;
            },

            handleLogSearch: function () {
                let _this = this;
                _this.logCurrentPage = 1;
                _this.getLogList();
            },
            logSortChange: function (e) {
                this.logSearch.sort_t = e.prop;
                if (e.prop == 'ctime_n') {
                    this.logSearch.sort_t = 'ctime';
                }
                this.logSearch.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.logCurrentPage = 1;
                this.getLogList();
            },
            handleLogSizeChange(val) {

                this.logCurrentPage = 1;
                this.logLimit = val;
                this.getLogList();
            },
            handleLogCurrentChange(val) {
                this.logCurrentPage = val;
                this.getLogList()
            },
            getLogList: function () {
                let that = this;
                let params = {
                    id: that.uid,
                    time: that.logSearch.time,
                    keyword: that.logSearch.keyword,
                    page: that.logCurrentPage,
                    limit: that.logLimit,
                    t: that.logSearch.sort_t,
                    order: that.logSearch.order
                }
                that.loadinglog = true;
                that.emptytextlog = "数据加载中";
                httpPost('m=crm&c=crm_my_customer&a=getMemberLogList', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        that.logList = data.data.logList;
                        that.logTotal = parseInt(data.data.total)
                        that.logPageSizes = data.data.page_sizes
                        that.logLimit = data.data.page_size
                        if (that.prevLogPage != that.logCurrentPage) {
                            that.prevLogPage = that.logCurrentPage;
                            that.$refs.logTable.bodyWrapper.scrollTop = 0;
                        }
                        that.loadinglog = false;
                        if (that.logList.length == 0) {
                            that.emptytextlog = "暂无数据";
                        }
                    } else {
                        message.error('获取日志信息失败')
                        return false
                    }
                }).catch(function (e) {
                })
            },

            handleWebLogSearch: function () {
                let _this = this;
                _this.webLogCurrentPage = 1;
                _this.getWebLogList();
            },
            webLogSortChange: function (e) {
                this.webLogSearch.sort_t = e.prop;
                if (e.prop == 'time_n') {
                    this.webLogSearch.sort_t = 'time';
                }
                this.webLogSearch.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.webLogCurrentPage = 1;
                this.getWebLogList();
            },
            handleWebLogSizeChange(val) {

                this.webLogCurrentPage = 1;
                this.webLogLimit = val;
                this.getWebLogList();
            },
            handleWebLogCurrentChange(val) {
                this.webLogCurrentPage = val;
                this.getWebLogList()
            },
            getWebLogList: function () {
                let that = this;
                let params = {

                    id: that.uid,
                    time: that.webLogSearch.time,
                    keyword: that.webLogSearch.keyword,
                    page: that.webLogCurrentPage,
                    limit: that.webLogLimit,
                    t: that.webLogSearch.sort_t,
                    order: that.webLogSearch.order
                }
                that.loadingweblog = true;
                that.emptytextweblog = "数据加载中";
                httpPost('m=crm&c=crm_my_customer&a=getWebLogList', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        that.webLogList = data.data.webLogList;
                        that.webLogTotal = parseInt(data.data.total)
                        that.webLogPageSizes = data.data.page_sizes
                        that.webLogLimit = data.data.page_size
                        if (that.prevWebLogPage != that.webLogCurrentPage) {
                            that.prevWebLogPage = that.webLogCurrentPage;
                            that.$refs.webLogTable.bodyWrapper.scrollTop = 0;
                        }
                        that.loadingweblog = false;
                        if (that.webLogList.length == 0) {
                            that.emptytextweblog = "暂无数据";
                        }
                    } else {
                        message.error('获取行为信息失败')
                        return false
                    }
                }).catch(function (e) {
                })
            },

            handleOptLogSearch: function () {
                let _this = this;
                _this.optLogCurrentPage = 1;
                _this.getOptLogList();
            },
            optLogSortChange: function (e) {
                this.optLogSearch.sort_t = e.prop;
                if (e.prop == 'ctime_n') {
                    this.optLogSearch.sort_t = 'ctime';
                }
                this.optLogSearch.order = e.order == 'ascending' ? 'asc' : 'desc';
                this.optLogCurrentPage = 1;
                this.getOptLogList();
            },
            handleOptLogSizeChange(val) {

                this.optLogCurrentPage = 1;
                this.optLogLimit = val;
                this.getOptLogList();
            },
            handleOptLogCurrentChange(val) {
                this.optLogCurrentPage = val;
                this.getOptLogList()
            },
            getOptLogList: function () {
                let that = this;
                let params = {

                    id: that.uid,
                    time: that.optLogSearch.time,
                    keyword: that.optLogSearch.keyword,
                    page: that.optLogCurrentPage,
                    limit: that.optLogLimit,
                    t: that.optLogSearch.sort_t,
                    order: that.optLogSearch.order
                }
                that.loadingoptlog = true;
                that.emptytextoptlog = "数据加载中";
                httpPost('m=crm&c=crm_my_customer&a=getCrmComLogList', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {

                        that.optLogList = data.data.optLogList;
                        that.optLogTotal = parseInt(data.data.total)
                        that.optLogPageSizes = data.data.page_sizes
                        that.optLogLimit = data.data.page_size
                        if (that.prevOptLogPage != that.optLogCurrentPage) {
                            that.prevOptLogPage = that.optLogCurrentPage;
                            that.$refs.optLogTable.bodyWrapper.scrollTop = 0;
                        }
                        that.loadingoptlog = false;
                        if (that.optLogList.length == 0) {
                            that.emptytextoptlog = "暂无数据";
                        }
                    } else {
                        message.error('获取操作日志失败')
                        return false
                    }
                }).catch(function (e) {
                })
            },
            addfollow: function () {
                var comInfo = this.comInfo;
                this.follow_id = '';
                this.follow_uid = comInfo.uid;
                this.follow_status = comInfo.crm_status;
                this.follow_type = comInfo.crm_type;
                this.follow_name = comInfo.name;
                this.follow_calllog_id = '';
                this.follow_show = true;
            },
            closeAddfollow: function () {
                this.follow_show = false;
                this.getComInfo();
                this.getFollowLog();
            },
            clueCustomer: function () {
                let that = this;
                let params = {uids: that.comInfo.uid}
                httpPost('m=crm&c=crm_my_customer&a=clueCustomer', params).then(function (res) {
                    var data = res.data;
                    if (data.error == 0) {
                        message.success(data.msg, function () {
                            that.comInfo.clue = 1;
                            // that.$emit("child-event");
                        })
                    } else {
                        message.error(data.msg)
                        return false
                    }
                }).catch(function (e) {
                })
            }
        }
    };
</script>
<style scoped>.w_110 {
    width: 110px
}

.w_150 {
    width: 150px
}

.w_180 {
    width: 180px
}

.w_260 {
    width: 260px
}

.admin_datatip {
    margin-bottom: 0
}

.ml_5 {
    margin-left: 5px
}

.ml_10 {
    margin-left: 10px
}

.rb_txt {
    color: red;
    font-weight: 700
}

.followLogOneLeft .el-form-item__content {
    width: calc(100% - 80px)
}

.followLogOneLeft .el-form-item__content .el-select {
    width: 100%
}

.followLogOneLeft .el-form-item__content .el-date-editor {
    width: 100%
}

.ollowFormrenyuan {
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center
}</style>