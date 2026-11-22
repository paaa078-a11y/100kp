<template>
    <div class="moduleElHight">
        <div class="moduleSeachbig">
            <!--关键字搜索和查询在一起-->
			<div class="tableSeachInpt tableSeachInptsmall tableSeacFromer" style="padding: 2px 0;">
			    <el-input v-model="search_params.keyword" @keyup.enter.native="search" placeholder="请输入搜索内容" size="small" clearable>
			    	<el-select v-model="search_params.type" size="small" slot="prepend" placeholder="用户名">
			    	    <el-option label="公司名称" value="1"></el-option>
			    	    <el-option label="职位名称" value="2"></el-option>
			    	</el-select>
			    </el-input>
			</div>

            <div class="tableSeachInpt">
                <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
            </div>
        </div>
        <!--<div class="admin_datatip"><i class="el-icon-document"></i> 数据统计：共 400 条<span class="admin_datatip_n">未审核：32 条-->
        <!--</span><span class="admin_datatip_n">未通过：3 条</span> <span class="admin_datatip_n">已锁定：1 条</span> <span-->
        <!--class="admin_datatip_n">搜索结果： 400 条</span>-->
        <!--</div>-->
        <div class="moduleElTable"
             style="border: 1px solid #ebeef5; width: calc(100% - 2px);">
            <el-table :data="tableData" style="width: 100%" stripe
                      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }"
                      ref="multipleTable" v-loading="loading" :empty-text="emptytext">
                <el-table-column prop="jobid" label="职位编号" width="110"></el-table-column>
                <el-table-column prop="name" label="职位" min-width="220"></el-table-column>
                <el-table-column prop="com_name" label="企业" min-width="180"></el-table-column>
                <el-table-column prop="money" label="赏金总额" width="150">
                    <template slot-scope="scope">
                        ￥{{scope.row.money}}
                    </template>
                </el-table-column>
                <el-table-column prop="sysj" label="投递赏金" width="140">
                    <template slot-scope="scope">
                        ￥{{scope.row.sqmoney}}
                    </template>
                </el-table-column>
                <el-table-column prop="tgl" label="面试赏金" width="150">
                    <template slot-scope="scope">
                        ￥{{scope.row.invitemoney}}
                    </template>
                </el-table-column>
                <el-table-column prop="sjdj" label="入职赏金" width="150">
                    <template slot-scope="scope">
                        ￥{{scope.row.offermoney}}
                    </template>
                </el-table-column>
                <el-table-column prop="lastupdate_n" label="更新时间" width="170"></el-table-column>
                <el-table-column prop="yprs" label="应聘人数" width="150">
                    <template slot-scope="scope">
                        <el-link v-if="scope.row.sqnum > 0" type="success" @click="detail(scope.row)">{{ scope.row.sqnum }}人 查看</el-link>
                        <span v-else style="color:red;">暂无应聘</span>
                        <el-link v-if="scope.row.sqArb==26" type="danger" @click="detail(scope.row)">仲裁</el-link>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="140" fixed="right">
                    <template slot-scope="scope">
                        <div class="cz_button">

                            <el-button type="danger"  @click="delrow(scope.row.jobid)">删除</el-button>

                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div>
                <!--<el-checkbox v-model="checkedAll" @change="selectAllBottom">全选</el-checkbox>-->
                <!--<el-button>批量删除</el-button>-->
                <!--<el-button>批量关闭</el-button>-->
            </div>
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange"
                               @current-change="handleCurrentChange"
                               :current-page="currentPage" :page-sizes="pageSizes"
                               :page-size="perPage"
                               layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
        <el-drawer title="赏金详细" :visible.sync="drawerdtl" append-to-body
                   size="500px">
            <div class="shbox" v-for="(item,index) in sjlist" :key="index" style="margin-bottom:10px;">
                <div class="shinfo">
                    <div class="shinfotop" style="padding-left: 0px;">
                        <div class="shcomname">{{item.uname}}</div>
                        <div class="shcomdj">求职意向：
                            <span style="color:red">{{item.jobclass}}</span>
                        </div>
                        <div class="shcomdj">应聘职位：
                            <span>{{item.name}}</span>
                        </div>
                        <div class="shcomdj">学历：
                            <span>{{item.edu}}</span>
                        </div>
                        <div class="shcomdj">工作经验：
                            <span>{{item.exp}}</span>
                        </div>
                        <div class="shcomdj">状态：
                            <span>{{item.log.nowmsg}}</span>
                        </div>
                        <div class="sh_zwsz">
                            <el-button type="warning" size="mini" @click="handlePreview(item.eid)">查看简历</el-button>
                            <el-button v-if="item.status == '26'" type="primary" size="mini" @click="getStatus(item.id)">仲裁</el-button>
                        </div>
                    </div>
                    <div style="margin-top:20px;" v-if="item.log.loglist">
                        <el-timeline :reverse="reverse" >
                            <el-timeline-item
                                    v-for="(activity, index) in item.log.loglist"
                                    :type="index == item.log.loglist.length - 1 ? 'warning': 'info'"
                                    :key="index"
                                    :timestamp="activity.time_n">
                                <span v-html="activity.info"></span>
                            </el-timeline-item>
                        </el-timeline>
                    </div>
                </div>
            </div>
        </el-drawer>
        <!--简历预览弹窗-->
        <div class="modluDrawer">
            <el-drawer title="简历预览" :append-to-body="true" :visible.sync="resumePreviewVisible" :destroy-on-close="true" size="530px">
                <resume_preview :id="eid"></resume_preview>
            </el-drawer>
        </div>
        <!--仲裁弹窗-->
        <div class="modluDrawer">
            <el-dialog title="仲裁" width="600px" :visible.sync="drawerzc" append-to-body
                       :modal-append-to-body="false">
                <div class="toolClasDia fenpeizhand" v-if="zcinfo">
                    <div class="toolClasList">
                        <div class="toolClasTite" style="width: 275px">
                            <span>【申请人】：</span>
                        </div>
                        <div class="toolClasCont">
                            {{zcinfo.username}}
                        </div>
                        <div class="toolClasTite" style="width: 275px">
                            <span>【联系手机】：</span>
                        </div>
                        <div class="toolClasCont">
                            {{zcinfo.telphone}}
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite" style="width: 275px">
                            <span>【悬赏企业】：</span>
                        </div>
                        <div class="toolClasCont">
                            {{zcinfo.comname}}
                        </div>
                        <div class="toolClasTite" style="width: 275px">
                            <span>【联系方式】：</span>
                        </div>
                        <div class="toolClasCont">
                            {{zcinfo.linktel}}
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>仲裁原因：</span>
                        </div>
                        <div class="toolClasCont">
                            {{zcinfo.loginfo}}
                        </div>
                    </div>
                    <div class="toolClasList" v-if="zcinfo.arbpic">
                        <div class="toolClasTite">
                            <span>上传凭证：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-link :href="zcinfo.arbpic" target="_blank">查看凭证</el-link>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>申请自述：</span>
                        </div>
                        <div class="toolClasCont">
                            {{zcinfo.arbinfo}}
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>仲裁说明：</span>
                        </div>
                        <div class="toolClasCont">
                            <el-input type="textarea" v-model="zcdesc" :rows="2" placeholder="请输入仲裁说明">
                            </el-input>
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span>申请自述：</span>
                        </div>
                        <div class="toolClasCont">
                            {{zcinfo.arbinfo}}
                        </div>
                    </div>
                    <div class="toolClasList">
                        <div class="toolClasTite">
                            <span></span>
                        </div>
                        <div class="toolClasCont">
                            <el-alert title="请管理员务必电话联系供求双方，了解具体情况后再作出判断！" type="warning" :closable="false"></el-alert>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="zcSave('28')">退钱给企业</el-button>
                    <el-button type="warning" @click="zcSave('29')">发放赏金给个人</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>

<script>
    module.exports = {
        data: function () {
            return {
                loading: false,
                emptytext: '暂无数据',
                search_params: {
                    type: '1',
                    keyword: '',
                },
                tableData: [],
                currentPage: 1,
                perPage: 0,
                pageSizes: [],
                total: 0,
                tableHig: true,
                drawerdtl: false,
                sjlist: [],
                resumePreviewVisible: false,
                eid: 0,
                reverse: false,
                drawerzc: false,
                zcinfo: null,
                zcdesc: '',
                rewardid: '',
                curr_row: null,
                islook: false,

                prevPage: 0
            }
        },
        mounted() {

        },
        created() {
            this.getList();
        },
        components: {
            'resume_preview': httpVueLoader('../../../component/resume_preview.vue'),
        },
        methods: {
            zcSave: function(status){
                var that = this
                if(!that.rewardid){
                    message.error('请选择需要操作的赏单！');
                    return false;
                }
                if(!status){
                    message.error('请选择您的仲裁操作！');
                    return false;
                }
                if(!that.zcdesc){
                    message.error('请填写相关仲裁说明！');
                    return false;
                }
                httpPost('m=user&c=jobpack&a=getarb', {
                    rewardid:that.rewardid,
                    status: status,
                    content: that.zcdesc
                }).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        message.success(res.msg, function () {
                            that.getList()
                            that.detail(that.curr_row)
                            that.drawerzc = false
                        })
                    } else {
                        message.error(res.msg)
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            // 仲裁
            getStatus: function (rewardid){
                var that = this
                that.rewardid = rewardid
                httpPost('m=user&c=jobpack&a=getreward', {rewardid:rewardid}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.zcinfo = res.data
                        that.drawerzc = true
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            // 预览简历
            handlePreview(eid) {
                this.eid = parseInt(eid);
                this.resumePreviewVisible = true;
            },
            // 赏金详细
            detail: function (row) {
                var that = this
                that.curr_row = row
                httpPost('m=user&c=jobpack&a=rewardlog', {jobid: row.jobid}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.sjlist = res.data.list
                        that.drawerdtl = true
                    }
                }).catch(function (e) {
                    console.log(e)
                })
            },
            getParams: function (params = {}) {
                var that = this;
                for (let i in params) {
                    if (i != 'page' && typeof that.search_params[i] != 'undefined') {
                        that.search_params[i] = params[i];
                    }
                }
            },
            handleSizeChange(val) {
                this.perPage = val;
                scrollToTop()
                this.getList()
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.getList()
            },
            search() {
                this.currentPage = 1;
                this.getList();
            },
            async getList() {
                let that = this;
                let params = {
                    page: that.currentPage,
                    pageSize: that.perPage
                }
                if (that.search_params.type) {
                    params.type = that.search_params.type
                }
                if (that.search_params.keyword) {
                    params.keyword = that.search_params.keyword
                }
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=user&c=jobpack&a=reward', params, {hideloading: true}).then(function (result) {
                    var res = result.data
                    if (res.error == 0) {
                        that.tableData = res.data.list
                        that.perPage = parseInt(res.data.perPage)
                        that.pageSizes = res.data.pageSizes
                        that.total = parseInt(res.data.total)
                        that.loading = false;
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
            delrow(id) {
                delConfirm(this, id, this.delete);
            },
            async delete(id) {
                let that = this;
                let params = {
                    delid: id
                };
                httpPost('m=user&c=jobpack&a=delreward', params).then(function (response) {
                    if (response.data.error == 0) {
                        message.success('操作成功', function () {
                            that.$refs.multipleTable.clearSelection();
                            that.getList();
                        });
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
        },
    };
</script>
<style >
    .m_xs_bx_f {
        width: 650px;
        padding-left: 15px;
    }
    .m_xs_bx_s_nm {
        width: 280px;
    }
    .m_xs_bx_s_hb {
        color: #8c9093;
        width: 320px;
        font-size: 12px;
    }
    .m_xs_bx_x {
        width: 100%;
        color: #b4b2b2;
    }
    .m_xs_bx_x_w {
        width: 280px;
    }

    .fl {
        float: left;
    }
    .tableSeacFromer{
    margin-right: 8px;
}
.tableSeacFromer .el-input-group__prepend{
    padding: 0;
    background: none;
}
.tableSeacFromer .el-select{
    margin-right: 0;
    width: 160px;
}
.tableSeacFromer .el-input{
    margin-right: 0;
}
</style>
