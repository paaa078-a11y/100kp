<template>
    <div class="liveKztMokuai">
        <div class="liveKztTongsd">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="直播统计" name="zbtj">
                    <div class="liveKztTonLis">
                        <ul>
                            <li>
                                <div class="liveKztTonIcmg">
                                    <img src="../../images/Group-30.png" alt="">
                                </div>
                                <div class="liveKztTonNum">
                                    <b>{{tj.pv}}</b>
                                    <span>访问次数（PV）</span>
                                </div>
                            </li>
                            <li>
                                <div class="liveKztTonIcmg">
                                    <img src="../../images/Group-32.png" alt="">
                                </div>
                                <div class="liveKztTonNum">
                                    <b>{{tj.uv}}</b>
                                    <span>直播访客（UV）</span>
                                </div>
                            </li>
                            <li>
                                <div class="liveKztTonIcmg">
                                    <img src="../../images/Group-31.png" alt="">
                                </div>
                                <div class="liveKztTonNum">
                                    <b>{{tj.onlinenum}}</b>
                                    <span>同时最高在线人数（人）</span>
                                </div>
                            </li>
                            <li>
                                <div class="liveKztTonIcmg">
                                    <img src="../../images/Group-33.png" alt="">
                                </div>
                                <div class="liveKztTonNum">
                                    <b>{{tj.duration}}</b>
                                    <span>视频直播时长（分钟）</span>
                                </div>
                            </li>
                            <li>
                                <div class="liveKztTonIcmg">
                                    <img src="../../images/Group-35.png" alt="">
                                </div>
                                <div class="liveKztTonNum">
                                    <b>{{tj.regnum}}</b>
                                    <span>新注册用户数（个）</span>
                                </div>
                            </li>
                            <li>
                                <div class="liveKztTonIcmg">
                                    <img src="../../images/Group-36.png" alt="">
                                </div>
                                <div class="liveKztTonNum">
                                    <b>{{tj.sqnum}}</b>
                                    <span>简历投递数（次）</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="入场记录" name="rcjl">
                    <div class="liveKztToTable">
                        <el-table :data="tj.log" style="width: 100%" stripe
                                  :header-cell-style="{ background: '#2F3A5B', color: '#AEB0C4' }" :height="tj.uv > 10 ? '85%' : '100%'">
                            <el-table-column prop="name" label="名称" width="180"></el-table-column>
                            <el-table-column prop="ip" label="IP" width="180"></el-table-column>
                            <el-table-column prop="ctime_n" label="进入时间" width="180"></el-table-column>
                            <el-table-column prop="utype" label="类型"></el-table-column>
                        </el-table>
                        <div v-if="tj.uv > 10" style="display: flex;align-items: center;justify-content: center;">
                            <el-button @click="touserlog" type="primary" round>查看更多入场记录
                            </el-button>
                        </div>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="入场趋势" name="rcqs">
                    <div class="liveKztToEchat" id="main1"></div>
                </el-tab-pane>
            </el-tabs>
        </div>
        <!--查看更多入场记录-->
        <el-drawer title="入场记录" :visible.sync="draweruserlog"
                   :modal-append-to-body="false" append-to-body size="80%">
            <liveuserlog ref="userlog" :id="xid"></liveuserlog>
        </el-drawer>
    </div>
</template>

<script>
    module.exports = {
        props: {
            id: {
                type: String,
                default: ''
            },
            tjdata: {
                type: Object,
                default: () => {
                }
            },
        },
        data: function () {
            return {
                
                activeName: 'zbtj',
                xid: '',
                tj: {},
                xdata: [],
                ydata: [],
                draweruserlog: false
            }
        },
        mounted() {

        },
        components: {
            'liveuserlog': httpVueLoader('./liveuserlog.vue'),
        },
        watch: {
            id: {
                handler(val, oldVal) {
                    this.xid = val;
                },
                immediate: true,
                deep: true,
            },
            tjdata: {
                handler(val, oldVal) {
                    this.tj = val;
                },
                immediate: true,
                deep: true,
            },
        },
        methods: {
            // 查看更多入场记录
            touserlog(){
                this.draweruserlog = true
                this.$refs.userlog.getList()
            },
            handleClick(tab) {
                if(tab.name == 'rcqs'){
                    var self = this;
                    httpPost('m=neirong&c=xjhlive&a=tendency', {xid: self.xid}).then(function (response) {
                        if (response.data.error == 0) {
                            var res = response.data.data
                            self.xdata = [], self.ydata = [];
                            for (var i in res) {
                                self.xdata.push(res[i].tjtime);
                                self.ydata.push(res[i].count);
                            }
                            self.$nextTick(() => {
                                self.initCharts1();
                            })
                        } else {
                            message.error(response.data.msg);
                        }
                    }).catch(function (error) {
                        console.log(error);
                    })
                }
            },
            initCharts1() {
                var myChart = echarts.init(document.getElementById('main1'));
                var option = {
                    color: ['#8997FB', '#23C9C9', '#1890FF'],
                    legend: {
                        left: 'right',
                        // icon: 'circle',
                        show: false
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    grid: {
                        top: '6%',
                        left: '2%',
                        right: '2%',
                        bottom: '2%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        // boundaryGap: false,
                        data: this.xdata,
                        axisLine: {show: false},
                        axisTick: {show: false},
                        splitLine: {
                            lineStyle: {
                                type: 'dashed',
                                color: '#f3f3f3'
                            },
                            show: false
                        }
                    },
                    yAxis: {
                        type: 'value',
                        axisLine: {show: false},
                        axisTick: {show: false},
                        splitLine: {
                            lineStyle: {
                                type: 'dashed',
                                color: '#374366'
                            },
                            show: true
                        }
                    },
                    series: [

                        {
                            name: '新入场人数',
                            data: this.ydata,
                            type: 'line',
                            stack: 'total',
                            symbol: 'circle',//拐点样式
                            symbolSize: 10,
                        },
                    ]
                };
                myChart.setOption(option);
                setTimeout(function () {
                    window.addEventListener("resize", () => {
                        myChart.resize();
                    });
                }, 200);
            },
        },
    };
</script>

<style scoped>

</style>