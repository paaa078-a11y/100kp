<template>
	<div :class="{moduleElenAl: true, followLogDrawer: drawer}">
		<div class="moduleSeachs" v-if="cansearch" style="padding: 0 0 8px 0;">
			<div class="moduleElTabInpt">
				<div class="moduleInptList" v-if="crmUserOptions.length > 0">
					<el-select size="small" v-model="search.crm_uid" placeholder="业务员" clearable @change="handleSearch">
                        <el-option v-if="tab==1" label="全部下属" value="all"></el-option>
						<el-option v-for="item in crmUserOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
					<el-select size="small" v-model="search.follow_type" placeholder="跟进方式" clearable @change="handleSearch">
						<el-option v-for="item in followTypeOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList" style="padding-right: 0;">
					<el-input size="small" @keyup.enter.native="handleSearch" placeholder="请输入检索关键词" v-model="search.keyword" class="input-with-select" style="margin-right: 8px; width: 340px;" clearable>
						<el-select v-model="search.type" slot="prepend" placeholder="请选择">
							<el-option label="客户名称" value="1"></el-option>
							<el-option label="跟进内容" value="2"></el-option>
						</el-select>
					</el-input>
				</div>
				<div class="moduleInptList">
					<el-date-picker size="small" v-model="search.time" style="width: 250px;" type="daterange" align="right" unlink-panels range-separator="至" start-placeholder="跟进开始" end-placeholder="结束日期" :picker-options="timeOptions" value-format="timestamp" clearable @change="handleSearch"></el-date-picker>
				</div>
				<div class="moduleInptList">
					<el-button type="primary" icon="el-icon-search" size="mini" @click="handleSearch">查询</el-button>
				</div>
			</div>
		</div>
		<div class="moduleElTable" style="padding: 0; width: 100%; margin-top: 0; height: calc(100% - 102px);">
			<el-table :data="tableData" border style="width: 100%" stripe :empty-text="emptytext"
					  :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%"
					  @selection-change="handleSelectionChange" :default-sort="{prop: 'id', order: 'descending'}"
					  @sort-change='sortChange' ref="followLogTable" v-loading="loading">
				<el-table-column type="selection" width="55"></el-table-column>
				<el-table-column label="客户信息" min-width="230">
					<template slot-scope="props">
						<div class="moduleProps">
							<el-button type="text" v-if="props.row.com_name != ''" @click="openComInfo(props)">{{ props.row.com_name }}</el-button>
							<span v-else style="color: red;">客户信息未完善</span>
							<div class=""> {{ props.row.link_man }}{{ props.row.link_tel }}</div>
						</div>
					</template>
				</el-table-column>


				<el-table-column prop=" " label="跟进内容" min-width="300">
				<template slot-scope="props">
				<div class="" style="color: #f00 ;"> {{ props.row.content }} </div>
				<div class="gsd " style="padding-top: 5px;;"><el-tag size="mini" type="info">{{ props.row.type_n }}</el-tag> {{ props.row.aname }} {{ props.row.ftime_n }}</div>
				</template>
				</el-table-column>
				<el-table-column label="是否录音" min-width="90" align="center">
					<template slot-scope="props">
						<el-tag type="success" v-if="props.row.oss_record_path != ''" style="cursor: pointer; width: 87px;" @click="playVoice(props.row.id, props.row.oss_record_path)">
							<i class="yuYinBoxWt" :id="'onPlay_'+props.row.id">{{props.row.time_length_n}}</i>
						</el-tag>
						<span v-else>否</span>
					</template>
				</el-table-column>
				<el-table-column label="业务员"  min-width="140" align="center">
					<template slot-scope="props">
						<div class="moduleProps">
							<span v-if="props.row.crm_uid > 0">{{ props.row.crm_name }}</span>
							<span v-else>已释放<el-tooltip class="item" effect="dark" :content="'前业务员：'+props.row.pcrmname+'；释放时间：'+props.row.sf_time" placement="right"><i class="el-icon-info" style="margin-left: 4px;"></i></el-tooltip></span>
						</div>
					</template>
				</el-table-column>
				<el-table-column prop="atime_n" label="录入时间" width="160"></el-table-column>
				<el-table-column fixed="right" label="操作" width="140">
					<template slot-scope="scope">
						<div class="moduleElTaCaoz" style="display: initial;">
							<el-button size="mini" @click="openComInfo(scope)">管理</el-button>
							<el-button size="mini" @click="delFollowLog(scope)" type="danger">删除</el-button>
						</div>
					</template>
				</el-table-column>
			</el-table>
		</div>
		<div class="modulePaging">
			<div>
				<el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange"> 全选 </el-checkbox>
				<el-button size="mini" style="margin-left: 20px;" @click="delFollowLogSel">批量删除</el-button>
			</div>
			<div class="modulePagNum">
				<el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size.sync="pageSize" :page-sizes="pageSizes" layout="total, sizes, prev, pager, next, jumper" :total="total"></el-pagination>
			</div>
		</div>
		<div id="recordVoice" class="none"></div>
		<!--弹窗：客户管理-->
		<div class="modluDrawer">
			<el-drawer title="客户管理" :visible.sync="comInfoDrawer" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
				<div class="modluDrawerKehuguanli">
				<com_info ref="comInfo" :cuid="cuid" v-if="comInfoDrawer"></com_info>
				</div>
			</el-drawer>
		</div>
	</div>
</template>

<script>
	var isIELowVision = false;

	$(document).ready(function () {
		isIELowVision = IEVersion();
	});

	function IEVersion() {
		//取得浏览器的userAgent字符串
		var userAgent = navigator.userAgent;
		//判断是否IE浏览器
		var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1;
		if (isIE) {
			var reIE = new RegExp("MSIE (\\d+\\.\\d+);");
			reIE.test(userAgent);
			var fIEVersion = parseFloat(RegExp["$1"]);
			if (fIEVersion < 10) {
				return true;
			}
		} else {
			return false;
		}
	}

	module.exports = {
		data: function () {
			return {
				emptytext: '暂无数据',
				loading: false,
				tab: 0,
				cansearch: true,
				search: {
					crm_uid: '',
					follow_type: '',
					type: '1',
					keyword: '',
					time: '',
					comid:'',
				},
				crmUserOptions: [],
				followTypeOptions: [],
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

				tableData: [],
				total: 0,

				currentPage: 1,
                prevPage: 0,
				pageSize: 0,
				pageSizes: [],

				// 批量选择
				checkAll: false,
				isIndeterminate: false,
				selectedItem: [],

				ossUrl: '',

				comInfoDrawer: false,
				cuid: '',
                orgUid:[]
			}
		},
		components: {
			'com_info': httpVueLoader('./comInfo.vue'),
		},
		props: {
            drawer: {
                type: Boolean, // 是否是抽屉方式打开组件，默认否
                default: false
            },
			log_tab: Number,
			day: {
			    type: [Number,String],
			    default: ''
			},
            uid: {
                type: [Number,String],
                default: ''
            },
			source: { // 加载来源
				type: String,
				default: ''
			},
			source_data: { // 来源数据
				type: Object,
				default: null
			},
			from: {
			    type: String,
			    default: ''
			},
			param:{
				type: Object,
				default: function(){
					return {};
				}
			},
			ywy: {
                type: Boolean,
				default: false
			}
		},
		mounted(){
			this.sourceDataHandle();
			this.getStaticData();
			let params = window.parent.homeapp.$route.params;
			if (params.follow == 'all'){

				this.search.crm_uid = params.crm_uid;
				this.tab = 0;
				this.getFollowLog();
			}else if (params.today == '1'){	//	今日跟进

				const timestamp = Math.round(new Date());
				this.search.time = [timestamp, timestamp];
				this.tab = 0;
				this.getFollowLog();
			}else if(this.from == 'persona' || this.tab == 0 || this.tab == 1){

				this.getFollowLog();
			}
		},
		watch: {
			searchable: {
				handler(val) {
					if (typeof val !== 'undefined') {
						this.cansearch = val;
					}
				},
				immediate: true,
				deep: true,
			},
			log_tab: {
				handler(val) {
					this.tab = val;
				},
				deep: true,
				immediate: true
			},
			day: {
				handler(val, oldVal) {
					const end = new Date();
					const start = new Date();
					if(val=='1'){

					}else if(val=='2'){
						start.setTime(start.getTime() - 3600 * 1000 * 24);
					}else if(val=='3'){
						start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
					}else if(val=='4'){
						start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
					}
					if (val) {
						this.search.time = [start.getTime(), end.getTime()];
					}
				},
				immediate: true,
			},
			uid: {
				handler(val) {
					this.search.crm_uid = val
					if (this.search.crm_uid) {
						this.getFollowLog(0);
					}
				},
				immediate: true,
			},
			source_data: {
				handler(val, oldVal) {
					this.resetSearch();
					this.sourceDataHandle();
					this.handleSearch();
				}
			},
			param: {
				handler(obj) {
					if (!$.isEmptyObject(obj)) {
						for(let i in obj){
							if(typeof this.search[i]!='undefined'){
								this.search[i] = obj[i];
							}
						}
					}
				},
				immediate: true,
				deep: true,
			},
		},
		methods: {
			resetSearch: function() {
				// 新增搜索参数需要同步修改
				this.search = {
					crm_uid: '',
					follow_type: '',
					type: '1',
					keyword: '',
					time: ''
				};
			},
			setSearch: function(uid){

				let _this = this;
				_this.search.crm_uid = uid;
				_this.getFollowLog();
			},
			getFollowLog() {
				var that = this;
				var params = JSON.parse(JSON.stringify(this.search));
                if (this.search.crm_uid != '') {
                    params.crm_uid = this.search.crm_uid;
                    if (this.search.crm_uid == 'all') {
                        params.orgUids = this.orgUid.join(',');
                    }
                }
				params.pageSize = that.pageSize;
				params.page = that.currentPage;
				params.tab = that.tab;
				that.loading = true;
				that.emptytext = "数据加载中";
				var url = '';
				if(this.from == 'persona'){
					url = 'm=crm&c=crm_my_customer&a=getFollowLog';
				} else {
                    if (this.ywy == true) {
                        url = 'm=crm&c=followLogYwy&a=getFollowLog';
                    } else {
                        url = 'm=crm&c=followLog&a=getFollowLog';
                    }
				}
				httpPost(url, params).then(function (res) {
					let data = res.data.data;
					that.tableData = data.list;
					that.total = data.total;
					that.pageSize = parseInt(data.pageSize);
					that.pageSizes = data.pageSizes;
                    if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        that.$refs.followLogTable.bodyWrapper.scrollTop = 0;
                    }
					that.loading = false;
					if (that.tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
				}).catch(function (error) {
					console.log(error);
				})
			},
			getStaticData:function(){
				var that = this
				let params = {}
				if(this.from == 'persona'){
					url = 'm=crm&c=crm_my_customer&a=getFollowStaticData';
				}else{
                    if (this.ywy == true) {
                        url = 'm=crm&c=followLogYwy&a=getFollowStaticData';
                    } else {
                        url = 'm=crm&c=followLog&a=getFollowStaticData';
                    }
				}
				httpPost(url,params,{hideloading:true}).then((result) => {
					var res = result.data;
					if (res.error == 0) {
						that.crmUserOptions = [];
						that.orgUid = [];
						res.data.crmUser.forEach(function (item) {
							that.crmUserOptions.push({value: item.uid, label: item.name})
                            that.orgUid.push(item.uid);
						});
						that.followTypeOptions = [];
						res.data.followType.forEach(function (item) {
							that.followTypeOptions.push({value: item.id, label: item.name})
						});
						that.ossUrl = res.data.contactOssUrl;
					}
				});
			},
			sortChange: function (e) {
				this.search.t = e.prop;
				this.search.order = e.order == 'ascending' ? 'asc' : 'desc';
				this.handleSearch();
			},
			handleSearch: function () {
				this.currentPage = 1
				this.getFollowLog()
			},
			handleSelectionChange(val) {
				this.selectedItem = val;
				if (this.selectedItem.length == 0) {
					this.isIndeterminate = false;
					this.checkAll = false;
				} else {
					if (this.selectedItem.length == this.tableData.length) {
						this.isIndeterminate = false;
						this.checkAll = true;
					} else {
						this.isIndeterminate = true;
						this.checkAll = false;
					}
				}
			},
			handleCheckAllChange(val) {
				val ? this.$refs.followLogTable.toggleAllSelection() : this.$refs.followLogTable.clearSelection();
			},
			playVoice: function(chatId, path){
				let that = this
				let voiceUrl = that.ossUrl + path;
				let onplay = false;
				if ($('.yuYinBoxOnPlay')) {
					$('.yuYinBoxOnPlay').attr('class', 'yuYinBoxWt');
				}
				if (isIELowVision) {
					var head = document.head || document.getElementsByTagName('head')[0];
					var bgSound = document.getElementsByTagName('bgSound')[0];
					if (!bgSound) {
						let player = document.createElement('bgSound');
						head.appendChild(player);
						player.src = voiceUrl;
						onplay = true;
					}else{
						if (bgSound.src == '') {
							bgSound.src = voiceUrl;
							onplay = true;
						} else {
							bgSound.src = '';
						}
					}
				} else {
					let audio = $("#player")[0];
					if(audio && !audio.paused && audio.src==voiceUrl){
						audio.pause();
						audio.currentTime = 0;
					}else{
						$("#recordVoice").html("")
						//来一个audio内容，看好这里已经给src赋值了
						var aud = $('<audio id="player"  src="' + voiceUrl + '" hidden></audio>')
						//放到容器里区
						$("#recordVoice").html(aud)
						//获取当前audio
						audio = aud[0];
						audio.play(); // 播放
						onplay = true;
					}
					audio.addEventListener("play", function() { //开始播放时触发
						//console.log('开始播放');
					});
					audio.addEventListener("pause", function() { // 暂停时会触发，当播放完一首歌曲时也会触发
						//console.log('播放暂停');
					})
					audio.addEventListener("ended", function() {
						//console.log('播放结束');
						$('.yuYinBoxOnPlay').attr('class', 'yuYinBoxWt');
					}, false);
				}

				if (onplay) {
					$('#onPlay_' + chatId).attr('class', 'yuYinBoxOnPlay');
				} else {
					$('#onPlay_' + chatId).attr('class', 'yuYinBoxWt');
				}
			},
			delFollowLog(scope, isMore) {
				var that = this;
				let name = '';
				let idArr = [], nameArr = [];
				let params = {};
				if (isMore) {
					this.selectedItem.forEach((item) => {

						idArr.push(item.id);
						if (nameArr.indexOf(item.com_name) == -1) {
							nameArr.push(item.com_name);
						}
					});
					name = '（' + nameArr.join('，') + '）';
					params.id = idArr;
				} else {

					name = '（' + scope.row.com_name + '）';
					params.id = scope.row.id;
				}

				delConfirm(this, params, this.delete, '您确定要删除客户'+name+'跟进记录吗？');
			},
			delFollowLogSel: function () {
				var that = this;
				if (!that.selectedItem.length) {
					message.error('请选择要删除的客户跟进记录');
					return;
				}
				this.delFollowLog(null, true);
			},
			delete(params){
				var self = this, url = '';
				if (this.ywy == true) {
                    url = 'm=crm&c=followLogYwy&a=delFollowLog'
				} else {
				    url = 'm=crm&c=followLog&a=delFollowLog'
				}
				httpPost(url, params).then(function (response) {
					let res = response.data;
					if (res.error == 0) {
						message.success(res.msg, function () {
							self.getFollowLog();
						});
					} else {
						message.error(res.msg);
					}
				}).catch(function (error) {
					console.log(error);
				})
			},
			handleSizeChange(val) {
				this.pageSize = val;
				this.getFollowLog();
			},
			handleCurrentChange(val) {
				this.currentPage = val;
				this.getFollowLog();
			},
			openComInfo: function (scope) {
				this.comInfoDrawer = true;
				this.cuid = scope.row.uid
			},

			sourceDataHandle() {
				let source_data = this.source_data;
				if (!source_data) { // 无数据
					return false;
				}

				if (source_data.crm_uid) {
					this.search.crm_uid = source_data.crm_uid;
				}
				if (source_data.times) {
					this.timesHandle(); // 目前仅工作台使用
				}
			},
			// 加载组件，字符串时间转换
			timesHandle() {
				let source_data = this.source_data;
				if (!source_data || !source_data.times) { // 无数据
					return false;
				}

				let now = new Date();
				if (source_data.times == 'month') {
					// 获取当前月份的第一天
					let firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
					// 获取当前月份的最后一天
					let lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
					this.search.time = [firstDay.getTime(), lastDay.getTime()];
				} else if (source_data.times == 'yesterday') {
					now.setHours(0, 0, 0, 0); // 将小时、分钟、秒和毫秒都设置为0
					let yestarday = now.setDate(now.getDate() - 1);
					this.search.time = [yestarday, yestarday];
				} else if (source_data.times == 'day7') {
					now.setHours(0, 0, 0, 0); // 将小时、分钟、秒和毫秒都设置为0
					let end = now.getTime();
					this.search.time = [now.setDate(now.getDate() - 6), end];
				}
			}
		},
	};
</script>
<style scoped>
	/* 针对抽屉弹出单独增加的样式开始 */
    .followLogDrawer {
		overflow: hidden;
		position: relative;
		width: 100%;
		height: 100%;
		padding: 0 20px;
    }
	.followLogDrawer .moduleElTable {
		margin-top: 0;
		height: calc(100% - (60px + 50px + 10px));
	}
	/* 针对抽屉弹出单独增加的样式结束 */
	.yuYinBoxWt{width: 15px;height: 15px;background: url(../../images/bf.png) no-repeat;padding-left: 18px;}
	.yuYinBoxOnPlay{width: 15px;height: 15px;background: url(../../images/bfz.gif) no-repeat;padding-left: 18px;}
</style>