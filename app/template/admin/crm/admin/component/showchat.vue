<template>
    <div class="moduleChatSubs">
        <div class="showChatCont">
            <div class="showChatOnes">
                <div class="showChatTite">
                    <span>轨迹曲线图</span>
                </div>
                <div class="showChatTubao" id="showmain1"></div>
            </div>
            <div class="showChatOnes">
                <div class="showChatTite">
                    <span>最多访问</span>
                </div>
                <div class="showChatTubao" id="showmain2"></div>
            </div>
        </div>
    </div>
</template>

<script>
module.exports = {
    props: {
        cuid: {
            type: String,
            default: ''
        },
        searchday: {
            type: String,
            default: ''
        },
    },
    data: function () {
        return {
            uid: '',
            day: '',
            qxt_keys: [],
            qxt_val: [],
            cnt_keys: [],
            cnt_val: []
        }
    },
    mounted() {
        // this.getChatData()
    },
    watch: {
        cuid: {
            handler(value) {
                if (this.uid != value) {
                    this.uid = value
                }
            },
            deep: true,
            immediate: true
        },
        searchday: {
            handler(value) {
                if (this.day != value) {
                    this.day = value;
                }
            },
            deep: true,
            immediate: true
        },
    },
    methods: {
        // 获取图标数据
        getChatData: function(){
            var that = this;
            var params = {uid: that.uid, day: that.day}
            httpPost('m=crm&c=dataBoard&a=dtlchat', params).then(function (res) {
                let data = res.data.data;
                that.qxt_keys = data.qxt_keys;
                that.qxt_val = data.qxt_val;
                that.cnt_keys = data.cnt_keys;
                that.cnt_val = data.cnt_val;
                that.initEcharts1();
                that.initEcharts2();
            }).catch(function (error) {
                console.log(error);
            })
        },
        initEcharts1() {
            var myChart = echarts.init(document.getElementById('showmain1'));
            var option = {
                title: {
                    text: '操作（次）',
                    textStyle: {
                        color: '#999999',
                        fontSize: 12,
                        fontWeight: '500'
                    }
                },
                color: ['#4367F9', '#46a6ff', '#5259F4'],
                // legend: {},
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    top: '11%',
                    left: '1%',
                    right: '3%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: this.qxt_keys,
                    axisLine: { show: false },
                    axisTick: { show: false },
                    splitLine: {
                        lineStyle: {
                            type: 'dashed',
                            color: '#f3f3f3'
                        },
                        show: false
                    },
                },
                yAxis: {
                    type: 'value',
                    axisTick: {
                        show: false
                    },
                    axisLine: {
                        show: false
                    },
                    splitLine: {
                        lineStyle: {
                            color: '#eee',
                            type: 'dashed'
                        },
                        show: true
                    },
                },
                series: [
                    {
                        name: '轨迹次数',
                        data: this.qxt_val,
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                //右，下，左，上
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                    {
                                        offset: 0,
                                        color: '#156EFD'
                                    },
                                    {
                                        offset: 0.9,
                                        color: '#fff'
                                    }
                                ], false),
                            }
                        },
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
        initEcharts2() {
            var myChart = echarts.init(document.getElementById('showmain2'));
            var option1 = {
                title: {
                    text: '访问量（次）',
                    textStyle: {
                        color: '#999999',
                        fontSize: 12,
                        fontWeight: '500'
                    }

                },
                color: ['#0062ff'],
                grid: {
                    top: '11%',
                    left: '1%',
                    right: '3%',
                    bottom: '3%',
                    containLabel: true
                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{b} ({c})'
                },
                xAxis: {
                    type: 'category',
                    axisLabel: {
                        show: true,
                        textStyle: {
                            color: '#666',
                            fontSize: 12
                        }
                    },
                    axisLine: {
                        show: true,
                        lineStyle: {
                            color: "#f3f3f3",
                            width: 1,
                            type: "solid"
                        }
                    },
                    data: this.cnt_keys
                },
                yAxis: {
                    type: 'value',
                    axisLine: { show: false },
                    axisTick: { show: false },
                    splitLine: { //网格线
                        lineStyle: {
                            type: 'dashed' //设置网格线类型 dotted：虚线 solid:实线
                        },
                        show: true //隐藏或显示
                    }
                },
                series: [
                    {
                        data: this.cnt_val,
                        type: 'bar',
                        showBackground: true,
                        barWidth: 20,
                        backgroundStyle: {
                            color: 'rgba(180, 180, 180, 0.2)'
                        },
                        label: {
                            // 柱图头部显示值
                            show: true,
                            position: "top",
                            color: "#8896FB",
                            fontSize: "12px",
                            formatter: (params) => {
                                return params.value;
                            },
                        },
                        itemStyle: {
                            normal: {
                                barBorderRadius: [12, 12, 0, 0],
                                color: new echarts.graphic.LinearGradient(0, 1, 0, 0, [{
                                    offset: 0,
                                    color: "#D2E9FF"
                                }, {
                                    offset: 1,
                                    color: "#8896FB"
                                }], false)
                            },
                        }
                        // itemStyle: {
                        //     barBorderRadius: [12, 12, 0, 0]
                        // },
                    }
                ]
            };
            myChart.setOption(option1);
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