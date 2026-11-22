<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" @keyup.enter.native="search" placeholder="请输入内容" v-model="keyword" class="input-with-select" clearable>
                        <el-select v-model="keytype" slot="prepend" placeholder="请选择">
                            <el-option label="用户名" value="1"></el-option>
                            <el-option label="姓名" value="2"></el-option>
                            <el-option label="手机号" value="3"></el-option>
							<el-option label="用户ID" value="4"></el-option>
							<el-option label="IP" value="5"></el-option>
                        </el-select>
                    </el-input>
                </div>
				<div class="moduleInptList">
				    <el-select size="small" v-model="source" placeholder="数据来源" clearable @change="search">
						<el-option v-for="(item,index) in source_arr" :key="index" :label="item" :value="index"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
				    <el-select size="small" v-model="lotime" placeholder="最近登陆" clearable @change="search">
						<el-option label="今天" value="1"></el-option>
						<el-option label="最近三天" value="3"></el-option>
						<el-option label="最近七天" value="7"></el-option>
						<el-option label="最近半月" value="15"></el-option>
						<el-option label="最近一个月" value="30"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
				    <el-select size="small" v-model="adtime" placeholder="最近注册" clearable @change="search">
						<el-option label="今天" value="1"></el-option>
						<el-option label="最近三天" value="3"></el-option>
						<el-option label="最近七天" value="7"></el-option>
						<el-option label="最近半月" value="15"></el-option>
						<el-option label="最近一个月" value="30"></el-option>
					</el-select>
				</div>
				<div class="moduleInptList">
				    <el-select size="small" v-model="status" placeholder="审核状态" clearable @change="search">
						<el-option label="已审核" value="1"></el-option>
						<el-option label="已锁定" value="2"></el-option>
						<el-option label="未通过" value="3"></el-option>
						<el-option label="未审核" value="4"></el-option>
					</el-select>
				</div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table ref="multipleTable" :data="tableData" v-loading="list_loading" @selection-change="selectionChange"
				@mousedown.native="mouseDownHandler"
                @mouseup.native="mouseUpHandler"
                @mousemove.native="mouseMoveHandler"


                :default-sort = "{prop: 'uid', order: 'descending'}" :empty-text="emptytext"
                @sort-change="sortChange" stripe style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }">
                <el-table-column prop="uid" label="用户ID" width="100" sortable="custom">
                </el-table-column>
                <el-table-column label="用户名/姓名" width="120">
                    <template slot-scope="props">
                       <div>{{ props.row.username_n }}</div>
                            <span style="color: #1890ff;">{{ props.row.username }}</span>

                    </template>
                </el-table-column>
                <el-table-column label="手机号/归属地" width="150">
                    <template slot-scope="props">
                        <div class=" " v-if="props.row.telphone">
                            <div>{{ props.row.telphone }}</div>
                            <div  class="gsd"  v-if="props.row.moblie_address">{{ props.row.moblie_address }}</div>
                            <el-button v-else type="text" @click="getmobileaddress(props.row)" style="padding: 0;">查询归属地</el-button>
                        </div>
						<div v-else>--</div>
                    </template>
                </el-table-column>
                <el-table-column label="登录/注册时间" width="150">
                    <template slot-scope="props">
                            <div>{{ props.row.login_date?props.row.login_date_n:'从未登录' }}</div>
                            <div>{{ props.row.reg_date_n }}</div>

                    </template>
                </el-table-column>
                <el-table-column label="来源/ip/归属" width="180">
                    <template slot-scope="props">
                        <div class=" ">
                            <span> {{ props.row.source_n }}</span>
							<div v-if="props.row.login_ip">
								<div class="gsd" >{{props.row.login_ip}}</div>
								<div class="gsd"   v-if="props.row.login_address">{{props.row.login_address}}</div>
								<el-button v-else type="text" @click="getipaddress(props.row)" style="padding: 0;">查询归属地</el-button>
							</div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column prop="label" label="备注" min-width="250">
                </el-table-column>
				<el-table-column prop="shzhuat" label="审核状态" width="140">
					<template slot-scope="props">
					    <div class="moduleProps">
					        <span v-if="props.row.r_status=='1'">已审核</span>
							<span v-else-if="props.row.r_status=='2'">已锁定</span>
							<span v-else-if="props.row.r_status=='3'">未通过</span>
							<span v-else>未审核</span>
					    </div>
					</template>
				</el-table-column>

                <el-table-column fixed="right" label="操作" width="186">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
							<el-tooltip class="item" effect="dark" content="点击图标，可使用虚拟号联系客户" placement="right">
								<el-button type="primary" size="mini" @click="contact_customer(scope.row)">拨号</el-button>
							</el-tooltip>
                            <el-button size="mini" @click="addresume(scope.row.uid)">简历</el-button>
                            <el-button size="mini"  @click="bzbox(scope.row)">{{scope.row.label?'查备':'备注'}}</el-button>
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
		<el-dialog title="备注" width="430px" :visible.sync="bzshow" :modal-append-to-body="false">
			<div class="dialog_item">
				<span class="mingce">用户名：</span>
				<div class="drawerModInpt">
					{{bz_username}}
				</div>
			</div>
			<div class="dialog_item">
				<span class="mingce">备注：</span>
				<div class="drawerModInpt">
					<el-input type="textarea" v-model="bz_content" style="flex: 1;"></el-input>
				</div>
			</div>
			<span slot="footer" class="dialog-footer">
		        <el-button @click="bzshow = false">取 消</el-button>
		        <el-button type="primary" @click="setBz">确 定</el-button>
		    </span>
		</el-dialog>
        <div class="modluDrawer">
            <el-drawer title="添加简历"  :modal-append-to-body="false" append-to-body :visible.sync="addresumeVisible" :destroy-on-close="true" size="50%">
                <addresume :uid="aruid" @close-update="closeAddresume" v-if="addresumeVisible"></addresume>
            </el-drawer>
        </div>
    </div>
</template>

<script>
module.exports = {
    data: function () {
        return {
			mouseFlag: false,
            mouseOffset: 0,
            emptytext: '暂无数据',
            source_arr:[],
            tableData: [],
            total: 0,
            limit: 0,
            currentPage: 1,
            prevPage: 0,
            page_sizes: [],

            list_loading: false,

            allchecked: false,
            choosedata: [],

            keyword:'',
            keytype:'1',
            adtime:'',
            lotime:'',
            source:'',
			status:'',
            sort_t:'',
            order:'',

            bzshow:false,
            bzid:'',
			bz_username:'',
            bz_content:'',

            addresumeVisible:false,
            aruid:'',

        }
    },
	components: {
	    'addresume': httpVueLoader('../../component/addresume.vue'),
	},
    mounted() {
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
            let divData = this.$refs.multipleTable.bodyWrapper;
            if (this.mouseFlag) {
                // 设置水平方向的元素的位置
                divData.scrollLeft -= (- this.mouseOffset + (this.mouseOffset = e.clientX));
            }
        },



		getStaticData(){
		    var that = this
			let params = {}
		    httpPost('m=crm&c=crm_chat&a=getNrStaticData',params,{hideloading:true}).then((result) => {
				var res = result.data;
				if (res.error == 0) {
					that.source_arr = res.data.source_arr
				}
		    });
		},
        async getList() {
            let that = this;
            let params = {
                page: that.currentPage,
                limit: that.limit,
                t:that.sort_t,
                order:that.order,
                keyword:that.keyword,
                keytype:that.keytype,
                lotime:that.lotime,
				adtime:that.adtime,
				status:that.status,
        		source:that.source,
        	}

            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=crm&c=crm_chat&a=noresume', params).then((result) => {
                this.list_loading = false;
                var res = result.data;

                if (res.error == 0) {
                    that.tableData = res.data.list
        			that.total = parseInt(res.data.total)
                    that.page_sizes = res.data.page_sizes;
					that.limit = res.data.page_size;
                    if(that.prevPage != that.currentPage){
                        that.prevPage = that.currentPage;
                        that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                        scrollToTop()
                    }
                    if (that.tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        },

        search: function () {
            this.currentPage = 1;
            this.getList();
        },
        sortChange:function(e){
            this.sort_t = e.prop;

            this.order = e.order=='ascending'?'asc':'desc';
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

        contact_customer:function(row){

        	var phone = row.telphone;
        	var uid = row.uid;

        	var params = {
        		mobile: phone,
        		uid: uid,
        	};

        	message.confirm('确定联系客户吗？', function () {
        		httpPost('m=crm&c=crm_chat&a=contactCustomer', params).then(function (result) {

        		    var res = result.data;
        		    if (res.error == '200') {
        				message.success(res.msg,function(){
							that.addresume(uid);
						});
        			} else {
        		        message.error(res.msg); return;
        		    }
        		}).catch(function (e) {
        		    console.log(e)
        		})
        	});
        },
        bzbox:function(row){
        	this.bzid = row.uid;
        	this.bz_username = row.username;
        	var bz_content = '';
        	if(row.label_n){
        		bz_content = row.label_n;
        	}
        	this.bz_content = bz_content;

        	this.bzshow = true;
        },
        setBz:function(){

        	var that = this;
        	if(that.bz_content==''){
        		message.warning('请填写备注内容'); return;
        	}
        	var params = {
        		id:that.bzid,
        		content:that.bz_content
        	};
        	httpPost('m=crm&c=crm_chat&a=userlabel', params).then((result)=>{
        	    this.bzshow = false;
        	    var res = result.data;
        	    if (res.error == 0) {
        	        message.success(res.msg, function () {that.getList()}); return;
        	    } else {
        	        message.error(res.msg); return;
        	    }
        	}).catch(function (e) {
        	    console.log(e)
        	})
        },
        getipaddress:function(row){
			var that = this;
			var tableData = deepClone(that.tableData);
			var params = {
				uid:row.uid,
				ip:row.login_ip
			};
			httpPost('m=index&c=getIpAddress', params).then(function (result) {
			    this.bzshow = false;
			    var res = result.data;
			    if (res.error == 0) {
			        message.success('查询成功', function () {
						for(let i in tableData){
							if(tableData[i].uid == row.uid){
								tableData[i].login_address = res.msg;
								break;
							}
						}
						that.tableData = tableData;
					});
					return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
		getmobileaddress:function(row){
			var that = this;
			var tableData = deepClone(that.tableData);
			var params = {
				uid:row.uid,
				moblie:row.telphone
			};
			httpPost('m=index&c=getMobileAddress', params).then(function (result) {
			    this.bzshow = false;
			    var res = result.data;
			    if (res.error == 0) {
			        message.success('查询成功', function () {
						for(let i in tableData){
							if(tableData[i].uid == row.uid){
								tableData[i].moblie_address = res.msg;
								break;
							}
						}
						that.tableData = tableData;
					});

					return;
			    } else {
			        message.error(res.msg); return;
			    }
			}).catch(function (e) {
			    console.log(e)
			})
		},
        addresume:function(uid){
        	this.aruid = uid;
        	this.addresumeVisible = true;
        },
        closeAddresume: function () {
        	this.aruid = '';
        	this.addresumeVisible = false;
        	this.getList();
        },
    },
};
</script>
<style scoped>
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: initial;
    margin: 3px 0;
}
.dialog_item{
    overflow: hidden;
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    padding: 10px 0;
}
.dialog_item .mingce{
    overflow: hidden;
    display: block;
    text-align: right;
    width: 70px;
}
.dialog_item .drawerModInpt{
    overflow: hidden;
    display: block;
    width: calc(100% - 70px);
}
</style>