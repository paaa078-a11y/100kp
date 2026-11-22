<!--客户公海-->
<template>
	<div class="moduleElHight" :style="simple ? 'padding: 0 20px;' : ''" style="height: initial;">
		<!-- 简化版客户公海头部 -->
		<template v-if="simple">
			<div class="moduleElSearchInf">
				<div class="moduleElTabInpt" style="flex-wrap: wrap;">
					<div class="moduleInptList moduleInptWidt">
						<el-input size="small" @keyup.enter.native="search" placeholder="搜索关键词" v-model="keyword" class="input-with-select" clearable>
							<el-select v-model="ktype" slot="prepend" placeholder="请选择">
								<el-option label="公司名" value="1"></el-option>
								<el-option label="联系人" value="2"></el-option>
								<el-option label="电话" value="3"></el-option>
							</el-select>
						</el-input>
					</div>
					<div class="moduleInptList" style="display: flex; align-items: center;">
						<el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
						<el-button class="" size="mini" style="margin: 0 0 0 8px;" type="primary" @click="addcustomer"
							icon="el-icon-document-add">新增客户</el-button>
					</div>
				</div>
			</div>
		</template>
		<!-- 完整版客户公海头部 -->
		<template v-else>
			<el-tabs v-model="tabName" :before-leave="tabBeforeLeave">
				<el-tab-pane label="全部客户" name="1"></el-tab-pane>
				<el-tab-pane label="新客户" name="2"></el-tab-pane>
				<el-tab-pane label="高意向客户" name="3"></el-tab-pane>
				<el-tab-pane label="活跃客户" name="4"></el-tab-pane>
				<el-tab-pane label="到期客户" name="5"></el-tab-pane>
				<el-tab-pane label="潜在客户" name="6"></el-tab-pane>
				<el-tab-pane label="近期释放" name="7"></el-tab-pane>
				<el-tab-pane label="入库企业" name="8"></el-tab-pane>
				<el-tab-pane label="线索库" name="9"></el-tab-pane>
			</el-tabs>
			<div class="moduleElSearchInf">
				<div class="moduleElTabInpt" style="flex-wrap: wrap;">
					<div class="moduleInptList moduleInptWidt">
						<el-input size="small" placeholder="搜索关键词" @keyup.enter.native="search" clearable v-model="keyword" class="input-with-select">
							<el-select v-model="ktype" slot="prepend" placeholder="请选择">
								<el-option label="公司名" value="1"></el-option>
								<el-option label="联系人" value="2"></el-option>
								<el-option label="电话" value="3"></el-option>
								<el-option label="UID" value="4"></el-option>
							</el-select>
						</el-input>
					</div>
					<div class="moduleInptList">
						<el-select size="small" v-model="rating" placeholder="会员类型" clearable @change="search">
							<el-option v-for="item in ratingArr" :key="item.id" :label="item.name"
								:value="item.id"></el-option>
						</el-select>
					</div>
					<div class="moduleInptList">
						<el-select size="small" v-model="vipetime" placeholder="到期时间" clearable @change="search">
							<el-option v-for="(item, key) in vip_etimearr" :key="key" :label="item" :value="key"></el-option>
						</el-select>
					</div>
					<div class="moduleInptList">
						<el-select size="small" v-model="crmType" placeholder="客户等级" clearable @change="search">
							<el-option v-for="item in client_type" :key="item.val" :label="item.name"
								:value="item.val"></el-option>
						</el-select>
					</div>
					<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
						<el-select size="small" v-model="crmStatus" placeholder="客户标签" clearable @change="search">
							<el-option v-for="item in client_status" :key="item.val" :label="item.name"
								:value="item.val"></el-option>
						</el-select>
					</div>
					<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
						<el-select size="small" v-model="yyzz_status" placeholder="企业资质" clearable @change="search">
							<el-option v-for="(item, key) in yyzz_statusarr" :key="key" :label="item"
								:value="key"></el-option>
						</el-select>
					</div>
					<div v-if="jttype != 'new'" class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
						<el-date-picker size="small" v-model="regtime" type="daterange" value-format="yyyy-MM-dd"
							range-separator="至" start-placeholder="注册开始" end-placeholder="结束日期" style="width: 230px;"
							clearable @change="search"></el-date-picker>
					</div>
					<div v-if="jttype != 'new' && jttype != 'gyx' && jttype != 'hy' && jttype != 'qz'"
						class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
						<el-select size="small" v-model="loginDate" placeholder="最近登录" clearable @change="loginDateChange">
							<el-option v-for="(item, key) in login_datearr" :key="key" :label="item"
								:value="key"></el-option>
						</el-select>
					</div>
					<div v-if="jttype != 'new' && jttype != 'gyx' && jttype != 'hy' && jttype != 'qz'"
						class="moduleInptList" v-show="loginDate == '7'" :class="{ 'searchbutnOnff': seachbutn }">
						<el-date-picker size="small" v-model="logintime" type="daterange" value-format="yyyy-MM-dd"
							range-separator="至" start-placeholder="登录开始" end-placeholder="结束日期" style="width: 230px;"
							clearable @change="search"></el-date-picker>
					</div>
					<div v-if="jttype != 'new'" class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
						<el-select size="small" v-model="last_ftime" placeholder="上次跟进" clearable @change="lastFtimeChange">
							<el-option v-for="(item, key) in last_ftimearr" :key="key" :label="item"
								:value="key"></el-option>
						</el-select>
					</div>
					<div v-if="jttype != 'new'" class="moduleInptList" v-show="last_ftime == '6'"
						:class="{ 'searchbutnOnff': seachbutn }">
						<el-date-picker size="small" v-model="lastdate" type="daterange" value-format="yyyy-MM-dd"
							range-separator="至" start-placeholder="跟进开始" end-placeholder="结束日期" style="width: 230px;"
							clearable @change="search"></el-date-picker>
					</div>
					<div v-if="jttype != 'new' && jttype != 'jqsf'" class="moduleInptList"
						:class="{ 'searchbutnOnff': seachbutn }">
						<el-select size="small" v-model="release_time" placeholder="释放日期" clearable
							@change="releaseTimeChange">
							<el-option v-for="(item, key) in releaseTime_arr" :key="key" :label="item"
								:value="key"></el-option>
						</el-select>
					</div>
					<div v-if="jttype != 'new' && jttype != 'jqsf'" class="moduleInptList" v-show="release_time == '6'"
						:class="{ 'searchbutnOnff': seachbutn }">
						<el-date-picker size="small" v-model="releasedate" type="daterange" value-format="yyyy-MM-dd"
							range-separator="至" start-placeholder="释放开始" end-placeholder="结束日期" style="width: 230px;"
							clearable @change="search"></el-date-picker>
					</div>
					<div class="moduleInptList" :class="{ 'searchbutnOnff': seachbutn }">
					    <el-select size="small" v-model="fact_status" placeholder="实地核验" clearable @change="search">
					        <el-option label="是" value="1"></el-option>
							<el-option label="否" value="2"></el-option>
					    </el-select>
					</div>
					<!-- <div class="moduleInptList moduleInptWidt" :class="{ 'searchbutnOnff': seachbutn }">
                    <el-input size="small" placeholder="搜索关键词" v-model="keyword" class="input-with-select">
                        <el-select v-model="ktype" slot="prepend" placeholder="请选择">
                            <el-option label="公司名" value="1"></el-option>
                            <el-option label="联系人" value="2"></el-option>
                            <el-option label="电话" value="3"></el-option>
							<el-option label="UID" value="4"></el-option>
                        </el-select>
                    </el-input>
                </div> -->
					<div class="moduleInptList" style="display: flex; align-items: center;">
						<el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
						<el-button class="" size="mini" style="margin: 0 0 0 8px;" type="primary" @click="addcustomer"
							icon="el-icon-document-add">新增客户</el-button>
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
		<div v-if="!simple" class="admin_datatip">
			<i class="el-icon-document"></i>
			公海客户：
			<span class="admin_datatip_n"><el-link type="primary" :underline="false"
					@click="rstatusChange('')">{{ numarr.companyAllNum }}</el-link> 位客户 </span>
			<span class="admin_datatip_n">未审核：<el-link type="primary" :underline="false"
					@click="rstatusChange(1)">{{ numarr.companyStatusNum1 }}</el-link> </span>
			<span class="admin_datatip_n">未通过：<el-link type="primary" :underline="false"
					@click="rstatusChange(3)">{{ numarr.companyStatusNum2 }}</el-link> </span>
			<span class="admin_datatip_n">已锁定：<el-link type="primary" :underline="false"
					@click="rstatusChange(2)">{{ numarr.companyStatusNum3 }}</el-link> </span>
			<span class="admin_datatip_n">暂停招聘：<el-link type="primary" :underline="false"
					@click="rstatusChange(4)">{{ numarr.companyStatusNum4 }}</el-link> </span>
		</div>

		<div :class="{ 'moduleElTable': true, 'moduleElMoreCrmcus': tableHig }"
			:style="'border: 1px solid #ebeef5; width: calc(100% - 2px); ' + (simple ? '' : 'height: calc(100% - 200px);')">
			<el-table ref="table" :data="tableData" stripe style="width: 100%" v-loading="list_loading"
				@mousedown.native="mouseDownHandler"
                @mouseup.native="mouseUpHandler"
                @mousemove.native="mouseMoveHandler"
				@selection-change="selectionChange" :default-sort="{ prop: 'id', order: 'descending' }"
				@sort-change="sortChange" :empty-text="emptytext"
				:header-cell-style="{ background: '#f5f7fa', color: '#606266' }">
				<el-table-column type="selection" width="55">
				</el-table-column>
				<el-table-column prop="uid" label="UID" width="80" sortable="custom">
				</el-table-column>
				<el-table-column label="客户名称" min-width="180">
					<template slot-scope="scope">
						<div class=" ">
							<span v-if="sy_crm_link == '1'"><el-link type="text"
									@click="openComInfo(scope)">{{ scope.row.name }}</el-link></span><!--管理弹窗-->
							<span v-else-if="scope.row.name != ''">{{ scope.row.name }}</span>
							<span v-else>尚未完善基本信息</span>
							<span style="color:#f00" v-if="scope.row.r_status == 0">未审核</span>
							<span style="color:#f00" v-else-if="scope.row.r_status == 2">已锁定</span>
							<span style="color:#f00" v-else-if="scope.row.r_status == 3">未通过</span>
							<span style="color:#f00" v-else-if="scope.row.r_status == 4">已暂停</span>
							<div v-if="sy_crm_link == '1'">{{ scope.row.username }}</div>
						</div>
						<div class="moduCrmIcon">
							<comcert :key="scope.row.uid" :row="scope.row" :config="config" @close-update="closeComcert">
							</comcert>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="等级/到期时间" min-width="150" prop="vipetime" sortable="custom">
					<template slot-scope="scope">
						<div class="moduleProps">
							<div v-if="scope.row.isvip == 1">{{ scope.row.rating_name }}</div>
							<div v-else>{{ scope.row.oldrating_name ? scope.row.oldrating_name : scope.row.rating_name }}</div>
							<div>
								<div :style="scope.row.vipetime_status == '2' ? 'color:#ff2b00;' : ''">{{ scope.row.vipetime_n }}
								</div>
								<div v-if="scope.row.r_status != '4' && scope.row.vipetime_status != '0'"
									:style="scope.row.vipetime_status == '2' ? 'color:#ff2b00;' : 'color:#409eff;'">
									({{ scope.row.vipDay }})</div>
								<div v-if="scope.row.r_status == '4'" style="color:#ff2b00;">{{ scope.row.ztDay }}</div>
							</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="联系信息" width="160">
					<template slot-scope="scope">
						<div class="moduleProps">
							<div>{{ scope.row.linkman }}</div>
							<div v-if="sy_crm_link == '1'">{{ scope.row.linktel }}</div>
							<div class="gsd">
								{{ scope.row.job_city_one }}{{ scope.row.job_city_two }}{{ scope.row.job_city_three }}</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="注册/登录" width="120" prop="login_date" sortable="custom">
					<template slot-scope="scope">
						<div class="moduleProps">
							<div v-html="scope.row.reg_date_n"></div>
							<div v-html="scope.row.login_date_n"></div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="等级/标签" width="150">
					<template slot-scope="scope">
						<div class="moduleProps">
							<div>客户等级：{{ scope.row.crm_type ? scope.row.crm_type : '--' }}</div>
							<div>客户标签：{{ scope.row.crm_status ? scope.row.crm_status : '--' }}</div>
						</div>
					</template>
				</el-table-column>

				<el-table-column label="跟进时间" width="150" prop="f_time" sortable="custom">
					<template slot-scope="scope">
						<div class="moduleProps">
							<el-popover placement="right" trigger="hover" :disabled="typeof scope.row.c_desc == 'undefined' || scope.row.c_desc == ''">
								<div style='overflow: hidden;'>
									<div style='font-size:16px;font-weight: bold;color:#333;line-height:40px;background:#f9f9f9;padding-left:10px;'>最近跟进记录</div>
									<div>
										<div style='overflow: hidden;padding-left:10px;line-height:32px;font-size:14px;display: flex;align-items: center; margin:5px 0;'>
											<p style='color:#666;'>{{ scope.row.c_name }}</p>
											<span style='color:#666; background: rgba(120, 186, 50, 0.2); color:#78BA32;border-radius: 5px;display: block;margin-left:15px;padding:0 10px;'>{{ scope.row.c_type }}</span>
										</div>
										<div style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>跟进时间：{{ scope.row.c_time }}</div>
										<div style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;' v-html="'跟进内容：' + scope.row.c_desc"></div>
										<div v-if="scope.row.crm_pic_arr" style='overflow: hidden;color:#666;padding-left:10px;line-height:32px;font-size:14px;'>
											<img v-for="(pic, key) in scope.row.crm_pic_arr" :key="key" :src="pic" width='40' height='40' />
										</div>
									</div>
								</div>
								<div slot="reference" class="gsd">{{ scope.row.lastTime }}</div>
							</el-popover>
							<el-tooltip class="item" effect="dark" :content="'计划于'+scope.row.nextTime+'再次跟进'" placement="left" v-if="scope.row.nextTime">
								<div class="gsd">{{ scope.row.nextTime }}</div>
							</el-tooltip>
						</div>
					</template>
				</el-table-column>
				<el-table-column prop="release_time" label="释放时间" width="150" sortable="custom">
					<template slot-scope="scope">
						<div>{{ scope.row.release_time_n }}</div>
					</template>
				</el-table-column>
				<el-table-column fixed="right" label="操作" width="190">
					<template slot-scope="scope">
						<div class="moduleElTaCaoz">
							<el-button v-if="sy_crm_link == '1'" size="mini"
								@click="openComInfo(scope)">管理</el-button><!--管理弹窗-->
							<el-button v-if="scope.row.crm_uid != ''" size="mini"
								@click="crmnewReceive(scope.row.uid)">认领</el-button>
							<el-button v-if="clue == '2'" size="mini" @click="clueMyCustomer(scope.row.uid)">转企业库</el-button>
							<el-button size="mini" @click="openPersona(scope)">画像</el-button>
						</div>
					</template>
				</el-table-column>
			</el-table>
		</div>
		<div class="modulePaging" style="height: initial; flex-wrap: wrap; padding-top: 10px;">
			<div class="modulecz" style="width:100%;">
				<el-checkbox v-model="allchecked" @change="allcheckChange">全选</el-checkbox>
				<el-button size="mini" type="primary" @click="crmnewReceiveAll">认领客户</el-button>
				<el-button size="mini" @click="clueMyCustomerAll" v-if="clue == '2'">转企业库</el-button>
			</div>
			<div class="modulePagNum" style="padding-top: 8px;">
				<el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
					:current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total"
					layout="total, sizes, prev, pager, next, jumper">
				</el-pagination>
			</div>
		</div>

		<!--录客户-->
		<div class="modluDrawer">
			<el-drawer title="录入客户" :close-on-press-escape="false" :wrapper-closable="false" :visible.sync="addcustomer_show" append-to-body :show-close="true" :with-header="true" size="60%">
				<addcustomer @close-update="closeAddcustomer" v-if="addcustomer_show"></addcustomer>
			</el-drawer>
		</div>
		<!--弹窗：客户管理-->
		<div class="modluDrawer">
			<el-drawer title="客户管理" :visible.sync="comInfoDrawer" :append-to-body="true" :show-close="true" :with-header="true" size="80%">
				<div class="modluDrawerKehuguanli">
				<com_info ref="comInfo" :cuid="comuid" v-if="comInfoDrawer"></com_info>
				</div>
			</el-drawer>
		</div>
		<!-- 客户画像 -->
		<div class="modluDrawer">
			<el-drawer title="企业画像" :close-on-press-escape="false" :visible.sync="personaShow" :append-to-body="true" size="60%">
				<huaxiang :comid="comuid" v-if="personaShow"></huaxiang>
			</el-drawer>
		</div>
	</div>
</template>

<script>
module.exports = {
	props: {
		simple: { // 简化客户公海页面，为true没有头部和tab切换，搜索栏仅保留公司名称搜索
			type: Boolean,
			default: false
		},
		source: { // 加载来源
			type: String,
			default: ''
		},
		source_data: { // 来源数据
			type: Object,
			default: null
		},
		paramdata: {
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
				this.getList();
			}
		}
	},
	data: function () {
		return {
			mouseFlag: false,
            mouseOffset: 0,
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
			releaseTime_arr: [],
			has_jobarr: [],
			login_datearr: [],
			numarr: [],
			tiparr: [],
			adminUserList: [],
			sy_crm_link: '',

			clue: '',
			loginDate: '',
			crm_uid: '',
			crmType: '',
			crmStatus: '',
			fact_status:'',
			rating: '',
			vipetime: '',
			yyzz_status: '',
			last_ftime: '',
			release_time: '',
			hasjob: '',
			logintime: [],
			lastdate: [],
			releasedate: [],
			regtime: [],
			keyword: '',
			ktype: '1',
			r_status: '',
			lastF: '',
			vipstime: '',
			jttype: '',
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
			islook: false,

			addcustomer_show: false,
			isst: '',
			cuid: '',

			comInfoDrawer: false,
			comuid: '',

			personaShow: false,
		}
	},
	components: {
		'addcustomer': httpVueLoader('./addcustomer.vue'),
		'comcert': httpVueLoader('./comcert.vue'),
		'com_info': httpVueLoader('./comInfo.vue'),
		'huaxiang': httpVueLoader('./huaxiang.vue'),
	},
	mounted() {
		let paramdata = this.paramdata
		if (paramdata) {
			for (let i in paramdata) {
				this[i] = paramdata[i]
			}
		}
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




		tabBeforeLeave: function (n, o) {

			var that = this;

			this.resetsearch()

			switch (n) {
				case '1':
					break;
				case '2':
					that.jttype = 'new';
					that.regtime = [formatDate(new Date()), formatDate(new Date())];
					break;
				case '3':
					that.jttype = 'gyx';
					break;
				case '4':
					that.jttype = 'hy';
					break;
				case '5':
					that.jttype = 'dq';
					break;
				case '6':
					that.jttype = 'qz';
					break;
				case '7':
					that.jttype = 'jqsf';
					break;
				case '8':
					that.clue = 1;
					break;
				case '9':
					that.clue = 2;
					break;
				default:
					that.clue = '';
			}

			this.getList();
		},

		resetsearch: function () {
			this.logpopshow = false;
			this.clue = '';
			this.loginDate = '';
			this.crm_uid = '';
			this.crmType = '';
			this.crmStatus = '';
			this.fact_status = '';
			this.rating = '';
			this.vipetime = '';
			this.yyzz_status = '';
			this.last_ftime = '';
			this.release_time = '';
			this.hasjob = '';
			this.logintime = [];
			this.lastdate = [];
			this.releasedate = [];
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
				crm_uid: that.crm_uid,
				crmType: that.crmType,
				crm_status: that.crmStatus,
				fact_status:that.fact_status,
				clue: that.clue,
				rating: that.rating,
				vipetime: that.vipetime,
				yyzz_status: that.yyzz_status,
				lastFtime: that.last_ftime,
				releaseTime: that.release_time,
				ktype: that.ktype,
				r_status: that.r_status,
				lastF: that.lastF,
				vipstime: that.vipstime,
			}

			if (this.source) {
				params.source = this.source;
				// 工作台跳转，搜索参数处理
				if (this.source == 'jobtai' && this.source_data) {
					if (this.source_data.type == 'new') {
						this.regtime = [
							formatDate(new Date()),
							formatDate(new Date())
						];
					} else {
						params.jttype = this.source_data.type;
					}
				}
			} else {
				params.jttype = this.jttype;
			}

			if (this.logintime && this.logintime.length > 0) {
				params.logintime_s = this.logintime[0];
				params.logintime_e = this.logintime[1];
			}
			if (this.regtime && this.regtime.length > 0) {
				params.regtime_s = this.regtime[0];
				params.regtime_e = this.regtime[1];
			}
			if (this.lastdate && this.lastdate.length > 0) {
				params.lastdate_s = this.lastdate[0];
				params.lastdate_e = this.lastdate[1];
			}
			if (this.releasedate && this.releasedate.length > 0) {
				params.releasedate_s = this.releasedate[0];
				params.releasedate_e = this.releasedate[1];
			}

			this.list_loading = true;
			that.emptytext = "数据加载中";
			httpPost('m=crm&c=crm_customer', params).then((result) => {
				this.list_loading = false;
				var res = result.data;

				if (res.error == 0) {
					that.tableData = res.data.list;
					that.total = parseInt(res.data.total)
					that.page_sizes = res.data.page_sizes;
					that.limit = res.data.page_size;
                    if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        that.$refs.table.bodyWrapper.scrollTop = 0;
                        scrollToTop()
                    }
					if (that.tableData.length === 0) {
						that.emptytext = "暂无数据";
					}
				}
				this.islook = true;
			}).catch(function (e) {
				console.log(e)
			})
		},
		getStaticData: function () {
			var that = this
			let params = {}
			httpPost('m=crm&c=crm_customer&a=getStaticData', params, { hideloading: true }).then((result) => {
				var res = result.data;
				if (res.error == 0) {
					that.config = res.data.config;
					that.sy_crm_link = res.data.sy_crm_link;

					that.numarr = res.data.numarr;
					that.client_type = res.data.client_type;
					that.client_status = res.data.client_status;
					that.ratingArr = res.data.ratingArr;
					that.yyzz_statusarr = res.data.yyzz_statusarr;
					that.vip_etimearr = res.data.vip_etimearr;
					that.releaseTime_arr = res.data.releaseTime_arr;
					that.last_ftimearr = res.data.last_ftimearr;
					that.login_datearr = res.data.login_datearr;
				}
			});
		},
		rstatusChange: function (r_status) {
			this.resetsearch();
			this.r_status = r_status;
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

		loginDateChange: function () {
			if (this.loginDate != '7') {
				this.logintime = [];
				this.search();
			}
		},
		lastFtimeChange: function () {
			if (this.last_ftime != '6') {
				this.lastdate = [];
				this.search();
			}
		},
		releaseTimeChange: function () {
			if (this.release_time != '6') {
				this.releasedate = [];
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
		clueMyCustomer: function (uid) {
			var _this = this;

			var params = {
				uids: uid
			};
			delConfirm(_this, params, this.cluePost, '确认转企业库？')
		},
		clueMyCustomerAll: function () {
			var _this = this;
			var idarr = [];
			if (this.choosedata.length > 0) {
				for (let i in this.choosedata) {
					idarr.push(this.choosedata[i].uid);
				}
			} else {
				message.error('请选择要转换的线索！'); return;
			}
			var params = {
				uids: idarr,
			};

			delConfirm(_this, params, this.cluePost, '确认转企业库？')
		},
		async cluePost(params) {

			let that = this;

			httpPost('m=crm&c=crm_customer&a=clueCustomer', params).then(function (result) {

				var res = result.data;
				if (res.error == 0) {
					message.success(res.msg, function () { that.getList(); }); return;
				} else {
					message.error(res.msg); return;
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
		crmnewReceive: function (uid) {
			var _this = this;

			var params = {
				uids: uid
			};
			delConfirm(_this, params, this.receivePost, '确定要认领该客户？')
		},
		crmnewReceiveAll: function () {
			var _this = this;
			var idarr = [];
			if (this.choosedata.length > 0) {
				for (let i in this.choosedata) {
					idarr.push(this.choosedata[i].uid);
				}
			} else {
				message.error('请选择要领取的客户！'); return;
			}
			var params = {
				uids: idarr.join(',')
			};

			delConfirm(_this, params, this.receivePost, '确定要认领选中客户？')
		},
		async receivePost(params) {

			let that = this;

			httpPost('m=crm&c=crm_customer&a=claimCom', params).then(function (result) {

				var res = result.data;
				if (res.error == 0) {
					message.success(res.msg, function () { that.getList(); }); return;
				} else {
					message.error(res.msg); return;
				}
			}).catch(function (e) {
				console.log(e)
			})
		},

		tocomjob: function (params) {
			window.parent.homeapp.checkMenuTwo(1, 6, 40, '职位管理', '/companyjob', params);
		},
		closeComcert: function () {
			this.search();
		},
		openComInfo: function (scope) {
			this.comInfoDrawer = true;
			this.comuid = scope.row.uid
		},
		openPersona: function (scope) {
			this.personaShow = true;
			this.comuid = scope.row.uid
		},

	},
};
</script>
<style scoped>.el-button--primary.is-plain {
	background: #f4f4f5;
	border: none;
}

.el-button--primary.is-plain:hover {
	color: #409eff;
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
}</style>