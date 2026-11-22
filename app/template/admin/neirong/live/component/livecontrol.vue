<template>
    <div id="livekzt" class="liveKztSubject">
        <div class="liveKztMode">
            <div class="liveKztModeUps">
                <livevideo ref="video" :xinfo="info"></livevideo>
                <div class="liveKztComShow" v-if="explain.comname">
                    <div class="liveKztCominc" @click="companClek('explain')">
                        <div class="liveKztComBitma">
                            <span>{{explain.comname}}</span>
                        </div>
                        <div class="liveKztCodButn">
                            <el-button type="text">详细</el-button>
                        </div>
                    </div>
                    <div class="liveKztComposr">
                        <el-carousel height="40px">
                            <el-carousel-item v-for="(item,index) in explain.joblist" :key="'jitem'+index">
                                <div class="posterList">
                                    <div class="posterLiInfo" v-for="(job,jindex) in item" :key="'job'+jindex">
                                        <div class="posterLiNae">
                                            <span>{{job.name}}</span>
                                        </div>
                                        <div class="posterLiBtn">
                                            <el-button type="text">{{job.salary}}</el-button>
                                        </div>
                                    </div>
                                </div>
                            </el-carousel-item>
                        </el-carousel>
                    </div>
                </div>
            </div>
            <div class="liveKztModeDwon">
                <livecompay ref="com" :id="xid" :list="comlist"></livecompay>
            </div>
        </div>
        <div class="liveKztMode">
            <div class="liveKztModeUps" :class="{ 'liveKztModeUpCur': liveHeight }">
                <livehudon ref="hudong" :id="xid" :list="jylist" :xinfo="info" @liveup="liveup"></livehudon>
            </div>
            <div class="liveKztModeDwon" :class="{ 'liveKztModeDwonCur': liveHeight }">
                <div class="liveKztHeight">
                    <div class="liveKztHeIcon" @click="liveHeight = !liveHeight"
                         :class="{ 'liveKztHeIconCur': liveHeight }">
                        <i class="el-icon-arrow-down"></i>
                        <i class="el-icon-arrow-up"></i>
                    </div>
                </div>
                <livetonji ref="tj" :id="xid" :tjdata="tj"></livetonji>
            </div>
        </div>
        <div class="modluDrawer">
            <el-dialog title="" :visible.sync="combox" :with-header="true" append-to-body
                       :modal-append-to-body="false" :show-close="true" width="500px">
                <div class="comPopuInfo">
                    <div class="comPopuInImg">
                        <img :src="com.logo" alt="">
                    </div>
                    <div class="comPopuInTite">
                        <h4>{{com.comname}}</h4>
                    </div>
                </div>
                <div class="comPopuPositul">
                    <ul>
                        <div v-for="(item,cjkey) in com.joblist" :key="'cjob'+cjkey" class="comPopuPosLiae">
                            <li v-for="(job,jkey) in item" :key="'job'+jkey">
                                <div class="comPopuPosTites">
                                    <div class="comPopuPosName">
                                        <p>{{job.name}}</p>
                                        <span>{{job.salary}}</span>
                                    </div>
                                    <div class="comPopuPosGhuku">
                                        <span>{{job.citystr}}·{{job.job_exp}}经验·{{job.job_edu}}学历</span>
                                    </div>
                                </div>
                                <div class="comPopuPosButnd">
                                    <el-button type="primary" size="mini" round @click="shareJob(job)">分享到聊天</el-button>
                                </div>
                            </li>
                        </div>
                    </ul>
                </div>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    module.exports = {
        props: {
            id: {
                type: String,
                default: ''
            },
        },
        data: function () {
            return {
                xid: '',
                info: {},
                comlist: [],
                jylist: [],
                explain: {comname: '', joblist: []},
                liveHeight: false,
                combox: false,
                com: {},
                chaturl: '',
                tj: {},
            }
        },
        mounted() {
			
        },
        components: {
            'livevideo': httpVueLoader('./livevideo.vue'),
            'livehudon': httpVueLoader('./livehudon.vue'),
            'livecompay': httpVueLoader('./livecompay.vue'),
            'livetonji': httpVueLoader('./livetonji.vue'),
        },
        watch: {
            id: {
                handler(val, oldVal) {
                    this.xid = val;
                    if (val) {
                        this.getinfo()
                        this.getControlData()
                        this.getTj()
                    }
                },
                immediate: true,
                deep: true,
            },
        },
        methods: {
            shareJob: function(job){
                this.$refs.hudong.socketcheck(4, job)
            },
            async getinfo() {
                var that = this
                let params = {id: that.xid}
                httpPost('m=neirong&c=xjhlive&a=control', params).then(function (response) {
                    if (response.data.error == 0) {
                        that.info = response.data.data
                        that.$emit('setsyncid', that.info.syncid)
                    } else {
                        message.error(response.data.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            async getControlData() {
                var that = this
                let params = {xid: that.xid}
                httpPost('m=neirong&c=xjhlive&a=getControlData', params).then(function (response) {
                    var res = response.data
                    if (res.error == 0) {
                        that.comlist = res.data.comlist;
                        that.jylist = res.data.jylist;
                        if (res.data.explain) {
                            that.explain = res.data.explain;
                        } else {
                            that.explain = {comname: '', joblist: []}
                        }
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            getTj: function(){
                var self = this;
                httpPost('m=neirong&c=xjhlive&a=getTj', {xid: self.xid}).then(function (response) {
                    var res = response.data
                    if (res.error == 0) {
                        self.tj = res.data;
                    } else {
                        message.error(res.msg);
                    }
                }).catch(function (error) {
                    console.log(error);
                })
            },
            companClek: function (e) {
                if (e == 'explain') {
                    this.com = this.explain;
                } else {
                    this.com = this.comlist[e];
                }
                this.combox = true;
            },
			liveup: function(e){
				console.log(e);
				that.info.livestatus = e.status;
			}
        },
    };
</script>
<style>  .el-dialog__body {
    padding: 0px 20px;
}

.wxsettip_small {
    padding: 20px 0
}

.yd_ztkyd_active {
    background-color: #409eff;
    color: #fff;
}

.admin_web_tip {
    display: inline-block;
    line-height: 16px;
    padding-left: 20px;
    color: #999;
    font-size: 12px;
    padding-top: 8px;
}
</style>