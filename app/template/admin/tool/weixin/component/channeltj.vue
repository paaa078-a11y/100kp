<template>
    <div class="moduleElHight" v-if="islook">
        <div class="channelStatOne">
            <div class="channelStaInfo">
                <div class="channelTitle">
                    <div class="channelTitName">
                        <span>总览数据</span>
                    </div>
                    <div class="channelTitSele">
                        <el-select v-model="qdval" placeholder="请选择" @change="qdselect">
                            <el-option v-for="item in qdarr" :key="item.id" :label="item.name" :value="item.id">
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="channelStaData">
                    <ul>
                        <li class="channelStaCur">
                            <div class="channelStaGzrs">
                                <div class="channelGzrsImg">
                                    <img src="../../images/chanIm1.png" alt="">
                                </div>
                                <div class="channelGzrsNum">
                                    <b>{{ generalData.follow_today }}</b>
                                    <span>今日关注人数</span>
                                </div>
                            </div>
                            <div class="channelStaQgrs">
                                <div class="channelQgrsLis">
                                    <span>今日取关的人数</span>

                                    <el-tooltip content="今日取消关注的人数" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>

                                    <span class="reds">{{ generalData.unfollow_today }}</span>
                                </div>
                                <div class="channelssx"></div>
                                <div class="channelQgrsLis">
                                    <span>今日净关注人数</span>
                                    <el-tooltip content="今日关注数减去今日取关人数" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                    <span class="reds">{{ generalData.netfollow_today }}</span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="channelStaGzrs">
                                <div class="channelGzrsImg">
                                    <img src="../../images/chanIm2.png" alt="">
                                </div>
                                <div class="channelGzrsNum">
                                    <b>{{ generalData.scan_today }}</b>
                                    <span>今日扫码人数</span>
                                </div>
                            </div>
                            <div class="channelStaQgrs">
                                <div class="channelQgrsLis">
                                    <span>新用户</span>
                                    <el-tooltip content="通过扫该渠道码关注此公众号的用户" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                    <span class="reds">{{ generalData.newcustomer_scan_today }}</span>
                                </div>
                                <div class="channelssx"></div>
                                <div class="channelQgrsLis">
                                    <span>老用户</span>
                                    <el-tooltip content="在该渠道码建立前关注此公众号的用户" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                    <span class="reds">{{ generalData.oldcustomer_scan_today }}</span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="channelStaGzrs">
                                <div class="channelGzrsImg">
                                    <img src="../../images/chanIm3.png" alt="">
                                </div>
                                <div class="channelGzrsNum">
                                    <b>{{ generalData.scan_all }}</b>
                                    <span>总扫码人数</span>
                                </div>
                            </div>
                            <div class="channelStaQgrs">
                                <div class="channelQgrsLis">
                                    <span>新用户</span>
                                    <el-tooltip content="通过扫该渠道码关注此公众号的用户" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                    <span class="reds">{{ generalData.newcustomer_scan_all }}</span>
                                </div>
                                <div class="channelssx"></div>
                                <div class="channelQgrsLis">
                                    <span>老用户</span>
                                    <el-tooltip content="在该渠道码建立前关注此公众号的用户" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                    <span class="reds">{{ generalData.oldcustomer_scan_all }}</span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="channelStaGzrs">
                                <div class="channelGzrsImg">
                                    <img src="../../images/chanIm4.png" alt="">
                                </div>
                                <div class="channelGzrsNum">
                                    <b>{{ generalData.follow_all }}</b>
                                    <span>总关注人数</span>
                                </div>
                            </div>
                            <div class="channelStaQgrs">
                                <div class="channelQgrsLis">
                                    <span>总取关人数</span>
                                    <el-tooltip content="总的取关的人数" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                    <span class="reds">{{ generalData.unfollow_all }}</span>
                                </div>
                                <div class="channelssx"></div>
                                <div class="channelQgrsLis">
                                    <span>总净关注人数</span>
                                    <el-tooltip content="总关注数减去总取关人数" placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                    <span class="reds">{{ generalData.netfollow_all }}</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="channelStaEchart">
                <div class="channelTitle">
                    <div class="channelTitName">
                        <span>图表数据</span>
                    </div>
                    <div class="channelTitSele">
                        <div class="channelTitDay">
                            <span :class="echartsday == 1 ? 'channelTitCur' : ''" @click="ecartsTab(1)">今天</span>
                            <span :class="echartsday == 7 ? 'channelTitCur' : ''" @click="ecartsTab(7)">7天内</span>
                            <span :class="echartsday == 15 ? 'channelTitCur' : ''" @click="ecartsTab(15)">15天内</span>
                            <span :class="echartsday == 30 ? 'channelTitCur' : ''" @click="ecartsTab(30)">30天内</span>
                        </div>
                    </div>
                </div>
                <div class="channelEchMain" id="main3">

                </div>
            </div>
        </div>
        <div class="channelStatTwo">
            <div class="channelTitle">
                <div class="channelTitName">
                    <el-date-picker v-model="daterange" type="daterange" @change="changedate"
                        :picker-options="pickerOptions" value-format="yyyy-MM-dd" range-separator="至"
                        start-placeholder="开始日期" end-placeholder="结束日期">
                    </el-date-picker>
                </div>
                <div class="channelTitSele">
                    <el-button type="primary" plain icon="el-icon-upload2" @click="Export()">导出数据</el-button>
                </div>
            </div>
        </div>
        <div class="channelStatThre">
            <el-table ref="table" :data="tablist" v-loading="list_loading" border
                :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" :empty-text="emptytext">
                <el-table-column prop="date" label="时间">
                </el-table-column>
                <el-table-column prop="scannum" label="扫码人数">
                </el-table-column>
                <el-table-column prop="follownum" label="关注人数">
                </el-table-column>
                <el-table-column prop="unfollownum" label="取关人数">
                </el-table-column>
                <el-table-column prop="netfollownum" label="净关注人数">
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                               :current-page="page" :page-size="limit" :page-sizes="pageSizes" :total="total"
                               layout="total, sizes, prev, pager, next, jumper">
                </el-pagination>
            </div>
        </div>
        <div id="export" style="display:none;">
            <div style=" margin-top:10px;">
                <div>
                    <form :action="`${baseUrl}?m=tool&&c=qdm&a=xls`" method="post" id="formstatus" class="myform">
                        <input type="hidden" name="pytoken" :value="pytoken">
                        <input name="export_pid" :value="qdval" type="hidden">
                        <input name="export_sdate" :value="sdate" type="hidden">
                        <input name="export_edate" :value="edate" type="hidden">
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
    
<script>

module.exports = {
    data: function () {
        return {
            islook: false,
            emptytext: '暂无数据',
            qdarr: [],//所有可选的渠道码
            qdval: 0,//选中的渠道码id
            qdname: '',

            generalData: {},//总体统计

            echartsday: 7,
            xAxis: [],
            scanNumArr: [],
            followNumArr: [],
            unfollowNumArr: [],

            daterange: [],
            sdate: '',
            edate: '',
            list_loading: false,
            tablist: [],
            page: 1,
			prevPage:0,
            limit: 0,
            pageSizes: [],
            pagev: '',
            pageNum: 1,
            total: 0,
            pickerOptions: {
                disabledDate(time) {
                    return time.getTime() > Date.now();
                }
            },

            pytoken: localStorage.getItem("pytoken"),
            baseUrl: localStorage.getItem("baseUrl"),
        }
    },

    mounted() {
        this.qdTongji();
    },
    methods: {
        qdselect: function () {
            this.initData()
        },
        qdTongji: function () {
            var self = this;
            httpPost('m=tool&c=qdm&a=qdTongji', {}).then(function (result) {

                var res = result.data;
                if (res.error == 0) {
                    self.qdval = res.data.qdval;
                    self.qdname = res.data.qdname;
                    self.qdarr = res.data.qdarr;

                    self.initData();

                    self.islook = true;
                }
            }).catch(function (e) {
                console.log(e)
            })

        },
        initData: function () {
            this.echartsday = 7;

            this.daterange = [];
            this.sdate = '';
            this.edate = '';

            this.generalPost();
            this.echartsPost();
            this.tableList('refresh');
        },
        handleCurrentChange(val) {
            this.page = val;
            this.tableList()
        },
        handleSizeChange(val) {
            this.page = 1
            this.limit = val
            this.tableList()
        },
        changedate: function (e) {

            if (this.daterange && this.daterange.length > 0) {
                this.sdate = this.daterange[0];
                this.edate = this.daterange[1];
            } else {
                this.sdate = '';
                this.edate = '';
            }
            this.tabledateDone();
        },
        //时间选择器完成选中
        tabledateDone: function () {
            if (this.sdate != '' && this.edate != '') {
                if (this.sdate <= this.edate) {
                    this.tableList('refresh');
                } else {
                    message.error('请选择正确的时间范围！'); return;
                }

            }
        },
        tableList: function (type = 'list') {
            var self = this;
            if (type == 'refresh') {
                self.page = 1;
            }
            var param = {
                qid: self.qdval,
                sdate: self.sdate,
                edate: self.edate,
                page: self.page,
                limit: self.limit,
            };
            this.list_loading = true;
            self.emptytext = "数据加载中";
            httpPost('m=tool&c=qdm&a=tableList', param,{hideloading: true}).then(function (result) {

                var res = result.data;
                if (res.error == 0) {
                    if (res.data.list == 0) {
                        self.page = 1;
                    }
                    self.tablist = res.data.list;
                    self.pageNum = res.data.pageNum;
                    self.total = res.data.total;
                    self.pageSizes = res.data.pageSizes;
					
					if(that.prevPage != that.page){
						that.prevPage = that.page;
						that.$refs.table.bodyWrapper.scrollTop = 0;
					}
                    if (self.tablist.length === 0){
                        self.emptytext = "暂无数据";
                    }
                }
                self.list_loading = false;

            }).catch(function (e) {
                console.log(e)
            })

        },
        generalPost: function () {
            var self = this;
            httpPost('m=tool&c=qdm&a=generalPost', { qid: self.qdval },{hideloading: true}).then(function (result) {

                var res = result.data;
                if (res.error == 0) {
                    self.generalData = res.data.generalData;
                }
            }).catch(function (e) {
                console.log(e)
            })

        },
        //切换图表天数
        ecartsTab: function (echartsday) {
            if (echartsday != this.echartsday) {
                this.echartsday = echartsday;
                this.echartsPost();
            }

        },
        echartsPost: function () {
            var self = this;
            httpPost('m=tool&c=qdm&a=echartsPost', { qid: self.qdval, echartsday: self.echartsday },{hideloading: true}).then(function (result) {

                var res = result.data;
                if (res.error == 0) {
                    self.xAxis = res.data.xAxis;
                    self.scanNumArr = res.data.scanNumArr;
                    self.followNumArr = res.data.followNumArr;
                    self.unfollowNumArr = res.data.unfollowNumArr;
                    self.echartsData();
                }
            }).catch(function (e) {
                console.log(e)
            })

        },
        echartsData() {
            var self = this;
            var myChart = echarts.init(document.getElementById('main3'));
            var option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    top: '3%',
                    left: '2%',
                    right: '3%',
                    bottom: '2%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: self.xAxis,
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
                    splitLine: {
                        lineStyle: {
                            type: 'dashed',
                            color: '#f3f3f3'
                        },
                        show: true
                    }
                },
                series: [
                    {
                        name: '总扫码数',
                        data: self.scanNumArr,
                        type: 'bar',
                        color: new echarts.graphic.LinearGradient(0, 1, 0, 0, [
                            {
                                offset: 0,
                                color: '#399EFC'
                            },
                            {
                                offset: 1,
                                color: '#3ED572'
                            }
                        ])
                    },
                    {
                        name: '关注人数',
                        data: self.followNumArr,
                        type: 'bar',
                        color: new echarts.graphic.LinearGradient(0, 1, 0, 0, [
                            {
                                offset: 0,
                                color: '#399EFC'
                            },
                            {
                                offset: 1,
                                color: '#3ADFF2'
                            }
                        ])
                    },
                    {
                        name: '取关人数',
                        data: self.unfollowNumArr,
                        type: 'bar',
                        color: new echarts.graphic.LinearGradient(0, 1, 0, 0, [
                            {
                                offset: 0,
                                color: '#399EFC'
                            },
                            {
                                offset: 1,
                                color: '#3700eb'
                            }
                        ])
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
        Export: function () {

            var self = this;

            message.confirm('确定导出记录吗？', function () {

                var param = {
                    export_pid: self.qdval,
                    export_sdate: self.sdate,
                    export_edate: self.edate,
                };

                httpPost('m=tool&c=qdm&a=checkxls', param).then(function (result) {

                    var res = result.data;
                    if (res.error == 0) {
                        $('#formstatus').submit();
                    } else {
                        message.error(res.msg); return;
                    }

                }).catch(function (e) {
                    console.log(e)
                })

            },'','','warning')

        },

    },
};
</script>
<style scoped>
.moduleElHight {
    overflow-y: auto;
}
</style>