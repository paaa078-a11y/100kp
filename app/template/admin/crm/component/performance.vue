<!--我的/下属业绩-->
<template>
    <div class="drawerModlue">
        <div class="moduleSeachs">
			<div v-if="depart=='1'">
			    <el-select v-if="adminUserList.length > 0" v-model="crmmanager"  size="small"  placeholder="请选择客户经理"  clearable @change="adminChange">
                    <el-option label="全部" value="all"></el-option>
			        <el-option v-for="item in adminUserList" :label="item.name" :value="item.uid" :key="item.uid"></el-option>
			    </el-select>
			</div>
            <div class="crmperforTips">
                <span :class="(time==''&& !daterangeShow)?'crmSpancur':''" @click="timeTab('')">全部</span>
                <span :class="time=='-1'?'crmSpancur':''" @click="timeTab('-1')">今天</span>
                <span :class="time=='1'?'crmSpancur':''" @click="timeTab('1')">昨天</span>
                <span :class="time=='7'?'crmSpancur':''" @click="timeTab('7')">7天内</span>
                <span :class="time=='30'?'crmSpancur':''" @click="timeTab('30')">30天内</span>
                <span :class="time=='90'?'crmSpancur':''" @click="timeTab('90')">90天内</span>
                <span :class="(time=='' && daterangeShow)?'crmSpancur':''" @click="timeTab('range')">自定义</span>
				<span v-if="daterangeShow">
					<el-date-picker v-model="daterange" @change="changedate" type="daterange" value-format="yyyy-MM-dd" range-separator="~" start-placeholder="起始日期"
				        end-placeholder="结束日期">
				    </el-date-picker>
				</span>
            </div>

        </div>
        <div class="moduleCrmAchi">
            <div class="modCrmAchiLis">
                <div class="modCrmAchiTite">
                    <span>企业模板</span>
                </div>
                <div class="modCrmAchiCont">
                    <div class="modCrmAchiData" v-if="tpl_data.length>0">
                        <div class="modCrmAchiLieb">
                            <div class="modCrmAchiImg">
                                <img src="../../images/crmimg1.png" alt="">
                            </div>
                            <div class="modCrmAchiKehu">
                                <div>
                                    <span>客户总数</span>
                                    <b>{{tpl_data[3].all}}人</b>
                                </div>
                            </div>
                            <div class="modCrmAchiTime">
                                <div class="modCrmAchTimeLis">
                                    <div>
                                        <span>本月</span>
                                        <b>{{tpl_data[0].all}}</b>
                                    </div>
                                </div>
                                <div class="modCrmAchTimeLis">
                                    <div>
                                        <span>本周</span>
                                        <b>{{tpl_data[1].all}}</b>
                                    </div>
                                </div>
                                <div class="modCrmAchTimeLis">
                                    <div>
                                        <span>昨日</span>
                                        <b>{{tpl_data[2].all}}</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modCrmAchiLieb">
                            <div class="modCrmAchiImg">
                                <img src="../../images/crmimg2.png" alt="">
                            </div>
                            <div class="modCrmAchiKehu">
                                <div>
                                    <span>待跟进</span>
                                    <b>{{tpl_data[3].new}}人</b>
                                </div>
                            </div>
                            <div class="modCrmAchiTime">
                                <div class="modCrmAchTimeLis">
                                    <div>
                                        <span>本月</span>
                                        <b>{{tpl_data[0].new}}</b>
                                    </div>
                                </div>
                                <div class="modCrmAchTimeLis">
                                    <div>
                                        <span>本周</span>
                                        <b>{{tpl_data[1].new}}</b>
                                    </div>
                                </div>
                                <div class="modCrmAchTimeLis">
                                    <div>
                                        <span>昨日</span>
                                        <b>{{tpl_data[2].new}}</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modCrmAchiLieb">
                            <div class="modCrmAchiImg">
                                <img src="../../images/crmimg3.png" alt="">
                            </div>
                            <div class="modCrmAchiKehu">
                                <div>
                                    <span>已跟进</span>
                                    <b>{{tpl_data[3].attention}}人</b>
                                </div>
                            </div>
                            <div class="modCrmAchiTime">
                                <div class="modCrmAchTimeLis">
                                    <div>
                                        <span>本月</span>
                                        <b>{{tpl_data[0].attention}}</b>
                                    </div>
                                </div>
                                <div class="modCrmAchTimeLis">
                                    <div>
                                        <span>本周</span>
                                        <b>{{tpl_data[1].attention}}</b>
                                    </div>
                                </div>
                                <div class="modCrmAchTimeLis">
                                    <div>
                                        <span>昨日</span>
                                        <b>{{tpl_data[2].attention}}</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modCrmAchiLis">
                <div class="modCrmAchiTite">
                    <span>{{tpl_title}}</span>
                </div>
                <div class="modCrmAchiCont">
                    <div class="modCrmAchiEchat" id="main11"></div>
                </div>
            </div>
            <div class="modCrmAchiLis">
                <div class="modCrmAchiTite modCrmAchiPosit">
                    <span>{{amount_title}}</span>
                </div>
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="总计" name="first">
                        <div class="modCrmAchongs">
                            <div class="modCrmAchonNeir">
                                <div class="modChonNeirOne">
                                    <div class="modChonNeiTwo">
                                        <div>
                                            <b>¥{{total_num}}</b>
                                            <span>成交金额</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modChonNeirInfo">
                                    <ul>
                                        <li>
                                            <div>
                                                <img src="../../images/crmimg4.png" alt="">
                                                <span>本月</span>
                                                <span>{{month_num}}</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <img src="../../images/crmimg5.png" alt="">
                                                <span>本周</span>
                                                <span>{{week_num}}</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <img src="../../images/crmimg6.png" alt="">
                                                <span>昨日</span>
                                                <span>{{yestoday_num}}</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="图表" name="second">
                        <div class="modCrmAchChat1" id="main22"></div>
                    </el-tab-pane>
                </el-tabs>
            </div>
            <div class="modCrmAchiLis">
                <div class="modCrmAchiTite">
                    <span>{{concern_title}}</span>
                </div>
                <div class="modCrmAchiCont">
                    <div class="modCrmAchiEchat" id="main33"></div>
                </div>
            </div>
        </div>
        <div class="moduleCrmTable">
            <div class="modCrmAchiTite modCrmAchiPosit" style="top: 4px;">
                <span>统计列表</span>
            </div>
            <el-tabs v-model="activeName2" @tab-click="handleClick">
                <el-tab-pane label="客户数量统计" name="first2">
                    <el-table :data="tpl_tableData" style="width: 100%" border stripe :empty-text="emptytext"
                        :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" v-loading="loading">
                        <el-table-column prop="name" label="日期">
                        </el-table-column>
                        <el-table-column prop="cnum" label="客户总数">
                        </el-table-column>
                        <el-table-column prop="dnum" label="待跟进客户">
                        </el-table-column>
                        <el-table-column prop="ynum" label="已跟进客户">
                        </el-table-column>
                    </el-table>
                </el-tab-pane>
                <el-tab-pane label="跟进记录统计" name="second2">
                    <el-table :data="concern_tableData" style="width: 100%" border stripe :empty-text="emptytext"
                        :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                        <el-table-column prop="name" label="跟进方式">
                        </el-table-column>
                        <el-table-column prop="value" label="跟进客户(人)">
                        </el-table-column>
                    </el-table>
                </el-tab-pane>
                <el-tab-pane label="成交金额统计" name="third2">
                    <el-table :data="amount_tableData" style="width: 100%" border stripe :empty-text="emptytext"
                        :header-cell-style="{ background: '#f5f7fa', color: '#606266' }" height="100%" v-loading="loading">
                        <el-table-column prop="name" label="日期">
                        </el-table-column>
                        <el-table-column prop="value" label="成交金额">
                        </el-table-column>
                    </el-table>
                </el-tab-pane>
            </el-tabs>
        </div>
    </div>
</template>

<script>

module.exports = {
    props: {
        depart: {//1为下属业绩
            type: [String, Number],
            default: ''
        },
    },

    data: function () {
        return {
        	emptytext: '暂无数据',
            activeName: 'second',
            activeName2: 'first2',
            loading:false,
			adminUserList:[],

			crmmanager:'',
			time:'',
			stime:'',
			etime:'',
			daterange:[],
			daterangeShow:false,

			tpl_data:[],
			tpl_title:'',
			tpl_tableData:[],
			tpl_xAxis:[],
			tpl_series:[],

			concern_title:'',
			concern_tableData:[],
			concern_series:[],

			amount_title:'',
			amount_tableData:[],
			amount_xAxis:[],
			amount_series:[],
			month_num:0,
			week_num:0,
			yestoday_num:0,
			total_num:0,
		}
    },
	mounted(){

		this.initData();

	},
    methods: {
		adminChange:function(){
			this.initData();
		},
        initData:function(){

			this.tplData();
			this.concernData();
			this.amountData();

		},
		timeTab:function(type){
			if(type=='range'){
				this.time = '';
				this.daterangeShow = true;
			}else{
				this.stime = '';
				this.etime = '';
				this.daterange = [];
				this.daterangeShow = false;
				this.time = type;
				this.initData();
			}
		},
		changedate: function (e) {
			if(this.daterange.length>0){
				this.stime = this.daterange[0];
				this.etime = this.daterange[1];
				this.initData();
			}
		},
		async tplData(){
			var that = this;
			var param = {
				time:that.time,
				stime:that.stime,
				etime:that.etime,
				// depart:that.depart,
				// crmmanager:that.crmmanager
			};
			if (that.crmmanager) {
			    param.crmmanager = that.crmmanager
			    param.depart = 1
            }
			that.loading = true;
			that.emptytext = "数据加载中";
			httpPost('m=crm&c=crm_my_performance&a=index',param).then((result)=>{

			    var res = result.data;
			    if (res.error == 0) {
					this.adminUserList = res.data.adminUserList;
					this.tpl_data = res.data.tpldata;
					this.tpl_title = res.data.title;
					this.tpl_tableData = res.data.tableData;
					this.tpl_xAxis= res.data.xAxis;
					this.tpl_series= res.data.series;
					that.loading = false;
					if (that.tpl_tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
			    }
				this.$nextTick(() => {
				    this.initCharts1();
				})
			}).catch(function(e){
			    console.log(e)
			})
		},
		async concernData(){
			var that = this;
			var param = {
				time:that.time,
				stime:that.stime,
				etime:that.etime,
				// depart:that.depart,
				// crmmanager:that.crmmanager
			};
            if (that.crmmanager) {
                param.crmmanager = that.crmmanager
                param.depart = 1
            }
			that.loading = true;
			that.emptytext = "数据加载中";
			httpPost('m=crm&c=crm_my_performance&a=concern',param).then((result)=>{

			    var res = result.data;
			    if (res.error == 0) {
					this.concern_title = res.data.title;
					this.concern_tableData = res.data.tableData;
					this.concern_series= res.data.series;
					if (that.concern_tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
				}
				this.$nextTick(() => {
				    this.initCharts3();
				})
			}).catch(function(e){
			    console.log(e)
			})
		},
		async amountData(){
			var that = this;
			var param = {
				time:that.time,
				stime:that.stime,
				etime:that.etime,
				// depart:that.depart,
				// crmmanager:that.crmmanager
			};
            if (that.crmmanager) {
                param.crmmanager = that.crmmanager
                param.depart = 1
            }
			that.loading = true;
			that.emptytext = "数据加载中";
			httpPost('m=crm&c=crm_my_performance&a=amount',param).then((result)=>{

			    var res = result.data;
			    if (res.error == 0) {
					this.month_num = res.data.month_num;
					this.week_num = res.data.week_num;
					this.yestoday_num = res.data.yestoday_num;
					this.total_num = res.data.total_num;
					this.amount_title = res.data.title;
					this.amount_tableData = res.data.tableData;
					this.amount_xAxis= res.data.xAxis;
					this.amount_series= res.data.series;
					that.loading = false;
					if (that.amount_tableData.length === 0){
                        that.emptytext = "暂无数据";
                    }
			    }
				this.$nextTick(() => {
				    this.initCharts2();
				})
			}).catch(function(e){
			    console.log(e)
			})
		},
		handleClick:function(){

		},

		initCharts1() {
			var that = this;
		    var myChart = echarts.init(document.getElementById('main11'));
		    var option = {
		        color: ['#23C9C9', '#1890FF', '#1890FF'],
		        legend: {
		            left: 'right',
		            // icon: 'circle',
		        },
		        tooltip: {
		            trigger: 'axis',
		            axisPointer: {
		                type: 'shadow'
		            }
		        },
		        grid: {
		            top: '8%',
		            left: '1%',
		            right: '1%',
		            bottom: '1%',
		            containLabel: true
		        },
		        xAxis: {
		            type: 'category',
		            // boundaryGap: false,
		            data: that.tpl_xAxis,
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
		        series: that.tpl_series
		    };
		    myChart.setOption(option);
		    setTimeout(function () {
		        window.addEventListener("resize", () => {
		            myChart.resize();
		        });
		    }, 200);
		},
		initCharts2() {

			var that = this;

		    var myChart = echarts.init(document.getElementById('main22'));
		    var option = {
		        color: ['#39c3d5', '#46a6ff', '#5259F4'],
		        legend: {
		            left: 'right',
		        },
		        tooltip: {
		            trigger: 'axis',
		            axisPointer: {
		                type: 'shadow'
		            }
		        },
		        grid: {
		            top: '8%',
		            left: '1%',
		            right: '1%',
		            bottom: '1%',
		            containLabel: true
		        },
		        xAxis: {
		            type: 'category',
		            boundaryGap: false,
		            data: that.amount_xAxis,
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
		        series: [
		            {
		                name: '成交金额',
		                data: that.amount_series,
		                type: 'line',
		                smooth: true,
		                areaStyle: {}
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
		initCharts3() {

			var that = this;

		    var myChart = echarts.init(document.getElementById('main33'));
		    var option = {
		        color: ['#2659F0', '#73A0FB', '#B0CCFE', '#FFAD35', '#FFD454'],
		        tooltip: {
		            trigger: 'item',
		            formatter: "{a} <br/>{b}: {c} ({d}%)"
		        },
		        legend: {
		            left: 'center',
		            top: 'bottom',
		            icon: 'circle',
		        },
		        grid: {
		            top: '0%',
		            left: '2%',
		            right: '2%',
		            bottom: '5%',
		            containLabel: true
		        },
		        series: [
		            {
		                name: '收入来源',
		                type: 'pie',
		                radius: ['40%', '60%'],
		                center: ['50%', '42%'],
		                avoidLabelOverlap: false,
		                data: that.concern_series
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
    },
};
</script>
<style scoped>
.drawerModInfo::-webkit-scrollbar {
    display: none;
}
.drawerModlue{
    overflow-y: auto;
    position: relative;
    width: 100%;
    height: 100%;
	padding: 0;
}
.drawerModInfo{
    overflow-y: auto;
    height: calc(100% - 80px);
}
.moduleCrmTable .el-tabs__content {
	width: 100%;
	height: calc(100% - 45px);
}
.moduleCrmTable .el-tab-pane {
	width: 100%;
	height: 100%;
	overflow: hidden;
	position: relative;
}
</style>