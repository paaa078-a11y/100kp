<!--crm_chat_resume-->
<template>
	<div class="drawerModlue" v-loading="addloading">
	    <div class="drawerModInfo">
	        <div>基本资料</div>
	        <div class="drawerModLis">
	            <div class="drawerModTite">
	                <span><font color="#FF0000">*</font>用户姓名</span>
	            </div>
	            <div class="drawerModInpt">
	                <el-input v-model="name" placeholder=""></el-input>
	            </div>
	        </div>
	        <div class="drawerModLis">
	            <div class="drawerModTite">
	                <span><font color="#FF0000">*</font>性别</span>
	            </div>
	            <div class="drawerModInpt">
	                <el-radio-group v-model="sex">
	                    <el-radio v-for="(sexv, key) in user_sex" :key="key" :label="key">{{sexv}}</el-radio>
	                </el-radio-group>
	            </div>
	        </div>
	        <div class="drawerModLis">
	            <div class="drawerModTite">
	                <span><font color="#FF0000">*</font>教育程度</span>
	            </div>
	            <div class="drawerModInpt">
	                <el-select v-model="edu" placeholder="请选择">
	                    <el-option v-for="item in user_edu" :key="item.val" :label="item.name" :value="item.val"></el-option>
	                </el-select>
	            </div>
	        </div>
	        
	        <div class="drawerModLis">
	            <div class="drawerModTite">
	                <span><font color="#FF0000">*</font>工作经验</span>
	            </div>
	            <div class="drawerModInpt">
	                <el-select v-model="exp" placeholder="请选择">
	                    <el-option v-for="item in user_word" :key="item.val" :label="item.name" :value="item.val"></el-option>
	                </el-select>
	            </div>
	        </div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>出生年月</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-date-picker v-model="birthday" type="month" placeholder="选择月" value-format="yyyy-MM" :picker-options="pickerOptions"></el-date-picker>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>手机</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-input type="number" v-model="telphone" placeholder=""></el-input>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>现居住地</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-input v-model="living" placeholder=""></el-input>
			    </div>
			</div>
			
	        <div>求职意向</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>期望职位</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-input v-model="expect_name" placeholder=""></el-input>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>工作职能</span>
			    </div>
			    <div class="drawerModInpt">
					<job_class multiple :max="5" @confirm="confirmJob" :selected="jobSelected"></job_class>
				</div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>工作地点</span>
			    </div>
			    <div class="drawerModInpt">
					<city_class multiple :max="5" @confirm="confirmCity" :selected="citySelected"></city_class>
				</div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>期望薪资</span>
			    </div>
			    <div class="drawerModInpt">
			        <div class="TableInpt" style="max-width: 500px;">
			            <el-input type="number" style="margin-right: 12px;" v-model="minsalary" placeholder=""> </el-input>
			            <el-input type="number" v-model="maxsalary" placeholder=""> </el-input>
			        </div>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>从事行业</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-select v-model="hy" placeholder="请选择">
			            <el-option v-for="item in industry" :key="item.val" :label="item.name" :value="item.val"></el-option>
			        </el-select>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>到岗时间</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-select v-model="report" placeholder="请选择">
			            <el-option v-for="item in user_report" :key="item.val" :label="item.name" :value="item.val"></el-option>
			        </el-select>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>工作性质</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-select v-model="type" placeholder="请选择">
			            <el-option v-for="item in user_type" :key="item.val" :label="item.name" :value="item.val"></el-option>
			        </el-select>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span><font color="#FF0000">*</font>求职状态</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-select v-model="jobstatus" placeholder="请选择">
			            <el-option v-for="item in user_jobstatus" :key="item.val" :label="item.name" :value="item.val"></el-option>
			        </el-select>
			    </div>
			</div>
			<div>最近一份工作</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>工作单位</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-input v-model="work_name" placeholder=""></el-input>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>工作时间</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-date-picker v-model="work_sdate" type="month" placeholder="选择开始时间"></el-date-picker>
			        <el-date-picker style="margin: 0 8px;" :disabled="todayCheck" v-model="work_edate" type="month" placeholder="选择结束时间"></el-date-picker>
			        <el-checkbox v-model="todayCheck" @change="todayChange($event, 'work')">至今</el-checkbox>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>担任职位</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-input v-model="work_title" placeholder=""></el-input>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>工作内容</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-input type="textarea" v-model="work_content" :autosize="{ minRows: 3, maxRows: 6 }"></el-input>
			    </div>
			</div>
			<div>教育经历</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>学校名称</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-input v-model="edu_name" placeholder=""></el-input>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>在校时间</span>
			    </div>
			    <div class="drawerModInpt">
					<el-date-picker v-model="edu_sdate" type="month" placeholder="开始日期"></el-date-picker>
					<el-date-picker style="margin-left: 8px;" v-model="edu_edate" type="month" placeholder="结束日期"></el-date-picker>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>最高学历</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-select v-model="edu_education" placeholder="请选择">
			            <el-option v-for="item in user_edu" :key="item.val" :label="item.name" :value="item.val"></el-option>
			        </el-select>
			    </div>
			</div>
			<div class="drawerModLis">
			    <div class="drawerModTite">
			        <span>所学专业</span>
			    </div>
			    <div class="drawerModInpt">
			        <el-input v-model="edu_specialty" placeholder=""></el-input>
			    </div>
			</div>
			
	        <div>自我介绍</div>
	        <div class="drawerModLis">
	            <div class="drawerModTite">
	                <span>自我介绍</span>
	            </div>
	            <div class="drawerModInpt">
	                <el-input type="textarea" :rows="2" v-model="description" placeholder=""></el-input>
	            </div>
	        </div>
	        <div class="setBasicButn" style="border: none;">
	            <el-button type="primary" size="medium" @click="save">保存</el-button>
	        </div>
	    </div>
	
	</div>
</template>

<script>

module.exports = {
	props: {
		uid: {
			type: [String, Number],
			default: ''
		}
	},
	data: function () {
		return {
			user_sex:[],
			user_edu:[],
			user_word:[],
			industry:[],
			user_report:[],
			user_type:[],
			user_jobstatus:[],
			jobclass_list:[],
			cityclass_list:[],
			
			pickerOptions: {
			    disabledDate(time) {
			        let year = parseInt((new Date()).getFullYear());
			        year = year-16;
			        return time.getTime() > new Date(year + '-12-31');
			    },
			},
			jobSelected: null,
			citySelected: null,
			
			name:'',
			sex:'',
			edu:'',
			exp:'',
			birthday:'',
			telphone:'',
			living:'',
			expect_name:'',
			job_classid:'',
			city_classid:'',
			minsalary:'',
			maxsalary:'',
			hy:'',
			report:'',
			type:'',
			jobstatus:'',
			work_name:'',
			work_title:'',
			todayCheck:false,
			work_sdate:'',
			work_edate:'',
			work_content:'',
			edu_name:'',
			edu_sdate: '',
			edu_edate: '',
			edu_education:'',
			edu_specialty:'',
			description:'',
			addloading: false,
			saveloading: false,
		}
	},
	components: {
	    'job_class': httpVueLoader('../../component/job_class.vue'),
	    'city_class': httpVueLoader('../../component/city_class.vue'),
	},
	created: function () {
		
		
		this.getInfo();
	},
	mounted() {
		
	},
	methods: {
		async getInfo() {
			
			let that = this;

			this.addloading = true;

			httpPost('m=crm&c=crm_chat&a=newResume', {uid:that.uid}).then((result) => {

				this.addloading = false;

				var res = result.data;
				if (res.error == 0) {
					var info = res.data.info;
					that.name = info.name;
					that.sex = info.sex;
					that.edu = info.edu && info.edu>0?info.edu:'';
					that.exp = info.exp && info.exp>0?info.exp:'';
					that.telphone = info.telphone;
					that.living = info.living;
					that.birthday = info.birthday?info.birthday:'1988-08';
					
					that.user_sex = res.data.user_sex;
					that.user_word = res.data.user_word;
					that.user_edu = res.data.user_edu;
					that.industry = res.data.industry;
					that.user_report = res.data.user_report;
					that.user_type = res.data.user_type;
					that.user_jobstatus = res.data.user_jobstatus;
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
		confirmJob(data) {
		    this.job_classid = data.jobId.join(',');
		},
		confirmCity(data) {
		    this.city_classid = data.cityId.join(',');
		},
		async save() {
			var that = this;
			var uid = that.uid;
			
			//  基本信息
			var resume_name = that.name;
			if (resume_name == '') {
				message.warning('求职者姓名不能为空！');
				return false;
			}
			var sex = that.sex;
			if (sex == '') {
				message.warning('性别不能为空！');
				return false;
			}
			var edu = that.edu;
			if (edu == '') {
				message.warning('请选择最高学历！');
				return false;
			}
			var exp = that.exp;
			if (exp == '') {
				message.warning('请选择工作经验！');
				return false;
			}
			var birthday = that.birthday;
			if (birthday == '') {
				message.warning('出生年月不能为空！');
				return false;
			}
			var telphone = that.telphone;
			if (telphone == '') {
				message.warning('手机号码不能为空！');
				return false;
			} else if (!isjsMobile(telphone)) {
				message.warning('手机号码格式错误！');
				return false;
			}
			var living = that.living;
			if (living == '') {
				message.warning('现居住地不能为空！');
				return false;
			}
	
			//  求职意向
			var name = that.expect_name;
			if (name == "") {
				message.warning('请填写期望职位！');
				return false;
			}
			var job_classid = that.job_classid;
			if (job_classid == "") {
				message.warning('请选择工作职能！');
				return false;
			}
			var city_classid = that.city_classid;
			if (city_classid == '') {
				message.warning('请选择工作地点！');
				return false;
			}
			var minsalary = that.minsalary;
			var maxsalary = that.maxsalary;
			if (minsalary == "" || parseInt(minsalary) == 0) {
				message.warning('请填写期望薪资！');
				return false;
			}
			if (maxsalary && parseInt(maxsalary) <= parseInt(minsalary)) {
				message.warning('最高薪资必须大于最低薪资！');
				return false;
			}
	
			var hy = that.hy;
	
			var report = that.report;
			if (report == "") {
				message.warning('请选择到岗时间！');
				return false;
			}
			var typeid = that.type;
			if (typeid == "") {
				message.warning('请选择工作性质！');
				return false;
			}
			var jobstatus = that.jobstatus;
			if (jobstatus == "") {
				message.warning('请选择求职状态！');
				return false;
			}
			//  工作经历
			var work_name = that.work_name;
			var work_sdate = that.work_sdate;
			var work_edate = that.work_edate;
			var work_title = that.work_title;
			var work_content = that.work_content;
			if (work_edate != '') {
				var sd_w = new Date(Date.parse(work_sdate));
				var ed_w = new Date(Date.parse(work_edate));
				if (sd_w >= ed_w) {
					message.warning('结束日期必须大于开始日期！');
					return false;
				}
			}
			var workToday = that.todayCheck?1:2;
			
			//  教育经历
			var edu_sdate = that.edu_sdate;
			var edu_edate = that.edu_edate;
			var edu_name = that.edu_name;
			var education = that.edu_education;
			var specialty = that.edu_specialty;
			if (edu_edate != '') {
				var sd_e = new Date(Date.parse(edu_sdate));
				var ed_e = new Date(Date.parse(edu_edate));
				if (sd_e >= ed_e) {
					message.warning('离校日期必须大于入学日期！');
				}
			}
	
			var param = {
				resume_name: resume_name, sex: sex, edu: edu, exp: exp, birthday: birthday, telphone: telphone, living: living,
				name: name, job_classid: job_classid, city_classid: city_classid, minsalary: minsalary, maxsalary: maxsalary, hy: hy, report: report, type: typeid, jobstatus: jobstatus,
				work_name: work_name, work_sdate: work_sdate, work_edate: work_edate, work_title: work_title, work_content: work_content, work_today: workToday,
				edu_name: edu_name, edu_sdate: edu_sdate, edu_edate: edu_edate, education: education, specialty: specialty, uid: uid
			};
			
			this.saveloading = true;
			httpPost('m=crm&c=crm_chat&a=saveResume', param).then((result) => {
				this.saveloading = false;
				var res = result.data;
				if (res.error == 0) {
					message.success(res.msg, () => {

						this.$emit("close-update");
					});
				} else {
					message.error(res.msg);
				}
			}).catch(function (e) {
				console.log(e)
			})
		},
		
		// 至今选择
		todayChange(val, type) {
		    if (type == 'work') {
				this.work_edate = '';
		    }
		},
	},
};
</script>
<style scoped>
.drawerModInfo::-webkit-scrollbar {
	display: none;
}

.drawerModlue {
	overflow: hidden;
	position: relative;
	width: 100%;
	height: 100%;
}

.drawerModInfo {
	overflow-y: auto;
	height: calc(100% - 80px);
}</style>