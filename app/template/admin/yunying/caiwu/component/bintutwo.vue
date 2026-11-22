<template>
    <div class="xiaofeiBintAll">
        <div class="xiafeBintListd">
            <div class="cwszInfoTite">
                <div class="cwszInfoNames"><span>消费行业</span></div>
                <div class="echartTimes">
                    <el-date-picker v-model="xiaofeiTime" size="small" type="daterange" range-separator="至"
                                    start-placeholder="开始日期" value-format="yyyy-MM-dd" @change="getlist"
                                    end-placeholder="结束日期">
                    </el-date-picker>
                </div>
            </div>
            <div class="chatsBinDome" id="chatshour2">
            </div>
        </div>
        <div class="xiafeBintListd">
            <div class="cwszInfoTite">
                <div class="cwszInfoNames"><span>新开单/续费占比</span></div>
                <div class="echartTimes">
                    <el-date-picker v-model="create_renewal_time" size="small" type="daterange" range-separator="至"
                                    start-placeholder="开始日期" @change="createRenewal" value-format="yyyy-MM-dd"
                                    end-placeholder="结束日期" >
                    </el-date-picker>
                </div>
            </div>
            <div class="chatsBinDome" id="chathuiy2"></div>
        </div>
        <div class="xiafeBintListd">
            <div class="cwszInfoTite">
                <div class="cwszInfoNames"><span>新开单/续费金额占比</span></div>
                <div class="echartTimes">
                    <el-date-picker v-model="create_renewal_price_time" size="small" type="daterange" range-separator="至"
                                    start-placeholder="开始日期" value-format="yyyy-MM-dd" @change="createRenewalPrice"
                                    end-placeholder="结束日期">
                    </el-date-picker>
                </div>
            </div>
            <div class="chatsBinDome" id="chataoca2"></div>
        </div>
    </div>
</template>
<script>
module.exports = {
    props: {
        time: Array
    },
    watch: {
        time: {
            handler(val) {
                this.create_renewal_time = this.time;
                this.create_renewal_price_time = this.time;
                this.xiaofeiTime = this.time;
                this.getlist();
                this.createRenewal();
                this.createRenewalPrice();
            },
            immediate: true,
            deep: true,
        }
    },
    data: function () {
        return {
            create_renewal_time: [],
            create_renewal_price_time: [],
            xiaofeiTime: [],
            inComeTitle: [],
            list: [],
            vip_list: [],
            create_renewal_list:[],
            create_renewal_price_list:[],
            price_list: [],
            uri: "m=yunying&c=",
            value1: '',
        }
    },
    methods: {
        getlist: async function () {
            let _this = this;
            let url = _this.uri + 'finance_statis&a=hangye';
            let sendData = {};
            if (this.time.length > 0) {
                sendData.time_end = this.xiaofeiTime[1];
                sendData.time_begin = this.xiaofeiTime[0]
            }
            let response = await httpPost(url, sendData,{hideloading: true})
            let res = response.data;
            if (res.error == 0) {
                _this.list = res.data.values;
                _this.getCharts1();
            }
        },
        createRenewal: async function () {
            let _this = this;
            let url = _this.uri + 'finance_statis&a=create_renewal_compare';
            let sendData = {};
            if (this.time.length > 0) {
                sendData.time_end = this.create_renewal_time[1];
                sendData.time_begin = this.create_renewal_time[0]
            }
            let response = await httpPost(url, sendData,{hideloading: true})
            let res = response.data;
            if (res.error == 0) {
                _this.create_renewal_list = res.data.values;
                _this.getCharts2();
            }
        },
        createRenewalPrice: async function () {
            let _this = this;
            let url = _this.uri + 'finance_statis&a=create_renewal_compare_price';
            let sendData = {};
            if (this.time.length > 0) {
                sendData.time_end = this.create_renewal_price_time[1];
                sendData.time_begin = this.create_renewal_price_time[0]
            }
            let response = await httpPost(url, sendData,{hideloading: true})
            let res = response.data;
            if (res.error == 0) {
                _this.create_renewal_price_list = res.data.values;
                _this.getCharts3();
            }
        },
        getCharts1() {
            var myChart = echarts.init(document.getElementById('chatshour2'));


            var option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    },
                },
                color: ['#5971FB', '#FF7752', '#D172FB', '#52C4B9', '#F15384'],
                legend: {
                    left: 'center',
                    bottom: '2%',
                },
                series: [{
                    name: '消费行业',
                    type: 'pie',
                    radius: ['42%', '65%'],
                    center: ['50%', '50%'],
                    data: this.list,
                    itemStyle: {
                        normal: {
                            label:{
                                show: true,
                                formatter: '{b}: {c} ({d}%)',
                                textStyle:{fontSize:"12"}
                            }
                        }
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '12',
                            fontWeight: 'bold'
                        }
                    },
                }]
            };
            myChart.setOption(option);
            setTimeout(function () {
                window.addEventListener("resize", () => {
                    myChart.resize();
                });
            }, 200);
        },
        getCharts2() {
            var myChart = echarts.init(document.getElementById('chathuiy2'));
            var option = {
                tooltip: {
                    trigger: 'item'
                },
                color: ['#5971FB', '#FF7752', '#D172FB', '#52C4B9', '#F15384'],
                legend: {
                    left: 'center',
                    bottom: '2%',
                },
                series: [
                    {
                        name: '会员套餐数量占比',
                        type: 'pie',
                        radius: ['42%', '65%'],
                        center: ['50%', '50%'],
                        data: this.create_renewal_list,
                        itemStyle: {
                            normal: {
                                label:{
                                    show: true,
                                    formatter: '{b}: {c} ({d}%)',
                                    textStyle:{fontSize:"12"}
                                }
                            }
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: '12',
                                fontWeight: 'bold'
                            }
                        },
                    }
                ]
            };

            myChart.setOption(option);
            setTimeout(function () {
                window.addEventListener("resize", () => {
                    myChart.resize();
                });
            }, 200);
        },
        getCharts3() {
            var myChart = echarts.init(document.getElementById('chataoca2'));
            var option = {
                tooltip: {
                    trigger: 'item'
                },
                color: ['#52C4B9', '#F15384'],
                legend: {
                    left: 'center',
                    bottom: '2%',
                },
                series: [
                    {
                        name: '新开单/续费金额占比',
                        type: 'pie',
                        radius: ['42%', '65%'],
                        center: ['50%', '50%'],
                        data: this.create_renewal_price_list,
                        // data: [
                        //     { value: 1048, name: '金牌会员' },
                        //     { value: 735, name: '银牌会员' },
                        //     { value: 580, name: '年会员' },
                        //     { value: 484, name: '免费会员' },
                        // ],
                        itemStyle: {
                            normal: {
                                label:{
                                    show: true,
                                    formatter: '{b}: {c} ({d}%)',
                                    textStyle:{fontSize:"12"}
                                }
                            }
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: '12',
                                fontWeight: 'bold'
                            }
                        },
                    }
                ]
            };

            myChart.setOption(option);
            setTimeout(function () {
                window.addEventListener("resize", () => {
                    myChart.resize();
                });
            }, 200);
        }
    },
};
</script>
<style scoped></style>