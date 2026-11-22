<template>
    <div class="moduleElHight">
		<div class="tableDome_tip">
			<el-alert title="微信红包记录功能主要记录网站发给用户的红包记录，记录上有用户名、金额、发送时间、状态等参数。为防止刷红包，未发放状态的需要后台对应功能审核后才能发放" type="success" :closable="false">
			</el-alert>
		</div>
        <div class="moduleSeachs">
            <div class="moduleSeachleft">
				<div class="moduleInptList">
				    <el-input placeholder="请输入搜索内容" v-model="keyword" class="input-with-select" size="small" clearable>
				        <el-select v-model="type" slot="prepend" placeholder="搜索类型">
				            <el-option label="微信昵称" value="1"></el-option>
				            <el-option label="已绑定用户" value="2"></el-option>
				        </el-select>
				    </el-input>
				</div>
                
                <div class="tableSeachInpt" style="margin-bottom: 0px;;">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
             
        </div>

        <div class="moduleElTable">
            <el-table ref="table" :data="tableData" v-loading="list_loading" border style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :empty-text="emptytext">
                <el-table-column prop="id" label="编号" width="80"> </el-table-column>
                <el-table-column prop="re_nick" label="微信昵称"  > </el-table-column>
                <el-table-column prop="total_amount" label="红包金额" > </el-table-column>
				<el-table-column label="红包类型" >
                    <template slot-scope="scope">
                        <span v-if="scope.row.type==1">关注微信公众号</span>
                        <span v-else-if="scope.row.type==2">绑定微信账户</span>
                        <span v-else-if="scope.row.type==3">创建首份简历</span>
                        <span v-else-if="scope.row.type==4">企业完善资料</span>
                    </template>     
                </el-table-column>
				<el-table-column prop="username" label="已绑定用户" > </el-table-column>
                <el-table-column prop="yhmlx" label="用户类型" >
                    <template slot-scope="scope">
                        <span v-if="scope.row.usertype==1">个人</span>
                        <span v-else-if="scope.row.usertype==2">企业</span>
                    </template>  
                </el-table-column>
                <el-table-column prop="msg" label="红包提示" > </el-table-column>
                <el-table-column prop="time_n" label="发送时间" width="180"></el-table-column>
				<el-table-column label=" 发放状态" >
                    <template slot-scope="scope">
                        <span v-if="scope.row.is_send==1">
                            <span v-if="scope.row.status==1" class="admin_state1">成功</span>
                            <span v-else class="admin_state2">失败</span>
                        </span>
                        <span v-else>未发放</span>
                    </template> 
                </el-table-column>
                 
            </el-table>
        </div>


        <div class="modulePaging">
            
            <div class="modulePagNum"  >
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
    data: function () {
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
            state:'',
            time:'',
            daterange:[],
            sort_t:'',
            order:'',
        }
    },

    mounted() {
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
            httpPost('m=tool&c=weixinred&a=wxlog', params, {hideloading: true}).then((result) => {
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
        
        handleCurrentChange(val) {
            this.currentPage = val;
            this.getList()
        },
        handleSizeChange(val) {
            this.currentPage = 1
            this.limit = val
            this.getList()
        },
        
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

 .moduleElTable {
    padding: 0;
    margin: 0;
  height: calc(100% - 136px);
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
.el-dialog__body{padding:0px 20px;}
</style>