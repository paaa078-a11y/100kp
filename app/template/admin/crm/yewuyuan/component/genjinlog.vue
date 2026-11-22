<template>
    <div class="moduleElHight">
        <div class="moduleElTabSeach">
            <div class="moduleElTabInpt">
                <div class="moduleInptList">
                    <el-input size="small" @keyup.enter.native="search" placeholder="请输入内容" v-model="keyword" class="input-with-select">
                        <el-select v-model="keytype" slot="prepend" placeholder="请选择">
                            <el-option label="个人姓名" value="1"></el-option>
                            <el-option label="备注内容" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
				<div class="moduleInptList">

				   <el-select size="small" v-model="time" clearable placeholder="备注时间" @change="search">
				       <el-option label="今天" value="1"></el-option>
				       <el-option label="最近三天" value="3"></el-option>
				       <el-option label="最近七天" value="7"></el-option>
				       <el-option label="最近半月" value="15"></el-option>
				       <el-option label="最近一月" value="30"></el-option>
				   </el-select>
				</div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable" style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table ref="table" :data="tableData" v-loading="list_loading" @selection-change="selectionChange"
                @mousedown.native="mouseDownHandler"
                @mouseup.native="mouseUpHandler"
                @mousemove.native="mouseMoveHandler"
                :default-sort = "{prop: 'id', order: 'descending'}"
                @sort-change="sortChange" stripe style="width: 100%" :empty-text="emptytext"
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }">
                <el-table-column type="selection" width="55">
                </el-table-column>
                <el-table-column prop="id" label="编号" width="120" sortable="custom">
                </el-table-column>
                <el-table-column label="个人姓名" width="180">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <el-link type="primary" :underline="false" @click="userPreview(props.row.uid)">{{ props.row.r_name }}</el-link>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="业务员" width="160">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <span style="color: #2778f8;">{{ props.row.c_name }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="content" label="备注内容" min-width="240">
                </el-table-column>
                <el-table-column prop="time_n" label="备注时间" width="170">
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="90">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz">
                            <el-button  type="danger" size="mini" @click="deleteinfo(scope.row.id)">删除</el-button>
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
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    :current-page="currentPage" :page-size="limit" :page-sizes="page_sizes" :total="total"
                    layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
        <div class="modluDrawer">
            <el-drawer title="简历预览" :append-to-body="true" :visible.sync="resumePreviewVisible" :destroy-on-close="true" size="530px">
                <resume_preview :uid="ruid" v-if="resumePreviewVisible"></resume_preview>
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
            tableData: [],
            total: 0,
            limit: 0,
            currentPage: 1,
            page_sizes: [],

            list_loading: false,

            allchecked: false,
            choosedata: [],

            keyword:'',
            keytype:'1',
            time:'',
            daterange:[],
            sort_t:'',
            order:'',

			resumePreviewVisible:false,
			ruid:'',
            prevPage: 0
        }
    },
	components: {
	    'resume_preview': httpVueLoader('../../../component/resume_preview.vue'),
	},
    mounted() {
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
            let divData = this.$refs.table.bodyWrapper;
            if (this.mouseFlag) {
                // 设置水平方向的元素的位置
                divData.scrollLeft -= (- this.mouseOffset + (this.mouseOffset = e.clientX));
            }
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
                time:that.time,
                prevPage: 0
            }
            this.list_loading = true;
            that.emptytext = "数据加载中";
            httpPost('m=crm&c=crm_chat&a=follow', params).then((result) => {
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
                message.error('请选择要删除的数据！'); return;
            }
            var params = {
                del: idarr
            };

            delConfirm(_this, params, this.deletePost)
        },
        async deletePost(params) {

            let that = this;

            httpPost('m=crm&c=crm_chat&a=delFollow', params).then(function (result) {

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
		userPreview: function(uid) {
			this.ruid = uid;
			this.resumePreviewVisible = true;
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
</style>