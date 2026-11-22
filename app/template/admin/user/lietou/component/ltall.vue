<template>
	<div class="moduleElHight">
		<div class="moduleElSearchInf">
			<div class="moduleElTabInpt">
				<div class="moduleSeachleft">
					<div class="moduleInptList">
						<el-input placeholder="请输入搜索内容" @keyup.enter.native="doUserQuery" v-model="search.keyword" class="input-with-select" size="small"
							clearable>
							<el-select v-model="search.type" slot="prepend" placeholder="请选择">
								<el-option v-for="(value, key) in typeArr" :key="key" :label="value"
									:value="key"></el-option>
							</el-select>
						</el-input>
					</div>
					<div class="tableSeachInpt tableSeachInptsmall">
						<el-select v-model="search.rec" size="small" slot="prepend" placeholder="推荐状态" clearable @change="doUserQuery">
							<el-option v-for="(value, key) in recArr" :key="key" :label="value" :value="key"></el-option>
						</el-select>
					</div>
					<div class="tableSeachInpt tableSeachInptsmall">
						<el-select v-model="search.status" size="small" slot="prepend" placeholder="审核状态" clearable @change="doUserQuery">
							<el-option v-for="(value, key) in statusArr" :key="key" :label="value" :value="key"></el-option>
						</el-select>
					</div>
					<div class="tableSeachInpt tableSeachInptsmall">
						<el-select v-model="search.register" size="small" slot="prepend" placeholder="注册时间" clearable @change="doUserQuery">
							<el-option v-for="(item, index) in time" :key="index" :label="item.label"
								:value="item.value"></el-option>
						</el-select>
					</div>
					<div class="tableSeachInpt tableSeachInptsmall">
						<el-select v-model="search.login" size="small" slot="prepend" placeholder="登录时间" clearable @change="doUserQuery">
							<el-option v-for="(item, index) in time" :key="index" :label="item.label"
								:value="item.value"></el-option>
						</el-select>
					</div>
					<div class="tableSeachInpt tableSeachInptsmall">
						<el-select v-model="search.rating" size="small" slot="prepend" placeholder="会员等级" clearable @change="doUserQuery">
							<el-option v-for="(value, key) in ratingArr" :key="key" :label="value" :value="key"></el-option>
						</el-select>
					</div>
					<div class="tableSeachInpt">
						<el-button type="primary" icon="el-icon-search" size="mini" @click="doUserQuery">查询</el-button>
					</div>
				</div>
			</div>
		</div>
		<div class="admin_datatip">
			<i class="el-icon-document"></i>
			数据统计：共 {{ data.ltAllNum }} 条
			<span class="admin_datatip_n" @click="lockList('1')">未审核：{{ data.ltStatusNum1?data.ltStatusNum1:0 }} 条 </span>
			<span class="admin_datatip_n" @click="lockList('2')">未通过：{{ data.ltStatusNum2?data.ltStatusNum2:0 }} 条</span>
			<span class="admin_datatip_n" @click="lockList('3')">已锁定：{{ data.ltStatusNum3?data.ltStatusNum3:0 }} 条</span>
			<span class="admin_datatip_n">搜索结果： {{ total }} 条</span>
		</div>
		<div class="moduleElTable">
			<el-table :data="tableData" stripe style="width: 100%; border: 1px solid rgb(235, 238, 245);"
				@selection-change="selectChange" @sort-change="shortChange" ref="multipleTable"
				:header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
				<template slot="empty">
					<p>{{ dataText }}</p>
				</template>
				<el-table-column type="selection" width="55">
				</el-table-column>
				<el-table-column prop="uid" label="用户ID" width="90" sortable="custom">
				</el-table-column>
				<el-table-column label="公司名称/用户名" min-width="180">
					<template slot-scope="props">
						<div class="moduleProps">
                            <span class="yhm"> {{ props.row.com_name }}</span>
                            <el-link @click="tomember(props.row.uid, props.row.usertype)">{{ props.row.username }}</el-link>
                            <el-tooltip v-if="props.row.r_status == 2" class="item" effect="dark" content="已锁定"
                                        placement="top-start">
                                <i class="el-icon-lock" style="color: orange"></i>
                            </el-tooltip>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="会员等级" min-width="180">
					<template slot-scope="props">
						<div class="moduleDengGai">
							<span>{{ props.row.rating_name }}</span>
							<el-button type="text" @click="updateUserLevel(props.row)">修改</el-button>
						</div>

						<!-- <span style="color: #2778F8;" @click="editVipLevelFun(props.row.uid)">修改</span> -->



						<div class="moduleProps">
							<span class="gsd">{{ props.row.vip_etime_ymd }}</span>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="企业认证" width="200">
					<template slot-scope="scope">
						<div class="rz_box">
							<template>
								<el-tooltip v-if="scope.row.yyzz_status == 1" class="item" effect="dark" content="营业执照已认证"
									placement="top-start">
									<el-button type="text" @click="drawerFun('yyzz', scope.row)">
										<i class="rzicon rzicon_zzyrz"></i>
									</el-button>
								</el-tooltip>
								<el-tooltip v-else class="item" effect="dark" content="营业执照未认证" placement="top-start">
									<el-button type="text" @click="drawerFun('yyzz', scope.row)">
										<i class="rzicon rzicon_zzwrz"></i>
									</el-button>
								</el-tooltip>
							</template>
							<template>
								<el-tooltip v-if="scope.row.moblie_status == 1" class="item" effect="dark" content="手机已认证"
									placement="top-start">
									<el-button type="text" @click="drawerFun('moblie', scope.row)">
										<i class="rzicon rzicon_sjyrz"></i>
									</el-button>
								</el-tooltip>
								<el-tooltip v-else class="item" effect="dark" content="手机未认证" placement="top-start">
									<el-button type="text" @click="drawerFun('moblie', scope.row)">
										<i class="rzicon rzicon_sjwrz"></i>
									</el-button>
								</el-tooltip>
							</template>

							<template>
								<el-tooltip v-if="scope.row.email_status == 1" class="item" effect="dark" content="邮箱已认证"
									placement="top-start">
									<el-button type="text" @click="drawerFun('email', scope.row)">
										<i class="rzicon rzicon_yxyrz"></i>
									</el-button>
								</el-tooltip>
								<el-tooltip v-else class="item" effect="dark" content="邮箱未认证" placement="top-start">
									<el-button type="text" @click="drawerFun('email', scope.row)">
										<i class="rzicon rzicon_yxwrz"></i>
									</el-button>
								</el-tooltip>
							</template>

							<template>
								<el-tooltip v-if="scope.row.wxid != ''" class="item" effect="dark" content="微信已认证"
									placement="top-start">
									<el-button type="text">
										<i class="rzicon rzicon_wxyrz"></i>
									</el-button>
								</el-tooltip>
								<el-tooltip v-else class="item" effect="dark" content="微信未认证" placement="top-start">
									<el-button type="text">
										<i class="rzicon rzicon_wxwrz"></i>
									</el-button>
								</el-tooltip>
							</template>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="手机号/EMAIL" width="200">
					<template slot-scope="props">
						<div class="moduleProps">
							<span>{{ props.row.moblie }}</span>
							<span>{{ props.row.email }}</span>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="注册/登录" width="180">
					<template slot-scope="props">
						<div class="moduleProps">
                            <span>{{ props.row.reg_date_ymd }}</span>
							<span class="gsd">{{ props.row.login_date_ymd ? props.row.login_date_ymd : '未登录' }}</span>
						</div>
					</template>
				</el-table-column>
				<el-table-column prop="zt" label="状态">
					<template slot-scope="props">
						<div class="admin_state">
							<span v-if="props.row.r_status == '1'" class="admin_state1">已审核</span>
							<span v-else-if="props.row.r_status == '3'" class="admin_state2">未通过</span>
							<span v-else-if="props.row.r_status == '2'" class="admin_state3">已锁定</span>
							<span v-else class="admin_state4">待审核</span>
							<!--							<span class="admin_state5">已暂停</span>-->
						</div>
					</template>
				</el-table-column>
				<el-table-column label="站点" width="130">
					<template slot-scope="props">
						<div class="moduleProps">
							<span>{{ dname[props.row.did] }}</span>
							<el-button type="text" @click="shareZhan(props.row)" style="padding: 0;">分配</el-button>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="推荐" width="120">
					<template slot-scope="scope">
						<div class="moduleProps">
							<el-switch v-model="scope.row.rec" active-value="1" inactive-value="0"
								@change="recFun(scope.row)">
							</el-switch>
						</div>
					</template>
				</el-table-column>
				<el-table-column fixed="right" label="操作" width="140">
					<template slot-scope="scope">
						<div class="cz_button">
							<el-button size="mini" @click="drawerFun('shenhe', scope.row)">审核</el-button>
							<el-button size="mini" @click="editDetail(scope.row)">修改</el-button>
						</div>
						<div class="cz_button" style="margin-top: 10px;">
							<el-button size="mini" @click="showLog(scope.row)">日志</el-button>
							<el-popover placement="bottom" width="90" trigger="hover">
								<div class="moduleMores">
									<el-button type="text" @click="resetPassword(scope.row)">重置密码</el-button>
									<el-button type="text" @click="del(scope.row)">删除会员</el-button>
								</div>
								<el-button size="small" plain slot="reference" @click="visible = !visible">更多
								</el-button>
							</el-popover>
						</div>
					</template>
				</el-table-column>
			</el-table>
		</div>
		<div class="modulePaging">
			<div>
				<el-checkbox v-model="checkedAll" :indeterminate="isIndeterminate"
					@change="selectAllBottom">全选</el-checkbox>
				<el-button @click="batchDel" size="mini">批量删除</el-button>
				<el-button @click="batchShenhe" size="mini">批量审核</el-button>
				<el-button @click="batchRenZheng" size="mini">批量认证</el-button>
			</div>
			<div class="modulePagNum">
				<el-pagination :total="total" @current-change="userPageChange" :page-size="pageSize" :page-sizes="pageSizes"
					@size-change="userPageSizeChange" :current-page.sync="page"
					layout="total, sizes, prev, pager, next, jumper" :pager-count="pagerCount">
				</el-pagination>
			</div>
		</div>

		<div class="modluDrawer">
			<el-dialog title="批量认证" :visible.sync="renzhengDrawer" :with-header="true" :modal-append-to-body="false"
				:show-close="true" width="500px">
				<div class="toolClasDia fenpeizhand">

					<div class="toolClasList">
						<div class="toolClasTite">
							<span>认证类型：</span>
						</div>
						<div class="toolClasCont">
							<el-checkbox-group v-model="renzhengType">
								<el-checkbox label="ltname_email">邮箱</el-checkbox>
								<el-checkbox label="ltname_moblie">手机</el-checkbox>
								<el-checkbox label="ltname_yyzz">营业执照</el-checkbox>
							</el-checkbox-group>
						</div>
					</div>
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>认证操作：</span>
						</div>
						<div class="toolClasCont">
							<el-radio-group v-model="renzhengValue">
								<el-radio label="0">待认证</el-radio>
								<el-radio label="1">已认证</el-radio>
							</el-radio-group>
						</div>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="renzhengDrawer = false">取 消</el-button>
					<el-button type="primary" @click="renzhengSave" :disabled="submitLoading">确 定</el-button>
				</span>
			</el-dialog>
		</div>

		<div class="modluDrawer">
			<el-dialog :title="drawerTitle" :visible.sync="drawer3" :with-header="true" :modal-append-to-body="false"
				:show-close="true" width="500px">
				<div class="toolClasDia fenpeizhand">

					<div class="toolClasList" v-if="drawerType == 'email' || drawerType == 'moblie'">
						<div class="toolClasTite">
							<span>{{ titleColumn }}</span>
						</div>
						<div class="toolClasCont">
							<el-input v-model="titleColumnValue"></el-input>
						</div>
					</div>
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>{{ radioTitle }}</span>
						</div>
						<div class="toolClasCont" v-if="drawerType == 'shenhe' || drawerType == 'batchShenhe'">
							<el-radio-group v-model="drawerRadio">
								<el-radio label="1">已审核</el-radio>
								<el-radio label="3">未通过</el-radio>
							</el-radio-group>

						</div>
						<div class="toolClasCont" v-else>
							<el-radio-group v-model="drawerRadio">
								<el-radio v-if="drawerType == 'yyzz'" label="0">待认证</el-radio>
								<el-radio label="1">已认证</el-radio>
							</el-radio-group>
						</div>
					</div>
					<template>
						<div class="toolClasList" v-if="drawerType == 'yyzz'">
							<div class="toolClasTite">
								<span>认证执照：</span>
							</div>
							<div class="toolClasCont" v-if="detail.check">
								<el-avatar shape="square" :size="50" :src="detail.check"></el-avatar>
								<span @click="chakanPhoto(detail.check)">查看大图</span>
							</div>
							<div class="toolClasCont" v-else>
								暂无营业执照
							</div>
						</div>
						<div class="toolClasList"
							v-if="drawerType == 'yyzz' || drawerType == 'shenhe' || drawerType == 'batchShenhe'">
							<div class="toolClasTite">
								<span>审核说明：</span>
							</div>
							<div class="toolClasCont">
								<el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="drawerTextarea">
								</el-input>
							</div>
						</div>
					</template>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="drawer3 = false">取 消</el-button>
					<el-button type="primary" @click="drawerSave" :disabled="submitLoading">确 定</el-button>
				</span>
			</el-dialog>
		</div>

		<div class="modluDrawer">
			<el-dialog title="猎头会员等级修改" :visible.sync="drawer6" :with-header="true" :modal-append-to-body="false"
				:show-close="true" width="630px">
				<div class="huiyuanDeng">
					<div class="huiyuanList">
						<div class="huiyuanTite">
							<span>会员等级</span>
						</div>
						<div class="huiyuanFrom">
							<el-select v-model="userLevelArr.rating" placeholder="请选择" @change="getRatingInfo">
								<el-option key="0" label="请选择" value="0"></el-option>
								<el-option v-for="(value, key) in ratingArr" :key="key" :label="value"
									:value="key"></el-option>
							</el-select>
						</div>
					</div>
					<div class="huiyuanList">
						<div class="huiyuanTite">
							<span>账户积分</span>
						</div>
						<div class="huiyuanFrom">
							<el-input v-model="integral" placeholder="请输入内容"></el-input>
						</div>
					</div>
					<div class="huiyuanList">
						<div class="huiyuanTite">
							<span>会员到期时间</span>
						</div>
						<div class="huiyuanFrom">
							<span>{{ userLevelArr.vipetime }}</span>
						</div>
					</div>
					<div class="huiyuanList">
						<div class="huiyuanTite">
							<span>延长会员有效期</span>
						</div>
						<div class="huiyuanFrom">
							<el-date-picker v-model="userLevelArr.vipetime" type="date" placeholder="选择日期">
							</el-date-picker>
						</div>
					</div>
					<div class="huiyuanList">
						<div class="huiyuanTite">
							<span>发布职位数</span>
						</div>
						<div class="huiyuanFrom">
							<el-input v-model="userLevelArr.lt_job_num" placeholder="请输入内容"></el-input>
						</div>
					</div>
					<div class="huiyuanList">
						<div class="huiyuanTite">
							<span>简历下载数</span>
						</div>
						<div class="huiyuanFrom">
							<el-input v-model="userLevelArr.lt_down_resume" placeholder="请输入内容"></el-input>
						</div>
					</div>
					<div class="huiyuanList">
						<div class="huiyuanTite">
							<span>刷新职位</span>
						</div>
						<div class="huiyuanFrom">
							<el-input v-model="userLevelArr.lt_breakjob_num" placeholder="请输入内容"></el-input>
						</div>
					</div>
					<div class="huiyuanList">
						<div class="huiyuanTite">
							<span>直聊对象数</span>
						</div>
						<div class="huiyuanFrom">
							<el-input v-model="userLevelArr.chat_num" placeholder="请输入内容"></el-input>
						</div>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="drawer6 = false">取 消</el-button>
					<el-button type="primary" @click="savaUserLevel" :disabled="submitLoading">确 定</el-button>
				</span>
			</el-dialog>
		</div>
		<!-- 猎头基本信息弹出框-->
		<el-drawer title="猎头基本信息" :append-to-body="true" :visible.sync="infoDrawer" size="60%">
			<div class="tanchuangkoul">
				<div v-if="JSON.stringify(loginInfo) != '{}'" class="admin_datatip"><i class="el-icon-document"></i>
					注册IP：{{ loginInfo.reg_ip }} 注册时间：{{ loginInfo.reg_date_ymd }} 登录IP：{{ loginInfo.login_ip }} 最近登录时间：{{
						loginInfo.login_date_ymd }}
					登录次数：{{ loginInfo.login_hits }}
				</div>
				<el-tabs v-model="activeName" @tab-click="handleClick">
					<el-tab-pane label="基本信息" name="first">
						<ltedit :uid="detail.uid" @save-getlist="infoDrawer = false; doUserQuery()"></ltedit>
					</el-tab-pane>
					<el-tab-pane label="账户信息" name="second">
						<lemember :member="loginInfo" @save-getlist="infoDrawer = false; doUserQuery()"></lemember>
					</el-tab-pane>
					<el-tab-pane label="用户套餐" name="third">
						<lttc :ratingarr="ratingArrL" :uid="detail.uid"></lttc>
					</el-tab-pane>
				</el-tabs>
			</div>
		</el-drawer>
		<!--分配站点弹窗-->
		<div class="modluDrawer">
			<el-dialog title="分配站点" :visible.sync="zhandianDrawer" :with-header="true" :modal-append-to-body="false"
				:show-close="true" width="450px">
				<div class="wxsettip_small ">当前用户</div>
				<el-input placeholder="企业用户" v-model="shareZhanArr.username" :disabled="true"></el-input>
				<div>
					<div class="wxsettip_small ">分配站点</div>
					<div class="wxsettip_Sealect">
						<el-select v-model="shareZhanArr.did" size="small" slot="prepend" placeholder="使用范围" filterable>
							<el-option v-for="(value, key) in dname" :key="key" :label="value" :value="key"></el-option>
						</el-select>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="zhandianDrawer = false">取 消</el-button>
					<el-button type="primary" @click="shareSave" :disabled="submitLoading">确 定</el-button>
				</span>
			</el-dialog>
		</div>
	</div>
</template>
    
<script>
module.exports = {
	props: {
		status: { type: String, default: '' },
	},
	data: function () {
		return {
			loading: false,
			pagerCount: 5,
			dataText: '数据加载中',
			// 统计
			submitLoading: false,
			data: {},
			input3: '',
			checkedAll: false,
			isIndeterminate: false,// checkbox 的不确定状态
			value: true,
			textarea: '',
			drawer3: false,
			drawer6: false,
			activeName: 'first',
			infoDrawer: false,
			czuserdrawer: false,
			seachbutn: true,
			tableHig: false,
			radio: '1',
			tximage: 'https://cube.elemecdn.com/3/7c/3ea6beec64369c2642b92c6726f1epng.png',
			tableData: [],
			options: [{
				value: '选项1',
				label: '黄金糕'
			}, {
				value: '选项2',
				label: '双皮奶'
			}],
			time: [{
				value: 1,
				label: "今天"
			}, {
				value: 3,
				label: "最近三天"
			}, {
				value: 7,
				label: "最近七天"
			}, {
				value: 15,
				label: "最近半月"
			}, {
				value: 30,
				label: "最近一个月"
			}],
			//  参数
			uri: "m=user&c=",
			total: 0,
			page: 1,
			idsArr: [],
			pageSize: 0,
			pageSizes: [],
			search: {
				rec: '',
				rating: '',
				register: '',
				status: this.status,
				lotime: '',
				adtime: '',
				login: '',
				type: '1'
			},
			typeArr: {
				1: '用户名',
				2: '公司名称',
				3: 'EMAIL',
				4: '手机号'
			},
			recArr: {
				1: '已推荐',
				2: '未推荐'
			},
			statusArr: {
				1: '已审核',
				3: '未通过',
				2: '已锁定',
				4: '未审核'
			},
			//  弹窗
			drawerTitle: '',
			drawerType: '',
			radioTitle: '',
			drawerTextarea: '',
			drawerRadio: '',
			titleColumnValue: '',
			detail: {},
			//站点
			zhandianDrawer: false,
			shareZhanArr: {
				did: '0'
			},
			// 批量认证
			renzhengType: [],
			renzhengValue: '',
			renzhengDrawer: false,
			loginInfo: {},
			//  用户信息
			ratingArr: [],
			ratingArrL: [],
			member: {},
			statis: {},
			userLevelArr: {},
			integral: '',
			// 如下参数只是为了程序不报错，有弹框，但是功能未用到
			value1: '',
			value3: '',

			dname:{},
			visible: false,
            baseUrl: localStorage.getItem("baseUrl"),

            prevPage: 0

        }
	},
	components: {
		'ltedit': httpVueLoader('./lt_component/edit.vue'),
		'lemember': httpVueLoader('./lt_component/edit_member.vue'),
		'lttc': httpVueLoader('./lt_component/edit_lttc.vue'),
	},
	created() {
		this.getList();
		this.ltNumFun();
		this.getCacheFun();
	},
	methods: {
		getCacheFun: function () {
			let _this = this;
			let url = _this.uri + 'admin_lt_member&a=getCache';
			httpPost(url, {}, { hideloading: true }).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					_this.ratingArr = res.data.ratingarr;
					_this.dname = res.data.dname;
				}
			})
		},
		lockList: function (status) {
			this.search.status = status;
			this.page = 1
			this.getList()
		},
		selectAllBottom(value) {
			value ? this.$refs.multipleTable.toggleAllSelection() : this.$refs.multipleTable.clearSelection();
		},
		del: function (detail) {
			let _this = this;
			let url = this.uri + 'admin_lt_member&a=del';
			delConfirm(this, { del: detail.uid }, function (params) {
				httpPost(url, params).then(function (response) {
					let res = response.data;
					if (res.error == 0) {
						message.success(res.msg, _this.getList());
					} else {
						message.error(res.msg);
					}
				})
			}, '确定要删除？')
		},
		batchShenhe: function () {
			let ids = this.idsArr;
			if (!ids.length) {
				message.error('请选择要操作的数据项');
				return
			}
			this.drawerFun('batchShenhe', null)
			// let _this = this;
			// let url = this.uri + 'admin_lt_member&a=del'
		},
		batchDel: function () {
			let ids = this.idsArr;
			if (!ids.length) {
				message.error('请选择要删除的数据');
				return
			}
			let _this = this;
			let url = this.uri + 'admin_lt_member&a=del'
			delConfirm(this, { del: ids }, function (params) {
				httpPost(url, params).then(function (response) {
					let res = response.data;
					if (res.error == 0) {
						message.success(res.msg, _this.getList());
					} else {
						message.error(res.msg);
					}
				})
			}, '确定要删除？')
		},
		batchRenZheng: function () {
			let ids = this.idsArr;
			if (!ids.length) {
				message.error('请选择要操作的数据项');
				return
			}
			this.renzhengDrawer = true;
		},
		shortChange(e) {
			let orderMap = { ascending: 'asc', descending: 'desc' }
			this.search.t = e.order ? e.prop : null;
			this.search.order = orderMap[e.order];
			this.page = 1;
			this.getList();
		},
		selectChange: function (val) {
			this.idsArr = [];
			let _this = this;
			if (val.length) {
				val.forEach(item => {
					_this.idsArr.push(item.uid);
				});
			}
			if (_this.idsArr.length == 0) {
				_this.checkedAll = false;
				this.isIndeterminate = false;

			} else {
				if (_this.idsArr.length == _this.tableData.length) {
					_this.checkedAll = true;
					this.isIndeterminate = false;

				} else {
					_this.checkedAll = false;
					this.isIndeterminate = true;

				}
			}
		},
		doUserQuery() {
			this.page = 1
			this.getList()
		},
		userPageChange(val) {
			this.page = val
			this.getList()
		},
		userPageSizeChange(val) {
			this.pageSize = val
			this.getList()
		},
		
		ltNumFun: function () {
			let _this = this;
			let url = _this.uri + 'admin_lt_member&a=ltNum';
			httpPost(url, {}, { hideloading: true }).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					_this.data = res.data;
				}
			})
		},
		getList: function () {
			let _this = this;
			let url = _this.uri + 'admin_lt_member&a=index';
			_this.search.page = this.page;
			_this.search.pageSize = this.pageSize;
			_this.loading = true;
			httpPost(url, _this.search, {hideloading: true}).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					_this.tableData = res.data.data;
					_this.total = res.data.total;
					_this.loading = false;
					_this.pageSizes = res.data.pageSizes;
					if(_this.prevPage != _this.page){
						_this.prevPage = _this.page;
						_this.$refs.multipleTable.bodyWrapper.scrollTop = 0;
					}
					if (_this.tableData.length === 0) {
						_this.dataText = "暂无数据";
					}
				}
			})
		},
		handleSizeChange(val) {
			console.log(`每页 ${val} 条`);
		},
		handleCurrentChange(val) {
			console.log(`当前页: ${val}`);
		},
		tomember(uid, usertype) {
			let url = this.baseUrl+'?m=user&c=admin_lt_member&a=Imitate&uid=' + uid;
			let _this = this;
			if (usertype != '3') {
				let content = '';
				if (usertype == '0') {
					content = '该账户当前没有设置身份，以猎头身份模拟进入可能导致部分功能无法正常使用，是否确认进入？';
				} else {
					if (usertype == '1') {
						var u = '求职者';
					} else if (usertype == '2') {
						var u = '招聘者';
					} else if (usertype == '4') {
						var u = '培训';
					}
					content = "该账户当前身份为" + u + "，以猎头身份模拟进入可能导致部分功能无法正常使用，是否确认进入？";
				}
				_this.$confirm(content, '温馨提示', {
					confirmButtonText: '确定',
					cancelButtonText: '取消',
					type: 'warning'
				}).then(() => {
					window.open(url);
				}).catch(() => {
				});
			} else {
				window.open(url);
			}
		},
		handleClick: function () {
			let _this = this;
			let url = this.uri + 'admin_lt_member&a=ratingList';
			if (this.activeName == 'third') {
				// 获取当前的
				httpPost(url, {}).then(function (response) {
					let res = response.data;
					if (res.error == 0) {
						_this.ratingArrL = res.data;
					} else {
						message.error(res.msg);
					}
				})

				this.$nextTick(() => {
					// 在 DOM 中添加 my-component 组件
					this.activeName = 'third';
				});
			}
		},
		drawerFunClear: function () {
			this.drawerTitle = ''
			this.radioTitle = ''
			this.drawerRadio = ''
			this.titleColumn = ''
			this.titleColumnValue = ''
			this.drawerTextarea = ''
		},
		// 弹窗
		drawerFun: function (type, detail) {
			this.drawerFunClear();
			this.drawerType = type;
			if (type == 'yyzz') {
				this.drawerTitle = '营业执照认证'
				this.radioTitle = '审核操作：';
				this.drawerRadio = detail.yyzz_status;
				let url = this.uri + 'admin_lt_member&a=yyzzlockinfo';
				let _this = this;
				httpPost(url, { uid: detail.uid }).then(function (response) {
					_this.drawerTextarea = response.data;
				})
			}
			if (type == 'moblie') {
				this.drawerTitle = '手机认证'
				this.titleColumn = '手机号码：'
				this.radioTitle = '认证操作：';
				this.drawerRadio = detail.moblie_status;
				this.titleColumnValue = detail.moblie
			}
			if (type == 'email') {
				this.drawerTitle = '邮箱认证'
				this.titleColumn = '邮箱：'
				this.radioTitle = '认证操作：';
				this.drawerRadio = detail.email_status;
				this.titleColumnValue = detail.email;
			}

			if (type == 'shenhe' || type == 'batchShenhe') {
				this.drawerTitle = '猎头用户审核';
				this.radioTitle = '审核操作';
				// this.drawerTextarea = detail.statusbody
				if (type == 'shenhe') {
					let url = this.uri + 'admin_lt_member&a=lockinfo';
					let _this = this;
					httpPost(url, { uid: detail.uid }).then(function (response) {
						_this.drawerTextarea = response.data;
					})
					this.drawerRadio = detail.r_status
				}
				this.drawerRadio = '';
			}
			if (type != 'batchShenhe') {
				this.detail = detail;
			}
			this.drawer3 = true;
		},
		chakanPhoto: function (imgPath) {
			window.open(imgPath);
		},
		drawerSave: function () {
			let sendData = {
				uid: this.detail.uid
			}
			if (this.drawerType == 'batchShenhe') {
				let ids = this.idsArr.join(',');
				sendData = {
					uid: ids
				}
			}
			let _this = this;
			let url = '';
			if (!this.drawerRadio) {
				message.error('请选择认证操作!');
				return;
			}
			if (this.drawerType == 'yyzz') {
				sendData.statusbody = this.drawerTextarea;
				sendData.r_status = this.drawerRadio
				url = this.uri + 'admin_lt_member&a=ltstatus';
			}
			if (this.drawerType == 'moblie') {
				sendData.mstatus = this.drawerRadio;
				sendData.ltphonemoblie = this.titleColumnValue;
				url = this.uri + 'admin_lt_member&a=mobliestatus';

			}
			if (this.drawerType == 'email') {
				sendData.estatus = this.drawerRadio;
				sendData.ltemailmail = this.titleColumnValue;
				url = this.uri + 'admin_lt_member&a=emailstatus';
			}
			if (this.drawerType == 'shenhe' || this.drawerType == 'batchShenhe') {
				sendData.status = this.drawerRadio;
				sendData.statusbody = this.drawerTextarea;
				url = this.uri + 'admin_lt_member&a=status';

			}
			_this.submitLoading = true;
			httpPost(url, sendData).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					message.success(res.msg, function(){
						_this.drawer3 = false;
						_this.getList()
					});
				} else {
					message.error(res.msg);
				}
			}).catch(function (error) {
				console.log(error);
			}).finally(function () {
				setTimeout(function() {
                    _this.submitLoading = false;
                }, 2000);
			});
			
		},
		shareZhan: function (detail) {
			this.shareZhanArr.uid = detail.uid;
			this.shareZhanArr.did = '' + detail.did;
			this.shareZhanArr.username = detail.username;
			this.zhandianDrawer = true
		},
		shareSave: function () {
			let _this = this;
			let url = this.uri + 'admin_lt_member&a=checksitedid';
			_this.submitLoading = true;

			httpPost(url, _this.shareZhanArr).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					message.success(res.msg, function(){
						_this.zhandianDrawer = false;
						_this.getList()
					})
				} else {
					message.error(res.msg);
				}
			}).catch(function (error) {
				console.log(error);
			}).finally(function () {
				setTimeout(function() {
                    _this.submitLoading = false;
                }, 2000);
			});
			
		},
		recFun: function (detail) {
			let _this = this;
			let url = this.uri + 'admin_lt_member&a=lt_rec';
			let sendData = {
				id: detail.uid,
				rec: detail.rec,
				type: 'rec',
			};
			httpPost(url, sendData).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					message.success(res.msg, _this.getList())
				} else {
					message.error(res.msg);
				}
			})
		},
		// 重置密码
		resetPassword: function (params) {
			let username = params.username;
			let _this = this;
			let url = this.uri + 'admin_lt_member&a=reset_pw';
			delConfirm(this, { uid: params.uid }, function (params) {
				httpPost(url, params).then(function (response) {
					let res = response.data;
					if (res.error == 0) {
						message.success("用户：" + username + " 密码已经重置为123456！", _this.getList());
					} else {
						message.error(res.msg);
					}
				})
			}, '确定要重置密码吗？')
		},
		renzhengSave: function () {
			if (this.renzhengType.length < 1) {
				message.error('请选择认证类型');
				return;
			}
			let _this = this;
			if (this.renzhengValue == '') {
				message.error('请选择认证状态!');
				return;
			}
			let sendData = {};
			for (let i = 0; i < _this.renzhengType.length; i++) {
				sendData[_this.renzhengType[i]] = 'on';
			}
			sendData.plstatus = this.renzhengValue;
			sendData.uid = this.idsArr.join(',');
			// plstatus
			let url = this.uri + 'admin_lt_member&a=batchfirm';
			_this.submitLoading = true;

			httpPost(url, sendData).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					message.success(res.msg, function(){
						_this.renzhengDrawer = false;
						_this.getList()
					});
				} else {
					message.error(res.msg);
				}
			}).catch(function (error) {
				console.log(error);
			}).finally(function () {
				setTimeout(function() {
                    _this.submitLoading = false;
                }, 2000);
			});
		},
		//  编辑
		editDetail: async function (detail) {
			this.detail = detail;

			let _this = this;
			let url = this.uri + 'admin_lt_member&a=getLoginInfo';
			let response = await httpPost(url, { id: detail.uid });
			let res = response.data;
			if (res.error == 0) {
				_this.loginInfo = res.data;
			} else {
				message.error(res.msg);
			}
			this.activeName = 'first';
			this.infoDrawer = true;
		},
		showLog: function (detail) {
			console.log(detail);
			this.$emit("show-log", { id: detail.uid });
		},
		updateUserLevel: function (detail) {

			let _this = this;
			let url = this.uri + 'admin_lt_member&a=getstatis';
			this.detail = detail;
			httpPost(url, { uid: detail.uid }).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					_this.userLevelArr = res.data;
					_this.integral = res.data.integral;
					_this.drawer6 = true;
				} else {
					message.error(res.msg);
				}
			})
		},
		getRatingInfo: function () {
			let _this = this;
			let url = this.uri + 'admin_lt_member&a=getrating';
			httpPost(url, { uid: this.detail.uid, id: this.userLevelArr.rating }).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					_this.userLevelArr = res.data;
				} else {
					message.error(res.msg);
				}
			})
		},
		savaUserLevel: function () {
			let _this = this;
			if (_this.userLevelArr.rating == '0') {
				message.error('请选择会员等级');
				return;
			}
			_this.userLevelArr.delaytime = _this.userLevelArr.vipetime;
			_this.userLevelArr.ratuid = _this.detail.uid;
			_this.userLevelArr.integral = _this.integral;
			let url = this.uri + 'admin_lt_member&a=uprating';
			_this.submitLoading = true;

			httpPost(url, _this.userLevelArr).then(function (response) {
				let res = response.data;
				if (res.error == 0) {
					message.success(res.msg, function(){
						_this.drawer6 = true;
						_this.getList()
					});
				} else {
					message.error(res.msg);
				}
			}).catch(function (error) {
				console.log(error);
			}).finally(function () {
				setTimeout(function() {
                    _this.submitLoading = false;
                }, 2000);
			});
			
		}

	},

};
</script>
<style scoped>
.el-button--primary.is-plain {
	background: #f4f4f5;
	border: none;
}

.el-button--primary.is-plain:hover {
	color: #409eff;
}

.moduleElTaCaoz {
	justify-content: space-between;
}

.el-dialog__body {
	padding: 0px 20px;
}

.moduleElTaCaoz .el-button {
	width: 47%;
	margin: 3px 0;
}

.moduleDengGai .el-button {
	overflow: hidden;
	position: relative;
	padding: 0 6px;
}

.moduleElTable {
	height: calc(100% - 135px);
}

@media (max-width: 1290px) {
	.moduleElTable {
		height: calc(100% - 140px) !important;
	}
}
</style>