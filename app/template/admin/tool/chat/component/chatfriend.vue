<template>
    <div class="moduleElHight">


        <div class="moduleElTable">
            <el-table  ref="table" :data="tableData" v-loading="list_loading" border style="width: 100%"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" :empty-text="emptytext">
                <el-table-column prop="uid" label="用户id" width="80"> </el-table-column>
				<el-table-column prop="countname" label="用户姓名"  > </el-table-column>
				<el-table-column prop="username" label="账户"  > </el-table-column>
				<el-table-column prop="fid" label="好友用户id"> </el-table-column>
				<el-table-column prop="rcountname" label="好友用户姓名"  > </el-table-column>
				<el-table-column prop="rusername" label="好友账户" > </el-table-column>
				<el-table-column prop="stime_n" label="开始时间"> </el-table-column> 
				<el-table-column prop="ntime_n" label="最新聊天时间"> </el-table-column> 
			</el-table>
        </div>
         <div class="modulePaging" style="padding-top: 5px;">
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
	props:{
	    from: {
		    type: String,
		    default: ''
		},
		param:{
			type: Object,
			default: function(){
				return {};
			}
		}
	},
	watch: {
	    param: {
			handler(obj) {
				if (!$.isEmptyObject(obj)) {
					for(let i in obj){
					    this[i] = obj[i];
					}
				}
			},
			immediate: true,
			deep: true,
		},
	},
    data: function () {
        return {
            emptytext: '暂无数据',
            tableData: [],
            total: 0,
            limit: 0,
            currentPage: 1,
			prevPage:0,
            page_sizes: [],
			uid:'',
			usertype:'',
            list_loading: false,
    
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
                uid:that.uid,
                usertype:that.usertype,
            }
            
            this.list_loading = true;
            that.emptytext = "数据加载中";
			var url = '';
			
			if(this.from=='persona'){
				url = 'm=crm&c=crm_my_customer&a=chatfriends';
			}
			
            httpPost(url, params).then((result) => {
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
.el-dialog__body{padding:0px 20px;}
</style>