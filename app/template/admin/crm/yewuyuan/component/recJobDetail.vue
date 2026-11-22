<template>
    <div class="moduleElHight" style="padding: 0 20px;">
        <div class="moduleSeachs">
            <div class="moduleSeachleft">
            </div>
            <div class="moduleSeachButn" style="display: flex;align-items: center;">
                <el-button type="primary" icon="el-icon-share" size="mini" @click="shareOpen()">分享到朋友圈</el-button>
            </div>
        </div>
        <div class="admin_datatip">
            <i class="el-icon-document"></i>
            推送时间：{{recInfo.time_n}}
            <span class="admin_datatip_n">职位：{{recInfo.jnum}} 个</span>
            <span class="admin_datatip_n">合计点击：{{recInfo.hits}} 次</span>
        </div>
        <div class="moduleElTable" style="height: calc(100% - 68px);">
            <el-table :data="list" style="width: 100%" :show-header="false"
                      :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" v-loading="loading">
                <template slot="empty">
                    <p>{{dataText}}</p>
                </template>
                <el-table-column label="职位信息" sortable="custom">
                    <template slot-scope="scope">
                        <div>
                            <div>
                                {{scope.row.uname}} {{scope.row.name}}
                            </div>
                            <div class="gsd">
                                <span>{{scope.row.city_n}}</span>
                                <span>{{scope.row.exp_n}}经验</span>
                                <span>{{scope.row.edu_n}}学历</span>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="企业信息" width="" sortable="custom">
                    <template slot-scope="scope">
                        <div>
                            <div class="">{{scope.row.salary_n}}</div>
                            <div class="gsd">{{scope.row.com_name}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="lastupdate_n" label="更新时间" width="100">
                </el-table-column>
            </el-table>
        </div>

        <div class="modluDrawer">
            <el-dialog title="" :visible.sync="dialogQrCode" append-to-body width="250px" center>
                <div style="text-align: center">
                    <img :src="shareCode" width="200" height="200">
                </div>
                <div style="text-align: center">
                    <span>二维码分享</span>
                </div>
            </el-dialog>
        </div>
    </div>
</template>

<script>
    module.exports = {
        props: ['id'],
        data: function () {
            return {
                pytoken: null,
                baseUrl: null,

                loading: false,
                dataText: '数据加载中',

                recInfo: {},
                // 列表
                list: [],

                // 分享到朋友圈
                dialogQrCode: false,
                shareCode: '',
            }
        },
        components: {
        },
        created: function () {
            this.pytoken = localStorage.getItem('pytoken');
            this.baseUrl = localStorage.getItem('baseUrl');
            this.getList();
        },
        methods: {
            getList() {
                let that = this,
                    params = {
                        id: that.id
                    };
                that.loading = true;
                httpPost('m=crm&c=crm_index&a=getRecInfo', params).then(function (response) {
                    let res = response.data,
                        data = res.data;

                    that.recInfo = data.recInfo;
                    that.list = data.recJobs;
                    that.loading = false;
                    if (that.list.length === 0) {
                        that.dataText = "暂无数据";
                    }
                })
            },

            shareOpen() {
                this.shareCode = `${this.baseUrl}?m=crm&c=crm_index&a=getShareCode&ids=${this.recInfo.ids}&pytoken=${this.pytoken}`;
                this.dialogQrCode = true;
            },
        },
        watch: {
            id: function (val, oldVal) {
                this.getList();
            },
        }
    };
</script>
<style scoped></style>