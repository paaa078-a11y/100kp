<template>
    <div class="moduleElHight" style="padding: 0 20px;">
        <div class="moduleSeachs">
        	<div class="moduleElTabInpt" style="flex-wrap: wrap;">
				<div class="moduleInptList">
					<el-input size="small" @keyup.enter.native="search" v-model="keyword" placeholder="公司或职位名称" prefix-icon="el-icon-search"></el-input>
				</div>
        		<div class="moduleInptList" style="width: 380px;">
					<job_class @confirm="confirmJob" :selected="jobSelected"></job_class>
        		</div>
				<div class="moduleInptList">
					<city_class @confirm="confirmCity" :selected="citySelected"></city_class>
				</div>
        		<div class="moduleInptList">
        			<el-select size="small" v-model="job_exp" placeholder="工作经验" clearable @change="search">
        				<el-option v-for="item in job_exp_arr" :key="item.val" :label="item.name" :value="item.val"></el-option>
        			</el-select>
        		</div>
				<div class="moduleInptList">
					<el-select size="small" v-model="adtime" placeholder="发布日期" clearable @change="search">
						<el-option label="今天" value="1"></el-option>
						<el-option label="最近三天" value="3"></el-option>
						<el-option label="最近七天" value="7"></el-option>
						<el-option label="最近半月" value="15"></el-option>
						<el-option label="最近一个月" value="30"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
					<el-select size="small" v-model="welfare" multiple filterable collapse-tags style="margin-left: 20px;" placeholder="职位福利" @change="search">
					    <el-option v-for="item in job_welfare_arr" :key="item.val" :label="item.name" :value="item.val"> </el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
					<el-select size="small" v-model="job_edu" placeholder="学历要求" clearable @change="search">
						<el-option v-for="item in job_edu_arr" :key="item.val" :label="item.name" :value="item.val"></el-option>
					</el-select>
				</div>

        		<div class="moduleInptList">
        			<el-button type="primary" icon="el-icon-search" @click="search">查询</el-button>
        		</div>
        	</div>
        </div>
        <div class="moduleElTable" style="border: 1px solid #ebeef5; width: calc(100% - 2px); height: calc(100% - 175px);">
            <el-table ref="table" :data="tableData" v-loading="list_loading" :empty-text="emptytext"
                stripe :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%">
                <el-table-column label="职位/企业">
					<template slot-scope="scope">
					    <div class="moduleProps">
					        <div>{{scope.row.name}}</div>
							<div>{{scope.row.com_name}}</div>
						</div>
					</template>
				</el-table-column>
                <el-table-column prop="citystr" label="地区"></el-table-column>
				<el-table-column prop="job_exp" label="经验"></el-table-column>
				<el-table-column prop="job_edu" label="学历"></el-table-column>
				<el-table-column prop="job_salary" label="薪资"></el-table-column>
				<el-table-column prop="lastupdate_n" label="更新时间"></el-table-column>
				<el-table-column label="操作">
					<template slot-scope="scope">
					    <div class="moduleElTaCaoz">
					        <el-button type="primary" size="mini" @click="sendJob(scope.row)">发送</el-button>
						</div>
					</template>
				</el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">

            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total"
                    layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>

    </div>
</template>

<script>
module.exports = {
	props: {
		eid: {
			type: [Number,String],
			default: ''
		},
	},
	components: {
	    'job_class': httpVueLoader('../../../component/job_class.vue'),
	    'city_class': httpVueLoader('../../../component/city_class.vue'),
	},
    data: function () {
        return {
        	emptytext: '暂无数据',
			job_exp_arr:[],
			job_edu_arr:[],
			job_welfare_arr:[],

            tableData: [],
            total: 0,
            limit: 0,
            prevPage: 0,
            currentPage: 1,
            page_sizes: [],

            list_loading: false,

			jobSelected:{},
			citySelected:{},

			eidval:'',
			keyword:'',
			job_class:'',
			city_class:'',
			job_exp:'',
			job_edu:'',
			adtime:'',
			welfare:[],
            sort_t:'',
            order:'',

        }
    },
	mounted() {
		this.eidval = this.eid;
		this.getStaticData();
		this.getList();
    },
    methods: {
		search:function(){
			this.eidval='';
			this.currentPage = 1;
			this.getList();
		},
		getStaticData(){
		    var that = this
			let params = {
				eid:that.eidval,
			}
		    httpPost('m=crm&c=crm_chat&a=getJoblistStaticData',params,{hideloading:true}).then((result) => {
				var res = result.data;
				if (res.error == 0) {
					if(that.eidval!=''){
						that.jobSelected = res.data.jobnameArr;
						that.citySelected = res.data.citynameArr;
					}
					that.job_exp_arr = res.data.job_exp_arr;
					that.job_edu_arr = res.data.job_edu_arr;
					that.job_welfare_arr = res.data.job_welfare_arr;
				}
		    });
		},
        async getList() {
            let that = this;
            let params = {
				eid:that.eidval,
				keyword:that.keyword,
				job_class:that.job_class,
				city_class:that.city_class,
				job_exp:that.job_exp,
				job_edu:that.job_edu,
				adtime:that.adtime,
				welfare:that.welfare.length>0?that.welfare.join(','):'',
				page: that.currentPage,
                limit: that.limit,
            }

            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=crm&c=crm_chat&a=joblist', params).then((result) => {
                this.list_loading = false;
                var res = result.data;

                if (res.error == 0) {

                    that.tableData = res.data.list
                    that.total = parseInt(res.data.total)
                    that.page_sizes = res.data.page_sizes;
					that.limit = res.data.page_size;
                    if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        that.$refs.table.bodyWrapper.scrollTop = 0;
                    }
                    if (that.tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
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

		confirmJob(data) {

		    this.job_class = data.jobId.join(',');
		},
		confirmCity(data){
			this.city_class = data.cityId.join(',');
		},
		sendJob:function(row){
			this.$emit("send-job",{job:row});
		}

    },
};
</script>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 57px;
    margin: 3px 0;
}

.moduleInptList .el-select .el-input{
	width: initial;
}
.moduleInptList .mplatejdshds{
	width: 100%;
}
.moduleInptList .mplatejdshds .el-select{
	width: 100%;
}
</style>