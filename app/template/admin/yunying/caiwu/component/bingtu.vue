<template>
    <div class="xiaofeiBintAll">
        <div class="xiafeBintListd">
            <div class="cwszInfoTite">
                <div class="cwszInfoNames"><span>收入来源</span></div>
                <div class="echartTimes">
                    <el-date-picker v-model="income_time" @change="getlist"
                                    size="small" type="daterange"
                                    range-separator="至" start-placeholder="开始日期" value-format="yyyy-MM-dd"
                        end-placeholder="结束日期">
                    </el-date-picker>
                </div>
            </div>
            <div class="chatsBinDome" id="chatshour">
            </div>
        </div>
        <div class="xiafeBintListd">
            <div class="cwszInfoTite">
                <div class="cwszInfoNames"><span>会员套餐数量占比</span></div>
                <div class="echartTimes">
                    <el-date-picker v-model="company_order_time" size="small"
                                    @change="companyorder"
                                    type="daterange" range-separator="至" start-placeholder="开始日期" value-format="yyyy-MM-dd"
                        end-placeholder="结束日期">
                    </el-date-picker>
                </div>
            </div>
            <div class="chatsBinDome" id="chathuiy"></div>
        </div>
        <div class="xiafeBintListd">
            <div class="cwszInfoTite">
                <div class="cwszInfoNames"><span>会员套餐金额占比</span></div>
                <div class="echartTimes">
                    <el-date-picker v-model="price_time"
                                    @change="companyorderPrice"
                                    size="small" type="daterange" range-separator="至" start-placeholder="开始日期" value-format="yyyy-MM-dd"
                        end-placeholder="结束日期">
                    </el-date-picker>
                </div>
            </div>
            <div class="chatsBinDome" id="chataoca"></div>
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
                this.income_time = this.time;
                this.company_order_time = this.time;
                this.price_time = this.time;
                this.getlist();
                this.companyorder();
                this.companyorderPrice();
                // this.shouzhiFun();
            },
            immediate: true,
            deep: true,
        }
    },
    data: function () {
        return {
            income_time :[],
            company_order_time:[],
            price_time:[],
            inComeTitle:[],
            list:[],
            vip_list:[],
            price_list:[],
            uri: "m=yunying&c=",
            value1: '',
        }
    },
    // mounted() {
    //     this.getCharts2();
    //     this.getCharts3();
    // },
    methods: {
         getlist:async function(){
            let _this = this;
            let url = _this.uri + 'finance_statis&a=income';
            let sendData = {};
            if (this.time.length > 0) {
                sendData.time_end = this.income_time[1];
                sendData.time_begin = this.income_time[0]
            }
            let response =await httpPost(url, sendData,{hideloading: true})
            let res = response.data;
            if (res.error == 0) {
                _this.list = res.data.data.values;
                _this.getCharts1();
            }
        },
        companyorder:async function () {
            let _this = this;
            let url = _this.uri + 'finance_statis&a=vip_companyorder';
            let sendData = {};
            if (this.time.length > 0) {
                sendData.time_end = this.company_order_time[1];
                sendData.time_begin = this.company_order_time[0]
            }
            let response =await httpPost(url, sendData,{hideloading: true})
            let res = response.data;
            if (res.error == 0) {
                _this.vip_list = res.data.values;
                _this.getCharts2();
            }
        },
        companyorderPrice:async function(){
            let _this = this;
            let url = _this.uri + 'finance_statis&a=vipprice_companyorder';
            let sendData = {};
            if (this.time.length > 0) {
                sendData.time_end = this.price_time[1];
                sendData.time_begin = this.price_time[0]
            }
            let response =await httpPost(url, sendData,{hideloading: true})
            let res = response.data;
            if (res.error == 0) {
                _this.price_list = res.data.values;
                _this.getCharts3();
            }
        },
        getCharts1() {
            var myChart = echarts.init(document.getElementById('chatshour'));
            var option = {
                tooltip: {
                    trigger: 'item'
                },
                color: ['#5971FB', '#FF7752', '#D172FB', '#52C4B9', '#F15384'],
                legend: {
                    // orient: 'vertical',
                    left: 'center',
                    bottom:'2%',
                },
                series: [
                    {
                        name: '收入来源',
                        type: 'pie',
                        radius: ['42%', '65%'],
                        center: ['50%', '50%'],
                        data:this.list,
                        // data: [
                        //     { value: 1048, name: '下载简历' },
                        //     { value: 735, name: '会员充值' },
                        //     { value: 580, name: '金额充值' },
                        //     { value: 484, name: '积分充值' },
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
        },
        getCharts2() {
            var myChart = echarts.init(document.getElementById('chathuiy'));
            var option = {
                tooltip: {
                    trigger: 'item'
                },
                color: ['#5971FB', '#FF7752', '#D172FB', '#52C4B9', '#F15384'],
                legend: {
                    left: 'center',
                    bottom:'2%',
                },
                series: [
                    {
                        name: '会员套餐数量占比',
                        type: 'pie',
                        radius: ['42%', '65%'],
                        center: ['50%', '50%'],
                        data: this.vip_list,
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
            var myChart = echarts.init(document.getElementById('chataoca'));
            var option = {
                tooltip: {
                    trigger: 'item'
                },
                color: ['#5971FB', '#FF7752', '#D172FB', '#52C4B9', '#F15384'],
                legend: {
                    left: 'center',
                    bottom:'2%',
                },
                series: [
                    {
                        name: '会员套餐金额',
                        type: 'pie',
                        radius: ['42%', '65%'],
                        center: ['50%', '50%'],
                        data:this.price_list,
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