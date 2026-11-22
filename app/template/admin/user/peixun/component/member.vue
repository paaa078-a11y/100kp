<template>
	<div class="moduleElHight">
		<div class="moduleElSearchInf">
			<div class="moduleElTabInpt" style="flex-wrap: wrap;">
				<div class="moduleInptList moduleInptWidt">
					<el-input placeholder="输入你要搜索的关键字" @keyup.enter.native="search" size="small" v-model="searchForm.keyword" clearable class="input-with-select">
						<el-select v-model="searchForm.type" slot="prepend" placeholder="请选择">
							<el-option label="用户名" :value="1"></el-option>
							<el-option label="机构名称" :value="2"></el-option>
							<el-option label="EMAIL" :value="3"></el-option>
							<el-option label="手机号" :value="4"></el-option>
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
			数据统计：共 <span @click="init">{{pxAllNum}}</span> 条
			<span class="admin_datatip_n">未审核：<span @click="statusSearch('4')">{{pxStatusNum1?pxStatusNum1:0}}</span> 条 </span>
			<span class="admin_datatip_n">未通过：<span @click="statusSearch('3')">{{pxStatusNum2?pxStatusNum2:0}}</span> 条</span>
			<span class="admin_datatip_n">已锁定：<span @click="statusSearch('2')">{{pxStatusNum3?pxStatusNum3:0}}</span> 条</span>
			<span class="admin_datatip_n">搜索结果： {{total}} 条</span>
		</div>
		<div class="moduleElTable moduleElMoreInt" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
			<el-table :data="list" stripe style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
					  @sort-change="sortChange"
				:header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
				<template slot="empty">
					<p>{{dataText}}</p>
				</template>
				<el-table-column type="selection" width="55">
				</el-table-column>
				<el-table-column prop="uid" label="编号" width="90" sortable="custom">
				</el-table-column>
				<el-table-column label="用户名/机构名称" min-width="180">
					<template slot-scope="scope">
						<div class="moduleProps">
							<div class="moduCrmIcon">
								<el-link type="primary" :underline="false" @click="toMember(scope.row)">{{ scope.row.username }}</el-link>
                                <el-tooltip v-if="scope.row.r_status == 2" class="item" effect="dark" content="已锁定"
                                            placement="top-start">
                                    <i class="el-icon-lock" style="color: orange"></i>
                                </el-tooltip>
							</div>
							<div><span>{{ scope.row.train_name }}</span></div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="企业认证" width="200">
					<template slot-scope="scope">
						<div class="moduCrmIcon">
							<div>
								<el-link :underline="false" @click="emailRz(scope.row)" v-if="scope.row.email_status == 1"><img src="/config/ajax_img/1-1.png" alt=""></el-link>
								<el-link :underline="false" @click="emailRz(scope.row)" v-else><img src="/config/ajax_img/1-2.png" alt=""></el-link>
							</div>
							<div>
								<el-link :underline="false" @click="moblieRz(scope.row)" v-if="scope.row.moblie_status == 1"><img src="/config/ajax_img/2-1.png" alt=""></el-link>
								<el-link :underline="false" @click="moblieRz(scope.row)" v-else><img src="/config/ajax_img/2-2.png" alt=""></el-link>
							</div>
							<div>
								<el-link :underline="false" v-if="scope.row.wxid != ''"><img src="/config/ajax_img/4-1.png" alt=""></el-link>
								<el-link :underline="false" v-else><img src="/config/ajax_img/4-2.png" alt=""></el-link>
							</div>
							<div>
								<el-link :underline="false" @click="yyzzRz(scope.row)" v-if="scope.row.yyzz_status == 1"><img src="/config/ajax_img/3-1.png" alt=""></el-link>
								<el-link :underline="false" @click="yyzzRz(scope.row)" v-else><img src="/config/ajax_img/3-2.png" alt=""></el-link>
							</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="手机号/EMAIL" width="200">
					<template slot-scope="scope">
						<div class="moduleProps">
							<div v-if="scope.row.moblie">{{ scope.row.moblie }}</div>
							<div v-if="scope.row.email">{{ scope.row.email }}</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="注册/登录" width="180">
					<template slot-scope="props">
						<div class="moduleProps">
                            <div>{{ props.row.reg_date_n }}</div>
							<div>
								<span v-if="props.row.login_date > 0" class="gsd">{{ props.row.login_date_n }}</span>
								<span v-else class="gsd">未登录</span>
							</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="站点" width="130">
					<template slot-scope="scope">
						<div class="moduleProps">
							<div>{{ domainList[scope.row.did] }}</div>
							<el-button type="text" @click="openDomain(scope.row)" style="padding: 0;">分配</el-button>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="推荐" width="120">
					<template slot-scope="scope">
						<div class="moduleProps">
							<el-switch v-model="scope.row.rec" active-color="#13ce66" inactive-color="#ff4949"
									   active-value="1" inactive-value="0" @change="changeRec($event, scope.row)">
							</el-switch>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="审核状态" width="140">
					<template slot-scope="scope">
						<div class="admin_state">
							<span v-if="scope.row.r_status == 1" class="admin_state1">已审核</span>
							<span v-else-if="scope.row.r_status == 3" class="admin_state2">未通过</span>
							<span v-else-if="scope.row.r_status == 2" class="admin_state3">已锁定</span>
							<span v-else class="admin_state5">未审核</span>
						</div>
					</template>
				</el-table-column>
				<el-table-column fixed="right" label="操作" width="130">
					<template slot-scope="scope">
						<div class="moduleElTaCaoz">
							<el-button size="mini" @click="openAudit(scope.row)">审核</el-button>
							<el-button size="mini" @click="resetPassword(scope.row)">密码</el-button>
							<el-button size="mini" @click="openEdit(scope.row)">修改</el-button>
							<el-popover placement="bottom" width="60" trigger="hover">
								<div class="moduleMores">
									<el-button size="mini" @click="openLog(scope.row.uid)">日志</el-button>
									<el-button size="mini" @click="openDel(scope.$index)">删除</el-button>
								</div>
								<el-button size="mini" slot="reference">更多</el-button>
							</el-popover>
						</div>
					</template>
				</el-table-column>
			</el-table>
		</div>
		<div class="modulePaging">
			<div>
				<el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate"
							 @change="checkAll">全选</el-checkbox>
				<el-button @click="batch('del')" size="mini">批量删除</el-button>
				<el-button @click="batch('audit')" size="mini">批量审核</el-button>
				<el-button @click="batch('domain')" size="mini">批量选择分站</el-button>
				<el-button @click="batch('auth')" size="mini">批量认证</el-button>
			</div>
			<div class="modulePagNum">
				<el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
							   :current-page="page" :page-sizes="pageSizes" :page-size="limit"
							   layout="total, sizes, prev, pager, next, jumper" :total="total" :pager-count="pagerCount">
				</el-pagination>
			</div>
		</div>
		<!-- 弹窗 -->
		<div class="modluDrawer">
			<el-dialog title="删除培训数据" :visible.sync="dialogDel" :modal-append-to-body="false"
					   :show-close="true" width="400px">
				<div>
					<el-checkbox v-model="ruleFormDel.delAccount" true-label="1" false-label="0">同步删除账号</el-checkbox>
				</div>
				<div>
					<i class="el-icon-warning"></i> 勾选删除账号所有数据信息
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="dialogDel = false">取 消</el-button>
					<el-button type="primary" @click="delSubmit" :disabled="saveLoading">确 定</el-button>
				</span>
			</el-dialog>
		</div>
		<div class="modluDrawer">
			<el-dialog title="培训用户审核" :visible.sync="dialogAudit" :modal-append-to-body="false"
				:show-close="true" width="500px">
				<div class="toolClasDia fenpeizhand">
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>审核操作：</span>
						</div>
						<div class="toolClasCont">
							<el-radio v-model="ruleFormAudit.status" label="1">正常</el-radio>
							<el-radio v-model="ruleFormAudit.status" label="3">未通过</el-radio>
						</div>
					</div>
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>审核说明：</span>
						</div>
						<div class="toolClasCont">
							<el-input type="textarea" :rows="2" placeholder="" v-model="ruleFormAudit.statusbody">
							</el-input>
						</div>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="dialogAudit = false">取 消</el-button>
					<el-button type="primary" @click="saveAudit" :disabled="saveLoading">确 定</el-button>
				</span>
			</el-dialog>
		</div>
		<div class="modluDrawer">
			<el-dialog title="分配站点" :visible.sync="dialogDomain" :modal-append-to-body="false"
				:show-close="true" width="500px">
				<div class="toolClasDia fenpeizhand">
					<div class="toolClasList" v-if="detail.id">
						<div class="toolClasTite">
							<span>用户名：</span>
						</div>
						<div class="toolClasCont">
							<span>{{detail.train_name ? detail.train_name : detail.username }}</span>
						</div>
					</div>
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>切换站点：</span>
						</div>
						<div class="toolClasCont">
							<el-select v-model="ruleFormDomain.did" filterable placeholder="请选择">
								<el-option v-for="(item, key) in domainList" :key="key" :label="item" :value="key">
								</el-option>
							</el-select>
						</div>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="dialogDomain = false">取 消</el-button>
					<el-button type="primary" @click="saveDomain" :disabled="saveLoading">确 定</el-button>
				</span>
			</el-dialog>
			<el-dialog title="邮箱认证" :visible.sync="dialogEmail" :modal-append-to-body="false"
					   :show-close="true" width="500px">
				<div class="toolClasDia fenpeizhand">
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>邮箱：</span>
						</div>
						<div class="toolClasCont">
							<el-input placeholder="" v-model="ruleFormEmail.email">
							</el-input>
						</div>
					</div>
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>认证操作：</span>
						</div>
						<div class="toolClasCont">
							<el-radio v-model="ruleFormEmail.status" label="1">已认证</el-radio>
						</div>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="dialogEmail = false">取 消</el-button>
					<el-button type="primary" @click="emailRzSubmit" :disabled="saveLoading">确 定</el-button>
				</span>
			</el-dialog>
			<el-dialog title="手机认证" :visible.sync="dialogMobile" :modal-append-to-body="false"
					   :show-close="true" width="500px">
				<div class="toolClasDia fenpeizhand">
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>手机号：</span>
						</div>
						<div class="toolClasCont">
							<el-input placeholder="" v-model="ruleFormMobile.mobile">
							</el-input>
						</div>
					</div>
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>认证操作：</span>
						</div>
						<div class="toolClasCont">
							<el-radio v-model="ruleFormMobile.status" label="1">已认证</el-radio>
						</div>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="dialogMobile = false">取 消</el-button>
					<el-button type="primary" @click="moblieRzSubmit" :disabled="saveLoading">确 定</el-button>
				</span>
			</el-dialog>
			<el-dialog title="营业执照认证" :visible.sync="dialogYyzz" append-to-body
					   :show-close="true" width="500px">
				<div class="toolClasDia fenpeizhand">
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>认证图片：</span>
						</div>
						<div class="toolClasCont">
							<el-image v-if="detail.check" :src="detail.check" style="width: 150px; height: 80px"
									  :preview-src-list="[detail.check]">
							</el-image>
							<span v-else>暂无营业执照</span>
						</div>
					</div>
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>认证操作：</span>
						</div>
						<div class="toolClasCont">
							<el-radio v-model="ruleFormYyzz.r_status" label="0">待认证</el-radio>
							<el-radio v-model="ruleFormYyzz.r_status" label="1">已认证</el-radio>
						</div>
					</div>
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>审核说明：</span>
						</div>
						<div class="toolClasCont">
							<el-input type="textarea" :rows="2" placeholder="" v-model="ruleFormYyzz.statusbody">
							</el-input>
						</div>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="dialogYyzz = false">取 消</el-button>
					<el-button type="primary" @click="yyzzRzSubmit" :disabled="saveLoading">确 定</el-button>
				</span>
			</el-dialog>
			<el-dialog title="批量认证" :visible.sync="dialogAuth" append-to-body
					   :show-close="true" width="500px">
				<div class="toolClasDia fenpeizhand">
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>认证类型：</span>
						</div>
						<div class="toolClasCont">
							<el-checkbox-group v-model="ruleFormAuth.type">
								<el-checkbox label="email">邮箱</el-checkbox>
								<el-checkbox label="mobile">手机</el-checkbox>
								<el-checkbox label="yyzz">营业执照</el-checkbox>
							</el-checkbox-group>
						</div>
					</div>
					<div class="toolClasList">
						<div class="toolClasTite">
							<span>认证操作：</span>
						</div>
						<div class="toolClasCont">
							<el-radio v-model="ruleFormAuth.status" label="0">待认证</el-radio>
							<el-radio v-model="ruleFormAuth.status" label="1">已认证</el-radio>
						</div>
					</div>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="dialogAuth = false">取 消</el-button>
					<el-button type="primary" @click="authSubmit" :disabled="saveLoading">确 定</el-button>
				</span>
			</el-dialog>

			<el-drawer title="培训用户修改" :visible.sync="drawerEdit" append-to-body size="880px">
				<edit :uid="detail.uid" @child-event="closeEdit"></edit>
			</el-drawer>
		</div>
	</div>
</template>

<script>
module.exports = {
	props: {
		status: {type: String, default: ''}
	},
	data: function () {
		return {
			loading: false,
			pagerCount: 5,
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

			// 数据统计
			pxAllNum: 0,
			pxStatusNum1: 0,
			pxStatusNum2: 0,
			pxStatusNum3: 0,

			domainList: {},

			// 分站切换
			dialogDomain: false,
			ruleFormDomain: {},

			// 认证
			dialogEmail: false,
			ruleFormEmail: {},
			dialogMobile: false,
			ruleFormMobile: {},
			dialogYyzz: false,
			ruleFormYyzz: {},

			// 批量认证
			dialogAuth: false,
			ruleFormAuth: {},

			// 审核
			dialogAudit: false,
			ruleFormAudit: {},

			// 修改
			drawerEdit: false,

			// 删除
			dialogDel: false,
			ruleFormDel: {},
            saveLoading: false,

            prevPage: 0
		}
	},
	components: {
		'edit': httpVueLoader('./member_edit.vue'),
	},
	mounted() {
        var that = this
        setTimeout(function () {
            that.getCacheFun();
        }, 200)
    },
	created() {
		this.init();
	},
	methods: {
		getCacheFun:function(){
            let that = this;
            httpPost('m=user&c=peixun_member&a=getCacheData', {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    that.domainList = res.data.dname
                    that.searchlist = res.data.search_list;
                }
            })
        },
		init() {
			// this.resetSearch();
			this.getCountData();
			this.search();
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

			httpPost('m=user&c=peixun_member&a=pxNum').then(function (response) {
				let res = response.data;

				that.pxAllNum = res.pxAllNum;
				that.pxStatusNum1 = res.pxStatusNum1;
				that.pxStatusNum2 = res.pxStatusNum2;
				that.pxStatusNum3 = res.pxStatusNum3;
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
			httpPost('m=user&c=peixun_member', { ...params, ...searchForm }, {hideloading: true}).then(function (response) {
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
			}else if (this.multipleSelection.length == 0){
				message.error('请选择要操作的数据项');
				return false;
			}

			let idArr = [];
			this.multipleSelection.forEach(function (item) {
				idArr.push(item.uid);
			})
			this.idArr = idArr;

			if (type == 'del') {
				this.openDel();
			} else if (type == 'domain') {
				this.openDomain();
			} else if (type == 'audit') {
				this.openAudit();
			} else if (type == 'auth') {
				this.openAuth();
			}
		},
		checkAll(val) {
			val ? this.checkedAllIndeterminate = false : '';
			this.$refs.multipleTable.toggleAllSelection();
		},

		toMember(row) {
			let that = this;

			if (row.usertype != '4') {
				if (row.usertype == '0') {
					delConfirm(this, {}, function(){
						that.getMemberUrl(row.uid);
					},"该账户当前没有设置身份，以培训身份模拟进入可能导致部分功能无法正常使用，是否确认进入？");
				} else {
					var usertype = '';
					if (row.usertype == '1') {
						usertype = '求职者';
					} else if (row.usertype == '2') {
						usertype = '招聘者';
					} else if (row.usertype == '3') {
						usertype = '猎头用户';
					}

					delConfirm(this, {}, function(){
						that.getMemberUrl(row.uid);
					},"该账户当前身份为" + usertype + "，以培训身份模拟进入可能导致部分功能无法正常使用，是否确认进入？");
				}
			} else {
				that.getMemberUrl(row.uid);
			}
		},

		async getMemberUrl(uid) {
			let response = await httpPost('m=user&c=peixun_member&a=Imitate', {uid: uid});

			let res = response.data;
			if (res.error === 0) {
				window.open(res.data.url);
			} else {
				message.error(res.msg);
			}
		},

		openDomain(row) {
			if (typeof row == 'undefined') { // 批量操作
				this.detail = {};
				this.$set(this.ruleFormDomain, 'uid', this.idArr);
				this.$set(this.ruleFormDomain, 'did', '');
			} else { // 单个操作
				this.detail = row;
				this.$set(this.ruleFormDomain, 'uid', row.uid);
				this.$set(this.ruleFormDomain, 'did', row.did && this.domainList[row.did] ?  '' + row.did : '');
			}

			this.dialogDomain = true;
		},

		saveDomain() {
			let that = this,
				ruleForm = that.ruleFormDomain;

			if (!ruleForm.did) {
				message.error('请选择需要切换的站点');
				return false;
			}

			if (that.saveLoading) {
				return false;
			}

			that.saveLoading = true;

			httpPost('m=user&c=peixun_member&a=checksitedid', ruleForm).then(function (response) {
				let res = response.data;

				if (res.error > 0) {
					that.saveLoading = false;
					message.error(res.msg);
				} else {
					that.dialogDomain = false;
					that.getList();
					that.$refs.multipleTable.clearSelection();
					message.success(res.msg, function () {
						that.saveLoading = false;
					})
				}
			})
		},

		emailRz(row) {
			this.dialogEmail = true;
			this.ruleFormEmail = {
				uid: row.uid,
				email: row.email,
				status: row.email_status,
			};
		},
		emailRzSubmit() {
			let that = this,
				ruleForm = this.ruleFormEmail;

			if (!ruleForm.email) {
				message.error('请输入邮箱');
				return false;
			}

			that.saveLoading = true;

			httpPost('m=user&c=peixun_member&a=emailstatus', ruleForm).then(function (response) {
				let res = response.data;

				if (res.error > 0) {
					that.saveLoading = false;
					message.error(res.msg);
				} else {
					that.dialogEmail = false;
					that.getList();
					that.$refs.multipleTable.clearSelection();
					message.success(res.msg, function () {
						that.saveLoading = false;
					})
				}
			})
		},
		moblieRz(row) {
			this.dialogMobile = true;
			this.ruleFormMobile = {
				uid: row.uid,
				mobile: row.moblie,
				status: row.moblie_status,
			};
		},
		moblieRzSubmit() {
			let that = this,
				ruleForm = this.ruleFormMobile;

			if (!ruleForm.mobile) {
				message.error('请输入手机号');
				return false;
			}

			that.saveLoading = true;

			httpPost('m=user&c=peixun_member&a=mobilestatus', ruleForm).then(function (response) {
				let res = response.data;

				if (res.error > 0) {
					that.saveLoading = false;
					message.error(res.msg);
				} else {
					that.dialogMobile = false;
					that.getList();
					that.$refs.multipleTable.clearSelection();
					message.success(res.msg, function () {
						that.saveLoading = false;
					})
				}
			})
		},
		yyzzRz(row) {
			let that = this;
			that.dialogYyzz = true;
			that.detail = row;

			httpPost('m=user&c=peixun_member&a=sbody', {uid: row.uid}).then(function (response) {
				let res = response.data;

				that.ruleFormYyzz = {
					uid: row.uid,
					r_status: row.yyzz_status,
					statusbody: res,
				};
			})
		},
		yyzzRzSubmit() {
			let that = this,
				ruleForm = this.ruleFormYyzz;

			if (typeof ruleForm.r_status == 'undefined' || ruleForm.r_status === '') {
				message.error('请选择审核状态');
				return false;
			}

			that.saveLoading = true;

			httpPost('m=user&c=peixun_member&a=trainstatus', ruleForm).then(function (response) {
				let res = response.data;

				if (res.error > 0) {
					that.saveLoading = false;
					message.error(res.msg);
				} else {
					that.dialogYyzz = false;
					that.getList();
					that.$refs.multipleTable.clearSelection();
					message.success(res.msg, function () {
						that.saveLoading = false;
					})
				}
			})
		},
		openAuth() {
			this.dialogAuth = true;
			this.ruleFormAuth = {
				uid: this.idArr,
				type: [],
				status: ''
			};
		},
		authSubmit() {
			let that = this,
				ruleForm = this.ruleFormAuth;

			if (typeof ruleForm.type == 'undefined' || ruleForm.type.length == 0) {
				message.error('请选择认证类型');
				return false;
			}

			if (typeof ruleForm.status == 'undefined' || ruleForm.status === '') {
				message.error('请选择认证状态');
				return false;
			}

			that.saveLoading = true;

			httpPost('m=user&c=peixun_member&a=batchfirm', ruleForm).then(function (response) {
				let res = response.data;

				if (res.error > 0) {
					that.saveLoading = false;
					message.error(res.msg);
				} else {
					that.dialogAuth = false;
					that.getList();
					that.$refs.multipleTable.clearSelection();
					message.success(res.msg, function () {
						that.saveLoading = false;
					})
				}
			})
		},


		resetPassword(row) {
			let that = this;
			delConfirm(this, {uid: row.uid}, function(params) {
                httpPost('m=user&c=users_member&a=reset_pw', params).then(function(res) {
					if (res.data.error > 0) {
						message.error(res.data.msg);
					} else {
						message.alert("用户：" + row.username + " 密码已经重置为123456！");
					}
				})

            }, '确定要重置密码吗？')
		},

		changeRec(val, row) {
			let that = this;

			httpPost('m=user&c=peixun_member&a=rec', {uid: row.uid, rec: val}).then(function(res) {
				if (res.data.error > 0) {
					message.error(res.data.msg);
				}
			})
		},

		openAudit(row) {
			let that = this;

			if (typeof row != 'undefined') {
				if (row.r_status == 2) {
					message.error('当前用户为已锁定状态，无法进行审核操作');
					return false;
				}

				httpPost('m=user&c=peixun_member&a=lockinfo', {uid: row.uid}).then(function (response) {
					let res = response.data;

					that.ruleFormAudit = {
						uid: row.uid,
						status: row.r_status,
						statusbody: res,
					};
				})
			} else {
				that.ruleFormAudit = {
					uid: this.idArr,
					status: '',
					statusbody: '',
				};
			}
			that.dialogAudit = true;
		},

		saveAudit() {
			let that = this,
				params = that.ruleFormAudit;

			if (typeof params.status == 'undefined' || params.status === '' || params.status === '0') {
				message.error('请选择审核状态');
				return false;
			}

			if (that.saveLoading) {
				return false;
			}
			that.saveLoading = true;

			httpPost('m=user&c=peixun_member&a=status', params).then(function(res) {
				if (res.data.error > 0) {
					that.saveLoading = false;
					message.error(res.data.msg);
				} else {
					that.dialogAudit = false;
					that.getList();
					message.success(res.data.msg, function () {
						that.$refs.multipleTable.clearSelection();
						that.saveLoading = false;
					});
				}
			})
		},

		openEdit(row) {
			this.detail = row;
			this.drawerEdit = true;
		},

		closeEdit() {
			this.drawerEdit = false;
			this.getList();
		},

		openDel(idx) {
			if (typeof idx == 'undefined') { // 批量删除
				this.ruleFormDel = {
					del: this.idArr,
					delAccount: 0
				}
			} else {// 单个删除
				this.ruleFormDel = {
					del: this.list[idx].uid,
					delAccount: 0
				}
			}

			this.dialogDel = true;
		},
		delSubmit() {
			let that = this,
				ruleForm = this.ruleFormDel;

			if (that.saveLoading) {
				return false;
			}
			that.saveLoading = true;

			httpPost('m=user&c=peixun_member&a=del', ruleForm).then(function (response) {
				let res = response.data;

				if (res.error > 0) {
					that.saveLoading = false;
					message.error(res.msg);
				} else {
					that.dialogDel = false;
					that.getList();
					that.$refs.multipleTable.clearSelection();
					message.success(res.msg, function () {
						that.saveLoading = false;
					})
				}
			})
		},

		openLog(uid) {
			custoapp.activeName = 'log';
			custoapp.uid = uid
		},
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

.moduleElTaCaoz .el-button {
	margin: 3px 0;
}
.moduleElTaCaoz .elinks {
	width: 50px;
}
</style>