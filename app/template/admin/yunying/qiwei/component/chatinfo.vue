<template>
    <el-drawer title="群聊详情" :visible.sync="showinfo" append-to-body size="80%">
        <div class="ql_cont">
            <div class="qunliaToubu">
                <div class="ql_top">
                    <div class="qunliaQunm">
                        <div class="ql_q">群</div>
                        <div class="qunliaInfo">
                            <div class="qunmiangc">{{ groupInfo.name }}</div>
                            <div class="ql_qz">群主：{{ groupInfo.owner_name }} 共 {{ numArr.inNum }} 位成员</div>
                        </div>
                    </div>
                    <div class="qunliaTips">
                        <div class="qunliaTipLis">
                            <div>
                                <span>总人数</span>
                                <b>{{ numArr.totalNum }}</b>
                            </div>
                        </div>
                        <div class="qunliaTipLis">
                            <div>
                                <span>实际人数</span>
                                <b>{{ numArr.realNum }}</b>
                            </div>
                        </div>
                        <div class="qunliaTipLis">
                            <div>
                                <span>注册用户</span>
                                <b>{{ numArr.regNum }}</b>
                            </div>
                        </div>
                        <div class="qunliaTipLis">
                            <div>
                                <span>今日新增</span>
                                <b>{{ numArr.newNum }}</b>
                            </div>
                        </div>
                        <div class="qunliaTipLis">
                            <div>
                                <span>今日流失</span>
                                <b>{{ numArr.delNum }}</b>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="qunliaGong">
                    <div class="qunliaGohead">
                        <div class="qunliaGoTite">群公告：</div>
                        <div class="ql_bth">
                            <el-button type="primary" size="mini" @click="syncGroupNotice">更新群信息</el-button>
                        </div>
                    </div>
                    <div class="ql_gg">此群暂无群公告，请在企微设置之后点击上方更新按钮~</div>
                </div>
            </div>
            <!-- <div class="admin_datatip"><i class="el-icon-document"></i> 总人数：{{ numArr.totalNum }}
                <span class="admin_datatip_n">实际人数：{{ numArr.realNum }} </span>
                <span class="admin_datatip_n">注册用户：{{ numArr.regNum }} </span>
                <span class="admin_datatip_n">今日新增： {{ numArr.newNum }} </span>
                <span class="admin_datatip_n">今日流失： {{ numArr.delNum }} </span>
            </div> -->
            <div class="ql_contlist">
                <table class="ql_conttable ">
                    <thead>
                        <tr align="left">
                            <th>成员信息</th>
                            <th>是否注册</th>
                            <th>简历完善度</th>
                            <th>投递次数</th>
                            <th>入群时间</th>
                            <th>入群方式</th>
                            <th>是否退群</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr align="left" v-for="(item, index) in memberlist" :key="index">
                            <td>{{ item.name }} <span class="ql_qywx" v-html="item.type_n"></span></td>
                            <td>{{ item.is_reg }}</td>
                            <td>{{ item.integrity }}</td>
                            <td>
								<template v-if="item.sq_uid!=''">
								    <el-link type="primary" :underline="false" @click="sqlist(item.sq_uid)">{{item.sq_num}}</el-link>
								</template>
								<template v-else>{{item.sq_num}}</template>
							</td>
                            <td>{{ item.join_time_n }}</td>
                            <td v-html="item.scene_n"></td>
                            <td><span cla="ql_wtq">{{ item.quit_n }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modulePaging">
                <div></div>
                <div class="modulePagNum">
                    <el-pagination background @size-change="handleInfoSizeChange" @current-change="handleInfoCurrentChange"
                        :current-page="infoCurrentPage" :page-sizes="infoPageSizes" :page-size="infoPerPage"
                        layout="total, sizes, prev, pager, next, jumper" :total="infototal">
                    </el-pagination>
                </div>
            </div>
			
			<!--弹窗：收到简历-->
			<div class="modluDrawer">
			    <el-drawer title="收到简历" :visible.sync="sqShow" :append-to-body="true" :modal-append-to-body="false" :show-close="true" :with-header="true" size="80%">
			        <comlog_index :param="sq_param" :searchable="false" v-if="sqShow"></comlog_index>
			    </el-drawer>
			</div>
        </div>
    </el-drawer>
</template>

<script>
module.exports = {
    props: {

    },
    data: function () {
        return {
            showinfo: false,
            infoCurrentPage: 1,
            infoPerPage: 0,
            infoPageSizes: [],
            infototal: 0,
            groupInfo: {},
            numArr: {},
            memberlist: [],
            chatid: '',
			
			sqShow:false,
			sq_param:{},
        }
    },
	components: {
	    'comlog_index': httpVueLoader('../../../user/company/component/comlog_index.vue'),
	},
    mounted() {

    },
    methods: {
		sqlist:function(uid){
			var param={};
			param.user_id = uid;
			this.sq_param = param;
			this.sqShow = true;
		},
        async getinfo() {
            let that = this;
            var params = {
                page: that.infoCurrentPage,
                pageSize: that.infoPerPage
            }
            if (!that.chatid) {
                that.$message.error('chat_id不能为空')
                return false
            } else {
                params.chat_id = this.chatid
            }
            httpPost('m=yunying&c=workwx_group&a=info', params).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    that.infoPerPage = parseInt(res.data.perPage)
                    that.infoPageSizes = res.data.pageSizes
                    that.infototal = parseInt(res.data.total)
                    that.groupInfo = res.data.groupInfo
                    that.numArr = res.data.numArr
                    that.memberlist = res.data.list
                    that.showinfo = true
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
        // 群聊详情成员列表每页数量修改
        handleInfoSizeChange(val) {
            this.infoPerPage = val;
            this.getinfo()
        },
        // 群聊详情成员列表页码改变
        handleInfoCurrentChange(val) {
            this.infoCurrentPage = val;
            this.getinfo()
        },
        // 更新群信息
        syncGroupNotice(needshowinfo) {
            var that = this
            var ii = that.$message.warning({
                message: '信息同步中，请稍候。。。',
                duration: 5000
            });
            if (!that.chatid) {
                that.$message.error('chat_id不能为空')
                return false
            }
            httpPost('m=yunying&c=workwx_group&a=syncInfo', { chat_id: this.chatid }).then(function (result) {
                var res = result.data
                if (res.error == 0) {
                    ii.close();
                    that.$message.success(res.msg)
                    if (needshowinfo != 2) {
                        that.getinfo()
                    }
                }
            }).catch(function (e) {
                console.log(e)
            })
        },
    },
};
</script>
<style scoped>
.ql_cont {
    padding: 0 20px;
}

.ql_contlist {
    width: 100%;
}

.ql_conttable {
    overflow: hidden;
    position: relative;
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #EBEEF5;
}

.ql_conttable thead {
    background: #F5F7FA;
}

.ql_conttable th {
    border: 1px solid #EBEEF5;
    padding: 10px;
    height: calc(45px - 20px);
    font-size: 13px;
    color: var(--font-color2);
    font-weight: 500;
}

.ql_conttable td {
    border: 1px solid #EBEEF5;
    padding: 13px 10px;
    font-size: 13px;
    color: #666
}

.ql_conttable tbody {
    overflow: hidden;
    position: relative;
}

.ql_conttable tbody tr:hover {
    background: #F5F7FA;
}
</style>