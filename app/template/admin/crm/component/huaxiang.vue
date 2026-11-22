<!--客户信息-->
<template>
	<div class="huaxianCome" v-loading="loading">
		<div class="huaxianTite">
			<div class="huaxiaName">
				<span>{{comInfo.name ? comInfo.name : '企业尚未完善资料'}}</span>
			</div>
			<div class="huaxiaImage">
				<img src="../../images/crmicon11.png" alt="" v-if="comInfo.email_status == '1'">
				<img src="../../images/crmicon22.png" alt="" v-if="comInfo.wx_status=='1'">
				<img src="../../images/crmicon33.png" alt="" v-if="comInfo.moblie_status == '1'">
				<img src="../../images/crmicon44.png" alt="" v-if="comInfo.yyzz_status == '1'">
			</div>
		</div>
		<div class="huaxiaTips">
			<div class="huaxiaTipsLise">
				会员等级：<el-tag type="success" size="mini">{{comInfo.rating_name}}</el-tag> <span
					v-if="comInfo.vipetime">{{comInfo.vipetime_n}}到期</span>
			</div>
			<div class="huaxiaTipsLise">
				所属销售：<el-tag type="info" size="mini">{{comInfo.crm_name}}</el-tag>
			</div>
			<div class="huaxiaTipsLise">
				最后跟进时间：{{comInfo.f_time_n}}
			</div>
		</div>
		<div class="huaxiaTips">
			<div class="huaxiaTipsLise ">
				联系电话：<span class="huaxiaTipsLisetel">{{comInfo.linkman}}&nbsp;{{comInfo.linktel}}</span>
			</div>
			<div class="huaxiaTipsLise">
				最后登录时间：{{comInfo.login_day}}
			</div>
		</div>
		<div class="huaxiaGangwei">
			<div class="huaxiaGawfd">
				<div class="huaxiaGawList huaxiaGawList5">
					<div class="huaxiaGawImg">
						<img src="../../images/hx1.png" alt="">
					</div>
					<div class="huaxiaGawNum">
						<span>在招岗位</span>
						<b>{{comInfo.jobNum}}</b>
					</div>
				</div>
			</div>
			<div class="huaxiaGawfd">
				<div class="huaxiaGawList huaxiaGawList1">
					<div class="huaxiaGawImg">
						<img src="../../images/hx2.png" alt="">
					</div>
					<div class="huaxiaGawNum ">
						<span>收到简历</span>
						<b>{{persona.sqNum}}</b>
					</div>
				</div>
			</div>
			<div class="huaxiaGawfd">
				<div class="huaxiaGawList huaxiaGawList4">
					<div class="huaxiaGawImg">
						<img src="../../images/hx3.png" alt="">
					</div>
					<div class="huaxiaGawNum">
						<span>下载简历</span>
						<b>{{persona.downNum}}</b>
					</div>
				</div>
			</div>
			<div class="huaxiaGawfd">
				<div class="huaxiaGawList huaxiaGawList3">
					<div class="huaxiaGawImg">
						<img src="../../images/hx4.png" alt="">
					</div>
					<div class="huaxiaGawNum">
						<span>邀请面试</span>
						<b>{{persona.yqNum}}</b>
					</div>
				</div>
			</div>
			<div class="huaxiaGawfd">
				<div class="huaxiaGawList huaxiaGawList2">
					<div class="huaxiaGawImg">
						<img src="../../images/hx5.png" alt="">
					</div>
					<div class="huaxiaGawNum">
						<span>简历查看率</span>
						<b>{{persona.readRate}}</b>
					</div>
				</div>
			</div>
		</div>
		<div class="huaxiaTabsy">

			<el-tabs v-model="activeName" @tab-click="handleClick">
				<el-tab-pane label="数据简报" name="first">
					<div class="sjjb_box">
						<div class="sjjb_list">
							<span class="sjjb_bq">入驻</span>{{comInfo.name ? comInfo.name : '企业'}}于<span class="sjjb_bq_n">{{persona.reg_date}}注册</span>
						</div>
						<div class="sjjb_list" v-if="persona.loginAllNum>0">
							<span class="sjjb_bq">活跃</span>
							<span class="sjjb_bq_n">
							{{persona.regdays}} 天</span>以来
							<span class="sjjb_bq_n cur_p" @click="loginlog(0)">共登录 {{persona.loginAllNum}} 次</span>，
							<span class="sjjb_bq_n cur_p" @click="loginlog(1)">最近一{{persona.loginDtype}}{{persona.loginDNum>0?`登录${persona.loginDNum}次`:'无登陆'}}</span>，
							<span class="" v-if="persona.huoyueData!=''">每天的 <span class="sjjb_bq_n">{{persona.huoyueData}}</span> 最为活跃</span>
						</div>

						<div class="sjjb_list" v-if="persona.joballnum>0">
							<span class="sjjb_bq">需求</span>
							曾经发布<span class="sjjb_bq_n cur_p" @click="joblist(0)">{{persona.joballnum}}个岗位</span>
							<template v-if="persona.zpjobnum>0">，目前<span class="sjjb_bq_n cur_p" @click="joblist(1)">在招岗位 {{persona.zpjobnum}} 个</span> </template>
							<span v-if="persona.keywordData!=''">
								，最常搜索<span class="sjjb_bq_n">{{persona.keywordData}}</span>简历
							</span>
						</div>
						<div class="sjjb_list" v-if="persona.sqNum>0">
							<span class="sjjb_bq">效果</span>
							一共<span class="sjjb_bq_n cur_p" @click="sqlist()">收到 {{persona.sqNum}} 份简历</span><template v-if="persona.unReadNum>0">，有<span class="sjjb_bq_n cur_p"  @click="sqlist({browse:'1'})">{{persona.unReadNum}}份</span>暂未查看</template>， 查看率为 <span class="sjjb_bq_n">{{persona.readRate}} </span> ，
							<span class=" " v-if="persona.sqWeekNum>0">最近一周收到<span class="cur_p" @click="sqlist({time:'7'})">{{persona.sqWeekNum}} 份简历</span></span>
							<span placement="top" v-if="persona.yqNum>0 || persona.downNum>0 || persona.chatfriendNum>0">
								<template v-if="persona.yqNum>0">发起<span class="sjjb_bq_n cur_p" @click="yqlist()">邀请面试 {{persona.yqNum}}次</span></template>
								<template v-if="persona.downNum>0"><template v-if="persona.yqNum>0">，</template>下载简历<span class="sjjb_bq_n cur_p" @click="downlist()">{{persona.downNum}}份</span></template>
								<template v-if="persona.chatfriendNum>0"><template v-if="persona.yqNum>0 || persona.downNum>0">，</template>与<span class="sjjb_bq_n cur_p" @click="showchatfriend()">{{persona.chatfriendNum}}人在线沟通</span>过</template>
							</span>
						</div>
						<div class="sjjb_list" v-if="persona.followNum>0">
							<span class="sjjb_bq">跟进</span>
							业务员<span class="sjjb_bq_n cur_p" @click="followlog()">跟进{{persona.followNum}}次</span>，最近一次跟进在<span class="sjjb_bq_n">{{persona.lastfollowDate}}</span>
						</div>
						<div class="sjjb_list" v-if="persona.coNum>0">
							<span class="sjjb_bq">合作</span>
							合作过<span class="sjjb_bq_n cur_p" @click="crmorder()">{{persona.coNum}}次</span>，{{persona.coNum>3?'最近三次为':'分别为'}} {{persona.coData}}
						</div>
						<div class="sjjb_list">
							<span class="sjjb_bq">状态</span>
							当前为<span class="sjjb_bq_n">{{comInfo.rating_name}}</span>状态
							<template v-if="comInfo.isVip==1">，距离到期<span class="sjjb_bq_n">{{comInfo.vipDay}}</span></template>
							<template v-if="comInfo.crm_uid!='' && comInfo.crm_uid>0">，由 <span class="sjjb_bq_n">{{comInfo.crm_name}}</span>进行维护</template>
							<template v-else>，暂无人员维护</template>
						</div>
					</div>


				</el-tab-pane>
				<el-tab-pane label="时间轴" name="second">
					<div class="huaxiaTabJabna">
						<el-timeline :reverse="behavior.reverseone">
							<el-timeline-item placement="top" color="#2778f8"
								v-for="(activity, index) in behavior.logList" :key="index"
								:timestamp="activity.timehis_n + '(' + activity.timeymd_n + ')[' + activity.ip + ']'">
								<div class="huaxiaTabText"><span class="sjjb_bq_n">{{ activity.info }}</span></div>
							</el-timeline-item>
						</el-timeline>
						<template v-if="behavior.pageCode == '2'">
							<template v-if="behavior.logList.length > 0">没有更多了</template>
							<el-empty description="暂无记录" v-else></el-empty>
						</template>
						<template v-if="behavior.pageCode == '1'">
							<h3 @click="getloglist">加载更多...</h3>
						</template>
					</div>
				</el-tab-pane>
				<el-tab-pane label="活跃图表" name="third">
					<div class="huaxiaTabChat" id="huaxchat1"></div>
				</el-tab-pane>
				<el-tab-pane label="成交记录" name="orderList">
					<div class="huaxiaTabTable">
						<el-table :data="orderList" stripe
							:header-cell-style="{ background: '#f5f7fa', color: '#606266' }" style="width: 100%"
							:default-sort="{ prop: 'order_id', order: 'descending' }" @sort-change='orderSortChange'
							ref="orderTable" v-loading="loadingorder" :empty-text="emptytextorder" border>
							<el-table-column prop="order_id" label="订单编号" sortable="custom"
								:sort-orders="['descending', 'ascending']">
							</el-table-column>
							<el-table-column label="订单金额">
								<template slot-scope="props">
									<span style="color: red;">￥ {{ props.row.order_price }}</span>
								</template>
							</el-table-column>
							<el-table-column label="订单类型">
								<template slot-scope="props">
									<el-tag type="info">{{ props.row.type_n }}</el-tag>
								</template>
							</el-table-column>
							<el-table-column label="支付方式">
								<template slot-scope="props">
									<el-tag>{{ props.row.order_type_n }}</el-tag>
								</template>
							</el-table-column>
							<el-table-column prop="order_time_n" label="订单时间" sortable="custom"
								:sort-orders="['descending', 'ascending']">
							</el-table-column>
							<el-table-column label="订单状态">
								<template slot-scope="props">
									<el-tag v-if="props.row.order_state == 1">{{ props.row.order_state_n }}</el-tag>
									<el-tag type="success"
										v-if="props.row.order_state == 2">{{ props.row.order_state_n}}</el-tag>
									<el-tag type="info"
										v-if="props.row.order_state == 3">{{ props.row.order_state_n}}</el-tag>
									<el-tag type="danger"
										v-if="props.row.order_state == 0">{{ props.row.order_state_n}}</el-tag>
									<el-tag type="warning"
										v-if="props.row.order_state == 4">{{ props.row.order_state_n}}</el-tag>
								</template>
							</el-table-column>
							<el-table-column prop="order_remark" label="备注">
							</el-table-column>
						</el-table>
					</div>
					<div class="modulePaging">
						<div class="modulePagNum">
							<div class="modulePagNum" style="margin: 0 auto;">
								<el-pagination background @size-change="handleOrderSizeChange"
									@current-change="handleOrderCurrentChange" :current-page.sync="orderCurrentPage"
									:page-size="orderLimit" :page-sizes="orderPageSizes"
									layout="total, sizes, prev, pager, next, jumper"
									:total="orderTotal"></el-pagination>
							</div>
						</div>
					</div>
				</el-tab-pane>
			</el-tabs>
		</div>
		<!--弹窗：企业登录日志-->
		<div class="modluDrawer">
		    <el-drawer title="企业登录日志" :visible.sync="loginlogShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
		        <qylogin style="padding-left: 20px;" from="persona" :param="loginlog_param" v-if="loginlogShow"></qylogin>
		    </el-drawer>
		</div>
		<!--弹窗：企业职位-->
		<div class="modluDrawer">
		    <el-drawer title="企业职位" :visible.sync="jobShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
		        <company_job style="padding-left: 20px;" from="persona" :param="joblist_param" v-if="jobShow"></company_job>
		    </el-drawer>
		</div>
		<!--弹窗：收到简历-->
		<div class="modluDrawer">
		    <el-drawer title="收到简历" :visible.sync="sqShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
		        <comlog_index style="padding-left: 20px;" from='persona' :param="sq_param" from='persona' :searchable="false" v-if="sqShow"></comlog_index>
		    </el-drawer>
		</div>
		<!--弹窗：邀请-->
		<div class="modluDrawer">
		    <el-drawer title="邀请面试" :visible.sync="yqShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
		        <comlog_useridmsg style="padding-left: 20px;" from='persona' :param="yq_param" :searchable="false"></comlog_useridmsg>
		    </el-drawer>
		</div>
		<!--弹窗：下载-->
		<div class="modluDrawer">
		    <el-drawer title="下载简历" :visible.sync="downShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
		        <resumedown style="padding-left: 20px;" from='persona' :param="down_param"></resumedown>
		    </el-drawer>
		</div>
		<!--弹窗：跟进-->
		<div class="modluDrawer">
		    <el-drawer title="跟进记录" :visible.sync="followShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
		        <followlog style="padding-left: 10px;" from='persona' :param="follow_param" :searchable="false"></followlog>
		    </el-drawer>
		</div>
		<!--弹窗：crm订单-->
		<div class="modluDrawer">
		    <el-drawer title="crm订单" :visible.sync="orderShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
		        <company_order style="padding-left: 20px;" from='persona' :param="order_param"  :searchable="false"></company_order>
		    </el-drawer>
		</div>
		<!--弹窗：聊天好友-->
		<div class="modluDrawer">
		    <el-drawer title="聊天记录" :visible.sync="chatShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
		        <chatfriend style="padding-left: 20px;" from='persona' :param="chat_param"  :searchable="false"></chatfriend>
		    </el-drawer>
		</div>
	</div>
</template>

<script>
	module.exports = {
		props: {
			comid: {
				type: [String, Number],
				default: ''
			},
		},
		data: function() {
			return {
				loading: false,
				activeName: 'first',
				tableData: [],
				emptytext: '暂无数据',
				comInfo: {},
				persona: {},
				// 行为分析
				behavior: {
					reverseone: false,
					daterange: '',
					times: '',
					activeClass: '',
					fenxiDetail: {},
					dataCount: {},
					logList: [],
					pagenav: 1,
					pageCode: '',
					xialaStatus: true
				},

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
				loadingorder: false,
				emptytextorder: '暂无数据',

				xAxis: [],
				scanNumArr: [],

				loginlogShow:false,
				loginlog_param:{},
				jobShow:false,
				joblist_param:{},
				sqShow:false,
				sq_param:{},
				yqShow:false,
				yq_param:{},
				downShow:false,
				down_param:{},
				followShow:false,
				follow_param:{},
				orderShow:false,
				order_param:{},
				chatShow:false,
				chat_param:{},
			}
		},
		components: {
		    'qylogin': httpVueLoader('../../user/member/component/qylogin.vue'),
			'company_job': httpVueLoader('../../user/company/component/company_job.vue'),
			'comlog_index': httpVueLoader('../../user/company/component/comlog_index.vue'),
			'comlog_useridmsg': httpVueLoader('../../user/company/component/comlog_useridmsg.vue'),
			'resumedown': httpVueLoader('../../user/company/component/resume_down.vue'),
			'followlog': httpVueLoader('./followLog.vue'),
			'company_order': httpVueLoader('../../user/company/component/company_order.vue'),
			'chatfriend': httpVueLoader('../../tool/chat/component/chatfriend.vue'),
		},
		mounted() {
			this.getInfo();

		},
		methods: {
			showchatfriend:function(){
				this.chat_param = {uid:this.comInfo.uid,usertype:2};
				this.chatShow = true;
			},
			crmorder:function(){
				this.order_param = {com_uid:this.comInfo.uid,order_state:2,order_type:1};
				this.orderShow = true;
			},
			followlog:function(){
				this.follow_param = {comid:this.comInfo.uid};
				this.followShow = true;
			},
			yqlist:function(){
				this.yq_param = {comid:this.comInfo.uid};
				this.yqShow = true;
			},
			downlist:function(){
				this.down_param = {comid:this.comInfo.uid};
				this.downShow = true;
			},
			sqlist:function(param={}){
				param.com_id = this.comInfo.uid;
				this.sq_param = param;
				this.sqShow = true;
			},
			joblist:function(type){
				this.joblist_param = {uid:this.comInfo.uid};
				if(type==1){
					this.joblist_param.state = 1;
					this.joblist_param.status = 2;
				}
				this.jobShow = true;
			},
			loginlog:function(type){

				var param = {type:'3',keyword:this.comInfo.uid};

				if(type==1){
					param.date = this.persona.loginDtype=='周'?'week':'month';
				}

				this.loginlog_param = param;

				this.loginlogShow = true;
			},
			async getInfo() {
				let that = this;
				let params = {
					id: that.comid
				}

				this.loading = true;
				httpPost('m=crm&c=crm_my_customer&a=comPersona', params).then((result) => {
					this.loading = false;
					var res = result.data;
					if (res.error == 0) {
						that.comInfo = res.data.comInfo;
						that.persona = res.data.persona;
					}
				}).catch(function(e) {
					console.log(e)
				})
			},
			handleClick(tab, event) {
				if (this.activeName == 'second') {
					if (this.behavior.logList.length === 0) {
						this.getloglist();
					}
				} else if (this.activeName == 'orderList') {

					this.getOrderList();
				} else if (this.activeName == 'third') {

					this.getChartData();
				}
			},
			// 行为分析
			getloglist: function() {
				let _this = this,
					behavior = _this.behavior;
				let url = 'm=crm&c=crm_my_customer&a=getwebloglist';
				httpPost(url, {
					id: _this.comid,
					page: behavior.pagenav,
				}).then(function(response) {
					let res = response.data;
					if (res.error == 0) {
						if (res.data.webLogList.length > 0) {
							_this.behavior.pageCode = 1;
							_this.behavior.pagenav++;
							_this.behavior.logList = behavior.logList.concat(res.data.webLogList)
						} else {
							_this.behavior.pageCode = 2;
						}
					}
				})
			},
			orderSortChange: function(e) {
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
			getOrderList: function() {
				let that = this;
				let params = {
					id: that.comid,
					page: that.orderCurrentPage,
					limit: that.orderLimit,
					t: that.orderSearch.sort_t,
					order: that.orderSearch.order
				}
				that.loadingorder = true;
				that.emptytextorder = '数据加载中';
				httpPost('m=crm&c=crm_my_customer&a=getOrderList', params).then(function(res) {
					var data = res.data;
					if (data.error == 0) {

						that.orderList = data.data.orderList;
						that.orderTotal = parseInt(data.data.total)
						that.orderPageSizes = data.data.page_sizes
						that.orderLimit = data.data.page_size
                        if(that.prevOrderPage != that.orderCurrentPage){
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
				}).catch(function(e) {})
			},
			async getChartData() {

				var self = this;
				var params = {
					comid: self.comid
				};
				httpPost('m=crm&c=crm_my_customer&a=getChartData', params, {
					hideloading: true
				}).then(function(result) {

					var res = result.data;
					if (res.error == 0) {
						self.xAxis = res.data.xAxis;
						self.scanNumArr = res.data.scanNumArr;
						self.initEcharts1();
					}
				}).catch(function(e) {
					console.log(e)
				})

			},
			initEcharts1() {
				var self = this;
				var myChart = echarts.init(document.getElementById('huaxchat1'));
				var option = {
					tooltip: {
						trigger: 'axis'
					},
					color: ['#0062ff', '#30c3ce'],
					grid: {
						top: '10%',
						left: '1%',
						right: '3%',
						bottom: '1%',
						containLabel: true
					},
					xAxis: {
						type: 'category',
						boundaryGap: false,
						axisLine: {
							show: false
						},
						axisTick: {
							show: false
						},
						splitLine: {
							lineStyle: {
								type: 'dashed',
								color: '#f3f3f3'
							},
							show: false
						},
						data: self.xAxis,
					},
					yAxis: {
						type: 'value',
						axisTick: {
							show: false
						},
						axisLine: {
							show: false
						},
						splitLine: {
							lineStyle: {
								color: '#eee',
								type: 'dashed'
							},
							show: true
						},
					},
					series: [{
						name: '活跃次数',
						data: self.scanNumArr,
						type: 'line',
						stack: '总量',
						smooth: true,
						areaStyle: {
							normal: {
								//右，下，左，上
								color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
										offset: 0,
										color: '#156EFD'
									},
									{
										offset: 0.9,
										color: '#fff'
									}
								], false),
							}
						},
					}, ]
				};
				myChart.setOption(option);
				setTimeout(function() {
					window.addEventListener("resize", () => {
						myChart.resize();
					});
				}, 200);
			},

		},
	};
</script>
<style scoped>
	.cur_p{cursor: pointer}
</style>