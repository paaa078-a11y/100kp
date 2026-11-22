<template>
	<div class="moduleElHight">

		<div class="moduleSeachs">
			<div class="moduleSeachleft">
                <div class="moduleInptList">
                    <el-input placeholder="请输入搜索内容" v-model="keyword" class="input-with-select" size="small"
                              size="small" clearable>
                        <el-select slot="prepend" v-model="type" size="small" placeholder="企业名称">
                            <el-option label="用户名" value="1"></el-option>
                            <el-option label="内容" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
				<div class="tableSeachInpt" style="margin-bottom: 0px;;">
					<el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
				</div>
			</div>

		</div>

		<div class="moduleElTable">
			<el-table ref="table" :data="tableData" v-loading="list_loading" @selection-change="selectionChange"
                :default-sort = "{prop: 'id', order: 'descending'}"
                @sort-change="sortChange" border style="width: 100%"
				:header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :empty-text="emptytext">
				<el-table-column type="selection" width="55">
				</el-table-column>
				<el-table-column prop="id" label="编号" width="80" sortable="custom" :sort-orders="['descending','ascending']">
				</el-table-column>

				<el-table-column prop="name" label="用户名/用户" width="300">
					<template slot-scope="scope">
                      <div class="chat_sftag"> 
                        <div>
                            <el-tag size="mini" type="success" v-if="scope.row.usertype==1">个人</el-tag>
                            <el-tag size="mini" v-if="scope.row.usertype==2">企业</el-tag>
                            <el-tag size="mini" v-if="scope.row.usertype==3">猎头</el-tag>
                           
                        </div> 
							<div class="chat_sftag_r">
						 <div>{{scope.row.username}}</div>
						  <div>{{scope.row.uname_n}} </div>
						  </div>
						   </div> 
					</template>

				</el-table-column>
				<el-table-column prop="neirong" label="内容">
                    <template slot-scope="scope">
                        <div v-if="scope.row.content.length<30">{{scope.row.content}}</div>
                        <div v-else>{{scope.row.content.slice(0,30)}}...<el-link type="primary" @click="detailmsg(scope.row.content)">更多</el-link></div> 
                    </template>
				</el-table-column>

				<el-table-column label="操作" width="130"  fixed="right" header-align="center">					<template slot-scope="scope">
						<div class="cz_button">
							<el-button size="small " type=" " @click="editinfo(scope.row)">修改</el-button>
                            <el-button type="danger" size="small "  @click="deleteinfo(scope.row.id)">删除</el-button>
						</div>

					</template>
				</el-table-column>
			</el-table>
		</div>


		<div class="modulePaging">
            <div class="modulecz">
                <el-checkbox v-model="allchecked" @change="allcheckChange">全选</el-checkbox>
                <el-button  size="mini" @click="deleteAll">批量删除</el-button>
            </div>
            <div class="modulePagNum"  >
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total"
                    layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>

		<div class="modluDrawer">
			<el-dialog title="常用语修改" :visible.sync="editshow" :with-header="true" :modal-append-to-body="false" :show-close="true" width="440px">
				<div> 
                    <el-input type="textarea" :rows="2" placeholder="刚看了您发布的职位，我很感兴趣，希望您可以看下我的简历" v-model="einfo.content"></el-input>
				</div>
				<span slot="footer" class="dialog-footer">
					<el-button @click="editshow = false">取 消</el-button>
					<el-button type="primary"  @click="saveinfo" :loading="post_loading">确 定</el-button>
				</span>
			</el-dialog>
		</div>
	</div>
</template>

<script>
	module.exports = {
		data: function() {
			return {
                emptytext: '暂无数据',
				tableData: [],
                total: 0,
                limit: 0,
                currentPage: 1,
				prevPage:0,
                page_sizes: [],

                list_loading: false,
                
                allchecked: false,
                choosedata: [],

                keyword:'',
                type:'1',
                
                sort_t:'',
                order:'',

                editshow: false,
                einfo:{},
                post_loading:false,
			}
		},

		created: function () {
            this.getList();
        },
		methods: {
            async getList() {
                let that = this;
                let params = {
                    page: that.currentPage,
                    limit: that.limit,
                    t:that.sort_t,
                    order:that.order,
                    keyword:that.keyword,
                    type:that.type,
                }
                
                this.list_loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=tool&c=chatcyy&a=index', params, {hideloading: true}).then((result) => {
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
            deleteinfo: function (id) {
                var _this = this;
                
                var params = {
                    del: id
                };
                delConfirm(_this, params, this.deletePost)
            },
            deleteAll: function () {
                var _this = this;
                var idarr = [];
                if (this.choosedata.length > 0) {
                    for (let i in this.choosedata) {
                        idarr.push(this.choosedata[i].id);
                    }
                } else {
                    message.error('请选择要删除的数据'); return;
                }
                var params = {
                    del: idarr
                };

                delConfirm(_this, params, this.deletePost)
            },
            async deletePost(params) {

                let that = this;

                httpPost('m=tool&c=chatcyy&a=del', params).then(function (result) {

                    var res = result.data;
                    if (res.error == 0) {
                        message.success(res.msg, function () { location.reload(); }); return;
                    } else {
                        message.error(res.msg); return;
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            detailmsg(msg) {
                this.$alert(msg, {
                    confirmButtonText: '确定',
                });
            },
            editinfo:function(row){
                
                this.einfo = deepClone(row);
                this.editshow = true;
            },
            saveinfo:function(){
                
                var that = this,
                    param={};
                if(this.einfo.content!=''){
                    param.content =this.einfo.content;
                }else{
                    message.warning('请输入常用语');return;
                }
                
                param.id =this.einfo.id;

                that.post_loading = true;
        
                httpPost('m=tool&c=chatcyy&a=add',param).then((result)=>{
                    var res = result.data;
                    if(res.error==0){
                        message.success(res.msg,()=>{
                            that.editshow = false;
                            that.getList();
                        });
                    }else{
                        message.error(res.msg);
                    }
                }).catch(function(e){
                    console.log(e)
                }).finally(function () {
                    setTimeout(function () {
                        that.post_loading = false;
                    }, 2000);
                });
            }
		},
	};
</script>
<style scoped>
	.moduleSeachmore {
		padding: 0px;
	}

	.moduleSeachs {
		padding: 0px 0px 12px 0px;
		width: 100%;
	}

	 

	.tableSeachInptsmalltwo {
		margin-bottom: 0px;
		margin-right: 12px;
	}

	.tableSeachInptsmalltwo .el-input__inner {
		height: 32px;
		line-height: 32px;
		width: 260px;
		padding: 0px 5px;
		;
	}

	.el-dialog__body {
		padding: 0px 20px;
	}
</style>