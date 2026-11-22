<template>
    <div class="cwszInfoAll" style="margin-top: 0;">
        <div class="cwszInfoLeft" style="width: 100%;">
            <div class="cwszInfoTite">
                <div class="cwszInfoNames"><span>消费金额统计</span></div>
                <div class="echartTimes">
                    <el-date-picker v-model="timeT" size="small" type="daterange"
                                    value-format="yyyy-MM-dd"
                                    range-separator="至" @change="changeTime"
                        start-placeholder="开始日期" end-placeholder="结束日期">
                    </el-date-picker>
                </div>
            </div>
            <div class="cwszInfoFlecy">
                <div id="main1"></div>
                <div class="cwszInfoPaish">
                    <ul>
                        <template v-if="groupType.length">
                        <li v-for="item in groupType">{{ item.name }} {{item.price}}</li>
                        </template>
                        <template v-else>
                            <li>暂无数据</li>
                        </template>
                    </ul>
                </div>
            </div>

        </div>
        <!-- <div class="cwszInfoRight">
            <div class="cwszInfoTite">
                <div><span>消费行业</span></div>
            </div>
            <div id="mainxaio"></div>
        </div> -->

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
                // this.getlist();
                // this.chatxiaofei();
                this.timeT= val
                this.shouzhiFun();
                this.xiaofeiFun();
            },
            immediate: true,
            deep: true,
        }
    },
    data: function () {
        return {
            uri: "m=yunying&c=",
            shouzhi: {},
            timeT:[],
            title: "行业消费排行",
            xiaofeiArr: [],
            xiaofeiList: [],
            value11: '',
            shouruTitle: '收入来源',
            inComeTitle: '收入支出',
            shouzhiTitle: '收支统计',
            value1: '',
            groupType:[],
            name:[],
            values:[],
        }
    },
    methods: {
        changeTime:function () {
            this.name= [];
            this.values= [];
            this.shouzhiFun();
            this.xiaofeiFun();
        },
        async shouzhiFun() {
            let _this = this;
            let url = _this.uri + 'finance_statis&a=xiaofei';
            let sendData = {};
            if (this.time.length > 0) {
                sendData.time_end = this.timeT[1];
                sendData.time_begin = this.timeT[0]
            }
            let response = await httpPost(url, sendData,{hideloading: true})
            let res = response.data;
            if (res.error == 0) {
                _this.shouzhi = res.data;
                _this.name = res.data.names;
                _this.values = res.data.values;
                _this.initCharts1();
            }
        },
        async xiaofeiFun() {
            let _this = this;
            let url = _this.uri + 'finance_statis&a=xiaofei_list';
            let sendData = {};
            if (this.time.length > 0) {
                sendData.time_end = this.timeT[1];
                sendData.time_begin = this.timeT[0]
            }
            let response = await httpPost(url, sendData)
            let res = response.data;
            if (res.error == 0) {
                _this.groupType = res.data.list;
            }
        },
        initCharts1() {
            var myChart = echarts.init(document.getElementById('main1'));
            var option = {
                color: ['#39c3d5', '#46a6ff', '#5259F4'],
                legend: {},
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    top: '12%',
                    left: '2%',
                    right: '5%',
                    bottom: '4%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: this.shouzhi.names,
                    axisLine: { show: false },
                    axisTick: { show: false },
                    splitLine: {
                        lineStyle: {
                            type: 'dashed',
                            color: '#f3f3f3'
                        },
                        show: true
                    }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { show: false },
                    axisTick: { show: false },
                    splitLine: { //网格线
                        lineStyle: {
                            type: 'dashed',
                            color: '#f3f3f3'
                        },
                        show: true
                    }
                },
                series: this.shouzhi.values,
            };
            myChart.clear();
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
<style scoped></style>