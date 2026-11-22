<template>
    <div class="moduleElHight" style="padding: 0 20px;">

        <div class="moduleElTable moduleElTablTwo" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table ref="table" :data="tableData" v-loading="list_loading" :empty-text="emptytext"
                stripe :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%">
                <el-table-column prop="uid" label="求职者UID"></el-table-column>
                <el-table-column prop="name" label="姓名"></el-table-column>
				<el-table-column prop="comname" label="企业名称"></el-table-column>
				<el-table-column prop="time" label="时间"></el-table-column>
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
		uid: {
			type: [Number,String],
			default: ''
		},
	},
    data: function () {
        return {
            emptytext: '暂无数据',
            tableData: [],
            total: 0,
            limit: 0,
            prevPage: 0,
            currentPage: 1,
            page_sizes: [],

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
				uid:that.uid,
				page: that.currentPage,
                limit: that.limit,
            }

            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=crm&c=crm_chat&a=chatlogList', params).then((result) => {
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
.moduleElTaCaoz {
    justify-content: space-between;
}

.moduleElTaCaoz .el-button {
    width: 57px;
    margin: 3px 0;
}
</style>