<template>
    <div class="moduleElHight">
        <div class="moduleElSearchInf">
            <div class="moduleElTabInpt">
				<div class="moduleInptList">
				    <el-input size="small" @keyup.enter.native="search" placeholder="输入你要搜索的关键字" v-model="searchForm.keyword" class="input-with-select" clearable>
				        <el-select v-model="searchForm.type" slot="prepend" placeholder="">
				            <el-option label="公司名称" value="1"></el-option>
				            <el-option label="职位名称" value="2"></el-option>
				        </el-select>
				    </el-input>
				</div>
                <div v-for="(searchItem, searchIndex) in searchList" :key="searchIndex" class="tableSeachInptsmall moduleInptList">
                    <el-select v-model="searchForm[searchItem.param]" size="small" slot="prepend" :clearable="true" :placeholder="searchItem.name" @change="search">
                        <el-option v-for="(searchLabel, searchValue) in searchItem.value" :key="searchValue" :label="searchLabel" :value="searchValue" ></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList ">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="admin_datatip">
            <i class="el-icon-document"></i>
            数据统计：共 {{statis.jobAllNum}} 条
            <span class="admin_datatip_n" v-if="statis.jobStatusNum1>0">未审核：{{statis.jobStatusNum1}} 条 </span>
            <span class="admin_datatip_n" v-else>未审核：0 条 </span>
            <span class="admin_datatip_n" v-if="statis.jobStatusNum2>0">未通过：{{statis.jobStatusNum2}} 条</span>
            <span class="admin_datatip_n" v-else>未通过：0 条 </span>
            <span class="admin_datatip_n" v-if="statis.jobStatusNum3>0">已锁定：{{statis.jobStatusNum3}} 条</span>
            <span class="admin_datatip_n" v-else>已锁定：0 条 </span>
            <span class="admin_datatip_n">搜索结果：{{total}} 条</span>
        </div>
        <div class="moduleElTable" style="border: 1px solid #ebeef5; width: calc(100% - 2px); height: calc(100% - 135px);">
            <el-table :data="list" stripe style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" @sort-change="shortChange" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="编号" width="90" sortable="custom">
                </el-table-column>
                <el-table-column label="职位/公司" min-width="200">
                    <template slot-scope="scope">
						<el-link :href="scope.row.joburl" target="_blank" type="primary">{{ scope.row.name }}</el-link><br/>
						<el-link :href="scope.row.comurl" target="_blank" type="primary">{{ scope.row.com_name }}</el-link>
                    </template>
                </el-table-column>
                <el-table-column label="简历" width="140">
                    <template slot-scope="scope">
                        <div class="moduleProps">
                            <span>简历数：{{ scope.row.snum }}</span>
                            <span>未查看：{{ scope.row.browseNum }}</span>
                            <span>已面试：{{ scope.row.inviteNum }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="发布/更新时间" width="180">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <span>{{ props.row.sdate_n }}</span>
                            <span>{{ props.row.lastupdate_n }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="jobhits" label="浏览量" width="130">
                </el-table-column>
                <el-table-column prop="source" label="来源" width="120">
                    <template slot-scope="scope">
                        <span>{{source[scope.row.source]}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="shztai" label="审核状态" width="130">
                    <template slot-scope="scope">
                        <div v-if="scope.row.r_status==1">
                            <span v-if="scope.row.state==1">已审核</span>
                            <span v-else-if="scope.row.state==0">未审核</span>
                            <span v-else-if="scope.row.state==3">未通过</span>
                        </div>
                        <div v-else-if="scope.row.r_status==2">
                            <span v-if="scope.row.state==0">未审核</span>
                            <span v-else-if="scope.row.state==3">未通过</span>
                            <span v-else>已锁定</span>
                        </div>
                        <div v-else-if="scope.row.r_status==3">
                            <span v-if="scope.row.state==0">未审核</span>
                            <span v-else>未通过</span>
                        </div>
                        <div v-else>
                            <span v-if="scope.row.state==0">未审核</span>
                            <span v-else>未通过</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="招聘状态" width="140">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status==1" style="color:#b21010" @click="checkstate(scope.row.id,2)" >已下架</span>
                        <span v-else @click="checkstate(scope.row.id,1)">发布中</span>
                    </template>
                </el-table-column>
                <el-table-column label="推广" width="150">
                    <template slot-scope="scope">
                        <div class="moduleWitch">
                            <div class="moduleWitLis">
                                <span>置顶：</span>
                                <el-switch v-model="scope.row.istop" active-color="#1890FF" inactive-color="#B8BDC9" @change="isTop(scope.row,scope.$index)">
                                </el-switch>
                            </div>
                            <div class="moduleWitLis">
                                <span>推广：</span>
                                <el-switch v-model="scope.row.isrec" active-color="#1890FF" inactive-color="#B8BDC9" @change="isRec(scope.row,scope.$index)">
                                </el-switch>
                            </div>
                            <div class="moduleWitLis">
                                <span>紧急：</span>
                                <el-switch v-model="scope.row.isurgent" active-color="#1890FF" inactive-color="#B8BDC9" @change="isUrgent(scope.row,scope.$index)">
                                </el-switch>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="140">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button size="mini" @click="status(scope.row.id)">审核</el-button>
                            <el-button size="mini" @click="resumematch(scope.row)">匹配</el-button>
                            <el-button size="mini" @click="edit(scope.row.id)">修改</el-button>
                            <el-button size="mini" @click="del(scope.$index)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate" @change="checkAll">全选</el-checkbox>
                <el-button @click="batch('del')" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                   :current-page="page" :page-sizes="pageSizes" :page-size="limit"
                   layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <!-- 弹窗 -->
		<div class="modluDrawer">
		    <el-dialog :title="boxtitle" :visible.sync="drawerBox" :with-header="true" :modal-append-to-body="false"
		        :show-close="true" width="450px">
		        <div class="toolClasDia fenpeizhand">
		            <div class="toolClasList">
		                <div class="toolClasTite">
		                    <span>{{boxtitle}}：</span>
		                </div>
		                <div class="toolClasCont">
		                    <el-input v-model="days" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
		                        <span slot="suffix" class="slotspan" style="display: block; height: 35px; line-height: 35px;">天</span>
		                    </el-input>
		                </div>
		            </div>
					<div class="toolClasList" v-if="istop>0">
					    当前结束日期：{{xtime}}
					</div>
					<div class="toolClasList">
					    如需取消{{boxtitle}}请单击<el-checkbox v-model="iscancel"></el-checkbox> 点击确认即可
					</div>
		        </div>
		        <span slot="footer" class="dialog-footer">
		            <el-button @click="drawerBox = false">取 消</el-button>
		            <el-button type="primary" @click="subBox" :disabled="submitLoading">确 定</el-button>
		        </span>
		    </el-dialog>
		</div>
        <el-drawer title="职位审核" :visible.sync="statusBox" :before-close="handleClose"
            :modal-append-to-body="false" size="80%">
            <job_review :id="statusId" :comclass_name="comclass_name" :r_status="r_status" :job_audit="comdata.job_audit" :info="info" :snum="snum" :is_graduate="1"  @confirm="statusBox=false;getList()" ></job_review>
        </el-drawer>
		<!--匹配简历弹窗-->
		<div class="modluDrawer">
			<el-drawer title="匹配简历" :visible.sync="drawermatchresume" :modal-append-to-body="false" append-to-body
					   :show-close="true" :with-header="true" size="95%">
				<matchresume ref="matchresume" :job="curr_job" :jobtypes="job_types" :citytypes="city_types"></matchresume>
			</el-drawer>
		</div>
        <el-drawer title="修改职位" :visible.sync="drawerEditJob" append-to-body size="880px">
            <addjob ref="jobedit" :jid="jobid" :jtypes="job_types" :ctypes="city_types"></addjob>
        </el-drawer>
	</div>
</template>

<script>
	let jobeditor = null, jobtoolbar = null;
	const {createEditor, createToolbar} = window.wangEditor;
module.exports = {
	props: {
		state: {type: String, default: ''}
	},
    data: function () {
        return {
            loading: false,
            dataText: '数据加载中',
            searchList: [],
            searchForm: {
                type: '1',
                keyword: '',
				state: this.state
            },
            page: 1,
            limit: 0,
            list: [],
            total: 0,
            pageSizes: [],

            checkedAll: false, // 全选
            checkedAllIndeterminate: false,
            multipleSelection: [], // 多选值存储
            idArr: [],

            source:[],
            statis:[],

            statusBox: false,
            info:[],
            snum: 0,
            comdata:[],
            comclass_name:[],
            tpl:'',
            job_status:true,
			r_status:'',

			drawerBox: false,
			boxtitle:'',
			iscancel: false,
			days: '',
			xtime:'',
			xurl:'',
			pid:'',
			istop: 0,
			submitLoading:false,

			drawerEditJob: false,
			curr_job:null,
			jobCompany: null,
			jobAddressList: [],
			sel_jobtype: [],
			mychecked: false,
			job_types: [],
			city_types: [],
			cacheData: null,
			jionly: 0,
			jobcomdatacache: [],
			jobcomclassnamecache: [],

			drawermatchresume: false,
            jobid:'',

            prevPage: 0,
            statusId:0
        }
    },
    mounted() {
        var that = this
        setTimeout(function () {
            that.getTjNum();
            that.getCacheFun();
        }, 200)
    },
	components: {
		'matchresume': httpVueLoader('./match_resume.vue'),
        'addjob': httpVueLoader('./addjob.vue'),
        'job_review': httpVueLoader('../../company/component/job_review.vue'),
	},
    methods: {
        getCacheFun:function(){
            let that = this;
            httpPost('m=user&c=school_graduate&a=getCacheData', {},{hideloading: true}).then(function (response) {
                let res = response.data;
                if (res.error == 0) {
                    that.cacheData = res.data.cache
                    that.job_types = res.data.job_types
                    that.city_types = res.data.city_types
                    that.jionly = res.data.jionly
                    that.jobcomdatacache = res.data.comdata
                    that.jobcomclassnamecache = res.data.comclass_name;
                    that.searchList = res.data.search_list;
                    that.source = res.data.source;
                }
            })
        },
        getTjNum: function () {
            var that = this;
            httpPost('m=user&c=school_graduate&a=jobNum', {}, { hideloading: true }).then(function (result) {
                var res = result.data;
                if (res.error == 0) {
                    that.statis = res.data.statis;
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
		resumematch: function(row){
			var that = this
			that.curr_job = row
			that.drawermatchresume = true
			that.$nextTick(function(){
				that.$refs.matchresume.getList()
			})
		},
        edit: function (id) {
            var that = this;
            that.jobid = id;
            that.drawerEditJob = true;
        },
		subBox(){
			let that = this;
			if (that.days == '' && that.iscancel == false){
				message.error('天数不能为空');
				return false;
			}
			let params = {
				pid: that.pid,
				addday: that.days,
				s: that.iscancel ==false ? 0 : 1
			}
			
			let msg = '';
			if (that.istop >0){
				if (that.iscancel){
					msg = '确定取消该职位置顶';
				}else{
					msg = '是否确定为该职位增加'+ that.days +'天置顶服务';
				}

				
                that.submitLoading = true;
				delConfirm(this, params, function (params) {
					httpPost(that.xurl, params).then(function(res) {
					    if (res.data.error > 0) {
					        message.error(res.data.msg);
					    } else {
					        message.success(res.data.msg, function () {
                                that.drawerBox = false;
					            that.getList();
					        });
					    }
					}).finally(function () {
						setTimeout(function() {
                            that.submitLoading = false;
                        }, 2000);
					});
				}, msg)
			}else{
                that.submitLoading = true;
				httpPost(that.xurl, params).then(function(res) {
				    if (res.data.error > 0) {
				        message.error(res.data.msg);
				    } else {
				        message.success(res.data.msg, function () {
                            that.drawerBox = false;
				            that.getList();
				        });
				    }
				}).finally(function () {
					setTimeout(function() {
                        that.submitLoading = false;
                    }, 2000);
				});
			}

		},
		checkstate(id,type){
			let that = this;

			httpPost('m=user&c=school_graduate&a=checkstate', {id:id,state:type}).then(function(response) {
				let res = response.data;
			    that.getList();
			})
		},
		isTop(row,index){
			let that = this;
			that.boxtitle = '职位置顶';
			that.xurl = 'm=user&c=school_graduate&a=xuanshang';
			that.xtime = row.xstime;
			that.pid = row.id;
			if(that.list[index].istop){
				that.list[index].istop = false;
			}else{
				that.list[index].istop = true;
			}
			that.iscancel=false;
			that.istop = 1;
			that.days = '';
			that.drawerBox = true;

		},
		isRec(row,index){
			let that = this;
			that.boxtitle = '职位推广';
			that.xurl = 'm=user&c=school_graduate&a=recommend';
			that.xtime = row.recdate;
			that.pid = row.id;
			if(that.list[index].isrec){
				that.list[index].isrec = false;
			}else{
				that.list[index].isrec = true;
			}
			that.iscancel=false;
			that.istop = 0;
			that.days = '';
			that.drawerBox = true;
		},
		isUrgent(row,index){
			let that = this;
			that.boxtitle = '紧急招聘';
			that.xurl = 'm=user&c=school_graduate&a=urgent';
			that.xtime = row.eurgent;
			that.pid = row.id;
			if(that.list[index].isurgent){
				that.list[index].isurgent = false;
			}else{
				that.list[index].isurgent = true;
			}
			that.iscancel=false;
			that.istop = 0;
			that.days = '';
			that.drawerBox = true;
		},
        tplChange(e){
            this.info.statusbody = this.comclass_name[e];
        },
        status(id){
            let that = this;
            this.statusId= id;
            that.statusBox = true;
            // httpPost('m=user&c=school_graduate&a=jobAudit', {id:id},{hideloading: true}).then(function(response) {
            //     let res = response.data;
            //     that.info = res.data.info;
            //     that.snum = res.data.snum;
            //     that.comdata = res.data.comdata;
            //     that.comclass_name = res.data.comclass_name;
			// 	that.r_status = res.data.info.r_status;
            // })
        },
        handleClose(){
            this.statusBox = false;
            this.getList();
        },
		shortChange(e) {
		    let orderMap = {ascending: 'asc', descending: 'desc'}
		    this.searchForm.t = e.order ? e.prop : null;
		    this.searchForm.order = orderMap[e.order];
		    this.search();
		},
        handleSizeChange(val) {
            this.limit = val;
            this.getList();
        },
        handleCurrentChange(val) {
            this.page = val;
            this.getList();
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
                };
                that.loading = true;

            httpPost('m=user&c=school_graduate', {...params, ...searchForm}, {hideloading: true}).then(function (response) {
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
            this.multipleSelection.forEach(function(item) {
                idArr.push(item.id);
            })
            this.idArr = idArr;

            if (type == 'del') {
                this.del();
            }
        },
        checkAll(val) {
            val ? this.checkedAllIndeterminate = false : '';
            this.$refs.multipleTable.toggleAllSelection();
        },
        del(idx) {
            let that = this,
                params = {},
                msg = '';

            if (typeof idx == 'undefined') { // 批量删除
                params.del = this.idArr;
                msg = '你确定要删除选中项吗？';
            } else {// 单个删除
                params.del = that.list[idx].id;
                msg = '你确定要删除当前项吗？';
            }

            delConfirm(this, params, function (params) {
                httpPost('m=user&c=school_graduate&a=del', params).then(function(res) {
                    if (res.data.error > 0) {
                        message.error(res.data.msg);
                    } else {
                        message.success(res.data.msg, function () {
                            that.$refs.multipleTable.clearSelection();
                            that.getList();
                        });
                    }
                })
            }, msg)
        },
    },
};
</script>
<style>
	.button-new-tag {
		margin-left: 10px;
		height: 32px;
		line-height: 30px;
		padding-top: 0;
		padding-bottom: 0;
	}

	.input-new-tag {
		width: 90px;
		margin-left: 10px;
		vertical-align: bottom;
	}

	.job_set_list {
		margin-bottom: 10px;
		margin-top: 10px;
		font-size: 14px;
		color: #606266;
	}

	.job_set_pd {
		padding-left: 20px;
	}

	.TableInptline {
		line-height: 35px;
		padding: 0px 10px;
	}

	.jobchecked {
		padding: 8px 0 0 10px
	}
</style>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 47%;
    margin: 3px 0;
}

</style>