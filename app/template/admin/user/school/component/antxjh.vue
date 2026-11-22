<template>
    <div class="moduleElHight" style="padding: 0 20px;">
        <div class="moduleElTable"
            style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="antlist" style="width: 100%" stripe
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }"
                      ref="multipleTable" @selection-change="handleSelectionChange" height="100%" v-loading="loading">
                <template slot="empty">
					<p>{{dataText}}</p>
				</template>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="编号" width="120"></el-table-column>
                <el-table-column prop="name_n" label="用户名称"></el-table-column>
                <el-table-column prop="telphone_n" label="联系方式" ></el-table-column>
                <el-table-column prop="time_n" label="关注时间" align="center"></el-table-column>
                <el-table-column label="操作" fixed="right">
                    <template slot-scope="scope">
                        <div class="cz_button">
                            <el-button size="mini" @click="delant(scope.$index)" type="danger">删除</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <el-checkbox v-model="checkedAll" :indeterminate="checkedAllIndeterminate" @change="antcheckAll">全选</el-checkbox>
                <el-button @click="antbatch('del')" size="mini">批量删除</el-button>
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                   :current-page="page" :page-sizes="pageSizes" :page-size="limit"
                   layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
    </div>
</template>

<script>
module.exports = {
    props: {
        xjh_id: {
            type: String,
            default: {}
        },
    },
    data: function () {
        return {
        	loading: false,
        	dataText: '数据加载中',
            page: 1,
            limit: 0,
            antlist: [],
            total: 0,
            pageSizes: [],

            checkedAll: false, // 全选
            checkedAllIndeterminate: false,
            multipleSelection: [], // 多选值存储
            idArr: [],
			islook: false,
			tableHig: true,
			xjhid: '',

			prevPage: 0
        }
    },
    watch: {
        xjh_id: {
            handler(val) {
                this.xjhid = val;
				this.getList();
            },
            immediate: true,
            deep: true,
        },
    },
    methods: {
        handleSizeChange(val) {
            this.limit = val;
            this.getList();
        },
        handleCurrentChange(val) {
            this.page = val;
            this.getList();
        },
		getList() {
		    let that = this,
				params = {
					id: that.xjhid,
					page: that.page,
					limit: that.limit,
				};
				that.loading = true;

		    httpPost('m=user&c=school_xjh&a=listxjh', params).then(function (response) {
		        let res = response.data,
		            data = res.data;

		        that.antlist = data.list;
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
				if (that.antlist.length === 0) {
                    that.dataText = "暂无数据";
                }
		    })
		},
		handleSelectionChange(val) {
		    if (val.length == 0) {
		        this.checkedAll = false;
		        this.checkedAllIndeterminate = false;
		    } else {
		        if (val.length === this.antlist.length) {
		            this.checkedAll = true;
		            this.checkedAllIndeterminate = false;
		        } else {
		            this.checkedAll = false;
		            this.checkedAllIndeterminate = true;
		        }
		    }
		    this.multipleSelection = val;
		},
		antbatch(type) {
		    if (this.multipleSelection.length == 0) {
		        message.warning('请选择要操作的数据项');
		        return false;
		    }

		    let idArr = [];
		    this.multipleSelection.forEach(function(item) {
		        idArr.push(item.id);
		    })
		    this.idArr = idArr;

		    if (type == 'del') {
		        this.delant();
		    }
		},
		antcheckAll(val) {
		    val ? this.checkedAllIndeterminate = false : '';
		    this.$refs.multipleTable.toggleAllSelection();
		},
		delant(idx) {
		    let that = this,
		        params = {},
		        msg = '';

		    if (typeof idx == 'undefined') { // 批量删除
		        params.del = this.idArr;
		        msg = '你确定要删除选中项吗？';
		    } else {// 单个删除
		        params.del = that.antlist[idx].id;
		        msg = '你确定要删除当前项吗？';
		    }

		    delConfirm(this, params, function (params) {
		        httpPost('m=user&c=school_xjh&a=dellsit', params).then(function(res) {
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
.jlzt .el-button--text {
    font-size: 12px;
}

.jlwgk .el-button--text {
    color: #666;
    font-size: 12px;
}

.el-tag {
    margin-right: 10px;
    margin-bottom: 10px;
}

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

/* 上传样式开始 */
.avatar-uploader .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.avatar-uploader .el-upload:hover {
    border-color: #409EFF;
}

.avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 100px;
    height: 100px;
    line-height: 100px;
    text-align: center;
}

.avatar {
    width: 100px;
    height: 100px;
    display: block;
}

.fenpeizhand .toolClasList {
    flex-wrap: wrap;
}

.toolClasTipse {
    overflow: hidden;
    position: relative;
    padding-left: 75px;
    width: calc(100% - 75px);
}

.toolClasTipse .el-alert {
    overflow: hidden;
    position: relative;
    padding: 6px 0;
    background: none;
}

/* 上传样式结束 */
</style>
