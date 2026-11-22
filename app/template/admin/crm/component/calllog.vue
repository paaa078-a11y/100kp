<template>
    <div :class="{moduleElenAl: true, moduleElenAlDrawer: drawer}">
        <div class="moduleSeachs">
            <div class="moduleElTabInpt">
                <div class="moduleInptList" v-if="source != 'jobtai'">
                    <el-select size="small" v-model="searchForm.crm_uid" placeholder="客户经理" clearable @change="search">
                        <el-option v-for="item in adminUserList" :key="item.uid" :label="item.name" :value="item.uid"></el-option>
                    </el-select>
                </div>
				<div v-for="(searchItem, searchIndex) in searchList" class="moduleInptList">
				    <el-select size="small" v-model="searchForm[searchItem.param]" slot="prepend" :clearable="true" :placeholder="searchItem.name" @change="search">
				        <el-option v-for="(searchLabel, searchValue) in searchItem.value" :label="searchLabel" :value="searchValue" ></el-option>
				    </el-select>
				</div>
                <div class="moduleInptList">
                    <el-date-picker size="small" v-model="daterange" type="datetimerange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"
                        :default-time="['00:00:00', '23:59:59']" style="width: 280px;" @change="search"></el-date-picker>
                </div>
                <div class="moduleInptList">
                    <el-input size="small" @keyup.enter.native="search" placeholder="请输入手机号查询" v-model="searchForm.keyword" class="input-with-select" clearable>
                        <el-select v-model="searchForm.crm_type" slot="prepend" placeholder="被叫">
                            <el-option label="被叫" value="1"></el-option>
                            <el-option label="主叫" value="2"></el-option>
                        </el-select>
                    </el-input>
                </div>
                <div class="moduleInptList">
                    <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
                </div>
            </div>
        </div>
        <div class="moduleElTable">
            <el-table :data="list" border style="width: 100%" ref="multipleTable" @selection-change="handleSelectionChange"
                :header-cell-style="{background:'#f5f7fa',color:'#606266'}" height="100%" v-loading="loading" :empty-text="emptytext">
                <el-table-column prop="callee" label="被叫号码" width="150">
                </el-table-column>
                <el-table-column label="名称/业务员" min-width="200">
                    <template slot-scope="props">
                        <div class="moduleProps">
                            <span>{{ props.row.callee_name }}</span>
                            <span class="gsd">{{ props.row.username }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="caller" label="主叫号码" width="150">
                </el-table-column>
                <el-table-column label="接通/挂断" width="220">
					<template slot-scope="scope">
					    <div class="moduleProps" v-if="scope.row.connect_time>0">
					        <span>{{scope.row.connect_time_n}} 接通</span>
					    </div>
						<div class="moduleProps" v-else>
							<span>-</span>
						</div>
						<div class="moduleProps" v-if="scope.row.disconnect_time>0">
						    <span>{{scope.row.disconnect_time_n}} 挂断</span>
						</div>
						<div class="moduleProps" v-else>
							<span>-</span>
						</div>
					</template>
                </el-table-column>
                <el-table-column label="状态" width="160">
                    <template slot-scope="props">
						<el-tag type="danger"size="mini" v-if="props.row.call_status == 1">主叫挂断</el-tag>
						<el-tag type="warning"size="mini" v-else-if="props.row.call_status == 2">主叫接通</el-tag>
						<el-tag type="success"size="mini" v-else-if="props.row.call_status == 3">已接通</el-tag>
						<el-tag type="danger"size="mini" v-else-if="props.row.call_status == 0 && props.row.status == 3">未接通</el-tag>
						<el-tag type="info" size="mini"v-else-if="props.row.has_session != 0"> 同步中</el-tag>
						<template v-else-if="props.row.err_msg != '呼叫失败'">
							<el-tooltip class="item" effect="dark" :content="props.row.err_msg" placement="right">
								<el-tag type="danger">呼叫失败 <i class="el-icon-warning"></i></el-tag>
							</el-tooltip>
						</template>
						<el-tag type="danger" v-else>呼叫失败</el-tag>
					</template>
                </el-table-column>
                <el-table-column prop="time_length_n" label="通话时长(秒)" width="140">
                </el-table-column>
                <el-table-column label="是否录音" width="130">
					<template slot-scope="scope">
						<el-tag type="success" v-if="scope.row.oss_record_path != ''" style="cursor: pointer; width: 87px;" @click="playVoice(scope.row.id, scope.row.oss_record_path)">
							<i class="yuYinBoxWt" :id="'onPlay_'+scope.row.id">{{scope.row.time_length_n}}</i>
						</el-tag>
						<span v-else>否</span>
					</template>
                </el-table-column>
                <el-table-column prop="ctime_n" label="创建时间" width="200">
                </el-table-column>
                <el-table-column label="操作" width="100" fixed="right">
                    <template slot-scope="props">
						<template v-if="props.row.oss_record_path != '' ">
							<el-button size="mini" type="primary" @click="downRec(props.row.oss_record_path)">下载<i class="el-icon-download el-icon--right"></i></el-button>
						</template>
						<span v-else>--</span>
					</template>
                </el-table-column>
            </el-table>
        </div>
        <div class="modulePaging">
            <div class="modulePagNum">
                <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                   :current-page="page" :page-sizes="pageSizes" :page-size="limit"
                   layout="total, sizes, prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
    </div>
</template>
<!-- script -->
<script>
    var isIELowVision = false;
    $(document).ready(function () {
        isIELowVision = IEVersion();
    });

    function IEVersion() {
        //取得浏览器的userAgent字符串
        var userAgent = navigator.userAgent;
        //判断是否IE浏览器
        var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1;
        if (isIE) {
            var reIE = new RegExp("MSIE (\\d+\\.\\d+);");
            reIE.test(userAgent);
            var fIEVersion = parseFloat(RegExp["$1"]);
            if (fIEVersion < 10) {
                return true;
            }
        } else {
            return false;
        }
    }
    module.exports = {
        props: {
            drawer: {
                type: Boolean, // 是否是抽屉方式打开组件，默认否
                default: false
            },
			source: { // 加载来源
				type: String,
				default: ''
			},
            source_data: { // 来源数据
                type: Object,
                default: null
            },
            startend: {
                type: Array,
                default: function(){
                    return []
                }
            },
            callstatus: {
                type: String,
                default: '-1',

            }
        },
        watch: {
            source_data: {
                handler(val, oldVal) {
                    this.resetSearch();
                    this.sourceDataHandle();
                    this.search();
                }
            },
            startend: {
                handler(val, oldVal) {
                    if (val[0] && val[1]) {
                        let firstDay = new Date(val[0]);
                        // 获取当前月份的最后一天
                        let lastDay = new Date(val[1]);
                        this.daterange = [firstDay, lastDay];
                        this.search();
                    }
                },
                immediate: true
            },
            callstatus: {
                handler(val, oldVal) {
                    if (val && val !== '-1') {
                        this.searchForm.call_status = val
                        this.search();
                    }
                },
                immediate: true
            },
        },
        data: function () {
            return {
                emptytext: '暂无数据',
                loading: false,
                // 搜索筛选项
                searchList: [],
                searchForm: {
                    crm_type: '1',
                    keyword: '',
                    call_status:'-1'
                },
                daterange:[],
                adminUserList:[],
                contactOssUrl:'',
                prevPage: 0,
                page: 1,
                limit: 0,
                list: [],
                total: 0,
                pageSizes: [],

                checkedAll: false, // 全选
                checkedAllIndeterminate: false,
                multipleSelection: [], // 多选值存储
                idArr: [],
            }
        },
        created(){
            this.sourceDataHandle();
			this.getStaticData();
            this.getList();
        },
        methods: {
            downRec(path){
                window.location.href = baseUrl+'m=crm&c=crm_outcall&a=downloadRec&path=' + path +'&pytoken=' + localStorage.getItem('pytoken');
            },
            playVoice: function(chatId, path){
                let that = this
                let voiceUrl = that.contactOssUrl + path;
                let onplay = false;

                if ($('.yuYinBoxOnPlay')) {
                    $('.yuYinBoxOnPlay').attr('class', 'yuYinBoxWt');
                }

                if (isIELowVision) {
                    var head = document.head || document.getElementsByTagName('head')[0];
                    var bgSound = document.getElementsByTagName('bgSound')[0];
                    if (!bgSound) {
                        let player = document.createElement('bgSound');
                        head.appendChild(player);
                        player.src = voiceUrl;
                        onplay = true;
                    }else{
                        if (bgSound.src == '') {
                            bgSound.src = voiceUrl;
                            onplay = true;
                        } else {
                            bgSound.src = '';
                        }
                    }
                } else {
                    let audio = $("#player")[0];
                    if(audio && !audio.paused && audio.src==voiceUrl){
                        audio.pause();
                        audio.currentTime = 0;
                    }else{
                        $("#recordVoice").html("")
                        //来一个audio内容，看好这里已经给src赋值了
                        var aud = $('<audio id="player"  src="' + voiceUrl + '" hidden></audio>')
                        //放到容器里区
                        $("#recordVoice").html(aud)
                        //获取当前audio
                        audio = aud[0];
                        audio.play(); // 播放
                        onplay = true;
                    }
                    audio.addEventListener("play", function() { //开始播放时触发
                        //console.log('开始播放');
                    });
                    audio.addEventListener("pause", function() { // 暂停时会触发，当播放完一首歌曲时也会触发
                        //console.log('播放暂停');
                    })
                    audio.addEventListener("ended", function() {
                        //console.log('播放结束');
                        $('.yuYinBoxOnPlay').attr('class', 'yuYinBoxWt');
                    }, false);
                }
                if (onplay) {
                    $('#onPlay_' + chatId).attr('class', 'yuYinBoxOnPlay');
                } else {
                    $('#onPlay_' + chatId).attr('class', 'yuYinBoxWt');
                }
            },
            handleSizeChange(val) {
                this.limit = val;
                this.getList();
            },
            handleCurrentChange(val) {
                this.page = val;
                this.getList();
            },
            resetSearch() {
                this.daterange = [];
                this.searchForm = {
                    crm_type: '1',
                    keyword: '',
                    call_status:'-1',
                }
            },
            search() {
                this.page = 1;
                this.getList();
            },
            getList() {
                let that = this,
                    url = 'm=crm&c=crm_outcall&a=log',
                    searchForm = that.searchForm,
                    params = {
                        page: that.page,
                        limit: that.limit,
                    };
                if (this.daterange && this.daterange.length > 0) {
                    params['s_time'] = this.daterange[0].getTime()/1000;
                    params['e_time'] = this.daterange[1].getTime()/1000;
                }
                if (this.source) {
                    params.source = this.source;
                    if (this.source == 'jobtai') {
                        url = 'm=crm&c=crm_index&a=getOutcallLog'
                    }
                }
                that.loading = true;
                that.emptytext = "数据加载中";
                httpPost(url, {...params, ...searchForm}).then(function (response) {
                    let res = response.data,
                        data = res.data;

                    that.list = data.list;
                    that.total = parseInt(data.total);
                    that.pageSizes = data.page_sizes;
                    if (that.limit === 0) {
                        that.limit = parseInt(data.limit); // 取系统配置默认数量
                    }
                    if (that.page > data.page) {
                        that.page = parseInt(data.page); // 最后一页被删除后，取最新的页数
                    }
                    if(that.prevPage != that.page){
                        that.prevPage = that.page;
                        that.$refs.multipleTable.bodyWrapper.scrollTop = 0;
                    }
                    that.loading = false;
                    if (that.list.length === 0){
                        that.emptytext = "暂无数据";
                    }
                })
            },
			getStaticData(){
			    var that = this
				let params = {}
			    httpPost('m=crm&c=crm_outcall&a=getLogStaticData',params,{hideloading:true}).then((result) => {
					var res = result.data;
					if (res.error == 0) {
						that.searchList = res.data.search_list;
						that.adminUserList = res.data.adminUserList;
						that.contactOssUrl = res.data.contactOssUrl;
					}
			    });
			},
            handleSelectionChange(val) {
                if (val.length == 0) {
                    this.checkedAll = false;
                    this.checkedAllIndeterminate = false;
                } else {
                    if (val.length === this.list.length) {
                        this.checkedAll = true;
                        this.checkedAllIndeterminate = false;
                    } else {
                        this.checkedAll = false;
                        this.checkedAllIndeterminate = true;
                    }
                }
                this.multipleSelection = val;
            },
            batch(type) {
                if (this.multipleSelection.length == 0) {
                    message.warning('请选择要操作的数据项');
                    return false;
                }

                let idArr = [];
                this.multipleSelection.forEach(function(item) {
                    idArr.push(item.id);
                })
                this.idArr = idArr;

                if (type == 'del') {
                    this.del();
                }
            },
            checkAll(val) {
                val ? this.checkedAllIndeterminate = false : '';
                this.$refs.multipleTable.toggleAllSelection();
            },
            del(idx) {
                let that = this,
                    params = {},
                    msg = '';

                if (typeof idx == 'undefined') { // 批量删除
                    params.del = this.idArr;
                    msg = '你确定要删除选中项吗？';
                } else {// 单个删除
                    params.del = that.list[idx].id;
                    msg = '你确定要删除当前项吗？';
                }

                delConfirm(this, params, function (params) {
                    httpPost('m=crm&c=crm_outcall&a=del', params).then(function(res) {
                        if (res.data.error > 0) {
                            message.error(res.data.msg);
                        } else {
                            message.success(res.data.msg, function () {
                                that.$refs.multipleTable.clearSelection();
                                that.getList();
                            });
                        }
                    })
                }, msg)
            },
            sourceDataHandle() {
                let source_data = this.source_data;
                if (!source_data) { // 无数据
                    return false;
                }

                if (source_data.crm_uid) {
                    this.searchForm.crm_uid = source_data.crm_uid;
                }
                if (source_data.times) {
                    this.timesHandle(); // 目前仅工作台使用
                }
            },
            // 加载组件，字符串时间转换
            timesHandle() {
                let source_data = this.source_data;
                let now = new Date();
                if (source_data.times == 'month') {
                    // 获取当前月份的第一天
                    let firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    // 获取当前月份的最后一天
                    let lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
                    this.daterange = [firstDay, lastDay];
                } else if (source_data.times == 'yesterday') {
                    now.setHours(0, 0, 0, 0); // 将小时、分钟、秒和毫秒都设置为0
                    let yestardays = now.setDate(now.getDate() - 1); // 日期前移一天
                    let yestardaye = new Date(yestardays);
                    yestardaye.setHours(23, 59, 59);
                    this.daterange = [now, yestardaye];
                }  else if (source_data.times == 'today') {
                    now.setHours(0, 0, 0, 0); // 将小时、分钟、秒和毫秒都设置为0
                    let todays = now.setDate(now.getDate()); // 当前日期
                    let todayend = new Date(todays);
                    todayend.setHours(23, 59, 59);
                    this.daterange = [now, todayend];
                } else if (source_data.times == 'day7') {
                    now.setHours(0, 0, 0, 0); // 将小时、分钟、秒和毫秒都设置为0
                    let nowtime = now.getTime();
                    now.setDate(now.getDate() - 6); // 日期前移6天
                    let end = new Date(nowtime);
                    end.setHours(23, 59, 59);
                    this.daterange = [now, end];
                }
            },
        }
    };
</script>
<style>
    .moduleElenAlDrawer {
        position: relative;
        padding: 0 20px;
    }
    .moduleElenAlDrawer .moduleSeachs {
        padding: 0;
        width: 100%;
    }
    .moduleElenAlDrawer .moduleElTable {
        padding: 0;
        width: 100%;
        height: calc(100% - (34px + 50px) - 12px); /* 高度减去搜索栏、分页、以前表格的上边距 */
    }

    .yuYinBoxWt{
        width: 15px;
        height: 15px;
        background: url(../../../admin/images/bf.png) no-repeat;
        padding-left: 18px;
    }
    .yuYinBoxOnPlay{
        width: 15px;
        height: 15px;
        background: url(../../../admin/images/bfz.gif) no-repeat;
        padding-left: 18px;
    }
</style>