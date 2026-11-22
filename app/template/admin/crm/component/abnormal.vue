<template>
    <div class="drawerModlue">
        <div class="moduleSeachs">
            <div class="moduleElTabInpt">
                <div class="moduleInptList moduleInptWidt">
                    <el-select v-model="time" placeholder="统计时间" clearable @change="search">
                        <el-option label="昨天" value="1"></el-option>
                        <el-option label="今天" value="2"></el-option>
                        <el-option label="一周内" value="3"></el-option>
                        <el-option label="一月内" value="4"></el-option>
                        <el-option label="三月内" value="5"></el-option>
                    </el-select>
                </div>
                <div class="moduleInptList">
                    <el-date-picker v-model="daterange" type="datetimerange" value-format="yyyy-MM-dd HH:mm:ss" align="right" unlink-panels range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" clearable @change="changedate"></el-date-picker>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable">
            <el-table ref="table" :data="tableData" v-loading="list_loading" border style="width: 100%" stripe :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" :empty-text="emptytext">
                <el-table-column prop="uid" label="用户ID" width="100"></el-table-column>
                <el-table-column prop="username" label="用户名" width="170"></el-table-column>
                <el-table-column prop="name" label="名称" min-width="100"></el-table-column>
                <el-table-column prop="job_name" label="职位名称" min-width="100" v-if="type=='5' || type=='6'"></el-table-column>
                <el-table-column prop="job_name" label="拨号岗位" min-width="100" v-if="type=='7'"></el-table-column>
                <el-table-column prop="login_date" label="企业最后登录时间" min-width="100" v-if="type=='2'"></el-table-column>
                <el-table-column prop="num" label="统计" width="200"></el-table-column>
                <el-table-column fixed="right" label="操作" width="130">
                    <template slot-scope="scope">
                        <div class="moduleElTaCaoz" style="display: initial;">
                            <el-button type="danger" size="small" @click="showDetail(scope.row)">分析</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modluDrawer">
            <el-drawer :visible.sync="detail_show" :modal-append-to-body="false" append-to-body :show-close="true" :with-header="true" size="70%">
                <div slot="title">
                    <div class="yj_chlose" style="margin-bottom: 5px;">
                        <span style="font-weight: bold; font-size: 18px;">{{detail_title}}</span>
                    </div>
                </div>
                <abnormaldetail v-if="detail_show" :usertype='detail_usertype' :uid="detail_uid" :type="type" :timev="time" :job_id="detail_jobid" @set-title="setTitle"></abnormaldetail>
            </el-drawer>
        </div>
    </div>
</template>
<script>
    module.exports = {
        props: {
            type_v: {
                type: String,
                default: ''
            },
            time_v: {
                type: String,
                default: ''
            },
			crm_uid: {
				type: String,
				default: ''
			}
        },
        watch: {
            type_v: {
                handler(n, v) {
                    this.type = n ? n : '1';
                },
                immediate: true
            },
            time_v: {
                handler(n, v) {
                    this.time = n;
                },
                immediate: true
            }
        },
        data: function () {
            return {
                emptytext: '暂无数据',
                numarr: {},
                tableData: [],
                total: 0,
                limit: 50,
                currentPage: 1,
                page_sizes: [],

                list_loading: false,

                keyword: '',
                type: '',
                time: '',
                daterange: [],
                sort_t: '',
                order: '',

                detail_show: false,
                detail_title: '',
                detail_uid: '',
                detail_jobid: '',
                detail_usertype: ''
            }
        },
        components: {
            'abnormaldetail': httpVueLoader('./abnormaldetail.vue'),
        },
        mounted() {
            this.getList();
        },
        methods: {
            async getList() {
                let that = this;
                let params = {
                    limit: that.limit,
                    type: that.type,
                    time: that.time,
                    list: 1,
                    usertype: 2,
                }
                if (this.daterange && this.daterange.length > 0) {

                    params['time'] = this.daterange[0] + '~' + this.daterange[1];
                }
                if (this.crm_uid == 'all'){

					params.all = 1;
				} else if (parseInt(this.crm_uid) > 0){

					params.crm_uid = this.crm_uid;
				}
                this.list_loading = true;
                that.emptytext = "数据加载中";
                httpPost('m=crm&c=crm_my_customer&a=abnormal', params).then((result) => {
                    this.list_loading = false;
                    var res = result.data;
                    if (res.error == 0) {
                        that.tableData = res.data.list;
                        if (that.tableData.length === 0) {
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
            changedate: function (e) {
                if (e !== null) {
                    this.time = '';
                }
            },
            showDetail: function (row) {
                var detail_jobid = '';
                var detail_usertype = '';
                this.detail_uid = row.uid;
                if (this.type == '5' || this.type == '6') {
                    detail_jobid = row.job_id;
                    detail_usertype = row.usertype;
                }
                this.detail_jobid = detail_jobid;
                this.detail_usertype = detail_usertype;
                this.detail_show = true;
            },
            setTitle: function (e) {
                this.detail_title = e.title;
            }
        }
    };
</script>
<style scoped>
    .moduleInptList .el-select .el-input {
        width: 140px;
    }
</style>