<!--权益提醒-->
<template>
	<div class="drawerModlue">

		<div class="moduleElTable">
			<el-table ref="table" :key="timer" :data="tableData" v-loading="list_loading" border style="width: 100%"
				stripe :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" :empty-text="emptytext">
				<el-table-column prop="uid" :label="uid_label" width="100"></el-table-column>
				<el-table-column label="姓名" width="170" v-if="nameshow">
					<template slot-scope="scope">
						<el-link type="primary" :underline="false"
							@click="showuser(scope.row.uid)">{{scope.row.name}}</el-link>
					</template>
				</el-table-column>
				<el-table-column label="企业名称" width="170">
					<template slot-scope="scope">
						<el-link type="primary" :underline="false"
							@click="showcom(scope.row.comid)">{{scope.row.comname}}</el-link>
					</template>
				</el-table-column>
				<el-table-column prop="jobname" label="职位名称" v-if="jobshow"></el-table-column>
				<el-table-column label="简历查看状态" v-if="bodyshow">
					<template slot-scope="scope">
						<div v-if="scope.row.body!=''">取消申请,原因：{{scope.row.body}}</div>
						<div v-else>未查看</div>
					</template>
				</el-table-column>
				<el-table-column prop="content" label="端口" v-if="type=='4'"></el-table-column>
				<el-table-column prop="ip" label="IP" v-if="type=='4'"></el-table-column>
				<el-table-column prop="city" label="IP归属地" v-if="type=='4'"></el-table-column>
				<el-table-column prop="time" label="时间"></el-table-column>
			</el-table>
		</div>
		<div class="modulePaging">
			<div class="modulePagNum">
				<el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
					:current-page="currentPage" :page-size.sync="limit" :page-sizes="page_sizes" :total="total"
					layout="total, sizes, prev, pager, next, jumper">
				</el-pagination>
			</div>
		</div>

		<div class="modluDrawer">
			<el-dialog :title="`企业数据  ${com.time}`" :visible.sync="comShow" :with-header="true"
				:modal-append-to-body="false" append-to-body :show-close="true" width="610px">

				<div class="admin_comtck_box">
					<div class="admin_comtck_hd">
						<div class="comtck_hd_name">
							<el-link type="primary" :underline="false" @click="comPreview">{{com.name}}</el-link>
							<div class="comtck_hd_btn">
								<el-button type="danger" size="mini" @click="lock(com.uid,'2')">锁定账号</el-button>
							</div>
						</div>
						<div class="comtck_hd_userInfo">
							<span>用户名：{{com.username}}</span>
							<span>联系电话：{{com.link || '无'}}</span>
							<span>用户ID：{{com.uid}}</span>
							<span>状态：{{com.status}}</span>
						</div>
					</div>

					<div class="admin_comtck_md">
						<ul class="comtck_md_list">
							<li><a style="cursor: default;">
									<div>{{com.lookresume_num}}</div><span>简历浏览</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.downresume_num}}</div><span>简历下载</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.downdel_num}}</div><span>删除简历下载记录</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.yqms_num}}</div><span>邀请面试</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.addjob_num}}</div><span>发布职位</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.refreshjob_num}}</div><span>刷新职位</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.sqjob_num}}</div><span>收到简历</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.sqdel_num}}</div><span>删除收到简历记录</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.chatlog_num}}</div><span>直聊人数</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.loginlog_num}}</div><span>登录次数</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.privacylog_num}}</div><span>简历隐私号</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{com.weblog_num}}</div><span>IP活跃次数</span>
								</a></li>
						</ul>
					</div>
				</div>

			</el-dialog>
		</div>
		<div class="modluDrawer">
			<el-dialog :title="`求职者数据  ${user.time}`" :visible.sync="userShow" :with-header="true"
				:modal-append-to-body="false"  append-to-body :show-close="true" width="610px">

				<div class="admin_comtck_box">
					<div class="admin_comtck_hd">
						<div class="comtck_hd_name">
							<el-link type="primary" :underline="false" @click="userPreview">{{user.name}}</el-link>
							<div class="comtck_hd_btn">
								<el-button type="danger" size="mini" @click="lock(user.uid,'2')">锁定账号</el-button>
							</div>
						</div>
						<div class="comtck_hd_userInfo">
							<span>用户名：{{user.username}}</span>
							<span>联系电话：{{user.link || '无'}}</span>
							<span>用户ID：{{user.uid}}</span>
							<span>状态：{{user.status}}</span>
						</div>
					</div>

					<div class="admin_comtck_md">
						<ul class="comtck_md_list">
							<li><a style="cursor: default;">
									<div>{{user.tdnum_num}}</div><span>简历投递数量</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{user.tdclass_num}}</div><span>简历投递分类</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{user.usersqdel_num}}</div><span>删除简历投递记录</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{user.lookjob_num}}</div><span>职位浏览</span>
								</a></li>
							<li><a style="cursor: default;">
									<div>{{user.tellog_num}}</div><span>职位拨号</span>
								</a></li>
							<li style="border-bottom: 1px solid #eee;"><a style="cursor: default;">
									<div>{{user.chatlog_num}}</div><span>直聊人数</span>
								</a></li>
							<li style="border-bottom: 1px solid #eee;"><a style="cursor: default;">
									<div>{{user.loginlog_num}}</div><span>登录次数</span>
								</a></li>
							<li style="border-bottom: 1px solid #eee;"><a style="cursor: default;">
									<div>{{user.privacylog_num}}</div><span>职位隐私号</span>
								</a></li>
							<li style="border-right: 1px solid #eee;"><a style="cursor: default;">
									<div>{{user.weblog_num}}</div><span>IP活跃次数</span>
								</a></li>
						</ul>
					</div>
				</div>

			</el-dialog>
		</div>
		<div class="modluDrawer">
		    <el-drawer title="简历预览" :append-to-body="true" :visible.sync="resumePreviewVisible" :destroy-on-close="true" size="530px">
		        <resume_preview :id="user.eid" v-if="resumePreviewVisible"></resume_preview>
		    </el-drawer>
		</div>
		<div class="modluDrawer">
		    <el-drawer title="企业预览" :append-to-body="true" :visible.sync="comPreviewVisible" :destroy-on-close="true" size="530px">
		        <com_preview :uid="com.uid" v-if="comPreviewVisible"></com_preview>
		    </el-drawer>
		</div>
	</div>
</template>

<script>
	module.exports = {
			props: {
				usertype: {
					type: String,
					default: ''
				},
				uid: {
					type: String,
					default: ''
				},
				type: {
					type: String,
					default: ''
				},
				timev: {
					type: String,
					default: ''
				},
				job_id: {
					type: String,
					default: ''
				},
			},
			data: function() {
				return {
					emptytext: '暂无数据',
					title: '',
					tableData: [],
					total: 0,
					limit: 0,
					currentPage: 1,
                    prevPage: 0,
					page_sizes: [],

					list_loading: false,

					uid_label: 'UID',
					nameshow: true,
					jobshow: true,
					bodyshow: true,
					timer: 0,

					com: {},
					comShow: false,
					user: {},
					userShow: false,
					resumePreviewVisible:false,
					comPreviewVisible:false
				}
			},
			components: {
			    'resume_preview': httpVueLoader('../../component/resume_preview.vue'),
				'com_preview': httpVueLoader('../../component/com_preview.vue'),
			},
			mounted() {

				this.uid_label = this.type != '4' ? '求职者UID' : '企业UID';
				this.nameshow = this.type != '4' ? true : false;

				if (['3', '4', '7', '9'].indexOf(this.type) != -1) {
					this.jobshow = false;
				} else {
					this.jobshow = true;
				}

				this.bodyshow = this.type == '2' ? true : false;
				this.timer = new Date().getTime();
				this.getStaticData();
				this.getList();
			},
			methods: {
				getStaticData(){
				    var that = this
					let params = {
						type: that.type,
						uid: that.uid,
						usertype: that.usertype,
						job_id: that.job_id
					}
				    httpPost('m=crm&c=crm_my_customer&a=getAbDStaticData',params,{hideloading:true}).then((result) => {
						var res = result.data;
						if (res.error == 0) {
							that.title = res.data.title
							that.$emit("set-title", {
								title: that.title
							});
						}
				    });
				},
				async getList() {
					let that = this;
					let params = {
						page: that.currentPage,
						limit: that.limit,
						type: that.type,
						uid: that.uid,
						time: that.timev,
						usertype: that.usertype,
						job_id: that.job_id
					}

					this.list_loading = true;
					that.emptytext = "数据加载中";
					httpPost('m=crm&c=crm_my_customer&a=abnormalDetail', params).then((result) => {
						this.list_loading = false;
						var res = result.data;

						if (res.error == 0) {

							that.tableData = res.data.list
							that.total = parseInt(res.data.total)
							that.page_sizes = res.data.page_sizes
							that.limit = res.data.page_size;
                            if(that.prevPage != that.currentPage){
                                that.prevPage = that.currentPage;
                                that.$refs.table.bodyWrapper.scrollTop = 0;
                            }
							if (that.tableData.length === 0){
	                            that.emptytext = "暂无数据";
	                        }

						}
					}).catch(function(e) {
						console.log(e)
					})
				},
				search: function() {
					this.currentPage = 1;
					this.getList();
				},
				handleCurrentChange(val) {
					this.currentPage = val;
					this.getList()
				},
				handleSizeChange(val) {
					this.currentPage = 1
					this.limit = val
					this.getList()
				},
				showcom: function(uid) {

					var that = this;
					var params = {
						uid: uid,
						time: that.timev
					};
					httpPost('m=crm&c=crm_my_customer&a=comstatis', params).then((result) => {
						this.list_loading = false;
						var res = result.data;

						if (res.error == 0) {
							that.com = res.data;
							that.comShow = true;

						}
					}).catch(function(e) {
						console.log(e)
					})
				},
				showuser: function(uid) {

					var that = this;
					var params = {
						uid: uid,
						time: that.timev
					};
					httpPost('m=crm&c=crm_my_customer&a=userstatis', params).then((result) => {
						this.list_loading = false;
						var res = result.data;

						if (res.error == 0) {
							that.user = res.data;
							that.userShow = true;

						}
					}).catch(function(e) {
						console.log(e)
					})
				},
				lock: function(uid,type) {
					this.$prompt('请填写锁定原因', '锁定原因', {
						confirmButtonText: '确定',
						cancelButtonText: '取消',
						inputType:'textarea'
					}).then(({
						value
					}) => {
						var params = {
							uid: uid,
							type: type,
							lock_info: value,
						};
						startLoading();
						httpPost('m=crm&c=crm_my_customer&a=lock', params).then((result) => {
							endLoading();
							var res = result.data;
							if (res.error == '9') {
								message.success(res.msg)
							}else{
								message.error(res.msg)
							}

						}).catch(function(e) {
							console.log(e)
						})
					}).catch(() => {
					});
				},
				comPreview: function() {
					this.comPreviewVisible = true;
				},
				userPreview: function() {
					this.resumePreviewVisible = true;
				}
			}
	}
</script>
<style scoped>
	.moduleInptList .el-select .el-input {
		width: 140px;
	}
</style>